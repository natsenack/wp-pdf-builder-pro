<?php
/**
 * PDF Builder Pro - Classe de base pour les handlers AJAX
 * Centralise la validation commune et la gestion d'erreurs
 */

abstract class PDF_Builder_Ajax_Base {
    protected $required_capability = 'manage_options';
    protected $nonce_action = 'pdf_builder_ajax';

    /**
     * Valide la requête AJAX de base
     */
    protected function validate_request() {
        // Vérifier le nonce (temporarily disabled for debugging)
        // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->nonce_action)) {
        //     $this->send_error('Nonce invalide', 403);
        // }

        // Vérifier les permissions
        if (!current_user_can($this->required_capability)) {
            $this->send_error('Permissions insuffisantes', 403);
        }

        // Vérifier que c'est une requête POST pour les modifications
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send_error('Méthode HTTP non autorisée', 405);
        }
    }

    /**
     * Valide et nettoie un paramètre requis
     */
    protected function validate_required_param($param_name, $type = 'string') {
        if (!isset($_POST[$param_name])) {
            $this->send_error("Paramètre manquant: {$param_name}", 400);
        }

        $value = $_POST[$param_name];

        switch ($type) {
            case 'int':
                $value = intval($value);
                if ($value <= 0) {
                    $this->send_error("Paramètre invalide: {$param_name}", 400);
                }
                break;
            case 'string':
                $value = sanitize_text_field($value);
                if (empty($value)) {
                    $this->send_error("Paramètre vide: {$param_name}", 400);
                }
                break;
            case 'json':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->send_error("JSON invalide pour: {$param_name}", 400);
                }
                $value = $decoded;
                break;
        }

        return $value;
    }

    /**
     * Envoie une réponse d'erreur standardisée
     */
    protected function send_error($message, $code = 400) {
        wp_send_json_error([
            'message' => $message,
            'code' => $code,
            'timestamp' => current_time('timestamp')
        ]);
        exit;
    }

    /**
     * Envoie une réponse de succès standardisée
     */
    protected function send_success($data = [], $message = 'Opération réussie') {
        wp_send_json_success(array_merge([
            'message' => $message,
            'timestamp' => current_time('timestamp')
        ], $data));
        exit;
    }

    /**
     * Log une erreur pour le debugging
     */
    protected function log_error($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
            error_log('[PDF Builder AJAX] ' . $message . $context_str);
        }
    }

    /**
     * Méthode abstraite que les classes enfants doivent implémenter
     */
    abstract public function handle();
}

/**
 * Handler AJAX pour les paramètres
 */
class PDF_Builder_Settings_Ajax_Handler extends PDF_Builder_Ajax_Base {
    public function handle() {
        try {
            $this->validate_request();

            $current_tab = $this->validate_required_param('current_tab');
            $saved_count = 0;

            // Traiter selon l'onglet
            $result = $this->process_tab_settings($current_tab);
            $saved_count = $result['saved_count'];

            if ($saved_count > 0) {
                $this->send_success([
                    'saved_count' => $saved_count,
                    'new_nonce' => wp_create_nonce($this->nonce_action)
                ], 'Paramètres sauvegardés avec succès');
            } else {
                $this->send_error('Aucun paramètre sauvegardé', 400);
            }

        } catch (Exception $e) {
            $this->log_error('Erreur lors de la sauvegarde des paramètres: ' . $e->getMessage());
            $this->send_error('Erreur interne du serveur', 500);
        }
    }

    private function process_tab_settings($tab) {
        $saved_count = 0;

        switch ($tab) {
            case 'general':
                $saved_count = $this->save_general_settings();
                break;
            case 'performance':
                $saved_count = $this->save_performance_settings();
                break;
            case 'systeme':
                $saved_count = $this->save_system_settings();
                break;
            // Ajouter d'autres onglets...
            default:
                $this->send_error('Onglet inconnu', 400);
        }

        return ['saved_count' => $saved_count];
    }

    private function save_general_settings() {
        $settings = [
            'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'company_phone_manual' => sanitize_text_field($_POST['company_phone_manual'] ?? ''),
            'company_siret' => sanitize_text_field($_POST['company_siret'] ?? ''),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    private function save_performance_settings() {
        $settings = [
            'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
            'cache_expiry' => intval($_POST['cache_expiry'] ?? 3600),
            'compression_enabled' => isset($_POST['compression_enabled']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    private function save_system_settings() {
        $settings = [
            'cache_enabled' => $_POST['cache_enabled'] ?? '0',
            'cache_compression' => $_POST['cache_compression'] ?? '0',
            'auto_maintenance' => $_POST['systeme_auto_maintenance'] ?? '0',
            'auto_backup' => $_POST['systeme_auto_backup'] ?? '0',
        ];

        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }
}

/**
 * Handler AJAX pour les templates
 */
class PDF_Builder_Template_Ajax_Handler extends PDF_Builder_Ajax_Base {
    public function handle() {
        try {
            $this->validate_request();

            $action = $this->validate_required_param('template_action');

            switch ($action) {
                case 'save':
                    $this->handle_save_template();
                    break;
                case 'load':
                    $this->handle_load_template();
                    break;
                case 'delete':
                    $this->handle_delete_template();
                    break;
                default:
                    $this->send_error('Action template inconnue', 400);
            }

        } catch (Exception $e) {
            $this->log_error('Erreur template AJAX: ' . $e->getMessage());
            $this->send_error('Erreur interne du serveur', 500);
        }
    }

    private function handle_save_template() {
        $template_id = $this->validate_required_param('template_id', 'int');
        $template_data = $this->validate_required_param('template_data', 'json');

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->update(
            $table_templates,
            [
                'template_data' => wp_json_encode($template_data),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $template_id],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            $this->send_error('Erreur lors de la sauvegarde', 500);
        }

        $this->send_success(['template_id' => $template_id], 'Template sauvegardé avec succès');
    }

    private function handle_load_template() {
        $template_id = $this->validate_required_param('template_id', 'int');

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            $this->send_error('Template non trouvé', 404);
        }

        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->send_error('Erreur de décodage JSON', 500);
        }

        $this->send_success([
            'template' => $template_data,
            'id' => $template['id'],
            'name' => $template['name']
        ]);
    }

    private function handle_delete_template() {
        $template_id = $this->validate_required_param('template_id', 'int');

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->delete($table_templates, ['id' => $template_id], ['%d']);

        if ($result === false) {
            $this->send_error('Erreur lors de la suppression', 500);
        }

        $this->send_success([], 'Template supprimé avec succès');
    }
}

// Fonction d'initialisation des handlers AJAX
function pdf_builder_init_ajax_handlers() {
    // Settings handler - Désactivé pour éviter les conflits avec le système unifié
    // $settings_handler = new PDF_Builder_Settings_Ajax_Handler();
    // add_action('wp_ajax_pdf_builder_save_settings', [$settings_handler, 'handle']);

    // Template handler
    $template_handler = new PDF_Builder_Template_Ajax_Handler();
    add_action('wp_ajax_pdf_builder_save_template', [$template_handler, 'handle']);
    add_action('wp_ajax_pdf_builder_load_template', [$template_handler, 'handle']);
    add_action('wp_ajax_pdf_builder_delete_template', [$template_handler, 'handle']);
}

// Initialiser les handlers
add_action('init', 'pdf_builder_init_ajax_handlers');
?>