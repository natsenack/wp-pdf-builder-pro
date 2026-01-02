import React, { useMemo } from 'react';

/**
 * Thèmes de tableau préfabriqués
 */
const TABLE_THEMES = {
  default: {
    label: 'Défaut',
    headerBg: '#f8fafc',
    headerText: '#334155',
    headerBorder: '#e2e8f0',
    rowBg: '#ffffff',
    altRowBg: '#fafbfc',
    rowText: '#334155',
    rowBorder: '#f1f5f9',
    borderWidth: 1
  },
  classic: {
    label: 'Classique',
    headerBg: '#1e293b',
    headerText: '#ffffff',
    headerBorder: '#334155',
    rowBg: '#ffffff',
    altRowBg: '#ffffff',
    rowText: '#000000',
    rowBorder: '#334155',
    borderWidth: 1.5
  },
  striped: {
    label: 'Alterné',
    headerBg: '#e0f2fe',
    headerText: '#0c4a6e',
    headerBorder: '#0ea5e9',
    rowBg: '#ffffff',
    altRowBg: '#f8fafc',
    rowText: '#0c4a6e',
    rowBorder: '#f0f9ff',
    borderWidth: 1
  },
  bordered: {
    label: 'Encadré',
    headerBg: '#f8fafc',
    headerText: '#475569',
    headerBorder: '#94a3b8',
    rowBg: '#ffffff',
    altRowBg: '#ffffff',
    rowText: '#475569',
    rowBorder: '#e2e8f0',
    borderWidth: 1
  },
  minimal: {
    label: 'Minimal',
    headerBg: '#ffffff',
    headerText: '#6b7280',
    headerBorder: '#f3f4f6',
    rowBg: '#ffffff',
    altRowBg: '#ffffff',
    rowText: '#6b7280',
    rowBorder: '#f9fafb',
    borderWidth: 0.5
  },
  modern: {
    label: 'Moderne',
    headerBg: '#e9d5ff',
    headerText: '#6b21a8',
    headerBorder: '#a855f7',
    rowBg: '#ffffff',
    altRowBg: '#faf5ff',
    rowText: '#6b21a8',
    rowBorder: '#f3e8ff',
    borderWidth: 1
  },
  blue_ocean: {
    label: 'Océan',
    headerBg: '#dbeafe',
    headerText: '#1e40af',
    headerBorder: '#3b82f6',
    rowBg: '#ffffff',
    altRowBg: '#eff6ff',
    rowText: '#1e40af',
    rowBorder: '#eff6ff',
    borderWidth: 1
  },
  emerald_forest: {
    label: 'Forêt',
    headerBg: '#d1fae5',
    headerText: '#065f46',
    headerBorder: '#10b981',
    rowBg: '#ffffff',
    altRowBg: '#ecfdf5',
    rowText: '#065f46',
    rowBorder: '#ecfdf5',
    borderWidth: 1
  },
  sunset_orange: {
    label: 'Coucher',
    headerBg: '#fed7aa',
    headerText: '#c2410c',
    headerBorder: '#f97316',
    rowBg: '#ffffff',
    altRowBg: '#fff7ed',
    rowText: '#c2410c',
    rowBorder: '#fff7ed',
    borderWidth: 1
  },
  royal_purple: {
    label: 'Royal',
    headerBg: '#e9d5ff',
    headerText: '#7c3aed',
    headerBorder: '#a855f7',
    rowBg: '#ffffff',
    altRowBg: '#faf5ff',
    rowText: '#7c3aed',
    rowBorder: '#faf5ff',
    borderWidth: 1
  },
  rose_pink: {
    label: 'Rose',
    headerBg: '#fce7f3',
    headerText: '#db2777',
    headerBorder: '#f472b6',
    rowBg: '#ffffff',
    altRowBg: '#fdf2f8',
    rowText: '#db2777',
    rowBorder: '#fdf2f8',
    borderWidth: 1
  },
  teal_aqua: {
    label: 'Aigue',
    headerBg: '#ccfbf1',
    headerText: '#0d9488',
    headerBorder: '#14b8a6',
    rowBg: '#ffffff',
    altRowBg: '#f0fdfa',
    rowText: '#0d9488',
    rowBorder: '#f0fdfa',
    borderWidth: 1
  }
};

/**
 * Composant de rendu du tableau de produits
 * Utilise HTML/CSS au lieu de Canvas pour une meilleure maintenabilité
 */
const TablePreview = ({ element = {} }) => {
  const themeKey = element.tableStyle || 'default';
  const theme = TABLE_THEMES[themeKey] || TABLE_THEMES.default;

  // Appliquer les surcharges de couleur simples
  const headerBg = element.tableColorPrimary || theme.headerBg;
  const altRowBg = element.tableColorSecondary || theme.altRowBg;
  const headerBorder = element.tableColorPrimary || theme.headerBorder;

  // Exemple de données de tableau
  const sampleData = {
    headers: ['Produit', 'Qté', 'Prix', 'Total'],
    rows: [
      ['Produit A', '2', '15,99 €', '31,98 €'],
      ['Produit B', '1', '25,50 €', '25,50 €'],
      ['Produit C', '3', '10,00 €', '30,00 €']
    ],
    totals: {
      subtotal: '87,48 €',
      tax: '16,62 €',
      total: '104,10 €'
    }
  };

  const tableStyle = useMemo(
    () => ({
      width: '100%',
      borderCollapse: 'collapse',
      fontSize: '12px',
      fontFamily: 'Arial, sans-serif'
    }),
    []
  );

  const headerCellStyle = useMemo(
    () => ({
      backgroundColor: headerBg,
      color: theme.headerText,
      padding: '8px 12px',
      textAlign: 'left',
      fontWeight: '600',
      border: `${theme.borderWidth}px solid ${headerBorder}`,
      borderBottom: `${theme.borderWidth * 1.5}px solid ${headerBorder}`
    }),
    [headerBg, theme.headerText, theme.borderWidth, headerBorder]
  );

  const rowCellStyle = (isAlt) => ({
    backgroundColor: isAlt ? altRowBg : theme.rowBg,
    color: theme.rowText,
    padding: '6px 12px',
    border: `${theme.borderWidth}px solid ${theme.rowBorder}`,
    textAlign: 'left'
  });

  const totalRowStyle = {
    backgroundColor: altRowBg,
    color: theme.rowText,
    padding: '8px 12px',
    border: `${theme.borderWidth}px solid ${theme.rowBorder}`,
    fontWeight: '600',
    textAlign: 'right'
  };

  return (
    <div style={{ padding: '8px', overflow: 'auto' }}>
      <table style={tableStyle}>
        <thead>
          <tr>
            {sampleData.headers.map((header, idx) => (
              <th key={idx} style={headerCellStyle}>
                {header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {sampleData.rows.map((row, rowIdx) => (
            <tr key={rowIdx}>
              {row.map((cell, cellIdx) => (
                <td key={cellIdx} style={rowCellStyle(rowIdx % 2 !== 0)}>
                  {cell}
                </td>
              ))}
            </tr>
          ))}
          <tr>
            <td colSpan="2" style={totalRowStyle}>
              Sous-total
            </td>
            <td style={totalRowStyle}></td>
            <td style={totalRowStyle}>{sampleData.totals.subtotal}</td>
          </tr>
          <tr>
            <td colSpan="2" style={totalRowStyle}>
              TVA
            </td>
            <td style={totalRowStyle}></td>
            <td style={totalRowStyle}>{sampleData.totals.tax}</td>
          </tr>
          <tr>
            <td colSpan="2" style={totalRowStyle}>
              TOTAL
            </td>
            <td style={totalRowStyle}></td>
            <td style={{ ...totalRowStyle, fontWeight: '700', fontSize: '13px' }}>
              {sampleData.totals.total}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  );
};

export default TablePreview;
