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
import { PreviewModal } from './PreviewModal';

export const PDFCanvasEditor = ({ options, onSave, onPreview }) => {
  const [tool, setTool] = useState('select');
  const [showGrid, setShowGrid] = useState(true);
  const [showPreviewModal, setShowPreviewModal] = useState(false);
  const [previewData, setPreviewData] = useState(null);
  const [isPropertiesCollapsed, setIsPropertiesCollapsed] = useState(false);

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

  // Gestionnaire pour la désélection et création d'éléments
  const handleCanvasClick = useCallback((e) => {
    if (e.target === e.currentTarget) {
      // Si un outil d'ajout est sélectionné, créer l'élément
      if (tool.startsWith('add-')) {
        const canvasRect = e.currentTarget.getBoundingClientRect();
        const clickX = e.clientX - canvasRect.left;
        const clickY = e.clientY - canvasRect.top;

        // Ajuster pour le zoom
        const adjustedX = clickX / canvasState.zoom.zoom;
        const adjustedY = clickY / canvasState.zoom.zoom;

        let elementType = 'text';
        let defaultProps = {};

        // Déterminer le type d'élément selon l'outil
        switch (tool) {
          case 'add-text':
            elementType = 'text';
            break;
          case 'add-text-title':
            elementType = 'text';
            defaultProps = { fontSize: 24, fontWeight: 'bold' };
            break;
          case 'add-text-subtitle':
            elementType = 'text';
            defaultProps = { fontSize: 18, fontWeight: 'bold' };
            break;
          case 'add-rectangle':
            elementType = 'rectangle';
            break;
          case 'add-circle':
            elementType = 'shape-circle';
            break;
          case 'add-line':
            elementType = 'line';
            break;
          case 'add-arrow':
            elementType = 'shape-arrow';
            break;
          case 'add-triangle':
            elementType = 'shape-triangle';
            break;
          case 'add-star':
            elementType = 'shape-star';
            break;
          case 'add-divider':
            elementType = 'divider';
            break;
          case 'add-image':
            elementType = 'image';
            break;
          default:
            // Pour les autres outils de la bibliothèque
            if (tool.startsWith('add-')) {
              elementType = tool.replace('add-', '');
            }
            break;
        }

        canvasState.addElement(elementType, {
          x: Math.max(0, adjustedX - 50),
          y: Math.max(0, adjustedY - 25),
          ...defaultProps
        });

        // Remettre l'outil de sélection après ajout
        setTool('select');
        return;
      }

      // Sinon, désélectionner
      canvasState.selection.clearSelection();
    }
  }, [canvasState, tool]);

  // Gestionnaire pour les changements de propriétés
  const handlePropertyChange = useCallback((elementId, property, value) => {
    console.log('🔧 PDFCanvasEditor handlePropertyChange:', elementId, property, '=', value);
    
    // Gérer les propriétés imbriquées (ex: "columns.image" -> { columns: { image: value } })
    const updates = {};
    if (property.includes('.')) {
      // Fonction récursive pour mettre à jour les propriétés imbriquées
      const updateNestedProperty = (obj, path, val) => {
        const keys = path.split('.');
        const lastKey = keys.pop();
        const target = keys.reduce((current, key) => {
          if (!current[key] || typeof current[key] !== 'object') {
            current[key] = {};
          } else {
            current[key] = { ...current[key] }; // Créer une copie pour éviter de modifier l'original
          }
          return current[key];
        }, obj);
        target[lastKey] = val;
        return obj;
      };

      updateNestedProperty(updates, property, value);
      console.log('🔧 Updates object:', updates);
    } else {
      updates[property] = value;
    }

    canvasState.updateElement(elementId, updates);
  }, [canvasState]);

  // Gestionnaire pour les mises à jour par lot
  const handleBatchUpdate = useCallback((updates) => {
    updates.forEach(({ elementId, property, value }) => {
      canvasState.updateElement(elementId, { [property]: value });
    });
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
            onClick={() => {
              setPreviewData(canvasState.saveTemplate());
              setShowPreviewModal(true);
            }}
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
        <div className={`editor-sidebar right-sidebar ${isPropertiesCollapsed ? 'collapsed' : ''}`}>
          {!isPropertiesCollapsed && (
            <PropertiesPanel
              selectedElements={canvasState.selection.selectedElements}
              elements={canvasState.elements}
              onPropertyChange={handlePropertyChange}
              onBatchUpdate={handleBatchUpdate}
            />
          )}
        </div>
      </div>

      {/* Bouton de toggle repositionné à la fin pour être au-dessus de tout */}
      <button
        className="sidebar-toggle-fixed"
        onClick={() => setIsPropertiesCollapsed(!isPropertiesCollapsed)}
        title={isPropertiesCollapsed ? 'Agrandir le panneau' : 'Réduire le panneau'}
        style={{
          position: 'fixed',
          top: '50%',
          right: isPropertiesCollapsed ? '70px' : '330px',
          transform: 'translateY(-50%)',
          zIndex: 999999
        }}
      >
        {isPropertiesCollapsed ? '▶' : '◀'}
      </button>

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

      {/* Modale d'aperçu */}
      <PreviewModal
        isOpen={showPreviewModal}
        onClose={() => {
          setShowPreviewModal(false);
          setPreviewData(null);
        }}
        templateData={previewData}
        canvasWidth={canvasState.canvasWidth}
        canvasHeight={canvasState.canvasHeight}
      />
    </div>
  );
};