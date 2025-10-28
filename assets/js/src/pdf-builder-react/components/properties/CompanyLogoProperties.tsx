import React from 'react';
import { Element } from '../../types/elements';

interface CompanyLogoPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function CompanyLogoProperties({ element, onChange, activeTab, setActiveTab }: CompanyLogoPropertiesProps) {
  const logoCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setLogoCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Company Logo */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setLogoCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: logoCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: logoCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setLogoCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: logoCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: logoCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setLogoCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: logoCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: logoCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {logoCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              URL du logo
            </label>
            <input
              type="text"
              value={(element as any).logoUrl || ''}
              onChange={(e) => onChange(element.id, 'logoUrl', e.target.value)}
              placeholder="https://exemple.com/logo.png"
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
              Texte alternatif
            </label>
            <input
              type="text"
              value={(element as any).altText || ''}
              onChange={(e) => onChange(element.id, 'altText', e.target.value)}
              placeholder="Logo de l'entreprise"
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
              Maintenir les proportions
            </label>
            <input
              type="checkbox"
              checked={(element as any).maintainAspectRatio !== false}
              onChange={(e) => onChange(element.id, 'maintainAspectRatio', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Préserve le ratio largeur/hauteur</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher une bordure
            </label>
            <input
              type="checkbox"
              checked={(element as any).showBorder !== false}
              onChange={(e) => onChange(element.id, 'showBorder', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche une bordure autour du logo</span>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {logoCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Mode d'ajustement
            </label>
            <select
              value={(element as any).objectFit || 'contain'}
              onChange={(e) => onChange(element.id, 'objectFit', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="contain">Contenir (respecte les proportions)</option>
              <option value="cover">Couvrir (remplit complètement)</option>
              <option value="fill">Remplir (étire si nécessaire)</option>
              <option value="none">Aucun (taille originale)</option>
              <option value="scale-down">Réduire (taille originale ou contenir)</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Opacité
            </label>
            <input
              type="range"
              min="0"
              max="1"
              step="0.1"
              value={(element as any).opacity || 1}
              onChange={(e) => onChange(element.id, 'opacity', parseFloat(e.target.value))}
              style={{
                width: '100%',
                marginTop: '4px'
              }}
            />
            <div style={{ fontSize: '11px', color: '#666', textAlign: 'center', marginTop: '2px' }}>
              {(element as any).opacity || 1}
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Rayon des coins
            </label>
            <input
              type="number"
              min="0"
              max="50"
              value={(element as any).borderRadius || 0}
              onChange={(e) => onChange(element.id, 'borderRadius', parseInt(e.target.value) || 0)}
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
              Rotation
            </label>
            <input
              type="number"
              min="-180"
              max="180"
              value={(element as any).rotation || 0}
              onChange={(e) => onChange(element.id, 'rotation', parseInt(e.target.value) || 0)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
            <div style={{ fontSize: '11px', color: '#666', marginTop: '2px' }}>Degrés</div>
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {logoCurrentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position X
            </label>
            <input
              type="number"
              value={(element as any).x || 0}
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
              value={(element as any).y || 0}
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
              value={(element as any).width || 100}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 100)}
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
              value={(element as any).height || 100}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 100)}
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