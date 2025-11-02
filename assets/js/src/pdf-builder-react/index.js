// Import des composants React
import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import { PDFBuilder } from './PDFBuilder.tsx';
import { DEFAULT_CANVAS_WIDTH, DEFAULT_CANVAS_HEIGHT } from './constants/canvas.ts';

// État de l'application
let currentTemplate = null;
let isModified = false;

function initPDFBuilderReact() {
  console.log('PDF Builder React initialized successfully!');
  return true;
}

// Export default pour webpack
const exports = {
  initPDFBuilderReact
};

// Assigner la fonction à window pour l'accès global depuis WordPress
if (typeof window !== 'undefined') {
  window.pdfBuilderReact = exports;
}

export default exports;