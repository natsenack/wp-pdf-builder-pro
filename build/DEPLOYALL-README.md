# Script de Déploiement Complet - deployall.ps1

## 📋 Résumé

Le script `deployall.ps1` automatise complètement le déploiement du plugin WordPress PDF Builder Pro sur le serveur distant. Il effectue :

1. **Compilation** des assets (npm run build)
2. **Gestion Git** : commit et push avant/après déploiement
3. **Collecte des fichiers** depuis le dossier `plugin/` (203+ fichiers)
4. **Transfert FTP asynchrone** : uploads parallèles rapides et fiables
5. **Gestion des erreurs** : retry automatique et logs détaillés
6. **Barre de progression** pour toutes les étapes

---

## 🚀 Utilisation

### Mode Standard (6 uploads simultanés)
```powershell
.\build\deployall.ps1
```

### Mode Rapide (10 uploads simultanés)
```powershell
.\build\deployall.ps1 -FastMode
```

### Mode Simulation (sans transfert FTP réel)
```powershell
.\build\deployall.ps1 -DryRun
```

### Sans Test de Connexion FTP
```powershell
.\build\deployall.ps1 -SkipConnectionTest
```

### Combinaisons Possibles
```powershell
# Test rapide sans connexion
.\build\deployall.ps1 -DryRun -SkipConnectionTest

# Déploiement ultra-rapide
.\build\deployall.ps1 -FastMode -SkipConnectionTest
```

---

## ✅ Fonctionnalités

### 1. Compilation
- Lance `npm run build` automatiquement
- Compile les assets dans les bons dossiers
- Continue même si compilation échoue (avec avertissement)

### 2. Gestion Git
- **Avant déploiement** : Détecte changements → Commit → Push
- **Après déploiement** : Commit "deploy: ..." → Push
- Gère les cas où rien n'a changé

### 3. Collecte des Fichiers
- Récupère **TOUS** les fichiers du dossier `plugin/`
- **Ignore automatiquement** :
  - `node_modules/`
  - `.git/`
  - `build/`, `logs/`
  - Fichiers temporaires (`*.tmp`, `*.bak`, etc.)
  - Fichiers système (`.DS_Store`, `Thumbs.db`)

### 4. Transfert FTP Asynchrone
- **Mode passif** : Optimisé pour les pare-feu
- **Uploads parallèles** : 
  - 6 simultanés (mode normal)
  - 10 simultanés (mode -FastMode)
- **Retry automatique** : 3 tentatives par fichier
- **Performance** : ~0.05-0.1 MB/s par connexion
- **Barre de progression** : % complété, vitesse, temps restant

### 5. Gestion d'Erreurs
- Retry automatique (3x) pour échecs temporaires
- Logs détaillés des erreurs 550 (fichier existant)
- Continue même si certains fichiers échouent
- Résumé final avec nombre de succès/erreurs

---

## 📊 Résultat Attendu

```
🎉 DEPLOIEMENT TERMINE !
============================================================
📊 RESUME DETAILLE:
   • Compilation: ✅ Reussie
   • Collecte fichiers: ✅ 203 fichiers (10.25 MB)
   • FTP Upload: ✅ 200/203 fichiers (10.15 MB)
   • Git: ✅ OK
   • Duree totale: 45 secondes
   • Timestamp: 2026-01-03 11:49:04

✨ DEPLOIEMENT REUSSI !
```

---

## ⚙️ Configuration

Modifiables dans le script (lignes 14-18) :
```powershell
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"
$WorkingDir = "I:\wp-pdf-builder-pro"
```

---

## 🔧 Paramètres

| Paramètre | Valeur | Description |
|-----------|--------|-------------|
| `-Mode` | `plugin` (défaut) | Mode plugin uniquement |
| | `full` | Mode complet (futur) |
| `-DryRun` | - | Simulation sans transfert FTP |
| `-FastMode` | - | 10 uploads simultanés au lieu de 6 |
| `-SkipConnectionTest` | - | Ignore le test FTP initial |

---

## 📈 Performance

Pour 203 fichiers (10.25 MB) :
- **Compilation** : ~5-8 secondes
- **Git** : ~2-3 secondes
- **Transfert FTP** : ~20-30 secondes (mode normal)
- **Transfert FTP** : ~15-20 secondes (mode -FastMode)
- **Total** : ~30-45 secondes

---

## 🛠️ Dépannage

### Erreur : "550 Fichier non disponible"
- Signifie que le répertoire n'existe pas sur le serveur
- Le script crée les répertoires automatiquement
- Si le problème persiste, vérifier la connexion FTP

### Erreur : "Timeout"
- La connexion FTP s'est interrompue
- Le script réessaye automatiquement (3x)
- Utiliser `-FastMode` pour moins de connexions simultanées

### Erreur : "Compilation échouée"
- Le script continue malgré l'erreur
- Les fichiers JS compilés ne seront pas à jour
- Vérifier les logs npm : `npm run build`

### Erreur : "Push échoué"
- Vérifier la connexion internet
- Vérifier les credentials Git
- Vérifier que la branche `dev` existe : `git branch`

---

## 📝 Logs et Fichiers

Logs FTP : `build/logs/deployment-*.log.json`
Backups : `build/backups/`
Fichiers temporaires : `build/`

---

## 🎯 Cas d'Usage

```powershell
# Première utilisation (test)
.\build\deployall.ps1 -DryRun

# Déploiement production
.\build\deployall.ps1

# Déploiement urgent
.\build\deployall.ps1 -FastMode

# Déploiement sans internet (mode offline)
.\build\deployall.ps1 -DryRun -SkipConnectionTest
```

---

## 📚 Informations Complémentaires

- **Créé** : 3 janvier 2026
- **Auteur** : Système de déploiement automatisé
- **Compatibilité** : PowerShell 5.1+, Windows 10/11
- **Dépendances** : npm, git, accès FTP
