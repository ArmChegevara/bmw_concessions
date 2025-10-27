<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  exit('ID invalide');
}

$stmt = $pdo->prepare("
  SELECT id, nom, description, date_creation, prix,
         adresse, ville, code_postal,
         latitude, longitude,
         contact_name, contact_email, photo
  FROM concessions
  WHERE id = ?
");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$c) {
  http_response_code(404);
  exit('Concession introuvable');
}

require __DIR__ . '/header.php';
?>
<main class="container py-4">
  <h1><?= htmlspecialchars($c['nom']) ?></h1>

  <div class="row g-4">
    <div class="col-md-4">
      <?php if (!empty($c['photo'])): ?>
        <img src="uploads/<?= htmlspecialchars($c['photo']) ?>" class="img-fluid rounded border">
      <?php else: ?>
        <div class="text-muted">Aucune photo</div>
      <?php endif; ?>

      <div class="mt-3">
        <h5 class="mb-1">Adresse</h5>
        <p class="mb-0">
          <?= htmlspecialchars($c['adresse'] ?? '-') ?><br>
          <?= htmlspecialchars(($c['code_postal'] ?? '') . ' ' . ($c['ville'] ?? '')) ?>
        </p>
        <?php if (empty($c['latitude']) || empty($c['longitude'])): ?>
          <span class="badge bg-warning text-dark mt-2">Sans géolocalisation</span>
        <?php endif; ?>
      </div>

      <div class="mt-3">
        <h5 class="mb-1">Contact</h5>
        <p class="mb-0">
          <?= htmlspecialchars($c['contact_name'] ?? '-') ?><br>
          <a href="mailto:<?= htmlspecialchars($c['contact_email'] ?? '') ?>">
            <?= htmlspecialchars($c['contact_email'] ?? '') ?>
          </a>
        </p>
      </div>

      <div class="mt-3">
        <h5 class="mb-1">Tarif</h5>
        <p class="mb-0">
          <?= is_numeric($c['prix']) ? number_format((float)$c['prix'], 0, ',', ' ') . ' €' : '-' ?>
        </p>
      </div>

      <div class="mt-3">
        <small class="text-muted">Ouverture: <?= htmlspecialchars($c['date_creation'] ?? '') ?></small>
      </div>
    </div>

    <div class="col-md-8">
      <p><?= nl2br(htmlspecialchars($c['description'])) ?></p>
      <div id="map" style="height:400px" class="border rounded"></div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <a class="btn btn-secondary" href="index.php">← Retour</a>
    <a class="btn btn-outline-dark" href="export_pdf.php?id=<?= (int)$c['id'] ?>">Exporter PDF</a>
    <?php if (function_exists('estVendeur') && estVendeur()): ?>
      <a class="btn btn-primary" href="edit.php?id=<?= (int)$c['id'] ?>">Modifier</a>
      <a class="btn btn-danger" onclick="return confirm('Supprimer ?')" href="delete.php?id=<?= (int)$c['id'] ?>">Supprimer</a>
    <?php endif; ?>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const hasCoords = Number.isFinite(parseFloat('<?= (string)$c['latitude'] ?>')) &&
      Number.isFinite(parseFloat('<?= (string)$c['longitude'] ?>'));

    const center = hasCoords ?
      [parseFloat('<?= (string)$c['latitude'] ?>'), parseFloat('<?= (string)$c['longitude'] ?>')] :
      [46.5, 2.3]; // центр Франции

    const zoom = hasCoords ? 13 : 5;

    const map = L.map('map').setView(center, zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(map);

    if (hasCoords) {
      L.marker(center).addTo(map).bindPopup(
        `<b><?= htmlspecialchars($c['nom']) ?></b><br>` +
        `<?= htmlspecialchars($c['adresse'] ?? '') ?><br>` +
        `<?= htmlspecialchars(($c['code_postal'] ?? '') . ' ' . ($c['ville'] ?? '')) ?>`
      );
    }
  });
</script>