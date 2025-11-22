// Fonction pour vérifier si le debug est activé
// Note: Now only used for explicit opt-in via PDF_BUILDER_VERBOSE
// By default, only errors are shown

// Extension de Window pour le debug
declare global {
  interface Window {
    PDF_BUILDER_VERBOSE?: boolean; // Set to true/false to control debug logging
    PDF_BUILDER_DEBUG_SAVE?: boolean; // Set to true to debug save operations
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: unknown[]) {
  // Disabled by default - only errors are shown
  // To enable debug logging: window.PDF_BUILDER_VERBOSE = true
  const isVerbose = typeof window !== 'undefined' && (window as unknown as Record<string, unknown>).PDF_BUILDER_VERBOSE === true;
  if (isVerbose) {

  }
}

// Fonction de debug pour les sauvegardes (activable séparément)
export function debugSave(...args: unknown[]) {
  // TEMPORAIRE : Toujours afficher les logs de sauvegarde pour le debug
  // To enable save debugging: window.PDF_BUILDER_DEBUG_SAVE = true

}

export function debugError(...args: unknown[]) {
  // Always show errors

}

export function debugWarn(...args: unknown[]) {
  // Always show warnings

}
