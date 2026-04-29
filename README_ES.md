# GolobaRMA

Paquete Laravel/Bagisto que extiende `bagisto/bagisto-rma` para el marketplace **Goloba** (Colombia).
Añade soporte para el **Derecho de Retracto** (Ley 1480, Art. 47), un mecanismo de disputas vendedor-admin
y correos transaccionales en español.

---

## Requisitos previos

| Dependencia | Versión |
|---|---|
| PHP | ^8.1 \| ^8.2 |
| Bagisto | ^2.x |
| `bagisto/bagisto-rma` | ^2.2 |
| `bagisto/marketplace` | compatible con Bagisto 2.x |

> **Nota:** `bagisto/bagisto-rma` y `bagisto/marketplace` deben estar instalados y configurados antes de instalar este paquete.

---

## Instalación

### 1. Copiar el paquete

Coloca el directorio `GolobaRMA` en la carpeta de paquetes del proyecto:

```
packages/
└── Goloba/
    └── GolobaRMA/
```

### 2. Registrar en `composer.json`

Agrega el autoload PSR-4 al `composer.json` **del proyecto** (no del paquete):

```json
"autoload": {
    "psr-4": {
        "Goloba\\GolobaRMA\\": "packages/Goloba/GolobaRMA/src"
    }
}
```

Luego regenera el autoloader:

```bash
composer dump-autoload
```

### 3. Registrar el Service Provider

Agrega el provider en `config/app.php`, dentro del array `providers`:

```php
Goloba\GolobaRMA\Providers\GolobaRMAServiceProvider::class,
```

> El provider ya está declarado en la sección `extra.laravel.providers` del `composer.json` del paquete, por lo que en instalaciones con auto-discovery activo este paso puede omitirse. En Bagisto se recomienda registrarlo explícitamente para garantizar el orden de carga.

### 4. Ejecutar las migraciones

```bash
php artisan migrate
```

Esto crea o modifica las siguientes tablas:

| Migración | Cambio |
|---|---|
| `2025_01_12_000001` | Columna `rma_type` en tabla `rma` |
| `2026_01_13_000002` | Columna `is_seller` en `rma_messages` |
| `2026_03_25_000003` | Columnas `retracto_expires_at`, `retracto_seal_intact` en `rma` |
| `2026_03_27_000004` | Tabla `colombian_holidays` |
| `2026_03_30_000005` | Tablas `rma_disputes` y `rma_dispute_images` |
| `2026_04_14_000006` | Estados adicionales en RMA (`paid`, `replaced`) |

### 5. Cargar festivos colombianos

El cálculo de días hábiles para el Derecho de Retracto requiere datos en la tabla `colombian_holidays`.
Ejecuta el seeder incluido:

```bash
php artisan db:seed --class="Goloba\GolobaRMA\Database\Seeders\ColombianHolidaysSeeder"
```

El seeder carga los festivos de **2026 y 2027**. Para años adicionales, agrega los registros manualmente
desde el panel de administración (Configuración → Festivos) o directamente en la tabla:

```sql
INSERT INTO colombian_holidays (date, name, year) VALUES ('2028-01-01', 'Año Nuevo', 2028);
```

> Si la tabla no tiene datos para el año en curso al momento de crear una RMA de retracto, el sistema lanzará una `\RuntimeException`.

### 6. Limpiar cachés

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Configuración

### Variables de entorno

No requiere variables de entorno adicionales. El paquete usa la configuración existente de Bagisto y Marketplace.

Si el proyecto usa el microservicio de tracking de Servientrega, asegúrate de que esté definida en `.env`:

```
SERVIENTREGA_TRACKING_URL=https://sewebhook.goloba.com
```

### Archivo de configuración de Retracto

La lógica de categorías que requieren verificación de sello intacto se gestiona en:

```
src/Config/retracto.php
```

Los IDs de categoría configurados como "condicionadas" (requieren checkbox de sello) son:
`4, 5, 6, 8, 10, 12, 14, 15`.

---

## Lo que registra el Service Provider

El `GolobaRMAServiceProvider` realiza automáticamente las siguientes acciones al cargar:

- Carga las migraciones del paquete.
- Registra las rutas de admin, seller y shop (las de shop se registran en `app->booted()` para ganar prioridad sobre el vendor).
- Prepone el namespace de vistas `rma` para sobrescribir vistas del vendor original.
- Carga las traducciones bajo el namespace `goloba-rma`.
- Fusiona la configuración de retracto y los menús de admin/seller.
- Registra los bindings del contenedor:
  - `RMAMessages` contract → `Goloba\GolobaRMA\Models\RMAMessages`
  - `RMAMessagesRepository` → `Goloba\GolobaRMA\Repositories\RMAMessagesRepository`
  - `OrderRMADataGrid` (shop) → versión extendida de GolobaRMA
- Registra `RetractoService` como singleton.
- Listener que habilita RMA automáticamente al crear productos (`catalog.product.create.after`).

---

## Estructura del paquete

```
packages/Goloba/GolobaRMA/
├── composer.json
├── src/
│   ├── Config/
│   │   ├── admin-menu.php          # Entrada "Festivos" en menú admin
│   │   ├── retracto.php            # Mapa de categorías condicionadas
│   │   └── seller-menu.php         # Entrada RMA en menú del seller
│   ├── Console/Commands/           # (Espacio reservado para comandos artisan futuros)
│   ├── Database/
│   │   ├── Migrations/             # 6 migraciones (ver sección de instalación)
│   │   └── Seeders/
│   │       └── ColombianHolidaysSeeder.php
│   ├── DataGrids/
│   │   ├── Shop/OrderRMADataGrid.php       # Solo muestra órdenes completadas
│   │   └── Seller/SellerRmaDataGrid.php    # Filtrado por seller
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── RmaController.php           # Override — notificaciones seller
│   │   │   ├── DisputeController.php       # Resolución de disputas
│   │   │   └── ColombianHolidaysController.php
│   │   ├── Seller/
│   │   │   └── RMAController.php           # Panel del vendedor + submitDispute()
│   │   └── Shop/
│   │       └── GolobaCustomerController.php # Override del shop customer controller
│   ├── Mail/                        # Mailables transaccionales (5 clases + helper)
│   ├── Models/
│   │   ├── RMA.php
│   │   ├── RMAMessages.php
│   │   ├── RmaDispute.php
│   │   └── RmaDisputeImage.php
│   ├── Providers/
│   │   ├── GolobaRMAServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Repositories/
│   │   └── RMAMessagesRepository.php
│   ├── Resources/
│   │   ├── lang/es/app.php          # Traducciones bajo namespace 'goloba-rma'
│   │   └── views/
│   │       ├── admin/               # Vistas admin (festivos, disputas)
│   │       ├── emails/              # Plantillas de correo
│   │       ├── seller/rma/          # Panel del vendedor
│   │       └── shop/customer/rma/   # Vistas sobrescritas del shop
│   ├── Routes/
│   │   ├── admin-routes.php
│   │   ├── seller-routes.php
│   │   └── shop-routes.php
│   └── Services/
│       ├── RetractoService.php      # Cálculo de días hábiles (Ley 1480)
│       └── ColombianHolidaysSeeder.php
```

---

## Rutas registradas

### Admin
| Método | URI | Nombre |
|---|---|---|
| GET/POST | `/admin/rma/...` | Sobreescritura de rutas del vendor |
| GET/POST | `/admin/rma/disputes/...` | Panel de disputas |
| GET/POST | `/admin/rma/festivos/...` | Gestión de festivos colombianos |

### Seller
| Método | URI | Nombre |
|---|---|---|
| GET | `/vendedor/cuenta/rma` | `goloba.seller.rma.index` |
| GET | `/vendedor/cuenta/rma/view/{id}` | `goloba.seller.rma.view` |
| POST | `/vendedor/cuenta/rma/change-status` | `goloba.seller.rma.change_status` |
| POST | `/vendedor/cuenta/rma/submit-dispute` | `goloba.seller.rma.submit_dispute` |
| GET | `/vendedor/cuenta/rma/get-messages` | `goloba.seller.rma.get_messages` |
| POST | `/vendedor/cuenta/rma/send-message` | `goloba.seller.rma.send_message` |

### Shop (cliente)
| Método | URI | Descripción |
|---|---|---|
| GET | `/customer/account/rma/check-retracto` | Verifica elegibilidad de retracto para una orden |

---

## Correos transaccionales

El paquete envía correos en español usando `Mail::queue()`. Los Mailables disponibles son:

| Clase | Evento | Destinatarios |
|---|---|---|
| `NewRequestCustomer` | Nueva solicitud RMA | Cliente |
| `NewRequestSeller` | Nueva solicitud RMA | Seller |
| `StatusUpdate` | Cambio de estado | Cliente o Seller |
| `DisputeCreatedAdmin` | Disputa abierta | Admin |
| `DisputeResolved` | Disputa resuelta | Seller + Cliente |

> El correo original del vendor (`CustomerRmaCreationEmail`, en inglés) es suprimido automáticamente para evitar duplicados.

---

## Comandos útiles

```bash
# Limpiar cachés
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Ver rutas del paquete
php artisan route:list | findstr rma

# Verificar migraciones
php artisan migrate:status

# Re-ejecutar seeder de festivos
php artisan db:seed --class="Goloba\GolobaRMA\Database\Seeders\ColombianHolidaysSeeder"
```

---

## Notas importantes

- **Tailwind:** Las vistas usan únicamente clases de color en variante sólida (p.ej. `bg-blue-500`, no `bg-blue-100`). El tema de Bagisto/Marketplace no compila el CSS de paquetes externos.
- **Blade/Vue:** No colocar directivas `@if/@endif` ni `{{ }}` dentro de bloques `<script type="text/x-template">`. Para condicionales dentro del template Vue usar `v-if`/`v-else`.
- **Modales en admin:** Usar `<teleport to="body">` con `z-[9999]` para escapar el stacking context del sidebar.
- **Festivos 2028:** Pendiente agregar al seeder o mediante el panel de administración.
