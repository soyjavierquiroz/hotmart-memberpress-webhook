<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/events/purchase-approved.php';

function hmw_log($msg) {
    $log_file = plugin_dir_path(__FILE__) . '/../hmw-log.txt';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, "$timestamp $msg\n", FILE_APPEND);
}

add_action('plugins_loaded', function () {
    add_action('rest_api_init', function () {
        register_rest_route('hmw/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => 'hmw_main_webhook_handler',
            'permission_callback' => '__return_true',
        ]);
    });
}, 20);

function hmw_main_webhook_handler($request) {
    $body = $request->get_json_params();
    $event = $body['event'] ?? '';
    hmw_log("Webhook recibido con evento: " . $event);

    switch ($event) {
        case 'PURCHASE_APPROVED':
            return hmw_handle_purchase_approved($request);
        default:
            hmw_log("Evento no soportado: " . $event);
            return new WP_REST_Response(['message' => 'Evento no soportado'], 200);
    }
}
