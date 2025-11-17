<?php
/**
 * Script de test pour vérifier les paramètres système sauvegardés
 * Version simplifiée sans authentification pour les tests
 */

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>Test des paramètres système PDF Builder</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

$system_settings = array(
    'cache_enabled',
    'cache_expiry',
    'max_cache_size',
    'auto_maintenance',
    'auto_backup',
    'backup_retention'
);

echo "<table>";
echo "<tr><th>Paramètre</th><th>Clé WordPress</th><th>Valeur actuelle</th><th>Description</th></tr>";

foreach ($system_settings as $setting) {
    $key = 'pdf_builder_' . $setting;
    $value = get_option($key, 'NON DÉFINI');

    $description = '';
    switch ($setting) {
        case 'cache_enabled': $description = 'Cache activé (0=désactivé, 1=activé)'; break;
        case 'cache_expiry': $description = 'Expiration du cache (heures)'; break;
        case 'max_cache_size': $description = 'Taille max du cache (Mo)'; break;
        case 'auto_maintenance': $description = 'Maintenance automatique (0=non, 1=oui)'; break;
        case 'auto_backup': $description = 'Sauvegarde automatique (0=non, 1=oui)'; break;
        case 'backup_retention': $description = 'Rétention des sauvegardes (jours)'; break;
    }

    echo "<tr>";
    echo "<td><strong>{$setting}</strong></td>";
    echo "<td><code>{$key}</code></td>";
    echo "<td><strong>{$value}</strong></td>";
    echo "<td>{$description}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<br><br>";
echo "<a href='" . admin_url('admin.php?page=pdf-builder-settings') . "'>&larr; Retour aux paramètres</a>";
?>