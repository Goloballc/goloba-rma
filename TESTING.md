# GolobaRMA - Guía de Testing

## ✅ Estado Actual

### Registros Completados
- ✅ Paquete registrado en composer.json
- ✅ ServiceProvider registrado en config/app.php
- ✅ Autoload ejecutado exitosamente
- ✅ Migración ejecutada (campo rma_type agregado)
- ✅ Rutas registradas correctamente
- ✅ Cachés limpiadas

### Rutas Disponibles
```
GET    /vendedor/cuenta/rma                    → goloba.seller.rma.index
GET    /vendedor/cuenta/rma/view/{id}          → goloba.seller.rma.view  
POST   /vendedor/cuenta/rma/change-status      → goloba.seller.rma.change_status
GET    /vendedor/cuenta/rma/get-messages       → goloba.seller.rma.get_messages
POST   /vendedor/cuenta/rma/send-message       → goloba.seller.rma.send_message
```

## 🧪 Plan de Testing

### 1. Testing Básico - Panel de Vendedor

#### A. Acceso al Listado de RMAs
1. Iniciar sesión como vendedor en el marketplace
2. Navegar a: `http://tu-dominio.test/vendedor/cuenta/rma`
3. **Verificar:**
   - ✅ Se carga la página sin errores
   - ✅ Se muestra el DataGrid con columnas: ID, Orden, Cliente, Tipo RMA, Estado, Fecha
   - ✅ Solo aparecen RMAs de productos del vendedor logueado
   - ✅ Los badges de tipo RMA se muestran correctamente (Derecho de Retracto / Estándar)
   - ✅ Los badges de estado tienen colores apropiados

#### B. Ver Detalle de RMA
1. Click en "Ver Detalle" de cualquier RMA
2. **Verificar:**
   - ✅ Se carga la vista detallada sin errores
   - ✅ Se muestra información general completa
   - ✅ Se listan los productos correctamente con imágenes
   - ✅ Se muestra información adicional del cliente
   - ✅ Aparece la sección de conversación

#### C. Cambiar Estado de RMA (Solo si está en Pending)
1. En la vista detallada de una RMA con estado "Pending"
2. Seleccionar "Aceptar" o "Rechazar" en el formulario
3. Opcionalmente agregar un mensaje
4. Click en "Actualizar Estado"
5. **Verificar:**
   - ✅ El estado se actualiza correctamente
   - ✅ Se muestra mensaje de éxito
   - ✅ Se envía email al cliente (revisar logs/correos)
   - ✅ Si se agregó mensaje, aparece en la conversación

#### D. Sistema de Mensajería
1. En la vista detallada, escribir un mensaje
2. Click en "Enviar Mensaje"
3. **Verificar:**
   - ✅ El mensaje se envía sin errores
   - ✅ Aparece en la lista de mensajes
   - ✅ El formulario se limpia después de enviar
   - ✅ Los mensajes se recargan automáticamente

### 2. Testing - Formulario de Cliente (Sin Dirección)

#### A. Crear Nueva RMA como Cliente
1. Iniciar sesión como cliente
2. Ir a "Mis RMAs" → "Crear Nueva RMA"
3. Seleccionar una orden elegible
4. Completar el formulario
5. **Verificar:**
   - ✅ NO aparece el campo "Return Pickup Address"
   - ✅ NO aparece el campo "Return Pickup Time"
   - ✅ El formulario se puede enviar sin estos campos
   - ✅ La RMA se crea correctamente en la base de datos
   - ✅ El vendedor puede ver esta RMA en su panel

### 3. Testing - Cálculo de Tipo RMA

#### A. Verificar Tipo Automático
1. En la base de datos, revisar RMAs recientes
2. **Query de prueba:**
```sql
SELECT 
    id, 
    order_id, 
    rma_type, 
    created_at,
    DATEDIFF(NOW(), (SELECT created_at FROM orders WHERE id = rma.order_id)) as dias_desde_compra
FROM rma 
ORDER BY id DESC 
LIMIT 10;
```
3. **Verificar:**
   - ✅ RMAs creadas ≤5 días hábiles tienen tipo 'retracto'
   - ✅ RMAs creadas >5 días hábiles tienen tipo 'standard'

### 4. Testing de Seguridad

#### A. Validación de Permisos
1. Como vendedor A, intentar acceder a RMA de vendedor B
2. **URL de prueba:** `/vendedor/cuenta/rma/view/{id_de_otro_vendedor}`
3. **Verificar:**
   - ✅ Se muestra error "No tienes permiso"
   - ✅ Redirecciona al listado de RMAs
   - ✅ No se puede cambiar estado de RMAs de otros vendedores

#### B. Intentar Acceso sin Autenticación
1. Cerrar sesión
2. Intentar acceder a `/vendedor/cuenta/rma`
3. **Verificar:**
   - ✅ Redirecciona al login de vendedor
   - ✅ No se puede acceder sin autenticación

### 5. Testing de Integración

#### A. Flujo Completo Cliente → Vendedor
1. **Cliente:**
   - Compra producto del vendedor
   - Espera a que la orden cambie a estado apropiado
   - Crea RMA desde su panel
   - Agrega mensaje e imágenes
2. **Vendedor:**
   - Recibe notificación (si está implementado)
   - Ve la RMA en su listado
   - Abre detalle y revisa
   - Acepta la RMA con mensaje
3. **Cliente:**
   - Recibe email de aceptación
   - Ve actualización en su panel

### 6. Testing de Base de Datos

#### A. Verificar Estructura
```sql
-- Verificar que existe el campo rma_type
DESCRIBE rma;

-- Debería mostrar:
-- rma_type | enum('standard','retracto') | YES | | standard |
```

#### B. Verificar Relaciones
```sql
-- Verificar que RMAs están vinculadas a items del vendedor
SELECT 
    rma.id as rma_id,
    rma.order_id,
    rma.rma_type,
    oi.marketplace_seller_id as vendedor_id,
    oi.product_id
FROM rma
INNER JOIN rma_items ri ON ri.rma_id = rma.id
INNER JOIN order_items oi ON oi.id = ri.order_item_id
LIMIT 10;
```

## 🐛 Problemas Comunes y Soluciones

### Problema 1: Error 404 en rutas de vendedor
**Solución:**
```bash
php artisan route:clear
php artisan cache:clear
```

### Problema 2: Vista no se carga (error de blade)
**Solución:**
```bash
php artisan view:clear
# Revisar logs en storage/logs/laravel.log
```

### Problema 3: No aparecen RMAs en el listado
**Diagnóstico:**
1. Verificar que el vendedor tiene productos asignados
2. Verificar que existen RMAs con items de ese vendedor
3. Revisar query del DataGrid

**Query de diagnóstico:**
```sql
-- Ver RMAs que debería ver el vendedor con ID = X
SELECT DISTINCT rma.*
FROM rma
INNER JOIN rma_items ON rma_items.rma_id = rma.id
INNER JOIN order_items ON order_items.id = rma_items.order_item_id
WHERE order_items.marketplace_seller_id = X; -- Reemplazar X con ID del vendedor
```

### Problema 4: Campos de dirección siguen apareciendo
**Solución:**
1. Verificar que la vista sobrescrita está en la ruta correcta
2. Limpiar caché de vistas: `php artisan view:clear`
3. Verificar ServiceProvider registra las vistas correctamente

## 📋 Checklist Final

Antes de considerar completo el testing:

- [ ] Panel de vendedor carga correctamente
- [ ] Lista de RMAs muestra solo las del vendedor
- [ ] Vista detallada funciona
- [ ] Cambio de estado funciona
- [ ] Mensajería funciona
- [ ] Formulario de cliente no pide dirección
- [ ] Tipo RMA se calcula correctamente
- [ ] Seguridad: no se pueden ver RMAs de otros vendedores
- [ ] Emails se envían correctamente
- [ ] Base de datos tiene estructura correcta

## 🎯 Próximos Pasos Después del Testing

1. **Si todo funciona:**
   - Documentar cualquier comportamiento inesperado
   - Crear manual de usuario para vendedores
   - Considerar implementar notificaciones push

2. **Si hay errores:**
   - Documentar errores encontrados
   - Revisar logs en `storage/logs/laravel.log`
   - Ajustar según sea necesario

3. **Mejoras futuras:**
   - Implementar ACL completo
   - Agregar estadísticas de RMA en dashboard vendedor
   - Implementar cálculo automático en creación de RMA (event listener)
   - Mejorar UI/UX con componentes Vue más interactivos
