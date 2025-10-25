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

    // React sera inclus dans le bundle webpack

    // Charger le runtime webpack avant le script principal
    $runtime_url = $assets_url . 'js/dist/runtime.fd1e176f059237da70e0.js?v=' . time();
    echo '<script type="text/javascript" src="' . esc_url($runtime_url) . '"></script>';

    // Script principal - CHARGER ENSUITE avec les composants React
    $script_url = $assets_url . 'js/dist/pdf-builder-admin.js?v=' . time();
    echo '<script type="text/javascript" src="' . esc_url($script_url) . '"></script>';

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

                    // Corriger les positions des éléments hors canvas
                    $canvas_width = 595;  // Largeur A4 en pixels à 72 DPI
                    $canvas_height = 842; // Hauteur A4 en pixels à 72 DPI

                    foreach ($initial_elements as &$element) {
                        // Vérifier et corriger la position X
                        if (isset($element['x']) && $element['x'] < 0) {
                            $element['x'] = 10; // Position minimale
                        }
                        if (isset($element['x']) && isset($element['width']) && ($element['x'] + $element['width']) > $canvas_width) {
                            $element['x'] = max(10, $canvas_width - $element['width'] - 10);
                        }

                        // Vérifier et corriger la position Y
                        if (isset($element['y']) && $element['y'] < 0) {
                            $element['y'] = 10; // Position minimale
                        }
                        if (isset($element['y']) && isset($element['height']) && ($element['y'] + $element['height']) > $canvas_height) {
                            $element['y'] = max(10, $canvas_height - $element['height'] - 10);
                        }

                        // Cas spécial pour les éléments sans dimensions explicites
                        if (isset($element['y']) && !isset($element['height'])) {
                            if ($element['y'] > $canvas_height - 50) {
                                $element['y'] = $canvas_height - 50;
                            }
                        }
                    }
                    unset($element); // Libérer la référence

                } elseif (isset($decoded_data['pages']) && is_array($decoded_data['pages']) && !empty($decoded_data['pages'])) {
                    // Fallback pour l'ancienne structure (si elle existe)
                    $first_page = $decoded_data['pages'][0];
                    if (isset($first_page['elements']) && is_array($first_page['elements'])) {
                        $initial_elements = $first_page['elements'];
                    }
                }
            }
        } else {
        }
    } else {
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

    const initApp = function() {
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
        const initExists = pdfBuilderProExists && pdfBuilderPro && typeof pdfBuilderPro.init === 'function';

        if (pdfBuilderProExists && initExists) {
            try {
                isInitialized = true;

                // Définir les données globales pour le JavaScript
                window.pdfBuilderData = {
                    templateId: <?php echo $template_id ? $template_id : 'null'; ?>,
                    templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    ajaxurl: ajaxurl,
                    nonce: (window.pdfBuilderAjax && window.pdfBuilderAjax.nonce) || ''
                };

                // console.log('📋 Initialisation via PDFBuilderPro.init()...');
                const pdfBuilderProRaw = window.pdfBuilderPro;
                const pdfBuilderPro = pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
                
                // Récupérer les paramètres du backend
                const backendSettings = <?php 
                    $settings = get_option('pdf_builder_settings', []);
                    echo json_encode([
                        'showGrid' => isset($settings['show_grid']) ? (bool)$settings['show_grid'] : true,
                        'snapToGrid' => isset($settings['snap_to_grid']) ? (bool)$settings['snap_to_grid'] : true,
                        'snapToElements' => isset($settings['snap_to_elements']) ? (bool)$settings['snap_to_elements'] : true
                    ]);
                ?>;                
                // Structurer les données d'initialisation avec les éléments et les paramètres
                const initialData = {
                    elements: <?php echo json_encode($initial_elements); ?>,
                    settings: backendSettings
                };
                
                pdfBuilderPro.init('invoice-quote-builder-container', {
                    templateId: <?php echo $template_id ? $template_id : 'null'; ?>,
                    templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    initialElements: initialData,
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
                    container.innerHTML =
                        '<div style="text-align: center; padding: 40px; color: #dc3545;">' +
                            '<h3>Erreur d\'initialisation</h3>' +
                            '<p>Une erreur s\'est produite lors du chargement de l\'éditeur.</p>' +
                            '<p>Vérifiez la console pour plus de détails.</p>' +
                            '<button onclick="location.reload()">Recharger la page</button>' +
                        '</div>';
                }
            }
        } else {
        }
    };

    // Attendre que tous les scripts soient chargés avant d'initialiser
    let scriptCheckAttempts = 0;
    const maxScriptCheckAttempts = 50; // 5 secondes maximum

    const checkScriptsLoaded = function() {
        scriptCheckAttempts++;

        // Vérifier que tous les chunks sont chargés avec le code splitting
        const pdfBuilderProRaw = window.pdfBuilderPro; // Utiliser la version minuscule (principale)
        const pdfBuilderProExists = typeof pdfBuilderProRaw !== 'undefined' && pdfBuilderProRaw !== null;

        // Gérer le cas où webpack expose le module avec une propriété 'default'
        const pdfBuilderPro = pdfBuilderProExists && pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
        const initExists = pdfBuilderProExists && pdfBuilderPro && typeof pdfBuilderPro.init === 'function';

        // Avec le code splitting, vérifier aussi que React est disponible - COMMENTÉ car React est maintenant bundlé
        // const reactExists = typeof window.React !== 'undefined';
        // const reactDomExists = typeof window.ReactDOM !== 'undefined';
        const reactExists = true; // React est bundlé dans PDFBuilderPro
        const reactDomExists = true; // ReactDOM est bundlé dans PDFBuilderPro

        if (pdfBuilderProExists && initExists && reactExists && reactDomExists) {
            initApp();
        } else if (scriptCheckAttempts < maxScriptCheckAttempts) {
            // Réessayer dans 100ms
            setTimeout(checkScriptsLoaded, 100);
        } else {
            // Afficher un message d'erreur à l'utilisateur
            const container = document.getElementById('invoice-quote-builder-container');
            if (container) {
                container.innerHTML =
                    '<div style="text-align: center; padding: 40px; color: #dc3545;">' +
                        '<h3>Erreur de chargement</h3>' +
                        '<p>Les scripts de l\'éditeur PDF n\'ont pas pu être chargés.</p>' +
                        '<p>Vérifiez la console pour plus de détails.</p>' +
                        '<button onclick="location.reload()">Recharger la page</button>' +
                    '</div>';
            }
        }
    };

    // Démarrer la vérification dès que le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkScriptsLoaded);
    } else {
        checkScriptsLoaded();
    }

    // CHARGER LE SCRIPT DE TEST ADAPTATIF TEMPORAIREMENT
    // À SUPPRIMER APRÈS LES TESTS
    ?>
    <script type="text/javascript">
    (function() {
        'use strict';

        // Attendre que le DOM soit chargé
        document.addEventListener('DOMContentLoaded', function() {
            // Créer un panneau de contrôle pour tester le redimensionnement
            var testPanel = document.createElement('div');
            testPanel.id = 'adaptive-test-panel';
            testPanel.innerHTML =
                '<div style="position: fixed; top: 20px; right: 20px; background: #1f2937; color: white; padding: 15px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 10000; font-family: monospace; font-size: 12px; min-width: 200px;">' +
                    '<h4 style="margin: 0 0 10px 0; color: #60a5fa;">🧪 Test Layout Adaptatif</h4>' +
                    '<div style="margin-bottom: 10px;">' +
                        '<label>Largeur sidebar: <span id="sidebar-width">auto</span>px</label>' +
                    '</div>' +
                    '<div style="margin-bottom: 10px;">' +
                        '<input type="range" id="width-slider" min="200" max="600" value="350" step="10" style="width: 100%; margin: 5px 0;">' +
                    '</div>' +
                    '<div style="display: flex; gap: 5px;">' +
                        '<button id="btn-narrow" style="flex: 1; padding: 5px; background: #dc2626; border: none; border-radius: 4px; color: white; cursor: pointer;">Étroit</button>' +
                        '<button id="btn-normal" style="flex: 1; padding: 5px; background: #059669; border: none; border-radius: 4px; color: white; cursor: pointer;">Normal</button>' +
                        '<button id="btn-wide" style="flex: 1; padding: 5px; background: #7c3aed; border: none; border-radius: 4px; color: white; cursor: pointer;">Large</button>' +
                    '</div>' +
                    '<div style="margin-top: 10px;">' +
                        '<button id="btn-close" style="width: 100%; padding: 5px; background: #6b7280; border: none; border-radius: 4px; color: white; cursor: pointer;">Fermer test</button>' +
                    '</div>' +
                '</div>';

            document.body.appendChild(testPanel);

            // Trouver le sidebar
            var sidebar = document.querySelector('.properties-panel') ||
                         document.querySelector('[class*="sidebar"]') ||
                         document.querySelector('#properties-panel');

            if (!sidebar) {
                console.warn('Sidebar non trouvé pour le test adaptatif');
                return;
            }

            var widthDisplay = document.getElementById('sidebar-width');
            var widthSlider = document.getElementById('width-slider');

            // Fonction pour mettre à jour la largeur
            function updateSidebarWidth(width) {
                if (width === 'auto') {
                    sidebar.style.width = '';
                    sidebar.style.minWidth = '';
                    sidebar.style.maxWidth = '';
                } else {
                    sidebar.style.width = width + 'px';
                    sidebar.style.minWidth = width + 'px';
                    sidebar.style.maxWidth = width + 'px';
                }
                widthDisplay.textContent = width;

                // Forcer un redessinement pour déclencher ResizeObserver
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            }

            // Événements des boutons
            document.getElementById('btn-narrow').addEventListener('click', function() { updateSidebarWidth(250); });
            document.getElementById('btn-normal').addEventListener('click', function() { updateSidebarWidth(350); });
            document.getElementById('btn-wide').addEventListener('click', function() { updateSidebarWidth(500); });
            document.getElementById('btn-close').addEventListener('click', function() {
                document.body.removeChild(testPanel);
                updateSidebarWidth('auto'); // Restaurer la largeur normale
            });

            // Slider pour contrôle fin
            widthSlider.addEventListener('input', function(e) {
                updateSidebarWidth(parseInt(e.target.value));
            });

            // Afficher la largeur initiale
            var initialWidth = sidebar.offsetWidth;
            widthDisplay.textContent = initialWidth;
            widthSlider.value = initialWidth;

            console.log('🧪 Test du layout adaptatif activé. Utilisez le panneau en haut à droite pour redimensionner le sidebar.');
        });
    })();
    </script>
    <?php
// Fin du template
?>
