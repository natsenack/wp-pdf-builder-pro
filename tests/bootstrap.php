<?php

/**
 * Bootstrap pour les tests PHPUnit de PDF Builder Pro
 */

// Définir les constantes WordPress de base pour les tests
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

// Simuler les fonctions WordPress de base nécessaires
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($path) {
        return mkdir($path, 0755, true);
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return true; // Pour les tests, toujours valider
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($text) {
        return filter_var($text, FILTER_SANITIZE_STRING);
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Pour les tests, toujours autoriser
    }
}

// Définir les constantes du plugin
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', __DIR__ . '/plugin/');
}

// Inclure l'autoloader du plugin si disponible
$autoloader = __DIR__ . '/plugin/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Inclure les fichiers du plugin nécessaires pour les tests
require_once __DIR__ . '/plugin/src/utilities/ImageConverter.php';

// Fonction utilitaire pour nettoyer les fichiers temporaires après les tests
function cleanup_temp_files() {
    $temp_dir = sys_get_temp_dir();
    $files = glob($temp_dir . '/pdf_preview_*.pdf');

    foreach ($files as $file) {
        if (file_exists($file) && filemtime($file) < time() - 3600) { // Plus vieux qu'1 heure
            unlink($file);
        }
    }
}

// Nettoyer les fichiers temporaires au démarrage des tests
cleanup_temp_files();