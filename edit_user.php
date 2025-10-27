<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (!estAdmin()) {
    http_response_code(403);
    exit('<div class="container py-5 alert alert-danger">Accès refusé.</div>');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('ID invalide');
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT id, username, email, role, prenom FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    http_response_code(404);
    exit('Utilisateur non trouvé');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '') $errors[] = 'Nom d’utilisateur et email obligatoires.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';

    if (!$errors) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, prenom=?, email=?, role=?, password_hash=? WHERE id=?");
            $stmt->execute([$username, $prenom, $email, $role, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, prenom=?, email=?, role=? WHERE id=?");
            $stmt->execute([$username, $prenom, $email, $role, $id]);
        }
        header('Location: admin_dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/header.php';
?>
<div class="container py-5">
    <h1>Modifier utilisateur #<?= $user['id'] ?></h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nom d’utilisateur *</label>
            <input class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Prénom</label>
            <input class="form-control" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email *</label>
            <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Rôle</label>
            <select class="form-select" name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nouveau mot de passe (optionnel)</label>
            <input class="form-control" type="password" name="password" placeholder="Laisser vide pour ne pas changer">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Enregistrer les modifications</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>