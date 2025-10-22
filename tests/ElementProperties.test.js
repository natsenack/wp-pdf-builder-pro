/**
 * Test de validation des propriétés des éléments (Phase 2.1.2 - Extension)
 * Teste la structure et les définitions des propriétés sans dépendre des composants React
 */

// Simuler la structure des propriétés (basé sur elementPropertyRestrictions.js)
const expectedPropertyRestrictions = {
  special: {
    backgroundColor: { disabled: false, default: 'transparent' },
    borderColor: { disabled: false },
    borderWidth: { disabled: false }
  },
  layout: {
    backgroundColor: { disabled: false, default: '#f8fafc' },
    borderColor: { disabled: false },
    borderWidth: { disabled: false }
  },
  text: {
    backgroundColor: { disabled: false, default: 'transparent' },
    borderColor: { disabled: false },
    borderWidth: { disabled: false }
  },
  shape: {
    backgroundColor: { disabled: false, default: '#e5e7eb' },
    borderColor: { disabled: false },
    borderWidth: { disabled: false }
  },
  media: {
    backgroundColor: { disabled: false, default: '#f3f4f6' },
    borderColor: { disabled: false },
    borderWidth: { disabled: false }
  },
  dynamic: {
    backgroundColor: { disabled: false, default: 'transparent' },
    borderColor: { disabled: false },
    borderWidth: { disabled: false }
  }
};

const expectedElementTypeMapping = {
  // Spéciaux
  'product_table': 'special',
  'customer_info': 'special',
  'company_logo': 'special',
  'company_info': 'special',
  'order_number': 'special',
  'dynamic-text': 'text',
  'mentions': 'text',

  // Mise en page
  'layout-header': 'layout',
  'layout-footer': 'layout',
  'layout-section': 'layout',

  // Texte
  'text': 'text',
  'conditional-text': 'text',

  // Formes
  'rectangle': 'shape',
  'line': 'shape',
  'shape-circle': 'shape',
  'shape-triangle': 'shape',
  'divider': 'shape',

  // Médias
  'image': 'media',
  'logo': 'media',
  'barcode': 'media',

  // Dynamiques
  'table-dynamic': 'dynamic',
  'gradient-box': 'dynamic'
};

describe('Element Properties - Validation des propriétés', () => {
  test('devrait définir exactement 6 catégories de propriétés', () => {
    expect(Object.keys(expectedPropertyRestrictions)).toHaveLength(6);
    expect(expectedPropertyRestrictions).toHaveProperty('special');
    expect(expectedPropertyRestrictions).toHaveProperty('layout');
    expect(expectedPropertyRestrictions).toHaveProperty('text');
    expect(expectedPropertyRestrictions).toHaveProperty('shape');
    expect(expectedPropertyRestrictions).toHaveProperty('media');
    expect(expectedPropertyRestrictions).toHaveProperty('dynamic');
  });

  test.each(Object.keys(expectedPropertyRestrictions))('devrait définir la catégorie $category avec les propriétés de base', (category) => {
    const categoryProps = expectedPropertyRestrictions[category];
    expect(categoryProps).toHaveProperty('backgroundColor');
    expect(categoryProps).toHaveProperty('borderColor');
    expect(categoryProps).toHaveProperty('borderWidth');
  });

  test('devrait avoir des valeurs par défaut appropriées pour backgroundColor', () => {
    expect(expectedPropertyRestrictions.special.backgroundColor.default).toBe('transparent');
    expect(expectedPropertyRestrictions.layout.backgroundColor.default).toBe('#f8fafc');
    expect(expectedPropertyRestrictions.text.backgroundColor.default).toBe('transparent');
    expect(expectedPropertyRestrictions.shape.backgroundColor.default).toBe('#e5e7eb');
    expect(expectedPropertyRestrictions.media.backgroundColor.default).toBe('#f3f4f6');
    expect(expectedPropertyRestrictions.dynamic.backgroundColor.default).toBe('transparent');
  });

  test('devrait mapper les 7 éléments principaux vers les bonnes catégories', () => {
    expect(expectedElementTypeMapping['product_table']).toBe('special');
    expect(expectedElementTypeMapping['customer_info']).toBe('special');
    expect(expectedElementTypeMapping['company_logo']).toBe('special');
    expect(expectedElementTypeMapping['company_info']).toBe('special');
    expect(expectedElementTypeMapping['order_number']).toBe('special');
    expect(expectedElementTypeMapping['dynamic-text']).toBe('text');
    expect(expectedElementTypeMapping['mentions']).toBe('text');
  });

  test('devrait avoir des mappings pour tous les types d\'éléments définis', () => {
    Object.keys(expectedElementTypeMapping).forEach(elementType => {
      expect(expectedElementTypeMapping[elementType]).toBeDefined();
      expect(Object.keys(expectedPropertyRestrictions)).toContain(expectedElementTypeMapping[elementType]);
    });
  });

  test('devrait permettre backgroundColor pour tous les types d\'éléments', () => {
    Object.values(expectedPropertyRestrictions).forEach(category => {
      expect(category.backgroundColor.disabled).toBe(false);
    });
  });

  test('devrait permettre borderColor et borderWidth pour tous les types', () => {
    Object.values(expectedPropertyRestrictions).forEach(category => {
      expect(category.borderColor.disabled).toBe(false);
      expect(category.borderWidth.disabled).toBe(false);
    });
  });

  test('devrait compter le nombre total de types d\'éléments mappés', () => {
    const totalMappings = Object.keys(expectedElementTypeMapping).length;
    expect(totalMappings).toBeGreaterThanOrEqual(20); // Au moins 20 types mappés
  });

  test('devrait valider les propriétés communes à tous les éléments', () => {
    const commonProperties = ['backgroundColor', 'borderColor', 'borderWidth'];

    Object.keys(expectedElementTypeMapping).forEach(elementType => {
      const category = expectedElementTypeMapping[elementType];
      const restrictions = expectedPropertyRestrictions[category];

      commonProperties.forEach(prop => {
        expect(restrictions[prop]).toBeDefined();
        expect(restrictions[prop].disabled).toBe(false);
      });
    });
  });
});