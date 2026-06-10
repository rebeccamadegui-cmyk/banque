#!/bin/bash
# Script de pré-commit pour valider l'API
# Exécute les tests avant chaque commit

echo "🧪 Exécution des tests pré-commit..."

# Vérifier si le serveur PHP est actif
if ! curl -s http://localhost/banque/api.php > /dev/null; then
    echo "❌ Erreur: Le serveur PHP n'est pas accessible sur http://localhost"
    echo "   Démarrez WAMP/LAMP ou vérifiez la configuration"
    exit 1
fi

# Exécuter les tests
php /c/wamp64/www/banque/tests/run-tests.php

# Vérifier le résultat
if [ $? -eq 0 ]; then
    echo "✅ Tests réussis - Commit autorisé"
    exit 0
else
    echo "❌ Tests échoués - Commit rejeté"
    exit 1
fi
