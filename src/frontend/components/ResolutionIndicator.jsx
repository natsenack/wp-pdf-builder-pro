import React, { useMemo } from 'react';

/**
 * Composant ResolutionIndicator
 * Affiche l'indicateur de résolution et DPI dans le coin supérieur droit du canvas
 */
const ResolutionIndicator = ({ canvasWidth, canvasHeight, dpi, zoom, showIndicator = true }) => {
  // Calculer les dimensions en pixels réels (sans zoom)
  const realWidth = useMemo(() => Math.round(canvasWidth), [canvasWidth]);
  const realHeight = useMemo(() => Math.round(canvasHeight), [canvasHeight]);

  // Calculer les dimensions affichées (avec zoom)
  const displayWidth = useMemo(() => Math.round(canvasWidth * zoom / 100), [canvasWidth, zoom]);
  const displayHeight = useMemo(() => Math.round(canvasHeight * zoom / 100), [canvasHeight, zoom]);

  // Ne pas afficher si désactivé
  if (!showIndicator) {
    return null;
  }

  return (
    <div className="resolution-indicator">
      <div className="resolution-info">
        <span className="resolution-dimensions">
          {displayWidth} × {displayHeight}px
        </span>
        <span className="resolution-dpi">
          {dpi} DPI
        </span>
      </div>
      <div className="resolution-zoom">
        {Math.round(zoom)}%
      </div>
    </div>
  );
};

export default ResolutionIndicator;