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
    if (wp_verify_nonce($_POST['security'], 'pdf_builder_ajax')) {
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
    if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
        $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'general');

    // Traiter directement selon l'onglet
    switch ($current_tab) {
        case 'all':
            try {
                // Helper function to get normalized value from POST
                $get_post_value = function($key) {
                    if (!isset($_POST[$key])) {
                        return null;
                    }
                    // Pour les arrays (noms se terminant par []), retourner l'array complet
                    if (is_array($_POST[$key])) {
                        return $_POST[$key];
                    }
                    // Pour les valeurs simples, retourner la valeur
                    return $_POST[$key];
                };

                // Traitement de tous les paramètres (bouton flottant de sauvegarde)

                // Paramètres généraux
                $value = $get_post_value('debug_mode');
                if ($value !== null) {
                    update_option('pdf_builder_debug_mode', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('log_level');
                if ($value !== null) {
                    update_option('pdf_builder_log_level', sanitize_text_field($value));
                }

                // Paramètres entreprise
                $value = $get_post_value('company_phone_manual');
                if ($value !== null) {
                    update_option('pdf_builder_company_phone_manual', sanitize_text_field($value));
                }
                $value = $get_post_value('company_siret');
                if ($value !== null) {
                    update_option('pdf_builder_company_siret', sanitize_text_field($value));
                }
                $value = $get_post_value('company_vat');
                if ($value !== null) {
                    update_option('pdf_builder_company_vat', sanitize_text_field($value));
                }
                $value = $get_post_value('company_rcs');
                if ($value !== null) {
                    update_option('pdf_builder_company_rcs', sanitize_text_field($value));
                }
                $value = $get_post_value('company_capital');
                if ($value !== null) {
                    update_option('pdf_builder_company_capital', sanitize_text_field($value));
                }

                // Paramètres cache
                $value = $get_post_value('cache_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_cache_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('cache_compression');
                if ($value !== null) {
                    update_option('pdf_builder_cache_compression', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('cache_auto_cleanup');
                if ($value !== null) {
                    update_option('pdf_builder_cache_auto_cleanup', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('cache_max_size');
                if ($value !== null) {
                    update_option('pdf_builder_cache_max_size', intval($value));
                }
                $value = $get_post_value('cache_ttl');
                if ($value !== null) {
                    update_option('pdf_builder_cache_ttl', intval($value));
                }

                // Paramètres système
                $value = $get_post_value('systeme_auto_maintenance');
                if ($value !== null) {
                    update_option('pdf_builder_auto_maintenance', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('performance_auto_optimization');
                if ($value !== null) {
                    update_option('pdf_builder_performance_auto_optimization', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('systeme_auto_backup');
                if ($value !== null) {
                    update_option('pdf_builder_auto_backup', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('systeme_backup_retention');
                if ($value !== null) {
                    update_option('pdf_builder_backup_retention', intval($value));
                }
                $value = $get_post_value('systeme_auto_backup_frequency');
                if ($value !== null) {
                    update_option('pdf_builder_auto_backup_frequency', sanitize_text_field($value));
                }

                // Paramètres d'accès (rôles)
                $value = $get_post_value('pdf_builder_allowed_roles');
                if ($value !== null && is_array($value)) {
                    update_option('pdf_builder_allowed_roles', $value);
                }

                // Paramètres de sécurité
                $value = $get_post_value('security_level');
                if ($value !== null) {
                    update_option('pdf_builder_security_level', sanitize_text_field($value));
                }
                $value = $get_post_value('enable_logging');
                if ($value !== null) {
                    update_option('pdf_builder_enable_logging', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_consent_required');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_consent_required', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_data_retention');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_data_retention', intval($value));
                }
                $value = $get_post_value('gdpr_audit_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_audit_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_encryption_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_encryption_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_consent_analytics');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_consent_analytics', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_consent_templates');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_consent_templates', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('gdpr_consent_marketing');
                if ($value !== null) {
                    update_option('pdf_builder_gdpr_consent_marketing', $value === '1' ? 1 : 0);
                }

                // Paramètres PDF
                $value = $get_post_value('pdf_quality');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_quality', sanitize_text_field($value));
                }
                $value = $get_post_value('pdf_page_size');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_page_size', sanitize_text_field($value));
                }
                $value = $get_post_value('pdf_orientation');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_orientation', sanitize_text_field($value));
                }
                $value = $get_post_value('pdf_cache_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_cache_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('pdf_compression');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_compression', sanitize_text_field($value));
                }
                $value = $get_post_value('pdf_metadata_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_metadata_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('pdf_print_optimized');
                if ($value !== null) {
                    update_option('pdf_builder_pdf_print_optimized', $value === '1' ? 1 : 0);
                }

                // Paramètres de contenu
                $value = $get_post_value('default_template');
                if ($value !== null) {
                    update_option('pdf_builder_default_template', sanitize_text_field($value));
                }
                $value = $get_post_value('template_library_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_template_library_enabled', $value === '1' ? 1 : 0);
                }

                // Paramètres développeur
                $value = $get_post_value('developer_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_developer_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('developer_password');
                if ($value !== null) {
                    update_option('pdf_builder_developer_password', sanitize_text_field($value));
                }
                $value = $get_post_value('license_test_mode');
                if ($value !== null) {
                    update_option('pdf_builder_license_test_mode', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_php_errors');
                if ($value !== null) {
                    update_option('pdf_builder_debug_php_errors', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_javascript');
                if ($value !== null) {
                    update_option('pdf_builder_debug_javascript', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_javascript_verbose');
                if ($value !== null) {
                    update_option('pdf_builder_debug_javascript_verbose', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_ajax');
                if ($value !== null) {
                    update_option('pdf_builder_debug_ajax', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_performance');
                if ($value !== null) {
                    update_option('pdf_builder_debug_performance', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_database');
                if ($value !== null) {
                    update_option('pdf_builder_debug_database', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('log_file_size');
                if ($value !== null) {
                    update_option('pdf_builder_log_file_size', intval($value));
                }
                $value = $get_post_value('log_retention');
                if ($value !== null) {
                    update_option('pdf_builder_log_retention', intval($value));
                }
                $value = $get_post_value('force_https');
                if ($value !== null) {
                    update_option('pdf_builder_force_https', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('performance_monitoring');
                if ($value !== null) {
                    update_option('pdf_builder_performance_monitoring', $value === '1' ? 1 : 0);
                }

                // Paramètres licence
                $value = $get_post_value('enable_expiration_notifications');
                if ($value !== null) {
                    update_option('pdf_builder_license_enable_notifications', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('notification_email');
                if ($value !== null) {
                    update_option('pdf_builder_license_notification_email', sanitize_email($value));
                }

                // Paramètres canvas (toutes catégories) - seulement les champs qui existent dans les modals
                $canvas_option_mappings = [
                    // Dimensions
                    'canvas_format' => 'pdf_builder_canvas_format',
                    'canvas_orientation' => 'pdf_builder_canvas_orientation',
                    'canvas_dpi' => 'pdf_builder_canvas_dpi',

                    // Apparence
                    'canvas_bg_color' => 'pdf_builder_canvas_bg_color',
                    'canvas_container_bg_color' => 'pdf_builder_canvas_container_bg_color',
                    'canvas_border_color' => 'pdf_builder_canvas_border_color',
                    'canvas_border_width' => 'pdf_builder_canvas_border_width',
                    'canvas_shadow_enabled' => 'pdf_builder_canvas_shadow_enabled',

                    // Grille
                    'canvas_guides_enabled' => 'pdf_builder_canvas_guides_enabled',
                    'canvas_grid_enabled' => 'pdf_builder_canvas_grid_enabled',
                    'canvas_grid_size' => 'pdf_builder_canvas_grid_size',
                    'canvas_snap_to_grid' => 'pdf_builder_canvas_snap_to_grid',

                    // Zoom
                    'canvas_zoom_min' => 'pdf_builder_canvas_zoom_min',
                    'canvas_zoom_max' => 'pdf_builder_canvas_zoom_max',
                    'canvas_zoom_default' => 'pdf_builder_canvas_zoom_default',
                    'canvas_zoom_step' => 'pdf_builder_canvas_zoom_step',

                    // Interactions
                    'canvas_selection_mode' => 'pdf_builder_canvas_selection_mode',
                    'canvas_multi_select' => 'pdf_builder_canvas_multi_select',
                    'canvas_drag_enabled' => 'pdf_builder_canvas_drag_enabled',
                    'canvas_resize_enabled' => 'pdf_builder_canvas_resize_enabled',
                    'canvas_rotate_enabled' => 'pdf_builder_canvas_rotate_enabled',
                    'canvas_keyboard_shortcuts' => 'pdf_builder_canvas_keyboard_shortcuts',

                    // Export
                    'canvas_export_format' => 'pdf_builder_canvas_export_format',
                    'canvas_export_quality' => 'pdf_builder_canvas_export_quality',
                    'canvas_export_transparent' => 'pdf_builder_canvas_export_transparent',

                    // Performance
                    'canvas_fps_target' => 'pdf_builder_canvas_fps_target',
                    'canvas_memory_limit_js' => 'pdf_builder_canvas_memory_limit_js',
                    'canvas_memory_limit_php' => 'pdf_builder_canvas_memory_limit_php',
                    'canvas_response_timeout' => 'pdf_builder_canvas_response_timeout',
                    'canvas_lazy_loading_editor' => 'pdf_builder_canvas_lazy_loading_editor',
                    'canvas_preload_critical' => 'pdf_builder_canvas_preload_critical',
                    'canvas_lazy_loading_plugin' => 'pdf_builder_canvas_lazy_loading_plugin',

                    // Autosave
                    'canvas_autosave_enabled' => 'pdf_builder_canvas_autosave_enabled',
                    'canvas_autosave_interval' => 'pdf_builder_canvas_autosave_interval',
                    'canvas_history_enabled' => 'pdf_builder_canvas_history_enabled',
                    'canvas_history_max' => 'pdf_builder_canvas_history_max',

                    // Debug
                    'canvas_debug_enabled' => 'pdf_builder_canvas_debug_enabled',
                    'canvas_performance_monitoring' => 'pdf_builder_canvas_performance_monitoring',
                    'canvas_error_reporting' => 'pdf_builder_canvas_error_reporting'
                ];

                foreach ($canvas_option_mappings as $field => $option_name) {
                    $value = $get_post_value($field);
                    if ($value !== null) {
                        $value = sanitize_text_field($value);
                        // Convert checkbox values
                        $checkbox_fields = [
                            'canvas_shadow_enabled', 'canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid',
                            'canvas_multi_select', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled',
                            'canvas_keyboard_shortcuts', 'canvas_export_transparent', 'canvas_lazy_loading_editor',
                            'canvas_preload_critical', 'canvas_lazy_loading_plugin', 'canvas_autosave_enabled',
                            'canvas_history_enabled', 'canvas_debug_enabled', 'canvas_performance_monitoring', 'canvas_error_reporting'
                        ];
                        if (in_array($field, $checkbox_fields)) {
                            $value = $value === '1' ? 1 : 0;
                        }
                        // Convert numeric values
                        $numeric_fields = [
                            'canvas_border_width', 'canvas_grid_size', 'canvas_zoom_min', 'canvas_zoom_max',
                            'canvas_zoom_default', 'canvas_zoom_step', 'canvas_export_quality', 'canvas_fps_target',
                            'canvas_memory_limit_js', 'canvas_memory_limit_php', 'canvas_response_timeout',
                            'canvas_autosave_interval', 'canvas_history_max'
                        ];
                        if (in_array($field, $numeric_fields)) {
                            $value = intval($value);
                        }
                        update_option($option_name, $value);
                    }
                }

                // Calculer et sauvegarder les dimensions du canvas en pixels
                $format = get_option('pdf_builder_canvas_format', 'A4');
                $orientation = 'portrait'; // FORCÉ EN PORTRAIT - v2.0
                $dpi = get_option('pdf_builder_canvas_dpi', 96);

                // Dimensions standard en mm pour chaque format
                $format_dimensions_mm = [
                    'A4' => ['width' => 210, 'height' => 297],
                    'A3' => ['width' => 297, 'height' => 420],
                    'A5' => ['width' => 148, 'height' => 210],
                    'Letter' => ['width' => 215.9, 'height' => 279.4],
                    'Legal' => ['width' => 215.9, 'height' => 355.6],
                    'Tabloid' => ['width' => 279.4, 'height' => 431.8]
                ];

                $dimensions = $format_dimensions_mm[$format] ?? $format_dimensions_mm['A4'];

                // Orientation temporairement désactivée - toujours portrait
                // Forcer les dimensions portrait (pas d'inversion)
                $width_px = round(($dimensions['width'] / 25.4) * $dpi);   // 794px pour A4
                $height_px = round(($dimensions['height'] / 25.4) * $dpi); // 1123px pour A4

                // Sauvegarder les dimensions calculées
                update_option('pdf_builder_canvas_width', $width_px);
                update_option('pdf_builder_canvas_height', $height_px);

                // Mettre à jour l'option globale des paramètres canvas pour l'éditeur React
                $canvas_settings = [
                    'default_canvas_format' => $format,
                    'default_canvas_orientation' => 'portrait', // FORCÉ EN PORTRAIT - v2.0
                    'default_canvas_dpi' => $dpi,
                    'canvas_width' => $width_px,
                    'canvas_height' => $height_px,
                ];
                update_option('pdf_builder_canvas_settings', $canvas_settings);

                // Paramètres développeur
                $value = $get_post_value('developer_enabled');
                if ($value !== null) {
                    update_option('pdf_builder_developer_enabled', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('developer_password');
                if ($value !== null) {
                    update_option('pdf_builder_developer_password', sanitize_text_field($value));
                }
                $value = $get_post_value('debug_php_errors');
                if ($value !== null) {
                    update_option('pdf_builder_debug_php_errors', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_javascript');
                if ($value !== null) {
                    update_option('pdf_builder_debug_javascript', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_javascript_verbose');
                if ($value !== null) {
                    update_option('pdf_builder_debug_javascript_verbose', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_ajax');
                if ($value !== null) {
                    update_option('pdf_builder_debug_ajax', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_performance');
                if ($value !== null) {
                    update_option('pdf_builder_debug_performance', $value === '1' ? 1 : 0);
                }
                $value = $get_post_value('debug_database');
                if ($value !== null) {
                    update_option('pdf_builder_debug_database', $value === '1' ? 1 : 0);
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
                    update_option('pdf_builder_force_https', $_POST['force_https'] === '1' ? 1 : 0);
                }
                if (isset($_POST['performance_monitoring'])) {
                    update_option('pdf_builder_performance_monitoring', $_POST['performance_monitoring'] === '1' ? 1 : 0);
                }

                // Return the new PDF options to the client for verification
                $saved = [
                    'pdf_metadata_enabled' => get_option('pdf_builder_pdf_metadata_enabled', 0) ? '1' : '0',
                    'pdf_print_optimized' => get_option('pdf_builder_pdf_print_optimized', 0) ? '1' : '0',
                    'pdf_cache_enabled' => get_option('pdf_builder_pdf_cache_enabled', 0) ? '1' : '0',
                    'pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
                    'pdf_page_size' => get_option('pdf_builder_pdf_page_size', 'A4'),
                    'pdf_orientation' => get_option('pdf_builder_pdf_orientation', 'portrait'),
                    'pdf_compression' => get_option('pdf_builder_pdf_compression', 'medium'),
                    // Ajouter tous les autres paramètres sauvegardés
                    'debug_mode' => get_option('pdf_builder_debug_mode', 0) ? '1' : '0',
                    'cache_enabled' => get_option('pdf_builder_cache_enabled', 1) ? '1' : '0',
                    'systeme_auto_maintenance' => get_option('pdf_builder_auto_maintenance', 1) ? '1' : '0',
                    'systeme_auto_backup' => get_option('pdf_builder_auto_backup', 1) ? '1' : '0',
                    'gdpr_enabled' => get_option('pdf_builder_gdpr_enabled', 0) ? '1' : '0'
                ];

                send_ajax_response(true, 'Tous les paramètres ont été sauvegardés avec succès.', ['saved_options' => $saved]);
            } catch (Exception $e) {
                send_ajax_response(false, 'Erreur lors de la sauvegarde: ' . $e->getMessage());
            }
                break;

            case 'contenu':
                // Paramètres de contenu et canvas
                if (isset($_POST['default_template'])) {
                    update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template']));
                }
                if (isset($_POST['template_library_enabled'])) {
                    update_option('pdf_builder_template_library_enabled', $_POST['template_library_enabled'] === '1' ? 1 : 0);
                }
                
                $saved = [
                    'default_template' => get_option('pdf_builder_default_template', 'blank'),
                    'template_library_enabled' => get_option('pdf_builder_template_library_enabled', true) ? '1' : '0'
                ];
                
                send_ajax_response(true, 'Paramètres de contenu sauvegardés avec succès.', ['saved_options' => $saved]);
                break;

            default:
                send_ajax_response(false, 'Onglet non reconnu: ' . $current_tab);
                break;
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}

// Canvas settings AJAX handler
function pdf_builder_save_canvas_settings_handler() {

    if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {

        $category = sanitize_text_field($_POST['category'] ?? 'dimensions');

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
                            $value = sanitize_text_field($_POST[$post_key]);
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        }
                    }

                    // Calculer et sauvegarder les dimensions en pixels
                    $format = get_option('pdf_builder_canvas_format', 'A4');
                    $orientation = get_option('pdf_builder_canvas_orientation', 'portrait');
                    $dpi = intval(get_option('pdf_builder_canvas_dpi', 96));

                    // Dimensions standard en mm pour chaque format
                    $formatDimensionsMM = [
                        'A4' => ['width' => 210, 'height' => 297],
                        'A3' => ['width' => 297, 'height' => 420],
                        'A5' => ['width' => 148, 'height' => 210],
                        'Letter' => ['width' => 215.9, 'height' => 279.4],
                        'Legal' => ['width' => 215.9, 'height' => 355.6],
                        'Tabloid' => ['width' => 279.4, 'height' => 431.8]
                    ];

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
                    error_log('PDF Builder: Saving apparence settings - POST data: ' . print_r($_POST, true));
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
                            } elseif ($post_key === 'canvas_border_width') {
                                $value = intval($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                            error_log("PDF Builder: Saved $option_key = " . print_r($value, true));
                        } elseif ($post_key === 'canvas_shadow_enabled') {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                            error_log("PDF Builder: Saved $option_key = false (checkbox unchecked)");
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
                            } elseif ($post_key === 'canvas_grid_size') {
                                $value = intval($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        } elseif (in_array($post_key, ['canvas_guides_enabled', 'canvas_grid_enabled', 'canvas_snap_to_grid'])) {
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
                        } elseif (in_array($post_key, ['canvas_multi_select', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_keyboard_shortcuts'])) {
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
                            } elseif ($post_key === 'canvas_export_quality') {
                                $value = intval($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        } elseif ($post_key === 'canvas_export_transparent') {
                            // Checkbox non cochée
                            update_option($option_key, false);
                            $saved_values[$post_key] = false;
                        }
                    }
                    break;

                case 'autosave':
                    // Sauvegarder les paramètres d'autosave
                    $autosave_mappings = [
                        'canvas_autosave_enabled' => 'pdf_builder_canvas_autosave_enabled',
                        'canvas_autosave_interval' => 'pdf_builder_canvas_autosave_interval',
                        'canvas_history_enabled' => 'pdf_builder_canvas_history_enabled',
                        'canvas_history_max' => 'pdf_builder_canvas_history_max'
                    ];

                    foreach ($autosave_mappings as $post_key => $option_key) {
                        if (isset($_POST[$post_key])) {
                            $value = $_POST[$post_key];
                            if (in_array($post_key, ['canvas_autosave_enabled', 'canvas_history_enabled'])) {
                                $value = $value === '1';
                            } elseif (in_array($post_key, ['canvas_autosave_interval', 'canvas_history_max'])) {
                                $value = intval($value);
                            }
                            update_option($option_key, $value);
                            $saved_values[$post_key] = $value;
                        } elseif (in_array($post_key, ['canvas_autosave_enabled', 'canvas_history_enabled'])) {
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
                        } elseif (in_array($post_key, ['canvas_lazy_loading_editor', 'canvas_preload_critical', 'canvas_lazy_loading_plugin'])) {
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
                    send_ajax_response(false, 'Catégorie de paramètres non reconnue: ' . $category);
                    return;
            }

            send_ajax_response(true, 'Paramètres ' . $category . ' sauvegardés avec succès.', ['saved' => $saved_values, 'category' => $category]);

        } catch (Exception $e) {
            send_ajax_response(false, 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}// Handler pour récupérer les paramètres canvas
function pdf_builder_get_canvas_settings_handler() {
    try {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
            send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
            return;
        }

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
                'auto_save_enabled' => get_option('pdf_builder_canvas_auto_save', '1') == '1',
                'auto_save_interval' => intval(get_option('pdf_builder_canvas_auto_save_interval', 30)),
                'auto_save_versions' => intval(get_option('pdf_builder_canvas_auto_save_versions', 10)),
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

            error_log('PDF Builder: get_canvas_settings returning: ' . print_r($settings, true));
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
                    'canvas_shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', false)
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

            case 'autosave':
                $values = [
                    'autosave_enabled' => get_option('pdf_builder_canvas_autosave_enabled', true),
                    'autosave_interval' => get_option('pdf_builder_canvas_autosave_interval', 30),
                    'history_enabled' => get_option('pdf_builder_canvas_history_enabled', true),
                    'history_max' => get_option('pdf_builder_canvas_history_max', 50)
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

// Hook AJAX actions - MOVED to pdf-builder-pro.php for global registration
// add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_handler');
// add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_save_settings_handler');
add_action('wp_ajax_pdf_builder_save_canvas_settings', 'pdf_builder_save_canvas_settings_handler');
add_action('wp_ajax_pdf_builder_get_canvas_settings', 'pdf_builder_get_canvas_settings_handler');