# 💡 Guide du support intégré - Tooltips et aide contextuelle

WP PDF Builder Pro inclut un système complet d'aide contextuelle pour guider les utilisateurs dans l'utilisation du plugin.

## 🎯 Vue d'ensemble

Le système d'aide intégré comprend :

- **Tooltips informatifs** : Bulles d'aide au survol
- **Aide contextuelle** : Guides pas à pas
- **Messages d'assistance** : Conseils intelligents
- **Raccourcis clavier** : Aide aux actions rapides

## 🔧 Configuration des tooltips

### Activation globale

Dans les paramètres du plugin :
1. **WP PDF Builder Pro > Paramètres**
2. Onglet **Interface utilisateur**
3. Section **Aide intégrée**
4. Cochez **Activer les tooltips**

### Niveaux d'aide

Trois niveaux disponibles :

- **Débutant** : Aide maximale, tooltips détaillés
- **Intermédiaire** : Aide équilibrée, tooltips essentiels
- **Expert** : Aide minimale, tooltips sur demande

## 📝 Tooltips par section

### Éditeur de templates

#### Barre d'outils
```
Ajouter élément > Texte
💡 "Ajoutez du texte statique ou utilisez des variables comme {{customer_name}}"

Ajouter élément > Image
💡 "Formats supportés : JPG, PNG, GIF. Taille max : 5MB"

Ajouter élément > Tableau
💡 "Créez des tableaux dynamiques avec {{product_list}}"
```

#### Canevas de conception
```
Élément sélectionné
💡 "Cliquez et glissez pour déplacer. Utilisez les poignées pour redimensionner"

Aperçu temps réel
💡 "L'aperçu se met à jour automatiquement. Cliquez pour tester les variables"
```

#### Propriétés
```
Police et taille
💡 "Choisissez Arial ou Times pour une meilleure compatibilité PDF"

Couleurs
💡 "Utilisez le nuancier pour maintenir la cohérence de votre charte"

Positionnement
💡 "Les coordonnées sont en millimètres depuis le coin supérieur gauche"
```

### Gestion des templates

#### Liste des templates
```
Actions groupées
💡 "Sélectionnez plusieurs templates pour les dupliquer ou supprimer en masse"

Filtres
💡 "Filtrez par type, statut ou date de modification"
```

#### Paramètres template
```
Format de page
💡 "A4 recommandé pour l'impression. Letter pour le marché américain"

Sécurité
💡 "Protégez vos PDFs sensibles avec un mot de passe"
```

## 🚀 Guides contextuels

### Guide de démarrage

Déclenché automatiquement pour les nouveaux utilisateurs :

1. **Bienvenue** : Présentation du plugin
2. **Premier template** : Création guidée
3. **Éditeur** : Découverte de l'interface
4. **Génération** : Test du premier PDF

### Guide avancé

Activé sur demande dans l'aide :

1. **Variables dynamiques** : Utilisation avancée
2. **Intégrations** : Configuration WooCommerce
3. **Automatisations** : Règles complexes
4. **Optimisation** : Performance et sécurité

## ⌨️ Raccourcis clavier

### Éditeur visuel
- `Ctrl+Z` : Annuler la dernière action
- `Ctrl+Y` : Rétablir
- `Ctrl+S` : Sauvegarder
- `Ctrl+P` : Aperçu PDF
- `Suppr` : Supprimer l'élément sélectionné

### Navigation
- `F1` : Ouvrir l'aide contextuelle
- `F11` : Mode plein écran
- `Échap` : Fermer les modales

## 💬 Messages d'assistance intelligents

### Détection d'erreurs
- **Variables manquantes** : "La variable {{unknown_var}} n'existe pas. Vérifiez l'orthographe."
- **Éléments superposés** : "Deux éléments se chevauchent. Ajustez leur position."
- **Taille image** : "Image trop grande (8MB). Compressez-la avant l'import."

### Conseils proactifs
- **Template vide** : "Commencez par ajouter un en-tête avec votre logo"
- **Pas de sauvegarde** : "N'oubliez pas de sauvegarder vos modifications"
- **Performance** : "Considérez compresser les images pour accélérer la génération"

## 🎨 Personnalisation

### Styles des tooltips

Configuration CSS personnalisable :
```css
.tooltip {
  background: #333;
  color: white;
  border-radius: 4px;
  padding: 8px 12px;
}

.tooltip-arrow {
  border-color: #333;
}
```

### Contenu personnalisé

Ajout de tooltips spécifiques :
```php
// Dans functions.php ou plugin personnalisé
add_filter('wp_pdf_builder_tooltip', function($tooltips) {
  $tooltips['custom_field'] = 'Votre aide personnalisée';
  return $tooltips;
});
```

## 📊 Analytics et feedback

### Suivi de l'utilisation

Métriques collectées (anonymes) :
- Sections d'aide consultées
- Tooltips affichés
- Guides complétés
- Temps passé dans l'éditeur

### Amélioration continue

- **Feedback utilisateurs** : Bouton "Cette aide était-elle utile ?"
- **Suggestions** : Liens vers la documentation détaillée
- **Rapports** : Analyse des points de blocage

## 🌐 Accessibilité

### Conformité WCAG
- **Contraste** : Ratio minimum 4.5:1
- **Navigation clavier** : Tous les éléments accessibles
- **Lecteurs d'écran** : Support complet
- **Langues multiples** : Tooltips traduits

### Options d'accessibilité
- **Taille de police** : Ajustable dans les paramètres
- **Couleurs** : Thèmes adaptés aux daltoniens
- **Animation** : Désactivable pour les sensibilités

## 🔧 Maintenance

### Mise à jour des tooltips

Processus de mise à jour :
1. **Révision** : Vérification de l'exactitude
2. **Traductions** : Synchronisation multilingue
3. **Tests** : Validation sur tous les navigateurs
4. **Déploiement** : Mise à jour automatique

### Désactivation sélective

Pour les utilisateurs avancés :
```php
// Désactiver tous les tooltips
add_filter('wp_pdf_builder_enable_tooltips', '__return_false');

// Désactiver une section spécifique
add_filter('wp_pdf_builder_tooltips_editor', '__return_empty_array');
```

## 📞 Support technique

### Ressources d'aide
- **Documentation complète** : [docs/user/README.md](../README.md)
- **Forum communautaire** : Échange entre utilisateurs
- **Support prioritaire** : Pour licences Pro et Enterprise

### Contact support
- **Email** : support@wp-pdf-builder-pro.com
- **Chat en ligne** : Disponible 9h-18h CET
- **Tickets** : Système de suivi détaillé

---

*Guide du support intégré - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\user\guides\help-system.md