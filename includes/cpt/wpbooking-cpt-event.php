<?php
// Registrar CPT
add_action('init', function () {
    register_post_type('wpbooking_event', array(
        'labels' => array(
            'name' => __wpb('Events'),
            'singular_name' => __wpb('Event'),
            'add_new' => __wpb('Add new'),
            'add_new_item' => __wpb('Add new event'),
            'edit_item' => __wpb('Edit event'),
            'new_item' => __wpb('New event'),
            'view_item' => __wpb('View event'),
            'search_items' => __wpb('Search events'),
            'not_found' => __wpb('No events found'),
            //'menu_name' => 'Eventos'
        ),
        'public' => true,
        //'show_ui' => true,
        'show_in_menu' => false,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar',
        'rewrite' => true,
        'has_archive' => true,
    ));
});

// Registrar Metaboxes
add_action('add_meta_boxes', function () {
    add_meta_box('wpbooking_event_meta',
     __wpb('Event settings'),
  'wpbooking_event_meta_callback',
   'wpbooking_event',
    'side'
     );
    // Titulo del evento
    add_meta_box(
        'wpbooking_event_title_metabox',
        __wpb('Calendar title'),
        'wpbooking_event_title_metabox_callback',
        'wpbooking_event',
        'side'
    );
    //Color del evento
    add_meta_box(
        'wpbooking_event_color_metabox',
        __wpb('Event color'),
        'wpbooking_event_color_metabox_callback',
        'wpbooking_event',
        'side'
    );
    //Color del texto del evento
    add_meta_box(
        'wpbooking_event_text_color_metabox',
        __wpb('Event text color'),
        'wpbooking_event_text_color_metabox_callback',
        'wpbooking_event',
        'side'
    );
});

function wpbooking_event_meta_callback($post) {
    $price = get_post_meta($post->ID, '_price', true);
    $hour_start = get_post_meta($post->ID, '_hour_start', true);
    $hour_end = get_post_meta($post->ID, '_hour_end', true);
    $enabled = get_post_meta($post->ID, '_enabled', true);
    $can_reserve = get_post_meta($post->ID, '_can_reserve', true);
    ?>
    <label><?php echo __wpb('Price'); ?>:
    <input type="text" name="price" value="<?php echo esc_attr($price); ?>" />
    </label><br><br>
    <label><?php echo __wpb('Hour start'); ?>:
        <input type="time" name="hour_start" value="<?php echo esc_attr($hour_start); ?>" />
    </label><br><br>
    <label><?php echo __wpb('Hour end'); ?>:
        <input type="time" name="hour_end" value="<?php echo esc_attr($hour_end); ?>" />
    </label><br><br>
    <label><?php echo __wpb('Enabled'); ?>:
        <input type="checkbox" name="enabled" value="1" <?= checked($enabled, '1', false) ?> />
    </label><br><br>
    <label><?php echo __wpb('Can reserve'); ?>:
        <input type="checkbox" name="can_reserve" value="1" <?= checked($can_reserve, '1', false) ?> />


    <?php
}

function wpbooking_event_title_metabox_callback($post) {
    $calendar_title = get_post_meta($post->ID, '_calendar_title', true);
    ?>
    <input type="text" name="calendar_title" id="_calendar_title" value="<?php echo esc_attr($calendar_title); ?>" style="width: 100%;">
    <?php
}

function wpbooking_event_color_metabox_callback($post) {
    $color = get_post_meta($post->ID, '_color', true) ?: '#ff0000';
    ?>
    <input type="text" name="color" id="_color" value="<?php echo esc_attr($color); ?>" class="wp-color-picker-field" data-default-color="#ff0000">
    <script>
        jQuery(document).ready(function($) {
            $('#_color').wpColorPicker();
        });
    </script>
    <?php
}

function wpbooking_event_text_color_metabox_callback($post) {
    $text_color = get_post_meta($post->ID, '_text_color', true) ?: '#000000';
    ?>
    <input type="text" name="text_color" id="_text_color" value="<?php echo esc_attr($text_color); ?>" class="wp-color-picker-field" data-default-color="#000000">
    <script>
        jQuery(document).ready(function($) {
            $('#_text_color').wpColorPicker();
        });
    </script>
    <?php
}

// Guardar datos del evento
add_action('save_post_wpbooking_event', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, '_calendar_title', sanitize_text_field($_POST['calendar_title'] ?? ''));
    update_post_meta($post_id, '_color', sanitize_hex_color($_POST['color'] ?? ''));
    update_post_meta($post_id, '_text_color', sanitize_hex_color($_POST['text_color'] ?? ''));
    update_post_meta($post_id, '_price', sanitize_text_field($_POST['price'] ?? ''));
    update_post_meta($post_id, '_hour_start', sanitize_text_field($_POST['hour_start'] ?? ''));
    update_post_meta($post_id, '_hour_end', sanitize_text_field($_POST['hour_end'] ?? ''));
    update_post_meta($post_id, '_enabled', isset($_POST['enabled']) ? '1' : '0');
    update_post_meta($post_id, '_can_reserve', isset($_POST['can_reserve']) ? '1' : '0');
});

// Añadir columnas personalizadas
add_filter('manage_wpbooking_event_posts_columns', function($columns) {
    $columns['calendar_title'] = __wpb('Calendar title');
    $columns['event_color'] = __wpb('Event color');
    $columns['enabled'] = __wpb('Enabled');
    return $columns;
});

// Mostrar los valores de las columnas personalizadas
add_action('manage_wpbooking_event_posts_custom_column', function($column, $post_id) {
    if ($column === 'calendar_title') {
        echo esc_html(get_post_meta($post_id, '_calendar_title', true));
    }

    if ($column === 'event_color') {
        $color = get_post_meta($post_id, '_color', true);
        echo '<span style="display:inline-block;width:20px;height:20px;background:' . esc_attr($color) . ';border:1px solid #ccc;"></span>';
    }

    if ($column === 'enabled') {
        $enabled = get_post_meta($post_id, '_enabled', true);
        echo $enabled ? __wpb('Yes') : __wpb('No');
    }
}, 10, 2);

// Pagina individual
add_filter('single_template', function ($template) {
    global $post;
    if ($post->post_type === 'wpbooking_event') {
        $custom = DIRECTORI_PLUGIN_WPBOOKING . 'templates/views/event/single-wpbooking-event.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
});
