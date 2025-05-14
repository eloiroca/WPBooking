<?php
get_header();

while (have_posts()) : the_post();
    // Mostrar el título del evento
    echo '<h1>' . get_the_title() . '</h1>';

    // Obtener los valores personalizados de precio, hora de inicio y hora de finalización
    $price = get_post_meta(get_the_ID(), '_price', true);
    $hour_start = get_post_meta(get_the_ID(), '_hour_start', true);
    $hour_end = get_post_meta(get_the_ID(), '_hour_end', true);

    // Desencriptamos la fecha
    $encoded_date = $_GET['d'] ?? '';
    $date = $encoded_date ? base64_decode(strtr($encoded_date, '-_', '+/')) : null;

    ?>
    <div>
        <p class="price">
            <span>
                <bdi><?php echo esc_html($price) ?><span>€</span></bdi>
            </span>
            <span> / <?php echo __wpb('Day') ?></span></p>
        <p class="date">
            <span>
                <bdi><?php echo esc_html($date) ?></bdi>
            </span>
            <span> / <?php echo __wpb('Date') ?></span>
        </p>
    </div>
    <div class="hours">
        <div class="hour-start">
            <span class="hour-title"><?php echo __wpb('Hour start') ?>: </span>
            <span>
                <bdi><?php echo esc_html($hour_start) ?></bdi>
            </span>
        </div>
        <div class="hour-end">
            <span class="hour-title"><?php echo __wpb('Hour end') ?>: </span>
            <span>
                <bdi><?php echo esc_html($hour_end) ?></bdi>
            </span>
        </div>
    </div>
<?php
    // Mostrar el contenido del evento
    echo '<div>' . get_the_content() . '</div>';
endwhile;

get_footer();
