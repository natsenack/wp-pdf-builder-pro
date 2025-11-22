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
if (class_exists('WP_PDF_Builder_Pro\Security\Role_Manager')) {
    \WP_PDF_Builder_Pro\Security\Role_Manager::check_and_block_access();
}

// Debug: Page loaded
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('[DEBUG] Settings page loaded');
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
        error_log('[DEBUG] POST data received: ' . print_r($_POST, true));
    }
} else {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[DEBUG] No POST data received');
    }
}

// Process form
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if ($is_ajax) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[DEBUG] Processing AJAX form submission');
        }
    }
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[DEBUG] Processing form submission');
    }
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[DEBUG] Nonce verified successfully');
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
            error_log('[DEBUG] Settings changed: ' . ($settings_changed ? 'YES' : 'NO'));
        }

        $result = update_option('pdf_builder_settings', $new_settings);
        try {
            // Debug: Always log the result for troubleshooting
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[DEBUG] update_option result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            }

            // Simplified success logic: if no exception was thrown, consider it successful
            if ($is_ajax) {
                send_ajax_response(true, 'Paramètres enregistrés avec succès.');
            } else {
                $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[DEBUG] Exception during save: ' . $e->getMessage());
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
        <div class="pdf-builder-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button button-secondary">
                👁️ <?php _e('Visiter le site', 'pdf-builder-pro'); ?>
            </a>
        </div>
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

    <!-- Modals -->
    <?php require_once 'settings-modals.php'; ?>

</div>

<!-- Floating Save Button - HORS du conteneur principal -->
<div id="floating-save-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999 !important; border-radius: 10px; padding: 5px;">
    <button type="button" class="floating-save-btn" id="floating-save-btn" style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; border: none; border-radius: 50px; padding: 15px 25px; font-size: 16px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
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
.floating-save-btn {
    background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
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

    // Gestion du bouton flottant de sauvegarde
    const floatingSaveBtn = document.getElementById('floating-save-btn');
    if (floatingSaveBtn) {
        console.log('Bouton flottant trouvé et initialisé');
        floatingSaveBtn.addEventListener('click', function() {
            console.log('Bouton flottant cliqué - sauvegarde AJAX');

            // Changer l'apparence du bouton pendant la sauvegarde
            const originalText = floatingSaveBtn.innerHTML;
            console.log('Texte original du bouton:', originalText);
            floatingSaveBtn.innerHTML = '<span class="save-icon">⏳</span><span class="save-text">Sauvegarde...</span>';
            floatingSaveBtn.classList.add('saving');

            // Timeout de sécurité : remettre le bouton à l'état normal après 5 secondes maximum
            const safetyTimeout = setTimeout(() => {
                console.warn('Timeout de sécurité activé - remise à zéro du bouton');
                floatingSaveBtn.innerHTML = originalText;
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
                formInputs.forEach(input => {
                    if (input.name && input.type !== 'submit' && input.type !== 'button' && 
                        input.name !== 'action' && input.name !== 'nonce' && input.name !== 'current_tab') {
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
            console.log('Envoi requête AJAX vers:', pdf_builder_ajax.ajax_url);
            console.log('Nonce utilisé:', pdf_builder_ajax.nonce);
            fetch(pdf_builder_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse AJAX reçue:', data);
                clearTimeout(safetyTimeout); // Annuler le timeout de sécurité

                if (data.success) {
                    console.log('Sauvegarde réussie, traitement du succès...');
                    // Succès
                    floatingSaveBtn.innerHTML = '<span class="save-icon">✅</span><span class="save-text">Sauvegardé !</span>';
                    floatingSaveBtn.classList.remove('saving');
                    floatingSaveBtn.classList.add('saved');

                    // Remettre le texte original après 2 secondes
                    setTimeout(() => {
                        floatingSaveBtn.innerHTML = originalText;
                        floatingSaveBtn.classList.remove('saved');
                    }, 2000);

                    // Notification de succès déjà gérée par le changement d'apparence du bouton
                    // La notification popup a été supprimée pour éviter les doublons
                } else {
                    // Erreur
                    console.error('Réponse complète en erreur:', data);
                    const errorMessage = data.data && data.data.message ? data.data.message : 'Erreur inconnue';
                    console.error('Erreur de sauvegarde:', errorMessage);
                    alert('Erreur de sauvegarde: ' + errorMessage);
                    floatingSaveBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur</span>';
                    floatingSaveBtn.classList.remove('saving');
                    floatingSaveBtn.classList.add('error');

                    // Remettre le texte original après 3 secondes
                    setTimeout(() => {
                        floatingSaveBtn.innerHTML = originalText;
                        floatingSaveBtn.classList.remove('error');
                    }, 3000);

                    // Afficher l'erreur
                    if (data.data && data.data.message) {
                        alert('Erreur de sauvegarde: ' + data.data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Erreur AJAX catchée:', error);
                clearTimeout(safetyTimeout); // Annuler le timeout de sécurité
                floatingSaveBtn.innerHTML = '<span class="save-icon">❌</span><span class="save-text">Erreur</span>';
                floatingSaveBtn.classList.remove('saving');
                floatingSaveBtn.classList.add('error');

                setTimeout(() => {
                    floatingSaveBtn.innerHTML = originalText;
                    floatingSaveBtn.classList.remove('error');
                }, 3000);

                alert('Erreur de connexion lors de la sauvegarde');
            });
        });
    } else {
        console.error('Bouton flottant non trouvé');
    }
});
</script>