# Organisation des fichiers CSS - PDF Builder Pro

## Structure actuelle

### 📁 `plugin/resources/assets/css/`
Dossier principal contenant tous les fichiers CSS utilisés par l'application.

#### Fichiers principaux :
- **`admin-global.css`** - Styles globaux et utilitaires pour l'administration WordPress
  - Messages d'état, boutons d'action, sections pliables
  - Indicateurs de chargement, tooltips, métriques, logs
  - Styles responsives et tableau de bord

- **`settings-tabs.css`** - Styles spécifiques aux onglets de navigation
  - Design moderne des onglets WordPress
  - Animations et effets visuels

- **`developer-settings.css`** - Styles pour les paramètres développeur
  - Onglet "Développeur" : contrôles développeur, outils, logs, modales
  - Bannière de statut, grille de contrôles, section outils développeur

- **`system-settings.css`** - Styles spécifiques à l'onglet "Système"
  - Cache & Performance : sections, métriques, boutons système
  - Indicateurs de statut, animations, design responsive

- **`notifications.css`** - Styles du système de notifications
  - Toasts, alertes, messages utilisateur

#### Fichiers spécialisés :
- **`pdf-builder-admin.css`** - Styles pour les modales d'aperçu PDF
- **`pdf-builder-react.css`** - Styles pour l'éditeur React (généré automatiquement)
- **`gdpr.css`** - Styles pour les fonctionnalités GDPR
- **`onboarding.css`** - Styles pour l'assistant de configuration
- **`wizard.css`** - Styles pour les assistants pas à pas
- **`editor.css`** - Styles pour l'éditeur intégré
- **`predefined-templates.css`** - Styles pour la gestion des templates

## Chargement des CSS

Les fichiers CSS sont chargés via `AdminScriptLoader.php` :
- `admin-global.css` : Toujours chargé
- `settings-tabs.css` : Chargé sur les pages de paramètres
- `notifications.css` : Chargé sur les pages de paramètres et éditeur
- Autres fichiers : Chargés de manière conditionnelle selon les besoins

## Organisation recommandée

L'organisation actuelle est **fonctionnelle et logique**. Les fichiers sont bien séparés par fonctionnalité :

1. **Styles globaux** → `admin-global.css`
2. **Navigation/onglets** → `settings-tabs.css`
3. **Paramètres développeur** → `developer-settings.css`
4. **Paramètres système** → `system-settings.css`
5. **Notifications** → `notifications.css`
6. **Fonctionnalités spécifiques** → Fichiers dédiés

## Nettoyage effectué

- ✅ Suppression du dossier `plugin/assets/css/` (fichiers obsolètes)
- ✅ Conservation uniquement des fichiers dans `plugin/resources/assets/css/`
- ✅ Vérification que tous les fichiers utilisés sont référencés dans le code PHP

## Maintenance

- Ajouter de nouveaux styles dans le fichier approprié selon la fonctionnalité
- Éviter de dupliquer des styles entre fichiers
- Documenter les nouvelles sections CSS avec des commentaires clairs