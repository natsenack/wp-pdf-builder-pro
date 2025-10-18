# Audit Complet des Propriétés - Canvas Editor vs PHP Controller

## 🎯 Objectif
Assurer que le PHP_Generator_Controller.php supporte TOUTES les propriétés utilisées dans PreviewModal.jsx et CanvasElement.jsx pour une synchronisation complète du metabox preview.

---

## 📋 Propriétés Communes à TOUS les Éléments

### Positionnement et Taille
- ✅ `x` - Coordonnée X (pixels)
- ✅ `y` - Coordonnée Y (pixels)
- ✅ `width` - Largeur (pixels)
- ✅ `height` - Hauteur (pixels)

### Propriétés de Base
- ✅ `id` - Identifiant unique
- ✅ `type` - Type d'élément
- ✅ `zIndex` - Ordre d'affichage (layering)
- ⚠️ `visible` - Élément visible/caché (non trouvé en PHP) **À VÉRIFIER**

### Typography (Texte)
- ✅ `fontSize` - Taille du texte
- ✅ `fontFamily` - Police de caractères
- ✅ `fontWeight` - Poids de la police (normal, bold, 600, 700, etc.)
- ✅ `fontStyle` - Style (normal, italic)
- ✅ `color` - Couleur du texte
- ⚠️ `textDecoration` - Décoration (underline, line-through, none) **À AJOUTER en PHP**
- ⚠️ `lineHeight` - Hauteur de ligne (numérique ou "1.2", "1.4") **À AJOUTER en PHP**
- ✅ `textAlign` - Alignement (left, center, right)

### Styling (Couleurs et Bordures)
- ✅ `backgroundColor` - Couleur de fond
- ✅ `borderColor` - Couleur de bordure
- ✅ `borderWidth` - Largeur de bordure
- ✅ `borderStyle` - Style de bordure (solid, dashed, dotted)
- ✅ `borderRadius` - Rayon de bordure (arrondi)

### Effets Visuels
- ⚠️ `opacity` - Opacité (0-100%, défaut 100) **À AJOUTER en PHP**
- ⚠️ `rotation` - Rotation (degrés) **À AJOUTER en PHP**
- ⚠️ `scale` - Échelle (100% = normal, 150% = +50%) **À AJOUTER en PHP**
- ⚠️ `brightness` - Luminosité (100% = normal) **À AJOUTER en PHP**
- ⚠️ `contrast` - Contraste (100% = normal) **À AJOUTER en PHP**
- ⚠️ `saturate` - Saturation (100% = normal) **À AJOUTER en PHP**

### Ombres
- ⚠️ `shadow` - Booléen d'activation d'ombre **À AJOUTER en PHP**
- ⚠️ `shadowOffsetX` - Décalage X de l'ombre (défaut 2px) **À AJOUTER en PHP**
- ⚠️ `shadowOffsetY` - Décalage Y de l'ombre (défaut 2px) **À AJOUTER en PHP**
- ⚠️ `shadowColor` - Couleur de l'ombre (défaut #000000) **À AJOUTER en PHP**

---

## 🔤 Propriétés Spécifiques par Type d'Élément

### 1. **TEXT** (Texte Simple)
```javascript
{
  type: 'text',
  content: string,
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor, borderWidth, borderStyle, borderColor, borderRadius,
  textDecoration,      // ⚠️ À AJOUTER
  lineHeight,          // ⚠️ À AJOUTER
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
  scale,               // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 2. **RECTANGLE** (Boîte/Forme)
```javascript
{
  type: 'rectangle',
  x, y, width, height,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
  scale,               // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 3. **CIRCLE** (Cercle)
```javascript
{
  type: 'circle',
  x, y, width, height,
  backgroundColor,
  borderWidth, borderStyle, borderColor,
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
  scale,               // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 4. **IMAGE**
```javascript
{
  type: 'image',
  imageUrl: string,
  x, y, width, height,
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
  scale,               // ⚠️ À AJOUTER
  brightness,          // ⚠️ À AJOUTER
  contrast,            // ⚠️ À AJOUTER
  saturate,            // ⚠️ À AJOUTER
  borderWidth, borderStyle, borderColor, borderRadius,
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 5. **LINE** (Ligne)
```javascript
{
  type: 'line',
  x, y, width, height,
  borderColor,
  borderWidth,
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 6. **DIVIDER**
```javascript
{
  type: 'divider',
  x, y, width, height,
  borderColor,
  borderWidth,
  borderStyle,
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
}
```

### 7. **PRODUCT_TABLE**
```javascript
{
  type: 'product_table',
  x, y, width, height,
  fontSize,
  fontFamily,
  borderWidth, borderColor, borderRadius,
  backgroundColor,
  tableStyle: enum ['default', 'classic', 'modern', 'minimal', 'striped', 'bordered', 'slate_gray', 'coral', 'teal', 'indigo', 'amber'],
  
  // Visibilité des colonnes
  columns: {
    image: boolean,    // ⚠️ À AJOUTER
    name: boolean,     // ⚠️ À AJOUTER
    sku: boolean,      // ⚠️ À AJOUTER
    quantity: boolean, // ⚠️ À AJOUTER
    price: boolean,    // ⚠️ À AJOUTER
    total: boolean     // ⚠️ À AJOUTER
  },
  
  // Options d'affichage
  showHeaders: boolean,    // ✅ Existe
  showBorders: boolean,    // ✅ Existe
  showSubtotal: boolean,   // ✅ Existe
  showShipping: boolean,   // ✅ Existe
  showTaxes: boolean,      // ✅ Existe
  showDiscount: boolean,   // ✅ Existe
  showTotal: boolean,      // ✅ Existe
  
  // Couleurs de lignes alternées
  evenRowBg: string,       // ⚠️ À AJOUTER
  oddRowBg: string,        // ⚠️ À AJOUTER
  evenRowTextColor: string, // ⚠️ À AJOUTER
  oddRowTextColor: string,  // ⚠️ À AJOUTER
  
  // Données d'aperçu
  previewProducts: [array] // ⚠️ À AJOUTER
}
```

### 8. **CUSTOMER_INFO**
```javascript
{
  type: 'customer_info',
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  layout: enum ['vertical', 'horizontal'],
  fields: array ['name', 'email', 'phone', 'address'],
  spacing: number,
  
  showLabels: boolean,  // ⚠️ À AJOUTER
  labelStyle: enum ['bold', 'uppercase', 'normal'],  // ⚠️ À AJOUTER
  
  lineHeight,           // ⚠️ À AJOUTER
  opacity,              // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 9. **COMPANY_INFO**
```javascript
{
  type: 'company_info',
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  showLogoAbove: boolean,  // ⚠️ À AJOUTER
  spacing: number,         // ⚠️ À AJOUTER
  
  lineHeight,              // ⚠️ À AJOUTER
  opacity,                 // ⚠️ À AJOUTER
  textDecoration,          // ⚠️ À AJOUTER
}
```

### 10. **COMPANY_LOGO**
```javascript
{
  type: 'company_logo',
  x, y, width, height,
  logoUrl: string,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  opacity,             // ⚠️ À AJOUTER
  rotation,            // ⚠️ À AJOUTER
  scale,               // ⚠️ À AJOUTER
  brightness,          // ⚠️ À AJOUTER
  contrast,            // ⚠️ À AJOUTER
  saturate,            // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 11. **ORDER_NUMBER**
```javascript
{
  type: 'order_number',
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  prefix: string,  // ⚠️ À AJOUTER
  suffix: string,  // ⚠️ À AJOUTER
  
  textDecoration,  // ⚠️ À AJOUTER
  lineHeight,      // ⚠️ À AJOUTER
  opacity,         // ⚠️ À AJOUTER
}
```

### 12. **ORDER_DATE**
```javascript
{
  type: 'order_date',
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  dateFormat: string,  // ⚠️ À AJOUTER (ex: 'd/m/Y', 'Y-m-d', etc.)
  
  textDecoration,      // ⚠️ À AJOUTER
  lineHeight,          // ⚠️ À AJOUTER
  opacity,             // ⚠️ À AJOUTER
}
```

### 13. **DOCUMENT_TYPE**
```javascript
{
  type: 'document_type',
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  content: string,  // Le type de document (Facture, Bon de Commande, etc.)
  
  textDecoration,   // ⚠️ À AJOUTER
  lineHeight,       // ⚠️ À AJOUTER
  opacity,          // ⚠️ À AJOUTER
}
```

### 14. **TOTAL**
```javascript
{
  type: 'total',
  x, y, width, height,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  prefix: string,        // ⚠️ À AJOUTER (ex: "Total: ")
  decimals: number,      // ⚠️ À AJOUTER (nombre de décimales)
  currencySymbol: string, // ⚠️ À AJOUTER (ex: "€", "$")
  
  textDecoration,        // ⚠️ À AJOUTER
  lineHeight,            // ⚠️ À AJOUTER
  opacity,               // ⚠️ À AJOUTER
}
```

### 15. **PROGRESS_BAR**
```javascript
{
  type: 'progress-bar',
  x, y, width, height,
  
  progressValue: number,     // Pourcentage (0-100)
  barColor: string,          // ✅ Existe
  backgroundColor: string,   // ✅ Existe
  
  showValue: boolean,        // ⚠️ À AJOUTER
  valuePosition: enum ['inside', 'outside'], // ⚠️ À AJOUTER
  valueColor: string,        // ⚠️ À AJOUTER
  valueFont: string,         // ⚠️ À AJOUTER
  valueFontSize: number,     // ⚠️ À AJOUTER
  
  borderRadius,
  opacity,                   // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 16. **BARCODE**
```javascript
{
  type: 'barcode',
  x, y, width, height,
  
  barcodeData: string,   // Données du code-barre
  barcodeFormat: enum ['CODE128', 'CODE39', 'EAN13', 'UPC'], // ⚠️ À AJOUTER
  
  opacity,               // ⚠️ À AJOUTER
  rotation,              // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 17. **QRCODE**
```javascript
{
  type: 'qrcode',
  x, y, width, height,
  
  qrData: string,        // Données du QR code
  errorCorrection: enum ['L', 'M', 'Q', 'H'],  // ⚠️ À AJOUTER
  
  opacity,               // ⚠️ À AJOUTER
  rotation,              // ⚠️ À AJOUTER
  backgroundColor,       // ⚠️ À AJOUTER
  shadow, shadowOffsetX, shadowOffsetY, shadowColor  // ⚠️ À AJOUTER
}
```

### 18. **DYNAMIC_TEXT**
```javascript
{
  type: 'dynamic-text',
  x, y, width, height,
  
  template: enum [
    'total_only',
    'order_info',
    'customer_info',
    'customer_address',
    'full_header',
    'invoice_header',
    'order_summary',
    'payment_info',
    'payment_terms',
    'shipping_info',
    'thank_you',
    'legal_notice',
    'bank_details',
    'contact_info',
    'order_confirmation',
    'delivery_note',
    'warranty_info',
    'return_policy',
    'signature_line',
    'invoice_footer',
    'terms_conditions',
    'quality_guarantee',
    'eco_friendly',
    'follow_up',
    'custom'
  ],
  customContent: string, // Contenu personnalisé pour template 'custom'
  
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  textDecoration,        // ⚠️ À AJOUTER
  lineHeight,            // ⚠️ À AJOUTER
  opacity,               // ⚠️ À AJOUTER
  rotation,              // ⚠️ À AJOUTER
}
```

### 19. **MENTIONS** (Mentions légales, etc.)
```javascript
{
  type: 'mentions',
  x, y, width, height,
  
  content: string,
  fontSize, fontFamily, fontWeight, fontStyle,
  color, textAlign,
  backgroundColor,
  borderWidth, borderStyle, borderColor, borderRadius,
  
  textDecoration,        // ⚠️ À AJOUTER
  lineHeight,            // ⚠️ À AJOUTER
  opacity,               // ⚠️ À AJOUTER
}
```

---

## 🔍 Récapitulatif des Propriétés Manquantes en PHP

### Propriétés de Style Avancées (Critiques)
1. **textDecoration** - Décoration du texte (underline, line-through)
2. **lineHeight** - Hauteur de ligne
3. **opacity** - Opacité des éléments
4. **rotation** - Rotation en degrés
5. **scale** - Mise à l'échelle (100% = normal)
6. **brightness** - Luminosité pour les images
7. **contrast** - Contraste pour les images
8. **saturate** - Saturation pour les images

### Propriétés d'Ombre (Critiques)
9. **shadow** - Booléen d'activation
10. **shadowOffsetX** - Décalage horizontal
11. **shadowOffsetY** - Décalage vertical
12. **shadowColor** - Couleur de l'ombre

### Propriétés de Tableaux Product (Importants)
13. **columns.image, columns.name, columns.sku, columns.quantity, columns.price, columns.total** - Visibilité des colonnes
14. **evenRowBg, oddRowBg** - Couleurs des lignes alternées
15. **evenRowTextColor, oddRowTextColor** - Couleur du texte des lignes alternées
16. **previewProducts** - Données de produits pour l'aperçu

### Propriétés de Customer Info (Importants)
17. **showLabels** - Afficher les étiquettes
18. **labelStyle** - Style des étiquettes

### Propriétés de Progress Bar (Importants)
19. **showValue** - Afficher le pourcentage
20. **valuePosition** - Position du pourcentage (inside/outside)
21. **valueColor, valueFont, valueFontSize** - Styling du pourcentage

### Propriétés de Code Barres/QR (Importants)
22. **barcodeFormat** - Format du code-barre
23. **errorCorrection** - Correction d'erreur du QR code

---

## ✅ Plan d'Action

1. **Étape 1** : Modifier `extract_element_properties()` dans PHP pour inclure TOUTES les propriétés manquantes
2. **Étape 2** : Mettre à jour chaque méthode `render_*_element()` pour utiliser ces propriétés
3. **Étape 3** : Tester avec des éléments complets incluant tous les effets visuels
4. **Étape 4** : Valider la synchronisation metabox preview

