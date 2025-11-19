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
                    update_option('pdf_builder_debug_mode', $_POST['debug_mode'] === '1' ? 1 : 0);
                }
                if (isset($_POST['log_level'])) {
                    update_option('pdf_builder_log_level', sanitize_text_field($_POST['log_level']));
                }

                // Paramètres cache
                if (isset($_POST['cache_enabled'])) {
                    update_option('pdf_builder_cache_enabled', $_POST['cache_enabled'] === '1' ? 1 : 0);
                }
                if (isset($_POST['cache_compression'])) {
                    update_option('pdf_builder_cache_compression', $_POST['cache_compression'] === '1' ? 1 : 0);
                }
                if (isset($_POST['cache_auto_cleanup'])) {
                    update_option('pdf_builder_cache_auto_cleanup', $_POST['cache_auto_cleanup'] === '1' ? 1 : 0);
                }
                if (isset($_POST['cache_max_size'])) {
                    update_option('pdf_builder_cache_max_size', intval($_POST['cache_max_size']));
                }
                if (isset($_POST['cache_ttl'])) {
                    update_option('pdf_builder_cache_ttl', intval($_POST['cache_ttl']));
                }

                // Paramètres système
                if (isset($_POST['systeme_auto_maintenance'])) {
                    update_option('pdf_builder_auto_maintenance', $_POST['systeme_auto_maintenance'] === '1' ? 1 : 0);
                }
                if (isset($_POST['systeme_auto_backup'])) {
                    update_option('pdf_builder_auto_backup', $_POST['systeme_auto_backup'] === '1' ? 1 : 0);
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
                    update_option('pdf_builder_enable_logging', $_POST['enable_logging'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_enabled'])) {
                    update_option('pdf_builder_gdpr_enabled', $_POST['gdpr_enabled'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_consent_required'])) {
                    update_option('pdf_builder_gdpr_consent_required', $_POST['gdpr_consent_required'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_data_retention'])) {
                    update_option('pdf_builder_gdpr_data_retention', intval($_POST['gdpr_data_retention']));
                }
                if (isset($_POST['gdpr_audit_enabled'])) {
                    update_option('pdf_builder_gdpr_audit_enabled', $_POST['gdpr_audit_enabled'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_encryption_enabled'])) {
                    update_option('pdf_builder_gdpr_encryption_enabled', $_POST['gdpr_encryption_enabled'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_consent_analytics'])) {
                    update_option('pdf_builder_gdpr_consent_analytics', $_POST['gdpr_consent_analytics'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_consent_templates'])) {
                    update_option('pdf_builder_gdpr_consent_templates', $_POST['gdpr_consent_templates'] === '1' ? 1 : 0);
                }
                if (isset($_POST['gdpr_consent_marketing'])) {
                    update_option('pdf_builder_gdpr_consent_marketing', $_POST['gdpr_consent_marketing'] === '1' ? 1 : 0);
                }

                // Paramètres PDF
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[DEBUG] AJAX save_settings - pdf fields: ' . print_r(array_intersect_key($_POST, array_flip(['pdf_quality','pdf_page_size','pdf_orientation','pdf_cache_enabled','pdf_compression','pdf_metadata_enabled','pdf_print_optimized'])), true));
                }
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
                    update_option('pdf_builder_pdf_cache_enabled', $_POST['pdf_cache_enabled'] === '1' ? 1 : 0);
                }
                if (isset($_POST['pdf_compression'])) {
                    update_option('pdf_builder_pdf_compression', sanitize_text_field($_POST['pdf_compression']));
                }
                if (isset($_POST['pdf_metadata_enabled'])) {
                    update_option('pdf_builder_pdf_metadata_enabled', $_POST['pdf_metadata_enabled'] === '1' ? 1 : 0);
                }
                if (isset($_POST['pdf_print_optimized'])) {
                    update_option('pdf_builder_pdf_print_optimized', $_POST['pdf_print_optimized'] === '1' ? 1 : 0);
                }

                // Paramètres de contenu
                if (isset($_POST['default_template'])) {
                    update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template']));
                }
                if (isset($_POST['template_library_enabled'])) {
                    update_option('pdf_builder_template_library_enabled', $_POST['template_library_enabled'] === '1' ? 1 : 0);
                }

                // Paramètres canvas (toutes catégories)
                $canvas_option_mappings = [
                    // Dimensions
                    'canvas_format' => 'pdf_builder_canvas_format',
                    'canvas_orientation' => 'pdf_builder_canvas_orientation',
                    'canvas_dpi' => 'pdf_builder_canvas_dpi',
                    'canvas_width' => 'pdf_builder_canvas_width',
                    'canvas_height' => 'pdf_builder_canvas_height',
                    
                    // Apparence
                    'canvas_bg_color' => 'pdf_builder_canvas_bg_color',
                    'canvas_border_color' => 'pdf_builder_canvas_border_color',
                    'canvas_border_width' => 'pdf_builder_canvas_border_width',
                    'canvas_shadow_enabled' => 'pdf_builder_canvas_shadow_enabled',
                    
                    // Grille
                    'canvas_grid_enabled' => 'pdf_builder_canvas_grid_enabled',
                    'canvas_grid_size' => 'pdf_builder_canvas_grid_size',
                    'canvas_guides_enabled' => 'pdf_builder_canvas_guides_enabled',
                    'canvas_snap_to_grid' => 'pdf_builder_canvas_snap_to_grid',
                    
                    // Zoom
                    'canvas_zoom_min' => 'pdf_builder_canvas_zoom_min',
                    'canvas_zoom_max' => 'pdf_builder_canvas_zoom_max',
                    'canvas_zoom_default' => 'pdf_builder_canvas_zoom_default',
                    'canvas_pan_enabled' => 'pdf_builder_canvas_pan_enabled',
                    
                    // Interaction
                    'canvas_drag_enabled' => 'pdf_builder_canvas_drag_enabled',
                    'canvas_resize_enabled' => 'pdf_builder_canvas_resize_enabled',
                    'canvas_rotate_enabled' => 'pdf_builder_canvas_rotate_enabled',
                    'canvas_multi_select' => 'pdf_builder_canvas_multi_select',
                    
                    // Comportement
                    'canvas_selection_mode' => 'pdf_builder_canvas_selection_mode',
                    'canvas_keyboard_shortcuts' => 'pdf_builder_canvas_keyboard_shortcuts',
                    'canvas_auto_save' => 'pdf_builder_canvas_auto_save',
                    
                    // Export
                    'canvas_export_format' => 'pdf_builder_canvas_export_format',
                    'canvas_export_quality' => 'pdf_builder_canvas_export_quality',
                    'canvas_export_transparent' => 'pdf_builder_canvas_export_transparent',
                    
                    // Performance
                    'canvas_fps_target' => 'pdf_builder_canvas_fps_target',
                    'canvas_memory_limit' => 'pdf_builder_canvas_memory_limit',
                    'canvas_lazy_loading' => 'pdf_builder_canvas_lazy_loading',
                    
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
                    if (isset($_POST[$field])) {
                        $value = sanitize_text_field($_POST[$field]);
                        // Convert checkbox values
                        $checkbox_fields = [
                            'canvas_shadow_enabled', 'canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid',
                            'canvas_pan_enabled', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled',
                            'canvas_multi_select', 'canvas_keyboard_shortcuts', 'canvas_auto_save', 'canvas_export_transparent',
                            'canvas_lazy_loading', 'canvas_autosave_enabled', 'canvas_history_enabled', 'canvas_debug_enabled',
                            'canvas_performance_monitoring', 'canvas_error_reporting'
                        ];
                        if (in_array($field, $checkbox_fields)) {
                            $value = $value === '1' ? 1 : 0;
                        }
                        // Convert numeric values
                        $numeric_fields = [
                            'canvas_width', 'canvas_height', 'canvas_border_width', 'canvas_grid_size', 'canvas_zoom_min',
                            'canvas_zoom_max', 'canvas_zoom_default', 'canvas_export_quality', 'canvas_fps_target',
                            'canvas_memory_limit', 'canvas_autosave_interval', 'canvas_history_max'
                        ];
                        if (in_array($field, $numeric_fields)) {
                            $value = intval($value);
                        }
                        update_option($option_name, $value);
                    }
                }

                // Return the new PDF options to the client for verification
                $saved = [
                    'pdf_metadata_enabled' => get_option('pdf_builder_pdf_metadata_enabled', 0) ? '1' : '0',
                    'pdf_print_optimized' => get_option('pdf_builder_pdf_print_optimized', 0) ? '1' : '0',
                    'pdf_cache_enabled' => get_option('pdf_builder_pdf_cache_enabled', 0) ? '1' : '0',
                    'pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
                    'pdf_page_size' => get_option('pdf_builder_pdf_page_size', 'A4'),
                    'pdf_orientation' => get_option('pdf_builder_pdf_orientation', 'portrait'),
                    'pdf_compression' => get_option('pdf_builder_pdf_compression', 'medium')
                ];

                send_ajax_response(true, 'Tous les paramètres ont été sauvegardés avec succès.', ['saved_options' => $saved]);
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
                send_ajax_response(false, 'Onglet non reconnu.');
                break;
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}

// Canvas settings AJAX handler
function pdf_builder_save_canvas_settings_handler() {
    if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_save_settings')) {
        // Utiliser le Canvas_Manager pour la sauvegarde centralisée
        if (class_exists('PDF_Builder_Canvas_Manager')) {
            $canvas_manager = new PDF_Builder_Canvas_Manager();
            // Mapper les champs du formulaire vers les noms attendus par le Canvas_Manager
            $settings = [];
            if (isset($_POST['canvas_bg_color'])) {
                $settings['canvas_background_color'] = sanitize_text_field($_POST['canvas_bg_color']);
            }
            if (isset($_POST['canvas_border_color'])) {
                $settings['container_background_color'] = sanitize_text_field($_POST['canvas_border_color']);
            }
            if (isset($_POST['canvas_border_width'])) {
                $settings['border_width'] = intval($_POST['canvas_border_width']);
            }
            if (isset($_POST['canvas_grid_size'])) {
                $settings['grid_size'] = intval($_POST['canvas_grid_size']);
            }
            if (isset($_POST['canvas_width'])) {
                $settings['default_canvas_width'] = intval($_POST['canvas_width']);
            }
            if (isset($_POST['canvas_height'])) {
                $settings['default_canvas_height'] = intval($_POST['canvas_height']);
            }
            if (isset($_POST['canvas_zoom_min'])) {
                $settings['zoom_min'] = intval($_POST['canvas_zoom_min']);
            }
            if (isset($_POST['canvas_zoom_max'])) {
                $settings['zoom_max'] = intval($_POST['canvas_zoom_max']);
            }
            if (isset($_POST['canvas_zoom_default'])) {
                $settings['zoom_default'] = intval($_POST['canvas_zoom_default']);
            }

            // Convertir les checkboxes
            $checkboxes = ['canvas_shadow_enabled', 'canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid', 'canvas_pan_enabled', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts', 'canvas_auto_save'];
            foreach ($checkboxes as $checkbox) {
                if (isset($_POST[$checkbox])) {
                    $settings[str_replace('canvas_', '', $checkbox)] = $_POST[$checkbox] === '1' ? 1 : 0;
                }
            }

            $saved = $canvas_manager->saveSettings($settings);
            if ($saved) {
                send_ajax_response(true, 'Paramètres canvas sauvegardés avec succès.', ['saved' => $settings]);
            } else {
                send_ajax_response(false, 'Erreur lors de la sauvegarde des paramètres canvas.');
            }
        } else {
            send_ajax_response(false, 'Canvas_Manager non disponible.');
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}

// Hook AJAX actions - MOVED to pdf-builder-pro.php for global registration
// add_action('wp_ajax_pdf_builder_clear_cache', 'pdf_builder_clear_cache_handler');
// add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_save_settings_handler');
// add_action('wp_ajax_pdf_builder_save_canvas_settings', 'pdf_builder_save_canvas_settings_handler');