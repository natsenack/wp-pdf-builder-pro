import { useState, useCallback, useRef, useEffect } from 'react';

export const useDragAndDrop = ({
  onElementMove,
  onElementDrop,
  snapToGrid = true,
  gridSize = 10,
  zoom = 1,
  canvasWidth = 595,
  canvasHeight = 842,
  guides = { horizontal: [], vertical: [] },
  snapToGuides = true
}) => {
  const [isDragging, setIsDragging] = useState(false);
  const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });
  const dragStartPos = useRef({ x: 0, y: 0 });
  const currentDragData = useRef(null);

  const snapToGridValue = useCallback((value) => {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);

  const snapToGuidesValue = useCallback((value, isHorizontal = true) => {
    if (!snapToGuides) return value;

    const guideArray = isHorizontal ? guides.horizontal : guides.vertical;
    const snapTolerance = 5; // pixels

    for (const guide of guideArray) {
      if (Math.abs(value - guide) <= snapTolerance) {
        return guide;
      }
    }

    return value;
  }, [snapToGuides, guides]);

  const snapValue = useCallback((value, isHorizontal = true) => {
    let snapped = value;

    // Appliquer l'aimantation à la grille d'abord
    snapped = snapToGridValue(snapped);

    // Puis appliquer l'aimantation aux guides
    snapped = snapToGuidesValue(snapped, isHorizontal);

    return snapped;
  }, [snapToGridValue, snapToGuidesValue]);

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

  const handleMouseDown = useCallback((e, elementId, elementRect, canvasRect = null, zoomLevel = 1) => {
    if (e.button !== 0) return; // Only left mouse button

    // Vérifier que l'élément source existe encore dans le DOM
    if (!e.target || !e.target.isConnected) {
      console.warn('Drag target no longer exists in DOM');
      return;
    }

    // Vérifier que l'élément parent existe encore
    if (!e.target.parentNode || !e.target.parentNode.isConnected) {
      console.warn('Drag target parent no longer exists in DOM');
      return;
    }

    e.preventDefault();
    setIsDragging(true);

    // Obtenir le canvasRect dynamiquement si non fourni
    const currentCanvasRect = canvasRect || { left: 0, top: 0, width: canvasWidth, height: canvasHeight };
    const currentZoom = zoomLevel || zoom || 1;

    const startX = (e.clientX - currentCanvasRect.left) / currentZoom;
    const startY = (e.clientY - currentCanvasRect.top) / currentZoom;
    let lastMouseX = startX;
    let lastMouseY = startY;

    dragStartPos.current = {
      x: startX - elementRect.left,
      y: startY - elementRect.top
    };

    const handleMouseMove = (moveEvent) => {
      try {
        // Vérifier que les données de drag existent toujours
        if (!currentDragData.current) {
          console.warn('Drag data no longer exists during move');
          return;
        }
        const mouseX = (moveEvent.clientX - currentCanvasRect.left) / currentZoom;
        const mouseY = (moveEvent.clientY - currentCanvasRect.top) / currentZoom;
        lastMouseX = mouseX;
        lastMouseY = mouseY;

        const deltaX = mouseX - startX;
        const deltaY = mouseY - startY;

        const effectiveCanvasWidth = canvasRect ? canvasRect.width / zoomLevel : canvasWidth;
        const effectiveCanvasHeight = canvasRect ? canvasRect.height / zoomLevel : canvasHeight;

        const newX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(elementRect.left + deltaX, false)));
        const newY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(elementRect.top + deltaY, true)));

        setDragOffset({ x: newX - elementRect.left, y: newY - elementRect.top });

        if (onElementMove) {
          onElementMove(elementId, { x: newX, y: newY });
        }
      } catch (error) {
        console.error('Error in handleMouseMove:', error);
      }
    };

    const handleMouseUp = () => {
      try {
        // Vérifier que les données de drag existent toujours
        if (!currentDragData.current) {
          console.warn('Drag data no longer exists during drop');
          setIsDragging(false);
          setDragOffset({ x: 0, y: 0 });
          return;
        }

        setIsDragging(false);
        setDragOffset({ x: 0, y: 0 });

        if (onElementDrop) {
          const effectiveCanvasWidth = canvasRect ? canvasRect.width / zoomLevel : canvasWidth;
          const effectiveCanvasHeight = canvasRect ? canvasRect.height / zoomLevel : canvasHeight;

          const finalX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(elementRect.left + (lastMouseX - startX), false)));
          const finalY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(elementRect.top + (lastMouseY - startY), true)));
          onElementDrop(elementId, { x: finalX, y: finalY });
        }

        // Nettoyer les event listeners
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
        currentDragData.current = null;
      } catch (error) {
        console.error('Error in handleMouseUp:', error);
      }
    };

    // Stocker les références pour le nettoyage
    currentDragData.current = { handleMouseMove, handleMouseUp };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  }, [snapToGridValue, onElementMove, onElementDrop, zoom, canvasWidth, canvasHeight]);

  const handleDragStart = useCallback((e, elementId, elementRect) => {
    try {
      e.dataTransfer.setData('text/plain', elementId);
      e.dataTransfer.effectAllowed = 'move';

      dragStartPos.current = {
        x: e.clientX - elementRect.left,
        y: e.clientY - elementRect.top
      };
    } catch (error) {
      console.error('Error in handleDragStart:', error);
    }
  }, []);

  const handleDragOver = useCallback((e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  }, []);

  const handleDrop = useCallback((e, canvasRect, elementRect) => {
    try {
      e.preventDefault();

      const elementId = e.dataTransfer.getData('text/plain');
      if (!elementId) return;

      const dropX = (e.clientX - canvasRect.left - dragStartPos.current.x) / zoom;
      const dropY = (e.clientY - canvasRect.top - dragStartPos.current.y) / zoom;

      const effectiveCanvasWidth = canvasRect ? canvasRect.width / zoom : canvasWidth;
      const effectiveCanvasHeight = canvasRect ? canvasRect.height / zoom : canvasHeight;

      const snappedX = Math.max(0, Math.min(effectiveCanvasWidth - elementRect.width, snapValue(dropX, false)));
      const snappedY = Math.max(0, Math.min(effectiveCanvasHeight - elementRect.height, snapValue(dropY, true)));

      if (onElementDrop) {
        onElementDrop(elementId, { x: snappedX, y: snappedY });
      }
    } catch (error) {
      console.error('Error in handleDrop:', error);
    }
  }, [snapToGridValue, onElementDrop, zoom, canvasWidth, canvasHeight]);

  return {
    isDragging,
    dragOffset,
    handleMouseDown,
    handleDragStart,
    handleDragOver,
    handleDrop
  };
};