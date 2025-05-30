<?php
add_action('wp_ajax_wpbooking_add_to_cart', 'wpbooking_add_to_cart_handler');
add_action('wp_ajax_nopriv_wpbooking_add_to_cart', 'wpbooking_add_to_cart_handler');

function wpbooking_add_to_cart_handler() {
    if (!isset($_POST['event_id'], $_POST['personas_json'], $_POST['services_json'], $_POST['date'])) {
        wp_send_json_error(['message' => __wpb('Invalid request')]);
    }

    $event_id = intval($_POST['event_id']);
    $personas = json_decode(stripslashes($_POST['personas_json']), true);
    $services = json_decode(stripslashes($_POST['services_json']), true);

    if (!is_array($personas) || !is_array($services)) {
        wp_send_json_error(['message' => __wpb('You must select at least one person or service')]);
    }

    // Total precio
    $total_price = 0;
    $date = $_POST['date'];
    $detalle = [];
    // Obtener las opciones de configuración
    $options = get_option('wpbooking_options', []);
    $multiply_price = !empty($options['multiply_price_qty']);
    $multiply_service_price = !empty($options['multiply_service_price_qty']);

    $detalle[] = '<b>' . __wpb('Date') . '</b>: ' . esc_html($date);
    $detalle[] = '<b>' . __wpb('Hour start') . '</b>: ' . get_post_meta($event_id, '_hour_start', true);
    $detalle[] = '<b>' . __wpb('Hour end') . '</b>: ' . get_post_meta($event_id, '_hour_end', true);

    foreach ($personas as $id => $qty) {
        $qty = intval($qty);
        if ($qty < 1) continue;
        $precio = floatval(get_post_meta($id, '_price', true));
        $line_total = $multiply_price ? ($precio * $qty) : $precio;
        $total_price += $line_total;
        $detalle[] = "$qty x <b>" . get_the_title($id) . " (" . wc_price($precio) . ")</b>";
    }

    foreach ($services as $id => $qty) {
        $qty = intval($qty);
        if ($qty < 1) continue;
        $precio = floatval(get_post_meta($id, '_price', true));
        $line_total = $multiply_service_price ? ($precio * $qty) : $precio;
        $total_price += $line_total;
        $detalle[] = "$qty x <b>" . get_the_title($id) . " (" . wc_price($precio) . ")</b>";
    }

    $product_id = wpbooking_get_or_create_fake_product();

    WC()->cart->add_to_cart($product_id, 1, 0, [], [
        'event_id' => $event_id,
        'personas' => $personas,
        'services' => $services,
        'custom_price' => $total_price,
        'custom_name' => get_the_title($event_id) . ':<br> ' . implode('<br> ', $detalle),
    ]);

    wp_send_json_success(['message' => __wpb('Event added to cart successfully')]);
}

add_action('wp_ajax_wpbooking_add_to_cart_gift', 'wpbooking_add_to_cart_gift_handler');
add_action('wp_ajax_nopriv_wpbooking_add_to_cart_gift', 'wpbooking_add_to_cart_gift_handler');

function wpbooking_add_to_cart_gift_handler() {
    if (!isset($_POST['personas_json'])) {
        wp_send_json_error(['message' => __wpb('Invalid request')]);
    }

    $personas = json_decode(stripslashes($_POST['personas_json']), true);

    if (!is_array($personas)) {
        wp_send_json_error(['message' => __wpb('You must select at least one person or service')]);
    }

    // Total precio
    $total_price = 0;
    $detalle = [];
    // Obtener las opciones de configuración
    $options = get_option('wpbooking_options', []);
    $multiply_price = !empty($options['multiply_price_qty']);

    $event_groups = [];
    // Agrupar personas por evento
    foreach ($personas as $persona) {
        $event_name = esc_html($persona['event_name']);
        $person_name = esc_html($persona['person_name']);
        $id = intval($persona['id']);
        $qty = intval($persona['qty']);

        if ($qty < 1) continue;

        $precio = floatval(get_post_meta($id, '_price', true));
        $line_total = $multiply_price ? ($precio * $qty) : $precio;
        $total_price += $line_total;

        if (!isset($event_groups[$event_name])) {
            $event_groups[$event_name] = [];
        }

        $event_groups[$event_name][] = "$qty x <b>" . $person_name . " (" . wc_price($precio) . ") </b>";
    }

    // Formatear el detalle
    foreach ($event_groups as $event_name => $details) {
        $detalle[] = "<small><i>$event_name</i>:</small><br>" . implode('<br>', $details);
    }

    $product_id = wpbooking_get_or_create_fake_product(true);

    WC()->cart->add_to_cart($product_id, 1, 0, [], [
        'event_id' => 0, // No hay evento asociado
        'gift' => true,
        'personas' => $personas,
        'custom_price' => $total_price,
        'custom_name' => __wpb('Buy Gift Voucher') . ':<br> ' . implode('<br> ', $detalle),
    ]);

    wp_send_json_success(['message' => __wpb('Event added to cart successfully')]);
}

function wpbooking_get_or_create_fake_product($gift=false) {
    $slug = 'wpbooking-producto-ficticio';
    $existing = get_page_by_path($slug, OBJECT, 'product');
    if ($existing) return $existing->ID;

    $post_id = wp_insert_post([
        'post_title' => 'Reserva WPBooking',
        'post_name' => $slug,
        'post_status' => 'publish',
        'post_type' => 'product',
    ]);

    update_post_meta($post_id, '_regular_price', '0');
    update_post_meta($post_id, '_price', '0');
    update_post_meta($post_id, '_virtual', 'yes');
    update_post_meta($post_id, '_sold_individually', 'yes');

    return $post_id;
}


//✅ Filtro para mostrar nombre y precio personalizado
add_filter('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $item) {
        if (!empty($item['custom_price'])) {
            $item['data']->set_price($item['custom_price']);
        }
        if (!empty($item['custom_name'])) {
            $item['data']->set_name($item['custom_name']);
        }
    }
}, 20, 1);




