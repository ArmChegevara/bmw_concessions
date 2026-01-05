<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';   // <-- IMPORTANT : estAdmin()/estVendeur se trouvent ici()

// Seuls l'administrateur ou le fournisseur peuvent supprimer
if (!function_exists('estAdmin') || !function_exists('estVendeur') || (!estAdmin() && !estVendeur())) {
    http_response_code(403);
    exit('Accès refusé.');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('ID manquant');
}

// CSRF token pour la confirmation POST
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        exit('CSRF');
    }
    $stmt = $pdo->prepare("DELETE FROM concessions WHERE id=?");
    $stmt->execute([$id]);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Concession supprimée'];
    header('Location: index.php');
    exit;
}

// GET — formulaire de confirmation
require __DIR__ . '/header.php';
?>
<main class="container py-5">
    <h1>Confirmer la suppression</h1>
    <p>Voulez-vous vraiment supprimer la concession #<?= (int)$id ?> ?</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <button class="btn btn-danger" type="submit">Supprimer</button>
        <a class="btn btn-secondary" href="concession.php?id=<?= (int)$id ?>">Annuler</a>
    </form>
</main>
