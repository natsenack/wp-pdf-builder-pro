<?php
/**
 * PDF Builder Pro - Input Validation & Sanitization Module
 * Phase 4: Validation d'entrée renforcée selon OWASP
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// VALIDATEUR D'ENTRÉE RENFORCÉ
// ============================================================================

class PDF_Builder_Input_Validator {

    private static $instance = null;
    private $validation_rules = [];

    private function __construct() {
        $this->init_validation_rules();
        $this->init_hooks();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialise les règles de validation
     */
    private function init_validation_rules() {
        $this->validation_rules = [
            // Règles pour les paramètres généraux
            'setting_key' => [
                'type' => 'string',
                'max_length' => 100,
                'pattern' => '/^[a-zA-Z0-9_-]+$/',
                'required' => true
            ],
            'setting_value' => [
                'type' => 'string',
                'max_length' => 10000,
                'sanitize' => 'sanitize_text_field'
            ],

            // Règles pour les templates
            'template_name' => [
                'type' => 'string',
                'max_length' => 255,
                'pattern' => '/^[a-zA-Z0-9\s\-_]+$/',
                'required' => true
            ],
            'template_data' => [
                'type' => 'json',
                'max_size' => 5242880, // 5MB
                'required' => true
            ],

            // Règles pour les paramètres canvas
            'canvas_width' => [
                'type' => 'integer',
                'min' => 100,
                'max' => 5000,
                'required' => true
            ],
            'canvas_height' => [
                'type' => 'integer',
                'min' => 100,
                'max' => 5000,
                'required' => true
            ],

            // Règles pour les couleurs
            'color' => [
                'type' => 'string',
                'pattern' => '/^#[a-fA-F0-9]{6}$/',
                'required' => false
            ],

            // Règles pour les URLs
            'url' => [
                'type' => 'url',
                'max_length' => 2000,
                'required' => false
            ],

            // Règles pour les emails
            'email' => [
                'type' => 'email',
                'max_length' => 254,
                'required' => false
            ]
        ];
    }

    /**
     * Initialise les hooks
     */
    private function init_hooks() {
        // Validation avant traitement AJAX
        add_action('wp_ajax_pdf_builder_unified_dispatch', [$this, 'validate_ajax_request'], 1);

        // Nettoyage automatique des entrées
        add_action('wp_ajax_pdf_builder_unified_dispatch', [$this, 'sanitize_request_data'], 2);

        // Validation des uploads
        add_filter('wp_handle_upload_prefilter', [$this, 'validate_upload']);
    }

    /**
     * Valide une requête AJAX
     */
    public function validate_ajax_request() {
        // Vérifier le nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_ajax')) {
            $this->log_validation_error('invalid_nonce', [
                'action' => $_REQUEST['action'] ?? 'unknown',
                'ip' => $this->get_client_ip()
            ]);
            wp_send_json_error(['message' => 'Requête non autorisée'], 403);
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            $this->log_validation_error('insufficient_permissions', [
                'action' => $_REQUEST['action'] ?? 'unknown',
                'user_id' => get_current_user_id(),
                'ip' => $this->get_client_ip()
            ]);
            wp_send_json_error(['message' => 'Permissions insuffisantes'], 403);
        }

        // Validation spécifique selon l'action
        $action = $_REQUEST['action'] ?? '';
        $this->validate_action_specific_data($action);
    }

    /**
     * Validation spécifique selon l'action
     */
    private function validate_action_specific_data($action) {
        switch ($action) {
            case 'pdf_builder_save_all_settings':
                $this->validate_settings_data();
                break;

            case 'pdf_builder_save_template':
            case 'pdf_builder_load_template':
                $this->validate_template_data();
                break;

            case 'pdf_builder_generate_preview':
                $this->validate_preview_data();
                break;

            default:
                // Validation générique pour les autres actions
                $this->validate_generic_data();
                break;
        }
    }

    /**
     * Valide les données de paramètres
     */
    private function validate_settings_data() {
        if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
            wp_send_json_error(['message' => 'Données de paramètres invalides']);
        }

        foreach ($_POST['settings'] as $key => $value) {
            if (!$this->validate_field($key, $value, 'setting_key')) {
                wp_send_json_error(['message' => "Paramètre invalide: $key"]);
            }
        }
    }

    /**
     * Valide les données de template
     */
    private function validate_template_data() {
        if (isset($_POST['template_name'])) {
            if (!$this->validate_field('template_name', $_POST['template_name'])) {
                wp_send_json_error(['message' => 'Nom de template invalide']);
            }
        }

        if (isset($_POST['template_data'])) {
            if (!$this->validate_field('template_data', $_POST['template_data'])) {
                wp_send_json_error(['message' => 'Données de template invalides']);
            }
        }
    }

    /**
     * Valide les données d'aperçu
     */
    private function validate_preview_data() {
        // Validation spécifique pour la génération d'aperçu
        if (isset($_POST['canvas_data'])) {
            $canvas_data = json_decode($_POST['canvas_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error(['message' => 'Données canvas invalides']);
            }

            // Valider les dimensions du canvas
            if (isset($canvas_data['width'])) {
                if (!$this->validate_field('canvas_width', $canvas_data['width'])) {
                    wp_send_json_error(['message' => 'Largeur canvas invalide']);
                }
            }

            if (isset($canvas_data['height'])) {
                if (!$this->validate_field('canvas_height', $canvas_data['height'])) {
                    wp_send_json_error(['message' => 'Hauteur canvas invalide']);
                }
            }
        }
    }

    /**
     * Validation générique
     */
    private function validate_generic_data() {
        // Validation de base pour tous les champs POST
        foreach ($_POST as $key => $value) {
            if (is_string($value) && strlen($value) > 10000) {
                wp_send_json_error(['message' => "Champ $key trop long"]);
            }
        }
    }

    /**
     * Valide un champ selon les règles
     */
    public function validate_field($field_name, $value, $rule_name = null) {
        $rule_name = $rule_name ?: $field_name;
        $rule = $this->validation_rules[$rule_name] ?? null;

        if (!$rule) {
            // Pas de règle spécifique, validation basique
            return $this->basic_validation($value);
        }

        // Vérifier si requis
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            return false;
        }

        // Validation selon le type
        switch ($rule['type']) {
            case 'string':
                return $this->validate_string($value, $rule);
            case 'integer':
                return $this->validate_integer($value, $rule);
            case 'email':
                return $this->validate_email($value, $rule);
            case 'url':
                return $this->validate_url($value, $rule);
            case 'json':
                return $this->validate_json($value, $rule);
            default:
                return $this->basic_validation($value);
        }
    }

    /**
     * Validation de chaîne
     */
    private function validate_string($value, $rule) {
        if (!is_string($value)) {
            return false;
        }

        // Longueur maximale
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            return false;
        }

        // Pattern regex
        if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
            return false;
        }

        return true;
    }

    /**
     * Validation d'entier
     */
    private function validate_integer($value, $rule) {
        if (!is_numeric($value)) {
            return false;
        }

        $int_value = intval($value);

        // Minimum
        if (isset($rule['min']) && $int_value < $rule['min']) {
            return false;
        }

        // Maximum
        if (isset($rule['max']) && $int_value > $rule['max']) {
            return false;
        }

        return true;
    }

    /**
     * Validation d'email
     */
    private function validate_email($value, $rule) {
        if (!is_string($value)) {
            return false;
        }

        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validation d'URL
     */
    private function validate_url($value, $rule) {
        if (!is_string($value)) {
            return false;
        }

        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validation JSON
     */
    private function validate_json($value, $rule) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        } elseif (is_array($value)) {
            $decoded = $value;
        } else {
            return false;
        }

        // Taille maximale (en octets)
        if (isset($rule['max_size'])) {
            $size = strlen(json_encode($decoded));
            if ($size > $rule['max_size']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validation basique
     */
    private function basic_validation($value) {
        // Protection contre les attaques XSS basiques
        if (is_string($value)) {
            // Vérifier les balises script dangereuses
            if (preg_match('/<script[^>]*>.*?<\/script>/is', $value)) {
                return false;
            }

            // Vérifier les événements JavaScript
            if (preg_match('/on\w+\s*=/i', $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Nettoie les données de requête
     */
    public function sanitize_request_data() {
        foreach ($_POST as $key => $value) {
            $_POST[$key] = $this->sanitize_value($value);
        }

        foreach ($_GET as $key => $value) {
            $_GET[$key] = $this->sanitize_value($value);
        }
    }

    /**
     * Nettoie une valeur
     */
    private function sanitize_value($value) {
        if (is_string($value)) {
            // Nettoyage XSS
            $value = wp_kses($value, $this->get_allowed_html());
            $value = sanitize_text_field($value);
        } elseif (is_array($value)) {
            $value = array_map([$this, 'sanitize_value'], $value);
        }

        return $value;
    }

    /**
     * HTML autorisé pour wp_kses
     */
    private function get_allowed_html() {
        return [
            'a' => ['href' => [], 'title' => []],
            'br' => [],
            'em' => [],
            'strong' => [],
            'p' => [],
            'span' => ['class' => []],
            'div' => ['class' => []]
        ];
    }

    /**
     * Valide un upload de fichier
     */
    public function validate_upload($file) {
        // Types MIME autorisés
        $allowed_mimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];

        $file_type = wp_check_filetype($file['name'], $allowed_mimes);

        if (!$file_type['type']) {
            $file['error'] = 'Type de fichier non autorisé';
            return $file;
        }

        // Vérifier la taille (max 10MB)
        $max_size = 10 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $file['error'] = 'Fichier trop volumineux (max 10MB)';
            return $file;
        }

        // Vérifier le contenu du fichier (basique)
        if ($this->is_suspicious_file($file['tmp_name'])) {
            $file['error'] = 'Contenu du fichier suspect';
            return $file;
        }

        return $file;
    }

    /**
     * Vérifie si un fichier est suspect
     */
    private function is_suspicious_file($file_path) {
        $content = file_get_contents($file_path, false, null, 0, 1024); // Premier KB

        $suspicious_patterns = [
            '<?php',
            '<script',
            'eval(',
            'base64_decode',
            'system(',
            'exec(',
            'shell_exec(',
            'javascript:',
            'vbscript:',
            'onload=',
            'onerror='
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log une erreur de validation
     */
    private function log_validation_error($type, $data) {
        error_log(sprintf(
            'PDF_BUILDER_VALIDATION_ERROR: %s - %s',
            $type,
            json_encode($data)
        ));
    }

    /**
     * Obtient l'IP du client
     */
    private function get_client_ip() {
        $headers = [
            'CF-CONNECTING-IP',
            'X-FORWARDED-FOR',
            'X-FORWARDED',
            'FORWARDED-FOR',
            'CLIENT_IP',
            'HTTP_CLIENT_IP'
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

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Ajoute une règle de validation personnalisée
     */
    public function add_validation_rule($field_name, $rule) {
        $this->validation_rules[$field_name] = $rule;
    }

    /**
     * Obtient les règles de validation
     */
    public function get_validation_rules() {
        return $this->validation_rules;
    }
}

// ============================================================================
// INITIALISATION
// ============================================================================

add_action('plugins_loaded', function() {
    PDF_Builder_Input_Validator::get_instance();
});

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/**
 * Valide un champ selon les règles définies
 */
function pdf_builder_validate_field($field_name, $value) {
    $validator = PDF_Builder_Input_Validator::get_instance();
    return $validator->validate_field($field_name, $value);
}

/**
 * Nettoie une valeur
 */
function pdf_builder_sanitize_value($value) {
    $validator = PDF_Builder_Input_Validator::get_instance();
    return $validator->sanitize_value($value);
}

/**
 * Ajoute une règle de validation personnalisée
 */
function pdf_builder_add_validation_rule($field_name, $rule) {
    $validator = PDF_Builder_Input_Validator::get_instance();
    $validator->add_validation_rule($field_name, $rule);
}