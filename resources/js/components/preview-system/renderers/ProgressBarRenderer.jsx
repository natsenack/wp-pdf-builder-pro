import React from 'react';

/**
 * Renderer pour les barres de progression
 */
export const ProgressBarRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 200,
    height = 20,
    backgroundColor = '#e5e7eb',
    borderColor = '#d1d5db',
    borderWidth = 1,
    borderRadius = 10,
    opacity = 100,
    progressValue = 75,
    progressColor = '#3b82f6',
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
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity: opacity / 100,
    overflow: 'hidden',
    display: visible ? 'block' : 'none',
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  const progressStyle = {
    width: `${Math.min(100, Math.max(0, progressValue))}%`,
    height: '100%',
    backgroundColor: progressColor,
    transition: 'width 0.3s ease'
  };

  return (
    <div
      className="preview-element preview-progress-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="progress-bar"
    >
      <div style={progressStyle} />
    </div>
  );
};