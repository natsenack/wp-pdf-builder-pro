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

    // DEBUG: Vérifier immédiatement si les scripts sont chargés
    console.log('🔍 PDF Builder Debug: Template editor loaded');
    console.log('🔍 PDF Builder Debug: Checking for enqueued scripts...');

    // Vérifier tous les scripts dans le DOM
    const allScripts = document.querySelectorAll('script[src]');
    console.log('🔍 PDF Builder Debug: Found ' + allScripts.length + ' scripts in DOM:');
    allScripts.forEach((script, index) => {
        const src = script.getAttribute('src');
        console.log('🔍 PDF Builder Debug: Script ' + index + ': ' + src);
    });

    // Vérifier spécifiquement nos scripts
    const pdfBuilderScripts = document.querySelectorAll('script[src*="pdf-builder-admin"]');
    console.log('🔍 PDF Builder Debug: Found ' + pdfBuilderScripts.length + ' PDF Builder scripts');

    // Vérifier les variables globales
    console.log('🔍 PDF Builder Debug: Global variables check:');
    console.log('- window.PDFBuilderPro:', typeof window.PDFBuilderPro);
    console.log('- window.pdfBuilderAjax:', typeof window.pdfBuilderAjax);
    console.log('- window.pdfBuilderCanvasSettings:', typeof window.pdfBuilderCanvasSettings);
    console.log('- window.pdfBuilderData:', typeof window.pdfBuilderData);

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
        //     init: typeof window.PDFBuilderPro?.init
        // });

        const pdfBuilderProExists = typeof window.PDFBuilderPro !== 'undefined' && window.PDFBuilderPro !== null;
        const initExists = pdfBuilderProExists && typeof window.PDFBuilderPro.init === 'function';

        // DEBUG: Log détaillé de l'état de PDFBuilderPro - TOUJOURS AFFICHÉ
        console.log('🔍 PDF Builder Debug: PDFBuilderPro check details:');
        console.log('- window.PDFBuilderPro type:', typeof window.PDFBuilderPro);
        console.log('- window.PDFBuilderPro value:', window.PDFBuilderPro);
        console.log('- pdfBuilderProExists:', pdfBuilderProExists);
        console.log('- initExists:', initExists);

        // LOGS DÉTAILLÉS QUOI QU'IL ARRIVE
        if (pdfBuilderProExists) {
            console.log('- PDFBuilderPro keys:', Object.keys(window.PDFBuilderPro));
            console.log('- PDFBuilderPro has init property:', 'init' in window.PDFBuilderPro);
            console.log('- PDFBuilderPro.init type:', typeof window.PDFBuilderPro.init);
            console.log('- PDFBuilderPro.init value:', window.PDFBuilderPro.init);
            console.log('- Direct check window.PDFBuilderPro.init:', !!window.PDFBuilderPro.init);
            console.log('- window.PDFBuilderPro === null?', window.PDFBuilderPro === null);
            console.log('- window.PDFBuilderPro === undefined?', window.PDFBuilderPro === undefined);
        } else {
            console.log('- PDFBuilderPro is null or undefined');
        }

        if (pdfBuilderProExists && initExists) {
            try {
                isInitialized = true;
                // console.log('✅ Scripts loaded successfully, initializing canvas editor...');

                // Définir les données globales pour le JavaScript
                window.pdfBuilderData = {
                    templateId: <?php echo $template_id ?: 'null'; ?>,
                    templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    ajaxurl: ajaxurl,
                    nonce: window.pdfBuilderAjax?.nonce || ''
                };

                // console.log('📋 Initialisation via PDFBuilderPro.init()...');
                window.PDFBuilderPro.init('invoice-quote-builder-container', {
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
                console.error('PDF Builder Pro: Erreur lors de l\'initialisation:', error);
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
            console.error('❌ Scripts non chargés - PDFBuilderPro ou init manquant');
        }
    };

    // Attendre que tous les scripts soient chargés avant d'initialiser
    let scriptCheckAttempts = 0;
    const maxScriptCheckAttempts = 50; // 5 secondes maximum

    const checkScriptsLoaded = () => {
        scriptCheckAttempts++;

        // Vérifier que tous les chunks sont chargés avec le code splitting
        const pdfBuilderProExists = typeof window.PDFBuilderPro !== 'undefined' && window.PDFBuilderPro !== null;
        const initExists = pdfBuilderProExists && typeof window.PDFBuilderPro.init === 'function';

        // Avec le code splitting, vérifier aussi que React est disponible
        const reactExists = typeof window.React !== 'undefined';
        const reactDomExists = typeof window.ReactDOM !== 'undefined';

        // LOGS DÉTAILLÉS À CHAQUE VÉRIFICATION
        if (scriptCheckAttempts % 10 === 0 || scriptCheckAttempts === 1) {
            console.log(`🔍 PDF Builder Debug: Check attempt ${scriptCheckAttempts}/50 - DETAILED`);
            console.log('- pdfBuilderProExists:', pdfBuilderProExists);
            console.log('- initExists:', initExists);
            console.log('- reactExists:', reactExists);
            console.log('- reactDomExists:', reactDomExists);
            if (pdfBuilderProExists) {
                console.log('- PDFBuilderPro keys:', Object.keys(window.PDFBuilderPro));
                console.log('- PDFBuilderPro has init:', 'init' in window.PDFBuilderPro);
                console.log('- PDFBuilderPro.init type:', typeof window.PDFBuilderPro.init);
                console.log('- PDFBuilderPro.init value:', window.PDFBuilderPro.init);
            }
        }

        if (pdfBuilderProExists && initExists && reactExists && reactDomExists) {
            initApp();
        } else if (scriptCheckAttempts < maxScriptCheckAttempts) {
            // Réessayer dans 100ms
            setTimeout(checkScriptsLoaded, 100);
        } else {
            console.error('❌ Timeout: Scripts PDF Builder Pro n\'ont pas pu être chargés après 5 secondes');
            console.error('Debug info:', {
                pdfBuilderProExists,
                initExists,
                reactExists,
                reactDomExists,
                attempts: scriptCheckAttempts
            });
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
