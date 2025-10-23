<?php
require 'config.php';
require 'header.php';

$favoris = [];
if (isset($_COOKIE['favoris']) && !empty($_COOKIE['favoris'])) {
    $favoris = explode(',', $_COOKIE['favoris']);
}

if (empty($favoris)) {
    echo "<p>Vous n'avez aucun favori.</p>";
} else {
    $ids = implode(',', array_map('intval', $favoris));
    $stmt = $pdo->query("SELECT * FROM articles WHERE id IN ($ids)");
    $articles = $stmt->fetchAll();

    foreach ($articles as $article) {
        echo "<div class= 'container p-5'><p>{$article['titre']} - {$article['prix']} €</p></div>";
    }
}
?>