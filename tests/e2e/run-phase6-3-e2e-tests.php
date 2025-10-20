<?php
/**
 * Script d'exécution complète Phase 6.3 - Tests E2E
 * Exécute tous les tests end-to-end en séquence
 */

echo "🚀 PHASE 6.3 - TESTS E2E COMPLETS\n";
echo "=================================\n";
echo "Exécution de tous les tests E2E...\n\n";

$startTime = microtime(true);
$testResults = [];
$overallSuccess = true;

// Fonction pour exécuter un test et collecter les résultats
function runE2ETest($testFile, $testName) {
    global $testResults, $overallSuccess;

    echo "📋 Exécution: $testName\n";
    echo str_repeat("-", 50) . "\n";

    $output = [];
    $returnCode = 0;

    // Exécuter le fichier de test PHP
    exec("php \"$testFile\"", $output, $returnCode);

    // Analyser les résultats
    $success = $returnCode === 0;
    $overallSuccess = $overallSuccess && $success;

    $testResults[] = [
        'name' => $testName,
        'file' => basename($testFile),
        'success' => $success,
        'output' => implode("\n", $output),
        'duration' => 0 // Sera calculé plus tard
    ];

    echo implode("\n", $output) . "\n\n";

    return $success;
}

// Liste des tests à exécuter
$tests = [
    [
        'file' => __DIR__ . '/e2e-user-scenarios.php',
        'name' => 'Tests Scénarios Utilisateur'
    ],
    [
        'file' => __DIR__ . '/e2e-woocommerce-orders.php',
        'name' => 'Tests Commandes WooCommerce'
    ],
    [
        'file' => __DIR__ . '/e2e-browser-compatibility.php',
        'name' => 'Tests Compatibilité Navigateurs'
    ],
    [
        'file' => __DIR__ . '/e2e-device-responsiveness.php',
        'name' => 'Tests Responsive Design'
    ],
    [
        'file' => __DIR__ . '/e2e-network-conditions.php',
        'name' => 'Tests Conditions Réseau'
    ],
    [
        'file' => __DIR__ . '/e2e-playwright-integration.php',
        'name' => 'Intégration Playwright'
    ]
];

// Exécuter tous les tests
foreach ($tests as $test) {
    $testStartTime = microtime(true);
    $success = runE2ETest($test['file'], $test['name']);
    $testEndTime = microtime(true);

    // Mettre à jour la durée dans les résultats
    $lastIndex = count($testResults) - 1;
    $testResults[$lastIndex]['duration'] = round($testEndTime - $testStartTime, 2);
}

// Calculer le temps total
$endTime = microtime(true);
$totalDuration = round($endTime - $startTime, 2);

// Générer le rapport final
echo "📊 RAPPORT FINAL PHASE 6.3 - TESTS E2E\n";
echo str_repeat("=", 50) . "\n";
echo "Temps total d'exécution: {$totalDuration}s\n";
echo "Tests exécutés: " . count($testResults) . "\n";
echo "Tests réussis: " . count(array_filter($testResults, fn($r) => $r['success'])) . "\n";
echo "Tests échoués: " . count(array_filter($testResults, fn($r) => !$r['success'])) . "\n\n";

echo "Détail des résultats:\n";
echo str_repeat("-", 50) . "\n";

foreach ($testResults as $result) {
    $status = $result['success'] ? "✅ RÉUSSI" : "❌ ÉCHEC";
    echo sprintf("%-30s %s (%ss)\n",
        $result['name'],
        $status,
        $result['duration']
    );
}

echo "\n" . str_repeat("=", 50) . "\n";

if ($overallSuccess) {
    echo "🎉 PHASE 6.3 TERMINÉE AVEC SUCCÈS !\n";
    echo "Tous les tests E2E ont passé.\n";
    echo "Le système est prêt pour la production.\n\n";

    echo "📋 RÉSUMÉ DES TESTS RÉALISÉS:\n";
    echo "• Tests scénarios utilisateur complets\n";
    echo "• Tests intégration WooCommerce (tous statuts)\n";
    echo "• Tests compatibilité navigateurs (Chrome, Firefox, Safari, Edge)\n";
    echo "• Tests responsive design (Desktop, Tablette, Mobile)\n";
    echo "• Tests conditions réseau (rapide/lente, offline/online)\n";
    echo "• Intégration Playwright pour automation complète\n\n";

    echo "🚀 PROCHAINES ÉTAPES:\n";
    echo "• Phase 7: Documentation développeur et utilisateur\n";
    echo "• Déploiement en production\n";
    echo "• Monitoring et maintenance\n";

} else {
    echo "⚠️  PHASE 6.3 TERMINÉE AVEC DES ÉCHECS\n";
    echo "Certains tests E2E ont échoué.\n";
    echo "Vérifiez les logs ci-dessus pour les détails.\n\n";

    echo "🔧 ACTIONS RECOMMANDÉES:\n";
    echo "• Analyser les échecs dans les rapports détaillés\n";
    echo "• Corriger les problèmes identifiés\n";
    echo "• Ré-exécuter les tests défaillants\n";
    echo "• Contacter l'équipe de développement si nécessaire\n";
}

echo str_repeat("=", 50) . "\n";

// Créer un fichier de rapport JSON pour archivage
$reportData = [
    'phase' => '6.3',
    'name' => 'Tests E2E Complets',
    'timestamp' => date('Y-m-d H:i:s'),
    'duration' => $totalDuration,
    'overall_success' => $overallSuccess,
    'tests' => $testResults
];

file_put_contents(__DIR__ . '/phase6-3-e2e-report.json', json_encode($reportData, JSON_PRETTY_PRINT));

echo "📄 Rapport détaillé sauvegardé: phase6-3-e2e-report.json\n";
?>