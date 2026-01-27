import { Element } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';

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
              value={(element.properties?.fillColor ?? element.fillColor) === 'transparent' ? '#ffffff' : ((element.properties?.fillColor ?? element.fillColor) || '#007bff')}
              onChange={(e) => onChange(element.id, 'properties', { ...element.properties, fillColor: e.target.value })}
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
              value={(element.properties?.strokeColor ?? element.strokeColor) === 'transparent' ? '#000000' : ((element.properties?.strokeColor ?? element.strokeColor) || '#000000')}
              onChange={(e) => onChange(element.id, 'properties', { ...element.properties, strokeColor: e.target.value })}
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
            <NumericPropertyInput
              label="Épaisseur de bordure"
              value={element.properties?.strokeWidth ?? element.strokeWidth}
              defaultValue={1}
              min={0}
              max={20}
              unit="px"
              onChange={(value) => onChange(element.id, 'properties', { ...element.properties, strokeWidth: value })}
            />
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {shapeCurrentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Position X"
              value={element.x}
              defaultValue={0}
              unit="px"
              onChange={(value) => onChange(element.id, 'x', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Position Y"
              value={element.y}
              defaultValue={0}
              unit="px"
              onChange={(value) => onChange(element.id, 'y', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Largeur"
              value={element.width}
              defaultValue={100}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, 'width', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Hauteur"
              value={element.height}
              defaultValue={100}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, 'height', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Rotation"
              value={element.rotation}
              defaultValue={0}
              min={-180}
              max={180}
              unit="°"
              onChange={(value) => onChange(element.id, 'rotation', value)}
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



