<?php
/**
 * Auto-Activation Script - Active automatiquement le plugin PDF Builder Pro
 */

// Test basique sans WordPress
echo "<h1>🚀 Auto-Activation PDF Builder Pro</h1>";
echo "<p><strong>Date:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<hr>";

// Test 1: Inclusion de WordPress
echo "<h2>1. 📘 Chargement WordPress</h2>";
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

if (file_exists($wp_load_path)) {
    echo "✅ wp-load.php trouvé<br>";
    require_once $wp_load_path;
    echo "✅ WordPress chargé<br>";
} else {
    echo "❌ wp-load.php introuvable à: $wp_load_path<br>";
    exit;
}

echo "<hr>";

// Test 2: Vérification état actuel
echo "<h2>2. 🔍 État Actuel du Plugin</h2>";
$active_plugins = get_option('active_plugins', array());
$plugin_file = 'wp-pdf-builder-pro/pdf-builder-pro.php';

$already_active = in_array($plugin_file, $active_plugins);
echo ($already_active ? "✅" : "❌") . " Plugin déjà actif<br>";

if ($already_active) {
    echo "<strong>🎉 Le plugin est déjà activé !</strong><br>";
    echo "<a href='test-simple.php'>➡️ Testez maintenant</a><br>";
    exit;
}

echo "<hr>";

// Test 3: Tentative d'activation
echo "<h2>3. ⚡ Activation du Plugin</h2>";

if (function_exists('activate_plugin')) {
    echo "✅ Fonction activate_plugin disponible<br>";

    try {
        $result = activate_plugin($plugin_file, '', false, false);

        if (is_wp_error($result)) {
            echo "❌ Échec activation: " . $result->get_error_message() . "<br>";
            echo "<strong>Détails:</strong><br>";
            echo "<pre>" . print_r($result->errors, true) . "</pre>";
        } else {
            echo "✅ Plugin activé avec succès !<br>";

            // Vérification
            $active_plugins = get_option('active_plugins', array());
            $now_active = in_array($plugin_file, $active_plugins);
            echo ($now_active ? "✅" : "❌") . " Confirmation activation<br>";
        }
    } catch (Exception $e) {
        echo "❌ Exception lors activation: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Fonction activate_plugin non disponible<br>";
}

echo "<hr>";

// Test 4: Test rapide du plugin
echo "<h2>4. 🧪 Test Rapide</h2>";
if (function_exists('pdf_builder_init')) {
    echo "✅ Fonction pdf_builder_init existe (plugin chargé)<br>";
} else {
    echo "❌ Fonction pdf_builder_init introuvable<br>";
}

echo "<hr>";

// Instructions finales
echo "<h2>🎯 Prochaines Étapes</h2>";
echo "<ol>";
echo "<li><strong>Si activation réussie:</strong> <a href='test-simple.php'>Testez le plugin</a></li>";
echo "<li><strong>Si échec:</strong> Activez manuellement dans wp-admin/plugins.php</li>";
echo "<li><strong>Vérifiez les erreurs:</strong> Consultez les logs WordPress</li>";
echo "</ol>";

echo "<p><em>Auto-activation terminée à " . date('H:i:s') . "</em></p>";
?></content>
<parameter name="filePath">d:\wp-pdf-builder-pro\plugin\auto-activate.php