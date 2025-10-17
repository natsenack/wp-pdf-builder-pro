// Imports doivent être au niveau supérieur du module
import React from 'react';
import ReactDOM from 'react-dom';

console.log('PDF Builder Pro: Script execution started');

// Test des imports de base
try {
    console.log('Testing React availability...');
    console.log('React version:', React.version);
    console.log('ReactDOM available:', typeof ReactDOM);

    // Exposer React globalement pour compatibilité
    if (typeof window !== 'undefined') {
        window.React = React;
        window.ReactDOM = ReactDOM;
        console.log('PDF Builder Pro: React exposed globally');
    }
} catch (error) {
    console.error('React test failed:', error);
}

// Définition basique de PDFBuilderPro
if (typeof window !== 'undefined') {
    window.PDFBuilderPro = {
        test: 'ok',
        version: 'debug-test',
        init: function(containerId, options) {
            console.log('PDFBuilderPro.init called with:', containerId, options);
            return { success: true };
        }
    };
    console.log('PDF Builder Pro: Basic PDFBuilderPro defined on window');
}

