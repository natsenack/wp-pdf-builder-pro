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


    try {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {

            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates')) {

            wp_send_json_error('Sécurité: Nonce invalide');
        }



        // Charger et instancier le Template Manager directement
        // Utiliser dirname(__FILE__) pour obtenir le chemin absolu
        // __FILE__ = /path/to/wp-pdf-builder-pro/plugin/src/AJAX/get-builtin-templates.php
        // Nous devons remonter 2 niveaux pour arriver à wp-pdf-builder-pro/plugin/
        $ajax_file = __FILE__;
        $ajax_dir = dirname($ajax_file);           // /path/to/wp-pdf-builder-pro/plugin/src/AJAX
        $src_dir = dirname($ajax_dir);              // /path/to/wp-pdf-builder-pro/plugin/src
        $template_manager_file = $src_dir . '/Managers/PDF_Builder_Template_Manager.php';
        



        if (!file_exists($template_manager_file)) {

            wp_send_json_error('Erreur interne: Fichier Template Manager non trouvé');
        }

        // Définir la constante si elle n'existe pas
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            // Calculer le plugin dir depuis le chemin actuel
            // __FILE__ = /path/to/.../plugin/src/AJAX/get-builtin-templates.php
            // dirname(__FILE__) = /path/to/.../plugin/src/AJAX
            // dirname(dirname(dirname(__FILE__))) = /path/to/.../plugin
            $plugin_root = dirname(dirname(dirname($ajax_file)));
            define('PDF_BUILDER_PLUGIN_DIR', $plugin_root . '/');

        }

        // Définir PDF_BUILDER_PLUGIN_URL si elle n'existe pas
        if (!defined('PDF_BUILDER_PLUGIN_URL')) {
            // Calculer le plugin URL en utilisant le chemin du plugin
            $plugin_file = $plugin_root . '/pdf-builder-pro.php';
            define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url($plugin_file));

        }

        require_once $template_manager_file;

        if (!class_exists('PDF_Builder_Template_Manager')) {

            wp_send_json_error('Erreur interne: Classe Template Manager non disponible');
        }


        $template_manager = new PDF_Builder_Template_Manager(null);



        // Récupérer les templates builtin
        $templates = $template_manager->get_builtin_templates();


        if (!is_array($templates)) {

            wp_send_json_error('Erreur interne: Templates non valides');
        }

        // Ajouter l'URL de prévisualisation à chaque template
        foreach ($templates as &$template) {
            $template['preview_url'] = $template_manager->get_template_preview_url($template['id']);

        }



        wp_send_json_success([
            'templates' => $templates
        ]);

    } catch (Exception $e) {


        wp_send_json_error('Erreur interne: ' . $e->getMessage());
    }
}

// Enregistrer le hook AJAX directement
// Désactivé car conflit avec la fonction dans bootstrap.php
// add_action('wp_ajax_get_builtin_templates', 'pdf_builder_ajax_get_builtin_templates_handler');
