/**
 * Configuration Jest pour les tests PDF Builder Pro
 */

// Mock des objets globaux utilisés dans les tests
global.PerformanceMetrics = {
  start: jest.fn(),
  end: jest.fn(),
  error: jest.fn(),
  getMetrics: jest.fn(() => ({}))
};

global.LocalCache = {
  clear: jest.fn(),
  save: jest.fn(),
  load: jest.fn(),
  simpleHash: jest.fn(() => 'mock-hash'),
  getSessionId: jest.fn(() => 'mock-session-id')
};

global.validateFormData = jest.fn(() => []);

global.AjaxCompat = {
  fetch: jest.fn(() => Promise.resolve({ ok: true, json: () => Promise.resolve({}) }))
};

// Mock de sessionStorage et localStorage pour les tests
const createMockStorage = () => {
  let storage = {};
  return {
    getItem: jest.fn(key => storage[key] || null),
    setItem: jest.fn((key, value) => { storage[key] = value; }),
    removeItem: jest.fn(key => { delete storage[key]; }),
    clear: jest.fn(() => { storage = {}; })
  };
};

global.sessionStorage = createMockStorage();
global.localStorage = createMockStorage();

// Mock de console pour réduire le bruit pendant les tests
global.console = {
  ...console,
  log: jest.fn(),
  error: jest.fn(),
  warn: jest.fn()
};