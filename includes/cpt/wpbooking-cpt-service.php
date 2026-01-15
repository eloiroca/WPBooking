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
    // Service Options
    add_meta_box(
        'wpbooking_service_options_metabox',
        __wpb('Service Options'),
        'wpbooking_service_options_metabox_callback',
        'wpbooking_service',
        'side'
    );
    // Display Settings
    add_meta_box(
        'wpbooking_service_display_metabox',
        __wpb('Display Settings'),
        'wpbooking_service_display_metabox_callback',
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
        if (is_wp_error($lang) || !$lang || !isset($lang['language_code']) || $lang['language_code'] !== $slave_lang) continue;

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

// Metabox service options
function wpbooking_service_options_metabox_callback($post) {
    if (wpbooking_is_translating_wpml($post->ID, 'wpbooking_service')) {
        echo '<p style="color: #d63638; font-weight: bold;">' . esc_html(__wpb('Editing translation. These fields are managed from the primary language.')) . '</p>';
        return;
    }
    
    $options = get_post_meta($post->ID, '_service_options', true);
    if (!is_array($options)) {
        $options = [];
    }
    
    wp_nonce_field('wpbooking_service_options_nonce', 'wpbooking_service_options_nonce');
    ?>
    <div id="service-options-container">
        <?php foreach ($options as $index => $option): ?>
            <div class="service-option-row" data-index="<?php echo $index; ?>">
                <label><?php echo __wpb('Description'); ?>:</label>
                <input type="text" name="service_options[<?php echo $index; ?>][description]" value="<?php echo esc_attr($option['description'] ?? ''); ?>" style="width:100%; margin-bottom: 5px;" placeholder="<?php echo __wpb('e.g. Premium option'); ?>">
                
                <label><?php echo __wpb('Price'); ?>:</label>
                <input type="number" step="0.01" name="service_options[<?php echo $index; ?>][price]" value="<?php echo esc_attr($option['price'] ?? ''); ?>" style="width:100%; margin-bottom: 10px;" placeholder="0.00">
                
                <button type="button" class="button remove-option" onclick="removeServiceOption(this)">
                    <?php echo __wpb('Remove'); ?>
                </button>
                <hr style="margin: 10px 0;">
            </div>
        <?php endforeach; ?>
    </div>
    
    <button type="button" class="button button-secondary" onclick="addServiceOption()">
        <?php echo __wpb('Add Option'); ?>
    </button>
    
    <script>
        let optionIndex = <?php echo count($options); ?>;
        
        function addServiceOption() {
            const container = document.getElementById('service-options-container');
            const row = document.createElement('div');
            row.className = 'service-option-row';
            row.dataset.index = optionIndex;
            row.innerHTML = `
                <label><?php echo __wpb('Description'); ?>:</label>
                <input type="text" name="service_options[${optionIndex}][description]" style="width:100%; margin-bottom: 5px;" placeholder="<?php echo __wpb('e.g. Premium option'); ?>">
                
                <label><?php echo __wpb('Price'); ?>:</label>
                <input type="number" step="0.01" name="service_options[${optionIndex}][price]" style="width:100%; margin-bottom: 10px;" placeholder="0.00">
                
                <button type="button" class="button remove-option" onclick="removeServiceOption(this)">
                    <?php echo __wpb('Remove'); ?>
                </button>
                <hr style="margin: 10px 0;">
            `;
            container.appendChild(row);
            optionIndex++;
        }
        
        function removeServiceOption(button) {
            button.closest('.service-option-row').remove();
        }
    </script>
    
    <p><small><?php echo __wpb('Options with empty description will not be displayed. Price 0 will show as "Free"'); ?></small></p>
    <?php
}

// Metabox display settings
function wpbooking_service_display_metabox_callback($post) {
    if (wpbooking_is_translating_wpml($post->ID, 'wpbooking_service')) {
        echo '<p style="color: #d63638; font-weight: bold;">' . esc_html(__wpb('Editing translation. These fields are managed from the primary language.')) . '</p>';
        return;
    }
    
    $show_price = get_post_meta($post->ID, '_show_price', true);
    $show_quantity = get_post_meta($post->ID, '_show_quantity', true);
    
    // Default values
    if ($show_price === '') $show_price = '1';
    if ($show_quantity === '') $show_quantity = '1';
    ?>
    
    <label>
        <input type="checkbox" name="show_price" value="1" <?php checked($show_price, '1'); ?>>
        <?php echo __wpb('Show service price'); ?>
    </label><br><br>
    
    <label>
        <input type="checkbox" name="show_quantity" value="1" <?php checked($show_quantity, '1'); ?>>
        <?php echo __wpb('Show quantity selector'); ?>
    </label>
    
    <p><small><?php echo __wpb('Uncheck to hide price or quantity controls'); ?></small></p>
    <?php
}

// Guardar metadatos
add_action('save_post_wpbooking_service', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Verificar nonce para las opciones
    if (!isset($_POST['wpbooking_service_options_nonce']) || 
        !wp_verify_nonce($_POST['wpbooking_service_options_nonce'], 'wpbooking_service_options_nonce')) {
        return;
    }

    update_post_meta($post_id, '_price', sanitize_text_field($_POST['price'] ?? ''));
    update_post_meta($post_id, '_min', sanitize_text_field($_POST['min'] ?? ''));
    update_post_meta($post_id, '_max', sanitize_text_field($_POST['max'] ?? ''));
    
    // Guardar opciones del servicio
    $service_options = [];
    if (isset($_POST['service_options']) && is_array($_POST['service_options'])) {
        foreach ($_POST['service_options'] as $option) {
            if (!empty($option['description']) || !empty($option['price'])) {
                $service_options[] = [
                    'description' => sanitize_text_field($option['description'] ?? ''),
                    'price' => sanitize_text_field($option['price'] ?? '0')
                ];
            }
        }
    }
    update_post_meta($post_id, '_service_options', $service_options);
    
    // Guardar configuración de visualización
    update_post_meta($post_id, '_show_price', isset($_POST['show_price']) ? '1' : '0');
    update_post_meta($post_id, '_show_quantity', isset($_POST['show_quantity']) ? '1' : '0');

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

