<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Système de secours et récupération
 * Gère les sauvegardes d'urgence et la récupération en cas de panne
 */

class PDF_Builder_Backup_Recovery_System {
    private static $instance = null;

    // Types de sauvegarde
    const BACKUP_TYPE_FULL = 'full';
    const BACKUP_TYPE_INCREMENTAL = 'incremental';
    const BACKUP_TYPE_CRITICAL = 'critical';

    // Statuts de sauvegarde
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Sauvegardes automatiques
        add_action('pdf_builder_auto_backup', [$this, 'create_automatic_backup']);

        // Récupération d'urgence
        add_action('wp_ajax_pdf_builder_create_backup', [$this, 'create_backup_ajax']);
        add_action('wp_ajax_pdf_builder_restore_backup', [$this, 'restore_backup_ajax']);
        add_action('wp_ajax_pdf_builder_get_backup_status', [$this, 'get_backup_status_ajax']);

        // Vérification d'intégrité
        add_action('pdf_builder_daily_integrity_check', [$this, 'check_system_integrity']);

        // Nettoyage des anciennes sauvegardes
        add_action('pdf_builder_weekly_cleanup', [$this, 'cleanup_old_backups']);
    }

    /**
     * Crée une sauvegarde complète du système
     */
    public function create_full_backup($name = null, $description = '') {
        try {
            $backup_id = $this->generate_backup_id();
            $backup_path = $this->get_backup_path($backup_id);

            // Créer le dossier de sauvegarde
            wp_mkdir_p($backup_path);

            // Collecter toutes les données
            $backup_data = [
                'metadata' => [
                    'id' => $backup_id,
                    'type' => self::BACKUP_TYPE_FULL,
                    'name' => $name ?: 'Sauvegarde complète ' . gmdate('Y-m-d H:i:s'),
                    'description' => $description,
                    'created_at' => current_time('mysql'),
                    'created_by' => get_current_user_id(),
                    'version' => PDF_BUILDER_VERSION,
                    'wordpress_version' => get_bloginfo('version'),
                    'php_version' => PHP_VERSION
                ],
                'database' => $this->backup_database(),
                'files' => $this->backup_files(),
                'configuration' => $this->backup_configuration(),
                'templates' => $this->backup_templates(),
                'logs' => $this->backup_logs()
            ];

            // Sauvegarder les métadonnées
            file_put_contents(
                $backup_path . '/metadata.json',
                json_encode($backup_data['metadata'], JSON_PRETTY_PRINT)
            );

            // Créer l'archive
            $archive_path = $this->create_backup_archive($backup_id, $backup_data);

            // Enregistrer la sauvegarde
            $this->register_backup($backup_data['metadata'], $archive_path);

            // Logger le succès
            error_log('Backup created successfully: ' . $backup_id);

            return $backup_id;

        } catch (Exception $e) {
            $this->log_backup_error('create_full_backup', $e);
            throw $e;
        }
    }

    /**
     * Crée une sauvegarde automatique
     */
    public function create_automatic_backup() {
        try {
            $name = 'Sauvegarde automatique ' . gmdate('Y-m-d H:i:s');
            $this->create_full_backup($name, 'Sauvegarde créée automatiquement');

        } catch (Exception $e) {
            $this->log_backup_error('automatic_backup', $e);
        }
    }

    /**
     * Restaure une sauvegarde
     */
    public function restore_backup($backup_id, $components = null) {
        try {
            // Vérifier que la sauvegarde existe
            $backup_info = $this->get_backup_info($backup_id);
            if (!$backup_info) {
                throw new Exception('Sauvegarde introuvable');
            }

            // Vérifier l'intégrité de la sauvegarde
            if (!$this->verify_backup_integrity($backup_id)) {
                throw new Exception('Sauvegarde corrompue');
            }

            // Créer une sauvegarde de secours avant restauration
            $emergency_backup_id = $this->create_emergency_backup();

            // Extraire l'archive
            $extract_path = $this->extract_backup_archive($backup_id);

            // Restaurer les composants
            $results = [];

            if ($components === null || in_array('database', $components)) {
                $results['database'] = $this->restore_database($extract_path);
            }

            if ($components === null || in_array('files', $components)) {
                $results['files'] = $this->restore_files($extract_path);
            }

            if ($components === null || in_array('configuration', $components)) {
                $results['configuration'] = $this->restore_configuration($extract_path);
            }

            if ($components === null || in_array('templates', $components)) {
                $results['templates'] = $this->restore_templates($extract_path);
            }

            // Nettoyer les fichiers temporaires
            $this->cleanup_temp_files($extract_path);

            // Marquer la restauration comme réussie
            $this->mark_backup_restored($backup_id, $emergency_backup_id);

            // Logger le succès
            error_log('Backup restored successfully: ' . $backup_id);

            return $results;

        } catch (Exception $e) {
            $this->log_backup_error('restore_backup', $e);
            throw $e;
        }
    }

    /**
     * Sauvegarde la base de données
     */
    private function backup_database() {
        global $wpdb;

        $tables = $this->get_plugin_tables();
        $backup_data = [];

        foreach ($tables as $table) {
            $table_data = $wpdb->get_results("SELECT * FROM $table", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $backup_data[$table] = $table_data;
        }

        return $backup_data;
    }

    /**
     * Sauvegarde les fichiers
     */
    private function backup_files() {
        $files_to_backup = [
            'templates' => PDF_BUILDER_PLUGIN_DIR . 'resources/templates/',
            'assets' => PDF_BUILDER_PLUGIN_DIR . 'resources/assets/',
            'config' => PDF_BUILDER_PLUGIN_DIR . 'config/',
            'uploads' => WP_CONTENT_DIR . '/uploads/pdf-builder/'
        ];

        $backup_data = [];

        foreach ($files_to_backup as $name => $path) {
            if (file_exists($path)) {
                $backup_data[$name] = $this->get_directory_structure($path);
            }
        }

        return $backup_data;
    }

    /**
     * Sauvegarde la configuration
     */
    private function backup_configuration() {
        return [
            'options' => $this->get_plugin_options(),
            'settings' => pdf_builder_config(),
            'capabilities' => $this->get_user_capabilities()
        ];
    }

    /**
     * Sauvegarde les templates
     */
    private function backup_templates() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_templates';
        return $wpdb->get_results("SELECT * FROM $table", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
    }

    /**
     * Sauvegarde les logs
     */
    private function backup_logs() {
        $log_files = glob(PDF_BUILDER_PLUGIN_DIR . 'logs/*.log');
        $logs = [];

        foreach ($log_files as $file) {
            $logs[basename($file)] = file_get_contents($file);
        }

        return $logs;
    }

    /**
     * Restaure la base de données
     */
    private function restore_database($extract_path) {
        $backup_file = $extract_path . '/database.sql';

        if (!file_exists($backup_file)) {
            throw new Exception('Fichier de sauvegarde base de données introuvable');
        }

        // Lire et exécuter le fichier SQL
        $sql = file_get_contents($backup_file);
        $queries = array_filter(array_map('trim', explode(';', $sql)));

        global $wpdb;

        foreach ($queries as $query) {
            if (!empty($query)) {
                $wpdb->query($query); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
            }
        }

        return ['status' => 'success', 'queries_executed' => count($queries)];
    }

    /**
     * Restaure les fichiers
     */
    private function restore_files($extract_path) {
        $files_backup = $extract_path . '/files.tar.gz';

        if (!file_exists($files_backup)) {
            return ['status' => 'skipped', 'message' => 'Aucune sauvegarde de fichiers trouvée'];
        }

        // Extraire l'archive des fichiers
        $extract_command = "tar -xzf $files_backup -C " . WP_CONTENT_DIR;
        exec($extract_command, $output, $return_var);

        return [
            'status' => $return_var === 0 ? 'success' : 'error',
            'command_output' => $output
        ];
    }

    /**
     * Restaure la configuration
     */
    private function restore_configuration($extract_path) {
        $config_file = $extract_path . '/configuration.json';

        if (!file_exists($config_file)) {
            return ['status' => 'skipped', 'message' => 'Aucune sauvegarde de configuration trouvée'];
        }

        $config = json_decode(file_get_contents($config_file), true);

        // Restaurer les options
        foreach ($config['options'] as $option => $value) {
            update_option($option, $value);
        }

        return ['status' => 'success', 'options_restored' => count($config['options'])];
    }

    /**
     * Restaure les templates
     */
    private function restore_templates($extract_path) {
        $templates_file = $extract_path . '/templates.json';

        if (!file_exists($templates_file)) {
            return ['status' => 'skipped', 'message' => 'Aucune sauvegarde de templates trouvée'];
        }

        $templates = json_decode(file_get_contents($templates_file), true);

        global $wpdb;
        $table = $wpdb->prefix . 'pdf_builder_templates';

        $restored_count = 0;
        foreach ($templates as $template) {
            $wpdb->replace($table, $template);
            $restored_count++;
        }

        return ['status' => 'success', 'templates_restored' => $restored_count];
    }

    /**
     * Crée une sauvegarde d'urgence avant restauration
     */
    private function create_emergency_backup() {
        $name = 'Sauvegarde d\'urgence avant restauration ' . gmdate('Y-m-d H:i:s');
        return $this->create_full_backup($name, 'Sauvegarde créée automatiquement avant restauration');
    }

    /**
     * Obtient la liste des tables du plugin
     */
    private function get_plugin_tables() {
        global $wpdb;

        return [
            $wpdb->prefix . 'pdf_builder_templates',
            $wpdb->prefix . 'pdf_builder_cache',
            $wpdb->prefix . 'pdf_builder_errors',
            $wpdb->prefix . 'pdf_builder_performance_metrics',
            $wpdb->prefix . 'pdf_builder_performance_issues',
            $wpdb->prefix . 'pdf_builder_backups',
            $wpdb->prefix . 'pdf_builder_analytics'
        ];
    }

    /**
     * Obtient les options du plugin
     */
    private function get_plugin_options() {
        global $wpdb;

        $options = $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT option_name, option_value
            FROM {$wpdb->options}
            WHERE option_name LIKE %s
        ", 'pdf_builder_%'), ARRAY_A);

        return array_column($options, 'option_value', 'option_name');
    }

    /**
     * Obtient les capacités utilisateur
     */
    private function get_user_capabilities() {
        $roles = wp_roles();
        $capabilities = [];

        if (!$roles || !isset($roles->roles)) {
            return $capabilities;
        }

        foreach ($roles->roles as $role_name => $role_info) {
            $capabilities[$role_name] = array_keys($role_info['capabilities']);
        }

        return $capabilities;
    }

    /**
     * Obtient la structure d'un dossier
     */
    private function get_directory_structure($dir, $base_path = '') {
        $structure = [];

        if (!is_dir($dir)) {
            return $structure;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $relative_path = $base_path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $structure[$relative_path] = $this->get_directory_structure($path, $relative_path);
            } else {
                $structure[$relative_path] = [
                    'size' => filesize($path),
                    'modified' => filemtime($path),
                    'hash' => md5_file($path)
                ];
            }
        }

        return $structure;
    }

    /**
     * Génère un ID unique pour la sauvegarde
     */
    private function generate_backup_id() {
        return 'backup_' . wp_generate_password(16, false) . '_' . time();
    }

    /**
     * Obtient le chemin de sauvegarde
     */
    private function get_backup_path($backup_id) {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/pdf-builder/backups/' . $backup_id;
    }

    /**
     * Crée l'archive de sauvegarde
     */
    private function create_backup_archive($backup_id, $backup_data) {
        $backup_path = $this->get_backup_path($backup_id);
        $archive_path = $backup_path . '.tar.gz';

        // Créer les fichiers de données
        file_put_contents($backup_path . '/database.json', json_encode($backup_data['database']));
        file_put_contents($backup_path . '/files_structure.json', json_encode($backup_data['files']));
        file_put_contents($backup_path . '/configuration.json', json_encode($backup_data['configuration']));
        file_put_contents($backup_path . '/templates.json', json_encode($backup_data['templates']));

        // Créer l'archive
        $command = "cd " . dirname($backup_path) . " && tar -czf $archive_path $backup_id/";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            throw new Exception('Erreur lors de la création de l\'archive');
        }

        // Supprimer le dossier temporaire
        $this->delete_directory($backup_path);

        return $archive_path;
    }

    /**
     * Extrait l'archive de sauvegarde
     */
    private function extract_backup_archive($backup_id) {
        $backup_info = $this->get_backup_info($backup_id);
        $archive_path = $backup_info['archive_path'];
        $extract_path = sys_get_temp_dir() . '/pdf_builder_restore_' . $backup_id;

        wp_mkdir_p($extract_path);

        $command = "tar -xzf $archive_path -C $extract_path";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            throw new Exception('Erreur lors de l\'extraction de l\'archive');
        }

        return $extract_path;
    }

    /**
     * Enregistre une sauvegarde
     */
    private function register_backup($metadata, $archive_path) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';

        $wpdb->insert(
            $table,
            [
                'backup_id' => $metadata['id'],
                'name' => $metadata['name'],
                'description' => $metadata['description'],
                'backup_data' => json_encode($metadata),
                'archive_path' => $archive_path,
                'file_size' => filesize($archive_path),
                'user_id' => $metadata['created_by'],
                'status' => self::STATUS_COMPLETED,
                'created_at' => $metadata['created_at']
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s']
        );
    }

    /**
     * Obtient les informations d'une sauvegarde
     */
    private function get_backup_info($backup_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';

        return $wpdb->get_row($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table WHERE backup_id = %s
        ", $backup_id), ARRAY_A);
    }

    /**
     * Vérifie l'intégrité d'une sauvegarde
     */
    private function verify_backup_integrity($backup_id) {
        $backup_info = $this->get_backup_info($backup_id);

        if (!$backup_info) {
            return false;
        }

        $archive_path = $backup_info['archive_path'];

        // Vérifier que le fichier existe
        if (!file_exists($archive_path)) {
            return false;
        }

        // Vérifier la taille du fichier
        if (filesize($archive_path) !== intval($backup_info['file_size'])) {
            return false;
        }

        // Essayer d'extraire pour vérifier l'intégrité
        $test_extract = sys_get_temp_dir() . '/pdf_builder_verify_' . $backup_id;
        wp_mkdir_p($test_extract);

        $command = "tar -tzf $archive_path -C $test_extract >/dev/null 2>&1";
        exec($command, $output, $return_var);

        $this->delete_directory($test_extract);

        return $return_var === 0;
    }

    /**
     * Marque une sauvegarde comme restaurée
     */
    private function mark_backup_restored($backup_id, $emergency_backup_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';

        $wpdb->update(
            $table,
            [
                'last_restored_at' => current_time('mysql'),
                'emergency_backup_id' => $emergency_backup_id
            ],
            ['backup_id' => $backup_id],
            ['%s', '%s'],
            ['%s']
        );
    }

    /**
     * Supprime un dossier récursivement
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                wp_delete_file($path);
            }
        }

        rmdir($dir); // phpcs:ignore WordPress.WP.AlternativeFunctions
    }

    /**
     * Nettoie les fichiers temporaires
     */
    private function cleanup_temp_files($path) {
        if (is_dir($path)) {
            $this->delete_directory($path);
        }
    }

    /**
     * Vérifie l'intégrité du système
     */
    public function check_system_integrity() {
        $issues = [];

        // Vérifier les tables de base de données
        $tables = $this->get_plugin_tables();
        foreach ($tables as $table) {
            if (!$this->table_exists($table)) {
                $issues[] = "Table manquante: $table";
            }
        }

        // Vérifier les fichiers critiques
        $critical_files = [
            PDF_BUILDER_PLUGIN_FILE,
            PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Loader.php',
            PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/Ajax_Handlers.php'
        ];

        foreach ($critical_files as $file) {
            if (!file_exists($file)) {
                $issues[] = "Fichier critique manquant: $file";
            }
        }

        // Vérifier les permissions
        $writable_dirs = [
            WP_CONTENT_DIR . '/uploads/pdf-builder/',
            PDF_BUILDER_PLUGIN_DIR . 'logs/'
        ];

        foreach ($writable_dirs as $dir) {
            if (!is_writable($dir)) { // phpcs:ignore WordPress.WP.AlternativeFunctions
                $issues[] = "Dossier non accessible en écriture: $dir";
            }
        }

        if (!empty($issues)) {
            // Créer une sauvegarde d'urgence
            $emergency_backup = $this->create_emergency_backup();

            // Notifier l'admin
            // Legacy notification calls removed — log this event as critical
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'compromised',
            'issues' => $issues,
            'checked_at' => current_time('mysql')
        ];
    }

    /**
     * Vérifie si une table existe
     */
    private function table_exists($table) {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
    }

    /**
     * Nettoie les anciennes sauvegardes
     */
    public function cleanup_old_backups() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';
        $retention_days = pdf_builder_config('backup_retention_days', 30);

        $deleted = $wpdb->query($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $table
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
            AND type != 'emergency'
        ", $retention_days));
    }

    /**
     * Log une erreur de sauvegarde
     */
    private function log_backup_error($operation, $exception) {
        error_log(wp_json_encode([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]));
    }

    /**
     * AJAX - Crée une sauvegarde
     */
    public function create_backup_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $name = sanitize_text_field($_POST['name'] ?? '');
            $description = sanitize_textarea_field($_POST['description'] ?? '');

            $backup_id = $this->create_full_backup($name, $description);

            wp_send_json_success([
                'message' => 'Sauvegarde créée avec succès',
                'backup_id' => $backup_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Restaure une sauvegarde
     */
    public function restore_backup_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $backup_id = sanitize_text_field($_POST['backup_id'] ?? '');
            $components = isset($_POST['components']) ? (array) $_POST['components'] : null;

            $results = $this->restore_backup($backup_id, $components);

            wp_send_json_success([
                'message' => 'Sauvegarde restaurée avec succès',
                'results' => $results
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la restauration: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut des sauvegardes
     */
    public function get_backup_status_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $backups = $this->get_backup_list();

            wp_send_json_success([
                'message' => 'Statut récupéré',
                'backups' => $backups
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient la liste des sauvegardes
     */
    public function get_backup_list() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';

        return $wpdb->get_results(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT 50
        ", ARRAY_A);
    }
}

// Fonctions globales
function pdf_builder_create_backup($name = '', $description = '') {
    return PDF_Builder_Backup_Recovery_System::get_instance()->create_full_backup($name, $description);
}

function pdf_builder_restore_backup($backup_id, $components = null) {
    return PDF_Builder_Backup_Recovery_System::get_instance()->restore_backup($backup_id, $components);
}

function pdf_builder_get_backups() {
    return PDF_Builder_Backup_Recovery_System::get_instance()->get_backup_list();
}

function pdf_builder_check_integrity() {
    return PDF_Builder_Backup_Recovery_System::get_instance()->check_system_integrity();
}

// Initialiser le système de sauvegarde
add_action('plugins_loaded', function() {
    PDF_Builder_Backup_Recovery_System::get_instance();
});



