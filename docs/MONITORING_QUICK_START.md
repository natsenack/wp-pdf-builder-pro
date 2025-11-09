# 🎯 Real-Time Element & Property Monitoring System

## Quick Start

### 1. Open Browser Console
Press `F12` or `Ctrl+Shift+I` while using the PDF Builder

### 2. View Dashboard
```javascript
CanvasMonitoringDashboard.showDashboard()
```

### 3. Track Changes
```javascript
// Get all changes
CanvasMonitoringDashboard.getHistory()

// Track specific element
CanvasMonitoringDashboard.getElementHistory("element-id")

// Track specific property
CanvasMonitoringDashboard.getPropertyHistory("fillColor")

// Generate report
CanvasMonitoringDashboard.generateReport()

// Export to JSON
CanvasMonitoringDashboard.exportHistory()
```

## 🔍 What Gets Tracked

### Element Lifecycle
- ✨ **Created** - Element added to canvas
- 🗑️ **Deleted** - Element removed from canvas
- 🔄 **Updated** - Element properties modified

### Property Changes (ALL properties)
- **Position**: `x`, `y`
- **Size**: `width`, `height`
- **Appearance**: `color`, `fillColor`, `strokeColor`, `opacity`
- **Text**: `text`, `fontSize`, `fontFamily`
- **State**: `visible`, `locked`
- **Everything else**: Custom properties per element type

## 📊 Console Logging

When you modify elements, real-time logs appear:

```
📊 [REAL-TIME] 3 changement(s) détecté(s):
  ✨ Créé: text-element-1
  📍 [PROPERTY] text-element-1 → x: 100 → 105
  🎨 [PROPERTY] text-element-1 → fillColor: "#000000" → "#FF6B6B"
```

## 📈 Dashboard Output

```
🎯 CANVAS MONITORING DASHBOARD
═══════════════════════════════════════
📊 SESSION STATISTICS
  Uptime: 45.23s
  Total Renders: 125
  Total Changes: 342
  Elements Tracked: 8

🔄 CHANGE BREAKDOWN
  ✨ created: 8
  🗑️ deleted: 1
  🔧 property_changed: 333

🔧 TOP CHANGED PROPERTIES
  📍 x: 85 changes
  📍 y: 82 changes
  🎨 fillColor: 28 changes

🎨 MOST CHANGED ELEMENTS
  📦 text-element-1: 187 changes
  📦 shape-element-2: 92 changes
  📦 image-element-3: 37 changes
```

## 💡 Use Cases

### Debug Element Sync
```javascript
// Check if position change propagates correctly
const history = CanvasMonitoringDashboard.getElementHistory("elem-1");
console.log("Element changes:", history.length);
```

### Find Performance Issues
```javascript
CanvasMonitoringDashboard.showDashboard();
// Check "TOP CHANGED PROPERTIES" section
// Identify which properties change most frequently
```

### Trace Property History
```javascript
// See all fillColor changes
const colorChanges = CanvasMonitoringDashboard.getPropertyHistory("fillColor");
colorChanges.forEach(c => {
  console.log(`${c.elementId}: ${c.oldValue} → ${c.newValue}`);
});
```

### Monitor Real-Time Changes
```javascript
// Listen to all changes
elementChangeTracker.onChange(change => {
  console.log(`Element ${change.elementId} changed:`, change);
});
```

## 🎨 Emoji Legend

| Emoji | Meaning |
|-------|---------|
| 📍 | Position (x, y) |
| 📏 | Size (width, height) |
| 🔄 | Rotation |
| 👁️ | Visibility/opacity |
| 🔒 | Lock state |
| 🎨 | Colors |
| 📝 | Text/font |
| 🖼️ | Images/media |
| 📊 | Table properties |
| ✨ | Element created |
| 🗑️ | Element deleted |
| 🔧 | Other properties |

## 🚀 Advanced Features

### Export Data for Analysis
```javascript
const data = CanvasMonitoringDashboard.exportHistory();
// data contains: timestamp, history[], snapshots[]
console.log(JSON.stringify(data, null, 2));
```

### Clear History
```javascript
CanvasMonitoringDashboard.clearHistory();
// Useful for testing specific scenarios
```

### Get Current Snapshots
```javascript
const snapshots = elementChangeTracker.getSnapshots();
// Returns Map<elementId, ElementSnapshot>
```

### Filter Changes by Time
```javascript
const recentChanges = elementChangeTracker.getChangesBetween(
  Date.now() - 5000,  // Last 5 seconds
  Date.now()
);
```

## ⚙️ Technical Details

### How It Works
1. **Change Detection**: Efficient hash-based comparison
2. **Tracking**: All property changes logged with timestamps
3. **History**: Last 500 changes stored in memory
4. **Performance**: Minimal overhead, no impact on render performance

### Implementation
- `ElementChangeTracker.ts`: Core tracking logic
- `CanvasMonitoringDashboard.ts`: Console dashboard
- `Canvas.tsx`: Integration point

### Files Modified
- `assets/js/src/pdf-builder-react/components/canvas/Canvas.tsx`
- `assets/js/src/pdf-builder-react/utils/ElementChangeTracker.ts` (new)
- `assets/js/src/pdf-builder-react/utils/CanvasMonitoringDashboard.ts` (new)

## 📚 See Also

- [Detailed Monitoring Guide](./REAL_TIME_MONITORING_GUIDE.md)
- [Architecture Documentation](./ARCHITECTURE_MODULAIRE_DETAILLEE.md)
- [Debug Instructions](./developer/)

## 🎯 Next Steps

1. **Try it**: Open the browser console and run `CanvasMonitoringDashboard.showDashboard()`
2. **Monitor**: Edit elements and watch real-time logs
3. **Analyze**: Use `getHistory()` to debug state synchronization
4. **Report**: Export data for bug reports or performance analysis

---

**Status**: Production Ready ✅  
**Monitoring**: Real-Time ⚡  
**Performance Impact**: Minimal 📊
