<?php
$options = get_option('wpbooking_options', []);
$price = !empty($options['parking_price_per_day']) ? floatval($options['parking_price_per_day']) : 0;

?>

<div class="single-wpbooking_event">
    <p class="price">
        <span>
            <b><?php echo __wpb('Price'); ?>:</b>
            <bdi><?php echo esc_html($price); ?><span>€</span></bdi>
        </span>
        <span class="lower"> / <?php echo __wpb('Day'); ?></span>
    </p>

    <div class="wpbooking-event-tickets">
        <div class="wpbooking-event-tickets-title">
            <h2><?php echo __wpb('Tickets') ?></h2>
        </div>

        <div class="wpbooking-personas-tickets">
            <div class="wpbooking-parking-row">
                <label for="date_range"><?php echo __wpb('Select dates'); ?>:</label>
                <input type="text" id="date_range" placeholder="DD/MM/YYYY a DD/MM/YYYY" />
            </div>
            <div class="wpbooking-parking-row">
                <label for="number_plate"><?php echo __wpb('Number plate'); ?>:</label>
                <input type="text" id="number_plate" />
            </div>
        </div>

    </div>

    <div class="wpbooking-ticket-total">
        <strong><?php echo __wpb('Total days') ?>:</strong>
        <span id="wpbooking-total-count">0</span><br>
        <strong><?php echo __wpb('Total price') ?>:</strong>
        <span id="wpbooking-total-price">0.00 €</span>
    </div>

    <form id="wpbooking-reserve-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="wpbooking_add_to_cart">
        <input type="hidden" name="start_date" id="wpbooking-start-date">
        <input type="hidden" name="end_date" id="wpbooking-end-date">
        <input type="hidden" name="total_days" id="wpbooking-total-days">
        <input type="hidden" name="number_plate" id="wpbooking-number-plate">
        <button type="submit" class="wpbooking-reserve-button"><?php echo __wpb('Reserve') ?></button>
    </form>
</div>

<script>
    var locale = <?php echo json_encode(LANG_WPBOOKING); ?>;
    if (locale === 'ca') {
        locale = 'cat';
    }

    jQuery(document).ready(function ($) {
        flatpickr("#date_range", {
            mode: "range",
            dateFormat: "d/m/Y",
            locale: locale,
            onChange: function (selectedDates) {
                if (selectedDates.length === 2) {
                    const start = selectedDates[0];
                    const end = selectedDates[1];
                    const diffTime = Math.abs(end - start);
                    const totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const total = totalDays * <?php echo $price; ?>;

                    $('#wpbooking-start-date').val(start.toLocaleDateString('es-ES'));
                    $('#wpbooking-end-date').val(end.toLocaleDateString('es-ES'));

                    $('#wpbooking-total-price').text(total.toFixed(2) + ' €');
                    $('#wpbooking-total-count').text(totalDays);
                    $('#wpbooking-total-days').val(totalDays);
                }
            }
        });

        // Enviar el formulario al reservar
        $('#wpbooking-reserve-form').on('submit', function (e) {
            // Desactivar el boton para evitar múltiples envíos
            $('#wpbooking-reserve-form').find('.wpbooking-reserve-button').prop('disabled', true);
            e.preventDefault();

            // Recopilar la matricula
            const numberPlate = $('#number_plate').val().trim();
            if (numberPlate === '' || $('#wpbooking-total-days').val() == 0) {
                Swal.fire({
                    title: "",
                    text: WPBookingData.error_dates_and_plate_number,
                    icon: "warning",
                    customClass: {
                        popup: 'swal-custom'
                    }
                });
                $('#wpbooking-reserve-form').find('.wpbooking-reserve-button').prop('disabled', false);
                return;
            }

            // Enviar los datos al servidor
            $.ajax({
                url: WPBookingData.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpbooking_add_to_cart_parking',
                    total_days: $('#wpbooking-total-days').val(),
                    selected_days: $('#wpbooking-start-date').val() + ' - ' + $('#wpbooking-end-date').val(),
                    number_plate: numberPlate,
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

