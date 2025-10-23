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
  const [newTemplateData, setNewTemplateData] = useState({
    name: '',
    width: 595,
    height: 842,
    orientation: 'portrait'
  });

  const handleSave = () => {
    if (onSave) {
      onSave();
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
        </div>

        <div className="header-right">
          <button
            className="header-btn save-btn"
            onClick={handleSave}
            title={isNew ? 'Sauvegarder le template' : 'Modifier le template'}
          >
            <span className="btn-icon">{isNew ? '💾' : '✏️'}</span>
            <span className="btn-text">{isNew ? 'Sauvegarder' : 'Modifier'}</span>
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
    </>
  );
};

export default TemplateHeader;