// Diagnostic de compatibilité navigateur pour PDF Builder

// Debug function
const debugEnabled = typeof window !== 'undefined' && window.pdfBuilderDebugSettings?.javascript;
const debugLog = (...args) => {}; // Debug logging disabled for production
const debugError = (...args) => {}; // Debug error logging disabled for production
const debugWarn = (...args) => {}; // Debug warn logging disabled for production
const debugTable = (...args) => {}; // Debug table logging disabled for production


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

  // APIs de stockage
  localStorage: typeof localStorage !== 'undefined',
  sessionStorage: typeof sessionStorage !== 'undefined',

  // User Agent
  userAgent: navigator.userAgent,
  chrome: /Chrome/.test(navigator.userAgent),
  firefox: /Firefox/.test(navigator.userAgent),
  safari: /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent),
  opera: /Opera|OPR/.test(navigator.userAgent),
  edge: /Edg/.test(navigator.userAgent),
  ie: /MSIE|Trident/.test(navigator.userAgent)
};

debugTable(compatibilityChecks);

// Vérifier les APIs problématiques
const problematicAPIs = Object.entries(compatibilityChecks)
  .filter(([key, value]) => typeof value === 'boolean' && !value)
  .map(([key]) => key);

if (problematicAPIs.length > 0) {
  debugError('❌ APIs manquantes:', problematicAPIs);
} else {

}

// Test des Event Listeners passifs
try {
  const testElement = document.createElement('div');
  testElement.addEventListener('test', () => {}, { passive: true, capture: false });

} catch (e) {
  debugError('❌ Event Listeners passifs NON supportés:', e);
}

// Test de fetch API
if (typeof fetch !== 'undefined') {
  fetch(window.location.href, { method: 'HEAD' })
    .then(() => {})
    .catch(e => debugError('❌ Fetch API ne fonctionne pas:', e));
}

// Test de Canvas
try {
  const testCanvas = document.createElement('canvas');
  const ctx = testCanvas.getContext('2d');
  if (ctx) {

  } else {
    debugError('❌ Canvas 2D API ne fonctionne pas');
  }
} catch (e) {
  debugError('❌ Erreur Canvas:', e);
}


