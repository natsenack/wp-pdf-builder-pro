# Technical Documentation - Renderer System Architecture

## Architecture Overview

Le système de rendu de Preview utilise une architecture modulaire avec un router principal (`ElementRenderer`) qui délègue à des renderers spécialisés selon le type d'élément.

```
PreviewContext (Context API)
    ↓
CanvasMode (Mode d'affichage)
    ↓
ElementRenderer (Router principal)
    ├→ TextRenderer (text)
    ├→ DynamicTextRenderer (dynamic-text)
    ├→ RectangleRenderer (rectangle, shape-rectangle)
    ├→ ImageRenderer (image)
    ├→ TableRenderer (product_table)
    ├→ BarcodeRenderer (barcode, qrcode)
    ├→ ProgressBarRenderer (progress-bar)
    └→ CustomInfoRenderers (customer_info, company_info, order_number)
```

---

## Core Data Flow

### 1. Context Setup
```javascript
// PreviewContext fournit:
{
  state: {
    loading: boolean,
    error: string,
    data: previewData,      // Les données du template
    config: {
      elements: [],         // Array d'éléments à rendu
      templateData: {}      // Données dynamiques
    }
  },
  actions: { clearPreview }
}
```

### 2. Element Structure
```javascript
{
  id: "elem_123",
  type: "text|dynamic-text|image|rectangle|...",
  x: 0,                          // Position horizontale (points)
  y: 0,                          // Position verticale (points)
  width: 200,                    // Largeur (points)
  height: 50,                    // Hauteur (points)
  visible: true,                 // Affichage
  rotation: 0,                   // Rotation en degrés
  scale: 1,                      // Multiplicateur d'échelle
  opacity: 100,                  // Opacité 0-100
  
  // Commun
  shadow: false,
  shadowColor: "#000000",
  shadowOffsetX: 2,
  shadowOffsetY: 2,
  
  // Spécifique au type (voir plus bas)
  ...typeSpecificProperties
}
```

### 3. Canvas Rendering
```javascript
// CanvasMode définit l'échelle:
canvasScale = Math.min(1, containerWidth / pageWidth)  // ~0.8x pour affichage

// Chaque élément positionné:
position: absolute
left: x * canvasScale + "px"
top: y * canvasScale + "px"
transform: rotate() scale()
```

---

## Renderer Details

### TextRenderer
**Propriétés spécifiques:**
```javascript
{
  content: "Texte affiché",
  fontSize: 12,
  fontFamily: "Arial",
  fontWeight: "normal|bold",
  fontStyle: "normal|italic",
  textDecoration: "none|underline|overline|line-through",
  textAlign: "left|center|right",
  color: "#000000",
  lineHeight: 1.5,
  letterSpacing: 0
}
```

**CSS Clé:**
```css
height: ${height}px;              /* Pas minHeight */
white-space: pre-wrap;            /* Préserve les sauts de ligne */
overflow: hidden;                 /* Évite débordement */
word-wrap: break-word;            /* Wrap automatique */
line-height: ${lineHeight.toString()};  /* En string */
```

### DynamicTextRenderer
**Similaire à TextRenderer mais avec interpolation:**
```javascript
{
  content: "Commande #{orderId} pour {customer.name}",
  // Utilise previewData pour remplacer les variables
}

// Utilisation:
const variables = previewData || {};
const interpolated = content.replace(/{([^}]+)}/g, (match, key) => 
  variables[key] || match
);
```

### RectangleRenderer
**Propriétés spécifiques:**
```javascript
{
  backgroundColor: "#ffffff",
  borderWidth: 1,
  borderColor: "#000000",
  borderRadius: 0,
  borderStyle: "solid|dashed|dotted"
}
```

**Rendu:** Simple `<div>` vide avec styling

### ImageRenderer
**Propriétés spécifiques:**
```javascript
{
  imageUrl: "https://...",
  alt: "Texte alternatif",
  objectFit: "contain|cover|fill|none|scale-down",
  brightness: 100,           // 0-200%
  contrast: 100,             // 0-200%
  saturate: 100              // 0-200%
}
```

**State Management:**
```javascript
const [imageLoaded, setImageLoaded] = useState(true);
const [imageError, setImageError] = useState(false);

// onLoad → setImageLoaded(true), setImageError(false)
// onError → setImageLoaded(false), setImageError(true)
```

**Placeholder:** Affichage si pas d'URL ou erreur de chargement

### BarcodeRenderer
**Propriétés spécifiques:**
```javascript
{
  content: "123456789",    // Valeur à encoder
  code: "123456789",       // Fallback
  format: "CODE128",       // Format du code-barres
  // Pour QR: type: 'qrcode'
}
```

**Génération:**
```javascript
// Code-barres avec JsBarcode
JsBarcode(svgRef.current, codeValue, {
  format: 'CODE128',
  width: 2,
  height: calculatedHeight,
  displayValue: true,
  fontSize: 12,
  margin: 2
});

// QR Code avec qrcode.js
QRCode.toCanvas(canvasRef.current, codeValue, {
  errorCorrectionLevel: 'H',
  type: 'image/png',
  quality: 1
});
```

### ProgressBarRenderer
**Propriétés spécifiques:**
```javascript
{
  progressValue: 65,          // Pourcentage 0-100
  progressColor: "#3b82f6",   // Couleur de remplissage
  backgroundColor: "#e5e7eb"  // Couleur du fond
}
```

**Rendu:** Inner div avec `width: ${progressValue}%`

### TableRenderer
**Propriétés spécifiques:**
```javascript
{
  dataSource: "order_items",        // Clé des données
  headers: ["Produit", "Qté", ...],
  columns: {
    image: true,
    name: true,
    sku: false,
    quantity: true,
    price: true,
    total: true
  },
  showHeaders: true,
  showBorders: false,
  tableStyle: "default"
}
```

**Data Extraction:**
```javascript
const elementKey = `product_table_${element.id}`;
const tableData = previewData[elementKey] || {};
const rows = tableData.rows || [];
```

---

## CSS Standards

### Positionnement
```css
position: absolute;
left: ${x * canvasScale}px;      /* Toujours en px */
top: ${y * canvasScale}px;       /* Toujours en px */
width: ${width * canvasScale}px;
height: ${height * canvasScale}px;
```

### Transformations
```css
transform: rotate(${rotation}deg) scale(${scale});
transform-origin: top left;       /* Évite distorsion en diagonale */
```

### Effets
```css
opacity: ${opacity / 100};
box-shadow: ${offsetX}px ${offsetY}px 4px ${color};
```

### Contenus
```css
overflow: hidden;                 /* Évite débordement */
box-sizing: border-box;           /* Inclut padding/border dans dimensions */
white-space: pre-wrap;            /* Pour texte */
word-wrap: break-word;            /* Wrap automatique */
```

---

## Usage Examples

### Créer un élément texte
```javascript
const textElement = {
  id: "text_1",
  type: "text",
  x: 50,
  y: 100,
  width: 200,
  height: 50,
  content: "Bonjour le monde",
  fontSize: 16,
  fontFamily: "Arial",
  color: "#000000",
  visible: true
};
```

### Créer un code-barres dynamique
```javascript
const barcodeElement = {
  id: "barcode_1",
  type: "barcode",
  x: 300,
  y: 50,
  width: 200,
  height: 80,
  content: "DYNAMIC_VALUE",    // Sera remplacé par les données
  format: "CODE128"
};

// Passer les données:
const templateData = {
  barcodeValue: "123456789"
};
```

### Créer un tableau de produits
```javascript
const tableElement = {
  id: "table_1",
  type: "product_table",
  x: 50,
  y: 200,
  width: 500,
  height: 300,
  dataSource: "order_items",
  headers: ["Produit", "Qté", "Prix"],
  showHeaders: true
};

// Passer les données:
const templateData = {
  [`product_table_${tableElement.id}`]: {
    rows: [
      ["Laptop", "1", "999€"],
      ["Souris", "2", "25€"]
    ]
  }
};
```

---

## Common Props

Tous les renderers reçoivent et supportent:

```javascript
element           // L'objet élément complet
previewData       // Les données du template (pour dynamic content)
canvasScale       // L'échelle de rendu (0-1)
mode              // Mode d'affichage (preview, edit, etc.)
```

---

## Performance Optimizations

### 1. Memoization
```javascript
// Renderers utilisent useMemo pour les calculs coûteux
const style = useMemo(() => ({
  ...
}), [dependencies]);
```

### 2. shouldComponentUpdate
```javascript
// React.memo peut être utilisé pour éviter re-renders
export default React.memo(TextRenderer, (prev, next) => {
  return JSON.stringify(prev.element) === JSON.stringify(next.element);
});
```

### 3. useEffect pour côté-effects
```javascript
// BarcodeRenderer génère le code au premier render
useEffect(() => {
  generateBarcode();
}, [codeValue]);  // Re-générer seulement si la valeur change
```

---

## Error Handling

### ImageRenderer
```javascript
onError={() => {
  setImageLoaded(false);
  setImageError(true);
  console.warn(`Failed to load image: ${finalImageUrl}`);
}}
```

### BarcodeRenderer
```javascript
try {
  JsBarcode(...);
} catch (err) {
  console.error('Barcode generation failed:', err);
  // Fallback à placeholder
}
```

---

## Testing Strategy

### Unit Tests
```javascript
// Test chaque renderer isolément
test('TextRenderer displays content correctly', () => {
  const element = { content: "Test", ... };
  const { getByText } = render(<TextRenderer element={element} />);
  expect(getByText("Test")).toBeInTheDocument();
});
```

### Integration Tests
```javascript
// Test ElementRenderer routing
test('ElementRenderer routes to correct renderer', () => {
  const textElement = { type: 'text', ... };
  // Vérifier que TextRenderer est appelé
});
```

### E2E Tests
```javascript
// Test dans la vraie modale
test('Preview modal displays all elements correctly', () => {
  // Charger modale avec éléments
  // Vérifier affichage
});
```

---

## Dependencies

### NPM Packages
```json
{
  "jsbarcode": "^3.11.5",
  "qrcode": "^1.5.0",
  "react": "^18.0.0",
  "react-dom": "^18.0.0"
}
```

### Imports Clés
```javascript
import { usePreviewContext } from '../context/PreviewContext';
import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';
```

---

## Deployment Checklist

- [ ] Tous les renderers compilent sans erreurs
- [ ] Webpack bundle size acceptable (< 1MB)
- [ ] Tests unitaires passent
- [ ] Tests d'intégration passent
- [ ] Performance test acceptable
- [ ] Console propre (pas d'erreurs)
- [ ] Images optimisées
- [ ] Codes-barres/QR codes générés correctement
- [ ] Données dynamiques remplacées correctement
- [ ] Styling cohérent avec design

---

**Last Updated:** 21 Octobre 2025
**Version:** 1.0.0
**Status:** Production Ready
