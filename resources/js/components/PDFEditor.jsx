import React, { useState, useRef, useEffect, useCallback } from 'react';
import { Toolbar } from './Toolbar';
import Canvas from './Canvas';
import { useGlobalSettings } from '../hooks/useGlobalSettings';
import './PDFEditor.css';

/**
 * PDFEditor - Éditeur Canvas Builder Complet et Performant
 * Version complète avec toolbar, canvas interactif et toutes les fonctionnalités
 */
const PDFEditor = ({ initialElements = [], onSave, templateName = '', isNew = true }) => {
  // États de l'éditeur
  const [selectedTool, setSelectedTool] = useState('select');
  const [zoom, setZoom] = useState(1.0);
  const [showGrid, setShowGrid] = useState(true);
  const [snapToGrid, setSnapToGrid] = useState(true);
  const [elements, setElements] = useState(initialElements);
  const [selectedElements, setSelectedElements] = useState([]);
  const [history, setHistory] = useState([initialElements]);
  const [historyIndex, setHistoryIndex] = useState(0);

  // Hooks globaux
  const { settings } = useGlobalSettings();

  // Dimensions du canvas (format A4)
  const canvasWidth = 595;
  const canvasHeight = 842;

  // Gestionnaire de sélection d'outil
  const handleToolSelect = useCallback((toolId) => {
    setSelectedTool(toolId);
  }, []);

  // Gestionnaire de zoom
  const handleZoomChange = useCallback((newZoom) => {
    setZoom(Math.max(0.1, Math.min(3.0, newZoom)));
  }, []);

  // Gestionnaire de grille
  const handleShowGridChange = useCallback((show) => {
    setShowGrid(show);
  }, []);

  const handleSnapToGridChange = useCallback((snap) => {
    setSnapToGrid(snap);
  }, []);

  // Gestionnaire d'historique
  const handleUndo = useCallback(() => {
    if (historyIndex > 0) {
      setHistoryIndex(historyIndex - 1);
      setElements(history[historyIndex - 1]);
    }
  }, [history, historyIndex]);

  const handleRedo = useCallback(() => {
    if (historyIndex < history.length - 1) {
      setHistoryIndex(historyIndex + 1);
      setElements(history[historyIndex + 1]);
    }
  }, [history, historyIndex]);

  const canUndo = historyIndex > 0;
  const canRedo = historyIndex < history.length - 1;

  // Gestionnaire d'aperçu
  const handlePreview = useCallback(() => {
    // TODO: Implémenter l'aperçu PDF
    console.log('Aperçu PDF demandé');
  }, []);

  // Gestionnaire de sélection d'éléments
  const handleElementSelect = useCallback((elementIds) => {
    setSelectedElements(elementIds);
  }, []);

  // Gestionnaire de mise à jour d'éléments
  const handleElementUpdate = useCallback((updatedElements) => {
    const newHistory = history.slice(0, historyIndex + 1);
    newHistory.push(updatedElements);
    setHistory(newHistory);
    setHistoryIndex(newHistory.length - 1);
    setElements(updatedElements);

    // Sauvegarder automatiquement
    if (onSave) {
      onSave(updatedElements);
    }
  }, [history, historyIndex, onSave]);

  // Gestionnaire de suppression d'éléments
  const handleElementRemove = useCallback((elementId) => {
    const newElements = elements.filter(el => el.id !== elementId);
    handleElementUpdate(newElements);
  }, [elements, handleElementUpdate]);

  return (
    <div className="pdf-editor">
      {/* Toolbar complète */}
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

      {/* Zone de travail avec canvas */}
      <div className="editor-workspace">
        <div className="canvas-container">
          <Canvas
            elements={elements}
            selectedElements={selectedElements}
            tool={selectedTool}
            zoom={zoom}
            showGrid={showGrid}
            snapToGrid={snapToGrid}
            gridSize={settings?.gridSize || 20}
            canvasWidth={canvasWidth}
            canvasHeight={canvasHeight}
            onElementSelect={handleElementSelect}
            onElementUpdate={handleElementUpdate}
            onElementRemove={handleElementRemove}
            onContextMenu={() => {}}
            selection={null}
            zoomHook={zoom}
          />
        </div>
      </div>

      {/* Informations */}
      <div className="editor-footer">
        <div className="status-info">
          <span>Outil: {selectedTool}</span>
          <span>Éléments: {elements.length}</span>
          <span>Zoom: {Math.round(zoom * 100)}%</span>
          <span>Sélectionnés: {selectedElements.length}</span>
        </div>
      </div>
    </div>
  );
};

export { PDFEditor };