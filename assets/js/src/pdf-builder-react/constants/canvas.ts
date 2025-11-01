// Constantes pour les dimensions et configurations du PDF Builder
// Note: Dimensions en pixels pour le rendu canvas (conversion depuis MM)
// MM_TO_PX = 2.833 (595px / 210mm)

export const CANVAS_DIMENSIONS = {
  A4_PORTRAIT: {
    width: 594,  // 210mm at 96 DPI (210 * 2.833 = 594px)
    height: 841, // 297mm at 96 DPI (297 * 2.833 = 841px)
    name: 'A4 Portrait'
  },
  A4_LANDSCAPE: {
    width: 841,  // 297mm at 96 DPI
    height: 594, // 210mm at 96 DPI
    name: 'A4 Landscape'
  }
} as const;

// Dimensions par défaut (A4 Portrait) - en pixels pour le rendu
export const DEFAULT_CANVAS_WIDTH = CANVAS_DIMENSIONS.A4_PORTRAIT.width;
export const DEFAULT_CANVAS_HEIGHT = CANVAS_DIMENSIONS.A4_PORTRAIT.height;