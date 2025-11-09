# 🔍 AUDIT DE SYNCHRONISATION - PROBLÈMES IDENTIFIÉS

## CRITIQUE 1: useEffect pour selectedElementsRef pas assez rapide

### Problème
```typescript
// useCanvasInteraction.ts ligne 158
useEffect(() => {
  selectedElementsRef.current = state.selection.selectedElements;
}, [state.selection.selectedElements]);
```

**Timing issue**:
1. User clique sur élément
2. handleMouseDown exécuté avec stale state (closure)
3. selectedElementsRef.current pas encore mis à jour
4. Hit test utilise ancienne valeur de selectedElementsRef

**Exemple**:
```
Frame 1: state.selection = ['A'] (redux update)
Frame 2: useEffect court (updateRef)
Frame 3: handleMouseDown utilise ref ✅

MAIS:
Si handleMouseDown appelé dans Frame 1.5 (avant Frame 2)
  → selectedElementsRef.current = [] (ancienne valeur)
  → Pas de détection du second clic!
```

### Solution
Remplacer complètement `selectedElementsRef` par `state.selection.selectedElements` dans les callbacks

---

## CRITIQUE 2: getResizeHandleAtPosition utilise stale elements

### Problème
```typescript
// useCanvasInteraction.ts ligne 27
const getResizeHandleAtPosition = (x: number, y: number, selectedIds: string[], elements: any[]) => {
  const selectedElements = elements.filter(el => selectedIds.includes(el.id));
  // ...
};

// Utilisé dans handleMouseDown:
const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);
```

**Timing issue**:
- Si handleMouseDown utilise `state` en closure
- Et state est stale au moment du clic
- Positions des éléments sont incorrectes
- Resize handles au mauvais endroit

### Solution
Passer les éléments et IDs sélectionnés directement au lieu de les laisser venir du closure stale

---

## CRITIQUE 3: completeUpdates perd properties intermittentes

### Problème
```typescript
// useCanvasInteraction.ts ligne 436
const completeUpdates = {
  x: newX,
  y: newY,
  ...Object.keys(element).reduce((acc, key) => {
    if (key !== 'x' && key !== 'y' && key !== 'updatedAt') {
      (acc as Record<string, unknown>)[key] = (element as Record<string, unknown>)[key];
    }
    return acc;
  }, {} as Record<string, unknown>)
};
```

**Problem**:
- Element peut avoir `src: undefined` au drag start
- Puis `src` ajouté pendant drag
- Ancien element object n'a pas la nouvelle src
- Properties perdues!

### Solution
Améliorer la logic pour préserver TOUTES les props même si undefined

---

## CRITIQUE 4: Canvas ref change + handleMouseDown re-crée

### Problème
```typescript
const { handleCanvasClick, handleMouseDown, handleMouseMove, handleMouseUp, handleContextMenu } = useCanvasInteraction({
  canvasRef
});
```

**Everytime canvasRef changes**, handleMouseDown est re-créé
- handleMouseDown dépend de [state, dispatch, canvasRef, ...]
- Chaque re-render change la ref
- handleMouseDown change
- Listener recreated
- Old closure lost

### Solution
Mémoriser canvasRef ou le passer via callback direct

---

## CRITIQUE 5: Pas de flush pour drag completion

### Problème
```typescript
// handleMouseMove dispatch UPDATE_ELEMENT
// Mais state pas flush immédiatement
// Next handleMouseMove utilise ancien state encore!
```

**Example**:
```
mousemove1: x=100, dispatch UPDATE_ELEMENT(x=100)
          state.elements[0].x = ??? (pas encore updaté!)
mousemove2: utilise state (ancien), calcule offset mal
          → Element jumps!
```

### Solution
Utiliser un `updatedStateRef` pour tracker l'état immédiat du drag

---

## CRITIQUE 6: imageCache pas synchronisé avec state.elements[].src

### Problème
```typescript
// Canvas.tsx drawCompanyLogo
const logoUrl = element.src;
let img = imageCache.current.get(logoUrl); // ✅ OK

// Mais aussi:
// Si element.src change pendant drag
// imageCache.current still has old image
// Canvas renders old image!
```

### Solution
Tracker dernière src rendering pour détecter changement

---

## CRITIQUE 7: Zoom/Pan pas appliqué lors du hit detection

### Problème
```typescript
// handleMouseDown
const canvasRelativeX = event.clientX - rect.left;
const x = (canvasRelativeX - state.canvas.pan.x) / zoomScale;

// MAIS: pan et zoom peuvent être wrong!
// Si user zoomed 150% + panned, transform incorrect
```

### Solution
Vérifier que pan.x, pan.y, zoom appliqués CORRECTEMENT à chaque update

---

## CRITIQUE 8: Selection state pas mis à jour avant drag-start

### Problème
```typescript
// handleMouseDown:
const isAlreadySelected = state.selection.selectedElements.includes(clickedElement.id);

if (!isAlreadySelected) {
  dispatch({ type: 'SET_SELECTION', payload: [clickedElement.id] });
  // ✅ Dispatch SET_SELECTION
  // ❌ BUT state.selection.selectedElements pas encore updaté!
  // handleMouseMove qui suit utilise ancien state!
}
```

**2 solutions**:
A) Wait for state update before starting drag
B) Track drag start immediately without waiting

### Solution Recommandée
Utiliser une variable locale pour "element to drag" plutôt que attendre state

---

## RÉSUMÉ DES BUGS

| Bug | Severité | Fichier | Ligne | Impact |
|-----|----------|---------|-------|--------|
| selectedElementsRef stale | 🔴 CRITIQUE | useCanvasInteraction.ts | 158 | Selection tracking broken |
| getResizeHandleAtPosition stale | 🔴 CRITIQUE | useCanvasInteraction.ts | 27 | Resize fails |
| completeUpdates loses props | 🔴 CRITIQUE | useCanvasInteraction.ts | 436 | Logo.src lost on drag |
| canvasRef re-creates handler | 🟡 HIGH | useCanvasInteraction.ts | callback | Listener churn |
| No flush for drag state | 🔴 CRITICAL | useCanvasInteraction.ts | mousemove | Element jumps |
| imageCache sync | 🟡 HIGH | Canvas.tsx | drawCompanyLogo | Old image shown |
| Zoom/pan transform | 🟡 HIGH | useCanvasInteraction.ts | 242 | Hit detection wrong |
| Selection not updated before drag | 🔴 CRITICAL | useCanvasInteraction.ts | handleMouseDown | Drag starts with wrong state |

---

## PRIORITÉ DES CORRECTIONS

1. **URGENT**: Fix selectedElementsRef + Selection before drag-start
2. **URGENT**: Fix completeUpdates property preservation
3. **HIGH**: Fix getResizeHandleAtPosition closure
4. **HIGH**: Fix imageCache sync
5. **MEDIUM**: Fix zoom/pan transform verification
6. **MEDIUM**: Fix canvasRef ref churn

