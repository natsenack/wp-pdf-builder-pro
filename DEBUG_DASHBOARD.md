# 🐛 Debug Dashboard - Boutons non-fonctionnels

## ✅ Vérifications effectuées

1. **✅ Fichiers déployés** : Tous les fichiers sont identiques en source et destination
   - `dashboard-page.php` : Hash MD5 identique
   - `AdminPageRenderer.php` : Hash MD5 identique  
   - `dashboard-css.min.css` : Hash MD5 identique (14,6 KB)

2. **✅ Code des boutons** : Les liens sont corrects
   - Créer PDF → `admin.php?page=pdf-builder-react-editor`
   - Templates → `admin.php?page=pdf-builder-templates`
   - Paramètres → `admin.php?page=pdf-builder-settings`

3. **✅ Pages WordPress** : Toutes les pages sont enregistrées
   - `pdf-builder-react-editor` → méthode `reactEditorPage()` existe
   - `pdf-builder-templates` → méthode `templatesPage()` existe
   - `pdf-builder-settings` → méthode `settings_page()` existe

4. **✅ CSS** : Aucun CSS bloquant les clics (`pointer-events` uniquement sur pseudo-éléments)

## 🔍 Étapes de diagnostic à essayer

### 1. Vider le cache WordPress

Si vous avez un plugin de cache actif (WP Rocket, W3 Total Cache, etc.) :
```
1. Allez dans le menu du plugin de cache
2. Cliquez sur "Vider tout le cache"
3. Rechargez la page du dashboard
```

### 2. Actualiser sans cache navigateur

```
Ctrl + Shift + R (ou Cmd + Shift + R sur Mac)
```

Cela force le navigateur à télécharger les nouveaux fichiers CSS/JS.

### 3. Vérifier dans la console du navigateur

```
1. Ouvrez la page du dashboard
2. Appuyez sur F12 pour ouvrir les DevTools
3. Allez dans l'onglet "Console"
4. Recherchez des erreurs JavaScript (en rouge)
5. Cliquez sur un bouton et observez s'il y a des erreurs
```

### 4. Tester le lien direct

Testez ces URLs directement dans votre navigateur :
```
http://votre-site.local/wp-admin/admin.php?page=pdf-builder-react-editor
http://votre-site.local/wp-admin/admin.php?page=pdf-builder-templates
http://votre-site.local/wp-admin/admin.php?page=pdf-builder-settings
```

Si ces URLs ouvrent les bonnes pages, le problème vient du clic sur les boutons.
Si ces URLs donnent une erreur, le problème vient de l'enregistrement des pages.

### 5. Vérifier les permissions

Les pages nécessitent la capability `manage_options`. Vérifiez que votre utilisateur :
```
1. Est administrateur
2. A accès au menu "PDF Builder Pro" dans la barre latérale
```

### 6. Désactiver temporairement d'autres plugins

Parfois, un plugin de sécurité ou d'optimisation peut bloquer les clics :
```
1. Allez dans "Extensions" > "Extensions installées"
2. Désactivez tous les plugins sauf "PDF Builder Pro"
3. Testez à nouveau les boutons
4. Réactivez les plugins un par un pour identifier le coupable
```

## 🔧 Test de diagnostic automatique

Vous pouvez ajouter ce code temporairement dans `dashboard-page.php` (ligne 23, juste après `<div class="wrap">`) :

```php
<!-- DEBUG INFO -->
<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;">
    <h3>🔧 Diagnostic</h3>
    <ul>
        <li><strong>Premium status:</strong> <?php echo $is_premium ? '✅ Premium actif' : '❌ Non-premium'; ?></li>
        <li><strong>Lien Créer PDF:</strong> <code><?php echo admin_url('admin.php?page=pdf-builder-react-editor'); ?></code></li>
        <li><strong>Lien Templates:</strong> <code><?php echo admin_url('admin.php?page=pdf-builder-templates'); ?></code></li>
        <li><strong>Lien Paramètres:</strong> <code><?php echo admin_url('admin.php?page=pdf-builder-settings'); ?></code></li>
        <li><strong>CSS chargé:</strong> <code>dashboard-css.min.css (<?php echo file_exists(PDF_BUILDER_PLUGIN_DIR . 'assets/css/dashboard-css.min.css') ? filesize(PDF_BUILDER_PLUGIN_DIR . 'assets/css/dashboard-css.min.css') . ' octets' : 'NON TROUVÉ'; ?>)</code></li>
    </ul>
</div>
<!-- FIN DEBUG -->
```

Cela affichera :
- Le statut premium
- Les URLs exactes générées
- La taille du fichier CSS

## 🎯 Résolution probable

**Cause la plus probable : Cache navigateur ou WordPress**

Solution :
1. Ctrl + Shift + R pour vider le cache
2. Vider le cache WordPress si plugin actif
3. Si les boutons fonctionnent après, c'était juste le cache !

**Deuxième cause probable : Conflit JavaScript**

Solution :
1. Ouvrir F12 > Console
2. Chercher erreurs JavaScript
3. Désactiver autres plugins pour tester

## 📝 Informations techniques

- **Taille CSS**: 14 980 octets (14,6 KB)
- **Version**: v1.0.1.1
- **Fichiers modifiés**: 
  - `dashboard-page.php` (364 lignes)
  - `AdminPageRenderer.php` (50 lignes)
  - `dashboard.css` (983 lignes)

---

**Dernière mise à jour**: <?php echo date('Y-m-d H:i:s'); ?>
