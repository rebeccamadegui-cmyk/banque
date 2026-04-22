# API Banque Complète

Une API REST complète pour la gestion de comptes bancaires avec toutes les opérations financières essentielles et les transferts inter-services.

## 🚀 Fonctionnalités

### ✅ Gestion des comptes

- **Créer un compte** bancaire
- **Lister tous les comptes**
- **Consulter un compte** spécifique avec son historique
- **Modifier** les informations d'un compte
- **Supprimer** un compte

### 💰 Opérations financières

- **Dépôt** d'argent sur un compte (avec support services)
- **Retrait** d'argent (avec vérification du solde et support services)
- **Virement** entre comptes (avec validation)

### 🔄 Transferts Inter-Services (OM, MOMO, UBA)

- **Envoyer de l'argent** vers Orange Money (OM)
- **Envoyer de l'argent** vers MTN Mobile Money (MOMO)
- **Envoyer de l'argent** vers UBA Bank
- **Recevoir de l'argent** depuis ces services
- **Suivi des transactions** par service

### 📊 Historique et transactions

- **Historique complet** des transactions par compte
- **Liste de toutes les transactions** du système
- **Transactions filtrées par service** (OM, MOMO, UBA)
- **Traçabilité** de toutes les opérations avec numéros de référence

## 📋 Endpoints API

### Comptes

| Méthode  | Endpoint           | Description                    |
| -------- | ------------------ | ------------------------------ |
| `GET`    | `/api.php`         | Lister tous les comptes        |
| `POST`   | `/api.php`         | Créer un nouveau compte        |
| `GET`    | `/api.php?id={id}` | Consulter un compte spécifique |
| `PUT`    | `/api.php?id={id}` | Modifier un compte             |
| `DELETE` | `/api.php?id={id}` | Supprimer un compte            |

### Opérations financières

| Méthode | Endpoint                           | Description           |
| ------- | ---------------------------------- | --------------------- |
| `POST`  | `/api.php?id={id}&action=depot`    | Effectuer un dépôt    |
| `POST`  | `/api.php?id={id}&action=retrait`  | Effectuer un retrait  |
| `POST`  | `/api.php?id={id}&action=virement` | Effectuer un virement |

### Transferts Inter-Services

| Méthode | Endpoint                                    | Description                |
| ------- | ------------------------------------------- | -------------------------- |
| `POST`  | `/api.php?id={id}&action=transfert_service` | Transférer vers un service |
| `POST`  | `/api.php?id={id}&action=reception_service` | Recevoir d'un service      |

### Transactions et Services

| Méthode | Endpoint                                          | Description                     |
| ------- | ------------------------------------------------- | ------------------------------- |
| `GET`   | `/api.php?action=transactions`                    | Lister toutes les transactions  |
| `GET`   | `/api.php?action=transactions_service`            | Lister transactions par service |
| `GET`   | `/api.php?action=services`                        | Lister les services supportés   |
| `GET`   | `/api.php?action=transactions`                    | Lister toutes les transactions  |
| `GET`   | `/api.php?action=transactions_service&service=OM` | Transactions par service        |
| `GET`   | `/api.php?action=services`                        | Lister les services supportés   |

## 📖 Documentation Swagger

- **Interface interactive** : `/swagger-ui/index.html` ou `/app.html`
- **Spécification OpenAPI** : `/swagger.json`
- **Accès rapide** : `/api.php?swagger`

## 🧪 Exemples d'utilisation

### Créer un compte

```bash
POST /api.php
Content-Type: application/json

{
  "titulaire": "Jean Dupont"
}

Réponse:
{
  "message": "Compte cree avec succes",
  "compte": {
    "id": 1775923632,
    "numero": "FR8712BANK",
    "titulaire": "Jean Dupont",
    "solde": 0,
    "service": "BANQUE"
  }
}
```

### Effectuer un dépôt

```bash
POST /api.php?id=1775923632&action=depot
Content-Type: application/json

{
  "montant": 100000,
  "description": "Salaire mensuel",
  "service": "BANQUE"
}

Réponse:
{
  "message": "Dépôt effectué avec succès",
  "compte": {
    "id": 1775923632,
    "numero": "FR8712BANK",
    "titulaire": "Jean Dupont",
    "solde": 100000,
    "service": "BANQUE"
  }
}
```

### Effectuer un retrait

```bash
POST /api.php?id=1775923632&action=retrait
Content-Type: application/json

{
  "montant": 25000,
  "description": "Retrait guichet",
  "service": "BANQUE"
}
```

### Effectuer un virement entre comptes

```bash
POST /api.php?id=1775923632&action=virement
Content-Type: application/json

{
  "montant": 50000,
  "compte_destinataire": 1775923890,
  "description": "Paiement loyer"
}

Réponse:
{
  "message": "Virement effectué avec succès",
  "compte_source": {
    "solde": 50000
  },
  "compte_destinataire": {
    "solde": 50000
  }
}
```

### 🆕 Transférer vers Orange Money (OM)

```bash
POST /api.php?id=1775923632&action=transfert_service
Content-Type: application/json

{
  "montant": 30000,
  "service_destination": "OM",
  "numero_beneficiaire": "+237681234567"
}

Réponse:
{
  "status": "success",
  "message": "Transfert vers OM effectué avec succès",
  "compte": {
    "solde": 20000
  },
  "details": {
    "montant": 30000,
    "service_origine": "BANQUE",
    "service_destination": "OM",
    "numero_beneficiaire": "+237681234567",
    "reference": "REF1704062400123",
    "date": "2024-01-01 12:40:00"
  }
}
```

### 🆕 Transférer vers MTN MOMO

```bash
POST /api.php?id=1775923632&action=transfert_service
Content-Type: application/json

{
  "montant": 20000,
  "service_destination": "MOMO",
  "numero_beneficiaire": "+237670123456"
}
```

### 🆕 Transférer vers UBA Bank

```bash
POST /api.php?id=1775923632&action=transfert_service
Content-Type: application/json

{
  "montant": 50000,
  "service_destination": "UBA"
}
```

### 🆕 Recevoir depuis Orange Money

```bash
POST /api.php?id=1775923632&action=reception_service
Content-Type: application/json

{
  "montant": 75000,
  "service_origine": "OM",
  "numero_emetteur": "+237681234567"
}

Réponse:
{
  "status": "success",
  "message": "Fonds reçus de OM avec succès",
  "compte": {
    "solde": 95000
  },
  "details": {
    "montant": 75000,
    "service_origine": "OM",
    "numero_emetteur": "+237681234567",
    "reference": "REF1704062400456",
    "date": "2024-01-01 12:45:00"
  }
}
```

### 🆕 Voir les transactions par service

```bash
GET /api.php?action=transactions_service&service=OM

Réponse:
{
  "status": "success",
  "service": "OM",
  "data": [
    {
      "id": "1704062400789",
      "compte_id": 1775923632,
      "type": "transfert_service_sortant",
      "montant": -30000,
      "description": "Transfert vers OM - +237681234567",
      "service": "BANQUE",
      "service_destination": "OM",
      "date": "2024-01-01 12:40:00"
    }
  ]
}
```

### Consulter un compte avec historique

```bash
GET /api.php?id=1775923632

Réponse:
{
  "status": "success",
  "compte": {
    "id": 1775923632,
    "numero": "FR8712BANK",
    "titulaire": "Jean Dupont",
    "solde": 95000,
    "service": "BANQUE"
  },
  "transactions": [...]
}
```

### 🆕 Lister les services disponibles

```bash
GET /api.php?action=services

Réponse:
{
  "status": "success",
  "services": ["OM", "MOMO", "UBA"],
  "description": "Services de paiement supportés"
}
```

## 🏗️ Architecture

- **Backend** : PHP pur (sans framework)
- **Stockage** : Fichiers JSON (`comptes.json`, `transactions.json`)
- **API** : RESTful avec méthodes HTTP appropriées
- **Documentation** : Swagger/OpenAPI 3.0
- **Déploiement** : Docker + Render

## 🔒 Sécurité

- Validation des données d'entrée
- Vérification des soldes avant retraits/virements
- Gestion d'erreurs appropriée
- Codes HTTP standards

## 🚀 Déploiement

L'API est déployée sur **Render** avec Docker :

- **URL de base** : `https://banque-api-xxxx.onrender.com`
- **Documentation** : `https://banque-api-xxxx.onrender.com/swagger-ui/index.html`

## 🛠️ Technologies

- **PHP 8.2**
- **Docker**
- **Swagger UI**
- **Render** (hébergement)

---

_API développée pour démontrer un système bancaire complet avec toutes les fonctionnalités essentielles._
