<?php
// Simuler l'environnement WordPress minimal pour les tests
define('ABSPATH', __DIR__ . '/');

// Définir les constantes nécessaires
define('PDF_BUILDER_PLUGIN_DIR', __DIR__ . '/plugin/');
define('PDF_BUILDER_PLUGIN_URL', 'http://localhost/wp-pdf-builder-pro-V2/plugin/');
define('PDF_BUILDER_VERSION', '2.0.0');

echo "Test direct de PDFEditorPreferences\n";
echo "===================================\n";

// Charger seulement la classe sans bootstrap complet
require_once 'plugin/src/Core/PDFEditorPreferences.php';

if (class_exists('PDFEditorPreferences')) {
    echo "✓ Classe PDFEditorPreferences trouvée\n";

    // Tester la création d'instance (sans hooks WordPress)
    try {
        $instance = PDFEditorPreferences::get_instance();
        if (is_object($instance)) {
            echo "✓ Instance créée avec succès\n";

            // Tester les préférences par défaut
            $prefs = $instance->get_preferences();
            echo "✓ Préférences par défaut: " . count($prefs) . " éléments\n";
            echo "  Clés: " . implode(', ', array_keys($prefs)) . "\n";

        } else {
            echo "✗ Échec de création de l'instance\n";
        }
    } catch (Exception $e) {
        echo "✗ Erreur lors de la création: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Classe PDFEditorPreferences NON trouvée\n";
}

echo "\nTest terminé.\n";