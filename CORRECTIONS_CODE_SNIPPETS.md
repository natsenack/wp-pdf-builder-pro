# Guide de Correction - Code Snippets Détaillés

## 🔧 Correction Prioritaire #1: Fuites d'Event Listeners Globaux

### ❌ AVANT (Problématique)
```typescript
// useCanvasInteraction.ts (lignes 144-265)

const startGlobalSelectionListeners = useCallback(() => {
  if (globalMouseMoveRef.current || globalMouseUpRef.current) return;

  // ❌ PROBLÈME: Ces callbacks capturent le state courant mais si state change,
  // elles continueront à utiliser l'ancien state
  globalMouseMoveRef.current = (event: MouseEvent) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    const zoomScale = state.canvas.zoom / 100; // ❌ State capture stale!
    const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
    const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;

    if (selectionMode === "lasso") {
      selectionPointsRef.current.push({ x, y });
      setSelectionUpdateTrigger((prev) => prev + 1);
    } else if (selectionMode === "rectangle") {
      const startX = Math.min(selectionStartRef.current.x, x);
      const startY = Math.min(selectionStartRef.current.y, y);
      const width = Math.abs(x - selectionStartRef.current.x);
      const height = Math.abs(y - selectionStartRef.current.y);
      selectionRectRef.current = { x: startX, y: startY, width, height };
      setSelectionUpdateTrigger((prev) => prev + 1);
    }
  };

  globalMouseUpRef.current = () => {
    stopGlobalSelectionListeners();
    
    if (isSelectingRef.current) {
      let selectedElementIds: string[] = [];

      if (
        selectionMode === "lasso" &&
        selectionPointsRef.current.length > 2
      ) {
        // ❌ PROBLÈME: state.elements peut être stale/différent du state initial
        selectedElementIds = state.elements
          .filter((element) => {
            const centerX = element.x + element.width / 2;
            const centerY = element.y + element.height / 2;
            let inside = false;
            const polygon = selectionPointsRef.current;
            for (
              let i = 0, j = polygon.length - 1;
              i < polygon.length;
              j = i++
            ) {
              const xi = polygon[i].x,
                yi = polygon[i].y;
              const xj = polygon[j].x,
                yj = polygon[j].y;
              if (
                yi > centerY !== yj > centerY &&
                centerX < ((xj - xi) * (centerY - yi)) / (yj - yi) + xi
              ) {
                inside = !inside;
              }
            }
            return inside;
          })
          .map((element) => element.id);
      }
      // ... rest
      
      if (selectedElementIds.length > 0) {
        dispatch({ type: "SET_SELECTION", payload: selectedElementIds });
      }

      isSelectingRef.current = false;
      selectionPointsRef.current = [];
      selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
    }
  };

  // ❌ PROBLÈME: Les listeners sont ajoutés mais si cette fonction est rappelée
  // (à cause des dépendances), l'ancienne référence globalMouseMoveRef.current
  // reste dans le document avec le state stale
  document.addEventListener("mousemove", globalMouseMoveRef.current, {
    passive: false,
  });
  document.addEventListener("mouseup", globalMouseUpRef.current, {
    passive: false,
  });
}, [
  canvasRef,
  state.canvas.zoom,
  state.canvas.pan,
  state.elements,
  selectionMode,
  dispatch,
]); // ❌ Trop de dépendances!

const stopGlobalSelectionListeners = useCallback(() => {
  if (globalMouseMoveRef.current) {
    document.removeEventListener("mousemove", globalMouseMoveRef.current);
    globalMouseMoveRef.current = null;
  }
  if (globalMouseUpRef.current) {
    document.removeEventListener("mouseup", globalMouseUpRef.current);
    globalMouseUpRef.current = null;
  }
}, []);
```

### ✅ APRÈS (Correction)
```typescript
// useCanvasInteraction.ts - VERSION CORRIGÉE

const startGlobalSelectionListeners = useCallback(() => {
  // ✅ Vérifier d'abord si déjà actif
  if (globalMouseMoveRef.current) return;

  // ✅ SOLUTION: Les handlers créés ici utiliseront TOUJOURS lastKnownStateRef
  // qui est mis à jour dans un useEffect séparé
  const handleGlobalMouseMove = (event: MouseEvent) => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const rect = canvas.getBoundingClientRect();
    if (!validateCanvasRect(rect)) return;

    // ✅ Utiliser toujours lastKnownStateRef - JAMAIS state directement
    const currentState = lastKnownStateRef.current;
    const zoomScale = currentState.canvas.zoom / 100;
    const panX = currentState.canvas.pan.x;
    const panY = currentState.canvas.pan.y;

    const canvasRelativeX = event.clientX - rect.left;
    const canvasRelativeY = event.clientY - rect.top;
    const x = (canvasRelativeX - panX) / zoomScale;
    const y = (canvasRelativeY - panY) / zoomScale;

    // ✅ Utiliser selectionMode qui est récupéré à chaque appel
    const curMode = currentState.selection.selectedElements.length > 0
      && canvasSettings.selectionMultiSelectEnabled
      ? canvasSettings.canvasSelectionMode
      : "click";

    if (selectionMode === "lasso") {
      selectionPointsRef.current.push({ x, y });
      setSelectionUpdateTrigger((prev) => prev + 1);
    } else if (selectionMode === "rectangle") {
      const startX = Math.min(selectionStartRef.current.x, x);
      const startY = Math.min(selectionStartRef.current.y, y);
      const width = Math.abs(x - selectionStartRef.current.x);
      const height = Math.abs(y - selectionStartRef.current.y);
      selectionRectRef.current = { x: startX, y: startY, width, height };
      setSelectionUpdateTrigger((prev) => prev + 1);
    }
  };

  const handleGlobalMouseUp = () => {
    // ✅ IMPORTANT: Arrêter les listeners IMMÉDIATEMENT
    stopGlobalSelectionListeners();

    if (isSelectingRef.current) {
      // ✅ Utiliser lastKnownStateRef au lieu de state stale
      const currentState = lastKnownStateRef.current;
      let selectedElementIds: string[] = [];

      if (
        selectionMode === "lasso" &&
        selectionPointsRef.current.length > 2
      ) {
        selectedElementIds = currentState.elements
          .filter((element) =>
            isElementInLasso(element, selectionPointsRef.current),
          )
          .map((element) => element.id);
      } else if (
        selectionMode === "rectangle" &&
        selectionRectRef.current.width > 0 &&
        selectionRectRef.current.height > 0
      ) {
        selectedElementIds = currentState.elements
          .filter((element) =>
            isElementInRectangle(element, selectionRectRef.current),
          )
          .map((element) => element.id);
      }

      if (selectedElementIds.length > 0) {
        dispatch({ type: "SET_SELECTION", payload: selectedElementIds });
      } else {
        dispatch({ type: "CLEAR_SELECTION" });
      }

      isSelectingRef.current = false;
      selectionPointsRef.current = [];
      selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
    }
  };

  // ✅ Stocker les références
  globalMouseMoveRef.current = handleGlobalMouseMove;
  globalMouseUpRef.current = handleGlobalMouseUp;

  // ✅ Utiliser capture phase pour être certain d'être appelé en premier
  document.addEventListener("mousemove", handleGlobalMouseMove, {
    capture: true,
    passive: false,
  });
  document.addEventListener("mouseup", handleGlobalMouseUp, {
    capture: true,
    passive: false,
  });
}, [canvasRef, canvasSettings.canvasSelectionMode, canvasSettings.selectionMultiSelectEnabled, dispatch, selectionMode]);
// ✅ Dépendances réduites et essentielles

const stopGlobalSelectionListeners = useCallback(() => {
  if (globalMouseMoveRef.current) {
    // ✅ Utiliser capture: true pour matcher l'ajout
    document.removeEventListener("mousemove", globalMouseMoveRef.current, {
      capture: true,
    });
    globalMouseMoveRef.current = null;
  }
  if (globalMouseUpRef.current) {
    document.removeEventListener("mouseup", globalMouseUpRef.current, {
      capture: true,
    });
    globalMouseUpRef.current = null;
  }
}, []);

// ✅ Ajouter un cleanup au démontage du composant
useEffect(() => {
  return () => {
    stopGlobalSelectionListeners();
  };
}, [stopGlobalSelectionListeners]);
```

---

## 🔧 Correction Prioritaire #2: Désynchronisation Ref/State

### ❌ AVANT (Problématique)
```typescript
// useCanvasInteraction.ts (lignes 56-58, 800-810)

// ❌ PROBLÈME: Deux sources de vérité
const selectedElementsRef = useRef<string[]>([]); // Source 1: Local ref
// ... Plus tard dans le code:
// state.selection.selectedElements est utilisé (Source 2: Redux state)

// Syncing:
useEffect(() => {
  selectedElementsRef.current = state.selection.selectedElements;
  lastKnownStateRef.current = state;
}, [state.selection.selectedElements, state.elements, state.canvas]);

// Utilisation:
const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  const isAlreadySelected = state.selection.selectedElements.includes(
    clickedElement.id,
  ); // ✅ Utilise state

  // ...
  
  const selectedIds = lastState.selection.selectedElements; // ✅ Parfois utilise state
  // Mais ailleurs:
  if (isDraggingRef.current && selectedElementsRef.current.length > 0) {
    // ❌ Utilise la ref!
    performDragUpdate(); // Qui utilise lastState.selection.selectedElements
  }
}, [state, ...]);
```

### ✅ APRÈS (Correction)
```typescript
// useCanvasInteraction.ts - VERSION CORRIGÉE

// ✅ SOLUTION: Supprimer selectedElementsRef complètement
// const selectedElementsRef = useRef<string[]>([]); // ❌ DELETED

// Le seul état utilisé est lastKnownStateRef.current.selection.selectedElements

const performDragUpdate = useCallback(() => {
  if (!pendingDragUpdateRef.current) {
    return;
  }

  const { x: currentMouseX, y: currentMouseY } = pendingDragUpdateRef.current;
  const lastState = lastKnownStateRef.current;

  // ✅ Une seule source: lastKnownStateRef.current
  const selectedIds = lastState.selection.selectedElements;
  if (selectedIds.length === 0) {
    return;
  }

  // ... rest du code reste pareil
}, [dispatch, canvasWidth, canvasHeight]);

const handleMouseMove = useCallback(
  (event: React.MouseEvent<HTMLCanvasElement>) => {
    // ... validation...

    if (isDraggingRef.current && lastKnownStateRef.current.selection.selectedElements.length > 0) {
      // ✅ Utilise lastKnownStateRef directement
      const now = Date.now();
      if (now - lastUpdateTimeRef.current > 16) {
        pendingDragUpdateRef.current = { x, y };
        performDragUpdate();
        lastUpdateTimeRef.current = now;
      }
    }
  },
  [performDragUpdate, ...]
);

// ✅ IMPORTANT: Garder ce useEffect qui synce le state
useEffect(() => {
  lastKnownStateRef.current = state;
}, [state.selection.selectedElements, state.elements, state.canvas]);
```

---

## 🔧 Correction Prioritaire #3: Calculs de Coordonnées Instables

### ❌ AVANT (Problématique)
```typescript
// useCanvasInteraction.ts (lignes 194-207, 743-758)

const handleMouseDown = useCallback((event) => {
  const rect = canvas.getBoundingClientRect();
  const zoomScale = state.canvas.zoom / 100; // ❌ Peut changer
  const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;

  if (selectionMode === "lasso" || selectionMode === "rectangle") {
    isSelectingRef.current = true;
    selectionStartRef.current = { x, y }; // ❌ Basé sur state.canvas.zoom/pan
    selectionPointsRef.current = [{ x, y }];
    startGlobalSelectionListeners();
    event.preventDefault();
    return;
  }
}, [state, ...]);

const handleMouseMove = useCallback((event) => {
  const zoomScale = state.canvas.zoom / 100; // ❌ PEUT ÊTRE DIFFÉRENT!
  const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
  
  // Calcul basé sur différents zoom/pan
  if (selectionMode === "rectangle") {
    const startX = Math.min(selectionStartRef.current.x, x);
    const width = Math.abs(x - selectionStartRef.current.x);
    // ❌ Les calculs ont des magnitudes différentes si zoom a changé
  }
}, [state, ...]);
```

### ✅ APRÈS (Correction)
```typescript
// useCanvasInteraction.ts - VERSION CORRIGÉE

// ✅ Refs pour mémoriser les paramètres de transformation au démarrage
const selectionStartZoomRef = useRef<number>(1);
const selectionStartPanRef = useRef<{ x: number; y: number }>({ x: 0, y: 0 });

const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  const canvas = canvasRef.current;
  if (!canvas) return;

  const rect = canvas.getBoundingClientRect();
  if (!validateCanvasRect(rect)) return;

  // ✅ Mémoriser les paramètres de transformation AU DÉMARRAGE
  const currentZoom = state.canvas.zoom / 100;
  const currentPan = { ...state.canvas.pan };
  
  const canvasRelativeX = event.clientX - rect.left;
  const canvasRelativeY = event.clientY - rect.top;
  const x = (canvasRelativeX - currentPan.x) / currentZoom;
  const y = (canvasRelativeY - currentPan.y) / currentZoom;

  // ... traiter les clics sur éléments ...

  if (selectionMode === "lasso" || selectionMode === "rectangle") {
    // ✅ IMPORTANT: Mémoriser pour toute la durée de la sélection
    selectionStartZoomRef.current = currentZoom;
    selectionStartPanRef.current = currentPan;
    
    isSelectingRef.current = true;
    selectionStartRef.current = { x, y };
    selectionPointsRef.current = [{ x, y }];
    
    if (selectionMode === "rectangle") {
      selectionRectRef.current = { x, y, width: 0, height: 0 };
    }
    
    startGlobalSelectionListeners();
    event.preventDefault();
    return;
  }
}, [state, canvasRef, dispatch, getResizeHandleAtPosition, selectionMode]);

const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  const now = Date.now();
  if (now - lastMouseMoveTimeRef.current < MOUSEMOVE_THROTTLE_MS) {
    return;
  }
  lastMouseMoveTimeRef.current = now;

  const canvas = canvasRef.current;
  if (!canvas) return;

  const rect = canvas.getBoundingClientRect();

  // ✅ Lors du mousemove pendant la sélection, utiliser les paramètres mémorisés
  if (isSelectingRef.current && !globalMouseMoveRef.current) {
    // ✅ Utiliser les paramètres mémorisés, PAS les paramètres courants
    const zoomScale = selectionStartZoomRef.current;
    const panX = selectionStartPanRef.current.x;
    const panY = selectionStartPanRef.current.y;

    const canvasRelativeX = event.clientX - rect.left;
    const canvasRelativeY = event.clientY - rect.top;
    const x = (canvasRelativeX - panX) / zoomScale;
    const y = (canvasRelativeY - panY) / zoomScale;

    if (selectionMode === "lasso") {
      selectionPointsRef.current.push({ x, y });
      setSelectionUpdateTrigger((prev) => prev + 1);
    } else if (selectionMode === "rectangle") {
      // ✅ Maintenant les calculs sont cohérents (même zoom/pan)
      const startX = Math.min(selectionStartRef.current.x, x);
      const startY = Math.min(selectionStartRef.current.y, y);
      const width = Math.abs(x - selectionStartRef.current.x);
      const height = Math.abs(y - selectionStartRef.current.y);
      selectionRectRef.current = { x: startX, y: startY, width, height };
      setSelectionUpdateTrigger((prev) => prev + 1);
    }
    return;
  }

  // ... reste du code handleMouseMove ...
}, [dispatch, canvasRef, getCursorAtPosition, updateCursor, calculateResize, state.canvas, performDragUpdate, selectionMode]);
```

---

## 🔧 Correction #4-6: Nettoyage Complet des Refs

### ❌ AVANT (Problématique)
```typescript
const handleMouseUp = useCallback(() => {
  debugLog(`[CanvasInteraction] Mouse up - ending interactions...`);

  if (rafIdRef.current !== null) {
    cancelAnimationFrame(rafIdRef.current);
    rafIdRef.current = null;
  }

  // ✅ Finaliser la sélection lasso/rectangle...
  if (isSelectingRef.current) {
    // ... code de finalization ...
  }

  // ❌ PROBLÈME 1: dragStartRef n'est JAMAIS réinitialisé
  isDraggingRef.current = false;
  isResizingRef.current = false;
  isRotatingRef.current = false;
  
  // ❌ PROBLÈME 2: Les listeners globaux ne sont pas stoppés ici!
  // stopGlobalSelectionListeners() n'est appelé que dans mouseup handler des listeners globaux
  
  resizeHandleRef.current = null;
  selectedElementRef.current = null;
  rotationStartRef.current = {};
  pendingRotationUpdateRef.current = null;
  
  // ❌ PROBLÈME 3: dragMouseStartRef et resizeMouseStartRef ne sont pas réinitialisés
}, [performDragUpdate, performRotationUpdate, dispatch]);
```

### ✅ APRÈS (Correction)
```typescript
const handleMouseUp = useCallback(() => {
  debugLog(`[CanvasInteraction] Mouse up - ending interactions...`);

  // ✅ ÉTAPE 1: Arrêter les listeners globaux IMMÉDIATEMENT
  stopGlobalSelectionListeners();

  // ✅ ÉTAPE 2: Finir les updates en attente avec RAF
  if (rafIdRef.current !== null) {
    cancelAnimationFrame(rafIdRef.current);
    rafIdRef.current = null;

    // ✅ Effectuer un dernier update si en attente
    if (pendingDragUpdateRef.current) {
      performDragUpdate();
    }
    if (pendingRotationUpdateRef.current) {
      performRotationUpdate();
    }
  }

  // ✅ ÉTAPE 3: Finaliser la sélection lasso/rectangle
  if (isSelectingRef.current) {
    let selectedElementIds: string[] = [];
    const currentState = lastKnownStateRef.current;

    if (selectionMode === "lasso" && selectionPointsRef.current.length > 2) {
      selectedElementIds = currentState.elements
        .filter((element) =>
          isElementInLasso(element, selectionPointsRef.current),
        )
        .map((element) => element.id);
    } else if (
      selectionMode === "rectangle" &&
      selectionRectRef.current.width > 0 &&
      selectionRectRef.current.height > 0
    ) {
      selectedElementIds = currentState.elements
        .filter((element) =>
          isElementInRectangle(element, selectionRectRef.current),
        )
        .map((element) => element.id);
    }

    if (selectedElementIds.length > 0) {
      dispatch({ type: "SET_SELECTION", payload: selectedElementIds });
    } else {
      dispatch({ type: "CLEAR_SELECTION" });
    }
  }

  // ✅ ÉTAPE 4: Snapshot final de snap pour rotation
  if (isRotatingRef.current) {
    const lastState = lastKnownStateRef.current;
    const selectedIds = lastState.selection.selectedElements;
    
    selectedIds.forEach((elementId) => {
      const element = lastState.elements.find((el) => el.id === elementId);
      if (element) {
        let currentRotation = (element as any).rotation || 0;
        let normalizedRotation = currentRotation % 360;
        if (normalizedRotation > 180) normalizedRotation -= 360;
        if (normalizedRotation < -180) normalizedRotation += 360;

        const finalSnapThreshold = 10;
        if (Math.abs(normalizedRotation) <= finalSnapThreshold) {
          dispatch({
            type: "UPDATE_ELEMENT",
            payload: {
              id: elementId,
              updates: { rotation: 0 },
            },
          });
        }
      }
    });
  }

  // ✅ ÉTAPE 5: Vider TOUS les flags et refs de manière systématique
  isDraggingRef.current = false;
  isResizingRef.current = false;
  isRotatingRef.current = false;
  isSelectingRef.current = false;

  // ✅ Vider complètement les refs de position
  dragStartRef.current = {}; // ✅ Vider, pas juste undefined
  dragMouseStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  resizeMouseStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  rotationMouseStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  
  // ✅ Vider les refs de sélection
  selectionPointsRef.current = []; // ✅ Vider array
  selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 }; // ✅ Reset
  selectionStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  selectionStartZoomRef.current = 1; // ✅ Reset (from correction #3)
  selectionStartPanRef.current = { x: 0, y: 0 }; // ✅ Reset
  
  // ✅ Vider les autres refs
  resizeHandleRef.current = null; // ✅ Reset
  selectedElementRef.current = null; // ✅ Reset
  rotationStartRef.current = {}; // ✅ Vider
  
  // ✅ Vider les refs d'updates en attente
  pendingDragUpdateRef.current = null; // ✅ Reset
  pendingRotationUpdateRef.current = null; // ✅ Reset

  // ✅ Réinitialiser les refs de timing
  lastUpdateTimeRef.current = 0; // ✅ Reset
  lastMouseMoveTimeRef.current = 0; // ✅ Reset
  
  debugLog(`[CanvasInteraction] All refs cleaned up successfully`);
}, [stopGlobalSelectionListeners, performDragUpdate, performRotationUpdate, dispatch, selectionMode]);

// ✅ IMPORTANT: Ajouter cleanup au démontage du composant
useEffect(() => {
  return () => {
    handleMouseUp(); // Appeler pour nettoyer  si le composant démonte pendant une interaction
  };
}, [handleMouseUp]);
```

---

## 🔧 Correction #7: Error Handling du Drop

### ❌ AVANT (Problématique)
```typescript
const handleDrop = useCallback(
  (e: React.DragEvent) => {
    if (!dragEnabled) return;

    e.preventDefault();
    e.stopPropagation();
    setIsDragOver(false);

    debugLog("[CanvasDrop] Processing drop event");

    try {
      const rawData = e.dataTransfer.getData("application/json");

      if (!rawData) {
        debugWarn("[CanvasDrop] No drag data received");
        throw new Error("No drag data received"); // ❌ Lance une Error
      }

      const dragData = JSON.parse(rawData);
      
      if (!validateDragData(dragData)) {
        throw new Error("Invalid drag data structure"); // ❌ Lance une Error
      }

      const position = calculateDropPosition(
        e.clientX,
        e.clientY,
        elementWidth,
        elementHeight,
      );
      // ❌ calculateDropPosition peut lancer une Error

      const newElement = createElementFromDragData(dragData, position);

      dispatch({ type: "ADD_ELEMENT", payload: newElement });
      debugLog(`[CanvasDrop] Element added to canvas successfully`);
    } catch (error) {
      debugError(`[CanvasDrop] Drop failed:`, error);
      // ❌ Aucun feedback utilisateur! L'utilisateur ne sait pas pourquoi ça a échoué
    }
  },
  [...]
);
```

### ✅ APRÈS (Correction)
```typescript
const calculateDropPosition = useCallback(
  (clientX: number, clientY: number, elementWidth: number = 100, elementHeight: number = 50) => {
    const wrapper = canvasRef.current;
    
    // ✅ SOLUTION: Validation douce avec fallback
    if (!wrapper) {
      debugWarn("[CanvasDrop] Canvas wrapper not available, using center position");
      // Retourner une position par défaut au lieu de lancer une Error
      return {
        x: 50,
        y: 50,
        originalCanvasX: 0,
        originalCanvasY: 0,
        transformedX: 50,
        transformedY: 50,
      };
    }

    const rect = wrapper.getBoundingClientRect();

    if (rect.width <= 0 || rect.height <= 0) {
      debugWarn("[CanvasDrop] Invalid canvas dimensions", {
        width: rect.width,
        height: rect.height,
      });
      // Fallback au lieu d'Error
      return {
        x: 50,
        y: 50,
        originalCanvasX: 0,
        originalCanvasY: 0,
        transformedX: 50,
        transformedY: 50,
      };
    }

    const zoomScale = state.canvas.zoom / 100;
    const canvasX = clientX - rect.left;
    const canvasY = clientY - rect.top;

    // ✅ SOLUTIONS: Clamp les coordonnées au lieu de les ignorer
    const clampedCanvasX = Math.max(0, Math.min(canvasX, rect.width));
    const clampedCanvasY = Math.max(0, Math.min(canvasY, rect.height));

    const transformedX = (clampedCanvasX - state.canvas.pan.x) / zoomScale;
    const transformedY = (clampedCanvasY - state.canvas.pan.y) / zoomScale;

    const centeredX = Math.max(0, transformedX - elementWidth / 2);
    const centeredY = Math.max(0, transformedY - elementHeight / 2);

    const clampedX = Math.max(0, Math.min(centeredX, canvasWidth - elementWidth));
    const clampedY = Math.max(0, Math.min(centeredY, canvasHeight - elementHeight));

    debugLog(
      `[CanvasDrop] Position calculation: client(${clientX}, ${clientY}) -> final(${clampedX}, ${clampedY})`,
    );

    return {
      x: clampedX,
      y: clampedY,
      originalCanvasX: canvasX,
      originalCanvasY: canvasY,
      transformedX,
      transformedY,
    };
  },
  [canvasRef, canvasWidth, canvasHeight, state.canvas]
);

const handleDrop = useCallback(
  (e: React.DragEvent) => {
    if (!dragEnabled) return;

    e.preventDefault();
    e.stopPropagation();

    // ✅ IMPORTANT: Toujours masquer le highlight, même en cas d'erreur
    setIsDragOver(false);

    debugLog("[CanvasDrop] Processing drop event");

    try {
      const rawData = e.dataTransfer.getData("application/json");

      if (!rawData) {
        debugWarn("[CanvasDrop] No drag data received");
        // ✅ Ne pas lancer d'Error, juste retourner
        showNotification?.({
          type: "warning",
          message: "Aucune donnée de drag reçue",
          duration: 3000,
        });
        return;
      }

      let dragData: unknown;
      try {
        dragData = JSON.parse(rawData);
      } catch (parseError) {
        debugError("[CanvasDrop] JSON parse error:", parseError);
        showNotification?.({
          type: "error",
          message: "Données de drag invalides (JSON malformé)",
          duration: 3000,
        });
        return;
      }

      if (!validateDragData(dragData)) {
        debugWarn("[CanvasDrop] Invalid drag data structure:", dragData);
        showNotification?.({
          type: "error",
          message: "Structure de données de drag invalide",
          duration: 3000,
        });
        return;
      }

      const elementWidth = (dragData.defaultProps.width as number) || 100;
      const elementHeight = (dragData.defaultProps.height as number) || 50;

      // ✅ calculateDropPosition retourne toujours une position valide
      const position = calculateDropPosition(
        e.clientX,
        e.clientY,
        elementWidth,
        elementHeight,
      );

      const newElement = createElementFromDragData(dragData, position);

      // Vérification des conflits d'ID
      const existingElement = elements.find((el) => el.id === newElement.id);
      if (existingElement) {
        newElement.id = generateElementId(dragData.type);
        debugWarn(`[CanvasDrop] ID conflict resolved, new ID: ${newElement.id}`);
      }

      dispatch({ type: "ADD_ELEMENT", payload: newElement });
      debugLog(`[CanvasDrop] Element added successfully`);

      // ✅ Feedback utilisateur sucesss
      showNotification?.({
        type: "success",
        message: "Élément ajouté au canvas",
        duration: 2000,
      });
    } catch (error) {
      debugError(`[CanvasDrop] Unexpected drop error:`, error);
      
      // ✅ Feedback utilisateur pour erreurs inattendues
      showNotification?.({
        type: "error",
        message: "Erreur lors du drop: " + (error instanceof Error ? error.message : "Erreur inconnue"),
        duration: 4000,
      });
    }
  },
  [
    validateDragData,
    calculateDropPosition,
    createElementFromDragData,
    elements,
    dispatch,
    generateElementId,
    dragEnabled,
    showNotification,
  ]
);
```

---

## Résumé des fichiers à modifier

| Fichier | Sections | Lignes |
|---------|----------|--------|
| `useCanvasInteraction.ts` | startGlobalSelectionListeners, stopGlobalSelectionListeners | 144-265 |
| `useCanvasInteraction.ts` | Supprimer selectedElementsRef, useEffect sync | 56-58, 800-810 |
| `useCanvasInteraction.ts` | Ajouter selectionStartZoomRef, selectionStartPanRef | - |
| `useCanvasInteraction.ts` | handleMouseDown, handleMouseMove pour sélection | 743-1341 |
| `useCanvasInteraction.ts` | handleMouseUp nettoyage complet | 1106-1136 |
| `useCanvasDrop.ts` | calculateDropPosition error handling | 50-100 |
| `useCanvasDrop.ts` | handleDrop avec feedback utilisateur | 130-200 |

