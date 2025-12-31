# Système de Cache Unifié - PDF Builder Pro

## Vue d'ensemble

Le plugin PDF Builder Pro dispose désormais d'un **système de cache centralisé** qui unifie tous les mécanismes de cache précédemment dispersés. Ce système fournit une interface unique pour gérer :

- ✅ Cache WordPress transients
- ✅ Cache d'assets (JS/CSS/Images)
- ✅ Cache AJAX côté client/serveur
- ✅ Cache d'images du canvas
- ✅ Cache des aperçus

## Architecture

### Classe Principale : `PDF_Builder_Cache_Manager`

Située dans `plugin/src/Managers/PDF_Builder_Cache_Manager.php`, cette classe Singleton gère tous les types de cache.

#### Types de Cache Supportés

1. **`transient`** - Cache WordPress standard (base de données)
2. **`object`** - Cache d'objets (WP_Object_Cache)
3. **`file`** - Cache fichier (sécurisé)
4. **`memory`** - Cache en mémoire PHP

### Préfixes de Cache

Chaque type utilise des préfixes spécifiques :
```php
const PREFIXES = [
    'transient' => 'pdf_builder_cache_',
    'ajax' => 'pdf_builder_ajax_',
    'asset' => 'pdf_builder_asset_',
    'image' => 'pdf_builder_image_',
    'preview' => 'pdf_builder_preview_',
    'rate_limit' => 'pdf_builder_rate_limit_'
];
```

## Utilisation

### Méthodes Publiques

#### Cache de Base
```php
use PDF_Builder\Managers\PDF_Builder_Cache_Manager;

// Instance
$cache = PDF_Builder_Cache_Manager::getInstance();

// Définir une valeur
$cache->set('ma_cle', 'ma_valeur', 'transient', 3600);

// Récupérer une valeur
$valeur = $cache->get('ma_cle', 'transient');

// Supprimer une valeur
$cache->delete('ma_cle', 'transient');

// Vérifier l'existence
if ($cache->exists('ma_cle', 'transient')) {
    // Existe
}

// Vider un type de cache
$cache->clear('transient'); // ou null pour tout vider
```

#### Cache Spécialisé

##### Assets
```php
// Mettre en cache un asset optimisé
$cache->setAssetCache('style.css', $css_content, 'css', 3600);

// Récupérer un asset
$css = $cache->getAssetCache('style.css', 'css');

// Optimiser et mettre en cache automatiquement
$optimized_css = $cache->optimizeAndCacheAsset('style.css', $original_css, 'css');
```

##### AJAX
```php
// Mettre en cache une réponse AJAX
$cache->setAjaxCache('get_users', ['page' => 1], $response_data, 300);

// Récupérer du cache AJAX
$cached_response = $cache->getAjaxCache('get_users', ['page' => 1]);
```

##### Images
```php
// Mettre en cache une image
$cache->setImageCache('logo.png', $image_data, ['width' => 200, 'height' => 100]);

// Récupérer une image
$image = $cache->getImageCache('logo.png');
```

#### Raccourcis Statiques
```php
use PDF_Builder\Managers\PDF_Builder_Cache_Manager as Cache;

// Utilisation directe
Cache::setCache('cle', 'valeur');
Cache::getCache('cle');
Cache::deleteCache('cle');
Cache::clearCache(); // Tout vider
```

## Configuration

### Paramètres WordPress

Tous les paramètres sont stockés dans `wp_options` sous la clé `pdf_builder_settings` :

```php
// Cache général
'pdf_builder_cache_enabled' => true,           // Cache activé globalement
'pdf_builder_cache_debug' => false,            // Mode debug
'pdf_builder_cache_stats' => true,             // Collecter des stats
'pdf_builder_cache_ttl' => 3600,               // TTL par défaut (1h)
'pdf_builder_cache_max_size' => 100,           // Taille max (MB)
'pdf_builder_cache_compression' => true,       // Compression activée

// Cache transients
'pdf_builder_cache_transient_enabled' => true,
'pdf_builder_cache_transient_prefix' => 'pdf_builder_cache_',

// Cache assets
'pdf_builder_asset_cache_enabled' => true,
'pdf_builder_asset_compression' => true,
'pdf_builder_asset_minify' => true,

// Cache AJAX
'pdf_builder_ajax_cache_enabled' => true,
'pdf_builder_ajax_cache_ttl' => 300,

// Cache images
'pdf_builder_image_cache_enabled' => true,
'pdf_builder_image_max_memory' => 256,

// Cache aperçus
'pdf_builder_preview_cache_enabled' => true,
'pdf_builder_preview_cache_max_items' => 50,

// Nettoyage
'pdf_builder_cache_auto_cleanup' => true,
'pdf_builder_cache_cleanup_interval' => 86400,
```

### Interface d'Administration

Un nouvel onglet **"Cache"** a été ajouté dans les paramètres du plugin (`wp-admin/admin.php?page=pdf-builder-settings&tab=cache`) avec :

- 📊 **Statistiques en temps réel** (hits, misses, taux de succès)
- ⚙️ **Configuration générale** du cache
- 🗃️ **Paramètres transients**
- 📦 **Configuration assets**
- 🌐 **Réglages AJAX**
- 🖼️ **Paramètres images**
- 👁️ **Configuration aperçus**
- 🔍 **Outils de débogage**

## Cache Côté Client

### JavaScript - AjaxCompat

Le système JavaScript utilise maintenant le cache centralisé :

```javascript
// Vérification automatique du cache serveur
const cached = await AjaxCompat.getServerCache('action', {param: 'value'});
if (cached) {
    return cached; // Utilise le cache
}

// Sauvegarde automatique en cache
await AjaxCompat.setServerCache('action', {param: 'value'}, response, 300);
```

### Canvas Images

Le cache d'images du canvas (`Canvas.tsx`) reste optimisé avec :
- LRU (Least Recently Used) automatique
- Gestion mémoire (256MB par défaut)
- Nettoyage intelligent
- Debug tools via `window.canvasMemoryDebug`

## Actions AJAX

### Nouvelles Actions Disponibles

```php
// Vider le cache
wp_ajax_pdf_builder_clear_cache

// Statistiques du cache
wp_ajax_pdf_builder_cache_stats

// Cache AJAX côté serveur
wp_ajax_pdf_builder_get_ajax_cache
wp_ajax_pdf_builder_set_ajax_cache

// Statut du cache
wp_ajax_pdf_builder_cache_status
wp_ajax_nopriv_pdf_builder_cache_status
```

### Utilisation JavaScript

```javascript
// Vider tout le cache
$.post(ajaxurl, {
    action: 'pdf_builder_clear_cache',
    nonce: pdfBuilderNonce
});

// Obtenir les statistiques
$.post(ajaxurl, {
    action: 'pdf_builder_cache_stats',
    nonce: pdfBuilderNonce
}, function(response) {
    console.log('Stats cache:', response.data.stats);
});
```

## Nettoyage Automatique

### Tâches Planifiées

Le système programme automatiquement :
- Nettoyage quotidien des caches expirés
- Maintenance hebdomadaire si configuré

### Nettoyage Manuel

```php
// Via PHP
$cache_manager = PDF_Builder_Cache_Manager::getInstance();
$cache_manager->cleanupExpiredCache();

// Via AJAX (interface admin)
$.post(ajaxurl, {
    action: 'pdf_builder_clear_cache',
    cache_type: 'all', // ou 'transient', 'asset', etc.
    nonce: pdfBuilderNonce
});
```

## Debugging

### Mode Debug

Activez le mode debug pour des logs détaillés :
```php
'pdf_builder_cache_debug' => true
```

### Statistiques

Consultez les statistiques en temps réel dans l'onglet Cache de l'admin.

### Outils de Développement

```javascript
// Console développeur
pdfBuilderCheckJSCache()    // Vérifier cache JS
pdfBuilderCheckCSS()        // Vérifier cache CSS
canvasMemoryDebug.getCacheStats()  // Stats mémoire canvas
```

## Migration depuis l'ancien système

### Code existant

L'ancien code utilisant des transients directement continue de fonctionner :

```php
// Avant (toujours valide)
set_transient('pdf_builder_old_key', 'value', 3600);
get_transient('pdf_builder_old_key');

// Nouveau (recommandé)
PDF_Builder_Cache_Manager::setCache('new_key', 'value', 'transient', 3600);
PDF_Builder_Cache_Manager::getCache('new_key', 'transient');
```

### Assets

L'Asset Optimizer utilise maintenant automatiquement le cache centralisé :

```php
// Automatique via CacheManager
$this->cache_manager->optimizeAndCacheAsset($filename, $content, $type);
```

## Performance

### Optimisations

- **Cache multi-niveaux** : Mémoire → Fichier → Base de données
- **Compression automatique** : GZIP pour assets volumineux
- **Minification** : Réduction taille JS/CSS
- **LRU intelligent** : Élimination des données peu utilisées

### Métriques

Le système collecte automatiquement :
- Nombre de hits/misses
- Taux de succès du cache
- Utilisation mémoire
- Nombre d'éléments en cache

## Sécurité

### Mesures de Sécurité

- **Préfixes uniques** pour éviter les conflits
- **Validation stricte** des clés et valeurs
- **Échappement automatique** des données
- **Droits d'accès** vérifiés pour toutes les actions AJAX
- **Nettoyage automatique** des données expirées

### Répertoire de Cache

Le cache fichier est stocké dans :
```
wp-content/uploads/pdf-builder-cache/
```

Avec protection `.htaccess` :
```
Deny from all
```

## Dépannage

### Problèmes Courants

#### Cache qui ne se vide pas
```php
// Forcer le vidage complet
PDF_Builder_Cache_Manager::clearCache();
```

#### Mémoire pleine
Vérifiez la configuration :
```php
'pdf_builder_cache_max_size' => 50, // Réduire à 50MB
'pdf_builder_image_max_memory' => 128, // Réduire à 128MB
```

#### Cache corrompu
```javascript
// Interface admin : bouton "Vider tout le cache"
pdfBuilderEmergencyFix() // JavaScript fallback
```

### Logs

Les logs détaillés sont disponibles dans :
- PHP : `wp-content/debug.log` (si WP_DEBUG activé)
- JavaScript : Console développeur

## API Complète

### Méthodes de CacheManager

```php
public function set($key, $value, $type = 'transient', $ttl = null)
public function get($key, $type = 'transient')
public function delete($key, $type = 'transient')
public function clear($type = null)
public function exists($key, $type = 'transient')
public function getStats()

// Spécialisées
public function setAssetCache($filename, $content, $type = 'css', $ttl = null)
public function getAssetCache($filename, $type = 'css')
public function setAjaxCache($action, $data, $result, $ttl = null)
public function getAjaxCache($action, $data)
public function setImageCache($url, $image_data, $metadata = [])
public function getImageCache($url)
```

### Constantes

```php
PDF_Builder_Cache_Manager::PREFIXES
PDF_Builder_Cache_Manager::CACHE_TYPES
```

---

## Résumé

Le système de cache unifié apporte :

✅ **Centrale de gestion** : Une seule classe pour tout gérer
✅ **Performance optimisée** : Cache multi-niveaux intelligent
✅ **Configuration flexible** : Interface admin complète
✅ **Sécurité renforcée** : Validation et nettoyage automatique
✅ **Compatibilité** : Migration douce depuis l'ancien système
✅ **Monitoring** : Statistiques et débogage avancés

Le cache est désormais **entièrement configurable** via l'interface admin et **transparent** pour les développeurs ! 🚀
