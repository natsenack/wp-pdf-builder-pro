# 📋 Variables Dynamiques WooCommerce - Phase 2.3.1

**📅 Date** : 22 octobre 2025
**🔄 Statut** : Collecte et documentation des variables disponibles

---

## 🎯 Vue d'ensemble

Ce document recense toutes les variables dynamiques disponibles dans le système PDF Builder Pro pour WooCommerce. Ces variables permettent d'injecter automatiquement des données depuis les commandes WooCommerce dans les templates PDF.

---

## 📊 Variables par Catégorie - Classification Détaillée

### 🆔 **1. Variables de Commande (Order)**
*Sous-catégories : Identifiant, Statut, Dates*

#### **1.1 Identifiant & Numérotation**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{order_id}}` | ID technique interne | 🔴 Haute |
| `{{order_number}}` | Numéro formaté client | 🔴 Haute |

#### **1.2 Statut & État**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{order_status}}` | Statut lisible | 🟡 Moyenne |
| `{{payment_method}}` | Méthode de paiement | 🟡 Moyenne |

#### **1.3 Dates & Temporal**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{order_date}}` | Date de création (JJ/MM/AAAA) | 🔴 Haute |
| `{{order_date_time}}` | Date et heure complète | 🟡 Moyenne |

### 👤 **2. Variables Client (Customer)**
*Sous-catégories : Identité, Contact, Profil*

#### **2.1 Identité Personnelle**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{customer_name}}` | Nom complet | 🔴 Haute |
| `{{customer_first_name}}` | Prénom | 🟡 Moyenne |
| `{{customer_last_name}}` | Nom de famille | 🟡 Moyenne |

#### **2.2 Informations de Contact**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{customer_email}}` | Adresse email | 🔴 Haute |
| `{{customer_phone}}` | Numéro de téléphone | 🟡 Moyenne |

### 🏠 **3. Variables d'Adresse (Addresses)**
*Sous-catégories : Facturation, Livraison, Géographie*

#### **3.1 Adresse de Facturation (Billing)**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{billing_company}}` | Société | 🟡 Moyenne |
| `{{billing_address_1}}` | Ligne 1 | 🔴 Haute |
| `{{billing_address_2}}` | Ligne 2 | 🟡 Moyenne |
| `{{billing_city}}` | Ville | 🔴 Haute |
| `{{billing_state}}` | État/Région | 🟡 Moyenne |
| `{{billing_postcode}}` | Code postal | 🔴 Haute |
| `{{billing_country}}` | Pays | 🔴 Haute |
| `{{billing_address}}` | Adresse complète formatée | 🔴 Haute |

#### **3.2 Adresse de Livraison (Shipping)**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{shipping_first_name}}` | Prénom livraison | 🟡 Moyenne |
| `{{shipping_last_name}}` | Nom livraison | 🟡 Moyenne |
| `{{shipping_company}}` | Société livraison | 🟢 Basse |
| `{{shipping_address_1}}` | Adresse livraison 1 | 🟡 Moyenne |
| `{{shipping_address_2}}` | Adresse livraison 2 | 🟢 Basse |
| `{{shipping_city}}` | Ville livraison | 🟡 Moyenne |
| `{{shipping_state}}` | État livraison | 🟢 Basse |
| `{{shipping_postcode}}` | CP livraison | 🟡 Moyenne |
| `{{shipping_country}}` | Pays livraison | 🟡 Moyenne |
| `{{shipping_address}}` | Adresse livraison complète | 🟡 Moyenne |

### 💰 **4. Variables Financières (Financial)**
*Sous-catégories : Totaux, Détails, Calculs*

#### **4.1 Totaux Principaux**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{total}}` / `{{order_total}}` | Total final | 🔴 Haute |
| `{{subtotal}}` / `{{order_subtotal}}` | Sous-total HT | 🔴 Haute |

#### **4.2 Détails Fiscaux**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{tax}}` / `{{order_tax}}` | Total TVA | 🟡 Moyenne |
| `{{shipping_total}}` / `{{order_shipping}}` | Frais de port | 🟡 Moyenne |
| `{{discount_total}}` | Remises appliquées | 🟡 Moyenne |

#### **4.3 Métadonnées**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{currency}}` | Devise utilisée | 🟡 Moyenne |

### 🏢 **5. Variables Société (Company)**
*Sous-catégories : Informations générales*

#### **5.1 Informations Complètes**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{company_info}}` | Toutes les infos société | 🟡 Moyenne |

*Note : Les variables société sont gérées via les options WordPress et non directement depuis WooCommerce*

### 📅 **6. Variables Système (System)**
*Sous-catégories : Dates, Métadonnées*

#### **6.1 Dates Dynamiques**
| Variable | Description | Priorité |
|----------|-------------|----------|
| `{{date}}` | Date du jour | 🟡 Moyenne |
| `{{due_date}}` | Date d'échéance (+30j) | 🟢 Basse |

---

## 📦 **7. Variables Produits (Items) - Données Avancées**
*Disponibles via les données d'items de commande*

### **7.1 Informations Produit**
| Propriété | Description | Type |
|-----------|-------------|------|
| `name` | Nom du produit | String |
| `quantity` | Quantité commandée | Number |
| `sku` | Référence produit | String |
| `product_id` | ID WooCommerce | Number |
| `variation_id` | ID variation | Number |
| `type` | Type de produit | String |

### **7.2 Prix et Calculs**
| Propriété | Description | Type |
|-----------|-------------|------|
| `price` | Prix unitaire | Number |
| `regular_price` | Prix catalogue | Number |
| `sale_price` | Prix soldé | Number |
| `total` | Total ligne TTC | Number |
| `total_tax` | Taxe ligne | Number |
| `subtotal` | Sous-total ligne | Number |

### **7.3 Variations (si applicable)**
| Propriété | Description | Type |
|-----------|-------------|------|
| `variation_attributes` | Attributs formatés | Object |
| `[attribute_name]` | Valeur d'attribut | String |

---

## 🎯 **Classification par Usage**

### 📄 **Variables Essentielles (Factures/Devis)**
- `{{order_number}}`, `{{order_date}}`, `{{customer_name}}`
- `{{billing_address}}`, `{{total}}`, `{{order_tax}}`
- Items avec `name`, `quantity`, `price`, `total`

### 📧 **Variables Contact (Emails/Communications)**
- `{{customer_email}}`, `{{customer_phone}}`
- `{{billing_address}}`, `{{shipping_address}}`

### 📊 **Variables Analytiques (Rapports)**
- `{{order_status}}`, `{{payment_method}}`
- `{{subtotal}}`, `{{discount_total}}`, `{{shipping_total}}`

### 🏷️ **Variables Marketing (Personnalisation)**
- `{{customer_first_name}}`, `{{customer_last_name}}`
- `{{order_date}}`, dates relatives

---

## ⚡ **Variables Obligatoires vs Optionnelles**

### 🔴 **Toujours Disponibles (Obligatoires)**
- `{{order_id}}`, `{{order_number}}`, `{{order_date}}`
- `{{total}}`, `{{subtotal}}`
- Items de base (`name`, `quantity`, `price`)

### 🟡 **Conditionnelles (Selon configuration)**
- Adresses : Disponibles si saisies par le client
- Email/Téléphone : Selon méthode de commande
- Variations : Seulement pour produits variables

### 🟢 **Avancées (Selon besoins métier)**
- `{{due_date}}`, dates calculées
- Attributs de variation détaillés
- Métadonnées fiscales avancées

---

## 🔄 **Évolution et Extensions Futures**

### 🚀 **Variables Potentielles à Ajouter**
- **Statuts avancés** : `{{order_status_slug}}`, `{{order_status_color}}`
- **Historique** : `{{order_modified_date}}`, `{{order_completed_date}}`
- **Client** : `{{customer_id}}`, `{{customer_orders_count}}`
- **Produits** : `{{product_categories}}`, `{{product_tags}}`
- **International** : Variables selon locale (`{{order_date_fr}}`, `{{order_date_en}}`)

### 🔧 **Calculs Dynamiques**
- **Jours restants** : `{{days_until_due}}`
- **Pourcentages** : `{{tax_percentage}}`
- **Échéanciers** : `{{installment_dates}}`

---

*Classification détaillée - Phase 2.3.2 terminée*

---

## 🔧 Variables Supplémentaires Disponibles

### 📦 **Variables Produits (Items)**
*Disponibles via `get_order_items_complete_data()` :*
- `name` : Nom du produit
- `quantity` : Quantité commandée
- `price` : Prix unitaire
- `regular_price` : Prix régulier
- `sale_price` : Prix soldé
- `total` : Total ligne
- `total_tax` : Taxe ligne
- `subtotal` : Sous-total ligne
- `sku` : Référence produit
- `product_id` : ID produit
- `variation_id` : ID variation
- `type` : Type de produit
- `variation_attributes` : Attributs de variation

### 🏷️ **Variables Variations**
*Pour les produits variables :*
- Attributs de variation formatés
- Valeurs d'attributs lisibles
- Labels d'attributs traduits

---

## 📝 Formats Détaillés et Exemples - Phase 2.3.3

### 🆔 **Variables de Commande - Formats & Exemples**

#### **1.1 Identifiant & Numérotation**
| Variable | Format Technique | Exemple Concret | Cas Particuliers |
|----------|------------------|-----------------|------------------|
| `{{order_id}}` | `int` (1-999999) | `1234` | ID interne WooCommerce |
| `{{order_number}}` | `string` (alphanumérique) | `CMD-2025-0123` | Formaté selon réglages WooCommerce |

#### **1.2 Statut & État**
| Variable | Format Technique | Exemple Concret | Cas Particuliers |
|----------|------------------|-----------------|------------------|
| `{{order_status}}` | `string` localisé | `Traitement en cours` | Traduit selon WP locale |
| `{{payment_method}}` | `string` | `Carte bancaire (Stripe)` | Titre complet de la méthode |

#### **1.3 Dates & Temporal**
| Variable | Format Technique | Exemple Concret | Cas Particuliers |
|----------|------------------|-----------------|------------------|
| `{{order_date}}` | `DD/MM/YYYY` | `22/10/2025` | Format français standard |
| `{{order_date_time}}` | `DD/MM/YYYY HH:MM:SS` | `22/10/2025 14:30:25` | Avec secondes |

### 👤 **Variables Client - Formats & Exemples**

#### **2.1 Identité Personnelle**
| Variable | Format Technique | Exemple Concret | Cas Particuliers |
|----------|------------------|-----------------|------------------|
| `{{customer_name}}` | `string` (trimmed) | `Jean Dupont` | Prénom + Nom, espaces nettoyés |
| `{{customer_first_name}}` | `string` | `Jean` | Peut être vide |
| `{{customer_last_name}}` | `string` | `Dupont` | Peut être vide |

#### **2.2 Informations de Contact**
| Variable | Format Technique | Exemple Concret | Cas Particuliers |
|----------|------------------|-----------------|------------------|
| `{{customer_email}}` | `email` validé | `jean.dupont@email.com` | Validé par WooCommerce |
| `{{customer_phone}}` | `string` (intl) | `+33 1 23 45 67 89` | Format international |

### 🏠 **Adresses - Formats & Exemples**

#### **Format d'Adresse Complet (`{{billing_address}}` / `{{shipping_address}}`)**
```html
<!-- Format HTML avec sauts de ligne -->
123 Rue de la Paix<br>
Appartement 5B<br>
75001 Paris, France
```

#### **3.1 Adresse de Facturation**
| Variable | Format Technique | Exemple Concret | Validation |
|----------|------------------|-----------------|------------------|
| `{{billing_address_1}}` | `string` (255 chars max) | `123 Rue de la Paix` | Champ WooCommerce standard |
| `{{billing_address_2}}` | `string` (optionnel) | `Appartement 5B` | Peut être vide |
| `{{billing_city}}` | `string` | `Paris` | Ville française |
| `{{billing_postcode}}` | `string` (5-10 chars) | `75001` | Format postal local |
| `{{billing_country}}` | `string` (ISO 3166-1 alpha-2) | `FR` | Code pays WooCommerce |

#### **3.2 Adresse de Livraison**
*Mêmes formats que facturation, mais peut différer du client*

### 💰 **Variables Financières - Formats & Exemples**

#### **Format Prix WooCommerce**
```php
// Format automatique selon réglages boutique
€1,234.56    // Français avec virgule
$1,234.56    // Anglais avec point
```

#### **4.1 Totaux Principaux**
| Variable | Format Technique | Exemple Concret | Calcul |
|----------|------------------|-----------------|------------------|
| `{{total}}` | `wc_price()` formaté | `€125.99` | `subtotal + tax + shipping - discount` |
| `{{subtotal}}` | `wc_price()` formaté | `€99.99` | Somme des items avant taxes |

#### **4.2 Détails Fiscaux**
| Variable | Format Technique | Exemple Concret | Calcul |
|----------|------------------|-----------------|------------------|
| `{{tax}}` | `wc_price()` formaté | `€26.00` | Taxe totale calculée |
| `{{shipping_total}}` | `wc_price()` formaté | `€15.00` | Frais de port TTC |
| `{{discount_total}}` | `wc_price()` formaté | `-€10.00` | Remises appliquées (négatif) |

### 📦 **Variables Produits - Formats Avancés**

#### **Structure d'un Item Complet**
```json
{
  "id": 123,
  "name": "T-shirt Premium - Rouge - L",
  "quantity": 2,
  "price": 25.99,
  "regular_price": 29.99,
  "sale_price": 25.99,
  "total": 51.98,
  "total_tax": 10.40,
  "subtotal": 51.98,
  "sku": "TSHIRT-RED-L",
  "product_id": 456,
  "variation_id": 789,
  "type": "variation",
  "variation_attributes": {
    "couleur": "Rouge",
    "taille": "L"
  }
}
```

#### **Formats par Type de Données**
| Type | Format Technique | Exemple | Validation |
|------|------------------|---------|------------|
| `price` | `float` (2 décimales) | `25.99` | Prix unitaire calculé |
| `quantity` | `int` | `2` | Quantité commandée |
| `sku` | `string` (alphanumérique) | `TSHIRT-RED-L` | Référence produit |
| `variation_attributes` | `object` | `{"couleur": "Rouge"}` | Attributs formatés |

### 📅 **Variables Système - Formats & Exemples**

#### **6.1 Dates Dynamiques**
| Variable | Format Technique | Exemple Concret | Calcul |
|----------|------------------|-----------------|------------------|
| `{{date}}` | `DD/MM/YYYY` | `22/10/2025` | `date('d/m/Y')` |
| `{{due_date}}` | `DD/MM/YYYY` | `21/11/2025` | `date('d/m/Y', strtotime('+30 days'))` |

---

## 🔄 **Exemples d'Utilisation Concrets**

### 📄 **Template Facture Standard**
```html
FACTURE N°{{order_number}}

Date: {{order_date}}
Client: {{customer_name}}
Email: {{customer_email}}

Adresse de facturation:
{{billing_address}}

Total HT: {{subtotal}}
TVA: {{tax}}
Total TTC: {{total}}
```

### 📧 **Template Email de Confirmation**
```html
Bonjour {{customer_first_name}},

Votre commande {{order_number}} du {{order_date}} a été confirmée.

Détails de livraison:
{{shipping_address}}

Montant total: {{total}}

Cordialement,
{{company_info}}
```

### 🏷️ **Template Bon de Livraison**
```html
BON DE LIVRAISON - {{order_number}}

Destinataire:
{{shipping_first_name}} {{shipping_last_name}}
{{shipping_address}}

Contenu de la commande:
[Liste des produits avec quantités]

Date d'émission: {{date}}
```

---

## ⚠️ **Cas Limites et Gestion d'Erreurs**

### **Données Manquantes**
| Scénario | Comportement | Solution |
|----------|--------------|----------|
| Client anonyme | Variables vides | `{{customer_name}}` → `""` |
| Adresse partielle | Champs vides | Reconstruction intelligente |
| Produit sans SKU | `sku` vide | Affichage conditionnel |

### **Formats Invalides**
| Problème | Détection | Fallback |
|----------|-----------|----------|
| Email mal formé | Validation WooCommerce | Non affiché |
| Prix négatif | Calcul incorrect | Vérification avant affichage |
| Date future | Commande invalide | Date actuelle |

### **Encodage et Caractères Spéciaux**
| Type | Gestion | Exemple |
|------|---------|---------|
| UTF-8 | Support complet | `José María` |
| HTML | Échappement | `<script>` → `&lt;script&gt;` |
| Emoji | Support natif | `📦🚚` |

---

## 🧪 **Tests de Validation**

### **Jeu de Données Test**
```json
{
  "order": {
    "id": 12345,
    "number": "CMD-2025-0123",
    "date": "2025-10-22 14:30:25",
    "status": "processing"
  },
  "customer": {
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean.dupont@email.com",
    "phone": "+33123456789"
  },
  "billing": {
    "address_1": "123 Rue de la Paix",
    "city": "Paris",
    "postcode": "75001",
    "country": "FR"
  },
  "totals": {
    "subtotal": 99.99,
    "tax": 20.00,
    "shipping": 5.00,
    "discount": 0.00,
    "total": 124.99
  }
}
```

### **Résultats Attendus**
- ✅ Toutes les variables se remplacent correctement
- ✅ Formats prix respectés (€99,99)
- ✅ Dates au
- ✅ Adresses avec sauts de ligne HTML

---

*Documentation complète des formats - Phase 2.3.3 terminée*

---

## 🚀 Utilisation dans les Templates

### 📄 **Syntaxe**
```php
// Dans le contenu des éléments texte
"Commande n°{{order_number}} du {{order_date}}"

// Dans les éléments dynamiques
"Client: {{customer_name}} - {{customer_email}}"
```

### 🔄 **Traitement**
1. **Récupération** : Variables extraites depuis l'objet WC_Order
2. **Formatage** : Application des formats appropriés (prix, dates)
3. **Sécurité** : Échappement automatique des contenus
4. **Fallbacks** : Valeurs par défaut pour données manquantes

---

## 🎨 **Variables de Style Dynamique - Phase 2.3.5**

### 🏷️ **Variables par Élément**

#### **7.1 product_table - Variables de Style Table**
| Variable | Format Technique | Exemple Concret | Origine Code | Description |
|----------|------------------|-----------------|--------------|-------------|
| `{{row_alternate_color}}` | `string` (hex color) | `#f9f9f9` ou `#ffffff` | `CanvasElement.jsx:1452` | Couleur alternée lignes pair/impair |
| `{{total_row_highlight}}` | `string` (CSS inline) | `font-weight: bold; background-color: #e8f4f8;` | `CanvasElement.jsx:1487` | Style spécial ligne total |
| `{{product_type_icon}}` | `string` (emoji/unicode) | 🛍️ / 💻 / 🔄 | `CanvasElement.jsx:1423` | Icône selon type produit |
| `{{quantity_badge_style}}` | `string` (CSS inline) | `background: green; color: white;` | `CanvasElement.jsx:1438` | Style badge quantité (vert >1, orange =1, rouge =0) |
| `{{discount_row_style}}` | `string` (CSS inline) | `color: #d32f2f; font-style: italic;` | Concept WooCommerce | Style ligne remise |
| `{{tax_row_style}}` | `string` (CSS inline) | `color: #666; font-size: 0.9em;` | Concept WooCommerce | Style ligne taxe |
| `{{shipping_row_style}}` | `string` (CSS inline) | `color: #1976d2; border-top: 1px solid #ddd;` | Concept WooCommerce | Style ligne frais de port |

#### **7.2 customer_info - Variables de Style Champs Client**
| Variable | Format Technique | Exemple Concret | Origine Code | Description |
|----------|------------------|-----------------|--------------|-------------|
| `{{field_label_style}}` | `string` (CSS inline) | `font-weight: bold; color: #666;` | `CanvasElement.jsx:1324` | Style des labels de champs |
| `{{field_value_style}}` | `string` (CSS inline) | `color: blue;` (email) / `color: green;` (téléphone) | `CanvasElement.jsx:1324` | Style des valeurs selon type champ |
| `{{address_block_style}}` | `string` (CSS inline) | `margin: 10px; padding: 5px; border: 1px solid #ddd;` | `CanvasElement.jsx:1324` | Style du bloc adresse complet |

#### **7.3 dynamic-text - Variables de Style Conditionnel**
| Variable | Format Technique | Exemple Concret | Origine Code | Description |
|----------|------------------|-----------------|--------------|-------------|
| `{{conditional_bold}}` | `string` (CSS inline) | `font-weight: bold;` | `CanvasElement.jsx:1389` | Gras si condition remplie (montant > 100€) |
| `{{conditional_color}}` | `string` (hex color) | `color: red;` (négatif) / `color: green;` (positif) | `CanvasElement.jsx:1389` | Couleur selon valeur numérique |
| `{{currency_format_style}}` | `string` (CSS inline) | `color: green;` (€) / `color: blue;` ($) | `CanvasElement.jsx:1389` | Style selon devise utilisée |
| `{{date_format_style}}` | `string` (CSS inline) | `color: gray;` (>30 jours) / `color: black;` (<7 jours) | `CanvasElement.jsx:1389` | Style date selon ancienneté |

#### **7.4 mentions - Variables de Style Légal**
| Variable | Format Technique | Exemple Concret | Origine Code | Description |
|----------|------------------|-----------------|--------------|-------------|
| `{{legal_field_style}}` | `string` (CSS inline) | `font-family: monospace;` (SIRET) / `font-weight: bold;` (TVA) | `CanvasElement.jsx:1521` | Style selon type de champ légal |
| `{{separator_style}}` | `string` (CSS inline) | `border-top: 1px solid #ddd; margin: 5px 0;` | `CanvasElement.jsx:1521` | Style des séparateurs entre mentions |
| `{{footer_style}}` | `string` (CSS inline) | `border: 1px solid #ccc; padding: 10px; font-size: 0.8em;` | `CanvasElement.jsx:1521` | Style du bloc mentions légales complet |

#### **7.5 company_info - Variables de Style Société**
| Variable | Format Technique | Exemple Concret | Origine Code | Description |
|----------|------------------|-----------------|--------------|-------------|
| `{{company_field_style}}` | `string` (CSS inline) | `font-weight: bold;` (nom) / `font-style: italic;` (contact) | `CanvasElement.jsx:1298` | Style selon type de champ société |
| `{{template_style}}` | `string` (CSS inline) | `text-align: center; border: 1px solid #eee;` | `CanvasElement.jsx:1298` | Style global selon template choisi |

#### **7.6 order_number - Variables de Style Statut**
| Variable | Format Technique | Exemple Concret | Origine Code | Description |
|----------|------------------|-----------------|--------------|-------------|
| `{{status_badge_style}}` | `string` (CSS inline) | `background: green; color: white;` (payé) / `background: red; color: white;` (impayé) | `CanvasElement.jsx:1356` | Style badge selon statut commande |
| `{{date_style}}` | `string` (CSS inline) | `color: gray;` (>7 jours) / `color: black;` (<7 jours) | `CanvasElement.jsx:1356` | Style date selon ancienneté commande |

---

*Variables de style dynamique ajoutées - Phase 2.3.5 terminée*

---

## 🎯 Prochaines Étapes

**Phase 2.3.2** : Classifier les variables par catégories (détailler les sous-catégories)
**Phase 2.3.3** : Documenter format et exemples de chaque variable
**Phase 2.3.4** : Créer guide d'utilisation pour les variables

---

*Documentation générée automatiquement depuis le code source - Phase 2.3.1 terminée*