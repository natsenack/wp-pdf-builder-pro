<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Core.php
 */

$filePath = __DIR__ . '/plugin/src/Core/PDF_Builder_Core.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace (vérifier d'abord s'il n'existe pas déjà)
if (strpos($content, 'namespace ') === false) {
    $content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Core;\n\n", $content);
}

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Core', 'class PdfBuilderCore', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'initialize_directories' => 'initializeDirectories',
    'register_admin_menu' => 'registerAdminMenu',
    'register_settings' => 'registerSettings',
    'admin_page' => 'adminPage',
    'templates_page' => 'templatesPage',
    'template_editor_page' => 'templateEditorPage',
    'settings_page' => 'settingsPage',
    'settings_section_callback' => 'settingsSectionCallback',
    'create_database_tables' => 'createDatabaseTables',
    'php_version_notice' => 'phpVersionNotice',
    'wp_version_notice' => 'wpVersionNotice',
    'init_admin' => 'initAdmin',
    'get_version' => 'getVersion',
    'generate_order_pdf' => 'generateOrderPdf',
    'ajax_save_settings' => 'ajaxSaveSettings',
    'ajax_get_settings' => 'ajaxGetSettings',
    'optimize_script_tags' => 'optimizeScriptTags',
    'optimize_style_tags' => 'optimizeStyleTags',
    'render_react_editor_page' => 'renderReactEditorPage'
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

echo "Corrections appliquées dans PDF_Builder_Core.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Core\n";
echo "- Nom de classe corrigé: PDF_Builder_Core → PdfBuilderCore\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>