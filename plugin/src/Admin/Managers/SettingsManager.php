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
    }

    /**
     * Enregistrer les paramètres WordPress
     */
    public function registerSettings()
    {
        // Section principale
        add_settings_section(
            'pdf_builder_general',
            __('Paramètres Généraux', 'pdf-builder-pro'),
            [$this, 'renderGeneralSection'],
            'pdf_builder_settings'
        );

        // Paramètres généraux
        register_setting('pdf_builder_settings', 'pdf_builder_company_name');
        register_setting('pdf_builder_settings', 'pdf_builder_company_address');
        register_setting('pdf_builder_settings', 'pdf_builder_company_phone');
        register_setting('pdf_builder_settings', 'pdf_builder_company_email');
        register_setting('pdf_builder_settings', 'pdf_builder_default_language');

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

        register_setting('pdf_builder_settings', 'pdf_builder_enable_cache');
        register_setting('pdf_builder_settings', 'pdf_builder_cache_timeout');
        register_setting('pdf_builder_settings', 'pdf_builder_compression_level');
        register_setting('pdf_builder_settings', 'pdf_builder_memory_limit');
        register_setting('pdf_builder_settings', 'pdf_builder_max_execution_time');

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

        // Enregistrer tous les paramètres canvas
        $canvas_settings = [
            'pdf_builder_canvas_format',
            'pdf_builder_canvas_orientation',
            'pdf_builder_canvas_dpi',
            'pdf_builder_canvas_width',
            'pdf_builder_canvas_height',
            'pdf_builder_canvas_drag_enabled',
            'pdf_builder_canvas_resize_enabled',
            'pdf_builder_canvas_rotate_enabled',
            'pdf_builder_canvas_multi_select',
            'pdf_builder_canvas_selection_mode',
            'pdf_builder_canvas_keyboard_shortcuts',
            'pdf_builder_canvas_grid_enabled',
            'pdf_builder_canvas_grid_size',
            'pdf_builder_canvas_snap_to_grid',
            'pdf_builder_canvas_navigation_enabled',
            'pdf_builder_canvas_zoom_default',
            'pdf_builder_canvas_export_format',
            'pdf_builder_canvas_export_quality',
            'pdf_builder_canvas_autosave_enabled',
            'pdf_builder_canvas_auto_save_interval',
        ];

        foreach ($canvas_settings as $setting) {
            register_setting('pdf_builder_settings', $setting);
        }
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
        $company_name = get_option('pdf_builder_company_name', '');
        $company_address = get_option('pdf_builder_company_address', '');
        $company_phone = get_option('pdf_builder_company_phone', '');
        $company_email = get_option('pdf_builder_company_email', '');
        $default_language = get_option('pdf_builder_default_language', 'fr');

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
        $enable_cache = get_option('pdf_builder_enable_cache', '1');
        $cache_timeout = get_option('pdf_builder_cache_timeout', 3600);

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
        $compression_level = get_option('pdf_builder_compression_level', 6);
        $memory_limit = get_option('pdf_builder_memory_limit', 256);
        $max_execution_time = get_option('pdf_builder_max_execution_time', 30);

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
            wp_send_json_error('Erreur: ' . $e->get_message());
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
            wp_send_json_error('Erreur: ' . $e->get_message());
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
     * Obtenir les paramètres généraux
     */
    private function getGeneralSettings()
    {
        return [
            'company_name' => get_option('pdf_builder_company_name', ''),
            'company_address' => get_option('pdf_builder_company_address', ''),
            'company_phone' => get_option('pdf_builder_company_phone', ''),
            'company_email' => get_option('pdf_builder_company_email', ''),
            'default_language' => get_option('pdf_builder_default_language', 'fr'),
        ];
    }

    /**
     * Obtenir les paramètres de performance
     */
    private function getPerformanceSettings()
    {
        return [
            'enable_cache' => get_option('pdf_builder_enable_cache', '1') === '1',
            'cache_timeout' => intval(get_option('pdf_builder_cache_timeout', 3600)),
            'compression_level' => intval(get_option('pdf_builder_compression_level', 6)),
            'memory_limit' => intval(get_option('pdf_builder_memory_limit', 256)),
            'max_execution_time' => intval(get_option('pdf_builder_max_execution_time', 30)),
        ];
    }

    /**
     * Obtenir les paramètres canvas
     */
    private function getCanvasSettings()
    {
        return [
            'format' => get_option('pdf_builder_canvas_format', 'A4'),
            'orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
            'dpi' => intval(get_option('pdf_builder_canvas_dpi', 96)),
            'width' => intval(get_option('pdf_builder_canvas_width', 794)),
            'height' => intval(get_option('pdf_builder_canvas_height', 1123)),
            'drag_enabled' => get_option('pdf_builder_canvas_drag_enabled', '1') === '1',
            'resize_enabled' => get_option('pdf_builder_canvas_resize_enabled', '1') === '1',
            'rotate_enabled' => get_option('pdf_builder_canvas_rotate_enabled', '1') === '1',
            'multi_select' => get_option('pdf_builder_canvas_multi_select', '1') === '1',
            'selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
            'keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') === '1',
            'grid_enabled' => get_option('pdf_builder_canvas_grid_enabled', '1') === '1',
            'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
            'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') === '1',
            'navigation_enabled' => get_option('pdf_builder_canvas_navigation_enabled', '1') === '1',
            'zoom_default' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
            'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
            'export_quality' => intval(get_option('pdf_builder_canvas_export_quality', 90)),
            'autosave_enabled' => get_option('pdf_builder_canvas_autosave_enabled', '1') === '1',
            'auto_save_interval' => intval(get_option('pdf_builder_canvas_auto_save_interval', 300)),
        ];
    }
}