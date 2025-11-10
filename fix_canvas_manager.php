<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans Canvas_Manager.php
 */

$filePath = __DIR__ . '/plugin/src/Canvas/Canvas_Manager.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace (vérifier d'abord s'il n'existe pas déjà)
if (strpos($content, 'namespace ') === false) {
    $content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Canvas;\n\n", $content);
}

// 2. Corriger le nom de la classe
$content = str_replace('class Canvas_Manager', 'class CanvasManager', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'get_instance' => 'getInstance',
    'load_settings' => 'loadSettings',
    'get_default_settings' => 'getDefaultSettings',
    'register_hooks' => 'registerHooks',
    'apply_canvas_settings_to_react' => 'applyCanvasSettingsToReact',
    'enqueue_canvas_settings_script' => 'enqueueCanvasSettingsScript',
    'get_canvas_settings_script' => 'getCanvasSettingsScript',
    'get_setting' => 'getSetting',
    'get_all_settings' => 'getAllSettings',
    'get_canvas_dimensions' => 'getCanvasDimensions',
    'get_canvas_margins' => 'getCanvasMargins',
    'get_grid_settings' => 'getGridSettings',
    'get_zoom_settings' => 'getZoomSettings',
    'get_selection_settings' => 'getSelectionSettings',
    'get_export_settings' => 'getExportSettings',
    'get_history_settings' => 'getHistorySettings',
    'is_feature_enabled' => 'isFeatureEnabled',
    'reset_to_defaults' => 'resetToDefaults',
    'save_settings' => 'saveSettings',
    'validate_settings' => 'validateSettings'
];

// 4. Corriger les appels de méthodes dans le fichier lui-même
foreach ($methodMappings as $old => $new) {
    // Remplacer les appels de méthodes (avec -> ou ::)
    $content = preg_replace('/(->|::)' . preg_quote($old, '/') . '\(/', '$1' . $new . '(', $content);
}

// 5. Corriger les déclarations de méthodes
foreach ($methodMappings as $old => $new) {
    $content = preg_replace('/function ' . preg_quote($old, '/') . '\(/', 'function ' . $new . '(', $content);
}

file_put_contents($filePath, $content);

echo "Corrections appliquées dans Canvas_Manager.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Canvas\n";
echo "- Nom de classe corrigé: Canvas_Manager → CanvasManager\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>