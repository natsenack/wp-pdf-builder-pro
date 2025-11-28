<?php
/**
 * Test ultra-simple de chargement de PDF_Builder_Security_Validator
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test ultra-simple PDF_Builder_Security_Validator</h1>\n";

echo "<h2>Étape 1: Définition des constantes</h2>\n";
if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/nats/data/www/threeaxe.fr/');
}
echo "<p>✅ ABSPATH défini</p>\n";

echo "<h2>Étape 2: Vérification du fichier</h2>\n";
$file_path = __DIR__ . '/wp-content/plugins/wp-pdf-builder-pro/src/Core/PDF_Builder_Security_Validator.php';
echo "<p>Chemin du fichier: $file_path</p>\n";

if (file_exists($file_path)) {
    echo "<p>✅ Fichier existe</p>\n";
    echo "<p>Taille du fichier: " . filesize($file_path) . " octets</p>\n";

    echo "<h2>Étape 3: Inclusion du fichier</h2>\n";
    try {
        require_once $file_path;
        echo "<p>✅ require_once réussi</p>\n";

        echo "<h2>Étape 4: Vérification de la classe</h2>\n";
        if (class_exists('PDF_Builder_Security_Validator')) {
            echo "<p>✅ Classe PDF_Builder_Security_Validator existe</p>\n";

            echo "<h2>Étape 5: Test des méthodes statiques</h2>\n";

            // Test sanitizeHtmlContent
            $result = PDF_Builder_Security_Validator::sanitizeHtmlContent('<p>test</p>');
            echo "<p>✅ sanitizeHtmlContent() fonctionne</p>\n";

            // Test validateJsonData
            $result = PDF_Builder_Security_Validator::validateJsonData('{"test": "value"}');
            echo "<p>✅ validateJsonData() fonctionne</p>\n";

            // Test validateNonce
            $result = PDF_Builder_Security_Validator::validateNonce('test', 'action');
            echo "<p>✅ validateNonce() fonctionne</p>\n";

            // Test checkPermissions
            $result = PDF_Builder_Security_Validator::checkPermissions();
            echo "<p>✅ checkPermissions() fonctionne</p>\n";

            echo "<h2>Étape 6: Test d'instanciation</h2>\n";
            $instance = PDF_Builder_Security_Validator::get_instance();
            echo "<p>✅ Instance créée avec succès</p>\n";
            echo "<p>Type: " . get_class($instance) . "</p>\n";

            echo "<h1>🎉 SUCCÈS TOTAL ! Le Security Validator fonctionne parfaitement !</h1>\n";

        } else {
            echo "<p>❌ ERREUR: Classe PDF_Builder_Security_Validator n'existe pas après inclusion</p>\n";
            echo "<p>Classes disponibles: " . implode(', ', get_declared_classes()) . "</p>\n";
        }

    } catch (Exception $e) {
        echo "<p>❌ ERREUR lors de require_once: " . $e->getMessage() . "</p>\n";
    } catch (Error $e) {
        echo "<p>❌ ERREUR FATALE lors de require_once: " . $e->getMessage() . "</p>\n";
        echo "<p>Trace: " . $e->getTraceAsString() . "</p>\n";
    }

} else {
    echo "<p>❌ ERREUR: Fichier n'existe pas</p>\n";
    echo "<p>Fichiers dans le répertoire: " . implode(', ', scandir(dirname($file_path))) . "</p>\n";
}

echo "<h2>Informations système</h2>\n";
echo "<p>PHP Version: " . phpversion() . "</p>\n";
echo "<p>OS: " . php_uname() . "</p>\n";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>\n";
echo "<p>Max execution time: " . ini_get('max_execution_time') . "</p>\n";
?>