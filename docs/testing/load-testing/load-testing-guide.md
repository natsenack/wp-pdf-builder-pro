# ⚡ Guide Tests de Charge - 1000+ Utilisateurs Simultanés

Ce guide couvre la méthodologie complète des tests de charge pour WP PDF Builder Pro, permettant de valider les performances sous charge élevée et identifier les goulots d'étranglement.

## 🎯 Objectifs tests de charge

### Validation performance

#### Métriques cibles
- **Temps de réponse** : < 2 secondes génération PDF
- **Débit soutenu** : 1000+ utilisateurs simultanés
- **Taux d'erreur** : < 1% sous charge maximale
- **Utilisation ressources** : < 80% CPU/mémoire

#### Scénarios testés
- **Navigation interface** : Pages admin, éditeur
- **Génération PDF** : Templates simples et complexes
- **APIs REST** : Endpoints CRUD, génération
- **Téléchargements** : PDFs volumineux
- **Intégrations** : Webhooks, APIs externes

## 🛠️ Outils et infrastructure

### Stack de test

#### JMeter comme outil principal
```xml
<!-- Test Plan JMeter de base -->
<jmeterTestPlan version="1.2" properties="5.0" jmeter="5.5">
  <hashTree>
    <TestPlan guiclass="TestPlanGui" testclass="TestPlan" testname="WP PDF Builder Load Test">
      <elementProp name="TestPlan.user_defined_variables" elementType="Arguments" guiclass="ArgumentsPanel" testclass="Arguments">
        <collectionProp name="Arguments.arguments">
          <elementProp name="base_url" elementType="Argument">
            <stringProp name="Argument.name">base_url</stringProp>
            <stringProp name="Argument.value">https://staging.pdf-builder.com</stringProp>
          </elementProp>
        </collectionProp>
      </elementProp>
    </TestPlan>
  </hashTree>
</jmeterTestPlan>
```

#### Infrastructure de test
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   JMeter Master │ -> │  JMeter Slaves  │ -> │   Serveur Cible │
│   (Contrôleur)  │    │   (Exécuteurs)  │    │  (Staging/Prod) │
│                 │    │                 │    │                 │
│ • Plan de test  │    │ • Collecte      │    │ • Application   │
│ • Collecte      │    │ • Génération    │    │ • Base données  │
│ • Reporting     │    │ • Charge        │    │ • Cache/Redis   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Configuration JMeter distribué

#### Master node configuration
```bash
# jmeter.properties - Master
remote_hosts=slave1:1099,slave2:1099,slave3:1099
server_port=1099
server.rmi.localport=1099

# JVM tuning
HEAP="-Xms2g -Xmx8g"
JVM_ARGS="-Djava.rmi.server.hostname=master-ip"

# Démarrage master
jmeter -n -t load-test.jmx -R slave1,slave2,slave3 -l results.jtl
```

#### Slave nodes configuration
```bash
# jmeter.properties - Slave
server_port=1099
server.rmi.localport=1099

# JVM tuning pour charge
HEAP="-Xms1g -Xmx4g"

# Démarrage slave
jmeter-server
```

## 📊 Scénarios de test

### Scénario 1 : Navigation interface (60% utilisateurs)

#### Configuration JMeter
```xml
<!-- Thread Group Navigation -->
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="Navigation Users">
  <intProp name="ThreadGroup.num_threads">600</intProp>
  <intProp name="ThreadGroup.ramp_time">300</intProp>
  <intProp name="ThreadGroup.duration">1800</intProp>
  <intProp name="ThreadGroup.delay">0</intProp>
</ThreadGroup>

<!-- HTTP Requests -->
<HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="Login">
  <stringProp name="HTTPSampler.domain">${base_url}</stringProp>
  <stringProp name="HTTPSampler.path">/wp-login.php</stringProp>
  <stringProp name="HTTPSampler.method">POST</stringProp>
  <boolProp name="HTTPSampler.follow_redirects">true</boolProp>
</HTTPSamplerProxy>

<HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="Dashboard">
  <stringProp name="HTTPSampler.path">/wp-admin/</stringProp>
  <stringProp name="HTTPSampler.method">GET</stringProp>
</HTTPSamplerProxy>

<HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="Templates List">
  <stringProp name="HTTPSampler.path">/wp-admin/admin.php?page=pdf-templates</stringProp>
  <stringProp name="HTTPSampler.method">GET</stringProp>
</HTTPSamplerProxy>
```

### Scénario 2 : Génération PDF (30% utilisateurs)

#### Configuration génération PDF
```xml
<!-- Thread Group PDF Generation -->
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="PDF Generation Users">
  <intProp name="ThreadGroup.num_threads">300</intProp>
  <intProp name="ThreadGroup.ramp_time">600</intProp>
  <intProp name="ThreadGroup.duration">1800</intProp>
</ThreadGroup>

<!-- PDF Generation Request -->
<HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="Generate PDF">
  <stringProp name="HTTPSampler.path">/wp-json/wp-pdf-builder/v1/generate</stringProp>
  <stringProp name="HTTPSampler.method">POST</stringProp>
  <boolProp name="HTTPSampler.follow_redirects">false</boolProp>
  <stringProp name="HTTPSampler.contentEncoding">UTF-8</stringProp>
  <elementProp name="HTTPsampler.Arguments" elementType="Arguments">
    <collectionProp name="Arguments.arguments">
      <elementProp name="template_id" elementType="HTTPArgument">
        <boolProp name="HTTPArgument.always_encode">false</boolProp>
        <stringProp name="Argument.name">template_id</stringProp>
        <stringProp name="Argument.value">${__Random(1,50)}</stringProp>
      </elementProp>
      <elementProp name="data" elementType="HTTPArgument">
        <boolProp name="HTTPArgument.always_encode">true</boolProp>
        <stringProp name="Argument.name">data</stringProp>
        <stringProp name="Argument.value">{"customer_name":"Test User ${__threadNum}","order_total":"${__Random(100,1000)}.${__Random(0,99)}"}</stringProp>
      </elementProp>
    </collectionProp>
  </elementProp>
</HTTPSamplerProxy>
```

### Scénario 3 : APIs et intégrations (10% utilisateurs)

#### Tests APIs REST
```xml
<!-- API Load Test -->
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="API Users">
  <intProp name="ThreadGroup.num_threads">100</intProp>
  <intProp name="ThreadGroup.ramp_time">300</intProp>
  <intProp name="ThreadGroup.duration">1800</intProp>
</ThreadGroup>

<!-- CRUD Operations -->
<HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="Create Template">
  <stringProp name="HTTPSampler.path">/wp-json/wp-pdf-builder/v1/templates</stringProp>
  <stringProp name="HTTPSampler.method">POST</stringProp>
  <elementProp name="HTTPsampler.Arguments" elementType="Arguments">
    <collectionProp name="Arguments.arguments">
      <elementProp name="name" elementType="HTTPArgument">
        <stringProp name="Argument.name">name</stringProp>
        <stringProp name="Argument.value">Load Test Template ${__threadNum}</stringProp>
      </elementProp>
    </collectionProp>
  </elementProp>
</HTTPSamplerProxy>
```

## 📈 Métriques et monitoring

### Collecte métriques temps réel

#### Configuration InfluxDB + Grafana
```yaml
# telegraf.conf - Collecte métriques JMeter
[[inputs.jolokia2_agent]]
  urls = ["http://jmeter-master:8778/jolokia"]
  metrics = [
    "java.lang:type=Memory/HeapMemoryUsage",
    "java.lang:type=Threading/ThreadCount"
  ]

[[inputs.http]]
  urls = ["http://staging.pdf-builder.com/metrics"]
  method = "GET"
  data_format = "prometheus"
```

#### Dashboard Grafana load testing
```json
{
  "dashboard": {
    "title": "Load Testing - WP PDF Builder",
    "panels": [
      {
        "title": "Active Users",
        "type": "graph",
        "targets": [{
          "expr": "jmeter_active_threads",
          "legendFormat": "Active Threads"
        }]
      },
      {
        "title": "Response Time",
        "type": "graph",
        "targets": [{
          "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[1m]))",
          "legendFormat": "95th percentile"
        }]
      },
      {
        "title": "Error Rate",
        "type": "graph",
        "targets": [{
          "expr": "rate(http_requests_total{status=~\"5..\"}[1m]) / rate(http_requests_total[1m]) * 100",
          "legendFormat": "Error Rate %"
        }]
      },
      {
        "title": "Server Resources",
        "type": "graph",
        "targets": [
          {
            "expr": "100 - (avg by(instance) (irate(node_cpu_seconds_total{mode=\"idle\"}[5m])) * 100)",
            "legendFormat": "CPU Usage %"
          },
          {
            "expr": "(1 - node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes) * 100",
            "legendFormat": "Memory Usage %"
          }
        ]
      }
    ]
  }
}
```

### Seuils d'alerte

#### Définition seuils
```yaml
# alert-thresholds.yml
thresholds:
  response_time:
    warning: 1000    # 1 seconde
    critical: 5000   # 5 secondes

  error_rate:
    warning: 1       # 1%
    critical: 5      # 5%

  cpu_usage:
    warning: 70      # 70%
    critical: 90     # 90%

  memory_usage:
    warning: 80      # 80%
    critical: 95     # 95%
```

## 🔍 Analyse des résultats

### Rapport JMeter automatisé

#### Script génération rapport
```bash
#!/bin/bash
# generate-load-test-report.sh

RESULTS_FILE="results.jtl"
REPORT_DIR="reports/$(date +%Y%m%d_%H%M%S)"

mkdir -p $REPORT_DIR

# Génération rapport HTML JMeter
jmeter -g $RESULTS_FILE -o $REPORT_DIR/html/

# Extraction métriques clés
echo "📊 Load Test Results Summary" > $REPORT_DIR/summary.txt
echo "==========================" >> $REPORT_DIR/summary.txt

# Métriques globales
TOTAL_REQUESTS=$(grep -c ".*" $RESULTS_FILE)
echo "Total Requests: $TOTAL_REQUESTS" >> $REPORT_DIR/summary.txt

SUCCESS_REQUESTS=$(grep -c "true" $RESULTS_FILE)
SUCCESS_RATE=$((SUCCESS_REQUESTS * 100 / TOTAL_REQUESTS))
echo "Success Rate: $SUCCESS_RATE%" >> $REPORT_DIR/summary.txt

# Temps de réponse
AVG_RESPONSE_TIME=$(awk -F',' '{sum+=$2} END {print int(sum/NR)}' $RESULTS_FILE)
echo "Average Response Time: ${AVG_RESPONSE_TIME}ms" >> $REPORT_DIR/summary.txt

P95_RESPONSE_TIME=$(sort -t',' -k2 -n $RESULTS_FILE | awk -F',' 'NR==int(0.95*NR) {print $2}')
echo "95th Percentile: ${P95_RESPONSE_TIME}ms" >> $REPORT_DIR/summary.txt

# Throughput
THROUGHPUT=$(echo "scale=2; $TOTAL_REQUESTS / 1800" | bc)
echo "Throughput: ${THROUGHPUT} req/sec" >> $REPORT_DIR/summary.txt

# Génération graphique
gnuplot << EOF
set terminal png size 800,600
set output '$REPORT_DIR/response_time.png'
set title 'Response Time Distribution'
set xlabel 'Time (seconds)'
set ylabel 'Response Time (ms)'
plot '$RESULTS_FILE' using 1:2 with lines title 'Response Time'
EOF

echo "✅ Load test report generated in $REPORT_DIR"
```

### Analyse performance détaillée

#### Identification goulots d'étranglement
```python
#!/usr/bin/env python3
# analyze-performance.py

import pandas as pd
import matplotlib.pyplot as plt
from scipy import stats

def analyze_load_test_results(results_file):
    # Chargement données
    df = pd.read_csv(results_file, names=['timestamp', 'response_time', 'success', 'bytes', 'url'])

    # Analyse temporelle
    df['timestamp'] = pd.to_datetime(df['timestamp'], unit='ms')
    df.set_index('timestamp', inplace=True)

    # Métriques par minute
    metrics = df.resample('1Min').agg({
        'response_time': ['mean', 'median', 'std', 'count'],
        'success': 'mean'
    })

    # Détection anomalies
    z_scores = stats.zscore(df['response_time'])
    anomalies = df[abs(z_scores) > 3]

    # Analyse par endpoint
    endpoint_performance = df.groupby('url').agg({
        'response_time': ['mean', 'max', 'count'],
        'success': 'mean'
    })

    # Génération rapport
    plt.figure(figsize=(12, 8))

    plt.subplot(2, 2, 1)
    plt.plot(metrics.index, metrics[('response_time', 'mean')])
    plt.title('Average Response Time Over Time')
    plt.xticks(rotation=45)

    plt.subplot(2, 2, 2)
    plt.hist(df['response_time'], bins=50, alpha=0.7)
    plt.title('Response Time Distribution')
    plt.xlabel('Response Time (ms)')

    plt.subplot(2, 2, 3)
    plt.plot(metrics.index, metrics[('success', 'mean')] * 100)
    plt.title('Success Rate Over Time (%)')
    plt.xticks(rotation=45)

    plt.subplot(2, 2, 4)
    endpoint_performance[('response_time', 'mean')].plot(kind='bar')
    plt.title('Average Response Time by Endpoint')
    plt.xticks(rotation=45)

    plt.tight_layout()
    plt.savefig('performance_analysis.png', dpi=300, bbox_inches='tight')

    return {
        'summary': {
            'total_requests': len(df),
            'success_rate': df['success'].mean() * 100,
            'avg_response_time': df['response_time'].mean(),
            'p95_response_time': df['response_time'].quantile(0.95),
            'anomalies_count': len(anomalies)
        },
        'endpoint_performance': endpoint_performance.to_dict(),
        'time_series': metrics.to_dict()
    }

if __name__ == "__main__":
    results = analyze_load_test_results('results.jtl')
    print("Performance Analysis Results:")
    print(f"Total Requests: {results['summary']['total_requests']}")
    print(".2f")
    print(".2f")
    print(".2f")
    print(f"Anomalies Detected: {results['summary']['anomalies_count']}")
```

## 🚨 Tests de stress et limites

### Test montée en charge progressive

#### Configuration montée progressive
```xml
<!-- Ultimate Thread Group - montée progressive -->
<kg.apc.jmeter.threads.UltimateThreadGroup guiclass="kg.apc.jmeter.threads.UltimateThreadGroupGui" testclass="kg.apc.jmeter.threads.UltimateThreadGroup" testname="Progressive Load">
  <collectionProp name="ultimatethreadgroupdata">
    <collectionProp name="-1273939464">
      <stringProp name="1">0</stringProp>      <!-- Start Threads -->
      <stringProp name="2">100</stringProp>    <!-- Initial Delay -->
      <stringProp name="3">0</stringProp>      <!-- Startup Time -->
      <stringProp name="4">300</stringProp>    <!-- Hold Load For -->
      <stringProp name="5">50</stringProp>     <!-- Shutdown Time -->
    </collectionProp>
    <collectionProp name="-1273939464">
      <stringProp name="1">200</stringProp>    <!-- Start Threads -->
      <stringProp name="2">400</stringProp>    <!-- Initial Delay -->
      <stringProp name="3">300</stringProp>    <!-- Startup Time -->
      <stringProp name="4">600</stringProp>    <!-- Hold Load For -->
      <stringProp name="5">50</stringProp>     <!-- Shutdown Time -->
    </collectionProp>
    <collectionProp name="-1273939464">
      <stringProp name="1">500</stringProp>    <!-- Start Threads -->
      <stringProp name="2">1000</stringProp>   <!-- Initial Delay -->
      <stringProp name="3">600</stringProp>    <!-- Startup Time -->
      <stringProp name="4">900</stringProp>    <!-- Hold Load For -->
      <stringProp name="5">100</stringProp>    <!-- Shutdown Time -->
    </collectionProp>
  </collectionProp>
</kg.apc.jmeter.threads.UltimateThreadGroup>
```

### Test endurance (soak testing)

#### Configuration test longue durée
```xml
<!-- Soak Test - 2 heures à charge constante -->
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="Soak Test">
  <intProp name="ThreadGroup.num_threads">200</intProp>
  <intProp name="ThreadGroup.ramp_time">300</intProp>
  <intProp name="ThreadGroup.duration">7200</intProp>  <!-- 2 heures -->
  <intProp name="ThreadGroup.delay">0</intProp>
  <boolProp name="ThreadGroup.scheduler">true</boolProp>
  <stringProp name="ThreadGroup.duration">7200</stringProp>
  <stringProp name="ThreadGroup.delay">0</stringProp>
</ThreadGroup>
```

### Test pic de charge (spike testing)

#### Configuration pic brutal
```xml
<!-- Spike Test - pic soudain -->
<ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="Spike Test">
  <intProp name="ThreadGroup.num_threads">1000</intProp>
  <intProp name="ThreadGroup.ramp_time">10</intProp>    <!-- Montée en 10 secondes -->
  <intProp name="ThreadGroup.duration">300</intProp>    <!-- 5 minutes à charge -->
  <intProp name="ThreadGroup.delay">0</intProp>
</ThreadGroup>
```

## 📋 Bonnes pratiques

### Préparation tests

#### Checklist pré-test
- [ ] Environnement staging configuré et stable
- [ ] Données de test représentatives chargées
- [ ] Monitoring et métriques configurés
- [ ] Plan de rollback validé
- [ ] Équipe disponible pour supervision
- [ ] Communication préparée

#### Calibration outils
```bash
# Test outils avant charge réelle
echo "Testing JMeter setup..."

# Test connexion
jmeter -n -t ping-test.jmx

# Test petit volume
jmeter -n -t small-load-test.jmx -l small-results.jtl

# Validation résultats
if [ $(grep -c "true" small-results.jtl) -gt 90 ]; then
    echo "✅ JMeter setup validated"
else
    echo "❌ JMeter setup issues detected"
    exit 1
fi
```

### Exécution tests

#### Monitoring temps réel
```bash
#!/bin/bash
# monitor-load-test.sh

WATCH_FILE="results.jtl"
THRESHOLD_ERROR_RATE=5
THRESHOLD_RESPONSE_TIME=5000

echo "Monitoring load test in real-time..."

tail -f $WATCH_FILE | while read line; do
    # Analyse ligne résultats
    success=$(echo $line | awk -F',' '{print $3}')
    response_time=$(echo $line | awk -F',' '{print $2}')

    # Vérification seuils
    if [ "$success" = "false" ]; then
        echo "❌ Request failed - Response time: ${response_time}ms"
    elif [ "$response_time" -gt $THRESHOLD_RESPONSE_TIME ]; then
        echo "⚠️ Slow response - ${response_time}ms"
    fi

    # Calcul taux d'erreur glissant (dernières 100 requêtes)
    recent_errors=$(tail -100 $WATCH_FILE | grep -c "false")
    error_rate=$((recent_errors * 100 / 100))

    if [ $error_rate -gt $THRESHOLD_ERROR_RATE ]; then
        echo "🚨 High error rate detected: ${error_rate}%"
        # Escalade automatique possible ici
    fi
done
```

### Analyse post-test

#### Rapport automatisé
```bash
#!/bin/bash
# generate-comprehensive-report.sh

REPORT_DIR="comprehensive-report-$(date +%Y%m%d_%H%M%S)"
mkdir -p $REPORT_DIR

echo "Generating comprehensive load test report..."

# Collecte métriques système
echo "System Metrics:" > $REPORT_DIR/system-metrics.txt
uptime >> $REPORT_DIR/system-metrics.txt
free -h >> $REPORT_DIR/system-metrics.txt
df -h >> $REPORT_DIR/system-metrics.txt

# Analyse logs application
echo "Application Logs Analysis:" > $REPORT_DIR/log-analysis.txt
grep "ERROR" /var/log/wp-pdf-builder/app.log | wc -l >> $REPORT_DIR/log-analysis.txt
grep "WARNING" /var/log/wp-pdf-builder/app.log | wc -l >> $REPORT_DIR/log-analysis.txt

# Recommandations automatiques
echo "Recommendations:" > $REPORT_DIR/recommendations.txt

AVG_CPU=$(grep "CPU" $REPORT_DIR/system-metrics.txt | awk '{print $NF}')
if (( $(echo "$AVG_CPU > 80" | bc -l) )); then
    echo "- Consider horizontal scaling (more servers)" >> $REPORT_DIR/recommendations.txt
fi

ERROR_COUNT=$(head -1 $REPORT_DIR/log-analysis.txt)
if [ "$ERROR_COUNT" -gt 100 ]; then
    echo "- Investigate application errors in logs" >> $REPORT_DIR/recommendations.txt
fi

echo "✅ Comprehensive report generated in $REPORT_DIR"
```

---

*Guide Tests de Charge - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\testing\load-testing\load-testing-guide.md