import { BaseElement } from '../../types/elements';
import { useCanvasSettings } from '../../contexts/CanvasSettingsContext';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';
import { ColorPropertyInput } from '../ui/ColorPropertyInput';

interface ExtendedElement extends BaseElement {
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
  const canvasSettings = useCanvasSettings();
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
          <ColorPropertyInput
            label="Couleur de la ligne"
            value={element.strokeColor}
            defaultValue="#000000"
            onChange={(value) => onChange(element.id, 'strokeColor', value)}
          />

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Épaisseur de la ligne"
              value={element.strokeWidth}
              defaultValue={2}
              min={1}
              max={20}
              unit="px"
              onChange={(value) => onChange(element.id, 'strokeWidth', value)}
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

      {/* Onglet Positionnement */}
      {lineCurrentTab === 'positionnement' && (
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
              label="Longueur"
              value={element.width}
              defaultValue={100}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, 'width', value)}
            />
          </div>

          {canvasSettings?.selectionRotationEnabled && (
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
          )}

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Padding interne (px)"
              value={element.padding || 12}
              defaultValue={12}
              min={0}
              max={50}
              onChange={(value) => onChange(element.id, 'padding', value)}
            />
          </div>
        </>
      )}
    </>
  );
}



