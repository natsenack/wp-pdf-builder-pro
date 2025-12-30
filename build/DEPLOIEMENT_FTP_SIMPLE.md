# Déploiement FTP Simple - PDF Builder Pro

## Description
Script ultra-simple pour déployer **uniquement les fichiers modifiés** (détectés via git diff) du plugin PDF Builder Pro vers un serveur FTP.

## Utilisation

### Déploiement complet
```powershell
.\deploy-ftp-simple.ps1
```

### Options disponibles

#### Mode test (liste les fichiers sans déployer)
```powershell
.\deploy-ftp-simple.ps1 -TestMode
```

#### Mode rapide (plus de connexions simultanées)
```powershell
.\deploy-ftp-simple.ps1 -FastMode
```

#### Sauter le test de connexion
```powershell
.\deploy-ftp-simple.ps1 -SkipConnectionTest
```

#### Combinaisons
```powershell
# Mode rapide sans test de connexion
.\deploy-ftp-simple.ps1 -FastMode -SkipConnectionTest

# Test avant déploiement rapide
.\deploy-ftp-simple.ps1 -TestMode -FastMode
```

## Configuration FTP
Le script utilise ces paramètres (modifiables dans le script) :
- **Serveur**: 65.108.242.181
- **Utilisateur**: nats
- **Mot de passe**: iZ6vU3zV2y
- **Chemin distant**: /wp-content/plugins/wp-pdf-builder-pro

## Fonctionnalités

### ✅ Détection intelligente des fichiers modifiés
- Utilise `git diff` pour détecter seulement les fichiers modifiés depuis le dernier commit
- Évite l'upload massif de tous les fichiers du plugin
- Déploiement rapide et ciblé

### ✅ Upload parallèle
- 4 connexions simultanées en mode normal
- 8 connexions simultanées en mode rapide
- Upload automatique des répertoires nécessaires

### ✅ Gestion des erreurs
- Retry automatique (3 tentatives par fichier)
- Timeout adapté selon le mode
- Rapport détaillé des erreurs

### ✅ Optimisation
- Mode binaire pour les assets (images, JS, CSS)
- Mode texte pour PHP/HTML/JSON (évite la corruption)
- Création récursive des répertoires

### ✅ Sécurité
- Gestion des caractères spéciaux
- Encodage UTF-8
- Nettoyage automatique des jobs

## Différence avec le déploiement complet

### Avant (version originale)
- Déployait **tous les fichiers** du plugin (434 fichiers)
- Temps d'upload : plusieurs minutes
- Consommait beaucoup de bande passante

### Maintenant (version optimisée)
- Déploie **seulement les fichiers modifiés** (1-5 fichiers typiquement)
- Temps d'upload : quelques secondes
- Économie de bande passante significative

## Scripts alternatifs

### Déploiement intelligent avec Git (recommandé pour développement)
```powershell
.\deploy-simple.ps1
```
- Détecte automatiquement les fichiers modifiés via Git
- Upload sélectif
- Gestion complète Git (commit/push/tag)

### Déploiement complet (si nécessaire)
```powershell
.\deploy-all.ps1
```
- Upload tous les fichiers du projet
- Plus lent mais complet
- Utile pour déploiements initiaux

## Dépannage

### Erreur de connexion
```powershell
# Vérifier la connexion FTP manuellement
Test-NetConnection 65.108.242.181 -Port 21
```

### Timeout
- Utiliser `-FastMode` pour réduire les timeouts
- Vérifier la connexion réseau

### Erreurs de fichiers
- Les erreurs sur les fichiers binaires sont normales
- Le script continue avec les autres fichiers

## Logs
Les logs sont sauvegardés dans `build/logs/` avec le format :
- `deployment-YYYYMMDD-HHMMSS.log`
- `deployment-YYYYMMDD-HHMMSS.log.json`
