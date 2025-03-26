<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'hmw_register_settings_page');

function hmw_register_settings_page() {
    add_menu_page(
        'Hotmart Webhook',
        'Hotmart Webhook',
        'manage_options',
        'hmw-settings',
        'hmw_render_settings_page',
        'dashicons-rest-api',
        90
    );
}

function hmw_render_settings_page() {
    if (isset($_POST['hmw_save_settings'])) {
        check_admin_referer('hmw_save_settings');

        $offer_codes = array_map('sanitize_text_field', $_POST['hmw_mappings'] ?? []);
        $memberpress_ids = array_map('intval', $_POST['hmw_membership_ids'] ?? []);
        $product_names = array_map('sanitize_text_field', $_POST['hmw_product_names'] ?? []);

        $saved = [];
        foreach ($offer_codes as $index => $offer_code) {
            if (!empty($offer_code) && !empty($memberpress_ids[$index])) {
                $saved[$offer_code] = [
                    'membership_id' => $memberpress_ids[$index],
                    'product_name' => $product_names[$index] ?? ''
                ];
            }
        }

        update_option('hmw_offer_membership_map', $saved);
        echo '<div class="updated"><p>Configuración guardada.</p></div>';
    }

    $mappings = get_option('hmw_offer_membership_map', []);
    $membership_posts = get_posts(['post_type' => 'memberpressproduct', 'numberposts' => -1]);
?>
<div class="wrap">
    <h1>Configuración Hotmart → MemberPress</h1>
    <form method="post">
        <?php wp_nonce_field('hmw_save_settings'); ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Offer Code</th>
                    <th>Membresía</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody id="hmw-mapping-rows">
                <?php foreach ($mappings as $offer => $config): ?>
                    <tr>
                        <td><input type="text" name="hmw_product_names[]" value="<?php echo esc_attr($config['product_name'] ?? ''); ?>"></td>
                        <td><input type="text" name="hmw_mappings[]" value="<?php echo esc_attr($offer); ?>"></td>
                        <td>
                            <select name="hmw_membership_ids[]">
                                <?php foreach ($membership_posts as $post): ?>
                                    <option value="<?php echo $post->ID; ?>" <?php selected($config['membership_id'] ?? '', $post->ID); ?>>
                                        <?php echo esc_html($post->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><button type="button" class="button hmw-remove-row">Eliminar</button></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td><input type="text" name="hmw_product_names[]" value=""></td>
                    <td><input type="text" name="hmw_mappings[]" value=""></td>
                    <td>
                        <select name="hmw_membership_ids[]">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($membership_posts as $post): ?>
                                <option value="<?php echo $post->ID; ?>"><?php echo esc_html($post->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><button type="button" class="button hmw-remove-row">Eliminar</button></td>
                </tr>
            </tbody>
        </table>
        <p><button type="button" class="button" id="hmw-add-row">Agregar mapeo</button></p>
        <p><input type="submit" class="button-primary" name="hmw_save_settings" value="Guardar configuración"></p>
    </form>
</div>
<script>
document.getElementById('hmw-add-row').addEventListener('click', function() {
    const tbody = document.getElementById('hmw-mapping-rows');
    const row = tbody.querySelector('tr').cloneNode(true);
    row.querySelectorAll('input, select').forEach(input => input.value = '');
    tbody.appendChild(row);
});
document.querySelectorAll('.hmw-remove-row').forEach(btn => {
    btn.addEventListener('click', function() {
        if (document.querySelectorAll('#hmw-mapping-rows tr').length > 1) {
            this.closest('tr').remove();
        }
    });
});
</script>
<?php } ?>
