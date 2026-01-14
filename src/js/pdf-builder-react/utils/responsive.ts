/**
 * Utilitaires CSS pour le responsive design
 * Classes utilitaires pour une gestion simplifiée du responsive
 */

import { BREAKPOINTS } from '../constants/responsive';

/**
 * Génère des classes CSS responsive
 */
export const responsiveUtils = `
/* Utilitaires responsive automatiques */

/* Visibilité */
.hidden-xs { display: none !important; }
.hidden-sm { display: none !important; }
.hidden-md { display: none !important; }
.hidden-lg { display: none !important; }
.hidden-xl { display: none !important; }

.visible-xs { display: block !important; }
.visible-sm { display: block !important; }
.visible-md { display: block !important; }
.visible-lg { display: block !important; }
.visible-xl { display: block !important; }

/* Media queries pour la visibilité */
@media (min-width: ${BREAKPOINTS.xs}px) {
  .hidden-xs { display: inherit !important; }
  .visible-xs { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.sm}px) {
  .hidden-sm { display: inherit !important; }
  .visible-sm { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.md}px) {
  .hidden-md { display: inherit !important; }
  .visible-md { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.lg}px) {
  .hidden-lg { display: inherit !important; }
  .visible-lg { display: none !important; }
}

@media (min-width: ${BREAKPOINTS.xl}px) {
  .hidden-xl { display: inherit !important; }
  .visible-xl { display: none !important; }
}

/* Flex utilities responsive */
.flex-column-xs { flex-direction: column !important; }
.flex-column-sm { flex-direction: column !important; }
.flex-column-md { flex-direction: column !important; }
.flex-row-xs { flex-direction: row !important; }
.flex-row-sm { flex-direction: row !important; }
.flex-row-md { flex-direction: row !important; }

/* Text alignment responsive */
.text-center-xs { text-align: center !important; }
.text-center-sm { text-align: center !important; }
.text-center-md { text-align: center !important; }
.text-left-xs { text-align: left !important; }
.text-left-sm { text-align: left !important; }
.text-left-md { text-align: left !important; }
.text-right-xs { text-align: right !important; }
.text-right-sm { text-align: right !important; }
.text-right-md { text-align: right !important; }

/* Spacing responsive */
.m-0-xs { margin: 0 !important; }
.m-0-sm { margin: 0 !important; }
.m-0-md { margin: 0 !important; }
.p-0-xs { padding: 0 !important; }
.p-0-sm { padding: 0 !important; }
.p-0-md { padding: 0 !important; }

/* Width responsive */
.w-100-xs { width: 100% !important; }
.w-100-sm { width: 100% !important; }
.w-100-md { width: 100% !important; }
.w-auto-xs { width: auto !important; }
.w-auto-sm { width: auto !important; }
.w-auto-md { width: auto !important; }

/* Container responsive */
.container-fluid-xs { width: 100% !important; padding-left: 15px !important; padding-right: 15px !important; }
.container-fluid-sm { width: 100% !important; padding-left: 15px !important; padding-right: 15px !important; }
.container-xs { max-width: 100% !important; margin: 0 auto !important; }
.container-sm { max-width: 100% !important; margin: 0 auto !important; }

/* Media queries pour les utilitaires */
@media (min-width: ${BREAKPOINTS.xs}px) {
  .flex-column-xs { flex-direction: row !important; }
  .flex-row-xs { flex-direction: column !important; }
  .text-center-xs { text-align: inherit !important; }
  .text-left-xs { text-align: inherit !important; }
  .text-right-xs { text-align: inherit !important; }
  .m-0-xs { margin: inherit !important; }
  .p-0-xs { padding: inherit !important; }
  .w-100-xs { width: inherit !important; }
  .w-auto-xs { width: inherit !important; }
  .container-fluid-xs { width: inherit !important; padding-left: inherit !important; padding-right: inherit !important; }
  .container-xs { max-width: inherit !important; }
}

@media (min-width: ${BREAKPOINTS.sm}px) {
  .flex-column-sm { flex-direction: row !important; }
  .flex-row-sm { flex-direction: column !important; }
  .text-center-sm { text-align: inherit !important; }
  .text-left-sm { text-align: inherit !important; }
  .text-right-sm { text-align: inherit !important; }
  .m-0-sm { margin: inherit !important; }
  .p-0-sm { padding: inherit !important; }
  .w-100-sm { width: inherit !important; }
  .w-auto-sm { width: inherit !important; }
  .container-fluid-sm { width: inherit !important; padding-left: inherit !important; padding-right: inherit !important; }
  .container-sm { max-width: inherit !important; }
}

@media (min-width: ${BREAKPOINTS.md}px) {
  .flex-column-md { flex-direction: row !important; }
  .flex-row-md { flex-direction: column !important; }
  .text-center-md { text-align: inherit !important; }
  .text-left-md { text-align: inherit !important; }
  .text-right-md { text-align: inherit !important; }
  .m-0-md { margin: inherit !important; }
  .p-0-md { padding: inherit !important; }
  .w-100-md { width: inherit !important; }
  .w-auto-md { width: inherit !important; }
}
`;

/**
 * Injecte les utilitaires CSS responsive dans le DOM
 */
export function injectResponsiveUtils(): void {
  if (typeof document === 'undefined') return;

  const existingStyle = document.getElementById('pdf-builder-responsive-utils');
  if (existingStyle) return;

  const style = document.createElement('style');
  style.id = 'pdf-builder-responsive-utils';
  style.textContent = responsiveUtils;
  document.head.appendChild(style);
}

