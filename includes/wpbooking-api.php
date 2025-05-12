<?php
add_action('rest_api_init', function () {
    register_rest_route('wpbooking/v1', '/events', [
        'methods'  => 'GET',
        'callback' => 'wpbooking_get_events',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('wpbooking/v1', '/events', [
        'methods'  => 'POST',
        'callback' => 'wpbooking_save_event',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ]);

    register_rest_route('wpbooking/v1', '/events/(?P<id>[a-zA-Z0-9_-]+)', [
        'methods'  => 'DELETE',
        'callback' => 'wpbooking_delete_event',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ]);
});
function wpbooking_get_events($request) {
    $start = sanitize_text_field($request->get_param('start'));
    $end   = sanitize_text_field($request->get_param('end'));
    $is_admin = $request->get_param('is_admin') === '1';

    $events = get_option('wpbooking_events', []);
    $options = get_option('wpbooking_options', []);
    $split_events = !$is_admin && !empty($options['individual_days']);

    $filtered = [];

    foreach ($events as $event) {
        $event_start = $event['start'] ?? '';
        $event_end = $event['end'] ?: $event_start;

        // Ignorar eventos ya pasados
        if ($event_end < date('Y-m-d')) continue;

        // Ignorar si no está en el rango
        if ($event_start > $end || $event_end < $start) continue;

        if ($split_events && $event_start !== $event_end) {
            // Evento individual por día
            $start_date = new DateTime($event_start);
            $end_date = new DateTime($event_end);

            while ($start_date < $end_date) {
                $e = $event;
                $e['start'] = $start_date->format('Y-m-d');
                $e['end'] = $start_date->format('Y-m-d');
                $e['id'] = $event['id'] . '_' . $start_date->format('Ymd');
                $filtered[] = $e;
                $start_date->modify('+1 day');
            }
        } else {
            // Evento normal
            $filtered[] = $event;
        }
    }

    return rest_ensure_response($filtered);
}

// POST (create/update)
function wpbooking_save_event($request) {
    try {
        $params = $request->get_json_params();
        $events = get_option('wpbooking_events', []);

        // Validar que la fecha de inicio no sea anterior al día de hoy
        $start_date = $params['start'] ?? ''; // Suponemos que 'start' es en formato 'Y-m-d'

        if ($start_date && strtotime($start_date) < strtotime('today')) {
            return rest_ensure_response([
                'success' => false,
                'message' => __wpb('Start date cannot be in the past'),
            ]);
        }

        $id = $params['id'] ?? uniqid('ev_');
        $events[$id] = array_merge([
            'id' => $id,
            'title' => '',
            'start' => '',
            'end' => '',
            'color' => '',
            'textColor' => '',
            'type' => 'wpbooking_event',
        ], $params);

        update_option('wpbooking_events', $events);
        return rest_ensure_response([
            'success' => true,
            'message' => __wpb('Event saved successfully'),
            'event' => $events[$id]
        ]);
    } catch (Throwable $e) {
        return new WP_Error('error_guardar', 'Error al guardar el evento', ['status' => 500]);
    }
}

function wpbooking_delete_event($request) {
    try {
        $id = $request['id'];
        $events = get_option('wpbooking_events', []);

        if (isset($events[$id])) {
            unset($events[$id]);
            update_option('wpbooking_events', $events);
            return rest_ensure_response([
                'success' => true,
                'message' => __wpb('Event deleted successfully'),
            ]);
        }

        return new WP_Error('not_found', __wpb('Event not found'), ['status' => 404]);
    } catch (Throwable $e) {
        return new WP_Error('error_borrar', __wpb('Error deleting event'), ['status' => 500]);
    }
}