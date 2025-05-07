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


//include_once(DIRECTORI_PLUGIN_WPBOOKING . 'class/WPBOOKING.php');

/************************************************************
 * Registrar estilos y scripts
 ************************************************************/
function registrar_estilos_scripts() {
    $versio = "1.0.0";
    // Modal
    wp_enqueue_style( 'style-wpbooking-micromodal', URL_PLUGIN_WPBOOKING. 'assets/css/micromodal/micromodal.css', array(), $versio);
    wp_enqueue_script( 'script-wpbooking-micromodal', URL_PLUGIN_WPBOOKING . 'assets/js/micromodal/micromodal.js', array(), $versio );

    // Calendar
    //wp_enqueue_script( 'script-wpbooking-calendar', URL_PLUGIN_WPBOOKING . 'assets/js/fullcalendar/index.global.min.js', array(), $versio );
    wp_enqueue_script( 'script-wpbooking-locale-calendar', URL_PLUGIN_WPBOOKING . 'assets/js/fullcalendar/locales-all.global.min.js', array(), $versio );

}
add_action('wp_enqueue_scripts', 'registrar_estilos_scripts');

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




