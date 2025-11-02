// Fonction pour vérifier si le debug est activé
function isDebugEnabled(): boolean {
  return process.env.NODE_ENV === 'development' ||
         window.location.hostname === 'localhost' ||
         window.location.search.includes('debug=pdf') ||
         (window as any).pdfBuilderDebug === true;
}

// Extension de Window pour le debug
declare global {
  interface Window {
    pdfBuilderDebug?: boolean;
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: any[]) {
  if (isDebugEnabled()) {
    console.log(...args);
  }
}

export function debugError(...args: any[]) {
  if (isDebugEnabled()) {
    console.error(...args);
  }
}

export function debugWarn(...args: any[]) {
  if (isDebugEnabled()) {
    console.warn(...args);
  }
}