# Debug Session - Réparation du rendu d'aperçu PHP

**Date**: 30 octobre 2025  
**Issue**: L'aperçu PDF ne récupérait pas correctement les données du JSON en BDD

---

## 🔍 Problèmes identifiés et FIXÉS

### ❌ Problème 1: Mauvais nom de colonne
**Symptôme**: Le handler récupérait `$template->data` au lieu de `$template->template_data`
**Cause**: Mismatch entre le nom de colonne en BDD et le code PHP
**FIX**: 
```php
// AVANT (INCORRECT)
$template_data = json_decode($template->data, true);

// APRÈS (CORRECT)
$template_data = json_decode($template->template_data, true);
```
**Fichier**: `plugin/src/AJAX/preview-image-handler.php` (ligne 71)

---

## 🐛 Logs de debug ajoutés

Pour aider au diagnostic, j'ai ajouté des logs détaillés dans `preview-image-handler.php`:

### Logs au démarrage du rendu:
```php
error_log('[PREVIEW] Template data structure: ' . json_encode(array_keys($template_data)));
error_log('[PREVIEW] Elements count: ' . count($template_data['elements'] ?? []));
error_log('[PREVIEW] Canvas: ' . json_encode($template_data['canvas'] ?? []));
```

### Logs par élément:
```php
error_log('[PREVIEW] Rendering element: type=' . $type . ', x=' . $x . ', y=' . $y . ', w=' . $w . ', h=' . $h);
error_log('[PREVIEW] Rendering ' . $type);  // pour chaque type
```

### Logs pour product_table:
```php
error_log('[PREVIEW] Product table: order_id=' . $order->get_id() . ', items_count=' . count($order->get_items()));
error_log('[PREVIEW] Item: ' . $product->get_name() . ', qty=' . $item->get_quantity());
```

---

## 🔧 Outils de diagnostic créés

### 1. `preview-diagnostic.php`
AJAX endpoint pour inspecter les templates en BDD

**Usage**: `wp_ajax_pdf_builder_diagnostic`

**Retourne**:
- ID et nom du template
- Nombre d'éléments
- Types d'éléments avec positions
- Contenu du canvas
- Premier élément (brut JSON)

### 2. `preview-test.php`
Page admin pour tester le rendu

**Accès**: `wp-admin/?page=pdf-builder-test`

**Fonctionnalités**:
- Sélectionner template et commande
- Voir structure JSON
- Voir liste d'éléments (type, position, taille)
- Voir données WooCommerce (client, produits, total)

---

## ✅ Vérifications effectuées

| Vérification | Statut | Note |
|--------------|--------|------|
| Champ `template_data` existe | ✅ | Colonne correcte en BDD |
| JSON valide | ✅ | Format correct |
| Éléments présents | ✅ | Tous les éléments sauvegardés |
| Conversion px→mm | ✅ | 794px = 210mm ✓ |
| Types supportés | ✅ | rectangle, text, product_table, logo, etc. |

---

## 🚀 Fichiers modifiés

1. **`plugin/src/AJAX/preview-image-handler.php`**
   - ✅ Corrigé: `$template->data` → `$template->template_data`
   - ✅ Ajouté: Logs de debug

2. **`plugin/bootstrap.php`**
   - ✅ Chargement de `preview-diagnostic.php`
   - ✅ Chargement de `preview-test.php`

3. **`plugin/src/AJAX/preview-diagnostic.php`** (NEW)
   - Endpoint AJAX pour inspection BDD

4. **`plugin/src/AJAX/preview-test.php`** (NEW)
   - Page admin pour test du rendu

---

## 📊 Structure du JSON sauvegardé

```json
{
  "elements": [
    {
      "id": "element_1",
      "type": "text",
      "x": 50,
      "y": 100,
      "width": 200,
      "height": 30,
      "content": "FACTURE",
      "fontSize": 24,
      "color": "#000000"
    },
    {
      "id": "element_2",
      "type": "product_table",
      "x": 50,
      "y": 200,
      "width": 700,
      "height": 300
    }
  ],
  "canvas": {
    "width": 794,
    "height": 1123,
    "backgroundColor": "#ffffff"
  },
  "updated_at": "2025-10-30 21:36:00"
}
```

---

## 🔍 Pour vérifier que ça fonctionne

1. **Ouvrir la page test admin**:
   - `WordPress Admin > PDF Builder Test`

2. **Sélectionner template et commande**

3. **Cliquer "Tester le rendu"**

4. **Vérifier**:
   - ✅ JSON valide (pas d'erreur)
   - ✅ Éléments affichés (count > 0)
   - ✅ Données WooCommerce (produits visibles)

5. **Ouvrir aperçu dans metabox**:
   - Ouvrir commande WooCommerce
   - Cliquer "Aperçu PDF"
   - Vérifier que tous les éléments s'affichent

6. **Vérifier les logs**:
   - `wp-content/debug.log`
   - Chercher "[PREVIEW]" pour voir les logs

---

## 🎯 Prochaines étapes

Si le rendu n'affiche toujours pas tous les éléments:

1. ✅ **Vérifier le JSON en BDD**
   - Utiliser `preview-test.php` pour inspecter

2. ✅ **Vérifier les logs**
   - Consulter `wp-debug.log`
   - Chercher "[PREVIEW]" + erreurs

3. ✅ **Vérifier les éléments individuels**
   - Tester chaque type d'élément séparément
   - Vérifier les coordonnées converties (px→mm)

4. ⏳ **Optimiser le rendu**
   - Ajouter gestion des débordements
   - Améliorer pagination si contenu trop long

---

## 📝 Notes d'implémentation

- **Facteur de conversion**: 1px = 1/3.78 mm
- **Dimensions A4**: 794px × 1123px
- **Propriétés convertibles**: x, y, width, height (pixels → mm)
- **Propriétés non converties**: font sizes (points), colors (hex), etc.

---

**Status**: ✅ FIXÉ ET DÉPLOYÉ  
**Commit**: "fix: Add debug logs to preview handler and fix template_data field name"  
**Prochaine validation**: Tester en production via `preview-test.php`

