<?php
if (isset($_GET["lang"])){
    //Creer un cookie "lang" qui va durer 1h
    setcookie("lang", $_GET["lang"], time() + 3600);
    header('Location: langue.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerer des cookies</title>
</head>
<body>
    <h2>Choisissez une langue :</h2>
    <a href="?lang=fr">Français</a> |
    <a href="?lang=en">Anglais</a> 

    <p>
        <?php
            if (isset ($_COOKIE['lang'])){
                echo "langue choisie : " . $_COOKIE['lang'];
            }

            else{
                echo "aucune langue selectionnée...";
            }
        ?>
    </p>
</body>
</html>