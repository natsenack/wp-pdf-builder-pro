<?php
/**
 * Activation temporaire des logs de débogage PHP
 */

// Simuler un environnement WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

require_once('../../../wp-load.php');

echo "<h1>🔧 Activation des logs de débogage PHP</h1>";

// Activer temporairement les logs de débogage
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}
if (!defined('WP_DEBUG_LOG')) {
    define('WP_DEBUG_LOG', true);
}

echo "<p>✅ WP_DEBUG activé</p>";
echo "<p>✅ WP_DEBUG_LOG activé</p>";

// Vérifier si le fichier de log existe
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "<p>📁 Fichier de log trouvé: <code>$log_file</code></p>";

    // Afficher les dernières lignes du log
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $last_lines = array_slice($lines, -20); // Dernières 20 lignes

    echo "<h2>Dernières entrées du log:</h2>";
    echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ccc;max-height:400px;overflow:auto;'>";
    foreach ($last_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>❌ Fichier de log non trouvé: <code>$log_file</code></p>";
    echo "<p>ℹ️ Le fichier sera créé automatiquement lors de la première erreur loggée.</p>";
}

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Actualisez la page de commande WooCommerce</li>";
echo "<li>Cliquez sur '👁️ Aperçu PDF'</li>";
echo "<li>Revenez sur cette page pour voir les nouveaux logs</li>";
echo "</ol>";
?>