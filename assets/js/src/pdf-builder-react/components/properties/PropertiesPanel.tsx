import React from 'react';
import { useBuilder } from '../../contexts/builder/BuilderContext.tsx';
import { Element } from '../../types/elements';

interface PropertiesPanelProps {
  className?: string;
}

export function PropertiesPanel({ className }: PropertiesPanelProps) {
  const { state, updateElement, removeElement } = useBuilder();

  const selectedElements = state.elements.filter(el =>
    state.selection.selectedElements.includes(el.id)
  );

  const handlePropertyChange = (elementId: string, property: string, value: any) => {
    updateElement(elementId, { [property]: value });
  };

  const handleDeleteSelected = () => {
    state.selection.selectedElements.forEach(id => {
      removeElement(id);
    });
  };

  if (selectedElements.length === 0) {
    return (
      <div className={`pdf-builder-properties ${className || ''}`} style={{
        padding: '12px',
        backgroundColor: '#f9f9f9',
        border: '1px solid #ddd',
        borderRadius: '4px',
        minHeight: '200px'
      }}>
        <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: 'bold' }}>
          Propriétés
        </h4>
        <p style={{ color: '#999', fontSize: '14px', margin: '0' }}>
          Sélectionnez un élément pour voir ses propriétés
        </p>
      </div>
    );
  }

  return (
    <div className={`pdf-builder-properties ${className || ''}`} style={{
      padding: '12px',
      backgroundColor: '#f9f9f9',
      border: '1px solid #ddd',
      borderRadius: '4px',
      maxHeight: '600px',
      overflowY: 'auto'
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
        <h4 style={{ margin: '0', fontSize: '14px', fontWeight: 'bold' }}>
          Propriétés ({selectedElements.length})
        </h4>
        <button
          onClick={handleDeleteSelected}
          style={{
            padding: '4px 8px',
            border: '1px solid #dc3545',
            borderRadius: '4px',
            backgroundColor: '#dc3545',
            color: '#ffffff',
            cursor: 'pointer',
            fontSize: '12px'
          }}
        >
          🗑️ Supprimer
        </button>
      </div>

      {selectedElements.map(element => (
        <div key={element.id} style={{
          marginBottom: '16px',
          padding: '12px',
          backgroundColor: '#ffffff',
          border: '1px solid #e0e0e0',
          borderRadius: '4px'
        }}>
          <h5 style={{ margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' }}>
            {element.type.charAt(0).toUpperCase() + element.type.slice(1)} - {element.id.slice(0, 8)}
          </h5>

          {/* Propriétés communes */}
          <div style={{ display: 'grid', gap: '8px' }}>
            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Position X
              </label>
              <input
                type="number"
                value={element.x}
                onChange={(e) => handlePropertyChange(element.id, 'x', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Position Y
              </label>
              <input
                type="number"
                value={element.y}
                onChange={(e) => handlePropertyChange(element.id, 'y', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Largeur
              </label>
              <input
                type="number"
                value={element.width}
                onChange={(e) => handlePropertyChange(element.id, 'width', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Hauteur
              </label>
              <input
                type="number"
                value={element.height}
                onChange={(e) => handlePropertyChange(element.id, 'height', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Rotation (°)
              </label>
              <input
                type="number"
                value={element.rotation || 0}
                onChange={(e) => handlePropertyChange(element.id, 'rotation', parseFloat(e.target.value) || 0)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
                Opacité
              </label>
              <input
                type="range"
                min="0"
                max="1"
                step="0.1"
                value={element.opacity || 1}
                onChange={(e) => handlePropertyChange(element.id, 'opacity', parseFloat(e.target.value))}
                style={{ width: '100%' }}
              />
              <span style={{ fontSize: '11px', color: '#666' }}>
                {Math.round((element.opacity || 1) * 100)}%
              </span>
            </div>

            {/* Propriétés spécifiques selon le type */}
            {renderSpecificProperties(element, handlePropertyChange)}
          </div>
        </div>
      ))}
    </div>
  );
}

// Fonction pour rendre les propriétés spécifiques au type d'élément
function renderSpecificProperties(
  element: Element,
  onChange: (elementId: string, property: string, value: any) => void
) {
  switch (element.type) {
    case 'rectangle':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de remplissage
            </label>
            <input
              type="color"
              value={(element as any).fillColor || '#ffffff'}
              onChange={(e) => onChange(element.id, 'fillColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de bordure
            </label>
            <input
              type="color"
              value={(element as any).strokeColor || '#000000'}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur bordure
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={(element as any).strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'circle':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de remplissage
            </label>
            <input
              type="color"
              value={(element as any).fillColor || '#ffffff'}
              onChange={(e) => onChange(element.id, 'fillColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de bordure
            </label>
            <input
              type="color"
              value={(element as any).strokeColor || '#000000'}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur bordure
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={(element as any).strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'text':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Texte
            </label>
            <textarea
              value={(element as any).text || ''}
              onChange={(e) => onChange(element.id, 'text', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px',
                minHeight: '60px',
                resize: 'vertical'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur
            </label>
            <input
              type="color"
              value={(element as any).color || '#000000'}
              onChange={(e) => onChange(element.id, 'color', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Taille police
            </label>
            <input
              type="number"
              min="8"
              max="72"
              value={(element as any).fontSize || 16}
              onChange={(e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 16)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    case 'line':
      return (
        <>
          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur
            </label>
            <input
              type="color"
              value={(element as any).strokeColor || '#000000'}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur
            </label>
            <input
              type="number"
              min="1"
              max="20"
              value={(element as any).strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseFloat(e.target.value) || 1)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      );

    default:
      return null;
  }
}