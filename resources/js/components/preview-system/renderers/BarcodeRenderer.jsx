import React from 'react';

/**
 * Renderer pour les codes-barres et QR codes
 */
export const BarcodeRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 150,
    height = 60,
    backgroundColor = 'transparent',
    borderColor = '#000000',
    borderWidth = 1,
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
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    opacity: opacity / 100,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '10px',
    color: '#666',
    fontFamily: 'monospace',
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  // Placeholder pour les codes-barres/QR codes
  const placeholderText = element.type === 'qrcode' ? 'QR CODE' : 'BARCODE';

  return (
    <div
      className="preview-element preview-barcode-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type={element.type}
    >
      <div style={{
        border: '1px dashed #ccc',
        width: '80%',
        height: '80%',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#f9f9f9'
      }}>
        {placeholderText}
      </div>
    </div>
  );
};