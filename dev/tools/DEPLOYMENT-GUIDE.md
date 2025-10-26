# 🚀 GUIDE DES SCRIPTS DE DÉPLOIEMENT
# ===================================

**📅 Dernière mise à jour : 21 octobre 2025**
**🎯 Script par défaut : `ftp-deploy-simple.ps1`**

## 📊 COMPARAISON DES SOLUTIONS

| Méthode | Vitesse | Complexité | Avantages | Inconvénients |
|---------|---------|------------|-----------|---------------|
| **FTP Optimisé (ftp-deploy-simple.ps1)** | ⚡ 0.33-3 f/s | 🟢 Simple | **RECOMMANDÉ** - Fonctionne partout, features complètes | Limité par serveur |
| **FTP Parallèle (ftp-deploy-simple.ps1 -Mode Parallel)** | 🚀 1-5 f/s | 🟢 Simple | Ultra-rapide, retry automatique | Consommation réseau |
| **SSH Posh-SSH (ssh-deploy-posh.ps1)** | 🏃 5+ f/s | 🟡 Moyen | Sécurisé, très rapide | Installation requise |
| **WinSCP (winscp-deploy.ps1)** | 🐌 2-10 f/s | 🔴 Complexe | Interface graphique | Installation lourde |

## 🎯 RECOMMANDATIONS (MISES À JOUR 2025)

### ✅ Pour TOUS les déploiements (recommandé) :
```powershell
# Script par défaut - fonctionnalités complètes
.\ftp-deploy-simple.ps1

# Mode ultra-rapide pour les gros déploiements
.\ftp-deploy-simple.ps1 -Mode Parallel -ParallelJobs 8
```

### Pour l'automatisation avancée :
```powershell
# Quand SSH est disponible
.\ssh-deploy-posh.ps1
```

### Solution de secours uniquement :
```powershell
# Interface graphique (installation requise)
.\winscp-deploy.ps1
```

## 🔧 INSTALLATION REQUISE

### ✅ Rien pour FTP (script par défaut) :
- PowerShell 5+ (préinstallé)
- Git, npm (pour compilation)
- Fichier `ftp-config.env`

### Pour SSH Posh-SSH :
```powershell
Install-Module -Name Posh-SSH -Scope CurrentUser -Force
```

### Pour WinSCP (non recommandé) :
- Téléchargez : https://winscp.net/
- Installation lourde (éviter si possible)

## 📝 UTILISATION DÉTAILLÉE

### ✅ 1. FTP Optimisé - SCRIPT PAR DÉFAUT (Recommandé)
```powershell
# Déploiement standard complet (compilation + Git + déploiement)
.\ftp-deploy-simple.ps1

# Mode parallèle ultra-rapide (3-5x plus rapide)
.\ftp-deploy-simple.ps1 -Mode Parallel -ParallelJobs 8

# Déploiement sans compilation (pour tests rapides)
.\ftp-deploy-simple.ps1 -NoCompile

# Forcer le déploiement même sans changements détectés
.\ftp-deploy-simple.ps1 -Force

# Test du processus (sans modifier Git)
.\ftp-deploy-simple.ps1 -NoGit -NoCompile

# Aide complète intégrée
Get-Help .\ftp-deploy-simple.ps1 -Full
```

**Paramètres avancés :**
- `-Mode Sequential/Parallel` : Mode de transfert
- `-Force` : Ignore la détection de changements
- `-NoCompile` : Saute la compilation npm
- `-NoGit` : Saute commit/push Git
- `-MaxRetries N` : Tentatives par fichier (défaut: 3)
- `-ParallelJobs N` : Jobs simultanés (défaut: 4)

### 2. SSH Posh-SSH (Alternative rapide)
```powershell
# Déploiement ultra-rapide si SSH disponible
.\ssh-deploy-posh.ps1

# Paramètres disponibles :
# -MaxParallel : Connexions parallèles (défaut 4)
# -DryRun : Simulation sans transfert
# -Delete : Supprime fichiers inexistants
```

## 🔐 SÉCURITÉ ET CONFIGURATION

### ✅ Configuration recommandée (2025) :
Le fichier `tools/ftp-config.env` doit contenir :
```env
FTP_HOST=votre-serveur.com
FTP_USER=votre-utilisateur
FTP_PASS=votre-mot-de-passe
FTP_PATH=/wp-content/plugins/wp-pdf-builder-pro
```

### ⚠️ Sécurité renforcée :
- **NE JAMAIS commiter** `ftp-config.env` (déjà dans `.gitignore`)
- **Utilisez des variables d'environnement** pour les credentials sensibles :
```powershell
# Pour la session actuelle
$env:FTP_PASS = "votre-mot-de-passe-sécurisé"

# Pour rendre permanent (ajouter à $PROFILE)
[Environment]::SetEnvironmentVariable("FTP_PASS", "votre-mot-de-passe", "User")
```

### 🔑 Alternative interactive :
Si `FTP_PASS` n'est pas défini, le script demande le mot de passe de manière sécurisée (masqué à l'écran).

## 📊 PERFORMANCES ATTENDUES (2025)

| Méthode | Vitesse | Avantages | Usage recommandé |
|---------|---------|-----------|------------------|
| **FTP Séquentiel** | 0.33 f/s | Fiable, faible charge | Production stable |
| **FTP Parallèle** | 1-5 f/s | 🚀 Ultra-rapide | Développement actif |
| **SSH Posh-SSH** | 5+ f/s | Sécurisé, rapide | Quand SSH disponible |
| **WinSCP** | 2-10 f/s | Interface graphique | Éviter si possible |

## ✨ NOUVELLES FONCTIONNALITÉS (v2025)

### 🎯 Script par défaut amélioré :
- **Détection intelligente** : Analyse Git + métriques détaillées
- **Modes flexibles** : Séquentiel/Parallèle avec contrôle fin
- **Retry automatique** : Backoff exponentiel, gestion robuste d'erreurs
- **Options avancées** : -Force, -NoCompile, -NoGit, etc.
- **Validation complète** : Prérequis, syntaxe, sécurité
- **Documentation intégrée** : `Get-Help` PowerShell complète

### 📈 Métriques avancées :
- Taille totale transférée
- Vitesse de transfert moyenne
- Statistiques par type de fichier
- Taux de succès/échec
- Temps de déploiement détaillé

## 🔍 DIAGNOSTIC ET TESTS

### Tester la connectivité :
```powershell
# Test FTP
Test-NetConnection -ComputerName $env:FTP_HOST -Port 21

# Test du script (mode test)
.\ftp-deploy-simple.ps1 -NoCompile -NoGit
```

### Validation de configuration :
```powershell
# Vérifier les prérequis
.\ftp-deploy-simple.ps1 -NoCompile -NoGit -Force
```

## 🚨 DÉPANNAGE

### "Aucun fichier à déployer" :
```powershell
# Forcer le déploiement
.\ftp-deploy-simple.ps1 -Force
```

### Erreur de compilation :
```powershell
# Ignorer la compilation
.\ftp-deploy-simple.ps1 -NoCompile
```

### Problème Git :
```powershell
# Ignorer Git
.\ftp-deploy-simple.ps1 -NoGit
```

### Erreur Posh-SSH :
```powershell
Install-Module -Name Posh-SSH -Scope CurrentUser -Force
```

## 🔄 VERSIONNAGE AUTOMATIQUE

Le script par défaut effectue automatiquement :
- `npm run build` (compilation)
- `git add . && git commit -m "Déploiement auto - [date]"`
- `git push origin dev`
- Upload FTP optimisé

*Options disponibles pour désactiver : `-NoCompile`, `-NoGit`*

## ⚠️ RECOMMANDATIONS FINALES (2025)

1. **✅ Utilisez TOUJOURS** `.\ftp-deploy-simple.ps1` (script par défaut)
2. **🚀 Activez le mode parallèle** pour les déploiements rapides
3. **🧪 Testez d'abord** avec `-NoGit -NoCompile`
4. **📊 Surveillez les métriques** affichées automatiquement
5. **🔄 Videz le cache WordPress** après déploiement
6. **🛡️ Gardez `ftp-config.env`** hors de Git (déjà ignoré)

---

**🎉 Le script `ftp-deploy-simple.ps1` est maintenant l'outil de déploiement universel recommandé !**

*Mis à jour automatiquement - 21 octobre 2025*