// Types spécifiques à PDF Builder Pro

// Types pour les templates PDF
export interface PDFTemplate {
  id: string;
  name: string;
  description: string;
  thumbnail?: string;
  category: string;
  isDefault: boolean;
  settings: PDFTemplateSettings;
}

export interface PDFTemplateSettings {
  pageSize: 'A4' | 'A3' | 'Letter' | 'Legal';
  orientation: 'portrait' | 'landscape';
  margins: {
    top: number;
    right: number;
    bottom: number;
    left: number;
  };
  fonts: PDFFont[];
  colors: PDFColorScheme;
}

// Types pour les polices PDF
export interface PDFFont {
  name: string;
  family: string;
  weight: 'normal' | 'bold' | 'italic' | 'bolditalic';
  size: number;
}

// Types pour les couleurs PDF
export interface PDFColorScheme {
  primary: string;
  secondary: string;
  accent: string;
  text: string;
  background: string;
}

// Types pour les éléments PDF
export interface PDFElement {
  id: string;
  type: 'text' | 'image' | 'shape' | 'table' | 'chart';
  position: {
    x: number;
    y: number;
    width: number;
    height: number;
  };
  properties: Record<string, any>;
  zIndex: number;
}

// Types pour les paramètres du builder
export interface PDFBuilderSettings {
  autoSave: boolean;
  autoSaveInterval: number;
  previewQuality: 'low' | 'medium' | 'high';
  gridSize: number;
  snapToGrid: boolean;
  showRulers: boolean;
  theme: 'light' | 'dark';
}

// Types pour les actions du builder
export interface PDFBuilderAction {
  type: string;
  payload?: any;
  meta?: {
    timestamp: number;
    userId?: number;
  };
}

// Types pour les états du builder
export interface PDFBuilderState {
  currentTemplate: PDFTemplate | null;
  elements: PDFElement[];
  selectedElement: string | null;
  settings: PDFBuilderSettings;
  isLoading: boolean;
  hasUnsavedChanges: boolean;
  history: {
    past: PDFBuilderState[];
    present: PDFBuilderState;
    future: PDFBuilderState[];
  };
}

// Types pour les erreurs
export interface PDFBuilderError {
  code: string;
  message: string;
  details?: any;
  timestamp: number;
}

// Types pour les événements
export interface PDFBuilderEvent {
  type: string;
  data?: any;
  timestamp: number;
}