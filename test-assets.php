<?php
/**
 * Test rapide des assets - PDF Builder Pro
 * Vérifie que les fichiers JavaScript sont accessibles
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Assets - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <h1>Test rapide des assets - PDF Builder Pro</h1>

    <div id="test-results"></div>

    <script>
        const results = document.getElementById('test-results');

        function addResult(message, type = 'info') {
            const div = document.createElement('div');
            div.className = `status ${type}`;
            div.textContent = message;
            results.appendChild(div);
        }

        // Test 1: Vérifier les constantes PHP
        addResult('Constantes PHP définies:', 'info');
        addResult('PDF_BUILDER_PLUGIN_URL: <?php echo defined("PDF_BUILDER_PLUGIN_URL") ? PDF_BUILDER_PLUGIN_URL : "NON DÉFINI"; ?>', defined("PDF_BUILDER_PLUGIN_URL") ? 'success' : 'error');
        addResult('PDF_BUILDER_PRO_ASSETS_URL: <?php echo defined("PDF_BUILDER_PRO_ASSETS_URL") ? PDF_BUILDER_PRO_ASSETS_URL : "NON DÉFINI"; ?>', defined("PDF_BUILDER_PRO_ASSETS_URL") ? 'success' : 'error');
        addResult('PDF_BUILDER_PRO_VERSION: <?php echo defined("PDF_BUILDER_PRO_VERSION") ? PDF_BUILDER_PRO_VERSION : "NON DÉFINI"; ?>', defined("PDF_BUILDER_PRO_VERSION") ? 'success' : 'error');

        // Test 2: Vérifier l'existence des fichiers
        const assetsUrl = '<?php echo defined("PDF_BUILDER_PRO_ASSETS_URL") ? PDF_BUILDER_PRO_ASSETS_URL : ""; ?>';
        if (assetsUrl) {
            const filesToCheck = [
                'js/dist/pdf-builder-admin.js',
                'js/dist/pdf-builder-nonce-fix.js',
                'css/pdf-builder-admin.css'
            ];

            filesToCheck.forEach(file => {
                fetch(assetsUrl + file, { method: 'HEAD' })
                    .then(response => {
                        if (response.ok) {
                            addResult(`✅ ${file} - Accessible`, 'success');
                        } else {
                            addResult(`❌ ${file} - HTTP ${response.status}`, 'error');
                        }
                    })
                    .catch(error => {
                        addResult(`❌ ${file} - Erreur: ${error.message}`, 'error');
                    });
            });
        } else {
            addResult('❌ Impossible de tester les fichiers - constante ASSETS_URL non définie', 'error');
        }

        addResult('Test terminé à ' + new Date().toLocaleTimeString(), 'info');
    </script>
</body>
</html>