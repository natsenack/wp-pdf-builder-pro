import React from 'react';

/**
 * Renderer pour les tableaux de produits
 */

// Fonction utilitaire pour convertir RGB en CSS
const rgbToCss = (rgbArray) => {
  if (!Array.isArray(rgbArray) || rgbArray.length !== 3) {
    return 'transparent';
  }
  return `rgb(${rgbArray[0]}, ${rgbArray[1]}, ${rgbArray[2]})`;
};

export const TableRenderer = ({ element, previewData, mode, canvasScale = 1 }) => {
  const {
    x = 0,
    y = 0,
    width = 500,
    height = 200,
    showHeaders = true,
    showBorders = false,
    tableStyle = 'default',
    backgroundColor = 'transparent',
    borderWidth = 1,
    borderColor = '#dddddd',
    borderRadius = 0,
    opacity = 1,
    // Nouvelles propriétés du système Canvas
    showLabels = true,
    headers = ['Produit', 'Qté', 'Prix'],
    dataSource = 'order_items',
    columns = {
      image: true,
      name: true,
      sku: false,
      quantity: true,
      price: true,
      total: true
    },
    showSubtotal = false,
    showShipping = true,
    showTaxes = true,
    showDiscount = false,
    showTotal = false,
    // Propriétés avancées
    rotation = 0,
    scale = 1,
    visible = true,
    shadow = false,
    shadowColor = '#000000',
    shadowOffsetX = 2,
    shadowOffsetY = 2
  } = element;

  // Récupérer les données du tableau
  const elementKey = `product_table_${element.id}`;
  const tableData = previewData[elementKey] || {};

  // Utiliser les données de style du tableau si disponibles
  const tableStyleData = tableData.tableStyleData || {
    header_bg: [248, 249, 250], // #f8f9fa
    header_border: [226, 232, 240], // #e2e8f0
    row_border: [241, 245, 249], // #f1f5f9
    alt_row_bg: [250, 251, 252], // #fafbfc
    headerTextColor: '#000000',
    rowTextColor: '#000000',
    border_width: 1,
    headerFontWeight: 'bold',
    headerFontSize: '12px',
    rowFontSize: '11px'
  };

  // Utiliser les headers depuis l'élément ou les données
  const tableHeaders = headers && headers.length > 0 ? headers : (tableData.headers || []);

  // Générer les headers dynamiquement selon les colonnes activées (fallback)
  const generateHeadersFromColumns = () => {
    const dynamicHeaders = [];
    if (columns.image) dynamicHeaders.push('Image');
    if (columns.name) dynamicHeaders.push('Produit');
    if (columns.sku) dynamicHeaders.push('SKU');
    if (columns.quantity) dynamicHeaders.push('Qté');
    if (columns.price) dynamicHeaders.push('Prix');
    if (columns.total) dynamicHeaders.push('Total');
    return dynamicHeaders;
  };

  // Utiliser les headers dans cet ordre de priorité :
  // 1. Headers personnalisés depuis l'élément
  // 2. Headers depuis les données (générés par SampleDataProvider)
  // 3. Headers générés dynamiquement depuis les colonnes
  const finalHeaders = tableHeaders.length > 0 ? tableHeaders :
                      (tableData.headers && tableData.headers.length > 0 ? tableData.headers :
                      generateHeadersFromColumns());

  const containerStyle = {
    position: 'absolute',
    left: `${x * canvasScale}px`,
    top: `${y * canvasScale}px`,
    width: `${width * canvasScale}px`,
    height: `${height * canvasScale}px`,
    backgroundColor,
    border: borderWidth > 0 ? `${borderWidth}px solid ${borderColor}` : 'none',
    borderRadius: `${borderRadius}px`,
    opacity,
    padding: '8px',
    boxSizing: 'border-box',
    overflow: 'auto',
    display: visible ? 'block' : 'none',
    transform: `rotate(${rotation}deg) scale(${scale})`,
    transformOrigin: 'top left',
    boxShadow: shadow ? `${shadowOffsetX}px ${shadowOffsetY}px 4px ${shadowColor}` : 'none'
  };

  const tableStyleConfig = {
    width: '100%',
    borderCollapse: showBorders ? 'collapse' : 'separate',
    borderSpacing: showBorders ? '0' : '2px',
    fontSize: tableStyleData.rowFontSize,
    fontFamily: 'Arial, sans-serif'
  };

  const headerStyle = {
    backgroundColor: rgbToCss(tableStyleData.header_bg),
    color: tableStyleData.headerTextColor,
    fontWeight: tableStyleData.headerFontWeight,
    fontSize: tableStyleData.headerFontSize,
    padding: '8px',
    textAlign: 'left',
    border: showBorders ? `${tableStyleData.border_width}px solid ${rgbToCss(tableStyleData.header_border)}` : 'none'
  };

  const imageStyle = {
    width: '40px',
    height: '40px',
    objectFit: 'cover',
    borderRadius: '4px'
  };

  const descriptionStyle = {
    maxWidth: '200px',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap'
  };

  const attributesStyle = {
    maxWidth: '150px',
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    fontSize: '10px'
  };

  const cellStyle = {
    padding: '6px 8px',
    border: showBorders ? `${tableStyleData.border_width}px solid ${rgbToCss(tableStyleData.row_border)}` : 'none',
    verticalAlign: 'top',
    fontSize: tableStyleData.rowFontSize,
    color: tableStyleData.rowTextColor
  };

  // Trouver l'index de la colonne "Prix" pour aligner les totaux
  const priceColumnIndex = finalHeaders.findIndex(header =>
    header.toLowerCase().includes('prix') ||
    header.toLowerCase() === 'total' ||
    header.toLowerCase() === 'tva'
  );

  // Déterminer où placer les labels des totaux
  // Si la colonne prix existe et n'est pas la première colonne, utiliser la colonne précédente
  // Sinon, utiliser la colonne prix elle-même avec une mise en page différente
  const labelColumnIndex = (priceColumnIndex > 0) ? priceColumnIndex - 1 : priceColumnIndex;
  const useSeparateLabelColumn = priceColumnIndex > 0;

  return (
    <div
      className="preview-element preview-table-element"
      style={containerStyle}
      data-element-id={element.id}
      data-element-type="product_table"
      data-table-renderer-version="improved-totals-alignment"
    >
      <table style={tableStyleConfig}>
        {showHeaders && finalHeaders && finalHeaders.length > 0 && (
          <thead>
            <tr>
              {finalHeaders.map((header, index) => (
                <th key={index} style={headerStyle}>
                  {header}
                </th>
              ))}
            </tr>
          </thead>
        )}
        <tbody>
          {tableData.rows && tableData.rows.map((row, rowIndex) => {
            // Appliquer les couleurs alternées des lignes
            const isEvenRow = rowIndex % 2 === 0;
            const rowBackgroundColor = isEvenRow
              ? rgbToCss(tableStyleData.alt_row_bg)
              : 'transparent';

            return (
              <tr key={rowIndex} style={{ backgroundColor: rowBackgroundColor }}>
                {row.map((cell, cellIndex) => {
                  const header = finalHeaders[cellIndex] || '';
                  const isImageColumn = header.toLowerCase() === 'image';
                  const isDescriptionColumn = header.toLowerCase().includes('description');
                  const isAttributesColumn = header.toLowerCase() === 'attributs';
                  const isQuantityColumn = header.toLowerCase() === 'qté';
                  const isPriceColumn = header.toLowerCase().includes('prix') || header.toLowerCase() === 'total' || header.toLowerCase() === 'tva' || header.toLowerCase() === 'remise';

                  let cellStyleWithAlignment = { ...cellStyle };

                  // Alignement spécial pour certaines colonnes
                  if (isQuantityColumn) {
                    cellStyleWithAlignment.textAlign = 'center';
                  } else if (isPriceColumn) {
                    cellStyleWithAlignment.textAlign = 'right';
                  }

                  return (
                    <td key={cellIndex} style={cellStyleWithAlignment}>
                      {isImageColumn && cell ? (
                        <img
                          src={cell}
                          alt="Produit"
                          style={imageStyle}
                          onError={(e) => {
                            e.target.style.display = 'none';
                          }}
                        />
                      ) : isDescriptionColumn && cell ? (
                        <span style={descriptionStyle} title={cell}>
                          {cell}
                        </span>
                      ) : isAttributesColumn && cell ? (
                        <span style={attributesStyle} title={cell}>
                          {cell}
                        </span>
                      ) : (
                        cell || '-'
                      )}
                    </td>
                  );
                })}
              </tr>
            );
          })}
        </tbody>
        {/* Lignes de totaux conditionnelles */}
        {(showSubtotal || showShipping || showTaxes || showDiscount || showTotal) &&
         tableData.totals && Object.keys(tableData.totals).length > 0 && (
          <tfoot>
            {showSubtotal && tableData.totals.subtotal && (
              <tr style={{ backgroundColor: rgbToCss(tableStyleData.alt_row_bg) }}>
                {Array.from({ length: finalHeaders.length }, (_, index) => {
                  if (useSeparateLabelColumn && index === labelColumnIndex) {
                    return (
                      <td key={index} style={{ ...cellStyle, fontWeight: 'bold', textAlign: 'right' }}>
                        Sous-total:
                      </td>
                    );
                  } else if (index === priceColumnIndex) {
                    return (
                      <td key={index} style={{
                        ...cellStyle,
                        fontWeight: 'bold',
                        textAlign: 'right',
                        borderTop: '1px solid #dee2e6',
                        ...(useSeparateLabelColumn ? {} : { paddingLeft: '80px' }) // Ajouter de l'espace pour le label si pas de colonne séparée
                      }}>
                        {useSeparateLabelColumn ? tableData.totals.subtotal : `Sous-total: ${tableData.totals.subtotal}`}
                      </td>
                    );
                  } else {
                    return <td key={index} style={cellStyle}></td>;
                  }
                })}
              </tr>
            )}
            {showShipping && tableData.totals.shipping && (
              <tr style={{ backgroundColor: 'transparent' }}>
                {Array.from({ length: finalHeaders.length }, (_, index) => {
                  if (useSeparateLabelColumn && index === labelColumnIndex) {
                    return (
                      <td key={index} style={{ ...cellStyle, fontWeight: 'bold', textAlign: 'right' }}>
                        Frais de port:
                      </td>
                    );
                  } else if (index === priceColumnIndex) {
                    return (
                      <td key={index} style={{
                        ...cellStyle,
                        fontWeight: 'bold',
                        textAlign: 'right',
                        ...(useSeparateLabelColumn ? {} : { paddingLeft: '80px' })
                      }}>
                        {useSeparateLabelColumn ? tableData.totals.shipping : `Frais de port: ${tableData.totals.shipping}`}
                      </td>
                    );
                  } else {
                    return <td key={index} style={cellStyle}></td>;
                  }
                })}
              </tr>
            )}
            {showTaxes && tableData.totals.tax && (
              <tr style={{ backgroundColor: rgbToCss(tableStyleData.alt_row_bg) }}>
                {Array.from({ length: finalHeaders.length }, (_, index) => {
                  if (useSeparateLabelColumn && index === labelColumnIndex) {
                    return (
                      <td key={index} style={{ ...cellStyle, fontWeight: 'bold', textAlign: 'right' }}>
                        TVA:
                      </td>
                    );
                  } else if (index === priceColumnIndex) {
                    return (
                      <td key={index} style={{
                        ...cellStyle,
                        fontWeight: 'bold',
                        textAlign: 'right',
                        ...(useSeparateLabelColumn ? {} : { paddingLeft: '80px' })
                      }}>
                        {useSeparateLabelColumn ? tableData.totals.tax : `TVA: ${tableData.totals.tax}`}
                      </td>
                    );
                  } else {
                    return <td key={index} style={cellStyle}></td>;
                  }
                })}
              </tr>
            )}
            {showDiscount && tableData.totals.discount && (
              <tr style={{ backgroundColor: 'transparent' }}>
                {Array.from({ length: finalHeaders.length }, (_, index) => {
                  if (useSeparateLabelColumn && index === labelColumnIndex) {
                    return (
                      <td key={index} style={{ ...cellStyle, fontWeight: 'bold', textAlign: 'right' }}>
                        Remise:
                      </td>
                    );
                  } else if (index === priceColumnIndex) {
                    return (
                      <td key={index} style={{
                        ...cellStyle,
                        fontWeight: 'bold',
                        textAlign: 'right',
                        ...(useSeparateLabelColumn ? {} : { paddingLeft: '80px' })
                      }}>
                        {useSeparateLabelColumn ? tableData.totals.discount : `Remise: ${tableData.totals.discount}`}
                      </td>
                    );
                  } else {
                    return <td key={index} style={cellStyle}></td>;
                  }
                })}
              </tr>
            )}
            {showTotal && tableData.totals.total && (
              <tr style={{ backgroundColor: rgbToCss(tableStyleData.alt_row_bg) }}>
                {Array.from({ length: finalHeaders.length }, (_, index) => {
                  if (useSeparateLabelColumn && index === labelColumnIndex) {
                    return (
                      <td key={index} style={{
                        ...cellStyle,
                        fontWeight: 'bold',
                        textAlign: 'right',
                        fontSize: '14px',
                        color: '#2563eb',
                        borderTop: '2px solid #2563eb'
                      }}>
                        Total:
                      </td>
                    );
                  } else if (index === priceColumnIndex) {
                    return (
                      <td key={index} style={{
                        ...cellStyle,
                        fontWeight: 'bold',
                        textAlign: 'right',
                        fontSize: '14px',
                        color: '#2563eb',
                        borderTop: '2px solid #2563eb',
                        ...(useSeparateLabelColumn ? {} : { paddingLeft: '80px' })
                      }}>
                        {useSeparateLabelColumn ? tableData.totals.total : `Total: ${tableData.totals.total}`}
                      </td>
                    );
                  } else {
                    return <td key={index} style={{
                      ...cellStyle,
                      borderTop: index === 0 ? '2px solid #2563eb' : 'none'
                    }}></td>;
                  }
                })}
              </tr>
            )}
          </tfoot>
        )}
      </table>

      {/* Message si pas de données */}
      {(!tableData.rows || tableData.rows.length === 0) && (
        <div style={{
          textAlign: 'center',
          color: '#6c757d',
          fontStyle: 'italic',
          padding: '20px'
        }}>
          Aucun produit à afficher
        </div>
      )}
    </div>
  );
};