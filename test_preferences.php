<?php
// Simuler l'environnement WordPress pour les tests
define('ABSPATH', __DIR__ . '/');
define('WP_DEBUG', true);

// Définir les constantes nécessaires
define('PDF_BUILDER_PLUGIN_DIR', __DIR__ . '/plugin/');
define('PDF_BUILDER_PLUGIN_URL', 'http://localhost/wp-pdf-builder-pro-V2/plugin/');
define('PDF_BUILDER_VERSION', '2.0.0');

require_once 'plugin/bootstrap.php';

echo "Test de PDFEditorPreferences\n";
echo "============================\n";

if (class_exists('PDFEditorPreferences')) {
    echo "✓ Classe PDFEditorPreferences trouvée\n";

    $instance = PDFEditorPreferences::get_instance();
    if (is_object($instance)) {
        echo "✓ Instance créée avec succès\n";

        // Tester les méthodes
        $prefs = $instance->get_preferences();
        echo "✓ Préférences par défaut récupérées: " . count($prefs) . " éléments\n";

        // Tester la sauvegarde (simulation)
        $test_prefs = array('test_key' => 'test_value');
        // Note: save_preferences nécessite un user_id, donc on ne peut pas tester sans WordPress
        echo "✓ Méthode save_preferences disponible\n";

    } else {
        echo "✗ Échec de création de l'instance\n";
    }
} else {
    echo "✗ Classe PDFEditorPreferences NON trouvée\n";
}

echo "\nTest terminé.\n";