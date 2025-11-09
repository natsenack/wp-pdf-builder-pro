# SIMULATION COMPLÈTE DU SYSTÈME D'ÉDITION

## SCÉNARIO 1: Clic sur élément (PREMIÈRE SÉLECTION)

### État initial:
```
state.elements = [logo, text, rectangle, ...]  // 9 éléments
state.selection.selectedElements = []           // Rien sélectionné
state.canvas.zoom = 100
state.canvas.pan = {x: 0, y: 0}
```

### Actions de l'utilisateur:
```
User clicks at screen coordinates: (365, 95)
```

### Flux d'exécution:

1. **Canvas.onMouseDown** déclenché
   - event.clientX = 365, event.clientY = 95
   - rect.left = 637 (depuis getBoundingClientRect)
   - ❌ **BUG**: rect calculé MAINTENANT, pas au moment du click!

2. **handleMouseDown (useCanvasInteraction.ts:206)**
   - Calcul coords canvas:
     ```
     zoomScale = 100 / 100 = 1
     x = (365 - 637 - 0) / 1 = -272  ❌ NÉGATIF! WRONG!
     y = (95 - rectTop - 0) / 1 = ?
     ```
   - ❌ **BUG MAJEUR**: rect.left = 637 est ÉNORME! 
   - Cela veut dire le canvas est décalé à droite à 637px
   - Mais le click à 365 est AVANT le canvas!
   - Les coords seraient négatives = NO ELEMENTS FOUND

3. **Hit Detection (isPointInElement)**
   - Cherche un élément au coords (-272, ?)
   - Tous les éléments ont x >= 0, donc tous les tests échouent
   - clickedElement = null

4. **Résultat**: Nothing happens! User can't click anything!

---

## 🔴 PROBLÈME IDENTIFIÉ: Calcul des coordonnées du canvas

Le problème vient de comment on calcule la position du canvas.

### Code problématique (ligne 211):
```typescript
const rect = canvas.getBoundingClientRect();
const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
```

### Le problème:
- `getBoundingClientRect()` retourne la position RELATIVE à la viewport
- Si le canvas est à droite (637px), rect.left = 637
- event.clientX = 365 est AVANT le canvas
- Résultat: x = 365 - 637 = -272 (HORS DU CANVAS!)

### Solution attendue:
Si le canvas est vraiment à 637px, alors event.clientX devrait être >= 637.
Sinon, l'utilisateur clique HORS du canvas et on ne devrait rien faire.

**Test question**: Est-ce que le canvas est vraiment à 637px? Probablement PAS!
C'est peut-être la position du PARENT du canvas qui est 637px.

---

## SCÉNARIO 2: Calcul correct du offset pendant le drag

### Supposons qu'on a cliqué sur logo_element à (305, 0) et on le drag:

1. **handleMouseDown (premier drag)**
   - clickedElement.id = "element_3" (logo)
   - Element position: x=305, y=0
   - Mouse position: x=362, y=83
   - Offset calculation:
     ```
     offsetX = 362 - 305 = 57 ✅ CORRECT
     offsetY = 83 - 0 = 83 ✅ CORRECT
     dragStartRef = {x: 57, y: 83}
     ```

2. **handleMouseMove (drag en cours)**
   - currentMouseX = 380
   - currentMouseY = 85
   - Calcul nouvelle position:
     ```
     newX = currentMouseX - dragStartRef.x = 380 - 57 = 323 ✅ CORRECT
     newY = currentMouseY - dragStartRef.y = 85 - 83 = 2 ✅ CORRECT
     ```

3. **Clamping (garder dans les limites)**
   - canvasWidth = 794, canvasHeight = 1123
   - minVisibleWidth = min(50, element.width * 0.3) = 50 (pour logo 174×169)
   - X clamping:
     ```
     if (323 < 0) newX = 0  ❌ FALSE
     if (323 + 50 > 794) newX = 794 - 50 = 744  ❌ FALSE
     newX = 323 ✅ FINAL OK
     ```

4. **UPDATE_ELEMENT dispatch**
   - completeUpdates = {...element, x: 323, y: 2}
   - Toutes les props (src, logoUrl, etc) préservées ✅

---

## SCÉNARIO 3: Resize depuis handle SE (sud-est)

### État:
```
element = {x: 305, y: 0, width: 174, height: 169, ...}
```

### User drags SE handle:

1. **handleMouseDown (resize start)**
   - Détection handle à (305 + 174 - 4, 0 + 169 - 4) = (475, 165)
   - User clicks near: (475, 165)
   - ✅ Handle detected
   - isResizingRef = true
   - dragStartRef = {x: 475, y: 165}

2. **handleMouseMove (resize)**
   - currentMouseX = 500 (user drag right)
   - currentMouseY = 200 (user drag down)
   - calculateResize avec handle="se":
     ```
     updates.width = max(20, 500 - 305) = 195 ✅ GROW
     updates.height = max(20, 200 - 0) = 200 ✅ GROW
     ```

3. **completeUpdates assembly**
   ```
   resizeUpdates = {width: 195, height: 200}
   completeUpdates = {
     width: 195,
     height: 200,
     x: 305,           // Préservé
     y: 0,             // Préservé
     src: "https://...", // Préservé ✅
     logoUrl: "...",   // Préservé ✅
     ... toutes les autres props
   }
   ```

---

## ❌ BUGS IDENTIFIÉS

### BUG 1: selectedElementsRef vs state.selection sync timing
```typescript
// PROBLEM: Dans handleMouseDown on utilise selectedElementsRef
// MAIS il peut être stale si useEffect n'a pas run encore

// handleMouseDown:
const isAlreadySelected = selectedElementsRef.current.includes(clickedElement.id);

// MAIS handleMouseDown est callback avec dépendance [state, canvasRef, dispatch]
// Si state CHANGE, handleMouseDown est créé NOUVEAU avec NEW state
// Mais selectedElementsRef n'est pas synchronisé!

// Solution: Utiliser state.selection.selectedElements au lieu de ref
```

### BUG 2: Calcul des coordonnées incorrect
```typescript
// Line 211:
const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;

// PROBLEM: Pas clair si rect.left inclut pan ou pas
// PROBLEM: Ordre des opérations peut être incorrect

// Correct flow devrait être:
// 1. clientX est dans l'espace viewport
// 2. rect.left est position du canvas dans viewport
// 3. (clientX - rect.left) = position relative au canvas
// 4. Puis appliquer pan offset DANS L'ESPACE CANVAS
// 5. Puis diviser par zoom

// Current code:
const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
// This assumes pan is in viewport space, but it's in canvas space!

// CORRECT should be:
const x = ((event.clientX - rect.left) - state.canvas.pan.x) / zoomScale;
// Or better:
const canvasX = event.clientX - rect.left;  // In canvas pixel space
const x = (canvasX - state.canvas.pan.x) / zoomScale;  // Apply pan (in canvas space) then zoom
```

### BUG 3: Hit detection margin for lines
```typescript
// Current:
const hitboxMargin = Math.max(3, element.height * 1.5);

// PROBLEM: Pour une ligne de height=2px:
// hitboxMargin = max(3, 2*1.5) = max(3, 3) = 3px

// Cela crée une hitbox ÉNORME pour une ligne fine
// Une ligne à Y=155 avec height=2 a hitbox [154-161]
// Cela peut overlap avec d'autres éléments!

// SOLUTION: Plus petit margin pour les lignes
const hitboxMargin = Math.max(2, element.height * 0.5);  // max 2px
```

### BUG 4: isPointInElement margin calculation
```typescript
// Current (après mon fix):
let hitboxMargin = 0;
if (element.type === 'line') {
  hitboxMargin = Math.max(3, element.height * 1.5);
}

// PROBLEM: Les margins sont additionnés SUR TOUS LES CÔTÉS
// Pour une ligne hauteur 2, margin 3:
//   top = y - 3 = 155 - 3 = 152
//   bottom = y + height + margin = 155 + 2 + 3 = 160
// Hitbox = [152, 160] = 8px de hauteur!

// Pour une ligne dans une table qui fait 2px,
// et on veut sélectionner une autre ligne à Y=170,
// on peut accidentellement sélectionner la première!
```

### BUG 5: Canvas ref stale closure
```typescript
// handleMouseDown dépend de [state, canvasRef, dispatch]
// MAIS canvasRef ne change jamais
// DONC handleMouseDown est recréé à CHAQUE changement de state

// Cela veut dire que PENDANT un drag, si state change,
// handleMouseDown est remplacé par une NOUVELLE version
// Et les refs (isDraggingRef, dragStartRef) ne sont pas synchronisés!

// SCÉNARIO PROBLÉMATIQUE:
1. User click on element → isDraggingRef.current = true
2. State changes (selection updated) → handleMouseDown recreated
3. User mousemove → uses NEW handleMouseDown
4. MAIS isDraggingRef.current still = true from OLD handleMouseDown
5. Result: Drag continues but with NEW state closures!
```

### BUG 6: getResizeHandleAtPosition uses stale state
```typescript
// Line 269:
const resizeHandle = getResizeHandleAtPosition(x, y, state.selection.selectedElements, state.elements);

// getResizeHandleAtPosition parameters:
function getResizeHandleAtPosition(x: number, y: number, selectedIds: string[], elements: any[]) {
  const handleSize = 8;
  const selectedElements = elements.filter(el => selectedIds.includes(el.id));
  // ...
}

// PROBLEM: Si element position changed but state.elements not updated yet,
// the handle positions will be WRONG!

// Example:
// User is dragging element at (305, 0)
// During drag, element.x = 310 but state might still show 305
// So resize handles calculated from (305, 0) not (310, 0)
```

---

## SOLUTIONS

### FIX 1: Utiliser state directement au lieu de selectedElementsRef
```typescript
// BEFORE:
const isAlreadySelected = selectedElementsRef.current.includes(clickedElement.id);

// AFTER:
const isAlreadySelected = state.selection.selectedElements.includes(clickedElement.id);
```

### FIX 2: Clarifier et corriger le calcul des coordonnées
```typescript
// BEFORE:
const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;

// AFTER - Option A (if pan is in viewport space):
const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;

// AFTER - Option B (if pan is in canvas space):
const x = ((event.clientX - rect.left) - state.canvas.pan.x) / zoomScale;

// Needs verification which one is correct!
```

### FIX 3: Réduire margin pour hit detection des lignes
```typescript
// BEFORE:
const hitboxMargin = Math.max(3, element.height * 1.5);

// AFTER:
const hitboxMargin = element.type === 'line' 
  ? Math.max(1, Math.min(5, element.height * 0.5))  // 0.5-5px max
  : 0;
```

### FIX 4: Éviter recréation de handleMouseDown
```typescript
// BEFORE: deps = [state, canvasRef, dispatch]
// Recreated à chaque state change!

// AFTER: Utiliser useRef pour les callbacks
// et useEffect pour synchroniser l'état
```

---

## ÉTAPES SUIVANTES

1. ✅ Vérifier rect.left calculation - peut-être problème d'affichage du canvas
2. ✅ Tester le hit detection avec positions réelles
3. ✅ Vérifier si selectedElementsRef sync fonctionne  
4. ✅ Vérifier coordinate transforms avec zoom/pan
5. ✅ Tester drag avec propriété preservation
6. ✅ Tester resize avec handle detection
