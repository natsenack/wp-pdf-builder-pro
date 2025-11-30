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

  // Vérifier les paramètres de debug centralisés
  if (typeof window.pdfBuilderDebugSettings !== 'undefined' &&
      window.pdfBuilderDebugSettings &&
      typeof window.pdfBuilderDebugSettings === 'object') {
    return !!window.pdfBuilderDebugSettings.javascript;
  }

  // Fallback vers pdfBuilderCanvasSettings pour la compatibilité
  if (typeof window.pdfBuilderCanvasSettings !== 'undefined' &&
      window.pdfBuilderCanvasSettings &&
      typeof window.pdfBuilderCanvasSettings === 'object') {
    const debugSettings = (window.pdfBuilderCanvasSettings as any).debug;
    if (debugSettings && typeof debugSettings === 'object') {
      return !!debugSettings.javascript;
    }
  }

  return false;
}

// Extension de Window pour le debug
declare global {
  interface Window {
    PDF_BUILDER_VERBOSE?: boolean; // Set to true/false to control debug logging
    PDF_BUILDER_DEBUG_SAVE?: boolean; // Set to true to debug save operations
    pdfBuilderDebugSettings?: {
      javascript?: boolean;
      ajax?: boolean;
      performance?: boolean;
      settings_page?: boolean;
      pdf_editor?: boolean;
      javascript_verbose?: boolean;
    };
    pdfBuilderCanvasSettings?: any;
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
