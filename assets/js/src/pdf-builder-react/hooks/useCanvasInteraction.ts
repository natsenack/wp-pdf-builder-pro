import React, { useCallback, useRef, useEffect } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { Element } from '../types/elements';

interface ElementUpdates {
  x?: number;
  y?: number;
  width?: number;
  height?: number;
}

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
  const selectedElementsRef = useRef<string[]>([]);  // ✅ Track locally instead of relying on stale state
  const resizeHandleRef = useRef<string | null>(null);
  const currentCursorRef = useRef<string>('default');

  // ✅ CORRECTION 5: Dernier state connu pour éviter closure stale
  const lastKnownStateRef = useRef(state);
  
  // ✅ CORRECTION 3: Throttling pour handleMouseMove
  const lastMouseMoveTimeRef = useRef<number>(0);
  const MOUSEMOVE_THROTTLE_MS = 16; // ~60 FPS (1000/60 ≈ 16ms)

  // Fonction utilitaire pour détecter les poignées de redimensionnement
  // ✅ BUGFIX-018: Consistent margin for hit detection across all element types
  const getResizeHandleAtPosition = (x: number, y: number, selectedIds: string[], elements: Element[]) => {
    const handleSize = 8;
    const handleMargin = 6;  // Consistent margin for all elements
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
        // Use consistent margin for all element types
        if (x >= handle.x - handleMargin && x <= handle.x + handleSize + handleMargin &&
            y >= handle.y - handleMargin && y <= handle.y + handleSize + handleMargin) {
          return { elementId: element.id, handle: handle.name };
        }
      }
    }

    return null;
  };

  // Fonction pour créer un élément selon le mode à une position donnée
  const createElementAtPosition = useCallback((x: number, y: number, mode: string) => {
    const elementId = `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

    let newElement: Element;

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
          visible: true,
          locked: false,
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
          visible: true,
          locked: false,
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
          visible: true,
          locked: false,
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
          visible: true,
          locked: false,
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
          visible: true,
          locked: false,
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

  // ✅ Syncer la ref avec l'état Redux (fallback au cas où dispatch arrive avant)
  useEffect(() => {
    selectedElementsRef.current = state.selection.selectedElements;
    // ✅ CORRECTION 5: Garder un snapshot du state courant
    lastKnownStateRef.current = state;
  }, [state.selection.selectedElements, state]);

  // ✅ CORRECTION 4: Fonction helper pour vérifier que rect est valide
  const validateCanvasRect = (rect: any): boolean => {
    // Vérifier que rect a des dimensions positives et que left/top sont raisonnables
    if (!rect || rect.width <= 0 || rect.height <= 0) {
      console.warn('❌ [RECT] Invalid canvas rect - zero dimensions:', rect);
      return false;
    }
    
    // Si rect.left ou rect.top sont très négatifs (canvas hors-écran), c'est OK
    // Mais si ils sont NaN, c'est un problème
    if (isNaN(rect.left) || isNaN(rect.top) || isNaN(rect.right) || isNaN(rect.bottom)) {
      console.warn('❌ [RECT] Canvas rect has NaN values:', rect);
      return false;
    }
    
    return true;
  };

  // Gestionnaire de clic pour la sélection et création d'éléments
  // Fonction utilitaire pour vérifier si un point est dans la hitbox d'un élément (avec marge pour les lignes)
  const isPointInElement = (x: number, y: number, element: Element): boolean => {
    // Pour les lignes, ajouter une marge RÉDUITE pour faciliter la sélection sans overlap excessif
    // Pour les autres éléments, pas de marge
    let hitboxMargin = 0;
    if (element.type === 'line') {
      // Marge très réduite: 1-2px max pour les lignes fines
      hitboxMargin = Math.max(1, Math.min(2, element.height * 0.5));
    }
    
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
    
    // ✅ BUGFIX-008: Validate rect BEFORE using it
    if (!validateCanvasRect(rect)) {
      console.warn('⚠️ [CLICK] Invalid canvas rect, skipping click handler');
      return;
    }
    
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
  }, [state, dispatch, canvasRef, createElementAtPosition]);

  // Gestionnaire de mouse down pour commencer le drag ou resize
  const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    
    // ✅ CORRECTION 4: Vérifier que rect est valide avant de l'utiliser
    if (!validateCanvasRect(rect)) {
      console.error('❌ [MOUSEDOWN] Canvas rect is invalid, skipping event');
      return;
    }

    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    
    // Calcul des coordonnées du canvas:
    // 1. (event.clientX - rect.left) = position relative au canvas en viewport space
    // 2. - state.canvas.pan.x = appliquer le pan (qui est en canvas space)
    // 3. / zoomScale = appliquer le zoom
    const canvasRelativeX = event.clientX - rect.left;
    const canvasRelativeY = event.clientY - rect.top;
    const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
    const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;
    
    console.log('🖱️ [MOUSEDOWN] screenX:', event.clientX, 'screenY:', event.clientY, 'canvasRect:', {left: rect.left, top: rect.top}, 'canvasRelative:', {x: canvasRelativeX, y: canvasRelativeY}, 'pan:', state.canvas.pan, 'zoomScale:', zoomScale, 'finalCoords:', {x, y});

    // ✅ Chercher n'importe quel élément au clic (sélectionné ou pas)
    const clickedElement = state.elements.find(el => {
      const isIn = isPointInElement(x, y, el);
      console.log('🔍 [HIT TEST]', el.type, el.id, '- x:', el.x, 'y:', el.y, 'w:', el.width, 'h:', el.height, 'clickX:', x.toFixed(2), 'clickY:', y.toFixed(2), 'isHit:', isIn);
      return isIn;
    });

    console.log('🖱️ [MOUSEDOWN] Éléments disponibles:', state.elements.length, 'Cliqué:', clickedElement ? clickedElement.id : 'AUCUN', 'Sélection avant:', state.selection.selectedElements);

    // Si on a cliqué sur un élément
    if (clickedElement) {
      // ✅ Utiliser state.selection directement (plus fiable que ref)
      const isAlreadySelected = state.selection.selectedElements.includes(clickedElement.id);
      
      // ✅ Si ce n'est pas sélectionné, le sélectionner d'abord
      if (!isAlreadySelected) {
        console.log('✅ [SELECTION] Sélection du nouvel élément:', clickedElement.id, 'type:', clickedElement.type);
        console.log('✅ [SELECTION] State selection AVANT dispatch:', state.selection.selectedElements);
        dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
        console.log('✅ [SELECTION] APRÈS dispatch - état sera mis à jour à:', [clickedElement.id]);
        // ✅ CORRECTION: Ne pas preventDefault pour permettre onClick de se déclencher
        // event.preventDefault();
        return;
      }

      // ✅ L'élément est déjà sélectionné - préparer le drag
      isDraggingRef.current = true;
      // Store the OFFSET from element's top-left corner to mouse click point
      const offsetX = x - clickedElement.x;
      const offsetY = y - clickedElement.y;
      dragStartRef.current = { x: offsetX, y: offsetY };
      selectedElementRef.current = clickedElement.id;
      console.log('🎯 [DRAG START] element:', clickedElement.id, 'clickX:', x, 'clickY:', y, 'elementX:', clickedElement.x, 'elementY:', clickedElement.y, 'offsetX:', offsetX, 'offsetY:', offsetY);
      event.preventDefault();
      return;
    }

    // Vérifier si on clique sur une poignée de redimensionnement
    const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
    if (resizeHandle) {
      console.log('📏 [RESIZE] Handle détecté:', resizeHandle.handle);
      isResizingRef.current = true;
      resizeHandleRef.current = resizeHandle.handle;
      selectedElementRef.current = resizeHandle.elementId;
      dragStartRef.current = { x, y };
      event.preventDefault();
      return;
    }

    // ✅ Sinon on a cliqué sur le vide - désélectionner
    if (state.selection.selectedElements.length > 0) {
      console.log('❌ [CLEAR] Clic sur le vide - désélection');
      dispatch({ type: 'CLEAR_SELECTION' });
      selectedElementRef.current = null;
    } else {
      console.log('❌ [NO ACTION] Clic sur le vide et rien sélectionné');
    }
  }, [state, canvasRef, dispatch]);

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
  const calculateResize = (element: Element, handle: string, currentX: number, currentY: number, _startPos: { x: number, y: number }) => {
    const updates: ElementUpdates = {};

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
    // ✅ CORRECTION 3: Throttling - limiter la fréquence des updates
    const now = Date.now();
    if (now - lastMouseMoveTimeRef.current < MOUSEMOVE_THROTTLE_MS) {
      return; // Skip cet event, trop rapide
    }
    lastMouseMoveTimeRef.current = now;

    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    
    // Même calcul que handleMouseDown pour cohérence
    const canvasRelativeX = event.clientX - rect.left;
    const canvasRelativeY = event.clientY - rect.top;
    const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
    const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;

    // Mettre à jour le curseur
    const cursor = getCursorAtPosition(x, y);
    updateCursor(cursor);

    if (isDraggingRef.current && selectedElementRef.current) {
      console.log('🎯 [DRAG] isDragging=true, element:', selectedElementRef.current, 'currentMouseX:', x, 'currentMouseY:', y);
      
      // ✅ CORRECTION 5: Utiliser lastKnownStateRef pour éviter closure stale
      const lastState = lastKnownStateRef.current;
      const element = lastState.elements.find(el => el.id === selectedElementRef.current);
      if (!element) {
        console.warn('❌ [DRAG] Element not found:', selectedElementRef.current);
        return;
      }

      // dragStartRef now contains the OFFSET (where we clicked on the element)
      // NEW position = current mouse position - offset
      let newX = x - dragStartRef.current.x;
      let newY = y - dragStartRef.current.y;
      console.log('🎯 [DRAG] currentMouse:', { x, y }, 'offset:', dragStartRef.current, 'newPosition:', { newX, newY }, 'element current pos:', { x: element.x, y: element.y });

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
      
      // ✅ CORRECTION 6: Améliorer la préservation des propriétés
      // Copier TOUS les champs de l'élément, même s'ils sont undefined
      const completeUpdates: Record<string, unknown> = { x: newX, y: newY };
      
      // Préserver TOUTES les propriétés
      for (const key in element) {
        if (key !== 'x' && key !== 'y' && key !== 'updatedAt') {
          completeUpdates[key] = (element as Record<string, unknown>)[key];
        }
      }
      
      console.log('🎯 [DRAG] Propriétés preservées:', Object.keys(completeUpdates).length, 'avec src:', !!completeUpdates.src);
      
      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates: completeUpdates
        }
      });
    } else if (isResizingRef.current && selectedElementRef.current && resizeHandleRef.current) {
      console.log('📏 [RESIZE] isResizing=true, element:', selectedElementRef.current);
      
      // ✅ CORRECTION 5: Utiliser lastKnownStateRef pour resize aussi
      const lastState = lastKnownStateRef.current;
      const element = lastState.elements.find(el => el.id === selectedElementRef.current);
      if (!element) return;

      const resizeUpdates = calculateResize(element, resizeHandleRef.current, x, y, dragStartRef.current);
      console.log('📏 [RESIZE] Dispatch UPDATE_ELEMENT - updates:', resizeUpdates);
      
      // ✅ CORRECTION 6: Préserver TOUTES les propriétés pendant resize
      const completeUpdates: Record<string, unknown> = { ...resizeUpdates };
      
      // Préserver les propriétés non-resize
      for (const key in element) {
        if (!(key in resizeUpdates) && key !== 'updatedAt') {
          completeUpdates[key] = (element as Record<string, unknown>)[key];
        }
      }
      
      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates: completeUpdates
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