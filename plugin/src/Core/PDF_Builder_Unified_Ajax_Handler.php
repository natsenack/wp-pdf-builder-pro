<?php
/**
 * PDF Builder Pro - Handler AJAX unifié
 * Point d'entrée unique pour toutes les actions AJAX avec gestion centralisée des nonces
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
        add_action('wp_ajax_pdf_builder_clear_all_cache', [$this, 'handle_clear_cache']);

        // Actions de maintenance
        add_action('wp_ajax_pdf_builder_optimize_database', [$this, 'handle_optimize_database']);
        add_action('wp_ajax_pdf_builder_remove_temp_files', [$this, 'handle_remove_temp_files']);

        // Actions de sauvegarde
        add_action('wp_ajax_pdf_builder_create_backup', [$this, 'handle_create_backup']);
        add_action('wp_ajax_pdf_builder_list_backups', [$this, 'handle_list_backups']);
        add_action('wp_ajax_pdf_builder_restore_backup', [$this, 'handle_restore_backup']);
        add_action('wp_ajax_pdf_builder_delete_backup', [$this, 'handle_delete_backup']);
        add_action('wp_ajax_pdf_builder_download_backup', [$this, 'handle_download_backup']);

        // Actions de licence
        add_action('wp_ajax_pdf_builder_test_license', [$this, 'handle_test_license']);

        // Actions de diagnostic
        add_action('wp_ajax_pdf_builder_export_diagnostic', [$this, 'handle_export_diagnostic']);
        add_action('wp_ajax_pdf_builder_view_logs', [$this, 'handle_view_logs']);

        // Actions de test
        add_action('wp_ajax_pdf_builder_test_ajax', [$this, 'handle_test_ajax']);
        add_action('wp_ajax_test_ajax', [$this, 'handle_test_ajax']);
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

            // Traiter selon l'onglet
            switch ($current_tab) {
                case 'all':
                    $saved_count = $this->save_all_settings();
                    break;
                case 'general':
                    $saved_count = $this->save_general_settings();
                    break;
                case 'performance':
                    $saved_count = $this->save_performance_settings();
                    break;
                case 'systeme':
                    $saved_count = $this->save_system_settings();
                    break;
                case 'maintenance':
                    $saved_count = $this->save_maintenance_settings();
                    break;
                case 'sauvegarde':
                    $saved_count = $this->save_backup_settings();
                    break;
                case 'acces':
                    $saved_count = $this->save_access_settings();
                    break;
                case 'securite':
                    $saved_count = $this->save_security_settings();
                    break;
                case 'pdf':
                    $saved_count = $this->save_pdf_settings();
                    break;
                case 'contenu':
                    $saved_count = $this->save_content_settings();
                    break;
                case 'developpeur':
                    $saved_count = $this->save_developer_settings();
                    break;
                case 'licence':
                    $saved_count = $this->save_license_settings();
                    break;
                case 'templates':
                    $saved_count = $this->save_templates_settings();
                    break;
                default:
                    wp_send_json_error(['message' => 'Onglet inconnu: ' . $current_tab]);
                    return;
            }

            if ($saved_count > 0) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'saved_count' => $saved_count,
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
     * Handler pour sauvegarder tous les paramètres
     */
    public function handle_save_all_settings() {
        if (!$this->nonce_manager->validate_ajax_request('save_all_settings')) {
            return;
        }

        try {
            $saved_count = $this->save_all_settings();

            wp_send_json_success([
                'message' => 'Tous les paramètres sauvegardés avec succès',
                'saved_count' => $saved_count,
                'new_nonce' => $this->nonce_manager->generate_nonce()
            ]);

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur sauvegarde tous: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Sauvegarde tous les paramètres
     */
    private function save_all_settings() {

        $settings = [
            // Général - Informations entreprise
            'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
            'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
            'company_vat' => sanitize_text_field($_POST['company_vat'] ?? ''),
            'company_rcs' => sanitize_text_field($_POST['company_rcs'] ?? ''),
            'company_capital' => sanitize_text_field($_POST['company_capital'] ?? ''),

            // Licence
            'license_test_mode' => isset($_POST['license_test_mode']) ? '1' : '0',

            // Système - Cache
            'cache_enabled' => !empty($_POST['cache_enabled']) ? '1' : '0',
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'cache_compression' => !empty($_POST['cache_compression']) ? '1' : '0',
            'cache_auto_cleanup' => !empty($_POST['cache_auto_cleanup']) ? '1' : '0',
            'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),

            // Système - Maintenance
            'auto_maintenance' => !empty($_POST['systeme_auto_maintenance']) ? '1' : '0',

            // Système - Sauvegarde
            'auto_backup' => !empty($_POST['systeme_auto_backup']) ? '1' : '0',
            'auto_backup_frequency' => sanitize_text_field($_POST['systeme_auto_backup_frequency'] ?? 'daily'),
            'backup_retention' => intval($_POST['systeme_backup_retention'] ?? 30),

            // Accès - Rôles autorisés
            'allowed_roles' => isset($_POST['pdf_builder_allowed_roles']) ? array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']) : ['administrator'],

            // Sécurité
            'security_level' => sanitize_text_field($_POST['security_level'] ?? 'medium'),
            'enable_logging' => !empty($_POST['enable_logging']) ? '1' : '0',

            // RGPD
            'gdpr_enabled' => !empty($_POST['gdpr_enabled']) ? '1' : '0',
            'gdpr_consent_required' => !empty($_POST['gdpr_consent_required']) ? '1' : '0',
            'gdpr_data_retention' => intval($_POST['gdpr_data_retention'] ?? 2555),
            'gdpr_audit_enabled' => !empty($_POST['gdpr_audit_enabled']) ? '1' : '0',
            'gdpr_encryption_enabled' => !empty($_POST['gdpr_encryption_enabled']) ? '1' : '0',
            'gdpr_consent_analytics' => !empty($_POST['gdpr_consent_analytics']) ? '1' : '0',
            'gdpr_consent_templates' => !empty($_POST['gdpr_consent_templates']) ? '1' : '0',
            'gdpr_consent_marketing' => !empty($_POST['gdpr_consent_marketing']) ? '1' : '0',

            // Développeur - Contrôle d'accès
            'developer_enabled' => isset($_POST['developer_enabled']) ? '1' : '0',
            'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),

            // Développeur - Debug
            'debug_php_errors' => !empty($_POST['debug_php_errors']) ? '1' : '0',
            'debug_javascript' => !empty($_POST['debug_javascript']) ? '1' : '0',
            'debug_javascript_verbose' => !empty($_POST['debug_javascript_verbose']) ? '1' : '0',
            'debug_ajax' => !empty($_POST['debug_ajax']) ? '1' : '0',
            'debug_performance' => !empty($_POST['debug_performance']) ? '1' : '0',
            'debug_database' => !empty($_POST['debug_database']) ? '1' : '0',

            // Développeur - Logs
            'log_level' => intval($_POST['log_level'] ?? 3),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),

            // Développeur - Optimisations
            'force_https' => !empty($_POST['force_https']) ? '1' : '0',
            'performance_monitoring' => !empty($_POST['performance_monitoring']) ? '1' : '0',

            // PDF - Paramètres
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            'pdf_cache_enabled' => !empty($_POST['pdf_cache_enabled']) ? '1' : '0',
            'pdf_compression' => sanitize_text_field($_POST['pdf_compression'] ?? 'medium'),
            'pdf_metadata_enabled' => !empty($_POST['pdf_metadata_enabled']) ? '1' : '0',
            'pdf_print_optimized' => !empty($_POST['pdf_print_optimized']) ? '1' : '0',

            // Contenu - Templates
            'template_library_enabled' => !empty($_POST['template_library_enabled']) ? '1' : '0',
            'default_template' => sanitize_text_field($_POST['default_template'] ?? 'blank'),

            // Templates par statut
            'order_status_templates' => isset($_POST['order_status_templates']) ? $_POST['order_status_templates'] : [],

            // Paramètres Canvas - ajoutés pour le bouton flottant
            'canvas_width' => intval($_POST['pdf_builder_canvas_canvas_width'] ?? $_POST['pdf_builder_canvas_width'] ?? 794),
            'canvas_height' => intval($_POST['pdf_builder_canvas_canvas_height'] ?? $_POST['pdf_builder_canvas_height'] ?? 1123),
            'canvas_format' => sanitize_text_field($_POST['pdf_builder_canvas_default_canvas_format'] ?? $_POST['pdf_builder_canvas_format'] ?? 'A4'),
            'canvas_orientation' => sanitize_text_field($_POST['pdf_builder_canvas_default_canvas_orientation'] ?? $_POST['pdf_builder_canvas_orientation'] ?? 'portrait'),
            'canvas_dpi' => intval($_POST['pdf_builder_canvas_default_canvas_dpi'] ?? $_POST['pdf_builder_canvas_dpi'] ?? 96),
            'canvas_bg_color' => sanitize_hex_color($_POST['pdf_builder_canvas_canvas_bg_color'] ?? $_POST['pdf_builder_canvas_bg_color'] ?? '#ffffff'),
            'canvas_border_color' => sanitize_hex_color($_POST['pdf_builder_canvas_canvas_border_color'] ?? $_POST['pdf_builder_canvas_border_color'] ?? '#cccccc'),
            'canvas_border_width' => intval($_POST['pdf_builder_canvas_canvas_border_width'] ?? $_POST['pdf_builder_canvas_border_width'] ?? 1),
            'canvas_shadow_enabled' => !empty($_POST['pdf_builder_canvas_canvas_shadow_enabled']) || !empty($_POST['pdf_builder_canvas_shadow_enabled']) ? '1' : '0',
            'canvas_container_bg_color' => sanitize_hex_color($_POST['pdf_builder_canvas_canvas_container_bg_color'] ?? $_POST['pdf_builder_canvas_container_bg_color'] ?? '#f8f9fa'),
            'canvas_zoom_min' => intval($_POST['pdf_builder_canvas_min_zoom'] ?? $_POST['pdf_builder_canvas_zoom_min'] ?? 10),
            'canvas_zoom_max' => intval($_POST['pdf_builder_canvas_max_zoom'] ?? $_POST['pdf_builder_canvas_zoom_max'] ?? 500),
            'canvas_zoom_default' => intval($_POST['pdf_builder_canvas_default_zoom'] ?? $_POST['pdf_builder_canvas_zoom_default'] ?? 100),
            'canvas_zoom_step' => intval($_POST['pdf_builder_canvas_zoom_step'] ?? $_POST['pdf_builder_canvas_zoom_step'] ?? 25),
            'canvas_grid_enabled' => !empty($_POST['pdf_builder_canvas_show_grid']) || !empty($_POST['pdf_builder_canvas_grid_enabled']) ? '1' : '0',
            'canvas_grid_size' => intval($_POST['pdf_builder_canvas_grid_size'] ?? $_POST['pdf_builder_canvas_grid_size'] ?? 20),
            'canvas_snap_to_grid' => !empty($_POST['pdf_builder_canvas_snap_to_grid']) || !empty($_POST['pdf_builder_canvas_snap_to_grid']) ? '1' : '0',
            'canvas_guides_enabled' => !empty($_POST['pdf_builder_canvas_show_guides']) || !empty($_POST['pdf_builder_canvas_guides_enabled']) ? '1' : '0',
            'canvas_drag_enabled' => !empty($_POST['pdf_builder_canvas_drag_enabled']) ? '1' : '0',
            'canvas_resize_enabled' => !empty($_POST['pdf_builder_canvas_resize_enabled']) ? '1' : '0',
            'canvas_rotate_enabled' => !empty($_POST['pdf_builder_canvas_rotate_enabled']) || !empty($_POST['pdf_builder_canvas_enable_rotation']) ? '1' : '0',
            'canvas_multi_select' => !empty($_POST['pdf_builder_canvas_multi_select']) ? '1' : '0',
            'canvas_keyboard_shortcuts' => !empty($_POST['pdf_builder_canvas_enable_keyboard_shortcuts']) || !empty($_POST['pdf_builder_canvas_keyboard_shortcuts']) ? '1' : '0',
            'canvas_selection_mode' => sanitize_text_field($_POST['pdf_builder_canvas_canvas_selection_mode'] ?? $_POST['pdf_builder_canvas_selection_mode'] ?? 'click'),
            'canvas_export_format' => sanitize_text_field($_POST['pdf_builder_canvas_export_format'] ?? $_POST['pdf_builder_canvas_export_format'] ?? 'png'),
            'canvas_export_quality' => intval($_POST['pdf_builder_canvas_export_quality'] ?? $_POST['pdf_builder_canvas_export_quality'] ?? 90),
            'canvas_export_transparent' => !empty($_POST['pdf_builder_canvas_export_transparent']) ? '1' : '0',
            'canvas_fps_target' => intval($_POST['pdf_builder_canvas_max_fps'] ?? $_POST['pdf_builder_canvas_fps_target'] ?? 60),
            'canvas_memory_limit_js' => intval($_POST['pdf_builder_canvas_memory_limit_js'] ?? $_POST['pdf_builder_canvas_memory_limit_js'] ?? 256),
            'canvas_memory_limit_php' => intval($_POST['pdf_builder_canvas_memory_limit_php'] ?? $_POST['pdf_builder_canvas_memory_limit_php'] ?? 256),
            'canvas_lazy_loading_editor' => !empty($_POST['pdf_builder_canvas_lazy_loading_editor']) ? '1' : '0',
            'canvas_preload_critical' => !empty($_POST['pdf_builder_canvas_preload_critical']) ? '1' : '0',
            'canvas_lazy_loading_plugin' => !empty($_POST['pdf_builder_canvas_lazy_loading_plugin']) ? '1' : '0',
            'canvas_debug_enabled' => !empty($_POST['pdf_builder_canvas_debug_mode']) || !empty($_POST['pdf_builder_canvas_debug_enabled']) ? '1' : '0',
            'canvas_performance_monitoring' => !empty($_POST['pdf_builder_canvas_show_fps']) || !empty($_POST['pdf_builder_canvas_performance_monitoring']) ? '1' : '0',
            'canvas_error_reporting' => !empty($_POST['pdf_builder_canvas_error_reporting']) ? '1' : '0',
        ];

        $saved_count = 0;
        $canvas_settings = [];

        foreach ($settings as $key => $value) {
            if ($key === 'allowed_roles') {
                update_option('pdf_builder_allowed_roles', $value);
            } elseif ($key === 'order_status_templates') {
                update_option('pdf_builder_order_status_templates', $value);
            } elseif (strpos($key, 'canvas_') === 0) {
                // Paramètres canvas utilisent le préfixe pdf_builder_canvas_
                $canvas_key = substr($key, 7); // Enlever 'canvas_' du début
                update_option('pdf_builder_canvas_' . $canvas_key, $value);
                $canvas_settings[$canvas_key] = $value;
            } else {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
        }

        // Sauvegarder aussi l'option globale canvas_settings pour la cohérence
        if (!empty($canvas_settings)) {
            update_option('pdf_builder_canvas_settings', $canvas_settings);
        }

        return $saved_count;
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
            update_option('pdf_builder_' . $key, $value);
        }

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
        $settings = [
            'developer_enabled' => $_POST['developer_enabled'] ?? '0',
            'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
            'debug_php_errors' => isset($_POST['debug_php_errors']) ? '1' : '0',
            'debug_javascript' => isset($_POST['debug_javascript']) ? '1' : '0',
            'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']) ? '1' : '0',
            'debug_ajax' => isset($_POST['debug_ajax']) ? '1' : '0',
            'debug_performance' => isset($_POST['debug_performance']) ? '1' : '0',
            'debug_database' => isset($_POST['debug_database']) ? '1' : '0',
            'log_level' => intval($_POST['log_level'] ?? 3),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),
            'license_test_mode' => isset($_POST['license_test_mode']) ? '1' : '0',
            'force_https' => isset($_POST['force_https']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
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

        wp_send_json_error(['message' => 'Handler not implemented']);
    }

    /**
     * Handler pour supprimer les fichiers temporaires
     */
    public function handle_remove_temp_files() {
        if (!$this->nonce_manager->validate_ajax_request('remove_temp_files')) {
            return;
        }

        wp_send_json_error(['message' => 'Handler not implemented']);
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
            $backup_manager = \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();
            $filepath = $backup_manager->backup_dir . $filename;

            if (!file_exists($filepath)) {
                wp_send_json_error(['message' => __('Fichier de sauvegarde introuvable.', 'pdf-builder-pro')]);
                return;
            }

            // Générer l'URL de téléchargement
            $upload_dir = wp_upload_dir();
            $relative_path = str_replace($upload_dir['basedir'], '', $filepath);
            $download_url = $upload_dir['baseurl'] . $relative_path;

            wp_send_json_success([
                'download_url' => $download_url,
                'filename' => $filename
            ]);

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur téléchargement sauvegarde: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }
}

// Initialiser le handler unifié
PDF_Builder_Unified_Ajax_Handler::get_instance();