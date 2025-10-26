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

    // CHARGEMENT DIRECT DES SCRIPTS ESSENTIELS UNIQUEMENT
    $script_loader_url = plugins_url('assets/js/dist/pdf-builder-script-loader.js', dirname(dirname(__FILE__)));
    $main_bundle_url = plugins_url('assets/js/dist/pdf-builder-admin-debug.js', dirname(dirname(__FILE__)));

    echo '<div style="background: lightgreen; padding: 10px; margin: 10px; border: 1px solid green; font-size: 12px;">';
    echo '<h4>📦 Chargement des scripts PDF Builder</h4>';
    echo '<script type="text/javascript" src="' . esc_url($script_loader_url) . '"></script>';
    echo '<script type="text/javascript" src="' . esc_url($main_bundle_url) . '"></script>';
    echo '<p>Scripts essentiels chargés</p>';
    echo '</div>';

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

<!-- Initialisation JavaScript - VERSION ES5 COMPATIBLE -->
<!-- Note: Tous les styles essentiels pour l'éditeur ont été consolidés ci-dessus. -->
<script>
(function() {
    'use strict';

    // Initialisation principale avec protection contre les exécutions multiples
    var isInitialized = false;

    var initApp = function() {
        if (isInitialized) {
            // console.log('PDF Builder already initialized, skipping...');
            return;
        }

        // Cacher l'état de chargement
        var loadingElement = document.querySelector('.pdf-builder-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        // console.log('Checking scripts loaded...');

        var pdfBuilderProExists = typeof window.pdfBuilderPro !== 'undefined' && window.pdfBuilderPro !== null;
        var pdfBuilderProRaw = window.pdfBuilderPro;
        var pdfBuilderPro = pdfBuilderProExists && pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
        var initExists = pdfBuilderProExists && pdfBuilderPro && typeof pdfBuilderPro.init === 'function';

        if (pdfBuilderProExists && initExists) {
            try {
                isInitialized = true;

                // Définir les données globales pour le JavaScript
                window.pdfBuilderData = {
                    templateId: <?php echo is_numeric($template_id) ? intval($template_id) : 'null'; ?>,
                    templateName: <?php
                        try {
                            echo $template_name ? json_encode($template_name) : 'null';
                        } catch (Exception $e) {
                            echo 'null';
                        }
                    ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    ajaxurl: ajaxurl,
                    nonce: (window.pdfBuilderAjax && window.pdfBuilderAjax.nonce) || ''
                };

                // console.log('📋 Initialisation via PDFBuilderPro.init()...');
                var pdfBuilderProRaw2 = window.pdfBuilderPro;
                var pdfBuilderPro2 = pdfBuilderProRaw2.default ? pdfBuilderProRaw2.default : pdfBuilderProRaw2;

                // Récupérer les paramètres du backend
                var backendSettings = <?php
                    try {
                        $settings = get_option('pdf_builder_settings', []);
                        $encoded = json_encode([
                            'showGrid' => isset($settings['show_grid']) ? (bool)$settings['show_grid'] : true,
                            'snapToGrid' => isset($settings['snap_to_grid']) ? (bool)$settings['snap_to_grid'] : true,
                            'snapToElements' => isset($settings['snap_to_elements']) ? (bool)$settings['snap_to_elements'] : true
                        ]);
                        echo $encoded ? $encoded : '{}';
                    } catch (Exception $e) {
                        echo '{}';
                    }
                ?>;
                // Structurer les données d'initialisation avec les éléments et les paramètres
                var initialData = {
                    elements: <?php
                        try {
                            echo json_encode($initial_elements);
                        } catch (Exception $e) {
                            echo '[]';
                        }
                    ?>,
                    settings: backendSettings
                };

                console.log('📋 Initialisation de l\'éditeur PDF...');
                console.log('📋 pdfBuilderPro disponible:', typeof window.pdfBuilderPro);
                console.log('📋 pdfBuilderPro.init disponible:', typeof window.pdfBuilderPro?.init);

                console.log('�🚨🚨 TEMPLATE: AVANT APPEL pdfBuilderPro.init() 🚨🚨🚨');
                try {
                    pdfBuilderPro.init('invoice-quote-builder-container', {
                        templateId: <?php echo is_numeric($template_id) ? intval($template_id) : 'null'; ?>,
                        templateName: <?php
                            try {
                                echo $template_name ? json_encode($template_name) : 'null';
                            } catch (Exception $e) {
                                echo 'null';
                            }
                        ?>,
                        isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                        initialElements: initialData.elements,
                        width: 595,
                        height: 842,
                        zoom: 1,
                        gridSize: 10,
                        snapToGrid: true,
                        maxHistorySize: 50
                    });
                    console.log('🚨🚨🚨 TEMPLATE: pdfBuilderPro.init() TERMINÉ SANS ERREUR 🚨🚨🚨');
                    
                    // Ajouter un petit indicateur de succès qui ne gêne pas React
                    const successIndicator = document.createElement('div');
                    successIndicator.id = 'pdf-init-success-indicator';
                    successIndicator.style.cssText = 'position:fixed;top:10px;left:10px;background:red;color:white;padding:5px;font-size:11px;z-index:999999;border-radius:3px;opacity:0.9;';
                    successIndicator.textContent = '✅ PDF Init OK - FORCE RELOAD - ' + new Date().toLocaleTimeString();
                    document.body.appendChild(successIndicator);
                    
                    // Supprimer l'indicateur après 5 secondes
                    setTimeout(() => {
                        const indicator = document.getElementById('pdf-init-success-indicator');
                        if (indicator) indicator.remove();
                    }, 5000);
                    
                } catch (initError) {
                    console.error('❌ ERREUR CAPTURÉE dans pdfBuilderPro.init():', initError);
                    console.error('Stack trace complet:', initError.stack);
                    throw initError; // Re-throw pour que le catch parent l'attrape
                }
                console.log('✅ pdfBuilderPro.init() terminé');
                } catch (error) {
                    console.error('❌ Erreur lors de l\'appel à pdfBuilderPro.init():', error);
                    console.error('Stack trace:', error.stack);
                    
                    // Afficher l'erreur dans l'interface
                    var container = document.getElementById('invoice-quote-builder-container');
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
                }
    };

    // Attendre que tous les scripts soient chargés avant d'initialiser
    var scriptCheckAttempts = 0;
    var maxScriptCheckAttempts = 50; // 5 secondes maximum

    var checkScriptsLoaded = function() {
        scriptCheckAttempts++;

        // Vérifier que tous les chunks sont chargés avec le code splitting
        var pdfBuilderProRaw = window.pdfBuilderPro; // Utiliser la version minuscule (principale)
        var pdfBuilderProExists = typeof pdfBuilderProRaw !== 'undefined' && pdfBuilderProRaw !== null;

        // Gérer le cas où webpack expose le module avec une propriété 'default'
        var pdfBuilderPro = pdfBuilderProExists && pdfBuilderProRaw.default ? pdfBuilderProRaw.default : pdfBuilderProRaw;
        var initExists = pdfBuilderProExists && pdfBuilderPro && typeof pdfBuilderPro.init === 'function';

        // Avec le code splitting, vérifier aussi que React est disponible - COMMENTÉ car React est maintenant bundlé
        // var reactExists = typeof window.React !== 'undefined';
        // var reactDomExists = typeof window.ReactDOM !== 'undefined';
        var reactExists = true; // React est bundlé dans PDFBuilderPro
        var reactDomExists = true; // ReactDOM est bundlé dans PDFBuilderPro

        if (pdfBuilderProExists && initExists && reactExists && reactDomExists) {
            initApp();
        } else if (scriptCheckAttempts < maxScriptCheckAttempts) {
            // Réessayer dans 100ms
            setTimeout(checkScriptsLoaded, 100);
        } else {
            // Afficher un message d'erreur à l'utilisateur
            var container = document.getElementById('invoice-quote-builder-container');
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

})();

</script>
<?php
// Fin du template
?>
