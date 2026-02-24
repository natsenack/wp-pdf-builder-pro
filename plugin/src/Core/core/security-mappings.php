<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder Security Mappings
 *
 * Centralise toutes les règles de sécurité et les configurations de sécurité
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Security_Mappings {

    // ==========================================
    // RÈGLES DE SÉCURITÉ GÉNÉRALES
    // ==========================================

    private static $security_rules = [
        // Permissions WordPress
        'required_capabilities' => [
            'manage_options' => 'manage_options',
            'edit_posts' => 'edit_posts',
            'upload_files' => 'upload_files',
            'pdf_builder_admin' => 'pdf_builder_admin',
            'pdf_builder_edit' => 'pdf_builder_edit',
            'pdf_builder_create' => 'pdf_builder_create',
            'pdf_builder_delete' => 'pdf_builder_delete'
        ],

        // Nonces
        'nonces' => [
            'save_template' => 'pdf_builder_save_template_nonce',
            'load_template' => 'pdf_builder_load_template_nonce',
            'delete_template' => 'pdf_builder_delete_template_nonce',
            'export_pdf' => 'pdf_builder_export_pdf_nonce',
            'save_settings' => 'pdf_builder_save_settings_nonce',
            'upload_file' => 'pdf_builder_upload_file_nonce',
            'ajax_request' => 'pdf_builder_ajax_nonce'
        ],

        // Actions AJAX autorisées
        'allowed_ajax_actions' => [
            'pdf_builder_save_template',
            'pdf_builder_load_template',
            'pdf_builder_delete_template',
            'pdf_builder_export_pdf',
            'pdf_builder_save_settings',
            'pdf_builder_load_settings',
            'pdf_builder_upload_image',
            'pdf_builder_get_fonts',
            'pdf_builder_validate_data',
            'pdf_builder_get_preview'
        ],

        // Types de fichiers autorisés
        'allowed_file_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
            'fonts' => ['ttf', 'otf', 'woff', 'woff2'],
            'documents' => ['pdf'],
            'data' => ['json', 'xml']
        ],

        // Tailles de fichiers maximales (en octets)
        'max_file_sizes' => [
            'image' => 5242880, // 5MB
            'font' => 2097152,  // 2MB
            'document' => 10485760, // 10MB
            'data' => 1048576   // 1MB
        ],

        // Limites de taux (requêtes par minute)
        'rate_limits' => [
            'ajax_requests' => 60,
            'file_uploads' => 10,
            'template_operations' => 30,
            'export_operations' => 5
        ]
    ];

    // ==========================================
    // RÈGLES DE SÉCURITÉ PAR CONTEXTE
    // ==========================================

    private static $context_security_rules = [
        'admin_page' => [
            'required_capability' => 'manage_options',
            'allowed_users' => ['administrator', 'editor'],
            'nonce_required' => true,
            'ssl_required' => true
        ],

        'ajax_request' => [
            'required_capability' => 'edit_posts',
            'allowed_users' => ['administrator', 'editor', 'author'],
            'nonce_required' => true,
            'ssl_required' => false,
            'rate_limiting' => true
        ],

        'file_upload' => [
            'required_capability' => 'upload_files',
            'allowed_users' => ['administrator', 'editor', 'author'],
            'nonce_required' => true,
            'ssl_required' => true,
            'file_validation' => true,
            'rate_limiting' => true
        ],

        'template_operation' => [
            'required_capability' => 'edit_posts',
            'allowed_users' => ['administrator', 'editor', 'author'],
            'nonce_required' => true,
            'ssl_required' => false,
            'ownership_check' => true
        ],

        'settings_save' => [
            'required_capability' => 'manage_options',
            'allowed_users' => ['administrator'],
            'nonce_required' => true,
            'ssl_required' => true,
            'input_sanitization' => true
        ]
    ];

    // ==========================================
    // RÈGLES DE SANITISATION
    // ==========================================

    private static $sanitization_rules = [
        'text' => [
            'method' => 'sanitize_text_field',
            'allow_html' => false,
            'max_length' => 1000
        ],

        'textarea' => [
            'method' => 'wp_kses_post',
            'allow_html' => true,
            'allowed_tags' => ['p', 'br', 'strong', 'em', 'u', 'a'],
            'max_length' => 10000
        ],

        'email' => [
            'method' => 'sanitize_email',
            'allow_html' => false,
            'max_length' => 100
        ],

        'url' => [
            'method' => 'esc_url_raw',
            'allow_html' => false,
            'max_length' => 2000
        ],

        'number' => [
            'method' => 'intval',
            'allow_html' => false,
            'min' => null,
            'max' => null
        ],

        'float' => [
            'method' => 'floatval',
            'allow_html' => false,
            'min' => null,
            'max' => null
        ],

        'boolean' => [
            'method' => 'wp_validate_boolean',
            'allow_html' => false
        ],

        'color' => [
            'method' => 'sanitize_hex_color',
            'allow_html' => false,
            'pattern' => '/^#[0-9A-Fa-f]{6}$/'
        ],

        'filename' => [
            'method' => 'sanitize_file_name',
            'allow_html' => false,
            'max_length' => 255
        ],

        'json' => [
            'method' => 'wp_json_encode',
            'allow_html' => false,
            'validate_json' => true
        ]
    ];

    // ==========================================
    // PATTERNS DE SÉCURITÉ
    // ==========================================

    private static $security_patterns = [
        'sql_injection' => [
            'patterns' => ['/\bUNION\b/i', '/\bSELECT\b.*\bFROM\b/i', '/\bDROP\b/i', '/\bDELETE\b/i', '/\bUPDATE\b/i', '/\bINSERT\b/i'],
            'severity' => 'high'
        ],

        'xss' => [
            'patterns' => ['/<script/i', '/javascript:/i', '/on\w+\s*=/i', '/<iframe/i', '/<object/i'],
            'severity' => 'high'
        ],

        'path_traversal' => [
            'patterns' => ['\.\./', '\.\.\\', '/etc/passwd', '/etc/shadow', 'C:\\\\windows\\\\system32'],
            'severity' => 'critical'
        ],

        'command_injection' => [
            'patterns' => [';', '\|', '\&\&', '\|\|', '`', '\$\(', '\$\{'],
            'severity' => 'critical'
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir toutes les règles de sécurité
     */
    public static function get_security_rules() {
        return self::$security_rules;
    }

    /**
     * Obtenir une règle de sécurité spécifique
     */
    public static function get_security_rule($key) {
        return self::$security_rules[$key] ?? null;
    }

    /**
     * Obtenir les règles de sécurité pour un contexte
     */
    public static function get_context_security_rules($context) {
        return self::$context_security_rules[$context] ?? [];
    }

    /**
     * Obtenir les règles de sanitisation
     */
    public static function get_sanitization_rules() {
        return self::$sanitization_rules;
    }

    /**
     * Obtenir une règle de sanitisation spécifique
     */
    public static function get_sanitization_rule($type) {
        return self::$sanitization_rules[$type] ?? null;
    }

    /**
     * Obtenir les patterns de sécurité
     */
    public static function get_security_patterns() {
        return self::$security_patterns;
    }

    /**
     * Vérifier si une action AJAX est autorisée
     */
    public static function is_ajax_action_allowed($action) {
        return in_array($action, self::$security_rules['allowed_ajax_actions']);
    }

    /**
     * Vérifier si un type de fichier est autorisé
     */
    public static function is_file_type_allowed($filename, $category = 'images') {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_types = self::$security_rules['allowed_file_types'][$category] ?? [];

        return in_array($extension, $allowed_types);
    }

    /**
     * Vérifier la taille d'un fichier
     */
    public static function is_file_size_allowed($size, $category = 'image') {
        $max_size = self::$security_rules['max_file_sizes'][$category] ?? 0;

        return $size <= $max_size;
    }

    /**
     * Obtenir la taille maximale pour un type de fichier
     */
    public static function get_max_file_size($category = 'image') {
        return self::$security_rules['max_file_sizes'][$category] ?? 0;
    }

    /**
     * Sanitiser une valeur selon son type
     */
    public static function sanitize_value($value, $type) {
        $rule = self::get_sanitization_rule($type);

        if (!$rule) {
            return sanitize_text_field($value);
        }

        $method = $rule['method'];

        if (!function_exists($method)) {
            return sanitize_text_field($value);
        }

        $sanitized = call_user_func($method, $value);

        // Validation supplémentaire pour les couleurs
        if ($type === 'color' && isset($rule['pattern'])) {
            if (!preg_match($rule['pattern'], $sanitized)) {
                return '#000000'; // Couleur par défaut
            }
        }

        // Validation JSON
        if ($type === 'json' && isset($rule['validate_json'])) {
            $decoded = json_decode($sanitized, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return '{}'; // JSON vide par défaut
            }
        }

        // Limite de longueur
        if (isset($rule['max_length']) && is_string($sanitized)) {
            $sanitized = substr($sanitized, 0, $rule['max_length']);
        }

        return $sanitized;
    }

    /**
     * Vérifier la sécurité d'une chaîne
     */
    public static function check_security_patterns($string) {
        $threats = [];

        foreach (self::$security_patterns as $type => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $string)) {
                    $threats[] = [
                        'type' => $type,
                        'severity' => $config['severity'],
                        'pattern' => $pattern
                    ];
                }
            }
        }

        return $threats;
    }

    /**
     * Générer un nonce pour une action
     */
    public static function generate_nonce($action) {
        $nonce_key = self::$security_rules['nonces'][$action] ?? 'pdf_builder_general_nonce';
        return wp_create_nonce($nonce_key);
    }

    /**
     * Vérifier un nonce
     */
    public static function verify_nonce($nonce, $action) {
        $nonce_key = self::$security_rules['nonces'][$action] ?? 'pdf_builder_general_nonce';
        return \pdf_builder_verify_nonce($nonce, $nonce_key);
    }

    /**
     * Vérifier les permissions pour un contexte
     */
    public static function check_context_permissions($context) {
        $rules = self::get_context_security_rules($context);

        if (empty($rules)) {
            return false;
        }

        // Vérifier la capacité requise
        if (isset($rules['required_capability'])) {
            if (!current_user_can($rules['required_capability'])) {
                return false;
            }
        }

        // Vérifier le rôle utilisateur
        if (isset($rules['allowed_users'])) {
            $user = wp_get_current_user();
            if (!in_array($user->roles[0] ?? '', $rules['allowed_users'])) {
                return false;
            }
        }

        // Vérifier SSL si requis
        if (isset($rules['ssl_required']) && $rules['ssl_required']) {
            if (!is_ssl()) {
                return false;
            }
        }

        return true;
    }
}



