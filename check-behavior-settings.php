<?php
/**
 * Script de vérification des paramètres de comportement du canvas
 */

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>⚙️ Vérification des paramètres de comportement du canvas</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .enabled{color:#28a745;font-weight:bold;} .disabled{color:#dc3545;font-weight:bold;} .selected{color:#007bff;font-weight:bold;}</style>";

$behavior_settings = array(
    'canvas_selection_mode' => array(
        'key' => 'pdf_builder_canvas_selection_mode',
        'default' => 'click',
        'description' => 'Mode de sélection (click/lasso/rectangle)',
        'type' => 'select'
    ),
    'canvas_keyboard_shortcuts' => array(
        'key' => 'pdf_builder_canvas_keyboard_shortcuts',
        'default' => '1',
        'description' => 'Raccourcis clavier activés (0=non, 1=oui)',
        'type' => 'checkbox'
    ),
    'canvas_auto_save' => array(
        'key' => 'pdf_builder_canvas_auto_save',
        'default' => '1',
        'description' => 'Sauvegarde automatique activée (0=non, 1=oui)',
        'type' => 'checkbox'
    )
);

echo "<table>";
echo "<tr><th>Paramètre</th><th>Clé WordPress</th><th>Valeur actuelle</th><th>Valeur par défaut</th><th>Status</th><th>Description</th></tr>";

foreach ($behavior_settings as $setting => $config) {
    $key = $config['key'];
    $default = $config['default'];
    $current_value = get_option($key, $default);
    $description = $config['description'];
    $type = $config['type'];

    // Déterminer le status
    $status = '';
    $status_class = '';

    if ($type === 'checkbox') {
        if ($current_value === '1') {
            $status = 'ACTIVÉ';
            $status_class = 'enabled';
        } else {
            $status = 'DÉSACTIVÉ';
            $status_class = 'disabled';
        }
    } elseif ($type === 'select') {
        $status = strtoupper($current_value);
        $status_class = 'selected';
    }

    echo "<tr>";
    echo "<td><strong>{$setting}</strong></td>";
    echo "<td><code>{$key}</code></td>";
    echo "<td><strong>{$current_value}</strong></td>";
    echo "<td>{$default}</td>";
    echo "<td class='{$status_class}'>{$status}</td>";
    echo "<td>{$description}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>📋 Résumé des paramètres actifs :</h2>";
echo "<ul>";

// Récupérer les valeurs actuelles
$selection_mode = get_option('pdf_builder_canvas_selection_mode', 'click');
$keyboard_shortcuts = get_option('pdf_builder_canvas_keyboard_shortcuts', '1');
$auto_save = get_option('pdf_builder_canvas_auto_save', '1');

echo "<li><strong>Mode de sélection :</strong> " . strtoupper($selection_mode) . "</li>";
echo "<li><strong>Raccourcis clavier :</strong> " . ($keyboard_shortcuts === '1' ? '<span class="enabled">ACTIVÉS</span>' : '<span class="disabled">DÉSACTIVÉS</span>') . "</li>";
echo "<li><strong>Sauvegarde automatique :</strong> " . ($auto_save === '1' ? '<span class="enabled">ACTIVÉE</span>' : '<span class="disabled">DÉSACTIVÉE</span>') . "</li>";

echo "</ul>";

echo "<h2>🔧 Test des raccourcis clavier disponibles :</h2>";
echo "<ul>";
echo "<li><strong>Ctrl+Z</strong> : Annuler la dernière action</li>";
echo "<li><strong>Ctrl+Y</strong> : Rétablir la dernière action annulée</li>";
echo "<li><strong>Delete/Backspace</strong> : Supprimer les éléments sélectionnés</li>";
echo "<li><strong>Ctrl+A</strong> : Sélectionner tous les éléments</li>";
echo "<li><strong>Ctrl+C</strong> : Copier (non implémenté)</li>";
echo "<li><strong>Ctrl+V</strong> : Coller (non implémenté)</li>";
echo "<li><strong>Ctrl+D</strong> : Dupliquer (non implémenté)</li>";
echo "</ul>";

echo "<p><em>Script exécuté le " . date('d/m/Y à H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">i:\wp-pdf-builder-pro\check-behavior-settings.php