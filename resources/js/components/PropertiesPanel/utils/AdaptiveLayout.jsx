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
  forceVertical = false,
  demoMode = false // Mode démo pour tester les layouts
}) => {
  const { isVertical, containerRef } = useAdaptiveLayout(minWidth);
  const shouldBeVertical = forceVertical || isVertical;

  return (
    <div
      ref={containerRef}
      className={`adaptive-control ${shouldBeVertical ? 'adaptive-vertical' : 'adaptive-horizontal'} ${className}`}
      style={demoMode ? {
        border: '2px dashed #3b82f6',
        padding: '8px',
        margin: '4px 0',
        borderRadius: '6px',
        backgroundColor: shouldBeVertical ? '#eff6ff' : '#f0fdf4'
      } : {}}
    >
      <label className="adaptive-label" style={demoMode ? {
        color: shouldBeVertical ? '#1d4ed8' : '#166534',
        fontWeight: 'bold'
      } : {}}>
        {label}
        {demoMode && (
          <span style={{
            fontSize: '10px',
            marginLeft: '8px',
            padding: '2px 6px',
            borderRadius: '10px',
            backgroundColor: shouldBeVertical ? '#dbeafe' : '#dcfce7',
            color: shouldBeVertical ? '#1e40af' : '#166534'
          }}>
            {shouldBeVertical ? 'VERTICAL' : 'HORIZONTAL'}
          </span>
        )}
      </label>
      <div className="adaptive-content">
        {children}
      </div>
    </div>
  );
};

export { useAdaptiveLayout, AdaptiveControl };