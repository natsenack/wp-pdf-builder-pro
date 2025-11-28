<?php
/**
 * SCRIPT POUR DÉSACTIVER TEMPORAIREMENT LE PLUGIN PDF BUILDER PRO
 * À exécuter si le diagnostic montre que c'est le plugin qui cause la page blanche
 */

// Activer les erreurs pour voir ce qui se passe
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>DÉSACTIVATION PLUGIN PDF BUILDER</title></head><body>";
echo "<h1>🔧 DÉSACTIVATION TEMPORAIRE DU PLUGIN PDF BUILDER PRO</h1>";

// Vérifier si on peut accéder à WordPress
if (!defined('ABSPATH')) {
    echo "<p>❌ ABSPATH non défini - impossible d'accéder à WordPress</p>";
    exit;
}

require_once ABSPATH . 'wp-load.php';

if (!function_exists('deactivate_plugins')) {
    echo "<p>❌ Fonction deactivate_plugins non disponible</p>";
    exit;
}

echo "<h2>📋 État actuel des plugins</h2>";

// Lister les plugins actifs
$active_plugins = get_option('active_plugins', []);
echo "<h3>Plugins actifs:</h3>";
echo "<ul>";
foreach ($active_plugins as $plugin) {
    echo "<li>$plugin</li>";
}
echo "</ul>";

// Chercher notre plugin
$plugin_to_deactivate = 'wp-pdf-builder-pro/pdf-builder-pro.php';
$found = false;

foreach ($active_plugins as $key => $plugin) {
    if ($plugin === $plugin_to_deactivate) {
        $found = true;
        break;
    }
}

if ($found) {
    echo "<h2>🔄 Désactivation du plugin...</h2>";

    // Désactiver le plugin
    deactivate_plugins($plugin_to_deactivate);

    echo "<p>✅ Plugin PDF Builder Pro désactivé avec succès!</p>";

    // Vérifier que c'est bien désactivé
    $active_plugins_after = get_option('active_plugins', []);
    $still_active = in_array($plugin_to_deactivate, $active_plugins_after);

    if (!$still_active) {
        echo "<p>✅ Confirmation: Le plugin n'est plus dans la liste des plugins actifs.</p>";
        echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #2e7d32;'>🎉 SUCCÈS!</h3>";
        echo "<p>Le plugin PDF Builder Pro a été désactivé. Testez maintenant votre site WordPress :</p>";
        echo "<p><strong><a href='https://threeaxe.fr' target='_blank'>https://threeaxe.fr</a></strong></p>";
        echo "<p>Si la page blanche disparaît, alors le problème venait bien du plugin.</p>";
        echo "</div>";
    } else {
        echo "<p>❌ Échec de la désactivation - le plugin est toujours actif</p>";
    }

} else {
    echo "<h2>ℹ️ Plugin déjà inactif</h2>";
    echo "<p>Le plugin PDF Builder Pro n'était pas actif.</p>";
}

echo "<h2>🔄 Actions suivantes</h2>";
echo "<ul>";
echo "<li><strong>Testez votre site:</strong> <a href='https://threeaxe.fr' target='_blank'>https://threeaxe.fr</a></li>";
echo "<li><strong>Si la page blanche disparaît:</strong> Le problème venait du plugin - nous pourrons le réactiver après correction</li>";
echo "<li><strong>Si la page blanche persiste:</strong> Le problème vient d'ailleurs (autre plugin, thème, configuration serveur)</li>";
echo "</ul>";

echo "<p><a href='https://threeaxe.fr/wp-admin/plugins.php' target='_blank'>Aller à la page des plugins</a></p>";

echo "</body></html>";
?>