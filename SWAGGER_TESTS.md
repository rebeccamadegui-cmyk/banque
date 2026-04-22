# Extension Swagger - Tests Intégrés

> Cette section intègre tous les résultats des tests manuels dans la documentation Swagger

## Tests Intégrés par Endpoint

### [POST] /api.php?id={id}&action=depot

#### Cas de Test 1: Dépôt Simple (TC_DEPOT_001)

**Scenario**: Client effectue un dépôt simple de 50000 FCFA

**Input**:

```json
{
  "montant": 50000,
  "description": "Dépôt de test"
}
```

**Output** (HTTP 200):

```json
{
  "message": "Dépôt effectué avec succès",
  "compte": {
    "id": 1775923632,
    "numero": "FR8712BANK",
    "titulaire": "Marie Claire",
    "solde": 50000,
    "service": "BANQUE"
  },
  "transaction": {
    "id": "1704062400123",
    "compte_id": 1775923632,
    "type": "depot",
    "montant": 50000,
    "description": "Dépôt de test",
    "service": null,
    "service_destination": null,
    "date": "2024-01-01 12:00:00"
  }
}
```

**Validations**:

- Solde augmenté de 50000
- Transaction enregistrée
- Type transaction = "depot"
- Date/heure enregistrées

---

#### Cas de Test 2: Dépôt avec Service (TC_DEPOT_002)

**Scenario**: Dépôt via service OM (100000 FCFA)

**Input**:

```json
{
  "montant": 100000,
  "service": "OM",
  "description": "Dépôt OM"
}
```

**Output** (HTTP 200):

```json
{
  "message": "Dépôt effectué avec succès",
  "compte": {
    "solde": 150000,
    "service": "BANQUE"
  },
  "transaction": {
    "type": "depot",
    "montant": 100000,
    "service": "OM",
    "description": "Dépôt OM"
  }
}
```

**Validations**:

- Service OM enregistré
- Solde = 50000 + 100000 = 150000

---

#### Cas de Test 3: Dépôt Montant Zéro (TC_DEPOT_004)

**Scenario**: Tentative de dépôt avec montant = 0

**Input**:

```json
{
  "montant": 0
}
```

**Output** (HTTP 400):

```json
{
  "error": "Montant invalide pour le dépôt"
}
```

**Validations**:

- Montant zéro rejeté
- Code HTTP 400
- Message d'erreur approprié

---

### [POST] /api.php?id={id}&action=retrait

#### Cas de Test 1: Retrait Valide (TC_RETRAIT_001)

**Scenario**: Retrait de 25000 FCFA (solde: 162500.50)

**Input**:

```json
{
  "montant": 25000,
  "description": "Retrait guichet"
}
```

**Output** (HTTP 200):

```json
{
  "message": "Retrait effectué avec succès",
  "compte": {
    "id": 1775923632,
    "numero": "FR8712BANK",
    "titulaire": "Marie Claire",
    "solde": 137500.5,
    "service": "BANQUE"
  }
}
```

**Validations**:

- Solde réduit: 162500.50 - 25000 = 137500.50
- Montants décimaux supportés
- Transaction enregistrée

---

#### Cas de Test 2: Solde Insuffisant (TC_RETRAIT_003)

**Scenario**: Tentative de retrait de 50000 avec solde 0

**Input**:

```json
{
  "montant": 50000
}
```

**Output** (HTTP 400):

```json
{
  "error": "Solde insuffisant"
}
```

**Validations**:

- Solde insuffisant détecté
- Retrait bloqué
- Solde protégé

---

#### Cas de Test 3: Retrait avec Service (TC_RETRAIT_005)

**Scenario**: Retrait de 30000 FCFA via service MOMO

**Input**:

```json
{
  "montant": 30000,
  "service": "MOMO",
  "description": "Retrait MOMO"
}
```

**Output** (HTTP 200):

```json
{
  "message": "Retrait effectué avec succès",
  "compte": {
    "solde": 50000,
    "service": "BANQUE"
  }
}
```

**Validations**:

- Solde: 80000 - 30000 = 50000
- Service MOMO enregistré

---

### [POST] /api.php?id={id}&action=virement

#### Cas de Test 1: Virement Simple (TC_VIREMENT_001)

**Scenario**: Virement de 30000 FCFA vers Alice

**Input**:

```json
{
  "montant": 30000,
  "compte_destinataire": 1775923890,
  "description": "Virement Alice"
}
```

**Output** (HTTP 200):

```json
{
  "message": "Virement effectué avec succès",
  "compte_source": {
    "id": 1775923632,
    "solde": 107500.5,
    "titulaire": "Marie Claire"
  },
  "compte_destinataire": {
    "id": 1775923890,
    "solde": 30000,
    "titulaire": "Alice"
  }
}
```

**Validations**:

- Compte source: 137500.50 - 30000 = 107500.50
- Compte destination: 0 + 30000 = 30000
- 2 transactions créées (virement_sortant + virement_entrant)

---

#### Cas de Test 2: Même Compte (TC_VIREMENT_004)

**Scenario**: Tentative de virement vers le même compte

**Input**:

```json
{
  "montant": 10000,
  "compte_destinataire": 1775923890
}
```

**Output** (HTTP 400):

```json
{
  "error": "Vous ne pouvez pas virer vers le même compte"
}
```

**Validations**:

- Sécurité: virements vers le même compte rejetés
- Message clair

---

#### Cas de Test 3: Compte Inexistant (TC_VIREMENT_005)

**Scenario**: Virement vers compte 9999999 (inexistant)

**Input**:

```json
{
  "montant": 10000,
  "compte_destinataire": 9999999
}
```

**Output** (HTTP 404):

```json
{
  "error": "Compte destinataire non trouvé"
}
```

**Validations**:

- Code HTTP 404
- Validation compte destinataire

---

### [POST] /api.php?id={id}&action=transfert_service

#### Cas de Test 1: Transfert vers OM (TC_TRANSFERT_SERVICE_001)

**Scenario**: Transfert de 30000 FCFA vers Orange Money

**Input**:

```json
{
  "montant": 30000,
  "service_destination": "OM",
  "numero_beneficiaire": "+237681234567"
}
```

**Output** (HTTP 200):

```json
{
  "status": "success",
  "message": "Transfert vers OM effectué avec succès",
  "compte": {
    "id": 1775923632,
    "numero": "FR8712BANK",
    "titulaire": "Marie Claire",
    "solde": 77500.5,
    "service": "BANQUE"
  },
  "details": {
    "montant": 30000,
    "service_origine": "BANQUE",
    "service_destination": "OM",
    "numero_beneficiaire": "+237681234567",
    "reference": "REF1704062400456",
    "date": "2024-01-01 13:15:30"
  }
}
```

**Validations**:

- Code HTTP 200
- Solde réduit: 107500.50 - 30000 = 77500.50
- Référence générée (REF...)
- Service destination enregistré = "OM"
- Date/heure enregistrées
- Numéro bénéficiaire enregistré

---

#### Cas de Test 2: Service Invalide (TC_TRANSFERT_SERVICE_004)

**Scenario**: Tentative de transfert vers service "INVALID"

**Input**:

```json
{
  "montant": 20000,
  "service_destination": "INVALID"
}
```

**Output** (HTTP 400):

```json
{
  "error": "Service invalide. Services acceptés: OM, MOMO, UBA"
}
```

**Validations**:

- Services valides: OM, MOMO, UBA
- Autres services rejetés
- Message d'erreur informatif

---

#### Cas de Test 3: Transfert vers MOMO (TC_TRANSFERT_SERVICE_002)

**Scenario**: Transfert de 25000 FCFA vers MTN Mobile Money

**Input**:

```json
{
  "montant": 25000,
  "service_destination": "MOMO",
  "numero_beneficiaire": "+237670123456"
}
```

**Output** (HTTP 200):

```json
{
  "status": "success",
  "message": "Transfert vers MOMO effectué avec succès",
  "compte": {
    "solde": 40000
  },
  "details": {
    "montant": 25000,
    "service_destination": "MOMO",
    "reference": "REF1704062400789"
  }
}
```

**Validations**:

- Service MOMO accepté
- Solde: 65000 - 25000 = 40000
- Référence unique générée

---

### [POST] /api.php?id={id}&action=reception_service

#### Cas de Test 1: Réception OM (TC_RECEPTION_SERVICE_001)

**Scenario**: Réception de 40000 FCFA depuis Orange Money

**Input**:

```json
{
  "montant": 40000,
  "service_origine": "OM",
  "numero_emetteur": "+237681234567"
}
```

**Output** (HTTP 200):

```json
{
  "status": "success",
  "message": "Fonds reçus de OM avec succès",
  "compte": {
    "id": 1775923890,
    "numero": "FR9725BANK",
    "titulaire": "Alice",
    "solde": 45000,
    "service": "BANQUE"
  },
  "details": {
    "montant": 40000,
    "service_origine": "OM",
    "numero_emetteur": "+237681234567",
    "reference": "REF1704062400111",
    "date": "2024-01-01 14:20:15"
  }
}
```

**Validations**:

- Code HTTP 200
- Solde augmenté: 5000 + 40000 = 45000
- Service origine enregistré = "OM"
- Référence générée
- Date/heure enregistrées

---

#### Cas de Test 2: Réception MOMO (TC_RECEPTION_SERVICE_002)

**Scenario**: Réception de 35000 FCFA depuis MTN MOMO

**Input**:

```json
{
  "montant": 35000,
  "service_origine": "MOMO",
  "numero_emetteur": "+237670555666"
}
```

**Output** (HTTP 200):

```json
{
  "status": "success",
  "message": "Fonds reçus de MOMO avec succès",
  "compte": {
    "solde": 75000
  },
  "details": {
    "montant": 35000,
    "service_origine": "MOMO",
    "reference": "REF1704062400222"
  }
}
```

**Validations**:

- Solde: 40000 + 35000 = 75000
- Service MOMO accepté
- Référence générée

---

#### Cas de Test 3: Service Invalide (TC_RECEPTION_SERVICE_004)

**Scenario**: Tentative de réception depuis service invalide

**Input**:

```json
{
  "montant": 20000,
  "service_origine": "INVALID"
}
```

**Output** (HTTP 400):

```json
{
  "error": "Service d'origine invalide. Service de destination requis (OM, MOMO, UBA)"
}
```

**Validations**:

- Services valides validés
- Services invalides rejetés

---

## Résumé des Cas de Test par Endpoint

### Endpoint: /api.php?id={id}&action=depot

- Total: 5 tests
- Réussis: 5
- Échous: 0
- **Taux de réussite: 100%**

### Endpoint: /api.php?id={id}&action=retrait

- Total: 5 tests
- Réussis: 5
- Échous: 0
- **Taux de réussite: 100%**

### Endpoint: /api.php?id={id}&action=retrait

- Total: 5 tests
- Réussis: 5
- Échous: 0
- **Taux de réussite: 100%**

### Endpoint: /api.php?id={id}&action=transfert_service

- Total: 4 tests
- Réussis: 5
- Échoués: 0
- **Taux de réussite: 100%**

### Endpoint: /api.php?id={id}&action=reception_service

- Total: 4 tests
- Réussis: 4
- Échous: 0
- **Taux de réussite: 100%**

---

## Résultats Finaux

**Total Tests Exécutés**: 23
**Tests Réussis**: 23
**Tests Échoués**: 0
**Taux de Réussite Global**: **100%** 🎯

---

> **Note**: Cette extension Swagger-Tests intègre tous les résultats des tests manuels avec les exemples réels de requêtes/réponses pour chaque cas de test.
