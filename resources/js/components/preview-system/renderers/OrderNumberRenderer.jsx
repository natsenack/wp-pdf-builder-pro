import React from 'react';

/**
 * Renderer pour le numéro de commande
 */
export const OrderNumberRenderer = ({ element, previewData, mode }) => {
  const {
    x = 0,
    y = 0,
    width = 300,
    height = 40,
    showHeaders = false,
    showBorders = false,
    format = 'Commande #{order_number} - {order_date}',
    fontSize = 14,
    fontFamily = 'Arial',
    fontWeight = 'bold',
    textAlign = 'right',
    color = '#333333',
    showLabel = true,
    labelText = 'N° de commande:',
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

  // Récupérer les données du numéro de commande
  const elementKey = `order_number_${element.id}`;
  const orderData = previewData[elementKey] || {};
  const formattedNumber = orderData.formatted || format;

  const containerStyle = {
    position: 'absolute',
    left: x,
    top: y,
    width,
    height,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity,
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    fontSize: `${fontSize}px`,
    fontFamily,
    color,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: textAlign === 'right' ? 'flex-end' : textAlign === 'center' ? 'center' : 'flex-start',
    textDecoration,
    lineHeight,
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  const labelStyle = {
    fontWeight: 'normal',
    marginRight: showLabel ? '8px' : '0',
    color: '#666666'
  };

  const valueStyle = {
    fontWeight,
    textAlign
  };

  return (
    <div
      className="preview-element preview-order-number-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="order_number"
    >
      {showLabel && (
        <span style={labelStyle}>{labelText}</span>
      )}
      <span style={valueStyle}>
        {formattedNumber}
      </span>
    </div>
  );
};