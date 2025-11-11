// Fonction pour vérifier si le debug est activé
function isDebugEnabled(): boolean {
  // ✅ CORRECTION: Check for PDF_BUILDER_VERBOSE flag first
  // This allows users to enable verbose logging with: window.PDF_BUILDER_VERBOSE = true
  if ((window as any).PDF_BUILDER_VERBOSE === true) {
    return true;
  }
  
  // If verbose mode is explicitly disabled, don't log
  if ((window as any).PDF_BUILDER_VERBOSE === false) {
    return false;
  }
  
  // Otherwise use development environment checks
  return process.env.NODE_ENV === 'development' ||
         window.location.hostname === 'localhost' ||
         window.location.search.includes('debug=pdf') ||
         (window as any).pdfBuilderDebug === true;
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