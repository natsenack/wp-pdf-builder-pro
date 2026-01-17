<?php

/**
 * PDF Builder Pro - Gestionnaire de Paramètres
 * Gère la sauvegarde et récupération des paramètres
 */

namespace PDF_Builder\Admin\Managers;

use Exception;

/**
 * Classe responsable de la gestion des paramètres
 */
class SettingsManager
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;
        $this->registerHooks();
    }

    /**
     * Enregistrer les hooks
     */
    private function registerHooks()
    {
        // Hooks pour les paramètres - seulement l'enregistrement, pas la page
        add_action('admin_init', [$this, 'registerSettings']);

        // Charger les styles pour les pages d'administration
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminStyles']);

        // Hook pour vérifier la sauvegarde des paramètres
        add_action('update_option_pdf_builder_settings', [$this, 'onSettingsUpdated'], 10, 3);
    }

    /**
     * Enregistrer les paramètres WordPress
     */
    public function registerSettings()
    {
        // Enregistrer le tableau principal des paramètres
        register_setting('pdf_builder_settings', 'pdf_builder_settings', [$this, 'sanitizeSettings']);

        // Section principale
        add_settings_section(
            'pdf_builder_general',
            __('Paramètres Généraux', 'pdf-builder-pro'),
            [$this, 'renderGeneralSection'],
            'pdf_builder_settings'
        );

        add_settings_field(
            'company_info',
            __('Informations Entreprise', 'pdf-builder-pro'),
            [$this, 'renderCompanyInfoField'],
            'pdf_builder_settings',
            'pdf_builder_general'
        );

        // Section performance
        add_settings_section(
            'pdf_builder_performance',
            __('Performance', 'pdf-builder-pro'),
            [$this, 'renderPerformanceSection'],
            'pdf_builder_settings'
        );

        add_settings_field(
            'cache_settings',
            __('Cache', 'pdf-builder-pro'),
            [$this, 'renderCacheField'],
            'pdf_builder_settings',
            'pdf_builder_performance'
        );

        add_settings_field(
            'performance_limits',
            __('Limites de Performance', 'pdf-builder-pro'),
            [$this, 'renderPerformanceLimitsField'],
            'pdf_builder_settings',
            'pdf_builder_performance'
        );

        // Section canvas
        add_settings_section(
            'pdf_builder_canvas',
            __('Paramètres Canvas', 'pdf-builder-pro'),
            [$this, 'renderCanvasSection'],
            'pdf_builder_settings'
        );

        add_settings_field(
            'canvas_display_dimensions',
            __('🎨 Affichage & Dimensions', 'pdf-builder-pro'),
            [$this, 'renderCanvasDisplayDimensionsField'],
            'pdf_builder_settings',
            'pdf_builder_canvas'
        );
    }



    /**
     * Section générale
     */
    public function renderGeneralSection()
    {
        echo '<p>' . __('Configurez les paramètres généraux du plugin PDF Builder Pro.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Section performance
     */
    public function renderPerformanceSection()
    {
        echo '<p>' . __('Paramètres de performance et optimisation.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Section canvas
     */
    public function renderCanvasSection()
    {
        echo '<p>' . __('Paramètres par défaut du canvas d\'édition.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Champ informations entreprise
     */
    public function renderCompanyInfoField()
    {
        $settings = get_option('pdf_builder_settings', array());
        $company_name = $settings['pdf_builder_company_name'] ?? '';
        $company_address = $settings['pdf_builder_company_address'] ?? '';
        $company_phone = $settings['pdf_builder_company_phone'] ?? '';
        $company_email = $settings['pdf_builder_company_email'] ?? '';
        $default_language = $settings['pdf_builder_default_language'] ?? 'fr';

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="pdf_builder_company_name">' . __('Nom de l\'entreprise', 'pdf-builder-pro') . '</label></th>';
        echo '<td><input type="text" id="pdf_builder_company_name" name="pdf_builder_company_name" value="' . esc_attr($company_name) . '" class="regular-text"></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_company_address">' . __('Adresse', 'pdf-builder-pro') . '</label></th>';
        echo '<td><textarea id="pdf_builder_company_address" name="pdf_builder_company_address" rows="3" class="regular-text">' . esc_textarea($company_address) . '</textarea></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_company_phone">' . __('Téléphone', 'pdf-builder-pro') . '</label></th>';
        echo '<td><input type="tel" id="pdf_builder_company_phone" name="pdf_builder_company_phone" value="' . esc_attr($company_phone) . '" class="regular-text"></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_company_email">' . __('Email', 'pdf-builder-pro') . '</label></th>';
        echo '<td><input type="email" id="pdf_builder_company_email" name="pdf_builder_company_email" value="' . esc_attr($company_email) . '" class="regular-text"></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_default_language">' . __('Langue par défaut', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<select id="pdf_builder_default_language" name="pdf_builder_default_language">';
        echo '<option value="fr" ' . selected($default_language, 'fr', false) . '>Français</option>';
        echo '<option value="en" ' . selected($default_language, 'en', false) . '>English</option>';
        echo '<option value="es" ' . selected($default_language, 'es', false) . '>Español</option>';
        echo '<option value="de" ' . selected($default_language, 'de', false) . '>Deutsch</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Champ cache
     */
    public function renderCacheField()
    {
        $settings = get_option('pdf_builder_settings', array());
        $enable_cache = $settings['pdf_builder_cache_enabled'] ?? '1';
        $cache_timeout = $settings['pdf_builder_cache_ttl'] ?? 3600;

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="pdf_builder_enable_cache">' . __('Activer le cache', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="checkbox" id="pdf_builder_enable_cache" name="pdf_builder_enable_cache" value="1" ' . checked($enable_cache, '1', false) . '>';
        echo '<p class="description">' . __('Améliore les performances en cachant les résultats des requêtes.', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_cache_timeout">' . __('Timeout du cache (secondes)', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_cache_timeout" name="pdf_builder_cache_timeout" value="' . esc_attr($cache_timeout) . '" min="60" max="86400" step="60">';
        echo '<p class="description">' . __('Durée avant expiration du cache (3600 = 1 heure).', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Champ limites de performance
     */
    public function renderPerformanceLimitsField()
    {
        $settings = get_option('pdf_builder_settings', array());
        $compression_level = $settings['pdf_builder_compression_level'] ?? 6;
        $memory_limit = $settings['pdf_builder_memory_limit'] ?? 256;
        $max_execution_time = $settings['pdf_builder_max_execution_time'] ?? 30;

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="pdf_builder_compression_level">' . __('Niveau de compression', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_compression_level" name="pdf_builder_compression_level" value="' . esc_attr($compression_level) . '" min="0" max="9">';
        echo '<p class="description">' . __('Niveau de compression des images (0-9, 6 recommandé).', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_memory_limit">' . __('Limite mémoire (MB)', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_memory_limit" name="pdf_builder_memory_limit" value="' . esc_attr($memory_limit) . '" min="64" max="1024" step="64">';
        echo '<p class="description">' . __('Mémoire maximale allouée pour la génération PDF.', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_max_execution_time">' . __('Temps d\'exécution max (secondes)', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_max_execution_time" name="pdf_builder_max_execution_time" value="' . esc_attr($max_execution_time) . '" min="10" max="300" step="5">';
        echo '<p class="description">' . __('Temps maximum pour générer un PDF.', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Sauvegarder les paramètres généraux (AJAX)
     */
    public function ajaxSaveGeneralSettings()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Paramètres généraux
            $settings = [
                'pdf_builder_company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
                'pdf_builder_company_address' => sanitize_textarea_field($_POST['company_address'] ?? ''),
                'pdf_builder_company_phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
                'pdf_builder_company_email' => sanitize_email($_POST['company_email'] ?? ''),
                'pdf_builder_default_language' => sanitize_text_field($_POST['default_language'] ?? 'fr'),
            ];

            foreach ($settings as $key => $value) {
                update_option($key, $value);
            }

            wp_send_json_success([
                'message' => 'Paramètres généraux sauvegardés'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les paramètres de performance (AJAX)
     */
    public function ajaxSavePerformanceSettings()
    {
        try {
            // Vérifier les permissions
            if (!is_user_logged_in() || !current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                wp_send_json_error('Nonce invalide');
                return;
            }

            // Paramètres de performance
            $settings = [
                'pdf_builder_enable_cache' => isset($_POST['enable_cache']) ? '1' : '0',
                'pdf_builder_cache_timeout' => intval($_POST['cache_timeout'] ?? 3600),
                'pdf_builder_compression_level' => intval($_POST['compression_level'] ?? 6),
                'pdf_builder_memory_limit' => intval($_POST['memory_limit'] ?? 256),
                'pdf_builder_max_execution_time' => intval($_POST['max_execution_time'] ?? 30),
            ];

            foreach ($settings as $key => $value) {
                update_option($key, $value);
            }

            wp_send_json_success([
                'message' => 'Paramètres de performance sauvegardés'
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir tous les paramètres
     */
    public function getAllSettings()
    {
        return [
            'general' => $this->getGeneralSettings(),
            'performance' => $this->getPerformanceSettings(),
            'canvas' => $this->getCanvasSettings(),
        ];
    }

    /**
     * Champ Affichage & Dimensions du Canvas
     */
    public function renderCanvasDisplayDimensionsField()
    {
        $settings = get_option('pdf_builder_settings', array());
        $allow_portrait = $settings['pdf_builder_canvas_allow_portrait'] ?? '1';
        $allow_landscape = $settings['pdf_builder_canvas_allow_landscape'] ?? '1';
        $default_orientation = $settings['pdf_builder_canvas_default_orientation'] ?? 'portrait';

        echo '<fieldset>';
        echo '<legend style="font-weight: 600; margin-bottom: 10px;">' . __('Autorisations d\'orientation du canvas', 'pdf-builder-pro') . '</legend>';
        
        echo '<label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_allow_portrait" value="1" ' . checked($allow_portrait, '1', false) . '>';
        echo __('Autoriser l\'orientation Portrait (794×1123 px)', 'pdf-builder-pro');
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_allow_landscape" value="1" ' . checked($allow_landscape, '1', false) . '>';
        echo __('Autoriser l\'orientation Paysage (1123×794 px)', 'pdf-builder-pro');
        echo '</label>';

        echo '<div style="margin-top: 12px; padding: 10px; background-color: #f0f0f0; border-left: 3px solid #0073aa; border-radius: 3px;">';
        echo '<label style="display: block; margin-bottom: 8px;">';
        echo '<strong>' . __('Orientation par défaut', 'pdf-builder-pro') . '</strong>';
        echo '</label>';
        echo '<select name="pdf_builder_canvas_default_orientation" style="padding: 6px 8px;">';
        echo '<option value="portrait" ' . selected($default_orientation, 'portrait', false) . '>' . __('Portrait', 'pdf-builder-pro') . '</option>';
        echo '<option value="landscape" ' . selected($default_orientation, 'landscape', false) . '>' . __('Paysage', 'pdf-builder-pro') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Cette orientation sera appliquée par défaut lors de la création d\'un nouveau template.', 'pdf-builder-pro') . '</p>';
        echo '</div>';

        echo '</fieldset>';
    }

    /**
     * Obtenir les paramètres généraux
     */
    private function getGeneralSettings()
    {
        $settings = get_option('pdf_builder_settings', array());
        return [
            'company_name' => $settings['pdf_builder_company_name'] ?? '',
            'company_address' => $settings['pdf_builder_company_address'] ?? '',
            'company_phone' => $settings['pdf_builder_company_phone'] ?? '',
            'company_email' => $settings['pdf_builder_company_email'] ?? '',
            'company_phone_manual' => $settings['pdf_builder_company_phone_manual'] ?? '',
            'company_siret' => $settings['pdf_builder_company_siret'] ?? '',
            'company_vat' => $settings['pdf_builder_company_vat'] ?? '',
            'company_rcs' => $settings['pdf_builder_company_rcs'] ?? '',
            'company_capital' => $settings['pdf_builder_company_capital'] ?? '',
            'default_language' => $settings['pdf_builder_default_language'] ?? 'fr',
        ];
    }

    /**
     * Obtenir les paramètres de performance
     */
    private function getPerformanceSettings()
    {
        $settings = get_option('pdf_builder_settings', array());
        return [
            'enable_cache' => ($settings['pdf_builder_cache_enabled'] ?? '0') === '1',
            'cache_timeout' => intval($settings['pdf_builder_cache_ttl'] ?? 3600),
            'compression_level' => intval($settings['pdf_builder_compression_level'] ?? 6),
            'memory_limit' => intval($settings['pdf_builder_memory_limit'] ?? 256),
            'max_execution_time' => intval($settings['pdf_builder_max_execution_time'] ?? 30),
        ];
    }

    /**
     * Obtenir les paramètres canvas
     */
    private function getCanvasSettings()
    {
        $settings = get_option('pdf_builder_settings', array());
        return [
            'format' => $settings['pdf_builder_canvas_canvas_format'] ?? 'A4',
            'orientation' => $settings['pdf_builder_canvas_canvas_orientation'] ?? 'portrait',
            'dpi' => intval($settings['pdf_builder_canvas_canvas_dpi'] ?? 96),
            'width' => intval($settings['pdf_builder_canvas_canvas_width'] ?? 794),
            'height' => intval($settings['pdf_builder_canvas_canvas_height'] ?? 1123),
            'drag_enabled' => ($settings['pdf_builder_canvas_canvas_drag_enabled'] ?? '1') === '1',
            'resize_enabled' => ($settings['pdf_builder_canvas_canvas_resize_enabled'] ?? '1') === '1',
            'rotate_enabled' => ($settings['pdf_builder_canvas_canvas_rotate_enabled'] ?? '1') === '1',
            'multi_select' => ($settings['pdf_builder_canvas_canvas_multi_select'] ?? '1') === '1',
            'selection_mode' => $settings['pdf_builder_canvas_canvas_selection_mode'] ?? 'click',
            'keyboard_shortcuts' => ($settings['pdf_builder_canvas_canvas_keyboard_shortcuts'] ?? '1') === '1',
            'grid_enabled' => ($settings['pdf_builder_canvas_canvas_grid_enabled'] ?? '1') === '1',
            'grid_size' => intval($settings['pdf_builder_canvas_canvas_grid_size'] ?? 20),
            'snap_to_grid' => ($settings['pdf_builder_canvas_canvas_snap_to_grid'] ?? '1') === '1',
            'navigation_enabled' => ($settings['pdf_builder_canvas_canvas_navigation_enabled'] ?? '1') === '1',
            'zoom_default' => intval($settings['pdf_builder_canvas_canvas_zoom_default'] ?? 100),
            'export_format' => $settings['pdf_builder_canvas_canvas_export_format'] ?? 'png',
            'export_quality' => intval($settings['pdf_builder_canvas_canvas_export_quality'] ?? 90),
        ];
    }

    /**
     * Fonction de sanitisation pour les paramètres
     */
    public function sanitizeSettings($input)
    {
        error_log('[PDF Builder] === SANITIZE SETTINGS START ===');
        error_log('[PDF Builder] sanitizeSettings called with input count: ' . (is_array($input) ? count($input) : 'not array'));
        error_log('[PDF Builder] Raw input data: ' . print_r($input, true));

        // Vérifier si l'input est vide
        if (empty($input)) {
            error_log('[PDF Builder] ERROR: sanitizeSettings called with empty input!');
            return array();
        }

        $sanitized = array();

        // Récupérer les valeurs existantes pour les fusionner
        $existing = get_option('pdf_builder_settings', array());
        error_log('[PDF Builder] Existing settings count: ' . count($existing));
        error_log('[PDF Builder] Existing settings keys: ' . implode(', ', array_keys($existing)));

        // Commencer avec les valeurs existantes
        $sanitized = $existing;

        // CORRECTION: Suppression de la logique des checkboxes non présentes dans l'input

        // LOGS SPECIFIQUES POUR LES TEMPLATES
        error_log('[PDF Builder] === CHECKING TEMPLATE FIELDS ===');
        $template_fields = ['pdf_builder_default_template', 'pdf_builder_template_library_enabled'];
        foreach ($template_fields as $field) {
            if (isset($input[$field])) {
                error_log("[PDF Builder] Template field '$field' found with value: '" . $input[$field] . "'");
            } else {
                error_log("[PDF Builder] Template field '$field' NOT found in input");
            }
        }

        // LOGS SPECIFIQUES POUR LES COULEURS CANVAS
        error_log('[PDF Builder] === CHECKING CANVAS COLOR FIELDS ===');
        $canvas_color_fields = [
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_container_bg_color'
        ];
        foreach ($canvas_color_fields as $field) {
            if (isset($input[$field])) {
                error_log("[PDF Builder] Canvas color field '$field' found with value: '" . $input[$field] . "'");
            } else {
                error_log("[PDF Builder] Canvas color field '$field' NOT found in input");
            }
        }

        // TRAITEMENT SPECIFIQUE DES CHAMPS TEMPLATE
        if (isset($input['pdf_builder_default_template'])) {
            $allowed_templates = ['blank', 'invoice', 'quote'];
            $sanitized['pdf_builder_default_template'] = in_array($input['pdf_builder_default_template'], $allowed_templates) ? $input['pdf_builder_default_template'] : 'blank';
        }
        if (isset($input['pdf_builder_template_library_enabled'])) {
            $sanitized['pdf_builder_template_library_enabled'] = $input['pdf_builder_template_library_enabled'] ? '1' : '0';
        }

        // Sanitisation des paramètres généraux
        if (isset($input['pdf_builder_company_phone_manual'])) {
            $sanitized['pdf_builder_company_phone_manual'] = sanitize_text_field($input['pdf_builder_company_phone_manual']);
        }
        if (isset($input['pdf_builder_company_siret'])) {
            $sanitized['pdf_builder_company_siret'] = sanitize_text_field($input['pdf_builder_company_siret']);
        }
        if (isset($input['pdf_builder_company_vat'])) {
            $sanitized['pdf_builder_company_vat'] = sanitize_text_field($input['pdf_builder_company_vat']);
        }
        if (isset($input['pdf_builder_company_rcs'])) {
            $sanitized['pdf_builder_company_rcs'] = sanitize_text_field($input['pdf_builder_company_rcs']);
        }
        if (isset($input['pdf_builder_company_capital'])) {
            $sanitized['pdf_builder_company_capital'] = sanitize_text_field($input['pdf_builder_company_capital']);
        }

        // Sanitisation des paramètres système/cache
        if (isset($input['pdf_builder_cache_enabled'])) {
            $sanitized['pdf_builder_cache_enabled'] = $input['pdf_builder_cache_enabled'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_cache_compression'])) {
            $sanitized['pdf_builder_cache_compression'] = $input['pdf_builder_cache_compression'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_cache_auto_cleanup'])) {
            $sanitized['pdf_builder_cache_auto_cleanup'] = $input['pdf_builder_cache_auto_cleanup'] ? '1' : '0';
        }

        // Sanitisation des paramètres de sécurité
        if (isset($input['pdf_builder_security_level'])) {
            $allowed_levels = array('low', 'medium', 'high');
            $sanitized['pdf_builder_security_level'] = in_array($input['pdf_builder_security_level'], $allowed_levels) ? $input['pdf_builder_security_level'] : 'medium';
        }
        if (isset($input['pdf_builder_enable_logging'])) {
            $sanitized['pdf_builder_enable_logging'] = $input['pdf_builder_enable_logging'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_gdpr_enabled'])) {
            $sanitized['pdf_builder_gdpr_enabled'] = $input['pdf_builder_gdpr_enabled'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_gdpr_consent_required'])) {
            $sanitized['pdf_builder_gdpr_consent_required'] = $input['pdf_builder_gdpr_consent_required'] ? '1' : '0';
        }

        // Sanitisation des paramètres développeur
        if (isset($input['pdf_builder_developer_enabled'])) {
            $sanitized['pdf_builder_developer_enabled'] = $input['pdf_builder_developer_enabled'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_developer_password'])) {
            $sanitized['pdf_builder_developer_password'] = sanitize_text_field($input['pdf_builder_developer_password']);
        }
        if (isset($input['pdf_builder_license_test_mode'])) {
            $sanitized['pdf_builder_license_test_mode'] = $input['pdf_builder_license_test_mode'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_debug_php_errors'])) {
            $sanitized['pdf_builder_debug_php_errors'] = $input['pdf_builder_debug_php_errors'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_debug_javascript'])) {
            $sanitized['pdf_builder_debug_javascript'] = $input['pdf_builder_debug_javascript'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_debug_javascript_verbose'])) {
            $sanitized['pdf_builder_debug_javascript_verbose'] = $input['pdf_builder_debug_javascript_verbose'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_debug_ajax'])) {
            $sanitized['pdf_builder_debug_ajax'] = $input['pdf_builder_debug_ajax'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_debug_performance'])) {
            $sanitized['pdf_builder_debug_performance'] = $input['pdf_builder_debug_performance'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_debug_database'])) {
            $sanitized['pdf_builder_debug_database'] = $input['pdf_builder_debug_database'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_log_level'])) {
            $allowed_levels = array('error', 'warning', 'info', 'debug');
            $sanitized['pdf_builder_log_level'] = in_array($input['pdf_builder_log_level'], $allowed_levels) ? $input['pdf_builder_log_level'] : 'warning';
        }
        if (isset($input['pdf_builder_log_file_size'])) {
            $sanitized['pdf_builder_log_file_size'] = intval($input['pdf_builder_log_file_size']);
            $sanitized['pdf_builder_log_file_size'] = max(1, min(100, $sanitized['pdf_builder_log_file_size']));
        }
        if (isset($input['pdf_builder_log_retention'])) {
            $sanitized['pdf_builder_log_retention'] = intval($input['pdf_builder_log_retention']);
            $sanitized['pdf_builder_log_retention'] = max(1, min(365, $sanitized['pdf_builder_log_retention']));
        }
        if (isset($input['pdf_builder_force_https'])) {
            $sanitized['pdf_builder_force_https'] = $input['pdf_builder_force_https'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_performance_monitoring'])) {
            $sanitized['pdf_builder_performance_monitoring'] = $input['pdf_builder_performance_monitoring'] ? '1' : '0';
        }

        // Sanitisation des paramètres de licence
        if (isset($input['pdf_builder_license_email_reminders'])) {
            $sanitized['pdf_builder_license_email_reminders'] = $input['pdf_builder_license_email_reminders'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_license_reminder_email'])) {
            $sanitized['pdf_builder_license_reminder_email'] = sanitize_email($input['pdf_builder_license_reminder_email']);
        }

        // Sanitisation des paramètres PDF
        if (isset($input['pdf_builder_pdf_quality'])) {
            $allowed_qualities = ['low', 'medium', 'high'];
            $sanitized['pdf_builder_pdf_quality'] = in_array($input['pdf_builder_pdf_quality'], $allowed_qualities) ? $input['pdf_builder_pdf_quality'] : 'high';
        }
        if (isset($input['pdf_builder_default_format'])) {
            $allowed_formats = ['A4', 'A3', 'Letter'];
            $sanitized['pdf_builder_default_format'] = in_array($input['pdf_builder_default_format'], $allowed_formats) ? $input['pdf_builder_default_format'] : 'A4';
        }
        if (isset($input['pdf_builder_default_orientation'])) {
            $allowed_orientations = ['portrait', 'landscape'];
            $sanitized['pdf_builder_default_orientation'] = in_array($input['pdf_builder_default_orientation'], $allowed_orientations) ? $input['pdf_builder_default_orientation'] : 'portrait';
        }
        if (isset($input['pdf_builder_pdf_cache_enabled'])) {
            $sanitized['pdf_builder_pdf_cache_enabled'] = $input['pdf_builder_pdf_cache_enabled'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_pdf_compression'])) {
            $allowed_compressions = ['none', 'medium', 'high'];
            $sanitized['pdf_builder_pdf_compression'] = in_array($input['pdf_builder_pdf_compression'], $allowed_compressions) ? $input['pdf_builder_pdf_compression'] : 'medium';
        }
        if (isset($input['pdf_builder_pdf_metadata_enabled'])) {
            $sanitized['pdf_builder_pdf_metadata_enabled'] = $input['pdf_builder_pdf_metadata_enabled'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_pdf_print_optimized'])) {
            $sanitized['pdf_builder_pdf_print_optimized'] = $input['pdf_builder_pdf_print_optimized'] ? '1' : '0';
        }

        // Sanitisation des paramètres canvas
        $canvas_fields = [
            'pdf_builder_canvas_width' => 'intval',
            'pdf_builder_canvas_height' => 'intval',
            'pdf_builder_canvas_dpi' => 'intval',
            'pdf_builder_canvas_format' => 'sanitize_text_field',
            'pdf_builder_canvas_bg_color' => 'sanitize_hex_color',
            'pdf_builder_canvas_border_color' => 'sanitize_hex_color',
            'pdf_builder_canvas_border_width' => 'intval',
            'pdf_builder_canvas_shadow_enabled' => 'bool',
            'pdf_builder_canvas_container_bg_color' => 'sanitize_hex_color',
            'pdf_builder_canvas_grid_enabled' => 'bool',
            'pdf_builder_canvas_grid_size' => 'intval',
            'pdf_builder_canvas_guides_enabled' => 'bool',
            'pdf_builder_canvas_snap_to_grid' => 'bool',
            'pdf_builder_canvas_zoom_min' => 'intval',
            'pdf_builder_canvas_zoom_max' => 'intval',
            'pdf_builder_canvas_zoom_default' => 'intval',
            'pdf_builder_canvas_zoom_step' => 'intval',
            'pdf_builder_canvas_export_quality' => 'intval',
            'pdf_builder_canvas_export_format' => 'sanitize_text_field',
            'pdf_builder_canvas_export_transparent' => 'bool',
            'pdf_builder_canvas_drag_enabled' => 'bool',
            'pdf_builder_canvas_resize_enabled' => 'bool',
            'pdf_builder_canvas_rotate_enabled' => 'bool',
            'pdf_builder_canvas_multi_select' => 'bool',
            'pdf_builder_canvas_selection_mode' => 'sanitize_text_field',
            'pdf_builder_canvas_keyboard_shortcuts' => 'bool',
            'pdf_builder_canvas_fps_target' => 'intval',
            'pdf_builder_canvas_memory_limit_js' => 'intval',
            'pdf_builder_canvas_response_timeout' => 'intval',
            'pdf_builder_canvas_lazy_loading_editor' => 'bool',
            'pdf_builder_canvas_preload_critical' => 'bool',
            'pdf_builder_canvas_lazy_loading_plugin' => 'bool',
            'pdf_builder_canvas_debug_enabled' => 'bool',
            'pdf_builder_canvas_performance_monitoring' => 'bool',
            'pdf_builder_canvas_error_reporting' => 'bool',
            'pdf_builder_canvas_memory_limit_php' => 'intval',
            'pdf_builder_canvas_allow_portrait' => 'bool',
            'pdf_builder_canvas_allow_landscape' => 'bool',
        ];

        foreach ($canvas_fields as $field => $type) {
            if (isset($input[$field])) {
                switch ($type) {
                    case 'intval':
                        $sanitized[$field] = intval($input[$field]);
                        break;
                    case 'bool':
                        $sanitized[$field] = $input[$field] ? '1' : '0';
                        break;
                    case 'sanitize_hex_color':
                        $sanitized[$field] = sanitize_hex_color($input[$field]) ?: $input[$field];
                        break;
                    default:
                        $sanitized[$field] = sanitize_text_field($input[$field]);
                        break;
                }
            }
        }

        // Sanitisation du paramètre des templates par statut
        if (isset($input['pdf_builder_order_status_templates']) && is_array($input['pdf_builder_order_status_templates'])) {
            $sanitized['pdf_builder_order_status_templates'] = array_map('sanitize_text_field', $input['pdf_builder_order_status_templates']);
        }

        // Pour l'instant, on accepte les autres valeurs telles quelles mais sanitizées
        foreach ($input as $key => $value) {
            if (!isset($sanitized[$key])) {
                if (is_array($value)) {
                    $sanitized[$key] = array_map('sanitize_text_field', $value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }

        error_log('[PDF Builder] === FINAL TEMPLATE VALUES ===');
        foreach ($template_fields as $field) {
            $final_value = isset($sanitized[$field]) ? $sanitized[$field] : 'NOT_SET';
            error_log("[PDF Builder] Final '$field' = '$final_value'");
        }

        error_log('[PDF Builder] === FINAL CANVAS COLOR VALUES ===');
        foreach ($canvas_color_fields as $field) {
            $final_value = isset($sanitized[$field]) ? $sanitized[$field] : 'NOT_SET';
            error_log("[PDF Builder] Final '$field' = '$final_value'");
        }

        error_log('[PDF Builder] sanitizeSettings returning sanitized data: ' . print_r($sanitized, true));
        error_log('[PDF Builder] Final sanitized array count: ' . count($sanitized));
        return $sanitized;
    }

    /**
     * Hook appelé quand les paramètres sont mis à jour
     */
    public function onSettingsUpdated($old_value, $new_value, $option)
    {
        error_log('[PDF Builder] ✅ SUCCESS: onSettingsUpdated called for option: ' . $option);
        error_log('[PDF Builder] ✅ Old value count: ' . (is_array($old_value) ? count($old_value) : 'not array'));
        error_log('[PDF Builder] ✅ New value count: ' . (is_array($new_value) ? count($new_value) : 'not array'));

        // Logs spécifiques pour les champs problématiques
        $check_fields = [
            'pdf_builder_default_template',
            'pdf_builder_template_library_enabled',
            'pdf_builder_canvas_canvas_bg_color',
            'pdf_builder_canvas_canvas_border_color',
            'pdf_builder_canvas_canvas_container_bg_color'
        ];

        error_log('[PDF Builder] === SETTINGS UPDATE DETAILS ===');
        foreach ($check_fields as $field) {
            $old_val = isset($old_value[$field]) ? $old_value[$field] : 'NOT_SET';
            $new_val = isset($new_value[$field]) ? $new_value[$field] : 'NOT_SET';
            $changed = ($old_val !== $new_val) ? 'CHANGED' : 'UNCHANGED';
            error_log("[PDF Builder] $field: '$old_val' -> '$new_val' [$changed]");
        }

        error_log('[PDF Builder] ✅ New value sample: ' . print_r(array_slice($new_value, 0, 5), true));
    }

    /**
     * Charger les styles d'administration pour les paramètres
     */
    public function enqueueAdminStyles($hook)
    {
        // Charger les styles seulement sur les pages d'administration du plugin
        if (strpos($hook, 'pdf-builder') !== false || strpos($hook, 'settings_page_pdf_builder') !== false) {
            $css_file = plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/css/settings.css';
            $css_path = plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/css/settings.css';

            // Vérifier que le fichier existe avant de le charger
            if (file_exists($css_path)) {
                wp_enqueue_style(
                    'pdf-builder-settings',
                    $css_file,
                    array(),
                    filemtime($css_path),
                    'all'
                );
            }
        }
    }
}
