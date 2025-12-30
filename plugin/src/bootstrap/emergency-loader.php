<?php
/**
 * PDF Builder Pro - Emergency Loader Module
 * Fonctions d'urgence pour charger les utilitaires critiques
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// ✅ FONCTION DE CHARGEMENT D'URGENCE DES UTILITAIRES
// ============================================================================

/**
 * Fonction d'urgence pour charger les utilitaires si nécessaire
 * Peut être appelée depuis n'importe où pour garantir la disponibilité des classes
 */
function pdf_builder_load_utilities_emergency() {
    static $utilities_loaded = false;

    if ($utilities_loaded) {
        return;
    }

    $utilities = array(
        'PDF_Builder_Onboarding_Manager.php',
        'PDF_Builder_GDPR_Manager.php'
    );

    foreach ($utilities as $utility) {
        $utility_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/' . $utility;
        if (file_exists($utility_path) && !class_exists('PDF_Builder\\Utilities\\' . str_replace('.php', '', $utility))) {
            require_once $utility_path;
        }
    }

    $utilities_loaded = true;
}

// ============================================================================
// ✅ FONCTION GLOBALE DE VÉRIFICATION DE CLASSE
// ============================================================================

/**
 * Fonction globale pour vérifier et charger la classe Onboarding Manager
 * Peut être appelée depuis n'importe où dans le code
 */
function pdf_builder_ensure_onboarding_manager() {
    if (!class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
        pdf_builder_load_utilities_emergency();

        // Double vérification avec chargement manuel
        $onboarding_path = PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php';
        if (file_exists($onboarding_path)) {
            require_once $onboarding_path;
        }
    }

    return class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager');
}

/**
 * Fonction globale GARANTIE pour obtenir l'instance Onboarding Manager
 * Utilise la classe alias qui est toujours disponible
 */
function pdf_builder_get_onboarding_manager() {
    // Essayer d'abord la vraie classe
    if (class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager')) {
        return \PDF_Builder\Utilities\PDF_Builder_Onboarding_Manager::get_instance();
    }

    // Fallback vers la classe alias (toujours disponible)
    if (class_exists('PDF_Builder_Onboarding_Manager_Alias')) {
        return PDF_Builder_Onboarding_Manager_Alias::get_instance();
    }

    // Dernier recours - créer une instance standalone
    return PDF_Builder_Onboarding_Manager_Standalone::get_instance();
}

/**
 * Fonction de diagnostic pour l'Onboarding Manager
 * Affiche des informations de debug si la classe n'est pas trouvée
 */
function pdf_builder_diagnose_onboarding_manager() {
    $class_exists = class_exists('PDF_Builder\\Utilities\\PDF_Builder_Onboarding_Manager');
    $alias_exists = class_exists('PDF_Builder_Onboarding_Manager_Alias');
    $standalone_exists = class_exists('PDF_Builder_Onboarding_Manager_Standalone');
    $file_exists = file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php');

    $diagnostics = array(
        'Real class exists' => $class_exists ? 'YES' : 'NO',
        'Alias class exists' => $alias_exists ? 'YES' : 'NO',
        'Standalone class exists' => $standalone_exists ? 'YES' : 'NO',
        'File exists' => $file_exists ? 'YES' : 'NO',
        'Plugin directory' => PDF_BUILDER_PLUGIN_DIR,
        'File path' => PDF_BUILDER_PLUGIN_DIR . 'src/utilities/PDF_Builder_Onboarding_Manager.php'
    );

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[PDF Builder] Onboarding Manager Diagnostics: ' . json_encode($diagnostics));
    }

    return $diagnostics;
}