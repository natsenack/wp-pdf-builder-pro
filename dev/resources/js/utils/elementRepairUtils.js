/**
 * Utilitaires de réparation automatique des propriétés d'éléments
 */

/**
 * Répare automatiquement les propriétés des éléments product_table
 */
export const repairProductTableProperties = (elements) => {
  const defaultProperties = {
    // Fonctionnalités de base
    showHeaders: true,
    showBorders: true,
    showAlternatingRows: true,
    showSku: true,
    showDescription: true,
    showQuantity: true,
    
    // Style et apparence
    fontSize: 11,
    currency: '€',
    tableStyle: 'default',
    
    // Alignements
    textAlign: 'left',
    verticalAlign: 'top',
    
    // Couleurs
    backgroundColor: '#ffffff',
    headerBackgroundColor: '#f9fafb',
    headerTextColor: '#111827',
    alternateRowColor: '#f9fafb',
    borderColor: '#e5e7eb',
    textColor: '#374151',
    
    // Positionnement
    x: 0,
    y: 0,
    width: 500,
    height: 200
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

    // Validation des booléens
    const booleanProps = ['showHeaders', 'showBorders', 'showAlternatingRows', 'showSku', 'showDescription', 'showQuantity'];
    booleanProps.forEach(prop => {
      if (typeof repairedElement[prop] !== 'boolean') {
        repairedElement[prop] = defaultProperties[prop];
      }
    });

    // Validation des nombres
    const numberProps = ['fontSize', 'x', 'y', 'width', 'height'];
    numberProps.forEach(prop => {
      if (typeof repairedElement[prop] !== 'number') {
        repairedElement[prop] = defaultProperties[prop];
      }
    });

    // Validation des alignements
    const validHorizontalAligns = ['left', 'center', 'right'];
    if (!validHorizontalAligns.includes(repairedElement.textAlign)) {
      repairedElement.textAlign = defaultProperties.textAlign;
    }

    const validVerticalAligns = ['top', 'middle', 'bottom'];
    if (!validVerticalAligns.includes(repairedElement.verticalAlign)) {
      repairedElement.verticalAlign = defaultProperties.verticalAlign;
    }

    // Validation des couleurs (format hexadécimal)
    const colorProperties = ['backgroundColor', 'headerBackgroundColor', 'alternateRowColor', 'borderColor', 'headerTextColor', 'textColor'];
    colorProperties.forEach(prop => {
      if (repairedElement[prop] && !/^#[0-9A-Fa-f]{6}$/.test(repairedElement[prop])) {
        repairedElement[prop] = defaultProperties[prop];
      }
    });

    // Validation de la devise
    if (!repairedElement.currency || typeof repairedElement.currency !== 'string') {
      repairedElement.currency = defaultProperties.currency;
    }

    return repairedElement;
  });
};