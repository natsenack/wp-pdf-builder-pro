<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Template Manager
 * Gestion centralisée des templates
 */

class PDF_Builder_Template_Manager {

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance) {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // AJAX handlers pour les templates
        add_action('wp_ajax_pdf_builder_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);
    }

    /**
     * Page de gestion des templates
     */
    public function templates_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Récupérer tous les templates
        $templates = $wpdb->get_results("SELECT * FROM $table_templates ORDER BY updated_at DESC", ARRAY_A);

        include plugin_dir_path(dirname(__FILE__)) . '../templates-page.php';
    }

    /**
     * AJAX - Sauvegarder un template
     */
    public function ajax_save_template() {
        // Log pour débogage
        error_log('PDF Builder: ajax_save_template called');

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('PDF Builder: Insufficient permissions for user');
            wp_send_json_error('Permissions insuffisantes');
        }

        // Log du nonce reçu
        $received_nonce = isset($_POST['nonce']) ? $_POST['nonce'] : 'none';
        error_log('PDF Builder: Received nonce: ' . $received_nonce);

        // Vérification de sécurité avec nonce WordPress (accepter plusieurs types de nonce)
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions') ||
                          wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
        }

        if (!$nonce_valid) {
            error_log('PDF Builder: Nonce validation failed');
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        error_log('PDF Builder: Nonce validation passed');

        $template_data = isset($_POST['template_data']) ? trim(wp_unslash($_POST['template_data'])) : '';
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log('PDF Builder: Template data length: ' . strlen($template_data));
        error_log('PDF Builder: Template name: ' . $template_name);
        error_log('PDF Builder: Template ID: ' . $template_id);

        // Log des premières 500 caractères des données pour débogage
        error_log('PDF Builder: Template data preview: ' . substr($template_data, 0, 500));

        // Valider que c'est du JSON valide
        $decoded_test = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('PDF Builder: JSON decode error: ' . json_last_error_msg());
            error_log('PDF Builder: Raw template data that failed (first 2000 chars): ' . substr($template_data, 0, 2000));
            error_log('PDF Builder: Template data length: ' . strlen($template_data));
            error_log('PDF Builder: Last JSON error code: ' . json_last_error());
            wp_send_json_error('Données JSON invalides: ' . json_last_error_msg());
        }

        // Log des données décodées pour debug
        error_log('PDF Builder: Successfully decoded JSON. Element count: ' . (isset($decoded_test['elements']) ? count($decoded_test['elements']) : 'N/A'));

        if (empty($template_data) || empty($template_name)) {
            error_log('PDF Builder: Missing template data or name');
            wp_send_json_error('Données template ou nom manquant');
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $data = array(
            'name' => $template_name,
            'template_data' => $template_data,
            'updated_at' => current_time('mysql')
        );

        error_log('PDF Builder: Preparing to save template. Table: ' . $table_templates);

        if ($template_id > 0) {
            // Update existing template
            error_log('PDF Builder: Updating existing template ID: ' . $template_id);
            $result = $wpdb->update($table_templates, $data, array('id' => $template_id));
        } else {
            // Create new template
            error_log('PDF Builder: Creating new template');
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_templates, $data);
            $template_id = $wpdb->insert_id;
        }

        error_log('PDF Builder: Database operation result: ' . ($result !== false ? 'success' : 'failed'));
        if ($result === false) {
            error_log('PDF Builder: Database error: ' . $wpdb->last_error);
        }

        if ($result !== false) {
            error_log('PDF Builder: Template saved successfully with ID: ' . $template_id);

            // DEBUG: Log the saved data structure
            $saved_data = json_decode($template_data, true);
            if ($saved_data && isset($saved_data['elements'])) {
                error_log('PDF Builder SAVE - Elements saved: ' . count($saved_data['elements']));
                if (!empty($saved_data['elements'])) {
                    $first_elem = $saved_data['elements'][0];
                    error_log('PDF Builder SAVE - First element ID: ' . ($first_elem['id'] ?? 'NO ID'));
                    error_log('PDF Builder SAVE - First element backgroundColor: ' . ($first_elem['backgroundColor'] ?? 'NO BGCOLOR'));
                }
            }

            wp_send_json_success(array(
                'message' => 'Template sauvegardé avec succès',
                'template_id' => $template_id
            ));
        } else {
            error_log('PDF Builder: Failed to save template');
            wp_send_json_error('Erreur lors de la sauvegarde du template');
        }
    }

    /**
     * AJAX - Charger un template
     */
    public function ajax_load_template() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$template_id) {
            wp_send_json_error('ID template invalide');
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if ($template) {
            $template_data_raw = $template['template_data'];

            // DEBUG: Log complet des données de la DB
            error_log("PDF Builder DB - Raw template_data from DB: " . $template_data_raw);

            $template_data = json_decode($template_data_raw, true);
            if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log("PDF Builder DB - JSON decode error: " . json_last_error_msg());
                wp_send_json_error('Données du template corrompues - Erreur JSON: ' . json_last_error_msg());
            } else {
                // DEBUG: Log des données décodées
                error_log("PDF Builder DB - Decoded template_data keys: " . implode(', ', array_keys($template_data)));
                if (isset($template_data['elements'])) {
                    error_log("PDF Builder DB - Number of elements: " . count($template_data['elements']));
                    foreach ($template_data['elements'] as $index => $element) {
                        if (is_array($element)) {
                            error_log("PDF Builder DB - Element $index keys: " . implode(', ', array_keys($element)));
                            if (isset($element['type'])) {
                                error_log("PDF Builder DB - Element $index type: " . $element['type']);
                                if ($element['type'] === 'product_table') {
                                    error_log("PDF Builder DB - Table element $index properties: " . json_encode(array_intersect_key($element, array_flip(['showHeaders', 'showBorders', 'columns', 'tableStyle', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal']))));
                                }
                            }
                        }
                    }
                }
            }

            wp_send_json_success(array(
                'template' => $template_data,
                'name' => $template['name']
            ));
        } else {
            wp_send_json_error('Template non trouvé');
        }
    }

    /**
     * AJAX - Vider le cache REST
     */
    public function ajax_flush_rest_cache() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vider le cache des transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        wp_send_json_success('Cache REST vidé avec succès');
    }

    /**
     * Charger un template de manière robuste
     */
    public function load_template_robust($template_id) {
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
}