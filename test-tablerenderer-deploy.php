<?php
// Test de vérification des modifications TableRenderer
echo "<h1>🔍 Test des Modifications TableRenderer</h1>";
echo "<p>Si vous voyez cette page, les fichiers sont déployés.</p>";

// Vérifier si le fichier TableRenderer existe et contient nos modifications
$tableRendererPath = __DIR__ . '/../resources/js/components/preview-system/renderers/TableRenderer.jsx';

if (file_exists($tableRendererPath)) {
    $content = file_get_contents($tableRendererPath);

    echo "<h2>✅ Fichier TableRenderer trouvé</h2>";

    // Vérifier nos modifications spécifiques
    $checks = [
        'data-table-renderer-version' => strpos($content, 'data-table-renderer-version="improved-totals-alignment"') !== false,
        'priceColumnIndex' => strpos($content, 'priceColumnIndex') !== false,
        'findIndex header' => strpos($content, 'findIndex(header =>') !== false,
        'Array.from length finalHeaders' => strpos($content, 'Array.from({ length: finalHeaders.length }') !== false
    ];

    echo "<h3>Modifications détectées :</h3><ul>";
    foreach ($checks as $check => $result) {
        $status = $result ? '✅' : '❌';
        echo "<li>$status $check</li>";
    }
    echo "</ul>";

    $allGood = !in_array(false, $checks);
    if ($allGood) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Toutes les modifications sont présentes dans le fichier !</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Certaines modifications sont manquantes.</p>";
    }

} else {
    echo "<h2 style='color: red;'>❌ Fichier TableRenderer NON trouvé</h2>";
    echo "<p>Chemin vérifié : $tableRendererPath</p>";
}

echo "<hr>";
echo "<h2>📋 Instructions pour l'utilisateur</h2>";
echo "<ol>";
echo "<li>Si toutes les modifications sont détectées : le problème vient du cache navigateur</li>";
echo "<li>Videz le cache : Ctrl+F5 ou Cmd+Shift+R</li>";
echo "<li>Allez dans WP Admin → Réglages → Permaliens → Enregistrer</li>";
echo "<li>Testez à nouveau sur la page d'édition PDF Builder</li>";
echo "</ol>";
?>