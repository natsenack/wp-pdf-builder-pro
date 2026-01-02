import React from 'react';

/**
 * Renderer pour les éléments de texte simple
 */
export const TextRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 200,
    height = 50,
    content = '',
    text = content || 'Texte d\'exemple',
    fontSize = 14,
    fontFamily = 'Arial',
    fontWeight = 'normal',
    fontStyle = 'normal',
    textAlign = 'left',
    color = '#333333',
    backgroundColor = 'transparent',
    borderWidth = 0,
    borderColor = '#000000',
    borderRadius = 0,
    opacity = 1,
    rotation = 0,
    scale = 1,
    visible = true,
    shadow = false,
    shadowColor = '#000000',
    shadowOffsetX = 2,
    shadowOffsetY = 2,
    textDecoration = 'none',
    lineHeight = 1.2,
    padding = 4
  } = element;

  const style = {
    position: 'absolute',
    left: `${x * canvasScale}px`,
    top: `${y * canvasScale}px`,
    width: `${width * canvasScale}px`,
    height: `${height * canvasScale}px`,
    fontSize: `${fontSize * canvasScale}px`,
    fontFamily,
    fontWeight,
    fontStyle,
    textAlign,
    color,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity,
    padding: `${padding}px`,
    boxSizing: 'border-box',
    overflow: 'hidden',
    display: visible ? 'block' : 'none',
    whiteSpace: 'pre-wrap',
    wordWrap: 'break-word',
    textDecoration,
    lineHeight: `${lineHeight}`,
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'top left',
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  return (
    <div
      className="preview-element preview-text-element"
      style={style}
      data-element-id={element.id}
      data-element-type="text"
    >
      {text}
    </div>
  );
};