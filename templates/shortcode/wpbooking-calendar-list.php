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

    // Filtrar por idioma esclavo
    $lang_details = apply_filters('wpml_post_language_details', null, $postId);
    if (is_wp_error($lang_details) || !$lang_details || !isset($lang_details['language_code']) || $lang_details['language_code'] !== $slave_lang) continue;

    // Ignorar si no está habilitado
    $enabled = get_post_meta($postId, '_enabled', true);
    if ($enabled !== '1') continue;

    $event_start = $event['start'] ?? '';
    // Ignorar eventos que no tienen fecha de fin
    if (empty($event['end'])) continue;

    $event_end = $event['end'];

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
?>

<div class="wpbooking-calendar-list-wrapper">
    <div class="wpbooking-event-list">
        <?php if (empty($filtered_events)): ?>
            <p><?php echo __wpb('No events found'); ?></p>
        <?php else: ?>
            <?php foreach ($filtered_events as $event): ?>
                <?php
                $start_fmt = date_i18n(get_option('date_format'), $event['start_date_obj']->getTimestamp());
                $end_fmt = date_i18n(get_option('date_format'), $event['end_date_obj']->getTimestamp());

                // Si es el mismo día, solo mostrar una fecha
                $is_same_day = $event['start'] === $event['end'] || empty($event['end']);
                ?>
                <div class="wpbooking-event-item">
                    <h4 class="wpbooking-event-item-title">
                        <a href="javascript:void(0);">
                            <?php echo esc_html($event['display_title']); ?>
                        </a>
                    </h4>
                    <div class="wpbooking-event-item-dates">
                        <span class="wpbooking-event-date-start">
                            <strong><?php echo __wpb('Start'); ?>:</strong> <?php echo $start_fmt; ?>
                        </span>
                        <?php if (!$is_same_day): ?>
                            <span class="wpbooking-event-date-end">
                                <strong><?php echo __wpb('End'); ?>:</strong> <?php echo $end_fmt; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
