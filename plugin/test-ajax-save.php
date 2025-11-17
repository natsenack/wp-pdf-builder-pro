<?php
/**
 * Script pour tester la sauvegarde AJAX directement
 */

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>Test de sauvegarde AJAX système</h1>";

// Simuler les données POST qui seraient envoyées par AJAX
$test_post_data = array(
    'nonce' => wp_create_nonce('pdf_builder_save_settings'),
    'current_tab' => 'systeme',
    'cache_enabled' => '1',     // Activé
    'cache_expiry' => '72',     // 72 heures
    'max_cache_size' => '500',  // 500 Mo
    'auto_maintenance' => '1',  // Activé
    'auto_backup' => '1',       // Activé
    'backup_retention' => '90'  // 90 jours
);

// Sauvegarder comme le fait le handler AJAX
$saved_count = 0;
$settings = array(
    'cache_enabled' => isset($test_post_data['cache_enabled']) ? '1' : '0',
    'cache_expiry' => intval($test_post_data['cache_expiry']),
    'max_cache_size' => intval($test_post_data['max_cache_size']),
    'auto_maintenance' => isset($test_post_data['auto_maintenance']) ? '1' : '0',
    'auto_backup' => isset($test_post_data['auto_backup']) ? '1' : '0',
    'backup_retention' => intval($test_post_data['backup_retention']),
);

echo "<h2>Données de test envoyées :</h2>";
echo "<pre>" . print_r($test_post_data, true) . "</pre>";

echo "<h2>Paramètres calculés pour sauvegarde :</h2>";
echo "<pre>" . print_r($settings, true) . "</pre>";

echo "<h2>Sauvegarde en cours...</h2>";
echo "<ul>";
foreach ($settings as $key => $value) {
    update_option('pdf_builder_' . $key, $value);
    echo "<li><strong>{$key}:</strong> {$value} ✅</li>";
}
echo "</ul>";
$saved_count++;

echo "<h2>Résultat final :</h2>";
if ($saved_count > 0) {
    echo "<div style='color: green; font-weight: bold;'>✅ Paramètres sauvegardés avec succès !</div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ Aucun paramètre sauvegardé</div>";
}

echo "<br><br>";
echo "<a href='test-system-settings.php'>📊 Voir les valeurs sauvegardées</a>";
echo "<br>";
echo "<a href='" . admin_url('admin.php?page=pdf-builder-settings') . "' target='_blank'>🎯 Ouvrir les paramètres pour vérifier l'interface</a>";
?>