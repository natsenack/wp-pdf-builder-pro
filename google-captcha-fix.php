<?php
/**
 * Correction pour le plugin Google Captcha - wp is not defined
 *
 * Ce fichier corrige l'erreur "wp is not defined" dans le plugin Google Captcha
 * en s'assurant que l'objet wp est disponible avant utilisation.
 */

// Fonction pour corriger le script Google Captcha
function fix_google_captcha_wp_object() {
    // Vérifier si nous sommes sur une page admin
    if (!is_admin()) {
        return;
    }

    // Ajouter un script qui définit wp si ce n'est pas déjà fait
    $script = "
    <script type='text/javascript'>
    // S'assurer que l'objet wp global est disponible
    if (typeof window.wp === 'undefined') {
        window.wp = {};
        console.log('PDF Builder Pro: Objet wp initialisé pour compatibilité');
    }
    </script>
    ";

    echo $script;
}

// Alternative: Modifier la dépendance du script Google Captcha
function fix_google_captcha_script_dependency() {
    // Désenregistrer le script problématique et le réenregistrer avec les bonnes dépendances
    global $wp_scripts;

    if (isset($wp_scripts->registered['google-captcha-general-script'])) {
        // Modifier les dépendances pour s'assurer que wp-api est chargé avant
        $wp_scripts->registered['google-captcha-general-script']->deps[] = 'wp-api';
    }
}

// Hooks pour appliquer les corrections
add_action('admin_head', 'fix_google_captcha_wp_object', 1);
add_action('wp_enqueue_scripts', 'fix_google_captcha_script_dependency', 1);
add_action('admin_enqueue_scripts', 'fix_google_captcha_script_dependency', 1);