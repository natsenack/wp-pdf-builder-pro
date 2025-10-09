import { useState, useCallback, useRef } from 'react';

export const useDragAndDrop = ({
  onElementMove,
  onElementDrop,
  snapToGrid = true,
  gridSize = 10
}) => {
  const [isDragging, setIsDragging] = useState(false);
  const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });
  const dragStartPos = useRef({ x: 0, y: 0 });

  const snapToGridValue = useCallback((value) => {
    if (!snapToGrid) return value;
    return Math.round(value / gridSize) * gridSize;
  }, [snapToGrid, gridSize]);

  const handleMouseDown = useCallback((e, elementId, elementRect) => {
    if (e.button !== 0) return; // Only left mouse button

    e.preventDefault();
    setIsDragging(true);

    const startX = e.clientX;
    const startY = e.clientY;

    dragStartPos.current = {
      x: startX - elementRect.left,
      y: startY - elementRect.top
    };

    const handleMouseMove = (moveEvent) => {
      const deltaX = moveEvent.clientX - startX;
      const deltaY = moveEvent.clientY - startY;

      const newX = snapToGridValue(elementRect.left + deltaX);
      const newY = snapToGridValue(elementRect.top + deltaY);

      setDragOffset({ x: newX - elementRect.left, y: newY - elementRect.top });

      if (onElementMove) {
        onElementMove(elementId, { x: newX, y: newY });
      }
    };

    const handleMouseUp = () => {
      setIsDragging(false);
      setDragOffset({ x: 0, y: 0 });

      if (onElementDrop) {
        const finalX = snapToGridValue(elementRect.left + (e.clientX - startX));
        const finalY = snapToGridValue(elementRect.top + (e.clientY - startY));
        onElementDrop(elementId, { x: finalX, y: finalY });
      }

      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
    };

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  }, [snapToGridValue, onElementMove, onElementDrop]);

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

  const handleDrop = useCallback((e, canvasRect) => {
    e.preventDefault();

    const elementId = e.dataTransfer.getData('text/plain');
    if (!elementId) return;

    const dropX = e.clientX - canvasRect.left - dragStartPos.current.x;
    const dropY = e.clientY - canvasRect.top - dragStartPos.current.y;

    const snappedX = snapToGridValue(dropX);
    const snappedY = snapToGridValue(dropY);

    if (onElementDrop) {
      onElementDrop(elementId, { x: snappedX, y: snappedY });
    }
  }, [snapToGridValue, onElementDrop]);

  return {
    isDragging,
    dragOffset,
    handleMouseDown,
    handleDragStart,
    handleDragOver,
    handleDrop
  };
};