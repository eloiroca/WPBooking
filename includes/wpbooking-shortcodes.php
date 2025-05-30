<?php
/************************************************************
 * Shortcode para mostrar el calendario de WPBooking
 ************************************************************/
add_shortcode('wpbooking_calendar', 'shortcode_wpbooking_calendar');
function shortcode_wpbooking_calendar() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/shortcode/wpbooking-calendar.php");
    $content = ob_get_clean();
    return $content;
}
/************************************************************
 * Shortcode para mostrar todos los CPT Event de WPBooking para que se pueda regalar
 ************************************************************/
add_shortcode('wpbooking_gift_events', 'shortcode_wpbooking_gift_events');
function shortcode_wpbooking_gift_events() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/shortcode/wpbooking-gift-events.php");
    $content = ob_get_clean();
    return $content;
}