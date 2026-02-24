<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
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

        // init_hooks() will be called later when WordPress is loaded

    }

    /**
     * Initialize the task scheduler when WordPress is ready
     */
    public function init() {
        $this->init_hooks();
        $this->schedule_tasks();

    }

    /**
     * Programme toutes les tâches définies
     */
    private function schedule_tasks() {
        foreach (self::TASKS as $task_name => $task_config) {
            if (!wp_next_scheduled($task_name)) {
                $interval = $task_config['interval'];
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
        add_action('wp_ajax_pdf_builder_check_wp_cron_config', [$this, 'ajax_check_wp_cron_config']);
        add_action('wp_ajax_pdf_builder_check_scheduled_tasks', [$this, 'ajax_check_scheduled_tasks']);
        add_action('wp_ajax_pdf_builder_cron_test', [$this, 'ajax_cron_test']);

        // S'assurer que les actions AJAX sont enregistrées pour l'admin
        add_action('init', [$this, 'register_ajax_actions']);
    }

    /**
     * Enregistre les actions AJAX pour le diagnostic cron
     */
    public function register_ajax_actions() {

        add_action('wp_ajax_pdf_builder_get_backup_stats', [$this, 'ajax_get_backup_stats']);
        add_action('wp_ajax_pdf_builder_create_backup', [$this, 'ajax_create_backup']);

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
     * Effectue la rotation des logs
     */

    /**
     * Nettoie les données de performance anciennes
     */

    /**
     * Vérifie la santé sécurité
     */

    /**
     * Optimise les tables de base de données
     */

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
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(wp_json_encode([
                'task' => $task_name,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]));
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
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
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
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
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
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
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
     * AJAX handler pour vérifier la configuration WP Cron
     */
    public function ajax_check_wp_cron_config() {
        // Vérifier le nonce
        if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        $cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;

        wp_send_json_success([
            'cron_disabled' => $cron_disabled,
            'cron_constant_defined' => defined('DISABLE_WP_CRON')
        ]);
    }

    /**
     * AJAX handler pour vérifier les tâches planifiées
     */
    public function ajax_check_scheduled_tasks() {
        // Vérifier le nonce
        if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        $scheduled_tasks = [];
        foreach (self::TASKS as $task_name => $config) {
            if (wp_next_scheduled($task_name)) {
                $scheduled_tasks[] = $task_name;
            }
        }

        wp_send_json_success([
            'scheduled_tasks' => $scheduled_tasks,
            'total_tasks' => count(self::TASKS)
        ]);
    }

    /**
     * AJAX handler pour tester la réponse du système cron
     */
    public function ajax_cron_test() {

        
        // Vérifier le nonce
        if (!pdf_builder_verify_nonce($_GET['nonce'] ?? '', 'pdf_builder_cron_test')) {

            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }



        // Test simple de réponse
        wp_send_json_success([
            'test' => 'ok',
            'timestamp' => time(),
            'message' => 'WP Cron system is responding'
        ]);
    }

    /**
     * AJAX handler pour obtenir les statistiques de sauvegarde
     */
    public function ajax_get_backup_stats() {
        // Vérifier le nonce
        if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        $stats = [
            'total_backups' => 0,
            'manual_backups' => 0,
            'last_backup' => null
        ];

        // Scanner le dossier de sauvegarde
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups/';
        if (is_dir($backup_dir)) {
            $files = glob($backup_dir . '*.json');
            if ($files) {
                $stats['total_backups'] = count($files);

                foreach ($files as $file) {
                    $filename = basename($file);
                    $stats['manual_backups']++;
                }

                // Trier par date (le plus récent en premier)
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });

                if (!empty($files)) {
                    $stats['last_backup'] = basename($files[0]);
                }
            }
        }

        wp_send_json_success($stats);
    }

    /**
     * AJAX handler pour créer une sauvegarde manuelle
     */
    public function ajax_create_backup() {
        // Vérifier le nonce
        if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        try {
            if (class_exists('\\PDF_Builder\\Managers\\PDF_Builder_Backup_Restore_Manager')) {
                $backup_manager = \PDF_Builder\Managers\PDF_Builder_Backup_Restore_Manager::getInstance();
                $result = $backup_manager->createBackup();

                if (is_wp_error($result)) {
                    wp_send_json_error(['message' => $result->get_error_message()]);
                } else {
                    wp_send_json_success([
                        'message' => 'Sauvegarde créée avec succès',
                        'file' => $result['filename'] ?? null
                    ]);
                }
            } else {
                wp_send_json_error(['message' => 'Gestionnaire de sauvegarde non disponible']);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Nettoyer le cache expiré (fonction supprimée - système de cache retiré)
     */
    public function cleanup_expired_cache() {
        // Système de cache supprimé - cette méthode ne fait plus rien
        return;
    }

    /**
     * Callback pour la rotation des logs
     */

    /**
     * Callback pour nettoyer les données de performance
     */

    /**
     * Callback pour la vérification de santé sécurité
     */

    /**
     * Callback pour l'optimisation de la base de données
     */
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



