# GolobaRMA - Sistema de RMA para Marketplace Colombiano

Paquete de extensión del sistema RMA de Bagisto para marketplace con soporte de legislación colombiana.

## 🎯 Características

- ✅ Panel de vendedor con listado y detalle de RMAs
- ✅ Filtrado automático por vendedor (solo ve sus RMAs)
- ✅ Detección automática de "derecho de retracto" (5 días hábiles)
- ✅ Formulario cliente sin campos de dirección de recolección
- ✅ Compatible con estructura marketplace de Bagisto

## 📦 Instalación

1. El paquete ya está instalado en `packages/Goloba/GolobaRMA`
2. Registrado en `config/app.php`
3. Migraciones ejecutadas
4. Vistas sobrescritas correctamente

## 🎨 Estilos

### Modo Actual: **Usando estilos del core de Bagisto**

Las vistas usan únicamente clases Tailwind que ya existen en Bagisto core.
No se compilan ni publican assets adicionales.

**Ventajas:**
- ✅ Cero dependencias adicionales
- ✅ Cero paso de build
- ✅ Consistencia garantizada con el tema
- ✅ Actualizaciones más fáciles

### Modo Futuro: **Estilos custom (si el cliente lo requiere)**

La infraestructura está preparada para estilos custom:

```bash
# 1. Instalar dependencias
cd packages/Goloba/GolobaRMA
npm install

# 2. Activar estilos en los archivos CSS
# Editar: src/Resources/assets/css/seller.css
# Descomentar las líneas @tailwind

# 3. Compilar assets
npm run dev    # Desarrollo (con watch)
npm run build  # Producción

# 4. Publicar a public/
php artisan vendor:publish --tag=goloba-rma-assets --force

# 5. Cargar en las vistas
# Agregar en las vistas: @vite(['packages/Goloba/GolobaRMA/...'])
```

## 📂 Estructura

```
packages/Goloba/GolobaRMA/
├── src/
│   ├── Config/              # Configuración del menú
│   ├── Controllers/         # Controladores del vendedor
│   ├── DataGrids/          # Tablas con paginación
│   ├── Database/
│   │   └── Migrations/     # Migración campo rma_type
│   ├── Models/             # Modelo RMA extendido
│   ├── Providers/          # Service Provider
│   ├── Resources/
│   │   ├── assets/         # CSS/JS (preparados, no compilados)
│   │   └── views/          # Vistas Blade
│   └── Routes/             # Rutas del vendedor
├── publishable/            # Assets compilados (cuando se activen)
├── package.json            # NPM config (preparado)
├── vite.config.js          # Vite config (preparado)
├── tailwind.config.js      # Tailwind config (preparado)
└── README.md               # Este archivo
```

## 🛣️ Rutas

### Panel Vendedor
- `/vendedor/cuenta/rma` - Listado de RMAs
- `/vendedor/cuenta/rma/view/{id}` - Detalle de RMA

### Panel Cliente
- `/customer/account/rma` - Listado de RMAs
- `/customer/account/rma/create` - Crear nueva RMA (sin campos de dirección)

## 🔧 Desarrollo

### Vistas Sobrescritas
- `shop/customer/rma/create.blade.php` - Formulario sin dirección de recolección

### Rutas de Debug (comentadas)
Las rutas de debug están comentadas en `Routes/seller-routes.php`.
Para activarlas, descomenta las líneas correspondientes.

## 🚀 Próximas Funcionalidades

- [ ] Integración con WOMPI para reembolsos automáticos
- [ ] Sistema PQR/Chat post-venta
- [ ] Migración a object storage (DigitalOcean Spaces)
- [ ] Métricas de vendedor separadas de calificación de producto

## 📝 Notas

- El campo `rma_type` distingue entre 'retracto' (≤5 días hábiles) y 'standard' (>5 días)
- La relación marketplace: rma → rma_items → marketplace_order_items → marketplace_products → sellers
- Los estilos custom están preparados pero NO activados por diseño (enfoque híbrido)
