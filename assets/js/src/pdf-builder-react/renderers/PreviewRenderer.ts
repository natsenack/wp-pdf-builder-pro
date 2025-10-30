/**
 * PreviewRenderer - Classe unifiée pour le rendu d'aperçu PDF
 *
 * Gère le rendu Canvas 2D pour les aperçus PDF avec injection dynamique
 * de données selon le mode (Canvas fictif / Metabox réel).
 *
 * Phase 2.3.1 - Infrastructure de rendu unifié
 */

import { Element } from '../types/elements';

export interface DataProvider {
  getVariableValue(variable: string): string;
  getMode(): 'canvas' | 'metabox';
}

export interface RenderOptions {
  canvas: HTMLCanvasElement;
  elements: Element[];
  dataProvider: DataProvider;
  zoom?: number;
  width?: number;
  height?: number;
}

export class PreviewRenderer {
  private static readonly A4_WIDTH_PX = 794;  // 210mm * 150 DPI / 25.4
  private static readonly A4_HEIGHT_PX = 1123; // 297mm * 150 DPI / 25.4

  /**
   * Rend un aperçu complet sur le canvas
   */
  static render(options: RenderOptions): void {
    const {
      canvas,
      elements,
      dataProvider,
      zoom = 1.0,
      width = this.A4_WIDTH_PX,
      height = this.A4_HEIGHT_PX
    } = options;

    const ctx = canvas.getContext('2d');
    if (!ctx) {
      throw new Error('Canvas 2D context not available');
    }

    // Définir les dimensions du canvas
    canvas.width = width;
    canvas.height = height;

    // Fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Appliquer le zoom si nécessaire
    if (zoom !== 1.0) {
      ctx.save();
      ctx.scale(zoom, zoom);
    }

    // Rendre chaque élément
    elements.forEach(element => {
      this.renderElement(ctx, element, dataProvider);
    });

    // Restaurer le contexte si zoom appliqué
    if (zoom !== 1.0) {
      ctx.restore();
    }
  }

  /**
   * Rend un élément individuel sur le canvas
   */
  private static renderElement(
    ctx: CanvasRenderingContext2D,
    element: Element,
    dataProvider: DataProvider
  ): void {
    ctx.save();

    // Appliquer la transformation de base (position, rotation)
    ctx.translate(element.x, element.y);
    if (element.rotation) {
      ctx.rotate((element.rotation * Math.PI) / 180);
    }

    const props = element as any;

    switch (element.type) {
      case 'rectangle':
        this.renderRectangle(ctx, props);
        break;

      case 'text':
        this.renderText(ctx, props, dataProvider);
        break;

      case 'company_logo':
        this.renderCompanyLogo(ctx, props);
        break;

      case 'order_number':
        this.renderOrderNumber(ctx, props, dataProvider);
        break;

      case 'customer_name':
        this.renderCustomerName(ctx, props, dataProvider);
        break;

      case 'product_table':
        this.renderProductTable(ctx, props, dataProvider);
        break;

      // Ajouter d'autres types d'éléments selon les besoins

      default:
        console.warn(`Unknown element type: ${element.type}`);
    }

    ctx.restore();
  }

  /**
   * Rend un rectangle
   */
  private static renderRectangle(ctx: CanvasRenderingContext2D, props: any): void {
    ctx.fillStyle = props.fillColor || props.backgroundColor || '#ffffff';
    ctx.strokeStyle = props.strokeColor || props.borderColor || '#000000';
    ctx.lineWidth = props.strokeWidth || props.borderWidth || 1;

    ctx.fillRect(0, 0, props.width, props.height);
    ctx.strokeRect(0, 0, props.width, props.height);
  }

  /**
   * Rend du texte avec remplacement des variables
   */
  private static renderText(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    ctx.fillStyle = props.color || props.textColor || '#000000';
    ctx.font = `${props.fontWeight || 'normal'} ${props.fontSize || 14}px ${props.fontFamily || 'Arial'}`;

    const textAlign = props.textAlign || props.align || 'left';
    ctx.textAlign = textAlign as CanvasTextAlign;
    ctx.textBaseline = 'top';

    // Remplacer les variables dans le texte
    const text = this.replaceVariables(props.text || 'Texte', dataProvider);

    const lines = text.split('\n');
    let y = 0;
    const lineHeight = props.fontSize || 14;

    lines.forEach((line: string) => {
      const x = textAlign === 'center' ? props.width / 2 :
                textAlign === 'right' ? props.width : 0;
      ctx.fillText(line, x, y);
      y += lineHeight;
    });
  }

  /**
   * Rend le logo de l'entreprise
   */
  private static renderCompanyLogo(ctx: CanvasRenderingContext2D, props: any): void {
    if (props.src || props.imageUrl) {
      const img = new Image();
      img.onload = () => {
        ctx.drawImage(img, 0, 0, props.width, props.height);
      };
      img.src = props.src || props.imageUrl;
    } else {
      // Placeholder
      ctx.fillStyle = '#f0f0f0';
      ctx.strokeStyle = '#ccc';
      ctx.lineWidth = 1;
      ctx.fillRect(0, 0, props.width, props.height);
      ctx.strokeRect(0, 0, props.width, props.height);

      // Texte placeholder
      ctx.fillStyle = '#666';
      ctx.font = '12px Arial';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText('LOGO', props.width / 2, props.height / 2);
    }
  }

  /**
   * Rend le numéro de commande
   */
  private static renderOrderNumber(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    const orderNumber = dataProvider.getVariableValue('order_number');
    this.renderText(ctx, { ...props, text: orderNumber }, dataProvider);
  }

  /**
   * Rend le nom du client
   */
  private static renderCustomerName(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    const customerName = dataProvider.getVariableValue('customer_name');
    this.renderText(ctx, { ...props, text: customerName }, dataProvider);
  }

  /**
   * Rend le tableau des produits (version simplifiée)
   */
  private static renderProductTable(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    // Version simplifiée - à améliorer selon les besoins
    const products = dataProvider.getVariableValue('products');
    this.renderText(ctx, { ...props, text: `Produits:\n${products}` }, dataProvider);
  }

  /**
   * Remplace les variables {{variable}} dans un texte
   */
  private static replaceVariables(text: string, dataProvider: DataProvider): string {
    // Regex pour trouver les variables {{variable}}
    const variableRegex = /\{\{([^}]+)\}\}/g;

    return text.replace(variableRegex, (match, variableName) => {
      return dataProvider.getVariableValue(variableName) || match;
    });
  }

  /**
   * Retourne les dimensions A4 par défaut
   */
  static getDefaultDimensions(): { width: number; height: number } {
    return {
      width: this.A4_WIDTH_PX,
      height: this.A4_HEIGHT_PX
    };
  }

  /**
   * Calcule les dimensions en pixels depuis des mm
   */
  static calculatePixelDimensions(widthMm: number, heightMm: number, dpi: number = 150): { width: number; height: number } {
    const factor = this.A4_WIDTH_PX / 210; // 210mm = A4 width
    return {
      width: Math.round(widthMm * factor),
      height: Math.round(heightMm * factor)
    };
  }
}