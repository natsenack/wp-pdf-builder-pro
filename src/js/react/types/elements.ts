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

// ✅ NEW: Structure pour les produits du tableau
export interface ProductTableProduct {
  name: string;
  sku?: string;
  quantity: number;
  price: number;
  total: number;
  description?: string;
  image?: string; // URL de l'image produit
}

// ✅ NEW: Structure pour les frais du tableau
export interface ProductTableFee {
  name: string;
  total: number;
}

// ✅ NEW: Structure pour les totaux du tableau
export interface ProductTableTotals {
  subtotal: number;
  shippingCost: number;
  taxCost: number;
  taxRate: number;
  discount: number;
  total: number;
}

// ✅ NEW: Structure complète pour les données product_table
// Les frais sont au même niveau que les produits, pas imbriqués dans les totaux
export interface ProductTableData {
  products: ProductTableProduct[];
  fees: ProductTableFee[];  // ✅ REFACTOR: Frais au même niveau que produits
  totals: ProductTableTotals;
}

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
  
  // ✅ NEW: Support pour données réelles vs fictives
  /** Indique si cet élément utilise des données réelles (WooCommerce) */
  isRealDataElement?: boolean;
  
  /** Valeur fictive/par défaut affichée en édition */
  defaultTestValue?: unknown;
  
  /** Identifiant de la propriété réelle à récupérer depuis WooCommerce */
  realDataKey?: string;
}

export interface BaseElementProperties {
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
  borderRadius?: number;
  padding?: number;
  showBackground?: boolean;
  fillColor?: string;
  strokeColor?: string;
  strokeWidth?: number;
}

export interface OrderNumberElement extends BaseElement {
  type: 'order_number';
  // Propriétés d'affichage
  showHeaders?: boolean;
  showBorders?: boolean;
  showBackground?: boolean;
  showLabel?: boolean;
  showDate?: boolean;
  // Propriétés de mise en page
  contentAlign?: 'left' | 'center' | 'right';
  labelPosition?: 'above' | 'left' | 'right' | 'below';
  labelText?: string;
  // Propriétés de police générales
  fontFamily?: string;
  fontSize?: number;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: string;
  // Propriétés de police pour les en-têtes (label)
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  // Propriétés de police pour le corps (numéro et date)
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
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
  // Propriétés de couleur
  headerTextColor?: string;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  backgroundColor?: string;
  borderColor?: string;
  // Propriétés de style
  theme?: string;
  // Propriétés spécifiques à l'ordre
  prefix?: string;
  suffix?: string;
  showOrderDate?: boolean;
  dateFormat?: string;
}

export interface WoocommerceOrderDateElement extends BaseElement {
  type: 'woocommerce_order_date';
  dateFormat?: string;
  showTime?: boolean;
  fontFamily?: string;
  fontSize?: number;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: string;
  padding?: { top?: number; right?: number; bottom?: number; left?: number };
  border?: { width?: number; style?: string; color?: string };
  backgroundColor?: string;
  showBackground?: boolean;
}

export interface WoocommerceInvoiceNumberElement extends BaseElement {
  type: 'woocommerce_invoice_number';
  prefix?: string;
  suffix?: string;
  fontFamily?: string;
  fontSize?: number;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: string;
  padding?: { top?: number; right?: number; bottom?: number; left?: number };
  border?: { width?: number; style?: string; color?: string };
  backgroundColor?: string;
  showBackground?: boolean;
}

export interface DynamicTextElement extends BaseElement {
  type: 'dynamic_text';
  text?: string;
  content?: string;
  textTemplate?: string;
  theme?: string;
  textDecoration?: string;
  fontSize?: number;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  autoWrap?: boolean;
  bold?: boolean;
  italic?: boolean;
  underline?: boolean;
  showBackground?: boolean;
  backgroundColor?: string;
}

export interface ProductTableElement extends BaseElement {
  type: 'product_table';
  // Propriétés d'affichage
  showHeaders?: boolean;
  showBorders?: boolean;
  showAlternatingRows?: boolean;
  showSku?: boolean;
  showDescription?: boolean;
  showQuantity?: boolean;
  showImage?: boolean; // ✅ NEW: Afficher les images des produits
  showShipping?: boolean;
  showTax?: boolean;
  showGlobalDiscount?: boolean;
  // Propriétés de police globale
  globalFontSize?: number;
  globalFontFamily?: string;
  globalFontWeight?: string;
  globalFontStyle?: string;
  globalFontEnabled?: boolean;
  // Propriétés de police
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
  rowFontSize?: number;
  rowFontFamily?: string;
  rowFontWeight?: string;
  rowFontStyle?: string;
  totalFontSize?: number;
  totalFontFamily?: string;
  totalFontWeight?: string;
  totalFontStyle?: string;
  // Propriétés d'alignement
  textAlign?: 'left' | 'center' | 'right';
  headerBackgroundColor?: string;
  headerTextColor?: string;
  bodyBackgroundColor?: string;
  bodyTextColor?: string;
  alternateRowColor?: string;
  rowTextColor?: string;
  totalTextColor?: string;
  // Propriétés de thème et style
  theme?: string;
  textColor?: string;
  backgroundColor?: string;
  borderColor?: string;
  currency?: string;
  // Propriétés de données
  shippingCost?: number;
  taxRate?: number;
  globalDiscount?: number;
  orderFees?: number;
  verticalAlign?: 'top' | 'middle' | 'bottom';
  
  // ✅ NEW: Données produits, frais et totaux
  /** Produits affichés dans le tableau (fictifs en édition, réels en aperçu) */
  products?: ProductTableProduct[];
  
  /** Frais (port, paiement, emballage, etc.) affichés au même niveau que produits */
  fees?: ProductTableFee[];
  
  /** Totaux du tableau (subtotaux, taxes, remise, total final) */
  totals?: ProductTableTotals;
}export interface MentionsElement extends BaseElement {
  type: 'mentions';
  mentionType?: string;
  selectedMentions?: string[];
  medleySeparator?: string;
  separatorStyle?: string;
  theme?: string;
  content?: string;
  lineHeight?: number;
  margin?: number;
  padding?: number;
  text?: string;
  showSeparator?: boolean;
  showBackground?: boolean;
}

export interface CustomerInfoElement extends BaseElement {
  type: 'customer_info';
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
  bodyTextColor?: string;
  // Propriétés de mise en page
  layout?: 'vertical' | 'horizontal';
  showLabels?: boolean;
  labelPosition?: 'before' | 'above';
  // Propriétés de style générales
  fontFamily?: string;
  fontSize?: number;
  fontWeight?: string;
  fontStyle?: string;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  textAlign?: 'left' | 'center' | 'right';
  backgroundColor?: string;
  showBackground?: boolean;
  border?: { width?: number; style?: string; color?: string };
  padding?: { top?: number; right?: number; bottom?: number; left?: number };
}

export interface CompanyInfoElement extends BaseElement {
  type: 'company_info';
  template?: string;
  fields?: string[];
  layout?: 'vertical' | 'horizontal';
  separator?: string;
  // Informations de l'entreprise
  name?: string;
  address?: string;
  phone?: string;
  email?: string;
  website?: string;
  taxId?: string;
  registrationNumber?: string;
  // Propriétés de style
  fontFamily?: string;
  fontSize?: number;
  fontWeight?: string;
  fontStyle?: string;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  textAlign?: 'left' | 'center' | 'right';
  backgroundColor?: string;
  showBackground?: boolean;
  border?: { width?: number; style?: string; color?: string };
  padding?: { top?: number; right?: number; bottom?: number; left?: number };
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
  // Modal de prévisualisation
  showPreviewModal: boolean;
  // Contenu HTML de l'aperçu
  htmlPreviewContent: string;
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
  | { type: 'SET_HTML_PREVIEW_CONTENT'; payload: string }
  | { type: 'SET_ORDER_ID'; payload: string | undefined }
  | { type: 'UNDO' }
  | { type: 'REDO' }
  | { type: 'RESET' }
  | { type: 'SAVE_TEMPLATE'; payload?: { id?: string; name?: string; lastSaved?: Date } }
  | { type: 'SET_TEMPLATE_MODIFIED'; payload: boolean }
  | { type: 'SET_TEMPLATE_SAVING'; payload: boolean }
  | { type: 'SET_TEMPLATE_LOADING'; payload: boolean }
  | { type: 'UPDATE_TEMPLATE_SETTINGS'; payload: Partial<TemplateState> }
  | { type: 'TOGGLE_GUIDES' }
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
  description?: string;
  elements?: BaseElement[];
  canvas?: Partial<CanvasState>;
  lastSaved?: Date;
  showGuides?: boolean;
  snapToGrid?: boolean;
  marginTop?: number;
  marginBottom?: number;
}

// Propriétés spécifiques pour les éléments
export interface RectangleElement extends BaseElement {
  type: 'rectangle';
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
  borderRadius?: number;
  fillColor?: string;
  strokeColor?: string;
  strokeWidth?: number;
}

export interface CircleElement extends BaseElement {
  type: 'circle';
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
  fillColor?: string;
  strokeColor?: string;
  strokeWidth?: number;
}

export interface TextElement extends BaseElement {
  type: 'text';
  text?: string;
  fontSize?: number;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  fontFamily?: string;
  fontWeight?: string;
  fontStyle?: string;
  textAlign?: 'left' | 'center' | 'right';
  textDecoration?: string;
  autoWrap?: boolean;
  bold?: boolean;
  italic?: boolean;
  underline?: boolean;
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
  verticalAlign?: 'top' | 'middle' | 'bottom';
}

export interface LineElement extends BaseElement {
  type: 'line';
  backgroundColor?: string;
  borderColor?: string;
  borderWidth?: number;
}

export interface DynamicTextElementProperties extends BaseElementProperties {
  textTemplate?: string;
  theme?: string;
  textDecoration?: string;
}

export interface DocumentTypeElementProperties extends BaseElementProperties {
  documentType?: string;
  verticalAlign?: 'top' | 'middle' | 'bottom';
}

export interface CustomerInfoElementProperties extends BaseElementProperties {
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
  // ✅ NEW: Padding interne
  padding?: number;
  // Propriétés de style
  headerTextColor?: string;
  verticalAlign?: 'top' | 'middle' | 'bottom';
}

export interface CompanyInfoElementProperties extends BaseElementProperties {
  showCompanyName?: boolean;
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
  showBorders?: boolean;
  showCompanyName?: boolean;
  showSiret?: boolean;
  showVat?: boolean;
  showRcs?: boolean;
  showCapital?: boolean;
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

export interface ImageElement extends BaseElement {
  type: 'image';
  src?: string;
  logoUrl?: string;
  alt?: string;
  fit?: 'contain' | 'cover' | 'fill' | 'none' | 'scale-down';
  position?: 'center' | 'top' | 'bottom' | 'left' | 'right' | 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';
  opacity?: number;
  alignment?: 'left' | 'center' | 'right';
  maintainAspectRatio?: boolean;
}

export interface OrderNumberElementProperties extends BaseElementProperties {
  // Propriétés d'affichage
  showHeaders?: boolean;
  showBorders?: boolean;
  showLabel?: boolean;
  showDate?: boolean;
  // Propriétés de mise en page
  contentAlign?: 'left' | 'center' | 'right';
  labelPosition?: 'above' | 'left' | 'right' | 'below';
  labelText?: string;
  // Propriétés de police pour les en-têtes (label)
  headerFontSize?: number;
  headerFontFamily?: string;
  headerFontWeight?: string;
  headerFontStyle?: string;
  // Propriétés de police pour le corps (numéro et date)
  bodyFontSize?: number;
  bodyFontFamily?: string;
  bodyFontWeight?: string;
  bodyFontStyle?: string;
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
  // Propriétés de couleur
  headerTextColor?: string;
  textColor?: string;
  color?: string; // Alias pour textColor pour compatibilité
  // Propriétés de style
  theme?: string;
  // Propriétés spécifiques à l'ordre
  prefix?: string;
  suffix?: string;
  showOrderDate?: boolean;
  dateFormat?: string;
}

export interface MentionsElementProperties extends BaseTextProperties {
  mentionType?: string;
  selectedMentions?: string[];
  medleySeparator?: string;
  separatorStyle?: string;
  separatorColor?: string;
  separatorWidth?: number;
  theme?: string;
  content?: string;
  lineHeight?: number;
  margin?: number;
  padding?: number;
  text?: string;
  showSeparator?: boolean;
  showBackground?: boolean; // Override
}

// Interface spécifique pour les éléments CompanyInfo
export interface CompanyInfoElement extends BaseElement {
  type: 'company_info';
  // Propriétés d'affichage
  showCompanyName?: boolean;
  showAddress?: boolean;
  showPhone?: boolean;
  showEmail?: boolean;
  showWebsite?: boolean;
  showTaxId?: boolean;
  // Propriétés de logo
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
  showBorders?: boolean;
  showSiret?: boolean;
  showVat?: boolean;
  showRcs?: boolean;
  showCapital?: boolean;
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

// Interface spécifique pour les éléments CompanyLogo
export interface CompanyLogoElement extends BaseElement {
  type: 'company_logo';
  // Propriétés d'image
  src?: string;
  logoUrl?: string;
  alt?: string;
  fit?: 'contain' | 'cover' | 'fill' | 'none' | 'scale-down';
  position?: 'center' | 'top' | 'bottom' | 'left' | 'right' | 'top-left' | 'top-right' | 'bottom-left' | 'bottom-right';
  opacity?: number;
  alignment?: 'left' | 'center' | 'right';
  maintainAspectRatio?: boolean;
}

// Interface spécifique pour les éléments CustomerInfo
export interface CustomerInfoElement extends BaseElement {
  type: 'customer_info';
  // Propriétés d'affichage
  showName?: boolean;
  showEmail?: boolean;
  showPhone?: boolean;
  showAddress?: boolean;
  showCompany?: boolean;
  showPaymentMethod?: boolean;
  showTransactionId?: boolean;
  // Propriétés de couleur
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
  // ✅ NEW: Padding interne
  padding?: number;
  // Propriétés de style
  headerTextColor?: string;
  verticalAlign?: 'top' | 'middle' | 'bottom';
}

// Interface spécifique pour les éléments DocumentType
export interface DocumentTypeElement extends BaseElement {
  type: 'document_type';
  documentType?: string;
  verticalAlign?: 'top' | 'middle' | 'bottom';
}

// Type union pour tous les éléments supportés
export type Element =
  | OrderNumberElement
  | WoocommerceOrderDateElement
  | WoocommerceInvoiceNumberElement
  | DynamicTextElement
  | ProductTableElement
  | MentionsElement
  | CustomerInfoElement
  | CompanyInfoElement
  | RectangleElement
  | CircleElement
  | TextElement
  | LineElement
  | DocumentTypeElement;



