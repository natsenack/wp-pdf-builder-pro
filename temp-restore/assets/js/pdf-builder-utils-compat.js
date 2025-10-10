/**
 * PDF Builder Pro - Utilitaires (couche de compatibilité)
 * Ce fichier sera supprimé une fois la migration complète vers TypeScript terminée
 * Il importe les fonctions du nouveau module common.ts pour maintenir la compatibilité
 */

import {
  generateId,
  mmToPx,
  pxToMm,
  isA4Format,
  calculateAspectRatio,
  formatPrice,
  formatDate,
  sanitizeText,
  throttle,
  debounce,
  isValidHexColor,
  getRelativePosition,
  clamp,
  deepClone,
  log
} from '../src/utils/common';

// Exposition globale pour compatibilité avec l'ancien code JavaScript
const PDF_BUILDER_UTILS = {
  generateId: (prefix = 'element') => generateId(prefix),
  mmToPx,
  pxToMm,
  isA4Format,
  calculateAspectRatio,
  formatPrice,
  formatDate,
  sanitizeText,
  throttle,
  debounce,
  isValidHex: isValidHexColor, // Alias pour compatibilité
  getRelativePosition,
  clamp,
  deepClone,
  log
};

// Exposition globale
if (typeof window !== 'undefined') {
  window.PDF_BUILDER_UTILS = PDF_BUILDER_UTILS;
}

console.log('✅ PDF Builder Utils (compatibility layer) loaded');

export default PDF_BUILDER_UTILS;