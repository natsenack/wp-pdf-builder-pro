import { useState, useCallback, useRef } from 'react';

export const useResize = ({
  onElementResize,
  snapToGrid = true,
  gridSize = 10,
  minWidth = 20,
  minHeight = 20
}) => {
  const [isResizing, setIsResizing] = useState(false);
  const [resizeHandle, setResizeHandle] = useState(null);
  const resizeStartPos = useRef({ x: 0, y: 0 });
  const originalRect = useRef({ x: 0, y: 0, width: 0, height: 0 });

  const snapToGridValue = useCallback((value) => {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);

  const handleResizeStart = useCallback((e, handle, elementRect) => {
    console.log('useResize handleResizeStart', handle, elementRect);
    e.preventDefault();
    e.stopPropagation();

    setIsResizing(true);
    setResizeHandle(handle);

    resizeStartPos.current = { x: e.clientX, y: e.clientY };
    originalRect.current = { ...elementRect };

    const handleMouseMove = (moveEvent) => {
      const deltaX = moveEvent.clientX - resizeStartPos.current.x;
      const deltaY = moveEvent.clientY - resizeStartPos.current.y;

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
  }, [snapToGridValue, minWidth, minHeight, onElementResize]);

  return {
    isResizing,
    resizeHandle,
    handleResizeStart
  };
};