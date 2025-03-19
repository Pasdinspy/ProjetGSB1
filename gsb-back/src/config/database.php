<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Chercher le .env dans les différents emplacements possibles
        $envPaths = [
            __DIR__ . '/../../.env',               // Dans gsb-back
            __DIR__ . '/../../../.env',            // À la racine du projet
            getenv('HOME') . '/ProjetGsb1/.env'    // Chemin absolu
        ];

        $envFound = false;
        foreach ($envPaths as $envFile) {
            if (file_exists($envFile)) {
                error_log("Fichier .env trouvé : " . $envFile);
                $env = parse_ini_file($envFile);
                if ($env !== false) {
                    $envFound = true;
                    break;
                }
            }
        }

        if (!$envFound) {
            error_log("Aucun fichier .env trouvé dans les chemins : " . implode(', ', $envPaths));
            throw new Exception("Configuration de la base de données manquante");
        }

        $this->host = $env['DB_HOST'] ?? null;
        $this->db_name = $env['DB_NAME'] ?? null;
        $this->username = $env['DB_USER'] ?? null;
        $this->password = $env['DB_PASS'] ?? null;

        if (!$this->host || !$this->db_name || !$this->username || !$this->password) {
            error_log("Configuration incomplète: " . print_r($env, true));
            throw new Exception("Configuration de la base de données incomplète");
        }

        error_log("Configuration BD chargée: host={$this->host}, db={$this->db_name}, user={$this->username}");
    }

    public function getConnection() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            error_log("Tentative de connexion avec DSN: {$dsn}");

            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            error_log("Connexion à la base de données réussie");
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Erreur PDO: " . $e->getMessage());
            throw new Exception("Impossible de se connecter à la base de données: " . $e->getMessage());
        }
    }
}