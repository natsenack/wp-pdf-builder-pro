// Diagnostics de compatibilité navigateur et polyfills
import '../pdf-builder-react/utils/browser-polyfills.js';
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

  // Afficher un résumé seulement en mode débogage ou s'il y a des erreurs
  if (isDebugMode() || results.errors.length > 0) {
    console.group('🔍 Diagnostic de compatibilité navigateur');
    // Liste des APIs vérifiées
    // console.table(results.apis); // Optionnel pour la table complète

    if (results.errors.length > 0) {
      console.warn('🚨 APIs non supportées:');
      results.errors.forEach(error => console.warn(error));
    }
    console.groupEnd();
  }

  // Stocker les résultats pour débogage
  window.browserCompatibilityResults = results;

  return results;
}

// Fonction d'aide pour vérifier le mode débogage
function isDebugMode() {
  return typeof window !== 'undefined' && window.pdfBuilderDebugSettings?.javascript;
}

// Exécuter le diagnostic au chargement (seulement en mode débogage ou s'il y a des erreurs)
function runDiagnostic() {
  const results = checkBrowserCompatibility();

  if (isDebugMode() || results.errors.length > 0) {
    return results; // La fonction checkBrowserCompatibility gère déjà les logs
  }

  // En mode production et sans erreurs, redéfinir checkBrowserCompatibility pour éviter les appels répétitifs
  window.checkBrowserCompatibility = () => results;
  return results;
}

if (typeof window !== 'undefined') {
  // Attendre que le DOM soit prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runDiagnostic);
  } else {
    runDiagnostic();
  }
}

export { checkBrowserCompatibility };
