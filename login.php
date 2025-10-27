<?php
// login.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userOrEmail = trim($_POST['user'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($userOrEmail === '' || $password === '') $errors[] = 'Введите логин/email и пароль.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$userOrEmail, $userOrEmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($password, $row['password_hash'])) {
            $errors[] = 'Неправильный логин или пароль.';
        } else {
            login_by_id((int)$row['id']);
            header('Location: index.php');
            exit;
        }
    }
}
?>

<?php require_once 'header.php'; ?>
<div class="container py-5">
    <h1>Connexion</h1>
    <?php if ($errors): ?><div class="alert alert-danger">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div><?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Login ou Email</label>
            <input class="form-control" name="user" value="<?= htmlspecialchars($_POST['user'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Пароль</label>
            <input class="form-control" type="password" name="password">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Войти</button>
            <a class="btn btn-link" href="register.php">Créer un compte</a>
        </div>
    </form>
</div>