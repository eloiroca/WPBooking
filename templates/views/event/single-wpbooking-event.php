<?php
get_header();

while (have_posts()) : the_post();
    // Mostrar el título del evento
    echo '<h1>' . get_the_title() . '</h1>';

    // Mostrar el contenido del evento
    echo '<div>' . get_the_content() . '</div>';

    // Obtener los valores personalizados de precio, hora de inicio y hora de finalización
    $price = get_post_meta(get_the_ID(), '_price', true);
    $hour_start = get_post_meta(get_the_ID(), '_hour_start', true);
    $hour_end = get_post_meta(get_the_ID(), '_hour_end', true);

    // Mostrar los campos de precio y horas
    echo '<div><strong>' . __wpb('Price') . ':</strong> ' . esc_html($price) . '</div>';
    echo '<div><strong>' . __wpb('Start time') . ':</strong> ' . esc_html($hour_start) . '</div>';
    echo '<div><strong>' . __wpb('End time') . ':</strong> ' . esc_html($hour_end) . '</div>';
endwhile;

get_footer();
