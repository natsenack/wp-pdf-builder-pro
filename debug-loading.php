<?php
/**
 * Script de diagnostic complet pour identifier le problème de chargement
 */

echo "<h1>🔍 DIAGNOSTIC COMPLET - Chargement Script PDF Builder</h1>";

// Vérification 1: Structure des fichiers
echo "<h2>1. Structure des fichiers</h2>";
$base_dir = dirname(__FILE__);
$plugin_dir = $base_dir . '/plugin/templates/admin/settings-parts';
$assets_dir = $base_dir . '/assets/js';

echo "<ul>";
echo "<li><strong>Répertoire de base:</strong> " . htmlspecialchars($base_dir) . "</li>";
echo "<li><strong>Répertoire plugin:</strong> " . htmlspecialchars($plugin_dir) . " (existe: " . (is_dir($plugin_dir) ? 'OUI' : 'NON') . ")</li>";
echo "<li><strong>Répertoire assets:</strong> " . htmlspecialchars($assets_dir) . " (existe: " . (is_dir($assets_dir) ? 'OUI' : 'NON') . ")</li>";
echo "</ul>";

// Vérification 2: Fichier settings-main.php
echo "<h2>2. Fichier settings-main.php</h2>";
$settings_main = $plugin_dir . '/settings-main.php';
echo "<ul>";
echo "<li><strong>Chemin complet:</strong> " . htmlspecialchars($settings_main) . "</li>";
echo "<li><strong>Existe:</strong> " . (file_exists($settings_main) ? 'OUI' : 'NON') . "</li>";
if (file_exists($settings_main)) {
    echo "<li><strong>Taille:</strong> " . filesize($settings_main) . " octets</li>";
    echo "<li><strong>Modification:</strong> " . date('Y-m-d H:i:s', filemtime($settings_main)) . "</li>";
}
echo "</ul>";

// Vérification 3: Script settings-tabs.js
echo "<h2>3. Script settings-tabs.js</h2>";
$settings_tabs = $assets_dir . '/settings-tabs.js';
echo "<ul>";
echo "<li><strong>Chemin complet:</strong> " . htmlspecialchars($settings_tabs) . "</li>";
echo "<li><strong>Existe:</strong> " . (file_exists($settings_tabs) ? 'OUI' : 'NON') . "</li>";
if (file_exists($settings_tabs)) {
    echo "<li><strong>Taille:</strong> " . filesize($settings_tabs) . " octets</li>";
    echo "<li><strong>Modification:</strong> " . date('Y-m-d H:i:s', filemtime($settings_tabs)) . "</li>";
    
    // Afficher les premières lignes du script
    echo "<li><strong>Premières lignes:</strong><br><pre>";
    echo htmlspecialchars(substr(file_get_contents($settings_tabs), 0, 500));
    echo "...</pre></li>";
}
echo "</ul>";

// Vérification 4: URLs WordPress
echo "<h2>4. URLs WordPress (simulation)</h2>";
if (function_exists('plugins_url')) {
    $script_path = plugins_url('../assets/js/settings-tabs.js', $settings_main);
    echo "<ul>";
    echo "<li><strong>plugins_url:</strong> " . htmlspecialchars($script_path) . "</li>";
    echo "<li><strong>Chemin relatif:</strong> ../assets/js/settings-tabs.js</li>";
    echo "</ul>";
} else {
    echo "<p><em>plugins_url non disponible (pas dans WordPress)</em></p>";
}

// Vérification 5: Test de lecture directe
echo "<h2>5. Test de lecture directe du script</h2>";
if (file_exists($settings_tabs)) {
    $content = file_get_contents($settings_tabs);
    echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
    echo "<strong>Contenu du script (premiers 1000 caractères):</strong><br>";
    echo "<pre style='white-space: pre-wrap; font-size: 10px;'>";
    echo htmlspecialchars(substr($content, 0, 1000));
    if (strlen($content) > 1000) {
        echo "\n... (tronqué, " . (strlen($content) - 1000) . " caractères restants)";
    }
    echo "</pre>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Fichier settings-tabs.js introuvable!</p>";
}

// Vérification 6: Création du script de test
echo "<h2>6. Génération du script de test</h2>";
$test_script = $base_dir . '/test-script-direct.js';
if (file_exists($settings_tabs)) {
    $js_content = file_get_contents($settings_tabs);
    file_put_contents($test_script, $js_content);
    echo "<p>✅ Script copié vers: " . htmlspecialchars($test_script) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Impossible de copier le script (fichier source introuvable)</p>";
}

// Générer un test HTML autonome
echo "<h2>7. Test HTML autonome</h2>";
$test_html = $base_dir . '/test-standalone.html';
$standalone_html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Standalone - Navigation PDF Builder</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .nav-tab { display: inline-block; padding: 10px; background: #f0f0f0; margin: 2px; cursor: pointer; text-decoration: none; color: #333; }
        .nav-tab.active { background: #0073aa; color: white; }
        .tab-content { padding: 20px; border: 1px solid #ccc; margin-top: 10px; display: none; }
        .tab-content.active { display: block; }
        .status { background: #f9f9f9; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Test Standalone - Navigation PDF Builder</h1>
    <div class="status">
        <strong>Statut:</strong> <span id="status">Chargement...</span><br>
        <strong>Timestamp:</strong> {{TIMESTAMP}}
    </div>
    
    <nav id="pdf-builder-tabs">
        <a href="#general" class="nav-tab active" data-tab="general">Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence">Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme">Système</a>
        <a href="#securite" class="nav-tab" data-tab="securite">Sécurité</a>
    </nav>
    
    <section id="pdf-builder-tab-content">
        <div id="general" class="tab-content active"><h2>Général</h2><p>Contenu de l'onglet Général</p></div>
        <div id="licence" class="tab-content"><h2>Licence</h2><p>Contenu de l'onglet Licence</p></div>
        <div id="systeme" class="tab-content"><h2>Système</h2><p>Contenu de l'onglet Système</p></div>
        <div id="securite" class="tab-content"><h2>Sécurité</h2><p>Contenu de l'onglet Sécurité</p></div>
    </section>
    
    <script>
        // Configuration
        window.PDF_BUILDER_CONFIG = { debug: true };
        
        console.log('🔥 Test standalone chargé');
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔥 DOM Content Loaded');
            
            const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
            const contents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
            
            document.getElementById('status').innerHTML = \`Trouvé \${tabs.length} onglets et \${contents.length} contenus\`;
            
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const tabId = tab.getAttribute('data-tab');
                    console.log('🖱️ Clic sur:', tabId);
                    
                    // Désactiver tous
                    tabs.forEach(function(t) { t.classList.remove('active'); });
                    contents.forEach(function(c) { c.classList.remove('active'); });
                    
                    // Activer
                    tab.classList.add('active');
                    const target = document.getElementById(tabId);
                    if (target) {
                        target.classList.add('active');
                        document.getElementById('status').innerHTML = \`✅ Navigation vers \${tabId} réussie\`;
                    }
                });
            });
            
            setTimeout(function() {
                document.getElementById('status').innerHTML = '✅ Navigation initialisée avec succès';
            }, 500);
        });
    </script>
</body>
</html>
HTML;

$standalone_html = str_replace('{{TIMESTAMP}}', date('Y-m-d H:i:s'), $standalone_html);
file_put_contents($test_html, $standalone_html);
echo "<p>✅ Test HTML généré: <a href='test-standalone.html' target='_blank'>Ouvrir le test</a></p>";

echo "<h2>📋 Instructions de test</h2>";
echo "<ol>";
echo "<li><strong>Test WordPress:</strong> Allez sur la page des paramètres PDF Builder et regardez la console du navigateur pour les logs de diagnostic.</li>";
echo "<li><strong>Test direct:</strong> Ajoutez <code>?test-direct=true</code> à l'URL de la page des paramètres pour charger le script directement.</li>";
echo "<li><strong>Test standalone:</strong> Ouvrez <a href='test-standalone.html' target='_blank'>ce fichier</a> pour tester la navigation sans WordPress.</li>";
echo "</ol>";

echo "<p><strong>Dernière mise à jour:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>