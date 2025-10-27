<?php
// ===== Headers / CORS =====
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ===== Config / DB =====
require 'config.php';

// ===== Read JSON body (once) =====
$rawBody = file_get_contents('php://input');
$input = [];
if ($rawBody !== '' && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $tmp = json_decode($rawBody, true);
    if (is_array($tmp)) $input = $tmp;
}

// ===== API KEY =====
$API_KEY = "12345";
$key = $_GET['key'] ?? $_POST['key'] ?? ($input['key'] ?? null);
$hdrAuth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$hdrApi  = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!$key && $hdrApi) $key = $hdrApi;
if (!$key && stripos($hdrAuth, 'Bearer ') === 0) $key = substr($hdrAuth, 7);
if (!$key && stripos($hdrAuth, 'Api-Key ') === 0) $key = substr($hdrAuth, 8);

if ($key !== $API_KEY) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Clé API non valide...."]);
    exit;
}

// ===== Router =====
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        case 'GET':
            // id => одна запись, иначе — список
            if (isset($_GET['id'])) {
                $id = (int) $_GET['id'];
                $stmt = $pdo->prepare("
                    SELECT id, nom, description, date_creation, prix,
                           latitude, longitude,
                           contact_name, contact_email, photo,
                           adresse, ville, code_postal
                    FROM concessions
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    echo json_encode(["success" => true, "data" => $row]);
                } else {
                    http_response_code(404);
                    echo json_encode(["success" => false, "error" => "concession non trouvée"]);
                }
            } else {
                $stmt = $pdo->query("
                    SELECT id, nom, description, date_creation, prix,
                           latitude, longitude,
                           contact_name, contact_email, photo,
                           adresse, ville, code_postal
                    FROM concessions
                    ORDER BY id DESC
                ");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(["success" => true, "data" => $rows]);
            }
            break;

        case 'POST':
            // Принимаем JSON. Делаем latitude/longitude опциональными (могут быть null)
            $required = ['nom', 'description', 'prix', 'contact_name', 'contact_email'];
            foreach ($required as $k) {
                if (!isset($input[$k])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "error" => "Champs manquants: $k"]);
                    exit;
                }
            }

            $nom           = trim((string)$input['nom']);
            $description   = trim((string)$input['description']);
            $date_creation = isset($input['date_creation']) ? (string)$input['date_creation'] : null;
            $prix          = (int)$input['prix'];
            $latitude      = isset($input['latitude'])  ? (float)$input['latitude']  : null;
            $longitude     = isset($input['longitude']) ? (float)$input['longitude'] : null;
            $contact_name  = trim((string)$input['contact_name']);
            $contact_email = trim((string)$input['contact_email']);
            $photo         = isset($input['photo']) ? trim((string)$input['photo']) : null;

            // адресные поля — опционально, но если пришли, запишем
            $adresse       = isset($input['adresse'])     ? trim((string)$input['adresse'])     : null;
            $ville         = isset($input['ville'])       ? trim((string)$input['ville'])       : null;
            $code_postal   = isset($input['code_postal']) ? trim((string)$input['code_postal']) : null;

            if ($nom === '' || $prix <= 0 || !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(["success" => false, "error" => "Données non valides"]);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO concessions
                  (nom, description, date_creation, prix,
                   latitude, longitude,
                   contact_name, contact_email, photo,
                   adresse, ville, code_postal)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            ");
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


            echo json_encode([
                "success" => true,
                "message" => "Concession ajoutée avec succès",
                "id" => (int)$pdo->lastInsertId()
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["success" => false, "error" => "Méthode non autorisée"]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Erreur serveur: " . $e->getMessage()]);
}
