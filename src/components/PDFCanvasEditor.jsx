import React, { useState, useRef, useEffect, useCallback } from 'react';
import { CanvasElement } from './CanvasElement';
import { useDragAndDrop } from '../hooks/useDragAndDrop';
import { Toolbar } from './Toolbar';
import { PropertiesPanel } from './PropertiesPanel';
import { ElementLibrary } from './ElementLibrary';
import { ContextMenu } from './ContextMenu';
import { WooCommerceElement } from './WooCommerceElements';
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
  const canvasRef = useRef(null);

  // Hook pour le drag and drop
  const dragAndDrop = useDragAndDrop({
    onElementMove: (elementId, position) => {
      canvasState.updateElement(elementId, position);
    },
    onElementDrop: (elementId, position) => {
      canvasState.updateElement(elementId, position);
    },
    canvasRect: canvasRef.current?.getBoundingClientRect(),
    zoom: canvasState.zoom.zoom,
    canvasWidth: canvasState.canvasWidth,
    canvasHeight: canvasState.canvasHeight
  });

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

      {/* Barre d'outils - déplacée sous le header pour prendre toute la largeur */}
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
          {/* Canvas avec éléments interactifs */}
          <div
            className="canvas-container"
            onClick={handleCanvasClick}
            onContextMenu={handleContextMenu}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
          >
            <div
              className="canvas-zoom-wrapper"
              style={{
                transform: `scale(${canvasState.zoom.zoom})`,
                transformOrigin: 'center'
              }}
            >
              <div className="canvas" ref={canvasRef}>
              <div className="canvas">
                {/* Grille de fond */}
                {showGrid && (
                  <div
                    className="canvas-grid"
                    style={{
                      position: 'absolute',
                      top: 0,
                      left: 0,
                      width: '100%',
                      height: '100%',
                      backgroundImage: `
                        linear-gradient(to right, #f1f5f9 1px, transparent 1px),
                        linear-gradient(to bottom, #f1f5f9 1px, transparent 1px)
                      `,
                      backgroundSize: '10px 10px',
                      pointerEvents: 'none'
                    }}
                  />
                )}

                {/* Éléments normaux rendus comme composants interactifs */}
                {canvasState.elements
                  .filter(el => !el.type.startsWith('woocommerce-'))
                  .map(element => {
                    return (
                      <CanvasElement
                        key={element.id}
                        element={element}
                        isSelected={canvasState.selection.selectedElements.includes(element.id)}
                        zoom={1} // Le zoom est géré au niveau du wrapper
                        snapToGrid={true}
                        gridSize={10}
                        canvasWidth={canvasState.canvasWidth}
                        canvasHeight={canvasState.canvasHeight}
                        onSelect={() => handleElementSelect(element.id)}
                        onUpdate={(updates) => canvasState.updateElement(element.id, updates)}
                        onRemove={() => canvasState.deleteElement(element.id)}
                        onContextMenu={handleContextMenu}
                        dragAndDrop={dragAndDrop}
                      />
                    );
                  })}

                {/* Éléments WooCommerce superposés */}
                {canvasState.elements
                  .filter(el => el.type.startsWith('woocommerce-'))
                  .map(element => (
                    <WooCommerceElement
                      key={element.id}
                      element={element}
                      isSelected={canvasState.selection.selectedElements.includes(element.id)}
                      onSelect={handleElementSelect}
                      onUpdate={canvasState.updateElement}
                      dragAndDrop={dragAndDrop}
                      zoom={1} // Le zoom est géré au niveau du wrapper
                      canvasWidth={canvasState.canvasWidth}
                      canvasHeight={canvasState.canvasHeight}
                    />
                  ))}
              </div>
            </div>
          </div>
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