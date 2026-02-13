# Analyse Complète du Système Drag & Drop et Sélection Rectangle - Canvas React

**Date:** 13 février 2026  
**Scope:** `useCanvasInteraction.ts`, `useCanvasDrop.ts`, `Canvas.tsx`  
**Total Problèmes Identifiés:** 9 (critique: 3, important: 4, modéré: 2)

---

## 🔴 PROBLÈMES CRITIQUES

### 1. **Fuites d'Event Listeners Globaux (CRITIQUE)**

**Fichier:** `useCanvasInteraction.ts` (lignes 175-265)  
**Sévérité:** CRITIQUE - Peut causer des memory leaks et comportements imprédictibles

#### Root Cause
Dans `startGlobalSelectionListeners()`, les listeners `mousemove` et `mouseup` sont ajoutés à `document` avec des références de fonction stockées dans `globalMouseMoveRef.current` et `globalMouseUpRef.current`. Cependant:
1. Les callbacks sont créées dans le scope du `useCallback` avec des dépendances
2. Si les dépendances changent, les anciennes références sont conservées
3. `stopGlobalSelectionListeners()` ne supprime que les listeners actuels, pas les anciens

#### Impact
- **Memory Leak:** Les listeners persistent en mémoire si state change
- **Comportements fantômes:** Les événements déclenchent des callbacks avec un state obsolète
- **Sélection bugguée:** Si le state change pendant la sélection (pan/zoom), les calculs sont incorrects

#### Code Problématique
```typescript
// ❌ PROBLÈME: globalMouseMoveRef et globalMouseUpRef changent mais les listeners ne sont jamais cleanés
globalMouseMoveRef.current = (event: MouseEvent) => {
  // ... utilise state.canvas.zoom, state.canvas.pan, state.elements
  // Si state change, la fonction n'est pas re-exécutée car elle garde la même référence
};

globalMouseUpRef.current = () => {
  // ... utilise state.elements, state.selection
  // LE STATE UTILISÉ ICI EST STALE
  selectedElementIds = state.elements.filter(...); // ❌ State stale!
};

// Ajout des listeners
document.addEventListener("mousemove", globalMouseMoveRef.current, {
  passive: false,
});
document.addEventListener("mouseup", globalMouseUpRef.current, {
  passive: false,
});
```

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Utiliser event delegation avec une reffresh du state
const startGlobalSelectionListeners = useCallback(() => {
  if (globalMouseMoveRef.current || globalMouseUpRef.current) return;

  const handleGlobalMouseMove = (event: MouseEvent) => {
    const currentState = lastKnownStateRef.current; // Toujours à jour
    // ... reste du code
  };

  const handleGlobalMouseUp = () => {
    const currentState = lastKnownStateRef.current; // Toujours à jour
    // ... rest
    // IMPORTANT: Stopper immédiatement les listeners avant dispatch
    stopGlobalSelectionListeners();
  };

  globalMouseMoveRef.current = handleGlobalMouseMove;
  globalMouseUpRef.current = handleGlobalMouseUp;

  document.addEventListener("mousemove", handleGlobalMouseMove, {
    capture: true, // Utiliser capture pour être certain d'être le premier
    passive: false,
  });
  document.addEventListener("mouseup", handleGlobalMouseUp, {
    capture: true,
    passive: false,
  });
}, [lastKnownStateRef]); // Pas d'autre dépendance!

const stopGlobalSelectionListeners = useCallback(() => {
  if (!globalMouseMoveRef.current || !globalMouseUpRef.current) return;

  // Supprimer avec les mêmes références exactes
  document.removeEventListener("mousemove", globalMouseMoveRef.current, {
    capture: true,
  });
  document.removeEventListener("mouseup", globalMouseUpRef.current, {
    capture: true,
  });

  globalMouseMoveRef.current = null;
  globalMouseUpRef.current = null;
}, []);
```

---

### 2. **Désynchronisation Ref/State pour la Sélection (CRITIQUE)**

**Fichier:** `useCanvasInteraction.ts` (lignes 56-58, 800-810)  
**Sévérité:** CRITIQUE - Cause des incohérences de sélection et de drag

#### Root Cause
- `selectedElementsRef.current` est mis à jour depuis `state.selection.selectedElements` dans un useEffect (ligne 800)
- Mais `selectedElementsRef.current` est aussi utilisé dans `performDragUpdate()` pour déterminer les éléments à déplacer (ligne 350)
- Et `state.selection.selectedElements` est utilisé dans `handleMouseDown()` (ligne 752)
- Ces deux sources de vérité peuvent diverger lors d'updates rapides

#### Impact
- **Drag incorrect:** Des éléments peuvent être draggés sans être réellement sélectionnés
- **Sélection fantôme:** La sélection visuelle et la sélection logique ne correspondent pas
- **Actions perdues:** Un élément peut être sélectionné visuellement mais pas en état Redux

#### Code Problématique
```typescript
// ❌ PROBLÈME 1: Deux sources de vérité
selectedElementsRef.current = state.selection.selectedElements; // (ligne 800)

// ❌ PROBLÈME 2: Utilisation directe de la ref sans vérification du state
if (isDraggingRef.current && selectedElementsRef.current.length > 0) { // (ligne 1336)
  const now = Date.now();
  if (now - lastUpdateTimeRef.current > 16) {
    pendingDragUpdateRef.current = { x, y };
    performDragUpdate(); // Utilise lastKnownStateRef.current au lieu de state
    lastUpdateTimeRef.current = now;
  }
}

// ❌ PROBLÈME 3: performDragUpdate utilise selectedIds du state, pas de la ref
const selectedIds = lastState.selection.selectedElements; // (ligne 361)
```

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Une seule source de vérité - utiliser toujours lastKnownStateRef
// Supprimer selectedElementsRef complètement

// Dans performDragUpdate():
const performDragUpdate = useCallback(() => {
  if (!pendingDragUpdateRef.current) return;

  const { x: currentMouseX, y: currentMouseY } = pendingDragUpdateRef.current;
  const lastState = lastKnownStateRef.current; // ✅ Unique source

  // Utiliser DIRECTEMENT from lastState, pas de selectedElementsRef
  const selectedIds = lastState.selection.selectedElements;
  
  // ... rest reste pareil
}, []);

// Et supprimer l'useEffect qui synce la ref:
// ❌ SUPPRIMER:
// useEffect(() => {
//   selectedElementsRef.current = state.selection.selectedElements;
//   lastKnownStateRef.current = state;
// }, [state.selection.selectedElements, state.elements, state.canvas]);
```

---

### 3. **Calculs de Coordonnées Instables avec Zoom/Pan (CRITIQUE)**

**Fichier:** `useCanvasInteraction.ts` (lignes 194-207, 743-758)  
**Sévérité:** CRITIQUE - Les sélections rectangle/lasso sont inexactes

#### Root Cause
Les coordonnées sont calculées en utilisant directement `state.canvas.zoom` et `state.canvas.pan`:
```typescript
const zoomScale = state.canvas.zoom / 100; // ❌ Peut être stale
const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale; // ❌ Pan peut être stale
const y = (canvasRelativeY - state.canvas.pan.y) / zoomScale;
```

Mais si l'utilisateur pan/zoom pendant la sélection rectangle, le calcul initial vs final sont différents, causant une sélection incorrecte.

#### Impact
- **Sélection rectangle inexacte:** Les points de départ et fin sont calculés avec des zoom/pan différents
- **Sélection lasso jittery:** Les points ajoutés à `selectionPointsRef.current` utilisent des zoom/pan inconsistents
- **Sélection d'éléments fantôme:** Des éléments non cliqués peuvent être sélectionnés

#### Code Problématique
```typescript
// ❌ PROBLÈME: Zoom et pan peuvent changer pendant la sélection
const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  const zoomScale = state.canvas.zoom / 100; // 🔴 PAS GARANTI D'ÊTRE CONSTANT
  const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;

  // Plus tard...
  selectionStartRef.current = { x, y }; // Début de sélection
}, [state, ...]);

const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  const zoomScale = state.canvas.zoom / 100; // 🔴 PEUT ÊTRE DIFFÉRENT!
  const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
  
  // Calcul du rectangle avec zoom potentiellement différent
  const width = Math.abs(x - selectionStartRef.current.x); // ❌ Magnitudes différentes
}, [state, ...]);
```

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Mémoriser le zoom/pan au démarrage de la sélection
const selectionStartZoomRef = useRef<number>(1);
const selectionStartPanRef = useRef<{ x: number; y: number }>({ x: 0, y: 0 });

const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  // ... validation code ...
  
  if (selectionMode === "lasso" || selectionMode === "rectangle") {
    // ✅ Mémoriser les paramètres au démarrage
    selectionStartZoomRef.current = state.canvas.zoom / 100;
    selectionStartPanRef.current = { ...state.canvas.pan };
    
    // Calculated coords avec ces paramètres mémorisés
    const zoomScale = selectionStartZoomRef.current;
    const x = (canvasRelativeX - selectionStartPanRef.current.x) / zoomScale;
    const y = (canvasRelativeY - selectionStartPanRef.current.y) / zoomScale;
    
    isSelectingRef.current = true;
    selectionStartRef.current = { x, y };
    startGlobalSelectionListeners();
    event.preventDefault();
    return;
  }
}, [state, ...]);

const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  // ... validation ...
  
  if (isSelectingRef.current && !globalMouseMoveRef.current) {
    // ✅ Utiliser les paramètres mémorisés, pas les paramètres courants
    const zoomScale = selectionStartZoomRef.current;
    const panX = selectionStartPanRef.current.x;
    const panY = selectionStartPanRef.current.y;
    
    const x = (canvasRelativeX - panX) / zoomScale;
    const y = (canvasRelativeY - panY) / zoomScale;
    
    // Maintenant les calculs sont cohérents
    if (selectionMode === "rectangle") {
      const startX = Math.min(selectionStartRef.current.x, x);
      const width = Math.abs(x - selectionStartRef.current.x);
      selectionRectRef.current = { x: startX, y: startY, width, height };
    }
  }
}, []);
```

---

## 🟠 PROBLÈMES IMPORTANTS

### 4. **Dépendances useCallback Incohérentes (IMPORTANT)**

**Fichier:** `useCanvasInteraction.ts` (lignes 144-161, 743-765)  
**Sévérité:** IMPORTANT - Cause des re-calculations inutiles et state stale

#### Root Cause
```typescript
// ❌ PROBLÈME 1: Dépendances trop restrictives
const startGlobalSelectionListeners = useCallback(() => {
  // Utilise: state.canvas.zoom, state.canvas.pan, state.elements, selectionMode, dispatch
}, [canvasRef, state.canvas.zoom, state.canvas.pan, state.elements, selectionMode, dispatch]);
// ^^ Toute modification du zoom/pan crée une nouvelle fonction et les listeners précédents ne sont pas cleanés

// ❌ PROBLÈME 2: Dépendances manquantes
const handleMouseDown = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  // Utilise: dragStartRef, selectedElementsRef, selectionMode, canvasSettings
  const isMultiSelect = canvasSettings.selectionMultiSelectEnabled && event.ctrlKey;
  // ^^ canvasSettings.selectionMultiSelectEnabled n'est pas dans les dépendances
}, [state, canvasRef, dispatch, getResizeHandleAtPosition]);
//  ^^ state entier au lieu de dépendances spécifiques
```

#### Impact
- **Listeners zombies:** Quand state.canvas.zoom change, une nouvelle fonction est créée mais l'ancienne disparaît du ref, créant un listener orphelin
- **Stale closures:** Des fonctions capturent un state ancien et l'utilisent
- **Performance:** Re-creation inutiles de functions

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Dépendances ciblées et lastKnownStateRef
const startGlobalSelectionListeners = useCallback(() => {
  // Pas de dépendance sur state directement - utiliser lastKnownStateRef
  // qui est mis à jour dans un useEffect séparé
  globalMouseMoveRef.current = (event: MouseEvent) => {
    const currentState = lastKnownStateRef.current; // ✅ Toujours à jour
    const zoomScale = currentState.canvas.zoom / 100;
    // ... rest
  };
  // ...
}, []); // ✅ Pas de dépendances - la fonction capture lastKnownStateRef qui change
```

---

### 5. **Système de Throttling et RAF Désynchronisés (IMPORTANT)**

**Fichier:** `useCanvasInteraction.ts` (lignes 1321-1341)  
**Sévérité:** IMPORTANT - Drag/resize saccadés ou trop rapides

#### Root Cause
```typescript
// ❌ PROBLÈME: Deux systèmes de throttling qui se battent
const MOUSEMOVE_THROTTLE_MS = 8; // Throttle à 8ms
const lastMouseMoveTimeRef = useRef<number>(0);

const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  // ✅ Throttling basé sur temps
  const now = Date.now();
  if (now - lastMouseMoveTimeRef.current < MOUSEMOVE_THROTTLE_MS) {
    return; // Skip cet event
  }
  lastMouseMoveTimeRef.current = now;

  // Mais aussi RAF limiting:
  if (isDraggingRef.current && selectedElementsRef.current.length > 0) {
    const now = Date.now(); // ❌ Calculé deux fois!
    if (now - lastUpdateTimeRef.current > 16) { // ❌ Throttle différent (16ms)
      pendingDragUpdateRef.current = { x, y };
      performDragUpdate(); // ✅ Appelé directement, pas via RAF
      lastUpdateTimeRef.current = now;
    }
  }
}, [performDragUpdate, ...]);
```

#### Impact
- **Drag inconsistant:** Parfois rapide (8ms), parfois lent (16ms)
- **Saut de frames:** Les updates ne sont pas synchronisées avec le repaint
- **CPU waste:** Double calcul de timestamp

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Un seul système de throttling cohérent
const MOUSEMOVE_THROTTLE_MS = 16; // ~60 FPS

const handleMouseMove = useCallback((event: React.MouseEvent<HTMLCanvasElement>) => {
  // ✅ Un seul throttling
  const now = performance.now();
  if (now - lastMouseMoveTimeRef.current < MOUSEMOVE_THROTTLE_MS) {
    return;
  }
  lastMouseMoveTimeRef.current = now;

  // ... rest du code
  
  if (isDraggingRef.current) {
    // ✅ Pas de throttling supplémentaire ici - déjà fait plus haut
    pendingDragUpdateRef.current = { x, y };
    // RAF gardera la fonction quelque part si nécessaire
    if (rafIdRef.current === null) {
      rafIdRef.current = requestAnimationFrame(() => {
        performDragUpdate();
        rafIdRef.current = null;
      });
    }
  }
}, []);
```

---

### 6. **Nettoyage Incomplet des Refs Après Drag/Drop (IMPORTANT)**

**Fichier:** `useCanvasInteraction.ts` (lignes 1106-1136)  
**Sévérité:** IMPORTANT - Drag/résise peuvent rester "actifs" après mouseup

#### Root Cause
```typescript
const handleMouseUp = useCallback(() => {
  // ✅ Flags réinitialisés
  isDraggingRef.current = false;
  isResizingRef.current = false;
  isRotatingRef.current = false;
  resizeHandleRef.current = null;
  selectedElementRef.current = null;
  rotationStartRef.current = {};
  pendingRotationUpdateRef.current = null;
  
  // ❌ PROBLÈME 1: JAMAIS réinitialisé
  // dragStartRef reste rempli
  
  // ❌ PROBLÈME 2: RAF n'est pas toujours annulé
  if (rafIdRef.current !== null) {
    cancelAnimationFrame(rafIdRef.current);
    rafIdRef.current = null;
  }
  // Mais si performDragUpdate est appelé ailleurs, rafIdRef peut ne pas être nettoyé
  
  // ❌ PROBLÈME 3: Global listeners peuvent rester
  // stopGlobalSelectionListeners() n'est même pas appelé ici!
}, [performDragUpdate, performRotationUpdate, dispatch]);
```

#### Impact
- **Drag fantôme:** Après mouseup, dragStartRef contient des données qui peuvent être réutilisées
- **Mémoire:** dragStartRef accumule des positions sans jamais être nettoyé
- **Comportements bizarres:** Un deuxième drag peut utiliser des données du drag précédent

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Nettoyage complet
const handleMouseUp = useCallback(() => {
  // Stopper les listeners globaux d'abord
  stopGlobalSelectionListeners();

  // Annuler RAF en cours
  if (rafIdRef.current !== null) {
    cancelAnimationFrame(rafIdRef.current);
    rafIdRef.current = null;
  }

  // ✅ Effectuer dernière update si nécessaire
  if (pendingDragUpdateRef.current) {
    performDragUpdate();
  }
  if (pendingRotationUpdateRef.current) {
    performRotationUpdate();
  }

  // ✅ Vider TOUTES les refs
  isDraggingRef.current = false;
  isResizingRef.current = false;
  isRotatingRef.current = false;
  isSelectingRef.current = false;
  resizeHandleRef.current = null;
  selectedElementRef.current = null;
  dragStartRef.current = {}; // ✅ Vider le contenu
  dragMouseStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  resizeMouseStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  rotationMouseStartRef.current = { x: 0, y: 0 }; // ✅ Reset
  rotationStartRef.current = {};
  pendingDragUpdateRef.current = null;
  pendingRotationUpdateRef.current = null;
  selectionPointsRef.current = []; // ✅ Vider
  selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 }; // ✅ Reset
}, [stopGlobalSelectionListeners, performDragUpdate, performRotationUpdate]);
```

---

### 7. **useCanvasDrop Pas d'Erreur Handling (IMPORTANT)**

**Fichier:** `useCanvasDrop.ts` (lignes 50-100)  
**Sévérité:** IMPORTANT - Erreurs silencieuses en drag/drop

#### Root Cause
```typescript
const calculateDropPosition = useCallback((clientX, clientY, ...) => {
  const wrapper = canvasRef.current;
  if (!wrapper) {
    throw new Error("Canvas wrapper ref not available"); // ❌ Lance une Error
  }

  const rect = wrapper.getBoundingClientRect();

  if (rect.width <= 0 || rect.height <= 0) {
    throw new Error("Invalid canvas dimensions"); // ❌ Lance une Error
  }

  // Validation des coordonnées
  if (canvasX < 0 || canvasY < 0 || canvasX > rect.width || canvasY > rect.height) {
    // ❌ AUCUN LOG! Silencieusement ne fait rien
  }
  // ...
}, [canvasRef, ...]);

const handleDrop = useCallback((e: React.DragEvent) => {
  try {
    // ... validation et dispatch
    dispatch({ type: "ADD_ELEMENT", payload: newElement });
  } catch (error) {
    debugError(`[CanvasDrop] Drop failed:`, error); // ❌ Log uniquement, pas de feedback utilisateur
  }
}, [...]);
```

#### Impact
- **Drops échouées silencieusement:** L'utilisateur ne sait pas pourquoi son drag/drop n'a pas marché
- **Pas de fallback:** Si calculateDropPosition échoue, pas de position par défaut
- **Difficile à debugger:** Les erreurs sont loggées mais pas communicées à l'utilisateur

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Validation douce + fallback
const calculateDropPosition = useCallback(
  (...) => {
    const wrapper = canvasRef.current;
    if (!wrapper) {
      debugWarn("[CanvasDrop] Canvas wrapper not available, using fallback position");
      return { x: 50, y: 50, originalCanvasX: 0, originalCanvasY: 0, transformedX: 50, transformedY: 50 };
    }

    const rect = wrapper.getBoundingClientRect();
    if (rect.width <= 0 || rect.height <= 0) {
      debugWarn("[CanvasDrop] Invalid canvas dimensions", { width: rect.width, height: rect.height });
      return { x: 50, y: 50, originalCanvasX: 0, originalCanvasY: 0, transformedX: 50, transformedY: 50 };
    }

    // Clamp les coordonnées au lieu de les valider seulement
    const clampedCanvasX = Math.max(0, Math.min(canvasX, rect.width));
    const clampedCanvasY = Math.max(0, Math.min(canvasY, rect.height));

    // ... rest avec clamped values
    return { x, y, ... };
  },
  [...]
);

const handleDrop = useCallback((e: React.DragEvent) => {
  e.preventDefault();
  e.stopPropagation();
  setIsDragOver(false); // ✅ Toujours masquer le highlight

  try {
    // ... existing code
    dispatch({ type: "ADD_ELEMENT", payload: newElement });
    debugLog("[CanvasDrop] Element added successfully");
  } catch (error) {
    debugError(`[CanvasDrop] Drop failed:`, error);
    
    // ✅ Feedback utilisateur ou tooltip?
    // Optionnellement: Afficher un toast/alert
    if (window.showNotification) {
      window.showNotification({
        type: "error",
        message: "Impossible d'ajouter l'élément au canvas",
        duration: 3000,
      });
    }
  }
}, [...]);
```

---

## 🟡 PROBLÈMES MODÉRÉS

### 8. **Cache d'Images Sans Limites de Taille (MODÉRÉ)**

**Fichier:** `Canvas.tsx` (lignes 3680-3720)  
**Sévérité:** MODÉRÉ - Fuite mémoire progressive

#### Root Cause
```typescript
const cleanupImageCache = useCallback(() => {
  const cache = imageCache.current;
  const currentMemory = calculateCacheMemoryUsage();

  // ✅ Check réalisé
  if (isMemoryLimitExceeded() || cache.size > MAX_CACHE_ITEMS) {
    // ✅ Cleanup réalisé
    // Mais le calcul du "memory to free" peut être inexact
    
    // ❌ PROBLÈME: Estimée la taille d'une image est imprécis
    const estimateImageMemorySize = (img: HTMLImageElement): number => {
      const bytesPerPixel = 4;
      return img.naturalWidth * img.naturalHeight * bytesPerPixel; // ❌ Oublie le codec
    };
    // Une image JPEG compressée prend beaucoup moins que naturalWidth * naturalHeight * 4
  }
}, [calculateCacheMemoryUsage, memoryLimitJs]);
```

#### Impact
- **Surestimation mémoire:** Les images utilisent moins de mémoire que calculée
- **Cache trop agressivement nettoyé:** Des images bonnes sont supprimées
- **Instabilité:** Comportement imprévisible si estimations sont fausses

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Utiliser actualMemoryUsage au lieu d'estimation
const estimateImageMemorySize = (img: HTMLImageElement): number => {
  // ✅ Approche plus précise: mesurer l'objet Image réel
  if ((img as any).memoryUsage !== undefined) {
    return (img as any).memoryUsage;
  }

  // Fallback: Estimation conservative
  // Une image en cache peut prendre plusieurs formats (uncompressed, compressed, metadata)
  const uncompressedSize = img.naturalWidth * img.naturalHeight * 4; // RGBA
  const compressionRatio = 0.3; // Les images JPEG/WebP sont ~30% de la taille uncompressed
  return uncompressedSize * compressionRatio;
};

// Ajouter un listener pour obtenir la taille réelle quand possible
img.onload = () => {
  // ✅ Essayer d'obtenir la taille du fichier original
  fetch(imageUrl, { method: "HEAD" })
    .then((response) => {
      const contentLength = response.headers.get("content-length");
      if (contentLength) {
        (img as any).memoryUsage = parseInt(contentLength);
      }
    })
    .catch(() => {
      // Utiliser l'estimation
    });
};
```

---

### 9. **Initialisation de State Incohérente (MODÉRÉ)**

**Fichier:** `useCanvasInteraction.ts` (lignes 30-70)  
**Sévérité:** MODÉRÉ - Peut causer des comportements inattendus au démarrage

#### Root Cause
```typescript
// ❌ PROBLÈME: État initial avec dépendances non synchronisées
const selectionMode = canvasSettings.selectionMultiSelectEnabled
  ? canvasSettings.canvasSelectionMode
  : "click";
// Cette valeur est calculée à chaque render, donc selectionMode peut changer
// si canvasSettings change, mais les refs ne sont pas mises à jour

// ❌ PROBLÈME 2: isSelectingRef et autres flags ne sont jamais initialisés
const isSelectingRef = useRef(false);
const isDraggingRef = useRef(false);
// Ces flags sont réinitialisés dans handleMouseUp, mais que se passe-t-il
// si le composant est monté avec isDraggingRef = true? (edge case très rare)
```

#### Impact
- **Mode de sélection qui change soudainement:** Si l'utilisateur change les settings pendant une sélection
- **Comportements incohérents au démarrage:** Si le componeny est re-mounted
- **Difficult à tester:** Les conditions initiales ne sont pas garanties

#### Solution Recommandée
```typescript
// ✅ SOLUTION: Initialiser correctement et détecter les changements
useEffect(() => {
  // Si le mode de sélection change, nettoyer l'état en cours
  if (isSelectingRef.current) {
    // Arrêter la sélection en cours
    isSelectingRef.current = false;
    selectionPointsRef.current = [];
    selectionRectRef.current = { x: 0, y: 0, width: 0, height: 0 };
    stopGlobalSelectionListeners();
    
    debugLog("[CanvasInteraction] Selection mode changed, clearing selection");
  }
  
  // Même pour drag/resize si nécessaire
  if (isDraggingRef.current || isResizingRef.current) {
    isDraggingRef.current = false;
    isResizingRef.current = false;
    dragStartRef.current = {};
    resizeHandleRef.current = null;
    
    debugLog("[CanvasInteraction] Interaction interrupted due to mode change");
  }
}, [selectionMode]); // Déclenché si selectionMode change

// Au démontage, nettoyer aussi
useEffect(() => {
  return () => {
    // Cleanup au démontage
    stopGlobalSelectionListeners();
    if (rafIdRef.current !== null) {
      cancelAnimationFrame(rafIdRef.current);
    }
    // ... autre cleanup
  };
}, [stopGlobalSelectionListeners]);
```

---

## 📊 Résumé des Problèmes par Fichier

| Fichier | Problèmes | Gravité |
|---------|-----------|---------|
| `useCanvasInteraction.ts` | 1-6, 8-9 | Critique |
| `useCanvasDrop.ts` | 7 | Important |
| `Canvas.tsx` | (Intégration des hooks) | Dépend des hooks |

---

## ✅ Checklist de Correction

- [ ] **P1:** Refactoriser global listeners avec capture phase et lastKnownStateRef uniquement
- [ ] **P2:** Supprimer selectedElementsRef, utiliser uniquement lastKnownStateRef.current.selection
- [ ] **P3:** Mémoriser zoom/pan au démarrage de sélection rectangle/lasso  
- [ ] **P4:** Réduire les dépendances useCallback à lastKnownStateRef + handler functions
- [ ] **P5:** Unifier throttling mousemove et RAF
- [ ] **P6:** Compléter handleMouseUp cleanup et ajouter useEffect cleanup
- [ ] **P7:** Ajouter fallback to calculateDropPosition et feedback utilisateur
- [ ] **P8:** Améliorer estimateImageMemorySize avec content-length réel
- [ ] **P9:** Ajouter useEffect pour détecter changement selectionMode et nettoyer l'état

