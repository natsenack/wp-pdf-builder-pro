<?php
/**
 * Script pour modifier les paramètres système et tester la sauvegarde
 */

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>Modification des paramètres système pour test</h1>";

// Valeurs de test différentes
$test_values = array(
    'cache_enabled' => '0',  // Désactivé
    'cache_expiry' => '48',  // 48 heures
    'max_cache_size' => '200', // 200 Mo
    'auto_maintenance' => '0', // Désactivé
    'auto_backup' => '0',     // Désactivé
    'backup_retention' => '60' // 60 jours
);

echo "<h2>Valeurs actuelles avant modification :</h2>";
echo "<ul>";
foreach ($test_values as $key => $new_value) {
    $current_value = get_option('pdf_builder_' . $key, 'NON DÉFINI');
    echo "<li><strong>{$key}:</strong> {$current_value}</li>";
}
echo "</ul>";

echo "<h2>Application des nouvelles valeurs de test :</h2>";
echo "<ul>";
foreach ($test_values as $key => $new_value) {
    update_option('pdf_builder_' . $key, $new_value);
    echo "<li><strong>{$key}:</strong> {$new_value} ✅</li>";
}
echo "</ul>";

echo "<h2>Vérification des nouvelles valeurs :</h2>";
echo "<ul>";
foreach ($test_values as $key => $expected_value) {
    $actual_value = get_option('pdf_builder_' . $key, 'NON DÉFINI');
    $status = ($actual_value == $expected_value) ? '✅ OK' : '❌ ERREUR';
    echo "<li><strong>{$key}:</strong> {$actual_value} {$status}</li>";
}
echo "</ul>";

echo "<br><br>";
echo "<a href='" . admin_url('admin.php?page=pdf-builder-settings') . "' target='_blank'>🎯 Ouvrir les paramètres pour vérifier l'interface</a>";
echo "<br>";
echo "<a href='test-system-settings.php'>📊 Voir les valeurs actuelles</a>";
?>