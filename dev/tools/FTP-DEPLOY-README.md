# 🚀 FTP DEPLOY - Script par Défaut
## Version Optimale pour WP PDF Builder Pro

Script de déploiement FTP complet et optimisé avec les fonctionnalités avancées suivantes :

### ✨ Fonctionnalités Principales

- **🔍 Détection intelligente** : Analyse automatique des fichiers modifiés via Git
- **⚡ Modes flexibles** : Séquentiel ou parallèle avec contrôle du nombre de jobs
- **🛡️ Gestion d'erreurs robuste** : Retry automatique avec backoff exponentiel
- **📊 Statistiques détaillées** : Taille, vitesse, types de fichiers
- **🔧 Options configurables** : Compilation, Git, force, etc.
- **📋 Validation des prérequis** : Vérification automatique de l'environnement

### 🎯 Usage Rapide

```powershell
# Déploiement ULTRA-RAPIDE (recommandé)
.\ftp-deploy-fast.ps1

# Déploiement rapide sans compilation
.\ftp-deploy-fast.ps1 -NoCompile

# Ancien script (mode avancé)
.\ftp-deploy-simple.ps1
```

### 📋 Paramètres Disponibles

| Paramètre | Description | Défaut |
|-----------|-------------|---------|
| `-Mode` | `Sequential` ou `Parallel` | `Sequential` |
| `-Force` | Forcer même sans changements | `false` |
| `-NoCompile` | Ignorer la compilation | `false` |
| `-NoGit` | Ignorer Git (commit/push) | `false` |
| `-MaxRetries` | Tentatives par fichier | `3` |
| `-ParallelJobs` | Jobs simultanés (mode Parallel) | `4` |

### 🔧 Configuration Requise

Créez le fichier `tools/ftp-config.env` :

```env
FTP_HOST=votre-serveur.com
FTP_USER=votre-utilisateur
FTP_PASS=votre-mot-de-passe
FTP_PATH=/wp-content/plugins/wp-pdf-builder-pro
```

### 📊 Métriques et Statistiques

Le script fournit des informations détaillées :
- Nombre de fichiers par type
- Taille totale transférée
- Vitesse de transfert moyenne
- Temps de déploiement
- Taux de succès/échec

### 🛠️ Dépannage

**Problème : Aucun fichier détecté**
```powershell
# Solution : forcer le déploiement
.\ftp-deploy-simple.ps1 -Force
```

**Problème : Compilation échoue**
```powershell
# Solution : ignorer la compilation
.\ftp-deploy-simple.ps1 -NoCompile
```

**Problème : Erreur Git**
```powershell
# Solution : ignorer Git
.\ftp-deploy-simple.ps1 -NoGit
```

### 🎯 Scénarios d'Usage

#### Développement Actif
```powershell
.\ftp-deploy-simple.ps1 -Mode Parallel
# Déploiement rapide pendant le développement
```

#### Déploiement de Production
```powershell
.\ftp-deploy-simple.ps1 -Force
# Déploiement complet avec vérifications
```

#### Test et Validation
```powershell
.\ftp-deploy-simple.ps1 -NoGit -NoCompile
# Test du processus sans modifier Git
```

### ⚡ Performances

- **ftp-deploy-fast.ps1** : Mode séquentiel ultra-optimisé (~4-5 min pour 50 fichiers)
- **ftp-deploy-simple.ps1** : Mode avancé avec options multiples (~8-10 min)

**Recommandation** : Utilisez `ftp-deploy-fast.ps1` pour un déploiement quotidien rapide.

### 🔒 Sécurité

- Validation des prérequis avant exécution
- Gestion sécurisée des credentials FTP
- Logs détaillés pour audit
- Pas de stockage des mots de passe en clair

---

**📝 Note** : Ce script est maintenant le script de déploiement par défaut recommandé pour tous les déploiements WP PDF Builder Pro.