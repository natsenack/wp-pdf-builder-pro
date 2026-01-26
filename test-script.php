<?php
// Test script pour vérifier le remplacement wp.preferences
echo "<h1>Test du remplacement wp.preferences</h1>";

// Simuler l'environnement WordPress
$_SERVER['REQUEST_URI'] = '/wp-admin/admin.php?page=pdf-builder-editor';

// Inclure le fichier ReactAssetsV2.php
require_once 'includes/ReactAssetsV2.php';

// Simuler admin_enqueue_scripts
do_action('admin_enqueue_scripts');

echo "<p>Script chargé. Ouvrez la console du navigateur pour voir les logs.</p>";
echo "<p>Si vous voyez '[PDF Builder] Remplacement wp-preferences activé', le script fonctionne.</p>";
?>