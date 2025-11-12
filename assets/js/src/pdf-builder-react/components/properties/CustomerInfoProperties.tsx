import React from 'react';
import { CustomerInfoElement } from '../../types/elements';

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

interface CustomerInfoPropertiesProps {
  element: CustomerInfoElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
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
          <Toggle
            checked={element.showHeaders !== false}
            onChange={(checked) => onChange(element.id, 'showHeaders', checked)}
            label="Afficher les en-têtes"
            description="Affiche les titres des sections"
          />

          <Toggle
            checked={element.showBorders !== false}
            onChange={(checked) => onChange(element.id, 'showBorders', checked)}
            label="Afficher les bordures"
            description="Affiche les bordures autour des sections"
          />

          <Toggle
            checked={element.showName !== false}
            onChange={(checked) => onChange(element.id, 'showName', checked)}
            label="Afficher le nom"
            description="Nom du client"
          />

          <Toggle
            checked={element.showFullName !== false}
            onChange={(checked) => onChange(element.id, 'showFullName', checked)}
            label="Afficher le nom complet"
            description="Prénom et nom du client"
          />

          <Toggle
            checked={element.showAddress !== false}
            onChange={(checked) => onChange(element.id, 'showAddress', checked)}
            label="Afficher l'adresse"
            description="Adresse complète du client"
          />

          <Toggle
            checked={element.showEmail !== false}
            onChange={(checked) => onChange(element.id, 'showEmail', checked)}
            label="Afficher l'email"
            description="Adresse email du client"
          />

          <Toggle
            checked={element.showPhone !== false}
            onChange={(checked) => onChange(element.id, 'showPhone', checked)}
            label="Afficher le téléphone"
            description="Numéro de téléphone du client"
          />

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Informations entreprise (pour factures B2B)
            </label>
          </div>

          <Toggle
            checked={element.showCompany !== false}
            onChange={(checked) => onChange(element.id, 'showCompany', checked)}
            label="Afficher l'entreprise"
            description="Nom de l'entreprise du client"
          />

          <Toggle
            checked={element.showCompanyName !== false}
            onChange={(checked) => onChange(element.id, 'showCompanyName', checked)}
            label="Afficher le nom de l'entreprise"
            description="Nom de l'entreprise du client"
          />

          <Toggle
            checked={element.showVatNumber !== false}
            onChange={(checked) => onChange(element.id, 'showVatNumber', checked)}
            label="Afficher le numéro TVA"
            description="Numéro de TVA intracommunautaire"
          />

          <Toggle
            checked={element.showCompanyAddress !== false}
            onChange={(checked) => onChange(element.id, 'showCompanyAddress', checked)}
            label="Afficher l'adresse de l'entreprise"
            description="Adresse de l'entreprise (si différente)"
          />

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Disposition
            </label>
            <select
              value={element.layout || 'vertical'}
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

          {/* Police de l'en-tête - Uniquement si les en-têtes sont affichés */}
          {element.showHeaders !== false && (
            <div style={{ marginBottom: '16px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }}>
              <h4 style={{ margin: '0 0 12px 0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
                Police de l&apos;en-tête
              </h4>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Taille de police
              </label>
              <input
                type="number"
                min="8"
                max="32"
                value={element.headerFontSize || (element.fontSize || 12) + 2}
                onChange={(e) => onChange(element.id, 'headerFontSize', parseInt(e.target.value) || 14)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Famille de police
              </label>
              <select
                value={element.headerFontFamily || element.fontFamily || 'Arial'}
                onChange={(e) => onChange(element.id, 'headerFontFamily', e.target.value)}
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

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Épaisseur de police
              </label>
              <select
                value={element.headerFontWeight || element.fontWeight || 'normal'}
                onChange={(e) => onChange(element.id, 'headerFontWeight', e.target.value)}
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

            <div style={{ marginBottom: '0' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Style de police
              </label>
              <select
                value={element.headerFontStyle || element.fontStyle || 'normal'}
                onChange={(e) => onChange(element.id, 'headerFontStyle', e.target.value)}
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
          </div>
          )}

          {/* Police du corps du texte */}
          <div style={{ marginBottom: '16px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }}>
            <h4 style={{ margin: '0 0 12px 0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
              Police du corps du texte
            </h4>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Taille de police
              </label>
              <input
                type="number"
                min="8"
                max="24"
                value={element.bodyFontSize || element.fontSize || 12}
                onChange={(e) => onChange(element.id, 'bodyFontSize', parseInt(e.target.value) || 12)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              />
            </div>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Famille de police
              </label>
              <select
                value={element.bodyFontFamily || element.fontFamily || 'Arial'}
                onChange={(e) => onChange(element.id, 'bodyFontFamily', e.target.value)}
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

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Épaisseur de police
              </label>
              <select
                value={element.bodyFontWeight || element.fontWeight || 'normal'}
                onChange={(e) => onChange(element.id, 'bodyFontWeight', e.target.value)}
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

            <div style={{ marginBottom: '0' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Style de police
              </label>
              <select
                value={element.bodyFontStyle || element.fontStyle || 'normal'}
                onChange={(e) => onChange(element.id, 'bodyFontStyle', e.target.value)}
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
          </div>

          {/* Anciens contrôles de police (pour compatibilité) */}
          <div style={{ marginBottom: '12px', opacity: 0.6 }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px', color: '#666' }}>
              ⚠️ Paramètres généraux (obsolètes - utilisez les sections ci-dessus)
            </label>
            <input
              type="number"
              min="8"
              max="24"
              value={element.fontSize || 12}
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

          <div style={{ marginBottom: '12px', opacity: 0.6 }}>
            <select
              value={element.fontFamily || 'Arial'}
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

          <div style={{ marginBottom: '12px', opacity: 0.6 }}>
            <select
              value={element.fontWeight || 'normal'}
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

          <div style={{ marginBottom: '12px', opacity: 0.6 }}>
            <select
              value={element.fontStyle || 'normal'}
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
              value={element.backgroundColor || 'transparent'}
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
              value={element.borderColor || '#e5e7eb'}
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
              value={element.textColor || '#374151'}
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
              value={element.textAlign || 'left'}
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
              value={element.verticalAlign || 'top'}
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
