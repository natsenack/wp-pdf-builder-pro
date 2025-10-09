<?php
/**
 * Template Editor Page - PDF Builder Pro
 * React/TypeScript Canvas Editor
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

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

// Temporaire : désactiver l'utilisation du template manager qui n'existe pas encore
// $core = PDF_Builder_Core::getInstance();
// $template_manager = $core->get_template_manager();

$template = null; // Temporaire : pas de template chargé
?>
    <div id="pdf-builder-container" data-is-new="<?php echo $is_new ? 'true' : 'false'; ?>" style="height: calc(100vh - 120px); padding: 20px; background: #f5f5f5; border-radius: 8px; margin: 10px 0;">
        <!-- React App will be mounted here -->
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #ffffff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                <h2><?php echo $is_new ? __('Créer un nouveau template', 'pdf-builder-pro') : __('Éditer le template', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Chargement de l\'éditeur React/TypeScript avancé...', 'pdf-builder-pro'); ?></p>
                <div style="margin-top: 2rem;">
                    <button id="flush-rest-cache-btn" style="background: #dc3232; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
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

    console.log('PDF Builder Pro: Template editor script loaded');

    // Initialisation optimisée avec polling intelligent
    let attempts = 0;
    const maxAttempts = 50; // ~1.5 secondes max

    const initApp = () => {
        console.log('PDF Builder Pro: Checking for PDFBuilderPro.init (attempt ' + attempts + ')');
        console.log('PDF Builder Pro: window.PDFBuilderPro:', window.PDFBuilderPro);

        if (window.PDFBuilderPro?.init) {
            console.log('PDF Builder Pro: PDFBuilderPro.init found, calling with pdf-builder-container');
            window.PDFBuilderPro.init('pdf-builder-container', {
                templateId: null,
                isNew: true,
                width: 595,
                height: 842,
                zoom: 1,
                gridSize: 10,
                snapToGrid: true,
                maxHistorySize: 50
            });
            return;
        }

        if (++attempts < maxAttempts) {
            console.log('PDF Builder Pro: PDFBuilderPro.init not ready, retrying...');
            requestAnimationFrame(initApp);
        } else {
            console.log('PDF Builder Pro: Max attempts reached, PDFBuilderPro.init never became available');
        }
    };

    // Démarrer l'initialisation immédiatement après DOM ready
    if (document.readyState === 'loading') {
        console.log('PDF Builder Pro: DOM still loading, waiting for DOMContentLoaded');
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        console.log('PDF Builder Pro: DOM already loaded, starting init');
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
})();
</script>



