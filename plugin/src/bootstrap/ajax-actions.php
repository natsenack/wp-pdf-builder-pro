<?php
/**
 * PDF Builder Pro - AJAX Actions Module
 * Actions AJAX pour les paramètres (déplacées depuis settings-main.php)
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// ACTIONS AJAX POUR LES PARAMÈTRES (DÉPLACÉES DEPUIS settings-main.php)
// ============================================================================

// Gestionnaire AJAX des paramètres développeur
add_action('wp_ajax_pdf_builder_developer_save_settings', function() {
    // error_log('PDF Builder Développeur: Gestionnaire AJAX DÉMARRÉ à ' . date('Y-m-d H:i:s'));

    try {
        // Journaliser toutes les données POST pour le débogage
        // error_log('PDF Builder Développeur: Données POST reçues: ' . print_r($_POST, true));

        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        $nonce_valid = wp_verify_nonce($nonce_value, 'pdf_builder_settings_ajax');
        // error_log('PDF Builder Développeur: Résultat de vérification du nonce: ' . ($nonce_valid ? 'VALIDE' : 'INVALIDE'));

        if (!$nonce_valid) {
            // error_log('PDF Builder Développeur: Échec de vérification du nonce');
            wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
            return;
        }

        // Vérifier la capacité utilisateur
        $has_capability = current_user_can('manage_options');
        // error_log('PDF Builder Développeur: Vérification de capacité utilisateur: ' . ($has_capability ? 'A' : 'NON'));

        if (!$has_capability) {
            // error_log('PDF Builder Développeur: Permissions insuffisantes');
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Obtenir la clé et la valeur du paramètre
        $setting_key = sanitize_text_field($_POST['setting_key'] ?? '');
        $setting_value = sanitize_text_field($_POST['setting_value'] ?? '');

        // error_log("PDF Builder Développeur: Clé paramètre: '{$setting_key}', valeur: '{$setting_value}'");

        // Valider la clé de paramètre (autoriser seulement les paramètres développeur)
        $allowed_keys = [
            'pdf_builder_developer_enabled',
            'pdf_builder_canvas_debug_enabled',
            'pdf_builder_developer_password'
        ];

        if (!in_array($setting_key, $allowed_keys)) {
            // error_log("PDF Builder Développeur: Clé paramètre invalide: {$setting_key}");
            wp_send_json_error(['message' => 'Clé paramètre invalide']);
            return;
        }

        // Obtenir les paramètres existants
        $settings = get_option('pdf_builder_settings', []);

        // Mettre à jour le paramètre spécifique
        $settings[$setting_key] = $setting_value;

        // Sauvegarder en base de données
        $updated = update_option('pdf_builder_settings', $settings);
        // error_log("PDF Builder Développeur: Résultat update_option: " . ($updated ? 'SUCCÈS' : 'AUCUN CHANGEMENT'));

        wp_send_json_success([
            'message' => 'Paramètre développeur sauvegardé avec succès',
            'setting' => $setting_key,
            'value' => $setting_value
        ]);

    } catch (Exception $e) {
        // error_log('PDF Builder Développeur: Erreur AJAX - ' . $e->getMessage());
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});