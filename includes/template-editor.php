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

        // DEBUG: Log des données brutes du template
        error_log("PDF Builder LOAD - Template name: '{$template_name}'");
        error_log("PDF Builder LOAD - Raw template_data length: " . strlen($template['template_data']));
        error_log("PDF Builder LOAD - Raw template_data: " . substr($template['template_data'], 0, 500) . (strlen($template['template_data']) > 500 ? '... (truncated)' : ''));

        // Décoder et préparer les données du template
        $template_data_raw = $template['template_data'];
        if (!empty($template_data_raw)) {
            $decoded_data = json_decode($template_data_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_data)) {
                $template_data = $decoded_data;

                // DEBUG: Log des données décodées
                error_log("PDF Builder LOAD - Decoded data keys: " . implode(', ', array_keys($decoded_data)));
                error_log("PDF Builder LOAD - Elements count in decoded data: " . (isset($decoded_data['elements']) ? count($decoded_data['elements']) : 'NO ELEMENTS KEY'));

                // Extraire les éléments initiaux depuis la structure du template
                if (isset($decoded_data['pages']) && is_array($decoded_data['pages']) && !empty($decoded_data['pages'])) {
                    $first_page = $decoded_data['pages'][0];
                    if (isset($first_page['elements']) && is_array($first_page['elements'])) {
                        $initial_elements = $first_page['elements'];
                        error_log("PDF Builder LOAD - Elements loaded from pages[0].elements: " . count($initial_elements));
                    }
                } elseif (isset($decoded_data['elements']) && is_array($decoded_data['elements'])) {
                    // Fallback pour l'ancienne structure
                    $initial_elements = $decoded_data['elements'];
                    error_log("PDF Builder LOAD - Elements loaded from elements (fallback): " . count($initial_elements));
                } else {
                    error_log("PDF Builder LOAD - No elements found in any structure");
                }
            } else {
                error_log("PDF Builder LOAD - JSON decode error: " . json_last_error_msg());
            }
        } else {
            error_log("PDF Builder LOAD - Template data is empty");
        }
    } else {
        error_log("PDF Builder LOAD - Template not found in database for ID: {$template_id}");
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

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
(function() {
    'use strict';

    // Fonction pour attendre que pdfBuilderAjax soit disponible
    const waitForPdfBuilderAjax = () => {
        return new Promise((resolve, reject) => {
            const checkPdfBuilderAjax = () => {
                if (typeof pdfBuilderAjax !== 'undefined') {
                    resolve();
                } else if (attempts++ < 100) { // Attendre jusqu'à 5 secondes
                    setTimeout(checkPdfBuilderAjax, 50);
                } else {
                    reject(new Error('pdfBuilderAjax n\'a pas été chargé'));
                }
            };
            let attempts = 0;
            checkPdfBuilderAjax();
        });
    };

    // Initialisation principale
    const initEditor = async () => {
        try {
            // Attendre que pdfBuilderAjax soit disponible
            await waitForPdfBuilderAjax();
            
            // Vérifier que pdfBuilderAjax est disponible
            // console.log('PDF Builder Editor: Vérification de pdfBuilderAjax:', typeof pdfBuilderAjax, pdfBuilderAjax);
            // console.log('PDF Builder Editor: ajaxurl disponible:', typeof ajaxurl, ajaxurl);
            
            // S'assurer qu'ajaxurl est défini
            if (typeof ajaxurl === 'undefined') {
                ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                // console.log('PDF Builder Editor: ajaxurl défini manuellement:', ajaxurl);
            }

            // Ajouter la classe pour masquer les éléments WordPress
            document.body.classList.add('pdf-builder-active');

            // Fonction pour vérifier si les scripts sont chargés
            const checkScriptsLoaded = () => {
                return typeof window.PDFBuilderPro !== 'undefined' &&
                       typeof window.PDFBuilderPro.init === 'function';
            };

            // Initialisation optimisée avec polling intelligent
            let attempts = 0;
            const maxAttempts = 100; // ~5 secondes max

            const initApp = () => {
                if (checkScriptsLoaded()) {
                    try {
                        // Définir les données globales pour le JavaScript
                        window.pdfBuilderData = {
                            templateId: <?php echo $template_id ?: 'null'; ?>,
                            templateName: <?php echo $template_name ? json_encode($template_name) : 'null'; ?>,
                            isNew: <?php echo $is_new ? 'true' : 'false'; ?>,
                            ajaxurl: ajaxurl,
                            nonce: window.pdfBuilderAjax?.nonce || ''
                        };

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
                        
                        return;
                    } catch (error) {
                        console.error('PDF Builder Pro: Erreur lors de l\'initialisation:', error);
                        return;
                    }
                }

                if (++attempts < maxAttempts) {
                    setTimeout(initApp, 50); // Attendre 50ms et réessayer
                } else {
                    console.error('PDF Builder Pro: Timeout - Scripts non chargés après', maxAttempts * 50, 'ms');
                    // Afficher un message d'erreur à l'utilisateur
                    const container = document.getElementById('invoice-quote-builder-container');
                    if (container) {
                        container.innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #dc3545;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
                                <h2>Erreur de chargement</h2>
                                <p>Les scripts nécessaires n'ont pas pu être chargés.</p>
                                <p>Vérifiez la console pour plus de détails.</p>
                                <button onclick="location.reload()" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px;">
                                    Recharger la page
                                </button>
                            </div>
                        `;
                    }
                }
            };

            // Démarrer l'initialisation après DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initApp);
            } else {
                initApp();
            }

            // Gestionnaire de cache optimisé
            document.getElementById('flush-rest-cache-btn')?.addEventListener('click', function() {
                const btn = this, status = document.getElementById('cache-status');
                btn.disabled = true;
                btn.textContent = '🔄 Vidage...';
                status.textContent = 'Vidage du cache REST...';

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=pdf_builder_flush_rest_cache&nonce=' + (window.wpApiSettings?.nonce || '')
                })
                .then(r => r.json())
                .then(d => {
                    status.innerHTML = d.success
                        ? '<span style="color:green">✅ ' + d.data.message + '</span>'
                        : '<span style="color:red">❌ ' + (d.data || 'Erreur') + '</span>';
                    d.success && setTimeout(() => location.reload(), 1500);
                })
                .catch(e => {
                    status.innerHTML = '<span style="color:red">❌ Erreur réseau</span>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = '🔄 Vider Cache REST';
                });
            });
            
        } catch (error) {
            console.error('PDF Builder Editor: Erreur d\'initialisation:', error);
            // Afficher un message d'erreur
            const container = document.getElementById('invoice-quote-builder-container');
            if (container) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
                        <h2>Erreur de chargement</h2>
                        <p>pdfBuilderAjax n'est pas disponible: ${error.message}</p>
                        <p>Vérifiez la console pour plus de détails.</p>
                        <button onclick="location.reload()" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px;">
                            Recharger la page
                        </button>
                    </div>
                `;
            }
        }
    };

    // Démarrer l'initialisation
    initEditor();
    
})();
</script>





