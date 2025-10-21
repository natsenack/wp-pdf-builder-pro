import { SampleDataProvider } from '../data/SampleDataProvider';

/**
 * Mode Canvas : Aperçu avec données d'exemple
 * Utilisé dans l'éditeur pour prévisualiser les éléments avec des données fictives
 */
export class CanvasMode {
  /**
   * Charge les données d'aperçu pour le mode Canvas
   * @param {Array} elements - Liste des éléments du canvas
   * @param {number|null} orderId - ID de commande (ignoré en mode Canvas)
   * @param {Object} templateData - Données du template
   * @returns {Promise<Object>} Données d'aperçu
   */
  static async loadData(elements, orderId = null, templateData = {}) {
    const dataProvider = new SampleDataProvider();

    // Collecter tous les types d'éléments présents
    const elementTypes = [...new Set(elements.map(el => el.type))];

    // Générer des données d'exemple pour chaque type d'élément
    const previewData = {};

    for (const elementType of elementTypes) {
      const elementsOfType = elements.filter(el => el.type === elementType);

      // Pour chaque élément du type, générer des données spécifiques
      for (const element of elementsOfType) {
        const elementKey = `${element.type}_${element.id}`;
        previewData[elementKey] = await dataProvider.getElementData(
          element.type,
          element.properties || element
        );
      }
    }

    // Ajouter des variables globales d'exemple
    previewData.global = {
      order_number: 'CMD-2025-001',
      order_date: '19/10/2025',
      order_total: '149,99 €',
      customer_name: 'Jean Dupont',
      customer_email: 'jean.dupont@email.com',
      customer_phone: '+33 6 12 34 56 78',
      company_name: 'Ma Société SARL',
      company_address: '123 Rue de la Paix\n75001 Paris\nFrance',
      company_phone: '+33 1 42 86 75 30',
      company_email: 'contact@masociete.com'
    };

    return previewData;
  }

  /**
   * Valide si le mode Canvas peut être utilisé
   * @param {Array} elements - Liste des éléments
   * @returns {boolean} True si valide
   */
  static validate(elements) {
    // Le mode Canvas accepte tous les éléments
    return elements && Array.isArray(elements);
  }

  /**
   * Retourne les capacités du mode Canvas
   * @returns {Object} Capacités disponibles
   */
  static getCapabilities() {
    return {
      supportsRealData: false,
      supportsDynamicVariables: true,
      supportsAllElements: true,
      maxElements: 50,
      features: [
        'données_exemple',
        'variables_globales',
        'rendu_temps_reel',
        'guides_marge'
      ]
    };
  }
}

// Fournir un composant React par défaut qui utilise les helpers statiques
import React from 'react';

function CanvasModeComponent(props) {
  // Composant léger qui délègue le rendu aux renderers existants via le context
  // Ici on suppose que le PreviewContext / renderers prendront en charge l'affichage
  return (
    <div className="canvas-mode-component">
      {/* Le vrai rendu est géré par le système de preview via loadData */}
      <div>Chargement de l'aperçu Canvas...</div>
    </div>
  );
}

export default CanvasModeComponent;