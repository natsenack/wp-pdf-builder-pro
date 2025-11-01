/**
 * Constantes pour les dimensions du PDF Builder
 * 
 * IMPORTANT: Le système utilise les MILLIMÈTRES en interne (MM)
 * A4 Standard:
 *   - Portrait: 210mm × 297mm
 *   - Landscape: 297mm × 210mm
 * 
 * Pour le rendu canvas (pixels), on utilise un ratio:
 *   MM_TO_PX = 595 / 210 ≈ 2.833
 */

// Dimensions réelles A4 en MILLIMÈTRES (source de vérité)
export const A4_DIMENSIONS_MM = {
  PORTRAIT: {
    width: 210,   // 210mm
    height: 297,  // 297mm
    name: 'A4 Portrait'
  },
  LANDSCAPE: {
    width: 297,   // 297mm
    height: 210,  // 210mm
    name: 'A4 Landscape'
  }
} as const;

// Dimensions pour le rendu canvas en PIXELS (calculées depuis MM)
// Conversion: 210mm → 595px, 297mm → 1123px (DPI 150)
export const CANVAS_DIMENSIONS = {
  A4_PORTRAIT: {
    width: 595,   // 210mm converti en pixels (210 * 2.833)
    height: 1123, // 297mm converti en pixels (297 * 2.833)
    name: 'A4 Portrait'
  },
  A4_LANDSCAPE: {
    width: 1123,  // 297mm converti en pixels
    height: 595,  // 210mm converti en pixels
    name: 'A4 Landscape'
  }
} as const;

// Dimensions par défaut pour le rendu canvas (A4 Portrait)
export const DEFAULT_CANVAS_WIDTH = CANVAS_DIMENSIONS.A4_PORTRAIT.width;   // 595px
export const DEFAULT_CANVAS_HEIGHT = CANVAS_DIMENSIONS.A4_PORTRAIT.height; // 1123px

// Dimensions de travail en MM (utilisation interne)
export const DEFAULT_TEMPLATE_WIDTH_MM = A4_DIMENSIONS_MM.PORTRAIT.width;   // 210mm
export const DEFAULT_TEMPLATE_HEIGHT_MM = A4_DIMENSIONS_MM.PORTRAIT.height; // 297mm