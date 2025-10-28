<?php
// Test simple pour vérifier le déploiement
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Test de déploiement réussi</h1>';
echo '<p>Timestamp: ' . time() . '</p>';
echo '<p>Si vous voyez cette page, le déploiement fonctionne !</p>';
echo '<p><a href="debug_templates.php">Aller au script de debug complet</a></p>';
?>