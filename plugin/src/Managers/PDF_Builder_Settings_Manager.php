<?php

namespace PDF_Builder\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}
/**
 * PDF Builder Pro - Settings Manager
 * Gestion centralisée des paramètres et configurations
 */

class PDF_Builder_Settings_Manager
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initHooks();
    }

    /**
     * Initialiser les hooks
     */
    private function initHooks()
    {
        // Hooks pour les paramètres
        add_action('admin_init', [$this, 'registerSettings']);
        
        // Hooks pour logger les mises à jour des paramètres
        add_action('update_option_pdf_builder_settings', [$this, 'logSettingsUpdate'], 10, 3);
        add_action('update_option_pdf_builder_allowed_roles', [$this, 'logSettingsUpdate'], 10, 3);
        add_action('update_option_pdf_builder_company_vat', [$this, 'logSettingsUpdate'], 10, 3);
        add_action('update_option_pdf_builder_company_rcs', [$this, 'logSettingsUpdate'], 10, 3);
        add_action('update_option_pdf_builder_company_siret', [$this, 'logSettingsUpdate'], 10, 3);
        add_action('update_option_pdf_builder_order_status_templates', [$this, 'logSettingsUpdate'], 10, 3);
        
        // Hook générique pour toutes les options pdf_builder_
        add_action('update_option', [$this, 'logAllSettingsUpdate'], 10, 3);
        
        if (class_exists('\PDF_Builder_Logger')) {
            \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Settings Manager hooks initialized');
        }
    }

    /**
     * Page de paramètres généraux
     */
    public function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
            $this->saveSettings();
            echo '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . '</p></div>';
        }

        include plugin_dir_path(dirname(__FILE__)) . '../../resources/templates/admin/settings-page.php';
    }



    /**
     * Enregistrer les paramètres
     */
    public function registerSettings()
    {
        // Paramètre principal pour les settings
        register_setting('pdf_builder_settings', 'pdf_builder_settings');

        // Paramètres généraux
        register_setting('pdf_builder_settings', 'pdf_builder_allowed_roles');
        register_setting('pdf_builder_settings', 'pdf_builder_company_vat');
        register_setting('pdf_builder_settings', 'pdf_builder_company_rcs');
        register_setting('pdf_builder_settings', 'pdf_builder_company_siret');

        // Paramètres des templates par statut de commande
        register_setting('pdf_builder_order_status_templates', 'pdf_builder_order_status_templates');
    }

    /**
     * Récupérer un paramètre
     *
     * @param string $option Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed Valeur du paramètre
     */
    public function getSetting($option, $default = false)
    {
        return get_option($option, $default);
    }

    /**
     * Sauvegarder les paramètres généraux
     */
    private function saveSettings()
    {
        
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === SAVE SETTINGS CALLED ==='); }
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] POST data: ' . json_encode($_POST)); }
        // Rôles autorisés
        $allowed_roles = isset($_POST['allowed_roles']) ? $_POST['allowed_roles'] : ['administrator'];
        pdf_builder_update_option('pdf_builder_allowed_roles', $allowed_roles);

        // Informations société (seulement les champs non disponibles dans WooCommerce/WordPress)
        $company_vat = sanitize_text_field($_POST['company_vat'] ?? '');
        $company_rcs = sanitize_text_field($_POST['company_rcs'] ?? '');
        $company_siret = sanitize_text_field($_POST['company_siret'] ?? '');

        pdf_builder_update_option('pdf_builder_company_vat', $company_vat);
        pdf_builder_update_option('pdf_builder_company_rcs', $company_rcs);
        pdf_builder_update_option('pdf_builder_company_siret', $company_siret);

        // Templates par statut de commande
        $status_templates = [];
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            foreach ($_POST['order_status_templates'] as $status => $template_id) {
                $status_templates[$status] = intval($template_id);
            }
        }
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $settings['pdf_builder_order_status_templates'] = $status_templates;
        pdf_builder_update_option('pdf_builder_settings', $settings);
    }



    /**
     * Sanitiser une valeur de paramètre
     */
    private function sanitizeSettingValue($value)
    {
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
    private function cleanJsonData($json_string)
    {
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

    /**
     * Logger toutes les mises à jour des paramètres PDF Builder
     */
    public function logAllSettingsUpdate($option, $old_value, $new_value)
    {
        if (strpos($option, 'pdf_builder_') === 0) {
            $this->addPersistentLog('[PHP] ALL OPTIONS - Option updated: ' . $option);
            
            if (isset($_POST['pdf_builder_floating_save']) && $_POST['pdf_builder_floating_save'] == '1') {
                $this->addPersistentLog('[PHP] ALL OPTIONS - FLOATING SAVE: ' . $option);
                $this->addPersistentLog('[PHP] ALL OPTIONS - POST data keys: ' . implode(', ', array_keys($_POST)));
            } else {
                $this->addPersistentLog('[PHP] ALL OPTIONS - NORMAL SAVE: ' . $option);
            }
        }
    }

    /**
     * Logger les mises à jour des paramètres, surtout si via bouton flottant
     */
    public function logSettingsUpdate($old_value, $new_value, $option)
    {
        if (class_exists('\PDF_Builder_Logger')) {
            \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Option updated: ' . $option);
            
            if (isset($_POST['pdf_builder_floating_save']) && $_POST['pdf_builder_floating_save'] == '1') {
                \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Paramètre mis à jour via bouton flottant: ' . $option);
                \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Ancienne valeur: ' . json_encode($old_value));
                \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Nouvelle valeur: ' . json_encode($new_value));
            } else {
                \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Paramètre mis à jour normalement (pas via bouton flottant): ' . $option);
            }
        }
    }

    /**
     * Ajouter un log persistant
     */
    private function addPersistentLog($message)
    {
        $existing_logs = get_option('pdf_builder_debug_logs', array());
        $existing_logs[] = $message . ' (' . date('Y-m-d H:i:s') . ')';
        // Garder seulement les 50 derniers logs
        if (count($existing_logs) > 50) {
            $existing_logs = array_slice($existing_logs, -50);
        }
        update_option('pdf_builder_debug_logs', $existing_logs);
    }
}





