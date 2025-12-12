<?php
/**
 * Test de validation finale de la syntaxe JavaScript
 */

// Fonction pour analyser la syntaxe JavaScript basique
function checkBasicJSSyntax($filePath) {
    $content = file_get_contents($filePath);

    // Compter les parenthèses ouvrantes et fermantes
    $openParens = substr_count($content, '(');
    $closeParens = substr_count($content, ')');

    // Compter les accolades ouvrantes et fermantes
    $openBraces = substr_count($content, '{');
    $closeBraces = substr_count($content, '}');

    // Vérifier les appels de méthodes problématiques
    $invalidFocusCalls = preg_match('/document\.body\.focus\(\)/', $content);

    return [
        'parentheses' => ['open' => $openParens, 'close' => $closeParens, 'balanced' => $openParens === $closeParens],
        'braces' => ['open' => $openBraces, 'close' => $closeBraces, 'balanced' => $openBraces === $closeBraces],
        'invalid_focus_calls' => $invalidFocusCalls
    ];
}

// Tester le fichier corrigé
$file = 'plugin/resources/templates/admin/settings-parts/settings-contenu.php';
$syntax = checkBasicJSSyntax($file);

echo "🔍 VALIDATION FINALE SYNTAXE JAVASCRIPT\n";
echo "=======================================\n\n";

echo "📊 Analyse du fichier: $file\n";
echo "   - Parenthèses: {$syntax['parentheses']['open']} ouvrantes, {$syntax['parentheses']['close']} fermantes\n";
echo "   - Accolades: {$syntax['braces']['open']} ouvrantes, {$syntax['braces']['close']} fermantes\n";
echo "   - Appels focus() invalides: " . ($syntax['invalid_focus_calls'] ? 'OUI ❌' : 'NON ✅') . "\n\n";

$allGood = $syntax['parentheses']['balanced'] && $syntax['braces']['balanced'] && !$syntax['invalid_focus_calls'];

if ($allGood) {
    echo "✅ VALIDATION RÉUSSIE: Syntaxe JavaScript corrigée !\n";
    echo "   Les modales devraient maintenant fonctionner sans erreur.\n\n";
} else {
    echo "❌ PROBLÈMES DÉTECTÉS:\n";
    if (!$syntax['parentheses']['balanced']) echo "   - Parenthèses déséquilibrées\n";
    if (!$syntax['braces']['balanced']) echo "   - Accolades déséquilibrées\n";
    if ($syntax['invalid_focus_calls']) echo "   - Appels focus() invalides présents\n";
    echo "\n";
}

echo "🎯 PROCHAINES ÉTAPES:\n";
echo "   1. Actualiser la page WordPress Admin\n";
echo "   2. Vérifier que l'erreur 'missing ) after argument list' a disparu\n";
echo "   3. Tester l'ouverture et la fermeture des modales\n";
echo "   4. Vérifier que les paramètres s'appliquent correctement\n\n";

echo "=======================================\n";
echo "FIN DE LA VALIDATION\n";
?>