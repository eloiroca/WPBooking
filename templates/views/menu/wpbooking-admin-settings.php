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
                <th scope="row"><?php echo __wpb('Events on individual dates') ?></th>
                <td>
                    <input type="checkbox" name="wpbooking_options[individual_days]" value="1"
                        <?php checked(1, $options['individual_days'] ?? 0); ?> />
                    <label for="individual_days">
                        <?php echo __wpb('Show the same event on individual dates') ?>
                    </label>
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
