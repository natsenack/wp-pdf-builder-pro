<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Template Editor Page - PDF Builder Pro
 * React/TypeScript Canvas Editor
 */

error_log("PDF Builder Debug: Début de template-editor.php");

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
wp_enqueue_style('toastr', PDF_BUILDER_PRO_ASSETS_URL . 'css/toastr/toastr.min.css', [], '2.1.4');
wp_enqueue_script('toastr', PDF_BUILDER_PRO_ASSETS_URL . 'js/toastr/toastr.min.js', ['jquery'], '2.1.4', true);

// Scripts JavaScript principaux
wp_enqueue_script('pdf-builder-admin-v3', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js', ['jquery', 'wp-api'], '8.0.0_force_' . microtime(true), true);
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
    console.log("PDF Builder: Variables AJAX définies globalement:", window.pdfBuilderAjax);
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

error_log("PDF Builder Debug: Scripts enqueued in template-editor.php");

// Forcer l'impression des scripts enqueued (au cas où wp_head n'ait pas encore été appelé)
add_action('wp_print_scripts', function() {
    wp_print_scripts(['pdf-builder-admin-v3', 'pdf-builder-nonce-fix-v2', 'toastr']);
    wp_print_styles(['pdf-builder-admin', 'toastr', 'pdf-builder-canvas-editor']);
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

                // LOG SPÉCIFIQUE POUR TEMPLATE 1 - COMMENTÉ
                // if ($template_id == 1) {
                //     error_log("TEMPLATE 1 - JSON decode successful");
                //     error_log("TEMPLATE 1 - Decoded data keys: " . implode(', ', array_keys($decoded_data)));
                //     error_log("TEMPLATE 1 - Has elements key: " . (isset($decoded_data['elements']) ? 'YES' : 'NO'));
                //     if (isset($decoded_data['elements'])) {
                //         error_log("TEMPLATE 1 - Elements count: " . count($decoded_data['elements']));
                //         if (count($decoded_data['elements']) > 0) {
                //             error_log("TEMPLATE 1 - First element: " . json_encode($decoded_data['elements'][0]));
                //         }
                //     }
                // }

                // DEBUG: Log des données décodées
                // error_log("PDF Builder LOAD - Decoded data keys: " . implode(', ', array_keys($decoded_data)));
                // error_log("PDF Builder LOAD - Elements count in decoded data: " . (isset($decoded_data['elements']) ? count($decoded_data['elements']) : 'NO ELEMENTS KEY'));

                // Extraire les éléments initiaux depuis la structure du template
                // Structure actuelle : { elements: [...], canvasWidth, canvasHeight, version }
                if (isset($decoded_data['elements']) && is_array($decoded_data['elements'])) {
                    $initial_elements = $decoded_data['elements'];
                    // error_log("PDF Builder LOAD - Elements loaded from elements: " . count($initial_elements));

                    // DEBUG: Log des propriétés des éléments
                    foreach ($initial_elements as $index => $element) {
                        if (is_array($element)) {
                            // error_log("PDF Builder LOAD - Element $index properties: " . implode(', ', array_keys($element)));
                            // if (isset($element['type'])) {
                            //     error_log("PDF Builder LOAD - Element $index type: " . $element['type']);
                            // }
                            // if (isset($element['backgroundColor'])) {
                            //     error_log("PDF Builder LOAD - Element $index backgroundColor: " . $element['backgroundColor']);
                            // }
                        }
                    }
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
    
    <div id="invoice-quote-builder-container" data-is-new="<?php echo $is_new ? 'true' : 'false'; ?>" style="padding: 20px; background: #ffffff; border-radius: 8px; margin: 10px 0;">
        <!-- React App will be mounted here -->
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #ffffff; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                <h2><?php echo $is_new ? __('Créer un nouveau template', 'pdf-builder-pro') : __('Éditer le template', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Chargement de l\'éditeur React/TypeScript avancé...', 'pdf-builder-pro'); ?></p>
                <div style="margin-top: 2rem;">
                    <button id="flush-rest-cache-btn" style="background: #94a3b8; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                        🔄 Vider Cache REST
                    </button>
                    <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #007cba; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                </div>
                <div id="cache-status" style="margin-top: 1rem; font-size: 12px; color: #666;"></div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS DE SECOURS - Chargement direct si wp_enqueue_script ne fonctionne pas -->
<script>
console.log('🔍 PDF Builder: Starting direct script loading...');
console.log('Assets URL:', '<?php echo PDF_BUILDER_PRO_ASSETS_URL; ?>');
</script>
<script src="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/vendors.js?ver=' . time(); ?>" onerror="console.error('❌ Vendors script failed to load');"></script>
<script src="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'js/toastr/toastr.min.js?ver=' . time(); ?>"></script>
<script src="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js?ver=' . time(); ?>" onerror="console.error('❌ Main script failed to load');"></script>
<script src="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-nonce-fix.js?ver=' . time(); ?>" onerror="console.error('❌ Nonce fix script failed to load');"></script>
<script>
if (typeof window.PDFBuilderPro === 'undefined') {
    console.error('❌ PDFBuilderPro is undefined - script execution failed');
}
</script>
<link rel="stylesheet" href="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css?ver=' . time(); ?>" onerror="console.error('❌ pdf-builder-admin.css failed to load')" />
<link rel="stylesheet" href="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-canvas.css?ver=' . time(); ?>" onerror="console.error('❌ pdf-builder-canvas.css failed to load')" />
<link rel="stylesheet" href="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-react.css?ver=' . time(); ?>" onerror="console.error('❌ pdf-builder-react.css failed to load')" />
<link rel="stylesheet" href="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'css/woocommerce-elements.css?ver=' . time(); ?>" onerror="console.error('❌ woocommerce-elements.css failed to load')" />
<link rel="stylesheet" href="<?php echo PDF_BUILDER_PRO_ASSETS_URL . 'css/toastr/toastr.min.css?ver=' . time(); ?>" onerror="console.error('❌ toastr.min.css failed to load')" />

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
(function() {
    'use strict';

    // Initialisation principale avec protection contre les exécutions multiples
    let isInitialized = false;

    const initApp = () => {
        if (isInitialized) {
            console.log('PDF Builder already initialized, skipping...');
            return;
        }

        console.log('Checking scripts loaded...', {
            PDFBuilderPro: typeof window.PDFBuilderPro,
            init: typeof window.PDFBuilderPro?.init
        });

        const pdfBuilderProExists = typeof window.PDFBuilderPro !== 'undefined';
        const initExists = typeof window.PDFBuilderPro?.init === 'function';

        if (pdfBuilderProExists && initExists) {
            try {
                isInitialized = true;
                console.log('✅ Scripts loaded successfully, initializing canvas editor...');

                // Définir les données globales pour le JavaScript
                window.pdfBuilderData = {
                    templateId: <?php echo $template_id ?: 'null'; ?>,
                    templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                    isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                    ajaxurl: ajaxurl,
                    nonce: window.pdfBuilderAjax?.nonce || ''
                };

                console.log('📋 Initialisation via PDFBuilderPro.init()...');
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
            }
        } else {
            console.error('❌ Scripts non chargés - PDFBuilderPro ou init manquant');
        }
    };    // Démarrer l'initialisation après DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }

})();
</script>





