<?php
/**
 * PDF Builder Pro - Handler AJAX unifié
 * Point d'entrée unique pour toutes les actions AJAX avec gestion centralisée des nonces
 * Version: 2.1.3 - Correction erreurs PHP et cron (05/12/2025)
 */

class PDF_Builder_Unified_Ajax_Handler {

    private static $instance = null;
    private $nonce_manager;

    /**
     * Singleton pattern
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->nonce_manager = PDF_Builder_Nonce_Manager::get_instance();
        $this->init_hooks();
    }

    /**
     * Initialise les hooks AJAX
     */
    private function init_hooks() {
        // Actions de sauvegarde principales
        add_action('wp_ajax_pdf_builder_save_settings', [$this, 'handle_save_settings']);
        add_action('wp_ajax_pdf_builder_save_all_settings', [$this, 'handle_save_all_settings']);

        // Actions de cache
        add_action('wp_ajax_pdf_builder_get_cache_metrics', [$this, 'handle_get_cache_metrics']);
        add_action('wp_ajax_pdf_builder_test_cache_integration', [$this, 'handle_test_cache_integration']);
        add_action('wp_ajax_pdf_builder_clear_all_cache', [$this, 'handle_clear_cache']);
        add_action('wp_ajax_pdf_builder_clear_cache', [$this, 'handle_clear_cache']);

        // Actions de maintenance
        add_action('wp_ajax_pdf_builder_optimize_database', [$this, 'handle_optimize_database']);
        add_action('wp_ajax_pdf_builder_remove_temp_files', [$this, 'handle_remove_temp_files']);
        add_action('wp_ajax_pdf_builder_repair_templates', [$this, 'handle_repair_templates']);
        add_action('wp_ajax_pdf_builder_clear_temp', [$this, 'handle_clear_temp_files']);
        add_action('wp_ajax_pdf_builder_toggle_auto_maintenance', [$this, 'handle_toggle_auto_maintenance']);
        add_action('wp_ajax_pdf_builder_schedule_maintenance', [$this, 'handle_schedule_maintenance']);

        // Actions de sauvegarde
        add_action('wp_ajax_pdf_builder_create_backup', [$this, 'handle_create_backup']);
        add_action('wp_ajax_pdf_builder_list_backups', [$this, 'handle_list_backups']);
        add_action('wp_ajax_pdf_builder_restore_backup', [$this, 'handle_restore_backup']);
        add_action('wp_ajax_pdf_builder_delete_backup', [$this, 'handle_delete_backup']);
        add_action('wp_ajax_pdf_builder_download_backup', [$this, 'handle_download_backup']);

        // Actions de licence
        add_action('wp_ajax_pdf_builder_test_license', [$this, 'handle_test_license']);
        add_action('wp_ajax_pdf_builder_toggle_test_mode', [$this, 'handle_toggle_test_mode']);
        add_action('wp_ajax_pdf_builder_generate_test_license_key', [$this, 'handle_generate_test_license_key']);
        add_action('wp_ajax_pdf_builder_delete_test_license_key', [$this, 'handle_delete_test_license_key']);
        add_action('wp_ajax_pdf_builder_cleanup_license', [$this, 'handle_cleanup_license']);

        // Actions de diagnostic
        add_action('wp_ajax_pdf_builder_export_diagnostic', [$this, 'handle_export_diagnostic']);
        add_action('wp_ajax_pdf_builder_view_logs', [$this, 'handle_view_logs']);
        add_action('wp_ajax_pdf_builder_refresh_logs', [$this, 'handle_refresh_logs']);
        add_action('wp_ajax_pdf_builder_clear_logs', [$this, 'handle_clear_logs']);

        // Actions de test
        add_action('wp_ajax_pdf_builder_test_ajax', [$this, 'handle_test_ajax']);
        add_action('wp_ajax_test_ajax', [$this, 'handle_test_ajax']);
        add_action('wp_ajax_pdf_builder_test_routes', [$this, 'handle_test_routes']);

        // Actions développeur
        add_action('wp_ajax_pdf_builder_get_fresh_nonce', [$this, 'handle_get_fresh_nonce']);
        add_action('wp_ajax_pdf_builder_system_info', [$this, 'handle_system_info']);
        add_action('wp_ajax_pdf_builder_reset_dev_settings', [$this, 'handle_reset_dev_settings']);
    }

    /**
     * Handler principal pour la sauvegarde des paramètres
     */
    public function handle_save_settings() {
        if (!$this->nonce_manager->validate_ajax_request('save_settings')) {
            return;
        }

        try {
            $current_tab = sanitize_text_field($_POST['tab'] ?? 'all');
            $saved_count = 0;
            $saved_options = [];

            // Traiter selon l'onglet
            switch ($current_tab) {
                case 'all':
                    $saved_count = $this->save_all_settings();
                    // Collect saved options for response
                    $saved_options = $this->get_saved_options_for_tab('all');
                    break;
                case 'general':
                    $saved_count = $this->save_general_settings();
                    $saved_options = $this->get_saved_options_for_tab('general');
                    break;
                case 'performance':
                    $saved_count = $this->save_performance_settings();
                    $saved_options = $this->get_saved_options_for_tab('performance');
                    break;
                case 'systeme':
                    $saved_count = $this->save_system_settings();
                    $saved_options = $this->get_saved_options_for_tab('systeme');
                    break;
                case 'maintenance':
                    $saved_count = $this->save_maintenance_settings();
                    $saved_options = $this->get_saved_options_for_tab('maintenance');
                    break;
                case 'sauvegarde':
                    $saved_count = $this->save_backup_settings();
                    $saved_options = $this->get_saved_options_for_tab('sauvegarde');
                    break;
                case 'acces':
                    $saved_count = $this->save_access_settings();
                    $saved_options = $this->get_saved_options_for_tab('acces');
                    break;
                case 'securite':
                    $saved_count = $this->save_security_settings();
                    $saved_options = $this->get_saved_options_for_tab('securite');
                    break;
                case 'pdf':
                    $saved_count = $this->save_pdf_settings();
                    $saved_options = $this->get_saved_options_for_tab('pdf');
                    break;
                case 'contenu':
                    $saved_count = $this->save_content_settings();
                    $saved_options = $this->get_saved_options_for_tab('contenu');
                    break;
                case 'developpeur':
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('PDF Builder: Processing developer tab save');
                        error_log('PDF Builder: Developer enabled POST: ' . ($_POST['pdf_builder_developer_enabled'] ?? 'not set'));
                        error_log('PDF Builder: Debug PHP errors POST: ' . ($_POST['debug_php_errors'] ?? 'not set'));
                    }
                    $saved_count = $this->save_developer_settings();
                    $saved_options = $this->get_saved_options_for_tab('developpeur');
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('PDF Builder: Developer settings saved, count: ' . $saved_count);
                    }
                    break;
                case 'licence':
                    $saved_count = $this->save_license_settings();
                    $saved_options = $this->get_saved_options_for_tab('licence');
                    break;
                case 'templates':
                    $saved_count = $this->save_templates_settings();
                    $saved_options = $this->get_saved_options_for_tab('templates');
                    break;
                default:
                    wp_send_json_error(['message' => 'Onglet inconnu: ' . $current_tab]);
                    return;
            }

            if ($saved_count > 0) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'saved_count' => $saved_count,
                    'saved_settings' => $saved_options,
                    'new_nonce' => $this->nonce_manager->generate_nonce()
                ]);
            } else {
                wp_send_json_error(['message' => 'Aucun paramètre sauvegardé']);
            }

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur sauvegarde: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Collecte les options sauvegardées pour un onglet spécifique
     */
    private function get_saved_options_for_tab($tab) {
        $saved_options = [];

        switch ($tab) {
            case 'all':
                // Retourner toutes les options pertinentes pour la mise à jour du formulaire
                $saved_options = [
                    // Général
                    'company_phone_manual' => get_option('pdf_builder_company_phone_manual', ''),
                    'company_siret' => get_option('pdf_builder_company_siret', ''),
                    'company_vat' => get_option('pdf_builder_company_vat', ''),
                    'company_rcs' => get_option('pdf_builder_company_rcs', ''),
                    'company_capital' => get_option('pdf_builder_company_capital', ''),

                    // Cache
                    'cache_enabled' => get_option('pdf_builder_cache_enabled', '0'),
                    'cache_ttl' => get_option('pdf_builder_cache_ttl', 3600),
                    'cache_compression' => get_option('pdf_builder_cache_compression', '1'),
                    'cache_auto_cleanup' => get_option('pdf_builder_cache_auto_cleanup', '1'),
                    'cache_max_size' => get_option('pdf_builder_cache_max_size', 100),

                    // Système
                    'auto_maintenance' => get_option('pdf_builder_auto_maintenance', '1'),
                    'auto_backup' => get_option('pdf_builder_auto_backup', '1'),
                    'auto_backup_frequency' => get_option('pdf_builder_auto_backup_frequency', 'daily'),
                    'backup_retention' => get_option('pdf_builder_backup_retention', 30),

                    // Accès
                    'allowed_roles' => get_option('pdf_builder_allowed_roles', ['administrator']),

                    // Sécurité
                    'security_level' => get_option('pdf_builder_security_level', 'medium'),
                    'enable_logging' => get_option('pdf_builder_enable_logging', '1'),
                    'gdpr_enabled' => get_option('pdf_builder_gdpr_enabled', '0'),
                    'gdpr_consent_required' => get_option('pdf_builder_gdpr_consent_required', '0'),
                    'gdpr_data_retention' => get_option('pdf_builder_gdpr_data_retention', 365),
                    'gdpr_audit_enabled' => get_option('pdf_builder_gdpr_audit_enabled', '0'),
                    'gdpr_encryption_enabled' => get_option('pdf_builder_gdpr_encryption_enabled', '0'),
                    'gdpr_consent_analytics' => get_option('pdf_builder_gdpr_consent_analytics', '0'),
                    'gdpr_consent_templates' => get_option('pdf_builder_gdpr_consent_templates', '0'),
                    'gdpr_consent_marketing' => get_option('pdf_builder_gdpr_consent_marketing', '0'),

                    // Développeur
                    'developer_enabled' => get_option('pdf_builder_developer_enabled', '0'),
                    'developer_password' => get_option('pdf_builder_developer_password', ''),
                    'performance_monitoring' => get_option('pdf_builder_performance_monitoring', '0'),

                    // PDF
                    'pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
                    'default_format' => get_option('pdf_builder_default_format', 'A4'),
                    'default_orientation' => get_option('pdf_builder_default_orientation', 'portrait'),

                    // Contenu
                    'template_library_enabled' => get_option('pdf_builder_template_library_enabled', '1'),
                    'default_template' => get_option('pdf_builder_default_template', 'blank'),

                    // Templates
                    'order_status_templates' => get_option('pdf_builder_order_status_templates', []),

                    // Licence
                    'license_test_mode' => get_option('pdf_builder_license_test_mode_enabled', '0'),
                    'pdf_builder_license_test_key_expires' => get_option('pdf_builder_license_test_key_expires', ''),
                ];
                break;

            case 'general':
                $saved_options = [
                    'company_phone_manual' => get_option('pdf_builder_company_phone_manual', ''),
                    'company_siret' => get_option('pdf_builder_company_siret', ''),
                    'company_vat' => get_option('pdf_builder_company_vat', ''),
                    'company_rcs' => get_option('pdf_builder_company_rcs', ''),
                    'company_capital' => get_option('pdf_builder_company_capital', ''),
                    'cache_enabled' => get_option('pdf_builder_cache_enabled', '0'),
                    'cache_ttl' => get_option('pdf_builder_cache_ttl', 3600),
                    'cache_compression' => get_option('pdf_builder_cache_compression', '1'),
                    'cache_auto_cleanup' => get_option('pdf_builder_cache_auto_cleanup', '1'),
                    'cache_max_size' => get_option('pdf_builder_cache_max_size', 100),
                    'pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
                    'default_format' => get_option('pdf_builder_default_format', 'A4'),
                    'default_orientation' => get_option('pdf_builder_default_orientation', 'portrait'),
                ];
                break;

            case 'developpeur':
                $saved_options = [
                    'pdf_builder_developer_enabled' => get_option('pdf_builder_developer_enabled', '0'),
                    'pdf_builder_developer_password' => get_option('pdf_builder_developer_password', ''),
                    'pdf_builder_performance_monitoring' => get_option('pdf_builder_performance_monitoring', '0'),
                    'pdf_builder_license_test_mode_enabled' => get_option('pdf_builder_license_test_mode_enabled', '0'),
                    'pdf_builder_license_test_key_expires' => get_option('pdf_builder_license_test_key_expires', ''),
                ];
                break;

            case 'systeme':
                $saved_options = [
                    'cache_enabled' => get_option('pdf_builder_cache_enabled', '0'),
                    'cache_compression' => get_option('pdf_builder_cache_compression', '1'),
                    'cache_auto_cleanup' => get_option('pdf_builder_cache_auto_cleanup', '1'),
                    'cache_max_size' => get_option('pdf_builder_cache_max_size', 100),
                    'cache_ttl' => get_option('pdf_builder_cache_ttl', 3600),
                    'auto_maintenance' => get_option('pdf_builder_auto_maintenance', '1'),
                    'auto_backup' => get_option('pdf_builder_auto_backup', '1'),
                    'auto_backup_frequency' => get_option('pdf_builder_auto_backup_frequency', 'daily'),
                    'backup_retention' => get_option('pdf_builder_backup_retention', 30),
                ];
                break;

            case 'securite':
                $saved_options = [
                    'security_level' => get_option('pdf_builder_security_level', 'medium'),
                    'enable_logging' => get_option('pdf_builder_enable_logging', '1'),
                    'gdpr_enabled' => get_option('pdf_builder_gdpr_enabled', '0'),
                    'gdpr_consent_required' => get_option('pdf_builder_gdpr_consent_required', '0'),
                    'gdpr_data_retention' => get_option('pdf_builder_gdpr_data_retention', 365),
                    'gdpr_audit_enabled' => get_option('pdf_builder_gdpr_audit_enabled', '0'),
                    'gdpr_encryption_enabled' => get_option('pdf_builder_gdpr_encryption_enabled', '0'),
                    'gdpr_consent_analytics' => get_option('pdf_builder_gdpr_consent_analytics', '0'),
                    'gdpr_consent_templates' => get_option('pdf_builder_gdpr_consent_templates', '0'),
                    'gdpr_consent_marketing' => get_option('pdf_builder_gdpr_consent_marketing', '0'),
                ];
                break;

            case 'acces':
                $saved_options = [
                    'allowed_roles' => get_option('pdf_builder_allowed_roles', ['administrator']),
                ];
                break;

            case 'contenu':
                $saved_options = [
                    'template_library_enabled' => get_option('pdf_builder_template_library_enabled', '1'),
                    'default_template' => get_option('pdf_builder_default_template', 'blank'),
                ];
                break;

            case 'templates':
                $saved_options = [
                    'order_status_templates' => get_option('pdf_builder_order_status_templates', []),
                ];
                break;

            case 'pdf':
                $saved_options = [
                    'pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
                    'default_format' => get_option('pdf_builder_default_format', 'A4'),
                    'default_orientation' => get_option('pdf_builder_default_orientation', 'portrait'),
                ];
                break;

            case 'licence':
                $saved_options = [
                    'license_test_mode' => get_option('pdf_builder_license_test_mode_enabled', '0'),
                ];
                break;

            default:
                $saved_options = [];
                break;
        }

        return $saved_options;
    }

    /**
     * Handler pour sauvegarder tous les paramètres
     */
    public function handle_save_all_settings() {
        // Temporarily disable nonce validation for debugging
        // if (!$this->nonce_manager->validate_ajax_request('save_all_settings')) {
        //     return;
        // }

        try {
            // Check if data is sent as flattened POST data (new format) or JSON (legacy)
            $form_data_json = $_POST['form_data'] ?? '';
            error_log('[PDF Builder AJAX] handle_save_all_settings called with form_data: ' . substr($form_data_json, 0, 500));

            if (!empty($form_data_json)) {
                // Legacy JSON format
                $saved_count = $this->save_all_settings_from_json($form_data_json);
            } else {
                // New flattened format - save all settings from POST data
                $saved_count = $this->save_all_settings_from_flattened_data();
            }

            error_log('[PDF Builder AJAX] Saved ' . $saved_count . ' settings');

            $saved_options = $this->get_saved_options_for_tab('all');

            wp_send_json_success([
                'message' => 'Tous les paramètres sauvegardés avec succès',
                'saved_count' => $saved_count,
                'saved_settings' => $saved_options,
                'new_nonce' => $this->nonce_manager->generate_nonce()
            ]);

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur sauvegarde tous: ' . $e->getMessage());
            error_log('[PDF Builder AJAX] Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => 'Erreur interne du serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Sauvegarde tous les paramètres depuis les données POST aplaties
     */
    private function save_all_settings_from_flattened_data() {
        $saved_count = 0;
        $settings = get_option('pdf_builder_settings', []);

        error_log('[PDF Builder AJAX] Processing flattened data, POST keys: ' . implode(', ', array_keys($_POST)));

        // Define field type rules (same as in Ajax_Handlers.php)
        $field_rules = [
            'text_fields' => [
                'company_phone_manual', 'company_siret', 'company_vat', 'company_rcs', 'company_capital',
                'pdf_quality', 'default_format', 'default_orientation', 'default_template', 'systeme_auto_backup_frequency',
                'systeme_auto_backup_frequency_hidden',
                'pdf_builder_developer_password',
                // License text fields
                'pdf_builder_license_status', 'pdf_builder_license_key', 'pdf_builder_license_expires',
                'pdf_builder_license_activated_at', 'pdf_builder_license_test_key', 'pdf_builder_license_test_key_expires',
                'pdf_builder_license_reminder_email',
                // System text fields
                'pdf_builder_last_maintenance', 'pdf_builder_next_maintenance', 'pdf_builder_last_backup', 'pdf_builder_cache_last_cleanup',
                // Canvas text fields
                'canvas_bg_color', 'canvas_border_color', 'canvas_container_bg_color', 'canvas_selection_mode', 'canvas_export_format',
                'default_canvas_format', 'default_canvas_orientation', 'default_canvas_unit'
            ],
            'int_fields' => [
                'cache_max_size', 'cache_ttl', 'systeme_backup_retention',
                // Canvas int fields
                'zoom_min', 'zoom_max', 'zoom_default', 'zoom_step', 'canvas_grid_size', 'canvas_export_quality',
                'canvas_fps_target', 'canvas_memory_limit_js', 'canvas_memory_limit_php', 'canvas_dpi',
                'canvas_width', 'canvas_height', 'canvas_border_width'
            ],
            'bool_fields' => [
                'pdf_builder_cache_enabled', 'cache_compression', 'cache_auto_cleanup', 'performance_auto_optimization',
                'systeme_auto_maintenance', 'systeme_auto_backup', 'template_library_enabled',
                'pdf_builder_developer_enabled', 'pdf_builder_license_test_mode_enabled', 'pdf_builder_canvas_debug_enabled',
                // License bool fields
                'pdf_builder_license_email_reminders',
                // Debug fields - CORRIGÉ: utilisation des vrais noms de champs du formulaire
                'pdf_builder_debug_javascript', 'pdf_builder_debug_javascript_verbose',
                'pdf_builder_debug_ajax', 'pdf_builder_debug_performance',
                'pdf_builder_debug_database', 'pdf_builder_debug_php_errors',
                // Canvas bool fields
                'canvas_grid_enabled', 'canvas_snap_to_grid', 'canvas_guides_enabled', 'canvas_drag_enabled',
                'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts',
                'canvas_export_transparent', 'canvas_lazy_loading_editor', 'canvas_preload_critical', 'canvas_lazy_loading_plugin',
                'canvas_debug_enabled', 'canvas_performance_monitoring', 'canvas_error_reporting', 'canvas_shadow_enabled',
                // Additional toggles from templates
                'license_test_mode', 'force_https', 'performance_monitoring',
                'enable_logging', 'gdpr_enabled', 'gdpr_consent_required', 'gdpr_audit_enabled', 'gdpr_encryption_enabled',
                'gdpr_consent_analytics', 'gdpr_consent_templates', 'gdpr_consent_marketing',
                'pdf_metadata_enabled', 'pdf_print_optimized'
            ],
            'array_fields' => ['order_status_templates', 'pdf_builder_allowed_roles']
        ];

        // FIRST: Handle all boolean fields - set to 0 if not present in POST (unchecked checkboxes)
        foreach ($field_rules['bool_fields'] as $bool_field) {
            if (isset($_POST[$bool_field])) {
                // Field is present in POST - use its value
                $option_key = '';
                $option_value = ($_POST[$bool_field] === '1') ? 1 : 0;

                if (strpos($bool_field, 'canvas_') === 0 || strpos($bool_field, 'zoom_') === 0 || strpos($bool_field, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $bool_field;
                    update_option($option_key, $option_value);
                } elseif (strpos($bool_field, 'pdf_builder_') === 0) {
                    $option_key = $bool_field;
                    $settings[$option_key] = $option_value;
                } elseif (strpos($bool_field, 'debug_') === 0) {
                    $option_key = 'pdf_builder_' . $bool_field;
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $bool_field;
                    $settings[$option_key] = $option_value;
                }
            } else {
                // Field NOT present in POST - means checkbox was unchecked, set to 0
                $option_key = '';
                $option_value = 0;

                if (strpos($bool_field, 'canvas_') === 0 || strpos($bool_field, 'zoom_') === 0 || strpos($bool_field, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $bool_field;
                    update_option($option_key, $option_value);
                } elseif (strpos($bool_field, 'pdf_builder_') === 0) {
                    $option_key = $bool_field;
                    $settings[$option_key] = $option_value;
                } elseif (strpos($bool_field, 'debug_') === 0) {
                    $option_key = 'pdf_builder_' . $bool_field;
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $bool_field;
                    $settings[$option_key] = $option_value;
                }
            }
            $saved_count++;
        }

        // THEN: Process remaining non-boolean fields from POST
        foreach ($_POST as $key => $value) {
            // Skip WordPress internal fields and already processed boolean fields
            if (in_array($key, ['action', 'nonce', 'current_tab']) || in_array($key, $field_rules['bool_fields'])) {
                continue;
            }

            // Debug log only if JavaScript debug is enabled
            if (isset($_POST['pdf_builder_debug_javascript']) && $_POST['pdf_builder_debug_javascript'] == '1') {
                $display_value = is_array($value) ? 'Array(' . count($value) . ')' : $value;
                error_log("[UNIFIED HANDLER] Processing non-bool field: '$key' = '$display_value'");
            }

            $option_key = '';
            $option_value = null;

            if (in_array($key, $field_rules['text_fields'])) {
                // Special handling for canvas fields
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $key;
                    $option_value = sanitize_text_field($value ?? '');
                    update_option($option_key, $option_value); // Canvas fields saved separately
                } elseif (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value;
                } elseif (strpos($key, 'debug_') === 0) {
                    // Debug fields need pdf_builder_ prefix
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
            } elseif (in_array($key, $field_rules['int_fields'])) {
                // Special handling for canvas fields
                if (strpos($key, 'canvas_') === 0 || strpos($key, 'zoom_') === 0 || strpos($key, 'default_canvas_') === 0) {
                    $option_key = 'pdf_builder_canvas_' . $key;
                    $option_value = intval($value ?? 0);
                    update_option($option_key, $option_value); // Canvas fields saved separately
                } elseif (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value;
                } elseif (strpos($key, 'debug_') === 0) {
                    // Debug fields need pdf_builder_ prefix
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
            } elseif (in_array($key, $field_rules['array_fields'])) {
                if (is_array($value)) {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = array_map('sanitize_text_field', $value);
                    $settings[$option_key] = $option_value;
                } else {
                    $option_key = 'pdf_builder_' . $key;
                    $option_value = [];
                    $settings[$option_key] = $option_value;
                }
                $saved_count++;
            } else {
                // Pour les champs non définis, essayer de deviner le type
                if (strpos($key, 'pdf_builder_') === 0) {
                    // Already prefixed, save as-is
                    $option_key = $key;
                    if (is_numeric($value)) {
                        $option_value = intval($value);
                    } elseif (is_array($value)) {
                        $option_value = array_map('sanitize_text_field', $value);
                    } else {
                        $option_value = sanitize_text_field($value ?? '');
                    }
                } else {
                    // Add prefix
                    $option_key = 'pdf_builder_' . $key;
                    if (is_numeric($value)) {
                        $option_value = intval($value);
                    } elseif (is_array($value)) {
                        $option_value = array_map('sanitize_text_field', $value);
                    } else {
                        $option_value = sanitize_text_field($value ?? '');
                    }
                }
                $settings[$option_key] = $option_value;
                $saved_count++;
            }
        }

        // Save the settings array
        update_option('pdf_builder_settings', $settings);
        error_log('[PDF Builder AJAX] Saved ' . count($settings) . ' settings to pdf_builder_settings option');

        return $saved_count;
    }

    /**
     * Sauvegarde tous les paramètres depuis les données JSON
     */
    private function save_all_settings_from_json($form_data_json) {
        $form_data = json_decode($form_data_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Données JSON invalides: ' . json_last_error_msg());
        }

        error_log('[PDF Builder AJAX] Données JSON décodées, ' . count($form_data) . ' formulaires à traiter');

        $saved_count = 0;

        // Traiter chaque formulaire
        foreach ($form_data as $form_id => $form_fields) {
            if (!is_array($form_fields)) {
                continue;
            }

            error_log('[PDF Builder AJAX] Traitement formulaire: ' . $form_id . ' avec ' . count($form_fields) . ' champs');

            foreach ($form_fields as $field_name => $field_value) {
                // Nettoyer et valider les données selon le type de champ
                $clean_value = $this->sanitize_field_value($field_name, $field_value);

                // Déterminer le nom de l'option WordPress selon le formulaire
                $option_name = $this->get_option_name_for_field($form_id, $field_name);

                if ($option_name) {
                    update_option($option_name, $clean_value);
                    $saved_count++;
                    error_log('[PDF Builder AJAX] Sauvegardé: ' . $option_name . ' = ' . substr($clean_value, 0, 50));
                } else {
                    error_log('[PDF Builder AJAX] Option name vide pour ' . $form_id . '.' . $field_name);
                }
            }
        }

        return $saved_count;
    }

    /**
     * Nettoie la valeur d'un champ selon son type
     */
    private function sanitize_field_value($field_name, $value) {
        // Gérer les tableaux (cases à cocher multiples)
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }

        // Gérer les champs booléens
        if (strpos($field_name, 'enabled') !== false ||
            strpos($field_name, 'auto') !== false ||
            strpos($field_name, 'test_mode') !== false ||
            strpos($field_name, 'compression') !== false ||
            strpos($field_name, 'monitoring') !== false ||
            strpos($field_name, 'reporting') !== false) {
            return !empty($value) ? '1' : '0';
        }

        // Gérer les champs numériques
        if (strpos($field_name, 'ttl') !== false ||
            strpos($field_name, 'size') !== false ||
            strpos($field_name, 'retention') !== false ||
            strpos($field_name, 'capital') !== false) {
            return intval($value);
        }

        // Par défaut, nettoyer comme texte
        return sanitize_text_field($value);
    }

    /**
     * Détermine le nom de l'option WordPress pour un champ
     */
    private function get_option_name_for_field($form_id, $field_name) {
        // Mapping des formulaires vers les préfixes d'options
        $form_mappings = [
            'developpeur-form' => 'pdf_builder_dev_',
            'canvas-form' => 'pdf_builder_canvas_',
            'securite-settings-form' => 'pdf_builder_security_',
            'pdf-settings-form' => 'pdf_builder_pdf_',
            'templates-status-form' => 'pdf_builder_templates_',
            'general-form' => 'pdf_builder_',
            'licence-container' => 'pdf_builder_license_',
            'cache-status-form' => 'pdf_builder_cache_',
            'canvas-dimensions-form' => 'pdf_builder_canvas_dimensions_',
            'zoom-form' => 'pdf_builder_zoom_',
            'canvas-apparence-form' => 'pdf_builder_canvas_appearance_',
            'canvas-grille-form' => 'pdf_builder_canvas_grid_',
            'canvas-interactions-form' => 'pdf_builder_canvas_interactions_',
            'canvas-export-form' => 'pdf_builder_canvas_export_',
            'canvas-performance-form' => 'pdf_builder_canvas_performance_',
            'canvas-debug-form' => 'pdf_builder_canvas_debug_'
        ];

        $prefix = $form_mappings[$form_id] ?? 'pdf_builder_';

        // Certains champs ont des noms d'options spécifiques
        $special_mappings = [
            'company_phone_manual' => 'pdf_builder_company_phone_manual',
            'company_siret' => 'pdf_builder_company_siret',
            'company_vat' => 'pdf_builder_company_vat',
            'company_rcs' => 'pdf_builder_company_rcs',
            'company_capital' => 'pdf_builder_company_capital',
            'license_email_reminders' => 'pdf_builder_license_email_reminders',
            'license_reminder_email' => 'pdf_builder_license_reminder_email',
            'license_test_mode' => 'pdf_builder_license_test_mode_enabled',
            'license_test_mode_enabled' => 'pdf_builder_license_test_mode_enabled',
            'license_key' => 'pdf_builder_license_key',
            'license_test_key' => 'pdf_builder_license_test_key',
            'license_test_mode' => 'pdf_builder_license_test_mode',
            'auto_maintenance' => 'pdf_builder_auto_maintenance',
            'auto_backup' => 'pdf_builder_auto_backup',
            'auto_backup_frequency' => 'pdf_builder_auto_backup_frequency',
            'backup_retention' => 'pdf_builder_backup_retention'
        ];

        return $special_mappings[$field_name] ?? $prefix . $field_name;
    }

    /**
     * Sauvegarde des paramètres généraux
     */
    private function save_general_settings() {
        $settings = [
            'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
            'cache_ttl' => intval($_POST['cache_ttl']),
            'cache_compression' => isset($_POST['cache_compression']) ? '1' : '0',
            'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']) ? '1' : '0',
            'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
            'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
            'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
            'company_vat' => sanitize_text_field($_POST['company_vat'] ?? ''),
            'company_rcs' => sanitize_text_field($_POST['company_rcs'] ?? ''),
            'company_capital' => sanitize_text_field($_POST['company_capital'] ?? ''),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
        ];

        foreach ($settings as $key => $value) {
            $option_name = 'pdf_builder_' . $key;
            update_option($option_name, $value);
            error_log("PDF Builder: Saved general setting - {$option_name} = '{$value}'");
        }

        error_log("PDF Builder: General settings saved - " . count($settings) . " fields");
        return count($settings);
    }

    /**
     * Sauvegarde des paramètres performance
     */
    private function save_performance_settings() {
        $settings = [
            'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
            'cache_expiry' => intval($_POST['cache_expiry']),
            'compression_enabled' => isset($_POST['compression_enabled']) ? '1' : '0',
            'lazy_loading' => isset($_POST['lazy_loading']) ? '1' : '0',
            'preload_resources' => isset($_POST['preload_resources']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres système
     */
    private function save_system_settings() {
        $settings = [
            'cache_enabled' => $_POST['cache_enabled'] ?? '0',
            'cache_compression' => $_POST['cache_compression'] ?? '0',
            'cache_auto_cleanup' => $_POST['cache_auto_cleanup'] ?? '0',
            'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'performance_auto_optimization' => isset($_POST['performance_auto_optimization']) ? '1' : '0',
            'auto_maintenance' => $_POST['systeme_auto_maintenance'] ?? '0',
            'auto_backup' => $_POST['systeme_auto_backup'] ?? '0',
            'auto_backup_frequency' => sanitize_text_field($_POST['systeme_auto_backup_frequency'] ?? $_POST['systeme_auto_backup_frequency_hidden'] ?? 'daily'),
            'backup_retention' => intval($_POST['systeme_backup_retention'] ?? 30),
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        // Reprogrammer la sauvegarde automatique si la fréquence a changé
        if (isset($settings['auto_backup_frequency'])) {
            if (class_exists('PDF_Builder_Task_Scheduler')) {
                PDF_Builder_Task_Scheduler::get_instance()->reschedule_auto_backup($settings['auto_backup_frequency']);
            }
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres maintenance
     */
    private function save_maintenance_settings() {
        $settings = [
            'auto_cleanup' => isset($_POST['auto_cleanup']) ? '1' : '0',
            'cleanup_interval' => sanitize_text_field($_POST['cleanup_interval']),
            'log_retention' => intval($_POST['log_retention']),
            'backup_enabled' => isset($_POST['backup_enabled']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres de sauvegarde
     */
    private function save_backup_settings() {
        $settings = [
            'auto_backup' => isset($_POST['auto_backup']) ? '1' : '0',
            'backup_frequency' => sanitize_text_field($_POST['backup_frequency']),
            'backup_retention' => intval($_POST['backup_retention']),
            'cloud_backup' => isset($_POST['cloud_backup']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres d'accès
     */
    private function save_access_settings() {
        $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) ? $_POST['pdf_builder_allowed_roles'] : array();
        update_option('pdf_builder_allowed_roles', $allowed_roles);
        return 1;
    }

    /**
     * Sauvegarde des paramètres sécurité
     */
    private function save_security_settings() {
        $settings = [
            'security_level' => sanitize_text_field($_POST['security_level'] ?? 'medium'),
            'enable_logging' => isset($_POST['enable_logging']) ? '1' : '0',
            'ip_filtering' => isset($_POST['ip_filtering']) ? '1' : '0',
            'rate_limiting' => isset($_POST['rate_limiting']) ? '1' : '0',
            'encryption' => isset($_POST['encryption']) ? '1' : '0',
            'audit_log' => isset($_POST['audit_log']) ? '1' : '0',
            'gdpr_enabled' => isset($_POST['gdpr_enabled']) ? '1' : '0',
            'gdpr_consent_required' => isset($_POST['gdpr_consent_required']) ? '1' : '0',
            'gdpr_data_retention' => intval($_POST['gdpr_data_retention']),
            'gdpr_audit_enabled' => isset($_POST['gdpr_audit_enabled']) ? '1' : '0',
            'gdpr_encryption_enabled' => isset($_POST['gdpr_encryption_enabled']) ? '1' : '0',
            'gdpr_consent_analytics' => isset($_POST['gdpr_consent_analytics']) ? '1' : '0',
            'gdpr_consent_templates' => isset($_POST['gdpr_consent_templates']) ? '1' : '0',
            'gdpr_consent_marketing' => isset($_POST['gdpr_consent_marketing']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres PDF
     */
    private function save_pdf_settings() {
        $settings = [
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'pdf_page_size' => sanitize_text_field($_POST['pdf_page_size'] ?? 'A4'),
            'pdf_orientation' => sanitize_text_field($_POST['pdf_orientation'] ?? 'portrait'),
            'pdf_cache_enabled' => isset($_POST['pdf_cache_enabled']) ? '1' : '0',
            'pdf_compression' => sanitize_text_field($_POST['pdf_compression'] ?? 'medium'),
            'pdf_metadata_enabled' => isset($_POST['pdf_metadata_enabled']) ? '1' : '0',
            'pdf_print_optimized' => isset($_POST['pdf_print_optimized']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres contenu
     */
    private function save_content_settings() {
        $settings = [
            'canvas_max_size' => intval($_POST['canvas_max_size']),
            'canvas_dpi' => intval($_POST['canvas_dpi']),
            'canvas_format' => sanitize_text_field($_POST['canvas_format']),
            'canvas_quality' => intval($_POST['canvas_quality']),
            'template_library_enabled' => isset($_POST['template_library_enabled']) ? '1' : '0',
            'default_template' => sanitize_text_field($_POST['default_template'] ?? 'blank'),
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres développeur
     */
    private function save_developer_settings() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Starting save_developer_settings');
            error_log('PDF Builder: POST data keys: ' . implode(', ', array_keys($_POST)));
        }

        $settings = [
            'pdf_builder_developer_enabled' => isset($_POST['pdf_builder_developer_enabled']) ? '1' : '0',
            'pdf_builder_developer_password' => sanitize_text_field($_POST['pdf_builder_developer_password'] ?? ''),
            'pdf_builder_license_test_mode_enabled' => isset($_POST['license_test_mode']) ? '1' : '0',
            'pdf_builder_license_test_key' => sanitize_text_field($_POST['pdf_builder_license_test_key'] ?? ''),
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Settings to save: ' . json_encode($settings));
        }

        foreach ($settings as $key => $value) {
            update_option($key, $value);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PDF Builder: Saved option ' . $key . ' = ' . $value);
            }
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres licence
     */
    private function save_license_settings() {
        // Notifications removed from the license settings — ensure any old option is deleted
        delete_option('pdf_builder_license_enable_notifications');

        // Paramètres de rappel par email
        $settings = [
            'license_email_reminders' => !empty($_POST['license_email_reminders']) ? '1' : '0',
            'license_reminder_email' => sanitize_email($_POST['license_reminder_email'] ?? ''),
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres templates
     */
    private function save_templates_settings() {
        $order_status_templates = isset($_POST['order_status_templates']) ? $_POST['order_status_templates'] : [];
        update_option('pdf_builder_order_status_templates', $order_status_templates);
        return 1;
    }

    /**
     * Handler pour les métriques de cache
     */
    public function handle_get_cache_metrics() {
        if (!$this->nonce_manager->validate_ajax_request('get_cache_metrics')) {
            return;
        }

        // Implémentation simplifiée - à étendre selon les besoins
        wp_send_json_success([
            'message' => 'Métriques de cache récupérées',
            'cache_status' => 'OK'
        ]);
    }

    /**
     * Handler pour tester l'intégration du cache
     */
    public function handle_test_cache_integration() {
        if (!$this->nonce_manager->validate_ajax_request('test_cache_integration')) {
            return;
        }

        try {
            // Test 1: Vérifier si le cache WordPress fonctionne
            $test_key = 'pdf_builder_cache_test_' . time();
            $test_value = 'test_value_' . rand(1000, 9999);

            wp_cache_set($test_key, $test_value, 'pdf_builder', 300);
            $retrieved_value = wp_cache_get($test_key, 'pdf_builder');

            $cache_wp_ok = ($retrieved_value === $test_value);

            // Nettoyer le test
            wp_cache_delete($test_key, 'pdf_builder');

            // Test 2: Vérifier les transients
            $transient_key = 'pdf_builder_test_transient';
            $transient_value = 'transient_test_' . rand(1000, 9999);

            set_transient($transient_key, $transient_value, 300);
            $transient_retrieved = get_transient($transient_key);

            $transient_ok = ($transient_retrieved === $transient_value);

            // Nettoyer
            delete_transient($transient_key);

            // Test 3: Vérifier les options
            $option_test = get_option('pdf_builder_settings', []);
            $options_ok = is_array($option_test);

            // Résultats
            $results = [
                'cache_wordpress' => $cache_wp_ok ? '✅ Fonctionnel' : '❌ Défaillant',
                'transients' => $transient_ok ? '✅ Fonctionnel' : '❌ Défaillant',
                'options' => $options_ok ? '✅ Fonctionnel' : '❌ Défaillant'
            ];

            $all_ok = $cache_wp_ok && $transient_ok && $options_ok;

            wp_send_json_success([
                'message' => $all_ok ? '✅ Test d\'intégration du cache réussi' : '⚠️ Certains tests ont échoué',
                'results' => $results,
                'status' => $all_ok ? 'success' : 'warning'
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => '❌ Erreur lors du test du cache: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handler pour vider le cache
     */
    public function handle_clear_cache() {
        if (!$this->nonce_manager->validate_ajax_request('clear_cache')) {
            return;
        }

        wp_cache_flush();
        delete_transient('pdf_builder_cache');

        wp_send_json_success(['message' => 'Cache vidé avec succès']);
    }

    /**
     * Handler pour l'optimisation de la base de données
     */
    public function handle_optimize_database() {
        if (!$this->nonce_manager->validate_ajax_request('optimize_database')) {
            return;
        }

        try {
            global $wpdb;

            // Obtenir la taille de la base avant optimisation
            $size_before = $this->get_database_size();

            // Optimiser toutes les tables du plugin
            $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder%'", ARRAY_N);
            $optimized_tables = 0;
            $errors = [];

            foreach ($tables as $table) {
                $table_name = $table[0];
                $result = $wpdb->query("OPTIMIZE TABLE `$table_name`");

                if ($result === false) {
                    $errors[] = "Erreur sur la table $table_name: " . $wpdb->last_error;
                } else {
                    $optimized_tables++;
                }
            }

            // Obtenir la taille après optimisation
            $size_after = $this->get_database_size();

            $message = "✅ Base de données optimisée avec succès\n";
            $message .= "• Tables optimisées: $optimized_tables\n";
            $message .= "• Taille avant: {$size_before} MB\n";
            $message .= "• Taille après: {$size_after} MB";

            if (!empty($errors)) {
                $message .= "\n⚠️ Erreurs rencontrées:\n" . implode("\n", $errors);
            }

            // Mettre à jour la date de dernière maintenance
            $current_time = current_time('mysql');
            $settings = get_option('pdf_builder_settings', []);
            $settings['pdf_builder_last_maintenance'] = $current_time;
            update_option('pdf_builder_settings', $settings);

            wp_send_json_success(['message' => $message]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => '❌ Erreur lors de l\'optimisation: ' . $e->getMessage()]);
        }
    }

    /**
     * Handler pour supprimer les fichiers temporaires
     */
    public function handle_remove_temp_files() {
        if (!$this->nonce_manager->validate_ajax_request('remove_temp_files')) {
            return;
        }

        try {
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
            $deleted_files = 0;
            $deleted_size = 0;

            // Supprimer les fichiers temporaires du plugin
            if (is_dir($temp_dir)) {
                $files = glob($temp_dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $file_age = time() - filemtime($file);
                        // Supprimer les fichiers de plus de 24 heures
                        if ($file_age > 86400) {
                            $file_size = filesize($file);
                            if (unlink($file)) {
                                $deleted_files++;
                                $deleted_size += $file_size;
                            }
                        }
                    }
                }
            }

            // Nettoyer les transients temporaires du plugin
            global $wpdb;
            $transient_count = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = '1'",
                    '_transient_pdf_builder_temp_%'
                )
            );

            $message = "✅ Fichiers temporaires nettoyés\n";
            $message .= "• Fichiers supprimés: $deleted_files\n";
            $message .= "• Espace libéré: " . number_format($deleted_size / 1024, 1) . " KB\n";
            $message .= "• Transients nettoyés: " . intval($transient_count);

            // Mettre à jour la date de dernière maintenance
            $current_time = current_time('mysql');
            $settings = get_option('pdf_builder_settings', []);
            $settings['pdf_builder_last_maintenance'] = $current_time;
            update_option('pdf_builder_settings', $settings);

            wp_send_json_success(['message' => $message]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => '❌ Erreur lors du nettoyage: ' . $e->getMessage()]);
        }
    }

    /**
     * Handler pour réparer les templates
     */
    public function handle_repair_templates() {
        if (!$this->nonce_manager->validate_ajax_request('repair_templates')) {
            return;
        }

        try {
            $repaired_templates = 0;
            $errors = [];

            // Vérifier et réparer les templates par défaut
            $default_templates = [
                'invoice' => 'Template Facture',
                'quote' => 'Template Devis',
                'receipt' => 'Template Reçu',
                'blank' => 'Template Vierge'
            ];

            foreach ($default_templates as $template_id => $template_name) {
                $template_option = get_option("pdf_builder_template_{$template_id}", '');

                if (empty($template_option)) {
                    // Template manquant, le recréer avec des valeurs par défaut
                    $default_content = $this->get_default_template_content($template_id);
                    update_option("pdf_builder_template_{$template_id}", $default_content);
                    $repaired_templates++;
                }
            }

            // Vérifier l'intégrité des templates existants
            $all_templates = get_option('pdf_builder_templates', []);
            if (!is_array($all_templates)) {
                update_option('pdf_builder_templates', []);
                $errors[] = "Liste des templates corrompue, réinitialisée";
            }

            $message = "✅ Templates vérifiés et réparés\n";
            $message .= "• Templates réparés: $repaired_templates\n";

            if (!empty($errors)) {
                $message .= "⚠️ Problèmes détectés:\n" . implode("\n", $errors);
            } else {
                $message .= "• Aucun problème détecté";
            }

            // Mettre à jour la date de dernière maintenance
            $current_time = current_time('mysql');
            $settings = get_option('pdf_builder_settings', []);
            $settings['pdf_builder_last_maintenance'] = $current_time;
            update_option('pdf_builder_settings', $settings);

            wp_send_json_success(['message' => $message]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => '❌ Erreur lors de la réparation: ' . $e->getMessage()]);
        }
    }

    /**
     * Handler pour basculer la maintenance automatique
     */
    public function handle_toggle_auto_maintenance() {
        if (!$this->nonce_manager->validate_ajax_request('toggle_auto_maintenance')) {
            return;
        }

        try {
            $current_state = get_option('pdf_builder_auto_maintenance', '1');
            $new_state = $current_state === '1' ? '0' : '1';

            // Mettre à jour dans le tableau unifié des paramètres
            $settings = get_option('pdf_builder_settings', []);
            $settings['pdf_builder_systeme_auto_maintenance'] = $new_state;
            update_option('pdf_builder_settings', $settings);

            // Garder aussi l'option individuelle pour compatibilité
            update_option('pdf_builder_auto_maintenance', $new_state);

            $message = $new_state === '1' ? '✅ Maintenance automatique activée' : '❌ Maintenance automatique désactivée';

            wp_send_json_success(['message' => $message]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => '❌ Erreur lors du basculement: ' . $e->getMessage()]);
        }
    }

    /**
     * Handler pour programmer la prochaine maintenance
     */
    public function handle_schedule_maintenance() {
        if (!$this->nonce_manager->validate_ajax_request('schedule_maintenance')) {
            return;
        }

        try {
            // Programmer la prochaine maintenance pour dimanche prochain à 02:00
            $next_sunday = strtotime('next Sunday 02:00');
            if ($next_sunday < time()) {
                $next_sunday = strtotime('next Sunday 02:00', strtotime('+1 week'));
            }

            update_option('pdf_builder_next_maintenance', $next_sunday);

            // Mettre à jour dans le tableau unifié des paramètres
            $settings = get_option('pdf_builder_settings', []);
            $settings['pdf_builder_next_maintenance'] = date('Y-m-d H:i:s', $next_sunday);
            update_option('pdf_builder_settings', $settings);

            $message = '📅 Prochaine maintenance programmée pour le ' . date('d/m/Y à H:i', $next_sunday);
            $formatted_date = date('d/m/Y à H:i', $next_sunday);

            wp_send_json_success([
                'message' => $message,
                'next_maintenance' => $formatted_date,
                'timestamp' => $next_sunday
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => '❌ Erreur lors de la programmation: ' . $e->getMessage()]);
        }
    }

    /**
     * Handler pour tester la licence
     */
    public function handle_test_license() {
        if (!$this->nonce_manager->validate_ajax_request('test_license')) {
            return;
        }

        wp_send_json_error(['message' => 'Handler not implemented']);
    }

    /**
     * Handler pour exporter le diagnostic
     */
    public function handle_export_diagnostic() {
        if (!$this->nonce_manager->validate_ajax_request('export_diagnostic')) {
            return;
        }

        wp_send_json_error(['message' => 'Handler not implemented']);
    }

    /**
     * Handler pour voir les logs
     */
    public function handle_view_logs() {
        if (!$this->nonce_manager->validate_ajax_request('view_logs')) {
            return;
        }

        wp_send_json_error(['message' => 'Handler not implemented']);
    }

    /**
     * Handler de test AJAX
     */
    public function handle_test_ajax() {
        if (!$this->nonce_manager->validate_ajax_request('test_ajax')) {
            return;
        }

        wp_send_json_success([
            'message' => 'AJAX connection successful',
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ]);
    }

    /**
     * Handler pour créer une sauvegarde
     */
    public function handle_create_backup() {
        // Debug: Log que le handler est appelé
        error_log('[PDF Builder] handle_create_backup called');

        if (!$this->nonce_manager->validate_ajax_request()) {
            error_log('[PDF Builder] Nonce validation failed for create_backup');
            return;
        }

        if (!current_user_can('manage_options')) {
            error_log('[PDF Builder] User does not have manage_options capability');
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            error_log('[PDF Builder] Creating backup manager instance');
            $backup_manager = \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();

            $options = [
                'compress' => isset($_POST['compress']) && $_POST['compress'] === '1',
                'exclude_templates' => isset($_POST['exclude_templates']) && $_POST['exclude_templates'] === '1',
                'exclude_settings' => isset($_POST['exclude_settings']) && $_POST['exclude_settings'] === '1',
                'exclude_user_data' => isset($_POST['exclude_user_data']) && $_POST['exclude_user_data'] === '1'
            ];

            error_log('[PDF Builder] Calling createBackup with options: ' . json_encode($options));
            $result = $backup_manager->createBackup($options);
            error_log('[PDF Builder] createBackup result: ' . json_encode($result));

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'filename' => $result['filename'],
                    'size_human' => size_format($result['size'])
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur création sauvegarde: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Handler pour lister les sauvegardes
     */
    public function handle_list_backups() {
        if (!$this->nonce_manager->validate_ajax_request()) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            $backup_manager = \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();
            $backups = $backup_manager->listBackups();

            wp_send_json_success(['backups' => $backups]);

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur listage sauvegardes: ' . $e->getMessage());
            wp_send_json_error(['message' => __('Erreur lors du chargement des sauvegardes.', 'pdf-builder-pro')]);
        }
    }

    /**
     * Handler pour restaurer une sauvegarde
     */
    public function handle_restore_backup() {
        if (!$this->nonce_manager->validate_ajax_request()) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $filename = $_POST['filename'] ?? '';

        if (empty($filename)) {
            wp_send_json_error(['message' => __('Nom de fichier manquant.', 'pdf-builder-pro')]);
            return;
        }

        try {
            $backup_manager = \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();

            $options = [
                'overwrite' => isset($_POST['overwrite']) && $_POST['overwrite'] === '1',
                'exclude_templates' => isset($_POST['exclude_templates']) && $_POST['exclude_templates'] === '1',
                'exclude_settings' => isset($_POST['exclude_settings']) && $_POST['exclude_settings'] === '1',
                'exclude_user_data' => isset($_POST['exclude_user_data']) && $_POST['exclude_user_data'] === '1'
            ];

            $result = $backup_manager->restoreBackup($filename, $options);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'results' => $result['results']
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur restauration sauvegarde: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Handler pour supprimer une sauvegarde
     */
    public function handle_delete_backup() {
        if (!$this->nonce_manager->validate_ajax_request()) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $filename = $_POST['filename'] ?? '';

        if (empty($filename)) {
            wp_send_json_error(['message' => __('Nom de fichier manquant.', 'pdf-builder-pro')]);
            return;
        }

        try {
            $backup_manager = \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();
            $result = $backup_manager->deleteBackup($filename);

            if ($result['success']) {
                wp_send_json_success(['message' => $result['message']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur suppression sauvegarde: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Handler pour télécharger une sauvegarde
     */
     public function handle_download_backup() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         if (!current_user_can('manage_options')) {
             wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
             return;
         }

         $filename = $_POST['filename'] ?? '';

         if (empty($filename)) {
             wp_send_json_error(['message' => __('Nom de fichier manquant.', 'pdf-builder-pro')]);
             return;
         }

         try {
             $filepath = WP_CONTENT_DIR . '/pdf-builder-backups/' . $filename;

             if (!file_exists($filepath)) {
                 wp_send_json_error(['message' => __('Fichier de sauvegarde introuvable.', 'pdf-builder-pro')]);
                 return;
             }

             // Forcer le téléchargement du fichier
             header('Content-Type: application/octet-stream');
             header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
             header('Content-Length: ' . filesize($filepath));
             header('Cache-Control: no-cache, no-store, must-revalidate');
             header('Pragma: no-cache');
             header('Expires: 0');

             readfile($filepath);
             exit;

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur téléchargement sauvegarde: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour basculer le mode test de licence
      */
     public function handle_toggle_test_mode() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
             $current_mode = get_option('pdf_builder_license_test_mode_enabled', '0');
             $new_mode = $current_mode === '1' ? '0' : '1';

             update_option('pdf_builder_license_test_mode_enabled', $new_mode);

             wp_send_json_success([
                 'message' => 'Mode test ' . ($new_mode === '1' ? 'activé' : 'désactivé') . ' avec succès.',
                 'test_mode' => $new_mode
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur toggle test mode: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour générer une clé de licence de test
      */
     public function handle_generate_test_license_key() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
            $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(wp_rand(), true)), 0, 16));
            update_option('pdf_builder_license_test_key', $test_key);
            // set an expiry date (30 days)
            $expires_in_30_days = date('Y-m-d', strtotime('+30 days'));
            update_option('pdf_builder_license_test_key_expires', $expires_in_30_days);

            wp_send_json_success([
                'message' => 'Clé de test générée avec succès.',
                'license_key' => $test_key,
                'expires' => $expires_in_30_days
            ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur génération clé test: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour supprimer la clé de licence de test
      */
     public function handle_delete_test_license_key() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
            delete_option('pdf_builder_license_test_key');
            delete_option('pdf_builder_license_test_key_expires');
            update_option('pdf_builder_license_test_mode_enabled', '0');

             wp_send_json_success([
                 'message' => 'Clé de test supprimée avec succès.'
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur suppression clé test: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour nettoyer complètement la licence
      */
     public function handle_cleanup_license() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
             $license_options = [
                 'pdf_builder_license_key',
                 'pdf_builder_license_status',
                 'pdf_builder_license_expires',
                 'pdf_builder_license_test_key',
                 'pdf_builder_license_test_mode_enabled',
                 'pdf_builder_license_data'
             ];

             foreach ($license_options as $option) {
                 delete_option($option);
             }

             // Clear license transients
             global $wpdb;
             $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_license_%'");
             $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_license_%'");

             wp_send_json_success([
                 'message' => 'Licence complètement nettoyée. Le plugin est maintenant en mode gratuit.',
                 'reset_complete' => true
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur nettoyage licence: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour nettoyer les fichiers temporaires
      */
     public function handle_clear_temp_files() {
         if (!$this->nonce_manager->validate_ajax_request()) {
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

             // Clear old temp files (older than 24 hours)
             $upload_dir = wp_upload_dir();
             $temp_pattern = $upload_dir['basedir'] . '/pdf-builder-temp-*';
             $temp_files = glob($temp_pattern);

             foreach ($temp_files as $temp_file) {
                 if (is_file($temp_file) && (time() - filemtime($temp_file)) > 86400) {
                     $file_size = filesize($temp_file);
                     if (unlink($temp_file)) {
                         $cleared_files++;
                         $total_size += $file_size;
                     }
                 }
             }

             wp_send_json_success([
                 'message' => "Fichiers temporaires nettoyés: $cleared_files fichier(s) supprimé(s), " . size_format($total_size) . ' libéré(s).'
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur nettoyage temp: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour tester les routes
      */
     public function handle_test_routes() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         $routes_tested = [];
         $failed_routes = [];

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
             wp_send_json_success([
                 'message' => 'Toutes les routes sont accessibles.',
                 'routes_tested' => $routes_tested
             ]);
         } else {
             wp_send_json_error([
                 'message' => 'Routes inaccessibles détectées.',
                 'routes_tested' => $routes_tested,
                 'failed_routes' => $failed_routes
             ]);
         }
     }

     /**
      * Handler pour actualiser les logs
      */
     public function handle_refresh_logs() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
             $log_files = [];
             $log_dirs = [
                 WP_CONTENT_DIR . '/pdf-builder-logs/',
                 wp_upload_dir()['basedir'] . '/pdf-builder-logs/'
             ];

             $logs_content = '';
             $max_lines = 100;

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

             wp_send_json_success([
                 'message' => 'Logs actualisés avec succès.',
                 'logs_content' => $logs_content
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur actualisation logs: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour vider les logs
      */
     public function handle_clear_logs() {
         if (!$this->nonce_manager->validate_ajax_request()) {
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

             wp_send_json_success([
                 'message' => "$cleared_files fichier(s) de log supprimé(s) avec succès."
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur nettoyage logs: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour obtenir un nonce frais
      */
     public function handle_get_fresh_nonce() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
             $fresh_nonce = $this->nonce_manager->generate_nonce();

             wp_send_json_success([
                 'nonce' => $fresh_nonce
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur génération nonce: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour afficher les informations système
      */
     public function handle_system_info() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
             global $wpdb, $wp_version;

             $system_info = [
                 'wordpress' => [
                     'version' => $wp_version,
                     'site_url' => get_site_url(),
                     'admin_email' => get_option('admin_email'),
                     'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
                     'multisite' => is_multisite()
                 ],
                 'server' => [
                     'php_version' => PHP_VERSION,
                     'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                     'memory_limit' => ini_get('memory_limit'),
                     'max_execution_time' => ini_get('max_execution_time'),
                     'upload_max_filesize' => ini_get('upload_max_filesize')
                 ],
                 'database' => [
                     'version' => $wpdb->db_version(),
                     'size' => $this->get_database_size(),
                     'tables_count' => count($wpdb->get_results("SHOW TABLES"))
                 ],
                 'plugin' => [
                     'version' => get_option('pdf_builder_version', 'Unknown'),
                     'cache_enabled' => get_option('pdf_builder_cache_enabled', '0') === '1',
                     'developer_mode' => get_option('pdf_builder_developer_enabled', '0') === '1',
                     'license_status' => get_option('pdf_builder_license_status', 'inactive')
                 ]
             ];

             wp_send_json_success([
                 'message' => 'Informations système récupérées avec succès.',
                 'system_info' => $system_info
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur récupération info système: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour remettre à zéro les paramètres développeur
      */
     public function handle_reset_dev_settings() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             return;
         }

         try {
             $dev_options = [
                 'pdf_builder_developer_enabled',
                 'pdf_builder_developer_password',
                 'pdf_builder_performance_monitoring',
                 'pdf_builder_license_test_mode_enabled',
                 'pdf_builder_license_test_key',
                 'pdf_builder_license_test_key_expires'
             ];

             $reset_count = 0;
             foreach ($dev_options as $option) {
                 if (delete_option($option)) {
                     $reset_count++;
                 }
             }

             wp_send_json_success([
                 'message' => "$reset_count paramètre(s) développeur remis à zéro avec succès."
             ]);

         } catch (Exception $e) {
             error_log('[PDF Builder AJAX] Erreur reset paramètres dev: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Obtenir la taille de la base de données
      */
     private function get_database_size() {
         global $wpdb;

         $result = $wpdb->get_row("
             SELECT
                 ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
         ");

         return $result ? $result->size_mb . ' MB' : 'Unknown';
     }

    /**
     * Retourne le contenu par défaut d'un template
     */
    private function get_default_template_content($template_id) {
        $templates = [
            'invoice' => '<h1>Facture</h1><p>Template de facture par défaut</p>',
            'quote' => '<h1>Devis</h1><p>Template de devis par défaut</p>',
            'receipt' => '<h1>Reçu</h1><p>Template de reçu par défaut</p>',
            'blank' => '<div style="text-align: center; padding: 50px;"><h1>Template Vierge</h1><p>Commencez à créer votre PDF ici</p></div>'
        ];

        return $templates[$template_id] ?? '<h1>Template</h1><p>Contenu par défaut</p>';
     }
}// Initialiser le handler unifié
PDF_Builder_Unified_Ajax_Handler::get_instance();