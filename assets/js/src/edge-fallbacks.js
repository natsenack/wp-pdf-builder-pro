// Corrections spécifiques pour Microsoft Edge
(function() {
  'use strict';

  // Détecter Edge (nouveau Chromium-based Edge)
  const isEdge = typeof navigator !== 'undefined' &&
    /Edg/.test(navigator.userAgent);

  // Détecter aussi l'ancien Edge
  const isOldEdge = typeof navigator !== 'undefined' &&
    /Edge/.test(navigator.userAgent) &&
    !/Edg/.test(navigator.userAgent);

  if (!isEdge && !isOldEdge) {
    return; // Ne rien faire si ce n'est pas Edge
  }

  const edgeVersion = isOldEdge ? 'ancien' : 'nouveau';
  console.log(`🌐 Application des corrections spécifiques à Edge (${edgeVersion})`);

  // Correction pour les Event Listeners passifs dans Edge
  if (typeof window !== 'undefined' && typeof window.EventTarget !== 'undefined') {
    const originalAddEventListener = window.EventTarget.prototype.addEventListener;

    window.EventTarget.prototype.addEventListener = function(type, listener, options) {
      // Edge peut avoir des problèmes avec les options passives
      if (typeof options === 'object' && options.passive !== undefined) {
        try {
          return originalAddEventListener.call(this, type, listener, options);
        } catch (_) {
          // Ne rien faire, continuer avec le fallback
        }

        // Fallback pour Edge
        const safeOptions = {};
        for (const key in options) {
          if (key !== 'passive') {
            safeOptions[key] = options[key];
          }
        }
        return originalAddEventListener.call(this, type, listener, safeOptions);
      }

      return originalAddEventListener.call(this, type, listener, options);
    };
  }

  // Correction pour les APIs Canvas dans Edge
  if (typeof document !== 'undefined') {
    const originalGetContext = HTMLCanvasElement.prototype.getContext;

    HTMLCanvasElement.prototype.getContext = function(contextType, contextAttributes) {
      try {
        // Edge peut avoir des problèmes avec WebGL
        if (contextType === 'webgl' || contextType === 'experimental-webgl') {
          let context = originalGetContext.call(this, contextType, contextAttributes);
          if (context) {
            return context;
          }

          // L'ancien Edge peut nécessiter experimental-webgl
          if (isOldEdge && contextType === 'webgl') {
            context = originalGetContext.call(this, 'experimental-webgl', contextAttributes);
            if (context) {
              console.warn('Utilisation du contexte WebGL expérimental dans l\'ancien Edge');
              return context;
            }
          }
        }

        return originalGetContext.call(this, contextType, contextAttributes);
      } catch (error) {
        console.error('Erreur lors de la création du contexte Canvas dans Edge:', error);
        return null;
      }
    };
  }

  // Correction pour les APIs de fichiers dans Edge
  if (typeof window !== 'undefined' && typeof window.FileReader !== 'undefined') {
    const originalReadAsDataURL = window.FileReader.prototype.readAsDataURL;

    window.FileReader.prototype.readAsDataURL = function(file) {
      try {
        // Edge peut avoir des problèmes avec certains types de fichiers
        if (!file || typeof file !== 'object') {
          throw new Error('Fichier invalide passé à FileReader');
        }

        // L'ancien Edge peut être lent avec les gros fichiers
        if (isOldEdge && file.size > 10 * 1024 * 1024) { // 10MB
          console.warn('Fichier volumineux détecté dans l\'ancien Edge, traitement peut être lent');
        }

        return originalReadAsDataURL.call(this, file);
      } catch (error) {
        console.error('Erreur FileReader dans Edge:', error);
        setTimeout(() => {
          if (this.onerror) {
            const event = new Event('error');
            event.target = this;
            this.onerror(event);
          }
        }, 0);
      }
    };
  }

  // Correction pour les APIs de fetch dans Edge
  if (typeof window !== 'undefined' && typeof window.fetch !== 'undefined') {
    const originalFetch = window.fetch;

    window.fetch = function(input, init) {
      try {
        // L'ancien Edge peut avoir des problèmes avec certaines options
        if (isOldEdge && init) {
          // L'ancien Edge ne supporte pas certaines options modernes
          const safeInit = {};
          for (const key in init) {
            if (['method', 'headers', 'body', 'mode', 'credentials', 'cache'].includes(key)) {
              safeInit[key] = init[key];
            }
          }
          return originalFetch.call(this, input, safeInit);
        }

        return originalFetch.call(this, input, init);
      } catch (error) {
        console.error('Erreur fetch dans Edge:', error);
        throw error;
      }
    };
  }

  // Correction pour les APIs de performance dans Edge
  if (typeof window !== 'undefined') {
    if (!window.performance) {
      window.performance = {};
    }

    if (!window.performance.now) {
      window.performance.now = function() {
        return Date.now();
      };
    }

    // L'ancien Edge peut ne pas avoir certaines APIs de performance
    if (isOldEdge && !window.performance.mark) {
      window.performance.mark = function() {
        // Stub pour l'ancien Edge
      };
    }
  }

  // Correction pour les APIs URL dans Edge
  if (typeof window !== 'undefined' && isOldEdge) {
    // L'ancien Edge peut ne pas avoir URLSearchParams
    if (!window.URLSearchParams) {
      // Utiliser le polyfill déjà défini dans browser-polyfills.js
      console.log('Utilisation du polyfill URLSearchParams pour l\'ancien Edge');
    }
  }

  console.log('✅ Corrections Edge appliquées');

})();