import React from 'react';
import { Element } from '../../types/elements';

interface MentionsPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function MentionsProperties({ element, onChange, activeTab, setActiveTab }: MentionsPropertiesProps) {
  const mentionsCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setMentionsCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  const mentionsThemes = [
    {
      id: 'legal',
      name: 'Légal',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #6b7280',
          borderRadius: '4px',
          backgroundColor: '#ffffff',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '1px'
        }}>
          <div style={{
            width: '90%',
            height: '2px',
            backgroundColor: '#6b7280'
          }}></div>
          <div style={{
            width: '75%',
            height: '1px',
            backgroundColor: '#9ca3af'
          }}></div>
          <div style={{
            width: '60%',
            height: '1px',
            backgroundColor: '#d1d5db'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#6b7280',
        textColor: '#374151',
        headerTextColor: '#111827'
      }
    },
    {
      id: 'subtle',
      name: 'Discret',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #e5e7eb',
          borderRadius: '4px',
          backgroundColor: '#f9fafb',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '1px'
        }}>
          <div style={{
            width: '90%',
            height: '1px',
            backgroundColor: '#9ca3af'
          }}></div>
          <div style={{
            width: '75%',
            height: '1px',
            backgroundColor: '#d1d5db'
          }}></div>
          <div style={{
            width: '60%',
            height: '1px',
            backgroundColor: '#e5e7eb'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#f9fafb',
        borderColor: '#e5e7eb',
        textColor: '#6b7280',
        headerTextColor: '#374151'
      }
    },
    {
      id: 'minimal',
      name: 'Minimal',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: 'none',
          borderRadius: '4px',
          backgroundColor: 'transparent',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '1px'
        }}>
          <div style={{
            width: '90%',
            height: '1px',
            backgroundColor: '#d1d5db'
          }}></div>
          <div style={{
            width: '75%',
            height: '1px',
            backgroundColor: '#e5e7eb'
          }}></div>
          <div style={{
            width: '60%',
            height: '1px',
            backgroundColor: '#f3f4f6'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: 'transparent',
        borderColor: 'transparent',
        textColor: '#6b7280',
        headerTextColor: '#374151'
      }
    }
  ];

  const predefinedMentions = [
    {
      key: 'cgv',
      label: 'Conditions Générales de Vente',
      text: 'Conditions Générales de Vente applicables. Consultez notre site web pour plus de détails.'
    },
    {
      key: 'legal',
      label: 'Mentions légales',
      text: 'Document établi sous la responsabilité de l\'entreprise. Toutes les informations sont confidentielles.'
    },
    {
      key: 'payment',
      label: 'Conditions de paiement',
      text: 'Paiement dû dans les délais convenus. Tout retard peut entraîner des pénalités.'
    },
    {
      key: 'warranty',
      label: 'Garantie',
      text: 'Garantie légale de conformité et garantie contre les vices cachés selon les articles L217-4 et suivants du Code de la consommation.'
    },
    {
      key: 'returns',
      label: 'Droit de rétractation',
      text: 'Droit de rétractation de 14 jours selon l\'article L221-18 du Code de la consommation.'
    },
    {
      key: 'custom',
      label: 'Personnalisé',
      text: ''
    }
  ];

  return (
    <>
      {/* Système d'onglets pour Mentions */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setMentionsCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: mentionsCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: mentionsCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setMentionsCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: mentionsCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: mentionsCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setMentionsCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: mentionsCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: mentionsCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {mentionsCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Type de mentions
            </label>
            <select
              value={(element as any).mentionType || 'custom'}
              onChange={(e) => {
                const selectedMention = predefinedMentions.find(m => m.key === e.target.value);
                onChange(element.id, 'mentionType', e.target.value);
                if (selectedMention && selectedMention.text) {
                  onChange(element.id, 'text', selectedMention.text);
                }
              }}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              {predefinedMentions.map((mention) => (
                <option key={mention.key} value={mention.key}>
                  {mention.label}
                </option>
              ))}
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Texte des mentions
            </label>
            <textarea
              value={(element as any).text || ''}
              onChange={(e) => onChange(element.id, 'text', e.target.value)}
              placeholder="Entrez le texte des mentions légales..."
              rows={6}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px',
                resize: 'vertical'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher un séparateur
            </label>
            <input
              type="checkbox"
              checked={(element as any).showSeparator !== false}
              onChange={(e) => onChange(element.id, 'showSeparator', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Ligne de séparation avant les mentions</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Style du séparateur
            </label>
            <select
              value={(element as any).separatorStyle || 'solid'}
              onChange={(e) => onChange(element.id, 'separatorStyle', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="solid">Ligne continue</option>
              <option value="dashed">Tirets</option>
              <option value="dotted">Pointillés</option>
              <option value="double">Double ligne</option>
            </select>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {mentionsCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Thème visuel
            </label>
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
              gap: '8px',
              maxHeight: '200px',
              overflowY: 'auto',
              padding: '4px',
              border: '1px solid #e0e0e0',
              borderRadius: '4px',
              backgroundColor: '#fafafa'
            }}>
              {mentionsThemes.map((theme) => (
                <div
                  key={theme.id}
                  onClick={() => onChange(element.id, 'theme', theme.id)}
                  style={{
                    cursor: 'pointer',
                    border: (element as any).theme === theme.id ? '2px solid #007bff' : '2px solid transparent',
                    borderRadius: '6px',
                    padding: '6px',
                    backgroundColor: '#ffffff',
                    transition: 'all 0.2s ease'
                  }}
                  title={theme.name}
                >
                  <div style={{ fontSize: '10px', fontWeight: 'bold', marginBottom: '4px', textAlign: 'center' }}>
                    {theme.name}
                  </div>
                  {theme.preview}
                </div>
              ))}
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Taille du texte
            </label>
            <select
              value={(element as any).fontSize || '10'}
              onChange={(e) => onChange(element.id, 'fontSize', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="8">Très petit (8px)</option>
              <option value="10">Petit (10px)</option>
              <option value="11">Normal (11px)</option>
              <option value="12">Moyen (12px)</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Alignement du texte
            </label>
            <select
              value={(element as any).textAlign || 'left'}
              onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="left">Gauche</option>
              <option value="center">Centre</option>
              <option value="right">Droite</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Style du texte
            </label>
            <div style={{ display: 'flex', gap: '8px', flexWrap: 'wrap' }}>
              <label style={{ fontSize: '11px', display: 'flex', alignItems: 'center' }}>
                <input
                  type="checkbox"
                  checked={(element as any).fontWeight === 'bold'}
                  onChange={(e) => onChange(element.id, 'fontWeight', e.target.checked ? 'bold' : 'normal')}
                  style={{ marginRight: '4px' }}
                />
                Gras
              </label>
              <label style={{ fontSize: '11px', display: 'flex', alignItems: 'center' }}>
                <input
                  type="checkbox"
                  checked={(element as any).fontStyle === 'italic'}
                  onChange={(e) => onChange(element.id, 'fontStyle', e.target.checked ? 'italic' : 'normal')}
                  style={{ marginRight: '4px' }}
                />
                Italique
              </label>
            </div>
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {mentionsCurrentTab === 'positionnement' && (
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
              value={(element as any).width || 400}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 400)}
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
              value={(element as any).height || 80}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 80)}
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