# 🔧 PDF Builder Pro - Cache Clearing Guide

## ❌ Problème
L'erreur **"Unexpected token 'export'"** persiste malgré le déploiement. Cela signifie que le **cache serveur** n'a pas été vidé.

---

## ✅ Solutions (Par ordre de priorité)

### 🥇 Solution 1: Vider le cache navigateur (Immédiat)
```
Windows: Ctrl + Shift + R
Mac: Cmd + Shift + R
```
**Important**: Cela doit être fait sur chaque navigateur/onglet

---

### 🥈 Solution 2: Force cache busting (WordPress Admin)
Accédez à:
```
https://threeaxe.fr/wp-admin/admin.php?nocache=1
```

Puis rechargez l'éditeur PDF.

---

### 🥉 Solution 3: Vider le cache via SSH (Serveur)
Si vous avez accès SSH au serveur:

```bash
# 1. SSH sur le serveur
ssh user@threeaxe.fr

# 2. Vider OPcache
php -r "opcache_reset();"

# 3. Vider WordPress cache (si WP-CLI installé)
wp cache flush

# 4. Vider les fichiers cache
rm -rf /var/www/html/wp-content/cache/*
rm -rf /var/www/html/wp-content/uploads/cache/*
rm -rf /var/www/html/wp-content/object-cache.php

# 5. Redémarrer PHP-FPM (si applicable)
sudo systemctl restart php-fpm
```

---

### 4️⃣ Solution 4: Via wp-config.php
Ajoutez temporairement à `wp-config.php`:

```php
// Ajouter AVANT: /* That's all, stop editing! */

// Vider les caches
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Force sans cache
define('WP_CACHE', false);

// Vider OPcache si possible
if (function_exists('opcache_reset')) {
    opcache_reset();
}
```

**Après que cela fonctionne**, supprimez ces lignes.

---

### 5️⃣ Solution 5: Forcer une nouvelle version
Modifiez `AdminScriptLoader.php` ligne 315:

```php
// Avant:
wp_enqueue_script('pdf-builder-react', $react_script_url, ['pdf-builder-wrap'], $version_param, true);

// Après (ajoutez "&nocache=" . time()):
wp_enqueue_script('pdf-builder-react', $react_script_url . '?nocache=' . time(), ['pdf-builder-wrap'], $version_param, true);
```

---

## 🔍 Vérification

Après avoir vidé le cache, cherchez dans la console du navigateur (F12):

**❌ Avant** (avec l'erreur):
```
webpage_content_reporter.js:1 Uncaught SyntaxError: Unexpected token 'export'
```

**✅ Après** (sans l'erreur):
```
✅ PDF Builder React initialized successfully
```

---

## 📋 Checklist de Dépannage

- [ ] Vider cache navigateur: Ctrl+Shift+R
- [ ] Attendre 10 secondes et rechargez
- [ ] Vérifier F12 > Console (l'erreur devrait disparaître)
- [ ] Si toujours présent → Vider cache SSH
- [ ] Si toujours présent → Modifier wp-config.php
- [ ] Si toujours présent → Contacter l'hébergeur pour cache proxy

---

## 🆘 Si cela ne fonctionne toujours pas

1. **Contactez votre hébergeur** - Ils peuvent avoir un **cache proxy** (Cloudflare, etc.)
2. **Demandez-leur de vider le cache** pour `pdf-builder-react.js`
3. **Vérifiez via FTP** que le fichier a été uploadé correctement:
   ```
   Fichier: wp-content/plugins/wp-pdf-builder-pro/resources/assets/js/dist/pdf-builder-react.js
   Taille: ~568 KB (pas ~541 KB)
   Date: 14/01/2026 19:15 ou plus récent
   ```

---

## 📞 Détails Techniques

**Fichier problématique**: `resources/assets/js/dist/pdf-builder-react.js`
- **Ancien**: ~541 KB (webpack minifié)
- **Nouveau**: ~568 KB (Babel transpilé) ✅
- **Timestamp**: 2026-01-14 18:55:51

**Erreur qui disparaît**:
```
Uncaught SyntaxError: Unexpected token 'export' (at webpage_content_reporter.js:1:115558)
```

Ce message disparaîtra une fois le cache vidé et la nouvelle version chargée.
