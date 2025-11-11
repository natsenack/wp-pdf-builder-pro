/**
 * PreviewImageAPI.ts
 *
 * API pour générer des images de prévisualisation côté serveur PHP
 * Utilise l'action AJAX pour créer des rendus PNG/JPG via TCPDF
 *
 * @author AI Assistant
 * @since 1.1.0 (Phase 3.0)
 */

declare global {
  interface Window {
    ajaxurl?: string;
  }
}

import { debugError, debugWarn } from '../utils/debug';

export interface PreviewImageOptions {
  orderId: number;
  templateId: number;
  format?: 'png' | 'jpg' | 'pdf';
  width?: number;
  height?: number;
}

export interface PreviewImageResponse {
  success: boolean;
  data?: {
    image: string; // Data URL (base64)
    format: string;
    type: string;
  };
  error?: string;
}

/**
 * Classe pour gérer la génération d'images de prévisualisation
 */
export class PreviewImageAPI {
  private static instance: PreviewImageAPI;
  private nonce: string = '';
  private isGenerating: boolean = false;
  private cachedImages: Map<string, string> = new Map();

  private constructor() {
    // Récupérer le nonce depuis le document
    const nonceElement = document.getElementById('pdf_builder_nonce');
    if (nonceElement) {
      this.nonce = nonceElement.getAttribute('data-nonce') || '';
    }
  }

  /**
   * Récupère l'instance singleton
   */
  static getInstance(): PreviewImageAPI {
    if (!PreviewImageAPI.instance) {
      PreviewImageAPI.instance = new PreviewImageAPI();
    }
    return PreviewImageAPI.instance;
  }

  /**
   * Génère une image de prévisualisation du PDF
   */
  async generatePreviewImage(
    options: PreviewImageOptions
  ): Promise<PreviewImageResponse> {
    // Vérifier le cache
    const cacheKey = this.getCacheKey(options);
    if (this.cachedImages.has(cacheKey)) {

      return {
        success: true,
        data: {
          image: this.cachedImages.get(cacheKey)!,
          format: options.format || 'png',
          type: `image/${options.format || 'png'}`
        }
      };
    }

    // Éviter les appels multiples simultanés
    if (this.isGenerating) {
      debugWarn('[PreviewImageAPI] Génération déjà en cours');
      return {
        success: false,
        error: 'Génération déjà en cours'
      };
    }

    this.isGenerating = true;

    try {
      const formData = new FormData();
      formData.append('action', 'pdf_builder_preview_image');
      formData.append('nonce', this.nonce);
      formData.append('order_id', String(options.orderId));
      formData.append('template_id', String(options.templateId));
      if (options.format) {
        formData.append('format', options.format);
      }

      const response = await fetch(
        window.ajaxurl || '/wp-admin/admin-ajax.php',
        {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        }
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${result.data}`);
      }

      if (!result.success) {
        throw new Error(result.data || 'Erreur de génération');
      }

      // Mettre en cache
      const imageData = result.data.image;
      this.cachedImages.set(cacheKey, imageData);


      return {
        success: true,
        data: result.data
      };

    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Erreur inconnue';
      debugError('[PreviewImageAPI] Erreur:', errorMessage);

      return {
        success: false,
        error: errorMessage
      };

    } finally {
      this.isGenerating = false;
    }
  }

  /**
   * Valide les données avant la génération
   */
  validateOptions(options: PreviewImageOptions): boolean {
    if (!options.orderId || options.orderId <= 0) {
      debugError('[PreviewImageAPI] Order ID invalide');
      return false;
    }

    if (!options.templateId || options.templateId <= 0) {
      debugError('[PreviewImageAPI] Template ID invalide');
      return false;
    }

    if (options.format && !['png', 'jpg', 'pdf'].includes(options.format)) {
      debugWarn('[PreviewImageAPI] Format invalide, utilisation de png par défaut');
    }

    return true;
  }

  /**
   * Génère une clé de cache
   */
  private getCacheKey(options: PreviewImageOptions): string {
    return `preview_${options.orderId}_${options.templateId}_${options.format || 'png'}`;
  }

  /**
   * Vide le cache
   */
  clearCache(): void {
    this.cachedImages.clear();

  }

  /**
   * Vide le cache pour une commande spécifique
   */
  clearCacheForOrder(orderId: number): void {
    const keysToDelete: string[] = [];
    for (const [key] of this.cachedImages) {
      if (key.includes(`_${orderId}_`)) {
        keysToDelete.push(key);
      }
    }
    keysToDelete.forEach(key => this.cachedImages.delete(key));

  }

  /**
   * Télécharge l'image en tant que fichier
   */
  async downloadPreviewImage(
    imageDataUrl: string,
    filename: string
  ): Promise<void> {
    try {
      const link = document.createElement('a');
      link.href = imageDataUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } catch (error) {
      debugError('[PreviewImageAPI] Erreur lors du téléchargement:', error);
      throw error;
    }
  }
}

export default PreviewImageAPI.getInstance();
