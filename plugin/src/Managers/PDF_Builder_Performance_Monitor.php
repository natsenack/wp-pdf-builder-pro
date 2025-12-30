<?php

namespace PDF_Builder\Managers;

/**
 * Moniteur de Performance - PDF Builder Pro DISABLED
 *
 * Système de monitoring supprimé pour simplification
 */

class PdfBuilderPerformanceMonitor_DISABLED
{
    /**
     * Initialiser le monitoring - DÉSACTIVÉ
     */
    public static function init()
    {
        // Monitoring désactivé - système de cache supprimé
        return false;
    }
}
        }

        $execution_times = [];
        $memory_usages = [];
        foreach ($metrics as $metric) {
            if (isset($metric['data']['execution_time'])) {
                $execution_times[] = $metric['data']['execution_time'];
                if ($metric['data']['execution_time'] > 1) {
                    $stats['slow_requests']++;
                }
                if ($metric['data']['execution_time'] > $stats['max_execution_time']) {
                    $stats['max_execution_time'] = $metric['data']['execution_time'];
                }
            }

            if (isset($metric['data']['memory_used'])) {
                $memory_usages[] = $metric['data']['memory_used'];
                if ($metric['data']['memory_used'] > 50 * 1024 * 1024) {
                // 50MB
                    $stats['high_memory_requests']++;
                }
                if ($metric['data']['memory_used'] > $stats['max_memory_usage']) {
                    $stats['max_memory_usage'] = $metric['data']['memory_used'];
                }
            }
        }

        if (!empty($execution_times)) {
            $stats['avg_execution_time'] = array_sum($execution_times) / count($execution_times);
        }

        if (!empty($memory_usages)) {
            $stats['avg_memory_usage'] = array_sum($memory_usages) / count($memory_usages);
        }

        return $stats;
    }

    /**
     * Nettoyer les anciens logs de performance
     */
    public static function cleanupOldLogs($days = 30)
    {
        $log_file = WP_CONTENT_DIR . '/pdf-builder-performance.log';
        if (!file_exists($log_file)) {
            return;
        }

        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        $filtered_lines = [];
        foreach ($lines as $line) {
            $parts = explode(' - ', $line, 2);
            if (count($parts) === 2) {
                $timestamp = strtotime($parts[0]);
                if ($timestamp > $cutoff_time) {
                    $filtered_lines[] = $line;
                }
            }
        }

        // file_put_contents($log_file, implode("\n", $filtered_lines) . "\n");
    }
}

// Initialiser le monitoring si activé
if (
    defined('PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING') &&
    PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING
) {
    PDF_Builder_Performance_Monitor::init();
}
