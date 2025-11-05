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

            // Pas de traitement spécial pour les templates builtin
            return result;
        };

    })();
    </script>
    <?php
}, 10);
