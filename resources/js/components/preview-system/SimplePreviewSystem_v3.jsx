import React from 'react';

/**
 * SYSTÈME D'APERÇU COMPLETEMENT RECONSTRUIT - VERSION 3.0
 * Architecture ultra-simple et robuste pour éviter tous les problèmes
 */

// =============================================================================
// 1. SYSTÈME DE POSITIONNEMENT ULTRA-SIMPLE
// =============================================================================

/**
 * Hook pour calculer les dimensions et l'échelle de l'aperçu
 */
export function usePreviewScaling(templateWidth, templateHeight, containerWidth = 800, containerHeight = 600) {
  return React.useMemo(() => {
    // Calcul de l'échelle pour que le template tienne dans le conteneur
    const scaleX = containerWidth / templateWidth;
    const scaleY = containerHeight / templateHeight;
    const scale = Math.min(scaleX, scaleY, 1); // Ne pas agrandir

    return {
      scale,
      displayWidth: templateWidth * scale,
      displayHeight: templateHeight * scale,
      templateWidth,
      templateHeight
    };
  }, [templateWidth, templateHeight, containerWidth, containerHeight]);
}

/**
 * Composant de base pour positionner un élément
 */
export function PositionedElement({ element, scale, children, className = '' }) {
  const style = {
    position: 'absolute',
    left: element.x * scale,
    top: element.y * scale,
    width: element.width * scale,
    height: element.height * scale,
    // Styles de base pour éviter les conflits
    boxSizing: 'border-box',
    overflow: 'hidden'
  };

  return (
    <div
      className={`preview-element ${className}`}
      style={style}
      data-element-id={element.id}
      data-element-type={element.type}
    >
      {children}
    </div>
  );
}

// =============================================================================
// 2. RENDERERS ULTRA-SIMPLES POUR CHAQUE TYPE D'ÉLÉMENT
// =============================================================================

/**
 * Renderer pour le texte - Version ultra-simple
 */
export function SimpleTextRenderer({ element, scale }) {
  const fontSize = (element.fontSize || 14) * scale;
  const lineHeight = element.lineHeight || 1.2;

  return (
    <PositionedElement element={element} scale={scale} className="text-element">
      <div
        style={{
          width: '100%',
          height: '100%',
          fontSize: `${fontSize}px`,
          fontFamily: element.fontFamily || 'Arial',
          fontWeight: element.fontWeight || 'normal',
          fontStyle: element.fontStyle || 'normal',
          color: element.color || '#000000',
          textAlign: element.textAlign || 'left',
          lineHeight: lineHeight,
          backgroundColor: element.backgroundColor || 'transparent',
          border: element.borderWidth ? `${element.borderWidth * scale}px solid ${element.borderColor || '#000'}` : 'none',
          borderRadius: `${(element.borderRadius || 0) * scale}px`,
          padding: `${(element.padding || 0) * scale}px`,
          display: 'flex',
          alignItems: element.textAlign === 'center' ? 'center' :
                     element.textAlign === 'right' ? 'flex-end' : 'flex-start',
          justifyContent: 'flex-start',
          wordWrap: 'break-word',
          overflowWrap: 'break-word',
          whiteSpace: 'pre-wrap'
        }}
      >
        {element.text || element.content || 'Texte'}
      </div>
    </PositionedElement>
  );
}

/**
 * Renderer pour les rectangles/formes
 */
export function SimpleRectangleRenderer({ element, scale }) {
  return (
    <PositionedElement element={element} scale={scale} className="rectangle-element">
      <div
        style={{
          width: '100%',
          height: '100%',
          backgroundColor: element.backgroundColor || '#cccccc',
          border: element.borderWidth ? `${element.borderWidth * scale}px solid ${element.borderColor || '#000'}` : 'none',
          borderRadius: `${(element.borderRadius || 0) * scale}px`,
          opacity: element.opacity ?? 1
        }}
      />
    </PositionedElement>
  );
}

/**
 * Renderer pour les images
 */
export function SimpleImageRenderer({ element, scale }) {
  const [imageLoaded, setImageLoaded] = React.useState(false);
  const [imageError, setImageError] = React.useState(false);

  return (
    <PositionedElement element={element} scale={scale} className="image-element">
      <div
        style={{
          width: '100%',
          height: '100%',
          backgroundColor: '#f0f0f0',
          border: element.borderWidth ? `${element.borderWidth * scale}px solid ${element.borderColor || '#000'}` : 'none',
          borderRadius: `${(element.borderRadius || 0) * scale}px`,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          overflow: 'hidden'
        }}
      >
        {element.src || element.url ? (
          <>
            {!imageError && (
              <img
                src={element.src || element.url}
                alt={element.alt || 'Image'}
                style={{
                  width: '100%',
                  height: '100%',
                  objectFit: element.objectFit || 'cover',
                  objectPosition: element.objectPosition || 'center',
                  opacity: imageLoaded ? 1 : 0,
                  transition: 'opacity 0.2s ease'
                }}
                onLoad={() => setImageLoaded(true)}
                onError={() => setImageError(true)}
              />
            )}
            {imageError && (
              <div style={{
                color: '#666',
                fontSize: `${12 * scale}px`,
                textAlign: 'center'
              }}>
                🖼️ Image introuvable
              </div>
            )}
          </>
        ) : (
          <div style={{
            color: '#999',
            fontSize: `${12 * scale}px`,
            textAlign: 'center'
          }}>
            📷 Aucune image
          </div>
        )}
      </div>
    </PositionedElement>
  );
}

/**
 * Renderer pour les tableaux
 */
export function SimpleTableRenderer({ element, scale }) {
  const tableData = element.data || element.rows || [
    ['Colonne 1', 'Colonne 2'],
    ['Donnée 1', 'Donnée 2']
  ];

  const cellStyle = {
    border: `1px solid ${element.borderColor || '#ddd'}`,
    padding: `${(element.cellPadding || 4) * scale}px`,
    fontSize: `${(element.fontSize || 12) * scale}px`,
    textAlign: element.textAlign || 'left'
  };

  return (
    <PositionedElement element={element} scale={scale} className="table-element">
      <div
        style={{
          width: '100%',
          height: '100%',
          backgroundColor: element.backgroundColor || 'transparent',
          border: element.borderWidth ? `${element.borderWidth * scale}px solid ${element.borderColor || '#000'}` : 'none',
          borderRadius: `${(element.borderRadius || 0) * scale}px`,
          padding: `${(element.padding || 0) * scale}px`,
          overflow: 'hidden'
        }}
      >
        <table style={{
          width: '100%',
          height: '100%',
          borderCollapse: 'collapse',
          fontSize: 'inherit',
          color: 'inherit'
        }}>
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
    </PositionedElement>
  );
}

/**
 * Renderer générique pour éléments inconnus
 */
export function SimpleUnknownRenderer({ element, scale }) {
  return (
    <PositionedElement element={element} scale={scale} className="unknown-element">
      <div
        style={{
          width: '100%',
          height: '100%',
          backgroundColor: '#ffeaa7',
          border: '2px dashed #d63031',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: `${12 * scale}px`,
          color: '#d63031',
          textAlign: 'center'
        }}
      >
        ❓ {element.type || 'Inconnu'}
      </div>
    </PositionedElement>
  );
}

// =============================================================================
// 3. SYSTÈME DE RENDU PRINCIPAL ULTRA-SIMPLE
// =============================================================================

/**
 * Renderer universel qui route vers le bon renderer selon le type
 */
export function SimpleElementRenderer({ element, scale }) {
  // Logs de débogage pour voir exactement ce qui se passe
  console.log('🎨 Rendering element:', {
    id: element.id,
    type: element.type,
    x: element.x,
    y: element.y,
    width: element.width,
    height: element.height,
    scale: scale,
    displayX: element.x * scale,
    displayY: element.y * scale,
    displayWidth: element.width * scale,
    displayHeight: element.height * scale
  });

  switch (element.type) {
    case 'text':
      return <SimpleTextRenderer element={element} scale={scale} />;
    case 'rectangle':
    case 'rect':
      return <SimpleRectangleRenderer element={element} scale={scale} />;
    case 'image':
    case 'img':
      return <SimpleImageRenderer element={element} scale={scale} />;
    case 'table':
      return <SimpleTableRenderer element={element} scale={scale} />;
    default:
      return <SimpleUnknownRenderer element={element} scale={scale} />;
  }
}

// =============================================================================
// 4. COMPOSANT D'APERÇU PRINCIPAL ULTRA-SIMPLE
// =============================================================================

/**
 * Composant d'aperçu principal - Version 3.0 ultra-simple
 */
export function SimpleCanvasPreview({
  elements = [],
  templateWidth = 595,
  templateHeight = 842,
  containerWidth = 800,
  containerHeight = 600,
  showDebug = false
}) {
  const { scale, displayWidth, displayHeight } = usePreviewScaling(
    templateWidth,
    templateHeight,
    containerWidth,
    containerHeight
  );

  console.log('📐 Canvas Preview Config:', {
    templateWidth,
    templateHeight,
    containerWidth,
    containerHeight,
    scale,
    displayWidth,
    displayHeight,
    elementsCount: elements.length
  });

  return (
    <div style={{
      width: '100%',
      height: '100%',
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      padding: '20px',
      backgroundColor: '#f8f9fa'
    }}>
      {/* Informations de débogage */}
      {showDebug && (
        <div style={{
          marginBottom: '20px',
          padding: '12px 20px',
          backgroundColor: 'white',
          borderRadius: '8px',
          border: '1px solid #e9ecef',
          fontSize: '14px',
          color: '#495057',
          textAlign: 'center'
        }}>
          <div style={{ fontWeight: '600', marginBottom: '8px' }}>🔍 Debug Info</div>
          <div>Template: {templateWidth}×{templateHeight} • Scale: {(scale * 100).toFixed(1)}% • Elements: {elements.length}</div>
          <div>Display: {displayWidth.toFixed(0)}×{displayHeight.toFixed(0)}px</div>
        </div>
      )}

      {/* Conteneur du canvas */}
      <div style={{
        position: 'relative',
        width: displayWidth,
        height: displayHeight,
        backgroundColor: 'white',
        border: '1px solid #dee2e6',
        borderRadius: '4px',
        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
        overflow: 'hidden'
      }}>
        {/* Rendu de tous les éléments */}
        {elements.map(element => (
          <SimpleElementRenderer
            key={element.id}
            element={element}
            scale={scale}
          />
        ))}

        {/* Message si aucun élément */}
        {elements.length === 0 && (
          <div style={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            textAlign: 'center',
            color: '#6c757d',
            fontSize: '16px'
          }}>
            <div style={{ fontSize: '48px', marginBottom: '16px' }}>📄</div>
            <div>Aucun élément dans l'aperçu</div>
          </div>
        )}

        {/* Grille de débogage */}
        {showDebug && (
          <svg
            style={{
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              height: '100%',
              pointerEvents: 'none',
              opacity: 0.1
            }}
          >
            <defs>
              <pattern
                id="debug-grid"
                width="20"
                height="20"
                patternUnits="userSpaceOnUse"
              >
                <path
                  d="M 20 0 L 0 0 0 20"
                  fill="none"
                  stroke="#ff6b6b"
                  strokeWidth="1"
                />
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#debug-grid)" />
          </svg>
        )}
      </div>

      {/* Informations du template */}
      <div style={{
        marginTop: '20px',
        padding: '12px 20px',
        backgroundColor: 'white',
        borderRadius: '8px',
        border: '1px solid #e9ecef',
        fontSize: '13px',
        color: '#6c757d',
        textAlign: 'center'
      }}>
        <div>📏 Dimensions: {templateWidth} × {templateHeight} points</div>
        <div>📐 Échelle: {(scale * 100).toFixed(1)}% • Affichage: {displayWidth.toFixed(0)} × {displayHeight.toFixed(0)} px</div>
      </div>
    </div>
  );
}

// =============================================================================
// 5. MODAL D'APERÇU ULTRA-SIMPLE
// =============================================================================

/**
 * Modal d'aperçu ultra-simple
 */
export function SimplePreviewModal({
  isOpen,
  onClose,
  elements = [],
  templateWidth = 595,
  templateHeight = 842,
  title = "Aperçu PDF"
}) {
  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      backgroundColor: 'rgba(0, 0, 0, 0.75)',
      backdropFilter: 'blur(4px)',
      zIndex: 10000,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      padding: '20px'
    }}>
      <div style={{
        width: '95%',
        height: '95%',
        maxWidth: '1200px',
        backgroundColor: 'white',
        borderRadius: '12px',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.3)',
        display: 'flex',
        flexDirection: 'column',
        overflow: 'hidden'
      }}>
        {/* Header */}
        <div style={{
          padding: '20px 24px',
          borderBottom: '1px solid #e9ecef',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          backgroundColor: '#f8f9fa'
        }}>
          <h3 style={{
            margin: 0,
            fontSize: '18px',
            fontWeight: '600',
            color: '#1f2937'
          }}>
            {title}
          </h3>
          <button
            onClick={onClose}
            style={{
              padding: '8px',
              border: 'none',
              borderRadius: '6px',
              backgroundColor: '#ef4444',
              color: 'white',
              cursor: 'pointer',
              fontSize: '16px',
              width: '36px',
              height: '36px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          >
            ✕
          </button>
        </div>

        {/* Contenu */}
        <div style={{
          flex: 1,
          overflow: 'auto'
        }}>
          <SimpleCanvasPreview
            elements={elements}
            templateWidth={templateWidth}
            templateHeight={templateHeight}
            showDebug={true}
          />
        </div>
      </div>
    </div>
  );
}

// =============================================================================
// 6. COMPOSANT DE TEST ULTRA-SIMPLE
// =============================================================================

/**
 * Composant de test avec des données d'exemple
 */
export function SimplePreviewTest() {
  const [showPreview, setShowPreview] = React.useState(false);

  // Données de test ultra-simples
  const testElements = [
    {
      id: 1,
      type: 'text',
      x: 50,
      y: 50,
      width: 200,
      height: 40,
      text: 'Titre du document',
      fontSize: 18,
      fontWeight: 'bold',
      color: '#1f2937'
    },
    {
      id: 2,
      type: 'rectangle',
      x: 50,
      y: 100,
      width: 300,
      height: 100,
      backgroundColor: '#3b82f6',
      borderRadius: 8
    },
    {
      id: 3,
      type: 'text',
      x: 70,
      y: 120,
      width: 260,
      height: 60,
      text: 'Contenu dans le rectangle bleu',
      fontSize: 14,
      color: 'white',
      textAlign: 'center'
    },
    {
      id: 4,
      type: 'image',
      x: 400,
      y: 50,
      width: 120,
      height: 120,
      src: 'https://via.placeholder.com/120x120/6366f1/white?text=LOGO',
      borderRadius: 8
    },
    {
      id: 5,
      type: 'table',
      x: 50,
      y: 220,
      width: 400,
      height: 120,
      data: [
        ['Produit', 'Quantité', 'Prix'],
        ['Article A', '2', '29,99 €'],
        ['Article B', '1', '15,50 €'],
        ['Total', '3', '45,49 €']
      ],
      fontSize: 12
    },
    {
      id: 6,
      type: 'rectangle',
      x: 100,
      y: 360,
      width: 150,
      height: 50,
      backgroundColor: '#10b981',
      borderRadius: 25
    },
    {
      id: 7,
      type: 'text',
      x: 110,
      y: 370,
      width: 130,
      height: 30,
      text: 'Élément vert',
      fontSize: 14,
      color: 'white',
      textAlign: 'center'
    }
  ];

  return (
    <div style={{ padding: '20px' }}>
      <h2>🧪 Test du système d'aperçu ultra-simple v3.0</h2>
      <p>Cliquez pour voir l'aperçu avec des éléments de test parfaitement positionnés.</p>

      <button
        onClick={() => setShowPreview(true)}
        style={{
          padding: '12px 24px',
          backgroundColor: '#3b82f6',
          color: 'white',
          border: 'none',
          borderRadius: '8px',
          fontSize: '16px',
          cursor: 'pointer',
          margin: '20px 0'
        }}
      >
        🔍 Ouvrir l'aperçu de test
      </button>

      <div style={{ marginTop: '20px', fontSize: '14px', color: '#666' }}>
        <h3>Éléments de test inclus :</h3>
        <ul>
          <li>✅ Texte avec différentes tailles et couleurs</li>
          <li>✅ Rectangles avec coins arrondis</li>
          <li>✅ Image avec placeholder</li>
          <li>✅ Tableau avec données</li>
          <li>✅ Positionnement précis à des coordonnées spécifiques</li>
          <li>✅ Échelle automatique pour s'adapter à la fenêtre</li>
        </ul>
      </div>

      <SimplePreviewModal
        isOpen={showPreview}
        onClose={() => setShowPreview(false)}
        elements={testElements}
        templateWidth={595}
        templateHeight={842}
        title="Aperçu de test - Version 3.0"
      />
    </div>
  );
}

export default {
  SimpleCanvasPreview,
  SimplePreviewModal,
  SimplePreviewTest,
  SimpleElementRenderer,
  usePreviewScaling
};