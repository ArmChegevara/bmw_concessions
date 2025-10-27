<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php'; // estVendeur(), getUtilisateur()

// доступ только админу/продавцу
if (!function_exists('estVendeur') || !estVendeur()) {
  http_response_code(403);
  exit('<div class="container py-5 alert alert-danger">Accès refusé</div>');
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_ok($t)
{
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t ?? '');
}

/** Универсальный GET JSON */
function http_get_json(string $url, string $ua, ?int &$code = null)
{
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_TIMEOUT        => 10,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_USERAGENT      => $ua,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body !== false && $code === 200) {
      $j = json_decode($body, true);
      return is_array($j) ? $j : null;
    }
    return null;
  }
  $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 10, 'header' => "User-Agent: $ua\r\n"]]);
  $body = @file_get_contents($url, false, $ctx);
  $code = 0;
  if (isset($http_response_header) && preg_match('#HTTP/\S+\s+(\d{3})#', $http_response_header[0], $m)) $code = (int)$m[1];
  if ($body !== false && $code === 200) {
    $j = json_decode($body, true);
    return is_array($j) ? $j : null;
  }
  return null;
}
/** Геокодинг: Nominatim -> Photon */
function geocode_address(string $full, ?string &$dbg = null): ?array
{
  $dbgLines = [];
  $q = trim($full);
  if ($q === '') {
    $dbg = 'empty';
    return null;
  }
  $UA = 'BMW-France-Concessions/1.0 (your-email@domain.tld)'; // <-- укажи реальный e-mail!

  $url1 = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' . urlencode($q);
  $j1   = http_get_json($url1, $UA, $c1);
  $dbgLines[] = "nom:$c1";
  if (is_array($j1) && !empty($j1[0]['lat']) && !empty($j1[0]['lon'])) {
    $dbg = implode('|', $dbgLines);
    return ['lat' => (float)$j1[0]['lat'], 'lng' => (float)$j1[0]['lon']];
  }
  $url2 = 'https://photon.komoot.io/api/?q=' . urlencode($q) . '&limit=1';
  $j2   = http_get_json($url2, $UA, $c2);
  $dbgLines[] = "photon:$c2";
  if (isset($j2['features'][0]['geometry']['coordinates'])) {
    $coord = $j2['features'][0]['geometry']['coordinates']; // [lng, lat]
    $dbg = implode('|', $dbgLines);
    return ['lat' => (float)$coord[1], 'lng' => (float)$coord[0]];
  }
  $dbg = implode('|', $dbgLines);
  return null;
}

// читаем запись (с адресом!)
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, nom, description, date_creation, prix,
                              adresse, ville, code_postal,
                              latitude, longitude,
                              contact_name, contact_email, photo
                       FROM concessions WHERE id=?");
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) {
  http_response_code(404);
  exit('Concession introuvable');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_ok($_POST['csrf_token'] ?? '')) $errors[] = 'CSRF invalide';

  $nom           = trim($_POST['nom'] ?? '');
  $description   = trim($_POST['description'] ?? '');
  $date_creation = trim($_POST['date_creation'] ?? '');
  $prix          = (int)($_POST['prix'] ?? 0);

  $adresse       = trim($_POST['adresse'] ?? '');
  $ville         = trim($_POST['ville'] ?? '');
  $code_postal   = trim($_POST['code_postal'] ?? '');

  $contact_name  = trim($_POST['contact_name'] ?? '');
  $contact_email = trim($_POST['contact_email'] ?? '');

  if ($nom === '')                                  $errors[] = 'Nom requis';
  if ($description === '')                          $errors[] = 'Description requise';
  if ($prix <= 0)                                   $errors[] = 'Prix > 0 requis';
  if ($adresse === '' || $ville === '' || $code_postal === '') $errors[] = 'Adresse/Ville/Code postal requis';
  if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL))       $errors[] = 'Email invalide';
  if ($date_creation !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_creation)) $errors[] = 'Date invalide (YYYY-MM-DD)';

  // фото (опционально)
  $photoName = $c['photo'];
  if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
      $errors[] = "Image trop lourde (> 5 Mo).";
    } else {
      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
      $inf = @getimagesize($_FILES['photo']['tmp_name']);
      $mime = $inf['mime'] ?? null;
      if (!isset($allowed[$mime])) {
        $errors[] = "Format image non supporté (JPG/PNG/WEBP).";
      } else {
        $dir = __DIR__ . '/uploads';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $ext = $allowed[$mime];
        $photoName = 'c-' . $id . '-' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . '/' . $photoName)) {
          $errors[] = "Erreur lors de l’enregistrement de l’image.";
        }
      }
    }
  }

  // координаты: пересчитать по чеку, иначе оставить как были
  $latitude  = $c['latitude']  !== null ? (float)$c['latitude']  : null;
  $longitude = $c['longitude'] !== null ? (float)$c['longitude'] : null;
  $recalc = !empty($_POST['recalc_geo']);
  if ($recalc && !$errors) {
    $full = trim($adresse . ', ' . $code_postal . ' ' . $ville . ', France');
    $dbg = '';
    $pt = geocode_address($full, $dbg);
    if ($pt) {
      $latitude  = $pt['lat'];
      $longitude = $pt['lng'];
    } else {
      // мягко: не блокируем сохранение
      error_log('GEOCODE WARN [' . $full . '] => ' . $dbg);
      // можно оставить старые координаты
    }
  }

  if (!$errors) {
    $stmt = $pdo->prepare("
      UPDATE concessions
         SET nom=?,
             description=?,
             date_creation=?,
             prix=?,
             adresse=?,
             ville=?,
             code_postal=?,
             latitude=?,
             longitude=?,
             contact_name=?,
             contact_email=?,
             photo=?
       WHERE id=?
    ");
    $stmt->execute([
      $nom,
      $description,
      ($date_creation ?: null),
      $prix,
      $adresse,
      $ville,
      $code_postal,
      $latitude,
      $longitude,
      $contact_name,
      $contact_email,
      $photoName,
      $id
    ]);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Concession mise à jour'];
    header('Location: concession.php?id=' . $id);
    exit;
  }
}

require_once __DIR__ . '/header.php';
?>
<main class="container py-4">
  <h1>Modifier la concession</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <div class="col-md-6">
      <label class="form-label">Nom *</label>
      <input class="form-control" name="nom" value="<?= htmlspecialchars($c['nom']) ?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Date d’ouverture</label>
      <input class="form-control" type="date" name="date_creation" value="<?= htmlspecialchars($c['date_creation']) ?>">
    </div>

    <div class="col-12">
      <label class="form-label">Description *</label>
      <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($c['description']) ?></textarea>
    </div>

    <div class="col-md-4">
      <label class="form-label">Prix *</label>
      <input class="form-control" type="number" min="1" name="prix" value="<?= (int)$c['prix'] ?>" required>
    </div>

    <!-- Адрес -->
    <div class="col-md-6">
      <label class="form-label">Adresse *</label>
      <input class="form-control" name="adresse" value="<?= htmlspecialchars($c['adresse']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Ville *</label>
      <input class="form-control" name="ville" value="<?= htmlspecialchars($c['ville']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Code postal *</label>
      <input class="form-control" name="code_postal" value="<?= htmlspecialchars($c['code_postal']) ?>" required>
    </div>

    <div class="col-12">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="recalc_geo" id="recalc_geo">
        <label class="form-check-label" for="recalc_geo">
          Recalculer les coordonnées depuis l’adresse
        </label>
      </div>
      <small class="text-muted">
        Les coordonnées actuelles:
        <?= htmlspecialchars((string)$c['latitude']) ?> /
        <?= htmlspecialchars((string)$c['longitude']) ?>
      </small>
    </div>

    <div class="col-md-6">
      <label class="form-label">Nom du contact *</label>
      <input class="form-control" name="contact_name" value="<?= htmlspecialchars($c['contact_name']) ?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Email du contact *</label>
      <input class="form-control" type="email" name="contact_email" value="<?= htmlspecialchars($c['contact_email']) ?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Photo</label>
      <input class="form-control" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
      <?php if (!empty($c['photo'])): ?>
        <small class="text-muted d-block mt-1">Actuelle: <?= htmlspecialchars($c['photo']) ?></small>
      <?php endif; ?>
    </div>

    <div class="col-12">
      <button class="btn btn-primary" type="submit">Enregistrer</button>
      <a class="btn btn-secondary" href="concession.php?id=<?= (int)$c['id'] ?>">Annuler</a>
    </div>
  </form>
</main>