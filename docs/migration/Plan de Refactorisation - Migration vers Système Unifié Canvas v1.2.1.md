# Plan de Refactorisation - Migration vers Système Unifié Canvas v1.2.1

## Vue d'ensemble

Ce document décrit le plan de refactorisation pour migrer le système de paramètres canvas du PDF Builder Pro vers un système unifié. L'objectif est de remplacer les options WordPress individuelles par un système centralisé géré par le `PDF_Builder_Canvas_Manager`.

**Date de création** : 13 janvier 2026  
**Version cible** : v1.2.1  
**Priorité** : Moyenne  
**Durée estimée** : 5-8 jours  

## Contexte

Le système actuel utilise des options WordPress individuelles pour chaque paramètre canvas :
- `pdf_builder_canvas_width`
- `pdf_builder_canvas_height`
- `pdf_builder_canvas_grid_size`
- etc.

Ces options sont éparpillées dans le code et difficiles à maintenir. Le nouveau système unifié utilise `pdf_builder_canvas_settings` comme tableau centralisé.

## Objectifs

- ✅ Centraliser tous les paramètres canvas
- ✅ Améliorer la maintenabilité du code
- ✅ Simplifier l'ajout de nouveaux paramètres
- ✅ Réduire les erreurs de configuration
- ✅ Optimiser les performances de chargement

## Phase 1: Analyse et Préparation (1-2 jours)

### Objectif
Évaluer l'impact et préparer la migration

### Tâches
- [ ] Identifier tous les fichiers utilisant les anciennes options individuelles
- [ ] Créer une liste complète des mappings ancien → nouveau système
- [ ] Analyser les dépendances et impacts sur les fonctionnalités
- [ ] Préparer un script de migration des données existantes
- [ ] Créer des tests automatisés pour valider la migration

### Livrables
- Rapport d'impact détaillé
- Script de migration PHP
- Suite de tests unitaires

## Phase 2: Refactorisation Core (2-3 jours)

### Objectif
Mettre à jour les composants principaux

### Tâches par priorité

#### Priorité haute
- [ ] `PDF_Builder_Unified_Ajax_Handler.php` - Remplacer tous les `get_option('pdf_builder_canvas_*')` par des appels au Canvas Manager
- [ ] `canvas-monitor-diagnostic.php` - Adapter pour utiliser le nouveau système

#### Priorité moyenne
- [ ] `Ajax_Handlers.php` - Migrer les références restantes
- [ ] Templates admin - Mettre à jour les appels aux paramètres

#### Priorité basse
- [ ] Autres fichiers utilitaires

### Points d'attention
- Maintenir la compatibilité arrière pendant la transition
- Tester chaque modification individuellement
- Documenter les changements dans le code

## Phase 3: Tests et Validation (1-2 jours)

### Objectif
S'assurer que tout fonctionne correctement

### Tâches
- [ ] Tests unitaires pour le Canvas Manager
- [ ] Tests d'intégration pour les paramètres canvas
- [ ] Tests fonctionnels de l'interface admin
- [ ] Tests de performance (chargement des paramètres)
- [ ] Validation des données migrées

### Critères de qualité
- Couverture de tests > 90%
- Tous les tests passent
- Performance équivalente ou supérieure

## Phase 4: Nettoyage et Optimisation (0.5-1 jour)

### Objectif
Finaliser et optimiser

### Tâches
- [ ] Supprimer les anciennes options de la base de données
- [ ] Nettoyer le code du Canvas Manager (supprimer la compatibilité arrière)
- [ ] Optimiser les performances (lazy loading, cache)
- [ ] Mettre à jour la documentation

### Optimisations possibles
- Cache des paramètres fréquemment utilisés
- Lazy loading des paramètres non critiques
- Compression des données de configuration

## Phase 5: Déploiement et Monitoring (0.5 jour)

### Objectif
Déployer en production avec surveillance

### Tâches
- [ ] Script de migration production
- [ ] Monitoring des erreurs post-déploiement
- [ ] Plan de rollback si nécessaire
- [ ] Communication avec les utilisateurs

### Métriques de succès
- Temps de chargement des paramètres < 100ms
- Aucune erreur PHP liée aux paramètres canvas
- Feedback positif des utilisateurs

## Outils et Ressources Nécessaires

### Développement
- **Tests** : PHPUnit pour les tests unitaires
- **Migration** : Script PHP pour migrer les données
- **Backup** : Sauvegarde complète des options WordPress

### Monitoring
- **Logs** : Système de logging détaillé
- **Métriques** : Monitoring des performances
- **Alertes** : Notifications en cas d'erreur

## Mapping des Paramètres

| Ancien système | Nouveau système | Type | Défaut |
|----------------|-----------------|------|--------|
| `pdf_builder_canvas_width` | `default_canvas_width` | int | 794 |
| `pdf_builder_canvas_height` | `default_canvas_height` | int | 1123 |
| `pdf_builder_canvas_bg_color` | `canvas_background_color` | string | '#ffffff' |
| `pdf_builder_canvas_grid_size` | `grid_size` | int | 20 |
| `pdf_builder_canvas_zoom_min` | `min_zoom` | int | 25 |
| `pdf_builder_canvas_zoom_max` | `max_zoom` | int | 500 |

## Risques et Mitigation

### Risque élevé : Perte de paramètres utilisateur
**Impact** : Perte de configuration personnalisée  
**Probabilité** : Moyenne  
**Mitigation** :
- Backup complet des options avant migration
- Script de restauration automatique
- Validation des données migrées

### Risque moyen : Fonctionnalités cassées
**Impact** : Interface non fonctionnelle  
**Probabilité** : Élevée  
**Mitigation** :
- Tests exhaustifs avant déploiement
- Déploiement progressif (feature flags)
- Rollback automatique en cas d'erreur

### Risque faible : Performance dégradée
**Impact** : Lentueur de l'interface  
**Probabilité** : Faible  
**Mitigation** :
- Optimisations de cache
- Monitoring des performances
- Tests de charge

## Critères de Succès

- [ ] Toutes les fonctionnalités canvas opérationnelles
- [ ] Aucune référence aux anciennes options dans le code
- [ ] Tests passant à 100%
- [ ] Performance maintenue ou améliorée
- [ ] Données utilisateur préservées
- [ ] Documentation mise à jour

## Équipe et Responsabilités

- **Développeur principal** : Implémentation et tests
- **Reviewer** : Validation du code et tests
- **Product Owner** : Validation fonctionnelle
- **DevOps** : Déploiement et monitoring

## Plan de Communication

- **Début de projet** : Présentation du plan à l'équipe
- **Fin de phase** : Revue des avancées
- **Avant déploiement** : Validation finale
- **Post-déploiement** : Rapport de succès

## Budget et Planning

- **Durée totale** : 5-8 jours
- **Équipe** : 1 développeur + 0.5 reviewer
- **Coût estimé** : 40-60 heures
- **Date cible** : Fin janvier 2026

---

*Document créé le 13 janvier 2026 - Version 1.0*