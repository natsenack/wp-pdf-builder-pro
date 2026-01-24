# 🎯 Tutoriel Débutant : Votre premier PDF avec WP PDF Builder Pro

Bienvenue ! Ce tutoriel vous guide pas à pas pour créer votre premier template PDF. Aucune expérience préalable requise - suivez simplement les étapes.

## 📋 Prérequis

- WordPress installé et configuré
- Plugin WP PDF Builder Pro activé
- Accès administrateur WordPress
- 10-15 minutes de temps libre

## 🚀 Étape 1 : Accès au générateur

1. **Connectez-vous** à votre administration WordPress
2. **Naviguez** vers le menu latéral : **WP PDF Builder Pro**
3. **Cliquez** sur **Générateur** ou **Templates PDF**
4. **Sélectionnez** **Ajouter nouveau** ou **Créer un template**

![Accès au générateur](images/generator-access.png)

## 🎨 Étape 2 : Configuration de base

### Paramètres du document
- **Nom du template** : "Ma première facture" (ou autre nom descriptif)
- **Type** : Facture (ou choisissez selon vos besoins)
- **Format** : A4 (recommandé pour commencer)
- **Orientation** : Portrait

### Interface de l'éditeur
L'écran se divise en 3 zones principales :
- **Barre d'outils** (gauche) : Éléments à ajouter
- **Canevas** (centre) : Zone de conception
- **Propriétés** (droite) : Paramètres de l'élément sélectionné

## 🏗️ Étape 3 : Création de l'en-tête

### Ajouter le logo
1. Cliquez sur **Ajouter élément > Image**
2. Téléchargez votre logo entreprise
3. Positionnez-le en haut à gauche
4. Redimensionnez à 150x50px environ

### Ajouter les informations entreprise
1. Cliquez sur **Ajouter élément > Texte**
2. Tapez vos coordonnées :
   ```
   Ma Société SARL
   123 Rue de l'Entreprise
   75001 Paris
   Tél : 01 23 45 67 89
   Email : contact@masociete.com
   ```

3. Positionnez à droite du logo

### Ajouter numéro et date
1. **Numéro de facture** :
   - Ajoutez un élément texte : "Facture N° {{invoice_number}}"
   - Position : En haut à droite

2. **Date** :
   - Élément texte : "Date : {{current_date}}"
   - Position : Sous le numéro

## 👤 Étape 4 : Section client

### Créer la zone client
1. Ajoutez un **rectangle** pour encadrer la section
2. Couleur : Gris clair (#f5f5f5)

### Informations client
Ajoutez ces champs texte :
```
Facturé à :
{{customer_name}}
{{customer_address}}
{{customer_city}} {{customer_postal_code}}
{{customer_email}}
```

Position : En haut à gauche, sous l'en-tête

## 📦 Étape 5 : Détails de la commande

### Créer un tableau
1. Cliquez sur **Ajouter élément > Tableau**
2. Configurez 4 colonnes :
   - **Produit** (largeur 40%)
   - **Quantité** (20%)
   - **Prix** (20%)
   - **Total** (20%)

### En-têtes du tableau
Remplissez la première ligne :
| Produit | Quantité | Prix | Total |
|---------|----------|------|-------|

### Lignes de produits (dynamiques)
Utilisez ces variables pour chaque ligne :
- Produit : `{{product_name}}`
- Quantité : `{{product_quantity}}`
- Prix : `{{product_price}}`
- Total : `{{product_total}}`

## 💰 Étape 6 : Totaux et paiement

### Calculs automatiques
Ajoutez ces éléments sous le tableau :

**Sous-total :** `{{subtotal}}`
**TVA (20%) :** `{{tax_amount}}`
**Total TTC :** `{{total_amount}}`

### Conditions de paiement
Ajoutez un texte statique :
```
Conditions de paiement : 30 jours nets
Mode de paiement : Virement bancaire
IBAN : FR76 1234 5678 9012 3456 7890 123
```

## 📄 Étape 7 : Pied de page

### Mentions légales
Ajoutez en bas de page :
```
SARL au capital de 10 000€
RCS Paris B 123 456 789
TVA Intracommunautaire : FR 12 345 678 901
```

### Coordonnées
```
Contact : contact@masociete.com | Tél : 01 23 45 67 89
```

## 🎨 Étape 8 : Mise en forme finale

### Styles et couleurs
- **Couleurs** : Utilisez votre charte graphique
- **Polices** : Arial ou Times New Roman (10-12pt)
- **Alignements** : Gauche pour le texte, droite pour les montants

### Espacement
- **Marges** : 15mm autour du document
- **Interligne** : 1.2 pour la lisibilité
- **Espacement sections** : 10mm entre chaque bloc

## 🧪 Étape 9 : Test et sauvegarde

### Aperçu
1. Cliquez sur l'onglet **Aperçu**
2. Vérifiez la mise en page
3. Testez avec des données d'exemple

### Génération test
1. Cliquez sur **Générer PDF**
2. Ouvrez le fichier généré
3. Vérifiez l'impression

### Sauvegarde
1. Cliquez sur **Sauvegarder**
2. Votre template est maintenant disponible dans la liste

## 🎉 Félicitations !

Vous venez de créer votre premier template PDF ! 🎊

### Prochaines étapes
- **Testez** avec des données réelles
- **Personnalisez** les couleurs et polices
- **Ajoutez** des éléments avancés (codes-barres, etc.)
- **Créez** d'autres types de documents

### Ressources supplémentaires
- 📖 [Guide complet des templates](../guides/templates.md)
- ❓ [FAQ](../knowledge-base/faq.md)
- 🆘 [Support](../knowledge-base/support.md)

---

*Ce tutoriel a été créé pour vous guider pas à pas. N'hésitez pas à le refaire plusieurs fois pour maîtriser l'outil !*

*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\user\tutorials\beginner.md