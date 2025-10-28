# Mode Commande vs Mode Éditeur - Guide d'utilisation

## Vue d'ensemble

Le système PDF Builder Pro prend désormais en charge deux modes de fonctionnement pour les éléments WooCommerce :

- **Mode Éditeur** : Utilise des données fictives pour la prévisualisation et l'édition
- **Mode Commande** : Utilise des données réelles WooCommerce pour le rendu final

## Catégories d'éléments

### 1. Éléments Dynamiques (Données WooCommerce)
Ces éléments utilisent des données différentes selon le mode :
- `product_table` : Tableau des produits de la commande
- `customer_info` : Informations client (nom, adresse, email, téléphone)
- `order_number` : Numéro de commande formaté

### 2. Éléments Canvas (Données statiques)
Ces éléments utilisent toujours les données configurées dans l'éditeur :
- `company_logo` : Logo de l'entreprise
- `dynamic-text` : Texte avec variables (mais variables résolues selon le mode)
- `mentions` : Mentions légales

### 3. Éléments Hybrides (Mélange de sources)
Ces éléments combinent données configurables et données dynamiques :
- `company_info` : Informations entreprise (nom, adresse, etc. configurables dans l'éditeur)

## Utilisation dans le code

### Changement de mode

```typescript
import { useBuilder } from './contexts/builder/BuilderContext';

// Dans un composant
const { dispatch } = useBuilder();

// Passer en mode commande
dispatch({
  type: 'SET_PREVIEW_MODE',
  payload: 'command'
});

// Définir l'ID de commande
dispatch({
  type: 'SET_ORDER_ID',
  payload: '123'
});
```

### Chargement des données WooCommerce

```typescript
import { wooCommerceManager } from './utils/WooCommerceElementsManager';

// Charger les données de commande
await wooCommerceManager.loadOrderData('123');

// Charger les données client
await wooCommerceManager.loadCustomerData(123);
```

## Comportement par élément

### Product Table
- **Éditeur** : Affiche des produits fictifs (T-shirt, Jean, etc.)
- **Commande** : Affiche les vrais produits de la commande WooCommerce

### Customer Info
- **Éditeur** : Affiche "Marie Dupont" avec données fictives
- **Commande** : Affiche le vrai nom et informations du client

### Order Number
- **Éditeur** : Affiche "CMD-2024-01234"
- **Commande** : Affiche le vrai numéro de commande WooCommerce

### Dynamic Text
- **Éditeur** : Variables remplacées par données fictives
- **Commande** : Variables remplacées par données WooCommerce réelles

Variables supportées :
- `#{order_number}` : Numéro de commande
- `#{customer_name}` : Nom du client
- `#{order_date}` : Date de commande
- `#{total}` : Total de la commande

### Company Info (Hybride)
- Utilise les propriétés configurées dans l'élément :
  - `companyName` : Nom de l'entreprise
  - `companyAddress` : Adresse
  - `companyCity` : Ville
  - `companySiret` : Numéro SIRET
  - `companyTva` : Numéro TVA
  - `companyEmail` : Email
  - `companyPhone` : Téléphone

## Architecture

### WooCommerceElementsManager
Classe singleton qui gère :
- Chargement des données WooCommerce
- Mise en cache des données commande/client
- Formatage des données pour l'affichage

### BuilderState
État global étendu avec :
- `previewMode: 'editor' | 'command'`
- `orderId?: string`

### Fonctions de rendu Canvas
Chaque fonction `draw*` vérifie le mode et utilise la source de données appropriée.

## Tests

Les tests valident :
- Comportement correct en mode éditeur (données fictives)
- Comportement correct en mode commande (données WooCommerce)
- Mode hybride pour les éléments configurables
- Calculs des totaux et formatage des données

## Migration

Pour migrer du système existant :
1. Les éléments existants continueront de fonctionner en mode éditeur
2. Ajouter la logique de mode dans l'interface utilisateur
3. Implémenter le chargement des données WooCommerce lors du passage en mode commande