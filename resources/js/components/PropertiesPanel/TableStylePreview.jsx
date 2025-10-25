import React from 'react';
import { getTableStyles } from '../PDFEditor';
import './TableStylePreview.css';

const TableStylePreview = ({ selectedStyle, onStyleSelect }) => {
  const tableStyles = [
    { key: 'default', name: 'Défaut', description: 'Style simple et épuré' },
    { key: 'classic', name: 'Classique', description: 'Style traditionnel avec bordures' },
    { key: 'striped', name: 'Rayé', description: 'Lignes alternées pour une meilleure lisibilité' },
    { key: 'bordered', name: 'Avec bordures', description: 'Bordures complètes autour des cellules' },
    { key: 'minimal', name: 'Minimal', description: 'Design minimaliste sans bordures' },
    { key: 'modern', name: 'Moderne', description: 'Style contemporain avec ombres' },
    { key: 'blue_ocean', name: 'Océan bleu', description: 'Palette bleue apaisante' },
    { key: 'emerald_forest', name: 'Forêt d\'émeraude', description: 'Teintes vertes naturelles' },
    { key: 'sunset_orange', name: 'Orange coucher', description: 'Couleurs chaudes du coucher de soleil' },
    { key: 'royal_purple', name: 'Violet royal', description: 'Palette violette élégante' },
    { key: 'rose_pink', name: 'Rose bonbon', description: 'Teintes roses douces' },
    { key: 'teal_aqua', name: 'Aigue-marine', description: 'Bleu-vert rafraîchissant' }
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
        title={`${(tableStyles.find(s => s.key === styleKey) && tableStyles.find(s => s.key === styleKey).name) || styleKey} - ${(tableStyles.find(s => s.key === styleKey) && tableStyles.find(s => s.key === styleKey).description) || ''}`}
      >
        {/* Header */}
        <div
          style={{
            height: '24px',
            background: `linear-gradient(135deg, ${style.headerBg} 0%, ${style.headerBg} 100%)`,
            borderBottom: style.borderWidth > 0 ? `${style.borderWidth * 0.5}px solid ${style.headerBorder}` : 'none',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}
        >
          <div
            style={{
              fontSize: '7px',
              fontWeight: style.headerFontWeight,
              color: style.headerTextColor,
              textTransform: 'uppercase',
              letterSpacing: '0.5px',
              textAlign: 'center',
              lineHeight: '1'
            }}
          >
            Titre
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
              justifyContent: 'space-between',
              padding: '0 4px'
            }}
          >
            <div
              style={{
                fontSize: '6px',
                color: style.rowTextColor,
                opacity: 0.9,
                fontWeight: '500'
              }}
            >
              Produit A
            </div>
            <div
              style={{
                fontSize: '6px',
                color: style.rowTextColor,
                opacity: 0.7
              }}
            >
              25€
            </div>
          </div>
          <div
            style={{
              flex: 1,
              backgroundColor: style.altRowBg,
              borderBottom: style.borderWidth > 0 ? `${style.borderWidth * 0.3}px solid ${style.rowBorder}` : 'none',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              padding: '0 4px'
            }}
          >
            <div
              style={{
                fontSize: '6px',
                color: style.rowTextColor,
                opacity: 0.9,
                fontWeight: '500'
              }}
            >
              Produit B
            </div>
            <div
              style={{
                fontSize: '6px',
                color: style.rowTextColor,
                opacity: 0.7
              }}
            >
              15€
            </div>
          </div>
          <div
            style={{
              flex: 1,
              backgroundColor: style.rowBg,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontWeight: 'bold'
            }}
          >
            <div
              style={{
                fontSize: '6px',
                color: style.rowTextColor,
                opacity: 0.8
              }}
            >
              Total: 40€
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
      <label style={{ fontWeight: '600', color: '#374151', marginBottom: '4px', display: 'block' }}>
        🎨 Style tableau:
      </label>
      <div className="table-style-preview-grid">
        {tableStyles.map(({ key, name, description }) => (
          <div key={key} className="table-style-preview-item">
            <div
              className={`table-style-preview-container ${selectedStyle === key ? 'selected' : ''}`}
              onClick={() => onStyleSelect(key)}
              title={`${name} - ${description}`}
            >
              {renderTablePreview(key)}
            </div>
            <div className="table-style-name">
              {name}
            </div>
            {(key === 'default' || key === 'modern' || key === 'striped') && (
              <div style={{
                fontSize: '8px',
                color: '#059669',
                fontWeight: '600',
                marginTop: '2px',
                textTransform: 'uppercase',
                letterSpacing: '0.05em'
              }}>
                ★ Populaire
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default TableStylePreview;
