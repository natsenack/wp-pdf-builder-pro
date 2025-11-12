import React, { useState } from 'react';
import { CompanyInfoElement } from '../../types/elements';

interface CompanyInfoPropertiesProps {
  element: CompanyInfoElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

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

export function CompanyInfoProperties({ element, onChange, activeTab, setActiveTab }: CompanyInfoPropertiesProps) {
  const companyCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setCompanyCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  // État pour les accordéons de police
  const [fontAccordions, setFontAccordions] = useState({
    headerFont: false, // Accordéon du nom de l'entreprise fermé par défaut
    bodyFont: false   // Accordéon des informations fermé par défaut
  });

  const toggleAccordion = (accordion: 'headerFont' | 'bodyFont') => {
    setFontAccordions(prev => ({
      ...prev,
      [accordion]: !prev[accordion]
    }));
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
          <Toggle
            checked={element.showBackground !== false}
            onChange={(checked) => onChange(element.id, 'showBackground', checked)}
            label="Afficher le fond"
            description="Affiche un fond coloré derrière les informations"
          />

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
            checked={element.showCompanyName !== false}
            onChange={(checked) => onChange(element.id, 'showCompanyName', checked)}
            label="Afficher le nom de l'entreprise"
            description="Nom de l'entreprise"
          />

          <Toggle
            checked={element.showAddress !== false}
            onChange={(checked) => onChange(element.id, 'showAddress', checked)}
            label="Afficher l'adresse"
            description="Adresse complète de l'entreprise"
          />

          <Toggle
            checked={element.showPhone !== false}
            onChange={(checked) => onChange(element.id, 'showPhone', checked)}
            label="Afficher le téléphone"
            description="Numéro de téléphone"
          />

          <Toggle
            checked={element.showEmail !== false}
            onChange={(checked) => onChange(element.id, 'showEmail', checked)}
            label="Afficher l'email"
            description="Adresse email de l'entreprise"
          />

          <Toggle
            checked={element.showSiret !== false}
            onChange={(checked) => onChange(element.id, 'showSiret', checked)}
            label="Afficher le numéro SIRET"
            description="Numéro SIRET de l'entreprise"
          />

          <Toggle
            checked={element.showTva !== false}
            onChange={(checked) => onChange(element.id, 'showTva', checked)}
            label="Afficher le numéro TVA"
            description="Numéro TVA de l'entreprise"
          />
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
                    border: element.theme === theme.id ? '2px solid #007bff' : '2px solid transparent',
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

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          {/* Police du nom de l'entreprise - Accordéon */}
          {element.showHeaders !== false && (
            <div style={{ marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }}>
              <div
                onClick={() => toggleAccordion('headerFont')}
                style={{
                  padding: '12px',
                  backgroundColor: '#f8f9fa',
                  cursor: 'pointer',
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  borderBottom: fontAccordions.headerFont ? '1px solid #e9ecef' : 'none'
                }}
              >
                <h4 style={{ margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
                  Police du nom de l&apos;entreprise
                </h4>
                <span style={{
                  fontSize: '12px',
                  color: '#6c757d',
                  transform: fontAccordions.headerFont ? 'rotate(180deg)' : 'rotate(0deg)',
                  transition: 'transform 0.2s ease'
                }}>
                  ▼
                </span>
              </div>

              {fontAccordions.headerFont && (
                <div style={{ padding: '12px', backgroundColor: '#ffffff' }}>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Taille de police
              </label>
              <input
                type="number"
                min="10"
                max="32"
                value={element.headerFontSize || Math.round((element.fontSize || 12) * 1.2)}
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
                value={element.headerFontWeight || 'bold'}
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
            </div>
          )}

          {/* Police du corps du texte - Accordéon */}
          <div style={{ marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }}>
            <div
              onClick={() => toggleAccordion('bodyFont')}
              style={{
                padding: '12px',
                backgroundColor: '#f8f9fa',
                cursor: 'pointer',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                borderBottom: fontAccordions.bodyFont ? '1px solid #e9ecef' : 'none'
              }}
            >
              <h4 style={{ margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
                Police des informations
              </h4>
              <span style={{
                fontSize: '12px',
                color: '#6c757d',
                transform: fontAccordions.bodyFont ? 'rotate(180deg)' : 'rotate(0deg)',
                transition: 'transform 0.2s ease'
              }}>
                ▼
              </span>
            </div>

            {fontAccordions.bodyFont && (
              <div style={{ padding: '12px', backgroundColor: '#ffffff' }}>

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
            )}
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Alignement du texte
            </label>
            <select
              value={element.textAlign || 'left'}
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
              Couleur de fond
            </label>
            <select
              value={element.backgroundColor === 'transparent' ? 'none' : 'custom'}
              onChange={(e) => {
                if (e.target.value === 'none') {
                  onChange(element.id, 'backgroundColor', 'transparent');
                } else {
                  // Si on passe de "aucun fond" à "couleur personnalisée", utiliser la valeur par défaut
                  onChange(element.id, 'backgroundColor', element.backgroundColor === 'transparent' ? '#ffffff' : element.backgroundColor || '#ffffff');
                }
              }}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px',
                marginBottom: '6px'
              }}
            >
              <option value="custom">Couleur personnalisée</option>
              <option value="none">Aucun fond</option>
            </select>
            {element.backgroundColor !== 'transparent' && (
              <input
                type="color"
                value={element.backgroundColor || '#ffffff'}
                onChange={(e) => onChange(element.id, 'backgroundColor', e.target.value)}
                style={{
                  width: '100%',
                  height: '32px',
                  border: '1px solid #ccc',
                  borderRadius: '4px',
                  cursor: 'pointer'
                }}
              />
            )}
          </div>

          {element.showBorders !== false && (
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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
                  borderRadius: '4px',
                  cursor: 'pointer'
                }}
              />
            </div>
          )}

          {element.showBorders !== false && (
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                Épaisseur de la bordure
              </label>
              <select
                value={String(element.borderWidth || '1')}
                onChange={(e) => onChange(element.id, 'borderWidth', e.target.value)}
                style={{
                  width: '100%',
                  padding: '6px',
                  border: '1px solid #ccc',
                  borderRadius: '4px',
                  fontSize: '12px'
                }}
              >
                <option value="0.5">Fin (0.5px)</option>
                <option value="1">Normal (1px)</option>
                <option value="1.5">Moyen (1.5px)</option>
                <option value="2">Épais (2px)</option>
                <option value="3">Très épais (3px)</option>
              </select>
            </div>
          )}

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Couleur du texte
            </label>
            <input
              type="color"
              value={element.textColor || '#000000'}
              onChange={(e) => onChange(element.id, 'textColor', e.target.value)}
              style={{
                width: '100%',
                height: '32px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                cursor: 'pointer'
              }}
            />
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
              value={element.height || 100}
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
