import React, { useCallback, useRef } from 'react';
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
  const currentCursorRef = useRef<string>('default');

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

  // Fonction pour créer un élément selon le mode à une position donnée
  const createElementAtPosition = useCallback((x: number, y: number, mode: string) => {
    const elementId = `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

    let newElement: any;

    switch (mode) {
      case 'rectangle':
        newElement = {
          id: elementId,
          type: 'rectangle',
          x: x - 50, // Centrer sur le clic
          y: y - 50,
          width: 100,
          height: 100,
          fillColor: '#ffffff',
          strokeColor: '#000000',
          strokeWidth: 1,
          borderRadius: 0,
          rotation: 0,
          createdAt: new Date(),
          updatedAt: new Date()
        };
        break;

      case 'circle':
        newElement = {
          id: elementId,
          type: 'circle',
          x: x - 50,
          y: y - 50,
          width: 100,
          height: 100,
          fillColor: '#ffffff',
          strokeColor: '#000000',
          strokeWidth: 1,
          rotation: 0,
          createdAt: new Date(),
          updatedAt: new Date()
        };
        break;

      case 'line':
        newElement = {
          id: elementId,
          type: 'line',
          x: x - 50,
          y: y - 1, // Centrer verticalement sur le clic
          width: 100,
          height: 2, // Épaisseur de la ligne
          strokeColor: '#000000',
          strokeWidth: 2,
          rotation: 0,
          createdAt: new Date(),
          updatedAt: new Date()
        };
        break;

      case 'text':
        newElement = {
          id: elementId,
          type: 'text',
          x: x - 50,
          y: y - 10,
          width: 100,
          height: 30,
          text: 'Texte',
          fontSize: 16,
          color: '#000000',
          align: 'left',
          rotation: 0,
          createdAt: new Date(),
          updatedAt: new Date()
        };
        break;

      case 'image':
        newElement = {
          id: elementId,
          type: 'image',
          x: x - 50,
          y: y - 50,
          width: 100,
          height: 100,
          src: '', // URL de l'image à définir
          rotation: 0,
          createdAt: new Date(),
          updatedAt: new Date()
        };
        break;

      default:
        return;
    }

    // Ajouter l'élément au state
    dispatch({ type: 'ADD_ELEMENT', payload: newElement });

    // Sélectionner le nouvel élément
    dispatch({ type: 'SET_SELECTION', payload: [elementId] });
    selectedElementRef.current = elementId;

    // Remettre en mode sélection après création
    dispatch({ type: 'SET_MODE', payload: 'select' });

  }, [dispatch]);

  // Gestionnaire de clic pour la sélection et création d'éléments
  // Fonction utilitaire pour vérifier si un point est dans la hitbox d'un élément (avec marge pour les lignes)
  const isPointInElement = (x: number, y: number, element: any): boolean => {
    // Pour les lignes, ajouter une marge de 10px autour pour faciliter la sélection
    const hitboxMargin = element.type === 'line' ? 10 : 0;
    
    const left = element.x - hitboxMargin;
    const right = element.x + element.width + hitboxMargin;
    const top = element.y - hitboxMargin;
    const bottom = element.y + element.height + hitboxMargin;
    
    return x >= left && x <= right && y >= top && y <= bottom;
  };

  const handleCanvasClick = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;

    // Trouver l'élément cliqué (avec hitbox adaptée)
    const clickedElement = state.elements.find(el => isPointInElement(x, y, el));

    if (clickedElement) {
      // Sélectionner l'élément existant
      dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
      selectedElementRef.current = clickedElement.id;
    } else {
      // Aucun élément cliqué - vérifier si on doit créer un nouvel élément selon le mode
      if (state.mode !== 'select') {
        createElementAtPosition(x, y, state.mode);
      } else {
        // Mode sélection - désélectionner
        dispatch({ type: 'CLEAR_SELECTION' });
        selectedElementRef.current = null;
      }
    }
  }, [state, dispatch, canvasRef]);

  // Gestionnaire de mouse down pour commencer le drag ou resize
  const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;
    console.log('🖱️ [INTERACTION] handleMouseDown - x:', x, 'y:', y, 'selectedElements:', state.selection.selectedElements);

    // Vérifier si on clique sur un élément sélectionné pour le drag
    if (state.selection.selectedElements.length > 0) {
      const selectedElement = state.elements.find(el =>
        state.selection.selectedElements.includes(el.id) &&
        isPointInElement(x, y, el)
      );

      if (selectedElement) {
        isDraggingRef.current = true;
        // Store mouse position at drag START, not offset
        dragStartRef.current = { x, y };
        selectedElementRef.current = selectedElement.id;
        console.log('🖱️ [DRAG START] element:', selectedElement.id, 'mouseX:', x, 'mouseY:', y, 'elementX:', selectedElement.x, 'elementY:', selectedElement.y);
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

  // Gestionnaire de mouse up pour terminer le drag ou resize
  const handleMouseUp = useCallback(() => {
    isDraggingRef.current = false;
    isResizingRef.current = false;
    resizeHandleRef.current = null;
    selectedElementRef.current = null;
  }, []);

  // Fonction pour obtenir le curseur de redimensionnement selon la poignée
  const getResizeCursor = (handle: string | null): string => {
    switch (handle) {
      case 'nw':
      case 'se':
        return 'nw-resize';
      case 'ne':
      case 'sw':
        return 'ne-resize';
      default:
        return 'default';
    }
  };

  // Fonction pour déterminer le curseur approprié selon la position
  const getCursorAtPosition = useCallback((x: number, y: number): string => {
    // Si on est en train de draguer ou redimensionner, garder le curseur approprié
    if (isDraggingRef.current) {
      return 'grabbing';
    }
    if (isResizingRef.current) {
      return getResizeCursor(resizeHandleRef.current);
    }

    // Vérifier si on est sur une poignée de redimensionnement
    const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
    if (resizeHandle) {
      return getResizeCursor(resizeHandle.handle);
    }

    // Vérifier si on est sur un élément sélectionné (pour le déplacement)
    if (state.selection.selectedElements.length > 0) {
      const elementUnderMouse = state.elements.find(el =>
        state.selection.selectedElements.includes(el.id) &&
        isPointInElement(x, y, el)
      );

      if (elementUnderMouse) {
        return 'grab';
      }
    }

    // Curseur par défaut
    return 'default';
  }, [state.selection.selectedElements, state.elements]);

  // Fonction pour mettre à jour le curseur du canvas
  const updateCursor = useCallback((cursor: string) => {
    const canvas = canvasRef.current;
    if (canvas && cursor !== currentCursorRef.current) {
      canvas.style.cursor = cursor;
      currentCursorRef.current = cursor;
    }
  }, [canvasRef]);

  // Fonction utilitaire pour calculer le redimensionnement
  const calculateResize = (element: any, handle: string, currentX: number, currentY: number, _startPos: { x: number, y: number }) => {
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

  // Gestionnaire de mouse move pour le drag, resize et curseur
  const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;

    // Mettre à jour le curseur
    const cursor = getCursorAtPosition(x, y);
    updateCursor(cursor);

    if (isDraggingRef.current && selectedElementRef.current) {
      console.log('🎯 [DRAG] isDragging=true, element:', selectedElementRef.current, 'x:', x, 'y:', y);
      // Déplacer l'élément
      const element = state.elements.find(el => el.id === selectedElementRef.current);
      if (!element) {
        console.warn('❌ [DRAG] Element not found:', selectedElementRef.current);
        return;
      }

      // Calculate delta from drag START (dragStartRef is where mouse was when drag started)
      const deltaX = x - dragStartRef.current.x;
      const deltaY = y - dragStartRef.current.y;
      console.log('🎯 [DRAG] deltaX:', deltaX, 'deltaY:', deltaY, 'element.x:', element.x, 'element.y:', element.y);

      // NEW position = original position + delta
      let newX = element.x + deltaX;
      let newY = element.y + deltaY;

      // S'assurer que l'élément reste dans les limites du canvas
      const canvasWidth = 794; // Largeur A4 en pixels
      const canvasHeight = 1123; // Hauteur A4 en pixels

      // Clamp X position (laisser au moins 20px visible)
      const minVisibleWidth = Math.min(50, element.width * 0.3);
      if (newX < 0) newX = 0;
      if (newX + minVisibleWidth > canvasWidth) newX = canvasWidth - minVisibleWidth;

      // Clamp Y position (laisser au moins 20px visible)
      const minVisibleHeight = Math.min(30, element.height * 0.3);
      if (newY < 0) newY = 0;
      if (newY + minVisibleHeight > canvasHeight) newY = canvasHeight - minVisibleHeight;

      console.log('🎯 [DRAG] Dispatch UPDATE_ELEMENT - newX:', newX, 'newY:', newY);
      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates: { x: newX, y: newY }
        }
      });
    } else if (isResizingRef.current && selectedElementRef.current && resizeHandleRef.current) {
      console.log('📏 [RESIZE] isResizing=true, element:', selectedElementRef.current);
      // Redimensionner l'élément
      const element = state.elements.find(el => el.id === selectedElementRef.current);
      if (!element) return;

      const updates = calculateResize(element, resizeHandleRef.current, x, y, dragStartRef.current);
      console.log('📏 [RESIZE] Dispatch UPDATE_ELEMENT - updates:', updates);
      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates
        }
      });
    }
  }, [state, dispatch, canvasRef, getCursorAtPosition, updateCursor]);

  // Gestionnaire de clic droit pour afficher le menu contextuel
  const handleContextMenu = useCallback((event: React.MouseEvent<HTMLCanvasElement>, onContextMenu: (x: number, y: number, elementId?: string) => void) => {
    event.preventDefault(); // Empêcher le menu contextuel par défaut du navigateur

    const canvas = canvasRef.current;
    if (!canvas) return;

    // Pour le menu contextuel, nous utilisons les coordonnées absolues de la souris
    // (pas les coordonnées transformées du canvas)
    const menuX = event.clientX;
    const menuY = event.clientY;

    // Pour la détection d'élément, nous utilisons les coordonnées du canvas
    // Les éléments sont stockés dans l'espace monde (avec pan et zoom)
    // Pour la détection, utilisons les coordonnées dans l'espace canvas
    const rect = canvas.getBoundingClientRect();
    const rawCanvasX = event.clientX - rect.left;
    const rawCanvasY = event.clientY - rect.top;

    // Transformer en coordonnées monde (inverse des transformations du canvas)
    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    const canvasX = (rawCanvasX - state.canvas.pan.x) / zoomScale;
    const canvasY = (rawCanvasY - state.canvas.pan.y) / zoomScale;

    // Trouver l'élément cliqué (avec hitbox adaptée)
    const clickedElement = state.elements.find(el => isPointInElement(canvasX, canvasY, el));

    if (clickedElement) {
      // Ouvrir le menu contextuel pour l'élément
      onContextMenu(menuX, menuY, clickedElement.id);
    } else {
      // Ouvrir le menu contextuel général du canvas
      onContextMenu(menuX, menuY);
    }
  }, [state, canvasRef]);

  return {
    handleCanvasClick,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    handleContextMenu
  };
};