<?php
// Définir ABSPATH pour contourner la protection
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

echo "Test de la fonction AJAX directement:\n";
try {
    require_once 'settings-ajax.php';
    if (function_exists('pdf_builder_run_settings_tests_ajax')) {
        echo "✅ Fonction pdf_builder_run_settings_tests_ajax existe\n";
    } else {
        echo "❌ Fonction pdf_builder_run_settings_tests_ajax n'existe pas\n";
    }
} catch (Exception $e) {
    echo '❌ Erreur: ' . $e->getMessage() . "\n";
}
?>