/**
 * Constantes pour les dimensions du PDF Builder
 *
 * IMPORTANT: Le système utilise UNIQUEMENT les PIXELS (PX)
 * A4 Standard en pixels (pour correspondre au plugin WooCommerce PDF Invoice Builder):
 *   - Portrait: 794px × 1123px
 *   - Landscape: 1123px × 794px
 */

// Fonction pour récupérer les dimensions depuis les paramètres WordPress
export const getCanvasDimensions = async () => {
    // D'abord essayer de récupérer depuis window.pdfBuilderCanvasSettings
    if (window.pdfBuilderCanvasSettings?.default_canvas_width && window.pdfBuilderCanvasSettings?.default_canvas_height) {
        return {
            width: Math.max(100, Math.min(3000, window.pdfBuilderCanvasSettings.default_canvas_width)),
            height: Math.max(100, Math.min(3000, window.pdfBuilderCanvasSettings.default_canvas_height))
        };
    }

    // Sinon, récupérer depuis la base de données via AJAX
    try {
        const response = await fetch(window.pdfBuilderAjax?.ajax_url || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'pdf_builder_get_user_setting',
                setting_key: 'canvas_dimensions',
                nonce: window.pdfBuilderAjax?.nonce || ''
            })
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success && data.data) {
                const dimensions = JSON.parse(data.data);
                return {
                    width: Math.max(100, Math.min(3000, dimensions.width || 794)),
                    height: Math.max(100, Math.min(3000, dimensions.height || 1123))
                };
            }
        }
    } catch (error) {
        console.warn('[getCanvasDimensions] Erreur lors de la récupération AJAX:', error);
    }

    // Fallback vers les valeurs par défaut
    return {
        width: 794,
        height: 1123
    };
};

// Version synchrone pour la compatibilité (utilise les valeurs par défaut)
export const getCanvasDimensionsSync = () => {
    // Essayer de récupérer depuis window.pdfBuilderCanvasSettings d'abord
    if (window.pdfBuilderCanvasSettings?.default_canvas_width && window.pdfBuilderCanvasSettings?.default_canvas_height) {
        return {
            width: Math.max(100, Math.min(3000, window.pdfBuilderCanvasSettings.default_canvas_width)),
            height: Math.max(100, Math.min(3000, window.pdfBuilderCanvasSettings.default_canvas_height))
        };
    }

    // Fallback vers les valeurs par défaut
    return {
        width: 794,
        height: 1123
    };
};

// Dimensions A4 en PIXELS uniquement (comme l'autre plugin WooCommerce PDF Invoice Builder)
// A4 Portrait: 794×1123px (optimisé pour la visibilité écran)
// A4 Portrait: 794×1123px (plus large pour meilleure visibilité)
export const CANVAS_DIMENSIONS = {
  A4_PORTRAIT: {
    width: 794,   // A4 width in pixels (plus large que 594)
    height: 1123, // A4 height in pixels
    name: 'A4 Portrait'
  },
  A4_LANDSCAPE: {
    width: 1123,  // A4 landscape width in pixels
    height: 794,  // A4 landscape height in pixels
    name: 'A4 Landscape'
  }
} as const;

// Dimensions par défaut pour le rendu canvas (A4 Portrait en pixels)
export const DEFAULT_CANVAS_WIDTH = 794;   // 794px
export const DEFAULT_CANVAS_HEIGHT = 1123; // 1123px
