// DEBUG: Script execution started
alert('PDF Builder script executed');
console.error('🚀 PDF Builder Pro: Script execution started - FORCE LOG');

import React from 'react';
import ReactDOM from 'react-dom';

if (typeof window !== 'undefined') {
    window.React = React;
    window.ReactDOM = ReactDOM;
}

const pdfBuilderPro = {
    version: '2.0.0',
    editors: new Map(),
    init: function(containerId, options) {
        console.log('PDF Builder Pro init called for', containerId);
        return true;
    },
    destroy: function(containerId) {
        console.log('PDF Builder Pro destroy called for', containerId);
    },
    getData: function(containerId) {
        return null;
    },
    getElements: function() {
        return [];
    }
};

if (typeof window !== 'undefined') {
    window.pdfBuilderPro = pdfBuilderPro;
    window.initializePDFBuilderPro = function() {
        console.error('🚀 PDF Builder Pro: initializePDFBuilderPro called');
        return pdfBuilderPro;
    };
}

export default pdfBuilderPro;

console.error('🚀 PDF Builder Pro: Script fully loaded and globals exposed');
