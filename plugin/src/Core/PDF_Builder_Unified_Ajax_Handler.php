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
        error_log("[UNIFIED AJAX] PDF_Builder_Unified_Ajax_Handler constructor called");
        $this->nonce_manager = PDF_Builder_Nonce_Manager::get_instance();
        $this->init_hooks();
    }

    /**
     * Initialise les hooks AJAX
     */
    private function init_hooks() {
        error_log("[UNIFIED AJAX] init_hooks called - registering AJAX handlers");

        // Actions de sauvegarde principales
        add_action('wp_ajax_pdf_builder_save_settings', [$this, 'handle_save_settings']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_save_settings");

        add_action('wp_ajax_pdf_builder_save_all_settings', [$this, 'handle_save_all_settings']);
        // REMOVED: pdf_builder_save_canvas_settings is now handled by AjaxHandler to avoid conflicts
        // add_action('wp_ajax_pdf_builder_save_canvas_settings', [$this, 'handle_save_canvas_settings']);

        // Actions canvas
        add_action('wp_ajax_pdf_builder_get_canvas_orientations', [$this, 'handle_get_canvas_orientations']);

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
        // add_action('wp_ajax_pdf_builder_list_backups', [$this, 'handle_list_backups']); // Commented out - using main implementation
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

        // Actions canvas
        add_action('wp_ajax_pdf_builder_save_canvas_settings', [$this, 'handle_save_canvas_settings']);
        
        // Actions de génération PDF et images
        add_action('wp_ajax_pdf_builder_generate_pdf', [$this, 'handle_generate_pdf']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_generate_pdf");
        add_action('wp_ajax_pdf_builder_generate_image', [$this, 'handle_generate_image']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_generate_image");
        add_action('wp_ajax_pdf_builder_debug_html', [$this, 'handle_debug_html']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_debug_html");
        add_action('wp_ajax_pdf_builder_get_preview_html', [$this, 'handle_get_preview_html']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_get_preview_html");
    }

    /**
     * Handler principal pour la sauvegarde des paramètres
     */
    public function handle_save_settings() {
        error_log("[UNIFIED AJAX] handle_save_settings called - POST data: " . json_encode($_POST));
        error_log("[UNIFIED AJAX] REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("[UNIFIED AJAX] action parameter: " . ($_POST['action'] ?? 'NOT SET'));

        if (!$this->nonce_manager->validate_ajax_request('save_settings')) {
            error_log("[UNIFIED AJAX] Nonce validation FAILED");
            return;
        }

        error_log("[UNIFIED AJAX] Nonce validation PASSED");

        try {
            $current_tab = sanitize_text_field($_POST['tab'] ?? 'all');
            error_log("[UNIFIED AJAX] Processing tab: {$current_tab}");
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
                    error_log("[UNIFIED AJAX] Calling save_general_settings");
                    $saved_count = $this->save_general_settings();
                    $saved_options = $this->get_saved_options_for_tab('general');
                    error_log("[UNIFIED AJAX] save_general_settings returned: {$saved_count}");
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
                        // error_log('PDF Builder: Processing developer tab save');
                        // error_log('PDF Builder: Developer enabled POST: ' . ($_POST['pdf_builder_developer_enabled'] ?? 'not set'));
                        // error_log('PDF Builder: Debug PHP errors POST: ' . ($_POST['debug_php_errors'] ?? 'not set'));
                    }
                    $saved_count = $this->save_developer_settings();
                    $saved_options = $this->get_saved_options_for_tab('developpeur');
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        // error_log('PDF Builder: Developer settings saved, count: ' . $saved_count);
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
                error_log("[UNIFIED AJAX] Sending success response with saved_count: {$saved_count}");
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'saved_count' => $saved_count,
                    'saved_settings' => $saved_options,
                    'new_nonce' => $this->nonce_manager->generate_nonce()
                ]);
            } else {
                error_log("[UNIFIED AJAX] Sending error response - no settings saved");
                wp_send_json_error(['message' => 'Aucun paramètre sauvegardé']);
            }

        } catch (Exception $e) {
            // error_log('[PDF Builder AJAX] Erreur sauvegarde: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Handler pour la sauvegarde des paramètres Canvas
     */
    public function handle_save_canvas_settings() {
        error_log("[PDF Builder] SAVE_CANVAS_START - Handler called");

        if (!$this->nonce_manager->validate_ajax_request('pdf_builder_canvas_settings')) {
            error_log("[PDF Builder] SAVE_CANVAS_ERROR - Nonce validation failed");
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        error_log("[PDF Builder] SAVE_CANVAS_START - Nonce valid, processing POST data: " . print_r($_POST, true));

        try {
            $saved_count = 0;
            $saved_options = [];

            // Liste des paramètres Canvas à sauvegarder
            $canvas_settings = [
                'pdf_builder_canvas_width',
                'pdf_builder_canvas_height',
                'pdf_builder_canvas_dpi',
                'pdf_builder_canvas_format',
                'pdf_builder_canvas_formats',
                'pdf_builder_canvas_orientations',
                'pdf_builder_canvas_bg_color',
                'pdf_builder_canvas_border_color',
                'pdf_builder_canvas_border_width',
                'pdf_builder_canvas_shadow_enabled',
                'pdf_builder_canvas_container_bg_color',
                'pdf_builder_canvas_grid_enabled',
                'pdf_builder_canvas_grid_size',
                'pdf_builder_canvas_guides_enabled',
                'pdf_builder_canvas_snap_to_grid',
                'pdf_builder_canvas_zoom_min',
                'pdf_builder_canvas_zoom_max',
                'pdf_builder_canvas_zoom_default',
                'pdf_builder_canvas_zoom_step',
                'pdf_builder_canvas_export_quality',
                'pdf_builder_canvas_export_format',
                'pdf_builder_canvas_export_transparent',
                'pdf_builder_canvas_drag_enabled',
                'pdf_builder_canvas_resize_enabled',
                'pdf_builder_canvas_rotate_enabled',
                'pdf_builder_canvas_multi_select',
                'pdf_builder_canvas_selection_mode',
                'pdf_builder_canvas_keyboard_shortcuts',
                'pdf_builder_canvas_fps_target',
                'pdf_builder_canvas_memory_limit_js',
                'pdf_builder_canvas_response_timeout',
                'pdf_builder_canvas_lazy_loading_editor',
                'pdf_builder_canvas_preload_critical',
                'pdf_builder_canvas_lazy_loading_plugin',
                'pdf_builder_canvas_debug_enabled',
                'pdf_builder_canvas_performance_monitoring',
                'pdf_builder_canvas_error_reporting',
                'pdf_builder_canvas_memory_limit_php',
                'pdf_builder_canvas_backup' // Paramètre de cache/backup
            ];

            // Sauvegarder chaque paramètre
            foreach ($canvas_settings as $setting_key) {
                if (isset($_POST[$setting_key])) {
                    $value = $_POST[$setting_key];
                    
                    // Gestion spéciale pour les champs array (dpi, formats, orientations)
                    $array_fields = ['pdf_builder_canvas_dpi', 'pdf_builder_canvas_formats', 'pdf_builder_canvas_orientations'];
                    if (in_array($setting_key, $array_fields)) {
                        if (is_array($value)) {
                            $value = implode(',', array_map('sanitize_text_field', $value));
                        } elseif (is_string($value)) {
                            $value = sanitize_text_field($value);
                        } else {
                            $value = '';
                        }
                    } else {
                        $value = sanitize_text_field($value);
                    }
                    
                    // Log pour déboguer
                    error_log("[PDF Builder] Sauvegarde Canvas - {$setting_key}: {$value}");
                    
                    // Log spécifique pour les toggles de grille
                    if (in_array($setting_key, ['pdf_builder_canvas_grid_enabled', 'pdf_builder_canvas_guides_enabled', 'pdf_builder_canvas_snap_to_grid'])) {
                        error_log("[PDF Builder] GRID_TOGGLE_SAVE - {$setting_key}: {$value}");
                        
                        // Validation premium: forcer à '0' si l'utilisateur n'a pas accès à la fonctionnalité
                        if (!\PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation')) {
                            $value = '0';
                            error_log("[PDF Builder] GRID_TOGGLE_FORCED_OFF - {$setting_key}: utilisateur gratuit, forcé à 0");
                        }
                    }
                    
                    // Log pour tous les toggles d'interactions
                    if (in_array($setting_key, ['pdf_builder_canvas_drag_enabled', 'pdf_builder_canvas_resize_enabled', 'pdf_builder_canvas_rotate_enabled', 'pdf_builder_canvas_multi_select', 'pdf_builder_canvas_keyboard_shortcuts'])) {
                        error_log("[PDF Builder] INTERACTIONS_TOGGLE_SAVE - {$setting_key}: {$value}");
                    }
                    
                    // Validation spécifique selon le type de paramètre
                    if ((strpos($setting_key, '_width') !== false && strpos($setting_key, '_border_width') === false) || strpos($setting_key, '_height') !== false) {
                        $value = intval($value);
                        $value = max(100, min(5000, $value)); // Limiter entre 100 et 5000
                    } elseif (strpos($setting_key, '_border_width') !== false) {
                        $value = intval($value);
                        $value = max(0, min(10, $value)); // Limiter entre 0 et 10 pour l'épaisseur des bordures
                        error_log("[PDF Builder] Border width validé: {$value}");
                    } elseif (strpos($setting_key, '_dpi') !== false) {
                        $value = intval($value);
                        $value = max(72, min(600, $value)); // Limiter entre 72 et 600 DPI
                    } elseif (strpos($setting_key, '_size') !== false || strpos($setting_key, '_limit') !== false) {
                        $value = intval($value);
                    } elseif (strpos($setting_key, '_enabled') !== false || strpos($setting_key, '_transparent') !== false) {
                        $value = $value === '1' ? '1' : '0';
                    } elseif (strpos($setting_key, '_color') !== false) {
                        // Validation couleur hex
                        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $value)) {
                            $value = '#ffffff'; // Défaut blanc si invalide
                        }
                    }

                    pdf_builder_update_option($setting_key, $value);
                    
                    // Also update the settings array to keep consistency with the main form
                    $settings = pdf_builder_get_option('pdf_builder_settings', array());
                    $settings[$setting_key] = $value;
                    pdf_builder_update_option('pdf_builder_settings', $settings);
                    
                    $saved_options[$setting_key] = $value;
                    $saved_count++;
                    
                    // Vérifier immédiatement que la valeur a été sauvegardée
                    $verify_value = pdf_builder_get_option($setting_key);
                    error_log("[PDF Builder] SAVE_VERIFY - {$setting_key}: saved={$value}, retrieved={$verify_value}");
                    
                    // Log spécifique pour les toggles
                    if (in_array($setting_key, ['pdf_builder_canvas_grid_enabled', 'pdf_builder_canvas_guides_enabled', 'pdf_builder_canvas_snap_to_grid', 'pdf_builder_canvas_drag_enabled', 'pdf_builder_canvas_resize_enabled', 'pdf_builder_canvas_rotate_enabled', 'pdf_builder_canvas_multi_select', 'pdf_builder_canvas_keyboard_shortcuts'])) {
                        error_log("[PDF Builder] TOGGLE_SAVE_VERIFY - {$setting_key}: saved={$value}, retrieved={$verify_value}");
                    }
                }
            }

            if ($saved_count > 0) {
                error_log("[PDF Builder] SAVE_CANVAS_SUCCESS - {$saved_count} paramètres sauvegardés: " . implode(', ', array_keys($saved_options)));
                wp_send_json_success([
                    'message' => 'Paramètres Canvas sauvegardés avec succès',
                    'saved_count' => $saved_count,
                    'saved_settings' => $saved_options,
                    'new_nonce' => $this->nonce_manager->generate_nonce()
                ]);
            } else {
                error_log("[PDF Builder] SAVE_CANVAS_WARNING - Aucun paramètre sauvegardé");
                wp_send_json_error(['message' => 'Aucun paramètre Canvas sauvegardé']);
            }

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur sauvegarde Canvas: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Handler pour récupérer les orientations disponibles du canvas
     */
    public function handle_get_canvas_orientations() {
        if (!$this->nonce_manager->validate_ajax_request('pdf_builder_ajax')) {
            return;
        }

        try {
            // Récupérer les orientations disponibles depuis les paramètres canvas
            $available_orientations_string = pdf_builder_get_option('pdf_builder_canvas_orientations', 'portrait,landscape');
            
            if (is_string($available_orientations_string) && strpos($available_orientations_string, ',') !== false) {
                $available_orientations = explode(',', $available_orientations_string);
            } elseif (is_array($available_orientations_string)) {
                $available_orientations = $available_orientations_string;
            } else {
                $available_orientations = [$available_orientations_string];
            }
            
            $available_orientations = array_map('strval', $available_orientations);

            // Retourner les permissions d'orientation
            $orientation_permissions = [
                'allowPortrait' => in_array('portrait', $available_orientations),
                'allowLandscape' => in_array('landscape', $available_orientations),
                'defaultOrientation' => pdf_builder_get_option('pdf_builder_canvas_orientation', 'portrait'),
                'availableOrientations' => $available_orientations
            ];

            wp_send_json_success($orientation_permissions);

        } catch (Exception $e) {
            error_log('[PDF Builder AJAX] Erreur récupération orientations: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne du serveur']);
        }
    }

    /**
     * Collecte les options sauvegardées pour un onglet spécifique
     */
    private function get_saved_options_for_tab($tab) {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $saved_options = [];

        switch ($tab) {
            case 'all':
                // Retourner toutes les options pertinentes pour la mise à jour du formulaire
                $saved_options = [
                    // Général
                    'company_phone_manual' => $settings['pdf_builder_company_phone_manual'] ?? '',
                    'company_siret' => $settings['pdf_builder_company_siret'] ?? '',
                    'company_vat' => $settings['pdf_builder_company_vat'] ?? '',
                    'company_rcs' => $settings['pdf_builder_company_rcs'] ?? '',
                    'company_capital' => $settings['pdf_builder_company_capital'] ?? '',

                    // Cache
                    'cache_enabled' => $settings['pdf_builder_cache_enabled'] ?? '0',
                    'cache_ttl' => $settings['pdf_builder_cache_ttl'] ?? 3600,
                    'cache_compression' => $settings['pdf_builder_cache_compression'] ?? '1',
                    'cache_auto_cleanup' => $settings['pdf_builder_cache_auto_cleanup'] ?? '1',
                    'cache_max_size' => $settings['pdf_builder_cache_max_size'] ?? 100,

                    // Système
                    'auto_maintenance' => $settings['pdf_builder_auto_maintenance'] ?? '1',
                    'backup_retention' => $settings['pdf_builder_backup_retention'] ?? 30,

                    // Sécurité
                    'security_level' => $settings['pdf_builder_security_level'] ?? 'medium',
                    'enable_logging' => $settings['pdf_builder_enable_logging'] ?? '1',
                    'gdpr_enabled' => $settings['pdf_builder_gdpr_enabled'] ?? '0',
                    'gdpr_consent_required' => $settings['pdf_builder_gdpr_consent_required'] ?? '0',
                    'gdpr_data_retention' => $settings['pdf_builder_gdpr_data_retention'] ?? 365,
                    'gdpr_audit_enabled' => $settings['pdf_builder_gdpr_audit_enabled'] ?? '0',
                    'gdpr_encryption_enabled' => $settings['pdf_builder_gdpr_encryption_enabled'] ?? '0',
                    'gdpr_consent_analytics' => $settings['pdf_builder_gdpr_consent_analytics'] ?? '0',
                    'gdpr_consent_templates' => $settings['pdf_builder_gdpr_consent_templates'] ?? '0',
                    'gdpr_consent_marketing' => $settings['pdf_builder_gdpr_consent_marketing'] ?? '0',

                    // Développeur
                    'developer_enabled' => $settings['pdf_builder_developer_enabled'] ?? '0',
                    'developer_password' => $settings['pdf_builder_developer_password'] ?? '',
                    'performance_monitoring' => $settings['pdf_builder_performance_monitoring'] ?? '0',

                    // PDF
                    'pdf_quality' => $settings['pdf_builder_pdf_quality'] ?? 'high',
                    'default_format' => $settings['pdf_builder_default_format'] ?? 'A4',
                    'default_orientation' => $settings['pdf_builder_default_orientation'] ?? 'portrait',

                    // Contenu
                    'template_library_enabled' => pdf_builder_get_option('pdf_builder_template_library_enabled', '1'),
                    'default_template' => $settings['pdf_builder_default_template'] ?? 'blank',

                    // Canvas settings (stored individually)
                    'canvas_width' => pdf_builder_get_option('pdf_builder_canvas_width', 794),
                    'canvas_height' => pdf_builder_get_option('pdf_builder_canvas_height', 1123),
                    'canvas_dpi' => pdf_builder_get_option('pdf_builder_canvas_dpi', 96),
                    'canvas_format' => pdf_builder_get_option('pdf_builder_canvas_format', 'A4'),
                    'canvas_bg_color' => pdf_builder_get_option('pdf_builder_canvas_bg_color', '#ffffff'),
                    'canvas_border_color' => pdf_builder_get_option('pdf_builder_canvas_border_color', '#cccccc'),
                    'canvas_border_width' => pdf_builder_get_option('pdf_builder_canvas_border_width', 1),
                    'canvas_shadow_enabled' => pdf_builder_get_option('pdf_builder_canvas_shadow_enabled', '0'),
                    'canvas_container_bg_color' => pdf_builder_get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
                    'canvas_grid_enabled' => pdf_builder_get_option('pdf_builder_canvas_grid_enabled', '1'),
                    'canvas_grid_size' => pdf_builder_get_option('pdf_builder_canvas_grid_size', 20),
                    'canvas_guides_enabled' => pdf_builder_get_option('pdf_builder_canvas_guides_enabled', '1'),
                    'canvas_snap_to_grid' => pdf_builder_get_option('pdf_builder_canvas_snap_to_grid', '1'),
                    'canvas_zoom_min' => pdf_builder_get_option('pdf_builder_canvas_zoom_min', 25),
                    'canvas_zoom_max' => pdf_builder_get_option('pdf_builder_canvas_zoom_max', 500),
                    'canvas_zoom_default' => pdf_builder_get_option('pdf_builder_canvas_zoom_default', 100),
                    'canvas_zoom_step' => pdf_builder_get_option('pdf_builder_canvas_zoom_step', 25),
                    'canvas_drag_enabled' => pdf_builder_get_option('pdf_builder_canvas_drag_enabled', '1'),
                    'canvas_resize_enabled' => pdf_builder_get_option('pdf_builder_canvas_resize_enabled', '1'),
                    'canvas_rotate_enabled' => pdf_builder_get_option('pdf_builder_canvas_rotate_enabled', '1'),
                    'canvas_multi_select' => pdf_builder_get_option('pdf_builder_canvas_multi_select', '1'),
                    'canvas_selection_mode' => pdf_builder_get_option('pdf_builder_canvas_selection_mode', 'single'),
                    'canvas_keyboard_shortcuts' => pdf_builder_get_option('pdf_builder_canvas_keyboard_shortcuts', '1'),
                    'canvas_export_quality' => pdf_builder_get_option('pdf_builder_canvas_export_quality', 90),
                    'canvas_export_format' => pdf_builder_get_option('pdf_builder_canvas_export_format', 'png'),
                    'canvas_export_transparent' => pdf_builder_get_option('pdf_builder_canvas_export_transparent', '0'),
                    'canvas_fps_target' => pdf_builder_get_option('pdf_builder_canvas_fps_target', 60),
                    'canvas_memory_limit_js' => pdf_builder_get_option('pdf_builder_canvas_memory_limit_js', 50),
                    'canvas_memory_limit_php' => pdf_builder_get_option('pdf_builder_canvas_memory_limit_php', 256),
                    'canvas_lazy_loading_editor' => pdf_builder_get_option('pdf_builder_canvas_lazy_loading_editor', '1'),
                    'canvas_performance_monitoring' => pdf_builder_get_option('pdf_builder_canvas_performance_monitoring', '0'),
                    'canvas_error_reporting' => pdf_builder_get_option('pdf_builder_canvas_error_reporting', '0'),

                    // Templates
                    'order_status_templates' => $settings['pdf_builder_order_status_templates'] ?? [],

                    // Licence
                    'license_test_mode' => $settings['pdf_builder_license_test_mode_enabled'] ?? '0',
                    'license_email_reminders' => $settings['pdf_builder_license_email_reminders'] ?? '0',
                    'license_reminder_email' => $settings['pdf_builder_license_reminder_email'] ?? get_option('admin_email', ''),
                    'pdf_builder_license_test_key_expires' => $settings['pdf_builder_license_test_key_expires'] ?? '',
                ];
                break;

            case 'general':
                $saved_options = [
                    'company_phone_manual' => pdf_builder_get_option('pdf_builder_company_phone_manual', ''),
                    'company_siret' => pdf_builder_get_option('pdf_builder_company_siret', ''),
                    'company_vat' => pdf_builder_get_option('pdf_builder_company_vat', ''),
                    'company_rcs' => pdf_builder_get_option('pdf_builder_company_rcs', ''),
                    'company_capital' => pdf_builder_get_option('pdf_builder_company_capital', ''),
                    'cache_enabled' => pdf_builder_get_option('pdf_builder_cache_enabled', '0'),
                    'cache_ttl' => pdf_builder_get_option('pdf_builder_cache_ttl', 3600),
                    'cache_compression' => pdf_builder_get_option('pdf_builder_cache_compression', '1'),
                    'cache_auto_cleanup' => pdf_builder_get_option('pdf_builder_cache_auto_cleanup', '1'),
                    'cache_max_size' => pdf_builder_get_option('pdf_builder_cache_max_size', 100),
                    'pdf_quality' => pdf_builder_get_option('pdf_builder_pdf_quality', 'high'),
                    'default_format' => pdf_builder_get_option('pdf_builder_default_format', 'A4'),
                    'default_orientation' => pdf_builder_get_option('pdf_builder_default_orientation', 'portrait'),
                ];
                break;

            case 'developpeur':
                $saved_options = [
                    'pdf_builder_developer_enabled' => pdf_builder_get_option('pdf_builder_developer_enabled', '0'),
                    'pdf_builder_developer_password' => pdf_builder_get_option('pdf_builder_developer_password', ''),
                    'pdf_builder_performance_monitoring' => pdf_builder_get_option('pdf_builder_performance_monitoring', '0'),
                    'pdf_builder_license_test_mode_enabled' => pdf_builder_get_option('pdf_builder_license_test_mode_enabled', '0'),
                    'pdf_builder_license_test_key_expires' => pdf_builder_get_option('pdf_builder_license_test_key_expires', ''),
                ];
                break;

            case 'systeme':
                $saved_options = [
                    'cache_enabled' => pdf_builder_get_option('pdf_builder_cache_enabled', '0'),
                    'cache_compression' => pdf_builder_get_option('pdf_builder_cache_compression', '1'),
                    'cache_auto_cleanup' => pdf_builder_get_option('pdf_builder_cache_auto_cleanup', '1'),
                    'cache_max_size' => pdf_builder_get_option('pdf_builder_cache_max_size', 100),
                    'cache_ttl' => pdf_builder_get_option('pdf_builder_cache_ttl', 3600),
                    'auto_maintenance' => pdf_builder_get_option('pdf_builder_auto_maintenance', '1'),
                    'auto_backup' => pdf_builder_get_option('pdf_builder_auto_backup', '1'),
                    'auto_backup_frequency' => pdf_builder_get_option('pdf_builder_auto_backup_frequency', 'daily'),
                    'backup_retention' => pdf_builder_get_option('pdf_builder_backup_retention', 30),
                ];
                break;

            case 'securite':
                $saved_options = [
                    'security_level' => pdf_builder_get_option('pdf_builder_security_level', 'medium'),
                    'enable_logging' => pdf_builder_get_option('pdf_builder_enable_logging', '1'),
                    'gdpr_enabled' => pdf_builder_get_option('pdf_builder_gdpr_enabled', '0'),
                    'gdpr_consent_required' => pdf_builder_get_option('pdf_builder_gdpr_consent_required', '0'),
                    'gdpr_data_retention' => pdf_builder_get_option('pdf_builder_gdpr_data_retention', 365),
                    'gdpr_audit_enabled' => pdf_builder_get_option('pdf_builder_gdpr_audit_enabled', '0'),
                    'gdpr_encryption_enabled' => pdf_builder_get_option('pdf_builder_gdpr_encryption_enabled', '0'),
                    'gdpr_consent_analytics' => pdf_builder_get_option('pdf_builder_gdpr_consent_analytics', '0'),
                    'gdpr_consent_templates' => pdf_builder_get_option('pdf_builder_gdpr_consent_templates', '0'),
                    'gdpr_consent_marketing' => pdf_builder_get_option('pdf_builder_gdpr_consent_marketing', '0'),
                ];
                break;

            case 'contenu':
                $saved_options = [
                    // Canvas dimensions
                    'canvas_width' => pdf_builder_get_option('pdf_builder_canvas_width', 794),
                    'canvas_height' => pdf_builder_get_option('pdf_builder_canvas_height', 1123),
                    'canvas_dpi' => pdf_builder_get_option('pdf_builder_canvas_dpi', 96),
                    'canvas_format' => pdf_builder_get_option('pdf_builder_canvas_format', 'A4'),

                    // Canvas appearance
                    'canvas_bg_color' => pdf_builder_get_option('pdf_builder_canvas_bg_color', '#ffffff'),
                    'canvas_border_color' => pdf_builder_get_option('pdf_builder_canvas_border_color', '#cccccc'),
                    'canvas_border_width' => pdf_builder_get_option('pdf_builder_canvas_border_width', 1),
                    'canvas_shadow_enabled' => pdf_builder_get_option('pdf_builder_canvas_shadow_enabled', '0'),
                    'canvas_container_bg_color' => pdf_builder_get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),

                    // Canvas grid & guides
                    'canvas_grid_enabled' => pdf_builder_get_option('pdf_builder_canvas_grid_enabled', '1'),
                    'canvas_grid_size' => pdf_builder_get_option('pdf_builder_canvas_grid_size', 20),
                    'canvas_guides_enabled' => pdf_builder_get_option('pdf_builder_canvas_guides_enabled', '1'),
                    'canvas_snap_to_grid' => pdf_builder_get_option('pdf_builder_canvas_snap_to_grid', '1'),

                    // Canvas zoom
                    'canvas_zoom_min' => pdf_builder_get_option('pdf_builder_canvas_zoom_min', 25),
                    'canvas_zoom_max' => pdf_builder_get_option('pdf_builder_canvas_zoom_max', 500),
                    'canvas_zoom_default' => pdf_builder_get_option('pdf_builder_canvas_zoom_default', 100),
                    'canvas_zoom_step' => pdf_builder_get_option('pdf_builder_canvas_zoom_step', 25),

                    // Canvas interactions
                    'canvas_drag_enabled' => pdf_builder_get_option('pdf_builder_canvas_drag_enabled', '1'),
                    'canvas_resize_enabled' => pdf_builder_get_option('pdf_builder_canvas_resize_enabled', '1'),
                    'canvas_rotate_enabled' => pdf_builder_get_option('pdf_builder_canvas_rotate_enabled', '1'),
                    'canvas_multi_select' => pdf_builder_get_option('pdf_builder_canvas_multi_select', '1'),
                    'canvas_selection_mode' => pdf_builder_get_option('pdf_builder_canvas_selection_mode', 'single'),
                    'canvas_keyboard_shortcuts' => pdf_builder_get_option('pdf_builder_canvas_keyboard_shortcuts', '1'),

                    // Canvas export
                    'canvas_export_quality' => pdf_builder_get_option('pdf_builder_canvas_export_quality', 90),
                    'canvas_export_format' => pdf_builder_get_option('pdf_builder_canvas_export_format', 'png'),
                    'canvas_export_transparent' => pdf_builder_get_option('pdf_builder_canvas_export_transparent', '0'),

                    // Canvas performance
                    'canvas_fps_target' => pdf_builder_get_option('pdf_builder_canvas_fps_target', 60),
                    'canvas_memory_limit_js' => pdf_builder_get_option('pdf_builder_canvas_memory_limit_js', 50),
                    'canvas_memory_limit_php' => pdf_builder_get_option('pdf_builder_canvas_memory_limit_php', 256),

                    // Canvas debug
                    'canvas_lazy_loading_editor' => pdf_builder_get_option('pdf_builder_canvas_lazy_loading_editor', '1'),
                    'canvas_performance_monitoring' => pdf_builder_get_option('pdf_builder_canvas_performance_monitoring', '0'),
                    'canvas_error_reporting' => pdf_builder_get_option('pdf_builder_canvas_error_reporting', '0'),

                    // Other content settings
                    'canvas_max_size' => pdf_builder_get_option('pdf_builder_canvas_max_size', 10000),
                    'canvas_quality' => pdf_builder_get_option('pdf_builder_canvas_quality', 90),
                    'template_library_enabled' => $settings['pdf_builder_template_library_enabled'] ?? '1',
                    'default_template' => $settings['pdf_builder_default_template'] ?? 'blank',
                ];
                break;

            case 'templates':
                $saved_options = [
                    'order_status_templates' => $settings['pdf_builder_order_status_templates'] ?? [],
                ];
                break;

            case 'pdf':
                $saved_options = [
                    'pdf_quality' => pdf_builder_get_option('pdf_builder_pdf_quality', 'high'),
                    'default_format' => pdf_builder_get_option('pdf_builder_default_format', 'A4'),
                    'default_orientation' => pdf_builder_get_option('pdf_builder_default_orientation', 'portrait'),
                ];
                break;

            case 'licence':
                $saved_options = [
                    'license_key' => pdf_builder_get_option('pdf_builder_license_key', ''),
                    'license_status' => pdf_builder_get_option('pdf_builder_license_status', 'free'),
                    'license_data' => pdf_builder_get_option('pdf_builder_license_data', []),
                    'license_test_key' => pdf_builder_get_option('pdf_builder_license_test_key', ''),
                    'license_test_key_expires' => pdf_builder_get_option('pdf_builder_license_test_key_expires', ''),
                    'license_email_reminders' => pdf_builder_get_option('pdf_builder_license_email_reminders', '0'),
                    'license_test_mode' => pdf_builder_get_option('pdf_builder_license_test_mode_enabled', '0'),
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
        if (!$this->nonce_manager->validate_ajax_request('save_all_settings')) {
            return;
        }

        try {
            // Check if data is sent as flattened POST data (new format) or JSON (legacy)
            $form_data_json = $_POST['form_data'] ?? '';
            // error_log('[PDF Builder AJAX] handle_save_all_settings called with form_data: ' . substr($form_data_json, 0, 500));

            if (!empty($form_data_json)) {
                // Legacy JSON format - not supported anymore, use flattened format
                error_log('[PDF Builder AJAX] Legacy JSON format not supported, using flattened format instead');
                $saved_count = $this->save_all_settings_from_flattened_data();
            } else {
                // New flattened format - save all settings from POST data
                $saved_count = $this->save_all_settings_from_flattened_data();
            }

            // error_log('[PDF Builder AJAX] Saved ' . $saved_count . ' settings');

            $saved_options = $this->get_saved_options_for_tab('all');

            wp_send_json_success([
                'message' => 'Tous les paramètres sauvegardés avec succès',
                'saved_count' => $saved_count,
                'saved_settings' => $saved_options,
                'new_nonce' => $this->nonce_manager->generate_nonce()
            ]);

        } catch (Exception $e) {
            // error_log('[PDF Builder AJAX] Erreur sauvegarde tous: ' . $e->getMessage());
            // error_log('[PDF Builder AJAX] Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => 'Erreur interne du serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Sauvegarde tous les paramètres depuis les données POST aplaties
     */
    private function save_all_settings() {
        return $this->save_all_settings_from_flattened_data();
    }

    /**
     * Sauvegarde tous les paramètres depuis les données POST aplaties
     */
    private function save_all_settings_from_flattened_data() {
        $saved_count = 0;
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        error_log('[PDF Builder AJAX] Processing flattened data, POST keys: ' . implode(', ', array_keys($_POST)));
        error_log('[PDF Builder AJAX] Full POST data: ' . json_encode($_POST));

        // FIRST: Handle the main pdf_builder_settings array if it exists
        if (isset($_POST['pdf_builder_settings']) && is_array($_POST['pdf_builder_settings'])) {
            error_log('[PDF Builder AJAX] Found pdf_builder_settings array with keys: ' . implode(', ', array_keys($_POST['pdf_builder_settings'])));

            // EXTRACTION ET SAUVEGARDE DES CHAMPS DE L'ONGLET GÉNÉRAL DANS DES LIGNES SÉPARÉES
            $general_fields = [
                'pdf_builder_company_phone_manual',
                'pdf_builder_company_siret',
                'pdf_builder_company_vat',
                'pdf_builder_company_rcs',
                'pdf_builder_company_capital'
            ];

            foreach ($general_fields as $general_field) {
                if (isset($_POST['pdf_builder_settings'][$general_field])) {
                    // Sauvegarder dans une ligne séparée
                    pdf_builder_update_option($general_field, sanitize_text_field($_POST['pdf_builder_settings'][$general_field]));
                    error_log("[PDF Builder AJAX] Saved general field to separate row: {$general_field} = " . $_POST['pdf_builder_settings'][$general_field]);

                    // Supprimer du tableau POST pour éviter le double traitement
                    unset($_POST['pdf_builder_settings'][$general_field]);
                    $saved_count++;
                }
            }

            foreach ($_POST['pdf_builder_settings'] as $setting_key => $setting_value) {
                if (is_array($setting_value)) {
                    // Handle nested arrays like pdf_builder_order_status_templates
                    if ($setting_key === 'pdf_builder_order_status_templates') {
                        $settings[$setting_key] = array_map('sanitize_text_field', $setting_value);
                        error_log('[PDF Builder AJAX] Saved nested array ' . $setting_key . ' with ' . count($setting_value) . ' items: ' . json_encode($setting_value));
                    } else {
                        // Handle other nested arrays if needed
                        $settings[$setting_key] = $setting_value; // Keep as-is for now, sanitize later if needed
                    }
                } else {
                    // Handle simple values
                    if (is_numeric($setting_value)) {
                        $settings[$setting_key] = intval($setting_value);
                    } elseif ($setting_value === '1' || $setting_value === '0') {
                        $settings[$setting_key] = $setting_value;
                    } else {
                        $settings[$setting_key] = sanitize_text_field($setting_value);
                    }
                }
                $saved_count++;
            }

            // Remove pdf_builder_settings from $_POST to avoid double processing
            unset($_POST['pdf_builder_settings']);
        }

        // Debug: check if shadow field is present
        if (isset($_POST['pdf_builder_canvas_shadow_enabled'])) {
            error_log('[PDF Builder AJAX] Shadow field received: ' . $_POST['pdf_builder_canvas_shadow_enabled']);
        } else {
            error_log('[PDF Builder AJAX] Shadow field NOT received in POST');
        }

        // Define field type rules (same as in Ajax_Handlers.php)
        $field_rules = [
            'text_fields' => [
                'pdf_builder_company_phone_manual', 'pdf_builder_company_siret', 'pdf_builder_company_vat', 'pdf_builder_company_rcs', 'pdf_builder_company_capital',
                'pdf_builder_pdf_quality', 'pdf_builder_default_format', 'pdf_builder_default_orientation', 'pdf_builder_default_template',
                'pdf_builder_developer_password',
                // License text fields
                'pdf_builder_license_status', 'pdf_builder_license_key', 'pdf_builder_license_expires',
                'pdf_builder_license_activated_at', 'pdf_builder_license_test_key', 'pdf_builder_license_test_key_expires',
                'pdf_builder_license_reminder_email',
                // System text fields
                'pdf_builder_last_maintenance', 'pdf_builder_next_maintenance', 'pdf_builder_last_backup', 'pdf_builder_cache_last_cleanup',
                // Canvas text fields
                'pdf_builder_canvas_bg_color', 'pdf_builder_canvas_border_color', 'pdf_builder_canvas_container_bg_color', 'pdf_builder_canvas_selection_mode', 'pdf_builder_canvas_export_format',
                'pdf_builder_default_canvas_format', 'pdf_builder_default_canvas_orientation', 'pdf_builder_canvas_unit', 'pdf_builder_canvas_format'
            ],
            'int_fields' => [
                'pdf_builder_cache_max_size', 'pdf_builder_cache_ttl',
                // Canvas int fields
                'pdf_builder_zoom_min', 'pdf_builder_zoom_max', 'pdf_builder_zoom_default', 'pdf_builder_zoom_step', 'pdf_builder_canvas_grid_size', 'pdf_builder_canvas_export_quality',
                'pdf_builder_canvas_fps_target', 'pdf_builder_canvas_memory_limit_js', 'pdf_builder_canvas_memory_limit_php', 'pdf_builder_canvas_dpi',
                'pdf_builder_canvas_width', 'pdf_builder_canvas_height', 'pdf_builder_canvas_border_width', 'pdf_builder_canvas_max_size', 'pdf_builder_canvas_quality'
            ],
            'bool_fields' => [
                'pdf_builder_cache_enabled', 'pdf_builder_cache_compression', 'pdf_builder_cache_auto_cleanup', 'pdf_builder_performance_auto_optimization',
                'pdf_builder_systeme_auto_maintenance', 'pdf_builder_template_library_enabled',
                'pdf_builder_developer_enabled', 'pdf_builder_license_test_mode_enabled', 'pdf_builder_canvas_debug_enabled',
                // License bool fields
                'pdf_builder_license_email_reminders',
                // Debug fields - CORRIGÉ: utilisation des vrais noms de champs du formulaire
                'pdf_builder_debug_javascript', 'pdf_builder_debug_javascript_verbose',
                'pdf_builder_debug_ajax', 'pdf_builder_debug_performance',
                'pdf_builder_debug_database', 'pdf_builder_debug_php_errors',
                // Canvas bool fields
                'pdf_builder_canvas_grid_enabled', 'pdf_builder_canvas_snap_to_grid', 'pdf_builder_canvas_guides_enabled', 'pdf_builder_canvas_drag_enabled',
                'pdf_builder_canvas_resize_enabled', 'pdf_builder_canvas_rotate_enabled', 'pdf_builder_canvas_multi_select', 'pdf_builder_canvas_keyboard_shortcuts',
                'pdf_builder_canvas_export_transparent', 'pdf_builder_canvas_lazy_loading_editor', 'pdf_builder_canvas_preload_critical', 'pdf_builder_canvas_lazy_loading_plugin',
                'pdf_builder_canvas_debug_enabled', 'pdf_builder_canvas_performance_monitoring', 'pdf_builder_canvas_error_reporting', 'pdf_builder_canvas_shadow_enabled',
                // Additional toggles from templates
                'pdf_builder_license_test_mode_enabled', 'pdf_builder_force_https', 'pdf_builder_performance_monitoring',
                'pdf_builder_enable_logging', 'pdf_builder_gdpr_enabled', 'pdf_builder_gdpr_consent_required', 'pdf_builder_gdpr_audit_enabled', 'pdf_builder_gdpr_encryption_enabled',
                'pdf_builder_gdpr_consent_analytics', 'pdf_builder_gdpr_consent_templates', 'pdf_builder_gdpr_consent_marketing',
                'pdf_builder_pdf_metadata_enabled', 'pdf_builder_pdf_print_optimized'
            ],
            'array_fields' => ['order_status_templates'],
            'license_bool_fields' => [
                'license_email_reminders'
            ]
        ];

        // FIRST: Handle all boolean fields - set to 0 if not present in POST (unchecked checkboxes)
        foreach ($field_rules['bool_fields'] as $bool_field) {
            if (isset($_POST[$bool_field])) {
                // Field is present in POST - use its value
                $option_key = '';
                $option_value = ($_POST[$bool_field] === '1') ? 1 : 0;

                if (strpos($bool_field, 'pdf_builder_canvas_') === 0 || strpos($bool_field, 'pdf_builder_zoom_') === 0 || strpos($bool_field, 'pdf_builder_default_canvas_') === 0) {
                    $option_key = $bool_field;
                    $settings[$option_key] = $option_value; // Save to unified settings array instead of individual option
                    if ($bool_field === 'pdf_builder_canvas_shadow_enabled' || strpos($bool_field, 'pdf_builder_canvas_grid') === 0 || strpos($bool_field, 'pdf_builder_canvas_guide') === 0 || strpos($bool_field, 'pdf_builder_canvas_snap') === 0) {
                        error_log('[PDF Builder AJAX] Saved canvas field to settings: ' . $option_key . ' = ' . $option_value . ' (type: ' . gettype($option_value) . ')');
                    }
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

                if (strpos($bool_field, 'pdf_builder_canvas_') === 0 || strpos($bool_field, 'pdf_builder_zoom_') === 0 || strpos($bool_field, 'pdf_builder_default_canvas_') === 0) {
                    $option_key = $bool_field;
                    $settings[$option_key] = $option_value; // Save to unified settings array instead of individual option
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

        // Handle license boolean fields
        foreach ($field_rules['license_bool_fields'] as $bool_field) {
            if (isset($_POST[$bool_field])) {
                // Field is present in POST - use its value
                $option_value = ($_POST[$bool_field] === '1') ? '1' : '0';
                $option_key = 'pdf_builder_' . $bool_field;
                $settings[$option_key] = $option_value;
            } else {
                // Field NOT present in POST - means checkbox was unchecked, set to 0
                $option_key = 'pdf_builder_' . $bool_field;
                $settings[$option_key] = '0';
            }
            $saved_count++;
        }

        // THEN: Process remaining non-boolean fields from POST
        foreach ($_POST as $key => $value) {
            // Skip WordPress internal fields and already processed boolean fields
            if (in_array($key, ['action', 'nonce', 'current_tab']) || in_array($key, $field_rules['bool_fields']) || in_array($key, $field_rules['license_bool_fields'])) {
                continue;
            }

            // Debug log only if JavaScript debug is enabled
            if (isset($_POST['pdf_builder_debug_javascript']) && $_POST['pdf_builder_debug_javascript'] == '1') {
                $display_value = is_array($value) ? 'Array(' . count($value) . ')' : $value;
                // error_log("[UNIFIED HANDLER] Processing non-bool field: '$key' = '$display_value'");
            }

            $option_key = '';
            $option_value = null;

            if (in_array($key, $field_rules['text_fields'])) {
                // Special handling for canvas fields
                if (strpos($key, 'pdf_builder_canvas_') === 0 || strpos($key, 'pdf_builder_zoom_') === 0 || strpos($key, 'pdf_builder_default_canvas_') === 0) {
                    $option_key = $key;
                    $option_value = sanitize_text_field($value ?? '');
                    $settings[$option_key] = $option_value; // Save to unified settings array instead of individual option
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
                if (strpos($key, 'pdf_builder_canvas_') === 0 || strpos($key, 'pdf_builder_zoom_') === 0 || strpos($key, 'pdf_builder_default_canvas_') === 0) {
                    $option_key = $key;
                    $option_value = intval($value ?? 0);
                    $settings[$option_key] = $option_value; // Save to unified settings array instead of individual option
                    if ($key === 'pdf_builder_canvas_grid_size') {
                        error_log('[PDF Builder AJAX] Saved canvas int field to settings: ' . $option_key . ' = ' . $option_value . ' (type: ' . gettype($option_value) . ')');
                    }
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
                    $option_key = strpos($key, 'pdf_builder_') === 0 ? $key : 'pdf_builder_' . $key;
                    $option_value = array_map('sanitize_text_field', $value);
                    $settings[$option_key] = $option_value;
                    $saved_count++;
                } else {
                    $option_key = strpos($key, 'pdf_builder_') === 0 ? $key : 'pdf_builder_' . $key;
                    $option_value = [];
                    $settings[$option_key] = $option_value;
                    $saved_count++;
                }
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

        // EXTRACTION ET SAUVEGARDE DES CLÉS DE LICENCE DANS DES LIGNES SÉPARÉES
        $license_keys = [
            'pdf_builder_license_key',
            'pdf_builder_license_status',
            'pdf_builder_license_data',
            'pdf_builder_license_test_key',
            'pdf_builder_license_test_key_expires',
            'pdf_builder_license_email_reminders',
            'pdf_builder_license_test_mode_enabled'
        ];

        foreach ($license_keys as $license_key) {
            if (isset($settings[$license_key])) {
                // Sauvegarder dans une ligne séparée
                pdf_builder_update_option($license_key, $settings[$license_key]);
                error_log("[PDF Builder AJAX] Saved license key to separate row: {$license_key}");
                
                // Supprimer du tableau unifié
                unset($settings[$license_key]);
                $saved_count++;
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
            'canvas-debug-form' => 'pdf_builder_canvas_debug_',
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
            'license_test_mode' => 'pdf_builder_license_test_mode_enabled',
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
            'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
            'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
            'company_vat' => sanitize_text_field($_POST['company_vat'] ?? ''),
            'company_rcs' => sanitize_text_field($_POST['company_rcs'] ?? ''),
            'company_capital' => sanitize_text_field($_POST['company_capital'] ?? ''),
        ];

        error_log("PDF Builder: Saving general settings - " . json_encode($settings));

        foreach ($settings as $key => $value) {
            $option_name = 'pdf_builder_' . $key;
            $result = pdf_builder_update_option($option_name, $value);
            error_log("PDF Builder: Saved general setting - {$option_name} = '{$value}' (result: " . ($result ? 'SUCCESS' : 'FAILED') . ")");
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
            pdf_builder_update_option('pdf_builder_' . $key, $value);
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
        ];

        foreach ($settings as $key => $value) {
            pdf_builder_update_option('pdf_builder_' . $key, $value);
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
            pdf_builder_update_option('pdf_builder_' . $key, $value);
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
            pdf_builder_update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres d'accès
     */
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
            pdf_builder_update_option('pdf_builder_' . $key, $value);
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
            pdf_builder_update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres contenu
     */
    private function save_content_settings() {
        $saved_count = 0;

        // Traiter tous les paramètres canvas qui commencent par pdf_builder_canvas_
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pdf_builder_canvas_') === 0) {
                error_log("[PHP SAVE] Processing canvas setting: {$key} = {$value}");

                // Sanitiser selon le type de paramètre
                if (strpos($key, '_color') !== false || strpos($key, '_bg_color') !== false || strpos($key, '_border_color') !== false) {
                    // Couleurs - utiliser sanitize_hex_color
                    $sanitized_value = sanitize_hex_color($value);
                } elseif (strpos($key, '_enabled') !== false || strpos($key, '_activated') !== false ||
                         strpos($key, '_visible') !== false || strpos($key, '_active') !== false) {
                    // Booléens - convertir en '1' ou '0'
                    $sanitized_value = in_array(strtolower($value), ['1', 'on', 'true', 'yes']) ? '1' : '0';
                } elseif (is_numeric($value)) {
                    // Valeurs numériques
                    $sanitized_value = strpos($key, '_size') !== false ? intval($value) : (strpos($key, '_zoom') !== false ? floatval($value) : sanitize_text_field($value));
                } else {
                    // Texte par défaut
                    $sanitized_value = sanitize_text_field($value);
                }

                update_option($key, $sanitized_value);
                \wp_cache_delete('alloptions', 'options'); // Invalider le cache des options
                $saved_count++;

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("PDF Builder: Saved canvas setting {$key} = {$sanitized_value}");
                }
            }
        }

        error_log("[PHP SAVE] ===== COMPLETED: saved {$saved_count} settings =====");

        // Paramètres de contenu généraux (si présents)
        $general_settings = [
            'canvas_max_size' => intval($_POST['canvas_max_size'] ?? 0),
            'canvas_dpi' => intval($_POST['canvas_dpi'] ?? 0),
            'canvas_format' => sanitize_text_field($_POST['canvas_format'] ?? ''),
            'canvas_quality' => intval($_POST['canvas_quality'] ?? 0),
            'template_library_enabled' => isset($_POST['template_library_enabled']) ? '1' : '0',
            'default_template' => sanitize_text_field($_POST['default_template'] ?? 'blank'),
        ];

        foreach ($general_settings as $key => $value) {
            if (!empty($value) || $value === 0 || $value === '0') {
                pdf_builder_update_option('pdf_builder_' . $key, $value);
                $saved_count++;
            }
        }

        return $saved_count;
    }

    /**
     * Sauvegarde des paramètres développeur
     */
    private function save_developer_settings() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // error_log('PDF Builder: Starting save_developer_settings');
            // error_log('PDF Builder: POST data keys: ' . implode(', ', array_keys($_POST)));
        }

        $settings = [
            'pdf_builder_developer_enabled' => isset($_POST['pdf_builder_developer_enabled']) ? '1' : '0',
            'pdf_builder_developer_password' => sanitize_text_field($_POST['pdf_builder_developer_password'] ?? ''),
            'pdf_builder_license_test_mode_enabled' => isset($_POST['license_test_mode']) ? '1' : '0',
            'pdf_builder_license_test_key' => sanitize_text_field($_POST['pdf_builder_license_test_key'] ?? ''),
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // error_log('PDF Builder: Settings to save: ' . json_encode($settings));
        }

        foreach ($settings as $key => $value) {
            update_option($key, $value);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // error_log('PDF Builder: Saved option ' . $key . ' = ' . $value);
            }
        }

        return count($settings);
    }

    /**
     * Sauvegarde des paramètres licence
     */
    private function save_license_settings() {
        // Notifications removed from the license settings — ensure any old option is deleted
        \delete_option('pdf_builder_license_enable_notifications');

        // Paramètres de rappel par email - maintenant gérés par WordPress standard
        // Ces paramètres sont sauvegardés automatiquement via le formulaire WordPress
        // Ils sont dans $_POST['pdf_builder_settings']['pdf_builder_license_email_reminders']
        // et $_POST['pdf_builder_settings']['pdf_builder_license_reminder_email']

        // La fonction ne traite plus ces paramètres car ils sont gérés par WordPress
        return 0;
    }

    /**
     * Sauvegarde des paramètres templates
     */
    private function save_templates_settings() {
        $order_status_templates = isset($_POST['order_status_templates']) ? $_POST['order_status_templates'] : [];
        pdf_builder_update_option('pdf_builder_order_status_templates', $order_status_templates);
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

            \wp_cache_set($test_key, $test_value, 'pdf_builder', 300);
            $retrieved_value = wp_cache_get($test_key, 'pdf_builder');

            $cache_wp_ok = ($retrieved_value === $test_value);

            // Nettoyer le test
            \wp_cache_delete($test_key, 'pdf_builder');

            // Test 2: Vérifier les transients
            $transient_key = 'pdf_builder_test_transient';
            $transient_value = 'transient_test_' . rand(1000, 9999);

            set_transient($transient_key, $transient_value, 300);
            $transient_retrieved = get_transient($transient_key);

            $transient_ok = ($transient_retrieved === $transient_value);

            // Nettoyer
            delete_transient($transient_key);

            // Test 3: Vérifier les options
            $option_test = pdf_builder_get_option('pdf_builder_settings', array());
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

        \wp_cache_flush();
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
            $current_time = \current_time('mysql');
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $settings['pdf_builder_last_maintenance'] = $current_time;
            pdf_builder_update_option('pdf_builder_settings', $settings);

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
            $current_time = \current_time('mysql');
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $settings['pdf_builder_last_maintenance'] = $current_time;
            pdf_builder_update_option('pdf_builder_settings', $settings);

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
            $all_templates = pdf_builder_get_option('pdf_builder_templates', []);
            if (!is_array($all_templates)) {
                pdf_builder_update_option('pdf_builder_templates', []);
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
            $current_time = \current_time('mysql');
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $settings['pdf_builder_last_maintenance'] = $current_time;
            pdf_builder_update_option('pdf_builder_settings', $settings);

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
            $current_state = pdf_builder_get_option('pdf_builder_auto_maintenance', '1');
            $new_state = $current_state === '1' ? '0' : '1';

            // Mettre à jour dans le tableau unifié des paramètres
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $settings['pdf_builder_systeme_auto_maintenance'] = $new_state;
            pdf_builder_update_option('pdf_builder_settings', $settings);

            // Garder aussi l'option individuelle pour compatibilité
            pdf_builder_update_option('pdf_builder_auto_maintenance', $new_state);

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

            pdf_builder_update_option('pdf_builder_next_maintenance', $next_sunday);

            // Mettre à jour dans le tableau unifié des paramètres
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $settings['pdf_builder_next_maintenance'] = date('Y-m-d H:i:s', $next_sunday);
            pdf_builder_update_option('pdf_builder_settings', $settings);

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

        try {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Récupérer le statut de licence actuel
            $license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
            $license_key = pdf_builder_get_option('pdf_builder_license_key', '');
            $test_mode = pdf_builder_get_option('pdf_builder_license_test_mode_enabled', '0');

            // Simuler un test de licence (à implémenter selon vos besoins)
            $test_result = 'valid'; // ou 'invalid', 'expired', etc.

            // Si en mode test, vérifier la clé de test
            if ($test_mode === '1') {
                $test_key = pdf_builder_get_option('pdf_builder_license_test_key', '');
                $test_expires = pdf_builder_get_option('pdf_builder_license_test_key_expires', '');
                
                if (empty($test_key)) {
                    $test_result = 'no_test_key';
                } elseif (!empty($test_expires) && strtotime($test_expires) < time()) {
                    $test_result = 'test_expired';
                } else {
                    $test_result = 'test_valid';
                }
            }

            wp_send_json_success([
                'license_status' => $license_status,
                'license_key' => !empty($license_key) ? substr($license_key, 0, 8) . '...' : '',
                'test_mode' => $test_mode,
                'test_result' => $test_result,
                'tested_at' => \current_time('mysql')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du test de licence: ' . $e->getMessage()]);
        }
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
            'timestamp' => \current_time('mysql'),
            'user_id' => get_current_user_id()
        ]);
    }

    /**
     * Handler pour créer une sauvegarde
     */
    public function handle_create_backup() {
        // Debug: Log que le handler est appelé
        // error_log('[PDF Builder] handle_create_backup called');

        if (!$this->nonce_manager->validate_ajax_request()) {
            // error_log('[PDF Builder] Nonce validation failed for create_backup');
            return;
        }

        if (!current_user_can('manage_options')) {
            // error_log('[PDF Builder] User does not have manage_options capability');
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // error_log('[PDF Builder] Creating backup manager instance');
            $backup_manager = \PDF_Builder\Managers\PDF_Builder_Backup_Restore_Manager::getInstance();

            $options = [
                'compress' => isset($_POST['compress']) && $_POST['compress'] === '1',
                'exclude_templates' => isset($_POST['exclude_templates']) && $_POST['exclude_templates'] === '1',
                'exclude_settings' => isset($_POST['exclude_settings']) && $_POST['exclude_settings'] === '1',
                'exclude_user_data' => isset($_POST['exclude_user_data']) && $_POST['exclude_user_data'] === '1'
            ];

            // error_log('[PDF Builder] Calling createBackup with options: ' . json_encode($options));
            $result = $backup_manager->createBackup($options);
            // error_log('[PDF Builder] createBackup result: ' . json_encode($result));

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'filename' => $result['filename'],
                    'size_human' => \size_format($result['size'])
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            // error_log('[PDF Builder AJAX] Erreur création sauvegarde: ' . $e->getMessage());
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
            $backup_manager = \PDF_Builder\Managers\PDF_Builder_Backup_Restore_Manager::getInstance();
            $backups = $backup_manager->listBackups();

            wp_send_json_success(['backups' => $backups]);

        } catch (Exception $e) {
            // error_log('[PDF Builder AJAX] Erreur listage sauvegardes: ' . $e->getMessage());
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
            $backup_manager = \PDF_Builder\Managers\PDF_Builder_Backup_Restore_Manager::getInstance();

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
            // error_log('[PDF Builder AJAX] Erreur restauration sauvegarde: ' . $e->getMessage());
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
            $backup_manager = \PDF_Builder\Managers\PDF_Builder_Backup_Restore_Manager::getInstance();
            $result = $backup_manager->deleteBackup($filename);

            if ($result['success']) {
                wp_send_json_success(['message' => $result['message']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            // error_log('[PDF Builder AJAX] Erreur suppression sauvegarde: ' . $e->getMessage());
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

             // Vérifier que les headers n'ont pas encore été envoyés
             if (headers_sent()) {
                 wp_send_json_error(['message' => __('Impossible d\'envoyer les headers - sortie déjà commencée.', 'pdf-builder-pro')]);
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
             // error_log('[PDF Builder AJAX] Erreur téléchargement sauvegarde: ' . $e->getMessage());
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
             $current_mode = pdf_builder_get_option('pdf_builder_license_test_mode_enabled', '0');
             $new_mode = $current_mode === '1' ? '0' : '1';

             $response_data = [
                 'message' => 'Mode test ' . ($new_mode === '1' ? 'activé' : 'désactivé') . ' avec succès.',
                 'test_mode' => $new_mode
             ];

             // Sauvegarder le nouveau mode
             pdf_builder_update_option('pdf_builder_license_test_mode_enabled', $new_mode);

             // Si on active le mode test, générer automatiquement une clé de test si elle n'existe pas
             if ($new_mode === '1') {
                 $existing_test_key = pdf_builder_get_option('pdf_builder_license_test_key', '');
                 if (empty($existing_test_key)) {
                     // Générer une nouvelle clé de test
                     $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(wp_rand(), true)), 0, 16));
                     $expires_in_30_days = date('Y-m-d', strtotime('+30 days'));

                     pdf_builder_update_option('pdf_builder_license_test_key', $test_key);
                     pdf_builder_update_option('pdf_builder_license_test_key_expires', $expires_in_30_days);
                     pdf_builder_update_option('pdf_builder_license_status', 'active');

                     $response_data['test_key'] = $test_key;
                     $response_data['expires'] = $expires_in_30_days;
                     $response_data['message'] .= ' Clé de test générée automatiquement.';
                 } else {
                     // Retourner la clé existante
                     $response_data['test_key'] = $existing_test_key;
                     $response_data['expires'] = pdf_builder_get_option('pdf_builder_license_test_key_expires', '');
                 }
             } else {
                 // Si on désactive le mode test, on peut choisir de garder ou supprimer la clé
                 // Pour l'instant, on la garde pour permettre de la réactiver facilement
             }

             wp_send_json_success($response_data);

         } catch (Exception $e) {
             // error_log('[PDF Builder AJAX] Erreur toggle test mode: ' . $e->getMessage());
             wp_send_json_error(['message' => 'Erreur interne du serveur']);
         }
     }

     /**
      * Handler pour générer une clé de licence de test
      */
     public function handle_generate_test_license_key() {
         // Debug: Log the request
         error_log('PDF Builder - Generate test license key called');
         error_log('POST data: ' . print_r($_POST, true));

         if (!$this->nonce_manager->validate_ajax_request()) {
             error_log('PDF Builder - Nonce validation failed');
             return;
         }

         error_log('PDF Builder - Nonce validation passed');

         try {
            $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(wp_rand(), true)), 0, 16));
            $expires_in_30_days = date('Y-m-d', strtotime('+30 days'));

            // Sauvegarder individuellement
            pdf_builder_update_option('pdf_builder_license_test_key', $test_key);
            pdf_builder_update_option('pdf_builder_license_test_key_expires', $expires_in_30_days);
            pdf_builder_update_option('pdf_builder_license_status', 'active'); // Mettre le statut à "active" quand une clé de test est générée

            error_log('PDF Builder - Test license key generated: ' . $test_key);

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
            // Supprimer les clés de test individuellement
            pdf_builder_delete_option('pdf_builder_license_test_key');
            pdf_builder_delete_option('pdf_builder_license_test_key_expires');
            pdf_builder_update_option('pdf_builder_license_test_mode_enabled', '0');
            
            // Si le statut était 'active' (licence de test), remettre à 'free' en supprimant la clé de test
            $current_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
            if ($current_status === 'active' && !pdf_builder_get_option('pdf_builder_license_key', '')) {
                pdf_builder_update_option('pdf_builder_license_status', 'free');
            }

             wp_send_json_success([
                 'message' => 'Clé de test supprimée avec succès.'
             ]);

         } catch (Exception $e) {
             // error_log('[PDF Builder AJAX] Erreur suppression clé test: ' . $e->getMessage());
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
             error_log('[PDF Builder] Starting license cleanup');

             // Liste des options de licence à supprimer
             $license_options = [
                 'pdf_builder_license_key',
                 'pdf_builder_license_status',
                 'pdf_builder_license_expires',
                 'pdf_builder_license_data',
                 'pdf_builder_license_activated_at',
                 'pdf_builder_license_email_reminders',
                 'pdf_builder_license_reminder_email',
                 // Toujours supprimer la clé de test et le mode test lors du nettoyage complet
                 'pdf_builder_license_test_key',
                 'pdf_builder_license_test_key_expires',
                 'pdf_builder_license_test_mode_enabled'
             ];

             // Supprimer chaque option individuellement
             foreach ($license_options as $option) {
                 pdf_builder_delete_option($option);
                 error_log('[PDF Builder] Deleted option: ' . $option);
             }

             // Désactiver le mode test dans les paramètres généraux
             $settings = pdf_builder_get_option('pdf_builder_settings', []);
             if (isset($settings['pdf_builder_license_test_mode_enabled'])) {
                 $settings['pdf_builder_license_test_mode_enabled'] = '0';
                 pdf_builder_update_option('pdf_builder_settings', $settings);
                 error_log('[PDF Builder] Disabled test mode in settings');
             }

             // Définir le statut de licence à 'free'
             pdf_builder_update_option('pdf_builder_license_status', 'free');

             // Clear license transients
             global $wpdb;
             $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_license_%'");
             $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_license_%'");
             error_log('[PDF Builder] Cleared license transients');

             wp_send_json_success([
                 'message' => 'Licence complètement nettoyée. Le plugin est maintenant en mode gratuit.',
                 'reset_complete' => true
             ]);

         } catch (Exception $e) {
             // error_log('[PDF Builder AJAX] Erreur nettoyage licence: ' . $e->getMessage());
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
                 \get_temp_dir() . '/pdf-builder/'
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
                 'message' => "Fichiers temporaires nettoyés: $cleared_files fichier(s) supprimé(s), " . \size_format($total_size) . ' libéré(s).'
             ]);

         } catch (Exception $e) {
             // error_log('[PDF Builder AJAX] Erreur nettoyage temp: ' . $e->getMessage());
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
             $url = \admin_url($route);
             $response = \wp_remote_head($url, ['timeout' => 5]);

             if (\is_wp_error($response)) {
                 $error_message = is_object($response) && method_exists($response, 'get_error_message') ? $response->get_error_message() : 'Unknown error';
                 $failed_routes[] = $route . ' (' . $error_message . ')';
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
             // error_log('[PDF Builder AJAX] Erreur actualisation logs: ' . $e->getMessage());
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
             // error_log('[PDF Builder AJAX] Erreur nettoyage logs: ' . $e->getMessage());
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
             // error_log('[PDF Builder AJAX] Erreur génération nonce: ' . $e->getMessage());
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
                     'version' => pdf_builder_get_option('pdf_builder_version', 'Unknown'),
                     'cache_enabled' => pdf_builder_get_option('pdf_builder_cache_enabled', '0') === '1',
                     'developer_mode' => pdf_builder_get_option('pdf_builder_developer_enabled', '0') === '1',
                     'license_status' => pdf_builder_get_option('pdf_builder_license_status', 'inactive')
                 ]
             ];

             wp_send_json_success([
                 'message' => 'Informations système récupérées avec succès.',
                 'system_info' => $system_info
             ]);

         } catch (Exception $e) {
             // error_log('[PDF Builder AJAX] Erreur récupération info système: ' . $e->getMessage());
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
             // error_log('[PDF Builder AJAX] Erreur reset paramètres dev: ' . $e->getMessage());
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

    /**
     * Handler pour générer un PDF depuis le mode preview
     */
    public function handle_generate_pdf() {
        error_log("[PDF Builder] ========== GÉNÉRATION PDF DÉMARRÉE ==========");
        error_log("[PDF Builder] POST params: " . json_encode($_POST));
        
        // Vérifier les permissions - doit être connecté et avoir les droits de gestion WooCommerce
        if (!is_user_logged_in() || !current_user_can('edit_shop_orders')) {
            error_log("[PDF Builder] Permission refusée");
            wp_die('Permission refusée', '', ['response' => 403]);
        }

        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? 0);
        
        error_log("[PDF Builder] Template ID: '{$template_id}', Order ID: {$order_id}");

        if (!$template_id || !$order_id) {
            error_log("[PDF Builder] Paramètres manquants");
            wp_die('Paramètres manquants', '', ['response' => 400]);
        }

        try {
            // Vérifier que WooCommerce est actif
            if (!function_exists('wc_get_order')) {
                error_log("[PDF Builder] WooCommerce non actif");
                wp_die('WooCommerce n\'est pas actif', '', ['response' => 500]);
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                error_log("[PDF Builder] Commande #{$order_id} introuvable");
                wp_die('Commande introuvable', '', ['response' => 404]);
            }
            
            error_log("[PDF Builder] Commande #{$order_id} trouvée");

            // Récupérer le template
            $template = $this->get_template($template_id);
            if (!$template) {
                error_log("[PDF Builder] Template '{$template_id}' introuvable");
                wp_die('Modèle introuvable', '', ['response' => 404]);
            }
            
            error_log("[PDF Builder] Template '{$template_id}' trouvé: " . (isset($template['name']) ? $template['name'] : 'sans nom'));

            // Générer l'HTML avec les vraies données
            error_log("[PDF Builder] Début génération HTML pour PDF");
            $html = $this->generate_template_html($template, $order, 'pdf');
            error_log("[PDF Builder] HTML généré - Longueur: " . strlen($html) . " caractères");

            // Convertir en PDF avec dompdf
            require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
            
            $dompdf = new \Dompdf\Dompdf([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'fontHeightRatio' => 1.1,
                'isUnicode' => true,
                'enable_font_subsetting' => false,
                'defaultPaperSize' => 'A4'
            ]);
            
            // S'assurer que le HTML est en UTF-8 propre
            $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
            
            error_log("[PDF Builder] Chargement HTML dans dompdf");
            $dompdf->loadHtml($html);
            
            // Format papier depuis le template
            $template_data = json_decode($template['template_data'], true);
            $width = $template_data['canvasWidth'] ?? 595;
            $height = $template_data['canvasHeight'] ?? 842;
            $orientation = ($width > $height) ? 'landscape' : 'portrait';
            
            error_log("[PDF Builder] Format: {$width}x{$height}px, orientation: {$orientation}");
            $dompdf->setPaper([0, 0, $width, $height], $orientation);
            
            error_log("[PDF Builder] Rendu PDF");
            $dompdf->render();
            
            error_log("[PDF Builder] Envoi du PDF au navigateur");
            $dompdf->stream('facture-' . $order->get_order_number() . '.pdf', [
                'Attachment' => false  // false = afficher dans le navigateur, true = télécharger
            ]);
            exit;
        } catch (Exception $e) {
            error_log('[PDF Builder] Erreur génération PDF: ' . $e->getMessage());
            wp_die('Erreur: ' . $e->getMessage(), '', ['response' => 500]);
        }
    }

    /**
     * Génère une image (PNG/JPG) à partir d'un template et d'une commande
     * Fonctionnalité PREMIUM uniquement
     */
    public function handle_generate_image() {
        error_log("[PDF Builder] ========== GÉNÉRATION IMAGE DÉMARRÉE ==========");
        error_log("[PDF Builder] POST params: " . json_encode($_POST));
        
        // Vérifier les permissions
        if (!is_user_logged_in() || !current_user_can('edit_shop_orders')) {
            error_log("[PDF Builder] Permission refusée");
            wp_die('Permission refusée', '', ['response' => 403]);
        }

        // Vérifier le statut premium
        if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            if (!$license_manager->isPremium()) {
                error_log("[PDF Builder] Licence premium requise pour la génération d'images");
                wp_die('Cette fonctionnalité nécessite une licence premium', '', ['response' => 403]);
            }
        } else {
            error_log("[PDF Builder] License Manager non disponible");
            wp_die('Système de licence non disponible', '', ['response' => 500]);
        }

        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? 0);
        $format = sanitize_text_field($_POST['format'] ?? 'png');
        
        error_log("[PDF Builder] Template ID: '{$template_id}', Order ID: {$order_id}, Format: {$format}");

        if (!$template_id || !$order_id || !in_array($format, ['png', 'jpg'])) {
            error_log("[PDF Builder] Paramètres manquants ou invalides");
            wp_die('Paramètres manquants ou invalides', '', ['response' => 400]);
        }

        try {
            // Vérifier que WooCommerce est actif
            if (!function_exists('wc_get_order')) {
                error_log("[PDF Builder] WooCommerce non actif");
                wp_die('WooCommerce n\'est pas actif', '', ['response' => 500]);
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                error_log("[PDF Builder] Commande #{$order_id} introuvable");
                wp_die('Commande introuvable', '', ['response' => 404]);
            }
            
            error_log("[PDF Builder] Commande #{$order_id} trouvée");

            // Récupérer le template
            $template = $this->get_template($template_id);
            if (!$template) {
                error_log("[PDF Builder] Template '{$template_id}' introuvable");
                wp_die('Modèle introuvable', '', ['response' => 404]);
            }
            
            error_log("[PDF Builder] Template '{$template_id}' trouvé");

            // Générer l'HTML avec les vraies données
            error_log("[PDF Builder] Début génération HTML pour image");
            $html = $this->generate_template_html($template, $order);
            
            // Récupérer les dimensions du template
            $template_data = json_decode($template['template_data'], true);
            $width = $template_data['canvasWidth'] ?? 794;
            $height = $template_data['canvasHeight'] ?? 1123;
            
            error_log("[PDF Builder] Dimensions image: {$width}x{$height}px, format: {$format}");
            
            // Créer des fichiers temporaires
            $temp_dir = sys_get_temp_dir();
            $temp_image = $temp_dir . '/pdf-builder-' . uniqid() . '.' . $format;
            
            error_log("[PDF Builder] Fichier image : {$temp_image}");
            
            $conversion_success = false;
            $conversion_method = '';
            
            // Méthode Imagick: HTML → PDF → Image
            if (extension_loaded('imagick')) {
                try {
                    error_log("[PDF Builder] Tentative de génération avec Imagick (HTML → PDF → Image)");
                    
                    // Étape 1: Générer le PDF avec dompdf
                    require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
                    
                    $dompdf_options = new \Dompdf\Options();
                    $dompdf_options->set('isHtml5ParserEnabled', true);
                    $dompdf_options->set('isRemoteEnabled', true);
                    $dompdf_options->set('defaultFont', 'DejaVu Sans');
                    
                    $dompdf = new \Dompdf\Dompdf($dompdf_options);
                    $dompdf->setPaper([0, 0, $width * 0.75, $height * 0.75], 'portrait');
                    $dompdf->loadHtml($html);
                    $dompdf->render();
                    
                    // Sauvegarder le PDF temporaire
                    $temp_pdf = $temp_dir . '/pdf-builder-' . uniqid() . '.pdf';
                    file_put_contents($temp_pdf, $dompdf->output());
                    
                    error_log("[PDF Builder] PDF généré: " . filesize($temp_pdf) . " octets");
                    
                    // Étape 2: Convertir PDF → Image avec Imagick
                    $imagick = new \Imagick();
                    $imagick->setResolution(150, 150); // DPI pour meilleure qualité
                    $imagick->readImage($temp_pdf . '[0]'); // Lire première page
                    $imagick->setImageFormat($format === 'jpg' ? 'jpeg' : 'png');
                    
                    // Qualité pour JPG
                    if ($format === 'jpg') {
                        $imagick->setImageCompressionQuality(90);
                    }
                    
                    // Redimensionner si nécessaire pour correspondre aux dimensions demandées
                    $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                    
                    // Sauvegarder l'image
                    $imagick->writeImage($temp_image);
                    $imagick->clear();
                    $imagick->destroy();
                    
                    // Nettoyer le PDF temporaire
                    @unlink($temp_pdf);
                    
                    if (file_exists($temp_image) && filesize($temp_image) > 0) {
                        $conversion_success = true;
                        $conversion_method = 'Imagick (dompdf + ImageMagick)';
                        error_log("[PDF Builder] ✅ Génération réussie avec Imagick: " . filesize($temp_image) . " octets");
                    }
                } catch (\Exception $e) {
                    error_log("[PDF Builder] ⚠️ Imagick échoué: " . $e->getMessage());
                    error_log("[PDF Builder] Stack trace: " . $e->getTraceAsString());
                }
            } else {
                error_log("[PDF Builder] Extension Imagick non disponible");
            }
            
            // Méthode de fallback: Erreur si rien ne fonctionne
            if (!$conversion_success) {
                error_log("[PDF Builder] ❌ Aucune méthode de conversion disponible");
                
                wp_send_json_error([
                    'message' => "Extension Imagick non disponible",
                    'details' => "Pour activer la génération d'images PNG/JPG, installez l'extension PHP Imagick:\n\n" .
                                "Windows: Téléchargez la DLL depuis https://windows.php.net/downloads/pecl/releases/imagick/\n" .
                                "Linux: apt-get install php-imagick ou yum install php-imagick\n" .
                                "Redémarrez ensuite votre serveur web.",
                    'code' => 'NO_IMAGICK'
                ], 501);
            }
            
            error_log("[PDF Builder] Méthode utilisée: {$conversion_method}");
            
            // Lire le contenu de l'image
            $image_content = file_get_contents($temp_image);
            
            // Nettoyer le fichier temporaire
            @unlink($temp_image);
            
            // Envoyer l'image au navigateur
            $mime_type = ($format === 'png') ? 'image/png' : 'image/jpeg';
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="facture-' . $order->get_order_number() . '.' . $format . '"');
            header('Content-Length: ' . strlen($image_content));
            
            echo $image_content;
            exit;
        } catch (Exception $e) {
            error_log('[PDF Builder] Erreur génération image: ' . $e->getMessage());
            error_log('[PDF Builder] Stack trace: ' . $e->getTraceAsString());
            
            wp_send_json_error([
                'message' => 'Erreur lors de la génération de l\'image',
                'details' => $e->getMessage(),
                'code' => 'EXCEPTION'
            ], 500);
        }
    }

    /**
     * Handler pour obtenir le HTML de prévisualisation avec données de commande
     */
    public function handle_get_preview_html() {
        error_log("[PDF Builder] ========== GET PREVIEW HTML DÉMARRÉ ==========");
        
        // Vérifier les permissions
        if (!is_user_logged_in() || !current_user_can('edit_shop_orders')) {
            wp_send_json_error(['message' => 'Permission refusée'], 403);
            return;
        }

        // Vérifier le statut premium pour PNG/JPG
        if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            if (!$license_manager->isPremium()) {
                wp_send_json_error(['message' => 'Cette fonctionnalité nécessite une licence premium'], 403);
                return;
            }
        }

        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? 0);
        
        if (!$template_id || !$order_id) {
            wp_send_json_error(['message' => 'Paramètres manquants'], 400);
            return;
        }

        try {
            if (!function_exists('wc_get_order')) {
                wp_send_json_error(['message' => 'WooCommerce n\'est pas actif'], 500);
                return;
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error(['message' => 'Commande introuvable'], 404);
                return;
            }

            $template = $this->get_template($template_id);
            if (!$template) {
                wp_send_json_error(['message' => 'Modèle introuvable'], 404);
                return;
            }

            // Générer le HTML avec les vraies données
            $html = $this->generate_template_html($template, $order);
            
            // Extraire les dimensions du template
            $template_data = json_decode($template['template_data'], true);
            $width = $template_data['canvasWidth'] ?? 595;
            $height = $template_data['canvasHeight'] ?? 842;

            wp_send_json_success([
                'html' => $html,
                'width' => $width,
                'height' => $height,
                'order_number' => $order->get_order_number()
            ]);
        } catch (Exception $e) {
            error_log('[PDF Builder] Erreur get_preview_html: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'Erreur lors de la génération du HTML',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DEBUG: Affiche le HTML brut pour inspection
     */
    public function handle_debug_html() {
        // Pas de vérification de permission pour debug (à retirer en prod)
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? $_GET['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
        
        if (!$template_id || !$order_id) {
            die('Paramètres manquants: template_id=' . $template_id . ', order_id=' . $order_id);
        }

        try {
            if (!function_exists('wc_get_order')) {
                die('WooCommerce n\'est pas actif');
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                die('Commande introuvable: ' . $order_id);
            }

            $template = $this->get_template($template_id);
            if (!$template) {
                die('Template introuvable: ' . $template_id);
            }

            // Générer le HTML
            $html = $this->generate_template_html($template, $order);
            
            // Afficher directement le HTML
            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
            exit;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un template spécifique
     */
    private function get_template($template_id) {
        global $wpdb;
        
        // Les templates sont stockés dans la table wp_pdf_builder_templates
        $table_name = $wpdb->prefix . 'pdf_builder_templates';
        
        error_log("[PDF Builder] Recherche template ID '{$template_id}' dans table {$table_name}");
        
        $template = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $template_id
            ),
            ARRAY_A
        );
        
        if (!$template) {
            error_log("[PDF Builder] Template #{$template_id} introuvable dans la table");
            error_log("[PDF Builder] Utilisation du template fallback");
            return $this->get_fallback_template($template_id);
        }
        
        error_log("[PDF Builder] Template trouvé: " . ($template['name'] ?? 'sans nom'));
        error_log("[PDF Builder] Template data length: " . strlen($template['template_data'] ?? ''));
        error_log("[PDF Builder] Template JSON preview: " . substr($template['template_data'] ?? '', 0, 200));
        
        return $template;
    }

    /**
     * Génère un template de secours pour les tests
     */
    private function get_fallback_template($template_id) {
        $fallback_template = [
            'elements' => [
                [
                    'type' => 'text',
                    'x' => 50,
                    'y' => 50,
                    'width' => 200,
                    'height' => 40,
                    'content' => 'FACTURE',
                    'styles' => [
                        'fontSize' => 32,
                        'fontWeight' => 'bold',
                        'color' => '#0073aa'
                    ]
                ],
                [
                    'type' => 'customerInfo',
                    'x' => 50,
                    'y' => 120,
                    'width' => 250,
                    'height' => 120,
                    'styles' => [
                        'fontSize' => 14
                    ]
                ],
                [
                    'type' => 'table',
                    'x' => 50,
                    'y' => 280,
                    'width' => 495,
                    'height' => 200
                ]
            ],
            'canvas' => [
                'width' => 595,
                'height' => 842,
                'dpi' => 72,
                'orientation' => 'portrait'
            ]
        ];
        
        return [
            'id' => $template_id,
            'name' => 'Template Test (Fallback)',
            'template_data' => $fallback_template
        ];
    }

    /**
     * Génère l'HTML du template avec les vraies données de commande
     */
    private function generate_template_html($template, $order, $format = 'html') {
        // Récupérer l'état premium
        $is_premium = false;
        if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            $is_premium = $license_manager->isPremium();
        }

        // Utiliser l'OrderDataExtractor pour récupérer les données
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Generators/OrderDataExtractor.php';
        $data_extractor = new \PDF_Builder\Generators\OrderDataExtractor($order);
        $all_data = $data_extractor->get_all_data();

        // Récupérer les données du template
        $template_data = null;
        if (isset($template['template_data'])) {
            if (is_string($template['template_data'])) {
                $template_data = json_decode($template['template_data'], true);
            } else {
                $template_data = $template['template_data'];
            }
        }

        if (!$template_data || !isset($template_data['elements'])) {
            error_log("[PDF Builder] Template data invalide ou manquante");
            error_log("[PDF Builder] Template data keys: " . (is_array($template_data) ? implode(', ', array_keys($template_data)) : 'NOT ARRAY'));
            return $this->generate_fallback_html($template, $all_data);
        }

        $elements = $template_data['elements'];

        // Support de deux formats de canvas:
        // 1. Format avec objet canvas: {canvas: {width, height}, elements: []}
        // 2. Format plat: {canvasWidth, canvasHeight, elements: []}
        if (isset($template_data['canvas'])) {
            // Format avec objet canvas
            $canvas = $template_data['canvas'];
            $width = $canvas['width'] ?? 595;
            $height = $canvas['height'] ?? 842;
            $dpi = $canvas['dpi'] ?? 72;
            $orientation = $canvas['orientation'] ?? 'portrait';
        } else {
            // Format plat (par défaut)
            $width = $template_data['canvasWidth'] ?? 595;
            $height = $template_data['canvasHeight'] ?? 842;
            $dpi = $template_data['dpi'] ?? 72;
            $orientation = $template_data['orientation'] ?? 'portrait';
        }

        error_log("[PDF Builder] Canvas dimensions: {$width}x{$height} px");
        error_log("[PDF Builder] Nombre d'éléments à rendre: " . count($elements));

        // Début du HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . esc_html($template['name'] ?? 'Document') . '</title>
    <style>
        @page {
            margin: 0;
            size: ' . $width . 'px ' . $height . 'px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #ffffff;
            font-family: "DejaVu Sans", "Arial Unicode MS", sans-serif;
            margin: 0;
            padding: 0;
        }
        .pdf-canvas {
            position: relative;
            width: ' . $width . 'px;
            height: ' . $height . 'px;
            background: #ffffff;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .element {
            position: absolute;
            overflow: hidden;
            word-wrap: break-word;
            box-sizing: border-box;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="pdf-canvas">';

        // Générer chaque élément
        foreach ($elements as $element) {
            $html .= $this->render_element($element, $all_data, $is_premium, $format);
        }

        $html .= '
    </div>
</body>
</html>';

        error_log("[PDF Builder] HTML généré - Longueur totale: " . strlen($html) . " caractères");
        error_log("[PDF Builder] HTML preview (500 premiers caractères): " . substr($html, 0, 500));

        return $html;
    }

    /**
     * Génère le HTML d'un élément
     */
    private function render_element($element, $order_data, $is_premium = false, $format = 'html') {
        $type = $element['type'] ?? 'text';
        $element_id = $element['id'] ?? 'unknown';
        
        error_log("[PDF Builder] Rendu élément : type={$type}, id={$element_id}");
        
        // Vérifier si l'élément est visible
        if (isset($element['visible']) && $element['visible'] === false) {
            error_log("[PDF Builder] Élément {$element_id} non visible, ignoré");
            return '';
        }
        
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $width = $element['width'] ?? 100;
        $height = $element['height'] ?? 30;
        
        // Styles de base
        $styles = "left: {$x}px; top: {$y}px; width: {$width}px; height: {$height}px;";
        
        // Appliquer les styles de l'élément
        $styles .= $this->build_element_styles($element);
        
        // Rendu spécifique par type
        $rendered = '';
        switch ($type) {
            case 'product_table':
                $rendered = $this->render_product_table($element, $order_data, $styles);
                break;
            case 'customer_info':
                $rendered = $this->render_customer_info_element($element, $order_data, $styles, $is_premium);
                break;
            case 'company_info':
                $rendered = $this->render_company_info_element($element, $order_data, $styles, $is_premium, $format);
                break;
            case 'company_logo':
                $rendered = $this->render_company_logo($element, $styles);
                break;
            case 'line':
                $rendered = $this->render_line($element, $styles);
                break;
            case 'document_type':
                $rendered = $this->render_document_type($element, $styles);
                break;
            case 'order_number':
                $rendered = $this->render_order_number($element, $order_data, $styles);
                break;
            case 'woocommerce_order_date':
                $rendered = $this->render_order_date($element, $order_data, $styles);
                break;
            case 'woocommerce_invoice_number':
                $rendered = $this->render_invoice_number($element, $order_data, $styles);
                break;
            case 'dynamic_text':
                $rendered = $this->render_dynamic_text($element, $order_data, $styles);
                break;
            case 'mentions':
                $rendered = $this->render_mentions($element, $styles);
                break;
            case 'image':
                if (isset($element['src'])) {
                    $rendered = '<div class="element" style="' . $styles . '"><img src="' . esc_url($element['src']) . '" style="width: 100%; height: 100%; object-fit: contain;" /></div>';
                }
                break;
            case 'text':
            default:
                $content = $element['text'] ?? $element['content'] ?? '';
                // Appliquer white-space: pre-line pour que line-height fonctionne avec Dompdf
                $textStyle = $styles . '; white-space: pre-line;';
                $rendered = '<div class="element" style="' . $textStyle . '">' . esc_html($content) . '</div>';
                break;
        }
        
        error_log("[PDF Builder] Élément {$element_id} rendu: " . strlen($rendered) . " caractères");
        return $rendered;
    }
    
    /**
     * Construit les styles CSS d'un élément
     */
    private function build_element_styles($element) {
        $styles = '';
        
        // Polices et texte
        if (isset($element['fontSize'])) {
            $styles .= " font-size: {$element['fontSize']}px;";
        }
        if (isset($element['fontFamily'])) {
            $styles .= " font-family: {$element['fontFamily']};";
        }
        if (isset($element['fontWeight'])) {
            $styles .= " font-weight: {$element['fontWeight']};";
        }
        if (isset($element['fontStyle'])) {
            $styles .= " font-style: {$element['fontStyle']};";
        }
        if (isset($element['textDecoration'])) {
            $styles .= " text-decoration: {$element['textDecoration']};";
        }
        if (isset($element['textTransform'])) {
            $styles .= " text-transform: {$element['textTransform']};";
        }
        if (isset($element['letterSpacing']) && $element['letterSpacing'] !== 'normal') {
            $letterSpacingValue = floatval($element['letterSpacing']) . 'px';
            $styles .= " letter-spacing: {$letterSpacingValue};";
        }
        if (isset($element['wordSpacing']) && $element['wordSpacing'] !== 'normal') {
            $styles .= " word-spacing: {$element['wordSpacing']};";
        }
        if (isset($element['lineHeight'])) {
            $lineHeightValue = floatval($element['lineHeight']);
            $styles .= " line-height: {$lineHeightValue};";
        }
        if (isset($element['textAlign'])) {
            $styles .= " text-align: {$element['textAlign']};";
        }
        if (isset($element['verticalAlign'])) {
            $styles .= " vertical-align: {$element['verticalAlign']};";
        }
        
        // Couleurs
        if (isset($element['textColor'])) {
            $styles .= " color: {$element['textColor']};";
        }
        // Appliquer le fond seulement si showBackground est activé (ou non défini pour rétrocompatibilité)
        if (isset($element['backgroundColor']) && $element['backgroundColor'] !== 'transparent') {
            // Si showBackground est défini, on le respecte, sinon on affiche le fond pour rétrocompatibilité
            if (!isset($element['showBackground']) || $element['showBackground'] === true) {
                $styles .= " background-color: {$element['backgroundColor']};";
            }
        }
        
        // Bordures - Respecter showBorders (ou non défini pour rétrocompatibilité)
        if (isset($element['borderWidth']) && $element['borderWidth'] > 0) {
            // Si showBorders est défini, on le respecte, sinon on affiche les bordures pour rétrocompatibilité
            if (!isset($element['showBorders']) || $element['showBorders'] === true) {
                $border_color = $element['borderColor'] ?? '#000000';
                $border_style = $element['borderStyle'] ?? 'solid';
                $styles .= " border: {$element['borderWidth']}px {$border_style} {$border_color};";
            }
        }
        if (isset($element['borderRadius']) && $element['borderRadius'] > 0) {
            $styles .= " border-radius: {$element['borderRadius']}px;";
        }
        
        // Opacité
        if (isset($element['opacity'])) {
            $opacity = $element['opacity'];
            // Normaliser: si > 1, c'est un pourcentage (0-100), sinon c'est déjà 0-1
            if ($opacity > 1) {
                $opacity = $opacity / 100;
            }
            // Appliquer seulement si transparence < 100%
            if ($opacity < 1) {
                $styles .= " opacity: {$opacity};";
            }
            // Si opacity = 1, élément complètement opaque (pas de style nécessaire)
        }
        
        // Rotation
        if (isset($element['rotation']) && $element['rotation'] != 0) {
            $styles .= " transform: rotate({$element['rotation']}deg);";
        }
        
        // Ombre
        if (isset($element['shadowOffsetX']) || isset($element['shadowOffsetY']) || isset($element['shadowBlur'])) {
            $offsetX = $element['shadowOffsetX'] ?? 0;
            $offsetY = $element['shadowOffsetY'] ?? 0;
            $blur = $element['shadowBlur'] ?? 0;
            $color = $element['shadowColor'] ?? '#000000';
            if ($offsetX != 0 || $offsetY != 0 || $blur != 0) {
                $styles .= " box-shadow: {$offsetX}px {$offsetY}px {$blur}px {$color};";
            }
        }
        
        return $styles;
    }

    /**
     * Récupère le contenu d'un élément en fonction de son type
     */
    private function get_element_content($element, $order_data) {
        $type = $element['type'] ?? 'text';
        
        switch ($type) {
            case 'customerInfo':
                return $this->render_customer_info($order_data);
            case 'orderInfo':
                return $this->render_order_info($order_data);
            case 'invoiceNumber':
                return '<strong>Facture N° ' . esc_html($order_data['order']['order_number']) . '</strong>';
            case 'date':
                return esc_html($order_data['order']['date_formatted']);
            case 'companyInfo':
                // Info de l'entreprise depuis les settings
                return '<div style="line-height: 1.6;">' .
                       '<strong>' . esc_html(get_bloginfo('name')) . '</strong><br>' .
                       esc_html(get_option('woocommerce_store_address', '')) . '<br>' .
                       esc_html(get_option('woocommerce_store_city', '')) .
                       '</div>';
            case 'total':
                return '<strong>' . wc_price($order_data['totals']['total']) . '</strong>';
            case 'subtotal':
                return wc_price($order_data['totals']['subtotal']);
            case 'shipping':
                return wc_price($order_data['totals']['shipping']);
            case 'tax':
                return wc_price($order_data['totals']['tax']);
            case 'text':
            default:
                // Interpréter le contenu comme HTML si c'est du texte formaté
                $content = $element['content'] ?? '';
                // Remplacer les retours à la ligne par des <br>
                $content = nl2br(esc_html($content));
                return $content;
        }
    }

    /**
     * Rendu du tableau de produits WooCommerce
     */
    private function render_product_table($element, $order_data, $base_styles) {
        $html = '<div class="element" style="' . $base_styles . '">';
        
        // Récupérer tous les styles depuis le JSON de l'élément
        $show_borders = $element['showBorders'] ?? false;
        $border_color = $element['borderColor'] ?? '#e5e7eb';
        $border_width = $element['borderWidth'] ?? 1;
        $border_style = $show_borders ? "border: {$border_width}px solid {$border_color};" : 'border: none;';
        
        // Couleurs de fond
        $header_bg = $element['headerBackgroundColor'] ?? '#f9fafb';
        $alt_bg = $element['alternateRowColor'] ?? '#f9fafb';
        $bg_color = $element['backgroundColor'] ?? '#ffffff';
        
        // Couleurs de texte
        $header_color = $element['headerTextColor'] ?? '#111827';
        $row_color = $element['rowTextColor'] ?? '#374151';
        $total_color = $element['totalTextColor'] ?? '#111827';
        
        // Polices header
        $header_font_size = $element['headerFontSize'] ?? 12;
        $header_font_family = $element['headerFontFamily'] ?? 'Arial';
        $header_font_weight = $element['headerFontWeight'] ?? 'bold';
        $header_font_style = $element['headerFontStyle'] ?? 'normal';
        
        // Polices lignes
        $row_font_size = $element['rowFontSize'] ?? 11;
        $row_font_family = $element['rowFontFamily'] ?? 'Arial';
        $row_font_weight = $element['rowFontWeight'] ?? 'normal';
        $row_font_style = $element['rowFontStyle'] ?? 'normal';
        
        // Polices total
        $total_font_size = $element['totalFontSize'] ?? 12;
        $total_font_family = $element['totalFontFamily'] ?? 'Arial';
        $total_font_weight = $element['totalFontWeight'] ?? 'bold';
        $total_font_style = $element['totalFontStyle'] ?? 'normal';
        
        $html .= '<table style="width:100%; border-collapse: collapse; background-color: ' . $bg_color . ';">';
        
        // En-têtes
        if ($element['showHeaders'] ?? true) {
            $header_style = $border_style . " padding: 8px; background: {$header_bg}; color: {$header_color}; " .
                           "font-size: {$header_font_size}px; font-family: {$header_font_family}; " .
                           "font-weight: {$header_font_weight}; font-style: {$header_font_style};";
            
            $html .= '<thead><tr>';
            $html .= '<th style="' . $header_style . '">Produit</th>';
            $html .= '<th style="' . $header_style . ' text-align: center;">Qté</th>';
            $html .= '<th style="' . $header_style . ' text-align: right;">Prix</th>';
            $html .= '<th style="' . $header_style . ' text-align: right;">Total</th>';
            $html .= '</tr></thead>';
        }
        
        $html .= '<tbody>';
        $row_index = 0;
        
        $row_style_base = $border_style . " padding: 8px; color: {$row_color}; " .
                         "font-size: {$row_font_size}px; font-family: {$row_font_family}; " .
                         "font-weight: {$row_font_weight}; font-style: {$row_font_style};";
        
        // Produits
        foreach ($order_data['products'] as $product) {
            $bg = ($element['showAlternatingRows'] ?? true) && ($row_index % 2 === 1) ? $alt_bg : 'transparent';
            $html .= '<tr style="background: ' . $bg . ';">';
            $html .= '<td style="' . $row_style_base . '">' . esc_html($product['name']) . '</td>';
            $html .= '<td style="' . $row_style_base . ' text-align: center;">' . esc_html($product['quantity']) . '</td>';
            $html .= '<td style="' . $row_style_base . ' text-align: right;">' . $product['price'] . '</td>';
            $html .= '<td style="' . $row_style_base . ' text-align: right;">' . $product['total'] . '</td>';
            $html .= '</tr>';
            $row_index++;
        }
        
        // Frais de service (fees) - ajoutés comme lignes de produits
        if (isset($order_data['fees']) && !empty($order_data['fees'])) {
            foreach ($order_data['fees'] as $fee) {
                $bg = ($element['showAlternatingRows'] ?? true) && ($row_index % 2 === 1) ? $alt_bg : 'transparent';
                $html .= '<tr style="background: ' . $bg . ';">';
                $html .= '<td style="' . $row_style_base . '">' . esc_html($fee['name']) . '</td>';
                $html .= '<td style="' . $row_style_base . ' text-align: center;">1</td>';
                $html .= '<td style="' . $row_style_base . ' text-align: right;">' . $fee['total'] . '</td>';
                $html .= '<td style="' . $row_style_base . ' text-align: right;">' . $fee['total'] . '</td>';
                $html .= '</tr>';
                $row_index++;
            }
        }
        
        $html .= '</tbody></table>'; // Fermer le tableau des produits
        
        // Tableau des totaux séparé - aligné à droite
        $html .= '<div style="margin-top: 20px; width: 100%; display: table;">';
        $html .= '<table style="width: 50%; margin-left: auto; border-collapse: collapse;">';
        $html .= '<tbody>';
        
        // Style pour les lignes de summary (sous-total, remise, livraison, TVA)
        $summary_style = "text-align: right; padding: 6px 8px; " .
                        "font-size: {$row_font_size}px; font-family: {$row_font_family}; " .
                        "font-weight: {$row_font_weight}; color: {$row_color};";
        
        $summary_label_style = "text-align: left; padding: 6px 8px; " .
                               "font-size: {$row_font_size}px; font-family: {$row_font_family}; " .
                               "font-weight: {$row_font_weight}; color: {$row_color};";
        
        // Sous-total (avant remises et frais)
        if ($element['showSubtotal'] ?? true) {
            $html .= '<tr>';
            $html .= '<td style="' . $summary_label_style . '">Sous-total:</td>';
            $html .= '<td style="' . $summary_style . '">' . wc_price($order_data['totals']['subtotal_raw']) . '</td>';
            $html .= '</tr>';
        }
        
        // Frais de service (total des fees)
        if (isset($order_data['fees']) && !empty($order_data['fees'])) {
            $fees_total = 0;
            foreach ($order_data['fees'] as $fee) {
                $fees_total += floatval($fee['total_raw'] ?? 0);
            }
            if ($fees_total > 0) {
                $html .= '<tr>';
                $html .= '<td style="' . $summary_label_style . '">Frais:</td>';
                $html .= '<td style="' . $summary_style . '">' . wc_price($fees_total) . '</td>';
                $html .= '</tr>';
            }
        }
        
        // Réductions (si présentes)
        if (($element['showDiscount'] ?? true) && $order_data['totals']['discount_raw'] > 0) {
            $discount_style = str_replace($row_color, '#dc2626', $summary_style);
            $discount_label_style = str_replace($row_color, '#dc2626', $summary_label_style);
            $html .= '<tr>';
            $html .= '<td style="' . $discount_label_style . '">Remise:</td>';
            $html .= '<td style="' . $discount_style . '">-' . wc_price($order_data['totals']['discount_raw']) . '</td>';
            $html .= '</tr>';
        }
        
        // Frais de port
        if (($element['showShipping'] ?? true) && $order_data['totals']['shipping_raw'] > 0) {
            $html .= '<tr>';
            $html .= '<td style="' . $summary_label_style . '">Frais de port:</td>';
            $html .= '<td style="' . $summary_style . '">' . wc_price($order_data['totals']['shipping_raw']) . '</td>';
            $html .= '</tr>';
        }
        
        // TVA
        if (($element['showTax'] ?? true) && $order_data['totals']['tax_raw'] > 0) {
            $html .= '<tr>';
            $html .= '<td style="' . $summary_label_style . '">TVA (5.0%):</td>';
            $html .= '<td style="' . $summary_style . '">' . wc_price($order_data['totals']['tax_raw']) . '</td>';
            $html .= '</tr>';
        }
        
        // Total final avec séparateur
        $total_style = "text-align: right; padding: 10px 8px 6px 8px; " .
                      "font-size: {$total_font_size}px; font-family: {$total_font_family}; " .
                      "font-weight: {$total_font_weight}; font-style: {$total_font_style}; " .
                      "color: {$total_color}; border-top: 2px solid #333;";
        
        $total_label_style = "text-align: left; padding: 10px 8px 6px 8px; " .
                            "font-size: {$total_font_size}px; font-family: {$total_font_family}; " .
                            "font-weight: {$total_font_weight}; font-style: {$total_font_style}; " .
                            "color: {$total_color}; border-top: 2px solid #333;";
        
        $html .= '<tr>';
        $html .= '<td style="' . $total_label_style . '">TOTAL:</td>';
        $html .= '<td style="' . $total_style . '">' . wc_price($order_data['totals']['total_raw']) . '</td>';
        $html .= '</tr>';
        
        $html .= '</tbody></table>';
        $html .= '</div>'; // Fermer le conteneur des totaux
        
        $html .= '</div>'; // Fermer l'élément principal
        return $html;
    }
    
    /**
     * Rendu des informations client
     */
    private function render_customer_info_element($element, $order_data, $base_styles) {
        // Récupérer le padding horizontal et vertical (backward compatibility avec padding unique)
        $paddingHorizontal = isset($element['paddingHorizontal']) ? intval($element['paddingHorizontal']) : (isset($element['padding']) ? intval($element['padding']) : 12);
        $paddingVertical = isset($element['paddingVertical']) ? intval($element['paddingVertical']) : (isset($element['padding']) ? intval($element['padding']) : 12);
        
        // Récupérer les propriétés de disposition
        $layout = isset($element['layout']) ? $element['layout'] : 'vertical';
        $textAlign = isset($element['textAlign']) ? $element['textAlign'] : 'left';
        $verticalAlign = isset($element['verticalAlign']) ? $element['verticalAlign'] : 'top';
        $lineHeight = isset($element['lineHeight']) ? floatval($element['lineHeight']) : 1.5;
        $letterSpacing = isset($element['letterSpacing']) ? floatval($element['letterSpacing']) : 0;
        
        // Récupérer les options d'affichage
        $showHeaders = $element['showHeaders'] ?? true;
        $showBackground = $element['showBackground'] ?? true;
        $showBorders = $element['showBorders'] ?? true;
        $showFullName = $element['showFullName'] ?? true;
        
        // Récupérer les couleurs
        $headerTextColor = isset($element['headerTextColor']) ? $element['headerTextColor'] : '#111827';
        $textColor = isset($element['textColor']) ? $element['textColor'] : '#374151';
        $backgroundColor = isset($element['backgroundColor']) ? $element['backgroundColor'] : '#ffffff';
        $borderColor = isset($element['borderColor']) ? $element['borderColor'] : '#e5e7eb';
        
        // Récupérer les propriétés de police pour l'en-tête
        $headerFontFamily = isset($element['headerFontFamily']) ? $element['headerFontFamily'] : (isset($element['fontFamily']) ? $element['fontFamily'] : 'DejaVu Sans');
        $headerFontSize = isset($element['headerFontSize']) ? intval($element['headerFontSize']) : 14;
        $headerFontWeight = isset($element['headerFontWeight']) ? $element['headerFontWeight'] : 'bold';
        $headerFontStyle = isset($element['headerFontStyle']) ? $element['headerFontStyle'] : 'normal';
        
        // Récupérer les propriétés de police pour le corps
        $bodyFontFamily = isset($element['bodyFontFamily']) ? $element['bodyFontFamily'] : (isset($element['fontFamily']) ? $element['fontFamily'] : 'DejaVu Sans');
        $bodyFontSize = isset($element['bodyFontSize']) ? intval($element['bodyFontSize']) : 12;
        $bodyFontWeight = isset($element['bodyFontWeight']) ? $element['bodyFontWeight'] : 'normal';
        $bodyFontStyle = isset($element['bodyFontStyle']) ? $element['bodyFontStyle'] : 'normal';
        
        // Construire les lignes selon le layout (sans l'en-tête qui sera affiché séparément)
        $lines = [];
        
        if ($layout === 'vertical') {
            // Mode vertical : une info par ligne
            if ($showFullName) {
                $lines[] = '<strong>' . esc_html($order_data['customer']['full_name']) . '</strong>';
            }
            if ($element['showAddress'] ?? true) {
                $lines[] = esc_html($order_data['billing']['full_address']);
            }
            if ($element['showEmail'] ?? true) {
                $lines[] = esc_html($order_data['customer']['email']);
            }
            if (($element['showPhone'] ?? true) && !empty($order_data['customer']['phone'])) {
                $lines[] = esc_html($order_data['customer']['phone']);
            }
        } elseif ($layout === 'horizontal') {
            // Mode horizontal : plusieurs infos par ligne
            $line1 = '';
            $line2 = '';
            $line3 = '';
            
            if ($showFullName) {
                $line1 .= '<strong>' . esc_html($order_data['customer']['full_name']) . '</strong>';
            }
            if ($element['showEmail'] ?? true) {
                $line1 .= ($line1 ? ' | ' : '') . esc_html($order_data['customer']['email']);
            }
            if ($element['showAddress'] ?? true) {
                $line2 .= esc_html($order_data['billing']['full_address']);
            }
            if (($element['showPhone'] ?? true) && !empty($order_data['customer']['phone'])) {
                $line2 .= ($line2 ? ' | ' : '') . esc_html($order_data['customer']['phone']);
            }
            
            if ($line1) $lines[] = $line1;
            if ($line2) $lines[] = $line2;
            if ($line3) $lines[] = $line3;
        } elseif ($layout === 'compact') {
            // Mode compact : nom en premier, puis reste avec séparateurs
            if ($showFullName) {
                $lines[] = '<strong>' . esc_html($order_data['customer']['full_name']) . '</strong>';
            }
            
            $compactLine = '';
            if ($element['showAddress'] ?? true) {
                $compactLine .= esc_html($order_data['billing']['full_address']);  // Adresse complète
            }
            if ($element['showEmail'] ?? true) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($order_data['customer']['email']);
            }
            if (($element['showPhone'] ?? true) && !empty($order_data['customer']['phone'])) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($order_data['customer']['phone']);
            }
            
            if ($compactLine) $lines[] = $compactLine;
        }
        
        // Appliquer l'alignement horizontal et vertical
        $letterSpacingStyle = $letterSpacing !== 0 ? ' letter-spacing: ' . $letterSpacing . 'px;' : '';
        
        // Construire les styles du conteneur interne (utilise les propriétés bodyFont*)
        $inner_styles = 'padding: ' . $paddingVertical . 'px ' . $paddingHorizontal . 'px; text-align: ' . $textAlign . '; line-height: ' . $lineHeight . '; white-space: pre-line; color: ' . esc_attr($textColor) . '; font-family: ' . $bodyFontFamily . '; font-size: ' . $bodyFontSize . 'px; font-weight: ' . $bodyFontWeight . '; font-style: ' . $bodyFontStyle . ';' . $letterSpacingStyle;
        
        // Pour l'alignement vertical, on utilise flexbox
        if ($verticalAlign === 'middle') {
            $inner_styles .= ' display: flex; flex-direction: column; justify-content: center; height: 100%;';
        } elseif ($verticalAlign === 'bottom') {
            $inner_styles .= ' display: flex; flex-direction: column; justify-content: flex-end; height: 100%;';
        }
        
        $html = '<div class="element" style="' . $base_styles . '">';
        $html .= '<style>';
        // Style pour l'en-tête "Informations Client" (utilise headerFont*)
        $html .= '.customer-info-header { color: ' . $headerTextColor . '; font-family: ' . $headerFontFamily . '; font-size: ' . $headerFontSize . 'px; font-weight: ' . $headerFontWeight . '; font-style: ' . $headerFontStyle . '; line-height: 1.2; margin-bottom: 8px; }';
        // Style pour le nom du client (strong) utilise aussi headerFont* avec line-height 1.2
        $html .= 'strong { color: ' . $headerTextColor . '; font-family: ' . $headerFontFamily . '; font-size: ' . $headerFontSize . 'px; font-weight: ' . $headerFontWeight . '; font-style: ' . $headerFontStyle . '; line-height: 1.2; }';
        $html .= '</style>';
        $html .= '<div style="' . $inner_styles . '">';
        // Afficher l'en-tête SÉPARÉMENT, avant les lignes de contenu (comme dans React)
        if ($showHeaders) {
            $html .= '<div class="customer-info-header">Informations Client</div>';
        }
        $html .= implode("\n", $lines);
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Rendu des informations entreprise
     */
    private function render_company_info_element($element, $order_data, $base_styles, $is_premium = false, $format = 'html') {
        // Récupérer le padding horizontal et vertical (backward compatibility avec padding unique)
        $paddingHorizontal = isset($element['paddingHorizontal']) ? intval($element['paddingHorizontal']) : (isset($element['padding']) ? intval($element['padding']) : 12);
        $paddingVertical = isset($element['paddingVertical']) ? intval($element['paddingVertical']) : (isset($element['padding']) ? intval($element['padding']) : 12);
        
        // Récupérer les propriétés de disposition
        $layout = isset($element['layout']) ? $element['layout'] : 'vertical';
        $textAlign = isset($element['textAlign']) ? $element['textAlign'] : 'left';
        $verticalAlign = isset($element['verticalAlign']) ? $element['verticalAlign'] : 'top';
        $lineHeight = isset($element['lineHeight']) ? floatval($element['lineHeight']) : 1.1;
        $letterSpacing = isset($element['letterSpacing']) ? floatval($element['letterSpacing']) : 0;
        
        // ✅ NEW: Thèmes prédéfinis
        $companyThemes = [
            'corporate' => [
                'backgroundColor' => '#ffffff',
                'borderColor' => '#1f2937',
                'textColor' => '#374151',
                'headerTextColor' => '#111827',
            ],
            'modern' => [
                'backgroundColor' => '#ffffff',
                'borderColor' => '#3b82f6',
                'textColor' => '#1e40af',
                'headerTextColor' => '#1e3a8a',
            ],
            'elegant' => [
                'backgroundColor' => '#ffffff',
                'borderColor' => '#8b5cf6',
                'textColor' => '#6d28d9',
                'headerTextColor' => '#581c87',
            ],
            'minimal' => [
                'backgroundColor' => '#ffffff',
                'borderColor' => '#e5e7eb',
                'textColor' => '#374151',
                'headerTextColor' => '#111827',
            ],
            'professional' => [
                'backgroundColor' => '#ffffff',
                'borderColor' => '#059669',
                'textColor' => '#047857',
                'headerTextColor' => '#064e3b',
            ],
        ];
        
        // Récupérer le thème
        $themeName = isset($element['theme']) ? $element['theme'] : 'corporate';
        $theme = isset($companyThemes[$themeName]) ? $companyThemes[$themeName] : $companyThemes['corporate'];
        
        // Appliquer les couleurs du thème (en priorité sur les couleurs personnalisées)
        $backgroundColor = isset($element['backgroundColor']) && !empty($element['backgroundColor']) ? $element['backgroundColor'] : $theme['backgroundColor'];
        $borderColor = isset($element['borderColor']) && !empty($element['borderColor']) ? $element['borderColor'] : $theme['borderColor'];
        $textColor = isset($element['textColor']) && !empty($element['textColor']) ? $element['textColor'] : $theme['textColor'];
        $headerTextColor = isset($element['headerTextColor']) && !empty($element['headerTextColor']) ? $element['headerTextColor'] : $theme['headerTextColor'];
        
        // ✅ HELPER: Formater le numéro de téléphone (ajouter un point tous les 2 chiffres)
        $formatPhoneNumber = function($phone) {
            if (empty($phone)) return $phone;
            $cleaned = preg_replace('/\D/', '', $phone);
            $chunks = str_split($cleaned, 2);
            return implode('.', $chunks);
        };
        
        // Récupérer les données de l'entreprise depuis l'élément canvas (pas depuis les options WordPress)
        // Helper pour s'assurer qu'on a une string et pas un array
        $getString = function($value) {
            if (is_array($value)) {
                return isset($value[0]) ? (string)$value[0] : '';
            }
            return isset($value) ? (string)$value : '';
        };
        
        $companyName = $getString($element['companyName'] ?? get_bloginfo('name'));
        $address = $getString($element['companyAddress'] ?? get_option('woocommerce_store_address', ''));
        $city = $getString($element['companyCity'] ?? get_option('woocommerce_store_city', ''));
        $postcode = $getString($element['companyPostcode'] ?? get_option('woocommerce_store_postcode', ''));
        $email = $getString($element['companyEmail'] ?? get_option('admin_email', ''));
        $phone = $getString($element['companyPhone'] ?? get_option('woocommerce_store_phone', ''));
        $siret = $getString($element['companySiret'] ?? get_option('pdf_builder_company_siret', ''));
        $rcs = $getString($element['companyRcs'] ?? get_option('pdf_builder_company_rcs', ''));
        $tva = $getString($element['companyTva'] ?? get_option('pdf_builder_company_vat', ''));
        $capital = $getString($element['companyCapital'] ?? get_option('pdf_builder_company_capital', ''));
        
        // Ajouter le symbole € au capital s'il n'est pas déjà présent
        if (!empty($capital) && strpos($capital, '€') === false) {
            $capital .= ' €';
        }
        
        // ✅ NEW: Formater le téléphone
        $phone = $formatPhoneNumber($phone);
        
        // Construire les lignes selon le layout
        $lines = [];
        
        if ($layout === 'vertical') {
            // Mode vertical : une info par ligne (ordre identique au React)
            if ($element['showCompanyName'] ?? true) {
                $lines[] = '<strong>' . esc_html($companyName) . '</strong>';
            }
            // Afficher adresse puis ville séparément (comme React) pour éviter duplication
            if (($element['showAddress'] ?? true) && $address) {
                $lines[] = esc_html($address);
                // Afficher la ville sur une ligne séparée si elle existe
                if ($city) {
                    $lines[] = esc_html($city);
                }
            }
            if (($element['showSiret'] ?? true) && $siret) {
                $lines[] = esc_html($siret);
            }
            if (($element['showVat'] ?? true) && $tva) {
                $lines[] = esc_html($tva);
            }
            if (($element['showRcs'] ?? true) && $rcs) {
                $lines[] = esc_html($rcs);
            }
            if (($element['showCapital'] ?? true) && $capital) {
                $lines[] = esc_html($capital);
            }
            if (($element['showEmail'] ?? true) && $email) {
                $lines[] = esc_html($email);
            }
            if (($element['showPhone'] ?? true) && $phone) {
                $lines[] = esc_html($phone);
            }
        } elseif ($layout === 'horizontal') {
            // Mode horizontal : plusieurs infos par ligne, groupées logiquement
            if ($element['showCompanyName'] ?? true) {
                $lines[] = '<strong>' . esc_html($companyName) . '</strong>';
            }
            
            // Ligne 1: Adresse + Ville (si elle existe)
            $line1 = '';
            if (($element['showAddress'] ?? true) && $address) {
                $line1 .= esc_html($address);
                if ($city) {
                    $line1 .= ', ' . esc_html($city);
                }
            }
            if ($line1) $lines[] = $line1;
            
            // Ligne 2: Email + Phone
            $line2 = '';
            if (($element['showEmail'] ?? true) && $email) {
                $line2 .= esc_html($email);
            }
            if (($element['showPhone'] ?? true) && $phone) {
                $line2 .= ($line2 ? ' | ' : '') . esc_html($phone);
            }
            if ($line2) $lines[] = $line2;
            
            // Ligne 3: Infos légales (SIRET | RCS | TVA | Capital)
            $line3 = '';
            if (($element['showSiret'] ?? true) && $siret) {
                $line3 .= esc_html($siret);
            }
            if (($element['showRcs'] ?? true) && $rcs) {
                $line3 .= ($line3 ? ' | ' : '') . esc_html($rcs);
            }
            if (($element['showVat'] ?? true) && $tva) {
                $line3 .= ($line3 ? ' | ' : '') . esc_html($tva);
            }
            if (($element['showCapital'] ?? true) && $capital) {
                $line3 .= ($line3 ? ' | ' : '') . esc_html($capital);
            }
            if ($line3) $lines[] = $line3;
        } elseif ($layout === 'compact') {
            // Mode compact : nom en en-tête + reste avec séparateurs (ordre identique au React)
            if ($element['showCompanyName'] ?? true) {
                $lines[] = '<strong>' . esc_html($companyName) . '</strong>';
            }
            
            $compactLine = '';
            if (($element['showAddress'] ?? true) && $address) {
                $compactLine .= esc_html($address);
            }
            if (($element['showEmail'] ?? true) && $email) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($email);
            }
            if (($element['showPhone'] ?? true) && $phone) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($phone);
            }
            if (($element['showSiret'] ?? true) && $siret) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($siret);
            }
            if (($element['showVat'] ?? true) && $tva) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($tva);
            }
            if (($element['showRcs'] ?? true) && $rcs) {
                $compactLine .= ($compactLine ? ' • ' : '') . esc_html($rcs);
            }
            
            if ($compactLine) $lines[] = $compactLine;
        }
        
        // Appliquer l'alignement horizontal et vertical
        $letterSpacingStyle = $letterSpacing !== 0 ? ' letter-spacing: ' . $letterSpacing . 'px;' : '';
        
        // ✅ NEW: Ajouter couleurs du thème et polices
        $headerFontFamily = isset($element['headerFontFamily']) ? $element['headerFontFamily'] : (isset($element['fontFamily']) ? $element['fontFamily'] : 'DejaVu Sans');
        $headerFontSize = isset($element['headerFontSize']) ? intval($element['headerFontSize']) : 14;
        $headerFontWeight = isset($element['headerFontWeight']) ? $element['headerFontWeight'] : 'bold';
        $headerFontStyle = isset($element['headerFontStyle']) ? $element['headerFontStyle'] : 'normal';
        
        $bodyFontFamily = isset($element['bodyFontFamily']) ? $element['bodyFontFamily'] : (isset($element['fontFamily']) ? $element['fontFamily'] : 'DejaVu Sans');
        $bodyFontSize = isset($element['bodyFontSize']) ? intval($element['bodyFontSize']) : 12;
        $bodyFontWeight = isset($element['bodyFontWeight']) ? $element['bodyFontWeight'] : 'normal';
        $bodyFontStyle = isset($element['bodyFontStyle']) ? $element['bodyFontStyle'] : 'normal';
        
        $inner_styles = 'padding: ' . $paddingVertical . 'px ' . $paddingHorizontal . 'px; text-align: ' . $textAlign . '; line-height: ' . $lineHeight . '; white-space: pre-line; color: ' . $textColor . '; font-family: ' . $bodyFontFamily . '; font-size: ' . $bodyFontSize . 'px; font-weight: ' . $bodyFontWeight . '; font-style: ' . $bodyFontStyle . ';' . $letterSpacingStyle;
        
        // Pour l'alignement vertical, on utilise flexbox
        if ($verticalAlign === 'middle') {
            $inner_styles .= ' display: flex; flex-direction: column; justify-content: center; height: 100%;';
        } elseif ($verticalAlign === 'bottom') {
            $inner_styles .= ' display: flex; flex-direction: column; justify-content: flex-end; height: 100%;';
        }
        
        // Générer le HTML avec styles de police pour le header (strong)
        $html = '<div class="element" style="' . $base_styles . '">';
        $html .= '<style>';
        // ✅ Header (strong) utilise toujours line-height 1.2, pas le line-height personnalisé
        $html .= 'strong { color: ' . $headerTextColor . '; font-family: ' . $headerFontFamily . '; font-size: ' . $headerFontSize . 'px; font-weight: ' . $headerFontWeight . '; font-style: ' . $headerFontStyle . '; line-height: 1.2; }';
        $html .= '</style>';
        $html .= '<div style="' . $inner_styles . '">';
        // Plus besoin de html_entity_decode car htmlspecialchars préserve les caractères UTF-8
        $html .= implode("\n", $lines);
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Rendu du logo entreprise
     */
    private function render_company_logo($element, $base_styles) {
        $src = $element['src'] ?? '';
        if (!$src) return '';
        
        $object_fit = $element['objectFit'] ?? 'contain';
        
        // Convertir l'image en base64 pour compatibilité Dompdf
        $image_data = $this->get_image_as_base64($src);
        if ($image_data) {
            $src = $image_data;
        }
        
        return '<div class="element" style="' . $base_styles . '"><img src="' . $src . '" style="width: 100%; height: 100%; object-fit: ' . $object_fit . ';" /></div>';
    }
    
    /**
     * Convertit une image en base64 data URI
     */
    private function get_image_as_base64($url) {
        // Si c'est déjà en base64, retourner tel quel
        if (strpos($url, 'data:image') === 0) {
            return $url;
        }
        
        // Convertir les URLs WordPress relatives en absolues
        if (strpos($url, '/wp-content/') === 0) {
            $url = site_url($url);
        }
        
        try {
            // Télécharger l'image
            $response = wp_remote_get($url, [
                'timeout' => 10,
                'sslverify' => false // Important pour les sites en développement
            ]);
            
            if (is_wp_error($response)) {
                error_log('[PDF Builder] Erreur téléchargement image: ' . $response->get_error_message());
                return false;
            }
            
            $body = wp_remote_retrieve_body($response);
            $content_type = wp_remote_retrieve_header($response, 'content-type');
            
            if (empty($body)) {
                return false;
            }
            
            // Détecter le type MIME si non fourni
            if (empty($content_type)) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $content_type = $finfo->buffer($body);
            }
            
            // Convertir en base64
            $base64 = base64_encode($body);
            return "data:{$content_type};base64,{$base64}";
            
        } catch (Exception $e) {
            error_log('[PDF Builder] Exception lors conversion image: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rendu d'une ligne de séparation
     */
    private function render_line($element, $base_styles) {
        $color = $element['strokeColor'] ?? '#000000';
        $width = $element['strokeWidth'] ?? 1;
        return '<div class="element" style="' . $base_styles . ' border-top: ' . $width . 'px solid ' . $color . ';"></div>';
    }
    
    /**
     * Rendu du type de document
     */
    private function render_document_type($element, $base_styles) {
        $title = $element['title'] ?? 'FACTURE';
        return '<div class="element" style="' . $base_styles . ' display: flex; align-items: center; justify-content: center;">' .
               '<strong>' . esc_html($title) . '</strong></div>';
    }
    
    /**
     * Rendu du numéro de commande
     */
    private function render_order_number($element, $order_data, $base_styles) {
        $number = $order_data['order']['order_number'];
        $show_label = $element['showLabel'] ?? false;
        $label = $element['labelText'] ?? 'N° ';
        
        $content = $show_label ? $label . $number : 'N° ' . $number;
        return '<div class="element" style="' . $base_styles . '">' . esc_html($content) . '</div>';
    }
    
    /**
     * Rendu de la date de commande
     */
    private function render_order_date($element, $order_data, $base_styles) {
        return '<div class="element" style="' . $base_styles . '">' . esc_html($order_data['order']['date_formatted']) . '</div>';
    }
    
    /**
     * Rendu du numéro de facture
     */
    private function render_invoice_number($element, $order_data, $base_styles) {
        $invoice_number = 'INV-' . $order_data['order']['order_number'];
        return '<div class="element" style="' . $base_styles . '">' . esc_html($invoice_number) . '</div>';
    }
    
    /**
     * Rendu de texte dynamique
     */
    private function render_dynamic_text($element, $order_data, $base_styles) {
        $text = $element['text'] ?? $element['textTemplate'] ?? 'Signature du client';
        
        // DEBUG: Logs pour dynamic_text
        error_log('[PDF Builder] Dynamic_Text lineHeight from JSON: ' . ($element['lineHeight'] ?? 'NOT SET'));
        error_log('[PDF Builder] Dynamic_Text base_styles AVANT: ' . substr($base_styles, 0, 300));
        
        // Supprimer le line-height du base_styles car on va le gérer spécifiquement
        $base_styles_clean = preg_replace('/line-height:\s*[^;]+;/', '', $base_styles);
        
        error_log('[PDF Builder] Dynamic_Text base_styles APRÈS nettoyage: ' . substr($base_styles_clean, 0, 300));
        
        // Récupérer le line-height EXACT du JSON (string ou number)
        $line_height_ratio = isset($element['lineHeight']) ? $element['lineHeight'] : '1.3';
        
        // Calculer le lineHeight en pixels pour Dompdf (plus précis que le ratio)
        $font_size = isset($element['fontSize']) ? (is_numeric($element['fontSize']) ? $element['fontSize'] : 14) : 14;
        $line_height_px = ($font_size * floatval($line_height_ratio)) . 'px';
        
        error_log('[PDF Builder] Dynamic_Text lineHeight ratio: ' . $line_height_ratio . ', fontSize: ' . $font_size . 'px, lineHeight calculé: ' . $line_height_px);
        
        // FORCER line-height: 1.1 pour Dompdf (espacement minimal optimisé)
        // Dompdf ajoute trop d'espacement avec des valeurs en pixels
        $dompdf_line_height = '1.1';
        
        error_log('[PDF Builder] Dynamic_Text FORÇAGE line-height pour Dompdf: ' . $dompdf_line_height . ' (au lieu de ' . $line_height_px . ')');
        
        // Extraire les propriétés de positionnement (pour le conteneur) et de texte (pour le contenu)
        preg_match('/left:\s*[^;]+;/', $base_styles_clean, $left_match);
        preg_match('/top:\s*[^;]+;/', $base_styles_clean, $top_match);
        preg_match('/width:\s*[^;]+;/', $base_styles_clean, $width_match);
        preg_match('/height:\s*[^;]+;/', $base_styles_clean, $height_match);
        
        $position_styles = 'position: absolute; ' . 
                          ($left_match[0] ?? '') . ' ' . 
                          ($top_match[0] ?? '') . ' ' . 
                          ($width_match[0] ?? '') . ' ' . 
                          ($height_match[0] ?? '');
        
        // Extraire les propriétés de texte pour les appliquer sur le div intérieur
        preg_match('/font-size:\s*[^;]+;/', $base_styles_clean, $font_size_match);
        preg_match('/font-family:\s*[^;]+;/', $base_styles_clean, $font_family_match);
        preg_match('/font-weight:\s*[^;]+;/', $base_styles_clean, $font_weight_match);
        preg_match('/font-style:\s*[^;]+;/', $base_styles_clean, $font_style_match);
        preg_match('/text-align:\s*[^;]+;/', $base_styles_clean, $text_align_match);
        preg_match('/color:\s*[^;]+;/', $base_styles_clean, $color_match);
        preg_match('/text-decoration:\s*[^;]+;/', $base_styles_clean, $text_decoration_match);
        preg_match('/text-transform:\s*[^;]+;/', $base_styles_clean, $text_transform_match);
        preg_match('/letter-spacing:\s*[^;]+;/', $base_styles_clean, $letter_spacing_match);
        
        $text_styles = ($font_size_match[0] ?? '') . ' ' . 
                      ($font_family_match[0] ?? '') . ' ' . 
                      ($font_weight_match[0] ?? '') . ' ' . 
                      ($font_style_match[0] ?? '') . ' ' . 
                      ($text_align_match[0] ?? '') . ' ' . 
                      ($color_match[0] ?? '') . ' ' . 
                      ($text_decoration_match[0] ?? '') . ' ' . 
                      ($text_transform_match[0] ?? '') . ' ' .
                      ($letter_spacing_match[0] ?? '');
        
        // Le div extérieur est UNIQUEMENT un conteneur positionné
        $html = '<div class="element" style="' . $position_styles . ' margin: 0; padding: 0; box-sizing: border-box; overflow: hidden;">';
        
        // Le div intérieur contient le texte avec TOUTES les propriétés de texte
        // white-space: pre-line pour préserver les sauts de ligne (pas nl2br)
        // line-height: 1 pour forcer aucun espacement supplémentaire dans Dompdf
        $inner_style = 'white-space: pre-line; ' . 
                      'line-height: ' . $dompdf_line_height . '; ' . 
                      $text_styles . ' ' .
                      'margin: 0; padding: 0; display: block; box-sizing: border-box;';
        
        $html .= '<div style="' . $inner_style . '">' . esc_html($text) . '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendu des mentions légales
     */
    private function render_mentions($element, $base_styles) {
        $mention_type = $element['mentionType'] ?? 'custom';
        $text = '';
        
        // Si type "dynamic", générer automatiquement depuis les données WordPress
        if ($mention_type === 'dynamic') {
            $parts = [];
            $separator = $element['separator'] ?? ' • ';
            
            if ($element['showEmail'] ?? true) {
                $email = get_option('admin_email', '');
                if ($email) $parts[] = 'Email: ' . $email;
            }
            
            if ($element['showPhone'] ?? true) {
                $phone = get_option('woocommerce_store_phone', '');
                if ($phone) $parts[] = 'Tél: ' . $phone;
            }
            
            if ($element['showSiret'] ?? true) {
                $siret = get_option('woocommerce_store_siret', '');
                if ($siret) $parts[] = 'SIRET: ' . $siret;
            }
            
            if ($element['showVat'] ?? true) {
                $vat = get_option('woocommerce_store_vat', '');
                if ($vat) $parts[] = 'TVA: ' . $vat;
            }
            
            $text = implode($separator, $parts);
            
            // Si aucune donnée dynamique, utiliser le texte par défaut
            if (empty($text)) {
                $text = 'Conditions générales de vente disponibles sur demande.';
            }
        } else {
            // Type "custom" ou autre : utiliser le texte personnalisé
            $text = $element['text'] ?? 'Conditions générales de vente disponibles sur demande.';
        }
        
        // DEBUG: Log le lineHeight lu du JSON
        error_log('[PDF Builder] Mentions lineHeight from JSON: ' . ($element['lineHeight'] ?? 'NOT SET'));
        error_log('[PDF Builder] Mentions base_styles AVANT nettoyage: ' . substr($base_styles, 0, 200));
        
        // Supprimer le line-height du base_styles car on va le gérer spécifiquement
        $base_styles_clean = preg_replace('/line-height:\s*[^;]+;/', '', $base_styles);
        
        error_log('[PDF Builder] Mentions base_styles APRÈS nettoyage: ' . substr($base_styles_clean, 0, 200));
        
        // Récupérer le line-height EXACT du JSON (string ou number)
        $line_height_ratio = isset($element['lineHeight']) ? $element['lineHeight'] : '1.2';
        
        // Calculer le lineHeight en pixels pour Dompdf (plus précis que le ratio)
        $font_size = isset($element['fontSize']) ? (is_numeric($element['fontSize']) ? $element['fontSize'] : 10) : 10;
        $line_height_px = ($font_size * floatval($line_height_ratio)) . 'px';
        
        error_log('[PDF Builder] Mentions lineHeight ratio: ' . $line_height_ratio . ', fontSize: ' . $font_size . 'px, lineHeight calculé: ' . $line_height_px);
        
        // Extraire les propriétés de positionnement (pour le conteneur) et de texte (pour le contenu)
        // On garde UNIQUEMENT position, left, top, width, height sur le conteneur
        preg_match('/left:\s*[^;]+;/', $base_styles_clean, $left_match);
        preg_match('/top:\s*[^;]+;/', $base_styles_clean, $top_match);
        preg_match('/width:\s*[^;]+;/', $base_styles_clean, $width_match);
        preg_match('/height:\s*[^;]+;/', $base_styles_clean, $height_match);
        
        $position_styles = 'position: absolute; ' . 
                          ($left_match[0] ?? '') . ' ' . 
                          ($top_match[0] ?? '') . ' ' . 
                          ($width_match[0] ?? '') . ' ' . 
                          ($height_match[0] ?? '');
        
        // Extraire les propriétés de texte pour les appliquer sur le div intérieur
        preg_match('/font-size:\s*[^;]+;/', $base_styles_clean, $font_size_match);
        preg_match('/font-family:\s*[^;]+;/', $base_styles_clean, $font_family_match);
        preg_match('/font-weight:\s*[^;]+;/', $base_styles_clean, $font_weight_match);
        preg_match('/font-style:\s*[^;]+;/', $base_styles_clean, $font_style_match);
        preg_match('/text-align:\s*[^;]+;/', $base_styles_clean, $text_align_match);
        preg_match('/color:\s*[^;]+;/', $base_styles_clean, $color_match);
        preg_match('/text-decoration:\s*[^;]+;/', $base_styles_clean, $text_decoration_match);
        preg_match('/text-transform:\s*[^;]+;/', $base_styles_clean, $text_transform_match);
        preg_match('/letter-spacing:\s*[^;]+;/', $base_styles_clean, $letter_spacing_match);
        
        $text_styles = ($font_size_match[0] ?? '') . ' ' . 
                      ($font_family_match[0] ?? '') . ' ' . 
                      ($font_weight_match[0] ?? '') . ' ' . 
                      ($font_style_match[0] ?? '') . ' ' . 
                      ($text_align_match[0] ?? '') . ' ' . 
                      ($color_match[0] ?? '') . ' ' . 
                      ($text_decoration_match[0] ?? '') . ' ' . 
                      ($text_transform_match[0] ?? '') . ' ' .
                      ($letter_spacing_match[0] ?? '');
        
        // Le div extérieur est UNIQUEMENT un conteneur positionné
        $html = '<div class="element" style="' . $position_styles . ' margin: 0; padding: 0; box-sizing: border-box; overflow: hidden;">';
        
        // Ajouter le séparateur horizontal si activé
        if ($element['showSeparator'] ?? true) {
            $separator_style = $element['separatorStyle'] ?? 'solid';
            $separator_color = $element['separatorColor'] ?? '#e5e7eb';
            $separator_width = isset($element['separatorWidth']) && $element['separatorWidth'] > 0 ? $element['separatorWidth'] : 1;
            
            $hr_style = sprintf(
                'border: none; border-top: %dpx %s %s; margin: 0 0 0 0; padding: 0; line-height: 0; height: %dpx; display: block;',
                $separator_width,
                $separator_style,
                $separator_color,
                $separator_width
            );
            
            $html .= '<hr style="' . $hr_style . '" />';
        }
        
        // Le div intérieur contient le texte avec TOUTES les propriétés de texte
        // white-space: pre-line pour préserver les sauts de ligne
        $inner_style = 'white-space: pre-line; ' . 
                      'line-height: ' . $line_height_px . '; ' . 
                      $text_styles . ' ' .
                      'margin: 0; padding: 0; display: block; box-sizing: border-box;';
        
        $html .= '<div style="' . $inner_style . '">' . esc_html($text) . '</div>';
        $html .= '</div>';
        
        return $html;
    }



    /**
     * Génère un HTML de secours si le template n'est pas valide
     */
    private function generate_fallback_html($template, $all_data) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . esc_html($template['name'] ?? 'Facture') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #ffffff; }
        h1 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>' . esc_html($template['name'] ?? 'Facture') . '</h1>
    <p>Template canvas invalide - affichage de secours</p>
    <p>Commande: ' . esc_html($all_data['order']['order_number']) . '</p>
    <p>Client: ' . esc_html($all_data['customer']['full_name']) . '</p>
</body>
</html>';

        return $html;
    }
}





