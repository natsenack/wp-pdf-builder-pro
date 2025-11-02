// Configuration des logs
const DEBUG_MODE = process.env.NODE_ENV === 'development' || window.location.hostname === 'localhost';

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