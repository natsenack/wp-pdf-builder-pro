/**
 * Constantes pour les dimensions du PDF Builder
 * 
 * IMPORTANT: Le système utilise les PIXELS en interne (PX)
 * A4 Standard:
 *   - Portrait: 594px × 1123px (A4 210mm × 297mm)
 *   - Landscape: 1123px × 594px (A4 297mm × 210mm)
 */

// Dimensions A4 en PIXELS (comme l'autre plugin WooCommerce PDF Invoice Builder)
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