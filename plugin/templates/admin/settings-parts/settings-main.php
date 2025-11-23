<?php
/**
 * PDF Builder Pro - Main Settings Logic
 * Core settings processing and HTML structure
 * Updated: 2025-11-18 20:10:00
 */

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

// Charger les styles CSS
require_once dirname(__FILE__) . '/settings-styles.php';

// Debug: Page loaded
if (defined('WP_DEBUG') && WP_DEBUG) {

}

// Initialize
$notices = [];
$settings = get_option('pdf_builder_settings', []);
$canvas_settings = get_option('pdf_builder_canvas_settings', []);
// Charger la clé de test de licence si elle existe
$license_test_key = get_option('pdf_builder_license_test_key', '');
$license_test_mode = get_option('pdf_builder_license_test_mode_enabled', false);
$settings['license_test_mode'] = $license_test_mode;

// Charger les paramètres individuels sauvegardés via AJAX
$settings['cache_enabled'] = get_option('pdf_builder_cache_enabled', false);
$settings['cache_ttl'] = get_option('pdf_builder_cache_ttl', 3600);
$settings['cache_compression'] = get_option('pdf_builder_cache_compression', true);
$settings['cache_auto_cleanup'] = get_option('pdf_builder_cache_auto_cleanup', true);
$settings['cache_max_size'] = get_option('pdf_builder_cache_max_size', 100);
$settings['company_phone_manual'] = get_option('pdf_builder_company_phone_manual', '');
$settings['company_siret'] = get_option('pdf_builder_company_siret', '');
$settings['company_vat'] = get_option('pdf_builder_company_vat', '');
$settings['company_rcs'] = get_option('pdf_builder_company_rcs', '');
$settings['company_capital'] = get_option('pdf_builder_company_capital', '');
$settings['pdf_quality'] = get_option('pdf_builder_pdf_quality', 'high');
$settings['default_format'] = get_option('pdf_builder_default_format', 'A4');
$settings['default_orientation'] = get_option('pdf_builder_default_orientation', 'portrait');

// Charger les paramètres développeur
$settings['developer_enabled'] = get_option('pdf_builder_developer_enabled', false);
$settings['developer_password'] = get_option('pdf_builder_developer_password', '');
$settings['debug_php_errors'] = get_option('pdf_builder_debug_php_errors', false);
$settings['debug_javascript'] = get_option('pdf_builder_debug_javascript', false);
$settings['debug_javascript_verbose'] = get_option('pdf_builder_debug_javascript_verbose', false);
$settings['debug_ajax'] = get_option('pdf_builder_debug_ajax', false);
$settings['debug_performance'] = get_option('pdf_builder_debug_performance', false);
$settings['debug_database'] = get_option('pdf_builder_debug_database', false);
$settings['log_level'] = get_option('pdf_builder_log_level', 3);
$settings['log_file_size'] = get_option('pdf_builder_log_file_size', 10);
$settings['log_retention'] = get_option('pdf_builder_log_retention', 30);
$settings['force_https'] = get_option('pdf_builder_force_https', false);
$settings['performance_monitoring'] = get_option('pdf_builder_performance_monitoring', false);

// Vérifier que les valeurs sont bien définies
$company_phone_manual = $settings['company_phone_manual'] ?? '';
$company_siret = $settings['company_siret'] ?? '';
$company_vat = $settings['company_vat'] ?? '';
$company_rcs = $settings['company_rcs'] ?? '';
$company_capital = $settings['company_capital'] ?? '';

// Variables pour la configuration PDF
$pdf_quality = $settings['pdf_quality'] ?? 'high';
$default_format = $settings['default_format'] ?? 'A4';
$default_orientation = $settings['default_orientation'] ?? 'portrait';

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
            <a href="#general" class="nav-tab nav-tab-active" data-tab="general">
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
            <a href="#developpeur" class="nav-tab" data-tab="developpeur">
                <span class="tab-icon">👨‍💻</span>
                <span class="tab-text">Développeur</span>
            </a>
        </div>
    </div>
<?php

// Include canvas settings JavaScript (global)
$canvas_settings_js = get_option('pdf_builder_canvas_settings', []);
?>
<script>
    // Script de définition des paramètres canvas - exécuté très tôt
    window.pdfBuilderCanvasSettings = {
        'default_canvas_format': 'A4',
        'default_canvas_orientation': 'portrait',
        'default_canvas_unit': 'px',
        'default_orientation': 'portrait'
    };

    // Fonction pour convertir le format et l'orientation en dimensions pixels
    window.pdfBuilderCanvasSettings.getDimensionsFromFormat = function(format, orientation) {
        const formatDimensions = {
            'A6': { width: 349, height: 496 },
            'A5': { width: 496, height: 701 },
            'A4': { width: 794, height: 1123 },
            'A3': { width: 1123, height: 1587 },
            'A2': { width: 1587, height: 2245 },
            'A1': { width: 2245, height: 3175 },
            'A0': { width: 3175, height: 4494 },
            'Letter': { width: 816, height: 1056 },
            'Legal': { width: 816, height: 1344 },
            'Tabloid': { width: 1056, height: 1632 }
        };

        const dims = formatDimensions[format] || formatDimensions['A4'];

        // Inverser les dimensions si orientation paysage
        if (orientation === 'landscape') {
            return { width: dims.height, height: dims.width };
        }

        return dims;
    };

    // Ajouter les dimensions calculées aux paramètres
    window.pdfBuilderCanvasSettings.default_canvas_width = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
        window.pdfBuilderCanvasSettings.default_canvas_format,
        window.pdfBuilderCanvasSettings.default_canvas_orientation
    ).width;

    window.pdfBuilderCanvasSettings.default_canvas_height = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
        window.pdfBuilderCanvasSettings.default_canvas_format,
        window.pdfBuilderCanvasSettings.default_canvas_orientation
    ).height;
</script>

    <!-- Tab Content Containers -->
    <div id="general" class="tab-content active">
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
</style>


<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all tabs
            tabs.forEach(function(t) {
                t.classList.remove('nav-tab-active');
            });
            // Add active class to clicked tab
            this.classList.add('nav-tab-active');

            // Hide all tab contents
            contents.forEach(function(c) {
                c.classList.remove('active');
            });
            // Show corresponding tab content
            const target = this.getAttribute('href').substring(1);
            document.getElementById(target).classList.add('active');

            // Update URL hash without scrolling
            history.replaceState(null, null, '#' + target);
        });
    });

    // Check hash on load
    const hash = window.location.hash.substring(1);
    if (hash) {
        const tab = document.querySelector('.nav-tab[href="#' + hash + '"]');
        if (tab) {
            tab.click();
        }
    } else {
        const defaultTab = document.querySelector('.nav-tab[href="#general"]');
        if (defaultTab) {
            defaultTab.click();
        }
    }

    // Fonction pour mettre à jour les indicateurs ACTIF/INACTIF dans l'onglet Sécurité
    function updateSecurityStatusIndicators() {
        // Mettre à jour l'indicateur de sécurité (enable_logging)
        const enableLoggingCheckbox = document.getElementById('enable_logging');
        const securityStatus = document.getElementById('security-status-indicator');
        console.log('Security status element found:', !!securityStatus, 'checkbox checked:', enableLoggingCheckbox ? enableLoggingCheckbox.checked : 'N/A');
        if (enableLoggingCheckbox && securityStatus) {
            const isActive = enableLoggingCheckbox.checked;
            securityStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
            securityStatus.style.background = isActive ? '#28a745' : '#dc3545';
            console.log('Updated security status to:', securityStatus.textContent);
        }

        // Mettre à jour l'indicateur RGPD (gdpr_enabled)
        const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
        const rgpdStatus = document.getElementById('rgpd-status-indicator');
        console.log('RGPD status element found:', !!rgpdStatus, 'checkbox checked:', gdprEnabledCheckbox ? gdprEnabledCheckbox.checked : 'N/A');
        if (gdprEnabledCheckbox && rgpdStatus) {
            const isActive = gdprEnabledCheckbox.checked;
            rgpdStatus.textContent = isActive ? 'ACTIF' : 'INACTIF';
            rgpdStatus.style.background = isActive ? '#28a745' : '#dc3545';
            console.log('Updated RGPD status to:', rgpdStatus.textContent);
        }

        // Mettre à jour les indicateurs système
        updateSystemStatusIndicators();
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

    // Ajouter un event listener pour le toggle RGPD principal
    const gdprEnabledCheckbox = document.getElementById('gdpr_enabled');
    if (gdprEnabledCheckbox) {
        gdprEnabledCheckbox.addEventListener('change', function() {
            toggleRGPDControls();
            // Removed: updateSecurityStatusIndicators(); // Ne plus mettre à jour l'indicateur lors du toggle
        });
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
    } else {
        
    }
});

// Canvas configuration modals functionality - Version stable
(function() {
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
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        
                        if (data.success) {
                            
                            hideModal(modal);
                            this.textContent = originalText;
                            this.disabled = false;

                            // Update previews if function exists
                            if (typeof updateCanvasPreviews === 'function') {
                                
                                updateCanvasPreviews(category);
                            }

                            // Update modal values with saved data
                            if (typeof updateModalValues === 'function') {
                                // Get the saved values from the response or use current form values
                                const form = modal.querySelector('form');
                                if (form) {
                                    const formData = new FormData(form);
                                    const values = {};
                                    for (let [key, value] of formData.entries()) {
                                        values[key] = value;
                                    }
                                    updateModalValues(category, values);
                                }
                            }

                            // Update window.pdfBuilderCanvasSettings for real-time canvas updates
                            // Note: Removed updateWindowCanvasSettings() call as it causes AJAX errors
                            // if (category === 'apparence') {
                            //     updateWindowCanvasSettings();
                            // }

                            // Dispatch custom event for real-time canvas updates
                            if (category === 'apparence') {
                                
                                const updateEvent = new CustomEvent('pdfBuilderCanvasSettingsUpdated', {
                                    detail: { category: 'apparence' }
                                });
                                window.dispatchEvent(updateEvent);
                            }

                            // Afficher notification de succès
                            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                                window.pdfBuilderNotifications.showToast('Paramètres sauvegardés avec succès !', 'success', 4000);
                            } else if (window.PDF_Builder_Notification_Manager) {
                                window.PDF_Builder_Notification_Manager.show_toast('Paramètres sauvegardés avec succès !', 'success', 4000);
                            }
                        } else {
                            // Erreur de sauvegarde - afficher notification d'erreur
                            const errorMessage = data.data?.message || 'Erreur inconnue lors de la sauvegarde';
                            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                                window.pdfBuilderNotifications.showToast('Erreur de sauvegarde: ' + errorMessage, 'error', 6000);
                            } else if (window.PDF_Builder_Notification_Manager) {
                                window.PDF_Builder_Notification_Manager.show_toast('Erreur de sauvegarde: ' + errorMessage, 'error', 6000);
                            }
                            throw new Error(errorMessage);
                        }
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        
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
        // Dimensions standard en mm pour chaque format
        const formatDimensionsMM = {
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
        console.log('Updating canvas previews for category:', category);

        // Update dimensions card preview
        if (category === 'dimensions' || category === 'all') {
            updateDimensionsCardPreview();
        }

        // Update apparence card preview
        if (category === 'apparence' || category === 'all') {
            updateApparenceCardPreview();
        }

        // Update grille card preview
        if (category === 'grille' || category === 'all') {
            updateGrilleCardPreview();
        }

        // Update zoom card preview
        if (category === 'zoom' || category === 'all') {
            updateZoomCardPreview();
        }

        // Update interactions card preview
        if (category === 'interactions' || category === 'all') {
            updateInteractionsCardPreview();
        }

        // Update export card preview
        if (category === 'export' || category === 'all') {
            updateExportCardPreview();
        }

        // Update performance card preview
        if (category === 'performance' || category === 'all') {
            updatePerformanceCardPreview();
        }

        // Update autosave card preview
        if (category === 'autosave' || category === 'all') {
            updateAutosaveCardPreview();
        }
    };

    // Update dimensions card preview
    function updateDimensionsCardPreview() {
        const formatSelect = document.getElementById('canvas_format');
        const dpiSelect = document.getElementById('canvas_dpi');

        if (!formatSelect || !dpiSelect) return;

        const format = formatSelect.value;
        const dpi = parseInt(dpiSelect.value);

        // Dimensions standard en mm pour chaque format
        const formatDimensionsMM = {
            'A4': { width: 210, height: 297 },
            'A3': { width: 297, height: 420 },
            'A5': { width: 148, height: 210 },
            'Letter': { width: 215.9, height: 279.4 },
            'Legal': { width: 215.9, height: 355.6 },
            'Tabloid': { width: 279.4, height: 431.8 }
        };

        const dimensions = formatDimensionsMM[format] || formatDimensionsMM['A4'];

        // Calculer les dimensions en pixels
        const pixelsPerMM = dpi / 25.4;
        const widthPx = Math.round(dimensions.width * pixelsPerMM);
        const heightPx = Math.round(dimensions.height * pixelsPerMM);

        // Update card preview elements
        const cardWidth = document.getElementById('card-canvas-width');
        const cardHeight = document.getElementById('card-canvas-height');
        const cardDpi = document.getElementById('card-canvas-dpi');

        if (cardWidth) cardWidth.textContent = widthPx;
        if (cardHeight) cardHeight.textContent = heightPx;
        if (cardDpi) {
            cardDpi.textContent = `${dpi} DPI - ${format} (${dimensions.width.toFixed(1)}×${dimensions.height.toFixed(1)}mm)`;
        }
    }

    // Update apparence card preview
    function updateApparenceCardPreview() {
        const bgColorInput = document.getElementById('canvas_bg_color');
        const borderColorInput = document.getElementById('canvas_border_color');

        // Update color previews in the card
        const bgPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.bg');
        const borderPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.border');

        if (bgColorInput && bgPreview) {
            bgPreview.style.backgroundColor = bgColorInput.value;
        }
        if (borderColorInput && borderPreview) {
            borderPreview.style.backgroundColor = borderColorInput.value;
        }
    }

    // Update grille card preview
    function updateGrilleCardPreview() {
        const gridEnabled = document.getElementById('canvas_grid_enabled');
        const gridSize = document.getElementById('canvas_grid_size');
        const snapToGrid = document.getElementById('canvas_snap_to_grid');
        const guidesEnabled = document.getElementById('canvas_guides_enabled');

        const gridCard = document.querySelector('.canvas-card[data-category="grille"]');
        if (!gridCard) return;

        const gridContainer = gridCard.querySelector('.grid-preview-container');
        if (gridContainer) {
            if (gridEnabled && gridEnabled.checked) {
                gridContainer.classList.add('grid-active');
                gridContainer.classList.remove('grid-inactive');
            } else {
                gridContainer.classList.add('grid-inactive');
                gridContainer.classList.remove('grid-active');
            }
        }
    }

    // Update zoom card preview
    function updateZoomCardPreview() {
        const zoomMin = document.getElementById('canvas_zoom_min');
        const zoomMax = document.getElementById('canvas_zoom_max');

        const zoomValue = document.getElementById('zoom-preview-value');
        if (zoomValue && zoomMin && zoomMax) {
            zoomValue.textContent = `${zoomMin.value}-${zoomMax.value}%`;
        }
    }

    // Update interactions card preview
    function updateInteractionsCardPreview() {
        const dragEnabled = document.getElementById('canvas_drag_enabled');
        const resizeEnabled = document.getElementById('canvas_resize_enabled');
        const multiSelect = document.getElementById('canvas_multi_select');
        const selectionMode = document.getElementById('canvas_selection_mode');

        const interactionsCard = document.querySelector('.canvas-card[data-category="interactions"]');
        if (!interactionsCard) return;

        const miniCanvas = interactionsCard.querySelector('.mini-canvas');
        if (miniCanvas) {
            // Update drag state
            if (dragEnabled && dragEnabled.checked) {
                miniCanvas.classList.add('drag-enabled');
            } else {
                miniCanvas.classList.remove('drag-enabled');
            }

            // Update resize state
            if (resizeEnabled && resizeEnabled.checked) {
                miniCanvas.classList.add('resize-enabled');
            } else {
                miniCanvas.classList.remove('resize-enabled');
            }

            // Update multi-select state
            if (multiSelect && multiSelect.checked) {
                miniCanvas.classList.add('multi-select-enabled');
            } else {
                miniCanvas.classList.remove('multi-select-enabled');
            }
        }

        // Update selection mode indicator
        const modeIcons = interactionsCard.querySelectorAll('.mode-icon');
        modeIcons.forEach(icon => icon.classList.remove('active'));

        if (selectionMode && modeIcons.length > 0) {
            const modeValue = selectionMode.value;
            if (modeValue === 'rectangle' && modeIcons[0]) {
                modeIcons[0].classList.add('active');
            } else if (modeValue === 'lasso' && modeIcons[1]) {
                modeIcons[1].classList.add('active');
            } else if (modeValue === 'click' && modeIcons[2]) {
                modeIcons[2].classList.add('active');
            }
        }
    }

    // Update export card preview
    function updateExportCardPreview() {
        const exportFormat = document.getElementById('canvas_export_format');
        const exportQuality = document.getElementById('canvas_export_quality');

        const exportCard = document.querySelector('.canvas-card[data-category="export"]');
        if (!exportCard) return;

        // Update format badges
        const formatBadges = exportCard.querySelectorAll('.format-badge');
        formatBadges.forEach(badge => badge.classList.remove('active'));

        if (exportFormat) {
            const activeBadge = exportCard.querySelector(`.format-badge.${exportFormat.value.toLowerCase()}`);
            if (activeBadge) {
                activeBadge.classList.add('active');
            }
        }

        // Update quality bar
        const qualityFill = exportCard.querySelector('.quality-fill');
        const qualityText = exportCard.querySelector('.quality-text');

        if (exportQuality && qualityFill && qualityText) {
            const quality = parseInt(exportQuality.value);
            qualityFill.style.width = quality + '%';
            qualityText.textContent = quality + '%';
        }
    }

    // Update performance card preview
    function updatePerformanceCardPreview() {
        const fpsTarget = document.getElementById('canvas_fps_target');
        const memoryJs = document.getElementById('canvas_memory_limit_js');
        const memoryPhp = document.getElementById('canvas_memory_limit_php');
        const lazyLoadingEditor = document.getElementById('canvas_lazy_loading_editor');
        const lazyLoadingPlugin = document.getElementById('canvas_lazy_loading_plugin');

        // Update FPS metric
        const fpsValue = document.querySelector('.canvas-card[data-category="performance"] .metric-value:first-child');
        if (fpsTarget && fpsValue) {
            fpsValue.textContent = fpsTarget.value;
        }

        // Update memory metrics
        const memoryValues = document.querySelectorAll('.canvas-card[data-category="performance"] .metric-value');
        if (memoryJs && memoryValues[1]) {
            memoryValues[1].textContent = memoryJs.value + 'MB';
        }
        if (memoryPhp && memoryValues[2]) {
            memoryValues[2].textContent = memoryPhp.value + 'MB';
        }

        // Update lazy loading status
        const statusIndicator = document.querySelector('.canvas-card[data-category="performance"] .status-indicator');
        if (statusIndicator && lazyLoadingEditor && lazyLoadingPlugin) {
            const isActive = lazyLoadingEditor.checked && lazyLoadingPlugin.checked;
            statusIndicator.classList.toggle('active', isActive);
            statusIndicator.classList.toggle('inactive', !isActive);
        }
    }

    // Update autosave card preview
    function updateAutosaveCardPreview() {
        const autosaveInterval = document.getElementById('canvas_autosave_interval');
        const autosaveEnabled = document.getElementById('canvas_autosave_enabled');
        const versionsLimit = document.getElementById('canvas_versions_limit');

        const autosaveCard = document.querySelector('.canvas-card[data-category="autosave"]');
        if (!autosaveCard) return;

        // Update timer display
        const timerDisplay = autosaveCard.querySelector('.autosave-timer');
        if (autosaveInterval && timerDisplay) {
            const minutes = Math.floor(parseInt(autosaveInterval.value) / 60);
            timerDisplay.textContent = minutes + 'min';
        }

        // Update status
        const statusIndicator = autosaveCard.querySelector('.autosave-status');
        if (autosaveEnabled && statusIndicator) {
            if (autosaveEnabled.checked) {
                statusIndicator.classList.add('active');
            } else {
                statusIndicator.classList.remove('active');
            }
        }

        // Update versions dots
        const versionDots = autosaveCard.querySelectorAll('.version-dot');
        if (versionsLimit && versionDots.length > 0) {
            const limit = parseInt(versionsLimit.value);
            versionDots.forEach((dot, index) => {
                if (index < limit) {
                    dot.style.display = 'block';
                } else {
                    dot.style.display = 'none';
                }
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeModals();
            // Initialize all canvas previews on page load
            setTimeout(function() {
                if (typeof updateCanvasPreviews === 'function') {
                    updateCanvasPreviews('all');
                }
            }, 100);
        });
    } else {
        // DOM already loaded
        initializeModals();
        // Initialize all canvas previews on page load
        setTimeout(function() {
            if (typeof updateCanvasPreviews === 'function') {
                updateCanvasPreviews('all');
            }
        }, 100);
    }

    // Also try to initialize after a short delay as backup
    setTimeout(function() {
        if (!isInitialized) {
            
            initializeModals();
        }
    }, 2000);

})();</script>
