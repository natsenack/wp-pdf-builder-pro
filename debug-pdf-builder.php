<?php
/**
 * Script de débogage pour PDF Builder Pro
 * À utiliser temporairement pour tester l'initialisation
 */

// En-têtes pour éviter la mise en cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-log { background: #f5f5f5; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba; }
        .error { background: #ffeaea; border-left-color: #dc3232; }
        .success { background: #eaffea; border-left-color: #46b450; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>Debug PDF Builder Pro Initialization</h1>
    <div id="debug-output"></div>

    <script>
        const output = document.getElementById('debug-output');

        function log(message, type = 'info') {
            const div = document.createElement('div');
            div.className = `debug-log ${type}`;
            div.innerHTML = `<strong>${new Date().toLocaleTimeString()}:</strong> ${message}`;
            output.appendChild(div);
            console.log(message);
        }

        // Test de l'initialisation
        log('Démarrage du test d\'initialisation PDF Builder Pro');

        // Vérifier si la fonction initializePDFBuilderPro existe
        if (typeof window.initializePDFBuilderPro === 'function') {
            log('✅ window.initializePDFBuilderPro est disponible', 'success');

            try {
                log('Appel de initializePDFBuilderPro()...');
                const result = window.initializePDFBuilderPro();
                log('✅ initializePDFBuilderPro() appelée avec succès', 'success');
                log('Résultat: ' + JSON.stringify(result, null, 2));

                // Vérifier les objets globaux après initialisation
                setTimeout(() => {
                    log('Vérification des objets globaux après initialisation:');
                    log('window.pdfBuilderPro: ' + (typeof window.pdfBuilderPro));
                    log('window.PDFBuilderPro: ' + (typeof window.PDFBuilderPro));

                    if (window.pdfBuilderPro) {
                        log('Propriétés de pdfBuilderPro: ' + Object.keys(window.pdfBuilderPro).join(', '), 'success');
                    }
                }, 100);

            } catch (error) {
                log('❌ Erreur lors de l\'appel de initializePDFBuilderPro: ' + error.message, 'error');
            }

        } else {
            log('❌ window.initializePDFBuilderPro n\'est pas disponible', 'error');
            log('Fonctions disponibles sur window: ' + Object.keys(window).filter(key => typeof window[key] === 'function').slice(0, 20).join(', '));
        }

        // Vérifier si le script se charge correctement
        window.addEventListener('load', function() {
            log('Page complètement chargée');
            log('Scripts chargés: ' + document.scripts.length);
        });

        // Vérifier les erreurs JavaScript
        window.addEventListener('error', function(e) {
            log('Erreur JavaScript: ' + e.message + ' à ' + e.filename + ':' + e.lineno, 'error');
        });
    </script>

    <!-- Simuler le chargement du script PDF Builder Pro -->
    <script src="assets/js/dist/pdf-builder-admin.js"></script>
</body>
</html>