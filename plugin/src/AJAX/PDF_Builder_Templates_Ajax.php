<?php
/**
 * PDF Builder Pro - Templates AJAX Handler
 * Gestion des actions AJAX pour les templates prédéfinis
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDF_Builder_Templates_Ajax {

    public function __construct() {
        // Actions pour les templates prédéfinis - géré dans predefined-templates-manager.php
        // add_action('wp_ajax_pdf_builder_load_predefined_template', array($this, 'load_predefined_template'));
        add_action('wp_ajax_pdf_builder_create_from_predefined', array($this, 'create_from_predefined'));

        // Actions pour les templates personnalisés
        add_action('wp_ajax_pdf_builder_save_template_settings', array($this, 'save_template_settings'));
        add_action('wp_ajax_pdf_builder_set_default_template', array($this, 'set_default_template'));
        add_action('wp_ajax_pdf_builder_delete_template', array($this, 'delete_template'));
    }

    /**
     * Charge un template prédéfini depuis le fichier JSON
     */
    public function load_predefined_template() {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_predefined_templates')) {
                wp_send_json_error('Nonce invalide');
            }

            $template_slug = sanitize_text_field($_POST['template_slug'] ?? '');

            if (empty($template_slug)) {
                wp_send_json_error('Slug du template manquant');
            }

            // Chemin vers le dossier des templates prédéfinis
            $predefined_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/predefined/';
            $template_file = $predefined_dir . $template_slug . '.json';

            if (!file_exists($template_file)) {
                wp_send_json_error('Template prédéfini non trouvé');
            }

            // Charger le contenu du template
            $content = file_get_contents($template_file);
            $template_data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Erreur lors du décodage du JSON du template');
            }

            wp_send_json_success(array(
                'template' => $template_data,
                'slug' => $template_slug
            ));

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du chargement du template: ' . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau template personnalisé à partir d'un template prédéfini
     */
    public function create_from_predefined() {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                wp_send_json_error('Nonce invalide');
            }

            $template_slug = sanitize_text_field($_POST['template_slug'] ?? '');
            $template_name = sanitize_text_field($_POST['template_name'] ?? '');

            if (empty($template_slug)) {
                wp_send_json_error('Slug du template manquant');
            }

            if (empty($template_name)) {
                wp_send_json_error('Nom du template manquant');
            }

            // Charger d'abord le template prédéfini
            $predefined_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/predefined/';
            $template_file = $predefined_dir . $template_slug . '.json';

            if (!file_exists($template_file)) {
                wp_send_json_error('Template prédéfini non trouvé');
            }

            $content = file_get_contents($template_file);
            $template_data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Erreur lors du décodage du JSON du template');
            }

            // Créer un nouveau template personnalisé dans la base de données
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Encoder les données du template
            $template_json = wp_json_encode($template_data);

            // Insérer le nouveau template
            $result = $wpdb->insert(
                $table_templates,
                array(
                    'name' => $template_name,
                    'template_data' => $template_json,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'is_default' => 0
                ),
                array('%s', '%s', '%s', '%s', '%d')
            );

            if ($result === false) {
                wp_send_json_error('Erreur lors de la création du template dans la base de données');
            }

            $new_template_id = $wpdb->insert_id;

            wp_send_json_success(array(
                'template_id' => $new_template_id,
                'message' => 'Template créé avec succès',
                'redirect_url' => admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $new_template_id)
            ));

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la création du template: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarde les paramètres d'un template
     */
    public function save_template_settings() {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            $name = sanitize_text_field($_POST['name'] ?? '');
            $description = sanitize_textarea_field($_POST['description'] ?? '');
            $is_public = intval($_POST['is_public'] ?? 0);
            $paper_size = sanitize_text_field($_POST['paper_size'] ?? 'A4');
            $orientation = sanitize_text_field($_POST['orientation'] ?? 'portrait');
            $category = sanitize_text_field($_POST['category'] ?? 'autre');

            if (empty($template_id) || empty($name)) {
                wp_send_json_error('Données manquantes');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Vérifier que le template existe
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_templates WHERE id = %d",
                $template_id
            ));

            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Mettre à jour les paramètres
            $result = $wpdb->update(
                $table_templates,
                array(
                    'name' => $name,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $template_id),
                array('%s', '%s'),
                array('%d')
            );

            if ($result === false) {
                wp_send_json_error('Erreur lors de la mise à jour du template');
            }

            wp_send_json_success(array(
                'message' => 'Paramètres sauvegardés avec succès'
            ));

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Définit un template comme template par défaut
     */
    public function set_default_template() {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            $is_default = intval($_POST['is_default'] ?? 0);

            if (empty($template_id)) {
                wp_send_json_error('ID du template manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Vérifier que le template existe
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_templates WHERE id = %d",
                $template_id
            ));

            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Si on définit comme défaut, retirer le statut par défaut des autres templates
            if ($is_default) {
                $wpdb->update(
                    $table_templates,
                    array('is_default' => 0),
                    array('is_default' => 1),
                    array('%d'),
                    array('%d')
                );
            }

            // Mettre à jour le statut du template
            $result = $wpdb->update(
                $table_templates,
                array('is_default' => $is_default),
                array('id' => $template_id),
                array('%d'),
                array('%d')
            );

            if ($result === false) {
                wp_send_json_error('Erreur lors de la mise à jour du statut par défaut');
            }

            $message = $is_default ? 'Template défini comme par défaut' : 'Statut par défaut retiré';

            wp_send_json_success(array(
                'message' => $message
            ));

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la modification du statut: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un template
     */
    public function delete_template() {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);

            if (empty($template_id)) {
                wp_send_json_error('ID du template manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Vérifier que le template existe
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_templates WHERE id = %d",
                $template_id
            ));

            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Supprimer le template
            $result = $wpdb->delete(
                $table_templates,
                array('id' => $template_id),
                array('%d')
            );

            if ($result === false) {
                wp_send_json_error('Erreur lors de la suppression du template');
            }

            wp_send_json_success(array(
                'message' => 'Template supprimé avec succès'
            ));

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}

// Initialiser le handler AJAX
new PDF_Builder_Templates_Ajax();