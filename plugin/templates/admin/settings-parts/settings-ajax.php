<?php
/**
 * PDF Builder Pro - AJAX Handlers
 * All AJAX request processing for settings page
 * Updated: 2025-11-19 02:10:00
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Function to send AJAX response
function send_ajax_response($success, $message = '', $data = [])
{
    $log_file = WP_CONTENT_DIR . '/pdf-builder-debug.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - send_ajax_response called: success=$success, message=$message\n", FILE_APPEND);

    if ($success) {
        $merged = array_merge(['message' => $message], $data);
        wp_send_json_success($merged);
    } else {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Sending error response: $message\n", FILE_APPEND);
        wp_send_json_error(['message' => $message]);
    }
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
        // Debug & Logging
        'pdf_builder_debug_mode' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_log_level' => ['type' => 'string', 'sanitize' => 'sanitize_text_field'],
        'pdf_builder_log_file_size' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_log_retention' => ['type' => 'int', 'sanitize' => 'intval'],

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
        'pdf_builder_debug_javascript_verbose' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_debug_ajax' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_debug_performance' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_debug_database' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_log_level' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_log_file_size' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_log_retention' => ['type' => 'int', 'sanitize' => 'intval'],
        'pdf_builder_force_https' => ['type' => 'boolean', 'sanitize' => 'intval'],
        'pdf_builder_performance_monitoring' => ['type' => 'boolean', 'sanitize' => 'intval'],

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

    $current_tab = PDF_Builder_Sanitizer::text($_POST['current_tab'] ?? 'general');

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
            case 'all':
                $saved_count = 0;
                $errors = [];
                $processed_fields = [];
                $ignored_fields = [];

                error_log('PDF Builder DEBUG - All received POST fields: ' . implode(', ', array_keys($_POST)));

                foreach ($_POST as $key => $value) {
                    if (in_array($key, ['action', 'nonce', 'current_tab'])) {
                        $ignored_fields[] = $key;
                        continue;
                    }

                    $processed_fields[] = $key;

                    try {
                        $option_key = $key;
                        if (strpos($key, 'pdf_builder_') !== 0) {
                            $option_key = 'pdf_builder_' . $key;
                        }

                        if (strpos($key, '_enabled') !== false || strpos($key, '_debug') !== false) {
                            $sanitized_value = ($value === '1' || $value === 'true') ? 1 : 0;
                        } elseif (is_array($value)) {
                            $sanitized_value = array_map('sanitize_text_field', $value);
                        } else {
                            $sanitized_value = sanitize_text_field($value);
                        }

                        update_option($option_key, $sanitized_value);
                        $saved_count++;
                    } catch (Exception $e) {
                        $errors[] = "Erreur lors de la sauvegarde de $key: " . $e->getMessage();
                    }
                }

                // Handle some known checkboxes not sent
                $checkbox_fields = [
                    'debug_mode', 'log_level', 'pdf_cache_enabled', 'pdf_metadata_enabled', 'pdf_print_optimized', 'template_library_enabled', 'developer_enabled'
                ];
                foreach ($checkbox_fields as $field) {
                    if (!isset($_POST[$field])) {
                        update_option('pdf_builder_' . $field, 0);
                    }
                }

                $message = sprintf('%d paramètres sauvegardés.', $saved_count);
                if (!empty($errors)) {
                    $message .= ' ' . count($errors) . ' erreurs.';
                }

                send_ajax_response(true, $message, [
                    'saved_count' => $saved_count,
                    'errors' => $errors,
                    'debug_info' => [
                        'total_post' => count($_POST),
                        'ignored' => $ignored_fields,
                        'processed' => count($processed_fields),
                        'saved' => $saved_count,
                        'missing_fields' => implode(', ', array_diff($js_collected, array_keys($_POST)))
                    ]
                ]);
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

// Canvas settings AJAX handler
function pdf_builder_save_canvas_settings_handler() {

    $category = PDF_Builder_Sanitizer::text($_POST['category'] ?? 'dimensions');

    try {
            $saved_values = [];

            switch ($category) {
                case 'dimensions':
                    // Sauvegarder les paramètres de dimensions
                    $dimensions_mappings = [
                        'canvas_format' => 'pdf_builder_canvas_format',
                        'canvas_orientation' => 'pdf_builder_canvas_orientation',
                        'canvas_dpi' => 'pdf_builder_canvas_dpi'
                    ];

                    foreach ($dimensions_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = PDF_Builder_Sanitizer::text($_POST[$post_key]);
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                    }

                    // Calculer et sauvegarder les dimensions en pixels
                    $format = get_option('pdf_builder_canvas_format', 'A4');
                    $orientation = get_option('pdf_builder_canvas_orientation', 'portrait');
                    $dpi = intval(get_option('pdf_builder_canvas_dpi', 96));

                    // Utiliser les dimensions standard centralisées
                    $formatDimensionsMM = \PDF_Builder\PAPER_FORMATS;

                    $dimensions = isset($formatDimensionsMM[$format]) ? $formatDimensionsMM[$format] : $formatDimensionsMM['A4'];

                    // Appliquer l'orientation
                    if ($orientation === 'landscape') {
                        $temp = $dimensions['width'];
                        $dimensions['width'] = $dimensions['height'];
                        $dimensions['height'] = $temp;
                    }

                    // Convertir mm en pixels (1mm = dpi/25.4 pixels)
                    $width_px = round(($dimensions['width'] / 25.4) * $dpi);
                    $height_px = round(($dimensions['height'] / 25.4) * $dpi);

                    update_option('pdf_builder_canvas_width', $width_px);
                    update_option('pdf_builder_canvas_height', $height_px);

                    $saved_values['canvas_width'] = $width_px;
                    $saved_values['canvas_height'] = $height_px;
                    break;

                case 'zoom':
                    // Sauvegarder les paramètres de zoom
                    $zoom_mappings = [
                        'canvas_zoom_min' => 'pdf_builder_canvas_zoom_min',
                        'canvas_zoom_max' => 'pdf_builder_canvas_zoom_max',
                        'canvas_zoom_default' => 'pdf_builder_canvas_zoom_default',
                        'canvas_zoom_step' => 'pdf_builder_canvas_zoom_step'
                    ];

                    foreach ($zoom_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = floatval($_POST[$post_key]);
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                    }
                    break;

                case 'apparence':
                    // Sauvegarder les paramètres d'apparence
                    
                    $apparence_mappings = [
                        'canvas_bg_color' => 'pdf_builder_canvas_bg_color',
                        'canvas_container_bg_color' => 'pdf_builder_canvas_container_bg_color',
                        'canvas_border_color' => 'pdf_builder_canvas_border_color',
                        'canvas_border_width' => 'pdf_builder_canvas_border_width',
                        'canvas_shadow_enabled' => 'pdf_builder_canvas_shadow_enabled'
                    ];

                    foreach ($apparence_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key];
                            if ($post_key === 'canvas_shadow_enabled') {
                                $value = $value === '1';
                            } else if ($post_key === 'canvas_border_width') {
                                $value = PDF_Builder_Sanitizer::int($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                            
                        }
                        if ($post_key === 'canvas_shadow_enabled' && !isset($_POST[$post_key])) {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                            
                        }
                    }
                    break;

                case 'grille':
                    // Sauvegarder les paramètres de grille
                    $grille_mappings = [
                        'canvas_guides_enabled' => 'pdf_builder_canvas_guides_enabled',
                        'canvas_grid_enabled' => 'pdf_builder_canvas_grid_enabled',
                        'canvas_grid_size' => 'pdf_builder_canvas_grid_size',
                        'canvas_snap_to_grid' => 'pdf_builder_canvas_snap_to_grid'
                    ];

                    foreach ($grille_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key];
                            if (in_array($post_key, ['canvas_guides_enabled', 'canvas_grid_enabled', 'canvas_snap_to_grid'])) {
                                $value = $value === '1';
                            } else if ($post_key === 'canvas_grid_size') {
                                $value = PDF_Builder_Sanitizer::int($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                        if (in_array($post_key, ['canvas_guides_enabled', 'canvas_grid_enabled', 'canvas_snap_to_grid']) && !isset($_POST[$post_key])) {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                        }
                    }
                    break;

                case 'interactions':
                    // Sauvegarder les paramètres d'interactions
                    $interactions_mappings = [
                        'canvas_selection_mode' => 'pdf_builder_canvas_selection_mode',
                        'canvas_multi_select' => 'pdf_builder_canvas_multi_select',
                        'canvas_drag_enabled' => 'pdf_builder_canvas_drag_enabled',
                        'canvas_resize_enabled' => 'pdf_builder_canvas_resize_enabled',
                        'canvas_rotate_enabled' => 'pdf_builder_canvas_rotate_enabled',
                        'canvas_keyboard_shortcuts' => 'pdf_builder_canvas_keyboard_shortcuts'
                    ];

                    foreach ($interactions_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key];
                            if (in_array($post_key, ['canvas_multi_select', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_keyboard_shortcuts'])) {
                                $value = $value === '1';
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                        if (in_array($post_key, ['canvas_multi_select', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_keyboard_shortcuts']) && !isset($_POST[$post_key])) {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                        }
                    }
                    break;

                case 'export':
                    // Sauvegarder les paramètres d'export
                    $export_mappings = [
                        'canvas_export_format' => 'pdf_builder_canvas_export_format',
                        'canvas_export_quality' => 'pdf_builder_canvas_export_quality',
                        'canvas_export_transparent' => 'pdf_builder_canvas_export_transparent'
                    ];

                    foreach ($export_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key];
                            if ($post_key === 'canvas_export_transparent') {
                                $value = $value === '1';
                            } else if ($post_key === 'canvas_export_quality') {
                                $value = PDF_Builder_Sanitizer::int($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                        if ($post_key === 'canvas_export_transparent' && !isset($_POST[$post_key])) {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                        }
                    }
                    break;

                case 'performance':
                    // Sauvegarder les paramètres de performance (code existant)
                    $performance_mappings = [
                        'canvas_fps_target' => 'pdf_builder_canvas_fps_target',
                        'canvas_memory_limit_js' => 'pdf_builder_canvas_memory_limit_js',
                        'canvas_memory_limit_php' => 'pdf_builder_canvas_memory_limit_php',
                        'canvas_response_timeout' => 'pdf_builder_canvas_response_timeout',
                        'canvas_lazy_loading_editor' => 'pdf_builder_canvas_lazy_loading_editor',
                        'canvas_preload_critical' => 'pdf_builder_canvas_preload_critical',
                        'canvas_lazy_loading_plugin' => 'pdf_builder_canvas_lazy_loading_plugin'
                    ];

                    foreach ($performance_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key];
                            // Convertir les checkboxes en boolean
                            if (in_array($post_key, ['canvas_lazy_loading_editor', 'canvas_preload_critical', 'canvas_lazy_loading_plugin'])) {
                                $value = $value === '1';
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                        if (in_array($post_key, ['canvas_lazy_loading_editor', 'canvas_preload_critical', 'canvas_lazy_loading_plugin']) && !isset($_POST[$post_key])) {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                        }
                    }
                    break;

                case 'debug':
                    // Sauvegarder les paramètres de debug
                    $debug_mappings = [
                        'canvas_debug_enabled' => 'pdf_builder_canvas_debug_enabled',
                        'canvas_performance_monitoring' => 'pdf_builder_canvas_performance_monitoring',
                        'canvas_error_reporting' => 'pdf_builder_canvas_error_reporting'
                    ];

                    foreach ($debug_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key] === '1';
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        } else {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                        }
                    }
                    break;

                default:
                    send_ajax_response(false, "Catégorie de paramètres non reconnue: " . $category);
                    return;
            }

            // Update the combined canvas settings option for consistency
            $combined_settings = get_option('pdf_builder_canvas_settings', []);
            
            // Map saved values to combined settings keys
            $mappings = [
                'dimensions' => [
                    'canvas_format' => 'default_canvas_format',
                    'canvas_orientation' => 'default_canvas_orientation', 
                    'canvas_dpi' => 'default_canvas_dpi',
                    'canvas_width' => 'canvas_width',
                    'canvas_height' => 'canvas_height',
                ],
                'apparence' => [
                    'canvas_background_color' => 'canvas_background_color',
                    'canvas_container_bg_color' => 'container_background_color',
                    'canvas_border_color' => 'border_color',
                    'canvas_border_width' => 'border_width',
                    'canvas_shadow_enabled' => 'shadow_enabled',
                ],
                'zoom' => [
                    'canvas_zoom_default' => 'default_zoom',
                    'canvas_zoom_min' => 'min_zoom',
                    'canvas_zoom_max' => 'max_zoom',
                    'canvas_zoom_step' => 'zoom_step',
                    'canvas_zoom_with_wheel' => 'zoom_with_wheel',
                    'canvas_pan_enabled' => 'pan_with_mouse',
                ],
                'marges' => [
                    'canvas_margin_top' => 'margin_top',
                    'canvas_margin_right' => 'margin_right',
                    'canvas_margin_bottom' => 'margin_bottom',
                    'canvas_margin_left' => 'margin_left',
                    'canvas_show_margins' => 'show_margins',
                ],
                'grille' => [
                    'canvas_grid_enabled' => 'show_grid',
                    'canvas_grid_size' => 'grid_size',
                    'canvas_grid_color' => 'grid_color',
                    'canvas_snap_to_grid' => 'snap_to_grid',
                    'canvas_guides_enabled' => 'show_guides',
                    'canvas_snap_to_elements' => 'snap_to_elements',
                    'canvas_snap_tolerance' => 'snap_tolerance',
                ],
                'interactions' => [
                    'canvas_drag_enabled' => 'drag_enabled',
                    'canvas_resize_enabled' => 'resize_enabled',
                    'canvas_rotate_enabled' => 'rotate_enabled',
                    'canvas_multi_select' => 'multi_select',
                    'canvas_selection_mode' => 'selection_mode',
                    'canvas_keyboard_shortcuts' => 'keyboard_shortcuts',
                    'canvas_copy_paste_enabled' => 'copy_paste_enabled',
                    'canvas_show_resize_handles' => 'show_resize_handles',
                    'canvas_handle_size' => 'handle_size',
                    'canvas_handle_color' => 'handle_color',
                    'canvas_rotation_step' => 'rotation_step',
                ],
                'export' => [
                    'canvas_export_format' => 'export_format',
                    'canvas_export_quality' => 'export_quality',
                    'canvas_compress_images' => 'compress_images',
                    'canvas_image_quality' => 'image_quality',
                    'canvas_max_image_size' => 'max_image_size',
                    'canvas_include_metadata' => 'include_metadata',
                    'canvas_pdf_author' => 'pdf_author',
                    'canvas_pdf_subject' => 'pdf_subject',
                    'canvas_auto_crop' => 'auto_crop',
                    'canvas_embed_fonts' => 'embed_fonts',
                    'canvas_optimize_for_web' => 'optimize_for_web',
                ],
                'performance' => [
                    'canvas_fps_target' => 'max_fps',
                    'canvas_memory_limit_js' => 'memory_limit_js',
                    'canvas_memory_limit_php' => 'memory_limit_php',
                    'canvas_response_timeout' => 'response_timeout',
                    'canvas_lazy_loading_editor' => 'lazy_loading_editor',
                    'canvas_preload_critical' => 'preload_critical',
                    'canvas_lazy_loading_plugin' => 'lazy_loading_plugin',
                ],
                'debug' => [
                    'canvas_debug_enabled' => 'debug_enabled',
                    'canvas_performance_monitoring' => 'performance_monitoring',
                    'canvas_error_reporting' => 'error_reporting',
                ],
            ];
            
            if (isset($mappings[$category])) {
                foreach ($mappings[$category] as $saved_key => $combined_key) {
                    if (isset($saved_values[$saved_key])) {
                        // Convert types appropriately
                        $value = $saved_values[$saved_key];
                        if (in_array($combined_key, ['default_canvas_dpi', 'canvas_width', 'canvas_height', 'border_width', 'margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'grid_size', 'snap_tolerance', 'handle_size', 'rotation_step', 'export_quality', 'image_quality', 'max_image_size', 'auto_save_interval', 'auto_save_versions', 'max_fps'])) {
                            $value = PDF_Builder_Sanitizer::int($value);
                        } elseif (in_array($combined_key, ['shadow_enabled', 'show_margins', 'show_grid', 'snap_to_grid', 'snap_to_elements', 'show_guides', 'multi_select', 'copy_paste_enabled', 'show_resize_handles', 'enable_rotation', 'compress_images', 'include_metadata', 'auto_crop', 'embed_fonts', 'optimize_for_web', 'auto_save_enabled', 'lazy_loading_editor', 'preload_critical', 'lazy_loading_plugin', 'debug_enabled', 'performance_monitoring', 'error_reporting'])) {
                            $value = (bool)$value;
                        }
                        $combined_settings[$combined_key] = $value;
                    }
                }
                update_option('pdf_builder_canvas_settings', $combined_settings);
            }

            // Debug log
            error_log('AJAX save - category: ' . $category . ', saved_values: ' . json_encode($saved_values));
            error_log('data to send: ' . json_encode(['saved' => $saved_values, 'category' => $category]));

            send_ajax_response(true, 'Paramètres ' . $category . ' sauvegardés avec succès.', ['saved' => $saved_values, 'category' => $category]);
    } catch (Exception $e) {
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

        $category = PDF_Builder_Sanitizer::text($_POST['category'] ?? '');

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
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        send_ajax_response(false, 'Permissions insuffisantes.');
        return;
    }

    // Log de l'onglet en cours d'enregistrement
    $current_tab = PDF_Builder_Sanitizer::text($_POST['current_tab'] ?? 'general');
    error_log('[PDF Builder] SAVE: Enregistrement depuis l\'onglet "' . $current_tab . '"');

    try {
        $saved_count = 0;
        $errors = [];
        $processed_fields = [];
        $ignored_fields = [];
        $js_collected = isset($_POST['collectedFields']) ? json_decode(stripslashes($_POST['collectedFields']), true) : [];

        // Debug: Log tous les champs POST reçus
        error_log('PDF Builder DEBUG - Tous les champs POST reçus: ' . implode(', ', array_keys($_POST)));

        // Traiter tous les champs soumis
        foreach ($_POST as $key => $value) {
            // Ignorer les champs spéciaux
            if (in_array($key, ['action', 'nonce', 'current_tab'])) {
                $ignored_fields[] = $key;
                continue;
            }

            $processed_fields[] = $key;

            try {
                // Préfixer la clé avec pdf_builder_ si elle ne l'a pas déjà
                $option_key = $key;
                if (strpos($key, 'pdf_builder_') !== 0) {
                    $option_key = 'pdf_builder_' . $key;
                }

                // Traiter selon le type de champ
                if (strpos($key, '_enabled') !== false || strpos($key, '_debug') !== false) {
                    // Champs booléens
                    $sanitized_value = $value === '1' || $value === 'true' ? 1 : 0;
                } elseif (is_array($value)) {
                    // Tableaux
                    $sanitized_value = array_map('sanitize_text_field', $value);
                } else {
                    // Texte normal
                    $sanitized_value = sanitize_text_field($value);
                }

                // Sauvegarder l'option
                update_option($option_key, $sanitized_value);
                $saved_count++;

            } catch (Exception $e) {
                $errors[] = "Erreur lors de la sauvegarde de $key: " . $e->getMessage();
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
            'developer_enabled'
        ];

        foreach ($checkbox_fields as $field) {
            if (!isset($_POST[$field])) {
                update_option('pdf_builder_' . $field, 0);
                $saved_count++;
            }
        }

        // Message de succès
        $message = "✅ $saved_count paramètres sauvegardés avec succès.";
        if (!empty($errors)) {
            $message .= " ⚠️ " . count($errors) . " erreurs ignorées.";
        }

        // Préparer les options sauvegardées pour la réponse (sans préfixe pour correspondre aux noms de champs du formulaire)
        $saved_options = [];
        foreach ($processed_fields as $field) {
            $option_key = strpos($field, 'pdf_builder_') === 0 ? $field : 'pdf_builder_' . $field;
            $saved_options[$field] = get_option($option_key, '');
        }

        // Ajouter les champs checkbox traités séparément
        foreach ($checkbox_fields as $field) {
            $saved_options[$field] = get_option('pdf_builder_' . $field, 0) ? '1' : '0';
        }

        send_ajax_response(true, $message, [
            'saved_count' => $saved_count,
            'errors' => $errors,
            'saved_options' => $saved_options,
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
        ]);

        // Debug log détaillé
        error_log('PDF Builder DEBUG - Analyse détaillée:');
        error_log('  Total POST: ' . count($_POST));
        error_log('  Ignorés: ' . count($ignored_fields) . ' - ' . implode(', ', $ignored_fields));
        error_log('  Traités: ' . count($processed_fields));
        error_log('  Sauvegardés: ' . $saved_count);
        error_log('  Erreurs: ' . count($errors));

    } catch (Exception $e) {
        // Debug: Log the exception
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Exception in save_settings: ' . $e->getMessage());
            error_log('PDF Builder: Exception trace: ' . $e->getTraceAsString());
        }
        send_ajax_response(false, 'Error during saving: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for saving cache settings
 */
function pdf_builder_save_cache_settings_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Get form data
        $cache_enabled = !empty($_POST['cache_enabled']) ? '1' : '0';
        $cache_ttl = intval($_POST['cache_ttl'] ?? 3600);
        $cache_compression = !empty($_POST['cache_compression']) ? '1' : '0';
        $cache_auto_cleanup = !empty($_POST['cache_auto_cleanup']) ? '1' : '0';
        $cache_max_size = intval($_POST['cache_max_size'] ?? 100);

        // Validate values
        $cache_ttl = max(300, min(86400, $cache_ttl)); // 5 min to 24 hours
        $cache_max_size = max(10, min(1000, $cache_max_size)); // 10MB to 1GB

        // Save settings
        update_option('pdf_builder_cache_enabled', $cache_enabled);
        update_option('pdf_builder_cache_ttl', $cache_ttl);
        update_option('pdf_builder_cache_compression', $cache_compression);
        update_option('pdf_builder_cache_auto_cleanup', $cache_auto_cleanup);
        update_option('pdf_builder_cache_max_size', $cache_max_size);

        wp_send_json_success(array(
            'message' => 'Paramètres cache sauvegardés avec succès',
            'data' => array(
                'cache_enabled' => $cache_enabled,
                'cache_ttl' => $cache_ttl,
                'cache_compression' => $cache_compression,
                'cache_auto_cleanup' => $cache_auto_cleanup,
                'cache_max_size' => $cache_max_size
            )
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for clearing cache
 */
function pdf_builder_clear_cache_handler() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Clear WordPress cache
        wp_cache_flush();

        // Clear plugin transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        // Clear file cache if exists
        $cache_dirs = array(
            WP_CONTENT_DIR . '/cache/pdf-builder',
            WP_CONTENT_DIR . '/cache/pdf-builder-preview'
        );

        $cleared_files = 0;
        foreach ($cache_dirs as $cache_dir) {
            if (file_exists($cache_dir) && is_dir($cache_dir)) {
                $files = glob($cache_dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file) && unlink($file)) {
                        $cleared_files++;
                    }
                }
            }
        }

        wp_send_json_success(array(
            'message' => 'Cache vidé avec succès',
            'cleared_files' => $cleared_files
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du nettoyage du cache: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for getting cache metrics
 */
function pdf_builder_get_cache_metrics_handler() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        global $wpdb;

        // Get cache settings
        $cache_enabled = get_option('pdf_builder_cache_enabled', '0') === '1';
        $cache_ttl = intval(get_option('pdf_builder_cache_ttl', 3600));

        // Calculate cache size (approximate)
        $cache_size = 0;
        $cache_dirs = array(
            WP_CONTENT_DIR . '/cache/pdf-builder',
            WP_CONTENT_DIR . '/cache/pdf-builder-preview'
        );

        foreach ($cache_dirs as $cache_dir) {
            if (file_exists($cache_dir) && is_dir($cache_dir)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cache_dir));
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $cache_size += $file->getSize();
                    }
                }
            }
        }

        // Count transients
        $transient_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");

        // Get last cleanup time
        $last_cleanup = get_option('pdf_builder_cache_last_cleanup', 'Jamais');
        if ($last_cleanup !== 'Jamais') {
            $last_cleanup = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_cleanup));
        }

        // Format cache size
        $cache_size_formatted = size_format($cache_size);

        wp_send_json_success(array(
            'metrics' => array(
                'cache_enabled' => $cache_enabled,
                'cache_size' => $cache_size_formatted,
                'transient_count' => intval($transient_count),
                'last_cleanup' => $last_cleanup,
                'cache_ttl' => $cache_ttl
            )
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la récupération des métriques: ' . $e->getMessage());
    }
}

/**
 * AJAX handler for getting a fresh nonce
 */
function pdf_builder_get_fresh_nonce_handler() {
    error_log('PDF Builder Get Fresh Nonce: Handler appelé');

    // Check permissions
    if (!current_user_can('manage_options')) {
        error_log('PDF Builder Get Fresh Nonce: Permissions insuffisantes');
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    try {
        // Generate a fresh nonce for cache actions
        $fresh_nonce = wp_create_nonce('pdf_builder_cache_actions');
        error_log('PDF Builder Get Fresh Nonce: Nonce généré: ' . substr($fresh_nonce, 0, 12) . '...');

        wp_send_json_success(array(
            'nonce' => $fresh_nonce
        ));

    } catch (Exception $e) {
        error_log('PDF Builder Get Fresh Nonce: Exception - ' . $e->getMessage());
        wp_send_json_error('Erreur lors de la génération du nonce: ' . $e->getMessage());
    }
}

// Register AJAX actions for canvas settings
add_action('wp_ajax_pdf_builder_save_canvas_settings', 'pdf_builder_save_canvas_settings_handler');
add_action('wp_ajax_pdf_builder_get_canvas_settings', 'pdf_builder_get_canvas_settings_handler');
add_action('wp_ajax_pdf_builder_get_all_canvas_settings', 'pdf_builder_get_all_canvas_settings_handler');
add_action('wp_ajax_pdf_builder_save_all_settings', 'pdf_builder_save_all_settings_handler');
add_action('wp_ajax_pdf_builder_save_cache_settings', 'pdf_builder_save_cache_settings_handler');
add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_handler');
add_action('wp_ajax_pdf_builder_get_cache_metrics', 'pdf_builder_get_cache_metrics_handler');
add_action('wp_ajax_pdf_builder_export_diagnostic', 'pdf_builder_export_diagnostic_handler');
add_action('wp_ajax_pdf_builder_view_logs', 'pdf_builder_view_logs_handler');
add_action('wp_ajax_pdf_builder_optimize_database', 'pdf_builder_optimize_database_handler');
add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_get_fresh_nonce_handler');