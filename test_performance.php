<?php
/**
 * Test de performance des renderers - Phase 3.3.6
 * Mesure les performances après optimisation du cache
 */

require_once __DIR__ . '/src/Performance/PerformanceMonitor.php';
require_once __DIR__ . '/src/Cache/RendererCache.php';
require_once __DIR__ . '/src/Renderers/TextRenderer.php';
require_once __DIR__ . '/src/Renderers/TableRenderer.php';
require_once __DIR__ . '/src/Renderers/InfoRenderer.php';

// Démarrage de la surveillance
PerformanceMonitor::start();

echo "🚀 Test de Performance - Phase 3.3.6\n";
echo "=====================================\n\n";

// Test du cache
echo "1. Test du système de cache...\n";
$cache = new \PDF_Builder\Cache\RendererCache();

// Test des opérations de cache
$cache::set('test_key', 'test_value');
$value = $cache::get('test_key');
echo "   Cache opérationnel: " . ($value === 'test_value' ? '✅' : '❌') . "\n";

// Test des métriques de cache
$metrics = $cache::getMetrics();
echo "   Métriques de cache: " . ($metrics['total_requests'] > 0 ? '✅' : '❌') . "\n\n";

// Test des renderers
echo "2. Test des renderers avec cache...\n";

$context = [
    'customer' => [
        'full_name' => 'Jean Dupont',
        'email' => 'jean@example.com',
        'phone' => '01 23 45 67 89'
    ],
    'order' => [
        'number' => 'CMD-2025-001',
        'date' => '2025-01-15'
    ]
];

// Test TextRenderer
$textRenderer = new \PDF_Builder\Renderers\TextRenderer();

echo "   Test TextRenderer (x5 pour mesurer le cache)...\n";
for ($i = 1; $i <= 5; $i++) {
    $result = PerformanceMonitor::measure(function() use ($textRenderer, $context) {
        return $textRenderer->render([
            'type' => 'dynamic-text',
            'content' => 'Bonjour {{customer_full_name}}, commande {{order_number}}',
            'properties' => [
                'font-size' => '14px',
                'color' => '#000000',
                'font-weight' => 'bold'
            ]
        ], $context);
    }, [], "TextRenderer_run_{$i}");

    echo "     Run {$i}: " . (!empty($result['html']) ? '✅' : '❌') . "\n";
}

// Test TableRenderer
$tableRenderer = new \PDF_Builder\Renderers\TableRenderer();

echo "   Test TableRenderer (x3)...\n";
for ($i = 1; $i <= 3; $i++) {
    $result = PerformanceMonitor::measure(function() use ($tableRenderer, $context) {
        return $tableRenderer->render([
            'type' => 'product_table',
            'properties' => [
                'border-width' => '1px',
                'font-size' => '12px'
            ]
        ], array_merge($context, [
            'products' => [
                ['name' => 'Produit A', 'quantity' => 2, 'price' => 25.00],
                ['name' => 'Produit B', 'quantity' => 1, 'price' => 50.00]
            ]
        ]));
    }, [], "TableRenderer_run_{$i}");

    echo "     Run {$i}: " . (!empty($result['html']) ? '✅' : '❌') . "\n";
}

// Test InfoRenderer
$infoRenderer = new \PDF_Builder\Renderers\InfoRenderer();

echo "   Test InfoRenderer (x3)...\n";
for ($i = 1; $i <= 3; $i++) {
    $result = PerformanceMonitor::measure(function() use ($infoRenderer, $context) {
        return $infoRenderer->render([
            'type' => 'customer_info',
            'properties' => [
                'template' => 'default',
                'layout' => 'vertical',
                'font-size' => '13px'
            ]
        ], $context);
    }, [], "InfoRenderer_run_{$i}");

    echo "     Run {$i}: " . (!empty($result['html']) ? '✅' : '❌') . "\n";
}

// Rapport de performance
echo "\n3. Rapport de performance...\n";
$report = PerformanceMonitor::getReport();

echo "📊 Résumé:\n";
echo "   Temps total d'exécution: {$report['summary']['total_execution_time']}\n";
echo "   Nombre total de rendus: {$report['summary']['total_render_calls']}\n";
echo "   Temps moyen de rendu: {$report['summary']['average_render_time']}\n";
echo "   Pic d'utilisation mémoire: {$report['summary']['peak_memory_usage']}\n";

echo "\n⚡ Performance de rendu:\n";
echo "   Rendu le plus rapide: {$report['render_performance']['fastest_render']}\n";
echo "   Rendu le plus lent: {$report['render_performance']['slowest_render']}\n";
echo "   Temps médian: {$report['render_performance']['median_render_time']}\n";
echo "   Rendus < 500ms: {$report['render_performance']['renders_under_500ms']}\n";

echo "\n💾 Performance du cache:\n";
$cacheStats = $report['cache_performance'];
if (!empty($cacheStats)) {
    echo "   Taux de succès: " . ($cacheStats['hit_rate'] ?? 'N/A') . "%\n";
    echo "   Taille du cache: {$cacheStats['cache_size']} entrées\n";
    echo "   Utilisation mémoire: {$cacheStats['memory_usage']}\n";
} else {
    echo "   Aucune statistique de cache disponible\n";
}

// Vérification des seuils
echo "\n4. Vérification des seuils de performance...\n";
$thresholds = PerformanceMonitor::checkPerformanceThresholds();

$statusEmoji = [
    'excellent' => '🌟',
    'good' => '✅',
    'warning' => '⚠️',
    'critical' => '❌'
];

echo "   Temps de rendu OK (< 500ms): " . ($thresholds['render_time_ok'] ? '✅' : '❌') . "\n";
echo "   Utilisation mémoire OK (< 50MB): " . ($thresholds['memory_usage_ok'] ? '✅' : '❌') . "\n";
echo "   Taux de succès du cache (> 70%): " . ($thresholds['cache_hit_rate_ok'] ? '✅' : '❌') . "\n";
echo "   Aucun rendu lent (> 2s): " . ($thresholds['no_slow_renders'] ? '✅' : '❌') . "\n";
echo "   Statut global: {$statusEmoji[$thresholds['overall_status']]} {$thresholds['overall_status']}\n";

// Export des métriques
echo "\n5. Export des métriques...\n";
file_put_contents(__DIR__ . '/performance_report.json', PerformanceMonitor::exportMetrics('json'));
file_put_contents(__DIR__ . '/performance_report.log', PerformanceMonitor::exportMetrics('log'));

echo "   Rapport JSON: ✅ sauvegardé\n";
echo "   Rapport LOG: ✅ sauvegardé\n";

echo "\n🎉 Test de performance terminé!\n";

if ($thresholds['overall_status'] === 'excellent' || $thresholds['overall_status'] === 'good') {
    echo "✅ Objectif de performance atteint: Rendus < 500ms\n";
} else {
    echo "⚠️  Performance à optimiser davantage\n";
}
?>