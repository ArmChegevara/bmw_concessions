<?php
// add.php — créer une concession BMW (adresse -> auto lat/lng)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// доступ только админу/вендору
if (!function_exists('estVendeur') || !estVendeur()) {
    http_response_code(403);
    echo '<div class="container py-5"><div class="alert alert-danger">Accès refusé.</div></div>';
    exit;
}

// CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_check($t)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $t ?? '');
}

/** Универсальный GET JSON: сначала cURL, затем streams (openssl) */
function http_get_json(string $url, string $userAgent, ?int &$httpCode = null)
{
    // cURL
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => $userAgent,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body !== false && $httpCode === 200) {
            $json = json_decode($body, true);
            return is_array($json) ? $json : null;
        }
        return null;
    }
    // streams fallback
    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 10,
            'header'  => "User-Agent: $userAgent\r\n"
        ]
    ]);
    $body = @file_get_contents($url, false, $context);
    $httpCode = 0;
    if (isset($http_response_header) && preg_match('#HTTP/\S+\s+(\d{3})#', $http_response_header[0], $m)) {
        $httpCode = (int)$m[1];
    }
    if ($body !== false && $httpCode === 200) {
        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }
    return null;
}

/** Геокодинг: Nominatim -> Photon (fallback) */
function geocode_address(string $fullAddress, ?string &$debug = null): ?array
{
    $debugLines = [];
    $q = trim($fullAddress);
    if ($q === '') {
        $debug = 'Empty address';
        return null;
    }

    // ВАЖНО: укажи реальный email/UA
    $UA = 'BMW-France-Concessions/1.0 (your-real-email@domain.tld)';

    // 1) Nominatim
    $url1  = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' . urlencode($q);
    $data1 = http_get_json($url1, $UA, $code1);
    $debugLines[] = "Nominatim:$code1";

    if (is_array($data1) && !empty($data1[0]['lat']) && !empty($data1[0]['lon'])) {
        $debug = implode(' | ', $debugLines);
        return ['lat' => (float)$data1[0]['lat'], 'lng' => (float)$data1[0]['lon']];
    }

    // 2) Photon fallback
    $url2  = 'https://photon.komoot.io/api/?q=' . urlencode($q) . '&limit=1';
    $data2 = http_get_json($url2, $UA, $code2);
    $debugLines[] = "Photon:$code2";

    if (isset($data2['features'][0]['geometry']['coordinates'])) {
        $coords = $data2['features'][0]['geometry']['coordinates']; // [lng, lat]
        $debug = implode(' | ', $debugLines);
        return ['lat' => (float)$coords[1], 'lng' => (float)$coords[0]];
    }

    $debug = implode(' | ', $debugLines);
    return null;
}

$errors = [];
$photoName = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) CSRF
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $errors[] = "Jeton CSRF invalide.";
    }

    // 2) Чтение и базовая валидация
    $nom           = trim($_POST['nom'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $date_creation = trim($_POST['date_creation'] ?? ''); // YYYY-MM-DD
    $prix          = (int)($_POST['prix'] ?? 0);
    $contact_name  = trim($_POST['contact_name'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');

    $adresse       = trim($_POST['adresse'] ?? '');
    $ville         = trim($_POST['ville'] ?? '');
    $code_postal   = trim($_POST['code_postal'] ?? '');

    if ($nom === '')                $errors[] = "Nom requis.";
    if ($description === '')        $errors[] = "Description requise.";
    if ($prix <= 0)                 $errors[] = "Prix doit être > 0.";
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email de contact invalide.";
    if ($adresse === '' || $ville === '' || $code_postal === '') $errors[] = "Adresse/Ville/Code postal requis.";
    if ($date_creation !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_creation)) {
        $errors[] = "Date (YYYY-MM-DD) invalide.";
    }

    // 3) Фото (опционально) — без fileinfo
    if (!empty($_FILES['photo']['name'])) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur de téléchargement de la photo.";
        } else {
            if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $errors[] = "Image trop lourde (> 5 Mo).";
            } else {
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                $imgInfo = @getimagesize($_FILES['photo']['tmp_name']); // массив или false
                $mime    = $imgInfo['mime'] ?? null;

                if (!isset($allowed[$mime])) {
                    $errors[] = "Format image non supporté (jpg/png/webp).";
                } else {
                    $dir = __DIR__ . '/uploads';
                    if (!is_dir($dir)) mkdir($dir, 0775, true);

                    $ext = $allowed[$mime];
                    $base = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_FILENAME)));
                    $photoName = $base . '-' . time() . '.' . $ext;

                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . '/' . $photoName)) {
                        $errors[] = "Impossible d’enregistrer la photo.";
                        $photoName = null;
                    }
                }
            }
        }
    }

    // 4) Геокодинг адреса -> lat/lng (если ошибок ещё нет)
    // 4) Геокодинг адреса -> lat/lng (мягкий: не блокируем добавление)
    $latitude = null;
    $longitude = null;

    if (!$errors) {
        $fullAddress = trim($adresse . ', ' . $code_postal . ' ' . $ville . ', France');
        $debugGeo = '';
        $point = geocode_address($fullAddress, $debugGeo); // если у тебя версия без $debug — просто geocode_address($fullAddress)

        if ($point) {
            $latitude  = $point['lat'];
            $longitude = $point['lng'];
        } else {
            // логируем, но НЕ добавляем в $errors
            error_log('GEOCODE WARN [' . $fullAddress . '] => aucun résultat | ' . ($debugGeo ?? ''));
            // latitude/longitude останутся NULL — запись всё равно пойдёт в INSERT
        }
    }


    // 5) INSERT
    if (!$errors) {
        $sql = "INSERT INTO concessions
              (nom, description, date_creation, prix,
               latitude, longitude,
               contact_name, contact_email, photo,
               adresse, ville, code_postal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nom,
            $description,
            ($date_creation ?: null),
            $prix,
            $latitude,
            $longitude,
            $contact_name,
            $contact_email,
            $photoName,
            $adresse,
            $ville,
            $code_postal,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Concession ajoutée (#" . $pdo->lastInsertId() . ")."];
        header('Location: index.php');
        exit;
    }
}

require_once __DIR__ . '/header.php';
?>
<main role="main" class="container py-5">

    <?php if (!empty($_SESSION['flash'])): ?>
        <?php $f = $_SESSION['flash'];
        unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
    <?php endif; ?>

    <h1 class="mb-4">Ajouter une concession BMW</h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="col-md-6">
            <label class="form-label">Nom du concessionnaire *</label>
            <input type="text" name="nom" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Date d’ouverture</label>
            <input type="date" name="date_creation" class="form-control">
        </div>

        <div class="col-12">
            <label class="form-label">Description / Services *</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Prix (service standard) *</label>
            <input type="number" name="prix" min="1" class="form-control" required>
        </div>

        <!-- Адрес (без lat/lng в форме) -->
        <div class="col-md-6">
            <label class="form-label">Adresse *</label>
            <input type="text" name="adresse" class="form-control" placeholder="89 Rue de ..." required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Ville *</label>
            <input type="text" name="ville" class="form-control" placeholder="Reims" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Code postal *</label>
            <input type="text" name="code_postal" class="form-control" placeholder="51100" required>
        </div>
        <div class="col-12">
            <small class="text-muted">Les coordonnées seront remplies automatiquement à partir de l’adresse.</small>
        </div>

        <div class="col-md-6">
            <label class="form-label">Nom du contact *</label>
            <input type="text" name="contact_name" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email du contact *</label>
            <input type="email" name="contact_email" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Photo / Logo (jpg/png/webp)</label>
            <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="form-control">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</main>