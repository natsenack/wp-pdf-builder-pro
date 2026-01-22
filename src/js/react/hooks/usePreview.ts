import { useState, useCallback } from 'react';

console.log('[REACT USE PREVIEW HOOK] Hook file loaded and executing');

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
 * Hook React usePreview pour gérer l'aperçu PDF
 * Implémente les exigences de l'étape 1.5 du roadmap
 */
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
    const finalFormat = options.format || format;
    const quality = options.quality || 150;

    console.log('[REACT PREVIEW] generatePreview called with templateData:', templateData);
    console.log('[REACT PREVIEW] generatePreview options:', options);
    console.log('[REACT PREVIEW] generatePreview finalFormat:', finalFormat, 'quality:', quality);
    console.log('[REACT PREVIEW] window.pdfPreviewAPI available:', typeof window.pdfPreviewAPI);

    setIsGenerating(true);
    setError(null);
    setPreviewUrl(null);

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

      console.log('[REACT PREVIEW] About to call window.pdfPreviewAPI.generateEditorPreview');

      // Générer l'aperçu
      const result = await window.pdfPreviewAPI.generateEditorPreview(
        templateData,
        { format: finalFormat, quality }
      );

      console.log('[REACT PREVIEW] generateEditorPreview result:', result);
      console.log('[REACT PREVIEW] Result type:', typeof result);
      console.log('[REACT PREVIEW] Result is object:', result && typeof result === 'object');
      if (result && typeof result === 'object') {
        console.log('[REACT PREVIEW] Result has success:', 'success' in result, result.success);
        console.log('[REACT PREVIEW] Result has image_url:', 'image_url' in result, typeof result.image_url);
        console.log('[REACT PREVIEW] Result has error:', 'error' in result, result.error);
        console.log('[REACT PREVIEW] Full result object:', JSON.stringify(result, null, 2));
      }

      if (result && typeof result === 'object' && 'success' in result && result.success && 'image_url' in result && typeof result.image_url === 'string') {
        console.log('[REACT PREVIEW] Preview generated successfully, image_url:', result.image_url);
        if (finalFormat === 'pdf') {
          // Pour PDF, ouvrir dans un nouvel onglet
          console.log('[REACT PREVIEW] Opening PDF in new tab');
          window.open(result.image_url, '_blank');
          setPreviewUrl(null); // Ne pas afficher dans la modale
        } else {
          // Pour PNG/JPG, afficher dans la modale
          console.log('[REACT PREVIEW] Setting preview URL for modal display');
          setPreviewUrl(result.image_url);
        }
      } else {
        console.log('[REACT PREVIEW] Result validation failed, checking error message');
        const errorMsg = (result && typeof result === 'object' && 'error' in result && typeof result.error === 'string') ? result.error : 'Erreur lors de la génération de l\'aperçu';
        console.error('[REACT PREVIEW] Invalid result format, throwing error:', errorMsg);
        console.error('[REACT PREVIEW] Result validation details:');
        console.error('[REACT PREVIEW] - result exists:', !!result);
        console.error('[REACT PREVIEW] - is object:', result && typeof result === 'object');
        console.error('[REACT PREVIEW] - has success key:', result && 'success' in result);
        console.error('[REACT PREVIEW] - success value:', result && result.success);
        console.error('[REACT PREVIEW] - has image_url key:', result && 'image_url' in result);
        console.error('[REACT PREVIEW] - image_url type:', result && typeof result.image_url);
        throw new Error(errorMsg);
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erreur inconnue lors de la génération';
      console.error('[REACT PREVIEW] CATCH BLOCK - Exception caught:', errorMessage);
      console.error('[REACT PREVIEW] CATCH BLOCK - Full error object:', err);
      console.error('[REACT PREVIEW] CATCH BLOCK - Error stack:', err instanceof Error ? err.stack : 'No stack available');
      console.error('[REACT PREVIEW] CATCH BLOCK - Error type:', typeof err);
      console.error('[REACT PREVIEW] CATCH BLOCK - Error constructor:', err?.constructor?.name || 'Unknown');

      setError(errorMessage);
    } finally {
      console.log('[REACT PREVIEW] FINALLY BLOCK - Setting isGenerating to false');
      setIsGenerating(false);
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



