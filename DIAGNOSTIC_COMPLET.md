# 🔴 DIAGNOSTIC COMPLET DE L'ÉDITEUR REACT

## 📌 PROBLÈMES IDENTIFIÉS

### 1. ❌ SÉLECTION AU PREMIER CLIC NE FONCTIONNE PAS

**Localisation**: `useCanvasInteraction.ts` ligne 215-228

**Problème racine**:
- `handleMouseDown` vérifie `state.selection.selectedElements.includes(clickedElement.id)`
- Mais cette vérification utilise un `state` STALE (pas à jour)
- Quand on dispatch `SET_SELECTION`, le state ne change pas AVANT le rendu suivant
- Donc le deuxième condition (ligne 230) ne se déclenche pas immédiatement
- La sélection semble "fonctionner" mais elle est décalée d'un rendu

**Evidence**: Les logs montrent `APRÈS dispatch - état Redux devrait mettre à jour` mais rien n'indique que ça marche vraiment

**Solution**: Utiliser une `ref` pour tracker l'élément sélectionné au lieu de dépendre du `state`

---

### 2. ❌ LOGO PERD SON `src` AU DRAG

**Localisation**: `useCanvasInteraction.ts` ligne 399-413 (maintenant corrigé mais vérifier)

**Problème raccord**:
- Quand on faisait `updates: { x: newX, y: newY }`, ça ne passait pas `src`, `logoUrl`, etc.
- Le reducer faisait `{ ...el, ...updates }` donc les autres propriétés disparaissaient
- **FIXE APPLIQUÉ**: Maintenant on passe `completeUpdates` avec toutes les props

**Vérification**: Besoin de tester que ça marche

---

### 3. ❌ TEMPLATE LOAD MARQUE `isModified: true`

**Localisation**: `BuilderContext.tsx` ligne 445

**Problème**:
```typescript
template: {
  ...
  isModified: true,  // ❌ FAUX: Un template chargé de la BDD est CLEAN
  ...
}
```

**Solution**: Doit être `isModified: false` pour un template fraîchement chargé

---

### 4. ❌ COORDINATE TRANSFORMATION POSSIBLEMENT INCORRECTE

**Localisation**: `useCanvasInteraction.ts` ligne 203-205

```typescript
const zoomScale = state.canvas.zoom / 100;
const x = (event.clientX - rect.left - state.canvas.pan.x) / zoomScale;
const y = (event.clientY - rect.top - state.canvas.pan.y) / zoomScale;
```

**Problème potentiel**:
- Si pan est appliqué AVANT zoom dans le canvas, l'ordre est faux
- Doit vérifier comment le canvas transforme (scale puis translate vs translate puis scale)

**Vérification**: Chercher dans Canvas.tsx le code de transformation

---

### 5. ❌ isPointInElement HITBOX INCORRECTE POUR LIGNES

**Localisation**: `useCanvasInteraction.ts` ligne 173-182

```typescript
const hitboxMargin = element.type === 'line' ? 10 : 0;
```

**Problème**:
- Si la ligne a une hauteur de 2px et on ajoute 10px de marge, ça devient 22px de haut
- Mais la ligne draw peut être horizontale (height=2px, width=100px)
- La hitbox verticale sera gigantesque

**Solution**: Marge doit être adaptée à l'orientation de la ligne

---

### 6. ❌ RESIZE HANDLE DETECTION BASÉE SUR VIEILLE POSITION

**Localisation**: `useCanvasInteraction.ts` ligne 24-43

**Problème**:
- `getResizeHandleAtPosition` utilise `element.x`, `element.y` du `state.elements`
- Mais si on a fait un drag avant, ces positions sont stale (attendre le rendu)
- Donc les handles sont aux VIEILLES positions

**Solution**: Utiliser les positions en cours depuis `selectedElementRef` ou un state ref

---

### 7. ⚠️ MISSING `visible` ET `locked` FIELDS

**Localisation**: `types/elements.ts` et `BuilderContext.tsx`

**Problème**:
- `BaseElement` déclare `visible: boolean` et `locked: boolean` comme obligatoires
- Mais quand on crée des éléments, on n'initialise pas ces champs
- Peuvent être `undefined` et causer des bugs

**Evidence**:
- `createElementAtPosition` ne met pas `visible` ni `locked`
- LOAD_TEMPLATE ne les ajoute pas
- Canvas rendering peut crasher ou avoir du comportement étrange

---

### 8. ❌ ELEMENT TYPE SYSTEM TROP LOOSE

**Localisation**: `types/elements.ts` ligne 42

```typescript
export type Element = BaseElement;  // Pas assez spécifique
```

**Problème**:
- `Element` doit avoir des propriétés spécifiques selon le `type` (company_logo doit avoir `src`, text doit avoir `text`, etc.)
- Mais le type system ne le force pas
- Cause des bugs silencieux où les propriétés manquent

**Solution**: Créer un type union discriminé:
```typescript
export type Element = 
  | (BaseElement & { type: 'company_logo' } & ImageElementProperties)
  | (BaseElement & { type: 'text' } & TextElementProperties)
  | ...
```

---

### 9. ❌ REDUCER MUTATE DU STATE

**Localisation**: `BuilderContext.tsx` multiple places

**Problème**:
- `state.elements.map()` crée une nouvelle array mais si on modifie les objets c'est une shallow copy
- `clampElementPositions` crée une nouvelle array ✅ 
- `repairProductTableProperties` crée une nouvelle array ✅
- **MAIS**: Si une prop n'est pas un primitive, elle est référencée

**Impact**: Les mises à jour peuvent ne pas déclencher des re-renders (React compare par ref)

---

### 10. ❌ MISSING CLEANUP DANS useCanvasInteraction

**Localisation**: Le hook n'a pas de useEffect pour cleanup

**Problème**:
- Les refs `isDraggingRef`, `isResizingRef` ne sont jamais nettoyées
- Si l'utilisateur quitte de force, ça peut rester en état drag
- Pas de mouseup listener global

**Solution**: Ajouter un useEffect qui nettoie au unmount

---

### 11. ❌ Canvas.tsx PASSE DU STATE DANS handleMouseUp/DOWN

**Localisation**: `Canvas.tsx` ligne 2078

```typescript
onMouseDown={handleMouseDown}
```

**Problème**:
- Le handler dépend du state mais si state change avant que le handler se redéclenche, il utilise une vieille version
- **MITIGÉ**: React JSX binding re-crée la fonction à chaque rendu donc ça devrait marcher... mais ça dépend de `useCallback` dans le hook

---

### 12. ❌ IMAGEELEMENTPROPERTIES N'A PAS `visibility: boolean` et autres BaseElement fields

**Localisation**: `types/elements.ts` ligne 344-358

**Problème**:
- `ImageElementProperties` n'étend pas `BaseElement`
- Donc typiquement, les propriétés comme `src`, `logoUrl` ne sont pas garanties d'être avec un `id`, `type`, `x`, `y`
- Type system est cassé

**Solution**: Faire une proper union discriminée

---

## 🔧 FIXES À APPLIQUER (PAR ORDRE DE PRIORITÉ)

### P0 (CRITIQUE):
1. ✅ [DONE] Logo src loss on drag - preserve all properties in UPDATE_ELEMENT
2. ⏳ [TODO] Selection tracking with refs instead of state
3. ⏳ [TODO] Fix isModified flag in LOAD_TEMPLATE
4. ⏳ [TODO] Add missing `visible` and `locked` initialization

### P1 (IMPORTANT):
5. ⏳ [TODO] Fix coordinate transformation verification
6. ⏳ [TODO] Improve line hitbox detection
7. ⏳ [TODO] Fix resize handle position tracking

### P2 (NICE TO HAVE):
8. ⏳ [TODO] Proper discriminated union for Element type
9. ⏳ [TODO] Ensure shallow copy doesn't cause issues
10. ⏳ [TODO] Add cleanup to useCanvasInteraction
11. ⏳ [TODO] Fix ImageElementProperties typing

---

## 📊 IMPACT ANALYSIS

| Issue | Severity | Affects | Users See |
|-------|----------|---------|-----------|
| Selection stale | CRITICAL | Everything | Can't click first time |
| Logo src loss | CRITICAL | Logo element | Logo disappears on drag |
| isModified flag | HIGH | Save logic | False "modified" on load |
| Missing visible/locked | HIGH | Element behavior | Potential crashes |
| Coordinate transform | MEDIUM | Drag accuracy | Element offset on drag |
| Type system | MEDIUM | Developer exp | Silent bugs |
| Hitbox line | MEDIUM | Line selection | Lines hard to select |

