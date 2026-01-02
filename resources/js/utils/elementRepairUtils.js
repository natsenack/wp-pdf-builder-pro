/**
 * Utilitaires de réparation automatique des propriétés d'éléments
 */

/**
 * Répare automatiquement les propriétés des éléments product_table
 */
export const repairProductTableProperties = (elements) => {
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

  return elements.map(element => {
    if (element.type !== 'product_table') return element;

    const repairedElement = { ...element };

    // Ajouter les propriétés manquantes
    Object.keys(defaultProperties).forEach(prop => {
      if (!(prop in repairedElement)) {
        repairedElement[prop] = defaultProperties[prop];
      }
    });

    // Corriger les propriétés invalides
    if (repairedElement.columns && typeof repairedElement.columns !== 'string') {
      repairedElement.columns = defaultProperties.columns;
    }

    if (repairedElement.tableStyle && typeof repairedElement.tableStyle !== 'string') {
      repairedElement.tableStyle = defaultProperties.tableStyle;
    }

    // Validation des couleurs
    const colorProperties = ['evenRowBg', 'evenRowTextColor', 'oddRowBg', 'oddRowTextColor'];
    colorProperties.forEach(prop => {
      if (repairedElement[prop] && !/^#[0-9A-Fa-f]{6}$/.test(repairedElement[prop])) {
        repairedElement[prop] = defaultProperties[prop];
      }
    });

    return repairedElement;
  });
};