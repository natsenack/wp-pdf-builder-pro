import { useEffect, useRef, useCallback } from 'react';

/**
 * Hook personnalisé pour monitorer les performances du système d'aperçu
 * Mesure les temps de chargement, mémoire, et autres métriques
 */
export function usePerformanceMonitor(componentName = 'Unknown') {
  const startTimeRef = useRef(null);
  const renderCountRef = useRef(0);

  // Démarrer le monitoring
  useEffect(() => {
    startTimeRef.current = performance.now();
    renderCountRef.current = 0;

    return () => {
      const duration = performance.now() - startTimeRef.current;
    };
  }, [componentName]);

  // Tracker les renders
  useEffect(() => {
    renderCountRef.current += 1;
  });

  // Mesurer une opération spécifique
  const measureOperation = useCallback((operationName, operation) => {
    const start = performance.now();

    try {
      const result = operation();
      const duration = performance.now() - start;

      // Mesurer la mémoire si disponible
      if (performance.memory) {
      }

      return result;
    } catch (error) {
      const duration = performance.now() - start;
      throw error;
    }
  }, [componentName]);

  // Mesurer le temps de chargement
  const measureLoadTime = useCallback((resourceName) => {
    const start = performance.now();

    return {
      end: () => {
        const duration = performance.now() - start;
        return duration;
      }
    };
  }, [componentName]);

  return {
    measureOperation,
    measureLoadTime
  };
}

export default usePerformanceMonitor;