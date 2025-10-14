<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Settings Manager
 * Gestion centralisée des paramètres et configurations
 */

class PDF_Builder_Settings_Manager {

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance) {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Hooks pour les paramètres
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Page de paramètres généraux
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>Paramètres sauvegardés avec succès.</p></div>';
        }

        include plugin_dir_path(dirname(__FILE__)) . '../settings-page.php';
    }

    /**
     * Page de paramètres de rendu canvas
     */
    public function canvas_render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        if (isset($_POST['save_canvas_render_settings']) && wp_verify_nonce($_POST['canvas_render_nonce'], 'pdf_builder_canvas_render')) {
            $this->save_canvas_render_settings();
            echo '<div class="notice notice-success"><p>Paramètres de rendu sauvegardés avec succès.</p></div>';
        }

        include plugin_dir_path(dirname(__FILE__)) . '../canvas-render-settings-page.php';
    }

    /**
     * Enregistrer les paramètres
     */
    public function register_settings() {
        // Paramètres généraux
        register_setting('pdf_builder_settings', 'pdf_builder_allowed_roles');
        register_setting('pdf_builder_settings', 'pdf_builder_company_name');
        register_setting('pdf_builder_settings', 'pdf_builder_company_address');
        register_setting('pdf_builder_settings', 'pdf_builder_company_phone');
        register_setting('pdf_builder_settings', 'pdf_builder_company_email');

        // Paramètres de rendu canvas
        register_setting('pdf_builder_canvas_render', 'pdf_builder_canvas_dpi');
        register_setting('pdf_builder_canvas_render', 'pdf_builder_canvas_quality');
        register_setting('pdf_builder_canvas_render', 'pdf_builder_canvas_format');

        // Paramètres des templates par statut de commande
        register_setting('pdf_builder_order_status_templates', 'pdf_builder_order_status_templates');
    }

    /**
     * Sauvegarder les paramètres généraux
     */
    private function save_settings() {
        // Rôles autorisés
        $allowed_roles = isset($_POST['allowed_roles']) ? $_POST['allowed_roles'] : ['administrator'];
        update_option('pdf_builder_allowed_roles', $allowed_roles);

        // Informations société
        $company_name = sanitize_text_field($_POST['company_name'] ?? '');
        $company_address = sanitize_textarea_field($_POST['company_address'] ?? '');
        $company_phone = sanitize_text_field($_POST['company_phone'] ?? '');
        $company_email = sanitize_email($_POST['company_email'] ?? '');

        update_option('pdf_builder_company_name', $company_name);
        update_option('pdf_builder_company_address', $company_address);
        update_option('pdf_builder_company_phone', $company_phone);
        update_option('pdf_builder_company_email', $company_email);

        // Templates par statut de commande
        $status_templates = [];
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            foreach ($_POST['order_status_templates'] as $status => $template_id) {
                $status_templates[$status] = intval($template_id);
            }
        }
        update_option('pdf_builder_order_status_templates', $status_templates);
    }

    /**
     * Sauvegarder les paramètres de rendu canvas
     */
    private function save_canvas_render_settings() {
        $dpi = isset($_POST['canvas_dpi']) ? intval($_POST['canvas_dpi']) : 300;
        $quality = isset($_POST['canvas_quality']) ? intval($_POST['canvas_quality']) : 90;
        $format = isset($_POST['canvas_format']) ? sanitize_text_field($_POST['canvas_format']) : 'png';

        // Validation des valeurs
        $dpi = max(72, min(600, $dpi)); // Entre 72 et 600 DPI
        $quality = max(1, min(100, $quality)); // Entre 1 et 100%
        $allowed_formats = ['png', 'jpg', 'jpeg', 'webp'];
        if (!in_array($format, $allowed_formats)) {
            $format = 'png';
        }

        update_option('pdf_builder_canvas_dpi', $dpi);
        update_option('pdf_builder_canvas_quality', $quality);
        update_option('pdf_builder_canvas_format', $format);
    }

    /**
     * Sanitiser une valeur de paramètre
     */
    private function sanitize_setting_value($value) {
        if (is_array($value)) {
            return array_map([$this, 'sanitize_setting_value'], $value);
        }

        if (is_string($value)) {
            return sanitize_text_field($value);
        }

        return $value;
    }

    /**
     * Nettoyer les données JSON
     */
    private function clean_json_data($json_string) {
        // Supprimer les caractères de contrôle
        $json_string = preg_replace('/[\x00-\x1F\x7F]/', '', $json_string);

        // Supprimer les espaces insécables et autres caractères spéciaux
        $json_string = str_replace("\xC2\xA0", ' ', $json_string);

        // Décoder et ré-encoder pour nettoyer
        $data = json_decode($json_string, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return wp_json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        return $json_string;
    }
}