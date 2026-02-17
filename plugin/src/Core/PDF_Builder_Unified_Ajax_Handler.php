<?php
/**
 * PDF Builder Pro - Handler AJAX unifié
 * Point d'entrée unique pour toutes les actions AJAX avec gestion centralisée des nonces
 * Version: 2.1.3 - Correction erreurs PHP et cron (05/12/2025)
 */

class PDF_Builder_Unified_Ajax_Handler {

    private static $instance = null;
    private $nonce_manager;
    private $current_engine_name = 'dompdf'; // Moteur utilisé pour la génération en cours

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
        add_action('wp_ajax_pdf_builder_test_hook', [$this, 'handle_test_hook']);

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
        
        // Actions de test moteur PDF
        add_action('wp_ajax_pdf_builder_test_puppeteer', [$this, 'handle_test_puppeteer']);
        add_action('wp_ajax_pdf_builder_test_all_engines', [$this, 'handle_test_all_engines']);
        add_action('wp_ajax_pdf_builder_get_active_engine', [$this, 'handle_get_active_engine']);
        
        add_action('wp_ajax_pdf_builder_debug_html', [$this, 'handle_debug_html']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_debug_html");
        add_action('wp_ajax_pdf_builder_get_preview_html', [$this, 'handle_get_preview_html']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_get_preview_html");
        add_action('wp_ajax_pdf_builder_get_orders_list', [$this, 'handle_get_orders_list']);
        error_log("[UNIFIED AJAX] Registered wp_ajax_pdf_builder_get_orders_list");
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
                error_log("[UNIFIED AJAX] Sending error response - save failed");
                wp_send_json_error(['message' => 'Erreur lors de la sauvegarde en base de données']);
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
                'pdf_builder_default_canvas_format', 'pdf_builder_default_canvas_orientation', 'pdf_builder_canvas_unit', 'pdf_builder_canvas_format',
                // PDF Engine text fields
                'pdf_builder_engine', 'pdf_builder_puppeteer_url', 'pdf_builder_puppeteer_token'
            ],
            'int_fields' => [
                'pdf_builder_cache_max_size', 'pdf_builder_cache_ttl',
                // Canvas int fields
                'pdf_builder_zoom_min', 'pdf_builder_zoom_max', 'pdf_builder_zoom_default', 'pdf_builder_zoom_step', 'pdf_builder_canvas_grid_size', 'pdf_builder_canvas_export_quality',
                'pdf_builder_canvas_fps_target', 'pdf_builder_canvas_memory_limit_js', 'pdf_builder_canvas_memory_limit_php', 'pdf_builder_canvas_dpi',
                'pdf_builder_canvas_width', 'pdf_builder_canvas_height', 'pdf_builder_canvas_border_width', 'pdf_builder_canvas_max_size', 'pdf_builder_canvas_quality',
                // PDF Engine int fields
                'pdf_builder_puppeteer_timeout'
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
                'pdf_builder_pdf_metadata_enabled', 'pdf_builder_pdf_print_optimized',
                // PDF Engine bool fields
                'pdf_builder_puppeteer_fallback'
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

        // EXTRACTION ET SAUVEGARDE DES PARAMÈTRES MOTEUR PDF DANS DES LIGNES SÉPARÉES
        $pdf_engine_keys = [
            'pdf_builder_engine',
            'pdf_builder_puppeteer_url',
            'pdf_builder_puppeteer_token',
            'pdf_builder_puppeteer_timeout',
            'pdf_builder_puppeteer_fallback'
        ];

        foreach ($pdf_engine_keys as $engine_key) {
            if (isset($settings[$engine_key])) {
                // Sauvegarder dans la table personnalisée wp_pdf_builder_settings
                $saved_value = $settings[$engine_key];
                $result = pdf_builder_update_option($engine_key, $saved_value);
                error_log("[PDF Builder AJAX] Saved PDF engine setting to wp_pdf_builder_settings: {$engine_key} = {$saved_value} (Result: " . ($result ? 'SUCCESS' : 'FAILED') . ")");
                
                // Vérifier que la valeur a bien été sauvegardée
                $retrieved_value = pdf_builder_get_option($engine_key);
                error_log("[PDF Builder AJAX] Verification for {$engine_key}: Saved='{$saved_value}', Retrieved='{$retrieved_value}'");
                
                // Supprimer du tableau unifié pour éviter la duplication
                unset($settings[$engine_key]);
            } else {
                error_log("[PDF Builder AJAX] PDF engine key not found in settings: {$engine_key}");
            }
        }

        // Sauvegarder le tableau unifié des settings
        pdf_builder_update_option('pdf_builder_settings', $settings);
        error_log('[PDF Builder AJAX] Saved unified settings array with ' . count($settings) . ' items');

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
        $saved_count = 0;
        
        // Paramètres cache/performance/maintenance
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
            $saved_count++;
        }

        // Paramètres Puppeteer (top-level POST)
        $puppeteer_settings = [
            'pdf_builder_engine' => sanitize_text_field($_POST['pdf_builder_engine'] ?? 'puppeteer'),
            'pdf_builder_puppeteer_url' => esc_url_raw($_POST['pdf_builder_puppeteer_url'] ?? ''),
            'pdf_builder_puppeteer_token' => sanitize_text_field($_POST['pdf_builder_puppeteer_token'] ?? ''),
            'pdf_builder_puppeteer_timeout' => intval($_POST['pdf_builder_puppeteer_timeout'] ?? 30),
            'pdf_builder_puppeteer_fallback' => isset($_POST['pdf_builder_puppeteer_fallback']) ? '1' : '0',
        ];

        foreach ($puppeteer_settings as $key => $value) {
            $result = pdf_builder_update_option($key, $value);
            error_log("[UNIFIED AJAX] Saved Puppeteer setting: {$key} = {$value} (Result: " . ($result ? 'SUCCESS' : 'FAILED') . ")");
            if ($result) {
                $saved_count++;
            }
        }

        error_log("[UNIFIED AJAX] save_system_settings completed - Total saved: {$saved_count}");
        return $saved_count;
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
            error_log('PDF Builder: Starting save_developer_settings');
            error_log('PDF Builder: POST data keys: ' . implode(', ', array_keys($_POST)));
        }

        $saved_count = 0;
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Check if data is in the nested pdf_builder_settings array
        if (isset($_POST['pdf_builder_settings']) && is_array($_POST['pdf_builder_settings'])) {
            $developer_settings = [
                'pdf_builder_developer_enabled',
                'pdf_builder_developer_password',
                'pdf_builder_license_test_mode_enabled',
                'pdf_builder_license_test_key',
                'pdf_builder_debug_php_errors',
                'pdf_builder_debug_js_errors',
                'pdf_builder_debug_wpdb_errors',
                'pdf_builder_debug_ajax_requests',
                'pdf_builder_performance_monitoring',
                'pdf_builder_error_reporting'
            ];

            foreach ($developer_settings as $setting_key) {
                if (isset($_POST['pdf_builder_settings'][$setting_key])) {
                    $value = $_POST['pdf_builder_settings'][$setting_key];
                    
                    // Sanitize based on field type
                    if (strpos($setting_key, 'enabled') !== false || 
                        strpos($setting_key, 'monitoring') !== false ||
                        strpos($setting_key, 'reporting') !== false) {
                        // Boolean fields
                        $settings[$setting_key] = !empty($value) ? '1' : '0';
                    } else {
                        // Text fields (password, test key, etc.)
                        $settings[$setting_key] = sanitize_text_field($value);
                    }
                    
                    error_log('PDF Builder: Developer setting ' . $setting_key . ' = ' . $settings[$setting_key]);
                    $saved_count++;
                }
            }

            // Handle license-related settings that should be saved separately
            $license_keys = [
                'pdf_builder_license_test_key',
                'pdf_builder_license_test_mode_enabled'
            ];

            foreach ($license_keys as $license_key) {
                if (isset($settings[$license_key])) {
                    // Save to separate row
                    pdf_builder_update_option($license_key, $settings[$license_key]);
                    error_log("[PDF Builder AJAX] Saved license key to separate row: {$license_key}");
                    
                    // Remove from unified settings
                    unset($settings[$license_key]);
                }
            }

            // Save unified settings
            if ($saved_count > 0) {
                pdf_builder_update_option('pdf_builder_settings', $settings);
                error_log('PDF Builder: Saved ' . $saved_count . ' developer settings');
            }
        }

        return $saved_count;
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
        error_log('[UNIFIED AJAX] save_templates_settings called');
        error_log('[UNIFIED AJAX] POST keys: ' . implode(', ', array_keys($_POST)));
        
        // Chercher les données dans la structure imbriquée
        $order_status_templates = [];
        if (isset($_POST['pdf_builder_settings']['pdf_builder_order_status_templates'])) {
            $order_status_templates = $_POST['pdf_builder_settings']['pdf_builder_order_status_templates'];
            error_log('[UNIFIED AJAX] Found templates in nested structure: ' . json_encode($order_status_templates));
        } elseif (isset($_POST['order_status_templates'])) {
            // Fallback pour l'ancien format
            $order_status_templates = $_POST['order_status_templates'];
            error_log('[UNIFIED AJAX] Found templates in flat structure: ' . json_encode($order_status_templates));
        } else {
            error_log('[UNIFIED AJAX] No template data found in POST');
        }
        
        // Nettoyer les valeurs vides
        $clean_templates = [];
        foreach ($order_status_templates as $status => $template_id) {
            if (!empty($template_id)) {
                $clean_templates[sanitize_text_field($status)] = sanitize_text_field($template_id);
            }
        }
        
        error_log('[UNIFIED AJAX] Clean templates to save: ' . json_encode($clean_templates));
        
        // Sauvegarder même si vide (permet de désélectionner tous les templates)
        $result = pdf_builder_update_option('pdf_builder_order_status_templates', $clean_templates);
        error_log('[UNIFIED AJAX] Save result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        // Vérification immédiate
        $saved_value = pdf_builder_get_option('pdf_builder_order_status_templates', []);
        error_log('[UNIFIED AJAX] Verification - value in DB: ' . json_encode($saved_value));
        
        // Retourner 1 si sauvegarde réussie, 0 si échec
        return $result ? 1 : 0;
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
      * Handler pour tester les hooks disponibles
      */
     public function handle_test_hook() {
         if (!$this->nonce_manager->validate_ajax_request()) {
             wp_send_json_error('Nonce invalide');
             return;
         }

         // Vérifier les paramètres
         $hook_name = isset($_POST['hookName']) ? sanitize_text_field($_POST['hookName']) : '';
         $hook_type = isset($_POST['hookType']) ? sanitize_text_field($_POST['hookType']) : 'action';

         if (empty($hook_name)) {
             wp_send_json_error('Hook name is required');
             return;
         }

         // Vérifier que c'est un hook valide du plugin
         $valid_hooks = [
             'pdf_builder_template_data',
             'pdf_builder_element_render',
             'pdf_builder_security_check',
             'pdf_builder_before_save',
             'pdf_builder_after_save',
             'pdf_builder_initialize_canvas',
             'pdf_builder_render_complete',
             'pdf_builder_pdf_generated',
             'pdf_builder_admin_page_loaded',
             'pdf_builder_cache_cleared'
         ];

         if (!in_array($hook_name, $valid_hooks, true)) {
             wp_send_json_error('Hook non reconnu');
             return;
         }

         // Récupérer les informations du hook
         global $wp_filter;
         
         $is_registered = isset($wp_filter[$hook_name]);
         $callback_count = 0;
         $callbacks = [];

         if ($is_registered && is_array($wp_filter[$hook_name])) {
             foreach ($wp_filter[$hook_name] as $priority => $hooks_by_priority) {
                 if (is_array($hooks_by_priority)) {
                     foreach ($hooks_by_priority as $hook_id => $hook_data) {
                         if (is_array($hook_data) && isset($hook_data['function'])) {
                             $callback_count++;
                             
                             // Essayer de déterminer le nom de la fonction/classe
                             $function_name = 'Unknown';
                             $function = $hook_data['function'];
                             
                             if (is_string($function)) {
                                 $function_name = $function;
                             } elseif (is_array($function) && count($function) >= 2) {
                                 $class_name = is_object($function[0]) ? get_class($function[0]) : $function[0];
                                 $method_name = $function[1];
                                 $function_name = $class_name . '::' . $method_name;
                             } elseif (is_object($function) && $function instanceof \Closure) {
                                 $function_name = 'Closure';
                             }

                             $callbacks[] = [
                                 'function' => $function_name,
                                 'priority' => (int) $priority,
                                 'accepted_args' => isset($hook_data['accepted_args']) ? (int) $hook_data['accepted_args'] : 1
                             ];
                         }
                     }
                 }
             }

             // Trier par priorité
             usort($callbacks, function($a, $b) {
                 return $a['priority'] - $b['priority'];
             });
         }

         wp_send_json_success([
             'type' => $hook_type,
             'is_registered' => $is_registered,
             'callback_count' => $callback_count,
             'callbacks' => $callbacks
         ]);
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
    /**
     * Handler AJAX pour la génération de PDF
     * Optimisé avec fonctions helper pour meilleure performance et maintenabilité
     */
    public function handle_generate_pdf() {
        $this->debug_log("========== GÉNÉRATION PDF DÉMARRÉE ==========");
        $this->debug_log("POST params: " . json_encode($_POST));
        
        // Vérifier les permissions - doit être connecté et avoir les droits de gestion WooCommerce
        if (!is_user_logged_in() || !current_user_can('edit_shop_orders')) {
            $this->debug_log("Permission refusée", "WARNING");
            wp_die('Permission refusée', '', ['response' => 403]);
        }

        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? 0);
        
        $this->debug_log("Template ID: '{$template_id}', Order ID: {$order_id}");

        if (!$template_id || !$order_id) {
            $this->debug_log("Paramètres manquants", "WARNING");
            wp_die('Paramètres manquants', '', ['response' => 400]);
        }

        try {
            // Vérifier que WooCommerce est actif
            if (!function_exists('wc_get_order')) {
                $this->debug_log("WooCommerce non actif", "ERROR");
                wp_die('WooCommerce n\'est pas actif', '', ['response' => 500]);
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                $this->debug_log("Commande #{$order_id} introuvable", "WARNING");
                wp_die('Commande introuvable', '', ['response' => 404]);
            }
            
            $this->debug_log("Commande #{$order_id} trouvée");

            // Récupérer le template
            $template = $this->get_template($template_id);
            if (!$template) {
                $this->debug_log("Template '{$template_id}' introuvable", "WARNING");
                wp_die('Modèle introuvable', '', ['response' => 404]);
            }
            
            $this->debug_log("Template '{$template_id}' trouvé: " . ($template['name'] ?? 'sans nom'));

            // === NOUVEAU : DÉTERMINER LE MOTEUR PDF AVANT GÉNÉRATION HTML ===
            $engine = \PDF_Builder\PDF\Engines\PDFEngineFactory::create();
            $this->current_engine_name = strtolower($engine->get_name());
            $this->debug_log("Moteur PDF sélectionné: " . $engine->get_name());
            
            // Générer l'HTML avec les vraies données (avec styles optimisés pour le moteur)
            $this->debug_log("Début génération HTML pour PDF (moteur: {$this->current_engine_name})");
            $html = $this->generate_template_html($template, $order, 'pdf');
            $this->debug_log("HTML généré - Longueur: " . strlen($html) . " caractères");

            // Optimiser l'HTML avant le rendu
            $html = $this->optimize_html($html);
            
            // Configurer le format papier depuis le template
            $template_data = json_decode($template['template_data'], true);
            $width = $template_data['canvasWidth'] ?? 794;
            $height = $template_data['canvasHeight'] ?? 1123;
            
            $this->debug_log("Génération PDF avec moteur: " . $engine->get_name());
            
            // Générer le PDF avec le moteur sélectionné
            $pdf_content = $engine->generate($html, [
                'width' => $width,
                'height' => $height
            ]);
            
            if ($pdf_content === false) {
                $this->debug_log("Échec génération PDF", "ERROR");
                wp_die('Erreur lors de la génération du PDF', '', ['response' => 500]);
            }
            
            $this->debug_log("PDF généré avec succès - Taille: " . strlen($pdf_content) . " bytes");
            
            // Envoyer le PDF au navigateur
            $this->debug_log("Envoi du PDF au navigateur");
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="facture-' . $order->get_order_number() . '.pdf"');
            header('Content-Length: ' . strlen($pdf_content));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $pdf_content;
            exit;
            
        } catch (Exception $e) {
            $this->debug_log('Erreur génération PDF: ' . $e->getMessage(), "ERROR");
            wp_die('Erreur: ' . $e->getMessage(), '', ['response' => 500]);
        }
    }

    /**
     * Génère une image (PNG/JPG) à partir d'un template et d'une commande
     * Fonctionnalité PREMIUM uniquement (VERSION OPTIMISÉE avec Factory)
     */
    public function handle_generate_image() {
        $this->debug_log("========== GÉNÉRATION IMAGE DÉMARRÉE ==========");
        $this->debug_log("POST params: " . json_encode($_POST));
        
        // Vérifier les permissions
        if (!is_user_logged_in() || !current_user_can('edit_shop_orders')) {
            $this->debug_log("Permission refusée", "WARNING");
            wp_die('Permission refusée', '', ['response' => 403]);
        }

        // Vérifier le statut premium
        if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            if (!$license_manager->isPremium()) {
                $this->debug_log("Licence premium requise pour la génération d'images", "WARNING");
                wp_die('Cette fonctionnalité nécessite une licence premium', '', ['response' => 403]);
            }
        } else {
            $this->debug_log("License Manager non disponible", "ERROR");
            wp_die('Système de licence non disponible', '', ['response' => 500]);
        }

        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? 0);
        $format = sanitize_text_field($_POST['format'] ?? 'png');
        
        $this->debug_log("Template ID: '{$template_id}', Order ID: {$order_id}, Format: {$format}");

        if (!$template_id || !$order_id || !in_array($format, ['png', 'jpg'])) {
            $this->debug_log("Paramètres manquants ou invalides", "WARNING");
            wp_die('Paramètres manquants ou invalides', '', ['response' => 400]);
        }

        try {
            // Vérifier que WooCommerce est actif
            if (!function_exists('wc_get_order')) {
                $this->debug_log("WooCommerce non actif", "ERROR");
                wp_die('WooCommerce n\'est pas actif', '', ['response' => 500]);
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                $this->debug_log("Commande #{$order_id} introuvable", "WARNING");
                wp_die('Commande introuvable', '', ['response' => 404]);
            }
            
            $this->debug_log("Commande #{$order_id} trouvée");

            // Récupérer le template
            $template = $this->get_template($template_id);
            if (!$template) {
                $this->debug_log("Template '{$template_id}' introuvable", "WARNING");
                wp_die('Modèle introuvable', '', ['response' => 404]);
            }
            
            $this->debug_log("Template '{$template_id}' trouvé");

            // === DÉTERMINER LE MOTEUR PDF AVANT GÉNÉRATION HTML ===
            $engine = \PDF_Builder\PDF\Engines\PDFEngineFactory::create();
            $this->current_engine_name = strtolower($engine->get_name());
            $this->debug_log("Moteur sélectionné pour image: " . $engine->get_name());
            
            // Générer l'HTML avec les vraies données (avec styles optimisés pour le moteur)
            $this->debug_log("Début génération HTML pour image (moteur: {$this->current_engine_name})");
            $html = $this->generate_template_html($template, $order);
            
            // Optimiser le HTML
            $html = $this->optimize_html($html);
            
            // Récupérer les dimensions du template
            $template_data = json_decode($template['template_data'], true);
            $width = $template_data['canvasWidth'] ?? 794;
            $height = $template_data['canvasHeight'] ?? 1123;
            
            $this->debug_log("Dimensions image: {$width}x{$height}px, format: {$format}");
            $this->debug_log("Génération image avec moteur: " . $engine->get_name());
            
            // Générer l'image avec le moteur sélectionné
            $image_content = $engine->generate_image($html, [
                'format' => $format,
                'width' => $width,
                'height' => $height,
                'quality' => 90
            ]);
            
            if ($image_content === false) {
                $this->debug_log("Échec génération image", "ERROR");
                
                // Message d'erreur détaillé
                wp_send_json_error([
                    'message' => "Génération d'image échouée",
                    'details' => "Le moteur " . $engine->get_name() . " n'a pas pu générer l'image. " .
                                "Si vous utilisez Puppeteer, vérifiez la configuration. " .
                                "Sinon, installez l'extension PHP Imagick.",
                    'code' => 'IMAGE_GENERATION_FAILED'
                ], 500);
            }
            
            $this->debug_log("Image générée avec succès - Taille: " . strlen($image_content) . " bytes");
            
            // Envoyer l'image au navigateur
            $mime_type = ($format === 'png') ? 'image/png' : 'image/jpeg';
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: attachment; filename="facture-' . $order->get_order_number() . '.' . $format . '"');
            header('Content-Length: ' . strlen($image_content));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $image_content;
            exit;
        } catch (Exception $e) {
            $this->debug_log('Erreur génération image: ' . $e->getMessage(), "ERROR");
            $this->debug_log('Stack trace: ' . $e->getTraceAsString(), "ERROR");
            
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

        // NOTE: Prévisualisation HTML disponible pour tous (pas seulement premium)
        // La vérification premium est faite ailleurs pour la génération PNG/JPG

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
            // Dimensions par défaut : A4 @ 96 DPI (794×1123px)
            $width = $template_data['canvasWidth'] ?? 794;
            $height = $template_data['canvasHeight'] ?? 1123;

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
        
        $table_name = $wpdb->prefix . 'pdf_builder_templates';
        $template = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $template_id
            ),
            ARRAY_A
        );
        
        return $template ?: $this->get_fallback_template($template_id);
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
                // A4 @ 96 DPI (standard écran) - 794×1123px
                // Note: Pour PDF, Dompdf convertit en 72 DPI (×0.75) = 595×842pt
                'width' => 794,
                'height' => 1123,
                'dpi' => 96,  // DPI écran (React Canvas)
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
            return $this->generate_fallback_html($template, $all_data);
        }

        $elements = $template_data['elements'];

        // Support des deux formats de canvas - Dimensions par défaut : A4 @ 96 DPI
        if (isset($template_data['canvas'])) {
            $canvas = $template_data['canvas'];
            $width = $canvas['width'] ?? 794;
            $height = $canvas['height'] ?? 1123;
        } else {
            $width = $template_data['canvasWidth'] ?? 794;
            $height = $template_data['canvasHeight'] ?? 1123;
        }

        // Début du HTML
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html($template['name'] ?? 'Document') . '</title>
    <style>
        /* 
         * IMPORTANT: box-sizing: border-box est utilisé pour tous les éléments.
         * Cela signifie que border et padding sont INCLUS dans width/height.
         * 
         * Exemple: element avec width=100px, padding=10px, border=2px
         * - Avec border-box: contenu réel = 100 - 2*10 - 2*2 = 76px
         * - Sans border-box (React Canvas): dimension totale = 100 + 2*10 + 2*2 = 124px
         * 
         * Cette différence est compensée dans les calculs de rendu côté serveur.
         */
        @page {
            margin: 0;
            size: ' . $width . 'px ' . $height . 'px;
        }
        html {
            margin: 0;
            padding: 0;
            border: 0;
            box-sizing: border-box;
            /* CRITICAL: Forcer font-size pour éviter les variations de navigateur */
            font-size: 16px;
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
            border: 0;
            overflow-y: auto;
            overflow-x: hidden;
            font-size: 16px;
            max-height: 100vh;
        }
        .pdf-canvas {
            position: relative;
            display: block;
            width: ' . $width . 'px;
            height: ' . $height . 'px;
            background: #ffffff;
            margin: 0 auto;
            padding: 0 !important;
            border: 0 !important;
            overflow: visible;
            /* CRITICAL: Assurer aucun offset */
            transform: translate(0, 0);
        }
        .element {
            position: absolute !important;
            overflow: hidden;
            word-wrap: break-word;
            box-sizing: border-box !important;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        th, td {
            padding: 8px;
            text-align: left;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        th {
            font-weight: bold;
        }
        
        /* Règles spécifiques pour l\'impression */
        @media print {
            @page {
                margin: 0;
                size: ' . $width . 'px ' . $height . 'px;
            }
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .pdf-canvas {
                position: relative !important;
                width: ' . $width . 'px !important;
                height: ' . $height . 'px !important;
                overflow: visible !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
            }
            .element {
                position: absolute !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                page-break-inside: avoid !important;
            }
            /* Forcer impression des couleurs de fond */
            table, th, td, tr {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
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

        return $html;
    }

    /**
     * Génère le HTML d'un élément
     */
    private function render_element($element, $order_data, $is_premium = false, $format = 'html') {
        // Vérifier si l'élément est visible
        if (isset($element['visible']) && $element['visible'] === false) {
            return '';
        }
        
        $element_id = $element['id'] ?? uniqid('element_');
        $type = $element['type'] ?? 'text';
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $width = $element['width'] ?? 100;
        $height = $element['height'] ?? 30;
        
        // Styles de base avec position absolute et dimensions
        $styles = "position: absolute !important; margin: 0 !important; left: {$x}px !important; top: {$y}px !important; width: {$width}px !important; height: {$height}px !important;";
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
            case 'rectangle':
                $rendered = $this->render_rectangle($element, $styles);
                break;
            case 'circle':
                $rendered = $this->render_circle($element, $styles);
                break;
            case 'document_type':
                $rendered = $this->render_document_type($element, $styles);
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
                    // ✅ Support de objectFit (cohérent avec React : cover par défaut)
                    $objectFit = isset($element['fit']) ? $element['fit'] : (isset($element['objectFit']) ? $element['objectFit'] : 'cover');
                    $rendered = '<div class="element" style="' . $styles . '"><img src="' . esc_url($element['src']) . '" style="width: 100%; height: 100%; object-fit: ' . esc_attr($objectFit) . ';" /></div>';
                }
                break;
            case 'text':
            default:
                $content = $element['text'] ?? $element['content'] ?? '';
                
                // ✅ Support du padding horizontal et vertical (backward compatibility)
                $paddingHorizontal = 12;
                $paddingVertical = 12;
                $verticalAlign = isset($element['verticalAlign']) ? $element['verticalAlign'] : 'top';
                
                // Styles du conteneur interne avec padding et alignement
                $textStyle = $styles . '; white-space: pre-line; padding: ' . $paddingVertical . 'px ' . $paddingHorizontal . 'px;';
                
                // ✅ Alignement vertical via flexbox (cohérent avec React)
                if ($verticalAlign === 'middle') {
                    $textStyle .= ' display: flex; flex-direction: column; justify-content: center; height: 100%;';
                } elseif ($verticalAlign === 'bottom') {
                    $textStyle .= ' display: flex; flex-direction: column; justify-content: flex-end; height: 100%;';
                }
                
                $rendered = '<div class="element" style="' . $textStyle . '">' . esc_html($content) . '</div>';
                break;
        }
        
        error_log("[PDF Builder] Élément {$element_id} rendu: " . strlen($rendered) . " caractères");
        return $rendered;
    }
    
    /**
     * Construit les styles CSS d'un élément
     */
    /**
     * Construit les styles CSS d'un élément (VERSION OPTIMISÉE)
     * Optimisations:
     * - Utilise concaténation de chaîne au lieu de tableau
     * - Réduit les appels à isset()
     * - Utilise l'opérateur de fusion null (??)
     * - Évite les conditions imbriquées
     * 
     * @param array $element Données de l'élément
     * @return string Styles CSS
     */
    private function build_element_styles($element) {
        $css = '';
        
        // === TYPOGRAPHIE ===
        // Styles de texte avec mapping direct
        static $text_styles = [
            'fontSize' => 'font-size: %spx;',
            'fontFamily' => 'font-family: %s;',
            'fontWeight' => 'font-weight: %s;',
            'fontStyle' => 'font-style: %s;',
            'textDecoration' => 'text-decoration: %s;',
            'textTransform' => 'text-transform: %s;',
            'textAlign' => 'text-align: %s;',
            'textColor' => 'color: %s;',
        ];
        
        foreach ($text_styles as $prop => $format) {
            if (isset($element[$prop])) {
                $css .= sprintf($format, $element[$prop]) . ' ';
            }
        }
        
        // Word spacing (ignorer 'normal')
        if (($element['wordSpacing'] ?? 'normal') !== 'normal') {
            $css .= 'word-spacing: ' . $element['wordSpacing'] . '; ';
        }
        
        // Line height - Support complet avec Puppeteer, désactivé pour DomPDF
        if (isset($element['lineHeight']) && $element['lineHeight'] !== '' && $element['lineHeight'] !== 'normal') {
            // Puppeteer supporte pleinement line-height CSS moderne
            if ($this->current_engine_name === 'puppeteer') {
                $lineHeight = $element['lineHeight'];
                // Si c'est un nombre, utiliser comme multiplicateur (ex: 1.5)
                // Si c'est avec unité (px, em, etc.), utiliser tel quel
                if (is_numeric($lineHeight)) {
                    $css .= "line-height: {$lineHeight}; ";
                } else {
                    $css .= "line-height: {$lineHeight}; ";
                }
            }
            // DomPDF: ignorer line-height car support CSS incomplet
        }
        
        // === ARRIÈRE-PLAN ET BORDURES ===
        // Background (respecter showBackground)
        if (($element['backgroundColor'] ?? 'transparent') !== 'transparent') {
            if ($element['showBackground'] ?? true) {
                $css .= 'background-color: ' . $element['backgroundColor'] . '; ';
            }
        }
        
        // Bordures (respecter showBorders)
        $borderWidth = $element['borderWidth'] ?? 0;
        if ($borderWidth > 0 && ($element['showBorders'] ?? true)) {
            $borderColor = $element['borderColor'] ?? '#000000';
            $borderStyle = $element['borderStyle'] ?? 'solid';
            $css .= "border: {$borderWidth}px {$borderStyle} {$borderColor}; ";
        }
        
        // Border radius
        $borderRadius = $element['borderRadius'] ?? 0;
        if ($borderRadius > 0) {
            $css .= "border-radius: {$borderRadius}px; ";
        }
        
        // === EFFETS VISUELS ===
        // Opacité (normalisation 0-100 vers 0-1)
        if (isset($element['opacity'])) {
            $opacity = $element['opacity'] > 1 ? $element['opacity'] / 100 : $element['opacity'];
            if ($opacity < 1) {
                $css .= "opacity: {$opacity}; ";
            }
        }
        
        // Rotation 
        $rotation = $element['rotation'] ?? 0;
        if ($rotation != 0) {
            $css .= "transform: rotate({$rotation}deg); ";
        }
        
        // Ombre (uniquement si au moins un paramètre non nul)
        $shadowX = $element['shadowOffsetX'] ?? 0;
        $shadowY = $element['shadowOffsetY'] ?? 0;
        $shadowBlur = $element['shadowBlur'] ?? 0;
        if ($shadowX != 0 || $shadowY != 0 || $shadowBlur != 0) {
            $shadowColor = $element['shadowColor'] ?? '#000000';
            $css .= "box-shadow: {$shadowX}px {$shadowY}px {$shadowBlur}px {$shadowColor}; ";
        }
        
        return $css;
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
                return '<div>' .
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
     * Helper: Extrait les propriétés de padding avec fallback
     */
    private function extract_padding($element) {
        $default_padding = $element['padding'] ?? 12;
        return [
            'horizontal' => $element['paddingHorizontal'] ?? $default_padding,
            'vertical' => $element['paddingVertical'] ?? $default_padding
        ];
    }
    
    /**
     * Helper: Extrait les propriétés de police avec fallback
     */
    private function extract_font_props($element, $prefix = '', $defaults = []) {
        // Récupérer la police par défaut pour le prefix
        // ✅ FIX: Utiliser 'Arial' au lieu de 'DejaVu Sans' pour matcher React/Canvas defaults
        $default_family = $element['fontFamily'] ?? 'Arial';
        $default_size = $defaults['size'] ?? 12;
        $default_weight = $defaults['weight'] ?? 'normal';
        $default_style = $defaults['style'] ?? 'normal';
        
        // Construire les clés pour le prefix (ex: 'header' → 'headerFontFamily')
        $family_key = $prefix ? "{$prefix}FontFamily" : 'fontFamily';
        $size_key = $prefix ? "{$prefix}FontSize" : 'fontSize';
        $weight_key = $prefix ? "{$prefix}FontWeight" : 'fontWeight';
        $style_key = $prefix ? "{$prefix}FontStyle" : 'fontStyle';
        
        // Pour family, accepter aussi une version avec 'body' ou 'header' prefix du fontFamily
        $specific_family = $element[$family_key] ?? null;
        if (!$specific_family && $prefix) {
            // Fallback: utiliser fontFamily si la version spécifique n'existe pas
            $specific_family = $element['fontFamily'] ?? $defaults['family'] ?? $default_family;
        }
        
        return [
            'family' => $specific_family ?? $default_family,
            'size' => $element[$size_key] ?? $defaults['size'] ?? $default_size,
            'weight' => $element[$weight_key] ?? $defaults['weight'] ?? $default_weight,
            'style' => $element[$style_key] ?? $defaults['style'] ?? $default_style
        ];
    }
    
    /**
     * Helper: Extrait les propriétés de couleur avec fallback
     */
    private function extract_colors($element, $defaults = []) {
        return [
            'text' => $element['textColor'] ?? ($defaults['text'] ?? '#374151'),
            'header' => $element['headerTextColor'] ?? ($defaults['header'] ?? '#111827'),
            'background' => $element['backgroundColor'] ?? ($defaults['background'] ?? '#ffffff'),
            'border' => $element['borderColor'] ?? ($defaults['border'] ?? '#e5e7eb')
        ];
    }
    
    /**
     * Helper: Extrait les propriétés de layout
     */
    private function extract_layout_props($element) {
        return [
            'layout' => $element['layout'] ?? 'vertical',
            'textAlign' => $element['textAlign'] ?? 'left',
            'verticalAlign' => $element['verticalAlign'] ?? 'top',
            'letterSpacing' => floatval($element['letterSpacing'] ?? 0)
        ];
    }

    /**
     * Rendu du tableau de produits WooCommerce
     */
    private function render_product_table($element, $order_data, $base_styles) {
        $html = '<div class="element" style="' . $base_styles . '">';
        
        // Récupérer tous les styles depuis le JSON de l'élément
        $show_borders = $element['showBorders'] ?? true;
        $border_color = $element['borderColor'] ?? '#e5e7eb';
        $border_width = $element['borderWidth'] ?? 1;
        
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
        
        // Flags de colonnes (comme dans React)
        $show_image = $element['showImage'] ?? true; // Par défaut TRUE
        $show_name = true; // Toujours afficher
        $show_sku = $element['showSku'] ?? false;
        $show_description = $element['showDescription'] ?? false;
        $show_quantity = $element['showQuantity'] ?? true;
        $show_price = $element['showPrice'] ?? true;
        $show_total = $element['showTotal'] ?? true;
        
        // AUCUNE bordure sur les tableaux ni les cellules
        $cell_border_style = 'border: none;';
        
        $html .= '<table style="width:100%; border-collapse: collapse; background-color: ' . $bg_color . ';">';
        
        // En-têtes
        if ($element['showHeaders'] ?? true) {
            $header_style = $cell_border_style . " padding: 8px; background: {$header_bg}; color: {$header_color}; " .
                           "font-size: {$header_font_size}px; font-family: {$header_font_family}; " .
                           "font-weight: {$header_font_weight}; font-style: {$header_font_style};";
            
            $html .= '<thead><tr>';
            if ($show_image) $html .= '<th style="' . $header_style . ' text-align: center; width: 60px;">Img</th>';
            if ($show_name) $html .= '<th style="' . $header_style . '">Produit</th>';
            if ($show_sku) $html .= '<th style="' . $header_style . '">SKU</th>';
            if ($show_description) $html .= '<th style="' . $header_style . '">Description</th>';
            if ($show_quantity) $html .= '<th style="' . $header_style . ' text-align: center; width: 80px; max-width: 80px;">Qté</th>';
            if ($show_price) $html .= '<th style="' . $header_style . ' text-align: right; width: 80px; max-width: 80px;">Prix</th>';
            if ($show_total) $html .= '<th style="' . $header_style . ' text-align: right; width: 80px; max-width: 80px;">Total</th>';
            $html .= '</tr></thead>';
        }
        
        $html .= '<tbody>';
        $row_index = 0;
        
        $row_style_base = $cell_border_style . " padding: 8px; color: {$row_color}; " .
                         "font-size: {$row_font_size}px; font-family: {$row_font_family}; " .
                         "font-weight: {$row_font_weight}; font-style: {$row_font_style};";
        
        // Produits
        foreach ($order_data['products'] as $product) {
            // ✅ FIX: Utiliser $bg_color au lieu de transparent pour les lignes paires
            $row_bg = ($element['showAlternatingRows'] ?? true) && ($row_index % 2 === 1) ? $alt_bg : $bg_color;
            $html .= '<tr style="background: ' . $row_bg . ';">';
            
            // Colonne Image
            if ($show_image) {
                $img_url = $product['image'] ?? '';
                $img_html = '';
                if ($img_url) {
                    // Les images sont déjà en base64, pas besoin d'esc_url
                    if (strpos($img_url, 'data:') === 0) {
                        $img_html = '<img src="' . $img_url . '" style="max-width: 50px; max-height: 50px; object-fit: contain;" />';
                    } else {
                        $img_html = '<img src="' . esc_url($img_url) . '" style="max-width: 50px; max-height: 50px; object-fit: contain;" />';
                    }
                }
                $html .= '<td style="' . $row_style_base . ' text-align: center;">' . $img_html . '</td>';
            }
            
            // Colonne Produit
            if ($show_name) {
                $html .= '<td style="' . $row_style_base . '">' . esc_html($product['name']) . '</td>';
            }
            
            // Colonne SKU
            if ($show_sku) {
                $sku = $product['sku'] ?? 'N/A';
                $html .= '<td style="' . $row_style_base . '">' . esc_html($sku) . '</td>';
            }
            
            // Colonne Description
            if ($show_description) {
                $description = $product['description'] ?? '';
                $html .= '<td style="' . $row_style_base . '">' . esc_html($description) . '</td>';
            }
            
            // Colonne Quantité
            if ($show_quantity) {
                $html .= '<td style="' . $row_style_base . ' text-align: center; width: 80px; max-width: 80px;">' . esc_html($product['quantity']) . '</td>';
            }
            
            // Colonne Prix
            if ($show_price) {
                $html .= '<td style="' . $row_style_base . ' text-align: right; width: 80px; max-width: 80px;">' . $product['price'] . '</td>';
            }
            
            // Colonne Total
            if ($show_total) {
                $html .= '<td style="' . $row_style_base . ' text-align: right; width: 80px; max-width: 80px;">' . $product['total'] . '</td>';
            }
            
            $html .= '</tr>';
            $row_index++;
        }
        
        // Frais de service (fees) - ajoutés comme lignes de produits
        if (isset($order_data['fees']) && !empty($order_data['fees'])) {
            foreach ($order_data['fees'] as $fee) {
                $row_bg = ($element['showAlternatingRows'] ?? true) && ($row_index % 2 === 1) ? $alt_bg : $bg_color;
                $html .= '<tr style="background: ' . $row_bg . ';">';
                
                if ($show_image) $html .= '<td style="' . $row_style_base . '"></td>';
                if ($show_name) $html .= '<td style="' . $row_style_base . '">' . esc_html($fee['name']) . '</td>';
                if ($show_sku) $html .= '<td style="' . $row_style_base . '">FEE</td>';
                if ($show_description) $html .= '<td style="' . $row_style_base . '"></td>';
                if ($show_quantity) $html .= '<td style="' . $row_style_base . ' text-align: center; width: 80px; max-width: 80px;">1</td>';
                if ($show_price) $html .= '<td style="' . $row_style_base . ' text-align: right; width: 80px; max-width: 80px;">' . $fee['total'] . '</td>';
                if ($show_total) $html .= '<td style="' . $row_style_base . ' text-align: right; width: 80px; max-width: 80px;">' . $fee['total'] . '</td>';
                
                $html .= '</tr>';
                $row_index++;
            }
        }
        
        $html .= '</tbody></table>'; // Fermer le tableau des produits
        
        // Tableau des totaux séparé - aligné à droite (SANS bordure)
        $html .= '<div style="margin-top: 20px; width: 100%; display: table;">';
        
        // Le tableau des totaux n'a JAMAIS de bordure
        $html .= '<table style="width: 25%; margin-left: auto; border-collapse: collapse; margin-right: 5px;">';
        $html .= '<tbody>';
        
        // Ligne de séparation avant les totaux (comme dans React Canvas ligne 1327-1332)
        $html .= '<tr><td colspan="2" style="border-bottom: 1px solid #d1d5db; padding: 0; line-height: 0; height: 1px;"></td></tr>';
        $html .= '<tr><td colspan="2" style="padding: 10px 0 0 0;"></td></tr>'; // Espacement après la ligne
        
        // Style pour les lignes de summary (sous-total, remise, livraison, TVA) - SANS bordures de cellules
        $summary_style = "border: none; text-align: right; padding: 6px 8px; " .
                        "font-size: {$row_font_size}px; font-family: {$row_font_family}; " .
                        "font-weight: {$row_font_weight}; color: {$row_color};";
        
        $summary_label_style = "border: none; text-align: left; padding: 6px 8px; " .
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
        
        // Total final avec séparateur (ligne de séparation avec border-top uniquement)
        // IMPORTANT: border-left/right/bottom = none pour éviter les bordures de cellules
        $total_style = "border-top: 2px solid #333; border-left: none; border-right: none; border-bottom: none; text-align: right; padding: 10px 8px 6px 8px; " .
                      "font-size: {$total_font_size}px; font-family: {$total_font_family}; " .
                      "font-weight: {$total_font_weight}; font-style: {$total_font_style}; " .
                      "color: {$total_color};";
        
        $total_label_style = "border-top: 2px solid #333; border-left: none; border-right: none; border-bottom: none; text-align: left; padding: 10px 8px 6px 8px; " .
                            "font-size: {$total_font_size}px; font-family: {$total_font_family}; " .
                            "font-weight: {$total_font_weight}; font-style: {$total_font_style}; " .
                            "color: {$total_color};";
        
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
    private function render_customer_info_element($element, $order_data, $base_styles, $is_premium = false) {
        // Extraction des propriétés via helpers
        $padding = $this->extract_padding($element);
        $layout_props = $this->extract_layout_props($element);
        $colors = $this->extract_colors($element);
        $header_font = $this->extract_font_props($element, 'header', ['size' => 14, 'weight' => 'bold']);
        $body_font = $this->extract_font_props($element, 'body', ['size' => 12]);
        
        // Options d'affichage
        $show = [
            'headers' => $element['showHeaders'] ?? true,
            'fullName' => $element['showFullName'] ?? true,
            'address' => $element['showAddress'] ?? true,
            'email' => $element['showEmail'] ?? true,
            'phone' => $element['showPhone'] ?? true,
            'payment' => $element['showPaymentMethod'] ?? false,
            'transaction' => $element['showTransactionId'] ?? false
        ];
        
        // Construction des lignes selon le layout
        $lines = [];
        $customer = $order_data['customer'];
        $billing = $order_data['billing'];
        
        if ($layout_props['layout'] === 'vertical') {
            if ($show['fullName']) $lines[] = esc_html($customer['full_name']);
            if ($show['address']) {
                // Split par \n pour supporter les adresses multi-lignes (comme React)
                $address_lines = explode("\n", $billing['full_address']);
                foreach ($address_lines as $addr_line) {
                    if (trim($addr_line)) $lines[] = esc_html($addr_line);
                }
            }
            if ($show['email']) $lines[] = esc_html($customer['email']);
            if ($show['phone'] && !empty($customer['phone'])) $lines[] = esc_html($customer['phone']);
            if ($show['payment']) {
                $payment_method = $order_data['order']['payment_method'] ?? 'Carte bancaire';
                $lines[] = 'Paiement: ' . esc_html($payment_method);
            }
            if ($show['transaction']) {
                $transaction_id = $order_data['order']['transaction_id'] ?? 'N/A';
                $lines[] = 'ID: ' . esc_html($transaction_id);
            }
        } elseif ($layout_props['layout'] === 'horizontal') {
            $line1 = $line2 = $line3 = '';
            if ($show['fullName']) $line1 .= esc_html($customer['full_name']);
            if ($show['email']) $line1 .= ($line1 ? ' | ' : '') . esc_html($customer['email']);
            // Remplacer \n par , pour garder compact (comme React)
            if ($show['address']) $line2 .= esc_html(str_replace("\n", ', ', $billing['full_address']));
            if ($show['phone'] && !empty($customer['phone'])) $line2 .= ($line2 ? ' | ' : '') . esc_html($customer['phone']);
            if ($show['payment']) {
                $payment_method = $order_data['order']['payment_method'] ?? 'Carte bancaire';
                $line3 .= 'Paiement: ' . esc_html($payment_method);
            }
            if ($show['transaction']) {
                $transaction_id = $order_data['order']['transaction_id'] ?? 'N/A';
                $line3 .= ($line3 ? ' | ' : '') . 'ID: ' . esc_html($transaction_id);
            }
            
            if ($line1) $lines[] = $line1;
            if ($line2) $lines[] = $line2;
            if ($line3) $lines[] = $line3;
        } else { // compact
            if ($show['fullName']) $lines[] = esc_html($customer['full_name']);
            
            $compact_parts = [];
            // Remplacer \n par , pour mode compact (comme React)
            if ($show['address']) $compact_parts[] = esc_html(str_replace("\n", ', ', $billing['full_address']));
            if ($show['email']) $compact_parts[] = esc_html($customer['email']);
            if ($show['phone'] && !empty($customer['phone'])) $compact_parts[] = esc_html($customer['phone']);
            if ($show['payment']) {
                $payment_method = $order_data['order']['payment_method'] ?? 'Carte bancaire';
                $compact_parts[] = 'Paiement: ' . esc_html($payment_method);
            }
            if ($show['transaction']) {
                $transaction_id = $order_data['order']['transaction_id'] ?? 'N/A';
                $compact_parts[] = 'ID: ' . esc_html($transaction_id);
            }
            
            if ($compact_parts) $lines[] = implode(' • ', $compact_parts);
        }
        
        // Styles du conteneur - nettoyer TOUS les padding pour DOMPDF
        $base_styles_clean = preg_replace('/padding(-top|-bottom|-left|-right)?:\s*[^;]+;/i', '', $base_styles);
        // Retirer aussi les !important de position qui causent des conflits DOMPDF
        $base_styles_clean = str_replace('!important', '', $base_styles_clean);
        
        $letter_spacing = $layout_props['letterSpacing'] ? " letter-spacing: {$layout_props['letterSpacing']}px;" : '';
        $container_styles = $base_styles_clean . 
            " padding: {$padding['vertical']}px {$padding['horizontal']}px;" .
            " text-align: {$layout_props['textAlign']};" .
            " color: {$colors['text']};" .
            " font-family: {$body_font['family']};" .
            " font-size: {$body_font['size']}px;" .
            " font-weight: {$body_font['weight']};" .
            " font-style: {$body_font['style']};" .
            " box-sizing: border-box;" .
            $letter_spacing;
        
        // Alignement vertical via flexbox
        $container_styles .= ' display: flex; flex-direction: column;';
        if ($layout_props['verticalAlign'] === 'middle') {
            $container_styles .= ' justify-content: center;';
        } elseif ($layout_props['verticalAlign'] === 'bottom') {
            $container_styles .= ' justify-content: flex-end;';
        } else {
            $container_styles .= ' justify-content: flex-start;';
        }
        
        // Style header
        $header_style = "color: {$colors['header']}; font-family: {$header_font['family']}; font-size: {$header_font['size']}px; font-weight: {$header_font['weight']}; font-style: {$header_font['style']}; margin-bottom: 4px;";
        
        // Styles pour chaque ligne de body
        $line_style_base = "font-size: {$body_font['size']}px; font-family: {$body_font['family']}; font-weight: {$body_font['weight']}; font-style: {$body_font['style']}; color: {$colors['text']}; margin: 0; padding: 0;";
        
        // Line-height : activer pour Puppeteer, désactiver pour DomPDF
        if ($this->current_engine_name === 'puppeteer' && isset($element['lineHeight']) && $element['lineHeight'] !== '' && $element['lineHeight'] !== 'normal') {
            $line_style_base .= " line-height: {$element['lineHeight']};";
            error_log("[PDF Builder] customer_info: Puppeteer détecté, line-height activé: {$element['lineHeight']}");
        } else {
            error_log("[PDF Builder] customer_info: Moteur " . $this->current_engine_name . ", line-height désactivé");
        }
        
        // Génération HTML - margin-bottom comme gap React pour compatibilité DOMPDF
        $html = '<div class="element" style="' . $container_styles . '">';
        if ($show['headers']) {
            $html .= '<div style="' . $header_style . '">Informations Client</div>';
        }
        // Chaque ligne sans margin-bottom (espacement serré)
        $total_lines = count($lines);
        foreach ($lines as $index => $line) {
            $is_last = ($index === $total_lines - 1);
            $html .= '<div style="' . $line_style_base . '">' . $line . '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Rendu des informations entreprise
     */
    private function render_company_info_element($element, $order_data, $base_styles, $is_premium = false, $format = 'html') {
        // Extraction des propriétés via helpers
        $padding = $this->extract_padding($element);
        $layout_props = $this->extract_layout_props($element);
        
        // Thèmes prédéfinis
        $themes = [
            'corporate' => ['backgroundColor' => '#ffffff', 'borderColor' => '#1f2937', 'textColor' => '#374151', 'headerTextColor' => '#111827'],
            'modern' => ['backgroundColor' => '#ffffff', 'borderColor' => '#3b82f6', 'textColor' => '#1e40af', 'headerTextColor' => '#1e3a8a'],
            'elegant' => ['backgroundColor' => '#ffffff', 'borderColor' => '#8b5cf6', 'textColor' => '#6d28d9', 'headerTextColor' => '#581c87'],
            'minimal' => ['backgroundColor' => '#ffffff', 'borderColor' => '#e5e7eb', 'textColor' => '#374151', 'headerTextColor' => '#111827'],
            'professional' => ['backgroundColor' => '#ffffff', 'borderColor' => '#059669', 'textColor' => '#047857', 'headerTextColor' => '#064e3b'],
        ];
        
        $theme = $themes[$element['theme'] ?? 'corporate'] ?? $themes['corporate'];
        $colors = [
            'text' => $element['textColor'] ?? $theme['textColor'],
            'header' => $element['headerTextColor'] ?? $theme['headerTextColor'],
            'background' => $element['backgroundColor'] ?? $theme['backgroundColor'],
            'border' => $element['borderColor'] ?? $theme['borderColor']
        ];
        
        $header_font = $this->extract_font_props($element, 'header', ['size' => 14, 'weight' => 'bold']);
        $body_font = $this->extract_font_props($element, 'body', ['size' => 12]);
        
        // Helper pour formater le téléphone
        $format_phone = function($phone) {
            if (empty($phone)) return '';
            $cleaned = preg_replace('/\D/', '', $phone);
            return $cleaned ? implode('.', str_split($cleaned, 2)) : '';
        };
        
        // Helper pour convertir en string
        $to_string = function($value) {
            return is_array($value) ? ($value[0] ?? '') : ($value ?? '');
        };
        
        // Données de l'entreprise
        $company = [
            'name' => $to_string($element['companyName'] ?? get_bloginfo('name')),
            'address' => $to_string($element['companyAddress'] ?? get_option('woocommerce_store_address', '')),
            'city' => $to_string($element['companyCity'] ?? get_option('woocommerce_store_city', '')),
            'email' => $to_string($element['companyEmail'] ?? get_option('admin_email', '')),
            'phone' => $format_phone($to_string($element['companyPhone'] ?? get_option('woocommerce_store_phone', ''))),
            'siret' => $to_string($element['companySiret'] ?? get_option('pdf_builder_company_siret', '')),
            'rcs' => $to_string($element['companyRcs'] ?? get_option('pdf_builder_company_rcs', '')),
            'tva' => $to_string($element['companyTva'] ?? pdf_builder_get_option('pdf_builder_company_vat', '')),
            'capital' => $to_string($element['companyCapital'] ?? get_option('pdf_builder_company_capital', ''))
        ];
        
        // Ajouter € au capital si absent
        if ($company['capital'] && strpos($company['capital'], '€') === false) {
            $company['capital'] .= ' €';
        }
        
        // Options d'affichage
        $show = [
            'name' => $element['showCompanyName'] ?? true,
            'address' => $element['showAddress'] ?? true,
            'email' => $element['showEmail'] ?? true,
            'phone' => $element['showPhone'] ?? true,
            'siret' => $element['showSiret'] ?? true,
            'vat' => $element['showVat'] ?? true,
            'rcs' => $element['showRcs'] ?? true,
            'capital' => $element['showCapital'] ?? true
        ];
        
        // Construction des lignes
        $lines = [];
        $add_line = function($content) use (&$lines) { if ($content) $lines[] = $content; };
        
        if ($layout_props['layout'] === 'vertical') {
            if ($show['name']) $add_line('<strong>' . esc_html($company['name']) . '</strong>');
            if ($show['address'] && $company['address']) {
                $add_line(esc_html($company['address']));
                if ($company['city']) $add_line(esc_html($company['city']));
            }
            // Email et Téléphone AVANT les infos légales
            if ($show['email'] && $company['email']) $add_line(esc_html($company['email']));
            if ($show['phone'] && $company['phone']) $add_line(esc_html($company['phone']));
            // Infos légales après
            if ($show['siret'] && $company['siret']) $add_line('SIRET: ' . esc_html($company['siret']));
            if ($show['vat'] && $company['tva']) $add_line('TVA: ' . esc_html($company['tva']));
            if ($show['rcs'] && $company['rcs']) $add_line('RCS: ' . esc_html($company['rcs']));
            if ($show['capital'] && $company['capital']) $add_line('Capital: ' . esc_html($company['capital']));
        } elseif ($layout_props['layout'] === 'horizontal') {
            if ($show['name']) $add_line('<strong>' . esc_html($company['name']) . '</strong>');
            
            $parts = [];
            if ($show['address'] && $company['address']) {
                $addr = esc_html($company['address']);
                if ($company['city']) $addr .= ', ' . esc_html($company['city']);
                $parts[] = $addr;
            }
            if ($parts) $add_line(implode('', $parts));
            
            $parts = [];
            if ($show['email'] && $company['email']) $parts[] = esc_html($company['email']);
            if ($show['phone'] && $company['phone']) $parts[] = esc_html($company['phone']);
            if ($parts) $add_line(implode(' | ', $parts));
            
            $parts = [];
            if ($show['siret'] && $company['siret']) $parts[] = 'SIRET: ' . esc_html($company['siret']);
            if ($show['rcs'] && $company['rcs']) $parts[] = 'RCS: ' . esc_html($company['rcs']);
            if ($show['vat'] && $company['tva']) $parts[] = 'TVA: ' . esc_html($company['tva']);
            if ($show['capital'] && $company['capital']) $parts[] = 'Capital: ' . esc_html($company['capital']);
            if ($parts) $add_line(implode(' | ', $parts));
        } else { // compact
            if ($show['name']) $add_line('<strong>' . esc_html($company['name']) . '</strong>');
            
            $parts = [];
            if ($show['address'] && $company['address']) $parts[] = esc_html($company['address']);
            if ($show['email'] && $company['email']) $parts[] = esc_html($company['email']);
            if ($show['phone'] && $company['phone']) $parts[] = esc_html($company['phone']);
            if ($show['siret'] && $company['siret']) $parts[] = 'SIRET: ' . esc_html($company['siret']);
            if ($show['vat'] && $company['tva']) $parts[] = 'TVA: ' . esc_html($company['tva']);
            if ($show['rcs'] && $company['rcs']) $parts[] = 'RCS: ' . esc_html($company['rcs']);
            if ($parts) $add_line(implode(' • ', $parts));
        }
        
        // Styles - nettoyer TOUS les padding pour DOMPDF (comme customer_info)
        $base_styles_clean = preg_replace('/padding(-top|-bottom|-left|-right)?:\s*[^;]+;/i', '', $base_styles);
        // NE PAS retirer les !important car ils sont nécessaires pour le positionnement
        // Le str_replace('!important', '') causait un mauvais positionnement des éléments
        
        $letter_spacing = $layout_props['letterSpacing'] ? " letter-spacing: {$layout_props['letterSpacing']}px;" : '';
        $container_styles = $base_styles_clean . 
            "; text-align: {$layout_props['textAlign']};" .
            " color: {$colors['text']};" .
            " font-family: {$body_font['family']};" .
            " font-size: {$body_font['size']}px;" .
            " font-weight: {$body_font['weight']};" .
            " font-style: {$body_font['style']};" .
            " box-sizing: border-box;" .
            $letter_spacing .
            ' width: 100%; height: 100%;';
        
        // Utiliser les propriétés de padding personnalisables depuis l'élément
        $padding_top = isset($element['paddingTop']) ? intval($element['paddingTop']) : 8;
        $padding_horizontal = isset($element['paddingHorizontal']) ? intval($element['paddingHorizontal']) : 12;
        $padding_bottom = isset($element['paddingBottom']) ? intval($element['paddingBottom']) : 12;
        $line_spacing = isset($element['lineSpacing']) ? intval($element['lineSpacing']) : 2;
        
        $container_styles .= " padding: {$padding_top}px {$padding_horizontal}px {$padding_bottom}px {$padding_horizontal}px;";
        
        // Alignement vertical
        if ($layout_props['verticalAlign'] === 'middle') {
            $container_styles .= ' display: flex; flex-direction: column; justify-content: center;';
        } elseif ($layout_props['verticalAlign'] === 'bottom') {
            $container_styles .= ' display: flex; flex-direction: column; justify-content: flex-end;';
        }
        
        // Style pour <strong>
        $strong_style = "color: {$colors['header']}; font-weight: {$header_font['weight']}; font-size: {$header_font['size']}px; font-family: {$header_font['family']}; font-style: {$header_font['style']};";
        
        // Traiter les lignes pour ajouter les styles aux balises <strong>
        $processedLines = array_map(function($line) use ($strong_style) {
            // Remplacer <strong> par <strong style="...">
            return preg_replace('/<strong>/', '<strong style="' . $strong_style . '">', $line);
        }, $lines);
        
        // Style de base pour chaque ligne
        $line_style = "margin: 0; padding: 0;";
        
        // Line-height : activer pour Puppeteer, désactiver pour DomPDF
        if ($this->current_engine_name === 'puppeteer' && isset($element['lineHeight']) && $element['lineHeight'] !== '' && $element['lineHeight'] !== 'normal') {
            $line_style .= " line-height: {$element['lineHeight']};";
            error_log("[PDF Builder] company_info: Puppeteer détecté, line-height activé: {$element['lineHeight']}");
        } else {
            error_log("[PDF Builder] company_info: Moteur " . $this->current_engine_name . ", line-height désactivé");
        }
        
        // Génération HTML - sans espacement entre les lignes
        $html = '<div class="element" style="' . $container_styles . '">';
        // Chaque ligne avec line-height si Puppeteer
        foreach ($processedLines as $line) {
            $html .= '<div style="' . $line_style . '">' . $line . '</div>';
        }
        $html .= '</div>'; // Fermer element container
        
        return $html;
    }
    
    /**
     * Rendu du logo entreprise
     */
    private function render_company_logo($element, $base_styles) {
        $src = $element['src'] ?? '';
        if (!$src) return '';
        
        // Extraire les dimensions du conteneur
        $container_width = $element['width'] ?? 100;
        $container_height = $element['height'] ?? 100;
        
        // Propriétés de style
        $object_fit = $element['objectFit'] ?? 'contain';
        $opacity = isset($element['opacity']) ? floatval($element['opacity']) : 1;
        $border_radius = isset($element['borderRadius']) ? intval($element['borderRadius']) : 0;
        $background_color = $element['backgroundColor'] ?? 'transparent';
        
        // Convertir l'image en base64 pour compatibilité Dompdf
        $image_data = $this->get_image_as_base64($src);
        if ($image_data) {
            $src = $image_data;
        }
        
        // IMPORTANT: Retirer les styles de bordure de $base_styles
        $base_styles = preg_replace('/\s*border[^;]*;/', '', $base_styles);
        $base_styles = preg_replace('/\s*border-radius[^;]*;/', '', $base_styles);
        
        // Obtenir les dimensions naturelles de l'image (approximation)
        $image_natural_width = 512;
        $image_natural_height = 512;
        
        // Calculer les dimensions de l'image rendue selon objectFit
        $container_aspect = $container_width / $container_height;
        $image_aspect = $image_natural_width / $image_natural_height;
        
        $logo_width = 0;
        $logo_height = 0;
        
        switch ($object_fit) {
            case 'contain':
                if ($container_aspect > $image_aspect) {
                    $logo_height = $container_height;
                    $logo_width = $logo_height * $image_aspect;
                } else {
                    $logo_width = $container_width;
                    $logo_height = $logo_width / $image_aspect;
                }
                break;
            
            case 'cover':
                if ($container_aspect > $image_aspect) {
                    $logo_width = $container_width;
                    $logo_height = $logo_width / $image_aspect;
                } else {
                    $logo_height = $container_height;
                    $logo_width = $logo_height * $image_aspect;
                }
                break;
            
            case 'fill':
                $logo_width = $container_width;
                $logo_height = $container_height;
                break;
            
            case 'none':
                $logo_width = min($image_natural_width, $container_width);
                $logo_height = min($image_natural_height, $container_height);
                break;
            
            case 'scale-down':
                if ($image_natural_width <= $container_width && $image_natural_height <= $container_height) {
                    $logo_width = $image_natural_width;
                    $logo_height = $image_natural_height;
                } else {
                    if ($container_aspect > $image_aspect) {
                        $logo_height = $container_height;
                        $logo_width = $logo_height * $image_aspect;
                    } else {
                        $logo_width = $container_width;
                        $logo_height = $logo_width / $image_aspect;
                    }
                }
                break;
            
            default:
                if ($container_aspect > $image_aspect) {
                    $logo_height = $container_height;
                    $logo_width = $logo_height * $image_aspect;
                } else {
                    $logo_width = $container_width;
                    $logo_height = $logo_width / $image_aspect;
                }
        }
        
        // Centrer l'image dans le conteneur avec position absolue (comme React Canvas)
        $image_x = ($container_width - $logo_width) / 2;
        $image_y = ($container_height - $logo_height) / 2;
        
        // Styles du conteneur externe (sans padding pour éviter le décalage)
        $outer_div_styles = $base_styles;
        if ($background_color !== 'transparent') {
            $outer_div_styles .= ' background-color: ' . esc_attr($background_color) . ';';
        }
        $outer_div_styles .= ' overflow: hidden;';
        $outer_div_styles .= ' box-sizing: border-box;';
        
        // Styles de l'image avec positionnement absolu (cohérent avec React)
        $img_styles = 'position: absolute;';
        $img_styles .= ' left: ' . round($image_x, 2) . 'px;';
        $img_styles .= ' top: ' . round($image_y, 2) . 'px;';
        $img_styles .= ' width: ' . round($logo_width, 2) . 'px;';
        $img_styles .= ' height: ' . round($logo_height, 2) . 'px;';
        $img_styles .= ' display: block;';
        
        // Opacité
        if ($opacity < 1) {
            $img_styles .= ' opacity: ' . esc_attr($opacity) . ';';
        }
        
        // BorderRadius sur l'image
        if ($border_radius > 0) {
            $img_styles .= ' border-radius: ' . esc_attr($border_radius) . 'px;';
        }
        
        // Rendu : conteneur + image en position absolue (plus de wrapper ni padding)
        return '<div class="element" style="' . $outer_div_styles . '">
                <img src="' . esc_attr($src) . '" style="' . $img_styles . '" />
        </div>';
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
     * Helper: Map text-align CSS values to flexbox justify-content values
     * @param string $text_align CSS text-align value (left, center, right, etc.)
     * @return string flexbox justify-content value
     */
    private function map_text_align_to_justify_content($text_align) {
        switch ($text_align) {
            case 'center':
                return 'center';
            case 'right':
                return 'flex-end';
            case 'left':
            default:
                return 'flex-start';
        }
    }
    
    /**
     * Rendu d'une ligne de séparation
     */
    private function render_line($element, $base_styles) {
        $color = $element['strokeColor'] ?? '#000000';
        $width = $element['strokeWidth'] ?? 1;
        $style = $element['borderStyle'] ?? $element['style'] ?? 'solid';
        
        // Version simplifiée sans flex pour meilleure compatibilité impression
        // Créer une div interne centrée verticalement avec position relative
        $line_styles = $base_styles . ' overflow: hidden;';
        
        // Calculer la position verticale pour centrer la ligne dans le conteneur
        $element_height = $element['height'] ?? 20;
        $top_offset = ($element_height - $width) / 2;
        
        // Styles pour la div interne (la vraie ligne)
        // IMPORTANT: Utiliser border au lieu de background-color pour compatibilité impression
        // (les navigateurs désactivent background-color par défaut lors de l'impression)
        $inner_style = "position: relative; top: {$top_offset}px; width: 100%;";
        
        if ($style === 'dashed') {
            $inner_style .= " border-bottom: {$width}px dashed {$color};";
        } elseif ($style === 'dotted') {
            $inner_style .= " border-bottom: {$width}px dotted {$color};";
        } else {
            // Solid : utiliser border solid au lieu de background-color
            $inner_style .= " border-bottom: {$width}px solid {$color}; height: 0;";
        }
        
        return '<div class="element" style="' . $line_styles . '"><div style="' . $inner_style . '"></div></div>';
    }
    
    /**
     * Rendu d'un rectangle
     */
    private function render_rectangle($element, $base_styles) {
        // Couleur de fond (fillColor ou backgroundColor)
        $backgroundColor = $element['fillColor'] ?? $element['backgroundColor'] ?? 'transparent';
        
        // Bordure (strokeColor/strokeWidth ou borderColor/borderWidth)
        $borderColor = $element['strokeColor'] ?? $element['borderColor'] ?? '#000000';
        $borderWidth = $element['strokeWidth'] ?? $element['borderWidth'] ?? 0;
        
        // Border radius
        $borderRadius = $element['borderRadius'] ?? 0;
        
        $rect_styles = $base_styles . ' background-color: ' . $backgroundColor . ';';
        
        if ($borderWidth > 0) {
            $rect_styles .= ' border: ' . $borderWidth . 'px solid ' . $borderColor . ';';
        }
        
        if ($borderRadius > 0) {
            $rect_styles .= ' border-radius: ' . $borderRadius . 'px;';
        }
        
        $rect_styles .= ' box-sizing: border-box;';
        
        return '<div class="element pdf-rectangle" style="' . $rect_styles . '"></div>';
    }
    
    /**
     * Rendu d'un cercle
     */
    private function render_circle($element, $base_styles) {
        // Couleur de fond (fillColor ou backgroundColor)
        $backgroundColor = $element['fillColor'] ?? $element['backgroundColor'] ?? 'transparent';
        
        // Bordure (strokeColor/strokeWidth ou borderColor/borderWidth)
        $borderColor = $element['strokeColor'] ?? $element['borderColor'] ?? '#000000';
        $borderWidth = $element['strokeWidth'] ?? $element['borderWidth'] ?? 0;
        
        $circle_styles = $base_styles . ' background-color: ' . $backgroundColor . ';';
        $circle_styles .= ' border-radius: 50%;'; // Clé pour faire un cercle
        
        if ($borderWidth > 0) {
            $circle_styles .= ' border: ' . $borderWidth . 'px solid ' . $borderColor . ';';
        }
        
        $circle_styles .= ' box-sizing: border-box;';
        
        return '<div class="element pdf-circle" style="' . $circle_styles . '"></div>';
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
     * Rendu de la date de commande
     */
    private function render_order_date($element, $order_data, $base_styles) {
        // Récupérer la date de la commande
        $date_string = $order_data['order']['date'] ?? '';
        if (empty($date_string)) {
            return '<div class="element" style="' . $base_styles . '">Date non disponible</div>';
        }

        // Créer un objet DateTime
        try {
            $date = new \DateTime($date_string);
        } catch (\Exception $e) {
            return '<div class="element" style="' . $base_styles . '">' . esc_html($date_string) . '</div>';
        }

        // Formater la date selon le format spécifié
        $format = $element['dateFormat'] ?? 'd/m/Y';
        $show_time = $element['showTime'] ?? false;

        // Convertir le format PHP en format date()
        $formatted_date = $this->format_date_php($date, $format);

        // Ajouter l'heure si nécessaire
        if ($show_time) {
            $formatted_date .= ' ' . $date->format('H:i');
        }

        // Gestion du label
        $show_label = $element['showLabel'] ?? true;
        $label_text = $element['labelText'] ?? 'Date de la facture :';
        $label_position = $element['labelPosition'] ?? 'left';
        $label_spacing = $element['labelSpacing'] ?? 8;

        // Propriétés du label
        $label_font_family = $element['labelFontFamily'] ?? ($element['fontFamily'] ?? 'DejaVu Sans');
        $label_font_size = $element['labelFontSize'] ?? ($element['fontSize'] ?? 12);
        $label_font_weight = $element['labelFontWeight'] ?? 'normal';
        $label_font_style = $element['labelFontStyle'] ?? 'normal';
        $label_color = $element['labelColor'] ?? ($element['textColor'] ?? ($element['color'] ?? '#000000'));

        // Propriétés de la date (priorité: textColor > color)
        $date_font_family = $element['fontFamily'] ?? 'DejaVu Sans';
        $date_font_size = $element['fontSize'] ?? 12;
        $date_font_weight = $element['fontWeight'] ?? 'normal';
        $date_font_style = $element['fontStyle'] ?? 'normal';
        $date_color = $element['textColor'] ?? ($element['color'] ?? '#000000');
        $text_align = $element['textAlign'] ?? 'left';
        $vertical_align = $element['verticalAlign'] ?? 'top';

        // Récupérer le padding (cohérence avec React Canvas)
        $padding_top = $element['padding']['top'] ?? $element['paddingTop'] ?? 0;
        $padding_right = $element['padding']['right'] ?? $element['paddingRight'] ?? 0;
        $padding_bottom = $element['padding']['bottom'] ?? $element['paddingBottom'] ?? 0;
        $padding_left = $element['padding']['left'] ?? $element['paddingLeft'] ?? 0;

        if ($show_label) {
            // Avec label : utiliser flexbox pour positionner label + date
            $container_styles = $base_styles . ' display: flex !important; line-height: 1 !important;';
            
            // Appliquer le padding pour cohérence avec React Canvas
            $container_styles .= " padding: {$padding_top}px {$padding_right}px {$padding_bottom}px {$padding_left}px !important; box-sizing: border-box !important;";
            
            // Styles pour le label et la date
            $label_styles = "font-family: \"{$label_font_family}\" !important; font-size: {$label_font_size}px !important; font-weight: {$label_font_weight} !important; font-style: {$label_font_style} !important; color: {$label_color} !important; line-height: 1 !important; margin: 0 !important;";
            $date_styles = "font-family: \"{$date_font_family}\" !important; font-size: {$date_font_size}px !important; font-weight: {$date_font_weight} !important; font-style: {$date_font_style} !important; color: {$date_color} !important; line-height: 1 !important; margin: 0 !important;";

            // Layout selon la position du label
            switch ($label_position) {
                case 'top':
                case 'bottom':
                    // Direction verticale (colonne)
                    $container_styles .= ' flex-direction: column !important;';
                    
                    // justify-content contrôle l'axe vertical (principal)
                    if ($vertical_align === 'middle') {
                        $container_styles .= ' justify-content: center !important;';
                    } elseif ($vertical_align === 'bottom') {
                        $container_styles .= ' justify-content: flex-end !important;';
                    } else {
                        $container_styles .= ' justify-content: flex-start !important;';
                    }
                    
                    // align-items contrôle l'axe horizontal (transversal)
                    if ($text_align === 'center') {
                        $container_styles .= ' align-items: center !important;';
                    } elseif ($text_align === 'right') {
                        $container_styles .= ' align-items: flex-end !important;';
                    } else {
                        $container_styles .= ' align-items: flex-start !important;';
                    }
                    
                    if ($label_position === 'top') {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $label_styles . ' margin-bottom: ' . $label_spacing . 'px;">' . esc_html($label_text) . '</span>';
                        $html .= '<span style="' . $date_styles . '">' . esc_html($formatted_date) . '</span>';
                    } else {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $date_styles . ' margin-bottom: ' . $label_spacing . 'px;">' . esc_html($formatted_date) . '</span>';
                        $html .= '<span style="' . $label_styles . '">' . esc_html($label_text) . '</span>';
                    }
                    break;

                case 'right':
                case 'left':
                default:
                    // Direction horizontale (ligne)
                    $container_styles .= ' flex-direction: row !important;';
                    
                    // justify-content contrôle l'axe horizontal (principal)
                    if ($text_align === 'center') {
                        $container_styles .= ' justify-content: center !important;';
                    } elseif ($text_align === 'right') {
                        $container_styles .= ' justify-content: flex-end !important;';
                    } else {
                        $container_styles .= ' justify-content: flex-start !important;';
                    }
                    
                    // align-items contrôle l'axe vertical (transversal)
                    if ($vertical_align === 'middle') {
                        $container_styles .= ' align-items: center !important;';
                    } elseif ($vertical_align === 'bottom') {
                        $container_styles .= ' align-items: flex-end !important;';
                    } else {
                        $container_styles .= ' align-items: flex-start !important;';
                    }
                    
                    if ($label_position === 'right') {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $date_styles . ' margin-right: ' . $label_spacing . 'px;">' . esc_html($formatted_date) . '</span>';
                        $html .= '<span style="' . $label_styles . '">' . esc_html($label_text) . '</span>';
                    } else {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $label_styles . ' margin-right: ' . $label_spacing . 'px;">' . esc_html($label_text) . '</span>';
                        $html .= '<span style="' . $date_styles . '">' . esc_html($formatted_date) . '</span>';
                    }
                    break;
            }
            
            $html .= '</div>';
            return $html;
        } else {
            // Sans label : affichage simple de la date avec textAlign et verticalAlign
            $container_styles = $base_styles . ' display: flex !important; line-height: 1 !important;';
            
            // Appliquer le padding pour cohérence avec React Canvas
            $container_styles .= " padding: {$padding_top}px {$padding_right}px {$padding_bottom}px {$padding_left}px !important; box-sizing: border-box !important;";
            
            // Alignement vertical (justify-content car on va probablement utiliser column)
            if ($vertical_align === 'middle') {
                $container_styles .= ' justify-content: center !important;';
            } elseif ($vertical_align === 'bottom') {
                $container_styles .= ' justify-content: flex-end !important;';
            } else {
                $container_styles .= ' justify-content: flex-start !important;';
            }
            
            // Alignement horizontal (align-items pour column)
            if ($text_align === 'center') {
                $container_styles .= ' align-items: center !important;';
            } elseif ($text_align === 'right') {
                $container_styles .= ' align-items: flex-end !important;';
            } else {
                $container_styles .= ' align-items: flex-start !important;';
            }
            
            // Utiliser column pour que justify-content contrôle le vertical
            $container_styles .= ' flex-direction: column !important;';
            
            $date_styles = "font-family: \"{$date_font_family}\" !important; font-size: {$date_font_size}px !important; font-weight: {$date_font_weight} !important; font-style: {$date_font_style} !important; color: {$date_color} !important; line-height: 1 !important; margin: 0 !important;";
            return '<div class="element" style="' . $container_styles . '"><span style="' . $date_styles . '">' . esc_html($formatted_date) . '</span></div>';;
        }
    }

    /**
     * Formate une date selon le format PHP spécifié
     */
    private function format_date_php($date, $format) {
        // Mapping des formats personnalisés vers format() de PHP
        $months_fr = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];
        
        $months_short_fr = [
            1 => 'jan', 2 => 'fév', 3 => 'mar', 4 => 'avr',
            5 => 'mai', 6 => 'juin', 7 => 'juil', 8 => 'août',
            9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'déc'
        ];
        
        $days_fr = [
            0 => 'dimanche', 1 => 'lundi', 2 => 'mardi', 3 => 'mercredi',
            4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi'
        ];
        
        $days_short_fr = [
            0 => 'dim', 1 => 'lun', 2 => 'mar', 3 => 'mer',
            4 => 'jeu', 5 => 'ven', 6 => 'sam'
        ];

        switch ($format) {
            case 'd/m/Y':
                return $date->format('d/m/Y');
            case 'm/d/Y':
                return $date->format('m/d/Y');
            case 'Y-m-d':
                return $date->format('Y-m-d');
            case 'd-m-Y':
                return $date->format('d-m-Y');
            case 'd.m.Y':
                return $date->format('d.m.Y');
            case 'j F Y':
                return $date->format('j') . ' ' . $months_fr[(int)$date->format('n')] . ' ' . $date->format('Y');
            case 'l j F Y':
                return $days_fr[(int)$date->format('w')] . ' ' . $date->format('j') . ' ' . $months_fr[(int)$date->format('n')] . ' ' . $date->format('Y');
            case 'F j, Y':
                return $months_fr[(int)$date->format('n')] . ' ' . $date->format('j') . ', ' . $date->format('Y');
            case 'D, M j, Y':
                return $days_short_fr[(int)$date->format('w')] . ', ' . $months_short_fr[(int)$date->format('n')] . ' ' . $date->format('j') . ', ' . $date->format('Y');
            default:
                return $date->format('d/m/Y');
        }
    }
    
    /**
     * Rendu du numéro de facture
     */
    private function render_invoice_number($element, $order_data, $base_styles) {
        // Récupérer le numéro de facture
        $invoice_number = 'INV-' . $order_data['order']['order_number'];
        
        // Ajouter prefix et suffix si définis
        $prefix = $element['prefix'] ?? '';
        $suffix = $element['suffix'] ?? '';
        $display_number = $prefix . $invoice_number . $suffix;

        // Gestion du label
        $show_label = $element['showLabel'] ?? true;
        $label_text = $element['labelText'] ?? 'Numéro de facture :';
        $label_position = $element['labelPosition'] ?? 'left';
        $label_spacing = $element['labelSpacing'] ?? 8;

        // Propriétés du label
        $label_font_family = $element['labelFontFamily'] ?? ($element['fontFamily'] ?? 'DejaVu Sans');
        $label_font_size = $element['labelFontSize'] ?? ($element['fontSize'] ?? 12);
        $label_font_weight = $element['labelFontWeight'] ?? 'normal';
        $label_font_style = $element['labelFontStyle'] ?? 'normal';
        $label_color = $element['labelColor'] ?? ($element['textColor'] ?? ($element['color'] ?? '#000000'));

        // Propriétés du numéro (priorité: textColor > color)
        $number_font_family = $element['fontFamily'] ?? 'DejaVu Sans';
        $number_font_size = $element['fontSize'] ?? 12;
        $number_font_weight = $element['fontWeight'] ?? 'normal';
        $number_font_style = $element['fontStyle'] ?? 'normal';
        $number_color = $element['textColor'] ?? ($element['color'] ?? '#000000');
        $text_align = $element['textAlign'] ?? 'left';
        $vertical_align = $element['verticalAlign'] ?? 'top';

        // Récupérer le padding (cohérence avec React Canvas)
        $padding_top = $element['padding']['top'] ?? $element['paddingTop'] ?? 0;
        $padding_right = $element['padding']['right'] ?? $element['paddingRight'] ?? 0;
        $padding_bottom = $element['padding']['bottom'] ?? $element['paddingBottom'] ?? 0;
        $padding_left = $element['padding']['left'] ?? $element['paddingLeft'] ?? 0;

        if ($show_label) {
            // Avec label : utiliser flexbox pour positionner label + numéro
            $container_styles = $base_styles . ' display: flex !important; line-height: 1 !important;';
            
            // Appliquer le padding pour cohérence avec React Canvas
            $container_styles .= " padding: {$padding_top}px {$padding_right}px {$padding_bottom}px {$padding_left}px !important; box-sizing: border-box !important;";
            
            // Styles pour le label et le numéro
            $label_styles = "font-family: \"{$label_font_family}\" !important; font-size: {$label_font_size}px !important; font-weight: {$label_font_weight} !important; font-style: {$label_font_style} !important; color: {$label_color} !important; line-height: 1 !important; margin: 0 !important;";
            $number_styles = "font-family: \"{$number_font_family}\" !important; font-size: {$number_font_size}px !important; font-weight: {$number_font_weight} !important; font-style: {$number_font_style} !important; color: {$number_color} !important; line-height: 1 !important; margin: 0 !important;";

            // Layout selon la position du label
            switch ($label_position) {
                case 'top':
                case 'bottom':
                    // Direction verticale (colonne)
                    $container_styles .= ' flex-direction: column !important;';
                    
                    // justify-content contrôle l'axe vertical (principal)
                    if ($vertical_align === 'middle') {
                        $container_styles .= ' justify-content: center !important;';
                    } elseif ($vertical_align === 'bottom') {
                        $container_styles .= ' justify-content: flex-end !important;';
                    } else {
                        $container_styles .= ' justify-content: flex-start !important;';
                    }
                    
                    // align-items contrôle l'axe horizontal (transversal)
                    if ($text_align === 'center') {
                        $container_styles .= ' align-items: center !important;';
                    } elseif ($text_align === 'right') {
                        $container_styles .= ' align-items: flex-end !important;';
                    } else {
                        $container_styles .= ' align-items: flex-start !important;';
                    }
                    
                    if ($label_position === 'top') {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $label_styles . ' margin-bottom: ' . $label_spacing . 'px;">' . esc_html($label_text) . '</span>';
                        $html .= '<span style="' . $number_styles . '">' . esc_html($display_number) . '</span>';
                    } else {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $number_styles . ' margin-bottom: ' . $label_spacing . 'px;">' . esc_html($display_number) . '</span>';
                        $html .= '<span style="' . $label_styles . '">' . esc_html($label_text) . '</span>';
                    }
                    break;

                case 'right':
                case 'left':
                default:
                    // Direction horizontale (ligne)
                    $container_styles .= ' flex-direction: row !important;';
                    
                    // justify-content contrôle l'axe horizontal (principal)
                    if ($text_align === 'center') {
                        $container_styles .= ' justify-content: center !important;';
                    } elseif ($text_align === 'right') {
                        $container_styles .= ' justify-content: flex-end !important;';
                    } else {
                        $container_styles .= ' justify-content: flex-start !important;';
                    }
                    
                    // align-items contrôle l'axe vertical (transversal)
                    if ($vertical_align === 'middle') {
                        $container_styles .= ' align-items: center !important;';
                    } elseif ($vertical_align === 'bottom') {
                        $container_styles .= ' align-items: flex-end !important;';
                    } else {
                        $container_styles .= ' align-items: flex-start !important;';
                    }
                    
                    if ($label_position === 'right') {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $number_styles . ' margin-right: ' . $label_spacing . 'px;">' . esc_html($display_number) . '</span>';
                        $html .= '<span style="' . $label_styles . '">' . esc_html($label_text) . '</span>';
                    } else {
                        $html = '<div class="element" style="' . $container_styles . '">';
                        $html .= '<span style="' . $label_styles . ' margin-right: ' . $label_spacing . 'px;">' . esc_html($label_text) . '</span>';
                        $html .= '<span style="' . $number_styles . '">' . esc_html($display_number) . '</span>';
                    }
                    break;
            }
            
            $html .= '</div>';
            return $html;
        } else {
            // Sans label : affichage simple du numéro avec textAlign et verticalAlign
            $container_styles = $base_styles . ' display: flex !important; line-height: 1 !important;';
            
            // Appliquer le padding pour cohérence avec React Canvas
            $container_styles .= " padding: {$padding_top}px {$padding_right}px {$padding_bottom}px {$padding_left}px !important; box-sizing: border-box !important;";
            
            // Alignement vertical (justify-content car on va probablement utiliser column)
            if ($vertical_align === 'middle') {
                $container_styles .= ' justify-content: center !important;';
            } elseif ($vertical_align === 'bottom') {
                $container_styles .= ' justify-content: flex-end !important;';
            } else {
                $container_styles .= ' justify-content: flex-start !important;';
            }
            
            // Alignement horizontal (align-items pour column)
            if ($text_align === 'center') {
                $container_styles .= ' align-items: center !important;';
            } elseif ($text_align === 'right') {
                $container_styles .= ' align-items: flex-end !important;';
            } else {
                $container_styles .= ' align-items: flex-start !important;';
            }
            
            // Utiliser column pour que justify-content contrôle le vertical
            $container_styles .= ' flex-direction: column !important;';
            
            $number_styles = "font-family: \"{$number_font_family}\" !important; font-size: {$number_font_size}px !important; font-weight: {$number_font_weight} !important; font-style: {$number_font_style} !important; color: {$number_color} !important; line-height: 1 !important; margin: 0 !important;";
            return '<div class="element" style="' . $container_styles . '"><span style="' . $number_styles . '">' . esc_html($display_number) . '</span></div>';
        }
    }
    
    /**
     * Rendu de texte dynamique
     * 
     * SOLUTION DOMPDF COMPATIBLE: Utilise margin-bottom entre les lignes
     * Comme customer_info et company_info - splitter le texte par \n
     */
    /**
     * Rendu de texte dynamique
     * 
     * SOLUTION DOMPDF COMPATIBLE: Utilise margin-bottom (= gap React)
     * Formule IDENTIQUE à React: gap = fontSize × (lineHeight - 1)
     */
    private function render_dynamic_text($element, $order_data, $base_styles) {
        $text = $element['text'] ?? $element['textTemplate'] ?? 'Signature du client';
        
        // Nettoyer TOUS les padding ET line-height du base_styles pour DOMPDF (comme customer_info)
        $base_styles_clean = preg_replace('/padding(-top|-bottom|-left|-right)?:\s*[^;]+;/i', '', $base_styles);
        $base_styles_clean = preg_replace('/line-height:\s*[^;]+;/', '', $base_styles_clean);
        // Retirer aussi les !important de position qui causent des conflits DOMPDF
        $base_styles_clean = str_replace('!important', '', $base_styles_clean);
        
        // Récupérer fontSize DIRECTEMENT DU JSON
        $font_size = isset($element['fontSize']) ? floatval($element['fontSize']) : 12;
        
        // Calculer le gap comme dans React Canvas: 4px d'espacement entre les lignes
        $gap = 4;
        
        // Détecter le moteur PDF utilisé
        $pdf_engine = pdf_builder_get_option('pdf_builder_engine', 'puppeteer');
        $is_puppeteer = ($pdf_engine === 'puppeteer' || $pdf_engine === 'browsershot');
        
        // Pour Puppeteer: utiliser line-height au lieu de margin-bottom
        // line-height = (fontSize + gap) / fontSize
        $line_height = $is_puppeteer ? round(($font_size + $gap) / $font_size, 2) : 1;
        
        // Extraire les propriétés de positionnement
        preg_match('/left:\s*[^;]+;/', $base_styles_clean, $left_match);
        preg_match('/top:\s*[^;]+;/', $base_styles_clean, $top_match);
        preg_match('/width:\s*[^;]+;/', $base_styles_clean, $width_match);
        preg_match('/height:\s*[^;]+;/', $base_styles_clean, $height_match);
        
        $position_styles = 'position: absolute; ' . 
                          ($left_match[0] ?? '') . ' ' . 
                          ($top_match[0] ?? '') . ' ' . 
                          ($width_match[0] ?? '') . ' ' . 
                          ($height_match[0] ?? '');
        
        // Extraire les propriétés de texte depuis base_styles
        preg_match('/font-family:\s*[^;]+;/', $base_styles_clean, $font_family_match);
        preg_match('/font-weight:\s*[^;]+;/', $base_styles_clean, $font_weight_match);
        preg_match('/font-style:\s*[^;]+;/', $base_styles_clean, $font_style_match);
        preg_match('/text-align:\s*[^;]+;/', $base_styles_clean, $text_align_match);
        preg_match('/color:\s*[^;]+;/', $base_styles_clean, $color_match);
        preg_match('/text-decoration:\s*[^;]+;/', $base_styles_clean, $text_decoration_match);
        preg_match('/text-transform:\s*[^;]+;/', $base_styles_clean, $text_transform_match);
        preg_match('/letter-spacing:\s*[^;]+;/', $base_styles_clean, $letter_spacing_match);
        
        $text_styles = ($font_family_match[0] ?? '') . ' ' . 
                      ($font_weight_match[0] ?? '') . ' ' . 
                      ($font_style_match[0] ?? '') . ' ' . 
                      ($text_align_match[0] ?? '') . ' ' . 
                      ($color_match[0] ?? '') . ' ' . 
                      ($text_decoration_match[0] ?? '') . ' ' . 
                      ($text_transform_match[0] ?? '') . ' ' .
                      ($letter_spacing_match[0] ?? '');
        
        // Convertir les balises <br> en sauts de ligne avant de splitter
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        
        // Splitter le texte par les sauts de ligne
        $lines = preg_split('/\r\n|\n|\r/', $text);
        
        // Générer HTML selon le moteur PDF
        $html = '<div class="element" style="' . $position_styles . ' margin: 0; padding: 0; box-sizing: border-box; overflow: hidden;">';
        
        if ($is_puppeteer) {
            // PUPPETEER: Utiliser line-height pour l'espacement
            foreach ($lines as $line) {
                $content = trim($line) === '' ? '&nbsp;' : esc_html($line);
                $html .= '<div style="margin: 0; padding: 0; font-size: ' . $font_size . 'px; line-height: ' . $line_height . '; ' . $text_styles . '">' . $content . '</div>';
            }
        } else {
            // DOMPDF: Utiliser margin-bottom pour l'espacement
            $total_lines = count($lines);
            foreach ($lines as $index => $line) {
                $is_last = ($index === $total_lines - 1);
                $line_margin = $is_last ? '' : " margin-bottom: {$gap}px;";
                $content = trim($line) === '' ? '&nbsp;' : esc_html($line);
                $html .= '<div style="margin: 0; padding: 0; font-size: ' . $font_size . 'px; line-height: 1;' . $line_margin . ' ' . $text_styles . '">' . $content . '</div>';
            }
        }
        
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
        
        // Convertir les balises <br> en sauts de ligne
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        
        // Nettoyer TOUS les padding ET line-height pour DOMPDF (comme customer_info)
        $base_styles_clean = preg_replace('/padding(-top|-bottom|-left|-right)?:\s*[^;]+;/i', '', $base_styles);
        $base_styles_clean = preg_replace('/line-height:\s*[^;]+;/', '', $base_styles_clean);
        // Retirer aussi les !important de position qui causent des conflits DOMPDF
        $base_styles_clean = str_replace('!important', '', $base_styles_clean);
        
        // Espacement entre lignes : line-height 1.1 (comme React Canvas ligne 3518)
        $font_size = isset($element['fontSize']) ? floatval($element['fontSize']) : 10;
        $line_height_multiplier = 1.1;
        
        // Détecter le moteur PDF utilisé
        $pdf_engine = pdf_builder_get_option('pdf_builder_engine', 'puppeteer');
        $is_puppeteer = ($pdf_engine === 'puppeteer' || $pdf_engine === 'browsershot');
        
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
            
            // 10px d'espacement après le séparateur (comme dans Canvas.tsx ligne 3467: y += 10)
            $hr_style = sprintf(
                'border: none; border-top: %dpx %s %s; margin: 0 0 10px 0; padding: 0; line-height: 0; height: %dpx; display: block;',
                $separator_width,
                $separator_style,
                $separator_color,
                $separator_width
            );
            
            $html .= '<hr style="' . $hr_style . '" />';
        }
        
        // Splitter le texte par lignes
        $lines = explode("\n", $text);
        
        // Générer HTML selon le moteur PDF
        if ($is_puppeteer) {
            // PUPPETEER: Utiliser line-height pour l'espacement (comme React Canvas ligne 3518)
            foreach ($lines as $line) {
                $content = trim($line) === '' ? '&nbsp;' : esc_html($line);
                $html .= '<div style="margin: 0; padding: 0; font-size: ' . $font_size . 'px; line-height: ' . $line_height_multiplier . '; ' . $text_styles . '">' . $content . '</div>';
            }
        } else {
            // DOMPDF: Utiliser margin-bottom pour l'espacement
            $margin_bottom = round($font_size * ($line_height_multiplier - 1)); // 10% du fontSize
            $total_lines = count($lines);
            foreach ($lines as $index => $line) {
                $is_last = ($index === $total_lines - 1);
                $line_margin = $is_last ? '' : " margin-bottom: {$margin_bottom}px;";
                $content = trim($line) === '' ? '&nbsp;' : esc_html($line);
                $html .= '<div style="margin: 0; padding: 0; font-size: ' . $font_size . 'px; line-height: 1;' . $line_margin . ' ' . $text_styles . '">' . $content . '</div>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * ========================================
     * FONCTIONS HELPER D'OPTIMISATION PDF
     * ========================================
     */

    /**
     * Initialise DOMPDF avec une configuration optimale et cohérente
    /**
     * Initialise DOMPDF avec configuration optimale
     * DOMPDF est le système principal - compatible avec tous les hébergements WordPress
     */
    private function init_dompdf($custom_options = []) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
        
        // Configuration par défaut optimale
        $default_options = [
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'fontHeightRatio' => 1.1,
            'isUnicode' => true,
            'enable_font_subsetting' => false,
            'defaultPaperSize' => 'A4',
            'dpi' => 96, // Cohérent avec React Canvas (96 DPI)
            'enable_php' => false, // Sécurité
            'enable_javascript' => false, // Sécurité
            'enable_remote' => true, // Pour charger des images distantes
            'chroot' => ABSPATH, // Limite au répertoire WordPress
        ];
        
        // Fusion avec options personnalisées
        $options = array_merge($default_options, $custom_options);
        
        $this->debug_log("DOMPDF initialisé avec options: " . json_encode($options));
        
        return new \Dompdf\Dompdf($options);
    }
    
    /**
     * Initialise mPDF avec configuration optimale (optionnel/fallback)
     * mPDF offre un meilleur support CSS que DOMPDF (line-height, etc.)
     * @deprecated Utiliser init_dompdf() à la place (mPDF nécessite dépendance supplémentaire)
     */
    private function init_mpdf($template_data = []) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';
        
        // Dimensions par défaut : A4 @ 96 DPI (794×1123px) - cohérent avec React Canvas
        $width = $template_data['canvasWidth'] ?? ($template_data['canvas']['width'] ?? 794);
        $height = $template_data['canvasHeight'] ?? ($template_data['canvas']['height'] ?? 1123);
        $orientation = ($width > $height) ? 'L' : 'P'; // L = Landscape, P = Portrait
        
        // Convertir pixels en millimètres (96 DPI: 1mm = 3.78px)
        $width_mm = round($width / 3.78, 2);
        $height_mm = round($height / 3.78, 2);
        
        // Configuration mPDF optimale
        $config = [
            'mode' => 'utf-8',
            'format' => [$width_mm, $height_mm],
            'orientation' => $orientation,
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'default_font' => 'dejavusans',
            'fontDir' => [PDF_BUILDER_PLUGIN_DIR . 'vendor/mpdf/mpdf/ttfonts'],
            'tempDir' => sys_get_temp_dir(),
            'dpi' => 96, // Cohérent avec React Canvas
            'img_dpi' => 96,
        ];
        
        $this->debug_log("mPDF initialisé: {$width}x{$height}px ({$width_mm}x{$height_mm}mm), orientation: {$orientation}");
        
        return new \Mpdf\Mpdf($config);
    }

    /**
     * Logger conditionnel - Log uniquement si le mode debug est activé
     * 
     * @param string $message Message à logger
     * @param string $level Niveau (INFO, WARNING, ERROR)
     */
    private function debug_log($message, $level = 'INFO') {
        // Vérifier si les logs debug sont activés
        $debug_enabled = get_option('pdf_builder_debug_enabled', false);
        $debug_php_errors = get_option('pdf_builder_developer_enabled', false);
        
        // Logger uniquement si debug activé OU en mode développement WordPress
        if ($debug_enabled || $debug_php_errors || (defined('WP_DEBUG') && WP_DEBUG)) {
            error_log("[PDF Builder - {$level}] {$message}");
        }
    }

    /**
     * Optimise le HTML pour le rendu PDF
     * - Nettoie les espaces inutiles
     * - Assure l'encodage UTF-8
     * - Supprime les commentaires HTML
     * - Normalise les sauts de ligne
     * 
     * @param string $html HTML brut
     * @return string HTML optimisé
     */
    private function optimize_html($html) {
        // Assurer l'encodage UTF-8 propre
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        
        // Supprimer les commentaires HTML (mais garder les commentaires conditionnels IE)
        $html = preg_replace('/<!--(?!\[if\s).*?-->/s', '', $html);
        
        // Supprimer les espaces multiples entre les balises
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Supprimer les retours à la ligne inutiles (sauf dans <pre> et <textarea>)
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Normaliser les sauts de ligne
        $html = str_replace(["\r\n", "\r"], "\n", $html);
        
        $this->debug_log("HTML optimisé - Taille avant: " . strlen($html) . " caractères");
        
        return $html;
    }

    /**
     * Configure le format papier pour DOMPDF à partir des données du template
     * 
     * @param \Dompdf\Dompdf $dompdf Instance DOMPDF
     * @param array $template_data Données du template
     * @return array [width, height, orientation]
     */
    private function configure_paper_size($dompdf, $template_data) {
        // Dimensions par défaut : A4 @ 96 DPI (794×1123px) - cohérent avec React Canvas
        $width = $template_data['canvasWidth'] ?? ($template_data['canvas']['width'] ?? 794);
        $height = $template_data['canvasHeight'] ?? ($template_data['canvas']['height'] ?? 1123);
        $orientation = ($width > $height) ? 'landscape' : 'portrait';
        
        // Convertir pixels en points (1px = 0.75pt pour DOMPDF)
        $width_pt = $width * 0.75;
        $height_pt = $height * 0.75;
        
        $dompdf->setPaper([0, 0, $width_pt, $height_pt], $orientation);
        
        $this->debug_log("Format papier: {$width}x{$height}px ({$width_pt}x{$height_pt}pt), orientation: {$orientation}");
        
        return [$width, $height, $orientation];
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

    /**
     * Récupère la liste des commandes WooCommerce pour le select d'aperçu
     */
    public function handle_get_orders_list() {
        try {
            // Vérifier le nonce
            if (!$this->nonce_manager->validate_ajax_request('get_orders_list')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            // Vérifier les permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Récupérer les commandes récentes (limitées à 50 pour performance)
            $args = [
                'limit' => 50,
                'orderby' => 'date',
                'order' => 'DESC',
                'status' => ['wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending'],
            ];

            $orders = wc_get_orders($args);
            $orders_list = [];

            foreach ($orders as $order) {
                $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                if (empty(trim($customer_name))) {
                    $customer_name = $order->get_billing_email() ?: 'Client anonyme';
                }

                // Formater le prix en texte brut (sans HTML) pour l'affichage dans le select
                $total = number_format($order->get_total(), 2, ',', ' ');
                $currency = get_woocommerce_currency_symbol($order->get_currency());
                // Décoder les entités HTML (&euro; → €)
                $currency = html_entity_decode($currency, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $total_formatted = $total . ' ' . $currency;

                $orders_list[] = [
                    'id' => $order->get_id(),
                    'number' => $order->get_order_number(),
                    'customer' => trim($customer_name),
                    'date' => $order->get_date_created()->date('d/m/Y'),
                    'total' => $total_formatted,
                ];
            }

            wp_send_json_success($orders_list);

        } catch (Exception $e) {
            $this->debug_log("Erreur lors de la récupération des commandes: " . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur lors de la récupération des commandes']);
        }
    }

    /**
     * Test de connexion Puppeteer
     * Handler AJAX pour tester uniquement le moteur Puppeteer
     */
    public function handle_test_puppeteer() {
        try {
            // Vérifier nonce
            check_ajax_referer('pdf_builder_test_puppeteer', '_ajax_nonce');

            // Vérifier permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            error_log("[PDF Engine Test] Test Puppeteer demandé");

            // Charger la factory
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/PDF/Engines/PDFEngineFactory.php';

            // Récupérer la configuration actuelle depuis wp_pdf_builder_settings
            $config = [
                'api_url' => pdf_builder_get_option('pdf_builder_puppeteer_url', ''),
                'api_token' => pdf_builder_get_option('pdf_builder_puppeteer_token', ''),
                'timeout' => pdf_builder_get_option('pdf_builder_puppeteer_timeout', 30),
                'fallback_enabled' => false, // Désactiver le fallback pour ce test
            ];

            // Valider la configuration
            if (empty($config['api_url'])) {
                wp_send_json_error([
                    'message' => 'URL Puppeteer non configurée. Veuillez renseigner l\'URL de votre serveur Puppeteer.'
                ]);
                return;
            }

            if (empty($config['api_token'])) {
                wp_send_json_error([
                    'message' => 'Token Puppeteer non configuré. Veuillez renseigner le token d\'authentification.'
                ]);
                return;
            }

            // Créer une instance du moteur Puppeteer
            $engine = \PDF_Builder\PDF\Engines\PDFEngineFactory::create('puppeteer', $config);

            if (!$engine) {
                wp_send_json_error([
                    'message' => 'Impossible de créer une instance du moteur Puppeteer'
                ]);
                return;
            }

            // Tester la connexion
            $test_result = $engine->test_connection();

            if ($test_result['success']) {
                error_log("[PDF Engine Test] Puppeteer OK");
                wp_send_json_success([
                    'message' => sprintf(
                        'Connexion Puppeteer réussie! 🎉<br>URL: %s<br>Temps de réponse: %dms',
                        esc_html($config['api_url']),
                        isset($test_result['response_time']) ? $test_result['response_time'] : 0
                    )
                ]);
            } else {
                error_log("[PDF Engine Test] Puppeteer ERREUR: " . $test_result['message']);
                wp_send_json_error([
                    'message' => sprintf(
                        'Échec de connexion Puppeteer ❌<br>URL: %s<br>Erreur: %s<br><br>Vérifiez que:<br>• Le serveur Puppeteer est démarré<br>• L\'URL est correcte<br>• Le token est valide',
                        esc_html($config['api_url']),
                        esc_html($test_result['message'])
                    )
                ]);
            }

        } catch (Exception $e) {
            error_log("[PDF Engine Test] Exception: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'Erreur lors du test: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test de tous les moteurs PDF
     * Handler AJAX pour tester tous les moteurs disponibles
     */
    public function handle_test_all_engines() {
        try {
            // Vérifier nonce
            check_ajax_referer('pdf_builder_test_engines', '_ajax_nonce');

            // Vérifier permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            error_log("[PDF Engine Test] Test de tous les moteurs demandé");

            // Charger la factory
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/PDF/Engines/PDFEngineFactory.php';

            // Tester tous les moteurs
            $results = \PDF_Builder\PDF\Engines\PDFEngineFactory::test_all_engines();

            if (empty($results)) {
                wp_send_json_error(['message' => 'Aucun moteur n\'a pu être testé']);
                return;
            }

            // Formatter les résultats pour l'affichage
            $formatted_results = [];

            foreach ($results as $engine_name => $result) {
                $status = $result['success'] ? 'DISPONIBLE' : 'INDISPONIBLE';
                $icon = $result['success'] ? '✅' : '❌';
                
                $details = [
                    'success' => $result['success'],
                    'message' => sprintf(
                        '%s %s - %s',
                        $icon,
                        strtoupper($engine_name),
                        $status
                    )
                ];

                if (isset($result['response_time'])) {
                    $details['message'] .= sprintf(' (Temps: %dms)', $result['response_time']);
                }

                if (!$result['success'] && isset($result['message'])) {
                    $details['message'] .= sprintf('<br>Raison: %s', esc_html($result['message']));
                }

                $formatted_results[$engine_name] = $details;
            }

            error_log("[PDF Engine Test] Résultats: " . json_encode($formatted_results));
            wp_send_json_success($formatted_results);

        } catch (Exception $e) {
            error_log("[PDF Engine Test] Exception: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'Erreur lors des tests: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Retourne le moteur PDF actuellement actif
     * Utilisé pour afficher l'indicateur dans l'interface
     */
    public function handle_get_active_engine() {
        try {
            // Récupérer le moteur configuré
            $engine_name = pdf_builder_get_option('pdf_builder_engine', 'puppeteer');
            
            // Tester si Puppeteer est disponible
            $is_puppeteer_available = false;
            $puppeteer_url = pdf_builder_get_option('pdf_builder_puppeteer_url', '');
            
            if ($engine_name === 'puppeteer' && !empty($puppeteer_url)) {
                require_once PDF_BUILDER_PLUGIN_DIR . 'src/PDF/Engines/PuppeteerEngine.php';
                $puppeteer = new \PDF_Builder\PDF\Engines\PuppeteerEngine();
                $is_puppeteer_available = $puppeteer->is_available();
            }
            
            // Déterminer le moteur effectif
            $effective_engine = $engine_name;
            if ($engine_name === 'puppeteer' && !$is_puppeteer_available) {
                $effective_engine = 'dompdf'; // Fallback
            }
            
            wp_send_json_success([
                'configured' => $engine_name,
                'effective' => $effective_engine,
                'available' => $is_puppeteer_available,
                'display_name' => $effective_engine === 'puppeteer' ? 'Puppeteer' : 'DomPDF',
                'icon' => $effective_engine === 'puppeteer' ? '🚀' : '📄'
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
}





