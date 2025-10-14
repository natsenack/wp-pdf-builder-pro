<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Diagnostic d'urgence - PDF Builder Pro
 * Ce fichier permet de diagnostiquer pourquoi du code PHP apparaît brut
 */

// Test 1: PHP fonctionne ?
echo "<h2>🧪 Test PHP</h2>";
echo "<p>✅ PHP fonctionne - Version: " . phpversion() . "</p>";

// Test 2: WordPress chargé ?
echo "<h2>🔍 Test WordPress</h2>";
if (function_exists('wp_get_current_user')) {
    echo "<p>✅ WordPress est chargé</p>";
    $user = wp_get_current_user();
    echo "<p>Utilisateur actuel: " . ($user->ID ? $user->display_name : 'Non connecté') . "</p>";
} else {
    echo "<p>❌ WordPress n'est PAS chargé - C'est le problème !</p>";
    echo "<p>💡 Le plugin n'est pas inclus correctement par WordPress</p>";
}

// Test 3: Plugin activé ?
echo "<h2>🔌 Test Plugin</h2>";
if (function_exists('is_plugin_active')) {
    if (is_plugin_active('pdf-builder-pro/pdf-builder-pro.php')) {
        echo "<p>✅ Plugin PDF Builder Pro est activé</p>";
    } else {
        echo "<p>❌ Plugin PDF Builder Pro n'est PAS activé</p>";
    }
} else {
    echo "<p>⚠️ Fonction is_plugin_active non disponible</p>";
}

// Test 4: Fichiers présents ?
echo "<h2>📁 Test Fichiers</h2>";
$files_to_check = [
    'pdf-builder-pro.php',
    'includes/classes/class-pdf-builder-admin.php',
    'includes/classes/managers/class-pdf-builder-pdf-generator.php'
];

foreach ($files_to_check as $file) {
    $path = plugin_dir_path(__FILE__) . '../' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p>✅ $file existe ($size octets)</p>";
    } else {
        echo "<p>❌ $file MANQUANT</p>";
    }
}

// Test 5: Erreurs PHP
echo "<h2>🚨 Test Erreurs</h2>";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test d'inclusion du PDF generator
echo "<h2>📄 Test Inclusion PDF Generator</h2>";
try {
    $generator_path = plugin_dir_path(__FILE__) . '../includes/classes/managers/class-pdf-builder-pdf-generator.php';
    if (file_exists($generator_path)) {
        echo "<p>🔍 Tentative d'inclusion du PDF generator...</p>";
        require_once $generator_path;
        if (class_exists('PDF_Builder_PDF_Generator')) {
            echo "<p>✅ Classe PDF_Builder_PDF_Generator chargée avec succès</p>";
        } else {
            echo "<p>❌ Classe PDF_Builder_PDF_Generator non trouvée après inclusion</p>";
        }
    } else {
        echo "<p>❌ Fichier PDF generator introuvable</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur lors de l'inclusion: " . $e->getMessage() . "</p>";
}

echo "<hr><p><strong>Si vous voyez du code PHP brut au lieu de ces tests, c'est que PHP ne fonctionne pas sur le serveur.</strong></p>";
?>