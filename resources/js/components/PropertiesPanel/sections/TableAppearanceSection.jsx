import React from 'react';
import Accordion from '../Accordion';
import ColorPicker from '../ColorPicker';
import TableStylePreview from '../TableStylePreview';

const renderTableAppearanceSection = (selectedElement, localProperties, handlePropertyChange, activeTab) => {
  return (
    <Accordion title="🎨 Apparence du tableau" defaultExpanded={true}>
      <div className="section-content">
        <TableStylePreview
          selectedStyle={localProperties.tableStyle || 'default'}
          onStyleSelect={(style) => handlePropertyChange(selectedElement.id, 'tableStyle', style)}
        />

        {/* Couleurs des lignes alternées */}
        <ColorPicker
          label="Fond lignes paires"
          value={localProperties.evenRowBg || '#ffffff'}
          onChange={(value) => handlePropertyChange(selectedElement.id, 'evenRowBg', value)}
          presets={['#ffffff', '#f8fafc', '#f1f5f9', '#e2e8f0']}
        />

        <ColorPicker
          label="Texte lignes paires"
          value={localProperties.evenRowTextColor || '#000000'}
          onChange={(value) => handlePropertyChange(selectedElement.id, 'evenRowTextColor', value)}
          presets={['#000000', '#1e293b', '#334155', '#475569']}
        />

        <ColorPicker
          label="Fond lignes impaires"
          value={localProperties.oddRowBg || '#f8fafc'}
          onChange={(value) => handlePropertyChange(selectedElement.id, 'oddRowBg', value)}
          presets={['#f8fafc', '#f1f5f9', '#e2e8f0', '#ffffff']}
        />

        <ColorPicker
          label="Texte lignes impaires"
          value={localProperties.oddRowTextColor || '#000000'}
          onChange={(value) => handlePropertyChange(selectedElement.id, 'oddRowTextColor', value)}
          presets={['#000000', '#1e293b', '#334155', '#475569']}
        />
      </div>
    </Accordion>
  );
};

export default renderTableAppearanceSection;