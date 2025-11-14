// Polyfills pour la compatibilité navigateur
(function() {
  'use strict';

  // Polyfill pour les Event Listeners passifs
  if (typeof window !== 'undefined' && typeof window.EventTarget !== 'undefined' && !window.EventTarget.prototype.addEventListenerPassive) {
    const originalAddEventListener = window.EventTarget.prototype.addEventListener;
    window.EventTarget.prototype.addEventListener = function(type, listener, options) {
      if (typeof options === 'object' && options.passive !== undefined) {
        // Créer une copie des options sans 'passive' pour les navigateurs qui ne le supportent pas
        const otherOptions = {};
        for (const key in options) {
          if (key !== 'passive') {
            otherOptions[key] = options[key];
          }
        }
        return originalAddEventListener.call(this, type, listener, otherOptions);
      }
      return originalAddEventListener.call(this, type, listener, options);
    };
  }

  // Polyfill pour URLSearchParams (pour IE)
  if (typeof window !== 'undefined' && typeof window.URLSearchParams === 'undefined') {
    window.URLSearchParams = function(search) {
      this.params = {};
      if (search) {
        const pairs = search.replace(/^\?/, '').split('&');
        for (let i = 0; i < pairs.length; i++) {
          const pair = pairs[i].split('=');
          this.params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
        }
      }
    };

    window.URLSearchParams.prototype.get = function(key) {
      return this.params[key] || null;
    };

    window.URLSearchParams.prototype.set = function(key, value) {
      this.params[key] = value;
    };

    window.URLSearchParams.prototype.toString = function() {
      const pairs = [];
      for (const key in this.params) {
        pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(this.params[key]));
      }
      return pairs.join('&');
    };
  }

  // Polyfill pour Element.closest (pour IE)
  if (typeof window !== 'undefined' && typeof window.Element !== 'undefined' && !window.Element.prototype.closest) {
    const findClosest = function(element, selector) {
      if (!element || element.nodeType !== 1) return null;
      if (element.matches && element.matches(selector)) return element;
      return findClosest(element.parentElement, selector);
    };
    window.Element.prototype.closest = function(selector) {
      return findClosest(this, selector);
    };
  }

  // Polyfill pour Element.matches (pour IE)
  if (typeof window !== 'undefined' && typeof window.Element !== 'undefined' && !window.Element.prototype.matches) {
    window.Element.prototype.matches = window.Element.prototype.msMatchesSelector ||
                                window.Element.prototype.webkitMatchesSelector;
  }

  // Polyfill pour Array.includes (pour IE)
  if (typeof Array !== 'undefined' && !Array.prototype.includes) {
    Array.prototype.includes = function(searchElement, fromIndex) {
      return this.indexOf(searchElement, fromIndex) !== -1;
    };
  }

  // Polyfill pour Object.assign (pour IE)
  if (typeof Object !== 'undefined' && typeof Object.assign !== 'function') {
    Object.assign = function(target) {
      if (target == null) {
        throw new TypeError('Cannot convert undefined or null to object');
      }

      target = Object(target);
      for (let index = 1; index < arguments.length; index++) {
        const source = arguments[index];
        if (source != null) {
          for (const key in source) {
            if (Object.prototype.hasOwnProperty.call(source, key)) {
              target[key] = source[key];
            }
          }
        }
      }
      return target;
    };
  }

  // Polyfill pour Promise (version simplifiée pour IE)
  if (typeof window !== 'undefined' && typeof window.Promise === 'undefined') {
    window.Promise = function(executor) {
      this._state = 'pending';
      this._value = undefined;
      this._callbacks = [];

      const resolve = (value) => {
        if (this._state === 'pending') {
          this._state = 'fulfilled';
          this._value = value;
          this._callbacks.forEach((callback) => {
            if (callback.onFulfilled) {
              callback.onFulfilled(value);
            }
          });
        }
      };

      const reject = (reason) => {
        if (this._state === 'pending') {
          this._state = 'rejected';
          this._value = reason;
          this._callbacks.forEach((callback) => {
            if (callback.onRejected) {
              callback.onRejected(reason);
            }
          });
        }
      };

      try {
        executor(resolve, reject);
      } catch (error) {
        reject(error);
      }
    };

    window.Promise.prototype.then = function(onFulfilled, onRejected) {
      return new window.Promise((resolve, reject) => {
        const handleCallback = () => {
          try {
            if (this._state === 'fulfilled') {
              const result = onFulfilled ? onFulfilled(this._value) : this._value;
              resolve(result);
            } else if (this._state === 'rejected') {
              const result = onRejected ? onRejected(this._value) : this._value;
              reject(result);
            }
          } catch (error) {
            reject(error);
          }
        };

        if (this._state === 'pending') {
          this._callbacks.push({
            onFulfilled: onFulfilled,
            onRejected: onRejected,
            resolve: resolve,
            reject: reject
          });
        } else {
          setTimeout(handleCallback, 0);
        }
      });
    };

    window.Promise.prototype.catch = function(onRejected) {
      return this.then(null, onRejected);
    };
  }

  console.log('🔧 Polyfills chargés pour la compatibilité navigateur');

})();