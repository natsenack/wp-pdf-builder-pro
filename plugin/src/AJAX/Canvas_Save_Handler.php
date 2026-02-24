<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * Canvas Save Handler - Sauvegarde 100% des éléments du canvas
 * 
 * Responsabilité: Gérer la persistance complète des éléments du builder React,
 * incluant: positions, styles, propriétés spécifiques, données réelles
 */

namespace PDF_Builder\AJAX;

if ( ! defined( 'ABSPATH' ) ) exit;

use PDF_Builder\Security\Security_Validator;

class Canvas_Save_Handler {

    /**
     * Enregistre les hooks
     */
    public static function register_hooks() {
        \error_log('[Canvas_Save_Handler] Registering hooks...');
        \add_action('wp_ajax_pdf_builder_save_canvas', [self::class, 'save_canvas_ajax']);
        \add_action('wp_ajax_pdf_builder_save_template_full', [self::class, 'save_template_full_ajax']);
        \add_action('wp_ajax_pdf_builder_render_template_html', [self::class, 'render_template_html_ajax']);
        \error_log('[Canvas_Save_Handler] Hooks registered successfully');
    }

    /**
     * AJAX: Sauvegarde le canvas avec tous ses éléments
     * 
     * ✅ PHASE 12: Support pour JSON body depuis React
     * 
     * Attendu: POST (JSON body || FormData) {
     *   template_id: number
     *   canvas_data: {JSON string || object}
     *   nonce: string
     * }
     */
    public static function save_canvas_ajax() {
        try {
            // Permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // ✅ PHASE 12: Lire les données depuis JSON body ou POST parameters
            $input_data = self::get_post_data();

            // Nonce
            if (!\pdf_builder_verify_nonce($input_data['nonce'] ?? '', 'pdf_builder_canvas')) {
                \error_log('[Canvas Save] Nonce invalid: ' . ($input_data['nonce'] ?? 'NO_NONCE_PROVIDED'));
                \wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($input_data['template_id'] ?? 0);
            if (empty($template_id)) {
                \wp_send_json_error('template_id manquant');
            }

            // Récupérer et valider les données du canvas
            $canvas_data = $input_data['canvas_data'] ?? null;
            
            // Si c'est un string JSON, le parser
            if (is_string($canvas_data)) {
                $canvas_data = json_decode($canvas_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \error_log('[Canvas Save] JSON decode error: ' . json_last_error_msg());
                    \wp_send_json_error('Données JSON invalides');
                }
            }

            if (empty($canvas_data)) {
                \error_log('[Canvas Save] canvas_data empty or null');
                \wp_send_json_error('canvas_data manquant');
            }

            // Valider la structure
            $validation = self::validate_canvas_data($canvas_data);
            if (!$validation['valid']) {
                \error_log('[Canvas Save] Validation error: ' . json_encode($validation['errors']));
                \wp_send_json_error('Données invalides: ' . implode(', ', $validation['errors']));
            }

            // Sauvegarder en DB
            $result = self::save_to_database($template_id, $canvas_data);
            if (!$result['success']) {
                \wp_send_json_error($result['error']);
            }

            \wp_send_json_success([
                'message' => 'Canvas sauvegardé avec succès',
                'template_id' => $template_id,
                'element_count' => count($canvas_data['elements'] ?? []),
                'timestamp' => current_time('mysql'),
            ]);

        } catch (\Exception $e) {
            \error_log('[Canvas Save] Exception: ' . $e->getMessage());
            \wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Sauvegarde le template COMPLET (métadonnées + canvas + éléments)
     * 
     * ✅ PHASE 12: Support pour JSON body depuis React
     * 
     * Attendu: POST (JSON body) ||(FormData) {
     *   template_id: number
     *   template_name: string
     *   template_description: string
     *   template_data: JSON string ou object
     *   nonce: string
     * }
     */
    public static function save_template_full_ajax() {
        \error_log('[Canvas Save] ✅ save_template_full_ajax called!');
        try {
            // Permissions
            if (!\current_user_can('manage_options')) {
                \error_log('[Canvas Save] User does not have manage_options capability');
                \wp_send_json_error('Permissions insuffisantes');
            }

            // ✅ PHASE 12: Lire les données depuis JSON body ou POST parameters
            $input_data = self::get_post_data();

            // Nonce
            if (!\pdf_builder_verify_nonce($input_data['nonce'] ?? '', 'pdf_builder_templates')) {
                \error_log('[Canvas Save] Nonce invalid: ' . ($input_data['nonce'] ?? 'NO_NONCE_PROVIDED'));
                \wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($input_data['template_id'] ?? 0);
            $template_name = \sanitize_text_field($input_data['template_name'] ?? '');
            $template_description = \sanitize_textarea_field($input_data['template_description'] ?? '');

            if (empty($template_id) || empty($template_name)) {
                \error_log('[Canvas Save] Missing template_id or template_name: ' . json_encode([
                    'template_id' => $template_id,
                    'template_name' => $template_name
                ]));
                \wp_send_json_error('template_id ou template_name manquant');
            }

            // Récupérer et valider les données complètes du template
            $template_data = $input_data['template_data'] ?? null;
            
            // Si c'est un string JSON, le parser
            if (is_string($template_data)) {
                $template_data = json_decode($template_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \error_log('[Canvas Save] JSON decode error: ' . json_last_error_msg());
                    \wp_send_json_error('Données JSON invalides');
                }
            }

            if (empty($template_data)) {
                \error_log('[Canvas Save] template_data empty or null');
                \wp_send_json_error('template_data manquant');
            }

            // Valider
            if (!isset($template_data['elements']) || !is_array($template_data['elements'])) {
                \error_log('[Canvas Save] Elements invalides: ' . json_encode($template_data));
                \wp_send_json_error('Elements invalides');
            }

            if (!isset($template_data['canvas']) || !is_array($template_data['canvas'])) {
                \error_log('[Canvas Save] Canvas invalide: ' . json_encode($template_data));
                \wp_send_json_error('Canvas invalide');
            }

            \error_log('[Canvas Save] Saving template ' . $template_id . ' with ' . count($template_data['elements']) . ' elements');

            // Sauvegarder en DB
            $result = self::save_template_to_database($template_id, [
                'name' => $template_name,
                'description' => $template_description,
                'data' => $template_data,
            ]);

            if (!$result['success']) {
                \wp_send_json_error($result['error']);
            }

            \wp_send_json_success([
                'message' => 'Template sauvegardé avec succès',
                'template_id' => $template_id,
                'element_count' => count($template_data['elements']),
                'timestamp' => current_time('mysql'),
            ]);

        } catch (\Exception $e) {
            \error_log('[Template Save] Exception: ' . $e->getMessage());
            \wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Rend un template HTML à partir du JSON template_data
     * 
     * Utilisé pour générer un aperçu HTML du template
     * 
     * Attendu: POST (FormData) {
     *   template_data: JSON string
     *   order_data: JSON string (optionnel)
     * }
     * 
     * Retourne: {success: true, data: {html: "..."}}
     */
    public static function render_template_html_ajax() {
        try {
            \error_log('[HTML Render] AJAX request received');
            
            // Get raw POST data (handle both JSON body and FormData)
            $input_data = self::get_post_data();
            
            // Basic security check - verify nonce if provided
            if (!empty($input_data['nonce'])) {
                if (!\pdf_builder_verify_nonce($input_data['nonce'], 'pdf_builder_canvas')) {
                    \error_log('[HTML Render] Invalid nonce provided');
                    \wp_send_json_error(['error' => 'Nonce invalide']);
                    return;
                }
            }
            
            $template_data_json = $input_data['template_data'] ?? '';
            $order_data_json = $input_data['order_data'] ?? '{}';

            if (empty($template_data_json)) {
                \error_log('[HTML Render] Missing template_data');
                \wp_send_json_error([
                    'error' => 'template_data manquant',
                ]);
                return;
            }

            // Parser le JSON
            $template_data = json_decode($template_data_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \error_log('[HTML Render] JSON decode error for template_data: ' . json_last_error_msg());
                \wp_send_json_error([
                    'error' => 'template_data JSON invalide',
                ]);
                return;
            }

            $order_data = json_decode($order_data_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $order_data = [];
            }

            \error_log('[HTML Render] Rendering template with ' . count($template_data['elements'] ?? []) . ' elements');

            // Charger DocumentHTMLGenerator
            if (!class_exists(\PDF_Builder\HTMLGenerators\DocumentHTMLGenerator::class)) {
                \error_log('[HTML Render] DocumentHTMLGenerator class not found');
                \wp_send_json_error([
                    'error' => 'HTML Generator not available',
                ]);
                return;
            }

            // Récupérer les données de l'entreprise (optionnel)
            $company_data = self::get_company_data();

            // Générer l'HTML
            $html_generator = new \PDF_Builder\HTMLGenerators\DocumentHTMLGenerator(
                $template_data,
                $order_data,
                $company_data
            );

            $html = $html_generator->generate();

            if (empty($html)) {
                \error_log('[HTML Render] Generated HTML is empty');
                \wp_send_json_error([
                    'error' => 'Failed to generate HTML',
                ]);
                return;
            }

            \error_log('[HTML Render] HTML generated successfully, length=' . strlen($html));

            // Retourner l'HTML
            \wp_send_json_success([
                'html' => $html,
            ]);

        } catch (\Exception $e) {
            \error_log('[HTML Render] Exception: ' . $e->getMessage());
            \wp_send_json_error([
                'error' => 'Erreur: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Récupère les données de l'entreprise depuis les options WordPress
     */
    private static function get_company_data() {
        return [
            'name' => \get_option('pdf_builder_company_name', ''),
            'address' => \get_option('pdf_builder_company_address', ''),
            'phone' => \get_option('pdf_builder_company_phone', ''),
            'email' => \get_option('pdf_builder_company_email', ''),
            'logo' => \get_option('pdf_builder_company_logo', ''),
        ];
    }

    /**     * ✅ PHASE 12: Récupère les données POST (FormData ou JSON body)
     * 
     * Support dual pour FormData classique et JSON body (depuis React)
     */
    private static function get_post_data() {
        // Priorité 1: Vérifier si c'est un JSON body
        $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($content_type, 'application/json') !== false) {
            $raw_input = file_get_contents('php://input');
            if (!empty($raw_input)) {
                $json_data = json_decode($raw_input, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
                    \error_log('[Canvas Save] Parsed JSON body: ' . json_encode(array_keys($json_data)));
                    return $json_data;
                }
            }
        }

        // Priorité 2: Utiliser $_POST classique (FormData)
        \error_log('[Canvas Save] Using $_POST data: ' . json_encode(array_keys($_POST)));
        return $_POST;
    }

    /**     * Sauvegarde les données du canvas en DB
     * 
     * Insère/met à jour la colonne template_data avec tout le contenu
     */
    private static function save_to_database($template_id, $canvas_data) {
        global $wpdb;

        try {
            $table = $wpdb->prefix . 'pdf_builder_templates';

            // Récupérer le template existant
            $existing = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id)
            );

            if (!$existing) {
                return ['success' => false, 'error' => 'Template non trouvé'];
            }

            // Décoder les données existantes pour préserver les métadonnées
            $current_data = json_decode($existing->template_data ?? '{}', true);
            if (!is_array($current_data)) {
                $current_data = [];
            }

            // Fusionner: garder les anciennes métadonnées, remplacer canvas + elements
            $merged_data = array_merge($current_data, [
                'version' => '2.0',
                'canvas' => $canvas_data['canvas'] ?? [],
                'elements' => $canvas_data['elements'] ?? [],
                'lastSaved' => current_time('mysql'),
            ]);

            // Sauvegarder en DB
            $updated = $wpdb->update(
                $table,
                [
                    'template_data' => wp_json_encode($merged_data),
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => $template_id],
                ['%s', '%s'],
                ['%d']
            );

            if ($updated === false) {
                \error_log('[Canvas Save] DB update failed: ' . $wpdb->last_error);
                return ['success' => false, 'error' => 'Erreur DB: ' . $wpdb->last_error];
            }

            \error_log("[Canvas Save] Template $template_id sauvegardé avec " . 
                count($canvas_data['elements'] ?? []) . ' éléments');

            return ['success' => true];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sauvegarde le template COMPLET incluant métadonnées
     */
    private static function save_template_to_database($template_id, $template_info) {
        global $wpdb;

        try {
            $table = $wpdb->prefix . 'pdf_builder_templates';

            // Préparer les données complètes
            $template_data = $template_info['data'];

            // Ajouter les métadonnées à niveau supérieur pour compatibilité
            $full_data = [
                'version' => '2.0',
                'template' => [
                    'name' => $template_info['name'],
                    'description' => $template_info['description'],
                ],
                'canvas' => $template_data['canvas'] ?? [],
                'elements' => $template_data['elements'] ?? [],
                'lastSaved' => current_time('mysql'),
            ];

            // Sauvegarder
            $updated = $wpdb->update(
                $table,
                [
                    'name' => $template_info['name'],
                    'template_data' => wp_json_encode($full_data),
                    'updated_at' => current_time('mysql'),
                ],
                ['id' => $template_id],
                ['%s', '%s', '%s'],
                ['%d']
            );

            if ($updated === false) {
                return ['success' => false, 'error' => 'Erreur DB'];
            }

            return ['success' => true];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Valide la structure des données du canvas
     */
    private static function validate_canvas_data($data) {
        $errors = [];

        // Vérifier les éléments
        if (!isset($data['elements']) || !is_array($data['elements'])) {
            $errors[] = 'Elements doit être un array';
            return ['valid' => false, 'errors' => $errors];
        }

        foreach ($data['elements'] as $idx => $element) {
            if (!is_array($element) && !is_object($element)) {
                $errors[] = "Élément $idx: type invalide";
                continue;
            }

            $elem = (array)$element;

            // Propriétés critiques
            if (empty($elem['id'])) {
                $errors[] = "Élément $idx: id manquant";
            }
            if (empty($elem['type'])) {
                $errors[] = "Élément $idx: type manquant";
            }
            if (!isset($elem['x']) || !is_numeric($elem['x'])) {
                $errors[] = "Élément $idx: x invalide";
            }
            if (!isset($elem['y']) || !is_numeric($elem['y'])) {
                $errors[] = "Élément $idx: y invalide";
            }
            if (!isset($elem['width']) || !is_numeric($elem['width'])) {
                $errors[] = "Élément $idx: width invalide";
            }
            if (!isset($elem['height']) || !is_numeric($elem['height'])) {
                $errors[] = "Élément $idx: height invalide";
            }
        }

        // Vérifier le canvas
        if (!isset($data['canvas']) || !is_array($data['canvas'])) {
            $errors[] = 'Canvas doit être un object';
        } else {
            $canvas = $data['canvas'];
            if (!isset($canvas['width']) || !is_numeric($canvas['width'])) {
                $errors[] = 'Canvas: width invalide';
            }
            if (!isset($canvas['height']) || !is_numeric($canvas['height'])) {
                $errors[] = 'Canvas: height invalide';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
