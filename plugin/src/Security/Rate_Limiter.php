<?php

/**
 * Gestionnaire du Rate Limiting
 * Limite à 100 requêtes par minute par IP
 */

namespace PDF_Builder\Security;

class Rate_Limiter
{
    const LIMIT_PER_MINUTE = 100;
    const TRANSIENT_PREFIX = 'pdf_builder_rate_limit_';

    /**
     * Initialise le gestionnaire de rate limiting
     */
    public static function init()
    {
        add_action('plugins_loaded', [__CLASS__, 'checkRateLimit'], 10);
    }

    /**
     * Vérifie et applique le rate limiting
     */
    public static function checkRateLimit()
    {
        // ✅ RATE LIMIT ACTIVÉ - Protection contre les attaques par déni de service
        if (!isset($_REQUEST['action'])) {
            return;
        }

        $action = sanitize_text_field($_REQUEST['action']);
        if (strpos($action, 'pdf_builder') !== 0) {
            return;
        }

        $ip = self::getClientIp();
        $transient_key = self::TRANSIENT_PREFIX . md5($ip . $action);

        $requests = get_transient($transient_key);
        if ($requests === false) {
            $requests = 0;
        }

        $requests++;

        // Limite plus stricte pour les actions sensibles
        $sensitive_actions = ['pdf_builder_save_template', 'pdf_builder_delete_template'];
        $limit = in_array($action, $sensitive_actions) ? 10 : self::LIMIT_PER_MINUTE;

        if ($requests > $limit) {
            // Log l'incident de sécurité
            error_log(sprintf(
                'PDF_BUILDER_SECURITY: Rate limit exceeded for IP %s on action %s (%d requests)',
                $ip,
                $action,
                $requests
            ));

            // Réponse d'erreur avec code HTTP approprié
            http_response_code(429);
            wp_die(__('Trop de requêtes. Veuillez patienter avant de réessayer.', 'pdf-builder'), 429);
        }

        // Stocker le compteur (expire après 1 minute)
        set_transient($transient_key, $requests, 60);
    }

    /**
     * Obtient l'adresse IP du client
     */
    private static function getClientIp()
    {
        $headers = [
            'CF-CONNECTING-IP',  // Cloudflare
            'X-FORWARDED-FOR',   // Proxy/Load Balancer
            'X-FORWARDED',       // Proxy
            'FORWARDED-FOR',     // RFC 7239
            'FORWARDED',         // RFC 7239
            'CLIENT_IP',         // Apache
            'HTTP_CLIENT_IP'     // Client IP
        ];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        // Fallback
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Obtient le nombre de requêtes pour une IP donnée
     */
    public static function getRequestCount($ip = null)
    {
        if ($ip === null) {
            $ip = self::getClientIp();
        }

        $transient_key = self::TRANSIENT_PREFIX . $ip;
        return intval(get_transient($transient_key));
    }

    /**
     * Réinitialise le compteur pour une IP
     */
    public static function resetForIp($ip = null)
    {
        if ($ip === null) {
            $ip = self::getClientIp();
        }

        $transient_key = self::TRANSIENT_PREFIX . $ip;
        delete_transient($transient_key);
    }
}

// Initialiser au chargement
Rate_Limiter::init();
