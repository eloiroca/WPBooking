<?php
get_header();

while (have_posts()) : the_post();
    // Obtener los valores personalizados de precio, hora de inicio y hora de finalización
    $event_id = wpbooking_get_original_post_id(get_the_ID(), 'wpbooking_event');
    $price = get_post_meta($event_id, '_price', true);
    $hour_start = get_post_meta($event_id, '_hour_start', true);
    $hour_end = get_post_meta($event_id, '_hour_end', true);
    $color = get_post_meta($event_id, '_color', true);


    // Obtener las opciones de configuración
    $options = get_option('wpbooking_options');
    $multiply_price = !empty($options['multiply_price_qty']) ? 'true' : 'false';
    $multiply_service_price_qty = !empty($options['multiply_service_price_qty']) ? 'true' : 'false';

    //Obtener las personas
    $persons = get_posts([
        'post_type' => 'wpbooking_person',
        'posts_per_page' => -1,
        'meta_key' => '_price',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ]);
    // Filtrar las personas asignadas al evento actual
    $filtered = array_filter($persons, function ($person) use ($event_id) {
        $assigned = get_post_meta($person->ID, '_assigned_events', true);
        return is_array($assigned) && in_array($event_id, $assigned);
    });

    //Obtener los servicios
    $services = get_posts([
        'post_type' => 'wpbooking_service',
        'posts_per_page' => -1,
        'meta_key' => '_price',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ]);
    // Filtrar los servicios asignados al evento actual
    $filtered_services = array_filter($services, function ($service) use ($event_id) {
        $assigned = get_post_meta($service->ID, '_assigned_events', true);
        return is_array($assigned) && in_array($event_id, $assigned);
    });

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
    if ($filtered) {
    ?>
    <div class="wpbooking-event-tickets">
        <div class="wpbooking-event-tickets-title">
            <h2><?php echo __wpb('Tickets') ?></h2>
        </div>
        <?php
        if ($filtered) {
            echo '<div class="wpbooking-personas-tickets">';
            foreach ($filtered as $persona) {
                $persona_id = $persona->ID;
                $nombre = get_the_title($persona_id);
                $precio = get_post_meta($persona_id, '_price', true);
                $min = get_post_meta($persona_id, '_min', true) ?: 0;
                $max = get_post_meta($persona_id, '_max', true) ?: 10;
                $precio_texto = $precio == 0 ? __wpb('Free') : number_format($precio, 2) . ' €';

                echo '<div class="wpbooking-ticket-row" data-id="' . esc_attr($persona_id) . '" data-price="' . esc_attr($precio) . '">';
                echo '<label>' . esc_html($nombre) . ': <b>' . esc_html($precio_texto) . '</b></label>';
                echo '<div class="wpbooking-qty-control">';
                echo '<button type="button" class="wpb-qty-minus">-</button>';
                echo '<input type="number" name="personas[' . esc_attr($persona_id) . ']" value="' . $min . '" min="' . $min . '" max="' . $max . '" readonly />';
                echo '<button type="button" class="wpb-qty-plus">+</button>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
    <?php
    }
    if ($filtered_services) {
    ?>
    <div class="wpbooking-event-services">
        <div class="wpbooking-event-services-title">
            <h2><?php echo __wpb('Services') ?></h2>
        </div>
        <?php
        if ($filtered_services) {
            echo '<div class="wpbooking-services-list">';
            foreach ($filtered_services as $service) {
                $service_id = $service->ID;
                $nombre = get_the_title($service_id);
                $precio = get_post_meta($service_id, '_price', true);
                $min = get_post_meta($service_id, '_min', true) ?: 0;
                $max = get_post_meta($service_id, '_max', true) ?: 10;
                $precio_texto = $precio == 0 ? __wpb('Free') : number_format($precio, 2) . ' €';

                echo '<div class="wpbooking-service-row" data-id="' . esc_attr($service_id) . '" data-price="' . esc_attr($precio) . '">';
                echo '<label>' . esc_html($nombre) . ': <b>' . esc_html($precio_texto) . '</b></label>';
                echo '<div class="wpbooking-qty-control">';
                echo '<button type="button" class="wpb-qty-minus">-</button>';
                echo '<input type="number" name="services[' . esc_attr($service_id) . ']" value="' . $min . '" min="' . $min . '" max="' . $max . '" readonly />';
                echo '<button type="button" class="wpb-qty-plus">+</button>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
    <?php
    }

    // Si no hay ni personas ni servicios, mostramos un mensaje
    if (!$filtered && !$filtered_services) {
        echo '<p>' . __wpb('No tickets or services available for this event.') . '</p>';
        return;
    }
    ?>

    <div class="wpbooking-ticket-total">
        <?php if ($filtered) { ?>
        <strong><?php echo __wpb('Total people') ?>:</strong>
        <span id="wpbooking-total-count">0</span><br>
        <?php } ?>
        <?php if ($filtered_services) { ?>
        <strong><?php echo __wpb('Total services') ?>:</strong>
        <span id="wpbooking-total-services-count">0</span><br>
        <?php } ?>
        <strong><?php echo __wpb('Total price') ?>:</strong>
        <span id="wpbooking-total-price">0.00 €</span>
    </div>

    <form id="wpbooking-reserve-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="wpbooking_add_to_cart">
        <input type="hidden" name="event_id" value="<?php echo esc_attr($event_id); ?>">
        <input type="hidden" name="personas_json" id="wpbooking-personas-json">
        <input type="hidden" name="services_json" id="wpbooking-services-json">
        <button type="submit" class="wpbooking-reserve-button"><?php echo __wpb('Reserve') ?></button>
    </form>

    <?php
// Mostrar el contenido del evento
echo '<div>' . get_the_content() . '</div>';

?>
    <script>
        jQuery(document).ready(function ($) {
            const wpbMultiplyPrice = <?php echo $multiply_price; ?>;

            function updateTotal() {
                let totalPersons = 0;
                let totalServices = 0;
                let totalPrice = 0;

                // Personas
                $('.wpbooking-ticket-row').each(function () {
                    const input = $(this).find('input[type="number"]');
                    const qty = parseInt(input.val()) || 0;
                    const price = parseFloat($(this).data('price')) || 0;

                    totalPersons += qty;
                    totalPrice += wpbMultiplyPrice ? qty * price : (qty > 0 ? price : 0);
                });

                // Servicios
                $('.wpbooking-service-row').each(function () {
                    const input = $(this).find('input[type="number"]');
                    const qty = parseInt(input.val()) || 0;
                    const price = parseFloat($(this).data('price')) || 0;

                    totalServices += qty;
                    totalPrice += wpbMultiplyPrice ? qty * price : (qty > 0 ? price : 0);
                });

                $('#wpbooking-total-count').text(totalPersons);
                $('#wpbooking-total-services-count').text(totalServices);
                $('#wpbooking-total-price').text(totalPrice.toFixed(2) + ' €');
            }

            function setupControls(selector) {
                $(selector).each(function () {
                    const row = $(this);
                    const input = row.find('input[type="number"]');
                    const min = parseInt(input.attr('min'));
                    const max = parseInt(input.attr('max'));

                    row.find('.wpb-qty-minus').on('click', function () {
                        let val = parseInt(input.val());
                        if (val > min) {
                            input.val(val - 1);
                            updateTotal();
                        }
                    });

                    row.find('.wpb-qty-plus').on('click', function () {
                        let val = parseInt(input.val());
                        if (val < max) {
                            input.val(val + 1);
                            updateTotal();
                        }
                    });
                });
            }

            setupControls('.wpbooking-ticket-row');
            setupControls('.wpbooking-service-row');
            updateTotal(); // inicial

            // Enviar el formulario al reservar
            $('#wpbooking-reserve-form').on('submit', function (e) {
                e.preventDefault();

                const personas = {};
                const services = {};

                $('.wpbooking-ticket-row').each(function () {
                    const id = $(this).data('id');
                    const qty = parseInt($(this).find('input').val());
                    if (qty > 0) personas[id] = qty;
                });

                $('.wpbooking-service-row').each(function () {
                    const id = $(this).data('id');
                    const qty = parseInt($(this).find('input').val());
                    if (qty > 0) services[id] = qty;
                });

                //Si no hay personas ni servicios, mostramos un mensaje
                if (Object.keys(personas).length === 0 && Object.keys(services).length === 0) {
                    Swal.fire({
                        title: "",
                        text: WPBookingData.error_select_person_or_service,
                        icon: "warning",
                        customClass: {
                            popup: 'swal-custom'
                        }
                    });
                    return;
                }

                $.ajax({
                    url: WPBookingData.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpbooking_add_to_cart',
                        event_id: $('#wpbooking-reserve-form input[name="event_id"]').val(),
                        personas_json: JSON.stringify(personas),
                        services_json: JSON.stringify(services),
                        date: '<?php echo esc_js($date); ?>',
                    },
                    success: function (res) {
                        if (res.success) {
                            const lang = window.location.pathname.split('/')[1]; // Detecta idioma actual
                            const langPrefix = lang && lang.length === 2 ? `/${lang}` : ''; // Si es 'es', 'ca', etc.
                            window.location.href = `${langPrefix}/cart/`;
                        } else {
                            alert(res.data.message || 'Error al reservar');
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: "",
                            text: "Error en la solicitud.",
                            icon: "error",
                            customClass: {
                                popup: 'swal-custom'
                            }
                        });
                    }
                });
            });

            // Reparar el canvio de idioma en WPML
            const urlParams = new URLSearchParams(window.location.search);
            const dateParam = urlParams.get('d');

            if (dateParam) {
                $('.wpml-ls-menu-item a').each(function () {
                    const href = $(this).attr('href');
                    if (href && !href.includes('?')) {
                        $(this).attr('href', href + '?d=' + dateParam);
                    } else if (href && !href.includes('d=')) {
                        $(this).attr('href', href + '&d=' + dateParam);
                    }
                });
            }
        });


    </script>

<?php

endwhile;

get_footer();
