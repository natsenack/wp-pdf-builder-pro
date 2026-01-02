import React from 'react';

/**
 * ModalSkeleton - Composant de chargement pour le système d'aperçu
 * Affiche un skeleton loader pendant le lazy loading des composants
 */
function ModalSkeleton() {
  return (
    <div className="pdf-preview-skeleton">
      {/* Header skeleton */}
      <div className="pdf-preview-skeleton-header">
        <div className="pdf-preview-skeleton-title"></div>
        <div className="pdf-preview-skeleton-close"></div>
      </div>

      {/* Toolbar skeleton */}
      <div className="pdf-preview-skeleton-toolbar">
        <div className="pdf-preview-skeleton-button"></div>
        <div className="pdf-preview-skeleton-button"></div>
        <div className="pdf-preview-skeleton-button"></div>
        <div className="pdf-preview-skeleton-spacer"></div>
        <div className="pdf-preview-skeleton-zoom"></div>
      </div>

      {/* Content skeleton */}
      <div className="pdf-preview-skeleton-content">
        <div className="pdf-preview-skeleton-page">
          {/* Simule le contenu d'une page PDF */}
          <div className="pdf-preview-skeleton-line"></div>
          <div className="pdf-preview-skeleton-line short"></div>
          <div className="pdf-preview-skeleton-line"></div>
          <div className="pdf-preview-skeleton-line medium"></div>
          <div className="pdf-preview-skeleton-line"></div>
          <div className="pdf-preview-skeleton-line short"></div>
          <div className="pdf-preview-skeleton-line"></div>
          <div className="pdf-preview-skeleton-spacer"></div>
          <div className="pdf-preview-skeleton-line"></div>
          <div className="pdf-preview-skeleton-line medium"></div>
          <div className="pdf-preview-skeleton-line"></div>
        </div>
      </div>

      {/* Footer skeleton */}
      <div className="pdf-preview-skeleton-footer">
        <div className="pdf-preview-skeleton-nav"></div>
        <div className="pdf-preview-skeleton-page-info"></div>
        <div className="pdf-preview-skeleton-export"></div>
      </div>
    </div>
  );
}

export default ModalSkeleton;