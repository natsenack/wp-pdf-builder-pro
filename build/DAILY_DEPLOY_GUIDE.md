# Guide d'utilisation du déploiement quotidien

## Vue d'ensemble
Le mode déploiement quotidien (`-DailyDeploy`) combine automatiquement trois étapes en une seule commande :
1. **Diagnostic système** - Vérification complète de l'état du projet
2. **Auto-correction** - Correction automatique des erreurs détectées (si nécessaire)
3. **Déploiement** - Déploiement automatique du plugin

## Utilisation simple

### Déploiement quotidien standard
```powershell
.\deploy.ps1 -DailyDeploy
```
- Mode par défaut : `plugin` (déploie seulement le dossier plugin/)
- Diagnostic automatique + auto-correction + déploiement

### Déploiement quotidien complet (mode développement)
```powershell
.\deploy.ps1 -DailyDeploy -Mode full
```
- Déploie tout le projet vers `/wp-content/plugins/wp-pdf-builder-pro-dev`
- Utile pour les environnements de développement

## Avantages

### 🤖 Automatisation complète
- Aucune intervention manuelle requise
- Idéal pour les déploiements quotidiens réguliers
- Parfait pour l'intégration CI/CD

### 🛡️ Sécurité intégrée
- Diagnostic préalable de 22 points de contrôle
- Auto-correction des erreurs communes
- Arrêt automatique si les problèmes sont trop graves

### ⚡ Performance optimisée
- Transferts FTP parallèles (10 connexions simultanées)
- Synchronisation intelligente (seulement les fichiers modifiés)
- Logs détaillés pour le suivi

## Flux de fonctionnement

```
Démarrage → Diagnostic → Auto-correction (si nécessaire) → Déploiement → Terminé
     ↓            ↓                ↓                        ↓          ↓
  Activation   22 tests        Correction auto           Transfert    Succès
  du mode      système         des erreurs              parallèle
```

## Gestion des erreurs

### Diagnostic réussi
- Passage direct au déploiement
- Aucune correction nécessaire

### Diagnostic avec erreurs corrigibles
- Application automatique des corrections
- Continuation du déploiement si correction réussie

### Diagnostic avec erreurs critiques
- Arrêt du processus
- Message d'erreur détaillé
- Correction manuelle requise

## Exemples de sortie

### Cas normal (diagnostic réussi)
```
📅 MODE DÉPLOIEMENT QUOTIDIEN ACTIVÉ
🔍 ÉTAPE 1/3 : DIAGNOSTIC SYSTÈME
✅ DIAGNOSTIC RÉUSSI - Passage direct au déploiement
🚀 ÉTAPE 3/3 : DÉPLOIEMENT
```

### Cas avec auto-correction
```
📅 MODE DÉPLOIEMENT QUOTIDIEN ACTIVÉ
🔍 ÉTAPE 1/3 : DIAGNOSTIC SYSTÈME
❌ DIAGNOSTIC ÉCHOUÉ - Tentative de correction automatique...
🔧 ÉTAPE 2/3 : AUTO-CORRECTION
✅ AUTO-CORRECTION RÉUSSIE - Continuation du déploiement
🚀 ÉTAPE 3/3 : DÉPLOIEMENT
```

## Logs et suivi

Tous les déploiements quotidiens génèrent des logs détaillés dans :
- `build/logs/deployment-YYYYMMDD-HHMMSS.log`
- Sauvegardes automatiques dans `build/backups/`

## Recommandations

- **Utilisez `-DailyDeploy`** pour tous les déploiements quotidiens
- **Vérifiez les logs** après chaque déploiement
- **Surveillez les avertissements** du diagnostic
- **Testez d'abord** avec `-Mode test` si vous modifiez le script

## Commandes alternatives

Si vous préférez plus de contrôle :
```powershell
# Diagnostic seul
.\deploy.ps1 -Diagnostic

# Diagnostic + auto-correction
.\deploy.ps1 -Diagnostic -AutoFix

# Déploiement manuel
.\deploy.ps1 -Mode plugin
```