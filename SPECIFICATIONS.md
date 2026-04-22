# Spécifications Fonctionnelles et Non-Fonctionnelles

## 📋 Spécifications Fonctionnelles

### SF1: Dépôt (Deposit)

**Description**: Ajouter de l'argent à un compte bancaire

**Acteurs**: Client, Système Bancaire

**Préconditions**:

- Le compte doit exister
- Le montant doit être positif (> 0)
- Le compte doit avoir un solde existant

**Scénario Nominal**:

1. Le client sélectionne son compte
2. Saisit le montant du dépôt
3. Confirme l'opération
4. Le système valide le montant
5. Le solde du compte est augmenté
6. Une transaction est enregistrée

**Postconditions**:

- Le solde est augmenté du montant
- Une transaction est enregistrée en base
- Un reçu est généré avec la référence

**Cas d'erreur**:

- Compte inexistant → Erreur 404
- Montant invalide (≤ 0) → Erreur 400
- Montant non numérique → Erreur 400

---

### SF2: Retrait (Withdrawal)

**Description**: Retirer de l'argent d'un compte bancaire

**Acteurs**: Client, Système Bancaire

**Préconditions**:

- Le compte doit exister
- Le montant doit être positif (> 0)
- Le solde doit être ≥ montant demandé

**Scénario Nominal**:

1. Le client sélectionne son compte
2. Saisit le montant du retrait
3. Confirme l'opération
4. Le système valide le montant
5. Le système vérifie le solde
6. Le solde du compte est diminué
7. Une transaction est enregistrée

**Postconditions**:

- Le solde est diminué du montant
- Une transaction est enregistrée
- Un reçu est généré

**Cas d'erreur**:

- Compte inexistant → Erreur 404
- Montant invalide (≤ 0) → Erreur 400
- Solde insuffisant → Erreur 400
- Montant non numérique → Erreur 400

---

### SF3: Virement entre Comptes (Transfer)

**Description**: Transférer de l'argent d'un compte à un autre compte

**Acteurs**: Client, Système Bancaire

**Préconditions**:

- Les deux comptes doivent exister
- Le montant doit être positif (> 0)
- Le solde du compte source ≥ montant
- Les comptes doivent être différents

**Scénario Nominal**:

1. Le client sélectionne son compte source
2. Sélectionne le compte destinataire
3. Saisit le montant
4. Confirme l'opération
5. Le système valide les comptes et montant
6. Le système vérifie le solde source
7. Le solde source est diminué
8. Le solde destinataire est augmenté
9. Deux transactions sont enregistrées

**Postconditions**:

- Solde source diminué du montant
- Solde destinataire augmenté du montant
- Deux transactions enregistrées (sortante + entrante)
- Récépissé généré

**Cas d'erreur**:

- Compte source inexistant → Erreur 404
- Compte destinataire inexistant → Erreur 404
- Montant invalide → Erreur 400
- Solde insuffisant → Erreur 400
- Virement vers le même compte → Erreur 400

---

### SF4: Transfert vers Service (Send to Service)

**Description**: Transférer de l'argent depuis un compte vers un service (OM, MOMO, UBA)

**Acteurs**: Client, Système Bancaire, Services (OM/MOMO/UBA)

**Préconditions**:

- Le compte doit exister
- Le service doit être valide (OM, MOMO, ou UBA)
- Le montant doit être positif (> 0)
- Le solde ≥ montant

**Scénario Nominal**:

1. Le client sélectionne son compte
2. Choisit le service destinataire (OM, MOMO, UBA)
3. Saisit le montant
4. Saisit le numéro du bénéficiaire (optionnel)
5. Confirme l'opération
6. Le système valide le service
7. Le système vérifie le solde
8. Le solde est diminué
9. Une référence unique est générée
10. La transaction est enregistrée avec les détails du service

**Postconditions**:

- Le solde est diminué du montant
- Une transaction avec service_destination est enregistrée
- Une référence unique (REF + timestamp) est générée
- Date/heure de l'opération est enregistrée

**Cas d'erreur**:

- Compte inexistant → Erreur 404
- Service invalide → Erreur 400
- Montant invalide → Erreur 400
- Solde insuffisant → Erreur 400
- Numéro bénéficiaire invalide → Avertissement (optionnel)

---

### SF5: Réception depuis Service (Receive from Service)

**Description**: Recevoir de l'argent sur un compte depuis un service (OM, MOMO, UBA)

**Acteurs**: Client, Système Bancaire, Services (OM/MOMO/UBA)

**Préconditions**:

- Le compte doit exister
- Le service doit être valide (OM, MOMO, ou UBA)
- Le montant doit être positif (> 0)

**Scénario Nominal**:

1. Le système reçoit une notification de service
2. Sélectionne le compte récepteur
3. Valide le service source
4. Valide le montant
5. Le solde est augmenté
6. Une référence unique est générée
7. La transaction est enregistrée avec service_origine

**Postconditions**:

- Le solde est augmenté du montant
- Une transaction avec service_origine est enregistrée
- Une référence unique est générée
- Date/heure de l'opération est enregistrée

**Cas d'erreur**:

- Compte inexistant → Erreur 404
- Service invalide → Erreur 400
- Montant invalide → Erreur 400

---

## 📊 Spécifications Non-Fonctionnelles

### SNF1: Performance

- Temps de réponse < 500ms pour toute opération
- Capacité: 1000 transactions/seconde
- Latence moyenne acceptable: 200ms

### SNF2: Disponibilité

- Disponibilité requise: 99.5%
- RTO (Recovery Time Objective): 1 heure
- RPO (Recovery Point Objective): 5 minutes

### SNF3: Sécurité

- HTTPS obligatoire en production
- Validation de tous les paramètres d'entrée
- Pas de stockage en clair des données sensibles
- CORS correctement configuré

### SNF4: Intégrité des Données

- Cohérence transactionnelle garantie
- Pas de débit double
- Historique immuable des transactions
- Sauvegardes régulières

### SNF5: Scalabilité

- Architecture sans état (stateless)
- Capable de gérer la croissance 10x
- Stockage distribué envisageable

### SNF6: Maintenabilité

- Code bien documenté
- API RESTful standard
- Documentation Swagger/OpenAPI
- Logs structurés

### SNF7: Fiabilité

- Transactions atomiques
- Validation cohérente des montants
- Gestion d'erreurs robuste
- Codes HTTP appropriés
