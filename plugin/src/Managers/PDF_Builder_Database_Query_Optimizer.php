<?php

namespace WP_PDF_Builder_Pro\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Database Query Optimizer
 * Optimisation des requêtes de base de données pour WooCommerce
 */

class PdfBuilderDatabaseQueryOptimizer
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Cache des requêtes préparées
     */
    private $prepared_statements = [];

    /**
     * Statistiques des requêtes
     */
    private $query_stats = [
        'executed' => 0,
        'cached' => 0,
        'optimized' => 0,
        'slow_queries' => []
    ];

    /**
     * Configuration d'optimisation
     */
    private $optimization_config = [
        'enable_prepared_statements' => true,
        'enable_query_caching' => true,
        'enable_index_hints' => true,
        'slow_query_threshold' => 0.5, // secondes
        'max_cache_size' => 100,
        'auto_explain' => false
    ];

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initializeOptimizer();
    }

    /**
     * Initialiser l'optimiseur
     */
    private function initializeOptimizer()
    {
        // Préparer les requêtes fréquemment utilisées
        $this->prepareCommonQueries();

        // Activer le cache si configuré
        if ($this->optimization_config['enable_query_caching']) {
            $this->initializeQueryCache();
        }

        // Hook pour mesurer les performances des requêtes
        add_filter('query', [$this, 'measure_query_performance']);
    }

    /**
     * Préparer les requêtes communes
     */
    private function prepareCommonQueries()
    {
        global $wpdb;

        if (!$this->optimization_config['enable_prepared_statements']) {
            return;
        }

        // Requête pour récupérer les données de commande WooCommerce
        $this->prepared_statements['order_data'] = $wpdb->prepare(
            "SELECT
                p.ID,
                p.post_date,
                pm.meta_value as order_total,
                pm2.meta_value as order_currency
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
             LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_order_currency'
             WHERE p.post_type = 'shop_order'
             AND p.ID = %d",
            0 // Placeholder
        );

        // Requête pour récupérer les éléments de commande
        $this->prepared_statements['order_items'] = $wpdb->prepare(
            "SELECT
                oi.order_item_id,
                oi.order_item_name,
                oim.meta_value as product_id,
                oim2.meta_value as quantity,
                oim3.meta_value as line_total
             FROM {$wpdb->prefix}woocommerce_order_items oi
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_product_id'
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oi.order_item_id = oim2.order_item_id AND oim2.meta_key = '_qty'
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim3 ON oi.order_item_id = oim3.order_item_id AND oim3.meta_key = '_line_total'
             WHERE oi.order_id = %d
             AND oi.order_item_type = 'line_item'",
            0 // Placeholder
        );

        // Requête pour récupérer les données client
        $this->prepared_statements['customer_data'] = $wpdb->prepare(
            "SELECT
                u.user_email,
                um.meta_value as first_name,
                um2.meta_value as last_name,
                pm.meta_value as billing_address
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_billing_address_index'
             LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
             LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'first_name'
             LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
             WHERE p.post_type = 'shop_order'
             AND p.ID = %d",
            0 // Placeholder
        );

        // Requête pour récupérer les métadonnées de produit
        $this->prepared_statements['product_meta'] = $wpdb->prepare(
            "SELECT pm.meta_key, pm.meta_value
             FROM {$wpdb->postmeta} pm
             WHERE pm.post_id = %d
             AND pm.meta_key IN ('_sku', '_regular_price', '_sale_price', '_weight', '_length', '_width', '_height')
             ORDER BY pm.meta_key",
            0 // Placeholder
        );
    }

    /**
     * Initialiser le cache des requêtes
     */
    private function initializeQueryCache()
    {
        if (!wp_cache_get('pdf_builder_query_cache')) {
            wp_cache_set('pdf_builder_query_cache', [], '', 3600);
        }
    }

    /**
     * Récupérer les données de commande optimisées
     */
    public function getOptimizedOrderData($order_id)
    {
        $cache_key = 'order_data_' . $order_id;

        // Vérifier le cache
        if ($this->optimization_config['enable_query_caching']) {
            $cached = wp_cache_get($cache_key, 'pdf_builder_query_cache');
            if ($cached !== false) {
                $this->query_stats['cached']++;
                return $cached;
            }
        }

        // Utiliser les requêtes préparées
        global $wpdb;

        $order_data = $wpdb->get_row(
            $wpdb->prepare($this->prepared_statements['order_data'], $order_id),
            ARRAY_A
        );

        if ($order_data) {
            $order_items = $wpdb->get_results(
                $wpdb->prepare($this->prepared_statements['order_items'], $order_id),
                ARRAY_A
            );

            $customer_data = $wpdb->get_row(
                $wpdb->prepare($this->prepared_statements['customer_data'], $order_id),
                ARRAY_A
            );

            $result = [
                'order' => $order_data,
                'items' => $order_items,
                'customer' => $customer_data
            ];

            // Mettre en cache
            if ($this->optimization_config['enable_query_caching']) {
                wp_cache_set($cache_key, $result, 'pdf_builder_query_cache', 1800); // 30 minutes
            }

            $this->query_stats['executed']++;
            return $result;
        }

        return false;
    }

    /**
     * Récupérer les données de produit optimisées
     */
    public function getOptimizedProductData($product_ids)
    {
        if (empty($product_ids)) {
            return [];
        }

        $cache_key = 'product_data_' . md5(serialize($product_ids));

        // Vérifier le cache
        if ($this->optimization_config['enable_query_caching']) {
            $cached = wp_cache_get($cache_key, 'pdf_builder_query_cache');
            if ($cached !== false) {
                $this->query_stats['cached']++;
                return $cached;
            }
        }

        global $wpdb;

        // Utiliser une seule requête pour tous les produits
        $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT
                p.ID,
                p.post_title,
                p.post_content,
                p.post_excerpt,
                pm.meta_key,
                pm.meta_value
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.ID IN ({$placeholders})
             AND p.post_type IN ('product', 'product_variation')
             AND pm.meta_key IN ('_sku', '_regular_price', '_sale_price', '_weight', '_length', '_width', '_height', '_thumbnail_id')
             ORDER BY p.ID, pm.meta_key",
            $product_ids
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        // Organiser les résultats par produit
        $products = [];
        foreach ($results as $row) {
            $product_id = $row['ID'];
            if (!isset($products[$product_id])) {
                $products[$product_id] = [
                    'id' => $product_id,
                    'title' => $row['post_title'],
                    'content' => $row['post_content'],
                    'excerpt' => $row['post_excerpt'],
                    'meta' => []
                ];
            }

            if ($row['meta_key'] && $row['meta_value']) {
                $products[$product_id]['meta'][$row['meta_key']] = $row['meta_value'];
            }
        }

        // Mettre en cache
        if ($this->optimization_config['enable_query_caching']) {
            wp_cache_set($cache_key, $products, 'pdf_builder_query_cache', 3600); // 1 heure
        }

        $this->query_stats['executed']++;
        return $products;
    }

    /**
     * Optimiser une requête WooCommerce
     */
    public function optimizeWoocommerceQuery($query, $query_type = 'general')
    {
        $optimized_query = $query;

        // Ajouter des hints d'index si activé
        if ($this->optimization_config['enable_index_hints']) {
            $optimized_query = $this->addIndexHints($query, $query_type);
        }

        // Optimiser les jointures
        $optimized_query = $this->optimizeJoins($optimized_query);

        // Optimiser les conditions WHERE
        $optimized_query = $this->optimizeWhereConditions($optimized_query);

        if ($optimized_query !== $query) {
            $this->query_stats['optimized']++;
        }

        return $optimized_query;
    }

    /**
     * Ajouter des hints d'index
     */
    private function addIndexHints($query, $query_type)
    {
        $hints = [
            'order' => 'USE INDEX (post_type_status_date)',
            'product' => 'USE INDEX (post_type)',
            'order_meta' => 'USE INDEX (meta_key)',
            'product_meta' => 'USE INDEX (post_id)'
        ];

        if (isset($hints[$query_type])) {
            // Insérer le hint après SELECT
            $query = preg_replace('/^SELECT/i', 'SELECT ' . $hints[$query_type], $query);
        }

        return $query;
    }

    /**
     * Optimiser les jointures
     */
    private function optimizeJoins($query)
    {
        // Remplacer LEFT JOIN par INNER JOIN quand possible
        $query = preg_replace('/LEFT JOIN\s+(\w+)\s+ON\s+([^=]+)=\1\.(\w+)/i', 'INNER JOIN $1 ON $2=$1.$3', $query);

        // Optimiser l'ordre des jointures (tables les plus petites en premier)
        // Cette optimisation est complexe et dépend de la structure spécifique

        return $query;
    }

    /**
     * Optimiser les conditions WHERE
     */
    private function optimizeWhereConditions($query)
    {
        // Utiliser IN au lieu de multiples OR
        $query = preg_replace('/(\w+)\s*=\s*([^)]+)\s+OR\s+\1\s*=\s*([^)]+)/i', '$1 IN ($2, $3)', $query);

        // Optimiser les conditions de date
        $query = preg_replace('/DATE\(([^)]+)\)\s*=\s*\'([^\']+)\'/i', '$1 >= \'$2 00:00:00\' AND $1 < \'$2 23:59:59\'', $query);

        return $query;
    }

    /**
     * Mesurer les performances des requêtes
     */
    public function measureQueryPerformance($query)
    {
        // Ne mesurer que les requêtes liées au PDF Builder
        if (
            strpos($query, 'pdf_builder') === false
            && strpos($query, 'woocommerce') === false
        ) {
            return $query;
        }

        $start_time = microtime(true);

        // Exécuter la requête et mesurer le temps
        add_action(
            'shutdown',
            function () use ($query, $start_time) {
                $end_time = microtime(true);
                $execution_time = $end_time - $start_time;

                if ($execution_time > $this->optimization_config['slow_query_threshold']) {
                    $this->query_stats['slow_queries'][] = [
                    'query' => $query,
                    'time' => $execution_time,
                    'timestamp' => current_time('mysql')
                    ];
                }
            }
        );

        return $query;
    }

    /**
     * Créer des index optimisés pour les performances
     */
    public function createPerformanceIndexes()
    {
        global $wpdb;

        $indexes = [
            // Index pour les commandes WooCommerce
            "CREATE INDEX IF NOT EXISTS idx_pdf_order_status_date ON {$wpdb->posts} (post_type, post_status, post_date)",
            "CREATE INDEX IF NOT EXISTS idx_pdf_order_meta ON {$wpdb->postmeta} (post_id, meta_key(50))",

            // Index pour les éléments de commande
            "CREATE INDEX IF NOT EXISTS idx_pdf_order_items ON {$wpdb->prefix}woocommerce_order_items (order_id, order_item_type)",

            // Index pour les métadonnées d'éléments de commande
            "CREATE INDEX IF NOT EXISTS idx_pdf_order_itemmeta ON {$wpdb->prefix}woocommerce_order_itemmeta (order_item_id, meta_key(50))",

            // Index composite pour les produits
            "CREATE INDEX IF NOT EXISTS idx_pdf_product_meta ON {$wpdb->postmeta} (post_id, meta_key(50), meta_value(100))"
        ];

        foreach ($indexes as $index_sql) {
            $wpdb->query($index_sql);
        }

        $logger = \PDF_Builder\Managers\PDF_Builder_Logger::getInstance();
        $logger->log('Index de performance créés pour PDF Builder', 'info', 'db_optimizer');
    }

    /**
     * Analyser les requêtes lentes
     */
    public function analyzeSlowQueries()
    {
        if (empty($this->query_stats['slow_queries'])) {
            return [];
        }

        $analysis = [];

        foreach ($this->query_stats['slow_queries'] as $slow_query) {
            $analysis[] = [
                'query' => $slow_query['query'],
                'execution_time' => $slow_query['time'],
                'timestamp' => $slow_query['timestamp'],
                'recommendations' => $this->getQueryRecommendations($slow_query['query'])
            ];
        }

        return $analysis;
    }

    /**
     * Obtenir des recommandations pour optimiser une requête
     */
    private function getQueryRecommendations($query)
    {
        $recommendations = [];

        // Vérifier l'absence d'index
        if (strpos($query, 'postmeta') !== false && strpos($query, 'meta_key') === false) {
            $recommendations[] = 'Ajouter un index sur meta_key pour les requêtes postmeta';
        }

        // Vérifier les jointures multiples
        $join_count = substr_count(strtoupper($query), 'JOIN');
        if ($join_count > 3) {
            $recommendations[] = 'Considérer diviser la requête ou utiliser des sous-requêtes';
        }

        // Vérifier l'utilisation de LIKE
        if (strpos(strtoupper($query), 'LIKE') !== false) {
            $recommendations[] = 'Éviter LIKE avec des jokers en début pour de meilleures performances';
        }

        // Vérifier les requêtes sans LIMIT
        if (strpos(strtoupper($query), 'LIMIT') === false) {
            $recommendations[] = 'Ajouter LIMIT pour éviter de récupérer trop de données';
        }

        return $recommendations;
    }

    /**
     * Obtenir les statistiques d'optimisation
     */
    public function getOptimizationStats()
    {
        return [
            'query_stats' => $this->query_stats,
            'config' => $this->optimization_config,
            'cache_info' => $this->getCacheInfo(),
            'slow_queries_analysis' => $this->analyzeSlowQueries()
        ];
    }

    /**
     * Obtenir les informations du cache
     */
    private function getCacheInfo()
    {
        $cache = wp_cache_get('pdf_builder_query_cache');
        return [
            'cache_enabled' => $this->optimization_config['enable_query_caching'],
            'cache_size' => is_array($cache) ? count($cache) : 0,
            'max_cache_size' => $this->optimization_config['max_cache_size']
        ];
    }

    /**
     * Nettoyer le cache des requêtes
     */
    public function clearQueryCache()
    {
        wp_cache_delete('pdf_builder_query_cache');
        $this->initializeQueryCache();

        $logger = \PDF_Builder\Managers\PDF_Builder_Logger::getInstance();
        $logger->log('Cache des requêtes nettoyé', 'info', 'db_optimizer');
    }

    /**
     * Optimiser la base de données
     */
    public function optimizeDatabase()
    {
        global $wpdb;

        // Réparer les tables
        $tables = [
            $wpdb->posts,
            $wpdb->postmeta,
            $wpdb->prefix . 'woocommerce_order_items',
            $wpdb->prefix . 'woocommerce_order_itemmeta'
        ];

        foreach ($tables as $table) {
            $wpdb->query("REPAIR TABLE {$table}");
            $wpdb->query("OPTIMIZE TABLE {$table}");
        }

        // Créer les index de performance
        $this->createPerformanceIndexes();

        $logger = \PDF_Builder\Managers\PDF_Builder_Logger::getInstance();
        $logger->log('Base de données optimisée pour PDF Builder', 'info', 'db_optimizer');
    }
}
