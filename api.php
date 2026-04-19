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

// Initialiser le fichier JSON s'il n'existe pas
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}

$comptes = json_decode(file_get_contents($filename), true);

//---LOGIQUE DE L'API---//

if ($methode === 'GET') {
    // Action : Lister tous les comptes
    echo json_encode([
        "status" => "success",
        "data" => $comptes
    ]);
    exit;
}

elseif ($methode === 'POST') {
    // Action : Créer un compte
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
        file_put_contents($filename, json_encode($comptes));

        echo json_encode([
            "message" => "Compte cree avec succes",
            "compte" => $nouveauCompte
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Le nom du titulaire est requis"]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée. Utilisez GET ou POST."]);

