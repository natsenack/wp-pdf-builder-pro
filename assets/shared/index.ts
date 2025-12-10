// Export centralisé des types et interfaces partagés
// Utilisez ces exports pour importer facilement les types dans vos fichiers TS/TSX

// Types WordPress
export * from './types/wordpress';

// Types PDF Builder
export * from './types/pdf-builder';

// Interfaces de composants
export * from './interfaces/components';

// Types utilitaires communs
export type Nullable<T> = T | null;
export type Optional<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;
export type ValueOf<T> = T[keyof T];

// Types pour les promesses
export type PromiseResult<T> = T extends PromiseLike<infer U> ? U : T;

// Types pour les fonctions
export type AnyFunction = (...args: any[]) => any;
export type Predicate<T> = (value: T) => boolean;

// Types pour les objets
export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export type DeepReadonly<T> = {
  readonly [P in keyof T]: T[P] extends object ? DeepReadonly<T[P]> : T[P];
};

// Types pour les tableaux
export type ArrayElement<T extends readonly unknown[]> = T extends readonly (infer U)[] ? U : never;

// Types pour les chaînes
export type Capitalize<S extends string> = S extends `${infer F}${infer R}` ? `${Uppercase<F>}${R}` : S;
export type Uncapitalize<S extends string> = S extends `${infer F}${infer R}` ? `${Lowercase<F>}${R}` : S;