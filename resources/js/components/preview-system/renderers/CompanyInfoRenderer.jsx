import React from 'react';

/**
 * Renderer pour les informations entreprise
 */
export const CompanyInfoRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 300,
    height = 120,
    showHeaders = false,
    showBorders = false,
    fields = ['name', 'address', 'phone', 'email'],
    layout = 'vertical',
    showLabels = false,
    labelStyle = 'normal',
    spacing = 4,
    fontSize = 12,
    fontFamily = 'Arial',
    fontWeight = 'normal',
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

  // Récupérer les données entreprise
  const elementKey = `company_info_${element.id}`;
  const companyData = previewData[elementKey] || {};

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
    color,
    display: visible ? 'block' : 'none',
    textDecoration,
    lineHeight,
    // Transformations
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'center center',
    // Ombres
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  const fieldStyle = {
    marginBottom: layout === 'vertical' ? `${spacing}px` : '0',
    display: layout === 'horizontal' ? 'inline-block' : 'block',
    marginRight: layout === 'horizontal' ? `${spacing * 2}px` : '0'
  };

  const labelStyleConfig = {
    fontWeight: labelStyle === 'bold' ? 'bold' : 'normal',
    textTransform: labelStyle === 'uppercase' ? 'uppercase' : 'none',
    marginRight: showLabels ? '8px' : '0'
  };

  const valueStyle = {
    fontWeight,
    textAlign
  };

  // Mapping des champs vers leurs libellés
  const fieldLabels = {
    name: 'Nom :',
    address: 'Adresse :',
    phone: 'Téléphone :',
    email: 'Email :',
    website: 'Site web :',
    vat: 'TVA :',
    rcs: 'RCS :',
    siret: 'SIRET :'
  };

  return (
    <div
      className="preview-element preview-company-info-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="company_info"
    >
      {fields.map((field, index) => {
        const value = companyData[field];
        if (!value) return null;

        return (
          <div key={field} style={fieldStyle}>
            {showLabels && (
              <span style={labelStyleConfig}>
                {fieldLabels[field] || `${field} :`}
              </span>
            )}
            <span style={valueStyle}>
              {field === 'address' ? (
                <span style={{ whiteSpace: 'pre-line' }}>{value}</span>
              ) : (
                value
              )}
            </span>
          </div>
        );
      })}

      {/* Message si aucune donnée */}
      {fields.length === 0 || Object.keys(companyData).length === 0 && (
        <div style={{
          textAlign: 'center',
          color: '#6c757d',
          fontStyle: 'italic',
          padding: '20px'
        }}>
          Aucune information entreprise
        </div>
      )}
    </div>
  );
};