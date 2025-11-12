import React from 'react';
import { Element } from '../../types/elements';

interface ExtendedElement extends Element {
  strokeColor?: string;
  strokeWidth?: number;
}

interface LinePropertiesProps {
  element: ExtendedElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function LineProperties({ element, onChange, activeTab, setActiveTab }: LinePropertiesProps) {
  const lineCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setLineCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Line */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setLineCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: lineCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: lineCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setLineCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: lineCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: lineCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setLineCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: lineCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: lineCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {lineCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Type d&apos;élément
            </label>
            <div style={{ padding: '8px', backgroundColor: '#f8f9fa', borderRadius: '4px', textAlign: 'center' }}>
              Ligne
            </div>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {lineCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Couleur de la ligne
            </label>
            <input
              type="color"
              value={element.strokeColor || '#000000'}
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
              Épaisseur de la ligne <span style={{ color: '#666', fontSize: '10px' }}>({element.strokeWidth || 2}px)</span>
            </label>
            <input
              type="number"
              min="1"
              max="20"
              value={element.strokeWidth || 2}
              onChange={(e) => onChange(element.id, 'strokeWidth', parseInt(e.target.value) || 2)}
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
      {lineCurrentTab === 'positionnement' && (
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
              Longueur <span style={{ color: '#666', fontSize: '10px' }}>({element.width || 100}px)</span>
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