import { useState, useCallback, useRef, useEffect } from 'react';

export const useDragAndDrop = ({
  onElementMove,
  onElementDrop,
  snapToGrid = true,
  gridSize = 10,
  zoom = 1,
  canvasWidth = 595,
  canvasHeight = 842,
  canvasRef = null
}) => {
  const [isDragging, setIsDragging] = useState(false);
  const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });
  const dragStartPos = useRef({ x: 0, y: 0 });
  const currentDragData = useRef(null);

  const snapToGridValue = useCallback((value) => {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);

  // Nettoyer les event listeners quand le composant se démonte
  useEffect(() => {
    return () => {
      if (currentDragData.current) {
        document.removeEventListener('mousemove', currentDragData.current.handleMouseMove);
        document.removeEventListener('mouseup', currentDragData.current.handleMouseUp);
        currentDragData.current = null;
      }
    };
  }, []);

  const handleMouseDown = useCallback((e, elementId, elementRect, zoomLevel = 1) => {
    if (e.button !== 0) return; // Only left mouse button

    e.preventDefault();
    setIsDragging(true);

    // Obtenir le canvasRect depuis canvasRef si disponible
    const canvasRect = canvasRef?.current?.getBoundingClientRect() || { left: 0, top: 0 };
    const currentZoom = zoomLevel || zoom || 1;

    const startX = (e.clientX - canvasRect.left) / currentZoom;
    const startY = (e.clientY - canvasRect.top) / currentZoom;
    let lastMouseX = startX;
    let lastMouseY = startY;

    dragStartPos.current = {
      x: startX - elementRect.left,
      y: startY - elementRect.top
    };

    const handleMouseMove = (moveEvent) => {
      const mouseX = (moveEvent.clientX - canvasRect.left) / currentZoom;
      const mouseY = (moveEvent.clientY - canvasRect.top) / currentZoom;
      lastMouseX = mouseX;
      lastMouseY = mouseY;

      const deltaX = mouseX - startX;
      const deltaY = mouseY - startY;

      const effectiveCanvasWidth = canvasRect ? canvasRect.width / zoomLevel : canvasWidth;
      const effectiveCanvasHeight = canvasRect ? canvasRect.height / zoomLevel : canvasHeight;

      const newX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapToGridValue(elementRect.left + deltaX)));
      const newY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapToGridValue(elementRect.top + deltaY)));

      setDragOffset({ x: newX - elementRect.left, y: newY - elementRect.top });

      if (onElementMove) {
        onElementMove(elementId, { x: newX, y: newY });
      }
    };

    const handleMouseUp = () => {
      setIsDragging(false);
      setDragOffset({ x: 0, y: 0 });

      if (onElementDrop) {
        const effectiveCanvasWidth = canvasRect ? canvasRect.width / zoomLevel : canvasWidth;
        const effectiveCanvasHeight = canvasRect ? canvasRect.height / zoomLevel : canvasHeight;

        const finalX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapToGridValue(elementRect.left + (lastMouseX - startX))));
        const finalY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapToGridValue(elementRect.top + (lastMouseY - startY))));
        onElementDrop(elementId, { x: finalX, y: finalY });
      }

      // Nettoyer les event listeners
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
      currentDragData.current = null;
    };

    // Stocker les références pour le nettoyage
    currentDragData.current = { handleMouseMove, handleMouseUp };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  }, [snapToGridValue, onElementMove, onElementDrop, zoom, canvasWidth, canvasHeight]);

  const handleDragStart = useCallback((e, elementId, elementRect) => {
    e.dataTransfer.setData('text/plain', elementId);
    e.dataTransfer.effectAllowed = 'move';

    dragStartPos.current = {
      x: e.clientX - elementRect.left,
      y: e.clientY - elementRect.top
    };
  }, []);

  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);

  const handleDrop = useCallback((e, elementRect) => {
    e.preventDefault();

    const elementId = e.dataTransfer.getData('text/plain');
    if (!elementId) return;

    // Obtenir le canvasRect depuis canvasRef si disponible
    const canvasRect = canvasRef?.current?.getBoundingClientRect() || { left: 0, top: 0 };

    const dropX = (e.clientX - canvasRect.left - dragStartPos.current.x) / zoom;
    const dropY = (e.clientY - canvasRect.top - dragStartPos.current.y) / zoom;

    const effectiveCanvasWidth = canvasRect ? canvasRect.width / zoom : canvasWidth;
    const effectiveCanvasHeight = canvasRect ? canvasRect.height / zoom : canvasHeight;

    const snappedX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapToGridValue(dropX)));
    const snappedY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapToGridValue(dropY)));

    if (onElementDrop) {
      onElementDrop(elementId, { x: snappedX, y: snappedY });
    }
  }, [snapToGridValue, onElementDrop, zoom, canvasWidth, canvasHeight, canvasRef]);

  return {
    isDragging,
    dragOffset,
    handleMouseDown,
    handleDragStart,
    handleDragOver,
    handleDrop
  };
};