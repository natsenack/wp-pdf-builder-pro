<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Template Editor Page - PDF Builder Pro
 * React/TypeScript Canvas Editor
 */

// Permissions are checked by WordPress via add_submenu_page capability parameter
// Additional check for logged-in users as fallback
if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
}

// Tous les scripts et styles sont maintenant chargés dans la classe admin via enqueue_admin_scripts
// Plus besoin d'enqueues ici car ils sont déjà faits avant wp_head()

// Forcer le chargement des scripts pour l'éditeur si ce n'est pas déjà fait
if (!did_action('admin_enqueue_scripts')) {
    do_action('admin_enqueue_scripts', 'pdf-builder-editor');
}

// S'assurer que le core PDF Builder est chargé
if (function_exists('pdf_builder_load_core_when_needed')) {
    pdf_builder_load_core_when_needed();
}

// CHARGEMENT DIRECT DES SCRIPTS - DERNIER RECOURS
// Si les méthodes WordPress ne fonctionnent pas, charger directement
if (!isset($GLOBALS['pdf_builder_scripts_loaded'])) {
    $GLOBALS['pdf_builder_scripts_loaded'] = true;

    // Charger jQuery si pas déjà chargé
    if (!wp_script_is('jquery', 'done')) {
        wp_enqueue_script('jquery');
    }

    // Charger directement les scripts PDF Builder
    $assets_url = defined('PDF_BUILDER_PRO_ASSETS_URL') ? PDF_BUILDER_PRO_ASSETS_URL : plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/';

    // Script principal - CHARGER DIRECTEMENT AVEC BALISE SCRIPT SYNCHRONE
    $script_url = $assets_url . 'js/dist/pdf-builder-admin.js?v=' . time();
    echo '<script type="text/javascript" src="' . esc_url($script_url) . '"></script>';

    // SCRIPT DE TEST - Vérifier si les variables globales sont définies
    echo '<script type="text/javascript">
        console.log("=== PDF Builder Script Execution Test ===");
        console.log("Script loaded at:", new Date().toISOString());
        console.log("window.PDFBuilderPro:", typeof window.PDFBuilderPro, window.PDFBuilderPro ? "defined" : "undefined");
        console.log("window.pdfBuilderPro:", typeof window.pdfBuilderPro, window.pdfBuilderPro ? "defined" : "undefined");
        console.log("window.React:", typeof window.React, window.React ? "defined" : "undefined");
        console.log("window.ReactDOM:", typeof window.ReactDOM, window.ReactDOM ? "defined" : "undefined");

        // Test d\'exécution du script
        if (typeof window.pdfBuilderPro !== "undefined") {
            console.log("✓ pdfBuilderPro is available globally");
            try {
                console.log("pdfBuilderPro instance:", window.pdfBuilderPro);
                console.log("pdfBuilderPro version:", window.pdfBuilderPro.version || "unknown");
            } catch(e) {
                console.error("Error accessing pdfBuilderPro:", e);
            }
        } else {
            console.error("✗ pdfBuilderPro is NOT available globally");
        }

        if (typeof window.React !== "undefined") {
            console.log("✓ React is available globally");
        } else {
            console.error("✗ React is NOT available globally");
        }

        if (typeof window.ReactDOM !== "undefined") {
            console.log("✓ ReactDOM is available globally");
        } else {
            console.error("✗ ReactDOM is NOT available globally");
        }
        console.log("=== End Script Execution Test ===");
    </script>';

    // Variables AJAX - AJOUTER DIRECTEMENT
    $ajax_vars = [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_order_actions'),
        'version' => '8.0.0_direct_' . time(),
        'timestamp' => time(),
        'strings' => [
            'loading' => 'Chargement...',
            'error' => 'Erreur',
            'success' => 'Succès',
        ]
    ];
    echo '<script type="text/javascript">window.pdfBuilderAjax = ' . wp_json_encode($ajax_vars) . ';</script>';

    // Variables AJAX pour wp_localize_script aussi
    wp_localize_script('pdf-builder-admin-direct', 'pdfBuilderAjax', $ajax_vars);

    // Forcer l'exécution des scripts enqueued
    wp_scripts()->do_items();
}

// Tentative classique si les classes sont disponibles
if (class_exists('PDF_Builder\Admin\PDF_Builder_Admin')) {
    // PDF_Builder_Admin::getInstance() nécessite une instance de la classe principale
    if (class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
        $core_instance = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
        $admin_instance = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance($core_instance);
        if (method_exists($admin_instance, 'enqueue_admin_scripts')) {
            $admin_instance->enqueue_admin_scripts('pdf-builder_page_pdf-builder-editor');
        }
    }
}

// Get template ID from URL
$template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
$is_new = $template_id === 0;

// Récupérer les données complètes du template si c'est un template existant
$template_name = '';
$template_data = null;
$initial_elements = [];

if (!$is_new && $template_id > 0) {
    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row(
        $wpdb->prepare("SELECT name, template_data FROM $table_templates WHERE id = %d", $template_id),
        ARRAY_A
    );

    if ($template) {
        $template_name = $template['name'];

        // Décoder et préparer les données du template
        $template_data_raw = $template['template_data'];
        if (!empty($template_data_raw)) {
            $decoded_data = json_decode($template_data_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_data)) {
                $template_data = $decoded_data;

                // Extraire les éléments initiaux depuis la structure du template
                // Structure actuelle : { elements: [...], canvasWidth, canvasHeight, version }
                if (isset($decoded_data['elements']) && is_array($decoded_data['elements'])) {
                    $initial_elements = $decoded_data['elements'];
                } elseif (isset($decoded_data['pages']) && is_array($decoded_data['pages']) && !empty($decoded_data['pages'])) {
                    // Fallback pour l'ancienne structure (si elle existe)
                    $first_page = $decoded_data['pages'][0];
                    if (isset($first_page['elements']) && is_array($first_page['elements'])) {
                        $initial_elements = $first_page['elements'];
                    }
                }
            }
        }
    }
}
?>
<div class="wrap">
    
    <div id="invoice-quote-builder-container" data-is-new="<?php echo $is_new ? 'true' : 'false'; ?>" class="pdf-builder-container">
        <!-- Loading state -->
        <div class="pdf-builder-loading">
            <div>
                <div class="icon">📄</div>
                <h2><?php echo $is_new ? __('Créer un nouveau template', 'pdf-builder-pro') : __('Éditer le template', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Chargement de l\'éditeur React/TypeScript avancé...', 'pdf-builder-pro'); ?></p>
                <p style="font-size: 12px; color: #666; margin-top: 10px;">Chargement des scripts JavaScript...</p>
                <div class="spinner"></div>
            </div>
        </div>
        <!-- React App will be mounted here -->
    </div>
</div>

<style>
/* Styles essentiels pour l'éditeur PDF */
.pdf-builder-container {
    min-height: 130vh; /* Étendu pour plus d'espace de travail vertical */
    background: #ffffff;
    border-radius: 8px;
    margin: 10px 0;
    position: relative;
    overflow: hidden;
}

.pdf-builder-container .pdf-canvas-editor {
    width: 100%;
    height: 100%;
    min-height: 600px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Styles de chargement temporaires */
.pdf-builder-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: #ffffff;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.pdf-builder-loading > div {
    text-align: center;
}

.pdf-builder-loading .icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.pdf-builder-loading h2 {
    margin: 0 0 1rem 0;
    color: #1e293b;
}

.pdf-builder-loading .spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007cba;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Assurer la priorité des styles CSS sur les styles inline */
.pdf-builder-container[style] {
    padding: 0 !important;
}

/* Styles pour éviter les conflits avec WordPress admin */
body.wp-admin .pdf-builder-container {
    margin-top: 0;
    margin-bottom: 0;
}

body.wp-admin #wpadminbar {
    z-index: 10000 !important;
}

body.wp-admin #adminmenu {
    z-index: 10000 !important;
}

body.wp-admin .pdf-builder-container {
    z-index: 10 !important;
}
</style>

<!-- Initialisation JavaScript -->
<!-- Note: Tous les styles essentiels pour l'éditeur ont été consolidés ci-dessus. -->
<script>
(function() {
    'use strict';

    // Initialisation principale avec protection contre les exécutions multiples
    let isInitialized = false;

    const initApp = () => {
        if (isInitialized) {
            // console.log('PDF Builder already initialized, skipping...');
            return;
        }

        // Cacher l'état de chargement
        const loadingElement = document.querySelector('.pdf-builder-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        // console.log('Checking scripts loaded...', {
        //     PDFBuilderPro: typeof window.PDFBuilderPro,
        //     pdfBuilderPro: typeof window.pdfBuilderPro,
        //     init: typeof window.pdfBuilderPro?.init
        // });

        const pdfBuilderProExists = typeof window.pdfBuilderPro !== 'undefined' && window.pdfBuilderPro !== null;
        const pdfBuilderProRaw = window.pdfBuilderPro;
        const pdfBuilderPro = pdfBuilderProExists && pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
        const initExists = pdfBuilderProExists && typeof pdfBuilderPro?.init === 'function';

        if (pdfBuilderProExists && initExists) {
            try {
                isInitialized = true;

                // Définir les données globales pour le JavaScript
                window.pdfBuilderData = {
                    templateId: <?php echo $template_id ?: 'null'; ?>,
                    templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    ajaxurl: ajaxurl,
                    nonce: window.pdfBuilderAjax?.nonce || ''
                };

                // console.log('📋 Initialisation via PDFBuilderPro.init()...');
                const pdfBuilderProRaw = window.pdfBuilderPro;
                const pdfBuilderPro = pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
                pdfBuilderPro.init('invoice-quote-builder-container', {
                    templateId: <?php echo $template_id ?: 'null'; ?>,
                    templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    initialElements: <?php echo json_encode($initial_elements); ?>,
                    width: 595,
                    height: 842,
                    zoom: 1,
                    gridSize: 10,
                    snapToGrid: true,
                    maxHistorySize: 50
                });
            } catch (error) {
                isInitialized = false; // Reset on error

                // Afficher l'erreur dans l'interface
                const container = document.getElementById('invoice-quote-builder-container');
                if (container) {
                    container.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #dc3545;">
                            <h3>Erreur d'initialisation</h3>
                            <p>Une erreur s'est produite lors du chargement de l'éditeur.</p>
                            <p>Vérifiez la console pour plus de détails.</p>
                            <button onclick="location.reload()">Recharger la page</button>
                        </div>
                    `;
                }
            }
        } else {
        }
    };

    // Attendre que tous les scripts soient chargés avant d'initialiser
    let scriptCheckAttempts = 0;
    const maxScriptCheckAttempts = 50; // 5 secondes maximum

    const checkScriptsLoaded = () => {
        scriptCheckAttempts++;

        // Vérifier que tous les chunks sont chargés avec le code splitting
        const pdfBuilderProRaw = window.PDFBuilderPro;
        const pdfBuilderProExists = typeof pdfBuilderProRaw !== 'undefined' && pdfBuilderProRaw !== null;

        // Gérer le cas où webpack expose le module avec une propriété 'default'
        const pdfBuilderPro = pdfBuilderProExists && pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
        const initExists = pdfBuilderProExists && typeof pdfBuilderPro?.init === 'function';

        // Avec le code splitting, vérifier aussi que React est disponible - COMMENTÉ car React est maintenant bundlé
        // const reactExists = typeof window.React !== 'undefined';
        // const reactDomExists = typeof window.ReactDOM !== 'undefined';
        const reactExists = true; // React est bundlé dans PDFBuilderPro
        const reactDomExists = true; // ReactDOM est bundlé dans PDFBuilderPro

        // LOGS DÉTAILLÉS À CHAQUE VÉRIFICATION
        if (scriptCheckAttempts % 10 === 0 || scriptCheckAttempts === 1) {
            console.log(`🔍 PDF Builder Debug: Check attempt ${scriptCheckAttempts}/50 - DETAILED`);
            console.log('- pdfBuilderProExists:', pdfBuilderProExists);
            console.log('- initExists:', initExists);
            console.log('- reactExists: (bundled in PDFBuilderPro)', reactExists);
            console.log('- reactDomExists: (bundled in PDFBuilderPro)', reactDomExists);
            if (pdfBuilderProExists) {
                const pdfBuilderProRaw = window.pdfBuilderPro;
                const pdfBuilderPro = pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
                console.log('- PDFBuilderPro keys:', Object.keys(pdfBuilderProRaw));
                console.log('- Has default property:', 'default' in pdfBuilderProRaw);
                console.log('- Using default:', !!pdfBuilderProRaw.default);
                console.log('- Final PDFBuilderPro keys:', Object.keys(pdfBuilderPro));
                console.log('- Final has init:', 'init' in pdfBuilderPro);
                console.log('- Final PDFBuilderPro.init type:', typeof pdfBuilderPro.init);
            }
        }

        if (pdfBuilderProExists && initExists && reactExists && reactDomExists) {
            initApp();
        } else if (scriptCheckAttempts < maxScriptCheckAttempts) {
            // Réessayer dans 100ms
            setTimeout(checkScriptsLoaded, 100);
        } else {
            // Afficher un message d'erreur à l'utilisateur
            const container = document.getElementById('invoice-quote-builder-container');
            if (container) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <h3>Erreur de chargement</h3>
                        <p>Les scripts de l'éditeur PDF n'ont pas pu être chargés.</p>
                        <p>Vérifiez la console pour plus de détails.</p>
                        <button onclick="location.reload()">Recharger la page</button>
                    </div>
                `;
            }
        }
    };

    // Démarrer la vérification dès que le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkScriptsLoaded);
    } else {
        checkScriptsLoaded();
    }

})();
</script>
