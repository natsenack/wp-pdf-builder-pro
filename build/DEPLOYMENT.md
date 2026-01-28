# 🚀 Déploiement PDF Builder Pro

## Script unifié : `build\deploy-simple.ps1`

### 📋 Modes disponibles

#### 🧪 **Mode Test** (recommandé en premier)
```powershell
.\build\deploy-simple.ps1 -Mode test
```
- **Étape 1** : Analyse des fichiers à déployer
- **Étape 2** : Simulation (aucun transfert)
- **Sécurisé** : Aucun risque de modifier le serveur

#### 📦 **Mode Plugin** (production)
```powershell
.\build\deploy-simple.ps1 -all
```
- **Étape 1** : Compilation des assets JavaScript/CSS
- **Étape 1.5** : Vérification/création des dossiers distants
- **Étape 2** : Transfert FTP des fichiers (467 fichiers, ~32 MB)
- **Étape 3** : Push Git (tag de version)
- **Destination** : `/wp-content/plugins/wp-pdf-builder-pro/`

##### Options avancées :
```powershell
# Synchronisation complète (tous les fichiers)
.\build\deploy-simple.ps1 -all -includevendor





---
**� Emplacement** : `build\deploy-simple.ps1` et `build/DEPLOYMENT.md`