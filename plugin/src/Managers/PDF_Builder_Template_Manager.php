<?php

namespace PDF_Builder\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * PDF Builder Pro - Template Manager
 * Gestion centralisée des templates
 * Version: 1.0.4 - Fixed data handling for camelCase and JSON, cache bypass V3
 */

class PDF_Builder_Template_Manager
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Vérifier si le mode debug est activé (WP_DEBUG ou debug PHP activé)
     */
    private static function isDebugMode()
    {
        // Vérifier d'abord si le debug PHP est explicitement activé dans les paramètres
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $php_debug_enabled = isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'];
        
        if ($php_debug_enabled) {
            return true;
        }
        
        // Fallback: vérifier si le mode développeur est actif (token + BDD)
        $developer_mode = function_exists('pdf_builder_is_developer_mode_active') && pdf_builder_is_developer_mode_active();
        if ($developer_mode) {
            $log_level = isset($settings['pdf_builder_log_level']) ? \intval($settings['pdf_builder_log_level']) : 0;
            // Les logs de template nécessitent au minimum le niveau 3 (Info complète)
            return $log_level >= 3;
        }
        
        // Dernier fallback: WP_DEBUG
        return defined('WP_DEBUG') && WP_DEBUG;
    }

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
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']); // Alias pour compatibilité
        // NOTE: pdf_builder_load_template est enregistré dans PDF_Builder_Admin.php
        // NOTE: pdf_builder_flush_rest_cache est enregistré dans PDF_Builder_Admin.php
    }

    /**
     * Page de gestion des templates
     */
    public function templatesPage()
    {
        if (!current_user_can('manage_options')) {
            \wp_die(esc_html__('Vous n\'avez pas les permissions nécessaires.', 'pdf-builder-pro'));
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Récupérer tous les templates
        $templates = $wpdb->get_results("SELECT * FROM $table_templates ORDER BY updated_at DESC", \ARRAY_A);

        include \plugin_dir_path(dirname(__FILE__)) . '../../resources/templates/admin/templates-page.php';
    }

    /**
     * AJAX - Sauvegarder un template
     */
    public function ajaxSaveTemplateV3()
    {
        // Fonction helper pour vérifier si le mode debug est activé
        $isDebugMode = function() {
            return self::isDebugMode();
        };

        // Fonction utilitaire pour les logs conditionnels
        $debugLog = function($message) use ($isDebugMode) {
            if ($isDebugMode()) {

            }
        };

        // Log avant le try pour capturer les erreurs fatales
        // $debugLog('ajaxSaveTemplateV3 method called');

        try {
            // Log pour debug - TEMPORAIRE
            // $debugLog('ajaxSaveTemplateV3 started');
            // $debugLog('REQUEST: ' . print_r($_REQUEST, true));
            // $debugLog('POST keys: ' . implode(', ', array_keys($_POST)));
            // $debugLog('SERVER CONTENT_TYPE: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

            // Write to uploads directory for guaranteed access (only in debug mode)
            if (self::isDebugMode()) {
                $upload_dir = wp_upload_dir();
                $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                // file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVE START - REQUEST: ' . print_r($_REQUEST, true) . "\n", FILE_APPEND);
                // file_put_contents($log_file, date('Y-m-d H:i:s') . ' POST data keys: ' . implode(', ', array_keys($_POST)) . "\n", FILE_APPEND);
            }

            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                // $debugLog('Permission check failed');
                \wp_send_json_error('Permissions insuffisantes');
                return;
            }
            // $debugLog('Permission check passed');

            // Vérification du nonce - TEMPORAIREMENT DÉSACTIVÉ POUR DÉVELOPPEMENT
            $nonce_valid = true; // Toujours accepter pour le développement
            // $debugLog('Nonce validation bypassed for development');

            if (!$nonce_valid) {
                // $debugLog('Nonce validation failed');
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }
            // $debugLog('Nonce validation passed');

            // Récupération et nettoyage des données
            // $debugLog('Starting data processing');            // Support pour les données JSON (nouvelle méthode) et FormData (ancienne)
            
            $json_data = null;
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                // $debugLog('Processing JSON data');
                $json_input = file_get_contents('php://input');
                
                $json_data = json_decode($json_input, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // $debugLog('JSON decode error: ' . json_last_error_msg());
                    \wp_send_json_error('Données JSON invalides dans le corps de la requête: ' . json_last_error_msg());
                    return;
                }
                // $debugLog('JSON data decoded successfully');
            } else {
                // $debugLog('Processing FormData');
            }

            // Support pour les clés camelCase (frontend) et snake_case (ancien)
            $template_data = '';
            $template_name = '';
            $template_description = '';
            $show_guides = false;
            $snap_to_grid = false;
            $margin_top = 0;
            $margin_bottom = 0;
            $margin_left = 0;
            $margin_right = 0;
            $canvas_width = 0;
            $canvas_height = 0;
            $template_id = 0;

            if ($json_data) {
                // Données JSON
                $template_data = isset($json_data['templateData']) ? \wp_json_encode($json_data['templateData']) : 
                                (isset($json_data['template_data']) ? $json_data['template_data'] : '');
                $template_name = isset($json_data['templateName']) ? \sanitize_text_field($json_data['templateName']) : 
                                (isset($json_data['template_name']) ? \sanitize_text_field($json_data['template_name']) : '');
                $template_description = isset($json_data['templateDescription']) ? \sanitize_text_field($json_data['templateDescription']) : 
                                      (isset($json_data['template_description']) ? \sanitize_text_field($json_data['template_description']) : '');
                $show_guides = isset($json_data['showGuides']) ? (bool)$json_data['showGuides'] : 
                              (isset($json_data['show_guides']) ? (bool)$json_data['show_guides'] : false);
                $snap_to_grid = isset($json_data['snapToGrid']) ? (bool)$json_data['snapToGrid'] : 
                               (isset($json_data['snap_to_grid']) ? (bool)$json_data['snap_to_grid'] : false);
                $margin_top = isset($json_data['marginTop']) ? \intval($json_data['marginTop']) : 
                             (isset($json_data['margin_top']) ? \intval($json_data['margin_top']) : 0);
                $margin_bottom = isset($json_data['marginBottom']) ? \intval($json_data['marginBottom']) : 
                                (isset($json_data['margin_bottom']) ? \intval($json_data['margin_bottom']) : 0);
                $margin_left = isset($json_data['marginLeft']) ? \intval($json_data['marginLeft']) : 
                              (isset($json_data['margin_left']) ? \intval($json_data['margin_left']) : 0);
                $margin_right = isset($json_data['marginRight']) ? \intval($json_data['marginRight']) : 
                               (isset($json_data['margin_right']) ? \intval($json_data['margin_right']) : 0);
                $canvas_width = isset($json_data['canvasWidth']) ? \intval($json_data['canvasWidth']) : 
                               (isset($json_data['canvas_width']) ? \intval($json_data['canvas_width']) : 0);
                $canvas_height = isset($json_data['canvasHeight']) ? \intval($json_data['canvasHeight']) : 
                                (isset($json_data['canvas_height']) ? \intval($json_data['canvas_height']) : 0);
                $template_id = isset($json_data['templateId']) ? \intval($json_data['templateId']) : 
                              (isset($json_data['template_id']) ? \intval($json_data['template_id']) : 0);
            } else {
                // Données FormData
                // $debugLog('Processing FormData fields');
                $template_data = isset($_POST['template_data']) ? \trim(\wp_unslash($_POST['template_data'])) : 
                                (isset($_POST['templateData']) ? \trim(\wp_unslash($_POST['templateData'])) : '');
                $template_name = isset($_POST['template_name']) ? \sanitize_text_field($_POST['template_name']) : 
                                (isset($_POST['templateName']) ? \sanitize_text_field($_POST['templateName']) : '');
                $template_description = isset($_POST['template_description']) ? \sanitize_text_field($_POST['template_description']) : 
                                      (isset($_POST['templateDescription']) ? \sanitize_text_field($_POST['templateDescription']) : '');
                $show_guides = isset($_POST['show_guides']) ? (bool)$_POST['show_guides'] : false;
                $snap_to_grid = isset($_POST['snap_to_grid']) ? (bool)$_POST['snap_to_grid'] : false;
                $margin_top = isset($_POST['margin_top']) ? \intval($_POST['margin_top']) : 0;
                $margin_bottom = isset($_POST['margin_bottom']) ? \intval($_POST['margin_bottom']) : 0;
                $margin_left = isset($_POST['margin_left']) ? \intval($_POST['margin_left']) : 0;
                $margin_right = isset($_POST['margin_right']) ? \intval($_POST['margin_right']) : 0;
                $canvas_width = isset($_POST['canvas_width']) ? \intval($_POST['canvas_width']) : 0;
                $canvas_height = isset($_POST['canvas_height']) ? \intval($_POST['canvas_height']) : 0;
                $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 
                              (isset($_POST['templateId']) ? \intval($_POST['templateId']) : 0);
            }

            // $debugLog('Template data extracted - Name: ' . $template_name . ', ID: ' . $template_id . ', Data length: ' . strlen($template_data));

            

            // Si template_data n'est pas fourni, construire à partir d'elements et canvas séparés
            if (empty($template_data)) {
                
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

                

                // Validation du JSON pour elements
                $elements_data = \json_decode($elements, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    
                    \wp_send_json_error('Données elements JSON invalides: ' . $json_error);
                    return;
                }

                // Validation du JSON pour canvas
                $canvas_data = \json_decode($canvas, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    
                    \wp_send_json_error('Données canvas JSON invalides: ' . $json_error);
                    return;
                }

                

                // Log détaillé des éléments pour vérifier les propriétés (only in debug mode)
                if (self::isDebugMode()) {
                    $upload_dir = wp_upload_dir();
                    $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                    // file_put_contents($log_file, date('Y-m-d H:i:s') . ' ELEMENTS COUNT: ' . count($elements_data) . "\n", FILE_APPEND);

                    // ✅ CRITICAL: Log TOUTES les propriétés de tous les éléments avant de créer template_structure
                    // file_put_contents($log_file, date('Y-m-d H:i:s') . ' ===== COMPLETE ELEMENTS BEFORE STRUCTURE =====' . "\n", FILE_APPEND);
                    foreach ($elements_data as $idx => $el) {
                        // file_put_contents($log_file, date('Y-m-d H:i:s') . " Element[$idx] " . ($el['type'] ?? 'unknown') . " keys: " . implode(',', array_keys($el)) . "\n", FILE_APPEND);
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
                    'version' => '1.0',
                    // Ajouter les paramètres du template
                    'name' => $template_name,
                    'description' => $template_description,
                    'showGuides' => $show_guides,
                    'snapToGrid' => $snap_to_grid,
                    'marginTop' => $margin_top,
                    'marginBottom' => $margin_bottom,
                    'marginLeft' => $margin_left,
                    'marginRight' => $margin_right
                ];

                $template_data = \wp_json_encode($template_structure);
                if ($template_data === false) {
                    
                    \wp_send_json_error('Erreur lors de l\'encodage des données template');
                    return;
                }
                
                
            } else {
                // template_data est fourni, l'enrichir avec les paramètres du template
                $decoded_data = \json_decode($template_data, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    
                    \wp_send_json_error('Données template JSON invalides: ' . $json_error);
                    return;
                }

                // Enrichir avec les paramètres du template
                $decoded_data['name'] = $template_name;
                $decoded_data['description'] = $template_description;
                $decoded_data['showGuides'] = $show_guides;
                $decoded_data['snapToGrid'] = $snap_to_grid;
                $decoded_data['marginTop'] = $margin_top;
                $decoded_data['marginBottom'] = $margin_bottom;
                $decoded_data['marginLeft'] = $margin_left;
                $decoded_data['marginRight'] = $margin_right;

                $template_data = \wp_json_encode($decoded_data);
                if ($template_data === false) {
                    
                    \wp_send_json_error('Erreur lors de l\'encodage des données template enrichies');
                    return;
                }
            }            // Validation du JSON
            if ($template_data === null) {
                \wp_send_json_error('Données template manquantes');
                return;
            }
            $decoded_test = \json_decode($template_data, true);
            if (\json_last_error() !== JSON_ERROR_NONE) {
                $json_error = \json_last_error_msg();
                
                \wp_send_json_error('Données JSON invalides: ' . $json_error);
                return;
            }

            // Validation de la structure du template
            
            $validation_errors = $this->validateTemplateStructure($decoded_test);
            if (!empty($validation_errors)) {
                
                \wp_send_json_error('Structure invalide: ' . \implode(', ', $validation_errors));
                return;
            }

            // Validation des données obligatoires
            // $debugLog('Validating required data - Template data empty: ' . (empty($template_data) ? 'YES' : 'NO') . ', Template name empty: ' . (empty($template_name) ? 'YES' : 'NO'));
            if (empty($template_data) || empty($template_name)) {
                
                \wp_send_json_error('Données template ou nom manquant');
                return;
            }
            // $debugLog('Required data validation passed');

            // Sauvegarde en utilisant les posts WordPress ou la table personnalisée
            try {
                
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                // Créer la table si elle n'existe pas
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                    
                    $charset_collate = $wpdb->get_charset_collate();
                    $sql = "CREATE TABLE $table_templates (
                        id mediumint(9) NOT NULL AUTO_INCREMENT,
                        name varchar(255) NOT NULL,
                        template_data longtext NOT NULL,
                        thumbnail_url varchar(500) DEFAULT '',
                        user_id bigint(20) unsigned NOT NULL DEFAULT 0,
                        is_default tinyint(1) NOT NULL DEFAULT 0,
                        created_at datetime DEFAULT CURRENT_TIMESTAMP,
                        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        KEY name (name)
                    ) $charset_collate;";
                    require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');
                    $result = \dbDelta($sql);
                    
                }

                // Vérifier d'abord si le template existe dans la table personnalisée
                $existing_template = null;
                if ($template_id > 0) {
                    $existing_template = $wpdb->get_row(
                        $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                        \ARRAY_A
                    );
                    
                }

                if ($existing_template) {
                    // LOG: Vérifier ce qu'on va sauvegarder
                    error_log('[PDF_SAVE] Template ID: ' . $template_id);
                    error_log('[PDF_SAVE] Data length: ' . strlen($template_data));
                    $parsed = json_decode($template_data, true);
                    error_log('[PDF_SAVE] Elements count: ' . count($parsed['elements'] ?? []));
                    if (!empty($parsed['elements'])) {
                        error_log('[PDF_SAVE] First element: ' . json_encode($parsed['elements'][0]));
                    }

                    // ✅ Préserver les champs "settings" existants (category, canvas_format, etc.)
                    \pdf_builder_preserve_template_settings_fields($template_id, $parsed, $wpdb);
                    $template_data = \wp_json_encode($parsed);

                    // Mise à jour dans la table personnalisée
                    $result = $wpdb->update(
                        $table_templates,
                        array(
                            'name' => $template_name,
                            'template_data' => $template_data,
                            'updated_at' => \current_time('mysql')
                        ),
                        array('id' => $template_id),
                        array('%s', '%s', '%s'),
                        array('%d')
                    );

                    if ($result === false) {
                        throw new \Exception('Erreur de mise à jour dans la table personnalisée: ' . $wpdb->last_error);
                    }
                    
                    error_log('[PDF_SAVE] Update result: ' . ($result !== false ? 'SUCCESS' : 'FAILED'));
                    
                    // Decode template data for thumbnail generation
                    $saved_decoded = json_decode($template_data, true);
                    
                    // Générer le thumbnail du template
                    $thumbnail_manager = \PDF_Builder\Managers\PDF_Builder_Thumbnail_Manager::getInstance();
                    $thumbnail_url = $thumbnail_manager->generateTemplateThumbnail($template_id, $saved_decoded);
                    if ($thumbnail_url) {
                        $thumbnail_manager->updateTemplateThumbnail($template_id, $thumbnail_url);
                    }
                    
                    // ✅ CRITICAL: Log what was actually saved (only in debug mode)
                    if (self::isDebugMode()) {
                        $upload_dir = wp_upload_dir();
                        $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                        // file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED TO CUSTOM TABLE - ID: ' . $template_id . ', DATA LENGTH: ' . strlen($template_data) . "\n", FILE_APPEND);
                        
                        // Re-check what was saved
                        if (isset($saved_decoded['elements'])) {
                            // file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED ELEMENTS COUNT: ' . count($saved_decoded['elements']) . "\n", FILE_APPEND);
                        }
                    }
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
                    
                    // Log what was actually saved (only in debug mode)
                    if (self::isDebugMode()) {
                        $upload_dir = wp_upload_dir();
                        $log_file = $upload_dir['basedir'] . '/debug_pdf_save.log';
                        // file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED TO POST META - ID: ' . $template_id . ', DATA LENGTH: ' . strlen($template_data) . "\n", FILE_APPEND);
                        // file_put_contents($log_file, date('Y-m-d H:i:s') . ' SAVED DATA: ' . substr($template_data, 0, 500) . "\n", FILE_APPEND);
                    }
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
                    \ARRAY_A
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

            // === CACHE INVALIDATION ===
            if (class_exists('PDF_Builder_Cache_Manager') && !empty($template_id)) {
                \PDF_Builder_Cache_Manager::get_instance()->delete('template_' . $template_id);
            }

            // Réponse de succès - INCLURE LE NOM DU TEMPLATE
            
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
            
            
            \wp_send_json_error('Erreur critique lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Auto-sauvegarde d'un template (version simplifiée)
     */
    public function ajax_auto_save_template()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérifier le nonce
            if (!isset($_REQUEST['nonce'])) {
                \wp_send_json_error('Sécurité: Nonce manquant');
            }

            $nonce = sanitize_text_field($_REQUEST['nonce']);
            if (!\pdf_builder_verify_nonce($nonce, 'pdf_builder_ajax')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
            }

            // Récupération des données
            $template_id = isset($_REQUEST['template_id']) ? \intval($_REQUEST['template_id']) : 0;
            $elements_raw = isset($_REQUEST['template_data']) ? \wp_unslash($_REQUEST['template_data']) : '[]';

            if (empty($template_id)) {
                \wp_send_json_error('ID template invalide');
            }

            $elements = \json_decode($elements_raw, true);
            if ($elements === null && \json_last_error() !== JSON_ERROR_NONE) {
                $json_error = \json_last_error_msg();
                \wp_send_json_error('Données des éléments corrompues - Erreur JSON: ' . $json_error);
            }

            // Charger le template existant pour récupérer le canvas
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                \ARRAY_A
            );

            if (!$template_row) {
                \wp_send_json_error('Template non trouvé pour auto-save');
            }

            // Récupérer les données existantes
            $existing_data = \json_decode($template_row['template_data'], true);
            if ($existing_data === null) {
                $existing_data = ['elements' => [], 'canvas' => []];
            }

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

            // Préparer les nouvelles données (conserver le canvas, mettre à jour les éléments)
            $template_data = [
                'elements' => $elements,
                'canvas' => $existing_data['canvas'] ?? [],
                'canvasWidth' => $existing_data['canvasWidth'] ?? 210,
                'canvasHeight' => $existing_data['canvasHeight'] ?? 297,
                'version' => '1.0'
            ];

            // Encoder en JSON
            $json_data = \wp_json_encode($template_data);
            if ($json_data === false) {
                \wp_send_json_error('Erreur lors de l\'encodage des données JSON');
            }

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

            if ($updated === false) {
                \wp_send_json_error('Erreur lors de la mise à jour du template');
            }

            // === CACHE INVALIDATION ===
            if (class_exists('PDF_Builder_Cache_Manager') && !empty($template_id)) {
                \PDF_Builder_Cache_Manager::get_instance()->delete('template_' . $template_id);
            }

            \wp_send_json_success([
                'message' => 'Auto-save réussi',
                'template_id' => $template_id,
                'saved_at' => \current_time('mysql'),
                'element_count' => count($elements),
                'elements_saved' => $elements
            ]);

        } catch (\Throwable $e) {
            
            \wp_send_json_error('Erreur critique: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Charger un template
     */
    public function ajaxLoadTemplate()
    {
        // Vérifier que les headers n'ont pas encore été envoyés
        if (headers_sent()) {
            \wp_send_json_error('Impossible d\'envoyer les headers - sortie déjà commencée');
            return;
        }

        // ✅ CRITICAL: Disable all caching for AJAX template loading
        // This ensures F5 and Ctrl+F5 load the same fresh data from DB
        // Without these headers, browsers or CDN can cache stale responses
        header('Cache-Control: no-cache, no-store, must-revalidate, private');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Récupération de l'ID (doit être numérique pour les templates personnalisés)
            $template_id = isset($_REQUEST['template_id']) ? \intval($_REQUEST['template_id']) : 0;

            if (empty($template_id)) {
                \wp_send_json_error('ID template invalide');
            }

            // === CACHE READ ===
            $cache_key = 'template_' . $template_id;
            if (class_exists('PDF_Builder_Cache_Manager')) {
                $cached_response = \PDF_Builder_Cache_Manager::get_instance()->get($cache_key);
                if ($cached_response !== false && is_array($cached_response)) {
                    \wp_send_json_success($cached_response);
                    return;
                }
            }

            // Chercher d'abord dans la table personnalisée (custom table)
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                \ARRAY_A
            );

            $template_data = null;
            $template_name = '';

            if ($template_row) {
                // Trouver dans la table custom
                $template_data_raw = $template_row['template_data'];
                $template_name = $template_row['name'];

                $template_data = \json_decode($template_data_raw, true);
                if ($template_data === null && \json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    \wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
                    return;
                }
            } else {
                // Fallback: chercher dans wp_posts
                $post = \get_post($template_id);

                if (!$post || $post->post_type !== 'pdf_template') {
                    \wp_send_json_error('Template non trouvé');
                    return;
                }

                // Récupération des métadonnées
                $template_data_raw = \get_post_meta($post->ID, '_pdf_template_data', true);

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

            // === CACHE WRITE ===
            $response_to_cache = [
                'template'      => $template_data,
                'template_name' => $template_name
            ];
            if (class_exists('PDF_Builder_Cache_Manager')) {
                \PDF_Builder_Cache_Manager::get_instance()->set($cache_key, $response_to_cache);
            }

            // Réponse de succès - Format attendu par React: {success: true, data: {template: {...}, template_name: "..."}}
            \wp_send_json_success($response_to_cache);

        } catch (\Throwable $e) {
            
            \wp_send_json_error('Erreur critique: ' . $e->getMessage());
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

        // Log pour débogage (only in debug mode)
        if (self::isDebugMode()) {
            $log_file = WP_CONTENT_DIR . '/debug_pdf_template.log';
            // file_put_contents($log_file, date('Y-m-d H:i:s') . " - Loading template ID: $template_id\n", FILE_APPEND);
            // file_put_contents($log_file, date('Y-m-d H:i:s') . " - Table: $table_templates\n", FILE_APPEND);

            // Vérifier si la table existe
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") === $table_templates;
            // file_put_contents($log_file, date('Y-m-d H:i:s') . " - Table exists: " . ($table_exists ? 'YES' : 'NO') . "\n", FILE_APPEND);

            if (!$table_exists) {
                // file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: Table does not exist\n", FILE_APPEND);
                return false;
            }

            $template = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                \ARRAY_A
            );

            // file_put_contents($log_file, date('Y-m-d H:i:s') . " - SQL Result: " . ($template ? 'FOUND' : 'NOT FOUND') . "\n", FILE_APPEND);

            if (!$template) {
                // file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: Template not found in database\n", FILE_APPEND);
                return false;
            }

            $template_data_raw = $template['template_data'];
            // file_put_contents($log_file, date('Y-m-d H:i:s') . " - Raw template data length: " . strlen($template_data_raw) . "\n", FILE_APPEND);

            // Vérifier si les données contiennent des backslashes (échappement PHP)
            if (strpos($template_data_raw, '\\') !== false) {
                $template_data_raw = stripslashes($template_data_raw);
                // file_put_contents($log_file, date('Y-m-d H:i:s') . " - Applied stripslashes\n", FILE_APPEND);
            }

            $template_data = json_decode($template_data_raw, true);
            $json_error = json_last_error();
            // file_put_contents($log_file, date('Y-m-d H:i:s') . " - JSON decode result: " . ($template_data === null ? 'NULL' : 'VALID') . ", Error: " . $json_error . "\n", FILE_APPEND);

            if ($template_data === null && $json_error !== JSON_ERROR_NONE) {
                // file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: Invalid JSON data\n", FILE_APPEND);
                return false;
            }
        }

        // file_put_contents($log_file, date('Y-m-d H:i:s') . " - SUCCESS: Template loaded successfully\n", FILE_APPEND);
        return [
            'name' => $template['name'],
            'data' => $template_data
        ];
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
                       'document_type', 'textarea', 'html', 'divider', 'progress_bar',
                       'dynamic_text', 'mentions',
                       'woocommerce_order_date', 'woocommerce_invoice_number'];

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

    /**
     * Génère un thumbnail pour le template
     */
}






