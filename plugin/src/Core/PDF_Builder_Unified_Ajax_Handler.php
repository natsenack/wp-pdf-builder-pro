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
            // Général
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
        ];

        $saved_count = 0;
        foreach ($settings as $key => $value) {
            if ($key === 'allowed_roles') {
                update_option('pdf_builder_allowed_roles', $value);
            } else {
                update_option('pdf_builder_' . $key, $value);
            }
            $saved_count++;
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
        $settings = [
            'license_enable_notifications' => isset($_POST['enable_expiration_notifications']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
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
}

// Initialiser le handler unifié
PDF_Builder_Unified_Ajax_Handler::get_instance();