import React from 'react';

// Global fallback for handleShowGridChange if not already defined
if (typeof window.handleShowGridChange === 'undefined') {
  window.handleShowGridChange = () => {
    console.log('handleShowGridChange fallback triggered');
    // TODO: Implémenter la logique de changement d'affichage de la grille
  };
}

const handleShowGridChange = () => {
    console.log('handleShowGridChange triggered');
    // TODO: Implémenter la logique de changement d'affichage de la grille
};

// Expose handleShowGridChange globally
if (typeof window !== 'undefined') {
  window.handleShowGridChange = handleShowGridChange;
}
