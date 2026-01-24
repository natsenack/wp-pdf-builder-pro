// Corrections spécifiques pour Firefox
(function() {
  'use strict';

  // Détecter Firefox
  const isFirefox = typeof navigator !== 'undefined' &&
    /Firefox/.test(navigator.userAgent);

  if (!isFirefox) {
    return; // Ne rien faire si ce n'est pas Firefox
  }



  // Correction pour les Event Listeners passifs dans Firefox
  if (typeof window !== 'undefined' && typeof window.EventTarget !== 'undefined') {
    const originalAddEventListener = window.EventTarget.prototype.addEventListener;

    window.EventTarget.prototype.addEventListener = function(type, listener, options) {
      // Firefox peut avoir des problèmes avec certaines combinaisons d'options
      if (typeof options === 'object' && options.passive !== undefined) {
        try {
          return originalAddEventListener.call(this, type, listener, options);
        } catch (_) {
          // Ne rien faire, continuer avec les options par défaut
        }

        // Fallback: utiliser des options sans passive
        const safeOptions = { capture: options.capture || false };
        return originalAddEventListener.call(this, type, listener, safeOptions);
      }

      return originalAddEventListener.call(this, type, listener, options);
    };
  }

  // Correction pour les APIs Canvas dans Firefox
  if (typeof document !== 'undefined') {
    const originalGetContext = HTMLCanvasElement.prototype.getContext;

    HTMLCanvasElement.prototype.getContext = function(contextType, contextAttributes) {
      try {
        // Firefox peut avoir des problèmes avec WebGL sur certains systèmes
        if (contextType === 'webgl' || contextType === 'experimental-webgl') {
          let context = originalGetContext.call(this, contextType, contextAttributes);
          if (context) {
            return context;
          }

          // Essayer l'autre variante
          const altType = contextType === 'webgl' ? 'experimental-webgl' : 'webgl';
          context = originalGetContext.call(this, altType, contextAttributes);
          if (context) {
            console.warn('Utilisation du contexte WebGL alternatif dans Firefox');
            return context;
          }
        }

        return originalGetContext.call(this, contextType, contextAttributes);
      } catch (error) {
        console.error('Erreur lors de la création du contexte Canvas dans Firefox:', error);
        return null;
      }
    };
  }

  // Correction pour les APIs de fichiers dans Firefox
  if (typeof window !== 'undefined' && typeof window.FileReader !== 'undefined') {
    const originalReadAsDataURL = window.FileReader.prototype.readAsDataURL;

    window.FileReader.prototype.readAsDataURL = function(file) {
      try {
        // Firefox peut avoir des problèmes avec certains types de fichiers
        if (!file || typeof file !== 'object') {
          throw new Error('Fichier invalide passé à FileReader');
        }

        // Firefox gère bien les gros fichiers mais peut être lent
        if (file.size > 100 * 1024 * 1024) { // 100MB
          console.warn('Fichier très volumineux détecté dans Firefox, traitement peut être lent');
        }

        return originalReadAsDataURL.call(this, file);
      } catch (error) {
        console.error('Erreur FileReader dans Firefox:', error);
        // Déclencher un événement d'erreur
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

  // Correction pour les APIs de performance dans Firefox
  if (typeof window !== 'undefined' && typeof window.performance !== 'undefined') {
    // Firefox peut avoir des problèmes avec certaines métriques de performance
    if (!window.performance.now) {
      window.performance.now = function() {
        return Date.now();
      };
    }
  }



})();
