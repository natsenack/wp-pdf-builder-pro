import React from 'react';
import { DocumentTypeElement } from '../../types/elements';

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
      lineHeight: '1.4'
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
    <div style={{ padding: '16px', backgroundColor: '#ffffff', borderRadius: '8px', marginBottom: '16px' }}>
      {/* Onglets */}
      <div style={{ display: 'flex', marginBottom: '16px', borderBottom: '1px solid #e5e7eb' }}>
        {[
          { key: 'fonctionnalites', label: 'Fonctionnalités' },
          { key: 'personnalisation', label: 'Personnalisation' },
          { key: 'positionnement', label: 'Positionnement' }
        ].map(tab => (
          <button
            key={tab.key}
            onClick={() => setDocumentCurrentTab(tab.key)}
            style={{
              padding: '8px 16px',
              border: 'none',
              backgroundColor: documentCurrentTab === tab.key ? '#007acc' : 'transparent',
              color: documentCurrentTab === tab.key ? '#ffffff' : '#6b7280',
              borderRadius: '4px 4px 0 0',
              cursor: 'pointer',
              fontSize: '12px',
              fontWeight: '500'
            }}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Contenu des onglets */}
      {documentCurrentTab === 'fonctionnalites' && (
        <div>
          <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }}>
            Type de Document
          </h4>

          <div style={{ marginBottom: '16px' }}>
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

          <div style={{ marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }}>
            <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }}>
              Affichage du fond
            </h4>

            <Toggle
              checked={element.showBackground !== false}
              onChange={(checked) => onChange(element.id, 'showBackground', checked)}
              label="Afficher le fond"
              description="Affiche un fond coloré derrière le texte"
            />

            {element.showBackground !== false && (
              <div style={{ marginTop: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Couleur de fond
                </label>
                <input
                  type="color"
                  value={element.backgroundColor || '#e5e7eb'}
                  onChange={(e) => onChange(element.id, 'backgroundColor', e.target.value)}
                  style={{
                    width: '100%',
                    height: '40px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    cursor: 'pointer'
                  }}
                />
              </div>
            )}
          </div>
        </div>
      )}

      {documentCurrentTab === 'personnalisation' && (
        <div>
          <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }}>
            Apparence du Texte
          </h4>

          <div style={{ display: 'grid', gap: '12px' }}>
            <div>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                Taille de police
              </label>
              <input
                type="number"
                value={element.fontSize || 18}
                onChange={(e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 18)}
                min="8"
                max="72"
                style={{
                  width: '100%',
                  padding: '8px 12px',
                  border: '1px solid #d1d5db',
                  borderRadius: '6px',
                  fontSize: '14px'
                }}
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
                Couleur du texte
              </label>
              <input
                type="color"
                value={element.textColor || '#000000'}
                onChange={(e) => onChange(element.id, 'textColor', e.target.value)}
                style={{
                  width: '100%',
                  height: '40px',
                  border: '1px solid #d1d5db',
                  borderRadius: '6px',
                  cursor: 'pointer'
                }}
              />
            </div>
          </div>
        </div>
      )}

      {documentCurrentTab === 'positionnement' && (
        <div>
          <h4 style={{ margin: '0 0 12px 0', fontSize: '14px', fontWeight: '600', color: '#374151' }}>
            Position et Dimensions
          </h4>

          <div style={{ display: 'grid', gap: '12px' }}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Position X
                </label>
                <input
                  type="number"
                  value={element.x}
                  onChange={(e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px'
                  }}
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Position Y
                </label>
                <input
                  type="number"
                  value={element.y}
                  onChange={(e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px'
                  }}
                />
              </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Largeur
                </label>
                <input
                  type="number"
                  value={element.width}
                  onChange={(e) => onChange(element.id, 'width', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px'
                  }}
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: '500', marginBottom: '4px', color: '#6b7280' }}>
                  Hauteur
                </label>
                <input
                  type="number"
                  value={element.height}
                  onChange={(e) => onChange(element.id, 'height', parseFloat(e.target.value) || 0)}
                  style={{
                    width: '100%',
                    padding: '8px 12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '6px',
                    fontSize: '14px'
                  }}
                />
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
