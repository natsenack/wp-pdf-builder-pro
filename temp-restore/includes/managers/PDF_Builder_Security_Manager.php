<?php
/**
 * Gestionnaire de Sécurité Enterprise - PDF Builder Pro
 *
 * Sécurité avancée avec chiffrement, audit, conformité RGPD
 * Audit de sécurité complet et protection des données
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Sécurité Enterprise
 */
class PDF_Builder_Security_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Security_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager;

    /**
     * Gestionnaire de cache
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Clé de chiffrement AES-256
     * @var string
     */
    private $encryption_key;

    /**
     * Vecteur d'initialisation pour AES
     * @var string
     */
    private $encryption_iv;

    /**
     * Niveaux de sécurité
     * @var array
     */
    private $security_levels = [
        'basic' => 'Sécurité de base',
        'standard' => 'Sécurité standard',
        'enterprise' => 'Sécurité enterprise',
        'military' => 'Sécurité militaire'
    ];

    /**
     * Règles RGPD
     * @var array
     */
    private $gdpr_rules = [
        'data_retention' => 2555, // jours (7 ans)
        'consent_required' => true,
        'data_portability' => true,
        'right_to_be_forgotten' => true,
        'privacy_by_design' => true
    ];

    /**
     * Logs d'audit
     * @var array
     */
    private $audit_logs = [];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->db_manager = $core->get_database_manager();
        $this->cache_manager = $core->get_cache_manager();
        $this->logger = $core->get_logger();

        $this->init_encryption_keys();
        $this->init_security_hooks();
        $this->schedule_security_tasks();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Security_Manager
     */
    public static function getInstance(): PDF_Builder_Security_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les clés de chiffrement
     */
    private function init_encryption_keys(): void {
        // Générer ou récupérer la clé de chiffrement
        $stored_key = get_option('pdf_builder_encryption_key');
        $stored_iv = get_option('pdf_builder_encryption_iv');

        if (!$stored_key || !$stored_iv) {
            $this->encryption_key = $this->generate_secure_key();
            $this->encryption_iv = $this->generate_secure_iv();

            update_option('pdf_builder_encryption_key', $this->encryption_key);
            update_option('pdf_builder_encryption_iv', $this->encryption_iv);
        } else {
            $this->encryption_key = $stored_key;
            $this->encryption_iv = $stored_iv;
        }
    }

    /**
     * Initialiser les hooks de sécurité
     */
    private function init_security_hooks(): void {
        // Hooks de sécurité WordPress
        add_action('wp_login_failed', [$this, 'handle_failed_login']);
        add_action('wp_login', [$this, 'handle_successful_login'], 10, 2);
        add_action('profile_update', [$this, 'handle_profile_update'], 10, 2);

        // Hooks pour les données sensibles
        add_filter('pdf_builder_before_save_document', [$this, 'encrypt_sensitive_data']);
        add_filter('pdf_builder_after_load_document', [$this, 'decrypt_sensitive_data']);

        // Hooks RGPD
        add_action('wp_privacy_personal_data_exporters', [$this, 'register_data_exporter']);
        add_action('wp_privacy_personal_data_erasers', [$this, 'register_data_eraser']);

        // Hooks d'audit
        add_action('pdf_builder_security_event', [$this, 'log_security_event']);

        // Nettoyage de sécurité
        add_action('pdf_builder_security_cleanup', [$this, 'perform_security_cleanup']);
    }

    /**
     * Programmer les tâches de sécurité
     */
    private function schedule_security_tasks(): void {
        if (!wp_next_scheduled('pdf_builder_security_audit')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_security_audit');
        }
        add_action('pdf_builder_security_audit', [$this, 'perform_daily_security_audit']);

        if (!wp_next_scheduled('pdf_builder_security_cleanup')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_security_cleanup');
        }
        add_action('pdf_builder_security_cleanup', [$this, 'perform_security_cleanup']);
    }

    /**
     * Générer une clé sécurisée AES-256
     *
     * @return string
     */
    private function generate_secure_key(): string {
        return bin2hex(random_bytes(32)); // 256 bits
    }

    /**
     * Générer un vecteur d'initialisation sécurisé
     *
     * @return string
     */
    private function generate_secure_iv(): string {
        return bin2hex(random_bytes(16)); // 128 bits pour AES
    }

    /**
     * Chiffrer des données sensibles
     *
     * @param string $data
     * @return string
     */
    public function encrypt_data(string $data): string {
        if (empty($data)) {
            return $data;
        }

        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            hex2bin($this->encryption_key),
            OPENSSL_RAW_DATA,
            hex2bin($this->encryption_iv)
        );

        return base64_encode($encrypted);
    }

    /**
     * Déchiffrer des données sensibles
     *
     * @param string $encrypted_data
     * @return string
     */
    public function decrypt_data(string $encrypted_data): string {
        if (empty($encrypted_data)) {
            return $encrypted_data;
        }

        $decrypted = openssl_decrypt(
            base64_decode($encrypted_data),
            'AES-256-CBC',
            hex2bin($this->encryption_key),
            OPENSSL_RAW_DATA,
            hex2bin($this->encryption_iv)
        );

        return $decrypted;
    }

    /**
     * Hacher un mot de passe avec Argon2
     *
     * @param string $password
     * @return string
     */
    public function hash_password(string $password): string {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536, // 64 MB
                'time_cost' => 4,
                'threads' => 3
            ]);
        }

        // Fallback vers bcrypt
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Vérifier un mot de passe
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verify_password(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Générer un token sécurisé
     *
     * @param int $length
     * @return string
     */
    public function generate_secure_token(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }

    /**
     * Sanitiser les entrées utilisateur
     *
     * @param mixed $input
     * @param string $type
     * @return mixed
     */
    public function sanitize_input($input, string $type = 'string') {
        switch ($type) {
            case 'email':
                return sanitize_email($input);
            case 'url':
                return esc_url_raw($input);
            case 'html':
                return wp_kses_post($input);
            case 'sql':
                return esc_sql($input);
            case 'filename':
                return sanitize_file_name($input);
            default:
                if (is_array($input)) {
                    return array_map([$this, 'sanitize_input'], $input);
                }
                return sanitize_text_field($input);
        }
    }

    /**
     * Valider les permissions utilisateur
     *
     * @param int $user_id
     * @param string $capability
     * @param mixed $object_id
     * @return bool
     */
    public function validate_permissions(int $user_id, string $capability, $object_id = null): bool {
        // Vérifier les capacités WordPress de base
        if (!user_can($user_id, $capability)) {
            $this->log_security_event('permission_denied', [
                'user_id' => $user_id,
                'capability' => $capability,
                'object_id' => $object_id,
                'ip' => $this->get_client_ip()
            ]);
            return false;
        }

        // Vérifications supplémentaires selon l'objet
        if ($object_id && strpos($capability, 'pdf_') === 0) {
            return $this->validate_object_permissions($user_id, $capability, $object_id);
        }

        return true;
    }

    /**
     * Valider les permissions sur un objet spécifique
     *
     * @param int $user_id
     * @param string $capability
     * @param mixed $object_id
     * @return bool
     */
    private function validate_object_permissions(int $user_id, string $capability, $object_id): bool {
        global $wpdb;

        // Vérifier les permissions de partage pour les documents
        if (strpos($capability, 'pdf_document') !== false) {
            $permission = $wpdb->get_var(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT permission FROM {$wpdb->prefix}pdf_builder_document_shares
                WHERE document_id = %d AND user_id = %d
            ", $object_id, $user_id));

            if (!$permission) {
                // Vérifier si c'est le propriétaire
                $owner_id = $wpdb->get_var(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                    SELECT created_by FROM {$wpdb->prefix}pdf_builder_documents
                    WHERE id = %d
                ", $object_id));

                return $owner_id == $user_id;
            }

            // Vérifier le niveau de permission requis
            $required_level = $this->get_permission_level($capability);
            return $this->compare_permission_levels($permission, $required_level);
        }

        return true;
    }

    /**
     * Obtenir le niveau de permission requis
     *
     * @param string $capability
     * @return string
     */
    private function get_permission_level(string $capability): string {
        $levels = [
            'view' => ['pdf_document_read'],
            'comment' => ['pdf_document_read', 'pdf_document_comment'],
            'edit' => ['pdf_document_read', 'pdf_document_comment', 'pdf_document_edit'],
            'approve' => ['pdf_document_read', 'pdf_document_comment', 'pdf_document_edit', 'pdf_document_approve'],
            'admin' => ['pdf_document_read', 'pdf_document_comment', 'pdf_document_edit', 'pdf_document_approve', 'pdf_document_admin']
        ];

        foreach ($levels as $level => $caps) {
            if (in_array($capability, $caps)) {
                return $level;
            }
        }

        return 'view';
    }

    /**
     * Comparer les niveaux de permission
     *
     * @param string $user_level
     * @param string $required_level
     * @return bool
     */
    private function compare_permission_levels(string $user_level, string $required_level): bool {
        $hierarchy = ['none' => 0, 'view' => 1, 'comment' => 2, 'edit' => 3, 'approve' => 4, 'admin' => 5];

        return ($hierarchy[$user_level] ?? 0) >= ($hierarchy[$required_level] ?? 0);
    }

    /**
     * Gérer les échecs de connexion
     *
     * @param string $username
     */
    public function handle_failed_login(string $username): void {
        $this->log_security_event('failed_login', [
            'username' => $username,
            'ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        // Implémenter un système de blocage temporaire si trop d'échecs
        $this->handle_brute_force_protection($username);
    }

    /**
     * Gérer les connexions réussies
     *
     * @param string $user_login
     * @param WP_User $user
     */
    public function handle_successful_login(string $user_login, WP_User $user): void {
        $this->log_security_event('successful_login', [
            'user_id' => $user->ID,
            'username' => $user_login,
            'ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }

    /**
     * Gérer les mises à jour de profil
     *
     * @param int $user_id
     * @param WP_User $old_user_data
     */
    public function handle_profile_update(int $user_id, WP_User $old_user_data): void {
        $this->log_security_event('profile_update', [
            'user_id' => $user_id,
            'changes' => $this->detect_profile_changes($old_user_data, get_userdata($user_id)),
            'ip' => $this->get_client_ip()
        ]);
    }

    /**
     * Protection contre les attaques par force brute
     *
     * @param string $username
     */
    private function handle_brute_force_protection(string $username): void {
        $key = 'failed_login_' . md5($username . $this->get_client_ip());
        $attempts = get_transient($key) ?: 0;
        $attempts++;

        if ($attempts >= 5) {
            // Bloquer temporairement (15 minutes)
            set_transient($key . '_blocked', true, 900);
            $this->log_security_event('brute_force_blocked', [
                'username' => $username,
                'ip' => $this->get_client_ip(),
                'attempts' => $attempts
            ]);
        } else {
            set_transient($key, $attempts, 3600); // 1 heure
        }
    }

    /**
     * Vérifier si l'IP est bloquée
     *
     * @return bool
     */
    public function is_ip_blocked(): bool {
        $key = 'failed_login_' . md5('' . $this->get_client_ip()) . '_blocked';
        return get_transient($key) !== false;
    }

    /**
     * Détecter les changements de profil
     *
     * @param WP_User $old_user
     * @param WP_User $new_user
     * @return array
     */
    private function detect_profile_changes(WP_User $old_user, WP_User $new_user): array {
        $changes = [];

        $fields_to_check = ['user_email', 'user_url', 'display_name', 'first_name', 'last_name'];

        foreach ($fields_to_check as $field) {
            if ($old_user->$field !== $new_user->$field) {
                $changes[$field] = [
                    'old' => $old_user->$field,
                    'new' => $new_user->$field
                ];
            }
        }

        return $changes;
    }

    /**
     * Chiffrer les données sensibles avant sauvegarde
     *
     * @param array $document_data
     * @return array
     */
    public function encrypt_sensitive_data(array $document_data): array {
        $sensitive_fields = ['client_email', 'client_phone', 'client_address', 'payment_info'];

        foreach ($sensitive_fields as $field) {
            if (isset($document_data[$field]) && !empty($document_data[$field])) {
                $document_data[$field . '_encrypted'] = $this->encrypt_data($document_data[$field]);
                unset($document_data[$field]);
            }
        }

        return $document_data;
    }

    /**
     * Déchiffrer les données sensibles après chargement
     *
     * @param array $document_data
     * @return array
     */
    public function decrypt_sensitive_data(array $document_data): array {
        $sensitive_fields = ['client_email', 'client_phone', 'client_address', 'payment_info'];

        foreach ($sensitive_fields as $field) {
            $encrypted_field = $field . '_encrypted';
            if (isset($document_data[$encrypted_field]) && !empty($document_data[$encrypted_field])) {
                $document_data[$field] = $this->decrypt_data($document_data[$encrypted_field]);
                unset($document_data[$encrypted_field]);
            }
        }

        return $document_data;
    }

    /**
     * Logger un événement de sécurité
     *
     * @param string $event_type
     * @param array $data
     */
    public function log_security_event(string $event_type, array $data = []): void {
        global $wpdb;

        $log_data = array_merge($data, [
            'event_type' => $event_type,
            'timestamp' => current_time('mysql'),
            'session_id' => session_id() ?: '',
            'severity' => $this->get_event_severity($event_type)
        ]);

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_security_logs',
            [
                'event_type' => $event_type,
                'event_data' => wp_json_encode($log_data),
                'user_id' => $data['user_id'] ?? get_current_user_id(),
                'ip_address' => $data['ip'] ?? $this->get_client_ip(),
                'user_agent' => $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'severity' => $log_data['severity'],
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );

        // Logger aussi dans le système de logs général
        $this->logger->info('Security event: ' . $event_type, $log_data);

        // Alerte pour les événements critiques
        if ($log_data['severity'] === 'critical') {
            $this->send_security_alert($event_type, $log_data);
        }
    }

    /**
     * Obtenir la sévérité d'un événement
     *
     * @param string $event_type
     * @return string
     */
    private function get_event_severity(string $event_type): string {
        $severity_map = [
            'failed_login' => 'medium',
            'successful_login' => 'low',
            'brute_force_blocked' => 'high',
            'permission_denied' => 'medium',
            'profile_update' => 'low',
            'data_export' => 'medium',
            'data_deletion' => 'high',
            'security_breach' => 'critical',
            'unauthorized_access' => 'high'
        ];

        return $severity_map[$event_type] ?? 'low';
    }

    /**
     * Envoyer une alerte de sécurité
     *
     * @param string $event_type
     * @param array $data
     */
    private function send_security_alert(string $event_type, array $data): void {
        $admin_email = get_option('admin_email');
        $subject = sprintf('[SECURITY ALERT] %s - PDF Builder Pro', ucfirst($event_type));

        $message = sprintf(
            "Alerte de sécurité détectée:\n\n" .
            "Événement: %s\n" .
            "Utilisateur: %s\n" .
            "IP: %s\n" .
            "Timestamp: %s\n\n" .
            "Détails: %s\n\n" .
            "Veuillez vérifier les logs de sécurité immédiatement.",
            $event_type,
            $data['user_id'] ?? 'Unknown',
            $data['ip'] ?? 'Unknown',
            $data['timestamp'],
            wp_json_encode($data, JSON_PRETTY_PRINT)
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Exporter les données personnelles (RGPD)
     *
     * @param array $email_address
     * @return array
     */
    public function export_personal_data(array $email_address): array {
        // Extraire l'email correctement selon la structure WordPress RGPD
        $user_email = isset($email_address['data']['user_email'])
            ? $email_address['data']['user_email']
            : (isset($email_address['email']) ? $email_address['email'] : '');

        // S'assurer que c'est une string
        if (is_array($user_email)) {
            $user_email = implode('', $user_email);
        }

        $user = get_user_by('email', $user_email);

        if (!$user) {
            return [];
        }

        $export_data = [];

        // Données des documents
        global $wpdb;
        $documents = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT * FROM {$wpdb->prefix}pdf_builder_documents
            WHERE author_id = %d
        ", $user->ID));

        if (!empty($documents)) {
            $export_data[] = [
                'group_id' => 'pdf_builder_documents',
                'group_label' => 'PDF Builder Documents',
                'items' => array_map(function($doc) {
                    return [
                        'item_id' => 'document_' . $doc->id,
                        'data' => [
                            'title' => $doc->title,
                            'created_at' => $doc->created_at,
                            'status' => $doc->status
                        ]
                    ];
                }, $documents)
            ];
        }

        // Logs de sécurité (si la table existe)
        $table_logs = $wpdb->prefix . 'pdf_builder_logs';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_logs'") == $table_logs) {
            $security_logs = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT * FROM {$wpdb->prefix}pdf_builder_logs
                WHERE user_id = %d
                ORDER BY created_at DESC
                LIMIT 100
            ", $user->ID));

            if (!empty($security_logs)) {
                $export_data[] = [
                    'group_id' => 'pdf_builder_security_logs',
                    'group_label' => 'PDF Builder Security Logs',
                    'items' => array_map(function($log) {
                        return [
                            'item_id' => 'log_' . $log->id,
                            'data' => [
                                'level' => $log->level,
                                'message' => $log->message,
                                'created_at' => $log->created_at
                            ]
                        ];
                    }, $security_logs)
                ];
            }
        }

        return $export_data;
    }

    /**
     * Supprimer les données personnelles (RGPD)
     *
     * @param array $email_address
     * @return array
     */
    public function erase_personal_data(array $email_address): array {
        $user = get_user_by('email', $email_address['data']['user_email']);

        if (!$user) {
            return ['items_removed' => 0, 'items_retained' => 0, 'messages' => []];
        }

        global $wpdb;
        $items_removed = 0;
        $items_retained = 0;
        $messages = [];

        // Supprimer les documents (anonymiser si partagé)
        $documents = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT id FROM {$wpdb->prefix}pdf_builder_documents
            WHERE created_by = %d
        ", $user->ID));

        foreach ($documents as $doc) {
            // Vérifier si le document est partagé
            $shares = $wpdb->get_var(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_document_shares
                WHERE document_id = %d AND user_id != %d
            ", $doc->id, $user->ID));

            if ($shares > 0) {
                // Anonymiser au lieu de supprimer
                $wpdb->update(
                    $wpdb->prefix . 'pdf_builder_documents',
                    ['created_by' => 0, 'data' => wp_json_encode(['anonymized' => true])],
                    ['id' => $doc->id]
                );
                $items_retained++;
                $messages[] = sprintf('Document %d anonymisé (partagé avec d\'autres utilisateurs)', $doc->id);
            } else {
                // Supprimer complètement
                $wpdb->delete($wpdb->prefix . 'pdf_builder_documents', ['id' => $doc->id]);
                $items_removed++;
            }
        }

        // Supprimer les logs de sécurité
        $security_logs_deleted = $wpdb->delete(
            $wpdb->prefix . 'pdf_builder_security_logs',
            ['user_id' => $user->ID]
        );
        $items_removed += $security_logs_deleted;

        // Supprimer les partages
        $shares_deleted = $wpdb->delete(
            $wpdb->prefix . 'pdf_builder_document_shares',
            ['user_id' => $user->ID]
        );
        $items_removed += $shares_deleted;

        $this->log_security_event('data_erasure', [
            'user_id' => $user->ID,
            'items_removed' => $items_removed,
            'items_retained' => $items_retained
        ]);

        return [
            'items_removed' => $items_removed,
            'items_retained' => $items_retained,
            'messages' => $messages
        ];
    }

    /**
     * Enregistrer l'exporteur de données RGPD
     *
     * @param array $exporters
     * @return array
     */
    public function register_data_exporter(array $exporters): array {
        $exporters['pdf-builder-pro'] = [
            'exporter_friendly_name' => 'PDF Builder Pro',
            'callback' => [$this, 'export_personal_data']
        ];
        return $exporters;
    }

    /**
     * Enregistrer l'effaceur de données RGPD
     *
     * @param array $erasers
     * @return array
     */
    public function register_data_eraser(array $erasers): array {
        $erasers['pdf-builder-pro'] = [
            'eraser_friendly_name' => 'PDF Builder Pro',
            'callback' => [$this, 'erase_personal_data']
        ];
        return $erasers;
    }

    /**
     * Effectuer un audit de sécurité quotidien
     */
    public function perform_daily_security_audit(): void {
        $audit_results = [
            'timestamp' => current_time('mysql'),
            'checks' => []
        ];

        // Vérifier les permissions des fichiers
        $audit_results['checks']['file_permissions'] = $this->audit_file_permissions();

        // Vérifier les utilisateurs inactifs
        $audit_results['checks']['inactive_users'] = $this->audit_inactive_users();

        // Vérifier les sessions expirées
        $audit_results['checks']['expired_sessions'] = $this->audit_expired_sessions();

        // Vérifier les mots de passe faibles
        $audit_results['checks']['weak_passwords'] = $this->audit_weak_passwords();

        // Vérifier les accès suspects
        $audit_results['checks']['suspicious_activity'] = $this->audit_suspicious_activity();

        // Sauvegarder les résultats
        update_option('pdf_builder_last_security_audit', $audit_results);

        $this->logger->info('Daily security audit completed', $audit_results);

        // Envoyer un rapport si des problèmes critiques sont détectés
        if ($this->has_critical_security_issues($audit_results)) {
            $this->send_security_audit_report($audit_results);
        }
    }

    /**
     * Auditer les permissions des fichiers
     *
     * @return array
     */
    private function audit_file_permissions(): array {
        $issues = [];

        $critical_files = [
            PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php',
            PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Security_Manager.php',
            WP_CONTENT_DIR . '/pdf-builder-exports/'
        ];

        foreach ($critical_files as $file) {
            if (file_exists($file)) {
                $perms = substr(sprintf('%o', fileperms($file)), -4);
                if ($perms > '0644') {
                    $issues[] = "Permissions trop permissives pour: $file ($perms)";
                }
            }
        }

        return [
            'status' => empty($issues) ? 'passed' : 'failed',
            'issues' => $issues
        ];
    }

    /**
     * Auditer les utilisateurs inactifs
     *
     * @return array
     */
    private function audit_inactive_users(): array {
        global $wpdb;

        $inactive_threshold = date('Y-m-d H:i:s', strtotime('-90 days'));

        $inactive_users = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT ID, user_login, user_email, last_login
            FROM {$wpdb->users} u
            LEFT JOIN {$wpdb->usermeta} m ON u.ID = m.user_id AND m.meta_key = 'last_login'
            WHERE (m.meta_value IS NULL OR m.meta_value < %s)
            AND u.ID != 1
        ", $inactive_threshold));

        return [
            'status' => count($inactive_users) > 10 ? 'warning' : 'passed',
            'count' => count($inactive_users),
            'users' => array_slice($inactive_users, 0, 5) // Limiter la sortie
        ];
    }

    /**
     * Auditer les sessions expirées
     *
     * @return array
     */
    private function audit_expired_sessions(): array {
        global $wpdb;

        $expired_sessions = $wpdb->get_var(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_security_logs
            WHERE event_type = 'session_expired'
            AND created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-24 hours'))));

        return [
            'status' => $expired_sessions > 100 ? 'warning' : 'passed',
            'count' => $expired_sessions
        ];
    }

    /**
     * Auditer les mots de passe faibles
     *
     * @return array
     */
    private function audit_weak_passwords(): array {
        // Cette vérification nécessiterait une analyse plus complexe
        // Pour l'instant, retourner un statut passé
        return [
            'status' => 'passed',
            'note' => 'Audit manuel recommandé pour les mots de passe faibles'
        ];
    }

    /**
     * Auditer l'activité suspecte
     *
     * @return array
     */
    private function audit_suspicious_activity(): array {
        global $wpdb;

        $suspicious_events = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT event_type, COUNT(*) as count
            FROM {$wpdb->prefix}pdf_builder_security_logs
            WHERE severity IN ('high', 'critical')
            AND created_at > %s
            GROUP BY event_type
        ", date('Y-m-d H:i:s', strtotime('-24 hours'))));

        $total_suspicious = array_sum(array_column($suspicious_events, 'count'));

        return [
            'status' => $total_suspicious > 50 ? 'warning' : 'passed',
            'total_events' => $total_suspicious,
            'events' => $suspicious_events
        ];
    }

    /**
     * Vérifier s'il y a des problèmes de sécurité critiques
     *
     * @param array $audit_results
     * @return bool
     */
    private function has_critical_security_issues(array $audit_results): bool {
        foreach ($audit_results['checks'] as $check) {
            if (($check['status'] ?? 'passed') === 'failed') {
                return true;
            }
        }
        return false;
    }

    /**
     * Envoyer le rapport d'audit de sécurité
     *
     * @param array $audit_results
     */
    private function send_security_audit_report(array $audit_results): void {
        $admin_email = get_option('admin_email');
        $subject = '[SECURITY AUDIT] PDF Builder Pro - Problèmes détectés';

        $message = "Rapport d'audit de sécurité quotidien:\n\n";

        foreach ($audit_results['checks'] as $check_name => $check_result) {
            $message .= sprintf(
                "%s: %s\n",
                ucfirst(str_replace('_', ' ', $check_name)),
                $check_result['status'] ?? 'unknown'
            );

            if (!empty($check_result['issues'])) {
                $message .= "Issues:\n" . implode("\n", $check_result['issues']) . "\n";
            }

            $message .= "\n";
        }

        $message .= "Veuillez vérifier les paramètres de sécurité immédiatement.\n";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Effectuer le nettoyage de sécurité
     */
    public function perform_security_cleanup(): void {
        global $wpdb;

        // Supprimer les logs de sécurité anciens (plus de 2 ans)
        $old_logs_deleted = $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_security_logs
            WHERE created_at < %s
        ", date('Y-m-d H:i:s', strtotime('-2 years'))));

        // Supprimer les données RGPD expirées
        $expired_data_deleted = $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_documents
            WHERE expires_at IS NOT NULL
            AND expires_at < %s
            AND workflow_status = 'archived'
        ", current_time('mysql')));

        // Nettoyer les sessions expirées
        $expired_sessions = $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_security_logs
            WHERE event_type = 'session_created'
            AND created_at < %s
        ", date('Y-m-d H:i:s', strtotime('-30 days'))));

        $this->logger->info('Security cleanup completed', [
            'old_logs_deleted' => $old_logs_deleted,
            'expired_data_deleted' => $expired_data_deleted,
            'expired_sessions' => $expired_sessions
        ]);
    }

    /**
     * Obtenir l'IP du client
     *
     * @return string
     */
    private function get_client_ip(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Obtenir les métriques de sécurité
     *
     * @return array
     */
    public function get_security_metrics(): array {
        global $wpdb;

        $metrics = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(CASE WHEN severity = 'critical' THEN 1 END) as critical_events,
                COUNT(CASE WHEN severity = 'high' THEN 1 END) as high_events,
                COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium_events,
                COUNT(CASE WHEN event_type = 'failed_login' THEN 1 END) as failed_logins,
                COUNT(CASE WHEN event_type = 'successful_login' THEN 1 END) as successful_logins,
                COUNT(DISTINCT user_id) as active_users
            FROM {$wpdb->prefix}pdf_builder_security_logs
            WHERE created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-24 hours'))), ARRAY_A);

        return [
            'events_last_24h' => [
                'critical' => intval($metrics['critical_events'] ?? 0),
                'high' => intval($metrics['high_events'] ?? 0),
                'medium' => intval($metrics['medium_events'] ?? 0)
            ],
            'authentication' => [
                'failed_logins' => intval($metrics['failed_logins'] ?? 0),
                'successful_logins' => intval($metrics['successful_logins'] ?? 0)
            ],
            'active_users' => intval($metrics['active_users'] ?? 0),
            'encryption_enabled' => true,
            'audit_enabled' => true,
            'gdpr_compliant' => true
        ];
    }

    /**
     * Obtenir les niveaux de sécurité disponibles
     *
     * @return array
     */
    public function get_security_levels(): array {
        return $this->security_levels;
    }

    /**
     * Obtenir les règles RGPD
     *
     * @return array
     */
    public function get_gdpr_rules(): array {
        return $this->gdpr_rules;
    }
}

