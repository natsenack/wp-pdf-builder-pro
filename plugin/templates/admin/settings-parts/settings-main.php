<?php
/**
 * PDF Builder Pro - Main Settings Logic
 * Core settings processing and HTML structure
 * Updated: 2025-11-18 20:10:00
 */

// DEBUG: Log que le fichier est chargé
error_log('PDF Builder: settings-main.php loaded at ' . date('Y-m-d H:i:s'));



if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
    wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'pdf-builder-pro'));
}

// Vérifier l'accès via Role_Manager si disponible
if (class_exists('PDF_Builder\\Security\\Role_Manager')) {
    \PDF_Builder\Security\Role_Manager::check_and_block_access();
}

// DEBUG: Log après vérifications de sécurité
error_log('PDF Builder: Security checks passed');

// Charger les styles CSS
require_once dirname(__FILE__) . '/settings-styles.php';

/**
 * Système centralisé de chargement des paramètres sauvegardés
 */
class PDF_Builder_Settings_Loader {

    /**
     * Configuration des paramètres à charger avec leurs valeurs par défaut
     */
    private static $settings_config = [
        // Paramètres généraux
        'pdf_builder_settings' => [],
        'pdf_builder_canvas_settings' => [],

        // Licence
        'pdf_builder_license_test_key' => '',
        'pdf_builder_license_test_mode_enabled' => false,

        // Cache
        'pdf_builder_cache_enabled' => false,
        'pdf_builder_cache_ttl' => 3600,
        'pdf_builder_cache_compression' => true,
        'pdf_builder_cache_auto_cleanup' => true,
        'pdf_builder_cache_max_size' => 100,

        // Entreprise
        'pdf_builder_company_phone_manual' => '',
        'pdf_builder_company_siret' => '',
        'pdf_builder_company_vat' => '',
        'pdf_builder_company_rcs' => '',
        'pdf_builder_company_capital' => '',

        // PDF
        'pdf_builder_pdf_quality' => 'high',
        'pdf_builder_default_format' => 'A4',
        'pdf_builder_default_orientation' => 'portrait',

        // Développeur
        'pdf_builder_developer_enabled' => false,
        'pdf_builder_developer_password' => '',
        'pdf_builder_debug_php_errors' => false,
        'pdf_builder_debug_javascript' => false,
        'pdf_builder_debug_javascript_verbose' => false,
        'pdf_builder_debug_ajax' => false,
        'pdf_builder_debug_pdf_editor' => false,
        'pdf_builder_debug_settings_page' => false,
        'pdf_builder_debug_performance' => false,
        'pdf_builder_debug_database' => false,
        'pdf_builder_log_level' => 3,
        'pdf_builder_log_file_size' => 10,
        'pdf_builder_log_retention' => 30,
        'pdf_builder_force_https' => false,
        'pdf_builder_performance_monitoring' => false,

        // Système
        'pdf_builder_auto_maintenance' => true,
        'pdf_builder_performance_auto_optimization' => false,
        'pdf_builder_auto_backup' => true,
        'pdf_builder_backup_retention' => 30,
        'pdf_builder_auto_backup_frequency' => 'daily',

        // Sécurité
        'pdf_builder_allowed_roles' => [],
        'pdf_builder_security_level' => 'medium',
        'pdf_builder_enable_logging' => true,

        // GDPR
        'pdf_builder_gdpr_enabled' => false,
        'pdf_builder_gdpr_consent_required' => false,
        'pdf_builder_gdpr_data_retention' => 365,
        'pdf_builder_gdpr_audit_enabled' => false,
        'pdf_builder_gdpr_encryption_enabled' => false,
        'pdf_builder_gdpr_consent_analytics' => false,
        'pdf_builder_gdpr_consent_templates' => false,
        'pdf_builder_gdpr_consent_marketing' => false,

        // Templates
        'pdf_builder_default_template' => 'blank',
        'pdf_builder_template_library_enabled' => true,
        'pdf_builder_order_status_templates' => [],

        // Canvas
        'pdf_builder_canvas_width' => 794,
        'pdf_builder_canvas_height' => 1123,
    ];

    /**
     * Charge tous les paramètres sauvegardés depuis la base de données
     */
    public static function load_all_settings() {
        $settings = [];

        foreach (self::$settings_config as $option_key => $default_value) {
            $settings[$option_key] = get_option($option_key, $default_value);
        }

        // Traitement spécial pour license_test_mode
        $settings['license_test_mode'] = $settings['pdf_builder_license_test_mode_enabled'];

        // Log le chargement si debug activé
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Paramètres chargés depuis BDD: ' . count($settings) . ' options');
        }

        return $settings;
    }

    /**
     * Charge un paramètre spécifique
     */
    public static function load_setting($key, $default = null) {
        if (!isset(self::$settings_config[$key])) {
            error_log("PDF Builder: Paramètre inconnu '$key'");
            return $default;
        }

        $default_value = $default ?? self::$settings_config[$key];
        return get_option($key, $default_value);
    }

    /**
     * Prépare les données pour les previews JavaScript
     */
    public static function prepare_preview_data($settings) {
        return [
            // Entreprise
            'company_phone_manual' => $settings['pdf_builder_company_phone_manual'] ?? '',
            'company_siret' => $settings['pdf_builder_company_siret'] ?? '',
            'company_vat' => $settings['pdf_builder_company_vat'] ?? '',
            'company_rcs' => $settings['pdf_builder_company_rcs'] ?? '',
            'company_capital' => $settings['pdf_builder_company_capital'] ?? '',

            // PDF
            'pdf_quality' => $settings['pdf_builder_pdf_quality'] ?? 'high',
            'default_format' => $settings['pdf_builder_default_format'] ?? 'A4',
            'default_orientation' => $settings['pdf_builder_default_orientation'] ?? 'portrait',

            // Cache
            'cache_enabled' => $settings['pdf_builder_cache_enabled'] ?? false,
            'cache_ttl' => $settings['pdf_builder_cache_ttl'] ?? 3600,
            'cache_compression' => $settings['pdf_builder_cache_compression'] ?? true,

            // Templates
            'template_library_enabled' => $settings['pdf_builder_template_library_enabled'] ?? true,

            // Développeur
            'developer_enabled' => $settings['pdf_builder_developer_enabled'] ?? false,
            'debug_mode' => $settings['pdf_builder_debug_mode'] ?? false,

            // Canvas
            'canvas_width' => $settings['pdf_builder_canvas_width'] ?? 794,
            'canvas_height' => $settings['pdf_builder_canvas_height'] ?? 1123,
            'canvas_settings' => $settings['pdf_builder_canvas_settings'] ?? [],
        ];
    }
}

// Debug: Page loaded
if (defined('WP_DEBUG') && WP_DEBUG) {

}

// Initialize
$notices = [];

// Charger TOUS les paramètres sauvegardés de manière centralisée
$all_settings = PDF_Builder_Settings_Loader::load_all_settings();

// IMPORTANT: Pour éviter les incohérences, utiliser la même source que les sauvegardes AJAX
$settings = get_option('pdf_builder_settings', []);
$canvas_settings = $all_settings['pdf_builder_canvas_settings'];

// Préparer les données pour les previews
$preview_data = PDF_Builder_Settings_Loader::prepare_preview_data($all_settings);

// Variables pour la rétrocompatibilité (utilisées dans les templates)
$company_phone_manual = $preview_data['company_phone_manual'];
$company_siret = $preview_data['company_siret'];
$company_vat = $preview_data['company_vat'];
$company_rcs = $preview_data['company_rcs'];
$company_capital = $preview_data['company_capital'];
$pdf_quality = $preview_data['pdf_quality'];
$default_format = $preview_data['default_format'];
$default_orientation = $preview_data['default_orientation'];

// Passer les données sauvegardées au JavaScript pour les previews
?>
<script>
// Données centralisées chargées depuis la base de données
// IMPORTANT: Charger depuis la même source que les sauvegardes AJAX pour éviter les incohérences
window.pdfBuilderSavedSettings = <?php echo json_encode(get_option('pdf_builder_settings', [])); ?>;
window.pdfBuilderCanvasSettings = {};

// Paramètres de debug pour le JavaScript
window.pdfBuilderDebugSettings = {
    javascript: false,
    javascript_verbose: false,
    ajax: false,
    performance: false,
    settings_page: true,  // ACTIVÉ pour déboguer la persistance des onglets
    pdf_editor: false,
    database: false
};

// Variables AJAX globales pour les requêtes AJAX
window.pdfBuilderAjax = {
    nonce: '',
    ajaxurl: ''
};

// Nonce spécifique pour les paramètres
window.pdfBuilderSettingsNonce = '';

// Paramètres de notifications pour le JavaScript
window.pdfBuilderNotifications = {
    settings: {
        enabled: true,
        position: 'top-right',
        duration: 5000,
        max_notifications: 5,
        animation: 'slide',
        sound_enabled: false,
        types: {
            success: {icon: '✅', color: '#28a745', bg: '#d4edda'},
            error: {icon: '❌', color: '#dc3545', bg: '#f8d7da'},
            warning: {icon: '⚠️', color: '#ffc107', bg: '#fff3cd'},
            info: {icon: 'ℹ️', color: '#17a2b8', bg: '#d1ecf1'}
        }
    },
    ajax_url: '',
    nonce: '',
    strings: {
        close: 'Fermer',
        dismiss_all: 'Tout fermer'
    }
};

// Système centralisé d'initialisation des previews avec données BDD
window.PDF_Builder_Preview_Manager = {
    /**
     * Initialise toutes les previews avec les données sauvegardées
     */
    initializeAllPreviews: function() {
        pdfBuilderDebug('Initializing all previews with saved data');

        // Initialiser les previews individuelles
        this.initializeCompanyPreview();
        this.initializePDFPreview();
        this.initializeCachePreview();
        this.initializeTemplatesPreview();
        this.initializeDeveloperPreview();
        this.initializeCanvasPreviews();

        pdfBuilderDebug('All previews initialized with saved data');
    },

    /**
     * Preview des informations entreprise
     */
    initializeCompanyPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les champs de preview entreprise
        const phoneField = document.querySelector('.company-phone-preview');
        if (phoneField && data.company_phone_manual) {
            phoneField.textContent = data.company_phone_manual;
        }

        const siretField = document.querySelector('.company-siret-preview');
        if (siretField && data.company_siret) {
            siretField.textContent = data.company_siret;
        }

        const vatField = document.querySelector('.company-vat-preview');
        if (vatField && data.company_vat) {
            vatField.textContent = data.company_vat;
        }

        const rcsField = document.querySelector('.company-rcs-preview');
        if (rcsField && data.company_rcs) {
            rcsField.textContent = data.company_rcs;
        }

        const capitalField = document.querySelector('.company-capital-preview');
        if (capitalField && data.company_capital) {
            capitalField.textContent = data.company_capital + ' €';
        }

        pdfBuilderDebug('Company preview initialized');
    },

    /**
     * Preview des paramètres PDF
     */
    initializePDFPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les champs de preview PDF
        const qualityField = document.querySelector('.pdf-quality-preview');
        if (qualityField && data.pdf_quality) {
            qualityField.textContent = data.pdf_quality;
        }

        const formatField = document.querySelector('.pdf-format-preview');
        if (formatField && data.default_format) {
            formatField.textContent = data.default_format;
        }

        const orientationField = document.querySelector('.pdf-orientation-preview');
        if (orientationField && data.default_orientation) {
            orientationField.textContent = data.default_orientation;
        }

        pdfBuilderDebug('PDF preview initialized');
    },

    /**
     * Preview des paramètres cache
     */
    initializeCachePreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les indicateurs de cache
        const cacheEnabledIndicator = document.querySelector('.cache-enabled-indicator');
        if (cacheEnabledIndicator) {
            // Changer la couleur selon l'état du cache
            cacheEnabledIndicator.style.color = data.cache_enabled ? '#28a745' : '#dc3545';
            cacheEnabledIndicator.textContent = data.cache_enabled ? 'Activé' : 'Désactivé';
        }

        const cacheTtlField = document.querySelector('.cache-ttl-preview');
        if (cacheTtlField && data.cache_ttl) {
            cacheTtlField.textContent = data.cache_ttl + ' secondes';
        }

        const cacheCompressionField = document.querySelector('.cache-compression-preview');
        if (cacheCompressionField) {
            cacheCompressionField.textContent = data.cache_compression ? 'Activée' : 'Désactivée';
        }

        pdfBuilderDebug('Cache preview initialized');
    },

    /**
     * Preview des paramètres templates
     */
    initializeTemplatesPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour l'indicateur de la bibliothèque de templates
        const templateLibraryIndicator = document.querySelector('.template-library-indicator');
        if (templateLibraryIndicator) {
            // Convertir explicitement en boolean (0, "0", false deviennent false)
            const isEnabled = Boolean(data.template_library_enabled && data.template_library_enabled !== "0" && data.template_library_enabled !== 0);

            templateLibraryIndicator.style.background = isEnabled ? '#28a745' : '#dc3545';
            templateLibraryIndicator.textContent = isEnabled ? 'ACTIF' : 'INACTIF';
        }

        pdfBuilderDebug('Templates preview initialized');
    },

    /**
     * Preview des paramètres développeur
     */
    initializeDeveloperPreview: function() {
        if (!window.pdfBuilderSavedSettings) return;

        const data = window.pdfBuilderSavedSettings;

        // Mettre à jour les indicateurs développeur
        const debugModeIndicator = document.querySelector('.debug-mode-indicator');
        if (debugModeIndicator) {
            // Changer la couleur selon l'état du mode debug
            debugModeIndicator.style.color = data.debug_mode ? '#28a745' : '#dc3545';
            debugModeIndicator.textContent = data.debug_mode ? 'Activé' : 'Désactivé';
        }

        const developerEnabledIndicator = document.querySelector('.developer-enabled-indicator');
        if (developerEnabledIndicator) {
            // Changer la couleur selon l'état du mode développeur
            developerEnabledIndicator.style.color = data.developer_enabled ? '#28a745' : '#dc3545';
            developerEnabledIndicator.textContent = data.developer_enabled ? 'Activé' : 'Désactivé';
        }

        // Mettre à jour l'indicateur de statut du mode développeur
        if (window.updateDeveloperStatusIndicator) {
            window.updateDeveloperStatusIndicator();
        }

        pdfBuilderDebug('Developer preview initialized');
    },

    /**
     * Initialise les previews canvas avec les données sauvegardées
     */
    initializeCanvasPreviews: function() {
        if (!window.pdfBuilderCanvasSettings) return;

        pdfBuilderDebug('Initializing canvas previews with saved settings');

        // Délai pour s'assurer que le DOM est prêt
        setTimeout(() => {
            try {
                // Initialiser les previews individuelles des cartes canvas
                if (typeof updateDimensionsCardPreview === 'function') {
                    updateDimensionsCardPreview();
                }
                if (typeof updateApparenceCardPreview === 'function') {
                    updateApparenceCardPreview();
                }
                if (typeof updateInteractionsCardPreview === 'function') {
                    updateInteractionsCardPreview();
                }
                if (typeof updatePerformanceCardPreview === 'function') {
                    updatePerformanceCardPreview();
                }
                if (typeof updateZoomCardPreview === 'function') {
                    updateZoomCardPreview();
                }
                if (typeof updateGridCardPreview === 'function') {
                    updateGridCardPreview();
                }
                if (typeof updateAutosaveCardPreview === 'function') {
                    updateAutosaveCardPreview();
                }

                pdfBuilderDebug('Canvas previews initialized successfully');
            } catch (error) {
                pdfBuilderError('Error initializing canvas previews:', error);
            }
        }, 100);
    }
};
</script>
<?php

// Log ALL POST data at the beginning
if (!empty($_POST)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        
    }
} else {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        
    }
}

// Process form - handle both regular form submissions and AJAX requests
if ((isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) || (isset($_POST['action']) && $_POST['action'] === 'pdf_builder_save_settings')) {
    if ($is_ajax) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: AJAX save request detected');
            error_log('PDF Builder: POST data count: ' . count($_POST));
            error_log('PDF Builder: Current tab: ' . ($_POST['current_tab'] ?? 'not set'));
        }
    }
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PDF Builder: Processing save request');
        error_log('PDF Builder: Action: ' . ($_POST['action'] ?? 'not set'));
        error_log('PDF Builder: Current tab: ' . ($_POST['current_tab'] ?? 'not set'));
        error_log('PDF Builder: Developer enabled: ' . ($_POST['pdf_builder_developer_enabled'] ?? 'not set'));
    }
    // Check nonce from POST data or AJAX header
    $nonce_to_check = $_POST['pdf_builder_settings_nonce'] ?? $_SERVER['HTTP_X_WP_NONCE'] ?? '';
    if (wp_verify_nonce($nonce_to_check, 'pdf_builder_settings')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }
        // Check for max_input_vars limit
        $max_input_vars = ini_get('max_input_vars');
        if ($max_input_vars && count($_POST) >= $max_input_vars) {
            $notices[] = '<div class="notice notice-error"><p><strong>⚠️</strong> Trop de paramètres soumis (' . count($_POST) . '). Limite PHP max_input_vars: ' . $max_input_vars . '. Certains paramètres n\'ont pas été sauvegardés.</p></div>';
        }

        // Debug: Log the received debug fields
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder: Received debug fields - pdf_builder_debug_pdf_editor: ' . (isset($_POST['pdf_builder_debug_pdf_editor']) ? $_POST['pdf_builder_debug_pdf_editor'] : 'NOT_SET'));
            error_log('PDF Builder: Received debug fields - pdf_builder_debug_settings_page: ' . (isset($_POST['pdf_builder_debug_settings_page']) ? $_POST['pdf_builder_debug_settings_page'] : 'NOT_SET'));
        }
        // Collect all form data from all tabs - comprehensive field processing
        // Process ALL fields dynamically since floating button sends data from all tabs
        $to_save = [];

        // Define field sanitization rules
        $field_rules = [
            // Text fields that need sanitization
            'text_fields' => [
                'company_phone_manual', 'company_siret', 'company_vat', 'company_rcs', 'company_capital',
                'pdf_quality', 'default_format', 'default_orientation', 'default_template',
                'systeme_auto_backup_frequency', 'pdf_builder_developer_password',
                'pdf_builder_log_level', 'memory_limit', 'debug_mode', 'max_template_size', 'max_execution_time'
            ],
            // Integer fields
            'int_fields' => [
                'cache_max_size', 'cache_ttl', 'systeme_backup_retention',
                'pdf_builder_log_file_size', 'pdf_builder_log_retention'
            ],
            // Boolean/checkbox fields (isset check)
            'bool_fields' => [
                'pdf_builder_cache_enabled', 'cache_compression', 'cache_auto_cleanup', 'performance_auto_optimization',
                'systeme_auto_maintenance', 'systeme_auto_backup', 'template_library_enabled',
                'pdf_builder_developer_enabled', 'pdf_builder_debug_php_errors', 'pdf_builder_debug_javascript',
                'pdf_builder_debug_javascript_verbose', 'pdf_builder_debug_ajax', 'pdf_builder_debug_performance',
                'pdf_builder_debug_database', 'pdf_builder_debug_pdf_editor', 'pdf_builder_debug_settings_page', 'pdf_builder_force_https', 'pdf_builder_license_test_mode_enabled'
            ],
            // Array fields
            'array_fields' => ['order_status_templates']
        ];

        // Process all POST data dynamically
        foreach ($_POST as $key => $value) {
            // Skip WordPress internal fields and security fields
            if (in_array($key, ['submit', 'pdf_builder_settings_nonce', 'action', 'tab', 'canvas_settings', '_wp_http_referer'])) {
                continue;
            }

            if (in_array($key, $field_rules['text_fields'])) {
                $to_save[$key] = sanitize_text_field($value ?? '');
            } elseif (in_array($key, $field_rules['int_fields'])) {
                $to_save[$key] = intval($value ?? 0);
            } elseif (in_array($key, $field_rules['bool_fields'])) {
                $to_save[$key] = isset($_POST[$key]);
            } elseif (in_array($key, $field_rules['array_fields'])) {
                if (is_array($value)) {
                    $to_save[$key] = array_map('sanitize_text_field', $value);
                } else {
                    $to_save[$key] = [];
                }
            } else {
                // For any other fields not explicitly defined, sanitize as text
                $to_save[$key] = sanitize_text_field($value ?? '');
            }
        }

        // Ensure all expected fields have defaults if not provided
        $defaults = [
            'company_phone_manual' => '',
            'company_siret' => '',
            'company_vat' => '',
            'company_rcs' => '',
            'company_capital' => '',
            'pdf_quality' => 'high',
            'default_format' => 'A4',
            'default_orientation' => 'portrait',
            'cache_enabled' => false,
            'cache_compression' => true,
            'cache_auto_cleanup' => true,
            'cache_max_size' => 100,
            'cache_ttl' => 3600,
            'performance_auto_optimization' => false,
            'systeme_auto_maintenance' => true,
            'systeme_auto_backup' => true,
            'systeme_auto_backup_frequency' => 'daily',
            'systeme_backup_retention' => 30,
            'default_template' => 'blank',
            'template_library_enabled' => true,
            'pdf_builder_developer_enabled' => false,
            'pdf_builder_developer_password' => '',
            'pdf_builder_debug_php_errors' => false,
            'pdf_builder_debug_javascript' => false,
            'pdf_builder_debug_javascript_verbose' => false,
            'pdf_builder_debug_ajax' => false,
            'pdf_builder_debug_pdf_editor' => false,
            'pdf_builder_debug_settings_page' => false,
            'pdf_builder_debug_performance' => false,
            'pdf_builder_debug_database' => false,
            'pdf_builder_log_level' => 'info',
            'pdf_builder_log_file_size' => 10,
            'pdf_builder_log_retention' => 30,
            'pdf_builder_force_https' => false,
            'pdf_builder_license_test_mode_enabled' => false,
            'order_status_templates' => [],
            'debug_mode' => false,
            'max_template_size' => 52428800,
            'max_execution_time' => 300,
            'memory_limit' => '256M'
        ];

        // Apply defaults for missing fields
        foreach ($defaults as $field => $default_value) {
            if (!isset($to_save[$field])) {
                $to_save[$field] = $default_value;
            }
        }
        $new_settings = array_merge($settings, $to_save);
        // Check if settings actually changed - use serialize for deep comparison
        $settings_changed = serialize($new_settings) !== serialize($settings);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }

        $result = update_option('pdf_builder_settings', $new_settings);

        // Sauvegarder aussi les paramètres canvas si fournis (pour le bouton flottant)
        if (isset($_POST['canvas_settings']) && !empty($_POST['canvas_settings'])) {
            $canvas_settings = json_decode(stripslashes($_POST['canvas_settings']), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($canvas_settings)) {
                update_option('pdf_builder_canvas_settings', $canvas_settings);
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('PDF Builder: Canvas settings saved via floating button: ' . count($canvas_settings) . ' settings');
                }
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('PDF Builder: Invalid canvas settings JSON: ' . json_last_error_msg());
                }
            }
        }

        // Also save canvas settings to the main settings array for consistency
        if (isset($_POST['canvas_settings']) && !empty($_POST['canvas_settings'])) {
            $canvas_settings = json_decode(stripslashes($_POST['canvas_settings']), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($canvas_settings)) {
                $to_save['canvas_settings'] = $canvas_settings;
            }
        }

        try {
            // Debug: Always log the result for troubleshooting
            if (defined('WP_DEBUG') && WP_DEBUG) {

            }

            // Simplified success logic: if no exception was thrown, consider it successful
            if ($is_ajax) {
                send_ajax_response(true, 'Paramètres enregistrés avec succès.');
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✅</strong> Paramètres enregistrés avec succès.</p></div>';
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {

            }
            if ($is_ajax) {
                send_ajax_response(false, 'Erreur lors de la sauvegarde des paramètres: ' . $e->getMessage());
            } else {
                $notices[] = '<div class="notice notice-error"><p><strong>❌</strong> Erreur lors de la sauvegarde des paramètres: ' . esc_html($e->getMessage()) . '</p></div>';
            }
        }
        $settings = get_option('pdf_builder_settings', []);
        // Also update the standalone options so that other parts of the plugin
        // which read from individual options get updated when the non-AJAX form is used

        // Company information
        update_option('pdf_builder_company_phone_manual', sanitize_text_field($_POST['company_phone_manual'] ?? ''));
        update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret'] ?? ''));
        update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat'] ?? ''));
        update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs'] ?? ''));
        update_option('pdf_builder_company_capital', sanitize_text_field($_POST['company_capital'] ?? ''));

        // PDF settings
        update_option('pdf_builder_pdf_quality', sanitize_text_field($_POST['pdf_quality'] ?? 'high'));
        update_option('pdf_builder_default_format', sanitize_text_field($_POST['default_format'] ?? 'A4'));
        update_option('pdf_builder_default_orientation', sanitize_text_field($_POST['default_orientation'] ?? 'portrait'));

        // Cache settings
        update_option('pdf_builder_cache_enabled', isset($_POST['pdf_builder_cache_enabled']) ? 1 : 0);
        update_option('pdf_builder_cache_compression', isset($_POST['cache_compression']) ? 1 : 0);
        update_option('pdf_builder_cache_auto_cleanup', isset($_POST['cache_auto_cleanup']) ? 1 : 0);
        update_option('pdf_builder_cache_max_size', intval($_POST['cache_max_size'] ?? 100));
        update_option('pdf_builder_cache_ttl', intval($_POST['cache_ttl'] ?? 3600));

        // System settings
        update_option('pdf_builder_performance_auto_optimization', isset($_POST['performance_auto_optimization']) ? 1 : 0);
        update_option('pdf_builder_auto_maintenance', isset($_POST['systeme_auto_maintenance']) ? 1 : 0);
        update_option('pdf_builder_auto_backup', isset($_POST['systeme_auto_backup']) ? 1 : 0);
        update_option('pdf_builder_auto_backup_frequency', sanitize_text_field($_POST['systeme_auto_backup_frequency'] ?? 'daily'));
        update_option('pdf_builder_backup_retention', intval($_POST['systeme_backup_retention'] ?? 30));

        // Template settings
        update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template'] ?? 'blank'));
        update_option('pdf_builder_template_library_enabled', isset($_POST['template_library_enabled']) ? 1 : 0);

        // Developer settings
        update_option('pdf_builder_developer_enabled', isset($_POST['pdf_builder_developer_enabled']) ? 1 : 0);
        update_option('pdf_builder_developer_password', sanitize_text_field($_POST['pdf_builder_developer_password'] ?? ''));
        update_option('pdf_builder_debug_php_errors', isset($_POST['pdf_builder_debug_php_errors']) ? 1 : 0);
        update_option('pdf_builder_debug_javascript', isset($_POST['pdf_builder_debug_javascript']) ? 1 : 0);
        update_option('pdf_builder_debug_javascript_verbose', isset($_POST['pdf_builder_debug_javascript_verbose']) ? 1 : 0);
        update_option('pdf_builder_debug_ajax', isset($_POST['pdf_builder_debug_ajax']) ? 1 : 0);
        update_option('pdf_builder_debug_pdf_editor', isset($_POST['pdf_builder_debug_pdf_editor']) ? 1 : 0);
        update_option('pdf_builder_debug_settings_page', isset($_POST['pdf_builder_debug_settings_page']) ? 1 : 0);
        update_option('pdf_builder_debug_performance', isset($_POST['pdf_builder_debug_performance']) ? 1 : 0);
        update_option('pdf_builder_debug_database', isset($_POST['pdf_builder_debug_database']) ? 1 : 0);
        update_option('pdf_builder_log_level', sanitize_text_field($_POST['pdf_builder_log_level'] ?? 'info'));
        update_option('pdf_builder_log_file_size', intval($_POST['pdf_builder_log_file_size'] ?? 10));
        update_option('pdf_builder_log_retention', intval($_POST['pdf_builder_log_retention'] ?? 30));
        update_option('pdf_builder_force_https', isset($_POST['pdf_builder_force_https']) ? 1 : 0);

        // License settings
        update_option('pdf_builder_license_test_mode_enabled', isset($_POST['pdf_builder_license_test_mode_enabled']) ? 1 : 0);

        // Templates mapping
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            update_option('pdf_builder_order_status_templates', array_map('sanitize_text_field', $_POST['order_status_templates']));
        }

        // Build settings array from individual options for template compatibility
        $settings = [
            // Company info
            'pdf_builder_company_phone_manual' => get_option('pdf_builder_company_phone_manual', ''),
            'pdf_builder_company_siret' => get_option('pdf_builder_company_siret', ''),
            'pdf_builder_company_vat' => get_option('pdf_builder_company_vat', ''),
            'pdf_builder_company_rcs' => get_option('pdf_builder_company_rcs', ''),
            'pdf_builder_company_capital' => get_option('pdf_builder_company_capital', ''),

            // PDF settings
            'pdf_builder_pdf_quality' => get_option('pdf_builder_pdf_quality', 'high'),
            'pdf_builder_default_format' => get_option('pdf_builder_default_format', 'A4'),
            'pdf_builder_default_orientation' => get_option('pdf_builder_default_orientation', 'portrait'),

            // Cache settings
            'pdf_builder_cache_enabled' => get_option('pdf_builder_cache_enabled', 1),
            'cache_compression' => get_option('pdf_builder_cache_compression', 0),
            'cache_auto_cleanup' => get_option('pdf_builder_cache_auto_cleanup', 0),
            'cache_max_size' => get_option('pdf_builder_cache_max_size', 100),
            'cache_ttl' => get_option('pdf_builder_cache_ttl', 3600),

            // System settings
            'performance_auto_optimization' => get_option('pdf_builder_performance_auto_optimization', 0),
            'pdf_builder_auto_maintenance' => get_option('pdf_builder_auto_maintenance', 1),
            'pdf_builder_auto_backup' => get_option('pdf_builder_auto_backup', 1),
            'systeme_auto_backup_frequency' => get_option('pdf_builder_auto_backup_frequency', 'daily'),
            'systeme_backup_retention' => get_option('pdf_builder_backup_retention', 30),

            // Template settings
            'default_template' => get_option('pdf_builder_default_template', 'blank'),
            'template_library_enabled' => get_option('pdf_builder_template_library_enabled', 0),

            // Developer settings
            'pdf_builder_developer_enabled' => get_option('pdf_builder_developer_enabled', 0),
            'pdf_builder_developer_password' => get_option('pdf_builder_developer_password', ''),
            'pdf_builder_debug_php_errors' => get_option('pdf_builder_debug_php_errors', 0),
            'pdf_builder_debug_javascript' => get_option('pdf_builder_debug_javascript', 0),
            'pdf_builder_debug_javascript_verbose' => get_option('pdf_builder_debug_javascript_verbose', 0),
            'pdf_builder_debug_ajax' => get_option('pdf_builder_debug_ajax', 0),
            'pdf_builder_debug_pdf_editor' => get_option('pdf_builder_debug_pdf_editor', 0),
            'pdf_builder_debug_settings_page' => get_option('pdf_builder_debug_settings_page', 0),
            'pdf_builder_debug_performance' => get_option('pdf_builder_debug_performance', 0),
            'pdf_builder_debug_database' => get_option('pdf_builder_debug_database', 0),
            'pdf_builder_log_level' => get_option('pdf_builder_log_level', 'info'),
            'pdf_builder_log_file_size' => get_option('pdf_builder_log_file_size', 10),
            'pdf_builder_log_retention' => get_option('pdf_builder_log_retention', 30),
            'pdf_builder_force_https' => get_option('pdf_builder_force_https', 0),

            // License settings
            'pdf_builder_license_test_mode_enabled' => get_option('pdf_builder_license_test_mode_enabled', 0),
        ];
    } else {
        $notices[] = '<div class="notice notice-error"><p><strong>❌</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
    }
}

// Handle cache clear
if (
    isset($_POST['clear_cache']) &&
    (isset($_POST['pdf_builder_clear_cache_nonce_performance']) ||
    isset($_POST['pdf_builder_clear_cache_nonce_maintenance']))
) {
    $nonce_verified = false;
    if (isset($_POST['pdf_builder_clear_cache_nonce_performance'])) {
        $nonce_verified = wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce_performance'], 'pdf_builder_clear_cache_performance');
    } elseif (isset($_POST['pdf_builder_clear_cache_nonce_maintenance'])) {
        $nonce_verified = wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce_maintenance'], 'pdf_builder_clear_cache_maintenance');
    }

    if ($nonce_verified) {
        // Clear transients and cache
        delete_transient('pdf_builder_cache');
        delete_transient('pdf_builder_templates');
        delete_transient('pdf_builder_elements');
        // Clear WP object cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        if ($is_ajax) {
            send_ajax_response(true, 'Cache vidé avec succès.');
        } else {
            $notices[] = '<div class="notice notice-success"><p><strong>✅</strong> Cache vidé avec succès.</p></div>';
        }
    }
}

// Handle other form submissions (moved to individual tab files for better organization)

// Main HTML structure
?>
<div class="wrap">
    <div class="pdf-builder-header">
        <h1><?php _e('⚙️ PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>
    </div>

    <?php foreach ($notices as $notice) {
        echo $notice;
    } ?>
    <!-- Tabs Navigation -->
    <div class="nav-tab-wrapper wp-clearfix">
        <div class="mobile-menu-toggle">
            <button class="mobile-menu-button" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            <span class="current-tab-text">Général</span>
        </div>
        <div class="nav-tabs-container">
            <a href="#general" class="nav-tab" data-tab="general">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text">Général</span>
            </a>
            <a href="#licence" class="nav-tab" data-tab="licence">
                <span class="tab-icon">🔑</span>
                <span class="tab-text">Licence</span>
            </a>
            <a href="#systeme" class="nav-tab" data-tab="systeme">
                <span class="tab-icon">🖥️</span>
                <span class="tab-text">Système</span>
            </a>
            <a href="#acces" class="nav-tab" data-tab="acces">
                <span class="tab-icon">🔐</span>
                <span class="tab-text">Accès</span>
            </a>
            <a href="#securite" class="nav-tab" data-tab="securite">
                <span class="tab-icon">🛡️</span>
                <span class="tab-text">Sécurité & Conformité</span>
            </a>
            <a href="#pdf" class="nav-tab" data-tab="pdf">
                <span class="tab-icon">📄</span>
                <span class="tab-text">Configuration PDF</span>
            </a>
            <a href="#contenu" class="nav-tab" data-tab="contenu">
                <span class="tab-icon">🎨</span>
                <span class="tab-text">Contenu & Design</span>
            </a>
            <a href="#templates" class="nav-tab" data-tab="templates">
                <span class="tab-icon">📋</span>
                <span class="tab-text">Templates par statut</span>
            </a>
            <a href="#developpeur" class="nav-tab" data-tab="developpeur">
                <span class="tab-icon">🛠️</span>
                <span class="tab-text">Développeur</span>
            </a>
        </div>
    </div>
<?php

// Canvas settings are now loaded in settings-canvas-params.php
?>

    <!-- Tab Content Containers -->
    <div id="general" class="tab-content">
        <?php require_once 'settings-general.php'; ?>
    </div>

    <div id="licence" class="tab-content">
        <?php require_once 'settings-licence.php'; ?>
    </div>

    <div id="systeme" class="tab-content">
        <?php require_once 'settings-systeme.php'; ?>
    </div>

    <div id="acces" class="tab-content">
        <?php require_once 'settings-acces.php'; ?>
    </div>

    <div id="securite" class="tab-content">
        <?php require_once 'settings-securite.php'; ?>
    </div>

    <div id="pdf" class="tab-content">
        <?php require_once 'settings-pdf.php'; ?>
    </div>

    <div id="contenu" class="tab-content">
        <?php require_once 'settings-contenu.php'; ?>
    </div>

    <div id="templates" class="tab-content">
        <?php require_once 'settings-templates.php'; ?>
    </div>

    <div id="developpeur" class="tab-content">
        <?php require_once 'settings-developpeur.php'; ?>
    </div>

<!-- Bouton flottant de sauvegarde universelle -->
<div id="floating-save-container">
    <button id="floating-save-btn" class="floating-save-btn" type="button">
        <span class="dashicons dashicons-cloud-upload"></span>
        <span class="btn-text">Enregistrer Tout</span>
    </button>
</div>

</div>

<!-- Modals - COMPLETEMENT HORS du conteneur principal -->
<?php require_once 'settings-modals.php'; ?>

<!-- Bouton de secours sans JavaScript -->
<noscript>
    <div style="position: fixed; bottom: 80px; right: 20px; z-index: 999999; background: #fff; border: 2px solid #007cba; border-radius: 8px; padding: 10px;">
        <strong>💾 Sauvegarde manuelle</strong><br>
        <small>JavaScript désactivé - Utilisez les boutons de chaque onglet</small>
    </div>
</noscript>

<script>
// Update zoom card preview
window.updateZoomCardPreview = function() {
    pdfBuilderDebug('updateZoomCardPreview called');
    try {
        // Try to get values from modal inputs first (real-time), then from settings
        const minZoomInput = document.getElementById("zoom_min");
        const maxZoomInput = document.getElementById("zoom_max");
        const defaultZoomInput = document.getElementById("zoom_default");
        const stepZoomInput = document.getElementById("zoom_step");

        const minZoom = minZoomInput ? parseInt(minZoomInput.value) : (window.pdfBuilderCanvasSettings?.min_zoom || window.pdfBuilderCanvasSettings?.default_zoom_min || 10);
        const maxZoom = maxZoomInput ? parseInt(maxZoomInput.value) : (window.pdfBuilderCanvasSettings?.max_zoom || window.pdfBuilderCanvasSettings?.default_zoom_max || 500);
        const defaultZoom = defaultZoomInput ? parseInt(defaultZoomInput.value) : (window.pdfBuilderCanvasSettings?.default_zoom || 100);
        const stepZoom = stepZoomInput ? parseInt(stepZoomInput.value) : (window.pdfBuilderCanvasSettings?.zoom_step || 25);

        pdfBuilderDebug('zoom values - min:', minZoom, 'max:', maxZoom, 'default:', defaultZoom, 'step:', stepZoom);

        // Update zoom level display
        const zoomLevel = document.querySelector('.zoom-level');
        if (zoomLevel) {
            zoomLevel.textContent = defaultZoom + '%';
            pdfBuilderDebug('Updated zoom level to:', defaultZoom + '%');
        } else {
            pdfBuilderDebug('zoomLevel element not found');
        }

        // Update zoom info
        const zoomInfo = document.querySelector('.zoom-info');
        if (zoomInfo) {
            zoomInfo.innerHTML = '<span>' + minZoom + '% - ' + maxZoom + '%</span><span>Pas: ' + stepZoom + '%</span>';
            pdfBuilderDebug('Updated zoom info');
        } else {
            pdfBuilderDebug('zoomInfo element not found');
        }

        pdfBuilderDebug('updateZoomCardPreview completed successfully');
    } catch (error) {
        pdfBuilderError('Error in updateZoomCardPreview:', error);
    }
};

// Tab switching functionality - VERSION AMÉLIORÉE avec persistance renforcée
function initializeTabs() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    // First, hide ALL tab contents
    contents.forEach(function(content) {
        content.classList.remove('active');
        content.style.display = 'none';
    });

    // Add click listeners to tabs
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: ===== TAB CLICK START =====');
                console.log('PDF Builder: Tab clicked - event:', e);
                console.log('PDF Builder: Tab element:', this);
                console.log('PDF Builder: Tab href:', this.getAttribute('href'));
            }

            e.preventDefault();
            
            const target = this.getAttribute('href').substring(1);
            
            // Switch to target tab
            setActiveTab(target);

            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: ===== TAB CLICK END =====');
            }
        });
    });

    // Fonction pour activer un onglet spécifique
    function setActiveTab(targetTab, saveToStorage = true) {
        // Remove active classes from all tabs and contents
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('nav-tab-active');
        });
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
        });

        // Find target tab and content
        const activeTab = document.querySelector('.nav-tab[href="#' + targetTab + '"]');
        const activeContent = document.getElementById(targetTab);

        if (activeTab && activeContent) {
            // Add active classes to target tab and content
            activeTab.classList.add('nav-tab-active');
            activeContent.classList.add('active');
            activeContent.style.display = 'block';

            // Update URL hash to match the active tab (without triggering scroll)
            if (window.location.hash.substring(1) !== targetTab) {
                history.replaceState(null, null, '#' + targetTab);
            }

                    // Save active tab to localStorage for persistence across reloads
                    if (saveToStorage) {
                        localStorage.setItem('pdf-builder-active-tab', targetTab);
                        // Also save timestamp for debugging
                        localStorage.setItem('pdf-builder-active-tab-time', Date.now());
                        if (window.pdfBuilderDebugSettings?.settings_page) {
                            console.log('PDF Builder: [SAVE] Tab saved to localStorage:', targetTab, 'at', new Date().toLocaleTimeString());
                        }
                    }            // Update canvas previews when switching to contenu tab
            if (targetTab === 'contenu') {
                if (window.pdfBuilderDebugSettings?.settings_page) {
                    console.log('PDF Builder: Switching to contenu tab, updating canvas previews');
                }
                try {
                    if (window.CanvasPreviewManager && typeof window.CanvasPreviewManager.updatePreviews === 'function') {
                        setTimeout(function() {
                            window.CanvasPreviewManager.updatePreviews('all');
                        }, 200);
                    } else if (window.updateCanvasPreviews) {
                        setTimeout(function() {
                            window.updateCanvasPreviews('all');
                        }, 200);
                    }
                    // Also initialize templates preview when switching to contenu tab
                    if (window.PDF_Builder_Preview_Manager && typeof window.PDF_Builder_Preview_Manager.initializeTemplatesPreview === 'function') {
                        setTimeout(function() {
                            window.PDF_Builder_Preview_Manager.initializeTemplatesPreview();
                        }, 200);
                    }
                } catch (error) {
                    if (window.pdfBuilderDebugSettings?.settings_page) {
                        console.error('PDF Builder: Error updating canvas previews on tab switch:', error);
                    }
                }
            }

            // Update mobile menu text
            const currentTabText = document.querySelector('.current-tab-text');
            if (currentTabText) {
                const tabText = activeTab.querySelector('.tab-text');
                if (tabText) {
                    currentTabText.textContent = tabText.textContent;
                }
            }

            // Update floating save button text
            updateFloatingSaveButtonText(targetTab);

            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: Tab switched to:', targetTab);
            }

            return true;
        } else {
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.error('PDF Builder: Could not find tab or content for target:', targetTab);
            }
            return false;
        }
    }

    // Exposer la fonction setActiveTab globalement
    window.setActiveTab = setActiveTab;

    // Déterminer l'onglet à activer au chargement
    const hash = window.location.hash.substring(1);
    let targetTab = 'general'; // Default tab
    
            // Priority order: localStorage > hash > default (pour rester sur l'onglet actif après rechargement)
            const savedTab = localStorage.getItem('pdf-builder-active-tab');
            if (savedTab) {
                // Validate that the saved tab corresponds to an existing tab
                const tabExists = document.querySelector('.nav-tab[href="#' + savedTab + '"]');
                if (tabExists) {
                    targetTab = savedTab;
                    if (window.pdfBuilderDebugSettings?.settings_page) {
                        console.log('PDF Builder: [LOAD] Using saved tab from localStorage:', targetTab, 'saved at', localStorage.getItem('pdf-builder-active-tab-time') ? new Date(parseInt(localStorage.getItem('pdf-builder-active-tab-time'))).toLocaleTimeString() : 'unknown');
                    }
                } else {
                    if (window.pdfBuilderDebugSettings?.settings_page) {
                        console.warn('PDF Builder: [LOAD] Saved tab not found:', savedTab, '- checking hash');
                    }
                }
            } else {
                if (window.pdfBuilderDebugSettings?.settings_page) {
                    console.log('PDF Builder: [LOAD] No saved tab in localStorage, will check hash or use default');
                }
            }    // Si pas de localStorage valide, essayer le hash (pour les liens directs)
    if (targetTab === 'general' && hash) {
        const tabExists = document.querySelector('.nav-tab[href="#' + hash + '"]');
        if (tabExists) {
            targetTab = hash;
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.log('PDF Builder: Using hash from URL:', targetTab);
            }
        } else {
            if (window.pdfBuilderDebugSettings?.settings_page) {
                console.warn('PDF Builder: Hash not found for tab:', hash, '- using default');
            }
        }
    }

    // Activer l'onglet déterminé (sans sauvegarder dans localStorage pour éviter la boucle)
    setActiveTab(targetTab, false);

    // Écouter les changements de hash dans l'URL (navigation backwards/forwards)
    window.addEventListener('hashchange', function() {
        const newHash = window.location.hash.substring(1);
        if (newHash && newHash !== targetTab) {
            const tabExists = document.querySelector('.nav-tab[href="#' + newHash + '"]');
            if (tabExists) {
                targetTab = newHash;
                setActiveTab(targetTab);
            }
        }
    });

    // Log final state after initialization
    if (window.pdfBuilderDebugSettings?.settings_page) {
        console.log('PDF Builder: ===== INITIALIZATION COMPLETE =====');
        console.log('PDF Builder: Active tab after init:', targetTab);
        console.log('PDF Builder: Active content after init:', document.querySelector('.tab-content.active'));
        console.log('PDF Builder: Hash in URL:', window.location.hash);
        console.log('PDF Builder: Saved in localStorage:', localStorage.getItem('pdf-builder-active-tab'));
        console.log('PDF Builder: ===== END INITIALIZATION =====');
    }

    // Debug: Force log current state every 5 seconds
    if (window.pdfBuilderDebugSettings?.settings_page) {
        setInterval(() => {
            console.log('PDF Builder: [DEBUG] Current state - Active tab:', document.querySelector('.nav-tab-active')?.getAttribute('href')?.substring(1));
            console.log('PDF Builder: [DEBUG] Current state - localStorage:', localStorage.getItem('pdf-builder-active-tab'));
            console.log('PDF Builder: [DEBUG] Current state - URL hash:', window.location.hash);
        }, 5000);
    }
}

if (window.pdfBuilderDebugSettings?.javascript) {
    console.log('PDF Builder: About to add DOMContentLoaded listener');
    console.log('PDF Builder: Document readyState:', document.readyState);
    console.log('PDF Builder: Window loaded:', window.loaded);
}

// Make initializeTabs globally accessible
window.initializeTabs = initializeTabs;

function updateSecurityStatusIndicators() {
    // Mettre à jour l'indicateur de sécurité (enable_logging)
    const enableLoggingCheckbox = document.getElementById('enable_logging');
    const securityStatus = document.getElementById('security-status-indicator');
    if (enableLoggingCheckbox && securityStatus) {
        const isActive = enableLoggingCheckbox.checked;
        securityStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        securityStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Mettre à jour l'indicateur RGPD (gdpr_enabled)
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    const rgpdStatus = document.getElementById('rgpd-status-indicator');
    if (gdprEnabledCheckbox && rgpdStatus) {
        const isActive = gdprEnabledCheckbox.checked;
        rgpdStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        rgpdStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Mettre à jour les indicateurs système
    updateSystemStatusIndicators();
}

// Fonction pour mettre à jour les indicateurs des templates assignés
function updateTemplateStatusIndicators() {
    // Parcourir tous les selects de templates
    const templateSelects = document.querySelectorAll('.template-select');
    
    templateSelects.forEach(select => {
        const selectValue = select.value;
        const selectId = select.id;
        
        // Trouver le conteneur parent (.template-status-card)
        const card = select.closest('.template-status-card');
        if (!card) return;
        
        // Trouver la section preview dans cette card
        const previewDiv = card.querySelector('.template-preview');
        if (!previewDiv) return;
        
        // Créer ou mettre à jour l'indicateur
        if (selectValue && selectValue !== '') {
            // Template assigné - récupérer le texte de l'option sélectionnée
            const selectedOption = select.querySelector('option[value="' + selectValue.replace(/"/g, '\\"') + '"]');
            const templateName = selectedOption ? selectedOption.textContent.trim() : 'Template inconnu';
            
            previewDiv.innerHTML = '<p class="current-template"><strong>Assigné :</strong> ' + templateName.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '<span class="assigned-badge">✅</span></p>';
        } else {
            // Aucun template assigné
            previewDiv.innerHTML = '<p class="no-template">Aucun template assigné</p>';
        }
    });
}

// Fonction pour mettre à jour l'indicateur de bibliothèque de templates
function updateTemplateLibraryIndicator() {
    const templateLibraryCheckbox = document.getElementById('template_library_enabled');
    const indicator = document.getElementById('template-library-indicator');
    
    if (templateLibraryCheckbox && indicator) {
        const isActive = templateLibraryCheckbox.checked;
        indicator.textContent = isActive ? 'ACTIF' : 'INACTIF';
        indicator.style.background = isActive ? '#28a745' : '#dc3545';
    }
}

// Fonction pour mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Système
function updateSystemStatusIndicators() {
    // Indicateur Cache & Performance
    const cacheEnabledCheckbox = document.getElementById('general_cache_enabled');
    const cacheStatus = document.querySelector('.cache-performance-status');
    if (cacheEnabledCheckbox && cacheStatus) {
        const isActive = cacheEnabledCheckbox.checked;
        cacheStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        cacheStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Indicateur Maintenance automatique
    const maintenanceCheckbox = document.getElementById('systeme_auto_maintenance');
    const maintenanceStatus = document.querySelector('.maintenance-status');
    if (maintenanceCheckbox && maintenanceStatus) {
        const isActive = maintenanceCheckbox.checked;
        maintenanceStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        maintenanceStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }

    // Indicateur Sauvegarde automatique
    const backupCheckbox = document.getElementById('systeme_auto_backup');
    const backupStatus = document.querySelector('.backup-status');
    if (backupCheckbox && backupStatus) {
        const isActive = backupCheckbox.checked;
        backupStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
        backupStatus.style.background = isActive ? '#28a745' : '#dc3545';
    }
}

// Fonction pour gérer l'activation/désactivation des contrôles RGPD
function toggleRGPDControls() {
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    const isEnabled = gdprEnabledCheckbox ? gdprEnabledCheckbox.checked : false;

    // Liste des contrôles à désactiver/activer
    const controlsToToggle = [
        'gdpr_consent_required',
        'gdpr_data_retention',
        'gdpr_audit_enabled',
        'gdpr_encryption_enabled',
        'gdpr_consent_analytics',
        'gdpr_consent_templates',
        'gdpr_consent_marketing',
        'export-format',
        'export-my-data',
        'delete-my-data',
        'view-consent-status',
        'refresh-audit-log',
        'export-audit-log'
    ];

    // Désactiver/activer chaque contrôle
    controlsToToggle.forEach(controlId => {
        const control = document.getElementById(controlId);
        if (control) {
            control.disabled = !isEnabled;

            // Ajouter/enlever une classe CSS pour le style visuel
            if (isEnabled) {
                control.classList.remove('gdpr-disabled');
            } else {
                control.classList.add('gdpr-disabled');
            }

            // Pour les labels de toggle, désactiver aussi le parent label
            if (control.type === 'checkbox') {
                const label = control.closest('label');
                if (label) {
                    if (isEnabled) {
                        label.classList.remove('gdpr-disabled');
                    } else {
                        label.classList.add('gdpr-disabled');
                    }
                }
            }
        }
    });

    // Désactiver/activer les sections entières (actions utilisateur et logs)
    const gdprSections = document.querySelectorAll('.gdpr-section');
    gdprSections.forEach(section => {
        if (isEnabled) {
            section.classList.remove('gdpr-disabled-section');
        } else {
            section.classList.add('gdpr-disabled-section');
        }
    });
}

// Make functions globally accessible
window.updateSecurityStatusIndicators = updateSecurityStatusIndicators;
window.updateTemplateStatusIndicators = updateTemplateStatusIndicators;
window.updateTemplateLibraryIndicator = updateTemplateLibraryIndicator;
window.updateSystemStatusIndicators = updateSystemStatusIndicators;
window.toggleRGPDControls = toggleRGPDControls;

// Update floating save button text based on active tab
function updateFloatingSaveButtonText(activeTabId) {
    const floatingSaveBtn = document.getElementById('floating-save-btn');
    if (!floatingSaveBtn) return;

    const btnTextSpan = floatingSaveBtn.querySelector('.btn-text');
    if (!btnTextSpan) return;

    // Map tab IDs to button text
    const tabTextMap = {
        'general': 'Enregistrer Général',
        'licence': 'Enregistrer Licence',
        'systeme': 'Enregistrer Système',
        'acces': 'Enregistrer Accès',
        'securite': 'Enregistrer Sécurité',
        'pdf': 'Enregistrer PDF',
        'contenu': 'Enregistrer Canvas',
        'templates': 'Enregistrer Templates',
        'developpeur': 'Enregistrer Développeur'
    };

    // Update button text
    const newText = tabTextMap[activeTabId] || 'Enregistrer Tout';
    btnTextSpan.textContent = newText;

    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log(`[FLOATING SAVE] Button text updated to: "${newText}" for tab: ${activeTabId}`);
    }
}

// Make updateFloatingSaveButtonText globally accessible
window.updateFloatingSaveButtonText = updateFloatingSaveButtonText;
</script>

<script>
(function() {
    'use strict';

    /**
     * Système centralisé de gestion des réponses AJAX avec gestion des nonces
     */
    window.PDF_Builder_Ajax_Handler = {
        // Configuration du système de nonce
        config: {
            nonceTTL: 20 * 60 * 1000, // 20 minutes (WordPress default est 24h, mais on est prudent)
            refreshThreshold: 5 * 60 * 1000, // Rafraîchir 5 minutes avant expiration
            maxRetries: 2, // Nombre maximum de tentatives
            retryDelay: 1000, // Délai entre tentatives (ms)
            preloadCount: 3, // Nombre de nonces à précharger
            enableProactiveRefresh: true, // Rafraîchissement proactif
            enableRetry: true // Retry automatique activé
        },

        // État du système de nonce
        nonceState: {
            current: null,
            created: null,
            expires: null,
            refreshTimer: null,
            preloadQueue: [],
            retryCount: 0,
            stats: {
                requests: 0,
                nonceErrors: 0,
                retries: 0,
                refreshes: 0,
                lastError: null
            }
        },

        /**
         * Initialise le système de nonce avancé
         */
        initialize: function() {
            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Initialisation du système de nonce avancé');
            }

            // Initialiser avec le nonce actuel
            this.nonceState.current = window.pdfBuilderAjax?.nonce;
            this.nonceState.created = Date.now();
            this.nonceState.expires = Date.now() + this.config.nonceTTL;

            // Démarrer le rafraîchissement proactif
            if (this.config.enableProactiveRefresh) {
                this.startProactiveRefresh();
            }

            // Précharger des nonces
            this.preloadNonces();

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Système de nonce initialisé:', {
                    current: this.nonceState.current ? '***' : null,
                    expires: new Date(this.nonceState.expires).toLocaleTimeString(),
                    proactiveRefresh: this.config.enableProactiveRefresh
                });
            }
        },

        /**
         * Démarre le rafraîchissement proactif des nonces
         */
        startProactiveRefresh: function() {
            if (this.nonceState.refreshTimer) {
                clearTimeout(this.nonceState.refreshTimer);
            }

            const timeUntilRefresh = Math.max(0, this.nonceState.expires - Date.now() - this.config.refreshThreshold);

            this.nonceState.refreshTimer = setTimeout(() => {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    console.log('[PDF Builder] Rafraîchissement proactif du nonce');
                }
                this.refreshNonce().then(() => {
                    // Redémarrer le timer pour le prochain rafraîchissement
                    this.startProactiveRefresh();
                }).catch(error => {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('Erreur lors du rafraîchissement proactif:', error);
                    }
                    // Redémarrer quand même
                    this.startProactiveRefresh();
                });
            }, timeUntilRefresh);

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Prochain rafraîchissement dans ' + Math.round(timeUntilRefresh / 1000 / 60) + ' minutes');
            }
        },

        /**
         * Précharge plusieurs nonces pour éviter les appels répétés
         */
        preloadNonces: function() {
            if (this.nonceState.preloadQueue.length >= this.config.preloadCount) {
                return; // Déjà assez de nonces
            }

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Préchargement de ' + this.config.preloadCount - this.nonceState.preloadQueue.length + ' nonces');
            }

            // Faire une requête simple pour obtenir un nouveau nonce
            const formData = new FormData();
            formData.append('action', 'pdf_builder_get_fresh_nonce');

            fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.nonce) {
                    this.nonceState.preloadQueue.push({
                        nonce: data.data.nonce,
                        created: Date.now(),
                        expires: Date.now() + this.config.nonceTTL
                    });

                    // Nettoyer les nonces expirés
                    this.nonceState.preloadQueue = this.nonceState.preloadQueue.filter(n =>
                        n.expires > Date.now()
                    );

                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log('[PDF Builder] Nonce préchargé (' + this.nonceState.preloadQueue.length + '/' + this.config.preloadCount + ')');
                    }

                    // Continuer le préchargement si nécessaire
                    if (this.nonceState.preloadQueue.length < this.config.preloadCount) {
                        setTimeout(() => this.preloadNonces(), 100);
                    }
                }
            })
            .catch(error => {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    console.warn('Erreur lors du préchargement de nonce:', error);
                }
            });
        },

        /**
         * Rafraîchit le nonce actuel
         */
        refreshNonce: function() {
            return new Promise((resolve, reject) => {
                // Utiliser un nonce préchargé si disponible
                if (this.nonceState.preloadQueue.length > 0) {
                    const freshNonce = this.nonceState.preloadQueue.shift();
                    this.setCurrentNonce(freshNonce.nonce, freshNonce.created);
                    this.nonceState.stats.refreshes++;
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log('[PDF Builder] Nonce rafraîchi depuis le cache');
                    }
                    resolve();
                    return;
                }

                // Sinon, faire une requête pour obtenir un nouveau nonce
                const formData = new FormData();
                formData.append('action', 'pdf_builder_get_fresh_nonce');

                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.nonce) {
                        this.setCurrentNonce(data.data.nonce, Date.now());
                        this.nonceState.stats.refreshes++;
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[PDF Builder] Nonce rafraîchi depuis le serveur');
                        }
                        resolve();
                    } else {
                        reject(new Error('Impossible d\'obtenir un nouveau nonce'));
                    }
                })
                .catch(reject);
            });
        },

        /**
         * Définit le nonce actuel
         */
        setCurrentNonce: function(nonce, created = Date.now()) {
            this.nonceState.current = nonce;
            this.nonceState.created = created;
            this.nonceState.expires = created + this.config.nonceTTL;

            // Mettre à jour la variable globale
            if (window.pdfBuilderAjax) {
                window.pdfBuilderAjax.nonce = nonce;
            }
        },

        /**
         * Vérifie si le nonce actuel est proche de l'expiration
         */
        isNonceExpiringSoon: function() {
            return (this.nonceState.expires - Date.now()) < this.config.refreshThreshold;
        },

        /**
         * Nettoie les ressources du système de nonce
         */
        cleanup: function() {
            if (this.nonceState.refreshTimer) {
                clearTimeout(this.nonceState.refreshTimer);
                this.nonceState.refreshTimer = null;
            }
            this.nonceState.preloadQueue = [];
        },

        /**
         * Force un rafraîchissement immédiat du nonce
         */
        forceRefresh: function() {
            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Rafraîchissement forcé du nonce');
            }
            return this.refreshNonce();
        },

        /**
         * Configure le système de nonce
         */
        configure: function(newConfig) {
            Object.assign(this.config, newConfig);
            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[PDF Builder] Configuration du système de nonce mise à jour:', this.config);
            }
        },

        /**
         * Effectue une requête AJAX avec gestion automatique des nonces
         */
        makeRequest: function(formData, options = {}) {
            const self = this;
            return new Promise((resolve, reject) => {
                // Options par défaut
                const defaultOptions = {
                    button: null,
                    context: 'Unknown',
                    successCallback: null,
                    errorCallback: null,
                    retryCount: 0
                };

                const opts = Object.assign({}, defaultOptions, options);

                // Mettre à jour le bouton si fourni
                if (opts.button) {
                    this.setButtonState(opts.button, 'loading');
                }

                // S'assurer que nous avons un nonce valide
                this.ensureValidNonce().then(() => {
                    // Ajouter le nonce aux données
                    if (formData instanceof FormData) {
                        formData.set('nonce', this.nonceState.current);
                    } else if (typeof formData === 'object') {
                        formData.nonce = this.nonceState.current;
                    }

                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.log('[PDF Builder AJAX] ' + opts.context + ' - Making request with nonce: ' + (this.nonceState.current ? this.nonceState.current.substring(0, 10) + '...' : 'NULL'));
                    }

                    // Faire la requête
                    fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData,
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-WP-Nonce': this.nonceState.current
                        }
                    })
                    .then(response => {
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[PDF Builder AJAX] ' + opts.context + ' - Response status: ' + response.status);
                        }
                        return response.json().catch(() => {
                            // Si la réponse n'est pas du JSON valide, créer une erreur
                            throw new Error('Invalid JSON response from server');
                        });
                    })
                    .then(data => {
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[PDF Builder AJAX] ' + opts.context + ' - Response:', data);
                        }

                        if (data.success) {
                            // Succès
                            if (opts.button && opts.context !== 'PDF Builder') {
                                this.setButtonState(opts.button, 'success');
                            }
                            if (opts.successCallback) {
                                // Assurer que l'instance de notifications existe
                                if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                    window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                                }
                                opts.successCallback.call(window.pdfBuilderNotificationsInstance || window, data, data);
                            }
                            resolve(data);
                        } else {
                            // Erreur côté serveur
                            const errorMessage = typeof data.data === 'string' ? data.data : JSON.stringify(data.data) || 'Unknown error';

                            // Vérifier si c'est une erreur de nonce
                            if (errorMessage.includes('Nonce invalide') || errorMessage.includes('invalid nonce')) {
                                this.nonceState.stats.nonceErrors++;

                                // Essayer de rafraîchir le nonce et réessayer
                                if (opts.retryCount < this.config.maxRetries) {
                                    if (window.pdfBuilderDebugSettings?.javascript) {
                                        console.log('[PDF Builder AJAX] ' + opts.context + ' - Nonce error, retrying (' + (opts.retryCount + 1) + '/' + this.config.maxRetries + ')');
                                    }
                                    opts.retryCount++;
                                    this.forceRefresh().then(() => {
                                        // Réessayer avec le nouveau nonce
                                        setTimeout(() => {
                                            this.makeRequest(formData, opts).then(resolve).catch(reject);
                                        }, this.config.retryDelay);
                                    }).catch(() => {
                                        // Échec du rafraîchissement, échouer
                                        if (opts.button) {
                                            this.setButtonState(opts.button, 'error');
                                        }
                                        if (opts.errorCallback) {
                                            // Assurer que l'instance de notifications existe
                                            if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                                window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                                            }
                                            opts.errorCallback.call(window.pdfBuilderNotificationsInstance || window, data, data);
                                        }
                                        reject(new Error(errorMessage));
                                    });
                                    return;
                                }
                            }

                            // Erreur normale
                            if (opts.button) {
                                this.setButtonState(opts.button, 'error');
                            }
                            if (opts.errorCallback) {
                                // Assurer que l'instance de notifications existe
                                if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                    window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                                }
                                opts.errorCallback.call(window.pdfBuilderNotificationsInstance || window, data, data);
                            }
                            reject(new Error(errorMessage));
                        }
                    })
                    .catch(error => {
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.error('[PDF Builder AJAX] ' + opts.context + ' - Network error:', error);
                        }
                        this.nonceState.stats.requests++;

                        // Erreur réseau
                        if (opts.button) {
                            this.setButtonState(opts.button, 'error');
                        }
                        if (opts.errorCallback) {
                            // Assurer que l'instance de notifications existe
                            if (!window.pdfBuilderNotificationsInstance && window.PDF_Builder_Notifications) {
                                window.pdfBuilderNotificationsInstance = new window.PDF_Builder_Notifications();
                            }
                            opts.errorCallback.call(window.pdfBuilderNotificationsInstance || window, {error: error.message}, {error: error.message});
                        }
                        reject(error);
                    });
                }).catch(error => {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('[PDF Builder AJAX] ' + opts.context + ' - Nonce validation failed:', error);
                    }
                    if (opts.button) {
                        this.setButtonState(opts.button, 'error');
                    }
                    reject(error);
                });
            });
        },

        /**
         * S'assure qu'un nonce valide est disponible
         */
        ensureValidNonce: function() {
            return new Promise((resolve) => {
                if (this.nonceState.current && !this.isNonceExpiringSoon()) {
                    resolve();
                } else {
                    this.refreshNonce().then(resolve).catch(() => {
                        // En cas d'échec du rafraîchissement, utiliser le nonce actuel s'il existe
                        if (this.nonceState.current) {
                            resolve();
                        } else {
                            throw new Error('Unable to obtain valid nonce');
                        }
                    });
                }
            });
        },

        /**
         * Définit l'état d'un bouton - VERSION SIMPLIFIÉE
         */
        setButtonState: function(button, state) {
            if (!button) return;

            const originalText = button.getAttribute('data-original-text') || button.textContent;

            switch (state) {
                case 'loading':
                    button.setAttribute('data-original-text', originalText);
                    button.disabled = true;
                    button.innerHTML = '<span class="dashicons dashicons-update spin"></span> Chargement...';
                    button.style.opacity = '0.7';
                    break;
                case 'success':
                    // Suppression de l'état "succès" - retour direct au texte original
                    button.disabled = false;
                    button.innerHTML = originalText;
                    button.style.opacity = '1';
                    button.removeAttribute('data-original-text');
                    break;
                case 'error':
                    button.disabled = false;
                    button.innerHTML = '<span class="dashicons dashicons-no"></span> Erreur';
                    button.style.opacity = '1';
                    // Reset après 2 secondes au lieu de 3
                    setTimeout(() => this.setButtonState(button, 'reset'), 2000);
                    break;
                case 'reset':
                default:
                    button.disabled = false;
                    button.innerHTML = originalText;
                    button.style.opacity = '1';
                    button.removeAttribute('data-original-text');
                    break;
            }
        },

        /**
         * Obtient les statistiques du système de nonce
         */
        getStats: function() {
            return {
                nonce: {
                    current: this.nonceState.current ? '***' : null,
                    created: this.nonceState.created,
                    expires: this.nonceState.expires,
                    timeUntilExpiry: Math.max(0, this.nonceState.expires - Date.now()),
                    isExpiringSoon: this.isNonceExpiringSoon()
                },
                stats: this.nonceState.stats,
                config: this.config,
                preloadQueue: this.nonceState.preloadQueue.length
            };
        },
    
        /**
         * Méthode show pour la compatibilité avec les successCallback qui utilisent this.show
         */
        show: function(message) {
            if (window.showSuccessNotification) {
                window.showSuccessNotification(message);
            } else if (window.pdfBuilderNotificationsInstance && window.pdfBuilderNotificationsInstance.success) {
                window.pdfBuilderNotificationsInstance.success(message);
            }
        }
    };

    // Initialiser le système de nonce avancé au chargement
    document.addEventListener('DOMContentLoaded', function() {
        PDF_Builder_Ajax_Handler.initialize();
    });

    // Nettoyer à la fermeture de la page
    window.addEventListener('beforeunload', function() {
        PDF_Builder_Ajax_Handler.cleanup();
    });

    // Exposer les statistiques globalement pour le debug
    window.pdfBuilderNonceStats = function() {
        return PDF_Builder_Ajax_Handler.getStats();
    };

    // Méthode show globale pour la compatibilité
    window.show = function(message) {
        if (window.pdfBuilderNotificationsInstance && window.pdfBuilderNotificationsInstance.show) {
            window.pdfBuilderNotificationsInstance.show(message, 'info');
        }
    };

    // Basic modal functionality
    function safeQuerySelector(selector) {
        try {
            return document.querySelector(selector);
        } catch (e) {
            return null;
        }
    }

    function safeQuerySelectorAll(selector) {
        try {
            return document.querySelectorAll(selector);
        } catch (e) {
            return [];
        }
    }

    function hideModal(modal) {
        if (!modal) return;
        try {
            modal.style.setProperty('display', 'none', 'important');
        } catch (e) {
        }
    }

    function showModal(modal) {
        if (!modal) return false;
        try {
            modal.style.setProperty('display', 'flex', 'important');
            modal.style.setProperty('position', 'fixed', 'important');
            modal.style.setProperty('top', '0', 'important');
            modal.style.setProperty('left', '0', 'important');
            modal.style.setProperty('width', '100%', 'important');
            modal.style.setProperty('height', '100%', 'important');
            modal.style.setProperty('background', 'rgba(0,0,0,0.7)', 'important');
            modal.style.setProperty('z-index', '2147483647', 'important');
            modal.style.setProperty('align-items', 'center', 'important');
            modal.style.setProperty('justify-content', 'center', 'important');
            return true;
        } catch (e) {
            return false;
        }
    }

    // Initialize modals
    function initializeModals() {
        // Hide all modals by default
        const allModals = safeQuerySelectorAll('.canvas-modal');
        allModals.forEach(hideModal);

        // Basic event delegation for modals
        document.addEventListener('click', function(event) {
            const target = event.target;

            // Handle configure buttons
            if (target.closest('.canvas-configure-btn')) {
                event.preventDefault();
                const button = target.closest('.canvas-configure-btn');
                const card = button.closest('.canvas-card');
                if (!card) return;

                const category = card.getAttribute('data-category');
                if (!category) return;

                const modalId = 'canvas-' + category + '-modal';
                const modal = document.getElementById(modalId);
                if (!modal) return;

                showModal(modal);
            }

            // Handle close buttons
            if (target.closest('.canvas-modal-close, .canvas-modal-cancel')) {
                const modal = target.closest('.canvas-modal');
                if (modal) hideModal(modal);
            }

            // Handle modal background click
            if (target.classList.contains('canvas-modal') || target.classList.contains('canvas-modal-overlay')) {
                hideModal(target.closest('.canvas-modal'));
            }

            // Handle save buttons
            if (target.closest('.canvas-modal-save')) {
                event.preventDefault();
                const saveButton = target.closest('.canvas-modal-save');
                const modal = saveButton.closest('.canvas-modal');
                const category = saveButton.getAttribute('data-category');

                if (!modal || !category) return;

                // Disable button and show loading state
                saveButton.disabled = true;
                const originalText = saveButton.textContent;
                saveButton.textContent = 'Sauvegarde...';
                saveButton.style.opacity = '0.7';

                // Collect form data
                const form = modal.querySelector('form');
                if (!form) {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('No form found in modal for category:', category);
                    }
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    saveButton.style.opacity = '1';
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('nonce', window.pdfBuilderCanvasSettings?.nonce || '');
                formData.append('category', category);

                // Make AJAX request using centralized handler
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('category', category);

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: saveButton,
                    context: 'Canvas Modal',
                    successCallback: (result, originalData) => {
                        // Update canvas settings in window object
                        if (originalData.data && originalData.data.result_data) {
                            // Update window.pdfBuilderCanvasSettings with new values
                            Object.assign(window.pdfBuilderCanvasSettings, originalData.data.result_data);

                            // Update previews
                            if (typeof window.updateCanvasPreviews === 'function') {
                                window.updateCanvasPreviews(category);
                            }

                            // Update PDF_Builder_Preview_Manager if available
                            if (window.PDF_Builder_Preview_Manager && typeof window.PDF_Builder_Preview_Manager.initializeAllPreviews === 'function') {
                                window.PDF_Builder_Preview_Manager.initializeAllPreviews();
                            }
                        }

                        // Close modal after short delay
                        setTimeout(() => {
                            hideModal(modal);
                            PDF_Builder_Ajax_Handler.setButtonState(saveButton, 'reset');
                        }, 1500);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Canvas modal save error:', error);
                });
            }

            // Handle cache modal save buttons
            if (target.closest('.cache-modal-save')) {
                event.preventDefault();
                const saveButton = target.closest('.cache-modal-save');
                const modal = saveButton.closest('.canvas-modal');
                const category = saveButton.getAttribute('data-category') || 'cache';

                if (!modal) return;

                // Disable button and show loading state
                saveButton.disabled = true;
                const originalText = saveButton.textContent;
                saveButton.textContent = 'Sauvegarde...';
                saveButton.style.opacity = '0.7';

                // Collect form data
                const form = modal.querySelector('form');
                if (!form) {
                    if (window.pdfBuilderDebugSettings?.javascript) {
                        console.error('No form found in cache modal');
                    }
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    saveButton.style.opacity = '1';
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_cache_settings');
                formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
                formData.append('category', category);

                // Make AJAX request using centralized handler
                formData.append('action', 'pdf_builder_save_cache_settings');
                formData.append('category', category);

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: saveButton,
                    context: 'Cache Modal',
                    successCallback: (result, originalData) => {
                        // Close modal after short delay
                        setTimeout(() => {
                            hideModal(modal);
                            PDF_Builder_Ajax_Handler.setButtonState(saveButton, 'reset');
                        }, 1500);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Cache modal save error:', error);
                });
            }

            // Handle clear cache buttons
            if (target.closest('.clear-cache-from-modal, #clear-cache-from-modal')) {
                event.preventDefault();
                const clearButton = target.closest('.clear-cache-from-modal, #clear-cache-from-modal');
                const modal = clearButton.closest('.canvas-modal');

                // Disable button and show loading state
                clearButton.disabled = true;
                const originalText = clearButton.textContent;
                clearButton.textContent = 'Nettoyage...';
                clearButton.style.opacity = '0.7';

                // Make AJAX request using centralized handler
                const formData = new FormData();
                formData.append('action', 'pdf_builder_clear_cache');

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: clearButton,
                    context: 'Clear Cache',
                    successCallback: (result, originalData) => {
                        // Reset button after delay
                        setTimeout(() => {
                            PDF_Builder_Ajax_Handler.setButtonState(clearButton, 'reset');
                        }, 3000);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Clear cache error:', error);
                });
            }

            // Handle perform cleanup buttons
            if (target.closest('.perform-cleanup-btn, #perform-cleanup-btn')) {
                event.preventDefault();
                const cleanupButton = target.closest('.perform-cleanup-btn, #perform-cleanup-btn');
                const modal = cleanupButton.closest('.canvas-modal');

                // Disable button and show loading state
                cleanupButton.disabled = true;
                const originalText = cleanupButton.textContent;
                cleanupButton.textContent = 'Nettoyage...';
                cleanupButton.style.opacity = '0.7';

                // Make AJAX request using centralized handler
                const formData = new FormData();
                formData.append('action', 'pdf_builder_remove_temp_files');

                PDF_Builder_Ajax_Handler.makeRequest(formData, {
                    button: cleanupButton,
                    context: 'Cleanup',
                    successCallback: (result, originalData) => {
                        // Reset button after delay
                        setTimeout(() => {
                            PDF_Builder_Ajax_Handler.setButtonState(cleanupButton, 'reset');
                        }, 3000);
                    }
                }).catch(error => {
                    // Error already handled by the centralized handler
                    console.error('Cleanup error:', error);
                });
            }
        });

        // Handle escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const visibleModals = safeQuerySelectorAll('.canvas-modal[style*="display: flex"]');
                visibleModals.forEach(hideModal);
            }
        });
    }

    // Basic preview update functions
    window.updateCanvasPreviews = function(category) {
        // Simplified preview updates
        if (typeof window.updateDimensionsCardPreview === 'function') {
            window.updateDimensionsCardPreview();
        }
        if (typeof window.updateApparenceCardPreview === 'function') {
            window.updateApparenceCardPreview();
        }
        if (typeof window.updateGrilleCardPreview === 'function') {
            window.updateGrilleCardPreview();
        }
        if (typeof window.updateInteractionsCardPreview === 'function') {
            window.updateInteractionsCardPreview();
        }
        if (typeof window.updateExportCardPreview === 'function') {
            window.updateExportCardPreview();
        }
        if (typeof window.updatePerformanceCardPreview === 'function') {
            window.updatePerformanceCardPreview();
        }
        if (typeof window.updateAutosaveCardPreview === 'function') {
            window.updateAutosaveCardPreview();
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeTabs();
            initializeModals();
            initializeFloatingSaveButton();
        });
    } else {
        initializeTabs();
        initializeModals();
        initializeFloatingSaveButton();
    }

    // Initialize floating save button
    function initializeFloatingSaveButton() {
        const floatingSaveBtn = document.getElementById('floating-save-btn');
        if (!floatingSaveBtn) return;

        floatingSaveBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Collect all form data from all tabs
            const formData = new FormData();

            // Add action and nonce
            formData.append('action', 'pdf_builder_save_all_settings');
            formData.append('nonce', window.pdfBuilderAjax?.nonce || '');

            // Get current active tab to determine context
            const activeTab = document.querySelector('.nav-tab-active');
            const currentTab = activeTab ? activeTab.getAttribute('href').substring(1) : 'general';
            formData.append('current_tab', currentTab);

            // Collect data from all visible forms and inputs across all tabs
            // This ensures we save data from all tabs, not just the active one
            const allInputs = document.querySelectorAll('input, select, textarea');
            let collectedCount = 0;
            let developerFields = 0;

            allInputs.forEach(input => {
                // Skip buttons and hidden fields we don't want, but include disabled inputs as they may have values
                if (input.type === 'button' || input.type === 'submit' || input.type === 'reset' ||
                    input.name === '' || input.type === 'hidden') {
                    return;
                }

                // Special debug for our problematic fields
                if (input.name === 'pdf_builder_debug_pdf_editor' || input.name === 'pdf_builder_debug_settings_page') {
                    console.log('[DEBUG FIELDS] Found field:', input.name, 'checked:', input.checked, 'disabled:', input.disabled, 'type:', input.type);
                }

                // Handle different input types
                if (input.type === 'checkbox') {
                    formData.append(input.name, input.checked ? '1' : '0');
                    collectedCount++;
                    if (input.name.includes('developer') || input.name.includes('debug') || input.name.includes('log')) {
                        developerFields++;
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[FLOATING SAVE] Developer checkbox: ' + input.name + ' = ' + (input.checked ? '1' : '0') + (input.disabled ? ' (disabled)' : ''));
                        }
                    }
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        formData.append(input.name, input.value);
                        collectedCount++;
                    }
                } else {
                    formData.append(input.name, input.value || '');
                    collectedCount++;
                    if (input.name.includes('developer') || input.name.includes('debug') || input.name.includes('log')) {
                        developerFields++;
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[FLOATING SAVE] Developer field: ' + input.name + ' = ' + (input.value || '') + (input.disabled ? ' (disabled)' : ''));
                        }
                    }
                }
            });

            if (window.pdfBuilderDebugSettings?.javascript) {
                console.log('[FLOATING SAVE] Total inputs collected: ' + collectedCount);
                console.log('[FLOATING SAVE] Developer fields collected: ' + developerFields);
                console.log('[FLOATING SAVE] Current tab: ' + currentTab);
            }

            // Make AJAX request using centralized handler
            PDF_Builder_Ajax_Handler.makeRequest(formData, {
                button: floatingSaveBtn,
                context: 'Floating Save Button',
                successCallback: function(result, originalData) {
                    // Update window.pdfBuilderSavedSettings with new values
                    if (originalData.data && originalData.data.saved_settings) {
                        window.pdfBuilderSavedSettings = Object.assign({}, window.pdfBuilderSavedSettings, originalData.data.saved_settings);
                        if (window.pdfBuilderDebugSettings?.javascript) {
                            console.log('[FLOATING SAVE] Updated window.pdfBuilderSavedSettings with new values');
                        }
                    }

                    // Update previews after successful save
                    if (window.PDF_Builder_Preview_Manager && typeof window.PDF_Builder_Preview_Manager.initializeAllPreviews === 'function') {
                        window.PDF_Builder_Preview_Manager.initializeAllPreviews();
                    }

                    // Update canvas previews if on contenu tab
                    if (currentTab === 'contenu' && typeof window.updateCanvasPreviews === 'function') {
                        window.updateCanvasPreviews('all');
                    }

                    // Update status indicators
                    if (typeof window.updateSecurityStatusIndicators === 'function') {
                        window.updateSecurityStatusIndicators();
                    }
                    if (typeof window.updateTemplateStatusIndicators === 'function') {
                        window.updateTemplateStatusIndicators();
                    }
                    if (typeof window.updateSystemStatusIndicators === 'function') {
                        window.updateSystemStatusIndicators();
                    }
                    if (typeof window.updateTemplateLibraryIndicator === 'function') {
                        window.updateTemplateLibraryIndicator();
                    }

                    // Re-sync checkboxes after settings update
                    if (typeof window.syncCheckboxesWithSavedSettings === 'function') {
                        window.syncCheckboxesWithSavedSettings();
                    }
                }
            }).catch(error => {
                console.error('Floating save error:', error);
            });
        });
    }

})();
</script>



<?php require_once __DIR__ . '/tab-diagnostic.php'; ?>

