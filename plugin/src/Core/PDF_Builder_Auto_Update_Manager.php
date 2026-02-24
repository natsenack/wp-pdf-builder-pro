<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * PDF Builder Pro - Gestionnaire de mises à jour automatiques
 * Gère les mises à jour automatiques, correctifs de sécurité et maintenance
 */

class PDF_Builder_Auto_Update_Manager {
    private static $instance = null;

    // Types de mises à jour
    const UPDATE_TYPE_CORE = 'core';
    const UPDATE_TYPE_SECURITY = 'security';
    const UPDATE_TYPE_FEATURE = 'feature';
    const UPDATE_TYPE_BUGFIX = 'bugfix';

    // Statuts de mise à jour
    const STATUS_AVAILABLE = 'available';
    const STATUS_DOWNLOADING = 'downloading';
    const STATUS_DOWNLOADED = 'downloaded';
    const STATUS_INSTALLING = 'installing';
    const STATUS_INSTALLED = 'installed';
    const STATUS_FAILED = 'failed';

    // Fréquences de vérification
    const CHECK_HOURLY = 'hourly';
    const CHECK_DAILY = 'daily';
    const CHECK_WEEKLY = 'weekly';

    // Clés de stockage
    const OPTION_UPDATE_SETTINGS = 'pdf_builder_update_settings';
    const OPTION_UPDATE_STATUS = 'pdf_builder_update_status';
    const OPTION_UPDATE_HISTORY = 'pdf_builder_update_history';
    const OPTION_SECURITY_PATCHES = 'pdf_builder_security_patches';

    // URL de l'API de mises à jour
    const UPDATE_API_URL = 'https://api.pdfbuilderpro.com/v1/updates';
    const SECURITY_API_URL = 'https://api.pdfbuilderpro.com/v1/security';

    // Cache
    private $update_settings = [];
    private $update_status = [];
    private $update_history = [];
    private $security_patches = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_update_data();
    }

    private function init_hooks() {
        // Actions AJAX
        add_action('wp_ajax_pdf_builder_check_updates', [$this, 'check_updates_ajax']);
        add_action('wp_ajax_pdf_builder_install_update', [$this, 'install_update_ajax']);
        add_action('wp_ajax_pdf_builder_get_update_status', [$this, 'get_update_status_ajax']);
        add_action('wp_ajax_pdf_builder_save_update_settings', [$this, 'save_update_settings_ajax']);

        // Actions d'administration
        add_action('admin_init', [$this, 'register_update_settings']);
        add_action('admin_menu', [$this, 'add_update_menu']);
        add_action('admin_notices', [$this, 'display_update_notices']);

        // Actions programmées
        add_action('pdf_builder_check_updates', [$this, 'check_for_updates']);
        add_action('pdf_builder_auto_update', [$this, 'perform_auto_update']);
        add_action('pdf_builder_security_check', [$this, 'check_security_patches']);
        add_action('pdf_builder_update_cleanup', [$this, 'cleanup_update_files']);

        // Filtres WordPress
        add_filter('pre_set_site_transient_update_plugins', [$this, 'inject_plugin_update']);
        add_filter('plugins_api', [$this, 'inject_plugin_info'], 10, 3);

        // Actions de plugin
        add_action('upgrader_process_complete', [$this, 'handle_update_complete'], 10, 2);

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_old_updates']);
    }

    /**
     * Charge les données de mise à jour
     */
    private function load_update_data() {
        $this->update_settings = get_option(self::OPTION_UPDATE_SETTINGS, $this->get_default_settings());
        $this->update_status = get_option(self::OPTION_UPDATE_STATUS, []);
        $this->update_history = get_option(self::OPTION_UPDATE_HISTORY, []);
        $this->security_patches = get_option(self::OPTION_SECURITY_PATCHES, []);
    }

    /**
     * Obtient les paramètres par défaut
     */
    private function get_default_settings() {
        return [
            'auto_update_enabled' => false,
            'update_frequency' => self::CHECK_DAILY,
            'update_types' => [
                self::UPDATE_TYPE_SECURITY => true,
                self::UPDATE_TYPE_BUGFIX => true,
                self::UPDATE_TYPE_CORE => false,
                self::UPDATE_TYPE_FEATURE => false
            ],
            'backup_before_update' => true,
            'notify_admin_updates' => true,
            'notify_admin_security' => true,
            'maintenance_mode' => false,
            'rollback_enabled' => true,
            'max_rollback_versions' => 3
        ];
    }

    /**
     * Enregistre les paramètres de mise à jour
     */
    public function register_update_settings() {
        register_setting(
            'pdf_builder_update_settings',
            self::OPTION_UPDATE_SETTINGS,
            [$this, 'sanitize_update_settings']
        );
    }

    /**
     * Nettoie les paramètres de mise à jour
     */
    public function sanitize_update_settings($settings) {
        $defaults = $this->get_default_settings();

        return [
            'auto_update_enabled' => isset($settings['auto_update_enabled']),
            'update_frequency' => in_array($settings['update_frequency'], [self::CHECK_HOURLY, self::CHECK_DAILY, self::CHECK_WEEKLY])
                ? $settings['update_frequency'] : $defaults['update_frequency'],
            'update_types' => array_map('boolval', $settings['update_types'] ?? []),
            'backup_before_update' => isset($settings['backup_before_update']),
            'notify_admin_updates' => isset($settings['notify_admin_updates']),
            'notify_admin_security' => isset($settings['notify_admin_security']),
            'maintenance_mode' => isset($settings['maintenance_mode']),
            'rollback_enabled' => isset($settings['rollback_enabled']),
            'max_rollback_versions' => intval($settings['max_rollback_versions'] ?? $defaults['max_rollback_versions'])
        ];
    }

    /**
     * Ajoute le menu de mise à jour
     */
    public function add_update_menu() {
        add_submenu_page(
            'pdf-builder-settings',
            pdf_builder_translate('Mises à jour automatiques', 'update'),
            pdf_builder_translate('Mises à jour', 'update'),
            'manage_options',
            'pdf-builder-updates',
            [$this, 'render_update_page']
        );
    }

    /**
     * Rend la page de mise à jour
     */
    public function render_update_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(pdf_builder_translate('Accès refusé', 'update')));
        }

        $settings = $this->update_settings;
        $status = $this->get_update_status();
        $history = $this->get_update_history(10);
        $security_patches = $this->get_security_patches();

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/update-management.php';
    }

    /**
     * Vérifie les mises à jour disponibles
     */
    public function check_for_updates() {
        try {
            // Vérifier la licence
            if (!function_exists('pdf_builder_is_license_active') || !pdf_builder_is_license_active()) {
                return;
            }

            // Appeler l'API de mises à jour
            $response = $this->call_update_api('check', [
                'current_version' => PDF_BUILDER_VERSION,
                'site_url' => get_site_url(),
                'license_key' => pdf_builder_get_option('pdf_builder_license_key'),
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version')
            ]);

            if (!$response['success']) {
                throw new Exception($response['message'] ?? pdf_builder_translate('Erreur lors de la vérification des mises à jour', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            $updates = $response['data']['updates'] ?? [];

            // Filtrer selon les paramètres
            $filtered_updates = $this->filter_updates_by_settings($updates);

            // Mettre à jour le statut
            $this->update_status = [
                'last_check' => time(),
                'available_updates' => $filtered_updates,
                'next_check' => $this->get_next_check_time()
            ];

            update_option(self::OPTION_UPDATE_STATUS, $this->update_status);

            // Vérifier les correctifs de sécurité
            $this->check_security_patches();

            // Logger la vérification
            error_log('Update check completed. Updates found: ' . count($filtered_updates));

        } catch (Exception $e) {
            // Logger l'erreur
            error_log('Error checking for updates: ' . $e->getMessage());
        }
    }

    /**
     * Filtre les mises à jour selon les paramètres
     */
    private function filter_updates_by_settings($updates) {
        $filtered = [];

        foreach ($updates as $update) {
            $type = $update['type'] ?? self::UPDATE_TYPE_CORE;

            if (isset($this->update_settings['update_types'][$type]) && $this->update_settings['update_types'][$type]) {
                $filtered[] = $update;
            }
        }

        return $filtered;
    }

    /**
     * Vérifie les correctifs de sécurité
     */
    public function check_security_patches() {
        try {
            $response = $this->call_security_api('check', [
                'current_version' => PDF_BUILDER_VERSION,
                'site_url' => get_site_url(),
                'license_key' => pdf_builder_get_option('pdf_builder_license_key')
            ]);

            if ($response['success']) {
                $this->security_patches = $response['data']['patches'] ?? [];
                update_option(self::OPTION_SECURITY_PATCHES, $this->security_patches);
            }

        } catch (Exception $e) {
            // Logger l'erreur silencieusement
            error_log('Security patch check failed: ' . $e->getMessage());
        }
    }

    /**
     * Installe une mise à jour
     */
    public function install_update($update_id) {
        try {
            if (empty($this->update_status['available_updates'])) {
                throw new Exception(pdf_builder_translate('Aucune mise à jour disponible', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            $update = $this->find_update_by_id($update_id);

            if (!$update) {
                throw new Exception(pdf_builder_translate('Mise à jour introuvable', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            // Vérifier les prérequis
            $this->check_update_prerequisites($update);

            // Créer une sauvegarde si activé
            if ($this->update_settings['backup_before_update']) {
                $this->create_update_backup($update);
            }

            // Télécharger la mise à jour
            $this->update_status['current_update'] = [
                'id' => $update_id,
                'status' => self::STATUS_DOWNLOADING,
                'started_at' => time()
            ];
            update_option(self::OPTION_UPDATE_STATUS, $this->update_status);

            $download_url = $this->get_update_download_url($update);

            // Télécharger et installer
            $result = $this->download_and_install_update($download_url, $update);

            if ($result['success']) {
                // Marquer comme installé
                $this->mark_update_installed($update, $result);

                // Nettoyer les fichiers temporaires
                $this->cleanup_update_files();

                // Logger l'installation
                error_log('Update installed successfully: ' . $update_id);

                return [
                    'success' => true,
                    'message' => pdf_builder_translate('Mise à jour installée avec succès', 'update'),
                    'update' => $update
                ];
            } else {
                throw new Exception($result['message'] ?? pdf_builder_translate('Erreur lors de l\'installation', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

        } catch (Exception $e) {
            // Marquer comme échoué
            if (isset($this->update_status['current_update'])) {
                $this->update_status['current_update']['status'] = self::STATUS_FAILED;
                $this->update_status['current_update']['error'] = $e->getMessage();
                update_option(self::OPTION_UPDATE_STATUS, $this->update_status);
            }

            // Logger l'erreur
            error_log('Update installation failed for ' . $update_id . ': ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Effectue une mise à jour automatique
     */
    public function perform_auto_update() {
        if (!$this->update_settings['auto_update_enabled']) {
            return;
        }

        $available_updates = $this->update_status['available_updates'] ?? [];

        foreach ($available_updates as $update) {
            // Ne mettre à jour automatiquement que les correctifs de sécurité et bug fixes
            if (in_array($update['type'], [self::UPDATE_TYPE_SECURITY, self::UPDATE_TYPE_BUGFIX])) {
                $result = $this->install_update($update['id']);

                if ($result['success']) {
                    // Notifier l'administrateur
                    if ($this->update_settings['notify_admin_updates']) {
                        $this->notify_admin_update_installed($update);
                    }
                }
            }
        }
    }

    /**
     * Trouve une mise à jour par ID
     */
    private function find_update_by_id($update_id) {
        $updates = $this->update_status['available_updates'] ?? [];

        foreach ($updates as $update) {
            if ($update['id'] === $update_id) {
                return $update;
            }
        }

        return null;
    }

    /**
     * Vérifie les prérequis d'une mise à jour
     */
    private function check_update_prerequisites($update) {
        // Vérifier la version PHP
        if (isset($update['requirements']['php']) && version_compare(PHP_VERSION, $update['requirements']['php'], '<')) {
            throw new Exception(sprintf(pdf_builder_translate('Version PHP requise : %s (actuelle : %s)', 'update'), $update['requirements']['php'], PHP_VERSION)); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        // Vérifier la version WordPress
        if (isset($update['requirements']['wp']) && version_compare(get_bloginfo('version'), $update['requirements']['wp'], '<')) {
            throw new Exception(sprintf(pdf_builder_translate('Version WordPress requise : %s', 'update'), $update['requirements']['wp'])); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        // Vérifier l'espace disque
        if (isset($update['size']) && $this->get_free_disk_space() < $update['size'] * 2) {
            throw new Exception(pdf_builder_translate('Espace disque insuffisant', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }
    }

    /**
     * Crée une sauvegarde avant mise à jour
     */
    private function create_update_backup($update) {
        if (!class_exists('PDF_Builder_Backup_Recovery_System')) {
            return;
        }

        $backup_system = PDF_Builder_Backup_Recovery_System::get_instance();

        $backup_id = $backup_system->create_backup([
            'type' => 'pre_update',
            'update_id' => $update['id'],
            'update_version' => $update['version'],
            'reason' => 'Sauvegarde automatique avant mise à jour'
        ]);

        return $backup_id;
    }

    /**
     * Obtient l'URL de téléchargement d'une mise à jour
     */
    private function get_update_download_url($update) {
        $response = $this->call_update_api('download_url', [
            'update_id' => $update['id'],
            'license_key' => pdf_builder_get_option('pdf_builder_license_key'),
            'site_url' => get_site_url()
        ]);

        if (!$response['success']) {
            throw new Exception($response['message'] ?? pdf_builder_translate('URL de téléchargement indisponible', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        return $response['data']['download_url'];
    }

    /**
     * Télécharge et installe une mise à jour
     */
    private function download_and_install_update($download_url, $update) {
        // Créer un répertoire temporaire
        $temp_dir = $this->create_temp_directory();

        try {
            // Télécharger le fichier
            $zip_file = $temp_dir . '/update.zip';
            $download_result = $this->download_file($download_url, $zip_file);

            if (!$download_result['success']) {
                throw new Exception($download_result['message']); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            // Extraire l'archive
            $extract_result = $this->extract_zip($zip_file, $temp_dir);

            if (!$extract_result['success']) {
                throw new Exception($extract_result['message']); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            // Mettre à jour le statut
            $this->update_status['current_update']['status'] = self::STATUS_INSTALLING;
            update_option(self::OPTION_UPDATE_STATUS, $this->update_status);

            // Installer les fichiers
            $install_result = $this->install_update_files($temp_dir, $update);

            if (!$install_result['success']) {
                throw new Exception($install_result['message']); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            // Nettoyer
            $this->cleanup_temp_directory($temp_dir);

            return [
                'success' => true,
                'installed_files' => $install_result['installed_files']
            ];

        } catch (Exception $e) {
            $this->cleanup_temp_directory($temp_dir);
            throw $e;
        }
    }

    /**
     * Marque une mise à jour comme installée
     */
    private function mark_update_installed($update, $result) {
        // Ajouter à l'historique
        $this->update_history[] = [
            'id' => $update['id'],
            'version' => $update['version'],
            'type' => $update['type'],
            'installed_at' => time(),
            'installed_by' => get_current_user_id(),
            'backup_id' => $result['backup_id'] ?? null,
            'installed_files' => $result['installed_files'] ?? []
        ];

        update_option(self::OPTION_UPDATE_HISTORY, $this->update_history);

        // Supprimer des mises à jour disponibles
        if (isset($this->update_status['available_updates'])) {
            $this->update_status['available_updates'] = array_filter(
                $this->update_status['available_updates'],
                function($u) use ($update) {
                    return $u['id'] !== $update['id'];
                }
            );
        }

        // Nettoyer le statut actuel
        unset($this->update_status['current_update']);

        update_option(self::OPTION_UPDATE_STATUS, $this->update_status);
    }

    /**
     * Obtient le statut des mises à jour
     */
    public function get_update_status() {
        return array_merge([
            'last_check' => 0,
            'available_updates' => [],
            'current_update' => null,
            'next_check' => $this->get_next_check_time()
        ], $this->update_status);
    }

    /**
     * Obtient l'historique des mises à jour
     */
    public function get_update_history($limit = null) {
        $history = array_reverse($this->update_history);

        if ($limit) {
            $history = array_slice($history, 0, $limit);
        }

        return $history;
    }

    /**
     * Obtient les correctifs de sécurité
     */
    public function get_security_patches() {
        return $this->security_patches;
    }

    /**
     * Obtient le prochain temps de vérification
     */
    private function get_next_check_time() {
        $frequency = $this->update_settings['update_frequency'];

        switch ($frequency) {
            case self::CHECK_HOURLY:
                return time() + HOUR_IN_SECONDS;
            case self::CHECK_WEEKLY:
                return time() + WEEK_IN_SECONDS;
            case self::CHECK_DAILY:
            default:
                return time() + DAY_IN_SECONDS;
        }
    }

    /**
     * Obtient l'espace disque libre
     */
    private function get_free_disk_space() {
        $free_space = disk_free_space(PDF_BUILDER_PLUGIN_DIR);

        return $free_space ?: 0;
    }

    /**
     * Crée un répertoire temporaire
     */
    private function create_temp_directory() {
        $temp_dir = WP_CONTENT_DIR . '/pdf-builder-updates/' . time() . '_' . wp_generate_password(8, false);

        if (!wp_mkdir_p($temp_dir)) {
            throw new Exception(pdf_builder_translate('Impossible de créer le répertoire temporaire', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        return $temp_dir;
    }

    /**
     * Nettoie un répertoire temporaire
     */
    private function cleanup_temp_directory($temp_dir) {
        if (is_dir($temp_dir)) {
            $this->delete_directory($temp_dir);
        }
    }

    /**
     * Télécharge un fichier
     */
    private function download_file($url, $destination) {
        $response = wp_remote_get($url, [
            'timeout' => 300,
            'stream' => true,
            'filename' => $destination
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => pdf_builder_translate('Erreur de téléchargement', 'update') . ': ' . $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            return [
                'success' => false,
                'message' => sprintf(pdf_builder_translate('Erreur HTTP %d', 'update'), $status_code)
            ];
        }

        return ['success' => true];
    }

    /**
     * Extrait une archive ZIP
     */
    private function extract_zip($zip_file, $destination) {
        if (!class_exists('ZipArchive')) {
            return [
                'success' => false,
                'message' => pdf_builder_translate('ZipArchive non disponible', 'update')
            ];
        }

        $zip = new ZipArchive();

        if ($zip->open($zip_file) !== true) {
            return [
                'success' => false,
                'message' => pdf_builder_translate('Impossible d\'ouvrir l\'archive', 'update')
            ];
        }

        if (!$zip->extractTo($destination)) {
            $zip->close();
            return [
                'success' => false,
                'message' => pdf_builder_translate('Erreur lors de l\'extraction', 'update')
            ];
        }

        $zip->close();

        return ['success' => true];
    }

    /**
     * Installe les fichiers de mise à jour
     */
    private function install_update_files($source_dir, $update) {
        $installed_files = [];
        $plugin_dir = PDF_BUILDER_PLUGIN_DIR;

        // Copier les fichiers
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative_path = str_replace($source_dir . '/', '', $file->getPathname());
                $destination = $plugin_dir . '/' . $relative_path;

                // Créer le répertoire de destination
                $dest_dir = dirname($destination);
                if (!is_dir($dest_dir)) {
                    wp_mkdir_p($dest_dir);
                }

                // Copier le fichier
                if (copy($file->getPathname(), $destination)) {
                    $installed_files[] = $relative_path;
                } else {
                    throw new Exception(sprintf(pdf_builder_translate('Erreur lors de la copie de %s', 'update'), $relative_path)); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
                }
            }
        }

        return [
            'success' => true,
            'installed_files' => $installed_files
        ];
    }

    /**
     * Supprime un répertoire récursivement
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }

    /**
     * Appelle l'API de mises à jour
     */
    private function call_update_api($action, $data = []) {
        return $this->call_api(self::UPDATE_API_URL . '/' . $action, $data);
    }

    /**
     * Appelle l'API de sécurité
     */
    private function call_security_api($action, $data = []) {
        return $this->call_api(self::SECURITY_API_URL . '/' . $action, $data);
    }

    /**
     * Appelle une API générique
     */
    private function call_api($url, $data = []) {
        $args = [
            'method' => 'POST',
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'PDF Builder Pro/' . PDF_BUILDER_VERSION . '; ' . get_site_url()
            ],
            'body' => wp_json_encode($data),
            'sslverify' => true
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response) && $response !== null) {
            throw new Exception(pdf_builder_translate('Erreur de connexion à l\'API', 'update') . ': ' . $response->get_error_message()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        if ($response === false) {
            throw new Exception(pdf_builder_translate('Erreur de connexion à l\'API', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(pdf_builder_translate('Réponse API invalide', 'update')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        return $data;
    }

    /**
     * Injecte les informations de mise à jour dans WordPress
     */
    public function inject_plugin_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_file = plugin_basename(PDF_BUILDER_PLUGIN_FILE);

        if (isset($this->update_status['available_updates'])) {
            $latest_update = null;

            foreach ($this->update_status['available_updates'] as $update) {
                if ($update['type'] === self::UPDATE_TYPE_CORE) {
                    if (!$latest_update || version_compare($update['version'], $latest_update['version'], '>')) {
                        $latest_update = $update;
                    }
                }
            }

            if ($latest_update) {
                $transient->response[$plugin_file] = (object) [
                    'slug' => 'pdf-builder-pro',
                    'new_version' => $latest_update['version'],
                    'url' => 'https://pdfbuilderpro.com',
                    'package' => $this->get_update_download_url($latest_update),
                    'tested' => $latest_update['tested_wp'] ?? get_bloginfo('version'),
                    'requires' => $latest_update['requires_wp'] ?? '5.0',
                    'requires_php' => $latest_update['requires_php'] ?? '7.2'
                ];
            }
        }

        return $transient;
    }

    /**
     * Injecte les informations du plugin dans l'API WordPress
     */
    public function inject_plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== 'pdf-builder-pro') {
            return $result;
        }

        // Retourner les informations du plugin depuis l'API
        try {
            $response = $this->call_update_api('info', [
                'license_key' => pdf_builder_get_option('pdf_builder_license_key'),
                'site_url' => get_site_url()
            ]);

            if ($response['success']) {
                return (object) $response['data'];
            }
        } catch (Exception $e) {
            // Retourner des informations de base en cas d'erreur
        }

        return $result;
    }

    /**
     * Gère la completion d'une mise à jour
     */
    public function handle_update_complete($upgrader, $options) {
        if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
            return;
        }

        $plugin_file = plugin_basename(PDF_BUILDER_PLUGIN_FILE);

        if (in_array($plugin_file, $options['plugins'])) {
            // Recharger les données de mise à jour
            $this->load_update_data();

            // Logger la mise à jour
            error_log('PDF Builder Pro updated to version: ' . PDF_BUILDER_VERSION);

            // Vérifier s'il y a des tâches post-mise à jour
            $this->run_post_update_tasks();
        }
    }

    /**
     * Exécute les tâches post-mise à jour
     */
    private function run_post_update_tasks() {
        // Recharger les classes si nécessaire
        // Nettoyer les caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Vider les caches transients
        delete_transient('pdf_builder_update_check');
    }

    /**
     * Notifie l'administrateur d'une mise à jour installée
     */
    private function notify_admin_update_installed($update) {
        $admin_email = get_option('admin_email');
        $subject = sprintf('PDF Builder Pro - Mise à jour %s installée', $update['version']);
        $message = sprintf(
            "Une mise à jour de PDF Builder Pro a été installée avec succès.\n\n" .
            "Version: %s\n" .
            "Type: %s\n" .
            "Installée le: %s\n\n" .
            "Le plugin fonctionne maintenant avec la nouvelle version.",
            $update['version'],
            $update['type'],
            date('d/m/Y H:i:s', time())
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Nettoie les fichiers de mise à jour
     */
    public function cleanup_update_files() {
        $update_dir = WP_CONTENT_DIR . '/pdf-builder-updates/';

        if (is_dir($update_dir)) {
            $this->delete_directory($update_dir);
        }
    }

    /**
     * Nettoie les anciennes mises à jour
     */
    public function cleanup_old_updates() {
        // Garder seulement les 10 dernières mises à jour dans l'historique
        if (count($this->update_history) > 10) {
            $this->update_history = array_slice($this->update_history, -10);
            update_option(self::OPTION_UPDATE_HISTORY, $this->update_history);
        }

        // Supprimer les sauvegardes de rollback trop anciennes
        if ($this->update_settings['rollback_enabled'] && class_exists('PDF_Builder_Backup_Recovery_System')) {
            $backup_system = PDF_Builder_Backup_Recovery_System::get_instance();
            $backup_system->cleanup_old_backups('pre_update', $this->update_settings['max_rollback_versions']);
        }
    }

    /**
     * Affiche les notifications de mise à jour
     */
    public function display_update_notices() {
        $status = $this->get_update_status();

        // Notifications de mises à jour disponibles
        if (!empty($status['available_updates'])) {
            $count = count($status['available_updates']);
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p>';
            // translators: %d: number of available plugin updates
            echo esc_html(sprintf(_n(
                '%d mise à jour est disponible pour PDF Builder Pro.',
                '%d mises à jour sont disponibles pour PDF Builder Pro.',
                $count,
                'pdf-builder-pro'
            ), $count));
            echo ' <a href="' . esc_url(admin_url('admin.php?page=pdf-builder-updates')) . '">' . esc_html(pdf_builder_translate('Voir les détails', 'update')) . '</a>';
            echo '</p>';
            echo '</div>';
        }

        // Notifications de correctifs de sécurité
        if (!empty($this->security_patches)) {
            $count = count($this->security_patches);
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>';
            // translators: %d: number of available security patches
            echo esc_html(sprintf(_n(
                '%d correctif de sécurité est disponible.',
                '%d correctifs de sécurité sont disponibles.',
                $count,
                'pdf-builder-pro'
            ), $count));
            echo ' <a href="' . esc_url(admin_url('admin.php?page=pdf-builder-updates')) . '">' . esc_html(pdf_builder_translate('Installer maintenant', 'update')) . '</a>';
            echo '</p>';
            echo '</div>';
        }

        // Notification de mise à jour en cours
        if (isset($status['current_update'])) {
            $current = $status['current_update'];
            $status_text = '';

            switch ($current['status']) {
                case self::STATUS_DOWNLOADING:
                    $status_text = pdf_builder_translate('Téléchargement en cours...', 'update');
                    break;
                case self::STATUS_INSTALLING:
                    $status_text = pdf_builder_translate('Installation en cours...', 'update');
                    break;
                case self::STATUS_FAILED:
                    $status_text = pdf_builder_translate('Échec de l\'installation', 'update');
                    break;
            }

            if ($status_text) {
                echo '<div class="notice notice-info">';
                echo '<p>' . esc_html(sprintf(pdf_builder_translate('Mise à jour PDF Builder Pro : %s', 'update'), $status_text)) . '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * AJAX - Vérifie les mises à jour
     */
    public function check_updates_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $this->check_for_updates();

            $status = $this->get_update_status();

            wp_send_json_success([
                'message' => pdf_builder_translate('Vérification terminée', 'update'),
                'status' => $status
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Installe une mise à jour
     */
    public function install_update_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $update_id = sanitize_text_field($_POST['update_id'] ?? '');

            if (empty($update_id)) {
                wp_send_json_error(['message' => 'ID de mise à jour manquant']);
                return;
            }

            $result = $this->install_update($update_id);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'update' => $result['update']
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut des mises à jour
     */
    public function get_update_status_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $status = $this->get_update_status();
            $history = $this->get_update_history(5);
            $security_patches = $this->get_security_patches();

            wp_send_json_success([
                'status' => $status,
                'history' => $history,
                'security_patches' => $security_patches
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Sauvegarde les paramètres de mise à jour
     */
    public function save_update_settings_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $settings = $_POST['settings'] ?? [];

            $sanitized_settings = $this->sanitize_update_settings($settings);
            update_option(self::OPTION_UPDATE_SETTINGS, $sanitized_settings);

            $this->update_settings = $sanitized_settings;

            // Reprogrammer les tâches selon la nouvelle fréquence
            $this->schedule_update_checks();

            wp_send_json_success([
                'message' => pdf_builder_translate('Paramètres sauvegardés', 'update')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Programme les vérifications de mise à jour
     */
    private function schedule_update_checks() {
        $frequency = $this->update_settings['update_frequency'];

        // Supprimer les anciennes programmations
        wp_clear_scheduled_hook('pdf_builder_check_updates');
        wp_clear_scheduled_hook('pdf_builder_auto_update');
        wp_clear_scheduled_hook('pdf_builder_security_check');

        // Programmer selon la fréquence
        switch ($frequency) {
            case self::CHECK_HOURLY:
                wp_schedule_event(time(), 'hourly', 'pdf_builder_check_updates');
                wp_schedule_event(time(), 'hourly', 'pdf_builder_auto_update');
                wp_schedule_event(time(), 'hourly', 'pdf_builder_security_check');
                break;
            case self::CHECK_WEEKLY:
                wp_schedule_event(time(), 'weekly', 'pdf_builder_check_updates');
                wp_schedule_event(time(), 'weekly', 'pdf_builder_auto_update');
                wp_schedule_event(time(), 'daily', 'pdf_builder_security_check');
                break;
            case self::CHECK_DAILY:
            default:
                wp_schedule_event(time(), 'daily', 'pdf_builder_check_updates');
                wp_schedule_event(time(), 'daily', 'pdf_builder_auto_update');
                wp_schedule_event(time(), 'daily', 'pdf_builder_security_check');
                break;
        }
    }
}

// Fonctions globales
function pdf_builder_update_manager() {
    return PDF_Builder_Auto_Update_Manager::get_instance();
}

function pdf_builder_check_updates() {
    return PDF_Builder_Auto_Update_Manager::get_instance()->check_for_updates();
}

function pdf_builder_install_update($update_id) {
    return PDF_Builder_Auto_Update_Manager::get_instance()->install_update($update_id);
}

function pdf_builder_get_update_status() {
    return PDF_Builder_Auto_Update_Manager::get_instance()->get_update_status();
}

function pdf_builder_get_update_history($limit = null) {
    return PDF_Builder_Auto_Update_Manager::get_instance()->get_update_history($limit);
}

function pdf_builder_get_security_patches() {
    return PDF_Builder_Auto_Update_Manager::get_instance()->get_security_patches();
}

// Initialiser le système de mise à jour automatique
add_action('plugins_loaded', function() {
    PDF_Builder_Auto_Update_Manager::get_instance();
});




