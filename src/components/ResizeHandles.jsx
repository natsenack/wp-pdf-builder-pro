import React from 'react';

/**
 * Composant pour les poignées de redimensionnement d'un élément
 * @param {Object} props - Propriétés du composant
 * @param {boolean} props.isVisible - Si les poignées sont visibles
 * @param {Function} props.onResizeStart - Callback appelé au début du redimensionnement
 * @param {Object} props.elementRect - Rectangle de l'élément {x, y, width, height}
 * @param {number} props.zoom - Niveau de zoom
 * @returns {JSX.Element|null} - Les poignées de redimensionnement
 */
export const ResizeHandles = ({
  isVisible,
  onResizeStart,
  elementRect,
  zoom = 1
}) => {
  if (!isVisible) return null;

  const handleMouseDown = (event, handle) => {
    onResizeStart(event, handle, elementRect, zoom);
  };

  return (
    <>
      {/* Poignée Nord */}
      <div
        className="resize-handle resize-handle-n"
        onMouseDown={(e) => handleMouseDown(e, 'n')}
      />

      {/* Poignée Sud */}
      <div
        className="resize-handle resize-handle-s"
        onMouseDown={(e) => handleMouseDown(e, 's')}
      />

      {/* Poignée Ouest */}
      <div
        className="resize-handle resize-handle-w"
        onMouseDown={(e) => handleMouseDown(e, 'w')}
      />

      {/* Poignée Est */}
      <div
        className="resize-handle resize-handle-e"
        onMouseDown={(e) => handleMouseDown(e, 'e')}
      />

      {/* Poignée Nord-Ouest */}
      <div
        className="resize-handle resize-handle-nw"
        onMouseDown={(e) => handleMouseDown(e, 'nw')}
      />

      {/* Poignée Nord-Est */}
      <div
        className="resize-handle resize-handle-ne"
        onMouseDown={(e) => handleMouseDown(e, 'ne')}
      />

      {/* Poignée Sud-Ouest */}
      <div
        className="resize-handle resize-handle-sw"
        onMouseDown={(e) => handleMouseDown(e, 'sw')}
      />

      {/* Poignée Sud-Est */}
      <div
        className="resize-handle resize-handle-se"
        onMouseDown={(e) => handleMouseDown(e, 'se')}
      />
    </>
  );
};