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
        // Tous les hooks de paramètres ont été déplacés vers settings-main.php
    }

    /**
     * Page de paramètres généraux
     */
    public function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        if ((isset($_POST['save_settings']) || isset($_POST['pdf_builder_floating_save'])) && wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
            $this->saveSettings();
            echo '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . '</p></div>';
        }

        include plugin_dir_path(dirname(__FILE__)) . '../../resources/templates/admin/settings-page.php';
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
        error_log('[PDF Builder] === SAVE SETTINGS CALLED ===');
        error_log('[PDF Builder] POST data: ' . json_encode($_POST));
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
     * Logger les mises à jour des paramètres, surtout si via bouton flottant
     */
    public function logSettingsUpdate($old_value, $new_value, $option)
    {
        error_log('[PDF Builder] Option updated: ' . $option);

        if (isset($_POST['pdf_builder_floating_save']) && $_POST['pdf_builder_floating_save'] == '1') {
            error_log('[PDF Builder] Paramètre mis à jour via bouton flottant: ' . $option);
            error_log('[PDF Builder] Ancienne valeur: ' . json_encode($old_value));
            error_log('[PDF Builder] Nouvelle valeur: ' . json_encode($new_value));
        } else {
            error_log('[PDF Builder] Paramètre mis à jour normalement (pas via bouton flottant): ' . $option);
        }
    }

}






