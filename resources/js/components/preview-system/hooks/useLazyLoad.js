import { useState, useEffect, useRef, useCallback } from 'react';

/**
 * Hook personnalisé pour gérer le lazy loading avec Intersection Observer
 * Optimise le chargement des éléments d'aperçu
 */
export function useLazyLoad(options = {}) {
  const {
    threshold = 0.1,
    rootMargin = '50px',
    triggerOnce = true
  } = options;

  const [isIntersecting, setIsIntersecting] = useState(false);
  const [hasTriggered, setHasTriggered] = useState(false);
  const elementRef = useRef(null);

  useEffect(() => {
    const element = elementRef.current;
    if (!element) return;

    // Si déjà déclenché et triggerOnce activé, ne rien faire
    if (hasTriggered && triggerOnce) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        const isElementIntersecting = entry.isIntersecting;

        setIsIntersecting(isElementIntersecting);

        if (isElementIntersecting && !hasTriggered) {
          setHasTriggered(true);
        }
      },
      {
        threshold,
        rootMargin
      }
    );

    observer.observe(element);

    return () => {
      observer.unobserve(element);
    };
  }, [threshold, rootMargin, hasTriggered, triggerOnce]);

  // Forcer le déclenchement manuellement
  const trigger = useCallback(() => {
    setHasTriggered(true);
    setIsIntersecting(true);
  }, []);

  // Reset pour permettre un nouveau déclenchement
  const reset = useCallback(() => {
    setHasTriggered(false);
    setIsIntersecting(false);
  }, []);

  return {
    elementRef,
    isIntersecting,
    hasTriggered,
    trigger,
    reset
  };
}

/**
 * Hook spécialisé pour le lazy loading des pages PDF
 */
export function useLazyPageLoad(pageNumber, options = {}) {
  const lazyLoad = useLazyLoad({
    threshold: 0.2,
    rootMargin: '100px',
    ...options
  });

  const [pageData, setPageData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (lazyLoad.hasTriggered && !pageData && !isLoading) {
      setIsLoading(true);

      // Simulation du chargement de la page
      // Remplacer par vraie logique de chargement
      setTimeout(() => {
        setPageData({
          number: pageNumber,
          content: `Contenu de la page ${pageNumber}`,
          loaded: true
        });
        setIsLoading(false);
      }, 300);
    }
  }, [lazyLoad.hasTriggered, pageData, isLoading, pageNumber]);

  return {
    ...lazyLoad,
    pageData,
    isLoading
  };
}

export default useLazyLoad;