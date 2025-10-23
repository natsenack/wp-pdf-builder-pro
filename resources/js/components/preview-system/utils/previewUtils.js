/**
 * Utilitaires communs pour le système d'aperçu PDF
 * Fonctions helper pour le rendu, calculs et formatage
 */

/**
 * Calcule les dimensions d'une page PDF en pixels
 * @param {string} format - Format de page (A4, A3, Letter, etc.)
 * @param {number} dpi - Résolution en DPI (par défaut 96)
 * @returns {Object} Dimensions {width, height} en pixels
 */
export function getPageDimensions(format = 'A4', dpi = 96) {
  const dimensions = {
    A4: { width: 595, height: 842 },      // Points PDF à 72 DPI
    A3: { width: 842, height: 1191 },
    Letter: { width: 612, height: 792 },
    Legal: { width: 612, height: 1008 }
  };

  const baseDims = dimensions[format] || dimensions.A4;

  // Conversion en pixels selon le DPI
  const scale = dpi / 72; // PDF base is 72 DPI

  return {
    width: Math.round(baseDims.width * scale),
    height: Math.round(baseDims.height * scale)
  };
}

/**
 * Calcule le niveau de zoom optimal pour contenir la page
 * @param {Object} pageDims - Dimensions de la page {width, height}
 * @param {Object} containerDims - Dimensions du conteneur {width, height}
 * @param {number} padding - Marge en pixels
 * @returns {number} Niveau de zoom (0.1 à 2.0)
 */
export function calculateOptimalZoom(pageDims, containerDims, padding = 20) {
  const availableWidth = containerDims.width - (padding * 2);
  const availableHeight = containerDims.height - (padding * 2);

  const scaleX = availableWidth / pageDims.width;
  const scaleY = availableHeight / pageDims.height;

  const optimalScale = Math.min(scaleX, scaleY, 1); // Max 100% pour éviter l'agrandissement excessif

  return Math.max(0.1, Math.min(2.0, optimalScale));
}

/**
 * Formate un numéro de page
 * @param {number} current - Page actuelle
 * @param {number} total - Nombre total de pages
 * @returns {string} Format "X / Y"
 */
export function formatPageNumber(current, total) {
  return `${current} / ${total}`;
}

/**
 * Génère une clé unique pour le cache
 * @param {string} prefix - Préfixe pour la clé
 * @param {Object} params - Paramètres à inclure
 * @returns {string} Clé de cache unique
 */
export function generateCacheKey(prefix, params = {}) {
  const sortedParams = Object.keys(params)
    .sort()
    .map(key => `${key}:${params[key]}`)
    .join('|');

  return `${prefix}_${btoa(sortedParams).replace(/[^a-zA-Z0-9]/g, '')}`;
}

/**
 * Débounce une fonction
 * @param {Function} func - Fonction à debouncer
 * @param {number} wait - Délai en ms
 * @returns {Function} Fonction debouncée
 */
export function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

/**
 * Throttle une fonction
 * @param {Function} func - Fonction à throttler
 * @param {number} limit - Limite en ms
 * @returns {Function} Fonction throttlée
 */
export function throttle(func, limit) {
  let inThrottle;
  return function executedFunction(...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}

/**
 * Mesure les performances d'une fonction
 * @param {Function} fn - Fonction à mesurer
 * @param {string} label - Label pour les logs
 * @returns {*} Résultat de la fonction
 */
export function measurePerformance(fn, label = 'Operation') {
  const start = performance.now();
  try {
    const result = fn();
    const duration = performance.now() - start;
    console.log(`[Performance] ${label}: ${duration.toFixed(2)}ms`);
    return result;
  } catch (error) {
    const duration = performance.now() - start;
    console.error(`[Performance] ${label} failed after ${duration.toFixed(2)}ms:`, error);
    throw error;
  }
}

/**
 * Vérifie si le navigateur supporte les fonctionnalités requises
 * @returns {Object} Support des fonctionnalités
 */
export function checkBrowserSupport() {
  return {
    intersectionObserver: 'IntersectionObserver' in window,
    resizeObserver: 'ResizeObserver' in window,
    webWorkers: 'Worker' in window,
    canvas: 'HTMLCanvasElement' in window,
    webgl: (() => {
      try {
        const canvas = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && canvas.getContext('webgl'));
      } catch (e) {
        return false;
      }
    })(),
    fetch: 'fetch' in window,
    promises: 'Promise' in window,
    asyncAwait: (async () => {})() instanceof Promise
  };
}

/**
 * Obtient les informations de débogage système
 * @returns {Object} Informations système
 */
export function getSystemInfo() {
  return {
    userAgent: navigator.userAgent,
    language: navigator.language,
    platform: navigator.platform,
    cookieEnabled: navigator.cookieEnabled,
    onLine: navigator.onLine,
    screen: {
      width: screen.width,
      height: screen.height,
      colorDepth: screen.colorDepth
    },
    viewport: {
      width: window.innerWidth,
      height: window.innerHeight
    },
    memory: performance.memory ? {
      used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
      total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
      limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
    } : null,
    timing: {
      loadTime: performance.timing.loadEventEnd - performance.timing.navigationStart,
      domReady: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart
    }
  };
}