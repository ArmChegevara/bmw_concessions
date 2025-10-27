<?php
// register.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '' || $email === '' || $password === '') $errors[] = 'Все поля обязательны (кроме prénom).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email неверный.';
    if ($password !== $password2) $errors[] = 'Пароли не совпадают.';
    if (strlen($password) < 8) $errors[] = 'Пароль должен быть минимум 8 символов.';

    // проверка уникальности
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) $errors[] = 'Пользователь с таким логином или email уже существует.';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, prenom) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hash, $prenom ?: null]);
        $newId = (int)$pdo->lastInsertId();
        // логин сразу после регистрации
        login_by_id($newId);
        $success = true;
        header('Location: index.php');
        exit;
    }
}
?>

<?php require_once 'header.php'; ?>
<div class="container py-5">
    <h1>Créer un compte</h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
        </div>
    <?php endif; ?>
    <form method="post" class="row g-3" novalidate>
        <div class="col-md-6">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Prénom</label>
            <input class="form-control" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Пароль</label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Повторите пароль</label>
            <input class="form-control" type="password" name="password2" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Зарегистрироваться</button>
            <a class="btn btn-link" href="login.php">Уже есть аккаунт? Войти</a>
        </div>
    </form>
</div>