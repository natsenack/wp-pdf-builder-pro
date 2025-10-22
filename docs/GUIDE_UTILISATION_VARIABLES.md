# 📋 Guide d'Utilisation des Variables WooCommerce

**PDF Builder Pro - Phase 2.3.4**  
*Guide rapide pour développeurs et utilisateurs*

---

## 🎯 **Règles d'Usage Générales**

### **Syntaxe des Variables**
```html
{{variable_name}}  <!-- Variable simple -->
{{VARIABLE_NAME}}  <!-- Non sensible à la casse -->
```

### **Quand utiliser chaque variable**

| **Contexte** | **Variables Recommandées** | **Exemple d'usage** |
|--------------|----------------------------|-------------------|
| **Facture** | `order_number`, `order_date`, `customer_*`, `billing_*`, `total`, `tax` | Document officiel avec toutes les données client |
| **Bon de livraison** | `shipping_*`, `order_number`, `customer_name` | Adresse de livraison + référence commande |
| **Email confirmation** | `customer_first_name`, `order_number`, `total` | Communication personnalisée |
| **Étiquette** | `customer_name`, `shipping_address` | Informations minimales pour expédition |

---

## 📄 **Templates Prédéfinis**

### **Template Facture Standard**
```html
<h1>FACTURE N°{{order_number}}</h1>

<strong>Émise le :</strong> {{order_date}}<br>
<strong>Statut :</strong> {{order_status}}

<h2>Client</h2>
{{customer_name}}<br>
{{customer_email}}<br>
{{customer_phone}}

<h2>Adresse de facturation</h2>
{{billing_address}}

<h2>Totaux</h2>
Sous-total HT : {{subtotal}}<br>
TVA : {{tax}}<br>
<strong>Total TTC : {{total}}</strong>
```

### **Template Bon de Livraison**
```html
<h1>BON DE LIVRAISON</h1>

<strong>Commande N° :</strong> {{order_number}}<br>
<strong>Date d'émission :</strong> {{date}}

<h2>Destinataire</h2>
{{shipping_first_name}} {{shipping_last_name}}<br>
{{shipping_address}}

<h2>Informations complémentaires</h2>
Email : {{customer_email}}<br>
Téléphone : {{customer_phone}}
```

### **Template Email de Confirmation**
```html
Bonjour {{customer_first_name}},

Votre commande {{order_number}} du {{order_date}} a été confirmée.

<strong>Détails de la commande :</strong>
- Total : {{total}}
- Adresse de livraison : {{shipping_city}}, {{shipping_country}}

Nous vous tiendrons informé de l'évolution de votre commande.

Cordialement,<br>
{{company_info}}
```

---

## ⚠️ **Erreurs Possibles et Solutions**

### **Variable non remplacée**
```
Cause : Variable inconnue ou mal orthographiée
Solution : Vérifier l'orthographe exacte dans VARIABLES_WOOCOMMERCE_DISPONIBLES.md
```

### **Données vides**
```
Cause : Commande sans client ou données manquantes
Solution : Variables optionnelles - gérer avec CSS (display:none) ou texte par défaut
```

### **Format incorrect**
```
Cause : Attente format différent (date US vs FR)
Solution : Utiliser les variables avec format spécifique (order_date vs order_date_time)
```

### **Performance lente**
```
Cause : Trop de variables dans un template complexe
Solution : Optimiser le nombre de variables, utiliser du cache
```

---

## 🔧 **Référence Rapide Développeur**

### **Variables Obligatoires (toujours présentes)**
- `{{order_id}}` - ID technique
- `{{order_number}}` - Numéro formaté
- `{{order_date}}` - Date au format JJ/MM/AAAA
- `{{total}}` - Montant total TTC

### **Variables Conditionnelles**
- `{{customer_name}}` - Vide si commande anonyme
- `{{shipping_address}}` - Différent de `billing_address`
- `{{company_info}}` - Selon configuration WooCommerce

### **Variables Calculées**
- `{{subtotal}}` = Prix des produits HT
- `{{tax}}` = TVA totale calculée
- `{{shipping_total}}` = Frais de port TTC
- `{{discount_total}}` = Remises appliquées (négatif)

### **Formatage Automatique**
- **Prix** : `wc_price()` avec devise (€, $, etc.)
- **Dates** : Format français JJ/MM/AAAA
- **Adresses** : HTML avec `<br>` pour sauts de ligne
- **Texte** : Échappé pour sécurité

---

## 🧪 **Tests de Validation**

### **Commande de test**
```bash
# Exécuter les tests d'intégration
php tests/unit/VariablesIntegrationTest.php
```

### **Résultats attendus**
- ✅ 9/9 tests de format passés
- ✅ Sécurité XSS validée
- ✅ Performance < 1ms pour 100 variables
- ✅ Gestion des données manquantes

---

## 📞 **Support et Maintenance**

### **Mise à jour des variables**
- Nouvelles variables ajoutées dans `VARIABLES_WOOCOMMERCE_DISPONIBLES.md`
- Tests mis à jour automatiquement
- Compatibilité ascendante garantie

### **Signaler un problème**
1. Vérifier la documentation complète
2. Tester avec les données d'exemple
3. Ouvrir un ticket avec template et données de test

---

*Guide créé automatiquement - Version 2.3.4*