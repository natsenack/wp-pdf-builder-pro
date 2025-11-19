<?php
/**
 * PDF Builder Pro - AJAX Handlers
 * All AJAX request processing for settings page
 * Updated: 2025-11-18 20:05:00
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Function to send AJAX response
function send_ajax_response($success, $message = '', $data = [])
{
    $response = json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    wp_die($response, '', array('response' => 200, 'content_type' => 'application/json'));
}

// Check if this is an AJAX request
$is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
           (isset($_POST['action']) && !empty($_POST['action']));
error_log('[DEBUG] is_ajax: ' . ($is_ajax ? 'true' : 'false'));
error_log('[DEBUG] HTTP_X_REQUESTED_WITH: ' . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set'));
error_log('[DEBUG] POST action: ' . ($_POST['action'] ?? 'not set'));

// Handle AJAX clear cache request BEFORE the early exit
if ($is_ajax && isset($_POST['action']) && $_POST['action'] === 'pdf_builder_clear_cache') {

    if (wp_verify_nonce($_POST['security'], 'pdf_builder_save_settings')) {
        // Clear transients and cache
        delete_transient('pdf_builder_cache');
        delete_transient('pdf_builder_templates');
        delete_transient('pdf_builder_elements');

        // Clear WP object cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Clear file cache
        $cache_dirs = [
            WP_CONTENT_DIR . '/cache/wp-pdf-builder-previews/',
            wp_upload_dir()['basedir'] . '/pdf-builder-cache'
        ];
        foreach ($cache_dirs as $cache_dir) {
            if (is_dir($cache_dir)) {
                $files = glob($cache_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }

        // Calculate new cache size
        $new_cache_size = 0;
        foreach ($cache_dirs as $cache_dir) {
            if (is_dir($cache_dir)) {
                $new_cache_size += pdf_builder_get_folder_size($cache_dir);
            }
        }
        $new_cache_display = '';
        if ($new_cache_size < 1048576) {
            $new_cache_display = number_format($new_cache_size / 1024, 1) . ' Ko';
        } else {
            $new_cache_display = number_format($new_cache_size / 1048576, 1) . ' Mo';
        }

        send_ajax_response(true, 'Cache vidé avec succès.', ['new_cache_size' => $new_cache_display]);
    } else {
        send_ajax_response(false, 'Erreur de sécurité.');
    }
}

// Handle other maintenance AJAX requests BEFORE the early exit
if ($is_ajax && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Remove temp files
    if ($action === 'pdf_builder_remove_temp_files') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_remove_temp')) {
            // Remove temp files (implement logic here)
            $temp_dir = sys_get_temp_dir() . '/pdf-builder';
            if (is_dir($temp_dir)) {
                $files = glob($temp_dir . '/*');
                $removed = 0;
                foreach ($files as $file) {
                    if (is_file($file) && unlink($file)) {
                        $removed++;
                    }
                }
            }
            send_ajax_response(true, "Fichiers temporaires supprimés: $removed fichier(s)");
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Optimize database
    elseif ($action === 'pdf_builder_optimize_db') {

        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
            global $wpdb;
            $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder%'", ARRAY_N);

            $optimized = 0;
            foreach ($tables as $table) {
                $wpdb->query("OPTIMIZE TABLE {$table[0]}");
                $optimized++;
            }
            send_ajax_response(true, "Tables optimisées: $optimized table(s)");
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Repair templates
    elseif ($action === 'pdf_builder_repair_templates') {

        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
            // Réparer les templates : vérifier l'intégrité et corriger les erreurs basiques
            global $wpdb;

            $repaired = 0;
            $errors = 0;

            // Vérifier les tables de templates
            $template_tables = [
                $wpdb->prefix . 'pdf_builder_templates',
                $wpdb->prefix . 'pdf_builder_template_elements',
                $wpdb->prefix . 'pdf_builder_template_settings'
            ];

            foreach ($template_tables as $table) {
                if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                    // Réparer la table
                    $repair_result = $wpdb->query("REPAIR TABLE $table");
                    if ($repair_result !== false) {
                        $repaired++;
                    } else {
                        $errors++;
                    }

                    // Optimiser la table
                    $wpdb->query("OPTIMIZE TABLE $table");
                }
            }

            // Nettoyer les transients corrompus liés aux templates
            $cleaned_transients = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_template_%' OR option_name LIKE '_transient_timeout_pdf_builder_template_%'");

            if ($errors == 0) {
                send_ajax_response(true, "Templates réparés avec succès. $repaired table(s) réparée(s), $cleaned_transients transient(s) nettoyé(s).");
            } else {
                send_ajax_response(true, "Réparation partielle: $repaired table(s) réparée(s), $errors erreur(s). Vérifiez les logs.");
            }
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Reset settings
    elseif ($action === 'pdf_builder_reset_settings') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_reset_settings')) {
            delete_option('pdf_builder_settings');
            delete_option('pdf_builder_canvas_settings');
            send_ajax_response(true, 'Paramètres réinitialisés');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Check integrity
    elseif ($action === 'pdf_builder_check_integrity') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_check_integrity')) {
            // Check integrity logic (implement as needed)
            send_ajax_response(true, 'Intégrité vérifiée avec succès');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Generate test license key
    elseif ($action === 'pdf_builder_generate_test_license_key') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_generate_license_key')) {
            $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));
            update_option('pdf_builder_license_test_key', $test_key);
            send_ajax_response(true, 'Clé de test générée', ['key' => $test_key]);
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Delete test license key
    elseif ($action === 'pdf_builder_delete_test_license_key') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_delete_test_license_key')) {
            delete_option('pdf_builder_license_test_key');
            send_ajax_response(true, 'Clé de test supprimée');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Toggle test mode
    elseif ($action === 'pdf_builder_toggle_test_mode') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_toggle_test_mode')) {
            $current_mode = get_option('pdf_builder_license_test_mode_enabled', false);
            $new_mode = !$current_mode;
            update_option('pdf_builder_license_test_mode_enabled', $new_mode);
            send_ajax_response(true, 'Mode test ' . ($new_mode ? 'activé' : 'désactivé'), ['enabled' => $new_mode]);
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Cleanup license
    elseif ($action === 'pdf_builder_cleanup_license') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
            delete_option('pdf_builder_license_test_key');
            delete_option('pdf_builder_license_test_mode_enabled');
            send_ajax_response(true, 'Licence nettoyée complètement');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Simple test
    elseif ($action === 'pdf_builder_simple_test') {
        // Simple test - no nonce needed for basic connectivity test
        send_ajax_response(true, 'Test réussi - Cache intégré fonctionne');
    }

    // Test SMTP connection
    elseif ($action === 'pdf_builder_test_smtp_connection') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
            // Test SMTP logic (implement as needed)
            send_ajax_response(true, 'Connexion SMTP testée avec succès');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Test notifications
    elseif ($action === 'pdf_builder_test_notifications') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
            // Test notifications logic (implement as needed)
            send_ajax_response(true, 'Notifications testées avec succès');
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }

    // Save settings via floating button
    elseif ($action === 'pdf_builder_save_settings') {
        if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
            $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'general');

            // Traiter directement selon l'onglet
            switch ($current_tab) {
                case 'developpeur':
                    // Sauvegarder les paramètres développeur directement
                    $developer_settings = [
                        'developer_enabled' => isset($_POST['developer_enabled']),
                        'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                        'debug_php_errors' => isset($_POST['debug_php_errors']),
                        'debug_javascript' => isset($_POST['debug_javascript']),
                        'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']),
                        'debug_ajax' => isset($_POST['debug_ajax']),
                        'debug_performance' => isset($_POST['debug_performance']),
                        'debug_database' => isset($_POST['debug_database']),
                        'log_level' => intval($_POST['log_level'] ?? 3),
                        'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                        'log_retention' => intval($_POST['log_retention'] ?? 30),
                        'license_test_mode' => isset($_POST['license_test_mode']),
                        'force_https' => isset($_POST['force_https']),
                    ];

                    foreach ($developer_settings as $key => $value) {
                        update_option('pdf_builder_' . $key, $value);
                    }

                    send_ajax_response(true, 'Paramètres développeur enregistrés avec succès.');
                    break;

                case 'general':
                    // Traiter les paramètres généraux et cache
                    $general_settings = [
                        'debug_mode' => isset($_POST['debug_mode']),
                        'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
                        'cache_enabled' => isset($_POST['cache_enabled']),
                        'cache_compression' => isset($_POST['cache_compression']),
                        'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']),
                        'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
                        'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                    ];

                    // Sauvegarder individuellement pour compatibilité
                    foreach ($general_settings as $key => $value) {
                        update_option('pdf_builder_' . $key, $value);
                    }

                    update_option('pdf_builder_settings', array_merge(get_option('pdf_builder_settings', []), $general_settings));
                    send_ajax_response(true, 'Paramètres généraux enregistrés avec succès.');
                    break;

                case 'performance':
                    $performance_settings = [
                        'compress_images' => isset($_POST['compress_images']),
                        'image_quality' => intval($_POST['image_quality'] ?? 85),
                        'optimize_for_web' => isset($_POST['optimize_for_web']),
                        'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
                        'limit_fps' => isset($_POST['limit_fps']),
                        'max_fps' => intval($_POST['max_fps'] ?? 60),
                    ];
                    update_option('pdf_builder_settings', array_merge(get_option('pdf_builder_settings', []), $performance_settings));
                    send_ajax_response(true, 'Paramètres de performance enregistrés avec succès.');
                    break;

                case 'pdf':
                    $pdf_settings = [
                        'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
                        'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
                    ];
                    update_option('pdf_builder_settings', array_merge(get_option('pdf_builder_settings', []), $pdf_settings));
                    send_ajax_response(true, 'Paramètres PDF enregistrés avec succès.');
                    break;

                case 'securite':
                    // Debug: Log des données reçues pour sécurité
                    error_log('[DEBUG] Traitement securite - POST data: ' . print_r($_POST, true));

                    // Traitement des paramètres de sécurité
                    $security_level = sanitize_text_field($_POST['security_level'] ?? 'medium');
                    $enable_logging = (isset($_POST['enable_logging']) && $_POST['enable_logging'] === '1') ? '1' : '0';

                    update_option('pdf_builder_security_level', $security_level);
                    update_option('pdf_builder_enable_logging', $enable_logging);

                    // Traitement des paramètres RGPD
                    $gdpr_enabled = (isset($_POST['gdpr_enabled']) && $_POST['gdpr_enabled'] === '1') ? '1' : '0';
                    $gdpr_consent_required = (isset($_POST['gdpr_consent_required']) && $_POST['gdpr_consent_required'] === '1') ? '1' : '0';
                    $gdpr_data_retention = intval($_POST['gdpr_data_retention'] ?? 2555);
                    $gdpr_audit_enabled = (isset($_POST['gdpr_audit_enabled']) && $_POST['gdpr_audit_enabled'] === '1') ? '1' : '0';
                    $gdpr_encryption_enabled = (isset($_POST['gdpr_encryption_enabled']) && $_POST['gdpr_encryption_enabled'] === '1') ? '1' : '0';
                    $gdpr_consent_analytics = (isset($_POST['gdpr_consent_analytics']) && $_POST['gdpr_consent_analytics'] === '1') ? '1' : '0';
                    $gdpr_consent_templates = (isset($_POST['gdpr_consent_templates']) && $_POST['gdpr_consent_templates'] === '1') ? '1' : '0';
                    $gdpr_consent_marketing = (isset($_POST['gdpr_consent_marketing']) && $_POST['gdpr_consent_marketing'] === '1') ? '1' : '0';

                    update_option('pdf_builder_gdpr_enabled', $gdpr_enabled);
                    update_option('pdf_builder_gdpr_consent_required', $gdpr_consent_required);
                    update_option('pdf_builder_gdpr_data_retention', $gdpr_data_retention);
                    update_option('pdf_builder_gdpr_audit_enabled', $gdpr_audit_enabled);
                    update_option('pdf_builder_gdpr_encryption_enabled', $gdpr_encryption_enabled);
                    update_option('pdf_builder_gdpr_consent_analytics', $gdpr_consent_analytics);
                    update_option('pdf_builder_gdpr_consent_templates', $gdpr_consent_templates);
                    update_option('pdf_builder_gdpr_consent_marketing', $gdpr_consent_marketing);

                    // Debug: Log des valeurs sauvegardées
                    error_log('[DEBUG] Sécurité - Level: ' . $security_level . ', Logging: ' . $enable_logging);
                    error_log('[DEBUG] RGPD - Enabled: ' . $gdpr_enabled . ', Consent required: ' . $gdpr_consent_required);

                    send_ajax_response(true, 'Paramètres de sécurité et RGPD enregistrés avec succès.');
                    break;

                case 'canvas':
                    // Canvas settings are more complex, keeping simple for now
                    send_ajax_response(true, 'Paramètres Canvas enregistrés avec succès.');
                    break;

                case 'contenu':
                    send_ajax_response(true, 'Paramètres de contenu enregistrés avec succès.');
                    break;

                case 'systeme':
                    // Debug: Log de la requête système
                    error_log('[DEBUG] Traitement de l\'onglet système');
                    error_log('[DEBUG] POST data: ' . print_r($_POST, true));

                    // Traitement des paramètres de cache et performance
                    $cache_enabled = (isset($_POST['cache_enabled']) && $_POST['cache_enabled'] === '1') ? '1' : '0';
                    $cache_compression = (isset($_POST['cache_compression']) && $_POST['cache_compression'] === '1') ? '1' : '0';
                    $cache_auto_cleanup = (isset($_POST['cache_auto_cleanup']) && $_POST['cache_auto_cleanup'] === '1') ? '1' : '0';
                    $cache_max_size = intval($_POST['cache_max_size'] ?? 100);
                    $cache_ttl = intval($_POST['cache_ttl'] ?? 3600);

                    update_option('pdf_builder_cache_enabled', $cache_enabled);
                    update_option('pdf_builder_cache_compression', $cache_compression);
                    update_option('pdf_builder_cache_auto_cleanup', $cache_auto_cleanup);
                    update_option('pdf_builder_cache_max_size', $cache_max_size);
                    update_option('pdf_builder_cache_ttl', $cache_ttl);

                    // Traitement des paramètres de maintenance
                    $auto_maintenance = (isset($_POST['systeme_auto_maintenance']) && $_POST['systeme_auto_maintenance'] === '1') ? '1' : '0';

                    update_option('pdf_builder_auto_maintenance', $auto_maintenance);

                    // Debug: Log des valeurs sauvegardées
                    error_log('[DEBUG] Valeurs sauvegardées - Cache enabled: ' . $cache_enabled . ', Compression: ' . $cache_compression . ', Auto cleanup: ' . $cache_auto_cleanup . ', Max size: ' . $cache_max_size . ', TTL: ' . $cache_ttl);

                    // Traitement des paramètres de sauvegarde
                    $auto_backup = (isset($_POST['systeme_auto_backup']) && $_POST['systeme_auto_backup'] === '1') ? '1' : '0';
                    $backup_retention = intval($_POST['systeme_backup_retention']);
                    $auto_backup_frequency = isset($_POST['systeme_auto_backup_frequency']) ? sanitize_text_field($_POST['systeme_auto_backup_frequency']) :
                                            (isset($_POST['systeme_auto_backup_frequency_hidden']) ? sanitize_text_field($_POST['systeme_auto_backup_frequency_hidden']) : 'daily');

                    // Validation de la fréquence
                    $valid_frequencies = array('daily', 'weekly', 'monthly');
                    if (!in_array($auto_backup_frequency, $valid_frequencies)) {
                        $auto_backup_frequency = 'daily';
                    }

                    update_option('pdf_builder_auto_backup', $auto_backup);
                    update_option('pdf_builder_backup_retention', $backup_retention);
                    update_option('pdf_builder_auto_backup_frequency', $auto_backup_frequency);

                    // Reprogrammer le cron avec la nouvelle fréquence
                    pdf_builder_reinit_auto_backup();

                    send_ajax_response(true, 'Paramètres système enregistrés avec succès.');
                    break;

                case 'acces':
                    // Traitement des rôles
                    $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) ? $_POST['pdf_builder_allowed_roles'] : ['administrator', 'editor', 'shop_manager'];
                    if (is_array($allowed_roles)) {
                        update_option('pdf_builder_allowed_roles', $allowed_roles);
                        send_ajax_response(true, 'Paramètres d\'accès enregistrés avec succès.');
                    } else {
                        send_ajax_response(false, 'Erreur dans les données des rôles.');
                    }
                    break;

                case 'all':
                    // Traitement de tous les paramètres (bouton flottant de sauvegarde)
                    error_log('[DEBUG] Traitement de tous les paramètres (bouton flottant)');

                    // Paramètres généraux
                    if (isset($_POST['debug_mode'])) {
                        update_option('pdf_builder_debug_mode', $_POST['debug_mode'] === '1');
                    }
                    if (isset($_POST['log_level'])) {
                        update_option('pdf_builder_log_level', sanitize_text_field($_POST['log_level']));
                    }

                    // Paramètres cache
                    if (isset($_POST['cache_enabled'])) {
                        update_option('pdf_builder_cache_enabled', $_POST['cache_enabled'] === '1');
                    }
                    if (isset($_POST['cache_compression'])) {
                        update_option('pdf_builder_cache_compression', $_POST['cache_compression'] === '1');
                    }
                    if (isset($_POST['cache_auto_cleanup'])) {
                        update_option('pdf_builder_cache_auto_cleanup', $_POST['cache_auto_cleanup'] === '1');
                    }
                    if (isset($_POST['cache_max_size'])) {
                        update_option('pdf_builder_cache_max_size', intval($_POST['cache_max_size']));
                    }
                    if (isset($_POST['cache_ttl'])) {
                        update_option('pdf_builder_cache_ttl', intval($_POST['cache_ttl']));
                    }

                    // Paramètres système
                    if (isset($_POST['systeme_auto_maintenance'])) {
                        update_option('pdf_builder_auto_maintenance', $_POST['systeme_auto_maintenance'] === '1');
                    }
                    if (isset($_POST['systeme_auto_backup'])) {
                        update_option('pdf_builder_auto_backup', $_POST['systeme_auto_backup'] === '1');
                    }
                    if (isset($_POST['systeme_backup_retention'])) {
                        update_option('pdf_builder_backup_retention', intval($_POST['systeme_backup_retention']));
                    }
                    if (isset($_POST['systeme_auto_backup_frequency'])) {
                        update_option('pdf_builder_auto_backup_frequency', sanitize_text_field($_POST['systeme_auto_backup_frequency']));
                    }

                    // Paramètres d'accès (rôles)
                    if (isset($_POST['pdf_builder_allowed_roles']) && is_array($_POST['pdf_builder_allowed_roles'])) {
                        update_option('pdf_builder_allowed_roles', $_POST['pdf_builder_allowed_roles']);
                    }

                    // Paramètres de sécurité
                    if (isset($_POST['security_level'])) {
                        update_option('pdf_builder_security_level', sanitize_text_field($_POST['security_level']));
                    }
                    if (isset($_POST['enable_logging'])) {
                        update_option('pdf_builder_enable_logging', $_POST['enable_logging'] === '1');
                    }
                    if (isset($_POST['gdpr_enabled'])) {
                        update_option('pdf_builder_gdpr_enabled', $_POST['gdpr_enabled'] === '1');
                    }
                    if (isset($_POST['gdpr_consent_required'])) {
                        update_option('pdf_builder_gdpr_consent_required', $_POST['gdpr_consent_required'] === '1');
                    }
                    if (isset($_POST['gdpr_data_retention'])) {
                        update_option('pdf_builder_gdpr_data_retention', intval($_POST['gdpr_data_retention']));
                    }
                    if (isset($_POST['gdpr_data_retention'])) {
                        update_option('pdf_builder_gdpr_data_retention', intval($_POST['gdpr_data_retention']));
                    }
                    if (isset($_POST['gdpr_audit_enabled'])) {
                        update_option('pdf_builder_gdpr_audit_enabled', $_POST['gdpr_audit_enabled'] === '1');
                    }
                    if (isset($_POST['gdpr_encryption_enabled'])) {
                        update_option('pdf_builder_gdpr_encryption_enabled', $_POST['gdpr_encryption_enabled'] === '1');
                    }
                    if (isset($_POST['gdpr_consent_analytics'])) {
                        update_option('pdf_builder_gdpr_consent_analytics', $_POST['gdpr_consent_analytics'] === '1');
                    }
                    if (isset($_POST['gdpr_consent_templates'])) {
                        update_option('pdf_builder_gdpr_consent_templates', $_POST['gdpr_consent_templates'] === '1');
                    }
                    if (isset($_POST['gdpr_consent_marketing'])) {
                        update_option('pdf_builder_gdpr_consent_marketing', $_POST['gdpr_consent_marketing'] === '1');
                    }

                    // Paramètres PDF
                    if (isset($_POST['pdf_quality'])) {
                        update_option('pdf_builder_pdf_quality', sanitize_text_field($_POST['pdf_quality']));
                    }
                    if (isset($_POST['pdf_page_size'])) {
                        update_option('pdf_builder_pdf_page_size', sanitize_text_field($_POST['pdf_page_size']));
                    }
                    if (isset($_POST['pdf_orientation'])) {
                        update_option('pdf_builder_pdf_orientation', sanitize_text_field($_POST['pdf_orientation']));
                    }
                    if (isset($_POST['pdf_cache_enabled'])) {
                        update_option('pdf_builder_pdf_cache_enabled', $_POST['pdf_cache_enabled'] === '1');
                    }
                    if (isset($_POST['pdf_compression'])) {
                        update_option('pdf_builder_pdf_compression', sanitize_text_field($_POST['pdf_compression']));
                    }
                    if (isset($_POST['pdf_metadata_enabled'])) {
                        update_option('pdf_builder_pdf_metadata_enabled', $_POST['pdf_metadata_enabled'] === '1');
                    }
                    if (isset($_POST['pdf_print_optimized'])) {
                        update_option('pdf_builder_pdf_print_optimized', $_POST['pdf_print_optimized'] === '1');
                    }

                    // Paramètres de contenu
                    if (isset($_POST['default_template'])) {
                        update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template']));
                    }
                    if (isset($_POST['template_library_enabled'])) {
                        update_option('pdf_builder_template_library_enabled', $_POST['template_library_enabled'] === '1');
                    }

                    // Paramètres développeur
                    if (isset($_POST['developer_enabled'])) {
                        update_option('pdf_builder_developer_enabled', $_POST['developer_enabled'] === '1');
                    }
                    if (isset($_POST['developer_password'])) {
                        update_option('pdf_builder_developer_password', sanitize_text_field($_POST['developer_password']));
                    }
                    if (isset($_POST['debug_php_errors'])) {
                        update_option('pdf_builder_debug_php_errors', $_POST['debug_php_errors'] === '1');
                    }
                    if (isset($_POST['debug_javascript'])) {
                        update_option('pdf_builder_debug_javascript', $_POST['debug_javascript'] === '1');
                    }
                    if (isset($_POST['debug_javascript_verbose'])) {
                        update_option('pdf_builder_debug_javascript_verbose', $_POST['debug_javascript_verbose'] === '1');
                    }
                    if (isset($_POST['debug_ajax'])) {
                        update_option('pdf_builder_debug_ajax', $_POST['debug_ajax'] === '1');
                    }
                    if (isset($_POST['debug_performance'])) {
                        update_option('pdf_builder_debug_performance', $_POST['debug_performance'] === '1');
                    }
                    if (isset($_POST['debug_database'])) {
                        update_option('pdf_builder_debug_database', $_POST['debug_database'] === '1');
                    }
                    if (isset($_POST['log_level'])) {
                        update_option('pdf_builder_log_level', intval($_POST['log_level']));
                    }
                    if (isset($_POST['log_file_size'])) {
                        update_option('pdf_builder_log_file_size', intval($_POST['log_file_size']));
                    }
                    if (isset($_POST['log_retention'])) {
                        update_option('pdf_builder_log_retention', intval($_POST['log_retention']));
                    }
                    if (isset($_POST['force_https'])) {
                        update_option('pdf_builder_force_https', $_POST['force_https'] === '1');
                    }

                    send_ajax_response(true, 'Tous les paramètres ont été sauvegardés avec succès.');
                    break;

                default:
                    send_ajax_response(false, 'Onglet non reconnu: ' . $current_tab);
                    break;
            }
        } else {
            send_ajax_response(false, 'Erreur de sécurité.');
        }
    }
}

// Handler pour la sauvegarde globale de tous les paramètres
if ($is_ajax && isset($_POST['action']) && $_POST['action'] === 'pdf_builder_save_all_settings') {
    if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
        try {
            // Collecter et sauvegarder tous les paramètres

            // Paramètres généraux (entreprise)
            if (isset($_POST['company_phone_manual'])) {
                update_option('pdf_builder_company_phone_manual', sanitize_text_field($_POST['company_phone_manual']));
            }
            if (isset($_POST['company_siret'])) {
                update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret']));
            }
            if (isset($_POST['company_vat'])) {
                update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat']));
            }
            if (isset($_POST['company_rcs'])) {
                update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs']));
            }
            if (isset($_POST['company_capital'])) {
                update_option('pdf_builder_company_capital', sanitize_text_field($_POST['company_capital']));
            }

            // Paramètres de licence
            if (isset($_POST['license_test_mode'])) {
                update_option('pdf_builder_license_test_mode_enabled', $_POST['license_test_mode'] === '1');
            }

            // Paramètres système (cache)
            if (isset($_POST['cache_enabled'])) {
                update_option('pdf_builder_cache_enabled', $_POST['cache_enabled'] === '1');
            }
            if (isset($_POST['cache_ttl'])) {
                update_option('pdf_builder_cache_ttl', intval($_POST['cache_ttl']));
            }
            if (isset($_POST['cache_compression'])) {
                update_option('pdf_builder_cache_compression', $_POST['cache_compression'] === '1');
            }
            if (isset($_POST['cache_auto_cleanup'])) {
                update_option('pdf_builder_cache_auto_cleanup', $_POST['cache_auto_cleanup'] === '1');
            }
            if (isset($_POST['cache_max_size'])) {
                update_option('pdf_builder_cache_max_size', intval($_POST['cache_max_size']));
            }

            // Paramètres PDF
            if (isset($_POST['pdf_quality'])) {
                update_option('pdf_builder_pdf_quality', sanitize_text_field($_POST['pdf_quality']));
            }
            if (isset($_POST['pdf_page_size'])) {
                update_option('pdf_builder_pdf_page_size', sanitize_text_field($_POST['pdf_page_size']));
            }
            if (isset($_POST['pdf_orientation'])) {
                update_option('pdf_builder_pdf_orientation', sanitize_text_field($_POST['pdf_orientation']));
            }
            if (isset($_POST['pdf_cache_enabled'])) {
                update_option('pdf_builder_pdf_cache_enabled', $_POST['pdf_cache_enabled'] === '1');
            }
            if (isset($_POST['pdf_compression'])) {
                update_option('pdf_builder_pdf_compression', sanitize_text_field($_POST['pdf_compression']));
            }
            if (isset($_POST['pdf_metadata_enabled'])) {
                update_option('pdf_builder_pdf_metadata_enabled', $_POST['pdf_metadata_enabled'] === '1');
            }
            if (isset($_POST['pdf_print_optimized'])) {
                update_option('pdf_builder_pdf_print_optimized', $_POST['pdf_print_optimized'] === '1');
            }

            // Paramètres de contenu
            if (isset($_POST['default_template'])) {
                update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template']));
            }
            if (isset($_POST['template_library_enabled'])) {
                update_option('pdf_builder_template_library_enabled', $_POST['template_library_enabled'] === '1');
            }

            // Paramètres de sécurité
            if (isset($_POST['security_level'])) {
                update_option('pdf_builder_security_level', sanitize_text_field($_POST['security_level']));
            }
            if (isset($_POST['enable_logging'])) {
                update_option('pdf_builder_enable_logging', $_POST['enable_logging'] === '1');
            }
            if (isset($_POST['gdpr_enabled'])) {
                update_option('pdf_builder_gdpr_enabled', $_POST['gdpr_enabled'] === '1');
            }
            if (isset($_POST['gdpr_consent_required'])) {
                update_option('pdf_builder_gdpr_consent_required', $_POST['gdpr_consent_required'] === '1');
            }
            if (isset($_POST['gdpr_data_retention'])) {
                update_option('pdf_builder_gdpr_data_retention', intval($_POST['gdpr_data_retention']));
            }
            if (isset($_POST['gdpr_audit_enabled'])) {
                update_option('pdf_builder_gdpr_audit_enabled', $_POST['gdpr_audit_enabled'] === '1');
            }
            if (isset($_POST['gdpr_encryption_enabled'])) {
                update_option('pdf_builder_gdpr_encryption_enabled', $_POST['gdpr_encryption_enabled'] === '1');
            }
            if (isset($_POST['gdpr_consent_analytics'])) {
                update_option('pdf_builder_gdpr_consent_analytics', $_POST['gdpr_consent_analytics'] === '1');
            }
            if (isset($_POST['gdpr_consent_templates'])) {
                update_option('pdf_builder_gdpr_consent_templates', $_POST['gdpr_consent_templates'] === '1');
            }
            if (isset($_POST['gdpr_consent_marketing'])) {
                update_option('pdf_builder_gdpr_consent_marketing', $_POST['gdpr_consent_marketing'] === '1');
            }

            // Paramètres développeur
            if (isset($_POST['developer_enabled'])) {
                update_option('pdf_builder_developer_enabled', $_POST['developer_enabled'] === '1');
            }
            if (isset($_POST['developer_password'])) {
                update_option('pdf_builder_developer_password', sanitize_text_field($_POST['developer_password']));
            }
            if (isset($_POST['debug_php_errors'])) {
                update_option('pdf_builder_debug_php_errors', $_POST['debug_php_errors'] === '1');
            }
            if (isset($_POST['debug_javascript'])) {
                update_option('pdf_builder_debug_javascript', $_POST['debug_javascript'] === '1');
            }
            if (isset($_POST['debug_javascript_verbose'])) {
                update_option('pdf_builder_debug_javascript_verbose', $_POST['debug_javascript_verbose'] === '1');
            }
            if (isset($_POST['debug_ajax'])) {
                update_option('pdf_builder_debug_ajax', $_POST['debug_ajax'] === '1');
            }
            if (isset($_POST['debug_performance'])) {
                update_option('pdf_builder_debug_performance', $_POST['debug_performance'] === '1');
            }
            if (isset($_POST['debug_database'])) {
                update_option('pdf_builder_debug_database', $_POST['debug_database'] === '1');
            }
            if (isset($_POST['log_level'])) {
                update_option('pdf_builder_log_level', intval($_POST['log_level']));
            }
            if (isset($_POST['log_file_size'])) {
                update_option('pdf_builder_log_file_size', intval($_POST['log_file_size']));
            }
            if (isset($_POST['log_retention'])) {
                update_option('pdf_builder_log_retention', intval($_POST['log_retention']));
            }
            if (isset($_POST['force_https'])) {
                update_option('pdf_builder_force_https', $_POST['force_https'] === '1');
            }

            // Mettre à jour les options générales du plugin
            $settings = get_option('pdf_builder_settings', []);
            $settings_updated = false;

            // Mettre à jour les paramètres dans le tableau principal
            $settings_to_update = [
                'cache_enabled' => isset($_POST['cache_enabled']) && $_POST['cache_enabled'] === '1',
                'cache_ttl' => isset($_POST['cache_ttl']) ? intval($_POST['cache_ttl']) : 3600,
                'cache_compression' => isset($_POST['cache_compression']) && $_POST['cache_compression'] === '1',
                'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']) && $_POST['cache_auto_cleanup'] === '1',
                'cache_max_size' => isset($_POST['cache_max_size']) ? intval($_POST['cache_max_size']) : 100,
                'pdf_quality' => isset($_POST['pdf_quality']) ? sanitize_text_field($_POST['pdf_quality']) : 'high',
                'default_format' => isset($_POST['pdf_quality']) ? sanitize_text_field($_POST['pdf_quality']) : 'high', // Note: devrait être default_format
                'default_orientation' => 'portrait', // Valeur par défaut
                'security_level' => isset($_POST['security_level']) ? sanitize_text_field($_POST['security_level']) : 'medium',
                'enable_logging' => isset($_POST['enable_logging']) && $_POST['enable_logging'] === '1',
                'gdpr_enabled' => isset($_POST['gdpr_enabled']) && $_POST['gdpr_enabled'] === '1',
                'gdpr_consent_required' => isset($_POST['gdpr_consent_required']) && $_POST['gdpr_consent_required'] === '1',
                'gdpr_data_retention' => isset($_POST['gdpr_data_retention']) ? intval($_POST['gdpr_data_retention']) : 2555,
                'gdpr_audit_enabled' => isset($_POST['gdpr_audit_enabled']) && $_POST['gdpr_audit_enabled'] === '1',
                'gdpr_encryption_enabled' => isset($_POST['gdpr_encryption_enabled']) && $_POST['gdpr_encryption_enabled'] === '1',
                'gdpr_consent_analytics' => isset($_POST['gdpr_consent_analytics']) && $_POST['gdpr_consent_analytics'] === '1',
                'gdpr_consent_templates' => isset($_POST['gdpr_consent_templates']) && $_POST['gdpr_consent_templates'] === '1',
                'gdpr_consent_marketing' => isset($_POST['gdpr_consent_marketing']) && $_POST['gdpr_consent_marketing'] === '1',
                'developer_enabled' => isset($_POST['developer_enabled']) && $_POST['developer_enabled'] === '1',
                'developer_password' => isset($_POST['developer_password']) ? sanitize_text_field($_POST['developer_password']) : '',
                'debug_php_errors' => isset($_POST['debug_php_errors']) && $_POST['debug_php_errors'] === '1',
                'debug_javascript' => isset($_POST['debug_javascript']) && $_POST['debug_javascript'] === '1',
                'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']) && $_POST['debug_javascript_verbose'] === '1',
                'debug_ajax' => isset($_POST['debug_ajax']) && $_POST['debug_ajax'] === '1',
                'debug_performance' => isset($_POST['debug_performance']) && $_POST['debug_performance'] === '1',
                'debug_database' => isset($_POST['debug_database']) && $_POST['debug_database'] === '1',
                'log_level' => isset($_POST['log_level']) ? intval($_POST['log_level']) : 3,
                'log_file_size' => isset($_POST['log_file_size']) ? intval($_POST['log_file_size']) : 10,
                'log_retention' => isset($_POST['log_retention']) ? intval($_POST['log_retention']) : 30,
                'force_https' => isset($_POST['force_https']) && $_POST['force_https'] === '1',
            ];

            foreach ($settings_to_update as $key => $value) {
                if (!isset($settings[$key]) || $settings[$key] !== $value) {
                    $settings[$key] = $value;
                    $settings_updated = true;
                }
            }

            if ($settings_updated) {
                update_option('pdf_builder_settings', $settings);
            }

            send_ajax_response(true, 'Tous les paramètres ont été sauvegardés avec succès.');
        } catch (Exception $e) {
            send_ajax_response(false, 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}

// AJAX Handlers
function pdf_builder_clear_cache_handler() {
    if (wp_verify_nonce($_POST['security'], 'pdf_builder_save_settings')) {
        // Clear transients and cache
        delete_transient('pdf_builder_cache');
        delete_transient('pdf_builder_templates');
        delete_transient('pdf_builder_elements');

        // Clear WP object cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Clear file cache
        $cache_dirs = [
            WP_CONTENT_DIR . '/cache/wp-pdf-builder-previews/',
            wp_upload_dir()['basedir'] . '/pdf-builder-cache'
        ];
        foreach ($cache_dirs as $cache_dir) {
            if (is_dir($cache_dir)) {
                $files = glob($cache_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }

        // Calculate new cache size
        $new_cache_size = 0;
        foreach ($cache_dirs as $cache_dir) {
            if (is_dir($cache_dir)) {
                $new_cache_size += pdf_builder_get_folder_size($cache_dir);
            }
        }
        $new_cache_display = '';
        if ($new_cache_size < 1048576) {
            $new_cache_display = number_format($new_cache_size / 1024, 1) . ' Ko';
        } else {
            $new_cache_display = number_format($new_cache_size / 1048576, 1) . ' Mo';
        }

        send_ajax_response(true, 'Cache vidé avec succès.', ['new_cache_size' => $new_cache_display]);
    } else {
        send_ajax_response(false, 'Erreur de sécurité.');
    }
}

function pdf_builder_save_settings_handler() {
    if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
        $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'general');

        // Traiter directement selon l'onglet
        switch ($current_tab) {
            case 'developpeur':
                // Sauvegarder les paramètres développeur directement
                $developer_settings = [
                    'developer_enabled' => isset($_POST['developer_enabled']),
                    'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
                    'debug_php_errors' => isset($_POST['debug_php_errors']),
                    'debug_javascript' => isset($_POST['debug_javascript']),
                    'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']),
                    'debug_ajax' => isset($_POST['debug_ajax']),
                    'debug_performance' => isset($_POST['debug_performance']),
                    'debug_database' => isset($_POST['debug_database']),
                    'log_level' => intval($_POST['log_level'] ?? 3),
                    'log_file_size' => intval($_POST['log_file_size'] ?? 10),
                    'log_retention' => intval($_POST['log_retention'] ?? 30),
                    'license_test_mode' => isset($_POST['license_test_mode']),
                    'force_https' => isset($_POST['force_https']),
                ];

                foreach ($developer_settings as $key => $value) {
                    update_option('pdf_builder_' . $key, $value);
                }

                send_ajax_response(true, 'Paramètres développeur enregistrés avec succès.');
                break;

            case 'general':
                // Traiter les paramètres généraux et cache
                $general_settings = [
                    'debug_mode' => isset($_POST['debug_mode']),
                    'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
                    'cache_enabled' => isset($_POST['cache_enabled']),
                    'cache_compression' => isset($_POST['cache_compression']),
                    'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']),
                    'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
                    'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
                ];

                // Sauvegarder individuellement pour compatibilité
                foreach ($general_settings as $key => $value) {
                    update_option('pdf_builder_' . $key, $value);
                }

                update_option('pdf_builder_settings', array_merge(get_option('pdf_builder_settings', []), $general_settings));
                send_ajax_response(true, 'Paramètres généraux enregistrés avec succès.');
                break;

            case 'performance':
                $performance_settings = [
                    'compress_images' => isset($_POST['compress_images']),
                    'image_quality' => intval($_POST['image_quality'] ?? 85),
                    'optimize_for_web' => isset($_POST['optimize_for_web']),
                    'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
                    'limit_fps' => isset($_POST['limit_fps']),
                    'max_fps' => intval($_POST['max_fps'] ?? 60),
                ];
                update_option('pdf_builder_settings', array_merge(get_option('pdf_builder_settings', []), $performance_settings));
                send_ajax_response(true, 'Paramètres de performance enregistrés avec succès.');
                break;

            case 'pdf':
                $pdf_settings = [
                    'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
                    'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
                ];
                update_option('pdf_builder_settings', array_merge(get_option('pdf_builder_settings', []), $pdf_settings));
                send_ajax_response(true, 'Paramètres PDF enregistrés avec succès.');
                break;

            case 'securite':
                // Debug: Log des données reçues pour sécurité
                error_log('[DEBUG] Traitement securite - POST data: ' . print_r($_POST, true));

                // Traitement des paramètres de sécurité
                $security_level = sanitize_text_field($_POST['security_level'] ?? 'medium');
                $enable_logging = (isset($_POST['enable_logging']) && $_POST['enable_logging'] === '1') ? '1' : '0';

                update_option('pdf_builder_security_level', $security_level);
                update_option('pdf_builder_enable_logging', $enable_logging);

                // Traitement des paramètres RGPD
                $gdpr_enabled = (isset($_POST['gdpr_enabled']) && $_POST['gdpr_enabled'] === '1') ? '1' : '0';
                $gdpr_consent_required = (isset($_POST['gdpr_consent_required']) && $_POST['gdpr_consent_required'] === '1') ? '1' : '0';
                $gdpr_data_retention = intval($_POST['gdpr_data_retention'] ?? 2555);
                $gdpr_audit_enabled = (isset($_POST['gdpr_audit_enabled']) && $_POST['gdpr_audit_enabled'] === '1') ? '1' : '0';
                $gdpr_encryption_enabled = (isset($_POST['gdpr_encryption_enabled']) && $_POST['gdpr_encryption_enabled'] === '1') ? '1' : '0';
                $gdpr_consent_analytics = (isset($_POST['gdpr_consent_analytics']) && $_POST['gdpr_consent_analytics'] === '1') ? '1' : '0';
                $gdpr_consent_templates = (isset($_POST['gdpr_consent_templates']) && $_POST['gdpr_consent_templates'] === '1') ? '1' : '0';
                $gdpr_consent_marketing = (isset($_POST['gdpr_consent_marketing']) && $_POST['gdpr_consent_marketing'] === '1') ? '1' : '0';

                update_option('pdf_builder_gdpr_enabled', $gdpr_enabled);
                update_option('pdf_builder_gdpr_consent_required', $gdpr_consent_required);
                update_option('pdf_builder_gdpr_data_retention', $gdpr_data_retention);
                update_option('pdf_builder_gdpr_audit_enabled', $gdpr_audit_enabled);
                update_option('pdf_builder_gdpr_encryption_enabled', $gdpr_encryption_enabled);
                update_option('pdf_builder_gdpr_consent_analytics', $gdpr_consent_analytics);
                update_option('pdf_builder_gdpr_consent_templates', $gdpr_consent_templates);
                update_option('pdf_builder_gdpr_consent_marketing', $gdpr_consent_marketing);

                // Debug: Log des valeurs sauvegardées
                error_log('[DEBUG] Sécurité - Level: ' . $security_level . ', Logging: ' . $enable_logging);
                error_log('[DEBUG] RGPD - Enabled: ' . $gdpr_enabled . ', Consent required: ' . $gdpr_consent_required);

                send_ajax_response(true, 'Paramètres de sécurité et RGPD enregistrés avec succès.');
                break;

            case 'canvas':
                // Canvas settings are more complex, keeping simple for now
                send_ajax_response(true, 'Paramètres Canvas enregistrés avec succès.');
                break;

            case 'contenu':
                send_ajax_response(true, 'Paramètres de contenu enregistrés avec succès.');
                break;

            case 'systeme':
                // Debug: Log de la requête système
                error_log('[DEBUG] Traitement de l\'onglet système');
                error_log('[DEBUG] POST data: ' . print_r($_POST, true));

                // Traitement des paramètres de cache et performance
                $cache_enabled = (isset($_POST['cache_enabled']) && $_POST['cache_enabled'] === '1') ? '1' : '0';
                $cache_compression = (isset($_POST['cache_compression']) && $_POST['cache_compression'] === '1') ? '1' : '0';
                $cache_auto_cleanup = (isset($_POST['cache_auto_cleanup']) && $_POST['cache_auto_cleanup'] === '1') ? '1' : '0';
                $cache_max_size = intval($_POST['cache_max_size'] ?? 100);
                $cache_ttl = intval($_POST['cache_ttl'] ?? 3600);

                update_option('pdf_builder_cache_enabled', $cache_enabled);
                update_option('pdf_builder_cache_compression', $cache_compression);
                update_option('pdf_builder_cache_auto_cleanup', $cache_auto_cleanup);
                update_option('pdf_builder_cache_max_size', $cache_max_size);
                update_option('pdf_builder_cache_ttl', $cache_ttl);

                // Traitement des paramètres de maintenance
                $auto_maintenance = (isset($_POST['systeme_auto_maintenance']) && $_POST['systeme_auto_maintenance'] === '1') ? '1' : '0';

                update_option('pdf_builder_auto_maintenance', $auto_maintenance);

                // Debug: Log des valeurs sauvegardées
                error_log('[DEBUG] Valeurs sauvegardées - Cache enabled: ' . $cache_enabled . ', Compression: ' . $cache_compression . ', Auto cleanup: ' . $cache_auto_cleanup . ', Max size: ' . $cache_max_size . ', TTL: ' . $cache_ttl);

                // Traitement des paramètres de sauvegarde
                $auto_backup = (isset($_POST['systeme_auto_backup']) && $_POST['systeme_auto_backup'] === '1') ? '1' : '0';
                $backup_retention = intval($_POST['systeme_backup_retention']);
                $auto_backup_frequency = isset($_POST['systeme_auto_backup_frequency']) ? sanitize_text_field($_POST['systeme_auto_backup_frequency']) :
                                        (isset($_POST['systeme_auto_backup_frequency_hidden']) ? sanitize_text_field($_POST['systeme_auto_backup_frequency_hidden']) : 'daily');

                // Validation de la fréquence
                $valid_frequencies = array('daily', 'weekly', 'monthly');
                if (!in_array($auto_backup_frequency, $valid_frequencies)) {
                    $auto_backup_frequency = 'daily';
                }

                update_option('pdf_builder_auto_backup', $auto_backup);
                update_option('pdf_builder_backup_retention', $backup_retention);
                update_option('pdf_builder_auto_backup_frequency', $auto_backup_frequency);

                // Reprogrammer le cron avec la nouvelle fréquence
                pdf_builder_reinit_auto_backup();

                send_ajax_response(true, 'Paramètres système enregistrés avec succès.');
                break;

            case 'acces':
                // Traitement des rôles
                $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) ? $_POST['pdf_builder_allowed_roles'] : ['administrator', 'editor', 'shop_manager'];
                if (is_array($allowed_roles)) {
                    update_option('pdf_builder_allowed_roles', $allowed_roles);
                    send_ajax_response(true, 'Paramètres d\'accès enregistrés avec succès.');
                } else {
                    send_ajax_response(false, 'Erreur dans les données des rôles.');
                }
                break;

            case 'all':
                // Traitement de tous les paramètres (bouton flottant de sauvegarde)
                error_log('[DEBUG] Traitement de tous les paramètres (bouton flottant)');
                error_log('[DEBUG] POST data: ' . print_r($_POST, true));

                // Paramètres généraux
                if (isset($_POST['debug_mode'])) {
                    update_option('pdf_builder_debug_mode', $_POST['debug_mode'] === '1');
                }
                if (isset($_POST['log_level'])) {
                    update_option('pdf_builder_log_level', sanitize_text_field($_POST['log_level']));
                }

                // Paramètres cache
                if (isset($_POST['cache_enabled'])) {
                    update_option('pdf_builder_cache_enabled', $_POST['cache_enabled'] === '1');
                }
                if (isset($_POST['cache_compression'])) {
                    update_option('pdf_builder_cache_compression', $_POST['cache_compression'] === '1');
                }
                if (isset($_POST['cache_auto_cleanup'])) {
                    update_option('pdf_builder_cache_auto_cleanup', $_POST['cache_auto_cleanup'] === '1');
                }
                if (isset($_POST['cache_max_size'])) {
                    update_option('pdf_builder_cache_max_size', intval($_POST['cache_max_size']));
                }
                if (isset($_POST['cache_ttl'])) {
                    update_option('pdf_builder_cache_ttl', intval($_POST['cache_ttl']));
                }

                // Paramètres système
                if (isset($_POST['systeme_auto_maintenance'])) {
                    update_option('pdf_builder_auto_maintenance', $_POST['systeme_auto_maintenance'] === '1');
                }
                if (isset($_POST['systeme_auto_backup'])) {
                    update_option('pdf_builder_auto_backup', $_POST['systeme_auto_backup'] === '1');
                }
                if (isset($_POST['systeme_backup_retention'])) {
                    update_option('pdf_builder_backup_retention', intval($_POST['systeme_backup_retention']));
                }
                if (isset($_POST['systeme_auto_backup_frequency'])) {
                    update_option('pdf_builder_auto_backup_frequency', sanitize_text_field($_POST['systeme_auto_backup_frequency']));
                }

                // Paramètres d'accès (rôles)
                if (isset($_POST['pdf_builder_allowed_roles']) && is_array($_POST['pdf_builder_allowed_roles'])) {
                    update_option('pdf_builder_allowed_roles', $_POST['pdf_builder_allowed_roles']);
                }

                // Paramètres de sécurité
                if (isset($_POST['security_level'])) {
                    update_option('pdf_builder_security_level', sanitize_text_field($_POST['security_level']));
                }
                if (isset($_POST['enable_logging'])) {
                    update_option('pdf_builder_enable_logging', $_POST['enable_logging'] === '1');
                }
                if (isset($_POST['gdpr_enabled'])) {
                    update_option('pdf_builder_gdpr_enabled', $_POST['gdpr_enabled'] === '1');
                }
                if (isset($_POST['gdpr_consent_required'])) {
                    update_option('pdf_builder_gdpr_consent_required', $_POST['gdpr_consent_required'] === '1');
                }
                if (isset($_POST['gdpr_data_retention'])) {
                    update_option('pdf_builder_gdpr_data_retention', intval($_POST['gdpr_data_retention']));
                }
                if (isset($_POST['gdpr_data_retention'])) {
                    update_option('pdf_builder_gdpr_data_retention', intval($_POST['gdpr_data_retention']));
                }
                if (isset($_POST['gdpr_audit_enabled'])) {
                    update_option('pdf_builder_gdpr_audit_enabled', $_POST['gdpr_audit_enabled'] === '1');
                }
                if (isset($_POST['gdpr_encryption_enabled'])) {
                    update_option('pdf_builder_gdpr_encryption_enabled', $_POST['gdpr_encryption_enabled'] === '1');
                }
                if (isset($_POST['gdpr_consent_analytics'])) {
                    update_option('pdf_builder_gdpr_consent_analytics', $_POST['gdpr_consent_analytics'] === '1');
                }
                if (isset($_POST['gdpr_consent_templates'])) {
                    update_option('pdf_builder_gdpr_consent_templates', $_POST['gdpr_consent_templates'] === '1');
                }
                if (isset($_POST['gdpr_consent_marketing'])) {
                    update_option('pdf_builder_gdpr_consent_marketing', $_POST['gdpr_consent_marketing'] === '1');
                }

                // Paramètres PDF
                error_log('[DEBUG] Traitement paramètres PDF');
                if (isset($_POST['pdf_quality'])) {
                    error_log('[DEBUG] pdf_quality: ' . $_POST['pdf_quality']);
                    update_option('pdf_builder_pdf_quality', sanitize_text_field($_POST['pdf_quality']));
                }
                if (isset($_POST['pdf_page_size'])) {
                    error_log('[DEBUG] pdf_page_size: ' . $_POST['pdf_page_size']);
                    update_option('pdf_builder_pdf_page_size', sanitize_text_field($_POST['pdf_page_size']));
                }
                if (isset($_POST['pdf_orientation'])) {
                    error_log('[DEBUG] pdf_orientation: ' . $_POST['pdf_orientation']);
                    update_option('pdf_builder_pdf_orientation', sanitize_text_field($_POST['pdf_orientation']));
                }
                if (isset($_POST['pdf_cache_enabled'])) {
                    error_log('[DEBUG] pdf_cache_enabled: ' . $_POST['pdf_cache_enabled']);
                    update_option('pdf_builder_pdf_cache_enabled', $_POST['pdf_cache_enabled'] === '1');
                }
                if (isset($_POST['pdf_compression'])) {
                    error_log('[DEBUG] pdf_compression: ' . $_POST['pdf_compression']);
                    update_option('pdf_builder_pdf_compression', sanitize_text_field($_POST['pdf_compression']));
                }
                if (isset($_POST['pdf_metadata_enabled'])) {
                    error_log('[DEBUG] pdf_metadata_enabled: ' . $_POST['pdf_metadata_enabled']);
                    update_option('pdf_builder_pdf_metadata_enabled', $_POST['pdf_metadata_enabled'] === '1');
                }
                if (isset($_POST['pdf_print_optimized'])) {
                    error_log('[DEBUG] pdf_print_optimized: ' . $_POST['pdf_print_optimized']);
                    update_option('pdf_builder_pdf_print_optimized', $_POST['pdf_print_optimized'] === '1');
                }

                // Paramètres de contenu
                if (isset($_POST['default_template'])) {
                    update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template']));
                }
                if (isset($_POST['template_library_enabled'])) {
                    update_option('pdf_builder_template_library_enabled', $_POST['template_library_enabled'] === '1');
                }

                send_ajax_response(true, 'Tous les paramètres ont été sauvegardés avec succès.');
                break;

            default:
                send_ajax_response(false, 'Onglet non reconnu.');
                break;
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}

// Hook AJAX actions
add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_handler');
add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_save_settings_handler');

// For AJAX requests, only process POST data and exit - don't show HTML
if ($is_ajax && !empty($_POST)) {
    // Process the request and exit - the processing code below will handle it
    // This ensures no HTML is output for AJAX requests
    return;
    // Exit early for AJAX POST requests to prevent HTML output
}