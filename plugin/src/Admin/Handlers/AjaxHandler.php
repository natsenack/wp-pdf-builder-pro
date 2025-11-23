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
        // Hooks AJAX principaux
        add_action('wp_ajax_pdf_builder_generate_pdf_from_canvas', [$this, 'ajaxGeneratePdfFromCanvas']);
        add_action('wp_ajax_pdf_builder_download_pdf', [$this, 'ajaxDownloadPdf']);
        add_action('wp_ajax_pdf_builder_save_template_v3', [$this, 'ajaxSaveTemplateV3']);
        add_action('wp_ajax_pdf_builder_auto_save_template', [$this, 'ajaxAutoSaveTemplateWrapper']);
        add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajaxLoadTemplate']);
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajaxFlushRestCache']);
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajaxGenerateOrderPdf']);

        // Hooks AJAX de maintenance
        add_action('wp_ajax_pdf_builder_check_database', [$this, 'ajaxCheckDatabase']);
        add_action('wp_ajax_pdf_builder_repair_database', [$this, 'ajaxRepairDatabase']);
        add_action('wp_ajax_pdf_builder_execute_sql_repair', [$this, 'ajaxExecuteSqlRepair']);
        add_action('wp_ajax_pdf_builder_clear_cache', [$this, 'ajaxClearCache']);
        add_action('wp_ajax_pdf_builder_check_integrity', [$this, 'ajaxCheckIntegrity']);

        // Hooks AJAX de paramètres
        add_action('wp_ajax_pdf_builder_save_settings', [$this, 'ajaxSaveSettings']);
        add_action('wp_ajax_pdf_builder_save_settings_page', [$this, 'ajaxSaveSettingsPage']);
        add_action('wp_ajax_pdf_builder_save_general_settings', [$this, 'ajaxSaveGeneralSettings']);
        add_action('wp_ajax_pdf_builder_save_performance_settings', [$this, 'ajaxSavePerformanceSettings']);

        // Hooks AJAX templates
        add_action('wp_ajax_pdf_builder_check_template_limit', [$this, 'ajaxCheckTemplateLimit']);

        // Hooks AJAX canvas
        add_action('wp_ajax_pdf_builder_save_canvas_settings', [$this, 'ajaxSaveCanvasSettings']);
        add_action('wp_ajax_pdf_builder_get_canvas_settings', [$this, 'ajaxGetCanvasSettings']);
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
        if ($this->admin->template_manager && method_exists($this->admin->template_manager, 'ajaxSaveTemplateV3')) {
            $this->admin->template_manager->ajaxSaveTemplateV3();
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            $result = $this->admin->saveTemplate($template_data, $template_name, $template_id);

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
    public function ajaxAutoSaveTemplateWrapper()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            $template_data = isset($_POST['template_data']) ? json_decode(stripslashes($_POST['template_data']), true) : null;

            if (!$template_data) {
                wp_send_json_error('Données de template manquantes');
                return;
            }

            // Sauvegarde automatique (sans nom, juste les données)
            $auto_save_key = 'pdf_builder_auto_save_' . get_current_user_id();
            update_option($auto_save_key, $template_data);

            wp_send_json_success([
                'message' => 'Sauvegarde automatique effectuée'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la sauvegarde automatique: ' . $e->getMessage());
        }
    }

    /**
     * Charger un template
     */
    public function ajaxLoadTemplate()
    {
        // Déléguer au template manager si disponible
        if ($this->admin->template_manager && method_exists($this->admin->template_manager, 'ajaxLoadTemplate')) {
            $this->admin->template_manager->ajaxLoadTemplate();
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
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

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

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
     * Sauvegarder la page de paramètres
     */
    public function ajaxSaveSettingsPage()
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

            // Collecter tous les paramètres
            $settings = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'pdf_builder_') === 0) {
                    $settings[$key] = sanitize_text_field($value);
                }
            }

            // Sauvegarder
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
        try {
            // Vérifier les permissions
            if (!is_user_logged_in()) {
                wp_send_json_error('Utilisateur non connecté');
                return;
            }

            // Vérifier le nonce
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if (!wp_verify_nonce($nonce, 'pdf_builder_nonce') &&
                !wp_verify_nonce($nonce, 'pdf_builder_order_actions') &&
                !wp_verify_nonce($nonce, 'pdf_builder_templates') &&
                !wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Récupérer la catégorie
            $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

            if (empty($category)) {
                wp_send_json_error('Catégorie manquante');
                return;
            }

            // Sauvegarder selon la catégorie
            $saved = false;
            switch ($category) {
                case 'dimensions':
                    $saved = $this->saveDimensionsSettings();
                    break;
                case 'zoom':
                    $saved = $this->saveZoomSettings();
                    break;
                case 'apparence':
                    $saved = $this->saveApparenceSettings();
                    break;
                case 'grille':
                    $saved = $this->saveGrilleSettings();
                    break;
                case 'interactions':
                    $saved = $this->saveInteractionsSettings();
                    break;
                case 'export':
                    $saved = $this->saveExportSettings();
                    break;
                case 'performance':
                    $saved = $this->savePerformanceSettings();
                    break;
                case 'autosave':
                    $saved = $this->saveAutosaveSettings();
                    break;
                case 'debug':
                    $saved = $this->saveDebugSettings();
                    break;
                default:
                    wp_send_json_error('Catégorie inconnue: ' . $category);
                    return;
            }

            if ($saved) {
                wp_send_json_success([
                    'message' => 'Paramètres ' . $category . ' sauvegardés avec succès'
                ]);
            } else {
                wp_send_json_error('Erreur lors de la sauvegarde des paramètres ' . $category);
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer les paramètres canvas
     */
    public function ajaxGetCanvasSettings()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in()) {
                wp_send_json_error('Utilisateur non connecté');
                return;
            }

            // Vérifier le nonce
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if (!wp_verify_nonce($nonce, 'pdf_builder_nonce') &&
                !wp_verify_nonce($nonce, 'pdf_builder_order_actions') &&
                !wp_verify_nonce($nonce, 'pdf_builder_templates')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Pour l'instant, retourner des paramètres par défaut
            wp_send_json_success([
                'canvas_settings' => [
                    'width' => 1123,
                    'height' => 794,
                    'unit' => 'mm',
                    'orientation' => 'landscape'
                ],
                'message' => 'Paramètres canvas récupérés (simulation)'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la récupération: ' . $e->getMessage());
        }
    }

    // Méthodes privées pour sauvegarder les paramètres canvas

    private function saveDimensionsSettings()
    {
        $updated = 0;

        // Format du document
        if (isset($_POST['canvas_format'])) {
            $format = sanitize_text_field($_POST['canvas_format']);
            // Validation des formats supportés
            $valid_formats = ['A4', 'A3', 'A5', 'Letter', 'Legal', 'Tabloid'];
            if (in_array($format, $valid_formats)) {
                update_option('pdf_builder_canvas_format', $format);
                $updated++;
            }
        }

        // Orientation (actuellement forcée en portrait)
        // TODO: Implémenter l'orientation paysage dans v2.0
        update_option('pdf_builder_canvas_orientation', 'portrait');
        $updated++;

        // Résolution DPI
        if (isset($_POST['canvas_dpi'])) {
            $dpi = intval($_POST['canvas_dpi']);
            // Validation des DPI supportés
            $valid_dpi = [72, 96, 150, 300];
            if (in_array($dpi, $valid_dpi)) {
                update_option('pdf_builder_canvas_dpi', $dpi);

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
        update_option('pdf_builder_canvas_height', $heightPx);
    }

    private function saveZoomSettings()
    {
        $updated = 0;

        // Zoom minimum
        if (isset($_POST['canvas_zoom_min'])) {
            $min = intval($_POST['canvas_zoom_min']);
            if ($min >= 1 && $min <= 100) {
                update_option('pdf_builder_canvas_zoom_min', $min);
                $updated++;
            }
        }

        // Zoom maximum
        if (isset($_POST['canvas_zoom_max'])) {
            $max = intval($_POST['canvas_zoom_max']);
            if ($max >= 100 && $max <= 1000) {
                update_option('pdf_builder_canvas_zoom_max', $max);
                $updated++;
            }
        }

        // Zoom par défaut
        if (isset($_POST['canvas_zoom_default'])) {
            $default = intval($_POST['canvas_zoom_default']);
            if ($default >= 10 && $default <= 500) {
                update_option('pdf_builder_canvas_zoom_default', $default);
                $updated++;
            }
        }

        // Pas de zoom
        if (isset($_POST['canvas_zoom_step'])) {
            $step = intval($_POST['canvas_zoom_step']);
            if ($step >= 5 && $step <= 50) {
                update_option('pdf_builder_canvas_zoom_step', $step);
                $updated++;
            }
        }

        return $updated > 0;
    }

    private function saveApparenceSettings()
    {
        $updated = 0;

        // Couleur de fond du canvas
        if (isset($_POST['canvas_bg_color'])) {
            update_option('pdf_builder_canvas_bg_color', sanitize_hex_color($_POST['canvas_bg_color']));
            $updated++;
        }

        // Couleur des bordures
        if (isset($_POST['canvas_border_color'])) {
            update_option('pdf_builder_canvas_border_color', sanitize_hex_color($_POST['canvas_border_color']));
            $updated++;
        }

        // Épaisseur des bordures
        if (isset($_POST['canvas_border_width'])) {
            $width = intval($_POST['canvas_border_width']);
            if ($width >= 0 && $width <= 10) {
                update_option('pdf_builder_canvas_border_width', $width);
                $updated++;
            }
        }

        // Ombre activée
        if (isset($_POST['canvas_shadow_enabled'])) {
            update_option('pdf_builder_canvas_shadow_enabled', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_shadow_enabled', '0');
            $updated++;
        }

        // Arrière-plan de l'éditeur
        if (isset($_POST['canvas_container_bg_color'])) {
            update_option('pdf_builder_canvas_container_bg_color', sanitize_hex_color($_POST['canvas_container_bg_color']));
            $updated++;
        }

        return $updated > 0;
    }

    private function saveGrilleSettings()
    {
        $updated = 0;

        // Guides activés
        if (isset($_POST['canvas_guides_enabled'])) {
            update_option('pdf_builder_canvas_guides_enabled', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_guides_enabled', '0');
            $updated++;
        }

        // Grille activée
        if (isset($_POST['canvas_grid_enabled'])) {
            update_option('pdf_builder_canvas_grid_enabled', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_grid_enabled', '0');
            $updated++;
        }

        // Taille de la grille
        if (isset($_POST['canvas_grid_size'])) {
            $size = intval($_POST['canvas_grid_size']);
            if ($size >= 5 && $size <= 100) {
                update_option('pdf_builder_canvas_grid_size', $size);
                $updated++;
            }
        }

        // Accrochage à la grille
        if (isset($_POST['canvas_snap_to_grid'])) {
            update_option('pdf_builder_canvas_snap_to_grid', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_snap_to_grid', '0');
            $updated++;
        }

        return $updated > 0;
    }

    private function saveInteractionsSettings()
    {
        $updated = 0;

        // Glisser-déposer activé
        if (isset($_POST['canvas_drag_enabled'])) {
            update_option('pdf_builder_canvas_drag_enabled', sanitize_text_field($_POST['canvas_drag_enabled']));
            $updated++;
        }

        // Redimensionnement activé
        if (isset($_POST['canvas_resize_enabled'])) {
            update_option('pdf_builder_canvas_resize_enabled', sanitize_text_field($_POST['canvas_resize_enabled']));
            $updated++;
        }

        // Rotation activée
        if (isset($_POST['canvas_rotate_enabled'])) {
            update_option('pdf_builder_canvas_rotate_enabled', sanitize_text_field($_POST['canvas_rotate_enabled']));
            $updated++;
        }

        // Sélection multiple
        if (isset($_POST['canvas_multi_select'])) {
            update_option('pdf_builder_canvas_multi_select', sanitize_text_field($_POST['canvas_multi_select']));
            $updated++;
        }

        // Mode de sélection
        if (isset($_POST['canvas_selection_mode'])) {
            update_option('pdf_builder_canvas_selection_mode', sanitize_text_field($_POST['canvas_selection_mode']));
            $updated++;
        }

        // Raccourcis clavier
        if (isset($_POST['canvas_keyboard_shortcuts'])) {
            update_option('pdf_builder_canvas_keyboard_shortcuts', sanitize_text_field($_POST['canvas_keyboard_shortcuts']));
            $updated++;
        }

        return $updated > 0;
    }

    private function saveExportSettings()
    {
        $updated = 0;

        // Format d'export
        if (isset($_POST['canvas_export_format'])) {
            $format = sanitize_text_field($_POST['canvas_export_format']);
            if (in_array($format, ['png', 'jpg', 'svg'])) {
                update_option('pdf_builder_canvas_export_format', $format);
                $updated++;
            }
        }

        // Qualité d'export
        if (isset($_POST['canvas_export_quality'])) {
            $quality = intval($_POST['canvas_export_quality']);
            if ($quality >= 1 && $quality <= 100) {
                update_option('pdf_builder_canvas_export_quality', $quality);
                $updated++;
            }
        }

        // Fond transparent
        if (isset($_POST['canvas_export_transparent'])) {
            update_option('pdf_builder_canvas_export_transparent', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_export_transparent', '0');
            $updated++;
        }

        return $updated > 0;
    }

    private function savePerformanceSettings()
    {
        $updated = 0;

        // Cible FPS
        if (isset($_POST['canvas_fps_target'])) {
            $fps = intval($_POST['canvas_fps_target']);
            if (in_array($fps, [30, 60, 120])) {
                update_option('pdf_builder_canvas_fps_target', $fps);
                $updated++;
            }
        }

        // Limite mémoire JavaScript
        if (isset($_POST['canvas_memory_limit_js'])) {
            $memory_js = sanitize_text_field($_POST['canvas_memory_limit_js']);
            if (in_array($memory_js, ['128', '256', '512', '1024'])) {
                update_option('pdf_builder_canvas_memory_limit_js', $memory_js);
                $updated++;
            }
        }

        // Chargement paresseux éditeur
        if (isset($_POST['canvas_lazy_loading_editor'])) {
            update_option('pdf_builder_canvas_lazy_loading_editor', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_lazy_loading_editor', '0');
            $updated++;
        }

        // Préchargement ressources critiques
        if (isset($_POST['canvas_preload_critical'])) {
            update_option('pdf_builder_canvas_preload_critical', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_preload_critical', '0');
            $updated++;
        }

        // Limite mémoire PHP
        if (isset($_POST['canvas_memory_limit_php'])) {
            $memory_php = sanitize_text_field($_POST['canvas_memory_limit_php']);
            if (in_array($memory_php, ['128', '256', '512', '1024'])) {
                update_option('pdf_builder_canvas_memory_limit_php', $memory_php);
                $updated++;
            }
        }

        // Timeout réponses AJAX
        if (isset($_POST['canvas_response_timeout'])) {
            $timeout = intval($_POST['canvas_response_timeout']);
            if (in_array($timeout, [10, 30, 60, 120])) {
                update_option('pdf_builder_canvas_response_timeout', $timeout);
                $updated++;
            }
        }

        // Chargement paresseux plugin
        if (isset($_POST['canvas_lazy_loading_plugin'])) {
            update_option('pdf_builder_canvas_lazy_loading_plugin', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_lazy_loading_plugin', '0');
            $updated++;
        }

        return $updated > 0;
    }

    private function saveAutosaveSettings()
    {
        $updated = 0;

        // Sauvegarde automatique activée
        if (isset($_POST['canvas_autosave_enabled'])) {
            update_option('pdf_builder_canvas_autosave_enabled', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_autosave_enabled', '0');
            $updated++;
        }

        // Intervalle de sauvegarde
        if (isset($_POST['canvas_autosave_interval'])) {
            $interval = intval($_POST['canvas_autosave_interval']);
            if ($interval >= 1 && $interval <= 60) {
                update_option('pdf_builder_canvas_autosave_interval', $interval);
                $updated++;
            }
        }

        // Historique activé
        if (isset($_POST['canvas_history_enabled'])) {
            update_option('pdf_builder_canvas_history_enabled', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_history_enabled', '0');
            $updated++;
        }

        return $updated > 0;
    }

    private function saveDebugSettings()
    {
        $updated = 0;

        // Debug activé
        if (isset($_POST['canvas_debug_enabled'])) {
            update_option('pdf_builder_canvas_debug_enabled', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_debug_enabled', '0');
            $updated++;
        }

        // Monitoring performance
        if (isset($_POST['canvas_performance_monitoring'])) {
            update_option('pdf_builder_canvas_performance_monitoring', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_performance_monitoring', '0');
            $updated++;
        }

        // Rapport d'erreurs
        if (isset($_POST['canvas_error_reporting'])) {
            update_option('pdf_builder_canvas_error_reporting', '1');
            $updated++;
        } else {
            update_option('pdf_builder_canvas_error_reporting', '0');
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
}