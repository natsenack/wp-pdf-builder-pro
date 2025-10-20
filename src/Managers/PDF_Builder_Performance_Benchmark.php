<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Performance Benchmark Suite
 * Tests de performance pour mesurer les optimisations
 */

class PDF_Builder_Performance_Benchmark
{

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Résultats des benchmarks
     */
    private $benchmark_results = [];

    /**
     * Métriques de performance
     */
    private $performance_metrics = [
        'pdf_generation_time' => [],
        'memory_usage' => [],
        'database_queries' => [],
        'asset_loading_time' => [],
        'cache_hit_ratio' => []
    ];

    /**
     * Configuration des tests
     */
    private $benchmark_config = [
        'iterations' => 5,
        'warmup_iterations' => 2,
        'enable_memory_profiling' => true,
        'enable_query_profiling' => true,
        'save_results' => true,
        'compare_with_baseline' => true
    ];

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
    }

    /**
     * Exécuter tous les benchmarks
     */
    public function run_full_benchmark_suite()
    {
        $this->log_benchmark_start('Suite complète de benchmarks');

        $results = [
            'pdf_generation' => $this->benchmark_pdf_generation(),
            'cache_performance' => $this->benchmark_cache_performance(),
            'database_queries' => $this->benchmark_database_queries(),
            'asset_optimization' => $this->benchmark_asset_optimization(),
            'memory_usage' => $this->benchmark_memory_usage(),
            'overall_score' => 0
        ];

        // Calculer le score global
        $results['overall_score'] = $this->calculate_overall_score($results);

        // Comparer avec la baseline si activé
        if ($this->benchmark_config['compare_with_baseline']) {
            $results['baseline_comparison'] = $this->compare_with_baseline($results);
        }

        $this->benchmark_results = $results;
        $this->save_benchmark_results($results);

        $this->log_benchmark_end('Suite complète terminée', $results['overall_score']);

        return $results;
    }

    /**
     * Benchmark de génération PDF
     */
    public function benchmark_pdf_generation()
    {
        $this->log_benchmark_start('Benchmark génération PDF');

        $test_cases = [
            'screenshot_only' => $this->get_test_canvas_data('simple'),
            'tcpdf_only' => $this->get_test_canvas_data('simple'),
            'dual_generation' => $this->get_test_canvas_data('complex'),
            'batch_generation' => array_fill(0, 3, $this->get_test_canvas_data('medium'))
        ];

        $results = [];

        foreach ($test_cases as $test_name => $test_data) {
            $results[$test_name] = $this->run_pdf_generation_test($test_name, $test_data);
        }

        $this->log_benchmark_end('Benchmark génération PDF terminé');
        return $results;
    }

    /**
     * Exécuter un test de génération PDF
     */
    private function run_pdf_generation_test($test_name, $test_data)
    {
        $times = [];
        $memory_usage = [];
        $success_count = 0;

        // Warmup
        for ($i = 0; $i < $this->benchmark_config['warmup_iterations']; $i++) {
            $this->generate_test_pdf($test_data);
        }

        // Tests de performance
        for ($i = 0; $i < $this->benchmark_config['iterations']; $i++) {
            $start_time = microtime(true);
            $start_memory = memory_get_usage(true);

            $result = $this->generate_test_pdf($test_data);

            $end_time = microtime(true);
            $end_memory = memory_get_usage(true);

            if ($result) {
                $times[] = $end_time - $start_time;
                $memory_usage[] = $end_memory - $start_memory;
                $success_count++;
            }

            // Pause entre les itérations
            usleep(100000); // 100ms
        }

        return [
            'test_name' => $test_name,
            'iterations' => $this->benchmark_config['iterations'],
            'success_rate' => ($success_count / $this->benchmark_config['iterations']) * 100,
            'avg_time' => array_sum($times) / count($times),
            'min_time' => min($times),
            'max_time' => max($times),
            'avg_memory' => array_sum($memory_usage) / count($memory_usage),
            'total_time' => array_sum($times)
        ];
    }

    /**
     * Générer un PDF de test
     */
    private function generate_test_pdf($test_data)
    {
        try {
            if (is_array($test_data) && isset($test_data[0])) {
                // Batch generation
                $results = [];
                foreach ($test_data as $data) {
                    $results[] = $this->generate_single_test_pdf($data);
                }
                return !in_array(false, $results);
            } else {
                // Single generation
                return $this->generate_single_test_pdf($test_data);
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Générer un seul PDF de test
     */
    private function generate_single_test_pdf($canvas_data)
    {
        $dual_generator = new PDF_Builder_Dual_PDF_Generator($this->main);
        $filename = 'benchmark_test_' . time() . '_' . rand(1000, 9999) . '.pdf';

        $result = $dual_generator->generate_dual_pdf($canvas_data, [], $filename);

        // Nettoyer le fichier de test
        if ($result && file_exists($result)) {
            unlink($result);
        }

        return $result !== false;
    }

    /**
     * Benchmark des performances du cache
     */
    public function benchmark_cache_performance()
    {
        $this->log_benchmark_start('Benchmark performances cache');

        $cache_manager = new PDF_Builder_Extended_Cache_Manager($this->main);

        $test_data = [
            'small' => str_repeat('x', 100),
            'medium' => str_repeat('x', 10000),
            'large' => str_repeat('x', 100000)
        ];

        $results = [];

        foreach ($test_data as $size => $data) {
            $results[$size] = $this->run_cache_performance_test($cache_manager, $size, $data);
        }

        $this->log_benchmark_end('Benchmark cache terminé');
        return $results;
    }

    /**
     * Exécuter un test de performance du cache
     */
    private function run_cache_performance_test($cache_manager, $size, $data)
    {
        $cache_key = "benchmark_{$size}_" . rand(1000, 9999);

        // Test d'écriture
        $write_times = [];
        for ($i = 0; $i < $this->benchmark_config['iterations']; $i++) {
            $start = microtime(true);
            $cache_manager->set($cache_key . "_write_$i", $data, 'benchmark');
            $write_times[] = microtime(true) - $start;
        }

        // Test de lecture
        $read_times = [];
        for ($i = 0; $i < $this->benchmark_config['iterations']; $i++) {
            $start = microtime(true);
            $cache_manager->get($cache_key . "_write_$i", 'benchmark');
            $read_times[] = microtime(true) - $start;
        }

        // Test de lecture cache miss
        $miss_times = [];
        for ($i = 0; $i < $this->benchmark_config['iterations']; $i++) {
            $start = microtime(true);
            $cache_manager->get($cache_key . "_miss_$i", 'benchmark');
            $miss_times[] = microtime(true) - $start;
        }

        return [
            'size' => $size,
            'data_size' => strlen($data),
            'avg_write_time' => array_sum($write_times) / count($write_times),
            'avg_read_time' => array_sum($read_times) / count($read_times),
            'avg_miss_time' => array_sum($miss_times) / count($miss_times),
            'write_speed' => strlen($data) / (array_sum($write_times) / count($write_times)), // bytes/second
            'read_speed' => strlen($data) / (array_sum($read_times) / count($read_times))   // bytes/second
        ];
    }

    /**
     * Benchmark des requêtes de base de données
     */
    public function benchmark_database_queries()
    {
        $this->log_benchmark_start('Benchmark requêtes DB');

        $db_optimizer = new PDF_Builder_Database_Query_Optimizer($this->main);

        $test_queries = [
            'order_data' => 'order_data_test',
            'product_data' => 'product_data_test',
            'complex_join' => 'complex_join_test'
        ];

        $results = [];

        foreach ($test_queries as $query_type => $test_name) {
            $results[$query_type] = $this->run_database_query_test($db_optimizer, $query_type);
        }

        $this->log_benchmark_end('Benchmark DB terminé');
        return $results;
    }

    /**
     * Exécuter un test de requête DB
     */
    private function run_database_query_test($db_optimizer, $query_type)
    {
        $times = [];
        $query_counts = [];

        for ($i = 0; $i < $this->benchmark_config['iterations']; $i++) {
            $start_queries = get_num_queries();

            $start = microtime(true);

            switch ($query_type) {
            case 'order_data':
                // Simuler récupération données commande
                $this->simulate_order_data_query($db_optimizer);
                break;
            case 'product_data':
                // Simuler récupération données produit
                $this->simulate_product_data_query($db_optimizer);
                break;
            case 'complex_join':
                // Simuler jointure complexe
                $this->simulate_complex_join_query($db_optimizer);
                break;
            }

            $times[] = microtime(true) - $start;
            $query_counts[] = get_num_queries() - $start_queries;
        }

        return [
            'query_type' => $query_type,
            'avg_time' => array_sum($times) / count($times),
            'avg_queries' => array_sum($query_counts) / count($query_counts),
            'min_time' => min($times),
            'max_time' => max($times)
        ];
    }

    /**
     * Simuler une requête de données de commande
     */
    private function simulate_order_data_query($db_optimizer)
    {
        // Utiliser un ID de commande existant ou créer des données de test
        $order_id = $this->get_test_order_id();
        if ($order_id) {
            $db_optimizer->get_optimized_order_data($order_id);
        }
    }

    /**
     * Simuler une requête de données produit
     */
    private function simulate_product_data_query($db_optimizer)
    {
        $product_ids = $this->get_test_product_ids();
        if (!empty($product_ids)) {
            $db_optimizer->get_optimized_product_data($product_ids);
        }
    }

    /**
     * Simuler une jointure complexe
     */
    private function simulate_complex_join_query($db_optimizer)
    {
        global $wpdb;

        $query = $db_optimizer->optimize_woocommerce_query(
            "SELECT p.ID, p.post_title, pm.meta_value, pm2.meta_value
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
             WHERE p.post_type = 'product'
             AND pm.meta_key = '_price'
             AND pm2.meta_key = '_sku'
             LIMIT 10",
            'product'
        );

        $wpdb->get_results($query);
    }

    /**
     * Benchmark d'optimisation des assets
     */
    public function benchmark_asset_optimization()
    {
        $this->log_benchmark_start('Benchmark optimisation assets');

        $asset_optimizer = new PDF_Builder_Asset_Optimizer($this->main);

        $results = [
            'optimization_time' => 0,
            'file_sizes_before' => 0,
            'file_sizes_after' => 0,
            'compression_ratio' => 0
        ];

        $start = microtime(true);
        $optimization_results = $asset_optimizer->optimize_all_assets();
        $results['optimization_time'] = microtime(true) - $start;

        // Calculer les économies
        foreach ($optimization_results as $type => $result) {
            if ($result['status'] === 'completed' && isset($result['files'])) {
                foreach ($result['files'] as $file) {
                    if (isset($file['original_size']) && isset($file['optimized_size'])) {
                        $results['file_sizes_before'] += $file['original_size'];
                        $results['file_sizes_after'] += $file['optimized_size'];
                    }
                }
            }
        }

        if ($results['file_sizes_before'] > 0) {
            $results['compression_ratio'] = (($results['file_sizes_before'] - $results['file_sizes_after']) / $results['file_sizes_before']) * 100;
        }

        $this->log_benchmark_end('Benchmark assets terminé');
        return $results;
    }

    /**
     * Benchmark d'utilisation mémoire
     */
    public function benchmark_memory_usage()
    {
        $this->log_benchmark_start('Benchmark utilisation mémoire');

        $memory_usage = [];

        for ($i = 0; $i < $this->benchmark_config['iterations']; $i++) {
            $start_memory = memory_get_usage(true);

            // Simuler un processus intensif
            $this->simulate_memory_intensive_process();

            $end_memory = memory_get_usage(true);
            $memory_usage[] = $end_memory - $start_memory;

            // Libérer la mémoire
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        $results = [
            'avg_memory_usage' => array_sum($memory_usage) / count($memory_usage),
            'min_memory_usage' => min($memory_usage),
            'max_memory_usage' => max($memory_usage),
            'memory_efficiency' => $this->calculate_memory_efficiency($memory_usage)
        ];

        $this->log_benchmark_end('Benchmark mémoire terminé');
        return $results;
    }

    /**
     * Simuler un processus intensif en mémoire
     */
    private function simulate_memory_intensive_process()
    {
        $data = [];

        // Créer des données en mémoire
        for ($i = 0; $i < 1000; $i++) {
            $data[] = [
                'canvas' => $this->get_test_canvas_data('complex'),
                'metadata' => str_repeat('metadata_', 100),
                'cache' => array_fill(0, 100, 'cached_data_' . $i)
            ];
        }

        // Traiter les données
        foreach ($data as $item) {
            $processed = serialize($item);
            $unserialized = unserialize($processed);
            // Simuler traitement
            usleep(1000);
        }

        // Libérer la mémoire
        unset($data);
    }

    /**
     * Calculer l'efficacité mémoire
     */
    private function calculate_memory_efficiency($memory_usage)
    {
        $avg_usage = array_sum($memory_usage) / count($memory_usage);
        $max_usage = max($memory_usage);

        // Plus le ratio est élevé, plus l'efficacité est bonne
        return $max_usage > 0 ? ($avg_usage / $max_usage) * 100 : 100;
    }

    /**
     * Calculer le score global
     */
    private function calculate_overall_score($results)
    {
        $scores = [];

        // Score génération PDF (40% du total)
        if (isset($results['pdf_generation'])) {
            $pdf_score = 0;
            $pdf_tests = 0;
            foreach ($results['pdf_generation'] as $test) {
                if ($test['success_rate'] == 100) {
                    $pdf_score += (1 / $test['avg_time']) * 1000; // Score basé sur la vitesse
                    $pdf_tests++;
                }
            }
            $scores['pdf'] = $pdf_tests > 0 ? $pdf_score / $pdf_tests : 0;
        }

        // Score cache (20% du total)
        if (isset($results['cache_performance'])) {
            $cache_score = 0;
            foreach ($results['cache_performance'] as $test) {
                $cache_score += $test['read_speed'] / 1000; // Score basé sur la vitesse de lecture
            }
            $scores['cache'] = $cache_score / count($results['cache_performance']);
        }

        // Score DB (20% du total)
        if (isset($results['database_queries'])) {
            $db_score = 0;
            foreach ($results['database_queries'] as $test) {
                $db_score += (1 / $test['avg_time']) * 100; // Score basé sur la vitesse des requêtes
            }
            $scores['db'] = $db_score / count($results['database_queries']);
        }

        // Score assets (10% du total)
        if (isset($results['asset_optimization'])) {
            $asset_score = $results['asset_optimization']['compression_ratio'];
            $scores['assets'] = $asset_score;
        }

        // Score mémoire (10% du total)
        if (isset($results['memory_usage'])) {
            $memory_score = 100 - $results['memory_usage']['memory_efficiency']; // Moins d'usage = meilleur score
            $scores['memory'] = $memory_score;
        }

        // Score pondéré final
        $weighted_score = (
            ($scores['pdf'] ?? 0) * 0.4 +
            ($scores['cache'] ?? 0) * 0.2 +
            ($scores['db'] ?? 0) * 0.2 +
            ($scores['assets'] ?? 0) * 0.1 +
            ($scores['memory'] ?? 0) * 0.1
        );

        return round($weighted_score, 2);
    }

    /**
     * Comparer avec la baseline
     */
    private function compare_with_baseline($current_results)
    {
        $baseline = get_option('pdf_builder_performance_baseline', false);

        if (!$baseline) {
            return ['message' => 'Aucune baseline disponible pour la comparaison'];
        }

        $comparison = [
            'baseline_score' => $baseline['overall_score'] ?? 0,
            'current_score' => $current_results['overall_score'],
            'improvement' => 0,
            'details' => []
        ];

        if ($baseline['overall_score'] > 0) {
            $comparison['improvement'] = (($current_results['overall_score'] - $baseline['overall_score']) / $baseline['overall_score']) * 100;
        }

        // Comparer chaque métrique
        foreach (['pdf_generation', 'cache_performance', 'database_queries'] as $metric) {
            if (isset($current_results[$metric]) && isset($baseline[$metric])) {
                $comparison['details'][$metric] = $this->compare_metric($current_results[$metric], $baseline[$metric]);
            }
        }

        return $comparison;
    }

    /**
     * Comparer une métrique spécifique
     */
    private function compare_metric($current, $baseline)
    {
        // Implémentation simplifiée - comparer les temps moyens
        $current_avg = 0;
        $baseline_avg = 0;

        if (is_array($current)) {
            foreach ($current as $test) {
                if (isset($test['avg_time'])) {
                    $current_avg += $test['avg_time'];
                }
            }
            $current_avg /= count($current);
        }

        if (is_array($baseline)) {
            foreach ($baseline as $test) {
                if (isset($test['avg_time'])) {
                    $baseline_avg += $test['avg_time'];
                }
            }
            $baseline_avg /= count($baseline);
        }

        $improvement = 0;
        if ($baseline_avg > 0) {
            $improvement = (($baseline_avg - $current_avg) / $baseline_avg) * 100;
        }

        return [
            'current_avg' => $current_avg,
            'baseline_avg' => $baseline_avg,
            'improvement_percent' => round($improvement, 2)
        ];
    }

    /**
     * Obtenir des données de test pour le canvas
     */
    private function get_test_canvas_data($complexity = 'simple')
    {
        $base_data = [
            'width' => 800,
            'height' => 600,
            'elements' => []
        ];

        switch ($complexity) {
        case 'simple':
            $base_data['elements'] = [
                [
                    'type' => 'text',
                    'text' => 'Test PDF Generation',
                    'x' => 100,
                    'y' => 100,
                    'fontSize' => 24,
                    'color' => '#000000'
                ]
            ];
            break;

        case 'medium':
            $base_data['elements'] = array_merge(
                $base_data['elements'], [
                [
                    'type' => 'rectangle',
                    'x' => 50,
                    'y' => 50,
                    'width' => 700,
                    'height' => 500,
                    'fillColor' => '#f0f0f0',
                    'strokeColor' => '#000000'
                ],
                [
                    'type' => 'text',
                    'text' => 'Benchmark Test Document',
                    'x' => 100,
                    'y' => 100,
                    'fontSize' => 32,
                    'color' => '#333333'
                ],
                [
                    'type' => 'line',
                    'x1' => 100,
                    'y1' => 150,
                    'x2' => 700,
                    'y2' => 150,
                    'color' => '#666666'
                ]
                    ]
            );
            break;

        case 'complex':
            $base_data = $this->get_test_canvas_data('medium');
            // Ajouter plus d'éléments
            for ($i = 0; $i < 20; $i++) {
                $base_data['elements'][] = [
                    'type' => 'text',
                    'text' => "Line item {$i}",
                    'x' => 120,
                    'y' => 180 + ($i * 20),
                    'fontSize' => 12,
                    'color' => '#000000'
                ];
            }
            break;
        }

        return $base_data;
    }

    /**
     * Obtenir un ID de commande de test
     */
    private function get_test_order_id()
    {
        global $wpdb;

        $order_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_type = 'shop_order'
                 AND post_status IN ('wc-completed', 'wc-processing')
                 ORDER BY ID DESC LIMIT 1"
            )
        );

        return $order_id ?: null;
    }

    /**
     * Obtenir des IDs de produits de test
     */
    private function get_test_product_ids()
    {
        global $wpdb;

        $product_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_type IN ('product', 'product_variation')
                 AND post_status = 'publish'
                 ORDER BY ID DESC LIMIT 5"
            )
        );

        return $product_ids ?: [];
    }

    /**
     * Sauvegarder les résultats des benchmarks
     */
    private function save_benchmark_results($results)
    {
        if (!$this->benchmark_config['save_results']) {
            return;
        }

        $benchmark_data = [
            'timestamp' => current_time('mysql'),
            'results' => $results,
            'config' => $this->benchmark_config,
            'system_info' => $this->get_system_info()
        ];

        update_option('pdf_builder_latest_benchmark', $benchmark_data, false);

        // Sauvegarder comme baseline si demandé
        if (isset($_GET['set_baseline']) && $_GET['set_baseline'] == '1') {
            update_option('pdf_builder_performance_baseline', $results, false);
        }
    }

    /**
     * Obtenir les informations système
     */
    private function get_system_info()
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => PDF_BUILDER_PRO_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        ];
    }

    /**
     * Logger le début d'un benchmark
     */
    private function log_benchmark_start($message)
    {
        $logger = new PDF_Builder_Logger();
        $logger->log("Début: {$message}", 'info', 'performance_benchmark');
    }

    /**
     * Logger la fin d'un benchmark
     */
    private function log_benchmark_end($message, $score = null)
    {
        $logger = new PDF_Builder_Logger();
        $score_text = $score ? " (Score: {$score})" : '';
        $logger->log("Fin: {$message}{$score_text}", 'info', 'performance_benchmark');
    }

    /**
     * Obtenir les derniers résultats de benchmark
     */
    public function get_latest_benchmark_results()
    {
        return get_option('pdf_builder_latest_benchmark', false);
    }

    /**
     * Obtenir la baseline de performance
     */
    public function get_performance_baseline()
    {
        return get_option('pdf_builder_performance_baseline', false);
    }

    /**
     * Réinitialiser la baseline
     */
    public function reset_baseline()
    {
        delete_option('pdf_builder_performance_baseline');
        delete_option('pdf_builder_latest_benchmark');

        $logger = new PDF_Builder_Logger();
        $logger->log('Baseline de performance réinitialisée', 'info', 'performance_benchmark');
    }
}
