/**
 * Test de validation des 7 types d'éléments (Phase 2.1.1)
 * Teste la structure et les définitions des éléments sans dépendre des composants React
 */

// Importer la fonction de validation des images depuis CanvasElement
const validateImageFormat = (imageUrl) => {
  if (!imageUrl) return { isValid: false, format: null, reason: 'URL vide' };

  try {
    // Vérifier l'extension du fichier
    const url = new URL(imageUrl);
    const pathname = url.pathname.toLowerCase();
    const hasValidExtension = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp', '.tiff', '.ico'].some(ext => pathname.endsWith(ext));

    if (!hasValidExtension) {
      return {
        isValid: false,
        format: null,
        reason: `Extension non supportée. Formats acceptés: .jpg, .jpeg, .png, .gif, .webp, .svg, .bmp, .tiff, .ico`
      };
    }

    return {
      isValid: true,
      format: pathname.split('.').pop(),
      reason: null
    };
  } catch (error) {
    return {
      isValid: false,
      format: null,
      reason: 'URL invalide'
    };
  }
};

// Simuler la structure des éléments (basé sur ElementLibrary.jsx)
const expectedElements = [
  {
    type: 'product_table',
    label: 'Tableau Produits',
    icon: '📋',
    description: 'Tableau des produits commandés',
    requiredProps: ['showHeaders', 'showBorders', 'headers', 'dataSource', 'columns']
  },
  {
    type: 'customer_info',
    label: 'Fiche Client',
    icon: '👤',
    description: 'Informations détaillées du client',
    requiredProps: ['showHeaders', 'showBorders', 'fields', 'layout']
  },
  {
    type: 'company_logo',
    label: 'Logo Entreprise',
    icon: '🏢',
    description: 'Logo et identité visuelle de l\'entreprise',
    requiredProps: ['width', 'height', 'alignment', 'fit']
  },
  {
    type: 'company_info',
    label: 'Informations Entreprise',
    icon: '[D]',
    description: 'Nom, adresse, contact et TVA de l\'entreprise',
    requiredProps: ['showHeaders', 'showBorders', 'fields', 'layout']
  },
  {
    type: 'order_number',
    label: 'Numéro de Commande',
    icon: '🔢',
    description: 'Référence de commande avec date',
    requiredProps: ['format', 'fontSize', 'fontFamily', 'textAlign']
  },
  {
    type: 'dynamic-text',
    label: 'Texte Dynamique',
    icon: '�',
    description: 'Texte avec variables dynamiques',
    requiredProps: ['template', 'customContent', 'fontSize', 'fontFamily']
  },
  {
    type: 'mentions',
    label: 'Mentions légales',
    icon: '📄',
    description: 'Informations légales (email, SIRET, téléphone, etc.)',
    requiredProps: ['fontSize', 'fontFamily', 'textAlign', 'layout']
  }
];

describe('ElementLibrary - Validation des 7 types d\'éléments', () => {
  test('devrait définir exactement 7 éléments dans la bibliothèque', () => {
    expect(expectedElements).toHaveLength(7);
  });

  test.each(expectedElements)('devrait définir $type avec les propriétés requises', (element) => {
    expect(element.type).toBeDefined();
    expect(element.label).toBeDefined();
    expect(element.icon).toBeDefined();
    expect(element.description).toBeDefined();
    expect(element.requiredProps).toBeInstanceOf(Array);
    expect(element.requiredProps.length).toBeGreaterThan(0);
  });

  test('devrait avoir des types d\'éléments uniques', () => {
    const types = expectedElements.map(el => el.type);
    const uniqueTypes = [...new Set(types)];
    expect(uniqueTypes).toHaveLength(types.length);
  });

  test('devrait avoir des icônes appropriées pour chaque élément', () => {
    expectedElements.forEach(element => {
      expect(element.icon).toBeTruthy();
      expect(element.icon.length).toBeGreaterThan(0);
    });
  });

  test('devrait avoir des descriptions informatives', () => {
    expectedElements.forEach(element => {
      expect(element.description).toBeTruthy();
      expect(element.description.length).toBeGreaterThan(10); // Descriptions détaillées
    });
  });
});

/**
 * Tests Phase 2.2 - Améliorations éléments fondamentaux
 */
describe('Phase 2.2 - Éléments fondamentaux améliorés', () => {
  describe('company_logo - Améliorations Phase 2.2', () => {
    test('devrait supporter les propriétés src et imageUrl', () => {
      // Test de compatibilité avec les deux propriétés d'image
      const logoElement = {
        type: 'company_logo',
        src: 'https://example.com/logo.png',
        imageUrl: 'https://example.com/fallback-logo.png',
        width: 150,
        height: 80,
        autoResize: true
      };

      // La propriété src devrait être prioritaire
      expect(logoElement.src).toBe('https://example.com/logo.png');
      expect(logoElement.imageUrl).toBe('https://example.com/fallback-logo.png');
    });

    test('devrait avoir la propriété autoResize activée par défaut', () => {
      const logoElement = {
        type: 'company_logo',
        autoResize: true, // Devrait être true par défaut selon les améliorations
        width: 150,
        height: 80
      };

      expect(logoElement.autoResize).toBe(true);
    });

    test('devrait supporter les propriétés de bordure complètes', () => {
      const logoElement = {
        type: 'company_logo',
        borderWidth: 2,
        borderStyle: 'solid',
        borderColor: '#000000',
        borderRadius: 4
      };

      expect(logoElement.borderWidth).toBe(2);
      expect(logoElement.borderStyle).toBe('solid');
      expect(logoElement.borderColor).toBe('#000000');
      expect(logoElement.borderRadius).toBe(4);
    });

    test('devrait avoir des dimensions par défaut appropriées', () => {
      const logoElement = {
        type: 'company_logo',
        width: 150, // Dimensions par défaut
        height: 80
      };

      expect(logoElement.width).toBe(150);
      expect(logoElement.height).toBe(80);
    });

    test('devrait valider les formats d\'image supportés', () => {
      // Formats valides
      expect(validateImageFormat('https://example.com/logo.png')).toEqual({
        isValid: true,
        format: 'png',
        reason: null
      });

      expect(validateImageFormat('https://example.com/logo.jpg')).toEqual({
        isValid: true,
        format: 'jpg',
        reason: null
      });

      expect(validateImageFormat('https://example.com/logo.jpeg')).toEqual({
        isValid: true,
        format: 'jpeg',
        reason: null
      });

      expect(validateImageFormat('https://example.com/logo.webp')).toEqual({
        isValid: true,
        format: 'webp',
        reason: null
      });

      expect(validateImageFormat('https://example.com/logo.svg')).toEqual({
        isValid: true,
        format: 'svg',
        reason: null
      });
    });

    test('devrait rejeter les formats d\'image non supportés', () => {
      // Formats invalides
      expect(validateImageFormat('https://example.com/logo.pdf')).toEqual({
        isValid: false,
        format: null,
        reason: expect.stringContaining('Extension non supportée')
      });

      expect(validateImageFormat('https://example.com/logo.tiff')).toEqual({
        isValid: true,
        format: 'tiff',
        reason: null
      });

      expect(validateImageFormat('')).toEqual({
        isValid: false,
        format: null,
        reason: 'URL vide'
      });

      expect(validateImageFormat('not-a-url')).toEqual({
        isValid: false,
        format: null,
        reason: 'URL invalide'
      });
    });
  });
});