<?php
/**
 * PDF Builder Pro - Gestionnaire de mises à jour
 * Gère les migrations de base de données et les mises à jour du plugin
 */

class PDF_Builder_Update_Manager {
    private static $instance = null;
    private $current_version;
    private $db_version;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->current_version = PDF_BUILDER_VERSION;
        $this->db_version = get_option('pdf_builder_db_version', '1.0.0');

        $this->init_hooks();
    }

    private function init_hooks() {
        // Vérifier les mises à jour lors de l'activation du plugin
        register_activation_hook(PDF_BUILDER_PLUGIN_FILE, [$this, 'check_for_updates']);

        // Hook pour les mises à jour automatiques
        add_action('admin_init', [$this, 'check_version']);

        // AJAX pour les mises à jour manuelles
        add_action('wp_ajax_pdf_builder_run_updates', [$this, 'run_updates_ajax']);

        // Nettoyage après mise à jour
        add_action('upgrader_process_complete', [$this, 'after_plugin_update'], 10, 2);
    }

    /**
     * Vérifie si une mise à jour est nécessaire
     */
    public function check_version() {
        if (version_compare($this->db_version, $this->current_version, '<')) {
            $this->run_updates();
        }
    }

    /**
     * Exécute les mises à jour de base de données
     */
    public function run_updates() {
        try {
            // Démarrer une transaction pour la sécurité
            global $wpdb;
            $wpdb->query('START TRANSACTION');

            $this->log_update_start();

            // Exécuter les migrations dans l'ordre
            $migrations = $this->get_available_migrations();

            foreach ($migrations as $version => $migration) {
                if (version_compare($this->db_version, $version, '<')) {
                    $this->log_update("Running migration: $version");

                    if ($this->execute_migration($migration)) {
                        update_option('pdf_builder_db_version', $version);
                        $this->db_version = $version;
                        $this->log_update("Migration $version completed successfully");
                    } else {
                        throw new Exception("Migration $version failed");
                    }
                }
            }

            // Mettre à jour la version finale
            update_option('pdf_builder_db_version', $this->current_version);
            update_option('pdf_builder_last_update', current_time('mysql'));

            $wpdb->query('COMMIT');

            $this->log_update("All updates completed successfully");

            // Nettoyer le cache après mise à jour
            $this->cleanup_after_update();

        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            $this->log_update_error($e->getMessage());

            // Notifier l'admin
            $this->notify_admin_update_error($e);
        }
    }

    /**
     * Exécute les mises à jour via AJAX
     */
    public function run_updates_ajax() {
        try {
            // Valider la requête
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $this->run_updates();

            wp_send_json_success([
                'message' => 'Mises à jour terminées avec succès',
                'new_version' => $this->current_version
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors des mises à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient la liste des migrations disponibles
     */
    private function get_available_migrations() {
        return [
            '1.1.0' => [$this, 'migrate_1_1_0'],
            '1.2.0' => [$this, 'migrate_1_2_0'],
            '1.3.0' => [$this, 'migrate_1_3_0'],
            '1.4.0' => [$this, 'migrate_1_4_0'],
            '1.5.0' => [$this, 'migrate_1_5_0'],
            '2.0.0' => [$this, 'migrate_2_0_0'],
            '2.1.0' => [$this, 'migrate_2_1_0'],
            '2.2.0' => [$this, 'migrate_2_2_0'],
            '2.3.0' => [$this, 'migrate_2_3_0'],
            '2.4.0' => [$this, 'migrate_2_4_0'],
            '2.5.0' => [$this, 'migrate_2_5_0'],
            '3.0.0' => [$this, 'migrate_3_0_0'],
            '3.1.0' => [$this, 'migrate_3_1_0'],
            '3.2.0' => [$this, 'migrate_3_2_0'],
            '3.3.0' => [$this, 'migrate_3_3_0'],
            '3.4.0' => [$this, 'migrate_3_4_0'],
            '3.5.0' => [$this, 'migrate_3_5_0'],
            '3.6.0' => [$this, 'migrate_3_6_0'],
            '3.7.0' => [$this, 'migrate_3_7_0'],
            '3.8.0' => [$this, 'migrate_3_8_0'],
            '4.0.0' => [$this, 'migrate_4_0_0'],
            '4.1.0' => [$this, 'migrate_4_1_0'],
            '4.2.0' => [$this, 'migrate_4_2_0'],
            '4.3.0' => [$this, 'migrate_4_3_0'],
            '4.4.0' => [$this, 'migrate_4_4_0'],
            '4.5.0' => [$this, 'migrate_4_5_0'],
            '4.6.0' => [$this, 'migrate_4_6_0'],
            '4.7.0' => [$this, 'migrate_4_7_0'],
            '4.8.0' => [$this, 'migrate_4_8_0'],
            '5.0.0' => [$this, 'migrate_5_0_0'],
            '5.1.0' => [$this, 'migrate_5_1_0'],
            '5.2.0' => [$this, 'migrate_5_2_0'],
            '5.3.0' => [$this, 'migrate_5_3_0'],
            '5.4.0' => [$this, 'migrate_5_4_0'],
            '5.5.0' => [$this, 'migrate_5_5_0'],
            '5.6.0' => [$this, 'migrate_5_6_0'],
            '5.7.0' => [$this, 'migrate_5_7_0'],
            '5.8.0' => [$this, 'migrate_5_8_0']
        ];
    }

    /**
     * Migration 1.1.0 - Ajout des tables de cache et d'erreurs
     */
    private function migrate_1_1_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table de cache
        $table_cache = $wpdb->prefix . 'pdf_builder_cache';
        $sql_cache = "CREATE TABLE $table_cache (
            cache_key varchar(191) NOT NULL,
            cache_value longtext NOT NULL,
            expires bigint(20) NOT NULL,
            PRIMARY KEY (cache_key),
            KEY expires (expires)
        ) $charset_collate;";

        // Table des erreurs
        $table_errors = $wpdb->prefix . 'pdf_builder_errors';
        $sql_errors = "CREATE TABLE $table_errors (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            file varchar(500),
            line int(11),
            context longtext,
            trace longtext,
            source varchar(50) DEFAULT 'php',
            user_id bigint(20) unsigned,
            ip varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY source (source),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_cache);
        dbDelta($sql_errors);

        return true;
    }

    /**
     * Migration 1.2.0 - Ajout des tables de performance
     */
    private function migrate_1_2_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table des métriques de performance
        $table_metrics = $wpdb->prefix . 'pdf_builder_performance_metrics';
        $sql_metrics = "CREATE TABLE $table_metrics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            identifier varchar(100) NOT NULL,
            duration float NOT NULL,
            memory_used bigint(20) NOT NULL,
            query_count int(11) DEFAULT 0,
            query_time float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY identifier (identifier),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Table des problèmes de performance
        $table_issues = $wpdb->prefix . 'pdf_builder_performance_issues';
        $sql_issues = "CREATE TABLE $table_issues (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(50) NOT NULL,
            data longtext NOT NULL,
            url varchar(500),
            user_id bigint(20) unsigned,
            ip varchar(45),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_metrics);
        dbDelta($sql_issues);

        return true;
    }

    /**
     * Migration 1.3.0 - Amélioration de la table templates
     */
    private function migrate_1_3_0() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_templates';

        // Ajouter des colonnes si elles n'existent pas
        $columns_to_add = [
            'is_public' => 'TINYINT(1) DEFAULT 0',
            'version' => 'VARCHAR(20) DEFAULT "1.0"',
            'last_modified' => 'DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'metadata' => 'LONGTEXT'
        ];

        foreach ($columns_to_add as $column => $definition) {
            if (!$this->column_exists($table, $column)) {
                $wpdb->query("ALTER TABLE $table ADD COLUMN $column $definition");
            }
        }

        return true;
    }

    /**
     * Migration 1.4.0 - Ajout des tables de sauvegarde
     */
    private function migrate_1_4_0() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_backups = $wpdb->prefix . 'pdf_builder_backups';
        $sql = "CREATE TABLE $table_backups (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            template_id bigint(20),
            backup_data longtext NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY template_id (template_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        return true;
    }

    /**
     * Migration 1.5.0 - Index et optimisations
     */
    private function migrate_1_5_0() {
        global $wpdb;

        // Ajouter des index pour améliorer les performances
        $tables = [
            $wpdb->prefix . 'pdf_builder_templates' => [
                'ADD INDEX idx_user_created (user_id, created_at)',
                'ADD INDEX idx_name (name(50))'
            ],
            $wpdb->prefix . 'pdf_builder_cache' => [
                'ADD INDEX idx_expires_key (expires, cache_key(50))'
            ],
            $wpdb->prefix . 'pdf_builder_errors' => [
                'ADD INDEX idx_level_created (level, created_at)',
                'ADD INDEX idx_user_id (user_id)'
            ]
        ];

        foreach ($tables as $table => $indexes) {
            foreach ($indexes as $index_sql) {
                // Vérifier si l'index n'existe pas déjà
                if (!$this->index_exists($table, $index_sql)) {
                    $wpdb->query("ALTER TABLE $table $index_sql");
                }
            }
        }

        return true;
    }

    /**
     * Migration 2.0.0 - Refonte majeure de l'architecture
     */
    private function migrate_2_0_0() {
        global $wpdb;

        // Migration des données existantes vers le nouveau format
        $this->migrate_legacy_data();

        // Créer les nouvelles tables pour la v2
        $this->create_v2_tables();

        return true;
    }

    /**
     * Migration 5.8.0 - Corrections de sécurité et améliorations
     */
    private function migrate_5_8_0() {
        global $wpdb;

        // Ajouter des colonnes de sécurité
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $security_columns = [
            'security_hash' => 'VARCHAR(64) DEFAULT ""',
            'last_security_check' => 'DATETIME NULL',
            'is_encrypted' => 'TINYINT(1) DEFAULT 0'
        ];

        foreach ($security_columns as $column => $definition) {
            if (!$this->column_exists($table_templates, $column)) {
                $wpdb->query("ALTER TABLE $table_templates ADD COLUMN $column $definition");
            }
        }

        // Générer les hash de sécurité pour les templates existants
        $templates = $wpdb->get_results("SELECT id, template_data FROM $table_templates WHERE security_hash = ''");
        foreach ($templates as $template) {
            $hash = hash('sha256', $template->template_data . wp_salt());
            $wpdb->update(
                $table_templates,
                ['security_hash' => $hash, 'last_security_check' => current_time('mysql')],
                ['id' => $template->id]
            );
        }

        return true;
    }

    // Placeholder pour les autres migrations
    private function migrate_2_1_0() { return true; }
    private function migrate_2_2_0() { return true; }
    private function migrate_2_3_0() { return true; }
    private function migrate_2_4_0() { return true; }
    private function migrate_2_5_0() { return true; }
    private function migrate_3_0_0() { return true; }
    private function migrate_3_1_0() { return true; }
    private function migrate_3_2_0() { return true; }
    private function migrate_3_3_0() { return true; }
    private function migrate_3_4_0() { return true; }
    private function migrate_3_5_0() { return true; }
    private function migrate_3_6_0() { return true; }
    private function migrate_3_7_0() { return true; }
    private function migrate_3_8_0() { return true; }
    private function migrate_4_0_0() { return true; }
    private function migrate_4_1_0() { return true; }
    private function migrate_4_2_0() { return true; }
    private function migrate_4_3_0() { return true; }
    private function migrate_4_4_0() { return true; }
    private function migrate_4_5_0() { return true; }
    private function migrate_4_6_0() { return true; }
    private function migrate_4_7_0() { return true; }
    private function migrate_4_8_0() { return true; }
    private function migrate_5_0_0() { return true; }
    private function migrate_5_1_0() { return true; }
    private function migrate_5_2_0() { return true; }
    private function migrate_5_3_0() { return true; }
    private function migrate_5_4_0() { return true; }
    private function migrate_5_5_0() { return true; }
    private function migrate_5_6_0() { return true; }
    private function migrate_5_7_0() { return true; }

    /**
     * Migre les données existantes
     */
    private function migrate_legacy_data() {
        // Implémentation de la migration des données legacy
        // Cette méthode serait appelée lors de la migration 2.0.0
    }

    /**
     * Crée les tables pour la v2
     */
    private function create_v2_tables() {
        // Implémentation de la création des nouvelles tables
    }

    /**
     * Exécute une migration spécifique
     */
    private function execute_migration($migration_callback) {
        if (is_callable($migration_callback)) {
            return call_user_func($migration_callback);
        }
        return false;
    }

    /**
     * Vérifie si une colonne existe dans une table
     */
    private function column_exists($table, $column) {
        global $wpdb;
        $result = $wpdb->get_results($wpdb->prepare(
            "SHOW COLUMNS FROM $table LIKE %s",
            $column
        ));
        return !empty($result);
    }

    /**
     * Vérifie si un index existe
     */
    private function index_exists($table, $index_sql) {
        // Logique simplifiée - en pratique, il faudrait parser le SQL
        return false;
    }

    /**
     * Log le début d'une mise à jour
     */
    private function log_update_start() {
        $this->log_update("Starting database update from {$this->db_version} to {$this->current_version}");
    }

    /**
     * Log une étape de mise à jour
     */
    private function log_update($message) {
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->info("Update Manager: $message");
        } else {
            // error_log("[PDF Builder Update] $message");
        }
    }

    /**
     * Log une erreur de mise à jour
     */
    private function log_update_error($error) {
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->error("Update Manager Error: $error");
        } else {
            // error_log("[PDF Builder Update Error] $error");
        }
    }

    /**
     * Notifie l'admin d'une erreur de mise à jour
     */
    private function notify_admin_update_error($exception) {
        $admin_email = get_option('admin_email');
        $subject = 'Erreur de mise à jour PDF Builder Pro';
        $message = "Une erreur s'est produite lors de la mise à jour du plugin PDF Builder Pro:\n\n" .
                  "Erreur: " . $exception->getMessage() . "\n" .
                  "Version actuelle: " . $this->current_version . "\n" .
                  "Version DB: " . $this->db_version . "\n\n" .
                  "Veuillez contacter le support technique.";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Nettoie après une mise à jour
     */
    private function cleanup_after_update() {
        // Vider le cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Nettoyer les transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");

        // Régénérer les règles de réécriture si nécessaire
        flush_rewrite_rules();
    }

    /**
     * Actions après mise à jour du plugin
     */
    public function after_plugin_update($upgrader_object, $options) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            if (isset($options['plugins']) && is_array($options['plugins'])) {
                foreach ($options['plugins'] as $plugin) {
                    if (strpos($plugin, 'pdf-builder-pro') !== false) {
                        // Le plugin a été mis à jour, vérifier les migrations
                        $this->check_version();
                        break;
                    }
                }
            }
        }
    }

    /**
     * Vérifie les mises à jour lors de l'activation
     */
    public function check_for_updates() {
        $this->check_version();
    }

    /**
     * Obtient le statut des mises à jour
     */
    public function get_update_status() {
        return [
            'current_version' => $this->current_version,
            'db_version' => $this->db_version,
            'needs_update' => version_compare($this->db_version, $this->current_version, '<'),
            'last_update' => get_option('pdf_builder_last_update')
        ];
    }
}

// Fonctions globales
function pdf_builder_get_db_update_status() {
    return PDF_Builder_Update_Manager::get_instance()->get_update_status();
}

function pdf_builder_run_updates() {
    PDF_Builder_Update_Manager::get_instance()->run_updates();
}

// Initialiser le gestionnaire de mises à jour
add_action('plugins_loaded', function() {
    PDF_Builder_Update_Manager::get_instance();
});

