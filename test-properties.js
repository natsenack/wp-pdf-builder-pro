// Test simple pour vérifier que les propriétés fonctionnent
// À exécuter dans la console du navigateur

// Fonction pour simuler un changement de propriété
function testPropertyUpdate() {
  // Trouver le premier élément
  const elements = window.pdfBuilderState?.elements;
  if (!elements || elements.length === 0) {
    return;
  }

  const element = elements[0];

  // Simuler un changement de position X
  const newX = element.x + 10;

  // Appeler updateElement (si disponible)
  if (window.pdfBuilderUpdateElement) {
    window.pdfBuilderUpdateElement(element.id, { x: newX });

    // Vérifier après un délai
    setTimeout(() => {
      const updatedElements = window.pdfBuilderState?.elements;
      const updatedElement = updatedElements?.find(el => el.id === element.id);
      if (updatedElement) {
        if (updatedElement.x === newX) {
        } else {
        }
      } else {
      }
    }, 100);
  } else {
  }
}

// Exposer la fonction de test
window.testPropertyUpdate = testPropertyUpdate;
