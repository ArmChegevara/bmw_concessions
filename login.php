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

//verifier la methode d'envoi
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

   if($email && $password) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    //oon compare $password qui est en clair depuis le $POST
    //avec le mdp encrypté en base $user['motdepasse]
    //grace a password verify
    if($user && password_verify($password, $user['mot_de_passe'])){
        connecterUtilisateur($user);
        header("Location: index.php");
        exit;
    }
    else{
        $error = "Email ou mot de passe incorrect";
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
        <h2 class="test-center mb-4">Connexion</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
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
            <button type="submit" class="btn btn-primary">Se connecter</button>    
        </div>
    </form>
    </div>
</div>