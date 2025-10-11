<?php
// Script de diagnostic pour vérifier les modifications du nonce
// Accessible via: https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/diagnostic.php?run=1

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Résultats du Diagnostic PDF Builder</h1>\n";
echo "<h2>Informations système</h2>\n";
echo "<pre>\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current file: " . __FILE__ . "\n";
echo "Current dir: " . __DIR__ . "\n";
echo "Run parameter: " . (isset($_GET['run']) ? $_GET['run'] : 'not set') . "\n";
echo "</pre>\n";

if (isset($_GET['run']) && $_GET['run'] === '1') {
    echo "<h2>Résultats du diagnostic</h2>\n";
    echo "<pre>\n";

    try {
        // Vérifier si la fonction ajax_load_canvas_elements existe et contient nos modifications
        $admin_file = __DIR__ . '/includes/classes/class-pdf-builder-admin-new.php';
        echo "Checking file: $admin_file\n";

        if (file_exists($admin_file)) {
            echo "✅ Fichier admin trouvé\n";

            $content = file_get_contents($admin_file);
            if ($content === false) {
                echo "❌ Erreur lors de la lecture du fichier\n";
            } else {
                echo "✅ Fichier lu avec succès (" . strlen($content) . " caractères)\n";

                if (strpos($content, '_cachebust_') !== false) {
                    echo "✅ Modifications _cachebust_ présentes\n";
                } else {
                    echo "❌ Modifications _cachebust_ ABSENTES\n";
                }

                if (strpos($content, 'wp_create_nonce(\'pdf_builder_canvas_v4_') !== false) {
                    echo "✅ Nouveau nonce trouvé (v4)\n";
                } else {
                    echo "❌ Nouveau nonce ABSENT\n";
                }

                // Compter les occurrences
                $cachebust_count = substr_count($content, '_cachebust_');
                echo "Nombre d'occurrences _cachebust_: $cachebust_count\n";
            }

        } else {
            echo "❌ Fichier admin NON trouvé: $admin_file\n";
        }

        echo "\n=== TEST NONCE ===\n";
        // Tester la génération du nonce
        if (function_exists('wp_create_nonce')) {
            echo "✅ Fonction wp_create_nonce disponible\n";
            $test_nonce = wp_create_nonce('pdf_builder_canvas_v3_' . 1 . '_cachebust_' . time());
            echo "Test nonce généré: " . substr($test_nonce, 0, 10) . "...\n";
            echo "Longueur: " . strlen($test_nonce) . "\n";
        } else {
            echo "❌ Fonction wp_create_nonce non disponible\n";
        }

    } catch (Exception $e) {
        echo "❌ ERREUR: " . $e->getMessage() . "\n";
    }

    echo "</pre>\n";
    exit;
}

// Si pas de paramètre run, afficher un message d'aide
echo '<h2>Instructions</h2>';
echo '<p>Ajoutez ?run=1 à l\'URL pour exécuter le diagnostic</p>';
echo '<p>Exemple: <code>' . $_SERVER['REQUEST_URI'] . '?run=1</code></p>';
?>