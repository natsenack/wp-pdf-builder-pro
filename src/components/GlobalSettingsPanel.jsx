import React, { useState, useEffect } from 'react';

export const GlobalSettingsPanel = ({ onSettingsChange }) => {
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

  // Sauvegarder les paramètres dans le localStorage
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

  // Sauvegarder automatiquement les changements
  useEffect(() => {
    localStorage.setItem('pdf-builder-global-settings', JSON.stringify(settings));
    if (onSettingsChange) {
      onSettingsChange(settings);
    }
  }, [settings, onSettingsChange]);

  const updateSetting = (key, value) => {
    setSettings(prev => ({
      ...prev,
      [key]: value
    }));
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

  return (
    <div className="global-settings-panel">
      <div className="settings-header">
        <h3>⚙️ Paramètres Globaux</h3>
        <button
          className="btn btn-secondary btn-sm"
          onClick={resetToDefaults}
          title="Réinitialiser aux valeurs par défaut"
        >
          🔄 Réinitialiser
        </button>
      </div>

      <div className="settings-section">
        <h4>🎯 Points de Redimensionnement</h4>

        <div className="setting-group">
          <label>
            <input
              type="checkbox"
              checked={settings.showResizeHandles}
              onChange={(e) => updateSetting('showResizeHandles', e.target.checked)}
            />
            Afficher les points de redimensionnement
          </label>
        </div>

        <div className="setting-group">
          <label>Taille des points:</label>
          <div className="slider-container">
            <input
              type="range"
              min="8"
              max="20"
              step="1"
              value={settings.resizeHandleSize}
              onChange={(e) => updateSetting('resizeHandleSize', parseInt(e.target.value))}
              disabled={!settings.showResizeHandles}
            />
            <span className="slider-value">{settings.resizeHandleSize}px</span>
          </div>
        </div>

        <div className="setting-group">
          <label>Couleur des points:</label>
          <input
            type="color"
            value={settings.resizeHandleColor}
            onChange={(e) => updateSetting('resizeHandleColor', e.target.value)}
            disabled={!settings.showResizeHandles}
          />
        </div>

        <div className="setting-group">
          <label>Couleur de la bordure:</label>
          <input
            type="color"
            value={settings.resizeHandleBorderColor}
            onChange={(e) => updateSetting('resizeHandleBorderColor', e.target.value)}
            disabled={!settings.showResizeHandles}
          />
        </div>
      </div>

      <div className="settings-section">
        <h4>🔲 Bordures de Sélection</h4>

        <div className="setting-group">
          <label>
            <input
              type="checkbox"
              checked={settings.selectionAnimation}
              onChange={(e) => updateSetting('selectionAnimation', e.target.checked)}
            />
            Animation de sélection
          </label>
        </div>

        <div className="setting-group">
          <label>Épaisseur de la bordure:</label>
          <div className="slider-container">
            <input
              type="range"
              min="1"
              max="5"
              step="0.5"
              value={settings.selectionBorderWidth}
              onChange={(e) => updateSetting('selectionBorderWidth', parseFloat(e.target.value))}
            />
            <span className="slider-value">{settings.selectionBorderWidth}px</span>
          </div>
        </div>

        <div className="setting-group">
          <label>Couleur de la bordure:</label>
          <input
            type="color"
            value={settings.selectionBorderColor.startsWith('var(') ? '#2563eb' : settings.selectionBorderColor}
            onChange={(e) => updateSetting('selectionBorderColor', e.target.value)}
          />
          <small>CSS variable supportée (ex: var(--primary-color))</small>
        </div>

        <div className="setting-group">
          <label>Transparence de l'ombre:</label>
          <div className="slider-container">
            <input
              type="range"
              min="0"
              max="0.5"
              step="0.05"
              value={settings.selectionShadowOpacity}
              onChange={(e) => updateSetting('selectionShadowOpacity', parseFloat(e.target.value))}
            />
            <span className="slider-value">{Math.round(settings.selectionShadowOpacity * 100)}%</span>
          </div>
        </div>
      </div>

      <div className="settings-section">
        <h4>📏 Zones de Redimensionnement</h4>

        <div className="setting-group">
          <label>
            <input
              type="checkbox"
              checked={settings.showResizeZones}
              onChange={(e) => updateSetting('showResizeZones', e.target.checked)}
            />
            Activer les zones de redimensionnement sur les bords
          </label>
        </div>

        <div className="setting-group">
          <label>Taille des zones:</label>
          <div className="slider-container">
            <input
              type="range"
              min="8"
              max="24"
              step="2"
              value={settings.resizeZoneSize}
              onChange={(e) => updateSetting('resizeZoneSize', parseInt(e.target.value))}
              disabled={!settings.showResizeZones}
            />
            <span className="slider-value">{settings.resizeZoneSize}px</span>
          </div>
        </div>

        <div className="setting-group">
          <label>Transparence au survol:</label>
          <div className="slider-container">
            <input
              type="range"
              min="0"
              max="0.3"
              step="0.05"
              value={settings.resizeZoneOpacity}
              onChange={(e) => updateSetting('resizeZoneOpacity', parseFloat(e.target.value))}
              disabled={!settings.showResizeZones}
            />
            <span className="slider-value">{Math.round(settings.resizeZoneOpacity * 100)}%</span>
          </div>
        </div>
      </div>

      <style jsx>{`
        .global-settings-panel {
          padding: 16px;
          max-height: 600px;
          overflow-y: auto;
        }

        .settings-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
          border-bottom: 1px solid var(--border-color);
          padding-bottom: 12px;
        }

        .settings-header h3 {
          margin: 0;
          color: var(--text-primary);
        }

        .settings-section {
          margin-bottom: 24px;
          padding: 16px;
          background: var(--surface-color);
          border-radius: 8px;
          border: 1px solid var(--border-color);
        }

        .settings-section h4 {
          margin: 0 0 16px 0;
          color: var(--text-primary);
          font-size: 14px;
          font-weight: 600;
        }

        .setting-group {
          margin-bottom: 16px;
        }

        .setting-group label {
          display: block;
          margin-bottom: 8px;
          font-weight: 500;
          color: var(--text-secondary);
          font-size: 13px;
        }

        .setting-group input[type="checkbox"] {
          margin-right: 8px;
        }

        .setting-group input[type="color"] {
          width: 40px;
          height: 32px;
          border: 1px solid var(--border-color);
          border-radius: 4px;
          cursor: pointer;
        }

        .slider-container {
          display: flex;
          align-items: center;
          gap: 12px;
        }

        .slider-container input[type="range"] {
          flex: 1;
          height: 6px;
          border-radius: 3px;
          background: var(--border-color);
          outline: none;
        }

        .slider-container input[type="range"]::-webkit-slider-thumb {
          appearance: none;
          width: 16px;
          height: 16px;
          border-radius: 50%;
          background: var(--primary-color);
          cursor: pointer;
        }

        .slider-container input[type="range"]::-moz-range-thumb {
          width: 16px;
          height: 16px;
          border-radius: 50%;
          background: var(--primary-color);
          cursor: pointer;
          border: none;
        }

        .slider-value {
          min-width: 40px;
          text-align: center;
          font-size: 12px;
          font-weight: 600;
          color: var(--primary-color);
        }

        .btn {
          padding: 6px 12px;
          border: 1px solid var(--border-color);
          border-radius: 4px;
          background: var(--surface-color);
          color: var(--text-secondary);
          cursor: pointer;
          font-size: 12px;
          transition: all 0.2s;
        }

        .btn:hover {
          background: var(--background-color);
          color: var(--text-primary);
        }

        .btn-secondary {
          background: var(--background-color);
          color: var(--text-secondary);
        }

        .btn-sm {
          padding: 4px 8px;
          font-size: 11px;
        }

        small {
          display: block;
          margin-top: 4px;
          font-size: 11px;
          color: var(--text-tertiary);
        }
      `}</style>
    </div>
  );
};