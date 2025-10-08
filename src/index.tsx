// Étendre l'interface Window pour WordPress
interface Window {
  PDFBuilderPro?: {
    init: (containerId: string) => void;
  };
}

import React from 'react';
import { createRoot } from 'react-dom/client';
import CanvasBuilder from './components/CanvasBuilder';

// Fonction d'initialisation pour WordPress
(window as any).PDFBuilderPro = {
  init: (containerId: string) => {
    const container = document.getElementById(containerId);
    if (container) {
      const root = createRoot(container);
      root.render(<CanvasBuilder />);
    } else {
      console.error(`Container with ID "${containerId}" not found`);
    }
  }
};

// Export par défaut pour les tests
export default CanvasBuilder;