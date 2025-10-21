import React from 'react';

/**
 * Renderer pour les mentions légales
 */
export const MentionsRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 400,
    height = 60,
    showEmail = true,
    showPhone = true,
    showSiret = true,
    showVat = false,
    showAddress = false,
    showWebsite = false,
    showCustomText = false,
    customText = '',
    fontSize = 8,
    fontFamily = 'Arial',
    fontWeight = 'normal',
    textAlign = 'center',
    color = '#666666',
    lineHeight = 1.2,
    separator = ' • ',
    layout = 'horizontal', // 'horizontal' ou 'vertical'
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
    textDecoration = 'none'
  } = element;

  // Récupérer les données des mentions
  const elementKey = `mentions_${element.id}`;
  const mentionsData = previewData[elementKey] || {};
  const mentions = mentionsData.mentions || [];

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
    padding: '4px',
    boxSizing: 'border-box',
    overflow: 'hidden',
    fontSize: `${fontSize * canvasScale}px`,
    fontFamily,
    fontWeight,
    textAlign,
    color,
    lineHeight,
    display: visible ? 'flex' : 'none',
    alignItems: 'center',
    justifyContent: layout === 'horizontal' ? 'center' : 'flex-start',
    flexDirection: layout === 'vertical' ? 'column' : 'row',
    gap: layout === 'vertical' ? '4px' : '0',
    textDecoration,
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  const mentionStyle = {
    flexShrink: 0
  };

  return (
    <div
      className="preview-element preview-mentions-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="mentions"
    >
      {mentions.map((mention, index) => (
        <React.Fragment key={index}>
          <span style={mentionStyle}>{mention}</span>
          {index < mentions.length - 1 && layout === 'horizontal' && (
            <span style={{ margin: '0 8px', color: '#999' }}>{separator}</span>
          )}
        </React.Fragment>
      ))}

      {/* Message si aucune mention */}
      {mentions.length === 0 && (
        <div style={{
          textAlign: 'center',
          color: '#6c757d',
          fontStyle: 'italic',
          width: '100%'
        }}>
          Aucune mention légale
        </div>
      )}
    </div>
  );
};