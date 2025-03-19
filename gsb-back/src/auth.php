<?php
namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use PDO;
use PDOException;

// Charger les variables d'environnement
$envFile = __DIR__ . '/../../.env';
if (!file_exists($envFile)) {
    die("Fichier .env non trouvé");
}
$envVars = parse_ini_file($envFile);

// Activation des erreurs PHP pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers CORS et JSON
header('Access-Control-Allow-Origin: ' . ($envVars['FRONTEND_URL'] ?? '*'));
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Gérer les requêtes OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Méthode non autorisée', null, 405);
}

// Récupérer et décoder les données JSON
$donnees = json_decode(file_get_contents('php://input'), true);

// Vérifier les données reçues
if (!isset($donnees['username']) || !isset($donnees['password'])) {
    sendResponse(false, 'Données de connexion manquantes', null, 400);
}

try {
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Requête préparée
    $sql = "SELECT u.id, u.VIS_ID, u.role, u.password, 
            v.VIS_NOM, v.VIS_PRENOM, v.VIS_ADRESSE, v.VIS_CP, v.VIS_VILLE 
            FROM user u 
            LEFT JOIN visiteur v ON u.VIS_ID = v.VIS_ID 
            WHERE u.username = :username";
    
    $stmt = $cnxBDD->prepare($sql);
    $stmt->bindParam(':username', $donnees['username']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $utilisateur = $stmt->fetch();
        
        // Vérification du mot de passe (simple comparaison pour l'instant)
        if ($utilisateur['password'] === $donnees['password']) {
            // Retirer le mot de passe des données renvoyées
            unset($utilisateur['password']);
            
            // Log de connexion réussie
            error_log(sprintf(
                "Connexion réussie - Utilisateur: %s, Role: %s, Date: %s",
                $donnees['username'],
                $utilisateur['role'],
                date('Y-m-d H:i:s')
            ));
            
            sendResponse(true, 'Connexion réussie', $utilisateur);
        } else {
            error_log("Échec de connexion - Mot de passe incorrect pour l'utilisateur: " . $donnees['username']);
            sendResponse(false, 'Identifiants incorrects', null, 401);
        }
    } else {
        error_log("Échec de connexion - Utilisateur non trouvé: " . $donnees['username']);
        sendResponse(false, 'Identifiants incorrects', null, 401);
    }
} catch (PDOException $e) {
    error_log('Erreur PDO: ' . $e->getMessage());
    sendResponse(false, 'Erreur de base de données', null, 500);
}

function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'succes' => $success,
        'message' => $message,
        'donnees' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}