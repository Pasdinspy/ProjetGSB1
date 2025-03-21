<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de débogage
error_log("Début du script getFichesFrais.php");

// Headers CORS plus permissifs pour le développement
header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Gérer les requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Requête OPTIONS reçue");
    http_response_code(200);
    exit();
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer et décoder les données JSON
$raw_data = file_get_contents('php://input');
error_log("Données brutes reçues: " . $raw_data);
$donnees = json_decode($raw_data, true);
error_log("Données décodées: " . print_r($donnees, true));

// Vérifier les données reçues
if (!isset($donnees['role'])) {
    error_log("Données de rôle manquantes");
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Données de rôle manquantes']);
    exit();
}

$role = $donnees['role'];
$VIS_ID = isset($donnees['VIS_ID']) ? $donnees['VIS_ID'] : null;

try {
    require_once __DIR__ . '/config/database.php';
    
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    if (!$cnxBDD) {
        throw new Exception("Impossible de se connecter à la base de données");
    }

    // Construire la requête SQL en fonction du rôle
    if ($role === 'ADMINISTRATEUR') {
        $sql = "SELECT * FROM fiche_frais";
        $stmt = $cnxBDD->prepare($sql);
    } else {
        $sql = "SELECT * FROM fiche_frais WHERE VIS_ID = :VIS_ID";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->bindParam(':VIS_ID', $VIS_ID);
    }
    
    $stmt->execute();
    $fichesFrais = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cnxBDD = null;

    http_response_code(200);
    echo json_encode(['succes' => true, 'fichesFrais' => $fichesFrais]);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>