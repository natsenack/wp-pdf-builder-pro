import React, { useRef, useCallback } from 'react';
import { useResize } from '../hooks/useResize';
import { ResizeHandles } from './ResizeHandles';

export const CanvasElement = ({
  element,
  isSelected,
  zoom,
  snapToGrid,
  gridSize,
  canvasWidth,
  canvasHeight,
  canvasRef,
  onSelect,
  onUpdate,
  onRemove,
  onContextMenu,
  dragAndDrop
}) => {
  const elementRef = useRef(null);

  // Hook de redimensionnement
  const resize = useResize({
    onResize: (newRect) => {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    onResizeEnd: (finalRect) => {
      onUpdate({
        x: finalRect.x,
        y: finalRect.y,
        width: finalRect.width,
        height: finalRect.height
      });
    },
    snapToGrid,
    gridSize,
    canvasRef
  });

  // Gestionnaire de clic sur l'élément
  const handleMouseDown = useCallback((e) => {
    e.stopPropagation();

    // Vérifier si Ctrl/Cmd est pressé pour la sélection multiple
    const addToSelection = e.ctrlKey || e.metaKey;

    if (!isSelected || addToSelection) {
      onSelect(addToSelection);
      return;
    }

    // Démarrer le drag & drop si disponible
    if (dragAndDrop?.startDrag) {
      dragAndDrop.startDrag(e, element);
    }
  }, [isSelected, onSelect, dragAndDrop, element]);

  // Gestionnaire de clic droit
  const handleContextMenuEvent = useCallback((e) => {
    e.preventDefault();
    e.stopPropagation();
    onContextMenu?.(e, element);
  }, [onContextMenu, element]);

  // Styles de l'élément
  const elementStyle = {
    position: 'absolute',
    left: `${element.x}px`,
    top: `${element.y}px`,
    width: `${element.width}px`,
    height: `${element.height}px`,
    backgroundColor: element.backgroundColor || 'transparent',
    border: element.borderWidth ? `${element.borderWidth}px ${element.borderStyle || 'solid'} ${element.borderColor || '#e5e7eb'}` : 'none',
    borderRadius: element.borderRadius || 0,
    boxSizing: 'border-box',
    cursor: isSelected ? 'move' : 'pointer',
    zIndex: isSelected ? 20 : 10
  };

  // Classes CSS
  const elementClasses = [
    'canvas-element',
    isSelected ? 'selected' : '',
    (!element.backgroundColor || element.backgroundColor === 'transparent') ? 'transparent-bg' : ''
  ].filter(Boolean).join(' ');

  return (
    <div
      ref={elementRef}
      className={elementClasses}
      style={elementStyle}
      onMouseDown={handleMouseDown}
      onContextMenu={handleContextMenuEvent}
    >
      {/* Contenu de l'élément selon son type */}
      <ElementContent element={element} zoom={zoom} />

      {/* Poignées de redimensionnement */}
      <ResizeHandles
        isVisible={isSelected}
        onResizeStart={resize.startResize}
        elementRect={{
          x: element.x,
          y: element.y,
          width: element.width,
          height: element.height
        }}
        zoom={zoom}
      />
    </div>
  );
};

// Composant pour le contenu des éléments selon leur type
const ElementContent = ({ element, zoom }) => {
  const contentStyle = {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    padding: '8px',
    boxSizing: 'border-box',
    fontSize: `${14 * zoom}px`,
    color: element.textColor || '#000000'
  };

  switch (element.type) {
    case 'text':
      return (
        <div style={contentStyle}>
          {element.content || 'Texte'}
        </div>
      );

    case 'image':
      return (
        <div style={contentStyle}>
          <img
            src={element.src || '/placeholder-image.png'}
            alt={element.alt || 'Image'}
            style={{
              maxWidth: '100%',
              maxHeight: '100%',
              objectFit: 'contain'
            }}
          />
        </div>
      );

    case 'divider':
      return (
        <div style={{
          ...contentStyle,
          borderTop: `2px solid ${element.color || '#d1d5db'}`,
          margin: 'auto 0'
        }} />
      );

    default:
      return (
        <div style={contentStyle}>
          {element.type || 'Élément'}
        </div>
      );
  }
};
