<?php
//1. etablir une connexion à la BDD
require_once 'config.php';

//2. preparer(stocker) la requete 
$sql = "SELECT * FROM articles";

//3. executer la requete
$res = $pdo->query($sql);

//4. traiter les données et les stocker dans un tableau associatif
$articles = $res->fetchAll(PDO::FETCH_ASSOC);
?>


<?php
require("header.php");
?>

    <!--TODO : flashmessage -->


    <!-- Begin page content -->
    <main role="main" class="container py-5">

      <?php if(estConnecte()) :?>
        <?php $user = getUtilisateur(); ?>
          <h1 class="mt-5"><strong>Projet CRUD</strong> - Bonjour <?= $user['prenom'] ?></h1>
        <?php endif; ?>
      <div class="py-5">
      

        <table class="table table-hover">
  <thead>
    <tr>
      <th scope="col">#id</th>
      <th scope="col">image</th>
      <th scope="col">titre</th>
      <th scope="col">Description</th>
      <th scope="col">Prix</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php
        //parcourir et afficher le tableau $articles
        foreach ($articles as $article) : ?>
            <tr>
            <th scope="row"><?=htmlspecialchars($article['id']);?></th>
            <td>
              <?php if ($article['image']): ?>
                <img src="uploads/<?=htmlspecialchars($article['image']) ?>"
                alt="<?=htmlspecialchars($article['titre']);?>"
                class="img-thumbnail object-fit-cover border rounded"
                style = "width: 60px; height: 60px;">
                <?php else : ?>
                  <span>Pas d'img</span>
                <?php endif; ?>  
            </td>
            <td><?=htmlspecialchars($article['titre']);?></td>
            <td><?=htmlspecialchars($article['description']);?></td>
            <td><?=htmlspecialchars($article['prix']);?></td>
            <td>
            <!-- BOUTON VOIR accessible a tous -->
              <a class="btn btn-success py-0" href="article.php?id=<?=htmlspecialchars($article['id']);?>">Voir</a>
            <!-- BOUTON MODIF / SUPPR accessible a admin (vendeur) -->
             <?php if (estVendeur()) : ?>
              <a class="btn btn-primary py-0" href="edit.php?id=<?=htmlspecialchars($article['id']);?>">Modif.</a>
              <a class="btn btn-danger py-0" onclick="return confirm('Voulez vous supprimer ?')" href="delete.php?id=<?=htmlspecialchars($article['id']);?>">Suppr.</a>
             <?php endif; ?> 
             <?php if (estClient()) : ?>
              <a class="btn btn-sm btn-danger" href="toggle_favoris.php?id=<?=htmlspecialchars($article['id']);?>">♥</a>
             <?php endif; ?> 
             </td>         
    <?php endforeach ?>

    </tr>
  </tbody>
</table>
      </div>
      </main>



    <!--JavaScript
    ================================================== -->
   
  </body>
</html>
