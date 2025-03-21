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
    
    $sql = "SELECT DISTINCT FFR_ANNEE FROM fiche_frais ORDER BY FFR_ANNEE DESC";
    $stmt = $cnxBDD->prepare($sql);
    $stmt->execute();
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $years]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>