
<?php
require 'config.php';
require 'header.php';

$error = '';
$success = '';


//Verifier que l'user est connecté + qu'il a les droit (est vendeur)
if (!estConnecte() || !estVendeur()) {
header('Location: index.php');
exit;
}


//on recupere toutes nos valeur saisie dans le form
if($_SERVER['REQUEST_METHOD'] === 'POST')
  {

  //Verification des champs requis 
  //isset : verif que les champs existent et dans $_POST
  //!empty : verif qu'ils pas vide
  //is_numeric : verif si c'est bien un nombre
  if (
    isset($_POST['titre'], $_POST['description'], $_POST['prix'])
    && !empty($_POST['titre'])
    && !empty($_POST['description'])
    && is_numeric($_POST['prix'])
  ){
    //recuperer les champs + nettoyage
      $titre = htmlspecialchars($_POST['titre']);
      $description = htmlspecialchars($_POST['description']);
      $prix = htmlspecialchars($_POST['prix']);
      $image = null;

      //Gestion upload d'image
      //verifications
      if (isset($_FILES['image']) && $_FILES['image']['error']=== UPLOAD_ERR_OK ){
        //nos regles de gestion du fichier
        //extension de fichier
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        //taille max
        $max_size = 5*1024*1024; //5Mo

        //recuperer le type MIME du fichier
        $file_type = $_FILES['image']['type'];
        //recuperer taille du fichier
        $file_size = $_FILES['image']['size'];

        //verifier que mon fichier respecte ces règles
        if(in_array($file_type, $allowed_types) && $file_size <= $max_size){
          //donner une extension au fichier
          $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
          //donner un nom
          $filename = uniqid('img_', true) . '.' .$extension;
          //donner un chemin d'upload
          $upload_path = 'uploads/' . $filename;

          //verifier et deplacer le fichier au bon endroit
          if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)){
            $image = $filename;
            $success = "image uploadée avec succès !";
          }
          else{
            $error = "Erreur lors de l'upload du fichier...";
          }
        }
        else{
          $error = "Fichier invalide... (types acceptés : jpg, png, webp)";
        }

      }

      //REQUETE UPDATE
      $stmt = $pdo->prepare("INSERT INTO articles (titre, description, prix, image) VALUES(?, ?, ?, ?)");
      $stmt->execute([$titre, $description, $prix, $image]);

      //rediriger l'user vers index une fois l'insert effectué
      // header("Location: index.php");
      // exit;
    }
    else{
      $error = "Erreur : champs manquants ou incorrects.";
    }
  }
?>  

    <!-- Begin page content -->
    <main role="main" class="container py-5">
      <h1 class="my-5">Ajout d'article</h1>
      <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?> 

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>    
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="titre" class="form-label">Titre</label>
          <input type="text" name="titre" class="form-control" id="titre" placeholder="renseigner un nom" required>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea name="description" class="form-control" id="description" placeholder="renseigner une description"></textarea>
        </div>
         <div class="mb-3">
          <label for="prix" class="form-label">Prix</label>
          <input type="number" name="prix" class="form-control" id="prix" placeholder="renseigner un prix" required>
        </div>
        <!-- Ajouter la partie catégorie -->
        <div class="mb-3">
          <label for="image" class="form-label">Image (jpg, png,...)</label>
          <input type="file" name="image" class="form-control" id="image" accept="image/*">
       
        </div>
        <button type="submit" class="btn btn-primary">Ajouter l'article</button>




      </form>

    </main>



    <!--JavaScript
    ================================================== -->
   
  </body>
</html>
