/**
 * Responsive Design Constants
 * Breakpoints et utilitaires pour le design responsive
 */

export const BREAKPOINTS = {
  // Mobile first approach
  xs: 480,   // Extra small devices (phones, < 480px)
  sm: 768,   // Small devices (tablets, < 768px)
  md: 992,   // Medium devices (small laptops, < 992px)
  lg: 1200,  // Large devices (desktops, < 1200px)
  xl: 1440,  // Extra large devices (large desktops, >= 1440px)
} as const;

export const MEDIA_QUERIES = {
  xs: `(max-width: ${BREAKPOINTS.xs - 1}px)`,
  sm: `(max-width: ${BREAKPOINTS.sm - 1}px)`,
  md: `(max-width: ${BREAKPOINTS.md - 1}px)`,
  lg: `(max-width: ${BREAKPOINTS.lg - 1}px)`,
  xl: `(min-width: ${BREAKPOINTS.xl}px)`,

  // Ranges
  xsOnly: `(max-width: ${BREAKPOINTS.xs - 1}px)`,
  smOnly: `(min-width: ${BREAKPOINTS.xs}px) and (max-width: ${BREAKPOINTS.sm - 1}px)`,
  mdOnly: `(min-width: ${BREAKPOINTS.sm}px) and (max-width: ${BREAKPOINTS.md - 1}px)`,
  lgOnly: `(min-width: ${BREAKPOINTS.md}px) and (max-width: ${BREAKPOINTS.lg - 1}px)`,
  xlOnly: `(min-width: ${BREAKPOINTS.lg}px)`,
} as const;

export type Breakpoint = keyof typeof BREAKPOINTS;
export type MediaQuery = keyof typeof MEDIA_QUERIES;

