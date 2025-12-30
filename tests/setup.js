/**
 * Configuration Jest pour les tests PDF Builder Pro
 */

// Charger les vraies implémentations des utilitaires
require('../plugin/assets/js/pdf-builder-utils.js');

// Mock des objets globaux WordPress nécessaires
global.wp = {
  ajax: {
    url: '/wp-admin/admin-ajax.php'
  }
};

// Configuration globale pour les tests
global.PDF_BUILDER_CONFIG = {
  debug: false,
  ajaxurl: '/wp-admin/admin-ajax.php',
  nonce: 'test_nonce_123'
};

// Mock de sessionStorage et localStorage pour les tests
const createStorageMock = () => {
  let storage = {};
  return {
    getItem: jest.fn(key => storage[key] || null),
    setItem: jest.fn((key, value) => { storage[key] = value; }),
    removeItem: jest.fn(key => { delete storage[key]; }),
    clear: jest.fn(() => { storage = {}; })
  };
};

global.sessionStorage = createStorageMock();
global.localStorage = createStorageMock();

// Mock de fetch pour les tests AJAX
global.fetch = jest.fn(() =>
  Promise.resolve({
    ok: true,
    json: () => Promise.resolve({ success: true, data: 'test' })
  })
);

// Mock de console pour réduire le bruit pendant les tests
global.console = {
  ...console,
  log: jest.fn(),
  error: jest.fn(),
  warn: jest.fn()
};