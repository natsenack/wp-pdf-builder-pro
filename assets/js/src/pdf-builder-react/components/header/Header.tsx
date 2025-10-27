import React from 'react';
import './Header.css';

interface HeaderProps {
  templateName?: string;
  isNewTemplate: boolean;
  isModified: boolean;
  onSave: () => void;
  onPreview: () => void;
  onNewTemplate: () => void;
  isSaving?: boolean;
}

export function Header({
  templateName,
  isNewTemplate,
  isModified,
  onSave,
  onPreview,
  onNewTemplate,
  isSaving = false
}: HeaderProps) {
  const displayName = isNewTemplate ? 'Nouveau template' : (templateName || 'Template sans nom');

  return (
    <div className="pdf-builder-header">
      <div className="header-left">
        <h2 className="template-title">
          {displayName}
          {isModified && <span className="modified-indicator"> *</span>}
        </h2>
        <span className="template-status">
          {isNewTemplate ? '(Non sauvegardé)' : '(Modifié)'}
        </span>
      </div>

      <div className="header-right">
        <button
          className="header-button header-button-secondary"
          onClick={onPreview}
          title="Aperçu du PDF"
        >
          <span className="dashicons dashicons-visibility"></span>
          Aperçu
        </button>

        <button
          className="header-button header-button-primary"
          onClick={onSave}
          disabled={isSaving}
          title={isNewTemplate ? 'Sauvegarder le nouveau template' : 'Sauvegarder les modifications'}
        >
          <span className="dashicons dashicons-saved"></span>
          {isSaving ? 'Sauvegarde...' : (isNewTemplate ? 'Sauvegarder' : 'Modifier')}
        </button>

        <button
          className="header-button header-button-secondary"
          onClick={onNewTemplate}
          title="Créer un nouveau template"
        >
          <span className="dashicons dashicons-plus"></span>
          Nouveau template
        </button>
      </div>
    </div>
  );
}