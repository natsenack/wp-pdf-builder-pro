<?php
/**
 * Gestionnaire du Rate Limiting
 * Limite à 100 requêtes par minute par IP
 */

namespace WP_PDF_Builder_Pro\Security;

class Rate_Limiter {
    
    const LIMIT_PER_MINUTE = 100;
    const TRANSIENT_PREFIX = 'pdf_builder_rate_limit_';
    
    /**
     * Initialise le gestionnaire de rate limiting
     */
    public static function init() {
        add_action('plugins_loaded', [__CLASS__, 'check_rate_limit'], 10);
    }
    
    /**
     * Vérifie et applique le rate limiting
     */
    public static function check_rate_limit() {
        // Ne vérifier que pour les actions AJAX du PDF Builder
        if (!isset($_REQUEST['action'])) {
            return;
        }
        
        $action = sanitize_text_field($_REQUEST['action']);
        if (strpos($action, 'pdf_builder') !== 0) {
            return;
        }
        
        $ip = self::get_client_ip();
        $transient_key = self::TRANSIENT_PREFIX . $ip;
        
        $count = intval(get_transient($transient_key));
        
        if ($count >= self::LIMIT_PER_MINUTE) {
            // Trop de requêtes
            wp_send_json_error([
                'message' => sprintf(
                    'Trop de requêtes. Limite : %d par minute. Veuillez patienter.',
                    self::LIMIT_PER_MINUTE
                ),
                'code' => 'rate_limit_exceeded'
            ], 429); // HTTP 429 Too Many Requests
            exit;
        }
        
        // Incrémenter le compteur (expire après 1 minute)
        set_transient($transient_key, $count + 1, 60);
        
        // Log les requêtes fréquentes (> 50)
        if ($count > 50) {
            error_log("[PDF Builder] Rate Limit Warning: IP {$ip} has {$count} requests/min");
        }
    }
    
    /**
     * Obtient l'adresse IP du client
     */
    private static function get_client_ip() {
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
    public static function get_request_count($ip = null) {
        if ($ip === null) {
            $ip = self::get_client_ip();
        }
        
        $transient_key = self::TRANSIENT_PREFIX . $ip;
        return intval(get_transient($transient_key));
    }
    
    /**
     * Réinitialise le compteur pour une IP
     */
    public static function reset_for_ip($ip = null) {
        if ($ip === null) {
            $ip = self::get_client_ip();
        }
        
        $transient_key = self::TRANSIENT_PREFIX . $ip;
        delete_transient($transient_key);
    }
}

// Initialiser au chargement
Rate_Limiter::init();
