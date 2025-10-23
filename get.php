<!-- Exemple de methode GET simple
Tester avec l'url : .../get.php?firstname=Tom&age=23 -->

<?php
echo "Mon prénom est " . $_GET['firstname'] . " et mon age est de " . $_GET['age'] . "ans";
?>