# 🎯 Schéma de Flux - JSON Viewer

## Flux d'interaction

```
┌─────────────────────────────────────────────────────────────────┐
│  PDF Builder - Template Editor                                  │
│                                                                   │
│  ┌─ HEADER ────────────────────────────────────────────────────┐ │
│  │  ← Retour    👁️ Aperçu ⚙️ Paramètres | 💾 Enregistrer    │ │
│  └──────────────────────────┬──────────────────────────────────┘ │
│                            │ Clic                                 │
│                            ▼                                      │
│  ┌─ CANVAS EDITOR ─────────────────────────────────────────────┐ │
│  │                                                              │ │
│  │  Éléments du template                                       │ │
│  │  (texte, images, tableaux, etc.)                           │ │
│  │                                                              │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌─ PREVIEW MODAL (1) ──────────────────────────────────────────┐ │
│  │                                                              │ │
│  │  Aperçu du PDF rendupuis                                   │ │
│  │  Canvas avec éléments visuels                              │ │
│  │                                                              │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌─ JSON VIEWER MODAL (2) ──────────────────────────────────────┐ │
│  │                                                              │ │
│  │  📋 JSON Brut du Template (ID: 123)             [×]          │ │
│  │  ─────────────────────────────────────────────────         │ │
│  │                                                              │ │
│  │  {                                                           │ │
│  │    "id": 123,                                               │ │
│  │    "name": "Facture Professionnelle",                       │ │
│  │    "elements": [                                            │ │
│  │      { "type": "text", "content": "FACTURE", ... },       │ │
│  │      { "type": "image", "x": 50, "y": 30, ... }           │ │
│  │    ]                                                        │ │
│  │  }                                                           │ │
│  │                                                              │ │
│  │  ─────────────────────────────────────────────────         │ │
│  │  [📋 Copier JSON] [💾 Télécharger] [Fermer]              │ │
│  │                                                              │ │
│  └──────────────────────────────────────────────────────────────┘ │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Fonctionnalités de la modale JSON

```
┌─────────────────────────────────────────────┐
│  Modale JSON Viewer                         │
├─────────────────────────────────────────────┤
│                                             │
│  ✅ Visualisation                          │
│     • JSON formaté et indenté              │
│     • Font monospace (Courier)            │
│     • Scrollable (si contenu long)         │
│                                             │
│  ✅ Actions disponibles                   │
│     • Copier → Presse-papiers             │
│     • Télécharger → Fichier .json         │
│     • Fermer → Fermer la modale            │
│                                             │
│  ✅ Indicateurs visuels                    │
│     • "✅ Copié!" pendant 2 sec           │
│     • Couleurs des boutons cohérentes     │
│                                             │
└─────────────────────────────────────────────┘
```

## Interactions utilisateur

### Scénario 1 : Visualiser le JSON
```
Utilisateur clique "👁️ Aperçu"
                    │
                    ▼
        État: showJsonModal = true
                    │
                    ▼
    Modale JSON s'affiche
         JSON formaté visible
```

### Scénario 2 : Copier le JSON
```
Utilisateur clique "📋 Copier JSON"
                    │
                    ▼
    navigator.clipboard.writeText(JSON)
                    │
                    ▼
    setCopySuccess(true)
                    │
                    ▼
    Bouton affiche "✅ Copié!" (2s)
                    │
                    ▼
    Retour à "📋 Copier JSON"
```

### Scénario 3 : Télécharger le JSON
```
Utilisateur clique "💾 Télécharger"
                    │
                    ▼
    Crée Blob(JSON)
                    │
                    ▼
    URL.createObjectURL(blob)
                    │
                    ▼
    Crée element <a> virtuel
                    │
                    ▼
    Déclenche download:
    "template-123-[timestamp].json"
                    │
                    ▼
    Fichier sauvegardé localement
```

### Scénario 4 : Fermer la modale
```
Utilisateur clique "Fermer" ou "×"
                    │
                    ▼
    setState(showJsonModal = false)
                    │
                    ▼
    Modale disparaît
                    │
                    ▼
    Aperçu PDF reste visible
```

## Structure des données affichées

```
{
  template: {
    id: number                          ← ID du template
    name: string                        ← Nom du template
    description: string                 ← Description
    tags: string[]                      ← Tags/Catégories
    
    canvasWidth: number                 ← Largeur canvas (px)
    canvasHeight: number                ← Hauteur canvas (px)
    marginTop: number                   ← Marge haut
    marginBottom: number                ← Marge bas
    
    showGuides: boolean                 ← Afficher les guides
    snapToGrid: boolean                 ← Magnétisme grille
    
    elements: [                         ← Tableau des éléments
      {
        id: string
        type: "text" | "image" | "line" | "rect" | ...
        x: number                       ← Position X
        y: number                       ← Position Y
        width: number                   ← Largeur
        height: number                  ← Hauteur
        rotation: number                ← Rotation (deg)
        style: {...}                    ← Styles CSS
        content: string                 ← Contenu
        ... autres propriétés
      },
      ...
    ],
    
    settings: {...}                     ← Paramètres additionnels
    
    createdAt: string                   ← Date création
    updatedAt: string                   ← Date modif
    isModified: boolean                 ← Modifié?
  }
}
```

## Performance

```
┌──────────────────────────────────────────┐
│  Optimisations appliquées                │
├──────────────────────────────────────────┤
│                                          │
│ ✅ JSON.stringify() appelé une fois     │
│    au clic du bouton (pas à chaque      │
│    rendu)                                │
│                                          │
│ ✅ Modale utilise position: fixed       │
│    (pas affectée par scroll/layout)     │
│                                          │
│ ✅ Pas de re-render inutile             │
│    (séparation des states)              │
│                                          │
│ ✅ Blob créé juste avant download       │
│    (pas stocké en mémoire)              │
│                                          │
│ ✅ URL.revokeObjectURL() appelé         │
│    après download (libère ressources)   │
│                                          │
└──────────────────────────────────────────┘
```

## Design Responsive

```
DESKTOP (> 768px)          MOBILE (< 768px)
─────────────────          ────────────────

Modale: 90vw               Modale: 100vw
Hauteur: 85vh              Hauteur: 90vh

Boutons: side by side      Boutons: stacked
Font: 12px (monospace)     Font: 11px
Padding: 16px              Padding: 12px
```

## Intégration avec BuilderContext

```
┌─ BuilderContext ──────────────────┐
│                                   │
│  state = {                        │
│    template: {                    │
│      id, name, elements, ...      │
│    }                              │
│  }                                │
│                                   │
│  Utilisé par:                     │
│  - Header.tsx (JSON viewer)       │
│  - Canvas.tsx (affichage)         │
│  - useTemplate.ts (gestion)       │
│                                   │
└─────────────────────────────────────┘
         │
         │ JSON.stringify(state.template)
         │
         ▼
  ┌─ Modale JSON ────┐
  │                  │
  │ {                │
  │   ...            │
  │ }                │
  │                  │
  └──────────────────┘
```
