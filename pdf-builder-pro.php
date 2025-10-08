<?php
/**
 * Plugin Name: PDF Builder Pro - VERSION ZÉRO
 * Plugin URI: https://github.com/your-repo/pdf-builder-pro
 * Description: Version zéro - plugin vide qui ne fait absolument rien
 * Version: 1.0.0
 * Author: Natsenack
 * Author URI: https://github.com/your-profile
 * License: GPL v2 or later
 * Text Domain: pdf-builder-pro
 * Domain Path: /languages
 */

// SÉCURITÉ ABSOLUE : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// VERSION ZÉRO : AUCUNE FONCTIONNALITÉ
// Ce plugin ne fait absolument rien
// Il ne charge aucun fichier, n'utilise aucune fonction WordPress
// Il ne fait que se déclarer comme plugin

// Hook d'activation vide
register_activation_hook(__FILE__, 'pdf_builder_zero_activate');

// Hook de désactivation vide
register_deactivation_hook(__FILE__, 'pdf_builder_zero_deactivate');

// Fonction d'activation qui ne fait RIEN
function pdf_builder_zero_activate() {
    // RIEN - juste exister
}

// Fonction de désactivation qui ne fait RIEN
function pdf_builder_zero_deactivate() {
    // RIEN - juste exister
}

// AUCUNE AUTRE FONCTIONNALITÉ
// AUCUN CHARGEMENT DE FICHIER
// AUCUNE UTILISATION DE FONCTIONS WORDPRESS
// FIN DU PLUGIN ZÉRO