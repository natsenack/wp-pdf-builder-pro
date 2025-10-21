import React, { useState, useCallback } from 'react';
import { usePreviewContext } from './context/PreviewContext';
import { usePerformanceMonitor } from './hooks/usePerformanceMonitor';

/**
 * NavigationControls - Contrôles de navigation pour l'aperçu modal
 * Inclut la navigation par page, zoom, rotation, et export
 */
function NavigationControls({ className = '' }) {
  const {
    state: { currentPage, totalPages, zoom, rotation, isFullscreen },
    actions: { setCurrentPage, setZoom, setRotation, toggleFullscreen }
  } = usePreviewContext();

  const { measureOperation } = usePerformanceMonitor('NavigationControls');
  const [showZoomMenu, setShowZoomMenu] = useState(false);

  // Navigation par page
  const goToPage = useCallback((page) => {
    const timer = measureOperation('goToPage');
    setCurrentPage(Math.max(1, Math.min(totalPages, page)));
    timer.end();
  }, [setCurrentPage, totalPages, measureOperation]);

  const goToPreviousPage = useCallback(() => {
    goToPage(currentPage - 1);
  }, [goToPage, currentPage]);

  const goToNextPage = useCallback(() => {
    goToPage(currentPage + 1);
  }, [goToPage, currentPage]);

  // Contrôles de zoom
  const zoomLevels = [25, 50, 75, 100, 125, 150, 200, 300, 400];

  const handleZoomChange = useCallback((newZoom) => {
    const timer = measureOperation('zoomChange');
    setZoom(Math.max(10, Math.min(500, newZoom)));
    setShowZoomMenu(false);
    timer.end();
  }, [setZoom, measureOperation]);

  const zoomIn = useCallback(() => {
    const currentIndex = zoomLevels.findIndex(level => level >= zoom);
    const nextZoom = zoomLevels[Math.min(currentIndex + 1, zoomLevels.length - 1)];
    handleZoomChange(nextZoom);
  }, [zoom, handleZoomChange, zoomLevels]);

  const zoomOut = useCallback(() => {
    const currentIndex = zoomLevels.findIndex(level => level >= zoom);
    const prevZoom = zoomLevels[Math.max(currentIndex - 1, 0)];
    handleZoomChange(prevZoom);
  }, [zoom, handleZoomChange, zoomLevels]);

  const fitToWidth = useCallback(() => {
    handleZoomChange(100); // Logique à implémenter selon la largeur du conteneur
  }, [handleZoomChange]);

  const fitToPage = useCallback(() => {
    handleZoomChange(100); // Logique à implémenter selon les dimensions de la page
  }, [handleZoomChange]);

  // Rotation
  const rotateClockwise = useCallback(() => {
    const timer = measureOperation('rotate');
    setRotation((rotation + 90) % 360);
    timer.end();
  }, [setRotation, rotation, measureOperation]);

  const rotateCounterClockwise = useCallback(() => {
    const timer = measureOperation('rotate');
    setRotation((rotation - 90 + 360) % 360);
    timer.end();
  }, [setRotation, rotation, measureOperation]);

  // Export (placeholder pour l'instant)
  const handleExport = useCallback(() => {
    const timer = measureOperation('export');
    // TODO: Implémenter l'export selon le type (PDF, PNG, etc.)
    console.log('Export functionality to be implemented');
    timer.end();
  }, [measureOperation]);

  return (
    <div className={`navigation-controls ${className}`}>
      {/* Barre de navigation principale */}
      <div className="nav-main-bar">
        {/* Navigation par page */}
        <div className="nav-page-controls">
          <button
            className="nav-btn nav-btn-previous"
            onClick={goToPreviousPage}
            disabled={currentPage <= 1}
            title="Page précédente"
          >
            ‹
          </button>

          <div className="nav-page-indicator">
            <input
              type="number"
              min="1"
              max={totalPages}
              value={currentPage}
              onChange={(e) => goToPage(parseInt(e.target.value) || 1)}
              className="nav-page-input"
            />
            <span className="nav-page-total"> / {totalPages}</span>
          </div>

          <button
            className="nav-btn nav-btn-next"
            onClick={goToNextPage}
            disabled={currentPage >= totalPages}
            title="Page suivante"
          >
            ›
          </button>
        </div>

        {/* Contrôles de zoom */}
        <div className="nav-zoom-controls">
          <button
            className="nav-btn nav-btn-zoom-out"
            onClick={zoomOut}
            disabled={zoom <= zoomLevels[0]}
            title="Zoom arrière"
          >
            −
          </button>

          <div className="nav-zoom-dropdown">
            <button
              className="nav-btn nav-zoom-current"
              onClick={() => setShowZoomMenu(!showZoomMenu)}
              title="Changer le zoom"
            >
              {zoom}%
            </button>

            {showZoomMenu && (
              <div className="nav-zoom-menu">
                {zoomLevels.map(level => (
                  <button
                    key={level}
                    className={`nav-zoom-option ${zoom === level ? 'active' : ''}`}
                    onClick={() => handleZoomChange(level)}
                  >
                    {level}%
                  </button>
                ))}
                <div className="nav-zoom-separator"></div>
                <button className="nav-zoom-option" onClick={fitToWidth}>
                  Ajuster à la largeur
                </button>
                <button className="nav-zoom-option" onClick={fitToPage}>
                  Ajuster à la page
                </button>
              </div>
            )}
          </div>

          <button
            className="nav-btn nav-btn-zoom-in"
            onClick={zoomIn}
            disabled={zoom >= zoomLevels[zoomLevels.length - 1]}
            title="Zoom avant"
          >
            +
          </button>
        </div>

        {/* Rotation */}
        <div className="nav-rotation-controls">
          <button
            className="nav-btn nav-btn-rotate-ccw"
            onClick={rotateCounterClockwise}
            title="Rotation antihoraire"
          >
            ⟲
          </button>

          <span className="nav-rotation-display">{rotation}°</span>

          <button
            className="nav-btn nav-btn-rotate-cw"
            onClick={rotateClockwise}
            title="Rotation horaire"
          >
            ⟳
          </button>
        </div>

        {/* Export et plein écran */}
        <div className="nav-action-controls">
          <button
            className="nav-btn nav-btn-export"
            onClick={handleExport}
            title="Exporter"
          >
            ⬇
          </button>

          <button
            className="nav-btn nav-btn-fullscreen"
            onClick={toggleFullscreen}
            title={isFullscreen ? 'Quitter le plein écran' : 'Plein écran'}
          >
            {isFullscreen ? '⛶' : '⛶'}
          </button>
        </div>
      </div>

      {/* Indicateur de statut */}
      <div className="nav-status-bar">
        <span className="nav-status-text">
          Page {currentPage} sur {totalPages} • Zoom {zoom}% • Rotation {rotation}°
        </span>
      </div>
    </div>
  );
}

export default React.memo(NavigationControls);