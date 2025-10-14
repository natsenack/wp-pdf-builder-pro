<?php
// Test PHP simple pour diagnostiquer le serveur
echo "<h1>Test PHP - threeaxe.fr</h1>";
echo "<p>Si vous voyez ce message, PHP fonctionne correctement.</p>";
echo "<p>Date/heure du serveur: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Version PHP: " . phpversion() . "</p>";
echo "<p>Serveur: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
phpinfo();
?>