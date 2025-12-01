/**
 * Constantes pour les dimensions du PDF Builder
 *
 * IMPORTANT: Le système utilise UNIQUEMENT les PIXELS (PX)
 * Les dimensions sont maintenant dynamiques selon les paramètres sauvegardés
 */

// Fonction pour récupérer les dimensions depuis les paramètres WordPress
export const getCanvasDimensions = () => {
  // Récupérer les dimensions depuis les paramètres sauvegardés
  const width = window.pdfBuilderCanvasSettings?.default_canvas_width ||
                parseInt(localStorage.getItem('pdf_builder_canvas_width') || '794');
  const height = window.pdfBuilderCanvasSettings?.default_canvas_height ||
                 parseInt(localStorage.getItem('pdf_builder_canvas_height') || '1123');

  return {
    width: Math.max(100, Math.min(3000, width)), // Limiter entre 100px et 3000px
    height: Math.max(100, Math.min(3000, height))
  };
};

// Dimensions par défaut pour le rendu canvas (récupérées dynamiquement)
export const DEFAULT_CANVAS_WIDTH = getCanvasDimensions().width;
export const DEFAULT_CANVAS_HEIGHT = getCanvasDimensions().height;

// Dimensions A4 en PIXELS (pour référence uniquement)
export const CANVAS_DIMENSIONS = {
  A4_PORTRAIT: {
    width: 794,
    height: 1123,
    name: 'A4 Portrait'
  },
  A4_LANDSCAPE: {
    width: 1123,
    height: 794,
    name: 'A4 Landscape'
  }
} as const;

