<?php
declare(strict_types=1);

// Configuration des en-têtes CORS
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

class UserManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Met à jour les informations d'un visiteur médical
     */
    private function updateVisiteur(array $data): void {
        $sql = "UPDATE visiteur SET 
                VIS_NOM = :nom,
                VIS_PRENOM = :prenom,
                VIS_ADRESSE = :adresse,
                VIS_CP = :cp,
                VIS_VILLE = :ville,
                VIS_DATE_EMBAUCHE = :date_embauche
                WHERE VIS_ID = :vis_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nom', $data['nom'], PDO::PARAM_STR);
        $stmt->bindValue(':prenom', $data['prenom'], PDO::PARAM_STR);
        $stmt->bindValue(':adresse', $data['adresse'], PDO::PARAM_STR);
        $stmt->bindValue(':cp', $data['cp'], PDO::PARAM_STR);
        $stmt->bindValue(':ville', $data['ville'], PDO::PARAM_STR);
        $stmt->bindValue(':date_embauche', $data['date_embauche'], PDO::PARAM_STR);
        $stmt->bindValue(':vis_id', $data['VIS_ID'], PDO::PARAM_INT);
        
        $stmt->execute();
    }

    /**
     * Met à jour les informations de l'utilisateur
     */
    private function updateUser(array $data): void {
        $sql = "UPDATE user SET username = :username, role = :role";
        $params = [
            ':username' => $data['username'],
            ':role' => $data['role'],
            ':id' => $data['id']
        ];

        if (!empty($data['password'])) {
            $sql .= ", password = :password";
            $params[':password'] = $data['password']; // Pas de hashage comme demandé
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, 
                $param === ':id' ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
        
        $stmt->execute();
    }

    /**
     * Procédure principale de mise à jour d'un utilisateur
     * @throws Exception
     */
    public function updateUserData(array $data): void {
        if (!isset($data['id']) || !isset($data['username']) || !isset($data['role'])) {
            throw new Exception('Données manquantes');
        }

        $this->db->beginTransaction();

        try {
            // Mise à jour du visiteur si c'est un visiteur médical
            if ($data['role'] === 'VISITEUR_MEDICAL' && isset($data['VIS_ID'])) {
                $this->updateVisiteur($data);
            }

            // Mise à jour de l'utilisateur
            $this->updateUser($data);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}

// Exécution principale
try {
    // Récupération et validation des données
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

    // Initialisation de la connexion
    $database = new Database();
    $manager = new UserManager($database->getConnection());

    // Mise à jour de l'utilisateur
    $manager->updateUserData($data);

    // Réponse en cas de succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur mis à jour avec succès'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ]);
    error_log($e->getMessage());
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}