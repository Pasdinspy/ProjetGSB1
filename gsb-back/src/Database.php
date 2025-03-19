<?php

namespace Debian\GsbBack;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database {
    private $connection = null;

    public function __construct() {
        try {
            // Chargement des variables d'environnement depuis la racine du projet
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $this->connection = new PDO(
                "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}