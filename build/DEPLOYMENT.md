# 🚀 Déploiement PDF Builder Pro

## Script unifié : `build\deploy.ps1`

### 📋 Modes disponibles

#### 🧪 **Mode Test** (recommandé en premier)
```powershell
.\build\deploy.ps1 -Mode test
```
- **Étape 1** : Analyse des fichiers à déployer
- **Étape 2** : Simulation (aucun transfert)
- **Sécurisé** : Aucun risque de modifier le serveur

#### 📦 **Mode Plugin** (production)
```powershell
.\build\deploy.ps1 -Mode plugin
```
- **Étape 1** : Compilation des assets JavaScript/CSS
- **Étape 1.5** : Vérification/création des dossiers distants
- **Étape 2** : Transfert FTP des fichiers (467 fichiers, ~32 MB)
- **Étape 3** : Push Git (tag de version)
- **Destination** : `/wp-content/plugins/wp-pdf-builder-pro/`

##### Options avancées :
```powershell
# Synchronisation complète (tous les fichiers)
.\build\deploy.ps1 -Mode plugin -FullSync

# Mode forcé (écrase tout)
.\build\deploy.ps1 -Mode plugin -Force
```

#### 🔧 **Mode Full** (développement)
```powershell
.\build\deploy.ps1 -Mode full
```
- **Étape 1** : Transfert FTP de tout le projet
- **Étape 2** : Push Git (tag de développement)
- **Destination** : `/wp-content/plugins/wp-pdf-builder-pro-dev/`

### 🔄 **Options de synchronisation**

| Option | Description | Quand l'utiliser |
|--------|-------------|------------------|
| `-FullSync` | Envoie tous les fichiers | Problèmes de synchro, première installation |
| `-Force` | Écrase tous les fichiers | Corrections majeures, reset complet |
| *(défaut)* | Synchronisation intelligente | Déploiements normaux |

### ⚠️ Sécurité
- **Testez toujours** avec `-Mode test` en premier
- **Confirmation requise** pour les modes `plugin` et `full`
- **Backup recommandé** avant utilisation de `-Force`
- **Vérification FTP** automatique avant transfert

### 📊 Ce qui est déployé

| Mode | Contenu | Destination | Usage |
|------|---------|-------------|-------|
| `test` | Analyse seulement | - | Préparation |
| `plugin` | Dossier `plugin/` uniquement | `/wp-content/plugins/wp-pdf-builder-pro/` | Production |
| `full` | Projet complet (filtrage) | `/wp-content/plugins/wp-pdf-builder-pro-dev/` | Développement |

### 🎯 Workflow recommandé
1. **Test** : `.\build\deploy.ps1 -Mode test`
2. **Vérification** : Contrôler la liste des fichiers
3. **Déploiement** : `.\build\deploy.ps1 -Mode plugin`
   - _Étape 1 : Compilation automatique des assets_
   - _Étape 2 : Transfert FTP avec barre de progression_
   - _Étape 3 : Push Git automatique_

---
**� Emplacement** : `build/deploy.ps1` et `build/DEPLOYMENT.md`