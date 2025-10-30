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

      case 'customer_info':
        this.renderCustomerInfo(ctx, props, dataProvider);
        break;

      case 'company_info':
        this.renderCompanyInfo(ctx, props, dataProvider);
        break;

      case 'mentions':
        this.renderMentions(ctx, props, dataProvider);
        break;

      case 'document_type':
        this.renderDocumentType(ctx, props, dataProvider);
        break;

      case 'line':
        this.renderLine(ctx, props);
        break;

      case 'dynamic-text':
        this.renderDynamicText(ctx, props, dataProvider);
        break;

      // Ajouter d'autres types d'éléments selon les besoins

      default:
        // Rendu de fallback pour les éléments inconnus
        this.renderRectangle(ctx, { ...props, fillColor: '#ffcccc', strokeColor: '#ff0000' });
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
    let text = this.replaceVariables(props.text || 'Texte', dataProvider);

    const lines = text.split('\n');
    let y = 0;
    const lineHeight = (props.lineHeight || props.fontSize || 14) * 1.2; // Ajouter un espacement entre les lignes

    lines.forEach((line: string, index: number) => {
      let x = 0;
      
      if (textAlign === 'center') {
        x = props.width / 2;
      } else if (textAlign === 'right') {
        x = props.width;
      }
      
      // Vérifier que le texte n'est pas vide
      if (line.trim()) {
        ctx.fillText(line, x, y);
      }
      
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
   * Rend les informations client
   */
  private static renderCustomerInfo(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    const customerInfo = [
      dataProvider.getVariableValue('customer_name'),
      dataProvider.getVariableValue('customer_email'),
      dataProvider.getVariableValue('customer_phone'),
      dataProvider.getVariableValue('customer_address')
    ].filter(info => info).join('\n');

    this.renderText(ctx, { ...props, text: customerInfo }, dataProvider);
  }

  /**
   * Rend les informations entreprise
   */
  private static renderCompanyInfo(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    const companyInfo = [
      dataProvider.getVariableValue('company_name'),
      dataProvider.getVariableValue('company_address'),
      dataProvider.getVariableValue('company_phone'),
      dataProvider.getVariableValue('company_email'),
      dataProvider.getVariableValue('company_vat')
    ].filter(info => info).join('\n');

    this.renderText(ctx, { ...props, text: companyInfo }, dataProvider);
  }

  /**
   * Rend les mentions légales
   */
  private static renderMentions(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    const mentions = props.text || dataProvider.getVariableValue('mentions') || 'Mentions légales';
    this.renderText(ctx, { ...props, text: mentions }, dataProvider);
  }

  /**
   * Rend le type de document
   */
  private static renderDocumentType(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    const docType = dataProvider.getVariableValue('document_type') || 'Document';
    this.renderText(ctx, { ...props, text: docType }, dataProvider);
  }

  /**
   * Rend une ligne
   */
  private static renderLine(ctx: CanvasRenderingContext2D, props: any): void {
    ctx.strokeStyle = props.color || props.borderColor || '#000000';
    ctx.lineWidth = props.height || props.borderWidth || 1;

    // Dessiner une ligne horizontale
    const y = props.height ? props.height / 2 : 1;
    ctx.beginPath();
    ctx.moveTo(0, y);
    ctx.lineTo(props.width, y);
    ctx.stroke();
  }

  /**
   * Rend du texte dynamique (alias pour renderText)
   */
  private static renderDynamicText(
    ctx: CanvasRenderingContext2D,
    props: any,
    dataProvider: DataProvider
  ): void {
    this.renderText(ctx, props, dataProvider);
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