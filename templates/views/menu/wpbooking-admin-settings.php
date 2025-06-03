<?php
$options = get_option('wpbooking_options', []);
?>

<div class="wrap">
    <h1>Configuración de WPBooking</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('wpbooking_settings');
        do_settings_sections('wpbooking_settings');
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo __wpb('Base language for events') ?></th>
                <td>
                    <select name="wpbooking_options[slave_language]">
                        <?php
                        if (function_exists('icl_get_languages')) {
                            $langs = icl_get_languages();
                            foreach ($langs as $lang) {
                                $selected = selected($options['slave_language'] ?? '', $lang['language_code'], false);
                                echo "<option value='{$lang['language_code']}' {$selected}>{$lang['translated_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                    <p class="description">
                        <?php echo __wpb('Only events created in this language will be used. Content will be shown translated.'); ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __wpb('Events on individual dates') ?></th>
                <td>
                    <input type="checkbox" name="wpbooking_options[individual_days]" value="1"
                        <?php checked(1, $options['individual_days'] ?? 0); ?> />
                    <label for="individual_days">
                        <?php echo __wpb('Show the same event on individual dates') ?>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __wpb('Block reservation on current day') ?></th>
                <td>
                    <input type="checkbox" name="wpbooking_options[block_current_day]" value="1"
                        <?php checked(1, $options['block_current_day'] ?? 0); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __wpb('Multiply person price by quantity'); ?></th>
                <td>
                    <input type="checkbox" name="wpbooking_options[multiply_price_qty]" value="1"
                        <?php checked(1, $options['multiply_price_qty'] ?? 0); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __wpb('Multiply service price by quantity'); ?></th>
                <td>
                    <input type="checkbox" name="wpbooking_options[multiply_service_price_qty]" value="1"
                        <?php checked(1, $options['multiply_service_price_qty'] ?? 0); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __wpb('Specify a price per day of parking'); ?></th>
                <td>
                    <input type="text" name="wpbooking_options[parking_price_per_day]" value="<?php echo esc_attr($options['parking_price_per_day'] ?? ''); ?>" />
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>

<style>
    .wpbooking-settings {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        max-width: 700px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .wpbooking-settings h1 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .wpbooking-settings table.form-table th {
        width: 300px;
        font-weight: 600;
    }

    .wpbooking-settings table.form-table td {
        padding: 10px 0;
    }

    .wpbooking-settings input[type="checkbox"] {
        transform: scale(1.2);
        margin-right: 10px;
    }

    .wpbooking-settings .submit input {
        background-color: #007cba;
        border: none;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
    }

</style>
