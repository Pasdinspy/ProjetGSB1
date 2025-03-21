<?php
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
    
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Début de la transaction
    $cnxBDD->beginTransaction();

    try {
        // Si c'est un visiteur médical, créer d'abord l'entrée dans la table visiteur
        $vis_id = null;
        if ($data['role'] === 'VISITEUR_MEDICAL') {
            $sql = "INSERT INTO visiteur (VIS_NOM, VIS_PRENOM, VIS_ADRESSE, VIS_CP, VIS_VILLE, VIS_DATE_EMBAUCHE) 
                    VALUES (:nom, :prenom, :adresse, :cp, :ville, :date_embauche)";
            $stmt = $cnxBDD->prepare($sql);
            $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':adresse' => $data['adresse'],
                ':cp' => $data['cp'],
                ':ville' => $data['ville'],
                ':date_embauche' => $data['date_embauche']
            ]);
            $vis_id = $cnxBDD->lastInsertId();
        }

        // Créer l'utilisateur
        $sql = "INSERT INTO user (VIS_ID, username, password, role) 
                VALUES (:vis_id, :username, :password, :role)";
        $stmt = $cnxBDD->prepare($sql);
        $stmt->execute([
            ':vis_id' => $vis_id,
            ':username' => $data['username'],
            ':password' => $data['password'], // Mot de passe non haché
            ':role' => $data['role']
        ]);

        $cnxBDD->commit();
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès']);

    } catch (Exception $e) {
        $cnxBDD->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>