<?php
/**
 * Test Plugin Status - Vérifie si le plugin est activé dans WordPress
 */

// Test basique sans WordPress
echo "<h1>🔍 Test État du Plugin PDF Builder Pro</h1>";
echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Test 1: Inclusion basique de WordPress
echo "<h2>1. 📘 Chargement WordPress</h2>";
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
echo "🔍 Chemin wp-load.php: $wp_load_path<br>";

if (file_exists($wp_load_path)) {
    echo "✅ wp-load.php trouvé<br>";
    try {
        require_once $wp_load_path;
        echo "✅ WordPress chargé<br>";
    } catch (Exception $e) {
        echo "❌ Erreur chargement WordPress: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ wp-load.php introuvable<br>";
}

echo "<hr>";

// Test 2: État du plugin (si WordPress chargé)
echo "<h2>2. 🔌 État du Plugin</h2>";
if (function_exists('get_option')) {
    echo "✅ Fonctions WordPress disponibles<br>";

    // Vérifier plugins actifs
    $active_plugins = get_option('active_plugins', array());
    echo "📦 Plugins actifs trouvés: " . count($active_plugins) . "<br>";

    $pdf_plugin_found = false;
    $pdf_plugin_active = false;

    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'pdf-builder') !== false) {
            echo "📝 Plugin trouvé: $plugin<br>";
            $pdf_plugin_found = true;
            if (strpos($plugin, 'wp-pdf-builder-pro') !== false) {
                $pdf_plugin_active = true;
                echo "✅ PDF Builder Pro ACTIF<br>";
            }
        }
    }

    if (!$pdf_plugin_found) {
        echo "❌ Aucun plugin PDF Builder trouvé dans les actifs<br>";
        echo "<strong>💡 Action:</strong> Allez dans wp-admin/plugins.php et activez 'PDF Builder Pro'<br>";
    }

    if ($pdf_plugin_found && !$pdf_plugin_active) {
        echo "⚠️ Plugin PDF trouvé mais pas le bon<br>";
    }

} else {
    echo "❌ Fonctions WordPress non disponibles<br>";
    echo "<strong>💡 Action:</strong> Le fichier wp-load.php n'est pas accessible<br>";
}

echo "<hr>";

// Test 3: Test manuel d'activation
echo "<h2>3. 🧪 Test Manuel d'Activation</h2>";
if (function_exists('activate_plugin')) {
    echo "✅ Fonction activate_plugin disponible<br>";

    $plugin_file = 'wp-pdf-builder-pro/pdf-builder-pro.php';
    $result = activate_plugin($plugin_file);

    if (is_wp_error($result)) {
        echo "❌ Échec activation: " . $result->get_error_message() . "<br>";
    } else {
        echo "✅ Plugin activé avec succès<br>";
        echo "<strong>🔄 Actualisez la page</strong> pour voir les changements<br>";
    }
} else {
    echo "❌ Fonction activate_plugin non disponible<br>";
}

echo "<hr>";

// Test 4: Diagnostic des chemins
echo "<h2>4. 📁 Diagnostic des Chemins</h2>";
echo "📍 __FILE__: " . __FILE__ . "<br>";
echo "📍 __DIR__: " . __DIR__ . "<br>";
echo "📍 plugin_dir_path: " . plugin_dir_path(__FILE__) . "<br>";
echo "📍 plugin_dir_url: " . plugin_dir_url(__FILE__) . "<br>";

echo "<hr>";

// Instructions
echo "<h2>🎯 Instructions</h2>";
echo "<ol>";
echo "<li><strong>Si plugin non actif:</strong> Allez dans wp-admin/plugins.php</li>";
echo "<li><strong>Activez le plugin:</strong> 'PDF Builder Pro'</li>";
echo "<li><strong>Revenez ici:</strong> Rechargez cette page</li>";
echo "<li><strong>Testez ensuite:</strong> <a href='test-simple.php'>test-simple.php</a></li>";
echo "</ol>";

echo "<p><em>Diagnostic terminé à " . date('H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">d:\wp-pdf-builder-pro\plugin\test-plugin-status.php