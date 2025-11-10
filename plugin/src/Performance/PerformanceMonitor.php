<?php

/**
 * PDF Builder Pro - Performance Monitor
 * Phase 3.3.6 - Surveillance des performances de rendu
 *
 * Mesure les métriques de performance :
 * - Temps de rendu par renderer
 * - Utilisation mémoire
 * - FPS et taux de rafraîchissement
 * - Statistiques de cache
 */

namespace PDF_Builder\Performance;

// Sécurité WordPress - Désactivée pour les tests
// if (!defined('ABSPATH')) {
//     exit('Accès direct interdit');
// }

class PerformanceMonitor
{
    /**
     * Métriques collectées
     */
    private static $metrics = [
        'render_times' => [],
        'memory_usage' => [],
        'cache_stats' => [],
        'renderer_calls' => [],
        'start_time' => null,
        'peak_memory' => 0
    ];

    /**
     * Démarre la surveillance des performances
     */
    public static function start(): void
    {
        self::$metrics['start_time'] = microtime(true);
        self::$metrics['peak_memory'] = memory_get_peak_usage(true);
    }

    /**
     * Mesure le temps d'exécution d'une fonction
     *
     * @param callable $callback Fonction à mesurer
     * @param array $args Arguments de la fonction
     * @param string $label Label pour les métriques
     * @return mixed Résultat de la fonction
     */
    public static function measure(callable $callback, array $args = [], string $label = 'unnamed')
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $result = call_user_func_array($callback, $args);
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $executionTime = ($endTime - $startTime) * 1000;
// en millisecondes
        $memoryUsed = $endMemory - $startMemory;
        self::$metrics['render_times'][] = [
            'label' => $label,
            'time' => round($executionTime, 2),
            'memory' => self::formatBytes($memoryUsed),
            'timestamp' => time()
        ];
// Mise à jour du pic mémoire
        if ($endMemory > self::$metrics['peak_memory']) {
            self::$metrics['peak_memory'] = $endMemory;
        }

        return $result;
    }

    /**
     * Enregistre un appel de renderer
     *
     * @param string $renderer Nom du renderer
     * @param string $elementType Type d'élément
     * @param float $duration Durée en millisecondes
     */
    public static function recordRendererCall(string $renderer, string $elementType, float $duration): void
    {
        $key = $renderer . '_' . $elementType;
        if (!isset(self::$metrics['renderer_calls'][$key])) {
            self::$metrics['renderer_calls'][$key] = [
                'count' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
                'max_time' => 0
            ];
        }

        $stats = &self::$metrics['renderer_calls'][$key];
        $stats['count']++;
        $stats['total_time'] += $duration;
        $stats['avg_time'] = $stats['total_time'] / $stats['count'];
        $stats['min_time'] = min($stats['min_time'], $duration);
        $stats['max_time'] = max($stats['max_time'], $duration);
    }

    /**
     * Mesure les performances du cache
     *
     * @param array $cacheMetrics Métriques du cache
     */
    public static function recordCacheStats(array $cacheMetrics): void
    {
        self::$metrics['cache_stats'][] = array_merge($cacheMetrics, [
            'timestamp' => time()
        ]);
    }

    /**
     * Obtient un rapport de performance complet
     *
     * @return array Rapport détaillé
     */
    public static function getReport(): array
    {
        $totalTime = self::$metrics['start_time'] ? (microtime(true) - self::$metrics['start_time']) * 1000 : 0;
        $renderTimeStats = self::calculateStats(array_column(self::$metrics['render_times'], 'time'));
        return [
            'summary' => [
                'total_execution_time' => round($totalTime, 2) . 'ms',
                'total_render_calls' => count(self::$metrics['render_times']),
                'average_render_time' => round($renderTimeStats['avg'], 2) . 'ms',
                'peak_memory_usage' => self::formatBytes(self::$metrics['peak_memory']),
                'current_memory_usage' => self::formatBytes(memory_get_usage(true))
            ],
            'render_performance' => [
                'fastest_render' => $renderTimeStats['min'] . 'ms',
                'slowest_render' => $renderTimeStats['max'] . 'ms',
                'median_render_time' => $renderTimeStats['median'] . 'ms',
                'renders_under_500ms' => count(array_filter(self::$metrics['render_times'], fn($r) => $r['time'] < 500))
            ],
            'renderer_breakdown' => self::$metrics['renderer_calls'],
            'cache_performance' => end(self::$metrics['cache_stats']) ?: [],
            'recent_renders' => array_slice(array_reverse(self::$metrics['render_times']), 0, 10),
            'system_info' => [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status()['opcache_enabled']
            ]
        ];
    }

    /**
     * Vérifie si les performances sont dans les limites acceptables
     *
     * @return array Résultats des vérifications
     */
    public static function checkPerformanceThresholds(): array
    {
        $report = self::getReport();
        return [
            'render_time_ok' => $report['render_performance']['median_render_time'] < 500,
            'memory_usage_ok' => self::$metrics['peak_memory'] < 50 * 1024 * 1024, // 50MB
            'cache_hit_rate_ok' => ($report['cache_performance']['hit_rate'] ?? 0) > 70,
            'no_slow_renders' => $report['render_performance']['slowest_render'] < 2000,
            'overall_status' => self::getOverallStatus($report)
        ];
    }

    /**
     * Exporte les métriques pour analyse
     *
     * @param string $format Format d'export ('json', 'csv', 'log')
     * @return string Données exportées
     */
    public static function exportMetrics(string $format = 'json'): string
    {
        $data = self::getReport();
        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            case 'csv':
                return self::exportToCSV($data);
            case 'log':
                return self::exportToLog($data);
            default:
                return json_encode($data);
        }
    }

    /**
     * Réinitialise toutes les métriques
     */
    public static function reset(): void
    {
        self::$metrics = [
            'render_times' => [],
            'memory_usage' => [],
            'cache_stats' => [],
            'renderer_calls' => [],
            'start_time' => microtime(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }

    /**
     * Calcule les statistiques de base pour un tableau de valeurs
     *
     * @param array $values Valeurs numériques
     * @return array Statistiques
     */
    private static function calculateStats(array $values): array
    {
        if (empty($values)) {
            return ['avg' => 0, 'min' => 0, 'max' => 0, 'median' => 0];
        }

        sort($values);
        $count = count($values);
        $middle = floor($count / 2);
        return [
            'avg' => array_sum($values) / $count,
            'min' => min($values),
            'max' => max($values),
            'median' => $values[$middle]
        ];
    }

    /**
     * Formate les octets en unités lisibles
     *
     * @param int $bytes Nombre d'octets
     * @return string Chaîne formatée
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Détermine le statut global des performances
     *
     * @param array $report Rapport de performance
     * @return string Statut ('excellent', 'good', 'warning', 'critical')
     */
    private static function getOverallStatus(array $report): string
    {
        $checks = self::checkPerformanceThresholds();
        if ($checks['render_time_ok'] && $checks['memory_usage_ok'] && $checks['cache_hit_rate_ok'] && $checks['no_slow_renders']) {
            return 'excellent';
        }

        if ($checks['render_time_ok'] && $checks['memory_usage_ok']) {
            return 'good';
        }

        if ($checks['render_time_ok'] || $checks['memory_usage_ok']) {
            return 'warning';
        }

        return 'critical';
    }

    /**
     * Exporte les données au format CSV
     *
     * @param array $data Données à exporter
     * @return string CSV
     */
    private static function exportToCSV(array $data): string
    {
        $csv = "Metric,Value\n";
// Summary
        foreach ($data['summary'] as $key => $value) {
            $csv .= "summary_{$key},{$value}\n";
        }

        // Render performance
        foreach ($data['render_performance'] as $key => $value) {
            $csv .= "render_{$key},{$value}\n";
        }

        return $csv;
    }

    /**
     * Exporte les données au format log
     *
     * @param array $data Données à exporter
     * @return string Log
     */
    private static function exportToLog(array $data): string
    {
        $log = "[" . date('Y-m-d H:i:s') . "] Performance Report\n";
        $log .= "=====================================\n";
        foreach ($data['summary'] as $key => $value) {
            $log .= ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }

        $log .= "\nRender Performance:\n";
        foreach ($data['render_performance'] as $key => $value) {
            $log .= "  " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }

        return $log;
    }
}
