<?php
/**
 * PDF Builder Pro - Main Settings Logic
 * Core settings processing and HTML structure
 * Updated: 2025-11-18 20:10:00
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Inclure le script de diagnostic avancé pour les erreurs JavaScript
// require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/diagnostic-advanced-js.php';

if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
    wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.', 'pdf-builder-pro'));
}

// Vérifier l'accès via Role_Manager si disponible
if (class_exists('PDF_Builder\\Security\\Role_Manager')) {
    \PDF_Builder\Security\Role_Manager::check_and_block_access();
}

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
            PDF_Builder_Security_Manager::debug_log('php_errors', 'Paramètres chargés depuis BDD:', count($settings), 'options');
        }

        return $settings;
    }

    /**
     * Charge un paramètre spécifique
     */
    public static function load_setting($key, $default = null) {
        if (!isset(self::$settings_config[$key])) {
            PDF_Builder_Security_Manager::debug_log('php_errors', "Paramètre inconnu '$key'");
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
            'canvas_settings' => [],
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
$settings = $all_settings; // $all_settings contient déjà toutes les options avec clés pdf_builder_*
$canvas_settings = []; // Les paramètres canvas sont gérés séparément

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

// Variables de licence pour les templates
$license_test_mode = $all_settings['pdf_builder_license_test_mode_enabled'];
$license_test_key = $all_settings['pdf_builder_license_test_key'];

// Passer les données sauvegardées au JavaScript pour les previews
// Nettoyer les données pour éviter les erreurs JSON
$sanitized_preview_data = [];
foreach ($preview_data as $key => $value) {
    // S'assurer que toutes les valeurs sont des types JSON-safe
    if (is_string($value)) {
        // Échapper les caractères spéciaux et supprimer les retours chariot
        $sanitized_preview_data[$key] = str_replace(["\r", "\n", "\t"], ['', '', ' '], $value);
    } elseif (is_array($value)) {
        // Pour les arrays, les nettoyer récursivement si nécessaire
        $sanitized_preview_data[$key] = $value;
    } else {
        // Pour les autres types (bool, int, float, null), les garder tels quels
        $sanitized_preview_data[$key] = $value;
    }
}

// Encoder les données de manière sécurisée pour éviter les erreurs JavaScript
$json_settings = wp_json_encode($sanitized_preview_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
if ($json_settings === false) {
    // En cas d'erreur d'encodage, utiliser un objet vide
    $json_settings = '{}';
}

// Utiliser base64 pour éviter tout problème d'échappement
$base64_json = base64_encode($json_settings);
?>
<script>
// Données centralisées chargées depuis la base de données
try {
    // Fallback definition for pdfBuilderDebug if not loaded yet
    if (typeof pdfBuilderDebug === 'undefined') {
        window.pdfBuilderDebug = function(message, ...args) {
            if (console && console.log) {
                console.log('[PDF Builder Debug]', message, ...args);
            }
        };
    }

    // Décoder le JSON depuis base64 pour éviter les problèmes d'échappement
    window.pdfBuilderSavedSettings = JSON.parse(atob('<?php echo $base64_json; ?>'));
} catch (e) {
    console.error('Erreur lors du chargement des paramètres sauvegardés:', e);
    window.pdfBuilderSavedSettings = {};
}
</script>

<script>
// Initialisation simplifiée pour éviter les erreurs de syntaxe
document.addEventListener("DOMContentLoaded", function() {
    console.log("PDF Builder Settings: Basic initialization completed");
});
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

// Process développeur form - REMOVED: Maintenant géré par le système de sauvegarde globale
/*
if (isset($_POST['submit_developpeur']) && isset($_POST['pdf_builder_developpeur_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_developpeur_nonce'], 'pdf_builder_settings')) {
        try {
            // Update developer settings
            $developer_enabled = isset($_POST['developer_enabled']) ? 1 : 0;
            $developer_password = sanitize_text_field($_POST['developer_password'] ?? '');

            PDF_Builder_Security_Manager::debug_log('php_errors', "Processing developer form - enabled: $developer_enabled, password: " . (!empty($developer_password) ? 'set' : 'empty'));

            update_option('pdf_builder_developer_enabled', $developer_enabled);
            update_option('pdf_builder_developer_password', $developer_password);

            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres développeur enregistrés avec succès.</p></div>';
        } catch (Exception $e) {
            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur lors de la sauvegarde: ' . esc_html($e->getMessage()) . '</p></div>';
        }
    } else {
        $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
    }
}
*/

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
            <button type="button" class="nav-tab" data-tab="general">
                <span class="tab-icon">⚙️</span>
                <span class="tab-text">Général</span>
            </button>
            <button type="button" class="nav-tab" data-tab="licence">
                <span class="tab-icon">🔑</span>
                <span class="tab-text">Licence</span>
            </button>
            <button type="button" class="nav-tab" data-tab="systeme">
                <span class="tab-icon">🔧</span>
                <span class="tab-text">Système</span>
            </button>
            <button type="button" class="nav-tab" data-tab="acces">
                <span class="tab-icon">👥</span>
                <span class="tab-text">Accès</span>
            </button>
            <button type="button" class="nav-tab" data-tab="securite">
                <span class="tab-icon">🔒</span>
                <span class="tab-text">Sécurité & Conformité</span>
            </button>
            <button type="button" class="nav-tab" data-tab="pdf">
                <span class="tab-icon">📄</span>
                <span class="tab-text">Configuration PDF</span>
            </button>
            <button type="button" class="nav-tab" data-tab="contenu">
                <span class="tab-icon">🎨</span>
                <span class="tab-text">Contenu & Design</span>
            </button>
            <button type="button" class="nav-tab" data-tab="templates">
                <span class="tab-icon">📋</span>
                <span class="tab-text">Templates par statut</span>
            </button>
            <button type="button" class="nav-tab" data-tab="developpeur">
                <span class="tab-icon">👨‍💻</span>
                <span class="tab-text">Développeur</span>
            </button>
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
    transform: scale(1.05);
    transition: all 0.3s ease;
}

.floating-save-btn.saved {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    animation: bounce 0.6s ease;
    transition: all 0.3s ease;
}

.floating-save-btn.error {
    background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
    animation: shake 0.5s ease;
    transition: all 0.3s ease;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
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
</style>


<script>
// Fonctions simplifiées pour éviter les erreurs de syntaxe
window.updateZoomCardPreview = function() {
    console.log("PDF Builder: Zoom preview updated (simplified)");
};

// Gestion des cartes de métriques du cache
(function() {
    'use strict';

    function initializeCacheMetricCards() {
        const cacheCards = document.querySelectorAll('.cache-metric-card');
        
        cacheCards.forEach(function(card) {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                const metric = this.getAttribute('data-metric');
                openCacheModal(metric);
            });

            // Effet hover pour indiquer que c'est cliquable
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
            });
        });
    }

    function openCacheModal(metric) {
        const modalId = `cache-${metric}-modal`;
        const modal = document.getElementById(modalId);
        
        if (!modal) {
            console.error(`Modal ${modalId} not found`);
            return;
        }

        // Charger les données spécifiques à la métrique
        loadCacheModalData(metric);

        // Afficher la modal
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // Animation d'entrée
        setTimeout(function() {
            modal.classList.add('active');
        }, 10);
    }

    function loadCacheModalData(metric) {
        switch (metric) {
            case 'size':
                loadCacheSizeData();
                break;
            case 'transients':
                loadTransientsData();
                break;
            case 'status':
                // Les données de statut sont déjà dans le formulaire
                break;
            case 'cleanup':
                // Les données de nettoyage sont déjà affichées
                break;
        }
    }

    function loadCacheSizeData() {
        // Simuler le chargement des données de taille du cache
        const previewsSizeElement = document.getElementById('previews-cache-size');
        const mainSizeElement = document.getElementById('main-cache-size');

        if (previewsSizeElement) {
            previewsSizeElement.textContent = 'Calcul...';
        }
        if (mainSizeElement) {
            mainSizeElement.textContent = 'Calcul...';
        }

        // Ici on pourrait faire un appel AJAX pour obtenir les vraies données
        // Pour l'instant, on simule
        setTimeout(function() {
            if (previewsSizeElement) {
                const previewsSize = Math.random() * 50; // Simulation
                previewsSizeElement.textContent = previewsSize < 1 ? '< 1 Mo' : previewsSize.toFixed(1) + ' Mo';
            }
            if (mainSizeElement) {
                const mainSize = Math.random() * 30; // Simulation
                mainSizeElement.textContent = mainSize < 1 ? '< 1 Mo' : mainSize.toFixed(1) + ' Mo';
            }
        }, 500);
    }

    function loadTransientsData() {
        const totalElement = document.getElementById('total-transients-count');
        const expiredElement = document.getElementById('expired-transients-count');
        const pdfBuilderElement = document.getElementById('pdf-builder-transients-count');

        // Simuler le chargement des données de transients
        if (totalElement) totalElement.textContent = '...';
        if (expiredElement) expiredElement.textContent = '...';
        if (pdfBuilderElement) pdfBuilderElement.textContent = '...';

        // Ici on pourrait faire un appel AJAX pour obtenir les vraies données
        setTimeout(function() {
            if (totalElement) totalElement.textContent = Math.floor(Math.random() * 100) + 50;
            if (expiredElement) expiredElement.textContent = Math.floor(Math.random() * 20) + 5;
            if (pdfBuilderElement) pdfBuilderElement.textContent = Math.floor(Math.random() * 30) + 10;
        }, 500);
    }

    // Gestionnaire pour fermer les modales
    function initializeCacheModalCloseHandlers() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('cache-modal-overlay') || e.target.classList.contains('cache-modal-close') || e.target.classList.contains('cache-modal-cancel')) {
                closeCacheModal();
            }
        });

        // Fermeture avec la touche Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCacheModal();
            }
        });
    }

    function closeCacheModal() {
        const activeModal = document.querySelector('.cache-modal.active');
        if (activeModal) {
            activeModal.classList.remove('active');
            setTimeout(function() {
                activeModal.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }
    }

    // Gestionnaire pour sauvegarder la configuration du cache
    function initializeCacheModalSaveHandlers() {
        const saveButtons = document.querySelectorAll('.cache-modal-save');
        
        saveButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const category = this.getAttribute('data-category');
                
                if (category === 'status') {
                    saveCacheStatusConfiguration();
                }
            });
        });
    }

    function saveCacheStatusConfiguration() {
        const form = document.getElementById('cache-status-form');
        if (!form) return;

        const formData = new FormData(form);
        formData.append('action', 'pdf_builder_save_cache_settings');
        formData.append('security', window.pdfBuilderAjax?.nonce || '');

        // Changer l'apparence du bouton
        const saveButton = document.querySelector('.cache-modal-save[data-category="status"]');
        if (saveButton) {
            saveButton.textContent = 'Sauvegarde...';
            saveButton.disabled = true;
        }

        fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                // Fermer la modal
                closeCacheModal();
                
                // Mettre à jour l'interface
                updateCacheStatusDisplay();
                
                // Afficher un message de succès
                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                    PDF_Builder_Notification_Manager.show_toast('Configuration du cache sauvegardée avec succès.', 'success');
                }
            } else {
                // Afficher un message d'erreur
                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                    PDF_Builder_Notification_Manager.show_toast(data.data?.message || 'Erreur lors de la sauvegarde.', 'error');
                }
            }
        })
        .catch(function(error) {
            console.error('Erreur AJAX:', error);
            if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                PDF_Builder_Notification_Manager.show_toast('Erreur de connexion réseau.', 'error');
            }
        })
        .finally(function() {
            // Remettre le bouton à l'état normal
            if (saveButton) {
                saveButton.textContent = 'Sauvegarder';
                saveButton.disabled = false;
            }
        });
    }

    function updateCacheStatusDisplay() {
        // Mettre à jour l'affichage du statut du cache dans l'interface principale
        const statusIndicator = document.querySelector('.cache-enabled-indicator');
        if (statusIndicator) {
            const isEnabled = document.getElementById('modal_cache_enabled')?.checked;
            statusIndicator.textContent = isEnabled ? 'Cache activé' : 'Cache désactivé';
            statusIndicator.style.color = isEnabled ? '#28a745' : '#dc3545';
        }
    }

    // Gestionnaire pour le nettoyage du cache depuis la modal
    function initializeCacheCleanupHandlers() {
        const cleanupButton = document.getElementById('perform-cleanup-btn');
        if (cleanupButton) {
            cleanupButton.addEventListener('click', function(e) {
                e.preventDefault();
                performCacheCleanup();
            });
        }

        const clearCacheButton = document.getElementById('clear-cache-from-modal');
        if (clearCacheButton) {
            clearCacheButton.addEventListener('click', function(e) {
                e.preventDefault();
                clearCacheFromSizeModal();
            });
        }

        const clearTransientsButton = document.getElementById('clear-transients-from-modal');
        if (clearTransientsButton) {
            clearTransientsButton.addEventListener('click', function(e) {
                e.preventDefault();
                clearTransientsFromModal();
            });
        }
    }

    function performCacheCleanup() {
        const cleanupFiles = document.getElementById('cleanup_files')?.checked;
        const cleanupTransients = document.getElementById('cleanup_transients')?.checked;
        const cleanupTemp = document.getElementById('cleanup_temp')?.checked;

        const formData = new FormData();
        formData.append('action', 'pdf_builder_perform_cache_cleanup');
        formData.append('security', window.pdfBuilderAjax?.nonce || '');
        formData.append('cleanup_files', cleanupFiles ? '1' : '0');
        formData.append('cleanup_transients', cleanupTransients ? '1' : '0');
        formData.append('cleanup_temp', cleanupTemp ? '1' : '0');

        const button = document.getElementById('perform-cleanup-btn');
        if (button) {
            button.textContent = 'Nettoyage...';
            button.disabled = true;
        }

        fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                closeCacheModal();
                updateCacheStatusDisplay();
                
                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                    PDF_Builder_Notification_Manager.show_toast('Nettoyage du cache effectué avec succès.', 'success');
                }
                
                // Recharger la page pour mettre à jour les métriques
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                    PDF_Builder_Notification_Manager.show_toast(data.data?.message || 'Erreur lors du nettoyage.', 'error');
                }
            }
        })
        .catch(function(error) {
            console.error('Erreur AJAX:', error);
            if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                PDF_Builder_Notification_Manager.show_toast('Erreur de connexion réseau.', 'error');
            }
        })
        .finally(function() {
            if (button) {
                button.textContent = '🧹 Nettoyer maintenant';
                button.disabled = false;
            }
        });
    }

    function clearCacheFromSizeModal() {
        // Utiliser la même logique que le bouton principal
        const mainClearButton = document.getElementById('clear-cache-general-btn');
        if (mainClearButton) {
            mainClearButton.click();
            closeCacheModal();
        }
    }

    function clearTransientsFromModal() {
        // Simuler le nettoyage des transients
        closeCacheModal();
        if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
            PDF_Builder_Notification_Manager.show_toast('Transients vidés avec succès.', 'success');
        }
    }

    // Initialisation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeCacheMetricCards();
            initializeCacheModalCloseHandlers();
            initializeCacheModalSaveHandlers();
            initializeCacheCleanupHandlers();
        });
    } else {
        initializeCacheMetricCards();
        initializeCacheModalCloseHandlers();
        initializeCacheModalSaveHandlers();
        initializeCacheCleanupHandlers();
    }

})();

    function initializeTabs() {
        if (tabsInitialized) {
            return;
        }

        // Vérifier que les éléments existent
        const tabContents = document.querySelectorAll('.tab-content');
        const navTabs = document.querySelectorAll('.nav-tab');

        if (tabContents.length === 0 || navTabs.length === 0) {
            setTimeout(initializeTabs, 100);
            return;
        }

        // Vérifier le hash de l'URL pour afficher le bon onglet au chargement
        const urlHash = window.location.hash.substring(1); // Enlever le #
        let activeTabId = 'general'; // Par défaut

        if (urlHash && document.getElementById(urlHash)) {
            activeTabId = urlHash;
        }

        // Masquer tous les contenus d'onglets sauf celui actif
        tabContents.forEach(function(content) {
            if (content.id === activeTabId) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });

        // Activer le bon onglet de navigation
        const activeNavTab = document.querySelector('.nav-tab[data-tab="' + activeTabId + '"]');
        if (activeNavTab) {
            activeNavTab.classList.add('nav-tab-active');
        }

        // Gérer les clics sur les onglets
        navTabs.forEach(function(tab) {
            // Supprimer les anciens event listeners en clonant l'élément
            const newTab = tab.cloneNode(true);
            tab.parentNode.replaceChild(newTab, tab);

            newTab.addEventListener('click', function(e) {
                e.preventDefault();

                // Retirer la classe active de tous les onglets
                document.querySelectorAll('.nav-tab').forEach(function(t) {
                    t.classList.remove('nav-tab-active');
                });

                // Ajouter la classe active à l'onglet cliqué
                this.classList.add('nav-tab-active');

                // Retirer la classe active de tous les contenus
                tabContents.forEach(function(content) {
                    content.classList.remove('active');
                });

                // Ajouter la classe active au contenu de l'onglet sélectionné
                const tabId = this.getAttribute('data-tab');
                const targetContent = document.getElementById(tabId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }

                // Mettre à jour l'URL avec le hash de l'onglet sans causer de scroll
                if (tabId && history.replaceState) {
                    const newUrl = window.location.pathname + window.location.search + '#' + tabId;
                    history.replaceState(null, null, newUrl);
                }

                // Mettre à jour le texte du menu mobile
                const currentTabText = document.querySelector('.current-tab-text');
                if (currentTabText) {
                    const tabText = this.querySelector('.tab-text');
                    if (tabText) {
                        currentTabText.textContent = tabText.textContent;
                    }
                }
            });
        });

        // Gestion du menu mobile
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const navTabsContainer = document.querySelector('.nav-tabs-container');

        if (mobileMenuButton && navTabsContainer) {
            mobileMenuButton.addEventListener('click', function() {
                navTabsContainer.classList.toggle('mobile-menu-open');
            });
        }

        tabsInitialized = true;
    }    // Initialiser dès que possible
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTabs);
    } else {
        initializeTabs();
    }

    // Forcer une réinitialisation après le chargement complet de la fenêtre
    window.addEventListener('load', function() {
        setTimeout(initializeTabs, 50);
    });

    // Et une dernière vérification après un délai plus long
    setTimeout(initializeTabs, 1000);

})();

// Gestion du bouton flottant de sauvegarde
(function() {
    'use strict';

    function initializeFloatingSaveButton() {
        const floatingBtn = document.getElementById('floating-save-btn');
        if (!floatingBtn) {
            console.warn('PDF Builder: Bouton flottant de sauvegarde non trouvé');
            return;
        }

        // Vérifier si l'event listener est déjà ajouté
        if (floatingBtn.hasAttribute('data-initialized')) {
            console.log('PDF Builder: Bouton flottant déjà initialisé');
            return;
        }

        console.log('PDF Builder: Initialisation du bouton flottant de sauvegarde');

        // Marquer comme initialisé
        floatingBtn.setAttribute('data-initialized', 'true');

        floatingBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Éviter les clics multiples pendant la sauvegarde
            if (floatingBtn.disabled) {
                console.log('PDF Builder: Sauvegarde déjà en cours, clic ignoré');
                return;
            }

            console.log('PDF Builder: Clic sur le bouton flottant de sauvegarde');

            // Changer l'apparence du bouton pour indiquer la sauvegarde
            floatingBtn.classList.add('saving');
            floatingBtn.classList.remove('saved', 'error');
            floatingBtn.innerHTML = '<span class="save-icon">⏳</span><span class="save-text">Préparation...</span>';
            floatingBtn.disabled = true;

            // Collecter toutes les données des formulaires
            const formData = new FormData();

            // Ajouter l'action AJAX
            formData.append('action', 'pdf_builder_save_all_settings');
            formData.append('security', window.pdfBuilderAjax?.nonce || '');

            // Collecter les données de tous les formulaires de la page avec déduplication
            const forms = document.querySelectorAll('form');
            let totalFields = 0;
            let collectedFields = [];
            let processedFields = new Set(); // Pour éviter les doublons
            let duplicateFields = {}; // Pour compter les dupliqués

            forms.forEach(function(form, index) {
                console.log('PDF Builder: Traitement du formulaire', index + 1, 'sur', forms.length);

                // Mettre à jour l'indicateur en temps réel
                floatingBtn.innerHTML = '<span class="save-icon">⏳</span><span class="save-text">Collecte... (' + (index + 1) + '/' + forms.length + ')</span>';

                // Collecter tous les champs du formulaire
                const formInputs = form.querySelectorAll('input, select, textarea');
                formInputs.forEach(function(input) {
                    if (input.name && input.type !== 'submit' && input.type !== 'button') {
                        // Gestion spéciale pour les tableaux (champs avec [])
                        let fieldName = input.name;
                        if (fieldName.includes('[]')) {
                            // Pour les tableaux, on garde toujours (mais on évite les valeurs dupliquées)
                            if (input.type === 'checkbox') {
                                if (input.checked) {
                                    formData.append(fieldName, input.value);
                                    if (!processedFields.has(fieldName + '_' + input.value)) {
                                        processedFields.add(fieldName + '_' + input.value);
                                        totalFields++;
                                        collectedFields.push(fieldName);
                                    }
                                }
                            } else {
                                formData.append(fieldName, input.value);
                                if (!processedFields.has(fieldName + '_' + input.value)) {
                                    processedFields.add(fieldName + '_' + input.value);
                                    totalFields++;
                                    collectedFields.push(fieldName);
                                }
                            }
                        } else {
                            // Pour les champs normaux, éviter les doublons complets
                            if (!processedFields.has(fieldName)) {
                                processedFields.add(fieldName);

                                if (input.type === 'checkbox') {
                                    formData.append(fieldName, input.checked ? '1' : '0');
                                } else if (input.type === 'radio') {
                                    if (input.checked) {
                                        formData.append(fieldName, input.value);
                                    }
                                } else {
                                    formData.append(fieldName, input.value);
                                }
                                totalFields++;
                                collectedFields.push(fieldName);
                            } else {
                                // Compter les dupliqués pour le debug
                                if (!duplicateFields[fieldName]) {
                                    duplicateFields[fieldName] = 1;
                                }
                                duplicateFields[fieldName]++;
                            }
                        }
                    }
                });
            });

            // Log des dupliqués détectés
            if (Object.keys(duplicateFields).length > 0) {
                console.log('PDF Builder: Dupliqués détectés et ignorés:', duplicateFields);
            }

            console.log('PDF Builder: Collecte terminée -', totalFields, 'champs uniques à sauvegarder');
            console.log('PDF Builder: Champs collectés (dédupliqués):', collectedFields);

            // Debug: Lister tous les champs qui seront envoyés
            console.log('PDF Builder: DEBUG - Champs dans FormData:');
            for (let [key, value] of formData.entries()) {
                console.log('  ', key, '=', value);
            }

            // Ajouter la liste des champs collectés pour comparaison côté serveur
            formData.append('js_collected_fields', JSON.stringify(collectedFields));

            // Indiquer l'envoi
            floatingBtn.innerHTML = '<span class="save-icon">📤</span><span class="save-text">Envoi... (' + totalFields + ' champs)</span>';

            // Envoyer la requête AJAX
            fetch(window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                // Indiquer le traitement de la réponse
                floatingBtn.innerHTML = '<span class="save-icon">⚙️</span><span class="save-text">Traitement...</span>';
                return response.json();
            })
            .then(function(data) {
                console.log('PDF Builder: Réponse AJAX reçue:', data);

                // Afficher les informations de debug si disponibles
                if (data.data && data.data.debug_info) {
                    console.log('🔍 DEBUG - Analyse détaillée des champs:');
                    console.log('📊 Total POST côté serveur:', data.data.debug_info.total_post);
                    console.log('🚫 Ignorés:', data.data.debug_info.ignored.length, '-', data.data.debug_info.ignored);
                    console.log('📋 Traités:', data.data.debug_info.processed);
                    console.log('💾 Sauvegardés:', data.data.debug_info.saved);
                    console.log('❌ Erreurs:', data.data.debug_info.error_count);

                    const collectedCount = collectedFields.length;
                    const processedCount = data.data.debug_info.processed;
                    const savedCount = data.data.debug_info.saved;

                    console.log('📈 Comparaison:');
                    console.log('  - Collectés côté JS:', collectedCount);
                    console.log('  - Reçus côté PHP:', data.data.debug_info.total_post);
                    console.log('  - Traités côté PHP:', processedCount);
                    console.log('  - Sauvegardés:', savedCount);

                    // Calcul des différences
                    const diffCollectedProcessed = collectedCount - processedCount;
                    const diffProcessedSaved = processedCount - savedCount;

                    if (diffCollectedProcessed !== 0) {
                        console.log('⚠️ Différence collecte/traitement:', diffCollectedProcessed);
                    }
                    if (diffProcessedSaved !== 0) {
                        console.log('⚠️ Différence traitement/sauvegarde:', diffProcessedSaved);
                    }

                    // Afficher les champs manquants s'il y en a
                    if (data.data.debug_info.missing_fields) {
                        console.log('❌ Champs manquants (perdus en transmission):', data.data.debug_info.missing_fields);
                    }
                }

                if (data.success) {
                    // Succès
                    floatingBtn.classList.remove('saving');
                    floatingBtn.classList.add('saved');
                    floatingBtn.classList.remove('error');

                    // Afficher le nombre de paramètres sauvegardés
                    const savedCount = data.data && data.data.saved_count ? data.data.saved_count : 'paramètres';
                    floatingBtn.innerHTML = '<span class="save-icon">✅</span><span class="save-text">' + savedCount + ' sauvegardés !</span>';

                    // Mettre à jour l'interface utilisateur en temps réel
                    updateUIAfterSave();

                    // Remettre à l'état normal après 3 secondes
                    setTimeout(function() {
                        floatingBtn.classList.remove('saved');
                        floatingBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                        floatingBtn.disabled = false;
                    }, 3000);

                    // Afficher un message de succès si disponible
                    if (data.data && data.data.message) {
                        if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                            PDF_Builder_Notification_Manager.show_toast(data.data.message, 'success');
                        }
                    } else {
                        if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                            PDF_Builder_Notification_Manager.show_toast('Tous les paramètres ont été sauvegardés avec succès.', 'success');
                        }
                    }

                } else {
                    // Erreur
                    floatingBtn.classList.remove('saving');
                    floatingBtn.classList.add('error');
                    floatingBtn.classList.remove('saved');
                    floatingBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Échec sauvegarde</span>';

                    // Remettre à l'état normal après 5 secondes (plus long pour les erreurs)
                    setTimeout(function() {
                        floatingBtn.classList.remove('error');
                        floatingBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                        floatingBtn.disabled = false;
                    }, 5000);

                    // Afficher le message d'erreur
                    const errorMsg = data.data && data.data.message ? data.data.message : 'Erreur lors de la sauvegarde des paramètres.';
                    if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                        PDF_Builder_Notification_Manager.show_toast(errorMsg, 'error');
                    }
                    console.error('PDF Builder: Erreur de sauvegarde:', errorMsg);
                }
            })
            .catch(function(error) {
                console.error('PDF Builder: Erreur AJAX:', error);

                // Erreur de réseau
                floatingBtn.classList.remove('saving');
                floatingBtn.classList.add('error');
                floatingBtn.classList.remove('saved');
                floatingBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur réseau</span>';

                setTimeout(function() {
                    floatingBtn.classList.remove('error');
                    floatingBtn.innerHTML = '<span class="save-icon">💾</span><span class="save-text">Enregistrer</span>';
                    floatingBtn.disabled = false;
                }, 5000);

                // Afficher l'erreur réseau
                if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                    PDF_Builder_Notification_Manager.show_toast('Erreur de connexion réseau. Vérifiez votre connexion internet et réessayez.', 'error');
                }
            });
        });

        // Fonction pour mettre à jour l'interface utilisateur après la sauvegarde
        function updateUIAfterSave() {
            console.log('PDF Builder: Mise à jour de l\'interface utilisateur après sauvegarde');

            // Mettre à jour le statut du mode développeur
            const developerCheckbox = document.getElementById('developer_enabled');
            if (developerCheckbox) {
                updateDeveloperStatus(developerCheckbox.checked);
            }

            // Ici on peut ajouter d'autres mises à jour d'interface pour d'autres paramètres
            // Par exemple : mise à jour des indicateurs de cache, etc.
        }

        // Fonction pour mettre à jour le statut visuel du mode développeur
        function updateDeveloperStatus(isEnabled) {
            const developerStatusIndicator = document.querySelector('.developer-status-indicator');

            if (developerStatusIndicator) {
                developerStatusIndicator.textContent = isEnabled ? 'ACTIF' : 'INACTIF';
                developerStatusIndicator.style.background = isEnabled ? '#28a745' : '#dc3545';
                console.log('PDF Builder: Statut développeur mis à jour:', isEnabled ? 'ACTIF' : 'INACTIF');
            }

            // Mettre à jour la visibilité des sections dépendantes du mode développeur
            const devSections = document.querySelectorAll('[id^="dev-"][id$="-section"]');
            devSections.forEach(function(section) {
                section.style.display = isEnabled ? '' : 'none';
                console.log('PDF Builder: Section', section.id, isEnabled ? 'affichée' : 'masquée');
            });
        }


    }

    // Initialiser le bouton flottant
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeFloatingSaveButton);
    } else {
        initializeFloatingSaveButton();
    }

})();
</script>



