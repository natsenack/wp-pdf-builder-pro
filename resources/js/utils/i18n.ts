// Fonctions d'internationalisation pour PDF Builder Pro
export function __ (text: string, domain: string = 'pdf-builder-pro'): string {
  // Dans un vrai plugin WordPress, ceci utiliserait wp.i18n
  // Pour le développement, on retourne simplement le texte
  return text;
}

export function _n (single: string, plural: string, number: number, domain: string = 'pdf-builder-pro'): string {
  // Version simple pour le développement
  return number === 1 ? single : plural;
}

export function sprintf (template: string, ...args: any[]): string {
  return template.replace(/%([sd])/g, (match, type) => {
    const arg = args.shift();
    return type === 's' ? String(arg) : String(Number(arg));
  });
}
