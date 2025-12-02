<?php
/**
 * Handler AJAX robuste pour les paramètres généraux
 * Simplifié et sans dépendances
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Charger les paramètres généraux de façon robuste
function pdf_builder_get_general_settings() {
    return [
        'company_phone_manual' => get_option('pdf_builder_company_phone_manual', ''),
        'company_siret' => get_option('pdf_builder_company_siret', ''),
        'company_vat' => get_option('pdf_builder_company_vat', ''),
        'company_rcs' => get_option('pdf_builder_company_rcs', ''),
        'company_capital' => get_option('pdf_builder_company_capital', ''),
    ];
}

// Sauvegarder les paramètres généraux
function pdf_builder_save_general_settings($data) {
    if (empty($data) || !is_array($data)) {
        return false;
    }

    $allowed_fields = [
        'company_phone_manual',
        'company_siret',
        'company_vat',
        'company_rcs',
        'company_capital',
    ];

    foreach ($allowed_fields as $field) {
        if (isset($data[$field])) {
            update_option(
                'pdf_builder_' . $field,
                sanitize_text_field($data[$field])
            );
        }
    }

    return true;
}

// AJAX handler pour sauvegarder les paramètres généraux
add_action('wp_ajax_pdf_builder_save_general', function() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permissions insuffisantes']);
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings_ajax')) {
        wp_send_json_error(['message' => 'Nonce invalide']);
        return;
    }

    // Récupérer et nettoyer les données
    $data = [];
    $allowed_fields = [
        'company_phone_manual',
        'company_siret',
        'company_vat',
        'company_rcs',
        'company_capital',
    ];

    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            $data[$field] = sanitize_text_field($_POST[$field]);
        }
    }

    // Sauvegarder
    if (pdf_builder_save_general_settings($data)) {
        wp_send_json_success([
            'message' => 'Paramètres généraux sauvegardés avec succès',
            'settings' => pdf_builder_get_general_settings(),
        ]);
    } else {
        wp_send_json_error(['message' => 'Erreur lors de la sauvegarde']);
    }
});
?>
