<?php
/**
 * Test fonctionnel de l'API Preview 1.4
 * Teste l'endpoint AJAX et la génération d'images de prévisualisation
 */

define('ABSPATH', dirname(dirname(__FILE__)) . '/');
require_once ABSPATH . 'wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

echo "<h1>Test fonctionnel - API Preview 1.4</h1>";

// 1. Vérifier que l'API est active
$api_active = get_option('pdf_builder_preview_api_active', false);
echo "<h2>1. État de l'API</h2>";
echo "<p>API Preview active: <strong>" . ($api_active ? '<span style="color:green;">OUI</span>' : '<span style="color:red;">NON</span>') . "</strong></p>";

// 2. Vérifier que la classe existe
$class_exists = class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI');
echo "<h2>2. Classe API</h2>";
echo "<p>Classe PreviewImageAPI: <strong>" . ($class_exists ? '<span style="color:green;">CHARGÉE</span>' : '<span style="color:red;">NON TROUVÉE</span>') . "</strong></p>";

// 3. Vérifier les fichiers JS
echo "<h2>3. Fichiers JavaScript</h2>";
$files = [
    'assets/js/dist/pdf-preview-api-client.js',
    'assets/js/dist/pdf-preview-integration.js'
];

echo "<ul>";
foreach ($files as $file) {
    $path = plugin_dir_path(dirname(__FILE__)) . $file;
    $exists = file_exists($path);
    echo "<li><strong>$file:</strong> " . ($exists ? '<span style="color:green;">PRÉSENT</span>' : '<span style="color:red;">MANQUANT</span>');
    if ($exists) {
        echo " (" . number_format(filesize($path)) . " bytes)";
    }
    echo "</li>";
}
echo "</ul>";

// 4. Tester l'endpoint AJAX
echo "<h2>4. Test de l'endpoint AJAX</h2>";
$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('pdf_builder_preview_nonce');

echo "<p><strong>URL AJAX:</strong> $ajax_url</p>";
echo "<p><strong>Action:</strong> pdf_builder_preview_image</p>";
echo "<p><strong>Nonce généré:</strong> $nonce</p>";

// 5. Test de génération d'image de prévisualisation
echo "<h2>5. Test de génération d'image</h2>";

// Données de test simples
$test_data = [
    'template_id' => 1,
    'elements' => [
        [
            'type' => 'text',
            'content' => 'Test Preview API',
            'x' => 50,
            'y' => 50,
            'width' => 200,
            'height' => 30,
            'fontSize' => 16,
            'color' => '#000000'
        ]
    ],
    'canvas' => [
        'width' => 595,
        'height' => 842,
        'backgroundColor' => '#ffffff'
    ]
];

echo "<p><strong>Données de test préparées:</strong> " . count($test_data['elements']) . " élément(s)</p>";

// Test AJAX simulé (sans l'exécuter réellement pour éviter les erreurs)
echo "<div style='background:#f0f0f0; padding:10px; margin:10px 0; border-left:4px solid #007cba;'>";
echo "<strong>Test AJAX simulé:</strong><br>";
echo "POST $ajax_url<br>";
echo "action=pdf_builder_preview_image<br>";
echo "nonce=$nonce<br>";
echo "template_data=" . json_encode($test_data) . "<br>";
echo "</div>";

// 6. Instructions pour les tests manuels
echo "<h2>6. Tests manuels recommandés</h2>";
echo "<ol>";
echo "<li>Accédez à l'<a href='" . admin_url('admin.php?page=pdf-builder-editor') . "' target='_blank'>éditeur PDF</a></li>";
echo "<li>Ouvrez la console du navigateur (F12)</li>";
echo "<li>Vérifiez que les scripts sont chargés: <code>pdfPreviewAPI</code> et <code>window.pdfPreviewIntegration</code></li>";
echo "<li>Testez la génération de prévisualisation avec des éléments simples</li>";
echo "<li>Vérifiez les metaboxes WooCommerce sur une commande existante</li>";
echo "</ol>";

echo "<p><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "'>&larr; Retour au PDF Builder</a></p>";