// Test script pour diagnostiquer les problèmes JSON
// Simule la fonction saveTemplate pour identifier la corruption des données

// Données de test qui simulent un template avec des éléments
const testElements = [
  {
    id: 'element1',
    type: 'text',
    x: 100,
    y: 100,
    width: 200,
    height: 50,
    text: 'Test Element 1',
    fontSize: 14,
    fontFamily: 'Arial'
  },
  {
    id: 'element2',
    type: 'rectangle',
    x: 50,
    y: 200,
    width: 150,
    height: 100,
    fillColor: '#FF0000',
    strokeColor: '#000000'
  }
];

const testTemplateData = {
  elements: testElements,
  canvasWidth: 595,
  canvasHeight: 842,
  version: '1.0'
};

console.log('🔍 PDF Builder - Données de test:', testTemplateData);
console.log('🔍 PDF Builder - Nombre d\'éléments:', testElements.length);

// Test de validation JSON
try {
  const jsonString = JSON.stringify(testTemplateData);
  console.log('🔍 PDF Builder - JSON stringifié, longueur:', jsonString.length);

  // Tester le parsing pour valider
  const testParse = JSON.parse(jsonString);
  console.log('🔍 PDF Builder - JSON validé côté client');
  console.log('✅ Test réussi - Les données de base sont sérialisables');
} catch (jsonError) {
  console.error('❌ PDF Builder - ERREUR JSON côté client:', jsonError);
  console.error('🔍 Détails de l\'erreur:', jsonError.message);
}

// Test avec des propriétés potentiellement problématiques
console.log('\n🔍 Test avec propriétés potentiellement problématiques...');

const problematicElements = [
  {
    id: 'element1',
    type: 'text',
    x: 100,
    y: 100,
    width: 200,
    height: 50,
    text: 'Test Element 1',
    fontSize: 14,
    fontFamily: 'Arial',
    // Propriétés potentiellement problématiques
    domElement: { tagName: 'DIV', innerHTML: 'mock' }, // Objet non sérialisable
    eventListeners: [() => console.log('test')], // Fonctions non sérialisables
    circularRef: null
  }
];

problematicElements[0].circularRef = problematicElements[0]; // Référence circulaire

const problematicTemplateData = {
  elements: problematicElements,
  canvasWidth: 595,
  canvasHeight: 842,
  version: '1.0'
};

try {
  const jsonString = JSON.stringify(problematicTemplateData);
  console.log('🔍 PDF Builder - JSON avec propriétés problématiques stringifié');
} catch (jsonError) {
  console.error('❌ PDF Builder - ERREUR JSON avec propriétés problématiques:', jsonError);
  console.error('🔍 Type d\'erreur:', jsonError.name);
  console.error('🔍 Message:', jsonError.message);
  console.log('✅ Test réussi - Détection des propriétés non-sérialisables');
}