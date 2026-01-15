# 🎨 Guide de création de templates PDF

Ce guide complet vous explique comment créer des templates PDF professionnels avec WP PDF Builder Pro, de la conception basique aux fonctionnalités avancées.

## 📋 Prérequis

Avant de commencer :
- Plugin WP PDF Builder Pro installé et activé
- Accès administrateur WordPress
- Connaissances de base en HTML/CSS (optionnel mais recommandé)

## 🚀 Création de votre premier template

### Étape 1 : Accéder au générateur
1. Dans l'admin WordPress : **WP PDF Builder Pro > Générateur**
2. Cliquez sur **Nouveau template**
3. Donnez un nom descriptif (ex: "Facture Standard")
4. Sélectionnez le type de document

### Étape 2 : Configuration de base
- **Format** : A4, Letter, ou personnalisé
- **Orientation** : Portrait ou Paysage
- **Marges** : 10mm par défaut (modifiables)
- **Police par défaut** : Arial, Times New Roman, etc.

## 🏗️ Structure d'un template

Un template PDF bien conçu comprend généralement :

```
┌─────────────────────────────────────┐
│          EN-TÊTE                    │
│  Logo + Infos entreprise            │
├─────────────────────────────────────┤
│          CORPS                      │
│  Contenu principal + Données        │
├─────────────────────────────────────┤
│          PIED DE PAGE               │
│  Mentions légales + Coordonnées     │
└─────────────────────────────────────┘
```

### En-tête
- Logo de l'entreprise (aligné à gauche)
- Nom et coordonnées (aligné à droite)
- Numéro de document et date

### Corps
- Informations client
- Détails de la commande/produit
- Tableaux de données
- Conditions générales

### Pied de page
- Mentions légales
- Conditions de paiement
- Coordonnées de contact

## 🛠️ Éléments de conception

### Texte et typographie

#### Texte statique
```html
<p>Ceci est un texte fixe qui ne change pas</p>
```

#### Texte dynamique (variables)
```
{{customer_name}} - Nom du client
{{order_date}} - Date de commande
{{total_amount}} - Montant total TTC
```

#### Styles de texte
- **Gras** : `<strong>Texte important</strong>`
- *Italique* : `<em>Texte mis en valeur</em>`
- <u>Souligné</u> : `<u>Texte souligné</u>`
- `Code` : `<code>Texte technique</code>`

### Images et médias

#### Ajouter une image
1. Cliquez sur **Ajouter élément > Image**
2. Téléchargez ou sélectionnez une image
3. Redimensionnez et positionnez
4. Définissez les propriétés (alt, dimensions)

#### Types d'images courants
- **Logo entreprise** : Format PNG transparent
- **Signatures** : Images scannées
- **Photos produits** : Formats JPG optimisés
- **Icônes** : SVG pour la scalabilité

### Tableaux de données

#### Tableau simple
```
| Produit | Quantité | Prix | Total |
|---------|----------|------|-------|
| Article 1 | 2 | 15€ | 30€ |
```

#### Tableau dynamique (commandes)
Utilisez les variables :
- `{{product_name}}` - Nom du produit
- `{{product_quantity}}` - Quantité
- `{{product_price}}` - Prix unitaire
- `{{product_total}}` - Total ligne

### Éléments graphiques

#### Lignes et séparateurs
- Lignes horizontales pour séparer les sections
- Bordures pour encadrer les informations importantes

#### Formes géométriques
- Rectangles pour les zones d'information
- Cercles pour les numéros d'étape

## 🎨 Styles et mise en page

### Couleurs
- **Primaire** : Couleur principale de votre marque
- **Secondaire** : Couleurs d'accentuation
- **Texte** : Noir ou gris foncé pour la lisibilité
- **Arrière-plan** : Blanc ou très clair

### Polices
- **Titres** : Polices épaisses (Bold, 14-18pt)
- **Corps** : Polices lisibles (Regular, 10-12pt)
- **Légende** : Polices plus petites (8-10pt)

### Espacement
- **Marges** : 10-15mm autour du document
- **Interligne** : 1.2-1.5 pour la lisibilité
- **Espacement éléments** : 5-10mm entre sections

## ⚡ Fonctionnalités avancées

### Conditions et logique

#### Affichage conditionnel
```
{{if customer_company}}
{{customer_company}}
{{/if}}
```

#### Boucles (pour les listes)
```
{{each products}}
- {{product_name}} ({{product_quantity}}x)
{{/each}}
```

### Calculs automatiques
- **Sous-totaux** : Calcul automatique des lignes
- **TVA** : Application automatique des taux
- **Remises** : Calcul des réductions

### Intégrations dynamiques

#### WooCommerce
- Données de commande automatiques
- Informations client depuis WooCommerce
- Produits et variations

#### CRM personnalisé
- Champs personnalisés du CRM
- Données clients synchronisées
- Historique des interactions

## 📱 Responsive et formats multiples

### Adaptation aux formats
- **A4** : Format standard bureau
- **Letter** : Format nord-américain
- **A5** : Format compact

### Optimisation mobile
- Polices lisibles sur petits écrans
- Tableaux adaptatifs
- Images optimisées

## 🧪 Tests et validation

### Aperçu en temps réel
1. Utilisez l'onglet **Aperçu** de l'éditeur
2. Testez avec des données réelles
3. Vérifiez l'impression (Ctrl+P)

### Tests de données
- **Données vides** : Comportement sans données
- **Données longues** : Textes très longs
- **Caractères spéciaux** : Accents, symboles

### Validation finale
- Impression test sur différentes imprimantes
- Vérification des couleurs (N&B)
- Contrôle de la lisibilité

## 💡 Bonnes pratiques

### Performance
- Optimisez les images (< 1MB)
- Limitez le nombre d'éléments par page
- Utilisez des polices web standards

### Maintenabilité
- Nommez clairement vos éléments
- Commentez vos variables complexes
- Versionnez vos templates

### Accessibilité
- Contraste suffisant (4.5:1 minimum)
- Texte alternatif pour les images
- Structure logique du document

## 🔧 Dépannage

### Problèmes courants
- **Éléments qui se chevauchent** : Ajustez les marges
- **Texte coupé** : Vérifiez les dimensions
- **Variables non remplacées** : Contrôlez la syntaxe

### Support avancé
- Consultez les logs d'erreur
- Utilisez le mode débogage
- Contactez le support technique

---

*Guide créé le 20 octobre 2025 - Version 1.0*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\user\guides\templates.md