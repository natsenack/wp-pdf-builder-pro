import {
  useRef,
  useEffect,
  useCallback,
  useState,
  useMemo,
  MutableRefObject,
  MouseEvent,
} from "react";
import { useBuilder } from "../../contexts/builder/BuilderContext";
import { useCanvasSettings, DEFAULT_SETTINGS } from "../../contexts/CanvasSettingsContext";
import { useCanvasSetting } from "../../hooks/useCanvasSettings";
import { useCanvasDrop } from "../../hooks/useCanvasDrop";
import { useCanvasInteraction } from "../../hooks/useCanvasInteraction";
import { useKeyboardShortcuts } from "../../hooks/useKeyboardShortcuts";
import {
  Element,
  ShapeElementProperties,
  TextElementProperties,
  LineElementProperties,
  ProductTableElementProperties,
  CustomerInfoElementProperties,
  CompanyInfoElementProperties,
  ImageElementProperties,
  OrderNumberElementProperties,
  MentionsElementProperties,
  DocumentTypeElementProperties,
  DynamicTextElement,
  BuilderState,
} from "../../types/elements";
import { wooCommerceManager } from "../../utils/WooCommerceElementsManager";
import { elementChangeTracker } from "../../utils/ElementChangeTracker";
import { debugWarn, debugError, debugLog } from "../../utils/debug";

// Déclaration pour l'API Performance
declare const performance: {
  memory?: {
    usedJSHeapSize: number;
    totalJSHeapSize: number;
    jsHeapSizeLimit: number;
  };
};

// Fonctions utilitaires pour la gestion mémoire des images
const estimateImageMemorySize = (img: HTMLImageElement): number => {
  // Estimation basée sur les dimensions et le nombre de canaux (RGBA = 4 octets par pixel)
  const bytesPerPixel = 4;
  return img.naturalWidth * img.naturalHeight * bytesPerPixel;
};

// Fonction utilitaire pour dessiner des rectangles avec coins arrondis
const roundedRect = (
  ctx: CanvasRenderingContext2D,
  x: number,
  y: number,
  width: number,
  height: number,
  radius: number
) => {
  ctx.beginPath();
  ctx.moveTo(x + radius, y);
  ctx.lineTo(x + width - radius, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
  ctx.lineTo(x + width, y + height - radius);
  ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
  ctx.lineTo(x + radius, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
  ctx.lineTo(x, y + radius);
  ctx.quadraticCurveTo(x, y, x + radius, y);
  ctx.closePath();
};

const cleanupImageCache = (
  imageCache: MutableRefObject<
    Map<string, { image: HTMLImageElement; size: number; lastUsed: number }>
  >
) => {
  const cache = imageCache.current;
  if (cache.size <= 100) return; // Max 100 images

  // Trier par date d'utilisation et supprimer les plus anciennes
  const entries = Array.from(cache.entries()).sort(
    ([, a], [, b]) => a.lastUsed - b.lastUsed
  );
  const toRemove = entries.slice(0, Math.ceil(cache.size * 0.2)); // Supprimer 20%

  toRemove.forEach(([url]) => cache.delete(url));
};
import { CanvasMonitoringDashboard } from "../../utils/CanvasMonitoringDashboard";
import { ContextMenu, ContextMenuItem } from "../ui/ContextMenu";

// Fonctions utilitaires de dessin (déplacées en dehors du composant pour éviter les avertissements React Compiler)

// Fonction pour normaliser les couleurs (simple identité pour le web)
const normalizeColor = (color: string): string => color;

// Fonction pour normaliser les poids de police
const normalizeFontWeight = (weight: string | number): string => {
  if (typeof weight === 'number') return weight.toString();
  if (weight === 'bold') return '700';
  if (weight === 'normal') return '400';
  return weight;
};

// Constantes communes pour les valeurs par défaut
const DEFAULT_FONT = {
  family: "Arial",
  size: 12,
  weight: "normal",
  style: "normal",
} as const;

const DEFAULT_COLORS = {
  background: "#ffffff",
  border: "#000000",
  text: "#000000",
} as const;

// Fonction helper pour configurer les polices
const createFontConfig = (props: any, baseSize: number = DEFAULT_FONT.size) => ({
  family: props.fontFamily || DEFAULT_FONT.family,
  size: props.fontSize || baseSize,
  weight: props.fontWeight || DEFAULT_FONT.weight,
  style: props.fontStyle || DEFAULT_FONT.style,
});

// Fonction helper pour configurer les couleurs avec normalisation
const createColorConfig = (props: any, defaults: typeof DEFAULT_COLORS = DEFAULT_COLORS) => ({
  background: normalizeColor(props.backgroundColor || defaults.background),
  border: normalizeColor(props.borderColor || defaults.border),
  text: normalizeColor(props.textColor || defaults.text),
});

// Fonction helper pour configurer le padding
const getPadding = (props: any) => ({
  top: props.padding?.top || props.paddingTop || 0,
  right: props.padding?.right || props.paddingRight || 0,
  bottom: props.padding?.bottom || props.paddingBottom || 0,
  left: props.padding?.left || props.paddingLeft || 0,
});

// Fonction helper pour calculer la position X selon l'alignement
const calculateTextX = (element: Element, textAlign: string, padding: ReturnType<typeof getPadding>) => {
  switch (textAlign) {
    case "center": return element.width / 2;
    case "right": return element.width - padding.right;
    default: return padding.left;
  }
};

// Fonction helper pour appliquer les bordures avec style
const applyBorder = (ctx: CanvasRenderingContext2D, element: Element, borderProps: any) => {
  if (!borderProps || borderProps.width <= 0) return;

  ctx.strokeStyle = borderProps.color || "#000000";
  ctx.lineWidth = borderProps.width;

  // Appliquer le style de ligne
  switch (borderProps.style) {
    case "dashed": ctx.setLineDash([5, 5]); break;
    case "dotted": ctx.setLineDash([2, 2]); break;
    default: ctx.setLineDash([]);
  }

  ctx.strokeRect(0, 0, element.width, element.height);
  ctx.setLineDash([]); // Reset
};

// Fonction helper pour configurer le contexte de rendu de base
const setupRenderContext = (
  ctx: CanvasRenderingContext2D,
  fontConfig: ReturnType<typeof createFontConfig>,
  colorConfig: ReturnType<typeof createColorConfig>,
  textAlign: string = "left",
  verticalAlign: string = "top"
) => {
  ctx.fillStyle = colorConfig.text;
  ctx.font = `${fontConfig.style} ${fontConfig.weight} ${fontConfig.size}px ${fontConfig.family}`;
  ctx.textAlign = textAlign as CanvasTextAlign;
  
  // Définir le textBaseline selon l'alignement vertical
  switch (verticalAlign) {
    case "middle":
      ctx.textBaseline = "middle";
      break;
    case "bottom":
      ctx.textBaseline = "bottom";
      break;
    default: // top
      ctx.textBaseline = "top";
  }
};

// Fonction helper pour configurer les couleurs des shapes
const createShapeColors = (props: any) => ({
  background: normalizeColor(props.backgroundColor || props.fillColor || "#ffffff"),
  border: normalizeColor(props.borderColor || props.strokeColor || "#000000"),
});

// Fonction helper pour appliquer les couleurs et styles de shape
const applyShapeStyle = (ctx: CanvasRenderingContext2D, colors: ReturnType<typeof createShapeColors>, borderWidth: number = 1) => {
  ctx.fillStyle = colors.background;
  ctx.strokeStyle = colors.border;
  ctx.lineWidth = borderWidth;
};

// Fonction helper pour calculer la position X selon l'alignement du texte
const calculateTextAlignX = (element: Element, align: string = "left", padding: number = 0) => {
  switch (align) {
    case "center":
      return element.width / 2;
    case "right":
      return element.width - padding;
    default:
      return padding;
  }
};

// Fonction helper pour calculer la position Y selon l'alignement vertical
const calculateTextY = (element: Element, verticalAlign: string = "top", fontSize: number = 12, padding: number = 0) => {
  switch (verticalAlign) {
    case "middle":
      return element.height / 2;
    case "bottom":
      return element.height - padding;
    default: // top
      return padding || fontSize;
  }
};

// Fonction helper pour calculer Y avec padding
const calculateTextYWithPadding = (element: Element, verticalAlign: string = "top", paddingConfig: ReturnType<typeof getPadding>) => {
  const centerY = element.height / 2;
  
  switch (verticalAlign) {
    case "middle":
      return centerY;
    case "bottom":
      return element.height - paddingConfig.bottom;
    default: // top
      return paddingConfig.top || 10;
  }
};
const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as RectangleElement;
  const colors = createShapeColors(props);

  applyShapeStyle(ctx, colors, props.borderWidth);

  if (props.borderRadius && props.borderRadius > 0) {
    roundedRect(ctx, 0, 0, element.width, element.height, props.borderRadius);
    ctx.fill();
    ctx.stroke();
  } else {
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeRect(0, 0, element.width, element.height);
  }
};

const drawCircle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as CircleElement;
  const colors = createShapeColors(props);

  applyShapeStyle(ctx, colors, props.borderWidth);

  const centerX = element.width / 2;
  const centerY = element.height / 2;
  const radius = Math.min(centerX, centerY);

  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
  ctx.fill();
  ctx.stroke();
};

const drawText = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as TextElement;
  const fontConfig = createFontConfig(props, 16);
  const colorConfig = createColorConfig(props);

  setupRenderContext(ctx, fontConfig, colorConfig, props.textAlign, props.verticalAlign);

  // ✅ NEW: Ajouter du padding
  const padding = props.padding || 12;

  const x = calculateTextAlignX(element, props.textAlign, padding);
  const y = calculateTextY(element, props.verticalAlign, fontConfig.size, padding);

  ctx.fillText(props.text || "Text", x, y);
};

const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as LineElement;
  const colors = createShapeColors(props);

  ctx.strokeStyle = colors.border;
  ctx.lineWidth = props.borderWidth || 2;

  ctx.beginPath();
  ctx.moveTo(0, element.height / 2);
  ctx.lineTo(element.width, element.height / 2);
  ctx.stroke();
};

// Fonction pour dessiner une image avec gestion optimisée du cache
const drawImage = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  imageCache: MutableRefObject<
    Map<string, { image: HTMLImageElement; size: number; lastUsed: number }>
  >
) => {
  const props = element as ImageElement;
  const imageUrl = props.src || "";

  if (!imageUrl) {
    // Placeholder pour image manquante
    drawImagePlaceholder(ctx, element);
    return;
  }

  const cachedImage = imageCache.current.get(imageUrl);
  if (!cachedImage) {
    loadAndCacheImage(imageUrl, imageCache);
    drawImagePlaceholder(ctx, element);
    return;
  }

  // Mettre à jour la date d'utilisation
  cachedImage.lastUsed = Date.now();

  const img = cachedImage.image;
  if (img.complete && img.naturalHeight !== 0) {
    drawImageWithObjectFit(ctx, img, element, props.fit || "cover");
  } else {
    drawImagePlaceholder(ctx, element);
  }
};

// Fonction helper pour dessiner un placeholder d'image
const drawImagePlaceholder = (ctx: CanvasRenderingContext2D, element: Element) => {
  const colors = createColorConfig({}, { background: "#f0f0f0", border: "#cccccc", text: "#999999" });

  ctx.fillStyle = colors.background;
  ctx.fillRect(0, 0, element.width, element.height);
  ctx.strokeStyle = colors.border;
  ctx.lineWidth = 1;
  ctx.strokeRect(0, 0, element.width, element.height);

  ctx.fillStyle = colors.text;
  ctx.font = "14px Arial";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillText("Image", element.width / 2, element.height / 2);
};

// Fonction helper pour charger et mettre en cache une image
const loadAndCacheImage = (
  imageUrl: string,
  imageCache: MutableRefObject<Map<string, { image: HTMLImageElement; size: number; lastUsed: number }>>
) => {
  const img = document.createElement("img");
  img.crossOrigin = "anonymous";
  img.src = imageUrl;

  img.onload = () => {
    const size = estimateImageMemorySize(img);
    imageCache.current.set(imageUrl, {
      image: img,
      size,
      lastUsed: Date.now(),
    });
    cleanupImageCache(imageCache);
  };

  img.onerror = () => {
    debugWarn(`[Canvas] Failed to load image: ${imageUrl}`);
  };
};

// Fonction helper pour dessiner une image avec object-fit
const drawImageWithObjectFit = (
  ctx: CanvasRenderingContext2D,
  img: HTMLImageElement,
  element: Element,
  objectFit: string
) => {
  let drawX = 0, drawY = 0, drawWidth = element.width, drawHeight = element.height;
  let sourceX = 0, sourceY = 0, sourceWidth = img.naturalWidth, sourceHeight = img.naturalHeight;

  switch (objectFit) {
    case "contain":
      const containRatio = Math.min(element.width / img.naturalWidth, element.height / img.naturalHeight);
      drawWidth = img.naturalWidth * containRatio;
      drawHeight = img.naturalHeight * containRatio;
      drawX = (element.width - drawWidth) / 2;
      drawY = (element.height - drawHeight) / 2;
      break;

    case "cover":
      const coverRatio = Math.max(element.width / img.naturalWidth, element.height / img.naturalHeight);
      sourceWidth = element.width / coverRatio;
      sourceHeight = element.height / coverRatio;
      sourceX = (img.naturalWidth - sourceWidth) / 2;
      sourceY = (img.naturalHeight - sourceHeight) / 2;
      break;

    case "scale-down":
      if (img.naturalWidth > element.width || img.naturalHeight > element.height) {
        const scaleDownRatio = Math.min(element.width / img.naturalWidth, element.height / img.naturalHeight);
        drawWidth = img.naturalWidth * scaleDownRatio;
        drawHeight = img.naturalHeight * scaleDownRatio;
        drawX = (element.width - drawWidth) / 2;
        drawY = (element.height - drawHeight) / 2;
      }
      break;
    // "fill" utilise les dimensions par défaut
  }

  ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, drawX, drawY, drawWidth, drawHeight);
};

// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawProductTable = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState,
  imageCache?: MutableRefObject<Map<string, { image: HTMLImageElement; size: number; lastUsed: number }>>,
  setImageLoadCount?: (fn: (prev: number) => number) => void
) => {
  const props = element as ProductTableElement;

  // ✅ BUGFIX-020: Validate element has minimum size for rendering
  const minWidth = 100;
  const minHeight = 50;

  if (element.width < minWidth || element.height < minHeight) {
    // Element too small, draw placeholder
    ctx.fillStyle = normalizeColor("#f0f0f0");
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.fillStyle = normalizeColor("#999999");
    ctx.font = "12px Arial";
    ctx.textAlign = "center";
    ctx.fillText("Trop petit", element.width / 2, element.height / 2);
    return;
  }

  const showHeaders = props.showHeaders !== false;
  const showBorders = props.showBorders !== false;
  const showAlternatingRows = props.showAlternatingRows !== false;
  const fontSize = props.fontSize || 11;
  const fontFamily = props.fontFamily || "Arial";
  const fontWeight = props.fontWeight || "normal";
  const fontStyle = props.fontStyle || "normal";
  const showSku = props.showSku !== false;
  const showDescription = props.showDescription !== false;
  const showShipping = props.showShipping !== false;
  const showTax = props.showTax !== false;
  const showGlobalDiscount = props.showGlobalDiscount !== false;
  const textColor = normalizeColor(props.textColor || "#000000");
  const borderRadius = props.borderRadius || 0;
  
  // ✅ NEW: Utiliser element.columns pour les colonnes dynamiques
  const showImage = props.columns?.image !== false;
  const showName = props.columns?.name !== false;
  const showQuantity = props.columns?.quantity !== false;
  const showPrice = props.columns?.price !== false;
  const showTotal = props.columns?.total !== false;

  // ✅ NEW: Use real WooCommerce data in preview mode
  let products: Array<{
    sku: string;
    name: string;
    description: string;
    qty: number;
    price: number;
    discount: number;
    total: number;
  }>;

  if (state.previewMode === "command") {
    // Get real order items from WooCommerce
    const orderItems = wooCommerceManager.getOrderItems();
    const orderFees = wooCommerceManager.getOrderFees();
    
    // Combine products and fees into one array
    products = [
      ...orderItems.map(item => ({
        sku: item.sku,
        name: item.name,
        description: item.description,
        qty: item.qty,
        price: item.price,
        discount: item.discount,
        total: item.total,
      })),
      ...orderFees.map(fee => ({
        sku: 'FEE',
        name: fee.name,
        description: '',
        qty: 1,
        price: fee.total,
        discount: 0,
        total: fee.total,
      }))
    ];
  } else {
    // Use props.products in editor mode
    products = (props.products || []).map(p => ({
      sku: p.sku || 'N/A',
      name: p.name,
      description: p.description || '',
      qty: p.quantity,
      price: p.price,
      discount: 0, // Les remises sont dans totals.discount
      total: p.total,
    }));
  }

  // 🔴 FIX: Si pas de produits, utiliser les données fictives par défaut
  if (!products || products.length === 0) {
    products = [
      {
        sku: "TSHIRT-001",
        name: "T-shirt Premium Bio",
        description: "T-shirt en coton biologique, coupe slim",
        qty: 2,
        price: 29.99,
        discount: 0,
        total: 59.98,
      },
      {
        sku: "JEAN-045",
        name: "Jean Slim Fit Noir",
        description: "Jean stretch confort, taille haute",
        qty: 1,
        price: 89.99,
        discount: 10.0,
        total: 79.99,
      },
      {
        sku: "SHOES-089",
        name: "Chaussures Running Pro",
        description: "Chaussures de running avec semelle amortissante",
        qty: 1,
        price: 129.99,
        discount: 0,
        total: 129.99,
      },
      {
        sku: "HOODIE-112",
        name: "Sweat à Capuche",
        description: "Sweat molletonné, capuche ajustable",
        qty: 1,
        price: 49.99,
        discount: 5.0,
        total: 44.99,
      },
      {
        sku: "SERVICE-FEE",
        name: "Frais de service",
        description: "Frais supplémentaires pour emballage premium",
        qty: 1,
        price: 15.00,
        discount: 0,
        total: 15.00,
      },
    ];
  }

  // Fees are now included in products array for preview mode
  const fees = state.previewMode === "command" ? [] : (props.fees || []);

  const currency = "€";

  // ✅ Use real WooCommerce totals in preview mode
  let subtotal: number;
  let shippingCost: number;
  let taxAmount: number;
  let globalDiscount: number;
  let totalFees = 0;
  let taxRate = 0; // Pour l'affichage du %

  if (state.previewMode === "command") {
    // Get real order totals from WooCommerce
    const orderTotals = wooCommerceManager.getOrderTotals();
    // Calculate subtotal from products (includes fees already)
    subtotal = products.reduce((sum, p) => sum + (Number(p.total) || 0), 0);
    shippingCost = Number(orderTotals.shipping) || 0;
    taxAmount = Number(orderTotals.tax) || 0;
    globalDiscount = Number(orderTotals.discount) || 0;
    // Calculate tax rate for display
    if (subtotal > 0 && taxAmount > 0) {
      taxRate = (taxAmount / subtotal) * 100;
    }
    // Fees already included in products array
    totalFees = 0;
  } else {
    // ✅ CALCUL CORRECT DES TOTALS - Pas de hardcoding
    // 1) Calculer le sous-total à partir des produits
    subtotal = products.reduce((sum, p) => sum + (p.total || 0), 0);
    
    // 2) Ajouter les frais supplémentaires si présents
    totalFees = fees.reduce((sum, f) => sum + (f.total || 0), 0);
    
    // 3) Lire les valeurs depuis les propriétés de l'élément OU utiliser les valeurs de totals comme fallback
    shippingCost = props.shippingCost as any || (props.totals?.shippingCost as any) || 10.0; // Valeur fictive: 10€
    taxRate = props.taxRate as any || (props.totals?.taxRate as any) || 5; // Valeur fictive: 5%
    globalDiscount = props.globalDiscount as any || (props.totals?.discount as any) || 20.0; // Valeur fictive: 20€
    
    // Calculate tax from rate
    taxAmount = showTax && taxRate > 0 ? (subtotal + shippingCost) * taxRate / 100 : 0;
  }
  
  // 4) APPLIQUER LES FLAGS ACTIFS - Mettre à zéro les éléments désactivés
  if (!showShipping) {
    shippingCost = 0;
  }
  if (!showTax) {
    taxAmount = 0; // Si TVA non affichée, ne pas l'appliquer
  }
  if (!showGlobalDiscount) {
    globalDiscount = 0; // Si remise non affichée, ne pas l'appliquer
  }
  
  // 5) Calculer le TOTAL FINAL: subtotal + frais de port + TVA + frais supplémentaires - remise
  const finalTotal = subtotal + shippingCost + taxAmount + totalFees - globalDiscount;

  // Configuration des colonnes
  interface TableColumn {
    key: string;
    label: string;
    width: number;
    align: "left" | "center" | "right";
    x: number;
  }

  // ✅ NEW: Définir les largeurs de base pour TOUTES les colonnes
  // avant de les normaliser ensemble
  const columnDefs: Array<{
    key: string;
    label: string;
    width: number;
    align: "left" | "center" | "right";
    show: boolean;
  }> = [
    {
      key: "image",
      label: "Img",
      width: 0.08,
      align: "center",
      show: showImage,
    },
    {
      key: "name",
      label: "Produit",
      width: showSku && showDescription
        ? 0.35
        : showSku || showDescription
        ? 0.45
        : 0.55,
      align: "left",
      show: showName,
    },
    {
      key: "sku",
      label: "SKU",
      width: 0.15,
      align: "left",
      show: showSku,
    },
    {
      key: "description",
      label: "Description",
      width: 0.25,
      align: "left",
      show: showDescription,
    },
    {
      key: "qty",
      label: "Qté",
      width: 0.08,
      align: "center",
      show: showQuantity,
    },
    {
      key: "price",
      label: "Prix",
      width: 0.12,
      align: "right",
      show: showPrice,
    },
    {
      key: "total",
      label: "Total",
      width: 0.12,
      align: "right",
      show: showTotal,
    },
  ];

  // ✅ NEW: Ajouter seulement les colonnes affichées
  const columns: TableColumn[] = [];
  for (const colDef of columnDefs) {
    if (colDef.show) {
      columns.push({
        key: colDef.key,
        label: colDef.label,
        width: colDef.width,
        align: colDef.align,
        x: 0,
      });
    }
  }

  // ✅ FIX: Colonne image avec largeur FIXE (en pixels)
  const IMAGE_COLUMN_WIDTH_PX = 70; // Largeur fixe pour images
  
  // Séparer les colonnes image et autres
  const imageColumnIndex = columns.findIndex(col => col.key === "image");
  const hasImageColumn = imageColumnIndex !== -1;
  const tableWidthPixels = element.width - 16;
  const availableWidthPixels = hasImageColumn ? tableWidthPixels - IMAGE_COLUMN_WIDTH_PX : tableWidthPixels;
  
  // Normaliser seulement les colonnes non-image
  const nonImageColumns = columns.filter((_, i) => i !== imageColumnIndex);
  const totalWidthNonImage = nonImageColumns.reduce((sum, col) => sum + col.width, 0);
  
  // Recalculer les largeurs proportionnelles pour les colonnes non-image
  nonImageColumns.forEach((col) => {
    col.width = (col.width / totalWidthNonImage) * (availableWidthPixels / tableWidthPixels);
  });
  
  // Fixer la largeur de la colonne image
  if (hasImageColumn) {
    columns[imageColumnIndex].width = IMAGE_COLUMN_WIDTH_PX / tableWidthPixels;
  }

  // Calcul des positions X des colonnes
  let currentX = 8;
  columns.forEach((col) => {
    col.x = currentX;
    currentX += col.width * (element.width - 16);
  });

  // ✅ Appliquer l'alignement vertical seulement (plus simple et moins risqué)
  const verticalAlign = props.verticalAlign || "top";

  // Calculer la hauteur totale du tableau pour l'alignement vertical
  const rowHeight = showDescription ? 50 : 35;
  const headerHeight = showHeaders ? 35 : 0;
  const productsCount = products.length;
  const tableHeight = headerHeight + productsCount * (rowHeight + 4) + 60; // +60 pour les totaux

  // Offset vertical seulement
  let offsetY = 0;

  // Alignement vertical - déplace le point d'origine vertical du tableau
  if (verticalAlign === "middle") {
    offsetY = Math.max(0, (element.height - tableHeight) / 2);
  } else if (verticalAlign === "bottom") {
    offsetY = Math.max(0, element.height - tableHeight - 10);
  }

  // Fond
  ctx.fillStyle = normalizeColor(props.backgroundColor || "#ffffff");
  ctx.fillRect(0, 0, element.width, element.height);

  // Bordure extérieure
  if (showBorders) {
    ctx.strokeStyle = props.borderColor || "#d1d5db";
    ctx.lineWidth = props.borderWidth || 1;
    if (borderRadius > 0) {
      roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
      ctx.stroke();
    } else {
      ctx.strokeRect(0, 0, element.width, element.height);
    }
  }

  ctx.textAlign = "left";
  let currentY = (showHeaders ? 25 : 15) + offsetY;

  // En-têtes avec style professionnel
  if (showHeaders) {
    ctx.fillStyle = normalizeColor(props.headerBackgroundColor || "#f9fafb");
    // Utiliser roundedRect si borderRadius > 0, sinon fillRect normal
    if (borderRadius > 0) {
      roundedRect(ctx, 1, 1 + offsetY, element.width - 2, 32, borderRadius);
      ctx.fill();
    } else {
      ctx.fillRect(1, 1 + offsetY, element.width - 2, 32);
    }

    ctx.fillStyle = normalizeColor(props.headerTextColor || "#374151");
    ctx.font = `${fontStyle} ${fontWeight} ${fontSize + 1}px ${fontFamily}`;
    ctx.textBaseline = "top";

    columns.forEach((col) => {
      ctx.textAlign = col.align as CanvasTextAlign;
      const textX =
        col.align === "right"
          ? col.x + col.width * (element.width - 16) - 4
          : col.align === "center"
          ? col.x + (col.width * (element.width - 16)) / 2
          : col.x;
      ctx.fillText(col.label, textX, 10 + offsetY); // Ajusté pour centrer dans la hauteur plus grande
    });

    // Ligne de séparation sous les en-têtes
    ctx.strokeStyle = normalizeColor("#e5e7eb");
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(4, 34 + offsetY); // Ajusté pour la nouvelle hauteur
    ctx.lineTo(element.width - 4, 34 + offsetY);
    ctx.stroke();

    currentY = 42 + offsetY; // Ajusté pour la nouvelle hauteur d'entête
  } else {
    currentY = 15 + offsetY;
  }

  // Produits avec alternance de couleurs
  ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
  ctx.textBaseline = "middle";

  products.forEach((product, index) => {
    // Calcul de la position Y absolue pour cette ligne
    const rowY = currentY + index * (rowHeight + 4);

    // Fond alterné pour les lignes (sans bordures)
    if (showAlternatingRows && index % 2 === 1) {
      ctx.fillStyle = normalizeColor(props.alternateRowColor || "#f9fafb");
      // Utiliser roundedRect si borderRadius > 0
      if (borderRadius > 0) {
        roundedRect(ctx, 1, rowY, element.width - 2, rowHeight, borderRadius);
        ctx.fill();
      } else {
        ctx.fillRect(1, rowY, element.width - 2, rowHeight);
      }
    }

    ctx.fillStyle = textColor; // Utiliser la couleur du texte depuis les propriétés

    columns.forEach((col) => {
      ctx.textAlign = col.align as CanvasTextAlign;
      const textX =
        col.align === "right"
          ? col.x + col.width * (element.width - 16) - 4
          : col.align === "center"
          ? col.x + (col.width * (element.width - 16)) / 2
          : col.x;

      let text = "";
      let isImage = false;
      let imageUrl = "";
      
      switch (col.key) {
        case "image":
          isImage = true;
          imageUrl = product.image || "";
          break;
        case "name":
          text = product.name;
          break;
        case "sku":
          text = product.sku;
          break;
        case "description":
          text = product.description;
          break;
        case "qty":
          text = product.qty.toString();
          break;
        case "price":
          text = `${product.price.toFixed(2)}${currency}`;
          break;
        case "discount":
          text =
            product.discount > 0
              ? `${product.discount.toFixed(2)}${currency}`
              : "-";
          break;
        case "total":
          text = `${product.total.toFixed(2)}${currency}`;
          break;
      }
      
      // ✅ NEW: Rendre l'image ou un placeholder
      if (isImage) {
        const cellWidth = col.width * (element.width - 16);
        const cellHeight = rowHeight - 2;
        const cellX = col.x;
        const cellY = rowY + 1; // ✅ FIX: Positionner la cellule au début de la ligne (pas au-dessus)
        
        // Dessiner le fond de la cellule
        ctx.fillStyle = normalizeColor("#f0f0f0");
        ctx.fillRect(cellX, cellY, cellWidth, cellHeight);
        
        if (imageUrl && imageCache) {
          // Vérifier si l'image est en cache
          let cachedImage = imageCache.current.get(imageUrl);
          
          if (!cachedImage) {
            // Charger l'image dans le cache
            const img = document.createElement("img");
            img.crossOrigin = "anonymous";
            img.src = imageUrl;
            
            img.onload = () => {
              const size = estimateImageMemorySize(img);
              imageCache.current.set(imageUrl, {
                image: img,
                size: size,
                lastUsed: Date.now(),
              });
              cleanupImageCache(imageCache);
              // Forcer un redraw du canvas quand l'image se charge
              if (setImageLoadCount) {
                setImageLoadCount((prev) => prev + 1);
              }
            };
            
            img.onerror = () => {
              debugWarn(`[Canvas ProductTable] Failed to load image: ${imageUrl}`);
            };
            
            // Afficher un placeholder en attendant
            ctx.fillStyle = normalizeColor("#999999");
            ctx.font = `${fontSize}px ${fontFamily}`;
            ctx.textAlign = "center";
            ctx.fillText("⏳", cellX + cellWidth / 2, cellY + cellHeight / 2);
          } else {
            // Image en cache, l'afficher
            const img = cachedImage.image;
            cachedImage.lastUsed = Date.now();
            
            // Vérifier que l'image est bien chargée
            if (img.complete && img.naturalHeight !== 0) {
              // Calculer les dimensions de l'image pour respecter le ratio et tenir dans la cellule
              const imgWidth = img.naturalWidth;
              const imgHeight = img.naturalHeight;
              const imgRatio = imgWidth / imgHeight;
              const cellRatio = cellWidth / cellHeight;
              
              let drawWidth = cellWidth;
              let drawHeight = cellHeight;
              let drawX = cellX;
              let drawY = cellY;
              
              // Contenir l'image dans la cellule en respectant le ratio
              if (imgRatio > cellRatio) {
                // Image plus large
                drawHeight = cellWidth / imgRatio;
                drawY = cellY + (cellHeight - drawHeight) / 2;
              } else {
                // Image plus haute
                drawWidth = cellHeight * imgRatio;
                drawX = cellX + (cellWidth - drawWidth) / 2;
              }
              
              // Dessiner l'image
              try {
                ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
              } catch (e) {
                debugWarn("[Canvas ProductTable] Error drawing image:", e);
              }
            } else {
              // Image en cache mais pas encore chargée
              ctx.fillStyle = normalizeColor("#999999");
              ctx.font = `${fontSize}px ${fontFamily}`;
              ctx.textAlign = "center";
              ctx.fillText("⏳", cellX + cellWidth / 2, cellY + cellHeight / 2);
            }
          }
        } else if (!imageUrl) {
          // Pas d'URL d'image, afficher un placeholder vide
          ctx.fillStyle = normalizeColor("#999999");
          ctx.font = `${fontSize}px ${fontFamily}`;
          ctx.textAlign = "center";
          ctx.fillText("📷", cellX + cellWidth / 2, cellY + cellHeight / 2);
        }
        
        // ✅ FIX: Restaurer la couleur de texte après le rendu de l'image
        ctx.fillStyle = textColor;
      } else {
        // Gestion du texte qui dépasse
        const maxWidth = col.width * (element.width - 16) - 8;
        if (ctx.measureText(text).width > maxWidth && col.key === "name") {
          // Tronquer avec "..."
          let truncated = text;
          while (
            ctx.measureText(truncated + "...").width > maxWidth &&
            truncated.length > 0
          ) {
            truncated = truncated.slice(0, -1);
          }
          text = truncated + "...";
        }

        ctx.fillText(text, textX, rowY + rowHeight / 2);
      }
    });
  });
  currentY = 55 + products.length * (rowHeight + 4) + 8;

  // Section des totaux

  // Ligne de séparation avant les totaux
  ctx.strokeStyle = normalizeColor("#d1d5db");
  ctx.lineWidth = 1;
  ctx.beginPath();
  ctx.moveTo(element.width - 200, currentY);
  ctx.lineTo(element.width - 8, currentY);
  ctx.stroke();

  currentY += 20;

  // Affichage des totaux
  ctx.font = `bold ${fontSize}px Arial`;
  ctx.fillStyle = textColor;
  ctx.textAlign = "left";

  const totalsY = currentY;
  ctx.fillText("Sous-total:", element.width - 200, totalsY);
  ctx.textAlign = "right";
  ctx.fillText(
    `${subtotal.toFixed(2)}${currency}`,
    element.width - 8,
    totalsY
  );

  currentY += 18;

  // Remise globale
  if (globalDiscount > 0 && showGlobalDiscount) {
    ctx.textAlign = "left";
    ctx.fillStyle = normalizeColor("#059669"); // Vert pour la remise
    ctx.fillText("Remise:", element.width - 200, currentY);
    ctx.textAlign = "right";
    ctx.fillText(
      `-${globalDiscount.toFixed(2)}${currency}`,
      element.width - 8,
      currentY
    );
    currentY += 18;
  }

  // Frais de port
  if (shippingCost > 0 && showShipping) {
    ctx.textAlign = "left";
    ctx.fillStyle = textColor;
    ctx.fillText("Frais de port:", element.width - 200, currentY);
    ctx.textAlign = "right";
    ctx.fillText(
      `+${shippingCost.toFixed(2)}${currency}`,
      element.width - 8,
      currentY
    );
    currentY += 18;
  }

  // Taxes
  if (taxAmount > 0 && showTax) {
    ctx.textAlign = "left";
    ctx.fillStyle = textColor;
    const taxLabel = taxRate > 0 ? `TVA (${taxRate.toFixed(1)}%):` : 'TVA:';
    ctx.fillText(taxLabel, element.width - 200, currentY);
    ctx.textAlign = "right";
    ctx.fillText(
      `+${taxAmount.toFixed(2)}${currency}`,
      element.width - 8,
      currentY
    );
    currentY += 18;
  }

  currentY += 8;
  ctx.strokeStyle = textColor;
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(element.width - 200, currentY - 5);
  ctx.lineTo(element.width - 8, currentY - 5);
  ctx.stroke();

  currentY += 8;
  ctx.font = `${fontStyle} bold ${fontSize + 2}px ${fontFamily}`;
  ctx.fillStyle = textColor;
  ctx.textAlign = "left";
  ctx.fillText("TOTAL:", element.width - 200, currentY);
  ctx.textAlign = "right";
  ctx.fillText(
    `${finalTotal.toFixed(2)}${currency}`,
    element.width - 8,
    currentY
  );
};

// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawCustomerInfo = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as CustomerInfoElement;
  const fontSize = props.fontSize || 12;
  const fontFamily = props.fontFamily || "Arial";
  const fontWeight = props.fontWeight || "normal";
  const fontStyle = props.fontStyle || "normal";
  // ✅ NEW: Ajouter du padding
  const padding = props.padding || 12;
  // Propriétés de police pour l'en-tête
  const headerFontSize = props.headerFontSize || fontSize + 2;
  const headerFontFamily = props.headerFontFamily || fontFamily;
  const headerFontWeight = props.headerFontWeight || fontWeight;
  const headerFontStyle = props.headerFontStyle || fontStyle;
  // Propriétés de police pour le corps du texte
  const bodyFontSize = props.bodyFontSize || fontSize;
  const bodyFontFamily = props.bodyFontFamily || fontFamily;
  const bodyFontWeight = props.bodyFontWeight || fontWeight;
  const bodyFontStyle = props.bodyFontStyle || fontStyle;
  const layout = props.layout || "vertical";
  const showHeaders = props.showHeaders !== false;
  const showBorders = props.showBorders !== false;
  const showFullName = props.showFullName !== false;
  const showAddress = props.showAddress !== false;
  const showEmail = props.showEmail !== false;
  const showPhone = props.showPhone !== false;
  const showPaymentMethod = props.showPaymentMethod !== false;
  const showTransactionId = props.showTransactionId !== false;
  // Alignement vertical
  const verticalAlign = props.verticalAlign || "top";

  // Fond
  if (props.showBackground !== false) {
    ctx.fillStyle = normalizeColor(props.backgroundColor || "#ffffff");
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordures
  if (showBorders) {
    ctx.strokeStyle = normalizeColor(props.borderColor || "#e5e7eb");
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = normalizeColor(props.textColor || "#000000");
  ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
  ctx.textAlign = "left";

  // Construire le contenu et calculer la hauteur totale
  let customerData: {
    name: string;
    address: string;
    email: string;
    phone: string;
  };

  if (state.previewMode === "command") {
    customerData = wooCommerceManager.getCustomerInfo();
  } else {
    customerData = {
      name: "Marie Dupont",
      address: "15 rue des Lilas, 75001 Paris",
      email: "marie.dupont@email.com",
      phone: "+33 6 12 34 56 78",
    };
  }

  // Construire les lignes de texte selon le layout
  const lines: string[] = [];
  
  if (layout === "vertical") {
    if (showFullName) {
      lines.push(customerData.name);
    }
    if (showAddress) {
      lines.push(customerData.address);
    }
    if (showEmail) {
      lines.push(customerData.email);
    }
    if (showPhone) {
      lines.push(customerData.phone);
    }
    if (showPaymentMethod) {
      lines.push("Paiement: Carte bancaire");
    }
    if (showTransactionId) {
      lines.push("ID: TXN123456789");
    }
  } else if (layout === "horizontal") {
    let line1 = "";
    let line2 = "";
    if (showFullName) line1 += customerData.name;
    if (showEmail) line1 += (line1 ? " - " : "") + customerData.email;
    if (showPhone) line2 = customerData.phone;
    if (line1) lines.push(line1);
    if (line2) lines.push(line2);
  } else if (layout === "compact") {
    let compactText = "";
    if (showFullName) compactText += customerData.name;
    if (showAddress)
      compactText +=
        (compactText ? " • " : "") + customerData.address.split(",")[0];
    if (showEmail)
      compactText += (compactText ? " • " : "") + customerData.email;
    if (showPhone)
      compactText += (compactText ? " • " : "") + customerData.phone;
    
    // Wrap text if too long
    const maxWidth = element.width - (padding * 2);
    const words = compactText.split(" ");
    let line = "";
    for (let i = 0; i < words.length; i++) {
      const testLine = line + (line ? " " : "") + words[i];
      const metrics = ctx.measureText(testLine);
      if (metrics.width > maxWidth && i > 0) {
        lines.push(line);
        line = words[i] + " ";
      } else {
        line = testLine;
      }
    }
    if (line.trim()) lines.push(line);
  }

  // Calculer la hauteur du contenu
  const headerHeight = showHeaders ? 25 : 0;
  const contentHeight = lines.length * 18; // 18px par ligne
  const totalContentHeight = headerHeight + contentHeight;
  
  // Calculer l'offset Y selon l'alignement vertical
  let startY: number;
  switch (verticalAlign) {
    case "middle":
      startY = Math.max(padding, (element.height - totalContentHeight) / 2);
      break;
    case "bottom":
      startY = Math.max(padding, element.height - totalContentHeight - padding);
      break;
    default: // top
      startY = padding;
  }

  let y = startY;

  // En-tête
  if (showHeaders) {
    ctx.fillStyle = normalizeColor(props.headerTextColor || "#111827");
    ctx.fillText("Informations Client", padding, y);
    y += 25;
    ctx.fillStyle = normalizeColor(props.textColor || "#000000");
  }

  ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;

  // Dessiner les lignes
  lines.forEach((lineText) => {
    ctx.fillText(lineText, padding, y);
    y += 18;
  });
};

// Constantes pour les thèmes company_info
const COMPANY_THEMES = {
  corporate: {
    backgroundColor: "#ffffff",
    borderColor: "#1f2937",
    textColor: "#374151",
    headerTextColor: "#111827",
  },
  modern: {
    backgroundColor: "#ffffff",
    borderColor: "#3b82f6",
    textColor: "#1e40af",
    headerTextColor: "#1e3a8a",
  },
  elegant: {
    backgroundColor: "#ffffff",
    borderColor: "#8b5cf6",
    textColor: "#6d28d9",
    headerTextColor: "#581c87",
  },
  minimal: {
    backgroundColor: "#ffffff",
    borderColor: "#e5e7eb",
    textColor: "#374151",
    headerTextColor: "#111827",
  },
  professional: {
    backgroundColor: "#ffffff",
    borderColor: "#059669",
    textColor: "#047857",
    headerTextColor: "#064e3b",
  },
} as const;

// Fonction helper pour récupérer les données d'entreprise
const getCompanyData = (props: CompanyInfoElementProperties) => {
  const baseData = {
    name: props.companyName || "",
    address: props.companyAddress || "",
    city: props.companyCity || "",
    siret: props.companySiret || "",
    tva: props.companyTva || "",
    rcs: props.companyRcs || "",
    capital: props.companyCapital || "",
    email: props.companyEmail || "",
    phone: props.companyPhone || "",
  };

  // Remplacer par les données dynamiques du plugin si disponibles
  const pluginCompany = (window as any).pdfBuilderData?.company;
  if (pluginCompany) {
    Object.keys(baseData).forEach(key => {
      if (pluginCompany[key] && pluginCompany[key] !== 'Non indiqué') {
        (baseData as any)[key] = pluginCompany[key];
      }
    });
  }

  return baseData;
};

// Fonction pour générer le texte formaté des informations d'entreprise
const generateCompanyInfoText = (props: CompanyInfoElementProperties): string => {
  const companyData = getCompanyData(props);

  // Configuration d'affichage
  const displayConfig = {
    companyName: props.showCompanyName !== false,
    address: props.showAddress !== false,
    phone: props.showPhone !== false,
    email: props.showEmail !== false,
    siret: props.showSiret !== false,
    vat: props.showVat !== false,
    rcs: props.showRcs !== false,
    capital: props.showCapital !== false,
  };

  const lines: string[] = [];

  // Ajouter le nom de l'entreprise
  if (shouldDisplayValue(companyData.name, displayConfig.companyName)) {
    lines.push(companyData.name);
  }

  // Ajouter l'adresse
  if (shouldDisplayValue(companyData.address, displayConfig.address)) {
    lines.push(companyData.address);
    if (shouldDisplayValue(companyData.city, displayConfig.address)) {
      lines.push(companyData.city);
    }
  }

  // Ajouter les autres informations
  const infoFields = [
    [companyData.siret, displayConfig.siret, 'SIRET'],
    [companyData.tva, displayConfig.vat, 'TVA'],
    [companyData.rcs, displayConfig.rcs, 'RCS'],
    [companyData.capital, displayConfig.capital, 'Capital'],
    [companyData.email, displayConfig.email, 'Email'],
    [companyData.phone, displayConfig.phone, 'Téléphone'],
  ];

  infoFields.forEach(([value, show, label]) => {
    if (shouldDisplayValue(value as string, show as boolean)) {
      lines.push(`${value}`);
    }
  });

  return lines.join('\n');
};

// Fonction helper pour vérifier si une valeur doit être affichée
const shouldDisplayValue = (value: string, showFlag: boolean) =>
  showFlag && value && value !== 'Non indiqué';

// Fonction helper pour dessiner une ligne de texte company_info
const drawCompanyLine = (
  ctx: CanvasRenderingContext2D,
  text: string,
  x: number,
  y: number,
  fontSize: number
) => {
  ctx.fillText(text, x, y);
  return y + Math.max(fontSize * 1.2, fontSize + 4); // Espacement minimum de 1.2x la taille de police ou taille de police + 4px
};

const drawCompanyInfo = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as CompanyInfoElement;

  // Configuration des polices
  const fontSize = props.fontSize || 12;
  const fontConfig = {
    family: props.fontFamily || "Arial",
    weight: normalizeFontWeight(props.fontWeight || "normal"),
    style: props.fontStyle || "normal",
    headerSize: props.headerFontSize || 14,
    headerFamily: props.headerFontFamily || props.fontFamily || "Arial",
    headerWeight: normalizeFontWeight(props.headerFontWeight || "bold"),
    headerStyle: props.headerFontStyle || "normal",
    bodySize: props.bodyFontSize || 12,
    bodyFamily: props.bodyFontFamily || props.fontFamily || "Arial",
    bodyWeight: normalizeFontWeight(props.bodyFontWeight || "normal"),
    bodyStyle: props.bodyFontStyle || "normal",
  };

  // Configuration d'affichage
  const displayConfig = {
    background: props.showBackground !== false,
    borders: props.showBorders !== false,
    companyName: props.showCompanyName !== false,
    address: props.showAddress !== false,
    phone: props.showPhone !== false,
    email: props.showEmail !== false,
    siret: props.showSiret !== false,
    vat: props.showVat !== false,
    rcs: props.showRcs !== false,
    capital: props.showCapital !== false,
  };

  // Thème et couleurs
  const theme = COMPANY_THEMES[(props.theme || "corporate") as keyof typeof COMPANY_THEMES] || COMPANY_THEMES.corporate;
  const colors = {
    background: normalizeColor(props.backgroundColor || theme.backgroundColor),
    border: normalizeColor(props.borderColor || theme.borderColor),
    text: normalizeColor(props.textColor || theme.textColor),
    headerText: normalizeColor(props.headerTextColor || theme.headerTextColor),
  };

  // Appliquer le fond et les bordures
  if (displayConfig.background) {
    ctx.fillStyle = colors.background;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  if (displayConfig.borders) {
    ctx.strokeStyle = colors.border;
    ctx.lineWidth = props.borderWidth || 1;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  // Configuration du contexte
  ctx.fillStyle = colors.text;
  ctx.textAlign = "left";

  // Position de départ
  let x = 10;
  let y = 20;

  // Récupération des données d'entreprise
  const companyData = getCompanyData(props);

  // Fonction helper pour dessiner avec la police appropriée
  const drawWithFont = (text: string, useHeaderFont: boolean = false) => {
    const config = useHeaderFont
      ? { size: fontConfig.headerSize, weight: fontConfig.headerWeight, style: fontConfig.headerStyle, family: fontConfig.headerFamily }
      : { size: fontConfig.bodySize, weight: fontConfig.bodyWeight, style: fontConfig.bodyStyle, family: fontConfig.bodyFamily };

    ctx.font = `${config.style} ${config.weight} ${config.size}px ${config.family}`;
    if (useHeaderFont) ctx.fillStyle = colors.headerText;
    y = drawCompanyLine(ctx, text, x, y, config.size);
    if (useHeaderFont) ctx.fillStyle = colors.text;
  };

  // Appliquer la police du corps par défaut
  ctx.font = `${fontConfig.bodyStyle} ${fontConfig.bodyWeight} ${fontConfig.bodySize}px ${fontConfig.bodyFamily}`;

  // Afficher les éléments selon la configuration
  if (shouldDisplayValue(companyData.name, displayConfig.companyName)) {
    drawWithFont(companyData.name, true);
  }

  if (shouldDisplayValue(companyData.address, displayConfig.address)) {
    // Appliquer la police du body pour l'adresse
    ctx.font = `${fontConfig.bodyStyle} ${fontConfig.bodyWeight} ${fontConfig.bodySize}px ${fontConfig.bodyFamily}`;
    y = drawCompanyLine(ctx, companyData.address, x, y, fontConfig.bodySize);
    if (shouldDisplayValue(companyData.city, displayConfig.address)) {
      y = drawCompanyLine(ctx, companyData.city, x, y, fontConfig.bodySize);
    }
  }

  // Appliquer les informations avec la police du body
  [
    [companyData.siret, displayConfig.siret],
    [companyData.tva, displayConfig.vat],
    [companyData.rcs, displayConfig.rcs],
    [companyData.capital, displayConfig.capital],
    [companyData.email, displayConfig.email],
    [companyData.phone, displayConfig.phone],
  ].forEach(([value, show]) => {
    if (shouldDisplayValue(value as string, show as boolean)) {
      ctx.font = `${fontConfig.bodyStyle} ${fontConfig.bodyWeight} ${fontConfig.bodySize}px ${fontConfig.bodyFamily}`;
      y = drawCompanyLine(ctx, value as string, x, y, fontConfig.bodySize);
    }
  });
};

const drawOrderNumber = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as OrderNumberElement;

  const fontSize = props.fontSize || 14;
  const fontFamily = props.fontFamily || "Arial";
  const fontWeight = props.fontWeight || "normal";
  const fontStyle = props.fontStyle || "normal";
  // Propriétés de police pour le label
  const labelFontSize = props.headerFontSize || fontSize;
  const labelFontFamily = props.headerFontFamily || fontFamily;
  const labelFontWeight = props.headerFontWeight || "bold";
  const labelFontStyle = props.headerFontStyle || fontStyle;
  // Propriétés de police pour le numéro
  const numberFontSize = props.numberFontSize || fontSize;
  const numberFontFamily = props.numberFontFamily || fontFamily;
  const numberFontWeight = props.numberFontWeight || fontWeight;
  const numberFontStyle = props.numberFontStyle || fontStyle;
  // Propriétés de police pour la date
  const dateFontSize = props.dateFontSize || fontSize - 2;
  const dateFontFamily = props.dateFontFamily || fontFamily;
  const dateFontWeight = props.dateFontWeight || fontWeight;
  const dateFontStyle = props.dateFontStyle || fontStyle;
  // const textAlign = props.textAlign || 'left'; // left, center, right
  // Propriétés d'alignement spécifiques
  // const labelTextAlign = props.labelTextAlign || textAlign;
  // const numberTextAlign = props.numberTextAlign || textAlign;
  // const dateTextAlign = props.dateTextAlign || textAlign;
  const contentAlign = props.contentAlign || "left"; // Alignement général du contenu dans l'élément
  const showLabel = props.showLabel !== false; // Par défaut true
  const showDate = props.showDate !== false; // Par défaut true
  const labelPosition = props.labelPosition || "above"; // above, left, right, below
  const labelText = props.labelText || "N° de commande:"; // Texte personnalisable du libellé

  // Fonction helper pour calculer la position X selon l'alignement général du contenu
  // const calculateContentX = (align: string) => {
  //   if (align === 'left') {
  //     return 10;
  //   } else if (align === 'center') {
  //     return element.width / 2;
  //   } else { // right
  //     return element.width - 10;
  //   }
  // };

  // Fonction helper pour calculer la position X selon l'alignement du texte
  // const calculateX = (align: string) => {
  //   if (align === 'left') {
  //     return 10;
  //   } else if (align === 'center') {
  //     return element.width / 2;
  //   } else { // right
  //     return element.width - 10;
  //   }
  // };

  // Appliquer le fond seulement si showBackground est activé
  if (props.showBackground !== false) {
    ctx.fillStyle = normalizeColor(props.backgroundColor || "#e5e7eb");
    ctx.fillRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = normalizeColor("#000000");

  // Numéro de commande et date fictifs ou réels selon le mode
  let orderNumber: string;
  let orderDate: string;

  if (state.previewMode === "command") {
    orderNumber = wooCommerceManager.getOrderNumber();
    orderDate = wooCommerceManager.getOrderDate();
  } else {
    // Utiliser les données WooCommerce si disponibles, sinon valeurs par défaut
    orderNumber = wooCommerceManager.getOrderNumber() || "CMD-2024-01234";
    orderDate = wooCommerceManager.getOrderDate() || "27/10/2024";
  }

  // Calculer la position Y initiale selon l'alignement vertical
  let y = calculateTextY(element, props.verticalAlign, fontSize, 10);

  // Calculer la largeur totale du contenu pour l'alignement général
  let totalContentWidth = 0;
  if (showLabel) {
    if (labelPosition === "above" || labelPosition === "below") {
      // Pour les positions verticales, prendre la largeur maximale
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      const labelWidth = ctx.measureText(labelText).width;
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      const numberWidth = ctx.measureText(orderNumber).width;
      totalContentWidth = Math.max(labelWidth, numberWidth);
    } else {
      // Pour les positions latérales, calculer la largeur combinée
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      const labelWidth = ctx.measureText(labelText).width;
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      const numberWidth = ctx.measureText(orderNumber).width;
      totalContentWidth = labelWidth + numberWidth + 15; // 15px d'espace
    }
  } else {
    // Juste le numéro
    ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
    totalContentWidth = ctx.measureText(orderNumber).width;
  }

  // Calculer le décalage pour l'alignement général du contenu
  let contentOffsetX = 0;
  if (contentAlign === "center") {
    contentOffsetX = (element.width - totalContentWidth) / 2 - 10; // -10 car on commence à 10
  } else if (contentAlign === "right") {
    contentOffsetX = element.width - totalContentWidth - 20; // -20 pour les marges
  }

  if (showLabel) {
    if (labelPosition === "above") {
      // Libellé au-dessus, numéro en-dessous - utiliser l'alignement général du contenu
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      // Appliquer textBaseline selon verticalAlign
      ctx.textBaseline = props.verticalAlign === "middle" ? "middle" : props.verticalAlign === "bottom" ? "bottom" : "top";
      const labelX =
        contentAlign === "left"
          ? 10 + contentOffsetX
          : contentAlign === "center"
          ? element.width / 2
          : element.width - 10;
      ctx.fillText(labelText, labelX, y);
      y += 18;
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      const numberX =
        contentAlign === "left"
          ? 10 + contentOffsetX
          : contentAlign === "center"
          ? element.width / 2
          : element.width - 10;
      ctx.fillText(orderNumber, numberX, y);
    } else if (labelPosition === "below") {
      // Numéro au-dessus, libellé en-dessous - utiliser l'alignement général du contenu
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      ctx.textBaseline = props.verticalAlign === "middle" ? "middle" : props.verticalAlign === "bottom" ? "bottom" : "top";
      const numberX =
        contentAlign === "left"
          ? 10 + contentOffsetX
          : contentAlign === "center"
          ? element.width / 2
          : element.width - 10;
      ctx.fillText(orderNumber, numberX, y);
      y += 18;
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = contentAlign as CanvasTextAlign;
      const labelX =
        contentAlign === "left"
          ? 10 + contentOffsetX
          : contentAlign === "center"
          ? element.width / 2
          : element.width - 10;
      ctx.fillText(labelText, labelX, y);
    } else if (labelPosition === "left") {
      // Libellé à gauche, numéro à droite - avec espacement optimal et alignement général
      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = "left" as CanvasTextAlign;
      ctx.textBaseline = props.verticalAlign === "middle" ? "middle" : props.verticalAlign === "bottom" ? "bottom" : "top";
      const labelX = 10 + contentOffsetX;
      ctx.fillText(labelText, labelX, y);

      // Calculer l'espace disponible pour centrer le numéro ou l'aligner intelligemment
      const labelWidth = ctx.measureText(labelText).width;
      const numberX = labelX + labelWidth + 15; // 15px d'espace après le libellé

      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = "left" as CanvasTextAlign;
      ctx.fillText(orderNumber, numberX, y);
    } else if (labelPosition === "right") {
      // Numéro à gauche, libellé à droite - avec espacement optimal et alignement général
      ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
      ctx.textAlign = "left" as CanvasTextAlign;
      ctx.textBaseline = props.verticalAlign === "middle" ? "middle" : props.verticalAlign === "bottom" ? "bottom" : "top";
      const numberX = 10 + contentOffsetX;
      ctx.fillText(orderNumber, numberX, y);

      // Calculer la position du libellé après le numéro
      const numberWidth = ctx.measureText(orderNumber).width;
      const labelX = numberX + numberWidth + 15; // 15px d'espace après le numéro

      ctx.font = `${labelFontStyle} ${labelFontWeight} ${labelFontSize}px ${labelFontFamily}`;
      ctx.textAlign = "left" as CanvasTextAlign;
      ctx.fillText(labelText, labelX, y);
    }
  } else {
    // Pas de libellé, juste le numéro avec alignement général du contenu
    ctx.font = `${numberFontStyle} ${numberFontWeight} ${numberFontSize}px ${numberFontFamily}`;
    ctx.textAlign = contentAlign as CanvasTextAlign;
    ctx.textBaseline = props.verticalAlign === "middle" ? "middle" : props.verticalAlign === "bottom" ? "bottom" : "top";
    // Pour le cas sans libellé, utiliser directement calculateContentX sans contentOffsetX
    // car contentOffsetX est calculé pour centrer le contenu total, mais ici on n'a que le numéro
    if (contentAlign === "left") {
      ctx.fillText(orderNumber, 10, y);
    } else if (contentAlign === "center") {
      ctx.fillText(orderNumber, element.width / 2, y);
    } else {
      // right
      ctx.fillText(orderNumber, element.width - 10, y);
    }
  }

  // Afficher la date sur une nouvelle ligne avec le même alignement général
  if (showDate) {
    ctx.font = `${dateFontStyle} ${dateFontWeight} ${dateFontSize}px ${dateFontFamily}`;
    ctx.textAlign = contentAlign as CanvasTextAlign;
    ctx.textBaseline = "top"; // Garder top pour la date
    // Pour la date, utiliser directement calculateContentX sans contentOffsetX
    // car contentOffsetX est calculé pour centrer le contenu total
    if (contentAlign === "left") {
      ctx.fillText(`Date: ${orderDate}`, 10, y + 20);
    } else if (contentAlign === "center") {
      ctx.fillText(`Date: ${orderDate}`, element.width / 2, y + 20);
    } else {
      // right
      ctx.fillText(`Date: ${orderDate}`, element.width - 10, y + 20);
    }
  }
};

const drawWoocommerceOrderDate = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as WoocommerceOrderDateElement;
  const fontConfig = createFontConfig(props, 12);
  const colorConfig = createColorConfig(props);
  const padding = getPadding(props);

  // Appliquer le fond
  if (props.showBackground !== false) {
    ctx.fillStyle = colorConfig.background;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Appliquer la bordure
  applyBorder(ctx, element, props.border);

  // Récupérer et formater la date
  const orderDate = state.previewMode === "command"
    ? wooCommerceManager.getOrderDate()
    : wooCommerceManager.getOrderDate() || "27/10/2024";

  const displayDate = formatOrderDate(orderDate, props.dateFormat, props.showTime);

  // Configurer le contexte et afficher
  setupRenderContext(ctx, fontConfig, colorConfig, props.textAlign, props.verticalAlign);
  const x = calculateTextX(element, props.textAlign, padding);
  const y = calculateTextYWithPadding(element, props.verticalAlign, padding);
  ctx.fillText(displayDate, x, y);
};

// Fonction helper pour formater les dates de commande
const formatOrderDate = (dateString: string, format: string = "d/m/Y", showTime: boolean = false): string => {
  try {
    const dateObj = new Date(dateString);
    if (isNaN(dateObj.getTime())) return dateString;

    const day = String(dateObj.getDate()).padStart(2, "0");
    const month = String(dateObj.getMonth() + 1).padStart(2, "0");
    const year = dateObj.getFullYear();

    let formattedDate: string;
    switch (format) {
      case "m/d/Y": formattedDate = `${month}/${day}/${year}`; break;
      case "Y-m-d": formattedDate = `${year}-${month}-${day}`; break;
      case "d-m-Y": formattedDate = `${day}-${month}-${year}`; break;
      case "d.m.Y": formattedDate = `${day}.${month}.${year}`; break;
      default: formattedDate = `${day}/${month}/${year}`;
    }

    if (showTime) {
      const hours = String(dateObj.getHours()).padStart(2, "0");
      const minutes = String(dateObj.getMinutes()).padStart(2, "0");
      formattedDate += ` ${hours}:${minutes}`;
    }

    return formattedDate;
  } catch {
    return dateString;
  }
};

const drawWoocommerceInvoiceNumber = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as WoocommerceInvoiceNumberElement;
  const fontConfig = createFontConfig(props, 12);
  const colorConfig = createColorConfig(props);
  const padding = getPadding(props);

  // Appliquer le fond
  if (props.showBackground !== false) {
    ctx.fillStyle = colorConfig.background;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Appliquer la bordure
  applyBorder(ctx, element, props.border);

  // Récupérer et formater le numéro de facture
  const invoiceNumber = wooCommerceManager.getInvoiceNumber?.() || "INV-2024-00001";
  const displayText = `${props.prefix || ""}${invoiceNumber}${props.suffix || ""}`;

  // Configurer le contexte et afficher
  setupRenderContext(ctx, fontConfig, colorConfig, props.textAlign, props.verticalAlign);
  const x = calculateTextX(element, props.textAlign, padding);
  const y = calculateTextYWithPadding(element, props.verticalAlign, padding);
  ctx.fillText(displayText, x, y);
};

const drawDocumentType = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as DocumentTypeElement;
  const fontConfig = createFontConfig(props, 18);
  fontConfig.weight = props.fontWeight || "bold"; // Override default weight

  const colorConfig = createColorConfig(props);

  // Appliquer le fond
  if (props.showBackground !== false) {
    ctx.fillStyle = normalizeColor(props.backgroundColor || "#e5e7eb");
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Configurer le contexte
  setupRenderContext(ctx, fontConfig, colorConfig, props.textAlign, props.verticalAlign);

  // Déterminer le type de document
  const documentType = props.documentType || "FACTURE";

  // Mapping des types techniques vers les labels lisibles
  const DOCUMENT_TYPE_LABELS = {
    FACTURE: "FACTURE",
    DEVIS: "DEVIS",
    BON_COMMANDE: "BON DE COMMANDE",
    AVOIR: "AVOIR",
    RELEVE: "RELEVE",
    CONTRAT: "CONTRAT",
  } as const;

  const displayText = DOCUMENT_TYPE_LABELS[documentType as keyof typeof DOCUMENT_TYPE_LABELS] || documentType;

  // Calculer la position X selon l'alignement horizontal
  const x = props.textAlign === "center"
    ? element.width / 2
    : props.textAlign === "right"
    ? element.width - 10
    : 10;

  // Calculer la position Y selon l'alignement vertical
  const y = calculateTextY(element, props.verticalAlign, fontConfig.size, 5);

  ctx.fillText(displayText, x, y);
};

interface CanvasProps {
  width: number;
  height: number;
  className?: string;
}

// Flag global pour afficher les logs détaillés des éléments (debug)
// Debug flags - set to true to enable verbose logging

// Constantes pour le cache des images
const MAX_CACHE_ITEMS = 100; // Max 100 images in cache

export const Canvas = function Canvas({
  width,
  height,
  className,
}: CanvasProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const canvasWrapperRef = useRef<HTMLDivElement>(null);

  // ✅ Track derniers éléments rendus pour éviter double rendu
  const lastRenderedElementsRef = useRef<string>("");
  const renderCountRef = useRef<number>(0);

  const { state, dispatch } = useBuilder();
  const canvasSettings = useCanvasSettings();

  debugLog("🎨 Canvas: Component initialized with props:", {
    width,
    height,
    className,
  });
  debugLog("📊 Canvas: Initial state:", {
    elements: state.elements.length,
    selection: state.selection.selectedElements.length,
    zoom: state.canvas.zoom,
  });

  debugLog(
    `[Canvas] Component initialized - Dimensions: ${width}x${height}, Settings loaded: ${!!canvasSettings}`
  );

  // Force re-render when canvas settings change (commenté pour éviter les boucles)
  // const [, forceUpdate] = useState({});
  // useEffect(() => {
  //   forceUpdate({});
  // }, [canvasSettings.canvasBackgroundColor, canvasSettings.borderColor, canvasSettings.borderWidth, canvasSettings.shadowEnabled, canvasSettings.containerBackgroundColor]);

  // État pour le menu contextuel
  const [contextMenu, setContextMenu] = useState<{
    isVisible: boolean;
    position: { x: number; y: number };
    elementId?: string;
  }>({
    isVisible: false,
    position: { x: 0, y: 0 },
  });

  // ✅ STATE for image loading - force redraw when images load
  const [imageLoadCount, setImageLoadCount] = useState(0);

  // Récupérer la limite mémoire JavaScript depuis les paramètres
  const memoryLimitJs = useCanvasSetting("memory_limit_js", 256) as number; // En MB, défaut 256MB

  // ✅ LAZY LOADING: Récupérer le paramètre depuis les settings
  const lazyLoadingEnabled = canvasSettings.lazyLoadingEditor;

  // ✅ LAZY LOADING: État pour tracker les éléments visibles
  const [visibleElements, setVisibleElements] = useState<Set<string>>(
    new Set()
  );
  const [viewportBounds, setViewportBounds] = useState({
    x: 0,
    y: 0,
    width: width,
    height: height,
  });

  // ✅ LAZY LOADING: Fonction pour déterminer si un élément est visible dans le viewport
  const isElementVisible = useCallback(
    (
      element: Element,
      viewport: { x: number; y: number; width: number; height: number }
    ): boolean => {
      // Calculer les bounds de l'élément (simplifié - on pourrait améliorer avec rotation, etc.)
      const elementBounds = {
        x: element.x,
        y: element.y,
        width: element.width,
        height: element.height,
      };

      // Vérifier si l'élément intersecte le viewport (avec une marge de 100px)
      const margin = 100;
      return !(
        elementBounds.x + elementBounds.width < viewportBounds.x - margin ||
        elementBounds.x > viewportBounds.x + viewportBounds.width + margin ||
        elementBounds.y + elementBounds.height < viewportBounds.y - margin ||
        elementBounds.y > viewportBounds.y + viewportBounds.height + margin
      );
    },
    [viewportBounds]
  ); // ✅ LAZY LOADING: Filtrer les éléments visibles
  const visibleElementsList = useMemo(() => {
    if (!lazyLoadingEnabled) {
      return state.elements; // Tous les éléments si lazy loading désactivé
    }

    // Toujours inclure les 5 premiers éléments pour éviter les sauts visuels
    const alwaysVisible = state.elements.slice(0, 5);
    const potentiallyVisible = state.elements
      .slice(5)
      .filter((element) => isElementVisible(element, viewportBounds));

    return [...alwaysVisible, ...potentiallyVisible];
  }, [state.elements, lazyLoadingEnabled, viewportBounds, isElementVisible]);

  // Cache pour les images chargées avec métadonnées de mémoire
  const imageCache = useRef<
    Map<string, { image: HTMLImageElement; size: number; lastUsed: number }>
  >(new Map());

  // ✅ LAZY LOADING: Hook pour mettre à jour le viewport quand le canvas change
  useEffect(() => {
    if (!canvasRef.current) return;

    const updateViewport = () => {
      const canvas = canvasRef.current;
      if (!canvas) return;

      const rect = canvas.getBoundingClientRect();
      setViewportBounds({
        x: -rect.left,
        y: -rect.top,
        width: window.innerWidth,
        height: window.innerHeight,
      });
    };

    // Mettre à jour initialement
    updateViewport();

    // Écouter les changements de scroll et resize avec passive: true
    window.addEventListener("scroll", updateViewport, { passive: true });
    window.addEventListener("resize", updateViewport, { passive: true });

    return () => {
      window.removeEventListener("scroll", updateViewport);
      window.removeEventListener("resize", updateViewport);
    };
  }, []);

  // ✅ CORRECTION 7: Tracker les URLs rendues pour détecter changements
  const renderedLogoUrlsRef = useRef<Map<string, string>>(new Map()); // elementId -> logoUrl

  // ✅ Flag: Track if we've done initial render check for images
  const initialImageCheckDoneRef = useRef(false);

  // Fonction pour estimer la taille mémoire d'une image (approximation)
  const estimateImageMemorySize = useCallback(
    (img: HTMLImageElement): number => {
      // Estimation basée sur : largeur * hauteur * 4 octets (RGBA) + overhead
      const pixelData = img.naturalWidth * img.naturalHeight * 4;
      const overhead = 1024; // Overhead approximatif par image
      return pixelData + overhead;
    },
    []
  );

  // Fonction pour calculer l'usage mémoire total du cache
  const calculateCacheMemoryUsage = useCallback((): number => {
    let totalSize = 0;
    for (const [, data] of imageCache.current) {
      totalSize += data.size;
    }
    return totalSize / (1024 * 1024); // Convertir en MB
  }, []);

  // Fonction pour vérifier si la limite mémoire est dépassée
  const isMemoryLimitExceeded = useCallback((): boolean => {
    const currentUsage = calculateCacheMemoryUsage();
    const limit = memoryLimitJs;

    // Vérifier aussi la mémoire globale du navigateur si disponible
    if ("memory" in performance) {
      const perfMemory = performance.memory!;
      const browserMemoryUsage = perfMemory.usedJSHeapSize / (1024 * 1024); // MB
      const browserLimit = perfMemory.jsHeapSizeLimit / (1024 * 1024); // MB

      // Si le navigateur approche sa limite, être plus agressif
      if (browserMemoryUsage > browserLimit * 0.8) {
        debugWarn(
          `[Canvas Memory] Browser memory usage high: ${browserMemoryUsage.toFixed(
            1
          )}MB / ${browserLimit.toFixed(1)}MB`
        );
        return true;
      }
    }

    return currentUsage > limit * 0.8; // Déclencher le nettoyage à 80% de la limite
  }, [calculateCacheMemoryUsage, memoryLimitJs]);

  // ✅ CORRECTION 2: Fonction pour nettoyer le cache des images avec gestion mémoire
  const cleanupImageCache = useCallback(() => {
    const cache = imageCache.current;
    const currentMemoryUsage = calculateCacheMemoryUsage();
    const memoryLimit = memoryLimitJs;

    debugLog(
      `[Canvas Memory] Starting cache cleanup - Current usage: ${currentMemoryUsage.toFixed(
        2
      )}MB, Limit: ${memoryLimit}MB, Items: ${cache.size}`
    );

    // Nettoyer si limite dépassée ou trop d'éléments
    if (isMemoryLimitExceeded() || cache.size > MAX_CACHE_ITEMS) {
      // Trier par date d'utilisation (LRU - Least Recently Used)
      const entries = Array.from(cache.entries()).sort(
        ([, a], [, b]) => a.lastUsed - b.lastUsed
      );

      // Calculer combien supprimer pour revenir sous 70% de la limite
      const targetMemoryUsage = memoryLimit * 0.7;
      let memoryToFree = Math.max(0, currentMemoryUsage - targetMemoryUsage);
      let itemsToRemove = Math.min(20, Math.ceil(cache.size * 0.2)); // Au moins 20% des éléments ou 20 éléments max

      let removed = 0;
      let memoryFreed = 0;

      for (const [url, data] of entries) {
        if (removed >= itemsToRemove && memoryFreed >= memoryToFree) break;

        cache.delete(url);
        memoryFreed += data.size / (1024 * 1024); // MB
        removed++;

        debugLog(
          `[Canvas Memory] Removed image from cache: ${url
            .split("/")
            .pop()}, Freed: ${(data.size / (1024 * 1024)).toFixed(2)}MB`
        );
      }

      debugLog(
        `[Canvas Memory] Cache cleanup completed - Removed ${removed} items, Freed ${memoryFreed.toFixed(
          2
        )}MB, New usage: ${(currentMemoryUsage - memoryFreed).toFixed(2)}MB`
      );
    } else {
      debugLog(
        `[Canvas Memory] Cache cleanup not needed - Usage within limits`
      );
    }
  }, [calculateCacheMemoryUsage, memoryLimitJs, isMemoryLimitExceeded]);

  // Fonction pour forcer un nettoyage manuel (utile pour le débogage)
  const forceCacheCleanup = useCallback(() => {
    cleanupImageCache();
  }, [cleanupImageCache]);

  // Exposer les fonctions de gestion mémoire globalement pour le débogage
  useEffect(() => {
    (window as any).canvasMemoryDebug = {
      getCacheStats: () => ({
        itemCount: imageCache.current.size,
        memoryUsage: calculateCacheMemoryUsage(),
        memoryLimit: memoryLimitJs,
        items: Array.from(imageCache.current.entries()).map(([url, data]) => ({
          url: url.split("/").pop(),
          size: (data.size / (1024 * 1024)).toFixed(2) + "MB",
          lastUsed: new Date(data.lastUsed).toLocaleTimeString(),
        })),
      }),
      forceCleanup: forceCacheCleanup,
      getBrowserMemory: () => {
        if ("memory" in performance) {
          const perfMemory = (performance as any).memory;
          return {
            used: (perfMemory.usedJSHeapSize / (1024 * 1024)).toFixed(1) + "MB",
            total:
              (perfMemory.totalJSHeapSize / (1024 * 1024)).toFixed(1) + "MB",
            limit:
              (perfMemory.jsHeapSizeLimit / (1024 * 1024)).toFixed(1) + "MB",
          };
        }
        return { error: "Performance.memory not available" };
      },
    };

    return () => {
      delete (window as any).canvasMemoryDebug;
    };
  }, [calculateCacheMemoryUsage, memoryLimitJs, forceCacheCleanup]); // Surveillance périodique de la mémoire globale du navigateur
  useEffect(() => {
    const memoryCheckInterval = setInterval(() => {
      if ("memory" in performance) {
        const perfMemory = (performance as any).memory;
        const browserMemoryUsage = perfMemory.usedJSHeapSize / (1024 * 1024); // MB
        const browserLimit = perfMemory.jsHeapSizeLimit / (1024 * 1024); // MB
        const cacheMemoryUsage = calculateCacheMemoryUsage();

        // Log détaillé de la mémoire si activé
        if (canvasSettings.debugMode) {
        }

        // Nettoyage d'urgence si mémoire critique
        if (browserMemoryUsage > browserLimit * 0.9) {
          debugWarn(
            `[Canvas Memory] Critical memory usage! Forcing cache cleanup...`
          );
          cleanupImageCache();
        }
      }
    }, 10000); // Vérification toutes les 10 secondes

    return () => clearInterval(memoryCheckInterval);
  }, [
    calculateCacheMemoryUsage,
    memoryLimitJs,
    cleanupImageCache,
    canvasSettings.debugMode,
  ]);

  // Utiliser les hooks pour les interactions
  const {
    handleDrop,
    handleDragOver,
    handleDragLeave,
    handleDragEnter,
    isDragOver,
  } = useCanvasDrop({
    canvasRef: canvasWrapperRef,
    canvasWidth: width,
    canvasHeight: height,
    elements: state.elements || [],
    dragEnabled: true,
  });

  const {
    handleCanvasClick,
    handleMouseDown,
    handleMouseMove,
    handleMouseUp,
    handleContextMenu,
    selectionState,
  } = useCanvasInteraction({
    canvasRef,
    canvasWidth: width,
    canvasHeight: height,
  });

  // Hook pour les raccourcis clavier
  const keyboardShortcutInfo = useKeyboardShortcuts();

  // Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode

  // Fonction helper pour dessiner un placeholder de logo
  const drawLogoPlaceholder = useCallback(
    (
      ctx: CanvasRenderingContext2D,
      element: Element,
      alignment: string,
      text: string
    ) => {
      const logoWidth = Math.min(element.width - 20, 120);
      const logoHeight = Math.min(element.height - 20, 60);

      let x = 10;
      if (alignment === "center") {
        x = (element.width - logoWidth) / 2;
      } else if (alignment === "right") {
        x = element.width - logoWidth - 10;
      }

      const y = (element.height - logoHeight) / 2;

      // Rectangle du logo
      ctx.fillStyle = normalizeColor("#f0f0f0");
      ctx.strokeStyle = normalizeColor("#ccc");
      ctx.lineWidth = 1;
      ctx.fillRect(x, y, logoWidth, logoHeight);
      ctx.strokeRect(x, y, logoWidth, logoHeight);

      // Texte du placeholder
      ctx.fillStyle = normalizeColor("#666");
      ctx.font = "12px Arial";
      ctx.textAlign = "center";
      ctx.fillText(text, x + logoWidth / 2, y + logoHeight / 2 + 4);
    },
    []
  );

  const drawCompanyLogo = useCallback(
    (ctx: CanvasRenderingContext2D, element: Element) => {
      const props = element as CompanyLogoElement;
      const logoUrl = props.src || props.logoUrl || "";

      // ✅ FIX: If no logo URL, show a better placeholder
      if (!logoUrl) {
        drawLogoPlaceholder(
          ctx,
          element,
          "center",
          "Configurez le logo entreprise"
        );
        return;
      }

      // const fit = props.fit || 'contain';
      const alignment = props.alignment || "left";

      // ✅ CORRECTION 7: Détecter si l'URL a changé
      const lastRenderedUrl = renderedLogoUrlsRef.current.get(element.id);
      if (logoUrl !== lastRenderedUrl) {
        renderedLogoUrlsRef.current.set(element.id, logoUrl);
      }

      // Fond transparent
      ctx.fillStyle = "transparent";
      ctx.fillRect(0, 0, element.width, element.height);

      if (logoUrl) {
        // Vérifier si l'image est en cache
        let cachedImage = imageCache.current.get(logoUrl);

        if (!cachedImage) {
          const img = document.createElement("img");
          img.crossOrigin = "anonymous";
          img.src = logoUrl;

          // Gérer les erreurs de chargement
          img.onerror = () => {
            debugError("❌ [LOGO] Image failed to load:", logoUrl);
          };

          // ✅ CRITICAL: Quand l'image se charge, redessiner le canvas
          img.onload = () => {
            const size = estimateImageMemorySize(img);
            imageCache.current.set(logoUrl, {
              image: img,
              size: size,
              lastUsed: Date.now(),
            });
            // Déclencher un nettoyage après ajout
            cleanupImageCache();
            // Incrémenter le counter pour forcer un redraw
            setImageLoadCount((prev) => prev + 1);
          };

          // Retourner temporairement pour éviter les erreurs
          return;
        }

        const img = cachedImage.image;
        // Mettre à jour la date d'utilisation
        cachedImage.lastUsed = Date.now();

        // ✅ APPROCHE PLUS DIRECTE: Vérifier img.complete au rendu au lieu de compter sur onload
        // Rendre l'image si elle a une URL valide, même si elle n'est pas encore complètement chargée
        const shouldRenderImage = logoUrl && logoUrl.trim() !== "";

        // DEBUG: Log detailed breakdown of shouldRenderImage condition

        // DEBUG: Log image state with more details

        if (shouldRenderImage) {
          try {
            // Appliquer la rotation si définie
            const rotation = element.rotation || 0;
            const opacity = element.opacity !== undefined ? element.opacity : 1;
            const borderRadius = element.borderRadius || 0;
            const objectFit = element.objectFit || "contain";

            // Calculer les dimensions et position selon objectFit
            const containerWidth = element.width - 20;
            const containerHeight = element.height - 20;

            // Si l'image n'est pas encore chargée, utiliser des dimensions par défaut ou essayer de deviner
            let imageAspectRatio: number;
            if (img.naturalWidth > 0 && img.naturalHeight > 0) {
              imageAspectRatio = img.naturalWidth / img.naturalHeight;
            } else {
              // Estimation par défaut pour les logos d'entreprise (généralement rectangulaires)
              imageAspectRatio = 2; // 2:1 ratio par défaut
            }

            const containerAspectRatio = containerWidth / containerHeight;

            let logoWidth: number;
            let logoHeight: number;
            let offsetX = 0;
            let offsetY = 0;

            switch (objectFit) {
              case "contain":
                // Respecte les proportions, image tient entièrement dans le conteneur
                if (containerAspectRatio > imageAspectRatio) {
                  logoHeight = containerHeight;
                  logoWidth = logoHeight * imageAspectRatio;
                } else {
                  logoWidth = containerWidth;
                  logoHeight = logoWidth / imageAspectRatio;
                }
                break;

              case "cover":
                // Respecte les proportions, image couvre entièrement le conteneur
                if (containerAspectRatio > imageAspectRatio) {
                  logoWidth = containerWidth;
                  logoHeight = logoWidth / imageAspectRatio;
                  offsetY = (containerHeight - logoHeight) / 2;
                } else {
                  logoHeight = containerHeight;
                  logoWidth = logoHeight * imageAspectRatio;
                  offsetX = (containerWidth - logoWidth) / 2;
                }
                break;

              case "fill":
                // Étire l'image pour remplir exactement le conteneur
                logoWidth = containerWidth;
                logoHeight = containerHeight;
                break;

              case "none":
                // Taille originale, centrée
                if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                  logoWidth = img.naturalWidth;
                  logoHeight = img.naturalHeight;
                } else {
                  // Taille par défaut si pas encore chargée
                  logoWidth = Math.min(containerWidth, 120);
                  logoHeight = Math.min(containerHeight, 60);
                }
                break;

              case "scale-down": {
                // Taille originale ou contain, selon ce qui est plus petit
                const originalWidth = img.naturalWidth || 120; // Défaut si pas chargé
                const originalHeight = img.naturalHeight || 60; // Défaut si pas chargé

                if (
                  originalWidth <= containerWidth &&
                  originalHeight <= containerHeight
                ) {
                  // Taille originale tient, l'utiliser
                  logoWidth = originalWidth;
                  logoHeight = originalHeight;
                } else {
                  // Utiliser contain
                  if (containerAspectRatio > imageAspectRatio) {
                    logoHeight = containerHeight;
                    logoWidth = logoHeight * imageAspectRatio;
                  } else {
                    logoWidth = containerWidth;
                    logoHeight = logoWidth / imageAspectRatio;
                  }
                }
                break;
              }

              default:
                // Par défaut contain
                if (containerAspectRatio > imageAspectRatio) {
                  logoHeight = containerHeight;
                  logoWidth = logoHeight * imageAspectRatio;
                } else {
                  logoWidth = containerWidth;
                  logoHeight = logoWidth / imageAspectRatio;
                }
            }

            // Calculer la position de base selon l'alignement
            let x = 10;
            if (alignment === "center") {
              x = (element.width - containerWidth) / 2;
            } else if (alignment === "right") {
              x = element.width - containerWidth - 10;
            }

            const y = (element.height - containerHeight) / 2;

            // Ajuster pour centrer l'image dans son conteneur selon objectFit
            const imageX = x + (containerWidth - logoWidth) / 2 + offsetX;
            const imageY = y + (containerHeight - logoHeight) / 2 + offsetY;

            // Sauvegarder le contexte
            ctx.save();

            // Appliquer l'opacité
            if (opacity < 1) {
              ctx.globalAlpha = opacity;
            }

            // Appliquer la rotation
            if (rotation !== 0) {
              const centerX = x + logoWidth / 2;
              const centerY = y + logoHeight / 2;
              ctx.translate(centerX, centerY);
              ctx.rotate((rotation * Math.PI) / 180);
              ctx.translate(-centerX, -centerY);
            }

            // Si borderRadius > 0, créer un chemin arrondi
            if (borderRadius > 0) {
              ctx.beginPath();
              roundedRect(ctx, x, y, logoWidth, logoHeight, borderRadius);
              ctx.clip();
            }

            // Essayer de dessiner l'image - si elle n'est pas chargée, cela ne fera rien
            // mais au moins on aura essayé
            ctx.drawImage(img, imageX, imageY, logoWidth, logoHeight);

            // Restaurer le contexte
            ctx.restore();
          } catch (error) {
            debugError(`❌ [LOGO] Error rendering image ${logoUrl}:`, error);
            // En cas d'erreur, dessiner un placeholder
            drawLogoPlaceholder(
              ctx,
              element,
              alignment,
              "Erreur de chargement"
            );
          }
        } else {
          // Pas d'URL valide, dessiner un placeholder
          drawLogoPlaceholder(ctx, element, alignment, "URL manquante");
        }
      } else {
        // Pas d'URL, dessiner un placeholder
        drawLogoPlaceholder(ctx, element, alignment, "Company_logo");
      }
    },
    [drawLogoPlaceholder, cleanupImageCache, estimateImageMemorySize]
  ); // ✅ BUGFIX-008: REMOVED setImageLoadCounter

  // ✅ BUGFIX-007: Memoize drawDynamicText to prevent recreation on every render
  const drawDynamicText = useCallback(
    (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as DynamicTextElement;
      const text = props.text || props.content || "Texte personnalisable";
      const fontSize = props.fontSize || 14;
      const fontFamily = props.fontFamily || "Arial";
      const fontWeight = props.fontWeight || "normal";
      const fontStyle = props.fontStyle || "normal";
      const autoWrap = props.autoWrap !== false; // Par défaut activé
      const textColor = props.textColor || props.color || "#000000";

      // Appliquer le fond seulement si showBackground est activé
      if (props.showBackground !== false) {
        ctx.fillStyle = normalizeColor(props.backgroundColor || "#e5e7eb");
        ctx.fillRect(0, 0, element.width, element.height);
      }

      ctx.fillStyle = normalizeColor(textColor);
      ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
      ctx.textAlign = "left";

      // Remplacer les variables génériques par des valeurs par défaut pour l'aperçu
      const processedText = text
        .replace(/\{\{customer_name\}\}/g, "Dupont Marie")
        .replace(/\{\{customer_first_name\}\}/g, "Marie")
        .replace(/\{\{customer_last_name\}\}/g, "Dupont")
        .replace(/\{\{customer_email\}\}/g, "marie.dupont@email.com")
        .replace(/\{\{customer_phone\}\}/g, "+33 1 23 45 67 89")
        .replace(/\{\{order_number\}\}/g, "CMD-2026-001")
        .replace(/\{\{order_date\}\}/g, new Date().toLocaleDateString("fr-FR"))
        .replace(/\{\{order_total\}\}/g, "150,00 €")
        .replace(/\{\{company_name\}\}/g, "Ma Société")
        .replace(/\{\{company_email\}\}/g, "contact@masociete.com")
        .replace(/\{\{company_phone\}\}/g, "+33 1 23 45 67 89")
        .replace(/\{\{company_address\}\}/g, "123 Rue de la Paix, 75001 Paris")
        .replace(/\{\{current_date\}\}/g, new Date().toLocaleDateString("fr-FR"))
        .replace(/\{\{current_time\}\}/g, new Date().toLocaleTimeString("fr-FR"));

      if (autoWrap) {
        // Fonction pour diviser le texte en lignes selon la largeur disponible
        const wrapText = (text: string, maxWidth: number): string[] => {
          const words = text.split(" ");
          const lines: string[] = [];
          let currentLine = "";

          for (const word of words) {
            const testLine = currentLine + (currentLine ? " " : "") + word;
            const metrics = ctx.measureText(testLine);

            if (metrics.width > maxWidth && currentLine) {
              lines.push(currentLine);
              currentLine = word;
            } else {
              currentLine = testLine;
            }
          }

          if (currentLine) {
            lines.push(currentLine);
          }

          return lines;
        };

        // Gérer les sauts de ligne existants (\n)
        const paragraphs = processedText.split("\n");
        let y = 25;

        paragraphs.forEach((paragraph: string) => {
          if (paragraph.trim()) {
            const lines = wrapText(paragraph, element.width - 20); // Marge de 10px de chaque côté
            lines.forEach((line: string) => {
              ctx.fillText(line, 10, y);
              y += fontSize + 4; // Espacement entre lignes
            });
          } else {
            y += fontSize + 4; // Ligne vide
          }
        });
      } else {
        // Comportement original : gérer uniquement les \n existants
        const lines = processedText.split("\n");
        let y = 25;
        lines.forEach((line: string) => {
          ctx.fillText(line, 10, y);
          y += fontSize + 4;
        });
      }
    },
    []
  ); // No deps - pure function

  // ✅ BUGFIX-007: Memoize drawMentions to prevent recreation on every render
  const drawMentions = useCallback(
    (ctx: CanvasRenderingContext2D, element: Element) => {
      const props = element as MentionsElement;
      const fontSizeRaw = props.fontSize || 10;

      // ✅ BUGFIX-021: Robust font size parsing for various formats
      let fontSize: number;
      if (typeof fontSizeRaw === "number") {
        fontSize = fontSizeRaw;
      } else if (typeof fontSizeRaw === "string") {
        // Try removing 'px', 'em', 'rem', 'pt' suffixes
        const numStr = fontSizeRaw.replace(/px|em|rem|pt|%/g, "").trim();
        fontSize = parseFloat(numStr) || 10;
        // If it's 'em' or 'rem', convert to approximate px (1em ≈ 16px)
        if (fontSizeRaw.includes("em") || fontSizeRaw.includes("rem")) {
          fontSize = fontSize * 16;
        }
      } else {
        fontSize = 10;
      }

      // Ensure fontSize is reasonable
      fontSize = Math.max(6, Math.min(72, fontSize));

      const fontFamily = props.fontFamily || "Arial";
      const fontWeight = props.fontWeight || "normal";
      const fontStyle = props.fontStyle || "normal";
      const textAlign = props.textAlign || "left";
      const text =
        props.text ||
        "SARL au capital de 10 000€ - RCS Lyon 123 456 789\nTVA FR 12 345 678 901 - SIRET 123 456 789 00012\ncontact@maboutique.com - +33 4 12 34 56 78";
      const showSeparator = props.showSeparator !== false;
      const separatorStyle = props.separatorStyle || "solid";
      const theme = (props.theme || "legal") as keyof typeof themes;

      // Définition des thèmes pour les mentions
      const themes = {
        legal: {
          backgroundColor: "#ffffff",
          borderColor: "#6b7280",
          textColor: "#374151",
          headerTextColor: "#111827",
        },
        subtle: {
          backgroundColor: "#f9fafb",
          borderColor: "#e5e7eb",
          textColor: "#6b7280",
          headerTextColor: "#374151",
        },
        minimal: {
          backgroundColor: "#ffffff",
          borderColor: "#f3f4f6",
          textColor: "#9ca3af",
          headerTextColor: "#6b7280",
        },
      };

      const currentTheme = themes[theme] || themes.legal;

      // Utiliser les couleurs personnalisées si définies, sinon utiliser le thème
      const bgColor = normalizeColor(
        props.backgroundColor || currentTheme.backgroundColor
      );
      const txtColor = normalizeColor(
        props.textColor || currentTheme.textColor
      );

      // Appliquer le fond seulement si showBackground est activé
      if (props.showBackground !== false) {
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, element.width, element.height);
      }

      ctx.fillStyle = txtColor;

      let y = 15;

      // Dessiner le séparateur si activé
      if (showSeparator) {
        ctx.strokeStyle = txtColor;
        ctx.lineWidth = 1;

        if (separatorStyle === "double") {
          ctx.beginPath();
          ctx.moveTo(10, y - 5);
          ctx.lineTo(element.width - 10, y - 5);
          ctx.stroke();
          ctx.beginPath();
          ctx.moveTo(10, y - 2);
          ctx.lineTo(element.width - 10, y - 2);
          ctx.stroke();
        } else {
          ctx.setLineDash(
            separatorStyle === "dashed"
              ? [5, 5]
              : separatorStyle === "dotted"
              ? [2, 2]
              : []
          );
          ctx.beginPath();
          ctx.moveTo(10, y - 5);
          ctx.lineTo(element.width - 10, y - 5);
          ctx.stroke();
          ctx.setLineDash([]); // Reset line dash
        }

        y += 10; // Espace après le séparateur
      }

      ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
      ctx.textAlign = textAlign as CanvasTextAlign;

      // Fonction de wrapping du texte
      const wrapText = (text: string, maxWidth: number): string[] => {
        if (!text) return [""];

        // Traiter chaque paragraphe séparément (séparé par \n)
        const paragraphs = text.split("\n");
        const wrappedParagraphs: string[] = [];

        for (const paragraph of paragraphs) {
          if (paragraph.trim() === "") {
            // Ligne vide (séparateur), on la garde telle quelle
            wrappedParagraphs.push("");
            continue;
          }

          // Wrapper le paragraphe comme avant
          const words = paragraph.split(" ");
          const lines: string[] = [];
          let currentLine = "";

          for (const word of words) {
            const testLine = currentLine ? currentLine + " " + word : word;
            const metrics = ctx.measureText(testLine);

            if (metrics.width > maxWidth && currentLine) {
              // Le mot ne rentre pas, on passe à la ligne
              lines.push(currentLine);
              currentLine = word;
            } else {
              currentLine = testLine;
            }
          }

          if (currentLine) {
            lines.push(currentLine);
          }

          wrappedParagraphs.push(...lines);
        }

        return wrappedParagraphs;
      };

      // Wrapper le texte selon la largeur disponible
      const maxWidth = element.width - 20; // Marge de 20px
      const wrappedLines = wrapText(text, maxWidth);

      // Calculer le nombre maximum de lignes qui peuvent tenir
      const lineHeight = fontSize + 2;
      const maxLines = Math.floor(
        (element.height - (showSeparator ? 25 : 15)) / lineHeight
      );

      // Rendre seulement les lignes qui tiennent
      wrappedLines.slice(0, maxLines).forEach((line: string, index: number) => {
        const x =
          textAlign === "center"
            ? element.width / 2
            : textAlign === "right"
            ? element.width - 10
            : 10;
        const lineY = (showSeparator ? 25 : 15) + index * lineHeight;
        ctx.fillText(line, x, lineY);
      });
    },
    []
  ); // No deps - pure function

  // ✅ BUGFIX-001/004: Memoize drawElement but pass state as parameter to avoid dependency cycle
  const drawElement = useCallback(
    (
      ctx: CanvasRenderingContext2D,
      element: Element,
      currentState: BuilderState
    ) => {
      // Vérifier si l'élément est visible
      if (element.visible === false) {
        debugLog(
          `[Canvas] Skipping invisible element: ${element.type} (${element.id})`
        );
        return;
      }

      debugLog(
        `[Canvas] Drawing element: ${element.type} (${
          element.id
        }) - Position: (${element.x}, ${element.y}), Size: ${element.width}x${
          element.height
        }, Rotation: ${element.rotation || 0}°`
      );

      ctx.save();

      // Appliquer transformation de l'élément
      if (element.rotation) {
        // Rotation autour du centre de l'élément
        const centerX = element.width / 2;
        const centerY = element.height / 2;
        ctx.translate(element.x + centerX, element.y + centerY);
        ctx.rotate((element.rotation * Math.PI) / 180);
        ctx.translate(-centerX, -centerY);
      } else {
        // Pas de rotation, translation normale
        ctx.translate(element.x, element.y);
      }

      // Dessiner selon le type d'élément
      switch (element.type) {
        case "rectangle":
          debugLog(`[Canvas] Rendering rectangle element: ${element.id}`);
          drawRectangle(ctx, element);
          break;
        case "circle":
          debugLog(`[Canvas] Rendering circle element: ${element.id}`);
          drawCircle(ctx, element);
          break;
        case "text":
          debugLog(`[Canvas] Rendering text element: ${element.id}`);
          drawText(ctx, element);
          break;
        case "line":
          debugLog(`[Canvas] Rendering line element: ${element.id}`);
          drawLine(ctx, element);
          break;
        case "product_table":
          debugLog(`[Canvas] Rendering product table element: ${element.id}`);
          drawProductTable(ctx, element, currentState, imageCache, setImageLoadCount);
          break;
        case "customer_info":
          debugLog(`[Canvas] Rendering customer info element: ${element.id}`);
          drawCustomerInfo(ctx, element, currentState);
          break;
        case "company_info":
          debugLog(`[Canvas] Rendering company info element: ${element.id}`);
          drawCompanyInfo(ctx, element, currentState);
          break;
        case "company_logo":
          debugLog(`[Canvas] Rendering company logo element: ${element.id}`);
          drawCompanyLogo(ctx, element);
          break;
        case "order_number":
          debugLog(`[Canvas] Rendering order number element: ${element.id}`);
          drawOrderNumber(ctx, element, currentState);
          break;
        case "woocommerce_order_date":
          debugLog(`[Canvas] Rendering woocommerce order date element: ${element.id}`);
          drawWoocommerceOrderDate(ctx, element, currentState);
          break;
        case "woocommerce_invoice_number":
          debugLog(`[Canvas] Rendering woocommerce invoice number element: ${element.id}`);
          drawWoocommerceInvoiceNumber(ctx, element, currentState);
          break;
        case "document_type":
          debugLog(`[Canvas] Rendering document type element: ${element.id}`);
          drawDocumentType(ctx, element, currentState);
          break;
        case "dynamic_text":
          debugLog(`[Canvas] Rendering dynamic text element: ${element.id}`);
          drawDynamicText(ctx, element);
          break;
        case "mentions":
          debugLog(`[Canvas] Rendering mentions element: ${element.id}`);
          drawMentions(ctx, element);
          break;
        case "image":
          debugLog(`[Canvas] Rendering image element: ${element.id}`);
          drawImage(ctx, element, imageCache);
          break;
        default:
          debugWarn(
            `[Canvas] Unknown element type: ${element.type} for element ${element.id}`
          );
          // Élément générique - dessiner un rectangle simple
          ctx.strokeStyle = normalizeColor("#000000");
          ctx.lineWidth = 1;
          ctx.strokeRect(0, 0, element.width, element.height);
      }
      ctx.restore();
    },
    [drawCompanyLogo, drawDynamicText, drawMentions, canvasSettings]
  ); // ✅ BUGFIX-007: Include memoized draw functions

  // Fonction pour dessiner la sélection
  const drawSelection = useCallback(
    (
      ctx: CanvasRenderingContext2D,
      selectedIds: string[],
      elements: Element[]
    ) => {
      const selectedElements = elements.filter((el) =>
        selectedIds.includes(el.id)
      );
      if (selectedElements.length === 0) {
        debugLog("[Canvas] Selection cleared - no elements selected");
        return;
      }

      debugLog(
        `[Canvas] Drawing selection for ${selectedElements.length} element(s):`,
        selectedIds
      );

      // Calculer les bounds de sélection
      let minX = Infinity,
        minY = Infinity,
        maxX = -Infinity,
        maxY = -Infinity;

      selectedElements.forEach((el) => {
        minX = Math.min(minX, el.x);
        minY = Math.min(minY, el.y);
        maxX = Math.max(maxX, el.x + el.width);
        maxY = Math.max(maxY, el.y + el.height);
      });

      // Rectangle de sélection
      ctx.strokeStyle = normalizeColor("#007acc");
      ctx.lineWidth = 1;
      ctx.setLineDash([5, 5]);
      ctx.strokeRect(minX - 2, minY - 2, maxX - minX + 4, maxY - minY + 4);

      // Poignées de redimensionnement (conditionnées par les settings)
      if (canvasSettings?.selectionShowHandles) {
        const handleSize = 6;
        ctx.fillStyle = normalizeColor("#007acc");
        ctx.setLineDash([]);

        // Coins
        ctx.fillRect(
          minX - handleSize / 2,
          minY - handleSize / 2,
          handleSize,
          handleSize
        );
        ctx.fillRect(
          maxX - handleSize / 2,
          minY - handleSize / 2,
          handleSize,
          handleSize
        );
        ctx.fillRect(
          minX - handleSize / 2,
          maxY - handleSize / 2,
          handleSize,
          handleSize
        );
        ctx.fillRect(
          maxX - handleSize / 2,
          maxY - handleSize / 2,
          handleSize,
          handleSize
        );

        // Centres des côtés
        const midX = (minX + maxX) / 2;
        const midY = (minY + maxY) / 2;
        ctx.fillRect(
          midX - handleSize / 2,
          minY - handleSize / 2,
          handleSize,
          handleSize
        );
        ctx.fillRect(
          midX - handleSize / 2,
          maxY - handleSize / 2,
          handleSize,
          handleSize
        );
        ctx.fillRect(
          minX - handleSize / 2,
          midY - handleSize / 2,
          handleSize,
          handleSize
        );
        ctx.fillRect(
          maxX - handleSize / 2,
          midY - handleSize / 2,
          handleSize,
          handleSize
        );
      }

      // Poignées de rotation (conditionnées par les settings)
      if (canvasSettings?.selectionRotationEnabled !== false) {
        const rotationHandleSize = 8;
        const rotationHandleDistance = 20;

        // Vérifier si au moins un élément a une rotation proche de 0°
        // Utiliser la même logique de normalisation que dans useCanvasInteraction.ts
        const hasZeroRotation = selectedElements.some((el) => {
          const rotation = (el as any).rotation || 0;
          // Normaliser l'angle entre -180° et 180° (même logique que le snap)
          let normalizedRotation = rotation % 360;
          if (normalizedRotation > 180) normalizedRotation -= 360;
          if (normalizedRotation < -180) normalizedRotation += 360;
          // Utiliser la tolérance pour 0° (10°) pour cohérence avec le snap ultra simple
          return Math.abs(normalizedRotation - 0) <= 10;
        });

        // Couleur différente pour indiquer le snap à 0°
        const handleColor = hasZeroRotation ? "#00cc44" : "#007acc";
        ctx.fillStyle = handleColor;
        ctx.strokeStyle = handleColor;
        ctx.lineWidth = 2;
        ctx.setLineDash([]);

        // Centre de la sélection
        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;

        // Position de la poignée de rotation (au-dessus du centre)
        const rotationHandleX = centerX;
        const rotationHandleY = minY - rotationHandleDistance;

        // Cercle pour la poignée de rotation
        ctx.beginPath();
        ctx.arc(
          rotationHandleX,
          rotationHandleY,
          rotationHandleSize / 2,
          0,
          2 * Math.PI
        );
        ctx.fill();

        // Ligne reliant la poignée au centre
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(rotationHandleX, rotationHandleY);
        ctx.stroke();
      } else {
        console.log('[CANVAS] NOT drawing rotation handles because selectionRotationEnabled is:', canvasSettings?.selectionRotationEnabled);
      }

      // Afficher les dimensions pour chaque élément sélectionné
      selectedElements.forEach((el) => {
        if (selectedIds.includes(el.id)) {
          // Coordonnées
          const x = el.x;
          const y = el.y;
          const width = el.width;
          const height = el.height;

          // Afficher les dimensions en pixels sur le coin supérieur droit
          ctx.font = "11px Arial";
          ctx.fillStyle = normalizeColor("#007acc");
          ctx.textAlign = "right";
          ctx.textBaseline = "top";

          const dimensionText = `${(width * 1).toFixed(1)}×${(
            height * 1
          ).toFixed(1)}px`;
          const padding = 4;
          const textWidth = ctx.measureText(dimensionText).width;

          // Fond blanc pour meilleure lisibilité
          ctx.fillStyle = "white";
          ctx.fillRect(
            x + width - textWidth - padding * 2,
            y - 20,
            textWidth + padding * 2,
            18
          );

          // Texte
          ctx.fillStyle = normalizeColor("#007acc");
          ctx.font = "bold 11px Arial";
          ctx.fillText(dimensionText, x + width - padding, y - 16);
        }
      });
    },
    [canvasSettings]
  );

  // Fonctions pour gérer le menu contextuel
  const showContextMenu = useCallback(
    (x: number, y: number, elementId?: string) => {
      setContextMenu({
        isVisible: true,
        position: { x, y },
        elementId,
      });
    },
    []
  );

  const hideContextMenu = useCallback(() => {
    setContextMenu((prev) => ({ ...prev, isVisible: false }));
  }, []);

  const handleContextMenuAction = useCallback(
    (action: string, elementId?: string) => {
      debugLog(
        `[Canvas] Context menu action: ${action} on element ${
          elementId || "none"
        }`
      );
      if (!elementId) return;

      switch (action) {
        case "bring-to-front": {
          debugLog(`[Canvas] Bringing element ${elementId} to front`);
          // Déplacer l'élément à la fin du tableau (devant tous les autres)
          const elementIndex = state.elements.findIndex(
            (el) => el.id === elementId
          );
          if (elementIndex !== -1) {
            const element = state.elements[elementIndex];
            const newElements = [
              ...state.elements.slice(0, elementIndex),
              ...state.elements.slice(elementIndex + 1),
              element,
            ];
            dispatch({ type: "SET_ELEMENTS", payload: newElements });
          }
          break;
        }
        case "send-to-back": {
          debugLog(`[Canvas] Sending element ${elementId} to back`);
          // Déplacer l'élément au début du tableau (derrière tous les autres)
          const elementIndex = state.elements.findIndex(
            (el) => el.id === elementId
          );
          if (elementIndex !== -1) {
            const element = state.elements[elementIndex];
            const newElements = [
              element,
              ...state.elements.slice(0, elementIndex),
              ...state.elements.slice(elementIndex + 1),
            ];
            dispatch({ type: "SET_ELEMENTS", payload: newElements });
          }
          break;
        }
        case "bring-forward": {
          debugLog(`[Canvas] Bringing element ${elementId} forward`);
          // Déplacer l'élément d'une position vers l'avant
          const elementIndex = state.elements.findIndex(
            (el) => el.id === elementId
          );
          if (elementIndex !== -1 && elementIndex < state.elements.length - 1) {
            const newElements = [...state.elements];
            [newElements[elementIndex], newElements[elementIndex + 1]] = [
              newElements[elementIndex + 1],
              newElements[elementIndex],
            ];
            dispatch({ type: "SET_ELEMENTS", payload: newElements });
          }
          break;
        }
        case "send-backward": {
          debugLog(`[Canvas] Sending element ${elementId} backward`);
          // Déplacer l'élément d'une position vers l'arrière
          const elementIndex = state.elements.findIndex(
            (el) => el.id === elementId
          );
          if (elementIndex > 0) {
            const newElements = [...state.elements];
            [newElements[elementIndex], newElements[elementIndex - 1]] = [
              newElements[elementIndex - 1],
              newElements[elementIndex],
            ];
            dispatch({ type: "SET_ELEMENTS", payload: newElements });
          }
          break;
        }
        case "duplicate": {
          debugLog(`[Canvas] Duplicating element ${elementId}`);
          // Dupliquer l'élément avec un nouvel ID et un léger décalage
          const element = state.elements.find((el) => el.id === elementId);
          if (element) {
            const duplicatedElement = {
              ...element,
              id: `element_${Date.now()}_${Math.random()
                .toString(36)
                .substr(2, 9)}`,
              x: element.x + 10,
              y: element.y + 10,
              createdAt: new Date(),
              updatedAt: new Date(),
            };
            dispatch({ type: "ADD_ELEMENT", payload: duplicatedElement });
          }
          break;
        }
        case "copy": {
          debugLog(`[Canvas] Copying element ${elementId}`);
          // Copier l'élément dans le presse-papiers interne
          const element = state.elements.find((el) => el.id === elementId);
          if (element) {
            // TODO: Implémenter le presse-papiers interne
          }
          break;
        }
        case "cut": {
          debugLog(`[Canvas] Cutting element ${elementId}`);
          // Couper l'élément (copier puis supprimer)
          const element = state.elements.find((el) => el.id === elementId);
          if (element) {
            // TODO: Implémenter le presse-papiers interne
            // dispatch({ type: 'REMOVE_ELEMENT', payload: elementId });
          }
          break;
        }
        case "reset-size": {
          debugLog(`[Canvas] Resetting size for element ${elementId}`);
          // Réinitialiser la taille de l'élément à ses dimensions par défaut
          const element = state.elements.find((el) => el.id === elementId);
          if (element) {
            const defaultSizes: {
              [key: string]: { width: number; height: number };
            } = {
              rectangle: { width: 100, height: 100 },
              circle: { width: 100, height: 100 },
              text: { width: 100, height: 30 },
              line: { width: 100, height: 2 },
              product_table: { width: 400, height: 200 },
              customer_info: { width: 300, height: 80 },
              company_info: { width: 300, height: 120 },
              company_logo: { width: 150, height: 80 },
              order_number: { width: 200, height: 40 },
              document_type: { width: 150, height: 30 },
              "dynamic_text": { width: 200, height: 60 },
              mentions: { width: 400, height: 80 },
            };

            const defaultSize = defaultSizes[element.type] || {
              width: 100,
              height: 100,
            };
            dispatch({
              type: "UPDATE_ELEMENT",
              payload: {
                id: elementId,
                updates: {
                  width: defaultSize.width,
                  height: defaultSize.height,
                },
              },
            });
          }
          break;
        }
        case "fit-to-content": {
          debugLog(`[Canvas] Fitting element ${elementId} to content`);
          // Ajuster la taille de l'élément à son contenu (pour le texte principalement)
          const element = state.elements.find((el) => el.id === elementId);
          if (
            element &&
            (element.type === "text" || element.type === "dynamic_text")
          ) {
            // Pour les éléments texte, ajuster la hauteur selon le contenu
            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");
            if (ctx) {
              const props = element as TextElementProperties;
              const fontSize = props.fontSize || 14;
              const fontFamily = props.fontFamily || "Arial";
              const fontWeight = props.fontWeight || "normal";
              const fontStyle = props.fontStyle || "normal";

              ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;

              const text = props.text || "Texte";
              const lines = text.split("\n");
              const lineHeight = fontSize + 4;
              const contentHeight = lines.length * lineHeight + 20; // Marges

              dispatch({
                type: "UPDATE_ELEMENT",
                payload: {
                  id: elementId,
                  updates: { height: Math.max(contentHeight, 30) },
                },
              });
            }
          }
          break;
        }
        case "delete":
          debugLog(`[Canvas] Deleting element ${elementId}`);
          dispatch({ type: "REMOVE_ELEMENT", payload: elementId });
          break;
        case "lock": {
          debugLog(`[Canvas] Toggling lock for element ${elementId}`);
          // Basculer l'état verrouillé de l'élément
          const element = state.elements.find((el) => el.id === elementId);
          if (element) {
            dispatch({
              type: "UPDATE_ELEMENT",
              payload: {
                id: elementId,
                updates: { locked: !element.locked },
              },
            });
          }
          break;
        }
      }
    },
    [state.elements, dispatch]
  );

  const getContextMenuItems = useCallback(
    (elementId?: string): ContextMenuItem[] => {
      if (!elementId) {
        // Menu contextuel pour le canvas vide
        return [
          {
            id: "section-edit",
            section: "ÉDITION",
          },
          {
            id: "paste",
            label: "Coller",
            icon: "📋",
            shortcut: "Ctrl+V",
            action: () => {
              // TODO: Implémenter le collage depuis le presse-papiers
            },
            disabled: true, // Désactiver jusqu'à implémentation
          },
          {
            id: "select-all",
            label: "Tout sélectionner",
            icon: "☑️",
            shortcut: "Ctrl+A",
            action: () => {
              // Sélectionner tous les éléments
              const allElementIds = state.elements.map((el) => el.id);
              dispatch({ type: "SET_SELECTION", payload: allElementIds });
            },
          },
        ];
      }

      // Menu contextuel pour un élément
      const element = state.elements.find((el) => el.id === elementId);
      const isLocked = element?.locked || false;

      const items: ContextMenuItem[] = [
        // Section Ordre des calques
        {
          id: "section-layers",
          section: "CALQUES",
        },
        {
          id: "layer-order",
          label: "Ordre des calques",
          icon: "📚",
          children: [
            {
              id: "bring-to-front",
              label: "Premier plan",
              icon: "⬆️",
              shortcut: "Ctrl+↑",
              action: () =>
                handleContextMenuAction("bring-to-front", elementId),
              disabled: isLocked,
            },
            {
              id: "send-to-back",
              label: "Arrière plan",
              icon: "⬇️",
              shortcut: "Ctrl+↓",
              action: () => handleContextMenuAction("send-to-back", elementId),
              disabled: isLocked,
            },
            {
              id: "bring-forward",
              label: "Avancer d'un plan",
              icon: "↗️",
              shortcut: "Ctrl+Shift+↑",
              action: () => handleContextMenuAction("bring-forward", elementId),
              disabled: isLocked,
            },
            {
              id: "send-backward",
              label: "Reculer d'un plan",
              icon: "↙️",
              shortcut: "Ctrl+Shift+↓",
              action: () => handleContextMenuAction("send-backward", elementId),
              disabled: isLocked,
            },
          ],
        },
        { id: "separator1", separator: true },

        // Section Édition
        {
          id: "section-edit",
          section: "ÉDITION",
        },
        {
          id: "duplicate",
          label: "Dupliquer",
          icon: "📋",
          shortcut: "Ctrl+D",
          action: () => handleContextMenuAction("duplicate", elementId),
          disabled: isLocked,
          children: [
            {
              id: "duplicate-here",
              label: "Dupliquer ici",
              icon: "📋",
              action: () => handleContextMenuAction("duplicate", elementId),
              disabled: isLocked,
            },
            {
              id: "duplicate-multiple",
              label: "Dupliquer plusieurs...",
              icon: "📋📋",
              action: () =>
                handleContextMenuAction("duplicate-multiple", elementId),
              disabled: isLocked,
            },
          ],
        },
        {
          id: "clipboard",
          label: "Presse-papiers",
          icon: "📄",
          children: [
            {
              id: "copy",
              label: "Copier",
              icon: "📄",
              shortcut: "Ctrl+C",
              action: () => handleContextMenuAction("copy", elementId),
              disabled: false,
            },
            {
              id: "cut",
              label: "Couper",
              icon: "✂️",
              shortcut: "Ctrl+X",
              action: () => handleContextMenuAction("cut", elementId),
              disabled: isLocked,
            },
          ],
        },
        { id: "separator2", separator: true },

        // Section Taille
        {
          id: "section-size",
          section: "TAILLE",
        },
        {
          id: "reset-size",
          label: "Taille par défaut",
          icon: "📏",
          shortcut: "Ctrl+0",
          action: () => handleContextMenuAction("reset-size", elementId),
          disabled: isLocked,
        },
        {
          id: "fit-to-content",
          label: "Ajuster au contenu",
          icon: "📐",
          shortcut: "Ctrl+Shift+F",
          action: () => handleContextMenuAction("fit-to-content", elementId),
          disabled:
            isLocked ||
            !(element?.type === "text" || element?.type === "dynamic_text"),
        },
        { id: "separator3", separator: true },

        // Section État
        {
          id: "section-state",
          section: "ÉTAT",
        },
        {
          id: "lock",
          label: isLocked ? "Déverrouiller" : "Verrouiller",
          icon: isLocked ? "🔓" : "🔒",
          shortcut: isLocked ? "Ctrl+Shift+L" : "Ctrl+L",
          action: () => handleContextMenuAction("lock", elementId),
        },
        { id: "separator4", separator: true },

        // Section Danger
        {
          id: "section-danger",
          section: "SUPPRESSION",
        },
        {
          id: "delete",
          label: "Supprimer",
          icon: "🗑️",
          shortcut: "Suppr",
          action: () => handleContextMenuAction("delete", elementId),
          disabled: false,
        },
      ];

      return items;
    },
    [state.elements, handleContextMenuAction, dispatch]
  );

  // Fonction pour dessiner la grille
  const drawGrid = useCallback(
    (
      ctx: CanvasRenderingContext2D,
      w: number,
      h: number,
      size: number,
      color: string
    ) => {
      ctx.strokeStyle = color;
      ctx.lineWidth = 1;

      for (let x = 0; x <= w; x += size) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, h);
        ctx.stroke();
      }

      for (let y = 0; y <= h; y += size) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(w, y);
        ctx.stroke();
      }
    },
    []
  ); // No deps - pure function

  // Fonction pour dessiner les guides
  const drawGuides = useCallback(
    (
      ctx: CanvasRenderingContext2D,
      canvasWidth: number,
      canvasHeight: number
    ) => {
      ctx.save();
      ctx.strokeStyle = normalizeColor("#007acc");
      ctx.lineWidth = 1;
      ctx.setLineDash([5, 5]);

      // Guide horizontal au milieu
      ctx.beginPath();
      ctx.moveTo(0, canvasHeight / 2);
      ctx.lineTo(canvasWidth, canvasHeight / 2);
      ctx.stroke();

      // Guide vertical au milieu
      ctx.beginPath();
      ctx.moveTo(canvasWidth / 2, 0);
      ctx.lineTo(canvasWidth / 2, canvasHeight);
      ctx.stroke();

      ctx.restore();
    },
    []
  );

  // Gestionnaire de clic droit pour le canvas
  const handleCanvasContextMenu = useCallback(
    (event: MouseEvent<HTMLCanvasElement>) => {
      event.preventDefault();
      debugLog(
        `👆 Canvas: Context menu triggered at (${event.clientX}, ${event.clientY})`
      );
      debugLog(
        `[Canvas] Context menu triggered at (${event.clientX}, ${event.clientY})`
      );
      handleContextMenu(event, (x, y, elementId) => {
        debugLog(
          `📋 Canvas: Context menu callback - Element: ${
            elementId || "canvas"
          }, Position: (${x}, ${y})`
        );
        debugLog(
          `[Canvas] Context menu callback - Element: ${
            elementId || "canvas"
          }, Position: (${x}, ${y})`
        );
        showContextMenu(x, y, elementId);
      });
    },
    [handleContextMenu, showContextMenu]
  );

  // Fonction de rendu du canvas
  const renderCanvas = useCallback(() => {
    const startTime = Date.now();
    renderCountRef.current += 1;

    debugLog(
      `🎨 Canvas: Render #${renderCountRef.current} started - Elements: ${state.elements.length}, Zoom: ${state.canvas.zoom}%, Selection: ${state.selection.selectedElements.length} items`
    );

    debugLog(
      `[Canvas] Render #${renderCountRef.current} started - Elements: ${
        state.elements.length
      }, Zoom: ${state.canvas.zoom}%, Pan: (${state.canvas.pan.x.toFixed(
        1
      )}, ${state.canvas.pan.y.toFixed(1)}), Selection: ${
        state.selection.selectedElements.length
      } items`
    );

    const canvas = canvasRef.current;
    if (!canvas) {
      debugLog("❌ Canvas: Render cancelled - canvas ref is null");
      debugLog("[Canvas] Render cancelled - canvas ref is null");
      return;
    }

    const ctx = canvas.getContext("2d");
    if (!ctx) {
      debugLog("❌ Canvas: Render cancelled - canvas context unavailable");
      debugLog("[Canvas] Render cancelled - canvas context unavailable");
      return;
    }

    // Clear canvas with background color from settings (matching PDF background)
    const canvasBgColor = normalizeColor("#ffffff");
    debugLog(
      `🖌️ Canvas: Clearing canvas with background color: ${canvasBgColor}`
    );
    debugLog(
      `[Canvas] Clearing canvas with background color: ${canvasBgColor}`
    );
    ctx.fillStyle = canvasBgColor;
    ctx.fillRect(0, 0, width, height);

    // Note: Canvas border is now handled by CSS styling based on settings

    // DEBUG: Log elements
    if (state.elements.length === 0) {
      // Pas d'éléments à dessiner
    } else {
      // Éléments présents
    }

    // Appliquer transformation (pan uniquement - zoom géré par CSS)
    ctx.save();

    ctx.translate(state.canvas.pan.x, state.canvas.pan.y);

    // Note: Zoom is now handled by CSS display size, no need for ctx.scale()

    // NOTE: Les marges seront réactivées après que le rendu des éléments soit fixé
    // const showMargins = canvasSettings.showMargins;
    // if (showMargins && canvasSettings) {
    //   const marginTopPx = (canvasSettings.marginTop || 0) * 3.78;
    //   const marginLeftPx = (canvasSettings.marginLeft || 0) * 3.78;
    //   ctx.translate(marginLeftPx, marginTopPx);
    // }

    // Dessiner la grille si activée (utiliser les paramètres Canvas Settings et l'état du toggle)
    if (canvasSettings?.gridShow && state.canvas.showGrid) {
      drawGrid(
        ctx,
        width,
        height,
        canvasSettings?.gridSize || 20,
        canvasSettings?.gridColor || "#e0e0e0"
      );
    }

    // Dessiner les guides si activés (utiliser les paramètres Canvas Settings et l'état du template)
    if (canvasSettings?.guidesEnabled && state.template.showGuides) {
      drawGuides(ctx, width, height);
    }

    // Dessiner les éléments
    debugLog(
      `📝 Canvas: Rendering ${visibleElementsList.length} visible elements (lazy loading: ${lazyLoadingEnabled})`
    );
    debugLog(
      `[Canvas] Rendering ${visibleElementsList.length} visible elements (lazy loading: ${lazyLoadingEnabled})`
    );
    visibleElementsList.forEach((element) => {
      debugLog(
        `🎯 Canvas: Drawing element: ${element.type} (${element.id}) at (${element.x}, ${element.y}) ${element.width}x${element.height}`
      );
      debugLog(
        `[Canvas] Drawing element: ${element.type} (${element.id}) at (${element.x}, ${element.y}) ${element.width}x${element.height}`
      );
      drawElement(ctx, element, state); // ✅ BUGFIX-001/004: Pass state as parameter
    });

    // Dessiner la sélection temporaire (rectangle/lasso en cours)
    if (selectionState?.isSelecting) {
      if (
        selectionState.selectionMode === "rectangle" &&
        selectionState.selectionRect.width > 0 &&
        selectionState.selectionRect.height > 0
      ) {
        // Dessiner le rectangle de sélection
        ctx.save();
        ctx.strokeStyle = normalizeColor("#0066cc");
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        ctx.strokeRect(
          selectionState.selectionRect.x,
          selectionState.selectionRect.y,
          selectionState.selectionRect.width,
          selectionState.selectionRect.height
        );

        // Remplir avec une couleur semi-transparente
        ctx.fillStyle = "rgba(0, 102, 204, 0.1)";
        ctx.fillRect(
          selectionState.selectionRect.x,
          selectionState.selectionRect.y,
          selectionState.selectionRect.width,
          selectionState.selectionRect.height
        );
        ctx.restore();
      } else if (
        selectionState.selectionMode === "lasso" &&
        selectionState.selectionPoints.length > 1
      ) {
        // Dessiner le lasso
        ctx.save();
        ctx.strokeStyle = normalizeColor("#0066cc");
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        ctx.beginPath();
        ctx.moveTo(
          selectionState.selectionPoints[0].x,
          selectionState.selectionPoints[0].y
        );
        for (let i = 1; i < selectionState.selectionPoints.length; i++) {
          ctx.lineTo(
            selectionState.selectionPoints[i].x,
            selectionState.selectionPoints[i].y
          );
        }
        ctx.closePath();
        ctx.stroke();

        // Remplir avec une couleur semi-transparente
        ctx.fillStyle = "rgba(0, 102, 204, 0.1)";
        ctx.fill();
        ctx.restore();
      }
    }

    // Dessiner la sélection
    if (state.selection.selectedElements.length > 0) {
      drawSelection(ctx, state.selection.selectedElements, state.elements);
    }

    ctx.restore();

    // Log rendu terminé avec métriques de performance
    const renderTime = Date.now() - startTime;
    debugLog(
      `✅ Canvas: Render #${renderCountRef.current} completed in ${renderTime}ms - ${state.elements.length} elements rendered`
    );
    debugLog(
      `[Canvas] Render #${renderCountRef.current} completed in ${renderTime}ms - ${state.elements.length} elements rendered`
    );

    // Log avertissement si le rendu prend trop de temps
    if (renderTime > 100) {
      debugWarn(
        `⚠️ Canvas: Slow render detected: ${renderTime}ms for ${state.elements.length} elements`
      );
      debugWarn(
        `[Canvas] Slow render detected: ${renderTime}ms for ${state.elements.length} elements`
      );
    }
  }, [
    width,
    height,
    canvasSettings,
    state,
    drawElement,
    drawGrid,
    drawGuides,
    selectionState,
    drawSelection,
    visibleElementsList,
  ]); // ✅ Include memoized drawGrid and drawGuides

  // Redessiner quand l'état change - CORRECTION: Supprimer renderCanvas des dépendances pour éviter les boucles
  useEffect(() => {
    debugLog(
      `🔄 Canvas: State change detected - triggering render. Elements: ${state.elements.length}, Selection: ${state.selection.selectedElements.length}, Zoom: ${state.canvas.zoom}%`
    );
    debugLog(
      `[Canvas] State change detected - triggering render. Elements: ${state.elements.length}, Selection: ${state.selection.selectedElements.length}, Zoom: ${state.canvas.zoom}%`
    );
    renderCanvas();
  }, [
    state,
    canvasSettings,
    imageLoadCount,
    selectionState?.updateTrigger,
    visibleElementsList,
  ]); // Dépendances directes au lieu de renderCanvas

  // Rendu initial - REMOVED: Redondant avec l'effet principal ci-dessus

  // ✅ Force initial render when elements first load (for cached images)
  useEffect(() => {
    if (state.elements.length > 0 && !initialImageCheckDoneRef.current) {
      debugLog(
        `[Canvas] Initial elements loaded (${state.elements.length} elements) - scheduling image loading checks`
      );
      initialImageCheckDoneRef.current = true;

      // Force multiple renders to ensure images are displayed
      const timer1 = setTimeout(() => {
        debugLog(`[Canvas] Image loading check #1`);
        setImageLoadCount((prev) => prev + 1);
      }, 100);

      const timer2 = setTimeout(() => {
        debugLog(`[Canvas] Image loading check #2`);
        setImageLoadCount((prev) => prev + 1);
      }, 500);

      const timer3 = setTimeout(() => {
        debugLog(`[Canvas] Image loading check #3`);
        setImageLoadCount((prev) => prev + 1);
      }, 1000);

      // Add longer timeout for slow-loading images
      const timer4 = setTimeout(() => {
        debugLog(`[Canvas] Image loading check #4 (final)`);
        setImageLoadCount((prev) => prev + 1);
      }, 2000);

      return () => {
        clearTimeout(timer1);
        clearTimeout(timer2);
        clearTimeout(timer3);
        clearTimeout(timer4);
      };
    }
  }, [state.elements.length]);

  // ✅ CORRECTION 1: Ajouter beforeunload event pour avertir des changements non-sauvegardés
  useEffect(() => {
    const handleBeforeUnload = (e: Event) => {
      if (state.template.isModified) {
        e.preventDefault();
      }
    };

    window.addEventListener("beforeunload", handleBeforeUnload, {
      passive: true,
    });
    return () => window.removeEventListener("beforeunload", handleBeforeUnload);
  }, [state.template.isModified]);

  // 🎯 Initialize monitoring dashboard
  useEffect(() => {
    CanvasMonitoringDashboard.initialize();
    // Silent initialization
  }, []);

  // Calculate border style based on canvas settings and license
  const isPremium = window.pdfBuilderData?.license?.isPremium || false;

  const borderStyle = isDragOver 
    ? "2px solid #007acc" 
    : (isPremium && canvasSettings?.borderWidth && canvasSettings?.borderWidth > 0 
        ? `${canvasSettings.borderWidth}px solid ${canvasSettings?.borderColor || DEFAULT_SETTINGS.borderColor}` 
        : "none"
    );

  // Calculate canvas display size based on zoom
  const zoomScale = state.canvas.zoom / 100;
  const displayWidth = width * zoomScale;
  const displayHeight = height * zoomScale;

  debugLog(
    `[Canvas] Rendering canvas element - Display size: ${displayWidth}x${displayHeight}, Border: ${borderStyle}, Drag over: ${isDragOver}`
  );

  // ✅ Exposer une fonction pour capturer l'image du canvas
  useEffect(() => {
    const captureCanvasPreview = () => {
      if (canvasRef.current) {
        try {
          // Retourner l'image PNG du canvas en base64
          return canvasRef.current.toDataURL('image/png');
        } catch (error) {
          console.error('Erreur lors de la capture du canvas:', error);
          return null;
        }
      }
      return null;
    };

    // Exposer la fonction globalement
    (window as any).pdfBuilderCaptureCanvasPreview = captureCanvasPreview;

    return () => {
      delete (window as any).pdfBuilderCaptureCanvasPreview;
    };
  }, []);

  return (
    <>
      <div
        ref={canvasWrapperRef}
        onDragEnter={handleDragEnter}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        style={{
          display: "inline-flex",
          alignItems: "center",
          justifyContent: "center",
          border: borderStyle,
          borderRadius: "4px",
          backgroundColor: !isPremium 
            ? DEFAULT_SETTINGS.containerBackgroundColor // Fond par défaut en mode gratuit
            : (canvasSettings?.containerBackgroundColor || DEFAULT_SETTINGS.containerBackgroundColor),
          transition: "border-color 0.2s ease, box-shadow 0.2s ease",
          boxShadow: isDragOver 
            ? "0 0 0 2px rgba(0, 122, 204, 0.2)" 
            : (canvasSettings?.shadowEnabled 
                ? "2px 8px 16px rgba(0, 0, 0, 0.3), 0 4px 8px rgba(0, 0, 0, 0.2)" 
                : "none"),
          pointerEvents: "auto",
        }}
      >
        <canvas
          ref={canvasRef}
          width={width}
          height={height}
          className={className}
          onClick={handleCanvasClick}
          onMouseDown={handleMouseDown}
          onMouseMove={handleMouseMove}
          onMouseUp={handleMouseUp}
          onContextMenu={handleCanvasContextMenu}
          style={{
            width: `${displayWidth}px`,
            height: `${displayHeight}px`,
            cursor: "crosshair",
            backgroundColor: "#ffffff",
            border: "none",
            boxShadow: canvasSettings?.shadowEnabled
              ? "2px 8px 16px rgba(0, 0, 0, 0.3), 0 4px 8px rgba(0, 0, 0, 0.2)"
              : "none",
            display: "block",
          }}
        />
      </div>
      {contextMenu.isVisible && (
        <ContextMenu
          items={getContextMenuItems(contextMenu.elementId)}
          position={contextMenu.position}
          onClose={hideContextMenu}
          isVisible={contextMenu.isVisible}
        />
      )}
    </>
  );
};


