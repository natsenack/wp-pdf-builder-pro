<?php
namespace WP_PDF_Builder_Pro\Admin;

use WP_PDF_Builder_Pro\Canvas\Canvas_Manager;

/**
 * Canvas AJAX Handlers
 * Gère les requêtes AJAX pour les paramètres du canvas
 * 
 * @package WP_PDF_Builder_Pro
 * @since 1.1.0
 */
class Canvas_AJAX_Handler {

    /**
     * Enregistre les handlers AJAX
     */
    public static function register_hooks() {
        add_action('wp_ajax_pdf_builder_get_canvas_settings', [self::class, 'get_canvas_settings']);
        add_action('wp_ajax_pdf_builder_save_canvas_settings', [self::class, 'save_canvas_settings']);
        add_action('wp_ajax_pdf_builder_reset_canvas_settings', [self::class, 'reset_canvas_settings']);
    }

    /**
     * Récupère les paramètres du canvas
     */
    public static function get_canvas_settings() {
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Permissions insuffisantes', 'pdf-builder-pro')]);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
                wp_send_json_error(['message' => __('Nonce de sécurité invalide', 'pdf-builder-pro')]);
                return;
            }

            $canvas_manager = Canvas_Manager::get_instance();
            $settings = $canvas_manager->get_all_settings();

            wp_send_json_success([
                'settings' => $settings,
                'message' => __('Paramètres du canvas récupérés avec succès', 'pdf-builder-pro')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => sprintf(__('Erreur: %s', 'pdf-builder-pro'), $e->getMessage())
            ]);
        }
    }

    /**
     * Sauvegarde les paramètres du canvas
     */
    public static function save_canvas_settings() {
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Permissions insuffisantes', 'pdf-builder-pro')]);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
                wp_send_json_error(['message' => __('Nonce de sécurité invalide', 'pdf-builder-pro')]);
                return;
            }

            // Récupérer les paramètres
            $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : [];

            if (empty($settings)) {
                wp_send_json_error(['message' => __('Aucun paramètre à sauvegarder', 'pdf-builder-pro')]);
                return;
            }

            $canvas_manager = Canvas_Manager::get_instance();
            $saved = $canvas_manager->save_settings($settings);

            if ($saved) {
                wp_send_json_success([
                    'message' => __('Paramètres du canvas sauvegardés avec succès', 'pdf-builder-pro'),
                    'settings' => $canvas_manager->get_all_settings()
                ]);
            } else {
                wp_send_json_error([
                    'message' => __('Erreur lors de la sauvegarde des paramètres', 'pdf-builder-pro')
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => sprintf(__('Erreur: %s', 'pdf-builder-pro'), $e->getMessage())
            ]);
        }
    }

    /**
     * Réinitialise les paramètres du canvas aux valeurs par défaut
     */
    public static function reset_canvas_settings() {
        try {
            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Permissions insuffisantes', 'pdf-builder-pro')]);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
                wp_send_json_error(['message' => __('Nonce de sécurité invalide', 'pdf-builder-pro')]);
                return;
            }

            // Confirmer que l'utilisateur veut réinitialiser
            if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
                wp_send_json_error([
                    'message' => __('Action non confirmée', 'pdf-builder-pro')
                ]);
                return;
            }

            $canvas_manager = Canvas_Manager::get_instance();
            $canvas_manager->reset_to_defaults();

            wp_send_json_success([
                'message' => __('Paramètres du canvas réinitialisés aux valeurs par défaut', 'pdf-builder-pro'),
                'settings' => $canvas_manager->get_all_settings()
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => sprintf(__('Erreur: %s', 'pdf-builder-pro'), $e->getMessage())
            ]);
        }
    }
}
