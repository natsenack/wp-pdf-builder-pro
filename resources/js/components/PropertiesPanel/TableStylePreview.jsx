import React from 'react';
import { getTableStyles } from '../PDFEditor';
import './TableStylePreview.css';

const TableStylePreview = ({ selectedStyle, onStyleSelect }) => {
  const tableStyles = [
    { key: 'default', name: 'Défaut' },
    { key: 'classic', name: 'Classique' },
    { key: 'striped', name: 'Rayé' },
    { key: 'bordered', name: 'Avec bordures' },
    { key: 'minimal', name: 'Minimal' },
    { key: 'modern', name: 'Moderne' },
    { key: 'blue_ocean', name: 'Océan bleu' },
    { key: 'emerald_forest', name: 'Forêt d\'émeraude' },
    { key: 'sunset_orange', name: 'Orange coucher' },
    { key: 'royal_purple', name: 'Violet royal' },
    { key: 'rose_pink', name: 'Rose bonbon' },
    { key: 'teal_aqua', name: 'Aigue-marine' }
  ];

  const renderTablePreview = (styleKey) => {
    const style = getTableStyles(styleKey);

    return (
      <div
        className="table-style-preview-thumbnail"
        style={{
          borderRadius: `${style.borderRadius * 0.3}px`,
          backgroundColor: style.rowBg,
          border: style.borderWidth > 0 ? `${style.borderWidth * 0.5}px solid ${style.rowBorder}` : '1px solid #e2e8f0',
          boxShadow: style.shadowBlur > 0 ? `0 1px ${style.shadowBlur * 0.3}px ${style.shadowColor}` : 'none'
        }}
        onClick={() => onStyleSelect(styleKey)}
      >
        {/* Header */}
        <div
          style={{
            height: '20px',
            background: `linear-gradient(135deg, ${style.headerBg} 0%, ${style.headerBg} 100%)`,
            borderBottom: style.borderWidth > 0 ? `${style.borderWidth * 0.5}px solid ${style.headerBorder}` : 'none'
          }}
        >
          <div
            style={{
              fontSize: '6px',
              fontWeight: style.headerFontWeight,
              color: style.headerTextColor,
              textTransform: 'uppercase',
              letterSpacing: '0.5px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              height: '100%'
            }}
          >
            Header
          </div>
        </div>

        {/* Rows */}
        <div style={{ flex: 1, display: 'flex', flexDirection: 'column' }}>
          <div
            style={{
              flex: 1,
              backgroundColor: style.rowBg,
              borderBottom: style.borderWidth > 0 ? `${style.borderWidth * 0.3}px solid ${style.rowBorder}` : 'none',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          >
            <div
              style={{
                fontSize: '5px',
                color: style.rowTextColor,
                opacity: 0.8
              }}
            >
              Row 1
            </div>
          </div>
          <div
            style={{
              flex: 1,
              backgroundColor: style.altRowBg,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          >
            <div
              style={{
                fontSize: '5px',
                color: style.rowTextColor,
                opacity: 0.8
              }}
            >
              Row 2
            </div>
          </div>
        </div>

        {/* Selection indicator */}
        {selectedStyle === styleKey && (
          <div className="table-style-selection-indicator" />
        )}
      </div>
    );
  };

  return (
    <div className="property-row">
      <label>Style tableau:</label>
      <div className="table-style-preview-grid">
        {tableStyles.map(({ key, name }) => (
          <div key={key} className="table-style-preview-item">
            <div
              className={`table-style-preview-container ${selectedStyle === key ? 'selected' : ''}`}
              onClick={() => onStyleSelect(key)}
            >
              {renderTablePreview(key)}
            </div>
            <div className="table-style-name">
              {name}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default TableStylePreview;