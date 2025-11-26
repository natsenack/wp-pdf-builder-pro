<?php
/**
 * Test rapide d'injection des variables SampleDataProvider
 */

// Charger les classes nécessaires
require_once 'interfaces/DataProviderInterface.php';
require_once 'data/SampleDataProvider.php';
require_once 'generators/BaseGenerator.php';

echo "<h1>🧪 Test Injection Variables SampleDataProvider</h1>";

// Créer le DataProvider
$dataProvider = new PDF_Builder\Data\SampleDataProvider('canvas');

echo "<h2>📊 Variables disponibles dans SampleDataProvider:</h2>";
echo "<ul>";
$allVars = $dataProvider->getAllVariables();
foreach ($allVars as $key => $value) {
    echo "<li><code>{{$key}}</code> = <strong>{$value}</strong></li>";
}
echo "</ul>";

// Test d'injection simple
echo "<h2>🔄 Test d'injection dans un texte:</h2>";

$templateText = "Bonjour {{customer_name}},\n\nVotre commande {{order_number}} du {{order_date}} est {{order_status}}.\n\nTotal: {{order_total}}\nEmail: {{customer_email}}\n\nCordialement,\n{{company_name}}";

echo "<h3>Texte original:</h3>";
echo "<pre>" . htmlspecialchars($templateText) . "</pre>";

// Simuler l'injection (comme dans BaseGenerator)
function injectVariables($text, $dataProvider) {
    preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
    foreach ($matches[1] as $variable) {
        $value = $dataProvider->getVariableValue(trim($variable));
        $text = str_replace("{{{$variable}}}", $value, $text);
    }
    return $text;
}

$injectedText = injectVariables($templateText, $dataProvider);

echo "<h3>Texte injecté:</h3>";
echo "<pre>" . htmlspecialchars($injectedText) . "</pre>";

echo "<h2>✅ Test terminé</h2>";
echo "<p>Si vous voyez les variables remplacées par des valeurs, l'injection fonctionne !</p>";
?>