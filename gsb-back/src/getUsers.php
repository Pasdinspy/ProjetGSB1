<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Récupérer tous les utilisateurs de la table user
    $sql = "SELECT u.*, v.VIS_NOM, v.VIS_PRENOM, v.VIS_ADRESSE, v.VIS_CP, v.VIS_VILLE, v.VIS_DATE_EMBAUCHE 
            FROM user u 
            LEFT JOIN visiteur v ON u.VIS_ID = v.VIS_ID
            ORDER BY u.username";
            
    $stmt = $cnxBDD->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>