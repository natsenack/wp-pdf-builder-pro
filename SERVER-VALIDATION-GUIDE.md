# PDF Builder Pro - Guide Validation Serveur

## 🎯 Vue d'ensemble

Après déploiement du plugin PDF Builder Pro, il est **essentiel** de valider que tout fonctionne correctement sur le serveur de production.

## 🧪 Outil de Validation

### Script `server-validator.php`

Un script complet de validation serveur a été créé : `plugin/server-validator.php`

### 🚀 Déploiement du Validateur

1. **Le script est automatiquement déployé** avec le plugin
2. **Accessible via URL** : `https://votresite.com/wp-content/plugins/wp-pdf-builder-pro/server-validator.php`

### 📋 Tests Effectués

#### ✅ **Configuration Serveur**
- Version WordPress (5.0+ requis)
- Version PHP (8.0+ requis)
- Extensions PHP requises (mbstring, gd, xml, zip, curl)
- Constantes WordPress définies
- Mode DEBUG (désactivé en production)

#### ✅ **Plugin & Classes**
- Plugin activé dans WordPress
- Autoloader fonctionnel
- Classes principales chargées
- Interfaces implémentées correctement

#### ✅ **Base de Données**
- Tables du plugin créées
- Connexion DB opérationnelle
- Permissions d'accès

#### ✅ **Assets & Fichiers**
- Fichiers JavaScript compilés
- Fichiers CSS présents
- Permissions fichiers correctes
- Dossier cache accessible

#### ✅ **APIs & Fonctionnalités**
- Actions AJAX enregistrées
- API PreviewImageAPI accessible
- Génération PDF fonctionnelle
- Génération images de prévisualisation

#### ✅ **Intégration WooCommerce**
- WooCommerce détecté (si installé)
- Variables d'ordre accessibles
- Templates compatibles

#### ✅ **Performance**
- Temps de chargement acceptable
- Utilisation mémoire raisonnable

## 🎮 Utilisation

### Méthode 1: Interface Web
1. Accéder à : `https://votresite.com/wp-content/plugins/wp-pdf-builder-pro/server-validator.php`
2. Cliquer sur **"🚀 Lancer la Validation Complète"**
3. Attendre la fin des tests (~30 secondes)
4. Consulter le rapport détaillé

### Méthode 2: Ligne de Commande (SSH)
```bash
# Via WP-CLI
wp eval "require_once 'wp-content/plugins/wp-pdf-builder-pro/server-validator.php'; define('RUN_PDF_BUILDER_VALIDATION', true);"

# Ou directement via PHP
php wp-content/plugins/wp-pdf-builder-pro/server-validator.php
```

## 📊 Interprétation des Résultats

### ✅ **Score 90-100% : Production Ready**
- Plugin entièrement fonctionnel
- Toutes les fonctionnalités validées
- Prêt pour utilisation en production

### ⚠️ **Score 70-89% : Avertissements**
- Fonctionnalités de base OK
- Quelques optimisations recommandées
- Vérifier les avertissements

### ❌ **Score < 70% : Problèmes Critiques**
- Corrections requises avant production
- Vérifier les erreurs détaillées
- Contacter l'équipe technique

## 🔧 Résolution des Problèmes Courants

### ❌ "Plugin NON activé"
**Solution :** Activer le plugin dans WordPress Admin > Extensions

### ❌ "Classe X NON trouvée"
**Solution :** Vérifier les permissions fichiers (755 dossiers, 644 fichiers)

### ❌ "Extension PHP manquante"
**Solution :** Contacter l'hébergeur pour installer l'extension requise

### ❌ "Dossier cache NON accessible"
**Solution :**
```bash
chmod 755 wp-content/plugins/wp-pdf-builder-pro/cache/
chown www-data:www-data wp-content/plugins/wp-pdf-builder-pro/cache/
```

### ⚠️ "Mode DEBUG activé"
**Solution :** Dans `wp-config.php`, définir `define('WP_DEBUG', false);`

## 📈 Métriques de Performance

### Temps de Chargement
- **Excellent :** < 50ms
- **Bon :** 50-100ms
- **Acceptable :** 100-200ms
- **À optimiser :** > 200ms

### Utilisation Mémoire
- **Optimale :** < 2MB par chargement
- **Acceptable :** 2-5MB
- **À surveiller :** > 5MB

## 🔄 Validation Périodique

### Recommandations
- **Après déploiement :** Validation complète
- **Après mise à jour :** Validation ciblée
- **Mensuellement :** Vérification rapide
- **Après incident :** Validation complète

### Automatisation
```bash
# Script de monitoring (cron)
#!/bin/bash
curl -s "https://votresite.com/wp-content/plugins/wp-pdf-builder-pro/server-validator.php?run_validation=1" > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ Validation OK - $(date)" >> /var/log/pdf-builder-monitoring.log
else
    echo "❌ Validation FAILED - $(date)" >> /var/log/pdf-builder-monitoring.log
    # Envoyer alerte
fi
```

## 📞 Support

### En cas de problème
1. **Consulter le rapport détaillé** du validateur
2. **Vérifier les logs WordPress** : `wp-content/debug.log`
3. **Contacter l'équipe technique** avec le rapport complet
4. **Fournir les informations système** :
   - Version WordPress
   - Version PHP
   - Hébergeur utilisé
   - Erreurs spécifiques

---

**🎯 Objectif :** Zéro erreur en production
**📊 Score cible :** 100/100
**🔄 Fréquence :** Après chaque déploiement