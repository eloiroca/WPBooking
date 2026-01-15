# Cambios realizados para añadir Radio Button a los Servicios

## Resumen
Se ha añadido la funcionalidad para incluir un radio button con precio adicional en los servicios del plugin WPBooking.

## Archivos modificados:

### 1. `/includes/cpt/wpbooking-cpt-service.php`
- **Añadido nuevo metabox**: `wpbooking_service_radio_metabox` para configurar el radio button
- **Nueva función**: `wpbooking_service_radio_metabox_callback()` para mostrar los campos:
  - `_radio_label`: Etiqueta del radio button
  - `_radio_price`: Precio adicional del radio button
- **Actualizada función de guardado** para incluir los nuevos campos

### 2. `/templates/views/event/single-wpbooking-event.php`
- **Modificada la visualización de servicios** para incluir:
  - Descripción del servicio (usando `post_content`)
  - Radio button con label y precio (solo si precio > 0)
- **Actualizado el JavaScript** para:
  - Calcular el precio adicional del radio button
  - Recoger los datos del radio button en el formulario
  - Incluir los datos en la llamada AJAX

### 3. `/includes/wpbooking-reservations.php`
- **Actualizado** `wpbooking_add_to_cart_handler()` para:
  - Recibir los datos de los radio buttons (`service_radios_json`)
  - Calcular el precio adicional del radio button
  - Incluir la información del radio button en el detalle de la compra
  - Guardar los datos del radio button en el carrito

### 4. `/assets/css/wpbooking.css`
- **Añadidos estilos CSS** para:
  - Contenedor de servicios con bordes y padding
  - Header del servicio con layout flex
  - Descripción del servicio
  - Radio button con estilo destacado
  - Efectos hover

### 5. Archivos de idiomas
- **`/lang/es.php`**: Añadidas traducciones en español
- **`/lang/ca.php`**: Añadidas traducciones en catalán  
- **`/lang/fr.php`**: Añadidas traducciones en francés

## Nuevas traducciones añadidas:
- `'Radio Button Option'` -> Configuración en diferentes idiomas
- `'Radio Label'` -> Campo para la etiqueta
- `'Radio Price'` -> Campo para el precio
- `'If price is 0, the radio button will not be displayed'` -> Descripción del comportamiento
- `'e.g. Premium option'` -> Ejemplo de uso

## Funcionalidad implementada:

1. **En el admin**:
   - Nuevo metabox "Radio Button Option" en la edición de servicios
   - Campos para configurar etiqueta y precio del radio button
   - Si el precio es 0, el radio button no se muestra

2. **En el frontend**:
   - Se muestra la descripción del servicio debajo del título
   - Radio button aparece solo si tiene precio > 0
   - El precio del radio button se suma al precio total
   - El radio button solo se cuenta si el servicio tiene cantidad > 0

3. **En el proceso de compra**:
   - Los datos del radio button se incluyen en el carrito
   - El precio adicional se calcula correctamente
   - Se muestra en el detalle de la compra con formato diferenciado

## Notas importantes:
- El radio button solo se muestra si el precio es mayor que 0
- El precio del radio button solo se suma si el servicio tiene cantidad > 0 y el radio está seleccionado
- Se mantiene compatibilidad con la funcionalidad existente
- Los estilos son responsive y siguen el diseño del tema