<?php
/**
 * Test script to check if PDF Builder scripts are loading
 */

// Check if we're in WordPress
if (!defined('ABSPATH')) {
    exit('This script must be run within WordPress');
}

// Get the script URL
$script_url = PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js';
$assets_url = PDF_BUILDER_PRO_ASSETS_URL;

echo "<h1>Test de chargement des scripts PDF Builder</h1>";
echo "<h2>Informations de debug :</h2>";
echo "<ul>";
echo "<li><strong>PDF_BUILDER_PRO_ASSETS_URL:</strong> {$assets_url}</li>";
echo "<li><strong>Script URL complète:</strong> {$script_url}</li>";
echo "<li><strong>Script existe localement:</strong> " . (file_exists(str_replace(site_url(), ABSPATH, $script_url)) ? 'OUI' : 'NON') . "</li>";
echo "</ul>";

// Test if script is accessible via HTTP
echo "<h2>Test d'accessibilité HTTP :</h2>";
$headers = get_headers($script_url, 1);
if ($headers) {
    echo "<p><strong>Status:</strong> " . $headers[0] . "</p>";
    echo "<p><strong>Content-Type:</strong> " . ($headers['Content-Type'] ?? 'N/A') . "</p>";
    echo "<p><strong>Content-Length:</strong> " . ($headers['Content-Length'] ?? 'N/A') . "</p>";
} else {
    echo "<p style='color: red;'>❌ Impossible d'accéder au script via HTTP</p>";
}

// Test script loading
echo "<h2>Test de chargement du script :</h2>";
echo "<div id='script-test-result'>Chargement en cours...</div>";

echo "<script>
setTimeout(function() {
    var result = document.getElementById('script-test-result');
    if (typeof window.PDFBuilderPro !== 'undefined') {
        result.innerHTML = '<span style=\"color: green;\">✅ Script chargé avec succès - PDFBuilderPro disponible</span>';
        console.log('PDFBuilderPro:', window.PDFBuilderPro);
    } else {
        result.innerHTML = '<span style=\"color: red;\">❌ Script non chargé - PDFBuilderPro indisponible</span>';
    }
}, 1000);
</script>";

// Load the script manually for testing
echo "<script src='{$script_url}'></script>";
?>