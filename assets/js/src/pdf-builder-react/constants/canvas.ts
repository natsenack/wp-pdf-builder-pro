// Constantes pour les dimensions et configurations du PDF Builder
export const CANVAS_DIMENSIONS = {
  A4_PORTRAIT: {
    width: 794, // 210mm at 96 DPI
    height: 1123, // 297mm at 96 DPI
    name: 'A4 Portrait'
  },
  A4_LANDSCAPE: {
    width: 1123, // 297mm at 96 DPI
    height: 794, // 210mm at 96 DPI
    name: 'A4 Landscape'
  }
} as const;

// Dimensions par défaut (A4 Portrait)
export const DEFAULT_CANVAS_WIDTH = CANVAS_DIMENSIONS.A4_PORTRAIT.width;
export const DEFAULT_CANVAS_HEIGHT = CANVAS_DIMENSIONS.A4_PORTRAIT.height;