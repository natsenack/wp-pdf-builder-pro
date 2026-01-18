<?php
/**
 * PDF Builder Pro - Gestionnaire centralisé des nonces
 * Système unifié pour la validation et le refresh automatique des nonces
 */

class PDF_Builder_Nonce_Manager {

    private static $instance = null;
    private $nonce_action = 'pdf_builder_ajax';
    private $nonce_ttl = 20 * 60 * 1000; // 20 minutes
    private $refresh_threshold = 5 * 60 * 1000; // 5 minutes avant expiration
    private $max_retries = 2;
    private $nonce_mappings = [
        'save_settings' => 'pdf_builder_save_settings_nonce',
        'pdf_builder_canvas_settings' => 'pdf_builder_canvas_settings',
        'get_cache_metrics' => 'pdf_builder_ajax',
        'test_cache_integration' => 'pdf_builder_ajax',
        'clear_cache' => 'pdf_builder_ajax',
        'optimize_database' => 'pdf_builder_ajax',
        'remove_temp_files' => 'pdf_builder_ajax',
        'repair_templates' => 'pdf_builder_ajax',
        'toggle_auto_maintenance' => 'pdf_builder_ajax',
        'schedule_maintenance' => 'pdf_builder_ajax',
        'test_license' => 'pdf_builder_ajax',
        'export_diagnostic' => 'pdf_builder_ajax',
        'view_logs' => 'pdf_builder_ajax',
        'test_ajax' => 'pdf_builder_ajax',
    ];

    /**
     * Singleton pattern
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialise les hooks
     */
    private function init_hooks() {
        add_action('wp_ajax_pdf_builder_get_fresh_nonce', [$this, 'ajax_get_fresh_nonce']);
        add_action('wp_ajax_nopriv_pdf_builder_get_fresh_nonce', [$this, 'ajax_get_fresh_nonce']);
    }

    /**
     * Génère un nouveau nonce
     */
    public function generate_nonce() {
        return wp_create_nonce($this->nonce_action);
    }

    /**
     * Valide un nonce avec gestion d'erreurs détaillée
     */
    public function validate_nonce($nonce, $context = '') {
        if (empty($nonce)) {
            $this->log_error('Nonce manquant', $context);
            return false;
        }

        $action = isset($this->nonce_mappings[$context]) ? $this->nonce_mappings[$context] : $this->nonce_action;

        // Debug: Log the nonce validation attempt
        // // error_log('[PDF Builder Nonce DEBUG] Validating nonce: ' . substr($nonce, 0, 10) . '... for action: ' . $action . ' in context: ' . $context);

        $is_valid = wp_verify_nonce($nonce, $action);

        // // error_log('[PDF Builder Nonce DEBUG] Validation result: ' . ($is_valid ? 'VALID' : 'INVALID'));

        if (!$is_valid) {
            $this->log_error('Nonce invalide', $context, [
                'provided_nonce' => substr($nonce, 0, 10) . '...',
                'expected_action' => $this->nonce_action
            ]);
        }

        return $is_valid;
    }

    /**
     * Valide une requête AJAX complète
     */
    public function validate_ajax_request($context = '') {
        // Vérifier les permissions de base
        if (!current_user_can('manage_options')) {
            $this->log_error('Permissions insuffisantes', $context);
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return false;
        }

        // Vérifier le nonce
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!$this->validate_nonce($nonce, $context)) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return false;
        }

        return true;
    }

    /**
     * Handler AJAX pour obtenir un nonce frais
     */
    public function ajax_get_fresh_nonce() {
        try {
            // Validation légère pour cette action
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $fresh_nonce = $this->generate_nonce();

            $this->log_info('Nonce frais généré', [
                'nonce_prefix' => substr($fresh_nonce, 0, 10) . '...',
                'user_id' => get_current_user_id(),
                'timestamp' => current_time('timestamp')
            ]);

            wp_send_json_success([
                'nonce' => $fresh_nonce,
                'generated_at' => current_time('timestamp'),
                'expires_in' => $this->nonce_ttl / 1000 // en secondes
            ]);

        } catch (Exception $e) {
            $this->log_error('Erreur génération nonce frais: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Erreur interne']);
        }
    }

    /**
     * Log une erreur
     */
    private function log_error($message, $context = '', $extra = []) {
        $log_data = [
            'message' => $message,
            'context' => $context,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];

        if (!empty($extra)) {
            $log_data['extra'] = $extra;
        }

        // // error_log('[PDF Builder Nonce] ERROR: ' . json_encode($log_data));
    }

    /**
     * Log une information
     */
    private function log_info($message, $extra = []) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        $log_data = [
            'message' => $message,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id()
        ];

        if (!empty($extra)) {
            $log_data['extra'] = $extra;
        }

        // // error_log('[PDF Builder Nonce] INFO: ' . json_encode($log_data));
    }

    /**
     * Vérifie si un nonce est proche de l'expiration
     */
    public function is_nonce_expiring_soon($nonce) {
        // Les nonces WordPress n'ont pas de timestamp direct
        // On considère qu'ils sont "proches de l'expiration" après un certain temps
        // Cette méthode pourrait être étendue avec un système de cache
        return false; // Pour l'instant, on laisse le JS gérer ça
    }
}

