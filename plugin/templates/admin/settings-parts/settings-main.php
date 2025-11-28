<?php
/**
 * PDF Builder Pro - Main Settings Logic
 * Core settings processing and HTML structure
 * Updated: 2025-11-18 20:10:00
 */

// DEBUG: Log que le fichier est chargé
error_log('PDF Builder: settings-main.php loaded at ' . date('Y-m-d H:i:s'));

echo '<script>console.log("PDF Builder: PHP file loaded and script tag executed");</script>';

// Test script - very simple to isolate the issue
echo '<script>
console.log("PDF Builder: TEST SCRIPT STARTED - IMMEDIATE EXECUTION");

// Test basic JavaScript execution
console.log("PDF Builder: Basic JavaScript execution test passed");

// Test if we can access window object
console.log("PDF Builder: Window object accessible:", typeof window);

// Test if we can access document object
console.log("PDF Builder: Document object accessible:", typeof document);

console.log("PDF Builder: TEST SCRIPT COMPLETED SUCCESSFULLY - IMMEDIATE EXECUTION");
</script>';


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

// Extraire les paramètres principaux
$settings = $all_settings['pdf_builder_settings'];
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
window.pdfBuilderSavedSettings = <?php echo wp_json_encode($preview_data); ?>;
window.pdfBuilderCanvasSettings = <?php echo wp_json_encode($canvas_settings); ?>;

// Variables AJAX globales pour les requêtes AJAX
window.pdfBuilderAjax = {
    nonce: '<?php echo wp_create_nonce('pdf_builder_save_settings'); ?>',
    ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>'
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

// Process form
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if ($is_ajax) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }
    }
    if (defined('WP_DEBUG') && WP_DEBUG) {
        
    }
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }
        // Check for max_input_vars limit
        $max_input_vars = ini_get('max_input_vars');
        if ($max_input_vars && count($_POST) >= $max_input_vars) {
            $notices[] = '<div class="notice notice-error"><p><strong>⚠️</strong> Trop de paramètres soumis (' . count($_POST) . '). Limite PHP max_input_vars: ' . $max_input_vars . '. Certains paramètres n\'ont pas été sauvegardés.</p></div>';
        }
        $to_save = [
            'debug_mode' => isset($_POST['debug_mode']),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            // PDF settings from general tab
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            // Performance settings moved to Performance tab only
            // PDF settings moved to PDF tab only
            // Canvas settings moved to Canvas tab only
            // Développeur
            'developer_enabled' => isset($_POST['developer_enabled']),
            'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
            'debug_php_errors' => isset($_POST['debug_php_errors']),
            'debug_javascript' => isset($_POST['debug_javascript']),
            'debug_javascript_verbose' => isset($_POST['debug_javascript_verbose']),
            'debug_ajax' => isset($_POST['debug_ajax']),
            'debug_performance' => isset($_POST['debug_performance']),
            'debug_database' => isset($_POST['debug_database']),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),
            'license_test_mode' => isset($_POST['license_test_mode']),
            'force_https' => isset($_POST['force_https']),
        ];
        $new_settings = array_merge($settings, $to_save);
        // Check if settings actually changed - use serialize for deep comparison
        $settings_changed = serialize($new_settings) !== serialize($settings);
        if (defined('WP_DEBUG') && WP_DEBUG) {
            
        }

        $result = update_option('pdf_builder_settings', $new_settings);
        try {
            // Debug: Always log the result for troubleshooting
            if (defined('WP_DEBUG') && WP_DEBUG) {
                
            }

            // Simplified success logic: if no exception was thrown, consider it successful
            if ($is_ajax) {
                send_ajax_response(true, 'Paramètres enregistrés avec succès.');
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                
            }
            if ($is_ajax) {
                send_ajax_response(false, 'Erreur lors de la sauvegarde des paramètres: ' . $e->getMessage());
            } else {
                $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur lors de la sauvegarde des paramètres: ' . esc_html($e->getMessage()) . '</p></div>';
            }
        }
        $settings = get_option('pdf_builder_settings', []);
        // Also update the standalone PDF options so that other parts of the plugin
        // which read from individual options get updated when the non-AJAX form is used
        if (isset($_POST['pdf_quality'])) {
            update_option('pdf_builder_pdf_quality', sanitize_text_field($_POST['pdf_quality']));
        }
        if (isset($_POST['pdf_page_size'])) {
            update_option('pdf_builder_pdf_page_size', sanitize_text_field($_POST['pdf_page_size']));
        }
        if (isset($_POST['pdf_orientation'])) {
            update_option('pdf_builder_pdf_orientation', sanitize_text_field($_POST['pdf_orientation']));
        }
        // Checkboxes
        update_option('pdf_builder_pdf_cache_enabled', isset($_POST['pdf_cache_enabled']) ? 1 : 0);
        if (isset($_POST['pdf_compression'])) {
            update_option('pdf_builder_pdf_compression', sanitize_text_field($_POST['pdf_compression']));
        }
        update_option('pdf_builder_pdf_metadata_enabled', isset($_POST['pdf_metadata_enabled']) ? 1 : 0);
        update_option('pdf_builder_pdf_print_optimized', isset($_POST['pdf_print_optimized']) ? 1 : 0);
        update_option('pdf_builder_template_library_enabled', isset($_POST['template_library_enabled']) ? 1 : 0);
        if (isset($_POST['default_template'])) {
            update_option('pdf_builder_default_template', sanitize_text_field($_POST['default_template']));
        }
    } else {
        $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
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
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Cache vidé avec succès.</p></div>';
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
                <span class="tab-icon">🔧</span>
                <span class="tab-text">Système</span>
            </a>
            <a href="#acces" class="nav-tab" data-tab="acces">
                <span class="tab-icon">👥</span>
                <span class="tab-text">Accès</span>
            </a>
            <a href="#securite" class="nav-tab" data-tab="securite">
                <span class="tab-icon">🔒</span>
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
                <span class="tab-icon">👨‍💻</span>
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

</div>

<!-- Modals - COMPLETEMENT HORS du conteneur principal -->
<?php require_once 'settings-modals.php'; ?>

<!-- Floating Save Button - HORS du conteneur principal -->
<div id="floating-save-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999 !important; border-radius: 10px; padding: 5px; display: block !important; visibility: visible !important; opacity: 1 !important;">
    <button type="button" class="floating-save-btn" id="floating-save-btn" style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border: none; border-radius: 50px; padding: 15px 25px; font-size: 16px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; visibility: visible !important; opacity: 1 !important;">
        <span class="save-icon">💾</span>
        <span class="save-text">Enregistrer</span>
    </button>
    <div class="floating-tooltip" style="position: absolute; bottom: 70px; right: 0; background: #333; color: white; padding: 8px 12px; border-radius: 6px; font-size: 14px; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
        Cliquez pour sauvegarder tous les paramètres
    </div>
</div>

<!-- Bouton de secours sans JavaScript -->
<noscript>
    <div style="position: fixed; bottom: 80px; right: 20px; z-index: 999999; background: #fff; border: 2px solid #007cba; border-radius: 8px; padding: 10px;">
        <strong>💾 Sauvegarde manuelle</strong><br>
        <small>JavaScript désactivé - Utilisez les boutons de chaque onglet</small>
    </div>
</noscript>

<style>
/* Styles pour le bouton flottant */
#floating-save-button {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 999999 !important;
    border-radius: 10px;
    padding: 5px;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.floating-save-btn {
    background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
    color: white !important;
    border: none;
    border-radius: 50px;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    display: flex !important;
    align-items: center;
    gap: 8px;
    visibility: visible !important;
    opacity: 1 !important;
}

.floating-save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.4);
}

.floating-save-btn.saving {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    animation: pulse 1.5s infinite;
}

.floating-save-btn.saved {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.floating-save-btn.error {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
}

.floating-tooltip {
    position: absolute;
    bottom: 70px;
    right: 0;
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.floating-save-btn:hover + .floating-tooltip,
.floating-tooltip:hover {
    opacity: 1;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Responsive design pour mobile */
@media (max-width: 768px) {
    #floating-save-button {
        bottom: 15px;
        right: 15px;
    }

    .floating-save-btn {
        padding: 12px 20px;
        font-size: 14px;
    }

    .floating-tooltip {
        display: none; /* Masquer le tooltip sur mobile */
    }
}

/* Styles pour les contrôles RGPD désactivés */
.gdpr-disabled {
    opacity: 0.5;
    pointer-events: none;
    cursor: not-allowed;
}

.gdpr-disabled-section {
    opacity: 0.5;
    pointer-events: none;
}

.gdpr-disabled-section * {
    pointer-events: none !important;
}

.gdpr-disabled + span.toggle-slider {
    background: #ccc !important;
    cursor: not-allowed;
}

.gdpr-disabled + span.toggle-slider:before {
    background: #999 !important;
}

/* Styles pour les onglets */
.tab-content {
    display: none !important;
}

.tab-content.active {
    display: block !important;
}
</style>


<script>
// Test script - very simple to isolate the issue
console.log('PDF Builder: TEST SCRIPT STARTED');

// Test PHP constants
try {
    const PDF_BUILDER_DEBUG_ENABLED = <?php echo wp_json_encode(isset($settings['debug_javascript']) && $settings['debug_javascript']); ?>;
    const PDF_BUILDER_DEBUG_VERBOSE = <?php echo wp_json_encode(isset($settings['debug_javascript_verbose']) && $settings['debug_javascript_verbose']); ?>;
    console.log('PDF Builder: PHP constants loaded successfully - DEBUG_ENABLED:', PDF_BUILDER_DEBUG_ENABLED, 'VERBOSE:', PDF_BUILDER_DEBUG_VERBOSE);
} catch (error) {
    console.error('PDF Builder: Error loading PHP constants:', error);
}

// Test basic JavaScript execution
console.log('PDF Builder: Basic JavaScript execution test passed');

// Test if we can access window object
console.log('PDF Builder: Window object accessible:', typeof window);

// Test if we can access document object
console.log('PDF Builder: Document object accessible:', typeof document);

console.log('PDF Builder: TEST SCRIPT COMPLETED SUCCESSFULLY');
</script>
<script>
// Update zoom card preview
console.log('PDF Builder: About to define updateZoomCardPreview');
window.updateZoomCardPreview = function() {
    console.log('PDF Builder: updateZoomCardPreview function called');
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

// Tab switching functionality
function initializeTabs() {
    console.log('PDF Builder: initializeTabs() function called');

    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    console.log('PDF Builder: Found tabs count:', tabs.length);
    console.log('PDF Builder: Found contents count:', contents.length);
    console.log('PDF Builder: Tabs elements:', Array.from(tabs).map(tab => ({
        href: tab.getAttribute('href'),
        dataTab: tab.getAttribute('data-tab'),
        text: tab.textContent.trim(),
        classList: tab.classList.toString()
    })));
    console.log('PDF Builder: Contents elements:', Array.from(contents).map(content => ({
        id: content.id,
        classList: content.classList.toString(),
        display: window.getComputedStyle(content).display,
        visibility: window.getComputedStyle(content).visibility
    })));

    // First, hide ALL tab contents
    console.log('PDF Builder: Hiding all tab contents initially');
    contents.forEach(function(content) {
        console.log('PDF Builder: Hiding content:', content.id);
        content.classList.remove('active');
        content.style.display = 'none';
        console.log('PDF Builder: After hiding, content display:', window.getComputedStyle(content).display);
    });

    // Add click listeners to tabs
    tabs.forEach(function(tab) {
        console.log('PDF Builder: Adding click listener to tab:', tab, 'href:', tab.getAttribute('href'));
        tab.addEventListener('click', function(e) {
            console.log('PDF Builder: ===== TAB CLICK START =====');
            console.log('PDF Builder: Tab clicked - event:', e);
            console.log('PDF Builder: Tab element:', this);
            console.log('PDF Builder: Tab href:', this.getAttribute('href'));

            e.preventDefault();
            console.log('PDF Builder: preventDefault() called');

            console.log('PDF Builder: Removing nav-tab-active from all tabs');
            // Remove active class from all tabs
            tabs.forEach(function(t) {
                console.log('PDF Builder: Processing tab for removal:', t, 'current classList:', t.classList.toString());
                t.classList.remove('nav-tab-active');
                console.log('PDF Builder: After removal, classList:', t.classList.toString());
            });

            console.log('PDF Builder: Adding nav-tab-active to clicked tab');
            // Add active class to clicked tab
            console.log('PDF Builder: Before adding class, clicked tab classList:', this.classList.toString());
            this.classList.add('nav-tab-active');
            console.log('PDF Builder: After adding class, clicked tab classList:', this.classList.toString());

            console.log('PDF Builder: Processing tab contents');
            // Hide all tab contents
            contents.forEach(function(c) {
                console.log('PDF Builder: Processing content for removal:', c.id, 'current classList:', c.classList.toString());
                c.classList.remove('active');
                c.style.display = 'none';
                console.log('PDF Builder: After removal, content classList:', c.classList.toString());
            });

            // Show corresponding tab content
            const target = this.getAttribute('href').substring(1);
            console.log('PDF Builder: Target content id:', target);

            const targetContent = document.getElementById(target);
            console.log('PDF Builder: Target content element found?', !!targetContent, 'Element:', targetContent);

            if (targetContent) {
                console.log('PDF Builder: Before adding active class, targetContent classList:', targetContent.classList.toString());
                targetContent.classList.add('active');
                targetContent.style.display = 'block';
                console.log('PDF Builder: After adding active class, targetContent classList:', targetContent.classList.toString());
                console.log('PDF Builder: Content should now be visible');
            } else {
                console.error('PDF Builder: Target content not found for id:', target);
                console.log('PDF Builder: Available content IDs:', Array.from(contents).map(c => c.id));
            }

            console.log('PDF Builder: ===== TAB CLICK END =====');

            // Update canvas previews when switching to contenu tab
            if (target === 'contenu') {
                console.log('PDF Builder: Switching to contenu tab, updating canvas previews');
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
                    console.error('PDF Builder: Error updating canvas previews on tab switch:', error);
                }
            }

            // Update URL hash without scrolling
            console.log('PDF Builder: Updating URL hash to:', '#' + target);
            history.replaceState(null, null, '#' + target);
        });
    });

    // Check hash on load and initialize tabs properly
    console.log('PDF Builder: Checking URL hash for initial tab');
    const hash = window.location.hash.substring(1);
    console.log('PDF Builder: Current hash:', hash);

    let targetTab = 'general'; // Default tab
    console.log('PDF Builder: Default targetTab set to:', targetTab);

    if (hash) {
        console.log('PDF Builder: Hash found, checking if tab exists for:', hash);
        const tabExists = document.querySelector('.nav-tab[href="#' + hash + '"]');
        console.log('PDF Builder: Tab exists for hash?', !!tabExists, 'Element:', tabExists);
        if (tabExists) {
            targetTab = hash;
            console.log('PDF Builder: targetTab updated to hash value:', targetTab);
        } else {
            console.log('PDF Builder: Hash tab does not exist, keeping default:', targetTab);
        }
    } else {
        console.log('PDF Builder: No hash found, using default tab');
    }

    console.log('PDF Builder: Final targetTab:', targetTab);

    // Set active tab and content without triggering click events
    console.log('PDF Builder: Looking for activeTab element with selector:', '.nav-tab[href="#' + targetTab + '"]');
    const activeTab = document.querySelector('.nav-tab[href="#' + targetTab + '"]');
    console.log('PDF Builder: activeTab found?', !!activeTab, 'Element:', activeTab);

    console.log('PDF Builder: Looking for activeContent element with id:', targetTab);
    const activeContent = document.getElementById(targetTab);
    console.log('PDF Builder: activeContent found?', !!activeContent, 'Element:', activeContent);

    if (activeTab && activeContent) {
        console.log('PDF Builder: Both activeTab and activeContent found, proceeding with initialization');

        console.log('PDF Builder: Removing active classes from all tabs and contents');
        // Remove active classes from all tabs and contents
        document.querySelectorAll('.nav-tab').forEach(tab => {
            console.log('PDF Builder: Removing nav-tab-active from tab:', tab);
            tab.classList.remove('nav-tab-active');
        });
        document.querySelectorAll('.tab-content').forEach(content => {
            console.log('PDF Builder: Removing active from content:', content.id);
            content.classList.remove('active');
            content.style.display = 'none';
        });

        console.log('PDF Builder: Adding active classes to target elements');
        // Add active classes to target tab and content
        console.log('PDF Builder: Adding nav-tab-active to activeTab:', activeTab);
        activeTab.classList.add('nav-tab-active');
        console.log('PDF Builder: Adding active to activeContent:', activeContent);
        activeContent.classList.add('active');
        activeContent.style.display = 'block';

        console.log('PDF Builder: Tab initialization completed successfully');

        // Update mobile menu text
        console.log('PDF Builder: Updating mobile menu text');
        const currentTabText = document.querySelector('.current-tab-text');
        if (currentTabText) {
            console.log('PDF Builder: currentTabText element found:', currentTabText);
            const tabText = activeTab.querySelector('.tab-text');
            if (tabText) {
                console.log('PDF Builder: tabText found:', tabText, 'textContent:', tabText.textContent);
                currentTabText.textContent = tabText.textContent;
                console.log('PDF Builder: Mobile menu text updated to:', tabText.textContent);
            } else {
                console.log('PDF Builder: tabText not found in activeTab');
            }
        } else {
            console.log('PDF Builder: currentTabText element not found');
        }

        // Log final state after initialization
        console.log('PDF Builder: ===== INITIALIZATION COMPLETE =====');
        console.log('PDF Builder: Active tab after init:', document.querySelector('.nav-tab-active'));
        console.log('PDF Builder: Active content after init:', document.querySelector('.tab-content.active'));
        console.log('PDF Builder: All tabs after init:', Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({
            href: tab.getAttribute('href'),
            classes: tab.classList.toString(),
            isActive: tab.classList.contains('nav-tab-active')
        })));
        console.log('PDF Builder: All contents after init:', Array.from(document.querySelectorAll('.tab-content')).map(content => ({
            id: content.id,
            classes: content.classList.toString(),
            isActive: content.classList.contains('active'),
            display: window.getComputedStyle(content).display,
            visibility: window.getComputedStyle(content).visibility
        })));
        console.log('PDF Builder: ===== END INITIALIZATION =====');
    } else {
        console.error('PDF Builder: Could not find activeTab or activeContent for targetTab:', targetTab);
        console.log('PDF Builder: Available tabs:', Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({href: tab.getAttribute('href'), element: tab})));
        console.log('PDF Builder: Available contents:', Array.from(document.querySelectorAll('.tab-content')).map(content => ({id: content.id, element: content})));
    }

    console.log('PDF Builder: Tabs initialization completed');
    console.log('PDF Builder: ===== FINAL STATE CHECK =====');
    console.log('PDF Builder: Final state - active tabs:', Array.from(document.querySelectorAll('.nav-tab-active')).map(tab => ({href: tab.getAttribute('href'), element: tab})));
    console.log('PDF Builder: Final state - active contents:', Array.from(document.querySelectorAll('.tab-content.active')).map(content => ({id: content.id, element: content})));
    console.log('PDF Builder: Final state - all tabs:', Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({href: tab.getAttribute('href'), classes: tab.classList.toString()})));
    console.log('PDF Builder: Final state - all contents:', Array.from(document.querySelectorAll('.tab-content')).map(content => ({id: content.id, classes: content.classList.toString()})));
    console.log('PDF Builder: ===== END FINAL STATE CHECK =====');
}

console.log('PDF Builder: About to add DOMContentLoaded listener');
console.log('PDF Builder: Document readyState:', document.readyState);
console.log('PDF Builder: Window loaded:', window.loaded);

document.addEventListener('DOMContentLoaded', function() {
    console.log('PDF Builder: ===== DOMContentLoaded FIRED =====');
    console.log('PDF Builder: DOMContentLoaded fired - starting tab initialization');

    // Add a delay to ensure all content is loaded
    setTimeout(function() {
        console.log('PDF Builder: Timeout fired - now initializing tabs');
        initializeTabs();
    }, 100);
});

// Also try to initialize immediately if DOM is already ready
console.log('PDF Builder: Checking if DOM is already ready');
if (document.readyState === 'loading') {
    console.log('PDF Builder: DOM still loading, waiting for DOMContentLoaded');
} else {
    console.log('PDF Builder: DOM already ready, initializing immediately');
    setTimeout(function() {
        console.log('PDF Builder: Immediate timeout fired - initializing tabs');
        initializeTabs();
    }, 100);
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
            
            previewDiv.innerHTML = '<p class="current-template"><strong>Assigné :</strong> ' + templateName.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '<span class="assigned-badge">✓</span></p>';
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
</script>
<script>
(function() {
    'use strict';

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
                    console.error('No form found in modal for category:', category);
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    saveButton.style.opacity = '1';
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_canvas_settings');
                formData.append('nonce', window.pdfBuilderCanvasSettings?.nonce || '');
                formData.append('category', category);

                // Make AJAX request
                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        saveButton.textContent = '✓ Sauvegardé';
                        saveButton.style.backgroundColor = '#28a745';
                        saveButton.style.color = 'white';

                        // Update canvas settings in window object
                        if (data.data && data.data.saved) {
                            // Update window.pdfBuilderCanvasSettings with new values
                            Object.assign(window.pdfBuilderCanvasSettings, data.data.saved);

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
                            // Reset button
                            saveButton.disabled = false;
                            saveButton.textContent = originalText;
                            saveButton.style.backgroundColor = '';
                            saveButton.style.color = '';
                            saveButton.style.opacity = '1';
                        }, 1500);

                    } else {
                        // Show error
                        saveButton.textContent = '❌ Erreur';
                        saveButton.style.backgroundColor = '#dc3545';
                        saveButton.style.color = 'white';

                        console.error('Save failed:', data.data?.message || 'Unknown error');

                        // Reset button after delay
                        setTimeout(() => {
                            saveButton.disabled = false;
                            saveButton.textContent = originalText;
                            saveButton.style.backgroundColor = '';
                            saveButton.style.color = '';
                            saveButton.style.opacity = '1';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    saveButton.textContent = '❌ Erreur réseau';
                    saveButton.style.backgroundColor = '#dc3545';
                    saveButton.style.color = 'white';

                    // Reset button after delay
                    setTimeout(() => {
                        saveButton.disabled = false;
                        saveButton.textContent = originalText;
                        saveButton.style.backgroundColor = '';
                        saveButton.style.color = '';
                        saveButton.style.opacity = '1';
                    }, 3000);
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
                    console.error('No form found in cache modal');
                    saveButton.disabled = false;
                    saveButton.textContent = originalText;
                    saveButton.style.opacity = '1';
                    return;
                }

                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_cache_settings');
                formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
                formData.append('category', category);

                // Make AJAX request
                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        saveButton.textContent = '✓ Sauvegardé';
                        saveButton.style.backgroundColor = '#28a745';
                        saveButton.style.color = 'white';

                        // Close modal after short delay
                        setTimeout(() => {
                            hideModal(modal);
                            // Reset button
                            saveButton.disabled = false;
                            saveButton.textContent = originalText;
                            saveButton.style.backgroundColor = '';
                            saveButton.style.color = '';
                            saveButton.style.opacity = '1';
                        }, 1500);

                    } else {
                        // Show error
                        saveButton.textContent = '❌ Erreur';
                        saveButton.style.backgroundColor = '#dc3545';
                        saveButton.style.color = 'white';

                        console.error('Cache save failed:', data.data?.message || 'Unknown error');

                        // Reset button after delay
                        setTimeout(() => {
                            saveButton.disabled = false;
                            saveButton.textContent = originalText;
                            saveButton.style.backgroundColor = '';
                            saveButton.style.color = '';
                            saveButton.style.opacity = '1';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    saveButton.textContent = '❌ Erreur réseau';
                    saveButton.style.backgroundColor = '#dc3545';
                    saveButton.style.color = 'white';

                    // Reset button after delay
                    setTimeout(() => {
                        saveButton.disabled = false;
                        saveButton.textContent = originalText;
                        saveButton.style.backgroundColor = '';
                        saveButton.style.color = '';
                        saveButton.style.opacity = '1';
                    }, 3000);
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

                // Make AJAX request
                const formData = new FormData();
                formData.append('action', 'pdf_builder_clear_cache');
                formData.append('nonce', window.pdfBuilderAjax?.nonce || '');

                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        clearButton.textContent = '✓ Cache vidé';
                        clearButton.style.backgroundColor = '#28a745';
                        clearButton.style.color = 'white';

                        // Reset button after delay
                        setTimeout(() => {
                            clearButton.disabled = false;
                            clearButton.textContent = originalText;
                            clearButton.style.backgroundColor = '';
                            clearButton.style.color = '';
                            clearButton.style.opacity = '1';
                        }, 3000);

                    } else {
                        // Show error
                        clearButton.textContent = '❌ Erreur';
                        clearButton.style.backgroundColor = '#dc3545';
                        clearButton.style.color = 'white';

                        console.error('Cache clear failed:', data.data?.message || 'Unknown error');

                        // Reset button after delay
                        setTimeout(() => {
                            clearButton.disabled = false;
                            clearButton.textContent = originalText;
                            clearButton.style.backgroundColor = '';
                            clearButton.style.color = '';
                            clearButton.style.opacity = '1';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    clearButton.textContent = '❌ Erreur réseau';
                    clearButton.style.backgroundColor = '#dc3545';
                    clearButton.style.color = 'white';

                    // Reset button after delay
                    setTimeout(() => {
                        clearButton.disabled = false;
                        clearButton.textContent = originalText;
                        clearButton.style.backgroundColor = '';
                        clearButton.style.color = '';
                        clearButton.style.opacity = '1';
                    }, 3000);
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

                // Make AJAX request
                const formData = new FormData();
                formData.append('action', 'pdf_builder_remove_temp_files');
                formData.append('nonce', window.pdfBuilderAjax?.nonce || '');

                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        cleanupButton.textContent = '✓ Nettoyé';
                        cleanupButton.style.backgroundColor = '#28a745';
                        cleanupButton.style.color = 'white';

                        // Reset button after delay
                        setTimeout(() => {
                            cleanupButton.disabled = false;
                            cleanupButton.textContent = originalText;
                            cleanupButton.style.backgroundColor = '';
                            cleanupButton.style.color = '';
                            cleanupButton.style.opacity = '1';
                        }, 3000);

                    } else {
                        // Show error
                        cleanupButton.textContent = '❌ Erreur';
                        cleanupButton.style.backgroundColor = '#dc3545';
                        cleanupButton.style.color = 'white';

                        console.error('Cleanup failed:', data.data?.message || 'Unknown error');

                        // Reset button after delay
                        setTimeout(() => {
                            cleanupButton.disabled = false;
                            cleanupButton.textContent = originalText;
                            cleanupButton.style.backgroundColor = '';
                            cleanupButton.style.color = '';
                            cleanupButton.style.opacity = '1';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    cleanupButton.textContent = '❌ Erreur réseau';
                    cleanupButton.style.backgroundColor = '#dc3545';
                    cleanupButton.style.color = 'white';

                    // Reset button after delay
                    setTimeout(() => {
                        cleanupButton.disabled = false;
                        cleanupButton.textContent = originalText;
                        cleanupButton.style.backgroundColor = '';
                        cleanupButton.style.color = '';
                        cleanupButton.style.opacity = '1';
                    }, 3000);
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

        // Handle floating save button
        const floatingSaveBtn = document.getElementById('floating-save-btn');
        if (floatingSaveBtn) {
            floatingSaveBtn.addEventListener('click', function(event) {
                event.preventDefault();

                console.log('🔄 [PDF Builder] Bouton flottant "Enregistrer" cliqué');

                // Disable button and show loading state
                floatingSaveBtn.disabled = true;
                const originalHTML = floatingSaveBtn.innerHTML;
                floatingSaveBtn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Enregistrement...';
                floatingSaveBtn.style.opacity = '0.7';

                // Get active tab
                const activeTab = document.querySelector('.nav-tab.nav-tab-active');
                if (!activeTab) {
                    console.error('No active tab found');
                    floatingSaveBtn.disabled = false;
                    floatingSaveBtn.innerHTML = originalHTML;
                    floatingSaveBtn.style.opacity = '1';
                    return;
                }

                const tabId = activeTab.getAttribute('data-tab') || activeTab.getAttribute('href')?.substring(1);
                console.log('📋 [PDF Builder] Onglet actif détecté:', tabId);

                if (!tabId) {
                    console.error('No tab ID found');
                    floatingSaveBtn.disabled = false;
                    floatingSaveBtn.innerHTML = originalHTML;
                    floatingSaveBtn.style.opacity = '1';
                    return;
                }

                // Get form for active tab
                const activeContent = document.getElementById(tabId);
                if (!activeContent) {
                    console.error('No active content found for tab:', tabId);
                    floatingSaveBtn.disabled = false;
                    floatingSaveBtn.innerHTML = originalHTML;
                    floatingSaveBtn.style.opacity = '1';
                    return;
                }

                const form = activeContent.querySelector('form');
                if (!form) {
                    console.error('No form found in active tab:', tabId);
                    floatingSaveBtn.disabled = false;
                    floatingSaveBtn.innerHTML = originalHTML;
                    floatingSaveBtn.style.opacity = '1';
                    return;
                }

                console.log('📝 [PDF Builder] Formulaire trouvé pour l\'onglet:', tabId);

                // Collect form data
                const formData = new FormData(form);
                formData.append('action', 'pdf_builder_save_settings');
                formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
                formData.append('tab', tabId);

                console.log('📤 [PDF Builder] Envoi des données du formulaire:', {
                    tab: tabId,
                    action: 'pdf_builder_save_settings',
                    dataCount: Array.from(formData.entries()).length
                });

                // Make AJAX request
                fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('📥 [PDF Builder] Réponse AJAX reçue:', data);

                    if (data.success) {
                        // Show success
                        floatingSaveBtn.innerHTML = '<span class="dashicons dashicons-yes"></span> Enregistré !';
                        floatingSaveBtn.style.background = 'linear-gradient(135deg, #28a745 0%, #1e7e34 100%)';

                        // Reset button after delay
                        setTimeout(() => {
                            floatingSaveBtn.disabled = false;
                            floatingSaveBtn.innerHTML = originalHTML;
                            floatingSaveBtn.style.background = '';
                            floatingSaveBtn.style.opacity = '1';
                        }, 3000);

                        // Show success notification if available
                        if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                            PDF_Builder_Notification_Manager.show_toast('Paramètres sauvegardés avec succès !', 'success');
                        }

                    } else {
                        // Show error
                        console.error('Save failed:', data.data?.message || 'Unknown error');
                        floatingSaveBtn.innerHTML = '<span class="dashicons dashicons-no"></span> Erreur';
                        floatingSaveBtn.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';

                        // Reset button after delay
                        setTimeout(() => {
                            floatingSaveBtn.disabled = false;
                            floatingSaveBtn.innerHTML = originalHTML;
                            floatingSaveBtn.style.background = '';
                            floatingSaveBtn.style.opacity = '1';
                        }, 5000);

                        // Show error notification if available
                        if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                            PDF_Builder_Notification_Manager.show_toast('Erreur lors de la sauvegarde: ' + (data.data?.message || 'Erreur inconnue'), 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('AJAX error:', error);
                    floatingSaveBtn.innerHTML = '<span class="dashicons dashicons-no"></span> Erreur réseau';
                    floatingSaveBtn.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';

                    // Reset button after delay
                    setTimeout(() => {
                        floatingSaveBtn.disabled = false;
                        floatingSaveBtn.innerHTML = originalHTML;
                        floatingSaveBtn.style.background = '';
                        floatingSaveBtn.style.opacity = '1';
                    }, 5000);

                    // Show error notification if available
                    if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                        PDF_Builder_Notification_Manager.show_toast('Erreur réseau lors de la sauvegarde', 'error');
                    }
                });
            });
        }
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
            initializeModals();
        });
    } else {
        initializeModals();
    }
})();
</script>



<?php require_once __DIR__ . '/tab-diagnostic.php'; ?>
