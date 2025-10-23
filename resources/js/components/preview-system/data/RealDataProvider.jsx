/**
 * Fournisseur de données réelles pour le mode Metabox
 * Récupère les vraies données depuis WooCommerce
 */
export class RealDataProvider {
  constructor() {
    this.ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
    this.nonce = window.pdfBuilderPro?.nonce || window.pdfBuilderAjax?.nonce || '';
    this.variablesCache = new Map(); // Cache des variables par commande
  }

  /**
   * Charge les données complètes d'une commande WooCommerce
   * @param {number} orderId - ID de la commande
   * @returns {Promise<Object>} Données de la commande
   */
  async loadOrderData(orderId) {
    const response = await this.makeAjaxRequest('pdf_builder_get_order_data', {
      order_id: orderId
    });

    if (!response.success) {
      throw new Error(response.data?.message || 'Erreur lors du chargement des données de commande');
    }

    return response.data.order;
  }

  /**
   * Charge les variables mappées pour une commande (utilise VariableMapper PHP)
   * @param {number} orderId - ID de la commande
   * @returns {Promise<Object>} Variables mappées
   */
  async loadVariables(orderId) {
    if (this.variablesCache.has(orderId)) {
      return this.variablesCache.get(orderId);
    }

    const response = await this.makeAjaxRequest('pdf_builder_get_order_preview_data', {
      order_id: orderId
    });

    if (!response.success) {
      throw new Error(response.data?.message || 'Erreur lors du chargement des variables d\'aperçu');
    }

    const variables = response.data.variables;
    this.variablesCache.set(orderId, variables);
    return variables;
  }

  /**
   * Valide l'accès à une commande
   * @param {number} orderId - ID de la commande
   * @returns {Promise<void>}
   */
  async validateOrderAccess(orderId) {
    const response = await this.makeAjaxRequest('pdf_builder_validate_order_access', {
      order_id: orderId
    });

    if (!response.success) {
      throw new Error(response.data?.message || 'Accès non autorisé à cette commande');
    }
  }

  /**
   * Génère des données réelles pour un type d'élément spécifique
   * @param {string} elementType - Type de l'élément
   * @param {Object} properties - Propriétés de l'élément
   * @param {Object} orderData - Données de la commande
   * @returns {Promise<any>} Données réelles
   */
  async getElementData(elementType, properties, orderData) {
    switch (elementType) {
      case 'product_table':
        return this.generateProductTableData(properties, orderData);

      case 'customer_info':
        return this.generateCustomerInfoData(properties, orderData);

      case 'company_logo':
        return this.generateCompanyLogoData(properties, orderData);

      case 'company_info':
        return this.generateCompanyInfoData(properties, orderData);

      case 'order_number':
        return this.generateOrderNumberData(properties, orderData);

      case 'dynamic-text':
        return this.generateDynamicTextData(properties, orderData);

      case 'mentions':
        return this.generateMentionsData(properties, orderData);

      default:
        return this.generateDefaultData(elementType, properties, orderData);
    }
  }

  /**
   * Génère des données pour un tableau de produits (données réelles)
   */
  generateProductTableData(properties, orderData) {
    const { columns = {}, showSubtotal = false, showShipping = true, showTaxes = true, tableStyle = 'default' } = properties;

    const tableData = {
      headers: [],
      rows: [],
      totals: {}
    };

    // Déterminer les colonnes à afficher
    if (columns.image !== false) tableData.headers.push('Image');
    if (columns.name !== false) tableData.headers.push('Produit');
    if (columns.sku !== false) tableData.headers.push('SKU');
    if (columns.quantity !== false) tableData.headers.push('Qté');
    if (columns.price !== false) tableData.headers.push('Prix');
    if (columns.total !== false) tableData.headers.push('Total');

    // Générer les lignes de produits depuis les données réelles
    if (orderData.items && Array.isArray(orderData.items)) {
      tableData.rows = orderData.items.map(item => {
        const row = [];
        if (columns.image !== false) row.push(item.image || '');
        if (columns.name !== false) row.push(item.name || '');
        if (columns.sku !== false) row.push(item.sku || '');
        if (columns.quantity !== false) row.push(item.quantity || 0);
        if (columns.price !== false) row.push(item.price || '0 €');
        if (columns.total !== false) row.push(item.total || '0 €');
        return row;
      });
    }

    // Ajouter les totaux si demandés
    if (showSubtotal) tableData.totals.subtotal = orderData.subtotal || '0 €';
    if (showShipping) tableData.totals.shipping = orderData.shipping_total || '0 €';
    if (showTaxes) tableData.totals.tax = orderData.total_tax || '0 €';
    tableData.totals.total = orderData.total || '0 €';

    // Ajouter les données de style du tableau (même logique que SampleDataProvider)
    tableData.tableStyleData = this.getTableStyleData(tableStyle);

    return tableData;
  }

  /**
   * Génère des données pour les informations client (données réelles)
   */
  generateCustomerInfoData(properties, orderData) {
    const { fields = [] } = properties;

    const data = {};
    fields.forEach(field => {
      switch (field) {
        case 'name':
          data.name = orderData.billing?.first_name && orderData.billing?.last_name
            ? `${orderData.billing.first_name} ${orderData.billing.last_name}`
            : '';
          break;
        case 'email':
          data.email = orderData.billing?.email || '';
          break;
        case 'phone':
          data.phone = orderData.billing?.phone || '';
          break;
        case 'address':
          data.address = this.formatAddress(orderData.billing);
          break;
        case 'company':
          data.company = orderData.billing?.company || '';
          break;
        case 'vat':
          data.vat = orderData.billing?.vat || '';
          break;
        case 'siret':
          data.siret = orderData.billing?.siret || '';
          break;
      }
    });

    return data;
  }

  /**
   * Génère des données pour le logo entreprise
   */
  generateCompanyLogoData(properties, orderData) {
    // Le logo entreprise est généralement stocké dans les options WordPress
    // Pour l'instant, retourner une valeur par défaut
    return {
      imageUrl: '', // À récupérer depuis wp_options
      alt: 'Logo de l\'entreprise'
    };
  }

  /**
   * Génère des données pour les informations entreprise (données réelles)
   */
  async generateCompanyInfoData(properties, orderData) {
    const { fields = [] } = properties;

    try {
      // Récupérer les données entreprise via AJAX
      const response = await this.makeAjaxRequest('pdf_builder_get_company_data');

      if (!response.success || !response.data?.company) {
        // Fallback vers des données vides en cas d'erreur
        const data = {};
        fields.forEach(field => {
          data[field] = '';
        });
        return data;
      }

      const companyData = response.data.company;
      const data = {};

      // Mapper les champs demandés avec les données récupérées
      fields.forEach(field => {
        switch (field) {
          case 'name':
            data.name = companyData.name || '';
            break;
          case 'address':
            data.address = companyData.address || '';
            break;
          case 'phone':
            data.phone = companyData.phone || '';
            break;
          case 'email':
            data.email = companyData.email || '';
            break;
          case 'website':
            data.website = companyData.website || '';
            break;
          case 'vat':
            data.vat = companyData.vat || '';
            break;
          case 'rcs':
            data.rcs = companyData.rcs || '';
            break;
          case 'siret':
            data.siret = companyData.siret || '';
            break;
          default:
            data[field] = '';
        }
      });

      return data;

    } catch (error) {
      console.warn('Erreur lors de la récupération des données entreprise:', error);
      // Fallback vers des données vides
      const data = {};
      fields.forEach(field => {
        data[field] = '';
      });
      return data;
    }
  }

  /**
   * Génère des données pour le numéro de commande
   */
  generateOrderNumberData(properties, orderData) {
    const { format = 'Commande #{order_number} - {order_date}' } = properties;

    return {
      formatted: format
        .replace('{order_number}', orderData.number || orderData.id || '')
        .replace('{order_date}', orderData.date_created || '')
    };
  }

  /**
   * Génère des données pour un élément de texte dynamique
   */
  async generateDynamicTextData(properties, orderData) {
    const { template = 'total_only', customContent = '' } = properties;

    let content = customContent;

    if (template === 'total_only') {
      content = `Total: ${orderData.total || '0 €'}`;
    }

    // Charger les variables mappées depuis le VariableMapper PHP
    try {
      const variables = await this.loadVariables(orderData.id || orderData.order?.id);

      // Remplacer les variables avec les vraies données du VariableMapper
      content = this.replaceVariablesWithMapper(content, variables);
    } catch (error) {
      console.warn('Erreur lors du chargement des variables, utilisation du fallback:', error);
      // Fallback vers l'ancien système si le VariableMapper échoue
      content = this.replaceVariables(content, orderData);
    }

    return { content };
  }

  /**
   * Génère des données pour les mentions légales
   */
  generateMentionsData(properties, orderData) {
    // Les mentions légales sont généralement dans wp_options
    // Simulation pour l'instant
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

    if (showEmail) mentions.push(orderData.company_email || '');
    if (showPhone) mentions.push(orderData.company_phone || '');
    if (showSiret) mentions.push(`SIRET: ${orderData.company_siret || ''}`);
    if (showVat) mentions.push(`TVA: ${orderData.company_vat || ''}`);
    if (showAddress) mentions.push(orderData.company_address || '');
    if (showWebsite) mentions.push(orderData.company_website || '');
    if (showCustomText && customText) mentions.push(customText);

    return { mentions };
  }

  /**
   * Génère des données par défaut pour les éléments non supportés
   */
  generateDefaultData(elementType, properties, orderData) {
    return {
      type: elementType,
      placeholder: `Données réelles pour ${elementType}`,
      properties,
      orderData
    };
  }

  /**
   * Formate une adresse depuis les données WooCommerce
   */
  formatAddress(addressData) {
    if (!addressData) return '';

    const parts = [
      addressData.address_1,
      addressData.address_2,
      addressData.postcode,
      addressData.city,
      addressData.state,
      addressData.country
    ].filter(Boolean);

    return parts.join('\n');
  }

  /**
   * Remplace les variables dynamiques dans un contenu
   */
  replaceVariables(content, orderData) {
    if (!content || !orderData) return content;

    const replacements = {
      '{{order_number}}': orderData.number || orderData.id || '',
      '{{order_date}}': orderData.date_created || '',
      '{{order_total}}': orderData.total || '0 €',
      '{{order_status}}': orderData.status || '',
      '{{customer_name}}': orderData.billing?.first_name && orderData.billing?.last_name
        ? `${orderData.billing.first_name} ${orderData.billing.last_name}`
        : '',
      '{{customer_email}}': orderData.billing?.email || '',
      '{{customer_phone}}': orderData.billing?.phone || '',
      '{{billing_address}}': this.formatAddress(orderData.billing),
      '{{shipping_address}}': this.formatAddress(orderData.shipping),
      '{{payment_method}}': orderData.payment_method_title || '',
      '{{shipping_method}}': orderData.shipping_method || '',
      '{{subtotal}}': orderData.subtotal || '0 €',
      '{{tax_amount}}': orderData.total_tax || '0 €',
      '{{shipping_amount}}': orderData.shipping_total || '0 €',
      '{{discount_amount}}': orderData.discount_total || '0 €',
      '{{total_excl_tax}}': orderData.total_excl_tax || '0 €'
    };

    let result = content;
    Object.entries(replacements).forEach(([variable, value]) => {
      result = result.replace(new RegExp(variable.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), value);
    });

    return result;
  }

  /**
   * Remplace les variables dans le contenu en utilisant les données du VariableMapper PHP
   * @param {string} content - Contenu avec variables
   * @param {Object} variables - Variables du VariableMapper
   * @returns {string} Contenu avec variables remplacées
   */
  replaceVariablesWithMapper(content, variables) {
    if (!content || !variables) return content;

    let result = content;

    // Remplacer chaque variable du mapper
    Object.entries(variables).forEach(([key, value]) => {
      const variable = `{{${key}}}`;
      const regex = new RegExp(variable.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
      result = result.replace(regex, value || '');
    });

    return result;
  }

  /**
   * Récupère les données de style pour un tableau
   */
  getTableStyleData(tableStyle) {
    const tableStyles = {
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
        header_bg: [59, 130, 246], // #3b82f6
        header_border: [37, 99, 235], // #2563eb
        row_border: [226, 232, 240], // #e2e8f0
        alt_row_bg: [248, 249, 250], // #f8fafc
        headerTextColor: '#ffffff',
        rowTextColor: '#1e293b',
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
        header_bg: [16, 185, 129], // #10b981
        header_border: [5, 150, 105], // #059669
        row_border: [209, 213, 219], // #d1d5db
        alt_row_bg: [236, 253, 245], // #ecfdf5
        headerTextColor: '#ffffff',
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
        header_bg: [12, 74, 110], // #0c4a6e
        header_border: [2, 132, 199], // #0284c7
        row_border: [186, 230, 253], // #bae6fd
        alt_row_bg: [240, 249, 255], // #f0f9ff
        headerTextColor: '#ffffff',
        rowTextColor: '#0c4a6e',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      sunset_orange: {
        header_bg: [154, 52, 18], // #9a3412
        header_border: [234, 88, 12], // #ea580c
        row_border: [253, 186, 116], // #fdba74
        alt_row_bg: [255, 247, 237], // #fff7ed
        headerTextColor: '#ffffff',
        rowTextColor: '#9a3412',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      royal_purple: {
        header_bg: [88, 28, 135], // #581c87
        header_border: [147, 51, 234], // #9333ea
        row_border: [221, 214, 254], // #ddd6fe
        alt_row_bg: [250, 245, 255], // #faf5ff
        headerTextColor: '#ffffff',
        rowTextColor: '#581c87',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      rose_pink: {
        header_bg: [190, 18, 60], // #be123c
        header_border: [236, 72, 153], // #ec4899
        row_border: [253, 164, 175], // #fda4af
        alt_row_bg: [255, 241, 242], // #fff1f2
        headerTextColor: '#ffffff',
        rowTextColor: '#be123c',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      teal_aqua: {
        header_bg: [5, 150, 105], // #059669
        header_border: [20, 184, 166], // #14b8a6
        row_border: [153, 246, 228], // #99f6e4
        alt_row_bg: [236, 253, 245], // #ecfdf5
        headerTextColor: '#ffffff',
        rowTextColor: '#065f46',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      crimson_red: {
        header_bg: [153, 27, 27], // #991b1b
        header_border: [239, 68, 68], // #ef4444
        row_border: [252, 165, 165], // #fca5a5
        alt_row_bg: [254, 242, 242], // #fef2f2
        headerTextColor: '#ffffff',
        rowTextColor: '#991b1b',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      amber_gold: {
        header_bg: [161, 98, 7], // #a16207
        header_border: [245, 158, 11], // #f59e0b
        row_border: [253, 230, 138], // #fde68a
        alt_row_bg: [254, 252, 232], // #fefce8
        headerTextColor: '#ffffff',
        rowTextColor: '#92400e',
        border_width: 1,
        headerFontWeight: 'bold',
        headerFontSize: '12px',
        rowFontSize: '11px'
      },
      indigo_night: {
        header_bg: [49, 46, 129], // #312e81
        header_border: [99, 102, 241], // #6366f1
        row_border: [196, 181, 253], // #c4b5fd
        alt_row_bg: [245, 243, 255], // #f5f3ff
        headerTextColor: '#ffffff',
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
    };

    return tableStyles[tableStyle] || tableStyles['default'];
  }

  /**
   * Effectue une requête AJAX vers WordPress
   */
  async makeAjaxRequest(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('nonce', this.nonce);

    Object.entries(data).forEach(([key, value]) => {
      formData.append(key, value);
    });

    const response = await fetch(this.ajaxUrl, {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }

    return await response.json();
  }
}