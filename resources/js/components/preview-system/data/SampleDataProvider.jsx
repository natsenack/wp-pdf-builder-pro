/**
 * Fournisseur de données d'exemple pour le mode Canvas
 * Génère des données fictives réalistes pour l'aperçu
 */
export class SampleDataProvider {
  constructor() {
    this.sampleData = {
      // Données de produits d'exemple étendues
      products: [
        {
          name: 'Ordinateur Portable Pro 15"',
          sku: 'LAPTOP-PRO-15',
          quantity: 1,
          price: '999,99 €',
          regular_price: '1 199,99 €',
          sale_price: '999,99 €',
          total: '999,99 €',
          subtotal: '999,99 €',
          tax: '199,99 €',
          discount: '200,00 €',
          image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjNGY0NmU1Ii8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxBUFQ8L3RleHQ+Cjwvc3ZnPg==',
          description: 'Ordinateur portable professionnel avec écran 15" 4K, processeur Intel i7, 16GB RAM, SSD 512GB.',
          short_description: 'Ordinateur portable professionnel 15" 4K',
          categories: ['Informatique', 'Ordinateurs Portables'],
          weight: '2.1 kg',
          dimensions: '35.5 x 24.5 x 1.8 cm',
          attributes: {
            'Processeur': 'Intel Core i7-11800H',
            'RAM': '16GB DDR4',
            'Stockage': 'SSD 512GB NVMe',
            'Écran': '15.6" 4K UHD'
          },
          stock_quantity: 15,
          stock_status: 'en_stock',
          product_type: 'simple',
          is_on_sale: true,
          is_virtual: false,
          is_downloadable: false,
          meta_data: {
            '_custom_field': 'Valeur personnalisée'
          }
        },
        {
          name: 'Souris Gaming RGB Optique',
          sku: 'MOUSE-RGB-001',
          quantity: 2,
          price: '49,99 €',
          regular_price: '59,99 €',
          sale_price: '49,99 €',
          total: '99,98 €',
          subtotal: '99,98 €',
          tax: '19,99 €',
          discount: '20,00 €',
          image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjMDU5NjY5Ii8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk1PVVNFPPC90ZXh0Pgo8L3N2Zz4=',
          description: 'Souris gaming optique RGB avec capteur PixArt 3335, 16 000 DPI, switches Omron et éclairage RGB personnalisable.',
          short_description: 'Souris gaming RGB 16K DPI',
          categories: ['Informatique', 'Périphériques', 'Gaming'],
          weight: '0.085 kg',
          dimensions: '12.5 x 6.8 x 3.8 cm',
          attributes: {
            'DPI': '16 000',
            'Switches': 'Omron',
            'Éclairage': 'RGB',
            'Capteur': 'PixArt 3335'
          },
          stock_quantity: 45,
          stock_status: 'en_stock',
          product_type: 'simple',
          is_on_sale: true,
          is_virtual: false,
          is_downloadable: false,
          meta_data: {}
        },
        {
          name: 'Clavier Mécanique Gaming',
          sku: 'KEYBOARD-MECH-001',
          quantity: 1,
          price: '129,99 €',
          regular_price: '149,99 €',
          sale_price: '129,99 €',
          total: '129,99 €',
          subtotal: '129,99 €',
          tax: '25,99 €',
          discount: '20,00 €',
          image: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjZGMyNjI2Ii8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPktFWUI8L3RleHQ+Cjwvc3ZnPg==',
          description: 'Clavier mécanique gaming avec switches Cherry MX Red, éclairage RGB par touche, repose-poignets ergonomique.',
          short_description: 'Clavier mécanique RGB Cherry MX',
          categories: ['Informatique', 'Périphériques', 'Gaming'],
          weight: '0.95 kg',
          dimensions: '43.5 x 13.5 x 3.8 cm',
          attributes: {
            'Switches': 'Cherry MX Red',
            'Éclairage': 'RGB par touche',
            'Disposition': 'AZERTY',
            'Connexion': 'USB-C'
          },
          stock_quantity: 8,
          stock_status: 'en_stock',
          product_type: 'simple',
          is_on_sale: true,
          is_virtual: false,
          is_downloadable: false,
          meta_data: {
            '_warranty': '2 ans'
          }
        }
      ],

      // Données client d'exemple
      customer: {
        name: 'Marie Dubois',
        email: 'marie.dubois@email.com',
        phone: '+33 6 12 34 56 78',
        address: '15 Avenue des Champs-Élysées\n75008 Paris\nFrance',
        billing_address: '15 Avenue des Champs-Élysées\n75008 Paris\nFrance',
        shipping_address: '456 Avenue des Roses\n75016 Paris\nFrance',
        company: 'TechCorp SARL',
        vat: 'FR12345678901',
        siret: '12345678901234'
      },

      // Données entreprise d'exemple
      company: {
        name: 'Ma Société SARL',
        address: '123 Rue de la Paix\n75001 Paris\nFrance',
        phone: '+33 1 42 86 75 30',
        email: 'contact@masociete.com',
        website: 'www.masociete.com',
        vat: 'FR98765432109',
        rcs: 'Paris B 123 456 789',
        siret: '98765432109876'
      },

      // Données commande d'exemple
      order: {
        number: 'CMD-2025-001',
        date: '19/10/2025',
        due_date: '19/11/2025',
        total: '1 229,96 €',
        subtotal: '1 129,97 €',
        tax: '99,99 €',
        shipping: '15,00 €',
        discount: '15,00 €',
        status: 'Traitement en cours',
        paymentMethod: 'Carte bancaire',
        shippingMethod: 'Colissimo 48h',
        // Styles de tableau disponibles
        tableStyles: {
          default: {
            header_bg: [248, 249, 250], // #f8f9fa
            header_border: [226, 232, 240], // #e2e8f0
            row_border: [241, 245, 249], // #f1f5f9
            alt_row_bg: [250, 251, 252], // #fafbfc
            headerTextColor: '#000000',
            rowTextColor: '#000000',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          classic: {
            header_bg: [30, 41, 59], // #1e293b
            header_border: [51, 65, 85], // #334155
            row_border: [51, 65, 85], // #334155
            alt_row_bg: [255, 255, 255], // #ffffff
            headerTextColor: '#ffffff',
            rowTextColor: '#1e293b',
            border_width: 1.5,
            headerFontWeight: '700',
            headerFontSize: '11px',
            rowFontSize: '10px'
          },
          blue: {
            header_bg: [224, 242, 254], // #e0f2fe
            header_border: [14, 165, 233], // #0ea5e9
            row_border: [240, 249, 255], // #f0f9ff
            alt_row_bg: [248, 249, 252], // #f8fafc
            headerTextColor: '#0c4a6e',
            rowTextColor: '#334155',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '11px',
            rowFontSize: '10px'
          },
          minimal: {
            header_bg: [255, 255, 255], // #ffffff
            header_border: [229, 231, 235], // #e5e7eb
            row_border: [229, 231, 235], // #e5e7eb
            alt_row_bg: [255, 255, 255], // #ffffff
            headerTextColor: '#374151',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: '600',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          light: {
            header_bg: [249, 250, 251], // #f9fafb
            header_border: [209, 213, 219], // #d1d5db
            row_border: [229, 231, 235], // #e5e7eb
            alt_row_bg: [255, 255, 255], // #ffffff
            headerTextColor: '#111827',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: '500',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          emerald_forest: {
            header_bg: [209, 250, 229], // #d1fae5
            header_border: [16, 185, 129], // #10b981
            row_border: [236, 253, 245], // #ecfdf5
            alt_row_bg: [236, 253, 245], // #ecfdf5
            headerTextColor: '#065f46',
            rowTextColor: '#065f46',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '11px',
            rowFontSize: '10px'
          },
          striped: {
            header_bg: [75, 85, 99], // #4b5563
            header_border: [107, 114, 128], // #6b7280
            row_border: [229, 231, 235], // #e5e7eb
            alt_row_bg: [249, 250, 251], // #f9fafb
            headerTextColor: '#ffffff',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          bordered: {
            header_bg: [31, 41, 55], // #1f2937
            header_border: [55, 65, 81], // #374151
            row_border: [55, 65, 81], // #374151
            alt_row_bg: [255, 255, 255], // #ffffff
            headerTextColor: '#ffffff',
            rowTextColor: '#1f2937',
            border_width: 2,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          modern: {
            header_bg: [17, 24, 39], // #111827
            header_border: [75, 85, 99], // #4b5563
            row_border: [209, 213, 219], // #d1d5db
            alt_row_bg: [243, 244, 246], // #f3f4f6
            headerTextColor: '#ffffff',
            rowTextColor: '#374151',
            border_width: 1,
            headerFontWeight: '600',
            headerFontSize: '13px',
            rowFontSize: '12px'
          },
          blue_ocean: {
            header_bg: [219, 234, 254], // #dbeafe
            header_border: [59, 130, 246], // #3b82f6
            row_border: [239, 246, 255], // #eff6ff
            alt_row_bg: [239, 246, 255], // #eff6ff
            headerTextColor: '#1e40af',
            rowTextColor: '#0c4a6e',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          sunset_orange: {
            header_bg: [253, 215, 170], // #fdd7aa
            header_border: [245, 101, 101], // #f56565
            row_border: [255, 247, 237], // #fff7ed
            alt_row_bg: [255, 247, 237], // #fff7ed
            headerTextColor: '#c2410c',
            rowTextColor: '#9a3412',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          royal_purple: {
            header_bg: [233, 213, 255], // #e9d5ff
            header_border: [168, 85, 247], // #a855f7
            row_border: [250, 245, 255], // #faf5ff
            alt_row_bg: [250, 245, 255], // #faf5ff
            headerTextColor: '#7c3aed',
            rowTextColor: '#581c87',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          rose_pink: {
            header_bg: [254, 226, 243], // #fee7f3
            header_border: [244, 114, 182], // #f472b6
            row_border: [253, 242, 248], // #fdf2f8
            alt_row_bg: [253, 242, 248], // #fdf2f8
            headerTextColor: '#db2777',
            rowTextColor: '#be123c',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          teal_aqua: {
            header_bg: [204, 251, 241], // #ccfbf1
            header_border: [20, 184, 166], // #14b8a6
            row_border: [240, 253, 250], // #f0fdfa
            alt_row_bg: [236, 253, 245], // #ecfdf5
            headerTextColor: '#0d9488',
            rowTextColor: '#065f46',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          crimson_red: {
            header_bg: [254, 202, 202], // #fecaca
            header_border: [239, 68, 68], // #ef4444
            row_border: [254, 242, 242], // #fef2f2
            alt_row_bg: [254, 242, 242], // #fef2f2
            headerTextColor: '#dc2626',
            rowTextColor: '#991b1b',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          amber_gold: {
            header_bg: [254, 243, 199], // #fef3c7
            header_border: [245, 158, 11], // #f59e0b
            row_border: [254, 252, 232], // #fefce8
            alt_row_bg: [254, 252, 232], // #fefce8
            headerTextColor: '#d97706',
            rowTextColor: '#92400e',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          indigo_night: {
            header_bg: [238, 242, 255], // #eef2ff
            header_border: [99, 102, 241], // #6366f1
            row_border: [238, 242, 255], // #eef2ff
            alt_row_bg: [245, 243, 255], // #f5f3ff
            headerTextColor: '#4338ca',
            rowTextColor: '#312e81',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          slate_gray: {
            header_bg: [51, 65, 85], // #334155
            header_border: [100, 116, 139], // #64748b
            row_border: [203, 213, 225], // #cbd5e1
            alt_row_bg: [248, 250, 252], // #f8fafc
            headerTextColor: '#ffffff',
            rowTextColor: '#334155',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          coral_sunset: {
            header_bg: [194, 65, 12], // #c2410c
            header_border: [251, 146, 60], // #fb923c
            row_border: [253, 186, 116], // #fdba74
            alt_row_bg: [255, 247, 237], // #fff7ed
            headerTextColor: '#ffffff',
            rowTextColor: '#9a3412',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          mint_green: {
            header_bg: [34, 197, 94], // #22c55e
            header_border: [74, 222, 128], // #4ade80
            row_border: [187, 247, 208], // #bbf7d0
            alt_row_bg: [240, 253, 244], // #f0fdf4
            headerTextColor: '#ffffff',
            rowTextColor: '#166534',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          violet_dream: {
            header_bg: [109, 40, 217], // #6d28d9
            header_border: [168, 85, 247], // #a855f7
            row_border: [233, 213, 255], // #e9d5ff
            alt_row_bg: [251, 245, 255], // #fbf5ff
            headerTextColor: '#ffffff',
            rowTextColor: '#6b21a8',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          sky_blue: {
            header_bg: [3, 105, 161], // #0369a1
            header_border: [14, 165, 233], // #0ea5e9
            row_border: [125, 211, 252], // #7dd3fc
            alt_row_bg: [240, 249, 255], // #f0f9ff
            headerTextColor: '#ffffff',
            rowTextColor: '#0c4a6e',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          forest_green: {
            header_bg: [21, 128, 61], // #15803d
            header_border: [34, 197, 94], // #22c55e
            row_border: [134, 239, 172], // #86efac
            alt_row_bg: [236, 253, 245], // #ecfdf5
            headerTextColor: '#ffffff',
            rowTextColor: '#14532d',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          },
          ruby_red: {
            header_bg: [185, 28, 28], // #b91c1c
            header_border: [239, 68, 68], // #ef4444
            row_border: [252, 165, 165], // #fca5a5
            alt_row_bg: [254, 226, 226], // #fee2e2
            headerTextColor: '#ffffff',
            rowTextColor: '#991b1b',
            border_width: 1,
            headerFontWeight: 'bold',
            headerFontSize: '12px',
            rowFontSize: '11px'
          }
        }
      }
    };
  }

  /**
   * Génère des données d'exemple pour un type d'élément spécifique
   * @param {string} elementType - Type de l'élément
   * @param {Object} properties - Propriétés de l'élément
   * @returns {Promise<any>} Données d'exemple
   */
  async getElementData(elementType, properties) {
    switch (elementType) {
      case 'product_table':
        return this.generateProductTableData(properties);

      case 'customer_info':
        return this.generateCustomerInfoData(properties);

      case 'company_logo':
        return this.generateCompanyLogoData(properties);

      case 'company_info':
        return this.generateCompanyInfoData(properties);

      case 'order_number':
        return this.generateOrderNumberData(properties);

      case 'dynamic-text':
      case 'conditional-text':
        return this.generateDynamicTextData(properties);

      case 'mentions':
        return this.generateMentionsData(properties);

      case 'rectangle':
      case 'line':
      case 'shape-rectangle':
      case 'shape-circle':
      case 'shape-line':
      case 'shape-arrow':
      case 'shape-triangle':
      case 'shape-star':
      case 'divider':
        return this.generateRectangleData(properties);

      case 'barcode':
      case 'qrcode':
        return this.generateBarcodeData(properties);

      case 'progress-bar':
        return this.generateProgressBarData(properties);

      case 'watermark':
        return this.generateWatermarkData(properties);

      default:
        return this.generateDefaultData(elementType, properties);
    }
  }

  /**
   * Génère des données pour un tableau de produits
   */
  generateProductTableData(properties) {
    const {
      columns = {},
      showSubtotal = false,
      showShipping = true,
      showTaxes = true,
      showDiscount = true,
      showTotal = true,
      tableStyle = 'default'
    } = properties;

    const tableData = {
      headers: [],
      rows: [],
      totals: {},
      style: tableStyle
    };

    // Configuration par défaut des colonnes si non spécifiée
    const defaultColumns = {
      image: false,
      name: true,
      sku: false,
      description: false,
      short_description: false,
      categories: false,
      quantity: true,
      price: true,
      regular_price: false,
      sale_price: false,
      discount: false,
      tax: false,
      weight: false,
      dimensions: false,
      attributes: false,
      stock_quantity: false,
      stock_status: false,
      total: true
    };

    // Fusionner avec les colonnes spécifiées
    const activeColumns = { ...defaultColumns, ...columns };

    // Déterminer les colonnes à afficher
    if (activeColumns.image !== false) tableData.headers.push('Image');
    if (activeColumns.name !== false) tableData.headers.push('Produit');
    if (activeColumns.sku !== false) tableData.headers.push('SKU');
    if (activeColumns.description !== false) tableData.headers.push('Description');
    if (activeColumns.short_description !== false) tableData.headers.push('Description courte');
    if (activeColumns.categories !== false) tableData.headers.push('Catégories');
    if (activeColumns.quantity !== false) tableData.headers.push('Qté');
    if (activeColumns.price !== false) tableData.headers.push('Prix');
    if (activeColumns.regular_price !== false) tableData.headers.push('Prix régulier');
    if (activeColumns.sale_price !== false) tableData.headers.push('Prix soldé');
    if (activeColumns.discount !== false) tableData.headers.push('Remise');
    if (activeColumns.tax !== false) tableData.headers.push('TVA');
    if (activeColumns.weight !== false) tableData.headers.push('Poids');
    if (activeColumns.dimensions !== false) tableData.headers.push('Dimensions');
    if (activeColumns.attributes !== false) tableData.headers.push('Attributs');
    if (activeColumns.stock_quantity !== false) tableData.headers.push('Stock');
    if (activeColumns.stock_status !== false) tableData.headers.push('Statut stock');
    if (activeColumns.total !== false) tableData.headers.push('Total');

    // Générer les lignes de produits
    tableData.rows = this.sampleData.products.map(product => {
      const row = [];
      if (activeColumns.image !== false) row.push(product.image);
      if (activeColumns.name !== false) row.push(product.name);
      if (activeColumns.sku !== false) row.push(product.sku || '-');
      if (activeColumns.description !== false) row.push(product.description || '-');
      if (activeColumns.short_description !== false) row.push(product.short_description || '-');
      if (activeColumns.categories !== false) row.push(product.categories ? product.categories.join(', ') : '-');
      if (activeColumns.quantity !== false) row.push(product.quantity);
      if (activeColumns.price !== false) row.push(product.price);
      if (activeColumns.regular_price !== false) row.push(product.regular_price || '-');
      if (activeColumns.sale_price !== false) row.push(product.sale_price || '-');
      if (activeColumns.discount !== false) row.push(product.discount || '0,00 €');
      if (activeColumns.tax !== false) row.push(product.tax || '0,00 €');
      if (activeColumns.weight !== false) row.push(product.weight || '-');
      if (activeColumns.dimensions !== false) row.push(product.dimensions || '-');
      if (activeColumns.attributes !== false) {
        const attrs = product.attributes ? Object.entries(product.attributes).map(([k, v]) => `${k}: ${v}`).join('; ') : '-';
        row.push(attrs);
      }
      if (activeColumns.stock_quantity !== false) row.push(product.stock_quantity || '-');
      if (activeColumns.stock_status !== false) row.push(product.stock_status === 'en_stock' ? 'En stock' : 'Rupture');
      if (activeColumns.total !== false) row.push(product.total);
      return row;
    });

    // Ajouter les totaux si demandés
    if (showSubtotal) tableData.totals.subtotal = this.sampleData.order.subtotal;
    if (showShipping) tableData.totals.shipping = this.sampleData.order.shipping;
    if (showDiscount) tableData.totals.discount = this.sampleData.order.discount;
    if (showTaxes) tableData.totals.tax = this.sampleData.order.tax;
    if (showTotal) tableData.totals.total = this.sampleData.order.total;

    // Ajouter les informations de style du tableau
    tableData.tableStyleData = this.sampleData.order.tableStyles[tableStyle] || this.sampleData.order.tableStyles['default'];

    return tableData;
  }

  /**
   * Génère des données pour les informations client
   */
  generateCustomerInfoData(properties) {
    const { fields = [] } = properties;

    const data = {};
    fields.forEach(field => {
      switch (field) {
        case 'name':
          data.name = this.sampleData.customer.name;
          break;
        case 'email':
          data.email = this.sampleData.customer.email;
          break;
        case 'phone':
          data.phone = this.sampleData.customer.phone;
          break;
        case 'address':
          data.address = this.sampleData.customer.address;
          break;
        case 'company':
          data.company = this.sampleData.customer.company;
          break;
        case 'vat':
          data.vat = this.sampleData.customer.vat;
          break;
        case 'siret':
          data.siret = this.sampleData.customer.siret;
          break;
      }
    });

    return data;
  }

  /**
   * Génère des données pour le logo entreprise
   */
  generateCompanyLogoData(properties) {
    return {
      imageUrl: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjgwIiB2aWV3Qm94PSIwIDAgMjAwIDgwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjMjU2M2ViIi8+Cjx0ZXh0IHg9IjEwMCIgeT0iNDAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5MT0dPPC90ZXh0Pgo8L3N2Zz4=',
      alt: 'Logo de l\'entreprise'
    };
  }

  /**
   * Génère des données pour les informations entreprise
   */
  generateCompanyInfoData(properties) {
    const { fields = [] } = properties;

    const data = {};
    fields.forEach(field => {
      switch (field) {
        case 'name':
          data.name = this.sampleData.company.name;
          break;
        case 'address':
          data.address = this.sampleData.company.address;
          break;
        case 'phone':
          data.phone = this.sampleData.company.phone;
          break;
        case 'email':
          data.email = this.sampleData.company.email;
          break;
        case 'website':
          data.website = this.sampleData.company.website;
          break;
        case 'vat':
          data.vat = this.sampleData.company.vat;
          break;
        case 'rcs':
          data.rcs = this.sampleData.company.rcs;
          break;
        case 'siret':
          data.siret = this.sampleData.company.siret;
          break;
      }
    });

    return data;
  }

  /**
   * Génère des données pour le numéro de commande
   */
  generateOrderNumberData(properties) {
    const { format = 'Commande #{order_number} - {order_date}' } = properties;

    return {
      formatted: format
        .replace('{order_number}', this.sampleData.order.number)
        .replace('{order_date}', this.sampleData.order.date)
    };
  }

  /**
   * Génère des données pour le texte dynamique
   */
  generateDynamicTextData(properties) {
    const { template = 'total_only', customContent = '', variables = {} } = properties;

    let content = customContent;

    // Templates prédéfinis avec leur contenu par défaut
    const templates = {
      'total_only': '{{order_total}} €',
      'order_info': 'Commande {{order_number}} - {{order_date}}',
      'customer_info': '{{customer_name}} - {{customer_email}}',
      'customer_address': '{{customer_name}}\n{{billing_address}}',
      'full_header': 'Facture N° {{order_number}}\nClient: {{customer_name}}\nTotal: {{order_total}} €',
      'invoice_header': 'FACTURE N° {{order_number}}\nDate: {{date}}\nClient: {{customer_name}}\n{{billing_address}}',
      'order_summary': 'Sous-total: {{order_subtotal}} €\nFrais de port: {{order_shipping}} €\nTVA: {{order_tax}} €\nTotal: {{order_total}} €',
      'payment_info': 'Échéance: {{due_date}}\nMontant: {{order_total}} €',
      'payment_terms': 'Conditions de paiement: 30 jours\nÉchéance: {{due_date}}\nMontant dû: {{order_total}} €',
      'shipping_info': 'Adresse de livraison:\n{{shipping_address}}',
      'thank_you': 'Merci pour votre commande !\nNous vous remercions de votre confiance.',
      'legal_notice': 'TVA non applicable - art. 293 B du CGI\nPaiement à 30 jours fin de mois',
      'bank_details': 'Coordonnées bancaires:\nIBAN: FR76 1234 5678 9012 3456 7890 123\nBIC: BNPAFRPP',
      'contact_info': 'Contact: contact@monentreprise.com\nTél: 01 23 45 67 89',
      'order_confirmation': 'CONFIRMATION DE COMMANDE\nCommande {{order_number}} du {{order_date}}\nStatut: Confirmée',
      'delivery_note': 'BON DE LIVRAISON\nCommande {{order_number}}\nDestinataire: {{customer_name}}\n{{shipping_address}}',
      'warranty_info': 'Garantie: 2 ans pièces et main d\'œuvre\nService après-vente: sav@monentreprise.com',
      'return_policy': 'Droit de rétractation: 14 jours\nRetour sous 30 jours pour défauts',
      'signature_line': 'Signature du client:\n\n_______________________________\nDate: {{date}}',
      'invoice_footer': 'Facture générée automatiquement le {{date}}\nConservez cette facture pour vos archives',
      'terms_conditions': 'Conditions générales de vente disponibles sur notre site\nwww.monentreprise.com/conditions',
      'quality_guarantee': 'Tous nos produits sont garantis contre les défauts\nService qualité: qualite@monentreprise.com',
      'eco_friendly': 'Entreprise engagée pour l\'environnement\nEmballages recyclables et biodégradables',
      'follow_up': 'Suivi de commande: {{order_number}}\nContact: suivi@monentreprise.com',
      'custom': customContent || '{{order_total}} €'
    };

    // Utiliser le template prédéfini si disponible, sinon utiliser customContent
    if (templates[template]) {
      content = templates[template];
    } else if (template === 'custom') {
      content = customContent;
    }

    // Remplacer toutes les variables disponibles
    const allData = {
      ...this.sampleData.customer,
      ...this.sampleData.company,
      ...this.sampleData.order,
      // Variables personnalisées passées en paramètre
      ...variables
    };

    // Remplacer les variables {{variable}} dans le contenu
    Object.keys(allData).forEach(key => {
      const regex = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
      content = content.replace(regex, allData[key] || '');
    });

    // Remplacer les variables d'articles si elles existent
    if (this.sampleData.products && this.sampleData.products.length > 0) {
      const firstProduct = this.sampleData.products[0];
      Object.keys(firstProduct).forEach(key => {
        const regex = new RegExp(`\\{\\{product_${key}\\}\\}`, 'g');
        content = content.replace(regex, firstProduct[key] || '');
      });
    }

    return { content };
  }

  /**
   * Génère des données pour les mentions légales
   */
  generateMentionsData(properties) {
    const {
      showEmail = true,
      showPhone = true,
      showSiret = true,
      showVat = false,
      showAddress = false,
      showWebsite = false,
      showCustomText = false,
      customText = ''
    } = properties;

    const mentions = [];

    if (showEmail) mentions.push(this.sampleData.company.email);
    if (showPhone) mentions.push(this.sampleData.company.phone);
    if (showSiret) mentions.push(`SIRET: ${this.sampleData.company.siret}`);
    if (showVat) mentions.push(`TVA: ${this.sampleData.company.vat}`);
    if (showAddress) mentions.push(this.sampleData.company.address.replace('\n', ' • '));
    if (showWebsite) mentions.push(this.sampleData.company.website);
    if (showCustomText && customText) mentions.push(customText);

    return { mentions };
  }

  /**
   * Génère des données pour les rectangles et formes géométriques
   */
  generateRectangleData(properties) {
    return {
      // Les rectangles utilisent principalement les propriétés CSS de base
      rendered: true
    };
  }

  /**
   * Génère des données pour les codes-barres et QR codes
   */
  generateBarcodeData(properties) {
    const { content = '123456789' } = properties;
    return {
      code: content,
      format: properties.type === 'qrcode' ? 'QR_CODE' : 'CODE128'
    };
  }

  /**
   * Génère des données pour les barres de progression
   */
  generateProgressBarData(properties) {
    const { progressValue = 75 } = properties;
    return {
      progress: Math.min(100, Math.max(0, progressValue)),
      label: `${progressValue}%`
    };
  }

  /**
   * Génère des données pour les filigranes
   */
  generateWatermarkData(properties) {
    const { content = 'CONFIDENTIEL' } = properties;
    return {
      text: content,
      angle: -45
    };
  }

  /**
   * Génère des données par défaut pour les éléments non supportés
   */
  generateDefaultData(elementType, properties) {
    return {
      type: elementType,
      placeholder: `Données d'exemple pour ${elementType}`,
      properties
    };
  }
}