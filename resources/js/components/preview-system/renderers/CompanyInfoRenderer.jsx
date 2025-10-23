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
    template = 'custom', // Nouveau: template prédéfini
    showLabels = false,
    showPlaceholders = true, // Nouveau: afficher les placeholders pour données manquantes
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

  // Configuration des templates prédéfinis par secteur
  const templates = {
    custom: {
      fields: fields,
      layout: layout,
      showLabels: showLabels,
      labelStyle: labelStyle,
      showPlaceholders: showPlaceholders
    },
    b2b: {
      fields: ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'],
      layout: 'vertical',
      showLabels: true,
      labelStyle: 'bold',
      showPlaceholders: true
    },
    b2c: {
      fields: ['name', 'address', 'phone', 'email', 'website'],
      layout: 'vertical',
      showLabels: true,
      labelStyle: 'normal',
      showPlaceholders: true
    },
    services: {
      fields: ['name', 'address', 'phone', 'email', 'website', 'vat'],
      layout: 'vertical',
      showLabels: true,
      labelStyle: 'normal',
      showPlaceholders: true
    },
    retail: {
      fields: ['name', 'address', 'phone', 'email'],
      layout: 'horizontal',
      showLabels: false,
      labelStyle: 'normal',
      showPlaceholders: false
    },
    minimal: {
      fields: ['name', 'phone', 'email'],
      layout: 'vertical',
      showLabels: false,
      labelStyle: 'normal',
      showPlaceholders: false
    },
    legal: {
      fields: ['name', 'address', 'vat', 'rcs', 'siret'],
      layout: 'vertical',
      showLabels: true,
      labelStyle: 'bold',
      showPlaceholders: true
    }
  };

  // Appliquer le template sélectionné
  const currentTemplate = templates[template] || templates.custom;
  const effectiveFields = currentTemplate.fields;
  const effectiveLayout = currentTemplate.layout;
  const effectiveShowLabels = currentTemplate.showLabels;
  const effectiveLabelStyle = currentTemplate.labelStyle;
  const effectiveShowPlaceholders = currentTemplate.showPlaceholders;

  // Calcul responsive : ajuster automatiquement la mise en page selon la largeur disponible
  const isNarrowContainer = width < 200;
  const isVeryNarrowContainer = width < 150;

  // Ajustements responsives
  const responsiveLayout = isVeryNarrowContainer ? 'vertical' :
                          (isNarrowContainer && effectiveLayout === 'horizontal') ? 'vertical' :
                          effectiveLayout;

  const responsiveFontSize = isVeryNarrowContainer ? Math.max(fontSize * 0.8, 8) :
                            isNarrowContainer ? Math.max(fontSize * 0.9, 9) :
                            fontSize;

  const responsiveSpacing = isVeryNarrowContainer ? Math.max(spacing * 0.5, 2) :
                           isNarrowContainer ? Math.max(spacing * 0.75, 3) :
                           spacing;

  const responsiveShowLabels = isVeryNarrowContainer ? false : effectiveShowLabels;
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
    fontSize: `${responsiveFontSize * canvasScale}px`,
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
    marginBottom: responsiveLayout === 'vertical' ? `${responsiveSpacing}px` : '0',
    display: responsiveLayout === 'horizontal' ? 'inline-block' : 'block',
    marginRight: responsiveLayout === 'horizontal' ? `${responsiveSpacing * 2}px` : '0'
  };

  const labelStyleConfig = {
    fontWeight: effectiveLabelStyle === 'bold' ? 'bold' : 'normal',
    textTransform: effectiveLabelStyle === 'uppercase' ? 'uppercase' : 'none',
    marginRight: effectiveShowLabels ? '8px' : '0'
  };

  const valueStyle = {
    fontWeight,
    textAlign
  };

  // Mapping des champs vers leurs libellés et fallbacks
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

  const fieldPlaceholders = {
    name: 'Nom de l\'entreprise non configuré',
    address: 'Adresse non configurée',
    phone: 'Téléphone non configuré',
    email: 'Email non configuré',
    website: 'Site web non configuré',
    vat: 'Numéro TVA non configuré',
    rcs: 'RCS non configuré',
    siret: 'SIRET non configuré'
  };

  return (
    <div
      className="preview-element preview-company-info-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="company_info"
    >
      {effectiveFields.map((field, index) => {
        const value = companyData[field];
        const hasValue = value && value.trim() !== '';

        // Si pas de valeur et qu'on ne montre pas les placeholders, skip
        if (!hasValue && !effectiveShowPlaceholders) return null;

        const displayValue = hasValue ? value : (fieldPlaceholders[field] || `${field} non configuré`);
        const isPlaceholder = !hasValue;

        return (
          <div key={field} style={fieldStyle}>
            {responsiveShowLabels && (
              <span style={labelStyleConfig}>
                {fieldLabels[field] || `${field} :`}
              </span>
            )}
            <span style={{
              ...valueStyle,
              color: isPlaceholder ? '#6c757d' : color,
              fontStyle: isPlaceholder ? 'italic' : 'normal',
              wordWrap: 'break-word',
              overflowWrap: 'break-word',
              maxWidth: '100%'
            }}>
              {field === 'address' ? (
                <span style={{
                  whiteSpace: isVeryNarrowContainer ? 'normal' : 'pre-line',
                  wordWrap: 'break-word'
                }}>
                  {displayValue}
                </span>
              ) : field === 'email' || field === 'website' ? (
                <span style={{
                  wordBreak: isNarrowContainer ? 'break-all' : 'break-word'
                }}>
                  {displayValue}
                </span>
              ) : (
                displayValue
              )}
            </span>
          </div>
        );
      })}

      {/* Message si aucun champ configuré */}
      {effectiveFields.length === 0 && (
        <div style={{
          textAlign: 'center',
          color: '#6c757d',
          fontStyle: 'italic',
          padding: '20px'
        }}>
          Aucun champ d'information entreprise configuré
        </div>
      )}
    </div>
  );
};