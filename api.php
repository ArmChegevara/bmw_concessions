<?php

//EN TETE HTTP
//indiquer la rép au format JSON (+ table de caractere)
header("Content-Type: application/json; charset=utf-8");
//autoriser les requetes externes
header("Access-Control-Allow-Origin: *");
//on autorise que le GET
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

//VERIF CLE API
$API_KEY = "12345";

$key = $_GET['key'] ??  $_POST['key'] ?? null;

if ($key !== $API_KEY){
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Clé API non valide...."]);
    exit;
}


//connexion à la BDD
require 'config.php';


//Exemple A : http://localhost:8888/071025/api.php?key=12345 -> tous les articles
//Exemple B : http://localhost:8888/071025/api.php?key=12345$id=2 -> l'article avec id 2
//Exemple C : POST ajout d'article http://localhost:8888/071025/api.php?key=12345 + infos en post dans header


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        //Exemple B : un seul article
try{
    if(isset($_GET['id'])){
        $id = (int) $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if($article){
            echo json_encode([
                "success" => true,
                "data" => $article
            ]);
        }
        else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "error" => "article non trouvé"
            ]);
        }

    } else {
        //Exemple A : tous les article
        $stmt = $pdo->query("SELECT * FROM articles");
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //encodage en json du res
        echo json_encode([
                "success" => true,
                "data" => $articles
            ]);
        
    }
}

catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
                "success" => false,
                "error" => "erreur avec la BDD : " . $e->getMessage()
            ]);
}
        break;

    case 'POST':
        //try 
        try {
            
        //lecture de la requete
        $input = json_decode(file_get_contents('php://input'), true, JSON_THROW_ON_ERROR);


        //verifier que les champs sont ok et bien envoyés
        if(!isset($input['titre'], $input['description'], $input['prix'])){
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Champs manquants"
            ]); 
            exit; 
        }

        //securise les champs + valider les champs (ex prix > 0)
        $titre = trim($input['titre']);
        $description = trim($input['description']);
        $prix = (float) $input['prix'];
        $image = null;

        if($titre ==='' || $prix <= 0){
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Titre ou prix non valide"
            ]); 
            exit; 
        }


        //Inserer les donnéers de manire securisé INSERT
      $stmt = $pdo->prepare("INSERT INTO articles (titre, description, prix, image) VALUES(?, ?, ?, ?)");
      $stmt->execute([$titre, $description, $prix, $image]);

        //renvoyer une reponse
        echo json_encode([
            "success" => true,
            "message" => "Article ajouté avec succès",
            "id" => $pdo->lastInsertId()
        ]);

        } 

        //catch
        catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "erreur avec la BDD : " . $e->getMessage()
            ]);
}

        break;
    

        //empecher tout autre methode (DELETE, PUT,PATCH...)
    default:
        //methode nn autorisée
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Méthode non autorisée"]);
        break;
}

