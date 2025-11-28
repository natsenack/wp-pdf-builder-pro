<?php
/**
 * Diagnostic WordPress - Test du chargement du plugin PDF Builder Pro
 * Ce script charge WordPress puis teste le plugin
 */

// Chemin vers WordPress (à adapter selon votre installation)
$wp_load_path = dirname(dirname(__FILE__)) . '/wp-load.php';

echo "<h1>🔍 Diagnostic WordPress - PDF Builder Pro</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Script:</strong> " . __FILE__ . "</p>";
echo "<p><strong>WP Load Path:</strong> $wp_load_path</p>";

// Test 1: Charger WordPress
echo "<h2>Test 1: Chargement WordPress</h2>";

if (file_exists($wp_load_path)) {
    echo "<p>✅ Fichier wp-load.php trouvé</p>";

    try {
        require_once($wp_load_path);
        echo "<p>✅ WordPress chargé avec succès</p>";
        echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
        echo "<p><strong>ABSPATH:</strong> " . ABSPATH . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception lors du chargement de WordPress: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    } catch (Error $e) {
        echo "<p style='color: red;'>❌ Erreur fatale lors du chargement de WordPress: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
} else {
    echo "<p style='color: red;'>❌ Fichier wp-load.php NON trouvé à: $wp_load_path</p>";
    echo "<p><strong>Corrigez le chemin dans le script</strong></p>";
    exit;
}

// Test 2: Vérifier les fonctions WordPress
echo "<h2>Test 2: Fonctions WordPress</h2>";
$wp_functions = ['get_option', 'wp_enqueue_script', 'add_action', 'register_activation_hook'];

$missing = [];
foreach ($wp_functions as $func) {
    if (!function_exists($func)) {
        $missing[] = $func;
    }
}

if (empty($missing)) {
    echo "<p style='color: green;'>✅ Toutes les fonctions WordPress de base sont disponibles</p>";
} else {
    echo "<p style='color: red;'>❌ Fonctions manquantes: " . implode(', ', $missing) . "</p>";
}

// Test 3: Vérifier les fichiers du plugin
echo "<h2>Test 3: Fichiers du plugin</h2>";
$plugin_dir = plugin_dir_path(__FILE__);
$plugin_files = [
    'pdf-builder-pro.php',
    'bootstrap.php',
    'core/autoloader.php'
];

foreach ($plugin_files as $file) {
    $path = $plugin_dir . $file;
    if (file_exists($path)) {
        echo "<p style='color: green;'>✅ $file (" . filesize($path) . " bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ $file MANQUANT</p>";
    }
}

// Test 4: Tester le chargement du plugin
echo "<h2>Test 4: Chargement du plugin PDF Builder Pro</h2>";

try {
    $main_file = $plugin_dir . 'pdf-builder-pro.php';

    if (file_exists($main_file)) {
        echo "<p>🔄 Tentative d'inclusion de pdf-builder-pro.php...</p>";

        ob_start();
        $result = include_once($main_file);
        $output = ob_get_clean();

        if ($result === false) {
            echo "<p style='color: red;'>❌ ERREUR lors de l'inclusion</p>";
        } else {
            echo "<p style='color: green;'>✅ Plugin inclus avec succès</p>";
            if (!empty($output)) {
                echo "<p><strong>Sortie:</strong> <pre>" . htmlspecialchars($output) . "</pre></p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Fichier principal introuvable</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Erreur fatale: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 5: Vérifier les classes
echo "<h2>Test 5: Classes du plugin</h2>";
$classes = [
    'PDF_Builder_Update_Manager',
    'PDF_Builder_Metrics_Analytics',
    'PDF_Builder_Intelligent_Loader'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✅ Classe $class disponible</p>";
    } else {
        echo "<p style='color: red;'>❌ Classe $class MANQUANTE</p>";
    }
}

// Test 6: Plugins actifs
echo "<h2>Test 6: État du plugin</h2>";
$active_plugins = get_option('active_plugins', []);

$pdf_builder_active = false;
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'wp-pdf-builder-pro') !== false) {
        $pdf_builder_active = true;
        echo "<p style='color: green;'>✅ PDF Builder Pro est ACTIVÉ: $plugin</p>";
        break;
    }
}

if (!$pdf_builder_active) {
    echo "<p style='color: red;'>❌ PDF Builder Pro n'est PAS activé</p>";
    echo "<p><strong>Plugins actifs (" . count($active_plugins) . "):</strong></p>";
    echo "<ul>";
    foreach ($active_plugins as $plugin) {
        echo "<li>$plugin</li>";
    }
    echo "</ul>";
}

// Test 7: Erreurs récentes
echo "<h2>Test 7: Erreurs PHP récentes</h2>";
$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -15);

    echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; max-height: 200px; overflow-y: auto;'>";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color: orange;'>⚠️ Log PHP non accessible</p>";
}

echo "<hr>";
echo "<h2>🔧 Résumé</h2>";
echo "<p>Si vous voyez des erreurs rouges, elles indiquent le problème causant la page blanche.</p>";
echo "<p><strong>URL d'accès direct:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
?></content>
<parameter name="filePath">i:\wp-pdf-builder-pro\plugin\diagnostic-full.php