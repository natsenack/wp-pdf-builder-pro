// Import global fallbacks first
import './globalFallback.js';

console.log('🚨🚨🚨 MAIN.JS CHARGÉ ET EXÉCUTÉ - TOP LEVEL 🚨🚨🚨');

// Vérifier immédiatement React
console.log('REACT CHECK - window.React:', typeof window.React);
console.log('REACT CHECK - window.ReactDOM:', typeof window.ReactDOM);
console.log('REACT CHECK - React.createElement:', typeof window.React?.createElement);
console.log('REACT CHECK - ReactDOM.createRoot:', typeof window.ReactDOM?.createRoot);

// Main application entry point that actually uses all components
import React from 'react';
import ReactDOM from 'react-dom/client';
import { PDFCanvasEditor } from './components/PDFCanvasEditor.jsx';

// Initialize the application
const init = (containerId, options = {}) => {
  console.log('🚀🚀🚀 NOUVELLE VERSION PDF Builder Pro: init() appelée avec', { containerId, options, timestamp: Date.now() });
  
  // Vérifier React et ReactDOM
  console.log('🔍 Vérification React global:', typeof window.React);
  console.log('🔍 Vérification ReactDOM global:', typeof window.ReactDOM);
  console.log('🔍 React.createElement disponible:', typeof window.React?.createElement);
  console.log('🔍 ReactDOM.createRoot disponible:', typeof window.ReactDOM?.createRoot);
  
  // Vérifier les imports locaux (devraient être undefined maintenant)
  console.log('🔍 React importé (devrait être undefined):', typeof React);
  console.log('🔍 ReactDOM importé (devrait être undefined):', typeof ReactDOM);
  console.log('🚀 React disponible:', typeof React);
  console.log('🚀 ReactDOM disponible:', typeof ReactDOM);
  console.log('🚀 ReactDOM.createRoot disponible:', typeof ReactDOM?.createRoot);

  const container = document.getElementById(containerId);
  console.log('🚀 Container recherché:', containerId, 'trouvé:', !!container);
  
  if (!container) {
    console.error('❌ PDF Builder Pro: Container non trouvé', containerId);
    return;
  }

  console.log('✅ PDF Builder Pro: Container trouvé', container);

  // Clear any existing content
  container.innerHTML = '';

  console.log('🚀 Tentative de création du root React...');
  
  // Vérifications supplémentaires avant utilisation
  if (!window.React) {
    console.error('❌ React n\'est pas disponible globalement');
    return;
  }
  if (!window.ReactDOM) {
    console.error('❌ ReactDOM n\'est pas disponible globalement');
    return;
  }
  if (!window.ReactDOM.createRoot) {
    console.error('❌ ReactDOM.createRoot n\'est pas disponible');
    return;
  }
  
  console.log('✅ Toutes les dépendances React sont disponibles');
  
  // Create React 18 root and render
  const root = window.ReactDOM.createRoot(container);
  console.log('✅ Root React créé:', !!root);
  
  try {
    console.log('�🚨🚨 MAIN.JS: TENTATIVE DE RENDU REACT 🚨🚨🚨');
    console.log('� PDFCanvasEditor importé:', typeof PDFCanvasEditor);
    console.log('� window.React.createElement:', typeof window.React?.createElement);
    
    const element = window.React.createElement(PDFCanvasEditor, {
      options: options
    });
    console.log('� ELEMENT REACT CRÉÉ:', !!element);
    console.log('🚨 TYPE ELEMENT:', element?.constructor?.name);
    
    console.log('🚨 AVANT ROOT.RENDER');
    root.render(element);
    console.log('🚨🚨🚨 ROOT.RENDER TERMINÉ SANS ERREUR 🚨🚨🚨');
    
    // Vérifier que le container a du contenu après le rendu
    setTimeout(() => {
      const containerContent = container.innerHTML;
      console.log('� CONTENU CONTAINER APRÈS RENDU:', containerContent.substring(0, 300) + '...');
      console.log('� NOMBRE ENFANTS:', container.children.length);
      if (container.children.length > 0) {
        console.log('🚨 PREMIER ENFANT:', container.children[0].tagName, container.children[0].className);
      }
    }, 100);
    
  } catch (error) {
    console.error('🚨🚨🚨 ERREUR FATALE DANS MAIN.JS RENDER 🚨🚨🚨:', error);
    console.error('🚨 STACK TRACE:', error.stack);
    console.error('🚨 TYPE ERREUR:', error.constructor.name);
    
    // Afficher l'erreur dans le container
    container.innerHTML = '<div style="color: red; padding: 20px; border: 2px solid red;"><h3>🚨 ERREUR REACT FATAL 🚨</h3><p>' + error.message + '</p><pre>' + error.stack + '</pre></div>';
  }

  console.log('✅ PDF Builder Pro: Éditeur initialisé avec succès - TIMESTAMP:', Date.now());
  
  // Ajouter un indicateur visible que les scripts sont chargés
  const indicator = document.createElement('div');
  indicator.id = 'pdf-builder-debug-indicator';
  indicator.style.cssText = 'position:fixed;top:10px;right:10px;background:red;color:white;padding:5px;font-size:12px;z-index:999999;border-radius:3px;';
  indicator.textContent = 'PDF Builder Scripts Chargés - ' + new Date().toLocaleTimeString();
  document.body.appendChild(indicator);
};

// Make it globally available
if (typeof window !== 'undefined') {
  if (!window.pdfBuilderPro) {
    window.pdfBuilderPro = {};
  }
  // Forcer l'assignation de la fonction init, même si pdfBuilderPro existe déjà
  console.log('🔧 Assignation de pdfBuilderPro.init...');
  window.pdfBuilderPro.init = init;
  console.log('✅ pdfBuilderPro.init assigné:', typeof window.pdfBuilderPro.init);
}

// Export for ES6 modules
export { init };
