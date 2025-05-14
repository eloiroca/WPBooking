<?php
get_header();

while (have_posts()) : the_post();
    // Obtener los valores personalizados de precio, hora de inicio y hora de finalización
    $price = get_post_meta(get_the_ID(), '_price', true);
    $hour_start = get_post_meta(get_the_ID(), '_hour_start', true);
    $hour_end = get_post_meta(get_the_ID(), '_hour_end', true);
    $color = get_post_meta(get_the_ID(), '_color', true);

    // Mostrar el título del evento
    echo '<h1 style="color:' . esc_attr($color) . '">' . get_the_title() . '</h1>';

    // Desencriptamos la fecha
    $encoded_date = $_GET['d'] ?? '';
    $date = $encoded_date ? base64_decode(strtr($encoded_date, '-_', '+/')) : null;
    $date = $date ? date('d/m/Y', strtotime($date)) : null;

    // Si no hay fecha, mostramos un mensaje de error
    if (!$date) {
        echo '<p>' . __wpb('There is no availability for today') . '</p>';
        echo '<p>' . __wpb('Tickets cannot be purchased online for the same day, but you can find them, if they are not sold out, at the Park box office.') . '</p>';
        echo '<a href="' . esc_url(home_url()) . '">' . __wpb('Thank\'s') . '</a>';
        exit;
    }

    ?>
    <div>
        <p class="date">
            <span>
                <b><?php echo __wpb('Date') ?>:</b>
                <bdi><?php echo esc_html($date) ?></bdi>
            </span>
        </p>
        <p class="price">
            <span>
                <b><?php echo __wpb('Price') ?>:</b>
                <bdi><?php echo esc_html($price) ?><span>€</span></bdi>
            </span>
            <span class="lower"> / <?php echo __wpb('Day') ?></span>
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
