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
    /* PDF Builder Pro - Canvas Template Style Helper */
    (function() {
        'use strict';

        // Intercepter le chargement du template pour aider l'application React
        const originalFetch = window.fetch;
        window.fetch = function() {
            const args = Array.from(arguments);
            const url = args[0];
            const result = originalFetch.apply(this, args);

            // Si c'est un appel de chargement de template builtin
            if (url && url.includes('load_builtin_template')) {
                return result.then(function(response) {
                    return response.clone().json().then(function(data) {
                        if (data.success && data.data && data.data.template) {
                            // Stocker les données du template pour que React puisse les utiliser
                            window.currentTemplateData = data.data.template;
                            
                            // Dispatcher un événement personnalisé pour informer React
                            setTimeout(function() {
                                const event = new CustomEvent('templateLoaded', { 
                                    detail: { template: data.data.template } 
                                });
                                document.dispatchEvent(event);
                            }, 100);
                        }
                        return response;
                    }).catch(function() {
                        return response;
                    });
                });
            }

            return result;
        };

    })();
    </script>
    <?php
}, 10);
