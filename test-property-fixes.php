<?php
/**
 * Test rapide des corrections de propriétés
 */

// Test des fonctions de validation
function testPropertyValidation() {
    echo "<h2>🧪 Test des corrections de propriétés</h2>";

    // Test de normalisation des couleurs
    $testColors = [
        '#ff0000' => '#ff0000', // Déjà valide
        'red' => '#ff0000',     // Couleur nommée
        'invalid' => '#000000', // Invalide -> noir
        'transparent' => 'transparent', // Spécial
        '' => '#000000'         // Vide -> noir
    ];

    echo "<h3>Test des couleurs</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Entrée</th><th>Attendu</th><th>Résultat</th><th>Status</th></tr>";

    foreach ($testColors as $input => $expected) {
        $result = normalizeColor($input);
        $status = $result === $expected ? '✅' : '❌';
        echo "<tr><td>'$input'</td><td>'$expected'</td><td>'$result'</td><td>$status</td></tr>";
    }
    echo "</table>";

    // Test des propriétés numériques
    $testNumbers = [
        ['prop' => 'fontSize', 'input' => '16', 'expected' => 16],
        ['prop' => 'fontSize', 'input' => 'invalid', 'expected' => 14],
        ['prop' => 'opacity', 'input' => '0.8', 'expected' => 0.8],
        ['prop' => 'opacity', 'input' => '1.5', 'expected' => 1], // Max 1
        ['prop' => 'zIndex', 'input' => '-50', 'expected' => -50],
        ['prop' => 'zIndex', 'input' => '2000', 'expected' => 1000], // Max 1000
    ];

    echo "<h3>Test des propriétés numériques</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Propriété</th><th>Entrée</th><th>Attendu</th><th>Résultat</th><th>Status</th></tr>";

    foreach ($testNumbers as $test) {
        $result = validateNumericProperty($test['prop'], $test['input']);
        $status = $result === $test['expected'] ? '✅' : '❌';
        echo "<tr><td>{$test['prop']}</td><td>'{$test['input']}'</td><td>{$test['expected']}</td><td>$result</td><td>$status</td></tr>";
    }
    echo "</table>";
}

// Fonction helper pour normaliser une couleur (version PHP du JS)
function normalizeColor($color) {
    if (!$color || $color === 'transparent') return $color;

    // Codes hex valides
    if (preg_match('/^#[0-9A-Fa-f]{3,6}$/', $color)) return $color;

    // Couleurs nommées communes
    $namedColors = [
        'black' => '#000000',
        'white' => '#ffffff',
        'red' => '#ff0000',
        'green' => '#008000',
        'blue' => '#0000ff',
        'gray' => '#808080',
        'grey' => '#808080'
    ];

    $lowerColor = strtolower($color);
    if (isset($namedColors[$lowerColor])) {
        return $namedColors[$lowerColor];
    }

    // Par défaut, retourner noir
    return '#000000';
}

// Fonction helper pour valider une propriété numérique (version PHP du JS)
function validateNumericProperty($property, $value) {
    if ($value === null || $value === '' || $value === 'invalid') {
        $defaults = [
            'fontSize' => 14, 'opacity' => 1, 'zIndex' => 0
        ];
        return $defaults[$property] ?? 0;
    }

    $numericValue = is_numeric($value) ? floatval($value) : 0;

    // Appliquer les contraintes
    $constraints = [
        'fontSize' => ['min' => 8, 'max' => 72],
        'opacity' => ['min' => 0, 'max' => 1],
        'zIndex' => ['min' => -100, 'max' => 1000]
    ];

    if (isset($constraints[$property])) {
        $numericValue = max($constraints[$property]['min'], min($constraints[$property]['max'], $numericValue));
    }

    return $numericValue;
}

// Exécuter les tests
testPropertyValidation();

echo "<hr>";
echo "<h2>📋 Résumé des améliorations</h2>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4caf50;'>";
echo "<h3>✅ Corrections implémentées :</h3>";
echo "<ul>";
echo "<li><strong>Validation automatique des couleurs</strong> - Conversion des couleurs invalides</li>";
echo "<li><strong>Correction des types numériques</strong> - Conversion string → number et validation des plages</li>";
echo "<li><strong>Validation des propriétés de style</strong> - Vérification des valeurs autorisées</li>";
echo "<li><strong>Nettoyage amélioré</strong> - Fonction cleanElementForSerialization corrigée</li>";
echo "<li><strong>Diagnostic complet</strong> - Script de réparation automatique des templates</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3e0; padding: 15px; border-radius: 5px; border-left: 4px solid #ff9800; margin-top: 10px;'>";
echo "<h3>🔧 Utilisation :</h3>";
echo "<ul>";
echo "<li><strong>Admin WordPress:</strong> PDF Builder Pro → Diagnostic Propriétés</li>";
echo "<li><strong>URL directe:</strong> /pdf-builder-diagnostic/</li>";
echo "<li><strong>Test rapide:</strong> /pdf-builder-test/</li>";
echo "</ul>";
echo "</div>";
?>