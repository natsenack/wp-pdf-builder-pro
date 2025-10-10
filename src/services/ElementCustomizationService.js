/**
 * Service de personnalisation des éléments
 * Fournit des utilitaires pour la gestion des propriétés d'éléments
 */
export class ElementCustomizationService {
  constructor() {
    this.propertyValidators = new Map();
    this.propertyPresets = new Map();
    this.propertyGroups = new Map();
    this.initDefaults();
  }

  /**
   * Initialise les validateurs, presets et groupes par défaut
   */
  initDefaults() {
    // Validateurs de propriétés
    this.propertyValidators.set('numeric', (value) => parseFloat(value) || 0);
    this.propertyValidators.set('positiveNumeric', (value) => Math.max(0, parseFloat(value) || 0));
    this.propertyValidators.set('percentage', (value) => Math.max(0, Math.min(100, parseFloat(value) || 100)));
    this.propertyValidators.set('angle', (value) => ((parseFloat(value) || 0) % 360 + 360) % 360);
    this.propertyValidators.set('color', (value) => this.validateColor(value));
    this.propertyValidators.set('fontSize', (value) => Math.max(8, Math.min(72, parseInt(value) || 14)));
    this.propertyValidators.set('borderWidth', (value) => Math.max(0, Math.min(20, parseInt(value) || 0)));
    this.propertyValidators.set('borderStyle', (value) => {
      const validStyles = ['solid', 'dashed', 'dotted', 'double'];
      return validStyles.includes(value) ? value : 'solid';
    });
    this.propertyValidators.set('borderRadius', (value) => Math.max(0, Math.min(100, parseInt(value) || 0)));

    // Presets de couleurs
    this.propertyPresets.set('colors', {
      slate: ['#f8fafc', '#f1f5f9', '#e2e8f0', '#cbd5e1', '#94a3b8', '#64748b', '#475569', '#334155', '#1e293b'],
      blue: ['#eff6ff', '#dbeafe', '#bfdbfe', '#93c5fd', '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8', '#1e40af'],
      green: ['#f0fdf4', '#dcfce7', '#bbf7d0', '#86efac', '#4ade80', '#22c55e', '#16a34a', '#15803d', '#166534'],
      red: ['#fef2f2', '#fee2e2', '#fecaca', '#fca5a5', '#f87171', '#ef4444', '#dc2626', '#b91c1c', '#991b1b']
    });

    // Groupes de propriétés
    this.propertyGroups.set('position', ['x', 'y']);
    this.propertyGroups.set('dimensions', ['width', 'height']);
    this.propertyGroups.set('typography', ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'color', 'textAlign']);
    this.propertyGroups.set('appearance', ['backgroundColor', 'borderColor', 'borderWidth', 'borderRadius']);
    this.propertyGroups.set('effects', ['opacity', 'shadow', 'brightness', 'contrast', 'saturate']);
    this.propertyGroups.set('transform', ['rotation', 'scale']);
  }

  /**
   * Valide une valeur de couleur
   */
  validateColor(value) {
    if (!value) return '#000000';

    // Vérifier si c'est un code hex valide
    if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
      return value;
    }

    // Vérifier si c'est un nom de couleur CSS valide
    const tempElement = document.createElement('div');
    tempElement.style.color = value;
    return tempElement.style.color || '#000000';
  }

  /**
   * Valide une propriété selon son type
   */
  validateProperty(property, value) {
    // Pour les propriétés boolean, retourner la valeur telle quelle
    if (typeof value === 'boolean') {
      return value;
    }

    // Pour les propriétés de colonnes (tableaux), retourner la valeur telle quelle
    if (property.startsWith('columns.')) {
      return value;
    }

    // Chercher un validateur pour cette propriété
    const validator = this.propertyValidators.get(property);
    if (validator) {
      return validator(value);
    }

    // Si pas de validateur spécifique, retourner la valeur telle quelle
    return value;
  }

  /**
   * Obtient les presets pour une catégorie
   */
  getPresets(category) {
    return this.propertyPresets.get(category) || {};
  }

  /**
   * Obtient les propriétés d'un groupe
   */
  getPropertiesInGroup(groupName) {
    return this.propertyGroups.get(groupName) || [];
  }

  /**
   * Applique un preset à un ensemble de propriétés
   */
  applyPreset(presetName, currentProperties) {
    const presets = {
      // Presets de style de texte
      'text-title': {
        fontSize: 24,
        fontWeight: 'bold',
        textAlign: 'center',
        color: '#1e293b'
      },
      'text-subtitle': {
        fontSize: 18,
        fontWeight: 'bold',
        textAlign: 'left',
        color: '#334155'
      },
      'text-body': {
        fontSize: 14,
        fontWeight: 'normal',
        textAlign: 'left',
        color: '#475569'
      },
      'text-caption': {
        fontSize: 12,
        fontWeight: 'normal',
        textAlign: 'left',
        color: '#64748b'
      },

      // Presets de formes
      'shape-rounded': {
        borderRadius: 8,
        borderWidth: 1,
        borderColor: '#e2e8f0'
      },
      'shape-circle': {
        borderRadius: 50,
        borderWidth: 1,
        borderColor: '#e2e8f0'
      },
      'shape-square': {
        borderRadius: 0,
        borderWidth: 1,
        borderColor: '#e2e8f0'
      },

      // Presets d'effets
      'effect-shadow-soft': {
        shadow: true,
        shadowColor: '#000000',
        shadowOffsetX: 1,
        shadowOffsetY: 1,
        opacity: 90
      },
      'effect-shadow-strong': {
        shadow: true,
        shadowColor: '#000000',
        shadowOffsetX: 3,
        shadowOffsetY: 3,
        opacity: 85
      },
      'effect-glow': {
        shadow: true,
        shadowColor: '#2563eb',
        shadowOffsetX: 0,
        shadowOffsetY: 0,
        opacity: 95
      },

      // Presets de couleurs
      'color-primary': {
        backgroundColor: '#2563eb',
        color: '#ffffff'
      },
      'color-secondary': {
        backgroundColor: '#64748b',
        color: '#ffffff'
      },
      'color-success': {
        backgroundColor: '#16a34a',
        color: '#ffffff'
      },
      'color-warning': {
        backgroundColor: '#ca8a04',
        color: '#ffffff'
      },
      'color-error': {
        backgroundColor: '#dc2626',
        color: '#ffffff'
      }
    };

    return presets[presetName] || {};
  }

  /**
   * Réinitialise les propriétés aux valeurs par défaut
   */
  getDefaultProperties(elementType = 'text') {
    // Propriétés communes à tous les éléments
    const defaults = {
      // Propriétés communes
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      opacity: 100,
      rotation: 0,
      scale: 100,
      visible: true,

      // Apparence
      backgroundColor: 'transparent',
      borderColor: '#e2e8f0',
      borderWidth: 0,
      borderStyle: 'solid',
      borderRadius: 0,

      // Typographie (disponible pour tous les éléments)
      color: '#1e293b',
      fontFamily: 'Inter, sans-serif',
      fontSize: 14,
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',

      // Contenu (pour éléments texte)
      content: 'Texte',

      // Images
      src: '',
      alt: '',
      objectFit: 'cover',

      // Effets
      shadow: false,
      shadowColor: '#000000',
      shadowOffsetX: 2,
      shadowOffsetY: 2,
      brightness: 100,
      contrast: 100,
      saturate: 100,

      // Propriétés spécifiques aux tableaux
      showHeaders: true,
      showBorders: true,
      headers: ['Produit', 'Qté', 'Prix'],
      dataSource: 'order_items',
      columns: {
        image: true,
        name: true,
        sku: false,
        quantity: true,
        price: true,
        total: true
      },
      showSubtotal: false,
      showShipping: true,
      showTaxes: true,
      showDiscount: false,
      showTotal: false,

      // Propriétés pour les barres de progression
      progressColor: '#3b82f6',
      progressValue: 75,

      // Propriétés pour les codes
      lineColor: '#64748b',
      lineWidth: 2,

      // Propriétés pour les types de document
      documentType: 'invoice',

      // Propriétés pour les logos et images
      imageUrl: '',

      // Propriétés d'espacement et mise en page
      spacing: 8,
      layout: 'vertical',
      alignment: 'left',
      fit: 'contain'
    };

    // Ajustements mineurs selon le type pour une meilleure UX
    const typeAdjustments = {
      'text': {
        width: 150,
        height: 30
      },
      'image': {
        width: 150,
        height: 100
      },
      'rectangle': {
        backgroundColor: '#f1f5f9',
        borderWidth: 1,
        width: 150,
        height: 80
      },
      'product_table': {
        width: 300,
        height: 150
      },
      'customer_info': {
        width: 200,
        height: 100
      },
      'company_logo': {
        width: 100,
        height: 60
      },
      'order_number': {
        width: 150,
        height: 30
      },
      'company_info': {
        width: 200,
        height: 80
      },
      'document_type': {
        width: 120,
        height: 40
      },
      'watermark': {
        width: 300,
        height: 200,
        opacity: 10,
        content: 'CONFIDENTIEL'
      },
      'progress-bar': {
        width: 200,
        height: 20
      },
      'barcode': {
        width: 150,
        height: 60
      },
      'qrcode': {
        width: 80,
        height: 80
      },
      'icon': {
        width: 50,
        height: 50
      },
      'line': {
        width: 200,
        height: 2
      }
    };

    return {
      ...defaults,
      ...(typeAdjustments[elementType] || {})
    };
  }

  /**
   * Calcule les propriétés calculées (readonly)
   */
  getComputedProperties(properties) {
    return {
      // Position absolue avec rotation
      absoluteX: properties.x + (properties.width / 2),
      absoluteY: properties.y + (properties.height / 2),

      // Dimensions avec échelle
      scaledWidth: properties.width * (properties.scale / 100),
      scaledHeight: properties.height * (properties.scale / 100),

      // Styles CSS calculés
      cssTransform: `rotate(${properties.rotation}deg) scale(${properties.scale / 100})`,
      cssFilter: `brightness(${properties.brightness}%) contrast(${properties.contrast}%) saturate(${properties.saturate}%)`,
      cssBoxShadow: properties.shadow
        ? `${properties.shadowOffsetX || 0}px ${properties.shadowOffsetY || 0}px 4px ${properties.shadowColor || '#000000'}`
        : 'none'
    };
  }

  /**
   * Vérifie si une propriété peut être animée
   */
  isAnimatable(property) {
    const animatableProperties = [
      'x', 'y', 'width', 'height', 'rotation', 'scale', 'opacity',
      'brightness', 'contrast', 'saturate'
    ];
    return animatableProperties.includes(property);
  }

  /**
   * Obtient les contraintes d'une propriété
   */
  getPropertyConstraints(property) {
    const constraints = {
      x: { min: -1000, max: 2000, step: 1 },
      y: { min: -1000, max: 2000, step: 1 },
      width: { min: 1, max: 2000, step: 1 },
      height: { min: 1, max: 2000, step: 1 },
      fontSize: { min: 8, max: 72, step: 1 },
      borderWidth: { min: 0, max: 20, step: 1 },
      borderRadius: { min: 0, max: 100, step: 1 },
      rotation: { min: -180, max: 180, step: 1 },
      scale: { min: 10, max: 200, step: 5 },
      opacity: { min: 0, max: 100, step: 1 },
      brightness: { min: 0, max: 200, step: 5 },
      contrast: { min: 0, max: 200, step: 5 },
      saturate: { min: 0, max: 200, step: 5 },
      shadowOffsetX: { min: -50, max: 50, step: 1 },
      shadowOffsetY: { min: -50, max: 50, step: 1 }
    };

    return constraints[property] || {};
  }
}

// Instance singleton du service
export const elementCustomizationService = new ElementCustomizationService();