// Constantes centralisées pour l'application PDF Builder

// Couleurs principales
export const COLORS = {
  PRIMARY: '#2563eb',
  PRIMARY_DARK: '#1d4ed8',
  PRIMARY_LIGHT: '#3b82f6',
  WHITE: '#ffffff',
  BLACK: '#000000',
  GRAY_LIGHT: '#f8fafc',
  GRAY_MEDIUM: '#e2e8f0',
  GRAY_DARK: '#64748b',
  SUCCESS: '#10b981',
  WARNING: '#f59e0b',
  ERROR: '#ef4444',
  TRANSPARENT: 'transparent'
};

// Valeurs par défaut pour les éléments
export const DEFAULT_ELEMENT_PROPERTIES = {
  fontSize: 12,
  fontFamily: 'Arial',
  color: '#000000',
  backgroundColor: 'transparent',
  borderWidth: 0,
  borderColor: '#000000',
  borderRadius: 0,
  opacity: 1,
  x: 0,
  y: 0,
  width: 200,
  height: 100,
  rotation: 0,
  scaleX: 1,
  scaleY: 1,
  zIndex: 1
};

// Liste des polices disponibles
export const FONT_FAMILIES = [
  { value: 'Arial', label: 'Arial' },
  { value: 'Helvetica', label: 'Helvetica' },
  { value: 'Times New Roman', label: 'Times New Roman' },
  { value: 'Courier New', label: 'Courier New' },
  { value: 'Georgia', label: 'Georgia' },
  { value: 'Verdana', label: 'Verdana' },
  { value: 'Trebuchet MS', label: 'Trebuchet MS' },
  { value: 'Comic Sans MS', label: 'Comic Sans MS' },
  { value: 'Impact', label: 'Impact' },
  { value: 'Lucida Console', label: 'Lucida Console' }
];

// Plages de valeurs pour les contrôles
export const CONTROL_RANGES = {
  fontSize: { min: 8, max: 72, step: 1, unit: 'px' },
  borderWidth: { min: 0, max: 10, step: 1, unit: 'px' },
  borderRadius: { min: 0, max: 50, step: 1, unit: 'px' },
  opacity: { min: 0, max: 1, step: 0.1, unit: '%' },
  rotation: { min: -180, max: 180, step: 1, unit: '°' },
  scale: { min: 0.1, max: 2, step: 0.1, unit: '' },
  zIndex: { min: 0, max: 100, step: 1, unit: '' },
  position: { min: 0, max: 1000, step: 1, unit: 'px' },
  dimensions: { min: 10, max: 800, step: 1, unit: 'px' },
  lineHeight: { min: 0.8, max: 3, step: 0.1, unit: '' },
  letterSpacing: { min: -2, max: 10, step: 0.5, unit: 'px' }
};