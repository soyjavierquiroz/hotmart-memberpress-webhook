<?php
/**
 * Plugin Name: Hotmart to MemberPress Webhook (Estable)
 * Description: Versión estable solo con PURCHASE_APPROVED funcional.
 * Version: 0.2.1
 * Author: Javier Quiroz
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('HMW_PLUGIN_DIR')) {
    define('HMW_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('HMW_PLUGIN_URL')) {
    define('HMW_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once HMW_PLUGIN_DIR . 'includes/admin-settings.php';
require_once HMW_PLUGIN_DIR . 'includes/webhook-handler.php';

register_activation_hook(__FILE__, function () {});
register_deactivation_hook(__FILE__, function () {});
