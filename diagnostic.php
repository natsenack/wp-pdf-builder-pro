<?php
// Script de diagnostic pour vérifier les modifications du nonce
// Accessible via: https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/diagnostic.php?run=1

if (isset($_GET['run']) && $_GET['run'] === '1') {
    echo "<h1>Résultats du Diagnostic PDF Builder</h1>\n";
    echo "<pre>\n";

    // Vérifier si la fonction ajax_load_canvas_elements existe et contient nos modifications
    $admin_file = __DIR__ . '/includes/classes/class-pdf-builder-admin.php';
    if (file_exists($admin_file)) {
        $content = file_get_contents($admin_file);

        echo "✅ Fichier admin trouvé\n";

        if (strpos($content, '_cachebust_') !== false) {
            echo "✅ Modifications _cachebust_ présentes\n";
        } else {
            echo "❌ Modifications _cachebust_ ABSENTES\n";
        }

        if (strpos($content, 'wp_create_nonce(\'pdf_builder_canvas_v3_') !== false) {
            echo "✅ Nouveau nonce trouvé\n";
        } else {
            echo "❌ Nouveau nonce ABSENT\n";
        }

        // Compter les occurrences
        $cachebust_count = substr_count($content, '_cachebust_');
        echo "Nombre d'occurrences _cachebust_: $cachebust_count\n";

    } else {
        echo "❌ Fichier admin NON trouvé: $admin_file\n";
    }

    echo "\n=== TEST NONCE ===\n";
    // Tester la génération du nonce
    if (function_exists('wp_create_nonce')) {
        $test_nonce = wp_create_nonce('pdf_builder_canvas_v3_' . 1 . '_cachebust_' . time());
        echo "Test nonce généré: " . substr($test_nonce, 0, 10) . "...\n";
        echo "Longueur: " . strlen($test_nonce) . "\n";
    } else {
        echo "❌ Fonction wp_create_nonce non disponible\n";
    }

    echo "\n=== CONTENU RECENT DU DEBUG.LOG ===\n";

    $debug_log = '/var/www/nats/data/www/threeaxe.fr/wp-content/debug.log';
    if (file_exists($debug_log)) {
        $lines = file($debug_log);
        $recent_lines = array_slice($lines, -10); // Dernières 10 lignes
        foreach ($recent_lines as $line) {
            echo htmlspecialchars($line);
        }
    } else {
        echo "❌ Fichier debug.log non trouvé: $debug_log\n";
    }

    echo "</pre>\n";
    exit;
}

// Si pas de paramètre run, afficher un message d'aide
echo '<h1>PDF Builder Diagnostic</h1>';
echo '<p>Ajoutez ?run=1 à l\'URL pour exécuter le diagnostic</p>';
echo '<p>Exemple: <code>' . $_SERVER['REQUEST_URI'] . '?run=1</code></p>';
?>