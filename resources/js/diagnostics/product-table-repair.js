/**
 * Fonction de réparation automatique pour les propriétés product_table
 * Corrige les éléments qui ont des propriétés manquantes ou invalides
 */

function repairProductTableProperties(elements) {
  const defaultProperties = {
    tableStyle: 'default',
    columns: 'name,price,quantity',
    showHeaders: true,
    showBorders: true,
    showSubtotal: true,
    showShipping: true,
    showTaxes: true,
    showDiscount: false,
    showTotal: true,
    evenRowBg: '#ffffff',
    evenRowTextColor: '#000000',
    oddRowBg: '#f8fafc',
    oddRowTextColor: '#000000'
  };

  console.log('=== REPAIRING PRODUCT_TABLE ELEMENTS ===');

  let repairedCount = 0;
  const repairedElements = elements.map(element => {
    if (element.type !== 'product_table') return element;

    let needsRepair = false;
    const repairedElement = { ...element };

    // Ajouter les propriétés manquantes
    Object.keys(defaultProperties).forEach(prop => {
      if (!(prop in repairedElement)) {
        repairedElement[prop] = defaultProperties[prop];
        needsRepair = true;
        console.log(`Added missing property ${prop} to element ${element.id}`);
      }
    });

    // Corriger les propriétés invalides
    if (repairedElement.columns && typeof repairedElement.columns !== 'string') {
      repairedElement.columns = defaultProperties.columns;
      needsRepair = true;
      console.log(`Fixed invalid columns property for element ${element.id}`);
    }

    if (repairedElement.tableStyle && typeof repairedElement.tableStyle !== 'string') {
      repairedElement.tableStyle = defaultProperties.tableStyle;
      needsRepair = true;
      console.log(`Fixed invalid tableStyle property for element ${element.id}`);
    }

    // Validation des couleurs
    const colorProperties = ['evenRowBg', 'evenRowTextColor', 'oddRowBg', 'oddRowTextColor'];
    colorProperties.forEach(prop => {
      if (repairedElement[prop] && !/^#[0-9A-Fa-f]{6}$/.test(repairedElement[prop])) {
        repairedElement[prop] = defaultProperties[prop];
        needsRepair = true;
        console.log(`Fixed invalid color property ${prop} for element ${element.id}`);
      }
    });

    if (needsRepair) {
      repairedCount++;
    }

    return repairedElement;
  });

  console.log(`=== REPAIR COMPLETE ===`);
  console.log(`Repaired ${repairedCount} elements`);

  return repairedElements;
}

// Exposer la fonction globalement
window.repairProductTableProperties = repairProductTableProperties;

console.log('Repair function loaded. Use: repairProductTableProperties(elements)');