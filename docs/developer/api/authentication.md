# 🔐 API REST - Authentification

L'API REST de PDF Builder Pro utilise le système d'authentification standard de WordPress avec des extensions pour la sécurité renforcée.

## 🎯 Méthodes d'Authentification

### 1. Cookies WordPress (Recommandé)
Utilise les cookies de session WordPress pour l'authentification automatique.

**Avantages :**
- ✅ Authentification transparente
- ✅ Gestion automatique des sessions
- ✅ Compatible avec tous les plugins WordPress

**Utilisation :**
```javascript
// JavaScript côté client
fetch('/wp-json/pdf-builder/v1/templates', {
    method: 'GET',
    credentials: 'same-origin' // Important !
})
.then(response => response.json())
.then(data => console.log(data));
```

### 2. Application Passwords (WordPress 5.6+)
Utilise des mots de passe d'application pour l'accès API.

**Configuration :**
1. Allez dans **Users → Profile**
2. Dans la section "Application Passwords", créez un nouveau mot de passe
3. Utilisez le nom d'utilisateur + mot de passe généré

**Exemple :**
```bash
curl -X GET "https://example.com/wp-json/pdf-builder/v1/templates" \
  -u "username:application_password" \
  -H "Content-Type: application/json"
```

### 3. JWT Tokens (Extension)
Pour les intégrations avancées, utilisez des tokens JWT.

```php
// Générer un token JWT
$token = PDF_Builder_API::generate_jwt_token([
    'user_id' => get_current_user_id(),
    'permissions' => ['read', 'write'],
    'expires' => time() + 3600 // 1 heure
]);
```

## 🛡️ Autorisation & Permissions

### Rôles WordPress
- **Administrator** : Accès complet
- **Editor** : Gestion templates + génération
- **Author** : Lecture + génération
- **Subscriber** : Génération uniquement

### Capabilities Personnalisées
```php
// Capabilities disponibles
'pdf_builder_manage_templates'    // CRUD templates
'pdf_builder_generate_pdf'        // Génération PDF
'pdf_builder_manage_settings'     // Paramètres plugin
'pdf_builder_view_analytics'      // Métriques/analytics
```

### Vérification des Permissions
```php
// Dans un endpoint personnalisé
function check_pdf_permissions($request) {
    $user = wp_get_current_user();

    // Vérifier le rôle
    if (!in_array('administrator', $user->roles)) {
        return new WP_Error('forbidden', 'Permissions insuffisantes', ['status' => 403]);
    }

    // Vérifier les capabilities
    if (!current_user_can('pdf_builder_generate_pdf')) {
        return new WP_Error('forbidden', 'Capability manquante', ['status' => 403]);
    }

    return true;
}
```

## 🔒 Sécurité Renforcée

### Nonces WordPress
Utilisez toujours des nonces pour les requêtes sensibles.

```php
// Générer un nonce côté serveur
$nonce = wp_create_nonce('pdf_builder_action');

// Vérifier côté serveur
if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_action')) {
    wp_die('Sécurité : Nonce invalide');
}
```

### Validation des Entrées
Toutes les entrées utilisateur sont automatiquement validées :

```php
// Validation automatique
$validated_data = PDF_Builder_API::validate_input($input_data, [
    'name' => 'string|required|max:255',
    'email' => 'email|required',
    'template_id' => 'integer|exists:templates,id'
]);
```

### Rate Limiting
Protection contre les abus :

```php
// Configuration du rate limiting
add_filter('pdf_builder_rate_limits', function($limits) {
    return [
        'generate_pdf' => [
            'limit' => 10,      // 10 PDFs par
            'period' => 60      // 60 secondes
        ],
        'api_requests' => [
            'limit' => 100,     // 100 requêtes par
            'period' => 60      // 60 secondes
        ]
    ];
});
```

## 🔑 Clés API (Intégrations Externes)

### Génération de Clés API
```php
// Générer une clé API
$api_key = PDF_Builder_API::generate_api_key([
    'name' => 'Mon Application Externe',
    'permissions' => ['read', 'write'],
    'ip_whitelist' => ['192.168.1.1', '10.0.0.1'],
    'expires_at' => '2026-10-20 00:00:00'
]);

echo $api_key; // "pbk_abc123def456..."
```

### Utilisation des Clés API
```bash
curl -X GET "https://example.com/wp-json/pdf-builder/v1/templates" \
  -H "Authorization: Bearer pbk_abc123def456..." \
  -H "Content-Type: application/json"
```

### Gestion des Clés API
```php
// Lister les clés actives
$api_keys = PDF_Builder_API::get_api_keys();

// Révoquer une clé
PDF_Builder_API::revoke_api_key('pbk_abc123def456...');

// Régénérer une clé
$new_key = PDF_Builder_API::regenerate_api_key('pbk_abc123def456...');
```

## 🌐 CORS & Cross-Origin

### Configuration CORS
```php
// Autoriser des origines spécifiques
add_filter('pdf_builder_cors_origins', function($origins) {
    return array_merge($origins, [
        'https://monapp.com',
        'https://staging.monapp.com'
    ]);
});

// Headers CORS personnalisés
add_filter('pdf_builder_cors_headers', function($headers) {
    return array_merge($headers, [
        'X-Custom-Header' => 'valeur'
    ]);
});
```

## 📊 Audit & Logging

### Logs d'Authentification
Toutes les tentatives d'authentification sont tracées :

```php
// Consulter les logs
$auth_logs = PDF_Builder_Audit::get_logs([
    'type' => 'authentication',
    'user_id' => 123,
    'date_from' => '2025-10-01',
    'date_to' => '2025-10-20'
]);
```

### Alertes Sécurité
Configuration d'alertes automatiques :

```php
// Alertes sur échecs d'authentification
add_action('pdf_builder_auth_failed', function($data) {
    // Envoyer email d'alerte
    wp_mail(
        'admin@site.com',
        'Tentative d\'authentification suspecte',
        "IP: {$data['ip']}, User: {$data['username']}"
    );
});
```

## 🧪 Tests d'Authentification

### Tests Unitaires
```php
// Tester l'authentification
class AuthenticationTest extends WP_UnitTestCase {
    public function test_api_key_authentication() {
        $api_key = PDF_Builder_API::generate_api_key(['name' => 'Test']);

        $request = new WP_REST_Request('GET', '/pdf-builder/v1/templates');
        $request->set_header('Authorization', "Bearer {$api_key}");

        $response = rest_do_request($request);
        $this->assertEquals(200, $response->get_status());
    }
}
```

### Tests d'Intégration
```bash
# Tester avec curl
curl -X POST "https://example.com/wp-json/pdf-builder/v1/auth/test" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{"test": "data"}'
```

## 🚨 Dépannage

### Erreurs Courantes

**401 Unauthorized**
```json
{
  "code": "pdf_builder_auth_required",
  "message": "Authentification requise",
  "data": {"status": 401}
}
```
**Solution :** Vérifiez vos credentials ou cookies

**403 Forbidden**
```json
{
  "code": "pdf_builder_insufficient_permissions",
  "message": "Permissions insuffisantes",
  "data": {"status": 403}
}
```
**Solution :** Vérifiez les rôles/capabilities utilisateur

**429 Too Many Requests**
```json
{
  "code": "pdf_builder_rate_limited",
  "message": "Trop de requêtes",
  "data": {
    "status": 429,
    "retry_after": 60
  }
}
```
**Solution :** Attendez le délai indiqué

---

**📖 Voir aussi :**
- [Endpoints API](./endpoints.md)
- [Exemples d'usage](./examples.md)
- [Documentation sécurité](../technical/security.md)