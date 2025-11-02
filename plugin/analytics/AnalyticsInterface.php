<?php
namespace WP_PDF_Builder_Pro\Analytics;

/**
 * Interface AnalyticsInterface
 * Définit le contrat pour le système d'analytics et métriques
 */
interface AnalyticsInterface {

    /**
     * Enregistre un événement d'utilisation
     *
     * @param string $event Nom de l'événement
     * @param array $data Données associées à l'événement
     * @param int|null $user_id ID de l'utilisateur (null pour anonyme)
     */
    public function trackEvent(string $event, array $data = [], ?int $user_id = null): void;

    /**
     * Enregistre les métriques de performance
     *
     * @param string $operation Nom de l'opération
     * @param float $duration Durée en secondes
     * @param array $metadata Métadonnées supplémentaires
     */
    public function trackPerformance(string $operation, float $duration, array $metadata = []): void;

    /**
     * Enregistre une erreur
     *
     * @param string $error_type Type d'erreur
     * @param string $message Message d'erreur
     * @param array $context Contexte de l'erreur
     */
    public function trackError(string $error_type, string $message, array $context = []): void;

    /**
     * Récupère les métriques d'utilisation
     *
     * @param string $metric_type Type de métrique
     * @param array $filters Filtres à appliquer
     * @return array Données des métriques
     */
    public function getMetrics(string $metric_type, array $filters = []): array;

    /**
     * Récupère les templates les plus populaires
     *
     * @param int $limit Nombre maximum de résultats
     * @param string $period Période (day, week, month)
     * @return array Liste des templates populaires
     */
    public function getPopularTemplates(int $limit = 10, string $period = 'month'): array;

    /**
     * Récupère les métriques de performance
     *
     * @param string $operation Opération spécifique ou 'all'
     * @param string $period Période
     * @return array Métriques de performance
     */
    public function getPerformanceMetrics(string $operation = 'all', string $period = 'week'): array;

    /**
     * Nettoie les anciennes données d'analytics
     *
     * @param int $days_to_keep Nombre de jours à conserver
     */
    public function cleanupOldData(int $days_to_keep = 90): void;
}

/**
 * Classe AnalyticsTracker
 * Implémentation de base du système d'analytics
 */
class AnalyticsTracker implements AnalyticsInterface {

    /** @var string Préfixe pour les transients */
    private $transient_prefix = 'wp_pdf_analytics_';

    /** @var int Durée de vie des transients (24h) */
    private $transient_expiry = 86400;

    public function trackEvent(string $event, array $data = [], ?int $user_id = null): void {
        $event_data = [
            'event' => $event,
            'data' => $data,
            'user_id' => $user_id,
            'timestamp' => time(),
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        // Stockage en base de données (à implémenter selon les besoins)
        $this->storeEventData($event_data);

        // Log pour debugging
        $this->logEvent($event, $data);
    }

    public function trackPerformance(string $operation, float $duration, array $metadata = []): void {
        $perf_data = [
            'operation' => $operation,
            'duration' => $duration,
            'metadata' => $metadata,
            'timestamp' => time(),
            'memory_usage' => memory_get_peak_usage(true),
            'php_version' => PHP_VERSION
        ];

        $this->storePerformanceData($perf_data);

        // Alertes sur performances dégradées
        if ($duration > 10) { // Plus de 10 secondes
            $this->logWarning("Slow operation detected: {$operation} took {$duration}s");
        }
    }

    public function trackError(string $error_type, string $message, array $context = []): void {
        $error_data = [
            'type' => $error_type,
            'message' => $message,
            'context' => $context,
            'timestamp' => time(),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            'server_info' => [
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
            ]
        ];

        $this->storeErrorData($error_data);
        $this->logError("{$error_type}: {$message}", $context);
    }

    public function getMetrics(string $metric_type, array $filters = []): array {
        $cache_key = $this->transient_prefix . 'metrics_' . $metric_type . '_' . md5(serialize($filters));

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $metrics = $this->fetchMetricsFromStorage($metric_type, $filters);

        set_transient($cache_key, $metrics, $this->transient_expiry);
        return $metrics;
    }

    public function getPopularTemplates(int $limit = 10, string $period = 'month'): array {
        $cache_key = $this->transient_prefix . 'popular_templates_' . $period . '_' . $limit;

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $templates = $this->fetchPopularTemplates($limit, $period);

        set_transient($cache_key, $templates, $this->transient_expiry);
        return $templates;
    }

    public function getPerformanceMetrics(string $operation = 'all', string $period = 'week'): array {
        $cache_key = $this->transient_prefix . 'perf_' . $operation . '_' . $period;

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $metrics = $this->fetchPerformanceMetrics($operation, $period);

        set_transient($cache_key, $metrics, $this->transient_expiry);
        return $metrics;
    }

    public function cleanupOldData(int $days_to_keep = 90): void {
        $cutoff_time = time() - ($days_to_keep * 86400);

        // Nettoyage des transients
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
            '_transient%' . $this->transient_prefix . '%',
            $cutoff_time
        ));

        // Nettoyage des données persistantes (à implémenter selon le stockage choisi)
        $this->cleanupPersistentData($cutoff_time);

        $this->logInfo("Cleaned up analytics data older than {$days_to_keep} days");
    }

    /**
     * Stocke les données d'événement
     *
     * @param array $event_data Données de l'événement
     */
    private function storeEventData(array $event_data): void {
        // Implémentation temporaire - utiliser transients pour développement
        $key = $this->transient_prefix . 'events_' . date('Y-m-d-H');
        $events = get_transient($key) ?: [];
        $events[] = $event_data;

        // Limiter à 100 événements par heure
        if (count($events) > 100) {
            array_shift($events);
        }

        set_transient($key, $events, $this->transient_expiry);
    }

    /**
     * Stocke les données de performance
     *
     * @param array $perf_data Données de performance
     */
    private function storePerformanceData(array $perf_data): void {
        $key = $this->transient_prefix . 'perf_' . date('Y-m-d-H');
        $metrics = get_transient($key) ?: [];
        $metrics[] = $perf_data;

        // Limiter à 50 métriques par heure
        if (count($metrics) > 50) {
            array_shift($metrics);
        }

        set_transient($key, $metrics, $this->transient_expiry);
    }

    /**
     * Stocke les données d'erreur
     *
     * @param array $error_data Données d'erreur
     */
    private function storeErrorData(array $error_data): void {
        $key = $this->transient_prefix . 'errors_' . date('Y-m-d');
        $errors = get_transient($key) ?: [];
        $errors[] = $error_data;

        set_transient($key, $errors, $this->transient_expiry);
    }

    /**
     * Récupère les métriques depuis le stockage
     *
     * @param string $metric_type Type de métrique
     * @param array $filters Filtres
     * @return array Données des métriques
     */
    private function fetchMetricsFromStorage(string $metric_type, array $filters): array {
        // Implémentation temporaire - retourner des données fictives
        return [
            'total_events' => \rand(100, 1000),
            'unique_users' => \rand(10, 100),
            'avg_session_duration' => \rand(60, 600),
            'conversion_rate' => \rand(5, 25) / 100
        ];
    }

    /**
     * Récupère les templates populaires
     *
     * @param int $limit Nombre maximum
     * @param string $period Période
     * @return array Templates populaires
     */
    private function fetchPopularTemplates(int $limit, string $period): array {
        // Implémentation temporaire - retourner des données fictives
        $templates = [];
        for ($i = 1; $i <= $limit; $i++) {
            $templates[] = [
                'id' => $i,
                'name' => "Template {$i}",
                'usage_count' => \rand(10, 100),
                'last_used' => \date('Y-m-d H:i:s', \time() - \rand(0, 86400 * 30))
            ];
        }

        return \array_slice($templates, 0, $limit);
    }

    /**
     * Récupère les métriques de performance
     *
     * @param string $operation Opération
     * @param string $period Période
     * @return array Métriques de performance
     */
    private function fetchPerformanceMetrics(string $operation, string $period): array {
        // Implémentation temporaire - retourner des données fictives
        return [
            'avg_duration' => \rand(1, 5) + (\rand(0, 99) / 100),
            'min_duration' => 0.5,
            'max_duration' => \rand(5, 15),
            'total_operations' => \rand(100, 1000),
            'success_rate' => (\rand(90, 99) / 100)
        ];
    }

    /**
     * Nettoie les données persistantes
     *
     * @param int $cutoff_time Timestamp limite
     */
    private function cleanupPersistentData(int $cutoff_time): void {
        // Implémentation selon le système de stockage choisi
        // Pour l'instant, rien à faire avec les transients
    }

    /**
     * Récupère l'IP du client
     *
     * @return string IP du client
     */
    private function getClientIP(): string {
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
                $ip = $_SERVER[$header];
                // Prendre la première IP si plusieurs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Log un événement
     *
     * @param string $event Nom de l'événement
     * @param array $data Données
     */
    private function logEvent(string $event, array $data): void {
        $message = "[Analytics Event] {$event}";
        if (!empty($data)) {
            $message .= " - " . json_encode($data);
        }
        error_log($message);
    }

    /**
     * Log un avertissement
     *
     * @param string $message Message
     */
    private function logWarning(string $message): void {
        error_log("[Analytics Warning] {$message}");
    }

    /**
     * Log une erreur
     *
     * @param string $message Message
     * @param array $context Contexte
     */
    private function logError(string $message, array $context = []): void {
        $full_message = "[Analytics Error] {$message}";
        if (!empty($context)) {
            $full_message .= " - Context: " . json_encode($context);
        }
        error_log($full_message);
    }

    /**
     * Log une information
     *
     * @param string $message Message
     */
    private function logInfo(string $message): void {
        error_log("[Analytics Info] {$message}");
    }
}