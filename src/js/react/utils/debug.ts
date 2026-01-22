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
    pdfBuilderData?: {
      nonce: string;
      ajaxUrl: string;
      templateId?: string | number;
      existingTemplate?: any;
      hasExistingData?: boolean;
    };
    pdfBuilderCanvasSettings?: any; // Canvas settings from WordPress
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: unknown[]) {
  if (isDebugEnabled()) {
    
  }
}

// Fonction de debug pour les sauvegardes (activable séparément)
export function debugSave(...args: unknown[]) {
  if (isDebugEnabled()) {
    
  }
}

export function debugError(...args: unknown[]) {
  if (isDebugEnabled()) {
    
  }
}

export function debugWarn(...args: unknown[]) {
  if (isDebugEnabled()) {
    
  }
}

export function debugTable(data: unknown) {
  if (isDebugEnabled()) {
    console.table(data);
  }
}

// Keep an internal verbose flag in sync with window.pdfBuilderDebugSettings
if (typeof window !== 'undefined') {
  try {
    window.PDF_BUILDER_VERBOSE = !!(window.pdfBuilderDebugSettings && window.pdfBuilderDebugSettings.javascript);
  } catch (e) {
    // ignore
  }
  // Listener to update verbose flag at runtime
  if (window.addEventListener) {
    window.addEventListener('pdfBuilder:debugSettingsChanged', (e: any) => {
      try {
        const detail = e && e.detail ? e.detail : window.pdfBuilderDebugSettings;
        window.PDF_BUILDER_VERBOSE = !!(detail && detail.javascript);
        if (typeof window.console !== 'undefined' && window.PDF_BUILDER_VERBOSE) {
          
        }
      } catch (err) {
        if (typeof window.console !== 'undefined') {
          
        }
      }
    });
  }
}



