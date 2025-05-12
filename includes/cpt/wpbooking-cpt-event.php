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
        //'show_in_menu' => 'wpbooking', // Aquí lo enlazas como submenú
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar',
    ));
});

// Registrar Metaboxes
add_action('add_meta_boxes', function () {
    add_meta_box('wpbooking_event_meta', 'Datos del Evento', 'wpbooking_event_meta_callback', 'wpbooking_event', 'normal', 'default');
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
    // Dias excepcionales
    add_meta_box(
        'wpbooking_event_exceptions_metabox',
        __('Fechas Excepcionales', 'wpbooking'),
        'wpbooking_event_exceptions_metabox_callback',
        'wpbooking_event',
        'side'
    );
});

function wpbooking_event_meta_callback($post) {
    $start_date = get_post_meta($post->ID, '_start_date', true);
    $end_date = get_post_meta($post->ID, '_end_date', true);
    $enabled = get_post_meta($post->ID, '_enabled', true);
    ?>
    <label><?php echo __wpb('Start date'); ?>: <input type="date" name="start_date" value="<?= esc_attr($start_date) ?>" /></label><br><br>
    <label><?php echo __wpb('End date'); ?>: <input type="date" name="end_date" value="<?= esc_attr($end_date) ?>" /></label><br><br>
    <label><?php echo __wpb('Enabled'); ?>:
        <input type="checkbox" name="enabled" value="1" <?= checked($enabled, '1', false) ?> />
    </label><br><br>


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

function wpbooking_event_exceptions_metabox_callback($post) {
    $exceptions = get_post_meta($post->ID, '_exceptions', true);
    $exceptions = is_array($exceptions) ? $exceptions : [];

    ?>
    <div id="wpbooking-exceptions-wrap">
        <input type="date" id="wpbooking-exception-date">
        <button type="button" class="button" id="wpbooking-add-exception"><?php _e('Añadir fecha'); ?></button>
        <ul id="wpbooking-exceptions-list">
            <?php foreach ($exceptions as $date): ?>
                <li data-date="<?= esc_attr($date) ?>">
                    <?= esc_html($date) ?>
                    <span class="remove-exception" style="cursor:pointer; color:red; margin-left:10px;">&times;</span>
                </li>
            <?php endforeach; ?>
        </ul>
        <input type="hidden" name="_exceptions" id="wpbooking-exceptions-field" value="<?= esc_attr(json_encode($exceptions)) ?>">
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('wpbooking-exception-date');
            const addBtn = document.getElementById('wpbooking-add-exception');
            const list = document.getElementById('wpbooking-exceptions-list');
            const field = document.getElementById('wpbooking-exceptions-field');

            const updateField = () => {
                const dates = Array.from(list.querySelectorAll('li')).map(li => li.dataset.date);
                field.value = JSON.stringify(dates);
            };

            addBtn.addEventListener('click', () => {
                const date = input.value;
                if (!date || list.querySelector(`li[data-date="${date}"]`)) return;

                const li = document.createElement('li');
                li.dataset.date = date;
                li.innerHTML = `${date} <span class="remove-exception" style="cursor:pointer; color:red; margin-left:10px;">&times;</span>`;
                list.appendChild(li);
                updateField();
            });

            list.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-exception')) {
                    e.target.closest('li').remove();
                    updateField();
                }
            });
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
    update_post_meta($post_id, '_start_date', sanitize_text_field($_POST['start_date'] ?? ''));
    update_post_meta($post_id, '_end_date', sanitize_text_field($_POST['end_date'] ?? ''));
    update_post_meta($post_id, '_enabled', isset($_POST['enabled']) ? '1' : '0');

    if (isset($_POST['_exceptions'])) {
        $data = json_decode(stripslashes($_POST['_exceptions']), true);
        update_post_meta($post_id, '_exceptions', array_filter($data));
    }
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