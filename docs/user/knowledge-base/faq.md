# ❓ FAQ - Questions Fréquemment Posées

Bienvenue dans la FAQ de WP PDF Builder Pro ! Trouvez rapidement des réponses aux questions les plus courantes.

## 🚀 Installation et configuration

### Comment installer WP PDF Builder Pro ?

**Réponse :** L'installation se fait en 3 étapes simples :

1. Téléchargez le fichier ZIP du plugin
2. Dans WordPress : **Extensions > Ajouter > Téléverser une extension**
3. Activez le plugin et accédez à **WP PDF Builder Pro** dans le menu

### Le plugin est-il compatible avec mon thème WordPress ?

**Réponse :** WP PDF Builder Pro est compatible avec la plupart des thèmes WordPress modernes. Cependant, certains thèmes très anciens peuvent nécessiter des ajustements. Testez toujours vos templates PDF avec votre thème actif.

### Puis-je utiliser le plugin avec WooCommerce ?

**Réponse :** Oui ! WP PDF Builder Pro s'intègre parfaitement avec WooCommerce pour générer automatiquement des factures, devis et bons de livraison.

## 🎨 Création de templates

### Comment créer mon premier template PDF ?

**Réponse :** Suivez ces étapes :

1. Allez dans **WP PDF Builder Pro > Générateur**
2. Cliquez sur **Nouveau template**
3. Utilisez l'éditeur visuel pour ajouter des éléments
4. Sauvegardez et testez

Consultez notre [tutoriel débutant](../tutorials/beginner.md) pour un guide détaillé.

### Quels éléments puis-je ajouter à mes templates ?

**Réponse :** L'éditeur visuel propose :

- **Texte** : Statique ou dynamique (variables)
- **Images** : Logos, photos, signatures
- **Tableaux** : Données structurées
- **Formes** : Lignes, rectangles, cercles
- **Codes-barres/QR codes** : Génération automatique

### Les variables dynamiques, comment ça marche ?

**Réponse :** Les variables sont automatiquement remplacées par des données réelles :

- `{{customer_name}}` → "Jean Dupont"
- `{{order_total}}` → "150,00 €"
- `{{order_date}}` → "15/10/2025"

Une liste complète est disponible dans la documentation développeur.

## ⚙️ Configuration et paramètres

### Comment changer le format de papier ?

**Réponse :** Dans les paramètres du template :

1. Ouvrez votre template en édition
2. Onglet **Paramètres**
3. Section **Format de page**
4. Choisissez A4, Letter, ou dimensions personnalisées

### Puis-je protéger mes PDFs par mot de passe ?

**Réponse :** Oui, dans les paramètres avancés :

1. **WP PDF Builder Pro > Paramètres**
2. Onglet **Sécurité**
3. Activez **Protection PDF**
4. Définissez le mot de passe

### Comment optimiser la taille des fichiers PDF ?

**Réponse :** Plusieurs options :

- Compressez les images avant l'import
- Utilisez le format JPG au lieu de PNG pour les photos
- Activez la compression PDF dans les paramètres
- Évitez les polices personnalisées lourdes

## 🔗 Intégrations

### Comment connecter avec mon CRM ?

**Réponse :** WP PDF Builder Pro supporte :

- **HubSpot** : API native
- **Salesforce** : Connecteur dédié
- **CRM personnalisé** : Webhooks et API REST

Consultez le guide d'intégration pour la configuration détaillée.

### L'automatisation des emails fonctionne-t-elle ?

**Réponse :** Oui ! Configurez des règles :

1. **WP PDF Builder Pro > Automatisations**
2. Créez une nouvelle règle
3. Déclencheur : "Nouvelle commande"
4. Action : "Envoyer PDF par email"

## 🐛 Dépannage

### Mon PDF s'affiche vide, pourquoi ?

**Réponse :** Causes possibles :

- **Variables non définies** : Vérifiez la syntaxe `{{variable}}`
- **Données manquantes** : Assurez-vous que les données source existent
- **Permissions** : Vérifiez les droits d'écriture sur le serveur

Activez les logs de débogage pour identifier le problème.

### Les images ne s'affichent pas dans le PDF

**Réponse :** Solutions :

1. Vérifiez le format (JPG, PNG, GIF supportés)
2. Contrôlez la taille (< 5MB recommandé)
3. Assurez-vous que l'URL de l'image est accessible
4. Testez avec une image simple d'abord

### Le texte est coupé ou mal positionné

**Réponse :** Ajustements à faire :

- Vérifiez les marges du document (minimum 10mm)
- Ajustez la taille de police (10-12pt recommandé)
- Utilisez des sauts de ligne explicites
- Testez l'aperçu avant génération

## 📊 Performance et optimisation

### Combien de temps prend la génération d'un PDF ?

**Réponse :** Temps typiques :

- **Simple** : 2-5 secondes
- **Complexe** (beaucoup d'images) : 10-30 secondes
- **Facteurs** : Taille des images, complexité du template, charge serveur

### Puis-je générer plusieurs PDFs en batch ?

**Réponse :** Oui, via l'API ou les automatisations :

- **API REST** : Endpoint `/wp-json/wp-pdf-builder/v1/generate-batch`
- **Interface** : Outil "Génération groupée" dans l'admin
- **Automatisation** : Règles pour les commandes multiples

### Quelle est la limite de templates par site ?

**Réponse :** Pas de limite technique, mais recommandations :

- **Petit site** : 10-20 templates
- **Site e-commerce** : 50-100 templates
- **Entreprise** : Templates illimités

## 💰 Licences et support

### Quels sont les plans disponibles ?

**Réponse :** Trois niveaux :

- **Gratuit** : 5 templates, fonctionnalités de base
- **Pro** : Templates illimités, intégrations avancées
- **Enterprise** : Support prioritaire, fonctionnalités personnalisées

### Comment obtenir du support ?

**Réponse :** Plusieurs canaux :

1. **Documentation** : Consultez d'abord cette FAQ
2. **Forum communautaire** : Échangez avec d'autres utilisateurs
3. **Support email** : support@wp-pdf-builder-pro.com
4. **Chat en ligne** : Disponible pour les licences Pro+

### Puis-je annuler mon abonnement ?

**Réponse :** Oui, à tout moment :

- Accédez à votre compte client
- Section "Abonnements"
- Cliquez sur "Annuler"
- Vos templates restent accessibles

## 🔄 Mises à jour

### Comment mettre à jour le plugin ?

**Réponse :** Mises à jour automatiques :

1. WordPress détecte les mises à jour disponibles
2. **Tableau de bord > Mises à jour**
3. Cochez WP PDF Builder Pro
4. Cliquez sur "Mettre à jour"

### Les mises à jour sont-elles payantes ?

**Réponse :** Selon votre licence :

- **Gratuit** : Mises à jour mineures gratuites
- **Pro/Enterprise** : Toutes les mises à jour incluses
- **Support** : 1 an de support et mises à jour

---

*FAQ mise à jour le 20 octobre 2025*

**N'avez pas trouvé votre réponse ?** Contactez notre [support](../knowledge-base/support.md) ou consultez la [base de connaissances complète](../knowledge-base/).</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\user\knowledge-base\faq.md