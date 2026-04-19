# API Banque Complète

Une API REST complète pour la gestion de comptes bancaires avec toutes les opérations financières essentielles.

## 🚀 Fonctionnalités

### ✅ Gestion des comptes
- **Créer un compte** bancaire
- **Lister tous les comptes**
- **Consulter un compte** spécifique avec son historique
- **Modifier** les informations d'un compte
- **Supprimer** un compte

### 💰 Opérations financières
- **Dépôt** d'argent sur un compte
- **Retrait** d'argent (avec vérification du solde)
- **Virement** entre comptes (avec validation)

### 📊 Historique et transactions
- **Historique complet** des transactions par compte
- **Liste de toutes les transactions** du système
- **Traçabilité** de toutes les opérations

## 📋 Endpoints API

### Comptes

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/api.php` | Lister tous les comptes |
| `POST` | `/api.php` | Créer un nouveau compte |
| `GET` | `/api.php?id={id}` | Consulter un compte spécifique |
| `PUT` | `/api.php?id={id}` | Modifier un compte |
| `DELETE` | `/api.php?id={id}` | Supprimer un compte |

### Opérations financières

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api.php?id={id}&action=depot` | Effectuer un dépôt |
| `POST` | `/api.php?id={id}&action=retrait` | Effectuer un retrait |
| `POST` | `/api.php?id={id}&action=virement` | Effectuer un virement |

### Transactions

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/api.php?action=transactions` | Lister toutes les transactions |

## 📖 Documentation Swagger

- **Interface interactive** : `/swagger-ui/index.html`
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
```

### Effectuer un dépôt
```bash
POST /api.php?id=1775923632&action=depot
Content-Type: application/json

{
  "montant": 1000.50,
  "description": "Salaire"
}
```

### Effectuer un virement
```bash
POST /api.php?id=1775923632&action=virement
Content-Type: application/json

{
  "montant": 200.00,
  "compte_destinataire": 1775923633,
  "description": "Paiement loyer"
}
```

### Consulter un compte avec historique
```bash
GET /api.php?id=1775923632
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

*API développée pour démontrer un système bancaire complet avec toutes les fonctionnalités essentielles.*