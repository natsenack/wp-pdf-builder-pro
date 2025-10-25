// Fonctions helper utilitaires pour les composants PropertiesPanel

/**
 * Analyse sécurisée d'un nombre flottant
 * @param {any} value - Valeur à analyser
 * @param {number} defaultValue - Valeur par défaut si l'analyse échoue
 * @returns {number} Le nombre analysé ou la valeur par défaut
 */
export const safeParseFloat = (value, defaultValue = 0) => {
  if (value === null || value === undefined || value === '') return defaultValue;
  const parsed = parseFloat(value);
  return isNaN(parsed) ? defaultValue : parsed;
};

/**
 * Analyse sécurisée d'un nombre entier
 * @param {any} value - Valeur à analyser
 * @param {number} defaultValue - Valeur par défaut si l'analyse échoue
 * @returns {number} Le nombre analysé ou la valeur par défaut
 */
export const safeParseInt = (value, defaultValue = 0) => {
  if (value === null || value === undefined || value === '') return defaultValue;
  const parsed = parseInt(value, 10);
  return isNaN(parsed) ? defaultValue : parsed;
};

/**
 * Détermine si une section doit être affichée pour un type d'élément
 * @param {string} sectionName - Nom de la section
 * @param {string} elementType - Type d'élément
 * @returns {boolean} True si la section doit être affichée
 */
export const shouldShowSection = (sectionName, elementType) => {
  // Sections à cacher selon le type d'élément
  const hiddenSections = {
    // Pour les tableaux : pas de typographie (trop complexe), pas de couleurs/bordures (géré par TableAppearanceSection)
    product_table: ['typography', 'colors', 'borders', 'font']
  };

  const elementHiddenSections = hiddenSections[elementType] || [];
  return !elementHiddenSections.includes(sectionName);
};

/**
 * Obtient l'ordre intelligent des propriétés selon le type d'élément
 * @param {string} elementType - Type d'élément
 * @param {string} tab - Onglet (appearance, layout, etc.)
 * @returns {string[]} Ordre des sections pour cet élément et onglet
 */
export const getSmartPropertyOrder = (elementType, tab) => {
  const orders = {
    // Ordre pour l'onglet Apparence
    appearance: {
      // Éléments texte : couleur et police en premier
      text: ['colors', 'typography', 'borders', 'effects'],
      'dynamic-text': ['colors', 'typography', 'borders', 'effects'],
      'layout-header': ['colors', 'typography', 'borders', 'effects'],
      'layout-footer': ['colors', 'typography', 'borders', 'effects'],
      'layout-section': ['colors', 'typography', 'borders', 'effects'],

      // Éléments image : couleur de fond et bordures en premier
      logo: ['colors', 'typography', 'borders', 'effects'],
      company_logo: ['colors', 'typography', 'borders', 'effects'],

      // Éléments tableau : couleurs spéciales en premier
      product_table: ['colors', 'typography', 'borders', 'effects'],

      // Éléments par défaut
      default: ['colors', 'typography', 'borders', 'effects']
    },

    // Ordre pour l'onglet Mise en page
    layout: {
      text: ['position', 'dimensions', 'transform', 'layers'],
      'dynamic-text': ['position', 'dimensions', 'transform', 'layers'],
      logo: ['position', 'dimensions', 'transform', 'layers'],
      company_logo: ['position', 'dimensions', 'transform', 'layers'],
      product_table: ['position', 'dimensions', 'transform', 'layers'],
      default: ['position', 'dimensions', 'transform', 'layers']
    },

    // Ordre pour l'onglet Contenu
    content: {
      text: ['text', 'variables'],
      'dynamic-text': ['dynamic_text', 'variables'],
      logo: ['image'],
      company_logo: ['company_fields'],
      customer_info: ['customer_fields'],
      company_info: ['company_fields'],
      order_number: ['order_number'],
      product_table: ['table'],
      default: []
    },

    // Ordre pour l'onglet Effets
    effects: {
      text: ['opacity', 'shadows', 'filters'],
      'dynamic-text': ['opacity', 'shadows', 'filters'],
      logo: ['opacity', 'shadows'],
      company_logo: ['opacity', 'shadows'],
      product_table: ['opacity', 'shadows', 'filters'],
      default: ['opacity', 'shadows']
    }
  };

  return (orders[tab] && orders[tab][elementType]) || (orders[tab] && orders[tab].default) || [];
};