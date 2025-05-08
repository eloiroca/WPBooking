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
        'Calendari',
        'Calendari',
        'manage_options',
        'wpbooking-calendar',
        'wpbooking_calendar_page'
    );
});

// Página principal vacía o con texto
function wpbooking_menu_page() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/views/menu/wpbooking-admin-calendar.php");
    $content = ob_get_clean();
    echo $content;
}

// Página del calendario
function wpbooking_calendar_page() {
    // Todoo
}

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





