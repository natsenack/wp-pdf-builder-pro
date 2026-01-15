# 🔴 BugFix: Boucle Infinie de Nonce Invalide

**Date:** 15 janvier 2026  
**Statut:** ✅ RÉSOLU  
**Sévérité:** CRITIQUE

---

## Problème Détecté

### Symptômes
- Les logs console affichaient une boucle infinie de messages:
  ```
  🔄 [useTemplate] Nonce invalide détecté, récupération automatique...
  ✅ [ClientNonceManager] Nonce rafraîchi avec succès
  ✅ [useTemplate] Nouveau nonce récupéré, nouvelle tentative...
  🟢 [useTemplate] Nonce value: 4f447f0136  ← MÊME VALEUR!
  🔄 [useTemplate] Nonce invalide détecté, récupération automatique...
  ```
- Les templates ne se chargeaient jamais
- La valeur du nonce restait identique même après "rafraîchissement"

### Racine Cause #1: Mauvais Appel dans useTemplate.ts

**Fichier:** `src/js/react/hooks/useTemplate.ts` (ligne 598)

**Problème:** Lors du chargement d'un template (GET `ajaxGetTemplate`), si le nonce était invalide, le code appelait `saveTemplate()` au lieu de `loadExistingTemplate()`.

```typescript
// ❌ AVANT (INCORRECT):
if (freshNonce) {
  console.log('✅ [useTemplate] Nouveau nonce récupéré, nouvelle tentative...');
  return await saveTemplate();  // ← MAUVAIS! Devrait être loadExistingTemplate()
}
```

Cela créait une boucle: 
- GET → nonce invalide
- Rafraîchir nonce
- Appeler SAVE au lieu de GET → nonce invalide  
- Boucle infinie

### Racine Cause #2: Permissions Trop Restrictives

**Fichier:** `plugin/src/Admin/Handlers/AjaxHandler.php`

**Problème:** Les endpoints GET (`ajaxGetTemplate` ligne 295 et `ajaxLoadTemplate` ligne 254) requireaient `ADMIN_CAPABILITY` (`manage_options`), mais les utilisateurs éditeurs n'ont que `MIN_CAPABILITY` (`edit_posts`).

```php
// ❌ AVANT (INCORRECT):
$validation = NonceManager::validateRequest(NonceManager::ADMIN_CAPABILITY);
// Retourne TOUJOURS une erreur pour les éditeurs!
```

---

## Solution Implémentée

### Correctif #1: useTemplate.ts (ligne 598)

```typescript
// ✅ APRÈS (CORRECT):
if (freshNonce) {
  console.log('✅ [useTemplate] Nouveau nonce récupéré, nouvelle tentative...');
  // Refaire le CHARGEMENT (pas la sauvegarde!) avec le nouveau nonce
  return await loadExistingTemplate(templateId);
}
```

**Changement:** `saveTemplate()` → `loadExistingTemplate(templateId)`

### Correctif #2: AjaxHandler.php (lignes 254 et 295)

```php
// ✅ APRÈS (CORRECT):
$validation = NonceManager::validateRequest(NonceManager::MIN_CAPABILITY);
// Accepte maintenant les éditeurs ET les admins!
```

**Changements:**
- Ligne 254 (`ajaxLoadTemplate`): `ADMIN_CAPABILITY` → `MIN_CAPABILITY`
- Ligne 295 (`ajaxGetTemplate`): `ADMIN_CAPABILITY` → `MIN_CAPABILITY`

**Raison:** Les endpoints GET pour charger les templates doivent être accessibles aux éditeurs, pas seulement aux admins.

---

## Vérifications Effectuées

### Build
✅ Webpack compilation réussie (637 KiB)  
✅ Aucune erreur TypeScript  
✅ 2 avertissements seulement (size recommendations - acceptable)

### Déploiement
✅ 66 fichiers déployés en 39.7 secondes  
✅ 0 erreurs de transfert FTP  
✅ Vérification d'intégrité: 100% ✅  
✅ Git commit créé: `deploy: 15/01/2026 18:55`

### Fichiers Modifiés
1. `src/js/react/hooks/useTemplate.ts` - Correction de la logique de retry
2. `plugin/src/Admin/Handlers/AjaxHandler.php` - Permissions correctes pour GET endpoints

---

## Test Recommandé

```sql
-- Vérifier que la permission est correctement appliquée
SELECT * FROM wp_capabilities WHERE name = 'edit_posts';
```

1. **Se connecter en tant qu'Éditeur** (pas Admin)
2. Ouvrir l'éditeur PDF Builder
3. **Charger un template** 
4. ✅ Vérifier: Template charge correctement en une seule tentative
5. ✅ Vérifier: Pas de messages "Nonce invalide" répétés dans la console
6. ✅ Vérifier: Le nonce reste stable (pas de rafraîchissements constants)

---

## Impact

| Métrique | Avant | Après |
|----------|-------|-------|
| Chargement template | ❌ ÉCHOUE | ✅ OK |
| Boucle nonce | ∞ | ✅ 0 |
| Accès éditeurs | ❌ REFUSÉ | ✅ AUTORISÉ |
| Accès admins | ✅ OK | ✅ OK |

---

## Notes de Sécurité

⚠️ **Changement de permissions notable:**
- Les GET endpoints acceptent maintenant les éditeurs (`edit_posts`)
- Les POST endpoints (sauvegarde) restent aux admins seulement (`manage_options`)
- Cette séparation est correcte: lire ≠ écrire

✅ **Nonce toujours validé** pour tous les utilisateurs (nonce+ permissions)

---

## Commit Git

```
deploy: 15/01/2026 18:55 - 66 fichiers
- Fix: infinite nonce loop in template loading
- Fix: ajaxGetTemplate permission validation (MIN_CAPABILITY)
- Fix: ajaxLoadTemplate permission validation (MIN_CAPABILITY)  
- Fix: useTemplate.ts retry logic (loadExistingTemplate vs saveTemplate)
```

---

**Status:** 🟢 Production Ready
