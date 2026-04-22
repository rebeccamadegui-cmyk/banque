<?php
$methode = $_SERVER['REQUEST_METHOD'];

// CORS et préflight
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($methode === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}

// Route de documentation Swagger
if (isset($_GET['swagger']) || isset($_GET['doc']) || isset($_GET['docs'])) {
    header("Content-Type: text/html; charset=utf-8");
    echo file_get_contents(__DIR__ . '/swagger-ui/index.html');
    exit(0);
}

header("Content-Type: application/json");

$filename = __DIR__ . '/comptes.json';
$transactions_filename = __DIR__ . '/transactions.json';

// Initialiser les fichiers JSON s'ils n'existent pas
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}
if (!file_exists($transactions_filename)) {
    file_put_contents($transactions_filename, json_encode([]));
}

$comptes = json_decode(file_get_contents($filename), true);
$transactions = json_decode(file_get_contents($transactions_filename), true);

// Fonctions utilitaires
function trouverCompteParId($id) {
    global $comptes;
    foreach ($comptes as $index => $compte) {
        if ($compte['id'] == $id) {
            return ['index' => $index, 'compte' => $compte];
        }
    }
    return null;
}

function enregistrerTransaction($type, $compte_id, $montant, $description = '', $service = null, $service_destination = null) {
    global $transactions, $transactions_filename;
    $transaction = [
        'id' => time() . rand(1000, 9999),
        'compte_id' => $compte_id,
        'type' => $type,
        'montant' => $montant,
        'description' => $description,
        'service' => $service,
        'service_destination' => $service_destination,
        'date' => date('Y-m-d H:i:s')
    ];
    $transactions[] = $transaction;
    file_put_contents($transactions_filename, json_encode($transactions));
    return $transaction;
}

function sauvegarderComptes() {
    global $comptes, $filename;
    file_put_contents($filename, json_encode($comptes));
}

//---LOGIQUE DE L'API---//

// Récupérer l'ID du compte depuis l'URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($methode === 'GET') {
    if ($id) {
        // Consulter un compte spécifique
        $result = trouverCompteParId($id);
        if ($result) {
            // Récupérer les transactions du compte
            $compte_transactions = array_filter($transactions, function($t) use ($id) {
                return $t['compte_id'] == $id;
            });
            echo json_encode([
                "status" => "success",
                "compte" => $result['compte'],
                "transactions" => array_values($compte_transactions)
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Compte non trouvé"]);
        }
    } elseif ($action === 'transactions') {
        // Lister toutes les transactions
        echo json_encode([
            "status" => "success",
            "data" => $transactions
        ]);
    } elseif ($action === 'transactions_service') {
        // Lister les transactions par service
        $service = isset($_GET['service']) ? strtoupper($_GET['service']) : null;
        if (!$service) {
            http_response_code(400);
            echo json_encode(["error" => "Paramètre 'service' requis"]);
            exit;
        }
        
        $transactions_service = array_filter($transactions, function($t) use ($service) {
            return ($t['service'] === $service || $t['service_destination'] === $service);
        });
        
        echo json_encode([
            "status" => "success",
            "service" => $service,
            "data" => array_values($transactions_service)
        ]);
    } elseif ($action === 'services') {
        // Liste des services supportés
        echo json_encode([
            "status" => "success",
            "services" => ['OM', 'MOMO', 'UBA'],
            "description" => "Services de paiement supportés"
        ]);
    } else {
        // Lister tous les comptes
        echo json_encode([
            "status" => "success",
            "data" => $comptes
        ]);
    }
    exit;
}

elseif ($methode === 'POST') {
    if ($id && $action) {
        // Actions sur un compte existant
        $result = trouverCompteParId($id);
        if (!$result) {
            http_response_code(404);
            echo json_encode(["error" => "Compte non trouvé"]);
            exit;
        }

        $body = file_get_contents('php://input');
        $donnees = json_decode($body, true);

        if ($body === '' || $donnees === null) {
            http_response_code(400);
            echo json_encode(["error" => "Corps JSON invalide ou manquant"]);
            exit;
        }

        if ($action === 'depot') {
            if (!isset($donnees['montant']) || !is_numeric($donnees['montant']) || $donnees['montant'] <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Montant invalide pour le dépôt"]);
                exit;
            }
            $comptes[$result['index']]['solde'] += $donnees['montant'];
            sauvegarderComptes();
            $service = $donnees['service'] ?? null;
            $transaction = enregistrerTransaction('depot', $id, $donnees['montant'], $donnees['description'] ?? 'Dépôt', $service);
            echo json_encode([
                "message" => "Dépôt effectué avec succès",
                "compte" => $comptes[$result['index']],
                "transaction" => $transaction
            ]);
        }
        elseif ($action === 'retrait') {
            if (!isset($donnees['montant']) || !is_numeric($donnees['montant']) || $donnees['montant'] <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Montant invalide pour le retrait"]);
                exit;
            }
            if ($comptes[$result['index']]['solde'] < $donnees['montant']) {
                http_response_code(400);
                echo json_encode(["error" => "Solde insuffisant"]);
                exit;
            }
            $comptes[$result['index']]['solde'] -= $donnees['montant'];
            sauvegarderComptes();
            $service = $donnees['service'] ?? null;
            enregistrerTransaction('retrait', $id, -$donnees['montant'], $donnees['description'] ?? 'Retrait', $service);
            echo json_encode([
                "message" => "Retrait effectué avec succès",
                "compte" => $comptes[$result['index']]
            ]);
        }
        elseif ($action === 'virement') {
            if (!isset($donnees['montant']) || !is_numeric($donnees['montant']) || $donnees['montant'] <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Montant invalide pour le virement"]);
                exit;
            }
            if (!isset($donnees['compte_destinataire'])) {
                http_response_code(400);
                echo json_encode(["error" => "ID du compte destinataire requis"]);
                exit;
            }
            if ($comptes[$result['index']]['solde'] < $donnees['montant']) {
                http_response_code(400);
                echo json_encode(["error" => "Solde insuffisant"]);
                exit;
            }

            $dest_result = trouverCompteParId($donnees['compte_destinataire']);
            if (!$dest_result) {
                http_response_code(404);
                echo json_encode(["error" => "Compte destinataire non trouvé"]);
                exit;
            }

            // Effectuer le virement
            $comptes[$result['index']]['solde'] -= $donnees['montant'];
            $comptes[$dest_result['index']]['solde'] += $donnees['montant'];
            sauvegarderComptes();

            enregistrerTransaction('virement_sortant', $id, -$donnees['montant'], 'Virement vers compte ' . $donnees['compte_destinataire']);
            enregistrerTransaction('virement_entrant', $donnees['compte_destinataire'], $donnees['montant'], 'Virement depuis compte ' . $id);

            echo json_encode([
                "message" => "Virement effectué avec succès",
                "compte_source" => $comptes[$result['index']],
                "compte_destinataire" => $comptes[$dest_result['index']]
            ]);
        }
        elseif ($action === 'transfert_service') {
            // Transfert d'argent vers un autre service (OM, MOMO, UBA)
            if (!isset($donnees['montant']) || !is_numeric($donnees['montant']) || $donnees['montant'] <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Montant invalide pour le transfert"]);
                exit;
            }
            
            if (!isset($donnees['service_destination'])) {
                http_response_code(400);
                echo json_encode(["error" => "Service de destination requis (OM, MOMO, UBA)"]);
                exit;
            }
            
            $services_valides = ['OM', 'MOMO', 'UBA'];
            if (!in_array(strtoupper($donnees['service_destination']), $services_valides)) {
                http_response_code(400);
                echo json_encode(["error" => "Service invalide. Services acceptés: OM, MOMO, UBA"]);
                exit;
            }
            
            $service_destination = strtoupper($donnees['service_destination']);
            $service_origine = $donnees['service_origine'] ?? 'BANQUE';
            
            if ($comptes[$result['index']]['solde'] < $donnees['montant']) {
                http_response_code(400);
                echo json_encode(["error" => "Solde insuffisant pour ce transfert"]);
                exit;
            }
            
            // Effectuer le transfert
            $comptes[$result['index']]['solde'] -= $donnees['montant'];
            sauvegarderComptes();
            
            // Enregistrer les transactions
            enregistrerTransaction(
                'transfert_service_sortant', 
                $id, 
                -$donnees['montant'], 
                "Transfert vers " . $service_destination . " - " . ($donnees['numero_beneficiaire'] ?? ''),
                $service_origine,
                $service_destination
            );
            
            echo json_encode([
                "status" => "success",
                "message" => "Transfert vers " . $service_destination . " effectué avec succès",
                "compte" => $comptes[$result['index']],
                "details" => [
                    "montant" => $donnees['montant'],
                    "service_origine" => $service_origine,
                    "service_destination" => $service_destination,
                    "numero_beneficiaire" => $donnees['numero_beneficiaire'] ?? null,
                    "reference" => "REF" . time(),
                    "date" => date('Y-m-d H:i:s')
                ]
            ]);
        }
        elseif ($action === 'reception_service') {
            // Recevoir de l'argent d'un autre service (OM, MOMO, UBA)
            if (!isset($donnees['montant']) || !is_numeric($donnees['montant']) || $donnees['montant'] <= 0) {
                http_response_code(400);
                echo json_encode(["error" => "Montant invalide"]);
                exit;
            }
            
            if (!isset($donnees['service_origine'])) {
                http_response_code(400);
                echo json_encode(["error" => "Service d'origine requis"]);
                exit;
            }
            
            $services_valides = ['OM', 'MOMO', 'UBA'];
            if (!in_array(strtoupper($donnees['service_origine']), $services_valides)) {
                http_response_code(400);
                echo json_encode(["error" => "Service d'origine invalide"]);
                exit;
            }
            
            // Effectuer la réception
            $comptes[$result['index']]['solde'] += $donnees['montant'];
            sauvegarderComptes();
            
            $service_origine = strtoupper($donnees['service_origine']);
            
            // Enregistrer la transaction
            enregistrerTransaction(
                'transfert_service_entrant', 
                $id, 
                $donnees['montant'], 
                "Réception depuis " . $service_origine . " - " . ($donnees['numero_emetteur'] ?? ''),
                $service_origine,
                'BANQUE'
            );
            
            echo json_encode([
                "status" => "success",
                "message" => "Fonds reçus de " . $service_origine . " avec succès",
                "compte" => $comptes[$result['index']],
                "details" => [
                    "montant" => $donnees['montant'],
                    "service_origine" => $service_origine,
                    "numero_emetteur" => $donnees['numero_emetteur'] ?? null,
                    "reference" => "REF" . time(),
                    "date" => date('Y-m-d H:i:s')
                ]
            ]);
        }
        else {
            http_response_code(400);
            echo json_encode(["error" => "Action non reconnue"]);
        }
        } else {
            // Créer un compte
        $body = file_get_contents('php://input');
        $donnees = json_decode($body, true);

        if ($body === '' || $donnees === null) {
            http_response_code(400);
            echo json_encode(["error" => "Corps JSON invalide ou manquant"]);
            exit;
        }

        if (!empty($donnees['titulaire'])) {
            $nouveauCompte = [
                "id" => time(), // ID unique basé sur l'heure
                "numero" => "FR" . rand(1000, 9999) . "BANK",
                "titulaire" => $donnees['titulaire'],
                "solde" => 0
            ];

            $comptes[] = $nouveauCompte;
            sauvegarderComptes();

            echo json_encode([
                "message" => "Compte cree avec succes",
                "compte" => $nouveauCompte
            ]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Le nom du titulaire est requis"]);
        }
    }
    exit;
}

elseif ($methode === 'PUT') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID du compte requis"]);
        exit;
    }

    $result = trouverCompteParId($id);
    if (!$result) {
        http_response_code(404);
        echo json_encode(["error" => "Compte non trouvé"]);
        exit;
    }

    $body = file_get_contents('php://input');
    $donnees = json_decode($body, true);

    if ($body === '' || $donnees === null) {
        http_response_code(400);
        echo json_encode(["error" => "Corps JSON invalide ou manquant"]);
        exit;
    }

    if (isset($donnees['titulaire']) && !empty($donnees['titulaire'])) {
        $comptes[$result['index']]['titulaire'] = $donnees['titulaire'];
        sauvegarderComptes();
        echo json_encode([
            "message" => "Compte mis à jour avec succès",
            "compte" => $comptes[$result['index']]
        ]);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Nom du titulaire requis pour la mise à jour"]);
    }
    exit;
}

elseif ($methode === 'DELETE') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(["error" => "ID du compte requis"]);
        exit;
    }

    $result = trouverCompteParId($id);
    if (!$result) {
        http_response_code(404);
        echo json_encode(["error" => "Compte non trouvé"]);
        exit;
    }

    // Supprimer le compte
    array_splice($comptes, $result['index'], 1);
    sauvegarderComptes();

    echo json_encode([
        "message" => "Compte supprimé avec succès"
    ]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée. Utilisez GET, POST, PUT ou DELETE."]);

