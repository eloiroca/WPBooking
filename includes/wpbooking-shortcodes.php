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
/************************************************************
 * Shortcode para mostrar reservas de plazas de parking
 ************************************************************/
add_shortcode('wpbooking_parking', 'shortcode_wpbooking_parking');
function shortcode_wpbooking_parking() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/shortcode/wpbooking-parking.php");
    $content = ob_get_clean();
    return $content;
}
/************************************************************
 * Shortcode para mostrar el listado de eventos de WPBooking
 ************************************************************/
add_shortcode('wpbooking_calendar_list', 'shortcode_wpbooking_calendar_list');
function shortcode_wpbooking_calendar_list() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/shortcode/wpbooking-calendar-list.php");
    $content = ob_get_clean();
    return $content;
}