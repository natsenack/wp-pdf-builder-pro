<?php
/**
 * Test Script Loading - PDF Builder Pro
 * Vérifie que React, ReactDOM et PDFBuilderPro sont correctement chargés
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Script Loading - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <h1>Test de chargement des scripts - PDF Builder Pro</h1>

    <div id="test-results"></div>

    <script>
        const results = document.getElementById('test-results');

        function addResult(message, type = 'info') {
            const div = document.createElement('div');
            div.className = `status ${type}`;
            div.textContent = message;
            results.appendChild(div);
        }

        // Test 1: Vérifier React
        if (typeof window.React !== 'undefined') {
            addResult('✅ React est chargé (version: ' + React.version + ')', 'success');
        } else {
            addResult('❌ React n\'est pas chargé', 'error');
        }

        // Test 2: Vérifier ReactDOM
        if (typeof window.ReactDOM !== 'undefined') {
            addResult('✅ ReactDOM est chargé', 'success');
        } else {
            addResult('❌ ReactDOM n\'est pas chargé', 'error');
        }

        // Test 3: Vérifier PDFBuilderPro
        if (typeof window.PDFBuilderPro !== 'undefined') {
            addResult('✅ PDFBuilderPro est chargé', 'success');

            // Test 4: Vérifier la méthode init
            if (typeof window.PDFBuilderPro.init === 'function') {
                addResult('✅ PDFBuilderPro.init() est disponible', 'success');
            } else {
                addResult('❌ PDFBuilderPro.init() n\'est pas disponible', 'error');
            }
        } else {
            addResult('❌ PDFBuilderPro n\'est pas chargé', 'error');
        }

        // Test 5: Vérifier les variables AJAX
        if (typeof window.pdfBuilderAjax !== 'undefined') {
            addResult('✅ Variables AJAX chargées', 'success');
        } else {
            addResult('❌ Variables AJAX non chargées', 'error');
        }

        addResult('Test terminé à ' + new Date().toLocaleTimeString(), 'info');
    </script>

    <?php
    // Charger les mêmes scripts que dans template-editor.php
    wp_enqueue_script('pdf-builder-admin-v3', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js', ['jquery', 'wp-api'], '8.0.0_test_' . microtime(true), true);
    wp_enqueue_script('pdf-builder-nonce-fix-v2', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-nonce-fix.js', ['jquery'], '4.0.0_test_' . time(), true);

    // Variables JavaScript pour AJAX (simplifiées pour le test)
    wp_localize_script('pdf-builder-admin-v3', 'pdfBuilderAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_nonce'),
        'version' => 'test_' . time(),
    ]);

    // Forcer l'impression des scripts
    wp_print_scripts(['pdf-builder-admin-v3', 'pdf-builder-nonce-fix-v2']);
    ?>
</body>
</html>