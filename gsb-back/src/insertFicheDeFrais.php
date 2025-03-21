<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de débogage
error_log("Début du script insertFicheDeFrais.php");

// Headers CORS plus permissifs pour le développement
header('Access-Control-Allow-Origin: http://51.83.74.206:8080');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Gérer les requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    error_log("Requête OPTIONS reçue");
    http_response_code(200);
    exit();
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['succes' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer et décoder les données JSON
$raw_data = file_get_contents('php://input');
error_log("Données brutes reçues: " . $raw_data);
$donnees = json_decode($raw_data, true);
error_log("Données décodées: " . print_r($donnees, true));

// Vérifier les données reçues
if (!isset($donnees['month']) || !isset($donnees['year']) || !isset($donnees['repas']) || !isset($donnees['nuitees']) || !isset($donnees['etape']) || !isset($donnees['km']) || !isset($donnees['VIS_ID'])) {
    error_log("Données de frais manquantes");
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Données de frais manquantes']);
    exit();
}

$month = $donnees['month'];
$year = $donnees['year'];
$repas = $donnees['repas'];
$nuitees = $donnees['nuitees'];
$etape = $donnees['etape'];
$km = $donnees['km'];
$VIS_ID = $donnees['VIS_ID'];

$date_modif = date("Y-m-d");

try {
    require_once __DIR__ . '/config/database.php';
    
    $database = new Database();
    $cnxBDD = $database->getConnection();
    
    if (!$cnxBDD) {
        throw new Exception("Impossible de se connecter à la base de données");
    }

    // Vérifier si le VIS_ID existe dans la table visiteur
    $sql = "SELECT 1 FROM visiteur WHERE VIS_ID = :VIS_ID";
    $stmt = $cnxBDD->prepare($sql);
    $stmt->execute([':VIS_ID' => $VIS_ID]);
    if ($stmt->rowCount() == 0) {
        throw new Exception("VIS_ID n'existe pas");
    }

    // Récupérer les montants des frais forfait
    $sql = "SELECT FOR_ID, FOR_MONTANT FROM frais_forfait WHERE FOR_ID IN ('ETP', 'KM', 'NUI', 'REP')"; 
    $stmt = $cnxBDD->prepare($sql);
    $stmt->execute();
    
    $frais_forfait = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $frais_forfait[$row['FOR_ID']] = $row['FOR_MONTANT'];
    }

    // Calcul des totaux
    $total_etape = $etape * $frais_forfait['ETP'];
    $total_repas = $repas * $frais_forfait['REP'];
    $total_nuitees = $nuitees * $frais_forfait['NUI'];
    $total_km = $km * $frais_forfait['KM'];
    $total_montant_valide = $total_repas + $total_nuitees + $total_km + $total_etape;

    // Récupérer le prochain ID pour fiche_frais
    $sql = "SELECT MAX(FFR_ID) AS max_id FROM fiche_frais"; 
    $stmt = $cnxBDD->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $ID_FFR = $row['max_id'] + 1;

    // Insérer la fiche de frais
    $sql = "INSERT INTO fiche_frais (FFR_ID, VIS_ID, ETA_ID, FFR_ANNEE, FFR_MOIS, FFR_MONTANT_VALIDE, FFR_NB_JUSTIFICATIFS, FFR_DATE_MODIF) VALUES (:FFR_ID, :VIS_ID, 'CR', :FFR_ANNEE, :FFR_MOIS, :FFR_MONTANT_VALIDE, :FFR_NB_JUSTIFICATIFS, :FFR_DATE_MODIF)";
    $stmt = $cnxBDD->prepare($sql);
    $stmt->execute([
        ':FFR_ID' => $ID_FFR,
        ':VIS_ID' => $VIS_ID,
        ':FFR_ANNEE' => $year,
        ':FFR_MOIS' => $month,
        ':FFR_MONTANT_VALIDE' => $total_montant_valide,
        ':FFR_NB_JUSTIFICATIFS' => $etape,
        ':FFR_DATE_MODIF' => $date_modif,
    ]);

    // Insérer les lignes de frais forfait
    $sql = "INSERT INTO ligne_frais_forfait (FFR_ID, FOR_ID, LIG_QTE) VALUES (:FFR_ID, :FOR_ID, :LIG_QTE)";
    $stmt = $cnxBDD->prepare($sql);

    $stmt->execute([':FFR_ID' => $ID_FFR, ':FOR_ID' => 'REP', ':LIG_QTE' => $repas]);
    $stmt->execute([':FFR_ID' => $ID_FFR, ':FOR_ID' => 'KM', ':LIG_QTE' => $km]);
    $stmt->execute([':FFR_ID' => $ID_FFR, ':FOR_ID' => 'NUI', ':LIG_QTE' => $nuitees]);
    $stmt->execute([':FFR_ID' => $ID_FFR, ':FOR_ID' => 'ETP', ':LIG_QTE' => $etape]);

    // Fermer la connexion en la définissant à null
    $cnxBDD = null;

    http_response_code(200);
    echo json_encode(['succes' => true, 'message' => 'Fiche de frais insérée avec succès']);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>