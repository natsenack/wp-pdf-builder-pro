import React from 'react';
import { Element } from '../../types/elements';

interface CustomerInfoPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function CustomerInfoProperties({ element, onChange, activeTab, setActiveTab }: CustomerInfoPropertiesProps) {
  const customerCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setCustomerCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Customer Info */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setCustomerCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: customerCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: customerCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setCustomerCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: customerCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: customerCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setCustomerCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: customerCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: customerCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {customerCurrentTab === 'fonctionnalites' && (
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
              Afficher le nom complet
            </label>
            <input
              type="checkbox"
              checked={(element as any).showFullName !== false}
              onChange={(e) => onChange(element.id, 'showFullName', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Prénom et nom du client</span>
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
            <span style={{ fontSize: '11px', color: '#666' }}>Adresse complète du client</span>
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
            <span style={{ fontSize: '11px', color: '#666' }}>Adresse email du client</span>
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
            <span style={{ fontSize: '11px', color: '#666' }}>Numéro de téléphone du client</span>
          </div>

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Informations entreprise (pour factures B2B)
            </label>
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
            <span style={{ fontSize: '11px', color: '#666' }}>Nom de l'entreprise du client</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le numéro TVA
            </label>
            <input
              type="checkbox"
              checked={(element as any).showVatNumber !== false}
              onChange={(e) => onChange(element.id, 'showVatNumber', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Numéro de TVA intracommunautaire</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher l'adresse de l'entreprise
            </label>
            <input
              type="checkbox"
              checked={(element as any).showCompanyAddress !== false}
              onChange={(e) => onChange(element.id, 'showCompanyAddress', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Adresse de l'entreprise (si différente)</span>
          </div>

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Disposition
            </label>
            <select
              value={(element as any).layout || 'vertical'}
              onChange={(e) => onChange(element.id, 'layout', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="vertical">Verticale</option>
              <option value="horizontal">Horizontale</option>
              <option value="compact">Compacte</option>
            </select>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {customerCurrentTab === 'personnalisation' && (
        <>
          {/* Section Thèmes pour Customer Info */}
          <div style={{ marginBottom: '16px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Thèmes prédéfinis
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
              {[
                {
                  id: 'clean',
                  name: 'Propre',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #f3f4f6',
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
                        backgroundColor: '#f9fafb'
                      }}></div>
                      <div style={{
                        width: '75%',
                        height: '2px',
                        backgroundColor: '#ffffff'
                      }}></div>
                      <div style={{
                        width: '60%',
                        height: '2px',
                        backgroundColor: '#f9fafb'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#ffffff',
                    borderColor: '#f3f4f6',
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
                      border: '1px solid #f1f5f9',
                      borderRadius: '6px',
                      backgroundColor: '#fafbfc',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '90%',
                        height: '3px',
                        backgroundColor: '#e2e8f0',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '75%',
                        height: '3px',
                        backgroundColor: '#fafbfc',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '60%',
                        height: '3px',
                        backgroundColor: '#e2e8f0',
                        borderRadius: '1px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fafbfc',
                    borderColor: '#f1f5f9',
                    textColor: '#475569',
                    headerTextColor: '#334155'
                  }
                },
                {
                  id: 'elegant',
                  name: 'Élégant',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '2px solid #f3e8ff',
                      borderRadius: '8px',
                      backgroundColor: '#fefefe',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '90%',
                        height: '3px',
                        backgroundColor: '#f3e8ff',
                        borderRadius: '2px'
                      }}></div>
                      <div style={{
                        width: '75%',
                        height: '3px',
                        backgroundColor: '#fefefe',
                        borderRadius: '2px'
                      }}></div>
                      <div style={{
                        width: '60%',
                        height: '3px',
                        backgroundColor: '#f3e8ff',
                        borderRadius: '2px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fefefe',
                    borderColor: '#f3e8ff',
                    textColor: '#6b21a8',
                    headerTextColor: '#581c87'
                  }
                },
                {
                  id: 'corporate',
                  name: 'Corporate',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #e5e7eb',
                      borderRadius: '0px',
                      backgroundColor: '#ffffff',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '90%',
                        height: '3px',
                        backgroundColor: '#f3f4f6'
                      }}></div>
                      <div style={{
                        width: '75%',
                        height: '3px',
                        backgroundColor: '#ffffff'
                      }}></div>
                      <div style={{
                        width: '60%',
                        height: '3px',
                        backgroundColor: '#f3f4f6'
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
                  id: 'warm',
                  name: 'Chaleureux',
                  preview: (
                    <div style={{
                      width: '100%',
                      height: '35px',
                      border: '1px solid #fed7aa',
                      borderRadius: '6px',
                      backgroundColor: '#fff8f0',
                      display: 'flex',
                      flexDirection: 'column',
                      justifyContent: 'center',
                      alignItems: 'center',
                      gap: '2px'
                    }}>
                      <div style={{
                        width: '90%',
                        height: '3px',
                        backgroundColor: '#fed7aa',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '75%',
                        height: '3px',
                        backgroundColor: '#fff8f0',
                        borderRadius: '1px'
                      }}></div>
                      <div style={{
                        width: '60%',
                        height: '3px',
                        backgroundColor: '#fed7aa',
                        borderRadius: '1px'
                      }}></div>
                    </div>
                  ),
                  styles: {
                    backgroundColor: '#fff8f0',
                    borderColor: '#fed7aa',
                    textColor: '#9a3412',
                    headerTextColor: '#78350f'
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
                      borderRadius: '0px',
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
                        backgroundColor: '#e5e7eb'
                      }}></div>
                      <div style={{
                        width: '75%',
                        height: '1px',
                        backgroundColor: 'transparent'
                      }}></div>
                      <div style={{
                        width: '60%',
                        height: '1px',
                        backgroundColor: '#e5e7eb'
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
              ].map(theme => (
                <button
                  key={theme.id}
                  onClick={() => {
                    // Appliquer toutes les propriétés du thème
                    Object.entries(theme.styles).forEach(([property, value]) => {
                      onChange(element.id, property, value);
                    });
                  }}
                  style={{
                    padding: '6px',
                    border: '2px solid transparent',
                    borderRadius: '6px',
                    backgroundColor: '#ffffff',
                    cursor: 'pointer',
                    textAlign: 'center',
                    transition: 'all 0.2s ease',
                    minHeight: '70px',
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: '4px'
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.borderColor = '#007bff';
                    e.currentTarget.style.backgroundColor = '#f8f9fa';
                    e.currentTarget.style.transform = 'translateY(-1px)';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.borderColor = 'transparent';
                    e.currentTarget.style.backgroundColor = '#ffffff';
                    e.currentTarget.style.transform = 'translateY(0)';
                  }}
                  title={`Appliquer le thème ${theme.name}`}
                >
                  <div style={{
                    fontSize: '10px',
                    fontWeight: 'bold',
                    color: '#333',
                    textAlign: 'center',
                    lineHeight: '1.2'
                  }}>
                    {theme.name}
                  </div>
                  {theme.preview}
                </button>
              ))}
            </div>
          </div>

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Taille de police
            </label>
            <input
              type="number"
              min="8"
              max="24"
              value={(element as any).fontSize || 12}
              onChange={(e) => onChange(element.id, 'fontSize', parseInt(e.target.value) || 12)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Famille de police
            </label>
            <select
              value={(element as any).fontFamily || 'Arial'}
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
              <option value="Tahoma">Tahoma</option>
              <option value="Trebuchet MS">Trebuchet MS</option>
              <option value="Calibri">Calibri</option>
              <option value="Cambria">Cambria</option>
              <option value="Segoe UI">Segoe UI</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Épaisseur de police
            </label>
            <select
              value={(element as any).fontWeight || 'normal'}
              onChange={(e) => onChange(element.id, 'fontWeight', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="normal">Normal (400)</option>
              <option value="bold">Gras (700)</option>
              <option value="lighter">Fin (300)</option>
              <option value="bolder">Très gras (900)</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Style de police
            </label>
            <select
              value={(element as any).fontStyle || 'normal'}
              onChange={(e) => onChange(element.id, 'fontStyle', e.target.value)}
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
              <option value="oblique">Oblique</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur de fond
            </label>
            <input
              type="color"
              value={(element as any).backgroundColor || 'transparent'}
              onChange={(e) => onChange(element.id, 'backgroundColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur des bordures
            </label>
            <input
              type="color"
              value={(element as any).borderColor || '#e5e7eb'}
              onChange={(e) => onChange(element.id, 'borderColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Couleur du texte
            </label>
            <input
              type="color"
              value={(element as any).textColor || '#374151'}
              onChange={(e) => onChange(element.id, 'textColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '3px'
              }}
            />
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {customerCurrentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Position X
            </label>
            <input
              type="number"
              value={element.x}
              onChange={(e) => onChange(element.id, 'x', parseInt(e.target.value) || 0)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Position Y
            </label>
            <input
              type="number"
              value={element.y}
              onChange={(e) => onChange(element.id, 'y', parseInt(e.target.value) || 0)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Largeur
            </label>
            <input
              type="number"
              value={element.width}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 100)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Hauteur
            </label>
            <input
              type="number"
              value={element.height}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 100)}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement horizontal
            </label>
            <select
              value={(element as any).textAlign || 'left'}
              onChange={(e) => onChange(element.id, 'textAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="left">Gauche</option>
              <option value="center">Centre</option>
              <option value="right">Droite</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement vertical
            </label>
            <select
              value={(element as any).verticalAlign || 'top'}
              onChange={(e) => onChange(element.id, 'verticalAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="top">Haut</option>
              <option value="middle">Milieu</option>
              <option value="bottom">Bas</option>
            </select>
          </div>
        </>
      )}
    </>
  );
}