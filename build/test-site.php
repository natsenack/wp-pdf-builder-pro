<?php
// Test ultra-simple du site
echo "<h1>TEST SITE PDF BUILDER</h1>";
echo "<p>Si tu vois ce message, le site fonctionne !</p>";
echo "<p>Security Validator: " . (class_exists('PDF_Builder\\Core\\PDF_Builder_Security_Validator') ? 'OK' : 'ERREUR') . "</p>";
echo "<p>WordPress: " . (function_exists('wp_get_current_user') ? 'OK' : 'ERREUR') . "</p>";
?>