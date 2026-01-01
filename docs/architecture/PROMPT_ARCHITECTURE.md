# Architecture et Fonctions du Projet WP PDF Builder Pro

## Introduction
Ce document décrit l'architecture globale du projet WP PDF Builder Pro, ainsi que les fonctions principales et les règles de production associées.

## Architecture Globale

### Structure du Projet
Le projet est organisé en plusieurs modules principaux :

1. **Composants React** : Situés dans `assets/js/pdf-builder-react/components`, ces composants forment l'interface utilisateur principale.
2. **Contexte et Hooks** : Situés dans `assets/js/pdf-builder-react/contexts` et `assets/js/pdf-builder-react/hooks`, ils gèrent l'état global et les logiques réutilisables.
3. **Utilitaires** : Situés dans `assets/js/pdf-builder-react/utils`, ils fournissent des fonctions utilitaires pour le projet.
4. **Types et Constantes** : Situés dans `assets/js/pdf-builder-react/types` et `assets/js/pdf-builder-react/constants`, ils définissent les types et constantes utilisés dans le projet.

### Diagramme de l'Architecture

```plaintext
WP PDF Builder Pro
├── Composants React
│   ├── Canvas
│   ├── Toolbar
│   ├── PropertiesPanel
│   ├── Header
│   ├── ElementLibrary
│   └── ...
├── Contexte et Hooks
│   ├── BuilderContext
│   ├── CanvasSettingsContext
│   ├── useTemplate
│   ├── useCanvasSettings
│   └── ...
├── Utilitaires
│   ├── debug
│   ├── responsive
│   ├── unitConversion
│   └── ...
└── Types et Constantes
    ├── canvas
    ├── elements
    └── ...
```

## Fonctions Principales

### 1. PDFBuilder
- **Fichier** : [`assets/js/pdf-builder-react/PDFBuilder.tsx`](assets/js/pdf-builder-react/PDFBuilder.tsx)
- **Description** : Composant principal qui initialise le builder PDF avec les dimensions par défaut et gère les mises à jour dynamiques des dimensions du canvas.
- **Fonctions Clés** :
  - `PDFBuilder` : Initialise le builder avec des dimensions personnalisables.
  - Gestion des événements de mise à jour des dimensions du canvas.

### 2. PDFBuilderContent
- **Fichier** : [`assets/js/pdf-builder-react/components/PDFBuilderContent.tsx`](assets/js/pdf-builder-react/components/PDFBuilderContent.tsx)
- **Description** : Composant principal qui gère le contenu du builder PDF, incluant le header, la toolbar, le canvas, et le panneau de propriétés.
- **Fonctions Clés** :
  - Gestion de l'état du header et du panneau de propriétés.
  - Intégration des hooks pour la gestion des templates et des paramètres du canvas.
  - Gestion des événements de scroll et des dimensions du canvas.

### 3. Canvas
- **Fichier** : [`assets/js/pdf-builder-react/components/canvas/Canvas.tsx`](assets/js/pdf-builder-react/components/canvas/Canvas.tsx)
- **Description** : Composant qui gère le canvas de dessin pour la création de templates PDF.
- **Fonctions Clés** :
  - Gestion des interactions utilisateur sur le canvas.
  - Rendu des éléments sur le canvas.

### 4. Toolbar
- **Fichier** : [`assets/js/pdf-builder-react/components/toolbar/Toolbar.tsx`](assets/js/pdf-builder-react/components/toolbar/Toolbar.tsx)
- **Description** : Composant qui fournit une barre d'outils pour les actions courantes comme l'annulation, la refonte, et la gestion de la grille.
- **Fonctions Clés** :
  - Gestion des actions de l'utilisateur via la toolbar.

### 5. PropertiesPanel
- **Fichier** : [`assets/js/pdf-builder-react/components/properties/PropertiesPanel.tsx`](assets/js/pdf-builder-react/components/properties/PropertiesPanel.tsx)
- **Description** : Composant qui affiche les propriétés des éléments sélectionnés sur le canvas.
- **Fonctions Clés** :
  - Gestion des propriétés des éléments.

### 6. ElementLibrary
- **Fichier** : [`assets/js/pdf-builder-react/components/element-library/ElementLibrary.tsx`](assets/js/pdf-builder-react/components/element-library/ElementLibrary.tsx)
- **Description** : Composant qui fournit une bibliothèque d'éléments WooCommerce pour ajouter au canvas.
- **Fonctions Clés** :
  - Gestion de la sélection et de l'ajout d'éléments au canvas.

### 7. BuilderContext
- **Fichier** : [`assets/js/pdf-builder-react/contexts/builder/BuilderContext.tsx`](assets/js/pdf-builder-react/contexts/builder/BuilderContext.tsx)
- **Description** : Contexte qui gère l'état global du builder, incluant les éléments, la sélection, et l'historique des actions.
- **Fonctions Clés** :
  - `BuilderProvider` : Fournit le contexte aux composants enfants.
  - `useBuilder` : Hook pour accéder au contexte du builder.

### 8. CanvasSettingsContext
- **Fichier** : [`assets/js/pdf-builder-react/contexts/CanvasSettingsContext.tsx`](assets/js/pdf-builder-react/contexts/CanvasSettingsContext.tsx)
- **Description** : Contexte qui gère les paramètres du canvas, comme les dimensions et les couleurs.
- **Fonctions Clés** :
  - `CanvasSettingsProvider` : Fournit le contexte des paramètres du canvas.
  - `useCanvasSettings` : Hook pour accéder aux paramètres du canvas.

### 9. useTemplate
- **Fichier** : [`assets/js/pdf-builder-react/hooks/useTemplate.tsx`](assets/js/pdf-builder-react/hooks/useTemplate.tsx)
- **Description** : Hook qui gère les templates, incluant la sauvegarde, la prévisualisation, et la création de nouveaux templates.
- **Fonctions Clés** :
  - `saveTemplate` : Sauvegarde le template actuel.
  - `previewTemplate` : Prévusialise le template actuel.
  - `newTemplate` : Crée un nouveau template.

### 10. useCanvasSettings
- **Fichier** : [`assets/js/pdf-builder-react/hooks/useCanvasSettings.tsx`](assets/js/pdf-builder-react/hooks/useCanvasSettings.tsx)
- **Description** : Hook qui gère les paramètres du canvas, comme les dimensions et les couleurs.
- **Fonctions Clés** :
  - `updateCanvasSettings` : Met à jour les paramètres du canvas.

## Règles de Production

### 1. Gestion des États
- Utiliser des contextes pour gérer les états globaux.
- Éviter de passer des états via des props sur plusieurs niveaux.
- Utiliser des hooks personnalisés pour encapsuler la logique d'état.

### 2. Gestion des Événements
- Utiliser des écouteurs d'événements pour les interactions utilisateur.
- Nettoyer les écouteurs d'événements dans les effets de nettoyage des hooks.
- Utiliser des événements personnalisés pour la communication entre composants.

### 3. Gestion des Erreurs
- Utiliser des notifications pour informer l'utilisateur des erreurs.
- Logger les erreurs pour le débogage.
- Implémenter des mécanismes de récupération pour les erreurs critiques.

### 4. Responsivité
- Utiliser des hooks pour gérer la responsivité.
- Adapter l'interface utilisateur en fonction de la taille de l'écran.
- Tester l'interface sur différents appareils et tailles d'écran.

### 5. Sauvegarde et Chargement
- Sauvegarder les templates de manière asynchrone.
- Informer l'utilisateur de l'état de la sauvegarde.
- Implémenter des mécanismes de sauvegarde automatique.

### 6. Performance
- Utiliser des composants memoisés pour éviter des rendus inutiles.
- Optimiser les calculs coûteux avec des hooks comme `useMemo` et `useCallback`.
- Éviter les rendus inutiles en utilisant des comparaisons profondes pour les props.

### 7. Sécurité
- Valider les entrées utilisateur pour éviter les injections de code.
- Utiliser des mécanismes de sécurité pour les requêtes AJAX.
- Protéger les données sensibles avec des mécanismes de chiffrement.

### 8. Documentation
- Documenter les fonctions et composants avec des commentaires clairs.
- Maintenir une documentation à jour pour les règles de production.
- Utiliser des outils de documentation pour générer des rapports automatiques.

### 9. Tests
- Implémenter des tests unitaires pour les fonctions critiques.
- Utiliser des tests d'intégration pour vérifier les interactions entre composants.
- Automatiser les tests pour assurer une couverture complète.

### 10. Déploiement
- Utiliser des scripts de déploiement pour automatiser le processus.
- Implémenter des mécanismes de rollback pour les déploiements échoués.
- Tester les déploiements dans des environnements de staging avant la production.

### 11. Environnement de Production
- **Pas de LocalStorage** : Ne pas utiliser le localStorage pour stocker des données. Utiliser des solutions côté serveur ou des contextes React pour la gestion des états.
- **Pas de Système de Cache** : Éviter d'implémenter des systèmes de cache personnalisés. Utiliser des solutions éprouvées si nécessaire.
- **Git et Commit** : Il est inutile de faire des commits manuels. Le script de déploiement gère déjà cela.
- **Environnement de Production** : Nous sommes dans un environnement de production. Éviter tout code de test ou de bricolage.
- **Désactivation de Code** : Ne pas désactiver ou supprimer du code pour résoudre un problème. Le code doit fonctionner correctement dans un environnement de production, même s'il est temporairement désactivé.

### 12. Gestion des Problèmes et Déploiement
- **Résolution des Problèmes** : Si un problème persiste trop longtemps, rechercher dans le Git selon la date proposée. Si aucune date n'est proposée, restaurer le fichier ou autre pour réparer et l'adapter au code actuel.
- **Déploiement** : Toute modification des fichiers dans le plugin doit être déployée avec le script [`deploy-simple.ps1`](build/deploy-simple.ps1). En cas de restaurations ou de code non visible lors des changements par le git, utiliser l'autre script de déploiement.

## Conclusion
Ce document fournit une vue d'ensemble de l'architecture et des fonctions principales du projet WP PDF Builder Pro. Il sert de référence pour comprendre la structure du projet et les règles de production à suivre.