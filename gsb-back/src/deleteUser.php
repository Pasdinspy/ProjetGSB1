<?php
header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID utilisateur manquant');
    }

    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Début de la transaction
    $cnxBDD->beginTransaction();

    try {
        // D'abord, on récupère les informations de l'utilisateur
        $sql = "SELECT role, VIS_ID FROM user WHERE id = :id";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->execute([':id' => $data['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si c'est un visiteur médical, on supprime d'abord son entrée dans la table visiteur
        if ($user && $user['role'] === 'VISITEUR_MEDICAL' && $user['VIS_ID']) {
            $sql = "DELETE FROM visiteur WHERE VIS_ID = :vis_id";
            $stmt = $cnxBDD->prepare($sql);
            $stmt->execute([':vis_id' => $user['VIS_ID']]);
        }

        // Ensuite, on supprime l'utilisateur
        $sql = "DELETE FROM user WHERE id = :id";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->execute([':id' => $data['id']]);

        $cnxBDD->commit();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);

    } catch (Exception $e) {
        $cnxBDD->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>