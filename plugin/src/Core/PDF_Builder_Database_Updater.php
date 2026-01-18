<?php
/**
 * PDF Builder Pro - Système de mise à jour de base de données
 * Gère les migrations, schémas et mises à jour de données
 */

class PDF_Builder_Database_Updater {
    private static $instance = null;

    // Versions de base de données
    const DB_VERSION = '1.4.0';
    const DB_VERSION_OPTION = 'pdf_builder_db_version';

    // Types de migrations
    const MIGRATION_UP = 'up';
    const MIGRATION_DOWN = 'down';

    // Statuts de migration
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_ROLLED_BACK = 'rolled_back';

    private $migrations = [];
    private $current_version;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->current_version = get_option(self::DB_VERSION_OPTION, '0.0.0');
        $this->init_hooks();
        $this->load_migrations();
    }

    private function init_hooks() {
        // Actions de mise à jour
        add_action('wp_ajax_pdf_builder_run_migration', [$this, 'run_migration_ajax']);
        add_action('wp_ajax_pdf_builder_get_migration_status', [$this, 'get_migration_status_ajax']);
        add_action('wp_ajax_pdf_builder_rollback_migration', [$this, 'rollback_migration_ajax']);

        // Vérifications automatiques
        add_action('admin_init', [$this, 'check_for_updates']);
        add_action('pdf_builder_daily_maintenance', [$this, 'verify_database_integrity']);

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_migration_history']);
    }

    /**
     * Charge les migrations disponibles
     */
    private function load_migrations() {
        $this->migrations = [
            '1.0.0' => [
                'description' => 'Migration initiale - Tables de base',
                'up' => [$this, 'migrate_to_1_0_0'],
                'down' => [$this, 'rollback_from_1_0_0']
            ],
            '1.1.0' => [
                'description' => 'Ajout des tables de métriques et analytics',
                'up' => [$this, 'migrate_to_1_1_0'],
                'down' => [$this, 'rollback_from_1_1_0']
            ],
            '1.2.0' => [
                'description' => 'Ajout des tables de déploiement et santé',
                'up' => [$this, 'migrate_to_1_2_0'],
                'down' => [$this, 'rollback_from_1_2_0']
            ],
            '1.3.0' => [
                'description' => 'Optimisations de performance et index',
                'up' => [$this, 'migrate_to_1_3_0'],
                'down' => [$this, 'rollback_from_1_3_0']
            ],
            '1.4.0' => [
                'description' => 'Ajout de la table des paramètres canvas séparés',
                'up' => [$this, 'migrate_to_1_4_0'],
                'down' => [$this, 'rollback_from_1_4_0']
            ]
        ];
    }

    /**
     * Vérifie les mises à jour disponibles
     */
    public function check_for_updates() {
        if (version_compare($this->current_version, self::DB_VERSION, '<')) {
            add_action('admin_notices', [$this, 'show_update_notice']);
        }
    }

    /**
     * Affiche la notice de mise à jour
     */
    public function show_update_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $migrations_needed = $this->get_pending_migrations();

        if (!empty($migrations_needed)) {
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>PDF Builder Pro:</strong> ' . count($migrations_needed) . ' migration(s) de base de données en attente.</p>';
            echo '<p><a href="' . admin_url('admin.php?page=pdf-builder-migrations') . '" class="button button-primary">Exécuter les migrations</a></p>';
            echo '</div>';
        }
    }

    /**
     * Obtient les migrations en attente
     */
    public function get_pending_migrations() {
        $pending = [];

        foreach ($this->migrations as $version => $migration) {
            if (version_compare($version, $this->current_version, '>')) {
                $pending[$version] = $migration;
            }
        }

        return $pending;
    }

    /**
     * Exécute une migration
     */
    public function run_migration($target_version, $direction = self::MIGRATION_UP) {
        try {
            if (!isset($this->migrations[$target_version])) {
                throw new Exception('Migration introuvable: ' . $target_version);
            }

            $migration = $this->migrations[$target_version];

            // Créer un enregistrement de migration
            $migration_id = $this->create_migration_record($target_version, $direction);

            // Mettre à jour le statut
            $this->update_migration_status($migration_id, self::STATUS_RUNNING);

            // Démarrer une transaction
            global $wpdb;
            $wpdb->query('START TRANSACTION');

            try {
                // Exécuter la migration
                if ($direction === self::MIGRATION_UP) {
                    call_user_func($migration['up']);
                } else {
                    call_user_func($migration['down']);
                }

                // Valider la transaction
                $wpdb->query('COMMIT');

                // Mettre à jour la version
                if ($direction === self::MIGRATION_UP) {
                    update_option(self::DB_VERSION_OPTION, $target_version);
                    $this->current_version = $target_version;
                } else {
                    // Trouver la version précédente
                    $previous_version = $this->get_previous_version($target_version);
                    update_option(self::DB_VERSION_OPTION, $previous_version);
                    $this->current_version = $previous_version;
                }

                // Marquer comme succès
                $this->update_migration_status($migration_id, self::STATUS_SUCCESS, [
                    'completed_at' => current_time('mysql')
                ]);

                // Logger le succès
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->info("Database migration completed: $target_version ($direction)");
                }

                return $migration_id;

            } catch (Exception $e) {
                // Annuler la transaction
                $wpdb->query('ROLLBACK');

                // Marquer comme échoué
                $this->update_migration_status($migration_id, self::STATUS_FAILED, [
                    'error' => $e->getMessage(),
                    'failed_at' => current_time('mysql')
                ]);

                throw $e;
            }

        } catch (Exception $e) {
            // Logger l'erreur
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->error("Database migration failed: $target_version ($direction)", [
                    'error' => $e->getMessage()
                ]);
            }

            throw $e;
        }
    }

    /**
     * Exécute toutes les migrations en attente
     */
    public function run_all_pending_migrations() {
        $pending = $this->get_pending_migrations();
        $results = [];

        foreach ($pending as $version => $migration) {
            try {
                $migration_id = $this->run_migration($version, self::MIGRATION_UP);
                $results[$version] = [
                    'success' => true,
                    'migration_id' => $migration_id
                ];
            } catch (Exception $e) {
                $results[$version] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                break; // Arrêter en cas d'échec
            }
        }

        return $results;
    }

    /**
     * Annule une migration
     */
    public function rollback_migration($migration_id) {
        $migration = $this->get_migration_record($migration_id);

        if (!$migration) {
            throw new Exception('Migration introuvable');
        }

        if ($migration['status'] !== self::STATUS_SUCCESS) {
            throw new Exception('Seules les migrations réussies peuvent être annulées');
        }

        return $this->run_migration($migration['version'], self::MIGRATION_DOWN);
    }

    /**
     * Obtient la version précédente
     */
    private function get_previous_version($current_version) {
        $versions = array_keys($this->migrations);
        sort($versions);

        $current_index = array_search($current_version, $versions);

        if ($current_index === false || $current_index === 0) {
            return '0.0.0';
        }

        return $versions[$current_index - 1];
    }

    /**
     * Crée un enregistrement de migration
     */
    private function create_migration_record($version, $direction) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_migrations';

        $wpdb->insert(
            $table,
            [
                'version' => $version,
                'direction' => $direction,
                'status' => self::STATUS_PENDING,
                'user_id' => get_current_user_id(),
                'started_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );

        return $wpdb->insert_id;
    }

    /**
     * Met à jour le statut d'une migration
     */
    private function update_migration_status($migration_id, $status, $additional_data = []) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_migrations';

        $update_data = ['status' => $status] + $additional_data;

        $wpdb->update(
            $table,
            $update_data,
            ['id' => $migration_id],
            array_fill(0, count($update_data), '%s'),
            ['%d']
        );
    }

    /**
     * Obtient un enregistrement de migration
     */
    private function get_migration_record($migration_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_migrations';

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table WHERE id = %d
        ", $migration_id), ARRAY_A);
    }

    /**
     * Migration vers 1.0.0 - Tables de base
     */
    public function migrate_to_1_0_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des configurations
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_config (
                id int(11) NOT NULL AUTO_INCREMENT,
                config_key varchar(255) NOT NULL,
                config_value longtext,
                config_type varchar(50) DEFAULT 'string',
                is_public tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY config_key (config_key),
                KEY config_type (config_type),
                KEY is_public (is_public)
            ) $charset_collate
        ");

        // Table des logs
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_logs (
                id int(11) NOT NULL AUTO_INCREMENT,
                level varchar(20) NOT NULL,
                message text NOT NULL,
                context longtext,
                user_id int(11) DEFAULT NULL,
                ip_address varchar(45),
                user_agent text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY level (level),
                KEY user_id (user_id),
                KEY created_at (created_at),
                KEY level_created (level, created_at)
            ) $charset_collate
        ");

        // Table des caches
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_cache (
                cache_key varchar(255) NOT NULL,
                cache_value longtext,
                cache_group varchar(100) DEFAULT 'default',
                expires_at datetime NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (cache_key),
                KEY cache_group (cache_group),
                KEY expires_at (expires_at)
            ) $charset_collate
        ");

        // Table des tâches planifiées
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_tasks (
                id int(11) NOT NULL AUTO_INCREMENT,
                task_name varchar(255) NOT NULL,
                task_type varchar(50) NOT NULL,
                task_data longtext,
                status varchar(20) DEFAULT 'pending',
                priority int(11) DEFAULT 0,
                scheduled_at datetime NULL,
                executed_at datetime NULL,
                completed_at datetime NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY task_type (task_type),
                KEY status (status),
                KEY priority (priority),
                KEY scheduled_at (scheduled_at)
            ) $charset_collate
        ");
    }

    /**
     * Rollback de 1.0.0
     */
    public function rollback_from_1_0_0() {
        global $wpdb;

        $tables = [
            'pdf_builder_config',
            'pdf_builder_logs',
            'pdf_builder_cache',
            'pdf_builder_tasks'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
        }
    }

    /**
     * Migration vers 1.1.0 - Tables de métriques
     */
    public function migrate_to_1_1_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des métriques brutes
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_metrics (
                id int(11) NOT NULL AUTO_INCREMENT,
                type varchar(50) NOT NULL,
                name varchar(255) NOT NULL,
                value float NOT NULL,
                metadata longtext,
                user_id int(11) DEFAULT NULL,
                timestamp datetime NOT NULL,
                session_id varchar(255),
                ip_address varchar(45),
                user_agent text,
                PRIMARY KEY (id),
                KEY type (type),
                KEY name (name),
                KEY user_id (user_id),
                KEY timestamp (timestamp),
                KEY type_timestamp (type, timestamp)
            ) $charset_collate
        ");

        // Table des métriques agrégées
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_metrics_aggregated (
                id int(11) NOT NULL AUTO_INCREMENT,
                period varchar(20) NOT NULL,
                type varchar(50) NOT NULL,
                name varchar(255) NOT NULL,
                date datetime NOT NULL,
                count int(11) NOT NULL DEFAULT 0,
                avg_value float DEFAULT NULL,
                min_value float DEFAULT NULL,
                max_value float DEFAULT NULL,
                sum_value float DEFAULT NULL,
                data longtext,
                PRIMARY KEY (id),
                UNIQUE KEY unique_period_type_name_date (period, type, name, date),
                KEY period (period),
                KEY type (type),
                KEY name (name),
                KEY date (date)
            ) $charset_collate
        ");
    }

    /**
     * Rollback de 1.1.0
     */
    public function rollback_from_1_1_0() {
        global $wpdb;

        $tables = [
            'pdf_builder_metrics',
            'pdf_builder_metrics_aggregated'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
        }
    }

    /**
     * Migration vers 1.2.0 - Tables de déploiement et santé
     */
    public function migrate_to_1_2_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des déploiements
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_deployments (
                id int(11) NOT NULL AUTO_INCREMENT,
                version varchar(20) NOT NULL,
                environment varchar(20) NOT NULL,
                deployment_type varchar(20) NOT NULL,
                status varchar(20) NOT NULL,
                user_id int(11) DEFAULT NULL,
                previous_version varchar(20),
                backup_id int(11) DEFAULT NULL,
                error text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                started_at datetime NULL,
                completed_at datetime NULL,
                failed_at datetime NULL,
                PRIMARY KEY (id),
                KEY environment (environment),
                KEY status (status),
                KEY user_id (user_id),
                KEY created_at (created_at)
            ) $charset_collate
        ");

        // Table des migrations
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_migrations (
                id int(11) NOT NULL AUTO_INCREMENT,
                version varchar(20) NOT NULL,
                direction varchar(10) NOT NULL,
                status varchar(20) NOT NULL,
                user_id int(11) DEFAULT NULL,
                error text,
                started_at datetime DEFAULT CURRENT_TIMESTAMP,
                completed_at datetime NULL,
                failed_at datetime NULL,
                PRIMARY KEY (id),
                KEY version (version),
                KEY direction (direction),
                KEY status (status),
                KEY user_id (user_id)
            ) $charset_collate
        ");

        // Table de surveillance de santé
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_health_metrics (
                id int(11) NOT NULL AUTO_INCREMENT,
                timestamp datetime NOT NULL,
                overall_status varchar(20) NOT NULL,
                system_status varchar(20) DEFAULT 'unknown',
                database_status varchar(20) DEFAULT 'unknown',
                filesystem_status varchar(20) DEFAULT 'unknown',
                wordpress_status varchar(20) DEFAULT 'unknown',
                plugin_status varchar(20) DEFAULT 'unknown',
                metrics longtext,
                issues longtext,
                PRIMARY KEY (id),
                KEY timestamp (timestamp),
                KEY overall_status (overall_status)
            ) $charset_collate
        ");
    }

    /**
     * Rollback de 1.2.0
     */
    public function rollback_from_1_2_0() {
        global $wpdb;

        $tables = [
            'pdf_builder_deployments',
            'pdf_builder_migrations',
            'pdf_builder_health_metrics'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
        }
    }

    /**
     * Migration vers 1.3.0 - Optimisations
     */
    public function migrate_to_1_3_0() {
        global $wpdb;

        // Ajouter des index pour améliorer les performances
        $indexes = [
            "ALTER TABLE {$wpdb->prefix}pdf_builder_logs ADD INDEX idx_level_created (level, created_at)",
            "ALTER TABLE {$wpdb->prefix}pdf_builder_cache ADD INDEX idx_group_expires (cache_group, expires_at)",
            "ALTER TABLE {$wpdb->prefix}pdf_builder_tasks ADD INDEX idx_status_priority (status, priority)",
            "ALTER TABLE {$wpdb->prefix}pdf_builder_metrics ADD INDEX idx_type_timestamp (type, timestamp)",
            "ALTER TABLE {$wpdb->prefix}pdf_builder_metrics_aggregated ADD INDEX idx_period_date (period, date)",
            "ALTER TABLE {$wpdb->prefix}pdf_builder_deployments ADD INDEX idx_env_status (environment, status)",
            "ALTER TABLE {$wpdb->prefix}pdf_builder_health_metrics ADD INDEX idx_status_timestamp (overall_status, timestamp)"
        ];

        foreach ($indexes as $index_sql) {
            $wpdb->query($index_sql);
        }

        // Optimiser les tables
        $tables = [
            'pdf_builder_config',
            'pdf_builder_logs',
            'pdf_builder_cache',
            'pdf_builder_tasks',
            'pdf_builder_metrics',
            'pdf_builder_metrics_aggregated',
            'pdf_builder_deployments',
            'pdf_builder_migrations',
            'pdf_builder_health_metrics'
        ];

        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE {$wpdb->prefix}$table");
        }
    }

    /**
     * Rollback de 1.3.0
     */
    public function rollback_from_1_3_0() {
        global $wpdb;

        // Les index ne peuvent pas être facilement supprimés dans un rollback
        // Ils resteront en place, ce qui n'est pas grave
    }

    /**
     * Vérifie l'intégrité de la base de données
     */
    public function verify_database_integrity() {
        global $wpdb;

        $issues = [];

        // Vérifier que toutes les tables existent
        $required_tables = [
            'pdf_builder_config',
            'pdf_builder_logs',
            'pdf_builder_cache',
            'pdf_builder_tasks',
            'pdf_builder_metrics',
            'pdf_builder_metrics_aggregated',
            'pdf_builder_deployments',
            'pdf_builder_migrations',
            'pdf_builder_health_metrics'
        ];

        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*) FROM information_schema.tables
                WHERE table_schema = %s AND table_name = %s
            ", DB_NAME, $table_name));

            if (!$exists) {
                $issues[] = "Table manquante: $table_name";
            }
        }

        // Vérifier les tables corrompues
        foreach ($required_tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $result = $wpdb->get_var("CHECK TABLE $table_name");

            if ($result !== 'OK') {
                $issues[] = "Table corrompue: $table_name";
            }
        }

        if (!empty($issues)) {
            // Legacy notification calls removed — log as critical
            PDF_Builder_Logger::get_instance()->critical('Problèmes de base de données détectés: ' . implode(', ', $issues), ['issues' => $issues]);
        }
    }

    /**
     * Nettoie l'historique des migrations
     */
    public function cleanup_migration_history() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_migrations';

        // Garder seulement les 100 dernières migrations
        $wpdb->query("
            DELETE FROM $table
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT id FROM $table
                    ORDER BY started_at DESC
                    LIMIT 100
                ) tmp
            )
        ");
    }

    /**
     * Obtient l'historique des migrations
     */
    public function get_migration_history($limit = 50) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_migrations';

        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table
            ORDER BY started_at DESC
            LIMIT %d
        ", $limit), ARRAY_A);
    }

    /**
     * AJAX - Exécute une migration
     */
    public function run_migration_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $version = sanitize_text_field($_POST['version'] ?? '');
            $direction = sanitize_text_field($_POST['direction'] ?? self::MIGRATION_UP);

            if (empty($version)) {
                wp_send_json_error(['message' => 'Version manquante']);
                return;
            }

            $migration_id = $this->run_migration($version, $direction);

            wp_send_json_success([
                'message' => 'Migration exécutée avec succès',
                'migration_id' => $migration_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la migration: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut des migrations
     */
    public function get_migration_status_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $history = $this->get_migration_history(20);
            $pending = $this->get_pending_migrations();

            wp_send_json_success([
                'message' => 'Historique récupéré',
                'current_version' => $this->current_version,
                'latest_version' => self::DB_VERSION,
                'pending_migrations' => $pending,
                'migration_history' => $history
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Annule une migration
     */
    public function rollback_migration_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $migration_id = intval($_POST['migration_id'] ?? 0);

            if (!$migration_id) {
                wp_send_json_error(['message' => 'ID de migration manquant']);
                return;
            }

            $rollback_id = $this->rollback_migration($migration_id);

            wp_send_json_success([
                'message' => 'Migration annulée avec succès',
                'rollback_id' => $rollback_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'annulation: ' . $e->getMessage()]);
        }
    }

    /**
     * Migration vers 1.4.0 - Table des paramètres canvas séparés
     */
    public function migrate_to_1_4_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des paramètres canvas séparés
        $wpdb->query("
            CREATE TABLE {$wpdb->prefix}pdf_builder_settings (
                id int(11) NOT NULL AUTO_INCREMENT,
                setting_key varchar(255) NOT NULL,
                setting_value longtext,
                setting_group varchar(100) DEFAULT 'canvas',
                setting_type varchar(50) DEFAULT 'string',
                is_public tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY setting_key (setting_key),
                KEY setting_group (setting_group),
                KEY setting_type (setting_type),
                KEY is_public (is_public)
            ) $charset_collate
        ");

        // Migrer les paramètres canvas existants depuis wp_options vers la nouvelle table
        $existing_settings = get_option('pdf_builder_settings', []);
        if (!empty($existing_settings)) {
            foreach ($existing_settings as $key => $value) {
                if (strpos($key, 'pdf_builder_canvas_') === 0) {
                    $wpdb->insert(
                        $wpdb->prefix . 'pdf_builder_settings',
                        [
                            'setting_key' => $key,
                            'setting_value' => maybe_serialize($value),
                            'setting_group' => 'canvas',
                            'setting_type' => $this->detect_setting_type($value),
                            'is_public' => 0
                        ],
                        ['%s', '%s', '%s', '%s', '%d']
                    );
                }
            }
        }
    }

    /**
     * Rollback depuis 1.4.0
     */
    public function rollback_from_1_4_0() {
        global $wpdb;

        // Migrer les paramètres canvas depuis la table vers wp_options
        $canvas_settings = $wpdb->get_results("
            SELECT setting_key, setting_value
            FROM {$wpdb->prefix}pdf_builder_settings
            WHERE setting_group = 'canvas'
        ", ARRAY_A);

        if (!empty($canvas_settings)) {
            $existing_settings = get_option('pdf_builder_settings', []);
            foreach ($canvas_settings as $setting) {
                $existing_settings[$setting['setting_key']] = maybe_unserialize($setting['setting_value']);
            }
            update_option('pdf_builder_settings', $existing_settings);
        }

        // Supprimer la table
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pdf_builder_settings");
    }

    /**
     * Détecte le type d'un paramètre
     */
    private function detect_setting_type($value) {
        if (is_array($value)) {
            return 'array';
        } elseif (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_string($value) && strlen($value) > 100) {
            return 'textarea';
        } else {
            return 'string';
        }
    }
}

// Fonctions globales
function pdf_builder_db_updater() {
    return PDF_Builder_Database_Updater::get_instance();
}

function pdf_builder_run_migration($version, $direction = 'up') {
    return PDF_Builder_Database_Updater::get_instance()->run_migration($version, $direction);
}

function pdf_builder_run_pending_migrations() {
    return PDF_Builder_Database_Updater::get_instance()->run_all_pending_migrations();
}

function pdf_builder_get_migration_history($limit = 50) {
    return PDF_Builder_Database_Updater::get_instance()->get_migration_history($limit);
}

function pdf_builder_get_pending_migrations() {
    return PDF_Builder_Database_Updater::get_instance()->get_pending_migrations();
}

// Initialiser le système de mise à jour de base de données
add_action('plugins_loaded', function() {
    PDF_Builder_Database_Updater::get_instance();
});

