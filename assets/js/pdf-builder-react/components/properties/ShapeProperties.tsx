import React from 'react';
import { Element } from '../../types/elements';

interface ExtendedElement extends Element {
  fillColor?: string;
  strokeColor?: string;
  strokeWidth?: number;
  borderRadius?: number;
}

interface ShapePropertiesProps {
  element: ExtendedElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function ShapeProperties({ element, onChange, activeTab, setActiveTab }: ShapePropertiesProps) {
  const shapeCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setShapeCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Shape */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setShapeCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: shapeCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: shapeCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
            border: 'none',
            cursor: 'pointer',
            fontSize: '11px',
            fontWeight: 'bold',
            borderRadius: '3px 3px 0 0',
            minWidth: '0',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
            textOverflow: 'ellipsis'
          }}
          title="Fonctionnalités"
        >
          Fonctionnalités
        </button>
        <button
          onClick={() => setShapeCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: shapeCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: shapeCurrentTab === 'personnalisation' ? '#fff' : '#333',
            border: 'none',
            cursor: 'pointer',
            fontSize: '11px',
            fontWeight: 'bold',
            borderRadius: '3px 3px 0 0',
            minWidth: '0',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
            textOverflow: 'ellipsis'
          }}
          title="Personnalisation"
        >
          Personnalisation
        </button>
        <button
          onClick={() => setShapeCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: shapeCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: shapeCurrentTab === 'positionnement' ? '#fff' : '#333',
            border: 'none',
            cursor: 'pointer',
            fontSize: '11px',
            fontWeight: 'bold',
            borderRadius: '3px 3px 0 0',
            minWidth: '0',
            whiteSpace: 'nowrap',
            overflow: 'hidden',
            textOverflow: 'ellipsis'
          }}
          title="Positionnement"
        >
          Positionnement
        </button>
      </div>

      {/* Onglet Fonctionnalités */}
      {shapeCurrentTab === 'fonctionnalites' && (
        <>
          {element.type === 'circle' && (
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                Forme
              </label>
              <div style={{ padding: '8px', backgroundColor: '#f8f9fa', borderRadius: '4px', textAlign: 'center' }}>
                Cercle
              </div>
            </div>
          )}

          {element.type === 'rectangle' && (
            <>
              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Forme
                </label>
                <div style={{ padding: '8px', backgroundColor: '#f8f9fa', borderRadius: '4px', textAlign: 'center' }}>
                  Rectangle
                </div>
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Rayon des coins <span style={{ color: '#666', fontSize: '10px' }}>({element.borderRadius || 0}px)</span>
                </label>
                <input
                  type="number"
                  min="0"
                  max="50"
                  value={element.borderRadius || 0}
                  onChange={(e) => onChange(element.id, 'borderRadius', parseInt(e.target.value) || 0)}
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
          )}
        </>
      )}

      {/* Onglet Personnalisation */}
      {shapeCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Couleur de remplissage
            </label>
            <input
              type="color"
              value={element.fillColor === 'transparent' ? '#ffffff' : (element.fillColor || '#007bff')}
              onChange={(e) => onChange(element.id, 'fillColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                cursor: 'pointer'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Couleur de bordure
            </label>
            <input
              type="color"
              value={element.strokeColor === 'transparent' ? '#000000' : (element.strokeColor || '#000000')}
              onChange={(e) => onChange(element.id, 'strokeColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                cursor: 'pointer'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Épaisseur de bordure <span style={{ color: '#666', fontSize: '10px' }}>({element.strokeWidth || 1}px)</span>
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={element.strokeWidth || 1}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseInt(e.target.value) || 1)}
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
      )}

      {/* Onglet Positionnement */}
      {shapeCurrentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position X <span style={{ color: '#666', fontSize: '10px' }}>({element.x || 0}px)</span>
            </label>
            <input
              type="number"
              value={element.x || 0}
              onChange={(e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position Y <span style={{ color: '#666', fontSize: '10px' }}>({element.y || 0}px)</span>
            </label>
            <input
              type="number"
              value={element.y || 0}
              onChange={(e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Largeur <span style={{ color: '#666', fontSize: '10px' }}>({element.width || 100}px)</span>
            </label>
            <input
              type="number"
              min="1"
              value={element.width || 100}
              onChange={(e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Hauteur <span style={{ color: '#666', fontSize: '10px' }}>({element.height || 100}px)</span>
            </label>
            <input
              type="number"
              min="1"
              value={element.height || 100}
              onChange={(e) => onChange(element.id, 'height', parseFloat(e.target.value) || 100)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Rotation <span style={{ color: '#666', fontSize: '10px' }}>({element.rotation || 0}°)</span>
            </label>
            <input
              type="number"
              min="-180"
              max="180"
              value={element.rotation || 0}
              onChange={(e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Opacité <span style={{ color: '#666', fontSize: '10px' }}>({Math.round((element.opacity || 1) * 100)}%)</span>
            </label>
            <input
              type="range"
              min="0"
              max="1"
              step="0.1"
              value={element.opacity || 1}
              onChange={(e) => onChange(element.id, 'opacity', parseFloat(e.target.value))}
              style={{ width: '100%' }}
            />
          </div>
        </>
      )}
    </>
  );
}