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

    console.log(`[Performance] ${componentName} - Mount started`);

    return () => {
      const duration = performance.now() - startTimeRef.current;
      console.log(`[Performance] ${componentName} - Unmount after ${duration.toFixed(2)}ms, ${renderCountRef.current} renders`);
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

      console.log(`[Performance] ${componentName} - ${operationName}: ${duration.toFixed(2)}ms`);

      // Mesurer la mémoire si disponible
      if (performance.memory) {
        console.log(`[Performance] ${componentName} - Memory: ${(performance.memory.usedJSHeapSize / 1024 / 1024).toFixed(2)}MB used`);
      }

      return result;
    } catch (error) {
      const duration = performance.now() - start;
      console.error(`[Performance] ${componentName} - ${operationName} failed after ${duration.toFixed(2)}ms:`, error);
      throw error;
    }
  }, [componentName]);

  // Mesurer le temps de chargement
  const measureLoadTime = useCallback((resourceName) => {
    const start = performance.now();

    return {
      end: () => {
        const duration = performance.now() - start;
        console.log(`[Performance] ${componentName} - ${resourceName} loaded in ${duration.toFixed(2)}ms`);
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