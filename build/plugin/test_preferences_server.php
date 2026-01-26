<?php
/**
 * Test rapide du système de préférences PDF Editor
 * À exécuter sur le serveur pour vérifier le fonctionnement
 */

// Simuler un environnement WordPress minimal
define('ABSPATH', '/var/www/html/');
define('WP_DEBUG', true);

// Headers pour éviter les erreurs de cache
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

echo "=== TEST SYSTÈME DE PRÉFÉRENCES PDF EDITOR ===\n\n";

// Test 1: Vérifier que la classe existe
echo "1. Test de chargement de la classe:\n";
if (class_exists('PDFEditorPreferences')) {
    echo "   ✓ Classe PDFEditorPreferences trouvée\n";

    try {
        $instance = PDFEditorPreferences::get_instance();
        echo "   ✓ Instance créée avec succès\n";
    } catch (Exception $e) {
        echo "   ✗ Erreur lors de la création de l'instance: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ Classe PDFEditorPreferences NON trouvée\n";
}

// Test 2: Vérifier les fonctions globales
echo "\n2. Test des fonctions globales:\n";
if (function_exists('pdf_builder_get_user_preference')) {
    echo "   ✓ pdf_builder_get_user_preference() disponible\n";
} else {
    echo "   ✗ pdf_builder_get_user_preference() NON disponible\n";
}

if (function_exists('pdf_builder_set_user_preference')) {
    echo "   ✓ pdf_builder_set_user_preference() disponible\n";
} else {
    echo "   ✗ pdf_builder_set_user_preference() NON disponible\n";
}

if (function_exists('pdf_builder_get_all_user_preferences')) {
    echo "   ✓ pdf_builder_get_all_user_preferences() disponible\n";
} else {
    echo "   ✗ pdf_builder_get_all_user_preferences() NON disponible\n";
}

// Test 3: Vérifier que wp-preferences est désactivé
echo "\n3. Test de désactivation wp-preferences:\n";
global $wp_scripts;
if (isset($wp_scripts) && isset($wp_scripts->registered['wp-preferences'])) {
    echo "   ⚠ wp-preferences toujours enregistré\n";
} else {
    echo "   ✓ wp-preferences correctement désactivé\n";
}

// Test 4: Vérifier les handlers AJAX
echo "\n4. Test des handlers AJAX:\n";
if (has_action('wp_ajax_pdf_editor_save_preferences')) {
    echo "   ✓ Handler AJAX save_preferences enregistré\n";
} else {
    echo "   ✗ Handler AJAX save_preferences NON enregistré\n";
}

if (has_action('wp_ajax_pdf_editor_get_preferences')) {
    echo "   ✓ Handler AJAX get_preferences enregistré\n";
} else {
    echo "   ✗ Handler AJAX get_preferences NON enregistré\n";
}

echo "\n=== TEST TERMINÉ ===\n";
echo "Si tous les tests sont ✓, le système de préférences est opérationnel.\n";