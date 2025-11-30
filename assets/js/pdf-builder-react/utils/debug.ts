// Fonction pour vérifier si on est sur la page de l'éditeur PDF
function isPDFEditorPage(): boolean {
  // Vérifier si l'élément avec la classe 'pdf-builder' existe (composant PDFBuilderContent)
  return typeof document !== 'undefined' &&
         document.querySelector('.pdf-builder') !== null;
}

// Fonction pour vérifier si on est sur la page des paramètres
function isSettingsPage(): boolean {
  // Vérifier si on est sur la page des paramètres (admin.php?page=pdf-builder-settings)
  return typeof window !== 'undefined' &&
         typeof window.location !== 'undefined' &&
         window.location.href.indexOf('pdf-builder-settings') !== -1;
}

// Fonction pour vérifier si le debug est activé
function isDebugEnabled(): boolean {
  // Debug activé si explicitement forcé
  if (window.location?.search?.includes('debug=force')) {
    return true;
  }

  // Vérifier les paramètres de debug
  if (typeof window.pdfBuilderCanvasSettings === 'undefined' ||
      !window.pdfBuilderCanvasSettings ||
      typeof window.pdfBuilderCanvasSettings !== 'object') {
    return false;
  }

  const debugSettings = (window.pdfBuilderCanvasSettings as any).debug;
  if (!debugSettings || typeof debugSettings !== 'object') {
    return false;
  }

  // Si le debug JavaScript général est activé
  if (debugSettings.javascript === true) {
    // Si le debug PDF editor est activé, vérifier qu'on est sur la page appropriée
    if (debugSettings.pdf_editor === true) {
      return isPDFEditorPage();
    }
    // Si le debug settings page est activé, vérifier qu'on est sur la page appropriée
    if (debugSettings.settings_page === true) {
      return isSettingsPage();
    }
    // Sinon, debug général activé
    return true;
  }

  return false;
}

// Extension de Window pour le debug
declare global {
  interface Window {
    PDF_BUILDER_VERBOSE?: boolean; // Set to true/false to control debug logging
    PDF_BUILDER_DEBUG_SAVE?: boolean; // Set to true to debug save operations
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
