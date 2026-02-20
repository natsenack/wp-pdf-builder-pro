# Changelog - PDF Builder Pro

## Tous les changements notables de ce projet seront documentés dans ce fichier.

## Version 1.1.0.0 (À venir)

### Fonctionnalités (Features)

- 🆕 **Nouveaux éléments dans la liste React** : Ajout de nouveaux types d'éléments disponibles dans le panneau d'insertion
  - [ ] Élément 1 (à définir)
  - [ ] Élément 2 (à définir)
  - [ ] Élément 3 (à définir)

---

## Version 1.0.4.0 (À venir)

### Fonctionnalités (Features)

- 🆕 **Format A3 activé** : Le format papier A3 (297×420mm) est désormais disponible et sélectionnable dans les paramètres du template

### Restrictions en cours

> ⚠️ Les formats et options suivants sont **temporairement désactivés** dans le plugin et seront activés dans une prochaine version :

- 🔒 **Format désactivé** — 🇺🇸 Letter (8.5×11")
- 🔒 **Format désactivé** — ⚖️ Legal (8.5×14")
- 🔒 **Format désactivé** — 📦 Étiquette Colis (100×150mm)
- 🔒 **Orientation désactivée** — Paysage (seul le **Portrait** est disponible)

---

## Version 1.0.3.0 (Mars 2026)

### Corrections (Bug Fixes)

- [ ] **Bug 1** : À définir
- [ ] **Bug 2** : À définir
- [ ] **Bug 3** : À définir

## [1.0.2.0] - 2026-02-20

### ✨ Nouvelles fonctionnalités

- **Système de mises à jour automatiques** via EDD intégré à WordPress
- Vérification automatique des mises à jour (cache 12h)
- Hooks WordPress standards: `plugins_api`, `pre_set_site_transient_update_plugins`
- Notifications de mise à jour dans l'interface d'administration WordPress

### 🔒 Sécurité

- Chiffrement AES-256-CBC de la clé de licence en base de données
- Affichage masqué des clés (format: 5 caractères + 18 points)
- Décryption lazy-loaded au démarrage du plugin

### 📊 Améliorations

- Table de comparaison des fonctionnalités Gratuit vs Premium
  - Section visible: 6 fonctionnalités clés
  - Section cachée: 19 fonctionnalités supplémentaires
  - Total: 25 fonctionnalités listées
- Informations détaillées d'expiration et calcul des jours restants
- Couleur d'alerte des jours expiration (vert/orange/rouge)
- Boutons "Renouveler" et "Se désabonner" avec URLs EDD sécurisées
- Section "Informations détaillées" collapsible

### 🐛 Corrections

- Corrigé: bouton "Configurer le canevas" sur pages d'édition
- Corrigé: désactivation correcte des licences
- Corrigé: récupération des informations clients (nom, email, activations)

### 📝 Documentation

- Ajout du `changelog.json` pour servir les changelogs au client
- Ajout du `CHANGELOG.md` (ce fichier) pour la documentation

---

## [1.0.1.0] - 2026-01-15

### 🔧 Corrections

- Corrections de bugs critiques
- Optimisations de performance de l'éditeur

### 🎨 Amélioration UI

- Améliorations mineures de l'interface utilisateur

---

## [1.0.0.0] - 2025-12-01

### 🎉 Lancement initial

- Générateur de PDF professionnel avec éditeur visuel
- Templates professionnels inclus
- Support des éléments de base (texte, images, formes)
- Gestion des licences EDD intégrée
- Mode gratuit et premium

---

## Format de versioning

Le plugin utilise le format de versioning: `MAJOR.MINOR.PATCH.BUILD`

Exemple: `1.0.2.0`

- `1` = Majeure (changements majeurs)
- `0` = Mineure (nouvelles fonctionnalités)
- `2` = Patch (corrections de bugs)
- `0` = Build (numéro de build)

## Procédure de release

1. Mettre à jour la version dans `plugin/pdf-builder-pro.php` (header `Version:`)
2. Créer une entry dans `CHANGELOG.md`
3. Créer une entry dans `plugin/changelog.json`
4. Lancer `.\build\deploy-simple.ps1` pour générer le ZIP versionné
5. Uploader le ZIP en EDD
6. Committer les changements: `git commit -am "Release v1.0.2.0"`
