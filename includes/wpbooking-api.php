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
    $block_current_day = !empty($options['block_current_day']);

    $slave_lang = $options['slave_language'] ?? null;
    $current_lang = sanitize_text_field($request->get_param('lang')) ?: null;

    $today = date('Y-m-d');
    $filtered = [];

    foreach ($events as $event) {
        $postId = $event['eventPostId'] ?? false;
        if (!$postId) continue;

        // Filtrar por idioma esclavo
        $lang = apply_filters('wpml_post_language_details', null, $postId);
        if (!$lang || $lang['language_code'] !== $slave_lang) continue;

        // Ignorar si no está habilitado
        $enabled = get_post_meta($postId, '_enabled', true);
        if ($enabled !== '1') continue;

        $event_start = $event['start'] ?? '';
        $event_end = $event['end'] ?: $event_start;

        // Ignorar eventos ya pasados (end es a las 00:00, así que el mismo día ya está pasado)
        if ($event_end <= date('Y-m-d')) continue;

        // Ignorar si no está en el rango
        if ($event_start > $end || $event_end < $start) continue;

        // Ignorar si no está habilitado
        $postId = $event['eventPostId'] ?? false;
        if ($postId) {
            $enabled = get_post_meta($postId, '_enabled', true);
            if ($enabled !== '1') continue;
        }

        // Obtener título traducido
        $translated_id = apply_filters('wpml_object_id', $postId, 'wpbooking_event', true, $current_lang);
        $translated_post = get_post($translated_id);
        if ($translated_post && isset($event['title'])) {
            $calendar_title = get_post_meta($translated_id, '_calendar_title', true);
            $event['title'] = $calendar_title ?: $translated_post->post_title;
        }

        // Obtenemos los colores del postId para asignar el color al evento
        $color = get_post_meta($postId, '_color', true) ?: '#ff0000';
        $textColor = get_post_meta($postId, '_text_color', true) ?: '#000000';
        $event['color'] = $color;
        $event['textColor'] = $textColor;

        // Orden/Prioridad para ordenar en el calendario
        $order = (int) get_post_meta($postId, '_order', true);
        $event['order'] = $order;

        if ($split_events && $event_start !== $event_end) {
            // Evento individual por día
            $start_date = new DateTime($event_start);
            $end_date = new DateTime($event_end);

            while ($start_date < $end_date) {
                $e = $event;
                $e['start'] = $start_date->format('Y-m-d');
                $e['end'] = $start_date->format('Y-m-d');
                $e['id'] = $event['id'] . '_' . $start_date->format('Ymd');

                if (!$is_admin && $postId) {
                    $can_reserve = get_post_meta($postId, '_can_reserve', true);
                    if ($can_reserve == '1' && !($block_current_day && $e['start'] === $today)) {
                        $e['url'] = get_permalink($postId) . '?d=' . wpbooking_encrypt_date($e['start']);
                    }
                }

                $filtered[] = $e;
                $start_date->modify('+1 day');
            }
        } else {
            if (!$is_admin && $postId) {
                $can_reserve = get_post_meta($postId, '_can_reserve', true);
                if ($can_reserve == '1' && !($block_current_day && $event_start === $today)) {
                    $event['url'] = get_permalink($postId) . '?d=' . wpbooking_encrypt_date($event_start);
                }
            }
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

        // Asegurar que sea un array
        if (!is_array($events)) {
            $events = [];
        }

        $start_date = $params['start'] ?? '';
        $post_id = $params['eventPostId'] ?? '';

        // Validar fecha en pasado
        if ($start_date && strtotime($start_date) < strtotime('today')) {
            return rest_ensure_response([
                'success' => false,
                'message' => __wpb('Start date cannot be in the past'),
            ]);
        }

        // Validar que tenga eventPostId
        if (empty($post_id)) {
            return rest_ensure_response([
                'success' => false,
                'message' => __wpb('The event must be linked to a valid post ID'),
            ]);
        }

        $id = $params['id'] ?? uniqid('ev_');
        // Validar que el $id esta vacío
        if (empty($id)) {
            return rest_ensure_response([
                'success' => false,
                'message' => __wpb('Event ID cannot be empty'),
            ]);
        }
        $events[$id] = array_merge([
            'id' => $id,
            'title' => '',
            'start' => '',
            'end' => '',
            'color' => '',
            'textColor' => '',
            'type' => 'wpbooking_event',
            'eventPostId' => '',
        ], $params);

        update_option('wpbooking_events', $events);

        return rest_ensure_response([
            'success' => true,
            'message' => __wpb('Event saved successfully'),
            'event' => $events[$id]
        ]);
    } catch (Throwable $e) {
        return new WP_Error('error_guardar', __wpb('Error saving event') . ' ' . $e->getMessage(), ['status' => 500]);
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

function wpbooking_encrypt_date($date) {
    return rtrim(strtr(base64_encode($date), '+/', '-_'), '=');
}