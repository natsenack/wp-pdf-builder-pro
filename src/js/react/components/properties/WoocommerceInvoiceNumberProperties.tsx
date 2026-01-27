import { useState, ReactNode } from 'react';
import { WoocommerceInvoiceNumberElement } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';

// Composant Accordion personnalisé - même style que les autres propriétés
const Accordion = ({ title, children, defaultOpen = false }: {
  title: string;
  children: ReactNode;
  defaultOpen?: boolean;
}) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);

  return (
    <div style={{ marginBottom: '12px', border: '1px solid #ddd', borderRadius: '0', overflow: 'hidden' }}>
      <div
        onClick={() => setIsOpen(!isOpen)}
        style={{
          padding: '8px 10px',
          backgroundColor: '#f0f0f0',
          cursor: 'pointer',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: isOpen ? '1px solid #ddd' : 'none'
        }}
      >
        <h4 style={{ margin: '0', fontSize: '11px', fontWeight: 'bold', color: '#333' }}>
          {title}
        </h4>
        <span style={{
          fontSize: '11px',
          color: '#666',
          transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)',
          transition: 'transform 0.2s ease'
        }}>
          ▼
        </span>
      </div>

      {isOpen && (
        <div style={{ padding: '10px', backgroundColor: '#fff' }}>
          {children}
        </div>
      )}
    </div>
  );
};

interface WoocommerceInvoiceNumberPropertiesProps {
  element: WoocommerceInvoiceNumberElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function WoocommerceInvoiceNumberProperties({
  element,
  onChange,
  activeTab,
  setActiveTab,
}: WoocommerceInvoiceNumberPropertiesProps) {
  const currentTab = activeTab[element.id] || 'fonctionnalites';
  const setCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: currentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: currentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: currentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: currentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: currentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: currentTab === 'positionnement' ? '#fff' : '#333',
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
      {currentTab === 'fonctionnalites' && (
        <>
          <Accordion title="Format du numéro" defaultOpen={true}>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Préfixe
              </label>
              <input
                type="text"
                value={(element.properties?.prefix) || ''}
                onChange={(e) => onChange(element.id, 'properties', { ...element.properties, prefix: e.target.value })}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
                placeholder="Ex: INV-"
              />
            </div>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Suffixe
              </label>
              <input
                type="text"
                value={(element.properties?.suffix) || ''}
                onChange={(e) => onChange(element.id, 'properties', { ...element.properties, suffix: e.target.value })}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
                placeholder="Ex: /2024"
              />
            </div>
          </Accordion>
        </>
      )}

      {/* Onglet Personnalisation */}
      {currentTab === 'personnalisation' && (
        <>
          <Accordion title="Propriétés de texte générales" defaultOpen={true}>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Police
              </label>
              <select
                value={(element.properties?.fontFamily) || 'Arial'}
                onChange={(e) => onChange(element.id, 'properties', { ...element.properties, fontFamily: e.target.value })}
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
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
                <option value="Courier New">Courier New</option>
              </select>
            </div>
            <div style={{ marginBottom: '12px' }}>
              <NumericPropertyInput
                label="Taille"
                value={element.properties?.fontSize}
                defaultValue={12}
                min={8}
                max={72}
                unit="px"
                onChange={(value) => onChange(element.id, 'properties', { ...element.properties, fontSize: value })}
              />
            </div>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Couleur
              </label>
              <input
                type="color"
                value={(element.properties?.color) || '#000000'}
                onChange={(e) => onChange(element.id, 'properties', { ...element.properties, color: e.target.value })}
                style={{
                  width: '100%',
                  height: '32px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  cursor: 'pointer'
                }}
              />
            </div>
          </Accordion>
        </>
      )}

      {/* Onglet Positionnement */}
      {currentTab === 'positionnement' && (
        <Accordion title="Remplissage" defaultOpen={true}>
          <p style={{ fontSize: '11px', color: '#666', marginBottom: '8px' }}>Position et taille gérées par le canevas</p>
        </Accordion>
      )}
    </>
  );
}
