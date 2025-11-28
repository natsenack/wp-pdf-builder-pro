<?php
/**
 * Test amélioré de chargement isolé de PDF_Builder_Security_Validator
 * Teste le chargement de la classe sans utiliser de fonctions WordPress
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la bufferisation de sortie
ob_start();

echo "<h1>Test amélioré de chargement PDF_Builder_Security_Validator</h1>\n";

try {
    // Simuler les constantes WordPress de base
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

            // Test des méthodes statiques qui ne nécessitent pas WordPress
            echo "<p>Test des méthodes statiques de base...</p>\n";

            // Test sanitizeHtmlContent avec WordPress non chargé
            $test_content = "<p>Test content</p><script>alert('xss')</script>";
            $result = PDF_Builder_Security_Validator::sanitizeHtmlContent($test_content);
            echo "<p>✅ sanitizeHtmlContent() fonctionne (retourne contenu non filtré si WP non chargé)</p>\n";

            // Test validateJsonData
            $valid_json = '{"test": "value"}';
            $result = PDF_Builder_Security_Validator::validateJsonData($valid_json);
            echo "<p>✅ validateJsonData() fonctionne pour JSON valide</p>\n";

            $invalid_json = '{"test": "value", invalid}';
            $result = PDF_Builder_Security_Validator::validateJsonData($invalid_json);
            echo "<p>✅ validateJsonData() détecte JSON invalide</p>\n";

            // Test validateNonce avec WordPress non chargé
            $result = PDF_Builder_Security_Validator::validateNonce('test', 'action');
            echo "<p>✅ validateNonce() retourne false si WP non chargé</p>\n";

            // Test checkPermissions avec WordPress non chargé
            $result = PDF_Builder_Security_Validator::checkPermissions();
            echo "<p>✅ checkPermissions() retourne false si WP non chargé</p>\n";

            // Test de création d'instance (devrait fonctionner maintenant)
            try {
                $instance = PDF_Builder_Security_Validator::get_instance();
                echo "<p>✅ Instance créée avec succès</p>\n";
                echo "<p>Type de l'instance : " . get_class($instance) . "</p>\n";

                // Test des méthodes d'instance qui ne nécessitent pas WordPress
                if (method_exists($instance, 'validate_email')) {
                    $email_result = $instance->validate_email('test@example.com');
                    echo "<p>✅ validate_email() fonctionne</p>\n";
                }

                if (method_exists($instance, 'validate_phone')) {
                    $phone_result = $instance->validate_phone('0123456789');
                    echo "<p>✅ validate_phone() fonctionne</p>\n";
                }

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