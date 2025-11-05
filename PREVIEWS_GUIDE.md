# Guide des aperçus SVG

## Régénération simplifiée

Pour régénérer tous les aperçus SVG en une seule commande :

```bash
cd plugin/
php regenerate-all-previews.php
```

Ce script régénère automatiquement tous les templates :
- modern
- classic
- corporate
- minimal

## Déploiement

Après régénération, déployer avec :

```bash
.\build\deploy-simple.ps1 -Mode plugin
```

## Structure des aperçus

Chaque aperçu SVG contient :
- Une page blanche A4 proportionnelle (ratio 1:√2)
- L'aperçu du template centré sur la page
- Une ombre subtile pour l'effet 3D
- Marges appropriées autour de la page

## Templates disponibles

- `modern.json` → `modern-preview.svg`
- `classic.json` → `classic-preview.svg`
- `corporate.json` → `corporate-preview.svg`
- `minimal.json` → `minimal-preview.svg`