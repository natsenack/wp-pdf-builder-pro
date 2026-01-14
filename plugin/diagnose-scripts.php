<?php
/**
 * Script de diagnostic pour identifier les sources des scripts enregistrés
 * À placer dans plugin/ et appeler via wp-admin
 */

// Charger WordPress
if (!function_exists('wp_enqueue_script')) {
    require_once dirname(__DIR__) . '/wp-load.php';
}

// Vérifier les droits
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

global $wp_scripts;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic - Scripts WordPress</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .script-item { background: white; padding: 10px; margin: 5px 0; border-left: 4px solid #0073aa; }
        .error { border-left-color: #dc3545; color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        h2 { color: #23282d; }
        pre { background: #f9f9f9; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>📊 Diagnostic - Scripts Enregistrés</h1>
    
    <h2>Scripts potentiellement problématiques :</h2>
    <?php
    
    if (isset($wp_scripts) && $wp_scripts instanceof WP_Scripts) {
        $registered = $wp_scripts->registered;
        $problematic = array('webpage_content_reporter', 'content-reporter', 'snippet', 'isolated');
        
        foreach ($registered as $handle => $script) {
            foreach ($problematic as $pattern) {
                if (stripos($handle, $pattern) !== false) {
                    echo '<div class="script-item error">';
                    echo '<strong>' . esc_html($handle) . '</strong><br>';
                    echo 'Src: ' . ($script->src ? '<code>' . esc_url($script->src) . '</code>' : 'Inline') . '<br>';
                    echo 'Deps: ' . implode(', ', (array)$script->deps) . '<br>';
                    echo 'Ver: ' . esc_html($script->ver ?? 'N/A') . '<br>';
                    echo '</div>';
                }
            }
        }
    }
    ?>
    
    <h2>Tous les scripts enregistrés :</h2>
    <pre><?php 
        if (isset($wp_scripts) && $wp_scripts instanceof WP_Scripts) {
            $handles = array_keys($wp_scripts->registered);
            echo implode("\n", $handles);
        }
    ?></pre>
    
    <h2>ℹ️ Conseils :</h2>
    <ul>
        <li>Si "webpage_content_reporter" n'apparaît pas ici, c'est une <strong>extension Chrome</strong></li>
        <li>Les scripts commençant par "chrome-extension://" viennent du navigateur</li>
        <li>Vérifiez les extensions actives dans chrome://extensions/</li>
    </ul>
</body>
</html>
