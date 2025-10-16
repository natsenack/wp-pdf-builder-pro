import { useState, useCallback, useRef } from 'react';

export const useResize = ({
  onElementResize,
  snapToGrid = true,
  gridSize = 10,
  minWidth = 20,
  minHeight = 20,
  zoom = 1,
  canvasRect = null,
  canvasWidth = 595,
  canvasHeight = 842,
  guides = { horizontal: [], vertical: [] },
  snapToGuides = true,
  elementType = null
}) => {
  const [isResizing, setIsResizing] = useState(false);
  const [resizeHandle, setResizeHandle] = useState(null);
  const resizeStartPos = useRef({ x: 0, y: 0 });
  const originalRect = useRef({ x: 0, y: 0, width: 0, height: 0 });

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

  const handleResizeStart = useCallback((e, handle, elementRect, canvasRectParam = null, zoomLevel = 1) => {
    e.preventDefault();
    e.stopPropagation();

    // Vérifier que l'élément source existe encore dans le DOM
    if (!e.target || !e.target.isConnected) {
      console.warn('Resize handle target no longer exists in DOM');
      return;
    }

    // Vérifier que l'élément parent existe encore
    if (!e.target.parentNode || !e.target.parentNode.isConnected) {
      console.warn('Resize handle parent no longer exists in DOM');
      return;
    }

    setIsResizing(true);
    setResizeHandle(handle);

    // Ajuster les coordonnées pour le zoom
    const currentCanvasRect = canvasRectParam || canvasRect || { left: 0, top: 0 };
    const currentZoom = zoomLevel || zoom || 1;
    resizeStartPos.current = {
      x: (e.clientX - currentCanvasRect.left) / currentZoom,
      y: (e.clientY - currentCanvasRect.top) / currentZoom
    };
    originalRect.current = { ...elementRect };

    const handleMouseMove = (moveEvent) => {
      const mouseX = (moveEvent.clientX - currentCanvasRect.left) / currentZoom;
      const mouseY = (moveEvent.clientY - currentCanvasRect.top) / currentZoom;
      const deltaX = mouseX - resizeStartPos.current.x;
      const deltaY = mouseY - resizeStartPos.current.y;

      let newRect = { ...originalRect.current };

      // Pour les dividers et lignes, empêcher la modification de la hauteur seulement
      const isFixedHeight = elementType === 'divider' || elementType === 'line';

      switch (handle) {
        case 'nw':
          if (!isFixedHeight) {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          } else {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
          }
          break;

        case 'ne':
          if (!isFixedHeight) {
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          } else {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
          }
          break;

        case 'sw':
          if (!isFixedHeight) {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          } else {
            newRect.x = snapValue(originalRect.current.x + deltaX, false);
            newRect.width = snapValue(originalRect.current.width - deltaX, false);
          }
          break;

        case 'se':
          if (!isFixedHeight) {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          } else {
            newRect.width = snapValue(originalRect.current.width + deltaX, false);
          }
          break;

        case 'n':
          if (!isFixedHeight) {
            newRect.y = snapValue(originalRect.current.y + deltaY, true);
            newRect.height = snapValue(originalRect.current.height - deltaY, true);
          }
          break;

        case 's':
          if (!isFixedHeight) {
            newRect.height = snapValue(originalRect.current.height + deltaY, true);
          }
          break;

        case 'w':
          newRect.x = snapValue(originalRect.current.x + deltaX, false);
          newRect.width = snapValue(originalRect.current.width - deltaX, false);
          break;

        case 'e':
          newRect.width = snapValue(originalRect.current.width + deltaX, false);
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
      const effectiveCanvasWidth = canvasRectParam ? canvasRectParam.width / currentZoom : canvasWidth;
      const effectiveCanvasHeight = canvasRectParam ? canvasRectParam.height / currentZoom : canvasHeight;

      newRect.x = Math.max(0, Math.min(effectiveCanvasWidth - newRect.width, newRect.x));
      newRect.y = Math.max(0, Math.min(effectiveCanvasHeight - newRect.height, newRect.y));

      if (onElementResize) {
        onElementResize(newRect);
      }
    };

    const handleMouseUp = () => {
      setIsResizing(false);
      setResizeHandle(null);

      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
    };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  }, [snapToGridValue, minWidth, minHeight, onElementResize, zoom, canvasRect, canvasWidth, canvasHeight]);

  return {
    isResizing,
    resizeHandle,
    handleResizeStart
  };
};
