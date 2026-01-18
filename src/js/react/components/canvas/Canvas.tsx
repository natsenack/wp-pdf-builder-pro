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
import { useCanvasSettings } from "../../contexts/CanvasSettingsContext";
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

// Fonction helper pour normaliser les couleurs
const normalizeColor = (color: string): string => {
  if (!color || color === "transparent") {
    return "rgba(0,0,0,0)"; // Transparent
  }
  return color;
};

// Fonction utilitaire pour rectangle arrondi
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

// Fonctions de dessin pour les éléments
const drawRectangle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as ShapeElementProperties;
  const fillColor = normalizeColor(props.fillColor || "#ffffff");
  const strokeColor = normalizeColor(props.strokeColor || "#000000");
  const strokeWidth = props.strokeWidth || 1;
  const borderRadius = props.borderRadius || 0;

  ctx.fillStyle = fillColor;
  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  if (borderRadius > 0) {
    roundedRect(ctx, 0, 0, element.width, element.height, borderRadius);
    ctx.fill();
    ctx.stroke();
  } else {
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeRect(0, 0, element.width, element.height);
  }
};

const drawCircle = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as ShapeElementProperties;
  const fillColor = normalizeColor(props.fillColor || "#ffffff");
  const strokeColor = normalizeColor(props.strokeColor || "#000000");
  const strokeWidth = props.strokeWidth || 1;

  const centerX = element.width / 2;
  const centerY = element.height / 2;
  const radius = Math.min(centerX, centerY);

  ctx.fillStyle = fillColor;
  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
  ctx.fill();
  ctx.stroke();
};

const drawText = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as TextElementProperties;
  const text = props.text || "Text";
  const fontSize = props.fontSize || 16;
  const color = normalizeColor(props.color || "#000000");
  const align = props.align || "left";

  ctx.fillStyle = color;
  ctx.font = `${fontSize}px Arial`;
  ctx.textAlign = align as CanvasTextAlign;

  const x =
    align === "center"
      ? element.width / 2
      : align === "right"
      ? element.width
      : 0;
  ctx.fillText(text, x, fontSize);
};

const drawLine = (ctx: CanvasRenderingContext2D, element: Element) => {
  const props = element as LineElementProperties;
  const strokeColor = normalizeColor(props.strokeColor || "#000000");
  const strokeWidth = props.strokeWidth || 2;

  ctx.strokeStyle = strokeColor;
  ctx.lineWidth = strokeWidth;

  ctx.beginPath();
  ctx.moveTo(0, element.height / 2); // Centre verticalement
  ctx.lineTo(element.width, element.height / 2); // Ligne horizontale droite
  ctx.stroke();
};

// Fonction pour dessiner une image
const drawImage = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  imageCache: MutableRefObject<
    Map<string, { image: HTMLImageElement; size: number; lastUsed: number }>
  >
) => {
  const props = element as Element & { src?: string; objectFit?: string };
  const imageUrl = props.src || "";

  if (!imageUrl) {
    // Pas d'URL, dessiner un placeholder
    ctx.fillStyle = "#f0f0f0";
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeStyle = "#cccccc";
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
    ctx.fillStyle = "#999999";
    ctx.font = "14px Arial";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText("Image", element.width / 2, element.height / 2);
    return;
  }

  // Vérifier si l'image est en cache
  let cachedImage = imageCache.current.get(imageUrl);

  if (!cachedImage) {
    // Créer une nouvelle image et la mettre en cache
    const img = document.createElement("img");
    img.crossOrigin = "anonymous";
    img.src = imageUrl;

    // Attendre que l'image soit chargée pour calculer sa taille mémoire
    img.onload = () => {
      const size = estimateImageMemorySize(img);
      imageCache.current.set(imageUrl, {
        image: img,
        size: size,
        lastUsed: Date.now(),
      });
      // Déclencher un nettoyage après ajout
      cleanupImageCache(imageCache);
    };

    img.onerror = () => {
      debugWarn(`[Canvas] Failed to load image: ${imageUrl}`);
    };

    // Retourner temporairement pour éviter les erreurs
    return;
  }

  const img = cachedImage.image;
  // Mettre à jour la date d'utilisation
  cachedImage.lastUsed = Date.now();

  // Si l'image est chargée, la dessiner
  if (img.complete && img.naturalHeight !== 0) {
    // Appliquer object-fit
    const objectFit = props.objectFit || "cover";
    let drawX = 0,
      drawY = 0,
      drawWidth = element.width,
      drawHeight = element.height;
    let sourceX = 0,
      sourceY = 0,
      sourceWidth = img.naturalWidth,
      sourceHeight = img.naturalHeight;

    if (objectFit === "contain") {
      const ratio = Math.min(
        element.width / img.naturalWidth,
        element.height / img.naturalHeight
      );
      drawWidth = img.naturalWidth * ratio;
      drawHeight = img.naturalHeight * ratio;
      drawX = (element.width - drawWidth) / 2;
      drawY = (element.height - drawHeight) / 2;
    } else if (objectFit === "cover") {
      const ratio = Math.max(
        element.width / img.naturalWidth,
        element.height / img.naturalHeight
      );
      sourceWidth = element.width / ratio;
      sourceHeight = element.height / ratio;
      sourceX = (img.naturalWidth - sourceWidth) / 2;
      sourceY = (img.naturalHeight - sourceHeight) / 2;
    } else if (objectFit === "fill") {
      // Utiliser les dimensions de l'élément directement
    } else if (objectFit === "scale-down") {
      if (
        img.naturalWidth > element.width ||
        img.naturalHeight > element.height
      ) {
        const ratio = Math.min(
          element.width / img.naturalWidth,
          element.height / img.naturalHeight
        );
        drawWidth = img.naturalWidth * ratio;
        drawHeight = img.naturalHeight * ratio;
        drawX = (element.width - drawWidth) / 2;
        drawY = (element.height - drawHeight) / 2;
      }
    }

    ctx.drawImage(
      img,
      sourceX,
      sourceY,
      sourceWidth,
      sourceHeight,
      drawX,
      drawY,
      drawWidth,
      drawHeight
    );
  } else {
    // Image en cours de chargement ou erreur, dessiner un placeholder
    ctx.fillStyle = "#e0e0e0";
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.strokeStyle = "#999999";
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
    ctx.fillStyle = "#666666";
    ctx.font = "12px Arial";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(
      img.complete ? "Erreur" : "Chargement...",
      element.width / 2,
      element.height / 2
    );
  }
};

// Fonctions de rendu WooCommerce avec données fictives ou réelles selon le mode
const drawProductTable = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as ProductTableElementProperties;

  // ✅ BUGFIX-020: Validate element has minimum size for rendering
  const minWidth = 100;
  const minHeight = 50;

  if (element.width < minWidth || element.height < minHeight) {
    // Element too small, draw placeholder
    ctx.fillStyle = "#f0f0f0";
    ctx.fillRect(0, 0, element.width, element.height);
    ctx.fillStyle = "#999999";
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
  const showQuantity = props.showQuantity !== false;
  const showShipping = props.showShipping !== false;
  const showTax = props.showTax !== false;
  const showGlobalDiscount = props.showGlobalDiscount !== false;
  const textColor = normalizeColor(props.textColor || "#000000");
  const borderRadius = props.borderRadius || 0;

  let products: Array<{
    sku: string;
    name: string;
    description: string;
    qty: number;
    price: number;
    discount: number;
    total: number;
  }>;
  let shippingCost: number;
  let taxRate: number;
  let globalDiscount: number;
  let orderFees: number;
  let currency: string;

  // Utiliser les données WooCommerce si en mode commande, sinon données fictives
  // ✅ BUGFIX-015: Validate WooCommerceManager access safely
  if (state.previewMode === "command" && wooCommerceManager?.getOrderData?.()) {
    const orderData = wooCommerceManager.getOrderData();
    if (orderData) {
      const orderItems = wooCommerceManager.getOrderItems?.() || [];
      const orderTotals = wooCommerceManager.getOrderTotals?.() || {
        shipping: 0,
        tax: 0,
        subtotal: 0,
        discount: 0,
      };

      products = orderItems;
      shippingCost = orderTotals.shipping;
      taxRate =
        orderTotals.tax > 0
          ? (orderTotals.tax / orderTotals.subtotal) * 100
          : 20;
      globalDiscount = orderTotals.discount;
      orderFees = 0; // Les frais de commande sont déjà inclus dans les items
      currency = orderData.currency || "€";
    } else {
      // Fallback if orderData is null despite passing the check - use demo data
      shippingCost = props.shippingCost || 8.5;
      taxRate = props.taxRate || 20;
      globalDiscount = props.globalDiscount || 5;
      orderFees = props.orderFees || 2.5;
      currency = "€";

      products = [
        {
          sku: "DEMO-001",
          name: "Sample Product",
          description: "Demo product",
          qty: 1,
          price: 29.99,
          discount: 0,
          total: 29.99,
        },
      ];
    }
  } else {
    // Données fictives pour le mode éditeur
    shippingCost = props.shippingCost || 8.5;
    taxRate = props.taxRate || 20;
    globalDiscount = props.globalDiscount || 5;
    orderFees = props.orderFees || 2.5;
    currency = "€";

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
    ];
  }

  // Calcul du total avec remises (même logique pour données fictives et réelles)
  const subtotal = products.reduce(
    (sum, product) => sum + product.price * product.qty,
    0
  );
  const itemDiscounts = products.reduce(
    (sum, product) => sum + product.discount,
    0
  );
  const subtotalAfterItemDiscounts = subtotal - itemDiscounts;

  // Sous-total incluant les frais de commande
  const subtotalWithOrderFees = subtotalAfterItemDiscounts + orderFees;

  // Appliquer la remise globale sur le sous-total incluant les frais de commande (seulement si affichée)
  const globalDiscountAmount =
    globalDiscount > 0 && showGlobalDiscount
      ? (subtotalWithOrderFees * globalDiscount) / 100
      : 0;
  const subtotalAfterGlobalDiscount =
    subtotalWithOrderFees - globalDiscountAmount; // Ajouter les frais de port (seulement si affichés)
  const subtotalWithShipping =
    subtotalAfterGlobalDiscount + (showShipping ? shippingCost : 0);

  // Calculer les taxes (seulement si affichées)
  const taxAmount =
    taxRate > 0 && showTax ? (subtotalWithShipping * taxRate) / 100 : 0;

  // Total final
  const finalTotal = subtotalWithShipping + taxAmount;

  // Configuration des colonnes
  interface TableColumn {
    key: string;
    label: string;
    width: number;
    align: "left" | "center" | "right";
    x: number;
  }

  const columns: TableColumn[] = [];
  columns.push({
    key: "name",
    label: "Produit",
    width:
      showSku && showDescription
        ? 0.35
        : showSku || showDescription
        ? 0.45
        : 0.55,
    align: "left",
    x: 0,
  });
  if (showSku)
    columns.push({
      key: "sku",
      label: "SKU",
      width: 0.15,
      align: "left",
      x: 0,
    });
  if (showDescription)
    columns.push({
      key: "description",
      label: "Description",
      width: 0.25,
      align: "left",
      x: 0,
    });
  if (showQuantity)
    columns.push({
      key: "qty",
      label: "Qté",
      width: 0.08,
      align: "center",
      x: 0,
    });
  columns.push({
    key: "price",
    label: "Prix",
    width: 0.12,
    align: "right",
    x: 0,
  });
  columns.push({
    key: "total",
    label: "Total",
    width: 0.12,
    align: "right",
    x: 0,
  });

  // Normaliser les largeurs
  const totalWidth = columns.reduce((sum, col) => sum + col.width, 0);
  columns.forEach((col) => (col.width = col.width / totalWidth));

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
  ctx.fillStyle = props.backgroundColor || "#ffffff";
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
    ctx.fillStyle = props.headerBackgroundColor || "#f9fafb";
    // Utiliser roundedRect si borderRadius > 0, sinon fillRect normal
    if (borderRadius > 0) {
      roundedRect(ctx, 1, 1 + offsetY, element.width - 2, 32, borderRadius);
      ctx.fill();
    } else {
      ctx.fillRect(1, 1 + offsetY, element.width - 2, 32);
    }

    ctx.fillStyle = props.headerTextColor || "#374151";
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
    ctx.strokeStyle = "#e5e7eb";
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
      ctx.fillStyle = props.alternateRowColor || "#f9fafb";
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
      switch (col.key) {
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
    });
  });

  // Positionnement pour la section des totaux (après toutes les lignes de produits)
  currentY = 55 + products.length * (rowHeight + 4) + 8;

  // Section des totaux

  // Ligne de séparation avant les totaux
  ctx.strokeStyle = "#d1d5db";
  ctx.lineWidth = 1;
  ctx.beginPath();
  ctx.moveTo(element.width - 200, currentY);
  ctx.lineTo(element.width - 8, currentY);
  ctx.stroke();

  currentY += 20;

  // Affichage des totaux
  ctx.font = `bold ${fontSize}px Arial`;
  ctx.fillStyle = textColor; // Utiliser la couleur du texte
  ctx.textAlign = "left";

  const totalsY = currentY;
  ctx.fillText("Sous-total:", element.width - 200, totalsY);
  ctx.textAlign = "right";
  ctx.fillText(
    `${subtotalWithOrderFees.toFixed(2)}${currency}`,
    element.width - 8,
    totalsY
  );

  currentY += 18;

  // Remises combinées (articles + globale) - proviennent de coupons WooCommerce
  const totalDiscounts =
    itemDiscounts + (showGlobalDiscount ? globalDiscountAmount : 0);
  if (totalDiscounts > 0) {
    ctx.textAlign = "left";
    ctx.fillStyle = "#059669"; // Garder le vert pour la remise (couleur spéciale)
    ctx.fillText("Coupon:", element.width - 200, currentY);
    ctx.textAlign = "right";
    ctx.fillText(
      `-${totalDiscounts.toFixed(2)}${currency}`,
      element.width - 8,
      currentY
    );
    currentY += 18;
  }

  // Frais de port
  if (shippingCost > 0 && showShipping) {
    ctx.textAlign = "left";
    ctx.fillStyle = textColor; // Utiliser la couleur du texte
    ctx.fillText("Frais de port:", element.width - 200, currentY);
    ctx.textAlign = "right";
    ctx.fillText(
      `${shippingCost.toFixed(2)}${currency}`,
      element.width - 8,
      currentY
    );
    currentY += 18;
  }

  // Taxes
  if (taxAmount > 0 && showTax) {
    ctx.textAlign = "left";
    ctx.fillStyle = textColor; // Utiliser la couleur du texte
    ctx.fillText(`TVA (${taxRate}%):`, element.width - 200, currentY);
    ctx.textAlign = "right";
    ctx.fillText(
      `${taxAmount.toFixed(2)}${currency}`,
      element.width - 8,
      currentY
    );
    currentY += 18;
  }

  currentY += 8; // Plus d'espace avant la ligne de séparation du total
  ctx.strokeStyle = textColor; // Utiliser la couleur du texte pour la ligne
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.moveTo(element.width - 200, currentY - 5);
  ctx.lineTo(element.width - 8, currentY - 5);
  ctx.stroke();

  currentY += 8; // Plus d'espace après la ligne de séparation
  ctx.font = `${fontStyle} bold ${fontSize + 2}px ${fontFamily}`;
  ctx.fillStyle = textColor; // Utiliser la couleur du texte pour le total
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
  const props = element as CustomerInfoElementProperties;
  const fontSize = props.fontSize || 12;
  const fontFamily = props.fontFamily || "Arial";
  const fontWeight = props.fontWeight || "normal";
  const fontStyle = props.fontStyle || "normal";
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

  // Fond
  if (props.showBackground !== false) {
    ctx.fillStyle = props.backgroundColor || "#ffffff";
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Bordures
  if (showBorders) {
    ctx.strokeStyle = props.borderColor || "#e5e7eb";
    ctx.lineWidth = 1;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = normalizeColor(props.textColor || "#000000");
  ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
  ctx.textAlign = "left";

  let y = showHeaders ? 25 : 15;

  // En-tête
  if (showHeaders) {
    ctx.fillStyle = normalizeColor(props.headerTextColor || "#111827");
    ctx.fillText("Informations Client", 10, y);
    y += 20;
    ctx.fillStyle = normalizeColor(props.textColor || "#000000");
  }

  // Informations client fictives ou réelles selon le mode
  let customerData: {
    name: string;
    address: string;
    email: string;
    phone: string;
  };

  if (state.previewMode === "command") {
    customerData = wooCommerceManager.getCustomerInfo();
  } else {
    // Données fictives pour le mode éditeur
    customerData = {
      name: "Marie Dupont",
      address: "15 rue des Lilas, 75001 Paris",
      email: "marie.dupont@email.com",
      phone: "+33 6 12 34 56 78",
    };
  }

  ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;

  if (layout === "vertical") {
    if (showFullName) {
      ctx.fillText(customerData.name, 10, y);
      y += 18;
    }
    if (showAddress) {
      ctx.fillText(customerData.address, 10, y);
      y += 18;
    }
    if (showEmail) {
      ctx.fillText(customerData.email, 10, y);
      y += 18;
    }
    if (showPhone) {
      ctx.fillText(customerData.phone, 10, y);
      y += 18;
    }
    if (showPaymentMethod) {
      ctx.fillText("Paiement: Carte bancaire", 10, y);
      y += 18;
    }
    if (showTransactionId) {
      ctx.fillText("ID: TXN123456789", 10, y);
    }
  } else if (layout === "horizontal") {
    let text = "";
    if (showFullName) text += customerData.name;
    if (showEmail) text += (text ? " - " : "") + customerData.email;
    if (text) ctx.fillText(text, 10, y);

    if (showPhone) {
      ctx.fillText(
        customerData.phone,
        element.width - ctx.measureText(customerData.phone).width - 10,
        y
      );
    }
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
    const maxWidth = element.width - 20;
    const words = compactText.split(" ");
    let line = "";
    let compactY = y;

    for (let i = 0; i < words.length; i++) {
      const testLine = line + words[i] + " ";
      const metrics = ctx.measureText(testLine);
      if (metrics.width > maxWidth && i > 0) {
        ctx.fillText(line, 10, compactY);
        line = words[i] + " ";
        compactY += 16;
      } else {
        line = testLine;
      }
    }
    ctx.fillText(line, 10, compactY);
  }
};

const drawCompanyInfo = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  canvasSettings: any
) => {
  const props = element as CompanyInfoElementProperties;

  const fontSize = props.fontSize || 12;
  const fontFamily = props.fontFamily || "Arial";
  const fontWeight = props.fontWeight || "normal";
  const fontStyle = props.fontStyle || "normal";
  // Propriétés de police pour l'en-tête (nom de l'entreprise)
  const headerFontSize = props.headerFontSize || Math.round(fontSize * 1.2);
  const headerFontFamily = props.headerFontFamily || fontFamily;
  const headerFontWeight = props.headerFontWeight || "bold";
  const headerFontStyle = props.headerFontStyle || fontStyle;
  // Propriétés de police pour le corps du texte
  const bodyFontSize = props.bodyFontSize || fontSize;
  const bodyFontFamily = props.bodyFontFamily || fontFamily;
  const bodyFontWeight = props.bodyFontWeight || fontWeight;
  const bodyFontStyle = props.bodyFontStyle || fontStyle;
  const textAlign = "left"; // Forcer alignement à gauche pour company_info
  const theme = (props.theme || "corporate") as keyof typeof themes;
  const showBackground = props.showBackground !== false; // Par défaut true
  const showBorders = props.showBorders !== false; // Par défaut true
  const showCompanyName = props.showCompanyName !== false; // Par défaut true
  const showAddress = props.showAddress !== false; // Par défaut true
  const showPhone = props.showPhone !== false; // Par défaut true
  const showEmail = props.showEmail !== false; // Par défaut true
  const showSiret = props.showSiret !== false; // Par défaut true
  const showVat = props.showVat !== false; // Par défaut true
  const showRcs = props.showRcs !== false; // Par défaut true
  const showCapital = props.showCapital !== false; // Par défaut true

  // Définition des thèmes
  const themes = {
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
  };

  const currentTheme = themes[theme] || themes.corporate;

  // Utiliser les couleurs depuis les props de l'élément ou le thème (PAS les paramètres canvas globaux)
  const bgColor = normalizeColor(
    props.backgroundColor || currentTheme.backgroundColor
  );
  const borderCol = normalizeColor(
    props.borderColor || currentTheme.borderColor
  );
  const txtColor = normalizeColor(props.textColor || currentTheme.textColor);
  const headerTxtColor = normalizeColor(
    props.headerTextColor || currentTheme.headerTextColor
  );

  // Appliquer le fond si demandé
  if (showBackground) {
    ctx.fillStyle = bgColor;
    ctx.fillRect(0, 0, element.width, element.height);
  }

  // Appliquer les bordures si demandé
  if (showBorders) {
    ctx.strokeStyle = borderCol;
    ctx.lineWidth = props.borderWidth || 1;
    ctx.strokeRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = txtColor;
  ctx.textAlign = textAlign as CanvasTextAlign;

  // Calcul de la position X (toujours aligné à gauche pour company_info)
  let x = 10;

  let y = 20;

  // Informations entreprise hybrides : props configurables + valeurs par défaut
  const companyData = {
    name: props.companyName || "Ma Boutique En Ligne",
    address: props.companyAddress || "25 avenue des Commerçants",
    city: props.companyCity || "69000 Lyon",
    siret: props.companySiret || "SIRET: 123 456 789 00012",
    tva: props.companyTva || "TVA: FR 12 345 678 901",
    rcs: props.companyRcs || "RCS: Lyon B 123 456 789",
    capital: props.companyCapital || "Capital social: 10 000 €",
    email: props.companyEmail || "contact@maboutique.com",
    phone: props.companyPhone || "+33 4 12 34 56 78",
  };

  // Afficher le nom de l'entreprise si demandé
  if (showCompanyName) {
    ctx.fillStyle = headerTxtColor;
    ctx.font = `${headerFontStyle} ${headerFontWeight} ${headerFontSize}px ${headerFontFamily}`;
    ctx.fillText(companyData.name, x, y);
    y += Math.round(fontSize * 1.5);
    ctx.fillStyle = txtColor;
  }

  // Police normale pour les autres éléments
  ctx.font = `${bodyFontStyle} ${bodyFontWeight} ${bodyFontSize}px ${bodyFontFamily}`;

  // Afficher l'adresse si demandée
  if (showAddress) {
    ctx.fillText(companyData.address, x, y);
    y += Math.round(fontSize * 1.2);
    ctx.fillText(companyData.city, x, y);
    y += Math.round(fontSize * 1.5);
  }

  // Afficher le SIRET si demandé
  if (showSiret) {
    ctx.fillText(companyData.siret, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher la TVA si demandée
  if (showVat) {
    ctx.fillText(companyData.tva, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher le RCS si demandé
  if (showRcs) {
    ctx.fillText(companyData.rcs, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher le Capital social si demandé
  if (showCapital) {
    ctx.fillText(companyData.capital, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher l'email si demandé
  if (showEmail) {
    ctx.fillText(companyData.email, x, y);
    y += Math.round(fontSize * 1.2);
  }

  // Afficher le téléphone si demandé
  if (showPhone) {
    ctx.fillText(companyData.phone, x, y);
  }
};

const drawOrderNumber = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as OrderNumberElementProperties;

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
    ctx.fillStyle = props.backgroundColor || "#e5e7eb";
    ctx.fillRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = "#000000";

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

  let y = 20;

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

const drawDocumentType = (
  ctx: CanvasRenderingContext2D,
  element: Element,
  state: BuilderState
) => {
  const props = element as DocumentTypeElementProperties;
  const fontSize = props.fontSize || 18;
  const fontFamily = props.fontFamily || "Arial";
  const fontWeight = props.fontWeight || "bold";
  const fontStyle = props.fontStyle || "normal";
  const textAlign = props.textAlign || "left";
  const textColor = props.textColor || "#000000";

  // Appliquer le fond seulement si showBackground est activé
  if (props.showBackground !== false) {
    ctx.fillStyle = props.backgroundColor || "#e5e7eb";
    ctx.fillRect(0, 0, element.width, element.height);
  }

  ctx.fillStyle = textColor;
  ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
  ctx.textAlign = textAlign as CanvasTextAlign;

  // Type de document fictif ou réel selon le mode
  let documentType: string;

  if (state.previewMode === "command") {
    // En mode commande réel, on pourrait récupérer le type depuis WooCommerce
    // Pour l'instant, on utilise la valeur configurée ou une valeur par défaut
    documentType = props.documentType || "FACTURE";
  } else {
    // Données fictives pour le mode éditeur
    documentType = props.documentType || "FACTURE";
  }

  // Convertir les valeurs techniques en texte lisible
  const documentTypeLabels: { [key: string]: string } = {
    FACTURE: "FACTURE",
    DEVIS: "DEVIS",
    BON_COMMANDE: "BON DE COMMANDE",
    AVOIR: "AVOIR",
    RELEVE: "RELEVE",
    CONTRAT: "CONTRAT",
  };

  documentType = documentTypeLabels[documentType] || documentType;

  const x =
    textAlign === "center"
      ? element.width / 2
      : textAlign === "right"
      ? element.width - 10
      : 10;
  const y = element.height / 2 + fontSize / 3; // Centrer verticalement

  ctx.fillText(documentType, x, y);
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

  // Écouter les changements de couleur de fond depuis les paramètres
  useEffect(() => {
    debugLog(
      `[Canvas] Background color change detected: ${canvasSettings?.canvasBackgroundColor}`
    );
    const handleBgColorChange = (event: CustomEvent) => {
      debugLog(
        `[Canvas] Custom background color change event received:`,
        event.detail
      );
      // Forcer le re-rendu du canvas avec la nouvelle couleur
      renderCountRef.current += 1;
      // Le canvas se re-rendra automatiquement grâce aux dépendances du useEffect principal
    };

    window.addEventListener(
      "pdfBuilderCanvasBgColorChanged",
      handleBgColorChange as EventListener,
      { passive: true }
    );

    return () => {
      window.removeEventListener(
        "pdfBuilderCanvasBgColorChanged",
        handleBgColorChange as EventListener
      );
    };
  }, [canvasSettings?.canvasBackgroundColor]);

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
      ctx.fillStyle = "#f0f0f0";
      ctx.strokeStyle = "#ccc";
      ctx.lineWidth = 1;
      ctx.fillRect(x, y, logoWidth, logoHeight);
      ctx.strokeRect(x, y, logoWidth, logoHeight);

      // Texte du placeholder
      ctx.fillStyle = "#666";
      ctx.font = "12px Arial";
      ctx.textAlign = "center";
      ctx.fillText(text, x + logoWidth / 2, y + logoHeight / 2 + 4);
    },
    []
  );

  const drawCompanyLogo = useCallback(
    (ctx: CanvasRenderingContext2D, element: Element) => {
      const props = element as ImageElementProperties;
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
      const props = element as TextElementProperties & {
        showBackground?: boolean;
        backgroundColor?: string;
      };
      const text = props.text || "Texte personnalisable";
      const fontSize = props.fontSize || 14;
      const fontFamily = props.fontFamily || "Arial";
      const fontWeight = props.fontWeight || "normal";
      const fontStyle = props.fontStyle || "normal";
      const autoWrap = props.autoWrap !== false; // Par défaut activé

      // Appliquer le fond seulement si showBackground est activé
      if (props.showBackground !== false) {
        ctx.fillStyle = props.backgroundColor || "#e5e7eb";
        ctx.fillRect(0, 0, element.width, element.height);
      }

      ctx.fillStyle = "#000000";
      ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
      ctx.textAlign = "left";

      // Remplacer les variables génériques par des valeurs par défaut
      const processedText = text
        .replace(/\[date\]/g, new Date().toLocaleDateString("fr-FR"))
        .replace(/\[nom\]/g, "Dupont")
        .replace(/\[prenom\]/g, "Marie")
        .replace(/\[entreprise\]/g, "Ma Société")
        .replace(/\[telephone\]/g, "+33 1 23 45 67 89")
        .replace(/\[email\]/g, "contact@masociete.com")
        .replace(/\[site\]/g, "www.masociete.com")
        .replace(/\[ville\]/g, "Paris")
        .replace(/\[siret\]/g, "123 456 789 00012")
        .replace(/\[tva\]/g, "FR 12 345 678 901")
        .replace(/\[capital\]/g, "10 000")
        .replace(/\[rcs\]/g, "Paris B 123 456 789");

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
      const props = element as MentionsElementProperties;
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
          drawProductTable(ctx, element, currentState);
          break;
        case "customer_info":
          debugLog(`[Canvas] Rendering customer info element: ${element.id}`);
          drawCustomerInfo(ctx, element, currentState);
          break;
        case "company_info":
          debugLog(`[Canvas] Rendering company info element: ${element.id}`);
          drawCompanyInfo(ctx, element, canvasSettings);
          break;
        case "company_logo":
          debugLog(`[Canvas] Rendering company logo element: ${element.id}`);
          drawCompanyLogo(ctx, element);
          break;
        case "order-number":
        case "order_number":
          debugLog(`[Canvas] Rendering order number element: ${element.id}`);
          drawOrderNumber(ctx, element, currentState);
          break;
        case "document_type":
          debugLog(`[Canvas] Rendering document type element: ${element.id}`);
          drawDocumentType(ctx, element, currentState);
          break;
        case "dynamic-text":
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
          ctx.strokeStyle = "#000000";
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
      ctx.strokeStyle = "#007acc";
      ctx.lineWidth = 1;
      ctx.setLineDash([5, 5]);
      ctx.strokeRect(minX - 2, minY - 2, maxX - minX + 4, maxY - minY + 4);

      // Poignées de redimensionnement (conditionnées par les settings)
      if (canvasSettings?.selectionShowHandles) {
        const handleSize = 6;
        ctx.fillStyle = "#007acc";
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
      if (canvasSettings?.selectionRotationEnabled) {
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
          ctx.fillStyle = "#007acc";
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
          ctx.fillStyle = "#007acc";
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
              "order-number": { width: 200, height: 40 },
              document_type: { width: 150, height: 30 },
              "dynamic-text": { width: 200, height: 60 },
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
            (element.type === "text" || element.type === "dynamic-text")
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
            !(element?.type === "text" || element?.type === "dynamic-text"),
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
      ctx.strokeStyle = "#007acc";
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
    const canvasBgColor = normalizeColor(
      canvasSettings?.canvasBackgroundColor || "#ffffff"
    );
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
        ctx.strokeStyle = "#0066cc";
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
        ctx.strokeStyle = "#0066cc";
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

  // Calculate border style based on canvas settings
  const borderStyle = isDragOver
    ? "2px solid #007acc"
    : canvasSettings?.borderWidth && canvasSettings.borderWidth > 0
    ? `${canvasSettings.borderWidth}px solid ${
        canvasSettings?.borderColor || "#cccccc"
      }`
    : "none";

  // Calculate canvas display size based on zoom
  const zoomScale = state.canvas.zoom / 100;
  const displayWidth = width * zoomScale;
  const displayHeight = height * zoomScale;

  debugLog(
    `[Canvas] Rendering canvas element - Display size: ${displayWidth}x${displayHeight}, Border: ${borderStyle}, Drag over: ${isDragOver}`
  );

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
          transition: "border-color 0.2s ease, box-shadow 0.2s ease",
          boxShadow: isDragOver ? "0 0 0 2px rgba(0, 122, 204, 0.2)" : "none",
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
            backgroundColor: canvasSettings?.canvasBackgroundColor || "#ffffff",
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


