<?php
// Si fichier non accessible  : erreur + arret du script sur toute la page
require("../menu.php");
// identique mais un seul appel
// require_once("menu.php");

//Si le fihier non accessible : erreeur mais la page continue son script
//include("menu.php");
// include_once ("menu.php");
?>
<form method="POST" action="res.php">
    <input type="text" name="username">
    <input type="number" name="age">
    <input type="submit" value="Envoyer">
</form>
    