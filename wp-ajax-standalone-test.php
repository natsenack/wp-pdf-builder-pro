<?php
/**
 * Test AJAX WordPress - Version Standalone
 * Placez ce fichier dans le répertoire racine de WordPress
 * URL d'accès: https://threeaxe.fr/wp-ajax-standalone-test.php
 */

// Test basique AJAX WordPress
add_action('wp_ajax_test_basic_standalone', 'test_basic_ajax_handler_standalone');
add_action('wp_ajax_nopriv_test_basic_standalone', 'test_basic_ajax_handler_standalone');

function test_basic_ajax_handler_standalone() {
    wp_send_json_success([
        'message' => 'WordPress AJAX fonctionne',
        'method' => $_SERVER['REQUEST_METHOD'],
        'time' => time(),
        'user_logged_in' => is_user_logged_in()
    ]);
}

// Test heartbeat (fonctionnalité WordPress native)
add_action('wp_ajax_nopriv_heartbeat_standalone', 'test_heartbeat_standalone');
add_action('wp_ajax_heartbeat_standalone', 'test_heartbeat_standalone');

function test_heartbeat_standalone() {
    wp_send_json_success(['message' => 'Heartbeat standalone fonctionne']);
}

// Test notre action PDF Builder
add_action('wp_ajax_pdf_builder_test_standalone', 'pdf_builder_test_standalone');
add_action('wp_ajax_nopriv_pdf_builder_test_standalone', 'pdf_builder_test_standalone');

function pdf_builder_test_standalone() {
    wp_send_json_success([
        'message' => 'Action PDF Builder standalone fonctionne',
        'timestamp' => time(),
        'user_logged_in' => is_user_logged_in(),
        'current_user_can_read' => current_user_can('read'),
        'actions_registered' => [
            'wp_ajax_pdf_builder_preview' => has_action('wp_ajax_pdf_builder_preview'),
            'wp_ajax_nopriv_pdf_builder_preview' => has_action('wp_ajax_nopriv_pdf_builder_preview')
        ]
    ]);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX WordPress Standalone</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .result { margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 3px; }
        .error { background: #ffe6e6; border: 1px solid #ffcccc; }
        .success { background: #e6ffe6; border: 1px solid #ccffcc; }
        button { padding: 10px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>🧪 Test AJAX WordPress - Version Standalone</h1>
    <p>Cette page teste directement les fonctionnalités AJAX de WordPress.</p>

    <div class="test-section">
        <h2>Test 1: AJAX WordPress basique</h2>
        <button id="test-basic">Tester AJAX basique</button>
        <div id="result-basic" class="result"></div>
    </div>

    <div class="test-section">
        <h2>Test 2: Heartbeat WordPress</h2>
        <button id="test-heartbeat">Tester Heartbeat</button>
        <div id="result-heartbeat" class="result"></div>
    </div>

    <div class="test-section">
        <h2>Test 3: Action PDF Builder</h2>
        <button id="test-custom">Tester PDF Builder</button>
        <div id="result-custom" class="result"></div>
    </div>

    <div class="test-section">
        <h2>Informations système</h2>
        <p><strong>Actions enregistrées:</strong></p>
        <ul>
            <li>wp_ajax_test_basic_standalone: <?php echo has_action('wp_ajax_test_basic_standalone') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
            <li>wp_ajax_nopriv_test_basic_standalone: <?php echo has_action('wp_ajax_nopriv_test_basic_standalone') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
            <li>wp_ajax_heartbeat_standalone: <?php echo has_action('wp_ajax_heartbeat_standalone') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
            <li>wp_ajax_nopriv_heartbeat_standalone: <?php echo has_action('wp_ajax_nopriv_heartbeat_standalone') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
            <li>wp_ajax_pdf_builder_test_standalone: <?php echo has_action('wp_ajax_pdf_builder_test_standalone') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
            <li>wp_ajax_pdf_builder_preview: <?php echo has_action('wp_ajax_pdf_builder_preview') ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>'; ?></li>
        </ul>

        <p><strong>Utilisateur connecté:</strong> <?php echo is_user_logged_in() ? 'OUI' : 'NON'; ?></p>
        <p><strong>Peut lire:</strong> <?php echo current_user_can('read') ? 'OUI' : 'NON'; ?></p>
        <p><strong>WordPress version:</strong> <?php echo get_bloginfo('version'); ?></p>
    </div>

    <script>
    function showResult(elementId, data, isError = false) {
        const element = $('#' + elementId);
        element.removeClass('error success');
        element.addClass(isError ? 'error' : 'success');
        if (typeof data === 'object') {
            element.html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
        } else {
            element.html(data);
        }
    }

    $('#test-basic').click(function() {
        $('#result-basic').html('Test en cours...');
        $.post('/wp-admin/admin-ajax.php', {
            action: 'test_basic_standalone'
        })
        .done(function(response) {
            showResult('result-basic', response);
        })
        .fail(function(xhr, status, error) {
            showResult('result-basic', 'Erreur ' + xhr.status + ': ' + error + '<br>Réponse: ' + xhr.responseText, true);
        });
    });

    $('#test-heartbeat').click(function() {
        $('#result-heartbeat').html('Test en cours...');
        $.post('/wp-admin/admin-ajax.php', {
            action: 'heartbeat_standalone',
            data: {}
        })
        .done(function(response) {
            showResult('result-heartbeat', response);
        })
        .fail(function(xhr, status, error) {
            showResult('result-heartbeat', 'Erreur ' + xhr.status + ': ' + error + '<br>Réponse: ' + xhr.responseText, true);
        });
    });

    $('#test-custom').click(function() {
        $('#result-custom').html('Test en cours...');
        $.post('/wp-admin/admin-ajax.php', {
            action: 'pdf_builder_test_standalone'
        })
        .done(function(response) {
            showResult('result-custom', response);
        })
        .fail(function(xhr, status, error) {
            showResult('result-custom', 'Erreur ' + xhr.status + ': ' + error + '<br>Réponse: ' + xhr.responseText, true);
        });
    });
    </script>
</body>
</html>