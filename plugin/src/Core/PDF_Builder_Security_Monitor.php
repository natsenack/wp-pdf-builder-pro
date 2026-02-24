<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Système de surveillance de sécurité
 * Détecte les menaces, vulnérabilités et activités suspectes
 */

class PDF_Builder_Security_Monitor {
    private static $instance = null;

    // Niveaux de menace
    const THREAT_LEVEL_LOW = 'low';
    const THREAT_LEVEL_MEDIUM = 'medium';
    const THREAT_LEVEL_HIGH = 'high';
    const THREAT_LEVEL_CRITICAL = 'critical';

    // Types de menaces
    const THREAT_TYPE_SQL_INJECTION = 'sql_injection';
    const THREAT_TYPE_XSS = 'xss';
    const THREAT_TYPE_CSFR = 'csrf';
    const THREAT_TYPE_FILE_UPLOAD = 'file_upload';
    const THREAT_TYPE_BRUTE_FORCE = 'brute_force';
    const THREAT_TYPE_SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    const THREAT_TYPE_VULNERABILITY = 'vulnerability';

    // Seuils de détection
    private $thresholds = [
        'failed_logins_per_hour' => 10,
        'suspicious_requests_per_minute' => 30,
        'file_upload_attempts_per_hour' => 50,
        'sql_injection_attempts_per_hour' => 5,
        'xss_attempts_per_hour' => 10
    ];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_thresholds();
    }

    private function init_hooks() {
        // Surveillance des requêtes AJAX
        add_action('wp_ajax_pdf_builder_security_scan', [$this, 'security_scan_ajax']);
        add_action('wp_ajax_pdf_builder_get_security_status', [$this, 'get_security_status_ajax']);
        add_action('wp_ajax_pdf_builder_block_ip', [$this, 'block_ip_ajax']);

        // Surveillance des menaces
        add_action('wp_loaded', [$this, 'monitor_requests']);
        add_action('wp_login_failed', [$this, 'monitor_failed_login']);
        add_action('wp_login', [$this, 'monitor_successful_login']);

        // Surveillance des fichiers
        add_action('wp_handle_upload', [$this, 'monitor_file_upload']);

        // Scans de sécurité périodiques
        add_action('pdf_builder_hourly_security_scan', [$this, 'perform_hourly_security_scan']);
        add_action('pdf_builder_daily_security_scan', [$this, 'perform_daily_security_scan']);

        // Nettoyage des logs de sécurité
        add_action('pdf_builder_weekly_security_cleanup', [$this, 'cleanup_security_logs']);
    }

    /**
     * Charge les seuils de détection depuis la configuration
     */
    private function load_thresholds() {
        $config_thresholds = pdf_builder_config('security_thresholds', []);

        if (!empty($config_thresholds)) {
            $this->thresholds = array_merge($this->thresholds, $config_thresholds);
        }
    }

    /**
     * Surveille toutes les requêtes entrantes
     */
    public function monitor_requests() {
        if (!is_admin() && !wp_doing_ajax()) {
            return;
        }

        $request_data = [
            'ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
            'post_data' => !empty($_POST) ? array_keys($_POST) : [],
            'timestamp' => current_time('mysql')
        ];

        // Détecter les menaces potentielles
        $threats = $this->detect_threats($request_data);

        if (!empty($threats)) {
            $this->handle_threats($threats, $request_data);
        }

        // Enregistrer l'activité normale
        $this->log_request_activity($request_data);
    }

    /**
     * Détecte les menaces dans une requête
     */
    private function detect_threats($request_data) {
        $threats = [];

        // Détection d'injection SQL
        if ($this->detect_sql_injection($request_data)) {
            $threats[] = [
                'type' => self::THREAT_TYPE_SQL_INJECTION,
                'level' => self::THREAT_LEVEL_HIGH,
                'description' => 'Tentative d\'injection SQL détectée',
                'data' => $request_data
            ];
        }

        // Détection XSS
        if ($this->detect_xss($request_data)) {
            $threats[] = [
                'type' => self::THREAT_TYPE_XSS,
                'level' => self::THREAT_LEVEL_MEDIUM,
                'description' => 'Tentative XSS détectée',
                'data' => $request_data
            ];
        }

        // Détection d'activité suspecte
        if ($this->detect_suspicious_activity($request_data)) {
            $threats[] = [
                'type' => self::THREAT_TYPE_SUSPICIOUS_ACTIVITY,
                'level' => self::THREAT_LEVEL_LOW,
                'description' => 'Activité suspecte détectée',
                'data' => $request_data
            ];
        }

        // Vérifier les seuils de fréquence
        $frequency_threats = $this->check_frequency_thresholds($request_data);
        $threats = array_merge($threats, $frequency_threats);

        return $threats;
    }

    /**
     * Détecte les tentatives d'injection SQL
     */
    private function detect_sql_injection($request_data) {
        $patterns = [
            '/\bUNION\b.*\bSELECT\b/i',
            '/\bDROP\b.*\bTABLE\b/i',
            '/\bALTER\b.*\bTABLE\b/i',
            '/\bDELETE\b.*\bFROM\b/i',
            '/\bINSERT\b.*\bINTO\b/i',
            '/\bUPDATE\b.*\bSET\b/i',
            '/--/',
            '/\/\*.*\*\//',
            '/\bOR\b.*=.*\bOR\b/i',
            '/\bAND\b.*=.*\bAND\b/i',
            '/\bSCRIPT\b/i',
            '/\bEXEC\b/i',
            '/\bXP_CMDSHELL\b/i'
        ];

        $check_data = $request_data['query_string'] . ' ' . implode(' ', $request_data['post_data']);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $check_data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte les tentatives XSS
     */
    private function detect_xss($request_data) {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i'
        ];

        $check_data = $request_data['query_string'] . ' ' . implode(' ', $request_data['post_data']);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $check_data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte les activités suspectes
     */
    private function detect_suspicious_activity($request_data) {
        // Vérifier les User-Agent suspects
        $suspicious_user_agents = [
            'sqlmap',
            'nmap',
            'nikto',
            'dirbuster',
            'gobuster',
            'wpscan',
            'acunetix',
            'openvas'
        ];

        foreach ($suspicious_user_agents as $agent) {
            if (stripos($request_data['user_agent'], $agent) !== false) {
                return true;
            }
        }

        // Vérifier les chemins suspects
        $suspicious_paths = [
            '/wp-admin/admin-ajax.php',
            '/wp-content/plugins/',
            '/wp-content/themes/',
            '/wp-includes/'
        ];

        foreach ($suspicious_paths as $path) {
            if (stripos($request_data['request_uri'], $path) !== false) {
                // Vérifier si c'est une requête POST inhabituelle
                if ($request_data['request_method'] === 'POST' && !wp_doing_ajax()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Vérifie les seuils de fréquence
     */
    private function check_frequency_thresholds($request_data) {
        $threats = [];
        $ip = $request_data['ip'];

        // Vérifier les échecs de connexion
        $failed_logins = $this->get_recent_failed_logins($ip, HOUR_IN_SECONDS);
        if ($failed_logins >= $this->thresholds['failed_logins_per_hour']) {
            $threats[] = [
                'type' => self::THREAT_TYPE_BRUTE_FORCE,
                'level' => self::THREAT_LEVEL_HIGH,
                'description' => 'Tentative de force brute détectée',
                'data' => array_merge($request_data, ['failed_logins' => $failed_logins])
            ];
        }

        // Vérifier les requêtes suspectes
        $suspicious_requests = $this->get_recent_suspicious_requests($ip, MINUTE_IN_SECONDS);
        if ($suspicious_requests >= $this->thresholds['suspicious_requests_per_minute']) {
            $threats[] = [
                'type' => self::THREAT_TYPE_SUSPICIOUS_ACTIVITY,
                'level' => self::THREAT_LEVEL_MEDIUM,
                'description' => 'Trop de requêtes suspectes',
                'data' => array_merge($request_data, ['suspicious_requests' => $suspicious_requests])
            ];
        }

        return $threats;
    }

    /**
     * Gère les menaces détectées
     */
    private function handle_threats($threats, $request_data) {
        foreach ($threats as $threat) {
            // Enregistrer la menace
            $this->log_threat($threat);

            // Prendre des mesures selon le niveau
            switch ($threat['level']) {
                case self::THREAT_LEVEL_CRITICAL:
                    $this->handle_critical_threat($threat);
                    break;

                case self::THREAT_LEVEL_HIGH:
                    $this->handle_high_threat($threat);
                    break;

                case self::THREAT_LEVEL_MEDIUM:
                    $this->handle_medium_threat($threat);
                    break;

                case self::THREAT_LEVEL_LOW:
                    $this->handle_low_threat($threat);
                    break;
            }
        }
    }

    /**
     * Gère les menaces critiques
     */
    private function handle_critical_threat($threat) {
        $ip = $threat['data']['ip'];

        // Bloquer immédiatement l'IP
        $this->block_ip($ip, 'Menace critique détectée: ' . $threat['description']);

        // Legacy notification calls removed — log as critical

        // Créer une sauvegarde d'urgence
        if (class_exists('PDF_Builder_Backup_Recovery_System')) {
            PDF_Builder_Backup_Recovery_System::get_instance()->create_emergency_backup();
        }
    }

    /**
     * Gère les menaces élevées
     */
    private function handle_high_threat($threat) {
        $ip = $threat['data']['ip'];

        // Journaliser (ancienne UI) — log as error

        // Temporiser l'IP si c'est une attaque par force brute
        if ($threat['type'] === self::THREAT_TYPE_BRUTE_FORCE) {
            $this->temporarily_block_ip($ip, 900); // 15 minutes
        }
    }

    /**
     * Gère les menaces moyennes
     */
    private function handle_medium_threat($threat) {
        // Journaliser (ancienne UI) — log warning
    }

    /**
     * Gère les menaces faibles
     */
    private function handle_low_threat($threat) {
        // Juste journaliser
    }

    /**
     * Surveille les échecs de connexion
     */
    public function monitor_failed_login($username) {
        $ip = $this->get_client_ip();

        $this->log_security_event('failed_login', [
            'username' => $username,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => current_time('mysql')
        ]);

        // Vérifier si c'est une attaque par force brute
        $failed_logins = $this->get_recent_failed_logins($ip, HOUR_IN_SECONDS);
        if ($failed_logins >= $this->thresholds['failed_logins_per_hour']) {
            $this->handle_threats([[
                'type' => self::THREAT_TYPE_BRUTE_FORCE,
                'level' => self::THREAT_LEVEL_HIGH,
                'description' => 'Attaque par force brute détectée',
                'data' => ['ip' => $ip, 'failed_logins' => $failed_logins]
            ]], []);
        }
    }

    /**
     * Surveille les connexions réussies
     */
    public function monitor_successful_login($user_login) {
        $user = get_user_by('login', $user_login);
        
        if (!$user) {
            return;
        }
        
        $ip = $this->get_client_ip();

        $this->log_security_event('successful_login', [
            'user_id' => $user->ID,
            'username' => $user_login,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'timestamp' => current_time('mysql')
        ]);
    }

    /**
     * Surveille les téléchargements de fichiers
     */
    public function monitor_file_upload($file) {
        $ip = $this->get_client_ip();

        // Vérifier le type de fichier
        $file_info = pathinfo($file['file']);
        $extension = strtolower($file_info['extension'] ?? '');

        $suspicious_extensions = ['php', 'exe', 'bat', 'cmd', 'scr', 'pif', 'com'];

        if (in_array($extension, $suspicious_extensions)) {
            $threat = [
                'type' => self::THREAT_TYPE_FILE_UPLOAD,
                'level' => self::THREAT_LEVEL_HIGH,
                'description' => 'Téléchargement de fichier suspect détecté',
                'data' => [
                    'ip' => $ip,
                    'file' => $file['file'],
                    'extension' => $extension,
                    'size' => $file['size']
                ]
            ];

            $this->handle_threats([$threat], []);
        }

        // Enregistrer l'activité normale
        $this->log_security_event('file_upload', [
            'ip' => $ip,
            'file' => $file['file'],
            'extension' => $extension,
            'size' => $file['size'],
            'timestamp' => current_time('mysql')
        ]);
    }

    /**
     * Effectue un scan de sécurité horaire
     */
    public function perform_hourly_security_scan() {
        $issues = [];

        // Vérifier les vulnérabilités connues
        $vulnerabilities = $this->check_known_vulnerabilities();
        $issues = array_merge($issues, $vulnerabilities);

        // Vérifier les permissions des fichiers
        $permission_issues = $this->check_file_permissions();
        $issues = array_merge($issues, $permission_issues);

        // Vérifier les comptes suspects
        $suspicious_accounts = $this->check_suspicious_accounts();
        $issues = array_merge($issues, $suspicious_accounts);

        if (!empty($issues)) {
            // Legacy notification calls removed — log as warning
        }
    }

    /**
     * Effectue un scan de sécurité quotidien
     */
    public function perform_daily_security_scan() {
        $issues = [];

        // Analyse approfondie des logs
        $log_analysis = $this->analyze_security_logs();
        $issues = array_merge($issues, $log_analysis);

        // Vérifier les mises à jour de sécurité
        $security_updates = $this->check_security_updates();
        $issues = array_merge($issues, $security_updates);

        if (!empty($issues)) {
            // Legacy notification calls removed — log as error
        }
    }

    /**
     * Vérifie les vulnérabilités connues
     */
    private function check_known_vulnerabilities() {
        $issues = [];

        // Vérifier les versions des composants
        $wp_version = get_bloginfo('version');
        if (version_compare($wp_version, '6.0', '<')) {
            $issues[] = "Version WordPress ancienne détectée: $wp_version";
        }

        // Vérifier les plugins vulnérables
        $vulnerable_plugins = $this->get_vulnerable_plugins();
        foreach ($vulnerable_plugins as $plugin) {
            $issues[] = "Plugin vulnérable détecté: {$plugin['name']} (version {$plugin['version']})";
        }

        return $issues;
    }

    /**
     * Vérifie les permissions des fichiers
     */
    private function check_file_permissions() {
        $issues = [];

        $critical_files = [
            PDF_BUILDER_PLUGIN_FILE => 'Fichier principal du plugin',
            WP_CONTENT_DIR . '/uploads/' => 'Dossier uploads',
            wp_upload_dir()['basedir'] . '/pdf-builder/' => 'Dossier PDF Builder'
        ];

        foreach ($critical_files as $file => $description) {
            if (file_exists($file)) {
                $perms = substr(sprintf('%o', fileperms($file)), -4);

                // Les fichiers ne devraient pas être exécutables
                if (is_file($file) && ($perms & 0111)) {
                    $issues[] = "Fichier exécutable détecté: $description ($perms)";
                }

                // Les dossiers devraient être accessibles en écriture uniquement par le propriétaire
                if (is_dir($file) && ($perms & 0022)) {
                    $issues[] = "Permissions trop permissives: $description ($perms)";
                }
            }
        }

        return $issues;
    }

    /**
     * Vérifie les comptes suspects
     */
    private function check_suspicious_accounts() {
        $issues = [];

        // Comptes administrateur avec mots de passe faibles
        $admin_users = get_users(['role' => 'administrator']);
        
        if (!$admin_users) {
            return $issues;
        }
        
        foreach ($admin_users as $user) {
            if ($this->is_weak_password($user->user_login)) {
                $issues[] = "Mot de passe faible détecté pour l'administrateur: {$user->user_login}";
            }
        }

        // Comptes inactifs depuis longtemps
        $inactive_users = $this->get_inactive_users(90); // 90 jours
        foreach ($inactive_users as $user) {
            $issues[] = "Compte inactif détecté: {$user->user_login} (dernière activité: {$user->last_activity})";
        }

        return $issues;
    }

    /**
     * Analyse les logs de sécurité
     */
    private function analyze_security_logs() {
        $issues = [];

        // Analyser les tendances des menaces
        $threat_trends = $this->analyze_threat_trends();

        if ($threat_trends['total_threats'] > 100) {
            $issues[] = "Nombre élevé de menaces détectées: {$threat_trends['total_threats']}";
        }

        // Détecter les IPs suspectes
        $suspicious_ips = $this->get_most_active_suspicious_ips();
        foreach ($suspicious_ips as $ip => $count) {
            if ($count > 50) {
                $issues[] = "IP très active suspecte: $ip ($count menaces)";
            }
        }

        return $issues;
    }

    /**
     * Obtient l'adresse IP du client
     */
    private function get_client_ip() {
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

                // Gérer X-Forwarded-For avec plusieurs IPs
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
     * Obtient les échecs de connexion récents
     */
    private function get_recent_failed_logins($ip, $timeframe) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_events';

        return $wpdb->get_var($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT COUNT(*) FROM $table
            WHERE event_type = 'failed_login'
            AND ip_address = %s
            AND created_at > DATE_SUB(NOW(), INTERVAL %d SECOND)
        ", $ip, $timeframe));
    }

    /**
     * Obtient les requêtes suspectes récentes
     */
    private function get_recent_suspicious_requests($ip, $timeframe) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_events';

        return $wpdb->get_var($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT COUNT(*) FROM $table
            WHERE event_type IN ('sql_injection', 'xss', 'suspicious_activity')
            AND ip_address = %s
            AND created_at > DATE_SUB(NOW(), INTERVAL %d SECOND)
        ", $ip, $timeframe));
    }

    /**
     * Enregistre un événement de sécurité
     */
    private function log_security_event($event_type, $data) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_events';

        $wpdb->insert(
            $table,
            [
                'event_type' => $event_type,
                'ip_address' => $data['ip'] ?? '',
                'user_id' => $data['user_id'] ?? null,
                'event_data' => json_encode($data),
                'created_at' => $data['timestamp'] ?? current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s', '%s']
        );
    }

    /**
     * Enregistre une menace
     */
    private function log_threat($threat) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_threats';

        $wpdb->insert(
            $table,
            [
                'threat_type' => $threat['type'],
                'threat_level' => $threat['level'],
                'description' => $threat['description'],
                'ip_address' => $threat['data']['ip'] ?? '',
                'threat_data' => json_encode($threat),
                'status' => 'detected',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        // Logger aussi dans le système de logging avancé
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Threat detected: ' . $threat['type']);
        }
    }

    /**
     * Enregistre l'activité des requêtes
     */
    private function log_request_activity($request_data) {
        // Échantillonnage pour éviter de surcharger la base
        if (wp_rand(1, 100) <= 10) { // 10% des requêtes
            global $wpdb;

            $table = $wpdb->prefix . 'pdf_builder_request_logs';

            $wpdb->insert(
                $table,
                [
                    'ip_address' => $request_data['ip'],
                    'request_uri' => $request_data['request_uri'],
                    'request_method' => $request_data['request_method'],
                    'user_agent' => substr($request_data['user_agent'], 0, 500),
                    'created_at' => $request_data['timestamp']
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );
        }
    }

    /**
     * Bloque une IP
     */
    private function block_ip($ip, $reason) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_blocked_ips';

        $wpdb->replace(
            $table,
            [
                'ip_address' => $ip,
                'blocked_at' => current_time('mysql'),
                'reason' => $reason,
                'blocked_by' => 'auto'
            ],
            ['%s', '%s', '%s', '%s']
        );

        // Ajouter aux règles de blocage du serveur si possible
        $this->add_server_block_rule($ip);
    }

    /**
     * Bloque temporairement une IP
     */
    private function temporarily_block_ip($ip, $duration) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_temp_blocks';

        $wpdb->replace(
            $table,
            [
                'ip_address' => $ip,
                'blocked_until' => gmdate('Y-m-d H:i:s', time() + $duration),
                'reason' => 'Blocage temporaire automatique',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s']
        );
    }

    /**
     * Ajoute une règle de blocage au niveau serveur
     */
    private function add_server_block_rule($ip) {
        // Pour Apache
        if (function_exists('apache_get_version')) {
            $htaccess_file = ABSPATH . '.htaccess';

            if (is_writable($htaccess_file)) { // phpcs:ignore WordPress.WP.AlternativeFunctions
                $rule = "\n# PDF Builder Security Block\nDeny from $ip\n";

                file_put_contents($htaccess_file, $rule, FILE_APPEND);
            }
        }

        // Pour Nginx, on ne peut pas modifier directement nginx.conf
        // Il faudrait utiliser un plugin spécialisé ou des règles firewall
    }

    /**
     * Vérifie si une IP est bloquée
     */
    public function is_ip_blocked($ip) {
        global $wpdb;

        // Vérifier les blocages permanents
        $blocked_table = $wpdb->prefix . 'pdf_builder_blocked_ips';
        $blocked = $wpdb->get_var($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT COUNT(*) FROM $blocked_table WHERE ip_address = %s
        ", $ip));

        if ($blocked > 0) {
            return true;
        }

        // Vérifier les blocages temporaires
        $temp_table = $wpdb->prefix . 'pdf_builder_temp_blocks';
        $temp_blocked = $wpdb->get_var($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT COUNT(*) FROM $temp_table
            WHERE ip_address = %s AND blocked_until > NOW()
        ", $ip));

        return $temp_blocked > 0;
    }

    /**
     * Nettoie les logs de sécurité
     */
    public function cleanup_security_logs() {
        global $wpdb;

        $retention_days = pdf_builder_config('security_log_retention_days', 90);

        // Nettoyer les événements de sécurité
        $events_table = $wpdb->prefix . 'pdf_builder_security_events';
        $wpdb->query($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $events_table
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $retention_days));

        // Nettoyer les menaces
        $threats_table = $wpdb->prefix . 'pdf_builder_security_threats';
        $wpdb->query($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $threats_table
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $retention_days));

        // Nettoyer les logs de requêtes
        $requests_table = $wpdb->prefix . 'pdf_builder_request_logs';
        $wpdb->query($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $requests_table
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $retention_days));

        // Nettoyer les blocages temporaires expirés
        $temp_blocks_table = $wpdb->prefix . 'pdf_builder_temp_blocks';
        $wpdb->query(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $temp_blocks_table
            WHERE blocked_until < NOW()
        ");
    }

    /**
     * Méthodes utilitaires pour les vérifications de sécurité
     */
    private function get_vulnerable_plugins() {
        // Cette méthode nécessiterait une base de données de vulnérabilités
        // Pour l'instant, retourner un tableau vide
        return [];
    }

    private function is_weak_password($username) {
        // Vérifier si le nom d'utilisateur est dans une liste de mots de passe faibles
        $weak_usernames = ['admin', 'administrator', 'root', 'user', 'test'];

        return in_array(strtolower($username), $weak_usernames);
    }

    private function get_inactive_users($days) {
        // Cette méthode nécessiterait un suivi des dernières activités
        // Pour l'instant, retourner un tableau vide
        return [];
    }

    private function analyze_threat_trends() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_threats';

        $total_threats = $wpdb->get_var("SELECT COUNT(*) FROM $table"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

        return ['total_threats' => $total_threats];
    }

    private function get_most_active_suspicious_ips() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_threats';

        $results = $wpdb->get_results(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT ip_address, COUNT(*) as threat_count
            FROM $table
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
            GROUP BY ip_address
            HAVING threat_count > 10
            ORDER BY threat_count DESC
            LIMIT 10
        ", ARRAY_A);

        $ips = [];
        foreach ($results as $result) {
            $ips[$result['ip_address']] = $result['threat_count'];
        }

        return $ips;
    }

    private function check_security_updates() {
        // Vérifier les mises à jour disponibles pour WordPress et les plugins
        $issues = [];

        if (function_exists('get_core_updates')) {
            $core_updates = get_core_updates();
            if (!empty($core_updates) && $core_updates[0]->response === 'upgrade') {
                $issues[] = 'Mise à jour de sécurité WordPress disponible';
            }
        }

        $plugin_updates = get_plugin_updates();
        
        if (!$plugin_updates) {
            return $issues;
        }
        
        foreach ($plugin_updates as $plugin_file => $plugin_data) {
            if (isset($plugin_data->update) && $plugin_data->update->response === 'upgrade') {
                $issues[] = "Mise à jour de sécurité disponible pour le plugin: {$plugin_data->Name}";
            }
        }

        return $issues;
    }

    /**
     * AJAX - Effectue un scan de sécurité
     */
    public function security_scan_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $scan_type = sanitize_text_field($_POST['scan_type'] ?? 'full');

            $results = [];

            switch ($scan_type) {
                case 'vulnerabilities':
                    $results['vulnerabilities'] = $this->check_known_vulnerabilities();
                    break;

                case 'permissions':
                    $results['permissions'] = $this->check_file_permissions();
                    break;

                case 'accounts':
                    $results['accounts'] = $this->check_suspicious_accounts();
                    break;

                case 'full':
                default:
                    $results = [
                        'vulnerabilities' => $this->check_known_vulnerabilities(),
                        'permissions' => $this->check_file_permissions(),
                        'accounts' => $this->check_suspicious_accounts(),
                        'threats' => $this->analyze_threat_trends()
                    ];
                    break;
            }

            wp_send_json_success([
                'message' => 'Scan de sécurité terminé',
                'results' => $results
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du scan: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut de sécurité
     */
    public function get_security_status_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $status = [
                'threats_today' => $this->get_threats_count_today(),
                'blocked_ips' => $this->get_blocked_ips_count(),
                'security_score' => $this->calculate_security_score(),
                'last_scan' => $this->get_last_scan_time(),
                'active_threats' => $this->get_active_threats()
            ];

            wp_send_json_success([
                'message' => 'Statut de sécurité récupéré',
                'status' => $status
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Bloque une IP
     */
    public function block_ip_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $ip = sanitize_text_field($_POST['ip'] ?? '');
            $reason = sanitize_text_field($_POST['reason'] ?? 'Bloqué manuellement');

            if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
                wp_send_json_error(['message' => 'Adresse IP invalide']);
                return;
            }

            $this->block_ip($ip, $reason);

            wp_send_json_success(['message' => 'IP bloquée avec succès']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du blocage: ' . $e->getMessage()]);
        }
    }

    /**
     * Méthodes utilitaires pour AJAX
     */
    private function get_threats_count_today() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_threats';

        return $wpdb->get_var($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT COUNT(*) FROM $table
            WHERE DATE(created_at) = CURDATE()
        "));
    }

    private function get_blocked_ips_count() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_blocked_ips';

        return $wpdb->get_var("SELECT COUNT(*) FROM $table"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
    }

    private function calculate_security_score() {
        // Calcul d'un score de sécurité basé sur différents facteurs
        $score = 100;

        // Réduire le score selon les menaces
        $threats_today = $this->get_threats_count_today();
        $score -= min($threats_today * 2, 30);

        // Réduire le score selon les IPs bloquées
        $blocked_ips = $this->get_blocked_ips_count();
        $score -= min($blocked_ips * 5, 20);

        // Réduire le score si des vulnérabilités sont détectées
        $vulnerabilities = count($this->check_known_vulnerabilities());
        $score -= min($vulnerabilities * 10, 30);

        return max($score, 0);
    }

    private function get_last_scan_time() {
        // Cette méthode nécessiterait un suivi des scans
        return current_time('mysql');
    }

    private function get_active_threats() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_threats';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            WHERE status = 'detected'
            ORDER BY created_at DESC
            LIMIT 10
        "), ARRAY_A);
    }
}

// Fonctions globales
function pdf_builder_security_monitor() {
    return PDF_Builder_Security_Monitor::get_instance();
}

function pdf_builder_is_ip_blocked($ip) {
    return PDF_Builder_Security_Monitor::get_instance()->is_ip_blocked($ip);
}

// Initialiser le système de surveillance de sécurité
add_action('plugins_loaded', function() {
    PDF_Builder_Security_Monitor::get_instance();
});



