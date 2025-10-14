<?php
/**
 * Activation temporaire des logs de débogage PHP - Version optimisée
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

    // Obtenir la taille du fichier
    $file_size = filesize($log_file);
    echo "<p>📊 Taille du fichier: " . number_format($file_size / 1024 / 1024, 2) . " MB</p>";

    // Lire seulement les dernières lignes pour éviter l'épuisement mémoire
    echo "<h2>Dernières entrées du log (mémoire optimisée):</h2>";
    echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ccc;max-height:400px;overflow:auto;'>";

    // Utiliser une commande shell pour lire les dernières lignes
    $command = "tail -50 " . escapeshellarg($log_file);
    $last_lines = shell_exec($command);

    if ($last_lines) {
        echo htmlspecialchars($last_lines);
    } else {
        // Fallback: lire le fichier ligne par ligne en sens inverse
        echo "Utilisation de la méthode PHP alternative...\n";

        $lines = [];
        $handle = fopen($log_file, "r");
        if ($handle) {
            // Lire les 50 dernières lignes
            $line_count = 0;
            $max_lines = 50;

            // Aller à la fin du fichier
            fseek($handle, 0, SEEK_END);
            $pos = ftell($handle);

            // Lire en arrière
            while ($pos > 0 && $line_count < $max_lines) {
                $pos--;
                fseek($handle, $pos, SEEK_SET);
                if (fgetc($handle) === "\n") {
                    $line_count++;
                }
            }

            // Lire les lignes trouvées
            while (($line = fgets($handle)) !== false && count($lines) < $max_lines) {
                $lines[] = trim($line);
            }

            fclose($handle);

            // Afficher les lignes (elles seront dans l'ordre inverse)
            $lines = array_reverse($lines);
            foreach ($lines as $line) {
                if (!empty($line)) {
                    echo htmlspecialchars($line) . "\n";
                }
            }
        } else {
            echo "❌ Impossible d'ouvrir le fichier de log\n";
        }
    }

    echo "</pre>";

    // Chercher spécifiquement les erreurs PDF BUILDER
    echo "<h2>🔍 Erreurs PDF BUILDER récentes:</h2>";
    echo "<pre style='background:#ffe6e6;padding:10px;border:1px solid #ffcccc;max-height:200px;overflow:auto;'>";

    $pdf_errors = shell_exec("grep -i 'pdf builder' " . escapeshellarg($log_file) . " | tail -10");
    if ($pdf_errors) {
        echo htmlspecialchars($pdf_errors);
    } else {
        echo "Aucune erreur PDF BUILDER trouvée dans les logs récents.\n";
    }

    echo "</pre>";

} else {
    echo "<p>❌ Fichier de log non trouvé: <code>$log_file</code></p>";
    echo "<p>ℹ️ Le fichier sera créé automatiquement lors de la première erreur loggée.</p>";
}

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Actualisez cette page après avoir testé l'erreur</li>";
echo "<li>Les logs PDF BUILDER apparaîtront dans la section rouge ci-dessus</li>";
echo "<li>Partagez-moi les erreurs trouvées</li>";
echo "</ol>";
?>