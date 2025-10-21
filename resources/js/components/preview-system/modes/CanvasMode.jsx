import React from 'react';
import { usePreviewContext } from '../context/PreviewContext';

/**
 * CanvasMode - Mode d'aperçu canvas utilisant le système principal
 * Remplace la version simplifiée v3 pour utiliser l'architecture unifiée
 */
function CanvasMode() {
  const {
    state: { loading, error, data: previewData },
    actions: { clearPreview }
  } = usePreviewContext();

  if (loading) {
    return (
      <div className="canvas-mode-loading">
        <div className="spinner">Chargement...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="canvas-mode-error">
        <p>Erreur: {error}</p>
        <button onClick={clearPreview}>Réessayer</button>
      </div>
    );
  }

  return (
    <div className="canvas-mode">
      <div className="canvas-container">
        {previewData ? (
          <div className="preview-content">
            {/* Contenu d'aperçu à implémenter selon les besoins */}
            <p>Aperçu Canvas - Données chargées</p>
          </div>
        ) : (
          <div className="no-data">
            <p>Aucune donnée d'aperçu disponible</p>
          </div>
        )}
      </div>
    </div>
  );
}

// Fonction utilitaire pour charger des données (utilisée par PreviewModal)
CanvasMode.loadData = async (elements, templateData, config) => {
  // Logique de chargement des données d'aperçu
  return {
    elements: elements || [],
    templateData: templateData || {},
    config: config || {}
  };
};

export default CanvasMode;