<?php
/**
 * Lanceur rapide des tests PDF Builder Pro
 *
 * @package PDF_Builder_Pro
 * @version 1.0
 * @since 5.6
 */

// Définition des chemins
define('TESTS_DIR', __DIR__ . '/unit');
define('SRC_DIR', __DIR__ . '/../src');

// Fonction pour exécuter un fichier de test
function run_test_file($test_file) {
    $full_path = TESTS_DIR . '/' . $test_file;

    if (!file_exists($full_path)) {
        echo "❌ Fichier de test introuvable: $test_file\n";
        return false;
    }

    echo "\n🧪 Exécution de $test_file...\n";
    echo str_repeat("-", 50) . "\n";

    $command = "php \"$full_path\"";
    $output = shell_exec($command);

    if ($output === null) {
        echo "❌ Erreur lors de l'exécution de $test_file\n";
        return false;
    }

    echo $output;
    return true;
}

// Fonction pour scanner les fichiers de test
function get_test_files() {
    $test_files = [];

    if (is_dir(TESTS_DIR)) {
        $files = scandir(TESTS_DIR);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' &&
                strpos($file, '_Test.php') !== false &&
                strpos($file, 'Standalone') === false) { // Exclure les tests standalone temporaires
                $test_files[] = $file;
            }
        }
    }

    return $test_files;
}

// Fonction principale
function main() {
    echo "🚀 LANCEUR DE TESTS PDF BUILDER PRO\n";
    echo "===================================\n\n";

    // Vérifier si un test spécifique est demandé
    $specific_test = $argv[1] ?? null;

    if ($specific_test) {
        if (run_test_file($specific_test)) {
            echo "\n✅ Test terminé\n";
        } else {
            echo "\n❌ Échec du test\n";
            exit(1);
        }
    } else {
        // Exécuter tous les tests
        $test_files = get_test_files();

        if (empty($test_files)) {
            echo "❌ Aucun fichier de test trouvé dans " . TESTS_DIR . "\n";
            exit(1);
        }

        echo "Tests trouvés: " . count($test_files) . "\n";
        echo implode(", ", $test_files) . "\n\n";

        $success_count = 0;
        $total_count = count($test_files);

        foreach ($test_files as $test_file) {
            if (run_test_file($test_file)) {
                $success_count++;
            }
        }

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 RÉSULTATS GÉNÉRAUX\n";
        echo str_repeat("=", 50) . "\n";
        echo "Tests réussis: $success_count/$total_count\n";

        if ($success_count === $total_count) {
            echo "🎉 TOUS LES TESTS SONT RÉUSSIS !\n";
            exit(0);
        } else {
            echo "⚠️ Certains tests ont échoué\n";
            exit(1);
        }
    }
}

// Exécuter si appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    main();
}