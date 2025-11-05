# Template Pilote - Facture Démo

## 📄 Description
Template JSON complet pour une facture professionnelle avec tous les éléments nécessaires.

## 🎨 Caractéristiques
- **Format** : A4 (794x1123px)
- **Éléments** : 15 composants
- **Style** : Design professionnel avec en-tête bleue
- **Variables dynamiques** : Support complet des données WooCommerce

## 📋 Éléments inclus

### En-tête
- Fond bleu professionnel
- Titre "FACTURE" en blanc
- Numéro de facture dynamique `[NUMERO]`
- Date de facture `[DATE]`

### Informations
- **Entreprise** : Nom, adresse, téléphone, email
- **Client** : Nom, adresse, email

### Corps
- Ligne de séparation
- Titre "DÉTAIL DES PRESTATIONS"
- **Tableau des produits** avec colonnes :
  - Description (45%)
  - Quantité (15%)
  - Prix HT (20%)
  - Total HT (20%)

### Pied de page
- Totaux : Sous-total HT, TVA, Total TTC
- Conditions de paiement
- Mentions légales
- Espace logo

## 🚀 Comment utiliser

1. **Copiez le JSON** depuis `generate-template-pilote.php`
2. **Admin WordPress** > PDF Builder > Templates
3. **Nouveau template** > Collez le JSON
4. **Sauvegardez** et testez dans l'éditeur React

## 🔧 Variables disponibles

### Commande WooCommerce
- `[NUMERO]` - Numéro de commande
- `[DATE]` - Date de la commande
- `[MONTANT_TOTAL]` - Total TTC
- `[SOUS_TOTAL]` - Sous-total HT
- `[TVA]` - Montant TVA

### Entreprise
- `[NOM_ENTREPRISE]` - Nom de l'entreprise
- `[ADRESSE]` - Adresse complète
- `[TEL]` - Téléphone
- `[EMAIL]` - Email

### Client
- `[NOM_CLIENT]` - Nom du client
- `[ADRESSE_CLIENT]` - Adresse du client
- `[EMAIL_CLIENT]` - Email du client

## 📁 Fichiers
- `facture-pilote.json` - Template JSON source
- `generate-template-pilote.php` - Générateur du JSON
- `import-template-pilote.php` - Script d'import (nécessite WordPress)

## 🎯 Résultat attendu
Une facture PDF professionnelle avec :
- Mise en page claire et structurée
- Couleurs cohérentes (bleu #007cba)
- Typographie adaptée
- Espaces réservés pour toutes les données dynamiques