/**
 * Configuration centralisée du PDF Builder Pro
 * Définit tous les paramètres par défaut, validations et constantes
 */

// === PARAMÈTRES PAR DÉFAUT DES ÉLÉMENTS ===

export const ELEMENT_DEFAULT_PROPERTIES = {
  // Propriétés communes à tous les éléments
  common: {
    // Position et dimensions
    x: 50,
    y: 50,
    width: 100,
    height: 50,

    // Apparence de base
    backgroundColor: 'transparent',
    borderColor: 'transparent',
    borderWidth: 0,
    borderStyle: 'solid',
    borderRadius: 0,

    // Typographie
    color: '#1e293b',
    fontFamily: 'Inter, sans-serif',
    fontSize: 14,
    fontWeight: 'normal',
    fontStyle: 'normal',
    textAlign: 'left',
    textDecoration: 'none',

    // Contenu
    text: 'Texte',

    // Propriétés avancées
    opacity: 100,
    rotation: 0,
    scale: 100,
    visible: true,

    // Images et médias
    src: '',
    alt: '',
    objectFit: 'cover',
    brightness: 100,
    contrast: 100,
    saturate: 100,

    // Effets
    shadow: false,
    shadowColor: '#000000',
    shadowOffsetX: 2,
    shadowOffsetY: 2,

    // Propriétés spécifiques aux tableaux
    showHeaders: true,
    showBorders: true,
    dataSource: 'order_items',
    showSubtotal: false,
    showShipping: true,
    showTaxes: true,
    showDiscount: false,
    showTotal: false,

    // Propriétés de barre de progression
    progressColor: '#3b82f6',
    progressValue: 75,

    // Propriétés de code et lignes
    lineColor: '#64748b',
    lineWidth: 2,

    // Propriétés de document
    documentType: 'invoice',
    imageUrl: '',

    // Propriétés de mise en page
    spacing: 8,
    layout: 'vertical',
    alignment: 'left',
    fit: 'contain'
  },

  // Ajustements spécifiques par type d'élément
  adjustments: {
    'text': {
      width: 150,
      height: 30
    },
    'rectangle': {
      width: 150,
      height: 80,
      backgroundColor: '#e5e7eb'
    },
    'image': {
      width: 200,
      height: 150
    },
    'table': {
      width: 400,
      height: 200
    },
    'watermark': {
      width: 300,
      height: 200,
      opacity: 10,
      text: 'CONFIDENTIEL'
    },
    'progress-bar': {
      width: 200,
      height: 20
    },
    'barcode': {
      width: 150,
      height: 60
    }
  }
};

// === VALIDATIONS DES PROPRIÉTÉS ===

export const PROPERTY_VALIDATIONS = {
  // Propriétés numériques avec leurs contraintes
  numeric: {
    x: { min: -1000, max: 5000, default: 50 },
    y: { min: -1000, max: 5000, default: 50 },
    width: { min: 1, max: 2000, default: 100 },
    height: { min: 1, max: 2000, default: 50 },
    fontSize: { min: 6, max: 200, default: 14 },
    opacity: { min: 0, max: 100, default: 100 },
    rotation: { min: -360, max: 360, default: 0 },
    scale: { min: 10, max: 500, default: 100 },
    borderWidth: { min: 0, max: 50, default: 0 },
    borderRadius: { min: 0, max: 500, default: 0 },
    shadowOffsetX: { min: -100, max: 100, default: 2 },
    shadowOffsetY: { min: -100, max: 100, default: 2 },
    brightness: { min: 0, max: 200, default: 100 },
    contrast: { min: 0, max: 200, default: 100 },
    saturate: { min: 0, max: 200, default: 100 },
    progressValue: { min: 0, max: 100, default: 75 },
    lineWidth: { min: 1, max: 10, default: 2 },
    spacing: { min: 0, max: 100, default: 8 },
    lineHeight: { min: 0.5, max: 3, default: 1.2 },
    letterSpacing: { min: -10, max: 10, default: 0 },
    zIndex: { min: -1000, max: 1000, default: 0 },
    padding: { min: 0, max: 100, default: 0 }
  },

  // Propriétés de couleur
  colors: [
    'color', 'backgroundColor', 'borderColor', 'shadowColor',
    'progressColor', 'lineColor'
  ],

  // Propriétés énumérées avec leurs valeurs valides
  enums: {
    fontWeight: ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
    fontStyle: ['normal', 'italic', 'oblique'],
    textAlign: ['left', 'center', 'right', 'justify'],
    textDecoration: ['none', 'underline', 'overline', 'line-through'],
    textTransform: ['none', 'capitalize', 'uppercase', 'lowercase'],
    borderStyle: ['solid', 'dashed', 'dotted', 'double', 'none'],
    objectFit: ['fill', 'contain', 'cover', 'none', 'scale-down'],
    dataSource: ['order_items', 'cart_items', 'custom'],
    layout: ['vertical', 'horizontal', 'grid'],
    alignment: ['left', 'center', 'right', 'justify'],
    fit: ['contain', 'cover', 'fill', 'none'],
    documentType: ['invoice', 'quote', 'receipt', 'order']
  },

  // Propriétés booléennes
  booleans: [
    'visible', 'shadow', 'showHeaders', 'showBorders',
    'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'
  ]
};

// === CONSTANTES DE CANVAS ===

export const CANVAS_CONSTANTS = {
  defaultWidth: 595,  // A4 width in points
  defaultHeight: 842, // A4 height in points
  minWidth: 100,
  minHeight: 100,
  maxWidth: 2000,
  maxHeight: 2000,
  defaultUnit: 'mm',
  supportedUnits: ['mm', 'cm', 'in', 'pt', 'px']
};

// === CONSTANTES DE PERFORMANCE ===

export const PERFORMANCE_CONSTANTS = {
  maxElements: 1000,
  maxHistorySteps: 50,
  autoSaveInterval: 30000, // 30 secondes
  debounceDelay: 150,
  throttleDelay: 16
};

// === CONSTANTES D'INTERFACE UTILISATEUR ===

export const UI_CONSTANTS = {
  zoom: {
    default: 100,
    min: 10,
    max: 500,
    step: 25
  },
  grid: {
    defaultSize: 10,
    minSize: 5,
    maxSize: 100,
    defaultOpacity: 30
  },
  handles: {
    defaultSize: 8,
    minSize: 6,
    maxSize: 16
  },
  margins: {
    default: 10,
    min: 0,
    max: 100
  }
};

// === FONCTIONS UTILITAIRES ===

/**
 * Obtenir les propriétés par défaut pour un type d'élément
 */
export const getDefaultProperties = (elementType = 'text') => {
  const baseProps = { ...ELEMENT_DEFAULT_PROPERTIES.common };
  const adjustments = ELEMENT_DEFAULT_PROPERTIES.adjustments[elementType] || {};

  return { ...baseProps, ...adjustments };
};

/**
 * Valider une propriété selon son type
 */
export const validateProperty = (propertyName, value) => {
  // Validation numérique
  if (PROPERTY_VALIDATIONS.numeric[propertyName]) {
    const constraints = PROPERTY_VALIDATIONS.numeric[propertyName];
    if (typeof value === 'number') {
      return value >= constraints.min && value <= constraints.max;
    }
    return false;
  }

  // Validation couleur
  if (PROPERTY_VALIDATIONS.colors.includes(propertyName)) {
    if (value === 'transparent') return true;
    return /^#[0-9A-Fa-f]{3,6}$/i.test(value) ||
           ['black', 'white', 'red', 'green', 'blue', 'gray', 'grey'].includes(value.toLowerCase());
  }

  // Validation énumération
  if (PROPERTY_VALIDATIONS.enums[propertyName]) {
    return PROPERTY_VALIDATIONS.enums[propertyName].includes(value);
  }

  // Validation booléenne
  if (PROPERTY_VALIDATIONS.booleans.includes(propertyName)) {
    return typeof value === 'boolean';
  }

  // Validation chaîne de caractères
  if (propertyName === 'text' || propertyName === 'src' || propertyName === 'alt' || propertyName === 'fontFamily') {
    return typeof value === 'string';
  }

  // Par défaut, accepter la valeur
  return true;
};

/**
 * Corriger une propriété invalide
 */
export const fixInvalidProperty = (propertyName, invalidValue) => {
  // Valeurs numériques par défaut
  if (PROPERTY_VALIDATIONS.numeric[propertyName]) {
    return PROPERTY_VALIDATIONS.numeric[propertyName].default;
  }

  // Valeurs énumérées par défaut
  if (PROPERTY_VALIDATIONS.enums[propertyName]) {
    return PROPERTY_VALIDATIONS.enums[propertyName][0];
  }

  // Valeurs booléennes par défaut
  if (PROPERTY_VALIDATIONS.booleans.includes(propertyName)) {
    return false;
  }

  // Valeurs de couleur par défaut
  if (PROPERTY_VALIDATIONS.colors.includes(propertyName)) {
    return propertyName === 'color' ? '#1e293b' : 'transparent';
  }

  // Valeurs de texte par défaut
  if (propertyName === 'text') {
    return 'Texte';
  }

  if (propertyName === 'fontFamily') {
    return 'Inter, sans-serif';
  }

  // Retourner la valeur originale si aucune correction n'est définie
  return invalidValue;
};