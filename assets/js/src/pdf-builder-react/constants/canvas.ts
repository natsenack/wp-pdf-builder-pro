/**
 * Constantes pour les dimensions du PDF Builder
 * 
 * IMPORTANT: Le système utilise UNIQUEMENT les PIXELS (PX)
 * A4 Standard en pixels (pour correspondre au plugin WooCommerce PDF Invoice Builder):
 *   - Portrait: 794px × 1123px
 *   - Landscape: 1123px × 794px
 */

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
export const DEFAULT_CANVAS_WIDTH = CANVAS_DIMENSIONS.A4_PORTRAIT.width;   // 794px
export const DEFAULT_CANVAS_HEIGHT = CANVAS_DIMENSIONS.A4_PORTRAIT.height; // 1123px
