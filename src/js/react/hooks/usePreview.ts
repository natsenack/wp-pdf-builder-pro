import { useState, useCallback } from 'react';

console.log('[REACT USE PREVIEW HOOK] ===== FILE LOADED =====');
console.log('[REACT USE PREVIEW HOOK] Hook file loaded and executing at:', new Date().toISOString());
console.log('[REACT USE PREVIEW HOOK] React available:', typeof React);
console.log('[REACT USE PREVIEW HOOK] useState available:', typeof useState);
console.log('[REACT USE PREVIEW HOOK] useCallback available:', typeof useCallback);

export interface PreviewOptions {
  format?: 'png' | 'jpg' | 'pdf';
  quality?: number;
}

export interface PreviewResult {
  success: boolean;
  image_url?: string;
  error?: string;
}

export interface UsePreviewReturn {
  // État de la modale
  isModalOpen: boolean;
  openModal: () => void;
  closeModal: () => void;

  // État de génération
  isGenerating: boolean;
  previewUrl: string | null;
  error: string | null;

  // Options
  format: 'png' | 'jpg' | 'pdf';
  setFormat: (format: 'png' | 'jpg' | 'pdf') => void;

  // Actions
  generatePreview: (templateData: Record<string, unknown>, options?: PreviewOptions) => Promise<void>;
  clearPreview: () => void;
}

/**
 * Adapte le contenu du template selon le format demandé
 * Pour PNG/JPEG : simplification des éléments complexes
 * Pour PDF : conservation de tous les éléments avec propriétés complètes
 */
function adaptTemplateForFormat(templateData: Record<string, unknown>, format: 'png' | 'jpg' | 'pdf'): Record<string, unknown> {
  console.log('[HOOK USE PREVIEW] ===== ADAPTING TEMPLATE FOR FORMAT =====');
  console.log('[HOOK USE PREVIEW] Original format:', format);
  console.log('[HOOK USE PREVIEW] Original elements count:', (templateData.elements as unknown[])?.length || 0);

  if (format === 'pdf') {
    // Pour PDF, on garde tout le contenu tel quel
    console.log('[HOOK USE PREVIEW] PDF format - keeping all elements as-is');
    return { ...templateData };
  }

  // Pour PNG/JPEG, on adapte le contenu
  const adaptedData = { ...templateData };
  const elements = templateData.elements as unknown[];

  if (Array.isArray(elements)) {
    adaptedData.elements = elements.map((element: unknown, index: number) => {
      if (!element || typeof element !== 'object') {
        return element;
      }

      const el = element as Record<string, unknown>;
      const adaptedElement = { ...el };

      // Adapter selon le type d'élément pour les formats image
      const elementType = el.type as string;

      console.log(`[HOOK USE PREVIEW] Adapting element ${index} of type: ${elementType}`);

      switch (elementType) {
        case 'image':
          // Pour les images dans PNG/JPEG, réduire la qualité si spécifiée
          if (adaptedElement.imageQuality) {
            adaptedElement.imageQuality = Math.min(adaptedElement.imageQuality as number, 80);
            console.log(`[HOOK USE PREVIEW] Reduced image quality to: ${adaptedElement.imageQuality}`);
          }
          break;

        case 'text':
        case 'dynamic_text':
        case 'order_number':
        case 'woocommerce_order_date':
        case 'woocommerce_invoice_number':
          // Pour les textes, simplifier les effets complexes
          if (adaptedElement.textShadow) {
            adaptedElement.textShadow = undefined;
            console.log(`[HOOK USE PREVIEW] Removed text shadow for element ${index}`);
          }
          if (adaptedElement.textStroke) {
            adaptedElement.textStroke = undefined;
            console.log(`[HOOK USE PREVIEW] Removed text stroke for element ${index}`);
          }
          break;

        case 'shape':
        case 'rectangle':
        case 'circle':
          // Pour les formes, simplifier les gradients complexes
          if (adaptedElement.gradient && typeof adaptedElement.gradient === 'object') {
            const gradient = adaptedElement.gradient as Record<string, unknown>;
            if (gradient.type === 'radial' || gradient.stops && (gradient.stops as unknown[]).length > 2) {
              // Simplifier en gradient linéaire simple ou couleur unie
              adaptedElement.fillColor = gradient.stops?.[0]?.color || adaptedElement.fillColor;
              adaptedElement.gradient = undefined;
              console.log(`[HOOK USE PREVIEW] Simplified gradient for shape element ${index}`);
            }
          }
          break;

        case 'barcode':
        case 'qrcode':
          // Pour les codes-barres, réduire la complexité
          if (adaptedElement.errorCorrection) {
            adaptedElement.errorCorrection = 'M'; // Niveau moyen au lieu de élevé
            console.log(`[HOOK USE PREVIEW] Reduced barcode error correction for element ${index}`);
          }
          break;

        default:
          // Pour les autres types, garder tel quel
          console.log(`[HOOK USE PREVIEW] Keeping element ${index} of type ${elementType} as-is`);
          break;
      }

      return adaptedElement;
    });

    console.log('[HOOK USE PREVIEW] Adapted elements count:', adaptedData.elements.length);
  }

  console.log('[HOOK USE PREVIEW] ===== TEMPLATE ADAPTATION COMPLETED =====');
  return adaptedData;
}
export function usePreview(): UsePreviewReturn {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [isGenerating, setIsGenerating] = useState(false);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [format, setFormat] = useState<'png' | 'jpg' | 'pdf'>('png');

  const openModal = useCallback(() => {

    setIsModalOpen(true);
    setError(null);
    setPreviewUrl(null);
  }, []);

  const closeModal = useCallback(() => {

    setIsModalOpen(false);
    setIsGenerating(false);
    setPreviewUrl(null);
    setError(null);
  }, []);

  const clearPreview = useCallback(() => {
    setPreviewUrl(null);
    setError(null);
  }, []);

  const generatePreview = useCallback(async (
    templateData: Record<string, unknown>,
    options: PreviewOptions = {}
  ) => {
    console.log('[HOOK USE PREVIEW] ===== STARTING PREVIEW GENERATION =====');
    console.log('[HOOK USE PREVIEW] generatePreview called at:', new Date().toISOString());
    console.log('[HOOK USE PREVIEW] templateData received:', templateData);
    console.log('[HOOK USE PREVIEW] templateData keys:', Object.keys(templateData));
    console.log('[HOOK USE PREVIEW] templateData elements count:', (templateData.elements as unknown[])?.length || 0);
    console.log('[HOOK USE PREVIEW] options received:', options);
    console.log('[HOOK USE PREVIEW] Current hook state:');
    console.log('[HOOK USE PREVIEW] - isModalOpen:', isModalOpen);
    console.log('[HOOK USE PREVIEW] - isGenerating:', isGenerating);
    console.log('[HOOK USE PREVIEW] - previewUrl:', previewUrl);
    console.log('[HOOK USE PREVIEW] - error:', error);
    console.log('[HOOK USE PREVIEW] - format:', format);

    const finalFormat = options.format || format;
    const quality = options.quality || 150;

    console.log('[HOOK USE PREVIEW] Final computed values:');
    console.log('[HOOK USE PREVIEW] - finalFormat:', finalFormat);
    console.log('[HOOK USE PREVIEW] - quality:', quality);
    console.log('[HOOK USE PREVIEW] window.pdfPreviewAPI available:', typeof window.pdfPreviewAPI);

    setIsGenerating(true);
    setError(null);
    setPreviewUrl(null);

    console.log('[HOOK USE PREVIEW] State updated - setIsGenerating(true), cleared error and previewUrl');

    try {
      // Vérifier s'il y a du contenu dans le template
      const hasContent = (templateData.elements as unknown[]) && (templateData.elements as unknown[]).length > 0;
      console.log('[REACT PREVIEW] Template has content:', hasContent, 'elements count:', (templateData.elements as unknown[])?.length || 0);

      if (!hasContent) {
        console.error('[REACT PREVIEW] No content in template, throwing error');
        throw new Error('Aucun contenu dans le template. Ajoutez des éléments avant de générer un aperçu.');
      }

      // Vérifier la disponibilité de l'API Preview
      if (typeof window.pdfPreviewAPI === 'undefined') {
        console.error('[REACT PREVIEW] pdfPreviewAPI not available');
        throw new Error('API Preview non disponible. Vérifiez que les scripts sont chargés.');
      }

      console.log('[HOOK USE PREVIEW] About to call window.pdfPreviewAPI.generateEditorPreview');
      console.log('[HOOK USE PREVIEW] Calling with parameters:');
      console.log('[HOOK USE PREVIEW] - templateData:', JSON.stringify(templateData, null, 2));
      console.log('[HOOK USE PREVIEW] - options:', { format: finalFormat, quality });

      // Adapter le template selon le format demandé
      const adaptedTemplateData = adaptTemplateForFormat(templateData, finalFormat);
      console.log('[HOOK USE PREVIEW] Adapted templateData:', JSON.stringify(adaptedTemplateData, null, 2));

      // Générer l'aperçu
      const result = await window.pdfPreviewAPI.generateEditorPreview(
        adaptedTemplateData,
        { format: finalFormat, quality }
      );

      console.log('[HOOK USE PREVIEW] ===== API CALL COMPLETED =====');
      console.log('[HOOK USE PREVIEW] generateEditorPreview result received:', result);
      console.log('[HOOK USE PREVIEW] Result type:', typeof result);
      console.log('[HOOK USE PREVIEW] Result is object:', result && typeof result === 'object');
      if (result && typeof result === 'object') {
        console.log('[HOOK USE PREVIEW] Result keys:', Object.keys(result));
        console.log('[HOOK USE PREVIEW] Result has success:', 'success' in result, result.success);
        console.log('[HOOK USE PREVIEW] Result has image_url:', 'image_url' in result, typeof result.image_url);
        console.log('[HOOK USE PREVIEW] Result has error:', 'error' in result, result.error);
        console.log('[HOOK USE PREVIEW] Result has preview_url:', 'preview_url' in result, result.preview_url);
        console.log('[HOOK USE PREVIEW] Full result object:', JSON.stringify(result, null, 2));
      }

      if (result && typeof result === 'object' && 'success' in result && result.success && 'data' in result && result.data && 'image_url' in result.data && typeof result.data.image_url === 'string') {
        console.log('[HOOK USE PREVIEW] ===== VALIDATION PASSED =====');
        console.log('[HOOK USE PREVIEW] Preview generated successfully, image_url:', result.data.image_url);
        if (finalFormat === 'pdf') {
          // Pour PDF, ouvrir dans un nouvel onglet
          console.log('[HOOK USE PREVIEW] Opening PDF in new tab');
          window.open(result.data.image_url, '_blank');
          setPreviewUrl(null); // Ne pas afficher dans la modale
        } else {
          // Pour PNG/JPG, afficher dans la modale
          console.log('[HOOK USE PREVIEW] Setting preview URL for modal display');
          setPreviewUrl(result.data.image_url);
        }
      } else {
        console.log('[HOOK USE PREVIEW] ===== VALIDATION FAILED =====');
        console.log('[HOOK USE PREVIEW] Result validation failed, checking error message');
        console.log('[HOOK USE PREVIEW] Validation checks:');
        console.log('[HOOK USE PREVIEW] - result exists:', !!result);
        console.log('[HOOK USE PREVIEW] - is object:', result && typeof result === 'object');
        console.log('[HOOK USE PREVIEW] - has success key:', result && 'success' in result);
        console.log('[HOOK USE PREVIEW] - success value:', result && result.success);
        console.log('[HOOK USE PREVIEW] - has data key:', result && 'data' in result);
        console.log('[HOOK USE PREVIEW] - data exists:', result && result.data);
        console.log('[HOOK USE PREVIEW] - data has image_url key:', result && result.data && 'image_url' in result.data);
        console.log('[HOOK USE PREVIEW] - image_url type:', result && result.data && typeof result.data.image_url);
        console.log('[HOOK USE PREVIEW] - image_url value:', result && result.data && result.data.image_url);

        const errorMsg = (result && typeof result === 'object' && 'error' in result && typeof result.error === 'string') ? result.error : 'Erreur lors de la génération de l\'aperçu';
        console.error('[HOOK USE PREVIEW] Invalid result format, throwing error:', errorMsg);
        console.error('[HOOK USE PREVIEW] Result validation details:');
        console.error('[HOOK USE PREVIEW] - result exists:', !!result);
        console.error('[HOOK USE PREVIEW] - is object:', result && typeof result === 'object');
        console.error('[HOOK USE PREVIEW] - has success key:', result && 'success' in result);
        console.error('[HOOK USE PREVIEW] - success value:', result && result.success);
        console.error('[HOOK USE PREVIEW] - has data key:', result && 'data' in result);
        console.error('[HOOK USE PREVIEW] - data exists:', result && result.data);
        console.error('[HOOK USE PREVIEW] - data has image_url key:', result && result.data && 'image_url' in result.data);
        console.error('[HOOK USE PREVIEW] - image_url type:', result && result.data && typeof result.data.image_url);
        throw new Error(errorMsg);
      }
    } catch (err) {
      console.error('[HOOK USE PREVIEW] ===== CATCH BLOCK EXECUTED =====');
      console.error('[HOOK USE PREVIEW] Exception caught at:', new Date().toISOString());
      const errorMessage = err instanceof Error ? err.message : 'Erreur inconnue lors de la génération';
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Exception caught:', errorMessage);
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Full error object:', err);
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Error stack:', err instanceof Error ? err.stack : 'No stack available');
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Error type:', typeof err);
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Error constructor:', err?.constructor?.name || 'Unknown');
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Error instanceof Error:', err instanceof Error);
      console.error('[HOOK USE PREVIEW] CATCH BLOCK - Error toString():', err?.toString() || 'No toString method');

      setError(errorMessage);
      console.error('[HOOK USE PREVIEW] State updated - setError called with:', errorMessage);
    } finally {
      console.log('[HOOK USE PREVIEW] ===== FINALLY BLOCK =====');
      console.log('[HOOK USE PREVIEW] Setting isGenerating to false');
      setIsGenerating(false);
      console.log('[HOOK USE PREVIEW] ===== PREVIEW GENERATION PROCESS COMPLETED =====');
    }
  }, [format]);

  return {
    // État de la modale
    isModalOpen,
    openModal,
    closeModal,

    // État de génération
    isGenerating,
    previewUrl,
    error,

    // Options
    format,
    setFormat,

    // Actions
    generatePreview,
    clearPreview
  };
}

export default usePreview;



