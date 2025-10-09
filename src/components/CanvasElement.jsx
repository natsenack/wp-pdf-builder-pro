import React, { useRef, useCallback } from 'react';
import { useResize } from '../hooks/useResize';

export const CanvasElement = ({
  element,
  isSelected,
  zoom,
  snapToGrid,
  gridSize,
  onSelect,
  onUpdate,
  onRemove,
  onContextMenu,
  dragAndDrop
}) => {
  const elementRef = useRef(null);

  const resize = useResize({
    onElementResize: (newRect) => {
      onUpdate({
        x: newRect.x,
        y: newRect.y,
        width: newRect.width,
        height: newRect.height
      });
    },
    snapToGrid,
    gridSize
  });

  // Gestionnaire de clic sur l'élément
  const handleMouseDown = useCallback((e) => {
    console.log('CanvasElement handleMouseDown', element.id, e.clientX, e.clientY);
    e.stopPropagation();

    if (!isSelected) {
      console.log('Element not selected, selecting...');
      onSelect();
      return;
    }

    // Calculer les coordonnées relatives au canvas (en tenant compte du zoom)
    const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
    if (!canvas) return;

    const canvasRect = canvas.getBoundingClientRect();
    const elementRect = elementRef.current.getBoundingClientRect();

    // Ajuster pour le zoom - les coordonnées doivent être relatives au canvas non-zoomé
    const relativeRect = {
      left: (elementRect.left - canvasRect.left) / zoom,
      top: (elementRect.top - canvasRect.top) / zoom,
      width: elementRect.width / zoom,
      height: elementRect.height / zoom
    };

    console.log('Canvas rect:', canvasRect);
    console.log('Element rect:', elementRect);
    console.log('Relative rect:', relativeRect);

    // Vérifier si on clique sur une poignée de redimensionnement
    const clickX = (e.clientX - canvasRect.left) / zoom;
    const clickY = (e.clientY - canvasRect.top) / zoom;

    console.log('Click position relative to canvas:', clickX, clickY);

    const handleSize = 8 / zoom; // Ajuster la taille des poignées pour le zoom
    const elementLeft = element.x;
    const elementTop = element.y;
    const elementRight = element.x + element.width;
    const elementBottom = element.y + element.height;

    // Poignées de redimensionnement (coordonnées relatives au canvas)
    const handles = [
      { name: 'nw', x: elementLeft, y: elementTop },
      { name: 'ne', x: elementRight, y: elementTop },
      { name: 'sw', x: elementLeft, y: elementBottom },
      { name: 'se', x: elementRight, y: elementBottom },
      { name: 'n', x: elementLeft + element.width / 2, y: elementTop },
      { name: 's', x: elementLeft + element.width / 2, y: elementBottom },
      { name: 'w', x: elementLeft, y: elementTop + element.height / 2 },
      { name: 'e', x: elementRight, y: elementTop + element.height / 2 }
    ];

    const clickedHandle = handles.find(handle =>
      clickX >= handle.x - handleSize/2 && clickX <= handle.x + handleSize/2 &&
      clickY >= handle.y - handleSize/2 && clickY <= handle.y + handleSize/2
    );

    if (clickedHandle) {
      console.log('Clicked handle:', clickedHandle.name);
      const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      const canvasRect = canvas.getBoundingClientRect();
      resize.handleResizeStart(e, clickedHandle.name, {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    } else {
      console.log('Starting drag for element:', element.id);
      // Démarrer le drag avec les coordonnées relatives au canvas
      const canvas = elementRef.current.closest('.canvas-zoom-wrapper');
      const canvasRect = canvas.getBoundingClientRect();
      
      dragAndDrop.handleMouseDown(e, element.id, {
        left: element.x,
        top: element.y,
        width: element.width,
        height: element.height
      }, canvasRect, zoom);
    }
  }, [isSelected, onSelect, element, zoom, resize, dragAndDrop]);

  // Gestionnaire de double-clic pour édition
  const handleDoubleClick = useCallback((e) => {
    e.stopPropagation();

    if (element.type === 'text') {
      const newText = prompt('Modifier le texte:', element.text || '');
      if (newText !== null) {
        onUpdate({ text: newText });
      }
    }
  }, [element, onUpdate]);

  // Gestionnaire de clic droit
  const handleContextMenuEvent = useCallback((e) => {
    e.preventDefault();
    e.stopPropagation();
    if (onContextMenu) {
      onContextMenu(e, element.id);
    }
  }, [onContextMenu, element.id]);

  // Rendu de l'élément selon son type
  const renderElement = () => {
    const style = {
      position: 'absolute',
      left: element.x * zoom,
      top: element.y * zoom,
      width: element.width * zoom,
      height: element.height * zoom,
      zIndex: element.zIndex || 0
    };

    switch (element.type) {
      case 'text':
        return (
          <div
            style={{
              ...style,
              fontSize: (element.fontSize || 14) * zoom,
              fontFamily: element.fontFamily || 'Arial',
              color: element.color || '#1e293b',
              fontWeight: element.fontWeight || 'normal',
              textAlign: element.textAlign || 'left',
              lineHeight: 1.2,
              overflow: 'hidden',
              padding: '2px'
            }}
            onDoubleClick={handleDoubleClick}
          >
            {element.text || 'Texte'}
          </div>
        );

      case 'rectangle':
        return (
          <div
            style={{
              ...style,
              backgroundColor: element.fillColor || 'transparent',
              border: `${element.borderWidth || 1}px solid ${element.borderColor || '#6b7280'}`,
              borderRadius: element.borderRadius || 0
            }}
          />
        );

      case 'image':
        return (
          <div
            style={{
              ...style,
              backgroundColor: '#f8f9fa',
              border: '1px dashed #ccc',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: 12 * zoom,
              color: '#666'
            }}
          >
            📷 Image
          </div>
        );

      case 'line':
        return (
          <div
            style={{
              ...style,
              borderTop: `${element.lineWidth || 1}px solid ${element.lineColor || '#6b7280'}`,
              height: 0
            }}
          />
        );

      default:
        return (
          <div
            style={{
              ...style,
              backgroundColor: '#f1f3f4',
              border: '1px solid #ccc',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: 12 * zoom
            }}
          >
            {element.type}
          </div>
        );
    }
  };

  return (
    <>
      {/* Élément principal */}
      <div
        ref={elementRef}
        className={`canvas-element ${isSelected ? 'selected' : ''}`}
        style={{
          position: 'absolute',
          left: element.x * zoom,
          top: element.y * zoom,
          width: element.width * zoom,
          height: element.height * zoom,
          cursor: dragAndDrop.isDragging ? 'grabbing' : 'grab',
          userSelect: 'none'
        }}
        onMouseDown={handleMouseDown}
        onContextMenu={handleContextMenuEvent}
        draggable={false}
      >
        {renderElement()}
      </div>

      {/* Poignées de redimensionnement */}
      {isSelected && (
        <>
          {/* Coins */}
          <div
            className="resize-handle nw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'nw-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'nw', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle ne"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'ne-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'ne', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle sw"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'sw-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'sw', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle se"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'se-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'se', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />

          {/* Côtés */}
          <div
            className="resize-handle n"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: element.y * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'n-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'n', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle s"
            style={{
              position: 'absolute',
              left: (element.x + element.width / 2) * zoom - 4,
              top: (element.y + element.height) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 's-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 's', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle w"
            style={{
              position: 'absolute',
              left: element.x * zoom - 4,
              top: (element.y + element.height / 2) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'w-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'w', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
          <div
            className="resize-handle e"
            style={{
              position: 'absolute',
              left: (element.x + element.width) * zoom - 4,
              top: (element.y + element.height / 2) * zoom - 4,
              width: 8,
              height: 8,
              backgroundColor: '#007cba',
              border: '1px solid white',
              cursor: 'e-resize',
              pointerEvents: 'auto'
            }}
            onMouseDown={(e) => resize.handleResizeStart(e, 'e', {
              x: element.x,
              y: element.y,
              width: element.width,
              height: element.height
            })}
          />
        </>
      )}
    </>
  );
};