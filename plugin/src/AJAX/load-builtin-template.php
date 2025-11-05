<?php
/**
 * PDF Builder Pro - AJAX Handler for load_builtin_template
 * Charge un template builtin depuis les fichiers JSON
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Gestionnaire AJAX pour charger un template builtin
 */
function pdf_builder_ajax_load_builtin_template_handler() {
    try {
        // Vérification des permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification du nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        // Récupération de l'ID du template
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';

        if (empty($template_id)) {
            wp_send_json_error('ID template manquant');
        }

        // Validé l'ID (seulement alphanumériques et tirets)
        if (!preg_match('/^[a-z0-9-]+$/i', $template_id)) {
            wp_send_json_error('ID template invalide');
        }

        // Charger et instancier le Template Manager
        $plugin_file = defined('PDF_BUILDER_PLUGIN_FILE') ? PDF_BUILDER_PLUGIN_FILE : __FILE__;
        $plugin_dir = dirname(dirname(dirname($plugin_file)));
        $src_dir = $plugin_dir . '/src';
        $template_manager_file = $src_dir . '/Managers/PDF_Builder_Template_Manager.php';

        if (!file_exists($template_manager_file)) {
            wp_send_json_error('Erreur interne: Fichier Template Manager non trouvé');
        }

        require_once $template_manager_file;

        if (!class_exists('PDF_Builder_Template_Manager')) {
            wp_send_json_error('Erreur interne: Classe Template Manager non disponible');
        }

        $template_manager = new PDF_Builder_Template_Manager(null);

        // Construire le chemin du fichier JSON
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__, 3)));
        }

        $template_file = PDF_BUILDER_PLUGIN_DIR . 'templates/builtin/' . $template_id . '.json';

        // Sécurité: vérifier que le fichier n'échappe pas du répertoire
        if (strpos(realpath($template_file), realpath(PDF_BUILDER_PLUGIN_DIR . 'templates/builtin')) !== 0) {
            wp_send_json_error('Accès au fichier refusé');
        }

        // Charger le fichier JSON
        if (!file_exists($template_file)) {
            wp_send_json_error('Template builtin non trouvé: ' . $template_id);
        }

        $template_content = file_get_contents($template_file);
        if ($template_content === false) {
            wp_send_json_error('Erreur lors de la lecture du fichier template');
        }

        $template_data = json_decode($template_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('JSON invalide: ' . json_last_error_msg());
        }

        // Valider la structure du template
        $validation_errors = $template_manager->validate_template_structure($template_data);
        if (!empty($validation_errors)) {
            // Ajouter les propriétés par défaut si manquantes
            if (!isset($template_data['canvasWidth'])) {
                $template_data['canvasWidth'] = 794;
            }
            if (!isset($template_data['canvasHeight'])) {
                $template_data['canvasHeight'] = 1123;
            }
            if (!isset($template_data['elements'])) {
                $template_data['elements'] = [];
            }
        }

        // Compter les éléments et types
        $element_count = isset($template_data['elements']) ? count($template_data['elements']) : 0;
        $element_types = [];
        
        foreach ($template_data['elements'] as $element) {
            $type = $element['type'] ?? 'unknown';
            $element_types[$type] = ($element_types[$type] ?? 0) + 1;
        }

        // Retourner le template
        wp_send_json_success([
            'template' => $template_data,
            'name' => $template_data['name'] ?? ucfirst($template_id),
            'id' => $template_id,
            'is_builtin' => true,
            'element_count' => $element_count,
            'element_types' => $element_types
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors du chargement du template: ' . $e->getMessage());
    }
}

// Enregistrer le hook AJAX
add_action('wp_ajax_pdf_builder_load_builtin_template', 'pdf_builder_ajax_load_builtin_template_handler');
