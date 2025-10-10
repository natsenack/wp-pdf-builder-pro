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

    // Paramètres des zones de redimensionnement
    resizeZoneSize: 16,
    resizeZoneOpacity: 0.1,

    // Paramètres généraux
    showResizeHandles: true,
    showResizeZones: true,
    selectionAnimation: true
  });

  // Charger les paramètres depuis le localStorage au montage
  useEffect(() => {
    const savedSettings = localStorage.getItem('pdf-builder-global-settings');
    if (savedSettings) {
      try {
        const parsedSettings = JSON.parse(savedSettings);
        setSettings(prev => ({ ...prev, ...parsedSettings }));
      } catch (error) {
        console.warn('Erreur lors du chargement des paramètres globaux:', error);
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
    setSettings(prev => ({ ...prev, ...newSettings }));
  };

  const resetToDefaults = () => {
    const defaultSettings = {
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
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