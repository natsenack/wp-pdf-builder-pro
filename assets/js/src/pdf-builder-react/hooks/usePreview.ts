import { useState, useCallback } from 'react';
import { debugLog } from '../utils/debug';

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
    debugLog('🪟 Ouverture de la modale d\'aperçu');
    setIsModalOpen(true);
    setError(null);
    setPreviewUrl(null);
  }, []);

  const closeModal = useCallback(() => {
    debugLog('🪟 Fermeture de la modale d\'aperçu');
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

    debugLog('🎨 Génération d\'aperçu démarrée', { format: finalFormat, quality });

    setIsGenerating(true);
    setError(null);
    setPreviewUrl(null);

    try {
      // Vérifier s'il y a du contenu dans le template
      const hasContent = (templateData.elements as unknown[]) && (templateData.elements as unknown[]).length > 0;

      if (!hasContent) {
        throw new Error('Aucun contenu dans le template. Ajoutez des éléments avant de générer un aperçu.');
      }

      // Vérifier la disponibilité de l'API Preview
      if (typeof window.pdfPreviewAPI === 'undefined') {
        throw new Error('API Preview non disponible. Vérifiez que les scripts sont chargés.');
      }

      // Générer l'aperçu
      const result: PreviewResult = await window.pdfPreviewAPI.generateEditorPreview(
        templateData,
        { format: finalFormat, quality }
      );

      if (result && result.success && result.image_url) {
        if (finalFormat === 'pdf') {
          // Pour PDF, ouvrir dans un nouvel onglet
          debugLog('📄 Ouverture du PDF dans un nouvel onglet', result.image_url);
          window.open(result.image_url, '_blank');
          setPreviewUrl(null); // Ne pas afficher dans la modale
        } else {
          // Pour PNG/JPG, afficher dans la modale
          debugLog('🖼️ Aperçu généré avec succès', result.image_url);
          setPreviewUrl(result.image_url);
        }
      } else {
        throw new Error(result?.error || 'Erreur lors de la génération de l\'aperçu');
      }
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Erreur inconnue lors de la génération';
      debugLog('❌ Erreur de génération d\'aperçu', errorMessage);
      setError(errorMessage);
    } finally {
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