<?php
/**
 * Template Editor TEST Page - PDF Builder Pro
 * Test script loading and global variables
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Permissions check
if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test PDF Builder Script Loading</title>
    <meta charset='utf-8'>
    <?php
    // Forcer le chargement des scripts pour l'éditeur si ce n'est pas déjà fait
    if (!did_action('admin_enqueue_scripts')) {
        do_action('admin_enqueue_scripts', 'pdf-builder-editor');
    }
    wp_head();
    ?>
</head>
<body>
    <h1>Test PDF Builder Script Loading</h1>
    
    <div id='test-results'>
        <h2>Test Results</h2>
        <div id='script-status'></div>
        <div id='global-vars-status'></div>
        <div id='react-status'></div>
    </div>

    <div id='pdf-builder-editor-root'></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resultsDiv = document.getElementById('test-results');
            
            // Test 1: Check if scripts are loaded
            const scriptStatus = document.getElementById('script-status');
            const requiredScripts = [
                'pdf-builder-editor-js',
                'pdf-builder-editor-vendor-js'
            ];
            
            let scriptLoaded = true;
            requiredScripts.forEach(scriptId => {
                const script = document.getElementById(scriptId);
                if (!script) {
                    scriptStatus.innerHTML += '<p style=\"color: red;\">❌ Script ' + scriptId + ' not found</p>';
                    scriptLoaded = false;
                } else {
                    scriptStatus.innerHTML += '<p style=\"color: green;\">✅ Script ' + scriptId + ' loaded</p>';
                }
            });
            
            // Test 2: Check global variables
            const globalVarsStatus = document.getElementById('global-vars-status');
            const requiredGlobals = [
                'PDF_Builder_Config',
                'wp',
                'React',
                'ReactDOM'
            ];
            
            requiredGlobals.forEach(globalVar => {
                if (typeof window[globalVar] !== 'undefined') {
                    globalVarsStatus.innerHTML += '<p style=\"color: green;\">✅ Global ' + globalVar + ' available</p>';
                } else {
                    globalVarsStatus.innerHTML += '<p style=\"color: red;\">❌ Global ' + globalVar + ' not found</p>';
                }
            });
            
            // Test 3: Check React mounting
            const reactStatus = document.getElementById('react-status');
            const rootElement = document.getElementById('pdf-builder-editor-root');
            
            if (rootElement && typeof React !== 'undefined' && typeof ReactDOM !== 'undefined') {
                try {
                    const element = React.createElement('div', { className: 'test-react' }, 'React is working!');
                    ReactDOM.render(element, rootElement);
                    reactStatus.innerHTML = '<p style=\"color: green;\">✅ React mounting successful</p>';
                } catch (error) {
                    reactStatus.innerHTML = '<p style=\"color: red;\">❌ React mounting failed: ' + error.message + '</p>';
                }
            } else {
                reactStatus.innerHTML = '<p style=\"color: red;\">❌ React not available for mounting</p>';
            }
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>
