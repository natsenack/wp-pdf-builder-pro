import React from 'react';
import { Element } from '../../types/elements';

interface CompanyInfoPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function CompanyInfoProperties({ element, onChange, activeTab, setActiveTab }: CompanyInfoPropertiesProps) {
  const companyCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setCompanyCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  const companyThemes = [
    {
      id: 'corporate',
      name: 'Corporate',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #1f2937',
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
            height: '3px',
            backgroundColor: '#1f2937'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#6b7280'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#9ca3af'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#1f2937',
        textColor: '#374151',
        headerTextColor: '#111827'
      }
    },
    {
      id: 'modern',
      name: 'Moderne',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #3b82f6',
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
            height: '3px',
            backgroundColor: '#3b82f6'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#60a5fa'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#93c5fd'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#3b82f6',
        textColor: '#1e40af',
        headerTextColor: '#1e3a8a'
      }
    },
    {
      id: 'elegant',
      name: 'Élégant',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #8b5cf6',
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
            height: '3px',
            backgroundColor: '#8b5cf6'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#a78bfa'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#c4b5fd'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#8b5cf6',
        textColor: '#6d28d9',
        headerTextColor: '#581c87'
      }
    },
    {
      id: 'minimal',
      name: 'Minimal',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #e5e7eb',
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
            backgroundColor: '#374151'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#6b7280'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#9ca3af'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#e5e7eb',
        textColor: '#374151',
        headerTextColor: '#111827'
      }
    },
    {
      id: 'professional',
      name: 'Professionnel',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #059669',
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
            height: '3px',
            backgroundColor: '#059669'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#10b981'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#34d399'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#059669',
        textColor: '#065f46',
        headerTextColor: '#064e3b'
      }
    },
    {
      id: 'classic',
      name: 'Classique',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #92400e',
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
            height: '3px',
            backgroundColor: '#92400e'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#d97706'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#f59e0b'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#92400e',
        textColor: '#78350f',
        headerTextColor: '#451a03'
      }
    }
  ];

  return (
    <>
      {/* Système d'onglets pour Company Info */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setCompanyCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: companyCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: companyCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setCompanyCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: companyCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: companyCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setCompanyCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: companyCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: companyCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {companyCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher les en-têtes
            </label>
            <input
              type="checkbox"
              checked={(element as any).showHeaders !== false}
              onChange={(e) => onChange(element.id, 'showHeaders', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche les titres des sections</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher les bordures
            </label>
            <input
              type="checkbox"
              checked={(element as any).showBorders !== false}
              onChange={(e) => onChange(element.id, 'showBorders', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche les bordures autour des sections</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le nom de l'entreprise
            </label>
            <input
              type="checkbox"
              checked={(element as any).showCompanyName !== false}
              onChange={(e) => onChange(element.id, 'showCompanyName', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Nom de l'entreprise</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher l'adresse
            </label>
            <input
              type="checkbox"
              checked={(element as any).showAddress !== false}
              onChange={(e) => onChange(element.id, 'showAddress', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Adresse complète de l'entreprise</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le téléphone
            </label>
            <input
              type="checkbox"
              checked={(element as any).showPhone !== false}
              onChange={(e) => onChange(element.id, 'showPhone', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Numéro de téléphone</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher l'email
            </label>
            <input
              type="checkbox"
              checked={(element as any).showEmail !== false}
              onChange={(e) => onChange(element.id, 'showEmail', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Adresse email de l'entreprise</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le numéro SIRET
            </label>
            <input
              type="checkbox"
              checked={(element as any).showSiret !== false}
              onChange={(e) => onChange(element.id, 'showSiret', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Numéro SIRET de l'entreprise</span>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {companyCurrentTab === 'personnalisation' && (
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
              {companyThemes.map((theme) => (
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
              value={(element as any).fontSize || '12'}
              onChange={(e) => onChange(element.id, 'fontSize', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="10">Petit (10px)</option>
              <option value="12">Normal (12px)</option>
              <option value="14">Moyen (14px)</option>
              <option value="16">Grand (16px)</option>
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
        </>
      )}

      {/* Onglet Positionnement */}
      {companyCurrentTab === 'positionnement' && (
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
              value={(element as any).width || 200}
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