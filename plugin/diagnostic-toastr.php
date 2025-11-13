<?php
/**
 * Diagnostic - Vérifier les fichiers Toastr sur le serveur
 */

$plugin_dir = dirname(__FILE__);
$assets_dir = $plugin_dir . '/assets';

echo "=== DIAGNOSTIC TOASTR ===\n\n";
echo "Plugin dir: " . $plugin_dir . "\n";
echo "Assets dir: " . $assets_dir . "\n\n";

// Vérifier le dossier toastr CSS
$css_toastr_dir = $assets_dir . '/css/toastr';
$css_file = $css_toastr_dir . '/toastr.min.css';

echo "📁 CSS Toastr dir: " . $css_toastr_dir . "\n";
echo "   Existe: " . (is_dir($css_toastr_dir) ? "✅ OUI" : "❌ NON") . "\n";
echo "📄 CSS File: " . $css_file . "\n";
echo "   Existe: " . (file_exists($css_file) ? "✅ OUI (" . filesize($css_file) . " bytes)" : "❌ NON") . "\n\n";

// Vérifier le dossier toastr JS
$js_toastr_dir = $assets_dir . '/js/toastr';
$js_file = $js_toastr_dir . '/toastr.min.js';

echo "📁 JS Toastr dir: " . $js_toastr_dir . "\n";
echo "   Existe: " . (is_dir($js_toastr_dir) ? "✅ OUI" : "❌ NON") . "\n";
echo "📄 JS File: " . $js_file . "\n";
echo "   Existe: " . (file_exists($js_file) ? "✅ OUI (" . filesize($js_file) . " bytes)" : "❌ NON") . "\n\n";

// Lister les fichiers dans le dossier assets
echo "📂 Fichiers dans assets:\n";
if (is_dir($assets_dir)) {
    $files = scandir($assets_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - " . $file . (is_dir($assets_dir . '/' . $file) ? '/' : '') . "\n";
        }
    }
}

// Vérifier CSS et JS spécifiquement
echo "\n📋 Vérification des fichiers Toastr:\n";
if (file_exists($css_file)) {
    echo "✅ CSS Toastr: " . filesize($css_file) . " bytes\n";
} else {
    echo "❌ CSS Toastr: MANQUANT\n";
}

if (file_exists($js_file)) {
    echo "✅ JS Toastr: " . filesize($js_file) . " bytes\n";
} else {
    echo "❌ JS Toastr: MANQUANT\n";
}

echo "\n=== FIN DIAGNOSTIC ===\n";
?>
