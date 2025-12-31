"use strict";
(self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([["settings-global-save"],{

/***/ "./assets/js/settings-global-save.js":
/*!*******************************************!*\
  !*** ./assets/js/settings-global-save.js ***!
  \*******************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * PDF Builder Settings Global Save System
 * Handles global save functionality for all settings tabs
 */

(function ($) {
  'use strict';

  // Global settings save system
  window.PDFBuilderSettingsSaver = {
    /**
     * Collect all settings from a specific tab
     * @param {string} tabId - The tab ID to collect settings from
     * @returns {Object} Collected settings data
     */
    collectTabSettings: function collectTabSettings(tabId) {
      var settings = {};
      var tabContainer = document.getElementById('pdf-builder-tab-' + tabId);
      if (!tabContainer) {
        console.warn('[PDF Builder] Tab container not found:', tabId);
        return settings;
      }

      // Find all input, select, and textarea elements within the tab
      var inputs = tabContainer.querySelectorAll('input, select, textarea');
      inputs.forEach(function (element) {
        var name = element.name;
        if (!name) return;
        var value;

        // Handle different input types
        switch (element.type) {
          case 'checkbox':
            value = element.checked ? '1' : '0';
            break;
          case 'radio':
            if (element.checked) {
              value = element.value;
            } else {
              return; // Skip unchecked radio buttons
            }
            break;
          case 'number':
          case 'range':
            value = element.value ? parseFloat(element.value) : '';
            break;
          default:
            value = element.value || '';
        }

        // Remove 'pdf_builder_' prefix if present and add tab prefix
        var cleanName = name.replace(/^pdf_builder_/, '');
        var prefixedName = tabId + '_' + cleanName;
        settings[prefixedName] = value;
      });
      return settings;
    },
    /**
     * Collect all settings from all tabs
     * @returns {Object} All collected settings data
     */
    collectAllSettings: function collectAllSettings() {
      var allSettings = {};
      var tabs = ['general', 'appearance', 'security', 'advanced'];
      tabs.forEach(function (tabId) {
        var tabSettings = this.collectTabSettings(tabId);
        Object.assign(allSettings, tabSettings);
      }.bind(this));
      return allSettings;
    },
    /**
     * Save settings for a specific tab
     * @param {string} tabId - The tab ID to save
     * @param {Object} settings - Settings data to save
     * @returns {Promise} Promise that resolves with the AJAX response
     */
    saveTabSettings: function saveTabSettings(tabId, settings) {
      return new Promise(function (resolve, reject) {
        var nonce = window.pdfBuilderAjax ? window.pdfBuilderAjax.nonce : '';
        $.ajax({
          url: window.ajaxurl || window.pdfBuilderAjax.ajaxUrl,
          type: 'POST',
          data: _objectSpread({
            action: 'pdf_builder_save_' + tabId,
            _wpnonce: nonce
          }, settings),
          success: function success(response) {
            if (response.success) {
              resolve(response);
            } else {
              reject(new Error(response.data || 'Unknown error'));
            }
          },
          error: function error(xhr, status, _error) {
            reject(new Error('AJAX Error: ' + _error));
          }
        });
      });
    },
    /**
     * Save all settings globally across all tabs
     * @returns {Promise} Promise that resolves when all saves are complete
     */
    saveAllSettingsGlobally: function saveAllSettingsGlobally() {
      var self = this;
      var tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
      var promises = [];

      // Show loading state
      this.showGlobalSaveLoading();
      tabs.forEach(function (tabId) {
        var settings = self.collectTabSettings(tabId);
        if (Object.keys(settings).length > 0) {
          promises.push(self.saveTabSettings(tabId, settings));
        }
      });
      return Promise.all(promises).then(function (results) {
        self.hideGlobalSaveLoading();
        self.showGlobalSaveSuccess();
        return results;
      })["catch"](function (error) {
        self.hideGlobalSaveLoading();
        self.showGlobalSaveError(error.message);
        throw error;
      });
    },
    /**
     * Show global save loading state
     */
    showGlobalSaveLoading: function showGlobalSaveLoading() {
      var button = document.getElementById('pdf-builder-global-save-btn');
      if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner is-active"></span> Sauvegarde...';
      }
    },
    /**
     * Hide global save loading state
     */
    hideGlobalSaveLoading: function hideGlobalSaveLoading() {
      var button = document.getElementById('pdf-builder-global-save-btn');
      if (button) {
        button.disabled = false;
        button.innerHTML = 'Sauvegarder Tout';
      }
    },
    /**
     * Show global save success message
     */
    showGlobalSaveSuccess: function showGlobalSaveSuccess() {
      this.showGlobalSaveMessage('Paramètres sauvegardés avec succès!', 'success');
    },
    /**
     * Show global save error message
     */
    showGlobalSaveError: function showGlobalSaveError(message) {
      this.showGlobalSaveMessage('Erreur lors de la sauvegarde: ' + message, 'error');
    },
    /**
     * Show global save message
     */
    showGlobalSaveMessage: function showGlobalSaveMessage(message, type) {
      // Remove existing messages
      var existingMessages = document.querySelectorAll('.pdf-builder-global-save-message');
      existingMessages.forEach(function (msg) {
        msg.remove();
      });

      // Create new message
      var messageDiv = document.createElement('div');
      messageDiv.className = 'pdf-builder-global-save-message notice notice-' + type + ' is-dismissible';
      messageDiv.innerHTML = '<p>' + message + '</p>';

      // Add close button functionality
      var closeBtn = document.createElement('button');
      closeBtn.type = 'button';
      closeBtn.className = 'notice-dismiss';
      closeBtn.addEventListener('click', function () {
        messageDiv.remove();
      });
      messageDiv.appendChild(closeBtn);

      // Insert after the global save button
      var button = document.getElementById('pdf-builder-global-save-btn');
      if (button && button.parentNode) {
        button.parentNode.insertBefore(messageDiv, button.nextSibling);
      }

      // Auto-hide success messages after 5 seconds
      if (type === 'success') {
        setTimeout(function () {
          if (messageDiv.parentNode) {
            messageDiv.remove();
          }
        }, 5000);
      }
    },
    /**
     * Initialize the global save system
     */
    init: function init() {
      var self = this;

      // Bind global save button
      var globalSaveBtn = document.getElementById('pdf-builder-global-save-btn');
      if (globalSaveBtn) {
        globalSaveBtn.addEventListener('click', function (e) {
          e.preventDefault();
          self.saveAllSettingsGlobally()["catch"](function (error) {
            console.error('[PDF Builder] Global save failed:', error);
          });
        });
      }

      // Bind individual tab save buttons (if they exist)
      var tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
      tabs.forEach(function (tabId) {
        var tabSaveBtn = document.getElementById('pdf-builder-save-' + tabId + '-btn');
        if (tabSaveBtn) {
          tabSaveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var settings = self.collectTabSettings(tabId);
            self.saveTabSettings(tabId, settings).then(function () {
              self.showTabSaveSuccess(tabId);
            })["catch"](function (error) {
              self.showTabSaveError(tabId, error.message);
            });
          });
        }
      });
    },
    /**
     * Show tab-specific save success
     */
    showTabSaveSuccess: function showTabSaveSuccess(tabId) {
      this.showTabSaveMessage(tabId, 'Paramètres sauvegardés!', 'success');
    },
    /**
     * Show tab-specific save error
     */
    showTabSaveError: function showTabSaveError(tabId, message) {
      this.showTabSaveMessage(tabId, 'Erreur: ' + message, 'error');
    },
    /**
     * Show tab-specific save message
     */
    showTabSaveMessage: function showTabSaveMessage(tabId, message, type) {
      var tabContainer = document.getElementById('pdf-builder-tab-' + tabId);
      if (!tabContainer) return;

      // Remove existing messages in this tab
      var existingMessages = tabContainer.querySelectorAll('.pdf-builder-tab-save-message');
      existingMessages.forEach(function (msg) {
        msg.remove();
      });

      // Create new message
      var messageDiv = document.createElement('div');
      messageDiv.className = 'pdf-builder-tab-save-message notice notice-' + type;
      messageDiv.innerHTML = '<p>' + message + '</p>';

      // Insert at the top of the tab
      var firstElement = tabContainer.firstElementChild;
      tabContainer.insertBefore(messageDiv, firstElement);

      // Auto-hide success messages after 3 seconds
      if (type === 'success') {
        setTimeout(function () {
          if (messageDiv.parentNode) {
            messageDiv.remove();
          }
        }, 3000);
      }
    }
  };

  // Initialize when DOM is ready
  $(document).ready(function () {
    window.PDFBuilderSettingsSaver.init();
  });
})(jQuery);

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/settings-global-save.js"));
/******/ window.pdfBuilderReact = __webpack_exports__;
/******/ }
]);
//# sourceMappingURL=settings-global-save.bundle.js.map