<?php

namespace PDF_Builder\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use PDF_Builder\Canvas\Canvas_Manager;

/**
 * Canvas AJAX Handlers
 * Gère les requêtes AJAX pour les paramètres du canvas
 *
 * @package PDF_Builder
 * @since 1.1.0
 */
class Canvas_AJAX_Handler
{
    /**
     * Enregistre les handlers AJAX
     */
    public static function register_hooks()
    {
        // REMOVED: pdf_builder_get_canvas_settings is now handled by PDF_Builder_Admin
        // REMOVED: pdf_builder_save_canvas_settings is now handled by AjaxHandler to avoid conflicts
        // add_action('wp_ajax_pdf_builder_save_canvas_settings', [self::class, 'save_canvas_settings']);
        add_action('wp_ajax_pdf_builder_reset_canvas_settings', [self::class, 'reset_canvas_settings']);
    }

    /**
     * Récupère les paramètres du canvas
     */
    public static function getCanvasSettings()
    {
        try {
// Vérifier les permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error(['message' => \__('Permissions insuffisantes', 'pdf-builder-pro')]);
                return;
            }

            // Vérifier le nonce
            if (!\pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
                \wp_send_json_error(['message' => \__('Nonce de sécurité invalide', 'pdf-builder-pro')]);
                return;
            }

            $canvas_manager = Canvas_Manager::get_instance();
            $settings = $canvas_manager->getAllSettings();
            \wp_send_json_success([
                'settings' => $settings,
                'message' => \__('Paramètres du canvas récupérés avec succès', 'pdf-builder-pro')
            ]);
        } catch (\Exception $e) {
            \wp_send_json_error([
                // translators: %s: exception error message
                'message' => sprintf(\__('Erreur: %s', 'pdf-builder-pro'), $e->getMessage())
            ]);
        }
    }

    /**
     * Sauvegarde les paramètres du canvas
     */
    public static function saveCanvasSettings()
    {
        try {
// Vérifier les permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error(['message' => \__('Permissions insuffisantes', 'pdf-builder-pro')]);
                return;
            }

            // Vérifier le nonce
            if (!\pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
                \wp_send_json_error(['message' => \__('Nonce de sécurité invalide', 'pdf-builder-pro')]);
                return;
            }

            // Récupérer les paramètres
            $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : [];
            if (empty($settings)) {
                \wp_send_json_error(['message' => \__('Aucun paramètre à sauvegarder', 'pdf-builder-pro')]);
                return;
            }

            $canvas_manager = Canvas_Manager::get_instance();
            /** @phpstan-ignore-next-line Canvas_Manager::save_settings() defined in stub */
            $saved = $canvas_manager->save_settings($settings);
            if ($saved) {
                \wp_send_json_success([
                    'message' => \__('Paramètres du canvas sauvegardés avec succès', 'pdf-builder-pro'),
                    'settings' => $canvas_manager->getAllSettings()
                ]);
            } else {
                \wp_send_json_error([
                    'message' => \__('Erreur lors de la sauvegarde des paramètres', 'pdf-builder-pro')
                ]);
            }
        } catch (\Exception $e) {
            \wp_send_json_error([
                // translators: %s: exception error message
                'message' => sprintf(\__('Erreur: %s', 'pdf-builder-pro'), $e->getMessage())
            ]);
        }
    }

    /**
     * Réinitialise les paramètres du canvas aux valeurs par défaut
     */
    public static function resetCanvasSettings()
    {
        try {
// Vérifier les permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error(['message' => \__('Permissions insuffisantes', 'pdf-builder-pro')]);
                return;
            }

            // Vérifier le nonce
            if (!\pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
                \wp_send_json_error(['message' => \__('Nonce de sécurité invalide', 'pdf-builder-pro')]);
                return;
            }

            // Confirmer que l'utilisateur veut réinitialiser
            if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
                \wp_send_json_error([
                    'message' => \__('Action non confirmée', 'pdf-builder-pro')
                ]);
                return;
            }

            $canvas_manager = Canvas_Manager::get_instance();
            /** @phpstan-ignore-next-line Canvas_Manager::reset_to_defaults() defined in stub */
            $canvas_manager->reset_to_defaults();
            \wp_send_json_success([
                'message' => \__('Paramètres du canvas réinitialisés aux valeurs par défaut', 'pdf-builder-pro'),
                'settings' => $canvas_manager->getAllSettings()
            ]);
        } catch (\Exception $e) {
            \wp_send_json_error([
                // translators: %s: exception error message
                'message' => sprintf(\__('Erreur: %s', 'pdf-builder-pro'), $e->getMessage())
            ]);
        }
    }
}





