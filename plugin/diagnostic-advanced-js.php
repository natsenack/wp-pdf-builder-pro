<?php
/**
 * Script de diagnostic avancé pour identifier l'erreur JavaScript "Unexpected end of input"
 * Ce script analyse la page générée et identifie les scripts malformés
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Vérifier si nous sommes sur la page des paramètres
if (!isset($_GET['page']) || $_GET['page'] !== 'pdf-builder-settings') {
    return;
}

echo "<!-- ADVANCED DIAGNOSTIC SCRIPT -->\n";

// Ajouter un script qui va analyser tous les scripts de la page
echo "<script>\n";
echo "(function() {\n";
echo "    'use strict';\n";
echo "    \n";
echo "    console.log('🔍 Advanced JavaScript Diagnostic Started');\n";
echo "    \n";
echo "    // Analyser tous les scripts de la page\n";
echo "    var scripts = document.querySelectorAll('script');\n";
echo "    var scriptCount = scripts.length;\n";
echo "    console.log('📊 Total scripts found:', scriptCount);\n";
echo "    \n";
echo "    // Vérifier chaque script pour les erreurs de syntaxe\n";
echo "    for (var i = 0; i < scriptCount; i++) {\n";
echo "        var script = scripts[i];\n";
echo "        var scriptContent = script.textContent || script.innerText || '';\n";
echo "        \n";
echo "        // Ne vérifier que les scripts avec du contenu\n";
echo "        if (scriptContent.trim().length > 0) {\n";
echo "            try {\n";
echo "                // Essayer de parser le script\n";
echo "                eval(scriptContent);\n";
echo "                console.log('✅ Script', i, 'is valid');\n";
echo "            } catch (syntaxError) {\n";
echo "                console.error('❌ Script', i, 'has syntax error:', syntaxError.message);\n";
echo "                console.error('📝 Script content (first 200 chars):', scriptContent.substring(0, 200));\n";
echo "                \n";
echo "                // Chercher les problèmes courants\n";
echo "                var openBraces = (scriptContent.match(/{/g) || []).length;\n";
echo "                var closeBraces = (scriptContent.match(/}/g) || []).length;\n";
echo "                var openParens = (scriptContent.match(/\(/g) || []).length;\n";
echo "                var closeParens = (scriptContent.match(/\)/g) || []).length;\n";
echo "                var openBrackets = (scriptContent.match(/\[/g) || []).length;\n";
echo "                var closeBrackets = (scriptContent.match(/\]/g) || []).length;\n";
echo "                \n";
echo "                console.error('🔍 Braces: {', openBraces, '} ', closeBraces);\n";
echo "                console.error('🔍 Parens: (', openParens, ') ', closeParens);\n";
echo "                console.error('🔍 Brackets: [', openBrackets, '] ', closeBrackets);\n";
echo "                \n";
echo "                if (openBraces !== closeBraces) {\n";
echo "                    console.error('🚨 Brace mismatch detected!');\n";
echo "                }\n";
echo "                if (openParens !== closeParens) {\n";
echo "                    console.error('🚨 Parenthesis mismatch detected!');\n";
echo "                }\n";
echo "                if (openBrackets !== closeBrackets) {\n";
echo "                    console.error('🚨 Bracket mismatch detected!');\n";
echo "                }\n";
echo "                \n";
echo "                // Arrêter l'analyse après la première erreur trouvée\n";
echo "                break;\n";
echo "            }\n";
echo "        }\n";
echo "    }\n";
echo "    \n";
echo "    console.log('🔍 Advanced JavaScript Diagnostic Completed');\n";
echo "})();\n";
echo "</script>\n";
?>