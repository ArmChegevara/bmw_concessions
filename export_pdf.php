<?php
// 🧩 Показ ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ Подключения
require('./fpdf/fpdf.php');
require('./config.php'); // теперь правильный путь

// ✅ Получаем ID из URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ✅ SQL-запрос
$stmt = $pdo->prepare("SELECT * FROM concessions WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Concession non trouvée !");
}

// ✅ Создание PDF
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Fiche Concession BMW'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Nom : " . $data['nom']), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Description : " . $data['description']), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Date : " . ($data['date'] ?? '—')), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Prix : " . $data['prix'] . " €"), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Contact : " . $data['contact_name']), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Email : " . $data['contact_email']), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Latitude : " . $data['latitude']), 0, 1);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Longitude : " . $data['longitude']), 0, 1);

$pdf->Ln(10);

if (!empty($data['photo'])) {
    $imagePath = './uploads/' . $data['photo'];
    if (file_exists($imagePath)) {
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $pdf->Image($imagePath, 60, $pdf->GetY(), 90);
        } else {
            // ⚠️ Вместо ошибки — просто текстовое предупреждение
            $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "⚠️ Image non supportée (" . $ext . ")"), 0, 1);
        }
    } else {
        $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', "Image non trouvée"), 0, 1);
    }
}


// Фото, если есть
if (!empty($data['photo'])) {
    $imagePath = __DIR__ . '/uploads/' . $data['photo'];
    if (file_exists($imagePath)) {
        $pdf->Image($imagePath, 60, $pdf->GetY(), 90);
    }
}

// ✅ Отобразить PDF в браузере
$pdf->Output('I', 'Concession_' . $data['nom'] . '.pdf');
