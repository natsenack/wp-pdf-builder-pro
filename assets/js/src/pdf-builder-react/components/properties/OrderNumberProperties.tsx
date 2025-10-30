import React from 'react';
import { Element } from '../../types/elements';

interface OrderNumberPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'contenu' | 'style' | 'position' };
  setActiveTab: (tabs: { [key: string]: 'contenu' | 'style' | 'position' }) => void;
}

export function OrderNumberProperties({ element, onChange, activeTab, setActiveTab }: OrderNumberPropertiesProps) {
  const currentTab = activeTab[element.id] || 'contenu';
  const setCurrentTab = (tab: 'contenu' | 'style' | 'position') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <div style={{ padding: '8px' }}>
      {/* Onglets simplifiés */}
      <div style={{ display: 'flex', marginBottom: '16px', borderBottom: '2px solid #ddd', gap: '2px' }}>
        <button
          onClick={() => setCurrentTab('contenu')}
          style={{
            flex: 1,
            padding: '8px 4px',
            backgroundColor: currentTab === 'contenu' ? '#007bff' : '#f0f0f0',
            color: currentTab === 'contenu' ? '#fff' : '#333',
            border: 'none',
            cursor: 'pointer',
            fontSize: '11px',
            fontWeight: 'bold',
            borderRadius: '3px 3px 0 0'
          }}
        >
          Contenu
        </button>
        <button
          onClick={() => setCurrentTab('style')}
          style={{
            flex: 1,
            padding: '8px 4px',
            backgroundColor: currentTab === 'style' ? '#007bff' : '#f0f0f0',
            color: currentTab === 'style' ? '#fff' : '#333',
            border: 'none',
            cursor: 'pointer',
            fontSize: '11px',
            fontWeight: 'bold',
            borderRadius: '3px 3px 0 0'
          }}
        >
          Style
        </button>
        <button
          onClick={() => setCurrentTab('position')}
          style={{
            flex: 1,
            padding: '8px 4px',
            backgroundColor: currentTab === 'position' ? '#007bff' : '#f0f0f0',
            color: currentTab === 'position' ? '#fff' : '#333',
            border: 'none',
            cursor: 'pointer',
            fontSize: '11px',
            fontWeight: 'bold',
            borderRadius: '3px 3px 0 0'
          }}
        >
          Position
        </button>
      </div>

      {/* Onglet Contenu */}
      {currentTab === 'contenu' && (
        <div>
          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
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

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Afficher le libellé
            </label>
            <input
              type="checkbox"
              checked={(element as any).showLabel !== false}
              onChange={(e) => onChange(element.id, 'showLabel', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche un texte devant le numéro</span>
          </div>

          {(element as any).showLabel !== false && (
            <div style={{ marginBottom: '16px' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
                Texte du libellé
              </label>
              <input
                type="text"
                value={(element as any).labelText || 'N° de commande:'}
                onChange={(e) => onChange(element.id, 'labelText', e.target.value)}
                placeholder="N° de commande:"
                style={{
                  width: '100%',
                  padding: '8px',
                  border: '1px solid #ccc',
                  borderRadius: '4px',
                  fontSize: '12px'
                }}
              />
            </div>
          )}

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Position du libellé
            </label>
            <select
              value={(element as any).labelPosition || 'left'}
              onChange={(e) => onChange(element.id, 'labelPosition', e.target.value)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="left">Libellé à gauche du numéro</option>
              <option value="right">Numéro à gauche du libellé</option>
              <option value="above">Libellé au-dessus du numéro</option>
              <option value="below">Libellé en-dessous du numéro</option>
            </select>
          </div>

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Afficher la date
            </label>
            <input
              type="checkbox"
              checked={(element as any).showDate !== false}
              onChange={(e) => onChange(element.id, 'showDate', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche la date de commande</span>
          </div>
        </div>
      )}

      {/* Onglet Style */}
      {currentTab === 'style' && (
        <div>
          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Taille du texte
            </label>
            <select
              value={(element as any).fontSize || '14'}
              onChange={(e) => onChange(element.id, 'fontSize', e.target.value)}
              style={{
                width: '100%',
                padding: '8px',
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

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Police
            </label>
            <select
              value={(element as any).fontFamily || 'Arial'}
              onChange={(e) => onChange(element.id, 'fontFamily', e.target.value)}
              style={{
                width: '100%',
                padding: '8px',
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

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Style du texte
            </label>
            <select
              value={(element as any).fontWeight || 'normal'}
              onChange={(e) => onChange(element.id, 'fontWeight', e.target.value)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="normal">Normal</option>
              <option value="bold">Gras</option>
            </select>
          </div>

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Alignement
            </label>
            <select
              value={(element as any).textAlign || 'left'}
              onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="left">À gauche</option>
              <option value="center">Centré</option>
              <option value="right">À droite</option>
            </select>
            <div style={{ fontSize: '10px', color: '#666', marginTop: '4px' }}>
              {((element as any).labelPosition === 'left' || (element as any).labelPosition === 'right') ?
                'Pour les positions latérales, l\'alignement est optimisé automatiquement.' :
                'Alignement appliqué au numéro et au libellé.'
              }
            </div>
          </div>
        </div>
      )}

      {/* Onglet Position */}
      {currentTab === 'position' && (
        <div>
          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Position X
            </label>
            <input
              type="number"
              value={(element as any).x || 0}
              onChange={(e) => onChange(element.id, 'x', parseInt(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Position Y
            </label>
            <input
              type="number"
              value={(element as any).y || 0}
              onChange={(e) => onChange(element.id, 'y', parseInt(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Largeur
            </label>
            <input
              type="number"
              value={(element as any).width || 200}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 200)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>

          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Hauteur
            </label>
            <input
              type="number"
              value={(element as any).height || 40}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 40)}
              style={{
                width: '100%',
                padding: '8px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
          </div>
        </div>
      )}
    </div>
  );
}