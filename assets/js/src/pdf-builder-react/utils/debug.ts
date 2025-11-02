// Configuration des logs
const DEBUG_MODE = process.env.NODE_ENV === 'development' ||
                   window.location.hostname === 'localhost' ||
                   window.location.search.includes('debug=pdf') ||
                   (window as any).pdfBuilderDebug === true;

// Extension de Window pour le debug
declare global {
  interface Window {
    pdfBuilderDebug?: boolean;
  }
}

// Fonction de logging conditionnel
export function debugLog(...args: any[]) {
  if (DEBUG_MODE) {
    console.log(...args);
  }
}

export function debugError(...args: any[]) {
  if (DEBUG_MODE) {
    console.error(...args);
  }
}

export function debugWarn(...args: any[]) {
  if (DEBUG_MODE) {
    console.warn(...args);
  }
}