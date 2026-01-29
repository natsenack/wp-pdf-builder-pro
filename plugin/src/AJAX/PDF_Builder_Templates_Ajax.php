<?php

/**
 * PDF Builder Pro - Templates AJAX Handler
 * Gestion des actions AJAX pour les templates prédéfinis
 */

namespace PDF_Builder\AJAX;

use Exception;
use PDF_Builder_Logger;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

class PdfBuilderTemplatesAjax
{
    public function __construct()
    {
        // Actions pour les templates prédéfinis - géré dans predefined-templates-manager.php
        \add_action('wp_ajax_pdf_builder_create_from_predefined', array($this, 'createFromPredefined'));
        \add_action('wp_ajax_pdf_builder_load_predefined_into_editor', array($this, 'loadPredefinedIntoEditor'));
        // Actions pour les templates personnalisés
        \add_action('wp_ajax_pdf_builder_load_template_settings', array($this, 'loadTemplateSettings'));
        \add_action('wp_ajax_pdf_builder_save_template_settings', array($this, 'saveTemplateSettings'));
        \add_action('wp_ajax_pdf_builder_set_default_template', array($this, 'setDefaultTemplate'));
        \add_action('wp_ajax_pdf_builder_delete_template', array($this, 'deleteTemplate'));
        \add_action('wp_ajax_pdf_builder_save_order_status_templates', array($this, 'saveOrderStatusTemplates'));
    }

    /**
     * Charge un template prédéfini depuis le fichier JSON
     */
    public function loadPredefinedTemplate()
    {
        try {
// Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_predefined_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_slug = \sanitize_text_field($_POST['template_slug'] ?? '');
            if (empty($template_slug)) {
                \wp_send_json_error('Slug du template manquant');
            }

            // Chemin vers le dossier des templates prédéfinis
            $predefined_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/predefined/';
            $template_file = $predefined_dir . $template_slug . '.json';
            if (!file_exists($template_file)) {
                \wp_send_json_error('Template prédéfini non trouvé');
            }

            // Charger le contenu du template
            $content = file_get_contents($template_file);
            $template_data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \wp_send_json_error('Erreur lors du décodage du JSON du template');
            }

            \wp_send_json_success(array(
                'template' => $template_data,
                'slug' => $template_slug
            ));
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors du chargement du template: ' . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau template personnalisé à partir d'un template prédéfini
     */
    public function createFromPredefined()
    {
        try {
// Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_slug = \sanitize_text_field($_POST['template_slug'] ?? '');
            $template_name = \sanitize_text_field($_POST['template_name'] ?? '');
            if (empty($template_slug)) {
                \wp_send_json_error('Slug du template manquant');
            }

            if (empty($template_name)) {
                \wp_send_json_error('Nom du template manquant');
            }

            // Charger d'abord le template prédéfini
            $predefined_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/predefined/';
            $template_file = $predefined_dir . $template_slug . '.json';
            if (!file_exists($template_file)) {
                \wp_send_json_error('Template prédéfini non trouvé');
            }

            $content = file_get_contents($template_file);
            $template_data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \wp_send_json_error('Erreur lors du décodage du JSON du template');
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
                    'user_id' => get_current_user_id(),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'is_default' => 0
                ), array('%s', '%s', '%d', '%s', '%s', '%d'));
            if ($result === false) {
                \wp_send_json_error('Erreur lors de la création du template dans la base de données');
            }

            $new_template_id = $wpdb->insert_id;
            \wp_send_json_success(array(
                'template_id' => 1,
                'message' => 'Redirection vers l\'éditeur unique',
                'redirect_url' => admin_url('admin.php?page=pdf-builder-react-editor')
            ));
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors de la création du template: ' . $e->getMessage());
        }
    }

    /**
     * Charge un modèle prédéfini directement dans le template ID 1
     */
    public function loadPredefinedIntoEditor()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_slug = \sanitize_text_field($_POST['template_slug'] ?? '');
            if (empty($template_slug)) {
                \wp_send_json_error('Slug du modèle prédéfini manquant');
            }

            // Charger le modèle prédéfini depuis le fichier
            $predefined_dir = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/predefined/';
            $template_file = $predefined_dir . $template_slug . '.json';
            if (!file_exists($template_file)) {
                \wp_send_json_error('Modèle prédéfini introuvable');
            }

            $content = file_get_contents($template_file);
            $predefined_data = json_decode($content, true);
            if (!$predefined_data || !isset($predefined_data['elements'])) {
                \wp_send_json_error('Format du modèle prédéfini invalide');
            }

            // Préparer les données du template
            $template_data = [
                'name' => $predefined_data['name'] ?? 'Template depuis modèle prédéfini',
                'elements' => $predefined_data['elements'],
                'canvasWidth' => $predefined_data['canvasWidth'] ?? 794,
                'canvasHeight' => $predefined_data['canvasHeight'] ?? 1123,
                'canvas_settings' => [
                    'width' => $predefined_data['canvasWidth'] ?? 794,
                    'height' => $predefined_data['canvasHeight'] ?? 1123,
                    'background_color' => $predefined_data['canvas_settings']['background_color'] ?? '#ffffff'
                ],
                'version' => $predefined_data['version'] ?? '1.0',
                'last_modified' => current_time('mysql'),
                'is_from_predefined' => true,
                'predefined_slug' => $template_slug
            ];

            // Créer un NOUVEAU template au lieu de mettre à jour l'ID 1
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            
            $result = $wpdb->insert($table_templates, [
                'name' => $template_data['name'],
                'template_data' => wp_json_encode($template_data),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'is_default' => 0
            ]);

            if ($result === false) {
                \wp_send_json_error('Erreur lors de la création du template');
            }

            // Récupérer l'ID du template créé
            $template_id = $wpdb->insert_id;

            \wp_send_json_success([
                'message' => 'Modèle prédéfini chargé avec succès',
                'template_id' => $template_id,
                'redirect_url' => admin_url('admin.php?page=pdf-builder-react-editor&template_id=' . $template_id)
            ]);
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors du chargement du modèle prédéfini: ' . $e->getMessage());
        }
    }

    public function loadTemplateSettings()
    {
        try {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: loadTemplateSettings called with POST: ' . print_r($_POST, true)); }
// Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            if (empty($template_id)) {
                \wp_send_json_error('ID du template manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
// Récupérer le template
            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            if (!$template) {
                \wp_send_json_error('Template non trouvé');
            }

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: Template found: ' . print_r($template, true)); }

            // Extraire les informations depuis template_data si elles existent
            $template_data = json_decode($template['template_data'] ?? '{}', true);
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: Template data decoded: ' . print_r($template_data, true)); }

            // Si description ou category ne sont pas dans template_data, essayer de les deviner
            $description = $template_data['description'] ?? '';
            $category = $template_data['category'] ?? 'autre';

            // Si pas de description, en créer une par défaut basée sur le nom
            if (empty($description)) {
                $template_name_lower = strtolower($template['name']);
                if (strpos($template_name_lower, 'facture') !== false || strpos($template_name_lower, 'invoice') !== false) {
                    $description = 'Template de facture personnalisé';
                    $category = 'facture';
                } elseif (strpos($template_name_lower, 'devis') !== false || strpos($template_name_lower, 'quote') !== false) {
                    $description = 'Template de devis personnalisé';
                    $category = 'devis';
                } elseif (strpos($template_name_lower, 'commande') !== false || strpos($template_name_lower, 'order') !== false) {
                    $description = 'Template de commande personnalisé';
                    $category = 'commande';
                } elseif (strpos($template_name_lower, 'contrat') !== false || strpos($template_name_lower, 'contract') !== false) {
                    $description = 'Template de contrat personnalisé';
                    $category = 'contrat';
                } elseif (strpos($template_name_lower, 'newsletter') !== false) {
                    $description = 'Template de newsletter personnalisé';
                    $category = 'newsletter';
                } else {
                    $description = 'Template personnalisé';
                    $category = 'autre';
                }
            }

            // Récupérer les paramètres du canvas pour les options disponibles
            $canvas_manager = \PDF_Builder\Canvas\Canvas_Manager::getInstance();
            $canvas_settings = $canvas_manager->getAllSettings();

            $settings = array(
                'id' => $template['id'],
                'name' => $template['name'],
                'description' => $description,
                'category' => $category,
                'is_default' => $template['is_default'],
                'created_at' => $template['created_at'],
                'updated_at' => $template['updated_at'],
                'template_data' => $template_data,
                // Paramètres du canvas disponibles
                'canvas_settings' => array(
                    'default_canvas_format' => $canvas_settings['default_canvas_format'] ?? 'A4',
                    'default_canvas_orientation' => $canvas_settings['default_canvas_orientation'] ?? 'portrait',
                    'default_canvas_dpi' => $canvas_settings['default_canvas_dpi'] ?? 96,
                    'available_formats' => ['A3', 'A4', 'A5', 'Letter', 'Legal'],
                    'available_orientations' => ['portrait', 'landscape'],
                    'available_dpi' => [72, 96, 150, 300, 600]
                )
            );

            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: Settings to return: ' . print_r($settings, true)); }
            \wp_send_json_success(array('template' => $settings));
        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder: Exception in loadTemplateSettings: ' . $e->getMessage()); }
            \wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
        }
    }

    /**
     * Définit un template comme template par défaut
     */
    public function setDefaultTemplate()
    {
        try {
// Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            $is_default = intval($_POST['is_default'] ?? 0);
            if (empty($template_id)) {
                \wp_send_json_error('ID du template manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
// Vérifier que le template existe
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                \wp_send_json_error('Template non trouvé');
            }

            // Si on définit comme défaut, retirer le statut par défaut des autres templates
            if ($is_default) {
                $wpdb->update($table_templates, array('is_default' => 0), array('is_default' => 1), array('%d'), array('%d'));
            }

            // Mettre à jour le statut du template
            $result = $wpdb->update($table_templates, array('is_default' => $is_default), array('id' => $template_id), array('%d'), array('%d'));
            if ($result === false) {
                \wp_send_json_error('Erreur lors de la mise à jour du statut par défaut');
            }

            $message = $is_default ? 'Template défini comme par défaut' : 'Statut par défaut retiré';
            \wp_send_json_success(array(
                'message' => $message
            ));
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors de la modification du statut: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarde les paramètres d'un template
     */
    public function saveTemplateSettings()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            $template_name = \sanitize_text_field($_POST['template_name'] ?? '');
            $template_description = \sanitize_text_field($_POST['template_description'] ?? '');
            $template_category = \sanitize_text_field($_POST['template_category'] ?? 'autre');
            $is_default = intval($_POST['is_default'] ?? 0);
            
            // Nouveaux paramètres canvas
            $canvas_format = \sanitize_text_field($_POST['canvas_format'] ?? 'A4');
            $canvas_orientation = \sanitize_text_field($_POST['canvas_orientation'] ?? 'portrait');
            $canvas_dpi = intval($_POST['canvas_dpi'] ?? 96);

            if (empty($template_id) || empty($template_name)) {
                \wp_send_json_error('ID du template ou nom manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Récupérer les données actuelles du template
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                \wp_send_json_error('Template non trouvé');
            }

            // Décoder les données JSON actuelles
            $template_data = json_decode($existing->template_data ?? '{}', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $template_data = array();
            }

            // Mettre à jour les données du template avec les nouveaux paramètres canvas
            $template_data['category'] = $template_category;
            $template_data['canvas_format'] = $canvas_format;
            $template_data['canvas_orientation'] = $canvas_orientation;
            $template_data['canvas_dpi'] = $canvas_dpi;

            // Si on définit comme défaut, retirer le statut par défaut des autres templates
            if ($is_default) {
                $wpdb->update($table_templates, array('is_default' => 0), array('is_default' => 1), array('%d'), array('%d'));
            }

            // Mettre à jour le template
            $result = $wpdb->update(
                $table_templates,
                array(
                    'name' => $template_name,
                    'template_data' => wp_json_encode($template_data),
                    'is_default' => $is_default,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $template_id),
                array('%s', '%s', '%d', '%s'),
                array('%d')
            );

            if ($result === false) {
                \wp_send_json_error('Erreur lors de la sauvegarde des paramètres du template');
            }

            \wp_send_json_success(array(
                'message' => 'Paramètres du template sauvegardés avec succès',
                'template_id' => $template_id
            ));

        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * Supprime un template
     */
    public function deleteTemplate()
    {
        // Log immédiat pour vérifier que la fonction est appelée
        error_log('[PDF Builder] deleteTemplate() appelée avec POST: ' . print_r($_POST, true));

        try {
            error_log('[PDF Builder] deleteTemplate() - Après log initial, user logged in: ' . (is_user_logged_in() ? 'YES' : 'NO') . ', current_user_id: ' . get_current_user_id());

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->debug_log("DELETE_TEMPLATE_START - Début de la suppression du template - template_id: " . ($_POST['template_id'] ?? 'not_set') . ", nonce: " . ($_POST['nonce'] ?? 'not_set') . ", user_can: " . (current_user_can('manage_options') ? 'yes' : 'no'));
            }

            error_log('[PDF Builder] deleteTemplate() - Après PDF_Builder_Logger check');

// Vérification des permissions - permettre aux utilisateurs connectés de supprimer leurs templates
            if (!is_user_logged_in()) {
                error_log('[PDF Builder] deleteTemplate() - Utilisateur NON connecté');
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_ERROR - Utilisateur non connecté");
                }
                \wp_send_json_error('Utilisateur non connecté');
            }

            error_log('[PDF Builder] deleteTemplate() - Utilisateur connecté, vérification nonce');

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                error_log('[PDF Builder] deleteTemplate() - Nonce invalide: ' . ($_POST['nonce'] ?? 'not_set'));
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_ERROR - Nonce invalide - received: " . ($_POST['nonce'] ?? 'not_set'));
                }
                \wp_send_json_error('Nonce invalide');
            }

            error_log('[PDF Builder] deleteTemplate() - Nonce valide, récupération template_id');

            $template_id = intval($_POST['template_id'] ?? 0);
            if (empty($template_id)) {
                error_log('[PDF Builder] deleteTemplate() - Template ID manquant');
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_ERROR - ID du template manquant");
                }
                \wp_send_json_error('ID du template manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            error_log('[PDF Builder] deleteTemplate() - Table: ' . $table_templates . ', Template ID: ' . $template_id);
// Vérifier que le template existe et appartient à l'utilisateur actuel
            $template = $wpdb->get_row($wpdb->prepare("SELECT id, name, user_id FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            error_log('[PDF Builder] deleteTemplate() - Résultat de la requête: ' . print_r($template, true));
            if (!$template) {
                error_log('[PDF Builder] deleteTemplate() - Template non trouvé dans la base');
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_ERROR - Template non trouvé - template_id: $template_id");
                }
                \wp_send_json_error('Template non trouvé');
            }

            error_log('[PDF Builder] deleteTemplate() - Template trouvé: ' . $template['name'] . ', user_id: ' . $template['user_id']);

// Vérifier que l'utilisateur est propriétaire du template ou admin
            $current_user_id = get_current_user_id();
            error_log('[PDF Builder] deleteTemplate() - Current user ID: ' . $current_user_id . ', Template user ID: ' . $template['user_id'] . ', is_admin: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
            if ($template['user_id'] != $current_user_id && !current_user_can('manage_options')) {
                error_log('[PDF Builder] deleteTemplate() - Permissions insuffisantes');
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_ERROR - Permissions insuffisantes - template_user_id: {$template['user_id']}, current_user_id: $current_user_id");
                }
                \wp_send_json_error('Permissions insuffisantes');
            }

            error_log('[PDF Builder] deleteTemplate() - Permissions OK, suppression du template');

            $template_name = $template['name'];

// Supprimer le template
            $result = $wpdb->delete($table_templates, array('id' => $template_id), array('%d'));
            error_log('[PDF Builder] deleteTemplate() - Résultat de la suppression: ' . ($result === false ? 'FALSE' : $result));
            if ($result === false) {
                error_log('[PDF Builder] deleteTemplate() - Erreur suppression: ' . $wpdb->last_error);
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_ERROR - Erreur lors de la suppression du template - template_id: $template_id, db_error: " . $wpdb->last_error);
                }
                \wp_send_json_error('Erreur lors de la suppression du template');
            }

            error_log('[PDF Builder] deleteTemplate() - Template supprimé avec succès');

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info_log("DELETE_TEMPLATE_SUCCESS - Template supprimé avec succès - template_id: $template_id, template_name: $template_name");
            }
// Déclencher le hook de suppression de template
            \do_action('pdf_builder_template_deleted', $template_id, $template_name ?: 'Template #' . $template_id);
            \wp_send_json_success(array(
                'message' => 'Template supprimé avec succès'
            ));
        } catch (Exception $e) {
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->error_log("DELETE_TEMPLATE_EXCEPTION - Exception lors de la suppression - template_id: " . ($_POST['template_id'] ?? 'not_set') . ", error: " . $e->getMessage());
            }
            \wp_send_json_error('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Duplique un template existant
     */
    public function duplicateTemplate()
    {
        try {
// Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            $template_name = \sanitize_text_field($_POST['template_name'] ?? '');
            if (empty($template_id) || empty($template_name)) {
                \wp_send_json_error('ID du template ou nom manquant');
            }

            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
// Vérifier que le template existe
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id));
            if (!$existing) {
                \wp_send_json_error('Template non trouvé');
                return;
            }

            // Créer une copie du template
            $result = $wpdb->insert($table_templates, array(
                    'name' => $template_name,
                    'template_data' => $existing->template_data,
                    'user_id' => get_current_user_id(),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'is_default' => 0
                ), array('%s', '%s', '%d', '%s', '%s', '%d'));
            if ($result === false) {
                \wp_send_json_error('Erreur lors de la duplication du template');
            }

            $new_template_id = $wpdb->insert_id;
            \wp_send_json_success(array(
                'message' => 'Template dupliqué avec succès',
                'template_id' => $new_template_id
            ));
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors de la duplication: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarde les mappings des templates par statut de commande
     */
    public function saveOrderStatusTemplates()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            if (!\wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
                \wp_send_json_error('Nonce invalide');
            }

            // Récupérer les données JSON
            $templates_data_json = $_POST['templates_data'] ?? '';
            if (empty($templates_data_json)) {
                \wp_send_json_error('Données des templates manquantes');
            }

            // Décoder les données JSON
            $templates_data = json_decode(stripslashes($templates_data_json), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \wp_send_json_error('Erreur lors du décodage des données JSON');
            }

            // Valider et nettoyer les données
            $clean_data = array();
            foreach ($templates_data as $status => $template_id) {
                if (!empty($template_id) && is_numeric($template_id)) {
                    $clean_data[$status] = intval($template_id);
                }
            }

            // Sauvegarder dans les options WordPress
            pdf_builder_update_option('pdf_builder_order_status_templates', $clean_data);

            \wp_send_json_success(array(
                'message' => 'Mappings des templates sauvegardés avec succès',
                'saved_data' => $clean_data
            ));

        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }
}

// Initialiser le handler AJAX
new PdfBuilderTemplatesAjax();




