import React, { useState } from 'react';
import './TemplateHeader.css';

const TemplateHeader = ({
  templateName,
  isNew,
  onSave,
  onCreateNew,
  onPreview
}) => {
  const [showNewTemplateModal, setShowNewTemplateModal] = useState(false);
  const [showTemplateSettingsModal, setShowTemplateSettingsModal] = useState(false);
  const [newTemplateData, setNewTemplateData] = useState({
    name: '',
    width: 595,
    height: 842,
    orientation: 'portrait'
  });
  const [templateSettings, setTemplateSettings] = useState({
    name: templateName || '',
    description: '',
    category: 'autre'
  });
  const [isSaving, setIsSaving] = useState(false);
  const [saveMessage, setSaveMessage] = useState('');

  const handleSave = async () => {
    if (onSave) {
      setIsSaving(true);
      setSaveMessage('');

      try {
        await onSave();
        setSaveMessage('Template sauvegardé avec succès !');
        setTimeout(() => setSaveMessage(''), 3000); // Masquer le message après 3 secondes
      } catch (error) {
        setSaveMessage('Erreur lors de la sauvegarde');
        setTimeout(() => setSaveMessage(''), 3000);
      } finally {
        setIsSaving(false);
      }
    }
  };

  const handleCreateNew = () => {
    if (onCreateNew) {
      onCreateNew(newTemplateData);
    }
    setShowNewTemplateModal(false);
    // Reset form
    setNewTemplateData({
      name: '',
      width: 595,
      height: 842,
      orientation: 'portrait'
    });
  };

  const handleOpenTemplateSettings = () => {
    // Initialiser avec les valeurs actuelles
    setTemplateSettings({
      name: templateName || '',
      description: '',
      category: 'autre'
    });
    setShowTemplateSettingsModal(true);
  };

  const handleSaveTemplateSettings = () => {
    // TODO: Sauvegarder les paramètres du template
    console.log('Sauvegarde des paramètres:', templateSettings);
    setShowTemplateSettingsModal(false);
  };

  return (
    <>
      <div className="template-header">
        <div className="header-left">
          <button
            className="header-btn new-template-btn"
            onClick={() => setShowNewTemplateModal(true)}
            title="Créer un nouveau template"
          >
            <span className="btn-icon">📄</span>
            <span className="btn-text">Nouveau template</span>
          </button>
        </div>

        <div className="header-center">
          <h2 className="template-title">
            {isNew ? 'Nouveau template' : `Modifier: ${templateName || 'Template sans nom'}`}
          </h2>
          {saveMessage && (
            <div className={`save-message ${saveMessage.includes('succès') ? 'success' : 'error'}`}>
              {saveMessage}
            </div>
          )}
        </div>

        <div className="header-right">
          <button
            className="header-btn settings-btn"
            onClick={handleOpenTemplateSettings}
            title="Paramètres du template"
          >
            <span className="btn-icon">⚙️</span>
            <span className="btn-text">Paramètres</span>
          </button>

          <button
            className={`header-btn save-btn ${isSaving ? 'saving' : ''}`}
            onClick={handleSave}
            disabled={isSaving}
            title={isNew ? 'Sauvegarder le template' : 'Modifier le template'}
          >
            <span className="btn-icon">
              {isSaving ? '⏳' : (isNew ? '💾' : '✏️')}
            </span>
            <span className="btn-text">
              {isSaving ? 'Sauvegarde...' : (isNew ? 'Sauvegarder' : 'Modifier')}
            </span>
          </button>

          <button
            className="header-btn preview-btn"
            onClick={onPreview}
            title="Aperçu du PDF"
          >
            <span className="btn-icon">👁️</span>
            <span className="btn-text">Aperçu</span>
          </button>
        </div>
      </div>

      {/* Modal Nouveau Template */}
      {showNewTemplateModal && (
        <div className="modal-overlay" onClick={() => setShowNewTemplateModal(false)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3>Créer un nouveau template</h3>
              <button
                className="modal-close"
                onClick={() => setShowNewTemplateModal(false)}
              >
                ×
              </button>
            </div>

            <div className="modal-body">
              <div className="form-group">
                <label htmlFor="template-name">Nom du template</label>
                <input
                  id="template-name"
                  type="text"
                  value={newTemplateData.name}
                  onChange={(e) => setNewTemplateData({...newTemplateData, name: e.target.value})}
                  placeholder="Entrez le nom du template"
                />
              </div>

              <div className="form-group">
                <label htmlFor="template-width">Largeur (px)</label>
                <input
                  id="template-width"
                  type="number"
                  value={newTemplateData.width}
                  onChange={(e) => setNewTemplateData({...newTemplateData, width: parseInt(e.target.value)})}
                  min="100"
                  max="2000"
                />
              </div>

              <div className="form-group">
                <label htmlFor="template-height">Hauteur (px)</label>
                <input
                  id="template-height"
                  type="number"
                  value={newTemplateData.height}
                  onChange={(e) => setNewTemplateData({...newTemplateData, height: parseInt(e.target.value)})}
                  min="100"
                  max="3000"
                />
              </div>

              <div className="form-group">
                <label htmlFor="template-orientation">Orientation</label>
                <select
                  id="template-orientation"
                  value={newTemplateData.orientation}
                  onChange={(e) => setNewTemplateData({...newTemplateData, orientation: e.target.value})}
                >
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Paysage</option>
                </select>
              </div>
            </div>

            <div className="modal-footer">
              <button
                className="btn-secondary"
                onClick={() => setShowNewTemplateModal(false)}
              >
                Annuler
              </button>
              <button
                className="btn-primary"
                onClick={handleCreateNew}
                disabled={!newTemplateData.name.trim()}
              >
                Créer le template
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal Paramètres du Template */}
      {showTemplateSettingsModal && (
        <div className="modal-overlay" onClick={() => setShowTemplateSettingsModal(false)}>
          <div
            className="modal-content template-settings-modal"
            onClick={(e) => e.stopPropagation()}
            style={{
              maxWidth: '520px',
              animation: 'modalSlideIn 0.3s ease-out',
              background: 'white',
              borderRadius: '12px',
              boxShadow: '0 20px 60px rgba(0, 0, 0, 0.3)',
              border: 'none'
            }}
          >
            <div
              className="modal-header"
              style={{
                background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                color: 'white',
                borderBottom: 'none',
                borderRadius: '12px 12px 0 0',
                padding: '20px 24px'
              }}
            >
              <h3 style={{ color: 'white', fontSize: '20px', margin: '0' }}>Paramètres du template</h3>
              <button
                className="modal-close"
                onClick={() => setShowTemplateSettingsModal(false)}
                style={{
                  color: 'rgba(255, 255, 255, 0.8)',
                  background: 'rgba(255, 255, 255, 0.1)',
                  border: 'none',
                  borderRadius: '6px',
                  width: '32px',
                  height: '32px',
                  cursor: 'pointer',
                  fontSize: '18px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  transition: 'all 0.2s ease'
                }}
                onMouseEnter={(e) => {
                  e.target.style.color = 'white';
                  e.target.style.background = 'rgba(255, 255, 255, 0.2)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.color = 'rgba(255, 255, 255, 0.8)';
                  e.target.style.background = 'rgba(255, 255, 255, 0.1)';
                }}
              >
                ×
              </button>
            </div>

            <div
              className="modal-body"
              style={{
                padding: '24px',
                background: '#f8fafc'
              }}
            >
              <div
                className="form-group"
                style={{
                  marginBottom: '20px',
                  background: 'white',
                  padding: '16px',
                  borderRadius: '8px',
                  border: '1px solid #e2e8f0',
                  transition: 'all 0.2s ease'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = '#cbd5e0';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.06)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = '#e2e8f0';
                  e.target.style.boxShadow = 'none';
                }}
              >
                <label
                  htmlFor="settings-template-name"
                  style={{
                    color: '#2d3748',
                    fontWeight: '600',
                    marginBottom: '8px',
                    fontSize: '15px',
                    display: 'block'
                  }}
                >
                  Nom du template
                </label>
                <input
                  id="settings-template-name"
                  type="text"
                  value={templateSettings.name}
                  onChange={(e) => setTemplateSettings({...templateSettings, name: e.target.value})}
                  placeholder="Entrez le nom du template"
                  style={{
                    border: '2px solid #e2e8f0',
                    borderRadius: '6px',
                    padding: '10px 12px',
                    fontSize: '14px',
                    transition: 'all 0.2s ease',
                    width: '100%',
                    boxSizing: 'border-box'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#667eea';
                    e.target.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.15)';
                    e.target.style.transform = 'translateY(-1px)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e2e8f0';
                    e.target.style.boxShadow = 'none';
                    e.target.style.transform = 'none';
                  }}
                />
              </div>

              <div
                className="form-group"
                style={{
                  marginBottom: '20px',
                  background: 'white',
                  padding: '16px',
                  borderRadius: '8px',
                  border: '1px solid #e2e8f0',
                  transition: 'all 0.2s ease'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = '#cbd5e0';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.06)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = '#e2e8f0';
                  e.target.style.boxShadow = 'none';
                }}
              >
                <label
                  htmlFor="settings-template-description"
                  style={{
                    color: '#2d3748',
                    fontWeight: '600',
                    marginBottom: '8px',
                    fontSize: '15px',
                    display: 'block'
                  }}
                >
                  Description
                </label>
                <textarea
                  id="settings-template-description"
                  value={templateSettings.description}
                  onChange={(e) => setTemplateSettings({...templateSettings, description: e.target.value})}
                  placeholder="Entrez une description du template"
                  rows="3"
                  style={{
                    border: '2px solid #e2e8f0',
                    borderRadius: '6px',
                    padding: '10px 12px',
                    fontSize: '14px',
                    transition: 'all 0.2s ease',
                    width: '100%',
                    boxSizing: 'border-box',
                    resize: 'vertical'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#667eea';
                    e.target.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.15)';
                    e.target.style.transform = 'translateY(-1px)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e2e8f0';
                    e.target.style.boxShadow = 'none';
                    e.target.style.transform = 'none';
                  }}
                />
              </div>

              <div
                className="form-group"
                style={{
                  marginBottom: '20px',
                  background: 'white',
                  padding: '16px',
                  borderRadius: '8px',
                  border: '1px solid #e2e8f0',
                  transition: 'all 0.2s ease'
                }}
                onMouseEnter={(e) => {
                  e.target.style.borderColor = '#cbd5e0';
                  e.target.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.06)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.borderColor = '#e2e8f0';
                  e.target.style.boxShadow = 'none';
                }}
              >
                <label
                  htmlFor="settings-template-category"
                  style={{
                    color: '#2d3748',
                    fontWeight: '600',
                    marginBottom: '8px',
                    fontSize: '15px',
                    display: 'block'
                  }}
                >
                  Catégorie
                </label>
                <select
                  id="settings-template-category"
                  value={templateSettings.category}
                  onChange={(e) => setTemplateSettings({...templateSettings, category: e.target.value})}
                  style={{
                    border: '2px solid #e2e8f0',
                    borderRadius: '6px',
                    padding: '10px 12px',
                    fontSize: '14px',
                    transition: 'all 0.2s ease',
                    width: '100%',
                    boxSizing: 'border-box',
                    background: 'white'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#667eea';
                    e.target.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.15)';
                    e.target.style.transform = 'translateY(-1px)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e2e8f0';
                    e.target.style.boxShadow = 'none';
                    e.target.style.transform = 'none';
                  }}
                >
                  <option value="facture">Facture</option>
                  <option value="devis">Devis</option>
                  <option value="commande">Bon de commande</option>
                  <option value="contrat">Contrat</option>
                  <option value="newsletter">Newsletter</option>
                  <option value="autre">Autre</option>
                </select>
              </div>
            </div>

            <div
              className="modal-footer"
              style={{
                padding: '20px 24px',
                background: 'white',
                borderTop: '1px solid #e2e8f0',
                borderRadius: '0 0 12px 12px',
                display: 'flex',
                justifyContent: 'flex-end',
                gap: '12px'
              }}
            >
              <button
                className="btn-secondary"
                onClick={() => setShowTemplateSettingsModal(false)}
                style={{
                  border: '2px solid #e2e8f0',
                  background: 'white',
                  color: '#4a5568',
                  padding: '10px 20px',
                  fontWeight: '500',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  transition: 'all 0.2s ease',
                  fontSize: '14px'
                }}
                onMouseEnter={(e) => {
                  e.target.style.background = '#f7fafc';
                  e.target.style.borderColor = '#cbd5e0';
                  e.target.style.color = '#2d3748';
                }}
                onMouseLeave={(e) => {
                  e.target.style.background = 'white';
                  e.target.style.borderColor = '#e2e8f0';
                  e.target.style.color = '#4a5568';
                }}
              >
                Annuler
              </button>
              <button
                className="btn-primary"
                onClick={handleSaveTemplateSettings}
                style={{
                  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                  border: 'none',
                  color: 'white',
                  padding: '10px 20px',
                  fontWeight: '600',
                  borderRadius: '6px',
                  cursor: 'pointer',
                  transition: 'all 0.2s ease',
                  fontSize: '14px',
                  letterSpacing: '0.5px'
                }}
                onMouseEnter={(e) => {
                  e.target.style.background = 'linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%)';
                  e.target.style.transform = 'translateY(-2px)';
                  e.target.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.4)';
                }}
                onMouseLeave={(e) => {
                  e.target.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                  e.target.style.transform = 'none';
                  e.target.style.boxShadow = 'none';
                }}
              >
                Enregistrer
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default TemplateHeader;