<?php
/**
 * Plugin Name: WPBooking
 * Description: Plugin de reservas con calendario visual integrado con WooCommerce. Permite configurar disponibilidades, gestionar personas y servicios, y realizar pagos directamente desde el calendario.
 * Version: 1.0
 * Author: Eloi
 */

define("DIRECTORI_PLUGIN_WPBOOKING" , plugin_dir_path( __FILE__ ));
define("URL_PLUGIN_WPBOOKING" , plugin_dir_url( __FILE__ ));
//Language Definition
add_action('plugins_loaded', function() {
    if (!defined('LANG_WPBOOKING')) {
        if (defined('ICL_LANGUAGE_CODE')) {
            define('LANG_WPBOOKING', ICL_LANGUAGE_CODE);
        } elseif (function_exists('pll_current_language')) {
            define('LANG_WPBOOKING', pll_current_language());
        } else {
            define('LANG_WPBOOKING', get_locale());
        }
    }
});
define('START_CALENDAR_WPBOOKING', date('Y-m-01'));
define('END_CALENDAR_WPBOOKING', date('Y-m-01', strtotime('+3 months')));

/************************************************************
 * Funciones de traducción
 ************************************************************/
if (!function_exists('__wpb')) {
    function wpbooking_translations_array() {
        static $translations = null;

        if ($translations !== null) return $translations;

        $lang = defined('LANG_WPBOOKING') ? LANG_WPBOOKING : 'es';
        $path = DIRECTORI_PLUGIN_WPBOOKING . "lang/$lang.php";

        if (!file_exists($path)) {
            $path = DIRECTORI_PLUGIN_WPBOOKING . "lang/es.php";
        }

        $translations = include $path;
        return $translations;
    }

    function __wpb($key) {
        $translations = wpbooking_translations_array();
        return isset($translations[$key]) ? $translations[$key] : $key;
    }
}


//include_once(DIRECTORI_PLUGIN_WPBOOKING . 'class/WPBOOKING.php');

/************************************************************
 * Registrar estilos y scripts
 ************************************************************/
function registrar_estilos_scripts($hook = '') {
    $versio = "1.0.0";

    // Solo cargar en admin si es WPBooking
    if (is_admin() && strpos($hook, 'wpbooking') === false) return;

    // Modal
    wp_enqueue_style( 'style-wpbooking-micromodal', URL_PLUGIN_WPBOOKING. 'assets/css/micromodal/micromodal.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking-micromodal', URL_PLUGIN_WPBOOKING . 'assets/js/micromodal/micromodal.js', array(), $versio );

    // Calendar
    wp_enqueue_script( 'script-wpbooking-calendar', URL_PLUGIN_WPBOOKING . 'assets/js/fullcalendar/index.global.min.js', array(), $versio );
    wp_enqueue_script( 'script-wpbooking-locale-calendar', URL_PLUGIN_WPBOOKING . 'assets/js/fullcalendar/locales-all.global.min.js', array('script-wpbooking-calendar'), $versio );

    // WPBooking
    wp_enqueue_style( 'style-wpbooking', URL_PLUGIN_WPBOOKING. 'assets/css/wpbooking.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking', URL_PLUGIN_WPBOOKING . 'assets/js/wpbooking.js', array('script-wpbooking-locale-calendar'), $versio, true );


    // Variables to JavaScript
    wp_localize_script( 'script-wpbooking', 'WPBookingData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'start_calendar' => START_CALENDAR_WPBOOKING,
        'end_calendar' => END_CALENDAR_WPBOOKING,
        'is_admin' => is_admin(),
        'lang' => LANG_WPBOOKING
    ));

}
add_action('admin_enqueue_scripts', 'registrar_estilos_scripts');
add_action('wp_enqueue_scripts', 'registrar_estilos_scripts');


/************************************************************
 * Registrar Opciones Menu WP
 ************************************************************/
add_action('admin_menu', function () {
    add_menu_page(
        'WPBooking',
        'WPBooking',
        'manage_options',
        'wpbooking',
        'wpbooking_menu_page',
        'dashicons-calendar-alt',
        30
    );

    add_submenu_page(
        'wpbooking',
        __wpb('Events'),
        __wpb('Events'),
        'manage_options',
        'edit.php?post_type=wpbooking_event'
    );
});

// Página principal vacía o con texto
function wpbooking_menu_page() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/views/menu/wpbooking-admin-calendar.php");
    $content = ob_get_clean();
    echo $content;
}

/************************************************************
 * Registrar CPT Eventos
 ************************************************************/
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
});

function wpbooking_event_meta_callback($post) {
    $calendar_title = get_post_meta($post->ID, '_calendar_title', true);
    $color = get_post_meta($post->ID, '_color', true);
    $text_color = get_post_meta($post->ID, '_text_color', true);
    $start_date = get_post_meta($post->ID, '_start_date', true);
    $end_date = get_post_meta($post->ID, '_end_date', true);
    $enabled = get_post_meta($post->ID, '_enabled', true);
    $exceptions = get_post_meta($post->ID, '_exceptions', true);
    ?>
    <label><?php echo __wpb('Calendar title'); ?>: <input type="text" name="calendar_title" value="<?= esc_attr($calendar_title) ?>" style="width: 100%;" /></label><br><br>
    <label><?php echo __wpb('Event color'); ?>: <input type="color" name="color" value="<?= esc_attr($color ?: '#3788d8') ?>" /></label><br><br>
    <label><?php echo __wpb('Event text color'); ?>: <input type="color" name="text_color" value="<?= esc_attr($text_color ?: '#000000') ?>" /></label><br><br>
    <label><?php echo __wpb('Start date'); ?>: <input type="date" name="start_date" value="<?= esc_attr($start_date) ?>" /></label><br><br>
    <label><?php echo __wpb('End date'); ?>: <input type="date" name="end_date" value="<?= esc_attr($end_date) ?>" /></label><br><br>
    <label><?php echo __wpb('Enabled'); ?>:
        <input type="checkbox" name="enabled" value="1" <?= checked($enabled, '1', false) ?> />
    </label><br><br>
    <label>Fechas excepcionales (JSON):<br>
        <textarea name="exceptions" rows="4" cols="40"><?= esc_textarea($exceptions) ?></textarea>
    </label>
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
    update_post_meta($post_id, '_exceptions', wp_kses_post($_POST['exceptions'] ?? ''));
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


/************************************************************
 * Registrar Shortcodes
 ************************************************************/
add_shortcode('wpbooking_calendar', 'shortcode_wpbooking_calendar');
function shortcode_wpbooking_calendar() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/shortcode/wpbooking-calendar.php");
    $content = ob_get_clean();
    return $content;
}

/************************************************************
 * Registrar API REST Events
 ************************************************************/
add_action('rest_api_init', function () {
    register_rest_route('wpbooking/v1', '/events', [
        'methods'  => 'GET',
        'callback' => 'wpbooking_get_events',
        'permission_callback' => '__return_true',
    ]);
});
function wpbooking_get_events($request) {
    $start = sanitize_text_field($request->get_param('start'));
    $end = sanitize_text_field($request->get_param('end'));

    // Genera los eventos entre $start y $end
    $events = [
        [
            'title' => 'BÀSICA',
            'start' => '2025-05-10',
            'end' => '2025-05-10',
            'url' => 'https://example.com/',
            'color' => '#bad7b4',
            'textColor' => '#000000'
        ],
        [
            'title' => 'PRÈMIUM',
            'start' => '2025-05-10',
            'end' => '2025-05-10',
            'url' => 'https://example.com/',
            'color' => '#fff6c9',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXTRAORDINARIA',
            'start' => '2025-05-10',
            'end' => '2025-05-10',
            'url' => 'https://example.com/',
            'color' => '#bbdffb',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXTRAORDINARIA',
            'start' => '2025-05-10',
            'end' => '2025-05-10',
            'url' => 'https://example.com/',
            'color' => '#bbdffb',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXTRAORDINARIA',
            'start' => '2025-05-10',
            'end' => '2025-05-10',
            'url' => 'https://example.com/',
            'color' => '#bbdffb',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXTRAORDINARIA',
            'start' => '2025-05-10',
            'end' => '2025-05-10',
            'url' => 'https://example.com/',
            'color' => '#bbdffb',
            'textColor' => '#000000'
        ],
        [
            'title' => 'PRÈMIUM',
            'start' => '2025-05-15',
            'end' => '2025-05-19',
            'url' => 'https://example.com/',
            'color' => '#fff6c9',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXHAURIDES',
            'start' => '2025-05-21',
            'end' => '2025-05-24',
            'color' => '#e08e88',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXTRAORDINARIA',
            'start' => '2025-05-25',
            'end' => '2025-05-28',
            'color' => '#bbdffb',
            'textColor' => '#000000'
        ],
        [
            'title' => 'EXTRAORDINARIA',
            'start' => '2025-06-25',
            'end' => '2025-05-28',
            'color' => '#bbdffb',
            'textColor' => '#000000'
        ],
    ];

    return rest_ensure_response($events);
}





