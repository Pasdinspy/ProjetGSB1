<?php

namespace Debian\GsbBack\Controllers;

use Debian\GsbBack\Database;
use Firebase\JWT\JWT;
use PDO;
use PDOException;

class LoginController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleLogin() {
        // Headers CORS et JSON
        header('Access-Control-Allow-Origin: ' . $_ENV['FRONTEND_URL']);
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
            $this->sendResponse(false, 'Méthode non autorisée', null, 405);
            return;
        }

        // Récupérer et décoder les données JSON
        $donnees = json_decode(file_get_contents('php://input'), true);

        // Vérifier les données reçues
        if (!isset($donnees['username']) || !isset($donnees['password'])) {
            $this->sendResponse(false, 'Données de connexion manquantes', null, 400);
            return;
        }

        try {
            $conn = $this->db->getConnection();
            
            // Requête préparée
            $sql = "SELECT u.id, u.VIS_ID, u.role, u.password, 
                    v.VIS_NOM, v.VIS_PRENOM 
                    FROM user u 
                    LEFT JOIN visiteur v ON u.VIS_ID = v.VIS_ID 
                    WHERE u.username = :username";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $donnees['username']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $utilisateur = $stmt->fetch();
                
                if ($utilisateur['password'] === $donnees['password']) {
                    // Retirer le mot de passe des données
                    unset($utilisateur['password']);
                    
                    // Créer le token JWT
                    $payload = [
                        'id' => $utilisateur['id'],
                        'role' => $utilisateur['role'],
                        'VIS_ID' => $utilisateur['VIS_ID'],
                        'exp' => time() + (60 * 60) // Expire dans 1 heure
                    ];

                    $jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
                    
                    $this->sendResponse(true, 'Connexion réussie', [
                        'user' => $utilisateur,
                        'token' => $jwt
                    ]);
                } else {
                    $this->sendResponse(false, 'Identifiants incorrects', null, 401);
                }
            } else {
                $this->sendResponse(false, 'Identifiants incorrects', null, 401);
            }
        } catch (PDOException $e) {
            $this->sendResponse(false, 'Erreur de base de données', null, 500);
        }
    }

    private function sendResponse($success, $message, $data = null, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'succes' => $success,
            'message' => $message,
            'donnees' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
}