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

    add_submenu_page(
        'wpbooking',
        __wpb('Persons'),
        __wpb('Persons'),
        'manage_options',
        'edit.php?post_type=wpbooking_person'
    );
    add_filter('parent_file', function ($parent_file) {
        global $typenow;
        if ($typenow === 'wpbooking_event' || $typenow === 'wpbooking_person') {
            return 'wpbooking';
        }
        return $parent_file;
    });

    add_submenu_page(
        'wpbooking',
        __wpb('Settings'),
        __wpb('Settings'),
        'manage_options',
        'wpbooking-settings',
        'wpbooking_settings_page'
    );
});

// Registro de la opción
add_action('admin_init', function () {
    register_setting('wpbooking_settings', 'wpbooking_options');
});

// Página principal vacía o con texto
function wpbooking_menu_page() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/views/menu/wpbooking-admin-calendar.php");
    $content = ob_get_clean();
    echo $content;
}

function wpbooking_settings_page() {
    ob_start();
    include(DIRECTORI_PLUGIN_WPBOOKING."/templates/views/menu/wpbooking-admin-settings.php");
    $content = ob_get_clean();
    echo $content;
}