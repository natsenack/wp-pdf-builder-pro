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

// ============================================================================
// ACTIONS AJAX POUR LA PERFORMANCE DES ASSETS
// ============================================================================

// Gestionnaire AJAX pour la compression des assets
add_action('wp_ajax_pdf_builder_compress_assets', function() {
    try {
        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce_value, 'pdf_builder_asset_performance')) {
            wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Obtenir la liste des assets à compresser
        $assets_json = $_POST['assets'] ?? '';
        $assets = json_decode($assets_json, true);

        if (!is_array($assets)) {
            wp_send_json_error(['message' => 'Liste d\'assets invalide']);
            return;
        }

        // Obtenir le compresseur d'assets
        $compressor = pdf_builder_get_asset_compressor();

        if (!$compressor) {
            wp_send_json_error(['message' => 'Compresseur d\'assets non disponible']);
            return;
        }

        $compressed_count = 0;
        $errors = [];

        // Compresser chaque asset
        foreach ($assets as $asset_url) {
            try {
                // Déterminer le type d'asset
                $type = strpos($asset_url, '.js') !== false ? 'js' : 'css';

                // Obtenir l'URL compressée
                $compressed_url = $compressor->get_compressed_asset($asset_url, $type);

                if ($compressed_url && $compressed_url !== $asset_url) {
                    $compressed_count++;
                } else {
                    $errors[] = "Impossible de compresser: {$asset_url}";
                }
            } catch (Exception $e) {
                $errors[] = "Erreur pour {$asset_url}: " . $e->getMessage();
            }
        }

        wp_send_json_success([
            'message' => 'Compression des assets terminée',
            'compressed_count' => $compressed_count,
            'total_count' => count($assets),
            'errors' => $errors
        ]);

    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});

// Gestionnaire AJAX pour obtenir les statistiques de performance
add_action('wp_ajax_pdf_builder_get_asset_stats', function() {
    try {
        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce_value, 'pdf_builder_asset_performance')) {
            wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Obtenir les statistiques
        $stats = pdf_builder_get_asset_stats();

        wp_send_json_success([
            'stats' => $stats,
            'timestamp' => time()
        ]);

    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});

// ============================================================================
// ACTIONS AJAX POUR LA GESTION DE LA SÉCURITÉ
// ============================================================================

// Gestionnaire AJAX pour obtenir le rapport d'audit de sécurité
add_action('wp_ajax_pdf_builder_get_security_audit', function() {
    try {
        // Vérifier les permissions (admin seulement)
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce_value, 'pdf_builder_security')) {
            wp_send_json_error(['message' => 'Requête non autorisée']);
            return;
        }

        // Obtenir le rapport d'audit
        $audit = pdf_builder_get_security_audit();
        $report = $audit->generate_report();
        $vulnerability_count = $audit->get_vulnerability_count();

        wp_send_json_success([
            'report' => $report,
            'vulnerability_count' => $vulnerability_count,
            'audit_results' => $audit->get_audit_results()
        ]);

    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});

// Gestionnaire AJAX pour obtenir les logs de sécurité
add_action('wp_ajax_pdf_builder_get_security_logs', function() {
    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce_value, 'pdf_builder_security')) {
            wp_send_json_error(['message' => 'Requête non autorisée']);
            return;
        }

        // Paramètres de filtrage
        $filters = [];
        if (!empty($_POST['event_type'])) {
            $filters['event_type'] = sanitize_text_field($_POST['event_type']);
        }
        if (!empty($_POST['severity'])) {
            $filters['severity'] = sanitize_text_field($_POST['severity']);
        }
        if (!empty($_POST['date_from'])) {
            $filters['date_from'] = sanitize_text_field($_POST['date_from']);
        }
        if (!empty($_POST['date_to'])) {
            $filters['date_to'] = sanitize_text_field($_POST['date_to']);
        }

        $limit = intval($_POST['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? 0);

        // Obtenir les logs
        $logs = pdf_builder_get_security_logs($filters, $limit, $offset);
        $stats = pdf_builder_get_security_log_stats();

        wp_send_json_success([
            'logs' => $logs,
            'stats' => $stats,
            'total' => count($logs)
        ]);

    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});

// Gestionnaire AJAX pour mettre à jour les paramètres de sécurité
add_action('wp_ajax_pdf_builder_update_security_settings', function() {
    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Vérifier le nonce
        $nonce_value = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce_value, 'pdf_builder_security')) {
            wp_send_json_error(['message' => 'Requête non autorisée']);
            return;
        }

        // Obtenir les paramètres
        $settings = $_POST['settings'] ?? [];
        if (!is_array($settings)) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
            return;
        }

        // Valider et nettoyer les paramètres
        $validated_settings = [];
        foreach ($settings as $key => $value) {
            $key = sanitize_text_field($key);

            // Validation selon le type de paramètre
            switch ($key) {
                case 'rate_limiting_enabled':
                case 'input_validation_strict':
                case 'security_logging_enabled':
                case 'enable_csp':
                    $validated_settings[$key] = (bool) $value;
                    break;

                case 'max_requests_per_minute':
                case 'max_file_size':
                case 'max_execution_time':
                case 'memory_limit':
                    $validated_settings[$key] = absint($value);
                    break;

                case 'allowed_file_types':
                    if (is_array($value)) {
                        $validated_settings[$key] = array_map('sanitize_text_field', $value);
                    }
                    break;

                case 'csp_directives':
                    $validated_settings[$key] = sanitize_textarea_field($value);
                    break;

                default:
                    // Ignorer les paramètres inconnus
                    break;
            }
        }

        // Mettre à jour les paramètres
        $hardener = pdf_builder_get_security_hardener();
        $success = $hardener->update_security_settings($validated_settings);

        if ($success) {
            // Log l'événement
            pdf_builder_log_security_event('security_settings_updated', [
                'updated_settings' => array_keys($validated_settings),
                'user_id' => get_current_user_id()
            ], 'medium');

            wp_send_json_success([
                'message' => 'Paramètres de sécurité mis à jour avec succès',
                'updated_settings' => $validated_settings
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la mise à jour des paramètres']);
        }

    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
});