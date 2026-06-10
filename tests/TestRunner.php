<?php
/**
 * Classe TestRunner - Moteur de Tests Automatisés API Banque
 * Gère l'exécution, les assertions et la génération de rapports
 */

class TestRunner {
    private $config;
    private $results = [];
    private $total_tests = 0;
    private $passed_tests = 0;
    private $failed_tests = 0;
    private $start_time;
    private $test_data = [];

    public function __construct($config_path) {
        $this->config = require $config_path;
        $this->start_time = microtime(true);
        $this->createReportDirectory();
    }

    /**
     * Exécute une requête HTTP et retourne la réponse
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->config['server']['base_url'] . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['server']['timeout']);

        if (in_array($method, ['POST', 'PUT']) && $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'http_code' => $http_code,
            'body' => json_decode($response, true),
            'raw' => $response,
            'error' => $error,
        ];
    }

    /**
     * Crée un test avec assertion
     */
    private function test($name, $method, $endpoint, $data = null, $expect_code = 200) {
        $this->total_tests++;
        
        $response = $this->makeRequest($method, $endpoint, $data);
        $passed = ($response['http_code'] == $expect_code);

        if ($passed) {
            $this->passed_tests++;
            $status = 'PASS';
        } else {
            $this->failed_tests++;
            $status = 'FAIL';
        }

        $result = [
            'name' => $name,
            'status' => $status,
            'passed' => $passed,
            'method' => $method,
            'endpoint' => $endpoint,
            'http_code' => $response['http_code'],
            'expected_code' => $expect_code,
            'response' => $response['body'],
            'timestamp' => date($this->config['reports']['timestamp_format']),
        ];

        $this->results[] = $result;
        
        echo "$status | Test $this->total_tests: $name (HTTP {$response['http_code']})\n";
        
        return $response['body'];
    }

    /**
     * Assertion personnalisée
     */
    private function assertTrue($condition, $message) {
        $this->total_tests++;
        if ($condition) {
            $this->passed_tests++;
            echo "PASS | $message\n";
            return true;
        } else {
            $this->failed_tests++;
            echo "FAIL | $message\n";
            $this->results[] = ['name' => $message, 'passed' => false, 'status' => 'FAIL'];
            return false;
        }
    }

    /**
     * Suite de tests: Gestion des comptes
     */
    public function testAccounts() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 1: GESTION DES COMPTES (CRUD)                      ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        // Créer des comptes de test
        foreach ($this->config['test_data']['accounts'] as $account) {
            $response = $this->test(
                "Créer compte: {$account['titulaire']}",
                'POST',
                '',
                $account,
                200
            );
            
            if (isset($response['compte']['id'])) {
                $this->test_data['accounts'][] = $response['compte'];
            }
        }

        // Lister tous les comptes
        $response = $this->test('Lister tous les comptes', 'GET', '', null, 200);
        $this->assertTrue(is_array($response['data'] ?? []), 'Liste des comptes retournée');

        // Récupérer détails d'un compte
        if (!empty($this->test_data['accounts'])) {
            $account_id = $this->test_data['accounts'][0]['id'];
            $response = $this->test(
                "Récupérer détails compte ID: $account_id",
                'GET',
                "?id=$account_id",
                null,
                200
            );
            $this->assertTrue(isset($response['compte']['id']), 'Compte détail retourné');
        }

        // Modifier titulaire
        if (!empty($this->test_data['accounts'])) {
            $account_id = $this->test_data['accounts'][0]['id'];
            $this->test(
                "Modifier titulaire du compte $account_id",
                'PUT',
                "?id=$account_id",
                ['titulaire' => 'Titulaire Modifié'],
                200
            );
        }
    }

    /**
     * Suite de tests: Dépôts
     */
    public function testDeposits() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 2: OPÉRATIONS DE DÉPÔT                            ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        if (empty($this->test_data['accounts'])) {
            echo "⚠️  Aucun compte de test disponible. Créez d'abord des comptes.\n";
            return;
        }

        $account_id = $this->test_data['accounts'][0]['id'];
        $solde_initial = $this->test_data['accounts'][0]['solde'];

        // Dépôts valides
        foreach ($this->config['test_data']['deposit_amounts'] as $amount) {
            $this->test(
                "Dépôt de $amount FCFA sur compte $account_id",
                'POST',
                "?id=$account_id&action=depot",
                ['montant' => $amount, 'description' => "Dépôt test $amount FCFA"],
                200
            );
        }

        // Dépôts invalides
        $this->test('Dépôt avec montant négatif', 'POST', "?id=$account_id&action=depot",
            ['montant' => -5000], 400);
        
        $this->test('Dépôt avec montant zéro', 'POST', "?id=$account_id&action=depot",
            ['montant' => 0], 400);
        
        $this->test('Dépôt avec montant très élevé', 'POST', "?id=$account_id&action=depot",
            ['montant' => 9999999999], 400);
    }

    /**
     * Suite de tests: Retraits
     */
    public function testWithdrawals() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 3: OPÉRATIONS DE RETRAIT                          ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        if (empty($this->test_data['accounts'])) {
            echo "⚠️  Aucun compte de test disponible.\n";
            return;
        }

        $account_id = $this->test_data['accounts'][0]['id'];

        // Retraits valides (si solde suffisant)
        $this->test('Retrait de 1000 FCFA', 'POST', "?id=$account_id&action=retrait",
            ['montant' => 1000, 'description' => 'Retrait test'], 200);

        // Retraits invalides
        $this->test('Retrait montant négatif', 'POST', "?id=$account_id&action=retrait",
            ['montant' => -5000], 400);

        $this->test('Retrait montant zéro', 'POST', "?id=$account_id&action=retrait",
            ['montant' => 0], 400);

        $this->test('Retrait solde insuffisant', 'POST', "?id=$account_id&action=retrait",
            ['montant' => 999999999], 400);
    }

    /**
     * Suite de tests: Virements entre comptes
     */
    public function testTransfers() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 4: VIREMENTS ENTRE COMPTES                        ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        if (count($this->test_data['accounts']) < 2) {
            echo "⚠️  Minimum 2 comptes requis pour les virements.\n";
            return;
        }

        $account1_id = $this->test_data['accounts'][0]['id'];
        $account2_id = $this->test_data['accounts'][1]['id'];

        // Virement valide
        $this->test(
            "Virement de $account1_id vers $account2_id (5000 FCFA)",
            'POST',
            "?id=$account1_id&action=virement",
            ['montant' => 5000, 'compte_destinataire' => $account2_id],
            200
        );

        // Virement vers compte inexistant
        $this->test('Virement vers compte inexistant', 'POST', "?id=$account1_id&action=virement",
            ['montant' => 5000, 'compte_destinataire' => 999999999], 400);

        // Virement montant invalide
        $this->test('Virement montant négatif', 'POST', "?id=$account1_id&action=virement",
            ['montant' => -5000, 'compte_destinataire' => $account2_id], 400);
    }

    /**
     * Suite de tests: Transferts service
     */
    public function testServiceTransfers() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 5: TRANSFERTS VERS SERVICES (OM, MOMO, UBA)       ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        if (empty($this->test_data['accounts'])) {
            echo "⚠️  Aucun compte de test disponible.\n";
            return;
        }

        $account_id = $this->test_data['accounts'][0]['id'];

        // Transferts vers chaque service
        foreach ($this->config['test_data']['transfer_services'] as $service) {
            $this->test(
                "Transfert vers $service (5000 FCFA)",
                'POST',
                "?id=$account_id&action=transfert_service",
                [
                    'montant' => 5000,
                    'service_destination' => $service,
                    'numero_beneficiaire' => '06123456789'
                ],
                200
            );
        }

        // Service invalide
        $this->test('Transfert vers service invalide', 'POST', "?id=$account_id&action=transfert_service",
            ['montant' => 5000, 'service_destination' => 'INVALID'], 400);

        // Montant invalide
        $this->test('Transfert service montant zéro', 'POST', "?id=$account_id&action=transfert_service",
            ['montant' => 0, 'service_destination' => 'OM'], 400);
    }

    /**
     * Suite de tests: Historique des transactions
     */
    public function testTransactions() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 6: HISTORIQUE DES TRANSACTIONS                   ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        // Lister toutes les transactions
        $response = $this->test('Lister toutes les transactions', 'GET', '?action=transactions', null, 200);
        $this->assertTrue(is_array($response['data'] ?? []), 'Tableau de transactions retourné');

        // Transactions par service
        foreach ($this->config['test_data']['transfer_services'] as $service) {
            $response = $this->test(
                "Transactions du service $service",
                'GET',
                "?action=transactions_service&service=$service",
                null,
                200
            );
            $this->assertTrue(is_array($response['data'] ?? []), "Transactions $service retournées");
        }

        // Liste des services disponibles
        $response = $this->test('Récupérer liste des services', 'GET', '?action=services', null, 200);
        $this->assertTrue(is_array($response['services'] ?? []), 'Liste des services retournée');
    }

    /**
     * Suite de tests: Validations et erreurs
     */
    public function testValidations() {
        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║  SUITE 7: VALIDATIONS ET GESTION DES ERREURS             ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        // Compte inexistant
        $this->test('Compte inexistant', 'GET', '?id=999999999', null, 404);

        // Titulaire trop court
        if (!empty($this->test_data['accounts'])) {
            $this->test('Créer avec titulaire trop court', 'POST', '',
                ['titulaire' => 'AB'], 400);
        }

        // Titulaire trop long
        $long_name = str_repeat('A', 200);
        $this->test('Créer avec titulaire trop long', 'POST', '',
            ['titulaire' => $long_name], 400);

        // Paramètres invalides
        $this->test('Dépôt sans montant', 'POST', '?id=1&action=depot',
            [], 400);

        $this->test('Requête avec JSON invalide', 'POST', '?id=1&action=depot',
            null, 400);
    }

    /**
     * Exécute toutes les suites de tests
     */
    public function runAll() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║     SUITE DE TESTS AUTOMATISÉS API BANQUE                 ║\n";
        echo "║     Démarrage: " . date($this->config['reports']['timestamp_format']) . "         ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";

        if ($this->config['test_suites']['accounts']) $this->testAccounts();
        if ($this->config['test_suites']['deposits']) $this->testDeposits();
        if ($this->config['test_suites']['withdrawals']) $this->testWithdrawals();
        if ($this->config['test_suites']['transfers']) $this->testTransfers();
        if ($this->config['test_suites']['service_transfers']) $this->testServiceTransfers();
        if ($this->config['test_suites']['transactions']) $this->testTransactions();
        if ($this->config['test_suites']['validations']) $this->testValidations();

        $this->generateReport();
    }

    /**
     * Génère les rapports
     */
    private function generateReport() {
        $duration = microtime(true) - $this->start_time;
        $pass_rate = ($this->total_tests > 0) ? round(($this->passed_tests / $this->total_tests) * 100, 1) : 0;

        echo "\n╔════════════════════════════════════════════════════════════╗\n";
        echo "║                    RÉSUMÉ DES TESTS                       ║\n";
        echo "╠════════════════════════════════════════════════════════════╣\n";
        printf("║ Total des tests:  %d\n", $this->total_tests);
        printf("║ Tests réussis:    %d\n", $this->passed_tests);
        printf("║ Tests échoués:    %d\n", $this->failed_tests);
        printf("║ Taux de réussite: %.1f%%\n", $pass_rate);
        printf("║ Durée:            %.2fs\n", $duration);
        echo "╚════════════════════════════════════════════════════════════╝\n";

        // Générer JSON
        if ($this->config['reports']['generate_json']) {
            $this->generateJSONReport($duration, $pass_rate);
        }

        // Générer HTML
        if ($this->config['reports']['generate_html']) {
            $this->generateHTMLReport($duration, $pass_rate);
        }

        // Générer JUnit
        if ($this->config['reports']['generate_junit']) {
            $this->generateJUnitReport($duration);
        }

        echo "\nRapports generates dans: " . $this->config['reports']['output_dir'] . "\n";
    }

    /**
     * Génère un rapport JSON
     */
    private function generateJSONReport($duration, $pass_rate) {
        $report = [
            'timestamp' => date($this->config['reports']['timestamp_format']),
            'duration_seconds' => round($duration, 2),
            'statistics' => [
                'total_tests' => $this->total_tests,
                'passed' => $this->passed_tests,
                'failed' => $this->failed_tests,
                'pass_rate' => $pass_rate,
            ],
            'results' => $this->results,
        ];

        $filename = $this->config['reports']['output_dir'] . 'report-' . date('Y-m-d-His') . '.json';
        file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Rapport JSON: " . basename($filename) . "\n";
    }

    /**
     * Génère un rapport HTML
     */
    private function generateHTMLReport($duration, $pass_rate) {
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Tests - Banque API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        .timestamp { color: #7f8c8d; font-size: 14px; margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; }
        .stat h3 { font-size: 12px; opacity: 0.9; }
        .stat .value { font-size: 32px; font-weight: bold; margin-top: 10px; }
        .passed { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .failed { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .rate { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #ecf0f1; }
        tr:hover { background: #f8f9fa; }
        .pass { color: #27ae60; font-weight: bold; }
        .fail { color: #e74c3c; font-weight: bold; }
        .endpoint { color: #3498db; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Rapport de Tests - Banque API</h1>
        <p class="timestamp">Généré le ' . date($this->config['reports']['timestamp_format']) . '</p>
        
        <div class="summary">
            <div class="stat">
                <h3>Total Tests</h3>
                <div class="value">' . $this->total_tests . '</div>
            </div>
            <div class="stat passed">
                <h3>Tests Réussis</h3>
                <div class="value">' . $this->passed_tests . '</div>
            </div>
            <div class="stat failed">
                <h3>Tests Échoués</h3>
                <div class="value">' . $this->failed_tests . '</div>
            </div>
            <div class="stat rate">
                <h3>Taux de Réussite</h3>
                <div class="value">' . $pass_rate . '%</div>
            </div>
        </div>

        <h2 style="margin-top: 30px; margin-bottom: 15px;">Détails des Tests</h2>
        <table>
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Méthode</th>
                    <th>Endpoint</th>
                    <th>HTTP Code</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($this->results as $result) {
            $status_class = $result['passed'] ? 'pass' : 'fail';
            $html .= '<tr>
                <td>' . htmlspecialchars($result['name']) . '</td>
                <td>' . $result['method'] . '</td>
                <td class="endpoint">' . htmlspecialchars($result['endpoint']) . '</td>
                <td>' . $result['http_code'] . '</td>
                <td class="' . $status_class . '">' . $result['status'] . '</td>
            </tr>';
        }

        $html .= '</tbody>
        </table>
    </div>
</body>
</html>';

        $filename = $this->config['reports']['output_dir'] . 'report-' . date('Y-m-d-His') . '.html';
        file_put_contents($filename, $html);
        echo "Rapport HTML: " . basename($filename) . "\n";
    }

    /**
     * Génère un rapport JUnit XML
     */
    private function generateJUnitReport($duration) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<testsuites name="Banque API Tests" tests="' . $this->total_tests . '" failures="' . $this->failed_tests . '" time="' . round($duration, 2) . '">' . "\n";
        
        foreach ($this->results as $result) {
            $xml .= '  <testcase name="' . htmlspecialchars($result['name']) . '" time="0">' . "\n";
            if (!$result['passed']) {
                $xml .= '    <failure>HTTP ' . $result['http_code'] . ' expected ' . $result['expected_code'] . '</failure>' . "\n";
            }
            $xml .= '  </testcase>' . "\n";
        }

        $xml .= '</testsuites>';

        $filename = $this->config['reports']['output_dir'] . 'report-' . date('Y-m-d-His') . '.xml';
        file_put_contents($filename, $xml);
        echo "Rapport JUnit: " . basename($filename) . "\n";
    }

    /**
     * Crée le répertoire des rapports
     */
    private function createReportDirectory() {
        $dir = $this->config['reports']['output_dir'];
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
?>
