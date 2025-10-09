# Architecture Fidèle au Plugin Concurrent

## Vue d'ensemble

Notre implémentation a été restructurée pour être fidèle à l'architecture du plugin `woo-pdf-invoice-builder`, en utilisant les mêmes patterns et structures de données.

## Structures de Données

### FieldDTO (équivalent)
Chaque élément utilise maintenant une structure similaire à `FieldDTO` :
```javascript
{
  type: 'text' | 'field',  // Type d'élément (comme FieldFactory::GetField())
  fieldID: string,         // Identifiant unique (comme fieldID dans FieldDTO)
  fieldOptions: object,    // Propriétés spécifiques
  styles: object          // Styles CSS
}
```

### Types d'Éléments

- **`text`**: Éléments de texte libre (comme `FieldFactory::GetField('text')`)
- **`field`**: Subfields spécialisés (comme `FieldFactory::GetSubField()`)

## Gestion des Variables

### VariableManager (équivalent à TagManager)

Notre `VariableManager.js` reproduit fidèlement `TagManager::Process()` :

```javascript
// Comme TagManager::Process($text)
VariableManager.processText(text, useRealData, orderData)
```

### Variables Disponibles

- `[order_number]` - Numéro de commande
- `[order_date]` - Date de commande
- `[order_total]` - Total commande
- `[order_subtotal]` - Sous-total
- `[order_tax]` - TVA
- `[customer_name]` - Nom client
- `[customer_email]` - Email client
- `[billing_address]` - Adresse facturation
- `[shipping_address]` - Adresse livraison
- `[payment_method]` - Méthode paiement
- `[shipping_method]` - Méthode livraison

## Éléments Implémentés

### Informations Commande
- `order_number` (field) - Numéro de commande
- `order_date` (field) - Date de commande
- `order_total` (field) - Total commande
- `order_subtotal` (field) - Sous-total
- `order_tax` (field) - TVA
- `payment_method` (field) - Méthode de paiement
- `shipping_method` (field) - Méthode de livraison
- `order_info_combined` (text) - Informations combinées

### Informations Client
- `customer_name` (field) - Nom client
- `customer_email` (field) - Email client
- `billing_address` (field) - Adresse facturation
- `shipping_address` (field) - Adresse livraison
- `customer_info_combined` (text) - Informations combinées

### Éléments de Base
- `custom_text` (text) - Texte libre
- `shape_rectangle` (rectangle) - Forme rectangulaire

## Architecture PHP Concurrente Référencée

### Classes Principales
- `PDFFieldBase` - Classe de base abstraite
- `FieldFactory` - Factory pour créer les éléments
- `TagManager` - Gestionnaire de substitution de variables
- `OrderValueRetriever` - Récupération des données WooCommerce

### Méthodes Clés
- `FieldFactory::GetField($type)` - Crée un élément selon son type
- `FieldFactory::GetSubField($fieldType)` - Crée un subfield spécialisé
- `TagManager::Process($text)` - Substitue les variables dans le texte
- `PDFFieldBase::InternalGetHTML()` - Génère le HTML de l'élément

## Différences avec l'Original

1. **Côté Client vs Serveur**: Notre implémentation est JavaScript/React côté client, tandis que l'original est PHP côté serveur
2. **Prévisualisation**: Nous offrons une prévisualisation en temps réel avec données de test
3. **Interface Graphique**: Nous avons une interface drag & drop moderne

## Compatibilité

L'architecture est conçue pour être compatible avec la génération PDF côté serveur qui utiliserait la même logique que le plugin concurrent.