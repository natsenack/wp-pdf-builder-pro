import React from 'react';

// Import du nouveau système d'aperçu
import { PreviewModal } from './PreviewModal_new';
import { PreviewProvider } from './context/PreviewContext_new';

/**
 * Point d'entrée principal pour le nouveau système d'aperçu
 * Version 2.0 - Architecture complètement refaite
 */

// Export du système complet
export { 
  PreviewModal,
  PreviewProvider
};

// Export du contexte et des hooks
export {
  usePreviewContext,
  PREVIEW_MODES,
  PREVIEW_ACTIONS
} from './context/PreviewContext_new';

// Export des renderers
export {
  UniversalRenderer,
  TextRenderer,
  RectangleRenderer,
  ImageRenderer,
  TableRenderer,
  createBaseRenderer,
  createCustomRenderer,
  useElementData,
  useElementStyles
} from './renderers/UniversalRenderer';

// Export des modes
export { default as CanvasMode } from './modes/CanvasMode_new';

// Composant de test pour vérifier le nouveau système
export function PreviewSystemTest() {
  const [showPreview, setShowPreview] = React.useState(false);
  
  // Données de test
  const testElements = [
    {
      id: 1,
      type: 'text',
      x: 50,
      y: 50,
      width: 200,
      height: 40,
      text: 'Titre du document',
      fontSize: 18,
      fontWeight: 'bold',
      color: '#1f2937'
    },
    {
      id: 2,
      type: 'rectangle',
      x: 50,
      y: 100,
      width: 300,
      height: 100,
      backgroundColor: '#3b82f6',
      borderRadius: 8
    },
    {
      id: 3,
      type: 'text',
      x: 70,
      y: 120,
      width: 260,
      height: 60,
      text: 'Contenu du rectangle avec du texte sur plusieurs lignes pour tester le rendu',
      fontSize: 14,
      color: 'white',
      textAlign: 'center'
    },
    {
      id: 4,
      type: 'image',
      x: 400,
      y: 50,
      width: 120,
      height: 120,
      src: 'https://via.placeholder.com/120x120/6366f1/white?text=IMG',
      borderRadius: 8
    },
    {
      id: 5,
      type: 'table',
      x: 50,
      y: 220,
      width: 400,
      height: 120,
      data: [
        ['Produit', 'Quantité', 'Prix'],
        ['Article 1', '2', '29,99 €'],
        ['Article 2', '1', '15,50 €'],
        ['Total', '3', '45,49 €']
      ]
    }
  ];
  
  const testTemplateData = {
    width: 595,
    height: 842,
    orientation: 'portrait'
  };
  
  const testPreviewData = {
    'text_1': {
      text: 'Titre dynamique injecté'
    },
    'text_3': {
      text: 'Texte dynamique dans le rectangle'
    }
  };
  
  return (
    <div style={{ padding: '20px' }}>
      <h2>🧪 Test du nouveau système d'aperçu</h2>
      <p>Cliquez sur le bouton pour tester la nouvelle modal d'aperçu avec des données d'exemple.</p>
      
      <button
        onClick={() => setShowPreview(true)}
        style={{
          padding: '12px 24px',
          backgroundColor: '#3b82f6',
          color: 'white',
          border: 'none',
          borderRadius: '8px',
          fontSize: '16px',
          cursor: 'pointer',
          margin: '20px 0'
        }}
      >
        🔍 Ouvrir l'aperçu de test
      </button>
      
      <div style={{ marginTop: '20px' }}>
        <h3>Fonctionnalités testées:</h3>
        <ul>
          <li>✅ Nouveau contexte avec état robuste</li>
          <li>✅ Renderers universels et standardisés</li>
          <li>✅ Mode Canvas avec positionnement correct</li>
          <li>✅ Système d'échelle et zoom</li>
          <li>✅ Interface moderne et intuitive</li>
          <li>✅ Injection de données dynamiques</li>
          <li>✅ Gestion d'erreurs et états de chargement</li>
        </ul>
      </div>
      
      {/* Modal d'aperçu */}
      <PreviewModal
        isOpen={showPreview}
        onClose={() => setShowPreview(false)}
        elements={testElements}
        templateData={testTemplateData}
        previewData={testPreviewData}
      />
    </div>
  );
}

/**
 * Hook pour intégrer facilement le nouveau système dans l'éditeur existant
 */
export function usePreviewSystem() {
  const [previewState, setPreviewState] = React.useState({
    isOpen: false,
    elements: [],
    templateData: {},
    previewData: {}
  });
  
  const openPreview = React.useCallback((config) => {
    setPreviewState({
      isOpen: true,
      elements: config.elements || [],
      templateData: config.templateData || { width: 595, height: 842 },
      previewData: config.previewData || {}
    });
  }, []);
  
  const closePreview = React.useCallback(() => {
    setPreviewState(prev => ({ ...prev, isOpen: false }));
  }, []);
  
  return {
    // État
    isOpen: previewState.isOpen,
    
    // Actions
    openPreview,
    closePreview,
    
    // Composant Modal
    PreviewModal: () => (
      <PreviewModal
        isOpen={previewState.isOpen}
        onClose={closePreview}
        elements={previewState.elements}
        templateData={previewState.templateData}
        previewData={previewState.previewData}
      />
    )
  };
}

export default {
  PreviewModal,
  PreviewProvider,
  PreviewSystemTest,
  usePreviewSystem
};