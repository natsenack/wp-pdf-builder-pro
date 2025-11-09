# 🔧 CACHE DÉSACTIVÉ - PDF Builder Pro

## ⚠️ ATTENTION : Cache complètement désactivé

Le cache du plugin PDF Builder Pro a été **complètement désactivé** pour permettre les tests et le développement sans interférence du cache.

## 📋 Modifications effectuées

### 1. Headers de cache modifiés
- **Fichier modifié** : `plugin/pdf-builder-pro.php`
- **Fonction** : `pdf_builder_add_asset_cache_headers()`
- **Changement** : Vérifie l'option `cache_enabled` avant d'ajouter des headers de cache
- **Comportement** : Si cache désactivé → headers `no-cache`, sinon cache normal

### 2. Paramètres du plugin
- **Option** : `cache_enabled` = `false`
- **TTL** : `cache_ttl` = `0`
- **Transients supprimés** :
  - `pdf_builder_cache`
  - `pdf_builder_templates`
  - `pdf_builder_elements`

### 3. Cache WordPress vidé
- Fonction `wp_cache_flush()` exécutée
- Cache des posts nettoyé

## 🎯 Impact sur les performances

**⚠️ ATTENTION** : Avec le cache désactivé :
- ❌ Les assets JavaScript/CSS se rechargent à chaque requête
- ❌ Aucun cache des templates ou éléments
- ❌ Headers `Cache-Control: no-cache` envoyés
- ✅ Modifications visibles immédiatement
- ✅ Idéal pour le développement et tests

## 🔄 Comment réactiver le cache

### Option 1 : Via l'interface admin
1. Aller dans **WP Admin** → **PDF Builder** → **Paramètres**
2. Cocher **"Cache activé"**
3. Définir un **TTL du cache** (ex: 3600 secondes = 1 heure)
4. Sauvegarder

### Option 2 : Via code
```php
// Dans functions.php ou un plugin custom
$settings = get_option('pdf_builder_settings', []);
$settings['cache_enabled'] = true;
$settings['cache_ttl'] = 3600; // 1 heure
update_option('pdf_builder_settings', $settings);
```

### Option 3 : Script de réactivation
Créer un fichier `reactivate-cache.php` dans `/plugin/` :
```php
<?php
$settings = get_option('pdf_builder_settings', []);
$settings['cache_enabled'] = true;
$settings['cache_ttl'] = 3600;
update_option('pdf_builder_settings', $settings);
echo "Cache réactivé";
```

## 🧪 Tests à effectuer

Après avoir vidé le cache du navigateur (Ctrl+F5) :

1. **✅ Sélection au premier clic** : Vérifier que les éléments se sélectionnent au premier clic
2. **✅ Assets à jour** : Vérifier que les modifications JavaScript sont visibles immédiatement
3. **✅ Templates** : Vérifier que les changements de templates sont visibles sans délai
4. **✅ Éléments** : Vérifier que les modifications d'éléments sont visibles immédiatement

## 📁 Fichiers modifiés

- `plugin/pdf-builder-pro.php` - Headers de cache conditionnels
- `plugin/disable-cache.php` - Script de désactivation
- `plugin/disable-cache.ps1` - Script PowerShell d'exécution

## 🗑️ Nettoyage (optionnel)

Une fois les tests terminés, supprimer les fichiers temporaires :
- `plugin/disable-cache.php`
- `plugin/disable-cache.ps1`
- Ce fichier README

---
**Date de désactivation** : 9 novembre 2025
**Raison** : Tests de sélection au premier clic + développement