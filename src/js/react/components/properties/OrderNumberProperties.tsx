import { useState, ReactNode } from 'react';
import { OrderNumberElement } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';
import { ColorPropertyInput } from '../ui/ColorPropertyInput';

// Composant Accordion personnalisé
const Accordion = ({ title, children, defaultOpen = false }: {
  title: string;
  children: ReactNode;
  defaultOpen?: boolean;
}) => {
  const [isOpen, setIsOpen] = useState(defaultOpen);

  return (
    <div style={{ marginBottom: '16px', border: '1px solid #e9ecef', borderRadius: '4px', overflow: 'hidden' }}>
      <div
        onClick={() => setIsOpen(!isOpen)}
        style={{
          padding: '12px',
          backgroundColor: '#f8f9fa',
          cursor: 'pointer',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          borderBottom: isOpen ? '1px solid #e9ecef' : 'none'
        }}
      >
        <h4 style={{ margin: '0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
          {title}
        </h4>
        <span style={{
          fontSize: '12px',
          color: '#6c757d',
          transform: isOpen ? 'rotate(180deg)' : 'rotate(0deg)',
          transition: 'transform 0.2s ease'
        }}>
          ▼
        </span>
      </div>

      {isOpen && (
        <div style={{ padding: '12px', backgroundColor: '#ffffff' }}>
          {children}
        </div>
      )}
    </div>
  );
};

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
          {/* Accordéon Propriétés de texte générales */}
          <Accordion title="Propriétés de texte générales" defaultOpen={true}>
            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Police générale
              </label>
              <select
                value={String(element.fontFamily || 'Arial')}
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
                <option value="Courier New">Courier New</option>
                <option value="Impact">Impact</option>
                <option value="Lucida Sans">Lucida Sans</option>
                <option value="Comic Sans MS">Comic Sans MS</option>
                <option value="Lucida Console">Lucida Console</option>
              </select>
            </div>

            <div style={{ marginBottom: '12px' }}>
              <NumericPropertyInput
                label="Taille de police générale"
                value={element.fontSize || 14}
                defaultValue={14}
                min={8}
                max={72}
                unit="px"
                onChange={(value) => onChange(element.id, 'fontSize', value)}
              />
            </div>

            <ColorPropertyInput
              label="Couleur de texte générale"
              value={element.color || '#000000'}
              defaultValue="#000000"
              onChange={(value) => onChange(element.id, 'color', value)}
            />

            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Style de police général
              </label>
              <select
                value={String(element.fontWeight || 'normal')}
                onChange={(e) => onChange(element.id, 'fontWeight', e.target.value)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              >
                <option value="normal">Normal</option>
                <option value="bold">Gras</option>
                <option value="100">100 (Très fin)</option>
                <option value="200">200</option>
                <option value="300">300 (Fin)</option>
                <option value="400">400 (Normal)</option>
                <option value="500">500</option>
                <option value="600">600</option>
                <option value="700">700 (Gras)</option>
                <option value="800">800</option>
                <option value="900">900 (Très gras)</option>
              </select>
            </div>

            <div style={{ marginBottom: '12px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Style italique général
              </label>
              <select
                value={String(element.fontStyle || 'normal')}
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
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Alignement du texte
              </label>
              <select
                value={String(element.textAlign || 'left')}
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
                <option value="justify">Justifié</option>
              </select>
            </div>
          </Accordion>

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />
          {/* Section Structure de l'information */}
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
              Structure des informations
            </div>
            <div style={{ paddingLeft: '8px' }}>
              <Toggle
                checked={element.showHeaders !== false}
                onChange={(checked) => onChange(element.id, 'showHeaders', checked)}
                label="Afficher les en-têtes"
                description="Affiche les titres des sections"
              />

              <Toggle
                checked={element.showBackground !== false}
                onChange={(checked) => onChange(element.id, 'showBackground', checked)}
                label="Afficher le fond"
                description="Affiche un fond coloré derrière le numéro de commande"
              />

              <Toggle
                checked={element.showBorders !== false}
                onChange={(checked) => onChange(element.id, 'showBorders', checked)}
                label="Afficher les bordures"
                description="Affiche les bordures autour des sections"
              />
            </div>
          </div>

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
                checked={element.showLabel !== false}
                onChange={(checked) => onChange(element.id, 'showLabel', checked)}
                label="Afficher le libellé"
                description="Affiche un texte devant le numéro de commande"
              />

              <Toggle
                checked={element.showDate !== false}
                onChange={(checked) => onChange(element.id, 'showDate', checked)}
                label="Afficher la date"
                description="Affiche la date de commande"
              />
            </div>
          </div>

          {/* Section Configuration du libellé */}
          {element.showLabel !== false && (
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
                Configuration du libellé
              </div>
              <div style={{ paddingLeft: '8px' }}>
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

                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                    Position du libellé
                  </label>
                  <select
                    value={String(element.labelPosition || 'above')}
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
              </div>
            </div>
          )}

          {/* Section Format de date */}
          {element.showDate !== false && (
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
                Format de date
              </div>
              <div style={{ paddingLeft: '8px' }}>
                <div style={{ marginBottom: '12px' }}>
                  <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                    Format d&apos;affichage
                  </label>
                  <select
                    value={String(element.dateFormat || 'DD/MM/YYYY')}
                    onChange={(e) => onChange(element.id, 'dateFormat', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '6px',
                      border: '1px solid #ccc',
                      borderRadius: '4px',
                      fontSize: '12px'
                    }}
                  >
                    <option value="DD/MM/YYYY">JJ/MM/AAAA (31/12/2024)</option>
                    <option value="MM/DD/YYYY">MM/JJ/AAAA (12/31/2024)</option>
                    <option value="DD-MM-YYYY">JJ-MM-AAAA (31-12-2024)</option>
                    <option value="YYYY-MM-DD">AAAA-MM-JJ (2024-12-31)</option>
                    <option value="DD MMM YYYY">JJ MMM AAAA (31 déc. 2024)</option>
                  </select>
                  <div style={{ fontSize: '10px', color: '#666', marginTop: '4px' }}>
                    Format d&apos;affichage de la date de commande
                  </div>
                </div>
              </div>
            </div>
          )}

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
          {/* Accordéon Thèmes prédéfinis */}
          <Accordion title="Thèmes prédéfinis" defaultOpen={true}>
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
                    headerTextColor: '#111827',
                    showHeaders: true,
                    showBackground: true,
                    showBorders: true
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
                    headerTextColor: '#334155',
                    showHeaders: true,
                    showBackground: true,
                    showBorders: true
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
                    headerTextColor: '#581c87',
                    showHeaders: true,
                    showBackground: true,
                    showBorders: true
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
                    borderColor: '#e5e7eb',
                    textColor: '#374151',
                    headerTextColor: '#111827',
                    showHeaders: true,
                    showBackground: false,
                    showBorders: true
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
                    borderColor: 'transparent',
                    textColor: '#6b7280',
                    headerTextColor: '#374151',
                    showHeaders: false,
                    showBackground: false,
                    showBorders: false
                  }
                }
              ].map(theme => (
                <button
                  key={theme.id}
                  onClick={() => {
                    // Appliquer toutes les propriétés du thème
                    Object.entries(theme.styles).forEach(([property, value]) => {
                      if (property !== 'backgroundColor' || theme.styles.showBackground !== false) {
                        onChange(element.id, property, value);
                      }
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
          </Accordion>

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          {element.showLabel !== false && element.showHeaders !== false && (
            <Accordion title="Police du libellé" defaultOpen={false}>
              <div style={{ marginBottom: '8px' }}>
                <NumericPropertyInput
                  label="Taille de police"
                  value={element.headerFontSize || (element.fontSize || 14) + 2}
                  defaultValue={(element.fontSize || 14) + 2}
                  min={8}
                  max={32}
                  unit="px"
                  onChange={(value) => onChange(element.id, 'headerFontSize', value)}
                />
              </div>

              <div style={{ marginBottom: '8px' }}>
                <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Famille de police
                </label>
                <select
                  value={String(element.headerFontFamily || element.fontFamily || 'Arial')}
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
                  <option value="Courier New">Courier New</option>
                  <option value="Impact">Impact</option>
                  <option value="Lucida Sans">Lucida Sans</option>
                  <option value="Comic Sans MS">Comic Sans MS</option>
                  <option value="Lucida Console">Lucida Console</option>
                </select>
              </div>

              <div style={{ marginBottom: '8px' }}>
                <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Épaisseur de police
                </label>
                <select
                  value={String(element.headerFontWeight || element.fontWeight || 'bold')}
                  onChange={(e) => onChange(element.id, 'headerFontWeight', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '4px 8px',
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    fontSize: '12px'
                  }}
                >
                  <option value="normal">Normal</option>
                  <option value="bold">Gras</option>
                  <option value="100">100 (Très fin)</option>
                  <option value="200">200</option>
                  <option value="300">300 (Fin)</option>
                  <option value="400">400 (Normal)</option>
                  <option value="500">500</option>
                  <option value="600">600</option>
                  <option value="700">700 (Gras)</option>
                  <option value="800">800</option>
                  <option value="900">900 (Très gras)</option>
                </select>
              </div>

              <div style={{ marginBottom: '8px' }}>
                <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                  Style de police
                </label>
                <select
                  value={String(element.headerFontStyle || element.fontStyle || 'normal')}
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

              <ColorPropertyInput
                label="Couleur de police"
                value={element.headerTextColor || element.textColor || '#111827'}
                defaultValue="#111827"
                onChange={(value) => onChange(element.id, 'headerTextColor', value)}
              />
            </Accordion>
          )}

          {/* Accordéon Police du numéro et de la date */}
          <Accordion title="Police du numéro et de la date" defaultOpen={false}>
            <div style={{ marginBottom: '8px' }}>
              <NumericPropertyInput
                label="Taille de police du numéro"
                value={element.numberFontSize || element.bodyFontSize || element.fontSize || 14}
                defaultValue={element.bodyFontSize || element.fontSize || 14}
                min={8}
                max={24}
                unit="px"
                onChange={(value) => onChange(element.id, 'numberFontSize', value)}
              />
            </div>

            <div style={{ marginBottom: '8px' }}>
              <NumericPropertyInput
                label="Taille de police de la date"
                value={element.dateFontSize || (element.bodyFontSize || element.fontSize || 14) - 2}
                defaultValue={(element.bodyFontSize || element.fontSize || 14) - 2}
                min={8}
                max={24}
                unit="px"
                onChange={(value) => onChange(element.id, 'dateFontSize', value)}
              />
            </div>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Famille de police
              </label>
              <select
                value={String(element.bodyFontFamily || element.fontFamily || 'Arial')}
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
                <option value="Courier New">Courier New</option>
                <option value="Impact">Impact</option>
                <option value="Lucida Sans">Lucida Sans</option>
                <option value="Comic Sans MS">Comic Sans MS</option>
                <option value="Lucida Console">Lucida Console</option>
              </select>
            </div>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Épaisseur de police
              </label>
              <select
                value={String(element.bodyFontWeight || element.fontWeight || 'normal')}
                onChange={(e) => onChange(element.id, 'bodyFontWeight', e.target.value)}
                style={{
                  width: '100%',
                  padding: '4px 8px',
                  border: '1px solid #ccc',
                  borderRadius: '3px',
                  fontSize: '12px'
                }}
              >
                <option value="normal">Normal</option>
                <option value="bold">Gras</option>
                <option value="100">100 (Très fin)</option>
                <option value="200">200</option>
                <option value="300">300 (Fin)</option>
                <option value="400">400 (Normal)</option>
                <option value="500">500</option>
                <option value="600">600</option>
                <option value="700">700 (Gras)</option>
                <option value="800">800</option>
                <option value="900">900 (Très gras)</option>
              </select>
            </div>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Style de police
              </label>
              <select
                value={String(element.bodyFontStyle || element.fontStyle || 'normal')}
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

            <ColorPropertyInput
              label="Couleur de police"
              value={element.textColor || '#374151'}
              defaultValue="#374151"
              onChange={(value) => onChange(element.id, 'textColor', value)}
            />
          </Accordion>

          {/* Accordéon Couleurs */}
          <Accordion title="Couleurs" defaultOpen={false}>
            <ColorPropertyInput
              label="Couleur du texte du libellé"
              value={element.headerTextColor || element.textColor || '#111827'}
              defaultValue="#111827"
              onChange={(value) => onChange(element.id, 'headerTextColor', value)}
            />

            <ColorPropertyInput
              label="Couleur du texte du numéro"
              value={element.textColor || '#374151'}
              defaultValue="#374151"
              onChange={(value) => onChange(element.id, 'textColor', value)}
            />

            {element.showBackground !== false && (
              <ColorPropertyInput
                label="Couleur de fond"
                value={element.backgroundColor || '#ffffff'}
                defaultValue="#ffffff"
                onChange={(value) => onChange(element.id, 'backgroundColor', value)}
              />
            )}

            <ColorPropertyInput
              label="Couleur des bordures"
              value={element.borderColor || '#e5e7eb'}
              defaultValue="#e5e7eb"
              onChange={(value) => onChange(element.id, 'borderColor', value)}
            />
          </Accordion>
        </>
      )}

      {/* Onglet Positionnement */}
      {currentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Position X"
              value={element.x}
              defaultValue={0}
              unit="px"
              onChange={(value) => onChange(element.id, 'x', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Position Y"
              value={element.y}
              defaultValue={0}
              unit="px"
              onChange={(value) => onChange(element.id, 'y', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Largeur"
              value={element.width}
              defaultValue={200}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, 'width', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Hauteur"
              value={element.height}
              defaultValue={40}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, 'height', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement du contenu
            </label>
            <select
              value={String(element.contentAlign || 'left')}
              onChange={(e) => onChange(element.id, 'contentAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '4px 8px',
                border: '1px solid #ccc',
                borderRadius: '3px',
                fontSize: '12px'
              }}
            >
              <option value="left">Aligner à gauche</option>
              <option value="center">Centrer</option>
              <option value="right">Aligner à droite</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px' }}>
              Alignement horizontal
            </label>
            <select
              value={String(element.textAlign || 'left')}
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
              <option value="justify">Justifié</option>
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Padding interne (px)"
              value={element.padding || 12}
              defaultValue={12}
              min={0}
              max={50}
              onChange={(value) => onChange(element.id, 'padding', value)}
            />
          </div>
        </>
      )}
    </>
  );
}



