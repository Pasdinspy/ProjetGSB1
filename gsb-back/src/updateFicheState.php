<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    
    // Récupérer les données du corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['FFR_ID']) || !isset($data['ETA_ID'])) {
        throw new Exception('Données manquantes');
    }

    // Mettre à jour l'état de la fiche
    $sql = "UPDATE fiche_frais 
            SET ETA_ID = :eta_id,
                FFR_DATE_MODIF = CURRENT_DATE
            WHERE FFR_ID = :ffr_id";

    $stmt = $cnxBDD->prepare($sql);
    $stmt->bindParam(':eta_id', $data['ETA_ID']);
    $stmt->bindParam(':ffr_id', $data['FFR_ID']);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'État de la fiche mis à jour avec succès'
        ]);
    } else {
        throw new Exception('Erreur lors de la mise à jour de la fiche');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>