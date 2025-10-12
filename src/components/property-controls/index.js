// Point d'entrée pour tous les contrôles de propriétés
// Enregistre automatiquement tous les contrôles dans le registre

import { registerControl } from './ControlRegistry';

// Importer tous les contrôles
import TextControls from './TextControls';
import ImageControls from './ImageControls';
import TableControls from './TableControls';
import CustomerControls from './CustomerControls';
import DocumentTypeControls from './DocumentTypeControls';

// Enregistrer les contrôles dans le registre
registerControl('text', TextControls);
registerControl('variables', TextControls); // Variables dynamiques utilisent aussi TextControls
registerControl('image', ImageControls);
registerControl('table', TableControls);
registerControl('customer_fields', CustomerControls);
registerControl('document_type', DocumentTypeControls);

// Exporter tous les contrôles pour utilisation directe si nécessaire
export {
  TextControls,
  ImageControls,
  TableControls,
  CustomerControls,
  DocumentTypeControls
};

// Exporter le registre
export { registerControl, getControlComponent, hasControl, getAllControls, unregisterControl } from './ControlRegistry';