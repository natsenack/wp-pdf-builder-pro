<?php
/**
 * TEST RAPIDE - Vérification que le Security Validator se charge
 */

// Activer erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 TEST RAPIDE SECURITY VALIDATOR</h1>";

// Test chargement
try {
    require_once '/var/www/nats/data/www/threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/src/Core/PDF_Builder_Security_Validator.php';
    echo "<p>✅ Fichier chargé sans erreur</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
    exit;
}

// Test classe
if (class_exists('PDF_Builder\\Core\\PDF_Builder_Security_Validator')) {
    echo "<p>✅ Classe trouvée</p>";

    // Test méthodes statiques
    $result1 = PDF_Builder\Core\PDF_Builder_Security_Validator::sanitizeHtmlContent('<p>Test</p>');
    echo "<p>✅ sanitizeHtmlContent: " . htmlspecialchars($result1) . "</p>";

    $result2 = PDF_Builder\Core\PDF_Builder_Security_Validator::validateJsonData('{"test": true}');
    echo "<p>✅ validateJsonData: " . ($result2 ? 'OK' : 'FAIL') . "</p>";

    $result3 = PDF_Builder\Core\PDF_Builder_Security_Validator::checkPermissions();
    echo "<p>✅ checkPermissions: " . ($result3 ? 'true' : 'false') . "</p>";

} else {
    echo "<p>❌ Classe NON trouvée</p>";
}

echo "<h2>🎉 TEST TERMINÉ</h2>";
echo "<p>Si tu vois ce message, le Security Validator fonctionne !</p>";
echo "<p>Teste maintenant ton site: <a href='https://threeaxe.fr'>https://threeaxe.fr</a></p>";
?>