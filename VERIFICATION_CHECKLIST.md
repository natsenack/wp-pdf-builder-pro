# ✅ CHECKLIST DE VÉRIFICATION POST-CORRECTION

## 🎯 Vérification du Double Enqueue

### Phase 1 : Vérification des Logs PHP
- [ ] Accéder à `wp-content/debug.log` sur le serveur
- [ ] Chercher `[PHP] enqueue_admin_scripts appelée`
- [ ] ✅ Vérifier qu'il n'apparaît QU'UNE SEULE FOIS (pas 2)
- [ ] ✅ Vérifier qu'il n'y a pas d'erreur PHP

### Phase 2 : Vérification des Logs JavaScript
- [ ] Ouvrir l'inspecteur : F12 → Onglet "Console"
- [ ] Aller à : wp-admin/admin.php?page=pdf-builder-editor
- [ ] Rafraîchir la page (F5)
- [ ] ✅ Chercher : `[TEMPLATE EDITOR PAGE LOADED]` (doit apparaître 1 fois)
- [ ] ✅ Chercher : `pdf-builder-admin.js` (doit aparaître 1 fois)
- [ ] ✅ Chercher : `PDFBuilderPro` ou `pdfBuilderPro` (doit être défini)
- [ ] ✅ Vérifier que jQuery est chargé
- [ ] ✅ Pas d'erreurs rouges dans la console

### Phase 3 : Vérification des Variables Globales
```javascript
// Dans la console (F12), taper et vérifier :
typeof pdfBuilderAjax       // ✅ Doit être 'object'
typeof pdfBuilderPro        // ✅ Doit être 'object' ou 'undefined' (valide)
typeof jQuery               // ✅ Doit être 'function'
```

## 🎨 Vérification du Drag & Drop

### Avant Drag & Drop
- [ ] Ouvrir : wp-admin/admin.php?page=pdf-builder-editor
- [ ] Ouvrir la Console : F12 → Console
- [ ] **Ne pas faire d'action** → vérifier pas d'erreur

### Pendant Drag & Drop
1. **Chercher l'élément "Text"** dans la bibliothèque à gauche
2. **Glisser-déposer** sur le canvas blanc
3. ✅ **Vérifier dans la console** :
   - Logs d'événement drag (si logging activé)
   - Pas d'erreur rouge
   - Pas de "duplicate handle" warning
   - L'élément doit apparaître sur le canvas

### Après Drag & Drop
- [ ] ✅ L'élément doit être visible sur le canvas
- [ ] ✅ L'élément doit être sélectionnable
- [ ] ✅ Pas de message d'erreur en rouge

## 🔍 Vérification de la Structure de Chargement

### Vérifier qu'il n'y a qu'UN enqueue
```bash
# Terminal du serveur:
grep -n "wp_enqueue_script.*pdf-builder" plugin/src/Admin/PDF_Builder_Admin.php

# Résultat attendu (1 seul ligne):
1490:        wp_enqueue_script('pdf-builder-vanilla-bundle', $script_url, ...
```

### Vérifier que PDF_Builder_Core ne charge plus les scripts
```bash
# Terminal du serveur:
grep -n "wp_enqueue_script" plugin/src/Core/PDF_Builder_Core.php | head -5

# Résultat attendu: AUCUNE ligne (méthode vide)
# Or au maximum les utilisation dans optimize_script_tags
```

## 📊 Performance

### Vérifier la Performance
- [ ] F12 → Onglet "Network"
- [ ] Rafraîchir (F5)
- [ ] Chercher `pdf-builder-admin.js`
- [ ] ✅ Doit y avoir UN SEUL fichier (pas 2)
- [ ] ✅ Vérifier le size en KB (environ 169 KB compressé)
- [ ] ✅ Status doit être 200 (pas 304 redirect)

### Vérifier les Dépendances de Chargement
- [ ] jquery.js → doit charger avant pdf-builder-admin.js
- [ ] Pas de circular dependency

## 🎯 Tests Fonctionnels Complets

### Test 1 : Création d'Élément
- [ ] Glisser "Text" → ✅ Élément créé
- [ ] Glisser "Image" → ✅ Élément créé
- [ ] Glisser "Rect" → ✅ Élément créé

### Test 2 : Manipulation d'Éléments
- [ ] Sélectionner un élément → ✅ Bordure visible
- [ ] Déplacer l'élément → ✅ Glisse correctement
- [ ] Redimensionner → ✅ Fonctionne
- [ ] Supprimer → ✅ Fonctionne

### Test 3 : Sauvegarde
- [ ] Modifier un template → ✅ Sauvegarde automatique
- [ ] Vérifier les logs AJAX → ✅ Pas d'erreur

## 🚨 Problèmes Possibles et Solutions

### Si vous voyez toujours 2 logs d'enqueue :
1. **Vider le cache** :
   - Vider cache navigateur (Ctrl+Shift+Delete)
   - Vider cache WordPress (Admin → Diagnostic)
   - Videz le transient : `wp transient delete pdf_builder_*`

2. **Vérifier le Git** :
   - Vérifier que le déploiement FTP a bien eu lieu
   - Vérifier `plugin/src/Core/PDF_Builder_Core.php` ligne 200

### Si les logs JavaScript n'apparaissent pas :
1. Vérifier que le fichier `pdf-builder-admin.js` existe sur le serveur
2. Vérifier les permssions du fichier (755)
3. Vérifier qu'il n'y a pas d'erreur 404

### Si le drag & drop ne fonctionne pas :
1. Vérifier que `pdfBuilderPro` existe dans la console
2. Vérifier qu'il n'y a pas d'erreur JavaScript
3. Vérifier les permissions WordPress

## ✨ Succès Confirmé Quand :

1. ✅ **Un seul enqueue** du script admin
2. ✅ **Pas de duplicate handle warning**
3. ✅ **Logs JavaScript visibles** (une seule fois)
4. ✅ **Drag & drop fonctionne**
5. ✅ **Pas d'erreurs rouges** dans la console
6. ✅ **Performance correcte** (pas de double chargement)

---

## 📝 Notes

- Date de correction : 26 Octobre 2025
- Fichiers modifiés : 2 (PDF_Builder_Core.php, PDF_Builder_Admin.php)
- Déploiement : FTP (471 fichiers, 32.09 MB)
- Version déployée : v1.0.0-deploy-20251026-183315
