/**
 * Utilitaires de sécurité pour le système d'aperçu
 * Sanitisation, validation et protection XSS
 */

/**
 * Sanitise une chaîne de caractères pour éviter les attaques XSS
 * @param {string} str - Chaîne à sanitiser
 * @returns {string} Chaîne sanitizée
 */
export function sanitizeString(str) {
  if (typeof str !== 'string') return '';

  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#x27;')
    .replace(/\//g, '&#x2F;');
}

/**
 * Valide et sanitise les données d'éléments d'aperçu
 * @param {Array} elements - Tableau d'éléments à valider
 * @returns {Array} Éléments validés et sanitizés
 */
export function validatePreviewElements(elements) {
  if (!Array.isArray(elements)) return [];

  return elements
    .filter(element => {
      // Validation de base des propriétés requises
      return element &&
             typeof element === 'object' &&
             element.type &&
             typeof element.type === 'string';
    })
    .map(element => ({
      ...element,
      // Sanitisation des propriétés textuelles
      content: element.content ? sanitizeString(element.content) : '',
      text: element.text ? sanitizeString(element.text) : '',
      // Validation des propriétés numériques
      x: typeof element.x === 'number' ? element.x : 0,
      y: typeof element.y === 'number' ? element.y : 0,
      width: typeof element.width === 'number' ? element.width : null,
      height: typeof element.height === 'number' ? element.height : null,
      // Validation des propriétés spécifiques au type
      ...(element.type === 'text' && {
        fontSize: typeof element.fontSize === 'number' ? element.fontSize : 12,
        fontFamily: typeof element.fontFamily === 'string' ? element.fontFamily : 'Arial'
      }),
      ...(element.type === 'image' && {
        src: typeof element.src === 'string' ? element.src : '',
        alt: element.alt ? sanitizeString(element.alt) : ''
      })
    }));
}

/**
 * Valide les données de configuration d'aperçu
 * @param {Object} config - Configuration à valider
 * @returns {Object} Configuration validée
 */
export function validatePreviewConfig(config) {
  if (!config || typeof config !== 'object') {
    return {
      mode: 'canvas',
      quality: 'medium',
      format: 'pdf'
    };
  }

  return {
    mode: ['canvas', 'metabox'].includes(config.mode) ? config.mode : 'canvas',
    quality: ['low', 'medium', 'high'].includes(config.quality) ? config.quality : 'medium',
    format: ['pdf', 'png', 'jpg'].includes(config.format) ? config.format : 'pdf',
    maxPages: typeof config.maxPages === 'number' && config.maxPages > 0 ? config.maxPages : 50,
    timeout: typeof config.timeout === 'number' && config.timeout > 0 ? config.timeout : 30000
  };
}

/**
 * Génère un nonce sécurisé pour les requêtes AJAX
 * @returns {string} Nonce généré
 */
export function generateSecureNonce() {
  const array = new Uint8Array(16);
  crypto.getRandomValues(array);
  return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
}

/**
 * Valide un nonce reçu
 * @param {string} nonce - Nonce à valider
 * @param {string} expectedNonce - Nonce attendu
 * @returns {boolean} True si valide
 */
export function validateNonce(nonce, expectedNonce) {
  return typeof nonce === 'string' &&
         typeof expectedNonce === 'string' &&
         nonce.length === expectedNonce.length &&
         nonce === expectedNonce;
}

/**
 * Nettoie les données sensibles avant l'envoi
 * @param {Object} data - Données à nettoyer
 * @returns {Object} Données nettoyées
 */
export function sanitizeSensitiveData(data) {
  if (!data || typeof data !== 'object') return data;

  const sensitiveKeys = ['password', 'token', 'secret', 'key', 'nonce'];
  const cleaned = { ...data };

  Object.keys(cleaned).forEach(key => {
    if (sensitiveKeys.some(sensitive => key.toLowerCase().includes(sensitive))) {
      cleaned[key] = '[REDACTED]';
    } else if (typeof cleaned[key] === 'object') {
      cleaned[key] = sanitizeSensitiveData(cleaned[key]);
    }
  });

  return cleaned;
}