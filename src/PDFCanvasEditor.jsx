const handleShowGridChange = () => {
    console.log('handleShowGridChange triggered');
    // TODO: Implémenter la logique de changement d'affichage de la grille
};

// Expose handleShowGridChange globally
if (typeof window !== 'undefined') {
  window.handleShowGridChange = handleShowGridChange;
}
