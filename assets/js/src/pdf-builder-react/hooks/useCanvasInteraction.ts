import { useCallback, useRef } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';

interface UseCanvasInteractionProps {
  canvasRef: React.RefObject<HTMLCanvasElement>;
}

export const useCanvasInteraction = ({ canvasRef }: UseCanvasInteractionProps) => {
  const { state, dispatch } = useBuilder();

  // États pour le drag et resize
  const isDraggingRef = useRef(false);
  const isResizingRef = useRef(false);
  const dragStartRef = useRef({ x: 0, y: 0 });
  const selectedElementRef = useRef<string | null>(null);
  const resizeHandleRef = useRef<string | null>(null);

  // Gestionnaire de clic pour la sélection
  const handleCanvasClick = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left - state.canvas.pan.x) / state.canvas.zoom;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / state.canvas.zoom;

    // Trouver l'élément cliqué
    const clickedElement = state.elements.find(el =>
      x >= el.x && x <= el.x + el.width &&
      y >= el.y && y <= el.y + el.height
    );

    if (clickedElement) {
      dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
      selectedElementRef.current = clickedElement.id;
    } else {
      dispatch({ type: 'CLEAR_SELECTION' });
      selectedElementRef.current = null;
    }
  }, [state, dispatch, canvasRef]);

  // Gestionnaire de mouse down pour commencer le drag ou resize
  const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left - state.canvas.pan.x) / state.canvas.zoom;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / state.canvas.zoom;

    // Vérifier si on clique sur un élément sélectionné pour le drag
    if (state.selection.selectedElements.length > 0) {
      const selectedElement = state.elements.find(el =>
        state.selection.selectedElements.includes(el.id) &&
        x >= el.x && x <= el.x + el.width &&
        y >= el.y && y <= el.y + el.height
      );

      if (selectedElement) {
        isDraggingRef.current = true;
        dragStartRef.current = { x: x - selectedElement.x, y: y - selectedElement.y };
        selectedElementRef.current = selectedElement.id;
        event.preventDefault();
        return;
      }
    }

    // Vérifier si on clique sur une poignée de redimensionnement
    const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
    if (resizeHandle) {
      isResizingRef.current = true;
      resizeHandleRef.current = resizeHandle.handle;
      selectedElementRef.current = resizeHandle.elementId;
      dragStartRef.current = { x, y };
      event.preventDefault();
      return;
    }

    // Sinon, gérer comme un clic normal
    handleCanvasClick(event);
  }, [state, canvasRef, handleCanvasClick]);

  // Gestionnaire de mouse move pour le drag et resize
  const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left - state.canvas.pan.x) / state.canvas.zoom;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / state.canvas.zoom;

    if (isDraggingRef.current && selectedElementRef.current) {
      // Déplacer l'élément
      const deltaX = x - dragStartRef.current.x;
      const deltaY = y - dragStartRef.current.y;

      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates: { x: deltaX, y: deltaY }
        }
      });
    } else if (isResizingRef.current && selectedElementRef.current && resizeHandleRef.current) {
      // Redimensionner l'élément
      const element = state.elements.find(el => el.id === selectedElementRef.current);
      if (!element) return;

      const updates = calculateResize(element, resizeHandleRef.current, x, y, dragStartRef.current);
      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates
        }
      });
    }
  }, [state, dispatch, canvasRef]);

  // Gestionnaire de mouse up pour terminer le drag ou resize
  const handleMouseUp = useCallback(() => {
    isDraggingRef.current = false;
    isResizingRef.current = false;
    resizeHandleRef.current = null;
    selectedElementRef.current = null;
  }, []);

  // Fonction utilitaire pour détecter les poignées de redimensionnement
  const getResizeHandleAtPosition = (x: number, y: number, selectedIds: string[], elements: any[]) => {
    const handleSize = 8;
    const selectedElements = elements.filter(el => selectedIds.includes(el.id));

    for (const element of selectedElements) {
      // Calculer les positions des poignées
      const handles = [
        { name: 'nw', x: element.x - handleSize/2, y: element.y - handleSize/2 },
        { name: 'ne', x: element.x + element.width - handleSize/2, y: element.y - handleSize/2 },
        { name: 'sw', x: element.x - handleSize/2, y: element.y + element.height - handleSize/2 },
        { name: 'se', x: element.x + element.width - handleSize/2, y: element.y + element.height - handleSize/2 }
      ];

      for (const handle of handles) {
        if (x >= handle.x && x <= handle.x + handleSize &&
            y >= handle.y && y <= handle.y + handleSize) {
          return { elementId: element.id, handle: handle.name };
        }
      }
    }

    return null;
  };

  // Fonction utilitaire pour calculer le redimensionnement
  const calculateResize = (element: any, handle: string, currentX: number, currentY: number, startPos: { x: number, y: number }) => {
    const updates: any = {};

    switch (handle) {
      case 'se': // Sud-Est
        updates.width = Math.max(20, currentX - element.x);
        updates.height = Math.max(20, currentY - element.y);
        break;
      case 'sw': // Sud-Ouest
        updates.width = Math.max(20, element.x + element.width - currentX);
        updates.height = Math.max(20, currentY - element.y);
        updates.x = currentX;
        break;
      case 'ne': // Nord-Est
        updates.width = Math.max(20, currentX - element.x);
        updates.height = Math.max(20, element.y + element.height - currentY);
        updates.y = currentY;
        break;
      case 'nw': // Nord-Ouest
        updates.width = Math.max(20, element.x + element.width - currentX);
        updates.height = Math.max(20, element.y + element.height - currentY);
        updates.x = currentX;
        updates.y = currentY;
        break;
    }

    return updates;
  };

  return {
    handleCanvasClick,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp
  };
};