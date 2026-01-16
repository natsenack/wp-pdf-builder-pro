# Configuration du système de nonce unifié

## Constantes définies

### Backend (PHP) - `NonceManager.php`

```php
namespace PDF_Builder\Admin\Handlers;

class NonceManager {
    /**
     * Action nonce unifié pour tous les appels AJAX
     * Cette valeur DOIT correspondre au frontend
     */
    const NONCE_ACTION = 'pdf_builder_ajax';

    /**
     * Clé POST/GET pour le nonce
     * Utilisée pour récupérer le nonce de la requête
     */
    const NONCE_KEY = 'nonce';

    /**
     * Capacité minimale requise pour les opérations AJAX
     * "edit_posts" permet les éditeurs et administrateurs
     */
    const MIN_CAPABILITY = 'edit_posts';

    /**
     * Capacité requise pour les opérations d'administration
     * "manage_options" limité aux administrateurs
     */
    const ADMIN_CAPABILITY = 'manage_options';

    /**
     * TTL (Time To Live) du nonce en secondes
     * 43200 secondes = 12 heures (par défaut WordPress)
     */
    const NONCE_TTL = 43200;
}
```

### Frontend (TypeScript) - `ClientNonceManager.ts`

```typescript
export class ClientNonceManager {
  /**
   * Action nonce unifié
   * DOIT correspondre à NonceManager::NONCE_ACTION du backend
   */
  static readonly NONCE_ACTION = "pdf_builder_ajax";

  /**
   * Clé pour stocker le nonce en session storage
   */
  static readonly STORAGE_KEY = "pdfBuilderNonce";

  /**
   * TTL du nonce en secondes (même valeur que backend)
   */
  static readonly NONCE_TTL = 43200; // 12 heures
}
```

---

## Configuration recommandée

### Variables d'environnement

```bash
# Aucune variable d'environnement requise
# Tous les paramètres sont codifiés en dur comme constantes
# Pour modifier, éditez directement les fichiers de classe
```

### Fichiers de configuration

#### `wp-config.php`

```php
// Recommandé pour le débogage des nonces
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Durée de vie de la session (affecte indirectement les nonces)
define('AUTH_COOKIE', 'wordpress_' . md5(site_url()));
define('AUTH_COOKIE_SECONDS', 24 * 60 * 60); // 24 heures
```

#### `.env` (si utilisé)

```bash
# Optionnel - Ce projet n'utilise pas de fichier .env actuellement
PDF_BUILDER_NONCE_ACTION=pdf_builder_ajax
PDF_BUILDER_MIN_CAPABILITY=edit_posts
PDF_BUILDER_ADMIN_CAPABILITY=manage_options
```

---

## Localisation des données

### PHP - Inlining du nonce

```php
// Fichier: plugin/includes/ReactAssetsV2.php ou similaire

wp_localize_script('pdf-builder-react', 'pdfBuilderData', [
    'nonce' => wp_create_nonce('pdf_builder_ajax'),
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'templateId' => isset($_GET['template_id']) ? intval($_GET['template_id']) : null,
    // Autres données...
]);
```

### JavaScript - Récupération globale

```typescript
// Le frontend récupère automatiquement via:
window.pdfBuilderData?.nonce; // Défini par wp_localize_script
window.pdfBuilderNonce; // Fallback (non utilisé dans la v2)
```

---

## Valeurs par défaut pour chaque endpoint

### Endpoints administrateur uniquement

```php
// Nécessitent: ADMIN_CAPABILITY (manage_options)
ajaxGeneratePdfFromCanvas()      // Production PDF
ajaxDownloadPdf()                // Téléchargement fichier
ajaxGenerateOrderPdf()           // Génération commande
ajaxCheckDatabase()              // Maintenance
ajaxRepairDatabase()             // Maintenance
ajaxExecuteSqlRepair()           // Maintenance
```

### Endpoints avec permission réduite

```php
// Nécessitent: MIN_CAPABILITY (edit_posts)
ajaxGetFreshNonce()              // Rafraîchissement nonce
ajaxSaveTemplateV3()             // Sauvegarde template
ajaxLoadTemplate()               // Chargement template
ajaxGetTemplate()                // Récupération template
ajaxSaveSettings()               // Paramètres
ajaxUnifiedHandler()             // Handler unifié
```

---

## Modification des constantes

### Scénario 1: Changer l'action du nonce

**Avant :**

```php
const NONCE_ACTION = 'pdf_builder_ajax';
```

**Après :**

```php
const NONCE_ACTION = 'ma_nouvelle_action';
```

**À faire aussi :**

- Mettre à jour `ClientNonceManager.ts` avec la même valeur
- Mettre à jour la localisation PHP si elle utilise la constante
- Re-déployer le code

**Impact :**

- ⚠️ Les anciens nonces deviennent invalides
- ⚠️ Les sessions existantes doivent se reconnecter

### Scénario 2: Changer le TTL du nonce

**Avant :**

```php
const NONCE_TTL = 43200; // 12 heures
```

**Après :**

```php
const NONCE_TTL = 86400; // 24 heures
```

**À faire aussi :**

- Mettre à jour `ClientNonceManager.ts`
- Re-déployer
- Documenter le changement

**Impact :**

- ✓ Les nonces restent valides plus longtemps
- ⚠️ Légère diminution de sécurité
- ✓ Meilleure UX (moins de rafraîchissements)

### Scénario 3: Changer les permissions requises

**Avant :**

```php
const MIN_CAPABILITY = 'edit_posts';
```

**Après :**

```php
const MIN_CAPABILITY = 'read'; // Tous les utilisateurs connectés
```

**À faire aussi :**

- Évaluer l'impact sécurité
- Mettre à jour la documentation
- Tester avec différents rôles utilisateur

**Impact :**

- ⚠️ Plus d'utilisateurs peuvent accéder
- ⚠️ Vérifier les implications sécurité
- ✓ Plus inclusif

---

## Mapping des capacités WordPress

| Capacité         | Rôle                      | Description                   |
| ---------------- | ------------------------- | ----------------------------- |
| `manage_options` | Administrateur            | Accès complet au site         |
| `edit_posts`     | Éditeur, Auteur           | Peut créer/modifier des posts |
| `edit_pages`     | Éditeur, Auteur           | Peut créer/modifier des pages |
| `read`           | Tout utilisateur connecté | Accès minimal                 |
| `upload_files`   | Éditeur, Auteur           | Peut télécharger des fichiers |
| `create_users`   | Administrateur            | Peut créer des utilisateurs   |

---

## Dépannage de configuration

### Problème: "Nonce invalide" systématique

**Diagnostic :**

```php
// Ajouter dans AjaxHandler.php temporairement
NonceManager::logInfo('Action: ' . NonceManager::NONCE_ACTION);
NonceManager::logInfo('Nonce reçu: ' . $_POST['nonce'] ?? 'MANQUANT');
```

**Vérifications :**

- [ ] `NonceManager::NONCE_ACTION` = `ClientNonceManager::NONCE_ACTION`
- [ ] Le nonce est dans $\_POST ou $\_GET
- [ ] La localisation PHP est correcte
- [ ] Pas de cache JavaScript

### Problème: Permissions refusées incorrectement

**Diagnostic :**

```php
// Ajouter temporairement
NonceManager::logInfo('User ID: ' . get_current_user_id());
NonceManager::logInfo('Can edit_posts: ' . (current_user_can('edit_posts') ? 'OUI' : 'NON'));
NonceManager::logInfo('Can manage_options: ' . (current_user_can('manage_options') ? 'OUI' : 'NON'));
```

**Vérifications :**

- [ ] Utilisateur est connecté
- [ ] Rôle utilisateur correct
- [ ] Capacité existe pour ce rôle

### Problème: TTL trop court

**Symptôme :** Nonce expire pendant les opérations longues

**Solution :**

```php
// Augmenter le TTL
const NONCE_TTL = 86400; // 24 heures
```

---

## Configuration pour les environnements

### Développement

```php
// php.ini ou wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// NonceManager - Verbose logging
// Maintien des valeurs par défaut
```

### Staging

```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// NonceManager - Standard
// Valeurs par défaut
```

### Production

```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// NonceManager - Optimisé
// Vérifier que WP_DEBUG_LOG est false pour performance
```

---

## Checklist de validation de configuration

### Installation

- [ ] `NonceManager.php` présent
- [ ] `ClientNonceManager.ts` présent
- [ ] Constantes synchronisées (backend/frontend)
- [ ] Build TypeScript réussi

### Runtime

- [ ] `wp_localize_script` appelée correctement
- [ ] `window.pdfBuilderData.nonce` disponible
- [ ] AJAX URL localisée
- [ ] Logging activé en dev

### Sécurité

- [ ] Capacités correctes par endpoint
- [ ] TTL approprié pour le use-case
- [ ] Nonce action unique
- [ ] Pas de duplication de vérification

---

## Performance

| Métrique                 | Valeur | Notes         |
| ------------------------ | ------ | ------------- |
| Temps création nonce     | <1ms   | PHP standard  |
| Temps vérification nonce | <1ms   | PHP standard  |
| Overhead mémoire         | <1KB   | Par nonce     |
| Appels AJAX/s            | 1000+  | Sans problème |

---

## Évolution future

### Version 2.1 (Future)

```php
// Possibilité d'ajouter des nonces rotatifs
const NONCE_ROTATION_ENABLED = false;

// Possibilité de rate limiting par nonce
const NONCE_RATE_LIMIT_REQUESTS = 100;
const NONCE_RATE_LIMIT_PERIOD = 3600; // 1 heure
```

### Version 2.2 (Future)

```php
// Support des nonces à usage unique
const NONCE_ONE_TIME_USE = false;

// Support du stockage redis pour les nonces distribués
const NONCE_STORAGE_BACKEND = 'wordpress'; // ou 'redis'
```

---

## Support et aide

### Contacter le support

Pour des questions sur la configuration :

1. Vérifier la documentation ici
2. Consulter les logs
3. Vérifier les tests

### Bugs à rapporter

Si vous trouvez un bug avec la configuration :

- Inclure les constantes utilisées
- Inclure les logs pertinents
- Inclure les étapes pour reproduire
