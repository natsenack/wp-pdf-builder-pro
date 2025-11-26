<?php
/**
 * Script de diagnostic pour identifier la source de l'erreur JavaScript
 * "Unexpected end of input" à la ligne 7487
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Vérifier si nous sommes sur la page des paramètres
if (!isset($_GET['page']) || $_GET['page'] !== 'pdf-builder-settings') {
    return;
}

echo "<!-- DIAGNOSTIC SCRIPT - Checking for JavaScript errors -->\n";
echo "<script>\n";
echo "console.log('Diagnostic: Checking JavaScript syntax...');\n";

// Tester la syntaxe de base
echo "try {\n";
echo "  // Test basic JSON parsing\n";
echo "  var testJson = " . wp_json_encode(['test' => 'value']) . ";\n";
echo "  console.log('JSON parsing OK:', testJson);\n";
echo "} catch (e) {\n";
echo "  console.error('JSON parsing error:', e);\n";
echo "}\n";

echo "</script>\n";
?>