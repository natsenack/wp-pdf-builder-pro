import { useState, useEffect, useCallback } from 'react';

// Nouveau système d'aperçu côté serveur avec TCPDF

const PreviewModal = ({
  isOpen,
  onClose,
  elements = [],
  canvasWidth = 595,
  canvasHeight = 842,
  zoom = 1,
  ajaxurl,
  pdfBuilderNonce,
  onOpenPDFModal = null,
  useServerPreview = false
}) => {
  const [previewData, setPreviewData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  // Fonction pour nettoyer les éléments avant sérialisation JSON
  const cleanElementsForJSON = (elements) => {
    if (!Array.isArray(elements)) {
      throw new Error('Les éléments doivent être un tableau');
    }

    return elements.map(element => {
      if (!element || typeof element !== 'object') {
        throw new Error('Chaque élément doit être un objet valide');
      }

      // Créer une copie profonde de l'élément
      const cleaned = JSON.parse(JSON.stringify(element));

      // S'assurer que les propriétés numériques sont des nombres
      const numericProps = ['x', 'y', 'width', 'height', 'fontSize', 'padding', 'zIndex', 'borderWidth'];
      numericProps.forEach(prop => {
        if (cleaned[prop] !== undefined) {
          const numValue = parseFloat(cleaned[prop]);
          if (isNaN(numValue)) {
            throw new Error(`Propriété ${prop} doit être un nombre valide`);
          }
          cleaned[prop] = numValue;
        }
      });

      // Valider les propriétés requises
      if (typeof cleaned.type !== 'string') {
        throw new Error('Chaque élément doit avoir un type string');
      }

      // Nettoyer les propriétés potentiellement problématiques
      delete cleaned.tempId; // Supprimer les IDs temporaires si présents
      delete cleaned.isDragging; // Supprimer les états d'interaction
      delete cleaned.isResizing; // Supprimer les états d'interaction

      return cleaned;
    });
  };

  // Fonction de validation des éléments avant envoi
  const validateElementsBeforeSend = (elements) => {
    try {
      const cleanedElements = cleanElementsForJSON(elements);
      const jsonString = JSON.stringify(cleanedElements);
      
      // Vérifier que le JSON est valide
      JSON.parse(jsonString);
      
      // Vérifier la longueur raisonnable
      if (jsonString.length > 10000000) { // 10MB max
        throw new Error('JSON trop volumineux');
      }
      
      return { success: true, jsonString, cleanedElements };
    } catch (error) {
      console.error('Client-side validation failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Fonction pour rendre le contenu du canvas en HTML
  const renderCanvasContent = useCallback((elements) => {
    // Réduire les logs pour éviter la boucle infinie - n'afficher que les erreurs importantes
    if (!elements || elements.length === 0) {
      return <div style={{ padding: '20px', textAlign: 'center', color: '#666' }}>Aucun élément à afficher</div>;
    }

    // Vérifier que zoom est valide
    const validZoom = typeof zoom === 'number' && !isNaN(zoom) && zoom > 0 ? zoom : 1;

    return (
      <div
        style={{
          position: 'relative',
          width: canvasWidth * validZoom,
          height: canvasHeight * validZoom,
          backgroundColor: 'white',
          border: '1px solid #e2e8f0',
          borderRadius: '4px',
          overflow: 'hidden',
          margin: '0 auto'
        }}
      >
        {elements.map((element, index) => {
          // Vérifier que les propriétés essentielles existent
          if (typeof element.x !== 'number' || typeof element.y !== 'number' ||
              typeof element.width !== 'number' || typeof element.height !== 'number') {
            console.error('❌ Element missing required properties:', element);
            return null;
          }

          const elementPadding = element.padding || 0;
          
          // Styles spéciaux pour certains types d'éléments
          let baseStyle = {
            position: 'absolute',
            left: (element.x + elementPadding) * validZoom,
            top: (element.y + elementPadding) * validZoom,
            width: Math.max(1, (element.width - (elementPadding * 2))) * validZoom,
            height: Math.max(1, (element.height - (elementPadding * 2))) * validZoom,
            zIndex: element.zIndex || index + 1
          };

          // Pour les lignes, utiliser toute la largeur du canvas
          if (element.type === 'line') {
            baseStyle = {
              ...baseStyle,
              left: 0,
              width: canvasWidth * validZoom
            };
          }

          return (
            <div key={index} style={baseStyle}>
              {renderSpecialElement(element, validZoom)}
            </div>
          );
        })}
      </div>
    );
  }, [zoom, canvasWidth, canvasHeight]);

  // Fonction pour rendre un élément spécial (basée sur CanvasElement.jsx)
  const renderSpecialElement = useCallback((element, zoom) => {
    // Réduire les logs - n'afficher que les erreurs importantes
    switch (element.type) {
      case 'text':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              fontSize: (element.fontSize || 16) * zoom,
              color: element.color || '#000000',
              fontWeight: element.fontWeight === 'bold' ? 'bold' : 'normal',
              fontStyle: element.fontStyle === 'italic' ? 'italic' : 'normal',
              textDecoration: element.textDecoration || 'none',
              textAlign: element.textAlign || 'left',
              lineHeight: element.lineHeight || '1.2',
              whiteSpace: 'pre-wrap',
              overflow: 'hidden',
              padding: `${4 * zoom}px`,
              boxSizing: 'border-box'
            }}
          >
            {element.content || element.text || 'Texte'}
          </div>
        );

      case 'rectangle':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              backgroundColor: element.fillColor || 'transparent',
              border: element.borderWidth
                ? `${element.borderWidth * zoom}px ${element.borderStyle || 'solid'} ${element.borderColor || '#000000'}`
                : 'none',
              borderRadius: (element.borderRadius || 0) * zoom
            }}
          />
        );

      case 'image':
        return (
          <img
            src={element.src || element.imageUrl || ''}
            alt={element.alt || 'Image'}
            style={{
              width: '100%',
              height: '100%',
              objectFit: element.objectFit || 'cover',
              borderRadius: (element.borderRadius || 0) * zoom
            }}
            onError={(e) => {
              e.target.style.display = 'none';
            }}
          />
        );

      case 'line':
        return (
          <div
            style={{
              width: '100%',
              height: (element.lineWidth || element.strokeWidth || 1) * zoom,
              borderTop: `${(element.lineWidth || element.strokeWidth || 1) * zoom}px solid ${element.lineColor || element.strokeColor || '#000000'}`
            }}
          />
        );

      case 'divider':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              backgroundColor: element.color || element.fillColor || '#cccccc',
              height: `${(element.thickness || element.height || 2) * zoom}px`,
              margin: `${(element.margin || 10) * zoom}px 0`,
              borderRadius: (element.borderRadius || 0) * zoom
            }}
          />
        );

      case 'product_table':
        // Rendu dynamique du tableau de produits utilisant les propriétés de l'élément
        const getTableStyles = (tableStyle = 'default') => {
          const baseStyles = {
            default: {
              headerBg: '#f8fafc',
              headerBorder: '#e2e8f0',
              rowBorder: '#f1f5f9',
              altRowBg: '#fafbfc',
              borderWidth: 1,
              headerTextColor: '#334155',
              rowTextColor: '#334155',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 1px 3px rgba(0, 0, 0, 0.1)',
              borderRadius: '4px'
            },
            classic: {
              headerBg: '#1e293b',
              headerBorder: '#334155',
              rowBorder: '#334155',
              altRowBg: '#ffffff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#1e293b',
              headerFontWeight: '700',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
              borderRadius: '0px'
            },
            striped: {
              headerBg: '#3b82f6',
              headerBorder: '#2563eb',
              rowBorder: '#e2e8f0',
              altRowBg: '#f8fafc',
              borderWidth: 1,
              headerTextColor: '#ffffff',
              rowTextColor: '#334155',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 1px 4px rgba(59, 130, 246, 0.2)',
              borderRadius: '6px'
            },
            bordered: {
              headerBg: '#ffffff',
              headerBorder: '#374151',
              rowBorder: '#d1d5db',
              altRowBg: '#ffffff',
              borderWidth: 2,
              headerTextColor: '#111827',
              rowTextColor: '#111827',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 12px rgba(0, 0, 0, 0.1), inset 0 0 0 1px #e5e7eb',
              borderRadius: '8px'
            },
            minimal: {
              headerBg: '#ffffff',
              headerBorder: '#d1d5db',
              rowBorder: '#f3f4f6',
              altRowBg: '#ffffff',
              borderWidth: 0.5,
              headerTextColor: '#6b7280',
              rowTextColor: '#6b7280',
              headerFontWeight: '500',
              headerFontSize: '10px',
              rowFontSize: '9px',
              shadow: 'none',
              borderRadius: '0px'
            },
            modern: {
              gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              headerBorder: '#5b21b6',
              rowBorder: '#e9d5ff',
              altRowBg: '#faf5ff',
              borderWidth: 1,
              headerTextColor: '#ffffff',
              rowTextColor: '#6b21a8',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 20px rgba(102, 126, 234, 0.25)',
              borderRadius: '8px'
            },
            // Nouveaux styles colorés
            blue_ocean: {
              gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
              headerBorder: '#1e40af',
              rowBorder: '#dbeafe',
              altRowBg: '#eff6ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#1e3a8a',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
              borderRadius: '6px'
            },
            emerald_forest: {
              gradient: 'linear-gradient(135deg, #064e3b 0%, #10b981 100%)',
              headerBorder: '#065f46',
              rowBorder: '#d1fae5',
              altRowBg: '#ecfdf5',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#064e3b',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(16, 185, 129, 0.3)',
              borderRadius: '6px'
            },
            sunset_orange: {
              gradient: 'linear-gradient(135deg, #9a3412 0%, #f97316 100%)',
              headerBorder: '#c2410c',
              rowBorder: '#fed7aa',
              altRowBg: '#fff7ed',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#9a3412',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(249, 115, 22, 0.3)',
              borderRadius: '6px'
            },
            royal_purple: {
              gradient: 'linear-gradient(135deg, #581c87 0%, #a855f7 100%)',
              headerBorder: '#7c3aed',
              rowBorder: '#e9d5ff',
              altRowBg: '#faf5ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#581c87',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
              borderRadius: '6px'
            },
            rose_pink: {
              gradient: 'linear-gradient(135deg, #be185d 0%, #f472b6 100%)',
              headerBorder: '#db2777',
              rowBorder: '#fce7f3',
              altRowBg: '#fdf2f8',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#be185d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(244, 114, 182, 0.3)',
              borderRadius: '6px'
            },
            teal_aqua: {
              gradient: 'linear-gradient(135deg, #0f766e 0%, #14b8a6 100%)',
              headerBorder: '#0d9488',
              rowBorder: '#ccfbf1',
              altRowBg: '#f0fdfa',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#0f766e',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
              borderRadius: '6px'
            },
            crimson_red: {
              gradient: 'linear-gradient(135deg, #991b1b 0%, #ef4444 100%)',
              headerBorder: '#dc2626',
              rowBorder: '#fecaca',
              altRowBg: '#fef2f2',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#991b1b',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(239, 68, 68, 0.3)',
              borderRadius: '6px'
            },
            amber_gold: {
              gradient: 'linear-gradient(135deg, #92400e 0%, #f59e0b 100%)',
              headerBorder: '#d97706',
              rowBorder: '#fef3c7',
              altRowBg: '#fffbeb',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#92400e',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(245, 158, 11, 0.3)',
              borderRadius: '6px'
            },
            indigo_night: {
              gradient: 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)',
              headerBorder: '#4338ca',
              rowBorder: '#e0e7ff',
              altRowBg: '#eef2ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#312e81',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(99, 102, 241, 0.3)',
              borderRadius: '6px'
            },
            slate_gray: {
              gradient: 'linear-gradient(135deg, #374151 0%, #6b7280 100%)',
              headerBorder: '#4b5563',
              rowBorder: '#f3f4f6',
              altRowBg: '#f9fafb',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#374151',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(107, 114, 128, 0.3)',
              borderRadius: '6px'
            },
            coral_sunset: {
              gradient: 'linear-gradient(135deg, #c2410c 0%, #fb7185 100%)',
              headerBorder: '#ea580c',
              rowBorder: '#fed7d7',
              altRowBg: '#fef7f7',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#c2410c',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
              borderRadius: '6px'
            },
            mint_green: {
              gradient: 'linear-gradient(135deg, #065f46 0%, #34d399 100%)',
              headerBorder: '#047857',
              rowBorder: '#d1fae5',
              altRowBg: '#ecfdf5',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#065f46',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(52, 211, 153, 0.3)',
              borderRadius: '6px'
            },
            violet_dream: {
              gradient: 'linear-gradient(135deg, #6d28d9 0%, #c084fc 100%)',
              headerBorder: '#8b5cf6',
              rowBorder: '#ede9fe',
              altRowBg: '#f5f3ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#6d28d9',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(192, 132, 252, 0.3)',
              borderRadius: '6px'
            },
            sky_blue: {
              gradient: 'linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%)',
              headerBorder: '#0284c7',
              rowBorder: '#bae6fd',
              altRowBg: '#f0f9ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#0369a1',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(14, 165, 233, 0.3)',
              borderRadius: '6px'
            },
            forest_green: {
              gradient: 'linear-gradient(135deg, #14532d 0%, #22c55e 100%)',
              headerBorder: '#15803d',
              rowBorder: '#bbf7d0',
              altRowBg: '#f0fdf4',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#14532d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(34, 197, 94, 0.3)',
              borderRadius: '6px'
            },
            ruby_red: {
              gradient: 'linear-gradient(135deg, #b91c1c 0%, #f87171 100%)',
              headerBorder: '#dc2626',
              rowBorder: '#fecaca',
              altRowBg: '#fef2f2',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#b91c1b',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(248, 113, 113, 0.3)',
              borderRadius: '6px'
            },
            golden_yellow: {
              gradient: 'linear-gradient(135deg, #a16207 0%, #eab308 100%)',
              headerBorder: '#ca8a04',
              rowBorder: '#fef08a',
              altRowBg: '#fefce8',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#a16207',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(234, 179, 8, 0.3)',
              borderRadius: '6px'
            },
            navy_blue: {
              gradient: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
              headerBorder: '#1e40af',
              rowBorder: '#dbeafe',
              altRowBg: '#eff6ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#1e3a8a',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(59, 130, 246, 0.3)',
              borderRadius: '6px'
            },
            burgundy_wine: {
              gradient: 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
              headerBorder: '#991b1b',
              rowBorder: '#fecaca',
              altRowBg: '#fef2f2',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#7f1d1d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(220, 38, 38, 0.3)',
              borderRadius: '6px'
            },
            lavender_purple: {
              gradient: 'linear-gradient(135deg, #7c2d12 0%, #a855f7 100%)',
              headerBorder: '#9333ea',
              rowBorder: '#e9d5ff',
              altRowBg: '#faf5ff',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#7c2d12',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(168, 85, 247, 0.3)',
              borderRadius: '6px'
            },
            ocean_teal: {
              gradient: 'linear-gradient(135deg, #134e4a 0%, #14b8a6 100%)',
              headerBorder: '#0f766e',
              rowBorder: '#ccfbf1',
              altRowBg: '#f0fdfa',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#134e4a',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(20, 184, 166, 0.3)',
              borderRadius: '6px'
            },
            cherry_blossom: {
              gradient: 'linear-gradient(135deg, #be185d 0%, #fb7185 100%)',
              headerBorder: '#db2777',
              rowBorder: '#fce7f3',
              altRowBg: '#fdf2f8',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#be185d',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(251, 113, 133, 0.3)',
              borderRadius: '6px'
            },
            autumn_orange: {
              gradient: 'linear-gradient(135deg, #9a3412 0%, #fb923c 100%)',
              headerBorder: '#ea580c',
              rowBorder: '#fed7aa',
              altRowBg: '#fff7ed',
              borderWidth: 1.5,
              headerTextColor: '#ffffff',
              rowTextColor: '#9a3412',
              headerFontWeight: '600',
              headerFontSize: '11px',
              rowFontSize: '10px',
              shadow: '0 4px 16px rgba(251, 146, 60, 0.3)',
              borderRadius: '6px'
            }
          };
          return baseStyles[tableStyle] || baseStyles.default;
        };

        const tableStyles = getTableStyles(element.tableStyle);
        const showHeaders = element.showHeaders !== false;
        const showBorders = element.showBorders !== false;
        const headers = element.headers || ['Produit', 'Qté', 'Prix'];

        // Fonction pour obtenir l'en-tête d'une colonne
        const getColumnHeader = (columnType) => {
          const defaultHeaders = {
            image: 'Img',
            name: headers[0] || 'Produit',
            sku: 'SKU',
            quantity: headers[1] || 'Qté',
            price: headers[2] || 'Prix',
            total: 'Total'
          };
          return defaultHeaders[columnType] || columnType;
        };

        // Données d'exemple pour l'aperçu (cohérentes avec le canvas)
        const products = [
          { name: 'Produit A - Description du produit', sku: 'SKU001', quantity: 2, price: 19.99, total: 39.98 },
          { name: 'Produit B - Un autre article', sku: 'SKU002', quantity: 1, price: 29.99, total: 29.99 }
        ];

        // Calcul des totaux
        const subtotal = products.reduce((sum, product) => sum + product.total, 0);
        const shipping = element.showShipping ? 5.00 : 0;
        const tax = element.showTaxes ? 2.25 : 0;
        const discount = element.showDiscount ? -5.00 : 0;
        const total = subtotal + shipping + tax + discount;

        // Déterminer la dernière colonne visible pour les totaux
        const getLastVisibleColumn = () => {
          const columnKeys = ['image', 'name', 'sku', 'quantity', 'price', 'total'];
          for (let i = columnKeys.length - 1; i >= 0; i--) {
            if (element.columns?.[columnKeys[i]] !== false) {
              return columnKeys[i];
            }
          }
          return 'total';
        };
        const lastVisibleColumn = getLastVisibleColumn();

        return (
          <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            flexDirection: 'column',
            fontSize: `${(element.fontSize || 10) * zoom}px`,
            fontFamily: element.fontFamily || 'Arial, sans-serif',
            border: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : (element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * 0.5) * zoom}px solid ${element.borderColor || '#e5e7eb'}` : 'none'),
            borderRadius: `${(element.borderRadius || 2) * zoom}px`,
            overflow: 'hidden',
            backgroundColor: element.backgroundColor || 'transparent',
            boxSizing: 'border-box',
            boxShadow: tableStyles.shadow && element.tableStyle === 'modern' ? `0 4px 8px ${tableStyles.shadow}` : 'none'
          }}>
            {/* En-tête du tableau */}
            {showHeaders && (
              <div style={{
                display: 'flex',
                background: tableStyles.gradient || tableStyles.headerBg,
                borderBottom: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none',
                fontWeight: 'bold',
                color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
                boxShadow: tableStyles.shadow ? `0 2px 4px ${tableStyles.shadow}` : 'none'
              }}>
                {element.columns?.image !== false && (
                  <div style={{
                    flex: `0 0 ${40 * zoom}px`,
                    padding: `${4 * zoom}px`,
                    textAlign: 'center',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('image')}
                  </div>
                )}
                {element.columns?.name !== false && (
                  <div style={{
                    flex: 1,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'left',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('name')}
                  </div>
                )}
                {element.columns?.sku !== false && (
                  <div style={{
                    flex: `0 0 ${80 * zoom}px`,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'left',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('sku')}
                  </div>
                )}
                {element.columns?.quantity !== false && (
                  <div style={{
                    flex: `0 0 ${60 * zoom}px`,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'center',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('quantity')}
                  </div>
                )}
                {element.columns?.price !== false && (
                  <div style={{
                    flex: `0 0 ${80 * zoom}px`,
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right',
                    borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('price')}
                  </div>
                )}
                {element.columns?.total !== false && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: `${4 * zoom}px ${6 * zoom}px`,
                    textAlign: 'right'
                  }}>
                    {getColumnHeader('total')}
                  </div>
                )}
              </div>
            )}

            {/* Lignes de données */}
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
              {products.map((product, index) => (
                <div key={index} style={{
                  display: 'flex',
                  borderBottom: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}` : 'none',
                  backgroundColor: element.tableStyle === 'striped' && index % 2 === 1 ? tableStyles.altRowBg : 'transparent',
                  color: tableStyles.rowTextColor || '#000000',
                  boxShadow: tableStyles.shadow ? `0 1px 2px ${tableStyles.shadow}` : 'none'
                }}>
                  {element.columns?.image !== false && (
                    <div style={{
                      flex: `0 0 ${40 * zoom}px`,
                      padding: `${4 * zoom}px`,
                      textAlign: 'center',
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      📷
                    </div>
                  )}
                  {element.columns?.name !== false && (
                    <div style={{
                      flex: 1,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.name}
                    </div>
                  )}
                  {element.columns?.sku !== false && (
                    <div style={{
                      flex: `0 0 ${80 * zoom}px`,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.sku}
                    </div>
                  )}
                  {element.columns?.quantity !== false && (
                    <div style={{
                      flex: `0 0 ${60 * zoom}px`,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'center',
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.quantity}
                    </div>
                  )}
                  {element.columns?.price !== false && (
                    <div style={{
                      flex: `0 0 ${80 * zoom}px`,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right',
                      borderRight: showBorders ? `${tableStyles.borderWidth * zoom}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.price.toFixed(2)}€
                    </div>
                  )}
                  {element.columns?.total !== false && (
                    <div style={{
                      flex: `0 0 ${80 * zoom}px`,
                      padding: `${4 * zoom}px ${6 * zoom}px`,
                      textAlign: 'right'
                    }}>
                      {product.total.toFixed(2)}€
                    </div>
                  )}
                </div>
              ))}
            </div>

            {/* Totaux */}
            <div style={{ borderTop: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none' }}>
              {element.showSubtotal && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: `${4 * zoom}px ${6 * zoom}px`,
                  fontWeight: 'bold'
                }}>
                  <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                    <span>Sous-total:</span>
                    <span>{subtotal.toFixed(2)}€</span>
                  </div>
                </div>
              )}
              {element.showShipping && shipping > 0 && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: `${4 * zoom}px ${6 * zoom}px`
                }}>
                  <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                    <span>Port:</span>
                    <span>{shipping.toFixed(2)}€</span>
                  </div>
                </div>
              )}
              {element.showTaxes && tax > 0 && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: `${4 * zoom}px ${6 * zoom}px`
                }}>
                  <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                    <span>TVA:</span>
                    <span>{tax.toFixed(2)}€</span>
                  </div>
                </div>
              )}
              {element.showDiscount && discount < 0 && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: `${4 * zoom}px ${6 * zoom}px`
                }}>
                  <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                    <span>Remise:</span>
                    <span>{Math.abs(discount).toFixed(2)}€</span>
                  </div>
                </div>
              )}
              {element.showTotal && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: `${4 * zoom}px ${6 * zoom}px`,
                  fontWeight: 'bold',
                  background: tableStyles.gradient || tableStyles.headerBg,
                  color: tableStyles.headerTextColor || (element.tableStyle === 'modern' ? '#ffffff' : '#000000'),
                  boxShadow: tableStyles.shadow ? `0 2px 4px ${tableStyles.shadow}` : 'none'
                }}>
                  <div style={{ width: 'auto', textAlign: 'right', display: 'flex', justifyContent: 'space-between' }}>
                    <span>TOTAL:</span>
                    <span>{total.toFixed(2)}€</span>
                  </div>
                </div>
              )}
            </div>
          </div>
        );

      case 'customer_info':
        // Rendu dynamique des informations client utilisant les propriétés de l'élément
        const customerFields = element.fields || ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'];
        const showLabels = element.showLabels !== false;
        const layout = element.layout || 'vertical';
        const alignment = element.alignment || 'left';
        const spacing = element.spacing || 3;

        // Données fictives pour l'aperçu (seront remplacées par les vraies données lors de la génération)
        const customerData = {
          name: 'Jean Dupont',
          company: 'ABC Company SARL',
          address: '123 Rue de la Paix\n75001 Paris, France',
          email: 'jean.dupont@email.com',
          phone: '+33 6 12 34 56 78',
          tva: 'FR 12 345 678 901',
          siret: '123 456 789 00012',
          website: 'www.abc-company.com'
        };

        const containerStyle = {
          padding: `${8 * zoom}px`,
          fontSize: (element.fontSize || 12) * zoom,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: alignment,
          display: layout === 'horizontal' ? 'flex' : 'block',
          flexWrap: layout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: layout === 'horizontal' ? `${spacing * zoom}px` : '0'
        };

        return (
          <div style={containerStyle}>
            {customerFields.map((field, index) => {
              const fieldData = customerData[field];
              if (!fieldData) return null;

              const fieldStyle = layout === 'horizontal' ? {
                flex: '1',
                minWidth: `${120 * zoom}px`
              } : {
                marginBottom: index < customerFields.length - 1 ? `${spacing * zoom}px` : '0',
                display: 'flex',
                alignItems: 'flex-start'
              };

              return (
                <div key={field} style={fieldStyle}>
                  {showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      marginBottom: layout === 'horizontal' ? `${2 * zoom}px` : '0',
                      marginRight: layout === 'horizontal' ? '0' : `${8 * zoom}px`,
                      fontSize: `${11 * zoom}px`,
                      opacity: 0.8,
                      minWidth: layout === 'horizontal' ? 'auto' : `${80 * zoom}px`,
                      flexShrink: 0
                    }}>
                      {field === 'name' && 'Client'}
                      {field === 'company' && 'Entreprise'}
                      {field === 'address' && 'Adresse'}
                      {field === 'email' && 'Email'}
                      {field === 'phone' && 'Téléphone'}
                      {field === 'tva' && 'N° TVA'}
                      {field === 'siret' && 'SIRET'}
                      {field === 'website' && 'Site web'}
                      :
                    </div>
                  )}
                  <div style={{
                    whiteSpace: 'pre-line',
                    fontSize: (element.fontSize || 12) * zoom,
                    flex: layout === 'horizontal' ? '1' : 'auto'
                  }}>
                    {fieldData}
                  </div>
                </div>
              );
            })}
          </div>
        );

      case 'company_info':
        // Rendu dynamique des informations entreprise utilisant les propriétés de l'élément
        const companyFields = element.fields || ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'];
        const showCompanyLabels = element.showLabels !== false;
        const companyLayout = element.layout || 'vertical';
        const companyAlignment = element.alignment || 'left';
        const companySpacing = element.spacing || 3;

        // Données fictives pour l'aperçu (seront remplacées par les vraies données lors de la génération)
        const companyData = {
          name: 'ABC Company SARL',
          address: '456 Avenue des Champs\n75008 Paris, France',
          phone: '01 23 45 67 89',
          email: 'contact@abc-company.com',
          tva: 'FR 98 765 432 109',
          siret: '987 654 321 00098',
          rcs: 'Paris B 123 456 789',
          website: 'www.abc-company.com'
        };

        const companyContainerStyle = {
          padding: `${8 * zoom}px`,
          fontSize: (element.fontSize || 12) * zoom,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: companyAlignment,
          display: companyLayout === 'horizontal' ? 'flex' : 'block',
          flexWrap: companyLayout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: companyLayout === 'horizontal' ? `${companySpacing * zoom}px` : '0'
        };

        return (
          <div style={companyContainerStyle}>
            {companyFields.map((field, index) => {
              const fieldData = companyData[field];
              if (!fieldData) return null;

              const companyFieldStyle = companyLayout === 'horizontal' ? {
                flex: '1',
                minWidth: `${120 * zoom}px`
              } : {
                marginBottom: index < companyFields.length - 1 ? `${companySpacing * zoom}px` : '0',
                display: 'flex',
                alignItems: 'flex-start'
              };

              return (
                <div key={field} style={companyFieldStyle}>
                  {showCompanyLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      marginBottom: companyLayout === 'horizontal' ? `${2 * zoom}px` : '0',
                      marginRight: companyLayout === 'horizontal' ? '0' : `${8 * zoom}px`,
                      fontSize: `${11 * zoom}px`,
                      opacity: 0.8,
                      minWidth: companyLayout === 'horizontal' ? 'auto' : `${80 * zoom}px`,
                      flexShrink: 0
                    }}>
                      {field === 'name' && 'Entreprise'}
                      {field === 'address' && 'Adresse'}
                      {field === 'phone' && 'Téléphone'}
                      {field === 'email' && 'Email'}
                      {field === 'tva' && 'N° TVA'}
                      {field === 'siret' && 'SIRET'}
                      {field === 'rcs' && 'RCS'}
                      {field === 'website' && 'Site web'}
                      :
                    </div>
                  )}
                  <div style={{
                    whiteSpace: 'pre-line',
                    fontSize: (element.fontSize || 12) * zoom,
                    flex: companyLayout === 'horizontal' ? '1' : 'auto'
                  }}>
                    {fieldData}
                  </div>
                </div>
              );
            })}
          </div>
        );

      case 'company_logo':
        return (
          <div style={{
            width: '100%',
            height: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: `${(element.padding || 8) * zoom}px`,
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom,
            border: element.borderWidth ? `${element.borderWidth * zoom}px solid ${element.borderColor || '#e5e7eb'}` : 'none'
          }}>
            {element.imageUrl || element.src ? (
              <img
                src={element.imageUrl || element.src}
                alt={element.alt || "Logo entreprise"}
                style={{
                  maxWidth: '100%',
                  maxHeight: '100%',
                  objectFit: element.objectFit || 'contain'
                }}
              />
            ) : (
              <div style={{
                width: '100%',
                height: '100%',
                backgroundColor: '#f0f0f0',
                border: `${2 * zoom}px dashed #ccc`,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#666',
                fontSize: ((element.fontSize || 12) * zoom)
              }}>
                🏢 Logo
              </div>
            )}
          </div>
        );

      case 'order_number':
        return (
          <div style={{
            padding: `${(element.padding || 8) * zoom}px`,
            fontSize: (element.fontSize || 14) * zoom,
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#333',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'left',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom,
            border: element.borderWidth ? `${element.borderWidth * zoom}px solid ${element.borderColor || '#e5e7eb'}` : 'none'
          }}>
            {element.showLabel !== false && (
              <div style={{
                fontSize: ((element.fontSize || 14) * 0.8) * zoom,
                color: element.labelColor || '#666',
                marginBottom: `${2 * zoom}px`,
                fontWeight: 'normal'
              }}>
                {element.label || 'N° de commande'}:
              </div>
            )}
            <div style={{
              fontSize: (element.fontSize || 14) * zoom,
              fontWeight: element.fontWeight || 'bold'
            }}>
              {element.prefix || 'CMD-'}{element.orderNumber || '2025-00123'}
            </div>
          </div>
        );

      case 'document_type':
        return (
          <div style={{
            padding: `${(element.padding || 8) * zoom}px`,
            fontSize: `${(element.fontSize || 18) * zoom}px`,
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#1e293b',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'center',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: (element.borderRadius || 0) * zoom
          }}>
            {element.documentType === 'invoice' ? 'FACTURE' :
             element.documentType === 'quote' ? 'DEVIS' :
             element.documentType === 'receipt' ? 'REÇU' :
             element.documentType === 'order' ? 'COMMANDE' :
             element.documentType === 'credit_note' ? 'AVOIR' : 'DOCUMENT'}
          </div>
        );

      case 'progress-bar':
        return (
          <div style={{
            width: '100%',
            height: (element.height || 20) * zoom,
            backgroundColor: element.backgroundColor || '#e5e7eb',
            borderRadius: (element.borderRadius || 10) * zoom,
            overflow: 'hidden',
            border: element.borderWidth ? `${element.borderWidth * zoom}px solid ${element.borderColor || '#d1d5db'}` : 'none'
          }}>
            <div style={{
              width: `${Math.min(100, Math.max(0, element.progressValue || 75))}%`,
              height: '100%',
              backgroundColor: element.progressColor || '#3b82f6',
              borderRadius: (element.borderRadius || 10) * zoom,
              transition: element.animate !== false ? 'width 0.3s ease' : 'none'
            }} />
          </div>
        );

      default:
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              backgroundColor: '#f0f0f0',
              border: `${1 * zoom}px dashed #ccc`,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: `${12 * zoom}px`,
              color: '#666',
              padding: `${4 * zoom}px`,
              boxSizing: 'border-box'
            }}
          >
            {element.type || 'Élément inconnu'}
          </div>
        );
    }
  }, []);

  // Générer l'aperçu quand la modale s'ouvre
  useEffect(() => {
    if (isOpen && elements.length > 0) {
      if (useServerPreview) {
        // Utiliser l'aperçu unifié côté serveur
        generateServerPreview();
      } else {
        // Afficher immédiatement le contenu du canvas
        setPreviewData({
          success: true,
          elements_count: elements.length,
          width: 400,
          height: 566,
          fallback: false
        });
        // Puis générer l'aperçu côté serveur en arrière-plan
        generatePreview();
      }
    } else if (isOpen && elements.length === 0) {
      setPreviewData({
        success: true,
        elements_count: 0,
        width: 400,
        height: 566,
        fallback: false
      });
    }
  }, [isOpen, elements.length, useServerPreview]);

  const generatePreview = async () => {
    // Ne pas définir loading=true car l'aperçu s'affiche déjà
    setError(null);

    try {
      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        console.warn('Variables AJAX non disponibles pour validation côté serveur');
        return;
      }

      // Obtenir un nonce frais
      const nonceFormData = new FormData();
      nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');

      const nonceResponse = await fetch(ajaxUrl, {
        method: 'POST',
        body: nonceFormData
      });

      if (!nonceResponse.ok) {
        console.warn('Erreur obtention nonce pour validation:', nonceResponse.status);
        return;
      }

      const nonceData = await nonceResponse.json();
      if (!nonceData.success) {
        console.warn('Impossible d\'obtenir un nonce frais pour validation');
        return;
      }

      const freshNonce = nonceData.data.nonce;


      // Fonction pour nettoyer les éléments avant sérialisation JSON
      const cleanElementsForJSON = (elements) => {
        return elements.map(element => {
          const cleaned = { ...element };

          // Supprimer les propriétés non sérialisables
          const propertiesToRemove = ['reactKey', 'tempId', 'style', '_internalId', 'ref', 'key'];
          propertiesToRemove.forEach(prop => {
            delete cleaned[prop];
          });

          // Nettoyer récursivement tous les objets imbriqués
          const cleanObject = (obj) => {
            if (obj === null || typeof obj !== 'object') {
              return obj;
            }

            if (Array.isArray(obj)) {
              return obj.map(cleanObject);
            }

            const cleanedObj = {};
            for (const key in obj) {
              if (obj.hasOwnProperty(key)) {
                const value = obj[key];

                // Ignorer les fonctions, symboles, et objets complexes
                if (typeof value === 'function' || typeof value === 'symbol' ||
                    (typeof value === 'object' && value !== null &&
                     !(Array.isArray(value)) &&
                     !(value instanceof Date) &&
                     !(value instanceof RegExp))) {
                  continue; // Skip this property
                }

                // Nettoyer récursivement
                cleanedObj[key] = cleanObject(value);
              }
            }
            return cleanedObj;
          };

          // Appliquer le nettoyage récursif
          const fullyCleaned = cleanObject(cleaned);

          // S'assurer que les propriétés numériques sont des nombres
          ['x', 'y', 'width', 'height', 'fontSize', 'borderWidth', 'borderRadius'].forEach(prop => {
            if (fullyCleaned[prop] !== undefined && fullyCleaned[prop] !== null) {
              const num = parseFloat(fullyCleaned[prop]);
              if (!isNaN(num)) {
                fullyCleaned[prop] = num;
              } else {
                delete fullyCleaned[prop]; // Supprimer si pas un nombre valide
              }
            }
          });

          // S'assurer que les propriétés boolean sont des booléens
          ['showLabels', 'showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'].forEach(prop => {
            if (fullyCleaned[prop] !== undefined) {
              fullyCleaned[prop] = Boolean(fullyCleaned[prop]);
            }
          });

          // S'assurer que les chaînes sont des chaînes
          ['id', 'type', 'content', 'text', 'color', 'backgroundColor', 'borderColor', 'fontFamily', 'fontWeight', 'fontStyle', 'textDecoration', 'textAlign', 'borderStyle'].forEach(prop => {
            if (fullyCleaned[prop] !== undefined && fullyCleaned[prop] !== null) {
              fullyCleaned[prop] = String(fullyCleaned[prop]);
            }
          });

          return fullyCleaned;
        });
      };

      // Validation côté client avant envoi
      const validationResult = validateElementsBeforeSend(elements);
      if (!validationResult.success) {
        console.error('❌ Validation côté client échouée:', validationResult.error);
        setPreviewData(prev => ({
          ...prev,
          error: `Erreur de validation côté client: ${validationResult.error}`,
          isLoading: false
        }));
        return;
      }

      const { jsonString, cleanedElements } = validationResult;

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_validate_preview');
      formData.append('nonce', freshNonce);
      formData.append('elements', jsonString);

      // Faire l'appel AJAX en arrière-plan
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        console.warn('Erreur HTTP validation aperçu:', response.status);
        return;
      }

      let data;
      try {
        data = await response.json();
      } catch (jsonErr) {
        console.error('❌ Erreur parsing JSON réponse serveur:', jsonErr);
        const responseText = await response.text();
        console.error('Contenu brut de la réponse:', responseText.substring(0, 500));
        // Garder l'aperçu local mais marquer l'erreur
        setPreviewData(prev => ({
          ...prev,
          server_error: 'Réponse serveur invalide (pas du JSON)'
        }));
        return;
      }

      if (data.success) {
        // Mettre à jour previewData avec les données du serveur si nécessaire
        setPreviewData(prev => ({
          ...prev,
          ...data.data,
          server_validated: true
        }));
      } else {
        console.warn('⚠️ Validation aperçu côté serveur échouée:', data.data);
        // Garder l'aperçu local mais marquer qu'il y a un problème serveur
        // S'assurer que server_error est toujours une chaîne
        let errorMessage = 'Erreur validation serveur';
        if (typeof data.data === 'string') {
          errorMessage = data.data;
        } else if (data.data && typeof data.data === 'object' && data.data.message) {
          errorMessage = data.data.message;
        } else if (data.data && typeof data.data === 'object') {
          errorMessage = JSON.stringify(data.data);
        }

        setPreviewData(prev => ({
          ...prev,
          server_error: errorMessage
        }));
      }

    } catch (err) {
      console.warn('Erreur validation aperçu côté serveur:', err);
      // Ne pas afficher d'erreur car l'aperçu local fonctionne
      setPreviewData(prev => ({
        ...prev,
        server_error: err.message || 'Erreur inconnue côté serveur'
      }));
    }
  };

  const generateServerPreview = async () => {

    setLoading(true);
    setError(null);
    setPreviewData(null);

    // Timeout de fallback - si l'aperçu côté serveur prend trop de temps, afficher l'aperçu côté client
    const fallbackTimeout = setTimeout(() => {
      setPreviewData({
        success: true,
        elements_count: elements.length,
        width: canvasWidth,
        height: canvasHeight,
        fallback: true,
        server_timeout: true
      });
      setLoading(false);
    }, 10000); // 10 secondes timeout

    try {
      // Validation côté client avant envoi
      const validationResult = validateElementsBeforeSend(elements);
      if (!validationResult.success) {
        console.error('❌ Validation côté client échouée:', validationResult.error);
        setPreviewData(prev => ({
          ...prev,
          error: `Erreur de validation côté client: ${validationResult.error}`,
          isLoading: false
        }));
        return;
      }

      const { jsonString } = validationResult;

      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
        return;
      }

      // Obtenir un nonce frais pour l'aperçu
      const nonceFormData = new FormData();
      nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');

      const nonceResponse = await fetch(ajaxUrl, {
        method: 'POST',
        body: nonceFormData
      });

      if (!nonceResponse.ok) {
        throw new Error(`Erreur HTTP nonce: ${nonceResponse.status}`);
      }

      const nonceData = await nonceResponse.json();
      if (!nonceData.success) {
        throw new Error('Impossible d\'obtenir un nonce frais');
      }

      const freshNonce = nonceData.data.nonce;

      // Préparer les données pour l'AJAX unifié
      const formData = new FormData();
      formData.append('action', 'pdf_builder_unified_preview');
      formData.append('nonce', freshNonce);
      formData.append('elements', jsonString);


      const response = await fetch(ajaxurl || window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && data.data && data.data.url) {

        // Nettoyer le timeout de fallback
        clearTimeout(fallbackTimeout);

        // Mettre à jour l'état pour afficher le PDF dans la modale
        setPreviewData({
          url: data.data.url,
          server_validated: true,
          elements_count: elements.length,
          width: canvasWidth,
          height: canvasHeight,
          zoom: zoom
        });
        setLoading(false);
        setError(null);

        // Ne pas ouvrir de nouvel onglet - le PDF s'affichera dans la modale
        return;
      } else {
        throw new Error(data.data || 'Erreur génération aperçu côté serveur');
      }

    } catch (error) {
      console.error('❌ Erreur génération aperçu côté serveur:', error);
      // Nettoyer le timeout de fallback
      clearTimeout(fallbackTimeout);
      setError(`Erreur aperçu côté serveur: ${error.message}`);
      setLoading(false);
    }
  };

  const handlePrint = async () => {

    let printButton = null;

    try {
      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
        return;
      }

      // Obtenir un nonce frais
      const nonceFormData = new FormData();
      nonceFormData.append('action', 'pdf_builder_get_fresh_nonce');

      const nonceResponse = await fetch(ajaxUrl, {
        method: 'POST',
        body: nonceFormData
      });

      if (!nonceResponse.ok) {
        throw new Error(`Erreur HTTP nonce: ${nonceResponse.status}`);
      }

      const nonceData = await nonceResponse.json();
      if (!nonceData.success) {
        throw new Error('Impossible d\'obtenir un nonce frais');
      }

      const freshNonce = nonceData.data.nonce;

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_pdf');
      formData.append('nonce', freshNonce);
      formData.append('elements', JSON.stringify(elements));


      // Afficher un indicateur de chargement
      printButton = document.querySelector('.btn-primary');
      if (printButton) {
        const originalText = printButton.textContent;
        printButton.textContent = '⏳ Génération PDF...';
        printButton.disabled = true;
      }

      // Envoyer la requête AJAX
      const response = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error('Erreur réseau: ' + response.status);
      }

      const data = await response.json().catch(jsonError => {
        console.error('Erreur parsing JSON:', jsonError);
        throw new Error('Réponse invalide du serveur (pas du JSON)');
      });

      if (!data.success) {
        let errorMessage = 'Erreur inconnue lors de la génération du PDF';
        if (typeof data.data === 'string') {
          errorMessage = data.data;
        } else if (typeof data.data === 'object' && data.data !== null) {
          errorMessage = data.data.message || JSON.stringify(data.data);
        }
        throw new Error(errorMessage);
      }

      if (!data.data || !data.data.pdf) {
        throw new Error('Données PDF manquantes dans la réponse');
      }

      // Convertir le PDF base64 en blob
      const pdfBase64 = data.data.pdf;
      const pdfBlob = new Blob(
        [Uint8Array.from(atob(pdfBase64), c => c.charCodeAt(0))],
        { type: 'application/pdf' }
      );


      if (pdfBlob.size === 0) {
        throw new Error('Le PDF généré est vide');
      }

      // Créer un URL pour le blob PDF
      const pdfUrl = URL.createObjectURL(pdfBlob);

      // Ouvrir le PDF dans une modale si la prop est fournie, sinon dans une nouvelle fenêtre
      if (onOpenPDFModal) {
        onOpenPDFModal(pdfUrl);
      } else {
        // Fallback vers l'ancienne méthode
        const previewWindow = window.open(pdfUrl, '_blank');

        if (!previewWindow) {
          // Fallback si le popup est bloqué
          const link = document.createElement('a');
          link.href = pdfUrl;
          link.target = '_blank';
          link.rel = 'noopener noreferrer';
          document.body.appendChild(link);
          link.click();
          // Vérifier que l'élément existe encore avant de le supprimer
          if (link.parentNode === document.body) {
            document.body.removeChild(link);
          }
        }
      }

      // Libérer l'URL du blob après un délai (seulement si pas en modale)
      if (!onOpenPDFModal) {
        setTimeout(() => {
          URL.revokeObjectURL(pdfUrl);
        }, 1000);
      }


    } catch (error) {
      console.error('Erreur génération PDF:', error);
      alert('Erreur lors de la génération du PDF: ' + error.message);
    } finally {
      // Restaurer le bouton
      if (printButton) {
        printButton.textContent = '👁️ Imprimer PDF';
        printButton.disabled = false;
      }
    }
  };

  if (!isOpen) return null;

  return (
    <div className="preview-modal-overlay" onClick={onClose}>
      <div className="preview-modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="preview-modal-header">
          <h3>🎨 Aperçu Canvas - PDF Builder Pro v2.0</h3>
          <button className="preview-modal-close" onClick={onClose}>×</button>
        </div>

        <div className="preview-modal-body">
          {loading && (
            <div className="preview-loading">
              <div className="preview-spinner"></div>
              <p>Génération de l'aperçu...</p>
            </div>
          )}

          {error && (
            <div className="preview-error">
              <h4>❌ Erreur d'aperçu</h4>
              <p>{error}</p>
              <p><small>Le PDF pourra quand même être généré normalement.</small></p>
            </div>
          )}

          {previewData && (
            <div className="preview-content">
              <div style={{
                textAlign: 'center',
                marginBottom: '20px',
                padding: '10px',
                background: previewData.server_validated ? '#e8f5e8' : '#fff3cd',
                borderRadius: '4px',
                border: `1px solid ${previewData.server_validated ? '#c3e6c3' : '#ffeaa7'}`
              }}>
                <strong>{previewData.server_validated ? '✅' : '⚡'} Aperçu généré</strong><br/>
                <small>
                  {previewData.elements_count} élément{previewData.elements_count !== 1 ? 's' : ''} • {previewData.width}×{previewData.height}px
                  {previewData.server_validated && ' • Serveur validé'}
                  {previewData.server_error && ' • ⚠️ Problème serveur'}
                </small>
              </div>

              <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'flex-start',
                minHeight: '400px',
                backgroundColor: '#f8f9fa',
                borderRadius: '8px',
                padding: '20px'
              }}>
                {previewData.url ? (
                  // Aperçu côté serveur - afficher le PDF dans un iframe
                  <iframe
                    src={previewData.url}
                    style={{
                      width: '100%',
                      height: '600px',
                      border: '1px solid #dee2e6',
                      borderRadius: '4px',
                      backgroundColor: 'white'
                    }}
                    title="Aperçu PDF côté serveur"
                  />
                ) : (
                  // Aperçu côté client - rendre le HTML
                  renderCanvasContent(elements)
                )}
              </div>

              {previewData.server_error && (
                <div style={{
                  marginTop: '20px',
                  padding: '15px',
                  backgroundColor: '#ffeaa7',
                  borderRadius: '6px',
                  border: '1px solid #d4a574'
                }}>
                  <h5 style={{ margin: '0 0 10px 0', color: '#856404' }}>⚠️ Note</h5>
                  <p style={{ margin: '0', fontSize: '14px', color: '#333' }}>
                    L'aperçu s'affiche correctement, mais il y a un problème de validation côté serveur: {previewData.server_error}
                  </p>
                </div>
              )}

              <div style={{
                marginTop: '20px',
                padding: '15px',
                backgroundColor: '#e8f4fd',
                borderRadius: '6px',
                border: '1px solid #b3d9ff'
              }}>
                <h5 style={{ margin: '0 0 10px 0', color: '#0066cc' }}>ℹ️ Informations du Canvas</h5>
                <p style={{ margin: '0', fontSize: '14px', color: '#333' }}>
                  <strong>Dimensions:</strong> {canvasWidth} × {canvasHeight} pixels<br/>
                  <strong>Éléments:</strong> {elements.length}<br/>
                  <strong>Zoom:</strong> {Math.round(zoom * 100)}%<br/>
                  <strong>Status:</strong> {previewData.server_validated ? 'Validé côté serveur' : 'Aperçu local'}
                </p>
              </div>
            </div>
          )}

          {!loading && !error && !previewData && (
            <div className="preview-loading">
              <p>Préparation de l'aperçu...</p>
            </div>
          )}
        </div>

        <div className="preview-modal-footer">
          <button className="btn btn-secondary" onClick={onClose}>
            ❌ Fermer
          </button>
          <button className="btn btn-primary" onClick={handlePrint}>
            👁️ Imprimer PDF
          </button>
        </div>
      </div>
    </div>
  );
};

export default PreviewModal;

