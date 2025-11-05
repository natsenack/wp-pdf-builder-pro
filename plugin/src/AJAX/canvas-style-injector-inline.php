<?php
/**
 * PDF Builder Pro - Canvas Style Injector (inlined in PHP)
 * Instead of loading a separate JS file, we inline the style injector script
 */

add_action('wp_footer', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if we're on the React editor page
    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    if ($page !== 'pdf-builder-react-editor') {
        return;
    }
    
    ?>
    <script type="text/javascript">
    /* PDF Builder Pro - Canvas SVG Template Injector */
    (function() {
        'use strict';

        // Intercepter le chargement du template et charger le SVG
        const originalFetch = window.fetch;
        window.fetch = function() {
            const args = Array.from(arguments);
            const url = args[0];
            const result = originalFetch.apply(this, args);

            // Si c'est un appel de chargement de template builtin
            if (url && (url.includes('load_builtin_template') || url.includes('load_template'))) {
                return result.then(function(response) {
                    return response.clone().json().then(function(data) {
                        if (data.success && data.data && data.data.template) {
                            const templateId = data.data.id;
                            if (templateId) {
                                // Charger et afficher le SVG du template
                                loadTemplateFromSVG(templateId);
                            }
                        }
                        return response;
                    }).catch(function() {
                        return response;
                    });
                });
            }

            return result;
        };

        // Fonction pour charger et afficher le SVG du template
        function loadTemplateFromSVG(templateId) {
            // Construire les données pour l'AJAX
            const formData = new FormData();
            formData.append('action', 'pdf_builder_render_template_html');
            formData.append('template_id', templateId);
            formData.append('nonce', (window.pdfBuilderData && window.pdfBuilderData.nonce) || (typeof ajaxnonce !== 'undefined' ? ajaxnonce : ''));

            // Faire l'appel AJAX
            const ajaxUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data.html) {
                    // Trouver le canvas et injecter le SVG
                    setTimeout(function() {
                        const canvas = document.querySelector('.canvas, [class*="canvas"]');
                        if (canvas) {
                            // Créer un wrapper pour le SVG
                            const svgContainer = document.createElement('div');
                            svgContainer.id = 'svg-template-container';
                            svgContainer.style.cssText = 'position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;';
                            svgContainer.innerHTML = data.data.html;
                            
                            // Vider le canvas et ajouter le SVG
                            canvas.innerHTML = '';
                            canvas.appendChild(svgContainer);
                            
                            // Adapter le SVG aux dimensions du canvas
                            const svg = svgContainer.querySelector('svg');
                            if (svg) {
                                svg.style.maxWidth = '100%';
                                svg.style.maxHeight = '100%';
                                svg.style.width = 'auto';
                                svg.style.height = 'auto';
                            }
                        }
                    }, 200);
                }
            })
            .catch(function(err) { 
                console.error('Erreur chargement SVG:', err); 
            });
        }

    })();
    </script>
    <?php
}, 10);
