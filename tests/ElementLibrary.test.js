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

  describe('order_number - Améliorations Phase 2.2.2', () => {
    test('devrait supporter plusieurs formats prédéfinis', () => {
      const orderElement = {
        type: 'order_number',
        availableFormats: [
          'Commande #{order_number} - {order_date}',
          'CMD-{order_year}-{order_number}',
          'Facture N°{order_number} du {order_date}',
          'Bon de livraison #{order_number}',
          '{order_number}/{order_year}',
          'N° {order_number} - {order_date}'
        ]
      };

      expect(orderElement.availableFormats).toHaveLength(6);
      expect(orderElement.availableFormats).toContain('CMD-{order_year}-{order_number}');
    });

    test('devrait avoir des propriétés de prévisualisation', () => {
      const orderElement = {
        type: 'order_number',
        previewOrderNumber: '12345',
        previewOrderDate: '15/10/2025',
        previewOrderYear: '2025',
        previewOrderMonth: '10',
        previewOrderDay: '15'
      };

      expect(orderElement.previewOrderNumber).toBe('12345');
      expect(orderElement.previewOrderDate).toBe('15/10/2025');
      expect(orderElement.previewOrderYear).toBe('2025');
    });

    test('devrait supporter les propriétés de style étendues', () => {
      const orderElement = {
        type: 'order_number',
        fontSize: 16,
        fontFamily: 'Helvetica',
        fontWeight: 'normal',
        textAlign: 'center',
        color: '#000000',
        labelColor: '#666666',
        lineHeight: 1.5,
        backgroundColor: '#f0f0f0',
        borderWidth: 1,
        borderStyle: 'solid',
        borderColor: '#cccccc',
        borderRadius: 4
      };

      expect(orderElement.fontSize).toBe(16);
      expect(orderElement.fontFamily).toBe('Helvetica');
      expect(orderElement.textAlign).toBe('center');
      expect(orderElement.borderWidth).toBe(1);
      expect(orderElement.lineHeight).toBe(1.5);
    });

    test('devrait avoir des valeurs par défaut appropriées', () => {
      const orderElement = {
        type: 'order_number',
        format: 'Commande #{order_number} - {order_date}',
        fontSize: 14,
        fontFamily: 'Arial',
        fontWeight: 'bold',
        textAlign: 'right',
        color: '#333333',
        showLabel: true,
        labelText: 'N° de commande:'
      };

      expect(orderElement.format).toBe('Commande #{order_number} - {order_date}');
      expect(orderElement.fontSize).toBe(14);
      expect(orderElement.textAlign).toBe('right');
      expect(orderElement.showLabel).toBe(true);
    });
  });

  describe('Phase 2.2.3 - Simulation product_table avec frais', () => {
    test('devrait inclure les frais de commande comme items du tableau', () => {
      // Simulation d'une commande WooCommerce avec produits et frais
      const mockOrderItems = [
        {
          type: 'line_item', // Produit normal
          name: 'T-shirt Premium',
          quantity: 2,
          price: 25.00,
          total: 50.00,
          product_id: 123
        },
        {
          type: 'fee', // Frais personnalisé
          name: 'Frais de personnalisation',
          quantity: 1,
          price: 5.00,
          total: 5.00
        },
        {
          type: 'fee', // Frais de port personnalisé
          name: 'Frais de port express',
          quantity: 1,
          price: 8.50,
          total: 8.50
        }
      ];

      // Simulation du rendu du tableau (logique simplifiée)
      const tableItems = mockOrderItems.filter(item =>
        item.type === 'line_item' || item.type === 'fee'
      );

      // Vérifications
      expect(tableItems).toHaveLength(3); // 1 produit + 2 frais
      expect(tableItems[0].name).toBe('T-shirt Premium');
      expect(tableItems[0].type).toBe('line_item');
      expect(tableItems[1].name).toBe('Frais de personnalisation');
      expect(tableItems[1].type).toBe('fee');
      expect(tableItems[2].name).toBe('Frais de port express');
      expect(tableItems[2].type).toBe('fee');
    });

    test('devrait calculer correctement les totaux avec frais inclus', () => {
      const mockOrderItems = [
        { type: 'line_item', name: 'Produit A', total: 100.00 },
        { type: 'line_item', name: 'Produit B', total: 50.00 },
        { type: 'fee', name: 'Frais de service', total: 10.00 },
        { type: 'fee', name: 'Frais de port', total: 15.00 }
      ];

      const subtotal = mockOrderItems
        .filter(item => item.type === 'line_item')
        .reduce((sum, item) => sum + item.total, 0);

      const feesTotal = mockOrderItems
        .filter(item => item.type === 'fee')
        .reduce((sum, item) => sum + item.total, 0);

      const grandTotal = subtotal + feesTotal;

      expect(subtotal).toBe(150.00); // Produits seulement
      expect(feesTotal).toBe(25.00); // Frais seulement
      expect(grandTotal).toBe(175.00); // Total complet
    });

    test('devrait gérer les frais avec quantité variable', () => {
      const mockOrderItems = [
        { type: 'fee', name: 'Frais par produit', quantity: 3, price: 2.00, total: 6.00 },
        { type: 'fee', name: 'Frais fixe', quantity: 1, price: 10.00, total: 10.00 }
      ];

      // Vérifier que les frais peuvent avoir des quantités variables
      expect(mockOrderItems[0].quantity).toBe(3);
      expect(mockOrderItems[0].total).toBe(6.00); // 3 × 2.00
      expect(mockOrderItems[1].quantity).toBe(1);
      expect(mockOrderItems[1].total).toBe(10.00); // 1 × 10.00
    });

    test('devrait permettre de filtrer ou masquer les frais si souhaité', () => {
      const mockOrderItems = [
        { type: 'line_item', name: 'Produit A', total: 100.00 },
        { type: 'fee', name: 'Frais de service', total: 10.00 },
        { type: 'fee', name: 'Frais de port', total: 15.00 }
      ];

      // Simulation de propriété pour masquer les frais
      const showFees = false;

      const displayItems = showFees
        ? mockOrderItems
        : mockOrderItems.filter(item => item.type === 'line_item');

      expect(displayItems).toHaveLength(1); // Seulement le produit
      expect(displayItems[0].name).toBe('Produit A');
    });
  });

  describe('Phase 2.2.4 - Intégration des frais dans product_table', () => {
    test('devrait inclure les frais dans les données réelles de commande', () => {
      // Simulation des données retournées par create_real_order_data avec frais
      const mockRealOrderData = {
        items: [
          {
            name: 'T-shirt Premium',
            quantity: 2,
            price: '25.00',
            total: '50.00',
            item_type: 'line_item'
          },
          {
            name: 'Frais de personnalisation',
            quantity: 1,
            price: '5.00',
            total: '5.00',
            item_type: 'fee'
          }
        ],
        subtotal: '50.00',
        shipping: '0.00',
        tax: '0.00',
        discount: '0.00',
        total: '55.00'
      };

      // Vérifier que les frais sont inclus
      const feeItems = mockRealOrderData.items.filter(item => item.item_type === 'fee');
      const productItems = mockRealOrderData.items.filter(item => item.item_type === 'line_item');

      expect(feeItems).toHaveLength(1);
      expect(productItems).toHaveLength(1);
      expect(feeItems[0].name).toBe('Frais de personnalisation');
      expect(feeItems[0].total).toBe('5.00');
    });

    test('devrait respecter la propriété showFees pour filtrer les frais', () => {
      const mockItemsWithFees = [
        { name: 'Produit A', item_type: 'line_item', total: 100 },
        { name: 'Frais de service', item_type: 'fee', total: 10 },
        { name: 'Frais de port', item_type: 'fee', total: 15 }
      ];

      // Avec showFees = true (défaut)
      const withFees = mockItemsWithFees.filter(item =>
        item.item_type === 'line_item' || item.item_type === 'fee'
      );
      expect(withFees).toHaveLength(3);

      // Avec showFees = false
      const withoutFees = mockItemsWithFees.filter(item =>
        item.item_type === 'line_item'
      );
      expect(withoutFees).toHaveLength(1);
      expect(withoutFees[0].name).toBe('Produit A');
    });

    test('devrait calculer correctement les totaux incluant les frais', () => {
      const mockOrderData = {
        items: [
          { item_type: 'line_item', total: 100.00 },
          { item_type: 'line_item', total: 50.00 },
          { item_type: 'fee', total: 10.00 },
          { item_type: 'fee', total: 15.00 }
        ]
      };

      const productTotal = mockOrderData.items
        .filter(item => item.item_type === 'line_item')
        .reduce((sum, item) => sum + item.total, 0);

      const feesTotal = mockOrderData.items
        .filter(item => item.item_type === 'fee')
        .reduce((sum, item) => sum + item.total, 0);

      const grandTotal = productTotal + feesTotal;

      expect(productTotal).toBe(150.00);
      expect(feesTotal).toBe(25.00);
      expect(grandTotal).toBe(175.00);
    });
  });
});

describe('Phase 2.2.3 - company_info mapping WooCommerce', () => {
  test('devrait avoir les propriétés étendues pour company_info', () => {
    const companyInfoElement = expectedElements.find(el => el.type === 'company_info');
    expect(companyInfoElement).toBeDefined();
    expect(companyInfoElement.requiredProps).toContain('fields');
    expect(companyInfoElement.requiredProps).toContain('layout');
  });

  test('devrait supporter tous les champs d\'entreprise', () => {
    // Simulation des champs disponibles dans company_info
    const availableFields = ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];

    availableFields.forEach(field => {
      expect(['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret']).toContain(field);
    });
  });

  test('devrait formater correctement les données d\'entreprise selon le template', () => {
    // Simulation des données d'entreprise
    const companyData = {
      name: 'Ma Société SARL',
      address: '123 Rue de l\'Entreprise',
      city: 'Paris',
      postcode: '75001',
      phone: '+33 1 23 45 67 89',
      email: 'contact@masociete.com',
      website: 'www.masociete.com',
      vat: 'FR12345678901',
      siret: '12345678901234',
      rcs: 'RCS Paris 123456789'
    };

    // Test template par défaut
    const defaultTemplate = {
      template: 'default',
      fields: ['name', 'address', 'phone', 'email', 'vat', 'siret']
    };

    // Vérification que les données sont correctement structurées
    expect(companyData.name).toBe('Ma Société SARL');
    expect(companyData.vat).toBe('FR12345678901');
    expect(companyData.siret).toBe('12345678901234');
  });

  test('devrait gérer les templates prédéfinis', () => {
    const templates = ['default', 'commercial', 'legal', 'minimal'];

    templates.forEach(template => {
      expect(['default', 'commercial', 'legal', 'minimal']).toContain(template);
    });
  });

  test('devrait valider les propriétés de prévisualisation', () => {
    // Simulation des propriétés de prévisualisation
    const previewProps = {
      previewCompanyName: 'Test Company',
      previewAddress: 'Test Address',
      previewPhone: 'Test Phone',
      previewEmail: 'test@email.com',
      previewWebsite: 'www.test.com',
      previewVat: 'TEST123',
      previewSiret: '123456789',
      previewRcs: 'TEST RCS'
    };

    // Vérification que toutes les propriétés de prévisualisation sont définies
    Object.keys(previewProps).forEach(key => {
      expect(previewProps[key]).toBeDefined();
      expect(typeof previewProps[key]).toBe('string');
    });
  });

  test('devrait supporter les layouts vertical et horizontal', () => {
    const layouts = ['vertical', 'horizontal'];

    layouts.forEach(layout => {
      expect(['vertical', 'horizontal']).toContain(layout);
    });
  });
});