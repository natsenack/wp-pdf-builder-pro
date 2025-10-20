// Version simplifiée pour les tests - seulement l'exposition des globals
import React from 'react';
import ReactDOM from 'react-dom';

// Exposition minimale pour les tests
if (typeof window !== 'undefined') {
    window.React = React;
    window.ReactDOM = ReactDOM;
    window.PDFBuilderPreview = {
        PreviewModal: function(props) {
            return React.createElement('div', {
                style: {
                    padding: '20px',
                    background: '#f0f0f0',
                    border: '2px solid #007cba',
                    borderRadius: '8px',
                    textAlign: 'center'
                }
            }, '🧪 PreviewModal de Test - Mode: ' + (props.mode || 'inconnu'));
        }
    };
    console.log('=== BUNDLE SIMPLIFIÉ CHARGÉ ===');
    console.log('React disponible:', !!window.React);
    console.log('ReactDOM disponible:', !!window.ReactDOM);
    console.log('PDFBuilderPreview disponible:', !!window.PDFBuilderPreview);
}