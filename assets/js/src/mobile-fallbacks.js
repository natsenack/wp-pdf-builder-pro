// Corrections pour les navigateurs mobiles
(function() {
  'use strict';

  // Détecter les navigateurs mobiles
  const isMobile = typeof navigator !== 'undefined' &&
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

  if (!isMobile) {
    return; // Ne rien faire si ce n'est pas mobile
  }

  console.log('📱 Application des corrections pour navigateurs mobiles');

  // Correction pour les Event Listeners passifs sur mobile
  if (typeof window !== 'undefined' && typeof window.EventTarget !== 'undefined') {
    const originalAddEventListener = window.EventTarget.prototype.addEventListener;

    window.EventTarget.prototype.addEventListener = function(type, listener, options) {
      // Les navigateurs mobiles bénéficient particulièrement des listeners passifs
      // pour les événements de scroll et touch
      if (typeof options === 'object' && options.passive !== undefined) {
        try {
          return originalAddEventListener.call(this, type, listener, options);
        } catch (_) {
          // Ne rien faire, continuer avec le fallback
        }

        // Fallback pour les navigateurs mobiles qui ne supportent pas passive
        const safeOptions = {};
        for (const key in options) {
          if (key !== 'passive') {
            safeOptions[key] = options[key];
          }
        }
        return originalAddEventListener.call(this, type, listener, safeOptions);
      }

      // Forcer passive pour certains événements sur mobile pour les performances
      const passiveEvents = ['touchstart', 'touchmove', 'touchend', 'wheel', 'scroll'];
      if (typeof options === 'boolean' || (typeof options !== 'object' && passiveEvents.includes(type))) {
        try {
          const passiveOptions = { passive: true, capture: options.capture || false };
          return originalAddEventListener.call(this, type, listener, passiveOptions);
        } catch (_) {
          // Ne rien faire, utiliser les options originales
        }

        return originalAddEventListener.call(this, type, listener, options);
      }

      return originalAddEventListener.call(this, type, listener, options);
    };
  }

  // Correction pour les APIs Canvas sur mobile
  if (typeof document !== 'undefined') {
    const originalGetContext = HTMLCanvasElement.prototype.getContext;

    HTMLCanvasElement.prototype.getContext = function(contextType, contextAttributes) {
      try {
        // Les appareils mobiles peuvent avoir des limitations WebGL
        if (contextType === 'webgl' || contextType === 'experimental-webgl') {
          // Attributs optimisés pour mobile
          const mobileAttributes = {
            alpha: false, // Désactiver alpha pour de meilleures performances
            depth: true,
            stencil: false,
            antialias: false, // Désactiver antialiasing pour économiser la batterie
            premultipliedAlpha: false,
            preserveDrawingBuffer: false,
            ...contextAttributes
          };

          let context = originalGetContext.call(this, contextType, mobileAttributes);
          if (context) {
            console.log('Contexte WebGL optimisé pour mobile');
            return context;
          }
        }

        return originalGetContext.call(this, contextType, contextAttributes);
      } catch (error) {
        console.error('Erreur lors de la création du contexte Canvas sur mobile:', error);
        return null;
      }
    };
  }

  // Correction pour les APIs de fichiers sur mobile
  if (typeof window !== 'undefined' && typeof window.FileReader !== 'undefined') {
    const originalReadAsDataURL = window.FileReader.prototype.readAsDataURL;

    window.FileReader.prototype.readAsDataURL = function(file) {
      try {
        // Les appareils mobiles peuvent avoir des limitations de mémoire
        if (file && file.size > 5 * 1024 * 1024) { // 5MB
          console.warn('Fichier volumineux détecté sur mobile, traitement peut être lent ou échouer');
        }

        return originalReadAsDataURL.call(this, file);
      } catch (error) {
        console.error('Erreur FileReader sur mobile:', error);
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

  // Correction pour les APIs de vibration sur mobile
  if (typeof navigator !== 'undefined' && !navigator.vibrate) {
    navigator.vibrate = function(_pattern) {
      // Stub pour les navigateurs mobiles qui ne supportent pas la vibration
      console.log('Vibration demandée mais non supportée sur cet appareil');
      return false;
    };
  }

  // Correction pour les APIs de géolocalisation sur mobile
  if (typeof navigator !== 'undefined' && navigator.geolocation) {
    const originalGetCurrentPosition = navigator.geolocation.getCurrentPosition;

    navigator.geolocation.getCurrentPosition = function(success, error, options) {
      try {
        // Options optimisées pour mobile
        const mobileOptions = {
          enableHighAccuracy: false, // Économiser la batterie
          timeout: 10000,
          maximumAge: 300000, // 5 minutes
          ...options
        };

        return originalGetCurrentPosition.call(this, success, error, mobileOptions);
      } catch (e) {
        console.error('Erreur de géolocalisation sur mobile:', e);
        if (error) {
          error({ code: 1, message: 'Géolocalisation non disponible' });
        }
      }
    };
  }

  // Correction pour les APIs de batterie sur mobile
  if (typeof navigator !== 'undefined' && !navigator.getBattery) {
    // Polyfill simple pour l'API Battery
    navigator.getBattery = function() {
      return Promise.resolve({
        charging: true,
        chargingTime: 0,
        dischargingTime: Infinity,
        level: 1,
        addEventListener: function() {},
        removeEventListener: function() {}
      });
    };
  }

  console.log('✅ Corrections mobiles appliquées');

})();