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
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Vérification des données requises
    if (!isset($data['FFR_ID']) || !isset($data['ETA_ID'])) {
        throw new Exception('Données manquantes');
    }

    // Vérification que l'état demandé est valide (RE pour remboursé)
    if ($data['ETA_ID'] !== 'RE') {
        throw new Exception('État invalide');
    }

    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Début de la transaction
    $cnxBDD->beginTransaction();

    try {
        // Vérifier que la fiche existe et est clôturée (CL)
        $sql = "SELECT FFR_ID, ETA_ID 
                FROM fiche_frais 
                WHERE FFR_ID = :FFR_ID 
                AND ETA_ID = 'CL'";
        
        $stmt = $cnxBDD->prepare($sql);
        $stmt->bindParam(':FFR_ID', $data['FFR_ID']);
        $stmt->execute();
        
        $fiche = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fiche) {
            throw new Exception('Fiche non trouvée ou déjà remboursée');
        }

        // Mettre à jour l'état de la fiche
        $sql = "UPDATE fiche_frais 
                SET ETA_ID = :ETA_ID,
                    FFR_DATE_MODIF = CURRENT_DATE
                WHERE FFR_ID = :FFR_ID";
                
        $stmt = $cnxBDD->prepare($sql);
        $stmt->execute([
            ':ETA_ID' => $data['ETA_ID'],
            ':FFR_ID' => $data['FFR_ID']
        ]);

        $cnxBDD->commit();
        
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'La fiche a été marquée comme remboursée'
        ]);

    } catch (Exception $e) {
        $cnxBDD->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>