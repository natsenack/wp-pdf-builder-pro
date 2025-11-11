import React from 'react';
import { OrderNumberElement } from '../../types/elements';

interface OrderNumberPropertiesProps {
  element: OrderNumberElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function OrderNumberProperties({ element, onChange, activeTab, setActiveTab }: OrderNumberPropertiesProps) {
  // 🔍 Log des propriétés reçues par le composant
  console.log('🔍 [ORDER NUMBER PROPS] Component received element:', {
    id: element.id,
    contentAlign: element.contentAlign || 'NOT SET',
    labelPosition: element.labelPosition || 'NOT SET',
    showLabel: element.showLabel,
    labelText: element.labelText || 'NOT SET',
    allProperties: Object.keys(element)
  });

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
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le numéro de commande
            </label>
            <input
              type="checkbox"
              checked={true}
              disabled={true}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Toujours affiché</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le libellé
            </label>
            <input
              type="checkbox"
              checked={element.showLabel !== false}
              onChange={(e) => onChange(element.id, 'showLabel', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche un texte devant le numéro</span>
          </div>

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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher la date
            </label>
            <input
              type="checkbox"
              checked={element.showDate !== false}
              onChange={(e) => onChange(element.id, 'showDate', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche la date de commande</span>
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
              value={element.fontWeight || 'normal'}
              onChange={(e) => onChange(element.id, 'fontWeight', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="normal">Normal</option>
              <option value="bold">Gras</option>
            </select>
          </div>
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
