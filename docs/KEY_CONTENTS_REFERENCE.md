# 🔑 CONTENUS CLÉS DES FICHIERS IMPORTANTS - V1 → V2

**Date:** 15 janvier 2026  
**Document:** Contenus clés pour référence rapide

---

## 📌 TYPES PRINCIPAUX (types/elements.ts)

### BuilderState (État global complet)
```typescript
interface BuilderState {
  elements: Element[];              // Tous les éléments du canvas
  canvas: CanvasState;              // Paramètres canvas (zoom, pan, etc.)
  selection: SelectionState;        // Éléments sélectionnés
  drag: DragState;                  // État drag & drop
  mode: BuilderMode;                // Mode actuel: 'select', 'rectangle', etc.
  history: HistoryState;            // Historique undo/redo
  template: TemplateState;          // Infos template
  previewMode: 'editor' | 'command';// Mode édition ou commande
  orderId?: string;                 // ID commande si mode command
}
```

### BuilderMode (Modes disponibles)
```typescript
type BuilderMode = 
  | 'select'     // Sélection/déplacement éléments
  | 'rectangle'  // Créer rectangles
  | 'circle'     // Créer cercles
  | 'text'       // Créer texte
  | 'image'      // Ajouter images
  | 'line'       // Dessiner lignes
  | 'pan'        // Naviguer canvas
  | 'zoom';      // Zoomer
```

### BuilderAction (20+ actions dispatch)
```typescript
type BuilderAction =
  | { type: 'ADD_ELEMENT'; payload: Element }
  | { type: 'UPDATE_ELEMENT'; payload: { id: string; updates: Partial<Element> } }
  | { type: 'REMOVE_ELEMENT'; payload: string }
  | { type: 'SET_ELEMENTS'; payload: Element[] }
  | { type: 'SET_SELECTION'; payload: string[] }
  | { type: 'UNDO' }
  | { type: 'REDO' }
  | { type: 'SAVE_TEMPLATE'; payload?: { id?: string; name?: string } }
  | ... (+ 12 autres)
```

---

## 🏗️ CONTEXT PROVIDERS

### BuilderProvider
```tsx
import { BuilderProvider } from './contexts/builder/BuilderContext';
import { CanvasSettingsProvider } from './contexts/CanvasSettingsContext';

// Usage dans PDFBuilder.tsx
<CanvasSettingsProvider>
  <BuilderProvider>
    <PDFBuilderContent />
  </BuilderProvider>
</CanvasSettingsProvider>
```

### useBuilder() Hook
```typescript
const {
  state,                    // BuilderState complet
  dispatch,                 // Dispatch actions
  addElement,               // Ajouter élément
  updateElement,            // Modifier élément
  removeElement,            // Supprimer élément
  setSelection,             // Sélectionner éléments
  setMode,                  // Changer mode
  undo, redo, reset,        // Historique
  toggleGrid,               // Montrer/cacher grille
  toggleGuides,             // Montrer/cacher guides
  setCanvas                 // Modifier paramètres canvas
} = useBuilder();
```

### useCanvasSettings() Hook
```typescript
const {
  canvasWidth, canvasHeight,           // Dimensions
  gridShow, gridSize, gridSnapEnabled, // Grille
  guidesEnabled,                       // Guides
  zoomDefault, zoomMin, zoomMax,       // Zoom
  exportQuality, exportFormat,         // Export
  // ... + 40 autres propriétés
} = useCanvasSettings();
```

---

## 🎯 ÉLÉMENTS WOOCOMMERCE INCLUS

### 1. Product Table (Tableau Produits)
```typescript
{
  type: 'product_table',
  defaultProps: {
    x: 50, y: 50,
    width: 500, height: 200,
    showHeaders: true,
    showBorders: true,
    showAlternatingRows: true,
    showSku: false,
    showQuantity: true,
    fontSize: 11,
    backgroundColor: '#ffffff',
    headerBackgroundColor: '#f9fafb',
    textColor: '#374151'
    // ... + 20 autres propriétés
  }
}
```

### 2. Customer Info (Fiche Client)
```typescript
{
  type: 'customer_info',
  defaultProps: {
    x: 50, y: 220,
    width: 250, height: 120,
    showFullName: true,
    showAddress: true,
    showEmail: true,
    showPhone: true,
    layout: 'vertical',
    backgroundColor: '#e5e7eb'
    // ... + 15 autres
  }
}
```

### 3. Company Info (Infos Entreprise)
```typescript
{
  type: 'company_info',
  defaultProps: {
    showCompanyName: true,
    showAddress: true,
    showSiret: true,
    showVat: true,
    // ... + 18 autres
  }
}
```

### 4. Company Logo (Logo Entreprise)
```typescript
{
  type: 'company_logo',
  defaultProps: {
    x: 350, y: 50,
    width: 150, height: 80,
    fit: 'contain',
    objectFit: 'contain',
    src: '', // URL à définir
  }
}
```

### 5-10. Autres éléments
- order-number (Numéro commande)
- woocommerce-order-date (Date commande)
- woocommerce-invoice-number (Numéro facture)
- document_type (Type de document)
- dynamic-text (Texte dynamique)
- mentions (Mentions légales)

---

## 🎨 COMPOSANTS CLÉS

### PDFBuilderContent (Layout principal)
```tsx
export const PDFBuilderContent = () => {
  return (
    <div className="pdf-builder">
      {/* Header en haut */}
      <Header {...headerProps} />
      
      {/* Toolbar sous le header */}
      <Toolbar />
      
      {/* Contenu principal */}
      <div style={{ display: 'flex', gap: '0' }}>
        {/* Sidebar éléments */}
        <ElementLibrary />
        
        {/* Zone centrale avec canvas */}
        <div style={{ flex: 1 }}>
          <Canvas width={width} height={height} />
          {/* Bouton toggle properties panel */}
        </div>
        
        {/* Panneau propriétés à droite */}
        {isPropertiesPanelOpen && <PropertiesPanel />}
      </div>
    </div>
  );
};
```

### Canvas (Rendu HTML5)
```tsx
export function Canvas({ width, height }) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state } = useBuilder();
  
  useEffect(() => {
    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    
    // Dessiner tous les éléments
    state.elements.forEach(element => {
      ctx.save();
      ctx.translate(element.x, element.y);
      ctx.rotate(element.rotation || 0);
      
      // Appeler fonction de dessin selon type
      if (element.type === 'rectangle') drawRectangle(ctx, element);
      else if (element.type === 'text') drawText(ctx, element);
      else if (element.type === 'product_table') drawProductTable(ctx, element);
      // ... etc
      
      ctx.restore();
    });
  }, [state.elements]);
  
  return <canvas ref={canvasRef} width={width} height={height} />;
}
```

---

## 🔄 FLUX DE SAUVEGARDE

### Workflow Sauvegarde complète
```
Header.tsx (bouton Enregistrer)
  ↓
onSave() → useTemplate hook
  ↓
saveTemplate() function
  ↓
normalizeElementsBeforeSave()
  ↓
Envoi AJAX à WordPress
  ↓
wp_ajax action: pdf_builder_save_template
  ↓
Plugin sauvegarde dans DB
  ↓
Réponse success
  ↓
dispatch({ type: 'SAVE_TEMPLATE' })
  ↓
Mise à jour state (isModified = false)
  ↓
Notification success affichée
```

### Workflow Chargement template
```
useTemplate hook (au montage)
  ↓
getTemplateIdFromUrl() → récupère template ID
  ↓
loadExistingTemplate(id)
  ↓
Priorité 1: Utiliser données localisées window.pdfBuilderData
  Ou Priorité 2: Envoi AJAX si nécessaire
  ↓
normalizeElementsAfterLoad()
  ↓
dispatch({ type: 'LOAD_TEMPLATE', payload: { ... } })
  ↓
state.elements peuplé avec éléments chargés
  ↓
Canvas re-render avec éléments
  ↓
dispatch({ type: 'SET_TEMPLATE_LOADING', payload: false })
```

---

## 🪝 HOOKS IMPORTANTS

### useTemplate.ts (648 lignes)
```typescript
export function useTemplate() {
  // Retourne:
  return {
    // Infos template
    templateName,
    templateDescription,
    canvasWidth,
    canvasHeight,
    marginTop,
    marginBottom,
    showGuides,
    snapToGrid,
    
    // États
    isNewTemplate,
    isModified,
    isSaving,
    isLoading,
    isEditingExistingTemplate,
    
    // Fonctions
    saveTemplate,      // Sauvegarde
    previewTemplate,   // Aperçu
    newTemplate,       // Créer nouveau
    updateTemplateSettings
  };
}
```

### useResponsive.ts
```typescript
const isMobile = useIsMobile();   // < 480px
const isTablet = useIsTablet();   // 480px - 1024px
const isDesktop = !isMobile && !isTablet; // > 1024px
```

### useKeyboardShortcuts.ts
```typescript
// Raccourcis automatiquement mappés:
// Delete/Backspace → Supprimer éléments sélectionnés
// Ctrl+S → Sauvegarder
// Ctrl+Z → Undo
// Ctrl+Y → Redo
// Escape → Déselectionner
// Arrow keys → Déplacer éléments
// ... etc
```

---

## 📡 INTÉGRATION WORDPRESS

### Données localisées (wp_localize_script)
```javascript
// Dans PHP (WordPress)
wp_localize_script('pdf-builder-pro-react', 'pdfBuilderData', [
    'nonce' => wp_create_nonce('pdf_builder_nonce'),
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'templateId' => isset($template_id) ? $template_id : null,
    'existingTemplate' => $existing_template_data,
    'hasExistingData' => !empty($existing_template_data)
]);
```

### Accès depuis React
```typescript
// Partout dans l'app
const nonce = window.pdfBuilderData?.nonce;
const ajaxUrl = window.pdfBuilderData?.ajaxUrl;
const templateId = window.pdfBuilderData?.templateId;
const templateData = window.pdfBuilderData?.existingTemplate;

// Pour requêtes AJAX
fetch(window.pdfBuilderData.ajaxUrl, {
  method: 'POST',
  body: new URLSearchParams({
    action: 'pdf_builder_save_template',
    nonce: window.pdfBuilderData.nonce,
    template_data: JSON.stringify(templateData)
  })
});
```

---

## 🎨 STYLES PRINCIPAUX

### PDF Builder Container
```css
.pdf-builder {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  background: #ffffff;
}
```

### Toolbar
```css
.pdf-builder-toolbar {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #ffffff;
  border-bottom: 1px solid #e1e5e9;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
```

### Canvas Container
```css
.pdf-canvas-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8f8f8;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  overflow: auto;
  position: relative;
}
```

### Properties Panel
```css
.pdf-builder-properties {
  width: 430px;
  max-height: calc(100vh - 32px);
  overflow-y: auto;
  padding: 12px;
  background: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 4px;
}
```

---

## 🚀 ÉVÉNEMENTS PERSONNALISÉS

### Dispatch dans BuilderContext
```typescript
// Ajouter élément
dispatch({
  type: 'ADD_ELEMENT',
  payload: {
    id: 'elem-' + Date.now(),
    type: 'rectangle',
    x: 50,
    y: 50,
    width: 100,
    height: 100,
    visible: true,
    locked: false,
    createdAt: new Date(),
    updatedAt: new Date()
  }
});

// Mettre à jour élément
dispatch({
  type: 'UPDATE_ELEMENT',
  payload: {
    id: 'elem-123',
    updates: {
      x: 100,
      y: 150,
      fillColor: '#ff0000'
    }
  }
});

// Sélectionner éléments
dispatch({
  type: 'SET_SELECTION',
  payload: ['elem-1', 'elem-2', 'elem-3']
});
```

---

## 📊 CONST PRINCIPALES

### Canvas Dimensions (constants/canvas.ts)
```typescript
export const DEFAULT_CANVAS_WIDTH = 794;   // A4 width in pixels
export const DEFAULT_CANVAS_HEIGHT = 1123; // A4 height in pixels

export const CANVAS_DIMENSIONS = {
  A4_PORTRAIT: { width: 794, height: 1123 },
  A4_LANDSCAPE: { width: 1123, height: 794 }
};
```

### Responsive Breakpoints (constants/responsive.ts)
```typescript
export const BREAKPOINTS = {
  mobile: 480,    // < 480px
  tablet: 768,    // 480px - 1024px
  desktop: 1024   // > 1024px
};
```

---

## ✅ CHECKLIST DE CONFORMITÉ

- ✅ Tous les types TypeScript sont identiques
- ✅ Tous les contextes sont identiques
- ✅ Tous les hooks sont identiques
- ✅ Tous les utilitaires sont identiques
- ✅ Tous les composants sont identiques
- ✅ Tous les fichiers d'API sont identiques
- ✅ Tous les styles sont identiques
- ✅ Toutes les constantes sont identiques
- ✅ Points d'entrée identiques (index.tsx, PDFBuilder.tsx)
- ✅ Intégration WordPress identique

**CONFORMITÉ TOTALE: 100% ✅**

---

**DOCUMENT GÉNÉRÉ:** 15 janvier 2026  
**STATUT:** ✅ **RÉFÉRENCE COMPLÈTE DES CONTENUS CLÉS**
