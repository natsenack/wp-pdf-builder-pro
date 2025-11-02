<?php
namespace WP_PDF_Builder_Pro\Api;

class PreviewImageAPI {
    private $cache_dir;
    private $max_cache_age = 3600; // 1 heure
    private $rate_limit_window = 60; // 1 minute
    private $rate_limit_max = 10; // 10 requêtes par minute
    private $request_log = [];

    public function __construct() {
        $this->cache_dir = (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : sys_get_temp_dir()) . '/cache/wp-pdf-builder-previews/';

        // Créer répertoire cache si inexistant
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }

        add_action('wp_ajax_wp_pdf_preview_image', array($this, 'generate_preview'));
        add_action('wp_ajax_nopriv_wp_pdf_preview_image', array($this, 'generate_preview')); // Pour les vendeurs

        // Nettoyage automatique du cache
        add_action('wp_pdf_cleanup_preview_cache', array($this, 'cleanup_cache'));
        if (!wp_next_scheduled('wp_pdf_cleanup_preview_cache')) {
            wp_schedule_event(time(), 'hourly', 'wp_pdf_cleanup_preview_cache');
        }
    }

    /**
     * Point d'entrée unifié pour tous les aperçus
     */
    public function generate_preview() {
        $start_time = microtime(true);

        try {
            // Validation sécurité multi-couches
            $this->validate_request();

            // Rate limiting
            $this->check_rate_limit();

            // Récupération et validation des paramètres
            $params = $this->get_validated_params();

            // Log de sécurité
            $this->log_request($params);

            // Génération avec cache intelligent
            $result = $this->generate_with_cache($params);

            // Métriques performance
            $this->log_performance($start_time, $params['context']);

            // Réponse compressée
            $this->send_compressed_response($result);

        } catch (\Exception $e) {
            $this->handle_error($e, $start_time);
        }
    }

    /**
     * Validation sécurité multi-couches
     */
    private function validate_request() {
        // Vérification nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            $this->log_security_event('invalid_nonce', $_SERVER['REMOTE_ADDR']);
            wp_send_json_error(['message' => 'Security check failed', 'code' => 'INVALID_NONCE'], 403);
        }

        // Vérification permissions selon contexte
        $context = sanitize_text_field($_POST['context'] ?? 'editor');
        $required_cap = $this->get_required_capability($context);

        if (!current_user_can($required_cap)) {
            $this->log_security_event('insufficient_permissions', $_SERVER['REMOTE_ADDR'], $context);
            wp_send_json_error(['message' => 'Insufficient permissions', 'code' => 'INSUFFICIENT_PERMISSIONS'], 403);
        }

        // Validation taille données
        if (isset($_POST['template_data'])) {
            $data_size = strlen($_POST['template_data']);
            if ($data_size > 1024 * 1024) { // 1MB max
                $this->log_security_event('data_too_large', $_SERVER['REMOTE_ADDR'], $data_size);
                wp_send_json_error(['message' => 'Data too large', 'code' => 'DATA_TOO_LARGE'], 413);
            }
        }
    }

    /**
     * Rate limiting pour prévenir les abus
     */
    private function check_rate_limit() {
        $user_id = get_current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = $user_id ?: $ip;

        $transient_key = 'wp_pdf_rate_limit_' . md5($key);
        $requests = get_transient($transient_key) ?: [];

        // Nettoyer les anciennes requêtes
        $now = time();
        $requests = array_filter($requests, function($timestamp) use ($now) {
            return ($now - $timestamp) < $this->rate_limit_window;
        });

        if (count($requests) >= $this->rate_limit_max) {
            $this->log_security_event('rate_limit_exceeded', $ip, count($requests));
            wp_die('Rate limit exceeded', 429);
        }

        $requests[] = $now;
        set_transient($transient_key, $requests, $this->rate_limit_window);
    }

    /**
     * Récupération et validation des paramètres selon contexte
     */
    private function get_validated_params() {
        $context = sanitize_text_field($_POST['context'] ?? 'editor');

        $params = [
            'context' => $context,
            'template_data' => null,
            'preview_type' => 'design',
            'order_id' => null,
            'quality' => 150,
            'format' => 'png'
        ];

        // Paramètres spécifiques selon contexte
        switch ($context) {
            case 'editor':
                // Aperçu design - données fictives
                $params['template_data'] = $this->validate_template_data($_POST['template_data'] ?? '');
                $params['preview_type'] = 'design';
                break;

            case 'metabox':
                // Aperçu commande réelle
                $params['template_data'] = $this->validate_template_data($_POST['template_data'] ?? '');
                $params['order_id'] = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
                $params['preview_type'] = $params['order_id'] ? 'order' : 'design';

                // Validation commande existe
                if ($params['order_id'] && function_exists('wc_get_order')) {
                    $order = wc_get_order($params['order_id']);
                    if (!$order) {
                        wp_die('Order not found', 404);
                    }
                }
                break;

            default:
                wp_die('Invalid context', 400);
        }

        // Paramètres optionnels
        if (isset($_POST['quality'])) {
            $params['quality'] = max(50, min(300, intval($_POST['quality'])));
        }

        if (isset($_POST['format'])) {
            $allowed_formats = ['png', 'jpg', 'jpeg'];
            $params['format'] = in_array(strtolower($_POST['format']), $allowed_formats) ?
                               strtolower($_POST['format']) : 'png';
        }

        return $params;
    }

    /**
     * Validation des données template
     */
    private function validate_template_data($data) {
        if (empty($data)) {
            return $this->get_default_template();
        }

        $decoded = json_decode(stripslashes($data), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die('Invalid template data', 400);
        }

        // Validation structure de base
        if (!isset($decoded['template']) || !isset($decoded['template']['elements'])) {
            wp_die('Invalid template structure', 400);
        }

        // Sanitisation des éléments
        $decoded['template']['elements'] = array_map(function($element) {
            return $this->sanitize_element($element);
        }, $decoded['template']['elements']);

        return $decoded;
    }

    /**
     * Sanitisation d'un élément template
     */
    private function sanitize_element($element) {
        $sanitized = [];

        // Champs autorisés selon type
        $allowed_fields = [
            'type', 'content', 'x', 'y', 'width', 'height', 'fontSize', 'fontFamily',
            'color', 'backgroundColor', 'textAlign', 'fontWeight', 'fontStyle'
        ];

        foreach ($allowed_fields as $field) {
            if (isset($element[$field])) {
                if (is_string($element[$field])) {
                    $sanitized[$field] = sanitize_text_field($element[$field]);
                } elseif (is_numeric($element[$field])) {
                    $sanitized[$field] = floatval($element[$field]);
                } else {
                    $sanitized[$field] = $element[$field];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Génération avec cache intelligent
     */
    private function generate_with_cache($params) {
        $cache_key = $this->generate_cache_key($params);
        $cache_file = $this->cache_dir . $cache_key . '.' . $params['format'];

        // Vérifier cache valide
        if ($this->is_cache_valid($cache_file, $params)) {
            return [
                'image_url' => $this->get_cache_url($cache_key, $params['format']),
                'cached' => true,
                'cache_key' => $cache_key
            ];
        }

        // Générer nouveau aperçu
        $generator = $this->create_generator($params);
        $image_path = $generator->generate_preview_image($params['quality'], $params['format']);

        // Copier vers cache
        if (file_exists($image_path)) {
            copy($image_path, $cache_file);
            unlink($image_path); // Supprimer fichier temporaire
        }

        return [
            'image_url' => $this->get_cache_url($cache_key, $params['format']),
            'cached' => false,
            'cache_key' => $cache_key
        ];
    }

    /**
     * Création du générateur selon contexte
     */
    private function create_generator($params) {
        $use_fallback = true; // Toujours utiliser fallback pour aperçu

        if ($params['preview_type'] === 'order' && $params['order_id'] && function_exists('wc_get_order')) {
            $order = wc_get_order($params['order_id']);
            $data_provider = new \WP_PDF_Builder_Pro\Data\WooCommerceDataProvider();
            $data_provider->setOrder($order);
            return new \WP_PDF_Builder_Pro\Generators\PDFGenerator($params['template_data'], $data_provider, $use_fallback);
        } else {
            // Données fictives pour aperçu design
            $data_provider = new \WP_PDF_Builder_Pro\Data\SampleDataProvider();
            return new \WP_PDF_Builder_Pro\Generators\PDFGenerator($params['template_data'], $data_provider, $use_fallback);
        }
    }

    /**
     * Génération clé cache unique
     */
    private function generate_cache_key($params) {
        $data = [
            'template' => md5(serialize($params['template_data'])),
            'context' => $params['context'],
            'order_id' => $params['order_id'],
            'quality' => $params['quality'],
            'format' => $params['format']
        ];
        return md5(serialize($data));
    }

    /**
     * Vérification validité cache
     */
    private function is_cache_valid($cache_file, $params) {
        if (!file_exists($cache_file)) {
            return false;
        }

        // Vérifier âge du fichier
        $file_age = time() - filemtime($cache_file);
        if ($file_age > $this->max_cache_age) {
            return false;
        }

        // Pour les aperçus de commande, vérifier si commande modifiée récemment
        if ($params['order_id'] && function_exists('wc_get_order')) {
            $order = wc_get_order($params['order_id']);
            if ($order) {
                $order_modified = strtotime($order->get_date_modified());
                if ($order_modified > filemtime($cache_file)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * URL du fichier en cache
     */
    private function get_cache_url($cache_key, $format) {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/cache/wp-pdf-builder-previews/' . $cache_key . '.' . $format;
    }

    /**
     * Nettoyage automatique du cache
     */
    public function cleanup_cache() {
        if (!is_dir($this->cache_dir)) {
            return;
        }

        $files = glob($this->cache_dir . '*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $this->max_cache_age) {
                unlink($file);
            }
        }
    }

    /**
     * Template par défaut pour validation
     */
    private function get_default_template() {
        return [
            'template' => [
                'elements' => [
                    [
                        'type' => 'text',
                        'content' => 'Aperçu PDF Builder Pro',
                        'x' => 50,
                        'y' => 50,
                        'width' => 200,
                        'height' => 30,
                        'fontSize' => 16,
                        'color' => '#000000'
                    ]
                ]
            ]
        ];
    }

    /**
     * Permission requise selon contexte
     */
    private function get_required_capability($context) {
        switch ($context) {
            case 'editor':
                return 'manage_options'; // Admin seulement pour éditeur
            case 'metabox':
                return 'edit_shop_orders'; // Vendeurs peuvent voir metabox
            default:
                return 'manage_options';
        }
    }

    /**
     * Log des événements de sécurité
     */
    private function log_security_event($event, $ip, $extra = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $message = sprintf(
                '[PDF Builder Security] %s - IP: %s - Extra: %s',
                $event,
                $ip,
                is_scalar($extra) ? $extra : json_encode($extra)
            );
            error_log($message);
        }
    }

    /**
     * Log des requêtes pour monitoring
     */
    private function log_request($params) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->request_log[] = [
                'timestamp' => time(),
                'user_id' => get_current_user_id(),
                'context' => $params['context'],
                'order_id' => $params['order_id']
            ];
        }
    }

    /**
     * Log des performances
     */
    private function log_performance($start_time, $context) {
        $duration = microtime(true) - $start_time;

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[PDF Builder Performance] Context: %s - Duration: %.3fs',
                $context,
                $duration
            ));
        }

        // Alerte si génération lente (>2s)
        if ($duration > 2.0) {
            error_log(sprintf(
                '[PDF Builder Slow Generation] Context: %s - Duration: %.3fs - Alert!',
                $context,
                $duration
            ));
        }
    }

    /**
     * Réponse compressée pour performance
     */
    private function send_compressed_response($result) {
        // Compression GZIP si supportée
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            ob_start('ob_gzhandler');
        }

        wp_send_json_success($result);
    }

    /**
     * Gestion centralisée des erreurs
     */
    private function handle_error($exception, $start_time) {
        $duration = microtime(true) - $start_time;
        $error_message = $exception->getMessage();

        // Log détaillé de l'erreur
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[PDF Builder Error] %s - Duration: %.3fs - File: %s:%d',
                $error_message,
                $duration,
                $exception->getFile(),
                $exception->getLine()
            ));
        }

        // Réponse d'erreur générique pour sécurité
        wp_send_json_error([
            'message' => 'Preview generation failed',
            'code' => 'PREVIEW_ERROR'
        ]);
    }
}