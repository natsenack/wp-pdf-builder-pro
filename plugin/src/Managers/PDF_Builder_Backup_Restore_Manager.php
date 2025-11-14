<?php

namespace WP_PDF_Builder_Pro\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * PDF Builder Pro - Backup & Restore Manager
 * Gestionnaire de sauvegarde et restauration des templates
 */

class PdfBuilderBackupRestoreManager
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Version du format de sauvegarde
     */
    const BACKUP_VERSION = '1.0';

    /**
     * Dossier de sauvegarde
     */
    private $backup_dir;

    /**
     * Constructeur privé
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation
     */
    private function init()
    {
        $upload_dir = wp_upload_dir();
        $this->backup_dir = $upload_dir['basedir'] . '/pdf-builder-backups/';

        // Créer le dossier de sauvegarde s'il n'existe pas
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
        }

        // Hooks AJAX
        add_action('wp_ajax_pdf_builder_export_templates', [$this, 'exportTemplates']);
        add_action('wp_ajax_pdf_builder_import_templates', [$this, 'importTemplates']);
        add_action('wp_ajax_pdf_builder_create_backup', [$this, 'ajaxCreateBackup']);
        add_action('wp_ajax_pdf_builder_restore_backup', [$this, 'ajaxRestoreBackup']);
        add_action('wp_ajax_pdf_builder_list_backups', [$this, 'ajaxListBackups']);
        add_action('wp_ajax_pdf_builder_delete_backup', [$this, 'ajaxDeleteBackup']);
    }

    /**
     * Créer une sauvegarde complète
     *
     * @param array $options Options de sauvegarde
     * @return array Résultat de l'opération
     */
    public function createBackup($options = [])
    {
        try {
            $backup_data = [
                'version' => self::BACKUP_VERSION,
                'timestamp' => current_time('timestamp'),
                'site_url' => get_site_url(),
                'plugin_version' => PDF_BUILDER_VERSION ?? '1.1.0',
                'data' => []
            ];

            // Sauvegarder les templates
            if (!isset($options['exclude_templates']) || !$options['exclude_templates']) {
                $backup_data['data']['templates'] = $this->exportAllTemplates();
            }

            // Sauvegarder la configuration
            if (!isset($options['exclude_settings']) || !$options['exclude_settings']) {
                $backup_data['data']['settings'] = $this->exportSettings();
            }

            // Sauvegarder les métadonnées utilisateur
            if (!isset($options['exclude_user_data']) || !$options['exclude_user_data']) {
                $backup_data['data']['user_data'] = $this->exportUserData();
            }

            // Générer le nom du fichier
            $filename = 'pdf-builder-backup-' . date('Y-m-d-H-i-s', $backup_data['timestamp']) . '.json';

            // Sauvegarder dans un fichier
            $filepath = $this->backup_dir . $filename;
            $json_content = wp_json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if (file_put_contents($filepath, $json_content)) {
                // Compresser si demandé
                if (isset($options['compress']) && $options['compress']) {
                    $zip_filepath = $this->compressBackup($filepath);
                    if ($zip_filepath) {
                        unlink($filepath); // Supprimer le fichier JSON original
                        $filepath = $zip_filepath;
                        $filename = basename($zip_filepath);
                    }
                }

                return [
                    'success' => true,
                    'message' => __('Sauvegarde créée avec succès.', 'pdf-builder-pro'),
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => filesize($filepath)
                ];
            } else {
                throw new \Exception(__('Erreur lors de l\'écriture du fichier de sauvegarde.', 'pdf-builder-pro'));
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Restaurer une sauvegarde
     *
     * @param string $filename Nom du fichier de sauvegarde
     * @param array $options Options de restauration
     * @return array Résultat de l'opération
     */
    public function restoreBackup($filename, $options = [])
    {
        try {
            $filepath = $this->backup_dir . $filename;

            if (!file_exists($filepath)) {
                throw new \Exception(__('Fichier de sauvegarde introuvable.', 'pdf-builder-pro'));
            }

            // Décompresser si nécessaire
            if (pathinfo($filepath, PATHINFO_EXTENSION) === 'zip') {
                $json_filepath = $this->decompressBackup($filepath);
                if (!$json_filepath) {
                    throw new \Exception(__('Erreur lors de la décompression du fichier.', 'pdf-builder-pro'));
                }
                $filepath = $json_filepath;
            }

            // Lire le fichier
            $content = file_get_contents($filepath);
            if (!$content) {
                throw new \Exception(__('Erreur lors de la lecture du fichier de sauvegarde.', 'pdf-builder-pro'));
            }

            $backup_data = json_decode($content, true);
            if (!$backup_data || !isset($backup_data['version'])) {
                throw new \Exception(__('Format de sauvegarde invalide.', 'pdf-builder-pro'));
            }

            // Valider la version
            if (version_compare($backup_data['version'], self::BACKUP_VERSION, '>')) {
                throw new \Exception(__('Version de sauvegarde incompatible.', 'pdf-builder-pro'));
            }

            $results = [];

            // Restaurer les templates
            if (isset($backup_data['data']['templates']) &&
                (!isset($options['exclude_templates']) || !$options['exclude_templates'])) {
                $results['templates'] = $this->importTemplatesFromData($backup_data['data']['templates'], $options);
            }

            // Restaurer la configuration
            if (isset($backup_data['data']['settings']) &&
                (!isset($options['exclude_settings']) || !$options['exclude_settings'])) {
                $results['settings'] = $this->importSettings($backup_data['data']['settings']);
            }

            // Restaurer les données utilisateur
            if (isset($backup_data['data']['user_data']) &&
                (!isset($options['exclude_user_data']) || !$options['exclude_user_data'])) {
                $results['user_data'] = $this->importUserData($backup_data['data']['user_data']);
            }

            // Nettoyer le fichier temporaire si décompressé
            if (isset($json_filepath) && file_exists($json_filepath)) {
                unlink($json_filepath);
            }

            return [
                'success' => true,
                'message' => __('Restauration terminée avec succès.', 'pdf-builder-pro'),
                'results' => $results
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Exporter tous les templates
     *
     * @return array Données des templates
     */
    private function exportAllTemplates()
    {
        $templates = [];

        // Récupérer tous les templates depuis la base de données
        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_builder_templates';

        $results = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY created_at DESC",
            ARRAY_A
        );

        foreach ($results as $template) {
            $templates[] = [
                'id' => $template['id'],
                'name' => $template['name'],
                'description' => $template['description'],
                'data' => json_decode($template['data'], true),
                'user_id' => $template['user_id'],
                'is_premium' => $template['is_premium'] ?? false,
                'created_at' => $template['created_at'],
                'updated_at' => $template['updated_at'],
                'metadata' => json_decode($template['metadata'] ?? '{}', true)
            ];
        }

        return $templates;
    }

    /**
     * Exporter la configuration
     *
     * @return array Configuration
     */
    private function exportSettings()
    {
        $settings = [];

        // Liste des options à sauvegarder
        $option_keys = [
            'pdf_builder_allowed_roles',
            'pdf_builder_settings',
            'pdf_builder_license_key',
            'pdf_builder_license_status'
        ];

        foreach ($option_keys as $key) {
            $settings[$key] = get_option($key);
        }

        return $settings;
    }

    /**
     * Exporter les données utilisateur
     *
     * @return array Données utilisateur
     */
    private function exportUserData()
    {
        $user_data = [];

        // Récupérer les métadonnées utilisateur liées au PDF Builder
        $users = get_users();

        foreach ($users as $user) {
            $user_meta = [
                'pdf_builder_user_settings' => get_user_meta($user->ID, 'pdf_builder_user_settings', true),
                'pdf_builder_template_count' => get_user_meta($user->ID, 'pdf_builder_template_count', true),
                'pdf_builder_last_activity' => get_user_meta($user->ID, 'pdf_builder_last_activity', true)
            ];

            // Ne sauvegarder que les métadonnées non vides
            $user_meta = array_filter($user_meta);

            if (!empty($user_meta)) {
                $user_data[$user->ID] = [
                    'user_login' => $user->user_login,
                    'user_email' => $user->user_email,
                    'meta' => $user_meta
                ];
            }
        }

        return $user_data;
    }

    /**
     * Importer des templates depuis les données
     *
     * @param array $templates_data Données des templates
     * @param array $options Options d'import
     * @return array Résultat
     */
    private function importTemplatesFromData($templates_data, $options = [])
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_builder_templates';

        foreach ($templates_data as $template_data) {
            try {
                // Vérifier si le template existe déjà
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_name WHERE name = %s",
                    $template_data['name']
                ));

                if ($existing && (!isset($options['overwrite']) || !$options['overwrite'])) {
                    $skipped++;
                    continue;
                }

                // Préparer les données
                $data = [
                    'name' => sanitize_text_field($template_data['name']),
                    'description' => sanitize_textarea_field($template_data['description'] ?? ''),
                    'data' => wp_json_encode($template_data['data']),
                    'user_id' => $template_data['user_id'] ?? get_current_user_id(),
                    'is_premium' => $template_data['is_premium'] ?? 0,
                    'metadata' => wp_json_encode($template_data['metadata'] ?? []),
                    'updated_at' => current_time('mysql')
                ];

                if ($existing) {
                    // Mettre à jour
                    $wpdb->update(
                        $table_name,
                        $data,
                        ['id' => $existing]
                    );
                } else {
                    // Insérer
                    $data['created_at'] = current_time('mysql');
                    $wpdb->insert($table_name, $data);
                }

                $imported++;

            } catch (\Exception $e) {
                $errors[] = sprintf(
                    __('Erreur lors de l\'import du template "%s": %s', 'pdf-builder-pro'),
                    $template_data['name'],
                    $e->getMessage()
                );
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Importer la configuration
     *
     * @param array $settings Configuration
     * @return bool Succès
     */
    private function importSettings($settings)
    {
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        return true;
    }

    /**
     * Importer les données utilisateur
     *
     * @param array $user_data Données utilisateur
     * @return array Résultat
     */
    private function importUserData($user_data)
    {
        $imported = 0;

        foreach ($user_data as $user_id => $data) {
            // Trouver l'utilisateur par email ou login
            $user = get_user_by('email', $data['user_email']) ?: get_user_by('login', $data['user_login']);

            if ($user) {
                foreach ($data['meta'] as $meta_key => $meta_value) {
                    update_user_meta($user->ID, $meta_key, $meta_value);
                }
                $imported++;
            }
        }

        return ['imported' => $imported];
    }

    /**
     * Compresser une sauvegarde
     *
     * @param string $filepath Chemin du fichier à compresser
     * @return string|null Chemin du fichier compressé
     */
    private function compressBackup($filepath)
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip_filepath = $filepath . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zip_filepath, \ZipArchive::CREATE) === true) {
            $zip->addFile($filepath, basename($filepath));
            $zip->close();
            return $zip_filepath;
        }

        return null;
    }

    /**
     * Décompresser une sauvegarde
     *
     * @param string $zip_filepath Chemin du fichier ZIP
     * @return string|null Chemin du fichier décompressé
     */
    private function decompressBackup($zip_filepath)
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip = new \ZipArchive();
        $extract_path = sys_get_temp_dir() . '/pdf-builder-restore-' . uniqid();

        if ($zip->open($zip_filepath) === true) {
            $zip->extractTo($extract_path);
            $zip->close();

            // Trouver le fichier JSON
            $files = glob($extract_path . '/*.json');
            return !empty($files) ? $files[0] : null;
        }

        return null;
    }

    /**
     * Lister les sauvegardes disponibles
     *
     * @return array Liste des sauvegardes
     */
    public function listBackups()
    {
        $backups = [];

        try {
            if (!file_exists($this->backup_dir)) {
                // Créer le répertoire s'il n'existe pas
                if (!wp_mkdir_p($this->backup_dir)) {
                    error_log('PDF Builder: Impossible de créer le répertoire de sauvegarde: ' . $this->backup_dir);
                    return $backups;
                }
            }

            if (!is_readable($this->backup_dir)) {
                error_log('PDF Builder: Répertoire de sauvegarde non lisible: ' . $this->backup_dir);
                return $backups;
            }

            $files = glob($this->backup_dir . '*.{json,zip}', GLOB_BRACE);

            if ($files === false) {
                error_log('PDF Builder: Erreur lors de la lecture du répertoire de sauvegarde: ' . $this->backup_dir);
                return $backups;
            }

            foreach ($files as $file) {
                if (!is_readable($file)) {
                    error_log('PDF Builder: Fichier de sauvegarde non lisible: ' . $file);
                    continue;
                }

                $filename = basename($file);
                $size = @filesize($file);
                $modified = @filemtime($file);

                if ($size === false || $modified === false) {
                    error_log('PDF Builder: Impossible de lire les informations du fichier: ' . $file);
                    continue;
                }

                $backups[] = [
                    'filename' => $filename,
                    'filepath' => $file,
                    'size' => $size,
                    'size_human' => size_format($size),
                    'modified' => $modified,
                    'modified_human' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $modified),
                    'type' => pathinfo($file, PATHINFO_EXTENSION)
                ];
            }

            // Trier par date de modification (plus récent en premier)
            usort($backups, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });

        } catch (\Exception $e) {
            error_log('PDF Builder: Exception dans listBackups(): ' . $e->getMessage());
            return $backups;
        }

        return $backups;
    }

    /**
     * Supprimer une sauvegarde
     *
     * @param string $filename Nom du fichier
     * @return array Résultat de l'opération
     */
    public function deleteBackup($filename)
    {
        error_log('PDF Builder: deleteBackup called with: ' . $filename);

        try {
            $filepath = $this->backup_dir . $filename;
            error_log('PDF Builder: filepath: ' . $filepath);

            if (!file_exists($filepath)) {
                error_log('PDF Builder: file does not exist, returning success');
                return [
                    'success' => true,
                    'message' => __('Sauvegarde supprimée avec succès.', 'pdf-builder-pro')
                ];
            }

            if (unlink($filepath)) {
                error_log('PDF Builder: unlink success');
                return [
                    'success' => true,
                    'message' => __('Sauvegarde supprimée avec succès.', 'pdf-builder-pro')
                ];
            } else {
                error_log('PDF Builder: unlink failed');
                throw new \Exception(__('Erreur lors de la suppression du fichier.', 'pdf-builder-pro'));
            }
        } catch (\Exception $e) {
            error_log('PDF Builder: deleteBackup exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Nettoyer les anciennes sauvegardes
     *
     * @param int $keep_days Nombre de jours à garder
     * @return int Nombre de fichiers supprimés
     */
    public function cleanupOldBackups($keep_days = 30)
    {
        $deleted = 0;
        $cutoff_time = time() - ($keep_days * 24 * 60 * 60);

        $backups = $this->listBackups();

        foreach ($backups as $backup) {
            if ($backup['modified'] < $cutoff_time) {
                if ($this->deleteBackup($backup['filename'])) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Handler AJAX pour l'export des templates
     */
    public function exportTemplates()
    {
        check_ajax_referer('pdf_builder_backup', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        $result = $this->createBackup([
            'exclude_settings' => true,
            'exclude_user_data' => true,
            'compress' => true
        ]);

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'download_url' => wp_upload_dir()['baseurl'] . '/pdf-builder-backups/' . $result['filename']
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    /**
     * Handler AJAX pour l'import des templates
     */
    public function importTemplates()
    {
        check_ajax_referer('pdf_builder_backup', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        if (empty($_FILES['backup_file'])) {
            wp_send_json_error(['message' => __('Aucun fichier sélectionné.', 'pdf-builder-pro')]);
        }

        $file = $_FILES['backup_file'];

        // Validation du fichier
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => __('Erreur lors du téléchargement du fichier.', 'pdf-builder-pro')]);
        }

        // Vérifier l'extension
        $allowed_extensions = ['json', 'zip'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            wp_send_json_error(['message' => __('Type de fichier non autorisé.', 'pdf-builder-pro')]);
        }

        // Déplacer le fichier vers le dossier de sauvegarde
        $temp_filename = 'temp-import-' . uniqid() . '.' . $file_extension;
        $temp_filepath = $this->backup_dir . $temp_filename;

        if (!move_uploaded_file($file['tmp_name'], $temp_filepath)) {
            wp_send_json_error(['message' => __('Erreur lors du déplacement du fichier.', 'pdf-builder-pro')]);
        }

        // Restaurer depuis le fichier
        $result = $this->restoreBackup($temp_filename, [
            'exclude_settings' => true,
            'exclude_user_data' => true
        ]);

        // Supprimer le fichier temporaire
        unlink($temp_filepath);

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'results' => $result['results']
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    /**
     * Handler AJAX pour créer une sauvegarde
     */
    public function ajaxCreateBackup()
    {
        check_ajax_referer('pdf_builder_backup', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        $options = [
            'compress' => isset($_POST['compress']) && $_POST['compress'] === '1',
            'exclude_templates' => isset($_POST['exclude_templates']) && $_POST['exclude_templates'] === '1',
            'exclude_settings' => isset($_POST['exclude_settings']) && $_POST['exclude_settings'] === '1',
            'exclude_user_data' => isset($_POST['exclude_user_data']) && $_POST['exclude_user_data'] === '1'
        ];

        $result = $this->createBackup($options);

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'filename' => $result['filename'],
                'size_human' => size_format($result['size'])
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    /**
     * Handler AJAX pour restaurer une sauvegarde
     */
    public function ajaxRestoreBackup()
    {
        check_ajax_referer('pdf_builder_backup', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        $filename = sanitize_file_name($_POST['filename'] ?? '');

        if (empty($filename)) {
            wp_send_json_error(['message' => __('Nom de fichier manquant.', 'pdf-builder-pro')]);
        }

        $options = [
            'overwrite' => isset($_POST['overwrite']) && $_POST['overwrite'] === '1',
            'exclude_templates' => isset($_POST['exclude_templates']) && $_POST['exclude_templates'] === '1',
            'exclude_settings' => isset($_POST['exclude_settings']) && $_POST['exclude_settings'] === '1',
            'exclude_user_data' => isset($_POST['exclude_user_data']) && $_POST['exclude_user_data'] === '1'
        ];

        $result = $this->restoreBackup($filename, $options);

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'results' => $result['results']
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }

    /**
     * Handler AJAX pour lister les sauvegardes
     */
    public function ajaxListBackups()
    {
        try {
            error_log('PDF Builder: ajaxListBackups called');

            check_ajax_referer('pdf_builder_backup', 'nonce');
            error_log('PDF Builder: nonce verified');

            if (!current_user_can('manage_options')) {
                error_log('PDF Builder: insufficient permissions');
                wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
                return;
            }
            error_log('PDF Builder: permissions OK');

            $backups = $this->listBackups();
            error_log('PDF Builder: backups loaded, count: ' . count($backups));

            wp_send_json_success(['backups' => $backups]);
            error_log('PDF Builder: response sent');

        } catch (\Exception $e) {
            error_log('PDF Builder: Exception in ajaxListBackups: ' . $e->getMessage());
            error_log('PDF Builder: Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => __('Erreur lors du chargement des sauvegardes.', 'pdf-builder-pro')]);
        }
    }

    /**
     * Handler AJAX pour supprimer une sauvegarde
     */
    public function ajaxDeleteBackup()
    {
        error_log('PDF Builder: ajaxDeleteBackup called');
        error_log('PDF Builder: backup_dir: ' . $this->backup_dir);

        check_ajax_referer('pdf_builder_backup', 'nonce');

        if (!current_user_can('manage_options')) {
            error_log('PDF Builder: permissions failed');
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        $filename = sanitize_file_name($_POST['filename'] ?? '');
        error_log('PDF Builder: filename: ' . $filename);

        if (empty($filename)) {
            error_log('PDF Builder: filename empty');
            wp_send_json_error(['message' => __('Nom de fichier manquant.', 'pdf-builder-pro')]);
        }

        $result = $this->deleteBackup($filename);

        if ($result['success']) {
            error_log('PDF Builder: delete success');
            wp_send_json_success(['message' => $result['message']]);
        } else {
            error_log('PDF Builder: delete error: ' . $result['message']);
            wp_send_json_error(['message' => $result['message']]);
        }
    }
}