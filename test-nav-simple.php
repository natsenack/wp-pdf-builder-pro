<?php
/**
 * Test simple de navigation des onglets - Version autonome
 */
if (!defined('ABSPATH')) {
    // Mode standalone pour test
    define('ABSPATH', dirname(__FILE__) . '/');
}
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Navigation - PDF Builder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .nav-tab-wrapper {
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }
        
        .nav-tab {
            display: inline-block;
            padding: 10px 15px;
            background: #f1f1f1;
            border: 1px solid #ccc;
            border-bottom: none;
            text-decoration: none;
            color: #333;
            margin-right: 5px;
            cursor: pointer;
        }
        
        .nav-tab.nav-tab-active {
            background: #fff;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        
        .tab-content {
            padding: 20px;
            border: 1px solid #ccc;
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .debug {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>Test Navigation Simple - PDF Builder</h1>
    
    <div class="debug">
        <strong>📊 STATUT:</strong> <span id="status">Initialisation...</span><br>
        <strong>⏰ Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?>
    </div>

    <nav class="nav-tab-wrapper" id="pdf-builder-tabs" role="tablist">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab">Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence" role="tab">Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme" role="tab">Système</a>
        <a href="#securite" class="nav-tab" data-tab="securite" role="tab">Sécurité</a>
    </nav>

    <section id="pdf-builder-tab-content">
        <div id="general" class="tab-content active">
            <h2>Contenu Général</h2>
            <p>Ceci est l'onglet <strong>Général</strong>.</p>
        </div>
        
        <div id="licence" class="tab-content">
            <h2>Contenu Licence</h2>
            <p>Ceci est l'onglet <strong>Licence</strong>.</p>
        </div>
        
        <div id="systeme" class="tab-content">
            <h2>Contenu Système</h2>
            <p>Ceci est l'onglet <strong>Système</strong>.</p>
        </div>
        
        <div id="securite" class="tab-content">
            <h2>Contenu Sécurité</h2>
            <p>Ceci est l'onglet <strong>Sécurité</strong>.</p>
        </div>
    </section>

    <script>
        // Configuration de diagnostic
        window.PDF_BUILDER_CONFIG = {
            debug: true
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('status').innerHTML = 'DOM chargé, test en cours...';
            
            // Test simple de navigation
            setTimeout(function() {
                const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
                const contents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
                
                document.getElementById('status').innerHTML = 
                    `✅ ${tabs.length} onglets, ${contents.length} contenus trouvés`;
                
                // Test de navigation manuelle
                tabs.forEach(function(tab, index) {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const tabId = tab.getAttribute('data-tab');
                        document.getElementById('status').innerHTML = 
                            `🖱️ Clic sur: ${tabId}`;
                        
                        // Désactiver tous
                        tabs.forEach(function(t) {
                            t.classList.remove('nav-tab-active');
                        });
                        contents.forEach(function(c) {
                            c.classList.remove('active');
                        });
                        
                        // Activer celui cliqué
                        tab.classList.add('nav-tab-active');
                        const target = document.getElementById(tabId);
                        if (target) {
                            target.classList.add('active');
                            document.getElementById('status').innerHTML = 
                                `✅ Navigation vers: ${tabId} réussie`;
                        }
                    });
                });
                
            }, 100);
        });
    </script>
</body>
</html>