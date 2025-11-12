import React from 'react';
import { Element } from '../../types/elements';

interface ExtendedElement extends Element {
  src?: string;
  objectFit?: string;
}

interface ImagePropertiesProps {
  element: ExtendedElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function ImageProperties({ element, onChange, activeTab, setActiveTab }: ImagePropertiesProps) {
  const imageCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setImageCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  const openMediaLibrary = () => {
    if (window.wp?.media) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const media = window.wp.media({
        title: 'Sélectionner une image',
        button: {
          text: 'Utiliser cette image'
        },
        multiple: false
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      (media as any).on('select', () => {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const attachment = (media as any).state().get('selection').first().toJSON();
        onChange(element.id, 'src', attachment.url);
      });

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      (media as any).open();
    }
  };

  return (
    <>
      {/* Système d'onglets pour Image */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setImageCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: imageCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: imageCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setImageCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: imageCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: imageCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setImageCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: imageCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: imageCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {imageCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Source de l&apos;image
            </label>
            <div style={{ display: 'flex', gap: '4px' }}>
              <input
                type="text"
                value={element.src || ''}
                onChange={(e) => onChange(element.id, 'src', e.target.value)}
                placeholder="URL de l'image"
                style={{
                  flex: 1,
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
              <button
                onClick={openMediaLibrary}
                style={{
                  padding: '4px 8px',
                  border: '1px solid #007bff',
                  borderRadius: '3px',
                  backgroundColor: '#007bff',
                  color: '#fff',
                  cursor: 'pointer',
                  fontSize: '11px'
                }}
                title="Ouvrir la médiathèque WordPress"
              >
                📁
              </button>
            </div>
          </div>

          {element.src && (
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                Aperçu
              </label>
              <div style={{
                padding: '8px',
                border: '1px solid #e0e0e0',
                borderRadius: '4px',
                backgroundColor: '#f8f9fa',
                textAlign: 'center'
              }}>
                <img
                  src={element.src}
                  alt="Aperçu"
                  style={{
                    maxWidth: '100%',
                    maxHeight: '100px',
                    borderRadius: '4px',
                    boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                  }}
                />
              </div>
            </div>
          )}
        </>
      )}

      {/* Onglet Personnalisation */}
      {imageCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Ajustement de l&apos;image
            </label>
            <select
              value={element.objectFit || 'contain'}
              onChange={(e) => onChange(element.id, 'objectFit', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="contain">Contenir (respecter les proportions)</option>
              <option value="cover">Couvrir (remplir complètement)</option>
              <option value="fill">Remplir (étirer si nécessaire)</option>
              <option value="none">Aucun (taille originale)</option>
              <option value="scale-down">Réduire (taille originale ou contenir)</option>
            </select>
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {imageCurrentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position X <span style={{ color: '#666', fontSize: '10px' }}>({element.x || 0}px)</span>
            </label>
            <input
              type="number"
              value={element.x || 0}
              onChange={(e) => onChange(element.id, 'x', parseFloat(e.target.value) || 0)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position Y <span style={{ color: '#666', fontSize: '10px' }}>({element.y || 0}px)</span>
            </label>
            <input
              type="number"
              value={element.y || 0}
              onChange={(e) => onChange(element.id, 'y', parseFloat(e.target.value) || 0)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Largeur <span style={{ color: '#666', fontSize: '10px' }}>({element.width || 100}px)</span>
            </label>
            <input
              type="number"
              min="1"
              value={element.width || 100}
              onChange={(e) => onChange(element.id, 'width', parseFloat(e.target.value) || 100)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Hauteur <span style={{ color: '#666', fontSize: '10px' }}>({element.height || 100}px)</span>
            </label>
            <input
              type="number"
              min="1"
              value={element.height || 100}
              onChange={(e) => onChange(element.id, 'height', parseFloat(e.target.value) || 100)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Rotation <span style={{ color: '#666', fontSize: '10px' }}>({element.rotation || 0}°)</span>
            </label>
            <input
              type="number"
              min="-180"
              max="180"
              value={element.rotation || 0}
              onChange={(e) => onChange(element.id, 'rotation', parseFloat(e.target.value) || 0)}
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