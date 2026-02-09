# 🔐 Guide de Transition - Système Unifié de Nonces

## Version: 1.0.0
## Date: 6 février 2026

---

## 📋 Résumé

Ce guide explique comment utiliser le **nouveau système centralisé de gestion des nonces** en remplacement des anciennes méthodes dispersées.

### ✅ Points clés:
- ✅ **Une seule source de vérité**: `PDF_Builder_Nonce_Registry`
- ✅ **Validateur unifié**: `PDF_Builder_Nonce_Validator`
- ✅ **Résolution automatique des alias**
- ✅ **Logging pour audit de sécurité**
- ✅ **Support des anciennes actions avec migration progressive**

---

## 🚀 Utilisation

### Backend (PHP) - Créer un nonce

#### ❌ ANCIEN (à éviter)
```php
$nonce = wp_create_nonce('pdf_builder_ajax');
$nonce = wp_create_nonce('pdf_builder_settings');
$nonce = wp_create_nonce('pdf_builder_order_actions');
```

#### ✅ NOUVEAU (recommandé)
```php
// Mode simple
$nonce = \wp_create_nonce(
    PDF_Builder_Nonce_Registry::resolve_action('pdf_builder_ajax')
);

// Ou via helper direct
$nonce = \wp_create_nonce('pdf_builder_ajax'); // Autorisé (action reconnue)
```

### Backend - Vérifier un nonce

#### ❌ ANCIEN  
```php
if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
    wp_send_json_error('Nonce invalide');
    return;
}
```

#### ✅ NOUVEAU - Simple verification
```php
if (!pdf_builder_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
    wp_send_json_error('Sécurité échouée', 403);
    return;
}
```

#### ✅ NOUVEAU - Avec JSON error automatique
```php
// Lance une erreur JSON automatique si nonce invalide
pdf_builder_verify_request_or_json_error($_POST['nonce'], 'pdf_builder_ajax');
// Le code continue seulement si valide
```

#### ✅ NOUVEAU - Avec permissions
```php
// Vérifie permissions + nonce ensemble
if (!pdf_builder_verify_request(
    $_POST['nonce'], 
    'pdf_builder_order_actions',
    'manage_orders'  // capacité personnalisée (optionnel)
)) {
    wp_send_json_error('Accès refusé', 403);
    return;
}
```

---

## 📚 Registre des Actions Disponibles

Voir `PDF_Builder_Nonce_Registry::get_all_actions()` pour la liste complète.

| Action Canonique | Description | Alias Supportés |
|------------------|-------------|-----------------|
| `pdf_builder_ajax` | Action AJAX générale | (aucun) |
| `pdf_builder_settings` | Paramètres du plugin | (aucun) |
| `pdf_builder_templates` | Gestion des templates | `pdf_builder_predefined_templates` |
| `pdf_builder_order_actions` | Actions WooCommerce | (aucun) |
| `pdf_builder_gdpr` | Données RGPD | (aucun) |
| `pdf_builder_notifications` | Notifications | (aucun) |
| `pdf_builder_onboarding` | Onboarding | (aucun) |
| `pdf_builder_canvas_settings` | Paramètres canvas | (aucun) |
| `pdf_builder_license` | Gestion licences | `pdf_builder_deactivate` |
| `pdf_builder_maintenance` | Maintenance | (aucun) |
| `pdf_builder_cron` | CRON tasks | `pdf_builder_cron_test` |

---

## 🔄 Résolution des Alias

Le système résout automatiquement les anciennes actions vers les nouvelles:

```php
// Tous ces appels font référence à la même action canonique
pdf_builder_verify_nonce($nonce, 'pdf_builder_templates');
pdf_builder_verify_nonce($nonce, 'pdf_builder_predefined_templates'); // Alias
// Les deux résolvent vers: 'pdf_builder_templates'
```

---

## 🎯 Patterns de Migration

### Pattern 1: Action AJAX simple

#### AVANT
```php
add_action('wp_ajax_my_action', function() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'pdf_builder_ajax')) {
        wp_send_json_error('Nonce invalid');
        return;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No permission');
        return;
    }
    
    // ... votre code
});
```

#### APRÈS
```php
add_action('wp_ajax_my_action', function() {
    // Une seule ligne!
    pdf_builder_verify_request_or_json_error($_POST['nonce'], 'pdf_builder_ajax');
    
    // ... votre code (pas besoin de vérifier les permissions, elles sont incluses)
});
```

### Pattern 2: Actions WooCommerce avec capacités personnalisées

#### AVANT
```php
if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
    wp_send_json_error('Invalid nonce');
}
if (!current_user_can('manage_orders')) {
    wp_send_json_error('No permission');
}
```

#### APRÈS
```php
pdf_builder_verify_request_or_json_error($_POST['nonce'], 'pdf_builder_order_actions');
// Les capacités sont automatiquement correctes
```

---

## 🔐 Logging pour Audit

Chaque vérification de nonce est loggée:

```
[PDF_BUILDER_NONCE] {"timestamp":"2026-02-06 14:30:00","event":"NONCE_VERIFIED","action":"pdf_builder_ajax","user_id":1,"data":{"result":1,"source":"post"}}
```

Voir `WP_DEBUG` dans `wp-config.php` pour activer les logs.

---

## 📝 Actions Personnalisées

Ajouter une action personnalisée:

```php
PDF_Builder_Nonce_Registry::register_custom_action('mon_action', [
    'description' => 'Ma nouvelle action',
    'ttl' => 43200,
    'capability' => 'manage_options',
    'aliases' => ['ancien_nom_de_action'], // Support des anciens noms
]);
```

---

## ⚠️ Notes Importantes

1. **Compatibilité**: Le système supporte les anciennes actions, mais encourage la migration
2. **Performance**: Pas de requête BDD, tout en mémoire
3. **Sécurité**: Les capacités par défaut sont appliquées automatiquement
4. **Débogage**: Activez `WP_DEBUG` pour voir les logs de nonce

---

## 🐛 Dépannage

### Mon nonce ne fonctionne pas

1. Vérifiez l'action: `PDF_Builder_Nonce_Registry::is_registered('mon_action')`
2. Vérifiez que le nonce est bien envoyé depuis le frontend
3. Consultez les logs avec `WP_DEBUG = true`

### Les alias ne fonctionnent pas

Les alias doivent être enregistrés dans le registre. Contactez les admins si l'alias manque.

---

## 📖 Référence API Complète

### PDF_Builder_Nonce_Registry

```php
// Actions
PDF_Builder_Nonce_Registry::get_all_actions()      // Liste toutes actions
PDF_Builder_Nonce_Registry::is_registered($action) // Vérifie si exist
PDF_Builder_Nonce_Registry::resolve_action($alias) // Résout alias

// Config
PDF_Builder_Nonce_Registry::get_action_config($action)   // Config d'action
PDF_Builder_Nonce_Registry::get_capability($action)      // Capacité requise

// Custom
PDF_Builder_Nonce_Registry::register_custom_action(...) // Ajouter action
PDF_Builder_Nonce_Registry::log_nonce_event(...)        // Logger événement
```

### PDF_Builder_Nonce_Validator

```php
// Vérifications
PDF_Builder_Nonce_Validator::verify($nonce, $action, $source) // Bool
PDF_Builder_Nonce_Validator::verify_or_die(...)              // Die ou continue
PDF_Builder_Nonce_Validator::verify_or_json_error(...)       // JSON error ou continue
PDF_Builder_Nonce_Validator::verify_request(...)             // Nonce + perms
PDF_Builder_Nonce_Validator::verify_request_or_json_error(...) // Complet + die

// Helpers
PDF_Builder_Nonce_Validator::get_all_nonce_values()  // Récupère tous nonces de la requête
```

### Fonctions Raccourcis

```php
pdf_builder_verify_nonce($nonce, $action, $source)
pdf_builder_verify_request($nonce, $action, $capability)
pdf_builder_verify_request_or_json_error($nonce, $action, $capability)
```

---

**Questions?** Consultez le code source ou demandez aux développeurs.
