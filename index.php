<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';


// Paramètre de recherche (par nom/description/adresse/ville/code_postal)
$q = trim($_GET['q'] ?? '');

$sql = "
SELECT
  id, nom, description, prix,
  adresse, ville, code_postal,
  latitude, longitude,
  contact_name, contact_email, photo
FROM concessions
";
$params = [];

if ($q !== '') {
  $sql .= " WHERE nom LIKE ? OR description LIKE ? OR adresse LIKE ? OR ville LIKE ? OR code_postal LIKE ?";
  $like = "%$q%";
  $params = [$like, $like, $like, $like, $like];
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$concessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/header.php';
?>
<main role="main" class="container py-5">
  <?php if (function_exists('estConnecte') && estConnecte()) : ?>
    <?php $user = getUtilisateur(); ?>
    <h1 class="mt-5"><strong>BMW Concessions</strong> — Bonjour <?= htmlspecialchars($user['prenom'] ?? ''); ?></h1>
  <?php else: ?>
    <h1 class="mt-5"><strong>BMW Concessions</strong></h1>
  <?php endif; ?>

  <!-- recherche -->
  <form class="row g-2 mb-3" method="get" action="index.php">
    <div class="col-md-6">
      <input name="q" class="form-control" placeholder="Rechercher (nom, adresse, ville...)"
        value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-6">
      <button class="btn btn-primary">Rechercher</button>
      <a class="btn btn-secondary" href="index.php">Réinitialiser</a>
      <?php if (function_exists('estVendeur') && estVendeur()): ?>
        <a class="btn btn-success" href="add.php">+ Ajouter</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="py-4">
    <table class="table table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>#ID</th>
          <th>Photo</th>
          <th>Nom du concessionnaire</th>
          <th>Description / Services</th>
          <th>Prix (std.)</th>
          <th>Adresse</th>
          <th>Contact</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($concessions as $c): ?>
          <tr>
            <th scope="row"><?= (int)$c['id'] ?></th>

            <td style="width:80px">
              <?php if (!empty($c['photo'])): ?>
                <img src="uploads/<?= htmlspecialchars($c['photo']) ?>"
                  alt="Photo <?= htmlspecialchars($c['nom']) ?>"
                  class="img-thumbnail object-fit-cover border rounded"
                  style="width:60px;height:60px;">
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($c['nom']) ?></td>

            <td><?= nl2br(htmlspecialchars($c['description'])) ?></td>

            <td><?= is_numeric($c['prix']) ? number_format((float)$c['prix'], 0, ',', ' ') . ' €' : '-' ?></td>

            <td>
              <?= htmlspecialchars($c['adresse'] ?? '-') ?><br>
              <?= htmlspecialchars($c['code_postal'] ?? '') ?>
              <?= htmlspecialchars($c['ville'] ?? '') ?>
            </td>

            <td>
              <?= htmlspecialchars($c['contact_name'] ?? '') ?><br>
              <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? '') ?>">
                <?= htmlspecialchars($c['contact_email'] ?? '') ?>
              </a>
            </td>

            <td class="text-end" style="white-space:nowrap;">
              <a class="btn btn-success btn-sm" href="concession.php?id=<?= (int)$c['id'] ?>">Voir</a>
              <?php if (function_exists('estVendeur') && estVendeur()): ?>
                <a class="btn btn-primary btn-sm" href="edit.php?id=<?= (int)$c['id'] ?>">Modif.</a>
                <a class="btn btn-danger btn-sm"
                  onclick="return confirm('Voulez-vous supprimer ?')"
                  href="delete.php?id=<?= (int)$c['id'] ?>">Suppr.</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Carte sous le tableau -->
  <div id="map" style="height:500px;"></div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([46.5, 2.3], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

   // Si vous utilisez api.php, il renvoie déjà adresse/ville/code_postal (nous avons corrigé cela)
    fetch('api.php?key=12345')
      .then(r => r.json())
      .then(payload => {
        const data = payload.data ?? payload;
        data.forEach(c => {
          const lat = parseFloat(c.latitude),
            lng = parseFloat(c.longitude);
          if (Number.isFinite(lat) && Number.isFinite(lng)) {
            L.marker([lat, lng]).addTo(map).bindPopup(`
            <b>${c.nom ?? ''}</b><br>
            ${c.adresse ?? ''}<br>
            ${(c.code_postal ?? '')} ${(c.ville ?? '')}
          `);
          }
        });
      })
      .catch(e => console.error('Erreur API:', e));
  });
</script>
