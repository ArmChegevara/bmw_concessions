<?php
// auth.php — helper for auth & roles
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php'; // $pdo

// Проверить, вошёл ли пользователь
function estConnecte(): bool
{
    return !empty($_SESSION['user_id']);
}

// Получить данные текущего пользователя (ассоц. массив) или null
function getUtilisateur(): ?array
{
    global $pdo;
    if (!estConnecte()) return null;
    $id = (int)($_SESSION['user_id']);
    $stmt = $pdo->prepare("SELECT id, username, email, role, prenom FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    return $u ?: null;
}

// Проверить роль admin
function estAdmin(): bool
{
    $u = getUtilisateur();
    return $u && ($u['role'] === 'admin');
}

// Проверить роль vendeur (в твоём проекте эквивалент admin)
function estVendeur(): bool
{
    $u = getUtilisateur();
    return $u && in_array($u['role'], ['vendeur', 'admin'], true);
}
function estClient(): bool
{
    $u = getUtilisateur();
    return $u && $u['role'] === 'client';
}



// Авторизация по id
function login_by_id(int $id): void
{
    // регистрируем в сессии
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}

// Выход
function logout(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
