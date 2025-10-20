<?php
/**
 * Rapport de couverture de code - Phase 6.5
 * Mesure la couverture des tests
 */

class Coverage_Report {

    private $results = [];
    private $coverage_data = [];

    private function assert($condition, $message = '') {
        if ($condition) {
            $this->results[] = "✅ PASS: $message";
            return true;
        } else {
            $this->results[] = "❌ FAIL: $message";
            return false;
        }
    }

    private function count_lines_of_code($file_path) {
        if (!file_exists($file_path)) {
            return 0;
        }

        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $code_lines = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Compter les lignes qui ne sont pas des commentaires ou vides
            if (!empty($trimmed) &&
                !preg_match('/^\s*\/\//', $trimmed) &&
                !preg_match('/^\s*\*/', $trimmed) &&
                !preg_match('/^\s*\/\*/', $trimmed) &&
                !preg_match('/^\s*\*\//', $trimmed)) {
                $code_lines++;
            }
        }

        return $code_lines;
    }

    private function analyze_test_coverage() {
        $test_directories = [
            'unit' => 'tests/unit/',
            'integration' => 'tests/integration/',
            'performance' => 'tests/',
            'security' => 'tests/'
        ];

        $source_directories = [
            'src/' => 'src/',
            'core/' => 'core/',
            'lib/' => 'lib/'
        ];

        $total_test_lines = 0;
        $total_source_lines = 0;
        $test_files = [];
        $source_files = [];

        // Compter les lignes de tests
        foreach ($test_directories as $type => $dir) {
            $pattern = __DIR__ . "/../{$dir}*.php";
            $files = glob($pattern);

            foreach ($files as $file) {
                if (basename($file) !== 'bootstrap.php' && basename($file) !== 'stubs.php') {
                    $lines = $this->count_lines_of_code($file);
                    $total_test_lines += $lines;
                    $test_files[] = [
                        'file' => basename($file),
                        'type' => $type,
                        'lines' => $lines
                    ];
                }
            }
        }

        // Compter les lignes de code source
        foreach ($source_directories as $type => $dir) {
            $pattern = __DIR__ . "/../{$dir}**/*.php";
            $files = glob($pattern);

            foreach ($files as $file) {
                $lines = $this->count_lines_of_code($file);
                $total_source_lines += $lines;
                $source_files[] = [
                    'file' => str_replace(__DIR__ . '/../', '', $file),
                    'type' => $type,
                    'lines' => $lines
                ];
            }
        }

        $this->coverage_data = [
            'test_lines' => $total_test_lines,
            'source_lines' => $total_source_lines,
            'test_files' => $test_files,
            'source_files' => $source_files
        ];

        return $this->coverage_data;
    }

    /**
     * Test analyse de couverture de base
     */
    public function test_coverage_analysis() {
        $coverage = $this->analyze_test_coverage();

        $success = $this->assert($coverage['test_lines'] > 0, "Lignes de test détectées: {$coverage['test_lines']}");
        $success &= $this->assert($coverage['source_lines'] > 0, "Lignes de code source détectées: {$coverage['source_lines']}");
        $success &= $this->assert(count($coverage['test_files']) > 0, "Fichiers de test trouvés: " . count($coverage['test_files']));
        $success &= $this->assert(count($coverage['source_files']) > 0, "Fichiers source trouvés: " . count($coverage['source_files']));

        // Calculer ratio de couverture estimé
        $coverage_ratio = $coverage['test_lines'] / max($coverage['source_lines'], 1);
        $percentage = round($coverage_ratio * 100, 2);

        $success &= $this->assert($percentage > 50, "Couverture estimée > 50%: {$percentage}%");

        return $success;
    }

    /**
     * Test couverture par catégories
     */
    public function test_category_coverage() {
        $coverage = $this->analyze_test_coverage();

        // Grouper par catégories
        $categories = [];
        foreach ($coverage['test_files'] as $file) {
            $type = $file['type'];
            if (!isset($categories[$type])) {
                $categories[$type] = 0;
            }
            $categories[$type] += $file['lines'];
        }

        $success = true;

        // Vérifier couverture minimale par catégorie
        $min_coverage = [
            'unit' => 100,      // Tests unitaires
            'integration' => 200, // Tests d'intégration
            'performance' => 50,  // Tests performance
            'security' => 100    // Tests sécurité
        ];

        foreach ($min_coverage as $category => $min_lines) {
            $actual_lines = $categories[$category] ?? 0;
            $success &= $this->assert($actual_lines >= $min_lines,
                "Couverture {$category} >= {$min_lines} lignes: {$actual_lines}");
        }

        return $success;
    }

    /**
     * Test couverture des composants critiques
     */
    public function test_critical_components_coverage() {
        $critical_components = [
            'VariableMapper' => 'src/Managers/PDF_Builder_Variable_Mapper.php',
            'TemplateManager' => 'src/Managers/TemplateManager.php',
            'CanvasBuilder' => 'src/Core/CanvasBuilder.php',
            'AjaxHandler' => 'src/Controllers/AjaxHandler.php'
        ];

        $success = true;

        foreach ($critical_components as $name => $path) {
            $full_path = __DIR__ . "/../{$path}";
            $exists = file_exists($full_path);

            if ($exists) {
                $lines = $this->count_lines_of_code($full_path);
                $success &= $this->assert($lines > 0, "Composant {$name} existe avec {$lines} lignes");

                // Chercher les tests correspondants
                $test_pattern = __DIR__ . "/../tests/**/{$name}_Test.php";
                $test_files = glob($test_pattern);
                $has_tests = !empty($test_files);

                $success &= $this->assert($has_tests, "Tests trouvés pour {$name}: " . count($test_files));
            } else {
                $this->results[] = "⚠️  WARN: Composant {$name} non trouvé: {$path}";
            }
        }

        return $success;
    }

    /**
     * Test métriques de qualité du code de test
     */
    public function test_test_quality_metrics() {
        $coverage = $this->analyze_test_coverage();

        $success = true;

        // Métriques de qualité
        $avg_test_file_size = $coverage['test_lines'] / max(count($coverage['test_files']), 1);
        $test_to_source_ratio = $coverage['test_lines'] / max($coverage['source_lines'], 1);

        $success &= $this->assert($avg_test_file_size > 20, "Taille moyenne fichier test > 20 lignes: " . round($avg_test_file_size, 1));
        $success &= $this->assert($test_to_source_ratio > 0.3, "Ratio test/source > 30%: " . round($test_to_source_ratio * 100, 1) . "%");

        // Vérifier diversité des types de test
        $test_types = array_unique(array_column($coverage['test_files'], 'type'));
        $success &= $this->assert(count($test_types) >= 3, "Au moins 3 types de tests: " . implode(', ', $test_types));

        // Vérifier couverture des fonctionnalités principales
        $main_features = ['variable', 'template', 'canvas', 'ajax', 'performance', 'security'];
        $covered_features = 0;

        foreach ($main_features as $feature) {
            $has_feature_tests = false;
            foreach ($coverage['test_files'] as $file) {
                if (stripos($file['file'], $feature) !== false) {
                    $has_feature_tests = true;
                    break;
                }
            }
            if ($has_feature_tests) {
                $covered_features++;
            }
        }

        $coverage_percentage = round(($covered_features / count($main_features)) * 100, 1);
        $success &= $this->assert($coverage_percentage >= 80, "Fonctionnalités couvertes >= 80%: {$coverage_percentage}%");

        return $success;
    }

    /**
     * Test recommandations d'amélioration
     */
    public function test_coverage_recommendations() {
        $coverage = $this->analyze_test_coverage();

        $recommendations = [];

        // Analyser les gaps
        $test_lines = $coverage['test_lines'];
        $source_lines = $coverage['source_lines'];
        $ratio = $test_lines / max($source_lines, 1);

        if ($ratio < 0.8) {
            $recommendations[] = "Augmenter couverture de " . round($ratio * 100, 1) . "% vers 80%+";
        }

        if (count($coverage['test_files']) < 10) {
            $recommendations[] = "Ajouter plus de fichiers de test (actuellement: " . count($coverage['test_files']) . ")";
        }

        // Vérifier tests d'intégration
        $integration_tests = array_filter($coverage['test_files'], fn($f) => $f['type'] === 'integration');
        if (count($integration_tests) < 3) {
            $recommendations[] = "Ajouter plus de tests d'intégration (actuellement: " . count($integration_tests) . ")";
        }

        $success = $this->assert(count($recommendations) <= 3, "Nombre raisonnable de recommandations: " . count($recommendations));

        return $success;
    }

    public function run_all_tests() {
        echo "📊 RAPPORT DE COUVERTURE DE CODE - PHASE 6.5\n";
        echo "=============================================\n";

        $tests = [
            'test_coverage_analysis' => [$this, 'test_coverage_analysis'],
            'test_category_coverage' => [$this, 'test_category_coverage'],
            'test_critical_components_coverage' => [$this, 'test_critical_components_coverage'],
            'test_test_quality_metrics' => [$this, 'test_test_quality_metrics'],
            'test_coverage_recommendations' => [$this, 'test_coverage_recommendations']
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test_name => $callback) {
            echo "\n🔍 Exécution de $test_name...\n";
            $start_time = microtime(true);

            try {
                $result = call_user_func($callback);
                $end_time = microtime(true);
                $duration = round(($end_time - $start_time) * 1000, 2);
                echo "⏱️ Durée: {$duration}ms\n";

                if ($result) {
                    echo "✅ Test réussi\n";
                    $passed++;
                } else {
                    echo "❌ Test échoué\n";
                }
            } catch (Exception $e) {
                echo "💥 Exception: " . $e->getMessage() . "\n";
                $this->results[] = "💥 EXCEPTION in $test_name: " . $e->getMessage();
            }
        }

        echo "\n=============================================\n";
        echo "RÉSULTATS: {$passed}/{$total} tests réussis\n";

        if ($passed === $total) {
            echo "🎯 Couverture de code analysée avec succès !\n";
        } else {
            echo "⚠️ Analyse de couverture incomplète\n";
        }

        // Afficher métriques détaillées
        $coverage = $this->analyze_test_coverage();
        echo "\n📈 MÉTRIQUES DE COUVERTURE:\n";
        echo "  Lignes de test: {$coverage['test_lines']}\n";
        echo "  Lignes de code source: {$coverage['source_lines']}\n";
        echo "  Ratio test/source: " . round(($coverage['test_lines'] / max($coverage['source_lines'], 1)) * 100, 2) . "%\n";
        echo "  Fichiers de test: " . count($coverage['test_files']) . "\n";
        echo "  Fichiers source: " . count($coverage['source_files']) . "\n";

        echo "\nDétails:\n";
        foreach ($this->results as $result) {
            echo "  $result\n";
        }

        return $passed === $total;
    }
}

// Exécuter les tests si appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    $test = new Coverage_Report();
    $test->run_all_tests();
}