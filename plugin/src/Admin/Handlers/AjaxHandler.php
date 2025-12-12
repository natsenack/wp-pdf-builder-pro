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
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajaxFlushRestCache']);
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajaxGenerateOrderPdf']);

        // Hooks AJAX de maintenance
        add_action('wp_ajax_pdf_builder_check_database', [$this, 'ajaxCheckDatabase']);
        add_action('wp_ajax_pdf_builder_repair_database', [$this, 'ajaxRepairDatabase']);
        add_action('wp_ajax_pdf_builder_execute_sql_repair', [$this, 'ajaxExecuteSqlRepair']);
        add_action('wp_ajax_pdf_builder_clear_cache', [$this, 'ajaxClearCache']);
        add_action('wp_ajax_pdf_builder_check_integrity', [$this, 'ajaxCheckIntegrity']);
        add_action('wp_ajax_pdf_builder_check_template_limit', [$this, 'ajaxCheckTemplateLimit']);

        // Hooks AJAX canvas
        add_action('wp_ajax_pdf_builder_save_canvas_settings', [$this, 'ajaxSaveCanvasSettings']);
        add_action('wp_ajax_pdf_builder_get_canvas_settings', [$this, 'ajaxGetCanvasSettings']);
        add_action('wp_ajax_pdf_builder_save_order_status_templates', [$this, 'ajaxSaveOrderStatusTemplates']);
        add_action('wp_ajax_pdf_builder_get_template_mappings', [$this, 'handleGetTemplateMappings']);
    }

    /**
     * Générer un PDF depuis le canvas
     */
    public function ajaxGeneratePdfFromCanvas()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
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
        // error_log('[PDF Builder] ajaxLoadTemplate called - START');

        // Déléguer au template manager si disponible
        $template_manager = $this->admin->getTemplateManager();
        if ($template_manager && method_exists($template_manager, 'ajaxLoadTemplate')) {
            $template_manager->ajaxLoadTemplate();
            return;
        }

        // Implémentation de secours
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce depuis les paramètres GET ou POST
            $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : (isset($_POST['nonce']) ? $_POST['nonce'] : '');
            $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : (isset($_POST['template_id']) ? intval($_POST['template_id']) : null);
            if (!wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            if (!$template_id) {
                wp_send_json_error('ID de template manquant');
                return;
            }

            // Vérifier que template_processor existe
            if (!isset($this->admin->template_processor) || !$this->admin->template_processor) {
                // Fallback: charger le template directement
                return $this->fallbackLoadTemplate($template_id);
            }

            // Charger le template en utilisant le template processor
            $template = $this->admin->template_processor->loadTemplateRobust($template_id);

            if ($template) {
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
                // error_log('[PDF Builder] ajaxGetTemplate - Template ID: ' . $template_id);
                // error_log('[PDF Builder] ajaxGetTemplate - Template data has name: ' . (isset($template['name']) ? $template['name'] : 'NO'));
                // error_log('[PDF Builder] ajaxGetTemplate - Template data has _db_name: ' . (isset($template['_db_name']) ? $template['_db_name'] : 'NO'));
                // error_log('[PDF Builder] ajaxGetTemplate - DB template name: ' . ($db_template && isset($db_template['name']) ? $db_template['name'] : 'NO DB RECORD'));
                // error_log('[PDF Builder] ajaxGetTemplate - Final template_name: ' . $template_name);

                wp_send_json_success([
                    'template' => $template,
                    'template_name' => $template_name,
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
     * Vider le cache REST
     */
    public function ajaxFlushRestCache()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Vider le cache
            wp_cache_flush();
            delete_transient('pdf_builder_cache');

            wp_send_json_success([
                'message' => 'Cache vidé avec succès'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du vidage du cache: ' . $e->getMessage());
        }
    }

    /**
     * Générer un PDF de commande
     */
    public function ajaxGenerateOrderPdf()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;

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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Vérifications de base de données
            global $wpdb;

            $checks = [
                'templates_table' => $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_templates'") !== null,
                'orders_table' => $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_orders'") !== null,
                'cache_table' => $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_cache'") !== null,
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
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
     * Vider le cache
     */
    public function ajaxClearCache()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Vider tous les caches
            wp_cache_flush();
            delete_transient('pdf_builder_cache');
            delete_transient('pdf_builder_templates_cache');

            // Vider le cache des templates
            if (function_exists('wp_cache_delete')) {
                wp_cache_delete('pdf_builder_templates', 'pdf_builder');
            }

            wp_send_json_success([
                'message' => 'Cache vidé avec succès'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du vidage du cache: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les paramètres
     */
    public function ajaxSaveSettings()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce (temporarily disabled for debugging)
            // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
            //     wp_send_json_error('Nonce invalide');
            //     return;
            // }

            // Sauvegarder les paramètres généraux
            $settings = [
                'pdf_builder_enable_debug' => isset($_POST['enable_debug']) ? '1' : '0',
                'pdf_builder_enable_cache' => isset($_POST['enable_cache']) ? '1' : '0',
                'pdf_builder_cache_timeout' => intval($_POST['cache_timeout'] ?? 3600),
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

            // Vérifier les permissions de base
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
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

                case 'save_performance_settings':
                    $this->handleSavePerformanceSettings();
                    break;

                case 'get_settings':
                    $this->handleGetSettings();
                    break;

                case 'get_template_mappings':
                    $this->handleGetTemplateMappings();
                    break;

                case 'validate_settings':
                    $this->handleValidateSettings();
                    break;

                default:
                    // Action non reconnue - essayer l'ancien système de compatibilité
                    $this->handleLegacyAction($action);
                    break;
            }

        } catch (Exception $e) {
            error_log('PDF Builder - Erreur handler unifié: ' . $e->getMessage());
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
        $existing_settings = get_option('pdf_builder_settings', []);
        update_option($backup_key, $existing_settings, false);

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

            error_log('PHP: Received POST keys: ' . implode(', ', array_keys($_POST)));
            error_log('PHP: Settings to save: ' . implode(', ', array_keys($settings_to_save)));
            error_log('PHP: Templates data: ' . json_encode($templates_data));

            // Sauvegarder les templates séparément si des données existent
            if (!empty($templates_data)) {
                update_option('pdf_builder_order_status_templates', $templates_data);
                error_log('PHP: Templates data saved to pdf_builder_order_status_templates');
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
                $saved = update_option('pdf_builder_settings', $updated_settings);

                // Vérifier s'il y a eu une vraie erreur DB
                global $wpdb;
                $db_error = $wpdb->last_error;

                if (!$saved && !empty($db_error)) {
                    // Erreur DB réelle
                    error_log('PDF Builder - update_option failed. Last DB error: ' . $db_error);
                    error_log('PDF Builder - Settings size: ' . strlen(serialize($updated_settings)));
                    error_log('PDF Builder - Existing settings size: ' . strlen(serialize($existing_settings)));
                    error_log('PDF Builder - New settings count: ' . count($settings_to_save));

                    // Rollback en cas d'échec
                    $this->rollbackSettings($backup_key);
                    wp_send_json_error(['message' => 'Erreur lors de la sauvegarde en base de données']);
                    return;
                }
            }

            // Supprimer le backup si succès
            delete_option($backup_key);

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
            error_log('PDF Builder - Erreur sauvegarde: ' . $e->getMessage());
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
        $existing_settings = get_option('pdf_builder_settings', []);
        $updated_settings = array_merge($existing_settings, $general_settings);
        $saved = update_option('pdf_builder_settings', $updated_settings);

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
            'pdf_builder_cache_enabled',
            'pdf_builder_cache_max_size',
            'pdf_builder_cache_ttl',
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
        $existing_settings = get_option('pdf_builder_settings', []);
        $updated_settings = array_merge($existing_settings, $performance_settings);
        $saved = update_option('pdf_builder_settings', $updated_settings);

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
        $settings = get_option('pdf_builder_settings', []);
        wp_send_json_success([
            'settings' => $settings,
            'action' => 'get_settings'
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
            $mappings = get_option('pdf_builder_order_status_templates', []);

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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                wp_send_json_error('Nonce invalide');
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Paramètres de performance
            $settings = [
                'pdf_builder_enable_cache' => isset($_POST['enable_cache']) ? '1' : '0',
                'pdf_builder_cache_timeout' => intval($_POST['cache_timeout'] ?? 3600),
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
     * Sauvegarder les paramètres canvas
     */
    public function ajaxSaveCanvasSettings()
    {
        error_log('🎯 [PHP AJAX] ajaxSaveCanvasSettings METHOD CALLED');

        // LOGS PHP DÉTAILLÉS POUR DÉBOGAGE MAXIMAL
        error_log('🚀 [PHP AJAX SAVE] === DÉBUT ajaxSaveCanvasSettings === ' . date('Y-m-d H:i:s'));
        error_log('🔍 [PHP AJAX SAVE] REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
        error_log('🔍 [PHP AJAX SAVE] REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
        error_log('🔍 [PHP AJAX SAVE] HTTP_REFERER: ' . ($_SERVER['HTTP_REFERER'] ?? 'null'));
        error_log('🔍 [PHP AJAX SAVE] User logged in: ' . (is_user_logged_in() ? 'YES' : 'NO'));
        error_log('🔍 [PHP AJAX SAVE] User capabilities: ' . (current_user_can('manage_options') ? 'ADMIN' : 'LIMITED'));
        error_log('🔍 [PHP AJAX SAVE] Current user ID: ' . get_current_user_id());
        error_log('🔍 [PHP AJAX SAVE] $_POST data: ' . print_r($_POST, true));
        error_log('🔍 [PHP AJAX SAVE] $_FILES data: ' . print_r($_FILES, true));
        error_log('🔍 [PHP AJAX SAVE] $_SERVER data: ' . print_r($_SERVER, true));

        try {
            // Vérifier les permissions
            if (!is_user_logged_in()) {
                error_log('❌ [PHP AJAX SAVE] ERREUR: Utilisateur non connecté');
                wp_send_json_error('Utilisateur non connecté');
                return;
            }
            error_log('✅ [PHP AJAX SAVE] Utilisateur connecté');

            // Vérifier le nonce
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            error_log('🔍 [PHP AJAX SAVE] Nonce reçu: ' . $nonce);

            if (!wp_verify_nonce($nonce, 'pdf_builder_ajax') &&
                !wp_verify_nonce($nonce, 'pdf_builder_order_actions') &&
                !wp_verify_nonce($nonce, 'pdf_builder_templates') &&
                !wp_verify_nonce($nonce, 'pdf_builder_canvas_settings') &&
                !wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
                error_log('❌ [PHP AJAX SAVE] ERREUR: Nonce invalide');
                wp_send_json_error('Nonce invalide');
                return;
            }
            error_log('✅ [PHP AJAX SAVE] Nonce valide');

            // Récupérer la catégorie
            $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
            error_log('🔍 [PHP AJAX SAVE] Catégorie reçue: ' . $category);

            if (empty($category)) {
                error_log('❌ [PHP AJAX SAVE] ERREUR: Catégorie manquante');
                wp_send_json_error('Catégorie manquante');
                return;
            }
            error_log('✅ [PHP AJAX SAVE] Catégorie valide: ' . $category);

            // Sauvegarder selon la catégorie
            $saved = false;
            switch ($category) {
                case 'dimensions':
                    $saved = $this->saveDimensionsSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_width' => get_option('pdf_builder_canvas_width', '794'),
                            'canvas_height' => get_option('pdf_builder_canvas_height', '1123'),
                            'canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
                            'canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
                            'canvas_dpi' => get_option('pdf_builder_canvas_dpi', '96')
                        ];
                    }
                    break;
                case 'zoom':
                    $saved = $this->saveZoomSettings();
                    if ($saved) {
                        $savedData = [
                            'zoom_min' => get_option('pdf_builder_canvas_zoom_min', '10'),
                            'zoom_max' => get_option('pdf_builder_canvas_zoom_max', '500'),
                            'zoom_default' => get_option('pdf_builder_canvas_zoom_default', '100'),
                            'zoom_step' => get_option('pdf_builder_canvas_zoom_step', '25')
                        ];
                    }
                    break;
                case 'apparence':
                    $saved = $this->saveApparenceSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_bg_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
                            'canvas_border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
                            'canvas_border_width' => get_option('pdf_builder_canvas_border_width', '1'),
                            'canvas_shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0'),
                            'canvas_container_bg_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa')
                        ];
                    }
                    break;
                case 'grille':
                    $saved = $this->saveGrilleSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_guides_enabled' => get_option('pdf_builder_canvas_guides_enabled', '1'),
                            'canvas_grid_enabled' => get_option('pdf_builder_canvas_grid_enabled', '1'),
                            'canvas_grid_size' => get_option('pdf_builder_canvas_grid_size', '20'),
                            'canvas_snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1')
                        ];
                    }
                    break;
                case 'interactions':
                    $saved = $this->saveInteractionsSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_drag_enabled' => get_option('pdf_builder_canvas_drag_enabled', '1'),
                            'canvas_resize_enabled' => get_option('pdf_builder_canvas_resize_enabled', '1'),
                            'canvas_rotate_enabled' => get_option('pdf_builder_canvas_rotate_enabled', '1'),
                            'canvas_multi_select' => get_option('pdf_builder_canvas_multi_select', '1'),
                            'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'bounding_box'),
                            'canvas_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1')
                        ];
                    }
                    break;
                case 'export':
                    $saved = $this->saveExportSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
                            'canvas_export_quality' => get_option('pdf_builder_canvas_export_quality', '90'),
                            'canvas_export_transparent' => get_option('pdf_builder_canvas_export_transparent', '0')
                        ];
                    }
                    break;
                case 'performance':
                    $saved = $this->savePerformanceSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_fps_target' => get_option('pdf_builder_canvas_fps_target', '60'),
                            'canvas_memory_limit_js' => get_option('pdf_builder_canvas_memory_limit_js', '128'),
                            'canvas_memory_limit_php' => get_option('pdf_builder_canvas_memory_limit_php', '256'),
                            'canvas_lazy_loading_editor' => get_option('pdf_builder_canvas_lazy_loading_editor', '1'),
                            'canvas_preload_critical' => get_option('pdf_builder_canvas_preload_critical', '1'),
                            'canvas_lazy_loading_plugin' => get_option('pdf_builder_canvas_lazy_loading_plugin', '1')
                        ];
                    }
                    break;
                case 'debug':
                    $saved = $this->saveDebugSettings();
                    if ($saved) {
                        $savedData = [
                            'canvas_debug_enabled' => get_option('pdf_builder_canvas_debug_enabled', '0'),
                            'canvas_performance_monitoring' => get_option('pdf_builder_canvas_performance_monitoring', '0'),
                            'canvas_error_reporting' => get_option('pdf_builder_canvas_error_reporting', '0')
                        ];
                    }
                    break;
                default:
                    wp_send_json_error('Catégorie inconnue: ' . $category);
                    return;
            }

            if ($saved) {
                error_log('✅ [PHP AJAX] Sauvegarde réussie pour catégorie: ' . $category);
                error_log('🔍 [PHP AJAX] Données sauvegardées: ' . print_r($savedData ?? [], true));

                $response = [
                    'message' => 'Paramètres ' . $category . ' sauvegardés avec succès',
                    'debug' => [
                        'category' => $category,
                        'saved' => $saved,
                        'post_data' => $_POST,
                        'timestamp' => time()
                    ]
                ];
                if (isset($savedData)) {
                    $response['saved'] = $savedData;
                }
                error_log('📤 [PHP AJAX] Réponse de succès envoyée: ' . print_r($response, true));
                wp_send_json_success($response);
            } else {
                error_log('❌ [PHP AJAX] ERREUR: Échec de la sauvegarde pour catégorie: ' . $category);
                wp_send_json_error('Erreur lors de la sauvegarde des paramètres ' . $category);
            }

        } catch (Exception $e) {
            error_log('💥 [PHP AJAX] EXCEPTION: ' . $e->getMessage());
            error_log('🔍 [PHP AJAX] Trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }

        error_log('🏁 [PHP AJAX] Fin ajaxSaveCanvasSettings - ' . date('Y-m-d H:i:s'));
    }

    /**
     * Récupérer les paramètres canvas
     */
    public function ajaxGetCanvasSettings()
    {
        // LOGS PHP DÉTAILLÉS POUR DÉBOGAGE
        error_log('🚀 [PHP AJAX GET] Début ajaxGetCanvasSettings - ' . date('Y-m-d H:i:s'));
        error_log('🔍 [PHP AJAX GET] REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
        error_log('🔍 [PHP AJAX GET] User logged in: ' . (is_user_logged_in() ? 'YES' : 'NO'));
        error_log('🔍 [PHP AJAX GET] Current user ID: ' . get_current_user_id());
        error_log('🔍 [PHP AJAX GET] $_POST data: ' . print_r($_POST, true));

        try {
            // Vérifier les permissions
            if (!is_user_logged_in()) {
                error_log('❌ [PHP AJAX GET] ERREUR: Utilisateur non connecté');
                wp_send_json_error('Utilisateur non connecté');
                return;
            }
            error_log('✅ [PHP AJAX GET] Utilisateur connecté');

            // Vérifier le nonce
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            error_log('🔍 [PHP AJAX GET] Nonce reçu: ' . $nonce);

            if (!wp_verify_nonce($nonce, 'pdf_builder_ajax') &&
                !wp_verify_nonce($nonce, 'pdf_builder_order_actions') &&
                !wp_verify_nonce($nonce, 'pdf_builder_templates') &&
                !wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
                error_log('❌ [PHP AJAX GET] ERREUR: Nonce invalide');
                wp_send_json_error('Nonce invalide');
                return;
            }
            error_log('✅ [PHP AJAX GET] Nonce valide');

            // Récupérer les paramètres depuis Canvas_Manager
            $canvas_manager = \PDF_Builder\Canvas\Canvas_Manager::get_instance();
            $canvas_settings = $canvas_manager->get_all_settings();

            $response = [
                'canvas_settings' => $canvas_settings,
                'message' => 'Paramètres canvas récupérés avec succès',
                'debug' => [
                    'timestamp' => time(),
                    'user_id' => get_current_user_id(),
                    'request_data' => $_POST
                ]
            ];

            error_log('📤 [PHP AJAX GET] Réponse de succès envoyée: ' . print_r($response, true));
            wp_send_json_success($response);

        } catch (Exception $e) {
            error_log('💥 [PHP AJAX GET] EXCEPTION: ' . $e->getMessage());
            error_log('🔍 [PHP AJAX GET] Trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur lors de la récupération: ' . $e->getMessage());
        }

        error_log('🏁 [PHP AJAX GET] Fin ajaxGetCanvasSettings - ' . date('Y-m-d H:i:s'));
    }

    // Méthodes privées pour sauvegarder les paramètres canvas

    private function saveDimensionsSettings()
    {
        $updated = 0;

        // Format du document
        if (isset($_POST['pdf_builder_canvas_format'])) {
            $format = sanitize_text_field($_POST['pdf_builder_canvas_format']);
            // Validation des formats supportés
            $valid_formats = ['A4', 'A3', 'A5', 'Letter', 'Legal', 'Tabloid'];
            if (in_array($format, $valid_formats)) {
                update_option('pdf_builder_canvas_format', $format);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Orientation (actuellement forcée en portrait)
        // TODO: Implémenter l'orientation paysage dans v2.0
        update_option('pdf_builder_canvas_orientation', 'portrait');
        wp_cache_delete('alloptions', 'options');
        $updated++;

        // Résolution DPI
        if (isset($_POST['pdf_builder_canvas_dpi'])) {
            $dpi = intval($_POST['pdf_builder_canvas_dpi']);
            // Validation des DPI supportés
            $valid_dpi = [72, 96, 150, 300];
            if (in_array($dpi, $valid_dpi)) {
                update_option('pdf_builder_canvas_dpi', $dpi);
                wp_cache_delete('alloptions', 'options');

                // Recalculer les dimensions en pixels basées sur le nouveau DPI
                $this->updateCanvasDimensionsFromFormat($dpi);

                $updated++;
            }
        }

        return $updated > 0;
    }

    /**
     * Met à jour les dimensions du canvas en pixels basées sur le format et le DPI
     */
    private function updateCanvasDimensionsFromFormat($dpi)
    {
        $format = get_option('pdf_builder_canvas_format', 'A4');
        $orientation = get_option('pdf_builder_canvas_orientation', 'portrait');

        // Utiliser les dimensions standard centralisées
        $formatDimensionsMM = \PDF_Builder\PAPER_FORMATS;

        $dimensions = isset($formatDimensionsMM[$format]) ? $formatDimensionsMM[$format] : $formatDimensionsMM['A4'];

        // Appliquer l'orientation (actuellement toujours portrait)
        // TODO: Implémenter l'orientation paysage dans v2.0
        // if ($orientation === 'landscape') {
        //     $temp = $dimensions['width'];
        //     $dimensions['width'] = $dimensions['height'];
        //     $dimensions['height'] = $temp;
        // }

        // Convertir mm en pixels (1 pouce = 25.4 mm)
        $widthPx = round(($dimensions['width'] / 25.4) * $dpi);
        $heightPx = round(($dimensions['height'] / 25.4) * $dpi);

        // Sauvegarder les dimensions en pixels
        update_option('pdf_builder_canvas_width', $widthPx);
        wp_cache_delete('alloptions', 'options');
        update_option('pdf_builder_canvas_height', $heightPx);
        wp_cache_delete('alloptions', 'options');
    }

    private function saveZoomSettings()
    {
        $updated = 0;

        // Zoom minimum
        if (isset($_POST['pdf_builder_canvas_zoom_min'])) {
            $min = intval($_POST['pdf_builder_canvas_zoom_min']);
            if ($min >= 1 && $min <= 100) {
                update_option('pdf_builder_canvas_zoom_min', $min);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Zoom maximum
        if (isset($_POST['pdf_builder_canvas_zoom_max'])) {
            $max = intval($_POST['pdf_builder_canvas_zoom_max']);
            if ($max >= 100 && $max <= 1000) {
                update_option('pdf_builder_canvas_zoom_max', $max);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Zoom par défaut
        if (isset($_POST['pdf_builder_canvas_zoom_default'])) {
            $default = intval($_POST['pdf_builder_canvas_zoom_default']);
            if ($default >= 10 && $default <= 500) {
                update_option('pdf_builder_canvas_zoom_default', $default);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Pas de zoom
        if (isset($_POST['pdf_builder_canvas_zoom_step'])) {
            $step = intval($_POST['pdf_builder_canvas_zoom_step']);
            if ($step >= 5 && $step <= 50) {
                update_option('pdf_builder_canvas_zoom_step', $step);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        return $updated > 0;
    }

    private function saveApparenceSettings()
    {
        error_log("[PDF Builder Debug] saveApparenceSettings called");
        $updated = 0;

        // Couleur de fond du canvas
        if (isset($_POST['pdf_builder_canvas_bg_color'])) {
            $old_value = get_option('pdf_builder_canvas_bg_color', '#ffffff');
            update_option('pdf_builder_canvas_bg_color', sanitize_hex_color($_POST['pdf_builder_canvas_bg_color']));
            wp_cache_delete('alloptions', 'options');
            $new_value = get_option('pdf_builder_canvas_bg_color', '#ffffff');
            $updated++;
        }

        // Couleur des bordures
        if (isset($_POST['pdf_builder_canvas_border_color'])) {
            $old_value = get_option('pdf_builder_canvas_border_color', '#cccccc');
            update_option('pdf_builder_canvas_border_color', sanitize_hex_color($_POST['pdf_builder_canvas_border_color']));
            wp_cache_delete('alloptions', 'options');
            $new_value = get_option('pdf_builder_canvas_border_color', '#cccccc');
            $updated++;
        }

        // Épaisseur des bordures
        if (isset($_POST['pdf_builder_canvas_border_width'])) {
            $old_value = get_option('pdf_builder_canvas_border_width', '1');
            $width = intval($_POST['pdf_builder_canvas_border_width']);
            if ($width >= 0 && $width <= 10) {
                update_option('pdf_builder_canvas_border_width', $width);
                wp_cache_delete('alloptions', 'options');
                $new_value = get_option('pdf_builder_canvas_border_width', '1');
                $updated++;
            }
        }

        // Ombre activée
        if (isset($_POST['pdf_builder_canvas_shadow_enabled'])) {
            $old_value = get_option('pdf_builder_canvas_shadow_enabled', '0');
            $received_value = $_POST['pdf_builder_canvas_shadow_enabled'];
            error_log("[PDF Builder Debug] Shadow enabled received: " . $received_value . " (old: " . $old_value . ")");
            // Convertir en 1 ou 0
            $new_value_bool = in_array(strtolower($received_value), ['1', 'on', 'true', 'yes']) ? '1' : '0';
            $result = update_option('pdf_builder_canvas_shadow_enabled', $new_value_bool);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] APPARANCE_TOGGLE_SAVE - pdf_builder_canvas_shadow_enabled: ' . $new_value_bool . ' (update_result: ' . ($result ? 'true' : 'false') . ')');

            // Synchroniser avec le tableau groupé pdf_builder_canvas_settings
            $canvas_settings = get_option('pdf_builder_canvas_settings', []);
            $canvas_settings['shadow_enabled'] = $new_value_bool;
            update_option('pdf_builder_canvas_settings', $canvas_settings);
            wp_cache_delete('alloptions', 'options');
            error_log("[PDF Builder Debug] Updated canvas_settings shadow_enabled to: " . $new_value_bool);

            // Synchroniser avec le tableau principal pdf_builder_settings
            $main_settings = get_option('pdf_builder_settings', []);
            $main_settings['pdf_builder_canvas_shadow_enabled'] = $new_value_bool;
            update_option('pdf_builder_settings', $main_settings);
            wp_cache_delete('alloptions', 'options');
            error_log("[PDF Builder Debug] Updated main settings pdf_builder_canvas_shadow_enabled to: " . $new_value_bool);

            $new_value = get_option('pdf_builder_canvas_shadow_enabled', '0');
            error_log("[PDF Builder Debug] Shadow enabled updated to: " . $new_value);
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_shadow_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_shadow_enabled DB value: ' . $db_value);
            $updated++;
        } else {
            $old_value = get_option('pdf_builder_canvas_shadow_enabled', '0');
            error_log("[PDF Builder Debug] Shadow enabled not set in POST, setting to 0 (old: " . $old_value . ")");
            $result = update_option('pdf_builder_canvas_shadow_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] APPARANCE_TOGGLE_SAVE - pdf_builder_canvas_shadow_enabled: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_shadow_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_shadow_enabled DB value: ' . $db_value);
            $updated++;
        }

        // Arrière-plan de l'éditeur
        if (isset($_POST['pdf_builder_canvas_container_bg_color'])) {
            $old_value = get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa');
            update_option('pdf_builder_canvas_container_bg_color', sanitize_hex_color($_POST['pdf_builder_canvas_container_bg_color']));
            wp_cache_delete('alloptions', 'options');
            $new_value = get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa');
            $updated++;
        }

        return $updated > 0;
    }

    private function saveGrilleSettings()
    {
        error_log('🚀 [PHP AJAX SAVE] === saveGrilleSettings START === ' . date('Y-m-d H:i:s'));
        $updated = 0;

        // Guides activés
        if (isset($_POST['pdf_builder_canvas_guides_enabled'])) {
            $value = $_POST['pdf_builder_canvas_guides_enabled'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_guides_enabled', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] GRID_TOGGLE_SAVE - pdf_builder_canvas_guides_enabled: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_guides_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_guides_enabled DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_guides_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] GRID_TOGGLE_SAVE - pdf_builder_canvas_guides_enabled: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_guides_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_guides_enabled DB value: ' . $db_value);
            $updated++;
        }

        // Grille activée
        if (isset($_POST['pdf_builder_canvas_grid_enabled'])) {
            $value = $_POST['pdf_builder_canvas_grid_enabled'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_grid_enabled', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] GRID_TOGGLE_SAVE - pdf_builder_canvas_grid_enabled: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_grid_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_grid_enabled DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_grid_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] GRID_TOGGLE_SAVE - pdf_builder_canvas_grid_enabled: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_grid_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_grid_enabled DB value: ' . $db_value);
            $updated++;
        }

        // Taille de la grille
        if (isset($_POST['pdf_builder_canvas_grid_size'])) {
            $size = intval($_POST['pdf_builder_canvas_grid_size']);
            if ($size >= 5 && $size <= 100) {
                $result = update_option('pdf_builder_canvas_grid_size', $size);
                wp_cache_delete('alloptions', 'options');
                error_log('🔄 [PHP AJAX SAVE] GRID_SIZE_SAVE - pdf_builder_canvas_grid_size: ' . $size . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
                $updated++;
            }
        }

        // Accrochage à la grille
        if (isset($_POST['pdf_builder_canvas_snap_to_grid'])) {
            $value = $_POST['pdf_builder_canvas_snap_to_grid'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_snap_to_grid', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] GRID_TOGGLE_SAVE - pdf_builder_canvas_snap_to_grid: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_snap_to_grid'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_snap_to_grid DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_snap_to_grid', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] GRID_TOGGLE_SAVE - pdf_builder_canvas_snap_to_grid: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_snap_to_grid'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_snap_to_grid DB value: ' . $db_value);
            $updated++;
        }

        return $updated > 0;
    }

    private function saveInteractionsSettings()
    {
        $updated = 0;

        // Glisser-déposer activé
        if (isset($_POST['pdf_builder_canvas_drag_enabled'])) {
            $value = $_POST['pdf_builder_canvas_drag_enabled'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_drag_enabled', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_drag_enabled: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_drag_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_drag_enabled DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_drag_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_drag_enabled: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_drag_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_drag_enabled DB value: ' . $db_value);
            $updated++;
        }

        // Redimensionnement activé
        if (isset($_POST['pdf_builder_canvas_resize_enabled'])) {
            $value = $_POST['pdf_builder_canvas_resize_enabled'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_resize_enabled', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_resize_enabled: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_resize_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_resize_enabled DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_resize_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_resize_enabled: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_resize_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_resize_enabled DB value: ' . $db_value);
            $updated++;
        }

        // Rotation activée
        if (isset($_POST['pdf_builder_canvas_rotate_enabled'])) {
            $value = $_POST['pdf_builder_canvas_rotate_enabled'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_rotate_enabled', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_rotate_enabled: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_rotate_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_rotate_enabled DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_rotate_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_rotate_enabled: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_rotate_enabled'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_rotate_enabled DB value: ' . $db_value);
            $updated++;
        }

        // Sélection multiple
        if (isset($_POST['pdf_builder_canvas_multi_select'])) {
            $value = $_POST['pdf_builder_canvas_multi_select'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_multi_select', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_multi_select: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_multi_select'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_multi_select DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_multi_select', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_multi_select: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_multi_select'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_multi_select DB value: ' . $db_value);
            $updated++;
        }

        // Mode de sélection
        if (isset($_POST['pdf_builder_canvas_selection_mode'])) {
            update_option('pdf_builder_canvas_selection_mode', sanitize_text_field($_POST['pdf_builder_canvas_selection_mode']));
            wp_cache_delete('alloptions', 'options');
            $updated++;
        }

        // Raccourcis clavier
        if (isset($_POST['pdf_builder_canvas_keyboard_shortcuts'])) {
            $value = $_POST['pdf_builder_canvas_keyboard_shortcuts'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_keyboard_shortcuts', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_keyboard_shortcuts: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_keyboard_shortcuts'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_keyboard_shortcuts DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_keyboard_shortcuts', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] INTERACTIONS_TOGGLE_SAVE - pdf_builder_canvas_keyboard_shortcuts: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_keyboard_shortcuts'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_keyboard_shortcuts DB value: ' . $db_value);
            $updated++;
        }

        return $updated > 0;
    }

    private function saveExportSettings()
    {
        $updated = 0;

        // Format d'export
        if (isset($_POST['pdf_builder_canvas_export_format'])) {
            $format = sanitize_text_field($_POST['pdf_builder_canvas_export_format']);
            if (in_array($format, ['png', 'jpg', 'svg'])) {
                update_option('pdf_builder_canvas_export_format', $format);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Qualité d'export
        if (isset($_POST['pdf_builder_canvas_export_quality'])) {
            $quality = intval($_POST['pdf_builder_canvas_export_quality']);
            if ($quality >= 1 && $quality <= 100) {
                update_option('pdf_builder_canvas_export_quality', $quality);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Fond transparent
        if (isset($_POST['pdf_builder_canvas_export_transparent'])) {
            $value = $_POST['pdf_builder_canvas_export_transparent'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_export_transparent', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] EXPORT_TOGGLE_SAVE - pdf_builder_canvas_export_transparent: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_export_transparent'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_export_transparent DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_export_transparent', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] EXPORT_TOGGLE_SAVE - pdf_builder_canvas_export_transparent: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_export_transparent'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_export_transparent DB value: ' . $db_value);
            $updated++;
        }

        return $updated > 0;
    }

    private function savePerformanceSettings()
    {
        $updated = 0;

        // Cible FPS
        if (isset($_POST['pdf_builder_canvas_fps_target'])) {
            $fps = intval($_POST['canvas_fps_target']);
            if (in_array($fps, [30, 60, 120])) {
                update_option('pdf_builder_canvas_fps_target', $fps);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Limite mémoire JavaScript
        if (isset($_POST['pdf_builder_canvas_memory_limit_js'])) {
            $memory_js = sanitize_text_field($_POST['canvas_memory_limit_js']);
            if (in_array($memory_js, ['128', '256', '512', '1024'])) {
                update_option('pdf_builder_canvas_memory_limit_js', $memory_js);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Chargement paresseux éditeur
        if (isset($_POST['pdf_builder_canvas_lazy_loading_editor'])) {
            $value = $_POST['pdf_builder_canvas_lazy_loading_editor'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_lazy_loading_editor', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] PERFORMANCE_TOGGLE_SAVE - pdf_builder_canvas_lazy_loading_editor: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_lazy_loading_editor'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_lazy_loading_editor DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_lazy_loading_editor', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] PERFORMANCE_TOGGLE_SAVE - pdf_builder_canvas_lazy_loading_editor: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_lazy_loading_editor'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_lazy_loading_editor DB value: ' . $db_value);
            $updated++;
        }

        // Préchargement ressources critiques
        if (isset($_POST['pdf_builder_canvas_preload_critical'])) {
            $value = $_POST['pdf_builder_canvas_preload_critical'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_preload_critical', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] PERFORMANCE_TOGGLE_SAVE - pdf_builder_canvas_preload_critical: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_preload_critical'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_preload_critical DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_preload_critical', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] PERFORMANCE_TOGGLE_SAVE - pdf_builder_canvas_preload_critical: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_preload_critical'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_preload_critical DB value: ' . $db_value);
            $updated++;
        }

        // Limite mémoire PHP
        if (isset($_POST['pdf_builder_canvas_memory_limit_php'])) {
            $memory_php = sanitize_text_field($_POST['canvas_memory_limit_php']);
            if (in_array($memory_php, ['128', '256', '512', '1024'])) {
                update_option('pdf_builder_canvas_memory_limit_php', $memory_php);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Timeout réponses AJAX
        if (isset($_POST['pdf_builder_canvas_response_timeout'])) {
            $timeout = intval($_POST['canvas_response_timeout']);
            if (in_array($timeout, [10, 30, 60, 120])) {
                update_option('pdf_builder_canvas_response_timeout', $timeout);
                wp_cache_delete('alloptions', 'options');
                $updated++;
            }
        }

        // Chargement paresseux plugin
        if (isset($_POST['pdf_builder_canvas_lazy_loading_plugin'])) {
            $value = $_POST['pdf_builder_canvas_lazy_loading_plugin'] == '1' ? '1' : '0';
            $result = update_option('pdf_builder_canvas_lazy_loading_plugin', $value);
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] PERFORMANCE_TOGGLE_SAVE - pdf_builder_canvas_lazy_loading_plugin: ' . $value . ' (update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_lazy_loading_plugin'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_lazy_loading_plugin DB value: ' . $db_value);
            $updated++;
        } else {
            $result = update_option('pdf_builder_canvas_lazy_loading_plugin', '0');
            wp_cache_delete('alloptions', 'options');
            error_log('🔄 [PHP AJAX SAVE] PERFORMANCE_TOGGLE_SAVE - pdf_builder_canvas_lazy_loading_plugin: 0 (not set, update_result: ' . ($result ? 'true' : 'false') . ')');
            // Vérifier immédiatement dans la DB
            global $wpdb;
            $db_value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'pdf_builder_canvas_lazy_loading_plugin'));
            error_log('🔍 [PHP AJAX SAVE] DB_VERIFY - pdf_builder_canvas_lazy_loading_plugin DB value: ' . $db_value);
            $updated++;
        }

        return $updated > 0;
    }

    private function saveDebugSettings()
    {
        $updated = 0;

        // Debug activé
        if (isset($_POST['pdf_builder_canvas_debug_enabled'])) {
            update_option('pdf_builder_canvas_debug_enabled', '1');
            wp_cache_delete('alloptions', 'options');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_debug_enabled', '0');
            wp_cache_delete('alloptions', 'options');
            $updated++;
        }

        // Monitoring performance
        if (isset($_POST['pdf_builder_canvas_performance_monitoring'])) {
            update_option('pdf_builder_canvas_performance_monitoring', '1');
            wp_cache_delete('alloptions', 'options');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_performance_monitoring', '0');
            wp_cache_delete('alloptions', 'options');
            $updated++;
        }

        // Rapport d'erreurs
        if (isset($_POST['pdf_builder_canvas_error_reporting'])) {
            update_option('pdf_builder_canvas_error_reporting', '1');
            wp_cache_delete('alloptions', 'options');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_error_reporting', '0');
            wp_cache_delete('alloptions', 'options');
            $updated++;
        }

        return $updated > 0;
    }

    /**
     * AJAX - Vérifier la limite de templates
     */
    public function ajaxCheckTemplateLimit()
    {
        // Vérification nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
        }

        // Vérification permissions
        if (!current_user_can('manage_options')) {
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
            $checks['options_accessible'] = is_array(get_option('pdf_builder_settings', []));
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
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Vérifier que la table existe
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                wp_send_json_error('Table des templates introuvable');
                return;
            }

            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            if (!$template) {
                wp_send_json_error('Template introuvable');
                return;
            }

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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
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
            $existing_settings = get_option('pdf_builder_settings', []);

            // Fusionner avec les nouveaux paramètres
            $updated_settings = array_merge($existing_settings, $settings_to_save);

            // Sauvegarder dans la base de données
            $saved = update_option('pdf_builder_settings', $updated_settings);

            if ($saved) {
                wp_send_json_success([
                    'message' => 'Paramètres sauvegardés avec succès',
                    'saved_settings' => $settings_to_save
                ]);
            } else {
                wp_send_json_error(['message' => 'Erreur lors de la sauvegarde en base de données']);
            }

        } catch (Exception $e) {
            error_log('PDF Builder - Erreur sauvegarde unifiée: ' . $e->getMessage());
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
            update_option('pdf_builder_settings', $backup);
            delete_option($backup_key);
            error_log('PDF Builder - Rollback effectué depuis backup: ' . $backup_key);
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
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            error_log('PHP: ajaxSaveOrderStatusTemplates called');
            error_log('PHP: POST data: ' . print_r($_POST, true));

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
            update_option('pdf_builder_order_status_templates', $clean_templates);
            error_log('PHP: Saved to DB in ajaxSaveOrderStatusTemplates: ' . print_r($clean_templates, true));
            error_log('PHP: DB content after save: ' . print_r(get_option('pdf_builder_order_status_templates', []), true));

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
}
