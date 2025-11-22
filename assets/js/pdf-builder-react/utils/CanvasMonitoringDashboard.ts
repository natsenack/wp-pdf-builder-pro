/**
 * 🎯 CANVAS MONITORING DASHBOARD
 * 
 * Provides real-time monitoring, visualization, and debugging tools
 * for element changes, renders, and performance metrics.
 */

import { elementChangeTracker } from './ElementChangeTracker';

export class CanvasMonitoringDashboard {
  private static monitoringEnabled = false;
  private static startTime: number = 0;
  private static renderCount: number = 0;
  private static elementChanges: number = 0;

  /**
   * Initialize and start monitoring
   */
  static initialize(): void {
    this.monitoringEnabled = true;
    this.startTime = Date.now();
    // Silent initialization - dashboard is available via showDashboard()
  }

  /**
   * Increment render counter
   */
  static recordRender(): void {
    if (this.monitoringEnabled) {
      this.renderCount++;
    }
  }

  /**
   * Record element changes
   */
  static recordChanges(count: number): void {
    if (this.monitoringEnabled) {
      this.elementChanges += count;
    }
  }

  /**
   * Get formatted history
   */
  static getHistory() {
    return elementChangeTracker.getHistory();
  }

  /**
   * Get history for specific element
   */
  static getElementHistory(elementId: string) {
    return elementChangeTracker.getElementHistory(elementId);
  }

  /**
   * Get history for specific property
   */
  static getPropertyHistory(property: string) {
    return elementChangeTracker.getPropertyHistory(property);
  }

  /**
   * Generate detailed report
   */
  static generateReport(): string {
    const history = elementChangeTracker.getHistory();
    const snapshots = elementChangeTracker.getSnapshots();

    let report = '\n📋 DETAILED MONITORING REPORT\n';
    report += '═══════════════════════════════════════\n';
    report += `Total Events: ${history.length}\n`;
    report += `Elements: ${snapshots.size}\n\n`;

    // Recent changes
    report += 'Recent Changes (last 10):\n';
    history.slice(-10).forEach(change => {
      const time = new Date(change.timestamp).toLocaleTimeString();
      if (change.changeType === 'created') {
        report += `  [${time}] ✨ Created: ${change.elementId}\n`;
      } else if (change.changeType === 'deleted') {
        report += `  [${time}] 🗑️ Deleted: ${change.elementId}\n`;
      } else {
        report += `  [${time}] 🔧 ${change.elementId}.${change.property}: ${change.oldValue} → ${change.newValue}\n`;
      }
    });

    return report;
  }

  /**
   * Get emoji for change type
   */
  private static getChangeTypeEmoji(type: string): string {
    const emojiMap: Record<string, string> = {
      created: '✨',
      deleted: '🗑️',
      updated: '🔄',
      property_changed: '🔧'
    };
    return emojiMap[type] || '❓';
  }

  /**
   * Get emoji for property
   */
  private static getPropertyEmoji(prop: string): string {
    const emojiMap: Record<string, string> = {
      x: '📍',
      y: '📍',
      width: '📏',
      height: '📏',
      rotation: '🔄',
      opacity: '👁️',
      visible: '👁️',
      locked: '🔒',
      color: '🎨',
      fillColor: '🎨',
      strokeColor: '🖌️',
      fontSize: '📝',
      fontFamily: '📝',
      text: '📄'
    };
    return emojiMap[prop] || '🔧';
  }

  /**
   * Export history as JSON
   */
  static exportHistory(): string {
    return JSON.stringify(
      {
        timestamp: new Date().toISOString(),
        history: elementChangeTracker.getHistory(),
        snapshots: Array.from(elementChangeTracker.getSnapshots().values())
      },
      null,
      2
    );
  }

  /**
   * Clear all history
   */
  static clearHistory(): void {
    elementChangeTracker.clearHistory();
    this.renderCount = 0;
    this.elementChanges = 0;
  }
}

// Make available globally for console debugging
declare global {
  interface Window {
    CanvasMonitoringDashboard: typeof CanvasMonitoringDashboard;
  }
}

if (typeof window !== 'undefined') {
  (window as unknown as Record<string, unknown>).CanvasMonitoringDashboard = CanvasMonitoringDashboard;
}
