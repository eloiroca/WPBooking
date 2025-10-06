<?php
$options = get_option('wpbooking_options', []);
$slave_lang = $options['slave_language'] ?? null;
$multiply_price = !empty($options['multiply_price_qty']) ? 'true' : 'false';
$current_lang = defined('LANG_WPBOOKING') ? LANG_WPBOOKING : null;

$eventos = get_posts([
    'post_type' => 'wpbooking_event',
    'post_status' => 'publish',
    'numberposts' => -1,
    'meta_key' => '_price',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'meta_query' => [
        [
            'key' => '_enabled',
            'value' => '1',
            'compare' => '='
        ]
    ]
]);

$eventos_filtrados = [];

foreach ($eventos as $evento) {
    $lang = apply_filters('wpml_post_language_details', null, $evento->ID);
    if (is_wp_error($lang) || !$lang || !isset($lang['language_code']) || $lang['language_code'] !== $slave_lang) continue;

    // Si no se puede reservar, saltar al siguiente evento
    $can_reserve = get_post_meta($evento->ID, '_can_reserve', true);
    if ($can_reserve !== '1') continue; // Solo eventos que pueden reservarse

    // Título traducido si existe
    $translated_id = apply_filters('wpml_object_id', $evento->ID, 'wpbooking_event', true, $current_lang);
    $translated_post = get_post($translated_id);
    $translated_title = $translated_post ? $translated_post->post_title : $evento->post_title;

    // Guardar el evento con el título traducido
    $evento->_translated_title = $translated_title;

    // Obtenemos las personas asociadas al evento
    $persons = get_posts([
        'post_type' => 'wpbooking_person',
        'posts_per_page' => -1,
        'meta_key' => '_price',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ]);
    // Filtrar las personas asignadas al evento actual
    $event_id = $evento->ID;
    $filtered = array_filter($persons, function ($person) use ($event_id) {
        $assigned = get_post_meta($person->ID, '_assigned_events', true);
        return is_array($assigned) && in_array($event_id, $assigned);
    });

    // Añadir las personas filtradas al evento
    $evento->persons = $filtered;

    $eventos_filtrados[] = $evento;
}


?>
    <div class="single-wpbooking_event">
        <div class="wpbooking-event-tickets">
            <?php if ($eventos_filtrados): ?>
                <div class="wpbooking-personas-tickets">
                    <?php foreach ($eventos_filtrados as $evento):
                        $title = $evento->_translated_title ?: $evento->post_title;
                        $title_calendar = get_post_meta($evento->ID, '_calendar_title', true) ?: $title;
                        $color = get_post_meta($evento->ID, '_color', true) ?: '#ff0000';
                        $textColor = get_post_meta($evento->ID, '_text_color', true) ?: '#000000';
                        ?>
                        <div class="wpbooking-event-ticket" style="background-color: <?= esc_attr($color) ?>; color: <?= esc_attr($textColor) ?>;">
                            <h3><?= esc_html($title) ?></h3>
                            <div class="wpbooking-personas-tickets">
                                <?php if ($evento->persons): ?>
                                    <?php foreach ($evento->persons as $persona):
                                        $persona_id = $persona->ID;

                                        // Nombre persona traducido si existe
                                        $translated_id = apply_filters('wpml_object_id', $persona_id, 'wpbooking_person', true, $current_lang);
                                        $translated_post = get_post($translated_id);
                                        $translated_name = $translated_post ? $translated_post->post_title : $evento->post_title;

                                        $precio = get_post_meta($persona_id, '_price', true);
                                        $min = get_post_meta($persona_id, '_min', true) ?: 0;
                                        $max = get_post_meta($persona_id, '_max', true) ?: 10;
                                        $precio_texto = $precio == 0 ? __wpb('Free') : number_format($precio, 2) . ' €';
                                        ?>
                                        <div class="wpbooking-ticket-row" data-id="<?= esc_attr($persona_id) ?>" data-price="<?= esc_attr($precio) ?>" data-event-name="<?= esc_attr($title) ?>" data-person-name="<?= esc_attr($translated_name) ?>">
                                            <label><?= esc_html($translated_name) ?>: <b><?= esc_html($precio_texto) ?></b></label>
                                            <div class="wpbooking-qty-control">
                                                <button type="button" class="wpb-qty-minus">-</button>
                                                <input type="number" name="personas[<?= esc_attr($persona_id) ?>]" value="<?= $min ?>" min="<?= $min ?>" max="<?= $max ?>" readonly />
                                                <button type="button" class="wpb-qty-plus">+</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p><?= __wpb('No events found') ?></p>
            <?php endif; ?>
        </div>

        <div class="wpbooking-ticket-total">
        <?php if ($eventos_filtrados){ ?>
            <strong><?php echo __wpb('Total people') ?>:</strong>
            <span id="wpbooking-total-count">0</span><br>
        <?php } ?>
            <strong><?php echo __wpb('Total price') ?>:</strong>
            <span id="wpbooking-total-price">0.00 €</span>
        </div>

        <form id="wpbooking-reserve-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="wpbooking_add_to_cart">
            <input type="hidden" name="personas_json" id="wpbooking-personas-json">
            <button type="submit" class="wpbooking-reserve-button"><?php echo __wpb('Reserve') ?></button>
        </form>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            const wpbMultiplyPrice = <?php echo $multiply_price; ?>;

            function updateTotal() {
                let totalPersons = 0;
                let totalPrice = 0;

                // Personas
                $('.wpbooking-ticket-row').each(function () {
                    const input = $(this).find('input[type="number"]');
                    const qty = parseInt(input.val()) || 0;
                    const price = parseFloat($(this).data('price')) || 0;

                    totalPersons += qty;
                    totalPrice += wpbMultiplyPrice ? qty * price : (qty > 0 ? price : 0);
                });

                $('#wpbooking-total-count').text(totalPersons);
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
            updateTotal(); // Inicial

            // Enviar el formulario al reservar
            $('#wpbooking-reserve-form').on('submit', function (e) {
                // Desactivar el boton para evitar múltiples envíos
                $('#wpbooking-reserve-form').find('.wpbooking-reserve-button').prop('disabled', true);
                e.preventDefault();

                const personas = [];

                // Recopilar las personas seleccionadas
                $('.wpbooking-ticket-row').each(function () {
                    const id = $(this).data('id');
                    const eventName = $(this).data('event-name');
                    const personName = $(this).data('person-name');
                    const qty = parseInt($(this).find('input').val()) || 0;

                    if (qty > 0) {
                        personas.push({
                            id: id,
                            qty: qty,
                            event_name: eventName,
                            person_name: personName,
                        });
                    }
                });

                // Validar que haya al menos una persona seleccionada
                if (Object.keys(personas).length === 0) {
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

                // Enviar los datos al servidor
                $.ajax({
                    url: WPBookingData.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpbooking_add_to_cart_gift',
                        personas_json: JSON.stringify(personas),
                    },
                    success: function (res) {
                        if (res.success) {
                            const lang = window.location.pathname.split('/')[1]; // Detecta idioma actual
                            const langPrefix = lang && lang.length === 2 ? `/${lang}` : ''; // Si es 'es', 'ca', etc.
                            window.location.href = `${langPrefix}/checkout/`;
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
                    },
                    // final siempre se ejecuta
                    complete: function () {
                        $('#wpbooking-reserve-form').find('.wpbooking-reserve-button').prop('disabled', false);
                    }
                });
            });

        });
    </script>
<?php



