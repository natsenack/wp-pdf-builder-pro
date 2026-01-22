# Refonte du Système d'Aperçu - Résumé des Changements

## 📋 Vue d'ensemble

Le système d'aperçu a été complètement repensé de zéro pour plus de clarté, de performance et de maintenabilité.

## 🔧 Fichiers modifiés

### 1. `SimplePreviewGenerator.php` (Génération AJAX - Point d'entrée principal)
**État avant** : Code mélangé avec plusieurs responsabilités  
**État après** : Simplifié avec responsabilités claires

**Changements principaux :**
- ✅ Point d'entrée AJAX unique via `handle()`
- ✅ Validation stricte des permissions et nonce
- ✅ Extraction simple du `template_data` du POST
- ✅ Génération d'image PNG via `GeneratorManager`
- ✅ Système de cache efficace (24h par défaut)
- ✅ Gestion d'erreur propre avec réponses JSON

**Flux d'exécution :**
```
POST /wp-admin/admin-ajax.php?action=pdf_builder_generate_preview
  ↓
1. Vérifier permissions (manage_options)
2. Vérifier nonce (pdf_builder_nonce)
3. Extraire template_data JSON
4. Vérifier présence d'éléments
5. Générer image avec cache
6. Retourner URL ou erreur JSON
```

### 2. `PreviewImageAPI.php` (API REST - Compatibilité)
**État avant** : Énorme fichier (1200+ lignes) avec système de cache compliqué, cron, rate limiting, etc.  
**État après** : Simplifié à 252 lignes - REST API uniquement

**Changements principaux :**
- ✅ Suppression du système de cron compliqué
- ✅ Suppression du rate limiting complexe
- ✅ Suppression des métriques de performance
- ✅ Conservation des 2 routes REST essentielles :
  - `POST /wp-pdf-builder-pro/v1/preview` - Aperçu
  - `POST /wp-pdf-builder-pro/v1/download` - Téléchargement
- ✅ Cache simple 24h pour les aperçus
- ✅ Cache nettoyage automatique (7 jours)

## 📂 Structure du Cache

```
wp-content/uploads/pdf-builder-cache/previews/
├── [hash-md5].png      (Aperçu mise en cache 24h)
└── [autre-hash].png
```

**Clé de cache :** `md5(json_encode(elements) . quality)`

## 🔑 Points d'intégration

### Action AJAX
```php
// Dans le frontend JavaScript
jQuery.post(ajaxurl, {
    action: 'pdf_builder_generate_preview',
    nonce: wpnonce,
    template_data: JSON.stringify(templateData),
    quality: 150
}, function(response) {
    if (response.success) {
        console.log(response.data.url);
    }
});
```

### API REST
```php
// Exemples d'utilisation
POST /wp-json/wp-pdf-builder-pro/v1/preview
Content-Type: application/json
Authorization: Bearer <token>

{
  "context": "editor",
  "templateData": {...},
  "quality": 150
}
```

## ⚡ Améliorations de Performance

| Aspect | Avant | Après |
|--------|-------|-------|
| Taille du fichier API | 1200+ lignes | 252 lignes |
| Complexité du cache | Cron + transients + intelligent | Simple MD5 + fichier |
| Temps de réponse | Variable (cron delay) | Immédiat |
| Gestion d'erreur | Complexe | Directe |
| Maintenabilité | Difficile | Simple |

## 🛡️ Sécurité

✅ **Permissions** : Vérifiées selon le contexte
- `editor` → `manage_options`
- `metabox` → `edit_shop_orders`

✅ **Nonce** : Obligatoire pour AJAX (`pdf_builder_nonce`)

✅ **Validations** :
- JSON bien formé requis
- Éléments présents obligatoires
- Qualité entre 50-300

## 🧹 Nettoyage du Cache

Automatique lors de l'accès (fichiers > 7 jours supprimés):
```php
PreviewImageAPI::cleanupCache();
```

## ⚠️ Notes Importantes

1. **Les deux fichiers coexistent** : 
   - `SimplePreviewGenerator` pour AJAX (nouveau)
   - `PreviewImageAPI` pour REST API (allégé)

2. **Ancien code supprimé** :
   - Cron scheduling
   - Rate limiting complexe
   - Système de cache intelligent
   - Métriques de performance
   - Gestion des transients

3. **À vérifier** :
   - Les appels AJAX existants utilisent-ils `pdf_builder_nonce` ?
   - Les données template sont-elles valides JSON ?
   - Les permissions sont-elles appropriées pour vos cas d'usage ?

## 📝 Prochaines étapes

1. Tester la génération AJAX
2. Tester les routes REST
3. Vérifier le cache fonctionne correctement
4. Valider les permissions dans les contextes réels
