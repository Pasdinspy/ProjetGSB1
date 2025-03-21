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
    
    if (!isset($data['FFR_ID']) || !isset($data['repas']) || !isset($data['nuitees']) || 
        !isset($data['etape']) || !isset($data['km'])) {
        throw new Exception('Données manquantes');
    }

    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Début de la transaction
    $cnxBDD->beginTransaction();

    try {
        // Vérifier que la fiche existe et est en cours
        $sql = "SELECT f.*, ff.FOR_MONTANT 
                FROM fiche_frais f
                JOIN frais_forfait ff ON 1=1
                WHERE f.FFR_ID = :FFR_ID AND f.ETA_ID = 'CR'";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->bindParam(':FFR_ID', $data['FFR_ID']);
        $stmt->execute();
        
        $fiche = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fiche) {
            throw new Exception('Fiche non trouvée ou non modifiable');
        }

        // Mettre à jour les lignes de frais forfait
        $sql = "UPDATE ligne_frais_forfait SET LIG_QTE = :qte 
                WHERE FFR_ID = :FFR_ID AND FOR_ID = :for_id";
        $stmt = $cnxBDD->prepare($sql);

        // Repas
        $stmt->execute([
            ':qte' => $data['repas'],
            ':FFR_ID' => $data['FFR_ID'],
            ':for_id' => 'REP'
        ]);

        // Nuitées
        $stmt->execute([
            ':qte' => $data['nuitees'],
            ':FFR_ID' => $data['FFR_ID'],
            ':for_id' => 'NUI'
        ]);

        // Étape
        $stmt->execute([
            ':qte' => $data['etape'],
            ':FFR_ID' => $data['FFR_ID'],
            ':for_id' => 'ETP'
        ]);

        // Kilométrage
        $stmt->execute([
            ':qte' => $data['km'],
            ':FFR_ID' => $data['FFR_ID'],
            ':for_id' => 'KM'
        ]);

        // Calculer le nouveau montant total
        $sql = "UPDATE fiche_frais f
                SET FFR_MONTANT_VALIDE = (
                    SELECT SUM(lff.LIG_QTE * ff.FOR_MONTANT)
                    FROM ligne_frais_forfait lff
                    JOIN frais_forfait ff ON lff.FOR_ID = ff.FOR_ID
                    WHERE lff.FFR_ID = f.FFR_ID
                ),
                FFR_DATE_MODIF = CURRENT_DATE
                WHERE FFR_ID = :FFR_ID";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->execute([':FFR_ID' => $data['FFR_ID']]);

        $cnxBDD->commit();
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Fiche mise à jour avec succès']);

    } catch (Exception $e) {
        $cnxBDD->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>