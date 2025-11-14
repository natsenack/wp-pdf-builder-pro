// Diagnostics de compatibilité navigateur et polyfills
import './pdf-builder-react/utils/browser-polyfills.js';
import './chrome-fallbacks.js';
import './firefox-fallbacks.js';
import './safari-fallbacks.js';
import './edge-fallbacks.js';
import './mobile-fallbacks.js';

// Fonction de diagnostic des APIs navigateur
function checkBrowserCompatibility() {
  const results = {
    browser: navigator.userAgent,
    timestamp: new Date().toISOString(),
    apis: {},
    errors: []
  };

  // Test des APIs essentielles
  const apiTests = [
    {
      name: 'fetch',
      test: () => typeof fetch !== 'undefined',
      description: 'API Fetch pour les requêtes HTTP'
    },
    {
      name: 'Promise',
      test: () => typeof Promise !== 'undefined',
      description: 'Promises pour la programmation asynchrone'
    },
    {
      name: 'URLSearchParams',
      test: () => typeof URLSearchParams !== 'undefined',
      description: 'Manipulation des paramètres URL'
    },
    {
      name: 'Canvas',
      test: () => {
        try {
          const canvas = document.createElement('canvas');
          return !!(canvas.getContext && canvas.getContext('2d'));
        } catch (_) {
          return false;
        }
      },
      description: 'API Canvas 2D pour le rendu graphique'
    },
    {
      name: 'Drag and Drop',
      test: () => typeof document !== 'undefined' && 'ondragstart' in document.createElement('div'),
      description: 'API Drag & Drop pour l\'interface'
    },
    {
      name: 'File API',
      test: () => typeof File !== 'undefined' && typeof FileReader !== 'undefined',
      description: 'API File pour la gestion des fichiers'
    },
    {
      name: 'Event Listeners Passifs',
      test: () => {
        try {
          const options = { passive: true };
          const fn = () => {};
          window.addEventListener('test', fn, options);
          window.removeEventListener('test', fn, options);
          return true;
        } catch (_) {
          return false;
        }
      },
      description: 'Event Listeners passifs pour les performances'
    },
    {
      name: 'IntersectionObserver',
      test: () => typeof IntersectionObserver !== 'undefined',
      description: 'Intersection Observer pour la visibilité des éléments'
    },
    {
      name: 'ResizeObserver',
      test: () => typeof ResizeObserver !== 'undefined',
      description: 'Resize Observer pour les changements de taille'
    },
    {
      name: 'WebGL',
      test: () => {
        try {
          const canvas = document.createElement('canvas');
          return !!(canvas.getContext && canvas.getContext('webgl'));
        } catch (_) {
          return false;
        }
      },
      description: 'WebGL pour le rendu 3D accéléré'
    }
  ];

  // Exécuter les tests
  apiTests.forEach(api => {
    try {
      const supported = api.test();
      results.apis[api.name] = {
        supported: supported,
        description: api.description
      };

      if (!supported) {
        results.errors.push(`❌ ${api.name}: ${api.description} - Non supporté`);
      } else {
        console.log(`✅ ${api.name}: Supporté`);
      }
    } catch (error) {
      results.apis[api.name] = {
        supported: false,
        description: api.description,
        error: error.message
      };
      results.errors.push(`❌ ${api.name}: ${error.message}`);
    }
  });

  // Afficher un résumé
  console.group('🔍 Diagnostic de compatibilité navigateur');
  console.log('Navigateur:', results.browser);
  console.log('Timestamp:', results.timestamp);

  if (results.errors.length > 0) {
    console.warn('🚨 APIs non supportées:');
    results.errors.forEach(error => console.warn(error));
  } else {
    console.log('✅ Toutes les APIs essentielles sont supportées');
  }

  console.groupEnd();

  // Stocker les résultats pour débogage
  window.browserCompatibilityResults = results;

  return results;
}

// Exécuter le diagnostic au chargement
if (typeof window !== 'undefined') {
  // Attendre que le DOM soit prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkBrowserCompatibility);
  } else {
    checkBrowserCompatibility();
  }
}

export { checkBrowserCompatibility };