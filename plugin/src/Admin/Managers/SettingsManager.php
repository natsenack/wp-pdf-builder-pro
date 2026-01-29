<?php

/**
 * PDF Builder Pro - Gestionnaire de Paramètres
 * Gère la sauvegarde et récupération des paramètres
 */

namespace PDF_Builder\Admin\Managers;


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
        \add_action('admin_init', [$this, 'registerSettings']);

        // Charger les styles pour les pages d'administration
        \add_action('admin_enqueue_scripts', [$this, 'enqueueAdminStyles']);

        // Hook pour vérifier la sauvegarde des paramètres
        \add_action('update_option_pdf_builder_settings', [$this, 'onSettingsUpdated'], 10, 3);
    }

    /**
     * Enregistrer les paramètres WordPress
     */
    public function registerSettings()
    {
        // Enregistrer le tableau principal des paramètres
        \register_setting('pdf_builder_settings', 'pdf_builder_settings', [$this, 'sanitizeSettings']);

        // Section principale
        \add_settings_section(
            'pdf_builder_general',
            \__('Paramètres Généraux', 'pdf-builder-pro'),
            [$this, 'renderGeneralSection'],
            'pdf_builder_settings'
        );

        \add_settings_field(
            'company_info',
            \__('Informations Entreprise', 'pdf-builder-pro'),
            [$this, 'renderCompanyInfoField'],
            'pdf_builder_settings',
            'pdf_builder_general'
        );

        // Section performance
        \add_settings_section(
            'pdf_builder_performance',
            \__('Performance', 'pdf-builder-pro'),
            [$this, 'renderPerformanceSection'],
            'pdf_builder_settings'
        );

        \add_settings_field(
            'cache_settings',
            \__('Cache', 'pdf-builder-pro'),
            [$this, 'renderCacheField'],
            'pdf_builder_settings',
            'pdf_builder_performance'
        );

        \add_settings_field(
            'performance_limits',
            \__('Limites de Performance', 'pdf-builder-pro'),
            [$this, 'renderPerformanceLimitsField'],
            'pdf_builder_settings',
            'pdf_builder_performance'
        );

        // Section canvas
        \add_settings_section(
            'pdf_builder_canvas',
            \__('Paramètres Canvas', 'pdf-builder-pro'),
            [$this, 'renderCanvasSection'],
            'pdf_builder_settings'
        );

        \add_settings_field(
            'canvas_display_dimensions',
            \__('🎨 Affichage & Dimensions', 'pdf-builder-pro'),
            [$this, 'renderCanvasDisplayDimensionsField'],
            'pdf_builder_settings',
            'pdf_builder_canvas'
        );

        \add_settings_field(
            'canvas_available_options',
            \__('📋 Options Disponibles', 'pdf-builder-pro'),
            [$this, 'renderCanvasAvailableOptionsField'],
            'pdf_builder_settings',
            'pdf_builder_canvas'
        );
    }



    /**
     * Section générale
     */
    public function renderGeneralSection()
    {
        echo '<p>' . \__('Configurez les paramètres généraux du plugin PDF Builder Pro.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Section performance
     */
    public function renderPerformanceSection()
    {
        echo '<p>' . \__('Paramètres de performance et optimisation.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Section canvas
     */
    public function renderCanvasSection()
    {
        echo '<p>' . \__('Paramètres par défaut du canvas d\'édition.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Champ informations entreprise
     */
    public function renderCompanyInfoField()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $company_name = $settings['pdf_builder_company_name'] ?? '';
        $company_address = $settings['pdf_builder_company_address'] ?? '';
        $company_phone = $settings['pdf_builder_company_phone'] ?? '';
        $company_email = $settings['pdf_builder_company_email'] ?? '';
        $default_language = $settings['pdf_builder_default_language'] ?? 'fr';

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="pdf_builder_company_name">' . \__('Nom de l\'entreprise', 'pdf-builder-pro') . '</label></th>';
        echo '<td><input type="text" id="pdf_builder_company_name" name="pdf_builder_company_name" value="' . \esc_attr($company_name) . '" class="regular-text"></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_company_address">' . \__('Adresse', 'pdf-builder-pro') . '</label></th>';
        echo '<td><textarea id="pdf_builder_company_address" name="pdf_builder_company_address" rows="3" class="regular-text">' . \esc_textarea($company_address) . '</textarea></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_company_phone">' . \__('Téléphone', 'pdf-builder-pro') . '</label></th>';
        echo '<td><input type="tel" id="pdf_builder_company_phone" name="pdf_builder_company_phone" value="' . \esc_attr($company_phone) . '" class="regular-text"></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_company_email">' . \__('Email', 'pdf-builder-pro') . '</label></th>';
        echo '<td><input type="email" id="pdf_builder_company_email" name="pdf_builder_company_email" value="' . \esc_attr($company_email) . '" class="regular-text"></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_default_language">' . \__('Langue par défaut', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<select id="pdf_builder_default_language" name="pdf_builder_default_language">';
        echo '<option value="fr" ' . \selected($default_language, 'fr', false) . '>Français</option>';
        echo '<option value="en" ' . \selected($default_language, 'en', false) . '>English</option>';
        echo '<option value="es" ' . \selected($default_language, 'es', false) . '>Español</option>';
        echo '<option value="de" ' . \selected($default_language, 'de', false) . '>Deutsch</option>';
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
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $enable_cache = $settings['pdf_builder_cache_enabled'] ?? '1';
        $cache_timeout = $settings['pdf_builder_cache_ttl'] ?? 3600;

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="pdf_builder_enable_cache">' . \__('Activer le cache', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="checkbox" id="pdf_builder_enable_cache" name="pdf_builder_enable_cache" value="1" ' . \checked($enable_cache, '1', false) . '>';
        echo '<p class="description">' . \__('Améliore les performances en cachant les résultats des requêtes.', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_cache_timeout">' . \__('Timeout du cache (secondes)', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_cache_timeout" name="pdf_builder_cache_timeout" value="' . \esc_attr($cache_timeout) . '" min="60" max="86400" step="60">';
        echo '<p class="description">' . \__('Durée avant expiration du cache (3600 = 1 heure).', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Champ limites de performance
     */
    public function renderPerformanceLimitsField()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $compression_level = $settings['pdf_builder_compression_level'] ?? 6;
        $memory_limit = $settings['pdf_builder_memory_limit'] ?? 256;
        $max_execution_time = $settings['pdf_builder_max_execution_time'] ?? 30;

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="pdf_builder_compression_level">' . \__('Niveau de compression', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_compression_level" name="pdf_builder_compression_level" value="' . \esc_attr($compression_level) . '" min="0" max="9">';
        echo '<p class="description">' . \__('Niveau de compression des images (0-9, 6 recommandé).', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_memory_limit">' . \__('Limite mémoire (MB)', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_memory_limit" name="pdf_builder_memory_limit" value="' . \esc_attr($memory_limit) . '" min="64" max="1024" step="64">';
        echo '<p class="description">' . \__('Mémoire maximale allouée pour la génération PDF.', 'pdf-builder-pro') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th><label for="pdf_builder_max_execution_time">' . \__('Temps d\'exécution max (secondes)', 'pdf-builder-pro') . '</label></th>';
        echo '<td>';
        echo '<input type="number" id="pdf_builder_max_execution_time" name="pdf_builder_max_execution_time" value="' . \esc_attr($max_execution_time) . '" min="10" max="300" step="5">';
        echo '<p class="description">' . \__('Temps maximum pour générer un PDF.', 'pdf-builder-pro') . '</p>';
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
            if (!\is_user_logged_in() || !\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                \wp_send_json_error('Nonce invalide');
                return;
            }

            // Paramètres généraux
            $settings = [
                'pdf_builder_company_name' => \sanitize_text_field($_POST['company_name'] ?? ''),
                'pdf_builder_company_address' => \sanitize_textarea_field($_POST['company_address'] ?? ''),
                'pdf_builder_company_phone' => \sanitize_text_field($_POST['company_phone'] ?? ''),
                'pdf_builder_company_email' => \sanitize_email($_POST['company_email'] ?? ''),
                'pdf_builder_default_language' => \sanitize_text_field($_POST['default_language'] ?? 'fr'),
            ];

            foreach ($settings as $key => $value) {
                \update_option($key, $value);
            }

            \wp_send_json_success([
                'message' => 'Paramètres généraux sauvegardés'
            ]);

        } catch (Exception $e) {
            \wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder les paramètres de performance (AJAX)
     */
    public function ajaxSavePerformanceSettings()
    {
        try {
            // Vérifier les permissions
            if (!\is_user_logged_in() || !\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
                return;
            }

            // Vérifier le nonce
            if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
                \wp_send_json_error('Nonce invalide');
                return;
            }

            // Paramètres de performance
            $settings = [
                'pdf_builder_enable_cache' => isset($_POST['enable_cache']) ? '1' : '0',
                'pdf_builder_cache_timeout' => \intval($_POST['cache_timeout'] ?? 3600),
                'pdf_builder_compression_level' => \intval($_POST['compression_level'] ?? 6),
                'pdf_builder_memory_limit' => \intval($_POST['memory_limit'] ?? 256),
                'pdf_builder_max_execution_time' => \intval($_POST['max_execution_time'] ?? 30),
            ];

            foreach ($settings as $key => $value) {
                \update_option($key, $value);
            }

            \wp_send_json_success([
                'message' => 'Paramètres de performance sauvegardés'
            ]);

        } catch (Exception $e) {
            \wp_send_json_error('Erreur: ' . $e->getMessage());
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
     * Champ affichage et dimensions du canvas
     */
    public function renderCanvasDisplayDimensionsField()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Formats disponibles
        $format_a3_enabled = $settings['pdf_builder_canvas_format_a3_enabled'] ?? '1';
        $format_a4_enabled = $settings['pdf_builder_canvas_format_a4_enabled'] ?? '1';
        $format_a5_enabled = $settings['pdf_builder_canvas_format_a5_enabled'] ?? '1';
        $format_letter_enabled = $settings['pdf_builder_canvas_format_letter_enabled'] ?? '1';
        $format_legal_enabled = $settings['pdf_builder_canvas_format_legal_enabled'] ?? '1';

        // Orientations disponibles
        $orientation_portrait_enabled = $settings['pdf_builder_canvas_orientation_portrait_enabled'] ?? '1';
        $orientation_landscape_enabled = $settings['pdf_builder_canvas_orientation_landscape_enabled'] ?? '1';

        // Résolutions DPI disponibles
        $dpi_72_enabled = $settings['pdf_builder_canvas_dpi_72_enabled'] ?? '1';
        $dpi_96_enabled = $settings['pdf_builder_canvas_dpi_96_enabled'] ?? '1';
        $dpi_150_enabled = $settings['pdf_builder_canvas_dpi_150_enabled'] ?? '1';
        $dpi_300_enabled = $settings['pdf_builder_canvas_dpi_300_enabled'] ?? '1';
        $dpi_600_enabled = $settings['pdf_builder_canvas_dpi_600_enabled'] ?? '1';

        echo '<div style="max-width: 800px;">';

        // Formats de papier disponibles
        echo '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #e1e8ed; border-radius: 6px;">';
        echo '<h4 style="margin: 0 0 15px 0; color: #23282d;">📄 Formats de papier disponibles</h4>';
        echo '<p class="description" style="margin-bottom: 15px;">Sélectionnez les formats de papier que les utilisateurs pourront choisir dans les paramètres des templates.</p>';

        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">';
        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_format_a3_enabled" value="1" ' . checked($format_a3_enabled, '1', false) . '>';
        echo '<span>A3 (297×420 mm)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_format_a4_enabled" value="1" ' . checked($format_a4_enabled, '1', false) . '>';
        echo '<span>A4 (210×297 mm)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_format_a5_enabled" value="1" ' . checked($format_a5_enabled, '1', false) . '>';
        echo '<span>A5 (148×210 mm)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_format_letter_enabled" value="1" ' . checked($format_letter_enabled, '1', false) . '>';
        echo '<span>Letter (8.5×11")</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_format_legal_enabled" value="1" ' . checked($format_legal_enabled, '1', false) . '>';
        echo '<span>Legal (8.5×14")</span>';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        // Orientations disponibles
        echo '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #e1e8ed; border-radius: 6px;">';
        echo '<h4 style="margin: 0 0 15px 0; color: #23282d;">🔄 Orientations disponibles</h4>';
        echo '<p class="description" style="margin-bottom: 15px;">Sélectionnez les orientations que les utilisateurs pourront choisir dans les paramètres des templates.</p>';

        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">';
        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_orientation_portrait_enabled" value="1" ' . checked($orientation_portrait_enabled, '1', false) . '>';
        echo '<span>Portrait</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_orientation_landscape_enabled" value="1" ' . checked($orientation_landscape_enabled, '1', false) . '>';
        echo '<span>Paysage</span>';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        // Résolutions DPI disponibles
        echo '<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #e1e8ed; border-radius: 6px;">';
        echo '<h4 style="margin: 0 0 15px 0; color: #23282d;">🎯 Résolutions DPI disponibles</h4>';
        echo '<p class="description" style="margin-bottom: 15px;">Sélectionnez les résolutions DPI que les utilisateurs pourront choisir dans les paramètres des templates.</p>';

        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">';
        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_dpi_72_enabled" value="1" ' . checked($dpi_72_enabled, '1', false) . '>';
        echo '<span>72 DPI (Écran)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_dpi_96_enabled" value="1" ' . checked($dpi_96_enabled, '1', false) . '>';
        echo '<span>96 DPI (Web)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_dpi_150_enabled" value="1" ' . checked($dpi_150_enabled, '1', false) . '>';
        echo '<span>150 DPI (Impression)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_dpi_300_enabled" value="1" ' . checked($dpi_300_enabled, '1', false) . '>';
        echo '<span>300 DPI (Haute qualité)</span>';
        echo '</label>';

        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: white; border: 1px solid #ddd; border-radius: 4px;">';
        echo '<input type="checkbox" name="pdf_builder_canvas_dpi_600_enabled" value="1" ' . checked($dpi_600_enabled, '1', false) . '>';
        echo '<span>600 DPI (Très haute qualité)</span>';
        echo '</label>';
        echo '</div>';
        echo '</div>';

        echo '<div style="padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; color: #856404;">';
        echo '<strong>ℹ️ Note :</strong> Ces paramètres contrôlent uniquement les options disponibles dans les paramètres des templates individuels. Ils n\'affectent pas les fonctionnalités générales du canvas.';
        echo '</div>';

        echo '</div>';
    }

    /**
     * Obtenir les paramètres généraux
     */
    private function getGeneralSettings()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
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
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        return [
            'enable_cache' => ($settings['pdf_builder_cache_enabled'] ?? '0') === '1',
            'cache_timeout' => \intval($settings['pdf_builder_cache_ttl'] ?? 3600),
            'compression_level' => \intval($settings['pdf_builder_compression_level'] ?? 6),
            'memory_limit' => \intval($settings['pdf_builder_memory_limit'] ?? 256),
            'max_execution_time' => \intval($settings['pdf_builder_max_execution_time'] ?? 30),
        ];
    }

    /**
     * Obtenir les paramètres canvas
     */
    private function getCanvasSettings()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        return [
            'format' => $settings['pdf_builder_canvas_format'] ?? 'A4',
            'orientation' => $settings['pdf_builder_canvas_default_orientation'] ?? 'portrait',
            'dpi' => \intval($settings['pdf_builder_canvas_dpi'] ?? 96),
            'width' => \intval($settings['pdf_builder_canvas_width'] ?? 794),
            'height' => \intval($settings['pdf_builder_canvas_height'] ?? 1123),
            'drag_enabled' => ($settings['pdf_builder_canvas_drag_enabled'] ?? '1') === '1',
            'resize_enabled' => ($settings['pdf_builder_canvas_resize_enabled'] ?? '1') === '1',
            'rotate_enabled' => ($settings['pdf_builder_canvas_rotate_enabled'] ?? '1') === '1',
            'multi_select' => ($settings['pdf_builder_canvas_multi_select'] ?? '1') === '1',
            'selection_mode' => $settings['pdf_builder_canvas_selection_mode'] ?? 'click',
            'keyboard_shortcuts' => ($settings['pdf_builder_canvas_keyboard_shortcuts'] ?? '1') === '1',
            'grid_enabled' => ($settings['pdf_builder_canvas_grid_enabled'] ?? '1') === '1',
            'grid_size' => \intval($settings['pdf_builder_canvas_grid_size'] ?? 20),
            'snap_to_grid' => ($settings['pdf_builder_canvas_snap_to_grid'] ?? '1') === '1',
            'navigation_enabled' => ($settings['pdf_builder_canvas_navigation_enabled'] ?? '1') === '1',
            'zoom_default' => \intval($settings['pdf_builder_canvas_zoom_default'] ?? 100),
            'export_format' => $settings['pdf_builder_canvas_export_format'] ?? 'png',
            'export_quality' => \intval($settings['pdf_builder_canvas_export_quality'] ?? 90),
        ];
    }

    /**
     * Fonction de sanitisation pour les paramètres
     */
    public function sanitizeSettings($input)
    {
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === SANITIZE SETTINGS START ==='); }
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] sanitizeSettings called with input count: ' . (is_array($input) ? count($input) : 'not array')); }
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Raw input data: ' . print_r($input, true)); }

        // Vérifier si l'input est vide
        if (empty($input)) {
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ERROR: sanitizeSettings called with empty input!'); }
            return array();
        }

        $sanitized = array();

        // Récupérer les valeurs existantes pour les fusionner
        $existing = pdf_builder_get_option('pdf_builder_settings', array());
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Existing settings count: ' . count($existing)); }
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Existing settings keys: ' . implode(', ', array_keys($existing))); }

        // Commencer avec les valeurs existantes
        $sanitized = $existing;

        // CORRECTION: Suppression de la logique des checkboxes non présentes dans l'input

        // LOGS SPECIFIQUES POUR LES TEMPLATES
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === CHECKING TEMPLATE FIELDS ==='); }
        $template_fields = ['pdf_builder_default_template', 'pdf_builder_template_library_enabled'];
        foreach ($template_fields as $field) {
            if (isset($input[$field])) {
                if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Template field '$field' found with value: '" . $input[$field] . "'"); }
            } else {
                if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Template field '$field' NOT found in input"); }
            }
        }

        // LOGS SPECIFIQUES POUR LES COULEURS CANVAS
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === CHECKING CANVAS COLOR FIELDS ==='); }
        $canvas_color_fields = [
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_container_bg_color'
        ];
        foreach ($canvas_color_fields as $field) {
            if (isset($input[$field])) {
                if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Canvas color field '$field' found with value: '" . $input[$field] . "'"); }
            } else {
                if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Canvas color field '$field' NOT found in input"); }
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
            $sanitized['pdf_builder_company_phone_manual'] = \sanitize_text_field($input['pdf_builder_company_phone_manual']);
        }
        if (isset($input['pdf_builder_company_siret'])) {
            $sanitized['pdf_builder_company_siret'] = \sanitize_text_field($input['pdf_builder_company_siret']);
        }
        if (isset($input['pdf_builder_company_vat'])) {
            $sanitized['pdf_builder_company_vat'] = \sanitize_text_field($input['pdf_builder_company_vat']);
        }
        if (isset($input['pdf_builder_company_rcs'])) {
            $sanitized['pdf_builder_company_rcs'] = \sanitize_text_field($input['pdf_builder_company_rcs']);
        }
        if (isset($input['pdf_builder_company_capital'])) {
            $sanitized['pdf_builder_company_capital'] = \sanitize_text_field($input['pdf_builder_company_capital']);
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
            $sanitized['pdf_builder_developer_password'] = \sanitize_text_field($input['pdf_builder_developer_password']);
        }
        if (isset($input['pdf_builder_license_test_mode_enabled'])) {
            $sanitized['pdf_builder_license_test_mode_enabled'] = $input['pdf_builder_license_test_mode_enabled'] ? '1' : '0';
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
            $sanitized['pdf_builder_log_file_size'] = \intval($input['pdf_builder_log_file_size']);
            $sanitized['pdf_builder_log_file_size'] = max(1, min(100, $sanitized['pdf_builder_log_file_size']));
        }
        if (isset($input['pdf_builder_log_retention'])) {
            $sanitized['pdf_builder_log_retention'] = \intval($input['pdf_builder_log_retention']);
            $sanitized['pdf_builder_log_retention'] = max(1, min(365, $sanitized['pdf_builder_log_retention']));
        }
        if (isset($input['pdf_builder_force_https'])) {
            $sanitized['pdf_builder_force_https'] = $input['pdf_builder_force_https'] ? '1' : '0';
        }
        if (isset($input['pdf_builder_performance_monitoring'])) {
            $sanitized['pdf_builder_performance_monitoring'] = $input['pdf_builder_performance_monitoring'] ? '1' : '0';
        }

        // Sanitisation des paramètres de licence avec conformité RGPD
        if (isset($input['pdf_builder_license_email_reminders'])) {
            $email_reminders_enabled = $input['pdf_builder_license_email_reminders'] ? '1' : '0';
            $sanitized['pdf_builder_license_email_reminders'] = $email_reminders_enabled;

            // RGPD : Si les rappels sont désactivés, supprimer automatiquement l'adresse email
            if ($email_reminders_enabled === '0') {
                $sanitized['pdf_builder_license_reminder_email'] = '';
                if (class_exists('\PDF_Builder_Logger')) {
                    \PDF_Builder_Logger::get_instance()->debug_log('[RGPD] Rappels désactivés - adresse email supprimée automatiquement');
                }
                // Passer au traitement suivant (ne pas traiter l'email)
                goto after_email_processing;
            }
        }

        if (isset($input['pdf_builder_license_reminder_email'])) {
            $email = trim($input['pdf_builder_license_reminder_email']);

            // Vérifications RGPD pour l'adresse email
            if (!empty($email)) {
                // 1. Validation de l'email
                if (!\is_email($email)) {
                    // Email invalide - ne pas sauvegarder
                    if (class_exists('\PDF_Builder_Logger')) {
                        \PDF_Builder_Logger::get_instance()->debug_log('[RGPD] Email invalide fourni pour les rappels: ' . $email);
                    }
                    $email = '';
                } else {
                    // 2. Sanitisation de l'email
                    $email = \sanitize_email($email);

                    // 3. Vérification du consentement (case cochée)
                    $consent_given = isset($input['pdf_builder_license_email_reminders']) && $input['pdf_builder_license_email_reminders'] === '1';

                    if (!$consent_given) {
                        // Pas de consentement - ne pas sauvegarder l'email
                        if (class_exists('\PDF_Builder_Logger')) {
                            \PDF_Builder_Logger::get_instance()->debug_log('[RGPD] Consentement non donné pour l\'email: ' . $email);
                        }
                        $email = '';
                    } else {
                        // 4. Finalité légitime : rappels de licence uniquement
                        // L'email ne sera utilisé que pour les notifications de licence

                        // 5. Conservation limitée : liée à la durée de validité de la licence
                        // L'email sera automatiquement supprimé lors de la désactivation du mode test

                        if (class_exists('\PDF_Builder_Logger')) {
                            \PDF_Builder_Logger::get_instance()->debug_log('[RGPD] Email validé et consenti pour les rappels: ' . $email);
                        }
                    }
                }
            }

            $sanitized['pdf_builder_license_reminder_email'] = $email;
        }

        after_email_processing:

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
            'pdf_builder_canvas_format' => '\sanitize_text_field',
            'pdf_builder_canvas_bg_color' => '\sanitize_hex_color',
            'pdf_builder_canvas_border_color' => '\sanitize_hex_color',
            'pdf_builder_canvas_border_width' => 'intval',
            'pdf_builder_canvas_shadow_enabled' => 'bool',
            'pdf_builder_canvas_container_bg_color' => '\sanitize_hex_color',
            'pdf_builder_canvas_grid_enabled' => 'bool',
            'pdf_builder_canvas_grid_size' => 'intval',
            'pdf_builder_canvas_guides_enabled' => 'bool',
            'pdf_builder_canvas_snap_to_grid' => 'bool',
            'pdf_builder_canvas_zoom_min' => 'intval',
            'pdf_builder_canvas_zoom_max' => 'intval',
            'pdf_builder_canvas_zoom_default' => 'intval',
            'pdf_builder_canvas_zoom_step' => 'intval',
            'pdf_builder_canvas_export_quality' => 'intval',
            'pdf_builder_canvas_export_format' => '\sanitize_text_field',
            'pdf_builder_canvas_export_transparent' => 'bool',
            'pdf_builder_canvas_drag_enabled' => 'bool',
            'pdf_builder_canvas_resize_enabled' => 'bool',
            'pdf_builder_canvas_rotate_enabled' => 'bool',
            'pdf_builder_canvas_multi_select' => 'bool',
            'pdf_builder_canvas_selection_mode' => '\sanitize_text_field',
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
            // Nouveaux champs pour les options disponibles dans les templates
            'pdf_builder_canvas_format_a3_enabled' => 'bool',
            'pdf_builder_canvas_format_a4_enabled' => 'bool',
            'pdf_builder_canvas_format_a5_enabled' => 'bool',
            'pdf_builder_canvas_format_letter_enabled' => 'bool',
            'pdf_builder_canvas_format_legal_enabled' => 'bool',
            'pdf_builder_canvas_orientation_portrait_enabled' => 'bool',
            'pdf_builder_canvas_orientation_landscape_enabled' => 'bool',
            'pdf_builder_canvas_dpi_72_enabled' => 'bool',
            'pdf_builder_canvas_dpi_96_enabled' => 'bool',
            'pdf_builder_canvas_dpi_150_enabled' => 'bool',
            'pdf_builder_canvas_dpi_300_enabled' => 'bool',
            'pdf_builder_canvas_dpi_600_enabled' => 'bool',
        ];

        foreach ($canvas_fields as $field => $type) {
            if (isset($input[$field])) {
                switch ($type) {
                    case 'intval':
                        $sanitized[$field] = \intval($input[$field]);
                        break;
                    case 'bool':
                        $sanitized[$field] = $input[$field] ? '1' : '0';
                        break;
                    case '\sanitize_hex_color':
                        $sanitized[$field] = \sanitize_hex_color($input[$field]) ?: $input[$field];
                        break;
                    default:
                        $sanitized[$field] = \sanitize_text_field($input[$field]);
                        break;
                }
            }
        }

        // Sanitisation du paramètre des templates par statut
        if (isset($input['pdf_builder_order_status_templates']) && is_array($input['pdf_builder_order_status_templates'])) {
            $sanitized['pdf_builder_order_status_templates'] = array_map('\sanitize_text_field', $input['pdf_builder_order_status_templates']);
        }

        // Pour l'instant, on accepte les autres valeurs telles quelles mais sanitizées
        foreach ($input as $key => $value) {
            if (!isset($sanitized[$key])) {
                if (is_array($value)) {
                    $sanitized[$key] = array_map('\sanitize_text_field', $value);
                } else {
                    $sanitized[$key] = \sanitize_text_field($value);
                }
            }
        }

        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === FINAL TEMPLATE VALUES ==='); }
        foreach ($template_fields as $field) {
            $final_value = isset($sanitized[$field]) ? $sanitized[$field] : 'NOT_SET';
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Final '$field' = '$final_value'"); }
        }

        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === FINAL CANVAS COLOR VALUES ==='); }
        foreach ($canvas_color_fields as $field) {
            $final_value = isset($sanitized[$field]) ? $sanitized[$field] : 'NOT_SET';
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Final '$field' = '$final_value'"); }
        }

        // Sanitize les options disponibles (tableaux)
        if (isset($input['pdf_builder_available_formats']) && is_array($input['pdf_builder_available_formats'])) {
            $valid_formats = ['A3', 'A4', 'A5', 'Letter', 'Legal', 'Tabloid', 'Executive'];
            $sanitized['pdf_builder_available_formats'] = array_intersect($input['pdf_builder_available_formats'], $valid_formats);
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Sanitized available_formats: ' . implode(', ', $sanitized['pdf_builder_available_formats'])); }
        }

        if (isset($input['pdf_builder_available_orientations']) && is_array($input['pdf_builder_available_orientations'])) {
            $valid_orientations = ['portrait', 'landscape'];
            $sanitized['pdf_builder_available_orientations'] = array_intersect($input['pdf_builder_available_orientations'], $valid_orientations);
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Sanitized available_orientations: ' . implode(', ', $sanitized['pdf_builder_available_orientations'])); }
        }

        if (isset($input['pdf_builder_available_dpi']) && is_array($input['pdf_builder_available_dpi'])) {
            $valid_dpi = [72, 96, 150, 200, 300, 600, 1200];
            $sanitized['pdf_builder_available_dpi'] = array_map('intval', array_intersect($input['pdf_builder_available_dpi'], $valid_dpi));
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Sanitized available_dpi: ' . implode(', ', $sanitized['pdf_builder_available_dpi'])); }
        }

        // Save to custom table instead of wp_options
        pdf_builder_update_option('pdf_builder_settings', $sanitized);

        // Save individual general fields to separate rows
        $general_fields = [
            'pdf_builder_company_phone_manual',
            'pdf_builder_company_siret',
            'pdf_builder_company_vat',
            'pdf_builder_company_rcs',
            'pdf_builder_company_capital'
        ];

        foreach ($general_fields as $field) {
            if (isset($input[$field])) {
                pdf_builder_update_option($field, \sanitize_text_field($input[$field]));
                if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] Saved general field '$field' = '" . $input[$field] . "'"); }
            }
        }

        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] sanitizeSettings saved to custom table, returning false to prevent wp_options save'); }
        return false; // Prevent WordPress from saving to wp_options
    }

    /**
     * Hook appelé quand les paramètres sont mis à jour
     */
    public function onSettingsUpdated($old_value, $new_value, $option)
    {
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ✅ SUCCESS: onSettingsUpdated called for option: ' . $option); }
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ✅ Old value count: ' . (is_array($old_value) ? count($old_value) : 'not array')); }
        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ✅ New value count: ' . (is_array($new_value) ? count($new_value) : 'not array')); }

        // Logs spécifiques pour les champs problématiques
        $check_fields = [
            'pdf_builder_default_template',
            'pdf_builder_template_library_enabled',
            'pdf_builder_canvas_bg_color',
            'pdf_builder_canvas_border_color',
            'pdf_builder_canvas_container_bg_color'
        ];

        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === SETTINGS UPDATE DETAILS ==='); }
        foreach ($check_fields as $field) {
            $old_val = isset($old_value[$field]) ? $old_value[$field] : 'NOT_SET';
            $new_val = (is_array($new_value) && isset($new_value[$field])) ? $new_value[$field] : 'NOT_SET';
            $changed = ($old_val !== $new_val) ? 'CHANGED' : 'UNCHANGED';
            if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log("[PDF Builder] $field: '$old_val' -> '$new_val' [$changed]"); }
        }

        if (class_exists('\PDF_Builder_Logger')) { \PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] ✅ New value sample: ' . (is_array($new_value) ? print_r(array_slice($new_value, 0, 5), true) : 'Not an array (returned false from sanitizeSettings)')); }
    }

    /**
     * Charger les styles d'administration pour les paramètres
     */
    public function enqueueAdminStyles($hook)
    {
        // Charger les styles seulement sur les pages d'administration du plugin
        if (strpos($hook, 'pdf-builder') !== false || strpos($hook, 'settings_page_pdf_builder') !== false) {
            $css_file = \plugin_dir_url(dirname(dirname(dirname(__FILE__)))) . 'assets/css/settings.css';
            $css_path = \plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'assets/css/settings.css';

            // Vérifier que le fichier existe avant de le charger
            if (file_exists($css_path)) {
                \wp_enqueue_style(
                    'pdf-builder-settings',
                    $css_file,
                    array(),
                    filemtime($css_path),
                    'all'
                );
            }
        }
    }

    /**
     * Champ options disponibles canvas
     */
    public function renderCanvasAvailableOptionsField()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Formats disponibles
        $available_formats = $settings['pdf_builder_available_formats'] ?? ['A3', 'A4', 'A5', 'Letter', 'Legal'];
        $all_formats = ['A3', 'A4', 'A5', 'Letter', 'Legal', 'Tabloid', 'Executive'];

        // Orientations disponibles
        $available_orientations = $settings['pdf_builder_available_orientations'] ?? ['portrait', 'landscape'];
        $all_orientations = ['portrait', 'landscape'];

        // DPI disponibles
        $available_dpi = $settings['pdf_builder_available_dpi'] ?? [72, 96, 150, 300, 600];
        $all_dpi = [72, 96, 150, 200, 300, 600, 1200];

        echo '<div style="max-width: 600px;">';

        // Formats
        echo '<div style="margin-bottom: 20px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #23282d;">📄 Formats de papier disponibles</h4>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
        foreach ($all_formats as $format) {
            $checked = in_array($format, $available_formats) ? 'checked' : '';
            echo '<label style="display: flex; align-items: center; margin: 0;">';
            echo '<input type="checkbox" name="pdf_builder_available_formats[]" value="' . esc_attr($format) . '" ' . $checked . ' style="margin-right: 5px;">';
            echo esc_html($format);
            echo '</label>';
        }
        echo '</div>';
        echo '<p class="description">Sélectionnez les formats de papier que les utilisateurs peuvent choisir pour leurs templates.</p>';
        echo '</div>';

        // Orientations
        echo '<div style="margin-bottom: 20px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #23282d;">🔄 Orientations disponibles</h4>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
        foreach ($all_orientations as $orientation) {
            $checked = in_array($orientation, $available_orientations) ? 'checked' : '';
            $label = $orientation === 'portrait' ? 'Portrait' : 'Paysage';
            echo '<label style="display: flex; align-items: center; margin: 0;">';
            echo '<input type="checkbox" name="pdf_builder_available_orientations[]" value="' . esc_attr($orientation) . '" ' . $checked . ' style="margin-right: 5px;">';
            echo esc_html($label);
            echo '</label>';
        }
        echo '</div>';
        echo '<p class="description">Sélectionnez les orientations que les utilisateurs peuvent choisir pour leurs templates.</p>';
        echo '</div>';

        // DPI
        echo '<div style="margin-bottom: 20px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #23282d;">🎯 Résolutions DPI disponibles</h4>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
        foreach ($all_dpi as $dpi) {
            $checked = in_array($dpi, $available_dpi) ? 'checked' : '';
            echo '<label style="display: flex; align-items: center; margin: 0;">';
            echo '<input type="checkbox" name="pdf_builder_available_dpi[]" value="' . esc_attr($dpi) . '" ' . $checked . ' style="margin-right: 5px;">';
            echo esc_html($dpi) . ' DPI';
            echo '</label>';
        }
        echo '</div>';
        echo '<p class="description">Sélectionnez les résolutions DPI que les utilisateurs peuvent choisir pour leurs templates.</p>';
        echo '</div>';

        echo '</div>';
    }
}




