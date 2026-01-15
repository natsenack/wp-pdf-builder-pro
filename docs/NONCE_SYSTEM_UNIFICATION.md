# Unification du système de nonce - PDF Builder Pro V2

## Vue d'ensemble

Ce document décrit l'unification du système de gestion des nonces (jetons de sécurité CSRF) dans PDF Builder Pro V2. Le système assure la cohérence entre le backend PHP et le frontend React/TypeScript.

## Architecture

### Backend (PHP)

#### `NonceManager` (`plugin/src/Admin/Handlers/NonceManager.php`)

Classe centralisée pour la gestion des nonces côté serveur :

```php
class NonceManager {
  const NONCE_ACTION = 'pdf_builder_ajax';
  const NONCE_KEY = 'nonce';
  const MIN_CAPABILITY = 'edit_posts';
  const ADMIN_CAPABILITY = 'manage_options';
  const NONCE_TTL = 43200; // 12 heures
}
```

**Méthodes principales :**
- `createNonce(): string` - Génère un nonce valide
- `verifyNonce(?string $nonce)` - Vérifie un nonce (retourne 1, 2 ou false)
- `getNonceFromRequest(): ?string` - Récupère le nonce de la requête (GET ou POST)
- `checkPermissions(string $capability): bool` - Vérifie les permissions utilisateur
- `validateRequest(string $capability)` - Validation unifiée (permissions + nonce)
- `sendNonceErrorResponse()` - Envoie une réponse d'erreur nonce avec nonce frais
- `sendPermissionErrorResponse()` - Envoie une réponse d'erreur permissions
- `logInfo(string $message)` - Log formaté
- `getLocalizedData(): array` - Données pour l'inlining au frontend

#### `AjaxHandler` - Mises à jour

Tous les endpoints AJAX utilisant les nonces ont été modernisés pour utiliser `NonceManager` :

**Avant :**
```php
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_send_json_error('Permissions insuffisantes');
    return;
}
if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
    wp_send_json_error('Nonce invalide');
    return;
}
```

**Après :**
```php
$validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
if (!$validation['success']) {
    if ($validation['code'] === 'nonce_invalid') {
        NonceManager::sendNonceErrorResponse();
    } else {
        NonceManager::sendPermissionErrorResponse();
    }
    return;
}
```

**Endpoints mise à jour :**
- `ajaxGeneratePdfFromCanvas()`
- `ajaxDownloadPdf()`
- `ajaxSaveTemplateV3()`
- `ajaxLoadTemplate()`
- `ajaxGetTemplate()`
- `ajaxGenerateOrderPdf()`
- `ajaxGetFreshNonce()`
- `ajaxCheckDatabase()`
- `ajaxRepairDatabase()`
- `ajaxExecuteSqlRepair()`
- `ajaxSaveSettings()`
- `ajaxUnifiedHandler()`

### Frontend (TypeScript/React)

#### `ClientNonceManager` (`src/js/react/utils/ClientNonceManager.ts`)

Classe centralisée pour la gestion des nonces côté client :

```typescript
class ClientNonceManager {
  static readonly NONCE_ACTION = 'pdf_builder_ajax';
  static readonly STORAGE_KEY = 'pdfBuilderNonce';
  static readonly NONCE_TTL = 43200; // 12 heures
}
```

**Méthodes principales :**
- `getCurrentNonce(): string | null` - Obtient le nonce actuel
- `getAjaxUrl(): string` - Obtient l'URL AJAX
- `setNonce(nonce: string): void` - Met à jour le nonce globalement
- `refreshNonce(currentNonce?: string): Promise<string | null>` - Rafraîchit le nonce du serveur
- `addToFormData(formData: FormData, nonce?: string): FormData` - Ajoute le nonce à FormData (POST)
- `addToUrl(url: string, nonce?: string): string` - Ajoute le nonce à une URL (GET)
- `getConfig(): NonceConfig | null` - Obtient la configuration du nonce
- `isValid(): boolean` - Vérifie si le nonce est valide
- `log(message: string)` - Log formaté
- `logError(message: string)` - Error log formaté

#### `useTemplate.ts` - Mises à jour

Le hook a été modernisé pour utiliser `ClientNonceManager` :

**Avant :**
```typescript
const currentNonce = window.pdfBuilderData?.nonce || '';
formData.append('nonce', currentNonce);
const response = await fetch(window.pdfBuilderData?.ajaxUrl || '', {
  method: 'POST',
  body: formData
});
```

**Après :**
```typescript
import { ClientNonceManager } from '../utils/ClientNonceManager';

ClientNonceManager.addToFormData(formData);
const response = await fetch(ClientNonceManager.getAjaxUrl(), {
  method: 'POST',
  body: formData
});
```

**Gestion des erreurs nonce améliorée :**
```typescript
if (result.data && (result.data.includes('Nonce invalide') || result.data.code === 'nonce_invalid')) {
  const freshNonce = await ClientNonceManager.refreshNonce(ClientNonceManager.getCurrentNonce() || undefined);
  if (freshNonce) {
    return await saveTemplate(); // Retry
  }
}
```

## Flux de sécurité unifié

### 1. Initialisation
- **Backend :** `NonceManager::createNonce()` génère un nonce valide
- **Localization :** Le nonce est transmis au frontend via `wp_localize_script()`
- **Frontend :** `ClientNonceManager::getCurrentNonce()` récupère le nonce

### 2. Requête AJAX
- **Client :** `ClientNonceManager::addToFormData(formData)` ajoute le nonce
- **Serveur :** `NonceManager::validateRequest()` valide le nonce et les permissions
- **Réponse :** Succès ou erreur avec code d'erreur spécifique

### 3. Rafraîchissement (si expiration)
- **Client :** Détecte `'Nonce invalide'` dans la réponse
- **Client :** Appelle `ClientNonceManager::refreshNonce()`
- **Serveur :** Endpoint `pdf_builder_get_fresh_nonce` génère un nouveau nonce
- **Client :** Metà à jour `window.pdfBuilderData.nonce` avec le nouveau
- **Client :** Réessaie la requête originale automatiquement

## Avantages de l'unification

### Sécurité
✅ Action nonce cohérente (`pdf_builder_ajax`) partout  
✅ Permissions standardisées (pas de mix `manage_options`/`edit_posts`)  
✅ Logging unifié et traçable  
✅ Gestion d'erreur nonce centralisée  

### Maintenabilité
✅ Logique de nonce centralisée (un seul point de modification)  
✅ Code plus lisible avec `NonceManager::validateRequest()`  
✅ Moins de code dupliqué (permissions + nonce)  
✅ Facilite l'audit de sécurité  

### Expérience utilisateur
✅ Rafraîchissement automatique de nonce  
✅ Pas d'interruption lors de l'expiration du nonce  
✅ Gestion d'erreur cohérente côté client  
✅ Feedback utilisateur amélioré  

## Configuration

### Constantes modifiables

Editer `NonceManager.php` ou `ClientNonceManager.ts` pour changer :

```php
// PHP - Action du nonce
const NONCE_ACTION = 'pdf_builder_ajax';

// PHP - Permissions minimales
const MIN_CAPABILITY = 'edit_posts';
```

```typescript
// TypeScript - Action du nonce
static readonly NONCE_ACTION = 'pdf_builder_ajax';

// TypeScript - TTL du nonce
static readonly NONCE_TTL = 43200;
```

## Logging et débogage

### Logs PHP
```php
NonceManager::logInfo('Demande de génération de nonce frais');
// Sortie : [PDF Builder] [NonceManager] [INFO] Demande de génération de nonce frais
```

### Logs TypeScript
```typescript
ClientNonceManager.log('Nonce rafraîchi avec succès');
ClientNonceManager.logError('Erreur lors du rafraîchissement');
```

## Migration des anciens codes

Si vous avez du code utilisant l'ancien système, migrez comme suit :

### PHP
```php
// ❌ Ancien
if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_ajax')) {
    wp_send_json_error('Nonce invalide');
}

// ✅ Nouveau
$validation = NonceManager::validateRequest();
if (!$validation['success']) {
    NonceManager::sendNonceErrorResponse();
    return;
}
```

### TypeScript
```typescript
// ❌ Ancien
const nonce = window.pdfBuilderData?.nonce;
formData.append('nonce', nonce);

// ✅ Nouveau
import { ClientNonceManager } from '../utils/ClientNonceManager';
ClientNonceManager.addToFormData(formData);
```

## Fichiers modifiés

### Backend
- `plugin/src/Admin/Handlers/NonceManager.php` (nouveau)
- `plugin/src/Admin/Handlers/AjaxHandler.php` (mise à jour)

### Frontend
- `src/js/react/utils/ClientNonceManager.ts` (nouveau)
- `src/js/react/hooks/useTemplate.ts` (mise à jour)

## Tests recommandés

- [ ] Sauvegarder un template en administrateur
- [ ] Sauvegarder un template en utilisateur avec `edit_posts`
- [ ] Tenter l'accès en utilisateur sans permission
- [ ] Attendre plus de 12 heures (TTL) et refaire une action
- [ ] Vérifier les logs pour les messages de nonce
- [ ] Tester le rafraîchissement automatique du nonce

## Performance

- **Build size :** +2.5 KiB (ClientNonceManager + imports)
- **Runtime overhead :** Négligeable (opérations basiques)
- **Backend overhead :** Réduit (centralisation logique)

## Historique

- **v2.0.0** - Unification du système de nonce
  - Création de `NonceManager` (backend)
  - Création de `ClientNonceManager` (frontend)
  - Migration de tous les endpoints AJAX
  - Amélioration de la gestion d'erreur
