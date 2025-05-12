<?php
$eventos = get_posts([
    'post_type' => 'wpbooking_event',
    'post_status' => 'publish',
    'numberposts' => -1,
    'meta_key' => '_enabled',
    'meta_value' => '1'
]);
?>

<div class="wrap">
    <h1>WPBooking</h1>
    <div style="display: flex; gap: 2rem;">
        <div id="external-events" style="width: 200px;">
            <p><strong><?= __wpb('Events') ?></strong></p>

            <?php foreach ($eventos as $evento):
                $title = get_post_meta($evento->ID, '_calendar_title', true) ?: $evento->post_title;
                $color = get_post_meta($evento->ID, '_color', true) ?: '#ff0000';
                $textColor = get_post_meta($evento->ID, '_text_color', true) ?: '#000000';
                ?>
                <div class="fc-event"
                     data-event='<?= json_encode([
                         'type' => 'wpbooking_event',
                         'title' => $title,
                         'color' => $color,
                         'textColor' => $textColor
                     ]) ?>'
                     style="background-color: <?= esc_attr($color) ?>; color: <?= esc_attr($textColor) ?>;">
                    <?= esc_html($title) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="wpbooking-calendar" style="flex: 1;"></div>
    </div>
</div>
