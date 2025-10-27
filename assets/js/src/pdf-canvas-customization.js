/**
 * Service de personnalisation des éléments - Version Vanilla JS
 * Fournit des utilitaires pour la gestion des propriétés d'éléments
 * Migré depuis resources/js/services/ElementCustomizationService.js
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
   * Valide une propriété selon son type et applique des corrections automatiques
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
      try {
        return validator(value);
      } catch (error) {
        return this.getDefaultValue(property);
      }
    }

    // Validation spécifique selon le type de propriété
    if (this.isNumericProperty(property)) {
      return this.validateNumericProperty(property, value);
    }

    if (this.isColorProperty(property)) {
      return this.validateColorProperty(value);
    }

    if (this.isTextStyleProperty(property)) {
      return this.validateTextStyleProperty(property, value);
    }

    // Si pas de validateur spécifique, retourner la valeur telle quelle
    return value;
  }

  /**
   * Vérifie si une propriété est numérique
   */
  isNumericProperty(property) {
    const numericProps = [
      'x', 'y', 'width', 'height', 'fontSize', 'opacity',
      'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth',
      'borderRadius', 'rotation', 'padding'
    ];
    return numericProps.includes(property);
  }

  /**
   * Vérifie si une propriété est une couleur
   */
  isColorProperty(property) {
    const colorProps = ['color', 'backgroundColor', 'borderColor'];
    return colorProps.includes(property);
  }

  /**
   * Vérifie si une propriété est un style de texte
   */
  isTextStyleProperty(property) {
    const textProps = ['fontWeight', 'textAlign', 'textDecoration', 'textTransform', 'borderStyle'];
    return textProps.includes(property);
  }

  /**
   * Valide une propriété numérique
   */
  validateNumericProperty(property, value) {
    if (value === null || value === undefined || value === '') {
      return this.getDefaultValue(property);
    }

    let numericValue;
    if (typeof value === 'string') {
      numericValue = parseFloat(value);
      if (isNaN(numericValue)) {
        return this.getDefaultValue(property);
      }
    } else if (typeof value === 'number') {
      numericValue = value;
    } else {
      return this.getDefaultValue(property);
    }

    // Appliquer les contraintes selon la propriété
    const constraints = {
      fontSize: { min: 8, max: 72 },
      opacity: { min: 0, max: 1 },
      lineHeight: { min: 0.5, max: 3 },
      letterSpacing: { min: -5, max: 10 },
      zIndex: { min: -100, max: 1000 },
      borderWidth: { min: 0, max: 20 },
      borderRadius: { min: 0, max: 100 },
      rotation: { min: -180, max: 180 },
      padding: { min: 0, max: 100 }
    };

    if (constraints[property]) {
      const { min, max } = constraints[property];
      numericValue = Math.max(min, Math.min(max, numericValue));
    }

    return numericValue;
  }

  /**
   * Valide une propriété de couleur
   */
  validateColorProperty(value) {
    if (!value) return '#000000';
    if (value === 'transparent') return value;

    // Vérifier si c'est un code hex valide
    if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
      return value;
    }

    // Vérifier si c'est un nom de couleur CSS valide
    const tempElement = document.createElement('div');
    tempElement.style.color = value;
    const computedColor = tempElement.style.color;

    // Si le navigateur reconnaît la couleur, la retourner
    if (computedColor && computedColor !== '') {
      return value;
    }

    return '#000000';
  }

  /**
   * Valide une propriété de style de texte
   */
  validateTextStyleProperty(property, value) {
    const validations = {
      fontWeight: ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
      textAlign: ['left', 'center', 'right', 'justify'],
      textDecoration: ['none', 'underline', 'overline', 'line-through'],
      textTransform: ['none', 'capitalize', 'uppercase', 'lowercase'],
      borderStyle: ['solid', 'dashed', 'dotted', 'double', 'none']
    };

    if (validations[property] && validations[property].includes(value)) {
      return value;
    }

    // Valeurs par défaut
    const defaults = {
      fontWeight: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
      textTransform: 'none',
      borderStyle: 'solid'
    };

    return defaults[property] || value;
  }

  /**
   * Obtient la valeur par défaut pour une propriété
   */
  getDefaultValue(property) {
    const defaults = {
      x: 0, y: 0, width: 100, height: 50,
      fontSize: 14, opacity: 1, lineHeight: 1.2,
      letterSpacing: 0, zIndex: 0, borderWidth: 0,
      borderRadius: 0, rotation: 0, padding: 0,
      color: '#333333', backgroundColor: 'transparent',
      borderColor: '#dddddd', fontWeight: 'normal',
      textAlign: 'left', textDecoration: 'none',
      textTransform: 'none', borderStyle: 'solid'
    };

    return defaults[property] || null;
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
        borderColor: 'transparent'
      },
      'shape-circle': {
        borderRadius: 50,
        borderWidth: 1,
        borderColor: 'transparent'
      },
      'shape-square': {
        borderRadius: 0,
        borderWidth: 1,
        borderColor: 'transparent'
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
  /**
   * Calcule la position initiale intelligente selon le type d'élément
   * Stratégie de grille pour éviter les chevauchements
   */
  calculateInitialPosition(elementType) {
    // Stratégie de positionnement par type d'élément
    const positionStrategy = {
      // COLONNE GAUCHE (x: 50)
      'product_table': { x: 50, y: 50 },
      'customer_info': { x: 50, y: 220 },
      'company_info': { x: 50, y: 340 },
      'document_type': { x: 50, y: 430 },
      'mentions': { x: 50, y: 480 },
      'line': { x: 50, y: 40 },  // Haute à gauche
      'dynamic-text': { x: 50, y: 550 },
      
      // COLONNE DROITE (x: 350)
      'company_logo': { x: 350, y: 50 },
      'order_number': { x: 350, y: 130 },
      'woocommerce-order-date': { x: 350, y: 160 },
      'woocommerce-invoice-number': { x: 350, y: 190 },
      
      // Éléments texte standards (répartition)
      'text': { x: 50, y: 600 },
      'text-title': { x: 50, y: 10 },
      'text-subtitle': { x: 50, y: 60 },
      
      // Formes et autres
      'rectangle': { x: 50, y: 700 },
      'circle': { x: 150, y: 700 },
      'arrow': { x: 250, y: 700 },
      'image': { x: 400, y: 500 }
    };

    // Retourner la position stratégique ou défaut si non trouvée
    return positionStrategy[elementType] || { x: 50, y: 50 };
  }

  getDefaultProperties(elementType = 'text') {
    // Propriétés communes à tous les éléments
    const position = this.calculateInitialPosition(elementType);
    
    const defaults = {
      // Propriétés communes
      x: position.x,
      y: position.y,
      width: 100,
      height: 50,
      opacity: 100,
      rotation: 0,
      scale: 100,
      visible: true,

      // Apparence
      backgroundColor: 'transparent',
      borderColor: 'transparent',
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

      // Contenu (pour éléments texte) - sera remplacé par les ajustements de type
      // text: 'Texte',

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
        height: 30,
        text: 'Texte d\'exemple'
      },
      'text-title': {
        width: 200,
        height: 40,
        text: 'FACTURE PROFESSIONNELLE',
        fontSize: 24,
        fontWeight: 'bold'
      },
      'text-subtitle': {
        width: 180,
        height: 35,
        text: 'Équipement informatique',
        fontSize: 18,
        fontWeight: 'bold'
      },
      'image': {
        width: 150,
        height: 100
      },
      'rectangle': {
        backgroundColor: '#f1f5f9',
        borderWidth: 1,
        borderColor: '#e2e8f0',
        width: 150,
        height: 80
      },
      'circle': {
        backgroundColor: '#e0f2fe',
        borderWidth: 2,
        borderColor: '#0ea5e9',
        width: 80,
        height: 80
      },
      'line': {
        height: 2,
        lineWidth: 2,
        color: '#64748b'
      },
      'product_table': {
        width: 300,
        height: 150
      },
      'customer_info': {
        width: 200,
        height: 100,
        text: 'Marie Dupont\n15 Rue de la Paix\n75002 Paris\nFrance\nmarie.dupont@email.fr'
      },
      'company_logo': {
        width: 100,
        height: 60
      },
      'order_number': {
        width: 150,
        height: 30,
        text: 'Commande #WC-12345'
      },
      'company_info': {
        width: 200,
        height: 80,
        text: 'TechCorp SARL\n123 Boulevard Haussmann\n75008 Paris\nFrance\ncontact@techcorp.fr\nSIRET: 123 456 789 00012'
      },
      'document_type': {
        width: 120,
        height: 40,
        text: 'FACTURE'
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