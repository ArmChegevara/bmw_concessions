<?php
require_once 'config.php';
require("header.php");

$error ='';
$success ='';

//Verifier que l'user est connecté + qu'il a les droit (est vendeur)
if (!estConnecte() || !estVendeur()) {
header('Location: index.php');
exit;
}

//on vient verifier que id dans l'URL est présent et non NULL 
// si c'est le cas on prend sa version int sinon on la met à 0
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

//Recuperer l'article à modifier
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? ");
$stmt->execute([$id]);
$article = $stmt->fetch();

//verifie que l'article existe (en BDD)
if (!$article){
  $error = "Article introuvable...";
  exit;
}

//Traiter le form de modification
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $titre = $_POST['titre'];
  $description = $_POST['description'];
  $prix = $_POST['prix'];
  //reprend l'ancienne img par défaut
  $image = $article['image'];
  
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

  $stmt = $pdo->prepare("UPDATE articles SET titre=?, description=?, prix= ?, image=? WHERE id=?");
  $stmt->execute([$titre, $description, $prix, $image, $id]);

  header('Location: index.php');
  exit;
}
?>

    <!-- Begin page content -->
    <main role="main" class="container py-5">
      <!-- dispeser mes données pas besoin de foreach car un seul resultat -->
      <h1 class="mt-5">Modifier l'article <?=htmlspecialchars($article['titre']);?> </h1>
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" name="titre" class="form-control" id="titre" placeholder="renseigner un nom" required value="<?= htmlspecialchars($article['titre']) ?>">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control" id="description" >
              <?= htmlspecialchars($article['description']) ?>
            </textarea>
          </div>
          <div class="mb-3">
            <label for="prix" class="form-label">Prix</label>
            <input type="number" name="prix" class="form-control" id="prix" placeholder="renseigner un prix" required value="<?= htmlspecialchars($article['prix']) ?>">
          </div>
          <!-- Ajouter la partie catégorie -->

          <div class="mb-3">
            <label for="image" class="form-label">Image (jpg, png,...)</label>
            <?php if ($article['image']): ?>
              <div class="mb-2">
                <img src="uploads/<?= htmlspecialchars($article['image'])?>"
                alt="Image actuelle"
                class="img-thumbnail"
                style="max-width: 200px;">
                <p class="text-muted small">Image actuelle</p>
              </div>
              <?php endif; ?>
              <input type="file" name="image" class="form-control" id="image" accept="image/*">
                <p class="text-muted small">Laisser vide pour conserver l'img</p>
          </div>
          <button type="submit" class="btn btn-success">Modifier l'article</button>
      </form>    
      </main>


    <!--JavaScript
    ================================================== -->
   
  </body>
</html>
