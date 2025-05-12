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

    register_rest_route('wpbooking/v1', '/events', [
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

    $events = get_option('wpbooking_events', []); // sin json_decode
    $filtered = [];

    foreach ($events as $event) {
        $event_start = $event['start'] ?? '';
        $event_end = $event['end'] ?? $event_start;

        if ($event_start <= $end && $event_end >= $start) {
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