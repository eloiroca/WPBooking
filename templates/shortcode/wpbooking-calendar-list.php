<?php
$events = get_option('wpbooking_events', []);
$options = get_option('wpbooking_options', []);
$slave_lang = $options['slave_language'] ?? null;
$current_lang = defined('LANG_WPBOOKING') ? LANG_WPBOOKING : 'es';

$filtered_events = [];
$today = date('Y-m-d');

foreach ($events as $event) {
    $postId = $event['eventPostId'] ?? false;
    if (!$postId) continue;

    // Filtrar por idioma esclavo si WPML está activo
    if (defined('ICL_SITEPRESS_VERSION') && !empty($slave_lang)) {
        $lang_details = apply_filters('wpml_post_language_details', null, $postId);
        if (is_wp_error($lang_details) || !$lang_details || !isset($lang_details['language_code']) || $lang_details['language_code'] !== $slave_lang) continue;
    }

    // Ignorar si no está habilitado
    $enabled = get_post_meta($postId, '_enabled', true);
    if ($enabled !== '1') continue;

    $event_start = $event['start'] ?? '';
    $event_end = $event['end'] ?: $event_start;

    // Ignorar eventos ya pasados
    if ($event_end < $today) continue;

    // Obtener título traducido
    $translated_id = apply_filters('wpml_object_id', $postId, 'wpbooking_event', true, $current_lang);
    $translated_post = get_post($translated_id);
    if ($translated_post) {
        $calendar_title = get_post_meta($translated_id, '_calendar_title', true);
        $event['display_title'] = $calendar_title ?: $translated_post->post_title;
        $event['url'] = get_permalink($translated_id);
    } else {
        $event['display_title'] = $event['title'];
        $event['url'] = '#';
    }

    $event['start_date_obj'] = new DateTime($event_start);
    $event['end_date_obj'] = new DateTime($event_end);

    // Si es todo el día y tiene fecha fin, FullCalendar suele poner el día siguiente exclusivo.
    // Pero en el guardado de la base de datos parece que guardamos lo que nos llega.
    // Vamos a ajustar si es necesario para mostrar.
    // En el JS: if (info.event.allDay) { endDate.setDate(endDate.getDate() - 1); }
    // Asumimos que si start y end son iguales, es un solo día.

    $filtered_events[] = $event;
}

// Ordenar por fecha de inicio
usort($filtered_events, function($a, $b) {
    return strcmp($a['start'], $b['start']);
});

// Limitar a máximo 6 eventos próximos
$max_events = 6;
if (count($filtered_events) > $max_events) {
    $filtered_events = array_slice($filtered_events, 0, $max_events);
}
?>

<div class="wpbooking-calendar-list-wrapper">
    <div class="wpbooking-event-list">
        <?php if (empty($filtered_events)): ?>
            <p><?php echo __wpb('No events found'); ?></p>
        <?php else: ?>
            <h2 class="wpbooking-calendar-list-title"><?php echo __wpb('Coming Soon'); ?></h2>
            <?php foreach ($filtered_events as $event): ?>
                <?php
                $start_fmt = date_i18n(get_option('date_format'), $event['start_date_obj']->getTimestamp());
                // usar fecha de fin si está explícita, si no usar la fecha de inicio como fecha final
                $end_date_obj_for_display = (isset($event['end']) && $event['end'] !== '') ? $event['end_date_obj'] : $event['start_date_obj'];
                $end_fmt = date_i18n(get_option('date_format'), $end_date_obj_for_display->getTimestamp());

                // Obtener horas desde los metas del post asociado (si existe)
                $post_id_for_meta = $event['eventPostId'] ?? ($event['post_id'] ?? null);
                $hour_start_meta = $post_id_for_meta ? get_post_meta($post_id_for_meta, '_hour_start', true) : '';
                $hour_end_meta = $post_id_for_meta ? get_post_meta($post_id_for_meta, '_hour_end', true) : '';
                // si no hay hora de fin, usar la de inicio
                $hour_end_for_display = ($hour_end_meta !== '' && $hour_end_meta !== null) ? $hour_end_meta : $hour_start_meta;

                // Construir cadenas de fecha+hora para mostrar
                $start_display = $start_fmt . ($hour_start_meta ? ' ' . esc_html($hour_start_meta) : '');
                $end_display = $end_fmt . ($hour_end_for_display ? ' ' . esc_html($hour_end_for_display) : '');

                // Si es el mismo día, solo mostrar una fecha
                // Mostrar la fecha final incluso si no existe (se toma la fecha de inicio).
                // Consideramos 'mismo día' solo si la fecha de fin está explícitamente provista y es igual a la de inicio.
                $is_same_day = isset($event['end']) && $event['end'] !== '' && ($event['start'] === $event['end']);
                ?>
                <div class="wpbooking-event-item">
                    <h4 class="wpbooking-event-item-title">
                        <a href="javascript:void(0);">
                            <?php echo esc_html($event['display_title']); ?>
                        </a>
                    </h4>
                    <div class="wpbooking-event-item-dates">
                        <span class="wpbooking-event-date-start">
                            <strong><?php echo __wpb('Start'); ?>:</strong> <?php echo $start_display; ?>
                        </span>
                        <?php if (!$is_same_day): ?>
                            <span class="wpbooking-event-date-end">
                                <strong><?php echo __wpb('End'); ?>:</strong> <?php echo $end_display; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
