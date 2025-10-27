<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, nom, description, prix, latitude, longitude FROM concessions";
$params = [];
if ($q !== '') {
    $sql .= " WHERE nom LIKE ? OR description LIKE ?";
    $like = "%$q%";
    $params = [$like, $like];
}
$sql .= " ORDER BY id DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
