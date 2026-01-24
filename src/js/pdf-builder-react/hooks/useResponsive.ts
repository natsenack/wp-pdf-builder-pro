import { useState, useEffect } from 'react';
import { MEDIA_QUERIES, MediaQuery } from '../constants/responsive';

/**
 * Hook personnalisé pour détecter les media queries
 * @param query - La media query à vérifier
 * @returns boolean - True si la media query correspond
 */
export function useMediaQuery(query: MediaQuery): boolean {
  const [matches, setMatches] = useState<boolean>(false);

  useEffect(() => {
    const mediaQuery = window.matchMedia(MEDIA_QUERIES[query]);

    // Fonction de callback pour les changements
    const handleChange = (event: MediaQueryListEvent) => {
      setMatches(event.matches);
    };

    // Vérifier initialement
    setMatches(mediaQuery.matches);

    // Écouter les changements
    mediaQuery.addEventListener('change', handleChange);

    // Cleanup
    return () => {
      mediaQuery.removeEventListener('change', handleChange);
    };
  }, [query]);

  return matches;
}

/**
 * Hook pour obtenir le breakpoint actuel
 * @returns Le breakpoint actuel ('xs', 'sm', 'md', 'lg', 'xl')
 */
export function useBreakpoint(): 'xs' | 'sm' | 'md' | 'lg' | 'xl' {
  const isXs = useMediaQuery('xsOnly');
  const isSm = useMediaQuery('smOnly');
  const isMd = useMediaQuery('mdOnly');
  const isLg = useMediaQuery('lgOnly');
  const isXl = useMediaQuery('xlOnly');

  if (isXs) return 'xs';
  if (isSm) return 'sm';
  if (isMd) return 'md';
  if (isLg) return 'lg';
  return 'xl'; // Default to xl for very large screens
}

/**
 * Hook pour vérifier si l'écran est mobile
 * @returns boolean - True si mobile (xs ou sm)
 */
export function useIsMobile(): boolean {
  const breakpoint = useBreakpoint();
  return breakpoint === 'xs' || breakpoint === 'sm';
}

/**
 * Hook pour vérifier si l'écran est desktop
 * @returns boolean - True si desktop (lg ou xl)
 */
export function useIsDesktop(): boolean {
  const breakpoint = useBreakpoint();
  return breakpoint === 'lg' || breakpoint === 'xl';
}

/**
 * Hook pour vérifier si l'écran est tablette
 * @returns boolean - True si tablette (md)
 */
export function useIsTablet(): boolean {
  const breakpoint = useBreakpoint();
  return breakpoint === 'md';
}

