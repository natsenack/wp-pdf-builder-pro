// Diagnostic de compatibilité navigateur pour PDF Builder


// Vérifier les APIs critiques
const compatibilityChecks = {
  // APIs de base
  fetch: typeof fetch !== 'undefined',
  promise: typeof Promise !== 'undefined',
  urlSearchParams: typeof URLSearchParams !== 'undefined',

  // APIs Canvas
  canvas: typeof HTMLCanvasElement !== 'undefined',
  canvasContext2d: typeof CanvasRenderingContext2D !== 'undefined',

  // APIs DOM
  querySelector: typeof document.querySelector !== 'undefined',
  addEventListener: typeof document.addEventListener !== 'undefined',
  getBoundingClientRect: typeof Element.prototype.getBoundingClientRect !== 'undefined',

  // APIs Drag & Drop
  dataTransfer: typeof DataTransfer !== 'undefined',
  dragEvent: typeof DragEvent !== 'undefined',

  // APIs React
  react: typeof React !== 'undefined',
  reactDom: typeof ReactDOM !== 'undefined',
  createRoot: typeof ReactDOM !== 'undefined' && typeof ReactDOM.createRoot !== 'undefined',

  // APIs Modernes
  intersectionObserver: typeof IntersectionObserver !== 'undefined',
  mutationObserver: typeof MutationObserver !== 'undefined',
  resizeObserver: typeof ResizeObserver !== 'undefined',

  // APIs de fichiers
  fileReader: typeof FileReader !== 'undefined',
  blob: typeof Blob !== 'undefined',

  // APIs de stockage - INTERDITS (utiliser uniquement les options WordPress)
  // localStorage: typeof localStorage !== 'undefined', // INTERDIT
  // sessionStorage: typeof sessionStorage !== 'undefined', // INTERDIT

  // User Agent
  userAgent: navigator.userAgent,
  chrome: /Chrome/.test(navigator.userAgent),
  firefox: /Firefox/.test(navigator.userAgent),
  safari: /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent),
  opera: /Opera|OPR/.test(navigator.userAgent),
  edge: /Edg/.test(navigator.userAgent),
  ie: /MSIE|Trident/.test(navigator.userAgent)
};

console.table(compatibilityChecks);

// Vérifier les APIs problématiques
const problematicAPIs = Object.entries(compatibilityChecks)
  .filter(([key, value]) => typeof value === 'boolean' && !value)
  .map(([key]) => key);

if (problematicAPIs.length > 0) {
  console.error('❌ APIs manquantes:', problematicAPIs);
} else {

}

// Test des Event Listeners passifs
let passiveSupported = false;
try {
  const testElement = document.createElement('div');
  const options = Object.defineProperty({}, 'passive', {
    get: function() { passiveSupported = true; return true; }
  });
  testElement.addEventListener('test', () => {}, options);
} catch (e) {
  console.error('❌ Event Listeners passifs NON supportés:', e);
}

// Fonction utilitaire pour créer des event listeners optimisés
export const createOptimizedEventListener = (element, event, handler, options = {}) => {
  const defaultOptions = {
    passive: passiveSupported && !['touchstart', 'touchmove', 'wheel'].includes(event),
    capture: false,
    ...options
  };

  element.addEventListener(event, handler, defaultOptions);

  // Retourner une fonction de nettoyage
  return () => {
    element.removeEventListener(event, handler, defaultOptions);
  };
};

// Test de fetch API
if (typeof fetch !== 'undefined') {
  fetch(window.location.href, { method: 'HEAD' })
    .then(() => console.log('✅ Fetch API fonctionne'))
    .catch(e => console.error('❌ Fetch API ne fonctionne pas:', e));
}

// Test de Canvas
try {
  const testCanvas = document.createElement('canvas');
  const ctx = testCanvas.getContext('2d');
  if (ctx) {

  } else {
    console.error('❌ Canvas 2D API ne fonctionne pas');
  }
} catch (e) {
  console.error('❌ Erreur Canvas:', e);
}


