import React from 'react';
import { Element } from '../../types/elements';

interface ElementPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
}

export function ElementProperties({ element, onChange }: ElementPropertiesProps) {
  return (
    <>
      {/* Propriétés communes à tous les éléments */}
      <div style={{ marginBottom: '12px' }}>
        <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
          Largeur <span style={{ color: '#666', fontSize: '10px' }}>({((element.width || 100) * 1).toFixed(1)}px)</span>
        </label>
        <input
          type="number"
          min="1"
          step="0.1"
          value={element.width || 100}
          onChange={(e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100)}
          style={{
            width: '100%',
            padding: '4px 8px',
            border: '1px solid #ccc',
            borderRadius: '3px',
            fontSize: '12px'
          }}
          placeholder="Valeur en pixels"
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la largeur en pixels</small>
      </div>

      <div style={{ marginBottom: '12px' }}>
        <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
          Hauteur <span style={{ color: '#666', fontSize: '10px' }}>({((element.height || 50) * 1).toFixed(1)}px)</span>
        </label>
        <input
          type="number"
          min="1"
          step="0.1"
          value={element.height || 50}
          onChange={(e) => onChange(element.id, 'height', parseFloat(e.target.value) || 50)}
          style={{
            width: '100%',
            padding: '4px 8px',
            border: '1px solid #ccc',
            borderRadius: '3px',
            fontSize: '12px'
          }}
          placeholder="Valeur en pixels"
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la hauteur en pixels</small>
      </div>

      <div style={{ marginBottom: '12px' }}>
        <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
          Position X <span style={{ color: '#666', fontSize: '10px' }}>({((element.x || 0) * 1).toFixed(1)}px)</span>
        </label>
        <input
          type="number"
          min="0"
          step="0.1"
          value={element.x || 0}
          onChange={(e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0)}
          style={{
            width: '100%',
            padding: '4px 8px',
            border: '1px solid #ccc',
            borderRadius: '3px',
            fontSize: '12px'
          }}
          placeholder="Valeur en pixels"
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la position en pixels</small>
      </div>

      <div style={{ marginBottom: '12px' }}>
        <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
          Position Y <span style={{ color: '#666', fontSize: '10px' }}>({((element.y || 0) * 1).toFixed(1)}px)</span>
        </label>
        <input
          type="number"
          min="0"
          step="0.1"
          value={element.y || 0}
          onChange={(e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0)}
          style={{
            width: '100%',
            padding: '4px 8px',
            border: '1px solid #ccc',
            borderRadius: '3px',
            fontSize: '12px'
          }}
          placeholder="Valeur en pixels"
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la position en pixels</small>
      </div>

      {/* Propriétés spécifiques selon le type d'élément */}
      {element.type === 'rectangle' && (
        <>
          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de fond
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

          <div style={{ marginBottom: '12px' }}>
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur de bordure
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={(element as any).strokeWidth || 1}
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Rayon des coins
            </label>
            <input
              type="number"
              min="0"
              max="50"
              value={(element as any).borderRadius || 0}
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

      {element.type === 'circle' && (
        <>
          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de fond
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

          <div style={{ marginBottom: '12px' }}>
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur de bordure
            </label>
            <input
              type="number"
              min="0"
              max="20"
              value={(element as any).strokeWidth || 1}
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

      {element.type === 'text' && (
        <>
          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Texte
            </label>
            <textarea
              value={(element as any).text || ''}
              onChange={(e) => onChange(element.id, 'text', e.target.value)}
              rows={3}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px',
                resize: 'vertical'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Taille de police
            </label>
            <input
              type="number"
              min="8"
              max="72"
              value={(element as any).fontSize || 12}
              onChange={(e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 12)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur du texte
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement
            </label>
            <select
              value={(element as any).textAlign || 'left'}
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
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Police
            </label>
            <select
              value={(element as any).fontFamily || 'Arial'}
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
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Style du texte
            </label>
            <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
              <label style={{ display: 'flex', alignItems: 'center', fontSize: '11px' }}>
                <input
                  type="checkbox"
                  checked={(element as any).bold || false}
                  onChange={(e) => onChange(element.id, 'bold', e.target.checked)}
                  style={{ marginRight: '4px' }}
                />
                Gras
              </label>
              <label style={{ display: 'flex', alignItems: 'center', fontSize: '11px' }}>
                <input
                  type="checkbox"
                  checked={(element as any).italic || false}
                  onChange={(e) => onChange(element.id, 'italic', e.target.checked)}
                  style={{ marginRight: '4px' }}
                />
                Italique
              </label>
              <label style={{ display: 'flex', alignItems: 'center', fontSize: '11px' }}>
                <input
                  type="checkbox"
                  checked={(element as any).underline || false}
                  onChange={(e) => onChange(element.id, 'underline', e.target.checked)}
                  style={{ marginRight: '4px' }}
                />
                Souligné
              </label>
            </div>
          </div>
        </>
      )}

      {element.type === 'image' && (
        <>
          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              URL de l'image
            </label>
            <input
              type="text"
              value={(element as any).src || ''}
              onChange={(e) => onChange(element.id, 'src', e.target.value)}
              placeholder="https://example.com/image.jpg"
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Ajustement
            </label>
            <select
              value={(element as any).objectFit || 'contain'}
              onChange={(e) => onChange(element.id, 'objectFit', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="contain">Contenir</option>
              <option value="cover">Couvrir</option>
              <option value="fill">Remplir</option>
              <option value="none">Aucun</option>
              <option value="scale-down">Réduire</option>
            </select>
          </div>
        </>
      )}
    </>
  );
}