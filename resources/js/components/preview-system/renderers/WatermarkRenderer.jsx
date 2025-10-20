import React from 'react';

/**
 * Renderer pour les filigranes
 */
export const WatermarkRenderer = ({ element, previewData, mode }) => {
  const {
    x = 0,
    y = 0,
    width = 300,
    height = 200,
    content = 'CONFIDENTIEL',
    color = '#999999',
    fontSize = 48,
    fontFamily = 'Arial, sans-serif',
    fontWeight = 'bold',
    opacity = 10,
    rotation = -45,
    // Propriétés avancées
    scale = 1,
    visible = true,
    shadow = false,
    shadowColor = '#000000',
    shadowOffsetX = 2,
    shadowOffsetY = 2,
    textDecoration = 'none',
    lineHeight = 1.2
  } = element;

  const containerStyle = {
    position: 'absolute',
    left: x,
    top: y,
    width,
    height,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    pointerEvents: 'none',
    zIndex: 1000
  };

  const textStyle = {
    color,
    fontSize: `${fontSize}px`,
    fontFamily,
    fontWeight,
    opacity: opacity / 100,
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    textAlign: 'center',
    userSelect: 'none',
    whiteSpace: 'nowrap',
    textDecoration,
    lineHeight,
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  return (
    <div
      className="preview-element preview-watermark-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="watermark"
    >
      <div style={textStyle}>
        {content}
      </div>
    </div>
  );
};