import React from 'react';
import { OrderNumberElement } from '../../types/elements';

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

interface OrderNumberPropertiesProps {
  element: OrderNumberElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function OrderNumberProperties({ element, onChange, activeTab, setActiveTab }: OrderNumberPropertiesProps) {
  const currentTab = activeTab[element.id] || 'fonctionnalites';
  const setCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Order Number */}
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
          {/* Section Éléments principaux */}
          <div style={{ marginBottom: '16px' }}>
            <div style={{
              fontSize: '12px',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '8px',
              padding: '4px 8px',
              backgroundColor: '#f8f9fa',
              borderRadius: '3px',
              border: '1px solid #e9ecef'
            }}>
              Éléments principaux
            </div>
            <div style={{ paddingLeft: '8px' }}>
              <Toggle
                checked={element.showDate !== false}
                onChange={(checked) => onChange(element.id, 'showDate', checked)}
                label="Afficher la date"
                description="Affiche la date de commande"
              />
            </div>
          </div>

          {/* Section Affichage du fond */}
          <div style={{ marginBottom: '16px' }}>
            <div style={{
              fontSize: '12px',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '8px',
              padding: '4px 8px',
              backgroundColor: '#f8f9fa',
              borderRadius: '3px',
              border: '1px solid #e9ecef'
            }}>
              Affichage du fond
            </div>
            <div style={{ paddingLeft: '8px' }}>
              <Toggle
                checked={element.showBackground !== false}
                onChange={(checked) => onChange(element.id, 'showBackground', checked)}
                label="Afficher le fond"
                description="Affiche un fond coloré derrière le numéro de commande"
              />
            </div>
          </div>

          {/* Section Libellé personnalisé */}
          <div style={{ marginBottom: '16px' }}>
            <div style={{
              fontSize: '12px',
              fontWeight: 'bold',
              color: '#333',
              marginBottom: '8px',
              padding: '4px 8px',
              backgroundColor: '#f8f9fa',
              borderRadius: '3px',
              border: '1px solid #e9ecef'
            }}>
              Libellé personnalisé
            </div>
            <div style={{ paddingLeft: '8px' }}>
              <Toggle
                checked={element.showLabel !== false}
                onChange={(checked) => onChange(element.id, 'showLabel', checked)}
                label="Afficher le libellé"
                description="Affiche un texte devant le numéro"
              />

              {element.showLabel !== false && (
                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                    Texte du libellé
                  </label>
                  <input
                    type="text"
                    value={element.labelText || 'N° de commande:'}
                    onChange={(e) => onChange(element.id, 'labelText', e.target.value)}
                    placeholder="N° de commande:"
                    style={{
                      width: '100%',
                      padding: '6px',
                      border: '1px solid #ccc',
                      borderRadius: '4px',
                      fontSize: '12px'
                    }}
                  />
                  <div style={{ fontSize: '10px', color: '#666', marginTop: '4px' }}>
                    Texte affiché avant le numéro de commande
                  </div>
                </div>
              )}

              {element.showLabel !== false && (
                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                    Position du libellé
                  </label>
                  <select
                    value={element.labelPosition || 'above'}
                    onChange={(e) => onChange(element.id, 'labelPosition', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '6px',
                      border: '1px solid #ccc',
                      borderRadius: '4px',
                      fontSize: '12px'
                    }}
                  >
                    <option value="above">Au-dessus du numéro</option>
                    <option value="left">À gauche du numéro</option>
                    <option value="right">À droite du numéro</option>
                    <option value="below">En-dessous du numéro</option>
                  </select>
                </div>
              )}
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Alignement du contenu
            </label>
            <select
              value={element.contentAlign || 'left'}
              onChange={(e) => onChange(element.id, 'contentAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="left">Aligner à gauche</option>
              <option value="center">Centrer</option>
              <option value="right">Aligner à droite</option>
            </select>
            <div style={{ fontSize: '10px', color: '#666', marginTop: '4px' }}>
              Positionne tout le contenu (libellé, numéro, date) dans l&apos;élément
            </div>
          </div>

          <div style={{ marginBottom: '12px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }}>
            <div style={{ fontSize: '12px', fontWeight: 'bold', marginBottom: '8px', color: '#495057' }}>
              ℹ️ Information
            </div>
            <div style={{ fontSize: '11px', color: '#6c757d', lineHeight: '1.4' }}>
              Le numéro de commande est automatiquement récupéré depuis WooCommerce.
              Le format et la numérotation sont gérés par votre configuration WooCommerce.
            </div>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {currentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Taille du texte
            </label>
            <select
              value={element.fontSize || '14'}
              onChange={(e) => onChange(element.id, 'fontSize', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="12">Petit (12px)</option>
              <option value="14">Normal (14px)</option>
              <option value="16">Moyen (16px)</option>
              <option value="18">Grand (18px)</option>
              <option value="20">Très grand (20px)</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Police
            </label>
            <select
              value={element.fontFamily || 'Arial'}
              onChange={(e) => onChange(element.id, 'fontFamily', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="Arial">Arial</option>
              <option value="Helvetica">Helvetica</option>
              <option value="Times New Roman">Times New Roman</option>
              <option value="Georgia">Georgia</option>
              <option value="Verdana">Verdana</option>
              <option value="Calibri">Calibri</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Style du texte
            </label>
            <select
              value={element.fontStyle || 'normal'}
              onChange={(e) => onChange(element.id, 'fontStyle', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="normal">Normal</option>
              <option value="italic">Italique</option>
            </select>
          </div>

          {/* Section Couleur de fond - visible seulement si showBackground est activé */}
          {element.showBackground !== false && (
            <div style={{ marginTop: '16px', paddingTop: '16px', borderTop: '1px solid #e5e7eb' }}>
              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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
            </div>
          )}
        </>
      )}

      {/* Onglet Positionnement */}
      {currentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position X
            </label>
            <input
              type="number"
              value={element.x || 0}
              onChange={(e) => onChange(element.id, 'x', parseInt(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position Y
            </label>
            <input
              type="number"
              value={element.y || 0}
              onChange={(e) => onChange(element.id, 'y', parseInt(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Largeur
            </label>
            <input
              type="number"
              value={element.width || 200}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 200)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Hauteur
            </label>
            <input
              type="number"
              value={element.height || 40}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 40)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>
        </>
      )}
    </>
  );
}
