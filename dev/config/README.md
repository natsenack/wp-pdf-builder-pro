# 🔧 Dev Configuration

Configuration de développement et build pour PDF Builder Pro.

## 📁 Structure

```
dev/
└── config/
    └── build/
        └── webpack.config.cjs    ← Configuration Webpack principal
```

## 📦 Webpack Configuration

**File** : `build/webpack.config.cjs`

### Entry Points
```
assets/js/
├── pdf-canvas-vanilla.js           ← Canvas editor
├── pdf-preview-api-client.js       ← API client
├── pdf-preview-integration.js      ← Preview integration
├── settings-global-save.js         ← Settings save
├── settings-tabs-improved.js       ← Settings tabs
├── ajax-throttle.js                ← AJAX throttling
├── tabs-force.js                   ← Tab force handler
└── tabs-root-monitor.js            ← Tab root monitor
```

### Output
```
plugin/assets/
├── js/                             ← Compiled JavaScript bundles
├── css/                            ← Extracted CSS
├── images/                         ← Images
├── fonts/                          ← Fonts
└── shared/                         ← Shared assets (TypeScript, etc.)
```

### Features
- ✅ **Babel** : ES6+ → ES5 compatibility
- ✅ **TypeScript** : ts-loader support
- ✅ **CSS Extraction** : MiniCssExtractPlugin
- ✅ **Asset Optimization** : TerserPlugin for JS, Gzip compression
- ✅ **Code Splitting** : Vendor/Common chunks
- ✅ **Source Maps** : Development + Production
- ✅ **Asset Copying** : Static files to output

### Build Modes

```bash
# Development (watch mode)
npm run dev          # Single build
npm run watch        # Watch mode

# Production (optimized)
npm run build        # Production build
npm run build-prod   # Alias for build
```

### Configuration Details

#### Loaders
| Loader | Type | Options |
|--------|------|---------|
| babel-loader | .js/.jsx | ES6+ transform, @babel/plugin-transform-runtime |
| ts-loader | .ts/.tsx | TypeScript transpile |
| css-loader | .css | CSS processing |
| style-loader | .css | CSS injection (dev) |
| asset | images | Inline small images |
| asset/resource | fonts | Font files |

#### Plugins
| Plugin | Purpose |
|--------|---------|
| MiniCssExtractPlugin | Extract CSS to separate files |
| webpack.ProvidePlugin | Global Buffer/process polyfills |
| webpack.DefinePlugin | Environment variables |
| CopyPlugin | Copy static assets |
| CompressionPlugin | Gzip compression (production) |
| TerserPlugin | JS minification |

#### Optimization
- **Code Splitting** : Vendor + Common chunks
- **Runtime Chunk** : Separate runtime (caching)
- **Minification** : Drop console/debugger in production
- **Source Maps** : Full in production, cheap in development

### Aliases
```javascript
'@'        → assets/
'@shared'  → assets/shared/
'@ts'      → assets/ts/
'@js'      → assets/js/
```

### Performance Hints
- Max entrypoint size: 512 KB
- Max asset size: 512 KB
- Warnings enabled in production

---

## 🚀 Usage

```bash
# Install dependencies
npm install

# Development build (single)
npm run dev

# Development build (watch)
npm run watch

# Production build
npm run build

# Analyze bundle size
webpack-bundle-analyzer dist/stats.json
```

---

## 📊 Output Structure

After build, check:
```
plugin/assets/
├── js/
│   ├── pdf-canvas-vanilla.bundle.js
│   ├── pdf-preview-api-client.bundle.js
│   ├── ... (other bundles)
│   ├── vendors.js                          ← Vendor code
│   ├── runtime.js                          ← Webpack runtime
│   └── *.js.map                            ← Source maps
├── css/
│   └── *.bundle.css                        ← Extracted CSS
├── images/
│   └── [name].[hash].ext                   ← Images
├── fonts/
│   └── [name].[hash].ext                   ← Font files
└── shared/                                 ← Copied shared assets
```

---

## 🔍 Troubleshooting

### Build fails with "MODULE_NOT_FOUND"
- Clear node_modules: `rm -r node_modules && npm install`
- Check webpack config path in package.json scripts

### Source maps not working
- Dev: Check devtool = 'cheap-module-source-map'
- Prod: Check devtool = 'source-map'

### Bundle too large
- Run: `webpack-bundle-analyzer`
- Check code splitting configuration
- Review vendor chunk size

### TypeScript errors
- Run: `npx tsc --noEmit`
- Check tsconfig.json

---

## 📚 References

- [Webpack Documentation](https://webpack.js.org/)
- [Babel Documentation](https://babeljs.io/)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [CSS-Loader Documentation](https://webpack.js.org/loaders/css-loader/)

---

**Created** : 30 décembre 2025  
**Version** : 1.0
