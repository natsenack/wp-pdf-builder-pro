# 🎯 Guide d'utilisation PreviewImageAPI

**Version** : 1.0.0  
**Depuis** : Phase 3.0 (30 octobre 2025)  
**Destiné à** : Développeurs intégrant l'aperçu PDF

---

## 📚 Overview

`PreviewImageAPI` est une classe TypeScript singleton qui génère des images PNG d'aperçu PDF côté serveur (PHP/TCPDF) et les retourne en base64 pour affichage modal.

### Avantages
- ✅ Aperçu fidèle au PDF production (même rendu TCPDF)
- ✅ Supporte tous éléments (produits, logos, variables)
- ✅ Cache client pour performance
- ✅ Gestion erreurs robuste

---

## 🚀 Utilisation basique

### 1. Importer l'API
```typescript
import PreviewImageAPI from '../../api/PreviewImageAPI';

// Ou récupérer l'instance singleton
const api = PreviewImageAPI.getInstance();
```

### 2. Générer une image d'aperçu
```typescript
const result = await api.generatePreviewImage({
  orderId: 2025,           // ID commande WooCommerce
  templateId: 1,           // ID template PDF
  format: 'png'            // 'png' | 'jpg' | 'pdf'
});

if (result.success) {
  // Image générée !
  console.log(result.data.image); // Data URL base64
  
  // Afficher dans <img>
  const img = document.querySelector('img');
  img.src = result.data.image;
} else {
  console.error('Erreur:', result.error);
}
```

---

## 🛠️ API Complète

### Classe : `PreviewImageAPI`

#### Méthode : `getInstance()`
**Retour** : `PreviewImageAPI`  
**Description** : Récupère l'instance singleton (une seule instance par page)

```typescript
const api = PreviewImageAPI.getInstance();
```

#### Méthode : `generatePreviewImage(options)`
**Paramètres** :
```typescript
interface PreviewImageOptions {
  orderId: number;              // ✅ Requis - ID commande WooCommerce
  templateId: number;           // ✅ Requis - ID template PDF
  format?: 'png' | 'jpg' | 'pdf'; // ⏳ Optionnel - défaut 'png'
  width?: number;               // ⏳ Réservé pour futur
  height?: number;              // ⏳ Réservé pour futur
}
```

**Retour** :
```typescript
interface PreviewImageResponse {
  success: boolean;
  data?: {
    image: string;    // Data URL : "data:image/png;base64,..."
    format: string;   // Format : "png", "jpg", "pdf"
    type: string;     // MIME type : "image/png"
  };
  error?: string;    // Message d'erreur si success=false
}
```

**Exemple** :
```typescript
const response = await api.generatePreviewImage({
  orderId: 42,
  templateId: 3,
  format: 'png'
});

// Réponse succès
{
  success: true,
  data: {
    image: 'data:image/png;base64,iVBORw0KGgo...',
    format: 'png',
    type: 'image/png'
  }
}

// Réponse erreur
{
  success: false,
  error: 'Erreur: Order not found'
}
```

#### Méthode : `validateOptions(options)`
**Description** : Valide les paramètres avant génération  
**Retour** : `boolean`

```typescript
if (!api.validateOptions({ orderId: 0, templateId: 1 })) {
  console.error('Paramètres invalides');
}
```

**Validations** :
- ✅ orderId > 0
- ✅ templateId > 0
- ✅ format dans ['png', 'jpg', 'pdf']

#### Méthode : `clearCache()`
**Description** : Vide complètement le cache

```typescript
api.clearCache();
console.log('Cache vidé');
```

#### Méthode : `clearCacheForOrder(orderId)`
**Description** : Vide cache pour une commande spécifique

```typescript
api.clearCacheForOrder(42);
// Prochaine génération pour cette commande refera l'appel AJAX
```

#### Méthode : `downloadPreviewImage(imageDataUrl, filename)`
**Description** : Télécharge l'image en tant que fichier

```typescript
await api.downloadPreviewImage(
  'data:image/png;base64,...',
  'apercu_commande_42.png'
);
```

**Erreur possible** : Lance une exception si le téléchargement échoue

---

## 📋 Cas d'usage

### Cas 1 : Aperçu dans modal (utilisation actuelle)
```typescript
// Dans PreviewModal.tsx
const [previewImage, setPreviewImage] = useState<string | null>(null);
const [isLoading, setIsLoading] = useState(false);

const loadPreview = async () => {
  setIsLoading(true);
  
  const result = await PreviewImageAPI.generatePreviewImage({
    orderId: currentOrder.id,
    templateId: currentTemplate.id
  });
  
  if (result.success && result.data?.image) {
    setPreviewImage(result.data.image);
  }
  
  setIsLoading(false);
};

return (
  <div>
    {isLoading && <div>Chargement...</div>}
    {previewImage && <img src={previewImage} />}
  </div>
);
```

### Cas 2 : Aperçu avec retry
```typescript
const MAX_RETRIES = 3;

async function generateWithRetry(orderId, templateId, retries = 0) {
  try {
    const result = await PreviewImageAPI.generatePreviewImage({
      orderId,
      templateId
    });
    
    if (result.success) {
      return result.data;
    }
    
    if (retries < MAX_RETRIES) {
      console.warn(`Retry ${retries + 1}/${MAX_RETRIES}`);
      await new Promise(r => setTimeout(r, 1000)); // Wait 1s
      return generateWithRetry(orderId, templateId, retries + 1);
    }
    
    throw new Error(result.error);
  } catch (error) {
    console.error('Failed after retries:', error);
    throw error;
  }
}
```

### Cas 3 : Bouton télécharger aperçu
```typescript
<button onClick={async () => {
  const result = await api.generatePreviewImage({
    orderId: 42,
    templateId: 1
  });
  
  if (result.success) {
    await api.downloadPreviewImage(
      result.data.image,
      `apercu_cmd_42_${new Date().toISOString().slice(0,10)}.png`
    );
  }
}}>
  ⬇️ Télécharger aperçu
</button>
```

### Cas 4 : Invalidation cache après modification
```typescript
// Après modification du template
function handleSaveTemplate(templateId) {
  // ... sauvegarder
  
  // Invalider cache pour toutes les commandes de ce template
  api.clearCache();
  
  // Ou plus granulaire (si on connaît les commandes)
  activeOrders.forEach(order => {
    api.clearCacheForOrder(order.id);
  });
}
```

### Cas 5 : Génération en batch
```typescript
async function generateBatchPreviews(orders, templateId) {
  const results = [];
  
  for (const order of orders) {
    try {
      const result = await api.generatePreviewImage({
        orderId: order.id,
        templateId
      });
      
      if (result.success) {
        results.push({
          orderId: order.id,
          image: result.data.image
        });
      }
    } catch (error) {
      console.error(`Order ${order.id} failed:`, error);
    }
  }
  
  return results;
}

// Utilisation
const previews = await generateBatchPreviews(ordersList, templateId);
```

---

## ⚠️ Gestion d'erreurs

### Erreurs courantes

#### 1. Order not found (Order invalide)
```typescript
{
  success: false,
  error: 'Order not found'
}
```

**Solution** : Vérifier que orderId existe en WooCommerce

#### 2. Template not found (Template invalide)
```typescript
{
  success: false,
  error: 'Template not found'
}
```

**Solution** : Vérifier que templateId existe en BDD

#### 3. Invalid template data
```typescript
{
  success: false,
  error: 'Invalid template data'
}
```

**Solution** : Vérifier que le template JSON est valide

#### 4. Permission denied
```typescript
{
  success: false,
  error: 'Permission denied'
}
```

**Solution** : Utilisateur doit avoir rôle `manage_woocommerce` ou `edit_shop_orders`

#### 5. Invalid nonce
```typescript
{
  success: false,
  error: 'Invalid nonce'
}
```

**Solution** : Nonce AJAX expiré (session timeout), rafraîchir page

### Gestion globale des erreurs
```typescript
async function safeGeneratePreview(orderId, templateId) {
  try {
    const api = PreviewImageAPI.getInstance();
    
    if (!api.validateOptions({ orderId, templateId })) {
      throw new Error('Paramètres invalides');
    }
    
    const result = await api.generatePreviewImage({
      orderId,
      templateId
    });
    
    if (!result.success) {
      throw new Error(result.error || 'Erreur inconnue');
    }
    
    return result.data.image;
    
  } catch (error) {
    console.error('Preview generation failed:', error);
    
    // Afficher message utilisateur
    showErrorNotification(
      error.message || 'Impossible de générer l\'aperçu'
    );
    
    return null;
  }
}
```

---

## 🔒 Sécurité

### Nonce AJAX
L'API récupère automatiquement le nonce depuis le DOM :

```html
<!-- Pour que ça marche, il faut ce nonce quelque part dans la page -->
<div id="pdf_builder_nonce" data-nonce="<?php wp_create_nonce('pdf_builder_nonce'); ?>"></div>
```

### Limitations
- ✅ Utilisateur doit avoir `manage_woocommerce` ou `edit_shop_orders`
- ✅ Nonce validation côté serveur
- ✅ Pas d'exposition de données sensibles (image PNG seulement)

---

## 📊 Cache

### Fonctionnement
- **Clé de cache** : `preview_{orderId}_{templateId}_{format}`
- **Stockage** : Map JavaScript en mémoire (client-side)
- **Durée** : Tant que la page reste ouverte
- **Invalidation** : Manuel via `clearCache()` ou page refresh

### Exemple
```typescript
// 1ère génération → appel AJAX
api.generatePreviewImage({ orderId: 42, templateId: 1 });

// 2ème génération → cache (instantané)
api.generatePreviewImage({ orderId: 42, templateId: 1 });

// Vider cache
api.clearCache();

// 3ème génération → appel AJAX à nouveau
api.generatePreviewImage({ orderId: 42, templateId: 1 });
```

---

## 🐛 Debugging

### Logs console
L'API loggue ses opérations :

```javascript
// Ouvrir console (F12)
// Vous verrez :
// [PreviewImageAPI] Image trouvée en cache
// [PreviewImageAPI] Image générée avec succès
// [PreviewImageAPI] Erreur: Order not found
```

### Inspection réseau
- Ouvrir DevTools → Network tab
- Chercher requête AJAX `admin-ajax.php?action=pdf_builder_preview_image`
- Vérifier paramètres POST (order_id, template_id, nonce)
- Vérifier réponse (doit être JSON avec `success: true`)

### Logs PHP
Si erreur backend :
```bash
# Vérifier wp_debug.log
tail -100 /path/to/wp-content/debug.log | grep "pdf_builder_preview"
```

---

## 📈 Performance

### Optimisations intégrées
- ✅ Cache client : évite AJAX réplicata
- ✅ Singleton pattern : une seule instance
- ✅ Async/await : pas de blocage interface
- ✅ Conversion TCPDF → PNG côté serveur

### Temps typiques
- 1ère génération : 500-2000ms (TCPDF + conversion)
- 2ème génération (cache) : < 1ms
- Affichage image : instant

### Optimisation côté serveur
Le backend PHP utilise :
- ✅ Imagick pour conversion PNG (fast)
- ✅ Cache transients WordPress (si disponible)
- ✅ Requêtes DB minimales

---

## 🔄 Mise à jour future

### Prévu
- [ ] Support téléchargement ZIP (multi-images)
- [ ] Support formats différents (PDF direct, JPEG)
- [ ] Webhook pour pré-générer aperçus batch
- [ ] CDN pour caching long-term

---

*Document créé 30 octobre 2025 - API v1.0.0*
