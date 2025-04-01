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
    
    // Récupérer les paramètres de la requête
    $vis_id = isset($_GET['vis_id']) ? $_GET['vis_id'] : null;
    $role = isset($_GET['role']) ? $_GET['role'] : null;

    // Table de conversion des mois en nombres
    $moisToNumber = [
        'JANVIER' => 1,
        'FEVRIER' => 2,
        'MARS' => 3,
        'AVRIL' => 4,
        'MAI' => 5,
        'JUIN' => 6,
        'JUILLET' => 7,
        'AOUT' => 8,
        'SEPTEMBRE' => 9,
        'OCTOBRE' => 10,
        'NOVEMBRE' => 11,
        'DECEMBRE' => 12
    ];

    // Mise à jour des fiches de frais
    $updateQuery = "UPDATE fiche_frais 
                   SET ETA_ID = 'CL' 
                   WHERE ETA_ID = 'CR' 
                   AND (
                       STR_TO_DATE(CONCAT(FFR_ANNEE, '-', 
                           CASE FFR_MOIS 
                               WHEN 'JANVIER' THEN '01'
                               WHEN 'FEVRIER' THEN '02'
                               WHEN 'MARS' THEN '03'
                               WHEN 'AVRIL' THEN '04'
                               WHEN 'MAI' THEN '05'
                               WHEN 'JUIN' THEN '06'
                               WHEN 'JUILLET' THEN '07'
                               WHEN 'AOUT' THEN '08'
                               WHEN 'SEPTEMBRE' THEN '09'
                               WHEN 'OCTOBRE' THEN '10'
                               WHEN 'NOVEMBRE' THEN '11'
                               WHEN 'DECEMBRE' THEN '12'
                           END, '-01'), '%Y-%m-%d') < DATE_FORMAT(NOW(), '%Y-%m-01')
                   )";

    $cnxBDD->exec($updateQuery);

    // Requête pour récupérer les fiches
    $sql = "SELECT 
                f.*,
                v.VIS_NOM,
                v.VIS_PRENOM,
                e.ETA_LIB as status
            FROM fiche_frais f
            INNER JOIN visiteur v ON f.VIS_ID = v.VIS_ID
            INNER JOIN etat e ON f.ETA_ID = e.ETA_ID";

    // Ajout du filtre pour le visiteur médical
    if ($role === 'VISITEUR_MEDICAL' && $vis_id) {
        $sql .= " WHERE f.VIS_ID = :vis_id";
    }

    // Tri des résultats
    $sql .= " ORDER BY f.FFR_ANNEE DESC, 
              CASE f.FFR_MOIS 
                  WHEN 'DECEMBRE' THEN 12
                  WHEN 'NOVEMBRE' THEN 11
                  WHEN 'OCTOBRE' THEN 10
                  WHEN 'SEPTEMBRE' THEN 9
                  WHEN 'AOUT' THEN 8
                  WHEN 'JUILLET' THEN 7
                  WHEN 'JUIN' THEN 6
                  WHEN 'MAI' THEN 5
                  WHEN 'AVRIL' THEN 4
                  WHEN 'MARS' THEN 3
                  WHEN 'FEVRIER' THEN 2
                  WHEN 'JANVIER' THEN 1
              END DESC";

    $stmt = $cnxBDD->prepare($sql);
    if ($role === 'VISITEUR_MEDICAL' && $vis_id) {
        $stmt->bindParam(':vis_id', $vis_id);
    }
    
    $stmt->execute();
    $fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajout d'informations supplémentaires
    foreach ($fiches as &$fiche) {
        $fiche['modifiable'] = $fiche['ETA_ID'] === 'CR';
        $fiche['supprimable'] = $fiche['ETA_ID'] === 'CR';
        $fiche['etat_libelle'] = match($fiche['ETA_ID']) {
            'CR' => 'En cours',
            'CL' => 'Clôturée',
            'VA' => 'Validée',
            'RE' => 'Remboursée',
            default => $fiche['ETA_ID']
        };
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $fiches,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log("Erreur dans getFicheFrais.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des fiches de frais: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>