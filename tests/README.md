# Suite de Tests Automatisés - Banque API

Documentation complète du système de tests automatisés pour l'API Banque.

## Vue d'ensemble

Le système de tests automatisés offre:
- Tests complets de tous les endpoints API
- Validation des données entrantes et sortantes
- Gestion des erreurs et codes HTTP
- Rapports HTML, JSON et JUnit XML
- Intégration CI/CD

## Démarrage rapide

### Prérequis
- PHP 7.0+
- Serveur web (WAMP, LAMP, Nginx)
- cURL activé en PHP
- API Banque accessible sur `http://localhost/banque/api.php`

### Exécuter les tests

```bash
cd c:\wamp64\www\banque\tests
php run-tests.php
```

## Structure des fichiers

```
tests/
├── config.php           # Configuration des tests
├── TestRunner.php       # Classe principale de tests
├── run-tests.php        # Script d'exécution
├── .git-hooks-pre-commit.sh  # Hook Git (pré-commit)
└── README.md            # Cette documentation
```

## Configuration

Modifier `config.php` pour personnaliser:

```php
'server' => [
    'host' => 'localhost',
    'port' => 80,
    'base_url' => 'http://localhost/banque/api.php',
    'timeout' => 10,
],

'test_data' => [
    'accounts' => [...],           // Données de comptes de test
    'deposit_amounts' => [...],    // Montants de dépôt
    'withdrawal_amounts' => [...], // Montants de retrait
    'transfer_services' => [...],  // Services (OM, MOMO, UBA)
],

'test_suites' => [
    'accounts' => true,
    'deposits' => true,
    'withdrawals' => true,
    'transfers' => true,
    'service_transfers' => true,
    'transactions' => true,
    'validations' => true,
    'error_handling' => true,
],
```

## Suites de Tests

### Tests des Comptes (CRUD)
- Création de comptes
- Listing des comptes
- Récupération de détails
- Modification du titulaire
- Suppression de comptes

### Tests des Dépôts
- Dépôts valides (montants positifs)
- Validation montants négatifs
- Validation montants zéro
- Validation montants excessifs

### Tests des Retraits
- Retraits valides
- Détection solde insuffisant
- Validation des montants invalides

### Tests des Virements
- Virements inter-comptes
- Détection compte inexistant
- Validation montants

### Tests des Transferts Service
- Transfert OM
- Transfert MOMO
- Transfert UBA
- Validation service inexistant

### Tests de l'Historique
- Listing des transactions
- Filtrage par service
- Liste des services disponibles

### Tests de Validation
- Comptes inexistants
- Titulaires invalides
- Paramètres malformés

## Rapports

Les tests génèrent automatiquement:

### Rapport JSON
**Format:** `test-reports/report-YYYY-MM-DD-HHmmss.json`

```json
{
  "timestamp": "2026-06-10 14:30:45",
  "duration_seconds": 2.45,
  "statistics": {
    "total_tests": 50,
    "passed": 48,
    "failed": 2,
    "pass_rate": 96.0
  },
  "results": [...]
}
```

### Rapport HTML
**Format:** `test-reports/report-YYYY-MM-DD-HHmmss.html`

- Dashboard avec statistiques
- Tableau détaillé des résultats
- Codes couleur (réussi / échoué)
- Responsive et facile à consulter

### Rapport JUnit XML
**Format:** `test-reports/report-YYYY-MM-DD-HHmmss.xml`

Compatible avec:
- Jenkins CI/CD
- GitHub Actions
- GitLab CI
- Azure Pipelines

## Intégration Git

### Configuration du hook pré-commit

```bash
# Windows (PowerShell)
cp tests/.git-hooks-pre-commit.sh .git/hooks/pre-commit

# Linux/Mac
chmod +x tests/.git-hooks-pre-commit.sh
cp tests/.git-hooks-pre-commit.sh .git/hooks/pre-commit
```

Les tests s'exécutent automatiquement avant chaque commit.

## Exemple de sortie

```
SUITE DE TESTS AUTOMATISÉS API BANQUE
Démarrage: 2026-06-10 14:30:00

SUITE 1: GESTION DES COMPTES (CRUD)

PASS | Test 1: Créer compte: Test Account 1 (HTTP 200)
PASS | Test 2: Créer compte: Test Account 2 (HTTP 200)
PASS | Test 3: Créer compte: Test Account 3 (HTTP 200)
PASS | Test 4: Lister tous les comptes (HTTP 200)
PASS | Test 5: Récupérer détails compte (HTTP 200)

RÉSUMÉ DES TESTS
Total des tests:  50
Tests réussis:    48
Tests échoués:    2
Taux de réussite: 96.0%
Durée:            2.45s

Rapport JSON: report-2026-06-10-143000.json
Rapport HTML: report-2026-06-10-143000.html
Rapport JUnit: report-2026-06-10-143000.xml

Rapports générés dans: c:\wamp64\www\banque\test-reports\
```

## Dépannage

### Le serveur PHP n'est pas accessible
```
Erreur: Le serveur PHP n'est pas accessible sur http://localhost
```
Solution: Démarrez WAMP/LAMP et vérifiez que l'API Banque est accessible.

### Erreurs de connexion aux tests
```
cURL error 28: Operation timed out
```
Solution: Augmentez le timeout dans `config.php`:
```php
'server' => [
    'timeout' => 30, // Augmenter à 30 secondes
],
```

### Pas de base de données
```
Erreur: comptes.json introuvable
```
Solution: Assurez-vous que les fichiers JSON existent:
- `c:\wamp64\www\banque\comptes.json`
- `c:\wamp64\www\banque\transactions.json`

## API Endpoints testés

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/` | `GET` | Lister tous les comptes |
| `/` | `POST` | Créer un compte |
| `?id=X` | `GET` | Récupérer détails d'un compte |
| `?id=X` | `PUT` | Modifier un compte |
| `?id=X` | `DELETE` | Supprimer un compte |
| `?id=X&action=depot` | `POST` | Effectuer un dépôt |
| `?id=X&action=retrait` | `POST` | Effectuer un retrait |
| `?id=X&action=virement` | `POST` | Virement inter-comptes |
| `?id=X&action=transfert_service` | `POST` | Transfert service |
| `?action=transactions` | `GET` | Historique des transactions |
| `?action=transactions_service&service=X` | `GET` | Transactions par service |
| `?action=services` | `GET` | Liste des services |

## Bonnes pratiques

1. **Exécutez les tests régulièrement** - Avant chaque commit
2. **Consultez les rapports** - Vérifiez les résultats détaillés
3. **Corrigez les erreurs** - Adressez les défaillances immédiatement
4. **Mettez à jour la config** - Adaptez les données de test si nécessaire
5. **Archivez les rapports** - Gardez l'historique des résultats

## Support

Pour des questions ou problèmes:
1. Vérifiez que l'API est opérationnelle
2. Consultez les rapports JSON/HTML
3. Testez manuellement les endpoints avec cURL
4. Vérifiez les logs PHP/serveur

## Licence

Partie du projet Banque API - Tous droits réservés.
