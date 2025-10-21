/**
 * SYSTÈME D'APERÇU ULTRA-SIMPLE - VERSION 3.0
 * Export principal pour le nouveau système d'aperçu
 */

// Import du système ultra-simple
import { useSimplePreview } from './SimplePreviewSystem_v3';

// Ré-export du hook principal
export { useSimplePreview };

// Composant de test autonome
export function PreviewSystemTestV3() {
  return <div>Test du système d'aperçu v3</div>;
}

// Export par défaut
export default {
  useSimplePreview,
  PreviewSystemTestV3
};