<?php

/**
 * PDF Builder Pro - AJAX Handler for render_template_html
 * Retourne le SVG rendu du template pour affichage dans le canvas React
 * Utilise le même générateur SVG que la prévisualisation
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Gestionnaire AJAX pour rendre un template en SVG pour le canvas
 */
function pdf_builder_ajax_render_template_html_handler()
{

    try {
// Vérification des permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification du nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        // Récupération des données du template
        $template_data_json = isset($_POST['template_data']) ? wp_unslash($_POST['template_data']) : '';
        if (empty($template_data_json)) {
            wp_send_json_error('Données du template manquantes');
        }

        $template_data = json_decode($template_data_json, true);
        if ($template_data === null) {
            wp_send_json_error('JSON invalide');
        }

        // Charger le générateur SVG
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__, 3)));
        }

        $svg_generator_file = PDF_BUILDER_PLUGIN_DIR . 'generate-svg-preview.php';
        if (!file_exists($svg_generator_file)) {
            wp_send_json_error('Générateur SVG non trouvé');
        }

        // Charger la classe
        require_once $svg_generator_file;
        try {
        // Créer une instance avec les données du template
            // La classe s'attend à un fichier JSON, donc on crée un wrapper
            $temp_file = sys_get_temp_dir() . '/template_' . uniqid() . '.json';
            file_put_contents($temp_file, json_encode($template_data));
        // Générer le SVG
            $generator = new SVGPreviewGeneratorHonest($temp_file);
            $svg_content = $generator->generateSVG();
        // Nettoyer
            unlink($temp_file);
        // Retourner le SVG
            wp_send_json_success([
                'html' => $svg_content,
                'format' => 'svg',
                'success' => true
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Erreur génération SVG: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}

add_action('wp_ajax_pdf_builder_render_template_html', 'pdf_builder_ajax_render_template_html_handler');
