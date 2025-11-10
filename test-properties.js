// Test simple pour vérifier que les propriétés fonctionnent
// À exécuter dans la console du navigateur

console.log('🧪 [TEST] Début du test des propriétés');

// Fonction pour simuler un changement de propriété
function testPropertyUpdate() {
  // Trouver le premier élément
  const elements = window.pdfBuilderState?.elements;
  if (!elements || elements.length === 0) {
    console.error('❌ [TEST] Aucun élément trouvé');
    return;
  }

  const element = elements[0];
  console.log('📋 [TEST] Élément testé:', {id: element.id, x: element.x, y: element.y, width: element.width, height: element.height});

  // Simuler un changement de position X
  const newX = element.x + 10;
  console.log(`🔧 [TEST] Changement x: ${element.x} → ${newX}`);

  // Appeler updateElement (si disponible)
  if (window.pdfBuilderUpdateElement) {
    window.pdfBuilderUpdateElement(element.id, { x: newX });
    console.log('✅ [TEST] updateElement appelé');

    // Vérifier après un délai
    setTimeout(() => {
      const updatedElements = window.pdfBuilderState?.elements;
      const updatedElement = updatedElements?.find(el => el.id === element.id);
      if (updatedElement) {
        console.log('📊 [TEST] Élément après update:', {id: updatedElement.id, x: updatedElement.x, y: updatedElement.y});
        if (updatedElement.x === newX) {
          console.log('✅ [TEST] Propriété mise à jour avec succès!');
        } else {
          console.error('❌ [TEST] Propriété NON mise à jour:', updatedElement.x, 'vs attendu:', newX);
        }
      } else {
        console.error('❌ [TEST] Élément non trouvé après update');
      }
    }, 100);
  } else {
    console.error('❌ [TEST] updateElement non disponible');
  }
}

// Exposer la fonction de test
window.testPropertyUpdate = testPropertyUpdate;

console.log('🧪 [TEST] Fonction testPropertyUpdate() exposée. Exécutez-la pour tester.');