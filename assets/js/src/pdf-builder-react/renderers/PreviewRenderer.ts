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
      console.error('❌ [PREVIEW RENDER] Canvas 2D context not available');
      throw new Error('Canvas 2D context not available');
    }

    // Définir les dimensions du canvas
    canvas.width = width;
    canvas.height = height;

    // Fond blanc
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    // Rendre chaque élément
    elements.forEach(element => {
      this.renderElement(ctx, element, dataProvider);
    });
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
        console.log('🏢 [COMPANY LOGO] Starting company logo render');
        this.renderCompanyLogo(ctx, props);
        console.log('🏢 [COMPANY LOGO] Company logo render completed');
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
    console.log('🏢 [COMPANY LOGO] Props received:', props);

    // Essayer de récupérer l'URL du logo depuis les paramètres WordPress
    let logoUrl = props.src || props.logoUrl || props.imageUrl;
    console.log('🏢 [COMPANY LOGO] Initial logoUrl from props:', logoUrl);

    // Si pas d'URL définie, essayer de récupérer depuis WordPress/WooCommerce
    if (!logoUrl) {
      // Essayer depuis pdfBuilderData (paramètres personnalisés)
      if ((window as any).pdfBuilderData?.companyLogo) {
        logoUrl = (window as any).pdfBuilderData.companyLogo;
        console.log('🏢 [COMPANY LOGO] Found logoUrl from pdfBuilderData:', logoUrl);
      }

      // Essayer depuis les options WooCommerce
      if (!logoUrl && (window as any).woocommerce_settings?.companyLogo) {
        logoUrl = (window as any).woocommerce_settings.companyLogo;
        console.log('🏢 [COMPANY LOGO] Found logoUrl from woocommerce_settings:', logoUrl);
      }

      // Essayer depuis les options WordPress générales
      if (!logoUrl && (window as any).wpSettings?.siteLogo) {
        logoUrl = (window as any).wpSettings.siteLogo;
        console.log('🏢 [COMPANY LOGO] Found logoUrl from wpSettings:', logoUrl);
      }

      // URL de fallback pour les tests - un logo générique
      if (!logoUrl) {
        logoUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjgwIiB2aWV3Qm94PSIwIDAgMTUwIDgwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cmVjdCB3aWR0aD0iMTUwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjZjhmOWZhIiBzdHJva2U9IiNkZWUyZTYiIHN0cm9rZS13aWR0aD0iMSIvPgo8dGV4dCB4PSI3NSIgeT0iNDAiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzZjNzU3ZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zNWVtIj5Db21wYW55IExvZ288L3RleHQ+Cjwvc3ZnPgo=';
        console.log('🏢 [COMPANY LOGO] Using fallback logo URL');
      }
    }

    console.log('🏢 [COMPANY LOGO] Final logoUrl:', logoUrl);

    if (logoUrl) {
      console.log('🏢 [COMPANY LOGO] Loading image from URL');
      const img = new Image();
      img.onload = () => {
        console.log('🏢 [COMPANY LOGO] Image loaded successfully, size:', img.width, 'x', img.height);
        // Calculer les dimensions pour respecter le ratio et le fit
        const imgRatio = img.width / img.height;
        const containerRatio = props.width / props.height;
        let drawWidth = props.width;
        let drawHeight = props.height;
        let drawX = 0;
        let drawY = 0;

        if (props.fit === 'contain') {
          if (imgRatio > containerRatio) {
            // Image plus large que le conteneur
            drawHeight = props.width / imgRatio;
            drawY = (props.height - drawHeight) / 2;
          } else {
            // Image plus haute que le conteneur
            drawWidth = props.height * imgRatio;
            drawX = (props.width - drawWidth) / 2;
          }
        } else if (props.fit === 'cover') {
          if (imgRatio > containerRatio) {
            drawWidth = props.height * imgRatio;
            drawX = (props.width - drawWidth) / 2;
          } else {
            drawHeight = props.width / imgRatio;
            drawY = (props.height - drawHeight) / 2;
          }
        }

        // Gestion de l'alignement
        if (props.alignment === 'center') {
          drawX = (props.width - drawWidth) / 2;
        } else if (props.alignment === 'right') {
          drawX = props.width - drawWidth;
        }

        console.log('🏢 [COMPANY LOGO] Drawing at:', drawX, drawY, 'Size:', drawWidth, 'x', drawHeight);
        ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
      };
      img.onerror = () => {
        console.log('🏢 [COMPANY LOGO] Image failed to load, rendering placeholder');
        // En cas d'erreur de chargement, afficher le placeholder
        this.renderCompanyLogoPlaceholder(ctx, props);
      };
      img.src = logoUrl;
    } else {
      console.log('🏢 [COMPANY LOGO] No logoUrl found, rendering placeholder');
      // Afficher le placeholder si pas d'URL
      this.renderCompanyLogoPlaceholder(ctx, props);
    }
  }

  /**
   * Rend le placeholder du logo de l'entreprise
   */
  private static renderCompanyLogoPlaceholder(ctx: CanvasRenderingContext2D, props: any): void {
    // Fond gris clair
    ctx.fillStyle = '#f8f9fa';
    ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 1;
    ctx.fillRect(0, 0, props.width, props.height);
    ctx.strokeRect(0, 0, props.width, props.height);

    // Icône de bâtiment simplifiée
    ctx.fillStyle = '#6c757d';
    ctx.strokeStyle = '#6c757d';
    ctx.lineWidth = 2;

    const centerX = props.width / 2;
    const centerY = props.height / 2;
    const iconSize = Math.min(props.width, props.height) * 0.4;

    // Dessiner un bâtiment simple
    const buildingWidth = iconSize * 0.8;
    const buildingHeight = iconSize * 0.6;
    const buildingX = centerX - buildingWidth / 2;
    const buildingY = centerY - buildingHeight / 2;

    // Contour du bâtiment
    ctx.strokeRect(buildingX, buildingY, buildingWidth, buildingHeight);

    // Lignes horizontales pour les étages
    const floorHeight = buildingHeight / 3;
    for (let i = 1; i < 3; i++) {
      ctx.beginPath();
      ctx.moveTo(buildingX, buildingY + i * floorHeight);
      ctx.lineTo(buildingX + buildingWidth, buildingY + i * floorHeight);
      ctx.stroke();
    }

    // Lignes verticales pour les fenêtres
    const windowWidth = buildingWidth / 3;
    for (let i = 1; i < 3; i++) {
      ctx.beginPath();
      ctx.moveTo(buildingX + i * windowWidth, buildingY);
      ctx.lineTo(buildingX + i * windowWidth, buildingY + buildingHeight);
      ctx.stroke();
    }

    // Texte "LOGO"
    ctx.fillStyle = '#495057';
    ctx.font = `bold ${Math.max(10, iconSize * 0.15)}px Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'bottom';
    ctx.fillText('LOGO ENTREPRISE', centerX, centerY + iconSize * 0.4);
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