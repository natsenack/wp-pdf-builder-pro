# Déploiement PDF Builder Pro

## Script de déploiement

### `.\tools\ftp-deploy-fixed.ps1`
Déploie les fichiers via FTP de manière optimisée :
1. **Filtre automatiquement** les fichiers de production uniquement
2. **Déploie via FTP** avec connexions parallèles
3. **Archive automatiquement** les déploiements dans `archive/`

## Configuration FTP

Configurez vos paramètres FTP dans `tools/ftp-config.env` :

```env
FTP_HOST=votre-serveur-ftp.com
FTP_USER=votre-username
FTP_PASSWORD=votre-mot-de-passe
```

## Utilisation

```powershell
# Déploiement simple
.\tools\ftp-deploy-fixed.ps1

# Avec plus de connexions parallèles
.\tools\ftp-deploy-fixed.ps1 -MaxConcurrent 15
```
Éditez le fichier `ftp-config.env` :
```
FTP_HOST=ftp.votresite.com
FTP_USER=votre_username
FTP_PASSWORD=votre_mot_de_passe
FTP_PATH=/wp-content/plugins/wp-pdf-builder-pro
```

### Option 3 : Paramètres en ligne de commande
```powershell
.\ftp-deploy-optimized.ps1 -FtpHost "ftp.votresite.com" -FtpUser "username" -FtpPassword "password" -FtpPath "/wp-content/plugins/wp-pdf-builder-pro"
```

## Workflow de déploiement

```bash
# 1. Compiler et préparer les fichiers
npm run deploy:prepare

# 2. Déployer via FTP (crée automatiquement une archive locale)
.\ftp-deploy-optimized.ps1
```

## Fichiers générés

### Après `.\ftp-deploy-optimized.ps1` :
- `archive/backup-wp-pdf-builder-pro-YYYY-MM-DD-HHMMSS.zip` - Archive locale des fichiers avant déploiement

## Structure du plugin déployé

```
wp-pdf-builder-pro/
├── assets/
├── dist/
├── languages/
├── *.php
└── *.md
```

## Archivage automatique

Le script FTP crée automatiquement une archive locale dans `archive/` avant chaque déploiement, permettant de :
- Garder un historique des versions déployées
- Restaurer rapidement une version précédente si nécessaire
- Traçabilité des changements déployés
- Sécurité en cas de problème post-déploiement

### Avantages de l'archivage local :
- ✅ **Restauration rapide** : Archive complète prête à être extraite
- ✅ **Historique complet** : Chaque déploiement = une archive
- ✅ **Stockage local** : Pas d'encombrement sur le serveur
- ✅ **Filtrage intelligent** : Même exclusions que le déploiement FTP
- ✅ **Nommage automatique** : Timestamp précis pour traçabilité

### Restauration depuis une archive :
```powershell
# Lister les archives disponibles
Get-ChildItem archive -Filter "*.zip" | Sort-Object LastWriteTime -Descending

# Restaurer une archive spécifique
Expand-Archive -Path "archive\backup-wp-pdf-builder-pro-2025-10-07-143052.zip" -DestinationPath ".\restored-version"
```

### Nettoyage automatique des archives :
```powershell
# Nettoyer automatiquement (garde les 10 dernières)
Get-ChildItem archive -Filter "*.zip" | Sort-Object LastWriteTime -Descending | Select-Object -Skip 10 | Remove-Item
```

## 🎯 Avantages du système

- **🛡️ Sécurité maximale** : Archive automatique avant chaque déploiement
- **⚡ Restauration instantanée** : Rollback en quelques secondes
- **📊 Traçabilité complète** : Historique de tous les déploiements
- **💾 Gestion optimisée** : Nettoyage automatique des anciennes archives
- **🚀 Performance** : Archives locales, pas d'impact sur le serveur
- **🎯 Simplicité** : Scripts automatisés, pas d'action manuelle requise