<?php
/**
 * Test de l'API Preview 1.4 - Vérification de l'intégration
 * À exécuter dans le contexte WordPress
 */

// Vérifier que les constantes sont définies
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Fonction de test
function test_preview_api_integration() {
    $output = "<h2>Test d'intégration de l'API Preview 1.4</h2>\n";

    // Vérifier que l'API Preview est activée
    $preview_api_active = get_option('pdf_builder_preview_api_active', false);
    $output .= "<p><strong>API Preview active:</strong> " . ($preview_api_active ? '<span style="color:green;">OUI</span>' : '<span style="color:red;">NON</span>') . "</p>\n";

    // Vérifier que les fichiers JS existent
    $js_files = [
        'pdf-preview-api-client.js',
        'pdf-preview-integration.js'
    ];

    $output .= "<h3>Fichiers JavaScript compilés:</h3>\n<ul>\n";
    foreach ($js_files as $file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . 'assets/js/dist/' . $file;
        $exists = file_exists($file_path);
        $output .= "<li><strong>$file:</strong> " . ($exists ? '<span style="color:green;">EXISTS</span>' : '<span style="color:red;">MISSING</span>');
        if ($exists) {
            $output .= " (" . filesize($file_path) . " bytes)";
        }
        $output .= "</li>\n";
    }
    $output .= "</ul>\n";

    // Vérifier que la classe PreviewImageAPI existe
    $class_exists = class_exists('PDF_Builder\\API\\PreviewImageAPI');
    $output .= "<p><strong>Classe PreviewImageAPI:</strong> " . ($class_exists ? '<span style="color:green;">LOADED</span>' : '<span style="color:red;">NOT FOUND</span>') . "</p>\n";

    // Tester l'endpoint AJAX
    $output .= "<h3>Configuration AJAX:</h3>\n";
    $ajax_url = admin_url('admin-ajax.php');
    $output .= "<p><strong>URL AJAX:</strong> $ajax_url</p>\n";
    $output .= "<p><strong>Action disponible:</strong> pdf_builder_preview_image</p>\n";

    // Vérifier les scripts enqueued
    $output .= "<h3>Scripts enqueued (devraient être chargés sur les pages admin):</h3>\n<ul>\n";
    $output .= "<li>pdf-preview-api-client</li>\n";
    $output .= "<li>pdf-preview-integration (dépend de pdf-preview-api-client)</li>\n";
    $output .= "</ul>\n";

    $output .= "<p><em>Note: Vérifiez la console du navigateur sur les pages admin pour confirmer que les scripts sont chargés.</em></p>\n";

    return $output;
}

// Si appelé directement, afficher le test
if (isset($_GET['test_preview']) && current_user_can('manage_options')) {
    echo test_preview_api_integration();
    exit;
}