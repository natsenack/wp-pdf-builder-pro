<?php
/**
 * PDF Builder Pro - Planificateur de tâches
 * Gère les tâches automatiques et planifiées du plugin
 */

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
        $this->init_hooks();
        $this->schedule_tasks();
    }

    private function init_hooks() {
        // Activation/désactivation des tâches
        add_action('wp_ajax_pdf_builder_schedule_task', [$this, 'schedule_task_ajax']);
        add_action('wp_ajax_pdf_builder_unschedule_task', [$this, 'unschedule_task_ajax']);
        add_action('wp_ajax_pdf_builder_run_task_now', [$this, 'run_task_now_ajax']);

        // Enregistrer les intervalles personnalisés
        add_filter('cron_schedules', [$this, 'add_custom_cron_schedules']);

        // Hooks pour les tâches planifiées
        foreach (self::TASKS as $task_name => $task_config) {
            add_action($task_name, [$this, $task_config['callback']]);
        }

        // Fallback pour les sauvegardes automatiques quand le cron système ne fonctionne pas
        add_action('admin_init', [$this, 'check_auto_backup_fallback']);
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

        // Vérifier si assez de temps s'est écoulé depuis la dernière sauvegarde
        if (($now - $last_backup) >= $interval_seconds) {
            // Marquer que nous allons faire une sauvegarde pour éviter les exécutions multiples
            update_option('pdf_builder_last_auto_backup', $now);

            // Logger le fallback
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info('Auto backup fallback triggered - cron system unavailable');
            }

            // Log JavaScript pour indiquer l'utilisation du fallback
            if (is_admin()) {
                $time_since_last = round(($now - $last_backup) / 60, 1);
                echo "<script>console.log('[AUTO BACKUP FALLBACK] 🔄 Système cron indisponible - sauvegarde automatique déclenchée via fallback (dernière sauvegarde: " . $time_since_last . " min)');</script>";
            }

            // Exécuter la sauvegarde automatique
            $this->create_auto_backup();
        }
    }

    /**
     * Nettoie le cache expiré
     */
    public function cleanup_expired_cache() {
        try {
            if (!class_exists('PDF_Builder_Smart_Cache')) {
                return;
            }

            $cache = PDF_Builder_Smart_Cache::get_instance();
            $cleaned = $cache->cleanup_expired();

            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info("Cache cleanup completed: $cleaned items removed");
            }

        } catch (Exception $e) {
            $this->log_task_error('cache_cleanup', $e);
        }
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

        return $status;
    }

    /**
     * Met à jour la dernière exécution d'une tâche
     */
    private function update_last_run($task_name) {
        update_option("pdf_builder_last_run_$task_name", current_time('mysql'));
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
add_action('plugins_loaded', function() {
    PDF_Builder_Task_Scheduler::get_instance();
});