# Architecture des Valeurs Fictives ↔ Réelles

## 📊 Vue d'ensemble

Système complet permettant aux éléments du canvas d'afficher:
- **Mode Édition**: Données fictives/test (pour éditer sans commande)
- **Mode Aperçu**: Données réelles WooCommerce (aperçu avec commande réelle)

### Exceptions
- **company_info**: Affiche TOUJOURS les vraies données (exception importante)

---

## 🏗️ Architecture en 3 couches

### 1️⃣ Types (BaseElement Extension)
**Fichier**: `src/js/react/types/elements.ts`

```typescript
interface BaseElement {
  // ... propriétés existantes ...
  
  // ✅ NEW: Support données réelles
  isRealDataElement?: boolean;        // true = récupère données WooCommerce
  defaultTestValue?: unknown;         // valeur fictive affichée en édition
  realDataKey?: string;               // clé pour récupérer depuis WC
}
```

**Éléments RealData configurés automatiquement:**
- order_number
- woocommerce_order_date
- customer_info
- product_table
- company_info

---

### 2️⃣ ValueResolver (Source de vérité)
**Fichier**: `src/js/react/persistence/ValueResolver.ts`

Résout les valeurs selon la logique:
```typescript
if (elementType === 'company_info') {
  → Toujours retourner vraie valeur (EXCEPTION)
}

if (isPreviewMode === true) {
  → Retourner testValue (données fictives)
}

if (isPreviewMode === false && hasRealData) {
  → Retourner getRealValue() depuis WC
}

fallback → testValue
```

**Interface RealOrderData:**
```typescript
interface RealOrderData {
  orderId?: string;
  orderNumber?: string;
  orderDate?: string;
  customerName?: string;
  customerEmail?: string;
  products?: Array<{name, sku, quantity, price}>;
  companyName?: string;
  companyAddress?: string;
  // ...
}
```

---

### 3️⃣ CanvasPersistence (Couche d'application)
**Fichier**: `src/js/react/utils/CanvasPersistence.ts`

Nouvelle signature de `deserializeCanvasData()`:
```typescript
deserializeCanvasData(
  jsonData: string | object,
  options?: {
    mode?: 'editor' | 'preview';           // Mode d'affichage
    realOrderData?: RealOrderData | null;  // Données WooCommerce
  }
): { elements: Element[]; canvas: CanvasState }
```

**Logique:**
1. Parse les données depuis JSON
2. Normalise la structure
3. **Si mode='preview' + realOrderData:** Applique ValueResolver
4. Injecte les valeurs résolues dans les éléments RealData
5. Retourne éléments prêts à l'affichage

---

## 🔄 Flux complet

### Édition (Mode Editor)
```
React Component
  ↓
useTemplate.loadExistingTemplate()
  ↓
deserializeCanvasData(json, { mode: 'editor' })
  ↓
ValueResolver(isPreviewMode=true)
  ↓
Éléments avec données FICTIVES
  ↓
Canvas affiche: "N° 001", "Jean Dupont", produits fictifs
  EXCEPTION: company_info = vraies données
```

### Aperçu Miroir (Mode Preview)
```
React Component
  ↓
useTemplate.loadTemplateForPreview(orderId)
  ↓
Récupère RealOrderData depuis WC AJAX
  ↓
deserializeCanvasData(json, { 
  mode: 'preview', 
  realOrderData: wc_order_data 
})
  ↓
ValueResolver(isPreviewMode=false, realData=wc_order_data)
  ↓
Éléments avec données RÉELLES
  ↓
Canvas affiche: "N° 12345", "Jean Dumont", produits réels
  EXCEPTION: company_info = vraies données (comme en édition)
```

---

## 📦 Fichiers créés/modifiés

### Créés:
1. **ValueResolver.ts** (persistence/)
   - Classe responsable de résoudre les valeurs
   - Logique fictive/réelle centralisée
   - Gestion des exceptions (company_info)

2. **RealDataElementsHelper.ts** (utils/)
   - Initialisation des éléments RealData
   - Détection des types RealData
   - Configuration automatique

### Modifiés:
1. **elements.ts** (types/)
   - Extension BaseElement avec propriétés RealData
   - 3 nouvelles propriétés optionnelles

2. **CanvasPersistence.ts** (utils/)
   - Intégration ValueResolver dans `deserializeCanvasData()`
   - Support du mode et realOrderData
   - Injection de valeurs résolues

---

## 🎯 Utilisation dans les composants

### Charger template en mode édition:
```typescript
const { elements, canvas } = deserializeCanvasData(templateJsonString);
// Affiche données fictives
```

### Charger template pour aperçu:
```typescript
const realData = await fetchOrderDataFromWC(orderId);

const { elements, canvas } = deserializeCanvasData(
  templateJsonString,
  {
    mode: 'preview',
    realOrderData: realData
  }
);
// Affiche données réelles (sauf company_info = vraies)
```

---

## 🔍 Debugging

### Voir quels éléments sont RealData:
```typescript
debugCanvasData(data, 'Template avec RealData');
// Affiche: "📊 RealData elements: 3"
// List: order_number (key: orderNumber), customer_info, product_table
```

### Valider la structure:
```typescript
const { valid, errors } = validateCanvasData(data);
if (!valid) {
  errors.forEach(e => console.error(e));
}
```

---

## 📝 Notes importantes

1. **Sauvegarde**: `serializeCanvasData()` sauvegarde TOUT en JSON, y compris:
   - isRealDataElement: true
   - defaultTestValue: "N° 001"
   - realDataKey: "orderNumber"
   
   Ces propriétés restent dans la DB pour usage ultérieur.

2. **Fallback automatique**: Si mode=preview mais pas de realOrderData, utilise defaultTestValue.

3. **Compatibilité**: Éléments existants sans isRealDataElement restent inchangés (backward compatible).

4. **Exception company_info**: Elle s'applique MÊME en mode édition - toujours récupère vraie valeur si disponible.

---

## 🚀 Future: Preview Mirror System

Étapes à venir:
1. Ajouter bouton "Aperçu" dans l'éditeur
2. Popup/modal avec sélection de commande WC
3. Charger order data + rappeler deserializeCanvasData(mode='preview')
4. Afficher le template côte à côte ou en alternance
5. Possibilité de basculer entre édition/aperçu
