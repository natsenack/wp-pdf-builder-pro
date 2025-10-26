/**
 * Bibliothèque d'éléments PDF Builder Pro - Version Vanilla JS
 * Définit tous les éléments disponibles dans la sidebar de l'éditeur
 * Organisés par catégories avec labels, icônes et descriptions
 */

export const ELEMENT_LIBRARY = {
  // === ÉLÉMENTS SPÉCIAUX (WooCommerce) ===
  special: [
    {
      type: 'product_table',
      label: 'Tableau Produits',
      icon: '📋',
      description: 'Tableau des produits commandés avec quantités et prix',
      category: 'special',
      defaultProps: {
        x: 50,
        y: 100,
        width: 500,
        height: 200,
        showHeaders: true,
        showBorders: true,
        fontSize: 12,
        backgroundColor: '#ffffff',
        borderColor: '#e5e7eb',
        borderWidth: 1
      }
    },
    {
      type: 'customer_info',
      label: 'Fiche Client',
      icon: '👤',
      description: 'Informations détaillées du client (nom, adresse, email)',
      category: 'special',
      defaultProps: {
        x: 50,
        y: 50,
        width: 250,
        height: 120,
        showHeaders: true,
        showBorders: false,
        fontSize: 12,
        backgroundColor: 'transparent',
        layout: 'vertical'
      }
    },
    {
      type: 'company_info',
      label: 'Informations Entreprise',
      icon: '[D]',
      description: 'Nom, adresse, contact et TVA de l\'entreprise',
      category: 'special',
      defaultProps: {
        x: 320,
        y: 50,
        width: 250,
        height: 120,
        showHeaders: true,
        showBorders: false,
        fontSize: 12,
        backgroundColor: 'transparent',
        layout: 'vertical'
      }
    },
    {
      type: 'company_logo',
      label: 'Logo Entreprise',
      icon: '�',
      description: 'Logo et identité visuelle de l\'entreprise',
      category: 'special',
      defaultProps: {
        x: 50,
        y: 200,
        width: 150,
        height: 80,
        fit: 'contain',
        alignment: 'left'
      }
    },
    {
      type: 'order_number',
      label: 'Numéro de Commande',
      icon: '🔢',
      description: 'Référence de commande avec date',
      category: 'special',
      defaultProps: {
        x: 450,
        y: 20,
        width: 100,
        height: 30,
        fontSize: 14,
        fontFamily: 'Arial',
        textAlign: 'right',
        backgroundColor: 'transparent'
      }
    },
    {
      type: 'dynamic-text',
      label: 'Texte Dynamique',
      icon: '📝',
      description: 'Texte avec variables dynamiques',
      category: 'special',
      defaultProps: {
        x: 50,
        y: 320,
        width: 200,
        height: 40,
        template: 'Commande #{order_number}',
        fontSize: 14,
        fontFamily: 'Arial',
        backgroundColor: 'transparent'
      }
    },
    {
      type: 'mentions',
      label: 'Mentions légales',
      icon: '📄',
      description: 'Informations légales (email, SIRET, téléphone, etc.)',
      category: 'special',
      defaultProps: {
        x: 50,
        y: 380,
        width: 500,
        height: 60,
        fontSize: 10,
        fontFamily: 'Arial',
        textAlign: 'left',
        backgroundColor: 'transparent'
      }
    }
  ]
};

// Fonction pour obtenir tous les éléments organisés par catégories
export const getAllElements = () => {
  return ELEMENT_LIBRARY;
};

// Fonction pour obtenir les éléments d'une catégorie spécifique
export const getElementsByCategory = (category) => {
  return ELEMENT_LIBRARY[category] || [];
};

// Fonction pour obtenir un élément par son type
export const getElementByType = (type) => {
  for (const category in ELEMENT_LIBRARY) {
    const element = ELEMENT_LIBRARY[category].find(el => el.type === type);
    if (element) return element;
  }
  return null;
};

// Fonction pour obtenir tous les éléments à plat (pour les listes)
export const getAllElementsFlat = () => {
  const allElements = [];
  for (const category in ELEMENT_LIBRARY) {
    allElements.push(...ELEMENT_LIBRARY[category]);
  }
  return allElements;
};

// Fonction pour rechercher des éléments
export const searchElements = (query) => {
  const allElements = getAllElementsFlat();
  const lowerQuery = query.toLowerCase();
  return allElements.filter(element =>
    element.label.toLowerCase().includes(lowerQuery) ||
    element.description.toLowerCase().includes(lowerQuery) ||
    element.type.toLowerCase().includes(lowerQuery)
  );
};

// Export par défaut
export default ELEMENT_LIBRARY;