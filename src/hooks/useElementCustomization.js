import { useState, useCallback, useEffect, useMemo } from 'react';
import { elementCustomizationService } from '../services/ElementCustomizationService';
import {
  isPropertyAllowed,
  getPropertyDefault,
  validateProperty,
  fixInvalidProperty,
  ELEMENT_TYPE_MAPPING
} from '../utilities/elementPropertyRestrictions';

/**
 * Hook pour gérer la personnalisation des éléments
 * Gère l'état local des propriétés et les changements en temps réel
 */
export const useElementCustomization = (selectedElements, elements, onPropertyChange) => {
  const [localProperties, setLocalProperties] = useState({});
  const [activeTab, setActiveTab] = useState('appearance');

  // Obtenir l'élément sélectionné (mémorisé pour éviter les re-renders)
  const selectedElement = useMemo(() => {
    return selectedElements.length > 0
      ? elements.find(el => el.id === selectedElements[0])
      : null;
  }, [selectedElements, elements]);

  // Synchroniser les propriétés locales avec l'élément sélectionné
  useEffect(() => {
    console.log('🔧 useElementCustomization - selectedElement:', selectedElement?.id, 'backgroundColor:', selectedElement?.backgroundColor);
    console.log('🔧 useElementCustomization - selectedElement full properties:', selectedElement);

    if (selectedElement) {
      const defaultProperties = {
        // Valeurs par défaut de base
        color: '#333333',
        backgroundColor: 'transparent',
        borderColor: '#dddddd',
        borderWidth: 0,
        borderStyle: 'solid',
        borderRadius: 4,
        fontSize: 14,
        fontFamily: 'Inter',
        fontWeight: 'normal',
        fontStyle: 'normal',
        textDecoration: 'none',
        textAlign: 'left',
        lineHeight: 1.2,
        letterSpacing: 0,
      };

      console.log('🔧 useElementCustomization - default properties:', defaultProperties);

      // Appliquer les restrictions selon le type d'élément
      const elementType = selectedElement.type;
      const restrictedDefaults = { ...defaultProperties };

      // Pour les propriétés restreintes, utiliser les valeurs par défaut spécifiques
      Object.keys(defaultProperties).forEach(property => {
        const specificDefault = getPropertyDefault(elementType, property);
        if (specificDefault !== null) {
          restrictedDefaults[property] = specificDefault;
        }

        // Pour les éléments spéciaux, forcer backgroundColor à transparent
        if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && property === 'backgroundColor') {
          restrictedDefaults[property] = 'transparent';
        }
      });

      const newProperties = {
        // Valeurs par défaut avec restrictions
        ...restrictedDefaults,
        // Propriétés de l'élément (écrasent les défauts SAUF les propriétés restreintes)
        ...selectedElement
      };

      // Pour les propriétés restreintes, forcer les valeurs corrigées
      Object.keys(restrictedDefaults).forEach(property => {
        const isRestricted = !isPropertyAllowed(elementType, property);
        if (isRestricted) {
          const correctedValue = fixInvalidProperty(elementType, property, newProperties[property]);
          if (correctedValue !== newProperties[property]) {
            console.log(`🔧 Forçage de ${property} pour ${elementType}: '${newProperties[property]}' -> '${correctedValue}'`);
            newProperties[property] = correctedValue;
          }
        }
        // Pour les éléments spéciaux, garder backgroundColor à transparent par défaut seulement si pas défini
        else if (ELEMENT_TYPE_MAPPING[elementType] === 'special' && property === 'backgroundColor' && !newProperties[property]) {
          newProperties[property] = 'transparent';
        }
      });

      // Validation finale des propriétés
      Object.keys(newProperties).forEach(property => {
        const validation = validateProperty(elementType, property, newProperties[property]);
        if (!validation.valid) {
          console.warn(`Propriété invalide ${property} pour ${elementType}:`, validation.reason);
          newProperties[property] = fixInvalidProperty(elementType, property, newProperties[property]);
        }
      });

      console.log('🔧 useElementCustomization - merged properties:', newProperties);
      console.log('🔧 useElementCustomization - final backgroundColor:', newProperties.backgroundColor);
      console.log('🔧 useElementCustomization - backgroundColor source comparison:', {
        default: defaultProperties.backgroundColor,
        element: selectedElement.backgroundColor,
        final: newProperties.backgroundColor
      });
      setLocalProperties(newProperties);
    } else {
      console.log('🔧 useElementCustomization - no selectedElement, setting empty properties');
      setLocalProperties({});
    }
  }, [selectedElement?.id]); // Ne dépendre que de l'ID pour éviter les re-renders inutiles

  // Gestionnaire de changement de propriété avec validation
  const handlePropertyChange = useCallback((elementId, property, value) => {
    const element = elements.find(el => el.id === elementId);
    if (!element) return;

    let validatedValue = value;

    // Validation selon le système de restrictions
    const validation = validateProperty(element.type, property, value);
    if (!validation.valid) {
      console.warn(`Propriété non autorisée: ${validation.reason}`);
      // Ne pas appliquer le changement si la propriété n'est pas autorisée
      return;
    }

    // Validation supplémentaire selon le type de propriété (service existant)
    if (typeof value !== 'boolean' && !property.startsWith('columns.')) {
      try {
        const serviceValidated = elementCustomizationService.validateProperty(property, value);
        if (serviceValidated !== undefined) {
          validatedValue = serviceValidated;
        }
      } catch (error) {
        console.warn(`Erreur de validation pour ${property}:`, error);
      }
    }

    // Mettre à jour l'état local immédiatement pour l'UI
    setLocalProperties(prev => {
      let newProperties;
      if (property.includes('.')) {
        // Gérer les propriétés imbriquées (ex: "columns.image")
        const updateNestedProperty = (obj, path, value) => {
          const keys = path.split('.');
          const lastKey = keys.pop();
          const target = keys.reduce((current, key) => {
            if (!current[key] || typeof current[key] !== 'object') {
              current[key] = {};
            } else {
              current[key] = { ...current[key] }; // Créer une copie pour éviter de modifier l'original
            }
            return current[key];
          }, obj);
          target[lastKey] = value;
          return obj;
        };

        newProperties = { ...prev };
        updateNestedProperty(newProperties, property, validatedValue);
      } else {
        newProperties = { ...prev, [property]: validatedValue };
      }

      return newProperties;
    });

    // Notifier le parent pour la persistance
    onPropertyChange(elementId, property, validatedValue);
  }, [onPropertyChange, elements]);

  // Validation des valeurs de propriétés
  const validatePropertyValue = (property, value) => {
    switch (property) {
      case 'x':
      case 'y':
      case 'width':
      case 'height':
        return Math.max(0, parseInt(value) || 0);

      case 'fontSize':
        return Math.max(8, Math.min(72, parseInt(value) || 14));

      case 'lineHeight':
        return Math.max(0.5, Math.min(5, parseFloat(value) || 1.2));

      case 'letterSpacing':
        return Math.max(-5, Math.min(20, parseFloat(value) || 0));

      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));

      case 'borderStyle':
        const validStyles = ['solid', 'dashed', 'dotted', 'double'];
        return validStyles.includes(value) ? value : 'solid';

      case 'borderRadius':
        return Math.max(0, Math.min(100, parseInt(value) || 0));

      case 'rotation':
        return ((parseInt(value) || 0) % 360 + 360) % 360; // Normaliser entre 0-359

      case 'scale':
        return Math.max(10, Math.min(200, parseInt(value) || 100));

      case 'opacity':
        return Math.max(0, Math.min(100, parseInt(value) || 100));

      case 'brightness':
      case 'contrast':
      case 'saturate':
        return Math.max(0, Math.min(200, parseInt(value) || 100));

      case 'shadowOffsetX':
      case 'shadowOffsetY':
        return Math.max(-50, Math.min(50, parseInt(value) || 0));

      default:
        return value;
    }
  };

  // Appliquer des presets de propriétés
  const applyPropertyPreset = useCallback((elementId, preset) => {
    const presets = {
      // Presets de couleurs
      'color-dark': { color: '#1e293b' },
      'color-light': { color: '#f8fafc' },
      'color-primary': { color: '#2563eb' },
      'color-success': { color: '#16a34a' },
      'color-warning': { color: '#ca8a04' },
      'color-error': { color: '#dc2626' },

      // Presets de style de texte
      'text-title': { fontSize: 24, fontWeight: 'bold', textAlign: 'center' },
      'text-subtitle': { fontSize: 18, fontWeight: 'bold', textAlign: 'left' },
      'text-body': { fontSize: 14, fontWeight: 'normal', textAlign: 'left' },
      'text-caption': { fontSize: 12, fontWeight: 'normal', textAlign: 'left' },

      // Presets de formes
      'shape-rounded': { borderRadius: 8 },
      'shape-circle': { borderRadius: 50 },
      'shape-square': { borderRadius: 0 },

      // Presets d'effets
      'effect-shadow': { shadow: true, shadowColor: '#000000', shadowOffsetX: 2, shadowOffsetY: 2 },
      'effect-glow': { shadow: true, shadowColor: '#2563eb', shadowOffsetX: 0, shadowOffsetY: 0 },
      'effect-none': { shadow: false, brightness: 100, contrast: 100, saturate: 100 }
    };

    if (presets[preset]) {
      Object.entries(presets[preset]).forEach(([property, value]) => {
        handlePropertyChange(elementId, property, value);
      });
    }
  }, [handlePropertyChange]);

  // Réinitialiser les propriétés aux valeurs par défaut
  const resetProperties = useCallback((elementId) => {
    const defaultProperties = {
      x: 50,
      y: 50,
      width: 100,
      height: 50,
      backgroundColor: '#ffffff',
      borderColor: '#dddddd',
      borderWidth: 1,
      borderStyle: 'solid',
      borderRadius: 4,
      color: '#333333',
      fontSize: 14,
      fontFamily: 'Inter',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
      lineHeight: 1.2,
      letterSpacing: 0,
      opacity: 100,
      rotation: 0,
      scale: 100,
      shadow: false,
      brightness: 100,
      contrast: 100,
      saturate: 100
    };

    Object.entries(defaultProperties).forEach(([property, value]) => {
      handlePropertyChange(elementId, property, value);
    });
  }, [handlePropertyChange]);

  return {
    localProperties,
    activeTab,
    setActiveTab,
    selectedElement,
    handlePropertyChange,
    applyPropertyPreset,
    resetProperties,
    validatePropertyValue
  };
};