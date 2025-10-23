import React, { useState, useRef, useEffect } from 'react';
import { Toolbar } from './Toolbar';
import PreviewModal from './preview-system/components/PreviewModal';
import { PreviewProvider } from './preview-system/context/PreviewProvider';
import { usePreviewContext } from './preview-system/context/PreviewContext';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur principal avec toolbar et aperçu
 * Phase 2.2.4.1 - Implémentation du bouton aperçu dans l'éditeur Canvas
 */
const PDFEditorContent = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  // Contexte d'aperçu
  const { actions: { openPreview } } = usePreviewContext();

  // État de l'éditeur
  const [selectedTool, setSelectedTool] = useState('select');
  const [zoom, setZoom] = useState(1.0);
  const [showGrid, setShowGrid] = useState(true);
  const [snapToGrid, setSnapToGrid] = useState(true);
  const [elements, setElements] = useState(initialElements);
  const [history, setHistory] = useState([initialElements]);
  const [historyIndex, setHistoryIndex] = useState(0);

  // Références
  const canvasRef = useRef(null);

  // Gestionnaire d'outils
  const handleToolSelect = (toolId) => {
    setSelectedTool(toolId);
  };

  // Gestionnaire de zoom
  const handleZoomChange = (newZoom) => {
    setZoom(Math.max(0.1, Math.min(3.0, newZoom)));
  };

  // Gestionnaire de grille
  const handleShowGridChange = (show) => {
    setShowGrid(show);
  };

  const handleSnapToGridChange = (snap) => {
    setSnapToGrid(snap);
  };

  // Gestionnaire d'historique
  const handleUndo = () => {
    if (historyIndex > 0) {
      setHistoryIndex(historyIndex - 1);
      setElements(history[historyIndex - 1]);
    }
  };

  const handleRedo = () => {
    if (historyIndex < history.length - 1) {
      setHistoryIndex(historyIndex + 1);
      setElements(history[historyIndex + 1]);
    }
  };

  const canUndo = historyIndex > 0;
  const canRedo = historyIndex < history.length - 1;

  // Gestionnaire d'aperçu
  const handlePreview = () => {
    openPreview('canvas', null, { elements });
  };

  // Gestionnaire nouveau template
  const handleNewTemplate = () => {
    // TODO: Implémenter la logique de nouveau template
    console.log('Nouveau template demandé');
  };

  // Gestionnaire de sauvegarde des éléments
  const handleElementsChange = (newElements) => {
    const newHistory = history.slice(0, historyIndex + 1);
    newHistory.push(newElements);
    setHistory(newHistory);
    setHistoryIndex(newHistory.length - 1);
    setElements(newElements);

    // Sauvegarder automatiquement si callback fourni
    if (onSave) {
      onSave(newElements);
    }
  };

  // Écouter les événements globaux pour le bouton aperçu du header
  useEffect(() => {
    const handleGlobalPreview = (event) => {
      if (event.type === 'pdfBuilderPreview') {
        handlePreview();
      }
    };

    // Écouter l'événement personnalisé
    document.addEventListener('pdfBuilderPreview', handleGlobalPreview);

    // Exposer la fonction globalement pour le bouton du header
    window.pdfBuilderPro = window.pdfBuilderPro || {};
    window.pdfBuilderPro.triggerPreview = handlePreview;

    // Nettoyer les écouteurs
    return () => {
      document.removeEventListener('pdfBuilderPreview', handleGlobalPreview);
    };
  }, [elements]); // Dépendance sur elements pour que la fonction soit à jour

  return (
    <div className="pdf-editor">
      {/* Toolbar principale */}
      <Toolbar
        selectedTool={selectedTool}
        onToolSelect={handleToolSelect}
        zoom={zoom}
        onZoomChange={handleZoomChange}
        showGrid={showGrid}
        onShowGridChange={handleShowGridChange}
        snapToGrid={snapToGrid}
        onSnapToGridChange={handleSnapToGridChange}
        onUndo={handleUndo}
        onRedo={handleRedo}
        canUndo={canUndo}
        canRedo={canRedo}
        onPreview={handlePreview}
      />

      {/* Zone de travail principale */}
      <div className="editor-workspace">
        <div className="canvas-container">
          <canvas
            ref={canvasRef}
            className="pdf-canvas"
            width={595}
            height={842}
            style={{
              transform: `scale(${zoom})`,
              transformOrigin: 'top left'
            }}
          />
        </div>
      </div>

      {/* Modal d'aperçu */}
      <PreviewModal />
    </div>
  );
};

export const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  return (
    <PreviewProvider>
      <PDFEditorContent
        initialElements={initialElements}
        onSave={onSave}
        templateName={templateName}
        isNew={isNew}
      />
    </PreviewProvider>
  );
};
