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
    if ($success) {
        wp_send_json_success(array_merge(['message' => $message], $data));
    } else {
        wp_send_json_error(['message' => $message]);
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
        case 'all':
            // Traitement de tous les paramètres (bouton flottant de sauvegarde)

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
                    $current_value = get_option('pdf_builder_pdf_cache_enabled', 'not_set');
                    error_log('Before save: pdf_cache_enabled current value: ' . ($current_value === 'not_set' ? 'not_set' : ($current_value ? 'true' : 'false')));
                    error_log('POST pdf_cache_enabled: ' . $_POST['pdf_cache_enabled']);
                    $new_value = $_POST['pdf_cache_enabled'] === '1';
                    $update_result = update_option('pdf_builder_pdf_cache_enabled', $new_value);
                    error_log('Update result: ' . ($update_result ? 'success' : 'failed'));
                    $after_value = get_option('pdf_builder_pdf_cache_enabled', 'not_set');
                    error_log('After save: pdf_cache_enabled value: ' . ($after_value === 'not_set' ? 'not_set' : ($after_value ? 'true' : 'false')));
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

// Hook AJAX actions - MOVED to pdf-builder-pro.php for global registration
// add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_handler');
// add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_save_settings_handler');