<?php
require 'config.php';
require_once 'auth.php';

//Verifier que l'user est connecté + qu'il a les droit (est vendeur)
if (!estConnecte() || !estVendeur()) {
header('Location: index.php');
exit;
}

//verifier que id est valid, numeric, le transformer en entier sinon
if (!isset($_GET['id']) || !is_numeric($_GET['id'])){
    echo "Erreur : id manquant ou invalide";
    exit;
}
//recuperer l'ID et le forcer en entier
$id = (int) $_GET['id'];

//Verifier que l'article existe avec un SELECT
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
//on le stock dans une variable
$article = $stmt->fetch();

if(!$article){
    echo "Article inexistant";
    exit;
}

//TODO : recuperer l'img et la suppr du serveur 
//si elle existe

//Requete de suppression de l'article
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$stmt->execute([$id]);

header("Location : index.php");
exit;