/**
 * Configuration Jest pour les tests PDF Builder Pro
 */

// Mock des objets globaux utilisés dans les tests
global.PerformanceMetrics = {
  metrics: {},
  start: jest.fn(function(operation) {
    if (!this.metrics[operation]) {
      this.metrics[operation] = { count: 0, totalTime: 0, avgTime: 0, errorCount: 0 };
    }
    this.metrics[operation].startTime = Date.now();
  }),
  end: jest.fn(function(operation) {
    if (this.metrics[operation] && this.metrics[operation].startTime) {
      const duration = Date.now() - this.metrics[operation].startTime;
      this.metrics[operation].count++;
      this.metrics[operation].totalTime += duration;
      this.metrics[operation].avgTime = this.metrics[operation].totalTime / this.metrics[operation].count;
      delete this.metrics[operation].startTime;
    }
  }),
  error: jest.fn(function(operation, error) {
    if (!this.metrics[operation]) {
      this.metrics[operation] = { count: 0, totalTime: 0, avgTime: 0, errorCount: 0 };
    }
    this.metrics[operation].errorCount++;
  }),
  getMetrics: jest.fn(function() {
    return { ...this.metrics };
  })
};

global.LocalCache = {
  data: null,
  clear: jest.fn(function() {
    this.data = null;
    sessionStorage.removeItem('pdf_builder_settings_backup');
  }),
  save: jest.fn(function(data) {
    this.data = data;
    const cache = {
      data: data,
      timestamp: Date.now(),
      version: '1.1',
      hash: this.simpleHash(JSON.stringify(data)),
      sessionId: this.getSessionId()
    };
    sessionStorage.setItem('pdf_builder_settings_backup', JSON.stringify(cache));
  }),
  load: jest.fn(function() {
    try {
      const cacheStr = sessionStorage.getItem('pdf_builder_settings_backup');
      if (!cacheStr) return null;

      const cache = JSON.parse(cacheStr);

      // Vérifier l'expiration (3h ou plus)
      if (Date.now() - cache.timestamp >= 3 * 60 * 60 * 1000) {
        return null;
      }

      // Vérifier la version et le hash
      if (cache.version !== '1.1' || cache.hash !== this.simpleHash(JSON.stringify(cache.data))) {
        return null;
      }

      return cache.data;
    } catch (e) {
      // Données corrompues
      return null;
    }
  }),
  simpleHash: jest.fn(() => 'mock-hash'),
  getSessionId: jest.fn(() => 'mock-session-id')
};

global.validateFormData = jest.fn((formData) => {
  const errors = [];

  // Validation des champs numériques
  const numericFields = ['pdf_builder_cache_max_size', 'pdf_builder_cache_ttl'];

  for (const field of numericFields) {
    if (formData[field] && isNaN(parseInt(formData[field]))) {
      errors.push(`${field} doit être un nombre`);
    }
  }

  return errors;
});

global.AjaxCompat = {
  fetch: jest.fn((url) => {
    if (url.includes('invalid-url-that-does-not-exist')) {
      return Promise.reject(new Error('Network error'));
    }
    return Promise.resolve({
      ok: true,
      json: () => Promise.resolve({})
    });
  })
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