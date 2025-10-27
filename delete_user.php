<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (!estAdmin() && !estVendeur()) {
    http_response_code(403);
    exit('Accès refusé.');
}


$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) exit('ID invalide.');

// нельзя удалить самого себя
if ($id === (int)$_SESSION['user_id']) {
    exit('<div class="container py-5 alert alert-warning">Impossible de supprimer votre propre compte.</div>');
}

// подтверждение (GET) или удаление (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: admin_dashboard.php');
    exit;
}

require_once __DIR__ . '/header.php';
?>
<div class="container py-5">
    <h1>Supprimer l’utilisateur #<?= $id ?></h1>
    <p>Voulez-vous vraiment supprimer cet utilisateur ? Cette action est irréversible.</p>
    <form method="post">
        <button class="btn btn-danger" type="submit">Supprimer</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>