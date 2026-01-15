/**
 * Settings Tabs Script
 * Gère la navigation par onglets de la page paramètres
 */

(function () {
  "use strict";

  console.log("[PDF Builder] settings-tabs.js loaded");

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTabs);
  } else {
    initTabs();
  }

  function initTabs() {
    console.log("[PDF Builder] Initializing settings tabs");

    const tabs = document.querySelectorAll("[data-tab-id]");
    const panels = document.querySelectorAll("[data-panel-id]");

    tabs.forEach((tab) => {
      tab.addEventListener("click", function (e) {
        e.preventDefault();
        const tabId = this.getAttribute("data-tab-id");
        console.log("[PDF Builder] Tab clicked:", tabId);

        // Désactiver tous les onglets et panneaux
        tabs.forEach((t) => t.classList.remove("active"));
        panels.forEach((p) => p.classList.remove("active"));

        // Activer l'onglet et le panneau sélectionné
        this.classList.add("active");
        const panel = document.querySelector('[data-panel-id="' + tabId + '"]');
        if (panel) {
          panel.classList.add("active");
        }
      });
    });

    console.log("[PDF Builder] Settings tabs initialized");
  }
})();
