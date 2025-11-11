// Fonction pour vérifier si le debug est activé
function isDebugEnabled(): boolean {
  // ✅ CORRECTION: Check for PDF_BUILDER_VERBOSE flag ONLY
  // This requires explicit opt-in: window.PDF_BUILDER_VERBOSE = true
  // By default, debug logging is disabled in production
  const debugFlag = (window as unknown as Record<string, unknown>).PDF_BUILDER_VERBOSE;
  return debugFlag === true;
}

// Extension de Window pour le debug
declare global {
  interface Window {
    pdfBuilderDebug?: boolean;
    PDF_BUILDER_VERBOSE?: boolean; // Set to true/false to control debug logging
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: unknown[]) {
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