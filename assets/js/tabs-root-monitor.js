// tabs-root-monitor.js - Monitor for root tab changes
(function() {
    'use strict';

    window.TabsRootMonitor = {
        currentTab: null,

        monitor: function() {
            var tabs = document.querySelectorAll('.nav-tab-wrapper .nav-tab');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    var tabId = this.getAttribute('href') || this.getAttribute('data-tab');
                    window.TabsRootMonitor.onTabChange(tabId);
                });
            });
        },

        onTabChange: function(tabId) {
            this.currentTab = tabId;
            console.log('Tab changed to:', tabId);

            // Trigger custom event
            var event = new CustomEvent('tabChanged', {
                detail: { tabId: tabId }
            });
            document.dispatchEvent(event);
        },

        getCurrentTab: function() {
            return this.currentTab;
        },

        init: function() {
            console.log('Tabs root monitor initialized');
            this.monitor();
        }
    };

    // Auto-initialize
    window.TabsRootMonitor.init();
})();