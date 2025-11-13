<?php

namespace WP_PDF_Builder_Pro\Analytics;

/**
 * Classe AnalyticsTracker
 * Implémentation de base du système d'analytics
 */
class AnalyticsTracker implements AnalyticsInterface
{
    /** @var string Préfixe pour les transients */
    private $transient_prefix = 'wp_pdf_analytics_';
/** @var int Durée de vie des transients (24h) */
    private $transient_expiry = 86400;

    public function trackEvent(string $event, array $data = [], ?int $user_id = null): void
    {
        // ✅ ANALYTICS DÉSACTIVÉ - les transients ne sont plus utilisés
        // Log immédiat pour debug
        $this->logInfo("Event tracked: {$event}");
    }

    public function trackPerformance(string $operation, float $duration, array $metadata = []): void
    {
        // ✅ ANALYTICS DÉSACTIVÉ - les transients ne sont plus utilisés
        $this->logInfo("Performance tracked: {$operation} ({$duration}s)");
    }

    public function trackError(string $error_type, string $message, array $context = []): void
    {
        // ✅ ANALYTICS DÉSACTIVÉ - les transients ne sont plus utilisés
    }

    public function getMetrics(string $metric_type, array $filters = []): array
    {
        // ✅ ANALYTICS DÉSACTIVÉ - retourner tableau vide
        return [];
    }

    public function getPopularTemplates(int $limit = 10, string $period = 'month'): array
    {
        $templates = [];

        // Simulation de données populaires (à remplacer par vraie logique)
        $popular_data = [
            ['template_id' => 1, 'name' => 'Facture Standard', 'usage_count' => 150],
            ['template_id' => 2, 'name' => 'Devis Commercial', 'usage_count' => 120],
            ['template_id' => 3, 'name' => 'Bon de Commande', 'usage_count' => 95]
        ];

        return array_slice($popular_data, 0, $limit);
    }

    public function getPerformanceMetrics(string $operation = 'all', string $period = 'week'): array
    {
        $metrics = [];

        // Récupération des métriques de performance
        $perf_data = $this->getMetrics('perf', ['period' => $period]);

        if ($operation === 'all') {
            $metrics = $perf_data;
        } else {
            $metrics = array_filter($perf_data, function ($item) use ($operation) {
                return $item['operation'] === $operation;
            });
        }

        return $metrics;
    }

    public function cleanupOldData(int $days_to_keep = 90): void
    {
        global $wpdb;

        $cutoff_time = current_time('timestamp') - ($days_to_keep * DAY_IN_SECONDS);

        // Suppression des anciens transients
        $old_transients = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %s",
            '_transient_' . $this->transient_prefix . '%',
            $cutoff_time
        ));

        foreach ($old_transients as $transient) {
            delete_transient(str_replace('_transient_', '', $transient));
        }

        $this->logInfo("Cleaned up old analytics data (>{$days_to_keep} days)");
    }

    /**
     * Récupère l'adresse IP du client
     *
     * @return string Adresse IP
     */
    private function getClientIP(): string
    {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Vérifie si les données correspondent aux filtres
     *
     * @param array $data Données à vérifier
     * @param array $filters Filtres à appliquer
     * @return bool True si correspond
     */
    private function matchesFilters(array $data, array $filters): bool
    {
        foreach ($filters as $key => $value) {
            if (!isset($data[$key]) || $data[$key] !== $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Log une information
     *
     * @param string $message Message
     */
    private function logInfo(string $message): void
    {
    }
}
