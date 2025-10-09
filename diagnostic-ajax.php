<?php
/**
 * PDF Builder Pro - Diagnostic AJAX
 * Script de diagnostic pour identifier les problèmes AJAX WordPress
 */

// Test 1: Vérifier si les actions sont enregistrées
add_action('wp_ajax_pdf_builder_test', 'pdf_builder_test_ajax');
add_action('wp_ajax_nopriv_pdf_builder_test', 'pdf_builder_test_ajax');

function pdf_builder_test_ajax() {
    wp_send_json_success([
        'message' => 'AJAX fonctionne correctement',
        'timestamp' => time(),
        'user_logged_in' => is_user_logged_in(),
        'current_user_can_read' => current_user_can('read'),
        'actions_registered' => [
            'wp_ajax_pdf_builder_preview' => has_action('wp_ajax_pdf_builder_preview'),
            'wp_ajax_nopriv_pdf_builder_preview' => has_action('wp_ajax_nopriv_pdf_builder_preview')
        ]
    ]);
}

// Test 2: Page de diagnostic accessible via URL
add_action('wp_loaded', function() {
    if (isset($_GET['pdf_builder_diagnostic'])) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>PDF Builder Pro - Diagnostic AJAX</h1>';

        echo '<h2>Test 1: Actions enregistrées</h2>';
        echo '<p>wp_ajax_pdf_builder_preview: ' . (has_action('wp_ajax_pdf_builder_preview') ? 'OUI' : 'NON') . '</p>';
        echo '<p>wp_ajax_nopriv_pdf_builder_preview: ' . (has_action('wp_ajax_nopriv_pdf_builder_preview') ? 'OUI' : 'NON') . '</p>';

        echo '<h2>Test 2: Test AJAX direct</h2>';
        echo '<button onclick="testAjax()">Tester AJAX</button>';
        echo '<div id="result"></div>';

        echo '<script>
        function testAjax() {
            fetch("/wp-admin/admin-ajax.php?action=pdf_builder_test", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "test=data"
            })
            .then(response => {
                document.getElementById("result").innerHTML = "Status: " + response.status;
                return response.text();
            })
            .then(data => {
                document.getElementById("result").innerHTML += "<br>Réponse: " + data;
            })
            .catch(error => {
                document.getElementById("result").innerHTML = "Erreur: " + error;
            });
        }
        </script>';

        echo '<h2>Test 3: Variables JavaScript</h2>';
        echo '<p>Vérifiez que pdfBuilderAjax.nonce existe dans la console</p>';

        exit;
    }
});