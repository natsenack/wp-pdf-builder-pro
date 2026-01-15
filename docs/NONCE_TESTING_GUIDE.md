# Guide de test - Système de nonce unifié

## Vue d'ensemble

Ce guide vous aide à tester et valider le nouveau système de nonce unifié dans PDF Builder Pro V2.

## Prérequis

- Accès administrateur à WordPress
- Utilisateur avec capacité `edit_posts`
- Console de navigateur (DevTools)
- Accès au fichier debug.log de WordPress

## Tests manuels

### Test 1 : Vérification du nonce initial

**Objectif :** Confirmer que le nonce est chargé correctement au démarrage

**Étapes :**
1. Allez à la page d'édition d'un template
2. Ouvrez la console navigateur (F12)
3. Exécutez : `console.log(window.pdfBuilderData?.nonce)`
4. Vérifiez que vous voyez une chaîne de caractères (ex: `abcd1234efgh5678`)

**Résultat attendu :**
```javascript
// Output: "a1b2c3d4e5f6g7h8..."
```

---

### Test 2 : Sauvegarde de template (administrateur)

**Objectif :** Vérifier que la sauvegarde fonctionne avec les permissions d'admin

**Étapes :**
1. Connecté comme administrateur
2. Ouvrez ou créez un template
3. Modifiez quelque chose (ex: ajoutez un élément)
4. Cliquez sur "Sauvegarder"
5. Vérifiez que la sauvegarde réussit

**Résultat attendu :**
```
✅ Template sauvegardé avec succès
```

**Logs à vérifier :**
```bash
# Dans debug.log
[PDF Builder] [NonceManager] [INFO] Demande de génération de nonce frais
[PDF Builder] [NonceManager] [INFO] Génération d'un nonce frais
```

---

### Test 3 : Sauvegarde de template (utilisateur non-admin)

**Objectif :** Vérifier que les utilisateurs avec `edit_posts` peuvent sauvegarder

**Étapes :**
1. Créez un utilisateur avec rôle "Contributeur" (a `edit_posts`)
2. Connectez-vous avec cet utilisateur
3. Ouvrez un template existant
4. Modifiez un élément
5. Sauvegardez

**Résultat attendu :**
```
✅ Template sauvegardé avec succès
```

---

### Test 4 : Accès sans permission

**Objectif :** Vérifier que l'accès est refusé sans `edit_posts`

**Étapes :**
1. Créez un utilisateur avec rôle "Abonné" (SANS `edit_posts`)
2. Connectez-vous avec cet utilisateur
3. Essayez d'accéder à `/wp-admin/?page=pdf-builder-pro`
4. Essayez de faire une action AJAX

**Résultat attendu :**
```
❌ Permissions insuffisantes
```

**Logs :**
```bash
[PDF Builder] [NonceManager] [INFO] Permissions insuffisantes pour générer un nonce
```

---

### Test 5 : Rafraîchissement automatique du nonce (simulation)

**Objectif :** Vérifier que le nonce expiré est rafraîchi automatiquement

**Étapes :**
1. Dans la console du navigateur, exécutez :
```javascript
// Simuler un nonce expiré
window.pdfBuilderData.nonce = 'nonce_invalide_delibere';
```

2. Tentez de sauvegarder un template
3. Vérifiez la console pour le message de rafraîchissement
4. Le template devrait être sauvegardé (avec nouveau nonce)

**Résultat attendu :**
```
🔄 [useTemplate] Nonce invalide détecté, récupération automatique...
✅ [useTemplate] Nouveau nonce récupéré, nouvelle tentative...
✅ Template sauvegardé avec succès
```

---

### Test 6 : Vérification du logging

**Objectif :** Confirmer que le logging est unifié et traçable

**Étapes :**
1. Effectuez 3-4 opérations AJAX (sauvegarde, chargement, etc.)
2. Vérifiez le fichier `wp-content/debug.log`
3. Recherchez les entrées `[PDF Builder] [NonceManager]`

**Résultat attendu :**
```bash
# Logs trouvés :
[PDF Builder] [NonceManager] [INFO] Génération d'un nonce frais
[PDF Builder] [NonceManager] [INFO] Nonce frais généré avec succès
```

---

### Test 7 : Test sous charge (multiple AJAX simultanée)

**Objectif :** Vérifier que le système gère les requêtes simultanées

**Étapes :**
1. Dans la console du navigateur, exécutez :
```javascript
// Faire 5 requêtes simultanées
for (let i = 0; i < 5; i++) {
    fetch(window.pdfBuilderData?.ajaxUrl, {
        method: 'POST',
        body: new FormData()
            .append('action', 'pdf_builder_check_database')
            .append('nonce', window.pdfBuilderData?.nonce)
    }).then(r => r.json()).then(d => console.log(d));
}
```

2. Vérifiez que toutes les requêtes réussissent

**Résultat attendu :**
```
✅ 5 réponses réussies
Pas d'erreur de nonce
```

---

### Test 8 : Vérification GET vs POST

**Objectif :** Confirmer que le nonce fonctionne en GET et POST

**Étapes (GET) :**
1. Exécutez dans la console :
```javascript
const url = window.pdfBuilderData?.ajaxUrl + 
    '?action=pdf_builder_get_template' +
    '&template_id=1' +
    '&nonce=' + window.pdfBuilderData?.nonce;
fetch(url).then(r => r.json()).then(console.log);
```

2. Vérifiez que vous récupérez les données

**Étapes (POST) :**
1. Effectuez une sauvegarde normale
2. Vérifiez que le nonce est dans le FormData

**Résultat attendu :**
```
✅ GET : Nonce valide
✅ POST : Nonce valide
```

---

## Tests automatisés

### Test de validation PHP

```php
<?php
// Fichier test: tests/test-nonce-manager.php

namespace PDF_Builder\Tests;

use PDF_Builder\Admin\Handlers\NonceManager;

class TestNonceManager extends \WP_UnitTestCase {
    
    public function test_create_nonce() {
        $nonce = NonceManager::createNonce();
        $this->assertIsString($nonce);
        $this->assertGreaterThan(0, strlen($nonce));
    }
    
    public function test_verify_nonce() {
        $nonce = NonceManager::createNonce();
        $result = NonceManager::verifyNonce($nonce);
        $this->assertNotFalse($result);
    }
    
    public function test_validate_request_permissions() {
        // Sans permission
        if (!current_user_can('edit_posts')) {
            $result = NonceManager::validateRequest();
            $this->assertFalse($result['success']);
            $this->assertEquals('permission_denied', $result['code']);
        }
    }
    
    public function test_get_nonce_from_request() {
        $_POST['nonce'] = 'test_nonce_value';
        $nonce = NonceManager::getNonceFromRequest();
        $this->assertEquals('test_nonce_value', $nonce);
    }
}
```

### Test TypeScript

```typescript
// Fichier test: src/js/react/utils/__tests__/ClientNonceManager.test.ts

import { ClientNonceManager } from '../ClientNonceManager';

describe('ClientNonceManager', () => {
    
    beforeEach(() => {
        window.pdfBuilderData = {
            nonce: 'test_nonce_123',
            ajaxUrl: 'http://example.com/admin-ajax.php'
        };
    });
    
    test('getCurrentNonce should return nonce', () => {
        const nonce = ClientNonceManager.getCurrentNonce();
        expect(nonce).toBe('test_nonce_123');
    });
    
    test('getAjaxUrl should return ajax URL', () => {
        const url = ClientNonceManager.getAjaxUrl();
        expect(url).toBe('http://example.com/admin-ajax.php');
    });
    
    test('isValid should check nonce validity', () => {
        expect(ClientNonceManager.isValid()).toBe(true);
        
        window.pdfBuilderData!.nonce = '';
        expect(ClientNonceManager.isValid()).toBe(false);
    });
    
    test('addToFormData should append nonce', () => {
        const formData = new FormData();
        ClientNonceManager.addToFormData(formData);
        expect(formData.get('nonce')).toBe('test_nonce_123');
    });
    
    test('addToUrl should append nonce to URL', () => {
        const url = ClientNonceManager.addToUrl('http://example.com?action=test');
        expect(url).toContain('nonce=');
        expect(url).toContain('test_nonce_123');
    });
});
```

---

## Tests d'intégration

### Scenario 1: Workflow complet

```bash
# Scénario: Créer → Modifier → Sauvegarder → Charger
1. Créer un nouveau template
2. Ajouter un élément
3. Sauvegarder
4. Vérifier que l'ID est retourné
5. Recharger la page
6. Vérifier que le template est chargé correctement
7. Modifier l'élément
8. Sauvegarder à nouveau
9. Vérifier que les modifications sont persistées
```

### Scenario 2: Expiration de nonce

```bash
# Scénario: Tester le comportement avec nonce expiré
1. Obtenir un nonce valide
2. Attendre 12+ heures (ou simuler via mock)
3. Tenter une opération AJAX
4. Vérifier que le nonce est rafraîchi automatiquement
5. Vérifier que l'opération réussit
```

### Scenario 3: Changement de permissions

```bash
# Scénario: Changer les permissions en cours de session
1. Connecté avec utilisateur à permissions limitées
2. Sauvegarder un template (OK)
3. Admin retire la permission 'edit_posts'
4. Tenter de sauvegarder à nouveau
5. Vérifier que l'accès est refusé
```

---

## Résultats attendus

### ✅ Succès : Tous les tests passent si

- [x] Les nonces sont créés correctement
- [x] Les nonces sont vérifiés correctement
- [x] Les permissions sont appliquées
- [x] Les erreurs sont cohérentes
- [x] Le logging est unifié
- [x] Le rafraîchissement automatique fonctionne
- [x] Les requêtes GET et POST fonctionnent
- [x] Les requêtes simultanées sont gérées
- [x] Les utilisateurs sans permission sont refusés

### ⚠️ Attention : Points à observer

- Logs excessifs dans `debug.log`
- Latence de rafraîchissement de nonce
- Fuites mémoire avec les nonces non utilisés
- Performance sous charge importante

---

## Résolution des problèmes

### Problème: "Nonce invalide" persistant

**Cause possible :**
- Nonce expiré après 12 heures
- Session utilisateur expirée
- Mismatch entre l'action du nonce

**Solution :**
```javascript
// Forcer un rafraîchissement
const fresh = await ClientNonceManager.refreshNonce();
console.log('Nouveau nonce:', fresh);
```

### Problème: "Permissions insuffisantes"

**Cause possible :**
- Utilisateur n'a pas la capacité `edit_posts`
- Session corrompue
- Rôle utilisateur incorrect

**Solution :**
```php
// Vérifier les permissions dans les logs
[PDF Builder] [NonceManager] [INFO] Permissions insuffisantes pour...
```

### Problème: Logs manquants

**Cause possible :**
- `WP_DEBUG` non activé
- Fichier `debug.log` non accessible
- Permissions insuffisantes sur le fichier

**Solution :**
```php
// Ajouter à wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## Checklist de validation

- [ ] Nonce créé au chargement de la page
- [ ] Sauvegarde réussie (admin)
- [ ] Sauvegarde réussie (utilisateur edit_posts)
- [ ] Accès refusé (sans permission)
- [ ] Rafraîchissement automatique fonctionne
- [ ] Logs corrects et traçables
- [ ] GET et POST fonctionnent
- [ ] Requêtes simultanées OK
- [ ] Pas d'erreur dans la console
- [ ] Performance acceptable
- [ ] Pas de fuite mémoire

---

## Conclusion

Une fois tous les tests passés, le système de nonce unifié est prêt pour la production. Les trois niveaux de test (manuel, unitaire, intégration) assurent une couverture complète et une confiance dans le système de sécurité.
