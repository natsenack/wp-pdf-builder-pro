import React, { useState, useEffect, useCallback } from 'react';
import { usePreviewContext } from '../context/PreviewContext';
import { usePerformanceMonitor } from '../hooks/usePerformanceMonitor';
import PDFRenderer from './renderers/PDFRenderer';
import CanvasRenderer from './renderers/CanvasRenderer';
import ImageRenderer from './renderers/ImageRenderer';
import NavigationControls from './NavigationControls';

/**
 * MetaboxMode - Mode d'aperçu intégré dans les metaboxes WooCommerce
 * Optimisé pour l'intégration dans l'admin WordPress avec contraintes d'espace
 */
function MetaboxMode({
  productId,
  templateData,
  className = '',
  compact = true,
  showControls = true
}) {
  const {
    state: { loading, error, previewData },
    actions: { loadPreview, clearPreview }
  } = usePreviewContext();

  const { measureOperation } = usePerformanceMonitor('MetaboxMode');
  const [activeTab, setActiveTab] = useState('preview'); // preview, settings, export

  // Chargement des données d'aperçu au montage
  useEffect(() => {
    if (productId && templateData) {
      const timer = measureOperation('loadMetaboxPreview');
      loadPreview({
        type: 'metabox',
        productId,
        templateData,
        mode: 'compact'
      });
      timer.end();
    }

    return () => {
      clearPreview();
    };
  }, [productId, templateData, loadPreview, clearPreview, measureOperation]);

  // Gestion des onglets
  const handleTabChange = useCallback((tab) => {
    setActiveTab(tab);
  }, []);

  // Styles inline pour les indicateurs Phase 8
  const phase8Styles = `
    .phase8-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 10px;
      font-weight: bold;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
      z-index: 1000;
    }
    .phase8-metrics {
      font-size: 11px;
      color: #666;
      background: #f0f0f0;
      padding: 2px 6px;
      border-radius: 8px;
      margin-left: 8px;
    }
  `;

  // Injection des styles Phase 8
  useEffect(() => {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = phase8Styles;
    document.head.appendChild(styleSheet);

    return () => {
      document.head.removeChild(styleSheet);
    };
  }, []);

  // Rendu conditionnel selon l'état
  if (loading) {
    return (
      <div className={`metabox-mode loading ${className}`}>
        <div className="metabox-loading">
          <div className="metabox-spinner"></div>
          <span>Chargement de l'aperçu...</span>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={`metabox-mode error ${className}`}>
        <div className="metabox-error">
          <span className="error-icon">⚠️</span>
          <span className="error-message">
            Erreur lors du chargement de l'aperçu: {error.message}
          </span>
          <button
            className="error-retry-btn"
            onClick={() => loadPreview({
              type: 'metabox',
              productId,
              templateData,
              mode: 'compact'
            })}
          >
            Réessayer
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className={`metabox-mode ${compact ? 'compact' : ''} ${className}`}>
      {/* Badge Phase 8 - Indicateur visuel du nouveau système */}
      <div className="phase8-badge">
        🚀 Phase 8 Active
      </div>

      {/* En-tête avec onglets */}
      <div className="metabox-header">
        <div className="metabox-tabs">
          <button
            className={`metabox-tab ${activeTab === 'preview' ? 'active' : ''}`}
            onClick={() => handleTabChange('preview')}
          >
            Aperçu
          </button>
          <button
            className={`metabox-tab ${activeTab === 'settings' ? 'active' : ''}`}
            onClick={() => handleTabChange('settings')}
          >
            Paramètres
          </button>
          <button
            className={`metabox-tab ${activeTab === 'export' ? 'active' : ''}`}
            onClick={() => handleTabChange('export')}
          >
            Export
          </button>
        </div>

        <div className="metabox-actions">
          <button className="metabox-action-btn" title="Actualiser">
            🔄
          </button>
          <button className="metabox-action-btn" title="Plein écran">
            ⛶
          </button>
          {/* Métriques Phase 8 */}
          <div className="phase8-metrics">
            ⚡ {templateElements?.length || 0} éléments
          </div>
        </div>
      </div>

      {/* Contenu selon l'onglet actif */}
      <div className="metabox-content">
        {activeTab === 'preview' && (
          <div className="metabox-preview">
            {/* Zone d'aperçu compact */}
            <div className="metabox-preview-canvas">
              {previewData?.elements?.map((element) => {
                switch (element.type) {
                  case 'pdf':
                    return (
                      <PDFRenderer
                        key={element.id}
                        element={element}
                        previewData={previewData}
                        mode="metabox"
                      />
                    );
                  case 'canvas':
                    return (
                      <CanvasRenderer
                        key={element.id}
                        element={element}
                        previewData={previewData}
                        mode="metabox"
                      />
                    );
                  case 'image':
                  case 'company_logo':
                    return (
                      <ImageRenderer
                        key={element.id}
                        element={element}
                        previewData={previewData}
                        mode="metabox"
                      />
                    );
                  default:
                    return null;
                }
              })}
            </div>

            {/* Contrôles de navigation (optionnels) */}
            {showControls && (
              <div className="metabox-controls">
                <NavigationControls compact={true} />
              </div>
            )}
          </div>
        )}

        {activeTab === 'settings' && (
          <div className="metabox-settings">
            <div className="settings-group">
              <h4>Paramètres d'aperçu</h4>
              <div className="setting-item">
                <label>
                  <input type="checkbox" defaultChecked />
                  Afficher les marges
                </label>
              </div>
              <div className="setting-item">
                <label>
                  <input type="checkbox" defaultChecked />
                  Mode haute qualité
                </label>
              </div>
              <div className="setting-item">
                <label>
                  <input type="checkbox" />
                  Aperçu en temps réel
                </label>
              </div>
            </div>

            <div className="settings-group">
              <h4>Paramètres du template</h4>
              <div className="setting-item">
                <label>Taille de page:</label>
                <select defaultValue="a4">
                  <option value="a4">A4</option>
                  <option value="letter">Letter</option>
                  <option value="legal">Legal</option>
                </select>
              </div>
              <div className="setting-item">
                <label>Orientation:</label>
                <select defaultValue="portrait">
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Paysage</option>
                </select>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'export' && (
          <div className="metabox-export">
            <div className="export-options">
              <button className="export-btn export-pdf">
                📄 Exporter en PDF
              </button>
              <button className="export-btn export-png">
                🖼️ Exporter en PNG
              </button>
              <button className="export-btn export-jpeg">
                📷 Exporter en JPEG
              </button>
            </div>

            <div className="export-settings">
              <div className="setting-item">
                <label>
                  <input type="checkbox" defaultChecked />
                  Inclure les marges
                </label>
              </div>
              <div className="setting-item">
                <label>Résolution:</label>
                <select defaultValue="high">
                  <option value="low">Basse (72 DPI)</option>
                  <option value="medium">Moyenne (150 DPI)</option>
                  <option value="high">Haute (300 DPI)</option>
                </select>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default React.memo(MetaboxMode);