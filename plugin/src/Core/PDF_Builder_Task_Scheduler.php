<?php
/**
 * PDF Builder Pro - Planificateur de tâches
 * Gère les tâches automatiques et planifiées du plugin
 */

error_log('PDF Builder: Task Scheduler file loaded');

class PDF_Builder_Task_Scheduler {
    private static $instance = null;

    // Définition des tâches
    const TASKS = [
        'pdf_builder_cache_cleanup' => [
            'interval' => 'hourly',
            'callback' => 'cleanup_expired_cache',
            'description' => 'Nettoie le cache expiré'
        ],
        'pdf_builder_auto_backup' => [
            'interval' => 'dynamic', // Sera déterminé dynamiquement
            'callback' => 'create_auto_backup',
            'description' => 'Crée une sauvegarde automatique'
        ],
        'pdf_builder_log_rotation' => [
            'interval' => 'daily',
            'callback' => 'rotate_logs',
            'description' => 'Effectue la rotation des logs'
        ],
        'pdf_builder_performance_cleanup' => [
            'interval' => 'weekly',
            'callback' => 'cleanup_performance_data',
            'description' => 'Nettoie les données de performance anciennes'
        ],
        'pdf_builder_security_check' => [
            'interval' => 'twicedaily',
            'callback' => 'security_health_check',
            'description' => 'Vérifie la santé sécurité'
        ],
        'pdf_builder_optimize_database' => [
            'interval' => 'weekly',
            'callback' => 'optimize_database',
            'description' => 'Optimise les tables de base de données'
        ]
    ];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        error_log('PDF Builder: Task Scheduler constructor called');
        $this->init_hooks();
        $this->schedule_tasks();
        error_log('PDF Builder: Task Scheduler initialized');
    }

    /**
     * Programme toutes les tâches définies
     */
    private function schedule_tasks() {
        foreach (self::TASKS as $task_name => $task_config) {
            if (!wp_next_scheduled($task_name)) {
                $interval = $task_config['interval'];
                
                // Pour les tâches dynamiques, déterminer l'intervalle
                if ($interval === 'dynamic') {
                    if ($task_name === 'pdf_builder_auto_backup') {
                        $frequency = get_option('pdf_builder_auto_backup_frequency', 'daily');
                        $interval = $this->map_frequency_to_interval($frequency);
                    } else {
                        $interval = 'daily'; // fallback
                    }
                }
                
                wp_schedule_event(time(), $interval, $task_name);
            }
        }
    }

    private function init_hooks() {
        // Activation/désactivation des tâches
        add_action('wp_ajax_pdf_builder_schedule_task', [$this, 'schedule_task_ajax']);
        add_action('wp_ajax_pdf_builder_unschedule_task', [$this, 'unschedule_task_ajax']);
        add_action('wp_ajax_pdf_builder_run_task_now', [$this, 'run_task_now_ajax']);

        // Enregistrer les actions AJAX pour diagnostic et réparation
        $this->register_ajax_actions();

        // Enregistrer les intervalles personnalisés
        add_filter('cron_schedules', [$this, 'add_custom_cron_schedules']);

        // Hooks pour les tâches planifiées
        foreach (self::TASKS as $task_name => $task_config) {
            add_action($task_name, [$this, $task_config['callback']]);
        }

        // Fallback pour les sauvegardes automatiques quand le cron système ne fonctionne pas
        add_action('admin_init', [$this, 'check_auto_backup_fallback']);

        // S'assurer que les actions AJAX sont enregistrées pour l'admin
        add_action('admin_init', [$this, 'register_ajax_actions']);

        // Reprogrammer les tâches quand les paramètres changent
        add_action('update_option_pdf_builder_backup_frequency', [$this, 'on_backup_frequency_changed'], 10, 3);
        add_action('update_option_pdf_builder_auto_backup_enabled', [$this, 'on_auto_backup_enabled_changed'], 10, 3);
    }

    /**
     * Enregistre les actions AJAX pour le diagnostic cron
     */
    public function register_ajax_actions() {
        error_log('PDF Builder: Registering AJAX actions');
        add_action('wp_ajax_pdf_builder_diagnose_cron', [$this, 'ajax_diagnose_cron']);
        add_action('wp_ajax_pdf_builder_repair_cron', [$this, 'ajax_repair_cron']);
        add_action('wp_ajax_pdf_builder_get_backup_stats', [$this, 'ajax_get_backup_stats']);
        add_action('wp_ajax_pdf_builder_create_backup', [$this, 'ajax_create_backup']);
        add_action('wp_ajax_pdf_builder_change_backup_frequency', [$this, 'ajax_change_backup_frequency']);
        error_log('PDF Builder: AJAX actions registered');
    }

    /**
     * Ajoute des intervalles de cron personnalisés
     */
    public function add_custom_cron_schedules($schedules) {
        $schedules['twicedaily'] = [
            'interval' => 43200, // 12 heures
            'display' => 'Deux fois par jour'
        ];

        $schedules['weekly'] = [
            'interval' => 604800, // 7 jours
            'display' => 'Une fois par semaine'
        ];

        $schedules['monthly'] = [
            'interval' => 2592000, // 30 jours
            'display' => 'Une fois par mois'
        ];

        $schedules['every_minute'] = [
            'interval' => 60, // 1 minute pour les tests
            'display' => 'Toutes les minutes (test)'
        ];

        return $schedules;
    }

    /**
     * Mappe la fréquence utilisateur à un intervalle cron
     */
    private function map_frequency_to_interval($frequency) {
        $mapping = [
            'every_minute' => 'every_minute',
            'daily' => 'daily',
            'weekly' => 'weekly',
            'monthly' => 'monthly'
        ];

        return $mapping[$frequency] ?? 'daily';
    }

    /**
     * Annule la planification de toutes les tâches
     */
    public function unschedule_all_tasks() {
        foreach (self::TASKS as $task_name => $task_config) {
            wp_clear_scheduled_hook($task_name);
        }
    }

    /**
     * Met à jour la planification de la sauvegarde automatique selon la nouvelle fréquence
     */
    public function reschedule_auto_backup($new_frequency = null) {
        // Annuler la tâche existante
        wp_clear_scheduled_hook('pdf_builder_auto_backup');

        // Déterminer la fréquence à utiliser
        if ($new_frequency === null) {
            $new_frequency = get_option('pdf_builder_auto_backup_frequency', 'daily');
        }

        // Programmer avec la nouvelle fréquence
        $interval = $this->map_frequency_to_interval($new_frequency);
        wp_schedule_event(time(), $interval, 'pdf_builder_auto_backup');
    }

    /**
     * Fallback pour les sauvegardes automatiques quand le cron système ne fonctionne pas
     * Se déclenche à chaque visite admin pour vérifier si une sauvegarde doit être faite
     */
    public function check_auto_backup_fallback() {
        // Vérifier seulement si les sauvegardes automatiques sont activées
        if (!function_exists('pdf_builder_config') || !pdf_builder_config('auto_backup_enabled')) {
            return;
        }

        // Récupérer la fréquence configurée
        $frequency = get_option('pdf_builder_auto_backup_frequency', 'daily');
        $last_backup = get_option('pdf_builder_last_auto_backup', 0);
        $now = time();

        // Calculer l'intervalle en secondes selon la fréquence
        $intervals = [
            'every_minute' => 60,
            'daily' => 86400, // 24h
            'weekly' => 604800, // 7 jours
            'monthly' => 2592000 // 30 jours
        ];

        $interval_seconds = $intervals[$frequency] ?? 86400;

        // Log de débogage
        if (is_admin()) {
            $time_since_last = round(($now - $last_backup) / 60, 1);
            echo "<script>console.log('[AUTO BACKUP FALLBACK] 🔍 Vérification sauvegarde auto - Fréquence: {$frequency}, Intervalle: {$interval_seconds}s, Dernière: {$time_since_last}min, Maintenant: {$now}');</script>";
        }

        // Vérifier si assez de temps s'est écoulé depuis la dernière sauvegarde
        if (($now - $last_backup) >= $interval_seconds) {
            // Éviter les exécutions multiples en vérifiant un flag temporaire
            $backup_in_progress = get_transient('pdf_builder_auto_backup_in_progress');
            if ($backup_in_progress) {
                if (is_admin()) {
                    echo "<script>console.log('[AUTO BACKUP FALLBACK] ⏳ Sauvegarde déjà en cours, ignorée');</script>";
                }
                return;
            }

            // Marquer qu'une sauvegarde est en cours
            set_transient('pdf_builder_auto_backup_in_progress', true, 300); // 5 minutes max

            // Marquer que nous allons faire une sauvegarde pour éviter les exécutions multiples
            update_option('pdf_builder_last_auto_backup', $now);

            // Logger le fallback
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info('Auto backup fallback triggered - cron system unavailable');
            }

            // Log JavaScript pour indiquer l'utilisation du fallback
            if (is_admin()) {
                $time_since_last = round(($now - $last_backup) / 60, 1);
                echo "<script>console.log('[AUTO BACKUP FALLBACK] 🎯 DÉCLENCHEMENT - Système cron indisponible - sauvegarde automatique via fallback (dernière: {$time_since_last}min)');</script>";
            }

            // Exécuter la sauvegarde automatique
            $this->create_auto_backup();

            // Nettoyer le flag de progression
            delete_transient('pdf_builder_auto_backup_in_progress');
        } else {
            // Log quand on ne déclenche pas
            if (is_admin()) {
                $remaining_seconds = $interval_seconds - ($now - $last_backup);
                $remaining_minutes = round($remaining_seconds / 60, 1);
                echo "<script>console.log('[AUTO BACKUP FALLBACK] ⏰ Pas encore le moment - Prochaine sauvegarde dans {$remaining_minutes}min');</script>";
            }
        }
    }

    /**
     * Diagnostique l'état du système cron
     */
    public function diagnose_cron_system() {
        $issues = [];
        $recommendations = [];

        // Vérifier si WP Cron est désactivé
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            $issues[] = "WP Cron est désactivé (DISABLE_WP_CRON = true)";
            $recommendations[] = "Définir DISABLE_WP_CRON à false dans wp-config.php ou supprimer cette ligne";
        }

        // Vérifier les permissions de la base de données
        global $wpdb;
        $test_option = 'pdf_builder_cron_test_' . time();
        $test_result = add_option($test_option, 'test', '', 'no');
        if (!$test_result) {
            $issues[] = "Impossible d'écrire dans la table wp_options";
            $recommendations[] = "Vérifier les permissions de la base de données";
            $recommendations[] = "Vérifier que l'utilisateur MySQL a les droits INSERT/UPDATE";
        } else {
            delete_option($test_option);
        }

        // Vérifier si les tâches peuvent être sauvegardées
        $test_hook = 'pdf_builder_cron_test_' . time();
        $scheduled = wp_schedule_single_event(time() + 3600, $test_hook);

        if (!$scheduled) {
            $issues[] = "Impossible de planifier des événements cron";
            $recommendations[] = "Vérifier les permissions d'écriture sur la base de données";
            $recommendations[] = "Vérifier que la table wp_options est accessible";
            $recommendations[] = "Vérifier la taille de la table wp_options (elle pourrait être corrompue)";
        } else {
            // Nettoyer le test
            wp_clear_scheduled_hook($test_hook);
        }

        // Vérifier les tâches existantes
        $existing_tasks = [];
        foreach (self::TASKS as $task_name => $config) {
            if (wp_next_scheduled($task_name)) {
                $existing_tasks[] = $task_name;
            }
        }

        return [
            'cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'scheduled_tasks' => $existing_tasks,
            'fallback_active' => true // Notre système de fallback
        ];
    }
    public function repair_cron_system() {
        $results = [];

        // Forcer la reprogrammation de toutes les tâches
        $this->schedule_tasks();

        // Vérifier si les tâches ont été reprogrammées
        $scheduled_count = 0;
        foreach (self::TASKS as $task_name => $config) {
            if (wp_next_scheduled($task_name)) {
                $scheduled_count++;
            }
        }

        $results[] = "Reprogrammation des tâches terminée : $scheduled_count tâches planifiées";

        // Tester le système de fallback
        $fallback_test = $this->test_fallback_system();
        $results[] = "Test du système de fallback : " . ($fallback_test ? "Réussi" : "Échoué");

        return $results;
    }
    public function get_backup_statistics() {
        $stats = [
            'total_backups' => 0,
            'last_backup' => null,
            'next_backup' => null,
            'backup_frequency' => get_option('pdf_builder_backup_frequency', 'daily'),
            'cron_status' => 'unknown',
            'fallback_executions' => 0,
            'errors' => []
        ];

        // Compter les sauvegardes totales
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups/';
        if (is_dir($backup_dir)) {
            $files = glob($backup_dir . '*.json');
            $stats['total_backups'] = count($files);

            if (!empty($files)) {
                // Trier par date de modification
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $stats['last_backup'] = date('Y-m-d H:i:s', filemtime($files[0]));
            }
        }

        // Vérifier la prochaine exécution programmée
        $next_scheduled = wp_next_scheduled('pdf_builder_auto_backup');
        if ($next_scheduled) {
            $stats['next_backup'] = date('Y-m-d H:i:s', $next_scheduled);
            $stats['cron_status'] = 'active';
        } else {
            $stats['cron_status'] = 'inactive';
        }

        // Compter les exécutions de fallback
        $fallback_count = get_option('pdf_builder_fallback_executions', 0);
        $stats['fallback_executions'] = $fallback_count;

        // Récupérer les erreurs récentes
        $error_logs = get_option('pdf_builder_backup_errors', []);
        if (is_array($error_logs) && !empty($error_logs)) {
            $stats['errors'] = array_slice($error_logs, -5); // Dernières 5 erreurs
        }

        return $stats;
    }

    /**
     * Tester le système de fallback
     */
    private function test_fallback_system() {
        // Créer un transient de test
        $test_key = 'pdf_builder_fallback_test_' . time();
        set_transient($test_key, time(), 300); // 5 minutes

        // Vérifier si le transient peut être récupéré
        $value = get_transient($test_key);

        // Nettoyer
        delete_transient($test_key);

        return $value !== false;
    }

    /**
     * Crée une sauvegarde automatique
     */
    public function create_auto_backup() {
        try {
            if (!function_exists('pdf_builder_config') || !pdf_builder_config('auto_backup_enabled')) {
                return;
            }

            // Créer une sauvegarde des templates
            $backup_name = 'Sauvegarde automatique ' . date('Y-m-d H:i:s');
            $backup_data = $this->gather_backup_data();

            if (!empty($backup_data)) {
                $this->save_backup($backup_name, $backup_data, 'auto');

                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->info('Automatic backup created successfully');
                }

                // Log JavaScript pour le débogage côté client
                if (is_admin()) {
                    echo "<script>console.log('[AUTO BACKUP PHP] ✅ Sauvegarde automatique créée avec succès (via fallback):', '" . addslashes($backup_name) . "');</script>";
                }

                // Notification de succès
                if (class_exists('PDF_Builder_Notification_Manager')) {
                    $message = __('Sauvegarde automatique créée avec succès !', 'pdf-builder-pro');
                    PDF_Builder_Notification_Manager::get_instance()->success($message, ['duration' => 8000]);
                }

                // Déclencher une action pour mettre à jour l'interface en temps réel
                do_action('pdf_builder_auto_backup_created');
            }

        } catch (Exception $e) {
            $this->log_task_error('auto_backup', $e);
        }
    }

    /**
     * Effectue la rotation des logs
     */
    public function rotate_logs() {
        try {
            if (!class_exists('PDF_Builder_Logger')) {
                return;
            }

            $logger = PDF_Builder_Logger::get_instance();
            $rotated = $logger->rotate_logs();

            if ($rotated) {
                $logger->info('Log rotation completed');
            }

        } catch (Exception $e) {
            $this->log_task_error('log_rotation', $e);
        }
    }

    /**
     * Nettoie les données de performance anciennes
     */
    public function cleanup_performance_data() {
        try {
            if (!class_exists('PDF_Builder_Performance_Monitor')) {
                return;
            }

            $monitor = PDF_Builder_Performance_Monitor::get_instance();
            $monitor->cleanup_performance_logs();

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info('Performance data cleanup completed');
            }

        } catch (Exception $e) {
            $this->log_task_error('performance_cleanup', $e);
        }
    }

    /**
     * Vérifie la santé sécurité
     */
    public function security_health_check() {
        try {
            if (!class_exists('PDF_Builder_Security_Validator')) {
                return;
            }

            $validator = PDF_Builder_Security_Validator::get_instance();

            // Vérifier les tokens CSRF expirés
            $validator->cleanup_csrf_tokens();

            // Vérifier la santé générale
            $health = pdf_builder_health_check();

            if ($health['status'] !== 'healthy') {
                if (class_exists('PDF_Builder_Logger')) {
                    PDF_Builder_Logger::get_instance()->warning('Security health check found issues', [
                        'issues' => $health['issues']
                    ]);
                }
            }

        } catch (Exception $e) {
            $this->log_task_error('security_check', $e);
        }
    }

    /**
     * Optimise les tables de base de données
     */
    public function optimize_database() {
        try {
            global $wpdb;

            $tables = [
                $wpdb->prefix . 'pdf_builder_templates',
                $wpdb->prefix . 'pdf_builder_cache',
                $wpdb->prefix . 'pdf_builder_errors',
                $wpdb->prefix . 'pdf_builder_performance_metrics',
                $wpdb->prefix . 'pdf_builder_performance_issues',
                $wpdb->prefix . 'pdf_builder_backups'
            ];

            $optimized_tables = 0;

            foreach ($tables as $table) {
                if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                    $wpdb->query("OPTIMIZE TABLE $table");
                    $optimized_tables++;
                }
            }

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info("Database optimization completed: $optimized_tables tables optimized");
            }

        } catch (Exception $e) {
            $this->log_task_error('database_optimization', $e);
        }
    }

    /**
     * Rassemble les données pour la sauvegarde
     */
    private function gather_backup_data() {
        global $wpdb;

        $backup_data = [
            'timestamp' => current_time('mysql'),
            'version' => PDF_BUILDER_VERSION,
            'templates' => [],
            'settings' => [],
            'metadata' => []
        ];

        // Sauvegarder les templates
        $templates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pdf_builder_templates", ARRAY_A);
        $backup_data['templates'] = $templates;

        // Sauvegarder les paramètres
        $backup_data['settings'] = get_option('pdf_builder_config', []);

        // Métadonnées
        $backup_data['metadata'] = [
            'total_templates' => count($templates),
            'database_size' => $this->get_database_size(),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION
        ];

        return $backup_data;
    }

    /**
     * Sauvegarde les données
     */
    private function save_backup($name, $data, $type = 'manual') {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';

        $wpdb->insert(
            $table,
            [
                'name' => $name,
                'description' => "Sauvegarde $type créée automatiquement",
                'backup_data' => wp_json_encode($data),
                'user_id' => 0, // Système
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s']
        );

        // Nettoyer les anciennes sauvegardes automatiques
        $retention_count = pdf_builder_config('backup_retention_count', 10);
        $this->cleanup_old_backups($retention_count);
    }

    /**
     * Nettoie les anciennes sauvegardes
     */
    private function cleanup_old_backups($keep_count) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_backups';

        // Supprimer les sauvegardes les plus anciennes, en gardant les N plus récentes
        $wpdb->query($wpdb->prepare("
            DELETE FROM $table
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT id FROM $table
                    ORDER BY created_at DESC
                    LIMIT %d
                ) tmp
            )
        ", $keep_count));
    }

    /**
     * Obtient la taille de la base de données
     */
    private function get_database_size() {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'pdf_builder_templates',
            $wpdb->prefix . 'pdf_builder_cache',
            $wpdb->prefix . 'pdf_builder_errors',
            $wpdb->prefix . 'pdf_builder_performance_metrics',
            $wpdb->prefix . 'pdf_builder_performance_issues',
            $wpdb->prefix . 'pdf_builder_backups'
        ];

        $total_size = 0;

        foreach ($tables as $table) {
            $size = $wpdb->get_var("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_name = '$table'
                AND table_schema = DATABASE()
            ");

            if ($size) {
                $total_size += $size;
            }
        }

        return round($total_size, 2);
    }

    /**
     * Log une erreur de tâche
     */
    private function log_task_error($task_name, $exception) {
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->error("Task $task_name failed", [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        } else {
            error_log("[PDF Builder Task Error] $task_name: " . $exception->getMessage());
        }

        // Notification d'erreur pour les tâches importantes
        if (class_exists('PDF_Builder_Notification_Manager') && $task_name === 'auto_backup') {
            $message = __('Échec de la sauvegarde automatique', 'pdf-builder-pro');
            PDF_Builder_Notification_Manager::get_instance()->error($message, ['duration' => 10000]);
        }
    }

    /**
     * AJAX - Planifie une tâche
     */
    public function schedule_task_ajax() {
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

            $task_name = sanitize_key($_POST['task_name'] ?? '');

            if (!isset(self::TASKS[$task_name])) {
                wp_send_json_error(['message' => 'Tâche inconnue']);
                return;
            }

            if (!wp_next_scheduled($task_name)) {
                wp_schedule_event(time(), self::TASKS[$task_name]['interval'], $task_name);
                wp_send_json_success(['message' => 'Tâche planifiée avec succès']);
            } else {
                wp_send_json_error(['message' => 'La tâche est déjà planifiée']);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Annule la planification d'une tâche
     */
    public function unschedule_task_ajax() {
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

            $task_name = sanitize_key($_POST['task_name'] ?? '');

            if (!isset(self::TASKS[$task_name])) {
                wp_send_json_error(['message' => 'Tâche inconnue']);
                return;
            }

            wp_clear_scheduled_hook($task_name);
            wp_send_json_success(['message' => 'Tâche annulée avec succès']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Exécute une tâche immédiatement
     */
    public function run_task_now_ajax() {
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

            $task_name = sanitize_key($_POST['task_name'] ?? '');

            if (!isset(self::TASKS[$task_name])) {
                wp_send_json_error(['message' => 'Tâche inconnue']);
                return;
            }

            // Démarrer le monitoring de performance
            pdf_builder_start_timer("task_$task_name");

            // Exécuter la tâche
            call_user_func([$this, self::TASKS[$task_name]['callback']]);

            // Obtenir les métriques de performance
            $metrics = pdf_builder_end_timer("task_$task_name");

            wp_send_json_success([
                'message' => 'Tâche exécutée avec succès',
                'execution_time' => $metrics ? round($metrics['duration'], 2) : null
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'exécution: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient le statut de toutes les tâches
     */
    public function get_tasks_status() {
        $status = [];

        foreach (self::TASKS as $task_name => $task_config) {
            $next_run = wp_next_scheduled($task_name);
            $status[$task_name] = [
                'name' => $task_name,
                'description' => $task_config['description'],
                'interval' => $task_config['interval'],
                'scheduled' => $next_run !== false,
                'next_run' => $next_run ? date('Y-m-d H:i:s', $next_run) : null,
                'last_run' => get_option("pdf_builder_last_run_$task_name")
            ];
        }

        return $stats;
    }

    /**
     * AJAX handler pour diagnostiquer le système cron
     */
    public function ajax_diagnose_cron() {
        error_log('PDF Builder: ajax_diagnose_cron called');

        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_admin_nonce')) {
            error_log('PDF Builder: Invalid nonce in ajax_diagnose_cron');
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        error_log('PDF Builder: Nonce valid in ajax_diagnose_cron');

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('PDF Builder: Insufficient permissions in ajax_diagnose_cron');
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        error_log('PDF Builder: Permissions OK in ajax_diagnose_cron');

        try {
            $result = $this->diagnose_cron_system();
            error_log('PDF Builder: diagnose_cron_system completed successfully');
            wp_send_json_success([
                'status' => $result['cron_disabled'] ? 'Cron désactivé' : 'Cron actif',
                'details' => implode("\n", array_merge($result['issues'], $result['recommendations']))
            ]);
        } catch (Exception $e) {
            error_log('PDF Builder: Exception in ajax_diagnose_cron: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur lors du diagnostic: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler pour réparer le système cron
     */
    public function ajax_repair_cron() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_admin_nonce')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        try {
            $result = $this->repair_cron_system();
            wp_send_json_success(['message' => implode("\n", $result)]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la réparation: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler pour obtenir les statistiques de sauvegarde
     */
    public function ajax_get_backup_stats() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_admin_nonce')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        try {
            $stats = $this->get_backup_statistics();
            wp_send_json_success(['stats' => $stats]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler pour créer une sauvegarde manuelle
     */
    public function ajax_create_backup() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_admin_nonce')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        try {
            $this->create_auto_backup();
            wp_send_json_success(['message' => 'Sauvegarde manuelle créée avec succès']);
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la création de la sauvegarde: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler pour changer la fréquence de sauvegarde
     */
    public function ajax_change_backup_frequency() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_admin_nonce')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        $frequency = sanitize_text_field($_POST['frequency'] ?? '');

        try {
            if ($this->set_backup_frequency($frequency)) {
                wp_send_json_success(['message' => 'Fréquence de sauvegarde changée avec succès']);
            } else {
                wp_send_json_error(['message' => 'Fréquence non valide']);
            }
        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du changement de fréquence: ' . $e->getMessage()]);
        }
    }

    /**
     * Callback quand la fréquence de sauvegarde change
     */
    public function on_backup_frequency_changed($old_value, $new_value, $option) {
        if ($old_value !== $new_value) {
            $this->reschedule_auto_backup($new_value);
            error_log("PDF Builder: Fréquence de sauvegarde changée de '$old_value' à '$new_value', reprogrammation effectuée");
        }
    }

    /**
     * Callback quand l'activation des sauvegardes automatiques change
     */
    public function on_auto_backup_enabled_changed($old_value, $new_value, $option) {
        if ($old_value !== $new_value) {
            if ($new_value === '1') {
                // Réactiver les sauvegardes
                $frequency = get_option('pdf_builder_backup_frequency', 'daily');
                $this->reschedule_auto_backup($frequency);
                error_log("PDF Builder: Sauvegardes automatiques activées, fréquence: $frequency");
            } else {
                // Désactiver les sauvegardes
                wp_clear_scheduled_hook('pdf_builder_auto_backup');
                error_log("PDF Builder: Sauvegardes automatiques désactivées");
            }
        }
    }

    /**
     * Change la fréquence de sauvegarde automatique
     */
    public function set_backup_frequency($frequency) {
        $allowed_frequencies = ['every_minute', 'daily', 'weekly', 'monthly'];

        if (!in_array($frequency, $allowed_frequencies)) {
            return false;
        }

        update_option('pdf_builder_auto_backup_frequency', $frequency);
        $this->reschedule_auto_backup($frequency);

        error_log("PDF Builder: Backup frequency changed to $frequency");
        return true;
    }
}

// Fonctions globales
function pdf_builder_get_tasks_status() {
    return PDF_Builder_Task_Scheduler::get_instance()->get_tasks_status();
}

function pdf_builder_schedule_task($task_name) {
    if (isset(PDF_Builder_Task_Scheduler::TASKS[$task_name])) {
        wp_schedule_event(time(), PDF_Builder_Task_Scheduler::TASKS[$task_name]['interval'], $task_name);
        return true;
    }
    return false;
}

function pdf_builder_unschedule_task($task_name) {
    wp_clear_scheduled_hook($task_name);
    return true;
}

// Initialiser le planificateur de tâches
add_action('init', function() {
    PDF_Builder_Task_Scheduler::get_instance();
});

/**
 * Fonction utilitaire pour changer la fréquence de sauvegarde
 */
function pdf_builder_set_backup_frequency($frequency) {
    return PDF_Builder_Task_Scheduler::get_instance()->set_backup_frequency($frequency);
}