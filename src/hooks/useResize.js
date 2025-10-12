import { useState, useCallback, useRef, useEffect } from 'react';

/**
 * Hook pour gérer le redimensionnement d'éléments
 * @param {Object} options - Options du hook
 * @param {Function} options.onResize - Callback appelé pendant le redimensionnement
 * @param {Function} options.onResizeEnd - Callback appelé à la fin du redimensionnement
 * @param {boolean} options.snapToGrid - Si l'accrochage à la grille est activé
 * @param {number} options.gridSize - Taille de la grille
 * @param {number} options.minWidth - Largeur minimale
 * @param {number} options.minHeight - Hauteur minimale
 * @param {Object} options.canvasRef - Référence au canvas
 * @returns {Object} - État et méthodes de redimensionnement
 */
export const useResize = ({
  onResize,
  onResizeEnd,
  snapToGrid = true,
  gridSize = 10,
  minWidth = 20,
  minHeight = 20,
  canvasRef = null
} = {}) => {
  const [isResizing, setIsResizing] = useState(false);
  const [activeHandle, setActiveHandle] = useState(null);
  const resizeDataRef = useRef({
    startPos: { x: 0, y: 0 },
    originalRect: { x: 0, y: 0, width: 0, height: 0 },
    zoom: 1
  });

  // Fonction d'accrochage à la grille
  const snapToGridValue = useCallback((value) => {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);

  // Calculer le rectangle après redimensionnement
  const calculateNewRect = useCallback((handle, deltaX, deltaY, originalRect) => {
    let newRect = { ...originalRect };

    switch (handle) {
      case 'nw':
        newRect.x = snapToGridValue(originalRect.x + deltaX);
        newRect.y = snapToGridValue(originalRect.y + deltaY);
        newRect.width = Math.max(minWidth, snapToGridValue(originalRect.width - deltaX));
        newRect.height = Math.max(minHeight, snapToGridValue(originalRect.height - deltaY));
        break;

      case 'ne':
        newRect.y = snapToGridValue(originalRect.y + deltaY);
        newRect.width = Math.max(minWidth, snapToGridValue(originalRect.width + deltaX));
        newRect.height = Math.max(minHeight, snapToGridValue(originalRect.height - deltaY));
        break;

      case 'sw':
        newRect.x = snapToGridValue(originalRect.x + deltaX);
        newRect.width = Math.max(minWidth, snapToGridValue(originalRect.width - deltaX));
        newRect.height = Math.max(minHeight, snapToGridValue(originalRect.height + deltaY));
        break;

      case 'se':
        newRect.width = Math.max(minWidth, snapToGridValue(originalRect.width + deltaX));
        newRect.height = Math.max(minHeight, snapToGridValue(originalRect.height + deltaY));
        break;

      case 'n':
        newRect.y = snapToGridValue(originalRect.y + deltaY);
        newRect.height = Math.max(minHeight, snapToGridValue(originalRect.height - deltaY));
        break;

      case 's':
        newRect.height = Math.max(minHeight, snapToGridValue(originalRect.height + deltaY));
        break;

      case 'w':
        newRect.x = snapToGridValue(originalRect.x + deltaX);
        newRect.width = Math.max(minWidth, snapToGridValue(originalRect.width - deltaX));
        break;

      case 'e':
        newRect.width = Math.max(minWidth, snapToGridValue(originalRect.width + deltaX));
        break;

      default:
        break;
    }

    return newRect;
  }, [snapToGridValue, minWidth, minHeight]);

  // Démarrer le redimensionnement
  const startResize = useCallback((event, handle, elementRect, zoom = 1) => {
    event.preventDefault();
    event.stopPropagation();

    setIsResizing(true);
    setActiveHandle(handle);

    // Obtenir les coordonnées relatives au canvas
    const canvasRect = canvasRef?.current?.getBoundingClientRect() || { left: 0, top: 0 };
    resizeDataRef.current = {
      startPos: {
        x: (event.clientX - canvasRect.left) / zoom,
        y: (event.clientY - canvasRect.top) / zoom
      },
      originalRect: { ...elementRect },
      zoom
    };

    // Ajouter les écouteurs d'événements globaux
    const handleMouseMove = (moveEvent) => {
      const canvasRect = canvasRef?.current?.getBoundingClientRect() || { left: 0, top: 0 };
      const mouseX = (moveEvent.clientX - canvasRect.left) / resizeDataRef.current.zoom;
      const mouseY = (moveEvent.clientY - canvasRect.top) / resizeDataRef.current.zoom;

      const deltaX = mouseX - resizeDataRef.current.startPos.x;
      const deltaY = mouseY - resizeDataRef.current.startPos.y;

      const newRect = calculateNewRect(handle, deltaX, deltaY, resizeDataRef.current.originalRect);

      onResize?.(newRect);
    };

    const handleMouseUp = () => {
      setIsResizing(false);
      setActiveHandle(null);

      // Calculer le rectangle final
      const canvasRect = canvasRef?.current?.getBoundingClientRect() || { left: 0, top: 0 };
      const mouseX = (event.clientX - canvasRect.left) / resizeDataRef.current.zoom;
      const mouseY = (event.clientY - canvasRect.top) / resizeDataRef.current.zoom;

      const deltaX = mouseX - resizeDataRef.current.startPos.x;
      const deltaY = mouseY - resizeDataRef.current.startPos.y;

      const finalRect = calculateNewRect(handle, deltaX, deltaY, resizeDataRef.current.originalRect);

      onResizeEnd?.(finalRect);

      // Nettoyer les écouteurs
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
    };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  }, [canvasRef, calculateNewRect, onResize, onResizeEnd]);

  // Annuler le redimensionnement
  const cancelResize = useCallback(() => {
    setIsResizing(false);
    setActiveHandle(null);
  }, []);

  // Effet pour gérer l'annulation avec Échap
  useEffect(() => {
    const handleKeyDown = (event) => {
      if (event.key === 'Escape' && isResizing) {
        cancelResize();
      }
    };

    if (isResizing) {
      document.addEventListener('keydown', handleKeyDown);
      return () => document.removeEventListener('keydown', handleKeyDown);
    }
  }, [isResizing, cancelResize]);

  return {
    // État
    isResizing,
    activeHandle,

    // Méthodes
    startResize,
    cancelResize,

    // Utilitaires
    calculateNewRect
  };
};