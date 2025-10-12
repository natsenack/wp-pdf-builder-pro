import { useState, useEffect } from 'react';

export const useGlobalSettings = () => {
  const [settings, setSettings] = useState({
    // Paramètres des points de redimensionnement
    resizeHandleSize: 12,
    resizeHandleColor: '#007cba',
    resizeHandleBorderColor: '#ffffff',

    // Paramètres des bordures de sélection
    selectionBorderWidth: 2,
    selectionBorderColor: 'var(--primary-color)',
    selectionShadowOpacity: 0.1,
    selectionBorderSpacing: 2,

    // Paramètres des zones de redimensionnement
    resizeZoneSize: 16,
    resizeZoneOpacity: 0.1,

    // Paramètres généraux
    showResizeHandles: true,
    showResizeZones: true,
    selectionAnimation: true,

    // Paramètres par défaut des éléments
    defaultTextColor: '#000000',
    defaultBackgroundColor: '#ffffff',
    defaultFontSize: 14
  });

  // Fonction pour récupérer les paramètres WordPress
  const getWordPressSettings = () => {
    if (window.pdfBuilderCanvasSettings) {
      return window.pdfBuilderCanvasSettings;
    }
    return null;
  };

  // Charger les paramètres depuis WordPress ou localStorage au montage
  useEffect(() => {
    const wpSettings = getWordPressSettings();
    if (wpSettings) {
      // Utiliser les paramètres WordPress
      setSettings(prev => ({
        ...prev,
        resizeHandleSize: wpSettings.canvas_handle_size || prev.resizeHandleSize,
        resizeHandleColor: wpSettings.canvas_handle_color || prev.resizeHandleColor,
        resizeHandleBorderColor: wpSettings.canvas_handle_hover_color || prev.resizeHandleBorderColor,
        selectionBorderWidth: wpSettings.canvas_border_width || prev.selectionBorderWidth,
        selectionBorderColor: wpSettings.canvas_border_color || prev.selectionBorderColor,
        selectionBorderSpacing: wpSettings.canvas_border_spacing || prev.selectionBorderSpacing,
        showResizeHandles: wpSettings.canvas_resize_handles_enabled !== undefined ? wpSettings.canvas_resize_handles_enabled : prev.showResizeHandles,
        showResizeZones: wpSettings.canvas_element_borders_enabled !== undefined ? wpSettings.canvas_element_borders_enabled : prev.showResizeZones,
        // Nouveaux paramètres par défaut des éléments
        defaultTextColor: wpSettings.default_text_color || prev.defaultTextColor,
        defaultBackgroundColor: wpSettings.default_background_color || prev.defaultBackgroundColor,
        defaultFontSize: wpSettings.default_font_size || prev.defaultFontSize
      }));
    } else {
      // Fallback vers localStorage si les paramètres WordPress ne sont pas disponibles
      const savedSettings = localStorage.getItem('pdf-builder-global-settings');
      if (savedSettings) {
        try {
          const parsedSettings = JSON.parse(savedSettings);
          setSettings(prev => ({ ...prev, ...parsedSettings }));
        } catch (error) {
          console.warn('Erreur lors du chargement des paramètres globaux:', error);
        }
      }
    }
  }, []);

  // Appliquer les paramètres aux variables CSS
  useEffect(() => {
    const root = document.documentElement;

    // Appliquer les paramètres des points de redimensionnement
    root.style.setProperty('--resize-handle-size', `${settings.resizeHandleSize}px`);
    root.style.setProperty('--resize-handle-color', settings.resizeHandleColor);
    root.style.setProperty('--resize-handle-border-color', settings.resizeHandleBorderColor);

    // Appliquer les paramètres des bordures de sélection
    root.style.setProperty('--selection-border-width', `${settings.selectionBorderWidth}px`);
    root.style.setProperty('--selection-border-color', settings.selectionBorderColor);
    root.style.setProperty('--selection-border-spacing', `${settings.selectionBorderSpacing}px`);
    root.style.setProperty('--selection-shadow-opacity', settings.selectionShadowOpacity);

    // Appliquer les paramètres des zones de redimensionnement
    root.style.setProperty('--resize-zone-size', `${settings.resizeZoneSize}px`);
    root.style.setProperty('--resize-zone-opacity', settings.resizeZoneOpacity);

    // Appliquer les paramètres généraux
    root.style.setProperty('--show-resize-handles', settings.showResizeHandles ? 'block' : 'none');
    root.style.setProperty('--show-resize-zones', settings.showResizeZones ? 'auto' : 'none');
    root.style.setProperty('--selection-animation', settings.selectionAnimation ? 'all 0.2s ease' : 'none');

  }, [settings]);

  const updateSettings = (newSettings) => {
    setSettings(prev => {
      const updated = { ...prev, ...newSettings };
      // Sauvegarder dans localStorage pour la compatibilité
      localStorage.setItem('pdf-builder-global-settings', JSON.stringify(updated));
      return updated;
    });
  };

  const resetToDefaults = () => {
    const defaultSettings = {
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
      selectionBorderSpacing: 2,
      selectionShadowOpacity: 0.1,
      resizeZoneSize: 16,
      resizeZoneOpacity: 0.1,
      showResizeHandles: true,
      showResizeZones: true,
      selectionAnimation: true
    };
    setSettings(defaultSettings);
  };

  return {
    settings,
    updateSettings,
    resetToDefaults
  };
};