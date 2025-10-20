# 📖 Guide d'utilisation - WP PDF Builder Pro

## Vue d'ensemble

WP PDF Builder Pro est un plugin WordPress avancé pour créer des templates PDF personnalisés avec un éditeur visuel intuitif. Ce guide vous explique comment utiliser toutes les fonctionnalités de l'interface.

## 🏠 Interface principale

### Menu d'administration
Après activation, accédez à **WP PDF Builder Pro** dans le menu latéral WordPress. L'interface principale comprend :

- **Tableau de bord** : Vue d'ensemble des templates et statistiques
- **Templates PDF** : Gestion et création des templates
- **Générateur** : Éditeur visuel pour créer des PDFs
- **Paramètres** : Configuration globale du plugin

### Tableau de bord
Le tableau de bord affiche :
- Nombre total de templates actifs
- Statistiques de génération PDF
- Templates récemment modifiés
- Alertes et notifications système

## 📄 Gestion des templates

### Créer un nouveau template
1. Allez dans **Templates PDF > Ajouter nouveau**
2. Donnez un nom à votre template
3. Choisissez un type : Facture, Devis, Rapport, etc.
4. Cliquez sur **Créer**

### Éditer un template existant
1. Dans la liste des templates, cliquez sur **Modifier**
2. Utilisez l'éditeur visuel pour apporter des changements
3. Sauvegardez automatiquement ou manuellement

### Dupliquer un template
- Survolez un template dans la liste
- Cliquez sur **Dupliquer**
- Modifiez le nom et personnalisez

### Supprimer un template
- Cochez les templates à supprimer
- Utilisez **Actions groupées > Supprimer**
- Confirmez la suppression

## 🎨 Éditeur visuel

### Canevas de conception
L'éditeur visuel comprend :
- **Barre d'outils** : Éléments à ajouter (texte, images, tableaux)
- **Canevas** : Zone de conception du PDF
- **Propriétés** : Paramètres de l'élément sélectionné
- **Aperçu** : Visualisation en temps réel

### Éléments disponibles

#### Texte
- **Texte statique** : Contenu fixe
- **Texte dynamique** : Variables (nom client, date, etc.)
- **Texte conditionnel** : Affiché selon des conditions

#### Images et médias
- **Images fixes** : Logos, signatures
- **Images dynamiques** : Photos de produits
- **Codes-barres/QR codes** : Génération automatique

#### Tableaux et données
- **Tableaux statiques** : Structure fixe
- **Tableaux dynamiques** : Données de commandes/produits
- **Graphiques** : Diagrammes et statistiques

### Variables dynamiques
Utilisez ces variables dans vos templates :
- `{{customer_name}}` - Nom du client
- `{{order_number}}` - Numéro de commande
- `{{order_date}}` - Date de commande
- `{{total_amount}}` - Montant total
- `{{product_list}}` - Liste des produits

## ⚙️ Configuration

### Paramètres généraux
- **Format de papier** : A4, Letter, etc.
- **Orientation** : Portrait/Paysage
- **Marges** : Personnalisation des marges
- **Police par défaut** : Choix de la police

### Paramètres avancés
- **Compression PDF** : Optimisation de la taille
- **Sécurité** : Protection par mot de passe
- **Métadonnées** : Informations du document

## 🔗 Intégrations

### WooCommerce
- Génération automatique de factures
- Templates personnalisés par produit
- Envoi automatique par email

### CRM et applications tierces
- Synchronisation des données clients
- Export automatique vers HubSpot/Salesforce
- Webhooks personnalisés

## 📊 Génération et export

### Génération manuelle
1. Sélectionnez un template
2. Choisissez les données source
3. Cliquez sur **Générer PDF**
4. Téléchargez ou envoyez par email

### Génération automatique
- **Déclencheurs** : Nouvelle commande, paiement, etc.
- **Règles conditionnelles** : Selon le montant, produit, etc.
- **Envoi programmé** : Emails automatiques

## 🔍 Dépannage

### Problèmes courants
- **PDF vide** : Vérifiez les variables dynamiques
- **Mise en page cassée** : Contrôlez les marges et tailles
- **Polices non chargées** : Vérifiez les permissions des fichiers

### Logs et débogage
- Accédez aux logs dans **Outils > Logs PDF**
- Activez le mode débogage dans les paramètres
- Consultez la console développeur pour les erreurs JavaScript

## 💡 Astuces et bonnes pratiques

- Utilisez des noms descriptifs pour vos templates
- Testez régulièrement vos PDFs avec des données réelles
- Sauvegardez vos templates avant les modifications majeures
- Utilisez les variables dynamiques pour l'automatisation
- Optimisez les images pour réduire la taille des PDFs

---

*Guide mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\user\guides\usage.md