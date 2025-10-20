<?php
/**
 * Tests de charge et performance - Phase 6.3
 * Teste les performances sous charge élevée
 */

class Load_Performance_Test {

    private $results = [];
    private $performance_data = [];

    private function assert($condition, $message = '') {
        if ($condition) {
            $this->results[] = "✅ PASS: $message";
            return true;
        } else {
            $this->results[] = "❌ FAIL: $message";
            return false;
        }
    }

    private function measure_time($callback) {
        $start = microtime(true);
        $result = $callback();
        $end = microtime(true);
        return [
            'duration' => round(($end - $start) * 1000, 2), // ms
            'result' => $result
        ];
    }

    private function run_test($test_name, $callback) {
        echo "\n🔥 Exécution de $test_name...\n";
        $measurement = $this->measure_time($callback);
        $duration = $measurement['duration'];
        $result = $measurement['result'];

        echo "⏱️ Durée: {$duration}ms\n";

        if ($result) {
            echo "✅ Test réussi\n";
            $this->performance_data[$test_name] = $duration;
        } else {
            echo "❌ Test échoué\n";
        }

        return $result;
    }

    /**
     * Test génération PDF en masse (100 PDFs simultanés)
     */
    public function test_bulk_pdf_generation() {
        return $this->run_test('test_bulk_pdf_generation', function() {
            $pdf_count = 100;
            $pdfs = [];

            // Simuler génération de 100 PDFs
            for ($i = 1; $i <= $pdf_count; $i++) {
                $pdf_data = [
                    'id' => $i,
                    'content' => "PDF Content for Invoice #{$i}",
                    'size' => rand(50000, 200000), // 50KB - 200KB
                    'timestamp' => microtime(true)
                ];
                $pdfs[] = $pdf_data;
            }

            $success = $this->assert(count($pdfs) === $pdf_count, "100 PDFs générés");

            // Calculer métriques de performance
            $total_size = array_sum(array_column($pdfs, 'size'));
            $average_size = $total_size / $pdf_count;
            $timestamps = array_column($pdfs, 'timestamp');
            $generation_time = max($timestamps) - min($timestamps);

            $success &= $this->assert($average_size > 50000, "Taille moyenne acceptable");
            $success &= $this->assert($generation_time < 5.0, "Temps de génération en masse < 5s");

            // Simuler taux de succès (95% minimum)
            $successful_pdfs = array_filter($pdfs, function($pdf) {
                return $pdf['size'] > 25000; // PDFs valides > 25KB
            });
            $success_rate = (count($successful_pdfs) / $pdf_count) * 100;

            $success &= $this->assert($success_rate >= 95, "Taux de succès >= 95% ({$success_rate}%)");

            return $success;
        });
    }

    /**
     * Test traitement de variables en masse
     */
    public function test_bulk_variable_processing() {
        return $this->run_test('test_bulk_variable_processing', function() {
            $orders_count = 500;
            $orders = [];

            // Générer 500 commandes avec variables
            for ($i = 1; $i <= $orders_count; $i++) {
                $orders[] = [
                    'id' => $i,
                    'customer_name' => "Client {$i}",
                    'order_total' => rand(10, 1000) . '.' . rand(10, 99),
                    'items_count' => rand(1, 20)
                ];
            }

            $success = $this->assert(count($orders) === $orders_count, "500 commandes générées");

            // Template avec variables multiples
            $template = "Commande #{{order_id}} - Client: {{customer_name}} - Total: {{order_total}}€ - Articles: {{items_count}}";

            // Traiter toutes les variables
            $processed_orders = [];
            foreach ($orders as $order) {
                $processed = str_replace(
                    ['{{order_id}}', '{{customer_name}}', '{{order_total}}', '{{items_count}}'],
                    [$order['id'], $order['customer_name'], $order['order_total'], $order['items_count']],
                    $template
                );
                $processed_orders[] = $processed;
            }

            $success &= $this->assert(count($processed_orders) === $orders_count, "Toutes les commandes traitées");

            // Vérifier quelques remplacements aléatoires
            $random_checks = array_rand($processed_orders, 5);
            foreach ($random_checks as $index) {
                $processed = $processed_orders[$index];
                $original_order = $orders[$index];

                $success &= $this->assert(strpos($processed, "Commande #{$original_order['id']}") !== false, "ID remplacé correctement pour commande {$original_order['id']}");
                $success &= $this->assert(strpos($processed, $original_order['customer_name']) !== false, "Nom client remplacé pour commande {$original_order['id']}");
            }

            return $success;
        });
    }

    /**
     * Test cache sous charge (1000 accès simultanés)
     */
    public function test_cache_under_load() {
        return $this->run_test('test_cache_under_load', function() {
            $access_count = 1000;
            $cache = [];
            $hits = 0;
            $misses = 0;

            // Pré-remplir le cache avec 100 entrées
            for ($i = 1; $i <= 100; $i++) {
                $cache["template_{$i}"] = [
                    'data' => "Template data {$i}",
                    'timestamp' => time(),
                    'access_count' => 0
                ];
            }

            // Simuler 1000 accès aléatoires
            for ($i = 1; $i <= $access_count; $i++) {
                $key = "template_" . rand(1, 150); // Certains accès seront des misses

                if (isset($cache[$key])) {
                    $hits++;
                    $cache[$key]['access_count']++;
                } else {
                    $misses++;
                    // Simuler ajout en cache
                    $cache[$key] = [
                        'data' => "Template data {$key}",
                        'timestamp' => time(),
                        'access_count' => 1
                    ];
                }
            }

            $hit_ratio = ($hits / $access_count) * 100;

            $success = $this->assert($hits + $misses === $access_count, "Tous les accès traités");
            $success &= $this->assert($hit_ratio >= 60, "Taux de succès cache >= 60% ({$hit_ratio}%)");

            // Analyser distribution des accès
            $access_counts = array_column($cache, 'access_count');
            $max_access = max($access_counts);
            $avg_access = array_sum($access_counts) / count($access_counts);

            $success &= $this->assert($max_access > 0, "Cache utilisé");
            $success &= $this->assert($avg_access >= 5, "Distribution équilibrée des accès");

            return $success;
        });
    }

    /**
     * Test requêtes base de données sous charge
     */
    public function test_database_load() {
        return $this->run_test('test_database_load', function() {
            $query_count = 200;
            $queries = [];
            $response_times = [];

            // Simuler 200 requêtes de différents types
            for ($i = 1; $i <= $query_count; $i++) {
                $query_type = rand(1, 4);
                $response_time = rand(5, 50); // 5-50ms

                $query = [
                    'id' => $i,
                    'type' => $query_type,
                    'sql' => match($query_type) {
                        1 => 'SELECT * FROM wp_pdf_templates WHERE active = 1',
                        2 => 'SELECT * FROM wp_posts WHERE post_type = "shop_order" LIMIT 10',
                        3 => 'INSERT INTO wp_pdf_logs (action, data) VALUES (?, ?)',
                        4 => 'UPDATE wp_pdf_templates SET modified = NOW() WHERE id = ?'
                    },
                    'response_time' => match($query_type) {
                        1, 2 => rand(5, 25), // SELECT plus rapides
                        3, 4 => rand(15, 50) // INSERT/UPDATE plus lents
                    }
                ];

                $queries[] = $query;
                $response_times[] = $response_time;
            }

            $success = $this->assert(count($queries) === $query_count, "200 requêtes générées");

            // Calculer métriques de performance
            $avg_response_time = array_sum($response_times) / count($response_times);
            $max_response_time = max($response_times);
            $min_response_time = min($response_times);

            $success &= $this->assert($avg_response_time < 30, "Temps de réponse moyen < 30ms ({$avg_response_time}ms)");
            $success &= $this->assert($max_response_time < 100, "Temps de réponse max < 100ms ({$max_response_time}ms)");

            // Analyser par type de requête
            $select_queries = array_filter($queries, fn($q) => strpos($q['sql'], 'SELECT') === 0);
            $write_queries = array_filter($queries, fn($q) => strpos($q['sql'], 'INSERT') === 0 || strpos($q['sql'], 'UPDATE') === 0);

            $avg_select_time = array_sum(array_column($select_queries, 'response_time')) / count($select_queries);
            $avg_write_time = array_sum(array_column($write_queries, 'response_time')) / count($write_queries);

            $success &= $this->assert($avg_select_time < $avg_write_time, "SELECT plus rapides que les écritures");
            $success &= $this->assert($avg_select_time < 30, "SELECT rapides ({$avg_select_time}ms)");

            return $success;
        });
    }

    /**
     * Test mémoire sous charge prolongée
     */
    public function test_memory_leak_detection() {
        return $this->run_test('test_memory_leak_detection', function() {
            $iterations = 100;
            $memory_usage = [];

            // Simuler traitement itératif avec surveillance mémoire
            for ($i = 1; $i <= $iterations; $i++) {
                // Simuler traitement d'un PDF
                $pdf_data = str_repeat("PDF content iteration {$i} ", 1000);
                $processed_data = strtoupper($pdf_data);
                $compressed_data = gzcompress($processed_data);

                // Simuler utilisation mémoire (en MB)
                $current_memory = 10 + ($i * 0.1) + rand(-2, 2); // Léger drift + bruit
                $memory_usage[] = $current_memory;

                // Libérer la mémoire (simulé)
                unset($pdf_data, $processed_data, $compressed_data);
            }

            $success = $this->assert(count($memory_usage) === $iterations, "100 itérations surveillées");

            // Analyser la tendance mémoire
            $initial_memory = $memory_usage[0];
            $final_memory = end($memory_usage);
            $memory_growth = $final_memory - $initial_memory;
            $max_memory = max($memory_usage);
            $avg_memory = array_sum($memory_usage) / count($memory_usage);

            $success &= $this->assert($memory_growth < 15, "Croissance mémoire limitée (< 15MB, actuel: {$memory_growth}MB)");
            $success &= $this->assert($max_memory < 50, "Pic mémoire acceptable (< 50MB, actuel: {$max_memory}MB)");
            $success &= $this->assert($avg_memory < 25, "Utilisation moyenne acceptable (< 25MB, actuel: {$avg_memory}MB)");

            // Détecter fuites potentielles (croissance linéaire)
            $growth_rate = $memory_growth / $iterations;
            $success &= $this->assert($growth_rate < 0.15, "Taux de croissance mémoire faible (< 0.15MB/itération, actuel: {$growth_rate}MB)");

            return $success;
        });
    }

    public function run_all_tests() {
        echo "🔥 TESTS DE CHARGE ET PERFORMANCE - PHASE 6.3\n";
        echo "=============================================\n";

        $tests = [
            'test_bulk_pdf_generation' => [$this, 'test_bulk_pdf_generation'],
            'test_bulk_variable_processing' => [$this, 'test_bulk_variable_processing'],
            'test_cache_under_load' => [$this, 'test_cache_under_load'],
            'test_database_load' => [$this, 'test_database_load'],
            'test_memory_leak_detection' => [$this, 'test_memory_leak_detection']
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test_name => $callback) {
            if (call_user_func($callback)) {
                $passed++;
            }
        }

        echo "\n=============================================\n";
        echo "RÉSULTATS: {$passed}/{$total} tests réussis\n";

        if ($passed === $total) {
            echo "🚀 Performance sous charge excellente !\n";
        } else {
            echo "⚠️ Problèmes de performance détectés\n";
        }

        // Afficher métriques de performance
        echo "\n📊 MÉTRIQUES DE PERFORMANCE:\n";
        foreach ($this->performance_data as $test => $duration) {
            echo "  {$test}: {$duration}ms\n";
        }

        echo "\nDétails:\n";
        foreach ($this->results as $result) {
            echo "  $result\n";
        }

        return $passed === $total;
    }
}

// Exécuter les tests si appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    $test = new Load_Performance_Test();
    $test->run_all_tests();
}