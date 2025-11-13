// Types simplifiés pour éviter les erreurs TypeScript complexes
export interface Point {
  x: number;
  y: number;
}

export interface Size {
  width: number;
  height: number;
}

export interface Bounds extends Point, Size {}

export interface TemplateState {
  id?: string;
  name?: string;
  description?: string;
  tags?: string[];
  canvasWidth?: number;
  canvasHeight?: number;
  marginTop?: number;
  marginBottom?: number;
  showGuides?: boolean;
  snapToGrid?: boolean;
  isNew: boolean;
  isModified: boolean;
  isSaving: boolean;
  isLoading: boolean; // ✅ NEW: Template is loading from AJAX
  lastSaved?: Date;
}

export interface BaseElement {
  id: string;
  type: string;
  x: number;
  y: number;
  width: number;
  height: number;
  rotation?: number;
  opacity?: number;
  visible: boolean;
  locked: boolean;
  createdAt: Date;
  updatedAt: Date;
  // Propriétés communes de style
  fillColor?: string;
  strokeColor?: string;
  strokeWidth?: number;
  borderRadius?: number;
  text?: string;
  fontSize?: string | number;
  color?: string;
  textAlign?: string;
  fontFamily?: string;
  bold?: boolean;
  italic?: boolean;
  underline?: boolean;
  src?: string;
  objectFit?: string;
  [key: string]: unknown; // Propriétés dynamiques
}

export type Element = BaseElement;

export interface OrderNumberElement extends BaseElement {
  type: 'order-number';
  showLabel?: boolean;
  labelText?: string;
  labelPosition?: string;
  showDate?: boolean;
  contentAlign?: string;
  fontSize?: string | number;
  fontFamily?: string;
  fontWeight?: string;
}

export interface DynamicTextElement extends BaseElement {
  type: 'dynamic-text';
  textTemplate?: string;
  autoWrap?: boolean;
  theme?: string;
  textDecoration?: string;
  fontWeight?: string;
  fontStyle?: string;
}

export interface ProductTableElement extends BaseElement {
  type: 'product-table';
  showHeaders?: boolean;
  showBorders?: boolean;
  showAlternatingRows?: boolean;
  showSku?: boolean;
  showDescription?: boolean;
  showQuantity?: boolean;
  showShipping?: boolean;
  showTax?: boolean;
  showGlobalDiscount?: boolean;
  backgroundColor?: string;
  headerBackgroundColor?: string;
  alternateRowColor?: string;
  borderColor?: string;
  textColor?: string;
  verticalAlign?: string;
  // Propriétés de police globales
  globalFontSize?: number;
  globalFontFamily?: string;
  globalFontWeight?: string;
  globalFontStyle?: string;
  // Propriétés de police spécifiques à l'entête
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  headerTextColor?: string;
  // Propriétés de police spécifiques aux lignes
  rowFontSize?: number;
  rowFontFamily?: string;
  rowFontWeight?: string;
  rowFontStyle?: string;
  rowTextColor?: string;
  // Propriétés de police spécifiques aux totaux
  totalFontSize?: number;
  totalFontFamily?: string;
  totalFontWeight?: string;
  totalFontStyle?: string;
  totalTextColor?: string;
}

export interface MentionsElement extends BaseElement {
  type: 'mentions';
  mentionType?: string;
  selectedMentions?: string[];
  medleySeparator?: string;
  separatorStyle?: string;
  theme?: string;
}

export interface ElementProperties {
  [key: string]: unknown;
}

export interface CanvasState {
  zoom: number;
  pan: Point;
  showGrid: boolean;
  gridSize: number;
  snapToGrid: boolean;
  backgroundColor: string;
}

export interface SelectionState {
  selectedElements: string[];
  selectionBounds?: Bounds;
  isSelecting: boolean;
  selectionStart?: Point;
  selectionEnd?: Point;
}

export interface DragState {
  isDragging: boolean;
  dragStart?: Point;
  dragOffset?: Point;
  draggedElements: string[];
}

export interface BuilderState {
  elements: Element[];
  canvas: CanvasState;
  selection: SelectionState;
  drag: DragState;
  mode: BuilderMode;
  history: HistoryState;
  // Propriétés du template
  template: TemplateState;
  // Mode de prévisualisation (éditeur vs commande)
  previewMode: 'editor' | 'command';
  orderId?: string;
}

export type BuilderMode =
  | 'select'
  | 'rectangle'
  | 'circle'
  | 'text'
  | 'image'
  | 'line'
  | 'pan'
  | 'zoom';

export interface HistoryState {
  past: BuilderState[];
  present: BuilderState;
  future: BuilderState[];
  canUndo: boolean;
  canRedo: boolean;
}

export interface BuilderConfig {
  containerId: string;
  initialState?: Partial<BuilderState>;
  enableHistory?: boolean;
  maxHistorySize?: number;
  enableKeyboard?: boolean;
  enableContextMenu?: boolean;
  theme?: 'light' | 'dark';
}

// Événements
export interface BuilderEvents {
  onElementAdded: (element: Element) => void;
  onElementUpdated: (element: Element) => void;
  onElementRemoved: (elementId: string) => void;
  onSelectionChanged: (selectedIds: string[]) => void;
  onCanvasChanged: (canvas: CanvasState) => void;
  onModeChanged: (mode: BuilderMode) => void;
  onHistoryChanged: (history: HistoryState) => void;
}

// Actions pour le reducer
export type BuilderAction =
  | { type: 'ADD_ELEMENT'; payload: Element }
  | { type: 'UPDATE_ELEMENT'; payload: { id: string; updates: Partial<Element> } }
  | { type: 'REMOVE_ELEMENT'; payload: string }
  | { type: 'SET_ELEMENTS'; payload: Element[] }
  | { type: 'SET_SELECTION'; payload: string[] }
  | { type: 'CLEAR_SELECTION' }
  | { type: 'SET_CANVAS'; payload: Partial<CanvasState> }
  | { type: 'SET_MODE'; payload: BuilderMode }
  | { type: 'SET_DRAG_STATE'; payload: Partial<DragState> }
  | { type: 'SET_PREVIEW_MODE'; payload: 'editor' | 'command' }
  | { type: 'SET_SHOW_PREVIEW_MODAL'; payload: boolean }
  | { type: 'SET_ORDER_ID'; payload: string | undefined }
  | { type: 'UNDO' }
  | { type: 'REDO' }
  | { type: 'RESET' }
  | { type: 'SAVE_TEMPLATE'; payload?: { id?: string; name?: string; lastSaved?: Date } }
  | { type: 'SET_TEMPLATE_MODIFIED'; payload: boolean }
  | { type: 'SET_TEMPLATE_SAVING'; payload: boolean }
  | { type: 'UPDATE_TEMPLATE_SETTINGS'; payload: Partial<TemplateState> }
  | { type: 'LOAD_TEMPLATE'; payload: { id?: string; name?: string; elements?: Element[]; lastSaved?: Date } }
  | { type: 'NEW_TEMPLATE' };

// Props des composants
export interface CanvasProps {
  width: number;
  height: number;
  state: BuilderState;
  onStateChange: (action: BuilderAction) => void;
  className?: string;
}

export interface ToolbarProps {
  mode: BuilderMode;
  onModeChange: (mode: BuilderMode) => void;
  onAction: (action: string) => void;
  disabled?: boolean;
}

export interface PropertiesPanelProps {
  selectedElements: Element[];
  onUpdateElement: (id: string, updates: Partial<Element>) => void;
  onDeleteElements: (ids: string[]) => void;
}

export interface LoadTemplatePayload {
  id?: string;
  name?: string;
  elements?: BaseElement[];
  canvas?: Partial<CanvasState>;
  lastSaved?: Date;
}

// Propriétés spécifiques pour les éléments
export interface ShapeElementProperties {
  fillColor?: string;
  strokeColor?: string;
  strokeWidth?: number;
  borderRadius?: number;
}

export interface TextElementProperties {
  text?: string;
  fontSize?: number;
  color?: string;
  align?: 'left' | 'center' | 'right';
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  autoWrap?: boolean;
  backgroundColor?: string;
}

export interface LineElementProperties {
  strokeColor?: string;
  strokeWidth?: number;
}

export interface ProductTableElementProperties {
  showHeaders?: boolean;
  showBorders?: boolean;
  showAlternatingRows?: boolean;
  showSku?: boolean;
  showDescription?: boolean;
  showQuantity?: boolean;
  showShipping?: boolean;
  showTax?: boolean;
  showGlobalDiscount?: boolean;
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  headerBackgroundColor?: string;
  headerTextColor?: string;
  bodyBackgroundColor?: string;
  bodyTextColor?: string;
  alternateRowColor?: string;
  borderColor?: string;
  borderWidth?: number;
  padding?: number;
  theme?: string;
  textColor?: string;
  borderRadius?: number;
  shippingCost?: number;
  taxRate?: number;
  globalDiscount?: number;
  orderFees?: number;
  backgroundColor?: string;
  verticalAlign?: 'top' | 'middle' | 'bottom';
}

export interface DocumentTypeElementProperties {
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  textColor?: string;
  backgroundColor?: string;
  documentType?: string;
}

export interface CustomerInfoElementProperties {
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  textColor?: string;
  backgroundColor?: string;
  showName?: boolean;
  showEmail?: boolean;
  showPhone?: boolean;
  showAddress?: boolean;
  showCompany?: boolean;
  showPaymentMethod?: boolean;
  showTransactionId?: boolean;
  labelColor?: string;
  valueColor?: string;
  separator?: string;
  // Propriétés de police pour l'en-tête
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  // Propriétés de police pour le corps du texte
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
  // Propriétés de mise en page
  layout?: 'vertical' | 'horizontal';
  showHeaders?: boolean;
  showBorders?: boolean;
  showFullName?: boolean;
  // Propriétés de style
  borderColor?: string;
  headerTextColor?: string;
}

export interface CompanyInfoElementProperties {
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  textColor?: string;
  backgroundColor?: string;
  showName?: boolean;
  showAddress?: boolean;
  showPhone?: boolean;
  showEmail?: boolean;
  showWebsite?: boolean;
  showTaxId?: boolean;
  logoUrl?: string;
  logoWidth?: number;
  logoHeight?: number;
  separator?: string;
  // Propriétés de police pour l'en-tête (nom de l'entreprise)
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  // Propriétés de police pour le corps du texte
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
  // Propriétés de thème et style
  theme?: string;
  showHeaders?: boolean;
  showBackground?: boolean;
  showBorders?: boolean;
  showCompanyName?: boolean;
  showSiret?: boolean;
  showVat?: boolean;
  showRcs?: boolean;
  showCapital?: boolean;
  borderColor?: string;
  borderWidth?: number;
  headerTextColor?: string;
  // Propriétés de données d'entreprise
  companyName?: string;
  companyAddress?: string;
  companyCity?: string;
  companySiret?: string;
  companyTva?: string;
  companyRcs?: string;
  companyCapital?: string;
  companyEmail?: string;
  companyPhone?: string;
}

export interface ImageElementProperties {
  src?: string;
  logoUrl?: string;
  alt?: string;
  fit?: 'contain' | 'cover' | 'fill' | 'none' | 'scale-down';
  position?: 'center' | 'top' | 'bottom' | 'left' | 'right' | 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';
  opacity?: number;
  borderRadius?: number;
  borderWidth?: number;
  borderColor?: string;
  backgroundColor?: string;
  alignment?: 'left' | 'center' | 'right';
  maintainAspectRatio?: boolean;
}

export interface OrderNumberElementProperties {
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  textColor?: string;
  backgroundColor?: string;
  prefix?: string;
  suffix?: string;
  padding?: number;
  borderRadius?: number;
  borderWidth?: number;
  borderColor?: string;
  showOrderDate?: boolean;
  dateFormat?: string;
  // Propriétés de police pour le label
  labelFontSize?: number;
  labelFontFamily?: string;
  labelFontWeight?: string;
  labelFontStyle?: string;
  // Propriétés de police pour le numéro
  numberFontSize?: number;
  numberFontFamily?: string;
  numberFontWeight?: string;
  numberFontStyle?: string;
  // Propriétés de police pour la date
  dateFontSize?: number;
  dateFontFamily?: string;
  dateFontWeight?: string;
  dateFontStyle?: string;
  // Propriétés d'affichage et de mise en page
  contentAlign?: 'left' | 'center' | 'right';
  showLabel?: boolean;
  showDate?: boolean;
  labelPosition?: 'above' | 'left' | 'right' | 'below';
  labelText?: string;
}

export interface MentionsElementProperties {
  fontSize?: number | string;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  textColor?: string;
  backgroundColor?: string;
  content?: string;
  autoWrap?: boolean;
  lineHeight?: number;
  margin?: number;
  padding?: number;
  text?: string;
  showSeparator?: boolean;
  separatorStyle?: string;
  theme?: string;
}

// Interface spécifique pour les éléments CompanyInfo
export interface CompanyInfoElement extends BaseElement {
  type: 'company-info';
  // Propriétés d'affichage
  showHeaders?: boolean;
  showBorders?: boolean;
  showCompanyName?: boolean;
  showAddress?: boolean;
  showPhone?: boolean;
  showEmail?: boolean;
  showSiret?: boolean;
  showVat?: boolean;
  showRcs?: boolean;
  showCapital?: boolean;
  // Propriétés de thème
  theme?: string;
  // Propriétés de police pour les en-têtes
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  // Propriétés de police pour le corps
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
  // Propriétés de police générales (fallback)
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  // Propriétés de texte
  textAlign?: 'left' | 'center' | 'right';
  backgroundColor?: string;
  borderColor?: string;
  textColor?: string;
}

// Interface spécifique pour les éléments CompanyLogo
export interface CompanyLogoElement extends BaseElement {
  type: 'company-logo';
  // Propriétés d'image
  src?: string;
  altText?: string;
  // Propriétés d'affichage
  maintainAspectRatio?: boolean;
  showBorder?: boolean;
  objectFit?: string;
  opacity?: number;
  borderRadius?: number;
  rotation?: number;
}

// Interface spécifique pour les éléments CustomerInfo
export interface CustomerInfoElement extends BaseElement {
  type: 'customer-info';
  // Propriétés d'affichage
  showHeaders?: boolean;
  showBackground?: boolean;
  showBorders?: boolean;
  showFullName?: boolean;
  showAddress?: boolean;
  showEmail?: boolean;
  showPhone?: boolean;
  showCompanyName?: boolean;
  showVatNumber?: boolean;
  showCompanyAddress?: boolean;
  // Propriétés de mise en page
  layout?: string;
  // Propriétés de police pour les en-têtes
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  // Propriétés de police pour le corps
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
  // Propriétés de police générales (fallback)
  fontSize?: number;
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  // Propriétés de couleur
  backgroundColor?: string;
  borderColor?: string;
  textColor?: string;
  textAlign?: 'left' | 'center' | 'right';
  verticalAlign?: string;
}

// Interface spécifique pour les éléments DocumentType
export interface DocumentTypeElement extends BaseElement {
  type: 'document-type';
  // Propriétés du document
  documentType?: string;
  // Propriétés de police
  fontSize?: number;
  fontWeight?: string;
  // Propriétés de texte
  textAlign?: string;
  textColor?: string;
}
