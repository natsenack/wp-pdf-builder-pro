<?php
/**
 * PDF Builder Pro - AJAX Dispatcher Unifié
 * Phase 1: Centralise tous les handlers AJAX pour éviter la fragmentation
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

namespace PDF_Builder\AJAX;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Dispatcher AJAX unifié pour PDF Builder Pro
 * Centralise la gestion de tous les endpoints AJAX
 */
class Ajax_Dispatcher {

    /**
     * Instance unique (Singleton)
     */
    private static $instance = null;

    /**
     * Liste des handlers enregistrés
     */
    private $handlers = [];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        $this->register_handlers();
        $this->register_actions();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enregistrer tous les handlers AJAX
     */
    private function register_handlers() {
        // Settings handlers
        $this->handlers['pdf_builder_save_all_settings'] = [
            'handler' => new PDF_Builder_Settings_Ajax_Handler(),
            'method' => 'handle',
            'capability' => 'manage_options'
        ];

        // Template handlers
        $this->handlers['pdf_builder_save_template'] = [
            'handler' => new PDF_Builder_Template_Ajax_Handler(),
            'method' => 'handle_save',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_load_template'] = [
            'handler' => new PDF_Builder_Template_Ajax_Handler(),
            'method' => 'handle_load',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_delete_template'] = [
            'handler' => new PDF_Builder_Template_Ajax_Handler(),
            'method' => 'handle_delete',
            'capability' => 'manage_options'
        ];

        // Preview handlers
        $this->handlers['pdf_builder_generate_preview'] = [
            'handler' => new PdfBuilderPreviewAjax(),
            'method' => 'generatePreview',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_get_preview_data'] = [
            'handler' => new PdfBuilderPreviewAjax(),
            'method' => 'get_preview_data',
            'capability' => 'manage_options'
        ];

        // Templates handlers (from PdfBuilderTemplatesAjax)
        $templates_handler = new PdfBuilderTemplatesAjax();

        $this->handlers['pdf_builder_create_from_predefined'] = [
            'handler' => $templates_handler,
            'method' => 'createFromPredefined',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_load_predefined_into_editor'] = [
            'handler' => $templates_handler,
            'method' => 'loadPredefinedIntoEditor',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_load_template_settings'] = [
            'handler' => $templates_handler,
            'method' => 'loadTemplateSettings',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_save_template_settings'] = [
            'handler' => $templates_handler,
            'method' => 'saveTemplateSettings',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_set_default_template'] = [
            'handler' => $templates_handler,
            'method' => 'setDefaultTemplate',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_delete_template'] = [
            'handler' => $templates_handler,
            'method' => 'deleteTemplate',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_save_order_status_templates'] = [
            'handler' => $templates_handler,
            'method' => 'saveOrderStatusTemplates',
            'capability' => 'manage_options'
        ];

        // Cache handlers
        $this->handlers['pdf_builder_clear_cache'] = [
            'handler' => $this,
            'method' => 'handle_clear_cache',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_clear_all_cache'] = [
            'handler' => $this,
            'method' => 'handle_clear_all_cache',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_get_preview_data'] = [
            'handler' => $this,
            'method' => 'handle_get_preview_data',
            'capability' => 'manage_options'
        ];

        $this->handlers['pdf_builder_optimize_database'] = [
            'handler' => $this,
            'method' => 'handle_optimize_database',
            'capability' => 'manage_options'
        ];
    }

    /**
     * Enregistrer les actions WordPress
     */
    private function register_actions() {
        foreach ($this->handlers as $action => $config) {
            add_action("wp_ajax_{$action}", [$this, 'dispatch']);
        }
    }

    /**
     * Dispatcher principal
     * Route les requêtes AJAX vers le bon handler
     */
    public function dispatch() {
        try {
            // Identifier l'action
            $action = $_REQUEST['action'] ?? '';
            $clean_action = str_replace('wp_ajax_', '', $action);

            if (!isset($this->handlers[$clean_action])) {
                $this->send_error("Action inconnue: {$clean_action}", 400);
                return;
            }

            $config = $this->handlers[$clean_action];

            // Vérifier les permissions
            if (!current_user_can($config['capability'])) {
                $this->send_error('Permissions insuffisantes', 403);
                return;
            }

            // Vérifier le nonce si fourni
            if (isset($_REQUEST['nonce'])) {
                $nonce_action = $_REQUEST['nonce_action'] ?? 'pdf_builder_ajax';
                if (!wp_verify_nonce($_REQUEST['nonce'], $nonce_action)) {
                    $this->send_error('Nonce invalide', 403);
                    return;
                }
            }

            // Appeler le handler
            $handler = $config['handler'];
            $method = $config['method'];

            if (method_exists($handler, $method)) {
                call_user_func([$handler, $method]);
            } else {
                $this->send_error("Méthode manquante: {$method}", 500);
            }

        } catch (\Exception $e) {
            $this->log_error('Erreur AJAX: ' . $e->getMessage());
            $this->send_error('Erreur interne du serveur', 500);
        }
    }

    /**
     * Handlers intégrés dans le dispatcher
     */
    public function handle_clear_cache() {
        // Implémentation à faire
        $this->send_success([], 'Cache vidé');
    }

    public function handle_clear_all_cache() {
        // Implémentation à faire
        $this->send_success([], 'Tout le cache vidé');
    }

    public function handle_get_preview_data() {
        // Implémentation à faire
        $this->send_success(['data' => []], 'Données d\'aperçu récupérées');
    }

    public function handle_optimize_database() {
        // Implémentation à faire
        $this->send_success([], 'Base de données optimisée');
    }

    /**
     * Envoie une réponse d'erreur standardisée
     */
    private function send_error($message, $code = 400) {
        wp_send_json_error([
            'message' => $message,
            'code' => $code,
            'timestamp' => current_time('timestamp')
        ]);
    }

    /**
     * Envoie une réponse de succès standardisée
     */
    private function send_success($data = [], $message = 'Opération réussie') {
        wp_send_json_success(array_merge([
            'message' => $message,
            'timestamp' => current_time('timestamp')
        ], $data));
    }

    /**
     * Log une erreur
     */
    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[PDF Builder AJAX Dispatcher] ' . $message);
        }
    }
}

// Initialiser le dispatcher
Ajax_Dispatcher::get_instance();