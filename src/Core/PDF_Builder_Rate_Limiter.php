<?php
/**
 * PDF Builder Pro - Rate Limiter
 *
 * Classe responsable de la limitation du taux de requêtes
 * pour prévenir les attaques par déni de service
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 * @since   Phase 5.8 - Corrections Sécurité
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PDF_Builder_Rate_Limiter
{

    /**
     * Limites par défaut
     */
    const DEFAULT_LIMITS = [
        'pdf_generation' => [
            'max_requests' => 10,    // 10 générations par minute
            'window' => 60           // fenêtre de 60 secondes
        ],
        'admin_actions' => [
            'max_requests' => 30,    // 30 actions admin par minute
            'window' => 60
        ],
        'api_calls' => [
            'max_requests' => 100,   // 100 appels API par minute
            'window' => 60
        ]
    ];

    /**
     * Vérifie si la limite de taux est respectée
     *
     * @param  string $action  Type d'action (pdf_generation, admin_actions, api_calls)
     * @param  int    $user_id ID utilisateur (optionnel, utilise session si non fourni)
     * @return bool True si autorisé, false si limite dépassée
     */
    public static function check_rate_limit($action = 'pdf_generation', $user_id = null)
    {
        // Utilisation de l'ID utilisateur ou de la session
        if ($user_id === null) {
            $user_id = get_current_user_id();
            if (!$user_id) {
                // Pour les utilisateurs non connectés, utiliser l'IP
                $user_id = 'ip_' . self::get_client_ip_hash();
            }
        }

        $limits = self::get_limits_for_action($action);
        $key = self::build_cache_key($action, $user_id);

        // Récupération du compteur actuel
        $current_count = get_transient($key) ?: 0;

        // Vérification de la limite
        if ($current_count >= $limits['max_requests']) {
            self::log_rate_limit_exceeded($action, $user_id, $current_count, $limits['max_requests']);
            return false;
        }

        // Incrémentation du compteur
        set_transient($key, $current_count + 1, $limits['window']);

        return true;
    }

    /**
     * Obtient le nombre de requêtes restantes avant limite
     *
     * @param  string $action  Type d'action
     * @param  int    $user_id ID utilisateur
     * @return int Nombre de requêtes restantes
     */
    public static function get_remaining_requests($action = 'pdf_generation', $user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
            if (!$user_id) {
                $user_id = 'ip_' . self::get_client_ip_hash();
            }
        }

        $limits = self::get_limits_for_action($action);
        $key = self::build_cache_key($action, $user_id);

        $current_count = get_transient($key) ?: 0;
        return max(0, $limits['max_requests'] - $current_count);
    }

    /**
     * Obtient le temps restant avant reset du compteur
     *
     * @param  string $action  Type d'action
     * @param  int    $user_id ID utilisateur
     * @return int Temps en secondes
     */
    public static function get_reset_time($action = 'pdf_generation', $user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
            if (!$user_id) {
                $user_id = 'ip_' . self::get_client_ip_hash();
            }
        }

        $key = self::build_cache_key($action, $user_id);

        // Vérification si la clé existe
        $timeout = wp_cache_get($key . '_timeout', 'transient');
        if (!$timeout) {
            return 0;
        }

        return max(0, $timeout - time());
    }

    /**
     * Reset manuel du compteur (pour admin)
     *
     * @param  string $action  Type d'action
     * @param  int    $user_id ID utilisateur
     * @return bool True si reset réussi
     */
    public static function reset_counter($action = 'pdf_generation', $user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
            if (!$user_id) {
                $user_id = 'ip_' . self::get_client_ip_hash();
            }
        }

        $key = self::build_cache_key($action, $user_id);
        delete_transient($key);

        self::log_rate_limit_reset($action, $user_id);

        return true;
    }

    /**
     * Obtient les limites pour une action spécifique
     *
     * @param  string $action Type d'action
     * @return array Configuration des limites
     */
    private static function get_limits_for_action($action)
    {
        // Possibilité de personnaliser via options WordPress
        $custom_limits = get_option('pdf_builder_rate_limits', []);

        if (isset($custom_limits[$action])) {
            return wp_parse_args($custom_limits[$action], self::DEFAULT_LIMITS[$action] ?? self::DEFAULT_LIMITS['pdf_generation']);
        }

        return self::DEFAULT_LIMITS[$action] ?? self::DEFAULT_LIMITS['pdf_generation'];
    }

    /**
     * Construit la clé de cache pour le rate limiting
     *
     * @param  string $action     Type d'action
     * @param  string $identifier Identifiant utilisateur/IP
     * @return string Clé de cache
     */
    private static function build_cache_key($action, $identifier)
    {
        return 'pdf_rate_' . $action . '_' . md5($identifier);
    }

    /**
     * Hash l'adresse IP pour anonymisation
     *
     * @return string Hash de l'IP
     */
    private static function get_client_ip_hash()
    {
        $ip = self::get_client_ip();
        return hash('sha256', $ip . wp_salt('nonce'));
    }

    /**
     * Obtient l'adresse IP du client
     *
     * @return string Adresse IP
     */
    private static function get_client_ip()
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
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip_parts = explode(',', $ip);
                    $ip = trim(end($ip_parts));
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Log quand la limite est dépassée
     *
     * @param string $action        Action
     *                              concernée
     * @param string $identifier    Identifiant utilisateur
     * @param int    $current_count Nombre actuel de
     *                              requêtes
     * @param int    $max_requests  Limite maximale
     */
    private static function log_rate_limit_exceeded($action, $identifier, $current_count, $max_requests)
    {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'event' => 'rate_limit_exceeded',
            'action' => $action,
            'identifier' => $identifier,
            'current_count' => $current_count,
            'max_requests' => $max_requests,
            'ip' => self::get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF_Builder_Rate_Limit: ' . json_encode($log_data));
        }

        // Alerte admin si nécessaire (optionnel)
        self::maybe_send_admin_alert($action, $identifier, $current_count);
    }

    /**
     * Log quand le compteur est reset
     *
     * @param string $action     Action
     *                           concernée
     * @param string $identifier Identifiant utilisateur
     */
    private static function log_rate_limit_reset($action, $identifier)
    {
        $log_data = [
            'timestamp' => current_time('mysql'),
            'event' => 'rate_limit_reset',
            'action' => $action,
            'identifier' => $identifier,
            'ip' => self::get_client_ip()
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF_Builder_Rate_Limit: ' . json_encode($log_data));
        }
    }

    /**
     * Envoie une alerte admin si nécessaire
     *
     * @param string $action        Action
     *                              concernée
     * @param string $identifier    Identifiant utilisateur
     * @param int    $current_count Nombre de
     *                              requêtes
     */
    private static function maybe_send_admin_alert($action, $identifier, $current_count)
    {
        // Évite les spam d'alertes (une alerte par heure max)
        $alert_key = 'pdf_rate_alert_' . $action . '_' . date('Y-m-d-H');
        if (get_transient($alert_key)) {
            return;
        }

        set_transient($alert_key, true, HOUR_IN_SECONDS);

        $subject = sprintf('[PDF Builder] Alerte Rate Limiting - %s', ucfirst($action));
        $message = sprintf(
            "Alerte de sécurité PDF Builder :\n\n" .
            "Limite de taux dépassée pour l'action : %s\n" .
            "Identifiant : %s\n" .
            "Nombre de requêtes : %d\n" .
            "Adresse IP : %s\n" .
            "Timestamp : %s\n\n" .
            "Vérifiez les logs pour plus de détails.",
            $action,
            $identifier,
            $current_count,
            self::get_client_ip(),
            current_time('mysql')
        );

        wp_mail(get_option('admin_email'), $subject, $message);
    }

    /**
     * Configure les limites personnalisées via l'interface admin
     *
     * @param  array $custom_limits Nouvelles limites
     * @return bool True si sauvegardé
     */
    public static function set_custom_limits($custom_limits)
    {
        // Validation des limites
        $validated_limits = [];
        foreach ($custom_limits as $action => $limits) {
            if (isset($limits['max_requests']) && isset($limits['window'])) {
                $validated_limits[$action] = [
                    'max_requests' => max(1, intval($limits['max_requests'])),
                    'window' => max(10, intval($limits['window']))
                ];
            }
        }

        return update_option('pdf_builder_rate_limits', $validated_limits);
    }

    /**
     * Obtient les statistiques de rate limiting
     *
     * @return array Statistiques
     */
    public static function get_stats()
    {
        global $wpdb;

        // Cette fonction nécessiterait une table de logs personnalisée
        // Pour l'instant, retourne des stats basiques
        return [
            'total_blocks_today' => 0, // À implémenter avec table de logs
            'most_blocked_action' => '',
            'most_blocked_ip' => ''
        ];
    }
}
