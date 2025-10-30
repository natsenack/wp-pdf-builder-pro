# 🔧 Audit & Corrections Phase 3.0 - 30 octobre 2025 (21:27)

**Status** : ✅ FIXES DÉPLOYÉS  
**Commit** : `v1.0.0-30eplo25-20251030-212705`

---

## 🔴 Problèmes identifiés

### 1. **La metabox WooCommerce ne chargeait JAMAIS l'aperçu PHP**
- ❌ `loadPhpPreviewImage()` était définie mais JAMAIS appelée
- ❌ PreviewModal ne savait pas différencier éditeur vs metabox
- ❌ Aperçu affichait toujours les mêmes données fictives

### 2. **Pas de détection de contexte**
- ❌ Pas de distinction entre `state.elements` (éditeur) et vraies données (metabox)
- ❌ Window.pdf_builder (orderId + templateId) n'était pas exploité

### 3. **Données WooCommerce non utilisées**
- ❌ Malgré que PHP Handler récupère `$order->get_items()` (vrais produits)
- ❌ PreviewModal ne l'appelait jamais pour la metabox

---

## ✅ Corrections apportées

### 1. **Transmission des données globales PHP vers JS**
**Fichier** : `plugin/src/Managers/PDF_Builder_WooCommerce_Integration.php` (ligne 252+)

```javascript
// ✅ AVANT : orderId/templateId n'étaient que des variables locales
var orderId = 42;
var templateId = 1;

// ✅ APRÈS : données transmises globalement
window.pdf_builder = {
  orderId: 42,
  templateId: 1,
  nonce: 'xxx'
};
```

### 2. **Détection du contexte dans PreviewModal**
**Fichier** : `assets/js/src/pdf-builder-react/components/ui/PreviewModal.tsx` (ligne 93+)

```typescript
// ✅ NOUVEAU : Détecter si on est en metabox ou éditeur
const { orderId, templateId } = getOrderAndTemplateId();
const isMetabox = orderId > 0 && templateId > 0;

if (isMetabox) {
  // 🔴 METABOX : charger données réelles depuis PHP
  setUsePhpRendering(true);
} else if (state.elements.length > 0) {
  // ✅ ÉDITEUR : utiliser Canvas 2D
  setUsePhpRendering(false);
  setPreviewElements([...state.elements]);
}
```

### 3. **Appel de l'aperçu PHP en metabox**
**Fichier** : `assets/js/src/pdf-builder-react/components/ui/PreviewModal.tsx` (ligne 127+)

```typescript
// ✅ NOUVEAU useEffect : charger aperçu PHP si metabox
useEffect(() => {
  if (!isOpen || !usePhpRendering) return;
  
  const { orderId, templateId } = getOrderAndTemplateId();
  if (orderId > 0 && templateId > 0) {
    // Appelle PreviewImageAPI.generatePreviewImage(orderId, templateId)
    // qui retourne PNG avec données réelles WooCommerce !
  }
}, [isOpen, usePhpRendering, getOrderAndTemplateId]);
```

### 4. **Amélioration du rendu conditionnel**
**Fichier** : `assets/js/src/pdf-builder-react/components/ui/PreviewModal.tsx` (ligne ~920)

```tsx
// ✅ Affiche PHP PNG EN PRIORITÉ
{usePhpRendering && previewImage ? (
  <img src={previewImage} />  // VRAIS DATA WooCommerce rendues par TCPDF
) : (
  <canvas ref={canvasRef} />  // Fallback : Canvas 2D editeur
)}
```

---

## 🎯 Flux de données maintenant

### **Scénario 1 : Éditeur PDF (Canvas)**
```
Utilisateur clique "Aperçu"
  ↓
state.elements (éditeur)
  ↓
setUsePhpRendering(false)
  ↓
Canvas 2D rendu
  ↓
Aperçu avec données fictives (Jean Dupont, etc.)
```

### **Scénario 2 : Metabox WooCommerce (PHP)**
```
Utilisateur clique "Aperçu PDF" en metabox
  ↓
window.pdf_builder.orderId = 42, templateId = 1
  ↓
getOrderAndTemplateId() = { 42, 1 }
  ↓
setUsePhpRendering(true)
  ↓
AJAX → handler PHP (pdf_builder_preview_image)
  ↓
PHP récupère:
  - Template depuis BDD
  - Commande réelle WooCommerce ($order->get_items())
  - Rend avec TCPDF
  - Convertit PNG
  ↓
Base64 PNG retourné
  ↓
<img src="data:image/png;base64,..." />
  ↓
✅ Aperçu avec VRAIS données produits, client, totaux !
```

---

## 📊 Validation de l'architecture

### ✅ **Récupération BDD**
- Template JSON récupéré depuis `wp_pdf_builder_templates.data`
- Éléments extraits depuis `template_data['elements']`
- Corrrect !

### ✅ **Rendu TCPDF**
- product_table : boucle sur `$order->get_items()` (VRAIS produits)
- customer_info : appelle `$order->get_billing_*()` (VRAIS données)
- company_logo : charge depuis `$element['imageUrl']`
- Correct !

### ✅ **Affichage Frontend**
- PreviewModal détecte contexte (éditeur vs metabox)
- Appelle PreviewImageAPI avec orderId réel
- Affiche PNG base64
- Correct !

### ⚠️ **Cohérence Roadmap**
- ✅ Phase 3.0 : PreviewImageAPI implémenté ✓
- ✅ TCPDF rendering : fonctionnel ✓
- ✅ Dual mode (Canvas + PHP) : implémenté ✓
- ⏳ Auto-save : à faire Phase 3.1
- ⏳ JSON reload : à faire Phase 3.1

---

## 🧪 Ce qui devrait marcher maintenant

### Métabox WooCommerce
1. Ouvrir une commande avec des produits réels
2. Cliquer "Aperçu PDF"
3. ✅ Image PNG charge (base64)
4. ✅ Produits réels affichés dans le tableau
5. ✅ Client réel (Jean Dupont → nom RÉEL)
6. ✅ Totaux calculés correctement

### Éditeur PDF
1. Ouvrir éditeur Canvas
2. Cliquer "Aperçu"
3. ✅ Canvas 2D s'affiche
4. ✅ Données fictives (Jean Dupont)
5. ✅ Permet de prévisualiser le design

---

## 📝 Fichiers modifiés

| Fichier | Changement | Ligne |
|---------|-----------|-------|
| PreviewModal.tsx | Détection contexte + appel PHP | 93-127 |
| PreviewModal.tsx | useEffect PHP rendering | 133-156 |
| PreviewModal.tsx | Affichage conditionnel img/canvas | ~920 |
| WooCommerce_Integration.php | window.pdf_builder setup | 252+ |

---

## 🚀 Prochaines étapes

### Phase 3.1 (À faire)
- [ ] Sauvegarde automatique state.elements (2-3s)
- [ ] Rechargement JSON depuis BDD
- [ ] Indicateur "Saving..."

### Phase 3.2 (À faire)
- [ ] Tests 100+ scénarios
- [ ] Edge cases (image manquante, produit sans prix, etc.)
- [ ] Performance benchmarks

---

## ✅ Déploiement récapitulatif

- **Build** : ✅ Webpack SUCCESS
- **Upload FTP** : ✅ 3 fichiers (5.1s)
- **Git** : ✅ Commit + Tag v1.0.0-30eplo25-20251030-212705
- **Status** : ✅ PRÊT POUR TEST

---

**À tester maintenant sur le site réel :**
1. Métabox WooCommerce avec ordre réel
2. Vérifier que product_table affiche VRAIS articles
3. Vérifier que données client sont correctes
4. Console logs pour debug (voir [PREVIEW MODAL] Context detected)

