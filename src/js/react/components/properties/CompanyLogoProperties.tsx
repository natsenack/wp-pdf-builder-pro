import { CompanyLogoElement } from '../../types/elements';
import { NumericPropertyInput } from '../ui/NumericPropertyInput';

// Déclaration des types WordPress pour TypeScript
declare global {
  interface Window {
    wp?: {
      media?: (options?: Record<string, unknown>) => Record<string, unknown>;
    };
  }
}

interface CompanyLogoPropertiesProps {
  element: CompanyLogoElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
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
            <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
              <input
                type="text"
                value={element.src || ''}
                onChange={(e) => onChange(element.id, 'src', e.target.value)}
                placeholder="https://exemple.com/logo.png"
                style={{
                  flex: 1,
                  padding: '6px',
                  border: '1px solid #ccc',
                  borderRadius: '4px',
                  fontSize: '12px'
                }}
              />
              <button
                onClick={() => {
                  // Ouvrir la bibliothèque de médias WordPress
                  if (!window.wp?.media || typeof window.wp.media !== 'function') {
                    const errorMsg = 'Bibliothèque de médias WordPress non disponible (wp_enqueue_media non appelé ?)';

                    alert(errorMsg + '\n\nSaisissez l\'URL manuellement.');
                    return;
                  }

                  // eslint-disable-next-line @typescript-eslint/no-explicit-any
                  const mediaUploader: any = window.wp.media({
                    title: 'Sélectionner un logo',
                    button: {
                      text: 'Utiliser ce logo'
                    },
                    multiple: false,
                    library: {
                      type: 'image'
                    }
                  });

                  // eslint-disable-next-line @typescript-eslint/no-explicit-any
                  mediaUploader.on('select', () => {
                    // eslint-disable-next-line @typescript-eslint/no-explicit-any
                    const attachment = mediaUploader.state().get('selection').first().toJSON();

                    if (!attachment || !attachment.url) {
                      alert('Erreur: L\'image sélectionnée n\'a pas d\'URL valide');
                      return;
                    }

                    // Mettre à jour l'URL
                    onChange(element.id, 'src', attachment.url);

                    // Optionnellement, mettre à jour les dimensions si elles sont par défaut
                    if (!element.width || element.width === 150) {
                      onChange(element.id, 'width', attachment.width || 150);
                    }
                    if (!element.height || element.height === 80) {
                      onChange(element.id, 'height', attachment.height || 80);
                    }
                  });

                  mediaUploader.open();
                }}
                style={{
                  padding: '6px 12px',
                  backgroundColor: '#007bff',
                  color: '#fff',
                  border: 'none',
                  borderRadius: '4px',
                  cursor: 'pointer',
                  fontSize: '11px',
                  fontWeight: 'bold',
                  whiteSpace: 'nowrap'
                }}
                title="Sélectionner depuis la bibliothèque WordPress"
              >
                📁 Choisir
              </button>
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Texte alternatif
            </label>
            <input
              type="text"
              value={element.altText || ''}
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
              checked={element.maintainAspectRatio !== false}
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
              checked={element.showBorder === true}
              onChange={(e) => onChange(element.id, 'showBorder', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Affiche une bordure autour du logo</span>
          </div>

          {element.showBorder && (
            <>
              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Épaisseur de la bordure
                </label>
                <input
                  type="range"
                  min="1"
                  max="10"
                  step="1"
                  value={element.borderWidth || 1}
                  onChange={(e) => onChange(element.id, 'borderWidth', parseInt(e.target.value))}
                  style={{
                    width: '100%',
                    marginTop: '4px'
                  }}
                />
                <div style={{ fontSize: '11px', color: '#666', textAlign: 'center', marginTop: '2px' }}>
                  {element.borderWidth || 1} px
                </div>
              </div>

              <div style={{ marginBottom: '12px' }}>
                <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
                  Couleur de la bordure
                </label>
                <input
                  type="color"
                  value={element.borderColor || '#e5e7eb'}
                  onChange={(e) => onChange(element.id, 'borderColor', e.target.value)}
                  style={{
                    width: '100%',
                    height: '36px',
                    border: '1px solid #ccc',
                    borderRadius: '4px',
                    cursor: 'pointer'
                  }}
                />
              </div>
            </>
          )}
        </>
      )}

      {/* Onglet Personnalisation */}
      {logoCurrentTab === 'personnalisation' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Mode d&apos;ajustement
            </label>
            <select
              value={element.objectFit || 'contain'}
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
              value={element.opacity || 1}
              onChange={(e) => onChange(element.id, 'opacity', parseFloat(e.target.value))}
              style={{
                width: '100%',
                marginTop: '4px'
              }}
            />
            <div style={{ fontSize: '11px', color: '#666', textAlign: 'center', marginTop: '2px' }}>
              {element.opacity || 1}
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Rayon des coins"
              value={element.borderRadius}
              defaultValue={0}
              min={0}
              max={50}
              unit="px"
              onChange={(value) => onChange(element.id, 'borderRadius', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Rotation"
              value={element.rotation}
              defaultValue={0}
              min={-180}
              max={180}
              unit="°"
              onChange={(value) => onChange(element.id, 'rotation', value)}
            />
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {logoCurrentTab === 'positionnement' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Position X"
              value={element.x}
              defaultValue={0}
              min={0}
              max={1000}
              unit="px"
              onChange={(value) => onChange(element.id, 'x', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Position Y"
              value={element.y}
              defaultValue={0}
              min={0}
              max={1000}
              unit="px"
              onChange={(value) => onChange(element.id, 'y', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Largeur"
              value={element.width}
              defaultValue={100}
              min={10}
              max={1000}
              unit="px"
              onChange={(value) => onChange(element.id, 'width', value)}
            />
          </div>

          <div style={{ marginBottom: '12px' }}>
            <NumericPropertyInput
              label="Hauteur"
              value={element.height}
              defaultValue={100}
              min={10}
              max={1000}
              unit="px"
              onChange={(value) => onChange(element.id, 'height', value)}
            />
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

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Alignement horizontal
            </label>
            <select
              value={element.horizontalAlign || element.alignment || 'left'}
              onChange={(e) => onChange(element.id, 'horizontalAlign', e.target.value)}
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
              Alignement vertical
            </label>
            <select
              value={element.verticalAlign || 'center'}
              onChange={(e) => onChange(element.id, 'verticalAlign', e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              <option value="top">Haut</option>
              <option value="center">Centre</option>
              <option value="bottom">Bas</option>
            </select>
          </div>
        </>
      )}
    </>
  );
}



