<?php
// Registrar CPT Service
add_action('init', function () {
    register_post_type('wpbooking_service', array(
        'labels' => array(
            'name' => __wpb('Services'),
            'singular_name' => __wpb('Service'),
            'add_new' => __wpb('Add new'),
            'add_new_item' => __wpb('Add new service'),
            'edit_item' => __wpb('Edit service'),
            'new_item' => __wpb('New service'),
            'view_item' => __wpb('View service'),
            'search_items' => __wpb('Search services'),
            'not_found' => __wpb('No services found'),
        ),
        'public' => true,
        //'show_ui' => true,
        'show_in_menu' => false,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar',
        'rewrite' => false,
        'has_archive' => false,
    ));
});

// Metaboxes para Service
add_action('add_meta_boxes', function () {
    add_meta_box(
        'wpbooking_service_events_metabox',
        __wpb('Assign to events'),
        'wpbooking_service_events_metabox_callback',
        'wpbooking_service',
        'side'
    );
    // Precio del servicio
    add_meta_box(
        'wpbooking_service_price_metabox',
        __wpb('Price'),
        'wpbooking_service_price_metabox_callback',
        'wpbooking_service',
        'side'
    );
    // Cantidades
    add_meta_box(
        'wpbooking_service_qty_metabox',
        __wpb('Quantities'),
        'wpbooking_service_qty_metabox_callback',
        'wpbooking_service',
        'side'
    );
});

// Metabox eventos asignados
function wpbooking_service_events_metabox_callback($post) {
    if (wpbooking_is_translating_wpml($post->ID, 'wpbooking_service')) {
        echo '<p style="color: #d63638; font-weight: bold;">' . esc_html(__wpb('Editing translation. These fields are managed from the primary language.')) . '</p>';
        return;
    }

    $selected_events = get_post_meta($post->ID, '_assigned_events', true) ?: [];
    $options = get_option('wpbooking_options', []);
    $slave_lang = $options['slave_language'] ?? null;

    $events = get_posts([
        'post_type' => 'wpbooking_event',
        'numberposts' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    echo '<div style="max-height: 150px; overflow-y: auto;">';
    foreach ($events as $event) {
        $lang = apply_filters('wpml_post_language_details', null, $event->ID);
        if (!$lang || $lang['language_code'] !== $slave_lang) continue;

        $checked = in_array($event->ID, $selected_events) ? 'checked' : '';
        echo '<label style="display:block;margin-bottom:4px;">';
        echo '<input type="checkbox" name="assigned_events[]" value="' . esc_attr($event->ID) . '" ' . $checked . '> ';
        echo esc_html($event->post_title);
        echo '</label>';
    }
    echo '</div>';
}

// Metabox precio
function wpbooking_service_price_metabox_callback($post) {
    if (wpbooking_is_translating_wpml($post->ID, 'wpbooking_service')) {
        echo '<p style="color: #d63638; font-weight: bold;">' . esc_html(__wpb('Editing translation. These fields are managed from the primary language.')) . '</p>';
        return;
    }
    $price = get_post_meta($post->ID, '_price', true);
    ?>
    <input type="number" step="0.01" name="price" value="<?= esc_attr($price); ?>" style="width:100%;">
    <?php
}

// Metabox cantidades
function wpbooking_service_qty_metabox_callback($post) {
    if (wpbooking_is_translating_wpml($post->ID, 'wpbooking_service')) {
        echo '<p style="color: #d63638; font-weight: bold;">' . esc_html(__wpb('Editing translation. These fields are managed from the primary language.')) . '</p>';
        return;
    }
    $min = get_post_meta($post->ID, '_min', true);
    $max = get_post_meta($post->ID, '_max', true);
    ?>
    <label><?php echo __wpb('Min'); ?>:</label>
    <input type="number" name="min" value="<?= esc_attr($min); ?>" style="width:100%;"><br><br>
    <label><?php echo __wpb('Max'); ?>:</label>
    <input type="number" name="max" value="<?= esc_attr($max); ?>" style="width:100%;">
    <?php
}

// Guardar metadatos
add_action('save_post_wpbooking_service', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, '_price', sanitize_text_field($_POST['price'] ?? ''));
    update_post_meta($post_id, '_min', sanitize_text_field($_POST['min'] ?? ''));
    update_post_meta($post_id, '_max', sanitize_text_field($_POST['max'] ?? ''));

    $assigned = isset($_POST['assigned_events']) ? array_map('intval', $_POST['assigned_events']) : [];
    update_post_meta($post_id, '_assigned_events', $assigned);
});

// Añadir columna personalizada de precio
add_filter('manage_wpbooking_service_posts_columns', function ($columns) {
    $columns['price'] = __wpb('Price');
    return $columns;
});

// Contenido de la columna Price
add_action('manage_wpbooking_service_posts_custom_column', function ($column, $post_id) {
    if ($column === 'price') {
        echo esc_html(get_post_meta($post_id, '_price', true));
    }
}, 10, 2);

