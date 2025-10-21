import React from 'react';

/**
 * Renderer pour les éléments image (logos, etc.)
 */
export const ImageRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 150,
    height = 80,
    imageUrl = '',
    alt = 'Image',
    objectFit = 'contain', // 'contain', 'cover', 'fill', 'none', 'scale-down'
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
    brightness = 100,
    contrast = 100,
    saturate = 100
  } = element;

  // Récupérer les données d'image depuis l'aperçu
  const elementKey = `${element.type}_${element.id}`;
  const imageData = previewData[elementKey] || {};
  const finalImageUrl = imageData.imageUrl || imageUrl;

  const containerStyle = {
    position: 'absolute',
    left: x * canvasScale,
    top: y * canvasScale,
    width: width * canvasScale,
    height: height * canvasScale,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: 'center',
    boxSizing: 'border-box',
    overflow: 'hidden',
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  const imageStyle = {
    width: '100%',
    height: '100%',
    objectFit,
    borderRadius: borderWidth > 0 ? '0' : `${borderRadius}px`,
    // Filtres d'image
    filter: `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturate}%)`
  };

  const placeholderStyle = {
    width: '100%',
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#f8f9fa',
    border: '2px dashed #dee2e6',
    borderRadius: `${borderRadius}px`,
    color: '#6c757d',
    fontSize: '12px',
    textAlign: 'center',
    padding: '8px',
    boxSizing: 'border-box'
  };

  return (
    <div
      className="preview-element preview-image-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type={element.type}
    >
      {finalImageUrl ? (
        <img
          src={finalImageUrl}
          alt={alt}
          style={imageStyle}
          onError={(e) => {
            // Fallback vers le placeholder en cas d'erreur de chargement
            e.target.style.display = 'none';
            e.target.nextSibling.style.display = 'flex';
          }}
        />
      ) : null}

      {/* Placeholder affiché si pas d'image ou erreur de chargement */}
      <div
        style={{
          ...placeholderStyle,
          display: finalImageUrl ? 'none' : 'flex'
        }}
      >
        <div>
          <div style={{ fontSize: '16px', marginBottom: '4px' }}>📷</div>
          <div>{element.type === 'company_logo' ? 'Logo' : 'Image'}</div>
        </div>
      </div>
    </div>
  );
};