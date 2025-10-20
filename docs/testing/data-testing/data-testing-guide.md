# 🗃️ Guide Tests de Données - Validation Intégrité Production

Ce guide détaille les procédures complètes de test des données pour WP PDF Builder Pro, assurant que les données de production sont correctement extraites, anonymisées et validées avant utilisation en staging.

## 🎯 Objectifs tests de données

### Validation intégrité

#### Critères qualité données
- **Cohérence** : Relations entre tables préservées
- **Complétude** : Pas de données manquantes critiques
- **Exactitude** : Valeurs conformes aux règles métier
- **Actualité** : Données récentes et pertinentes
- **Confidentialité** : Données sensibles anonymisées

#### Métriques cibles
- **Taux correspondance** : > 99% données identiques
- **Temps extraction** : < 4 heures pour 1M+ enregistrements
- **Taux anonymisation** : 100% données sensibles masquées
- **Taux validation** : < 0.1% anomalies détectées

## 🏗️ Architecture extraction données

### Pipeline ETL sécurisé

#### Composants pipeline
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Source Prod   │ -> │   ETL Engine    │ -> │   Staging DB    │
│   (MySQL/Maria) │    │   (Pentaho PDI) │    │   (MySQL Test)  │
│                 │    │                 │    │                 │
│ • wp_posts      │    │ • Extraction    │    │ • wp_posts      │
│ • wp_postmeta   │    │ • Transformation│    │ • wp_postmeta   │
│ • wp_users      │    │ • Anonymisation │    │ • wp_users      │
│ • custom_tables │    │ • Chargement    │    │ • custom_tables │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

#### Sécurité extraction
- **Connexion chiffrée** : SSL/TLS obligatoire
- **Accès limité** : Utilisateur read-only dédié
- **Audit trails** : Logs complets des opérations
- **Chiffrement données** : Transit et stockage

### Configuration Pentaho Data Integration

#### Job principal extraction
```xml
<!-- main-extraction-job.kjb -->
<job>
  <name>WP PDF Builder Data Extraction</name>
  <entries>
    <entry>
      <name>Start</name>
      <type>SPECIAL</type>
      <start>Y</start>
    </entry>
    <entry>
      <name>Extract Core Tables</name>
      <type>TRANS</type>
      <filename>extract-core-tables.ktr</filename>
    </entry>
    <entry>
      <name>Extract Custom Tables</name>
      <type>TRANS</type>
      <filename>extract-custom-tables.ktr</filename>
    </entry>
    <entry>
      <name>Anonymize Sensitive Data</name>
      <type>TRANS</type>
      <filename>anonymize-data.ktr</filename>
    </entry>
    <entry>
      <name>Load to Staging</name>
      <type>TRANS</type>
      <filename>load-staging.ktr</filename>
    </entry>
    <entry>
      <name>Validate Data Integrity</name>
      <type>TRANS</type>
      <filename>validate-integrity.ktr</filename>
    </entry>
    <entry>
      <name>Generate Report</name>
      <type>TRANS</type>
      <filename>generate-report.ktr</filename>
    </entry>
  </entries>
</job>
```

## 📊 Extraction données core WordPress

### Tables essentielles

#### Extraction wp_posts et wp_postmeta
```sql
-- Extraction posts avec métadonnées
SELECT
    p.ID,
    p.post_author,
    p.post_date,
    p.post_date_gmt,
    p.post_content,
    p.post_title,
    p.post_excerpt,
    p.post_status,
    p.comment_status,
    p.ping_status,
    p.post_password,
    p.post_name,
    p.to_ping,
    p.pinged,
    p.post_modified,
    p.post_modified_gmt,
    p.post_content_filtered,
    p.post_parent,
    p.guid,
    p.menu_order,
    p.post_type,
    p.post_mime_type,
    p.comment_count,
    -- Métadonnées associées
    GROUP_CONCAT(
        CONCAT(pm.meta_key, ':', pm.meta_value)
        SEPARATOR '||'
    ) as post_metadata
FROM wp_posts p
LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id
WHERE p.post_type IN ('pdf_template', 'pdf_builder', 'attachment')
  AND p.post_status IN ('publish', 'draft', 'private')
GROUP BY p.ID;
```

#### Extraction utilisateurs (anonymisés)
```sql
-- Extraction utilisateurs avec anonymisation
SELECT
    u.ID,
    -- Anonymisation données personnelles
    CONCAT('user_', u.ID) as user_login,
    CONCAT('user_', u.ID, '@test.local') as user_email,
    'anonymized' as user_pass,
    CONCAT('User ', u.ID) as display_name,
    u.user_registered,
    u.user_status,
    u.user_activation_key,
    -- Métadonnées (filtrées)
    (SELECT GROUP_CONCAT(
        CONCAT(um.meta_key, ':',
            CASE
                WHEN um.meta_key LIKE '%email%' THEN CONCAT('email_', um.user_id, '@test.local')
                WHEN um.meta_key LIKE '%phone%' THEN CONCAT('phone_', um.user_id)
                WHEN um.meta_key LIKE '%address%' THEN 'anonymized_address'
                ELSE um.meta_value
            END
        ) SEPARATOR '||'
     ) FROM wp_usermeta um WHERE um.user_id = u.ID
    ) as user_metadata
FROM wp_users u
WHERE u.ID IN (
    SELECT DISTINCT post_author
    FROM wp_posts
    WHERE post_type IN ('pdf_template', 'pdf_builder')
);
```

### Tables personnalisées PDF Builder

#### Extraction templates PDF
```sql
-- Extraction templates personnalisés
SELECT
    t.id,
    t.name,
    t.description,
    t.template_data,
    t.created_at,
    t.updated_at,
    t.created_by,
    t.status,
    -- Éléments template
    (SELECT GROUP_CONCAT(
        CONCAT(te.element_type, ':', te.element_data)
        SEPARATOR '|||'
     ) FROM template_elements te WHERE te.template_id = t.id
    ) as template_elements,
    -- Permissions
    (SELECT GROUP_CONCAT(
        CONCAT(tp.user_id, ':', tp.permission_level)
        SEPARATOR '||'
     ) FROM template_permissions tp WHERE tp.template_id = t.id
    ) as template_permissions
FROM pdf_templates t
WHERE t.status IN ('active', 'draft');
```

#### Extraction données génération
```sql
-- Extraction historique génération PDF
SELECT
    g.id,
    g.template_id,
    g.user_id,
    g.generation_date,
    g.parameters,
    g.pdf_size,
    g.generation_time,
    g.status,
    g.error_message,
    -- Anonymisation données sensibles dans paramètres
    CASE
        WHEN JSON_EXTRACT(g.parameters, '$.customer_email') IS NOT NULL
        THEN JSON_SET(g.parameters, '$.customer_email', CONCAT('customer_', g.id, '@test.local'))
        ELSE g.parameters
    END as anonymized_parameters
FROM pdf_generations g
WHERE g.generation_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
ORDER BY g.generation_date DESC
LIMIT 100000; -- Échantillon représentatif
```

## 🔒 Anonymisation données sensibles

### Stratégies anonymisation

#### Données personnelles (PII)
```python
#!/usr/bin/env python3
# data-anonymizer.py

import hashlib
import random
import string
from faker import Faker

class DataAnonymizer:
    def __init__(self):
        self.fake = Faker('fr_FR')  # Données françaises
        self.consistent_map = {}   # Mapping cohérent pour relations

    def anonymize_email(self, original_email, user_id):
        """Anonymisation emails avec cohérence"""
        if user_id not in self.consistent_map:
            domain = random.choice(['test.local', 'example.com', 'demo.fr'])
            self.consistent_map[user_id] = f"user_{user_id}@{domain}"

        return self.consistent_map[user_id]

    def anonymize_phone(self, original_phone, user_id):
        """Anonymisation numéros téléphone"""
        if user_id not in self.consistent_map:
            # Génère numéro français valide
            self.consistent_map[user_id] = self.fake.phone_number()

        return self.consistent_map[user_id]

    def anonymize_address(self, original_address):
        """Anonymisation adresses"""
        return self.fake.address().replace('\n', ', ')

    def anonymize_name(self, original_name, user_id):
        """Anonymisation noms avec cohérence"""
        if user_id not in self.consistent_map:
            self.consistent_map[user_id] = self.fake.name()

        return self.consistent_map[user_id]

    def hash_sensitive_data(self, data, salt="pdf_builder_salt"):
        """Hashage données sensibles irréversibles"""
        return hashlib.sha256(f"{data}{salt}".encode()).hexdigest()

# Utilisation
anonymizer = DataAnonymizer()

# Exemple anonymisation DataFrame pandas
def anonymize_dataframe(df):
    df_copy = df.copy()

    for idx, row in df_copy.iterrows():
        user_id = row['user_id']

        # Anonymisation colonnes sensibles
        if 'email' in df_copy.columns:
            df_copy.at[idx, 'email'] = anonymizer.anonymize_email(row['email'], user_id)

        if 'phone' in df_copy.columns:
            df_copy.at[idx, 'phone'] = anonymizer.anonymize_phone(row['phone'], user_id)

        if 'customer_name' in df_copy.columns:
            df_copy.at[idx, 'customer_name'] = anonymizer.anonymize_name(row['customer_name'], user_id)

        if 'address' in df_copy.columns:
            df_copy.at[idx, 'address'] = anonymizer.anonymize_address(row['address'])

    return df_copy
```

#### Données financières
```sql
-- Fonction anonymisation données financières
DELIMITER //

CREATE FUNCTION anonymize_financial_data(original_value DECIMAL(10,2), record_id INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE anonymized_value DECIMAL(10,2);

    -- Préserve plage de valeurs mais masque précision
    SET anonymized_value = ROUND(original_value / 10) * 10;

    -- Ajoute variation aléatoire cohérente basée sur record_id
    SET anonymized_value = anonymized_value + (record_id % 100) - 50;

    -- Assure valeur positive
    RETURN GREATEST(anonymized_value, 0.01);
END //

DELIMITER ;

-- Utilisation dans extraction
SELECT
    order_id,
    anonymize_financial_data(order_total, order_id) as order_total_anonymized,
    order_date,
    customer_id
FROM orders;
```

### Validation anonymisation

#### Tests conformité RGPD
```python
#!/usr/bin/env python3
# gdpr-compliance-checker.py

import re
import pandas as pd

class GDPRComplianceChecker:
    def __init__(self):
        # Patterns RGPD pour données personnelles
        self.pii_patterns = {
            'email': r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b',
            'phone_fr': r'\b(0|\+33)[1-9]([-. ]?[0-9]{2}){4}\b',
            'phone_int': r'\+\d{1,3}[-. ]?\d{1,14}',
            'ssn': r'\b\d{3}[-.]?\d{2}[-.]?\d{4}\b',  # US SSN
            'credit_card': r'\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b',
            'iban': r'\b[A-Z]{2}\d{2}[A-Z0-9]{11,30}\b',
            'address': r'\d+\s+[A-Za-z0-9\s,.-]+(?:\d{5})?'  # Adresse française simplifiée
        }

    def check_pii_leakage(self, data):
        """Vérifie présence données personnelles non anonymisées"""
        findings = []

        for column in data.columns:
            column_data = str(data[column].dropna())

            for pii_type, pattern in self.pii_patterns.items():
                matches = re.findall(pattern, column_data)
                if matches:
                    findings.append({
                        'column': column,
                        'pii_type': pii_type,
                        'matches_count': len(matches),
                        'sample_matches': matches[:3]  # Premiers exemples
                    })

        return findings

    def validate_anonymization(self, original_data, anonymized_data):
        """Valide que l'anonymisation a été effective"""
        original_findings = self.check_pii_leakage(original_data)
        anonymized_findings = self.check_pii_leakage(anonymized_data)

        validation_results = {
            'original_pii_count': len(original_findings),
            'anonymized_pii_count': len(anonymized_findings),
            'anonymization_effective': len(anonymized_findings) == 0,
            'details': []
        }

        # Détails par type de données
        for finding in original_findings:
            pii_type = finding['pii_type']
            anonymized_matches = [
                f for f in anonymized_findings
                if f['pii_type'] == pii_type
            ]

            validation_results['details'].append({
                'pii_type': pii_type,
                'original_count': finding['matches_count'],
                'anonymized_count': len(anonymized_matches),
                'properly_anonymized': len(anonymized_matches) == 0
            })

        return validation_results

# Exemple utilisation
checker = GDPRComplianceChecker()

# Chargement données
original_df = pd.read_csv('original_customer_data.csv')
anonymized_df = pd.read_csv('anonymized_customer_data.csv')

# Validation
results = checker.validate_anonymization(original_df, anonymized_df)

if results['anonymization_effective']:
    print("✅ Anonymisation RGPD conforme")
else:
    print("❌ Problèmes d'anonymisation détectés:")
    for detail in results['details']:
        if not detail['properly_anonymized']:
            print(f"  - {detail['pii_type']}: {detail['anonymized_count']} occurrences restantes")
```

## ✅ Validation intégrité données

### Contrôles automatisés

#### Validation structurelle
```sql
-- Validation cohérence données extraites
DELIMITER //

CREATE PROCEDURE validate_data_integrity()
BEGIN
    DECLARE error_count INT DEFAULT 0;
    DECLARE warning_count INT DEFAULT 0;

    -- Vérification clés étrangères
    SELECT COUNT(*) INTO error_count
    FROM pdf_templates t
    LEFT JOIN wp_users u ON t.created_by = u.ID
    WHERE u.ID IS NULL AND t.created_by IS NOT NULL;

    IF error_count > 0 THEN
        INSERT INTO validation_log (check_type, severity, message, count)
        VALUES ('foreign_key', 'ERROR', 'Templates with invalid user references', error_count);
    END IF;

    -- Vérification données requises
    SELECT COUNT(*) INTO error_count
    FROM pdf_templates
    WHERE name IS NULL OR name = '';

    IF error_count > 0 THEN
        INSERT INTO validation_log (check_type, severity, message, count)
        VALUES ('required_data', 'ERROR', 'Templates without required name', error_count);
    END IF;

    -- Vérification plages de valeurs
    SELECT COUNT(*) INTO warning_count
    FROM pdf_generations
    WHERE generation_time < 0 OR generation_time > 300; -- > 5 minutes anormal

    IF warning_count > 0 THEN
        INSERT INTO validation_log (check_type, severity, message, count)
        VALUES ('value_range', 'WARNING', 'Unusual generation times detected', warning_count);
    END IF;

    -- Validation JSON templates
    SELECT COUNT(*) INTO error_count
    FROM pdf_templates
    WHERE JSON_VALID(template_data) = 0;

    IF error_count > 0 THEN
        INSERT INTO validation_log (check_type, severity, message, count)
        VALUES ('json_validity', 'ERROR', 'Invalid JSON in template data', error_count);
    END IF;

END //

DELIMITER ;
```

#### Validation volumétrie
```python
#!/usr/bin/env python3
# volume-validation.py

import pandas as pd
from sqlalchemy import create_engine

class VolumeValidator:
    def __init__(self, prod_connection, staging_connection):
        self.prod_engine = create_engine(prod_connection)
        self.staging_engine = create_engine(staging_connection)

    def compare_table_volumes(self):
        """Compare volumes entre prod et staging"""
        tables_to_check = [
            'wp_posts', 'wp_postmeta', 'wp_users', 'wp_usermeta',
            'pdf_templates', 'pdf_generations', 'template_elements'
        ]

        results = []

        for table in tables_to_check:
            # Volume production
            prod_count = pd.read_sql(f"SELECT COUNT(*) as count FROM {table}", self.prod_engine).iloc[0]['count']

            # Volume staging
            staging_count = pd.read_sql(f"SELECT COUNT(*) as count FROM {table}", self.staging_engine).iloc[0]['count']

            # Calcul différence
            difference = abs(prod_count - staging_count)
            difference_percent = (difference / prod_count * 100) if prod_count > 0 else 0

            results.append({
                'table': table,
                'prod_count': prod_count,
                'staging_count': staging_count,
                'difference': difference,
                'difference_percent': difference_percent,
                'acceptable': difference_percent < 1.0  # < 1% différence acceptable
            })

        return results

    def validate_sample_data(self, sample_size=1000):
        """Validation échantillon données"""
        validation_results = []

        # Échantillon templates
        prod_templates = pd.read_sql("""
            SELECT id, name, LENGTH(template_data) as data_size
            FROM pdf_templates
            ORDER BY RAND() LIMIT %s
        """, self.prod_engine, params=[sample_size])

        staging_templates = pd.read_sql("""
            SELECT id, name, LENGTH(template_data) as data_size
            FROM pdf_templates
            WHERE id IN %s
        """, self.staging_engine, params=[tuple(prod_templates['id'])])

        # Comparaison
        merged = prod_templates.merge(staging_templates, on='id', suffixes=('_prod', '_staging'))

        # Vérifications
        name_matches = (merged['name_prod'] == merged['name_staging']).sum()
        size_matches = abs(merged['data_size_prod'] - merged['data_size_staging']) < 100  # Tolérance 100 octets

        validation_results.append({
            'check': 'template_names',
            'matches': name_matches,
            'total': len(merged),
            'match_rate': name_matches / len(merged) * 100
        })

        validation_results.append({
            'check': 'template_sizes',
            'matches': size_matches.sum(),
            'total': len(merged),
            'match_rate': size_matches.sum() / len(merged) * 100
        })

        return validation_results

# Utilisation
validator = VolumeValidator(prod_conn, staging_conn)

volume_results = validator.compare_table_volumes()
sample_results = validator.validate_sample_data()

print("Volume Validation Results:")
for result in volume_results:
    status = "✅" if result['acceptable'] else "❌"
    print(f"{status} {result['table']}: {result['prod_count']} → {result['staging_count']} ({result['difference_percent']:.2f}% diff)")

print("\nSample Validation Results:")
for result in sample_results:
    print(f"✅ {result['check']}: {result['match_rate']:.2f}% match rate")
```

## 📋 Procédures opérationnelles

### Préparation extraction

#### Checklist pré-extraction
- [ ] Fenêtre maintenance planifiée (nuit/weekend)
- [ ] Sauvegarde complète production effectuée
- [ ] Connexions réseau sécurisées validées
- [ ] Utilisateur extraction avec permissions minimales
- [ ] Logs audit activés
- [ ] Monitoring ressources configuré

#### Configuration environnement
```bash
#!/bin/bash
# prepare-extraction-environment.sh

echo "Preparing data extraction environment..."

# Création répertoire extraction sécurisé
EXTRACTION_DIR="/secure/extraction/$(date +%Y%m%d_%H%M%S)"
mkdir -p $EXTRACTION_DIR
chmod 700 $EXTRACTION_DIR

# Configuration Pentaho
export PENTAHO_HOME="/opt/pentaho"
export KETTLE_HOME="$EXTRACTION_DIR/.kettle"

# Variables environnement
export DB_PROD_HOST="prod-db-server"
export DB_PROD_USER="extraction_user"
export DB_PROD_PASS="$(aws secretsmanager get-secret-value --secret-id prod-db-pass --query SecretString --output text)"
export DB_STAGING_HOST="staging-db-server"

# Validation connexions
echo "Testing database connections..."
mysql -h$DB_PROD_HOST -u$DB_PROD_USER -p$DB_PROD_PASS -e "SELECT 1" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Production DB connection OK"
else
    echo "❌ Production DB connection FAILED"
    exit 1
fi

mysql -h$DB_STAGING_HOST -uroot -p$DB_STAGING_PASS -e "SELECT 1" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ Staging DB connection OK"
else
    echo "❌ Staging DB connection FAILED"
    exit 1
fi

echo "Environment preparation complete"
```

### Exécution extraction

#### Monitoring temps réel
```bash
#!/bin/bash
# monitor-extraction.sh

LOG_FILE="extraction-$(date +%Y%m%d_%H%M%S).log"
PID_FILE="extraction.pid"

# Fonction logging
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a $LOG_FILE
}

# Démarrage monitoring
log "Starting extraction monitoring..."

# Lancement extraction Pentaho en arrière-plan
/opt/pentaho/kitchen.sh -file=main-extraction-job.kjb > extraction.out 2>&1 &
echo $! > $PID_FILE

# Monitoring boucle
while kill -0 $(cat $PID_FILE) 2>/dev/null; do
    # Métriques base production
    PROD_CONNECTIONS=$(mysql -h$DB_PROD_HOST -u$DB_PROD_USER -p$DB_PROD_PASS -e "SHOW PROCESSLIST" 2>/dev/null | wc -l)
    PROD_LOAD=$(mysql -h$DB_PROD_HOST -u$DB_PROD_USER -p$DB_PROD_PASS -e "SHOW ENGINE INNODB STATUS\G" | grep "History list length" | awk '{print $4}')

    # Métriques base staging
    STAGING_LOAD=$(mysql -h$DB_STAGING_HOST -uroot -p$DB_STAGING_PASS -e "SHOW ENGINE INNODB STATUS\G" | grep "History list length" | awk '{print $4}')

    # Métriques système
    CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}')
    MEM_USAGE=$(free | grep Mem | awk '{printf "%.2f", $3/$2 * 100.0}')

    log "Prod connections: $PROD_CONNECTIONS | Prod load: $PROD_LOAD | Staging load: $STAGING_LOAD | CPU: ${CPU_USAGE}% | Memory: ${MEM_USAGE}%"

    # Alertes seuils
    if (( $(echo "$CPU_USAGE > 80" | bc -l) )); then
        log "⚠️ HIGH CPU USAGE: ${CPU_USAGE}%"
    fi

    if (( $(echo "$MEM_USAGE > 85" | bc -l) )); then
        log "⚠️ HIGH MEMORY USAGE: ${MEM_USAGE}%"
    fi

    if [ "$PROD_CONNECTIONS" -gt 50 ]; then
        log "⚠️ HIGH PRODUCTION CONNECTIONS: $PROD_CONNECTIONS"
    fi

    sleep 30
done

log "Extraction process completed"
```

### Post-extraction validation

#### Rapport final automatisé
```bash
#!/bin/bash
# generate-extraction-report.sh

REPORT_DIR="extraction-report-$(date +%Y%m%d_%H%M%S)"
mkdir -p $REPORT_DIR

echo "Generating data extraction validation report..."

# Métriques générales
echo "📊 Data Extraction Report" > $REPORT_DIR/summary.txt
echo "========================" >> $REPORT_DIR/summary.txt
echo "Date: $(date)" >> $REPORT_DIR/summary.txt
echo "Duration: $(cat extraction.log | grep -E "Start|End" | tail -1)" >> $REPORT_DIR/summary.txt

# Statistiques volumétrie
echo -e "\n📈 Volume Statistics:" >> $REPORT_DIR/summary.txt
mysql -h$DB_STAGING_HOST -uroot -p$DB_STAGING_PASS -e "
SELECT
    table_name,
    table_rows as 'Row Count',
    ROUND(data_length/1024/1024, 2) as 'Data Size (MB)',
    ROUND(index_length/1024/1024, 2) as 'Index Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'wp_pdf_builder_staging'
ORDER BY data_length DESC;
" >> $REPORT_DIR/summary.txt

# Résultats validation
echo -e "\n✅ Validation Results:" >> $REPORT_DIR/summary.txt
mysql -h$DB_STAGING_HOST -uroot -p$DB_STAGING_PASS -e "
SELECT
    check_type,
    severity,
    message,
    count,
    created_at
FROM validation_log
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY created_at DESC;
" >> $REPORT_DIR/summary.txt

# Conformité RGPD
echo -e "\n🔒 GDPR Compliance Check:" >> $REPORT_DIR/summary.txt
if [ -f gdpr-check-results.json ]; then
    cat gdpr-check-results.json | jq -r '
        "PII Types Checked: \(.pii_types_checked)",
        "Anonymization Effective: \(.anonymization_effective)",
        "Remaining PII: \(.remaining_pii_count)"
    ' >> $REPORT_DIR/summary.txt
else
    echo "GDPR check not completed" >> $REPORT_DIR/summary.txt
fi

# Recommandations
echo -e "\n💡 Recommendations:" >> $REPORT_DIR/recommendations.txt

ERROR_COUNT=$(grep -c "ERROR" $REPORT_DIR/summary.txt)
WARNING_COUNT=$(grep -c "WARNING" $REPORT_DIR/summary.txt)

if [ "$ERROR_COUNT" -gt 0 ]; then
    echo "- Address critical data integrity errors before proceeding" >> $REPORT_DIR/recommendations.txt
fi

if [ "$WARNING_COUNT" -gt 5 ]; then
    echo "- Review data quality warnings" >> $REPORT_DIR/recommendations.txt
fi

DATA_SIZE=$(grep "Total Data Size" $REPORT_DIR/summary.txt | awk '{print $4}')
if (( $(echo "$DATA_SIZE > 5000" | bc -l) )); then
    echo "- Consider data archiving strategy for large datasets" >> $REPORT_DIR/recommendations.txt
fi

echo "✅ Extraction validation report generated in $REPORT_DIR"
```

---

*Guide Tests de Données - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\testing\data-testing\data-testing-guide.md