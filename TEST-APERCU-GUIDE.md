# 🧪 Guide de Test - Aperçu PDF Metabox

## ✅ Ce qui a été implémenté

L'action AJAX `pdf_builder_unified_preview` a été ajoutée à la classe `PDF_Builder_Admin` avec :

- ✅ Vérification des permissions WooCommerce
- ✅ Validation du nonce de sécurité
- ✅ Chargement automatique du template (manuel ou auto-détection)
- ✅ Génération d'aperçu PDF avec le contrôleur existant
- ✅ Sauvegarde temporaire dans `/uploads/pdf-builder-cache/previews/`
- ✅ Retour d'URL accessible pour l'aperçu

## 🧪 Comment tester

### 1. Activer le plugin
Assurez-vous que PDF Builder Pro est activé dans WordPress.

### 2. Aller dans WooCommerce > Commandes
Ouvrez une commande existante ou créez-en une nouvelle.

### 3. Vérifier le metabox
Le metabox "PDF Builder Pro" devrait apparaître avec :
- Sélecteur de template
- Bouton "🔍 Aperçu" (preview)
- Bouton "📄 Générer PDF"

### 4. Tester l'aperçu
1. Cliquez sur "🔍 Aperçu"
2. Le bouton devrait afficher "Chargement..."
3. Un nouvel onglet devrait s'ouvrir avec l'aperçu PDF

### 5. Vérifier les logs
En cas d'erreur, vérifiez :
- Console navigateur (F12 > Console)
- Logs PHP : `/uploads/pdf-builder-logs/`
- Logs serveur web

## 🔧 Dépannage

### Erreur "Permissions insuffisantes"
- Vérifiez que l'utilisateur a le rôle `manage_woocommerce`

### Erreur "Aucun template trouvé"
- Créez au moins un template dans PDF Builder > Templates
- Configurez les templates par statut dans Paramètres

### Erreur "Erreur lors de la génération"
- Vérifiez que TCPDF est installé : `lib/tcpdf/`
- Vérifiez les permissions d'écriture : `/uploads/pdf-builder-cache/`

### PDF ne s'ouvre pas
- Vérifiez l'URL générée dans la réponse AJAX
- Vérifiez que le fichier existe : `/uploads/pdf-builder-cache/previews/`

## 📊 Fonctionnalités de l'aperçu

- **Dimensions optimisées** : 400x566px (format réduit)
- **Cache temporaire** : Fichiers nettoyés automatiquement
- **Détection automatique** : Template basé sur le statut de commande
- **Sécurité** : Nonce et permissions vérifiées

## 🎯 Prochaines étapes

Une fois l'aperçu fonctionnel, nous pourrons :
1. Optimiser les performances (mise en cache)
2. Ajouter des options d'aperçu (format, qualité)
3. Intégrer avec le canvas editor pour prévisualisation temps réel