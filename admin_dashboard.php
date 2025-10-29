<?php
require_once __DIR__ . '/auth.php';
if (!estAdmin()) {
    http_response_code(403);
    exit('<div class="container py-5 alert alert-danger">Accès refusé.</div>');
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/header.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
&
// --- KPI
$totalUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAdmins  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$totalConcess = (int)$pdo->query("SELECT COUNT(*) FROM concessions")->fetchColumn();

// --- Users data
$users = $pdo->query("
    SELECT id, username, email, role, prenom, created_at
    FROM users
    ORDER BY id ASC
")->fetchAll(PDO::FETCH_ASSOC);

// --- Concessions filter
$q = trim($_GET['q'] ?? '');
$sql = "
    SELECT id, nom, description, prix,
           adresse, ville, code_postal,
           latitude, longitude,
           contact_name, contact_email, photo
    FROM concessions
";
$params = [];
if ($q !== '') {
    $sql .= " WHERE nom LIKE ? OR description LIKE ? OR adresse LIKE ? OR ville LIKE ? OR code_postal LIKE ?";
    $like   = "%$q%";
    $params = [$like, $like, $like, $like, $like];
}
$sql .= " ORDER BY id DESC";
$st = $pdo->prepare($sql);
$st->execute($params);
$concessions = $st->fetchAll(PDO::FETCH_ASSOC);

// --- CSRF для опасных действий (удаление/смена роли)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
?>
<div class="container py-5">

    <!-- KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="fw-bold">Utilisateurs</div>
                    <div class="display-6"><?= $totalUsers ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="fw-bold">Admins</div>
                    <div class="display-6"><?= $totalAdmins ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="fw-bold">Concessions</div>
                    <div class="display-6"><?= $totalConcess ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-concess" type="button" role="tab">Concessions</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-users" type="button" role="tab">Utilisateurs</button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- TAB: Concessions -->
        <div class="tab-pane fade show active" id="tab-concess" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <form class="d-flex gap-2" method="get" action="admin_dashboard.php">
                    <input class="form-control" name="q" placeholder="Rechercher (nom / adresse / ville / CP)"
                        value="<?= htmlspecialchars($q) ?>">
                    <button class="btn btn-primary">Rechercher</button>
                    <a class="btn btn-secondary" href="admin_dashboard.php">Reset</a>
                </form>
                <a href="add.php" class="btn btn-success">+ Ajouter une concession</a>
            </div>

            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#ID</th>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix</th>
                        <th>Adresse</th>
                        <th>Contact</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($concessions as $c): ?>
                        <tr>
                            <td><?= (int)$c['id'] ?></td>

                            <td style="width:70px">
                                <?php if (!empty($c['photo'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($c['photo']) ?>"
                                        alt="" width="60" height="60"
                                        style="object-fit:cover" class="rounded border">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($c['nom']) ?></td>

                            <td style="max-width:320px"><?= nl2br(htmlspecialchars($c['description'])) ?></td>

                            <td><?= is_numeric($c['prix']) ? number_format((float)$c['prix'], 0, ',', ' ') . ' €' : '-' ?></td>

                            <td>
                                <?= htmlspecialchars($c['adresse'] ?? '-') ?><br>
                                <?= htmlspecialchars(($c['code_postal'] ?? '') . ' ' . ($c['ville'] ?? '')) ?><br>
                                <small class="text-muted">
                                    <?php if (!empty($c['latitude']) && !empty($c['longitude'])): ?>
                                        <?= htmlspecialchars($c['latitude']) ?> / <?= htmlspecialchars($c['longitude']) ?>
                                    <?php else: ?>
                                        Sans géolocalisation
                                    <?php endif; ?>
                                </small>
                            </td>

                            <td>
                                <?= htmlspecialchars($c['contact_name'] ?? '') ?><br>
                                <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? '') ?>">
                                    <?= htmlspecialchars($c['contact_email'] ?? '') ?>
                                </a>
                            </td>

                            <td class="text-end" style="white-space:nowrap">
                                <a class="btn btn-sm btn-outline-secondary" href="concession.php?id=<?= (int)$c['id'] ?>">Voir</a>
                                <a class="btn btn-sm btn-primary" href="edit.php?id=<?= (int)$c['id'] ?>">Modifier</a>
                                <a class="btn btn-sm btn-danger"
                                    onclick="return confirm('Supprimer cette concession ?')"
                                    href="delete.php?id=<?= (int)$c['id'] ?>">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- TAB: Users -->
        <div class="tab-pane fade" id="tab-users" role="tabpanel">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom d’utilisateur</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Créé le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['prenom'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?= $u['role'] === 'admin'
                                    ? '<span class="badge bg-warning text-dark">Admin</span>'
                                    : '<span class="badge bg-secondary">User</span>' ?>
                            </td>
                            <td><?= htmlspecialchars($u['created_at']) ?></td>
                            <td class="text-end" style="white-space:nowrap">
                                <?php if ((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="edit_user.php?id=<?= (int)$u['id'] ?>">Modifier</a>

                                    <form method="post" action="delete_user.php?id=<?= (int)$u['id'] ?>"
                                        class="d-inline"
                                        onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                    </form>

                                    <?php if ($u['role'] === 'admin'): ?>
                                        <form method="post" action="change_role.php?id=<?= (int)$u['id'] ?>&role=user" class="d-inline"
                                            onsubmit="return confirm('Rétrograder cet admin ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <button class="btn btn-sm btn-outline-warning" type="submit">⬇ Rétrograder</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="change_role.php?id=<?= (int)$u['id'] ?>&role=admin" class="d-inline"
                                            onsubmit="return confirm('Promouvoir cet utilisateur ?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <button class="btn btn-sm btn-outline-success" type="submit">⬆ Définir admin</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <em>—</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>