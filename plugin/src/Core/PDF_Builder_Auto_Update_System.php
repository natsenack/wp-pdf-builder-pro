<?php
/**
 * PDF Builder Pro - Système de mise à jour automatique
 * Gère les mises à jour du plugin de manière sécurisée et automatique
 */

class PDF_Builder_Auto_Update_System {
    private static $instance = null;

    // Types de mise à jour
    const UPDATE_TYPE_PATCH = 'patch';
    const UPDATE_TYPE_MINOR = 'minor';
    const UPDATE_TYPE_MAJOR = 'major';
    const UPDATE_TYPE_SECURITY = 'security';

    // Statuts de mise à jour
    const STATUS_AVAILABLE = 'available';
    const STATUS_DOWNLOADING = 'downloading';
    const STATUS_DOWNLOADED = 'downloaded';
    const STATUS_INSTALLING = 'installing';
    const STATUS_INSTALLED = 'installed';
    const STATUS_FAILED = 'failed';

    // Branches de mise à jour
    const BRANCH_STABLE = 'stable';
    const BRANCH_BETA = 'beta';
    const BRANCH_DEV = 'dev';

    private $update_server_url = 'https://api.pdf-builder-pro.com/updates';
    private $current_branch;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->current_branch = pdf_builder_config('update_branch', self::BRANCH_STABLE);
        $this->init_hooks();
    }

    private function init_hooks() {
        // Vérifications de mises à jour
        add_action('wp_ajax_pdf_builder_check_updates', [$this, 'check_updates_ajax']);
        add_action('wp_ajax_pdf_builder_install_update', [$this, 'install_update_ajax']);
        add_action('wp_ajax_pdf_builder_get_update_status', [$this, 'get_update_status_ajax']);

        // Vérifications automatiques
        add_action('pdf_builder_daily_update_check', [$this, 'check_for_updates']);
        add_action('pdf_builder_weekly_update_check', [$this, 'perform_weekly_update_check']);

        // Nettoyage des anciennes versions
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_old_versions']);

        // Gestion des mises à jour WordPress
        add_filter('pre_set_site_transient_update_plugins', [$this, 'inject_plugin_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);

        // Mise à jour automatique si activée
        if (pdf_builder_config('auto_update_enabled', false)) {
            add_action('pdf_builder_auto_update', [$this, 'perform_auto_update']);
        }
    }

    /**
     * Vérifie les mises à jour disponibles
     */
    public function check_for_updates() {
        try {
            $current_version = PDF_BUILDER_VERSION;
            $updates = $this->fetch_available_updates();

            if (empty($updates)) {
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->info('No updates available');
                }
                return false;
            }

            // Filtrer selon la branche
            $updates = $this->filter_updates_by_branch($updates);

            // Trouver la meilleure mise à jour
            $best_update = $this->find_best_update($updates, $current_version);

            if ($best_update) {
                $this->store_update_info($best_update);

                // Notifier si c'est une mise à jour de sécurité
                if ($best_update['type'] === self::UPDATE_TYPE_SECURITY) {
                    // Legacy notification calls removed — log an error for security updates
                    PDF_Builder_Logger::get_instance()->warning("Mise à jour de sécurité disponible: {$best_update['version']}", ['update' => $best_update, 'current_version' => $current_version]);
                }

                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->info('Update available', [
                        'version' => $best_update['version'],
                        'type' => $best_update['type']
                    ]);
                }

                return $best_update;
            }

            return false;

        } catch (Exception $e) {
            $this->log_update_error('check_for_updates', $e);
            return false;
        }
    }

    /**
     * Récupère les mises à jour disponibles depuis le serveur
     */
    private function fetch_available_updates() {
        $response = wp_remote_get($this->update_server_url . '/available', [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_api_token(),
                'X-Site-URL' => get_site_url(),
                'X-Current-Version' => PDF_BUILDER_VERSION,
                'X-Branch' => $this->current_branch
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Erreur de connexion au serveur de mise à jour: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Réponse invalide du serveur de mise à jour');
        }

        if (!isset($data['updates']) || !is_array($data['updates'])) {
            throw new Exception('Format de réponse invalide');
        }

        return $data['updates'];
    }

    /**
     * Filtre les mises à jour selon la branche sélectionnée
     */
    private function filter_updates_by_branch($updates) {
        return array_filter($updates, function($update) {
            switch ($this->current_branch) {
                case self::BRANCH_STABLE:
                    return $update['branch'] === self::BRANCH_STABLE;

                case self::BRANCH_BETA:
                    return in_array($update['branch'], [self::BRANCH_STABLE, self::BRANCH_BETA]);

                case self::BRANCH_DEV:
                    return in_array($update['branch'], [self::BRANCH_STABLE, self::BRANCH_BETA, self::BRANCH_DEV]);

                default:
                    return $update['branch'] === self::BRANCH_STABLE;
            }
        });
    }

    /**
     * Trouve la meilleure mise à jour disponible
     */
    private function find_best_update($updates, $current_version) {
        $best_update = null;
        $best_priority = -1;

        $priorities = [
            self::UPDATE_TYPE_SECURITY => 4,
            self::UPDATE_TYPE_MAJOR => 3,
            self::UPDATE_TYPE_MINOR => 2,
            self::UPDATE_TYPE_PATCH => 1
        ];

        foreach ($updates as $update) {
            // Vérifier si la version est plus récente
            if (version_compare($update['version'], $current_version, '<=')) {
                continue;
            }

            $priority = $priorities[$update['type']] ?? 0;

            if ($priority > $best_priority) {
                $best_update = $update;
                $best_priority = $priority;
            }
        }

        return $best_update;
    }

    /**
     * Stocke les informations de mise à jour
     */
    private function store_update_info($update) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_updates';

        $wpdb->replace(
            $table,
            [
                'version' => $update['version'],
                'update_type' => $update['type'],
                'branch' => $update['branch'],
                'changelog' => $update['changelog'] ?? '',
                'download_url' => $update['download_url'] ?? '',
                'file_hash' => $update['file_hash'] ?? '',
                'file_size' => $update['file_size'] ?? 0,
                'requirements' => json_encode($update['requirements'] ?? []),
                'status' => self::STATUS_AVAILABLE,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );
    }

    /**
     * Télécharge une mise à jour
     */
    public function download_update($version) {
        try {
            $update_info = $this->get_update_info($version);

            if (!$update_info) {
                throw new Exception('Informations de mise à jour introuvables');
            }

            $this->update_status($version, self::STATUS_DOWNLOADING);

            // Créer le dossier de téléchargement
            $download_dir = $this->get_update_download_dir();
            wp_mkdir_p($download_dir);

            $download_path = $download_dir . '/pdf-builder-' . $version . '.zip';

            // Télécharger le fichier
            $response = wp_remote_get($update_info['download_url'], [
                'timeout' => 300, // 5 minutes
                'stream' => true,
                'filename' => $download_path
            ]);

            if (is_wp_error($response)) {
                throw new Exception('Erreur de téléchargement: ' . $response->get_error_message());
            }

            // Vérifier l'intégrité du fichier
            if (!$this->verify_download_integrity($download_path, $update_info)) {
                unlink($download_path);
                throw new Exception('Intégrité du fichier compromise');
            }

            $this->update_status($version, self::STATUS_DOWNLOADED, [
                'download_path' => $download_path,
                'downloaded_at' => current_time('mysql')
            ]);

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info('Update downloaded successfully', [
                    'version' => $version,
                    'size' => filesize($download_path)
                ]);
            }

            return $download_path;

        } catch (Exception $e) {
            $this->update_status($version, self::STATUS_FAILED, [
                'error' => $e->getMessage()
            ]);
            $this->log_update_error('download_update', $e);
            throw $e;
        }
    }

    /**
     * Installe une mise à jour
     */
    public function install_update($version) {
        try {
            $update_info = $this->get_update_info($version);

            if (!$update_info || $update_info['status'] !== self::STATUS_DOWNLOADED) {
                throw new Exception('Mise à jour non prête pour l\'installation');
            }

            $this->update_status($version, self::STATUS_INSTALLING);

            // Créer une sauvegarde avant l'installation
            if (class_exists('PDF_Builder_Backup_Recovery_System')) {
                $backup_id = PDF_Builder_Backup_Recovery_System::get_instance()->create_emergency_backup();
            }

            // Extraire l'archive
            $extract_path = $this->extract_update_archive($update_info['download_path']);

            // Vérifier les prérequis
            $this->check_update_requirements($update_info);

            // Effectuer les migrations de base de données si nécessaire
            $this->run_database_migrations($version, $extract_path);

            // Copier les nouveaux fichiers
            $this->install_update_files($extract_path);

            // Mettre à jour la version
            $this->update_plugin_version($version);

            // Nettoyer les fichiers temporaires
            $this->cleanup_installation_files($extract_path, $update_info['download_path']);

            $this->update_status($version, self::STATUS_INSTALLED, [
                'installed_at' => current_time('mysql'),
                'backup_id' => $backup_id ?? null
            ]);

            // Legacy notification calls removed — log info for success
            PDF_Builder_Logger::get_instance()->info('Mise à jour installée avec succès: PDF Builder Pro mis à jour vers la version ' . $version, ['version' => $version, 'backup_id' => $backup_id ?? null]);

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info('Update installed successfully', [
                    'version' => $version,
                    'backup_id' => $backup_id ?? null
                ]);
            }

            return true;

        } catch (Exception $e) {
            $this->update_status($version, self::STATUS_FAILED, [
                'error' => $e->getMessage(),
                'installation_failed_at' => current_time('mysql')
            ]);
            $this->log_update_error('install_update', $e);
            throw $e;
        }
    }

    /**
     * Effectue une mise à jour automatique
     */
    public function perform_auto_update() {
        try {
            $update = $this->check_for_updates();

            if (!$update) {
                return false;
            }

            // Ne faire des mises à jour automatiques que pour les patchs et correctifs de sécurité
            $allowed_auto_updates = [self::UPDATE_TYPE_PATCH, self::UPDATE_TYPE_SECURITY];

            if (!in_array($update['type'], $allowed_auto_updates)) {
                return false;
            }

            // Télécharger et installer
            $this->download_update($update['version']);
            $this->install_update($update['version']);

            return true;

        } catch (Exception $e) {
            $this->log_update_error('perform_auto_update', $e);
            return false;
        }
    }

    /**
     * Extrait l'archive de mise à jour
     */
    private function extract_update_archive($archive_path) {
        $extract_path = sys_get_temp_dir() . '/pdf_builder_update_' . time();

        wp_mkdir_p($extract_path);

        $command = "unzip -q $archive_path -d $extract_path";
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            $this->delete_directory($extract_path);
            throw new Exception('Erreur lors de l\'extraction de l\'archive');
        }

        return $extract_path;
    }

    /**
     * Vérifie les prérequis de mise à jour
     */
    private function check_update_requirements($update_info) {
        $requirements = json_decode($update_info['requirements'], true);

        if (empty($requirements)) {
            return;
        }

        // Vérifier la version PHP
        if (isset($requirements['php_version'])) {
            if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
                throw new Exception("Version PHP requise: {$requirements['php_version']} ou supérieure");
            }
        }

        // Vérifier la version WordPress
        if (isset($requirements['wordpress_version'])) {
            if (version_compare(get_bloginfo('version'), $requirements['wordpress_version'], '<')) {
                throw new Exception("Version WordPress requise: {$requirements['wordpress_version']} ou supérieure");
            }
        }

        // Vérifier les extensions PHP
        if (isset($requirements['php_extensions'])) {
            foreach ($requirements['php_extensions'] as $extension) {
                if (!extension_loaded($extension)) {
                    throw new Exception("Extension PHP requise manquante: $extension");
                }
            }
        }
    }

    /**
     * Exécute les migrations de base de données
     */
    private function run_database_migrations($version, $extract_path) {
        $migration_file = $extract_path . '/migrations.php';

        if (!file_exists($migration_file)) {
            return;
        }

        include_once $migration_file;

        if (function_exists('pdf_builder_run_migrations')) {
            pdf_builder_run_migrations($version);
        }
    }

    /**
     * Installe les fichiers de mise à jour
     */
    private function install_update_files($extract_path) {
        $plugin_dir = PDF_BUILDER_PLUGIN_DIR;

        // Copier tous les fichiers sauf certains dossiers
        $exclude_dirs = ['backups', 'logs', 'config'];

        $this->copy_directory_recursive($extract_path, $plugin_dir, $exclude_dirs);
    }

    /**
     * Met à jour la version du plugin
     */
    private function update_plugin_version($version) {
        pdf_builder_update_option('pdf_builder_version', $version);

        // Mettre à jour la constante si elle est définie
        if (defined('PDF_BUILDER_VERSION')) {
            // En PHP, on ne peut pas redéfinir une constante, mais on peut la mettre à jour en base
        }
    }

    /**
     * Copie un dossier récursivement
     */
    private function copy_directory_recursive($source, $destination, $exclude = []) {
        if (!is_dir($source)) {
            return;
        }

        $items = scandir($source);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $source_path = $source . DIRECTORY_SEPARATOR . $item;
            $dest_path = $destination . DIRECTORY_SEPARATOR . $item;

            // Vérifier les exclusions
            if (in_array($item, $exclude)) {
                continue;
            }

            if (is_dir($source_path)) {
                wp_mkdir_p($dest_path);
                $this->copy_directory_recursive($source_path, $dest_path, $exclude);
            } else {
                copy($source_path, $dest_path);
            }
        }
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
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Nettoie les fichiers d'installation
     */
    private function cleanup_installation_files($extract_path, $archive_path) {
        $this->delete_directory($extract_path);
        unlink($archive_path);
    }

    /**
     * Vérifie l'intégrité du téléchargement
     */
    private function verify_download_integrity($file_path, $update_info) {
        if (!file_exists($file_path)) {
            return false;
        }

        // Vérifier la taille du fichier
        if (filesize($file_path) !== intval($update_info['file_size'])) {
            return false;
        }

        // Vérifier le hash du fichier
        if (!empty($update_info['file_hash'])) {
            $actual_hash = hash_file('sha256', $file_path);
            if ($actual_hash !== $update_info['file_hash']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Met à jour le statut d'une mise à jour
     */
    private function update_status($version, $status, $additional_data = []) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_updates';

        $update_data = ['status' => $status] + $additional_data;

        $wpdb->update(
            $table,
            $update_data,
            ['version' => $version],
            array_fill(0, count($update_data), '%s'),
            ['%s']
        );
    }

    /**
     * Obtient les informations d'une mise à jour
     */
    private function get_update_info($version) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_updates';

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table WHERE version = %s
        ", $version), ARRAY_A);
    }

    /**
     * Obtient le dossier de téléchargement des mises à jour
     */
    private function get_update_download_dir() {
        $upload_dir = wp_upload_dir();
        $update_dir = $upload_dir['basedir'] . '/pdf-builder-updates/';

        wp_mkdir_p($update_dir);

        return $update_dir;
    }

    /**
     * Obtient le token API pour l'authentification
     */
    private function get_api_token() {
        $token = pdf_builder_get_option('pdf_builder_api_token');

        if (!$token) {
            $token = wp_generate_password(32, false);
            pdf_builder_update_option('pdf_builder_api_token', $token);
        }

        return $token;
    }

    /**
     * Effectue une vérification hebdomadaire des mises à jour
     */
    public function perform_weekly_update_check() {
        $update = $this->check_for_updates();

        if ($update) {
            // Legacy notification calls removed — log info
            PDF_Builder_Logger::get_instance()->info('Mise à jour disponible: Une nouvelle version de PDF Builder Pro est disponible: ' . $update['version'], ['update' => $update]);
        }
    }

    /**
     * Nettoie les anciennes versions
     */
    public function cleanup_old_versions() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_updates';
        $retention_months = pdf_builder_config('update_retention_months', 6);

        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM $table
            WHERE status = 'installed'
            AND installed_at < DATE_SUB(NOW(), INTERVAL %d MONTH)
        ", $retention_months));

        if ($deleted > 0 && class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->info("Old update records cleaned up: $deleted records removed");
        }
    }

    /**
     * Injecte les informations de mise à jour dans WordPress
     */
    public function inject_plugin_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $update = $this->check_for_updates();

        if ($update) {
            $plugin_file = plugin_basename(PDF_BUILDER_PLUGIN_FILE);

            $transient->response[$plugin_file] = (object) [
                'slug' => 'pdf-builder-pro',
                'new_version' => $update['version'],
                'url' => 'https://pdf-builder-pro.com',
                'package' => $update['download_url'],
                'tested' => get_bloginfo('version'),
                'requires_php' => $update['requirements']['php_version'] ?? '7.4',
                'compatibility' => []
            ];
        }

        return $transient;
    }

    /**
     * Fournit les informations détaillées du plugin
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== 'pdf-builder-pro') {
            return $result;
        }

        $update = $this->check_for_updates();

        if ($update) {
            return (object) [
                'name' => 'PDF Builder Pro',
                'slug' => 'pdf-builder-pro',
                'version' => $update['version'],
                'author' => '<a href="https://pdf-builder-pro.com">PDF Builder Team</a>',
                'author_profile' => 'https://pdf-builder-pro.com',
                'contributors' => [],
                'requires' => $update['requirements']['wordpress_version'] ?? '5.0',
                'tested' => get_bloginfo('version'),
                'requires_php' => $update['requirements']['php_version'] ?? '7.4',
                'compatibility' => [],
                'rating' => 100,
                'ratings' => [],
                'num_ratings' => 0,
                'support_threads' => 0,
                'support_threads_resolved' => 0,
                'active_installs' => 0,
                'last_updated' => $update['released_at'] ?? current_time('mysql'),
                'added' => '2023-01-01',
                'homepage' => 'https://pdf-builder-pro.com',
                'sections' => [
                    'description' => $update['description'] ?? 'Advanced PDF Builder for WordPress',
                    'changelog' => $update['changelog'] ?? 'See changelog for details'
                ],
                'download_link' => $update['download_url'],
                'tags' => ['pdf', 'builder', 'generator'],
                'stable_tag' => $update['version'],
                'versions' => [],
                'business_model' => false,
                'repository_url' => '',
                'commercial' => true
            ];
        }

        return $result;
    }

    /**
     * Log une erreur de mise à jour
     */
    private function log_update_error($operation, $exception) {
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->error("Update operation failed: $operation", [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        } else {

        }
    }

    /**
     * AJAX - Vérifie les mises à jour
     */
    public function check_updates_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('update_plugins')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $update = $this->check_for_updates();

            if ($update) {
                wp_send_json_success([
                    'message' => 'Mise à jour trouvée',
                    'update' => $update
                ]);
            } else {
                wp_send_json_success([
                    'message' => 'Aucune mise à jour disponible',
                    'update' => null
                ]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la vérification: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Installe une mise à jour
     */
    public function install_update_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('update_plugins')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $version = sanitize_text_field($_POST['version'] ?? '');

            if (empty($version)) {
                wp_send_json_error(['message' => 'Version manquante']);
                return;
            }

            // Télécharger d'abord
            $this->download_update($version);

            // Puis installer
            $this->install_update($version);

            wp_send_json_success([
                'message' => 'Mise à jour installée avec succès',
                'version' => $version
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'installation: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut des mises à jour
     */
    public function get_update_status_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('update_plugins')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $updates = $this->get_available_updates();

            wp_send_json_success([
                'message' => 'Statut récupéré',
                'updates' => $updates,
                'current_version' => PDF_BUILDER_VERSION,
                'branch' => $this->current_branch
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient la liste des mises à jour disponibles
     */
    public function get_available_updates() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_updates';

        return $wpdb->get_results("
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT 10
        ", ARRAY_A);
    }
}

// Fonctions globales
function pdf_builder_update_system() {
    return PDF_Builder_Auto_Update_System::get_instance();
}

function pdf_builder_check_auto_updates() {
    return PDF_Builder_Auto_Update_System::get_instance()->check_for_updates();
}

function pdf_builder_install_auto_update($version) {
    return PDF_Builder_Auto_Update_System::get_instance()->install_update($version);
}

function pdf_builder_get_updates() {
    return PDF_Builder_Auto_Update_System::get_instance()->get_available_updates();
}

// Initialiser le système de mise à jour automatique
add_action('plugins_loaded', function() {
    PDF_Builder_Auto_Update_System::get_instance();
});




