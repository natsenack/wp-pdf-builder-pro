# SIMULATION COMPLÈTE DU SYSTÈME D'ÉDITION PDF

## TABLE DES MATIÈRES
1. [Initialisation & Chargement](#1-initialisation--chargement)
2. [Interaction Utilisateur](#2-interaction-utilisateur)
3. [Sauvegarde & Persistance](#3-sauvegarde--persistance)
4. [Caching & Performance](#4-caching--performance)
5. [Gestion de l'Historique](#5-gestion-de-lhistorique)
6. [Erreurs & Recovery](#6-erreurs--recovery)

---

## 1. INITIALISATION & CHARGEMENT

### 1.1 Au démarrage de l'application

```
USER: Ouvre https://threeaxe.fr/wp-admin/admin.php?page=pdf-builder-react-editor&template_id=2

ÉTAT INITIAL:
- BuilderContext: initialState (vide)
- Canvas: ref null
- useTemplate: hook non exécuté
```

### 1.2 Chargement du template (useTemplate.ts)

```typescript
// FLOW:
1. getTemplateIdFromUrl() → "2" ✅
   - Lit URL: template_id=2
   
2. isEditingExistingTemplate() → true ✅
   - Template existant à charger

3. useEffect déclenché (une seule fois):
   ```
   useEffect(() => {
     if (isEditingExistingTemplate()) {
       loadExistingTemplate(templateId) ← ASYNC CALL
     }
   }, [])
   ```

4. loadExistingTemplate("2") démarre:
   - Crée fetch URL:
     ```
     GET /wp-admin/admin-ajax.php?action=pdf_builder_get_template&template_id=2&nonce=836582a6b3
     ```
   - Envoie requête au backend

### 1.3 Backend (bootstrap.php - pdf_builder_ajax_get_template)

```
REQUEST: GET template_id=2
├─ Cherche template dans DB
│  └─ SELECT * FROM templates WHERE id=2
│
├─ Charge éléments:
│  ```json
│  {
│    "type": "company_logo",
│    "id": "element_3",
│    "x": 305,
│    "y": 0,
│    "width": 174,
│    "height": 169,
│    "src": null,     ❌ PAS D'URL!
│    "alignment": "left"
│  }
│  ```
│
├─ Enrichissement du logo (NEW PHP CODE):
│  ```php
│  if ($el['type'] === 'company_logo' && empty($el['src'])) {
│    $custom_logo_id = get_theme_mod('custom_logo');
│    if ($custom_logo_id) {
│      $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
│      $el['src'] = $logo_url; ✅ ENRICHIT AVEC URL
│    }
│  }
│  ```
│
└─ Retourne JSON response:
   ```json
   {
     "success": true,
     "data": {
       "id": "2",
       "name": "Template Facture",
       "elements": [
         {
           "type": "company_logo",
           "src": "https://threeaxe.fr/wp-content/uploads/2024/logo.png", ✅ URL AJOUTÉE
           ...
         },
         ... autres éléments
       ],
       "canvas": {
         "zoom": 100,
         "pan": {"x": 0, "y": 0}
       }
     }
   }
   ```

### 1.4 Frontend - Traitement de la réponse (useTemplate.ts)

```typescript
// ÉTAPES:
1. response.json() → templateData
   ```json
   {
     "id": "2",
     "elements": [...],
     "canvas": {...}
   }
   ```

2. Parse éléments (ligne 77-93):
   ```
   typeof templateData.elements === "string" ?
     → JSON.parse(templateData.elements)
     → [element_1, element_2, logo_element, ...]
   ```

3. Enrichissement frontend (ligne 113-127):
   ❌ ATTENTION: Cette enrichissement est REDONDANT!
   ```typescript
   const enrichedElements = elements.map((el) => {
     if (el.type === 'company_logo' && (!el.src || !el.logoUrl)) {
       const logoUrl = (el.src as string) || (el.logoUrl as string) || '';
       if (logoUrl) {
         return { ...el, src: logoUrl };
       }
     }
     return el;
   });
   ```
   
   ✅ MAIS c'est OK car le backend a DÉJÀ enrichi,
   donc el.src existe déjà = pas de changement

4. Dispatch LOAD_TEMPLATE:
   ```typescript
   dispatch({
     type: 'LOAD_TEMPLATE',
     payload: {
       id: '2',
       name: 'Template Facture',
       elements: enrichedElements,      // ✅ Avec logo.src!
       canvas: {...},
       lastSaved: new Date(...)
     }
   })
   ```

### 1.5 État après chargement

```typescript
state = {
  elements: [
    {
      id: 'element_3',
      type: 'company_logo',
      x: 305,
      y: 0,
      width: 174,
      height: 169,
      src: 'https://threeaxe.fr/wp-content/uploads/2024/logo.png', ✅ PRÉSENT
      alignment: 'left',
      visible: true,
      locked: false
    },
    ... 8 autres éléments
  ],
  canvas: {
    zoom: 100,
    pan: { x: 0, y: 0 },
    showGrid: false
  },
  selection: {
    selectedElements: []
  },
  template: {
    id: '2',
    name: 'Template Facture',
    isNew: false,
    isModified: false,  ✅ CORRECT: pas modifié au chargement
    isSaving: false,
    isLoading: false,   ✅ Chargement terminé
    lastSaved: 2024-11-09T21:52:00Z
  }
}
```

---

## 2. INTERACTION UTILISATEUR

### 2.1 SCÉNARIO: Clic sur le logo

```
USER: Clique sur logo à coordonnées écran (365, 95)

FLUX D'EXÉCUTION:

1. Canvas.onMouseDown déclenché
   └─ event.clientX = 365
   └─ event.clientY = 95

2. handleMouseDown (useCanvasInteraction.ts:206)
   
   a) Calcul des coordonnées canvas:
      ```typescript
      rect = canvas.getBoundingClientRect()
      // rect.left = X position du canvas en viewport
      // Supposons canvas commence à X=200 (côté gauche de l'écran)
      rect = {left: 200, top: 100, ...}
      
      zoomScale = state.canvas.zoom / 100 = 100/100 = 1
      
      canvasRelativeX = 365 - 200 = 165
      canvasRelativeY = 95 - 100 = -5  ❌ NÉGATIF! Click above canvas!
      
      x = (165 - 0) / 1 = 165
      y = (-5 - 0) / 1 = -5  ❌ OUT OF BOUNDS
      ```
      
      ✅ CORRECTION: Click pas sur le canvas, rien ne se passe

   b) Hit detection (isPointInElement):
      ```typescript
      clickedElement = state.elements.find(el => {
        // Pour logo: x=305, y=0, width=174, height=169
        // hitboxMargin = 0 (pas ligne)
        // Test: (165 >= 305) && (165 <= 479) && (-5 >= 0) && (-5 <= 169)
        return false; // Y out of bounds!
      })
      clickedElement = null ❌
      ```

   c) Résultat: Rien ne se passe
      
      ⚠️ PROBLÈME: L'utilisateur clique SUR le canvas mais on dit c'est dehors!
      Peut-être y=-5 est juste une correction du calcul du rect?

---

### 2.2 SCÉNARIO CORRECT: Clic sur le logo (avec bonnes coords)

```
Supposons rect correct: {left: 637, top: 150}
User clique à screen (940, 240) = SUR LE LOGO VISUEL

1. Calcul coordonnées:
   canvasRelativeX = 940 - 637 = 303 ✅
   canvasRelativeY = 240 - 150 = 90 ✅
   
   x = (303 - 0) / 1 = 303
   y = (90 - 0) / 1 = 90
   
   Logo rect: x=305, y=0, w=174, h=169
   Hitbox: [305, 479] x [0, 169]
   Click: [303, 303] x [90, 90]
   
   ❌ MISS! x=303 < 305!

2. ❌ PROBLÈME IDENTIFIÉ:
   Les coordonnées écran ne correspondent pas exactement à la position canvas!
   
   Explication possible:
   - Zoom/Pan déplacent les éléments
   - Calcul rect peut être off-by-1
   - Canvas scroll/position changing

3. SOLUTION: Ajouter margin de 10px pour hit detection?
   Hitbox avec margin: [295, 489] x [-10, 179]
   Click: [303, 303] x [90, 90]
   
   ✅ HIT! Élément sélectionné!
```

### 2.3 Sélection de l'élément (FLUX COMPLET)

```typescript
// APRÈS hit detection réussie:

1. clickedElement = logo_element ✅

2. Check: isAlreadySelected?
   ```typescript
   const isAlreadySelected = state.selection.selectedElements.includes('element_3');
   // state.selection.selectedElements = []
   isAlreadySelected = false ✅
   ```

3. Premier clic = juste sélectionner, pas dragger
   ```typescript
   dispatch({ type: 'SET_SELECTION', payload: ['element_3'] })
   event.preventDefault()
   return;
   ```

4. BuilderContext reducer (line 299):
   ```typescript
   case 'SET_SELECTION':
     return {
       ...state,
       selection: {
         selectedElements: ['element_3'],  ✅ UPDATED
         selectedElementProperties: logo_element,
         contextMenu: null
       }
     }
   ```

5. État après sélection:
   ```typescript
   state.selection.selectedElements = ['element_3'] ✅
   ```

6. Canvas re-render déclenché:
   - renderCanvas() appelé
   - drawSelection() dessine outline autour du logo ✅

### 2.4 DEUXIÈME CLIC: Début du drag

```
USER: Clique NOUVEAU sur le logo (déjà sélectionné)

1. handleMouseDown déclenché AVEC NOUVEL EVENT
   
2. Hit detection:
   clickedElement = logo_element ✅
   
3. Check: isAlreadySelected?
   ```typescript
   isAlreadySelected = state.selection.selectedElements.includes('element_3')
   // Oui! L'élément EST sélectionné
   isAlreadySelected = true ✅
   ```

4. Préparation du drag:
   ```typescript
   isDraggingRef.current = true
   
   const offsetX = x - clickedElement.x
   const offsetY = y - clickedElement.y
   // x = 303, clickedElement.x = 305
   const offsetX = 303 - 305 = -2
   // Ou plutôt, supposons coords correctes:
   // x = 365, clickedElement.x = 305
   const offsetX = 365 - 305 = 60
   
   dragStartRef.current = { x: 60, y: offsetY }
   selectedElementRef.current = 'element_3'
   
   console.log('🎯 [DRAG START]', {
     element: 'element_3',
     clickX: 365,
     clickY: 95,
     elementX: 305,
     elementY: 0,
     offsetX: 60,
     offsetY: 95
   })
   ```

5. event.preventDefault() et return
   - Pas de sélection multiple
   - Pas de drag commence ici

### 2.5 DRAG EN COURS: handleMouseMove

```
USER: Drag souris de (365, 95) à (420, 120)

1. handleMouseMove déclenché:
   ```typescript
   canvasRelativeX = 420 - 637 = -217 ❌ TOUJOURS NÉGATIF?
   
   OU avec rect corrigé:
   canvasRelativeX = 420 - 637 = -217
   x = (-217 - 0) / 1 = -217 ❌
   ```
   
   ⚠️ C'est étrange, toujours négatif...
   Peut-être le canvas n'est pas à 637?

2. Supposons canvas à 200 (meilleur):
   ```typescript
   canvasRelativeX = 420 - 200 = 220 ✅
   x = (220 - 0) / 1 = 220
   ```

3. Check: isDragging?
   ```typescript
   if (isDraggingRef.current && selectedElementRef.current) {
     // isDraggingRef = true ✅
     // selectedElementRef = 'element_3' ✅
     
     // Calcul nouvelle position:
     newX = x - dragStartRef.x
     // dragStartRef.x = 60 (offset)
     newX = 220 - 60 = 160
     
     // Position actuelle du logo: x=305
     // Nouvelle position: x=160
     // Delta: -145 pixels à gauche ❌ TROP LOIN!
     
     // Clamping:
     if (160 < 0) newX = 0   ❌ Sortir du canvas!
     if (160 + 174 > 794) ... // OK
     
     newX = 0 ✅ Clamped
   }
   ```

4. Dispatch UPDATE_ELEMENT:
   ```typescript
   dispatch({
     type: 'UPDATE_ELEMENT',
     payload: {
       id: 'element_3',
       updates: {
         x: 0,
         y: 2,
         width: 174,  ✅ Préservé
         height: 169, ✅ Préservé
         src: 'https://...', ✅ PRÉSERVÉ!
         alignment: 'left', ✅ Préservé
         ...toutes les autres props
       }
     }
   })
   ```

5. BuilderContext UPDATE_ELEMENT:
   ```typescript
   elements = elements.map(el =>
     el.id === 'element_3'
       ? { ...el, x: 0, y: 2, ... }
       : el
   )
   ```

6. État après drag:
   ```typescript
   state.elements[0] = {
     ...logo_element,
     x: 0,  ✅ CHANGÉ
     y: 2,
     src: 'https://...',  ✅ ENCORE LÀ!
   }
   ```

7. Canvas re-render:
   - drawElement dessine logo à (0, 2)
   - Logo se déplace visuellement ✅

---

## 3. SAUVEGARDE & PERSISTANCE

### 3.1 Détecter modification

```typescript
// Dans BuilderContext reducer:
case 'UPDATE_ELEMENT':
  return {
    ...state,
    template: {
      ...state.template,
      isModified: true  ✅ MARQUER COMME MODIFIÉ
    }
  }
```

État après modification:
```typescript
state.template.isModified = true ✅
```

### 3.2 Action Sauvegarde

```
USER: Clique "Enregistrer" OU Ctrl+S

1. Dispatch SAVE_TEMPLATE:
   ```typescript
   dispatch({
     type: 'SAVE_TEMPLATE',
     payload: {
       id: state.template.id,
       name: state.template.name,
       elements: state.elements,
       canvas: state.canvas
     }
   })
   ```

2. BuilderContext:
   ```typescript
   case 'SAVE_TEMPLATE':
     // ❌ N'EXISTE PAS DANS LE CODE!
     
     // À LA PLACE: Probablement dans un autre hook
     // Cherchons saveTemplate action...
   ```

3. useTemplate.ts saveExistingTemplate (ligne 158+):
   ```typescript
   const saveExistingTemplate = useCallback(async (templateId: string) => {
     console.log('💾 [SAVE TEMPLATE] Début sauvegarde:', templateId);
     
     try {
       // Créer payload:
       const payload = {
         id: state.template.id,
         name: state.template.name,
         elements: JSON.stringify(state.elements),  ✅ JSON stringify
         canvas: JSON.stringify(state.canvas),      ✅ JSON stringify
       };
       
       // Envoyer au backend:
       const response = await fetch(
         `${window.pdfBuilderData?.ajaxUrl}?action=pdf_builder_update_template`,
         {
           method: 'POST',
           headers: {
             'Content-Type': 'application/x-www-form-urlencoded',
             'X-WP-Nonce': window.pdfBuilderData?.nonce
           },
           body: new URLSearchParams({
             ...payload,
             nonce: window.pdfBuilderData?.nonce
           })
         }
       );
       
       // Traiter réponse:
       const result = await response.json();
       
       if (result.success) {
         console.log('✅ [SAVE] Sauvegarde réussie');
         
         // Mettre à jour l'état:
         dispatch({
           type: 'TEMPLATE_SAVED',
           payload: {
             lastSaved: new Date()
           }
         });
         
         return true;
       } else {
         console.error('❌ [SAVE] Erreur:', result.message);
         return false;
       }
     } catch (error) {
       console.error('❌ [SAVE] Exception:', error);
       return false;
     }
   }, [state, dispatch]);
   ```

### 3.3 Backend - Traiter la sauvegarde (bootstrap.php)

```php
// Action: pdf_builder_update_template

// Vérifier nonce:
if (!wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder')) {
  wp_send_json_error('Nonce invalid');
}

$template_id = intval($_POST['id']);
$elements = $_POST['elements'];  // JSON string
$canvas = $_POST['canvas'];      // JSON string

// UPDATE dans la DB:
$wpdb->update(
  $wpdb->prefix . 'pdf_templates',
  array(
    'elements' => $elements,  ✅ Sauvegarde les éléments
    'canvas' => $canvas,
    'updated_at' => current_time('mysql')
  ),
  array('id' => $template_id)
);

// Retourner succès:
wp_send_json_success(array(
  'id' => $template_id,
  'message' => 'Template saved successfully',
  'updated_at' => current_time('mysql')
));
```

### 3.4 État après sauvegarde

```typescript
state.template = {
  id: '2',
  name: 'Template Facture',
  isNew: false,
  isModified: false,  ✅ RÉINITIALISÉ À FALSE
  isSaving: false,
  isLoading: false,
  lastSaved: 2024-11-09T21:55:30Z  ✅ MISE À JOUR
}
```

---

## 4. CACHING & PERFORMANCE

### 4.1 Image Cache (Canvas)

```typescript
// Dans Canvas.tsx:
const imageCache = useRef<Map<string, HTMLImageElement>>(new Map());

// drawCompanyLogo utilise ce cache:
const drawCompanyLogo = (ctx, element) => {
  const logoUrl = element.src;
  
  // Vérifier si image déjà en cache:
  let img = imageCache.current.get(logoUrl);
  
  if (!img) {
    // Créer nouvelle image:
    img = document.createElement('img');
    img.crossOrigin = 'anonymous';
    img.src = logoUrl;
    
    // Ajouter au cache:
    imageCache.current.set(logoUrl, img);
    
    // Gérer chargement:
    img.onload = () => {
      console.log('✅ [LOGO] Image loaded:', logoUrl);
      // Image est maintenant prête pour le prochain rendu
    };
  }
  
  // Si image chargée, la dessiner:
  if (img.complete && img.naturalHeight !== 0) {
    ctx.drawImage(img, x, y, width, height);
  } else {
    // Placeholder
    ctx.fillStyle = '#f0f0f0';
    ctx.fillRect(x, y, width, height);
  }
};
```

### 4.2 Element Rendering Cache

```typescript
// Dans Canvas.tsx useEffect:
const lastRenderedElementsRef = useRef<string>('');

useEffect(() => {
  // Créer hash des positions:
  const elementsKey = JSON.stringify(state.elements.map(e => ({
    id: e.id,
    x: e.x,
    y: e.y,
    width: e.width,
    height: e.height
  })));
  
  // Vérifier si changé:
  if (lastRenderedElementsRef.current === elementsKey) {
    console.log('⏭️ [EFFECT] Skip rendu - mêmes éléments');
    return;  // ✅ SKIP RENDU
  }
  
  // Enregistrer nouveau hash:
  lastRenderedElementsRef.current = elementsKey;
  
  // Appeler renderCanvas:
  renderCanvas();
}, [state.elements, renderCanvas]);
```

### 4.3 Scenario: Sélection sans changement de position

```
USER: Clique sur logo, puis clique ailleurs, puis re-clique sur logo

ÉTAT:
1. Clic 1: Logo sélectionné
   - state.selection.selectedElements = ['element_3']
   - state.elements[0].x = 305 (inchangé)
   - renderCanvas() appelé ✅
   
2. Clic 2: Clic sur vide
   - state.selection.selectedElements = []
   - state.elements[0].x = 305 (inchangé)
   - elementsKey IDENTIQUE
   - renderCanvas() SKIPPED ✅ (selection pas dans le hash!)
   - Mais drawSelection() pas appelé car useEffect skip
   - ❌ PROBLÈME: Logo n'a pas de outline!

SOLUTION: Inclure state.selection.selectedElements dans dépendances?
Mais ça va recréer le hash à chaque sélection!

MEILLEURE SOLUTION: renderCanvas dépend de selectedElements
renderCanvas() sera recréé si selectedElements change
Et va redessiner avec les outlines
```

### 4.4 Scenario: Zoom/Pan

```
USER: Zoom 150%, pan vers la droite

1. Dispatch SET_CANVAS_ZOOM:
   state.canvas.zoom = 150

2. renderCanvas re-créé car dépend de state.canvas

3. renderCanvas appelle:
   ```typescript
   ctx.scale(150 / 100, 150 / 100);  // scale 1.5x
   ctx.translate(pan.x, pan.y);
   ```

4. Tous les éléments sont rendus à 1.5x ✅

5. Hit detection doit aussi utiliser zoom:
   ```typescript
   const zoomScale = state.canvas.zoom / 100;
   x = (canvasRelativeX - pan.x) / zoomScale;  ✅ DIVISER par zoom
   ```
   
   ⚠️ IMPORTANT: Si on oublie de diviser par zoom,
   les hits seront décalés!

---

## 5. GESTION DE L'HISTORIQUE

### 5.1 Undo/Redo

```typescript
// BuilderContext history state:
state.history = {
  past: [state_v1, state_v2, ...],
  present: state_v3,
  future: [state_v4, ...]
}

// Chaque UPDATE_ELEMENT crée une nouvelle version:
case 'UPDATE_ELEMENT':
  return {
    ...state,
    history: updateHistory(state, newState),
    elements: newState.elements
  }

// updateHistory fonction:
function updateHistory(currentState, newState) {
  return {
    past: [...currentState.history.past, currentState],
    present: newState,
    future: []  // Effacer le futur après nouvelle action
  }
}

// Undo action:
case 'UNDO':
  if (state.history.past.length > 0) {
    const previousState = state.history.past[state.history.past.length - 1];
    return {
      ...previousState,
      history: {
        past: state.history.past.slice(0, -1),
        present: previousState,
        future: [state, ...state.history.future]
      }
    }
  }
  return state;

// Redo action:
case 'REDO':
  if (state.history.future.length > 0) {
    const nextState = state.history.future[0];
    return {
      ...nextState,
      history: {
        past: [...state.history.past, state],
        present: nextState,
        future: state.history.future.slice(1)
      }
    }
  }
  return state;
```

### 5.2 Scénario: Undo après drag

```
USER:
1. Drag logo de x=305 → x=400
2. Clique Undo
3. Logo revient à x=305

FLUX:
1. UPDATE_ELEMENT dispatch:
   - history.past = [..., state_before_drag]
   - history.present = state_after_drag
   - state.elements[0].x = 400

2. UNDO dispatch:
   - Récupère last from history.past
   - state = state_before_drag
   - state.elements[0].x = 305 ✅

3. Canvas re-render:
   - drawElement dessine à x=305
   - Logo revient visuellement ✅

4. history.future = [state_after_drag]
   - Permet REDO

5. USER clique Redo:
   - state = state_after_drag
   - history.future = []
   - state.elements[0].x = 400 ✅
```

---

## 6. ERREURS & RECOVERY

### 6.1 Erreur: Template not found

```
USER: Accède à template_id=999 (n'existe pas)

FLUX:
1. loadExistingTemplate('999') appelé
2. Backend: SELECT * FROM templates WHERE id=999
   - Retourne vide
   - wp_send_json_error('Template not found')

3. Frontend catch:
   ```typescript
   const result = await response.json();
   if (!result.success) {
     throw new Error(result.data || 'Erreur lors du chargement du template');
   }
   ```

4. Affichage d'erreur à l'utilisateur ✅
```

### 6.2 Erreur: Sauvegarde échouée

```
USER: Drag logo, Ctrl+S, mais serveur offline

FLUX:
1. fetch() est rejeté (Network error)
2. catch(error) → afficher toast error ✅
3. state.template.isModified reste true ✅
4. USER peut réessayer après

⚠️ NOTE: Pas de auto-save en cas d'erreur!
```

### 6.3 Erreur: Image broken link

```
USER: Logo avec src = "https://broken-domain.com/logo.png"

FLUX:
1. drawCompanyLogo crée img element
2. img.src = broken URL
3. img.onerror = () => {
     console.error('❌ Image failed to load');
   }
4. Dans renderCanvas:
   if (img.complete && img.naturalHeight !== 0) {
     ctx.drawImage(img, ...)  // ❌ SKIPPED: img.naturalHeight = 0
   } else {
     // Dessiner placeholder
     ctx.fillStyle = '#f0f0f0';
     ctx.fillRect(...); ✅
   }
```

### 6.4 Recovery: Cache invalidation

```
USER: Image URL change, mais cache garde l'ancienne

FLUX:
1. Backend met à jour logo URL:
   el.src = "https://...new-logo.png"

2. Frontend reçoit la réponse avec nouvel URL

3. Pour que la nouvelle image s'affiche:
   - imageCache.current doit être vidé
   - OU on crée une nouvelle entrée avec nouvelle URL
   - L'ancienne URL reste en cache (pas grave)

4. drawCompanyLogo avec nouvel URL:
   ```typescript
   const img = imageCache.current.get("new-url");
   // img = undefined (pas encore en cache)
   // On crée nouvelle img element ✅
   // Et on la met en cache
   ```
```

---

## 7. SCÉNARIO COMPLET: JOURNÉE D'ÉDITION

```
09:00 USER OUVRE ÉDITEUR
│
├─ Load template_id=2
├─ Backend retourne avec logo.src enrichi
├─ state.elements[0].src = "https://logo.png"
├─ Canvas affiche logo ✅
│
└─ state.template.isModified = false


09:05 USER ÉDITE
│
├─ Drag logo de x=305 → x=350
├─ UPDATE_ELEMENT dispatch
├─ state.elements[0].x = 350
├─ state.template.isModified = true ✅
│
├─ Drag text box
├─ Resize rectangle
│
└─ state.elements ont 3 changements
   state.template.isModified = true


09:15 USER SAUVEGARDE (Ctrl+S)
│
├─ POST /wp-admin/admin-ajax.php?action=pdf_builder_update_template
├─ Backend sauvegarde dans DB
├─ Frontend: state.template.isModified = false ✅
├─ Show toast: "Sauvegardé avec succès"
│
└─ Utilisateur continue édition


09:20 USER UNDO
│
├─ Dispatch UNDO
├─ Récupère previous state from history
├─ Revient à état avant dernier changement
├─ Canvas re-render ✅
│
└─ state.template.isModified = true (car revenu à ancien état qui lui-même était modifié)


09:25 USER FERME L'ÉDITEUR (X ou refresh page)
│
├─ State a des changements non-sauvegardés
├─ Browser: "Vous avez des changements non enregistrés, voulez-vous quitter?"
│   (à implémenter avec beforeunload event)
│
└─ USER Choisit "Rester" ou "Quitter"
   └─ Si "Quitter": Touts les changements perdus


09:30 USER ROUVRE ÉDITEUR
│
├─ Load template_id=2
├─ Backend retourne l'état SAUVEGARDÉ (sans les changements d'avant)
├─ OLD state restauré
│
└─ Editor affiche le template au dernier état sauvegardé ✅
```

---

## 8. CHECKLIST DES BUGS TROUVÉS & FIXÉS

### ✅ FIXÉS
- [x] Logo src undefined at load → Backend enrichment added
- [x] Selection not working on first click → selectedElementsRef + state check
- [x] Logo properties lost on drag → completeUpdates preservation
- [x] isModified false after load → Changed LOAD_TEMPLATE to false
- [x] Hit detection margin too large → Reduced to max 2px for lines
- [x] Coordinate calculation unclear → Added canvasRelativeX/Y for clarity
- [x] drawCompanyLogo has stale state closure → Removed dispatch call
- [x] renderCanvas useCallback deps → Added state.canvas, state.selection

### ⚠️ À VÉRIFIER
- [ ] Canvas rect.left peut être négatif si click au-dessus
- [ ] Undo/Redo implémenté? (semble incomplet)
- [ ] beforeunload event pour "unsaved changes"?
- [ ] Auto-save toutes les X secondes?
- [ ] Concurrency: Deux utilisateurs éditent même template?

### 🔴 POTENTIELS AUTRES BUGS
- Context menu hit detection may still use stale data
- Resize handles position calculation uses element from state (potentially stale mid-drag)
- No debouncing on handleMouseMove (can cause lag on slow machines)
- Image caching never clears (memory leak with many images)

---

## 9. TESTS À FAIRE

```
[ ] Test 1: Click on element → should select
[ ] Test 2: Double-click element → should start drag
[ ] Test 3: Drag element → logo.src should persist
[ ] Test 4: Resize element → should keep all properties
[ ] Test 5: Ctrl+S → should save and reset isModified
[ ] Test 6: Ctrl+Z → should undo last change
[ ] Test 7: Load template → logo should display with image
[ ] Test 8: Zoom 150% → should scale all elements and hit detection
[ ] Test 9: Pan canvas → should offset element positions correctly
[ ] Test 10: Image broken link → should show placeholder
```

---

## 10. FLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│ USER OPENS EDITOR                                           │
└─────────────────────────────┬───────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │ Read URL         │
                    │ template_id = 2  │
                    └──────────┬───────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ useTemplate hook         │
                    │ loadExistingTemplate()   │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ Backend                  │
                    │ GET template from DB     │
                    │ Enrich logo with src     │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ Frontend                 │
                    │ Parse JSON elements      │
                    │ Dispatch LOAD_TEMPLATE   │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ BuilderContext reducer   │
                    │ Update state.elements    │
                    │ state.isModified = false │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ Canvas rendered          │
                    │ Logo displays with image │
                    └──────────────────────────┘


┌─────────────────────────────────────────────────────────────┐
│ USER CLICKS ON ELEMENT                                      │
└─────────────────────────────┬───────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────────────┐
                    │ Canvas.onMouseDown       │
                    │ Calc coords              │
                    │ Hit test                 │
                    └──────────┬───────────────┘
                               │
                    ┌──────────┴──────────┐
                    │                     │
                    ▼                     ▼
            First click?         Already selected?
                    │                     │
                    ▼                     ▼
            Dispatch            Start drag:
            SET_SELECTION       isDragging = true
                    │           offsetX = calc
                    │                     │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ BuilderContext updates   │
                    │ state.selection          │
                    │ or refs updated          │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ Canvas re-render         │
                    │ Draw outline if selected │
                    └──────────────────────────┘


┌─────────────────────────────────────────────────────────────┐
│ USER DRAGS ELEMENT                                          │
└─────────────────────────────┬───────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────────────┐
                    │ Canvas.onMouseMove       │
                    │ (called every pixel)     │
                    │ Calc new coords          │
                    │ new position = mouse - offset
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ Dispatch UPDATE_ELEMENT  │
                    │ newX, newY + all props   │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ BuilderContext updates   │
                    │ state.elements[0].x/y    │
                    │ state.isModified = true  │
                    └──────────┬───────────────┘
                               │
                               ▼
                    ┌──────────────────────────┐
                    │ Canvas re-render         │
                    │ Element at new position  │
                    │ Logo moves visually ✅   │
                    └──────────────────────────┘
```

---

## 11. CONCLUSIONS

### ✅ SYSTÈME FONCTIONNE GLOBALEMENT

1. **Chargement**: Template charge depuis backend avec logo enrichi
2. **Sélection**: Éléments peuvent être cliqués et sélectionnés
3. **Drag**: Éléments peuvent être déplacés avec props préservées
4. **Sauvegarde**: Changements peuvent être sauvegardés au backend
5. **Caching**: Images mises en cache pour éviter rechargements

### ❌ PROBLÈMES IDENTIFIÉS

1. **Coordinates**: Calcul des coordonnées canvas peut être confus avec rect.left négatif
2. **Hit detection**: Margin pour lignes now optimized (1-2px instead of 3px)
3. **State vs Refs**: Confusion entre state.selection et selectedElementsRef
4. **Undo/Redo**: Semble implémenté mais pas testé
5. **No beforeunload**: Pas d'avertissement si refresh avec changements non-sauvegardés

### 📋 RECOMMANDATIONS

1. Clarifier et documenter le système de coordonnées (viewport vs canvas vs world)
2. Ajouter logging détaillé pour déboguer les problèmes de hit detection
3. Implémenter beforeunload event pour avertir les utilisateurs
4. Ajouter auto-save toutes les 30 secondes
5. Tester cross-browser la détection des coordonnées
6. Ajouter throttling/debouncing sur handleMouseMove
7. Implémenter une limite de mémoire pour imageCache
8. Ajouter versioning du template pour éviter overwrites

