<?php
/**
 * Test de chargement isolé de PDF_Builder_Security_Validator
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la bufferisation de sortie
ob_start();

echo "<h1>Test de chargement PDF_Builder_Security_Validator</h1>\n";

try {
    // Simuler les constantes WordPress
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/var/www/nats/data/www/threeaxe.fr/');
    }

    echo "<p>✅ Constantes définies</p>\n";

    // Inclure le fichier directement depuis le dossier du plugin
    $file_path = __DIR__ . '/wp-content/plugins/wp-pdf-builder-pro/src/Core/PDF_Builder_Security_Validator.php';
    echo "<p>Test inclusion de : $file_path</p>\n";

    if (file_exists($file_path)) {
        echo "<p>✅ Fichier existe</p>\n";

        require_once $file_path;
        echo "<p>✅ Fichier inclus sans erreur fatale</p>\n";

        if (class_exists('PDF_Builder_Security_Validator')) {
            echo "<p>✅ Classe PDF_Builder_Security_Validator existe</p>\n";

            try {
                $instance = PDF_Builder_Security_Validator::get_instance();
                echo "<p>✅ Instance créée avec succès</p>\n";
                echo "<p>Type de l'instance : " . get_class($instance) . "</p>\n";
            } catch (Exception $e) {
                echo "<p>❌ Erreur lors de la création de l'instance : " . $e->getMessage() . "</p>\n";
            }

        } else {
            echo "<p>❌ Classe PDF_Builder_Security_Validator n'existe pas après inclusion</p>\n";
        }

    } else {
        echo "<p>❌ Fichier n'existe pas : $file_path</p>\n";
    }

} catch (Exception $e) {
    echo "<p>❌ Exception : " . $e->getMessage() . "</p>\n";
} catch (Error $e) {
    echo "<p>❌ Erreur fatale : " . $e->getMessage() . "</p>\n";
}

// Vider le buffer
ob_end_flush();
?>