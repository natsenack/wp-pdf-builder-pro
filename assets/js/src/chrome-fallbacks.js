// Corrections spécifiques pour Chrome
(function() {
  'use strict';

  // Détecter Chrome
  const isChrome = typeof navigator !== 'undefined' &&
    /Chrome/.test(navigator.userAgent) &&
    /Google Inc/.test(navigator.vendor);

  if (!isChrome) {
    return; // Ne rien faire si ce n'est pas Chrome
  }

  console.log('🔧 Application des corrections spécifiques à Chrome');

  // Correction pour les Event Listeners passifs dans Chrome
  if (typeof window !== 'undefined' && typeof window.EventTarget !== 'undefined') {
    const originalAddEventListener = window.EventTarget.prototype.addEventListener;

    window.EventTarget.prototype.addEventListener = function(type, listener, options) {
      // Chrome peut avoir des problèmes avec certaines options d'event listeners
      if (typeof options === 'object' && options.passive !== undefined) {
        try {
          // Tester d'abord si l'option passive est supportée
          const testOptions = { passive: true };
          const testFn = () => {};
          window.addEventListener('test', testFn, testOptions);
          window.removeEventListener('test', testFn, testOptions);

          // Si ça marche, utiliser les options originales
          return originalAddEventListener.call(this, type, listener, options);
        } catch (_) {
          // Ne rien faire, continuer avec les options sans passive
        }

        // Fallback: créer des options sans passive
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

  // Correction pour les APIs Canvas dans Chrome
  if (typeof document !== 'undefined') {
    const originalGetContext = HTMLCanvasElement.prototype.getContext;

    HTMLCanvasElement.prototype.getContext = function(contextType, contextAttributes) {
      try {
        // Chrome peut avoir des problèmes avec certains contextes WebGL
        if (contextType === 'webgl' || contextType === 'experimental-webgl') {
          // Essayer d'abord le contexte standard
          let context = originalGetContext.call(this, contextType, contextAttributes);
          if (context) {
            return context;
          }

          // Essayer le contexte expérimental si le standard échoue
          if (contextType === 'webgl') {
            context = originalGetContext.call(this, 'experimental-webgl', contextAttributes);
            if (context) {
              console.warn('Utilisation du contexte WebGL expérimental dans Chrome');
              return context;
            }
          }
        }

        // Pour les autres contextes, utiliser normalement
        return originalGetContext.call(this, contextType, contextAttributes);
      } catch (error) {
        console.error('Erreur lors de la création du contexte Canvas dans Chrome:', error);
        return null;
      }
    };
  }

  // Correction pour les APIs de fichiers dans Chrome
  if (typeof window !== 'undefined' && typeof window.FileReader !== 'undefined') {
    const originalReadAsDataURL = window.FileReader.prototype.readAsDataURL;
    const originalReadAsText = window.FileReader.prototype.readAsText;
    const originalReadAsArrayBuffer = window.FileReader.prototype.readAsArrayBuffer;

    // Wrapper pour gérer les erreurs de FileReader dans Chrome
    const wrapFileReaderMethod = (originalMethod) => {
      return function(file) {
        try {
          // Vérifier que le fichier est valide
          if (!file || typeof file !== 'object') {
            throw new Error('Fichier invalide passé à FileReader');
          }

          // Chrome peut avoir des problèmes avec les gros fichiers
          if (file.size > 50 * 1024 * 1024) { // 50MB
            console.warn('Fichier volumineux détecté dans Chrome, cela peut causer des problèmes de performance');
          }

          return originalMethod.call(this, file);
        } catch (error) {
          console.error('Erreur FileReader dans Chrome:', error);
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
    };

    window.FileReader.prototype.readAsDataURL = wrapFileReaderMethod(originalReadAsDataURL);
    window.FileReader.prototype.readAsText = wrapFileReaderMethod(originalReadAsText);
    window.FileReader.prototype.readAsArrayBuffer = wrapFileReaderMethod(originalReadAsArrayBuffer);
  }

  // Correction pour les APIs de drag & drop dans Chrome
  if (typeof document !== 'undefined') {
    // Chrome peut avoir des problèmes avec les types MIME personnalisés
    const originalAddEventListener = document.addEventListener;

    document.addEventListener = function(type, listener, options) {
      if (type === 'drop' || type === 'dragover' || type === 'dragleave') {
        const wrappedListener = function(event) {
          try {
            // Chrome peut envoyer des événements avec des dataTransfer null
            if (event.dataTransfer === null) {
              console.warn('Événement drag & drop avec dataTransfer null dans Chrome');
              event.dataTransfer = {
                types: [],
                files: [],
                getData: () => '',
                setData: () => {},
                clearData: () => {},
                setDragImage: () => {}
              };
            }

            // Appeler le listener original
            return listener.call(this, event);
          } catch (error) {
            console.error('Erreur dans le gestionnaire drag & drop de Chrome:', error);
          }
        };

        return originalAddEventListener.call(this, type, wrappedListener, options);
      }

      return originalAddEventListener.call(this, type, listener, options);
    };
  }

  console.log('✅ Corrections Chrome appliquées');

})();