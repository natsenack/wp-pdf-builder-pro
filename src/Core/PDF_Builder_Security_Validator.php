<?php
/**
 * PDF Builder Pro - Validateur de Sécurité
 *
 * Classe responsable de la validation et sanitisation des inputs
 * pour prévenir les attaques XSS et autres vulnérabilités
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 * @since   Phase 5.8 - Corrections Sécurité
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PDF_Builder_Security_Validator
{

    /**
     * Sanitise le contenu HTML pour prévenir les attaques XSS
     *
     * @param  string $content Contenu HTML à sanitiser
     * @return string Contenu HTML sécurisé
     */
    public static function sanitize_html_content($content)
    {
        if (empty($content)) {
            return '';
        }

        // Liste des tags HTML autorisés pour les PDFs
        $allowed_tags = [
            'p' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'br' => [],
            'strong' => [
                'style' => [],
                'class' => []
            ],
            'em' => [
                'style' => [],
                'class' => []
            ],
            'u' => [
                'style' => [],
                'class' => []
            ],
            'h1' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'h2' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'h3' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'h4' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'h5' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'h6' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'table' => [
                'style' => [],
                'class' => [],
                'border' => [],
                'cellpadding' => [],
                'cellspacing' => []
            ],
            'tr' => [
                'style' => [],
                'class' => []
            ],
            'td' => [
                'style' => [],
                'class' => [],
                'colspan' => [],
                'rowspan' => []
            ],
            'th' => [
                'style' => [],
                'class' => [],
                'colspan' => [],
                'rowspan' => []
            ],
            'thead' => [
                'style' => [],
                'class' => []
            ],
            'tbody' => [
                'style' => [],
                'class' => []
            ],
            'img' => [
                'src' => [],
                'alt' => [],
                'style' => [],
                'class' => [],
                'width' => [],
                'height' => []
            ],
            'div' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'span' => [
                'style' => [],
                'class' => [],
                'id' => []
            ],
            'ul' => [
                'style' => [],
                'class' => []
            ],
            'ol' => [
                'style' => [],
                'class' => []
            ],
            'li' => [
                'style' => [],
                'class' => []
            ]
        ];

        // Utilisation de wp_kses pour sanitisation
        $sanitized = wp_kses($content, $allowed_tags);

        // Log des modifications pour audit
        if ($sanitized !== $content) {
            self::log_security_event(
                'html_sanitized', [
                'original_length' => strlen($content),
                'sanitized_length' => strlen($sanitized),
                'user_id' => get_current_user_id(),
                'ip' => self::get_client_ip()
                ]
            );
        }

        return $sanitized;
    }

    /**
     * Valide et sanitise les données JSON
     *
     * @param  string $json_data Données JSON à valider
     * @return mixed Données décodées et validées ou false si invalide
     */
    public static function validate_json_data($json_data)
    {
        if (empty($json_data)) {
            return false;
        }

        // Décodage JSON sécurisé
        $data = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            self::log_security_event(
                'invalid_json', [
                'error' => json_last_error_msg(),
                'user_id' => get_current_user_id(),
                'ip' => self::get_client_ip()
                ]
            );
            return false;
        }

        // Validation récursive des données
        return self::sanitize_array_data($data);
    }

    /**
     * Sanitise récursivement un tableau de données
     *
     * @param  array $data Données à sanitiser
     * @return array Données sanitizées
     */
    private static function sanitize_array_data($data)
    {
        if (!is_array($data)) {
            return is_string($data) ? sanitize_text_field($data) : $data;
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            $clean_key = sanitize_key($key);
            $sanitized[$clean_key] = self::sanitize_array_data($value);
        }

        return $sanitized;
    }

    /**
     * Valide un nonce WordPress
     *
     * @param  string $nonce  Valeur du nonce
     * @param  string $action Action associée au nonce
     * @return bool True si valide
     */
    public static function validate_nonce($nonce, $action)
    {
        $valid = wp_verify_nonce($nonce, $action);

        if (!$valid) {
            self::log_security_event(
                'invalid_nonce', [
                'action' => $action,
                'user_id' => get_current_user_id(),
                'ip' => self::get_client_ip()
                ]
            );
        }

        return $valid;
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
                // Gestion des IPs multiples (dernière IP dans la chaîne)
                if (strpos($ip, ',') !== false) {
                    $ip_parts = explode(',', $ip);
                    $ip = trim(end($ip_parts));
                }
                // Validation de l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Log un événement de sécurité
     *
     * @param string $event Type d'événement
     * @param array  $data  Données
     *                      supplémentaires
     */
    private static function log_security_event($event, $data = [])
    {
        $log_data = array_merge(
            [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'
            ], $data
        );

        // Log dans le fichier de debug WordPress si activé
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF_Builder_Security: ' . json_encode($log_data));
        }

        // Stockage en base pour audit (optionnel)
        // self::store_security_log($log_data);
    }

    /**
     * Vérifie si l'utilisateur a les permissions requises
     *
     * @param  string $capability Capacité requise (par défaut 'manage_options')
     * @return bool True si autorisé
     */
    public static function check_permissions($capability = 'manage_options')
    {
        if (!current_user_can($capability)) {
            self::log_security_event(
                'insufficient_permissions', [
                'required_capability' => $capability,
                'user_id' => get_current_user_id(),
                'ip' => self::get_client_ip()
                ]
            );
            return false;
        }
        return true;
    }
}
