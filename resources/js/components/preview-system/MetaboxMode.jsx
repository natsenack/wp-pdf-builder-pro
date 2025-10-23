import React, { useState, useEffect, useCallback } from 'react';
import { usePreviewContext } from './context/PreviewContext';
import { usePerformanceMonitor } from './hooks/usePerformanceMonitor';
import PDFRenderer from './renderers/PDFRenderer';
import CanvasRenderer from './renderers/CanvasRenderer';
import { ImageRenderer } from './renderers/ImageRenderer';
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
    state: { loading, error, data: previewData },
    actions: { loadPreview, clearPreview }
  } = usePreviewContext();

  const { measureOperation } = usePerformanceMonitor('MetaboxMode');
  const [activeTab, setActiveTab] = useState('preview'); // preview, settings, export

  // Chargement des données d'aperçu au montage
  useEffect(() => {
    if (productId && templateData) {
      const timer = measureLoadTime('loadMetaboxPreview');
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
      position: fixed !important;
      top: 50px !important;
      right: 20px !important;
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
      color: white !important;
      padding: 8px 16px !important;
      border-radius: 20px !important;
      font-size: 14px !important;
      font-weight: bold !important;
      box-shadow: 0 4px 12px rgba(255,107,107,0.4) !important;
      z-index: 999999 !important;
      border: 3px solid white !important;
      animation: phase8-pulse 2s infinite !important;
    }
    @keyframes phase8-pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    .phase8-metrics {
      font-size: 12px !important;
      color: #ff6b6b !important;
      background: #ffeaea !important;
      padding: 4px 8px !important;
      border-radius: 12px !important;
      margin-left: 8px !important;
      border: 1px solid #ff6b6b !important;
      font-weight: bold !important;
    }
    .metabox-mode {
      border: 3px solid #ff6b6b !important;
      position: relative !important;
    }
  `;

  // Injection des styles Phase 8
  useEffect(() => {
    console.log('=== PHASE 8: Injecting styles ===');
    const styleSheet = document.createElement('style');
    styleSheet.textContent = phase8Styles;
    document.head.appendChild(styleSheet);
    console.log('=== PHASE 8: Styles injected successfully ===');

    return () => {
      document.head.removeChild(styleSheet);
    };
  }, []);

  // Debug: Log du rendu
  console.log('=== PHASE 8: MetaboxMode rendering ===', { loading, error, previewData, templateElements });

  // Alerte de confirmation Phase 8
  useEffect(() => {
    console.log('=== PHASE 8 ALERT: MetaboxMode component loaded! ===');
    // Petit délai pour éviter de spammer
    const timer = setTimeout(() => {
      console.log('🚀 PHASE 8: Nouveau système d\'aperçu actif!');
      console.log('📊 État actuel:', { loading, error, templateElements: templateElements?.length || 0 });
    }, 1000);

    return () => clearTimeout(timer);
  }, [loading, error, templateElements]);

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
      {/* DEBUG: Indicateur immédiat que Phase 8 fonctionne */}
      <div style={{
        position: 'fixed',
        top: '20px',
        left: '20px',
        background: 'red',
        color: 'white',
        padding: '10px',
        borderRadius: '10px',
        fontSize: '16px',
        fontWeight: 'bold',
        zIndex: 999999,
        border: '3px solid yellow'
      }}>
        🔥 PHASE 8 ACTIVE - {new Date().toLocaleTimeString()}
      </div>

      {/* Badge Phase 8 - Indicateur visuel du nouveau système */}
      <div className="phase8-badge">
        🚀 Phase 8 Active - {templateElements?.length || 0} éléments
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