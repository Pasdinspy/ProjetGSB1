<?php
header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/database.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Début de la transaction
    $cnxBDD->beginTransaction();

    try {
        // Si c'est un visiteur médical, mettre à jour les informations du visiteur
        if ($data['role'] === 'VISITEUR_MEDICAL' && isset($data['VIS_ID'])) {
            $sql = "UPDATE visiteur SET 
                    VIS_NOM = :nom,
                    VIS_PRENOM = :prenom,
                    VIS_ADRESSE = :adresse,
                    VIS_CP = :cp,
                    VIS_VILLE = :ville,
                    VIS_DATE_EMBAUCHE = :date_embauche
                    WHERE VIS_ID = :vis_id";
            $stmt = $cnxBDD->prepare($sql);
            $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':adresse' => $data['adresse'],
                ':cp' => $data['cp'],
                ':ville' => $data['ville'],
                ':date_embauche' => $data['date_embauche'],
                ':vis_id' => $data['VIS_ID']
            ]);
        }

        // Mettre à jour l'utilisateur
        $sql = "UPDATE user SET username = :username, role = :role";
        $params = [
            ':username' => $data['username'],
            ':role' => $data['role'],
            ':id' => $data['id']
        ];

        // Si un nouveau mot de passe est fourni
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
            $params[':password'] = $data['password']; // Mot de passe non haché
        }

        $sql .= " WHERE id = :id";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->execute($params);

        $cnxBDD->commit();
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);

    } catch (Exception $e) {
        $cnxBDD->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>