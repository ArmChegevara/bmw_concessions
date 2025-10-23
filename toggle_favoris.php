<?php
require 'config.php';
require 'auth.php';

// Vérifier que l'utilisateur est connecté en tant que client
if (!estClient()) {
    header("Location: index.php");
    exit;
}

$id_article = $_GET['id'] ?? null;

if (!$id_article) {
    header("Location: index.php");
    exit;
}

// Vérifier que l'article existe
$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
$stmt->execute([$id_article]);
if (!$stmt->fetch()) {
    header("Location: index.php");
    exit;
}

// Exemple : gestion du cookie favoris
$favoris = [];

// Si un cookie existe déjà
if (isset($_COOKIE['favoris']) && !empty($_COOKIE['favoris'])) {
    $favoris = explode(',', $_COOKIE['favoris']); 
}

// Id de l’article actuel
$id_article = $_GET['id'] ?? null;

if ($id_article) {
    if (in_array($id_article, $favoris)) {
        // Retirer
        $favoris = array_diff($favoris, [$id_article]);
    } else {
        // Ajouter
        $favoris[] = $id_article;
    }

    // Nettoyer et retransformer en chaîne
    $favoris = array_unique(array_filter($favoris));
    $favoris_str = implode(',', $favoris); 

    setcookie('favoris', $favoris_str, time() + (3600*24));

    header('Location: index.php');
    exit;
}