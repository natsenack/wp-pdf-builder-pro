<?php
/**
 * Test AJAX WordPress - Version Simplifiée
 * Inclut WordPress correctement
 */

// Inclure WordPress
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('Erreur: Impossible de trouver wp-load.php');
}

// Test basique AJAX WordPress
add_action('wp_ajax_test_basic_simple', 'test_basic_ajax_handler_simple');
add_action('wp_ajax_nopriv_test_basic_simple', 'test_basic_ajax_handler_simple');

function test_basic_ajax_handler_simple() {
    wp_send_json_success([
        'message' => 'WordPress AJAX fonctionne',
        'method' => $_SERVER['REQUEST_METHOD'],
        'time' => time(),
        'user_logged_in' => is_user_logged_in()
    ]);
}

// Test notre action PDF Builder
add_action('wp_ajax_pdf_builder_test_simple', 'pdf_builder_test_simple');
add_action('wp_ajax_nopriv_pdf_builder_test_simple', 'pdf_builder_test_simple');

function pdf_builder_test_simple() {
    wp_send_json_success([
        'message' => 'Action PDF Builder simple fonctionne',
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
    <title>Test AJAX WordPress Simple</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .result { margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 3px; font-family: monospace; }
        .error { background: #ffe6e6; border: 1px solid #ffcccc; color: #d63031; }
        .success { background: #e6ffe6; border: 1px solid #ccffcc; color: #00b894; }
        button { padding: 10px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
        button:hover { background: #005a87; }
        .info { background: #e3f2fd; border: 1px solid #bbdefb; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test AJAX WordPress - Version Simple</h1>
        <p>Cette page teste les fonctionnalités AJAX de WordPress en incluant WordPress correctement.</p>

        <div class="info">
            <strong>Statut WordPress:</strong>
            <ul>
                <li>WordPress chargé: ✅</li>
                <li>Utilisateur connecté: <?php echo is_user_logged_in() ? '✅ OUI' : '❌ NON'; ?></li>
                <li>Peut lire: <?php echo current_user_can('read') ? '✅ OUI' : '❌ NON'; ?></li>
                <li>Version WordPress: <?php echo get_bloginfo('version'); ?></li>
            </ul>
        </div>

        <div class="test-section">
            <h2>Test 1: AJAX WordPress basique</h2>
            <button id="test-basic">Tester AJAX basique</button>
            <div id="result-basic" class="result"></div>
        </div>

        <div class="test-section">
            <h2>Test 2: Action PDF Builder</h2>
            <button id="test-custom">Tester PDF Builder</button>
            <div id="result-custom" class="result"></div>
        </div>

        <div class="test-section">
            <h2>Informations système</h2>
            <p><strong>Actions enregistrées:</strong></p>
            <ul>
                <li>wp_ajax_test_basic_simple: <?php echo has_action('wp_ajax_test_basic_simple') ? '<span style="color:green">✅ OUI</span>' : '<span style="color:red">❌ NON</span>'; ?></li>
                <li>wp_ajax_nopriv_test_basic_simple: <?php echo has_action('wp_ajax_nopriv_test_basic_simple') ? '<span style="color:green">✅ OUI</span>' : '<span style="color:red">❌ NON</span>'; ?></li>
                <li>wp_ajax_pdf_builder_test_simple: <?php echo has_action('wp_ajax_pdf_builder_test_simple') ? '<span style="color:green">✅ OUI</span>' : '<span style="color:red">❌ NON</span>'; ?></li>
                <li>wp_ajax_pdf_builder_preview: <?php echo has_action('wp_ajax_pdf_builder_preview') ? '<span style="color:green">✅ OUI</span>' : '<span style="color:red">❌ NON</span>'; ?></li>
            </ul>
        </div>
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
        $('#result-basic').html('🔄 Test en cours...');
        $.post('/wp-admin/admin-ajax.php', {
            action: 'test_basic_simple'
        })
        .done(function(response) {
            showResult('result-basic', response);
        })
        .fail(function(xhr, status, error) {
            showResult('result-basic', '❌ Erreur ' + xhr.status + ': ' + error + '<br>Réponse: ' + xhr.responseText, true);
        });
    });

    $('#test-custom').click(function() {
        $('#result-custom').html('🔄 Test en cours...');
        $.post('/wp-admin/admin-ajax.php', {
            action: 'pdf_builder_test_simple'
        })
        .done(function(response) {
            showResult('result-custom', response);
        })
        .fail(function(xhr, status, error) {
            showResult('result-custom', '❌ Erreur ' + xhr.status + ': ' + error + '<br>Réponse: ' + xhr.responseText, true);
        });
    });
    </script>
</body>
</html>