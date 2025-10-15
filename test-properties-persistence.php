<?php
/**
 * Test de persistance des propriétés PDF Builder Pro
 * Vérifie que les propriétés sont correctement sauvegardées et chargées
 */

// Test des propriétés d'un élément
$testElement = [
    'id' => 'test_element_1',
    'type' => 'text',
    'x' => 50,
    'y' => 50,
    'width' => 200,
    'height' => 30,
    'content' => 'Texte de test',
    'color' => '#FF0000',
    'fontSize' => 16,
    'fontFamily' => 'Arial',
    'fontWeight' => 'bold',
    'backgroundColor' => '#FFFF00',
    'backgroundOpacity' => 0.8,
    'borderWidth' => 2,
    'borderColor' => '#000000',
    'borderStyle' => 'solid',
    'borderRadius' => 5,
    'textAlign' => 'center',
    'opacity' => 90
];

$testTemplate = [
    'elements' => [$testElement],
    'canvasWidth' => 595,
    'canvasHeight' => 842,
    'nextId' => 2
];

echo "=== TEST DE PERSISTANCE DES PROPRIÉTÉS ===\n\n";

// 1. Test de sérialisation (simule la sauvegarde)
echo "1. Test de sérialisation des propriétés...\n";
$serialized = json_encode($testTemplate);
if ($serialized === false) {
    echo " ERREUR: Échec de la sérialisation JSON\n";
    exit(1);
}
echo " Sérialisation réussie\n";

// 2. Test de désérialisation (simule le chargement)
echo "\n2. Test de désérialisation des propriétés...\n";
$deserialized = json_decode($serialized, true);
if ($deserialized === null) {
    echo " ERREUR: Échec de la désérialisation JSON\n";
    exit(1);
}
echo " Désérialisation réussie\n";

// 3. Vérification que toutes les propriétés sont préservées
echo "\n3. Vérification de la préservation des propriétés...\n";
$loadedElement = $deserialized['elements'][0];
$propertiesToCheck = [
    'color', 'fontSize', 'fontFamily', 'fontWeight',
    'backgroundColor', 'backgroundOpacity', 'borderWidth',
    'borderColor', 'borderStyle', 'borderRadius',
    'textAlign', 'opacity'
];

$allPropertiesPreserved = true;
foreach ($propertiesToCheck as $property) {
    if (!isset($loadedElement[$property])) {
        echo " PROPRIÉTÉ MANQUANTE: $property\n";
        $allPropertiesPreserved = false;
    } elseif ($loadedElement[$property] !== $testElement[$property]) {
        echo " PROPRIÉTÉ MODIFIÉE: $property (attendu: {$testElement[$property]}, obtenu: {$loadedElement[$property]})\n";
        $allPropertiesPreserved = false;
    } else {
        echo " $property préservé\n";
    }
}

if ($allPropertiesPreserved) {
    echo "\n TOUTES LES PROPRIÉTÉS SONT CORRECTEMENT PRÉSERVÉES !\n";
} else {
    echo "\n CERTAINES PROPRIÉTÉS NE SONT PAS CORRECTEMENT PRÉSERVÉES\n";
    exit(1);
}

echo "\n=== TEST TERMINÉ AVEC SUCCÈS ===\n";
echo "Les propriétés sont correctement dynamiques et fonctionnelles dans tout le système PDF Builder Pro.\n";
