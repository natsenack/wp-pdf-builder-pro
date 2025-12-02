<?php
/**
 * PDF Builder Pro - AJAX Handlers
 * All AJAX request processing for settings page
 * Updated: 2025-11-30 14:25:00
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Function to send AJAX response
function send_ajax_response($success, $message = '', $data = [])
{
    $log_file = WP_CONTENT_DIR . '/pdf-builder-debug.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - send_ajax_response called: success=$success, message=$message, data_keys=" . implode(',', array_keys($data)) . "\n", FILE_APPEND);

    if ($success) {
        $merged = array_merge(['message' => $message], $data);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Sending SUCCESS response with data: " . json_encode($merged) . "\n", FILE_APPEND);
        wp_send_json_success($merged);
    } else {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Sending ERROR response: $message\n", FILE_APPEND);
        wp_send_json_error(['message' => $message]);
    }
}

// Helper to validate nonce accepting both action-specific and central 'pdf_builder_ajax' nonces
function is_valid_pdf_builder_nonce($nonce, $action = '') {
    $action = $action ?: 'pdf_builder_ajax';
    // Accept both the action-specific nonce (legacy) or the central pdf_builder_ajax nonce
    if (!empty($nonce) && (wp_verify_nonce($nonce, $action) || wp_verify_nonce($nonce, 'pdf_builder_ajax'))) {
        return true;
    }
    return false;
}

/**
 * Système centralisé de gestion des réponses AJAX
 */
class PDF_Builder_Ajax_Response_Manager {

    /**
     * Configuration des réponses par type d'action
     */
    private static $response_configs = [
        'save_settings' => [
            'success_message' => 'Tous les paramètres ont été sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres.',
            'include_saved_options' => true
        ],
        'save_canvas' => [
            'success_message' => 'Paramètres canvas sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres canvas.',
            'include_saved_options' => false
        ],
        'save_dimensions' => [
            'success_message' => 'Dimensions sauvegardées avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des dimensions.',
            'include_saved_options' => false
        ],
        'save_apparence' => [
            'success_message' => 'Paramètres d\'apparence sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres d\'apparence.',
            'include_saved_options' => false
        ],
        'save_grille' => [
            'success_message' => 'Paramètres de grille sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de grille.',
            'include_saved_options' => false
        ],
        'save_interaction' => [
            'success_message' => 'Paramètres d\'interaction sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres d\'interaction.',
            'include_saved_options' => false
        ],
        'save_performance' => [
            'success_message' => 'Paramètres de performance sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de performance.',
            'include_saved_options' => false
        ],
        'save_securite' => [
            'success_message' => 'Paramètres de sécurité sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de sécurité.',
            'include_saved_options' => false
        ],
        'save_contenu' => [
            'success_message' => 'Paramètres de contenu sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de contenu.',
            'include_saved_options' => false
        ]
    ];

    /**
     * Envoie une réponse AJAX de succès
     */
    public static function send_success($action, $data = []) {
        $config = self::$response_configs[$action] ?? [
            'success_message' => 'Opération réussie.',
            'include_saved_options' => false
        ];

        $response_data = $data;

        if ($config['include_saved_options']) {
            $response_data['saved_options'] = self::get_saved_options();
        }

        send_ajax_response(true, $config['success_message'], $response_data);
    }

    /**
     * Envoie une réponse AJAX d'erreur
     */
    public static function send_error($action, $custom_message = null) {
        $config = self::$response_configs[$action] ?? [
            'error_message' => 'Une erreur est survenue.'
        ];

        $message = $custom_message ?: $config['error_message'];
        send_ajax_response(false, $message);
    }

    /**
     * Récupère les options sauvegardées pour la réponse
     */
    private static function get_saved_options() {
        return [
            'pdf_metadata_enabled' => get_option('pdf_builder_pdf_metadata_enabled', 0) ? '1' : '0',
            'pdf_print_optimized' => get_option('pdf_builder_pdf_print_optimized', 0) ? '1' : '0',
            'pdf_cache_enabled' => get_option('pdf_builder_pdf_cache_enabled', 0) ? '1' : '0',
            'pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
            'pdf_page_size' => get_option('pdf_builder_pdf_page_size', 'A4'),
            'pdf_orientation' => get_option('pdf_builder_pdf_orientation', 'portrait'),
            'pdf_compression' => get_option('pdf_builder_pdf_compression', 'medium'),
            'debug_mode' => get_option('pdf_builder_debug_mode', 0) ? '1' : '0',
            'cache_enabled' => get_option('pdf_builder_cache_enabled', 1) ? '1' : '0',
            'systeme_auto_maintenance' => get_option('pdf_builder_auto_maintenance', 1) ? '1' : '0',
            'systeme_auto_backup' => get_option('pdf_builder_auto_backup', 1) ? '1' : '0',
            'gdpr_enabled' => get_option('pdf_builder_gdpr_enabled', 0) ? '1' : '0'
        ];
    }
}

/**
 * Système centralisé de gestion des options WordPress
 */
class PDF_Builder_Options_Manager {

    /**
     * Configuration des options et leurs validateurs
     */
    private static $option_configs = [
        // Company Info
        'pdf_builder_company_phone_manual' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_company_siret' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_company_vat' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_company_rcs' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_company_capital' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],

        // Cache
        'pdf_builder_cache_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_cache_compression' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_cache_auto_cleanup' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_cache_max_size' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_cache_ttl' => ['type' => 'int', 'sanitize' => 'intval'],

        // Maintenance
        'pdf_builder_auto_maintenance' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_performance_auto_optimization' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_auto_backup' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_backup_retention' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_auto_backup_frequency' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],

        // Security
        'pdf_builder_allowed_roles' => ['type' => 'array'],
        'pdf_builder_security_level' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_enable_logging' => ['type' => 'boolean', 'sanitize' => 'intval'],

        // GDPR
        'pdf_builder_gdpr_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_consent_required' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_data_retention' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_audit_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_encryption_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_consent_analytics' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_consent_templates' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_gdpr_consent_marketing' => ['type' => 'boolean', 'sanitize' => 'intval'],

        // PDF Settings
        'pdf_builder_pdf_quality' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_pdf_page_size' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_pdf_orientation' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_pdf_cache_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_pdf_compression' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_pdf_metadata_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_pdf_print_optimized' => ['type' => 'boolean', 'sanitize' => 'intval'],

        // Templates
        'pdf_builder_default_template' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_template_library_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],

        // Developer Settings
        'pdf_builder_developer_enabled' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_developer_password' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_license_test_mode' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_debug_php_errors' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_debug_javascript' => ['type' => 'boolean', 'sanitize' => 'intval'],
        // Canvas Settings
        'pdf_builder_canvas_width' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_canvas_height' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_canvas_settings' => ['type' => 'array'],

        // Order Status Templates
        'pdf_builder_order_status_templates' => ['type' => 'array'],
    ];

    /**
     * Sauvegarde une option avec validation automatique
     */
    public static function save_option($key, $value) {
        if (!isset(self::$option_configs[$key])) {
            error_log("PDF Builder: Option inconnue '$key'");
            return false;
        }

        $config = self::$option_configs[$key];

        // Validation et sanitisation
        $sanitized_value = self::sanitize_value($value, $config);

        // Sauvegarde
        $result = update_option($key, $sanitized_value);

        if ($result) {
            error_log("PDF Builder: Option '$key' sauvegardée avec succès");
        }

        return $result;
    }

    /**
     * Sanitise une valeur selon sa configuration
     */
    private static function sanitize_value($value, $config) {
        // Gestion des checkboxes (string '1'/'0' vers boolean)
        if ($config['type'] === 'boolean') {
            $value = $value === '1' || $value === 1 || $value === true;
        }

        // Application de la fonction de sanitisation
        if (isset($config['sanitize']) && function_exists($config['sanitize'])) {
            $value = call_user_func($config['sanitize'], $value);
        }

        // Conversion finale pour les types spécifiques
        switch ($config['type']) {
            case 'boolean':
                return $value ? 1 : 0;
            case 'int':
                return intval($value);
            case 'array':
                return is_array($value) ? $value : [];
            default:
                return $value;
        }
    }

    /**
     * Sauvegarde multiple d'options
     */
    public static function save_options($options) {
        $saved = [];
        $errors = [];

        foreach ($options as $key => $value) {
            try {
                if (self::save_option($key, $value)) {
                    $saved[] = $key;
                } else {
                    $errors[] = $key;
                }
            } catch (Exception $e) {
                $errors[] = $key;
                error_log("PDF Builder: Erreur sauvegarde option '$key': " . $e->getMessage());
            }
        }

        return ['saved' => $saved, 'errors' => $errors];
    }
}

// AJAX Handlers
function pdf_builder_update_cache_metrics_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        send_ajax_response(false, 'Permissions insuffisantes');
        return;
    }

    try {
        // Calculate cache metrics
        $cache_dirs = [
            WP_CONTENT_DIR . '/cache/wp-pdf-builder-previews/',
            wp_upload_dir()['basedir'] . '/pdf-builder-cache'
        ];

        $total_files = 0;
        $total_size = 0;

        foreach ($cache_dirs as $cache_dir) {
            if (is_dir($cache_dir)) {
                $files = glob($cache_dir . '*');
                $total_files += count($files);
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $total_size += filesize($file);
                    }
                }
            }
        }

        // Format size display
        $size_display = '';
        if ($total_size < 1048576) {
            $size_display = number_format($total_size / 1024, 1) . ' Ko';
        } else {
            $size_display = number_format($total_size / 1048576, 1) . ' Mo';
        }

        // Get cache settings
        $cache_enabled = get_option('pdf_builder_cache_enabled', 1) ? 'Activé' : 'Désactivé';
        $cache_compression = get_option('pdf_builder_cache_compression', 0) ? 'Activée' : 'Désactivée';
        $cache_auto_cleanup = get_option('pdf_builder_cache_auto_cleanup', 0) ? 'Activé' : 'Désactivé';
        $cache_max_size = get_option('pdf_builder_cache_max_size', 100) . ' Mo';
        $cache_ttl = get_option('pdf_builder_cache_ttl', 0) . ' heures';

        send_ajax_response(true, 'Métriques du cache mises à jour.', [
            'cache_size' => $size_display,
            'cache_files' => $total_files,
            'cache_status' => $cache_enabled,
            'cache_compression' => $cache_compression,
            'cache_auto_cleanup' => $cache_auto_cleanup,
            'cache_max_size' => $cache_max_size,
            'cache_ttl' => $cache_ttl,
            'last_updated' => current_time('H:i:s')
        ]);

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors de la récupération des métriques: ' . $e->getMessage());
    }
}

// Test License Handler
function pdf_builder_test_license_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Get license key from settings
    $license_key = get_option('pdf_builder_license_key', '');
    $license_status = get_option('pdf_builder_license_status', 'inactive');

    if (empty($license_key)) {
        send_ajax_response(false, 'Aucune clé de licence configurée.');
        return;
    }

    // Simulate license validation (in real implementation, this would call the license server)
    $is_valid = !empty($license_key) && strlen($license_key) > 10;

    if ($is_valid) {
        send_ajax_response(true, 'Licence valide et active.', [
            'license_key' => substr($license_key, 0, 10) . '...',
            'status' => 'active',
            'expires' => date('Y-m-d', strtotime('+1 year'))
        ]);
    } else {
        send_ajax_response(false, 'Licence invalide ou expirée.');
    }
}

// Test Routes Handler
function pdf_builder_test_routes_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $routes_tested = [];
    $failed_routes = [];

    // Test basic admin routes
    $admin_routes = [
        'admin.php?page=pdf-builder-settings' => 'Page principale des paramètres',
        'admin-ajax.php' => 'Endpoint AJAX WordPress'
    ];

    foreach ($admin_routes as $route => $description) {
        $url = admin_url($route);
        $response = wp_remote_head($url, ['timeout' => 5]);

        if (is_wp_error($response)) {
            $failed_routes[] = $route . ' (' . $response->get_error_message() . ')';
        } else {
            $routes_tested[] = $route . ' (OK)';
        }
    }

    if (empty($failed_routes)) {
        send_ajax_response(true, 'Toutes les routes sont accessibles.', ['routes_tested' => $routes_tested]);
    } else {
        send_ajax_response(false, 'Routes inaccessibles détectées.', [
            'routes_tested' => $routes_tested,
            'failed_routes' => $failed_routes
        ]);
    }
}

// Generate Test License Key Handler
function pdf_builder_generate_test_license_key_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (supporte le nonce central pdf_builder_ajax)
    $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    if (!is_valid_pdf_builder_nonce($nonce, 'pdf_builder_generate_test_license_key')) {
        send_ajax_response(false, 'Nonce invalide');
        return;
    }

    try {
        // Générer une clé de test aléatoire
        $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(wp_rand(), true)), 0, 16));

        // Sauvegarder la clé de test
        update_option('pdf_builder_license_test_key', $test_key);

        send_ajax_response(true, 'Clé de test générée avec succès.', [
            'license_key' => $test_key
        ]);

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors de la génération de la clé: ' . $e->getMessage());
    }
}

// Delete Test License Key Handler
function pdf_builder_delete_test_license_key_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (supporte le nonce central pdf_builder_ajax)
    $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    if (!is_valid_pdf_builder_nonce($nonce, 'pdf_builder_delete_test_license_key')) {
        send_ajax_response(false, 'Nonce invalide');
        return;
    }

    try {
        // Supprimer la clé de test
        delete_option('pdf_builder_license_test_key');

        send_ajax_response(true, 'Clé de test supprimée avec succès.');

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors de la suppression de la clé: ' . $e->getMessage());
    }
}

// Validate Test License Key Handler
function pdf_builder_validate_test_license_key_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce (supporte le nonce central pdf_builder_ajax)
    $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    if (!is_valid_pdf_builder_nonce($nonce, 'pdf_builder_validate_test_license_key')) {
        send_ajax_response(false, 'Nonce invalide');
        return;
    }

    try {
        // Récupérer la clé de test actuelle
        $test_key = get_option('pdf_builder_license_test_key', '');

        if (empty($test_key)) {
            send_ajax_response(false, 'Aucune clé de test configurée.');
            return;
        }

        // Simuler une validation (en production, cela contacterait le serveur de licences)
        $is_valid = !empty($test_key) && strpos($test_key, 'TEST-') === 0;

        if ($is_valid) {
            send_ajax_response(true, 'Clé de test valide.', [
                'license_key' => substr($test_key, 0, 10) . '...',
                'status' => 'active',
                'type' => 'test',
                'expires' => date('Y-m-d', strtotime('+30 days'))
            ]);
        } else {
            send_ajax_response(false, 'Clé de test invalide.');
        }

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors de la validation: ' . $e->getMessage());
    }
}

// Clear Temp Files Handler
function pdf_builder_clear_temp_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $temp_dirs = [
            WP_CONTENT_DIR . '/pdf-builder-temp/',
            get_temp_dir() . '/pdf-builder/'
        ];

        $cleared_files = 0;
        $total_size = 0;

        foreach ($temp_dirs as $temp_dir) {
            if (is_dir($temp_dir)) {
                $files = glob($temp_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $file_size = filesize($file);
                        if (unlink($file)) {
                            $cleared_files++;
                            $total_size += $file_size;
                        }
                    }
                }
            }
        }

        // Also clear old temp files (older than 24 hours)
        $upload_dir = wp_upload_dir();
        $temp_pattern = $upload_dir['basedir'] . '/pdf-builder-temp-*';
        $temp_files = glob($temp_pattern);

        foreach ($temp_files as $temp_file) {
            if (is_file($temp_file) && (time() - filemtime($temp_file)) > 86400) { // 24 hours
                $file_size = filesize($temp_file);
                if (unlink($temp_file)) {
                    $cleared_files++;
                    $total_size += $file_size;
                }
            }
        }

        send_ajax_response(true, "Fichiers temporaires nettoyés: $cleared_files fichier(s) supprimé(s), " . size_format($total_size) . ' libéré(s).');

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors du nettoyage des fichiers temporaires: ' . $e->getMessage());
    }
}

// Refresh Logs Handler
function pdf_builder_refresh_logs_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $log_files = [];
        $log_dirs = [
            WP_CONTENT_DIR . '/pdf-builder-logs/',
            wp_upload_dir()['basedir'] . '/pdf-builder-logs/'
        ];

        $logs_content = '';
        $max_lines = 100; // Limit to last 100 lines for performance

        foreach ($log_dirs as $log_dir) {
            if (is_dir($log_dir)) {
                $files = glob($log_dir . '*.log');
                foreach ($files as $file) {
                    if (is_file($file) && filesize($file) > 0) {
                        $lines = file($file);
                        $recent_lines = array_slice($lines, -$max_lines);
                        $logs_content .= "=== " . basename($file) . " ===\n";
                        $logs_content .= implode('', $recent_lines);
                        $logs_content .= "\n\n";
                    }
                }
            }
        }

        if (empty($logs_content)) {
            $logs_content = "Aucun log trouvé ou les logs sont vides.";
        }

        send_ajax_response(true, 'Logs actualisés avec succès.', [
            'logs_content' => $logs_content
        ]);

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors de l\'actualisation des logs: ' . $e->getMessage());
    }
}

// Clear Logs Handler
function pdf_builder_clear_logs_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        $log_dirs = [
            WP_CONTENT_DIR . '/pdf-builder-logs/',
            wp_upload_dir()['basedir'] . '/pdf-builder-logs/'
        ];

        $cleared_files = 0;

        foreach ($log_dirs as $log_dir) {
            if (is_dir($log_dir)) {
                $files = glob($log_dir . '*.log');
                foreach ($files as $file) {
                    if (is_file($file) && unlink($file)) {
                        $cleared_files++;
                    }
                }
            }
        }

        send_ajax_response(true, "$cleared_files fichier(s) de log supprimé(s) avec succès.");

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur lors du nettoyage des logs: ' . $e->getMessage());
    }
}

// Export Diagnostic Handler
function pdf_builder_export_diagnostic_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Diagnostic nonce check - log received value and verification results to help debugging
    $received_nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    $verify_ajax = $received_nonce ? wp_verify_nonce($received_nonce, 'pdf_builder_ajax') : false;
    $verify_cache = $received_nonce ? wp_verify_nonce($received_nonce, 'pdf_builder_cache_actions') : false;
    if (!$verify_ajax && !$verify_cache) {
        error_log('[PDF Builder Nonce] DEBUG EXPORT: Invalid nonce received: ' . substr($received_nonce, 0, 12) . '..., verify_ajax=' . intval($verify_ajax) . ', verify_cache=' . intval($verify_cache));
        send_ajax_response(false, 'Nonce invalide', ['received_nonce' => $received_nonce, 'verify_ajax' => $verify_ajax ? 1 : 0, 'verify_cache_actions' => $verify_cache ? 1 : 0]);
        return;
    }

    $diagnostic_data = [
        'timestamp' => current_time('Y-m-d H:i:s'),
        'wordpress' => [
            'version' => get_bloginfo('version'),
            'url' => get_site_url(),
            'admin_url' => admin_url(),
            'wp_debug' => WP_DEBUG ? 'enabled' : 'disabled'
        ],
        'plugin' => [
            'version' => PDF_BUILDER_VERSION,
            'license_status' => get_option('pdf_builder_license_status', 'inactive'),
            'settings_count' => count(get_option('pdf_builder_settings', []))
        ],
        'server' => [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ],
        'database' => [
            'table_prefix' => $GLOBALS['wpdb']->prefix,
            'charset' => $GLOBALS['wpdb']->charset,
            'collate' => $GLOBALS['wpdb']->collate
        ]
    ];

    // Create diagnostic file
    $filename = 'pdf-builder-diagnostic-' . date('Y-m-d-H-i-s') . '.json';
    $file_path = wp_upload_dir()['basedir'] . '/pdf-builder-diagnostics/' . $filename;

    // Ensure directory exists
    wp_mkdir_p(dirname($file_path));

    if (file_put_contents($file_path, json_encode($diagnostic_data, JSON_PRETTY_PRINT))) {
        send_ajax_response(true, 'Diagnostic exporté avec succès.', [
            'file_url' => wp_upload_dir()['baseurl'] . '/pdf-builder-diagnostics/' . $filename,
            'file_path' => $file_path
        ]);
    } else {
        send_ajax_response(false, 'Erreur lors de la création du fichier de diagnostic.');
    }
}

// View Logs Handler
function pdf_builder_view_logs_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Diagnostic nonce check - log received value and verification results to help debugging
    $received_nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    $verify_ajax = $received_nonce ? wp_verify_nonce($received_nonce, 'pdf_builder_ajax') : false;
    $verify_cache = $received_nonce ? wp_verify_nonce($received_nonce, 'pdf_builder_cache_actions') : false;
    if (!$verify_ajax && !$verify_cache) {
        error_log('[PDF Builder Nonce] DEBUG VIEW_LOGS: Invalid nonce received: ' . substr($received_nonce, 0, 12) . '..., verify_ajax=' . intval($verify_ajax) . ', verify_cache=' . intval($verify_cache));
        send_ajax_response(false, 'Nonce invalide', ['received_nonce' => $received_nonce, 'verify_ajax' => $verify_ajax ? 1 : 0, 'verify_cache_actions' => $verify_cache ? 1 : 0]);
        return;
    }

    $log_files = [];
    $log_dirs = [
        WP_CONTENT_DIR . '/pdf-builder-logs/',
        wp_upload_dir()['basedir'] . '/pdf-builder-logs/'
    ];

    foreach ($log_dirs as $log_dir) {
        if (is_dir($log_dir)) {
            $files = glob($log_dir . '*.log');
            foreach ($files as $file) {
                $log_files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }
    }

    if (!empty($log_files)) {
        // Sort by modification date (newest first)
        usort($log_files, function($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        send_ajax_response(true, count($log_files) . ' fichier(s) de log trouvé(s).', ['log_files' => $log_files]);
    } else {
        send_ajax_response(false, 'Aucun fichier de log trouvé.');
    }
}

function pdf_builder_save_settings_handler() {
    // Simple file logging for debugging
    $log_file = WP_CONTENT_DIR . '/pdf-builder-debug.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Handler called\n", FILE_APPEND);

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        send_ajax_response(false, 'Permissions insuffisantes');
        return;
    }

    // Vérifier le nonce pour la sécurité
    $received_nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
    $verify_ajax = $received_nonce ? wp_verify_nonce($received_nonce, 'pdf_builder_ajax') : false;
    if (!$verify_ajax) {
        error_log('[PDF Builder Nonce] ERROR: {"message":"Nonce invalide","context":"save_settings","timestamp":"' . current_time('Y-m-d H:i:s') . '","user_id":' . get_current_user_id() . ',"extra":{"provided_nonce":"' . substr($received_nonce, 0, 12) . '...","expected_action":"pdf_builder_ajax"}}');
        send_ajax_response(false, 'Nonce invalide', ['received_nonce' => $received_nonce, 'verify_ajax' => $verify_ajax ? 1 : 0]);
        return;
    }

    $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'general');

    // Extraire la liste des champs collectés côté JS pour comparaison (si présente)
    $js_collected = [];
    if (!empty($_POST['js_collected_fields'])) {
        $decoded = json_decode(stripslashes($_POST['js_collected_fields']), true);
        if (is_array($decoded)) {
            $js_collected = $decoded;
        }
        unset($_POST['js_collected_fields']);
    }

    // Traiter directement selon l'onglet
    try {
        switch ($current_tab) {
            case 'developpeur':
            case 'all':
                // Simplified save logic
                $saved_count = 0;
                $saved_options = [];

                error_log('PHP SAVE DEBUG - POST data received: ' . print_r($_POST, true));

                foreach ($_POST as $key => $value) {
                    if (in_array($key, ['action', 'nonce', 'current_tab', 'js_collected_fields'])) {
                        continue;
                    }

                    $option_key = strpos($key, 'pdf_builder_') === 0 ? $key : 'pdf_builder_' . $key;
                    update_option($option_key, sanitize_text_field($value));
                    $saved_value = get_option($option_key, '');
                    $saved_options[$option_key] = $saved_value;
                    $saved_count++;

                    error_log("PHP SAVE DEBUG - Saved {$key} -> {$option_key} = '{$saved_value}'");
                }

                error_log('PHP SAVE DEBUG - Returning result_data: ' . print_r($saved_options, true));

                $message = sprintf('%d paramètres sauvegardés.', $saved_count);

                $response_data = [
                    'saved_count' => $saved_count,
                    'result_data' => $saved_options
                ];

                send_ajax_response(true, $message, $response_data);
                break;

            default:
                send_ajax_response(false, 'Onglet non supporté: ' . $current_tab);
                break;
        }

    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Exception in save_settings: ' . $e->getMessage());
            error_log('PDF Builder: Exception trace: ' . $e->getTraceAsString());
        }
        send_ajax_response(false, 'Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}


// Handler pour récupérer les paramètres canvas
function pdf_builder_get_canvas_settings_handler() {
    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            send_ajax_response(false, 'Permissions insuffisantes.');
            return;
        }

        $category = sanitize_text_field($_POST['category'] ?? '');

        if (empty($category)) {
            // Retourner tous les paramètres si pas de catégorie spécifiée (rétrocompatibilité)
            $settings = [
                'canvas_width' => intval(get_option('pdf_builder_canvas_width', 794)),
                'canvas_height' => intval(get_option('pdf_builder_canvas_height', 1123)),
                'canvas_unit' => get_option('pdf_builder_canvas_unit', 'px'),
                'canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
                'canvas_background_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
                'canvas_show_transparency' => get_option('pdf_builder_canvas_show_transparency', false) == '1',
                'container_background_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
                'container_show_transparency' => get_option('pdf_builder_canvas_container_show_transparency', false) == '1',
                'border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
                'border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
                'shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0') == '1',
                'margin_top' => intval(get_option('pdf_builder_canvas_margin_top', 28)),
                'margin_right' => intval(get_option('pdf_builder_canvas_margin_right', 28)),
                'margin_bottom' => intval(get_option('pdf_builder_canvas_margin_bottom', 10)),
                'margin_left' => intval(get_option('pdf_builder_canvas_margin_left', 10)),
                'show_margins' => get_option('pdf_builder_canvas_show_margins', false) == '1',
                'show_grid' => get_option('pdf_builder_canvas_grid_enabled', '1') == '1',
                'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
                'grid_color' => get_option('pdf_builder_canvas_grid_color', '#e0e0e0'),
                'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') == '1',
                'snap_to_elements' => get_option('pdf_builder_canvas_snap_to_elements', false) == '1',
                'snap_tolerance' => intval(get_option('pdf_builder_canvas_snap_tolerance', 5)),
                'show_guides' => get_option('pdf_builder_canvas_guides_enabled', '1') == '1',
                'default_zoom' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
                'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
                'min_zoom' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
                'max_zoom' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
                'zoom_with_wheel' => get_option('pdf_builder_canvas_zoom_with_wheel', '1') == '1',
                'pan_with_mouse' => get_option('pdf_builder_canvas_pan_enabled', '1') == '1',
                'show_resize_handles' => get_option('pdf_builder_canvas_show_resize_handles', '1') == '1',
                'handle_size' => intval(get_option('pdf_builder_canvas_handle_size', 8)),
                'handle_color' => get_option('pdf_builder_canvas_handle_color', '#007cba'),
                'enable_rotation' => get_option('pdf_builder_canvas_rotate_enabled', '1') == '1',
                'rotation_step' => intval(get_option('pdf_builder_canvas_rotation_step', 15)),
                'multi_select' => get_option('pdf_builder_canvas_multi_select', '1') == '1',
                'copy_paste_enabled' => get_option('pdf_builder_canvas_copy_paste_enabled', '1') == '1',
                'export_quality' => intval(get_option('pdf_builder_canvas_export_quality', 90)),
                'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
                'compress_images' => get_option('pdf_builder_canvas_compress_images', '1') == '1',
                'image_quality' => intval(get_option('pdf_builder_canvas_image_quality', 85)),
                'max_image_size' => intval(get_option('pdf_builder_canvas_max_image_size', 2048)),
                'include_metadata' => get_option('pdf_builder_canvas_include_metadata', '1') == '1',
                'pdf_author' => get_option('pdf_builder_canvas_pdf_author', 'PDF Builder Pro'),
                'pdf_subject' => get_option('pdf_builder_canvas_pdf_subject', ''),
                'auto_crop' => get_option('pdf_builder_canvas_auto_crop', false) == '1',
                'embed_fonts' => get_option('pdf_builder_canvas_embed_fonts', '1') == '1',
                'optimize_for_web' => get_option('pdf_builder_canvas_optimize_for_web', '1') == '1',
                'enable_hardware_acceleration' => get_option('pdf_builder_canvas_enable_hardware_acceleration', '1') == '1',
                'limit_fps' => get_option('pdf_builder_canvas_limit_fps', '1') == '1',
                'max_fps' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
                'undo_levels' => intval(get_option('pdf_builder_canvas_undo_levels', 50)),
                'redo_levels' => intval(get_option('pdf_builder_canvas_redo_levels', 50)),
                'enable_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') == '1',
                'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
                'debug_mode' => get_option('pdf_builder_canvas_debug_mode', false) == '1',
                'show_fps' => get_option('pdf_builder_canvas_show_fps', false) == '1',
                // Paramètres de performance
                'fps_target' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
                'memory_limit_js' => intval(get_option('pdf_builder_canvas_memory_limit_js', 256)),
                'memory_limit_php' => intval(get_option('pdf_builder_canvas_memory_limit_php', 256)),
                'response_timeout' => intval(get_option('pdf_builder_canvas_response_timeout', 30)),
                'lazy_loading_editor' => get_option('pdf_builder_canvas_lazy_loading_editor', '1') == '1',
                'preload_critical' => get_option('pdf_builder_canvas_preload_critical', '1') == '1',
                'lazy_loading_plugin' => get_option('pdf_builder_canvas_lazy_loading_plugin', '1') == '1'
            ];

            
            send_ajax_response(true, 'Paramètres récupérés avec succès.', $settings);
            return;
        }

        // Retourner les paramètres pour une catégorie spécifique
        $values = [];

        switch ($category) {
            case 'grille':
                $values = [
                    'guides_enabled' => get_option('pdf_builder_canvas_guides_enabled', true),
                    'grid_enabled' => get_option('pdf_builder_canvas_grid_enabled', true),
                    'grid_size' => get_option('pdf_builder_canvas_grid_size', 20),
                    'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', true)
                ];
                break;

            case 'dimensions':
                $values = [
                    'format' => get_option('pdf_builder_canvas_format', 'A4'),
                    'orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
                    'dpi' => get_option('pdf_builder_canvas_dpi', 96)
                ];
                break;

            case 'zoom':
                $values = [
                    'zoom_min' => get_option('pdf_builder_canvas_zoom_min', 10),
                    'zoom_max' => get_option('pdf_builder_canvas_zoom_max', 500),
                    'zoom_default' => get_option('pdf_builder_canvas_zoom_default', 100),
                    'zoom_step' => get_option('pdf_builder_canvas_zoom_step', 25)
                ];
                break;

            case 'apparence':
                $values = [
                    'canvas_bg_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
                    'canvas_container_bg_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
                    'canvas_border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
                    'canvas_border_width' => get_option('pdf_builder_canvas_border_width', 1),
                    'canvas_shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0')
                ];
                break;

            case 'interactions':
                $values = [
                    'selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
                    'multi_select' => get_option('pdf_builder_canvas_multi_select', true),
                    'drag_enabled' => get_option('pdf_builder_canvas_drag_enabled', true),
                    'resize_enabled' => get_option('pdf_builder_canvas_resize_enabled', true),
                    'rotate_enabled' => get_option('pdf_builder_canvas_rotate_enabled', true),
                    'keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', true)
                ];
                break;

            case 'export':
                $values = [
                    'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
                    'export_quality' => get_option('pdf_builder_canvas_export_quality', 90),
                    'export_transparent' => get_option('pdf_builder_canvas_export_transparent', false)
                ];
                break;

            case 'performance':
                $values = [
                    'fps_target' => get_option('pdf_builder_canvas_fps_target', 60),
                    'memory_limit_js' => get_option('pdf_builder_canvas_memory_limit_js', 256),
                    'memory_limit_php' => get_option('pdf_builder_canvas_memory_limit_php', 256),
                    'response_timeout' => get_option('pdf_builder_canvas_response_timeout', 30),
                    'lazy_loading_editor' => get_option('pdf_builder_canvas_lazy_loading_editor', true),
                    'preload_critical' => get_option('pdf_builder_canvas_preload_critical', true),
                    'lazy_loading_plugin' => get_option('pdf_builder_canvas_lazy_loading_plugin', true)
                ];
                break;

            case 'debug':
                $values = [
                    'debug_enabled' => get_option('pdf_builder_canvas_debug_enabled', false),
                    'performance_monitoring' => get_option('pdf_builder_canvas_performance_monitoring', false),
                    'error_reporting' => get_option('pdf_builder_canvas_error_reporting', false)
                ];
                break;

            default:
                send_ajax_response(false, 'Catégorie inconnue: ' . $category);
                return;
        }

        send_ajax_response(true, 'Paramètres récupérés avec succès.', $values);

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur: ' . $e->getMessage());
    }
}

/**
 * AJAX handler to get all canvas settings for window object update
 */
function pdf_builder_get_all_canvas_settings_handler() {
    try {
        // Get all canvas settings in the same format as settings-canvas-params.php
        $canvas_settings = [
            'default_canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
            'default_canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
            'default_canvas_unit' => get_option('pdf_builder_canvas_unit', 'px'),
            'default_canvas_dpi' => intval(get_option('pdf_builder_canvas_dpi', 96)),
            'default_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
            'canvas_background_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
            'canvas_show_transparency' => get_option('pdf_builder_canvas_show_transparency', false),
            'container_background_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
            'container_show_transparency' => get_option('pdf_builder_canvas_container_show_transparency', false),
            'border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
            'border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
            'shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0') == '1',
            'margin_top' => intval(get_option('pdf_builder_canvas_margin_top', 28)),
            'margin_right' => intval(get_option('pdf_builder_canvas_margin_right', 28)),
            'margin_bottom' => intval(get_option('pdf_builder_canvas_margin_bottom', 10)),
            'margin_left' => intval(get_option('pdf_builder_canvas_margin_left', 10)),
            'show_margins' => get_option('pdf_builder_canvas_show_margins', '0') == '1',
            'show_grid' => get_option('pdf_builder_canvas_grid_enabled', '1') == '1',
            'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
            'grid_color' => get_option('pdf_builder_canvas_grid_color', '#e0e0e0'),
            'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') == '1',
            'snap_to_elements' => get_option('pdf_builder_canvas_snap_to_elements', '0') == '1',
            'snap_tolerance' => intval(get_option('pdf_builder_canvas_snap_tolerance', 5)),
            'show_guides' => get_option('pdf_builder_canvas_guides_enabled', '1') == '1',

            // 🔍 Zoom & Navigation
            'navigation_enabled' => get_option('pdf_builder_canvas_navigation_enabled', '1') == '1',
            'default_zoom' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
            'min_zoom' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
            'max_zoom' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
            'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
            'zoom_with_wheel' => get_option('pdf_builder_canvas_zoom_with_wheel', '1') == '1',
            'pan_with_mouse' => get_option('pdf_builder_canvas_pan_enabled', '1') == '1',

            'show_resize_handles' => get_option('pdf_builder_canvas_show_resize_handles', '1') == '1',
            'handle_size' => intval(get_option('pdf_builder_canvas_handle_size', 8)),
            'handle_color' => get_option('pdf_builder_canvas_handle_color', '#007cba'),
            'enable_rotation' => get_option('pdf_builder_canvas_rotate_enabled', '1') == '1',
            'rotation_step' => intval(get_option('pdf_builder_canvas_rotation_step', 15)),
            'multi_select' => get_option('pdf_builder_canvas_multi_select', '1') == '1',
            'copy_paste_enabled' => get_option('pdf_builder_canvas_copy_paste_enabled', '1') == '1',
            'export_quality' => get_option('pdf_builder_canvas_export_quality', 90),
            'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
            'compress_images' => get_option('pdf_builder_canvas_compress_images', '1') == '1',
            'image_quality' => intval(get_option('pdf_builder_canvas_image_quality', 85)),
            'max_image_size' => intval(get_option('pdf_builder_canvas_max_image_size', 2048)),
            'include_metadata' => get_option('pdf_builder_canvas_include_metadata', '1') == '1',
            'pdf_author' => get_option('pdf_builder_canvas_pdf_author', 'PDF Builder Pro'),
            'pdf_subject' => get_option('pdf_builder_canvas_pdf_subject', ''),
            'auto_crop' => get_option('pdf_builder_canvas_auto_crop', '0') == '1',
            'embed_fonts' => get_option('pdf_builder_canvas_embed_fonts', '1') == '1',
            'optimize_for_web' => get_option('pdf_builder_canvas_optimize_for_web', '1') == '1',
            'enable_hardware_acceleration' => get_option('pdf_builder_canvas_enable_hardware_acceleration', '1') == '1',
            'limit_fps' => get_option('pdf_builder_canvas_limit_fps', '1') == '1',
            'max_fps' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
            'undo_levels' => intval(get_option('pdf_builder_canvas_undo_levels', 50)),
            'redo_levels' => intval(get_option('pdf_builder_canvas_redo_levels', 50)),
            'enable_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') == '1',
            'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
            'debug_mode' => get_option('pdf_builder_canvas_debug_mode', '0') == '1',
            'show_fps' => get_option('pdf_builder_canvas_show_fps', '0') == '1'
        ];

        send_ajax_response(true, 'Paramètres récupérés', $canvas_settings);

    } catch (Exception $e) {
        send_ajax_response(false, 'Erreur: ' . $e->getMessage());
    }
}

// Hook AJAX actions - MOVED to pdf-builder-pro.php for global registration
// REMOVED: Canvas settings actions moved to PDF_Builder_Admin to avoid duplication

/**
 * Handler AJAX pour sauvegarder tous les paramètres depuis le bouton flottant
 */
function pdf_builder_save_all_settings_handler() {
    // LOG DIAGNOSTIC - Vérifier que le handler s'exécute
    error_log('=== PDF_BUILDER_SAVE_ALL_HANDLER EXECUTED ===');
    file_put_contents(WP_CONTENT_DIR . '/pdf-builder-debug.log', date('Y-m-d H:i:s') . " - HANDLER EXECUTED\n", FILE_APPEND);

    // ===== LOGGING DÉTAILLÉ POUR DEBUG DES TOGGLES DÉVELOPPEUR =====
    error_log('===== PDF BUILDER SAVE ALL SETTINGS - HANDLER STARTED =====');
    $log_file = WP_CONTENT_DIR . '/pdf-builder-debug.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - pdf_builder_save_all_settings_handler STARTED\n", FILE_APPEND);
    error_log('Timestamp: ' . current_time('mysql'));
    error_log('User ID: ' . get_current_user_id());
    error_log('REQUEST_METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
    error_log('Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'UNKNOWN'));

    // Debug: Log the nonce received
    error_log('PDF Builder SAVE ALL: Received nonce: ' . ($_POST['nonce'] ?? 'NOT SET'));
    error_log('PDF Builder SAVE ALL: All POST keys: ' . implode(', ', array_keys($_POST)));

    // LOG DES CHAMPS CRITIQUES POUR LES TOGGLES DÉVELOPPEUR
    $critical_fields = ['debug_javascript', 'developer_enabled'];
    foreach ($critical_fields as $field) {
        $value = isset($_POST[$field]) ? $_POST[$field] : 'NOT_SET';
        error_log("CRITICAL FIELD [{$field}]: {$value}");
    }

    // LOG SPÉCIFIQUE POUR LE TOGGLE DEBUG JAVASCRIPT
    error_log("=== DEBUG JAVASCRIPT TOGGLE ANALYSIS ===");
    error_log("pdf_builder_debug_javascript in POST: " . (isset($_POST['pdf_builder_debug_javascript']) ? $_POST['pdf_builder_debug_javascript'] : 'NOT_SET'));
    error_log("debug_javascript in POST: " . (isset($_POST['debug_javascript']) ? $_POST['debug_javascript'] : 'NOT_SET'));
    
    // Vérifier tous les champs POST contenant 'debug'
    $debug_related_fields = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'debug') !== false) {
            $debug_related_fields[$key] = $value;
        }
    }
    error_log("All debug-related fields in POST: " . json_encode($debug_related_fields));
    error_log("=== END DEBUG JAVASCRIPT TOGGLE ANALYSIS ===");

    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        error_log('PDF Builder SAVE ALL: ❌ NONCE VERIFICATION FAILED');
        send_ajax_response(false, 'Nonce invalide');
        return;
    }
    error_log('PDF Builder SAVE ALL: ✅ Nonce verification SUCCESS');

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        error_log('PDF Builder SAVE ALL: ❌ PERMISSIONS INSUFFISANTES');
        send_ajax_response(false, 'Permissions insuffisantes.');
        return;
    }
    error_log('PDF Builder SAVE ALL: ✅ Permissions OK');

    // Log de l'onglet en cours d'enregistrement
    $current_tab = isset($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'general';
    error_log('[PDF Builder] SAVE: Enregistrement depuis l\'onglet "' . $current_tab . '"');

    try {
        $saved_count = 0;
        $errors = [];
        $processed_fields = [];
        $ignored_fields = [];
        $js_collected = isset($_POST['collectedFields']) ? json_decode(stripslashes($_POST['collectedFields']), true) : [];

        // Debug: Log tous les champs POST reçus
        error_log('PDF Builder DEBUG - Tous les champs POST reçus: ' . implode(', ', array_keys($_POST)));

        // LOG DÉTAILLÉ DE TOUS LES CHAMPS POST AVEC LEURS VALEURS
        error_log('===== DÉTAIL DES CHAMPS POST REÇUS =====');
        foreach ($_POST as $key => $value) {
            $display_value = is_array($value) ? 'ARRAY[' . count($value) . ']' : (string)$value;
            error_log("POST[{$key}] = {$display_value}");
        }
        error_log('===== FIN DÉTAIL CHAMPS POST =====');

        // Traiter tous les champs soumis
        foreach ($_POST as $key => $value) {
            // Ignorer les champs spéciaux
            if (in_array($key, ['action', 'nonce', 'current_tab'])) {
                $ignored_fields[] = $key;
                error_log("IGNORED FIELD: {$key}");
                continue;
            }

            // CRITICAL: Log debug_javascript explicitly
            if (strpos($key, 'debug_javascript') !== false) {
                error_log("🔴 FOUND DEBUG_JAVASCRIPT IN POST: key='{$key}', value='" . (is_array($value) ? json_encode($value) : $value) . "'");
            }

            $processed_fields[] = $key;
            error_log("PROCESSING FIELD: {$key} (original value: " . (is_array($value) ? 'ARRAY' : $value) . ")");

            try {
                // Préfixer la clé avec pdf_builder_ si elle ne l'a pas déjà
                $option_key = $key;
                if (strpos($key, 'pdf_builder_') !== 0) {
                    $option_key = 'pdf_builder_' . $key;
                }

                // Traiter selon le type de champ
                if (strpos($key, '_enabled') !== false || strpos($key, '_debug') !== false || in_array($key, ['debug_javascript'])) {
                    // Champs booléens
                    $sanitized_value = $value === '1' || $value === 'true' ? 1 : 0;
                    error_log("BOOLEAN FIELD [{$key}]: original='{$value}' -> sanitized='{$sanitized_value}'");
                } elseif (is_array($value)) {
                    // Tableaux
                    $sanitized_value = array_map('sanitize_text_field', $value);
                    error_log("ARRAY FIELD [{$key}]: " . json_encode($sanitized_value));
                } else {
                    // Texte normal
                    $sanitized_value = sanitize_text_field($value);
                    error_log("TEXT FIELD [{$key}]: '{$sanitized_value}'");
                }

                // Sauvegarder l'option
                $update_result = update_option($option_key, $sanitized_value);
                $saved_count++;
                error_log("SAVED OPTION [{$option_key}] = '{$sanitized_value}' (update_result: " . ($update_result ? 'SUCCESS' : 'NO_CHANGE') . ")");

            } catch (Exception $e) {
                $errors[] = "Erreur lors de la sauvegarde de $key: " . $e->getMessage();
                error_log("ERROR SAVING [{$key}]: " . $e->getMessage());
            }
        }

        // Traiter les champs checkbox non cochés (qui ne sont pas envoyés)
        $checkbox_fields = [
            'debug_mode',
            'log_level',
            'pdf_cache_enabled',
            'pdf_metadata_enabled',
            'pdf_print_optimized',
            'template_library_enabled',
            'developer_enabled',
            'debug_javascript',
            'debug_javascript_verbose',
            'debug_ajax',
            'debug_performance',
            'debug_database',
            'debug_php_errors'
        ];

        error_log('===== TRAITEMENT CHAMPS CHECKBOX NON COCHÉS =====');

        // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
        error_log("BEFORE CHECKBOX PROCESSING - debug_javascript in POST: " . (isset($_POST['debug_javascript']) ? $_POST['debug_javascript'] : 'NOT_SET'));
        error_log("BEFORE CHECKBOX PROCESSING - pdf_builder_debug_javascript in POST: " . (isset($_POST['pdf_builder_debug_javascript']) ? $_POST['pdf_builder_debug_javascript'] : 'NOT_SET'));
        error_log("BEFORE CHECKBOX PROCESSING - debug_javascript in processed_fields: " . (in_array('debug_javascript', $processed_fields) ? 'YES' : 'NO'));
        error_log("BEFORE CHECKBOX PROCESSING - pdf_builder_debug_javascript in processed_fields: " . (in_array('pdf_builder_debug_javascript', $processed_fields) ? 'YES' : 'NO'));

        foreach ($checkbox_fields as $field) {
            // Vérifier AVEC et SANS le préfixe pdf_builder_
            $post_key_with_prefix = 'pdf_builder_' . $field;
            $post_key_without_prefix = $field;
            $post_key_checked = isset($_POST[$post_key_with_prefix]) ? $post_key_with_prefix : (isset($_POST[$post_key_without_prefix]) ? $post_key_without_prefix : null);
            $is_checked = $post_key_checked !== null;
            
            // La clé réelle de l'option en base de données
            $option_key = 'pdf_builder_' . $field;
            
            if (!$is_checked) {
                $old_value = get_option($option_key, 'NOT_SET');
                update_option($option_key, 0);
                $saved_count++;
                error_log("UNCHECKED CHECKBOX [{$field}]: old_value='{$old_value}' -> set to '0'");
                
                // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
                if ($field === 'debug_javascript') {
                    error_log("DEBUG_JAVASCRIPT SET TO 0 (was unchecked)");
                }
            } else {
                $checked_value = $_POST[$post_key_checked];
                $sanitized = $checked_value === '1' || $checked_value === 'true' ? 1 : 0;
                update_option($option_key, $sanitized);
                $saved_count++;
                error_log("CHECKBOX WAS SET [{$field}] from POST['{$post_key_checked}'] = '{$checked_value}' -> sanitized to '{$sanitized}'");
                
                // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
                if ($field === 'debug_javascript') {
                    error_log("DEBUG_JAVASCRIPT WAS CHECKED and SAVED: {$sanitized}");
                }
            }
        }
        error_log('===== FIN TRAITEMENT CHECKBOX =====');

        // VÉRIFICATION FINALE DES VALEURS SAUVEGARDÉES
        error_log('===== VÉRIFICATION VALEURS SAUVEGARDÉES EN BASE =====');
        foreach ($critical_fields as $field) {
            $saved_value = get_option('pdf_builder_' . $field, 'NOT_FOUND');
            error_log("DB CHECK [pdf_builder_{$field}] = '{$saved_value}'");
            
            // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
            if ($field === 'debug_javascript') {
                error_log("FINAL DB VALUE FOR DEBUG_JAVASCRIPT: '{$saved_value}'");
                error_log("DEBUG_JAVASCRIPT OPTION KEY: 'pdf_builder_debug_javascript'");
            }
        }
        error_log('===== FIN VÉRIFICATION DB =====');

        // Message de succès
        $message = "✅ $saved_count paramètres sauvegardés avec succès.";
        if (!empty($errors)) {
            $message .= " ⚠️ " . count($errors) . " erreurs ignorées.";
        }

        error_log('PDF Builder SAVE ALL - About to build saved_options from processed_fields');

        // Préparer les options sauvegardées pour la réponse (sans préfixe pour correspondre aux noms de champs du formulaire)
        $saved_options = [];
        try {
            error_log('===== CONSTRUCTION SAVED_OPTIONS =====');
            error_log('processed_fields count: ' . count($processed_fields));
            
            $fields_processed = 0;
            foreach ($processed_fields as $field) {
                $fields_processed++;
                
                // Normaliser la clé : retirer le préfixe pdf_builder_ pour la cohérence dans la réponse
                $display_key = str_replace('pdf_builder_', '', $field);
                
                $option_key = strpos($field, 'pdf_builder_') === 0 ? $field : 'pdf_builder_' . $field;
                $saved_value = get_option($option_key, '');
                
                // Utiliser la clé normalisée (sans préfixe) dans saved_options pour correspondre aux noms de formulaire
                $saved_options[$display_key] = $saved_value;
                
                // CRITICAL: Log debug_javascript explicitly
                if (strpos($field, 'debug_javascript') !== false) {
                    error_log("🟡 SAVED_OPTIONS LOOP - DEBUG_JAVASCRIPT: field='{$field}', display_key='{$display_key}', option_key='{$option_key}', saved_value='{$saved_value}'");
                }
            }
            error_log("🟠 FIELDS PROCESSED: {$fields_processed}");

            // Ajouter les champs checkbox traités séparément (qui n'étaient pas dans POST car non cochés)
            error_log('checkbox_fields: ' . implode(', ', $checkbox_fields));
            foreach ($checkbox_fields as $field) {
                // Vérifier si ce champ a déjà été ajouté (avec ou sans préfixe)
                $already_added = isset($saved_options[$field]) || isset($saved_options['pdf_builder_' . $field]);
                
                if (!$already_added) {
                    $option_key = 'pdf_builder_' . $field;
                    $db_value = get_option($option_key, 0);
                    $saved_options[$field] = $db_value ? '1' : '0';
                    error_log("CHECKBOX SAVED_OPTIONS [{$field}] -> option_key[{$option_key}] = db_value:'{$db_value}' -> saved:'{$saved_options[$field]}'");
                    
                    // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
                    if ($field === 'debug_javascript') {
                        error_log("DEBUG_JAVASCRIPT CHECKBOX (NEW): field='{$field}', option_key='{$option_key}', db_value='{$db_value}', saved='{$saved_options[$field]}'");
                    }
                } else {
                    error_log("CHECKBOX ALREADY_ADDED [{$field}]");
                    
                    // LOG SPÉCIFIQUE POUR DEBUG_JAVASCRIPT
                    if ($field === 'debug_javascript') {
                        error_log("DEBUG_JAVASCRIPT CHECKBOX (SKIPPED - already added): field='{$field}'");
                    }
                }
            }

            // IMPORTANT: Sauvegarder aussi dans l'option principale pour la persistance après rechargement
            $main_settings = [];
            foreach ($saved_options as $key => $value) {
                $main_key = strpos($key, 'pdf_builder_') === 0 ? str_replace('pdf_builder_', '', $key) : $key;
                $main_settings[$main_key] = $value;
            }
            update_option('pdf_builder_settings', $main_settings);
            error_log('PDF Builder SAVE ALL - Main settings updated with ' . count($main_settings) . ' fields');

            error_log('PDF Builder SAVE ALL - Final saved_options count: ' . count($saved_options));
            error_log('PDF Builder SAVE ALL - Saved options keys: ' . implode(', ', array_keys($saved_options)));
        } catch (Exception $e) {
            error_log('PDF Builder SAVE ALL - ERROR building saved_options: ' . $e->getMessage());
            $saved_options = ['error' => 'Failed to build saved options: ' . $e->getMessage()];
        }

        // Use wp_send_json_success directly to ensure proper response format
        $response_data = [
            'message' => $message,
            'new_nonce' => wp_create_nonce('pdf_builder_ajax'),
            'saved_count' => $saved_count,
            'errors' => $errors,
            'saved_settings' => $saved_options,
            'debug_info' => [
                'total_post' => count($_POST),
                'ignored' => $ignored_fields,
                'processed' => count($processed_fields),
                'saved' => $saved_count,
                'error_count' => count($errors),
                'comparison' => [
                    'js_collected' => count($js_collected),
                    'php_received' => count($_POST),
                    'php_processed' => count($processed_fields),
                    'saved' => $saved_count
                ],
                'missing_fields' => implode(', ', array_diff($js_collected, array_keys($_POST)))
            ]
        ];

        // ULTIMATE DEBUG: Vérifier le contenu exact avant envoi
        error_log('🔵 ABOUT TO SEND - saved_options keys: ' . implode(', ', array_keys($saved_options)));
        error_log('🔵 debug_javascript in saved_options: ' . ($saved_options['debug_javascript'] ?? 'NOT FOUND'));

        wp_send_json_success($response_data);

    } catch (Exception $e) {
        // Debug: Log the exception
        error_log('PDF Builder SAVE ALL: ❌ EXCEPTION: ' . $e->getMessage());
        error_log('PDF Builder SAVE ALL: Exception trace: ' . $e->getTraceAsString());
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Exception in save_settings: ' . $e->getMessage());
            error_log('PDF Builder: Exception trace: ' . $e->getTraceAsString());
        }
        wp_send_json_error('Error during saving: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for saving cache settings
 */


