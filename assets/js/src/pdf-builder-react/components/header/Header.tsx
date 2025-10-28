import React, { useState } from 'react';

interface HeaderProps {
  templateName: string;
  isNewTemplate: boolean;
  isModified: boolean;
  isSaving: boolean;
  isEditingExistingTemplate: boolean;
  onSave: () => void;
  onPreview: () => void;
  onNewTemplate: () => void;
}

export function Header({
  templateName,
  isNewTemplate,
  isModified,
  isSaving,
  isEditingExistingTemplate,
  onSave,
  onPreview,
  onNewTemplate
}: HeaderProps) {
  const [hoveredButton, setHoveredButton] = useState<string | null>(null);

  const buttonBaseStyles = {
    padding: '10px 16px',
    border: 'none',
    borderRadius: '6px',
    cursor: 'pointer',
    fontSize: '14px',
    fontWeight: '500',
    transition: 'all 0.2s ease',
    display: 'flex',
    alignItems: 'center',
    gap: '6px',
    whiteSpace: 'nowrap' as const
  };

  const primaryButtonStyles = {
    ...buttonBaseStyles,
    backgroundColor: '#4CAF50',
    color: '#fff',
    boxShadow: hoveredButton === 'save' ? '0 4px 12px rgba(76, 175, 80, 0.3)' : 'none',
    transform: hoveredButton === 'save' ? 'translateY(-2px)' : 'translateY(0)'
  };

  const secondaryButtonStyles = {
    ...buttonBaseStyles,
    backgroundColor: '#fff',
    border: '1px solid #ddd',
    color: '#333',
    boxShadow: hoveredButton === 'preview' || hoveredButton === 'new' ? '0 2px 8px rgba(0, 0, 0, 0.1)' : 'none',
    transform: hoveredButton === 'preview' || hoveredButton === 'new' ? 'translateY(-1px)' : 'translateY(0)'
  };

  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: '16px',
      backgroundColor: '#ffffff',
      borderBottom: '2px solid #e0e0e0',
      borderRadius: '8px 8px 0 0',
      boxShadow: '0 2px 8px rgba(0, 0, 0, 0.05)',
      gap: '16px'
    }}>
      {/* Left Section - Title and Status */}
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        minWidth: 0,
        flex: 1
      }}>
        <div style={{
          display: 'flex',
          alignItems: 'baseline',
          gap: '12px',
          minWidth: 0
        }}>
          <h2 style={{
            margin: 0,
            fontSize: '20px',
            fontWeight: '600',
            color: '#1a1a1a',
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap'
          }}>
            {templateName || 'Sans titre'}
          </h2>

          {/* Status Badges */}
          <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            flexShrink: 0
          }}>
            {isModified && (
              <span style={{
                fontSize: '12px',
                padding: '4px 10px',
                backgroundColor: '#fff3cd',
                color: '#856404',
                borderRadius: '4px',
                fontWeight: '500',
                border: '1px solid #ffeaa7',
                display: 'flex',
                alignItems: 'center',
                gap: '4px'
              }}>
                <span style={{ fontSize: '16px' }}>●</span>
                Modifié
              </span>
            )}
            {isNewTemplate && (
              <span style={{
                fontSize: '12px',
                padding: '4px 10px',
                backgroundColor: '#d1ecf1',
                color: '#0c5460',
                borderRadius: '4px',
                fontWeight: '500',
                border: '1px solid #bee5eb'
              }}>
                Nouveau
              </span>
            )}
            {isSaving && (
              <span style={{
                fontSize: '12px',
                padding: '4px 10px',
                backgroundColor: '#e8f4f8',
                color: '#0056b3',
                borderRadius: '4px',
                fontWeight: '500',
                border: '1px solid #b8daff',
                display: 'flex',
                alignItems: 'center',
                gap: '4px'
              }}>
                <span style={{
                  display: 'inline-block',
                  animation: 'spin 1s linear infinite',
                  transformOrigin: 'center'
                }}>
                  ⟳
                </span>
                Enregistrement...
              </span>
            )}
          </div>
        </div>
      </div>

      {/* Right Section - Action Buttons */}
      <div style={{
        display: 'flex',
        gap: '10px',
        flexShrink: 0,
        alignItems: 'center'
      }}>
        <button
          onClick={onNewTemplate}
          onMouseEnter={() => setHoveredButton('new')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Créer un nouveau modèle"
        >
          <span>➕</span>
          <span>Nouveau</span>
        </button>

        <button
          onClick={onPreview}
          onMouseEnter={() => setHoveredButton('preview')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...secondaryButtonStyles,
            opacity: isSaving ? 0.6 : 1,
            pointerEvents: isSaving ? 'none' : 'auto'
          }}
          title="Aperçu du modèle"
        >
          <span>👁️</span>
          <span>Aperçu</span>
        </button>

        <div style={{ width: '1px', height: '24px', backgroundColor: '#e0e0e0' }} />

        <button
          onClick={onSave}
          disabled={isSaving || !isModified}
          onMouseEnter={() => setHoveredButton('save')}
          onMouseLeave={() => setHoveredButton(null)}
          style={{
            ...primaryButtonStyles,
            opacity: (isSaving || !isModified) ? 0.6 : 1,
            pointerEvents: (isSaving || !isModified) ? 'none' : 'auto'
          }}
          title={`isModified: ${isModified}, isSaving: ${isSaving}, isEditingExistingTemplate: ${isEditingExistingTemplate}`}
        >
          <span>{isSaving ? '⟳' : '💾'}</span>
          <span>{isSaving ? 'Enregistrement...' : (isEditingExistingTemplate ? 'Modifier' : 'Enregistrer')}</span>
        </button>
      </div>

      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}
