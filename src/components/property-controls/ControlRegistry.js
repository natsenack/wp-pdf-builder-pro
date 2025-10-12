// Registre des contrôles de propriétés pour les éléments du canvas
// Permet d'enregistrer dynamiquement des contrôles et de les récupérer par nom

const CONTROL_REGISTRY = {};

// Fonction pour enregistrer un nouveau contrôle
export const registerControl = (name, component) => {
  CONTROL_REGISTRY[name] = component;
};

// Fonction pour récupérer un contrôle par son nom
export const getControlComponent = (name) => {
  return CONTROL_REGISTRY[name];
};

// Fonction pour vérifier si un contrôle existe
export const hasControl = (name) => {
  return !!CONTROL_REGISTRY[name];
};

// Fonction pour obtenir tous les contrôles enregistrés
export const getAllControls = () => {
  return { ...CONTROL_REGISTRY };
};

// Fonction pour supprimer un contrôle (utile pour les tests ou le nettoyage)
export const unregisterControl = (name) => {
  delete CONTROL_REGISTRY[name];
};

export default CONTROL_REGISTRY;