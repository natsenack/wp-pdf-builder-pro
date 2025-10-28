import React from 'react';
import { Element } from '../../types/elements';

interface DynamicTextPropertiesProps {
  element: Element;
  onChange: (elementId: string, property: string, value: any) => void;
  activeTab: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' };
  setActiveTab: (tabs: { [key: string]: 'fonctionnalites' | 'personnalisation' | 'positionnement' }) => void;
}

export function DynamicTextProperties({ element, onChange, activeTab, setActiveTab }: DynamicTextPropertiesProps) {
  const dynamicCurrentTab = activeTab[element.id] || 'fonctionnalites';
  const setDynamicCurrentTab = (tab: 'fonctionnalites' | 'personnalisation' | 'positionnement') => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  // Liste des exemples prédéfinis
  const textExamples = [
    // === PROFESSIONNEL ===
    {
      id: 'signature_zone',
      label: 'Zone de signature',
      template: 'Signature du client :\n\n______________________________\n\nDate :\n\n____/____/________'
    },
    {
      id: 'signature_client',
      label: 'Signature client',
      template: 'Signature du client\n\n______________________________\n\nNom et prénom :\n\nDate : ____/____/________'
    },
    {
      id: 'signature_entreprise',
      label: 'Signature entreprise',
      template: 'Pour l\'entreprise :\n\n______________________________\n\n[prenom] [nom]\n[entreprise]'
    },
    {
      id: 'signature_double',
      label: 'Double signature',
      template: 'Signature du client :\n______________________________\n\nSignature de l\'entreprise :\n______________________________\n\nDate : ____/____/________'
    },
    {
      id: 'signature_with_stamp',
      label: 'Signature avec cachet',
      template: 'Signature et cachet :\n\n______________________________\n\n[entreprise]\nCachet de l\'entreprise\n\nDate : ____/____/________'
    },
    {
      id: 'signature_contract',
      label: 'Signature contrat',
      template: 'Fait à [Ville], le [date]\n\nSignatures :\n\nLe Client :\n______________________________\n\nL\'Entreprise :\n______________________________\n\n[prenom] [nom]'
    },
    {
      id: 'signature_approval',
      label: 'Approbation',
      template: 'Lu et approuvé :\n\n______________________________\n\n[prenom] [nom]\n[date]'
    },
    {
      id: 'signature_witness',
      label: 'Témoin',
      template: 'Témoin :\n\n______________________________\n\nNom et prénom :\n\nAdresse :\n\nDate : ____/____/________'
    },
    {
      id: 'document_title',
      label: 'Titre de document',
      template: 'CONTRAT DE PRESTATION DE SERVICES'
    },
    {
      id: 'document_subtitle',
      label: 'Sous-titre de document',
      template: 'Entre les parties ci-dessous désignées'
    },

    // === FORMULAIRES ===
    {
      id: 'checkbox_terms',
      label: 'Case - Conditions générales',
      template: '☐ J\'ai lu et j\'accepte les conditions générales de vente'
    },
    {
      id: 'checkbox_privacy',
      label: 'Case - Politique de confidentialité',
      template: '☐ J\'accepte la politique de confidentialité'
    },
    {
      id: 'checkbox_newsletter',
      label: 'Case - Newsletter',
      template: '☐ Je souhaite m\'inscrire à la newsletter'
    },
    {
      id: 'checkbox_certification',
      label: 'Case - Certification',
      template: '☐ Je certifie l\'exactitude des informations fournies'
    },

    // === INFORMATIONS ===
    {
      id: 'contact_complete',
      label: 'Contact complet',
      template: '[entreprise]\n[Adresse ligne 1]\n[Code postal] [Ville]\n[telephone] | [email]\n[site]'
    },
    {
      id: 'contact_minimal',
      label: 'Contact minimal',
      template: '[email] | [telephone]'
    },
    {
      id: 'legal_mentions',
      label: 'Mentions légales',
      template: 'SIRET: [SIRET] | TVA: [TVA]\nCapital social: [capital]€'
    },

    // === DATES ===
    {
      id: 'date_today',
      label: 'Date du jour',
      template: 'Fait à [Ville], le [date]'
    },
    {
      id: 'date_contract',
      label: 'Date de contrat',
      template: 'Contrat établi le [date]'
    },

    // === TEXTES ===
    {
      id: 'paragraph_intro',
      label: 'Introduction',
      template: 'Par la présente, il est convenu ce qui suit entre les parties :'
    },
    {
      id: 'paragraph_conclusion',
      label: 'Conclusion',
      template: 'Les parties déclarent avoir lu et approuvé l\'intégralité du présent document.'
    },
    {
      id: 'paragraph_payment',
      label: 'Paiement',
      template: 'Le paiement s\'effectuera selon les modalités suivantes :'
    },

    // === LISTES ===
    {
      id: 'bullet_services',
      label: 'Services (liste)',
      template: '• Conseil et accompagnement\n• Formation personnalisée\n• Support technique'
    },
    {
      id: 'numbered_steps',
      label: 'Étapes (liste)',
      template: '1. Validation du cahier des charges\n2. Réalisation du projet\n3. Livraison et recette'
    },
    {
      id: 'bullet_features',
      label: 'Fonctionnalités',
      template: '• Interface intuitive\n• Performance optimisée\n• Sécurité renforcée'
    },

    // === PROFESSIONNEL AVANCÉ ===
    {
      id: 'confidentiality_clause',
      label: 'Clause de confidentialité',
      template: 'Les parties s\'engagent à garder confidentielles les informations échangées.'
    },
    {
      id: 'jurisdiction_clause',
      label: 'Clause de juridiction',
      template: 'Tout litige sera soumis aux tribunaux de [Ville].'
    },
    {
      id: 'termination_clause',
      label: 'Clause de résiliation',
      template: 'Le présent contrat peut être résilié par l\'une ou l\'autre des parties avec un préavis de 30 jours.'
    },

    {
      id: 'custom',
      label: 'Personnalisé',
      template: ''
    }
  ];

  // Détecter le template actuel
  const currentText = (element as any).text || '';
  const currentTemplate = (element as any).textTemplate || 
    textExamples.find((ex: any) => ex.template === currentText)?.id || 'custom';

  // Fonction pour changer de template
  const handleTemplateChange = (templateId: string) => {
    const selectedExample = textExamples.find((ex: any) => ex.id === templateId);
    if (selectedExample) {
      onChange(element.id, 'text', selectedExample.template);
      onChange(element.id, 'textTemplate', selectedExample.id);
    }
  };

  const dynamicTextThemes = [
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
            height: '2px',
            backgroundColor: '#e5e7eb'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#f3f4f6'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
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
      id: 'highlighted',
      name: 'Surligné',
      preview: (
        <div style={{
          width: '100%',
          height: '35px',
          border: '1px solid #dbeafe',
          borderRadius: '4px',
          backgroundColor: '#eff6ff',
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
          alignItems: 'center',
          gap: '1px'
        }}>
          <div style={{
            width: '90%',
            height: '2px',
            backgroundColor: '#dbeafe'
          }}></div>
          <div style={{
            width: '75%',
            height: '2px',
            backgroundColor: '#eff6ff'
          }}></div>
          <div style={{
            width: '60%',
            height: '2px',
            backgroundColor: '#dbeafe'
          }}></div>
        </div>
      ),
      styles: {
        backgroundColor: '#eff6ff',
        borderColor: '#dbeafe',
        textColor: '#1e40af',
        headerTextColor: '#1e3a8a'
      }
    }
  ];

  const availableVariables = [
    { key: '[date]', label: 'Date actuelle' },
    { key: '[nom]', label: 'Nom' },
    { key: '[prenom]', label: 'Prénom' },
    { key: '[entreprise]', label: 'Nom de l\'entreprise' },
    { key: '[telephone]', label: 'Téléphone' },
    { key: '[email]', label: 'Email' },
    { key: '[site]', label: 'Site web' },
    { key: '[ville]', label: 'Ville' },
    { key: '[siret]', label: 'Numéro SIRET' },
    { key: '[tva]', label: 'Numéro TVA' },
    { key: '[capital]', label: 'Capital social' },
    { key: '[rcs]', label: 'RCS' }
  ];

  return (
    <>
      {/* Système d'onglets pour Dynamic Text */}
      <div style={{ display: 'flex', marginBottom: '12px', borderBottom: '2px solid #ddd', gap: '2px', flexWrap: 'wrap' }}>
        <button
          onClick={() => setDynamicCurrentTab('fonctionnalites')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: dynamicCurrentTab === 'fonctionnalites' ? '#007bff' : '#f0f0f0',
            color: dynamicCurrentTab === 'fonctionnalites' ? '#fff' : '#333',
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
          onClick={() => setDynamicCurrentTab('personnalisation')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: dynamicCurrentTab === 'personnalisation' ? '#007bff' : '#f0f0f0',
            color: dynamicCurrentTab === 'personnalisation' ? '#fff' : '#333',
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
          onClick={() => setDynamicCurrentTab('positionnement')}
          style={{
            flex: '1 1 30%',
            padding: '8px 6px',
            backgroundColor: dynamicCurrentTab === 'positionnement' ? '#007bff' : '#f0f0f0',
            color: dynamicCurrentTab === 'positionnement' ? '#fff' : '#333',
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
      {dynamicCurrentTab === 'fonctionnalites' && (
        <>
          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Modèle de texte
            </label>
            <select
              value={currentTemplate}
              onChange={(e) => handleTemplateChange(e.target.value)}
              style={{
                width: '100%',
                padding: '6px',
                border: '1px solid #ccc',
                borderRadius: '4px',
                fontSize: '12px'
              }}
            >
              {textExamples.map((example: any) => (
                <option key={example.id} value={example.id}>
                  {example.label}
                </option>
              ))}
            </select>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Texte personnalisé
            </label>
            <textarea
              value={(element as any).text || ''}
              onChange={(e) => onChange(element.id, 'text', e.target.value)}
              placeholder="Modifiez le texte ou utilisez les variables disponibles"
              rows={3}
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
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '8px' }}>
              Variables disponibles
            </label>
            <div style={{
              maxHeight: '150px',
              overflowY: 'auto',
              border: '1px solid #e0e0e0',
              borderRadius: '4px',
              padding: '8px',
              backgroundColor: '#fafafa'
            }}>
              {availableVariables.map((variable) => (
                <div
                  key={variable.key}
                  onClick={() => {
                    const currentText = (element as any).text || '';
                    const newText = currentText + variable.key;
                    onChange(element.id, 'text', newText);
                  }}
                  style={{
                    cursor: 'pointer',
                    padding: '4px 8px',
                    marginBottom: '4px',
                    backgroundColor: '#ffffff',
                    border: '1px solid #e0e0e0',
                    borderRadius: '3px',
                    fontSize: '11px',
                    fontFamily: 'monospace'
                  }}
                  title={`Cliquez pour insérer ${variable.key}`}
                >
                  <strong>{variable.key}</strong> - {variable.label}
                </div>
              ))}
            </div>
          </div>

          <div style={{ marginBottom: '12px' }}>
            <label style={{ display: 'block', fontSize: '12px', fontWeight: 'bold', marginBottom: '6px' }}>
              Retour à la ligne automatique
            </label>
            <input
              type="checkbox"
              checked={(element as any).autoWrap !== false}
              onChange={(e) => onChange(element.id, 'autoWrap', e.target.checked)}
              style={{ marginRight: '8px' }}
            />
            <span style={{ fontSize: '11px', color: '#666' }}>Adapte le texte à la largeur</span>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {dynamicCurrentTab === 'personnalisation' && (
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
              {dynamicTextThemes.map((theme) => (
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
              <option value="justify">Justifié</option>
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
              <label style={{ fontSize: '11px', display: 'flex', alignItems: 'center' }}>
                <input
                  type="checkbox"
                  checked={(element as any).textDecoration === 'underline'}
                  onChange={(e) => onChange(element.id, 'textDecoration', e.target.checked ? 'underline' : 'none')}
                  style={{ marginRight: '4px' }}
                />
                Souligné
              </label>
            </div>
          </div>
        </>
      )}

      {/* Onglet Positionnement */}
      {dynamicCurrentTab === 'positionnement' && (
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
              value={(element as any).height || 50}
              onChange={(e) => onChange(element.id, 'height', parseInt(e.target.value) || 50)}
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