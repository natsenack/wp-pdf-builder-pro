import React, { useState, useEffect } from 'react';

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
      
      console.log('Client-side validation passed. JSON length:', jsonString.length);
      return { success: true, jsonString, cleanedElements };
    } catch (error) {
      console.error('Client-side validation failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Fonction pour rendre le contenu du canvas en HTML
  const renderCanvasContent = (elements) => {
    if (!elements || elements.length === 0) {
      return <div style={{ padding: '20px', textAlign: 'center', color: '#666' }}>Aucun élément à afficher</div>;
    }

    return (
      <div
        style={{
          position: 'relative',
          width: canvasWidth,
          height: canvasHeight,
          backgroundColor: 'white',
          border: '1px solid #e2e8f0',
          borderRadius: '4px',
          overflow: 'hidden',
          transform: `scale(${zoom})`,
          transformOrigin: 'top left',
          margin: '0 auto'
        }}
      >
        {elements.map((element, index) => {
          const baseStyle = {
            position: 'absolute',
            left: element.x || 0,
            top: element.y || 0,
            width: element.width || 100,
            height: element.height || 50,
            zIndex: element.zIndex || index + 1
          };

          return (
            <div key={index} style={baseStyle}>
              {renderSpecialElement(element, zoom)}
            </div>
          );
        })}
      </div>
    );
  };

  // Fonction pour rendre un élément spécial (basée sur CanvasElement.jsx)
  const renderSpecialElement = (element, zoom) => {
    switch (element.type) {
      case 'text':
        return (
          <div
            style={{
              width: '100%',
              height: '100%',
              fontSize: element.fontSize || 16,
              color: element.color || '#000000',
              fontWeight: element.fontWeight === 'bold' ? 'bold' : 'normal',
              fontStyle: element.fontStyle === 'italic' ? 'italic' : 'normal',
              textDecoration: element.textDecoration || 'none',
              textAlign: element.textAlign || 'left',
              lineHeight: element.lineHeight || '1.2',
              whiteSpace: 'pre-wrap',
              overflow: 'hidden',
              padding: '4px',
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
                ? `${element.borderWidth}px ${element.borderStyle || 'solid'} ${element.borderColor || '#000000'}`
                : 'none',
              borderRadius: element.borderRadius || 0
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
              borderRadius: element.borderRadius || 0
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
              height: element.height || element.strokeWidth || 1,
              borderTop: `${element.strokeWidth || 1}px solid ${element.strokeColor || '#000000'}`,
              height: 0
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
              height: `${element.thickness || element.height || 2}px`,
              margin: `${element.margin || 10}px 0`,
              borderRadius: element.borderRadius || 0
            }}
          />
        );

      case 'product_table':
        // Rendu dynamique du tableau de produits utilisant les propriétés de l'élément
        const getTableStyles = (tableStyle = 'default') => {
          const baseStyles = {
            default: {
              headerBg: '#f5f5f5',
              headerBorder: '#ddd',
              rowBorder: '#eee',
              altRowBg: '#fafafa',
              borderWidth: 1
            },
            classic: {
              headerBg: '#ffffff',
              headerBorder: '#000000',
              rowBorder: '#000000',
              altRowBg: '#ffffff',
              borderWidth: 1
            },
            striped: {
              headerBg: '#f8f9fa',
              headerBorder: '#dee2e6',
              rowBorder: '#dee2e6',
              altRowBg: '#e9ecef',
              borderWidth: 1
            },
            bordered: {
              headerBg: '#ffffff',
              headerBorder: '#dee2e6',
              rowBorder: '#dee2e6',
              altRowBg: '#ffffff',
              borderWidth: 2
            },
            minimal: {
              headerBg: '#ffffff',
              headerBorder: '#f1f1f1',
              rowBorder: '#f8f8f8',
              altRowBg: '#ffffff',
              borderWidth: 0.5
            },
            modern: {
              headerBg: '#007bff',
              headerBorder: '#007bff',
              rowBorder: '#e3f2fd',
              altRowBg: '#f8f9ff',
              borderWidth: 1
            }
          };
          return baseStyles[tableStyle] || baseStyles.default;
        };

        const tableStyles = getTableStyles(element.tableStyle);
        const showHeaders = element.showHeaders !== false;
        const showBorders = element.showBorders !== false;
        const columns = element.columns || {
          image: false,
          name: true,
          sku: false,
          quantity: true,
          price: true,
          total: true
        };
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

        // Données d'exemple pour l'aperçu
        const products = [
          { name: 'Produit A - Description', sku: 'SKU001', quantity: 2, price: 19.99, total: 39.98 },
          { name: 'Produit B - Article', sku: 'SKU002', quantity: 1, price: 29.99, total: 29.99 }
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
            if (columns[columnKeys[i]] !== false) {
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
            fontSize: element.fontSize || '10px',
            fontFamily: element.fontFamily || 'Arial, sans-serif',
            border: element.borderWidth && element.borderWidth > 0 ? `${Math.max(1, element.borderWidth * 0.5)}px solid ${element.borderColor || '#e5e7eb'}` : 'none',
            borderRadius: element.borderRadius ? `${element.borderRadius}px` : '2px',
            overflow: 'hidden',
            backgroundColor: element.backgroundColor || 'transparent',
            boxSizing: 'border-box'
          }}>
            {/* En-tête du tableau */}
            {showHeaders && (
              <div style={{
                display: 'flex',
                backgroundColor: tableStyles.headerBg,
                borderBottom: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none',
                fontWeight: 'bold',
                color: element.tableStyle === 'modern' ? '#ffffff' : '#000000'
              }}>
                {columns.image && (
                  <div style={{
                    flex: '0 0 40px',
                    padding: '4px',
                    textAlign: 'center',
                    borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('image')}
                  </div>
                )}
                {columns.name && (
                  <div style={{
                    flex: 1,
                    padding: '4px 6px',
                    textAlign: 'left',
                    borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('name')}
                  </div>
                )}
                {columns.sku && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: '4px 6px',
                    textAlign: 'left',
                    borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('sku')}
                  </div>
                )}
                {columns.quantity && (
                  <div style={{
                    flex: '0 0 60px',
                    padding: '4px 6px',
                    textAlign: 'center',
                    borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('quantity')}
                  </div>
                )}
                {columns.price && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: '4px 6px',
                    textAlign: 'right',
                    borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.headerBorder}` : 'none'
                  }}>
                    {getColumnHeader('price')}
                  </div>
                )}
                {columns.total && (
                  <div style={{
                    flex: '0 0 80px',
                    padding: '4px 6px',
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
                  backgroundColor: element.tableStyle === 'striped' && index % 2 === 1 ? tableStyles.altRowBg : 'transparent'
                }}>
                  {columns.image && (
                    <div style={{
                      flex: '0 0 40px',
                      padding: '4px',
                      textAlign: 'center',
                      borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      📷
                    </div>
                  )}
                  {columns.name && (
                    <div style={{
                      flex: 1,
                      padding: '4px 6px',
                      borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.name}
                    </div>
                  )}
                  {columns.sku && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: '4px 6px',
                      borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.sku}
                    </div>
                  )}
                  {columns.quantity && (
                    <div style={{
                      flex: '0 0 60px',
                      padding: '4px 6px',
                      textAlign: 'center',
                      borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.quantity}
                    </div>
                  )}
                  {columns.price && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: '4px 6px',
                      textAlign: 'right',
                      borderRight: showBorders ? `${tableStyles.borderWidth}px solid ${tableStyles.rowBorder}` : 'none'
                    }}>
                      {product.price.toFixed(2)}€
                    </div>
                  )}
                  {columns.total && (
                    <div style={{
                      flex: '0 0 80px',
                      padding: '4px 6px',
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
                  padding: '4px 6px',
                  fontWeight: 'bold'
                }}>
                  <div style={{ width: '80px', textAlign: 'right' }}>
                    Sous-total: {subtotal.toFixed(2)}€
                  </div>
                </div>
              )}
              {element.showShipping && shipping > 0 && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: '4px 6px'
                }}>
                  <div style={{ width: '80px', textAlign: 'right' }}>
                    Port: {shipping.toFixed(2)}€
                  </div>
                </div>
              )}
              {element.showTaxes && tax > 0 && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: '4px 6px'
                }}>
                  <div style={{ width: '80px', textAlign: 'right' }}>
                    TVA: {tax.toFixed(2)}€
                  </div>
                </div>
              )}
              {element.showDiscount && discount < 0 && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: '4px 6px'
                }}>
                  <div style={{ width: '80px', textAlign: 'right' }}>
                    Remise: {Math.abs(discount).toFixed(2)}€
                  </div>
                </div>
              )}
              {element.showTotal && (
                <div style={{
                  display: 'flex',
                  justifyContent: 'flex-end',
                  padding: '4px 6px',
                  fontWeight: 'bold',
                  backgroundColor: tableStyles.headerBg,
                  color: element.tableStyle === 'modern' ? '#ffffff' : '#000000'
                }}>
                  <div style={{ width: '80px', textAlign: 'right' }}>
                    TOTAL: {total.toFixed(2)}€
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
          address: '123 Rue de la Paix\n75001 Paris\nFrance',
          email: 'jean.dupont@email.com',
          phone: '+33 6 12 34 56 78',
          tva: 'FR 12 345 678 901',
          siret: '123 456 789 00012',
          website: 'www.abc-company.com'
        };

        const containerStyle = {
          padding: '8px',
          fontSize: element.fontSize || 12,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: alignment,
          display: layout === 'horizontal' ? 'flex' : 'block',
          flexWrap: layout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: layout === 'horizontal' ? `${spacing}px` : '0'
        };

        return (
          <div style={containerStyle}>
            {customerFields.map((field, index) => {
              const fieldData = customerData[field];
              if (!fieldData) return null;

              const fieldStyle = layout === 'horizontal' ? {
                flex: '1',
                minWidth: '120px'
              } : {
                marginBottom: index < customerFields.length - 1 ? `${spacing}px` : '0',
                display: 'flex',
                alignItems: 'flex-start'
              };

              return (
                <div key={field} style={fieldStyle}>
                  {showLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      marginBottom: layout === 'horizontal' ? '2px' : '0',
                      marginRight: layout === 'horizontal' ? '0' : '8px',
                      fontSize: '11px',
                      opacity: 0.8,
                      minWidth: layout === 'horizontal' ? 'auto' : '80px',
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
                    fontSize: element.fontSize || 12,
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
          address: '456 Avenue des Champs\n75008 Paris\nFrance',
          phone: '01 23 45 67 89',
          email: 'contact@abc-company.com',
          tva: 'FR 98 765 432 109',
          siret: '987 654 321 00098',
          rcs: 'Paris B 123 456 789',
          website: 'www.abc-company.com'
        };

        const companyContainerStyle = {
          padding: '8px',
          fontSize: element.fontSize || 12,
          lineHeight: element.lineHeight || '1.4',
          color: element.color || '#1e293b',
          fontFamily: element.fontFamily || 'Inter, sans-serif',
          textAlign: companyAlignment,
          display: companyLayout === 'horizontal' ? 'flex' : 'block',
          flexWrap: companyLayout === 'horizontal' ? 'wrap' : 'nowrap',
          gap: companyLayout === 'horizontal' ? `${companySpacing}px` : '0'
        };

        return (
          <div style={companyContainerStyle}>
            {companyFields.map((field, index) => {
              const fieldData = companyData[field];
              if (!fieldData) return null;

              const companyFieldStyle = companyLayout === 'horizontal' ? {
                flex: '1',
                minWidth: '120px'
              } : {
                marginBottom: index < companyFields.length - 1 ? `${companySpacing}px` : '0',
                display: 'flex',
                alignItems: 'flex-start'
              };

              return (
                <div key={field} style={companyFieldStyle}>
                  {showCompanyLabels && (
                    <div style={{
                      fontWeight: element.labelStyle === 'bold' ? 'bold' : 'normal',
                      marginBottom: companyLayout === 'horizontal' ? '2px' : '0',
                      marginRight: companyLayout === 'horizontal' ? '0' : '8px',
                      fontSize: '11px',
                      opacity: 0.8,
                      minWidth: companyLayout === 'horizontal' ? 'auto' : '80px',
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
                    fontSize: element.fontSize || 12,
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
            padding: element.padding || '8px',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: element.borderRadius || 0,
            border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#e5e7eb'}` : 'none'
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
                border: '2px dashed #ccc',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#666',
                fontSize: element.fontSize || '12px'
              }}>
                🏢 Logo
              </div>
            )}
          </div>
        );

      case 'order_number':
        return (
          <div style={{
            padding: element.padding || '8px',
            fontSize: element.fontSize || 14,
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#333',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'left',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: element.borderRadius || 0,
            border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#e5e7eb'}` : 'none'
          }}>
            {element.showLabel !== false && (
              <div style={{
                fontSize: (element.fontSize || 14) * 0.8,
                color: element.labelColor || '#666',
                marginBottom: '2px',
                fontWeight: 'normal'
              }}>
                {element.label || 'N° de commande'}:
              </div>
            )}
            <div style={{
              fontSize: element.fontSize || 14,
              fontWeight: element.fontWeight || 'bold'
            }}>
              {element.prefix || 'CMD-'}{element.orderNumber || '2025-00123'}
            </div>
          </div>
        );

      case 'document_type':
        return (
          <div style={{
            padding: element.padding || '8px',
            fontSize: element.fontSize || '18px',
            fontWeight: element.fontWeight || 'bold',
            color: element.color || '#1e293b',
            fontFamily: element.fontFamily || 'Inter, sans-serif',
            textAlign: element.textAlign || 'center',
            backgroundColor: element.backgroundColor || 'transparent',
            borderRadius: element.borderRadius || 0
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
            height: element.height || '100%',
            backgroundColor: element.backgroundColor || '#e5e7eb',
            borderRadius: element.borderRadius || '10px',
            overflow: 'hidden',
            border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#d1d5db'}` : 'none'
          }}>
            <div style={{
              width: `${Math.min(100, Math.max(0, element.progressValue || 75))}%`,
              height: '100%',
              backgroundColor: element.progressColor || '#3b82f6',
              borderRadius: element.borderRadius || '10px',
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
              border: '1px dashed #ccc',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '12px',
              color: '#666',
              padding: '4px',
              boxSizing: 'border-box'
            }}
          >
            {element.type || 'Élément inconnu'}
          </div>
        );
    }
  };

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
  }, [isOpen, elements]);

  const generatePreview = async () => {
    // Ne pas définir loading=true car l'aperçu s'affiche déjà
    setError(null);

    try {
      console.log('Validation aperçu côté serveur pour', elements.length, 'éléments');

      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        console.warn('Variables AJAX non disponibles pour validation côté serveur');
        return;
      }

      // Obtenir un nonce frais
      console.log('Obtention d\'un nonce frais pour validation...');
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
      console.log('Nonce frais obtenu pour validation:', freshNonce);

      console.log('Variables AJAX utilisées:', { ajaxUrl: ajaxUrl.substring(0, 50) + '...', nonceLength: freshNonce.length });
      console.log('Valeur du nonce envoyé:', freshNonce);
      console.log('Timestamp envoi:', Date.now());

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
      console.log('✅ Validation côté client réussie, longueur JSON:', jsonString.length);

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
        console.log('✅ Validation aperçu côté serveur réussie:', data.data);
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
    console.log('🖥️ Génération aperçu côté serveur unifié...');

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
      console.log('✅ Validation côté client réussie, longueur JSON:', jsonString.length);

      // Préparer les données pour l'AJAX unifié
      const formData = new FormData();
      formData.append('action', 'pdf_builder_unified_preview');
      formData.append('nonce', pdfBuilderNonce || window.pdfBuilderAjax?.nonce || '');
      formData.append('elements', jsonString);

      console.log('🌐 Envoi requête aperçu unifié...');

      const response = await fetch(ajaxurl || window.pdfBuilderAjax?.ajaxurl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && data.data && data.data.url) {
        console.log('✅ Aperçu côté serveur généré:', data.data.url);

        // Ouvrir le PDF dans une nouvelle fenêtre
        const previewWindow = window.open(data.data.url, '_blank');
        if (!previewWindow) {
          console.warn('Popup bloquée, tentative de téléchargement...');
          const link = document.createElement('a');
          link.href = data.data.url;
          link.target = '_blank';
          link.rel = 'noopener noreferrer';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        }

        // Fermer la modale d'aperçu
        onClose();
      } else {
        throw new Error(data.data || 'Erreur génération aperçu côté serveur');
      }

    } catch (error) {
      console.error('❌ Erreur génération aperçu côté serveur:', error);
      setPreviewData(prev => ({
        ...prev,
        error: `Erreur aperçu côté serveur: ${error.message}`,
        isLoading: false
      }));
    }
  };

  const handlePrint = async () => {
    console.log('Génération PDF finale...');

    let printButton = null;

    try {
      // Vérifier que les variables AJAX sont disponibles
      let ajaxUrl = window.pdfBuilderAjax?.ajaxurl || ajaxurl;

      if (!ajaxUrl) {
        alert('Erreur: Variables AJAX non disponibles. Rechargez la page.');
        return;
      }

      // Obtenir un nonce frais
      console.log('Obtention d\'un nonce frais pour PDF...');
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
      console.log('Nonce frais obtenu pour PDF:', freshNonce);

      // Préparer les données pour l'AJAX
      const formData = new FormData();
      formData.append('action', 'pdf_builder_generate_pdf');
      formData.append('nonce', freshNonce);
      formData.append('elements', JSON.stringify(elements));

      console.log('Envoi requête génération PDF...');

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

      console.log('Réponse reçue:', response.status);
      if (!response.ok) {
        throw new Error('Erreur réseau: ' + response.status);
      }

      const data = await response.json().catch(jsonError => {
        console.error('Erreur parsing JSON:', jsonError);
        throw new Error('Réponse invalide du serveur (pas du JSON)');
      });

      console.log('Données reçues:', data);

      // 🚨🚨🚨 AFFICHAGE DES LOGS DE DEBUG SERVEUR 🚨🚨🚨
      if (data.data && data.data.debug_logs) {
        console.log('🚨 LOGS DE DEBUG SERVEUR ULTRA-VISIBLES:');
        data.data.debug_logs.forEach((log, index) => {
          console.log(`🔥 LOG ${index}: ${log}`);
        });
        console.log('🚨 FIN DES LOGS DE DEBUG SERVEUR 🚨');
      }

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

      console.log('Blob PDF créé, taille:', pdfBlob.size, 'bytes');

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

      console.log('PDF généré et ouvert avec succès');

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
                {renderCanvasContent(elements)}
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