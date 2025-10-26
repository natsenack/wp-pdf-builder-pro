// Global fallbacks for PDF Builder Pro
// Polyfills and compatibility helpers

// Ensure console methods exist
if (typeof window !== 'undefined' && !window.console) {
  window.console = {
    log: function() {},
    warn: function() {},
    error: function() {},
    info: function() {}
  };
}

// Basic Object.assign polyfill for ES5
if (typeof Object.assign !== 'function') {
  Object.assign = function(target) {
    if (target == null) {
      throw new TypeError('Cannot convert undefined or null to object');
    }
    target = Object(target);
    for (var index = 1; index < arguments.length; index++) {
      var source = arguments[index];
      if (source != null) {
        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = key;
          }
        }
      }
    }
    return target;
  };
}

// Basic Array.includes polyfill
if (!Array.prototype.includes) {
  Array.prototype.includes = function(searchElement, fromIndex) {
    if (this == null) {
      throw new TypeError('"this" is null or not defined');
    }
    var o = Object(this);
    var len = o.length >>> 0;
    if (len === 0) {
      return false;
    }
    var n = fromIndex | 0;
    var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);
    while (k < len) {
      if (o[k] === searchElement) {
        return true;
      }
      k++;
    }
    return false;
  };
}

// Export for ES6 modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {};
}