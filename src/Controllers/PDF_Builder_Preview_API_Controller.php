<?php
/**
 * PDF Builder Pro - Endpoints AJAX pour le système d'aperçu unifié
 * Phase 2.5.1 - Définition des endpoints AJAX internes nécessaires
 *
 * Ce fichier définit les nouveaux endpoints AJAX requis pour le système d'aperçu unifié :
 * - wp_ajax_pdf_generate_preview : Génération d'aperçu (Canvas/Metabox)
 * - wp_ajax_pdf_validate_license : Validation de licence premium
 * - wp_ajax_pdf_get_template_variables : Récupération des variables dynamiques
 * - wp_ajax_pdf_export_canvas : Export PNG/JPG/PDF
 */

namespace PDF_Builder\Controllers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDF_Builder_Preview_API_Controller {

    public function __construct() {
        $this->register_endpoints();
    }

    /**
     * Enregistrer tous les endpoints AJAX
     */
    public function register_endpoints() {
        // Endpoint principal pour générer l'aperçu
        add_action('wp_ajax_pdf_generate_preview', [$this, 'handle_generate_preview']);
        add_action('wp_ajax_nopriv_pdf_generate_preview', [$this, 'handle_generate_preview']); // Pour le frontend si nécessaire

        // Validation de licence premium
        add_action('wp_ajax_pdf_validate_license', [$this, 'handle_validate_license']);

        // Récupération des variables dynamiques
        add_action('wp_ajax_pdf_get_template_variables', [$this, 'handle_get_template_variables']);

        // Export du canvas
        add_action('wp_ajax_pdf_export_canvas', [$this, 'handle_export_canvas']);
    }

    /**
     * Gérer la génération d'aperçu
     * Endpoint: wp_ajax_pdf_generate_preview
     *
     * Paramètres POST:
     * - mode: 'canvas' ou 'metabox'
     * - template_data: données du canvas/template (JSON)
     * - order_id: ID commande (optionnel pour metabox)
     * - format: 'html', 'png', 'jpg' (défaut: html)
     * - nonce: nonce WordPress
     */
    public function handle_generate_preview() {
        try {
            // Vérifier le rate limiting
            if (!$this->check_rate_limit('pdf_generate_preview')) {
                wp_send_json_error(['message' => 'Trop de requêtes. Veuillez réessayer dans une minute.']);
                $this->log_security_event('rate_limit_exceeded', ['endpoint' => 'pdf_generate_preview']);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_preview_nonce')) {
                wp_send_json_error(['message' => 'Nonce invalide ou expiré']);
                $this->log_security_event('invalid_nonce', ['endpoint' => 'pdf_generate_preview']);
                return;
            }

            // Vérifier les permissions
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                $this->log_security_event('permission_denied', ['endpoint' => 'pdf_generate_preview']);
                return;
            }

            // Valider et sanitiser les données d'entrée
            $validated_data = $this->validate_preview_request($_POST);

            // Générer l'aperçu selon le mode
            $preview_result = $this->generate_preview(
                $validated_data['mode'],
                $validated_data['template_data'],
                $validated_data['order_id'],
                $validated_data['format']
            );

            wp_send_json_success([
                'preview_url' => $preview_result['url'],
                'expires' => $preview_result['expires'],
                'format' => $validated_data['format'],
                'mode' => $validated_data['mode']
            ]);

        } catch (\Exception $e) {
            error_log('PDF Preview Error: ' . $e->getMessage());
            $this->log_security_event('preview_error', ['error' => $e->getMessage()]);
            wp_send_json_error(['message' => 'Erreur lors de la génération de l\'aperçu']);
        }
    }

    /**
     * Valider la licence premium
     * Endpoint: wp_ajax_pdf_validate_license
     *
     * Paramètres POST:
     * - license_key: clé de licence (optionnel)
     * - nonce: nonce WordPress
     */
    public function handle_validate_license() {
        try {
            // Vérifier le rate limiting
            if (!$this->check_rate_limit('pdf_validate_license')) {
                wp_send_json_error(['message' => 'Trop de requêtes. Veuillez réessayer dans une minute.']);
                $this->log_security_event('rate_limit_exceeded', ['endpoint' => 'pdf_validate_license']);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_license_nonce')) {
                wp_send_json_error(['message' => 'Nonce invalide ou expiré']);
                $this->log_security_event('invalid_nonce', ['endpoint' => 'pdf_validate_license']);
                return;
            }

            $license_key = sanitize_text_field($_POST['license_key'] ?? '');

            // Validation supplémentaire de la clé de licence
            if (!empty($license_key) && !preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
                wp_send_json_error(['message' => 'Format de clé de licence invalide']);
                $this->log_security_event('invalid_license_format', ['license_key' => substr($license_key, 0, 10) . '...']);
                return;
            }

            // Pour l'instant, retourner freemium (à implémenter avec vraie validation)
            $is_valid = $this->validate_license($license_key);

            wp_send_json_success([
                'valid' => $is_valid,
                'license_type' => $is_valid ? 'premium' : 'freemium',
                'expires' => $is_valid ? strtotime('+1 year') : null,
                'features' => $is_valid ? ['unlimited_templates', 'export_png_jpg', 'advanced_variables'] : ['basic_features']
            ]);

        } catch (\Exception $e) {
            error_log('PDF License Validation Error: ' . $e->getMessage());
            $this->log_security_event('license_validation_error', ['error' => $e->getMessage()]);
            wp_send_json_error(['message' => 'Erreur de validation de licence']);
        }
    }

    /**
     * Récupérer les variables dynamiques du template
     * Endpoint: wp_ajax_pdf_get_template_variables
     *
     * Paramètres POST:
     * - template_id: ID du template (optionnel)
     * - mode: 'canvas' ou 'metabox'
     * - nonce: nonce WordPress
     */
    public function handle_get_template_variables() {
        try {
            // Vérifier le rate limiting
            if (!$this->check_rate_limit('pdf_get_template_variables')) {
                wp_send_json_error(['message' => 'Trop de requêtes. Veuillez réessayer dans une minute.']);
                $this->log_security_event('rate_limit_exceeded', ['endpoint' => 'pdf_get_template_variables']);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_variables_nonce')) {
                wp_send_json_error(['message' => 'Nonce invalide ou expiré']);
                $this->log_security_event('invalid_nonce', ['endpoint' => 'pdf_get_template_variables']);
                return;
            }

            $template_id = intval($_POST['template_id'] ?? 0);
            $mode = sanitize_text_field($_POST['mode'] ?? 'canvas');

            if (!in_array($mode, ['canvas', 'metabox'])) {
                wp_send_json_error(['message' => 'Mode invalide']);
                $this->log_security_event('invalid_mode', ['mode' => $mode, 'endpoint' => 'pdf_get_template_variables']);
                return;
            }

            // Validation supplémentaire pour template_id
            if ($template_id < 0) {
                wp_send_json_error(['message' => 'ID de template invalide']);
                $this->log_security_event('invalid_template_id', ['template_id' => $template_id]);
                return;
            }

            $variables = $this->get_template_variables($template_id, $mode);
            $categories = $this->get_variable_categories();

            wp_send_json_success([
                'variables' => $variables,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            error_log('PDF Variables Error: ' . $e->getMessage());
            $this->log_security_event('variables_error', ['error' => $e->getMessage()]);
            wp_send_json_error(['message' => 'Erreur de récupération des variables']);
        }
    }

    /**
     * Exporter le canvas
     * Endpoint: wp_ajax_pdf_export_canvas
     *
     * Paramètres POST:
     * - template_data: données du canvas (JSON)
     * - format: 'pdf', 'png', 'jpg'
     * - quality: qualité (1-100, défaut 90)
     * - filename: nom du fichier personnalisé (optionnel)
     * - nonce: nonce WordPress
     */
    public function handle_export_canvas() {
        try {
            // Vérifier le rate limiting
            if (!$this->check_rate_limit('pdf_export_canvas')) {
                wp_send_json_error(['message' => 'Trop de requêtes. Veuillez réessayer dans une minute.']);
                $this->log_security_event('rate_limit_exceeded', ['endpoint' => 'pdf_export_canvas']);
                return;
            }

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_export_nonce')) {
                wp_send_json_error(['message' => 'Nonce invalide ou expiré']);
                $this->log_security_event('invalid_nonce', ['endpoint' => 'pdf_export_canvas']);
                return;
            }

            // Vérifier les permissions
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                $this->log_security_event('permission_denied', ['endpoint' => 'pdf_export_canvas']);
                return;
            }

            $template_data_raw = $_POST['template_data'] ?? '{}';
            $template_data = json_decode(stripslashes($template_data_raw), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error(['message' => 'Données template JSON invalides']);
                $this->log_security_event('invalid_json', ['endpoint' => 'pdf_export_canvas']);
                return;
            }

            $format = sanitize_text_field($_POST['format'] ?? 'pdf');
            $quality = intval($_POST['quality'] ?? 90);
            $filename = sanitize_file_name($_POST['filename'] ?? '');

            // Validation
            if (!in_array($format, ['pdf', 'png', 'jpg'])) {
                wp_send_json_error(['message' => 'Format invalide']);
                $this->log_security_event('invalid_format', ['format' => $format, 'endpoint' => 'pdf_export_canvas']);
                return;
            }

            if ($quality < 1 || $quality > 100) {
                $quality = 90;
            }

            // Validation de sécurité pour le filename
            if (!empty($filename) && !preg_match('/^[a-zA-Z0-9\-_\.\s]+$/', $filename)) {
                wp_send_json_error(['message' => 'Nom de fichier invalide']);
                $this->log_security_event('invalid_filename', ['filename' => $filename]);
                return;
            }

            // Sanitisation des données template
            $template_data = $this->sanitize_template_data($template_data);

            // Générer l'export
            $export_result = $this->export_canvas($template_data, $format, $quality, $filename);

            wp_send_json_success([
                'download_url' => $export_result['url'],
                'filename' => $export_result['filename'],
                'expires' => $export_result['expires']
            ]);

        } catch (\Exception $e) {
            error_log('PDF Export Error: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur lors de l\'export']);
        }
    }

    /**
     * Générer un aperçu (méthode privée)
     */
    private function generate_preview($mode, $template_data, $order_id, $format) {
        // TODO: Implémenter la logique de génération d'aperçu
        // Pour l'instant, retourner une URL temporaire simulée

        $preview_id = uniqid('preview_');
        $preview_url = add_query_arg([
            'pdf_preview' => $preview_id,
            'mode' => $mode,
            'format' => $format
        ], site_url());

        return [
            'url' => $preview_url,
            'expires' => time() + 3600 // 1 heure
        ];
    }

    /**
     * Valider une licence (méthode privée)
     */
    private function validate_license($license_key) {
        // TODO: Implémenter la vraie validation de licence
        // Pour l'instant, considérer comme freemium
        return false;
    }

    /**
     * Récupérer les variables du template (méthode privée)
     */
    private function get_template_variables($template_id, $mode) {
        // TODO: Implémenter la récupération des variables selon le mode
        // Retourner un exemple de structure
        return [
            'customer_name' => [
                'type' => 'string',
                'description' => 'Nom du client',
                'example' => 'Jean Dupont',
                'required' => true
            ],
            'order_total' => [
                'type' => 'number',
                'description' => 'Total commande',
                'format' => 'currency',
                'example' => '299.99'
            ]
        ];
    }

    /**
     * Récupérer les catégories de variables (méthode privée)
     */
    private function get_variable_categories() {
        return ['customer', 'order', 'company', 'dynamic'];
    }

    /**
     * Exporter le canvas (méthode privée)
     */
    private function export_canvas($template_data, $format, $quality, $filename) {
        // TODO: Implémenter la logique d'export
        // Pour l'instant, simuler un export

        $export_id = uniqid('export_');
        $download_url = add_query_arg([
            'pdf_download' => $export_id,
            'format' => $format
        ], site_url());

        return [
            'url' => $download_url,
            'filename' => $filename ?: 'export.' . $format,
            'expires' => time() + 3600
        ];
    }

    /**
     * Vérifier le rate limiting pour les requêtes
     * Utilise les transients WordPress pour limiter les requêtes par utilisateur/IP
     *
     * @param string $endpoint Nom de l'endpoint
     * @return bool True si la requête est autorisée
     */
    private function check_rate_limit($endpoint) {
        $user_id = get_current_user_id();
        $ip = $this->get_client_ip();

        // Clé unique pour cet utilisateur/endpoint
        $rate_key = 'pdf_rate_' . $endpoint . '_' . ($user_id ?: md5($ip));

        // Récupérer le compteur actuel
        $current_count = get_transient($rate_key) ?: 0;

        // Limites par endpoint (requêtes par minute)
        $limits = [
            'pdf_generate_preview' => 10,  // 10 aperçus par minute
            'pdf_validate_license' => 5,   // 5 validations par minute
            'pdf_get_template_variables' => 20, // 20 récupérations par minute
            'pdf_export_canvas' => 5       // 5 exports par minute
        ];

        $limit = $limits[$endpoint] ?? 10;

        if ($current_count >= $limit) {
            return false; // Limite dépassée
        }

        // Incrémenter le compteur
        set_transient($rate_key, $current_count + 1, 60); // Expire dans 60 secondes

        return true;
    }

    /**
     * Récupérer l'adresse IP du client de manière sécurisée
     *
     * @return string Adresse IP du client
     */
    private function get_client_ip() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
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

                // Gérer les listes d'IP (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Valider et sanitiser les données de requête d'aperçu
     *
     * @param array $post_data Données POST brutes
     * @return array Données validées et sanitizées
     * @throws \Exception Si validation échoue
     */
    private function validate_preview_request($post_data) {
        $validated = [];

        // Mode
        $validated['mode'] = sanitize_text_field($post_data['mode'] ?? 'canvas');
        if (!in_array($validated['mode'], ['canvas', 'metabox'])) {
            throw new \Exception('Mode invalide');
        }

        // Template data - validation JSON stricte
        $template_data_raw = $post_data['template_data'] ?? '{}';
        $template_data = json_decode(stripslashes($template_data_raw), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Données template JSON invalides');
        }

        // Validation structure de base
        if (!is_array($template_data)) {
            throw new \Exception('Données template doivent être un objet JSON');
        }

        // Sanitisation des données template (éviter XSS)
        $validated['template_data'] = $this->sanitize_template_data($template_data);

        // Order ID
        $validated['order_id'] = intval($post_data['order_id'] ?? 0);
        if ($validated['order_id'] < 0) {
            $validated['order_id'] = 0;
        }

        // Format
        $validated['format'] = sanitize_text_field($post_data['format'] ?? 'html');
        if (!in_array($validated['format'], ['html', 'png', 'jpg'])) {
            throw new \Exception('Format invalide');
        }

        return $validated;
    }

    /**
     * Sanitiser les données du template pour éviter les attaques XSS
     *
     * @param array $data Données du template
     * @return array Données sanitizées
     */
    private function sanitize_template_data($data) {
        $sanitized = [];

        foreach ($data as $key => $value) {
            // Sanitiser la clé
            $clean_key = sanitize_key($key);

            if (is_array($value)) {
                // Récursif pour les tableaux imbriqués
                $sanitized[$clean_key] = $this->sanitize_template_data($value);
            } elseif (is_string($value)) {
                // Sanitiser les chaînes (autoriser HTML limité si nécessaire)
                $sanitized[$clean_key] = wp_kses_post($value);
            } elseif (is_numeric($value)) {
                // Conserver les nombres
                $sanitized[$clean_key] = $value;
            } elseif (is_bool($value)) {
                // Conserver les booléens
                $sanitized[$clean_key] = $value;
            }
            // Ignorer les autres types (null, objets, etc.)
        }

        return $sanitized;
    }

    /**
     * Journaliser les événements de sécurité
     *
     * @param string $event Type d'événement
     * @param array $data Données contextuelles
     */
    private function log_security_event($event, $data = []) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'user_id' => get_current_user_id(),
            'ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data
        ];

        // Journaliser dans le fichier de logs du plugin
        $log_file = WP_CONTENT_DIR . '/pdf-builder-logs/security.log';
        $log_dir = dirname($log_file);

        if (!is_dir($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $log_message = sprintf(
            "[%s] SECURITY: %s - User: %d - IP: %s - Data: %s\n",
            $log_entry['timestamp'],
            $event,
            $log_entry['user_id'],
            $log_entry['ip'],
            json_encode($data)
        );

        error_log($log_message, 3, $log_file);

        // Aussi dans les logs WordPress pour les événements critiques
        if (in_array($event, ['rate_limit_exceeded', 'permission_denied', 'invalid_nonce'])) {
            error_log('PDF Builder Security: ' . $event . ' - IP: ' . $log_entry['ip']);
        }
    }
}

// Initialiser le contrôleur si on est dans WordPress
if (function_exists('add_action')) {
    new PDF_Builder_Preview_API_Controller();
}