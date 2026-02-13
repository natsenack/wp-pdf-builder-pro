# Plan d'Action et Tests Recommandés

## 📋 Ordre de Correction Recommandé

### Phase 1: Correctif Critique (URGENT - 1-2 jours)
> Ces corrections doivent être appliquées en priorité car elles causent des bugs visibles et des fuites mémoire

#### 1. **Désynchronisation Ref/State** (P2 - 30 min)
- Supprimer complètement `selectedElementsRef`
- Utiliser uniquement `lastKnownStateRef.current.selection.selectedElements`
- Vérifier tous les usages de `selectedElementsRef` et remplacer

**Impact:** Élimine les incohérences de sélection et de drag

```bash
grep -r "selectedElementsRef" src/js/react/hooks/
# À remplacer par: lastKnownStateRef.current.selection.selectedElements
```

#### 2. **Nettoyage des Refs** (P6 - 45 min)
- Implémenter le `handleMouseUp` complet avec toutes les réinitialisations
- Ajouter le cleanup au démontage du composant

**Impact:** Élimine les comportements fantômes après drag/drop

#### 3. **Fuites d'Event Listeners** (P1 - 1h)
- Refactoriser `startGlobalSelectionListeners`/`stopGlobalSelectionListeners`
- Utiliser `capture: true` et `lastKnownStateRef` uniquement
- Ajouter cleanup dans useEffect

**Impact:** Élimine les memory leaks et les listeners zombies

---

### Phase 2: Correctif Important (1-2 jours)
> Application lors du prochain sprint

#### 4. **Calculs de Coordonnées** (P3 - 1h)
- Ajouter `selectionStartZoomRef` et `selectionStartPanRef`
- Mémoriser les paramètres au démarrage de la sélection rectangle/lasso
- Utiliser les paramètres mémorisés dans handleMouseMove

**Impact:** Sélection rectangle/lasso exacte même pendant pan/zoom

```bash
# Check selectionMode utilization
grep -n "selectionMode ===" src/js/react/hooks/useCanvasInteraction.ts
```

#### 5. **Error Handling du Drop** (P7 - 1h)
- Ajouter fallback positions dans `calculateDropPosition`
- Ajouter feedback utilisateur avec notifications
- Valider gracieusement sans lancer d'Errors

**Impact:** Meilleure UX et debugging

#### 6. **Dépendances useCallback** (P4 - 1h30)
- Réduire les dépendances en utilisant `lastKnownStateRef`
- Ajouter les canvasSettings nécessaires
- Vérifier les closures stales

**Impact:** Performance et stability améliorées

---

### Phase 3: Optimisations (After Testing)
> Application si les tests passent avec Phase 1-2

#### 7. **Throttling/RAF** (P5 - 1h)
- Unifier à `16ms` (60 FPS)
- Utiliser un seul système
- Supprimer les double throttles

**Impact:** Drag/resize plus fluide

#### 8. **Cache d'Images** (P8 - 30 min)
- Utiliser `content-length` réel au lieu d'estimation
- Améliorer la fonction de cleanup

**Impact:** Gestion mémoire plus fiable

#### 9. **Initialisation State** (P9 - 30 min)
- Ajouter useEffect pour détecter changement selectionMode
- Nettoyer l'état en cours si le mode change

**Impact:** Comportement cohérent au démarrage

---

## 🧪 Plan de Tests Recommandé

### Test 1: Sélection Rectangle
**Objectif:** Vérifier que la sélection rectangle fonctionne correctement avec zoom/pan

```typescript
// Test Case
1. Créer 5 éléments en grille (100x100 chacun)
2. Zoomer à 200% du canvas
3. Pan le canvas de 50px à droite et 50px en bas
4. Effectuer une sélection rectangle qui doit inclure 2 éléments
5. Vérifier que exactement 2 éléments sont sélectionnés

Expected: Les 2 éléments corrects sont sélectionnés
Problème avant: Sélection incorrecte ou éléments fantômes sélectionnés
```

**Test Script:**
```javascript
// Dans la console du navigateur
const testRectSelection = async () => {
  // 1. Créer 5 éléments
  const elements = [];
  for (let i = 0; i < 5; i++) {
    dispatch({
      type: "ADD_ELEMENT",
      payload: {
        id: `test_rect_${i}`,
        type: "rectangle",
        x: (i % 3) * 120,
        y: Math.floor(i / 3) * 120,
        width: 100,
        height: 100,
        fillColor: "#" + Math.floor(Math.random()*16777215).toString(16),
      }
    });
  }
  
  // 2. Simuler sélection rectangle
  // ... code de simulation de mousemove dans la région
  
  // 3. Vérifier la sélection
  console.assert(
    state.selection.selectedElements.length === 2,
    "Expected 2 elements selected, got: " + state.selection.selectedElements.length
  );
};
```

---

### Test 2: Sélection Lasso
**Objectif:** Vérifier que le lasso ne crée pas de listeners orphelins

```typescript
// Test Case
1. Activer mode lasso
2. Effectuer 10 sélections lasso rapides (sans attendre la fin de la précédente)
3. Vérifier que le nombre de listeners globaux reste constant (2 max)
4. Vérifier qu'aucune memory leak n'apparaît

Expected: Pas de croissance mémoire
Problème avant: Memory usage augmente après chaque sélection
```

**Monitor:**
```javascript
// Vérifier les listeners
const getEventListenerCount = () => {
  // Chrome DevTools: Performance > Event Listeners
  // Ou vérifier directement:
  console.log("Global move listeners:", document._getEventListeners?.("mousemove")?.length ?? "N/A");
  console.log("Global up listeners:", document._getEventListeners?.("mouseup")?.length ?? "N/A");
};
```

---

### Test 3: Drag Multiple Éléments
**Objectif:** Vérifier que le drag multiple ne perd pas d'éléments

```typescript
// Test Case
1. Créer 5 éléments sélectionnables
2. Sélectionner les 5 avec Ctrl+Click
3. Draguer les 5 en même temps
4. Vérifier que les 5 bougent avec les bonnes positions relatives

Expected: Les 5 éléments bougent de manière cohérente
Problème avant: Certains éléments ne bougent pas ou positions incorrectes
```

**Assertion:**
```javascript
const testDragMultiple = () => {
  // Enregistrer les positions avant drag
  const positionsBefore = state.selection.selectedElements.map(id => {
    const el = state.elements.find(e => e.id === id);
    return { id, x: el.x, y: el.y };
  });
  
  // Simuler drag de 50px à droite et 30px vers le bas
  // ...
  
  // Vérifier que le déplacement est cohérent
  const expectedDelta = { x: 50, y: 30 };
  state.selection.selectedElements.forEach(id => {
    const before = positionsBefore.find(p => p.id === id);
    const after = state.elements.find(e => e.id === id);
    
    console.assert(
      after.x === before.x + expectedDelta.x &&
      after.y === before.y + expectedDelta.y,
      `Element ${id}: expected delta (${expectedDelta.x}, ${expectedDelta.y}), got (${after.x - before.x}, ${after.y - before.y})`
    );
  });
};
```

---

### Test 4: Drag & Drop
**Objectif:** Vérifier que le drag/drop ne place pas les éléments hors du canvas

```typescript
// Test Case
1. Pré-calculer les cas limites:
   - Drop à (0, 0) -> élément placé à (0, 0)
   - Drop à (canvas.width, canvas.height) -> élément placé au maximum valide
   - Drop avec zoom 50% -> position calculée correctement
   - Drop avec pan -> position calculée correctement

Expected: Tous les éléments dans les limites du canvas
Problème avant: Éléments peuvent être partiellement ou complètement hors du canvas
```

---

### Test 5: Memory & Performance
**Objectif:** Vérifier qu'il n'y a pas de memory leaks ou performance issues

```typescript
// Test Case - Memory
1. Charger un large canvas avec 50+ éléments et 20+ images
2. Effectuer 100 drag/drop d'éléments
3. Vérifier que la mémoire ne croît pas exponentiellement

Expected: Mémoire stable après les opérations
Problème avant: Croissance linéaire ou exponentielle
```

**Monitoring Script:**
```javascript
const memoryTest = async () => {
  if (!performance.memory) {
    console.warn("performance.memory not available in this browser");
    return;
  }
  
  const initialMemory = performance.memory.usedJSHeapSize / (1024 * 1024);
  console.log(`Initial memory: ${initialMemory.toFixed(2)} MB`);
  
  // Effectuer 100 opérations
  for (let i = 0; i < 100; i++) {
    // Simuler drag/drop
    // ... code ...
    
    if (i % 20 === 0 && i > 0) {
      const currentMemory = performance.memory.usedJSHeapSize / (1024 * 1024);
      const delta = currentMemory - initialMemory;
      console.log(
        `After ${i} ops: ${currentMemory.toFixed(2)} MB (delta: ${delta.toFixed(2)} MB)`
      );
      
      // Trigger garbage collection if available
      if (window.gc) {
        window.gc();
        const afterGC = performance.memory.usedJSHeapSize / (1024 * 1024);
        console.log(`  After GC: ${afterGC.toFixed(2)} MB`);
      }
    }
  }
  
  const finalMemory = performance.memory.usedJSHeapSize / (1024 * 1024);
  const totalDelta = finalMemory - initialMemory;
  console.log(
    `\nFinal memory: ${finalMemory.toFixed(2)} MB (total delta: ${totalDelta.toFixed(2)} MB)`
  );
  console.warn(
    totalDelta > 10 ? "⚠️ MEMORY LEAK DETECTED" : "✅ Memory usage acceptable"
  );
};

// Run: memoryTest()
```

---

### Test 6: Nettoyage Après Départ Rapide
**Objectif:** Vérifier que les refs sont bien nettoyées même en cas de départ rapide

```typescript
// Test Case
1. Commencer un drag
2. Immédiatement cancel (Escape key) avant le mouseup
3. Commencer un nouveau drag
4. Vérifier que le nouveau drag utilise les bonnes positions de départ

Expected: Nouveau drag commence correctement
Problème avant: Le nouveau drag utilise les positions du drag précédent
```

---

## 📊 Checklist de Validation Post-Correction

### Pour chaque correction:
- [ ] Code reviewer a approuvé
- [ ] Tests unitaires passent
- [ ] Tests d'intégration passent (Test 1-6 ci-dessus)
- [ ] Pas de console warnings ou errors
- [ ] Memory profile stable (DevTools Performance)
- [ ] Chrome DevTools montre 0 listeners "orphans"

### Avant merge:
- [ ] Tous les tests passent en local
- [ ] CI/CD pipeline passe
- [ ] Performance profiling OK
- [ ] Pas de regressions en staging
- [ ] Documentation mise à jour

---

## 🐛 Debugging Tips

### Detect Memory Leaks
```javascript
// Chrome DevTools Console
// Vérifier les listeners orphelins
console.log(getEventListeners(document).mousemove?.length ?? 0);
console.log(getEventListeners(document).mouseup?.length ?? 0);

// Vérifier la dernière sélection rectangle
console.log(lastState.selection.selectedElements);
console.log(selectionRectRef.current); // Should be 0,0,0,0 after mouseup
```

### Debug Canvas Coordinates
```javascript
// Override handleMouseDown pour logger tout
const originalHandleMouseDown = handleMouseDown;
window.handleMouseDown = (event) => {
  const rect = canvasRef.current.getBoundingClientRect();
  const zoomScale = state.canvas.zoom / 100;
  console.log({
    clientX: event.clientX,
    clientY: event.clientY,
    rect: { left: rect.left, top: rect.top, width: rect.width, height: rect.height },
    zoomScale,
    pan: state.canvas.pan,
    calculatedX: (event.clientX - rect.left - state.canvas.pan.x) / zoomScale,
    calculatedY: (event.clientY - rect.top - state.canvas.pan.y) / zoomScale,
  });
  return originalHandleMouseDown(event);
};
```

### Monitor RAF Usage
```javascript
let rafCount = 0;
const originalRAF = window.requestAnimationFrame;
window.requestAnimationFrame = (callback) => {
  rafCount++;
  console.log(`RAF #${rafCount}`);
  if (rafCount > 100) {
    console.warn("⚠️ Too many RAF calls - possible loop!");
  }
  return originalRAF(callback);
};
```

---

## 📝 Commit Message Template

```
fix(canvas): [ISSUE_NUMBER] Correct [PROBLEM_NAME]

## Problem
- [Describe the issue]
- [Impact on users]
- [Root cause]

## Solution
- [What was changed]
- [How it solves the problem]

## Testing
- [Test cases executed]
- [Expected vs Actual results]

## Performance Impact
- Memory: [Before/After]
- CPU: [Before/After]
- Listeners: [Before/After]

Fixes: #[ISSUE_NUMBER]
```

Example:
```
fix(canvas): Fix global event listener leaks in selection

## Problem
- startGlobalSelectionListeners creates new event listeners
  but old callbacks remain active if dependencies change
- Causes memory leaks and stale state closures
- Visible as duplicate selections and slowdowns

## Solution
- Removed all dependencies except lastKnownStateRef
- Use event capture phase for guaranteed cleanup
- Added cleanup in component unmount

## Testing
- Test 2: Lasso selection (10 rapid selections)
- Monitor: DocumentObject.addEventListener count stays at 0-2
- Memory: No growth detected over 100 operations

## Performance Impact
- Memory: -2.4 MB avg heap size after GC
- Listeners: Fixed (was 20+, now max 2)
- CPU: <1% impact
```

---

## 📞 Support & Questions

Pour chaque problème:
1. **Investigation:** Exécuter le test correspondant
2. **Reproduction:** Créer un minimal test case
3. **Logging:** Activer `debugMode` dans canvas settings
4. **DevTools:** Utiliser Memory profiler et Event Listeners inspector

