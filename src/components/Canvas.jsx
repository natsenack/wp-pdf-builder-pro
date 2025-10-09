import React, { useRef, useCallback } from 'react';
import { CanvasElement } from './CanvasElement';
import { useDragAndDrop } from '../hooks/useDragAndDrop';
import { useSelection } from '../hooks/useSelection';

export const Canvas = ({
  elements,
  selectedElements,
  tool,
  zoom,
  showGrid,
  snapToGrid,
  gridSize,
  canvasWidth,
  canvasHeight,
  onElementSelect,
  onElementUpdate,
  onElementRemove,
  onContextMenu,
  selection,
  zoomHook
}) => {
  const canvasRef = useRef(null);

  const dragAndDrop = useDragAndDrop({
    onElementMove: (elementId, position) => {
      onElementUpdate(elementId, position);
    },
    onElementDrop: (elementId, position) => {
      onElementUpdate(elementId, position);
    },
    snapToGrid,
    gridSize
  });

  // Gestionnaire de clic sur le canvas
  const handleCanvasClick = useCallback((e) => {
    if (e.target !== canvasRef.current) return;

    const rect = canvasRef.current.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Si on utilise l'outil de sélection
    if (tool === 'select') {
      // Désélectionner tous les éléments
      selection.clearSelection();
    } else if (tool.startsWith('add-')) {
      // Ajouter un nouvel élément
      const elementType = tool.replace('add-', '');
      // Cette logique sera gérée par le parent
    }
  }, [tool, zoom, selection]);

  // Gestionnaire de double-clic pour la sélection par boîte
  const handleCanvasDoubleClick = useCallback((e) => {
    if (tool !== 'select') return;

    const rect = canvasRef.current.getBoundingClientRect();
    const startX = (e.clientX - rect.left) / zoom;
    const startY = (e.clientY - rect.top) / zoom;

    selection.startSelectionBox(startX, startY);

    const handleMouseMove = (moveEvent) => {
      const currentX = (moveEvent.clientX - rect.left) / zoom;
      const currentY = (moveEvent.clientY - rect.top) / zoom;
      selection.updateSelectionBox(currentX, currentY);
    };

    const handleMouseUp = () => {
      selection.endSelectionBox(elements);
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
    };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  }, [tool, zoom, selection, elements]);

  // Gestionnaire de clic droit
  const handleContextMenuEvent = useCallback((e) => {
    e.preventDefault();

    const rect = canvasRef.current.getBoundingClientRect();
    const x = (e.clientX - rect.left) / zoom;
    const y = (e.clientY - rect.top) / zoom;

    // Vérifier si on clique sur un élément
    const clickedElement = elements.find(element => {
      return x >= element.x &&
             x <= element.x + element.width &&
             y >= element.y &&
             y <= element.y + element.height;
    });

    if (onContextMenu) {
      onContextMenu(e, clickedElement?.id);
    }
  }, [elements, zoom, onContextMenu]);

  // Rendu de la grille
  const renderGrid = () => {
    if (!showGrid) return null;

    const lines = [];
    const step = gridSize * zoom;

    // Lignes verticales
    for (let x = 0; x <= canvasWidth * zoom; x += step) {
      lines.push(
        <line
          key={`v-${x}`}
          x1={x}
          y1={0}
          x2={x}
          y2={canvasHeight * zoom}
          stroke="#e0e0e0"
          strokeWidth="1"
        />
      );
    }

    // Lignes horizontales
    for (let y = 0; y <= canvasHeight * zoom; y += step) {
      lines.push(
        <line
          key={`h-${y}`}
          x1={0}
          y1={y}
          x2={canvasWidth * zoom}
          y2={y}
          stroke="#e0e0e0"
          strokeWidth="1"
        />
      );
    }

    return (
      <svg
        className="canvas-grid"
        width={canvasWidth * zoom}
        height={canvasHeight * zoom}
        style={{ position: 'absolute', top: 0, left: 0, pointerEvents: 'none' }}
      >
        {lines}
      </svg>
    );
  };

  return (
    <div className="canvas-wrapper">
      <div
        ref={canvasRef}
        className="canvas"
        style={{
          width: canvasWidth * zoom,
          height: canvasHeight * zoom,
          position: 'relative',
          backgroundColor: 'white',
          border: '1px solid #ccc',
          cursor: tool === 'select' ? 'default' : 'crosshair',
          ...zoomHook.getTransformStyle()
        }}
        onClick={handleCanvasClick}
        onDoubleClick={handleCanvasDoubleClick}
        onContextMenu={handleContextMenuEvent}
        onDragOver={dragAndDrop.handleDragOver}
        onDrop={(e) => dragAndDrop.handleDrop(e, canvasRef.current.getBoundingClientRect())}
      >
        {/* Grille */}
        {renderGrid()}

        {/* Éléments du canvas */}
        {elements.map(element => (
          <CanvasElement
            key={element.id}
            element={element}
            isSelected={selection.isSelected(element.id)}
            zoom={zoom}
            snapToGrid={snapToGrid}
            gridSize={gridSize}
            onSelect={() => onElementSelect(element.id)}
            onUpdate={(updates) => onElementUpdate(element.id, updates)}
            onRemove={() => onElementRemove(element.id)}
            onContextMenu={(e) => onContextMenu(e, element.id)}
            dragAndDrop={dragAndDrop}
          />
        ))}

        {/* Boîte de sélection */}
        {selection.selectionBox && (
          <div
            className="selection-box"
            style={{
              position: 'absolute',
              left: selection.selectionBox.startX * zoom,
              top: selection.selectionBox.startY * zoom,
              width: (selection.selectionBox.endX - selection.selectionBox.startX) * zoom,
              height: (selection.selectionBox.endY - selection.selectionBox.startY) * zoom,
              border: '2px dashed #007cba',
              backgroundColor: 'rgba(0, 124, 186, 0.1)',
              pointerEvents: 'none'
            }}
          />
        )}
      </div>
    </div>
  );
};