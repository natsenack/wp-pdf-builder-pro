<?php

namespace PDF_Builder_Pro\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Template Manager
 * Gestion centralisée des templates
 * Version: 1.0.4 - Fixed data handling for camelCase and JSON, cache bypass V3
 */

class PdfBuilderTemplateManager
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance = null)
    {
        $this->main = $main_instance;
        // Les hooks AJAX sont enregistrés par PDF_Builder_Admin, pas ici
        // pour éviter que la traduction soit appelée trop tôt
    }

    /**
     * Initialiser les hooks
     * NOTE: Cette méthode n'est plus appelée depuis le constructeur
     * Les hooks AJAX sont enregistrés directement par PDF_Builder_Admin
     */
    private function initHooks()
    {
        // AJAX handlers pour les templates
        add_action('wp_ajax_pdf_builder_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']); // Alias pour compatibilité
        // NOTE: pdf_builder_load_template est enregistré dans PDF_Builder_Admin.php
        // add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajax_load_template']);
        // NOTE: pdf_builder_flush_rest_cache est enregistré dans PDF_Builder_Admin.php
        // add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);
    }

    /**
     * Page de gestion des templates
     */
    public function templatesPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Récupérer tous les templates
        $templates = $wpdb->get_results("SELECT * FROM $table_templates ORDER BY updated_at DESC", ARRAY_A);

        include plugin_dir_path(dirname(__FILE__)) . '../../templates/admin/templates-page.php';
    }

    /**
     * AJAX - Sauvegarder un template
     */
    public function ajaxSaveTemplateV3()
    {
        try {
            // Log pour debug
            error_log('PDF Builder: ajaxSaveTemplateV3 called - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
            // Write to uploads directory for guaranteed access
            $upload_dir = wp_upload_dir();
            $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
            file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVE START - REQUEST: ' . print_r($_REQUEST, true) . "\n", FILE_APPEND);
            file_put_contents($log_file, date('Y-m-d H:i:s') . ' POST data keys: ' . implode(', ', array_keys($_POST)) . "\n", FILE_APPEND);

            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                error_log('PDF Builder: Insufficient permissions');
                \wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérification du nonce
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                $nonce_valid = \wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce') ||
                              \wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions') ||
                              \wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
            }

            if (!$nonce_valid) {
                error_log('PDF Builder: Invalid nonce - received: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'none'));
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            error_log('PDF Builder: Nonce valid, processing data');

            // Récupération et nettoyage des données
            // Support pour les données JSON (nouvelle méthode) et FormData (ancienne)
            error_log('PDF Builder: Processing request data');
            $json_data = null;
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $json_input = file_get_contents('php://input');
                error_log('PDF Builder: JSON input length: ' . strlen($json_input));
                $json_data = json_decode($json_input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \wp_send_json_error('Données JSON invalides dans le corps de la requête: ' . json_last_error_msg());
                    return;
                }
            }

            // Support pour les clés camelCase (frontend) et snake_case (ancien)
            $template_data = '';
            $template_name = '';
            $template_id = 0;

            if ($json_data) {
                // Données JSON
                $template_data = isset($json_data['templateData']) ? \wp_json_encode($json_data['templateData']) : 
                                (isset($json_data['template_data']) ? $json_data['template_data'] : '');
                $template_name = isset($json_data['templateName']) ? \sanitize_text_field($json_data['templateName']) : 
                                (isset($json_data['template_name']) ? \sanitize_text_field($json_data['template_name']) : '');
                $template_id = isset($json_data['templateId']) ? \intval($json_data['templateId']) : 
                              (isset($json_data['template_id']) ? \intval($json_data['template_id']) : 0);
            } else {
                // Données FormData
                $template_data = isset($_POST['template_data']) ? \trim(\wp_unslash($_POST['template_data'])) : 
                                (isset($_POST['templateData']) ? \trim(\wp_unslash($_POST['templateData'])) : '');
                $template_name = isset($_POST['template_name']) ? \sanitize_text_field($_POST['template_name']) : 
                                (isset($_POST['templateName']) ? \sanitize_text_field($_POST['templateName']) : '');
                $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 
                              (isset($_POST['templateId']) ? \intval($_POST['templateId']) : 0);
            }

            error_log('PDF Builder: template_id: ' . $template_id . ', template_name: ' . $template_name . ', template_data length: ' . strlen($template_data));

            // Si template_data n'est pas fourni, construire à partir d'elements et canvas séparés
            if (empty($template_data)) {
                error_log('PDF Builder: Constructing template data from elements and canvas');
                $elements = '';
                $canvas = '';

                if ($json_data) {
                    // Données JSON
                    $elements = isset($json_data['elements']) ? \wp_json_encode($json_data['elements']) : 
                              (isset($json_data['elementsData']) ? \wp_json_encode($json_data['elementsData']) : '[]');
                    $canvas = isset($json_data['canvas']) ? \wp_json_encode($json_data['canvas']) : 
                             (isset($json_data['canvasData']) ? \wp_json_encode($json_data['canvasData']) : '{}');
                } else {
                    // Données FormData
                    $elements = isset($_POST['elements']) ? \wp_unslash($_POST['elements']) : 
                               (isset($_POST['elementsData']) ? \wp_unslash($_POST['elementsData']) : '[]');
                    $canvas = isset($_POST['canvas']) ? \wp_unslash($_POST['canvas']) : 
                             (isset($_POST['canvasData']) ? \wp_unslash($_POST['canvasData']) : '{}');
                }

                error_log('PDF Builder: elements length: ' . strlen($elements) . ', canvas: ' . $canvas);

                // Validation du JSON pour elements
                $elements_data = \json_decode($elements, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    error_log('PDF Builder: Elements JSON decode error: ' . $json_error . ' - Raw elements: ' . substr($elements, 0, 500));
                    \wp_send_json_error('Données elements JSON invalides: ' . $json_error);
                    return;
                }

                // Validation du JSON pour canvas
                $canvas_data = \json_decode($canvas, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    error_log('PDF Builder: Canvas JSON decode error: ' . $json_error . ' - Raw canvas: ' . $canvas);
                    \wp_send_json_error('Données canvas JSON invalides: ' . $json_error);
                    return;
                }

                error_log('PDF Builder: Elements count: ' . count($elements_data) . ', Canvas data: ' . json_encode($canvas_data));

                // Log détaillé des éléments pour vérifier les propriétés
                error_log('PDF Builder: SAVE - Elements data saved, count: ' . count($elements_data));
                $upload_dir = wp_upload_dir();
                $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                file_put_contents($log_file, date('Y-m-d H:i:s') . ' ELEMENTS COUNT: ' . count($elements_data) . "\n", FILE_APPEND);

                // Log order_number elements specifically BEFORE saving
                foreach ($elements_data as $el) {
                    if (isset($el['type']) && $el['type'] === 'order_number') {
                        error_log('PDF Builder: SAVE - Order number element BEFORE SAVE: ' . json_encode([
                            'id' => $el['id'] ?? 'missing',
                            'contentAlign' => $el['contentAlign'] ?? 'missing',
                            'labelPosition' => $el['labelPosition'] ?? 'missing',
                            'type' => $el['type'] ?? 'missing',
                            'all_keys' => array_keys($el)
                        ]));
                        file_put_contents($log_file, date('Y-m-d H:i:s') . ' ORDER ELEMENT BEFORE SAVE: ' . json_encode($el) . "\n", FILE_APPEND);
                    }
                }

                // 🏷️ Enrichir les éléments company_logo avec src si absent (même logique que GET)
                foreach ($elements_data as &$el) {
                    if (isset($el['type']) && $el['type'] === 'company_logo') {
                        // Si src est vide ou absent, chercher le logo WordPress
                        if (empty($el['src']) && empty($el['logoUrl'])) {
                            $custom_logo_id = \get_theme_mod('custom_logo');
                            if ($custom_logo_id) {
                                $logo_url = \wp_get_attachment_image_url($custom_logo_id, 'full');
                                if ($logo_url) {
                                    $el['src'] = $logo_url;
                                }
                            }
                        }
                    }
                }
                unset($el);

                // ✅ CORRECTION: Construction avec canvasWidth/canvasHeight standardisés
                $template_structure = [
                    'elements' => $elements_data,
                    'canvasWidth' => isset($canvas_data['width']) ? $canvas_data['width'] : 
                                   (isset($canvas_data['canvasWidth']) ? $canvas_data['canvasWidth'] : 794),
                    'canvasHeight' => isset($canvas_data['height']) ? $canvas_data['height'] : 
                                    (isset($canvas_data['canvasHeight']) ? $canvas_data['canvasHeight'] : 1123),
                    'version' => '1.0'
                ];

                $template_data = \wp_json_encode($template_structure);
                if ($template_data === false) {
                    error_log('PDF Builder: Template structure JSON encode failed');
                    \wp_send_json_error('Erreur lors de l\'encodage des données template');
                    return;
                }
                error_log('PDF Builder: Template data constructed, length: ' . strlen($template_data));
                error_log('PDF Builder: Final template structure: ' . json_encode($template_structure));
            }            // Validation du JSON
            $decoded_test = \json_decode($template_data, true);
            if (\json_last_error() !== JSON_ERROR_NONE) {
                $json_error = \json_last_error_msg();
                error_log('PDF Builder: Template data JSON decode error: ' . $json_error . ' - Raw data: ' . substr($template_data, 0, 500));
                \wp_send_json_error('Données JSON invalides: ' . $json_error);
                return;
            }

            // Validation de la structure du template
            error_log('PDF Builder: Validating template structure');
            $validation_errors = $this->validateTemplateStructure($decoded_test);
            if (!empty($validation_errors)) {
                error_log('PDF Builder: Validation errors: ' . implode(', ', $validation_errors));
                \wp_send_json_error('Structure invalide: ' . \implode(', ', $validation_errors));
                return;
            }

            // Validation des données obligatoires
            if (empty($template_data) || empty($template_name)) {
                error_log('PDF Builder: Missing required data - template_data empty: ' . empty($template_data) . ', template_name empty: ' . empty($template_name));
                \wp_send_json_error('Données template ou nom manquant');
                return;
            }

            // Sauvegarde en utilisant les posts WordPress ou la table personnalisée
            try {
                error_log('PDF Builder: Starting database save operation');
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                // Créer la table si elle n'existe pas
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                    error_log('PDF Builder: Creating templates table');
                    $charset_collate = $wpdb->get_charset_collate();
                    $sql = "CREATE TABLE $table_templates (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        name varchar(255) NOT NULL,
                        template_data longtext NOT NULL,
                        created_at datetime DEFAULT CURRENT_TIMESTAMP,
                        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        KEY name (name)
                    ) $charset_collate;";
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    $result = dbDelta($sql);
                    error_log('PDF Builder: Table creation result: ' . print_r($result, true));
                }

                // Vérifier d'abord si le template existe dans la table personnalisée
                $existing_template = null;
                if ($template_id > 0) {
                    $existing_template = $wpdb->get_row(
                        $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                        ARRAY_A
                    );
                    error_log('PDF Builder: Existing template in custom table: ' . ($existing_template ? 'YES' : 'NO'));
                }

                if ($existing_template) {
                    // Mise à jour dans la table personnalisée
                    $result = $wpdb->update(
                        $table_templates,
                        array(
                            'name' => $template_name,
                            'template_data' => $template_data,
                            'updated_at' => current_time('mysql')
                        ),
                        array('id' => $template_id),
                        array('%s', '%s', '%s'),
                        array('%d')
                    );

                    if ($result === false) {
                        throw new \Exception('Erreur de mise à jour dans la table personnalisée: ' . $wpdb->last_error);
                    }
                    
                    // Log what was actually saved
                    $upload_dir = wp_upload_dir();
                    $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                    file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED TO CUSTOM TABLE - ID: ' . $template_id . ', DATA LENGTH: ' . strlen($template_data) . "\n", FILE_APPEND);
                    file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED DATA: ' . substr($template_data, 0, 500) . "\n", FILE_APPEND);
                } else {
                    // Gérer comme post WordPress (nouveau template ou migration)
                    if ($template_id > 0) {
                        // Vérifier que le post existe et est du bon type
                        $existing_post = \get_post($template_id);
                        if (!$existing_post || $existing_post->post_type !== 'pdf_template') {
                            throw new \Exception('Template non trouvé ou type invalide');
                        }

                        // Mise à jour d'un template existant
                        $post_data = array(
                            'ID' => $template_id,
                            'post_title' => $template_name,
                            'post_modified' => \current_time('mysql')
                        );

                        $result = \wp_update_post($post_data, true);
                        if (\is_wp_error($result)) {
                            throw new \Exception('Erreur de mise à jour du post: ' . $result->get_error_message());
                        }
                    } else {
                        // Création d'un nouveau template
                        $post_data = array(
                            'post_title' => $template_name,
                            'post_type' => 'pdf_template',
                            'post_status' => 'publish',
                            'post_date' => \current_time('mysql'),
                            'post_modified' => \current_time('mysql')
                        );

                        $template_id = \wp_insert_post($post_data, true);
                        if (\is_wp_error($template_id)) {
                            throw new \Exception('Erreur de création du post: ' . $template_id->get_error_message());
                        }
                    }

                    // Sauvegarder les données du template dans les métadonnées
                    \update_post_meta($template_id, '_pdf_template_data', $template_data);
                    
                    // Log what was actually saved
                    $upload_dir = wp_upload_dir();
                    $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                    file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED TO POST META - ID: ' . $template_id . ', DATA LENGTH: ' . strlen($template_data) . "\n", FILE_APPEND);
                    file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED DATA: ' . substr($template_data, 0, 500) . "\n", FILE_APPEND);
                }
            } catch (\Exception $e) {
                \wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
                return;
            }

            // Vérification post-sauvegarde
            if ($existing_template) {
                // Vérification pour la table personnalisée
                $saved_template = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );

                if (!$saved_template) {
                    \wp_send_json_error('Erreur: Template introuvable après sauvegarde dans la table personnalisée');
                    return;
                }

                $saved_data = \json_decode($saved_template['template_data'], true);
                $element_count = isset($saved_data['elements']) ? \count($saved_data['elements']) : 0;
            } else {
                // Vérification pour les posts WordPress
                $saved_post = \get_post($template_id);
                $saved_template_data = \get_post_meta($template_id, '_pdf_template_data', true);

                if (!$saved_post || empty($saved_template_data)) {
                    \wp_send_json_error('Erreur: Template introuvable après sauvegarde');
                    return;
                }

                $saved_data = \json_decode($saved_template_data, true);
                $element_count = isset($saved_data['elements']) ? \count($saved_data['elements']) : 0;
            }

            // Réponse de succès - INCLURE LE NOM DU TEMPLATE
            error_log('PDF Builder: Template saved successfully, ID: ' . $template_id . ', Name: ' . $template_name);
            \wp_send_json_success(
                array(
                'message' => 'Template sauvegardé avec succès',
                'template_id' => $template_id,
                'template_name' => $template_name,
                'name' => $template_name,
                'element_count' => $element_count
                )
            );
        } catch (\Throwable $e) {
            error_log('PDF Builder: Critical error in ajaxSaveTemplateV3: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            error_log('PDF Builder: Stack trace: ' . $e->getTraceAsString());
            \wp_send_json_error('Erreur critique lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Auto-sauvegarde d'un template (version simplifiée)
     */
    public function ajax_auto_save_template()
    {
        // LOG AU DÉBUT POUR VÉRIFIER QUE LA FONCTION EST APPELEE
        $log_message = '🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] FUNCTION CALLED - ajax_auto_save_template() - REQUEST: ' . print_r($_REQUEST, true);
        error_log($log_message);
        // Écrire aussi dans un fichier temporaire pour être sûr
        file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' ' . $log_message . "\n", FILE_APPEND);
        try {
            error_log('PDF Builder: Auto-save - checking permissions and nonce');
            
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                error_log('PDF Builder: Auto-save - permissions failed');
                \wp_send_json_error('Permissions insuffisantes');
            }
            
            error_log('PDF Builder: Auto-save - permissions OK, processing data');

            // Vérifier le nonce
            if (!isset($_REQUEST['nonce']) || !\wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_nonce')) {
                error_log('PDF Builder: Auto-save - nonce verification failed');
                \wp_send_json_error('Sécurité: Nonce invalide');
            }

            error_log('PDF Builder: Auto-save - nonce OK, extracting data');

            // Récupération des données
            $template_id = isset($_REQUEST['template_id']) ? \intval($_REQUEST['template_id']) : 0;
            $elements_raw = isset($_REQUEST['elements']) ? \wp_unslash($_REQUEST['elements']) : '[]';

            if (empty($template_id)) {
                error_log('PDF Builder: Auto-save - invalid template_id: ' . $template_id);
                \wp_send_json_error('ID template invalide');
            }

            error_log('PDF Builder: Auto-save - template_id OK, decoding JSON');

            $elements = \json_decode($elements_raw, true);
            if ($elements === null && \json_last_error() !== JSON_ERROR_NONE) {
                $json_error = \json_last_error_msg();
                error_log('PDF Builder: Auto-save - JSON decode failed: ' . $json_error);
                \wp_send_json_error('Données des éléments corrompues - Erreur JSON: ' . $json_error);
            }
            
            error_log('PDF Builder: Auto-save - JSON decode success, elements count: ' . count($elements));
            error_log('PDF Builder: Auto-save - about to log elements');

            // Charger le template existant pour récupérer le canvas
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                ARRAY_A
            );

            if (!$template_row) {
                error_log('PDF Builder: Auto-save - template not found: ' . $template_id);
                \wp_send_json_error('Template non trouvé pour auto-save');
            }

            // Récupérer les données existantes
            $existing_data = \json_decode($template_row['template_data'], true);
            if ($existing_data === null) {
                $existing_data = ['elements' => [], 'canvas' => []];
            }

            error_log('PDF Builder: Auto-save - processing ' . count($elements) . ' elements');

            // Enrichir les éléments company_logo avec src si absent
            foreach ($elements as &$el) {
                if (isset($el['type']) && $el['type'] === 'company_logo') {
                    // Si src est vide ou absent, chercher le logo WordPress
                    if (empty($el['src']) && empty($el['logoUrl'])) {
                        $custom_logo_id = \get_theme_mod('custom_logo');
                        if ($custom_logo_id) {
                            $logo_url = \wp_get_attachment_image_url($custom_logo_id, 'full');
                            if ($logo_url) {
                                $el['src'] = $logo_url;
                            }
                        }
                    }
                }
            }
            unset($el);

            error_log('� 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO PREPARE TEMPLATE DATA...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO PREPARE TEMPLATE DATA...' . "\n", FILE_APPEND);

            error_log('�🔍 [AUTO-SAVE] Après enrichissement - Element count: ' . count($elements));
            if (!empty($elements)) {
                error_log('🔍 [AUTO-SAVE] Element[0] AFTER enrichment: ' . json_encode($elements[0]));
                // Check for company_logo specifically
                foreach ($elements as $idx => $el) {
                    if (isset($el['type']) && $el['type'] === 'company_logo') {
                        error_log('🔍 [AUTO-SAVE] company_logo[' . $idx . '] after enrichment: src=' . (isset($el['src']) ? $el['src'] : 'MISSING'));
                    }
                }
            }

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO CREATE TEMPLATE_DATA ARRAY...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO CREATE TEMPLATE_DATA ARRAY...' . "\n", FILE_APPEND);

            // Préparer les nouvelles données (conserver le canvas, mettre à jour les éléments)
            $template_data = [
                'elements' => $elements,
                'canvas' => $existing_data['canvas'] ?? [],
                'canvasWidth' => $existing_data['canvasWidth'] ?? 210,
                'canvasHeight' => $existing_data['canvasHeight'] ?? 297,
                'version' => '1.0'
            ];

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] TEMPLATE_DATA ARRAY CREATED SUCCESSFULLY');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' � 🔥 🔥 🔥 🔥 [AUTO-SAVE] TEMPLATE_DATA ARRAY CREATED SUCCESSFULLY' . "\n", FILE_APPEND);

            error_log('�🔍 [AUTO-SAVE] Template data structure - keys: ' . implode(', ', array_keys($template_data)));
            error_log('🔍 [AUTO-SAVE] Elements in template_data: ' . count($template_data['elements']));

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO JSON ENCODE...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO JSON ENCODE...' . "\n", FILE_APPEND);

            // Encoder en JSON
            $json_data = \wp_json_encode($template_data);
            if ($json_data === false) {
                error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] JSON ENCODE FAILED');
                file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] JSON ENCODE FAILED' . "\n", FILE_APPEND);
                \wp_send_json_error('Erreur lors de l\'encodage des données JSON');
            }

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] JSON ENCODE SUCCESS - LENGTH: ' . strlen($json_data));
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] JSON ENCODE SUCCESS - LENGTH: ' . strlen($json_data) . "\n", FILE_APPEND);

            error_log('🔍 [AUTO-SAVE] JSON encoded length: ' . strlen($json_data));
            error_log('🔍 [AUTO-SAVE] JSON saved to DB (first 500 chars): ' . substr($json_data, 0, 500));

            error_log('� 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO UPDATE DATABASE...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO UPDATE DATABASE...' . "\n", FILE_APPEND);

            // Mettre à jour la base de données
            $updated = $wpdb->update(
                $table_templates,
                [
                    'template_data' => $json_data,
                    'updated_at' => \current_time('mysql')
                ],
                ['id' => $template_id],
                ['%s', '%s'],
                ['%d']
            );

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] DATABASE UPDATE COMPLETED - RESULT: ' . ($updated === false ? 'FALSE' : $updated));
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] DATABASE UPDATE COMPLETED - RESULT: ' . ($updated === false ? 'FALSE' : $updated) . "\n", FILE_APPEND);

            if ($updated === false) {
                error_log('🔍 [AUTO-SAVE] Database update FAILED - Error: ' . $wpdb->last_error);
                file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔍 [AUTO-SAVE] Database update FAILED - Error: ' . $wpdb->last_error . "\n", FILE_APPEND);
                \wp_send_json_error('Erreur lors de la mise à jour du template');
            }

            error_log('� 🔥 🔥 🔥 🔥 [AUTO-SAVE] DATABASE UPDATE SUCCESSFUL - ABOUT TO VERIFY...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] DATABASE UPDATE SUCCESSFUL - ABOUT TO VERIFY...' . "\n", FILE_APPEND);

            error_log('�🔍 [AUTO-SAVE] Database update successful - rows affected: ' . $updated);
            
            // Vérifier que les données ont bien été sauvegardées en les relisant
            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO VERIFY DATA IN DB...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO VERIFY DATA IN DB...' . "\n", FILE_APPEND);

            $verify_row = $wpdb->get_row(
                $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id),
                ARRAY_A
            );

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] DB VERIFICATION COMPLETED - ROW FOUND: ' . ($verify_row ? 'YES' : 'NO'));
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] DB VERIFICATION COMPLETED - ROW FOUND: ' . ($verify_row ? 'YES' : 'NO') . "\n", FILE_APPEND);
            
            if ($verify_row) {
                $saved_data = json_decode($verify_row['template_data'], true);
                $saved_elements_count = isset($saved_data['elements']) ? count($saved_data['elements']) : 0;
                error_log('🔍 [AUTO-SAVE] VERIFICATION: ' . $saved_elements_count . ' elements found in DB after save');
                
                if ($saved_elements_count > 0) {
                    error_log('🔍 [AUTO-SAVE] VERIFICATION: First element type: ' . (isset($saved_data['elements'][0]['type']) ? $saved_data['elements'][0]['type'] : 'unknown'));
                }
            } else {
                error_log('🔍 [AUTO-SAVE] VERIFICATION FAILED: Could not read back from DB');
            }

            error_log('🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO SEND SUCCESS RESPONSE...');
            file_put_contents(ABSPATH . '/wp-content/debug_pdf_builder.log', date('Y-m-d H:i:s') . ' 🔥 🔥 🔥 🔥 🔥 [AUTO-SAVE] ABOUT TO SEND SUCCESS RESPONSE...' . "\n", FILE_APPEND);

            error_log('PDF Builder: Auto-save completed successfully - template_id: ' . $template_id . ', elements: ' . count($elements));

            \wp_send_json_success([
                'message' => 'Auto-save réussi',
                'template_id' => $template_id,
                'saved_at' => \current_time('mysql'),
                'element_count' => count($elements),
                'elements_saved' => $elements  // ← Return the saved elements so frontend can verify
            ]);

        } catch (\Throwable $e) {
            error_log('PDF Builder: Critical error in ajax_auto_save_template: ' . $e->getMessage());
            \wp_send_json_error('Erreur critique: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Charger un template
     */
    public function ajaxLoadTemplate()
    {
        // ✅ CRITICAL: Disable all caching for AJAX template loading
        // This ensures F5 and Ctrl+F5 load the same fresh data from DB
        // Without these headers, browsers or CDN can cache stale responses
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // LOG IMMEDIATELY to confirm function is called
        error_log('=== PDF BUILDER LOAD START === Template ID: ' . (isset($_REQUEST['template_id']) ? $_REQUEST['template_id'] : 'none'));
        
        try {
            // Log that function was called
            $upload_dir = wp_upload_dir();
            $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
            file_put_contents($log_file, date('Y-m-d H:i:s') . ' === LOAD FUNCTION CALLED === REQUEST: ' . print_r($_REQUEST, true) . "\n", FILE_APPEND);
            
            error_log('PDF Builder: ajaxLoadTemplate called - REQUEST: ' . print_r($_REQUEST, true));
            
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                error_log('PDF Builder: ajaxLoadTemplate - permissions failed');
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Récupération de l'ID (doit être numérique pour les templates personnalisés)
            $template_id = isset($_REQUEST['template_id']) ? \intval($_REQUEST['template_id']) : 0;
            error_log('PDF Builder: ajaxLoadTemplate - template_id: ' . $template_id);

            if (empty($template_id)) {
                error_log('PDF Builder: ajaxLoadTemplate - invalid template_id');
                \wp_send_json_error('ID template invalide');
            }

            // DIRECT DATABASE CHECK - BEFORE ANY PROCESSING
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                ARRAY_A
            );
            
            if ($template_row) {
                $raw_data = $template_row['template_data'];
                error_log('=== DIRECT DB CHECK === Template ID: ' . $template_id);
                error_log('=== DIRECT DB CHECK === Raw data contains contentAlign: ' . (strpos($raw_data, 'contentAlign') !== false ? 'YES' : 'NO'));
                error_log('=== DIRECT DB CHECK === Raw data contains labelPosition: ' . (strpos($raw_data, 'labelPosition') !== false ? 'YES' : 'NO'));
                
                // Extract order_number element from raw data
                if (strpos($raw_data, 'order_number') !== false) {
                    $data = json_decode($raw_data, true);
                    if ($data && isset($data['elements'])) {
                        foreach ($data['elements'] as $el) {
                            if (isset($el['type']) && $el['type'] === 'order_number') {
                                error_log('=== DIRECT DB CHECK === Order element in DB: ' . json_encode($el));
                                error_log('=== DIRECT DB CHECK === Has contentAlign: ' . (isset($el['contentAlign']) ? 'YES (' . $el['contentAlign'] . ')' : 'NO'));
                                error_log('=== DIRECT DB CHECK === Has labelPosition: ' . (isset($el['labelPosition']) ? 'YES (' . $el['labelPosition'] . ')' : 'NO'));
                                break;
                            }
                        }
                    }
                }
            }

            // Chercher d'abord dans la table personnalisée (custom table)
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                ARRAY_A
            );

            error_log('PDF Builder: ajaxLoadTemplate - template_row found: ' . ($template_row ? 'YES' : 'NO'));
            
            $template_data = null;
            $template_name = '';

            if ($template_row) {
                error_log('PDF Builder: ajaxLoadTemplate - USING CUSTOM TABLE DATA');
                // Trouver dans la table custom
                $template_data_raw = $template_row['template_data'];
                $template_name = $template_row['name'];
                
                error_log('PDF Builder: ajaxLoadTemplate - LOADING FROM CUSTOM TABLE');
                error_log('PDF Builder: ajaxLoadTemplate - template_data_raw length: ' . strlen($template_data_raw));
                error_log('PDF Builder: ajaxLoadTemplate - template_data_raw (first 500 chars): ' . substr($template_data_raw, 0, 500));
                
                // Log the raw data containing order_number properties
                if (strpos($template_data_raw, 'order_number') !== false) {
                    error_log('PDF Builder: ajaxLoadTemplate - RAW DATA CONTAINS ORDER NUMBER');
                    // Extract just the order_number element from raw JSON
                    $raw_data_log = $template_data_raw;
                    $upload_dir = wp_upload_dir();
                    $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
                    file_put_contents($log_file, date('Y-m-d H:i:s') . ' RAW TEMPLATE DATA: ' . $raw_data_log . "\n", FILE_APPEND);
                }
                
                // Write to uploads directory for guaranteed access
                $upload_dir = wp_upload_dir();
                $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
                file_put_contents($log_file, date('Y-m-d H:i:s') . ' LOAD FROM CUSTOM TABLE - ID: ' . $template_id . ', DATA LENGTH: ' . strlen($template_data_raw) . "\n", FILE_APPEND);
                file_put_contents($log_file, date('Y-m-d H:i:s') . ' LOADED DATA: ' . substr($template_data_raw, 0, 500) . "\n", FILE_APPEND);
                
                // ADD DEBUG LOG: Check if properties exist in raw data
                if (strpos($template_data_raw, 'contentAlign') !== false) {
                    error_log('PDF Builder: ajaxLoadTemplate - RAW DATA CONTAINS contentAlign');
                } else {
                    error_log('PDF Builder: ajaxLoadTemplate - RAW DATA DOES NOT CONTAIN contentAlign');
                }
                if (strpos($template_data_raw, 'labelPosition') !== false) {
                    error_log('PDF Builder: ajaxLoadTemplate - RAW DATA CONTAINS labelPosition');
                } else {
                    error_log('PDF Builder: ajaxLoadTemplate - RAW DATA DOES NOT CONTAIN labelPosition');
                }

                $template_data = \json_decode($template_data_raw, true);
                if ($template_data === null && \json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    error_log('PDF Builder: ajaxLoadTemplate - JSON decode failed: ' . $json_error);
                    \wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
                    return;
                }
                
                // LOG DECODED DATA - DÉTAILLÉ
                error_log('=== PDF BUILDER LOAD DECODED === Elements count: ' . (isset($template_data['elements']) ? count($template_data['elements']) : 'none'));
                if (isset($template_data['elements'])) {
                    foreach ($template_data['elements'] as $index => $el) {
                        if (isset($el['type']) && $el['type'] === 'order_number') {
                            error_log('=== PDF BUILDER LOAD DECODED ORDER ELEMENT ' . $index . ' === ' . json_encode($el));
                            error_log('=== PDF BUILDER LOAD DECODED ORDER ELEMENT ' . $index . ' KEYS === ' . json_encode(array_keys($el)));
                            error_log('=== PDF BUILDER LOAD DECODED ORDER ELEMENT ' . $index . ' PROPERTIES === ' . json_encode([
                                'contentAlign' => isset($el['contentAlign']) ? $el['contentAlign'] : 'NOT_SET',
                                'labelPosition' => isset($el['labelPosition']) ? $el['labelPosition'] : 'NOT_SET',
                                'hasContentAlign' => array_key_exists('contentAlign', $el),
                                'hasLabelPosition' => array_key_exists('labelPosition', $el)
                            ]));
                            
                            // Log the raw JSON data for this element to compare with saved data
                            $element_json = json_encode($el);
                            $upload_dir = wp_upload_dir();
                            $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
                            file_put_contents($log_file, date('Y-m-d H:i:s') . ' DECODED ORDER ELEMENT ' . $index . ': ' . $element_json . "\n", FILE_APPEND);
                        }
                    }
                }
                
                error_log('PDF Builder: ajaxLoadTemplate - JSON decode successful, elements count: ' . (isset($template_data['elements']) ? count($template_data['elements']) : 'N/A'));
            } else {
                error_log('PDF Builder: ajaxLoadTemplate - USING POST META DATA');
                // Fallback: chercher dans wp_posts
                $post = get_post($template_id);

                if (!$post || $post->post_type !== 'pdf_template') {
                    \wp_send_json_error('Template non trouvé');
                    return;
                }

                // Récupération des métadonnées
                $template_data_raw = get_post_meta($post->ID, '_pdf_template_data', true);

                if (empty($template_data_raw)) {
                    \wp_send_json_error('Données du template manquantes');
                    return;
                }

                $template_name = $post->post_title;
                $template_data = \json_decode($template_data_raw, true);
                if ($template_data === null && \json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    \wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
                    return;
                }
                
                // Log loading from post meta
                error_log('PDF Builder: ajaxLoadTemplate - LOADING FROM POST META');
                $upload_dir = wp_upload_dir();
                $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
                file_put_contents($log_file, date('Y-m-d H:i:s') . ' LOAD FROM POST META - ID: ' . $template_id . ', DATA LENGTH: ' . strlen($template_data_raw) . "\n", FILE_APPEND);
                file_put_contents($log_file, date('Y-m-d H:i:s') . ' LOADED DATA: ' . substr($template_data_raw, 0, 500) . "\n", FILE_APPEND);
            }

            // Validation de la structure
            $validation_errors = $this->validateTemplateStructure($template_data);
            if (!empty($validation_errors)) {
                // Ajouter les propriétés manquantes par défaut pour la compatibilité
                if (!isset($template_data['version'])) {
                    $template_data['version'] = '1.0.0';
                }
                if (!isset($template_data['canvasWidth'])) {
                    $template_data['canvasWidth'] = 794; // A4 width
                }
                if (!isset($template_data['canvasHeight'])) {
                    $template_data['canvasHeight'] = 1123; // A4 height
                }
                if (!isset($template_data['elements'])) {
                    $template_data['elements'] = [];
                }
            }

            // Analyse du contenu
            $element_count = isset($template_data['elements']) ? \count($template_data['elements']) : 0;

        // Analyser les types d'éléments
            $element_types = [];
            foreach ($template_data['elements'] as $element) {
                $type = $element['type'] ?? 'unknown';
                $element_types[$type] = ($element_types[$type] ?? 0) + 1;
            }

            error_log('PDF Builder: ajaxLoadTemplate - sending response with ' . $element_count . ' elements');
            error_log('PDF Builder: ajaxLoadTemplate - element types: ' . json_encode($element_types));
            
            // Vérifier si les éléments ont les bonnes propriétés
            if (!empty($template_data['elements'])) {
                $first_element = $template_data['elements'][0];
                error_log('PDF Builder: ajaxLoadTemplate - first element keys: ' . implode(', ', array_keys($first_element)));
                error_log('PDF Builder: ajaxLoadTemplate - first element type: ' . ($first_element['type'] ?? 'unknown'));
                if (isset($first_element['type']) && $first_element['type'] === 'order_number') {
                    error_log('PDF Builder: ajaxLoadTemplate - order_number properties: ' . json_encode([
                        'contentAlign' => $first_element['contentAlign'] ?? 'missing',
                        'labelPosition' => $first_element['labelPosition'] ?? 'missing'
                    ]));
                    $upload_dir = wp_upload_dir();
                    $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
                    file_put_contents($log_file, date('Y-m-d H:i:s') . ' LOAD - ORDER ELEMENT: ' . json_encode($first_element) . "\n", FILE_APPEND);
                }
            }

        // Réponse de succès
            // LOG JUST BEFORE RESPONSE
            error_log('=== PDF BUILDER LOAD END === Sending response with ' . $element_count . ' elements');
            $upload_dir = wp_upload_dir();
            $log_file = $upload_dir['basedir'] . '/debug_pdf_load.log';
            file_put_contents($log_file, date('Y-m-d H:i:s') . ' === SENDING RESPONSE === Elements: ' . $element_count . "\n", FILE_APPEND);
            
            // Add debug info to response
            $debug_info = array();
            if (isset($template_data['elements'])) {
                foreach ($template_data['elements'] as $el) {
                    if (isset($el['type']) && $el['type'] === 'order_number') {
                        $debug_info['order_element'] = $el;
                        $debug_info['order_element_json'] = json_encode($el);
                        $debug_info['has_contentAlign'] = isset($el['contentAlign']);
                        $debug_info['has_labelPosition'] = isset($el['labelPosition']);
                        $debug_info['contentAlign_value'] = $el['contentAlign'] ?? 'MISSING';
                        $debug_info['labelPosition_value'] = $el['labelPosition'] ?? 'MISSING';
                        break;
                    }
                }
            }
            
            // LOG FINAL RESPONSE DATA - JUST BEFORE SENDING
            error_log('=== PDF BUILDER LOAD FINAL RESPONSE === About to send template_data');
            error_log('=== PDF BUILDER LOAD FINAL RESPONSE === Template name: ' . $template_name);
            error_log('=== PDF BUILDER LOAD FINAL RESPONSE === template_data elements count: ' . (isset($template_data['elements']) ? count($template_data['elements']) : 'none'));
            if (isset($template_data['elements'])) {
                foreach ($template_data['elements'] as $index => $el) {
                    if (isset($el['type']) && $el['type'] === 'order_number') {
                        error_log('=== PDF BUILDER LOAD FINAL RESPONSE === Order element ' . $index . ': ' . json_encode($el));
                        error_log('=== PDF BUILDER LOAD FINAL RESPONSE === Order element ' . $index . ' contentAlign: ' . ($el['contentAlign'] ?? 'MISSING'));
                        error_log('=== PDF BUILDER LOAD FINAL RESPONSE === Order element ' . $index . ' labelPosition: ' . ($el['labelPosition'] ?? 'MISSING'));
                        break;
                    }
                }
            }
            error_log('=== PDF BUILDER LOAD FINAL RESPONSE === Full response data: ' . json_encode([
                'template' => $template_data,
                'name' => $template_name,
                'element_count' => $element_count,
                'element_types' => $element_types,
                'debug' => $debug_info
            ]));
            
            $response_data = array(
                'template' => $template_data,
                'name' => $template_name,
                'template_name' => $template_name,
                'element_count' => $element_count,
                'element_types' => $element_types,
                'debug' => $debug_info
            );
            
            error_log('=== PDF BUILDER ABOUT TO SEND RESPONSE ===');
            error_log('Response data array: ' . json_encode($response_data));
            error_log('Response data keys: ' . json_encode(array_keys($response_data)));
            error_log('Template name in response: ' . ($response_data['name'] ?? 'MISSING'));
            error_log('=== END ABOUT TO SEND RESPONSE ===');
            
            \wp_send_json_success($response_data);
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors du chargement du template: ' . $e->getMessage());
        }
    }



    /**
     * AJAX - Vider le cache REST
     */
    public function ajaxFlushRestCache()
    {
        if (!\current_user_can('manage_options')) {
            \wp_send_json_error('Permissions insuffisantes');
        }

        // Vider le cache des transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        \wp_send_json_success('Cache REST vidé avec succès');
    }

    /**
     * Charger un template de manière robuste
     */
    public function loadTemplateRobust($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            return false;
        }

        $template_data_raw = $template['template_data'];

        // Vérifier si les données contiennent des backslashes (échappement PHP)
        if (strpos($template_data_raw, '\\') !== false) {
            $template_data_raw = stripslashes($template_data_raw);
        }

        $template_data = json_decode($template_data_raw, true);
        if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $template_data;
    }

    /**
     * Valider la structure complète d'un template
     * Retourne un tableau d'erreurs (vide si valide)
     *
     * @param  array $template_data Données du template décodées
     * @return array Tableau d'erreurs de validation
     */
    private function validateTemplateStructure($template_data)
    {
        $errors = [];

        // ===== Vérification 1 : Type et structure de base =====
        if (!is_array($template_data)) {
            $errors[] = 'Les données doivent être un objet JSON (array PHP)';
            return $errors;
        }

        // ===== Vérification 2 : Propriétés obligatoires =====
        $required_keys = ['elements', 'canvasWidth', 'canvasHeight', 'version'];
        foreach ($required_keys as $key) {
            if (!isset($template_data[$key])) {
                $errors[] = "Propriété obligatoire manquante: '$key'";
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        // ===== Vérification 3 : Types des propriétés principales =====
        if (!is_array($template_data['elements'])) {
            $errors[] = "'elements' doit être un tableau d'objets";
        }

        if (!is_numeric($template_data['canvasWidth'])) {
            $errors[] = "'canvasWidth' doit être un nombre (reçu: " . gettype($template_data['canvasWidth']) . ')';
        }

        if (!is_numeric($template_data['canvasHeight'])) {
            $errors[] = "'canvasHeight' doit être un nombre (reçu: " . gettype($template_data['canvasHeight']) . ')';
        }

        if (!is_string($template_data['version'])) {
            $errors[] = "'version' doit être une chaîne de caractères";
        }

        if (!empty($errors)) {
            return $errors;
        }

        // ===== Vérification 4 : Valeurs numériques raisonnables =====
        $width = (float) $template_data['canvasWidth'];
        $height = (float) $template_data['canvasHeight'];

        if ($width < 50 || $width > 2000) {
            $errors[] = "canvasWidth doit être entre 50 et 2000 (reçu: $width)";
        }

        if ($height < 50 || $height > 2000) {
            $errors[] = "canvasHeight doit être entre 50 et 2000 (reçu: $height)";
        }

        // ===== Vérification 5 : Nombre d'éléments raisonnable =====
        $element_count = count($template_data['elements']);
        if ($element_count > 1000) {
            $errors[] = "Nombre d'éléments trop élevé: $element_count (max: 1000)";
        }

        // ===== Vérification 6 : Validation de chaque élément =====
        foreach ($template_data['elements'] as $index => $element) {
            $element_errors = $this->validateTemplateElement($element, $index);
            $errors = array_merge($errors, $element_errors);

            // Limiter à 10 erreurs pour éviter un flood de messages
            if (count($errors) >= 10) {
                $errors[] = '... et plus d\'erreurs détectées';
                break;
            }
        }

        return $errors;
    }

    /**
     * Valider un élément individuel du template
     *
     * @param  array $element Élément à valider
     * @param  int   $index   Index de l'élément dans
     *                        le
 tableau
     * @return array Tableau d'erreurs pour cet élément
     */
    private function validateTemplateElement($element, $index)
    {
        $errors = [];

        // Vérification que c'est un objet
        if (!is_array($element)) {
            $errors[] = "Élément $index: doit être un objet JSON (reçu: " . gettype($element) . ')';
            return $errors;
        }

        // Propriétés obligatoires pour chaque élément
        if (!isset($element['id'])) {
            $errors[] = "Élément $index: propriété 'id' manquante";
        }

        if (!isset($element['type'])) {
            $errors[] = "Élément $index: propriété 'type' manquante";
        }

        // Si les propriétés obligatoires manquent, arrêter ici
        if (count($errors) > 0) {
            return $errors;
        }

        $element_id = $element['id'];
        $element_type = $element['type'];

        // Vérifier le format de l'ID
        if (!is_string($element_id) || empty($element_id)) {
            $errors[] = "Élément $index: id doit être une chaîne non-vide (reçu: '$element_id')";
        }

        // Vérifier le type d'élément valide
        $valid_types = ['text', 'image', 'rectangle', 'line', 'product_table',
                       'customer_info', 'company_logo', 'company_info', 'order_number',
                       'document_type', 'textarea', 'html', 'divider', 'progress-bar',
                       'dynamic-text', 'mentions'];

        if (!in_array($element_type, $valid_types)) {
            $errors[] = "Élément $index ($element_id): type invalide '$element_type' (types valides: " .
                       implode(', ', $valid_types) . ')';
        }

        // Vérifier les propriétés numériques
        $numeric_props = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'zIndex',
                         'borderWidth', 'borderRadius', 'padding', 'margin', 'rotation'];

        foreach ($numeric_props as $prop) {
            if (isset($element[$prop])) {
                if (!is_numeric($element[$prop])) {
                    $errors[] = "Élément $index ($element_id): '$prop' doit être numérique (reçu: " .
                               gettype($element[$prop]) . ')';
                }
            }
        }

        // Vérifier que x, y, width, height sont présents et raisonnables
        $required_position_props = ['x', 'y', 'width', 'height'];
        foreach ($required_position_props as $prop) {
            if (!isset($element[$prop])) {
                $errors[] = "Élément $index ($element_id): propriété '$prop' obligatoire manquante";
            } else {
                $value = (float) $element[$prop];
                if ($value < 0 || $value > 3000) {
                    $errors[] = "Élément $index ($element_id): '$prop' doit être entre 0 et 3000 (reçu: $value)";
                }
            }
        }

        // Vérifier les propriétés de couleur (format hex)
        $color_props = ['color', 'backgroundColor', 'borderColor', 'shadowColor'];
        foreach ($color_props as $prop) {
            if (isset($element[$prop]) && !empty($element[$prop])) {
                $color = $element[$prop];
                if ($color !== 'transparent' && !preg_match('/^#[0-9A-Fa-f]{3,6}$/', $color)) {
                    $errors[] = "Élément $index ($element_id): '$prop' format couleur invalide '$color'";
                }
            }
        }

        // Vérifier les propriétés de texte
        $text_props = ['fontFamily', 'fontWeight', 'textAlign', 'textDecoration', 'fontStyle'];
        $valid_values = [
            'fontWeight' => ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
            'textAlign' => ['left', 'center', 'right', 'justify'],
            'textDecoration' => ['none', 'underline', 'overline', 'line-through'],
            'fontStyle' => ['normal', 'italic', 'oblique']
        ];

        foreach ($text_props as $prop) {
            if (isset($element[$prop]) && isset($valid_values[$prop])) {
                if (!in_array($element[$prop], $valid_values[$prop])) {
                    $errors[] = "Élément $index ($element_id): '$prop' valeur invalide '" .
                               $element[$prop] . "' (valeurs: " . implode(', ', $valid_values[$prop]) . ')';
                }
            }
        }

        return $errors;
    }
}
