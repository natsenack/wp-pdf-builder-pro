import React from 'react';

/**
 * Interface standardisée pour tous les renderers d'éléments
 * Version 2.0 - Architecture uniforme et performante
 */

// Props communes à tous les renderers
export const createBaseRenderer = (WrappedComponent) => {
  return React.forwardRef(function BaseRenderer(props, ref) {
    const {
      // Données de l'élément
      element,
      
      // Données d'aperçu (pour injection dynamique)
      previewData = {},
      
      // Configuration de rendu
      mode = 'canvas',
      scale = 1,
      zoom = 1,
      isPreview = true,
      
      // Callbacks (désactivés en mode aperçu)
      onSelect = null,
      onUpdate = null,
      onRemove = null,
      
      // Props supplémentaires
      className = '',
      style = {},
      ...restProps
    } = props;

    // Calcul de l'échelle finale
    const finalScale = scale * zoom;
    
    // Style de base pour tous les éléments
    const baseStyle = {
      position: 'absolute',
      left: (element.x || 0) * finalScale,
      top: (element.y || 0) * finalScale,
      width: (element.width || 100) * finalScale,
      height: (element.height || 50) * finalScale,
      
      // Propriétés communes
      opacity: element.opacity ?? 1,
      zIndex: element.zIndex ?? 1,
      transform: element.rotation ? `rotate(${element.rotation}deg)` : 'none',
      
      // Gestion des interactions en mode aperçu
      pointerEvents: isPreview ? 'none' : 'auto',
      userSelect: isPreview ? 'none' : 'auto',
      
      // Style personnalisé
      ...style
    };

    // Classes CSS communes
    const baseClassName = [
      'canvas-element',
      `element-${element.type}`,
      isPreview ? 'preview-mode' : 'edit-mode',
      className
    ].filter(Boolean).join(' ');

    // Props normalisées pour le composant wrappé
    const normalizedProps = {
      ...restProps,
      element,
      previewData,
      mode,
      scale: finalScale,
      isPreview,
      onSelect: isPreview ? null : onSelect,
      onUpdate: isPreview ? null : onUpdate,
      onRemove: isPreview ? null : onRemove,
      className: baseClassName,
      style: baseStyle,
      ref
    };

    return <WrappedComponent {...normalizedProps} />;
  });
};

/**
 * Hook pour normaliser les données d'un élément
 * Permet d'injecter des données dynamiques et de gérer les valeurs par défaut
 */
export function useElementData(element, previewData = {}) {
  return React.useMemo(() => {
    // Clé pour les données d'aperçu
    const elementKey = `${element.type}_${element.id}`;
    const elementPreviewData = previewData[elementKey] || {};
    
    // Fusion des données : élément de base + données d'aperçu
    return {
      ...element,
      ...elementPreviewData
    };
  }, [element, previewData]);
}

/**
 * Hook pour calculer les styles visuels d'un élément
 * Gère les bordures, arrière-plans, ombres, etc.
 */
export function useElementStyles(element, scale = 1) {
  return React.useMemo(() => {
    const {
      backgroundColor = 'transparent',
      borderWidth = 0,
      borderColor = '#000000',
      borderStyle = 'solid',
      borderRadius = 0,
      boxShadow = null,
      ...otherProps
    } = element;

    return {
      backgroundColor,
      border: borderWidth > 0 ? `${borderWidth * scale}px ${borderStyle} ${borderColor}` : 'none',
      borderRadius: `${borderRadius * scale}px`,
      boxShadow: boxShadow ? boxShadow : 'none',
      
      // Autres propriétés visuelles
      fontSize: element.fontSize ? `${element.fontSize * scale}px` : undefined,
      fontFamily: element.fontFamily,
      fontWeight: element.fontWeight,
      fontStyle: element.fontStyle,
      color: element.color,
      textAlign: element.textAlign,
      lineHeight: element.lineHeight,
      textDecoration: element.textDecoration,
    };
  }, [element, scale]);
}

/**
 * Renderer pour les éléments de texte
 * Version 2.0 - Optimisé et standardisé
 */
const TextRendererBase = ({ element, previewData, style, className, isPreview, scale }) => {
  const elementData = useElementData(element, previewData);
  const visualStyles = useElementStyles(elementData, scale);
  
  // Contenu du texte avec données dynamiques
  const textContent = elementData.text || elementData.content || 'Texte d\'exemple';
  
  return (
    <div
      className={className}
      style={{
        ...style,
        ...visualStyles,
        display: 'flex',
        alignItems: 'center',
        justifyContent: elementData.textAlign === 'center' ? 'center' : 
                      elementData.textAlign === 'right' ? 'flex-end' : 'flex-start',
        padding: `${(elementData.padding || 4) * scale}px`,
        wordWrap: 'break-word',
        overflow: 'hidden'
      }}
    >
      {textContent}
    </div>
  );
};

export const TextRenderer = createBaseRenderer(TextRendererBase);

/**
 * Renderer pour les rectangles/formes
 * Version 2.0 - Optimisé et standardisé
 */
const RectangleRendererBase = ({ element, previewData, style, className, scale }) => {
  const elementData = useElementData(element, previewData);
  const visualStyles = useElementStyles(elementData, scale);
  
  return (
    <div
      className={className}
      style={{
        ...style,
        ...visualStyles
      }}
    />
  );
};

export const RectangleRenderer = createBaseRenderer(RectangleRendererBase);

/**
 * Renderer pour les images
 * Version 2.0 - Optimisé et standardisé
 */
const ImageRendererBase = ({ element, previewData, style, className, scale }) => {
  const elementData = useElementData(element, previewData);
  const visualStyles = useElementStyles(elementData, scale);
  
  const imageSrc = elementData.src || elementData.url || elementData.image;
  const altText = elementData.alt || elementData.title || 'Image';
  
  return (
    <div
      className={className}
      style={{
        ...style,
        ...visualStyles,
        overflow: 'hidden'
      }}
    >
      {imageSrc ? (
        <img
          src={imageSrc}
          alt={altText}
          style={{
            width: '100%',
            height: '100%',
            objectFit: elementData.objectFit || 'cover',
            objectPosition: elementData.objectPosition || 'center'
          }}
          onError={(e) => {
            // Fallback en cas d'erreur de chargement
            e.target.style.display = 'none';
            e.target.parentElement.innerHTML = `
              <div style="
                width: 100%; 
                height: 100%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                background: #f5f5f5; 
                color: #999; 
                font-size: ${12 * scale}px;
                border: 2px dashed #ddd;
              ">
                📷 Image non trouvée
              </div>
            `;
          }}
        />
      ) : (
        <div
          style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            background: '#f5f5f5',
            color: '#999',
            fontSize: `${12 * scale}px`,
            border: '2px dashed #ddd'
          }}
        >
          📷 Aucune image
        </div>
      )}
    </div>
  );
};

export const ImageRenderer = createBaseRenderer(ImageRendererBase);

/**
 * Renderer pour les tableaux
 * Version 2.0 - Optimisé et standardisé
 */
const TableRendererBase = ({ element, previewData, style, className, scale }) => {
  const elementData = useElementData(element, previewData);
  const visualStyles = useElementStyles(elementData, scale);
  
  // Données du tableau avec fallback
  const tableData = elementData.data || elementData.rows || [
    ['En-tête 1', 'En-tête 2'],
    ['Cellule 1', 'Cellule 2'],
    ['Cellule 3', 'Cellule 4']
  ];
  
  const cellStyle = {
    border: `1px solid ${elementData.borderColor || '#ddd'}`,
    padding: `${(elementData.cellPadding || 4) * scale}px`,
    fontSize: `${(elementData.fontSize || 12) * scale}px`,
    textAlign: elementData.textAlign || 'left'
  };
  
  return (
    <div
      className={className}
      style={{
        ...style,
        ...visualStyles,
        overflow: 'hidden'
      }}
    >
      <table style={{ width: '100%', height: '100%', borderCollapse: 'collapse' }}>
        <tbody>
          {tableData.map((row, rowIndex) => (
            <tr key={rowIndex}>
              {row.map((cell, cellIndex) => (
                <td key={cellIndex} style={cellStyle}>
                  {cell}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export const TableRenderer = createBaseRenderer(TableRendererBase);

/**
 * Renderer générique pour éléments non reconnus
 * Version 2.0 - Optimisé et standardisé
 */
const UnknownRendererBase = ({ element, style, className, scale }) => {
  return (
    <div
      className={className}
      style={{
        ...style,
        backgroundColor: '#f0f0f0',
        border: '2px dashed #ccc',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: `${12 * scale}px`,
        color: '#666'
      }}
    >
      {element.type || 'Élément inconnu'}
    </div>
  );
};

export const UnknownRenderer = createBaseRenderer(UnknownRendererBase);

/**
 * Factory pour créer des renderers personnalisés
 * Permet d'étendre facilement le système
 */
export function createCustomRenderer(renderFunction) {
  const CustomRendererBase = (props) => {
    const elementData = useElementData(props.element, props.previewData);
    const visualStyles = useElementStyles(elementData, props.scale);
    
    return renderFunction({
      ...props,
      elementData,
      visualStyles
    });
  };
  
  return createBaseRenderer(CustomRendererBase);
}

/**
 * Renderer principal qui route vers le bon renderer selon le type
 * Version 2.0 - Architecture modulaire
 */
export function UniversalRenderer(props) {
  const { element } = props;
  
  // Mapping des types vers les renderers
  const rendererMap = {
    'text': TextRenderer,
    'rectangle': RectangleRenderer,
    'image': ImageRenderer,
    'table': TableRenderer,
    
    // Aliases courants
    'rect': RectangleRenderer,
    'img': ImageRenderer,
    'txt': TextRenderer,
    
    // Fallback
    'default': UnknownRenderer
  };
  
  // Sélection du renderer approprié
  const RendererComponent = rendererMap[element.type] || rendererMap.default;
  
  return <RendererComponent {...props} />;
}

// Export des utilities
export {
  createBaseRenderer,
  useElementData,
  useElementStyles
};