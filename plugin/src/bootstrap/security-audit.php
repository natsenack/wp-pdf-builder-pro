<?php
/**
 * PDF Builder Pro - Security Audit & Hardening Module
 * Phase 4: Audit de sécurité complet selon OWASP et hardening
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// AUDIT DE SÉCURITÉ OWASP
// ============================================================================

class PDF_Builder_Security_Audit {

    private static $instance = null;
    private $audit_results = [];
    private $vulnerabilities = [];

    private function __construct() {
        $this->run_security_audit();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Exécute l'audit de sécurité complet
     */
    private function run_security_audit() {
        $this->audit_results = [
            'timestamp' => current_time('timestamp'),
            'checks' => []
        ];

        // OWASP Top 10 Checks
        $this->check_injection_vulnerabilities();
        $this->check_broken_authentication();
        $this->check_sensitive_data_exposure();
        $this->check_xml_external_entities();
        $this->check_broken_access_control();
        $this->check_security_misconfiguration();
        $this->check_cross_site_scripting();
        $this->check_insecure_deserialization();
        $this->check_vulnerable_components();
        $this->check_insufficient_logging();

        // Additional security checks
        $this->check_file_upload_security();
        $this->check_rate_limiting();
        $this->check_input_validation();
        $this->check_output_encoding();
    }

    /**
     * A01:2021 - Broken Access Control
     */
    private function check_broken_access_control() {
        $issues = [];

        // Check for direct object references
        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\$_GET\[.*id.*\]/i', $content) && !preg_match('/current_user_can|wp_verify_nonce/i', $content)) {
                $issues[] = "Possible direct object reference in $file without access control";
            }
        }

        $this->audit_results['checks']['broken_access_control'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Implement proper access control checks for all user inputs',
                'Use current_user_can() for capability checks',
                'Validate object ownership before allowing access'
            ]
        ];
    }

    /**
     * A02:2021 - Cryptographic Failures
     */
    private function check_sensitive_data_exposure() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for hardcoded secrets
            if (preg_match('/password|secret|key.*=.*["\'][^"\']*["\']/i', $content)) {
                $issues[] = "Possible hardcoded credentials in $file";
            }

            // Check for insecure data transmission
            if (preg_match('/http:\/\/.*password|http:\/\/.*secret/i', $content)) {
                $issues[] = "Insecure data transmission (HTTP instead of HTTPS) in $file";
            }
        }

        $this->audit_results['checks']['sensitive_data_exposure'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Never hardcode credentials in source code',
                'Use HTTPS for all sensitive data transmission',
                'Implement proper encryption for sensitive data at rest'
            ]
        ];
    }

    /**
     * A03:2021 - Injection
     */
    private function check_injection_vulnerabilities() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for SQL injection vulnerabilities
            if (preg_match('/\$wpdb->query.*\$[^\$]/', $content) && !preg_match('/prepare|esc_sql/i', $content)) {
                $issues[] = "Possible SQL injection in $file - use prepared statements";
            }

            // Check for command injection
            if (preg_match('/exec\(|shell_exec\(|system\(/', $content)) {
                $issues[] = "Possible command injection in $file";
            }
        }

        $this->audit_results['checks']['injection'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Use $wpdb->prepare() for all SQL queries',
                'Validate and sanitize all user inputs',
                'Use esc_sql() for dynamic SQL parts'
            ]
        ];
    }

    /**
     * A04:2021 - Insecure Design
     */
    private function check_insecure_deserialization() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for unsafe deserialization
            if (preg_match('/unserialize\(/', $content) && !preg_match('/wp_unslash|stripslashes/i', $content)) {
                $issues[] = "Unsafe deserialization in $file";
            }
        }

        $this->audit_results['checks']['insecure_deserialization'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Avoid unserialize() with user data',
                'Use JSON for data serialization when possible',
                'Validate serialized data before unserializing'
            ]
        ];
    }

    /**
     * A05:2021 - Security Misconfiguration
     */
    private function check_security_misconfiguration() {
        $issues = [];

        // Check debug settings
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $issues[] = "WP_DEBUG is enabled in production";
        }

        // Check for exposed sensitive files
        $sensitive_files = ['wp-config.php', '.env', '.git'];
        foreach ($sensitive_files as $file) {
            if (file_exists(ABSPATH . $file)) {
                $issues[] = "Sensitive file $file is accessible";
            }
        }

        // Check file permissions (basic check)
        $plugin_dir = plugin_dir_path(__FILE__);
        if (is_writable($plugin_dir)) {
            $issues[] = "Plugin directory is writable - potential security risk";
        }

        $this->audit_results['checks']['security_misconfiguration'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Disable WP_DEBUG in production',
                'Protect sensitive files with .htaccess',
                'Set proper file permissions (755 for dirs, 644 for files)',
                'Remove unnecessary plugins and themes'
            ]
        ];
    }

    /**
     * A06:2021 - Vulnerable Components
     */
    private function check_vulnerable_components() {
        $issues = [];

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            $issues[] = "WordPress version $wp_version is outdated and vulnerable";
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $issues[] = "PHP version " . PHP_VERSION . " is outdated and vulnerable";
        }

        $this->audit_results['checks']['vulnerable_components'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Keep WordPress and PHP updated',
                'Regularly update all plugins and themes',
                'Monitor security advisories for dependencies'
            ]
        ];
    }

    /**
     * A07:2021 - Identification & Authentication Failures
     */
    private function check_broken_authentication() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for weak authentication
            if (preg_match('/wp_signon.*\[\$/', $content) && !preg_match('/wp_verify_nonce/i', $content)) {
                $issues[] = "Possible weak authentication in $file";
            }
        }

        $this->audit_results['checks']['broken_authentication'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Implement proper session management',
                'Use strong password policies',
                'Implement account lockout mechanisms'
            ]
        ];
    }

    /**
     * A08:2021 - Software & Data Integrity Failures
     */
    private function check_xml_external_entities() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for XML processing
            if (preg_match('/simplexml_load_string|DOMDocument/i', $content)) {
                $issues[] = "XML processing detected in $file - check for XXE vulnerabilities";
            }
        }

        $this->audit_results['checks']['xml_external_entities'] = [
            'status' => empty($issues) ? 'PASS' : 'WARN',
            'issues' => $issues,
            'recommendations' => [
                'Disable external entity processing in XML parsers',
                'Use JSON instead of XML when possible',
                'Validate XML input against schema'
            ]
        ];
    }

    /**
     * A09:2021 - Security Logging & Monitoring Failures
     */
    private function check_insufficient_logging() {
        $issues = [];

        $files = $this->get_php_files();
        $has_logging = false;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/error_log|wp_mail.*admin/i', $content)) {
                $has_logging = true;
                break;
            }
        }

        if (!$has_logging) {
            $issues[] = "No security logging detected";
        }

        $this->audit_results['checks']['insufficient_logging'] = [
            'status' => $has_logging ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Implement comprehensive security logging',
                'Log authentication failures',
                'Log privilege escalation attempts',
                'Monitor for suspicious activities'
            ]
        ];
    }

    /**
     * A10:2021 - Server-Side Request Forgery
     */
    private function check_cross_site_scripting() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for XSS vulnerabilities
            if (preg_match('/echo.*\$[^\$]/', $content) && !preg_match('/esc_html|esc_attr|wp_kses/i', $content)) {
                $issues[] = "Possible XSS vulnerability in $file - output not escaped";
            }
        }

        $this->audit_results['checks']['cross_site_scripting'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Escape all output with esc_html(), esc_attr(), or wp_kses()',
                'Use proper output sanitization',
                'Implement Content Security Policy (CSP)'
            ]
        ];
    }

    /**
     * File upload security
     */
    private function check_file_upload_security() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for file upload handling
            if (preg_match('/\$_FILES/i', $content)) {
                if (!preg_match('/wp_handle_upload|wp_check_filetype/i', $content)) {
                    $issues[] = "File upload in $file without proper validation";
                }
            }
        }

        $this->audit_results['checks']['file_upload_security'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Validate file types and sizes',
                'Use wp_handle_upload() for file processing',
                'Store uploads outside web root',
                'Scan uploaded files for malware'
            ]
        ];
    }

    /**
     * Rate limiting check
     */
    private function check_rate_limiting() {
        $rate_limiter_file = plugin_dir_path(__FILE__) . 'src/Security/Rate_Limiter.php';

        if (file_exists($rate_limiter_file)) {
            $content = file_get_contents($rate_limiter_file);
            $status = strpos($content, 'RATE LIMIT DÉSACTIVÉ') !== false ? 'FAIL' : 'PASS';
        } else {
            $status = 'FAIL';
        }

        $this->audit_results['checks']['rate_limiting'] = [
            'status' => $status,
            'issues' => $status === 'FAIL' ? ['Rate limiting is disabled or missing'] : [],
            'recommendations' => [
                'Implement rate limiting for all public endpoints',
                'Use different limits for different user roles',
                'Implement progressive delays for repeated attempts'
            ]
        ];
    }

    /**
     * Input validation check
     */
    private function check_input_validation() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for $_POST, $_GET usage without sanitization
            if (preg_match('/\$_POST\[.*\]/', $content) && !preg_match('/sanitize_text_field|sanitize_email|absint/i', $content)) {
                $issues[] = "Unsanitized POST input in $file";
            }

            if (preg_match('/\$_GET\[.*\]/', $content) && !preg_match('/sanitize_text_field|absint/i', $content)) {
                $issues[] = "Unsanitized GET input in $file";
            }
        }

        $this->audit_results['checks']['input_validation'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Sanitize all user inputs',
                'Validate input types and formats',
                'Use whitelist validation approach',
                'Implement input length limits'
            ]
        ];
    }

    /**
     * Output encoding check
     */
    private function check_output_encoding() {
        $issues = [];

        $files = $this->get_php_files();
        foreach ($files as $file) {
            $content = file_get_contents($file);

            // Check for output without encoding
            if (preg_match('/echo.*\$|print.*\$/', $content) && !preg_match('/esc_html|esc_attr|esc_js|wp_kses/i', $content)) {
                $issues[] = "Unencoded output in $file";
            }
        }

        $this->audit_results['checks']['output_encoding'] = [
            'status' => empty($issues) ? 'PASS' : 'FAIL',
            'issues' => $issues,
            'recommendations' => [
                'Encode all output to prevent XSS',
                'Use context-appropriate encoding functions',
                'Implement Content Security Policy'
            ]
        ];
    }

    /**
     * Get all PHP files in the plugin
     */
    private function get_php_files() {
        $plugin_dir = plugin_dir_path(__FILE__);
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get audit results
     */
    public function get_audit_results() {
        return $this->audit_results;
    }

    /**
     * Get vulnerabilities count
     */
    public function get_vulnerability_count() {
        $count = 0;
        foreach ($this->audit_results['checks'] as $check) {
            if ($check['status'] === 'FAIL') {
                $count += count($check['issues']);
            }
        }
        return $count;
    }

    /**
     * Generate security report
     */
    public function generate_report() {
        $report = "=== PDF BUILDER PRO - SECURITY AUDIT REPORT ===\n";
        $report .= "Date: " . date('Y-m-d H:i:s', $this->audit_results['timestamp']) . "\n\n";

        $total_checks = count($this->audit_results['checks']);
        $passed_checks = 0;
        $failed_checks = 0;

        foreach ($this->audit_results['checks'] as $check_name => $check_data) {
            $status = $check_data['status'];
            if ($status === 'PASS') {
                $passed_checks++;
            } elseif ($status === 'FAIL') {
                $failed_checks++;
            }

            $report .= strtoupper(str_replace('_', ' ', $check_name)) . ": " . $status . "\n";

            if (!empty($check_data['issues'])) {
                foreach ($check_data['issues'] as $issue) {
                    $report .= "  - " . $issue . "\n";
                }
            }

            if (!empty($check_data['recommendations'])) {
                $report .= "  Recommendations:\n";
                foreach ($check_data['recommendations'] as $rec) {
                    $report .= "    * " . $rec . "\n";
                }
            }
            $report .= "\n";
        }

        $report .= "SUMMARY:\n";
        $report .= "- Total checks: $total_checks\n";
        $report .= "- Passed: $passed_checks\n";
        $report .= "- Failed: $failed_checks\n";
        $report .= "- Vulnerability count: " . $this->get_vulnerability_count() . "\n";

        return $report;
    }
}

// ============================================================================
// GESTIONNAIRE DE SÉCURITÉ RENFORCÉ
// ============================================================================

class PDF_Builder_Security_Hardener {

    private static $instance = null;
    private $security_settings = [];

    private function __construct() {
        $this->security_settings = get_option('pdf_builder_security_settings', $this->get_default_settings());
        $this->init_security_measures();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Paramètres de sécurité par défaut
     */
    private function get_default_settings() {
        return [
            'rate_limiting_enabled' => true,
            'max_requests_per_minute' => 100,
            'max_file_size' => 52428800, // 50MB
            'allowed_file_types' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
            'input_validation_strict' => true,
            'security_logging_enabled' => true,
            'max_execution_time' => 300,
            'memory_limit' => 256,
            'enable_csp' => false,
            'csp_directives' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'"
        ];
    }

    /**
     * Initialise les mesures de sécurité
     */
    private function init_security_measures() {
        // Rate limiting amélioré
        if ($this->security_settings['rate_limiting_enabled']) {
            $this->init_rate_limiting();
        }

        // Validation d'entrée renforcée
        if ($this->security_settings['input_validation_strict']) {
            $this->init_input_validation();
        }

        // Logging de sécurité
        if ($this->security_settings['security_logging_enabled']) {
            $this->init_security_logging();
        }

        // Content Security Policy
        if ($this->security_settings['enable_csp']) {
            $this->init_csp();
        }

        // Headers de sécurité
        $this->init_security_headers();

        // Protection contre les attaques communes
        $this->init_attack_protection();
    }

    /**
     * Initialise le rate limiting amélioré
     */
    private function init_rate_limiting() {
        add_action('init', function() {
            // Rate limiting pour les requêtes AJAX
            if (wp_doing_ajax() && isset($_REQUEST['action'])) {
                $action = sanitize_text_field($_REQUEST['action']);
                if (strpos($action, 'pdf_builder') === 0) {
                    $this->check_rate_limit();
                }
            }
        });
    }

    /**
     * Vérifie les limites de taux
     */
    private function check_rate_limit() {
        $ip = $this->get_client_ip();
        $transient_key = 'pdf_builder_rate_' . md5($ip);

        $requests = get_transient($transient_key);
        if ($requests === false) {
            $requests = 0;
        }

        $requests++;

        if ($requests > $this->security_settings['max_requests_per_minute']) {
            // Log l'incident
            $this->log_security_event('rate_limit_exceeded', [
                'ip' => $ip,
                'requests' => $requests,
                'action' => $_REQUEST['action'] ?? 'unknown'
            ]);

            wp_die(__('Trop de requêtes. Veuillez réessayer plus tard.', 'pdf-builder'), 429);
        }

        set_transient($transient_key, $requests, 60); // 1 minute
    }

    /**
     * Initialise la validation d'entrée renforcée
     */
    private function init_input_validation() {
        // Filtre pour les requêtes AJAX
        add_filter('wp_ajax_pdf_builder_unified_dispatch', function($action) {
            return $this->validate_ajax_request($action);
        }, 1);

        // Validation des uploads
        add_filter('wp_handle_upload_prefilter', function($file) {
            return $this->validate_file_upload($file);
        });
    }

    /**
     * Valide les requêtes AJAX
     */
    private function validate_ajax_request($action) {
        // Vérifier le nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_ajax')) {
            $this->log_security_event('invalid_nonce', [
                'action' => $action,
                'ip' => $this->get_client_ip()
            ]);
            wp_die(__('Requête non autorisée.', 'pdf-builder'), 403);
        }

        // Vérifier les capacités utilisateur
        if (!current_user_can('manage_options')) {
            $this->log_security_event('insufficient_permissions', [
                'action' => $action,
                'user_id' => get_current_user_id(),
                'ip' => $this->get_client_ip()
            ]);
            wp_die(__('Permissions insuffisantes.', 'pdf-builder'), 403);
        }

        // Valider et nettoyer les entrées
        $this->sanitize_request_data();

        return $action;
    }

    /**
     * Nettoie les données de requête
     */
    private function sanitize_request_data() {
        foreach ($_POST as $key => $value) {
            if (is_string($value)) {
                $_POST[$key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $_POST[$key] = $this->sanitize_array($value);
            }
        }

        foreach ($_GET as $key => $value) {
            if (is_string($value)) {
                $_GET[$key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $_GET[$key] = $this->sanitize_array($value);
            }
        }
    }

    /**
     * Nettoie un tableau récursivement
     */
    private function sanitize_array($array) {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $array[$key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $array[$key] = $this->sanitize_array($value);
            }
        }
        return $array;
    }

    /**
     * Valide les uploads de fichiers
     */
    private function validate_file_upload($file) {
        // Vérifier la taille
        if ($file['size'] > $this->security_settings['max_file_size']) {
            $file['error'] = __('Fichier trop volumineux.', 'pdf-builder');
            return $file;
        }

        // Vérifier le type
        $file_type = wp_check_filetype($file['name']);
        if (!in_array(strtolower($file_type['ext']), $this->security_settings['allowed_file_types'])) {
            $file['error'] = __('Type de fichier non autorisé.', 'pdf-builder');
            return $file;
        }

        // Vérifier le contenu (basique)
        if ($this->is_malicious_file($file['tmp_name'])) {
            $file['error'] = __('Fichier potentiellement dangereux.', 'pdf-builder');
            return $file;
        }

        return $file;
    }

    /**
     * Vérifie si un fichier est potentiellement malveillant
     */
    private function is_malicious_file($file_path) {
        $content = file_get_contents($file_path);

        // Signatures malveillantes simples
        $malicious_patterns = [
            '<?php', '<script', 'eval(', 'base64_decode',
            'system(', 'exec(', 'shell_exec('
        ];

        foreach ($malicious_patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Initialise le logging de sécurité
     */
    private function init_security_logging() {
        add_action('wp_login_failed', function($username) {
            $this->log_security_event('login_failed', [
                'username' => $username,
                'ip' => $this->get_client_ip()
            ]);
        });

        add_action('wp_login', function($user_login, $user) {
            $this->log_security_event('login_success', [
                'username' => $user_login,
                'user_id' => $user->ID,
                'ip' => $this->get_client_ip()
            ]);
        }, 10, 2);
    }

    /**
     * Log un événement de sécurité
     */
    private function log_security_event($event_type, $data = []) {
        $log_entry = [
            'timestamp' => current_time('timestamp'),
            'event' => $event_type,
            'data' => $data,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        ];

        // Log dans le fichier de debug WordPress
        error_log('PDF_BUILDER_SECURITY: ' . json_encode($log_entry));

        // Stockage en base pour les événements critiques
        if (in_array($event_type, ['rate_limit_exceeded', 'invalid_nonce', 'insufficient_permissions'])) {
            $this->store_security_event($log_entry);
        }
    }

    /**
     * Stocke un événement de sécurité en base
     */
    private function store_security_event($event) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pdf_builder_security_logs';

        // Créer la table si elle n'existe pas
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $this->create_security_logs_table();
        }

        $wpdb->insert($table_name, [
            'event_type' => $event['event'],
            'event_data' => json_encode($event['data']),
            'ip_address' => $event['data']['ip'] ?? '',
            'user_agent' => $event['user_agent'],
            'created_at' => date('Y-m-d H:i:s', $event['timestamp'])
        ]);
    }

    /**
     * Crée la table des logs de sécurité
     */
    private function create_security_logs_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pdf_builder_security_logs';

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            event_type varchar(100) NOT NULL,
            event_data longtext,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_event_type (event_type),
            INDEX idx_created_at (created_at),
            INDEX idx_ip (ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Initialise les headers de sécurité
     */
    private function init_security_headers() {
        add_action('send_headers', function() {
            // Headers de sécurité recommandés
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');

            // HSTS (uniquement en HTTPS)
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        });
    }

    /**
     * Initialise la Content Security Policy
     */
    private function init_csp() {
        add_action('send_headers', function() {
            header('Content-Security-Policy: ' . $this->security_settings['csp_directives']);
        });
    }

    /**
     * Initialise la protection contre les attaques communes
     */
    private function init_attack_protection() {
        // Protection contre les attaques par déni de service basiques
        add_action('init', function() {
            // Limiter la taille des requêtes POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $content_length = $_SERVER['CONTENT_LENGTH'] ?? 0;
                if ($content_length > 10485760) { // 10MB
                    wp_die(__('Requête trop volumineuse.', 'pdf-builder'), 413);
                }
            }
        });

        // Protection contre les inclusions de fichiers
        add_action('init', function() {
            // Supprimer les caractères dangereux des chemins
            if (isset($_GET['file'])) {
                $_GET['file'] = str_replace(['../', '..\\', '\\', '/'], '', $_GET['file']);
            }
        });
    }

    /**
     * Obtient l'adresse IP réelle du client
     */
    private function get_client_ip() {
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

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Obtient les paramètres de sécurité
     */
    public function get_security_settings() {
        return $this->security_settings;
    }

    /**
     * Met à jour les paramètres de sécurité
     */
    public function update_security_settings($settings) {
        $this->security_settings = array_merge($this->security_settings, $settings);
        update_option('pdf_builder_security_settings', $this->security_settings);
        return true;
    }
}

// ============================================================================
// INITIALISATION DU MODULE
// ============================================================================

// Initialiser l'audit de sécurité
add_action('plugins_loaded', function() {
    PDF_Builder_Security_Audit::get_instance();
    PDF_Builder_Security_Hardener::get_instance();
});

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/**
 * Obtient l'instance de l'auditeur de sécurité
 */
function pdf_builder_get_security_audit() {
    return PDF_Builder_Security_Audit::get_instance();
}

/**
 * Obtient l'instance du renforceur de sécurité
 */
function pdf_builder_get_security_hardener() {
    return PDF_Builder_Security_Hardener::get_instance();
}

/**
 * Génère un rapport d'audit de sécurité
 */
function pdf_builder_generate_security_report() {
    $audit = pdf_builder_get_security_audit();
    return $audit->generate_report();
}

/**
 * Obtient le nombre de vulnérabilités
 */
function pdf_builder_get_vulnerability_count() {
    $audit = pdf_builder_get_security_audit();
    return $audit->get_vulnerability_count();
}

/**
 * Met à jour les paramètres de sécurité
 */
function pdf_builder_update_security_settings($settings) {
    $hardener = pdf_builder_get_security_hardener();
    return $hardener->update_security_settings($settings);
}

/**
 * Log un événement de sécurité personnalisé
 */
function pdf_builder_log_security_event($event_type, $data = []) {
    $hardener = pdf_builder_get_security_hardener();
    // Utiliser la méthode privée via réflexion (hack pour accès)
    $reflection = new ReflectionClass($hardener);
    $method = $reflection->getMethod('log_security_event');
    $method->setAccessible(true);
    $method->invoke($hardener, $event_type, $data);
}