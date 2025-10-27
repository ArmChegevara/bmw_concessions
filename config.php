<?php
// config.php — Connexion à la base BMW France

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Informations de connexion
$host = 'localhost';
$dbname = 'bmw_france';
$user = 'root';
$password = 'root';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error'   => 'Erreur de connexion à la base : ' . $e->getMessage()
    ]));
}
