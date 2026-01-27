import { Element } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';
import { ColorPropertyInput } from '../ui/ColorPropertyInput';

interface ElementPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: unknown) => void;
}

export function ElementProperties({ element, onChange }: ElementPropertiesProps) {
  return (
    <>
      {/* Propriétés communes à tous les éléments */}
      <div style={{ marginBottom: '12px' }}>
        <NumericPropertyInput
          label="Largeur"
          value={element.width}
          defaultValue={100}
          min={1}
          step={0.1}
          unit="px"
          onChange={(value) => onChange(element.id, 'width', value)}
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la largeur en pixels</small>
      </div>

      <div style={{ marginBottom: '12px' }}>
        <NumericPropertyInput
          label="Hauteur"
          value={element.height}
          defaultValue={50}
          min={1}
          step={0.1}
          unit="px"
          onChange={(value) => onChange(element.id, 'height', value)}
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la hauteur en pixels</small>
      </div>

      <div style={{ marginBottom: '12px' }}>
        <NumericPropertyInput
          label="Position X"
          value={element.x}
          defaultValue={0}
          min={0}
          step={0.1}
          unit="px"
          onChange={(value) => onChange(element.id, 'x', value)}
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la position en pixels</small>
      </div>

      <div style={{ marginBottom: '12px' }}>
        <NumericPropertyInput
          label="Position Y"
          value={element.y}
          defaultValue={0}
          min={0}
          step={0.1}
          unit="px"
          onChange={(value) => onChange(element.id, 'y', value)}
        />
        <small style={{ color: '#999', display: 'block', marginTop: '2px' }}>Entrer la position en pixels</small>
      </div>

      {/* Propriétés spécifiques selon le type d'élément */}
      {element.type === 'rectangle' && (
        <>
          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <ColorPropertyInput
            label="Couleur de fond"
            value={(element.properties?.fillColor ?? element.fillColor)}
            defaultValue="#ffffff"
            onChange={(value) => onChange(element.id, 'properties', { ...element.properties, fillColor: value })}
          />

          <ColorPropertyInput
            label="Couleur de bordure"
            value={element.strokeColor}
            defaultValue="#000000"
            onChange={(value) => onChange(element.id, 'strokeColor', value)}
          />

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Épaisseur de bordure"
              value={element.strokeWidth}
              defaultValue={1}
              min={0}
              max={20}
              unit="px"
              onChange={(value) => onChange(element.id, 'strokeWidth', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Rayon des coins"
              value={element.borderRadius}
              defaultValue={0}
              min={0}
              max={50}
              unit="px"
              onChange={(value) => onChange(element.id, 'borderRadius', value)}
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
              value={(element.properties?.fillColor ?? element.fillColor) || '#ffffff'}
              onChange={(e) => onChange(element.id, 'properties', { ...element.properties, fillColor: e.target.value })}
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
              value={element.strokeColor || '#000000'}
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
            <NumericPropertyInput
              label="Épaisseur de bordure"
              value={element.strokeWidth}
              defaultValue={1}
              min={0}
              max={20}
              unit="px"
              onChange={(value) => onChange(element.id, 'strokeWidth', value)}
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
              value={element.text || ''}
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
            <NumericPropertyInput
              label="Taille de police"
              value={element.fontSize}
              defaultValue={12}
              min={8}
              max={72}
              unit="px"
              onChange={(value) => onChange(element.id, 'fontSize', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <ColorPropertyInput
              label="Couleur du texte"
              value={element.color || '#000000'}
              defaultValue="#000000"
              onChange={(value) => onChange(element.id, 'color', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement
            </label>
            <select
              value={element.textAlign || 'left'}
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
                  checked={element.bold || false}
                  onChange={(e) => onChange(element.id, 'bold', e.target.checked)}
                  style={{ marginRight: '4px' }}
                />
                Gras
              </label>
              <label style={{ display: 'flex', alignItems: 'center', fontSize: '11px' }}>
                <input
                  type="checkbox"
                  checked={element.italic || false}
                  onChange={(e) => onChange(element.id, 'italic', e.target.checked)}
                  style={{ marginRight: '4px' }}
                />
                Italique
              </label>
              <label style={{ display: 'flex', alignItems: 'center', fontSize: '11px' }}>
                <input
                  type="checkbox"
                  checked={element.underline || false}
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
              URL de l&apos;image
            </label>
            <input
              type="text"
              value={element.src || ''}
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
              value={element.objectFit || 'contain'}
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



