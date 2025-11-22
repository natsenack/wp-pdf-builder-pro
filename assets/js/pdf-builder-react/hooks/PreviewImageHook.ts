/**
 * PreviewImageHook.ts
 * 
 * Enregistre le hook AJAX côté frontend pour charger l'image PHP rendue
 */

export function registerPreviewImageHook() {
  // Enregistrer l'handler AJAX au chargement de la page
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePreviewImageAjax);
  } else {
    initializePreviewImageAjax();
  }
}

function initializePreviewImageAjax() {
  // Ajouter l'action AJAX WordPress si le PHP n'a pas déjà été chargé
  const nonce = document.querySelector('[data-pdf-builder-nonce]')?.getAttribute('data-pdf-builder-nonce');
  if (nonce) {

  }

  // Émettre un événement personnalisé pour signaler que le hook est prêt
  const event = new CustomEvent('pdf-builder-preview-ready', {
    detail: { hasNonce: !!nonce }
  });
  document.dispatchEvent(event);
}

export default registerPreviewImageHook;
