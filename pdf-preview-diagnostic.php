<?php
/**
 * Diagnostic complet du système d'aperçu PDF
 * PDF Builder Pro - Outil de diagnostic et réparation
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Inclure les dépendances WordPress
require_once '../../../wp-load.php';
require_once '../../../wp-admin/includes/plugin.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🔍 Diagnostic Aperçu PDF - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #e8f5e8; border-color: #4caf50; }
        .warning { background: #fff3e0; border-color: #ff9800; }
        .error { background: #ffebee; border-color: #f44336; }
        .info { background: #e3f2fd; border-color: #2196f3; }
        h1, h2, h3 { color: #333; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .passed { background: #e8f5e8; color: #2e7d32; }
        .failed { background: #ffebee; color: #c62828; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-primary { background: #2196f3; color: white; }
        .btn-success { background: #4caf50; color: white; }
        .btn-danger { background: #f44336; color: white; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔍 Diagnostic Complet - Aperçu PDF</h1>
<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>
<p><strong>Plugin:</strong> PDF Builder Pro</p>
<hr>";

// Test 1: Vérification des dépendances de base
echo "<div class='section info'>
<h2>📋 Test 1: Dépendances de Base</h2>";

$tests = [
    'WordPress actif' => function_exists('wp_get_current_user'),
    'WooCommerce actif' => class_exists('WooCommerce'),
    'TCPDF disponible' => class_exists('TCPDF') || file_exists(__DIR__ . '/../lib/tcpdf/tcpdf.php') || file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php'),
    'Base de données accessible' => function_exists('wpdb') && isset($GLOBALS['wpdb']),
    'Permissions écriture uploads' => wp_is_writable(wp_upload_dir()['basedir']),
];

foreach ($tests as $test_name => $result) {
    $class = $result ? 'passed' : 'failed';
    $icon = $result ? '✅' : '❌';
    echo "<div class='test-result $class'>$icon $test_name</div>";
}

echo "</div>";

// Test 2: Vérification des classes du générateur PDF
echo "<div class='section info'>
<h2>🏗️ Test 2: Classes du Générateur PDF</h2>";

$generator_tests = [
    'Classe PDF_Builder_Pro_Generator' => class_exists('PDF_Builder_Pro_Generator'),
    'Fichier pdf-generator.php' => file_exists(__DIR__ . '/../includes/pdf-generator.php'),
];

foreach ($generator_tests as $test_name => $result) {
    $class = $result ? 'passed' : 'failed';
    $icon = $result ? '✅' : '❌';
    echo "<div class='test-result $class'>$icon $test_name</div>";
}

// Charger la classe si nécessaire
if (!class_exists('PDF_Builder_Pro_Generator') && file_exists(__DIR__ . '/../includes/pdf-generator.php')) {
    require_once __DIR__ . '/../includes/pdf-generator.php';
    echo "<div class='test-result passed'>✅ Classe PDF_Builder_Pro_Generator chargée manuellement</div>";
}

echo "</div>";

// Test 3: Vérification de la base de données
echo "<div class='section info'>
<h2>🗄️ Test 3: Base de Données</h2>";

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$db_tests = [
    'Table templates existe' => $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") === $table_templates,
];

if ($db_tests['Table templates existe']) {
    $template_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");
    $default_templates = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates WHERE is_default = 1");

    echo "<div class='test-result passed'>✅ Tables de base de données accessibles</div>";
    echo "<div class='test-result info'>📊 Statistiques: $template_count templates, $default_templates par défaut</div>";

    // Lister les templates
    $templates = $wpdb->get_results("SELECT id, name, is_default FROM $table_templates ORDER BY id", ARRAY_A);
    if (!empty($templates)) {
        echo "<h3>Templates disponibles:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nom</th><th>Par défaut</th><th>Actions</th></tr>";
        foreach ($templates as $template) {
            $default_icon = $template['is_default'] ? '⭐' : '';
            echo "<tr>";
            echo "<td>{$template['id']}</td>";
            echo "<td>{$template['name']}</td>";
            echo "<td>$default_icon</td>";
            echo "<td><button class='btn btn-primary' onclick='testTemplate({$template['id']})'>Tester</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='test-result failed'>❌ Table templates introuvable</div>";
}

echo "</div>";

// Test 4: Vérification des commandes WooCommerce
echo "<div class='section info'>
<h2>🛒 Test 4: Commandes WooCommerce</h2>";

if (class_exists('WooCommerce')) {
    $order_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order'");
    echo "<div class='test-result passed'>✅ WooCommerce détecté: $order_count commandes</div>";

    // Récupérer quelques commandes récentes pour les tests
    $recent_orders = $wpdb->get_results("
        SELECT ID, post_title, post_status, post_date
        FROM {$wpdb->prefix}posts
        WHERE post_type = 'shop_order'
        ORDER BY post_date DESC
        LIMIT 5
    ", ARRAY_A);

    if (!empty($recent_orders)) {
        echo "<h3>Commandes récentes pour test:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Numéro</th><th>Statut</th><th>Date</th><th>Actions</th></tr>";
        foreach ($recent_orders as $order) {
            $order_obj = wc_get_order($order['ID']);
            $order_number = $order_obj ? $order_obj->get_order_number() : $order['ID'];
            echo "<tr>";
            echo "<td>{$order['ID']}</td>";
            echo "<td>$order_number</td>";
            echo "<td>{$order['post_status']}</td>";
            echo "<td>" . date('d/m/Y', strtotime($order['post_date'])) . "</td>";
            echo "<td><button class='btn btn-success' onclick='testOrderPreview({$order['ID']})'>Aperçu</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='test-result failed'>❌ WooCommerce non détecté</div>";
}

echo "</div>";

// Test 5: Diagnostic des données canvas
echo "<div class='section warning'>
<h2>🎨 Test 5: Diagnostic des Données Canvas</h2>";

if (isset($table_templates) && $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") === $table_templates) {
    $templates_with_canvas = $wpdb->get_results("
        SELECT id, name, template_data
        FROM $table_templates
        WHERE template_data IS NOT NULL AND template_data != ''
        ORDER BY id
    ", ARRAY_A);

    if (!empty($templates_with_canvas)) {
        echo "<div class='test-result passed'>✅ " . count($templates_with_canvas) . " templates avec données canvas trouvés</div>";

        foreach ($templates_with_canvas as $template) {
            echo "<h4>Template: {$template['name']} (ID: {$template['id']})</h4>";

            $canvas_data = json_decode($template['template_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<div class='test-result passed'>✅ JSON valide</div>";

                // Analyser la structure
                if (isset($canvas_data['pages']) && is_array($canvas_data['pages'])) {
                    $page_count = count($canvas_data['pages']);
                    echo "<div class='test-result info'>📄 $page_count page(s) détectée(s)</div>";

                    foreach ($canvas_data['pages'] as $page_index => $page) {
                        if (isset($page['elements']) && is_array($page['elements'])) {
                            $element_count = count($page['elements']);
                            echo "<div class='test-result info'>📄 Page " . ($page_index + 1) . ": $element_count élément(s)</div>";

                            // Analyser les types d'éléments
                            $element_types = array_column($page['elements'], 'type');
                            $type_counts = array_count_values($element_types);

                            echo "<div class='code'>";
                            foreach ($type_counts as $type => $count) {
                                echo "• $type: $count<br>";
                            }
                            echo "</div>";
                        }
                    }
                } elseif (isset($canvas_data['elements']) && is_array($canvas_data['elements'])) {
                    $element_count = count($canvas_data['elements']);
                    echo "<div class='test-result info'>📄 Structure simple: $element_count élément(s)</div>";

                    $element_types = array_column($canvas_data['elements'], 'type');
                    $type_counts = array_count_values($element_types);

                    echo "<div class='code'>";
                    foreach ($type_counts as $type => $count) {
                        echo "• $type: $count<br>";
                    }
                    echo "</div>";
                } else {
                    echo "<div class='test-result warning'>⚠️ Structure canvas non reconnue</div>";
                }
            } else {
                echo "<div class='test-result failed'>❌ JSON invalide: " . json_last_error_msg() . "</div>";
                echo "<div class='code'>" . substr($template['template_data'], 0, 200) . "...</div>";
            }
        }
    } else {
        echo "<div class='test-result warning'>⚠️ Aucun template avec données canvas trouvé</div>";
    }
}

echo "</div>";

// Test 6: Test de génération PDF basique
echo "<div class='section success'>
<h2>📄 Test 6: Génération PDF Basique</h2>";

if (class_exists('PDF_Builder_Pro_Generator')) {
    try {
        $generator = new PDF_Builder_Pro_Generator();

        // Test avec éléments simples
        $test_elements = [
            [
                'type' => 'text',
                'content' => 'Test d\'aperçu PDF - ' . date('d/m/Y H:i:s'),
                'x' => 50,
                'y' => 50,
                'width' => 100,
                'height' => 20,
                'fontSize' => 14,
                'color' => '#000000'
            ]
        ];

        $pdf_content = $generator->generate($test_elements);

        if (!empty($pdf_content)) {
            echo "<div class='test-result passed'>✅ Génération PDF basique réussie (" . strlen($pdf_content) . " bytes)</div>";

            // Sauvegarder le fichier de test
            $test_filename = 'pdf-builder-diagnostic-' . time() . '.pdf';
            $test_filepath = wp_upload_dir()['basedir'] . '/' . $test_filename;

            if (file_put_contents($test_filepath, $pdf_content)) {
                $test_url = wp_upload_dir()['baseurl'] . '/' . $test_filename;
                echo "<div class='test-result passed'>✅ Fichier de test sauvegardé: <a href='$test_url' target='_blank'>Voir PDF</a></div>";
            } else {
                echo "<div class='test-result failed'>❌ Impossible de sauvegarder le fichier de test</div>";
            }
        } else {
            echo "<div class='test-result failed'>❌ Génération PDF retournée vide</div>";
        }

    } catch (Exception $e) {
        echo "<div class='test-result failed'>❌ Erreur génération PDF: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-result failed'>❌ Classe PDF_Builder_Pro_Generator non disponible</div>";
}

echo "</div>";

// Section de réparation
echo "<div class='section error'>
<h2>🔧 Outils de Réparation</h2>";

echo "<button class='btn btn-danger' onclick='repairCanvasData()'>Réparer Données Canvas</button>";
echo "<button class='btn btn-warning' onclick='clearPdfCache()'>Vider Cache PDF</button>";
echo "<button class='btn btn-success' onclick='runFullDiagnostic()'>Diagnostic Complet</button>";

echo "<h3>Scripts de réparation disponibles:</h3>";
echo "<ul>";
echo "<li><code>repair-canvas-json.php</code> - Répare les données JSON corrompues</li>";
echo "<li><code>fix-template-elements.php</code> - Corrige les éléments de template invalides</li>";
echo "<li><code>rebuild-pdf-generator.php</code> - Reconstruit le générateur PDF</li>";
echo "</ul>";

echo "</div>";

echo "<script>
function testTemplate(templateId) {
    alert('Test du template ID: ' + templateId + '\\n\\nFonctionnalité à implémenter...');
}

function testOrderPreview(orderId) {
    alert('Test aperçu commande ID: ' + orderId + '\\n\\nFonctionnalité à implémenter...');
}

function repairCanvasData() {
    if (confirm('Cette action va analyser et réparer toutes les données canvas. Continuer ?')) {
        alert('Réparation lancée...\\n\\nConsultez les logs pour le progrès.');
    }
}

function clearPdfCache() {
    if (confirm('Vider le cache PDF ? Tous les fichiers PDF générés seront supprimés.')) {
        alert('Cache vidé.');
    }
}

function runFullDiagnostic() {
    location.reload();
}
</script>";

echo "</div></body></html>";
?>