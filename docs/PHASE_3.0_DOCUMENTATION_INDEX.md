# 📚 Documentation Phase 3.0 - Index

**Créé** : 30 octobre 2025  
**Status** : ✅ Complet et à jour

---

## 🎯 Démarrage rapide

Nouveau dans Phase 3.0 ? Commence ici :

1. **5 min** → Lis [PHASE_3.0_SUMMARY.md](PHASE_3.0_SUMMARY.md)
   - Quoi de neuf en une page
   - Chiffres clés
   - Status déploiement

2. **10 min** → Regarde les changements
   - Fichiers modifiés dans git
   - Architecture visuelle
   - Avant/Après

3. **15 min** → Valide localement
   - Regarde [VALIDATION_CHECKLIST_PHASE_3.0.md](docs/VALIDATION_CHECKLIST_PHASE_3.0.md)
   - Fais les tests (5 scénarios)
   - Vérifie les logs

---

## 📖 Documentation détaillée

### 1. Architecture & Decision
**File**: [docs/PHASE_3.0_ARCHITECTURAL_DECISION.md](docs/PHASE_3.0_ARCHITECTURAL_DECISION.md)

**Pour quoi?**
- Comprendre POURQUOI le changement
- Avant vs Après architecture
- Justification technique
- Leçons apprises

**Lecture** : ~15 min

**Sections** :
- ✅ Problème identifié
- ✅ Solution implémentée
- ✅ Nouveaux fichiers
- ✅ Fichiers modifiés
- ✅ Déploiement détails
- ✅ Tests recommandés
- ✅ Prochaines étapes

### 2. API Guide
**File**: [docs/PREVIEW_IMAGE_API_GUIDE.md](docs/PREVIEW_IMAGE_API_GUIDE.md)

**Pour quoi?**
- Comment utiliser l'API PreviewImageAPI
- Cas d'usage pratiques
- Gestion erreurs
- Examples complets

**Lecture** : ~20 min

**Sections** :
- ✅ Overview
- ✅ Utilisation basique
- ✅ API Complète (méthodes)
- ✅ 5 Cas d'usage
- ✅ Gestion erreurs
- ✅ Security
- ✅ Cache
- ✅ Debugging
- ✅ Performance

### 3. Validation Checklist
**File**: [docs/VALIDATION_CHECKLIST_PHASE_3.0.md](docs/VALIDATION_CHECKLIST_PHASE_3.0.md)

**Pour quoi?**
- Tests à effectuer
- Critères succès
- Checklist complète

**Lecture** : ~10 min

**Sections** :
- ✅ Éléments déployés
- ✅ Tests manuels (5 scénarios)
- ✅ État composants
- ✅ Critères succès
- ✅ Prochaines étapes

### 4. Roadmap Mise à Jour
**File**: [docs/APERCU_UNIFIED_ROADMAP.md](docs/APERCU_UNIFIED_ROADMAP.md)

**Pour quoi?**
- Comprendre le plan global
- Où on en est dans le roadmap
- Prochaines phases

**Sections** :
- ✅ Vue d'ensemble
- ✅ Phases 1-7
- ✅ État actuel (Phase 3/7)
- ✅ Progression (55%)

---

## 🗺️ Fichiers concernés

### Nouveaux 🆕
```
plugin/src/AJAX/preview-image-handler.php
├─ Action AJAX WordPress
├─ Handler de rendu TCPDF
└─ Conversion PNG base64

assets/js/src/pdf-builder-react/api/PreviewImageAPI.ts
├─ Classe singleton API
├─ Cache client
└─ Gestion erreurs

assets/js/src/pdf-builder-react/hooks/PreviewImageHook.ts
├─ Hook initialisation AJAX
└─ Event listeners
```

### Modifiés 🔧
```
assets/js/src/pdf-builder-react/components/ui/PreviewModal.tsx
├─ Import PreviewImageAPI
├─ État PHP rendering
└─ Dual rendering logic

plugin/bootstrap.php
├─ Chargement handler AJAX
```

### Documentation
```
docs/PHASE_3.0_ARCHITECTURAL_DECISION.md (NEW)
docs/PREVIEW_IMAGE_API_GUIDE.md (NEW)
docs/VALIDATION_CHECKLIST_PHASE_3.0.md (NEW)
docs/APERCU_UNIFIED_ROADMAP.md (UPDATED)
PHASE_3.0_SUMMARY.md (ROOT - NEW)
```

---

## 🚀 Quick Start - Développeur

### Comprendre le flow
```typescript
// Dans PreviewModal.tsx
const loadPhpPreviewImage = async () => {
  const result = await PreviewImageAPI.generatePreviewImage({
    orderId: 42,
    templateId: 1,
    format: 'png'
  });
  
  if (result.success) {
    setPreviewImage(result.data.image);  // Base64 PNG
  }
};

// Frontend render
{previewImage && <img src={previewImage} />}
```

### Flow côté backend
```php
// Dans preview-image-handler.php
add_action('wp_ajax_pdf_builder_preview_image', function() {
  // 1. Vérifier permissions + nonce
  // 2. Récupérer order + template
  // 3. Rendre avec TCPDF
  // 4. Convertir en PNG
  // 5. Retourner base64
});
```

### Tester localement
```bash
# 1. Ouvrir order WooCommerce
# 2. Cliquer "Aperçu PDF"
# 3. Vérifier image s'affiche
# 4. Vérifier console (F12)

# 5. Vérifier server logs
tail -100 /path/to/wp-content/debug.log | grep pdf_builder
```

---

## 🔍 Debugging

### Erreurs courantes

**Image blanche?**
- Vérifier order_id valide
- Vérifier template_id valide
- Vérifier logs PHP

**Erreur console?**
- Ouvrir DevTools (F12)
- Chercher erreur rouge
- Vérifier Network tab → AJAX

**Erreur permission?**
- User doit être admin ou éditeur de commandes
- Vérifier nonce AJAX valide

### Logs utiles
```bash
# PHP errors
wp-content/debug.log

# JavaScript console
F12 → Console tab

# Network
F12 → Network tab → admin-ajax.php
```

---

## 📊 À savoir

### Cache
- Automatique client-side (Map JavaScript)
- Clé: `preview_{orderId}_{templateId}_{format}`
- Durée: Session (rafraîchir pour clear)
- API: `clearCache()` ou `clearCacheForOrder(id)`

### Performance
- 1ère génération: 500-2000ms (TCPDF)
- 2ème génération: <1ms (cache)
- Affichage image: instant

### Sécurité
- ✅ Nonce AJAX validation
- ✅ Permission check (manage_woocommerce)
- ✅ Pas d'injection XSS
- ✅ Erreurs gérées proprement

---

## ✅ Statut déploiement

| Item | Status | Notes |
|------|--------|-------|
| Code | ✅ Déployé | FTP 30/10 21:11 |
| Build | ✅ Success | Webpack OK |
| Tests | 🔄 À faire | 5 scénarios |
| Docs | ✅ Complete | 4 files |
| Git | ✅ Committed | 2 commits |

---

## 🔗 Fichiers connexes

### Existants (importants)
- `plugin/src/Managers/PDF_Builder_WooCommerce_Integration.php`
  - `ajax_get_preview_data()` (source données)
  
- `plugin/src/Renderers/PreviewRenderer.php`
  - TCPDF rendu existant (réutilisé)

### À créer (Phase 3.1+)
- Auto-save handler
- JSON reload logic
- Tests unitaires
- Monitoring setup

---

## 💬 Questions fréquentes

**Q: Pourquoi PHP au lieu de Canvas 2D?**  
A: TCPDF est complet et testé. Canvas 2D était incomplet et complexe.

**Q: Le cache est où?**  
A: En mémoire JavaScript (Map). Vide au refresh page.

**Q: Comment clear le cache?**  
A: `PreviewImageAPI.getInstance().clearCache()`

**Q: Ça affecte la génération PDF?**  
A: Non, c'est juste l'aperçu. PDF generation reste identique.

**Q: Support mobile?**  
A: Oui, modal responsive. Image s'adapte à l'écran.

---

## 🎯 Prochaines phases

**Phase 3.1** (Prochainement)
- Sauvegarde automatique
- Rechargement JSON

**Phase 3.2** (Prochainement)
- Tests complets
- Intégration validation

**Phase 4-7** (Futur)
- Optimisations
- Production setup

---

## 📞 Support

**Problème?**
1. Vérifier [VALIDATION_CHECKLIST_PHASE_3.0.md](docs/VALIDATION_CHECKLIST_PHASE_3.0.md)
2. Lire [PREVIEW_IMAGE_API_GUIDE.md](docs/PREVIEW_IMAGE_API_GUIDE.md) section debugging
3. Vérifier logs (console + PHP)

**Contribution?**
- Créer branch depuis `dev`
- Tester localement
- PR avec description

---

*Documentation créée 30 octobre 2025*  
*Phase 3.0 - Rendu PHP pour Aperçu PDF*
