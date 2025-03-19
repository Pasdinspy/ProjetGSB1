<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Debian\GsbBack\Controllers\LoginController;

// Activation des erreurs PHP pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

$loginController = new LoginController();
$loginController->handleLogin();