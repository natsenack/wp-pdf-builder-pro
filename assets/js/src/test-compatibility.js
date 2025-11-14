// Test rapide des corrections de compatibilité pour tous les navigateurs
(function() {
  'use strict';

  console.log('🧪 Test des corrections de compatibilité multi-navigateurs');

  // Détection du navigateur
  const browserInfo = {
    isChrome: typeof navigator !== 'undefined' && /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor),
    isFirefox: typeof navigator !== 'undefined' && /Firefox/.test(navigator.userAgent),
    isSafari: typeof navigator !== 'undefined' && /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent) && !/Chromium/.test(navigator.userAgent),
    isEdge: typeof navigator !== 'undefined' && (/Edg/.test(navigator.userAgent) || /Edge/.test(navigator.userAgent)),
    isMobile: typeof navigator !== 'undefined' && /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
  };

  console.log('🌐 Navigateur détecté:', {
    Chrome: browserInfo.isChrome,
    Firefox: browserInfo.isFirefox,
    Safari: browserInfo.isSafari,
    Edge: browserInfo.isEdge,
    Mobile: browserInfo.isMobile,
    UserAgent: navigator.userAgent
  });

  // Test des polyfills
  const tests = [
    {
      name: 'Polyfill Promise',
      test: () => typeof Promise !== 'undefined' && typeof Promise.prototype.then !== 'undefined'
    },
    {
      name: 'Polyfill URLSearchParams',
      test: () => typeof URLSearchParams !== 'undefined'
    },
    {
      name: 'Polyfill Element.closest',
      test: () => typeof window !== 'undefined' && typeof window.Element !== 'undefined' && typeof window.Element.prototype.closest !== 'undefined'
    },
    {
      name: 'Polyfill Array.includes',
      test: () => typeof Array !== 'undefined' && typeof Array.prototype.includes !== 'undefined'
    },
    {
      name: 'Polyfill Object.assign',
      test: () => typeof Object.assign !== 'undefined'
    }
  ];

  let passed = 0;
  let total = tests.length;

  tests.forEach(test => {
    try {
      const result = test.test();
      if (result) {
        console.log(`✅ ${test.name}: OK`);
        passed++;
      } else {
        console.log(`❌ ${test.name}: ÉCHEC`);
      }
    } catch (error) {
      console.log(`❌ ${test.name}: ERREUR - ${error.message}`);
    }
  });

  console.log(`📊 Résultat polyfills: ${passed}/${total} tests réussis`);

  // Tests spécifiques par navigateur
  const browserSpecificTests = [];

  if (browserInfo.isChrome) {
    browserSpecificTests.push(
      {
        name: 'Chrome: Event Listeners passifs',
        test: () => {
          const options = { passive: true };
          const fn = () => {};
          window.addEventListener('test', fn, options);
          window.removeEventListener('test', fn, options);
          return true;
        }
      },
      {
        name: 'Chrome: Canvas 2D Context',
        test: () => {
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          return ctx !== null;
        }
      }
    );
  }

  if (browserInfo.isFirefox) {
    browserSpecificTests.push(
      {
        name: 'Firefox: Performance API',
        test: () => typeof window.performance !== 'undefined' && typeof window.performance.now !== 'undefined'
      }
    );
  }

  if (browserInfo.isSafari) {
    browserSpecificTests.push(
      {
        name: 'Safari: Fetch API',
        test: () => typeof fetch !== 'undefined'
      }
    );
  }

  if (browserInfo.isEdge) {
    browserSpecificTests.push(
      {
        name: 'Edge: URLSearchParams',
        test: () => typeof URLSearchParams !== 'undefined'
      }
    );
  }

  if (browserInfo.isMobile) {
    browserSpecificTests.push(
      {
        name: 'Mobile: Touch Events',
        test: () => typeof document !== 'undefined' && 'ontouchstart' in document
      },
      {
        name: 'Mobile: Vibration API',
        test: () => typeof navigator !== 'undefined' && typeof navigator.vibrate !== 'undefined'
      }
    );
  }

  // Exécuter les tests spécifiques
  let browserPassed = 0;
  let browserTotal = browserSpecificTests.length;

  browserSpecificTests.forEach(test => {
    try {
      const result = test.test();
      if (result) {
        console.log(`✅ ${test.name}: OK`);
        browserPassed++;
      } else {
        console.log(`❌ ${test.name}: ÉCHEC`);
      }
    } catch (error) {
      console.log(`❌ ${test.name}: ERREUR - ${error.message}`);
    }
  });

  if (browserTotal > 0) {
    console.log(`📊 Résultat navigateur spécifique: ${browserPassed}/${browserTotal} tests réussis`);
  }

  const totalPassed = passed + browserPassed;
  const totalTests = total + browserTotal;

  if (totalPassed === totalTests) {
    console.log('🎉 Toutes les corrections de compatibilité sont opérationnelles !');
  } else {
    console.log('⚠️ Certaines corrections peuvent ne pas fonctionner correctement.');
  }

  // Résumé final
  console.log('📋 Résumé de compatibilité:');
  console.log(`   - Polyfills généraux: ${passed}/${total}`);
  if (browserTotal > 0) {
    const browserName = browserInfo.isChrome ? 'Chrome' :
                       browserInfo.isFirefox ? 'Firefox' :
                       browserInfo.isSafari ? 'Safari' :
                       browserInfo.isEdge ? 'Edge' : 'Mobile';
    console.log(`   - Corrections ${browserName}: ${browserPassed}/${browserTotal}`);
  }
  console.log(`   - Total: ${totalPassed}/${totalTests}`);

})();