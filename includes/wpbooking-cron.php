<?php
// Registrar el Cron Job
add_action('wp', 'wpbooking_schedule_event_deletion');

function wpbooking_schedule_event_deletion() {
    if (!wp_next_scheduled('wpbooking_delete_past_events')) {
        // Programa el cron para que se ejecute cada día
        wp_schedule_event(time(), 'daily', 'wpbooking_delete_past_events');
    }
}

// Eliminar el Cron Job al desactivar el plugin
register_deactivation_hook(__FILE__, 'wpbooking_remove_scheduled_event');
function wpbooking_remove_scheduled_event() {
    $timestamp = wp_next_scheduled('wpbooking_delete_past_events');
    wp_unschedule_event($timestamp, 'wpbooking_delete_past_events');
}

add_action('wpbooking_delete_past_events', 'wpbooking_delete_past_events_function');

function wpbooking_delete_past_events_function() {
    // Obtener todos los eventos guardados
    $events = get_option('wpbooking_events', []);

    // Fecha actual
    $today = date('Y-m-d'); // Obtener la fecha actual en formato 'Y-m-d'

    foreach ($events as $event_id => $event) {
        $start = $event['start'] ?? '';
        $end = $event['end'] ?? ''; // Si no tiene fecha de fin, se asume que el fin es el mismo día de inicio

        // Si la fecha de inicio ya pasó o el evento no tiene fecha final y la fecha de inicio es anterior a hoy
        if (($start <= $today && empty($end)) || ($end && $end < $today)) {
            unset($events[$event_id]); // Eliminar el evento
        }
    }

    // Actualizar la opción 'wpbooking_events' con los eventos restantes
    update_option('wpbooking_events', $events);
}
