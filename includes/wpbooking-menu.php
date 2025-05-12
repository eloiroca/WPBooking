<?php
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