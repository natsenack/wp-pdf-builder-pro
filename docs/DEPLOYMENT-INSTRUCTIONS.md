# 📤 INSTRUCTIONS DE DÉPLOIEMENT FTP

**Date:** 19 octobre 2025  
**Status:** ✅ Prêt pour déploiement  

---

## 🎯 Fichiers à Déployer

### Priorité 1 (CRITIQUE)
```
Fichier source: src/Managers/PDF_Builder_Template_Manager.php
Taille: 22.6 KB
Destination FTP: /wp-content/plugins/wp-pdf-builder-pro/src/Managers/
Checksum: À vérifier après upload
```

### Priorité 2 (Support)
```
Fichier source: src/Managers/PDF_Builder_WooCommerce_Integration.php
Destination FTP: /wp-content/plugins/wp-pdf-builder-pro/src/Managers/
```

### Priorité 3 (Bootstrap)
```
Fichier source: bootstrap.php
Destination FTP: /wp-content/plugins/wp-pdf-builder-pro/
```

---

## 🔐 Données de Connexion FTP

**À UTILISER VIA:**
- Panel d'hébergement (Hestia, cPanel, Plesk, etc.)
- Filezilla ou autre client FTP
- WinSCP
- Command line (curl, sftp, etc.)

**NE PAS PARTAGER** ces credentials en dehors de ce fichier local !

---

## 📋 Checklist Avant Déploiement

- [ ] Sauvegarder la BD complète
- [ ] Vérifier que WordPress fonctionne
- [ ] Tester localement: `php tools/test-validation.php` → 100% ✅
- [ ] Scanner templates: `php tools/validate-existing-templates.php` → >= 80% ✅
- [ ] Lire la documentation: `docs/CHECKLIST-POST-DEPLOYMENT.md`

---

## 🚀 Étapes de Déploiement

### 1. Via Panel d'Hébergement (Recommandé - Plus Simple)

1. Connectez-vous au panel (Hestia, cPanel, etc.)
2. Ouvrir le gestionnaire de fichiers
3. Naviguer à `/wp-content/plugins/wp-pdf-builder-pro/src/Managers/`
4. Uploader `PDF_Builder_Template_Manager.php`
5. Naviguer à `/wp-content/plugins/wp-pdf-builder-pro/`
6. Uploader `bootstrap.php`

### 2. Via Filezilla

1. Ouvrir Filezilla
2. Fichier → Gestionnaire de sites
3. Remplir les coordonnées FTP
4. Connecter
5. Naviguer à destination
6. Drag & drop les fichiers

### 3. Via Command Line (Linux/Mac)

```bash
sftp -P PORT user@host
cd /wp-content/plugins/wp-pdf-builder-pro/src/Managers/
put src/Managers/PDF_Builder_Template_Manager.php
cd ..
put bootstrap.php
```

---

## ✅ Vérification Post-Déploiement

### 1. Vérifier les fichiers sont en place
```bash
# Via SSH
ls -lh /var/www/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php

# Devrait afficher:
# -rw-r--r-- 1 www-data www-data 22.6K Oct 19 12:00 PDF_Builder_Template_Manager.php
```

### 2. Vérifier les permissions
```bash
# Via SSH
stat /var/www/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php

# Permission devrait être: 644 ou 755
```

### 3. Tester l'accès
```bash
# Aller à: https://votresite.com/wp-admin/

# Devrait fonctionner normalement
```

### 4. Tester la sauvegarde
1. Aller à PDF Builder → Templates
2. Créer/Modifier un template
3. Cliquer "Sauvegarder"
4. ✅ Voir un message de succès

### 5. Vérifier les logs
```bash
# Via SSH
tail -f /var/www/wp-content/debug.log | grep "PDF Builder"

# Résultat attendu:
# [PDF Builder] Template Save - ✅ Permissions vérifiées pour user ID 1
# [PDF Builder] Template Save - ✅ Nonce valide
# [PDF Builder] Template Save - ✅ SUCCÈS: Template ID=123 sauvegardé
```

---

## 🆘 En Cas de Problème

### Erreur 403 (Permission Denied)

**Cause:** Permissions FTP incorrectes

**Solution:**
1. Vérifier les permissions du fichier (644 ou 755)
2. Vérifier que l'utilisateur FTP a les droits d'écriture
3. Contacter l'hébergeur

### Erreur 550 (File not found)

**Cause:** Chemin FTP incorrect ou mauvaise structure

**Solution:**
1. Vérifier le chemin exact: `/wp-content/plugins/wp-pdf-builder-pro/`
2. Vérifier la structure existe
3. Créer les dossiers si nécessaire

### WordPress affiche erreur

**Cause:** PHP syntax error ou fichier corrompu

**Solution:**
1. Vérifier la taille du fichier uploadé
2. Re-uploader le fichier
3. Vérifier les logs PHP (`error_log`)
4. Rollback via Git si nécessaire

### Les logs ne s'affichent pas

**Cause:** WP_DEBUG non activé

**Solution:**
1. SSH: `nano wp-config.php`
2. Ajouter/Vérifier:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
3. Sauvegarder et rafraîchir

---

## 📊 Fichiers de Référence

Avant de déployer, consulter:
- `docs/CHECKLIST-POST-DEPLOYMENT.md` - Checklist détaillée
- `docs/GUIDE-CONSULTER-LOGS.md` - Comment lire les logs
- `docs/IMPROVEMENTS-VALIDATION-LOGGING.md` - Détails techniques
- `tools/validate-existing-templates.php` - Validation des templates

---

## 🎯 Résumé

| Étape | Action | Statut |
|-------|--------|--------|
| 1 | Backup DB | À faire |
| 2 | Upload PDF_Builder_Template_Manager.php | À faire |
| 3 | Upload bootstrap.php | À faire |
| 4 | Vérifier permissions | À faire |
| 5 | Tester sauvegarde | À faire |
| 6 | Vérifier logs | À faire |
| 7 | Monitorer 24-48h | À faire |

---

**Status:** 🟡 EN ATTENTE DE DÉPLOIEMENT  
**Fichiers:** Prêts ✅  
**Documentation:** Complète ✅  
**Rollback:** Disponible via Git ✅

🚀 **Prêt à déployer dès que les credentials FTP sont configurés!**
