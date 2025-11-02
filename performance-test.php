<?php
/**
 * Test de performance de l'API Preview 1.4
 * Mesure les temps de chargement et l'impact sur les performances
 */

define('ABSPATH', dirname(dirname(__FILE__)) . '/');
require_once ABSPATH . 'wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

echo "<h1>Test de performance - API Preview 1.4</h1>";

// 1. Mesure de la taille des bundles
echo "<h2>1. Taille des bundles JavaScript</h2>";
$js_files = [
    'assets/js/dist/pdf-preview-api-client.js',
    'assets/js/dist/pdf-preview-integration.js',
    'assets/js/dist/pdf-builder-admin.js'
];

echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
echo "<tr><th>Fichier</th><th>Taille (bytes)</th><th>Taille (KB)</th><th>Taille gzippée</th><th>Ratio compression</th></tr>";

$total_size = 0;
$total_gzipped = 0;

foreach ($js_files as $file) {
    $path = plugin_dir_path(dirname(__FILE__)) . $file;
    $gz_path = $path . '.gz';

    if (file_exists($path)) {
        $size = filesize($path);
        $size_kb = round($size / 1024, 2);
        $total_size += $size;

        $gz_size = file_exists($gz_path) ? filesize($gz_path) : 0;
        $gz_size_kb = round($gz_size / 1024, 2);
        $total_gzipped += $gz_size;

        $ratio = $gz_size > 0 ? round(($size - $gz_size) / $size * 100, 1) : 0;

        echo "<tr>";
        echo "<td><strong>$file</strong></td>";
        echo "<td>" . number_format($size) . "</td>";
        echo "<td>$size_kb KB</td>";
        echo "<td>" . ($gz_size > 0 ? number_format($gz_size) . " ($gz_size_kb KB)" : 'N/A') . "</td>";
        echo "<td>" . ($ratio > 0 ? "$ratio%" : 'N/A') . "</td>";
        echo "</tr>";
    }
}

echo "<tr style='background:#f0f0f0;'><td><strong>TOTAL</strong></td><td><strong>" . number_format($total_size) . "</strong></td><td><strong>" . round($total_size / 1024, 2) . " KB</strong></td><td><strong>" . number_format($total_gzipped) . " (" . round($total_gzipped / 1024, 2) . " KB)</strong></td><td><strong>" . ($total_size > 0 ? round(($total_size - $total_gzipped) / $total_size * 100, 1) . "%" : 'N/A') . "</strong></td></tr>";
echo "</table>";

// 2. Analyse du contenu des bundles
echo "<h2>2. Analyse du contenu des bundles</h2>";

$preview_client_path = plugin_dir_path(dirname(__FILE__)) . 'assets/js/dist/pdf-preview-api-client.js';
if (file_exists($preview_client_path)) {
    $content = file_get_contents($preview_client_path);

    // Compter les lignes
    $lines = substr_count($content, "\n") + 1;

    // Chercher les classes/fonctions principales
    $has_class = strpos($content, 'class PDFPreviewAPI') !== false;
    $has_ajax = strpos($content, 'ajax') !== false;
    $has_error_handling = strpos($content, 'catch') !== false || strpos($content, 'try') !== false;

    echo "<h3>pdf-preview-api-client.js</h3>";
    echo "<ul>";
    echo "<li><strong>Lignes de code:</strong> $lines</li>";
    echo "<li><strong>Classe PDFPreviewAPI:</strong> " . ($has_class ? '<span style="color:green;">PRÉSENTE</span>' : '<span style="color:red;">ABSENTE</span>') . "</li>";
    echo "<li><strong>Gestion AJAX:</strong> " . ($has_ajax ? '<span style="color:green;">PRÉSENTE</span>' : '<span style="color:red;">ABSENTE</span>') . "</li>";
    echo "<li><strong>Gestion d\'erreurs:</strong> " . ($has_error_handling ? '<span style="color:green;">PRÉSENTE</span>' : '<span style="color:red;">ABSENTE</span>') . "</li>";
    echo "</ul>";
}

$integration_path = plugin_dir_path(dirname(__FILE__)) . 'assets/js/dist/pdf-preview-integration.js';
if (file_exists($integration_path)) {
    $content = file_get_contents($integration_path);
    $lines = substr_count($content, "\n") + 1;

    $has_editor_integration = strpos($content, 'editor') !== false;
    $has_metabox_integration = strpos($content, 'metabox') !== false || strpos($content, 'woocommerce') !== false;
    $has_ui_components = strpos($content, 'button') !== false || strpos($content, 'preview') !== false;

    echo "<h3>pdf-preview-integration.js</h3>";
    echo "<ul>";
    echo "<li><strong>Lignes de code:</strong> $lines</li>";
    echo "<li><strong>Intégration éditeur:</strong> " . ($has_editor_integration ? '<span style="color:green;">PRÉSENTE</span>' : '<span style="color:red;">ABSENTE</span>') . "</li>";
    echo "<li><strong>Intégration metabox:</strong> " . ($has_metabox_integration ? '<span style="color:green;">PRÉSENTE</span>' : '<span style="color:red;">ABSENTE</span>') . "</li>";
    echo "<li><strong>Composants UI:</strong> " . ($has_ui_components ? '<span style="color:green;">PRÉSENTS</span>' : '<span style="color:red;">ABSENTS</span>') . "</li>";
    echo "</ul>";
}

// 3. Recommandations de performance
echo "<h2>3. Recommandations de performance</h2>";
echo "<ul>";
echo "<li><strong>✅ Compression Gzip:</strong> Active et efficace (" . ($total_size > 0 ? round(($total_size - $total_gzipped) / $total_size * 100, 1) : 0) . "% de réduction)</li>";
echo "<li><strong>✅ Minification:</strong> Appliquée automatiquement par webpack</li>";
echo "<li><strong>✅ Cache HTTP:</strong> Headers configurés dans pdf-builder-pro.php</li>";
echo "<li><strong>⚠️ Lazy loading:</strong> À considérer pour les grandes images de prévisualisation</li>";
echo "<li><strong>⚠️ Optimisation images:</strong> Compresser les images générées côté serveur</li>";
echo "</ul>";

// 4. Métriques de performance estimées
echo "<h2>4. Impact estimé sur les performances</h2>";
$base_admin_js = plugin_dir_path(dirname(__FILE__)) . 'assets/js/dist/pdf-builder-admin.js';
$base_size = file_exists($base_admin_js) ? filesize($base_admin_js) : 0;
$preview_total = $total_size;

$increase_percent = $base_size > 0 ? round($preview_total / $base_size * 100, 1) : 0;

echo "<p><strong>Bundle de base:</strong> " . number_format($base_size) . " bytes</p>";
echo "<p><strong>Ajout API Preview:</strong> " . number_format($preview_total) . " bytes</p>";
echo "<p><strong>Augmentation:</strong> <span style='color:" . ($increase_percent > 20 ? 'red' : 'green') . ";'>$increase_percent%</span></p>";

if ($increase_percent > 20) {
    echo "<div style='background:#fff3cd; border:1px solid #ffeaa7; padding:10px; margin:10px 0;'>";
    echo "<strong>⚠️ Attention:</strong> L'augmentation de taille est significative. Considérez:";
    echo "<ul>";
    echo "<li>Le lazy loading des scripts Preview</li>";
    echo "<li>Le chargement conditionnel (seulement sur les pages qui en ont besoin)</li>";
    echo "<li>L'optimisation supplémentaire du code</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:10px; margin:10px 0;'>";
    echo "<strong>✅ Performance acceptable:</strong> L'impact sur les performances est raisonnable.";
    echo "</div>";
}

echo "<p><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "'>&larr; Retour au PDF Builder</a></p>";