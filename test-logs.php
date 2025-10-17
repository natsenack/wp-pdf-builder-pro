<?php
/**
 * Test des logs PHP - PDF Builder Pro
 * Vérifie les logs d'enqueue des scripts
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Logs PHP - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Test des logs PHP - PDF Builder Pro</h1>

    <div class="info">
        <h3>Instructions :</h3>
        <ol>
            <li>Accédez à la page d'édition : <a href="https://threeaxe.fr/wp-admin/admin.php?page=pdf-builder-editor&template_id=1" target="_blank">Éditeur PDF</a></li>
            <li>Vérifiez la console du navigateur pour les erreurs JavaScript</li>
            <li>Vérifiez les logs PHP dans le fichier de debug WordPress ou les logs du serveur</li>
            <li>Revenez ici pour voir si les scripts sont chargés</li>
        </ol>
    </div>

    <div class="warning">
        <h3>Logs PHP attendus :</h3>
        <p>Si tout fonctionne, vous devriez voir dans les logs PHP :</p>
        <ul>
            <li><code>PDF Builder: enqueue_admin_scripts called with hook: pdf-builder_page_pdf-builder-editor</code></li>
            <li><code>PDF Builder: Current page: pdf-builder-editor</code></li>
            <li><code>PDF Builder: Hook pdf-builder_page_pdf-builder-editor allowed, proceeding with script loading</code></li>
        </ul>
    </div>

    <div class="info">
        <h3>Test des assets :</h3>
        <p>Après avoir visité la page d'édition, vérifiez si les scripts apparaissent dans le HTML source :</p>
        <ul>
            <li><code>pdf-builder-admin.js</code></li>
            <li><code>pdfBuilderAjax</code> (variable JavaScript)</li>
        </ul>
    </div>

    <p><a href="test-assets.php">← Retour au test des assets</a></p>
</body>
</html>