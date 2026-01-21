import React, { useCallback, useRef, useEffect, useState } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext';
import { useCanvasSettings } from '../contexts/CanvasSettingsContext';
import { Element } from '../types/elements';
import { debugLog, debugError, debugWarn } from '../utils/debug';

// Déclaration des APIs globales du navigateur
declare const requestAnimationFrame: (callback: () => void) => number;
declare const cancelAnimationFrame: (id: number) => void;

interface ElementUpdates {
  x?: number;
  y?: number;
  width?: number;
  height?: number;
}

interface UseCanvasInteractionProps {
  canvasRef: React.RefObject<HTMLCanvasElement>;
  canvasWidth?: number;
  canvasHeight?: number;
}

export const useCanvasInteraction = ({ canvasRef, canvasWidth = 794, canvasHeight = 1123 }: UseCanvasInteractionProps) => {
  const { state, dispatch } = useBuilder();
  const canvasSettings = useCanvasSettings();
  
  // Déterminer le mode de sélection effectif : si sélection multiple désactivée, forcer le mode 'click'
  const selectionMode = canvasSettings.selectionMultiSelectEnabled ? canvasSettings.canvasSelectionMode : 'click';

  // État pour déclencher le re-rendu du canvas pendant la sélection
  const [selectionUpdateTrigger, setSelectionUpdateTrigger] = useState(0);

  // États pour le drag et resize
  const isDraggingRef = useRef(false);
  const isResizingRef = useRef(false);
  const isRotatingRef = useRef(false);
  const dragStartRef = useRef<Record<string, { x: number; y: number }>>({});  // Pour drag multiple : positions initiales de tous les éléments
  const dragMouseStartRef = useRef({ x: 0, y: 0 });  // Position souris au début du drag
  const resizeMouseStartRef = useRef({ x: 0, y: 0 });  // Position souris au début du resize
  const rotationMouseStartRef = useRef({ x: 0, y: 0 });  // Position souris au début de la rotation
  const rotationStartRef = useRef<Record<string, number>>({});  // Rotations initiales des éléments
  const selectedElementRef = useRef<string | null>(null);
  const selectedElementsRef = useRef<string[]>([]);  // ✅ Track locally instead of relying on stale state
  const resizeHandleRef = useRef<string | null>(null);
  const currentCursorRef = useRef<string>('default');

  // États pour les modes de sélection avancés
  const isSelectingRef = useRef(false);  // En cours de sélection lasso/rectangle
  const selectionStartRef = useRef({ x: 0, y: 0 });  // Point de départ de la sélection
  const selectionPointsRef = useRef<{ x: number; y: number }[]>([]);  // Points pour le lasso
  const selectionRectRef = useRef({ x: 0, y: 0, width: 0, height: 0 });  // Rectangle de sélection

  // Refs pour les event listeners globaux pendant la sélection
  const globalMouseMoveRef = useRef<((event: MouseEvent) => void) | null>(null);
  const globalMouseUpRef = useRef<((event: MouseEvent) => void) | null>(null);

  // ✅ OPTIMISATION FLUIDITÉ: requestAnimationFrame pour synchroniser avec le refresh rate
  const rafIdRef = useRef<number | null>(null);
  const pendingDragUpdateRef = useRef<{ x: number; y: number } | null>(null);
  const pendingRotationUpdateRef = useRef<{ x: number; y: number } | null>(null);

  // ✅ CORRECTION 5: Dernier state connu pour éviter closure stale
  const lastKnownStateRef = useRef(state);

  // Fonctions pour gérer les événements globaux pendant la sélection
  const startGlobalSelectionListeners = useCallback(() => {
    if (globalMouseMoveRef.current || globalMouseUpRef.current) return; // Déjà actifs

    globalMouseMoveRef.current = (event: MouseEvent) => {
      const canvas = canvasRef.current;
      if (!canvas) return;

      const rect = canvas.getBoundingClientRect();
      const zoomScale = state.canvas.zoom / 100;

      // Calcul des coordonnées même si la souris est hors du canvas
      const canvasRelativeX = event.clientX - rect.left;
      const canvasRelativeY = event.clientY - rect.top;
      const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
      const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;

      // Mettre à jour la sélection
      if (selectionMode === 'lasso') {
        selectionPointsRef.current.push({ x, y });
        setSelectionUpdateTrigger(prev => prev + 1);
      } else if (selectionMode === 'rectangle') {
        const startX = Math.min(selectionStartRef.current.x, x);
        const startY = Math.min(selectionStartRef.current.y, y);
        const width = Math.abs(x - selectionStartRef.current.x);
        const height = Math.abs(y - selectionStartRef.current.y);
        selectionRectRef.current = { x: startX, y: startY, width, height };
        setSelectionUpdateTrigger(prev => prev + 1);
      }
    };

    globalMouseUpRef.current = () => {
      stopGlobalSelectionListeners();
      // Terminer la sélection directement ici
      if (isSelectingRef.current) {
        let selectedElementIds: string[] = [];

        if (selectionMode === 'lasso' && selectionPointsRef.current.length > 2) {
          // Utiliser la même logique que isElementInLasso
          selectedElementIds = state.elements
            .filter(element => {
              const centerX = element.x + element.width / 2;
              const centerY = element.y + element.height / 2;
              // Logique de isPointInPolygon dupliquée
              let inside = false;
              const polygon = selectionPointsRef.current;
              for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                const xi = polygon[i].x, yi = polygon[i].y;
                const xj = polygon[j].x, yj = polygon[j].y;
                if (((yi > centerY) !== (yj > centerY)) && (centerX < (xj - xi) * (centerY - yi) / (yj - yi) + xi)) {
                  inside = !inside;
                }
              }
              return inside;
            })
            .map(element => element.id);
        } else if (selectionMode === 'rectangle' && selectionRectRef.current.width > 0 && selectionRectRef.current.height > 0) {
          // Utiliser la même logique que isElementInRectangle
          selectedElementIds = state.elements
            .filter(element => {
              const elementRight = element.x + element.width;
              const elementBottom = element.y + element.height;
              const rectRight = selectionRectRef.current.x + selectionRectRef.current.width;
              const rectBottom = selectionRectRef.current.y + selectionRectRef.current.height;
              return !(element.x > rectRight || elementRight < selectionRectRef.current.x || element.y > rectBottom || elementBottom < selectionRectRef.current.y);
            })
            .map(element => element.id);
        }

        if (selectedElementIds.length > 0) {
          dispatch({ type: 'SET_SELECTION', payload: selectedElementIds });
        } else {
          dispatch({ type: 'CLEAR_SELECTION' });
        }

        // Réinitialiser l'état de sélection
        isSelectingRef.current = false;
        selectionPointsRef.current = [];
        selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
      }
    };

    document.addEventListener('mousemove', globalMouseMoveRef.current, { passive: false });
    document.addEventListener('mouseup', globalMouseUpRef.current, { passive: false });
  }, [canvasRef, state.canvas.zoom, state.canvas.pan, state.elements, selectionMode, dispatch]);

  const stopGlobalSelectionListeners = useCallback(() => {
    if (globalMouseMoveRef.current) {
      document.removeEventListener('mousemove', globalMouseMoveRef.current);
      globalMouseMoveRef.current = null;
    }
    if (globalMouseUpRef.current) {
      document.removeEventListener('mouseup', globalMouseUpRef.current);
      globalMouseUpRef.current = null;
    }
  }, []);

  // ✅ OPTIMISATION FLUIDITÉ: Fonction pour effectuer les updates de drag avec RAF
  const performDragUpdate = useCallback(() => {
    if (!pendingDragUpdateRef.current) {
      rafIdRef.current = null;
      return;
    }

    const { x: currentMouseX, y: currentMouseY } = pendingDragUpdateRef.current;
    const lastState = lastKnownStateRef.current;

    // ✅ MODIFICATION: Gérer le drag multiple
    const selectedIds = lastState.selection.selectedElements;
    if (selectedIds.length === 0) {
      rafIdRef.current = null;
      return;
    }

    // Calculer le delta de déplacement de la souris depuis le début du drag
    const mouseDeltaX = currentMouseX - dragMouseStartRef.current.x;
    const mouseDeltaY = currentMouseY - dragMouseStartRef.current.y;

    // Mettre à jour tous les éléments sélectionnés
    selectedIds.forEach(elementId => {
      const element = lastState.elements.find(el => el.id === elementId);
      if (!element) return;

      // Récupérer la position de départ de cet élément spécifique
      const elementStartPos = dragStartRef.current[elementId];
      if (!elementStartPos) return;

      // Calculer la nouvelle position en appliquant le delta de la souris à la position de départ
      let finalX = elementStartPos.x + mouseDeltaX;
      let finalY = elementStartPos.y + mouseDeltaY;

      // ✅ AJOUT: Logique d'accrochage à la grille
      if (lastState.canvas.snapToGrid && lastState.canvas.gridSize > 0) {
        const gridSize = lastState.canvas.gridSize;
        const snapTolerance = 5; // Tolérance de 5px pour l'accrochage

        // Calculer la distance à la grille la plus proche
        const nearestGridX = Math.round(finalX / gridSize) * gridSize;
        const nearestGridY = Math.round(finalY / gridSize) * gridSize;

        // Appliquer l'accrochage seulement si on est assez proche de la grille
        if (Math.abs(finalX - nearestGridX) <= snapTolerance) {
          finalX = nearestGridX;
        }
        if (Math.abs(finalY - nearestGridY) <= snapTolerance) {
          finalY = nearestGridY;
        }
      }

      // S'assurer que l'élément reste dans les limites du canvas
      const canvasWidthPx = canvasWidth;
      const canvasHeightPx = canvasHeight;

      // Clamp X position (laisser au moins 20px visible)
      const minVisibleWidth = Math.min(50, element.width * 0.3);
      if (finalX < 0) finalX = 0;
      if (finalX + minVisibleWidth > canvasWidthPx) finalX = canvasWidthPx - minVisibleWidth;

      // Clamp Y position (laisser au moins 20px visible)
      const minVisibleHeight = Math.min(30, element.height * 0.3);
      if (finalY < 0) finalY = 0;
      if (finalY + minVisibleHeight > canvasHeightPx) finalY = canvasHeightPx - minVisibleHeight;

      // ✅ CORRECTION 6: Améliorer la préservation des propriétés
      const completeUpdates: Record<string, unknown> = { x: finalX, y: finalY };

      // ✅ Préserver TOUTES les propriétés
      const elementAsRecord = element as Record<string, unknown>;
      Object.keys(elementAsRecord).forEach(key => {
        if (key !== 'x' && key !== 'y' && key !== 'updatedAt') {
          completeUpdates[key] = elementAsRecord[key];
        }
      });

      // ✅ CRITICAL: Explicitement préserver ces propriétés critiques
      if ('src' in elementAsRecord) {
        completeUpdates.src = elementAsRecord.src;
      }
      if ('logoUrl' in elementAsRecord) {
        completeUpdates.logoUrl = elementAsRecord.logoUrl;
      }
      if ('alignment' in elementAsRecord) {
        completeUpdates.alignment = elementAsRecord.alignment;
      }

      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: elementId,
          updates: completeUpdates
        }
      });
    });

    pendingDragUpdateRef.current = null;
    rafIdRef.current = null;
  }, [dispatch, canvasWidth, canvasHeight]);

  // ✅ OPTIMISATION FLUIDITÉ: Fonction pour effectuer les updates de rotation avec RAF
  const performRotationUpdate = useCallback(() => {
    if (!pendingRotationUpdateRef.current) {
      rafIdRef.current = null;
      return;
    }

    const { x: currentMouseX, y: currentMouseY } = pendingRotationUpdateRef.current;
    const lastState = lastKnownStateRef.current;

    // ✅ MODIFICATION: Gérer la rotation multiple
    const selectedIds = lastState.selection.selectedElements;
    if (selectedIds.length === 0) {
      rafIdRef.current = null;
      return;
    }

    // Calculer le centre de rotation (centre de la sélection)
    const selectedElements = lastState.elements.filter(el => selectedIds.includes(el.id));
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    selectedElements.forEach(el => {
      minX = Math.min(minX, el.x);
      minY = Math.min(minY, el.y);
      maxX = Math.max(maxX, el.x + el.width);
      maxY = Math.max(maxY, el.y + el.height);
    });

    const centerX = (minX + maxX) / 2;
    const centerY = (minY + maxY) / 2;

    // Calculer l'angle de rotation basé sur la position de la souris
    const startAngle = Math.atan2(rotationMouseStartRef.current.y - centerY, rotationMouseStartRef.current.x - centerX);
    const currentAngle = Math.atan2(currentMouseY - centerY, currentMouseX - centerX);
    
    // Calculer la différence angulaire avec gestion du wrap-around
    let angleDelta = currentAngle - startAngle;
    
    // Normaliser l'angle entre -π et π pour éviter les sauts
    while (angleDelta > Math.PI) angleDelta -= 2 * Math.PI;
    while (angleDelta < -Math.PI) angleDelta += 2 * Math.PI;
    
    // Ajuster la sensibilité de la rotation (moins violent)
    angleDelta *= 1.3; // Multiplier par 1.3 pour une rotation douce mais perceptible
    
    // Convertir en degrés
    let totalRotationDegrees = (angleDelta * 180) / Math.PI;

    // ✅ AJOUT: Snap magnétique ULTRA SIMPLE - forcer à 0° quand proche
    const zeroSnapTolerance = 8; // 8 degrees (moins agressif)

    // Calculer la rotation actuelle pour chaque élément
    selectedIds.forEach(elementId => {
      const element = lastState.elements.find(el => el.id === elementId);
      if (element) {
        const initialRotation = rotationStartRef.current[elementId] || 0;
        let currentRotation = initialRotation + totalRotationDegrees;

        // Normaliser l'angle entre -180° et 180°
        let normalizedRotation = currentRotation % 360;
        if (normalizedRotation > 180) normalizedRotation -= 360;
        if (normalizedRotation < -180) normalizedRotation += 360;

        // Distance à 0°
        const distanceToZero = Math.abs(normalizedRotation);

        // SI PROCHE DE 0°, FORCER totalRotationDegrees pour que la rotation finale soit 0°
        if (distanceToZero <= zeroSnapTolerance) {
          // Forcer totalRotationDegrees pour que newRotation = 0
          totalRotationDegrees = -initialRotation;
        }
      }
    });

    // Mettre à jour la rotation de tous les éléments sélectionnés
    selectedIds.forEach(elementId => {
      const element = lastState.elements.find(el => el.id === elementId);
      if (element) {
        const initialRotation = rotationStartRef.current[elementId] || 0;
        let newRotation = initialRotation + totalRotationDegrees;

        dispatch({
          type: 'UPDATE_ELEMENT',
          payload: {
            id: elementId,
            updates: { rotation: newRotation }
          }
        });
      }
    });

    pendingRotationUpdateRef.current = null;
    rafIdRef.current = null;
  }, [dispatch]);  // ✅ CORRECTION 3: Throttling pour handleMouseMove - optimisé pour fluidité maximale
  const lastMouseMoveTimeRef = useRef<number>(0);
  const MOUSEMOVE_THROTTLE_MS = 8; // Réduit de 100ms à 8ms pour fluidité maximale (120Hz)

  // Fonction utilitaire pour détecter les poignées de redimensionnement
  // ✅ BUGFIX-018: Consistent margin for hit detection across all element types
  const getResizeHandleAtPosition = useCallback((x: number, y: number, selectedIds: string[], elements: Element[]) => {
    const handleSize = 8;
    const handleMargin = 6;  // Consistent margin for all elements
    const selectedElements = elements.filter(el => selectedIds.includes(el.id));

    for (const element of selectedElements) {
      // Calculer les positions des poignées (8 poignées : 4 coins + 4 milieux)
      const handles = [
        // Coins
        { name: 'nw', x: element.x - handleSize/2, y: element.y - handleSize/2 },
        { name: 'ne', x: element.x + element.width - handleSize/2, y: element.y - handleSize/2 },
        { name: 'sw', x: element.x - handleSize/2, y: element.y + element.height - handleSize/2 },
        { name: 'se', x: element.x + element.width - handleSize/2, y: element.y + element.height - handleSize/2 },
        // Milieux des côtés
        { name: 'n', x: element.x + element.width/2 - handleSize/2, y: element.y - handleSize/2 },
        { name: 's', x: element.x + element.width/2 - handleSize/2, y: element.y + element.height - handleSize/2 },
        { name: 'w', x: element.x - handleSize/2, y: element.y + element.height/2 - handleSize/2 },
        { name: 'e', x: element.x + element.width - handleSize/2, y: element.y + element.height/2 - handleSize/2 }
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
  }, []);

  // Fonction pour créer un élément selon le mode à une position donnée
  const createElementAtPosition = useCallback((x: number, y: number, mode: string) => {
    const elementId = `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

    // ✅ AJOUT: Appliquer le snap à la grille lors de la création d'éléments
    let finalX = x;
    let finalY = y;

    if (state.canvas.snapToGrid && state.canvas.gridSize > 0) {
      const gridSize = state.canvas.gridSize;
      finalX = Math.round(x / gridSize) * gridSize;
      finalY = Math.round(y / gridSize) * gridSize;
    }

    let newElement: Element;

    switch (mode) {
      case 'rectangle':
        newElement = {
          id: elementId,
          type: 'rectangle',
          x: finalX - 50, // Centrer sur le clic (snapped)
          y: finalY - 50,
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
          x: finalX - 50,
          y: finalY - 50,
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
          x: finalX - 50,
          y: finalY - 1, // Centrer verticalement sur le clic
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
          x: finalX - 50,
          y: finalY - 10,
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
          x: finalX - 50,
          y: finalY - 50,
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

  }, [dispatch, state.canvas.snapToGrid, state.canvas.gridSize]);

  // ✅ Syncer la ref avec l'état Redux (correction: éviter la dépendance sur state entier)
  useEffect(() => {
    selectedElementsRef.current = state.selection.selectedElements;
    // ✅ CORRECTION 5: Garder un snapshot du state courant
    lastKnownStateRef.current = state;
  }, [state.selection.selectedElements, state.elements, state.canvas]); // Dépendances spécifiques au lieu de state entier

  // ✅ CORRECTION 4: Fonction helper pour vérifier que rect est valide
  const validateCanvasRect = (rect: { width: number; height: number; left: number; top: number; right: number; bottom: number }): boolean => {
    // Vérifier que rect a des dimensions positives et que left/top sont raisonnables
    if (!rect || rect.width <= 0 || rect.height <= 0) {
      return false;
    }
    
    // Si rect.left ou rect.top sont très négatifs (canvas hors-écran), c'est OK
    // Mais si ils sont NaN, c'est un problème
    if (isNaN(rect.left) || isNaN(rect.top) || isNaN(rect.right) || isNaN(rect.bottom)) {
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
      return;
    }

    // Note: zoom est en pourcentage (100%), donc diviser par 100 pour obtenir le facteur d'échelle
    const zoomScale = state.canvas.zoom / 100;
    const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;

    // ✅ CORRECTION: Vérifier qu'aucun élément n'est cliqué (pour éviter duplication avec handleMouseDown)
    // Note: On cherche du dernier vers le premier pour cohérence avec handleMouseDown
    const clickedElement = [...state.elements].reverse().find(el => isPointInElement(x, y, el));

    // Ne créer un élément que si on clique dans le vide ET qu'on n'est pas en mode sélection
    if (!clickedElement && state.mode !== 'select') {
      createElementAtPosition(x, y, state.mode);
    }
    // Note: La sélection est gérée exclusivement par handleMouseDown
  }, [state, canvasRef, createElementAtPosition]);

  // Gestionnaire de mouse down pour commencer le drag ou resize
  const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) {
      debugLog('[CanvasInteraction] Mouse down ignored - canvas ref null');
      return;
    }

    const rect = canvas.getBoundingClientRect();
    
    // ✅ CORRECTION 4: Vérifier que rect est valide avant de l'utiliser
    if (!validateCanvasRect(rect)) {
      debugLog('[CanvasInteraction] Mouse down ignored - invalid canvas rect');
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

    debugLog(`[CanvasInteraction] Mouse down at canvas coords (${x.toFixed(1)}, ${y.toFixed(1)}), zoom: ${zoomScale}, pan: (${state.canvas.pan.x.toFixed(1)}, ${state.canvas.pan.y.toFixed(1)})`);

    // ✅ Chercher n'importe quel élément au clic (sélectionné ou pas)
    // Note: On cherche du dernier vers le premier pour sélectionner l'élément rendu au-dessus
    const clickedElement = [...state.elements].reverse().find(el => {
      const isIn = isPointInElement(x, y, el);
      return isIn;
    });

    // Si on a cliqué sur un élément
    if (clickedElement) {
      debugLog(`[CanvasInteraction] Clicked element: ${clickedElement.type} (${clickedElement.id})`);
      // ✅ Utiliser state.selection directement (plus fiable que ref)
      const isAlreadySelected = state.selection.selectedElements.includes(clickedElement.id);
      
      // ✅ Vérifier si la sélection multiple est activée et si Ctrl est enfoncé
      const isMultiSelect = canvasSettings.selectionMultiSelectEnabled && event.ctrlKey;
      
      if (isMultiSelect) {
        debugLog(`[CanvasInteraction] Multi-select mode - ${isAlreadySelected ? 'removing' : 'adding'} element ${clickedElement.id}`);
        // ✅ Mode sélection multiple
        if (isAlreadySelected) {
          // Retirer l'élément de la sélection
          const newSelection = state.selection.selectedElements.filter(id => id !== clickedElement.id);
          dispatch({ type: 'SET_SELECTION', payload: newSelection });
        } else {
          // Ajouter l'élément à la sélection
          const newSelection = [...state.selection.selectedElements, clickedElement.id];
          dispatch({ type: 'SET_SELECTION', payload: newSelection });
        }
        event.preventDefault();
        return;
      } else {
        // ✅ Mode sélection simple (comportement actuel)
        if (!isAlreadySelected) {
          debugLog(`[CanvasInteraction] Selecting element ${clickedElement.id}`);
          dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
          // ✅ CORRECTION: Préparer le drag immédiatement pour permettre drag après sélection
          isDraggingRef.current = true;
          // Stocker les positions de départ de tous les éléments sélectionnés
          dragStartRef.current = { [clickedElement.id]: { x: clickedElement.x, y: clickedElement.y } };
          dragMouseStartRef.current = { x, y };  // Position souris
          selectedElementRef.current = clickedElement.id;
          event.preventDefault();
          return;
        }

        // ✅ L'élément est déjà sélectionné - préparer le drag
        debugLog(`[CanvasInteraction] Starting drag for ${state.selection.selectedElements.length} selected elements`);
        isDraggingRef.current = true;
        // Stocker les positions de départ de tous les éléments sélectionnés
        const startPositions: Record<string, { x: number; y: number }> = {};
        state.selection.selectedElements.forEach(id => {
          const element = state.elements.find(el => el.id === id);
          if (element) {
            startPositions[id] = { x: element.x, y: element.y };
          }
        });
        dragStartRef.current = startPositions;
        dragMouseStartRef.current = { x, y };  // Position souris
        selectedElementRef.current = clickedElement.id;
        event.preventDefault();
        return;
      }
    }

    // Vérifier si on clique sur une poignée de redimensionnement
    const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
    if (resizeHandle) {
      debugLog(`[CanvasInteraction] Starting resize - element: ${resizeHandle.elementId}, handle: ${resizeHandle.handle}`);
      isResizingRef.current = true;
      resizeHandleRef.current = resizeHandle.handle;
      selectedElementRef.current = resizeHandle.elementId;
      resizeMouseStartRef.current = { x, y };  // Position souris au début du resize
      event.preventDefault();
      return;
    }

    // Vérifier si on clique sur une poignée de rotation
    if (canvasSettings?.selectionRotationEnabled && state.selection.selectedElements.length > 0) {
      const selectedElements = state.elements.filter(el => state.selection.selectedElements.includes(el.id));
      if (selectedElements.length > 0) {
        // Calculer les bounds de sélection
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        selectedElements.forEach(el => {
          minX = Math.min(minX, el.x);
          minY = Math.min(minY, el.y);
          maxX = Math.max(maxX, el.x + el.width);
          maxY = Math.max(maxY, el.y + el.height);
        });

        // Position de la poignée de rotation
        const centerX = (minX + maxX) / 2;
        const rotationHandleY = minY - 20;
        const rotationHandleSize = 8;

        // Vérifier si on est sur la poignée de rotation
        const distance = Math.sqrt((x - centerX) ** 2 + (y - rotationHandleY) ** 2);
        if (distance <= rotationHandleSize / 2) {
          debugLog(`[CanvasInteraction] Starting rotation for ${state.selection.selectedElements.length} elements`);
          isRotatingRef.current = true;
          rotationMouseStartRef.current = { x, y };
          
          // Stocker les rotations initiales de tous les éléments sélectionnés
          const initialRotations: Record<string, number> = {};
          state.selection.selectedElements.forEach(elementId => {
            const element = state.elements.find(el => el.id === elementId);
            if (element) {
              initialRotations[elementId] = (element as any).rotation || 0;
            }
          });
          rotationStartRef.current = initialRotations;
          
          event.preventDefault();
          return;
        }
      }
    }

    // ✅ Sinon on a cliqué sur le vide - gérer selon le mode de sélection
    if (selectionMode === 'lasso' || selectionMode === 'rectangle') {
      debugLog(`[CanvasInteraction] Starting ${selectionMode} selection at (${x.toFixed(1)}, ${y.toFixed(1)})`);
      // Commencer une nouvelle sélection
      isSelectingRef.current = true;
      selectionStartRef.current = { x, y };
      selectionPointsRef.current = [{ x, y }];
      if (selectionMode === 'rectangle') {
        selectionRectRef.current = { x, y, width: 0, height: 0 };
      }
      // Démarrer les listeners globaux pour permettre la sélection hors canvas
      startGlobalSelectionListeners();
      // Ne pas désélectionner immédiatement, attendre la fin de la sélection
      event.preventDefault();
      return;
    } else {
      // Mode clic simple - désélectionner
      if (state.selection.selectedElements.length > 0) {
        debugLog(`[CanvasInteraction] Clearing selection (${state.selection.selectedElements.length} elements)`);
        dispatch({ type: 'CLEAR_SELECTION' });
        selectedElementRef.current = null;
      }
    }
  }, [state, canvasRef, dispatch, getResizeHandleAtPosition]);

  // Gestionnaire de mouse up pour terminer le drag ou resize
  const handleMouseUp = useCallback(() => {
    debugLog(`[CanvasInteraction] Mouse up - ending interactions (dragging: ${isDraggingRef.current}, resizing: ${isResizingRef.current}, rotating: ${isRotatingRef.current}, selecting: ${isSelectingRef.current})`);
    
    // Annuler tout RAF en cours et effectuer un dernier update si nécessaire
    if (rafIdRef.current !== null) {
      cancelAnimationFrame(rafIdRef.current);
      rafIdRef.current = null;

      // Effectuer un dernier update si il y en a un en attente
      if (pendingDragUpdateRef.current) {
        performDragUpdate();
      }
      if (pendingRotationUpdateRef.current) {
        performRotationUpdate();
      }
    }

    // ✅ AJOUT: Snap final ultra simple
    const lastState = lastKnownStateRef.current;
    const selectedIds = lastState.selection.selectedElements;
    if (selectedIds.length > 0 && isRotatingRef.current) {
      selectedIds.forEach(elementId => {
        const element = lastState.elements.find(el => el.id === elementId);
        if (element) {
          let currentRotation = element.rotation || 0;

          // Normaliser
          let normalizedRotation = currentRotation % 360;
          if (normalizedRotation > 180) normalizedRotation -= 360;
          if (normalizedRotation < -180) normalizedRotation += 360;

          // Si dans les 10°, forcer à 0°
          const finalSnapThreshold = 10; // 10 degrees (moins agressif)
          if (Math.abs(normalizedRotation) <= finalSnapThreshold) {
            dispatch({
              type: 'UPDATE_ELEMENT',
              payload: {
                id: elementId,
                updates: { rotation: 0 }
              }
            });
          }
        }
      });
    }

    // Finaliser la sélection lasso/rectangle si en cours
    if (isSelectingRef.current) {
      let selectedElementIds: string[] = [];

      if (selectionMode === 'lasso' && selectionPointsRef.current.length > 2) {
        // Sélection lasso : vérifier quels éléments sont à l'intérieur du polygone
        selectedElementIds = state.elements
          .filter(element => isElementInLasso(element, selectionPointsRef.current))
          .map(element => element.id);
        debugLog(`[CanvasInteraction] Lasso selection completed - ${selectedElementIds.length} elements selected`);
      } else if (selectionMode === 'rectangle' && selectionRectRef.current.width > 0 && selectionRectRef.current.height > 0) {
        // Sélection rectangle : vérifier quels éléments intersectent le rectangle
        selectedElementIds = state.elements
          .filter(element => isElementInRectangle(element, selectionRectRef.current))
          .map(element => element.id);
        debugLog(`[CanvasInteraction] Rectangle selection completed - ${selectedElementIds.length} elements selected`);
      }

      // Appliquer la sélection
      if (selectedElementIds.length > 0) {
        debugLog(`[CanvasInteraction] Applying selection: ${selectedElementIds.join(', ')}`);
        dispatch({ type: 'SET_SELECTION', payload: selectedElementIds });
      } else {
        debugLog(`[CanvasInteraction] No elements selected - clearing selection`);
        dispatch({ type: 'CLEAR_SELECTION' });
      }

      // Réinitialiser l'état de sélection
      isSelectingRef.current = false;
      selectionPointsRef.current = [];
      selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
    }

    isDraggingRef.current = false;
    isResizingRef.current = false;
    isRotatingRef.current = false;
    resizeHandleRef.current = null;
    selectedElementRef.current = null;
    rotationStartRef.current = {};
    pendingRotationUpdateRef.current = null;
  }, [performDragUpdate, performRotationUpdate, dispatch]);

  // Fonction pour obtenir le curseur de redimensionnement selon la poignée
  const getResizeCursor = (handle: string | null): string => {
    switch (handle) {
      case 'nw':
      case 'se':
        return 'nw-resize';
      case 'ne':
      case 'sw':
        return 'ne-resize';
      case 'n':
        return 'n-resize';
      case 's':
        return 's-resize';
      case 'w':
        return 'w-resize';
      case 'e':
        return 'e-resize';
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
    if (isRotatingRef.current) {
      return 'grabbing';
    }

    // Vérifier si on est sur une poignée de rotation
    if (canvasSettings?.selectionRotationEnabled && state.selection.selectedElements.length > 0) {
      const selectedElements = state.elements.filter(el => state.selection.selectedElements.includes(el.id));
      if (selectedElements.length > 0) {
        // Calculer les bounds de sélection
        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
        selectedElements.forEach(el => {
          minX = Math.min(minX, el.x);
          minY = Math.min(minY, el.y);
          maxX = Math.max(maxX, el.x + el.width);
          maxY = Math.max(maxY, el.y + el.height);
        });

        // Position de la poignée de rotation
        const centerX = (minX + maxX) / 2;
        const rotationHandleY = minY - 20;
        const rotationHandleSize = 8;

        // Vérifier si on est sur la poignée de rotation
        const distance = Math.sqrt((x - centerX) ** 2 + (y - rotationHandleY) ** 2);
        if (distance <= rotationHandleSize / 2) {
          return 'grab';
        }
      }
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
  }, [state.selection.selectedElements, state.elements, getResizeHandleAtPosition, canvasSettings.selectionRotationEnabled]);

  // Fonction pour mettre à jour le curseur du canvas
  const updateCursor = useCallback((cursor: string) => {
    const canvas = canvasRef.current;
    if (canvas && cursor !== currentCursorRef.current) {
      canvas.style.cursor = cursor;
      currentCursorRef.current = cursor;
    }
  }, [canvasRef]);

  // Fonction utilitaire pour calculer le redimensionnement
  const calculateResize = useCallback((element: Element, handle: string, currentX: number, currentY: number, _startPos: { x: number, y: number }) => {
    const updates: ElementUpdates = {};

    const MIN_SIZE = 20;

    switch (handle) {
      case 'se': { // Sud-Est (coin bas-droit) - coin suit directement la souris
        updates.width = Math.max(MIN_SIZE, currentX - element.x);
        updates.height = Math.max(MIN_SIZE, currentY - element.y);
        break;
      }
      case 'sw': { // Sud-Ouest (coin bas-gauche)
        const newX = Math.min(currentX, element.x + element.width - MIN_SIZE);
        updates.width = Math.max(MIN_SIZE, element.x + element.width - newX);
        updates.x = newX;
        updates.height = Math.max(MIN_SIZE, currentY - element.y);
        break;
      }
      case 'ne': { // Nord-Est (coin haut-droit)
        const newY = Math.min(currentY, element.y + element.height - MIN_SIZE);
        updates.width = Math.max(MIN_SIZE, currentX - element.x);
        updates.height = Math.max(MIN_SIZE, element.y + element.height - newY);
        updates.y = newY;
        break;
      }
      case 'nw': { // Nord-Ouest (coin haut-gauche) - coin suit directement la souris
        const newX = Math.min(currentX, element.x + element.width - MIN_SIZE);
        const newY = Math.min(currentY, element.y + element.height - MIN_SIZE);
        updates.width = Math.max(MIN_SIZE, element.x + element.width - newX);
        updates.height = Math.max(MIN_SIZE, element.y + element.height - newY);
        updates.x = newX;
        updates.y = newY;
        break;
      }
      case 'n': { // Nord (haut)
        const newY = Math.min(currentY, element.y + element.height - MIN_SIZE);
        updates.height = Math.max(MIN_SIZE, element.y + element.height - newY);
        updates.y = newY;
        break;
      }
      case 's': { // Sud (bas) - coin suit directement la souris
        updates.height = Math.max(MIN_SIZE, currentY - element.y);
        break;
      }
      case 'w': { // Ouest (gauche)
        const newX = Math.min(currentX, element.x + element.width - MIN_SIZE);
        updates.width = Math.max(MIN_SIZE, element.x + element.width - newX);
        updates.x = newX;
        break;
      }
      case 'e': { // Est (droite) - coin suit directement la souris
        updates.width = Math.max(MIN_SIZE, currentX - element.x);
        break;
      }
    }

    // ✅ AJOUT: Appliquer le snap à la grille pour les positions lors du redimensionnement
    if (state.canvas.snapToGrid && state.canvas.gridSize > 0) {
      const gridSize = state.canvas.gridSize;
      const snapTolerance = 5;

      if (updates.x !== undefined) {
        const nearestGridX = Math.round(updates.x / gridSize) * gridSize;
        if (Math.abs(updates.x - nearestGridX) <= snapTolerance) {
          updates.x = nearestGridX;
        }
      }

      if (updates.y !== undefined) {
        const nearestGridY = Math.round(updates.y / gridSize) * gridSize;
        if (Math.abs(updates.y - nearestGridY) <= snapTolerance) {
          updates.y = nearestGridY;
        }
      }
    }

    return updates;
  }, [state.canvas.snapToGrid, state.canvas.gridSize]);

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

    // Calcul correct des coordonnées avec zoom et pan
    const canvasRelativeX = event.clientX - rect.left;
    const canvasRelativeY = event.clientY - rect.top;
    const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;
    const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;
    
    // Mettre à jour le curseur
    const cursor = getCursorAtPosition(x, y);
    updateCursor(cursor);

    // Gérer la sélection lasso/rectangle en cours
    // Note: Si les listeners globaux sont actifs, la gestion se fait dans globalMouseMoveRef
    if (isSelectingRef.current && !globalMouseMoveRef.current) {
      if (selectionMode === 'lasso') {
        // Ajouter le point actuel au lasso
        selectionPointsRef.current.push({ x, y });
        // Forcer le re-rendu pour afficher le lasso en temps réel
        setSelectionUpdateTrigger(prev => prev + 1);
      } else if (selectionMode === 'rectangle') {
        // Mettre à jour le rectangle de sélection
        const startX = Math.min(selectionStartRef.current.x, x);
        const startY = Math.min(selectionStartRef.current.y, y);
        const width = Math.abs(x - selectionStartRef.current.x);
        const height = Math.abs(y - selectionStartRef.current.y);
        selectionRectRef.current = { x: startX, y: startY, width, height };
        // Forcer le re-rendu pour afficher le rectangle en temps réel
        setSelectionUpdateTrigger(prev => prev + 1);
      }
      return;
    }

    if (isDraggingRef.current && selectedElementRef.current) {
      // ✅ OPTIMISATION FLUIDITÉ: Pour le drag multiple, passer directement les coordonnées actuelles de la souris
      // performDragUpdate calculera la nouvelle position pour chaque élément individuellement
      pendingDragUpdateRef.current = { x, y };

      // Programmer l'update avec RAF si pas déjà programmé
      if (rafIdRef.current === null) {
        rafIdRef.current = requestAnimationFrame(performDragUpdate);
      }
    } else if (isResizingRef.current && selectedElementRef.current && resizeHandleRef.current) {
      debugLog(`[CanvasInteraction] Resizing element ${selectedElementRef.current} with handle ${resizeHandleRef.current} at (${x.toFixed(1)}, ${y.toFixed(1)})`);
      
      // ✅ BALANCED: Preserve essential properties without overkill
      const lastState = lastKnownStateRef.current;
      const element = lastState.elements.find(el => el.id === selectedElementRef.current);
      if (!element) return;

      const resizeUpdates = calculateResize(element, resizeHandleRef.current, x, y, resizeMouseStartRef.current);

      // ✅ Preserve essential visual properties (corners, styling, etc.)
      const essentialUpdates: Record<string, unknown> = { ...resizeUpdates };

      // Keep all properties except the ones we're updating and updatedAt
      const elementAsRecord = element as Record<string, unknown>;
      Object.keys(elementAsRecord).forEach(key => {
        if (!(key in resizeUpdates) && key !== 'updatedAt') {
          essentialUpdates[key] = elementAsRecord[key];
        }
      });

      dispatch({
        type: 'UPDATE_ELEMENT',
        payload: {
          id: selectedElementRef.current,
          updates: essentialUpdates as Partial<Element>
        }
      });
    } else if (isRotatingRef.current && state.selection.selectedElements.length > 0) {
      debugLog(`[CanvasInteraction] Rotating ${state.selection.selectedElements.length} elements at mouse position (${x.toFixed(1)}, ${y.toFixed(1)})`);
      
      // ✅ OPTIMISATION FLUIDITÉ: Pour la rotation, passer les coordonnées actuelles de la souris
      // performRotationUpdate calculera la rotation pour tous les éléments
      pendingRotationUpdateRef.current = { x, y };

      // Programmer l'update avec RAF si pas déjà programmé
      if (rafIdRef.current === null) {
        rafIdRef.current = requestAnimationFrame(performRotationUpdate);
      }
    }
  }, [dispatch, canvasRef, getCursorAtPosition, updateCursor, calculateResize, state.canvas, performDragUpdate]);

  // Cleanup des listeners globaux au démontage du composant
  useEffect(() => {
    return () => {
      stopGlobalSelectionListeners();
    };
  }, [stopGlobalSelectionListeners]);

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

  // Fonctions helper pour la sélection avancée
  const isElementInRectangle = useCallback((element: Element, rect: { x: number; y: number; width: number; height: number }): boolean => {
    // Vérifier si l'élément intersecte ou est contenu dans le rectangle
    const elementRight = element.x + element.width;
    const elementBottom = element.y + element.height;
    const rectRight = rect.x + rect.width;
    const rectBottom = rect.y + rect.height;

    // Vérifier l'intersection
    return !(element.x > rectRight || elementRight < rect.x || element.y > rectBottom || elementBottom < rect.y);
  }, []);

  const isElementInLasso = useCallback((element: Element, points: { x: number; y: number }[]): boolean => {
    if (points.length < 3) return false;

    // Utiliser l'algorithme du point dans le polygone (ray casting)
    // Vérifier si le centre de l'élément est dans le lasso
    const centerX = element.x + element.width / 2;
    const centerY = element.y + element.height / 2;

    return isPointInPolygon(centerX, centerY, points);
  }, []);

  const isPointInPolygon = (x: number, y: number, polygon: { x: number; y: number }[]): boolean => {
    let inside = false;
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
      const xi = polygon[i].x, yi = polygon[i].y;
      const xj = polygon[j].x, yj = polygon[j].y;

      if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
        inside = !inside;
      }
    }
    return inside;
  };

  return {
    handleCanvasClick,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    handleContextMenu,
    // Informations pour le rendu visuel de la sélection
    selectionState: {
      isSelecting: isSelectingRef.current,
      selectionMode,
      selectionRect: selectionRectRef.current,
      selectionPoints: selectionPointsRef.current,
      updateTrigger: selectionUpdateTrigger
    }
  };
};



