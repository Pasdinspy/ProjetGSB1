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
    if (!isset($_GET['FFR_ID'])) {
        throw new Exception('ID de la fiche manquant');
    }

    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    $sql = "SELECT lff.FOR_ID, lff.LIG_QTE 
            FROM ligne_frais_forfait lff
            WHERE lff.FFR_ID = :FFR_ID";
            
    $stmt = $cnxBDD->prepare($sql);
    $stmt->bindParam(':FFR_ID', $_GET['FFR_ID']);
    $stmt->execute();
    
    $frais = [
        'REP' => 0,
        'NUI' => 0,
        'ETP' => 0,
        'KM' => 0
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $frais[$row['FOR_ID']] = intval($row['LIG_QTE']);
    }

    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'data' => [
            'repas' => $frais['REP'],
            'nuitees' => $frais['NUI'],
            'etape' => $frais['ETP'],
            'km' => $frais['KM']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>