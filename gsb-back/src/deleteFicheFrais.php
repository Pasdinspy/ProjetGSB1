<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration des en-têtes CORS
header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Gestion de la requête OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

require_once __DIR__ . '/config/database.php';

class FicheFraisManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Vérifie l'existence et l'état d'une fiche de frais
     * @throws Exception
     */
    private function verifierFiche(int $ffrId): array {
        $sql = "SELECT ETA_ID, VIS_ID FROM fiche_frais WHERE FFR_ID = :FFR_ID";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':FFR_ID', $ffrId, PDO::PARAM_INT);
        $stmt->execute();
        
        $fiche = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fiche) {
            throw new Exception('Fiche non trouvée');
        }
        
        if ($fiche['ETA_ID'] !== 'CR') {
            throw new Exception('Seules les fiches en cours peuvent être supprimées');
        }

        return $fiche;
    }

    /**
     * Vérifie les droits d'accès pour la suppression
     * @throws Exception
     */
    private function verifierDroitsAcces(string $role, int $visId, int $ficheVisId): void {
        if ($role !== 'ADMINISTRATEUR' && $visId !== $ficheVisId) {
            throw new Exception('Non autorisé à supprimer cette fiche');
        }
    }

    /**
     * Supprime les lignes de frais forfaitaires
     */
    private function supprimerLignesFraisForfait(int $ffrId): void {
        $sql = "DELETE FROM ligne_frais_forfait WHERE FFR_ID = :FFR_ID";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':FFR_ID', $ffrId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Supprime la fiche de frais principale
     */
    private function supprimerFicheFrais(int $ffrId): void {
        $sql = "DELETE FROM fiche_frais WHERE FFR_ID = :FFR_ID";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':FFR_ID', $ffrId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Procédure principale de suppression d'une fiche de frais
     * @throws Exception
     */
    public function supprimerFiche(array $data): void {
        if (!isset($data['FFR_ID']) || !is_numeric($data['FFR_ID'])) {
            throw new Exception('ID de la fiche invalide ou manquant');
        }

        $ffrId = (int)$data['FFR_ID'];
        
        // Début de la transaction
        $this->db->beginTransaction();

        try {
            // Vérifications
            $fiche = $this->verifierFiche($ffrId);
            $this->verifierDroitsAcces(
                $data['role'] ?? '', 
                (int)($data['VIS_ID'] ?? 0), 
                (int)$fiche['VIS_ID']
            );

            // Suppressions dans l'ordre
            $this->supprimerLignesFraisForfait($ffrId);
            $this->supprimerFicheFrais($ffrId);

            // Validation de la transaction
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
    $manager = new FicheFraisManager($database->getConnection());

    // Suppression de la fiche
    $manager->supprimerFiche($data);

    // Réponse en cas de succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Fiche supprimée avec succès'
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