<?php
/**
 * Script de diagnostic pour vérifier les paramètres de comportement
 * À placer dans le répertoire du plugin et accéder via l'URL WordPress
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    die('Accès direct non autorisé');
}

echo "<h1>🔍 Diagnostic - Paramètres de comportement du canvas</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .enabled{color:#28a745;font-weight:bold;} .disabled{color:#dc3545;font-weight:bold;} .default{color:#6c757d;font-style:italic;}</style>";

$behavior_settings = array(
    'pdf_builder_canvas_selection_mode' => array(
        'label' => 'Mode de sélection',
        'default' => 'click',
        'type' => 'select',
        'options' => array('click' => 'Clic simple', 'lasso' => 'Lasso', 'rectangle' => 'Rectangle')
    ),
    'pdf_builder_canvas_keyboard_shortcuts' => array(
        'label' => 'Raccourcis clavier',
        'default' => '1',
        'type' => 'checkbox'
    ),
    'pdf_builder_canvas_auto_save' => array(
        'label' => 'Sauvegarde automatique',
        'default' => '1',
        'type' => 'checkbox'
    )
);

echo "<table>";
echo "<tr><th>Paramètre</th><th>Clé WordPress</th><th>Valeur actuelle</th><th>Valeur par défaut</th><th>Status</th><th>Correspondance</th></tr>";

foreach ($behavior_settings as $key => $config) {
    $current_value = get_option($key, $config['default']);
    $default_value = $config['default'];
    $label = $config['label'];
    $type = $config['type'];

    // Déterminer le status affiché
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
        $options = $config['options'];
        $status = isset($options[$current_value]) ? $options[$current_value] : $current_value;
        $status_class = 'enabled';
    }

    // Vérifier la correspondance
    $match = ($current_value === $default_value) ? '✅ Défaut' : '⚠️ Personnalisé';
    $match_class = ($current_value === $default_value) ? 'enabled' : 'disabled';

    echo "<tr>";
    echo "<td><strong>{$label}</strong></td>";
    echo "<td><code>{$key}</code></td>";
    echo "<td><strong>{$current_value}</strong></td>";
    echo "<td class='default'>{$default_value}</td>";
    echo "<td class='{$status_class}'>{$status}</td>";
    echo "<td class='{$match_class}'>{$match}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>🔧 Actions de correction</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='reset_behavior_settings' value='1'>";
echo "<p><button type='submit' class='button button-primary'>Réinitialiser tous les paramètres de comportement aux valeurs par défaut</button></p>";
echo "</form>";

// Traiter la réinitialisation
if (isset($_POST['reset_behavior_settings'])) {
    foreach ($behavior_settings as $key => $config) {
        update_option($key, $config['default']);
    }
    echo "<div style='background:#d4edda;color:#155724;padding:10px;margin:10px 0;border:1px solid #c3e6cb;border-radius:4px;'>✅ Paramètres réinitialisés aux valeurs par défaut. <a href=''>Actualiser la page</a></div>";
}

echo "<h2>📋 Test de l'interface modale</h2>";
echo "<p>Si les paramètres apparaissent désactivés dans la modale malgré les valeurs correctes ici, le problème vient probablement du CSS ou JavaScript de l'interface.</p>";

echo "<h2>🔍 Code PHP de la modale (pour vérification)</h2>";
echo "<pre style='background:#f8f9fa;padding:10px;border:1px solid #dee2e6;overflow:auto;'>";
// Simuler le code PHP de la modale
echo htmlspecialchars('<?php checked(get_option(\'pdf_builder_canvas_keyboard_shortcuts\', \'1\'), \'1\'); ?>') . "\n";
echo htmlspecialchars('<?php checked(get_option(\'pdf_builder_canvas_auto_save\', \'1\'), \'1\'); ?>') . "\n";
echo htmlspecialchars('<?php selected(get_option(\'pdf_builder_canvas_selection_mode\', \'click\'), \'click\'); ?>');
echo "</pre>";

echo "<p><em>Script exécuté le " . date('d/m/Y à H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">i:\wp-pdf-builder-pro\plugin\diagnostic-behavior-settings.php