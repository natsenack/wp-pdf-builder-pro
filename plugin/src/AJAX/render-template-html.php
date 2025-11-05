<?php
/**
 * PDF Builder Pro - AJAX Handler for render_template_html
 * Retourne le HTML du canvas avec le template rendu via SVG
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Gestionnaire AJAX pour rendre un template en HTML pour le canvas
 * Au lieu de laisser React rendre, on retourne le SVG/HTML déjà généré
 */
function pdf_builder_ajax_render_template_html_handler() {
    try {
        // Vérification des permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification du nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        // Récupération du template_id ou template_data
        $template_id = isset($_POST['template_id']) ? sanitize_text_field($_POST['template_id']) : '';
        
        if (empty($template_id)) {
            wp_send_json_error('ID template manquant');
        }

        // Valider l'ID du template (builtin ou custom)
        if (!preg_match('/^[a-z0-9_-]+$/i', $template_id)) {
            wp_send_json_error('ID template invalide');
        }

        // Chercher le fichier SVG preview builtin
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__, 3)));
        }

        $svg_file = PDF_BUILDER_PLUGIN_DIR . 'assets/images/templates/' . $template_id . '-preview.svg';

        // Vérifier que le fichier SVG existe et est valide
        if (!file_exists($svg_file)) {
            wp_send_json_error('Fichier SVG non trouvé pour le template: ' . $template_id);
        }

        // Vérifier que le chemin ne s'échappe pas
        $real_path = realpath($svg_file);
        $allowed_dir = realpath(PDF_BUILDER_PLUGIN_DIR . 'assets/images/templates');
        if (!$real_path || strpos($real_path, $allowed_dir) !== 0) {
            wp_send_json_error('Accès au fichier refusé');
        }

        // Lire le contenu du SVG
        $svg_content = file_get_contents($svg_file);
        if ($svg_content === false) {
            wp_send_json_error('Erreur lors de la lecture du fichier SVG');
        }

        // Retourner le SVG comme HTML pour le canvas
        wp_send_json_success([
            'html' => $svg_content,
            'format' => 'svg',
            'template_id' => $template_id,
            'success' => true
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}

add_action('wp_ajax_pdf_builder_render_template_html', 'pdf_builder_ajax_render_template_html_handler');
