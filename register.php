<?php

require 'config.php';
//importer mon fichier auth.php qui contient toutes mes fonctions de connexion
require 'auth.php';



 //Si user connecté -> rediriger 
 if (estConnecte()){
    header("Location: index.php");
    exit;
 }

 $error ='';
 $success = '';

//verifier la methode d'envoi
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $role='vendeur';


   if($email && $password && $nom && $prenom) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? ");
    $stmt->execute([$email]);

    //on verifie si le mail existe deja 
    if ($stmt->fetch()) {
        $error = "Cet email est déjà utilisé";
    }

    //sinon on enregistre l'user
    else{

        //on vient hasher le mdp avec la fonction passwordhash
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO utilisateurs(email, mot_de_passe, nom, prenom) VALUES (?,?,?,?)");
        if($stmt->execute([$email, $password_hash, $nom, $prenom])){
            $success = "Inscription réussi - Bienvenue !";
        }
        else{
            $error = "Erreur lors de l'inscription";
        }
    }
   }
   else {
    $error = "Veuillez remplir tous les champs";
   }
}

//si email + password renseigné -> faire la connexion (Select user)

require("header.php");
?>

<div class="my-5 p-5 row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <h2 class="test-center mb-4">S'inscrire</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?> 

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>    

    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control">
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control">
        </div>

        <?php if (estVendeur()): ?>
         <div class="mb-3">
            <label for="role" class="form-label">Type de compte</label>
            <select name="role" id="role" class="form-select" required>
                <option value ="client">Client</option>
                <option value ="vendeur">Vendeur</option>
            </select>    
        </div>
        <?php endif;?>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Se connecter</button>    
        </div>
    </form>
    </div>
</div>