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
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    // Récupérer le VIS_ID depuis la requête
    $vis_id = isset($_GET['vis_id']) ? $_GET['vis_id'] : null;
    $role = isset($_GET['role']) ? $_GET['role'] : null;

    // Vérifier et mettre à jour automatiquement les fiches de plus d'un mois
    $updateQuery = "UPDATE fiche_frais 
                   SET ETA_ID = 'CL' 
                   WHERE ETA_ID = 'CR' 
                   AND DATE_FORMAT(FFR_DATE_MODIF, '%Y-%m') < DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m')";
    $cnxBDD->exec($updateQuery);

    // Construire la requête SQL en fonction du rôle
    $sql = "SELECT f.*, v.VIS_NOM, v.VIS_PRENOM, e.ETA_LIB as status,
            DATE_FORMAT(f.FFR_DATE_MODIF, '%Y-%m') as periode,
            DATE_FORMAT(NOW(), '%Y-%m') as current_period
            FROM fiche_frais f
            INNER JOIN visiteur v ON f.VIS_ID = v.VIS_ID
            INNER JOIN etat e ON f.ETA_ID = e.ETA_ID";
    // Construire la requête SQL en fonction du rôle
    $sql = "SELECT f.*, v.VIS_NOM, v.VIS_PRENOM, e.ETA_LIB as status
            FROM fiche_frais f
            INNER JOIN visiteur v ON f.VIS_ID = v.VIS_ID
            INNER JOIN etat e ON f.ETA_ID = e.ETA_ID";

    // Si c'est un visiteur médical, filtrer par VIS_ID
    if ($role === 'VISITEUR_MEDICAL' && $vis_id) {
        $sql .= " WHERE f.VIS_ID = :vis_id";
    }
    
    $sql .= " ORDER BY f.FFR_DATE_MODIF DESC";

    $stmt = $cnxBDD->prepare($sql);
    
    if ($role === 'VISITEUR_MEDICAL' && $vis_id) {
        $stmt->bindParam(':vis_id', $vis_id);
    }
    
    $stmt->execute();
    $fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $fiches]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>