/**
 * Value Resolver - Gestion des valeurs fictives vs réelles
 * 
 * Classe responsable de résoudre les valeurs pour chaque élément
 * en fonction du mode (édition avec données fictives vs aperçu avec données réelles)
 * 
 * Architecture inspirée d'OrderValueRetriever du plugin concurrent
 * 
 * SPÉCIAL: product_table gère les produits + totaux + frais en mode preview
 * 
 * @module persistence/ValueResolver
 * @version 1.0.0
 */

import type { 
  Element,
  ProductTableData,
  ProductTableProduct,
  ProductTableTotals,
  ProductTableFee 
} from '../types/elements';

/**
 * Structure de données réelles WooCommerce
 * Contient les données de la commande actuelle
 */
export interface RealOrderData {
  orderId?: string | number;
  orderNumber?: string;
  orderDate?: string;
  customerName?: string;
  customerEmail?: string;
  customerPhone?: string;
  customerAddress?: string;
  customerCompany?: string;
  paymentMethod?: string;
  transactionId?: string;
  products?: Array<{
    name: string;
    sku: string;
    quantity: number;
    price: number;
    total: number;
  }>;
  productCount?: number;
  subtotal?: number;
  shippingCost?: number;
  taxCost?: number;
  taxRate?: number;
  discount?: number;
  total?: number;
  fees?: Array<{ name: string; total: number }>;
  totalFees?: number;
  companyName?: string;
  companyAddress?: string;
  companyPhone?: string;
  companyEmail?: string;
  companyWebsite?: string;
  companyTaxId?: string;
  companyRegistrationNumber?: string;
  [key: string]: unknown;
}

/**
 * Valeurs fictives pour product_table (produits + frais + totaux)
 * Les frais sont au même niveau que produits, pas imbriqués dans totaux
 */
const DEFAULT_TEST_PRODUCT_TABLE: ProductTableData = {
  products: [
    {
      name: 'Produit 1 - Exemple',
      sku: 'SKU-001',
      quantity: 2,
      price: 29.99,
      total: 59.98,
      description: 'Description du produit 1',
    },
    {
      name: 'Produit 2 - Exemple',
      sku: 'SKU-002',
      quantity: 1,
      price: 49.99,
      total: 49.99,
      description: 'Description du produit 2',
    },
  ],
  fees: [
    {
      name: 'Frais de service',
      total: 15.00,
    },
  ],  // ✅ REFACTOR: Frais au même niveau que produits
  totals: {
    subtotal: 109.97,
    shippingCost: 9.99,
    taxCost: 16.50,
    taxRate: 15,
    discount: 0,
    total: 151.46,  // 109.97 + 9.99 + 16.50 + 15.00 = 151.46
  },
};

/**
 * Valeurs fictives/par défaut pour l'édition
 */
const DEFAULT_TEST_VALUES = {
  order_number: 'N° 001',
  woocommerce_order_date: new Date().toLocaleDateString('fr-FR'),
  woocommerce_invoice_number: 'INV-001',
  customer_info: {
    name: 'Jean Dupont',
    email: 'jean@example.com',
    phone: '+33 1 23 45 67 89',
    address: '123 Rue de la Paix\n75000 Paris',
    company: 'Dupont SARL',
    transactionId: 'TXN-12345',
  },
  product_table: [
    {
      name: 'Produit 1',
      sku: 'SKU-001',
      quantity: 2,
      price: 29.99,
      total: 59.98,
    },
    {
      name: 'Produit 2',
      sku: 'SKU-002',
      quantity: 1,
      price: 49.99,
      total: 49.99,
    },
  ],
  company_info: {
    name: 'Ma Société SAS',
    address: '456 Avenue du Commerce\n92100 Boulogne',
    phone: '+33 2 34 56 78 90',
    email: 'contact@masociete.com',
    website: 'www.masociete.com',
    taxId: 'FR 12 345 678 901',
    registrationNumber: 'SIRET 12345678901234',
  },
};

/**
 * Configuration d'un élément pour la résolution de valeurs
 */
export interface ElementValueConfig {
  /** Type d'élément (order_number, customer_info, etc.) */
  elementType: string;
  
  /** Indique si c'est un élément avec données réelles */
  isRealDataElement: boolean;
  
  /** Valeur fictive par défaut (affichée en édition) */
  testValue?: unknown;
  
  /** Identifiant de la propriété réelle à récupérer */
  realDataKey?: string;
  
  /** Propriétés personnalisées sauvegardées */
  customProperties?: Record<string, unknown>;
  
  /** ✅ NEW: L'élément lui-même (pour récupérer ses données) */
  element?: Record<string, unknown>;
}

/**
 * ValueResolver - Résout les valeurs des éléments
 * 
 * Responsabilités:
 * 1. Fournir les valeurs fictives pour l'édition
 * 2. Fournir les valeurs réelles depuis WooCommerce
 * 3. Appliquer les exceptions (company_info = toujours réelle)
 * 4. Gérer les fallbacks et valeurs par défaut
 */
export class ValueResolver {
  private isPreviewMode: boolean;
  private realData: RealOrderData | null;

  /**
   * Crée une instance de ValueResolver
   * 
   * @param isPreviewMode - true = données fictives, false = données réelles
   * @param realData - Les données réelles WooCommerce (optionnel)
   */
  constructor(isPreviewMode: boolean = true, realData: RealOrderData | null = null) {
    this.isPreviewMode = isPreviewMode;
    this.realData = realData;
  }

  /**
   * Résout la valeur pour un élément donné
   * 
   * Logique:
   * 1. Si company_info → toujours retourner la vraie valeur (EXCEPTION)
   * 2. Si product_table:
   *    a. Mode édition: récupère les produits/totaux DE L'ÉLÉMENT (ce que l'utilisateur a défini)
   *    b. Mode preview: récupère les produits/totaux depuis WooCommerce
   * 3. Si en mode édition → retourne testValue
   * 4. Si mode réel (preview) → retourner valeur réelle depuis WooCommerce
   * 5. Sinon → retourner testValue (fallback)
   * 
   * @param config - Configuration d'élément (avec element optionnel)
   * @returns La valeur à afficher/utiliser
   */
  public getValue(config: ElementValueConfig): unknown {
    // Exception: company_info récupère TOUJOURS les vraies valeurs
    if (config.elementType === 'company_info') {
      return this.getRealValue(config);
    }

    // Spécial: product_table récupère depuis l'élément OU depuis WooCommerce
    if (config.elementType === 'product_table') {
      if (this.isPreviewMode && config.element) {
        // ✅ Mode édition (isPreviewMode=true) - récupérer du canvas React
        const canvasData = this.getProductTableFromElement(config.element);
        if (canvasData) {
          return canvasData;
        }
      } else if (!this.isPreviewMode && this.realData) {
        // Mode aperçu (isPreviewMode=false) - récupérer depuis WooCommerce
        return this.buildProductTableData(this.realData);
      }
      // Fallback: utiliser valeurs fictives
      return this.getTestValue(config);
    }

    // Mode édition: toujours utiliser les valeurs fictives (ou du canvas pour product_table)
    if (this.isPreviewMode) {
      return this.getTestValue(config);
    }

    // Mode réel: récupérer depuis les données réelles (avec fallback sur test)
    if (!this.isPreviewMode && this.realData) {
      const realValue = this.getRealValue(config);
      if (realValue !== undefined && realValue !== null) {
        return realValue;
      }
    }

    // Fallback: utiliser la valeur de test
    return this.getTestValue(config);
  }

  /**
   * Construit les données complètes pour product_table
   * Combine produits + frais + totaux depuis les données réelles
   * 
   * Structure: Les frais sont au même niveau que les produits (pas imbriqués dans totaux)
   * Ça rend le rendu PDF plus logique: on affiche produits, puis frais, puis totaux
   * 
   * Inspiré de DocumentGenerator.php du plugin concurrent
   */
  private buildProductTableData(realData: RealOrderData): ProductTableData {
    // Construire les produits
    const products: ProductTableProduct[] = (realData.products || []).map(p => ({
      name: p.name,
      sku: p.sku || 'N/A',
      quantity: p.quantity,
      price: p.price,
      total: p.total,
    }));

    // ✅ REFACTOR: Extraire les frais au même niveau que produits
    const fees: ProductTableFee[] = (realData.fees || []).map(f => ({
      name: f.name,
      total: f.total,
    }));

    // Construire les totaux (SANS frais imbriqués)
    const totals: ProductTableTotals = {
      subtotal: realData.subtotal || 0,
      shippingCost: realData.shippingCost || 0,
      taxCost: realData.taxCost || 0,
      taxRate: realData.taxRate || 0,
      discount: realData.discount || 0,
      total: realData.total || 0,
    };

    return {
      products,
      fees,  // ✅ NOUVEAU: Frais au même niveau que produits
      totals,
    };
  }

  /**
   * ✅ NEW: Récupère les données product_table depuis le canvas React
   * 
   * En mode édition, utilise ce que l'utilisateur a défini dans l'élément
   * (produits + frais + totaux qu'il a saisis/modifiés)
   * 
   * Retourne la structure avec frais au même niveau que produits
   * 
   * Si l'élément a déjà products/totals/fees, les retourne
   * Sinon, retourne undefined pour utiliser les valeurs fictives
   */
  private getProductTableFromElement(element: Record<string, unknown>): ProductTableData | undefined {
    // Extraire products, fees, et totals de l'élément
    const products = element.products;
    const fees = element.fees;
    const totals = element.totals;

    // Si au moins l'un des trois existe, les utiliser
    if (products || fees || totals) {
      return {
        products: Array.isArray(products) ? (products as ProductTableProduct[]) : [],
        fees: Array.isArray(fees) ? (fees as ProductTableFee[]) : [],  // ✅ NOUVEAU: Extraire frais
        totals: (totals as ProductTableTotals) || {
          subtotal: 0,
          shippingCost: 0,
          taxCost: 0,
          taxRate: 0,
          discount: 0,
          total: 0,
        },
      };
    }

    // Pas de données: retourner undefined pour utiliser les valeurs fictives
    return undefined;
  }

  /**
   * Récupère la valeur fictive/par défaut
   */
  private getTestValue(config: ElementValueConfig): unknown {
    // Utiliser la testValue personnalisée si fournie
    if (config.testValue !== undefined) {
      return config.testValue;
    }

    // Utiliser la valeur par défaut du système
    const defaults = DEFAULT_TEST_VALUES as Record<string, unknown>;
    return defaults[config.elementType] || `[${config.elementType}]`;
  }

  /**
   * Récupère la valeur réelle depuis les données WooCommerce
   */
  private getRealValue(config: ElementValueConfig): unknown {
    if (!this.realData) {
      return undefined;
    }

    // Utiliser la clé spécifiée (realDataKey)
    if (config.realDataKey) {
      return this.getNestedValue(this.realData, config.realDataKey);
    }

    // Fallback: utiliser le elementType comme clé
    return this.getNestedValue(this.realData, config.elementType);
  }

  /**
   * Utilitaire pour récupérer une valeur imbriquée
   * Support des chemins comme "customer.name" → customer_info?.customerName
   */
  private getNestedValue(obj: any, path: string): unknown {
    if (!obj) return undefined;

    // Conversion des chemins
    // "order_number" → "orderNumber"
    // "customer_info" → "customer*" (cherche customerName, customerEmail, etc.)
    // "company_info" → "company*"

    const camelPath = this.toCamelCase(path);
    
    if (camelPath in obj) {
      return obj[camelPath];
    }

    // Chercher les variantes possibles
    const keys = Object.keys(obj);
    const matchingKeys = keys.filter(key => 
      key.toLowerCase().includes(path.toLowerCase().replace(/_/g, ''))
    );

    if (matchingKeys.length === 1) {
      return obj[matchingKeys[0]];
    }

    if (matchingKeys.length > 1) {
      // Retourner objet si plusieurs correspondances
      const result: Record<string, unknown> = {};
      matchingKeys.forEach(key => {
        result[key] = obj[key];
      });
      return result;
    }

    return undefined;
  }

  /**
   * Convertit snake_case en camelCase
   * "order_number" → "orderNumber"
   */
  private toCamelCase(str: string): string {
    return str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
  }

  /**
   * Met à jour le mode de prévisualisation
   */
  public setPreviewMode(isPreview: boolean): void {
    this.isPreviewMode = isPreview;
  }

  /**
   * Met à jour les données réelles
   */
  public setRealData(data: RealOrderData | null): void {
    this.realData = data;
  }

  /**
   * Vérifie si on est en mode édition
   */
  public isEditorMode(): boolean {
    return this.isPreviewMode;
  }

  /**
   * Obtient les valeurs par défaut du système
   */
  public static getDefaultTestValues(): typeof DEFAULT_TEST_VALUES {
    return DEFAULT_TEST_VALUES;
  }

  /**
   * Crée une configuration de base pour un élément
   */
  public static createElementConfig(
    elementType: string,
    isRealData: boolean,
    testValue?: unknown,
    realDataKey?: string
  ): ElementValueConfig {
    return {
      elementType,
      isRealDataElement: isRealData,
      testValue,
      realDataKey: realDataKey || elementType,
    };
  }
}
