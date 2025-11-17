<?php
/**
 * Script de test pour vérifier les paramètres système sauvegardés
 * Version simplifiée sans authentification pour les tests
 */

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>🧪 TEST COMPLET - Paramètres système PDF Builder</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .success{color:#28a745;font-weight:bold;} .error{color:#dc3545;font-weight:bold;}</style>";

$system_settings = array(
    'cache_enabled',
    'cache_expiry',
    'max_cache_size',
    'auto_maintenance',
    'auto_backup',
    'backup_retention'
);

echo "<h2>📊 Valeurs actuelles en base de données :</h2>";
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

echo "<h2>🔄 Test de modification des valeurs :</h2>";
echo "<p>Cliquez sur les boutons ci-dessous pour tester la sauvegarde :</p>";

// Boutons de test
echo "<div style='margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;'>";
echo "<button onclick='testSystemSave()' style='padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;'>🧪 Tester sauvegarde système</button>";
echo "<button onclick='resetSystemSettings()' style='padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;'>🔄 Remettre valeurs par défaut</button>";
echo "<div id='test-result' style='margin-top: 10px;'></div>";
echo "</div>";

echo "<h2>📋 Instructions de test :</h2>";
echo "<ol>";
echo "<li><strong>Via interface :</strong> Allez dans <a href='" . admin_url('admin.php?page=pdf-builder-settings') . "' target='_blank'>Paramètres PDF Builder</a> → Onglet Système</li>";
echo "<li><strong>Modifiez les valeurs :</strong> Changez les toggles et champs numériques</li>";
echo "<li><strong>Sauvegardez :</strong> Utilisez soit le bouton global 'Enregistrer' en bas, soit le bouton spécifique 'Enregistrer les paramètres système'</li>";
echo "<li><strong>Vérifiez :</strong> Rechargez cette page pour voir si les valeurs ont changé</li>";
echo "</ol>";

echo "<h2>🎯 Ce qui doit fonctionner :</h2>";
echo "<ul>";
echo "<li>✅ Lecture des valeurs depuis la base de données vers l'interface</li>";
echo "<li>✅ Sauvegarde via le bouton global 'Enregistrer'</li>";
echo "<li>✅ Sauvegarde via le bouton spécifique de l'onglet système</li>";
echo "<li>✅ Gestion correcte des toggles (cases à cocher)</li>";
echo "<li>✅ Gestion correcte des champs numériques</li>";
echo "</ul>";

echo "<script>
function testSystemSave() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<span style=\"color: #007cba;\">⏳ Test en cours...</span>';

    // Simuler des données de formulaire système
    const formData = new FormData();
    formData.append('action', 'pdf_builder_save_settings');
    formData.append('nonce', '" . wp_create_nonce('pdf_builder_save_settings') . "');
    formData.append('current_tab', 'systeme');
    formData.append('systeme_cache_enabled', '1');
    formData.append('systeme_cache_expiry', '96');
    formData.append('systeme_max_cache_size', '250');
    formData.append('systeme_auto_maintenance', '1');
    formData.append('systeme_auto_backup', '1');
    formData.append('systeme_backup_retention', '45');

    fetch('" . admin_url('admin-ajax.php') . "', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<span class=\"success\">✅ Test réussi ! Rechargez la page pour voir les changements.</span>';
        } else {
            resultDiv.innerHTML = '<span class=\"error\">❌ Erreur: ' + (data.data || 'Erreur inconnue') + '</span>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<span class=\"error\">❌ Erreur de connexion: ' + error.message + '</span>';
    });
}

function resetSystemSettings() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<span style=\"color: #007cba;\">⏳ Remise à zéro en cours...</span>';

    // Remettre les valeurs par défaut
    const defaults = {
        'pdf_builder_cache_enabled': '1',
        'pdf_builder_cache_expiry': '24',
        'pdf_builder_max_cache_size': '100',
        'pdf_builder_auto_maintenance': '0',
        'pdf_builder_auto_backup': '0',
        'pdf_builder_backup_retention': '30'
    };

    fetch('reset-system-settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(defaults)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<span class=\"success\">✅ Valeurs remises par défaut ! Rechargez la page.</span>';
        } else {
            resultDiv.innerHTML = '<span class=\"error\">❌ Erreur lors de la remise à zéro</span>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<span class=\"error\">❌ Erreur: ' + error.message + '</span>';
    });
}
</script>";
?>