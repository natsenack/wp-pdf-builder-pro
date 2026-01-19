<?php

/**
 * PDF Builder Pro - Gestionnaire AJAX
 * Gère tous les appels AJAX de l'administration
 */

namespace PDF_Builder\Admin\Handlers;

use Exception;
use WP_Error;

/**
 * Classe responsable de la gestion des appels AJAX
 */
class AjaxHandler
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;

        // Ensure NonceManager is loaded
        if (!class_exists('PDF_Builder\Admin\Handlers\NonceManager')) {
            $nonce_manager_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Handlers/NonceManager.php';
            if (file_exists($nonce_manager_file)) {
                require_once $nonce_manager_file;
            }
        }

        $this->registerHooks();
    }

    /**
     * Enregistrer les hooks AJAX
     */
    private function registerHooks()
    {
        // Hook AJAX unifié principal - point d'entrée unique pour toutes les actions de sauvegarde
        add_action('wp_ajax_pdf_builder_ajax_handler', [$this, 'ajaxUnifiedHandler']);

        // Hooks AJAX principaux (génération PDF, templates)
        add_action('wp_ajax_pdf_builder_generate_pdf_from_canvas', [$this, 'ajaxGeneratePdfFromCanvas']);
        add_action('wp_ajax_pdf_builder_download_pdf', [$this, 'ajaxDownloadPdf']);
        add_action('wp_ajax_pdf_builder_save_template_v3', [$this, 'ajaxSaveTemplateV3']);
        add_action('wp_ajax_pdf_builder_save_template', [$this, 'ajaxSaveTemplateV3']);
        add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajaxLoadTemplate']);
        add_action('wp_ajax_pdf_builder_get_template', [$this, 'ajaxGetTemplate']);
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajaxGenerateOrderPdf']);
        add_action('wp_ajax_pdf_builder_get_fresh_nonce', [$this, 'ajaxGetFreshNonce']);

        // Hooks AJAX de maintenance
        add_action('wp_ajax_pdf_builder_check_database', [$this, 'ajaxCheckDatabase']);
        add_action('wp_ajax_pdf_builder_repair_database', [$this, 'ajaxRepairDatabase']);
        add_action('wp_ajax_pdf_builder_execute_sql_repair', [$this, 'ajaxExecuteSqlRepair']);
        add_action('wp_ajax_pdf_builder_check_integrity', [$this, 'ajaxCheckIntegrity']);
        add_action('wp_ajax_pdf_builder_check_template_limit', [$this, 'ajaxCheckTemplateLimit']);

        // Hooks AJAX canvas
        add_action('wp_ajax_pdf_builder_save_order_status_templates', [$this, 'ajaxSaveOrderStatusTemplates']);
        add_action('wp_ajax_pdf_builder_get_template_mappings', [$this, 'handleGetTemplateMappings']);
        add_action('wp_ajax_pdf_builder_get_canvas_orientations', [$this, 'ajaxGetCanvasOrientations']);
        add_action('wp_ajax_pdf_builder_save_canvas_modal_settings', [$this, 'ajaxSaveCanvasModalSettings']);
    }

    /**
     * Générer un PDF depuis le canvas
     */
    public function ajaxGeneratePdfFromCanvas()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Récupérer les données
            $template_data = isset($_POST['template_data']) ? json_decode(stripslashes($_POST['template_data']), true) : null;
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;

            if (!$template_data) {
                wp_send_json_error('Données de template manquantes');
                return;
            }

            // Générer le PDF
            $pdf_content = $this->admin->generateUnifiedHtml($template_data, $order_id);

            if (!$pdf_content) {
                wp_send_json_error('Erreur lors de la génération du PDF');
                return;
            }

            // Générer le PDF avec TCPDF ou autre
            $pdf_generator = new \PDF_Builder\Controllers\PdfBuilderProGenerator();
            $pdf_file = $pdf_generator->generateFromHtml($pdf_content, 'canvas_template_' . time() . '.pdf');

            if (!$pdf_file) {
                wp_send_json_error('Erreur lors de la création du fichier PDF');
                return;
            }

            wp_send_json_success([
                'pdf_url' => $pdf_file['url'],
                'pdf_path' => $pdf_file['path'],
                'message' => 'PDF généré avec succès'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger un PDF
     */
    public function ajaxDownloadPdf()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            $pdf_path = isset($_POST['pdf_path']) ? sanitize_text_field($_POST['pdf_path']) : '';

            if (empty($pdf_path) || !file_exists($pdf_path)) {
                wp_send_json_error('Fichier PDF introuvable');
                return;
            }

            // Forcer le téléchargement
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($pdf_path) . '"');
            header('Content-Length: ' . filesize($pdf_path));
            readfile($pdf_path);
            exit;

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer un nouveau nonce (pour les cas où le nonce est expiré)
     */
    public function ajaxGetFreshNonce()
    {
        NonceManager::logInfo('Demande de génération de nonce frais');
        
        // Vérifier les permissions uniquement (pas besoin de nonce valide pour en demander un)
        if (!NonceManager::checkPermissions(NonceManager::MIN_CAPABILITY)) {
            NonceManager::logInfo('Permissions insuffisantes pour générer un nonce');
            NonceManager::sendPermissionErrorResponse();
            return;
        }
        
        NonceManager::logInfo('Génération d\'un nonce frais');
        
        // Générer un nouveau nonce valide
        $fresh_nonce = NonceManager::createNonce();
        
        NonceManager::logInfo('Nonce frais généré avec succès');

        wp_send_json_success([
            'nonce' => $fresh_nonce,
            'message' => 'Nouveau nonce généré avec succès'
        ]);
    }

    /**
     * Sauvegarder un template (v3)
     */
    public function ajaxSaveTemplateV3()
    {
        // Déléguer au template manager si disponible
        $template_manager = $this->admin->getTemplateManager();
        
        if ($template_manager && method_exists($template_manager, 'ajaxSaveTemplateV3')) {
            $template_manager->ajaxSaveTemplateV3();
            return;
        }

        // Implémentation de secours
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            $template_data = isset($_POST['template_data']) ? json_decode(stripslashes($_POST['template_data']), true) : null;
            $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;

            if (!$template_data || empty($template_name)) {
                wp_send_json_error('Données de template ou nom manquant');
                return;
            }

            // Sauvegarder le template
            // Note: Template manager should be available, this fallback shouldn't be reached
            wp_send_json_error('Erreur: Template manager non disponible pour la sauvegarde');

            if ($result) {
                wp_send_json_success([
                    'template_id' => $result,
                    'message' => 'Template sauvegardé avec succès'
                ]);
            } else {
                wp_send_json_error('Erreur lors de la sauvegarde du template');
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Wrapper pour la sauvegarde automatique
     */
    /**
     * Charger un template
     */
    public function ajaxLoadTemplate()
    {
        // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxLoadTemplate called - START'); }

        // Déléguer au template manager si disponible
        $template_manager = $this->admin->getTemplateManager();
        if ($template_manager && method_exists($template_manager, 'ajaxLoadTemplate')) {
            $template_manager->ajaxLoadTemplate();
            return;
        }

        // Implémentation de secours
        try {
            // Valider les permissions et nonce de manière unifiée
            // 🔧 CORRECTION: Accepter les éditeurs aussi (MIN_CAPABILITY au lieu de ADMIN_CAPABILITY)
            $validation = NonceManager::validateRequest(NonceManager::MIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;

            if (!$template_id) {
                wp_send_json_error('ID de template manquant');
                return;
            }

            // Charger le template
            $template = $this->admin->loadTemplate($template_id);

            if ($template) {
                wp_send_json_success([
                    'template' => $template,
                    'message' => 'Template chargé avec succès'
                ]);
            } else {
                wp_send_json_error('Template introuvable');
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
        }
    }

    /**
     * Charger un template (version GET pour l'éditeur React)
     */
    public function ajaxGetTemplate()
    {
        try {
            // Debug logging
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxGetTemplate called at ' . current_time('Y-m-d H:i:s')); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] REQUEST_METHOD: ' . (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'UNKNOWN')); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] template_id GET: ' . (isset($_GET['template_id']) ? $_GET['template_id'] : 'NOT SET')); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] template_id POST: ' . (isset($_POST['template_id']) ? $_POST['template_id'] : 'NOT SET')); }

            // Valider les permissions et nonce de manière unifiée
            // 🔧 CORRECTION: Accepter les éditeurs aussi (MIN_CAPABILITY au lieu de ADMIN_CAPABILITY)
            $validation = NonceManager::validateRequest(NonceManager::MIN_CAPABILITY);
            if (!$validation['success']) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Nonce validation failed: ' . $validation['message']); }
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Nonce validation passed'); }

            // Récupérer le template_id depuis GET ou POST
            $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : (isset($_POST['template_id']) ? intval($_POST['template_id']) : null);

            if (!$template_id) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] No template_id provided'); }
                wp_send_json_error('ID de template manquant');
                return;
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Processing template_id: ' . $template_id); }

            // Vérifier que template_processor existe
            if (!isset($this->admin->template_processor) || !$this->admin->template_processor) {
                // Fallback: charger le template directement
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] template_processor not available, using fallback'); }
                return $this->fallbackLoadTemplate($template_id);
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Using template_processor to load template'); }

            // Charger le template en utilisant le template processor
            $template = $this->admin->template_processor->loadTemplateRobust($template_id);

            if ($template) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Template loaded successfully via template_processor'); }

                // Récupérer le nom du template depuis les métadonnées DB en priorité, sinon depuis la DB
                $template_name = '';
                $db_template = null;

                if (isset($template['_db_name']) && !empty($template['_db_name'])) {
                    $template_name = $template['_db_name'];
                } elseif (isset($template['name']) && !empty($template['name'])) {
                    $template_name = $template['name'];
                } elseif (isset($template['template_name']) && !empty($template['template_name'])) {
                    $template_name = $template['template_name'];
                } else {
                    // Fallback vers la colonne name de la DB
                    global $wpdb;
                    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
                    $db_template = $wpdb->get_row($wpdb->prepare("SELECT name FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
                    if ($db_template && !empty($db_template['name'])) {
                        $template_name = $db_template['name'];
                    } else {
                        $template_name = 'Template ' . $template_id;
                    }
                }

                // Debug logging
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxGetTemplate - Template ID: ' . $template_id); }
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxGetTemplate - Template data has name: ' . (isset($template['name']) ? $template['name'] : 'NO')); }
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxGetTemplate - Template data has _db_name: ' . (isset($template['_db_name']) ? $template['_db_name'] : 'NO')); }
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxGetTemplate - DB template name: ' . ($db_template && isset($db_template['name']) ? $db_template['name'] : 'NO DB RECORD')); }
                // if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ajaxGetTemplate - Final template_name: ' . $template_name); }

                wp_send_json_success([
                    'template' => $template,
                    'template_name' => $template_name,
                    'message' => 'Template chargé avec succès'
                ]);
            } else {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Template loading failed via template_processor, trying fallback'); }
                return $this->fallbackLoadTemplate($template_id);
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
        }
    }

    /**
     * Générer un PDF de commande
     */
    public function ajaxGenerateOrderPdf()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            if (!$order_id || !$template_id) {
                wp_send_json_error('ID de commande ou template manquant');
                return;
            }

            // Générer le PDF de commande
            $result = $this->admin->generateOrderPdf($order_id, $template_id);

            if ($result && isset($result['url'])) {
                wp_send_json_success([
                    'pdf_url' => $result['url'],
                    'message' => 'PDF de commande généré avec succès'
                ]);
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF de commande');
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier la base de données
     */
    public function ajaxCheckDatabase()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Vérifications de base de données
            global $wpdb;

            $checks = [
                'templates_table' => $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_templates'") !== null,
                'orders_table' => $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_orders'") !== null,
            ];

            $issues = [];
            foreach ($checks as $table => $exists) {
                if (!$exists) {
                    $issues[] = "Table {$table} manquante";
                }
            }

            wp_send_json_success([
                'checks' => $checks,
                'issues' => $issues,
                'message' => empty($issues) ? 'Base de données OK' : 'Problèmes détectés'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la vérification: ' . $e->getMessage());
        }
    }

    /**
     * Réparer la base de données
     */
    public function ajaxRepairDatabase()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Réparations de base de données
            $result = $this->admin->repairDatabase();

            wp_send_json_success([
                'result' => $result,
                'message' => 'Base de données réparée'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la réparation: ' . $e->getMessage());
        }
    }

    /**
     * Exécuter une réparation SQL
     */
    public function ajaxExecuteSqlRepair()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            $sql = isset($_POST['sql']) ? sanitize_textarea_field($_POST['sql']) : '';

            if (empty($sql)) {
                wp_send_json_error('Requête SQL manquante');
                return;
            }

            // Exécuter la réparation SQL
            global $wpdb;
            $result = $wpdb->query($sql);

            wp_send_json_success([
                'result' => $result,
                'message' => 'Requête exécutée avec succès'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de l\'exécution SQL: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les paramètres
     */
    public function ajaxSaveSettings()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Sauvegarder les paramètres généraux
            $settings = [
                'pdf_builder_enable_debug' => isset($_POST['enable_debug']) ? '1' : '0',
                'pdf_builder_max_file_size' => intval($_POST['max_file_size'] ?? 10),
            ];

            foreach ($settings as $key => $value) {
                update_option($key, $value);
            }

            wp_send_json_success([
                'message' => 'Paramètres sauvegardés avec succès'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Handler AJAX unifié - point d'entrée unique pour toutes les actions
     */
    public function ajaxUnifiedHandler()
    {
        try {
            // Rate limiting basique
            $this->checkRateLimit();

            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    wp_send_json_error(['message' => 'Nonce invalide', 'nonce' => NonceManager::createNonce()]);
                } else {
                    wp_send_json_error(['message' => 'Permissions insuffisantes']);
                }
                return;
            }

            // Validation de taille des données
            if (!$this->validateRequestSize()) {
                wp_send_json_error(['message' => 'Données trop volumineuses']);
                return;
            }

            // Déterminer l'action à effectuer
            $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

            // Router vers la bonne méthode selon l'action
            switch ($action) {
                case 'save_all_settings':
                case 'save_settings':
                    $this->handleSaveAllSettings();
                    break;

                case 'save_settings_page':
                    $this->handleSaveSettingsPage();
                    break;

                case 'save_general_settings':
                    $this->handleSaveGeneralSettings();
                    break;

                case 'get_settings':
                    $this->handleGetSettings();
                    break;

                case 'get_canvas_settings':
                    $this->handleGetCanvasSettings();
                    break;

                case 'get_template_mappings':
                    $this->handleGetTemplateMappings();
                    break;

                case 'validate_settings':
                    $this->handleValidateSettings();
                    break;

                // Actions de licence
                case 'cleanup_license':
                    $this->handleCleanupLicense();
                    break;
                case 'toggle_license_test_mode':
                    $this->handleToggleLicenseTestMode();
                    break;
                case 'generate_license_key':
                    $this->handleGenerateLicenseKey();
                    break;
                case 'delete_license_key':
                    $this->handleDeleteLicenseKey();
                    break;
                case 'validate_license_key':
                    $this->handleValidateLicenseKey();
                    break;

                // Gestion de la base de données
                case 'manage_database_table':
                    $this->handleManageDatabaseTable();
                    break;

                default:
                    // Action non reconnue - essayer l'ancien système de compatibilité
                    $this->handleLegacyAction($action);
                    break;
            }

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Erreur handler unifié: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion de la sauvegarde unifiée de tous les paramètres
     */
    private function handleSaveAllSettings()
    {
        // Créer un backup avant modification
        $backup_key = 'pdf_builder_backup_' . time();
        $existing_settings = pdf_builder_get_option('pdf_builder_settings', array());
        pdf_builder_update_option($backup_key, $existing_settings);

        // Nettoyer automatiquement les anciens backups (garder seulement les 5 derniers)
        $this->cleanupOldBackups();

        try {
            // Collecter et sanitiser tous les paramètres PDF Builder depuis $_POST
            $settings_to_save = [];
            $templates_data = [];

            foreach ($_POST as $key => $value) {
                // Ne traiter que les clés qui commencent par pdf_builder_
                if (strpos($key, 'pdf_builder_') === 0) {
                    // Traiter pdf_builder_order_status_templates séparément
                    if ($key === 'pdf_builder_order_status_templates') {
                        $templates_data = $this->sanitizeFieldValue($key, $value);
                    } else {
                        $sanitized_value = $this->sanitizeFieldValue($key, $value);
                        if ($sanitized_value !== '') {
                            $settings_to_save[$key] = $sanitized_value;
                        }
                    }
                }
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: Received POST keys: ' . implode(', ', array_keys($_POST))); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: Settings to save: ' . implode(', ', array_keys($settings_to_save))); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: Templates data: ' . json_encode($templates_data)); }

            // Sauvegarder les templates séparément si des données existent
            if (!empty($templates_data)) {
                pdf_builder_update_option('pdf_builder_order_status_templates', $templates_data);
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: Templates data saved to pdf_builder_order_status_templates'); }
            }

            if (empty($settings_to_save) && empty($templates_data)) {
                wp_send_json_error(['message' => 'Aucune donnée valide à sauvegarder']);
                return;
            }

            // Sauvegarder les paramètres généraux seulement s'il y en a
            if (!empty($settings_to_save)) {
                // Fusionner avec les paramètres existants
                $updated_settings = array_merge($existing_settings, $settings_to_save);

                // Sauvegarder dans la base de données
                $saved = pdf_builder_update_option('pdf_builder_settings', $updated_settings);

                // Vérifier s'il y a eu une vraie erreur DB
                global $wpdb;
                $db_error = $wpdb->last_error;

                if (!$saved && !empty($db_error)) {
                    // Erreur DB réelle
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - update_option failed. Last DB error: ' . $db_error); }
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Settings size: ' . strlen(serialize($updated_settings))); }
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Existing settings size: ' . strlen(serialize($existing_settings))); }
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - New settings count: ' . count($settings_to_save)); }

                    // Rollback en cas d'échec
                    $this->rollbackSettings($backup_key);
                    wp_send_json_error(['message' => 'Erreur lors de la sauvegarde en base de données']);
                    return;
                }
            }

            // Supprimer le backup si succès
            pdf_builder_delete_option($backup_key);

            wp_send_json_success([
                'message' => 'Paramètres sauvegardés avec succès',
                'saved_settings' => $settings_to_save,
                'saved_templates' => $templates_data,
                'action' => 'save_all_settings',
                'backup_cleaned' => true
            ]);

        } catch (Exception $e) {
            // Rollback en cas d'exception
            $this->rollbackSettings($backup_key);
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Erreur sauvegarde: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors du traitement des données']);
        }
    }

    /**
     * Gestion de la sauvegarde de la page de paramètres
     */
    private function handleSaveSettingsPage()
    {
        // Logique spécifique pour la page de paramètres
        $this->handleSaveAllSettings(); // Pour l'instant, rediriger vers la sauvegarde unifiée
    }

    /**
     * Gestion de la sauvegarde des paramètres généraux
     */
    private function handleSaveGeneralSettings()
    {
        // Collecter seulement les paramètres généraux
        $general_settings = [];
        $general_fields = [
            'pdf_builder_company_phone_manual',
            'pdf_builder_company_siret',
            'pdf_builder_company_vat',
            'pdf_builder_company_rcs',
            'pdf_builder_company_capital'
        ];

        foreach ($general_fields as $field) {
            if (isset($_POST[$field])) {
                $general_settings[$field] = sanitize_text_field($_POST[$field]);
            }
        }

        if (empty($general_settings)) {
            wp_send_json_error(['message' => 'Aucun paramètre général à sauvegarder']);
            return;
        }

        // Sauvegarder
        $existing_settings = pdf_builder_get_option('pdf_builder_settings', array());
        $updated_settings = array_merge($existing_settings, $general_settings);
        $saved = pdf_builder_update_option('pdf_builder_settings', $updated_settings);

        if ($saved) {
            wp_send_json_success([
                'message' => 'Paramètres généraux sauvegardés',
                'saved_settings' => $general_settings,
                'action' => 'save_general_settings'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la sauvegarde']);
        }
    }

    /**
     * Gestion de la sauvegarde des paramètres de performance
     */
    private function handleSavePerformanceSettings()
    {
        // Collecter seulement les paramètres de performance
        $performance_settings = [];
        $performance_fields = [
            'pdf_builder_performance_monitoring'
        ];

        foreach ($performance_fields as $field) {
            if (isset($_POST[$field])) {
                $performance_settings[$field] = sanitize_text_field($_POST[$field]);
            }
        }

        if (empty($performance_settings)) {
            wp_send_json_error(['message' => 'Aucun paramètre de performance à sauvegarder']);
            return;
        }

        // Sauvegarder
        $existing_settings = pdf_builder_get_option('pdf_builder_settings', array());
        $updated_settings = array_merge($existing_settings, $performance_settings);
        $saved = pdf_builder_update_option('pdf_builder_settings', $updated_settings);

        if ($saved) {
            wp_send_json_success([
                'message' => 'Paramètres de performance sauvegardés',
                'saved_settings' => $performance_settings,
                'action' => 'save_performance_settings'
            ]);
        } else {
            wp_send_json_error(['message' => 'Erreur lors de la sauvegarde']);
        }
    }

    /**
     * Gestion de la récupération des paramètres
     */
    private function handleGetSettings()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        wp_send_json_success([
            'settings' => $settings,
            'action' => 'get_settings'
        ]);
    }

    /**
     * Gestion de la récupération des paramètres canvas
     */
    private function handleGetCanvasSettings()
    {
        global $wpdb;
        $canvas_settings = [];

        $canvas_option_keys = [
            'pdf_builder_canvas_width',
            'pdf_builder_canvas_height',
            'pdf_builder_canvas_dpi',
            'pdf_builder_canvas_format',
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_border_width',
            'pdf_builder_canvas_container_bg_color',
            'pdf_builder_canvas_shadow_enabled',
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
            'pdf_builder_canvas_memory_limit_php'
        ];

        foreach ($canvas_option_keys as $key) {
            $value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $key));
            $canvas_settings[$key] = $value ?? '';
        }

        wp_send_json_success([
            'canvas_settings' => $canvas_settings,
            'action' => 'get_canvas_settings'
        ]);
    }

    /**
     * Gestion de la validation des paramètres
     */
    private function handleValidateSettings()
    {
        $errors = [];
        $warnings = [];

        // Validation des paramètres reçus
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pdf_builder_') === 0) {
                // Validation spécifique selon le type de champ
                if (strpos($key, '_email') !== false && !is_email($value)) {
                    $errors[] = "Email invalide: $key";
                }
                if (strpos($key, '_url') !== false && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[] = "URL invalide: $key";
                }
                // Ajouter d'autres validations selon les besoins
            }
        }

        if (empty($errors)) {
            wp_send_json_success([
                'valid' => true,
                'message' => 'Paramètres valides',
                'warnings' => $warnings,
                'action' => 'validate_settings'
            ]);
        } else {
            wp_send_json_error([
                'valid' => false,
                'errors' => $errors,
                'warnings' => $warnings,
                'action' => 'validate_settings'
            ]);
        }
    }

    /**
     * Récupérer les mappings de templates et la liste des templates disponibles
     */
    public function handleGetTemplateMappings()
    {
        try {
            global $wpdb;

            // Récupérer les mappings sauvegardés
            $mappings = pdf_builder_get_option('pdf_builder_order_status_templates', []);

            // Récupérer tous les types de templates disponibles (comme dans PDF_Template_Status_Manager)

            // Templates WordPress
            $templates_wp = $wpdb->get_results("
                SELECT ID, post_title
                FROM {$wpdb->posts}
                WHERE post_type = 'pdf_template'
                AND post_status = 'publish'
                ORDER BY post_title ASC
            ", ARRAY_A);

            $wp_templates = [];
            if ($templates_wp) {
                foreach ($templates_wp as $template) {
                    $wp_templates[$template['ID']] = $template['post_title'];
                }
            }

            // Templates personnalisés
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $templates_custom = $wpdb->get_results("
                SELECT id, name
                FROM {$table_templates}
                ORDER BY name ASC
            ", ARRAY_A);

            $custom_templates = [];
            if ($templates_custom) {
                foreach ($templates_custom as $template) {
                    $custom_templates['custom_' . $template['id']] = $template['name'];
                }
            }

            // Fusionner tous les templates
            $templates = array_merge($wp_templates, $custom_templates);

            wp_send_json_success([
                'mappings' => $mappings,
                'templates' => $templates,
                'action' => 'get_template_mappings'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la récupération des mappings: ' . $e->getMessage());
        }
    }

    /**
     * Gestion des actions legacy pour compatibilité
     */
    private function handleLegacyAction($action)
    {
        // Pour la compatibilité, essayer de deviner l'action depuis l'ancien système
        if (strpos($_POST['action'] ?? '', 'save_all_settings') !== false) {
            $this->handleSaveAllSettings();
        } elseif (strpos($_POST['action'] ?? '', 'save_settings_page') !== false) {
            $this->handleSaveSettingsPage();
        } else {
            wp_send_json_error(['message' => 'Action non reconnue: ' . $action]);
        }
    }

    /**
     * Sauvegarder les paramètres généraux
     */
    public function ajaxSaveGeneralSettings()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Paramètres généraux
            $settings = [
                'pdf_builder_company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
                'pdf_builder_company_address' => sanitize_textarea_field($_POST['company_address'] ?? ''),
                'pdf_builder_company_phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
                'pdf_builder_company_email' => sanitize_email($_POST['company_email'] ?? ''),
                'pdf_builder_default_language' => sanitize_text_field($_POST['default_language'] ?? 'fr'),
            ];

            foreach ($settings as $key => $value) {
                update_option($key, $value);
            }

            wp_send_json_success([
                'message' => 'Paramètres généraux sauvegardés'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les paramètres de performance
     */
    public function ajaxSavePerformanceSettings()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Paramètres de performance
            $settings = [
                'pdf_builder_compression_level' => intval($_POST['compression_level'] ?? 6),
                'pdf_builder_memory_limit' => intval($_POST['memory_limit'] ?? 256),
                'pdf_builder_max_execution_time' => intval($_POST['max_execution_time'] ?? 30),
            ];

            foreach ($settings as $key => $value) {
                update_option($key, $value);
            }

            wp_send_json_success([
                'message' => 'Paramètres de performance sauvegardés'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }



    /**
     * AJAX - Vérifier la limite de templates
     */
    public function ajaxCheckTemplateLimit()
    {
        // Utiliser le système de nonce unifié (lecture d'information)
        NonceManager::validateRequest(NonceManager::MIN_CAPABILITY);
        
        // Vérification permissions
        if (!current_user_can(NonceManager::MIN_CAPABILITY)) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }

        $can_create = $this->admin->can_create_template();

        wp_send_json_success([
            'can_create' => $can_create,
            'current_count' => $this->admin->count_user_templates(get_current_user_id()),
            'limit' => 1
        ]);
    }

    /**
     * AJAX - Vérifier l'intégrité du système
     */
    public function ajaxCheckIntegrity()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_check_integrity')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }
        try {
            $checks = [];
            $upload_dir = wp_upload_dir();
            $checks['upload_dir_writable'] = is_writable($upload_dir['basedir']);
            global $wpdb;
            $tables = ['pdf_builder_templates', 'pdf_builder_pdfs'];
            foreach ($tables as $table) {
                $result = $wpdb->get_row("CHECK TABLE {$wpdb->prefix}{$table}");
                $checks[$table] = $result ? $result->Msg_text : 'OK';
            }
            $checks['options_accessible'] = is_array(pdf_builder_get_option('pdf_builder_settings', array()));
            $all_ok = array_filter($checks, function($v) {
                return $v === true || $v === 'OK' || strpos($v, 'OK') === 0;
            });
            wp_send_json([
                'success' => true,
                'message' => count($all_ok) === count($checks) ? __('Intégrité vérifiée - OK.', 'pdf-builder-pro') : __('Problèmes détectés.', 'pdf-builder-pro'),
                'checks' => $checks
            ]);
        } catch (Exception $e) {
            wp_send_json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Fallback method to load template when template_processor is not available
     */
    private function fallbackLoadTemplate($template_id)
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] fallbackLoadTemplate called for template_id: ' . $template_id); }
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Vérifier que la table existe
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Templates table does not exist: ' . $table_templates); }
                wp_send_json_error('Table des templates introuvable');
                return;
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Templates table exists, querying for template_id: ' . $template_id); }
            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            if (!$template) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Template not found in database'); }
                wp_send_json_error('Template introuvable');
                return;
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Template found, attempting JSON decode'); }

            // Essayer de décoder le JSON
            $template_data = json_decode($template['template_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Ajouter les métadonnées de la base de données
                $template_data['_db_name'] = $template['name'];
                $template_data['_db_id'] = $template['id'];

                // S'assurer qu'il y a toujours un nom de template valide
                if (!isset($template_data['name']) || empty($template_data['name']) || preg_match('/^Template \d+$/', $template_data['name'])) {
                    $template_data['name'] = !empty($template['name']) ? $template['name'] : 'Template ' . $template_id;
                }
                $this->sendTemplateSuccessResponse($template_data, $template);
                return;
            }

            // Essayer le nettoyage normal si DataUtils est disponible
            if (isset($this->admin->data_utils) && method_exists($this->admin->data_utils, 'cleanJsonData')) {
                $clean_json = $this->admin->data_utils->cleanJsonData($template['template_data']);
                if ($clean_json !== $template['template_data']) {
                    $template_data = json_decode($clean_json, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Ajouter les métadonnées de la base de données
                        $template_data['_db_name'] = $template['name'];
                        $template_data['_db_id'] = $template['id'];

                        // Ajouter le nom du template depuis la base de données
                        if (isset($template['name']) && (!isset($template_data['name']) || empty($template_data['name']) || preg_match('/^Template \d+$/', $template_data['name']))) {
                            $template_data['name'] = $template['name'];
                        }
                        $this->sendTemplateSuccessResponse($template_data, $template);
                        return;
                    }
                }
            }

            // Essayer le nettoyage agressif si DataUtils est disponible
            if (isset($this->admin->data_utils) && method_exists($this->admin->data_utils, 'aggressiveJsonClean')) {
                $aggressive_clean = $this->admin->data_utils->aggressiveJsonClean($template['template_data']);
                if ($aggressive_clean !== $template['template_data']) {
                    $template_data = json_decode($aggressive_clean, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Ajouter les métadonnées de la base de données
                        $template_data['_db_name'] = $template['name'];
                        $template_data['_db_id'] = $template['id'];

                        // Ajouter le nom du template depuis la base de données
                        if (isset($template['name']) && (!isset($template_data['name']) || empty($template_data['name']) || preg_match('/^Template \d+$/', $template_data['name']))) {
                            $template_data['name'] = $template['name'];
                        }
                        $this->sendTemplateSuccessResponse($template_data, $template);
                        return;
                    }
                }
            }

            // Dernier recours - utiliser un template par défaut
            $default_template = $this->getDefaultInvoiceTemplate();
            $this->sendTemplateSuccessResponse($default_template, ['name' => 'Template par défaut']);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du chargement du template: ' . $e->getMessage());
        }
    }

    /**
     * Send successful template response
     */
    private function sendTemplateSuccessResponse($template_data, $template_info)
    {
        // Récupérer le nom du template depuis les métadonnées DB en priorité, sinon depuis la DB
        $template_name = '';
        if (isset($template_data['_db_name']) && !empty($template_data['_db_name'])) {
            $template_name = $template_data['_db_name'];
        } elseif (isset($template_data['name']) && !empty($template_data['name'])) {
            $template_name = $template_data['name'];
        } elseif (isset($template_data['template_name']) && !empty($template_data['template_name'])) {
            $template_name = $template_data['template_name'];
        } elseif (isset($template_info['name']) && !empty($template_info['name'])) {
            $template_name = $template_info['name'];
        } else {
            $template_name = 'Template ' . (isset($template_info['id']) ? $template_info['id'] : 'inconnu');
        }

        wp_send_json_success([
            'template' => $template_data,
            'template_name' => $template_name,
            'message' => 'Template chargé avec succès'
        ]);
    }

    /**
     * Get default invoice template
     */
    private function getDefaultInvoiceTemplate()
    {
        return array(
            'canvas' => array(
                'width' => 595,
                'height' => 842,
                'zoom' => 1,
                'pan' => array('x' => 0, 'y' => 0)
            ),
            'pages' => array(
                array(
                    'margins' => array('top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20),
                    'elements' => array(
                        array(
                            'id' => 'company_name',
                            'type' => 'text',
                            'position' => array('x' => 50, 'y' => 50),
                            'size' => array('width' => 200, 'height' => 30),
                            'style' => array('fontSize' => 18, 'fontWeight' => 'bold', 'color' => '#000000'),
                            'content' => 'Ma Société'
                        ),
                        array(
                            'id' => 'invoice_title',
                            'type' => 'text',
                            'position' => array('x' => 400, 'y' => 50),
                            'size' => array('width' => 150, 'height' => 30),
                            'style' => array('fontSize' => 20, 'fontWeight' => 'bold', 'color' => '#000000'),
                            'content' => 'FACTURE'
                        ),
                        array(
                            'id' => 'invoice_number',
                            'type' => 'invoice_number',
                            'position' => array('x' => 400, 'y' => 90),
                            'size' => array('width' => 150, 'height' => 25),
                            'style' => array('fontSize' => 14, 'color' => '#000000'),
                            'content' => 'N° de facture'
                        ),
                        array(
                            'id' => 'invoice_date',
                            'type' => 'invoice_date',
                            'position' => array('x' => 400, 'y' => 120),
                            'size' => array('width' => 150, 'height' => 25),
                            'style' => array('fontSize' => 14, 'color' => '#000000'),
                            'content' => 'Date'
                        ),
                        array(
                            'id' => 'customer_info',
                            'type' => 'customer_info',
                            'position' => array('x' => 50, 'y' => 150),
                            'size' => array('width' => 250, 'height' => 80),
                            'style' => array('fontSize' => 12, 'color' => '#000000'),
                            'content' => 'Informations client'
                        ),
                        array(
                            'id' => 'products_table',
                            'type' => 'product_table',
                            'position' => array('x' => 50, 'y' => 250),
                            'size' => array('width' => 500, 'height' => 200),
                            'style' => array('fontSize' => 12, 'color' => '#000000'),
                            'content' => 'Tableau produits'
                        ),
                        array(
                            'id' => 'total',
                            'type' => 'total',
                            'position' => array('x' => 400, 'y' => 500),
                            'size' => array('width' => 150, 'height' => 30),
                            'style' => array('fontSize' => 16, 'fontWeight' => 'bold', 'color' => '#000000'),
                            'content' => 'Total'
                        )
                    )
                )
            )
        );
    }

    /**
     * Sauvegarder tous les paramètres via AJAX (système unifié)
     */
    public function ajaxSaveAllSettings()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Récupérer tous les paramètres PDF Builder depuis $_POST
            $settings_to_save = [];
            foreach ($_POST as $key => $value) {
                // Ne traiter que les clés qui commencent par pdf_builder_
                if (strpos($key, 'pdf_builder_') === 0) {
                    // Gérer les arrays JSON (pour les checkboxes multiples)
                    if (is_string($value) && $this->isJson($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $settings_to_save[$key] = $decoded;
                        } else {
                            $settings_to_save[$key] = $value;
                        }
                    } else {
                        $settings_to_save[$key] = $value;
                    }
                }
            }

            if (empty($settings_to_save)) {
                wp_send_json_error(['message' => 'Aucune donnée à sauvegarder']);
                return;
            }

            // Récupérer les paramètres existants
            $existing_settings = pdf_builder_get_option('pdf_builder_settings', array());

            // Fusionner avec les nouveaux paramètres
            $updated_settings = array_merge($existing_settings, $settings_to_save);

            // Sauvegarder dans la base de données
            $saved = pdf_builder_update_option('pdf_builder_settings', $updated_settings);

            if ($saved) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'saved_settings' => $settings_to_save
                ]);
            } else {
                wp_send_json_error(['message' => 'Erreur lors de la sauvegarde en base de données']);
            }

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Erreur sauvegarde unifiée: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }

    /**
     * Vérifie si une chaîne est du JSON valide
     */
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        $decoded = json_decode($string, true);
        return $decoded !== null && json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Rollback des paramètres en cas d'erreur
     */
    private function rollbackSettings($backup_key)
    {
        $backup = get_option($backup_key, false);
        if ($backup !== false) {
            pdf_builder_update_option('pdf_builder_settings', $backup);
            delete_option($backup_key);
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Rollback effectué depuis backup: ' . $backup_key); }
        }
    }

    /**
     * Nettoyer les anciens backups automatiquement
     */
    private function cleanupOldBackups()
    {
        global $wpdb;

        // Récupérer tous les backups (max 5 derniers)
        $backups = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                 WHERE option_name LIKE %s
                 ORDER BY option_name DESC
                 LIMIT 999 OFFSET 5",
                'pdf_builder_backup_%'
            )
        );

        // Supprimer les anciens
        foreach ($backups as $backup) {
            delete_option($backup->option_name);
        }
    }

    /**
     * Rate limiting basique pour éviter les abus
     */
    private function checkRateLimit()
    {
        $user_id = get_current_user_id();
        $transient_key = 'pdf_builder_rate_limit_' . $user_id;
        $attempts = get_transient($transient_key);

        if ($attempts === false) {
            // Première tentative
            set_transient($transient_key, 1, 60); // 1 minute
        } elseif ($attempts >= 30) {
            // Trop de tentatives
            wp_send_json_error(['message' => 'Trop de requêtes. Veuillez patienter.']);
            exit;
        } else {
            // Incrémenter le compteur
            set_transient($transient_key, $attempts + 1, 60);
        }
    }

    /**
     * Validation de la taille des données de requête
     */
    private function validateRequestSize()
    {
        $max_size = 1024 * 1024; // 1MB max
        $content_length = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;

        if ($content_length > $max_size) {
            return false;
        }

        // Compter aussi la taille des fichiers POST
        $total_size = 0;
        foreach ($_POST as $key => $value) {
            $total_size += strlen($key) + strlen(is_array($value) ? serialize($value) : $value);
        }

        return $total_size <= $max_size;
    }

    /**
     * Sauvegarder les mappings de templates par statut de commande
     */
    public function ajaxSaveOrderStatusTemplates()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: ajaxSaveOrderStatusTemplates called'); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: POST data: ' . print_r($_POST, true)); }

            // Récupérer les données des templates
            $templates_data = isset($_POST['pdf_builder_order_status_templates']) ? $_POST['pdf_builder_order_status_templates'] : [];

            // Valider et nettoyer les données
            $clean_templates = [];
            if (is_array($templates_data)) {
                foreach ($templates_data as $status_key => $template_id) {
                    // Nettoyer les clés et valeurs
                    $clean_status = sanitize_text_field($status_key);
                    $clean_template = sanitize_text_field($template_id);

                    // Ne sauvegarder que si un template est sélectionné
                    if (!empty($clean_template)) {
                        $clean_templates[$clean_status] = $clean_template;
                    }
                }
            }

            // Sauvegarder dans la base de données
            pdf_builder_update_option('pdf_builder_order_status_templates', $clean_templates);
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: Saved to DB in ajaxSaveOrderStatusTemplates: ' . print_r($clean_templates, true)); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PHP: DB content after save: ' . print_r(pdf_builder_get_option('pdf_builder_order_status_templates', []), true)); }

            wp_send_json_success([
                'message' => 'Mappings de templates sauvegardés avec succès',
                'saved_count' => count($clean_templates)
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Sanitisation améliorée selon le type de champ
     */
    private function sanitizeFieldValue($key, $value)
    {
        // Cas spéciaux d'abord
        if ($key === 'pdf_builder_license_email_reminders') {
            // C'est un toggle boolean, pas un email
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']) ? '1' : '0';
        }

        // Gestion spéciale pour pdf_builder_order_status_templates (array)
        if ($key === 'pdf_builder_order_status_templates') {
            // Si c'est une chaîne JSON, la décoder
            if (is_string($value)) {
                // Essayer d'abord le décodage direct
                $decoded = json_decode($value, true);
                if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                } else {
                    // Essayer avec stripslashes si le décodage direct échoue
                    $stripped = stripslashes($value);
                    $decoded = json_decode($stripped, true);
                    if ($decoded !== null && json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    } else {
                        // Si toujours pas valide, retourner un array vide
                        $value = [];
                    }
                }
            }

            if (is_array($value)) {
                $clean_array = [];
                foreach ($value as $status_key => $template_id) {
                    $clean_status = sanitize_text_field($status_key);
                    $clean_template = sanitize_text_field($template_id);
                    if (!empty($clean_template)) {
                        $clean_array[$clean_status] = $clean_template;
                    }
                }
                return $clean_array;
            }
            return [];
        }

        // Déterminer le type de champ d'après le nom
        if (strpos($key, '_email') !== false) {
            return sanitize_email($value);
        } elseif (strpos($key, '_url') !== false) {
            return esc_url_raw($value);
        } elseif (strpos($key, '_number') !== false || strpos($key, '_size') !== false || strpos($key, '_ttl') !== false) {
            return is_numeric($value) ? (int)$value : 0;
        } elseif (strpos($key, '_boolean') !== false || strpos($key, '_enabled') !== false) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']) ? '1' : '0';
        } elseif ($this->isJson($value)) {
            // Pour les arrays JSON, valider que c'est du JSON valide
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return wp_json_encode($decoded); // Re-encoder pour uniformité
            }
            return '';
        } else {
            // Texte standard
            return sanitize_text_field($value);
        }
    }

    /**
     * Récupérer les permissions d'orientation du canvas
     */
    public function ajaxGetCanvasOrientations()
    {
        try {
            // Utiliser le système de nonce unifié (lecture d'information)
            NonceManager::validateRequest(NonceManager::MIN_CAPABILITY);
            
            // Vérifier les permissions
            if (!current_user_can(NonceManager::MIN_CAPABILITY)) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Récupérer les paramètres
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            
            $orientations = [
                'allowPortrait' => isset($settings['pdf_builder_canvas_allow_portrait']) && $settings['pdf_builder_canvas_allow_portrait'] === '1',
                'allowLandscape' => isset($settings['pdf_builder_canvas_allow_landscape']) && $settings['pdf_builder_canvas_allow_landscape'] === '1',
                'defaultOrientation' => $settings['pdf_builder_canvas_default_orientation'] ?? 'portrait'
            ];

            // S'assurer qu'au moins une orientation est activée
            if (!$orientations['allowPortrait'] && !$orientations['allowLandscape']) {
                $orientations['allowPortrait'] = true; // Portrait par défaut
            }

            wp_send_json_success($orientations);
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Sauvegarder les paramètres des modales canvas
     */
    public function ajaxSaveCanvasModalSettings()
    {
        try {
            // Valider les permissions et nonce de manière unifiée
            $validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    NonceManager::sendNonceErrorResponse();
                } else {
                    NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            // Récupérer les paramètres depuis la requête
            $category = sanitize_text_field($_POST['category'] ?? '');
            $settings_to_save = [];

            // Collecter TOUS les paramètres depuis POST (pas seulement ceux commençant par pdf_builder_canvas_)
            foreach ($_POST as $key => $value) {
                // Ignorer les champs spéciaux
                if (in_array($key, ['action', 'nonce', 'category'])) {
                    continue;
                }

                // Traiter les valeurs selon leur type
                if (is_array($value)) {
                    // Pour les tableaux (checkboxes multiples), imploser en chaîne
                    $settings_to_save[$key] = implode(',', array_map('sanitize_text_field', $value));
                } else {
                    // Pour les valeurs simples, utiliser sanitize_text_field
                    $settings_to_save[$key] = sanitize_text_field($value);
                }
            }

            if (empty($settings_to_save)) {
                wp_send_json_error(['message' => 'Aucune donnée à sauvegarder']);
                return;
            }

            // Récupérer les paramètres existants
            $existing_settings = pdf_builder_get_option('pdf_builder_settings', array());

            // Mettre à jour les paramètres
            $updated_count = 0;
            foreach ($settings_to_save as $key => $value) {
                $existing_settings[$key] = $value;
                $updated_count++;
            }

            // Sauvegarder dans l'option unifiée
            $saved = pdf_builder_update_option('pdf_builder_settings', $existing_settings);

            if ($saved) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'category' => $category,
                    'updated_count' => $updated_count
                ]);
            } else {
                wp_send_json_error(['message' => 'Erreur lors de la sauvegarde']);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Nettoyer complètement la licence
     */
    private function handleCleanupLicense()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Starting cleanup process'); }

            // Récupérer les paramètres actuels
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Current settings count: ' . count($settings)); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Current settings keys: ' . implode(', ', array_keys($settings))); }

            // Vérifier si le mode test est actif AVANT de commencer le nettoyage
            $test_mode_was_enabled = ($settings['pdf_builder_license_test_mode'] ?? '0') === '1';
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Test mode was enabled: ' . ($test_mode_was_enabled ? 'YES' : 'NO')); }

            // Liste des clés de licence à supprimer (nettoyage complet)
            $license_keys_to_remove = [
                'pdf_builder_license_key',
                'pdf_builder_license_status',
                'pdf_builder_license_expiry',
                'pdf_builder_license_type',
                'pdf_builder_license_last_check',
                'pdf_builder_license_validated',
                'pdf_builder_license_test_key',
                'pdf_builder_license_test_key_expires',
                'pdf_builder_license_test_mode'
            ];

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Keys to remove: ' . implode(', ', $license_keys_to_remove)); }

            $removed_count = 0;
            foreach ($license_keys_to_remove as $key) {
                if (isset($settings[$key])) {
                    $old_value = $settings[$key];
                    unset($settings[$key]);
                    $removed_count++;
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Removed key: ' . $key . ' (value was: ' . $old_value . ')'); }
                } else {
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Key not found: ' . $key); }
                }
            }

            // IMPORTANT: Désactiver temporairement le sanitize callback pour éviter qu'il remette les clés supprimées
            global $wp_filter;
            $sanitize_callbacks = null;
            if (isset($wp_filter['sanitize_option_pdf_builder_settings'])) {
                $sanitize_callbacks = $wp_filter['sanitize_option_pdf_builder_settings'];
                unset($wp_filter['sanitize_option_pdf_builder_settings']);
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Temporarily disabled sanitize callback'); }
            }

            // Sauvegarder les paramètres nettoyés SANS sanitize callback
            $update_result = pdf_builder_update_option('pdf_builder_settings', $settings);
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Update result: ' . ($update_result ? 'SUCCESS' : 'FAILED')); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Removed ' . $removed_count . ' license keys'); }

            // Réactiver le sanitize callback si il était présent
            if ($sanitize_callbacks !== null) {
                $wp_filter['sanitize_option_pdf_builder_settings'] = $sanitize_callbacks;
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Re-enabled sanitize callback'); }
            }

            // Vérifier que les clés ont bien été supprimées
            $updated_settings = pdf_builder_get_option('pdf_builder_settings', array());
            $remaining_license_keys = array_filter(array_keys($updated_settings), function($key) {
                return strpos($key, 'pdf_builder_license') === 0;
            });
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Remaining license keys: ' . implode(', ', $remaining_license_keys)); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Final settings count: ' . count($updated_settings)); }

            wp_send_json_success([
                'message' => 'Nettoyage complet réussi. ' . $removed_count . ' clés de licence supprimées.',
                'removed_count' => $removed_count,
                'remaining_keys' => $remaining_license_keys
            ]);

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCleanupLicense - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors du nettoyage: ' . $e->getMessage()]);
        }
    }

    /**
     * Basculer le mode test de licence
     */
    private function handleToggleLicenseTestMode()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleToggleLicenseTestMode - Starting toggle process'); }

            // Récupérer les paramètres actuels
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $current_mode = $settings['pdf_builder_license_test_mode'] ?? '0';

            // Basculer le mode
            $new_mode = $current_mode === '1' ? '0' : '1';
            $settings['pdf_builder_license_test_mode'] = $new_mode;

            // Sauvegarder
            $update_result = pdf_builder_update_option('pdf_builder_settings', $settings);

            // Vérifier que la sauvegarde a bien fonctionné
            $verify_settings = pdf_builder_get_option('pdf_builder_settings', array());
            $verify_mode = $verify_settings['pdf_builder_license_test_mode'] ?? 'NOT_SET';
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleToggleLicenseTestMode - VERIFICATION: saved=' . $new_mode . ' retrieved=' . $verify_mode); }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleToggleLicenseTestMode - Toggled from ' . $current_mode . ' to ' . $new_mode); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleToggleLicenseTestMode - Update result: ' . ($update_result ? 'SUCCESS' : 'FAILED')); }

            wp_send_json_success([
                'message' => 'Mode test ' . ($new_mode === '1' ? 'activé' : 'désactivé') . ' avec succès',
                'new_mode' => $new_mode
            ]);

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleToggleLicenseTestMode - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors du basculement: ' . $e->getMessage()]);
        }
    }

    /**
     * Générer une clé de test
     */
    private function handleGenerateLicenseKey()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleGenerateLicenseKey - Starting generation process'); }

            // Générer une clé aléatoire
            $test_key = 'TEST-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 16));

            // Récupérer les paramètres actuels
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $settings['pdf_builder_license_test_key'] = $test_key;

            // Sauvegarder
            $update_result = pdf_builder_update_option('pdf_builder_settings', $settings);

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleGenerateLicenseKey - Generated key: ' . $test_key); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleGenerateLicenseKey - Update result: ' . ($update_result ? 'SUCCESS' : 'FAILED')); }

            wp_send_json_success([
                'message' => 'Clé de test générée avec succès',
                'test_key' => $test_key
            ]);

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleGenerateLicenseKey - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la génération: ' . $e->getMessage()]);
        }
    }

    /**
     * Supprimer la clé de test
     */
    private function handleDeleteLicenseKey()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleDeleteLicenseKey - Starting deletion process'); }

            // Récupérer les paramètres actuels
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $old_key = $settings['pdf_builder_license_test_key'] ?? '';

            if (isset($settings['pdf_builder_license_test_key'])) {
                unset($settings['pdf_builder_license_test_key']);
            }

            // Sauvegarder
            $update_result = pdf_builder_update_option('pdf_builder_settings', $settings);

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleDeleteLicenseKey - Deleted key: ' . $old_key); }
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleDeleteLicenseKey - Update result: ' . ($update_result ? 'SUCCESS' : 'FAILED')); }

            wp_send_json_success([
                'message' => 'Clé de test supprimée avec succès'
            ]);

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleDeleteLicenseKey - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Valider la clé de test
     */
    private function handleValidateLicenseKey()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleValidateLicenseKey - Starting validation process'); }

            // Récupérer les paramètres actuels
            $settings = pdf_builder_get_option('pdf_builder_settings', array());
            $test_key = $settings['pdf_builder_license_test_key'] ?? '';

            if (empty($test_key)) {
                wp_send_json_error(['message' => 'Aucune clé de test à valider']);
                return;
            }

            // Validation simple pour les clés de test
            $is_valid = strpos($test_key, 'TEST-') === 0 && strlen($test_key) === 21;

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleValidateLicenseKey - Key: ' . $test_key . ', Valid: ' . ($is_valid ? 'YES' : 'NO')); }

            if ($is_valid) {
                wp_send_json_success([
                    'message' => 'Clé de test validée avec succès',
                    'valid' => true
                ]);
            } else {
                wp_send_json_error(['message' => 'Clé de test invalide']);
            }

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleValidateLicenseKey - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la validation: ' . $e->getMessage()]);
        }
    }

    /**
     * Gérer la table de base de données personnalisée
     */
    private function handleManageDatabaseTable()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleManageDatabaseTable - Starting'); }

            // Récupérer la sous-action
            $sub_action = isset($_POST['sub_action']) ? sanitize_text_field($_POST['sub_action']) : '';

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleManageDatabaseTable - Sub-action: ' . $sub_action); }

            // Charger la classe Settings_Table_Manager
            $table_manager_file = plugin_dir_path(dirname(__FILE__)) . 'Database/Settings_Table_Manager.php';
            
            if (!file_exists($table_manager_file)) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleManageDatabaseTable - Settings_Table_Manager not found at: ' . $table_manager_file); }
                wp_send_json_error(['message' => 'Gestionnaire de table non trouvé']);
                return;
            }

            if (!class_exists('PDF_Builder\Database\Settings_Table_Manager')) {
                require_once $table_manager_file;
            }

            $table_manager = new \PDF_Builder\Database\Settings_Table_Manager();

            // Router selon la sous-action
            switch ($sub_action) {
                case 'create_table':
                    $this->handleCreateTable($table_manager);
                    break;

                case 'migrate_data':
                    $this->handleMigrateData($table_manager);
                    break;

                case 'check_status':
                    $this->handleCheckDatabaseStatus($table_manager);
                    break;

                default:
                    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleManageDatabaseTable - Unknown sub-action: ' . $sub_action); }
                    wp_send_json_error(['message' => 'Sous-action non reconnue: ' . $sub_action]);
                    break;
            }

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleManageDatabaseTable - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la gestion de la BD: ' . $e->getMessage()]);
        }
    }

    /**
     * Créer la table de base de données
     */
    private function handleCreateTable($table_manager)
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCreateTable - Starting table creation'); }

            // Créer la table
            $result = $table_manager->create_table();

            if ($result) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCreateTable - Table created successfully'); }
                wp_send_json_success(['message' => 'Table wp_pdf_builder_settings créée avec succès']);
            } else {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCreateTable - Table creation failed'); }
                wp_send_json_error(['message' => 'Erreur lors de la création de la table']);
            }

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCreateTable - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Migrer les données
     */
    private function handleMigrateData($table_manager)
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleMigrateData - Starting data migration'); }

            // Migrer les données
            $result = $table_manager->migrate_data();

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleMigrateData - Migration result: ' . json_encode($result)); }

            if ($result && isset($result['success']) && $result['success']) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleMigrateData - Data migration completed successfully'); }
                wp_send_json_success([
                    'message' => 'Migration des données effectuée: ' . ($result['migrated'] ?? 0) . ' paramètres migrés',
                    'migrated' => $result['migrated'] ?? 0
                ]);
            } else {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleMigrateData - Data migration failed: ' . json_encode($result)); }
                wp_send_json_error(['message' => 'Erreur lors de la migration: ' . ($result['message'] ?? 'Erreur inconnue')]);
            }

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleMigrateData - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la migration: ' . $e->getMessage()]);
        }
    }

    /**
     * Vérifier l'état de la base de données
     */
    private function handleCheckDatabaseStatus($table_manager)
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCheckDatabaseStatus - Starting status check'); }

            global $wpdb;

            // Vérifier si la table existe
            $table_name = $wpdb->prefix . 'pdf_builder_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCheckDatabaseStatus - Table exists: ' . ($table_exists ? 'YES' : 'NO')); }

            $response = [
                'table_exists' => $table_exists,
                'columns_count' => 0,
                'records_count' => 0,
                'is_migrated' => false
            ];

            if ($table_exists) {
                // Compter les colonnes
                $columns = $wpdb->get_results("DESCRIBE $table_name");
                $response['columns_count'] = count($columns);

                // Compter les enregistrements
                $response['records_count'] = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name"));

                // Vérifier l'état de migration
                $response['is_migrated'] = $table_manager->is_migrated();
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCheckDatabaseStatus - Status: ' . json_encode($response)); }

            wp_send_json_success($response);

        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] handleCheckDatabaseStatus - Error: ' . $e->getMessage()); }
            wp_send_json_error(['message' => 'Erreur lors de la vérification: ' . $e->getMessage()]);
        }
    }
}




