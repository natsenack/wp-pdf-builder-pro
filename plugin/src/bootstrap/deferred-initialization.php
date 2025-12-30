<?php
/**
 * PDF Builder Pro - Deferred Initialization Module
 * Initialisation différée après chargement de WordPress
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// INITIALISATION DIFFÉRÉE - UNIQUEMENT APRÈS QUE WORDPRESS SOIT CHARGÉ
// ============================================================================

// Tous les hooks et initialisations sont maintenant différés jusqu'à ce que WordPress soit prêt
if (function_exists('add_action')) {
    // Initialiser l'Onboarding Manager une fois WordPress chargé
    add_action('plugins_loaded', function() {
        // Charger les utilitaires d'urgence si nécessaire
        pdf_builder_load_utilities_emergency();

        // Créer les classes d'alias pour la compatibilité
        if (!class_exists('PDF_Builder_Onboarding_Manager_Standalone')) {
            // Créer une version standalone de la classe pour les cas d'urgence
            class PDF_Builder_Onboarding_Manager_Standalone {
                private static $instance = null;

                public static function get_instance() {
                    if (self::$instance === null) {
                        self::$instance = new self();
                    }
                    return self::$instance;
                }

                public function __construct() {
                    // Constructeur minimal pour compatibilité
                }

                // Méthodes minimales pour éviter les erreurs
                public function check_onboarding_status() { return false; }
                public function ajax_complete_onboarding_step() { return false; }
                public function ajax_skip_onboarding() { return false; }
                public function ajax_reset_onboarding() { return false; }
                public function ajax_load_onboarding_step() { return false; }
                public function ajax_save_template_selection() { return false; }
                public function ajax_save_freemium_mode() { return false; }
                public function ajax_update_onboarding_step() { return false; }
                public function ajax_save_template_assignment() { return false; }
                public function ajax_mark_onboarding_complete() { return false; }
            }
        }

        // Maintenant charger la vraie classe si possible
        if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
            $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
            if (file_exists($onboarding_path)) {
                require_once $onboarding_path;
            } else {
                // error_log('PDF Builder: Fichier Onboarding Manager introuvable: ' . $onboarding_path);
            }
        }

        // Alias pour compatibilité - utiliser la vraie classe si disponible, sinon la standalone
        if (!class_exists('PDF_Builder_Onboarding_Manager_Alias')) {
            if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
                class PDF_Builder_Onboarding_Manager_Alias extends PDF_Builder\Utilities\PDF_Builder_Onboarding_Manager {}
            } else {
                class PDF_Builder_Onboarding_Manager_Alias extends PDF_Builder_Onboarding_Manager_Standalone {}
            }
        }

        // Vérification finale et création de l'instance
        if (!class_exists('PDF_Builder_Onboarding_Manager')) {
            class_alias('PDF_Builder_Onboarding_Manager_Alias', 'PDF_Builder_Onboarding_Manager');

            // Créer l'instance maintenant que WordPress est chargé
            try {
                PDF_Builder_Onboarding_Manager_Alias::get_instance();
            } catch (Exception $e) {
                // error_log('PDF Builder: Erreur lors de la création de l\'instance Onboarding Manager: ' . $e->getMessage());
            }
        }
    }, 0);

    // Initialiser l'API Preview après que WordPress soit chargé
    add_action('init', function() {
        if (class_exists('\\PDF_Builder\\Api\\PreviewImageAPI')) {
            new \PDF_Builder\Api\PreviewImageAPI();
        }
    });