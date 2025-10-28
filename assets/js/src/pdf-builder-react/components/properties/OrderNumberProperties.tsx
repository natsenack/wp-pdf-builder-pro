import React from 'react';
import { Element } from '../../types/elements';

interface OrderNumberPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function OrderNumberProperties({ element, onChange, activeTab, setActiveTab }: OrderNumberPropertiesProps) {
  const orderCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setOrderCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  const orderNumberThemes = [
    {
      id: 'standard',
      name: 'Standard',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #e5e7eb',
          borderRadius: '4px',
          backgroundColor: '#ffffff',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '12px',
          fontWeight: 'bold',
          color: '#374151'
        }}>
          #12345
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#e5e7eb',
        textColor: '#374151',
        fontWeight: 'normal'
      }
    },
    {
      id: 'bold',
      name: 'Gras',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #1f2937',
          borderRadius: '4px',
          backgroundColor: '#ffffff',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '12px',
          fontWeight: 'bold',
          color: '#111827'
        }}>
          #12345
        </div>
      ),
      styles: {
        backgroundColor: '#ffffff',
        borderColor: '#1f2937',
        textColor: '#111827',
        fontWeight: 'bold'
      }
    },
    {
      id: 'colored',
      name: 'Coloré',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #3b82f6',
          borderRadius: '4px',
          backgroundColor: '#eff6ff',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '12px',
          fontWeight: 'bold',
          color: '#1e40af'
        }}>
          #12345
        </div>
      ),
      styles: {
        backgroundColor: '#eff6ff',
        borderColor: '#3b82f6',
        textColor: '#1e40af',
        fontWeight: 'bold'
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
          alignItems: 'center',
          justifyContent: 'center',
          fontSize: '12px',
          fontWeight: 'normal',
          color: '#6b7280'
        }}>
          #12345
        </div>
      ),
      styles: {
        backgroundColor: 'transparent',
        borderColor: 'transparent',
        textColor: '#6b7280',
        fontWeight: 'normal'
      }
    }
  ];

  return (
    <>
      {/* Système d'onglets pour Order Number */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setOrderCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: orderCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: orderCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setOrderCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: orderCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: orderCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setOrderCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: orderCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: orderCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {orderCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Afficher le libellé
            </label>
            <input
              type="checkbox"
              checked={(element as any).showLabel !== false}
              onChange={(e) => onChange(element.id, 'showLabel', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche "N° de commande:"</span>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Position du libellé
            </label>
            <select
              value={(element as any).labelPosition || 'above'}
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
      {orderCurrentTab === 'personnalisation' && (
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
              {orderNumberThemes.map((theme) => (
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
              value={(element as any).fontSize || '14'}
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

          <hr style={{ margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' }} />

          {/* Police du label - Uniquement si le libellé est affiché */}
          {(element as any).showLabel !== false && (
            <div style={{ marginBottom: '16px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }}>
              <h4 style={{ margin: '0 0 12px 0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
                Police du label
              </h4>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Taille de police
              </label>
              <input
                type="number"
                min="8"
                max="24"
                value={(element as any).labelFontSize || (element as any).fontSize || 14}
                onChange={(e) => onChange(element.id, 'labelFontSize', parseInt(e.target.value) || 14)}
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
                value={(element as any).labelFontFamily || (element as any).fontFamily || 'Arial'}
                onChange={(e) => onChange(element.id, 'labelFontFamily', e.target.value)}
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
                value={(element as any).labelFontWeight || 'bold'}
                onChange={(e) => onChange(element.id, 'labelFontWeight', e.target.value)}
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
                value={(element as any).labelFontStyle || (element as any).fontStyle || 'normal'}
                onChange={(e) => onChange(element.id, 'labelFontStyle', e.target.value)}
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

            <div style={{ marginBottom: '0', marginTop: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Alignement du texte
              </label>
              <select
                value={(element as any).labelTextAlign || (element as any).textAlign || 'left'}
                onChange={(e) => onChange(element.id, 'labelTextAlign', e.target.value)}
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
          </div>
          )}

          {/* Police du numéro */}
          <div style={{ marginBottom: '16px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }}>
            <h4 style={{ margin: '0 0 12px 0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
              Police du numéro
            </h4>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Taille de police
              </label>
              <input
                type="number"
                min="8"
                max="24"
                value={(element as any).numberFontSize || (element as any).fontSize || 14}
                onChange={(e) => onChange(element.id, 'numberFontSize', parseInt(e.target.value) || 14)}
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
                value={(element as any).numberFontFamily || (element as any).fontFamily || 'Arial'}
                onChange={(e) => onChange(element.id, 'numberFontFamily', e.target.value)}
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
                value={(element as any).numberFontWeight || (element as any).fontWeight || 'normal'}
                onChange={(e) => onChange(element.id, 'numberFontWeight', e.target.value)}
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
                value={(element as any).numberFontStyle || (element as any).fontStyle || 'normal'}
                onChange={(e) => onChange(element.id, 'numberFontStyle', e.target.value)}
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

            <div style={{ marginBottom: '0', marginTop: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Alignement du texte
              </label>
              <select
                value={(element as any).numberTextAlign || (element as any).textAlign || 'left'}
                onChange={(e) => onChange(element.id, 'numberTextAlign', e.target.value)}
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
          </div>

          {/* Police de la date - Uniquement si la date est affichée */}
          {(element as any).showDate !== false && (
            <div style={{ marginBottom: '16px', padding: '12px', backgroundColor: '#f8f9fa', borderRadius: '4px', border: '1px solid #e9ecef' }}>
              <h4 style={{ margin: '0 0 12px 0', fontSize: '13px', fontWeight: 'bold', color: '#495057' }}>
                Police de la date
              </h4>

            <div style={{ marginBottom: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Taille de police
              </label>
              <input
                type="number"
                min="6"
                max="20"
                value={(element as any).dateFontSize || ((element as any).fontSize || 14) - 2}
                onChange={(e) => onChange(element.id, 'dateFontSize', parseInt(e.target.value) || 12)}
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
                value={(element as any).dateFontFamily || (element as any).fontFamily || 'Arial'}
                onChange={(e) => onChange(element.id, 'dateFontFamily', e.target.value)}
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
                value={(element as any).dateFontWeight || (element as any).fontWeight || 'normal'}
                onChange={(e) => onChange(element.id, 'dateFontWeight', e.target.value)}
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
                value={(element as any).dateFontStyle || (element as any).fontStyle || 'normal'}
                onChange={(e) => onChange(element.id, 'dateFontStyle', e.target.value)}
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

            <div style={{ marginBottom: '0', marginTop: '8px' }}>
              <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                Alignement du texte
              </label>
              <select
                value={(element as any).dateTextAlign || (element as any).textAlign || 'left'}
                onChange={(e) => onChange(element.id, 'dateTextAlign', e.target.value)}
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
          </div>
          )}

          {/* Anciens contrôles de police (pour compatibilité) */}
          <div style={{ marginBottom: '12px', opacity: 0.6 }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '4px', color: '#666' }}>
              ⚠️ Paramètres généraux (obsolètes - utilisez les sections ci-dessus)
            </label>
            <select
              value={(element as any).fontSize || '14'}
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
        </>
      )}

      {/* Onglet Positionnement */}
      {orderCurrentTab === 'positionnement' && (
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
              value={(element as any).width || 150}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 150)}
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
              value={(element as any).height || 30}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 30)}
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