import { useState, ReactNode } from 'react';
import { WoocommerceOrderDateElement } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';
import { ColorPropertyInput } from '../ui/ColorPropertyInput';

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

interface WoocommerceOrderDatePropertiesProps {
  element: WoocommerceOrderDateElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function WoocommerceOrderDateProperties({
  element,
  onChange,
  activeTab,
  setActiveTab,
}: WoocommerceOrderDatePropertiesProps) {
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
          <Accordion title="Format de la date" defaultOpen={true}>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Format
              </label>
              <select
                value={element.dateFormat || 'd/m/Y'}
                onChange={(e) => onChange(element.id, 'dateFormat', e.target.value)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              >
                <optgroup label="Formats courts">
                  <option value="d/m/Y">JJ/MM/AAAA (13/02/2026)</option>
                  <option value="m/d/Y">MM/JJ/AAAA (02/13/2026)</option>
                  <option value="Y-m-d">AAAA-MM-JJ (2026-02-13)</option>
                  <option value="d-m-Y">JJ-MM-AAAA (13-02-2026)</option>
                  <option value="d.m.Y">JJ.MM.AAAA (13.02.2026)</option>
                </optgroup>
                <optgroup label="Formats longs">
                  <option value="j F Y">J Mois AAAA (13 février 2026)</option>
                  <option value="l j F Y">Jour J Mois AAAA (jeudi 13 février 2026)</option>
                  <option value="F j, Y">Mois J, AAAA (février 13, 2026)</option>
                  <option value="D, M j, Y">Jour abrégé, Mois abrégé J, AAAA (jeu, fév 13, 2026)</option>
                </optgroup>
              </select>
            </div>
            <div style={{ marginBottom: '12px' }}>
              <input
                type="checkbox"
                checked={element.showTime || false}
                onChange={(e) => onChange(element.id, 'showTime', e.target.checked)}
                id={`showtime-${element.id}`}
                style={{ marginRight: '8px', cursor: 'pointer' }}
              />
              <label htmlFor={`showtime-${element.id}`} style={{ fontSize: '11px', fontWeight: '500', cursor: 'pointer' }}>
                Afficher l'heure
              </label>
            </div>
          </Accordion>

          <Accordion title="Label" defaultOpen={false}>
            <div style={{ marginBottom: '12px' }}>
              <input
                type="checkbox"
                checked={element.showLabel !== false}
                onChange={(e) => onChange(element.id, 'showLabel', e.target.checked)}
                id={`showlabel-${element.id}`}
                style={{ marginRight: '8px', cursor: 'pointer' }}
              />
              <label htmlFor={`showlabel-${element.id}`} style={{ fontSize: '11px', fontWeight: '500', cursor: 'pointer' }}>
                Afficher le label
              </label>
            </div>

            {element.showLabel !== false && (
              <>
                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                    Texte du label
                  </label>
                  <input
                    type="text"
                    value={element.labelText || 'Date de la facture :'}
                    onChange={(e) => onChange(element.id, 'labelText', e.target.value)}
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
                  <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                    Position du label
                  </label>
                  <select
                    value={element.labelPosition || 'left'}
                    onChange={(e) => onChange(element.id, 'labelPosition', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '4px 8px',
                      border: '1px solid #ccc',
                      borderRadius: '3px',
                      fontSize: '12px'
                    }}
                  >
                    <option value="top">Au-dessus</option>
                    <option value="left">À gauche</option>
                    <option value="bottom">En-dessous</option>
                    <option value="right">À droite</option>
                  </select>
                </div>

                <div style={{ marginBottom: '12px' }}>
                  <NumericPropertyInput
                    label="Espacement"
                    value={element.labelSpacing}
                    defaultValue={8}
                    min={0}
                    max={50}
                    unit="px"
                    onChange={(value) => onChange(element.id, 'labelSpacing', value)}
                  />
                </div>

                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                    Police du label
                  </label>
                  <select
                    value={element.labelFontFamily || element.fontFamily || 'Arial'}
                    onChange={(e) => onChange(element.id, 'labelFontFamily', e.target.value)}
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
                    label="Taille du label"
                    value={element.labelFontSize}
                    defaultValue={element.fontSize || 12}
                    min={8}
                    max={72}
                    unit="px"
                    onChange={(value) => onChange(element.id, 'labelFontSize', value)}
                  />
                </div>

                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                    Graisse du label
                  </label>
                  <select
                    value={element.labelFontWeight || 'normal'}
                    onChange={(e) => onChange(element.id, 'labelFontWeight', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '4px 8px',
                      border: '1px solid #ccc',
                      borderRadius: '3px',
                      fontSize: '12px'
                    }}
                  >
                    <option value="normal">Normal</option>
                    <option value="bold">Gras</option>
                    <option value="lighter">Léger</option>
                  </select>
                </div>

                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                    Style du label
                  </label>
                  <select
                    value={element.labelFontStyle || 'normal'}
                    onChange={(e) => onChange(element.id, 'labelFontStyle', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '4px 8px',
                      border: '1px solid #ccc',
                      borderRadius: '3px',
                      fontSize: '12px'
                    }}
                  >
                    <option value="normal">Normal</option>
                    <option value="italic">Italique</option>
                  </select>
                </div>

                <ColorPropertyInput
                  label="Couleur du label"
                  value={element.labelColor || element.color || '#000000'}
                  defaultValue="#000000"
                  onChange={(value) => onChange(element.id, 'labelColor', value)}
                />
              </>
            )}
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
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
                <option value="Courier New">Courier New</option>
              </select>
            </div>
            <div style={{ marginBottom: '12px' }}>
              <NumericPropertyInput
                label="Taille"
                value={element.fontSize}
                defaultValue={12}
                min={8}
                max={72}
                unit="px"
                onChange={(value) => onChange(element.id, 'fontSize', value)}
              />
            </div>
            <ColorPropertyInput
              label="Couleur"
              value={element.color || '#000000'}
              defaultValue="#000000"
              onChange={(value) => onChange(element.id, 'color', value)}
            />
          </Accordion>
        </>
      )}

      {/* Onglet Positionnement */}
      {currentTab === 'positionnement' && (
        <>
          <Accordion title="Alignement du contenu" defaultOpen={true}>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Alignement horizontal
              </label>
              <select
                value={element.textAlign || 'left'}
                onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  fontSize: '11px',
                  border: '1px solid #ddd',
                  borderRadius: '0'
                }}
              >
                <option value="left">Gauche</option>
                <option value="center">Centre</option>
                <option value="right">Droite</option>
              </select>
            </div>

            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Alignement vertical
              </label>
              <select
                value={element.verticalAlign || 'top'}
                onChange={(e) => onChange(element.id, 'verticalAlign', e.target.value as 'top' | 'middle' | 'bottom')}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  fontSize: '11px',
                  border: '1px solid #ddd',
                  borderRadius: '0'
                }}
              >
                <option value="top">Haut</option>
                <option value="middle">Milieu</option>
                <option value="bottom">Bas</option>
              </select>
            </div>
          </Accordion>

          <Accordion title="Position" defaultOpen={false}>
            <p style={{ fontSize: '11px', color: '#666', marginBottom: '0' }}>Position et taille gérées par le canevas</p>
          </Accordion>
        </>
      )}
    </>
  );
}
