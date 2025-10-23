<?php

//on vient verifier que id dans l'URL est présent et non NULL 
// si c'est le cas on prend sa version int sinon on la met à 0
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

//1. etablir une connexion à la BDD
require_once 'config.php';


try{
  //2. preparer(stocker) la requete 
  //on vient binder id
$sql = "SELECT * FROM articles WHERE id = :id";
//preparer la requete
$stmt = $pdo->prepare($sql);
// associer l'id à notre variable $id
$stmt->execute(['id' => $id]);

//4. Recuperer le SEUL resultat (fetch)
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// si ID est pas fdans la BDD affiche un message
if (!$article){
  echo "Article inexistant...";
  exit;
}
}

 catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}



?>

<?php
require("header.php");
?>

    <!-- Begin page content -->
    <main role="main" class="container py-5">
      <!-- dispeser mes données pas besoin de foreach car un seul resultat -->
      <h1 class="mt-5"><?=htmlspecialchars($article['titre']);?> </h1>

      <div class="py-5">
        <p><?=htmlspecialchars($article['description']);?></p>
        <strong><?=htmlspecialchars($article['prix']);?></strong>
         <?php if ($article['image']): ?>
              <div class="mb-2">
                <img src="uploads/<?= htmlspecialchars($article['image'])?>"
                alt="Image actuelle"
                class="img-thumbnail"
                style="max-width: 200px;">
                <!-- <p class="text-muted small">Image actuelle</p> -->
              </div>
              <?php endif; ?>
      </div>
      </main>


    <!--JavaScript
    ================================================== -->
   
  </body>
</html>
