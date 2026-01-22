# Preview System - Générateurs et Managers

## Structure du dossier

```
plugin/preview-system/
├── index.php                          # Loader principal
├── php/
│   ├── PreviewSystem.php              # Système stub minimaliste
│   ├── PreviewImageAPI.php            # API stub
│   └── PreviewAjaxHandler.php         # Handler AJAX (déprécié)
├── generators/                         # Système de génération complet
│   ├── BaseGenerator.php              # Classe abstraite de base
│   ├── PDFGenerator.php               # Générateur PDF avec DomPDF
│   ├── CanvasGenerator.php            # Générateur Canvas (fallback)
│   ├── ImageGenerator.php             # Générateur image GD (fallback final)
│   └── GeneratorManager.php           # Gestionnaire avec fallback automatique
├── managers/                           # Managers de génération
│   ├── PdfBuilderPreviewGenerator.php # Générateur d'aperçu (legacy)
│   ├── PDF_Builder_Screenshot_Renderer.php
│   ├── PDF_Builder_Thumbnail_Manager.php
│   └── PDF_Builder_Preview_Generator.php
├── hooks/                              # Hooks React et API
│   ├── usePreview.ts                  # Hook React pour l'aperçu
│   └── PreviewImageAPI.ts             # API image de prévisualisation
├── js/
│   ├── pdf-preview-api-client.js      # Client API stub (UI uniquement)
│   └── pdf-preview-integration.js     # Intégration jQuery
├── README.md                          # Documentation
└── GENERATORS_GUIDE.md                # Guide des générateurs
```

## Fonction

Le système de génération d'aperçu est organisé par couches:

1. **Layer 1: Générateurs** (plugin/preview-system/generators/)
   - BaseGenerator: Interface abstraite commune
   - PDFGenerator: DomPDF + fallback Imagick/Ghostscript
   - CanvasGenerator: GD pour rendu côté serveur
   - ImageGenerator: Fallback GD pur
   - GeneratorManager: Orchestration avec fallback automatique

2. **Layer 2: Managers** (plugin/preview-system/managers/)
   - Orchestration métier
   - Gestion cache
   - Intégration WooCommerce
   - Génération miniatures

3. **Layer 3: API** (plugin/preview-system/hooks/)
   - usePreview: Hook React pour gérer état aperçu
   - PreviewImageAPI: Client API pour générer images

4. **Layer 4: UI** (plugin/preview-system/js/)
   - pdf-preview-api-client: Stub API (génération désactivée)
   - pdf-preview-integration: Intégration jQuery existante

## État

- ✅ Générateurs: Complet (BaseGenerator, PDFGenerator, CanvasGenerator, ImageGenerator, GeneratorManager)
- ✅ Stub UI: Implémenté (génération retourne erreur)
- ⏳ Managers: À déplacer depuis plugin/src/Managers/
- ⏳ Hooks React: À déplacer depuis src/js/react/hooks/
- ⏳ API TypeScript: À déplacer depuis src/js/react/api/

## Évolution future

Pour réactiver la génération:

1. Implémenter les managers depuis plugin/src/Managers/
2. Implémenter les hooks React et l'API TypeScript
3. Passer les données du stub à la vraie génération
4. Activer les endpoints AJAX correspondants
