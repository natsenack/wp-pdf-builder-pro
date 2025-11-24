# Tests PDF Builder Pro

## Statut des Tests

Les tests PHPUnit présents dans ce dossier ont été créés lors de la Phase 4 du développement mais ne peuvent pas être exécutés dans l'environnement actuel car ils nécessitent :

- Un environnement de test WordPress complet
- La bibliothèque `wordpress-tests-lib`
- Configuration PHPUnit spécifique à WordPress

## Tests Disponibles

- `PreviewImageAPITest.php` - Tests de l'API d'aperçu
- `CanvasMetaboxIntegrationTest.php` - Tests d'intégration metabox
- `PreviewPerformanceTest.php` - Tests de performance
- `PreviewSecurityTest.php` - Tests de sécurité
- `SampleDataProviderTest.php` - Tests du fournisseur de données
- Et autres tests unitaires

## Validation Alternative

Au lieu des tests PHPUnit, utilisez le script de validation manuel :
```bash
php plugin/validate-phase4.php
```

Ce script valide toutes les fonctionnalités critiques sans nécessiter d'environnement de test WordPress.

## Recommandation Future

Pour activer ces tests PHPUnit :
1. Installer WordPress Test Suite
2. Configurer `WP_TESTS_DIR` environment variable
3. Exécuter `phpunit` dans ce dossier

Les tests sont conservés pour une future implémentation d'intégration continue.