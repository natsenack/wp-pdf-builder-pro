import React from 'react';
import { Element } from '../../types/elements';

interface ExtendedElement extends Element {
  verticalAlign?: string;
  textColor?: string;
  backgroundColor?: string;
  fontWeight?: string;
  fontStyle?: string;
  textDecoration?: string;
  textAlign?: string;
  fontFamily?: string;
  fontSize?: number;
  align?: string;
  color?: string;
}

interface TextPropertiesProps {
  element: ExtendedElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function TextProperties({ element, onChange, activeTab, setActiveTab }: TextPropertiesProps) {
  const textCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setTextCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Text */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setTextCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: textCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: textCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setTextCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: textCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: textCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setTextCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: textCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: textCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {textCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Contenu du texte
            </label>
            <textarea
              value={element.text || ''}
              onChange={(e) => onChange(element.id, 'text', e.target.value)}
              style={{
                width: '100%',
                minHeight: '60px',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px',
                fontFamily: 'monospace',
                resize: 'vertical'
              }}
              placeholder="Entrez votre texte ici..."
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Alignement horizontal
            </label>
            <select
              value={element.textAlign || element.align || 'left'}
              onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="left">Gauche</option>
              <option value="center">Centre</option>
              <option value="right">Droite</option>
              <option value="justify">Justifié</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Alignement vertical
            </label>
            <select
              value={element.verticalAlign || 'top'}
              onChange={(e) => onChange(element.id, 'verticalAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="top">Haut</option>
              <option value="middle">Milieu</option>
              <option value="bottom">Bas</option>
            </select>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {textCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Police
            </label>
            <select
              value={element.fontFamily || 'Arial'}
              onChange={(e) => onChange(element.id, 'fontFamily', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="Arial">Arial</option>
              <option value="Helvetica">Helvetica</option>
              <option value="Times New Roman">Times New Roman</option>
              <option value="Courier New">Courier New</option>
              <option value="Georgia">Georgia</option>
              <option value="Verdana">Verdana</option>
              <option value="Tahoma">Tahoma</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Taille de police <span style={{ color: '#666', fontSize: '10px' }}>({element.fontSize || 16}px)</span>
            </label>
            <input
              type="number"
              min="6"
              max="72"
              value={element.fontSize || 16}
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Style de police
            </label>
            <div style={{ display: 'flex', gap: '4px', flexWrap: 'wrap' }}>
              <button
                onClick={() => onChange(element.id, 'fontWeight', element.fontWeight === 'bold' ? 'normal' : 'bold')}
                style={{
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  backgroundColor: element.fontWeight === 'bold' ? '#007bff' : '#f8f9fa',
                  color: element.fontWeight === 'bold' ? '#fff' : '#333',
                  cursor: 'pointer',
                  fontSize: '11px',
                  fontWeight: 'bold'
                }}
              >
                B
              </button>
              <button
                onClick={() => onChange(element.id, 'fontStyle', element.fontStyle === 'italic' ? 'normal' : 'italic')}
                style={{
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  backgroundColor: element.fontStyle === 'italic' ? '#007bff' : '#f8f9fa',
                  color: element.fontStyle === 'italic' ? '#fff' : '#333',
                  cursor: 'pointer',
                  fontSize: '11px',
                  fontStyle: 'italic'
                }}
              >
                I
              </button>
              <button
                onClick={() => onChange(element.id, 'textDecoration', element.textDecoration === 'underline' ? 'none' : 'underline')}
                style={{
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  backgroundColor: element.textDecoration === 'underline' ? '#007bff' : '#f8f9fa',
                  color: element.textDecoration === 'underline' ? '#fff' : '#333',
                  cursor: 'pointer',
                  fontSize: '11px',
                  textDecoration: 'underline'
                }}
              >
                U
              </button>
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Couleur du texte
            </label>
            <input
              type="color"
              value={element.textColor || element.color || '#000000'}
              onChange={(e) => onChange(element.id, 'textColor', e.target.value)}
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
              Couleur de fond
            </label>
            <input
              type="color"
              value={element.backgroundColor === 'transparent' ? '#ffffff' : (element.backgroundColor || '#ffffff')}
              onChange={(e) => onChange(element.id, 'backgroundColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                cursor: 'pointer'
              }}
            />
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {textCurrentTab === 'positionnement' && (
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
              Hauteur <span style={{ color: '#666', fontSize: '10px' }}>({element.height || 50}px)</span>
            </label>
            <input
              type="number"
              min="1"
              value={element.height || 50}
              onChange={(e) => onChange(element.id, 'height', parseFloat(e.target.value) || 50)}
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