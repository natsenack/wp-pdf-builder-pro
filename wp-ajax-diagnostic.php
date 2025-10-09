<?php
/**
 * Diagnostic AJAX WordPress - Identifier le plugin coupable
 */

// Inclure WordPress
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    die('Erreur: Impossible de trouver wp-load.php');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic AJAX WordPress</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .plugins { background: #fff3cd; border-color: #ffeaa7; }
        .security { background: #ffe6e6; border-color: #ffcccc; }
        .result { margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 3px; font-family: monospace; }
        .error { background: #ffe6e6; border: 1px solid #ffcccc; color: #d63031; }
        .success { background: #e6ffe6; border: 1px solid #ccffcc; color: #00b894; }
        button { padding: 10px 15px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
        button:hover { background: #005a87; }
        .plugin-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px; }
        .plugin-item { padding: 10px; background: white; border: 1px solid #ddd; border-radius: 3px; }
        .danger { background: #ffe6e6; border-color: #ffcccc; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic AJAX WordPress</h1>
        <p>Identifier le plugin ou la configuration qui bloque AJAX</p>

        <div class="section">
            <h2>📊 Statut actuel</h2>
            <div class="result">
                <strong>WordPress chargé:</strong> ✅<br>
                <strong>Utilisateur connecté:</strong> <?php echo is_user_logged_in() ? '✅ OUI' : '❌ NON'; ?><br>
                <strong>Version WordPress:</strong> <?php echo get_bloginfo('version'); ?><br>
                <strong>Test AJAX rapide:</strong> <span id="quick-test">🔄 Test en cours...</span>
            </div>
        </div>

        <div class="section plugins">
            <h2>🔌 Plugins suspects (à désactiver temporairement)</h2>
            <div class="plugin-list">
                <?php
                $suspicious_plugins = [
                    'wordfence/wordfence.php' => ['name' => 'Wordfence Security', 'danger' => true, 'description' => 'Firewall très strict'],
                    'ithemes-security-pro/ithemes-security-pro.php' => ['name' => 'iThemes Security Pro', 'danger' => true, 'description' => 'Sécurité avancée'],
                    'better-wp-security/better-wp-security.php' => ['name' => 'Better WP Security', 'danger' => true, 'description' => 'Sécurité renforcée'],
                    'sucuri-scanner/sucuri.php' => ['name' => 'Sucuri Security', 'danger' => true, 'description' => 'Scanner de sécurité'],
                    'wp-cerber/wp-cerber.php' => ['name' => 'WP Cerber', 'danger' => true, 'description' => 'Sécurité anti-hack'],
                    'all-in-one-wp-security-and-firewall/wp-security.php' => ['name' => 'All In One WP Security', 'danger' => true, 'description' => 'Suite de sécurité complète'],
                    'bulletproof-security/bulletproof-security.php' => ['name' => 'BulletProof Security', 'danger' => true, 'description' => '.htaccess security'],
                    'wp-simple-firewall/icwp-wpsf.php' => ['name' => 'Shield Security', 'danger' => true, 'description' => 'Firewall moderne'],
                    'wps-hide-login/wps-hide-login.php' => ['name' => 'WPS Hide Login', 'warning' => true, 'description' => 'Cache la page login'],
                    'wp-super-cache/wp-cache.php' => ['name' => 'WP Super Cache', 'warning' => true, 'description' => 'Cache agressif'],
                    'w3-total-cache/w3-total-cache.php' => ['name' => 'W3 Total Cache', 'warning' => true, 'description' => 'Cache complet'],
                    'wp-rocket/wp-rocket.php' => ['name' => 'WP Rocket', 'warning' => true, 'description' => 'Cache premium'],
                    'cloudflare/cloudflare.php' => ['name' => 'Cloudflare', 'warning' => true, 'description' => 'CDN avec WAF']
                ];

                $active_plugins = get_option('active_plugins', []);

                foreach ($suspicious_plugins as $plugin_file => $info) {
                    $is_active = in_array($plugin_file, $active_plugins);
                    $class = $info['danger'] ? 'danger' : ($info['warning'] ? 'warning' : '');
                    echo "<div class='plugin-item {$class}'>";
                    echo "<strong>{$info['name']}</strong><br>";
                    echo "<small>{$info['description']}</small><br>";
                    echo "<span style='color: " . ($is_active ? 'red' : 'green') . "'>" . ($is_active ? '❌ ACTIF' : '✅ INACTIF') . "</span>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <div class="section security">
            <h2>🛡️ Actions recommandées</h2>
            <ol>
                <li><strong>Désactivez temporairement</strong> les plugins marqués en rouge (danger)</li>
                <li><strong>Testez AJAX</strong> après chaque désactivation</li>
                <li><strong>Vérifiez les réglages</strong> des plugins de sécurité (whitelist admin-ajax.php)</li>
                <li><strong>Consultez les logs</strong> du plugin de sécurité pour voir les blocages</li>
                <li><strong>Contactez l'hébergeur</strong> si c'est ModSecurity ou un WAF serveur</li>
            </ol>
        </div>

        <div class="section">
            <h2>🧪 Test AJAX détaillé</h2>
            <button id="test-ajax">Tester AJAX maintenant</button>
            <div id="ajax-result" class="result"></div>
        </div>

        <div class="section">
            <h2>📝 Prochaines étapes</h2>
            <p>Une fois le plugin coupable identifié :</p>
            <ul>
                <li>Configurez le plugin pour whitelister <code>admin-ajax.php</code></li>
                <li>Ou trouvez une alternative moins intrusive</li>
                <li>Ou contactez le support du plugin pour une solution</li>
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

    // Test rapide au chargement
    $(document).ready(function() {
        $.post('/wp-admin/admin-ajax.php', {
            action: 'test_basic_simple'
        })
        .done(function(response) {
            $('#quick-test').html('✅ AJAX fonctionne').addClass('success');
        })
        .fail(function(xhr, status, error) {
            $('#quick-test').html('❌ AJAX bloqué (' + xhr.status + ')').addClass('error');
        });
    });

    $('#test-ajax').click(function() {
        $('#ajax-result').html('🔄 Test détaillé en cours...');
        $.post('/wp-admin/admin-ajax.php', {
            action: 'pdf_builder_test_simple'
        })
        .done(function(response) {
            showResult('ajax-result', response);
        })
        .fail(function(xhr, status, error) {
            showResult('ajax-result', '❌ Erreur ' + xhr.status + ': ' + error + '<br>Réponse: ' + xhr.responseText + '<br><br><strong>🔍 Cause probable:</strong> Plugin de sécurité ou WAF bloque la requête', true);
        });
    });
    </script>
</body>
</html>