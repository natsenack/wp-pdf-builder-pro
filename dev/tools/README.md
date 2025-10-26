# 🛠️ Scripts de Validation et Déploiement

**PDF Builder Pro - Outils de Production**

Date: 19 octobre 2025  
Version: 1.0

---

## � Scripts Disponibles

### 1. `validate-existing-templates.php` ✨ NOUVEAU

**Objectif:** Scanner tous les templates existants en BD et vérifier s'ils passeront la nouvelle validation.

**Usage:**
```bash
php validate-existing-templates.php
```

**Que fait-il:**
- ✅ Décide le JSON de chaque template
- ✅ Applique les 6 étapes de validation
- ✅ Vérifie chaque élément
- ✅ Génère un rapport détaillé

**Output:**
```
✅ Template ID=1 ('Facture Standard')
   → Éléments: 15
   → Dimensions: 595x842

❌ Template ID=5 ('Vieux Template')
   → Propriété obligatoire manquante: 'version'
```

### 2. `test-validation.php` ✨ NOUVEAU

**Objectif:** Tester unitairement la logique de validation avec des cas de test.

**Usage:**
```bash
php test-validation.php
```

**Cas de test couverts:**
- ✅ Template valide complet
- ✅ Propriété obligatoire manquante
- ✅ Type d'élément invalide
- ✅ Couleur en format invalide
- ✅ Dimensions hors limites

**Output:**
```
Test 1: Template VALIDE
✅ PASSÉ - Template valide accepté

Total tests: 5
✅ Réussis: 5
📈 Taux de réussite: 100%
```

---

## � Scripts FTP

### 3. `ftp-create-folder.ps1` ✨ NOUVEAU

**Objectif:** Créer un sous-dossier sur le serveur FTP distant.

**Usage:**
```powershell
.\ftp-create-folder.ps1
```

**Que fait-il:**
- ✅ Charge la configuration FTP depuis `ftp-config.env`
- ✅ Demande le nom du sous-dossier à créer
- ✅ Crée le dossier sur le serveur distant
- ✅ Vérifie que la création a réussi

**Exemple d'utilisation:**
```
Entrez le nom du sous-dossier à créer: backup-2025-10-20
Sous-dossier à créer: /wp-content/plugins/wp-pdf-builder-pro/backup-2025-10-20
✅ SUCCÈS: Le sous-dossier 'backup-2025-10-20' a été créé avec succès !
```

**Prérequis:**
- Fichier `ftp-config.env` configuré avec les bonnes credentials
- Connexion réseau vers le serveur FTP

### 4. `ftp-list-folders.ps1` ✨ NOUVEAU

**Objectif:** Lister tous les dossiers et fichiers présents sur le serveur FTP distant.

**Usage:**
```powershell
.\ftp-list-folders.ps1
```

**Que fait-il:**
- ✅ Se connecte au serveur FTP
- ✅ Liste tous les dossiers et fichiers
- ✅ Affiche un résumé du contenu
- ✅ Utile pour vérifier l'état du déploiement

**Exemple d'output:**
```
📁 DOSSIERS (13):
   📁 assets, config, core, database, languages, lib, resources, src, templates, test...

📄 FICHIERS (3):
   📄 bootstrap.php, pdf-builder-pro.php, README.md

📊 RÉSUMÉ:
   • Dossiers: 13
   • Fichiers: 3
```

**Prérequis:**
- Fichier `ftp-config.env` configuré avec les bonnes credentials
- Connexion réseau vers le serveur FTP

### 4. `ftp-delete-folder.ps1` ✨ NOUVEAU

**Objectif:** Supprimer un sous-dossier du serveur FTP distant (avec confirmation).

**Usage:**
```powershell
.\ftp-delete-folder.ps1
```

**Que fait-il:**
- ✅ Liste tous les dossiers existants
- ✅ Demande confirmation avant suppression
- ✅ Supprime le dossier sélectionné
- ✅ Vérifie que la suppression a réussi

**Sécurité:**
- ⚠️ Demande une confirmation explicite "oui"
- ✅ Vérifie que le dossier existe avant suppression
- ✅ Affiche un avertissement si le dossier n'est pas vide

**Exemple d'utilisation:**
```
Dossiers disponibles:
   1. assets
   2. test
   3. backup-2025-10-20

Entrez le nom du dossier à supprimer: test
⚠️  ATTENTION: Cette action est IRRÉVERSIBLE. Confirmer ? (oui/non): oui
✅ SUCCÈS: Le dossier 'test' a été supprimé avec succès !
```

---

## � Scripts de Déploiement FTP

### 5. `ftp-deploy-fixed.ps1` ✨ VERSION CORRIGÉE

**Objectif:** Déploiement FTP complet avec gestion automatique des dossiers.

**Différences avec la version simple:**
- ✅ **Création automatique des dossiers** sur le serveur avant upload
- ✅ **Gestion d'erreurs améliorée** avec détails des problèmes
- ✅ **Compilation optionnelle** (demande confirmation)
- ✅ **Upload séquentiel** pour éviter les conflits
- ✅ **Vérification des dossiers** avant chaque upload

**Usage:**
```powershell
.\ftp-deploy-fixed.ps1
```

**Que fait-il:**
- ✅ **Compile automatiquement** le projet (obligatoire)
- ✅ Détecte les fichiers modifiés via Git
- ✅ **CRÉE LES DOSSIERS MANQUANTS** sur le serveur FTP
- ✅ Upload les fichiers avec gestion d'erreurs
- ✅ Commit et push Git automatique

**Résolution du problème:**
```
❌ AVANT: Erreur upload src/Managers/File.php (dossier inexistant)
✅ APRÈS: Création automatique de src/Managers/ puis upload réussi
```

**Exemple d'output corrigé:**
```
Uploading src/Managers/PDF_Builder_Template_Manager.php...
  📁 Créé: /wp-content/plugins/wp-pdf-builder-pro/src
  📁 Créé: /wp-content/plugins/wp-pdf-builder-pro/src/Managers
  ✅ OK src/Managers/PDF_Builder_Template_Manager.php
```

### 6. `ftp-test-folders.ps1` 🧪 SCRIPT DE TEST

**Objectif:** Tester la logique de création de dossiers FTP sans déployer tout le projet.

**Usage:**
```powershell
.\ftp-test-folders.ps1
```

**Que fait-il:**
- ✅ Teste la création récursive de dossiers
- ✅ Upload un fichier de test
- ✅ Vérifie que tout fonctionne
- ✅ Nettoie automatiquement

**Utile pour:**
- Vérifier la connexion FTP
- Tester la logique avant déploiement complet
- Déboguer les problèmes de dossiers

---

## �🚀 Workflow de Déploiement Recommandé

### Phase 1: Pré-Déploiement (Avant FTP)

```bash
# 1. Tester la validation localement
php tools/test-validation.php
# Résultat attendu: 100% ✅

# 2. Scanner les templates existants
php tools/validate-existing-templates.php
# Résultat attendu: >= 80% valides
```

### Phase 2: Déploiement FTP

```bash
# 3. Déployer les fichiers:
#    - src/Managers/PDF_Builder_Template_Manager.php
#    - src/Managers/PDF_Builder_WooCommerce_Integration.php
#    - bootstrap.php
```

### Phase 3: Post-Déploiement

```bash
# 4. Monitorer les logs (24-48h)
tail -f wp-content/debug.log | grep "PDF Builder"
```

---

## 📊 Résultats Attendus

### Au Test Local
```
Total tests: 5
✅ Réussis: 5
Taux de réussite: 100%
```
**Status:** ✅ PRÊT POUR PRODUCTION

### Au Scan des Templates
```
✅ Valides: 8 / 10
❌ Invalides: 2 / 10
Taux de réussite: 80%
```

| Taux | Recommandation |
|------|----------------|
| 100% | ✅ Déployer immédiatement |
| >= 95% | ✅ Déployer (corriger les templates après) |
| >= 80% | ⚠️ Déployer (monitorer et corriger) |
| < 80% | 🚨 Corriger avant déploiement |

---

## � Scripts de Déploiement FTP

### 3. `ftp-deploy-simple.ps1` ✅ **VERSION PAR DÉFAUT CORRIGÉE**

**Objectif:** Déploiement FTP complet avec création automatique des dossiers (maintenant corrigé).

**Fonctionnalités (version corrigée):**
- ✅ **Compilation automatique et obligatoire** avant déploiement
- ✅ **Création automatique des dossiers** sur le serveur avant upload
- ✅ **Gestion d'erreurs améliorée** avec détails des problèmes
- ✅ **Upload séquentiel** pour éviter les conflits
- ✅ **Vérification des dossiers** avant chaque upload

**Usage:**
```powershell
.\ftp-deploy-simple.ps1
```

**Résolution du problème corrigé:**
```
❌ AVANT: Erreur upload src/Managers/File.php (dossier inexistant)
✅ APRÈS: Création automatique de src/Managers/ puis upload réussi
```

**Nouveau comportement:**
```
Uploading src/Managers/PDF_Builder_Template_Manager.php...
  📁 Créé: /wp-content/plugins/wp-pdf-builder-pro/src
  📁 Créé: /wp-content/plugins/wp-pdf-builder-pro/src/Managers
  ✅ OK src/Managers/PDF_Builder_Template_Manager.php
```

### 4. `ftp-deploy-fixed.ps1` ✨ VERSION ALTERNATIVE

**Objectif:** Version alternative avec les mêmes corrections (conservée pour compatibilité).

### 5. `ftp-create-folder.ps1` 📁 CRÉATION DE DOSSIERS

**Objectif:** Créer un sous-dossier spécifique sur le serveur FTP.

### 6. `ftp-list-folders.ps1` 📋 LISTE DU CONTENU

**Objectif:** Lister tous les dossiers et fichiers présents sur le serveur FTP.

### 7. `ftp-delete-folder.ps1` 🗑️ SUPPRESSION DE DOSSIERS

**Objectif:** Supprimer un dossier du serveur FTP (avec confirmation).

### 8. `ftp-test-folders.ps1` 🧪 SCRIPT DE TEST

**Objectif:** Tester la logique de création de dossiers FTP sans déployer tout le projet.

---

## �📝 Notes

Ces outils ont été réorganisés depuis les dossiers `dev-tools/` et `build-tools-alt/` originaux pour une meilleure structure de projet.