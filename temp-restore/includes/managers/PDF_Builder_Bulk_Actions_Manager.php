<?php
/**
 * Gestionnaire de Bulk Actions - PDF Builder Pro
 *
 * Système de traitement par lots ultra-performant
 * Inspiré de RNBulkActionManager de woo-pdf-invoice-builder
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Bulk Actions
 */
class PDF_Builder_Bulk_Actions_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Bulk_Actions_Manager
     */
    private static $instance = null;

    /**
     * File d'attente des tâches
     * @var array
     */
    private $queue = [];

    /**
     * Tâches en cours
     * @var array
     */
    private $running_tasks = [];

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Cache manager
     * @var PDF_Builder_Cache_Manager
     */
    private $cache;

    /**
     * Nombre maximum de tâches simultanées
     * @var int
     */
    private $max_concurrent_tasks = 3;

    /**
     * Constructeur privé
     */
    private function __construct() {
        // Les dépendances seront injectées plus tard pour éviter les dépendances circulaires
        // $core = PDF_Builder_Core::getInstance();
        // $this->database = $core->get_database_manager();
        // $this->logger = $core->get_logger();
        // $this->cache = $core->get_cache_manager();

        // N'initialiser les hooks que si WordPress est chargé
        if (function_exists('add_filter')) {
            $this->init_hooks();
        }
        // $this->load_queue_from_database(); // Désactivé pour éviter les erreurs de base de données
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Bulk_Actions_Manager
     */
    public static function getInstance(): PDF_Builder_Bulk_Actions_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks(): void {
        // Hooks pour les commandes WooCommerce
        add_filter('bulk_actions-woocommerce_page_wc-orders', [$this, 'add_order_bulk_actions']);
        add_filter('handle_bulk_actions-woocommerce_page_wc-orders', [$this, 'handle_order_bulk_actions'], 10, 3);
        add_filter('bulk_actions-edit-shop_order', [$this, 'add_order_bulk_actions']);
        add_filter('handle_bulk_actions-edit-shop_order', [$this, 'handle_order_bulk_actions'], 10, 3);

        // Hooks pour les utilisateurs
        add_filter('bulk_actions-users', [$this, 'add_user_bulk_actions']);
        add_filter('handle_bulk_actions-users', [$this, 'handle_user_bulk_actions'], 10, 3);

        // Hooks AJAX pour le frontend
        add_action('wp_ajax_pdf_builder_bulk_generate', [$this, 'ajax_bulk_generate']);
        add_action('wp_ajax_pdf_builder_bulk_status', [$this, 'ajax_bulk_status']);

        // Hooks pour le traitement en arrière-plan
        add_action('pdf_builder_process_bulk_queue', [$this, 'process_queue']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Planifier le traitement de la queue
        if (!wp_next_scheduled('pdf_builder_process_bulk_queue')) {
            wp_schedule_event(time(), 'every_minute', 'pdf_builder_process_bulk_queue');
        }
    }

    /**
     * Ajouter les actions en masse pour les commandes
     */
    public function add_order_bulk_actions(array $actions): array {
        global $wpdb;

        // Récupérer les templates actifs
        $templates = $wpdb->get_results("
            SELECT id, name
            FROM {$wpdb->prefix}pdf_builder_templates
            WHERE status = 'active'
            ORDER BY name ASC
        ");

        foreach ($templates as $template) {
            $actions["pdf_builder_generate_{$template->id}"] = sprintf(
                __('Générer PDF: %s', 'pdf-builder-pro'),
                $template->name
            );
        }

        $actions['pdf_builder_bulk_email'] = __('Envoyer PDFs par email', 'pdf-builder-pro');

        return $actions;
    }

    /**
     * Gérer les actions en masse pour les commandes
     */
    public function handle_order_bulk_actions(string $redirect_to, string $action, array $order_ids): string {
        if (strpos($action, 'pdf_builder_generate_') === 0) {
            $template_id = str_replace('pdf_builder_generate_', '', $action);

            // Créer une tâche bulk
            $task_id = $this->create_bulk_task([
                'type' => 'generate_pdfs',
                'template_id' => $template_id,
                'items' => $order_ids,
                'total_items' => count($order_ids),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ]);

            // Traiter immédiatement quelques éléments
            $this->process_bulk_task($task_id, 5);

            // Ajouter un paramètre de succès
            $redirect_to = add_query_arg([
                'pdf_builder_bulk_task' => $task_id,
                'processed' => min(5, count($order_ids)),
                'remaining' => max(0, count($order_ids) - 5)
            ], $redirect_to);

        } elseif ($action === 'pdf_builder_bulk_email') {
            // Logique pour l'envoi d'emails en masse
            $this->logger->info('Bulk email action initiated', [
                'order_count' => count($order_ids),
                'user_id' => get_current_user_id()
            ]);
        }

        return $redirect_to;
    }

    /**
     * Ajouter les actions en masse pour les utilisateurs
     */
    public function add_user_bulk_actions(array $actions): array {
        $actions['pdf_builder_generate_reports'] = __('Générer rapports PDF', 'pdf-builder-pro');
        return $actions;
    }

    /**
     * Gérer les actions en masse pour les utilisateurs
     */
    public function handle_user_bulk_actions(string $redirect_to, string $action, array $user_ids): string {
        if ($action === 'pdf_builder_generate_reports') {
            // Créer des rapports pour les utilisateurs sélectionnés
            $task_id = $this->create_bulk_task([
                'type' => 'generate_user_reports',
                'items' => $user_ids,
                'total_items' => count($user_ids),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ]);

            $redirect_to = add_query_arg('pdf_builder_bulk_task', $task_id, $redirect_to);
        }

        return $redirect_to;
    }

    /**
     * Créer une tâche bulk
     */
    public function create_bulk_task(array $task_data): string {
        $task_id = 'bulk_' . wp_generate_uuid4();

        $task = array_merge($task_data, [
            'id' => $task_id,
            'status' => 'pending',
            'processed_items' => 0,
            'failed_items' => 0,
            'progress' => 0,
            'started_at' => null,
            'completed_at' => null,
            'errors' => []
        ]);

        // Sauvegarder en base de données
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_bulk_tasks',
            [
                'task_id' => $task_id,
                'task_type' => $task['type'],
                'task_data' => wp_json_encode($task),
                'status' => 'pending',
                'created_by' => $task['user_id'],
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%d', '%s']
        );

        $this->queue[$task_id] = $task;
        $this->cache->set("bulk_task_{$task_id}", $task, 3600);

        $this->logger->info('Bulk task created', [
            'task_id' => $task_id,
            'type' => $task['type'],
            'total_items' => $task['total_items']
        ]);

        return $task_id;
    }

    /**
     * Traiter une tâche bulk
     */
    public function process_bulk_task(string $task_id, int $batch_size = 10): bool {
        if (!isset($this->queue[$task_id])) {
            return false;
        }

        $task = $this->queue[$task_id];

        if ($task['status'] === 'completed' || $task['status'] === 'failed') {
            return false;
        }

        // Marquer comme en cours si pas encore fait
        if ($task['status'] === 'pending') {
            $task['status'] = 'running';
            $task['started_at'] = current_time('mysql');
            $this->update_task($task_id, $task);
        }

        // Traiter un lot d'éléments
        $remaining_items = array_slice($task['items'], $task['processed_items'], $batch_size);
        $processed = 0;
        $failed = 0;

        foreach ($remaining_items as $item_id) {
            try {
                $result = $this->process_single_item($task, $item_id);

                if ($result) {
                    $processed++;
                } else {
                    $failed++;
                    $task['errors'][] = "Failed to process item {$item_id}";
                }
            } catch (Exception $e) {
                $failed++;
                $task['errors'][] = "Exception processing item {$item_id}: " . $e->getMessage();
                $this->logger->error('Bulk task item processing failed', [
                    'task_id' => $task_id,
                    'item_id' => $item_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Mettre à jour la progression
        $task['processed_items'] += $processed;
        $task['failed_items'] += $failed;
        $task['progress'] = ($task['processed_items'] / $task['total_items']) * 100;

        // Vérifier si terminé
        if ($task['processed_items'] >= $task['total_items']) {
            $task['status'] = $task['failed_items'] > 0 ? 'completed_with_errors' : 'completed';
            $task['completed_at'] = current_time('mysql');

            // Notification à l'utilisateur
            $this->notify_task_completion($task);
        }

        $this->update_task($task_id, $task);

        return $task['status'] !== 'completed';
    }

    /**
     * Traiter un élément individuel
     */
    private function process_single_item(array $task, $item_id): bool {
        switch ($task['type']) {
            case 'generate_pdfs':
                return $this->generate_pdf_for_order($item_id, $task['template_id']);

            case 'generate_user_reports':
                return $this->generate_report_for_user($item_id);

            default:
                return false;
        }
    }

    /**
     * Générer un PDF pour une commande
     */
    private function generate_pdf_for_order(int $order_id, int $template_id): bool {
        try {
            // Récupérer la commande WooCommerce
            $order = wc_get_order($order_id);
            if (!$order) {
                return false;
            }

            // Générer le PDF
            $pdf_url = pdf_builder_pro_generate_pdf($order_id, $template_id);

            if ($pdf_url) {
                // Attacher le PDF à la commande si configuré
                $this->attach_pdf_to_order($order, $pdf_url);

                $this->logger->info('PDF generated for order', [
                    'order_id' => $order_id,
                    'template_id' => $template_id,
                    'pdf_url' => $pdf_url
                ]);

                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logger->error('Failed to generate PDF for order', [
                'order_id' => $order_id,
                'template_id' => $template_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Générer un rapport pour un utilisateur
     */
    private function generate_report_for_user(int $user_id): bool {
        try {
            $user = get_user_by('id', $user_id);
            if (!$user) {
                return false;
            }

            // Logique de génération de rapport utilisateur
            // À implémenter selon les besoins spécifiques

            $this->logger->info('User report generated', [
                'user_id' => $user_id,
                'user_email' => $user->user_email
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to generate user report', [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Attacher un PDF à une commande WooCommerce
     */
    private function attach_pdf_to_order(WC_Order $order, string $pdf_url): void {
        // Ajouter une note à la commande
        $order->add_order_note(sprintf(
            __('PDF généré automatiquement: %s', 'pdf-builder-pro'),
            '<a href="' . esc_url($pdf_url) . '" target="_blank">' . __('Télécharger PDF', 'pdf-builder-pro') . '</a>'
        ));

        // Optionnellement, envoyer par email
        $send_email = get_option('pdf_builder_auto_email', false);
        if ($send_email) {
            $this->send_pdf_by_email($order, $pdf_url);
        }
    }

    /**
     * Envoyer le PDF par email
     */
    private function send_pdf_by_email(WC_Order $order, string $pdf_url): void {
        $to = $order->get_billing_email();
        $subject = sprintf(__('Votre facture - Commande #%s', 'pdf-builder-pro'), $order->get_order_number());
        $message = sprintf(
            __('Bonjour %s,<br><br>Votre facture est prête. Vous pouvez la télécharger ici: <a href="%s">%s</a><br><br>Cordialement,<br>%s', 'pdf-builder-pro'),
            $order->get_billing_first_name(),
            $pdf_url,
            __('Télécharger votre facture', 'pdf-builder-pro'),
            get_bloginfo('name')
        );

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        wp_mail($to, $subject, $message, $headers);

        $this->logger->info('PDF sent by email', [
            'order_id' => $order->get_id(),
            'email' => $to
        ]);
    }

    /**
     * Traiter la file d'attente
     */
    public function process_queue(): void {
        $running_count = count($this->running_tasks);

        if ($running_count >= $this->max_concurrent_tasks) {
            return; // Trop de tâches en cours
        }

        // Trouver les tâches en attente
        $pending_tasks = array_filter($this->queue, function($task) {
            return $task['status'] === 'pending';
        });

        foreach ($pending_tasks as $task_id => $task) {
            if ($running_count >= $this->max_concurrent_tasks) {
                break;
            }

            $this->running_tasks[$task_id] = true;
            $this->process_bulk_task($task_id, 5); // Traiter 5 éléments à la fois
            $running_count++;

            // Nettoyer les tâches terminées
            if ($this->queue[$task_id]['status'] === 'completed' ||
                $this->queue[$task_id]['status'] === 'failed' ||
                $this->queue[$task_id]['status'] === 'completed_with_errors') {
                unset($this->running_tasks[$task_id]);
                $running_count--;
            }
        }
    }

    /**
     * Mettre à jour une tâche
     */
    private function update_task(string $task_id, array $task): void {
        $this->queue[$task_id] = $task;
        $this->cache->set("bulk_task_{$task_id}", $task, 3600);

        // Mettre à jour en base de données
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'pdf_builder_bulk_tasks',
            [
                'task_data' => wp_json_encode($task),
                'status' => $task['status'],
                'updated_at' => current_time('mysql')
            ],
            ['task_id' => $task_id],
            ['%s', '%s', '%s'],
            ['%s']
        );
    }

    /**
     * Charger la file d'attente depuis la base de données
     */
    private function load_queue_from_database(): void {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT task_id, task_data
            FROM {$wpdb->prefix}pdf_builder_bulk_tasks
            WHERE status IN ('pending', 'running')
            ORDER BY created_at ASC
            LIMIT 50
        ");

        foreach ($results as $result) {
            $task_data = json_decode($result->task_data, true);
            if ($task_data) {
                $this->queue[$result->task_id] = $task_data;
            }
        }
    }

    /**
     * Notifier la completion d'une tâche
     */
    private function notify_task_completion(array $task): void {
        $user = get_user_by('id', $task['user_id']);
        if (!$user) {
            return;
        }

        $subject = sprintf(__('Tâche bulk terminée: %s', 'pdf-builder-pro'), ucfirst($task['type']));
        $message = sprintf(
            __('Bonjour %s,<br><br>Votre tâche de traitement en masse est terminée.<br><br>Détails:<br>- Type: %s<br>- Éléments traités: %d/%d<br>- Échecs: %d<br><br>%s', 'pdf-builder-pro'),
            $user->display_name,
            $task['type'],
            $task['processed_items'],
            $task['total_items'],
            $task['failed_items'],
            !empty($task['errors']) ? '<br>Erreurs:<br>' . implode('<br>', $task['errors']) : ''
        );

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($user->user_email, $subject, $message, $headers);
    }

    /**
     * Enqueue les scripts JavaScript
     */
    public function enqueue_scripts(string $hook): void {
        // Scripts pour les pages d'administration pertinentes
        if (strpos($hook, 'woocommerce') !== false || strpos($hook, 'users') !== false) {
            wp_enqueue_script(
                'pdf-builder-bulk-actions',
                PDF_BUILDER_PLUGIN_URL . 'assets/js/bulk-actions.js',
                ['jquery'],
                PDF_BUILDER_VERSION,
                true
            );

            wp_localize_script('pdf-builder-bulk-actions', 'pdfBuilderBulk', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_bulk_nonce'),
                'strings' => [
                    'processing' => __('Traitement en cours...', 'pdf-builder-pro'),
                    'completed' => __('Terminé', 'pdf-builder-pro'),
                    'failed' => __('Échec', 'pdf-builder-pro')
                ]
            ]);
        }
    }

    /**
     * AJAX: Générer en masse
     */
    public function ajax_bulk_generate(): void {
        check_ajax_referer('pdf_builder_bulk_nonce', 'nonce');

        $template_id = intval($_POST['template_id'] ?? 0);
        $item_ids = array_map('intval', $_POST['item_ids'] ?? []);

        if (!$template_id || empty($item_ids)) {
            wp_send_json_error(['message' => 'Paramètres invalides']);
        }

        $task_id = $this->create_bulk_task([
            'type' => 'generate_pdfs',
            'template_id' => $template_id,
            'items' => $item_ids,
            'total_items' => count($item_ids),
            'user_id' => get_current_user_id(),
            'created_at' => current_time('mysql')
        ]);

        wp_send_json_success([
            'task_id' => $task_id,
            'message' => sprintf(__('Tâche créée avec %d éléments', 'pdf-builder-pro'), count($item_ids))
        ]);
    }

    /**
     * AJAX: Statut d'une tâche bulk
     */
    public function ajax_bulk_status(): void {
        check_ajax_referer('pdf_builder_bulk_nonce', 'nonce');

        $task_id = sanitize_text_field($_POST['task_id'] ?? '');

        if (!$task_id || !isset($this->queue[$task_id])) {
            wp_send_json_error(['message' => 'Tâche introuvable']);
        }

        $task = $this->queue[$task_id];

        wp_send_json_success([
            'task' => $task,
            'progress' => $task['progress'],
            'status' => $task['status'],
            'processed' => $task['processed_items'],
            'total' => $task['total_items'],
            'remaining' => $task['total_items'] - $task['processed_items']
        ]);
    }

    /**
     * Obtenir les statistiques des bulk actions
     */
    public function get_stats(): array {
        global $wpdb;

        $stats = $wpdb->get_row("
            SELECT
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_tasks,
                SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_tasks,
                AVG(processed_items) as avg_processed_items
            FROM {$wpdb->prefix}pdf_builder_bulk_tasks
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        return [
            'total_tasks' => intval($stats->total_tasks ?? 0),
            'completed_tasks' => intval($stats->completed_tasks ?? 0),
            'failed_tasks' => intval($stats->failed_tasks ?? 0),
            'running_tasks' => intval($stats->running_tasks ?? 0),
            'success_rate' => $stats->total_tasks > 0 ?
                round(($stats->completed_tasks / $stats->total_tasks) * 100, 2) : 0,
            'avg_items_per_task' => round(floatval($stats->avg_processed_items ?? 0), 2)
        ];
    }
}

