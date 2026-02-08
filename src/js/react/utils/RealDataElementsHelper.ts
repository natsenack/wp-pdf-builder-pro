/**
 * Real Data Elements Helper
 * 
 * Initialise et configure les éléments qui utilisent des données réelles WooCommerce
 * 
 * Liste des éléments RealData:
 * - order_number: numéro de commande (test: "N° 001")
 * - woocommerce_order_date: date de la commande (test: date actuelle)
 * - customer_info: infos client (test: données fictives)
 * - product_table: tableau des produits (test: produits fictifs)
 * - company_info: infos entreprise (EXCEPTION: toujours vraies)
 * 
 * @module utils/RealDataElementsHelper
 * @version 1.0.0
 */

import type { 
  OrderNumberElement, 
  CustomerInfoElement, 
  ProductTableElement, 
  CompanyInfoElement,
  WoocommerceOrderDateElement,
  BaseElement,
  Element 
} from '../types/elements';

/**
 * Types d'éléments qui récupèrent des données réelles
 */
const REAL_DATA_ELEMENT_TYPES = [
  'order_number',
  'woocommerce_order_date',
  'customer_info',
  'product_table',
  'company_info',
];

/**
 * Valeurs fictives par défaut pour chaque type d'élément RealData
 */
const DEFAULT_TEST_VALUES: Record<string, unknown> = {
  order_number: 'N° 001',
  woocommerce_order_date: new Date().toLocaleDateString('fr-FR'),
  customer_info: {
    name: 'Jean Dupont',
    email: 'jean@example.com',
    phone: '+33 1 23 45 67 89',
  },
  product_table: [
    { name: 'Produit 1', sku: 'SKU-001', quantity: 2, price: 29.99 },
    { name: 'Produit 2', sku: 'SKU-002', quantity: 1, price: 49.99 },
  ],
  company_info: {
    name: 'Ma Société SAS',
    address: '456 Avenue du Commerce',
    phone: '+33 2 34 56 78 90',
  },
};

/**
 * Mapping des clés réelles WooCommerce pour chaque élément RealData
 */
const REAL_DATA_KEYS: Record<string, string> = {
  order_number: 'orderNumber',
  woocommerce_order_date: 'orderDate',
  customer_info: 'customerName', // Récupère tout le customer_info via fallback
  product_table: 'products',
  company_info: 'companyName', // Récupère tout le company_info via fallback
};

/**
 * Vérifie si un type d'élément est un RealDataElement
 */
export function isRealDataElementType(type: string): boolean {
  return REAL_DATA_ELEMENT_TYPES.includes(type);
}

/**
 * Configure un élément pour qu'il soit un RealDataElement
 * 
 * Ajoute les propriétés nécessaires:
 * - isRealDataElement: true
 * - defaultTestValue: valeur fictive par défaut
 * - realDataKey: clé de récupération des données réelles
 */
export function configureRealDataElement<T extends Element>(
  element: T,
  type: string = element.type
): T {
  if (!isRealDataElementType(type)) {
    return element;
  }

  // ✅ Better approach: create new object keeping all properties
  const configured = { ...element } as T;
  
  // Add RealData properties
  (configured as any).isRealDataElement = true;
  (configured as any).defaultTestValue = DEFAULT_TEST_VALUES[type];
  (configured as any).realDataKey = REAL_DATA_KEYS[type];
  
  return configured;
}

/**
 * Crée un nouvel élément RealData avec toutes les bonnes propriétés
 */
export function createRealDataElement(
  type: string,
  baseElement: Partial<BaseElement>
): Element {
  const element: Element = {
    id: baseElement.id || `element-${Date.now()}`,
    type,
    x: baseElement.x || 0,
    y: baseElement.y || 0,
    width: baseElement.width || 200,
    height: baseElement.height || 50,
    visible: baseElement.visible !== false,
    locked: baseElement.locked ?? false,
    createdAt: baseElement.createdAt || new Date(),
    updatedAt: baseElement.updatedAt || new Date(),
  };

  return configureRealDataElement(element, type);
}

/**
 * Configure tous les éléments RealData dans une liste
 */
export function configureRealDataElements(elements: Element[]): Element[] {
  return elements.map(el => {
    if (isRealDataElementType(el.type) && !el.isRealDataElement) {
      return configureRealDataElement(el);
    }
    return el;
  });
}

/**
 * Obtient le type d'élément pour une icône/label dans l'UI
 */
export function getRealDataElementLabel(type: string): string {
  const labels: Record<string, string> = {
    order_number: '📦 Numéro de commande',
    woocommerce_order_date: '📅 Date de commande',
    customer_info: '👤 Informations client',
    product_table: '📊 Tableau des produits',
    company_info: '🏢 Informations société',
  };
  
  return labels[type] || type;
}

/**
 * Filtre les éléments RealData avec données réelles (aperçu)
 * vs éléments de l'éditeur
 */
export function filterRealDataElements(elements: Element[]): Element[] {
  return elements.filter(el => isRealDataElementType(el.type));
}

/**
 * Obtient la description pour afficher dans l'UI
 */
export function getRealDataElementDescription(type: string): string {
  const descriptions: Record<string, string> = {
    order_number: 'Récupère le numéro de commande depuis WooCommerce',
    woocommerce_order_date: 'Récupère la date de commande depuis WooCommerce',
    customer_info: 'Affiche les infos client (nom, email, adresse, etc.)',
    product_table: 'Affiche le tableau des produits commandés',
    company_info: 'EXCEPTION: Affiche toujours les vraies infos société',
  };
  
  return descriptions[type] || '';
}
