import React from 'react';

/**
 * Renderer pour les éléments de texte dynamique avec variables
 */
export const DynamicTextRenderer = ({ element, previewData, mode }) => {
  const {
    x = 0,
    y = 0,
    width = 300,
    height = 50,
    template = 'total_only',
    customContent = '{{order_total}} €',
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
    // Propriétés avancées
    rotation = 0,
    scale = 1,
    visible = true,
    shadow = false,
    shadowColor = '#000000',
    shadowOffsetX = 2,
    shadowOffsetY = 2,
    textDecoration = 'none',
    lineHeight = 1.2
  } = element;

  // Récupérer le contenu depuis les données d'aperçu
  const elementKey = `dynamic-text_${element.id}`;
  const elementData = previewData[elementKey] || {};
  const displayContent = elementData.content || customContent;

  const style = {
    position: 'absolute',
    left: x,
    top: y,
    width,
    height,
    fontSize: `${fontSize}px`,
    fontFamily,
    fontWeight,
    fontStyle,
    textAlign,
    color,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity,
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    whiteSpace: 'pre-wrap',
    wordWrap: 'break-word',
    textDecoration,
    lineHeight,
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  return (
    <div
      className="preview-element preview-dynamic-text-element"
      style={style}
      data-element-id={element.id}
      data-element-type="dynamic-text"
      title={`Template: ${template}`}
    >
      {displayContent}
    </div>
  );
};