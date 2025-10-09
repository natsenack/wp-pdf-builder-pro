import React, { useState, useRef, useEffect, useCallback } from 'react';
import { Canvas } from './Canvas';
import { Toolbar } from './Toolbar';
import { PropertiesPanel } from './PropertiesPanel';
import { ElementLibrary } from './ElementLibrary';
import { ContextMenu } from './ContextMenu';
import { useCanvasState } from '../hooks/useCanvasState';
import { useKeyboardShortcuts } from '../hooks/useKeyboardShortcuts';

export const PDFCanvasEditor = ({ options, onSave, onPreview }) => {
  const [tool, setTool] = useState('select');
  const [showGrid, setShowGrid] = useState(true);

  const canvasState = useCanvasState({
    initialElements: options.initialElements || [],
    canvasWidth: options.width || 595,
    canvasHeight: options.height || 842,
    onSave,
    onPreview
  });

  const editorRef = useRef(null);

  // Gestion des raccourcis clavier
  useKeyboardShortcuts({
    onDelete: canvasState.deleteSelectedElements,
    onCopy: canvasState.copySelectedElements,
    onPaste: canvasState.pasteElements,
    onUndo: canvasState.undo,
    onRedo: canvasState.redo,
    onSave: canvasState.saveTemplate,
    onZoomIn: canvasState.zoom.zoomIn,
    onZoomOut: canvasState.zoom.zoomOut
  });

  // Gestionnaire pour ajouter un élément depuis la bibliothèque
  const handleAddElement = useCallback((elementType, properties = {}) => {
    canvasState.addElement(elementType, properties);
    setTool('select');
  }, [canvasState]);

  // Gestionnaire pour la sélection d'élément
  const handleElementSelect = useCallback((elementId) => {
    canvasState.selection.selectElement(elementId);
  }, [canvasState.selection]);

  // Gestionnaire pour la désélection
  const handleCanvasClick = useCallback((e) => {
    if (e.target === e.currentTarget) {
      canvasState.selection.clearSelection();
    }
  }, [canvasState.selection]);

  // Gestionnaire pour les changements de propriétés
  const handlePropertyChange = useCallback((elementId, property, value) => {
    canvasState.updateElement(elementId, { [property]: value });
  }, [canvasState]);

  // Gestionnaire du menu contextuel
  const handleContextMenu = useCallback((e, elementId = null) => {
    e.preventDefault();
    canvasState.showContextMenu(e.clientX, e.clientY, elementId);
  }, [canvasState]);

  // Gestionnaire pour les actions du menu contextuel
  const handleContextMenuAction = useCallback((action) => {
    if (typeof action === 'function') {
      action();
    }
  }, []);

  // Gestionnaire pour le drag over
  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  // Gestionnaire pour le drop
  const handleDrop = useCallback((e) => {
    e.preventDefault();
    
    try {
      const data = JSON.parse(e.dataTransfer.getData('application/json'));
      
      if (data.type === 'new-element') {
        const canvasRect = e.currentTarget.getBoundingClientRect();
        const dropX = e.clientX - canvasRect.left;
        const dropY = e.clientY - canvasRect.top;
        
        // Ajuster pour le zoom
        const adjustedX = dropX / canvasState.zoom.zoom;
        const adjustedY = dropY / canvasState.zoom.zoom;
        
        canvasState.addElement(data.elementType, {
          x: Math.max(0, adjustedX - 50), // Centrer l'élément sur le point de drop
          y: Math.max(0, adjustedY - 25),
          ...data.defaultProps
        });
      }
    } catch (error) {
      console.error('Erreur lors du drop:', error);
    }
  }, [canvasState]);

  return (
    <div className="pdf-canvas-editor" ref={editorRef}>
      {/* Barre d'outils principale */}
      <div className="editor-header">
        <div className="editor-title">
          <h2>Éditeur PDF - {options.isNew ? 'Nouveau Template' : `Template #${options.templateId}`}</h2>
        </div>
        <div className="editor-actions">
          <button
            className="btn btn-secondary"
            onClick={() => onPreview(canvasState.saveTemplate())}
          >
            👁️ Aperçu
          </button>
          <button
            className="btn btn-primary"
            onClick={canvasState.saveTemplate}
          >
            💾 Sauvegarder
          </button>
        </div>
      </div>

      <div className="editor-workspace">
        {/* Bibliothèque d'éléments */}
        <div className="editor-sidebar left-sidebar">
          <ElementLibrary
            onAddElement={handleAddElement}
            selectedTool={tool}
            onToolSelect={setTool}
          />
        </div>

        {/* Zone de travail principale */}
        <div className="editor-main">
          {/* Barre d'outils */}
          <Toolbar
            selectedTool={tool}
            onToolSelect={setTool}
            zoom={canvasState.zoom.zoom}
            onZoomChange={canvasState.zoom.setZoomLevel}
            showGrid={showGrid}
            onShowGridChange={setShowGrid}
            snapToGrid={true} // Peut être configuré plus tard
            onSnapToGridChange={() => {}} // Peut être configuré plus tard
            onUndo={canvasState.undo}
            onRedo={canvasState.redo}
            canUndo={canvasState.canUndo}
            canRedo={canvasState.canRedo}
          />

          {/* Canvas */}
          <div
            className="canvas-container"
            onClick={handleCanvasClick}
            onContextMenu={handleContextMenu}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
          >
            <Canvas
              elements={canvasState.elements}
              selectedElements={canvasState.selection.selectedElements}
              tool={tool}
              zoom={canvasState.zoom.zoom}
              showGrid={showGrid}
              snapToGrid={true}
              gridSize={10}
              canvasWidth={canvasState.canvasWidth}
              canvasHeight={canvasState.canvasHeight}
              onElementSelect={handleElementSelect}
              onElementUpdate={canvasState.updateElement}
              onElementRemove={canvasState.deleteElement}
              onContextMenu={handleContextMenu}
              selection={canvasState.selection}
              zoomHook={canvasState.zoom}
            />
          </div>
        </div>

        {/* Panneau de propriétés */}
        <div className="editor-sidebar right-sidebar">
          <PropertiesPanel
            selectedElements={canvasState.selection.selectedElements}
            elements={canvasState.elements}
            onPropertyChange={handlePropertyChange}
          />
        </div>
      </div>

      {/* Menu contextuel */}
      {canvasState.contextMenu.contextMenu && (
        <ContextMenu
          menu={canvasState.contextMenu.contextMenu}
          onAction={handleContextMenuAction}
        />
      )}

      {/* Indicateur d'état */}
      <div className="editor-status">
        <span>Éléments: {canvasState.elements.length}</span>
        <span>|</span>
        <span>Zoom: {Math.round(canvasState.zoom.zoom * 100)}%</span>
        <span>|</span>
        <span>Outil: {tool}</span>
        {canvasState.selection.selectedElements.length > 0 && (
          <>
            <span>|</span>
            <span>Éléments sélectionnés: {canvasState.selection.selectedElements.length}</span>
          </>
        )}
      </div>
    </div>
  );
};