# 📊 Monitoring Production - Métriques et alertes

Ce guide couvre la mise en place complète du monitoring pour WP PDF Builder Pro en production, incluant métriques, alertes et tableaux de bord.

## 🎯 Vue d'ensemble monitoring

### Objectifs monitoring

#### Disponibilité
- **Uptime** : > 99.9% (8.76h downtime/mois max)
- **Temps de réponse** : < 500ms API, < 2s génération PDF
- **Taux d'erreur** : < 1% des requêtes

#### Performance
- **CPU** : < 70% utilisation moyenne
- **Mémoire** : < 80% utilisation
- **Disque** : > 20% espace libre
- **Base de données** : < 100 connexions actives

#### Fonctionnel
- **Génération PDF** : > 99.5% succès
- **API** : 100% disponibilité endpoints critiques
- **Intégrations** : Monitoring connecteurs externes

## 🏗️ Architecture monitoring

### Pile technologique

#### Infrastructure
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Application   │ -> │   Métriques      │ -> │   Alertes       │
│   (PHP/Laravel) │    │   (StatsD)       │    │   (PagerDuty)   │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│   Serveur Web   │ -> │   Logs           │ -> │   Dashboard     │
│   (Nginx)       │    │   (ELK Stack)    │    │   (Grafana)     │
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│   Base données  │ -> │   Traces         │ -> │   Rapports      │
│   (MySQL)       │    │   (Jaeger)       │    │   (Metabase)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Outils recommandés

#### Monitoring applicatif
- **New Relic** : APM complet
- **DataDog** : Observabilité unifiée
- **AppDynamics** : Monitoring performance

#### Infrastructure
- **Prometheus** : Collecte métriques
- **Grafana** : Visualisation
- **Zabbix** : Monitoring legacy

#### Logs
- **ELK Stack** : Elasticsearch, Logstash, Kibana
- **Graylog** : Centralisation logs
- **Splunk** : Enterprise logging

## 📈 Métriques essentielles

### Métriques application

#### Performance génération PDF
```php
<?php
// PdfMetrics.php

class PdfMetrics
{
    public function trackPdfGeneration($templateId, $startTime, $success, $fileSize = null)
    {
        $duration = microtime(true) - $startTime;

        $metrics = [
            'metric' => 'pdf.generation.duration',
            'value' => $duration,
            'tags' => [
                'template_id' => $templateId,
                'success' => $success ? 'true' : 'false',
                'size_kb' => $fileSize ? round($fileSize / 1024) : 0
            ]
        ];

        $this->sendMetric($metrics);

        // Métriques business
        if ($success) {
            $this->incrementCounter('pdf.generated.total');
            $this->histogram('pdf.size', $fileSize);
        } else {
            $this->incrementCounter('pdf.generation.errors');
        }
    }

    public function trackApiRequest($endpoint, $method, $responseTime, $statusCode)
    {
        $this->timing('api.response_time', $responseTime, [
            'endpoint' => $endpoint,
            'method' => $method,
            'status' => $statusCode
        ]);

        if ($statusCode >= 400) {
            $this->incrementCounter('api.errors', [
                'endpoint' => $endpoint,
                'status' => $statusCode
            ]);
        }
    }
}
```

#### Métriques base de données
```sql
-- Database metrics queries

-- Connexions actives
SELECT COUNT(*) as active_connections
FROM information_schema.processlist
WHERE command != 'Sleep';

-- Requêtes lentes
SELECT sql_text, exec_count, avg_timer_wait/1000000000 as avg_time_sec
FROM performance_schema.events_statements_summary_by_digest
WHERE avg_timer_wait > 1000000000  -- > 1 seconde
ORDER BY avg_timer_wait DESC
LIMIT 10;

-- Taille tables
SELECT
    table_name,
    ROUND(data_length/1024/1024, 2) as data_mb,
    ROUND(index_length/1024/1024, 2) as index_mb
FROM information_schema.tables
WHERE table_schema = 'wp_pdf_builder'
ORDER BY data_length + index_length DESC;
```

### Métriques infrastructure

#### Configuration Prometheus
```yaml
# prometheus.yml

global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'wp-pdf-builder'
    static_configs:
      - targets: ['localhost:9090']
    metrics_path: '/metrics'

  - job_name: 'mysql'
    static_configs:
      - targets: ['localhost:3306']
    params:
      collect[]:
        - global_status
        - global_variables
        - engine_innodb_status

  - job_name: 'nginx'
    static_configs:
      - targets: ['localhost:8080']
    metrics_path: '/status'
```

#### Métriques système (Node Exporter)
```bash
#!/bin/bash
# system-metrics.sh

# CPU usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')

# Memory usage
MEM_TOTAL=$(free | grep Mem | awk '{print $2}')
MEM_USED=$(free | grep Mem | awk '{print $3}')
MEM_USAGE=$((MEM_USED * 100 / MEM_TOTAL))

# Disk usage
DISK_USAGE=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')

# Network I/O
RX_BYTES=$(cat /proc/net/dev | grep eth0 | awk '{print $2}')
TX_BYTES=$(cat /proc/net/dev | grep eth0 | awk '{print $10}')

# Envoi métriques
curl -X POST http://localhost:9091/metrics/job/system \
     -H "Content-Type: application/json" \
     -d "{
       \"cpu_usage\": $CPU_USAGE,
       \"memory_usage\": $MEM_USAGE,
       \"disk_usage\": $DISK_USAGE,
       \"network_rx\": $RX_BYTES,
       \"network_tx\": $TX_BYTES
     }"
```

## 🚨 Système d'alertes

### Configuration alertes

#### Alertes critiques
```yaml
# alertmanager.yml

global:
  smtp_smarthost: 'smtp.gmail.com:587'
  smtp_from: 'alerts@wp-pdf-builder.com'

route:
  group_by: ['alertname']
  group_wait: 10s
  group_interval: 10s
  repeat_interval: 1h
  receiver: 'team'

receivers:
  - name: 'team'
    email_configs:
      - to: 'devops@company.com'
    slack_configs:
      - api_url: 'https://hooks.slack.com/services/...'
        channel: '#alerts'
```

#### Règles d'alerte Prometheus
```yaml
# alert_rules.yml

groups:
  - name: wp_pdf_builder_alerts
    rules:

    # Alerte disponibilité
    - alert: ServiceDown
      expr: up{job="wp-pdf-builder"} == 0
      for: 5m
      labels:
        severity: critical
      annotations:
        summary: "WP PDF Builder is down"
        description: "WP PDF Builder has been down for more than 5 minutes."

    # Alerte performance
    - alert: HighResponseTime
      expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 2
      for: 5m
      labels:
        severity: warning
      annotations:
        summary: "High response time detected"
        description: "95th percentile response time > 2s for 5 minutes."

    # Alerte erreurs
    - alert: HighErrorRate
      expr: rate(http_requests_total{status=~"5.."}[5m]) / rate(http_requests_total[5m]) > 0.05
      for: 2m
      labels:
        severity: warning
      annotations:
        summary: "High error rate detected"
        description: "Error rate > 5% for 2 minutes."

    # Alerte génération PDF
    - alert: PdfGenerationFailing
      expr: rate(pdf_generation_errors_total[5m]) > 0
      for: 1m
      labels:
        severity: warning
      annotations:
        summary: "PDF generation errors detected"
        description: "PDF generation has failed in the last minute."
```

### Escalade automatique

```python
#!/usr/bin/env python3
# alert-escalation.py

import time
import requests
from datetime import datetime, timedelta

class AlertEscalation:
    def __init__(self):
        self.alerts = {}
        self.escalation_levels = {
            0: {'delay': 0, 'channel': 'devops', 'message': 'Immediate alert'},
            1: {'delay': 300, 'channel': 'management', 'message': 'Escalation: 5min unresolved'},
            2: {'delay': 1800, 'channel': 'executives', 'message': 'Critical: 30min unresolved'},
            3: {'delay': 3600, 'channel': 'crisis', 'message': 'Crisis: 1h unresolved'}
        }

    def process_alert(self, alert_id, severity, message):
        if alert_id not in self.alerts:
            self.alerts[alert_id] = {
                'start_time': datetime.now(),
                'severity': severity,
                'message': message,
                'level': 0,
                'last_escalation': datetime.now()
            }

        self.check_escalation(alert_id)

    def check_escalation(self, alert_id):
        alert = self.alerts[alert_id]
        now = datetime.now()
        duration = (now - alert['start_time']).total_seconds()

        for level, config in self.escalation_levels.items():
            if level > alert['level'] and duration >= config['delay']:
                self.escalate_alert(alert_id, level, config)
                break

    def escalate_alert(self, alert_id, level, config):
        alert = self.alerts[alert_id]

        # Envoi notification
        self.send_notification(config['channel'], config['message'], alert)

        # Mise à jour niveau
        alert['level'] = level
        alert['last_escalation'] = datetime.now()

        print(f"Alert {alert_id} escalated to level {level}")

    def send_notification(self, channel, message, alert):
        # Slack notification
        payload = {
            'channel': f'#{channel}',
            'text': f"🚨 {message}",
            'attachments': [{
                'color': 'danger',
                'fields': [
                    {'title': 'Alert ID', 'value': alert['id'], 'short': True},
                    {'title': 'Severity', 'value': alert['severity'], 'short': True},
                    {'title': 'Duration', 'value': f"{(datetime.now() - alert['start_time']).total_seconds()}s", 'short': True},
                    {'title': 'Message', 'value': alert['message']}
                ]
            }]
        }

        requests.post(SLACK_WEBHOOK_URL, json=payload)

# Utilisation
escalation = AlertEscalation()

# Simulation alerte
escalation.process_alert('service_down', 'critical', 'WP PDF Builder is unreachable')
```

## 📊 Tableaux de bord

### Dashboard Grafana principal

```json
{
  "dashboard": {
    "title": "WP PDF Builder - Production Monitoring",
    "tags": ["wp-pdf-builder", "production"],
    "timezone": "browser",
    "panels": [
      {
        "title": "Service Uptime",
        "type": "stat",
        "targets": [{
          "expr": "up{job=\"wp-pdf-builder\"}",
          "legendFormat": "Uptime"
        }]
      },
      {
        "title": "Response Time",
        "type": "graph",
        "targets": [{
          "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
          "legendFormat": "95th percentile"
        }]
      },
      {
        "title": "PDF Generation Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(pdf_generated_total[5m])",
            "legendFormat": "Generated PDFs"
          },
          {
            "expr": "rate(pdf_generation_errors_total[5m])",
            "legendFormat": "Errors"
          }
        ]
      },
      {
        "title": "Database Performance",
        "type": "table",
        "targets": [{
          "expr": "mysql_global_status_threads_connected",
          "legendFormat": "Active Connections"
        }]
      },
      {
        "title": "Error Rate by Endpoint",
        "type": "table",
        "targets": [{
          "expr": "rate(http_requests_total{status=~\"5..\"}[5m]) / rate(http_requests_total[5m]) * 100",
          "legendFormat": "{{endpoint}}"
        }]
      }
    ]
  }
}
```

### Dashboard métier

```json
{
  "dashboard": {
    "title": "WP PDF Builder - Business Metrics",
    "panels": [
      {
        "title": "PDFs Generated Today",
        "type": "stat",
        "targets": [{
          "expr": "sum(increase(pdf_generated_total[24h]))",
          "legendFormat": "Total PDFs"
        }]
      },
      {
        "title": "Top Templates Used",
        "type": "table",
        "targets": [{
          "expr": "topk(10, sum(rate(pdf_generated_by_template_total[7d])) by (template_name))",
          "legendFormat": "{{template_name}}"
        }]
      },
      {
        "title": "User Activity",
        "type": "graph",
        "targets": [{
          "expr": "sum(rate(user_sessions_total[1h]))",
          "legendFormat": "Active Users"
        }]
      },
      {
        "title": "Revenue Impact",
        "type": "stat",
        "targets": [{
          "expr": "sum(pdf_revenue_total)",
          "legendFormat": "Revenue from PDFs"
        }]
      }
    ]
  }
}
```

## 📝 Logs et tracing

### Configuration logging centralisé

#### Logstash configuration
```conf
# logstash.conf

input {
  file {
    path => "/var/log/wp-pdf-builder/*.log"
    start_position => "beginning"
  }

  beats {
    port => 5044
  }
}

filter {
  grok {
    match => { "message" => "%{TIMESTAMP_ISO8601:timestamp} %{LOGLEVEL:level} %{DATA:class}: %{GREEDYDATA:message}" }
  }

  date {
    match => [ "timestamp", "ISO8601" ]
    target => "@timestamp"
  }
}

output {
  elasticsearch {
    hosts => ["elasticsearch:9200"]
    index => "wp-pdf-builder-%{+YYYY.MM.dd}"
  }

  stdout { codec => rubydebug }
}
```

#### Tracing distribué
```php
<?php
// Tracing.php

class TracingService
{
    private $tracer;

    public function __construct()
    {
        $this->tracer = new JaegerTracer('wp-pdf-builder');
    }

    public function startSpan($operationName, $parentSpan = null)
    {
        $span = $this->tracer->startSpan($operationName, [
            'child_of' => $parentSpan
        ]);

        // Ajout tags contextuels
        $span->setTag('user.id', auth()->id());
        $span->setTag('request.id', request()->header('X-Request-ID'));

        return $span;
    }

    public function tracePdfGeneration($templateId, $data)
    {
        $span = $this->startSpan('pdf.generation');

        try {
            $span->setTag('template.id', $templateId);
            $span->setTag('data.size', strlen(json_encode($data)));

            // Logique génération PDF
            $result = $this->generatePdf($templateId, $data);

            $span->setTag('result.success', true);
            $span->setTag('result.size', strlen($result));

            return $result;

        } catch (Exception $e) {
            $span->setTag('error', true);
            $span->log(['error' => $e->getMessage()]);
            throw $e;
        } finally {
            $span->finish();
        }
    }
}
```

## 🔍 Debugging et diagnostics

### Outil diagnostic automatique

```bash
#!/bin/bash
# diagnostic.sh

echo "🔍 WP PDF Builder Diagnostic Tool"
echo "=================================="

# Vérifications système
echo "📊 System checks:"

# Service status
echo -n "• Nginx: "
systemctl is-active nginx >/dev/null 2>&1 && echo "✅ Running" || echo "❌ Stopped"

echo -n "• PHP-FPM: "
systemctl is-active php8.2-fpm >/dev/null 2>&1 && echo "✅ Running" || echo "❌ Stopped"

echo -n "• MySQL: "
systemctl is-active mysql >/dev/null 2>&1 && echo "✅ Running" || echo "❌ Stopped"

# Ressources système
echo "• CPU Usage: $(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1"%"}')"
echo "• Memory Usage: $(free | grep Mem | awk '{printf "%.1f%%", $3/$2 * 100.0}')"

# Vérifications application
echo ""
echo "🔧 Application checks:"

# Health check
echo -n "• Application health: "
curl -f -s http://localhost/health >/dev/null && echo "✅ OK" || echo "❌ FAIL"

# Base de données
echo -n "• Database connection: "
mysql -u$DB_USER -p$DB_PASS -e "SELECT 1" $DB_NAME >/dev/null 2>&1 && echo "✅ OK" || echo "❌ FAIL"

# Filesystem
echo -n "• Storage writable: "
touch /var/www/html/storage/test.tmp >/dev/null 2>&1 && rm /var/www/html/storage/test.tmp && echo "✅ OK" || echo "❌ FAIL"

# Métriques récentes
echo ""
echo "📈 Recent metrics:"
echo "• PDFs generated (last hour): $(curl -s http://localhost/metrics | grep pdf_generated | tail -1 | awk '{print $2}')"
echo "• Error rate (last 5min): $(curl -s http://localhost/metrics | grep error_rate | tail -1 | awk '{print $2}')"

echo ""
echo "📝 Recommendations:"
# Logique recommandations basée sur les checks
if systemctl is-active nginx >/dev/null 2>&1; then
    echo "• All systems operational"
else
    echo "• Check service status and logs"
fi
```

## 📋 Runbook monitoring

### Procédures opérationnelles

#### Investigation alerte
1. **Réception alerte** : Analyser message et métriques
2. **Vérification** : Confirmer problème avec outils diagnostic
3. **Impact** : Évaluer nombre utilisateurs affectés
4. **Cause** : Identifier origine (logs, métriques)
5. **Résolution** : Appliquer fix ou rollback
6. **Communication** : Informer équipes et utilisateurs

#### Maintenance programmée
1. **Planning** : Communiquer fenêtre maintenance
2. **Préparation** : Tests en staging, préparation rollback
3. **Exécution** : Maintenance avec monitoring continu
4. **Validation** : Tests post-maintenance
5. **Rapport** : Documenter incident et résolution

---

*Monitoring Production - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\deployment\monitoring\performance-metrics.md