import React from 'react';
import { DocumentTypeElement } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';
import { ColorPropertyInput } from '../ui/ColorPropertyInput';

// Composant Accordion personnalisé
const Accordion = ({ title, children, defaultOpen = false }: {
  title: string;
  children: React.ReactNode;
  defaultOpen?: boolean;
}) => {
  const [isOpen, setIsOpen] = React.useState(defaultOpen);

  return (
    <div style={{ marginBottom: '8px', border: '1px solid #e0e0e0', borderRadius: '4px' }}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        style={{
          width: '100%',
          padding: '8px 12px',
          backgroundColor: '#f8f9fa',
          border: 'none',
          borderRadius: isOpen ? '4px 4px 0 0' : '4px',
          textAlign: 'left',
          cursor: 'pointer',
          fontSize: '12px',
          fontWeight: 'bold',
          color: '#333',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center'
        }}
      >
        <span>{title}</span>
        <span style={{
          transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)',
          transition: 'transform 0.2s ease',
          fontSize: '10px'
        }}>
          ▼
        </span>
      </button>
      {isOpen && (
        <div style={{ padding: '12px', borderTop: '1px solid #e0e0e0' }}>
          {children}
        </div>
      )}
    </div>
  );
};

// Composant Toggle personnalisé
const Toggle = ({ checked, onChange, label, description }: {
  checked: boolean;
  onChange: (checked: boolean) => void;
  label: string;
  description: string;
}) => (
  <div style={{ marginBottom: '12px' }}>
    <div style={{
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: '6px'
    }}>
      <label style={{
        fontSize: '12px',
        fontWeight: 'bold',
        color: '#333',
        flex: 1
      }}>
        {label}
      </label>
      <div
        onClick={() => onChange(!checked)}
        style={{
          position: 'relative',
          width: '44px',
          height: '24px',
          backgroundColor: checked ? '#007bff' : '#ccc',
          borderRadius: '12px',
          cursor: 'pointer',
          transition: 'background-color 0.2s ease',
          border: 'none'
        }}
      >
        <div
          style={{
            position: 'absolute',
            top: '2px',
            left: checked ? '22px' : '2px',
            width: '20px',
            height: '20px',
            backgroundColor: 'white',
            borderRadius: '50%',
            transition: 'left 0.2s ease',
            boxShadow: '0 1px 3px rgba(0,0,0,0.2)'
          }}
        />
      </div>
    </div>
    <div style={{
      fontSize: '11px',
      color: '#666',
      lineHeight: '1.1'
    }}>
      {description}
    </div>
  </div>
);

interface DocumentTypePropertiesProps {
  element: DocumentTypeElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function DocumentTypeProperties({ element, onChange, activeTab, setActiveTab }: DocumentTypePropertiesProps) {
  const documentCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setDocumentCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  const documentTypes = [
    { value: 'FACTURE', label: 'Facture' },
    { value: 'DEVIS', label: 'Devis' },
    { value: 'BON_COMMANDE', label: 'Bon de Commande' },
    { value: 'AVOIR', label: 'Avoir' },
    { value: 'RELEVE', label: 'Relevé' },
    { value: 'CONTRAT', label: 'Contrat' }
  ];

  return (
    <div style={{backgroundColor: '#ffffff', borderRadius: '8px', marginBottom: '16px' }}>
      {/* Onglets */}
      <div style={{ display: 'flex', marginBottom: '16px' }}>
        <button
          onClick={() => setDocumentCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: documentCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: documentCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setDocumentCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: documentCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: documentCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setDocumentCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: documentCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: documentCurrentTab === 'positionnement' ? '#fff' : '#333',
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

      {/* Contenu des onglets */}
      {documentCurrentTab === 'fonctionnalites' && (
        <>
          <Accordion title="Type de Document" defaultOpen={true}>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                Type
              </label>
              <select
                value={element.documentType || 'FACTURE'}
                onChange={(e) => onChange(element.id, 'documentType', e.target.value)}
                style={{
                  width: '100%',
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '6px',
                  fontSize: '14px',
                  backgroundColor: '#ffffff'
                }}
              >
                {documentTypes.map(type => (
                  <option key={type.value} value={type.value}>
                    {type.label}
                  </option>
                ))}
              </select>
            </div>
          </Accordion>

          <Accordion title="Affichage du fond" defaultOpen={false}>
            <Toggle
              checked={element.showBackground !== false}
              onChange={(checked) => onChange(element.id, 'showBackground', checked)}
              label="Afficher le fond"
              description="Affiche un fond coloré derrière le texte"
            />
          </Accordion>
        </>
      )}

      {documentCurrentTab === 'personnalisation' && (
        <>
          <Accordion title="Apparence du Texte" defaultOpen={true}>
            <div style={{ display: 'grid', gap: '12px' }}>
              <div>
                <NumericPropertyInput
                  label="Taille de police"
                  value={element.fontSize}
                  defaultValue={18}
                  min={8}
                  max={72}
                  unit="px"
                  onChange={(value) => onChange(element.id, 'fontSize', value)}
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Style de police
                </label>
                <select
                  value={element.fontWeight || 'bold'}
                  onChange={(e) => onChange(element.id, 'fontWeight', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px',
                    backgroundColor: '#ffffff'
                  }}
                >
                  <option value="normal">Normal</option>
                  <option value="bold">Gras</option>
                  <option value="bolder">Très gras</option>
                  <option value="lighter">Fin</option>
                </select>
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Alignement
                </label>
                <select
                  value={element.textAlign || 'left'}
                  onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px',
                    backgroundColor: '#ffffff'
                  }}
                >
                  <option value="left">Gauche</option>
                  <option value="center">Centre</option>
                  <option value="right">Droite</option>
                </select>
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Alignement vertical
                </label>
                <select
                  value={element.verticalAlign || 'top'}
                  onChange={(e) => onChange(element.id, 'verticalAlign', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px',
                    backgroundColor: '#ffffff'
                  }}
                >
                  <option value="top">Haut</option>
                  <option value="middle">Milieu</option>
                  <option value="bottom">Bas</option>
                </select>
              </div>

              <ColorPropertyInput
                label="Couleur du texte"
                value={element.textColor}
                defaultValue="#000000"
                onChange={(value) => onChange(element.id, 'textColor', value)}
              />
            </div>
          </Accordion>

          {/* Section Couleur de fond - visible seulement si showBackground est activé */}
          {element.showBackground !== false && (
            <Accordion title="Couleur de fond" defaultOpen={false}>
              <ColorPropertyInput
                label="Couleur de fond"
                value={element.backgroundColor as string}
                defaultValue="#e5e7eb"
                onChange={(value) => onChange(element.id, 'backgroundColor', value)}
              />
            </Accordion>
          )}
        </>
      )}

      {documentCurrentTab === 'positionnement' && (
        <>
          <Accordion title="Position et Dimensions" defaultOpen={true}>
            <div style={{ display: 'grid', gap: '12px' }}>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
                <NumericPropertyInput
                  label="Position X"
                  value={element.x}
                  defaultValue={0}
                  unit="px"
                  onChange={(value) => onChange(element.id, 'x', value)}
                />

                <NumericPropertyInput
                  label="Position Y"
                  value={element.y}
                  defaultValue={0}
                  unit="px"
                  onChange={(value) => onChange(element.id, 'y', value)}
                />
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
                <NumericPropertyInput
                  label="Largeur"
                  value={element.width}
                  defaultValue={100}
                  min={1}
                  unit="px"
                  onChange={(value) => onChange(element.id, 'width', value)}
                />

                <NumericPropertyInput
                  label="Hauteur"
                  value={element.height}
                  defaultValue={50}
                  min={1}
                  unit="px"
                  onChange={(value) => onChange(element.id, 'height', value)}
                />
              </div>

              <NumericPropertyInput
                label="Padding interne (px)"
                value={element.padding || 12}
                defaultValue={12}
                min={0}
                max={50}
                onChange={(value) => onChange(element.id, 'padding', value)}
              />
            </div>
          </Accordion>
        </>
      )}
    </div>
  );
}



