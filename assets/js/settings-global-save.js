// settings-global-save.js - Global save functionality for settings
(function() {
    'use strict';

    window.SettingsGlobalSave = {
        saveAll: function() {
            console.log('Saving all settings globally...');

            // Collect all form data
            var forms = document.querySelectorAll('form.settings-form');
            var allData = {};

            forms.forEach(function(form) {
                var formData = new FormData(form);
                for (var pair of formData.entries()) {
                    allData[pair[0]] = pair[1];
                }
            });

            // Send to server
            this.sendToServer(allData);
        },

        sendToServer: function(data) {
            // Placeholder for AJAX call
            console.log('Sending data to server:', data);

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'save_global_settings',
                    data: data
                })
            })
            .then(response => response.json())
            .then(data => console.log('Success:', data))
            .catch(error => console.error('Error:', error));
        },

        init: function() {
            console.log('Global save functionality initialized');

            // Bind to save buttons
            document.addEventListener('click', function(e) {
                if (e.target.matches('.global-save-btn')) {
                    e.preventDefault();
                    window.SettingsGlobalSave.saveAll();
                }
            });
        }
    };

    // Auto-initialize
    window.SettingsGlobalSave.init();
})();