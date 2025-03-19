<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de débogage
error_log("Début du script login.php");

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

// Log des données reçues
$raw_data = file_get_contents('php://input');
error_log("Données brutes reçues: " . $raw_data);

// Récupérer et décoder les données JSON
$donnees = json_decode($raw_data, true);
error_log("Données décodées: " . print_r($donnees, true));

// Vérifier les données reçues
if (!isset($donnees['username']) || !isset($donnees['password'])) {
    error_log("Données de connexion manquantes");
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Données de connexion manquantes']);
    exit();
}

try {
    require_once __DIR__ . '/config/database.php';
    
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    if (!$cnxBDD) {
        throw new Exception("Impossible de se connecter à la base de données");
    }

    $sql = "SELECT u.*, v.VIS_NOM, v.VIS_PRENOM 
            FROM user u 
            LEFT JOIN visiteur v ON u.VIS_ID = v.VIS_ID 
            WHERE u.username = :username AND u.password = :password";
    
    $stmt = $cnxBDD->prepare($sql);
    $stmt->bindParam(':username', $donnees['username']);
    $stmt->bindParam(':password', $donnees['password']);
    $stmt->execute();
    
    error_log("Requête SQL exécutée");
    
    if ($stmt->rowCount() > 0) {
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Utilisateur trouvé: " . print_r($utilisateur, true));
        
        http_response_code(200);
        echo json_encode([
            'succes' => true,
            'message' => 'Connexion réussie',
            'donnees' => [
                'user' => [
                    'id' => $utilisateur['id'],
                    'username' => $utilisateur['username'],
                    'role' => $utilisateur['role'],
                    'VIS_ID' => $utilisateur['VIS_ID'],
                    'VIS_NOM' => $utilisateur['VIS_NOM'],
                    'VIS_PRENOM' => $utilisateur['VIS_PRENOM']
                ]
            ]
        ]);
    } else {
        error_log("Aucun utilisateur trouvé");
        http_response_code(401);
        echo json_encode([
            'succes' => false,
            'message' => 'Identifiants incorrects'
        ]);
    }
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'succes' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>