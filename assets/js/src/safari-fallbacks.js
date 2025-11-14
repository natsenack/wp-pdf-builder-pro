// Corrections spécifiques pour Safari
(function() {
  'use strict';

  // Détecter Safari
  const isSafari = typeof navigator !== 'undefined' &&
    /Safari/.test(navigator.userAgent) &&
    !/Chrome/.test(navigator.userAgent) &&
    !/Chromium/.test(navigator.userAgent);

  if (!isSafari) {
    return; // Ne rien faire si ce n'est pas Safari
  }

  console.log('🧭 Application des corrections spécifiques à Safari');

  // Correction pour les Event Listeners passifs dans Safari
  if (typeof window !== 'undefined' && typeof window.EventTarget !== 'undefined') {
    const originalAddEventListener = window.EventTarget.prototype.addEventListener;

    window.EventTarget.prototype.addEventListener = function(type, listener, options) {
      // Safari a des problèmes spécifiques avec les options passives
      if (typeof options === 'object' && options.passive !== undefined) {
        try {
          // Safari peut ne pas supporter passive sur tous les événements
          const testOptions = { passive: true };
          const testFn = () => {};
          window.addEventListener('test', testFn, testOptions);
          window.removeEventListener('test', testFn, testOptions);

          return originalAddEventListener.call(this, type, listener, options);
        } catch (_) {
          // Ne rien faire, continuer avec le fallback
        }

        // Fallback pour Safari
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

  // Correction pour les APIs Canvas dans Safari
  if (typeof document !== 'undefined') {
    const originalGetContext = HTMLCanvasElement.prototype.getContext;

    HTMLCanvasElement.prototype.getContext = function(contextType, contextAttributes) {
      try {
        // Safari peut avoir des problèmes avec WebGL
        if (contextType === 'webgl' || contextType === 'experimental-webgl') {
          let context = originalGetContext.call(this, contextType, contextAttributes);
          if (context) {
            return context;
          }

          // Safari peut nécessiter des attributs spécifiques
          const safariAttributes = {
            alpha: true,
            depth: true,
            stencil: false,
            antialias: true,
            premultipliedAlpha: true,
            preserveDrawingBuffer: false,
            ...contextAttributes
          };

          context = originalGetContext.call(this, contextType, safariAttributes);
          if (context) {
            console.warn('Utilisation des attributs Safari pour WebGL');
            return context;
          }
        }

        return originalGetContext.call(this, contextType, contextAttributes);
      } catch (error) {
        console.error('Erreur lors de la création du contexte Canvas dans Safari:', error);
        return null;
      }
    };
  }

  // Correction pour les APIs de fichiers dans Safari
  if (typeof window !== 'undefined' && typeof window.FileReader !== 'undefined') {
    const originalReadAsDataURL = window.FileReader.prototype.readAsDataURL;
    const originalReadAsText = window.FileReader.prototype.readAsText;

    // Safari peut avoir des problèmes avec certains encodages
    window.FileReader.prototype.readAsText = function(file, encoding) {
      try {
        // Safari peut mal gérer certains encodages
        const safeEncoding = encoding || 'UTF-8';
        return originalReadAsText.call(this, file, safeEncoding);
      } catch (error) {
        console.error('Erreur FileReader.readAsText dans Safari:', error);
        // Fallback sans encodage spécifié
        return originalReadAsText.call(this, file);
      }
    };

    window.FileReader.prototype.readAsDataURL = function(file) {
      try {
        // Safari peut avoir des problèmes avec les fichiers volumineux
        if (file && file.size > 50 * 1024 * 1024) { // 50MB
          console.warn('Fichier volumineux détecté dans Safari, traitement peut être lent');
        }

        return originalReadAsDataURL.call(this, file);
      } catch (error) {
        console.error('Erreur FileReader.readAsDataURL dans Safari:', error);
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

  // Correction pour les APIs de performance dans Safari
  if (typeof window !== 'undefined') {
    // Safari peut ne pas avoir performance.now
    if (!window.performance) {
      window.performance = {};
    }

    if (!window.performance.now) {
      window.performance.now = function() {
        return Date.now();
      };
    }
  }

  // Correction pour les APIs de fetch dans Safari
  if (typeof window !== 'undefined' && typeof window.fetch !== 'undefined') {
    const originalFetch = window.fetch;

    window.fetch = function(input, init) {
      try {
        // Safari peut avoir des problèmes avec certaines options fetch
        if (init && init.mode === 'no-cors') {
          // Safari gère mal no-cors parfois
          console.warn('Mode no-cors détecté dans Safari, comportement peut différer');
        }

        return originalFetch.call(this, input, init);
      } catch (error) {
        console.error('Erreur fetch dans Safari:', error);
        throw error;
      }
    };
  }

  console.log('✅ Corrections Safari appliquées');

})();