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
      key: 'tva',
      label: 'TVA et mentions fiscales',
      text: 'TVA non applicable, art. 293 B du CGI. Mention : auto-entrepreneur soumise à l\'impôt sur le revenu.'
    },
    {
      key: 'penalties',
      label: 'Pénalités de retard',
      text: 'Tout retard de paiement donnera lieu au paiement d\'une pénalité égale à 3 fois le taux d\'intérêt légal en vigueur.'
    },
    {
      key: 'property',
      label: 'Réserve de propriété',
      text: 'Les biens vendus restent la propriété du vendeur jusqu\'au paiement intégral du prix.'
    },
    {
      key: 'jurisdiction',
      label: 'Juridiction compétente',
      text: 'Tout litige sera soumis à la compétence exclusive des tribunaux de commerce français.'
    },
    {
      key: 'rgpd',
      label: 'RGPD - Protection des données',
      text: 'Vos données personnelles sont traitées conformément au RGPD. Consultez notre politique de confidentialité.'
    },
    {
      key: 'discount',
      label: 'Escompte',
      text: 'Escompte pour paiement anticipé : 2% du montant HT si paiement sous 8 jours.'
    },
    {
      key: 'clause',
      label: 'Clause de réserve',
      text: 'Sous réserve d\'acceptation de votre commande et de disponibilité des produits.'
    },
    {
      key: 'intellectual',
      label: 'Propriété intellectuelle',
      text: 'Tous droits de propriété intellectuelle réservés. Reproduction interdite sans autorisation.'
    },
    {
      key: 'force',
      label: 'Force majeure',
      text: 'Aucun des parties ne pourra être tenu responsable en cas de force majeure.'
    },
    {
      key: 'liability',
      label: 'Limitation de responsabilité',
      text: 'Notre responsabilité est limitée à la valeur de la commande en cas de faute prouvée.'
    },
    {
      key: 'tva_info',
      label: 'Informations TVA',
      text: 'TVA non applicable - article 293 B du CGI. Régime micro-entreprise.'
    },
    {
      key: 'rcs_info',
      label: 'Informations RCS',
      text: 'RCS Paris 123 456 789 - SIRET 123 456 789 00012 - APE 1234Z'
    },
    {
      key: 'siret_info',
      label: 'Informations SIRET',
      text: 'SIRET 123 456 789 00012 - NAF 1234Z - TVA FR 12 345 678 901'
    },
    {
      key: 'legal_status',
      label: 'Statut juridique',
      text: 'Société à responsabilité limitée au capital de 10 000€ - RCS Paris 123 456 789'
    },
    {
      key: 'insurance',
      label: 'Assurance responsabilité',
      text: 'Couvert par assurance responsabilité civile professionnelle - Police N° 123456789'
    },
    {
      key: 'mediation',
      label: 'Médiation consommateur',
      text: 'En cas de litige, le consommateur peut saisir gratuitement le médiateur compétent.'
    },
    {
      key: 'iban',
      label: 'Coordonnées bancaires',
      text: 'IBAN FR76 1234 5678 9012 3456 7890 123 - BIC BNPAFRPP'
    },
    {
      key: 'delivery',
      label: 'Conditions de livraison',
      text: 'Livraison sous 3-5 jours ouvrés. Frais de port offerts à partir de 50€ HT.'
    },
    {
      key: 'packaging',
      label: 'Emballage et environnement',
      text: 'Emballages recyclables. Respectueux de l\'environnement.'
    },
    {
      key: 'medley',
      label: 'Médley (Combinaison)',
      text: ''
    },
    {
      key: 'custom',
      label: 'Personnalisé',
      text: ''
    }
  ];

  // Détecter automatiquement le type de mention basé sur le texte actuel
  const detectMentionType = () => {
    const currentText = (element as any).text || '';
    const currentMentionType = (element as any).mentionType || 'custom';

    // Si un type est déjà défini et que ce n'est pas custom, le garder
    if (currentMentionType && currentMentionType !== 'custom') {
      return currentMentionType;
    }

    // Pour le medley, vérifier s'il y a des mentions sélectionnées
    if ((element as any).selectedMentions && (element as any).selectedMentions.length > 0) {
      return 'medley';
    }

    // Sinon, essayer de détecter automatiquement
    const matchingMention = predefinedMentions.find(mention =>
      mention.key !== 'custom' && mention.key !== 'medley' && mention.text === currentText
    );

    return matchingMention ? matchingMention.key : 'custom';
  };

  const currentMentionType = detectMentionType();

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
              value={currentMentionType}
              onChange={(e) => {
                const selectedMention = predefinedMentions.find(m => m.key === e.target.value);
                onChange(element.id, 'mentionType', e.target.value);

                // Ne mettre à jour le texte que si ce n'est pas "custom" et qu'il y a du texte prédéfini
                if (selectedMention && selectedMention.key !== 'custom' && selectedMention.key !== 'medley' && selectedMention.text) {
                  onChange(element.id, 'text', selectedMention.text);
                }
                // Pour "custom" et "medley", on garde le texte actuel
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

          {/* Section Médley - Sélection des mentions à combiner */}
          {currentMentionType === 'medley' && (
            <div style={{ marginBottom: '12px', padding: '12px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#f9f9f9' }}>
              <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
                Sélectionnez les mentions à combiner :
              </label>
              <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                gap: '6px',
                maxHeight: '200px',
                overflowY: 'auto'
              }}>
                {predefinedMentions.filter(m => m.key !== 'medley' && m.key !== 'custom').map((mention) => {
                  const selectedMentions = (element as any).selectedMentions || [];
                  const isSelected = selectedMentions.includes(mention.key);

                  return (
                    <label key={mention.key} style={{
                      display: 'flex',
                      alignItems: 'flex-start',
                      fontSize: '11px',
                      cursor: 'pointer',
                      padding: '4px',
                      borderRadius: '3px',
                      backgroundColor: isSelected ? '#e3f2fd' : 'transparent'
                    }}>
                      <input
                        type="checkbox"
                        checked={isSelected}
                        onChange={(e) => {
                          const currentSelected = (element as any).selectedMentions || [];
                          let newSelected;

                          if (e.target.checked) {
                            newSelected = [...currentSelected, mention.key];
                          } else {
                            newSelected = currentSelected.filter((key: string) => key !== mention.key);
                          }

                          onChange(element.id, 'selectedMentions', newSelected);

                          // Générer le texte combiné avec le séparateur configuré
                          const separatorMap = {
                            'double_newline': '\n\n',
                            'single_newline': '\n',
                            'dash': ' - ',
                            'bullet': ' • ',
                            'pipe': ' | '
                          };
                          const separator = separatorMap[((element as any).medleySeparator || 'double_newline') as keyof typeof separatorMap] || '\n\n';

                          const combinedText = newSelected
                            .map((key: string) => predefinedMentions.find(m => m.key === key)?.text)
                            .filter(Boolean)
                            .join(separator);

                          onChange(element.id, 'text', combinedText);

                          // Ajuster automatiquement la hauteur et la largeur selon le contenu
                          const lines = combinedText.split('\n');
                          const fontSize = (element as any).fontSize || 10;
                          const lineHeight = fontSize * 1.4;
                          const padding = 15;
                          const minHeight = 60; // Hauteur minimale basée sur la valeur par défaut
                          const calculatedHeight = Math.max(minHeight, lines.length * lineHeight + padding * 2);
                          const maxHeight = 500;
                          const newHeight = Math.min(calculatedHeight, maxHeight);

                          // Calculer la largeur basée sur la ligne la plus longue
                          const canvas = document.createElement('canvas');
                          const ctx = canvas.getContext('2d');
                          if (ctx) {
                            ctx.font = `${(element as any).fontWeight || 'normal'} ${fontSize}px ${(element as any).fontFamily || 'Arial'}`;
                            const maxLineWidth = Math.max(...lines.map((line: string) => ctx.measureText(line).width));
                            const minWidth = 500; // Largeur minimale basée sur la valeur par défaut
                            const calculatedWidth = Math.max(minWidth, maxLineWidth + padding * 2);
                            const maxWidth = 800; // Largeur maximale
                            const newWidth = Math.min(calculatedWidth, maxWidth);

                            if ((element as any).width !== newWidth) {
                              onChange(element.id, 'width', newWidth);
                            }
                          }

                          if ((element as any).height !== newHeight) {
                            onChange(element.id, 'height', newHeight);
                          }
                        }}
                        style={{ marginRight: '6px', marginTop: '1px' }}
                      />
                      <div>
                        <div style={{ fontWeight: 'bold', marginBottom: '2px' }}>{mention.label}</div>
                        <div style={{ fontSize: '10px', color: '#666', lineHeight: '1.3' }}>
                          {mention.text.length > 60 ? mention.text.substring(0, 60) + '...' : mention.text}
                        </div>
                      </div>
                    </label>
                  );
                })}
              </div>
              <div style={{ marginTop: '8px', fontSize: '10px', color: '#666' }}>
                {(element as any).selectedMentions?.length || 0} mention(s) sélectionnée(s)
                {(element as any).selectedMentions?.length > 0 && (
                  <span style={{ color: '#007bff', marginLeft: '8px' }}>
                    • Dimensions ajustables manuellement (avec clipping)
                  </span>
                )}
              </div>
              {(element as any).selectedMentions?.length > 0 && (
                <div style={{ marginTop: '8px' }}>
                  <label style={{ display: 'block', fontSize: '11px', fontWeight: 'bold', marginBottom: '4px' }}>
                    Séparateur entre mentions :
                  </label>
                  <select
                    value={(element as any).medleySeparator || 'double_newline'}
                    onChange={(e) => {
                      onChange(element.id, 'medleySeparator', e.target.value);

                      // Régénérer le texte avec le nouveau séparateur
                      const selectedMentions = (element as any).selectedMentions || [];
                      const separatorMap = {
                        'double_newline': '\n\n',
                        'single_newline': '\n',
                        'dash': ' - ',
                        'bullet': ' • ',
                        'pipe': ' | '
                      };

                      const separator = separatorMap[e.target.value as keyof typeof separatorMap] || '\n\n';
                      const combinedText = selectedMentions
                        .map((key: string) => predefinedMentions.find(m => m.key === key)?.text)
                        .filter(Boolean)
                        .join(separator);

                      onChange(element.id, 'text', combinedText);

                      // Ajuster la hauteur et la largeur selon le nouveau nombre de lignes
                      const lines = combinedText.split('\n');
                      const fontSize = (element as any).fontSize || 10;
                      const lineHeight = fontSize * 1.4;
                      const padding = 15;
                      const minHeight = 60; // Hauteur minimale basée sur la valeur par défaut
                      const calculatedHeight = Math.max(minHeight, lines.length * lineHeight + padding * 2);
                      const maxHeight = 500;
                      const newHeight = Math.min(calculatedHeight, maxHeight);

                      // Calculer la largeur basée sur la ligne la plus longue
                      const canvas = document.createElement('canvas');
                      const ctx = canvas.getContext('2d');
                      if (ctx) {
                        ctx.font = `${(element as any).fontWeight || 'normal'} ${fontSize}px ${(element as any).fontFamily || 'Arial'}`;
                        const maxLineWidth = Math.max(...lines.map((line: string) => ctx.measureText(line).width));
                        const minWidth = 200;
                        const calculatedWidth = Math.max(minWidth, maxLineWidth + padding * 2);
                        const maxWidth = 800;
                        const newWidth = Math.min(calculatedWidth, maxWidth);

                        if ((element as any).width !== newWidth) {
                          onChange(element.id, 'width', newWidth);
                        }
                      }

                      if ((element as any).height !== newHeight) {
                        onChange(element.id, 'height', newHeight);
                      }
                    }}
                    style={{
                      width: '100%',
                      padding: '4px',
                      border: '1px solid #ccc',
                      borderRadius: '3px',
                      fontSize: '11px'
                    }}
                  >
                    <option value="double_newline">Double saut de ligne</option>
                    <option value="single_newline">Saut de ligne simple</option>
                    <option value="dash">Tiret (-)</option>
                    <option value="bullet">Point (•)</option>
                    <option value="pipe">Barre verticale (|)</option>
                  </select>
                </div>
              )}
            </div>
          )}

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
              onChange={(e) => {
                onChange(element.id, 'fontSize', e.target.value);

                // Si c'est un medley, ajuster la hauteur selon la nouvelle taille de police
                if (currentMentionType === 'medley' && (element as any).selectedMentions?.length > 0) {
                  const selectedMentions = (element as any).selectedMentions || [];
                  const separatorMap = {
                    'double_newline': '\n\n',
                    'single_newline': '\n',
                    'dash': ' - ',
                    'bullet': ' • ',
                    'pipe': ' | '
                  };
                  const separator = separatorMap[((element as any).medleySeparator || 'double_newline') as keyof typeof separatorMap] || '\n\n';

                  const combinedText = selectedMentions
                    .map((key: string) => predefinedMentions.find(m => m.key === key)?.text)
                    .filter(Boolean)
                    .join(separator);

                  const lines = combinedText.split('\n');
                  const fontSize = parseInt(e.target.value) || 10;
                  const lineHeight = fontSize * 1.4;
                  const padding = 15;
                  const minHeight = 60; // Hauteur minimale basée sur la valeur par défaut
                  const calculatedHeight = Math.max(minHeight, lines.length * lineHeight + padding * 2);
                  const maxHeight = 500;
                  const newHeight = Math.min(calculatedHeight, maxHeight);

                  // Calculer la largeur basée sur la ligne la plus longue
                  const canvas = document.createElement('canvas');
                  const ctx = canvas.getContext('2d');
                  if (ctx) {
                    ctx.font = `${(element as any).fontWeight || 'normal'} ${fontSize}px ${(element as any).fontFamily || 'Arial'}`;
                    const maxLineWidth = Math.max(...lines.map((line: string) => ctx.measureText(line).width));
                    const minWidth = 200;
                    const calculatedWidth = Math.max(minWidth, maxLineWidth + padding * 2);
                    const maxWidth = 800;
                    const newWidth = Math.min(calculatedWidth, maxWidth);

                    if ((element as any).width !== newWidth) {
                      onChange(element.id, 'width', newWidth);
                    }
                  }

                  if ((element as any).height !== newHeight) {
                    onChange(element.id, 'height', newHeight);
                  }
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
              Largeur {currentMentionType === 'medley' ? '(manuel = clipping activé)' : ''}
            </label>
            <input
              type="number"
              value={(element as any).width || 500}
              onChange={(e) => onChange(element.id, 'width', parseInt(e.target.value) || 500)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
            {currentMentionType === 'medley' && (
              <div style={{ fontSize: '10px', color: '#666', marginTop: '4px' }}>
                Redimensionner manuellement active le clipping du texte
              </div>
            )}
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Hauteur {currentMentionType === 'medley' ? '(manuel = clipping activé)' : ''}
            </label>
            <input
              type="number"
              value={(element as any).height || 60}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 60)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            />
            {currentMentionType === 'medley' && (
              <div style={{ fontSize: '10px', color: '#666', marginTop: '4px' }}>
                Redimensionner manuellement active le clipping du texte
              </div>
            )}
          </div>
        </>
      )}
    </>
  );
}