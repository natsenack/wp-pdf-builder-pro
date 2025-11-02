<?php
/**
 * Activation de l'API Preview 1.4
 * À exécuter dans le contexte WordPress
 */

define('ABSPATH', dirname(dirname(__FILE__)) . '/');
require_once ABSPATH . 'wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

// Activer l'API Preview
update_option('pdf_builder_preview_api_active', true);

echo "<h2>API Preview 1.4 - Activation</h2>";
echo "<p><strong>Status:</strong> ACTIVÉE</p>";
echo "<p>L'API Preview 1.4 est maintenant active et prête à être utilisée.</p>";

// Vérifier que la classe existe
$class_exists = class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI');
echo "<p><strong>Classe PreviewImageAPI:</strong> " . ($class_exists ? '<span style="color:green;">CHARGÉE</span>' : '<span style="color:red;">NON TROUVÉE</span>') . "</p>";

// Vérifier les fichiers JS
$files = [
    'assets/js/dist/pdf-preview-api-client.js',
    'assets/js/dist/pdf-preview-integration.js'
];

echo "<h3>Fichiers JavaScript:</h3><ul>";
foreach ($files as $file) {
    $path = plugin_dir_path(dirname(__FILE__)) . $file;
    $exists = file_exists($path);
    echo "<li><strong>$file:</strong> " . ($exists ? '<span style="color:green;">PRÉSENT</span>' : '<span style="color:red;">MANQUANT</span>');
    if ($exists) {
        echo " (" . filesize($path) . " bytes)";
    }
    echo "</li>";
}
echo "</ul>";

echo "<p><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "'>Retour au PDF Builder</a></p>";