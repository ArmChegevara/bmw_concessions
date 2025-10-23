<?php

require("../menu.php");

// htmlspecialchars : fonction qui securise les injections html
$username = htmlspecialchars($_POST['username']);
$age = htmlspecialchars($_POST['age']);


echo "prénom : " . $username . " et mon age :" . $age . "ans";
?>

