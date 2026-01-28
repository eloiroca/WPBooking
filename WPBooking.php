<?php
/**
 * Plugin Name: WPBooking
 * Description: Plugin de reservas con calendario visual integrado con WooCommerce. Permite configurar disponibilidades, gestionar personas y servicios, y realizar pagos directamente desde el calendario.
 * Version: 1.01
 * Author: Eloi
 * Author URI:        https://github.com/eloiroca/eloiroca
 */

define("DIRECTORI_PLUGIN_WPBOOKING" , plugin_dir_path( __FILE__ ));
define("URL_PLUGIN_WPBOOKING" , plugin_dir_url( __FILE__ ));

define("ASSETS_VERSION_WPBOOKING", "1.0.6");

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
define('END_CALENDAR_WPBOOKING', date('Y-m-01', strtotime('+1 years')));

/************************************************************
 * Funciones de traducción
 ************************************************************/
if (!function_exists('__wpb')) {
    function wpbooking_translations_array() {
        static $translations = null;

        if ($translations !== null) return $translations;

        $lang = defined('LANG_WPBOOKING') ? LANG_WPBOOKING : 'en';
        $path = DIRECTORI_PLUGIN_WPBOOKING . "lang/$lang.php";

        if (file_exists($path)) {
            $translations = include $path;
        } else {
            $translations = []; // si no existe el archivo, usar inglés (cadenas originales)
        }

        return $translations;
    }

    function __wpb($key) {
        $translations = wpbooking_translations_array();
        return $translations[$key] ?? $key;
    }
}


//include_once(DIRECTORI_PLUGIN_WPBOOKING . 'class/WPBOOKING.php');

/************************************************************
 * Registrar estilos y scripts
 ************************************************************/
function registrar_estilos_scripts($hook = '') {
    $versio = ASSETS_VERSION_WPBOOKING;

    // Solo cargar en admin si es WPBooking
    if (is_admin() && strpos($hook, 'wpbooking') === false) return;

    // Modal
    wp_enqueue_style( 'style-wpbooking-micromodal', URL_PLUGIN_WPBOOKING. 'assets/css/micromodal/micromodal.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking-micromodal', URL_PLUGIN_WPBOOKING . 'assets/js/micromodal/micromodal.js', array(), $versio );

    // Alertify
    wp_enqueue_style( 'style-wpbooking-alertify', URL_PLUGIN_WPBOOKING. 'assets/css/alertify/alertify.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking-alertify', URL_PLUGIN_WPBOOKING . 'assets/js/alertify/alertify.js', array(), $versio );

    // SweetAlert
    wp_enqueue_style( 'style-wpbooking-sweetalert', URL_PLUGIN_WPBOOKING . 'assets/css/sweetalert/sweetalert2.min.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking-sweetalert', URL_PLUGIN_WPBOOKING . 'assets/js/sweetalert/sweetalert2.min.js', array(), $versio );

    // Calendar
    wp_enqueue_script( 'script-wpbooking-calendar', URL_PLUGIN_WPBOOKING . 'assets/js/fullcalendar/index.global.min.js', array(), $versio );
    wp_enqueue_script( 'script-wpbooking-locale-calendar', URL_PLUGIN_WPBOOKING . 'assets/js/fullcalendar/locales-all.global.min.js', array('script-wpbooking-calendar'), $versio );

    // Datepicker
    wp_enqueue_style( 'style-wpbooking-datepicker', URL_PLUGIN_WPBOOKING . 'assets/css/datepicker/flatpickr.min.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking-datepicker', URL_PLUGIN_WPBOOKING . 'assets/js/datepicker/flatpickr.js', array(), $versio , true );
    wp_enqueue_script( 'script-wpbooking-datepicker-locale', URL_PLUGIN_WPBOOKING . 'assets/js/datepicker/flatpickr-'.LANG_WPBOOKING.'.js', array('script-wpbooking-datepicker'), $versio, true );

    // WPBooking
    wp_enqueue_style( 'style-wpbooking', URL_PLUGIN_WPBOOKING. 'assets/css/wpbooking.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking', URL_PLUGIN_WPBOOKING . 'assets/js/wpbooking.js', array('script-wpbooking-locale-calendar'), $versio, true );


    $options = get_option('wpbooking_options', []);
    $allow_past_events = !empty($options['allow_past_events']);

    wp_localize_script( 'script-wpbooking', 'WPBookingData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'start_calendar' => $allow_past_events ? null : START_CALENDAR_WPBOOKING,
        'end_calendar' => END_CALENDAR_WPBOOKING,
        'is_admin' => is_admin(),
        'lang' => LANG_WPBOOKING,
        'nonce' => wp_create_nonce('wp_rest'),
        'error_message' => __wpb('An error occurred while saving the event'),
        'error_select_person_or_service' => __wpb('You must select at least one person or service'),
        'error_dates_and_plate_number' => __wpb('You must select a date range and enter a number plate'),
        'show_modal_on_click' => !empty($options['show_modal_on_click']),
        'block_current_day' => !empty($options['block_current_day']),
        'allow_past_events' => !empty($options['allow_past_events']),
    ));

}
add_action('admin_enqueue_scripts', 'registrar_estilos_scripts');
add_action('wp_enqueue_scripts', 'registrar_estilos_scripts');

/************************************************************
 * Registrar Opciones Menu WP
 ************************************************************/
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/wpbooking-menu.php' );

/************************************************************
 * Registrar CPTs
 ************************************************************/
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/cpt/wpbooking-cpt-event.php' );
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/cpt/wpbooking-cpt-person.php' );
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/cpt/wpbooking-cpt-service.php' );

/************************************************************
 * Registrar Shortcodes
 ************************************************************/
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/wpbooking-shortcodes.php' );

/************************************************************
 * Registrar API REST
 ************************************************************/
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/wpbooking-api.php' );

/************************************************************
 * Registrar Controlador de Reservas (Add to Cart)
 ************************************************************/
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/wpbooking-reservations.php' );

/************************************************************
 * Registrar Hooks Woocommerce (Checkout, Order, etc.)
 ************************************************************/
include_once ( DIRECTORI_PLUGIN_WPBOOKING . 'includes/wpbooking-order.php' );

/************************************************************
 * Registrar CRON
 ************************************************************/
include_once( DIRECTORI_PLUGIN_WPBOOKING . 'includes/wpbooking-cron.php' );