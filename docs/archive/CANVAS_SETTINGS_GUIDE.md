# Canvas Settings Manager - Guide d'Utilisation

## Vue d'ensemble

Le Canvas Settings Manager est un système complet de gestion des paramètres du canvas PDF Builder Pro. Il permet de configurer et d'utiliser plus de 50 paramètres différents pour contrôler l'apparence et le comportement du canvas.

## Architecture

### 1. Backend PHP (Canvas_Manager)

**Fichier**: `plugin/src/Canvas/Canvas_Manager.php`

La classe `Canvas_Manager` est un singleton qui gère:
- Le stockage des paramètres dans les options WordPress
- L'injection des paramètres dans les scripts React
- Des méthodes de groupement pour accéder à des sous-ensembles de paramètres

**Initialisation**:
```php
use WP_PDF_Builder_Pro\Canvas\Canvas_Manager;

$canvas_manager = Canvas_Manager::get_instance();
```

**Accès aux paramètres**:
```php
// Tous les paramètres
$all_settings = $canvas_manager->get_all_settings();

// Un paramètre spécifique
$width = $canvas_manager->get_setting('default_canvas_width', 794);

// Groupes de paramètres
$dimensions = $canvas_manager->get_canvas_dimensions();
$margins = $canvas_manager->get_canvas_margins();
$grid = $canvas_manager->get_grid_settings();
$zoom = $canvas_manager->get_zoom_settings();
$selection = $canvas_manager->get_selection_settings();
$export = $canvas_manager->get_export_settings();
$history = $canvas_manager->get_history_settings();
```

**Sauvegarde des paramètres**:
```php
$canvas_manager->save_settings([
    'default_canvas_width' => 800,
    'show_grid' => true,
    'grid_size' => 20,
]);
```

**Réinitialisation**:
```php
$canvas_manager->reset_to_defaults();
```

### 2. Frontend React (Hooks)

**Fichier**: `assets/js/src/pdf-builder-react/hooks/useCanvasSettings.ts`

#### Hook principal: `useCanvasSettings()`

Retourne tous les paramètres du canvas:

```typescript
import { useCanvasSettings } from '@/hooks/useCanvasSettings';

function MyComponent() {
    const settings = useCanvasSettings();
    
    return (
        <div>
            <p>Largeur: {settings.default_canvas_width}px</p>
            <p>Hauteur: {settings.default_canvas_height}px</p>
        </div>
    );
}
```

#### Paramètre spécifique: `useCanvasSetting(key, defaultValue)`

```typescript
import { useCanvasSetting } from '@/hooks/useCanvasSettings';

function MyComponent() {
    const width = useCanvasSetting('default_canvas_width', 794);
    const gridSize = useCanvasSetting('grid_size', 10);
    
    return <p>Taille de grille: {gridSize}px</p>;
}
```

#### Hooks spécialisés (recommandés)

```typescript
import {
    useCanvasDimensions,
    useCanvasMargins,
    useGridSettings,
    useZoomSettings,
    useSelectionSettings,
    useExportSettings,
    useHistorySettings
} from '@/hooks/useCanvasSettings';

function CanvasComponent() {
    const dimensions = useCanvasDimensions();
    const margins = useCanvasMargins();
    const grid = useGridSettings();
    const zoom = useZoomSettings();
    
    return (
        <>
            <p>Canvas: {dimensions.width}x{dimensions.height}</p>
            <p>Marges: {margins.top}mm</p>
            <p>Grille: {grid.size}px</p>
            <p>Zoom: {zoom.default}%</p>
        </>
    );
}
```

### 3. Handlers AJAX (Canvas_AJAX_Handler)

**Fichier**: `plugin/src/Admin/Canvas_AJAX_Handler.php`

#### Récupérer les paramètres

**Action**: `pdf_builder_get_canvas_settings`

```javascript
jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
        action: 'pdf_builder_get_canvas_settings',
        nonce: pdf_builder_nonce
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data.settings);
        }
    }
});
```

#### Sauvegarder les paramètres

**Action**: `pdf_builder_save_canvas_settings`

```javascript
jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
        action: 'pdf_builder_save_canvas_settings',
        nonce: pdf_builder_nonce,
        settings: {
            default_canvas_width: 800,
            show_grid: true,
            grid_size: 20
        }
    },
    success: function(response) {
        if (response.success) {
            console.log('Sauvegardé avec succès');
        }
    }
});
```

#### Réinitialiser les paramètres

**Action**: `pdf_builder_reset_canvas_settings`

```javascript
jQuery.ajax({
    url: ajaxurl,
    type: 'POST',
    dataType: 'json',
    data: {
        action: 'pdf_builder_reset_canvas_settings',
        nonce: pdf_builder_nonce,
        confirm: 'yes'
    },
    success: function(response) {
        if (response.success) {
            console.log('Réinitialisé aux valeurs par défaut');
        }
    }
});
```

## Paramètres disponibles (50+)

### Dimensions (4)
- `default_canvas_width`: 794 (en px)
- `default_canvas_height`: 1123 (en px)
- `default_canvas_unit`: 'px'
- `default_orientation`: 'portrait'

### Couleurs & Fond (4)
- `canvas_background_color`: '#ffffff'
- `canvas_show_transparency`: false
- `container_background_color`: '#f8f9fa'
- `container_show_transparency`: false

### Marges (5)
- `margin_top`: 28
- `margin_right`: 28
- `margin_bottom`: 10
- `margin_left`: 10
- `show_margins`: false

### Grille & Aimants (7)
- `show_grid`: false
- `grid_size`: 10
- `grid_color`: '#e0e0e0'
- `snap_to_grid`: false
- `snap_to_elements`: false
- `snap_tolerance`: 5
- `show_guides`: false

### Zoom & Navigation (6)
- `default_zoom`: 100 (%)
- `zoom_step`: 25 (%)
- `min_zoom`: 10 (%)
- `max_zoom`: 500 (%)
- `zoom_with_wheel`: false
- `pan_with_mouse`: false

### Sélection & Manipulation (7)
- `show_resize_handles`: false
- `handle_size`: 8
- `handle_color`: '#007cba'
- `enable_rotation`: false
- `rotation_step`: 15
- `multi_select`: false
- `copy_paste_enabled`: false

### Export (6)
- `export_quality`: 'print'
- `export_format`: 'pdf'
- `compress_images`: true
- `image_quality`: 85
- `max_image_size`: 2048
- `include_metadata`: true

### PDF Metadata (2)
- `pdf_author`: 'PDF Builder Pro'
- `pdf_subject`: ''

### Optimisation (6)
- `auto_crop`: false
- `embed_fonts`: true
- `optimize_for_web`: true
- `enable_hardware_acceleration`: true
- `limit_fps`: true
- `max_fps`: 60

### Historique & Auto-save (5)
- `auto_save_enabled`: true
- `auto_save_interval`: 30 (secondes)
- `auto_save_versions`: 10
- `undo_levels`: 50
- `redo_levels`: 50

### Interface (2)
- `enable_keyboard_shortcuts`: true
- `debug_mode`: false
- `show_fps`: false

## Flux de données

```
WordPress Options DB
    ↓
Canvas_Manager::load_settings()
    ↓
window.pdfBuilderCanvasSettings (injected via JavaScript)
    ↓
React Hooks (useCanvasSettings, useCanvasDimensions, etc.)
    ↓
React Components
```

## Exemples complets

### Exemple 1: Afficher les dimensions du canvas

```typescript
import React from 'react';
import { useCanvasDimensions } from '@/hooks/useCanvasSettings';

export const CanvasDimensionsDisplay = () => {
    const { width, height, unit, orientation } = useCanvasDimensions();
    
    return (
        <div className="canvas-dimensions">
            <h3>Dimensions du Canvas</h3>
            <p>
                {width}{unit} × {height}{unit} 
                ({orientation})
            </p>
        </div>
    );
};
```

### Exemple 2: Configurer la grille dynamiquement

```typescript
import React, { useState } from 'react';
import { useGridSettings } from '@/hooks/useCanvasSettings';

export const GridConfigurator = () => {
    const { show, size, color, snapEnabled } = useGridSettings();
    
    const handleSaveSettings = () => {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_save_canvas_settings',
                nonce: wp.ajax.settings.nonce,
                settings: {
                    show_grid: true,
                    grid_size: 20,
                    snap_to_grid: true
                }
            }
        });
    };
    
    return (
        <div>
            <label>
                <input type="checkbox" defaultChecked={show} />
                Afficher grille
            </label>
            <input 
                type="number" 
                defaultValue={size} 
                placeholder="Taille grille" 
            />
            <button onClick={handleSaveSettings}>Sauvegarder</button>
        </div>
    );
};
```

### Exemple 3: Monitorer le zoom

```typescript
import React, { useEffect } from 'react';
import { useCanvasSetting } from '@/hooks/useCanvasSettings';

export const ZoomMonitor = () => {
    const currentZoom = useCanvasSetting('default_zoom', 100);
    const minZoom = useCanvasSetting('min_zoom', 10);
    const maxZoom = useCanvasSetting('max_zoom', 500);
    
    useEffect(() => {
        console.log(`Zoom: ${currentZoom}% (min: ${minZoom}%, max: ${maxZoom}%)`);
    }, [currentZoom]);
    
    return (
        <div>
            <p>Zoom actuel: {currentZoom}%</p>
            <p>Plage: {minZoom}% - {maxZoom}%</p>
        </div>
    );
};
```

## Bonnes pratiques

1. **Utiliser les hooks spécialisés**: Préférez `useCanvasDimensions()` plutôt que de récupérer chaque paramètre individuellement.

2. **Mémorisation**: Les hooks utilisent `useMemo` pour optimiser les performances.

3. **Gestion d'erreurs**: Toujours fournir des valeurs par défaut aux hooks.

4. **Nonces de sécurité**: Toujours vérifier les nonces pour les actions AJAX.

5. **Permissions**: Vérifier que l'utilisateur a la capacité `manage_options` avant de modifier les paramètres.

## Débogage

Pour afficher les paramètres du canvas dans la console:

```javascript
console.log(window.pdfBuilderCanvasSettings);
```

Pour réinitialiser les paramètres manuellement (développement uniquement):

```php
Canvas_Manager::get_instance()->reset_to_defaults();
```

## Performance

- Les paramètres sont cachés dans `$settings` et réutilisés
- Les hooks utilisent `useMemo` pour éviter les re-rendus inutiles
- Les appels AJAX sont déboucles et sérialisés

## Intégration future

Les paramètres du canvas peuvent être intégrés avec:
- Le générateur de PDF (PDFGenerator.php)
- L'éditeur React (pour l'aperçu en direct)
- Le système de cache (pour optimiser les performances)
- Le système de notifications (pour notifier des changements)
