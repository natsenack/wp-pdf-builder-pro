import { RealDataProvider } from '../data/RealDataProvider';

/**
 * Mode Metabox : Aperçu avec données réelles de commande WooCommerce
 * Utilisé dans la metabox des commandes pour afficher l'aperçu avec les vraies données
 */
export class MetaboxMode {
  /**
   * Charge les données d'aperçu pour le mode Metabox
   * @param {Array} elements - Liste des éléments du template
   * @param {number} orderId - ID de la commande WooCommerce
   * @param {Object} templateData - Données du template
   * @returns {Promise<Object>} Données d'aperçu
   */
  static async loadData(elements, orderId, templateData = {}) {
    if (!orderId) {
      throw new Error('ID de commande requis pour le mode Metabox');
    }

    const dataProvider = new RealDataProvider();

    try {
      // Charger les données de la commande
      const orderData = await dataProvider.loadOrderData(orderId);

      // Collecter tous les types d'éléments présents
      const elementTypes = [...new Set(elements.map(el => el.type))];

      // Générer des données réelles pour chaque type d'élément
      const previewData = {};

      for (const elementType of elementTypes) {
        const elementsOfType = elements.filter(el => el.type === elementType);

        // Pour chaque élément du type, générer des données spécifiques
        for (const element of elementsOfType) {
          const elementKey = `${element.type}_${element.id}`;
          previewData[elementKey] = await dataProvider.getElementData(
            element.type,
            element.properties || element,
            orderData
          );
        }
      }

      // Variables globales de la commande
      previewData.global = orderData;

      return previewData;

    } catch (error) {
      console.error('Erreur lors du chargement des données de commande:', error);
      throw new Error(`Impossible de charger les données de la commande ${orderId}: ${error.message}`);
    }
  }

  /**
   * Valide si le mode Metabox peut être utilisé
   * @param {Array} elements - Liste des éléments
   * @param {number} orderId - ID de la commande
   * @returns {boolean} True si valide
   */
  static validate(elements, orderId) {
    return elements &&
           Array.isArray(elements) &&
           orderId &&
           typeof orderId === 'number' &&
           orderId > 0;
  }

  /**
   * Retourne les capacités du mode Metabox
   * @returns {Object} Capacités disponibles
   */
  static getCapabilities() {
    return {
      supportsRealData: true,
      supportsDynamicVariables: true,
      supportsAllElements: true,
      requiresOrderId: true,
      maxElements: 100,
      features: [
        'données_réelles',
        'variables_dynamiques',
        'validation_commande',
        'sécurité_wc'
      ]
    };
  }

  /**
   * Vérifie si la commande existe et est accessible
   * @param {number} orderId - ID de la commande
   * @returns {Promise<boolean>} True si accessible
   */
  static async checkOrderAccess(orderId) {
    try {
      const dataProvider = new RealDataProvider();
      await dataProvider.validateOrderAccess(orderId);
      return true;
    } catch (error) {
      console.warn(`Accès refusé à la commande ${orderId}:`, error.message);
      return false;
    }
  }
}