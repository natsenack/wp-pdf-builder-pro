// Système de gestion des propriétés d'éléments
// Définit les restrictions et validations pour chaque type d'élément

export const ELEMENT_PROPERTY_RESTRICTIONS = {
  // Éléments spéciaux - pas de contrôle de fond
  special: {
    backgroundColor: {
      disabled: true,
      reason: 'Les éléments spéciaux n\'ont pas de fond contrôlable'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },

  // Éléments de mise en page - contrôle complet
  layout: {
    backgroundColor: {
      disabled: false,
      default: '#f8fafc'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },

  // Éléments de texte - contrôle complet
  text: {
    backgroundColor: {
      disabled: false,
      default: 'transparent'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },

  // Éléments graphiques - contrôle complet
  shape: {
    backgroundColor: {
      disabled: false,
      default: '#e5e7eb'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },

  // Éléments médias - contrôle limité
  media: {
    backgroundColor: {
      disabled: false,
      default: '#f3f4f6'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  },

  // Éléments dynamiques - contrôle complet
  dynamic: {
    backgroundColor: {
      disabled: false,
      default: 'transparent'
    },
    borderColor: {
      disabled: false
    },
    borderWidth: {
      disabled: false
    }
  }
};

// Mapping des types d'éléments vers leurs catégories
export const ELEMENT_TYPE_MAPPING = {
  // Spéciaux
  'product_table': 'special',
  'customer_info': 'special',
  'company_logo': 'special',
  'company_info': 'special',
  'order_number': 'special',
  'document_type': 'special',
  'progress-bar': 'special',

  // Mise en page
  'layout-header': 'layout',
  'layout-footer': 'layout',
  'layout-sidebar': 'layout',
  'layout-section': 'layout',
  'layout-container': 'layout',
  'layout-section-divider': 'layout',
  'layout-spacer': 'layout',
  'layout-two-column': 'layout',
  'layout-three-column': 'layout',

  // Texte
  'text': 'text',
  'dynamic-text': 'text',
  'conditional-text': 'text',
  'counter': 'text',
  'date-dynamic': 'text',
  'currency': 'text',
  'formula': 'text',

  // Formes
  'rectangle': 'shape',
  'line': 'shape',
  'shape-rectangle': 'shape',
  'shape-circle': 'shape',
  'shape-line': 'shape',
  'shape-arrow': 'shape',
  'shape-triangle': 'shape',
  'shape-star': 'shape',
  'divider': 'shape',

  // Médias
  'image': 'media',
  'image-upload': 'media',
  'logo': 'media',
  'barcode': 'media',
  'qrcode': 'media',
  'qrcode-dynamic': 'media',
  'icon': 'media',

  // Dynamiques
  'table-dynamic': 'dynamic',
  'gradient-box': 'dynamic',
  'shadow-box': 'dynamic',
  'rounded-box': 'dynamic',
  'border-box': 'dynamic',
  'background-pattern': 'dynamic',
  'watermark': 'dynamic',

  // Factures (mélange de catégories)
  'invoice-header': 'layout',
  'invoice-address-block': 'layout',
  'invoice-info-block': 'layout',
  'invoice-products-table': 'special',
  'invoice-totals-block': 'layout',
  'invoice-payment-terms': 'layout',
  'invoice-legal-footer': 'layout',
  'invoice-signature-block': 'layout'
};

// Fonction pour vérifier si une propriété est autorisée pour un type d'élément
export const isPropertyAllowed = (elementType, propertyName) => {
  const category = ELEMENT_TYPE_MAPPING[elementType] || 'text'; // défaut texte
  const restrictions = ELEMENT_PROPERTY_RESTRICTIONS[category];

  if (!restrictions || !restrictions[propertyName]) {
    return true; // propriété autorisée par défaut
  }

  return !restrictions[propertyName].disabled;
};

// Fonction pour obtenir la valeur par défaut d'une propriété
export const getPropertyDefault = (elementType, propertyName) => {
  const category = ELEMENT_TYPE_MAPPING[elementType] || 'text';
  const restrictions = ELEMENT_PROPERTY_RESTRICTIONS[category];

  if (restrictions && restrictions[propertyName] && restrictions[propertyName].default !== undefined) {
    return restrictions[propertyName].default;
  }

  return null; // pas de valeur par défaut spécifique
};

// Fonction pour valider une propriété
export const validateProperty = (elementType, propertyName, value) => {
  if (!isPropertyAllowed(elementType, propertyName)) {
    return {
      valid: false,
      reason: ELEMENT_PROPERTY_RESTRICTIONS[ELEMENT_TYPE_MAPPING[elementType] || 'text'][propertyName]?.reason || 'Propriété non autorisée'
    };
  }

  // Validations spécifiques selon le type de propriété
  switch (propertyName) {
    case 'backgroundColor':
      if (typeof value !== 'string') {
        return { valid: false, reason: 'La couleur doit être une chaîne' };
      }
      // Pour les éléments spéciaux, forcer transparent
      if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && value !== 'transparent') {
        return { valid: false, reason: 'Les éléments spéciaux doivent avoir un fond transparent' };
      }
      break;

    case 'borderWidth':
      if (typeof value !== 'number' || value < 0) {
        return { valid: false, reason: 'La largeur de bordure doit être un nombre positif' };
      }
      break;

    case 'fontSize':
      if (typeof value !== 'number' || value <= 0) {
        return { valid: false, reason: 'La taille de police doit être un nombre positif' };
      }
      break;

    case 'width':
    case 'height':
      if (typeof value !== 'number' || value <= 0) {
        return { valid: false, reason: 'Les dimensions doivent être positives' };
      }
      break;

    default:
      break;
  }

  return { valid: true };
};

// Fonction pour corriger automatiquement une propriété invalide
export const fixInvalidProperty = (elementType, propertyName, invalidValue) => {
  // Pour les éléments spéciaux, forcer backgroundColor à transparent
  if (propertyName === 'backgroundColor' && ELEMENT_TYPE_MAPPING[elementType] === 'special') {
    return 'transparent';
  }

  // Valeurs par défaut pour les propriétés numériques
  const numericDefaults = {
    borderWidth: 0,
    fontSize: 14,
    width: 100,
    height: 50,
    padding: 8
  };

  if (numericDefaults[propertyName] !== undefined) {
    return numericDefaults[propertyName];
  }

  // Valeurs par défaut pour les chaînes
  const stringDefaults = {
    backgroundColor: 'transparent',
    borderColor: 'transparent',
    color: '#000000',
    fontFamily: 'Arial, sans-serif'
  };

  return stringDefaults[propertyName] || invalidValue;
};