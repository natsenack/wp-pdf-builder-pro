# 🎯 REAL-TIME MONITORING - CHEAT SHEET

## TL;DR - One-Minute Setup

### Open Console & Run
```javascript
CanvasMonitoringDashboard.showDashboard()
```

That's it! You now see:
- Session stats
- Total changes
- Top changed properties
- Most changed elements

## Essential Commands

### View Dashboard
```javascript
CanvasMonitoringDashboard.showDashboard()
```

### Track One Element
```javascript
CanvasMonitoringDashboard.getElementHistory("id")
```

### Track One Property
```javascript
CanvasMonitoringDashboard.getPropertyHistory("fillColor")
```

### Get All Changes
```javascript
CanvasMonitoringDashboard.getHistory()
```

### Export to File
```javascript
copy(CanvasMonitoringDashboard.exportHistory())
// Then paste into file
```

### Clear History
```javascript
CanvasMonitoringDashboard.clearHistory()
```

### Generate Report
```javascript
console.log(CanvasMonitoringDashboard.generateReport())
```

## Emoji Reference

| Emoji | Means |
|-------|-------|
| 📍 | Position (x, y) |
| 📏 | Size (width, height) |
| 🔄 | Rotation |
| 👁️ | Visibility/opacity |
| 🔒 | Locked |
| 🎨 | Colors |
| 📝 | Text/font |
| 🖼️ | Images |
| 📊 | Tables |
| ✨ | Created |
| 🗑️ | Deleted |
| 🔧 | Other properties |

## Console Output Explained

```
📊 [REAL-TIME] 3 changement(s) détecté(s):
  ✨ Créé: text-element-1
  📍 [PROPERTY] text-element-1 → x: 100 → 105
  🎨 [PROPERTY] text-element-1 → fillColor: "#000000" → "#FF6B6B"
```

Means:
- Element created
- X position changed 100→105
- Fill color changed black→red

## Real-Time Listener

```javascript
elementChangeTracker.onChange(change => {
  console.log(`Changed: ${change.property}`);
});
```

## Quick Analysis

### What changed most?
```javascript
CanvasMonitoringDashboard.showDashboard()
// Check: "TOP CHANGED PROPERTIES"
```

### Which element changed most?
```javascript
CanvasMonitoringDashboard.showDashboard()
// Check: "MOST CHANGED ELEMENTS"
```

### All changes for element?
```javascript
CanvasMonitoringDashboard.getElementHistory("my-element")
console.table(result)
```

### All color changes?
```javascript
CanvasMonitoringDashboard.getPropertyHistory("fillColor")
console.table(result)
```

## Debug Workflow

1. **Open Console** (F12)
2. **Enable Monitoring** `CanvasMonitoringDashboard.showDashboard()`
3. **Make Changes** in the UI
4. **View Results** in console
5. **Export Data** `copy(CanvasMonitoringDashboard.exportHistory())`

## Common Tasks

### "Is my change syncing?"
```javascript
const before = CanvasMonitoringDashboard.getHistory().length;
// Make change
const after = CanvasMonitoringDashboard.getHistory().length;
console.log(after > before ? '✅ Synced!' : '❌ Not synced');
```

### "How many times did X change?"
```javascript
CanvasMonitoringDashboard.getPropertyHistory("x").length
```

### "What's the current state of element?"
```javascript
elementChangeTracker.getSnapshots().get("element-id")
```

### "Show me last 10 changes"
```javascript
console.table(
  CanvasMonitoringDashboard.getHistory().slice(-10)
)
```

### "Find changes in last minute"
```javascript
elementChangeTracker.getChangesBetween(
  Date.now() - 60000,
  Date.now()
)
```

## Files to Read

- **Quick Start**: [MONITORING_QUICK_START.md](./MONITORING_QUICK_START.md)
- **Full Guide**: [REAL_TIME_MONITORING_GUIDE.md](../REAL_TIME_MONITORING_GUIDE.md)
- **Examples**: [MONITORING_EXAMPLES.js](./MONITORING_EXAMPLES.js)
- **Architecture**: [MONITORING_SYSTEM_OVERVIEW.md](./MONITORING_SYSTEM_OVERVIEW.md)

## Troubleshooting

### "Not defined"?
- Refresh page
- Make sure PDF Builder loaded
- Check browser console

### "No changes"?
- Edit an element to trigger changes
- Try `CanvasMonitoringDashboard.clearHistory()`

### "Too much output"?
- Filter console by message type
- Run `CanvasMonitoringDashboard.clearHistory()`

### "Too slow"?
- Run `CanvasMonitoringDashboard.clearHistory()`
- Monitoring has minimal impact but history can grow

## Advanced

### Listen to changes
```javascript
elementChangeTracker.onChange(change => {
  if (change.property === "fillColor") {
    console.log("🎨 Color changed!");
  }
});
```

### Export for analysis
```javascript
const data = CanvasMonitoringDashboard.exportHistory();
// Contains: timestamp, history[], snapshots[]
```

### Custom filtering
```javascript
CanvasMonitoringDashboard.getHistory()
  .filter(c => c.changeType === 'property_changed')
  .filter(c => c.property === 'text')
```

---

**Keep this handy when debugging!** 📍

More help: Check `/docs` folder for detailed guides
