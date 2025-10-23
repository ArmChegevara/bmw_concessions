<?php
//1. demarrer une session
session_start();

if(isset($_SESSION["pseudo"])){
    echo "Bonjour " . $_SESSION["pseudo"];
    echo "<a href = logout.php>Se déconnecter </a>";
}

else{
    echo "veuillez vous connecter";
}