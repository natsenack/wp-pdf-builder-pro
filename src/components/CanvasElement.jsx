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
    e.stopPropagation();

    if (!isSelected) {
      onSelect();
      return;
    }

    // Vérifier si on clique sur une poignée de redimensionnement
    const rect = elementRef.current.getBoundingClientRect();
    const clickX = e.clientX - rect.left;
    const clickY = e.clientY - rect.top;

    const handleSize = 8;
    const elementRect = {
      x: element.x,
      y: element.y,
      width: element.width,
      height: element.height
    };

    // Poignées de redimensionnement
    const handles = [
      { name: 'nw', x: 0, y: 0 },
      { name: 'ne', x: element.width * zoom - handleSize, y: 0 },
      { name: 'sw', x: 0, y: element.height * zoom - handleSize },
      { name: 'se', x: element.width * zoom - handleSize, y: element.height * zoom - handleSize },
      { name: 'n', x: (element.width * zoom - handleSize) / 2, y: 0 },
      { name: 's', x: (element.width * zoom - handleSize) / 2, y: element.height * zoom - handleSize },
      { name: 'w', x: 0, y: (element.height * zoom - handleSize) / 2 },
      { name: 'e', x: element.width * zoom - handleSize, y: (element.height * zoom - handleSize) / 2 }
    ];

    const clickedHandle = handles.find(handle =>
      clickX >= handle.x && clickX <= handle.x + handleSize &&
      clickY >= handle.y && clickY <= handle.y + handleSize
    );

    if (clickedHandle) {
      resize.handleResizeStart(e, clickedHandle.name, elementRect);
    } else {
      // Démarrer le drag
      dragAndDrop.handleMouseDown(e, element.id, elementRect);
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
      left: 0,
      top: 0,
      width: '100%',
      height: '100%',
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
              color: element.color || '#000000',
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
              border: `${element.borderWidth || 1}px solid ${element.borderColor || '#000000'}`,
              borderRadius: element.borderRadius || 0
            }}
          />
        );

      case 'image':
        return (
          <div
            style={{
              ...style,
              backgroundColor: '#f0f0f0',
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
              borderTop: `${element.lineWidth || 1}px solid ${element.lineColor || '#000000'}`,
              height: 0
            }}
          />
        );

      default:
        return (
          <div
            style={{
              ...style,
              backgroundColor: '#e0e0e0',
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