import { ReactNode } from 'react';
import { useBreakpoint, useIsMobile, useIsTablet, useIsDesktop } from '../../hooks/useResponsive';

interface ResponsiveProps {
  children: ReactNode;
  breakpoint?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  mobile?: ReactNode;
  tablet?: ReactNode;
  desktop?: ReactNode;
  className?: string;
}

/**
 * Composant Responsive - Affiche du contenu différent selon le breakpoint
 */
export function Responsive({
  children,
  breakpoint,
  mobile,
  tablet,
  desktop,
  className = ''
}: ResponsiveProps) {
  const currentBreakpoint = useBreakpoint();
  const isMobile = useIsMobile();
  const isTablet = useIsTablet();
  const isDesktop = useIsDesktop();

  // Si un breakpoint spécifique est demandé
  if (breakpoint && currentBreakpoint === breakpoint) {
    return <div className={className}>{children}</div>;
  }

  // Contenu spécifique selon le type d'appareil
  if (isMobile && mobile) {
    return <div className={className}>{mobile}</div>;
  }

  if (isTablet && tablet) {
    return <div className={className}>{tablet}</div>;
  }

  if (isDesktop && desktop) {
    return <div className={className}>{desktop}</div>;
  }

  // Contenu par défaut
  return <div className={className}>{children}</div>;
}

interface ResponsiveContainerProps {
  children: ReactNode;
  className?: string;
  mobileClass?: string;
  tabletClass?: string;
  desktopClass?: string;
}

/**
 * Conteneur responsive qui applique des classes CSS différentes selon l'appareil
 */
export function ResponsiveContainer({
  children,
  className = '',
  mobileClass = '',
  tabletClass = '',
  desktopClass = ''
}: ResponsiveContainerProps) {
  const isMobile = useIsMobile();
  const isTablet = useIsTablet();
  const isDesktop = useIsDesktop();

  let responsiveClass = className;

  if (isMobile && mobileClass) {
    responsiveClass += ` ${mobileClass}`;
  } else if (isTablet && tabletClass) {
    responsiveClass += ` ${tabletClass}`;
  } else if (isDesktop && desktopClass) {
    responsiveClass += ` ${desktopClass}`;
  }

  return (
    <div className={responsiveClass.trim()}>
      {children}
    </div>
  );
}

interface HiddenProps {
  children: ReactNode;
  on?: ('xs' | 'sm' | 'md' | 'lg' | 'xl')[];
  className?: string;
}

/**
 * Composant Hidden - Cache le contenu sur certains breakpoints
 */
export function Hidden({ children, on = [], className = '' }: HiddenProps) {
  const currentBreakpoint = useBreakpoint();

  if (on.includes(currentBreakpoint)) {
    return null;
  }

  return <div className={className}>{children}</div>;
}

interface VisibleProps {
  children: ReactNode;
  on?: ('xs' | 'sm' | 'md' | 'lg' | 'xl')[];
  className?: string;
}

/**
 * Composant Visible - Affiche le contenu seulement sur certains breakpoints
 */
export function Visible({ children, on = [], className = '' }: VisibleProps) {
  const currentBreakpoint = useBreakpoint();

  if (!on.includes(currentBreakpoint)) {
    return null;
  }

  return <div className={className}>{children}</div>;
}



