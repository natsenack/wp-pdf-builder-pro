<?php
/**
 * Gestionnaire de Base de Données - PDF Builder Pro
 *
 * Gestion optimisée de la base de données avec :
 * - Requêtes préparées
 * - Cache des requêtes
 * - Optimisation automatique
 * - Gestion des transactions
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Base de Données
 */
class PDF_Builder_Database_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Database_Manager
     */
    private static $instance = null;

    /**
     * Instance WordPress DB
     * @var wpdb
     */
    private $wpdb;

    /**
     * Préfixe des tables
     * @var string
     */
    private $table_prefix;

    /**
     * Cache des requêtes
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Configuration
     * @var PDF_Builder_Config_Manager
     */
    private $config;

    /**
     * Métriques de performance
     * @var array
     */
    private $metrics = [
        'queries' => 0,
        'cached_queries' => 0,
        'slow_queries' => 0,
        'errors' => 0
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb ?? null;
        $this->table_prefix = $this->wpdb ? $this->wpdb->prefix . 'pdf_builder_' : 'pdf_builder_';
        $this->cache_manager = PDF_Builder_Cache_Manager::getInstance();
        $this->logger = PDF_Builder_Logger::getInstance();
        $this->config = PDF_Builder_Config_Manager::getInstance();

        // Hook pour intercepter les erreurs wpdb::prepare
        add_action('doing_it_wrong_run', [$this, 'log_wpdb_prepare_errors'], 10, 3);
    }

    /**
     * Logger les erreurs wpdb::prepare pour debug
     */
    public function log_wpdb_prepare_errors($function, $message, $version) {
        if (strpos($function, 'wpdb::prepare') !== false) {
            error_log('PDF Builder Debug - wpdb::prepare error: ' . $message);
            error_log('PDF Builder Debug - Backtrace: ' . wp_debug_backtrace_summary());
        }
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Database_Manager
     */
    public static function getInstance(): PDF_Builder_Database_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Créer les tables de base de données
     *
     * @return void
     */
    public function create_tables(): void {
        $charset_collate = $this->wpdb->get_charset_collate();

        $tables = [
            'templates' => "
                CREATE TABLE {$this->table_prefix}templates (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    name varchar(255) NOT NULL,
                    description text,
                    type varchar(50) NOT NULL DEFAULT 'pdf',
                    content longtext,
                    settings longtext,
                    status varchar(20) NOT NULL DEFAULT 'active',
                    category_id bigint(20) unsigned DEFAULT NULL,
                    author_id bigint(20) unsigned NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY type_status (type, status),
                    KEY author_id (author_id),
                    KEY category_id (category_id)
                ) $charset_collate;
            ",
            'documents' => "
                CREATE TABLE {$this->table_prefix}documents (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    template_id bigint(20) unsigned NOT NULL,
                    title varchar(255) NOT NULL,
                    data longtext,
                    file_path varchar(500),
                    file_size bigint(20) unsigned DEFAULT NULL,
                    status varchar(20) NOT NULL DEFAULT 'pending',
                    workflow_status varchar(20) NOT NULL DEFAULT 'draft',
                    author_id bigint(20) unsigned NOT NULL,
                    generated_at datetime DEFAULT NULL,
                    expires_at datetime DEFAULT NULL,
                    download_count int(11) DEFAULT 0,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY template_id (template_id),
                    KEY status (status),
                    KEY workflow_status (workflow_status),
                    KEY author_id (author_id),
                    KEY expires_at (expires_at)
                ) $charset_collate;
            ",
            'categories' => "
                CREATE TABLE {$this->table_prefix}categories (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    name varchar(255) NOT NULL,
                    description text,
                    parent_id bigint(20) unsigned DEFAULT NULL,
                    slug varchar(255) NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY slug (slug),
                    KEY parent_id (parent_id)
                ) $charset_collate;
            ",
            'logs' => "
                CREATE TABLE {$this->table_prefix}logs (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    level varchar(20) NOT NULL,
                    message text NOT NULL,
                    context longtext,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY level_created (level, created_at),
                    KEY user_id (user_id)
                ) $charset_collate;
            ",
            'cache' => "
                CREATE TABLE {$this->table_prefix}cache (
                    cache_key varchar(255) NOT NULL,
                    cache_value longtext,
                    expires_at datetime NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (cache_key),
                    KEY expires_at (expires_at)
                ) $charset_collate;
            ",
            'settings' => "
                CREATE TABLE {$this->table_prefix}settings (
                    setting_key varchar(255) NOT NULL,
                    setting_value longtext,
                    setting_type varchar(20) DEFAULT 'string',
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (setting_key)
                ) $charset_collate;
            ",
            'template_versions' => "
                CREATE TABLE {$this->table_prefix}template_versions (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    template_id bigint(20) unsigned NOT NULL,
                    version_number int(11) NOT NULL,
                    data longtext NOT NULL,
                    change_summary text,
                    created_by bigint(20) unsigned NOT NULL,
                    is_auto_save tinyint(1) DEFAULT 0,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY template_id (template_id),
                    KEY version_number (version_number),
                    KEY created_by (created_by),
                    KEY template_version (template_id, version_number)
                ) $charset_collate;
            ",
            'bulk_tasks' => "
                CREATE TABLE {$this->table_prefix}bulk_tasks (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    task_id varchar(100) NOT NULL,
                    task_type varchar(50) NOT NULL,
                    task_data longtext NOT NULL,
                    status varchar(30) NOT NULL DEFAULT 'pending',
                    total_items int(11) NOT NULL DEFAULT 0,
                    processed_items int(11) NOT NULL DEFAULT 0,
                    failed_items int(11) NOT NULL DEFAULT 0,
                    created_by bigint(20) unsigned NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY task_id (task_id),
                    KEY status (status),
                    KEY task_type (task_type),
                    KEY created_by (created_by),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'export_logs' => "
                CREATE TABLE {$this->table_prefix}export_logs (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    format varchar(10) NOT NULL,
                    file_size bigint(20) unsigned DEFAULT NULL,
                    export_time decimal(5,2) DEFAULT NULL,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY format (format),
                    KEY user_id (user_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'analytics_reports' => "
                CREATE TABLE {$this->table_prefix}analytics_reports (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    report_type varchar(20) NOT NULL,
                    report_data longtext NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY report_type (report_type),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'document_shares' => "
                CREATE TABLE {$this->table_prefix}document_shares (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    permission_level varchar(20) NOT NULL DEFAULT 'view',
                    shared_by bigint(20) unsigned NOT NULL,
                    shared_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    last_accessed datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY document_user (document_id, user_id),
                    KEY document_id (document_id),
                    KEY user_id (user_id),
                    KEY permission_level (permission_level),
                    KEY shared_by (shared_by)
                ) $charset_collate;
            ",
            'comments' => "
                CREATE TABLE {$this->table_prefix}comments (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    comment text NOT NULL,
                    metadata longtext,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY user_id (user_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'document_versions' => "
                CREATE TABLE {$this->table_prefix}document_versions (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    version_number int(11) NOT NULL,
                    data longtext NOT NULL,
                    change_summary text,
                    created_by bigint(20) unsigned NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY version_number (version_number),
                    KEY created_by (created_by),
                    UNIQUE KEY document_version (document_id, version_number)
                ) $charset_collate;
            ",
            'workflow_history' => "
                CREATE TABLE {$this->table_prefix}workflow_history (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    action varchar(50) NOT NULL,
                    old_value text,
                    new_value text,
                    comment text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY user_id (user_id),
                    KEY action (action),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'api_logs' => "
                CREATE TABLE {$this->table_prefix}api_logs (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    api_key_id varchar(100) DEFAULT NULL,
                    endpoint varchar(255) NOT NULL,
                    method varchar(10) NOT NULL,
                    response_code int(11) NOT NULL,
                    response_time decimal(5,2) DEFAULT NULL,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    request_data longtext,
                    response_data longtext,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY api_key_id (api_key_id),
                    KEY endpoint (endpoint),
                    KEY method (method),
                    KEY response_code (response_code),
                    KEY user_id (user_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'analytics_events' => "
                CREATE TABLE {$this->table_prefix}analytics_events (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_type varchar(50) NOT NULL,
                    event_data longtext,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    session_id varchar(100) DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY event_type (event_type),
                    KEY user_id (user_id),
                    KEY session_id (session_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'analytics_hourly' => "
                CREATE TABLE {$this->table_prefix}analytics_hourly (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_type varchar(50) NOT NULL,
                    period datetime NOT NULL,
                    count int(11) NOT NULL DEFAULT 0,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY event_period (event_type, period),
                    KEY event_type (event_type),
                    KEY period (period)
                ) $charset_collate;
            ",
            'webhooks' => "
                CREATE TABLE {$this->table_prefix}webhooks (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    webhook_id varchar(100) NOT NULL,
                    url varchar(500) NOT NULL,
                    events longtext NOT NULL,
                    secret varchar(255) NOT NULL,
                    active tinyint(1) NOT NULL DEFAULT 1,
                    failure_count int(11) NOT NULL DEFAULT 0,
                    last_triggered datetime DEFAULT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY webhook_id (webhook_id),
                    KEY active (active),
                    KEY created_at (created_at)
                ) $charset_collate;
            "
        ];

        // Only create tables if WordPress functions are available
        if (function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            foreach ($tables as $table_name => $sql) {
                dbDelta($sql);

                if ($this->wpdb->last_error) {
                    $this->logger->error('Failed to create table', [
                        'table' => $table_name,
                        'error' => $this->wpdb->last_error
                    ]);
                } else {
                    $this->logger->info('Table created successfully', ['table' => $table_name]);
                }
            }
        }

        // Mettre à jour la version de la base de données
        update_option('pdf_builder_db_version', PDF_BUILDER_VERSION);
    }

    /**
     * Créer uniquement les tables essentielles lors de l'activation
     *
     * @return void
     */
    public function create_essential_tables(): void {
        $charset_collate = $this->wpdb->get_charset_collate();

        $essential_tables = [
            'templates' => "
                CREATE TABLE {$this->table_prefix}templates (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    name varchar(255) NOT NULL,
                    description text,
                    type varchar(50) NOT NULL DEFAULT 'pdf',
                    content longtext,
                    settings longtext,
                    status varchar(20) NOT NULL DEFAULT 'active',
                    category_id bigint(20) unsigned DEFAULT NULL,
                    author_id bigint(20) unsigned NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY type_status (type, status),
                    KEY author_id (author_id),
                    KEY category_id (category_id)
                ) $charset_collate;
            ",
            'documents' => "
                CREATE TABLE {$this->table_prefix}documents (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    template_id bigint(20) unsigned NOT NULL,
                    title varchar(255) NOT NULL,
                    data longtext,
                    file_path varchar(500),
                    file_size bigint(20) unsigned DEFAULT NULL,
                    status varchar(20) NOT NULL DEFAULT 'pending',
                    workflow_status varchar(20) NOT NULL DEFAULT 'draft',
                    author_id bigint(20) unsigned NOT NULL,
                    generated_at datetime DEFAULT NULL,
                    expires_at datetime DEFAULT NULL,
                    download_count int(11) DEFAULT 0,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY template_id (template_id),
                    KEY status (status),
                    KEY workflow_status (workflow_status),
                    KEY author_id (author_id),
                    KEY expires_at (expires_at)
                ) $charset_collate;
            ",
            'settings' => "
                CREATE TABLE {$this->table_prefix}settings (
                    setting_key varchar(255) NOT NULL,
                    setting_value longtext,
                    setting_type varchar(20) DEFAULT 'string',
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (setting_key)
                ) $charset_collate;
            ",
            'logs' => "
                CREATE TABLE {$this->table_prefix}logs (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    level varchar(20) NOT NULL,
                    message text NOT NULL,
                    context longtext,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY level_created (level, created_at),
                    KEY user_id (user_id)
                ) $charset_collate;
            "
        ];

        if (function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            foreach ($essential_tables as $table_name => $sql) {
                // Vérifier si la table existe déjà
                $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_prefix}{$table_name}'") === $this->table_prefix . $table_name;

                if (!$table_exists) {
                    $this->logger->info('Creating essential table', ['table' => $table_name]);

                    // Essayer dbDelta d'abord
                    dbDelta($sql);

                    // Vérifier si la table a été créée
                    $table_created = $this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_prefix}{$table_name}'") === $this->table_prefix . $table_name;

                    if (!$table_created && !empty($this->wpdb->last_error)) {
                        $this->logger->error('dbDelta failed, trying direct query', [
                            'table' => $table_name,
                            'error' => $this->wpdb->last_error
                        ]);

                        // Essayer une requête directe
                        $this->wpdb->query($sql);

                        if ($this->wpdb->last_error) {
                            $this->logger->error('Direct query also failed', [
                                'table' => $table_name,
                                'error' => $this->wpdb->last_error
                            ]);
                        } else {
                            $this->logger->info('Essential table created with direct query', ['table' => $table_name]);
                        }
                    } elseif ($table_created) {
                        $this->logger->info('Essential table created successfully with dbDelta', ['table' => $table_name]);
                    }
                } else {
                    $this->logger->info('Essential table already exists', ['table' => $table_name]);
                }
            }
        }

        // Marquer que seules les tables essentielles ont été créées
        update_option('pdf_builder_db_version', PDF_BUILDER_VERSION . '_essential');

        // Vérifier que toutes les tables essentielles ont été créées
        $all_created = true;
        foreach (array_keys($essential_tables) as $table_name) {
            $table_full_name = $this->table_prefix . $table_name;
            if ($this->wpdb->get_var("SHOW TABLES LIKE '$table_full_name'") !== $table_full_name) {
                $this->logger->error('Essential table was not created', ['table' => $table_name]);
                $all_created = false;
            }
        }

        if ($all_created) {
            $this->logger->info('All essential tables created successfully');
        } else {
            $this->logger->error('Some essential tables failed to create');
        }
    }

    /**
     * Créer les tables restantes en arrière-plan
     *
     * @return void
     */
    public function create_remaining_tables(): void {
        $charset_collate = $this->wpdb->get_charset_collate();

        $remaining_tables = [
            'categories' => "
                CREATE TABLE {$this->table_prefix}categories (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    name varchar(255) NOT NULL,
                    description text,
                    parent_id bigint(20) unsigned DEFAULT NULL,
                    slug varchar(255) NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY slug (slug),
                    KEY parent_id (parent_id)
                ) $charset_collate;
            ",
            'cache' => "
                CREATE TABLE {$this->table_prefix}cache (
                    cache_key varchar(255) NOT NULL,
                    cache_value longtext,
                    expires_at datetime NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (cache_key),
                    KEY expires_at (expires_at)
                ) $charset_collate;
            ",
            'template_versions' => "
                CREATE TABLE {$this->table_prefix}template_versions (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    template_id bigint(20) unsigned NOT NULL,
                    version_number int(11) NOT NULL,
                    data longtext NOT NULL,
                    change_summary text,
                    created_by bigint(20) unsigned NOT NULL,
                    is_auto_save tinyint(1) DEFAULT 0,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY template_id (template_id),
                    KEY version_number (version_number),
                    KEY created_by (created_by),
                    KEY template_version (template_id, version_number)
                ) $charset_collate;
            ",
            'bulk_tasks' => "
                CREATE TABLE {$this->table_prefix}bulk_tasks (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    task_id varchar(100) NOT NULL,
                    task_type varchar(50) NOT NULL,
                    task_data longtext NOT NULL,
                    status varchar(30) NOT NULL DEFAULT 'pending',
                    total_items int(11) NOT NULL DEFAULT 0,
                    processed_items int(11) NOT NULL DEFAULT 0,
                    failed_items int(11) NOT NULL DEFAULT 0,
                    created_by bigint(20) unsigned NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY task_id (task_id),
                    KEY status (status),
                    KEY task_type (task_type),
                    KEY created_by (created_by),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'export_logs' => "
                CREATE TABLE {$this->table_prefix}export_logs (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    format varchar(10) NOT NULL,
                    file_size bigint(20) unsigned DEFAULT NULL,
                    export_time decimal(5,2) DEFAULT NULL,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY format (format),
                    KEY user_id (user_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'analytics_reports' => "
                CREATE TABLE {$this->table_prefix}analytics_reports (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    report_type varchar(20) NOT NULL,
                    report_data longtext NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY report_type (report_type),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'document_shares' => "
                CREATE TABLE {$this->table_prefix}document_shares (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    permission_level varchar(20) NOT NULL DEFAULT 'view',
                    shared_by bigint(20) unsigned NOT NULL,
                    shared_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    last_accessed datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY document_user (document_id, user_id),
                    KEY document_id (document_id),
                    KEY user_id (user_id),
                    KEY permission_level (permission_level),
                    KEY shared_by (shared_by)
                ) $charset_collate;
            ",
            'comments' => "
                CREATE TABLE {$this->table_prefix}comments (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    comment text NOT NULL,
                    metadata longtext,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY user_id (user_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'document_versions' => "
                CREATE TABLE {$this->table_prefix}document_versions (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    version_number int(11) NOT NULL,
                    data longtext NOT NULL,
                    change_summary text,
                    created_by bigint(20) unsigned NOT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY version_number (version_number),
                    KEY created_by (created_by),
                    UNIQUE KEY document_version (document_id, version_number)
                ) $charset_collate;
            ",
            'workflow_history' => "
                CREATE TABLE {$this->table_prefix}workflow_history (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    document_id bigint(20) unsigned NOT NULL,
                    user_id bigint(20) unsigned NOT NULL,
                    action varchar(50) NOT NULL,
                    old_value text,
                    new_value text,
                    comment text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY document_id (document_id),
                    KEY user_id (user_id),
                    KEY action (action),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'api_logs' => "
                CREATE TABLE {$this->table_prefix}api_logs (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    api_key_id varchar(100) DEFAULT NULL,
                    endpoint varchar(255) NOT NULL,
                    method varchar(10) NOT NULL,
                    response_code int(11) NOT NULL,
                    response_time decimal(5,2) DEFAULT NULL,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    request_data longtext,
                    response_data longtext,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY api_key_id (api_key_id),
                    KEY endpoint (endpoint),
                    KEY method (method),
                    KEY response_code (response_code),
                    KEY user_id (user_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'analytics_events' => "
                CREATE TABLE {$this->table_prefix}analytics_events (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_type varchar(50) NOT NULL,
                    event_data longtext,
                    user_id bigint(20) unsigned DEFAULT NULL,
                    session_id varchar(100) DEFAULT NULL,
                    ip_address varchar(45),
                    user_agent text,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY event_type (event_type),
                    KEY user_id (user_id),
                    KEY session_id (session_id),
                    KEY created_at (created_at)
                ) $charset_collate;
            ",
            'analytics_hourly' => "
                CREATE TABLE {$this->table_prefix}analytics_hourly (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    event_type varchar(50) NOT NULL,
                    period datetime NOT NULL,
                    count int(11) NOT NULL DEFAULT 0,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY event_period (event_type, period),
                    KEY event_type (event_type),
                    KEY period (period)
                ) $charset_collate;
            ",
            'webhooks' => "
                CREATE TABLE {$this->table_prefix}webhooks (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    webhook_id varchar(100) NOT NULL,
                    url varchar(500) NOT NULL,
                    events longtext NOT NULL,
                    secret varchar(255) NOT NULL,
                    active tinyint(1) NOT NULL DEFAULT 1,
                    failure_count int(11) NOT NULL DEFAULT 0,
                    last_triggered datetime DEFAULT NULL,
                    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY webhook_id (webhook_id),
                    KEY active (active),
                    KEY created_at (created_at)
                ) $charset_collate;
            "
        ];

        if (function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            foreach ($remaining_tables as $table_name => $sql) {
                dbDelta($sql);

                if ($this->wpdb->last_error) {
                    $this->logger->error('Failed to create remaining table', [
                        'table' => $table_name,
                        'error' => $this->wpdb->last_error
                    ]);
                } else {
                    $this->logger->info('Remaining table created successfully', ['table' => $table_name]);
                }
            }
        }

        // Mettre à jour la version de la base de données complète
        update_option('pdf_builder_db_version', PDF_BUILDER_VERSION);
    }

    /**
     * Vérifier si les tables existent
     *
     * @return bool
     */
    public function tables_exist(): bool {
        $tables = [
            $this->table_prefix . 'templates',
            $this->table_prefix . 'documents',
            $this->table_prefix . 'categories',
            $this->table_prefix . 'logs',
            $this->table_prefix . 'cache',
            $this->table_prefix . 'settings',
            $this->table_prefix . 'template_versions',
            $this->table_prefix . 'bulk_tasks',
            $this->table_prefix . 'export_logs',
            $this->table_prefix . 'analytics_reports',
            $this->table_prefix . 'document_shares',
            $this->table_prefix . 'comments',
            $this->table_prefix . 'document_versions',
            $this->table_prefix . 'workflow_history',
            $this->table_prefix . 'api_logs',
            $this->table_prefix . 'analytics_events',
            $this->table_prefix . 'analytics_hourly',
            $this->table_prefix . 'webhooks'
        ];

        if (!$this->wpdb) {
            return false; // WordPress database not available
        }

        foreach ($tables as $table) {
            if ($this->wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier si les tables essentielles existent
     *
     * @return bool
     */
    public function essential_tables_exist(): bool {
        $essential_tables = [
            $this->table_prefix . 'templates',
            $this->table_prefix . 'documents',
            $this->table_prefix . 'settings'
        ];

        foreach ($essential_tables as $table) {
            if ($this->wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifier et corriger la structure des tables
     *
     * @return void
     */
    public function verify_and_fix_table_structure(): void {
        // Vérifier la structure de la table templates si elle existe
        $templates_table = $this->table_prefix . 'templates';
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$templates_table'") === $templates_table) {
            $this->verify_templates_table_structure();
        }

        // Vérifier la structure de la table documents si elle existe
        $documents_table = $this->table_prefix . 'documents';
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$documents_table'") === $documents_table) {
            $this->verify_documents_table_structure();
        }
    }

    /**
     * Vérifier et corriger la structure de la table templates
     *
     * @return void
     */
    private function verify_templates_table_structure(): void {
        $table_name = $this->table_prefix . 'templates';

        // Vérifier si la colonne status existe
        $columns = $this->wpdb->get_results("DESCRIBE $table_name");
        $has_status = false;

        foreach ($columns as $column) {
            if ($column->Field === 'status') {
                $has_status = true;
                break;
            }
        }

        if (!$has_status) {
            // Ajouter la colonne status
            $this->wpdb->query("ALTER TABLE $table_name ADD COLUMN status varchar(20) NOT NULL DEFAULT 'active'");
            $this->logger->info('Added status column to templates table');
        }

        // Vérifier d'autres colonnes importantes si nécessaire
        $this->verify_column_exists($table_name, 'type', "varchar(50) NOT NULL DEFAULT 'pdf'");
        $this->verify_column_exists($table_name, 'author_id', 'bigint(20) unsigned NOT NULL');
        $this->verify_column_exists($table_name, 'created_at', 'datetime NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->verify_column_exists($table_name, 'updated_at', 'datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    /**
     * Vérifier et corriger la structure de la table documents
     *
     * @return void
     */
    private function verify_documents_table_structure(): void {
        $table_name = $this->table_prefix . 'documents';

        // Vérifier les colonnes importantes
        $this->verify_column_exists($table_name, 'status', "varchar(20) NOT NULL DEFAULT 'pending'");
        $this->verify_column_exists($table_name, 'workflow_status', "varchar(20) NOT NULL DEFAULT 'draft'");
        $this->verify_column_exists($table_name, 'author_id', 'bigint(20) unsigned NOT NULL');
        $this->verify_column_exists($table_name, 'created_at', 'datetime NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    /**
     * Vérifier si une colonne existe et l'ajouter si nécessaire
     *
     * @param string $table_name
     * @param string $column_name
     * @param string $column_definition
     * @return void
     */
    private function verify_column_exists(string $table_name, string $column_name, string $column_definition): void {
        // Vérifier d'abord si la table existe
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            $this->logger->warning('Cannot verify column on non-existent table', [
                'table' => $table_name,
                'column' => $column_name
            ]);
            return;
        }

        $columns = $this->wpdb->get_results("DESCRIBE $table_name");
        $exists = false;

        foreach ($columns as $column) {
            if ($column->Field === $column_name) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $this->wpdb->query("ALTER TABLE $table_name ADD COLUMN $column_name $column_definition");
            $this->logger->info("Added $column_name column to $table_name table");
        }
    }

    /**
     * Exécuter une requête SELECT avec cache
     *
     * @param string $query
     * @param array $args
     * @param int $cache_ttl
     * @return mixed
     */
    public function get_row(string $query, array $args = [], int $cache_ttl = 300) {
        $cache_key = $this->get_query_cache_key($query, $args);

        // Vérifier le cache
        $cached_result = $this->cache_manager->get($cache_key);
        if ($cached_result !== null) {
            $this->metrics['cached_queries']++;
            return $cached_result;
        }

        // Utiliser notre helper sécurisé pour toutes les requêtes préparées
        if (!empty($args)) {
            $prepared_query = PDF_Builder_Debug_Helper::safe_wpdb_prepare($query, ...$args);
            $result = $this->wpdb->get_row($prepared_query, ARRAY_A);
        } else {
            $result = $this->wpdb->get_row($query, ARRAY_A);
        }

        $this->log_query_performance($query, $args);

        if ($result && $cache_ttl > 0) {
            $this->cache_manager->set($cache_key, $result, $cache_ttl);
        }

        return $result;
    }

    /**
     * Exécuter une requête SELECT multiple avec cache
     *
     * @param string $query
     * @param array $args
     * @param int $cache_ttl
     * @return array
     */
    public function get_results(string $query, array $args = [], int $cache_ttl = 300): array {
        $cache_key = $this->get_query_cache_key($query, $args);

        // Vérifier le cache
        $cached_result = $this->cache_manager->get($cache_key);
        if ($cached_result !== null) {
            $this->metrics['cached_queries']++;
            return $cached_result;
        }

        // Remplacer automatiquement les noms de table courts par les noms complets
        $query = $this->replace_table_names($query);

        // Utiliser notre helper sécurisé pour toutes les requêtes préparées
        if (!empty($args)) {
            $prepared_query = PDF_Builder_Debug_Helper::safe_wpdb_prepare($query, ...$args);
            $result = $this->wpdb->get_results($prepared_query, ARRAY_A);
        } else {
            $result = $this->wpdb->get_results($query, ARRAY_A);
        }

        $this->log_query_performance($query, $args);

        if ($result && $cache_ttl > 0) {
            $this->cache_manager->set($cache_key, $result, $cache_ttl);
        }

        return $result ?: [];
    }

    /**
     * Exécuter une requête et retourner une valeur unique (comme COUNT)
     *
     * @param string $query
     * @param array $args
     * @param int $cache_ttl
     * @return mixed|null
     */
    public function get_var(string $query, array $args = [], int $cache_ttl = 300) {
        $cache_key = $this->get_query_cache_key($query, $args);

        // Vérifier le cache
        $cached_result = $this->cache_manager->get($cache_key);
        if ($cached_result !== null) {
            $this->metrics['cached_queries']++;
            return $cached_result;
        }

        // Remplacer automatiquement les noms de table courts par les noms complets
        $query = $this->replace_table_names($query);

        // Utiliser notre helper sécurisé pour toutes les requêtes préparées
        if (!empty($args)) {
            $prepared_query = PDF_Builder_Debug_Helper::safe_wpdb_prepare($query, ...$args);
            $result = $this->wpdb->get_var($prepared_query);
        } else {
            $result = $this->wpdb->get_var($query);
        }

        $this->log_query_performance($query, $args);

        if ($cache_ttl > 0) {
            $this->cache_manager->set($cache_key, $result, $cache_ttl);
        }

        return $result;
    }

    /**
     * Exécuter une requête INSERT
     *
     * @param string $table
     * @param array $data
     * @param array $format
     * @return int|false
     */
    public function insert(string $table, array $data, array $format = []) {
        $full_table = $this->get_full_table_name($table);

        $result = $this->wpdb->insert($full_table, $data, $format);

        if ($result === false) {
            $this->metrics['errors']++;
            $this->logger->error('Database insert failed', [
                'table' => $table,
                'error' => $this->wpdb->last_error,
                'data' => $data
            ]);
            return false;
        }

        $this->invalidate_table_cache($table);
        $this->log_query_performance("INSERT INTO $table", []);

        return $this->wpdb->insert_id;
    }

    /**
     * Exécuter une requête UPDATE
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array $format
     * @param array $where_format
     * @return int|false
     */
    public function update(string $table, array $data, array $where, array $format = [], array $where_format = []) {
        $full_table = $this->get_full_table_name($table);

        $result = $this->wpdb->update($full_table, $data, $where, $format, $where_format);

        if ($result === false) {
            $this->metrics['errors']++;
            $this->logger->error('Database update failed', [
                'table' => $table,
                'error' => $this->wpdb->last_error,
                'data' => $data,
                'where' => $where
            ]);
            return false;
        }

        $this->invalidate_table_cache($table);
        $this->log_query_performance("UPDATE $table", []);

        return $result;
    }

    /**
     * Exécuter une requête DELETE
     *
     * @param string $table
     * @param array $where
     * @param array $where_format
     * @return int|false
     */
    public function delete(string $table, array $where, array $where_format = []) {
        $full_table = $this->get_full_table_name($table);

        // Construire la clause WHERE en gérant les opérateurs
        $where_clauses = [];
        $where_values = [];
        $where_formats = [];

        foreach ($where as $column => $condition) {
            if (is_array($condition) && count($condition) === 2) {
                // Condition avec opérateur: ['<', value]
                list($operator, $value) = $condition;
                $where_clauses[] = "`$column` $operator %s";
                $where_values[] = $value;
                $where_formats[] = '%s'; // Par défaut string, peut être override par $where_format
            } else {
                // Condition simple: value
                $where_clauses[] = "`$column` = %s";
                $where_values[] = $condition;
                $where_formats[] = '%s';
            }
        }

        // Override formats si spécifié
        if (!empty($where_format)) {
            $where_formats = array_merge($where_formats, $where_format);
        }

        $where_sql = implode(' AND ', $where_clauses);
        $sql = "DELETE FROM $full_table WHERE $where_sql";

        $prepared_query = PDF_Builder_Debug_Helper::safe_wpdb_prepare($sql, ...$where_values);

        $result = $this->wpdb->query($prepared_query);

        if ($result === false) {
            $this->metrics['errors']++;
            $this->logger->error('Database delete failed', [
                'table' => $table,
                'error' => $this->wpdb->last_error,
                'where' => $where,
                'sql' => $sql
            ]);
            return false;
        }

        $this->invalidate_table_cache($table);
        $this->log_query_performance("DELETE FROM $table", $where_values);

        return $result;
    }

    /**
     * Démarrer une transaction
     *
     * @return void
     */
    public function begin_transaction(): void {
        $this->wpdb->query('START TRANSACTION');
        $this->logger->debug('Database transaction started');
    }

    /**
     * Valider une transaction
     *
     * @return void
     */
    public function commit(): void {
        $this->wpdb->query('COMMIT');
        $this->logger->debug('Database transaction committed');
    }

    /**
     * Annuler une transaction
     *
     * @return void
     */
    public function rollback(): void {
        $this->wpdb->query('ROLLBACK');
        $this->logger->warning('Database transaction rolled back');
    }

    /**
     * Optimiser les tables
     *
     * @return void
     */
    public function optimize_tables(): void {
        $tables = [
            'templates',
            'documents',
            'categories',
            'logs',
            'cache',
            'settings'
        ];

        foreach ($tables as $table) {
            $full_table = $this->get_full_table_name($table);
            $this->wpdb->query("OPTIMIZE TABLE $full_table");
        }

        $this->logger->info('Database tables optimized');
    }

    /**
     * Nettoyer la base de données
     *
     * @return void
     */
    public function cleanup(): void {
        // Supprimer les documents expirés
        $this->delete('documents', [
            'expires_at' => ['<', current_time('mysql')]
        ]);

        // Supprimer les logs anciens
        $retention_days = $this->config->get('log_retention_days', 90);
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));

        $this->delete('logs', [
            'created_at' => ['<', $cutoff_date]
        ]);

        // Nettoyer le cache expiré
        $prepared_query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
            "DELETE FROM {$this->get_full_table_name('cache')} WHERE expires_at < %s",
            current_time('mysql')
        );
        $this->wpdb->query($prepared_query);

        $this->logger->info('Database cleanup completed');
    }

    /**
     * Obtenir les statistiques de la base de données
     *
     * @return array
     */
    public function get_stats(): array {
        $stats = [
            'queries' => $this->metrics['queries'],
            'cached_queries' => $this->metrics['cached_queries'],
            'slow_queries' => $this->metrics['slow_queries'],
            'errors' => $this->metrics['errors'],
            'table_sizes' => []
        ];

        $tables = ['templates', 'documents', 'categories', 'logs', 'cache', 'settings'];

        foreach ($tables as $table) {
            $full_table = $this->get_full_table_name($table);
            $prepared_query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
                "SELECT COUNT(*) as count, 
                        ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb 
                 FROM information_schema.TABLES 
                 WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $full_table
            );
            $result = $this->wpdb->get_row($prepared_query, ARRAY_A);

            if ($result) {
                $stats['table_sizes'][$table] = [
                    'count' => (int) $result['count'],
                    'size_mb' => (float) $result['size_mb']
                ];
            }
        }

        return $stats;
    }

    /**
     * Obtenir le nom complet de la table
     *
     * @param string $table
     * @return string
     */
    public function get_full_table_name(string $table): string {
        return $this->table_prefix . $table;
    }

    /**
     * Remplacer les noms de table courts par les noms complets dans une requête
     *
     * @param string $query
     * @return string
     */
    private function replace_table_names(string $query): string {
        $tables = [
            'templates',
            'documents',
            'categories',
            'logs',
            'cache',
            'settings',
            'template_versions',
            'bulk_tasks',
            'export_logs',
            'analytics_reports',
            'document_shares',
            'comments',
            'document_versions',
            'workflow_history',
            'api_logs',
            'analytics_events',
            'analytics_hourly',
            'webhooks'
        ];

        foreach ($tables as $table) {
            $query = preg_replace('/\b' . preg_quote($table, '/') . '\b/', $this->get_full_table_name($table), $query);
        }

        return $query;
    }

    /**
     * Générer une clé de cache pour une requête
     *
     * @param string $query
     * @param array $args
     * @return string
     */
    private function get_query_cache_key(string $query, array $args): string {
        return 'db_query_' . md5($query . serialize($args));
    }

    /**
     * Invalider le cache d'une table
     *
     * @param string $table
     * @return void
     */
    private function invalidate_table_cache(string $table): void {
        $this->cache_manager->delete('db_query_*' . $table . '*');
    }

    /**
     * Logger les performances des requêtes
     *
     * @param string $query
     * @param array $args
     * @return void
     */
    private function log_query_performance(string $query, array $args): void {
        $this->metrics['queries']++;

        $query_time = $this->wpdb->timer_stop();
        $slow_query_threshold = 0.5; // 500ms

        if ($query_time > $slow_query_threshold) {
            $this->metrics['slow_queries']++;
            $this->logger->warning('Slow database query', [
                'query' => $query,
                'args' => $args,
                'time' => $query_time
            ]);
        }
    }

    /**
     * Valider le début d'une requête pour éviter les erreurs
     */
    private function validate_query_start() {
        if (!$this->wpdb) {
            throw new Exception('Database connection not available');
        }
    }

    /**
     * Créer une nouvelle version de template
     *
     * @param array $version_data
     * @return int|false
     */
    public function create_template_version(array $version_data) {
        $this->validate_query_start();

        $result = $this->wpdb->insert(
            $this->table_prefix . 'template_versions',
            [
                'template_id' => $version_data['template_id'],
                'version_number' => $version_data['version_number'],
                'data' => $version_data['data'],
                'change_summary' => $version_data['change_summary'] ?? '',
                'created_by' => $version_data['created_by'],
                'is_auto_save' => $version_data['is_auto_save'] ? 1 : 0,
                'created_at' => $version_data['created_at']
            ],
            ['%d', '%d', '%s', '%s', '%d', '%d', '%s']
        );

        $this->log_query_performance(
            'INSERT INTO template_versions',
            [$version_data['template_id'], $version_data['version_number']]
        );

        if ($result === false) {
            $this->metrics['errors']++;
            $this->logger->error('Failed to create template version', [
                'error' => $this->wpdb->last_error,
                'template_id' => $version_data['template_id']
            ]);
            return false;
        }

        $version_id = $this->wpdb->insert_id;
        $this->invalidate_table_cache('template_versions');

        return $version_id;
    }

    /**
     * Récupérer une version spécifique de template
     *
     * @param int $version_id
     * @return object|null
     */
    public function get_template_version(int $version_id) {
        $this->validate_query_start();

        $cache_key = 'template_version_' . $version_id;
        $version = $this->cache_manager->get($cache_key);

        if ($version === false) {
            $query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
                "SELECT * FROM {$this->table_prefix}template_versions WHERE id = %d",
                $version_id
            );

            $version = $this->wpdb->get_row($query);

            $this->log_query_performance('SELECT template_version by id', [$version_id]);

            if ($version) {
                $this->cache_manager->set($cache_key, $version, 300); // Cache 5 minutes
            }
        } else {
            $this->metrics['cached_queries']++;
        }

        return $version;
    }

    /**
     * Récupérer toutes les versions d'un template
     *
     * @param int $template_id
     * @param int $limit
     * @return array
     */
    public function get_template_versions(int $template_id, int $limit = 10): array {
        $this->validate_query_start();

        $cache_key = 'template_versions_' . $template_id . '_' . $limit;
        $versions = $this->cache_manager->get($cache_key);

        if ($versions === false) {
            $query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
                "SELECT * FROM {$this->table_prefix}template_versions 
                 WHERE template_id = %d 
                 ORDER BY version_number DESC 
                 LIMIT %d",
                $template_id,
                $limit
            );

            $versions = $this->wpdb->get_results($query);

            $this->log_query_performance('SELECT template_versions', [$template_id, $limit]);

            if ($versions) {
                $this->cache_manager->set($cache_key, $versions, 300); // Cache 5 minutes
            }
        } else {
            $this->metrics['cached_queries']++;
        }

        return $versions ?: [];
    }

    /**
     * Récupérer la dernière version d'un template
     *
     * @param int $template_id
     * @return object|null
     */
    public function get_latest_template_version(int $template_id) {
        $this->validate_query_start();

        $query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
            "SELECT * FROM {$this->table_prefix}template_versions 
             WHERE template_id = %d 
             ORDER BY version_number DESC 
             LIMIT 1",
            $template_id
        );

        $version = $this->wpdb->get_row($query);

        $this->log_query_performance('SELECT latest template_version', [$template_id]);

        return $version;
    }

    /**
     * Supprimer les anciennes versions de template (cleanup)
     *
     * @param int $template_id
     * @param int $keep_versions Nombre de versions à garder
     * @return int Nombre de versions supprimées
     */
    public function cleanup_template_versions(int $template_id, int $keep_versions = 10): int {
        $this->validate_query_start();

        // Récupérer les versions à supprimer
        $query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
            "SELECT id FROM {$this->table_prefix}template_versions 
             WHERE template_id = %d 
             ORDER BY version_number DESC 
             LIMIT 999999 OFFSET %d",
            $template_id,
            $keep_versions
        );

        $versions_to_delete = $this->wpdb->get_col($query);

        if (empty($versions_to_delete)) {
            return 0;
        }

        // Valider que c'est bien un array d'entiers
        $versions_to_delete = array_map('intval', $versions_to_delete);

        // Supprimer les versions
        $placeholders = str_repeat('%d,', count($versions_to_delete) - 1) . '%d';
        $query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
            "DELETE FROM {$this->table_prefix}template_versions 
             WHERE id IN ({$placeholders})",
            ...$versions_to_delete
        );

        $deleted = $this->wpdb->query($query);

        $this->log_query_performance('DELETE old template_versions', [$template_id, $keep_versions]);

        if ($deleted !== false) {
            $this->invalidate_table_cache('template_versions');
            $this->logger->info('Cleaned up old template versions', [
                'template_id' => $template_id,
                'deleted_count' => $deleted,
                'kept_versions' => $keep_versions
            ]);
        }

        return $deleted ?: 0;
    }

    /**
     * Récupérer un template avec ses métadonnées
     *
     * @param int $template_id
     * @return object|null
     */
    public function get_template(int $template_id) {
        $this->validate_query_start();

        $cache_key = 'template_' . $template_id;
        $template = $this->cache_manager->get($cache_key);

        if ($template === false) {
            $query = PDF_Builder_Debug_Helper::safe_wpdb_prepare(
                "SELECT t.*, u.display_name as author_name, c.name as category_name
                 FROM {$this->table_prefix}templates t
                 LEFT JOIN {$this->wpdb->users} u ON t.author_id = u.ID
                 LEFT JOIN {$this->table_prefix}categories c ON t.category_id = c.id
                 WHERE t.id = %d",
                $template_id
            );

            $template = $this->wpdb->get_row($query);

            $this->log_query_performance('SELECT template with metadata', [$template_id]);

            if ($template) {
                $this->cache_manager->set($cache_key, $template, 300); // Cache 5 minutes
            }
        } else {
            $this->metrics['cached_queries']++;
        }

        return $template;
    }

    /**
     * Créer un nouveau template
     *
     * @param array $template_data
     * @return int|false
     */
    public function create_template(array $template_data) {
        $this->validate_query_start();

        $result = $this->wpdb->insert(
            $this->table_prefix . 'templates',
            [
                'name' => $template_data['name'],
                'description' => $template_data['description'] ?? '',
                'type' => $template_data['type'] ?? 'pdf',
                'content' => $template_data['data'] ?? '',
                'settings' => $template_data['settings'] ?? '{}',
                'status' => $template_data['status'] ?? 'active',
                'category_id' => $template_data['category_id'] ?? null,
                'author_id' => $template_data['created_by'] ?? get_current_user_id(),
                'created_at' => $template_data['created_at'] ?? current_time('mysql'),
                'updated_at' => $template_data['updated_at'] ?? current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s']
        );

        $this->log_query_performance('INSERT template', [$template_data['name']]);

        if ($result === false) {
            $this->metrics['errors']++;
            $this->logger->error('Failed to create template', [
                'error' => $this->wpdb->last_error,
                'name' => $template_data['name']
            ]);
            return false;
        }

        $template_id = $this->wpdb->insert_id;
        $this->invalidate_table_cache('templates');

        return $template_id;
    }

    /**
     * Mettre à jour un template
     *
     * @param int $template_id
     * @param array $update_data
     * @return bool
     */
    public function update_template(int $template_id, array $update_data): bool {
        $this->validate_query_start();

        $update_data['updated_at'] = current_time('mysql');

        // Vérifier si le template existe avant la mise à jour
        $exists = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_prefix}templates WHERE id = %d",
            $template_id
        ));

        if (!$exists) {
            $this->logger->error('Template does not exist', ['template_id' => $template_id]);
            return false;
        }

        $result = $this->wpdb->update(
            $this->table_prefix . 'templates',
            $update_data,
            ['id' => $template_id],
            $this->get_format_array($update_data),
            ['%d']
        );

        $this->log_query_performance('UPDATE template', [$template_id]);

        // wpdb->update() retourne le nombre de lignes affectées, ou false en cas d'erreur
        if ($result === false) {
            $this->metrics['errors']++;
            $this->logger->error('Failed to update template', [
                'error' => $this->wpdb->last_error,
                'template_id' => $template_id,
                'update_data' => $update_data
            ]);
            return false;
        }

        // Si result est 0, cela signifie que aucune ligne n'a été modifiée
        // (probablement parce que les données sont identiques)
        // Dans ce cas, on considère que c'est un succès
        $this->logger->info('Template update completed', [
            'template_id' => $template_id,
            'rows_affected' => $result,
            'update_data' => $update_data
        ]);

        // Invalider le cache
        $this->cache_manager->delete('template_' . $template_id);
        $this->invalidate_table_cache('templates');

        return true;
    }

    /**
     * Générer le tableau de formats pour wpdb
     *
     * @param array $data
     * @return array
     */
    private function get_format_array(array $data): array {
        $formats = [];
        foreach ($data as $key => $value) {
            if (is_int($value)) {
                $formats[] = '%d';
            } elseif (is_float($value)) {
                $formats[] = '%f';
            } else {
                $formats[] = '%s';
            }
        }
        return $formats;
    }

    /**
     * Initialisation du gestionnaire
     *
     * @return void
     */
    public function init(): void {
        if (!$this->wpdb) {
            // WordPress database not available, skip initialization
            return;
        }

        if (!$this->tables_exist()) {
            $this->create_tables();
        }

        // Nettoyer la base de données périodiquement
        $last_cleanup = $this->cache_manager->get('db_last_cleanup');
        $cleanup_interval = $this->config->get('db_cleanup_interval', 86400);

        if (!$last_cleanup || (time() - $last_cleanup) > $cleanup_interval) {
            $this->cleanup();
            $this->cache_manager->set('db_last_cleanup', time(), $cleanup_interval);
        }

        $this->logger->info('Database manager initialized', $this->get_stats());
    }

    /**
     * Vérifie si une table existe
     *
     * @param string $table_name
     * @return bool
     */
    public function table_exists(string $table_name): bool {
        $full_table_name = $this->get_table_name($table_name);
        $query = $this->wpdb->prepare("SHOW TABLES LIKE %s", $full_table_name);
        $result = $this->wpdb->get_var($query);
        return !empty($result);
    }

    /**
     * Obtient le nombre d'enregistrements dans une table
     *
     * @param string $table_name
     * @return int
     */
    public function get_table_count(string $table_name): int {
        $full_table_name = $this->get_table_name($table_name);
        $query = "SELECT COUNT(*) FROM {$full_table_name}";
        return (int) $this->wpdb->get_var($query);
    }

    /**
     * Obtient le nom complet d'une table avec le préfixe
     *
     * @param string $table_name
     * @return string
     */
    public function get_table_name(string $table_name): string {
        return $this->table_prefix . $table_name;
    }
}

