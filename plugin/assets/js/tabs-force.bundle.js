"use strict";
(self["webpackChunkpdfBuilderReact"] = self["webpackChunkpdfBuilderReact"] || []).push([["tabs-force"],{

/***/ "./assets/js/tabs-force.js":
/*!*********************************!*\
  !*** ./assets/js/tabs-force.js ***!
  \*********************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/**
 * Navigation des onglets PDF Builder - Version Force Chargement
 */

(function () {
  'use strict';

  // Configuration de force
  var CONFIG = {
    debug: true,
    forceLoad: true
  };

  // Fonction de logging
  function log(message) {
    var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
  } // Logging disabled for production

  // Fonction de switch onglet
  function switchTab(tabId) {
    log('SWITCH vers:', tabId);
    var tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
    var tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
    log('Éléments trouvés:', {
      buttons: tabButtons.length,
      contents: tabContents.length
    });

    // Désactiver tous
    tabButtons.forEach(function (btn) {
      return btn.classList.remove('nav-tab-active');
    });
    tabContents.forEach(function (content) {
      return content.classList.remove('active');
    });

    // Activer l'onglet cible
    var targetBtn = document.querySelector("[data-tab=\"".concat(tabId, "\"]"));
    var targetContent = document.getElementById(tabId) || document.getElementById("tab-".concat(tabId)) || document.getElementById("tab-content-".concat(tabId));
    if (targetBtn) {
      targetBtn.classList.add('nav-tab-active');
      log('✅ Bouton activé:', targetBtn.textContent.trim());
    } else {
      log('❌ Bouton non trouvé pour:', tabId);
    }
    if (targetContent) {
      targetContent.classList.add('active');
      log('✅ Contenu activé:', targetContent.id);
    } else {
      log('❌ Contenu non trouvé pour:', tabId);
    }

    // Si un manager global existe, déléguer l'action
    if (window.PDF_BUILDER_TABS && typeof window.PDF_BUILDER_TABS.switchToTab === 'function') {
      try {
        window.PDF_BUILDER_TABS.switchToTab(tabId);
        log('Délégué switchTab au manager global');
        return;
      } catch (e) {
        log('Erreur lors de l\'appel du manager global:', e.message || e);
      }
    }

    // Déclencher événement si aucun manager global
    document.dispatchEvent(new CustomEvent('pdfBuilderTabChanged', {
      detail: {
        tabId: tabId,
        source: 'force'
      }
    }));
    log('SWITCH terminé pour:', tabId);
  }

  // Gestionnaire de clic
  function handleTabClick(e) {
    e.preventDefault();
    e.stopPropagation();
    var tabId = e.currentTarget.getAttribute('data-tab');
    if (!tabId) {
      log('❌ Aucun data-tab trouvé');
      return;
    }
    log('CLIC détecté sur:', tabId);
    // Si un manager global existe, utilisez son API pour s'assurer d'un comportement centralisé
    if (window.PDF_BUILDER_TABS && typeof window.PDF_BUILDER_TABS.switchToTab === 'function') {
      window.PDF_BUILDER_TABS.switchToTab(tabId);
      return;
    }
    switchTab(tabId);
  }

  // Initialisation
  function initialize() {
    log('INITIALISATION FORCE');
    var tabsContainer = document.getElementById('pdf-builder-tabs');
    var contentContainer = document.getElementById('pdf-builder-tab-content');
    if (!tabsContainer) {
      log('❌ Container onglets non trouvé');
      return false;
    }
    if (!contentContainer) {
      log('❌ Container contenu non trouvé');
      return false;
    }
    var tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
    var tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
    log('Onglets trouvés:', tabButtons.length);
    log('Contenus trouvés:', tabContents.length);

    // Attacher les événements
    tabButtons.forEach(function (btn, index) {
      btn.removeEventListener('click', handleTabClick);
      btn.addEventListener('click', handleTabClick);
      log("Event listener ajout\xE9 \xE0 l'onglet ".concat(index + 1, ":"), btn.getAttribute('data-tab'));
    });

    // Activer le premier onglet
    if (tabButtons[0]) {
      var firstTab = tabButtons[0].getAttribute('data-tab');
      log('Activation du premier onglet:', firstTab);
      setTimeout(function () {
        return switchTab(firstTab);
      }, 100);
    }
    window.PDF_BUILDER_TABS_FORCE_INITIALIZED = true;
    log('✅ INITIALISATION FORCE TERMINÉE');
    return true;
  }

  // Démarrage
  function start() {
    log('DÉMARRAGE FORCE');
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initialize);
    } else {
      initialize();
    }

    // Essayer aussi après un délai
    setTimeout(initialize, 500);

    // Surveillance continue
    setInterval(function () {
      if (!window.PDF_BUILDER_TABS_FORCE_INITIALIZED) {
        log('🔄 Nouvelle tentative d\'initialisation...');
        initialize();
      }
    }, 2000);
  }

  // Lancement immédiat
  start();

  // Export global pour diagnostic
  window.PDF_BUILDER_FORCE = {
    switchTab: switchTab,
    initialize: initialize,
    config: CONFIG
  };
})();

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ var __webpack_exports__ = (__webpack_exec__("./assets/js/tabs-force.js"));
/******/ window.pdfBuilderReact = __webpack_exports__;
/******/ }
]);
//# sourceMappingURL=tabs-force.bundle.js.map