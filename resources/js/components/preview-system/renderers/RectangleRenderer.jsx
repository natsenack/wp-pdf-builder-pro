import React from 'react';

/**
 * Renderer pour les éléments géométriques (rectangles, lignes, formes)
 */
export const RectangleRenderer = ({ element, previewData, mode }) => {
  console.log('PDF Builder Debug: RectangleRenderer called for element:', element.id, 'with props:', {
    x: element.x, y: element.y, width: element.width, height: element.height,
    backgroundColor: element.backgroundColor, borderColor: element.borderColor, borderWidth: element.borderWidth
  });

  const {
    x = 0,
    y = 0,
    width = 100,
    height = 50,
    backgroundColor = 'transparent',
    borderColor = '#000000',
    borderWidth = 1,
    borderRadius = 0,
    opacity = 100,
    // Propriétés avancées
    rotation = 0,
    scale = 1,
    visible = true,
    shadow = false,
    shadowColor = '#000000',
    shadowOffsetX = 2,
    shadowOffsetY = 2
  } = element;

  const containerStyle = {
    position: 'absolute',
    left: x,
    top: y,
    width,
    height,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity: opacity / 100,
    display: visible ? 'block' : 'none',
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  return (
    <div
      className="preview-element preview-rectangle-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type={element.type}
    />
  );
};