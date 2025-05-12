<?php
add_shortcode('wpbooking_calendar', 'shortcode_wpbooking_calendar');
function shortcode_wpbooking_calendar() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/shortcode/wpbooking-calendar.php");
    $content = ob_get_clean();
    return $content;
}