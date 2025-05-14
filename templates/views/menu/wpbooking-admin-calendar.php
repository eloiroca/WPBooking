<?php
$options = get_option('wpbooking_options', []);
$slave_lang = $options['slave_language'] ?? null;
$current_lang = defined('LANG_WPBOOKING') ? LANG_WPBOOKING : null;

$eventos = get_posts([
    'post_type' => 'wpbooking_event',
    'post_status' => 'publish',
    'numberposts' => -1,
    'meta_key' => '_enabled',
    'meta_value' => '1'
]);

$eventos_filtrados = [];

foreach ($eventos as $evento) {
    $lang = apply_filters('wpml_post_language_details', null, $evento->ID);
    if (!$lang || $lang['language_code'] !== $slave_lang) continue;

    // Título traducido si existe
    $translated_id = apply_filters('wpml_object_id', $evento->ID, 'wpbooking_event', true, $current_lang);
    $translated_post = get_post($translated_id);
    $translated_title = $translated_post ? $translated_post->post_title : $evento->post_title;

    // Guardar el evento con el título traducido
    $evento->_translated_title = $translated_title;
    $eventos_filtrados[] = $evento;
}
?>

<div class="wrap">
    <h1>WPBooking</h1>
    <div style="display: flex; gap: 2rem;">
        <div id="external-events" style="width: 200px;">
            <p><strong><?= __wpb('Events') ?></strong></p>

            <?php foreach ($eventos_filtrados as $evento):
                $title = $evento->_translated_title ?: $evento->post_title;
                $color = get_post_meta($evento->ID, '_color', true) ?: '#ff0000';
                $textColor = get_post_meta($evento->ID, '_text_color', true) ?: '#000000';
                ?>
                <div class="fc-event"
                     data-event='<?= json_encode([
                         'type' => 'wpbooking_event',
                         'title' => $title,
                         'color' => $color,
                         'textColor' => $textColor,
                         'eventPostId' => $evento->ID,
                     ]) ?>'
                     style="background-color: <?= esc_attr($color) ?>; color: <?= esc_attr($textColor) ?>;">
                    <?= esc_html($title) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="wpbooking-calendar" style="flex: 1;"></div>
    </div>
</div>
