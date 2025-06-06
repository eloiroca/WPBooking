<?php
/************************************************************
 * Hook para enviar el cupon de regalo del producto Gift por email
 ************************************************************/
add_action( 'woocommerce_email_order_details', 'enviar_cupon_por_correo_electronico', 10, 4 );
function enviar_cupon_por_correo_electronico($order, $sent_to_admin, $plain_text, $email) {
    if ( $email->id === 'new_order' ) return; // evitar email al admin
    if ( get_post_meta( $order->get_id(), '_wpb_generated_coupon', true ) ) return;
    $producto_cheque_regalo = false;
    $producto_cheque_regalo_nombre = '';
    // Recuperar el producto específico en la orden y obtener su precio
    $descuento_monto = 0;
    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        if ($product) {
            $gift = $item->get_meta('_wpb_gift');
            if ($gift) {
                $descuento_monto = $item->get_total();
                $producto_cheque_regalo = true;
                $producto_cheque_regalo_nombre = $item->get_name();
                break;
            }
        }
    }
    if ($producto_cheque_regalo){
        // Crear el cupón con el valor obtenido
        $coupon_code = 'CUPON_' . uniqid(); // Generar un código de cupón aleatorio
        $coupon_amount = $descuento_monto;
        $coupon = array(
            'post_title' => $coupon_code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon'
        );
        $coupon_id = wp_insert_post( $coupon );

        // El cupon tiene una validez de un año
        $fecha_actual = new DateTime();
        $fecha_validez = $fecha_actual->modify('+1 year');

        update_post_meta( $coupon_id, 'discount_type', 'fixed_cart' );
        update_post_meta( $coupon_id, 'coupon_amount', $coupon_amount );
        update_post_meta( $coupon_id, 'individual_use', 'yes' );
        update_post_meta( $coupon_id, 'usage_limit', 1 );
        update_post_meta( $coupon_id, 'expiry_date', $fecha_validez->format('Y-m-d') );
        update_post_meta( $order->get_id(), '_wpb_generated_coupon', $coupon_code ); // Marca como generado

        // Agregar el cupón a la orden
        // $order->apply_coupon( $coupon_code );
        echo '<p> Gràcies per la compra del xec regal. Quan ja tingueu la data per fer la reserva, entreu a <a href="https://www.lamanreana.com/">lamanreana.com</a> i, utilitzeu el codi de regal. Recordeu que és únic i només per a un sol ús:  <h2><span style="color:black;"><b>' . $coupon_code.'</b>  </span></h2>';

        //if($_SERVER['REMOTE_ADDR'] == '212.170.194.121'){
        try {
            $url_imagen_cupon = generar_imagen_cupon($coupon_code, $fecha_validez, $producto_cheque_regalo_nombre);
        } catch (Exception|Throwable $e) {
            $url_imagen_cupon = DIRECTORI_PLUGIN_WPBOOKING . 'assets/images/xecregal.jpg';
        }

        echo '<img style="display: inline-block; width: 680px;" src="' . $url_imagen_cupon . '" alt="Cupón Personalizado">';

        //}
    }
}

function generar_imagen_cupon($codigo_cupon, $fecha_validez, $producto_cheque_regalo_nombre) {
    // Ruta de la imagen base
    $imagen_base = DIRECTORI_PLUGIN_WPBOOKING . 'assets/images/xecregal.jpg';

    // Crear un nombre único para la imagen
    $nombre_imagen = 'cupon_' . $codigo_cupon . '_' . uniqid() . '.jpg';
    $ruta_imagen_modificada = DIRECTORI_PLUGIN_WPBOOKING . 'assets/images/' . $nombre_imagen;

    // Crear imagen desde el archivo base
    $imagen = imagecreatefromjpeg($imagen_base);

    // Definir los colores
    $color_verde = imagecolorallocate($imagen, 109, 157, 107); // Color verde (#6d9d6b)
    $color_negro = imagecolorallocate($imagen, 0, 0, 0); // Color negro

    // Ruta de la fuente TTF (asegúrate de que exista)
    $fuente = DIRECTORI_PLUGIN_WPBOOKING . 'assets/fonts/baloobhai-bold.ttf';

    // Tamaño del texto
    $tamano_fuente = 30; // Ajusta el tamaño según sea necesario

    // Posición del texto (ajusta según la imagen)
    $x = 1220; // Coordenada X
    $y = 875; // Coordenada Y

    // Agregar el texto a la imagen
    imagettftext($imagen, $tamano_fuente, 0, $x, $y, $color_negro, $fuente, $codigo_cupon);

    // Calcular el año de validez
    $mes_actual = date('n'); // Número del mes actual (1-12)
    $ano_actual = date('Y'); // Año actual
    $ano_validez = ($mes_actual > 6) ? $ano_actual++ : $ano_actual; // Incrementar el año si estamos después de junio

    // Generar el texto de validez
    $tamano_fuente = 40;
    $texto_validez = "VÀLID FINS EL " . $fecha_validez->format('d/m/Y');

    // Posición del segundo texto (validez)
    $x2 = 620; // Coordenada X del segundo texto
    $y2 = 1345; // Coordenada Y del segundo texto (debajo del primer texto)

    // Agregar el texto de validez
    imagettftext($imagen, $tamano_fuente, 0, $x2, $y2, $color_negro, $fuente, $texto_validez);

    // Procesar el texto del producto para eliminar etiquetas HTML y manejar saltos de línea
    $texto_producto = strtoupper($producto_cheque_regalo_nombre); // Convertir a mayúsculas
    $texto_producto = strip_tags($texto_producto, '<br>'); // Permitir solo <br>
    $texto_producto = preg_replace('/<br\s*\/?>/i', "\n", $texto_producto); // Reemplazar <br> y <br /> con saltos de línea

    // Eliminar el símbolo de euro y su entidad HTML
    $texto_producto = str_replace(['€', '&EURO;'], '', $texto_producto);

    // Decodificar entidades HTML y convertir a UTF-8
    $texto_producto = html_entity_decode($texto_producto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texto_producto = mb_convert_encoding($texto_producto, 'UTF-8', 'auto');

    // Dividir el texto en líneas
    $lineas_producto = explode("\n", $texto_producto);

    // Coordenadas iniciales
    $x_producto = 360; // Coordenada X
    $y_producto = 930; // Coordenada Y inicial
    $tamano_fuente_producto = 20; // Tamaño de la fuente
    $espaciado_lineas = 40; // Espaciado entre líneas

    // Agregar cada línea de texto a la imagen, omitiendo la primera
    $contador = 0;
    foreach ($lineas_producto as $linea) {
        $linea = trim($linea); // Eliminar espacios en blanco al inicio y al final
        if (!empty($linea) && $contador > 0) { // Evitar líneas vacías y omitir la primera
            imagettftext($imagen, $tamano_fuente_producto, 0, $x_producto, $y_producto, $color_negro, $fuente, $linea);
            $y_producto += $espaciado_lineas; // Mover la posición Y para la siguiente línea
        }
        $contador++;
    }

    // Guardar la imagen modificada
    imagejpeg($imagen, $ruta_imagen_modificada);

    // Liberar memoria
    imagedestroy($imagen);

    // Devolver la URL de la imagen generada
    return URL_PLUGIN_WPBOOKING . 'assets/images/' . $nombre_imagen;
}
/************************************************************
 * Hook para mostrar la columna Tickets y Cupones Generados
 ************************************************************/
add_action('admin_init', 'wpbooking_register_woocommerce_hooks');
function wpbooking_register_woocommerce_hooks() {
    add_filter('manage_edit-shop_order_columns', 'cw_add_order_data_reserva_column_header');
    add_action('manage_shop_order_posts_custom_column', 'cw_add_order_data_reserva_column_content');
}

function cw_add_order_data_reserva_column_header($columns) {
    $new_columns = array();
    foreach ($columns as $column_name => $column_info) {
        $new_columns[$column_name] = $column_info;
        if ('order_date' === $column_name) { // Asegúrate de que 'order_date' es el nombre correcto
            $new_columns['order_products_info'] = __wpb('Tickets');
        }
        if ('order_date' === $column_name) { // Cambiar 'order_date' por un nombre válido si es necesario
            $new_columns['order_wpb_coupons'] = __wpb('Coupon');
        }
    }
    return $new_columns;
}


function cw_add_order_data_reserva_column_content( $column ) {
    global $post;

    if ( 'order_products_info' === $column ) {
        $order = wc_get_order( $post->ID );
        $items = $order->get_items();

        foreach ( $items as $item ) {
            // Obtener el nombre del producto
            $product_name = $item->get_name();
            // Mostramos el nombre y <br>
            echo esc_html( strip_tags($product_name) ) . "<br>";
        }
    }
    if ( 'order_wpb_coupons' === $column ) {
        $order = wc_get_order( $post->ID );
        $coupons = $order->get_meta('_wpb_generated_coupon', true);

        if ( ! empty( $coupons ) ) {
            $coupons = is_array($coupons) ? $coupons : array($coupons);
            foreach ( $coupons as $coupon ) {
                // Mostramos el cupón
                echo esc_html( strip_tags($coupon) ) . "<br>";
            }
        } else {
            echo __( 'No cupons', 'wpbooking' );
        }
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'cw_add_order_data_reserva_column_content' );
/************************************************************
 * Hook para poder buscar por el cupon generado
 ************************************************************/
add_action('pre_get_posts', 'filter_orders_by_coupon');
function filter_orders_by_coupon($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'shop_order') {
        $search_term = $query->get('s');
        if (!empty($search_term)) {
            $meta_query = array(
                array(
                    'key'     => '_wpb_generated_coupon',
                    'value'   => $search_term,
                    'compare' => 'LIKE',
                ),
            );
            $query->set('meta_query', $meta_query);
            $query->set('s', ''); // Clear the default search to avoid conflicts
        }
    }
}