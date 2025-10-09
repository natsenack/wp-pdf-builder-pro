import { useState, useCallback, useEffect } from 'react';

/**
 * Hook pour gérer la personnalisation des éléments
 * Gère l'état local des propriétés et les changements en temps réel
 */
export const useElementCustomization = (selectedElements, elements, onPropertyChange) => {
  const [localProperties, setLocalProperties] = useState({});
  const [activeTab, setActiveTab] = useState('appearance');

  // Obtenir l'élément sélectionné
  const selectedElement = selectedElements.length > 0
    ? elements.find(el => el.id === selectedElements[0])
    : null;

  // Synchroniser les propriétés locales avec l'élément sélectionné
  useEffect(() => {
    if (selectedElement) {
      setLocalProperties({ ...selectedElement });
    } else {
      setLocalProperties({});
    }
  }, [selectedElement]);

  // Gestionnaire de changement de propriété avec validation
  const handlePropertyChange = useCallback((elementId, property, value) => {
    // Validation des valeurs selon le type de propriété
    const validatedValue = validatePropertyValue(property, value);

    // Mettre à jour l'état local immédiatement pour l'UI
    setLocalProperties(prev => ({ ...prev, [property]: validatedValue }));

    // Notifier le parent pour la persistance
    onPropertyChange(elementId, property, validatedValue);
  }, [onPropertyChange]);

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

      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));

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
      borderRadius: 4,
      color: '#333333',
      fontSize: 14,
      fontFamily: 'Arial, sans-serif',
      fontWeight: 'normal',
      fontStyle: 'normal',
      textAlign: 'left',
      textDecoration: 'none',
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