<?php
add_action('wp_ajax_wpbooking_add_to_cart', 'wpbooking_add_to_cart_handler');
add_action('wp_ajax_nopriv_wpbooking_add_to_cart', 'wpbooking_add_to_cart_handler');

function wpbooking_add_to_cart_handler() {
    if (!isset($_POST['event_id'], $_POST['personas_json'], $_POST['services_json'])) {
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

    foreach ($personas as $id => $qty) {
        $precio = floatval(get_post_meta($id, '_price', true));
        $total_price += $precio * intval($qty);
    }

    foreach ($services as $id => $qty) {
        $precio = floatval(get_post_meta($id, '_price', true));
        $total_price += $precio * intval($qty);
    }

    // Crear producto ficticio si no existe
    $product_id = wpbooking_get_or_create_fake_product();

    // Añadir al carrito
    WC()->cart->add_to_cart($product_id, 1, 0, [], [
        'event_id' => $event_id,
        'personas' => $personas,
        'services' => $services,
        'custom_price' => $total_price,
        'custom_name' => get_the_title($event_id),
    ]);

    wp_send_json_success(['message' => __wpb('Event added to cart successfully')]);
}


function wpbooking_get_or_create_fake_product() {
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

