<?php
/**
 * PDF Builder Pro - Système de déploiement continu
 * Automatise les déploiements, rollbacks et gestion des environnements
 */

class PDF_Builder_Continuous_Deployment {
    private static $instance = null;

    // Environnements de déploiement
    const ENV_DEVELOPMENT = 'development';
    const ENV_STAGING = 'staging';
    const ENV_PRODUCTION = 'production';

    // Statuts de déploiement
    const STATUS_PENDING = 'pending';
    const STATUS_DEPLOYING = 'deploying';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_ROLLED_BACK = 'rolled_back';

    // Types de déploiement
    const DEPLOY_TYPE_AUTOMATIC = 'automatic';
    const DEPLOY_TYPE_MANUAL = 'manual';
    const DEPLOY_TYPE_ROLLBACK = 'rollback';

    private $environments = [];
    private $current_env;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->current_env = wp_get_environment_type() ?: self::ENV_PRODUCTION;
        $this->init_environments();
        $this->init_hooks();
    }

    private function init_hooks() {
        // Déploiement automatique
        add_action('wp_ajax_pdf_builder_deploy', [$this, 'deploy_ajax']);
        add_action('wp_ajax_pdf_builder_get_deployment_status', [$this, 'get_deployment_status_ajax']);
        add_action('wp_ajax_pdf_builder_rollback', [$this, 'rollback_ajax']);

        // Webhooks pour CI/CD
        add_action('wp_ajax_nopriv_pdf_builder_webhook', [$this, 'webhook_handler']);
        add_action('wp_ajax_pdf_builder_webhook', [$this, 'webhook_handler']);

        // Vérifications pré-déploiement
        add_action('pdf_builder_pre_deployment_check', [$this, 'pre_deployment_check']);

        // Nettoyage des déploiements
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_old_deployments']);

        // Surveillance des déploiements
        add_action('pdf_builder_deployment_monitor', [$this, 'monitor_deployments']);
    }

    /**
     * Initialise les environnements de déploiement
     */
    private function init_environments() {
        $this->environments = [
            self::ENV_DEVELOPMENT => [
                'name' => 'Développement',
                'auto_deploy' => true,
                'require_tests' => false,
                'backup_before_deploy' => false,
                // notification_channels removed
                'webhook_secret' => wp_generate_password(32, false)
            ],
            self::ENV_STAGING => [
                'name' => 'Staging',
                'auto_deploy' => pdf_builder_config('auto_deploy_staging', true),
                'require_tests' => true,
                'backup_before_deploy' => true,
                // notification_channels removed
                'webhook_secret' => wp_generate_password(32, false)
            ],
            self::ENV_PRODUCTION => [
                'name' => 'Production',
                'auto_deploy' => pdf_builder_config('auto_deploy_production', false),
                'require_tests' => true,
                'backup_before_deploy' => true,
                // notification_channels removed
                'webhook_secret' => wp_generate_password(32, false)
            ]
        ];
    }

    /**
     * Effectue un déploiement
     */
    public function deploy($version, $environment = null, $type = self::DEPLOY_TYPE_MANUAL) {
        try {
            $environment = $environment ?: $this->current_env;

            if (!isset($this->environments[$environment])) {
                throw new Exception('Environnement de déploiement invalide');
            }

            $env_config = $this->environments[$environment];

            // Créer un enregistrement de déploiement
            $deployment_id = $this->create_deployment_record($version, $environment, $type);

            // Mettre à jour le statut
            $this->update_deployment_status($deployment_id, self::STATUS_DEPLOYING);

            // Vérifications pré-déploiement
            $this->run_pre_deployment_checks($deployment_id, $env_config);

            // Créer une sauvegarde si nécessaire
            if ($env_config['backup_before_deploy']) {
                $backup_id = $this->create_pre_deployment_backup($deployment_id);
            }

            // Télécharger la nouvelle version
            $download_path = $this->download_release($version);

            // Extraire et déployer
            $this->extract_and_deploy($download_path, $deployment_id);

            // Exécuter les migrations
            $this->run_migrations($version, $deployment_id);

            // Vérifications post-déploiement
            $this->run_post_deployment_checks($deployment_id);

            // Mettre à jour le statut
            $this->update_deployment_status($deployment_id, self::STATUS_SUCCESS, [
                'backup_id' => $backup_id ?? null,
                'completed_at' => current_time('mysql')
            ]);

            // Notifier le succès
            $this->notify_deployment_success($deployment_id);

                    'deployment_id' => $deployment_id,
                    'version' => $version,
                    'environment' => $environment
                ]);
            }

            return $deployment_id;

        } catch (Exception $e) {
            // Marquer le déploiement comme échoué
            if (isset($deployment_id)) {
                $this->update_deployment_status($deployment_id, self::STATUS_FAILED, [
                    'error' => $e->getMessage(),
                    'failed_at' => current_time('mysql')
                ]);
            }

            // Notifier l'échec
            $this->notify_deployment_failure($deployment_id ?? null, $e);

            $this->log_deployment_error('deploy', $e);
            throw $e;
        }
    }

    /**
     * Effectue un rollback
     */
    public function rollback($deployment_id, $target_version = null) {
        try {
            $deployment = $this->get_deployment_record($deployment_id);

            if (!$deployment) {
                throw new Exception('Déploiement introuvable');
            }

            // Créer un enregistrement de rollback
            $rollback_id = $this->create_deployment_record(
                $target_version ?: $deployment['previous_version'],
                $deployment['environment'],
                self::DEPLOY_TYPE_ROLLBACK
            );

            $this->update_deployment_status($rollback_id, self::STATUS_DEPLOYING);

            // Restaurer la sauvegarde
            if ($deployment['backup_id']) {
                $this->restore_backup($deployment['backup_id'], $rollback_id);
            } else {
                // Rollback manuel vers une version précédente
                $this->rollback_to_version($target_version ?: $deployment['previous_version'], $rollback_id);
            }

            $this->update_deployment_status($rollback_id, self::STATUS_SUCCESS, [
                'rolled_back_from' => $deployment_id,
                'completed_at' => current_time('mysql')
            ]);

            // Notifier le rollback
            $this->notify_rollback_success($rollback_id, $deployment_id);

                    'rollback_id' => $rollback_id,
                    'from_deployment' => $deployment_id
                ]);
            }

            return $rollback_id;

        } catch (Exception $e) {
            if (isset($rollback_id)) {
                $this->update_deployment_status($rollback_id, self::STATUS_FAILED, [
                    'error' => $e->getMessage()
                ]);
            }

            $this->notify_rollback_failure($rollback_id ?? null, $e);
            $this->log_deployment_error('rollback', $e);
            throw $e;
        }
    }

    /**
     * Gère les webhooks pour CI/CD
     */
    public function webhook_handler() {
        try {
            $payload = json_decode(file_get_contents('php://input'), true);

            if (!$payload) {
                wp_send_json_error(['message' => 'Payload invalide']);
                return;
            }

            // Vérifier la signature du webhook
            $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
            $secret = $this->environments[$this->current_env]['webhook_secret'];

            if (!$this->verify_webhook_signature($payload, $signature, $secret)) {
                wp_send_json_error(['message' => 'Signature invalide']);
                return;
            }

            // Traiter le webhook selon le type
            $event_type = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? $payload['event'] ?? '';

            switch ($event_type) {
                case 'push':
                    $this->handle_push_webhook($payload);
                    break;

                case 'release':
                    $this->handle_release_webhook($payload);
                    break;

                case 'deployment':
                    $this->handle_deployment_webhook($payload);
                    break;

                default:
                    wp_send_json(['message' => 'Événement non supporté']);
                    return;
            }

            wp_send_json_success(['message' => 'Webhook traité avec succès']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur webhook: ' . $e->getMessage()]);
        }
    }

    /**
     * Gère les webhooks de push
     */
    private function handle_push_webhook($payload) {
        $branch = $payload['ref'] ?? '';

        // Déploiement automatique pour la branche principale
        if ($branch === 'refs/heads/main' || $branch === 'refs/heads/master') {
            if ($this->environments[$this->current_env]['auto_deploy']) {
                $version = $payload['after'] ?? time(); // Utiliser le commit hash comme version
                $this->deploy($version, null, self::DEPLOY_TYPE_AUTOMATIC);
            }
        }
    }

    /**
     * Gère les webhooks de release
     */
    private function handle_release_webhook($payload) {
        if (($payload['action'] ?? '') === 'published') {
            $release = $payload['release'] ?? [];
            $version = $release['tag_name'] ?? '';

            if (!empty($version) && $this->environments[$this->current_env]['auto_deploy']) {
                $this->deploy($version, null, self::DEPLOY_TYPE_AUTOMATIC);
            }
        }
    }

    /**
     * Gère les webhooks de déploiement
     */
    private function handle_deployment_webhook($payload) {
        // Traiter les statuts de déploiement externes
        $deployment = $payload['deployment'] ?? [];
        $status = $payload['deployment_status']['state'] ?? '';

        // Mettre à jour le statut local si nécessaire
        if ($status === 'success' || $status === 'failure') {
            // Logique pour synchroniser avec les déploiements externes
        }
    }

    /**
     * Vérifie la signature du webhook
     */
    private function verify_webhook_signature($payload, $signature, $secret) {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        $expected_signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), $secret);

        return hash_equals($expected_signature, $signature);
    }

    /**
     * Crée un enregistrement de déploiement
     */
    private function create_deployment_record($version, $environment, $type) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_deployments';

        $wpdb->insert(
            $table,
            [
                'version' => $version,
                'environment' => $environment,
                'deployment_type' => $type,
                'status' => self::STATUS_PENDING,
                'user_id' => get_current_user_id(),
                'previous_version' => PDF_BUILDER_VERSION,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );

        return $wpdb->insert_id;
    }

    /**
     * Met à jour le statut d'un déploiement
     */
    private function update_deployment_status($deployment_id, $status, $additional_data = []) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_deployments';

        $update_data = ['status' => $status] + $additional_data;

        $wpdb->update(
            $table,
            $update_data,
            ['id' => $deployment_id],
            array_fill(0, count($update_data), '%s'),
            ['%d']
        );
    }

    /**
     * Obtient un enregistrement de déploiement
     */
    private function get_deployment_record($deployment_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_deployments';

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table WHERE id = %d
        ", $deployment_id), ARRAY_A);
    }

    /**
     * Exécute les vérifications pré-déploiement
     */
    private function run_pre_deployment_checks($deployment_id, $env_config) {
        $issues = [];

        // Vérifier les tests si requis
        if ($env_config['require_tests']) {
            $test_results = $this->run_deployment_tests();
            if (!$test_results['passed']) {
                $issues[] = 'Tests échoués: ' . $test_results['message'];
            }
        }

        // Vérifier l'intégrité du système
        if (class_exists('PDF_Builder_Diagnostic_Tool')) {
            $integrity_check = PDF_Builder_Diagnostic_Tool::get_instance()->check_system_integrity();
            if ($integrity_check['status'] !== 'healthy') {
                $issues[] = 'Problèmes d\'intégrité système détectés';
            }
        }

        // Vérifier l'espace disque
        $disk_free = disk_free_space(ABSPATH);
        $min_disk_space = 100 * 1024 * 1024; // 100MB

        if ($disk_free < $min_disk_space) {
            $issues[] = 'Espace disque insuffisant';
        }

        if (!empty($issues)) {
            throw new Exception('Vérifications pré-déploiement échouées: ' . implode(', ', $issues));
        }
    }

    /**
     * Exécute les tests de déploiement
     */
    private function run_deployment_tests() {
        if (!class_exists('PDF_Builder_Test_Suite')) {
            return ['passed' => true, 'message' => 'Suite de tests non disponible'];
        }

        $test_suite = PDF_Builder_Test_Suite::get_instance();

        // Exécuter les tests critiques
        $results = $test_suite->run_test_suite('unit');
        $results = array_merge($results, $test_suite->run_test_suite('integration'));

        $all_passed = $results['summary']['failed'] === 0 && $results['summary']['errors'] === 0;

        return [
            'passed' => $all_passed,
            'message' => $all_passed ? 'Tous les tests réussis' : 'Échecs de tests détectés'
        ];
    }

    /**
     * Crée une sauvegarde pré-déploiement
     */
    private function create_pre_deployment_backup($deployment_id) {
        if (!class_exists('PDF_Builder_Backup_Recovery_System')) {
            return null;
        }

        $backup_system = PDF_Builder_Backup_Recovery_System::get_instance();
        $backup_name = 'Sauvegarde pré-déploiement ' . $deployment_id;

        return $backup_system->create_full_backup($backup_name, 'Sauvegarde créée automatiquement avant déploiement');
    }

    /**
     * Télécharge une release
     */
    private function download_release($version) {
        // Pour l'instant, simuler un téléchargement
        // En production, cela téléchargerait depuis un repository Git ou un système de releases

        $download_url = "https://api.github.com/repos/your-org/pdf-builder-pro/releases/download/$version/pdf-builder-pro-$version.zip";

        $response = wp_remote_get($download_url, [
            'timeout' => 300,
            'headers' => [
                'Authorization' => 'token ' . pdf_builder_config('github_token', ''),
                'Accept' => 'application/octet-stream'
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception('Erreur de téléchargement: ' . $response->get_error_message());
        }

        $upload_dir = wp_upload_dir();
        $download_dir = $upload_dir['basedir'] . '/pdf-builder-deployments/';

        wp_mkdir_p($download_dir);

        $download_path = $download_dir . "pdf-builder-$version.zip";
        file_put_contents($download_path, wp_remote_retrieve_body($response));

        return $download_path;
    }

    /**
     * Extrait et déploie les fichiers
     */
    private function extract_and_deploy($archive_path, $deployment_id) {
        // Valider les chemins d'entrée
        if (!file_exists($archive_path) || !is_readable($archive_path)) {
            throw new Exception('Archive non accessible ou inexistante');
        }

        $extract_path = sys_get_temp_dir() . '/pdf_builder_deploy_' . $deployment_id;

        // Créer le répertoire d'extraction de manière sécurisée
        if (!wp_mkdir_p($extract_path)) {
            throw new Exception('Impossible de créer le répertoire d\'extraction');
        }

        // Sécuriser la commande unzip
        $command = sprintf(
            'unzip -q %s -d %s',
            escapeshellarg($archive_path),
            escapeshellarg($extract_path)
        );

        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            $this->cleanup_temp_files($extract_path);
            throw new Exception('Erreur lors de l\'extraction de l\'archive: ' . implode(' ', $output));
        }

        // Sauvegarder les fichiers actuels
        $this->backup_current_files($deployment_id);

        // Copier les nouveaux fichiers
        $this->deploy_files($extract_path, $deployment_id);

        // Nettoyer
        $this->cleanup_temp_files($extract_path);
        unlink($archive_path);
    }

    /**
     * Sauvegarde les fichiers actuels
     */
    private function backup_current_files($deployment_id) {
        $plugin_dir = PDF_BUILDER_PLUGIN_DIR;
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups/deployment_' . $deployment_id;

        wp_mkdir_p($backup_dir);

        // Copier les fichiers importants
        $this->copy_directory_recursive($plugin_dir, $backup_dir, ['backups', 'logs']);
    }

    /**
     * Déploie les fichiers
     */
    private function deploy_files($source_dir, $deployment_id) {
        $plugin_dir = PDF_BUILDER_PLUGIN_DIR;

        // Copier les nouveaux fichiers
        $this->copy_directory_recursive($source_dir, $plugin_dir, ['backups', 'logs', 'config']);
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
     * Exécute les migrations
     */
    private function run_migrations($version, $deployment_id) {
        $migration_file = PDF_BUILDER_PLUGIN_DIR . 'migrations.php';

        if (file_exists($migration_file)) {
            include_once $migration_file;

            if (function_exists('pdf_builder_run_migrations')) {
                pdf_builder_run_migrations($version);
            }
        }
    }

    /**
     * Exécute les vérifications post-déploiement
     */
    private function run_post_deployment_checks($deployment_id) {
        // Vérifier que le plugin fonctionne
        if (!function_exists('pdf_builder_init')) {
            throw new Exception('Fonction d\'initialisation du plugin manquante après déploiement');
        }

        // Vérifier la version
        if (!defined('PDF_BUILDER_VERSION')) {
            throw new Exception('Constante de version manquante après déploiement');
        }

        // Tester une fonctionnalité de base
        $test_config = pdf_builder_config('version');
        if (!$test_config) {
            throw new Exception('Configuration inaccessible après déploiement');
        }
    }

    /**
     * Restaure une sauvegarde
     */
    private function restore_backup($backup_id, $deployment_id) {
        if (!class_exists('PDF_Builder_Backup_Recovery_System')) {
            throw new Exception('Système de sauvegarde non disponible');
        }

        $backup_system = PDF_Builder_Backup_Recovery_System::get_instance();
        $backup_system->restore_backup($backup_id, ['database', 'files', 'configuration']);
    }

    /**
     * Effectue un rollback vers une version spécifique
     */
    private function rollback_to_version($version, $deployment_id) {
        // Télécharger l'ancienne version
        $download_path = $this->download_release($version);

        // Extraire et déployer
        $this->extract_and_deploy($download_path, $deployment_id);
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
     * Vérifications pré-déploiement périodiques
     */
    public function pre_deployment_check() {
        $issues = [];

        // Vérifier les mises à jour disponibles
        if (class_exists('PDF_Builder_Auto_Update_System')) {
            $update_system = PDF_Builder_Auto_Update_System::get_instance();
            $update = $update_system->check_for_updates();

            if ($update) {
                $issues[] = 'Mise à jour disponible: ' . $update['version'];
            }
        }

        // Vérifier l'état du système
        if (class_exists('PDF_Builder_Diagnostic_Tool')) {
            $diagnostic = PDF_Builder_Diagnostic_Tool::get_instance();
            $system_check = $diagnostic->check_system_integrity();

            if ($system_check['status'] !== 'healthy') {
                $issues = array_merge($issues, $system_check['issues']);
            }
        }

        if (!empty($issues)) {
            // Legacy notification calls removed — log as warning
        }
    }

    /**
     * Nettoie les anciens déploiements
     */
    public function cleanup_old_deployments() {
        global $wpdb;

        $retention_months = pdf_builder_config('deployment_retention_months', 12);

        $table = $wpdb->prefix . 'pdf_builder_deployments';
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM $table
            WHERE status = 'success'
            AND created_at < DATE_SUB(NOW(), INTERVAL %d MONTH)
        ", $retention_months));

        }
    }

    /**
     * Surveille les déploiements
     */
    public function monitor_deployments() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_deployments';

        // Vérifier les déploiements bloqués
        $stuck_deployments = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table
            WHERE status = 'deploying'
            AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ", ARRAY_A));

        foreach ($stuck_deployments as $deployment) {
            // Marquer comme échoué
            $this->update_deployment_status($deployment['id'], self::STATUS_FAILED, [
                'error' => 'Déploiement bloqué - timeout dépassé'
            ]);

            // Legacy notification calls removed — log as error
        }
    }

    /**
     * Notifie le succès d'un déploiement
     */
    private function notify_deployment_success($deployment_id) {
        $deployment = $this->get_deployment_record($deployment_id);
        $env_config = $this->environments[$deployment['environment']];

        $message = "Déploiement réussi\n";
        $message .= "Version: {$deployment['version']}\n";
        $message .= "Environnement: {$env_config['name']}\n";
        $message .= "Type: {$deployment['deployment_type']}\n";
        $message .= "Déploiement ID: {$deployment_id}";

        // Legacy notification calls removed — log success
    }

    /**
     * Notifie l'échec d'un déploiement
     */
    private function notify_deployment_failure($deployment_id, $exception) {
        $deployment = $deployment_id ? $this->get_deployment_record($deployment_id) : null;
        $env_config = $deployment ? $this->environments[$deployment['environment']] : $this->environments[$this->current_env];

        $message = "Échec de déploiement\n";
        if ($deployment) {
            $message .= "Version: {$deployment['version']}\n";
            $message .= "Environnement: {$env_config['name']}\n";
        }
        $message .= "Erreur: {$exception->getMessage()}";

        // Legacy notification calls removed — log critical
    }

    /**
     * Notifie le succès d'un rollback
     */
    private function notify_rollback_success($rollback_id, $original_deployment_id) {
        $rollback = $this->get_deployment_record($rollback_id);
        $env_config = $this->environments[$rollback['environment']];

        $message = "Rollback réussi\n";
        $message .= "Version restaurée: {$rollback['version']}\n";
        $message .= "Déploiement original: {$original_deployment_id}\n";
        $message .= "Rollback ID: {$rollback_id}";

        // Legacy notification calls removed — log warning
    }

    /**
     * Notifie l'échec d'un rollback
     */
    private function notify_rollback_failure($rollback_id, $exception) {
        $rollback = $rollback_id ? $this->get_deployment_record($rollback_id) : null;
        $env_config = $rollback ? $this->environments[$rollback['environment']] : $this->environments[$this->current_env];

        $message = "Échec de rollback\n";
        $message .= "Erreur: {$exception->getMessage()}";

        // Legacy notification calls removed — log critical
    }

    /**
     * Log une erreur de déploiement
     */
    private function log_deployment_error($operation, $exception) {
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        } else {

        }
    }

    /**
     * AJAX - Effectue un déploiement
     */
    public function deploy_ajax() {
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
            $environment = sanitize_text_field($_POST['environment'] ?? $this->current_env);

            if (empty($version)) {
                wp_send_json_error(['message' => 'Version manquante']);
                return;
            }

            $deployment_id = $this->deploy($version, $environment, self::DEPLOY_TYPE_MANUAL);

            wp_send_json_success([
                'message' => 'Déploiement lancé avec succès',
                'deployment_id' => $deployment_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du déploiement: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut des déploiements
     */
    public function get_deployment_status_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $deployments = $this->get_deployment_history(20);

            wp_send_json_success([
                'message' => 'Historique récupéré',
                'deployments' => $deployments,
                'current_version' => PDF_BUILDER_VERSION,
                'current_environment' => $this->current_env
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Effectue un rollback
     */
    public function rollback_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $deployment_id = intval($_POST['deployment_id'] ?? 0);
            $target_version = sanitize_text_field($_POST['target_version'] ?? '');

            if (!$deployment_id) {
                wp_send_json_error(['message' => 'ID de déploiement manquant']);
                return;
            }

            $rollback_id = $this->rollback($deployment_id, $target_version ?: null);

            wp_send_json_success([
                'message' => 'Rollback lancé avec succès',
                'rollback_id' => $rollback_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du rollback: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient l'historique des déploiements
     */
    public function get_deployment_history($limit = 20) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_deployments';

        $deployments = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT %d
        ", $limit), ARRAY_A);

        return $deployments;
    }
}

// Fonctions globales
function pdf_builder_deployment_system() {
    return PDF_Builder_Continuous_Deployment::get_instance();
}

function pdf_builder_deploy($version, $environment = null) {
    return PDF_Builder_Continuous_Deployment::get_instance()->deploy($version, $environment);
}

function pdf_builder_rollback($deployment_id, $target_version = null) {
    return PDF_Builder_Continuous_Deployment::get_instance()->rollback($deployment_id, $target_version);
}

function pdf_builder_get_deployments($limit = 20) {
    return PDF_Builder_Continuous_Deployment::get_instance()->get_deployment_history($limit);
}

// Initialiser le système de déploiement continu
add_action('plugins_loaded', function() {
    PDF_Builder_Continuous_Deployment::get_instance();
});



