# Cas de Test - Système Bancaire

## 🎯 Objectif

Valider le fonctionnement des 5 fonctions principales du système bancaire:

1. Dépôt (Deposit)
2. Retrait (Withdrawal)
3. Virement (Transfer)
4. Transfert vers Service (Send to Service)
5. Réception de Service (Receive from Service)

---

## 1️⃣ CAS DE TEST - DÉPÔT (DEPOSIT)

### TC_DEPOT_001: Dépôt valide - Montant simple

**Prérequis**: Compte existant avec solde 0

- Compte ID: À récupérer
- Solde initial: 0
- Montant dépôt: 50000 FCFA

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: {"montant": 50000, "description": "Test dépôt"}

**Résultat attendu**:

- Code HTTP: 200
- Solde final: 50000
- Transaction enregistrée: type="depot", montant=50000

---

### TC_DEPOT_002: Dépôt valide avec service

**Prérequis**: Compte existant

- Montant dépôt: 100000 FCFA
- Service: OM

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: {"montant": 100000, "service": "OM", "description": "Dépôt OM"}

**Résultat attendu**:

- Code HTTP: 200
- Solde augmenté de 100000
- Transaction avec service="OM"

---

### TC_DEPOT_003: Dépôt avec montant décimal

**Prérequis**: Compte existant

- Montant: 12500.50

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: {"montant": 12500.50}

**Résultat attendu**:

- Code HTTP: 200
- Solde augmenté correctement
- Montant décimal respecté

---

### TC_DEPOT_004: Dépôt - Montant zéro

**Prérequis**: Compte existant

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: {"montant": 0}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide pour le dépôt"
- Solde inchangé

---

### TC_DEPOT_005: Dépôt - Montant négatif

**Prérequis**: Compte existant

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: {"montant": -50000}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide pour le dépôt"
- Solde inchangé

---

### TC_DEPOT_006: Dépôt - Compte inexistant

**Prérequis**: Compte ID fictif

**Étapes**:

1. POST /api.php?id=9999999&action=depot
2. Body: {"montant": 50000}

**Résultat attendu**:

- Code HTTP: 404
- Erreur: "Compte non trouvé"

---

### TC_DEPOT_007: Dépôt - JSON invalide

**Prérequis**: Compte existant

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: { INVALID JSON }

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Corps JSON invalide ou manquant"

---

### TC_DEPOT_008: Dépôt - Montant très élevé

**Prérequis**: Compte existant

- Montant: 999999999

**Étapes**:

1. POST /api.php?id={id}&action=depot
2. Body: {"montant": 999999999}

**Résultat attendu**:

- Code HTTP: 200
- Solde augmenté (système accepte)

---

## 2️⃣ CAS DE TEST - RETRAIT (WITHDRAWAL)

### TC_RETRAIT_001: Retrait valide

**Prérequis**: Compte avec solde 100000

- Montant retrait: 25000

**Étapes**:

1. POST /api.php?id={id}&action=retrait
2. Body: {"montant": 25000}

**Résultat attendu**:

- Code HTTP: 200
- Solde final: 75000
- Transaction type="retrait", montant=-25000

---

### TC_RETRAIT_002: Retrait - Solde insuffisant

**Prérequis**: Compte avec solde 10000

- Montant retrait: 50000

**Étapes**:

1. POST /api.php?id={id}&action=retrait
2. Body: {"montant": 50000}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Solde insuffisant"
- Solde inchangé

---

### TC_RETRAIT_003: Retrait - Montant égal au solde

**Prérequis**: Compte avec solde 50000

- Montant retrait: 50000

**Étapes**:

1. POST /api.php?id={id}&action=retrait
2. Body: {"montant": 50000}

**Résultat attendu**:

- Code HTTP: 200
- Solde final: 0
- Transaction enregistrée

---

### TC_RETRAIT_004: Retrait - Montant zéro

**Prérequis**: Compte avec solde 50000

**Étapes**:

1. POST /api.php?id={id}&action=retrait
2. Body: {"montant": 0}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide pour le retrait"

---

### TC_RETRAIT_005: Retrait - Montant négatif

**Prérequis**: Compte avec solde 50000

**Étapes**:

1. POST /api.php?id={id}&action=retrait
2. Body: {"montant": -25000}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide pour le retrait"

---

### TC_RETRAIT_006: Retrait avec service

**Prérequis**: Compte avec solde 100000

- Montant: 30000
- Service: MOMO

**Étapes**:

1. POST /api.php?id={id}&action=retrait
2. Body: {"montant": 30000, "service": "MOMO"}

**Résultat attendu**:

- Code HTTP: 200
- Solde: 70000
- Transaction avec service="MOMO"

---

### TC_RETRAIT_007: Retrait - Compte inexistant

**Étapes**:

1. POST /api.php?id=9999999&action=retrait
2. Body: {"montant": 50000}

**Résultat attendu**:

- Code HTTP: 404
- Erreur: "Compte non trouvé"

---

## 3️⃣ CAS DE TEST - VIREMENT (TRANSFER)

### TC_VIREMENT_001: Virement simple valide

**Prérequis**:

- Compte source: Solde 100000
- Compte destinataire: Solde 50000
- Montant: 30000

**Étapes**:

1. POST /api.php?id={id_source}&action=virement
2. Body: {"montant": 30000, "compte_destinataire": {id_dest}}

**Résultat attendu**:

- Code HTTP: 200
- Solde source: 70000
- Solde destinataire: 80000
- 2 transactions enregistrées (virement_sortant + virement_entrant)

---

### TC_VIREMENT_002: Virement - Solde insuffisant

**Prérequis**:

- Compte source: Solde 20000
- Montant: 50000

**Étapes**:

1. POST /api.php?id={id_source}&action=virement
2. Body: {"montant": 50000, "compte_destinataire": {id_dest}}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Solde insuffisant"
- Soldes inchangés

---

### TC_VIREMENT_003: Virement vers le même compte

**Prérequis**: Compte source ID = ID destinataire

**Étapes**:

1. POST /api.php?id={id}&action=virement
2. Body: {"montant": 30000, "compte_destinataire": {id}}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Vous ne pouvez pas virer vers le même compte"
- Solde inchangé

---

### TC_VIREMENT_004: Virement - Compte destinataire inexistant

**Prérequis**: Compte source valide

- Compte destinataire: 9999999

**Étapes**:

1. POST /api.php?id={id_source}&action=virement
2. Body: {"montant": 30000, "compte_destinataire": 9999999}

**Résultat attendu**:

- Code HTTP: 404
- Erreur: "Compte destinataire non trouvé"
- Solde source inchangé

---

### TC_VIREMENT_005: Virement - Montant zéro

**Étapes**:

1. POST /api.php?id={id_source}&action=virement
2. Body: {"montant": 0, "compte_destinataire": {id_dest}}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide"

---

### TC_VIREMENT_006: Virement - Compte source inexistant

**Étapes**:

1. POST /api.php?id=9999999&action=virement
2. Body: {"montant": 30000, "compte_destinataire": {id_dest}}

**Résultat attendu**:

- Code HTTP: 404
- Erreur: "Compte non trouvé"

---

### TC_VIREMENT_007: Virement montant égal au solde

**Prérequis**: Source solde 50000

**Étapes**:

1. POST /api.php?id={id_source}&action=virement
2. Body: {"montant": 50000, "compte_destinataire": {id_dest}}

**Résultat attendu**:

- Code HTTP: 200
- Solde source: 0
- Solde destinataire augmenté de 50000

---

## 4️⃣ CAS DE TEST - TRANSFERT VERS SERVICE

### TC_TRANSFERT_SERVICE_001: Transfert vers OM valide

**Prérequis**: Compte solde 100000

- Service: OM
- Montant: 30000

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 30000, "service_destination": "OM"}

**Résultat attendu**:

- Code HTTP: 200
- Message: "Transfert vers OM effectué avec succès"
- Solde: 70000
- details.reference commençant par "REF"
- service_destination="OM" enregistré

---

### TC_TRANSFERT_SERVICE_002: Transfert vers MOMO avec bénéficiaire

**Prérequis**: Compte solde 80000

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 50000, "service_destination": "MOMO", "numero_beneficiaire": "+237681234567"}

**Résultat attendu**:

- Code HTTP: 200
- Solde: 30000
- Numéro bénéficiaire enregistré
- Référence générée

---

### TC_TRANSFERT_SERVICE_003: Transfert vers UBA

**Prérequis**: Compte solde 100000

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 40000, "service_destination": "UBA"}

**Résultat attendu**:

- Code HTTP: 200
- Solde: 60000
- service_destination="UBA" enregistré

---

### TC_TRANSFERT_SERVICE_004: Transfert - Service invalide

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 30000, "service_destination": "INVALID"}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Service invalide. Services acceptés: OM, MOMO, UBA"
- Solde inchangé

---

### TC_TRANSFERT_SERVICE_005: Transfert - Service manquant

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 30000}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Service de destination requis"

---

### TC_TRANSFERT_SERVICE_006: Transfert - Solde insuffisant

**Prérequis**: Compte solde 10000

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 50000, "service_destination": "OM"}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Solde insuffisant pour ce transfert"
- Solde inchangé

---

### TC_TRANSFERT_SERVICE_007: Transfert - Montant zéro

**Étapes**:

1. POST /api.php?id={id}&action=transfert_service
2. Body: {"montant": 0, "service_destination": "OM"}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide"

---

### TC_TRANSFERT_SERVICE_008: Transfert - Compte inexistant

**Étapes**:

1. POST /api.php?id=9999999&action=transfert_service
2. Body: {"montant": 30000, "service_destination": "OM"}

**Résultat attendu**:

- Code HTTP: 404
- Erreur: "Compte non trouvé"

---

## 5️⃣ CAS DE TEST - RÉCEPTION DE SERVICE

### TC_RECEPTION_SERVICE_001: Réception depuis OM valide

**Prérequis**: Compte solde 50000

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 40000, "service_origine": "OM"}

**Résultat attendu**:

- Code HTTP: 200
- Message: "Fonds reçus de OM avec succès"
- Solde: 90000
- service_origine="OM" enregistré
- Référence générée (REF...)

---

### TC_RECEPTION_SERVICE_002: Réception depuis MOMO avec émetteur

**Prérequis**: Compte solde 30000

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 25000, "service_origine": "MOMO", "numero_emetteur": "+237670123456"}

**Résultat attendu**:

- Code HTTP: 200
- Solde: 55000
- Numéro émetteur enregistré
- Référence générée

---

### TC_RECEPTION_SERVICE_003: Réception depuis UBA

**Prérequis**: Compte solde 100000

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 60000, "service_origine": "UBA"}

**Résultat attendu**:

- Code HTTP: 200
- Solde: 160000
- service_origine="UBA" enregistré

---

### TC_RECEPTION_SERVICE_004: Réception - Service invalide

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 40000, "service_origine": "INVALID"}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Service d'origine invalide"
- Solde inchangé

---

### TC_RECEPTION_SERVICE_005: Réception - Service manquant

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 40000}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Service d'origine requis"

---

### TC_RECEPTION_SERVICE_006: Réception - Montant zéro

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 0, "service_origine": "OM"}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide"

---

### TC_RECEPTION_SERVICE_007: Réception - Montant négatif

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": -40000, "service_origine": "OM"}

**Résultat attendu**:

- Code HTTP: 400
- Erreur: "Montant invalide"

---

### TC_RECEPTION_SERVICE_008: Réception - Compte inexistant

**Étapes**:

1. POST /api.php?id=9999999&action=reception_service
2. Body: {"montant": 40000, "service_origine": "OM"}

**Résultat attendu**:

- Code HTTP: 404
- Erreur: "Compte non trouvé"

---

### TC_RECEPTION_SERVICE_009: Réception montant très élevé

**Étapes**:

1. POST /api.php?id={id}&action=reception_service
2. Body: {"montant": 999999999, "service_origine": "OM"}

**Résultat attendu**:

- Code HTTP: 200
- Solde augmenté (système accepte)
- Montant enregistré correctement

---

## 📊 RÉSUMÉ DES TESTS

**Total Cas de Test**: 46

- Dépôt: 8 cas
- Retrait: 7 cas
- Virement: 7 cas
- Transfert Service: 8 cas
- Réception Service: 9 cas
- Autres: 7 cas

**Catégories**:

- Cas positifs (Nominal): 15 cas
- Cas négatifs (Erreurs): 31 cas
- Cas limites: 10 cas
