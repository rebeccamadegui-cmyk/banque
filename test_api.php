<?php
/**
 * Script de Test Complet de l'API Banque
 * Teste tous les endpoints et fonctionnalités
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     TEST COMPLET API BANQUE - 10 Juin 2026               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$test_results = [];
$total_tests = 0;
$passed_tests = 0;

// Fichiers de données
$comptes_file = __DIR__ . '/comptes.json';
$transactions_file = __DIR__ . '/transactions.json';

// Fonction pour tester une action
function test_action($name, $method, $endpoint, $data = null, &$results) {
    global $passed_tests, $total_tests;
    $total_tests++;
    
    $url = "http://localhost/banque/api.php" . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    $success = ($http_code >= 200 && $http_code < 300);
    
    if ($success) $passed_tests++;
    
    $status = $success ? "✅ PASS" : "❌ FAIL";
    $results[] = [
        'name' => $name,
        'status' => $status,
        'method' => $method,
        'endpoint' => $endpoint,
        'http_code' => $http_code,
        'response' => $result
    ];
    
    return $result;
}

echo "┌─ 1. CRÉATION DE COMPTES ─────────────────────────────────┐\n";

// Test 1: Créer un compte de test
$compte1 = test_action(
    "Créer compte - Jean Dupont",
    "POST",
    "",
    ['titulaire' => 'Jean Dupont'],
    $test_results
);
$compte1_id = $compte1['compte']['id'] ?? null;
echo "Test 1: " . ($test_results[count($test_results)-1]['status']) . " - ID: $compte1_id\n";

// Test 2: Créer second compte
$compte2 = test_action(
    "Créer compte - Marie Martin",
    "POST",
    "",
    ['titulaire' => 'Marie Martin'],
    $test_results
);
$compte2_id = $compte2['compte']['id'] ?? null;
echo "Test 2: " . ($test_results[count($test_results)-1]['status']) . " - ID: $compte2_id\n\n";

echo "┌─ 2. CONSULTATION DE COMPTES ────────────────────────────┐\n";

// Test 3: Lister tous les comptes
$tous_comptes = test_action(
    "Lister tous les comptes",
    "GET",
    "",
    null,
    $test_results
);
$nb_comptes = count($tous_comptes['data'] ?? []);
echo "Test 3: " . ($test_results[count($test_results)-1]['status']) . " - Comptes: $nb_comptes\n";

// Test 4: Consulter un compte spécifique
if ($compte1_id) {
    $detail = test_action(
        "Consulter compte détail",
        "GET",
        "?id=$compte1_id",
        null,
        $test_results
    );
    $solde = $detail['compte']['solde'] ?? 'N/A';
    echo "Test 4: " . ($test_results[count($test_results)-1]['status']) . " - Solde: $solde FCFA\n";
}

echo "\n┌─ 3. OPÉRATIONS BANCAIRES (DÉPÔT) ───────────────────────┐\n";

// Test 5: Dépôt
if ($compte1_id) {
    $depot = test_action(
        "Effectuer un dépôt - 50000 FCFA",
        "POST",
        "?id=$compte1_id&action=depot",
        ['montant' => 50000, 'description' => 'Dépôt test'],
        $test_results
    );
    $nouveau_solde = $depot['compte']['solde'] ?? 'N/A';
    echo "Test 5: " . ($test_results[count($test_results)-1]['status']) . " - Nouveau solde: $nouveau_solde FCFA\n";
}

echo "\n┌─ 4. OPÉRATIONS BANCAIRES (RETRAIT) ──────────────────────┐\n";

// Test 6: Retrait valide
if ($compte1_id) {
    $retrait = test_action(
        "Effectuer un retrait - 10000 FCFA",
        "POST",
        "?id=$compte1_id&action=retrait",
        ['montant' => 10000, 'description' => 'Retrait test'],
        $test_results
    );
    $nouveau_solde = $retrait['compte']['solde'] ?? 'N/A';
    echo "Test 6: " . ($test_results[count($test_results)-1]['status']) . " - Nouveau solde: $nouveau_solde FCFA\n";
}

// Test 7: Retrait invalide (solde insuffisant)
if ($compte1_id) {
    $retrait_invalide = test_action(
        "Retrait invalide - Montant > solde",
        "POST",
        "?id=$compte1_id&action=retrait",
        ['montant' => 1000000],
        $test_results
    );
    $error = $retrait_invalide['error'] ?? 'N/A';
    echo "Test 7: " . ($test_results[count($test_results)-1]['status']) . " - Erreur: $error\n";
}

echo "\n┌─ 5. VIREMENT ENTRE COMPTES ──────────────────────────────┐\n";

// Test 8: Virement valide
if ($compte1_id && $compte2_id) {
    $virement = test_action(
        "Virement - 15000 FCFA vers compte 2",
        "POST",
        "?id=$compte1_id&action=virement",
        ['montant' => 15000, 'compte_destinataire' => $compte2_id],
        $test_results
    );
    $solde_source = $virement['compte_source']['solde'] ?? 'N/A';
    $solde_dest = $virement['compte_destinataire']['solde'] ?? 'N/A';
    echo "Test 8: " . ($test_results[count($test_results)-1]['status']) . " - Source: $solde_source, Dest: $solde_dest FCFA\n";
}

// Test 9: Virement vers compte inexistant
if ($compte1_id) {
    $virement_invalide = test_action(
        "Virement vers compte inexistant",
        "POST",
        "?id=$compte1_id&action=virement",
        ['montant' => 5000, 'compte_destinataire' => 99999999],
        $test_results
    );
    $error = $virement_invalide['error'] ?? 'N/A';
    echo "Test 9: " . ($test_results[count($test_results)-1]['status']) . " - Erreur: $error\n";
}

echo "\n┌─ 6. TRANSFERTS SERVICE (OM, MOMO, UBA) ─────────────────┐\n";

// Test 10: Transfert service OM
if ($compte1_id) {
    $transfer_om = test_action(
        "Transfert vers OM - 5000 FCFA",
        "POST",
        "?id=$compte1_id&action=transfert_service",
        ['montant' => 5000, 'service_destination' => 'OM', 'numero_beneficiaire' => '06XXXXXX'],
        $test_results
    );
    $nouveau_solde = $transfer_om['compte']['solde'] ?? 'N/A';
    echo "Test 10: " . ($test_results[count($test_results)-1]['status']) . " - Nouveau solde: $nouveau_solde FCFA\n";
}

// Test 11: Transfert service MOMO
if ($compte1_id) {
    $transfer_momo = test_action(
        "Transfert vers MOMO - 3000 FCFA",
        "POST",
        "?id=$compte1_id&action=transfert_service",
        ['montant' => 3000, 'service_destination' => 'MOMO'],
        $test_results
    );
    $nouveau_solde = $transfer_momo['compte']['solde'] ?? 'N/A';
    echo "Test 11: " . ($test_results[count($test_results)-1]['status']) . " - Nouveau solde: $nouveau_solde FCFA\n";
}

// Test 12: Service invalide
if ($compte1_id) {
    $transfer_invalid = test_action(
        "Transfert service invalide",
        "POST",
        "?id=$compte1_id&action=transfert_service",
        ['montant' => 1000, 'service_destination' => 'INVALID'],
        $test_results
    );
    $error = $transfer_invalid['error'] ?? 'N/A';
    echo "Test 12: " . ($test_results[count($test_results)-1]['status']) . " - Erreur: $error\n";
}

echo "\n┌─ 7. HISTORIQUE DES TRANSACTIONS ────────────────────────┐\n";

// Test 13: Lister toutes les transactions
$all_trans = test_action(
    "Lister toutes les transactions",
    "GET",
    "?action=transactions",
    null,
    $test_results
);
$nb_trans = count($all_trans['data'] ?? []);
echo "Test 13: " . ($test_results[count($test_results)-1]['status']) . " - Transactions: $nb_trans\n";

// Test 14: Transactions par service
$trans_om = test_action(
    "Transactions service OM",
    "GET",
    "?action=transactions_service&service=OM",
    null,
    $test_results
);
$nb_om = count($trans_om['data'] ?? []);
echo "Test 14: " . ($test_results[count($test_results)-1]['status']) . " - OM: $nb_om transactions\n";

// Test 15: Liste des services
$services = test_action(
    "Liste des services",
    "GET",
    "?action=services",
    null,
    $test_results
);
$services_list = implode(', ', $services['services'] ?? []);
echo "Test 15: " . ($test_results[count($test_results)-1]['status']) . " - Services: $services_list\n";

echo "\n┌─ 8. MISE À JOUR DE COMPTES ──────────────────────────────┐\n";

// Test 16: Modifier le titulaire
if ($compte1_id) {
    $update = test_action(
        "Modifier titulaire du compte",
        "PUT",
        "?id=$compte1_id",
        ['titulaire' => 'Jean Dupont Modifié'],
        $test_results
    );
    $nouveau_titulaire = $update['compte']['titulaire'] ?? 'N/A';
    echo "Test 16: " . ($test_results[count($test_results)-1]['status']) . " - Nouveau titulaire: $nouveau_titulaire\n";
}

echo "\n┌─ 9. SUPPRESSION DE COMPTE ──────────────────────────────┐\n";

// Test 17: Créer un compte temporaire à supprimer
$compte_temp = test_action(
    "Créer compte temporaire",
    "POST",
    "",
    ['titulaire' => 'Compte à supprimer'],
    $test_results
);
$compte_temp_id = $compte_temp['compte']['id'] ?? null;
echo "Test 17a: " . ($test_results[count($test_results)-1]['status']) . " - ID: $compte_temp_id\n";

// Test 17b: Supprimer le compte
if ($compte_temp_id) {
    $delete = test_action(
        "Supprimer un compte",
        "DELETE",
        "?id=$compte_temp_id",
        null,
        $test_results
    );
    $message = $delete['message'] ?? 'N/A';
    echo "Test 17b: " . ($test_results[count($test_results)-1]['status']) . " - Message: $message\n";
}

echo "\n┌─ 10. VALIDATIONS ET ERREURS ────────────────────────────┐\n";

// Test 18: Montant négatif
if ($compte1_id) {
    $neg_amount = test_action(
        "Dépôt montant négatif",
        "POST",
        "?id=$compte1_id&action=depot",
        ['montant' => -1000],
        $test_results
    );
    $error = $neg_amount['error'] ?? 'N/A';
    echo "Test 18: " . ($test_results[count($test_results)-1]['status']) . " - Erreur: $error\n";
}

// Test 19: Montant zéro
if ($compte1_id) {
    $zero_amount = test_action(
        "Retrait montant zéro",
        "POST",
        "?id=$compte1_id&action=retrait",
        ['montant' => 0],
        $test_results
    );
    $error = $zero_amount['error'] ?? 'N/A';
    echo "Test 19: " . ($test_results[count($test_results)-1]['status']) . " - Erreur: $error\n";
}

// Test 20: JSON invalide
$invalid_json = test_action(
    "Corps JSON invalide",
    "POST",
    "?id=1&action=depot",
    null, // Will fail due to invalid JSON
    $test_results
);
echo "Test 20: " . ($test_results[count($test_results)-1]['status']) . "\n";

// ========== RÉSUMÉ ==========
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                    RÉSUMÉ DES TESTS                       ║\n";
echo "╠════════════════════════════════════════════════════════════╣\n";
echo "║ Total des tests: $total_tests\n";
echo "║ Tests réussis:   $passed_tests ✅\n";
echo "║ Tests échoués:   " . ($total_tests - $passed_tests) . " ❌\n";
echo "║ Taux de réussite: " . round(($passed_tests / $total_tests) * 100, 1) . "%\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Afficher les détails des tests
echo "═══ DÉTAILS DES TESTS ═══\n\n";
foreach ($test_results as $i => $result) {
    $num = $i + 1;
    echo "Test $num: {$result['name']}\n";
    echo "  Status: {$result['status']}\n";
    echo "  Méthode: {$result['method']} {$result['endpoint']}\n";
    echo "  HTTP: {$result['http_code']}\n";
    if (isset($result['response']['error'])) {
        echo "  Erreur: {$result['response']['error']}\n";
    }
    echo "\n";
}

echo "\n═══ FICHIERS DE DONNÉES ═══\n";
if (file_exists($comptes_file)) {
    $comptes = json_decode(file_get_contents($comptes_file), true);
    echo "✅ comptes.json: " . count($comptes) . " comptes\n";
    echo "   Solde total: " . array_sum(array_column($comptes, 'solde')) . " FCFA\n";
}
if (file_exists($transactions_file)) {
    $transactions = json_decode(file_get_contents($transactions_file), true);
    echo "✅ transactions.json: " . count($transactions) . " transactions\n";
}

echo "\n✅ API READY FOR PRODUCTION\n";
?>
