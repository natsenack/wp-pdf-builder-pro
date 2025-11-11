// Fonction pour vérifier si le debug est activé
// Note: Now only used for explicit opt-in via PDF_BUILDER_VERBOSE
// By default, only errors are shown

// Extension de Window pour le debug
declare global {
  interface Window {
    PDF_BUILDER_VERBOSE?: boolean; // Set to true/false to control debug logging
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: unknown[]) {
  // Disabled by default - only errors are shown
  // To enable debug logging: window.PDF_BUILDER_VERBOSE = true
  const isVerbose = typeof window !== 'undefined' && (window as unknown as Record<string, unknown>).PDF_BUILDER_VERBOSE === true;
  if (isVerbose) {
    console.log(...args);
  }
}

export function debugError(...args: unknown[]) {
  // Always show errors
  console.error(...args);
}

export function debugWarn(...args: unknown[]) {
  // Always show warnings
  console.warn(...args);
}