# Notes de Mise à Jour - PDF Builder Pro V2

## Version 1.1.0.1 (27 Janvier 2026)

### Corrections (Bug Fixes)

- ✅ **Propriétés de police séparées** : Correction de l'application des propriétés de police distinctes pour le nom de l'entreprise et les informations
  - Le nom utilise maintenant `headerFontSize`, `headerFontFamily`, `headerFontWeight`, `headerFontStyle`
  - Les informations utilisent `bodyFontSize`, `bodyFontFamily`, `bodyFontWeight`, `bodyFontStyle`
- ✅ **Espacement des lignes** : Correction du chevauchement du contenu dans l'élément company_info
  - Augmentation de l'espacement de 0.2x à 1.2x la taille de police minimum
- ✅ **Fonction normalizeColor** : Ajout de la fonction manquante pour éviter les erreurs JavaScript
- ✅ **Optimisation du code Canvas.tsx** : Refactorisation complète pour améliorer les performances
  - Création de fonctions helper pour la gestion des polices et couleurs
  - Réduction de la duplication de code
  - Amélioration de la maintenabilité

### Améliorations (Enhancements)

- 🔄 **Interface de personnalisation** : Support complet des propriétés de police séparées
  - Section "Police du nom de l'entreprise" (14px, Arial, Bold)
  - Section "Police des informations" (12px, Arial, Normal)
- 🔄 **Gestion mémoire Canvas** : Optimisation du cache des images
- 🔄 **Normalisation des poids de police** : Support des valeurs numériques (700) et textuelles (bold)

### Fonctionnalités (Features)

- 🆕 **Éditeur React Canvas** : Interface moderne pour la création de PDF
- 🆕 **Prévisualisation temps réel** : Aperçu instantané des modifications
- 🆕 **API de prévisualisation** : Communication optimisée avec le backend PHP
- 🆕 **paramètres de police des éléments** :company_info et cutomer_info un alignement

---

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

---

---

## Prochaines versions

### Version 1.2.0 (Roadmap)

- [ ] Éditeur visuel drag & drop
- [ ] Bibliothèque de composants
- [ ] Intégrations tierces (CRM, ERP)
- [ ] API REST complète
- [ ] Support multi-langues

### Version 2.0.0 (Vision)

- [ ] Architecture microservices
- [ ] Analytics et reporting avancés

---

_Dernière mise à jour : 27 Janvier 2026_</content>
<parameter name="filePath">i:\wp-pdf-builder-pro-V2\CHANGELOG.md
