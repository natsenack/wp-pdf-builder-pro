<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.PHP.DevelopmentFunctions.error_log_error_log

namespace PDF_Builder\Analytics;

/**
 * Classe AnalyticsTracker
 * Implémentation légère du système d'analytics avec anonymisation
 */
class AnalyticsTracker implements AnalyticsInterface
{
    /** @var string Préfixe pour les options */
    private $option_prefix = 'pdf_builder_analytics_';
    /** @var int Durée de vie des données (30 jours) */
    private $data_retention = 2592000; // 30 jours en secondes

    public function __construct()
    {
        // Nettoyer automatiquement les anciennes données
        $this->cleanupOldData();
    }

    public function trackEvent(string $event, array $data = [], ?int $user_id = null): void
    {
        // Vérifier si l'analytics est activé
        if (!$this->isAnalyticsEnabled()) {
            return;
        }

        // Anonymiser les données sensibles
        $anonymized_data = $this->anonymizeData($data);

        // Stocker l'événement de manière anonymisée
        $this->storeEvent($event, $anonymized_data);

        // Log pour debug si mode développeur
        if ($this->isDeveloperMode()) {
            $this->logInfo("Event tracked: {$event}");
        }
    }

    public function trackPerformance(string $operation, float $duration, array $metadata = []): void
    {
        if (!$this->isAnalyticsEnabled()) {
            return;
        }

        // Anonymiser les métadonnées
        $anonymized_metadata = $this->anonymizeData($metadata);

        // Stocker les métriques de performance
        $this->storePerformanceMetric($operation, $duration, $anonymized_metadata);

        if ($this->isDeveloperMode()) {
            $this->logInfo("Performance tracked: {$operation} ({$duration}s)");
        }
    }

    public function trackError(string $error_type, string $message, array $context = []): void
    {
        if (!$this->isAnalyticsEnabled()) {
            return;
        }

        // Anonymiser le contexte d'erreur
        $anonymized_context = $this->anonymizeData($context);

        // Stocker l'erreur (sans le message détaillé pour la confidentialité)
        $this->storeError($error_type, $anonymized_context);

        if ($this->isDeveloperMode()) {
            $this->logInfo("Error tracked: {$error_type}");
        }
    }

    public function getMetrics(string $metric_type, array $filters = []): array
    {
        if (!$this->isAnalyticsEnabled()) {
            return [];
        }

        return $this->retrieveMetrics($metric_type, $filters);
    }

    public function getPopularTemplates(int $limit = 10, string $period = 'month'): array
    {
        if (!$this->isAnalyticsEnabled()) {
            // Retourner des données simulées si analytics désactivé
            return $this->getSimulatedPopularTemplates($limit);
        }

        return $this->retrievePopularTemplates($limit, $period);
    }

    /**
     * Vérifie si l'analytics est activé
     */
    private function isAnalyticsEnabled(): bool
    {
        return pdf_builder_get_option('pdf_builder_analytics_enabled', false) === true;
    }

    /**
     * Vérifie si le mode développeur est activé
     */
    private function isDeveloperMode(): bool
    {
        return function_exists('pdf_builder_is_developer_mode_active') && pdf_builder_is_developer_mode_active();
    }

    /**
     * Anonymise les données sensibles
     */
    private function anonymizeData(array $data): array
    {
        $anonymized = [];

        foreach ($data as $key => $value) {
            // Champs sensibles à anonymiser
            $sensitive_fields = [
                'email', 'user_email', 'customer_email',
                'name', 'user_name', 'customer_name', 'first_name', 'last_name',
                'phone', 'address', 'ip', 'user_ip',
                'password', 'token', 'key', 'secret'
            ];

            if (in_array(strtolower($key), $sensitive_fields)) {
                // Remplacer par un hash anonymisé ou supprimer
                $anonymized[$key] = $this->anonymizeValue($value);
            } elseif (is_array($value)) {
                // Récursivement anonymiser les sous-tableaux
                $anonymized[$key] = $this->anonymizeData($value);
            } elseif (is_string($value) && strlen($value) > 100) {
                // Tronquer les longues chaînes
                $anonymized[$key] = substr($value, 0, 100) . '...';
            } else {
                $anonymized[$key] = $value;
            }
        }

        return $anonymized;
    }

    /**
     * Anonymise une valeur individuelle
     */
    private function anonymizeValue($value): string
    {
        if (empty($value)) {
            return '';
        }

        // Pour les emails : hash du domaine uniquement
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $value);
            return 'user@' . substr(md5($parts[1]), 0, 8) . '.anon';
        }

        // Pour les noms : première lettre + hash
        if (is_string($value) && strlen($value) > 2) {
            return substr($value, 0, 1) . '***' . substr(md5($value), 0, 4);
        }

        // Pour autres valeurs : hash simple
        return substr(md5((string)$value), 0, 8);
    }

    /**
     * Stocke un événement anonymisé
     */
    private function storeEvent(string $event, array $data): void
    {
        $events = get_option($this->option_prefix . 'events', []);
        $events[] = [
            'event' => $event,
            'data' => $data,
            'timestamp' => time(),
            'session_id' => $this->getSessionId()
        ];

        // Garder seulement les 1000 derniers événements
        if (count($events) > 1000) {
            $events = array_slice($events, -1000);
        }

        update_option($this->option_prefix . 'events', $events);
    }

    /**
     * Stocke une métrique de performance
     */
    private function storePerformanceMetric(string $operation, float $duration, array $metadata): void
    {
        $metrics = get_option($this->option_prefix . 'performance', []);
        $metrics[] = [
            'operation' => $operation,
            'duration' => $duration,
            'metadata' => $metadata,
            'timestamp' => time()
        ];

        // Garder seulement les 500 dernières métriques
        if (count($metrics) > 500) {
            $metrics = array_slice($metrics, -500);
        }

        update_option($this->option_prefix . 'performance', $metrics);
    }

    /**
     * Stocke une erreur anonymisée
     */
    private function storeError(string $error_type, array $context): void
    {
        $errors = get_option($this->option_prefix . 'errors', []);
        $errors[] = [
            'type' => $error_type,
            'context' => $context,
            'timestamp' => time(),
            'count' => 1
        ];

        // Agréger les erreurs similaires
        $aggregated = [];
        foreach ($errors as $error) {
            $key = $error['type'] . '_' . md5(serialize($error['context']));
            if (!isset($aggregated[$key])) {
                $aggregated[$key] = $error;
            } else {
                $aggregated[$key]['count']++;
                $aggregated[$key]['timestamp'] = max($aggregated[$key]['timestamp'], $error['timestamp']);
            }
        }

        // Garder seulement les 100 dernières erreurs
        $aggregated = array_slice($aggregated, -100);

        update_option($this->option_prefix . 'errors', array_values($aggregated));
    }

    /**
     * Récupère les métriques
     */
    private function retrieveMetrics(string $metric_type, array $filters = []): array
    {
        switch ($metric_type) {
            case 'events':
                return get_option($this->option_prefix . 'events', []);
            case 'performance':
                return get_option($this->option_prefix . 'performance', []);
            case 'errors':
                return get_option($this->option_prefix . 'errors', []);
            default:
                return [];
        }
    }

    /**
     * Récupère les templates populaires
     */
    private function retrievePopularTemplates(int $limit = 10, string $period = 'month'): array
    {
        $events = get_option($this->option_prefix . 'events', []);
        $template_usage = [];

        // Calculer la période
        $period_seconds = $this->getPeriodSeconds($period);
        $cutoff_time = time() - $period_seconds;

        foreach ($events as $event) {
            if ($event['timestamp'] < $cutoff_time) {
                continue;
            }

            if (isset($event['data']['template_id'])) {
                $template_id = $event['data']['template_id'];
                if (!isset($template_usage[$template_id])) {
                    $template_usage[$template_id] = [
                        'template_id' => $template_id,
                        'usage_count' => 0,
                        'last_used' => $event['timestamp']
                    ];
                }
                $template_usage[$template_id]['usage_count']++;
                $template_usage[$template_id]['last_used'] = max(
                    $template_usage[$template_id]['last_used'],
                    $event['timestamp']
                );
            }
        }

        // Trier par usage décroissant
        usort($template_usage, function($a, $b) {
            return $b['usage_count'] <=> $a['usage_count'];
        });

        return array_slice($template_usage, 0, $limit);
    }

    /**
     * Retourne des données simulées si analytics désactivé
     */
    private function getSimulatedPopularTemplates(int $limit): array
    {
        return [
            ['template_id' => 1, 'name' => 'Facture Standard', 'usage_count' => 150],
            ['template_id' => 2, 'name' => 'Devis Commercial', 'usage_count' => 120],
            ['template_id' => 3, 'name' => 'Bon de Commande', 'usage_count' => 95]
        ];
    }

    /**
     * Génère un ID de session anonymisé
     */
    private function getSessionId(): string
    {
        $session_id = session_id();
        if (empty($session_id)) {
            $session_id = uniqid('session_', true);
        }
        return substr(md5($session_id), 0, 16);
    }

    /**
     * Convertit une période en secondes
     */
    private function getPeriodSeconds(string $period): int
    {
        switch ($period) {
            case 'day': return 86400;
            case 'week': return 604800;
            case 'month': return 2592000;
            case 'year': return 31536000;
            default: return 2592000; // month
        }
    }

    /**
     * Log pour debug
     */
    private function logInfo(string $message): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {

        }
    }

    /**
     * Récupère les métriques de performance
     */
    public function getPerformanceMetrics(string $operation = 'all', string $period = 'week'): array
    {
        $metrics = [];

        // Récupération des métriques de performance
        $perf_data = $this->getMetrics('performance', ['period' => $period]);

        if ($operation === 'all') {
            $metrics = $perf_data;
        } else {
            $metrics = array_filter($perf_data, function ($item) use ($operation) {
                return $item['operation'] === $operation;
            });
        }

        return $metrics;
    }

    /**
     * Nettoie les anciennes données avec paramètre personnalisable
     */
    public function cleanupOldData(int $days_to_keep = 90): void
    {
        $cutoff_time = time() - ($days_to_keep * 86400); // Convertir jours en secondes

        // Nettoyer les événements
        $events = get_option($this->option_prefix . 'events', []);
        $events = array_filter($events, function($event) use ($cutoff_time) {
            return $event['timestamp'] > $cutoff_time;
        });
        update_option($this->option_prefix . 'events', array_values($events));

        // Nettoyer les métriques de performance
        $performance = get_option($this->option_prefix . 'performance', []);
        $performance = array_filter($performance, function($metric) use ($cutoff_time) {
            return $metric['timestamp'] > $cutoff_time;
        });
        update_option($this->option_prefix . 'performance', array_values($performance));

        // Nettoyer les erreurs
        $errors = get_option($this->option_prefix . 'errors', []);
        $errors = array_filter($errors, function($error) use ($cutoff_time) {
            return $error['timestamp'] > $cutoff_time;
        });
        update_option($this->option_prefix . 'errors', array_values($errors));

        $this->logInfo("Cleaned up old analytics data (>{$days_to_keep} days)");
    }

    /**
     * Récupère l'adresse IP du client
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
}





