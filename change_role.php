<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (!estAdmin()) {
    http_response_code(403);
    exit('Accès refusé.');
}

$id = (int)($_GET['id'] ?? 0);
$newRole = ($_GET['role'] ?? '') === 'admin' ? 'admin' : 'user';

if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $id]);
}

header('Location: admin_dashboard.php');
exit;
