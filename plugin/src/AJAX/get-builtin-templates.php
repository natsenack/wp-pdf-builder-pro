<?php
/**
 * PDF Builder Pro - AJAX Handler for get_builtin_templates
 * Direct AJAX handler outside of class structure
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Gestionnaire AJAX pour récupérer les templates builtin
 * Directement enregistré sans passer par la classe Admin
 */
function pdf_builder_ajax_get_builtin_templates_handler() {
    error_log("PDF Builder AJAX Handler - get_builtin_templates called directly");

    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('PDF Builder AJAX Handler - Permissions insuffisantes');
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates')) {
            error_log('PDF Builder AJAX Handler - Nonce invalide');
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        error_log('PDF Builder AJAX Handler - Permissions et nonce OK');

        // Charger et instancier le Template Manager directement
        // Utiliser dirname(__FILE__) pour obtenir le chemin absolu
        // __FILE__ = /path/to/wp-pdf-builder-pro/plugin/src/AJAX/get-builtin-templates.php
        // Nous devons remonter 2 niveaux pour arriver à wp-pdf-builder-pro/plugin/
        $ajax_file = __FILE__;
        $ajax_dir = dirname($ajax_file);           // /path/to/wp-pdf-builder-pro/plugin/src/AJAX
        $src_dir = dirname($ajax_dir);              // /path/to/wp-pdf-builder-pro/plugin/src
        $template_manager_file = $src_dir . '/Managers/PDF_Builder_Template_Manager.php';
        
        error_log('PDF Builder AJAX Handler - Ajax file: ' . $ajax_file);
        error_log('PDF Builder AJAX Handler - Loading template manager from: ' . $template_manager_file);

        if (!file_exists($template_manager_file)) {
            error_log('PDF Builder AJAX Handler - Template manager file not found');
            wp_send_json_error('Erreur interne: Fichier Template Manager non trouvé');
        }

        require_once $template_manager_file;

        if (!class_exists('PDF_Builder_Template_Manager')) {
            error_log('PDF Builder AJAX Handler - Template Manager class does not exist after loading');
            wp_send_json_error('Erreur interne: Classe Template Manager non disponible');
        }

        error_log('PDF Builder AJAX Handler - Creating Template Manager instance');
        $template_manager = new PDF_Builder_Template_Manager(null);

        error_log('PDF Builder AJAX Handler - Template Manager instance created: ' . (is_object($template_manager) ? 'success' : 'failed'));

        // Récupérer les templates builtin
        $templates = $template_manager->get_builtin_templates();
        error_log('PDF Builder AJAX Handler - Templates retrieved: ' . (is_array($templates) ? count($templates) : 'not array'));

        if (!is_array($templates)) {
            error_log('PDF Builder AJAX Handler - Templates is not an array');
            wp_send_json_error('Erreur interne: Templates non valides');
        }

        // Ajouter l'URL de prévisualisation à chaque template
        foreach ($templates as &$template) {
            $template['preview_url'] = $template_manager->get_template_preview_url($template['id']);
            error_log('PDF Builder AJAX Handler - Template ' . $template['id'] . ' preview URL: ' . $template['preview_url']);
        }

        error_log('PDF Builder AJAX Handler - Sending success response with ' . count($templates) . ' templates');

        wp_send_json_success([
            'templates' => $templates
        ]);

    } catch (Exception $e) {
        error_log('PDF Builder AJAX Handler - Exception: ' . $e->getMessage());
        error_log('PDF Builder AJAX Handler - Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Erreur interne: ' . $e->getMessage());
    }
}

// Enregistrer le hook AJAX directement
add_action('wp_ajax_get_builtin_templates', 'pdf_builder_ajax_get_builtin_templates_handler');
