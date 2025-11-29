// Fonction pour vérifier si le debug est activé
function isDebugEnabled(): boolean {
  // Debug activé si explicitement forcé ou si activé dans les paramètres
  return window.location?.search?.includes('debug=force') ||
         (typeof window.pdfBuilderCanvasSettings !== 'undefined' &&
          window.pdfBuilderCanvasSettings?.debug?.javascript === true) ||
         false;
}

// Extension de Window pour le debug
declare global {
  interface Window {
    PDF_BUILDER_VERBOSE?: boolean; // Set to true/false to control debug logging
    PDF_BUILDER_DEBUG_SAVE?: boolean; // Set to true to debug save operations
    pdfBuilderCanvasSettings?: {
      debug?: {
        javascript?: boolean;
        javascript_verbose?: boolean;
      };
    };
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: unknown[]) {
  if (isDebugEnabled()) {
    console.log(...args);
  }
}

// Fonction de debug pour les sauvegardes (activable séparément)
export function debugSave(...args: unknown[]) {
  if (isDebugEnabled()) {
    console.log(...args);
  }
}

export function debugError(...args: unknown[]) {
  if (isDebugEnabled()) {
    console.error(...args);
  }
}

export function debugWarn(...args: unknown[]) {
  if (isDebugEnabled()) {
    console.warn(...args);
  }
}

export function debugTable(data: unknown) {
  if (isDebugEnabled()) {
    console.table(data);
  }
}
