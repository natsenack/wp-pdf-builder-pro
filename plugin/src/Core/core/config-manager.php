<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Configuration Manager
 *
 * Centralise toutes les configurations d'options WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Config_Manager {

    // ==========================================
    // CONFIGURATIONS D'OPTIONS CENTRALISÉES
    // ==========================================

    private static $option_configs = [
        // Debug & Logging
        'pdf_builder_debug_mode' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => false],

        // Company Info
        'pdf_builder_company_phone_manual' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''],
        'pdf_builder_company_siret' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''],
        'pdf_builder_company_vat' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''],
        'pdf_builder_company_rcs' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''],
        'pdf_builder_company_capital' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''],

        // Cache
        'pdf_builder_cache_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_cache_compression' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => false],
        'pdf_builder_cache_auto_cleanup' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_cache_max_size' => ['type' => 'int', 'sanitize' => 'intval', 'default' => 100],
        'pdf_builder_cache_ttl' => ['type' => 'int', 'sanitize' => 'intval', 'default' => 3600],

        // Maintenance
        'pdf_builder_auto_maintenance' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_performance_auto_optimization' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_auto_backup' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_backup_retention' => ['type' => 'int', 'sanitize' => 'intval', 'default' => 7],
        'pdf_builder_auto_backup_frequency' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'daily'],

        // Security
        'pdf_builder_allowed_roles' => ['type' => 'array', 'default' => ['administrator']],
        'pdf_builder_security_level' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'medium'],
        'pdf_builder_enable_logging' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],

        // GDPR
        'pdf_builder_gdpr_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_gdpr_consent_required' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_gdpr_data_retention' => ['type' => 'int', 'sanitize' => 'intval', 'default' => 2555],
        'pdf_builder_gdpr_audit_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => false],
        'pdf_builder_gdpr_encryption_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => false],
        'pdf_builder_gdpr_consent_analytics' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => false],
        'pdf_builder_gdpr_consent_templates' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_gdpr_consent_marketing' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => false],

        // PDF Settings
        'pdf_builder_pdf_quality' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'high'],
        'pdf_builder_pdf_page_size' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'A4'],
        'pdf_builder_pdf_orientation' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'portrait'],
        'pdf_builder_pdf_cache_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_pdf_compression' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => 'medium'],
        'pdf_builder_pdf_metadata_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],
        'pdf_builder_pdf_print_optimized' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],

        // Templates
        'pdf_builder_default_template' => ['type' => 'string', 'sanitize' => 'sanitize_text_field', 'default' => ''],
        'pdf_builder_template_library_enabled' => ['type' => 'boolean', 'sanitize' => 'intval', 'default' => true],

        // Canvas Settings
        'pdf_builder_canvas_width' => ['type' => 'int', 'sanitize' => 'intval', 'default' => 794],
        'pdf_builder_canvas_height' => ['type' => 'int', 'sanitize' => 'intval', 'default' => 1123],
        'pdf_builder_canvas_settings' => ['type' => 'array', 'default' => []],
    ];

    // ==========================================
    // CONFIGURATIONS DE RÉPONSE AJAX
    // ==========================================

    private static $response_configs = [
        'save_settings' => [
            'success_message' => 'Tous les paramètres ont été sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres.',
            'include_saved_options' => true
        ],
        'save_canvas' => [
            'success_message' => 'Paramètres canvas sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres canvas.',
            'include_saved_options' => false
        ],
        'save_dimensions' => [
            'success_message' => 'Dimensions sauvegardées avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des dimensions.',
            'include_saved_options' => false
        ],
        'save_apparence' => [
            'success_message' => 'Paramètres d\'apparence sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres d\'apparence.',
            'include_saved_options' => false
        ],
        'save_grille' => [
            'success_message' => 'Paramètres de grille sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de grille.',
            'include_saved_options' => false
        ],
        'save_interaction' => [
            'success_message' => 'Paramètres d\'interaction sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres d\'interaction.',
            'include_saved_options' => false
        ],
        'save_performance' => [
            'success_message' => 'Paramètres de performance sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de performance.',
            'include_saved_options' => false
        ],
        'save_securite' => [
            'success_message' => 'Paramètres de sécurité sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de sécurité.',
            'include_saved_options' => false
        ],
        'save_contenu' => [
            'success_message' => 'Paramètres de contenu sauvegardés avec succès.',
            'error_message' => 'Erreur lors de la sauvegarde des paramètres de contenu.',
            'include_saved_options' => false
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir la configuration d'une option
     */
    public static function get_option_config($option_name) {
        return self::$option_configs[$option_name] ?? null;
    }

    /**
     * Obtenir toutes les configurations d'options
     */
    public static function get_all_option_configs() {
        return self::$option_configs;
    }

    /**
     * Obtenir la configuration de réponse pour une action
     */
    public static function get_response_config($action) {
        return self::$response_configs[$action] ?? null;
    }

    /**
     * Obtenir toutes les configurations de réponse
     */
    public static function get_all_response_configs() {
        return self::$response_configs;
    }

    /**
     * Obtenir la valeur par défaut d'une option
     */
    public static function get_default_value($option_name) {
        $config = self::get_option_config($option_name);
        return $config ? ($config['default'] ?? null) : null;
    }

    /**
     * Sanitiser une valeur selon sa configuration
     */
    public static function sanitize_value($option_name, $value) {
        $config = self::get_option_config($option_name);
        if (!$config || !isset($config['sanitize'])) {
            return $value;
        }

        $sanitize_function = $config['sanitize'];
        if (function_exists($sanitize_function)) {
            return $sanitize_function($value);
        }

        return $value;
    }

    /**
     * Valider le type d'une valeur
     */
    public static function validate_type($option_name, $value) {
        $config = self::get_option_config($option_name);
        if (!$config || !isset($config['type'])) {
            return true; // Pas de validation si pas de type défini
        }

        switch ($config['type']) {
            case 'boolean':
                return is_bool($value) || in_array($value, [0, 1, '0', '1']);
            case 'int':
                return is_numeric($value) && intval($value) == $value;
            case 'string':
                return is_string($value);
            case 'array':
                return is_array($value);
            default:
                return true;
        }
    }

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check health of configuration system
     */
    public function check_health() {
        $health = [
            'status' => 'ok',
            'issues' => [],
            'configs_loaded' => count(self::$option_configs),
            'responses_loaded' => count(self::$response_configs)
        ];

        // Vérifier que les configurations essentielles sont présentes
        $essential_configs = ['pdf_builder_cache_enabled', 'pdf_builder_debug_mode'];
        foreach ($essential_configs as $config) {
            if (!isset(self::$option_configs[$config])) {
                $health['issues'][] = "Configuration manquante: {$config}";
                $health['status'] = 'warning';
            }
        }

        return $health;
    }
}



