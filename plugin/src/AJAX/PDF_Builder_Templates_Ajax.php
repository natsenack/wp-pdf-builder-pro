<?php

/**
 * PDF Builder Pro - Templates AJAX Handler
 * Gestion des actions AJAX pour les templates prédéfinis
 */

namespace WP_PDF_Builder_Pro\AJAX;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PdfBuilderTemplatesAjax
{
    public function __construct()
    {
        // Actions pour les templates prédéfinis - géré dans predefined-templates-manager.php
        // add_action('wp_ajax_pdf_builder_load_predefined_template', array($this, 'loadPredefinedTemplate'));
        add_action('wp_ajax_pdf_builder_create_from_predefined', array($this, 'createFromPredefined'));
        add_action('wp_ajax_pdf_builder_load_predefined_into_editor', array($this, 'loadPredefinedIntoEditor'));
        // Actions pour les templates personnalisés
        add_action('wp_ajax_pdf_builder_load_template_settings', array($this, 'loadTemplateSettings'));
        add_action('wp_ajax_pdf_builder_save_template_settings', array($this, 'saveTemplateSettings'));
        add_action('wp_ajax_pdf_builder_set_default_template', array($this, 'setDefaultTemplate'));
        add_action('wp_ajax_pdf_builder_delete_template', array($this, 'deleteTemplate'));
        add_action('wp_ajax_pdf_builder_duplicate_template', array($this, 'duplicateTemplate'));
    }

    /**
     * Charge un template prédéfini depuis le fichier JSON
     */
    public function loadPredefinedTemplate()
    {
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
    public function createFromPredefined()
    {
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
            $result = $wpdb->insert($table_templates, array(
                    'name' => $template_name,
                    'template_data' => $template_json,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'is_default' => 0
                ), array('%s', '%s', '%s', '%s', '%d'));
            if ($result === false) {
                wp_send_json_error('Erreur lors de la création du template dans la base de données');
            }

            $new_template_id = $wpdb->insert_id;
            wp_send_json_success(array(
                'template_id' => 1,
                'message' => 'Redirection vers l\'éditeur unique',
                'redirect_url' => admin_url('admin.php?page=pdf-builder-react-editor')
            ));
        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la création du template: ' . $e->getMessage());
        }
    }

    /**
     * Charge un modèle prédéfini directement dans le template ID 1
     */
    public function loadPredefinedIntoEditor()
    {
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
            if (empty($template_slug)) {
                wp_send_json_error('Slug du modèle prédéfini manquant');
            }

            // Charger le modèle prédéfini depuis le fichier
            $predefined_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/predefined/';
            $template_file = $predefined_dir . $template_slug . '.json';
            if (!file_exists($template_file)) {
                wp_send_json_error('Modèle prédéfini introuvable');
            }

            $content = file_get_contents($template_file);
            $predefined_data = json_decode($content, true);
            if (!$predefined_data || !isset($predefined_data['elements'])) {
                wp_send_json_error('Format du modèle prédéfini invalide');
            }

            // Préparer les données du template
            $template_data = [
                'name' => $predefined_data['name'] ?? 'Template depuis modèle prédéfini',
                'elements' => $predefined_data['elements'],
                'canvas_settings' => [
                    'width' => 595, // A4 width in points
                    'height' => 842, // A4 height in points
                    'background_color' => $predefined_data['canvas_settings']['background_color'] ?? '#ffffff'
                ],
                'version' => '1.0',
                'last_modified' => current_time('mysql')
            ];
// Mettre à jour le template ID 1 dans la base de données
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $result = $wpdb->update($table_templates, [
                    'name' => $template_data['name'],
                    'template_data' => wp_json_encode($template_data),
                    'updated_at' => current_time('mysql')
                ], ['id' => 1], ['%s', '%s', '%s'], ['%d']);
            if ($result === false) {
                wp_send_json_error('Erreur lors de la mise à jour du template');
            }

            wp_send_json_success([
                'message' => 'Modèle prédéfini chargé avec succès',
                'redirect_url' => admin_url('admin.php?page=pdf-builder-react-editor')
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du chargement du modèle prédéfini: ' . $e->getMessage());
        }
    }

    /**
     * Charge les paramètres d'un template
     */
    public function loadTemplateSettings()
    {
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
// Récupérer le template
            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            if (!$template) {
                wp_send_json_error('Template non trouvé');
            }

            // Extraire les informations depuis template_data si elles existent
            $template_data = json_decode($template['template_data'] ?? '{}', true);
            $settings = array(
                'name' => $template['name'],
                'description' => $template_data['description'] ?? 'Description du template...',
                'category' => $template_data['category'] ?? 'autre',
                'is_public' => $template_data['is_public'] ?? false,
                'paper_size' => $template_data['paper_size'] ?? 'A4',
                'orientation' => $template_data['orientation'] ?? 'portrait'
            );
            wp_send_json_success($settings);
        } catch (Exception $e) {
            wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarde les paramètres d'un template
     */
    public function saveTemplateSettings()
    {
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
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Récupérer les données actuelles du template
            $current_template = $wpdb->get_row($wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            $template_data = json_decode($current_template['template_data'] ?? '{}', true);
// Mettre à jour les paramètres dans template_data
            $template_data['description'] = $description;
            $template_data['category'] = $category;
            $template_data['is_public'] = $is_public;
            $template_data['paper_size'] = $paper_size;
            $template_data['orientation'] = $orientation;
// Mettre à jour le template
            $result = $wpdb->update($table_templates, array(
                    'name' => $name,
                    'template_data' => wp_json_encode($template_data),
                    'updated_at' => current_time('mysql')
                ), array('id' => $template_id), array('%s', '%s', '%s'), array('%d'));
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
    public function setDefaultTemplate()
    {
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
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Si on définit comme défaut, retirer le statut par défaut des autres templates
            if ($is_default) {
                $wpdb->update($table_templates, array('is_default' => 0), array('is_default' => 1), array('%d'), array('%d'));
            }

            // Mettre à jour le statut du template
            $result = $wpdb->update($table_templates, array('is_default' => $is_default), array('id' => $template_id), array('%d'), array('%d'));
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
    public function deleteTemplate()
    {
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
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Supprimer le template
            $result = $wpdb->delete($table_templates, array('id' => $template_id), array('%d'));
            if ($result === false) {
                wp_send_json_error('Erreur lors de la suppression du template');
            }

            // Récupérer le nom du template pour la notification
            $template_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_templates WHERE id = %d", $template_id));
// Déclencher le hook de suppression de template
            do_action('pdf_builder_template_deleted', $template_id, $template_name ?: 'Template #' . $template_id);
            wp_send_json_success(array(
                'message' => 'Template supprimé avec succès'
            ));
        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Duplique un template existant
     */
    public function duplicateTemplate()
    {
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
            $template_name = sanitize_text_field($_POST['template_name'] ?? '');
            if (empty($template_id) || empty($template_name)) {
                wp_send_json_error('ID du template ou nom manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
// Vérifier que le template existe
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                wp_send_json_error('Template non trouvé');
            }

            // Créer une copie du template
            $result = $wpdb->insert($table_templates, array(
                    'name' => $template_name,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'is_default' => 0
                ), array('%s', '%s', '%s', '%d'));
            if ($result === false) {
                wp_send_json_error('Erreur lors de la duplication du template');
            }

            $new_template_id = $wpdb->insert_id;
            wp_send_json_success(array(
                'message' => 'Template dupliqué avec succès',
                'template_id' => $new_template_id
            ));
        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la duplication: ' . $e->getMessage());
        }
    }
}

// Initialiser le handler AJAX
new PdfBuilderTemplatesAjax();
