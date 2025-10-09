<?php
/**
 * Test AJAX WordPress de base
 * Créez ce fichier dans le répertoire racine de WordPress
 */

// Test basique AJAX WordPress
add_action('wp_ajax_test_basic', 'test_basic_ajax_handler');
add_action('wp_ajax_nopriv_test_basic', 'test_basic_ajax_handler');

function test_basic_ajax_handler() {
    wp_send_json_success([
        'message' => 'WordPress AJAX fonctionne',
        'method' => $_SERVER['REQUEST_METHOD'],
        'time' => time()
    ]);
}

// Test heartbeat (fonctionnalité WordPress native)
add_action('wp_ajax_nopriv_heartbeat', 'test_heartbeat');
add_action('wp_ajax_heartbeat', 'test_heartbeat');

function test_heartbeat() {
    wp_send_json_success(['message' => 'Heartbeat fonctionne']);
}

// Page de test accessible via navigateur
add_action('wp_loaded', function() {
    if (isset($_GET['wp_ajax_test'])) {
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Test AJAX WordPress</title>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        </head>
        <body>
            <h1>Test AJAX WordPress de Base</h1>

            <h2>Test 1: AJAX WordPress basique</h2>
            <button id="test-basic">Tester AJAX basique</button>
            <div id="result-basic"></div>

            <h2>Test 2: Heartbeat WordPress</h2>
            <button id="test-heartbeat">Tester Heartbeat</button>
            <div id="result-heartbeat"></div>

            <h2>Test 3: Notre action personnalisée</h2>
            <button id="test-custom">Tester notre action</button>
            <div id="result-custom"></div>

            <h2>Informations système</h2>
            <p>Actions enregistrées:</p>
            <ul>
                <li>wp_ajax_test_basic: <?php echo has_action('wp_ajax_test_basic') ? 'OUI' : 'NON'; ?></li>
                <li>wp_ajax_nopriv_test_basic: <?php echo has_action('wp_ajax_nopriv_test_basic') ? 'OUI' : 'NON'; ?></li>
                <li>wp_ajax_heartbeat: <?php echo has_action('wp_ajax_heartbeat') ? 'OUI' : 'NON'; ?></li>
                <li>wp_ajax_nopriv_heartbeat: <?php echo has_action('wp_ajax_nopriv_heartbeat') ? 'OUI' : 'NON'; ?></li>
                <li>wp_ajax_pdf_builder_test: <?php echo has_action('wp_ajax_pdf_builder_test') ? 'OUI' : 'NON'; ?></li>
            </ul>

            <script>
            $('#test-basic').click(function() {
                $('#result-basic').html('Test en cours...');
                $.post('/wp-admin/admin-ajax.php', {
                    action: 'test_basic'
                })
                .done(function(response) {
                    $('#result-basic').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                })
                .fail(function(xhr, status, error) {
                    $('#result-basic').html('<strong style="color:red">Erreur ' + xhr.status + ': ' + error + '</strong><br>Réponse: ' + xhr.responseText);
                });
            });

            $('#test-heartbeat').click(function() {
                $('#result-heartbeat').html('Test en cours...');
                $.post('/wp-admin/admin-ajax.php', {
                    action: 'heartbeat',
                    data: {}
                })
                .done(function(response) {
                    $('#result-heartbeat').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                })
                .fail(function(xhr, status, error) {
                    $('#result-heartbeat').html('<strong style="color:red">Erreur ' + xhr.status + ': ' + error + '</strong><br>Réponse: ' + xhr.responseText);
                });
            });

            $('#test-custom').click(function() {
                $('#result-custom').html('Test en cours...');
                $.post('/wp-admin/admin-ajax.php', {
                    action: 'pdf_builder_test'
                })
                .done(function(response) {
                    $('#result-custom').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                })
                .fail(function(xhr, status, error) {
                    $('#result-custom').html('<strong style="color:red">Erreur ' + xhr.status + ': ' + error + '</strong><br>Réponse: ' + xhr.responseText);
                });
            });
            </script>
        </body>
        </html>
        <?php
        exit;
    }
});