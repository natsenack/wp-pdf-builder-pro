<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder Performance Mappings
 *
 * Centralise toutes les configurations de performance et optimisations
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Performance_Mappings {

    // ==========================================
    // CONFIGURATIONS DE PERFORMANCE
    // ==========================================

    private static $performance_configs = [
        // Limites de mémoire
        'memory_limits' => [
            'php_min' => '128M',
            'php_recommended' => '256M',
            'js_heap_min' => 50, // MB
            'js_heap_recommended' => 100, // MB
            'canvas_memory_limit' => 200 // MB
        ],

        // Timeouts
        'timeouts' => [
            'ajax_request' => 30, // secondes
            'file_upload' => 60, // secondes
            'export_operation' => 120, // secondes
            'template_load' => 15, // secondes
            'canvas_render' => 10, // secondes
            'image_processing' => 45, // secondes
            'font_load' => 20 // secondes
        ],

        // Limites d'éléments
        'element_limits' => [
            'max_elements_per_template' => 500,
            'max_text_elements' => 100,
            'max_image_elements' => 50,
            'max_shape_elements' => 200,
            'max_grouped_elements' => 1000,
            'max_nested_groups' => 5
        ],

        // Tailles d'images
        'image_sizes' => [
            'thumbnail_max' => 150, // pixels
            'preview_max' => 800, // pixels
            'export_max' => 3000, // pixels
            'memory_threshold' => 2048, // pixels - seuil pour optimisation mémoire
            'compression_quality' => 85 // pourcentage
        ],

        // Cache
        'cache_settings' => [
            'template_cache_ttl' => 3600, // secondes
            'image_cache_ttl' => 7200, // secondes
            'font_cache_ttl' => 86400, // secondes
            'config_cache_ttl' => 1800, // secondes
            'max_cache_size' => '100M'
        ],

        // Optimisations JavaScript
        'js_optimizations' => [
            'debounce_delay' => 300, // millisecondes
            'throttle_delay' => 100, // millisecondes
            'lazy_load_threshold' => 100, // pixels
            'virtual_scroll_threshold' => 50, // éléments
            'fps_target' => 60,
            'memory_cleanup_interval' => 30000 // millisecondes
        ],

        // Optimisations de rendu
        'render_optimizations' => [
            'batch_size' => 10, // éléments par batch
            'render_delay' => 16, // millisecondes (~60fps)
            'quality_reduction_threshold' => 100, // éléments
            'simplified_render_threshold' => 200, // éléments
            'progress_update_interval' => 100 // millisecondes
        ]
    ];

    // ==========================================
    // SEUILS DE PERFORMANCE
    // ==========================================

    private static $performance_thresholds = [
        'warning' => [
            'elements_count' => 100,
            'image_size_mb' => 2,
            'render_time_ms' => 1000,
            'memory_usage_mb' => 50,
            'cpu_usage_percent' => 70
        ],

        'critical' => [
            'elements_count' => 300,
            'image_size_mb' => 5,
            'render_time_ms' => 5000,
            'memory_usage_mb' => 100,
            'cpu_usage_percent' => 90
        ],

        'optimal' => [
            'elements_count' => 50,
            'image_size_mb' => 1,
            'render_time_ms' => 500,
            'memory_usage_mb' => 25,
            'cpu_usage_percent' => 50
        ]
    ];

    // ==========================================
    // STRATÉGIES D'OPTIMISATION
    // ==========================================

    private static $optimization_strategies = [
        'lazy_loading' => [
            'enabled' => true,
            'threshold' => 50, // éléments
            'batch_size' => 10,
            'delay' => 100 // millisecondes
        ],

        'virtualization' => [
            'enabled' => true,
            'container_height' => 600, // pixels
            'item_height' => 40, // pixels
            'overscan' => 5 // éléments
        ],

        'compression' => [
            'image_quality' => 85,
            'enable_lossy' => true,
            'max_colors' => 256,
            'strip_metadata' => true
        ],

        'caching' => [
            'browser_cache' => true,
            'memory_cache' => true,
            'file_cache' => true,
            'cdn_cache' => false
        ],

        'debouncing' => [
            'input_events' => 300, // millisecondes
            'resize_events' => 150, // millisecondes
            'scroll_events' => 100, // millisecondes
            'save_events' => 1000 // millisecondes
        ],

        'throttling' => [
            'render_updates' => 60, // fps
            'progress_updates' => 10, // fps
            'memory_checks' => 5 // par seconde
        ]
    ];

    // ==========================================
    // MÉTRIQUES DE PERFORMANCE
    // ==========================================

    private static $performance_metrics = [
        'render_time' => [
            'name' => 'Temps de rendu',
            'unit' => 'ms',
            'threshold_warning' => 1000,
            'threshold_critical' => 5000,
            'description' => 'Temps nécessaire pour rendre le canvas'
        ],

        'memory_usage' => [
            'name' => 'Utilisation mémoire',
            'unit' => 'MB',
            'threshold_warning' => 50,
            'threshold_critical' => 100,
            'description' => 'Mémoire utilisée par le processus'
        ],

        'cpu_usage' => [
            'name' => 'Utilisation CPU',
            'unit' => '%',
            'threshold_warning' => 70,
            'threshold_critical' => 90,
            'description' => 'Pourcentage d\'utilisation du processeur'
        ],

        'elements_count' => [
            'name' => 'Nombre d\'éléments',
            'unit' => 'éléments',
            'threshold_warning' => 100,
            'threshold_critical' => 300,
            'description' => 'Nombre total d\'éléments dans le template'
        ],

        'image_size' => [
            'name' => 'Taille des images',
            'unit' => 'MB',
            'threshold_warning' => 2,
            'threshold_critical' => 5,
            'description' => 'Taille totale des images chargées'
        ],

        'network_requests' => [
            'name' => 'Requêtes réseau',
            'unit' => 'requêtes',
            'threshold_warning' => 20,
            'threshold_critical' => 50,
            'description' => 'Nombre de requêtes HTTP effectuées'
        ],

        'dom_nodes' => [
            'name' => 'Nœuds DOM',
            'unit' => 'nœuds',
            'threshold_warning' => 1000,
            'threshold_critical' => 5000,
            'description' => 'Nombre de nœuds dans le DOM'
        ],

        'fps' => [
            'name' => 'Images par seconde',
            'unit' => 'fps',
            'threshold_warning' => 30,
            'threshold_critical' => 15,
            'description' => 'Taux de rafraîchissement du canvas'
        ]
    ];

    // ==========================================
    // RECOMMANDATIONS DE PERFORMANCE
    // ==========================================

    private static $performance_recommendations = [
        'high_memory_usage' => [
            'message' => 'Utilisation mémoire élevée détectée',
            'suggestions' => [
                'Réduire le nombre d\'éléments dans le template',
                'Utiliser des images plus petites ou compressées',
                'Activer la pagination pour les gros templates',
                'Fermer les autres onglets du navigateur'
            ]
        ],

        'slow_render_time' => [
            'message' => 'Temps de rendu lent détecté',
            'suggestions' => [
                'Simplifier les éléments complexes (ombres, dégradés)',
                'Utiliser des polices système au lieu de polices personnalisées',
                'Réduire la résolution des images',
                'Désactiver les animations temporaires'
            ]
        ],

        'high_cpu_usage' => [
            'message' => 'Utilisation CPU élevée détectée',
            'suggestions' => [
                'Fermer les autres applications',
                'Utiliser un navigateur plus récent',
                'Désactiver les extensions du navigateur',
                'Réduire la fréquence des mises à jour'
            ]
        ],

        'too_many_elements' => [
            'message' => 'Trop d\'éléments dans le template',
            'suggestions' => [
                'Grouper les éléments similaires',
                'Utiliser des templates plus petits',
                'Supprimer les éléments inutiles',
                'Diviser le document en plusieurs pages'
            ]
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir toutes les configurations de performance
     */
    public static function get_performance_configs() {
        return self::$performance_configs;
    }

    /**
     * Obtenir une configuration de performance spécifique
     */
    public static function get_performance_config($key) {
        return self::$performance_configs[$key] ?? null;
    }

    /**
     * Obtenir les seuils de performance
     */
    public static function get_performance_thresholds() {
        return self::$performance_thresholds;
    }

    /**
     * Obtenir les seuils pour un niveau spécifique
     */
    public static function get_thresholds_by_level($level) {
        return self::$performance_thresholds[$level] ?? [];
    }

    /**
     * Obtenir les stratégies d'optimisation
     */
    public static function get_optimization_strategies() {
        return self::$optimization_strategies;
    }

    /**
     * Obtenir une stratégie d'optimisation spécifique
     */
    public static function get_optimization_strategy($key) {
        return self::$optimization_strategies[$key] ?? null;
    }

    /**
     * Obtenir les métriques de performance
     */
    public static function get_performance_metrics() {
        return self::$performance_metrics;
    }

    /**
     * Obtenir une métrique de performance spécifique
     */
    public static function get_performance_metric($key) {
        return self::$performance_metrics[$key] ?? null;
    }

    /**
     * Obtenir les recommandations de performance
     */
    public static function get_performance_recommendations() {
        return self::$performance_recommendations;
    }

    /**
     * Obtenir une recommandation de performance spécifique
     */
    public static function get_performance_recommendation($key) {
        return self::$performance_recommendations[$key] ?? null;
    }

    /**
     * Vérifier si une valeur dépasse un seuil
     */
    public static function check_threshold($metric, $value, $level = 'warning') {
        $thresholds = self::get_thresholds_by_level($level);
        $threshold = $thresholds[$metric] ?? null;

        if ($threshold === null) {
            return false;
        }

        return $value > $threshold;
    }

    /**
     * Obtenir le niveau de performance pour une métrique
     */
    public static function get_performance_level($metric, $value) {
        if (self::check_threshold($metric, $value, 'critical')) {
            return 'critical';
        }

        if (self::check_threshold($metric, $value, 'warning')) {
            return 'warning';
        }

        return 'optimal';
    }

    /**
     * Générer des recommandations basées sur les métriques
     */
    public static function generate_recommendations($metrics) {
        $recommendations = [];

        foreach ($metrics as $metric => $value) {
            $level = self::get_performance_level($metric, $value);

            if ($level !== 'optimal') {
                $recommendation_key = $metric . '_' . $level;
                $recommendation = self::get_performance_recommendation($recommendation_key);

                if ($recommendation) {
                    $recommendations[] = $recommendation;
                }
            }
        }

        return $recommendations;
    }

    /**
     * Vérifier si l'optimisation doit être activée
     */
    public static function should_enable_optimization($optimization_type, $current_metrics) {
        $strategy = self::get_optimization_strategy($optimization_type);

        if (!$strategy || !isset($strategy['enabled']) || !$strategy['enabled']) {
            return false;
        }

        // Vérifier les conditions d'activation basées sur les métriques
        switch ($optimization_type) {
            case 'lazy_loading':
                return isset($current_metrics['elements_count']) &&
                       $current_metrics['elements_count'] > ($strategy['threshold'] ?? 50);

            case 'virtualization':
                return isset($current_metrics['dom_nodes']) &&
                       $current_metrics['dom_nodes'] > ($strategy['container_height'] ?? 600) / ($strategy['item_height'] ?? 40);

            case 'compression':
                return isset($current_metrics['image_size']) &&
                       $current_metrics['image_size'] > 1; // MB

            default:
                return true;
        }
    }

    /**
     * Obtenir les paramètres d'optimisation adaptés
     */
    public static function get_adaptive_settings($current_metrics) {
        $settings = [];

        // Ajuster la qualité de rendu basée sur les performances
        if (isset($current_metrics['fps']) && $current_metrics['fps'] < 30) {
            $settings['render_quality'] = 'low';
            $settings['disable_shadows'] = true;
            $settings['simplify_gradients'] = true;
        }

        // Ajuster la taille des batches
        if (isset($current_metrics['memory_usage']) && $current_metrics['memory_usage'] > 75) {
            $settings['batch_size'] = min(self::$performance_configs['render_optimizations']['batch_size'], 5);
        }

        // Ajuster les timeouts
        if (isset($current_metrics['render_time']) && $current_metrics['render_time'] > 2000) {
            $settings['timeout_multiplier'] = 1.5;
        }

        return $settings;
    }
}



