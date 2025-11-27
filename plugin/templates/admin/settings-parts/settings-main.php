<?php
/**
 * PDF Builder Pro - Main Settings Logic
 * Core settings processing and HTML structure
 * Updated: 2025-11-18 20:10:00
 */

// DEBUG: Log que le fichier est chargé
error_log('PDF Builder: settings-main.php loaded at ' . date('Y-m-d H:i:s'));

echo '<script>console.log("PDF Builder: PHP file loaded and script tag executed");</script>';


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
window.pdfBuilderSavedSettings = <?php echo json_encode($preview_data); ?>;
window.pdfBuilderCanvasSettings = <?php echo json_encode($canvas_settings); ?>;

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
    const PDF_BUILDER_DEBUG_ENABLED = <?php echo isset($settings['debug_javascript']) && $settings['debug_javascript'] ? 'true' : 'false'; ?>;
    const PDF_BUILDER_DEBUG_VERBOSE = <?php echo isset($settings['debug_javascript_verbose']) && $settings['debug_javascript_verbose'] ? 'true' : 'false'; ?>;
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
            zoomLevel.textContent = `${defaultZoom}%`;
            pdfBuilderDebug('Updated zoom level to:', defaultZoom + '%');
        } else {
            pdfBuilderDebug('zoomLevel element not found');
        }

        // Update zoom info
        const zoomInfo = document.querySelector('.zoom-info');
        if (zoomInfo) {
            zoomInfo.innerHTML = `
                <span>${minZoom}% - ${maxZoom}%</span>
                <span>Pas: ${stepZoom}%</span>
            `;
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
console.log('PDF Builder: About to add DOMContentLoaded listener');
document.addEventListener('DOMContentLoaded', function() {
    console.log('PDF Builder: ===== DOMContentLoaded FIRED =====');
    console.log('PDF Builder: DOMContentLoaded fired - starting tab initialization');

    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    console.log('PDF Builder: Found tabs count:', tabs.length);
    console.log('PDF Builder: Found contents count:', contents.length);
    console.log('PDF Builder: Tabs elements:', Array.from(tabs).map(tab => ({href: tab.getAttribute('href'), element: tab})));
    console.log('PDF Builder: Contents elements:', Array.from(contents).map(content => ({id: content.id, element: content})));

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
    function initializeTabs() {
        console.log('PDF Builder: initializeTabs() function called');

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
            });

            console.log('PDF Builder: Adding active classes to target elements');
            // Add active classes to target tab and content
            console.log('PDF Builder: Adding nav-tab-active to activeTab:', activeTab);
            activeTab.classList.add('nav-tab-active');
            console.log('PDF Builder: Adding active to activeContent:', activeContent);
            activeContent.classList.add('active');

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
        } else {
            console.error('PDF Builder: Could not find activeTab or activeContent for targetTab:', targetTab);
            console.log('PDF Builder: Available tabs:', Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({href: tab.getAttribute('href'), element: tab})));
            console.log('PDF Builder: Available contents:', Array.from(document.querySelectorAll('.tab-content')).map(content => ({id: content.id, element: content})));
        }
    }

    // Initialize tabs on load
    console.log('PDF Builder: About to call initializeTabs()');
    initializeTabs();
    console.log('PDF Builder: initializeTabs() completed');

    console.log('PDF Builder: Tabs initialization completed');
    console.log('PDF Builder: ===== FINAL STATE CHECK =====');
    console.log('PDF Builder: Final state - active tabs:', Array.from(document.querySelectorAll('.nav-tab-active')).map(tab => ({href: tab.getAttribute('href'), element: tab})));
    console.log('PDF Builder: Final state - active contents:', Array.from(document.querySelectorAll('.tab-content.active')).map(content => ({id: content.id, element: content})));
    console.log('PDF Builder: Final state - all tabs:', Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({href: tab.getAttribute('href'), classes: tab.classList.toString()})));
    console.log('PDF Builder: Final state - all contents:', Array.from(document.querySelectorAll('.tab-content')).map(content => ({id: content.id, classes: content.classList.toString()})));
    console.log('PDF Builder: ===== END FINAL STATE CHECK =====');

    // Fonction pour mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Sécurité
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
                const selectedOption = select.querySelector(`option[value="${selectValue}"]`);
                const templateName = selectedOption ? selectedOption.textContent : 'Template inconnu';
                
                previewDiv.innerHTML = `
                    <p class="current-template">
                        <strong>Assigné :</strong> ${templateName}
                        <span class="assigned-badge">✓</span>
                    </p>
                `;
            } else {
                // Aucun template assigné
                previewDiv.innerHTML = `
                    <p class="no-template">Aucun template assigné</p>
                `;
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

    // Initialiser les indicateurs au chargement de la page
    updateSecurityStatusIndicators();
    toggleRGPDControls();
    updateTemplateLibraryIndicator();

    // Ajouter un event listener pour le toggle RGPD principal
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    if (gdprEnabledCheckbox) {
        gdprEnabledCheckbox.addEventListener('change', function() {
            toggleRGPDControls();
            // Removed: updateSecurityStatusIndicators(); // Ne plus mettre à jour l'indicateur lors du toggle
        });
    }

    // Ajouter des event listeners pour les indicateurs en temps réel
    const enableLoggingCheckbox = document.getElementById('enable_logging');
    if (enableLoggingCheckbox) {
        enableLoggingCheckbox.addEventListener('change', updateSecurityStatusIndicators);
    }

    const cacheEnabledCheckbox = document.getElementById('general_cache_enabled');
    if (cacheEnabledCheckbox) {
        cacheEnabledCheckbox.addEventListener('change', updateSystemStatusIndicators);
    }

    const maintenanceCheckbox = document.getElementById('systeme_auto_maintenance');
    if (maintenanceCheckbox) {
        maintenanceCheckbox.addEventListener('change', updateSystemStatusIndicators);
    }

    const backupCheckbox = document.getElementById('systeme_auto_backup');
    if (backupCheckbox) {
        backupCheckbox.addEventListener('change', updateSystemStatusIndicators);
    }

    // Gestion du bouton flottant de sauvegarde
    const floatingSaveBtn = document.getElementById('floating-save-btn');
    if (floatingSaveBtn) {
        floatingSaveBtn.addEventListener('click', function() {
            // Vérifier que pdf_builder_ajax est défini
            if (typeof pdf_builder_ajax === 'undefined') {
                alert('Erreur: Configuration AJAX manquante. Actualisez la page.');
                return;
            }

            // Changer l'apparence du bouton pendant la sauvegarde
            const originalText = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>'; // Texte fixe original
            floatingSaveBtn.innerHTML = '<span class="save-icon">⏳</span><span class="save-text">Sauvegarde...</span>';
            floatingSaveBtn.classList.add('saving');

            // Timeout de sécurité : remettre le bouton à l'état normal après 5 secondes maximum
            const safetyTimeout = setTimeout(() => {
                floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                floatingSaveBtn.classList.remove('saving', 'saved', 'error');
            }, 5000);

            // Collecter les données de tous les formulaires
            const formData = new FormData();

            // Ajouter l'action AJAX
            formData.append('action', 'pdf_builder_save_settings');
            formData.append('nonce', pdf_builder_ajax?.nonce || '');
            formData.append('current_tab', 'all'); // Sauvegarder tous les onglets

            // Collecter les données de TOUS les formulaires (pas seulement visibles)
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const formInputs = form.querySelectorAll('input, select, textarea');

                // Collecter d'abord les checkboxes multiples (arrays)
                const checkboxArrays = {};
                formInputs.forEach(input => {
                    if (input.type === 'checkbox' && input.name && input.name.endsWith('[]')) {
                        const baseName = input.name.slice(0, -2); // Retirer []
                        if (!checkboxArrays[baseName]) {
                            checkboxArrays[baseName] = [];
                        }
                        // Inclure même les checkboxes disabled si elles sont checked
                        if (input.checked) {
                            checkboxArrays[baseName].push(input.value);
                        }
                    }
                });

                // Ajouter les arrays de checkboxes
                Object.keys(checkboxArrays).forEach(name => {
                    if (checkboxArrays[name].length > 0) {
                        checkboxArrays[name].forEach(value => {
                            formData.append(name + '[]', value);
                        });
                    } else {
                        // Si aucun checkbox coché, envoyer un array vide
                        formData.append(name + '[]', '');
                    }
                });

                // Collecter les autres inputs
                formInputs.forEach(input => {
                    if (input.name && input.type !== 'submit' && input.type !== 'button' &&
                        input.name !== 'action' && input.name !== 'nonce' && input.name !== 'current_tab' &&
                        !input.name.endsWith('[]')) { // Ne pas traiter les arrays ici
                        if (input.type === 'checkbox') {
                            formData.append(input.name, input.checked ? '1' : '0');
                        } else if (input.type === 'radio') {
                            if (input.checked) {
                                formData.append(input.name, input.value);
                            }
                        } else {
                            formData.append(input.name, input.value);
                        }
                    }
                });
            });

            // Envoyer la requête AJAX
            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                clearTimeout(safetyTimeout); // Annuler le timeout de sécurité

                if (data.success) {
                    // Succès
                    floatingSaveBtn.innerHTML = '<span class="save-icon">✅</span><span class="save-text">Sauvegardé !</span>';
                    floatingSaveBtn.classList.remove('saving');
                    floatingSaveBtn.classList.add('saved');

                    // Mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Sécurité & Conformité
                    updateSecurityStatusIndicators();

                    // Mettre à jour l'état des contrôles RGPD
                    toggleRGPDControls();

                    // Mettre à jour les indicateurs des templates assignés
                    updateTemplateStatusIndicators();

                    // Mettre à jour l'indicateur de bibliothèque de templates
                    updateTemplateLibraryIndicator();

                    // Remettre le texte original après 2 secondes
                    setTimeout(() => {
                        floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                        floatingSaveBtn.classList.remove('saved');
                    }, 2000);

                    // Notification de succès déjà gérée par le changement d'apparence du bouton
                    // La notification popup a été supprimée pour éviter les doublons
                } else {
                    // Erreur
                    
                    const errorMessage = data.data && data.data.message ? data.data.message : 'Erreur inconnue';
                    
                    alert('Erreur de sauvegarde: ' + errorMessage);
                    floatingSaveBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur</span>';
                    floatingSaveBtn.classList.remove('saving');
                    floatingSaveBtn.classList.add('error');

                    // Remettre le texte original après 3 secondes
                    setTimeout(() => {
                        floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                        floatingSaveBtn.classList.remove('error');
                    }, 3000);

                    // Afficher l'erreur
                    if (data.data && data.data.message) {
                        alert('Erreur de sauvegarde: ' + data.data.message);
                    }
                }
            })
            .catch(error => {
                
                clearTimeout(safetyTimeout); // Annuler le timeout de sécurité
                floatingSaveBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur</span>';
                floatingSaveBtn.classList.remove('saving');
                floatingSaveBtn.classList.add('error');

                setTimeout(() => {
                    floatingSaveBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                    floatingSaveBtn.classList.remove('error');
                }, 3000);

                alert('Erreur de connexion lors de la sauvegarde');
            });
        });
    }

    // Initialize zoom card preview with real values
    updateZoomCardPreview();

    // Initialize all canvas card previews with real values
    // Use setTimeout to ensure window.pdfBuilderCanvasSettings is loaded
    setTimeout(function() {
        try {
            if (window.updateCanvasPreviews) {
                window.updateCanvasPreviews('all');
            } else if (window.CanvasPreviewManager && typeof window.CanvasPreviewManager.updatePreviews === 'function') {
                window.CanvasPreviewManager.updatePreviews('all');
            } else {
                // Fallback: call individual update functions if they exist
                const updateFunctions = [
                    'updateDimensionsCardPreview', 'updateApparenceCardPreview', 'updatePerformanceCardPreview',
                    'updateAutosaveCardPreview', 'updateZoomCardPreview', 'updateGrilleCardPreview',
                    'updateInteractionsCardPreview', 'updateExportCardPreview'
                ];
                updateFunctions.forEach(funcName => {
                    if (typeof window[funcName] === 'function') {
                        try {
                            window[funcName]();
                        } catch (error) {
                            pdfBuilderError('Error calling ' + funcName + ':', error);
                        }
                    }
                });
            }
        } catch (error) {
            pdfBuilderError('Error initializing canvas previews:', error);
        }
    }, 500);
});

// Canvas configuration modals functionality - Version stable
{
    'use strict';

    // Configuration
    const MODAL_Z_INDEX = 2147483647; // Maximum z-index possible (signed 32-bit integer)
    const MODAL_CONFIG = {
        display: 'flex',
        visibility: 'visible',
        opacity: '1',
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        background: 'rgba(0,0,0,0.7)',
        'z-index': MODAL_Z_INDEX.toString()
    };

    let isInitialized = false;

    // Utility functions
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

    function applyModalStyles(modal) {
        if (!modal) return false;

        try {
            // Reset all styles first
            modal.style.cssText = '';

            // Apply configuration with !important
            Object.keys(MODAL_CONFIG).forEach(property => {
                modal.style.setProperty(property, MODAL_CONFIG[property], 'important');
            });

            // Additional safety styles
            modal.style.setProperty('pointer-events', 'auto', 'important');
            modal.style.setProperty('align-items', 'center', 'important');
            modal.style.setProperty('justify-content', 'center', 'important');

            return true;
        } catch (e) {
            
            return false;
        }
    }

    function hideModal(modal) {
        if (!modal) return;
        try {
            // Restore original settings if dimensions or apparence modal was cancelled
            if (modal.getAttribute('data-category') === 'dimensions' && modal._originalDimensionsSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.default_canvas_format = modal._originalDimensionsSettings.format;
                    window.pdfBuilderCanvasSettings.default_canvas_dpi = modal._originalDimensionsSettings.dpi;
                    
                    // Update preview with restored values
                    if (typeof updateDimensionsCardPreview === 'function') {
                        updateDimensionsCardPreview();
                    }
                }
                delete modal._originalDimensionsSettings;
            }
            if (modal.getAttribute('data-category') === 'apparence' && modal._originalApparenceSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.canvas_background_color = modal._originalApparenceSettings.bgColor;
                    window.pdfBuilderCanvasSettings.border_color = modal._originalApparenceSettings.borderColor;
                    window.pdfBuilderCanvasSettings.border_width = modal._originalApparenceSettings.borderWidth;
                    window.pdfBuilderCanvasSettings.shadow_enabled = modal._originalApparenceSettings.shadowEnabled;
                    window.pdfBuilderCanvasSettings.container_background_color = modal._originalApparenceSettings.containerBgColor;
                    
                    // Update preview with restored values
                    if (typeof updateApparenceCardPreview === 'function') {
                        updateApparenceCardPreview();
                    }
                }
                delete modal._originalApparenceSettings;
            }
            if (modal.getAttribute('data-category') === 'interactions' && modal._originalInteractionsSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.drag_enabled = modal._originalInteractionsSettings.dragEnabled;
                    window.pdfBuilderCanvasSettings.resize_enabled = modal._originalInteractionsSettings.resizeEnabled;
                    window.pdfBuilderCanvasSettings.rotate_enabled = modal._originalInteractionsSettings.rotateEnabled;
                    window.pdfBuilderCanvasSettings.multi_select = modal._originalInteractionsSettings.multiSelect;
                    window.pdfBuilderCanvasSettings.selection_mode = modal._originalInteractionsSettings.selectionMode;
                    window.pdfBuilderCanvasSettings.keyboard_shortcuts = modal._originalInteractionsSettings.keyboardShortcuts;
                    
                    // Update preview with restored values
                    if (typeof updateInteractionsCardPreview === 'function') {
                        updateInteractionsCardPreview();
                    }
                }
                delete modal._originalInteractionsSettings;
            }
            if (modal.getAttribute('data-category') === 'autosave' && modal._originalAutosaveSettings) {
                if (window.pdfBuilderCanvasSettings) {
                    window.pdfBuilderCanvasSettings.autosave_enabled = modal._originalAutosaveSettings.autosaveEnabled;
                    window.pdfBuilderCanvasSettings.autosave_interval = modal._originalAutosaveSettings.autosaveInterval;
                    window.pdfBuilderCanvasSettings.versions_limit = modal._originalAutosaveSettings.versionsLimit;
                    
                    // Update preview with restored values
                    if (typeof updateAutosaveCardPreview === 'function') {
                        updateAutosaveCardPreview();
                    }
                }
                delete modal._originalAutosaveSettings;
            }

            modal.style.setProperty('display', 'none', 'important');
        } catch (e) {
            
        }
    }

    function showModal(modal) {
        if (!modal) return false;

        try {
            const success = applyModalStyles(modal);

            if (success) {
                // Initialize event listeners for this modal
                initializeModalEventListeners(modal);

                // Synchronize modal values with current settings for apparence modal
                if (modal.getAttribute('data-category') === 'apparence') {
                    synchronizeApparenceModalValues(modal);
                }

                // Synchronize modal values with current settings for interactions modal
                if (modal.getAttribute('data-category') === 'interactions') {
                    synchronizeInteractionsModalValues(modal);
                }

                // Synchronize modal values with current settings for autosave modal
                if (modal.getAttribute('data-category') === 'autosave') {
                    synchronizeAutosaveModalValues(modal);
                }

                // Verify modal is visible after a short delay
                setTimeout(() => {
                    const rect = modal.getBoundingClientRect();
                    const isVisible = rect.width > 0 && rect.height > 0;

                    if (!isVisible) {
                        
                    }
                }, 100);
            }

            return success;
        } catch (e) {
            
            return false;
        }
    }

    function initializeModalEventListeners(modal) {
        if (!modal) return;

        // Utiliser le système centralisé CanvasPreviewManager
        if (window.CanvasPreviewManager && typeof window.CanvasPreviewManager.initializeRealTimeUpdates === 'function') {
            window.CanvasPreviewManager.initializeRealTimeUpdates(modal);
        } else {
            // Fallback: initialize basic event listeners
            pdfBuilderDebug('CanvasPreviewManager not available, using fallback initialization');
        }
    }

    function initializeModals() {
        if (isInitialized) return;

        try {
            // Hide all modals by default
            const allModals = safeQuerySelectorAll('.canvas-modal');
            allModals.forEach(hideModal);

            // Use event delegation for better stability
            document.addEventListener('click', function(event) {
                const target = event.target;

                // Handle configure buttons
                if (target.closest('.canvas-configure-btn')) {
                    event.preventDefault();
                    event.stopPropagation();

                    const button = target.closest('.canvas-configure-btn');
                    const card = button.closest('.canvas-card');

                    if (!card) {
                        
                        return;
                    }

                    const category = card.getAttribute('data-category');
                    if (!category) {
                        
                        return;
                    }

                    const modalId = 'canvas-' + category + '-modal';
                    const modal = document.getElementById(modalId);

                    if (!modal) {
                        
                        return;
                    }

                    
                    const success = showModal(modal);

                    if (success) {
                        // Modal opened successfully - update values from database
                        
                        // Always refresh modal values from database when opening
                        if (typeof updateCanvasPreviews === 'function') {
                            updateCanvasPreviews(category);
                        }
                    }
                }

                // Handle close buttons
                if (target.closest('.canvas-modal-close, .canvas-modal-cancel')) {
                    const modal = target.closest('.canvas-modal');
                    if (modal) {
                        hideModal(modal);
                    }
                }

                // Handle modal background click to close
                if (target.classList.contains('canvas-modal') || target.classList.contains('canvas-modal-overlay')) {
                    hideModal(target.closest('.canvas-modal'));
                }
            }, true); // Use capture phase for better event handling

            // Handle escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const visibleModals = safeQuerySelectorAll('.canvas-modal[style*="display: flex"]');
                    visibleModals.forEach(hideModal);
                }
            });

            // Handle save buttons with proper error handling
            const saveButtons = safeQuerySelectorAll('.canvas-modal-save');
            saveButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();

                    const modal = this.closest('.canvas-modal');
                    const category = this.getAttribute('data-category');
                    const form = modal ? modal.querySelector('form') : null;

                    if (!form) {
                        alert('Erreur: Formulaire non trouvé');
                        return;
                    }

                    // Get AJAX config with fallbacks
                    let ajaxConfig = null;
                    if (typeof pdf_builder_ajax !== 'undefined') {
                        ajaxConfig = pdf_builder_ajax;
                    } else if (typeof pdfBuilderAjax !== 'undefined') {
                        ajaxConfig = pdfBuilderAjax;
                    } else if (typeof ajaxurl !== 'undefined') {
                        ajaxConfig = { ajax_url: ajaxurl, nonce: '' };
                    }

                    if (!ajaxConfig || !ajaxConfig.ajax_url) {
                        alert('Erreur de configuration AJAX: variables AJAX non trouvées');
                        
                        return;
                    }

                    // Collect form data safely
                    let formData;
                    try {
                        formData = new FormData(form);
                        formData.append('action', 'pdf_builder_save_canvas_settings');
                        formData.append('category', category || '');
                        formData.append('nonce', ajaxConfig.nonce || '');

                        // Debug: Log form data
                        pdfBuilderDebug('Form data for category', category + ':');
                        if (PDF_BUILDER_DEBUG_VERBOSE) {
                            for (let [key, value] of formData.entries()) {
                                pdfBuilderDebug(key, '=', value);
                            }
                        }

                        

                    } catch (e) {
                        
                        alert('Erreur lors de la préparation des données');
                        return;
                    }

                    // Show loading state
                    const originalText = this.textContent;
                    this.textContent = 'Sauvegarde...';
                    this.disabled = true;

                    // Send AJAX request with timeout
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

                    fetch(ajaxConfig.ajax_url, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        signal: controller.signal
                    })
                    .then(response => {
                        clearTimeout(timeoutId);
                        pdfBuilderDebug('AJAX response status:', response.status);
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        pdfBuilderDebug('AJAX response data:', data);
                        
                        if (data.success) {
                            hideModal(modal);
                            this.textContent = originalText;
                            this.disabled = false;

                            // Clear original settings since save was successful
                            delete modal._originalDimensionsSettings;
                            delete modal._originalApparenceSettings;
                            delete modal._originalInteractionsSettings;
                            delete modal._originalAutosaveSettings;

                            // Update window.pdfBuilderCanvasSettings with saved values for dimensions
                            if (category === 'dimensions' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_width) {
                                    window.pdfBuilderCanvasSettings.canvas_width = parseInt(data.data.saved.canvas_width);
                                }
                                if (data.data.saved.canvas_height) {
                                    window.pdfBuilderCanvasSettings.canvas_height = parseInt(data.data.saved.canvas_height);
                                }
                                if (data.data.saved.canvas_format) {
                                    window.pdfBuilderCanvasSettings.default_canvas_format = data.data.saved.canvas_format;
                                }
                                if (data.data.saved.canvas_dpi) {
                                    window.pdfBuilderCanvasSettings.default_canvas_dpi = parseInt(data.data.saved.canvas_dpi);
                                }
                                if (data.data.saved.canvas_orientation) {
                                    window.pdfBuilderCanvasSettings.default_canvas_orientation = data.data.saved.canvas_orientation;
                                }

                                // Déclencher l'événement pour mettre à jour l'éditeur React
                                const event = new CustomEvent('pdfBuilderUpdateCanvasDimensions', {
                                    detail: {
                                        width: parseInt(data.data.saved.canvas_width),
                                        height: parseInt(data.data.saved.canvas_height)
                                    }
                                });
                                document.dispatchEvent(event);
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for apparence
                            if (category === 'apparence' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_bg_color !== undefined) {
                                    window.pdfBuilderCanvasSettings.canvas_background_color = data.data.saved.canvas_bg_color;
                                }
                                if (data.data.saved.canvas_border_color !== undefined) {
                                    window.pdfBuilderCanvasSettings.border_color = data.data.saved.canvas_border_color;
                                }
                                if (data.data.saved.canvas_border_width !== undefined) {
                                    window.pdfBuilderCanvasSettings.border_width = parseInt(data.data.saved.canvas_border_width);
                                }
                                if (data.data.saved.canvas_shadow_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.shadow_enabled = data.data.saved.canvas_shadow_enabled === '1' || data.data.saved.canvas_shadow_enabled === true;
                                }
                                if (data.data.saved.canvas_container_bg_color !== undefined) {
                                    window.pdfBuilderCanvasSettings.container_background_color = data.data.saved.canvas_container_bg_color;
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for performance
                            if (category === 'performance' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_fps_target !== undefined) {
                                    window.pdfBuilderCanvasSettings.fps_target = parseInt(data.data.saved.canvas_fps_target);
                                }
                                if (data.data.saved.canvas_memory_limit_js !== undefined) {
                                    window.pdfBuilderCanvasSettings.memory_limit_js = parseInt(data.data.saved.canvas_memory_limit_js);
                                }
                                if (data.data.saved.canvas_memory_limit_php !== undefined) {
                                    window.pdfBuilderCanvasSettings.memory_limit_php = parseInt(data.data.saved.canvas_memory_limit_php);
                                }
                                if (data.data.saved.canvas_lazy_loading_editor !== undefined) {
                                    window.pdfBuilderCanvasSettings.lazy_loading_editor = data.data.saved.canvas_lazy_loading_editor === '1' || data.data.saved.canvas_lazy_loading_editor === true;
                                }
                                if (data.data.saved.canvas_lazy_loading_plugin !== undefined) {
                                    window.pdfBuilderCanvasSettings.lazy_loading_plugin = data.data.saved.canvas_lazy_loading_plugin === '1' || data.data.saved.canvas_lazy_loading_plugin === true;
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for autosave
                            if (category === 'autosave' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_autosave_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.autosave_enabled = data.data.saved.canvas_autosave_enabled === '1' || data.data.saved.canvas_autosave_enabled === true;
                                }
                                if (data.data.saved.canvas_autosave_interval !== undefined) {
                                    window.pdfBuilderCanvasSettings.autosave_interval = parseInt(data.data.saved.canvas_autosave_interval);
                                }
                                if (data.data.saved.canvas_versions_limit !== undefined) {
                                    window.pdfBuilderCanvasSettings.versions_limit = parseInt(data.data.saved.canvas_versions_limit);
                                }
                                if (data.data.saved.canvas_history_max !== undefined) {
                                    window.pdfBuilderCanvasSettings.versions_limit = parseInt(data.data.saved.canvas_history_max);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for export
                            if (category === 'export' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_export_format !== undefined) {
                                    window.pdfBuilderCanvasSettings.export_format = data.data.saved.canvas_export_format;
                                }
                                if (data.data.saved.canvas_export_quality !== undefined) {
                                    window.pdfBuilderCanvasSettings.export_quality = parseInt(data.data.saved.canvas_export_quality);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for zoom
                            if (category === 'zoom' && data.data && data.data.saved) {
                                if (data.data.saved.zoom_min !== undefined) {
                                    window.pdfBuilderCanvasSettings.min_zoom = parseInt(data.data.saved.zoom_min);
                                }
                                if (data.data.saved.zoom_max !== undefined) {
                                    window.pdfBuilderCanvasSettings.max_zoom = parseInt(data.data.saved.zoom_max);
                                }
                                if (data.data.saved.zoom_default !== undefined) {
                                    window.pdfBuilderCanvasSettings.default_zoom = parseInt(data.data.saved.zoom_default);
                                }
                                if (data.data.saved.zoom_step !== undefined) {
                                    window.pdfBuilderCanvasSettings.zoom_step = parseInt(data.data.saved.zoom_step);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for grille
                            if (category === 'grille' && data.data && data.data.saved) {
                                if (data.data.saved.canvas_grid_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.show_grid = data.data.saved.canvas_grid_enabled === '1' || data.data.saved.canvas_grid_enabled === true;
                                }
                                if (data.data.saved.canvas_grid_size !== undefined) {
                                    window.pdfBuilderCanvasSettings.grid_size = parseInt(data.data.saved.canvas_grid_size);
                                }
                                if (data.data.saved.canvas_snap_to_grid !== undefined) {
                                    window.pdfBuilderCanvasSettings.snap_to_grid = data.data.saved.canvas_snap_to_grid === '1' || data.data.saved.canvas_snap_to_grid === true;
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings with saved values for interactions
                            if (category === 'interactions' && data.data && data.data.saved) {
                                pdfBuilderDebug('Updating interactions settings with:', data.data.saved);
                                if (data.data.saved.canvas_drag_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.drag_enabled = data.data.saved.canvas_drag_enabled === '1' || data.data.saved.canvas_drag_enabled === true;
                                }
                                if (data.data.saved.canvas_resize_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.resize_enabled = data.data.saved.canvas_resize_enabled === '1' || data.data.saved.canvas_resize_enabled === true;
                                }
                                if (data.data.saved.canvas_rotate_enabled !== undefined) {
                                    window.pdfBuilderCanvasSettings.rotate_enabled = data.data.saved.canvas_rotate_enabled === '1' || data.data.saved.canvas_rotate_enabled === true;
                                }
                                if (data.data.saved.canvas_multi_select !== undefined) {
                                    window.pdfBuilderCanvasSettings.multi_select = data.data.saved.canvas_multi_select === '1' || data.data.saved.canvas_multi_select === true;
                                }
                                if (data.data.saved.canvas_selection_mode !== undefined) {
                                    window.pdfBuilderCanvasSettings.selection_mode = data.data.saved.canvas_selection_mode;
                                }
                                if (data.data.saved.canvas_keyboard_shortcuts !== undefined) {
                                    window.pdfBuilderCanvasSettings.keyboard_shortcuts = data.data.saved.canvas_keyboard_shortcuts === '1' || data.data.saved.canvas_keyboard_shortcuts === true;
                                }
                                pdfBuilderDebug('Updated window.pdfBuilderCanvasSettings:', window.pdfBuilderCanvasSettings);
                            }

                            // Update canvas previews after successful save
                            if (category === 'dimensions' && typeof window.updateDimensionsCardPreview === 'function') {
                                try {
                                    window.updateDimensionsCardPreview();
                                } catch (error) {
                                    console.error('PDF_BUILDER_DEBUG: Error calling updateDimensionsCardPreview:', error);
                                }
                            }
                            if (category === 'apparence' && typeof window.updateApparenceCardPreview === 'function') {
                                setTimeout(function() {
                                    try {
                                        window.updateApparenceCardPreview();
                                    } catch (error) {
                                        console.error('PDF_BUILDER_DEBUG: Error calling updateApparenceCardPreview:', error);
                                    }
                                }, 100);
                            }
                            if (category === 'performance' && typeof window.updatePerformanceCardPreview === 'function') {
                                console.log('PDF_BUILDER_DEBUG: Calling updatePerformanceCardPreview');
                                setTimeout(function() {
                                    window.updatePerformanceCardPreview();
                                }, 100);
                            }
                            if (category === 'autosave' && typeof window.updateAutosaveCardPreview === 'function') {
                                console.log('PDF_BUILDER_DEBUG: Calling updateAutosaveCardPreview');
                                setTimeout(function() {
                                    window.updateAutosaveCardPreview();
                                }, 100);
                            }
                            if (category === 'export' && typeof window.updateExportCardPreview === 'function') {
                                console.log('PDF_BUILDER_DEBUG: Calling updateExportCardPreview');
                                setTimeout(function() {
                                    window.updateExportCardPreview();
                                }, 100);
                            }
                            if (category === 'zoom' && typeof window.updateZoomCardPreview === 'function') {
                                console.log('PDF_BUILDER_DEBUG: Calling updateZoomCardPreview');
                                setTimeout(function() {
                                    window.updateZoomCardPreview();
                                }, 100);
                            }
                            if (category === 'grille' && typeof window.updateGrilleCardPreview === 'function') {
                                console.log('PDF_BUILDER_DEBUG: Calling updateGrilleCardPreview');
                                setTimeout(function() {
                                    window.updateGrilleCardPreview();
                                }, 100);
                            }
                            if (category === 'interactions' && typeof window.updateInteractionsCardPreview === 'function') {
                                console.log('PDF_BUILDER_DEBUG: Calling updateInteractionsCardPreview');
                                setTimeout(function() {
                                    updateInteractionsCardPreview();
                                }, 100);
                            }
                        } else {
                            const errorMessage = (data.data && data.data.message) || 'Unknown error during save';
                            if (window.pdfBuilderNotifications) {
                                if (window.pdfBuilderNotifications.showToast) {
                                    window.pdfBuilderNotifications.showToast('Save error: ' + errorMessage, 'error', 6000);
                                }
                            }
                            if (window.PDF_Builder_Notification_Manager) {
                                if (window.PDF_Builder_Notification_Manager.show_toast) {
                                    window.PDF_Builder_Notification_Manager.show_toast('Save error: ' + errorMessage, 'error', 6000);
                                }
                            }
                            throw new Error(errorMessage);
                        }
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        console.log('PDF_BUILDER_DEBUG: AJAX error:', error);
                        
                        this.textContent = originalText;
                        this.disabled = false;

                        if (error.name === 'AbortError') {
                            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                                window.pdfBuilderNotifications.showToast('Erreur: Timeout de la requête (30 secondes)', 'error', 6000);
                            } else if (window.PDF_Builder_Notification_Manager) {
                                window.PDF_Builder_Notification_Manager.show_toast('Erreur: Timeout de la requête (30 secondes)', 'error', 6000);
                            }
                        } else {
                            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                                window.pdfBuilderNotifications.showToast('Erreur lors de la sauvegarde: ' + error.message, 'error', 6000);
                            } else if (window.PDF_Builder_Notification_Manager) {
                                window.PDF_Builder_Notification_Manager.show_toast('Erreur lors de la sauvegarde: ' + error.message, 'error', 6000);
                            }
                        }
            });

            });
});
            // Initialize zoom preview if function exists
            // Removed automatic updateZoomPreview call to prevent conflicts with manual modal updates
            // if (typeof updateZoomPreview === 'function') {
            //     // Delay initialization to ensure DOM is ready
            //     setTimeout(updateZoomPreview, 1000);
            // }

            isInitialized = true;
            

        } catch (e) {
            
        }
    }

    // Function to update modal values in DOM
    function updateModalValues(category, values) {
        const modalId = `canvas-${category}-modal`;
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }

        // Update values based on category
        switch (category) {
            case 'grille':
                updateGrilleModal(modal, values);
                break;
            case 'dimensions':
                updateDimensionsModal(modal, values);
                break;
            case 'zoom':
                updateZoomModal(modal, values);
                break;
            case 'apparence':
                updateApparenceModal(modal, values);
                break;
            case 'interactions':
                updateInteractionsModal(modal, values);
                break;
            case 'export':
                updateExportModal(modal, values);
                break;
            case 'performance':
                updatePerformanceModal(modal, values);
                break;
            case 'autosave':
                updateAutosaveModal(modal, values);
                break;
            case 'debug':
                updateDebugModal(modal, values);
                break;
            default:
                console.warn('⚠️ Unknown category:', category);
        }
    }

    // Update grille modal values
    function updateGrilleModal(modal, values) {
        const isGridEnabled = values.grid_enabled === '1' || values.grid_enabled === true;

        // Update checkboxes
        const guidesCheckbox = modal.querySelector('#canvas_guides_enabled');
        if (guidesCheckbox) {
            guidesCheckbox.checked = values.guides_enabled === '1' || values.guides_enabled === true;
        }

        const gridCheckbox = modal.querySelector('#canvas_grid_enabled');
        if (gridCheckbox) {
            gridCheckbox.checked = isGridEnabled;
        }

        // Update grid size input
        const gridSizeInput = modal.querySelector('#canvas_grid_size');
        if (gridSizeInput) {
            gridSizeInput.value = values.grid_size || 20;
            gridSizeInput.disabled = !isGridEnabled;
        }

        // Update snap to grid checkbox
        const snapCheckbox = modal.querySelector('#canvas_snap_to_grid');
        if (snapCheckbox) {
            snapCheckbox.checked = values.snap_to_grid === '1' || values.snap_to_grid === true;
            snapCheckbox.disabled = !isGridEnabled;
        }

        // Update toggle switch visual states
        const gridToggle = gridCheckbox?.closest('.toggle-switch');
        const snapToggle = snapCheckbox?.closest('.toggle-switch');

        // Note: gridToggle should NEVER be disabled - it's the main control
        // Only dependent controls (snapToggle) should be disabled when grid is off
        if (snapToggle) {
            snapToggle.classList.toggle('disabled', !isGridEnabled);
        }
    }

    // Update apparence modal values
    function updateApparenceModal(modal, values) {
        

        // Update canvas background color
        const canvasBgColorInput = modal.querySelector('#canvas_bg_color');
        if (canvasBgColorInput && values.canvas_bg_color) {
            canvasBgColorInput.value = values.canvas_bg_color;
            
        }

        // Update container background color
        const containerBgColorInput = modal.querySelector('#canvas_container_bg_color');
        if (containerBgColorInput && values.canvas_container_bg_color) {
            containerBgColorInput.value = values.canvas_container_bg_color;
            
        }

        // Update border color
        const borderColorInput = modal.querySelector('#canvas_border_color');
        if (borderColorInput && values.canvas_border_color) {
            borderColorInput.value = values.canvas_border_color;
            
        }

        // Update border width
        const borderWidthInput = modal.querySelector('#canvas_border_width');
        if (borderWidthInput && values.canvas_border_width !== undefined) {
            borderWidthInput.value = values.canvas_border_width;
            
        }

        // Update shadow enabled checkbox
        const shadowCheckbox = modal.querySelector('#canvas_shadow_enabled');
        if (shadowCheckbox) {
            const isEnabled = values.canvas_shadow_enabled === '1' || values.canvas_shadow_enabled === true;
            shadowCheckbox.checked = isEnabled;
            
        }
    }
    function updateInteractionsModal(modal, values) {
        // Update drag enabled
        const dragCheckbox = modal.querySelector('#canvas_drag_enabled');
        if (dragCheckbox) {
            dragCheckbox.checked = values.drag_enabled === '1' || values.drag_enabled === true;
        }

        // Update resize enabled
        const resizeCheckbox = modal.querySelector('#canvas_resize_enabled');
        if (resizeCheckbox) {
            resizeCheckbox.checked = values.resize_enabled === '1' || values.resize_enabled === true;
        }

        // Update rotate enabled
        const rotateCheckbox = modal.querySelector('#canvas_rotate_enabled');
        if (rotateCheckbox) {
            rotateCheckbox.checked = values.rotate_enabled === '1' || values.rotate_enabled === true;
        }

        // Update multi select
        const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
        if (multiSelectCheckbox) {
            multiSelectCheckbox.checked = values.multi_select === '1' || values.multi_select === true;
        }

        // Update selection mode
        const selectionModeSelect = modal.querySelector('#canvas_selection_mode');
        if (selectionModeSelect) {
            selectionModeSelect.value = values.selection_mode || 'click';
        }

        // Update keyboard shortcuts
        const keyboardCheckbox = modal.querySelector('#canvas_keyboard_shortcuts');
        if (keyboardCheckbox) {
            keyboardCheckbox.checked = values.keyboard_shortcuts === '1' || values.keyboard_shortcuts === true;
        }

        // Apply dependency logic: disable selection mode when multi-select is disabled
        updateSelectionModeDependency(modal);
    }

    // Function to handle dependency between multi-select and selection mode
    function updateSelectionModeDependency(modal) {
        const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
        const selectionModeSelect = modal.querySelector('#canvas_selection_mode');
        const selectionModeLabel = modal.querySelector('label[for="canvas_selection_mode"]');

        if (!multiSelectCheckbox || !selectionModeSelect) return;

        const isMultiSelectEnabled = multiSelectCheckbox.checked;

        // Enable/disable selection mode based on multi-select
        selectionModeSelect.disabled = !isMultiSelectEnabled;

        // Update visual appearance
        if (isMultiSelectEnabled) {
            selectionModeSelect.style.opacity = '1';
            if (selectionModeLabel) {
                selectionModeLabel.style.opacity = '1';
            }
        } else {
            selectionModeSelect.style.opacity = '0.5';
            if (selectionModeLabel) {
                selectionModeLabel.style.opacity = '0.5';
            }
        }
    }

    // Function to initialize modal event listeners
    function initializeModalEventListeners(modal) {
        // Handle interactions modal dependencies
        if (modal.id === 'canvas-interactions-modal') {
            const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
            if (multiSelectCheckbox) {
                multiSelectCheckbox.addEventListener('change', function() {
                    updateSelectionModeDependency(modal);
                });
            }
        }

        // Note: Real-time preview updates have been removed.
        // Previews now only update after successful save operations.
    }
    function updateExportModal(modal, values) {
        // Update export format
        const formatSelect = modal.querySelector('#canvas_export_format');
        if (formatSelect && values.canvas_export_format) {
            formatSelect.value = values.canvas_export_format;
        }

        // Update export quality
        const qualityInput = modal.querySelector('#canvas_export_quality');
        if (qualityInput && values.canvas_export_quality !== undefined) {
            qualityInput.value = values.canvas_export_quality;
        }

        // Update transparent background checkbox
        const transparentCheckbox = modal.querySelector('#canvas_export_transparent');
        if (transparentCheckbox) {
            transparentCheckbox.checked = values.canvas_export_transparent === '1' || values.canvas_export_transparent === true;
        }
    }
    function updatePerformanceModal(modal, values) {
        // Update FPS target
        const fpsSelect = modal.querySelector('#canvas_fps_target');
        if (fpsSelect && values.canvas_fps_target) {
            fpsSelect.value = values.canvas_fps_target;
        }

        // Update memory limits
        const memoryJsSelect = modal.querySelector('#canvas_memory_limit_js');
        if (memoryJsSelect && values.canvas_memory_limit_js) {
            memoryJsSelect.value = values.canvas_memory_limit_js;
        }

        const memoryPhpSelect = modal.querySelector('#canvas_memory_limit_php');
        if (memoryPhpSelect && values.canvas_memory_limit_php) {
            memoryPhpSelect.value = values.canvas_memory_limit_php;
        }

        // Update timeout
        const timeoutSelect = modal.querySelector('#canvas_response_timeout');
        if (timeoutSelect && values.canvas_response_timeout) {
            timeoutSelect.value = values.canvas_response_timeout;
        }

        // Update checkboxes
        const lazyEditorCheckbox = modal.querySelector('#canvas_lazy_loading_editor');
        if (lazyEditorCheckbox) {
            lazyEditorCheckbox.checked = values.canvas_lazy_loading_editor === '1' || values.canvas_lazy_loading_editor === true;
        }

        const preloadCheckbox = modal.querySelector('#canvas_preload_critical');
        if (preloadCheckbox) {
            preloadCheckbox.checked = values.canvas_preload_critical === '1' || values.canvas_preload_critical === true;
        }

        const lazyPluginCheckbox = modal.querySelector('#canvas_lazy_loading_plugin');
        if (lazyPluginCheckbox) {
            lazyPluginCheckbox.checked = values.canvas_lazy_loading_plugin === '1' || values.canvas_lazy_loading_plugin === true;
        }
    }

    function updateZoomModal(modal, values) {
        // Update zoom minimum
        const zoomMinInput = modal.querySelector('#zoom_min');
        if (zoomMinInput && values.canvas_zoom_min !== undefined) {
            zoomMinInput.value = values.canvas_zoom_min;
        }

        // Update zoom maximum
        const zoomMaxInput = modal.querySelector('#zoom_max');
        if (zoomMaxInput && values.canvas_zoom_max !== undefined) {
            zoomMaxInput.value = values.canvas_zoom_max;
        }

        // Update zoom default
        const zoomDefaultInput = modal.querySelector('#zoom_default');
        if (zoomDefaultInput && values.canvas_zoom_default !== undefined) {
            zoomDefaultInput.value = values.canvas_zoom_default;
        }

        // Update zoom step
        const zoomStepInput = modal.querySelector('#zoom_step');
        if (zoomStepInput && values.canvas_zoom_step !== undefined) {
            zoomStepInput.value = values.canvas_zoom_step;
        }
    }

    function updateAutosaveModal(modal, values) {
        // Update autosave enabled
        const autosaveCheckbox = modal.querySelector('#canvas_autosave_enabled');
        if (autosaveCheckbox) {
            autosaveCheckbox.checked = values.canvas_autosave_enabled === '1' || values.canvas_autosave_enabled === true;
        }

        // Update autosave interval
        const intervalInput = modal.querySelector('#canvas_autosave_interval');
        if (intervalInput && values.canvas_autosave_interval !== undefined) {
            intervalInput.value = values.canvas_autosave_interval;
        }

        // Update history enabled
        const historyCheckbox = modal.querySelector('#canvas_history_enabled');
        if (historyCheckbox) {
            historyCheckbox.checked = values.canvas_history_enabled === '1' || values.canvas_history_enabled === true;
        }
    }
    function updateDebugModal(modal, values) {
        // Update debug enabled
        const debugCheckbox = modal.querySelector('#canvas_debug_enabled');
        if (debugCheckbox) {
            debugCheckbox.checked = values.canvas_debug_enabled === '1' || values.canvas_debug_enabled === true;
        }

        // Update performance monitoring
        const perfCheckbox = modal.querySelector('#canvas_performance_monitoring');
        if (perfCheckbox) {
            perfCheckbox.checked = values.canvas_performance_monitoring === '1' || values.canvas_performance_monitoring === true;
        }

        // Update error reporting
        const errorCheckbox = modal.querySelector('#canvas_error_reporting');
        if (errorCheckbox) {
            errorCheckbox.checked = values.canvas_error_reporting === '1' || values.canvas_error_reporting === true;
        }
    }

    // Update dimensions modal values
    function updateDimensionsModal(modal, values) {
        // Update format select
        const formatSelect = modal.querySelector('#canvas_format');
        if (formatSelect && values.format) {
            formatSelect.value = values.format;
        }

        // Update DPI select
        const dpiSelect = modal.querySelector('#canvas_dpi');
        if (dpiSelect && values.dpi) {
            dpiSelect.value = values.dpi;
        }

        // Update calculated dimensions display
        updateCalculatedDimensions(modal, values.format || 'A4', values.dpi || 96);
    }

    // Function to update calculated dimensions display
    function updateCalculatedDimensions(modal, format, dpi) {
        // Utiliser les dimensions standard centralisées
        const formatDimensionsMM = window.pdfBuilderPaperFormats || {
            'A4': { width: 210, height: 297 },
            'A3': { width: 297, height: 420 },
            'A5': { width: 148, height: 210 },
            'Letter': { width: 215.9, height: 279.4 },
            'Legal': { width: 215.9, height: 355.6 },
            'Tabloid': { width: 279.4, height: 431.8 }
        };

        const dimensions = formatDimensionsMM[format] || formatDimensionsMM['A4'];

        // Calculer les dimensions en pixels (1 inch = 25.4mm, 1 inch = dpi pixels)
        const pixelsPerMM = dpi / 25.4;
        const widthPx = Math.round(dimensions.width * pixelsPerMM);
        const heightPx = Math.round(dimensions.height * pixelsPerMM);

        // Update pixel dimensions display
        const widthDisplay = modal.querySelector('#canvas-width-display');
        const heightDisplay = modal.querySelector('#canvas-height-display');
        if (widthDisplay) widthDisplay.textContent = widthPx;
        if (heightDisplay) heightDisplay.textContent = heightPx;

        // Update mm dimensions display
        const mmDisplay = modal.querySelector('#canvas-mm-display');
        if (mmDisplay) {
            mmDisplay.textContent = dimensions.width + '×' + dimensions.height + 'mm';
        }
    }

    // Function to update canvas card previews in real-time
    window.updateCanvasPreviews = function(category) {
        console.log('updateCanvasPreviews called with category:', category);

        // Utiliser le système centralisé CanvasPreviewManager si disponible
        if (window.CanvasPreviewManager && typeof window.CanvasPreviewManager.updatePreviews === 'function') {
            console.log('Using CanvasPreviewManager.updatePreviews for category:', category);
            try {
                window.CanvasPreviewManager.updatePreviews(category);
                console.log('CanvasPreviewManager.updatePreviews completed successfully');
            } catch (error) {
                console.error('Error in CanvasPreviewManager.updatePreviews:', error);
            }
            return;
        }

        console.log('CanvasPreviewManager not available, using fallback logic');
        // Fallback vers l'ancienne logique si CanvasPreviewManager n'est pas disponible
        if (typeof window.updateDimensionsCardPreview === 'function') {
            window.updateDimensionsCardPreview();
        }

        // Update apparence card preview
        if (category === 'apparence' || category === 'all') {
            if (typeof window.updateApparenceCardPreview === 'function') {
                window.updateApparenceCardPreview();
            }
        }

        // Update grille card preview
        if (category === 'grille' || category === 'all') {
            if (typeof window.updateGrilleCardPreview === 'function') {
                window.updateGrilleCardPreview();
            }
        }

        // Update zoom card preview
        if (category === 'zoom' || category === 'all') {
            if (typeof window.updateZoomCardPreview === 'function') {
                window.updateZoomCardPreview();
            }
        }

        // Update interactions card preview
        if (category === 'interactions' || category === 'all') {
            if (typeof window.updateInteractionsCardPreview === 'function') {
                window.updateInteractionsCardPreview();
            }
        }

        // Update export card preview
        if (category === 'export' || category === 'all') {
            if (typeof window.updateExportCardPreview === 'function') {
                window.updateExportCardPreview();
            }
        }

        // Update performance card preview
        if (category === 'performance' || category === 'all') {
            if (typeof window.updatePerformanceCardPreview === 'function') {
                window.updatePerformanceCardPreview();
            }
        }

        // Update autosave card preview
        if (category === 'autosave' || category === 'all') {
            if (typeof window.updateAutosaveCardPreview === 'function') {
                window.updateAutosaveCardPreview();
            }
        }
    };

    // Update dimensions card preview
    window.updateDimensionsCardPreview = function() {
        pdfBuilderDebug('updateDimensionsCardPreview called - using saved settings');
        // This function is now defined in pdf-preview-integration.js with proper logic
    };

    // Update apparence card preview
    window.updateApparenceCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const bgColorInput = document.getElementById("canvas_bg_color");
        const borderColorInput = document.getElementById("canvas_border_color");

        const bgColor = bgColorInput ? bgColorInput.value : (window.pdfBuilderCanvasSettings?.canvas_background_color || '#ffffff');
        const borderColor = borderColorInput ? borderColorInput.value : (window.pdfBuilderCanvasSettings?.border_color || '#cccccc');

        // Update color previews in the card
        const bgPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.bg');
        const borderPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.border');

        if (bgPreview && bgColor) {
            bgPreview.style.backgroundColor = bgColor;
        }
        if (borderPreview && borderColor) {
            borderPreview.style.backgroundColor = borderColor;
        }
    };

    // Update grille card preview
    window.updateGrilleCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const gridEnabledInput = document.getElementById("canvas_grid_enabled");

        const isGridEnabled = gridEnabledInput ? gridEnabledInput.checked : (window.pdfBuilderCanvasSettings?.show_grid === true || window.pdfBuilderCanvasSettings?.show_grid === '1');

        const gridCard = document.querySelector('.canvas-card[data-category="grille"]');
        if (!gridCard) return;

        const gridContainer = gridCard.querySelector('.grid-preview-container');
        if (gridContainer) {
            if (isGridEnabled) {
                gridContainer.classList.add('grid-active');
                gridContainer.classList.remove('grid-inactive');
            } else {
                gridContainer.classList.add('grid-inactive');
                gridContainer.classList.remove('grid-active');
            }
        }
    };

    // Update interactions card preview
    window.updateInteractionsCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const dragEnabledInput = document.getElementById("canvas_drag_enabled");
        const resizeEnabledInput = document.getElementById("canvas_resize_enabled");
        const multiSelectInput = document.getElementById("canvas_multi_select");
        const selectionModeInput = document.getElementById("canvas_selection_mode");

        const dragEnabled = dragEnabledInput ? dragEnabledInput.checked : (window.pdfBuilderCanvasSettings?.drag_enabled === true || window.pdfBuilderCanvasSettings?.drag_enabled === '1');
        const resizeEnabled = resizeEnabledInput ? resizeEnabledInput.checked : (window.pdfBuilderCanvasSettings?.resize_enabled === true || window.pdfBuilderCanvasSettings?.resize_enabled === '1');
        const multiSelect = multiSelectInput ? multiSelectInput.checked : (window.pdfBuilderCanvasSettings?.multi_select === true || window.pdfBuilderCanvasSettings?.multi_select === '1');
        const selectionMode = selectionModeInput ? selectionModeInput.value : (window.pdfBuilderCanvasSettings?.selection_mode || 'rectangle');

        const interactionsCard = document.querySelector('.canvas-card[data-category="interactions"]');
        if (!interactionsCard) return;

        const miniCanvas = interactionsCard.querySelector('.mini-canvas');
        if (miniCanvas) {
            // Update drag state
            if (dragEnabled) {
                miniCanvas.classList.add('drag-enabled');
            } else {
                miniCanvas.classList.remove('drag-enabled');
            }

            // Update resize state
            if (resizeEnabled) {
                miniCanvas.classList.add('resize-enabled');
            } else {
                miniCanvas.classList.remove('resize-enabled');
            }

            // Update multi-select state
            if (multiSelect) {
                miniCanvas.classList.add('multi-select-enabled');
            } else {
                miniCanvas.classList.remove('multi-select-enabled');
            }
        }

        // Update selection mode indicator
        const selectionModeIndicator = interactionsCard.querySelector('.selection-mode-indicator');
        if (selectionModeIndicator) {
            // Remove active class from all mode icons
            const modeIcons = selectionModeIndicator.querySelectorAll('.mode-icon');
            modeIcons.forEach(icon => icon.classList.remove('active'));

            // Add active class to current mode based on selection mode
            if (selectionMode === 'rectangle' && modeIcons[0]) {
                modeIcons[0].classList.add('active');
            } else if (selectionMode === 'lasso' && modeIcons[1]) {
                modeIcons[1].classList.add('active');
            } else if (selectionMode === 'click' && modeIcons[2]) {
                modeIcons[2].classList.add('active');
            }
        }
    };

    // Update export card preview
    window.updateExportCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const exportFormatInput = document.getElementById("canvas_export_format");
        const exportQualityInput = document.getElementById("canvas_export_quality");

        const exportFormat = exportFormatInput ? exportFormatInput.value : (window.pdfBuilderCanvasSettings?.export_format || 'pdf');
        const exportQuality = exportQualityInput ? parseInt(exportQualityInput.value) : (window.pdfBuilderCanvasSettings?.export_quality || 90);

        const exportCard = document.querySelector('.canvas-card[data-category="export"]');
        if (!exportCard) return;

        // Update format badges
        const formatBadges = exportCard.querySelectorAll('.format-badge');
        formatBadges.forEach(badge => badge.classList.remove('active'));

        const activeBadge = exportCard.querySelector(`.format-badge.${exportFormat.toLowerCase()}`);
        if (activeBadge) {
            activeBadge.classList.add('active');
        }

        // Update quality bar
        const qualityFill = exportCard.querySelector('.quality-fill');
        const qualityText = exportCard.querySelector('.quality-text');

        if (qualityFill && qualityText) {
            const quality = parseInt(exportQuality);
            qualityFill.style.width = quality + '%';
            qualityText.textContent = quality + '%';
        }
    };

    // Update performance card preview
    window.updatePerformanceCardPreview = function() {
        console.log('updatePerformanceCardPreview called');
        // Try to get values from modal inputs first (real-time), then from settings
        const fpsTargetInput = document.getElementById("canvas_fps_target");
        const memoryJsInput = document.getElementById("canvas_memory_limit_js");
        const memoryPhpInput = document.getElementById("canvas_memory_limit_php");
        const lazyLoadingEditorInput = document.getElementById("canvas_lazy_loading_editor");
        const lazyLoadingPluginInput = document.getElementById("canvas_lazy_loading_plugin");

        const fpsTarget = fpsTargetInput ? parseInt(fpsTargetInput.value) : (window.pdfBuilderCanvasSettings?.fps_target || 60);
        const memoryJs = memoryJsInput ? parseInt(memoryJsInput.value) : (window.pdfBuilderCanvasSettings?.memory_limit_js || 128);
        const memoryPhp = memoryPhpInput ? parseInt(memoryPhpInput.value) : (window.pdfBuilderCanvasSettings?.memory_limit_php || 256);
        const lazyLoadingEditor = lazyLoadingEditorInput ? lazyLoadingEditorInput.checked : (window.pdfBuilderCanvasSettings?.lazy_loading_editor === true || window.pdfBuilderCanvasSettings?.lazy_loading_editor === '1');
        const lazyLoadingPlugin = lazyLoadingPluginInput ? lazyLoadingPluginInput.checked : (window.pdfBuilderCanvasSettings?.lazy_loading_plugin === true || window.pdfBuilderCanvasSettings?.lazy_loading_plugin === '1');

        // Update FPS metric
        const fpsValue = document.querySelector('.canvas-card[data-category="performance"] .metric-value:first-child');
        if (fpsValue) {
            fpsValue.textContent = fpsTarget;
        }

        // Update memory metrics
        const memoryValues = document.querySelectorAll('.canvas-card[data-category="performance"] .metric-value');
        if (memoryValues[1]) {
            memoryValues[1].textContent = memoryJs + 'MB';
        }
        if (memoryValues[2]) {
            memoryValues[2].textContent = memoryPhp + 'MB';
        }

        // Update lazy loading status
        const statusIndicator = document.querySelector('.canvas-card[data-category="performance"] .status-indicator');
        if (statusIndicator) {
            const isActive = lazyLoadingEditor && lazyLoadingPlugin;
            statusIndicator.classList.toggle('active', isActive);
            statusIndicator.classList.toggle('inactive', !isActive);
        }
    };

    // Update autosave card preview
    window.updateAutosaveCardPreview = function() {
        console.log('PDF_BUILDER_DEBUG: updateAutosaveCardPreview called');
        try {
            // Try to get values from modal inputs first (real-time), then from settings
            const autosaveEnabledInput = document.getElementById("canvas_autosave_enabled");
            const autosaveIntervalInput = document.getElementById("canvas_autosave_interval");
            const versionsLimitInput = document.getElementById("canvas_history_max");

            const autosaveInterval = autosaveIntervalInput ? parseInt(autosaveIntervalInput.value) : (window.pdfBuilderCanvasSettings?.autosave_interval || 5);
            const autosaveEnabled = autosaveEnabledInput ? autosaveEnabledInput.checked : (window.pdfBuilderCanvasSettings?.autosave_enabled === true || window.pdfBuilderCanvasSettings?.autosave_enabled === '1');
            const versionsLimit = versionsLimitInput ? parseInt(versionsLimitInput.value) : (window.pdfBuilderCanvasSettings?.versions_limit || 10);

            console.log('PDF_BUILDER_DEBUG: autosave values - enabled:', autosaveEnabled, 'interval:', autosaveInterval, 'versionsLimit:', versionsLimit);

            const autosaveCard = document.querySelector('.canvas-card[data-category="autosave"]');
            console.log('PDF_BUILDER_DEBUG: autosaveCard found:', autosaveCard);
            if (!autosaveCard) return;

            // Update timer display
            const timerDisplay = autosaveCard.querySelector('.autosave-timer');
            if (timerDisplay) {
                const minutes = autosaveInterval;
                timerDisplay.textContent = minutes + 'min';
                console.log('PDF_BUILDER_DEBUG: Updated timer display to:', minutes + 'min');
            } else {
                console.log('PDF_BUILDER_DEBUG: timerDisplay element not found');
            }

            // Update status
            const statusIndicator = autosaveCard.querySelector('.autosave-status');
            if (statusIndicator) {
                if (autosaveEnabled) {
                    statusIndicator.classList.add('active');
                } else {
                    statusIndicator.classList.remove('active');
                }
                console.log('PDF_BUILDER_DEBUG: Updated status indicator');
            } else {
                console.log('PDF_BUILDER_DEBUG: statusIndicator element not found');
            }

            // Update versions dots
            const versionDots = autosaveCard.querySelectorAll('.version-dot');
            if (versionDots.length > 0) {
                const limit = parseInt(versionsLimit);
                versionDots.forEach((dot, index) => {
                    if (index < limit) {
                        dot.style.display = 'block';
                    } else {
                        dot.style.display = 'none';
                    }
                });
                console.log('PDF_BUILDER_DEBUG: Updated version dots, showing:', limit);
            } else {
                console.log('PDF_BUILDER_DEBUG: versionDots elements not found');
            }

            console.log('PDF_BUILDER_DEBUG: updateAutosaveCardPreview completed successfully');
        } catch (error) {
            console.error('PDF_BUILDER_DEBUG: Error in updateAutosaveCardPreview:', error);
        }
    };

    // ==========================================
    // SYSTÈME CENTRALISÉ DES PREVIEWS TEMPS RÉEL
    // ==========================================

    // Configuration des champs qui déclenchent des previews temps réel
    const RealTimePreviewConfigs = {
        dimensions: {
            fields: ['canvas_format', 'canvas_dpi', 'canvas_orientation'],
            settingMappings: {
                'canvas_format': 'default_canvas_format',
                'canvas_dpi': 'default_canvas_dpi',
                'canvas_orientation': 'default_canvas_orientation'
            },
            valueTransformers: {
                'canvas_dpi': (value) => parseInt(value)
            },
            updateFunction: 'updateDimensionsCardPreview'
        },
        apparence: {
            fields: ['canvas_bg_color', 'canvas_border_color', 'canvas_border_width', 'canvas_shadow_enabled', 'canvas_container_bg_color'],
            settingMappings: {
                'canvas_bg_color': 'canvas_background_color',
                'canvas_border_color': 'border_color',
                'canvas_border_width': 'border_width',
                'canvas_shadow_enabled': 'shadow_enabled',
                'canvas_container_bg_color': 'container_background_color'
            },
            valueTransformers: {
                'canvas_border_width': (value) => parseInt(value),
                'canvas_shadow_enabled': (value) => value === 'on' || value === true
            },
            updateFunction: 'updateApparenceCardPreview'
        },
        interactions: {
            fields: ['canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_selection_mode', 'canvas_keyboard_shortcuts'],
            settingMappings: {
                'canvas_drag_enabled': 'drag_enabled',
                'canvas_resize_enabled': 'resize_enabled',
                'canvas_rotate_enabled': 'rotate_enabled',
                'canvas_multi_select': 'multi_select',
                'canvas_selection_mode': 'selection_mode',
                'canvas_keyboard_shortcuts': 'keyboard_shortcuts'
            },
            valueTransformers: {
                'canvas_drag_enabled': (value) => value === 'on' || value === true,
                'canvas_resize_enabled': (value) => value === 'on' || value === true,
                'canvas_rotate_enabled': (value) => value === 'on' || value === true,
                'canvas_multi_select': (value) => value === 'on' || value === true,
                'canvas_keyboard_shortcuts': (value) => value === 'on' || value === true
            },
            updateFunction: 'updateInteractionsCardPreview'
        },
        autosave: {
            fields: ['canvas_autosave_enabled', 'canvas_autosave_interval', 'canvas_history_max'],
            settingMappings: {
                'canvas_autosave_enabled': 'autosave_enabled',
                'canvas_autosave_interval': 'autosave_interval',
                'canvas_history_max': 'versions_limit'
            },
            valueTransformers: {
                'canvas_autosave_enabled': (value) => value === 'on' || value === true,
                'canvas_autosave_interval': (value) => parseInt(value),
                'canvas_history_max': (value) => parseInt(value)
            },
            updateFunction: 'updateAutosaveCardPreview'
        },
        templates: {
            fields: ['template_library_enabled'],
            updateFunction: function() {
                // Mise à jour directe de l'indicateur templates
                const checkbox = document.getElementById('template_library_enabled');
                const indicator = document.querySelector('.template-library-indicator');
                if (checkbox && indicator) {
                    const isEnabled = checkbox.checked;
                    indicator.style.background = isEnabled ? '#28a745' : '#dc3545';
                    indicator.textContent = isEnabled ? 'ACTIF' : 'INACTIF';
                }
            }
        }
    };

    // Fonction générique d'initialisation des previews temps réel
    function initializeRealTimePreview(category) {
        const config = RealTimePreviewConfigs[category];
        if (!config) return;

        // Écouter les changements sur les champs configurés
        ['change', 'input'].forEach(eventType => {
            document.addEventListener(eventType, function(event) {
                const target = event.target;
                const modal = target.closest(`.canvas-modal[data-category="${category}"]`);

                if (modal && config.fields.includes(target.id)) {
                    // Mettre à jour window.pdfBuilderCanvasSettings temporairement
                    if (window.pdfBuilderCanvasSettings) {
                        const settingKey = config.settingMappings[target.id];
                        let value = target.type === 'checkbox' ? target.checked : target.value;

                        // Appliquer la transformation si elle existe
                        if (config.valueTransformers && config.valueTransformers[target.id]) {
                            value = config.valueTransformers[target.id](value);
                        }

                        window.pdfBuilderCanvasSettings[settingKey] = value;

                        // Mettre à jour la preview immédiatement
                        if (typeof config.updateFunction === 'function') {
                            config.updateFunction();
                        } else if (typeof window[config.updateFunction] === 'function') {
                            window[config.updateFunction]();
                        }
                    }
                }
            });
        });
    }

    // Remplacer les fonctions individuelles par des appels génériques
    function initializeDimensionsRealTimePreview() {
        initializeRealTimePreview('dimensions');
    }

    function initializeApparenceRealTimePreview() {
        initializeRealTimePreview('apparence');
    }

    function initializeInteractionsRealTimePreview() {
        initializeRealTimePreview('interactions');
    }

    function initializeAutosaveRealTimePreview() {
        initializeRealTimePreview('autosave');
    }

    function initializeTemplatesRealTimePreview() {
        initializeRealTimePreview('templates');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeModals();
            initializeAutosaveRealTimePreview();
            initializeTemplatesRealTimePreview();

            // Initialize all previews with saved data from database
            setTimeout(function() {
                if (window.PDF_Builder_Preview_Manager) {
                    window.PDF_Builder_Preview_Manager.initializeAllPreviews();
                }
            }, 200); // Wait for data to be loaded
        });
    } else {
        // DOM already loaded
        initializeModals();
        initializeAutosaveRealTimePreview();
        initializeTemplatesRealTimePreview();

        // Initialize all previews with saved data from database
        setTimeout(function() {
            if (window.PDF_Builder_Preview_Manager) {
                window.PDF_Builder_Preview_Manager.initializeAllPreviews();
            }
        }, 200); // Wait for data to be loaded
    }

    // Also try to initialize after a short delay as backup
    setTimeout(function() {
        if (!isInitialized) {
            
            initializeModals();
        }
    }, 2000);

    // ==========================================
    // SYSTÈME CENTRALISÉ DE GESTION DES MODALES
    // ==========================================

    // Configuration centralisée des modales
    const ModalConfigs = {
        dimensions: {
            fields: [
                { id: 'canvas_format', setting: 'default_canvas_format', default: 'A4' },
                { id: 'canvas_dpi', setting: 'default_canvas_dpi', default: 96 },
                { id: 'canvas_orientation', setting: 'default_canvas_orientation', default: 'portrait' }
            ]
        },
        apparence: {
            fields: [
                { id: 'canvas_bg_color', setting: 'canvas_background_color', default: '#ffffff' },
                { id: 'canvas_border_color', setting: 'border_color', default: '#cccccc' },
                { id: 'canvas_border_width', setting: 'border_width', default: 1 },
                { id: 'canvas_shadow_enabled', setting: 'shadow_enabled', default: false, type: 'checkbox' },
                { id: 'canvas_container_bg_color', setting: 'container_background_color', default: '#f8f9fa' }
            ]
        },
        interactions: {
            fields: [
                { id: 'canvas_drag_enabled', setting: 'drag_enabled', default: true, type: 'checkbox' },
                { id: 'canvas_resize_enabled', setting: 'resize_enabled', default: true, type: 'checkbox' },
                { id: 'canvas_rotate_enabled', setting: 'rotate_enabled', default: true, type: 'checkbox' },
                { id: 'canvas_multi_select', setting: 'multi_select', default: true, type: 'checkbox' },
                { id: 'canvas_selection_mode', setting: 'selection_mode', default: 'bounding_box' },
                { id: 'canvas_keyboard_shortcuts', setting: 'keyboard_shortcuts', default: true, type: 'checkbox' }
            ],
            onSync: function(modal) { updateSelectionModeDependency(modal); }
        },
        autosave: {
            fields: [
                { id: 'canvas_autosave_enabled', setting: 'autosave_enabled', default: true, type: 'checkbox' },
                { id: 'canvas_autosave_interval', setting: 'autosave_interval', default: 5 },
                { id: 'canvas_history_max', setting: 'versions_limit', default: 10 }
            ]
        }
    };

    // Fonction générique de synchronisation des modales
    function synchronizeModalValues(modal, category) {
        if (!modal || !window.pdfBuilderCanvasSettings) return;

        const config = ModalConfigs[category];
        if (!config) return;

        // Stocker les valeurs originales pour restauration si annulé
        const originalValues = {};
        config.fields.forEach(field => {
            originalValues[field.setting] = window.pdfBuilderCanvasSettings[field.setting];
        });
        modal[`_original${category.charAt(0).toUpperCase() + category.slice(1)}Settings`] = originalValues;

        // Appliquer les valeurs actuelles aux inputs
        config.fields.forEach(field => {
            const element = modal.querySelector(`#${field.id}`);
            if (element) {
                const value = window.pdfBuilderCanvasSettings[field.setting] ?? field.default;
                if (field.type === 'checkbox') {
                    element.checked = value;
                } else {
                    element.value = value;
                }
            }
        });

        // Callback personnalisé si défini
        if (config.onSync) {
            config.onSync(modal);
        }
    }

    // Remplacer les fonctions individuelles par des appels génériques
    function synchronizeDimensionsModalValues(modal) {
        synchronizeModalValues(modal, 'dimensions');
    }

    function synchronizeApparenceModalValues(modal) {
        synchronizeModalValues(modal, 'apparence');
    }

    function synchronizeInteractionsModalValues(modal) {
        synchronizeModalValues(modal, 'interactions');
    }

    function synchronizeAutosaveModalValues(modal) {
        synchronizeModalValues(modal, 'autosave');
    }
}

console.log('PDF Builder: Main script loaded successfully');
} catch (error) {
    console.error('PDF Builder: Error in main script:', error);
    console.error('PDF Builder: Error stack:', error.stack);
}
</script>
</script>

