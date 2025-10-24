import React from 'react';
import FontControls from '../FontControls';

const renderFontSection = (selectedElement, localProperties, handlePropertyChange) => (
  <FontControls
    key="font"
    elementId={selectedElement.id}
    properties={localProperties}
    onPropertyChange={handlePropertyChange}
  />
);

export default renderFontSection;