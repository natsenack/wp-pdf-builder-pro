import { useState, useCallback, useRef } from 'react';

export const useResize = ({
  onElementResize,
  snapToGrid = true,
  gridSize = 10,
  minWidth = 20,
  minHeight = 20,
  zoom = 1,
  canvasRef = null,
  canvasWidth = 595,
  canvasHeight = 842
}) => {
  const [isResizing, setIsResizing] = useState(false);
  const [resizeHandle, setResizeHandle] = useState(null);
  const resizeStartPos = useRef({ x: 0, y: 0 });
  const originalRect = useRef({ x: 0, y: 0, width: 0, height: 0 });

  const snapToGridValue = useCallback((value) => {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);

  const handleResizeStart = useCallback((e, handle, elementRect, zoomLevel = 1) => {
    console.log('🔧 handleResizeStart called:', {
      handle,
      elementRect,
      zoomLevel,
      eventType: e.type,
      clientX: e.clientX,
      clientY: e.clientY,
      canvasRef: !!canvasRef?.current
    });
    e.preventDefault();
    e.stopPropagation();

    setIsResizing(true);
    setResizeHandle(handle);

    // Obtenir le canvasRect depuis canvasRef si disponible
    const canvasRect = canvasRef?.current?.getBoundingClientRect() || { left: 0, top: 0 };
    const currentZoom = zoomLevel || zoom || 1;
    resizeStartPos.current = {
      x: (e.clientX - canvasRect.left) / currentZoom,
      y: (e.clientY - canvasRect.top) / currentZoom
    };
    originalRect.current = { ...elementRect };

    const handleMouseMove = (moveEvent) => {
      console.log('🔄 handleMouseMove called:', {
        clientX: moveEvent.clientX,
        clientY: moveEvent.clientY,
        deltaX: (moveEvent.clientX - canvasRect.left) / currentZoom - resizeStartPos.current.x,
        deltaY: (moveEvent.clientY - canvasRect.top) / currentZoom - resizeStartPos.current.y,
        handle,
        isResizing
      });
      const mouseX = (moveEvent.clientX - canvasRect.left) / currentZoom;
      const mouseY = (moveEvent.clientY - canvasRect.top) / currentZoom;
      const deltaX = mouseX - resizeStartPos.current.x;
      const deltaY = mouseY - resizeStartPos.current.y;

      let newRect = { ...originalRect.current };

      switch (handle) {
        case 'nw':
          newRect.x = snapToGridValue(originalRect.current.x + deltaX);
          newRect.y = snapToGridValue(originalRect.current.y + deltaY);
          newRect.width = snapToGridValue(originalRect.current.width - deltaX);
          newRect.height = snapToGridValue(originalRect.current.height - deltaY);
          break;

        case 'ne':
          newRect.y = snapToGridValue(originalRect.current.y + deltaY);
          newRect.width = snapToGridValue(originalRect.current.width + deltaX);
          newRect.height = snapToGridValue(originalRect.current.height - deltaY);
          break;

        case 'sw':
          newRect.x = snapToGridValue(originalRect.current.x + deltaX);
          newRect.width = snapToGridValue(originalRect.current.width - deltaX);
          newRect.height = snapToGridValue(originalRect.current.height + deltaY);
          break;

        case 'se':
          newRect.width = snapToGridValue(originalRect.current.width + deltaX);
          newRect.height = snapToGridValue(originalRect.current.height + deltaY);
          break;

        case 'n':
          newRect.y = snapToGridValue(originalRect.current.y + deltaY);
          newRect.height = snapToGridValue(originalRect.current.height - deltaY);
          break;

        case 's':
          newRect.height = snapToGridValue(originalRect.current.height + deltaY);
          break;

        case 'w':
          newRect.x = snapToGridValue(originalRect.current.x + deltaX);
          newRect.width = snapToGridValue(originalRect.current.width - deltaX);
          break;

        case 'e':
          newRect.width = snapToGridValue(originalRect.current.width + deltaX);
          break;

        default:
          break;
      }

      // Appliquer les contraintes de taille minimale
      if (newRect.width < minWidth) {
        if (handle.includes('w')) {
          newRect.x = originalRect.current.x + originalRect.current.width - minWidth;
        }
        newRect.width = minWidth;
      }

      if (newRect.height < minHeight) {
        if (handle.includes('n')) {
          newRect.y = originalRect.current.y + originalRect.current.height - minHeight;
        }
        newRect.height = minHeight;
      }

      // Appliquer les contraintes du canvas
      const effectiveCanvasWidth = canvasWidth;
      const effectiveCanvasHeight = canvasHeight;

      newRect.x = Math.max(0, Math.min(effectiveCanvasWidth - newRect.width, newRect.x));
      newRect.y = Math.max(0, Math.min(effectiveCanvasHeight - newRect.height, newRect.y));

      if (onElementResize) {
        onElementResize(newRect);
      }
    };

    const handleMouseUp = () => {
      console.log('🛑 handleMouseUp called: resizing ended');
      setIsResizing(false);
      setResizeHandle(null);

      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
    };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
    console.log('🎧 Event listeners added for resize:', { handle, isResizing: true });
  }, [snapToGridValue, minWidth, minHeight, onElementResize, zoom, canvasWidth, canvasHeight]);

  return {
    isResizing,
    resizeHandle,
    handleResizeStart
  };
};