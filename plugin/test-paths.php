<?php
/**
 * Script de diagnostic pour vérifier les chemins des templates builtin
 */

// Simuler l'environnement WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

echo "<h1>Diagnostic des templates builtin</h1>";

// Test 1: Vérifier plugin_dir_path
echo "<h2>Test 1: plugin_dir_path</h2>";
$plugin_dir = plugin_dir_path(dirname(__FILE__));
echo "<p>plugin_dir_path(dirname(__FILE__)): {$plugin_dir}</p>";

// Test 2: Construire le chemin vers builtin
echo "<h2>Test 2: Chemin vers builtin</h2>";
$builtin_dir = $plugin_dir . "templates/builtin/";
echo "<p>builtin_dir: {$builtin_dir}</p>";
echo "<p>is_dir(builtin_dir): " . (is_dir($builtin_dir) ? 'true' : 'false') . "</p>";

// Test 3: Lister les fichiers
echo "<h2>Test 3: Fichiers dans builtin</h2>";
if (is_dir($builtin_dir)) {
    $files = glob($builtin_dir . '*.json');
    echo "<p>Nombre de fichiers JSON trouvés: " . count($files) . "</p>";
    foreach ($files as $file) {
        echo "<p>- " . basename($file) . " (exists: " . (file_exists($file) ? 'true' : 'false') . ")</p>";
    }
} else {
    echo "<p>Le dossier builtin n'existe pas !</p>";
}

// Test 4: Test des chemins pour chaque builtin_id
echo "<h2>Test 4: Test des chemins individuels</h2>";
$builtin_ids = ['classic', 'corporate', 'minimal', 'modern'];

foreach ($builtin_ids as $id) {
    $file_path = $builtin_dir . $id . '.json';
    echo "<p>{$id}: {$file_path} (exists: " . (file_exists($file_path) ? 'true' : 'false') . ")</p>";
}

// Test 5: Test de la fonction plugin_dir_path avec le fichier actuel
echo "<h2>Test 5: plugin_dir_path depuis PDF_Builder_Admin.php</h2>";
$admin_file = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/src/Admin/PDF_Builder_Admin.php';
if (file_exists($admin_file)) {
    $admin_plugin_dir = plugin_dir_path(dirname($admin_file));
    echo "<p>Depuis PDF_Builder_Admin.php: {$admin_plugin_dir}</p>";
    $admin_builtin_dir = $admin_plugin_dir . "templates/builtin/";
    echo "<p>builtin_dir depuis admin: {$admin_builtin_dir}</p>";
    echo "<p>is_dir: " . (is_dir($admin_builtin_dir) ? 'true' : 'false') . "</p>";
} else {
    echo "<p>Fichier PDF_Builder_Admin.php non trouvé: {$admin_file}</p>";
}
?>