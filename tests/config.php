<?php
/**
 * Configuration des Tests API Banque
 * Définit l'environnement et les paramètres de test
 */

return [
    // Configuration serveur
    'server' => [
        'host' => 'localhost',
        'port' => 80,
        'base_url' => 'http://localhost/banque/api.php',
        'timeout' => 10,
    ],

    // Configuration des données de test
    'test_data' => [
        'accounts' => [
            ['titulaire' => 'Test Account 1'],
            ['titulaire' => 'Test Account 2'],
            ['titulaire' => 'Test Account 3'],
        ],
        'deposit_amounts' => [10000, 50000, 100000],
        'withdrawal_amounts' => [1000, 5000, 10000],
        'transfer_services' => ['OM', 'MOMO', 'UBA'],
    ],

    // Configuration des rapports
    'reports' => [
        'output_dir' => __DIR__ . '/../test-reports/',
        'timestamp_format' => 'Y-m-d H:i:s',
        'generate_json' => true,
        'generate_html' => true,
        'generate_junit' => true,
    ],

    // Validations
    'validations' => [
        'min_montant' => 100,
        'max_montant' => 999999999,
        'min_titulaire_length' => 3,
        'max_titulaire_length' => 100,
    ],

    // Tests à exécuter
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
];
?>
