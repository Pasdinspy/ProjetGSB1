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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

require_once __DIR__ . '/config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['FFR_ID'])) {
        throw new Exception('ID de la fiche manquant');
    }

    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Début de la transaction
    $cnxBDD->beginTransaction();

    try {
        // Vérifier que la fiche existe et est en cours
        $sql = "SELECT ETA_ID, VIS_ID FROM fiche_frais WHERE FFR_ID = :FFR_ID";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->bindParam(':FFR_ID', $data['FFR_ID']);
        $stmt->execute();
        
        $fiche = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fiche) {
            throw new Exception('Fiche non trouvée');
        }

        if ($fiche['ETA_ID'] !== 'CR') {
            throw new Exception('Seules les fiches en cours peuvent être supprimées');
        }

        // Vérifier les droits d'accès
        if ($data['role'] !== 'ADMINISTRATEUR' && $fiche['VIS_ID'] != $data['VIS_ID']) {
            throw new Exception('Non autorisé à supprimer cette fiche');
        }

        // Supprimer les lignes de frais forfaitaires
        $sql = "DELETE FROM ligne_frais_forfait WHERE FFR_ID = :FFR_ID";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->bindParam(':FFR_ID', $data['FFR_ID']);
        $stmt->execute();

        // Supprimer la fiche principale
        $sql = "DELETE FROM fiche_frais WHERE FFR_ID = :FFR_ID";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->bindParam(':FFR_ID', $data['FFR_ID']);
        $stmt->execute();

        // Valider la transaction
        $cnxBDD->commit();

        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Fiche supprimée avec succès'
        ]);

    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $cnxBDD->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
    ]);
}

// Fermer la connexion
$cnxBDD = null;
?>