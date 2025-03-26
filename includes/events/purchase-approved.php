<?php
if (!defined('ABSPATH')) {
    exit;
}

function hmw_handle_purchase_approved($request) {
    hmw_log('PURCHASE_APPROVED handler invocado');

    try {
        $body = $request->get_json_params();
        hmw_log('Payload: ' . json_encode($body));

        $data = $body['data'] ?? [];
        $buyer_email = sanitize_email($data['buyer']['email'] ?? '');
        $offer_code = sanitize_text_field($data['purchase']['offer']['code'] ?? '');

        hmw_log("Comprador: $buyer_email - Oferta: $offer_code");

        if (empty($buyer_email) || empty($offer_code)) {
            hmw_log('Datos incompletos');
            return new WP_REST_Response(['message' => 'Datos incompletos'], 400);
        }

        $map = get_option('hmw_offer_membership_map', []);
        $entry = $map[$offer_code] ?? null;
        $membership_id = is_array($entry) ? ($entry['membership_id'] ?? null) : $entry;

        if (!$membership_id) {
            hmw_log("Oferta no mapeada: $offer_code");
            return new WP_REST_Response(['message' => 'Oferta no mapeada'], 200);
        }

        $user = get_user_by('email', $buyer_email);
        if (!$user) {
            hmw_log("Usuario no existe, creando...");
            $first_name = sanitize_text_field($data['buyer']['first_name'] ?? 'User');
            $last_name = sanitize_text_field($data['buyer']['last_name'] ?? 'Anonimo');
            $username = sanitize_user(strtolower($first_name . $last_name . rand(1000, 9999)));
            $password = wp_generate_password();

            $user_id = wp_create_user($username, $password, $buyer_email);
            if (is_wp_error($user_id)) {
                hmw_log("Error al crear usuario: " . $user_id->get_error_message());
                return new WP_REST_Response(['message' => 'Error al crear usuario'], 500);
            }

            wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);

            $user = get_user_by('id', $user_id);
            hmw_log("Usuario creado con ID: " . $user->ID);
        } else {
            hmw_log("Usuario encontrado con ID: " . $user->ID);
        }

        if (!class_exists('MeprTransaction')) {
            hmw_log("ERROR: MeprTransaction no disponible");
            return new WP_REST_Response(['message' => 'MemberPress no disponible (MeprTransaction)'], 500);
        }

        $txn = new MeprTransaction();
        $txn->user_id = $user->ID;
        $txn->product_id = $membership_id;
        $txn->status = MeprTransaction::$complete_str;
        $txn->gateway = 'manual';
        $txn->trans_num = uniqid('hmw_', true);
        $txn->expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
        $txn->store();

        hmw_log("Membresía asignada: $membership_id");

        return new WP_REST_Response(['message' => 'Membresía asignada'], 200);
    } catch (Throwable $e) {
        hmw_log("EXCEPCIÓN: " . $e->getMessage());
        return new WP_REST_Response(['message' => 'Error interno del servidor'], 500);
    }
}
