import React, { useState, useEffect, useRef } from 'react';

// Hook pour détecter l'espace disponible et adapter le layout
const useAdaptiveLayout = (minWidth = 350) => {
  const [isVertical, setIsVertical] = useState(false);
  const containerRef = useRef(null);

  useEffect(() => {
    const checkLayout = () => {
      if (containerRef.current) {
        const rect = containerRef.current.getBoundingClientRect();
        setIsVertical(rect.width < minWidth);
      }
    };

    // Vérification initiale
    checkLayout();

    // Écouteur de redimensionnement
    window.addEventListener('resize', checkLayout);

    // Observer pour les changements de DOM
    const observer = new ResizeObserver(checkLayout);
    if (containerRef.current) {
      observer.observe(containerRef.current);
    }

    return () => {
      window.removeEventListener('resize', checkLayout);
      observer.disconnect();
    };
  }, [minWidth]);

  return { isVertical, containerRef };
};

// Composant adaptatif pour les contrôles avec titre
const AdaptiveControl = ({
  label,
  children,
  minWidth = 350,
  className = '',
  forceVertical = false
}) => {
  const { isVertical, containerRef } = useAdaptiveLayout(minWidth);
  const shouldBeVertical = forceVertical || isVertical;

  return (
    <div
      ref={containerRef}
      className={`adaptive-control ${shouldBeVertical ? 'adaptive-vertical' : 'adaptive-horizontal'} ${className}`}
    >
      <label className="adaptive-label">{label}</label>
      <div className="adaptive-content">
        {children}
      </div>
    </div>
  );
};

export { useAdaptiveLayout, AdaptiveControl };