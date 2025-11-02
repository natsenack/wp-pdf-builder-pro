# 🎯 API Preview 1.4 - Intégration Complète

## Vue d'ensemble

L'API Preview 1.4 est maintenant **complètement intégrée** dans votre plugin PDF Builder Pro. Cette implémentation fournit une expérience utilisateur fluide pour générer et afficher des aperçus PDF en temps réel.

## 📁 Fichiers créés

### 1. `assets/js/pdf-preview-api-client.js`
Client JavaScript complet pour l'API Preview 1.4
- ✅ Gestion des requêtes AJAX sécurisées
- ✅ Cache intelligent côté client
- ✅ Interface utilisateur (modal d'aperçu)
- ✅ Gestion d'erreurs et indicateurs de chargement

### 2. `assets/js/pdf-preview-integration.js`
Intégration complète dans l'interface utilisateur
- ✅ Boutons d'aperçu dans l'éditeur
- ✅ Intégration metabox WooCommerce
- ✅ Raccourcis clavier (Ctrl+P)
- ✅ Détection automatique du contexte

## 🚀 Utilisation Rapide

### Dans l'Éditeur (Canvas)
```javascript
// Automatique - bouton "👁️ Aperçu" ajouté dans la barre d'outils
// Ou raccourci clavier: Ctrl+P (Cmd+P sur Mac)

// Manuellement:
await generateEditorPreview(templateData, { quality: 150, format: 'png' });
```

### Dans la Metabox WooCommerce
```javascript
// Automatique - boutons ajoutés dans la metabox
// Ou raccourci clavier: Ctrl+P

// Manuellement:
await generateOrderPreview(templateData, orderId, { quality: 150, format: 'png' });
```

### Détection Automatique
```javascript
// Fonctionne dans les deux contextes:
await generateQuickPreview(templateData, orderId);
```

## 🎛️ Fonctionnalités

### Aperçu Éditeur
- **Données fictives** : Jean Dupont, commande exemple
- **Rendu rapide** : Canvas avec données d'exemple
- **Bouton intégré** : Dans la barre d'outils de l'éditeur
- **Raccourci** : `Ctrl+P` pour aperçu instantané

### Aperçu Metabox
- **Données réelles** : Depuis la commande WooCommerce
- **Variables dynamiques** : `{{customer_name}}`, `{{order_total}}`, etc.
- **Boutons multiples** :
  - 👁️ **Aperçu Image** : Screenshot rapide
  - 📄 **Générer PDF** : PDF complet
- **Actions** : Télécharger, Imprimer, Régénérer

### Interface Utilisateur
- **Modal responsive** : S'adapte à toutes les tailles d'écran
- **Zoom intelligent** : Ajustement automatique
- **Navigation** : Facile à fermer (× ou clic extérieur)
- **Actions contextuelles** : Télécharger, Imprimer, Régénérer

### Performance
- **Cache intelligent** : Évite les régénérations inutiles
- **Compression GZIP** : Réponses optimisées
- **Rate limiting** : Protection contre les abus
- **Indicateurs visuels** : Loading states et feedback

## 🔧 Configuration

### Variables Globales Requises
```javascript
// Automatiquement configuré via wp_localize_script
window.pdfBuilderAjax = {
    ajaxurl: '/wp-admin/admin-ajax.php',
    nonce: 'your-nonce-here',
    version: '1.1.0'
};
```

### Classes CSS pour le Styling
```css
/* Modal d'aperçu */
#pdf-preview-modal {
    /* Styles automatiques */
}

/* Indicateur de chargement */
#pdf-preview-loader {
    /* Spinner animé */
}

/* Boutons d'action */
#pdf-preview-actions button {
    /* Styles des boutons */
}
```

## 🔒 Sécurité

### Côté Client
- ✅ **Nonces WordPress** : Protection CSRF
- ✅ **Sanitisation** : Toutes les entrées nettoyées
- ✅ **Validation** : Types et formats vérifiés

### Côté Serveur (API)
- ✅ **Rate limiting** : 10 req/minute par utilisateur
- ✅ **Permissions** : Vérification des rôles utilisateur
- ✅ **Logging** : Toutes les actions tracées
- ✅ **Validation** : Données et contexte vérifiés

## 📊 Métriques et Monitoring

### Logs Disponibles
```php
// En mode debug (WP_DEBUG = true)
[PHP] Script URL: /wp-content/plugins/wp-pdf-builder-pro/assets/js/dist/pdf-builder-admin.js
[PHP] Script enqueued successfully with version: 1.1.0-20251102
[JS] 📤 Envoi requête preview éditeur...
[JS] ✅ Aperçu éditeur généré: {...}
[JS] 🖼️ Aperçu affiché: /wp-content/uploads/cache/wp-pdf-builder-previews/abc123.png
```

### Métriques Performance
- **Temps de génération** : Tracké automatiquement
- **Taux de succès** : Succès vs erreurs
- **Cache hits** : Utilisation du cache
- **Rate limits** : Requêtes rejetées

## 🐛 Dépannage

### Problèmes Courants

#### "Classe PreviewImageAPI non trouvée"
```bash
# Vérifier que l'autoloader fonctionne
php -r "require 'plugin/core/autoloader.php'; echo class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI') ? 'OK' : 'ERREUR';"
```

#### "Endpoint AJAX inaccessible"
```javascript
// Vérifier la configuration
console.log('Endpoint:', window.pdfBuilderAjax?.ajaxurl);
console.log('Nonce:', window.pdfBuilderAjax?.nonce);
```

#### "Aperçu ne s'affiche pas"
```javascript
// Vérifier les erreurs réseau
await generateEditorPreview(templateData).catch(console.error);
```

### Debug Mode
```php
// Activer dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 🎯 Prochaines Étapes

### Phase 4.2 (Tests)
- [ ] Tests unitaires API
- [ ] Tests d'intégration UI
- [ ] Tests de performance
- [ ] Tests de sécurité

### Phase 4.3 (Optimisations)
- [ ] Lazy loading des aperçus
- [ ] Préchargement intelligent
- [ ] Cache prédictif
- [ ] Compression avancée

### Phase 5.0 (Nouvelles Fonctionnalités)
- [ ] Aperçu temps réel (live preview)
- [ ] Annotations et commentaires
- [ ] Partage d'aperçus
- [ ] Historique des versions

## 📞 Support

Pour toute question concernant l'API Preview 1.4 :
1. Vérifiez les logs de debug
2. Testez avec les exemples fournis
3. Consultez la documentation API
4. Ouvrez une issue sur GitHub

---

**🎉 L'API Preview 1.4 est maintenant opérationnelle et prête à offrir une expérience utilisateur exceptionnelle !**