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
if (!defined('PDF_BUILDER_DEBUG_MODE') || !PDF_BUILDER_DEBUG_MODE) {
    if (!is_user_logged_in() || !current_user_can('read')) {
        wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
    }
}

// CHARGER LES SCRIPTS DIRECTEMENT POUR CETTE PAGE
// Charger les scripts et styles nécessaires pour l'éditeur
wp_enqueue_style('pdf-builder-admin', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css', [], PDF_BUILDER_PRO_VERSION);
wp_enqueue_style('pdf-builder-react', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-react.css', [], PDF_BUILDER_PRO_VERSION);
wp_enqueue_style('woocommerce-elements', PDF_BUILDER_PRO_ASSETS_URL . 'css/woocommerce-elements.css', [], PDF_BUILDER_PRO_VERSION);
wp_enqueue_style('toastr', PDF_BUILDER_PRO_ASSETS_URL . 'css/toastr/toastr.min.css', [], '2.1.4');
wp_enqueue_script('toastr', PDF_BUILDER_PRO_ASSETS_URL . 'js/toastr/toastr.min.js', ['jquery'], '2.1.4', true);

// Scripts JavaScript principaux
wp_enqueue_script('pdf-builder-vendors', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/vendors.js', ['jquery'], '1.0.0_force_' . microtime(true), true);
wp_enqueue_script('pdf-builder-admin-v3', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js', ['jquery', 'pdf-builder-vendors'], '8.0.0_force_' . microtime(true), true);
wp_enqueue_script('pdf-builder-nonce-fix-v2', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-nonce-fix.js', ['jquery'], '4.0.0_force_reload_' . time(), true);

// Variables JavaScript pour AJAX
wp_localize_script('pdf-builder-admin-v3', 'pdfBuilderAjax', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('pdf_builder_nonce'),
    'version' => '7.0.0_force_reload_' . time(),
    'timestamp' => time(),
    'strings' => [
        'loading' => __('Chargement...', 'pdf-builder-pro'),
        'error' => __('Erreur', 'pdf-builder-pro'),
        'success' => __('Succès', 'pdf-builder-pro'),
        'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce template ?', 'pdf-builder-pro'),
        'confirm_duplicate' => __('Dupliquer ce template ?', 'pdf-builder-pro'),
    ]
]);

// Variables globales
wp_add_inline_script('pdf-builder-admin-v3', '
    window.pdfBuilderAjax = window.pdfBuilderAjax || ' . json_encode([
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_nonce'),
        'version' => '8.0.0_force_' . time(),
        'timestamp' => time(),
        'strings' => [
            'loading' => __('Chargement...', 'pdf-builder-pro'),
            'error' => __('Erreur', 'pdf-builder-pro'),
            'success' => __('Succès', 'pdf-builder-pro'),
            'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce template ?', 'pdf-builder-pro'),
            'confirm_duplicate' => __('Dupliquer ce template ?', 'pdf-builder-pro'),
        ]
    ]) . ';
    // console.log("PDF Builder: Variables AJAX définies globalement:", window.pdfBuilderAjax);
', 'before');

// Paramètres du canvas
$canvas_settings = get_option('pdf_builder_settings', []);
wp_localize_script('pdf-builder-admin-v3', 'pdfBuilderCanvasSettings', [
    'default_canvas_width' => $canvas_settings['default_canvas_width'] ?? 210,
    'default_canvas_height' => $canvas_settings['default_canvas_height'] ?? 297,
    'default_canvas_unit' => $canvas_settings['default_canvas_unit'] ?? 'mm',
    'default_orientation' => $canvas_settings['default_orientation'] ?? 'portrait',
    'canvas_background_color' => $canvas_settings['canvas_background_color'] ?? '#ffffff',
    'canvas_show_transparency' => $canvas_settings['canvas_show_transparency'] ?? false,
    'enable_rotation' => $canvas_settings['enable_rotation'] ?? true,
    'rotation_step' => $canvas_settings['rotation_step'] ?? 15,
    'rotation_snap' => $canvas_settings['rotation_snap'] ?? true,
]);

// Styles pour l'éditeur canvas
wp_enqueue_style('pdf-builder-canvas-editor', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-canvas.css', [], PDF_BUILDER_PRO_VERSION);

// Forcer l'impression des scripts enqueued (au cas où wp_head n'ait pas encore été appelé)
add_action('wp_print_scripts', function() {
    wp_print_scripts(['pdf-builder-admin-v3', 'pdf-builder-nonce-fix-v2', 'toastr']);
    wp_print_styles(['pdf-builder-admin', 'pdf-builder-react', 'woocommerce-elements', 'toastr', 'pdf-builder-canvas-editor']);
}, 100);

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

    // LOG SPÉCIFIQUE POUR TEMPLATE 1 - COMMENTÉ
    // if ($template_id == 1) {
    //     error_log("=== DIAGNOSTIC TEMPLATE 1 ===");
    //     error_log("Template ID: $template_id");
    //     error_log("Is new: $is_new");
    //     error_log("Table name: $table_templates");
    //     error_log("Template found in DB: " . ($template ? 'YES' : 'NO'));
    //     if ($template) {
    //         error_log("Template name: " . $template['name']);
    //         error_log("Template data length: " . strlen($template['template_data']));
    //         error_log("Template data preview: " . substr($template['template_data'], 0, 200));
    //     } else {
    //         error_log("No template found in database for ID 1");
    //         // Vérifier s'il y a d'autres templates
    //         $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY id", ARRAY_A);
    //         error_log("All templates in DB: " . json_encode($all_templates));
    //     }
    //     error_log("=== END DIAGNOSTIC TEMPLATE 1 ===");
    // }

    if ($template) {
        $template_name = $template['name'];

        // DEBUG: Log des données brutes du template
        // error_log("PDF Builder LOAD - Template name: '{$template_name}'");
        // error_log("PDF Builder LOAD - Raw template_data length: " . strlen($template['template_data']));
        // error_log("PDF Builder LOAD - Raw template_data: " . substr($template['template_data'], 0, 500) . (strlen($template['template_data']) > 500 ? '... (truncated)' : ''));

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
                        // error_log("PDF Builder LOAD - Elements loaded from pages[0].elements (legacy): " . count($initial_elements));
                    }
                } else {
                    // error_log("PDF Builder LOAD - No elements found in any structure");
                    // error_log("PDF Builder LOAD - Available keys in decoded data: " . implode(', ', array_keys($decoded_data)));
                }
            } else {
                // error_log("PDF Builder LOAD - JSON decode error: " . json_last_error_msg());
            }
        } else {
            // error_log("PDF Builder LOAD - Template data is empty");
        }
    } else {
        // error_log("PDF Builder LOAD - Template not found in database for ID: {$template_id}");
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
    min-height: calc(100vh - 200px);
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
    z-index: 1000 !important;
}

body.wp-admin .pdf-builder-container {
    z-index: 10 !important;
}
</style>

<!-- Initialisation JavaScript -->
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
        //     init: typeof window.PDFBuilderPro?.init
        // });

        const pdfBuilderProExists = typeof window.PDFBuilderPro !== 'undefined';
        const initExists = typeof window.PDFBuilderPro?.init === 'function';

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

        const pdfBuilderProExists = typeof window.PDFBuilderPro !== 'undefined';
        const initExists = typeof window.PDFBuilderPro?.init === 'function';

        if (pdfBuilderProExists && initExists) {
            initApp();
        } else if (scriptCheckAttempts < maxScriptCheckAttempts) {
            // Réessayer dans 100ms
            setTimeout(checkScriptsLoaded, 100);
        } else {
            console.error('❌ Timeout: Scripts PDF Builder Pro n\'ont pas pu être chargés après 5 secondes');
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





