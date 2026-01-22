# Preview System - PDF Builder Pro

## 🏗️ Architecture

Système d'aperçu organisé en **4 couches**:

```
┌─────────────────────────────────────────────────────────────┐
│ LAYER 4: UI & React Hooks                                   │
│ - usePreview (React hook for state management)              │
│ - PreviewImageAPI (TypeScript client)                       │
│ - pdf-preview-integration.js (jQuery UI)                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ LAYER 3: Preview API (Stub - UI Only)                       │
│ - PreviewSystem.php (minimal ~25 lines)                     │
│ - PreviewImageAPI.php (stub ~15 lines)                      │
│ - PreviewAjaxHandler.php (deprecated)                       │
│ Returns: Promise.reject() for all generation attempts       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ LAYER 2: Managers & Business Logic (TODO)                   │
│ - PdfBuilderPreviewGenerator.php (caching, WooCommerce)     │
│ - PDF_Builder_Screenshot_Renderer.php (canvas capture)      │
│ - PDF_Builder_Thumbnail_Manager.php (miniatures)            │
│ Location: Currently in plugin/src/Managers/ - to migrate    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ LAYER 1: Generators (Fallback System) ✅ COMPLETE           │
│                                                              │
│ GeneratorManager (orchestration)                            │
│          ↓                    ↓                     ↓        │
│    PDFGenerator         CanvasGenerator      ImageGenerator │
│   (DomPDF based)        (GD based)           (GD fallback)  │
│          ↓                                                   │
│   - Imagick convert                                         │
│   - Ghostscript convert                                     │
│   - GD placeholder                                          │
└─────────────────────────────────────────────────────────────┘
```

## 📁 Fichiers et structure

```
plugin/preview-system/
├── index.php                              # Main loader (tous les includes)
│
├── generators/                            # LAYER 1: Système de génération ✅
│   ├── BaseGenerator.php                  # Classe abstraite (373 lignes)
│   ├── PDFGenerator.php                   # Générateur PDF (999 lignes)
│   ├── CanvasGenerator.php                # Générateur Canvas/GD (353 lignes)
│   ├── ImageGenerator.php                 # Générateur image (129 lignes)
│   └── GeneratorManager.php               # Orchestration (412 lignes)
│
├── managers/                              # LAYER 2: Logique métier (TODO)
│   ├── PdfBuilderPreviewGenerator.php     # À migrer depuis plugin/src/Managers/
│   ├── PDF_Builder_Screenshot_Renderer.php
│   └── PDF_Builder_Thumbnail_Manager.php
│
├── php/                                   # LAYER 3: Preview API (Stub) ✅
│   ├── PreviewSystem.php                  # Système stub (~25 lignes)
│   ├── PreviewImageAPI.php                # API stub (~15 lignes)
│   └── PreviewAjaxHandler.php             # Handler AJAX (déprécié)
│
├── js/                                    # LAYER 4a: JavaScript
│   ├── pdf-preview-api-client.js          # Client API stub
│   └── pdf-preview-integration.js         # Intégration jQuery (référence)
│
├── hooks/                                 # LAYER 4b: React Hooks (TODO)
│   ├── usePreview.ts                      # À migrer depuis src/js/react/hooks/
│   └── PreviewImageAPI.ts                 # À migrer depuis src/js/react/api/
│
├── GENERATORS_GUIDE.md                    # Guide détaillé des générateurs
├── README.md                              # Documentation originale
└── README_COMPLETE.md                     # Cette documentation détaillée
```

## ✅ État de chaque composant

### Générateurs (LAYER 1) ✅ COMPLET
| Fichier | Lignes | Statut | Description |
|---------|--------|--------|-------------|
| BaseGenerator.php | 373 | ✅ Complet | Classe abstraite commune |
| PDFGenerator.php | 999 | ✅ Complet | Générateur PDF avec DomPDF + fallbacks |
| CanvasGenerator.php | 353 | ✅ Complet | Générateur Canvas (rendu serveur GD) |
| ImageGenerator.php | 129 | ✅ Complet | Générateur image (fallback final GD) |
| GeneratorManager.php | 412 | ✅ Complet | Orchestration + fallback automatique |

**Caractéristiques:**
- Fallback automatique en cas d'erreur
- Logging détaillé de chaque étape
- Métriques de performance
- Support format: PDF, PNG, JPG

### Managers (LAYER 2) ⏳ EN ATTENTE
| Fichier | Emplacement actuel | Statut | Lignes |
|---------|-------------------|--------|--------|
| PdfBuilderPreviewGenerator.php | `plugin/src/Managers/` | ⏳ À migrer | 521 |
| PDF_Builder_Screenshot_Renderer.php | `plugin/src/Managers/` | ⏳ À migrer | 372 |
| PDF_Builder_Thumbnail_Manager.php | `plugin/src/Managers/` | ⏳ À migrer | ? |
| PDF_Builder_Preview_Generator.php | `plugin/src/AJAX/Managers/` | ⏳ À migrer | 103 |

**À faire:**
- [ ] Créer dossier `managers/`
- [ ] Migrer PdfBuilderPreviewGenerator.php
- [ ] Migrer PDF_Builder_Screenshot_Renderer.php
- [ ] Migrer PDF_Builder_Thumbnail_Manager.php

### Preview API (LAYER 3) ✅ STUB COMPLET
| Fichier | Lignes | Statut | Description |
|---------|--------|--------|-------------|
| PreviewSystem.php | ~25 | ✅ Stub | Système minimal |
| PreviewImageAPI.php | ~15 | ✅ Stub | API stub |
| PreviewAjaxHandler.php | ~10 | ✅ Stub | Handler AJAX |

**Caractéristiques:**
- Retourne `Promise.reject()` pour toute tentative de génération
- Buttons, metabox, modales restent intacts (UI uniquement)
- Pas d'endpoints AJAX implémentés
- Prêt pour réactivation future

### Hooks & API (LAYER 4) ⏳ EN ATTENTE
| Fichier | Type | Emplacement | Statut |
|---------|------|-----------|--------|
| usePreview.ts | React Hook | `src/js/react/hooks/` | ⏳ À migrer |
| PreviewImageAPI.ts | TypeScript | `src/js/react/api/` | ⏳ À migrer |
| pdf-preview-integration.js | jQuery | `src/js/admin/` | ✅ Référencé |

**À faire:**
- [ ] Créer dossier `hooks/`
- [ ] Migrer usePreview.ts
- [ ] Migrer PreviewImageAPI.ts
- [ ] Mettre à jour imports webpack

## 🔄 Système de Fallback

```
Tentative 1: GeneratorManager → PDFGenerator (DomPDF)
    ↓ (en cas d'erreur)
    ├─ Imagick conversion
    ├─ Ghostscript conversion
    ├─ External API conversion
    └─ GD placeholder

Tentative 2: CanvasGenerator (serveur GD)
    ↓ (en cas d'erreur)
    └─ Image placeholder

Tentative 3: ImageGenerator (fallback final)
    ↓ (garantit toujours une réponse)
    └─ Image simple avec informations
```

## 🎯 État actuel du système

### ✅ Complété
- Générateurs (Layer 1) - architecture complète avec fallback
- Stub API (Layer 3) - UI-only, génération désactivée
- Documentation et guides

### ⏳ En attente
- Managers (Layer 2) - logique métier à migrer
- React Hooks (Layer 4) - TypeScript à migrer
- Tests d'intégration complets
- Réactivation de la génération

### ❌ Intentionnellement désactivé
- Génération réelle d'aperçu (retourne erreur)
- Endpoints AJAX de génération
- Conversion PDF → Image en temps réel
- Caching d'aperçu

## 📊 Fichiers supprimés lors de la centralisation

```
❌ plugin/api/PreviewSystem.php
❌ plugin/api/SimplePreviewGenerator.php
❌ plugin/api/PreviewImageAPI.php
❌ plugin/src/AJAX/PDF_Builder_Preview_Ajax.php
❌ src/js/admin/pdf-preview-api-client.js (old version)
```

Tous remplacés par le nouveau système dans `plugin/preview-system/`

## 🚀 Utilisation

### Charger le système
```php
// Dans plugin/bootstrap.php
require_once PDF_BUILDER_PLUGIN_DIR . 'preview-system/index.php';
```

### Utiliser les générateurs
```php
use PDF_Builder\Generators\GeneratorManager;

$manager = new GeneratorManager();
$result = $manager->generatePreview(
    $template_data,    // array
    $data_provider,    // DataProviderInterface
    'png',            // 'pdf', 'png', 'jpg'
    []                // options
);
```

### API JavaScript (actuellement stub)
```javascript
// Retourne Promise.reject()
window.pdfPreviewAPI.generateEditorPreview(templateData, options)
    .then(response => console.log(response))
    .catch(err => console.log('Preview generation disabled'));
```

## 🔮 Plan d'évolution

### Phase 1: ✅ Centralisation (COMPLÉTÉE)
- ✅ Créer dossier `plugin/preview-system/`
- ✅ Créer générateurs modulaires
- ✅ Implémenter stub API
- ✅ Supprimer anciens fichiers
- ✅ Documenter architecture

### Phase 2: ⏳ Intégration managers (À FAIRE)
- [ ] Créer dossier `managers/`
- [ ] Migrer PdfBuilderPreviewGenerator
- [ ] Migrer Screenshot Renderer
- [ ] Migrer Thumbnail Manager
- [ ] Ajouter logique de caching
- [ ] Intégrer WooCommerce

### Phase 3: ⏳ Activation React/TypeScript (À FAIRE)
- [ ] Créer dossier `hooks/`
- [ ] Migrer usePreview hook
- [ ] Migrer PreviewImageAPI TypeScript
- [ ] Mettre à jour webpack.config.cjs
- [ ] Tester intégration complète

### Phase 4: ⏳ Réactivation génération (À FAIRE)
- [ ] Réimplémenter endpoints AJAX
- [ ] Activer génération dans PDFGenerator
- [ ] Tester conversions PDF → Image
- [ ] Ajouter monitoring
- [ ] Performance optimization

### Phase 5: 🔮 Optimisations avancées (FUTURE)
- Compression images automatique
- Rate limiting
- Cache distribuée
- Async job processing
- Metrics & monitoring

## 📝 Poids des fichiers

| Composant | Fichiers | Lignes | Taille |
|-----------|----------|--------|--------|
| Générateurs | 5 | 2,266 | ~80 KB |
| Managers (TODO) | 3 | ~1,000 | ~35 KB |
| API Stub | 3 | ~50 | ~2 KB |
| Hooks (TODO) | 2 | ~500 | ~20 KB |
| **Total** | **13** | **~3,816** | **~137 KB** |

## 🔗 Références

- [GENERATORS_GUIDE.md](GENERATORS_GUIDE.md) - Guide détaillé de chaque générateur
- [plugin/bootstrap.php](../bootstrap.php#L783-L784) - Point de charge
- [plugin/src/Core/PDF_Builder_Loader.php](../src/Core/PDF_Builder_Loader.php#L244-L245) - Loader supplémentaire
- [webpack.config.cjs](../../webpack.config.cjs#L25) - Configuration webpack

## 💡 Notes importantes

1. **Architecture modulaire**: Chaque générateur peut fonctionner indépendamment
2. **Fallback automatique**: GeneratorManager tente automatiquement les alternatives
3. **Logging complet**: Toutes les étapes sont loggées pour le debugging
4. **Prêt pour réactivation**: Le système est conçu pour réactiver la génération sans modifications majeures
5. **Zero breaking changes**: Les interfaces publiques restent compatibles

## ✋ Support & Questions

Pour plus de détails sur les générateurs, voir [GENERATORS_GUIDE.md](GENERATORS_GUIDE.md)
